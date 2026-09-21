(function () {
  "use strict";

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function qsa(sel, root) {
    return Array.from((root || document).querySelectorAll(sel));
  }

  function lockScroll(lock) {
    document.body.classList.toggle("isScrollLocked", lock);
    document.body.classList.toggle("modal-open", lock);
  }

  function initCopyButtons() {
    qsa("[data-copy-target]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var text = btn.getAttribute("data-copy-target") || "";
        var label = qs("[data-copy-label]", btn);
        if (!text) return;

        function onSuccess() {
          btn.classList.add("isCopied");
          if (label) {
            label.textContent = btn.getAttribute("data-copy-done") || "✓ 복사됨";
          }
          setTimeout(function () {
            btn.classList.remove("isCopied");
            if (label) {
              label.textContent =
                btn.getAttribute("data-copy-default") ||
                (btn.classList.contains("accountRow") ? "터치하여 복사" : "주소 복사");
            }
          }, 1500);
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(onSuccess).catch(function () {
            fallbackCopy(text, onSuccess);
          });
        } else {
          fallbackCopy(text, onSuccess);
        }
      });
    });
  }

  function fallbackCopy(text, cb) {
    var ta = document.createElement("textarea");
    ta.value = text;
    ta.setAttribute("readonly", "");
    ta.style.position = "fixed";
    ta.style.left = "-9999px";
    document.body.appendChild(ta);
    ta.select();
    try {
      document.execCommand("copy");
      cb();
    } catch (e) {
      /* ignore */
    }
    document.body.removeChild(ta);
  }

  function bindSheetDragDismiss(sheet, closeFn) {
    if (!sheet || typeof closeFn !== "function") return;

    var panel = sheet.querySelector("[class*='__panel']");
    if (!panel) return;

    var HANDLE_ZONE = 56;
    var DISMISS_PX = 110;
    var DISMISS_VELOCITY = 0.65;
    var startY = 0;
    var lastY = 0;
    var lastT = 0;
    var dy = 0;
    var dragging = false;
    var activePointer = null;
    var canDrag = false;

    function scrollParent(el) {
      var node = el;
      while (node && node !== panel) {
        if (node.scrollHeight > node.clientHeight + 1) {
          var style = window.getComputedStyle(node);
          var oy = style.overflowY;
          if (oy === "auto" || oy === "scroll" || oy === "overlay") {
            return node;
          }
        }
        node = node.parentElement;
      }
      if (panel.scrollHeight > panel.clientHeight + 1) return panel;
      return null;
    }

    function resetPanel(animate) {
      panel.style.transition = animate
        ? "transform 0.28s cubic-bezier(0.22, 1, 0.36, 1)"
        : "";
      panel.style.transform = "";
      panel.classList.remove("isDragging");
      window.setTimeout(function () {
        if (!panel.classList.contains("isDragging")) {
          panel.style.transition = "";
        }
      }, 300);
    }

    function onPointerDown(e) {
      if (!sheet.classList.contains("isOpen")) return;
      if (activePointer !== null) return;
      if (e.pointerType === "mouse" && e.button !== 0) return;

      var target = e.target;
      if (
        target.closest(
          "button, a, input, textarea, select, label, [role='tab'], [data-copy-target]"
        )
      ) {
        var rectEarly = panel.getBoundingClientRect();
        if (e.clientY - rectEarly.top > HANDLE_ZONE) return;
      }

      var rect = panel.getBoundingClientRect();
      var fromHandle = e.clientY - rect.top <= HANDLE_ZONE;
      // 핸들 바 영역에서만 드래그 닫기 (본문 스크롤과 충돌 방지)
      canDrag = fromHandle;
      if (!canDrag) return;

      activePointer = e.pointerId;
      startY = e.clientY;
      lastY = e.clientY;
      lastT = Date.now();
      dy = 0;
      dragging = false;
      try {
        panel.setPointerCapture(e.pointerId);
      } catch (err) {
        /* ignore */
      }
    }

    function onPointerMove(e) {
      if (activePointer !== e.pointerId || !canDrag) return;

      var delta = e.clientY - startY;
      if (!dragging) {
        if (delta < 8) return;
        var scroller = scrollParent(e.target);
        var rect = panel.getBoundingClientRect();
        var fromHandle = startY - rect.top <= HANDLE_ZONE;
        if (!fromHandle && scroller && scroller.scrollTop > 0) {
          activePointer = null;
          canDrag = false;
          return;
        }
        dragging = true;
        panel.classList.add("isDragging");
        panel.style.transition = "none";
      }

      dy = Math.max(0, delta);
      panel.style.transform = "translateY(" + dy + "px)";
      lastY = e.clientY;
      lastT = Date.now();
      if (e.cancelable) e.preventDefault();
    }

    function onPointerUp(e) {
      if (activePointer !== e.pointerId) return;
      activePointer = null;
      canDrag = false;

      if (!dragging) {
        resetPanel(false);
        return;
      }

      var elapsed = Math.max(1, Date.now() - lastT);
      var velocity = (e.clientY - lastY) / elapsed;
      var shouldClose = dy >= DISMISS_PX || velocity > DISMISS_VELOCITY;

      if (shouldClose) {
        panel.style.transition =
          "transform 0.24s cubic-bezier(0.22, 1, 0.36, 1)";
        panel.style.transform = "translateY(110%)";
        panel.classList.remove("isDragging");
        window.setTimeout(function () {
          closeFn();
          panel.style.transition = "";
          panel.style.transform = "";
        }, 220);
      } else {
        resetPanel(true);
      }
      dragging = false;
    }

    panel.addEventListener("pointerdown", onPointerDown);
    panel.addEventListener("pointermove", onPointerMove);
    panel.addEventListener("pointerup", onPointerUp);
    panel.addEventListener("pointercancel", onPointerUp);
  }

  function initBottomSheet(options) {
    var sheet = qs(options.sheet);
    if (!sheet) return;

    // scene에 transform이 있으면 fixed가 깨지므로 body로 이동
    if (sheet.parentElement !== document.body) {
      document.body.appendChild(sheet);
    }

    function openSheet() {
      sheet.hidden = false;
      sheet.setAttribute("aria-hidden", "false");
      requestAnimationFrame(function () {
        sheet.classList.add("isOpen");
      });
      lockScroll(true);
    }

    function closeSheet() {
      sheet.classList.remove("isOpen");
      lockScroll(false);
      window.setTimeout(function () {
        if (!sheet.classList.contains("isOpen")) {
          sheet.hidden = true;
          sheet.setAttribute("aria-hidden", "true");
        }
      }, 320);
    }

    qsa(options.open).forEach(function (btn) {
      btn.addEventListener("click", openSheet);
    });

    qsa(options.close, sheet).forEach(function (el) {
      el.addEventListener("click", closeSheet);
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && sheet.classList.contains("isOpen")) {
        closeSheet();
      }
    });

    bindSheetDragDismiss(sheet, closeSheet);

    if (typeof options.onInit === "function") {
      options.onInit(sheet);
    }
  }

  function initAccountSheet() {
    initBottomSheet({
      sheet: "[data-account-sheet]",
      open: "[data-account-open]",
      close: "[data-account-close]",
      onInit: function () {
        qsa("[data-account-tab]").forEach(function (tab) {
          tab.addEventListener("click", function () {
            var key = tab.getAttribute("data-account-tab");
            qsa("[data-account-tab]").forEach(function (t) {
              t.classList.toggle("isActive", t === tab);
              t.setAttribute("aria-selected", t === tab ? "true" : "false");
            });
            qsa("[data-account-panel]").forEach(function (panel) {
              panel.hidden = panel.getAttribute("data-account-panel") !== key;
            });
          });
        });
      },
    });
  }

  function initContactSheet() {
    initBottomSheet({
      sheet: "[data-contact-sheet]",
      open: "[data-contact-open]",
      close: "[data-contact-close]",
      onInit: function (sheet) {
        qsa("[data-contact-tab]", sheet).forEach(function (tab) {
          tab.addEventListener("click", function () {
            var key = tab.getAttribute("data-contact-tab");
            qsa("[data-contact-tab]", sheet).forEach(function (t) {
              t.classList.toggle("isActive", t === tab);
              t.setAttribute("aria-selected", t === tab ? "true" : "false");
            });
            qsa("[data-contact-panel]", sheet).forEach(function (panel) {
              panel.hidden = panel.getAttribute("data-contact-panel") !== key;
            });
          });
        });
      },
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    initCopyButtons();
    initAccountSheet();
    initContactSheet();
  });

  window.weddingCommon = {
    lockScroll: lockScroll,
    qs: qs,
    qsa: qsa,
    bindSheetDragDismiss: bindSheetDragDismiss,
  };
})();
