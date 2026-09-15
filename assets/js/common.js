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
          if (label) label.textContent = "✓ 복사되었습니다";
          setTimeout(function () {
            btn.classList.remove("isCopied");
            if (label) {
              label.textContent = btn.classList.contains("accountRow") ? "터치하여 복사" : "주소 복사";
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

  function initAccountSheet() {
    var sheet = qs("[data-account-sheet]");
    if (!sheet) return;

    // scene에 transform이 있으면 fixed가 깨지므로 body로 이동
    if (sheet.parentElement !== document.body) {
      document.body.appendChild(sheet);
    }

    function openSheet() {
      sheet.hidden = false;
      sheet.setAttribute("aria-hidden", "false");
      // 다음 프레임에 isOpen → 하단 슬라이드 인
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

    qsa("[data-account-open]").forEach(function (btn) {
      btn.addEventListener("click", openSheet);
    });

    qsa("[data-account-close]").forEach(function (el) {
      el.addEventListener("click", closeSheet);
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && sheet.classList.contains("isOpen")) {
        closeSheet();
      }
    });

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
  }

  document.addEventListener("DOMContentLoaded", function () {
    initCopyButtons();
    initAccountSheet();
  });

  window.weddingCommon = {
    lockScroll: lockScroll,
    qs: qs,
    qsa: qsa,
  };
})();
