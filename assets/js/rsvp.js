(function () {
  "use strict";

  var STORAGE_KEY = "wedding_rsvp_hide_date";
  var DONE_KEY = "wedding_rsvp_submitted";
  var submitting = false;

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function qsa(sel, root) {
    return Array.from((root || document).querySelectorAll(sel));
  }

  function todayKey() {
    var d = new Date();
    return (
      d.getFullYear() +
      "-" +
      String(d.getMonth() + 1).padStart(2, "0") +
      "-" +
      String(d.getDate()).padStart(2, "0")
    );
  }

  function shouldShow() {
    try {
      if (localStorage.getItem(DONE_KEY) === "1") return false;
      if (localStorage.getItem(STORAGE_KEY) === todayKey()) return false;
    } catch (e) {
      /* ignore */
    }
    return true;
  }

  function markHideToday() {
    try {
      localStorage.setItem(STORAGE_KEY, todayKey());
    } catch (e) {
      /* ignore */
    }
  }

  function markDone() {
    try {
      localStorage.setItem(DONE_KEY, "1");
      localStorage.removeItem(STORAGE_KEY);
    } catch (e) {
      /* ignore */
    }
  }

  function apiBase() {
    var path = window.location.pathname;
    if (path.endsWith(".php")) {
      path = path.replace(/\/[^/]*$/, "/");
    }
    if (!path.endsWith("/")) path += "/";
    return path + "api/rsvp/";
  }

  function lockScroll(on) {
    if (window.weddingCommon && window.weddingCommon.lockScroll) {
      window.weddingCommon.lockScroll(on);
      return;
    }
    document.body.classList.toggle("isScrollLocked", !!on);
    document.body.classList.toggle("modal-open", !!on);
  }

  function setError(el, msg) {
    if (!el) return;
    if (!msg) {
      el.hidden = true;
      el.textContent = "";
      return;
    }
    el.hidden = false;
    el.textContent = msg;
  }

  function showDoneMessage(modal, attending) {
    qsa("[data-rsvp-done-msg]", modal).forEach(function (el) {
      var match = el.getAttribute("data-rsvp-done-msg") === attending;
      el.hidden = !match;
    });
  }

  function showView(modal, name) {
    qsa("[data-rsvp-view]", modal).forEach(function (view) {
      var match = view.getAttribute("data-rsvp-view") === name;
      view.hidden = !match;
    });
  }

  function openModal(modal) {
    modal.hidden = false;
    modal.setAttribute("aria-hidden", "false");
    requestAnimationFrame(function () {
      modal.classList.add("isOpen");
    });
    lockScroll(true);
  }

  function closeModal(modal) {
    var hideToday = qs("[data-rsvp-hide-today]", modal);
    if (hideToday && hideToday.checked) {
      markHideToday();
    }
    closePrivacyModal();
    modal.classList.remove("isOpen");
    lockScroll(false);
    window.setTimeout(function () {
      modal.hidden = true;
      modal.setAttribute("aria-hidden", "true");
      showView(modal, "intro");
    }, 340);
  }

  function openPrivacyModal() {
    var privacy = qs("[data-rsvp-privacy-modal]");
    if (!privacy) return;
    if (privacy.parentElement !== document.body) {
      document.body.appendChild(privacy);
    }
    privacy.hidden = false;
    privacy.setAttribute("aria-hidden", "false");
    requestAnimationFrame(function () {
      privacy.classList.add("isOpen");
    });
  }

  function closePrivacyModal() {
    var privacy = qs("[data-rsvp-privacy-modal]");
    if (!privacy) return;
    privacy.classList.remove("isOpen");
    window.setTimeout(function () {
      if (!privacy.classList.contains("isOpen")) {
        privacy.hidden = true;
        privacy.setAttribute("aria-hidden", "true");
      }
    }, 280);
  }

  function digitsOnly(value) {
    return String(value || "").replace(/\D+/g, "");
  }

  function syncAttendFields(form) {
    var attending = form.querySelector('input[name="attending"]:checked');
    var wrap = qs("[data-rsvp-attend-fields]", form);
    var guestsField = qs("[data-rsvp-guests-field]", form);
    var guestsLabel = qs("[data-rsvp-guests-label]", form);
    var attendGrid = qs("[data-rsvp-attend-grid]", form);
    var isYes = attending && attending.value === "yes";

    if (wrap) wrap.hidden = !isYes;
    if (guestsField) guestsField.hidden = !isYes;
    if (guestsLabel) guestsLabel.hidden = !isYes;
    if (attendGrid) {
      attendGrid.classList.toggle("rsvpForm__chips--3", isYes);
      attendGrid.classList.toggle("rsvpForm__chips--2", !isYes);
    }

    var guests = form.querySelector('input[name="guests"]');
    if (guests) {
      guests.disabled = !isYes;
      guests.required = isYes;
    }
    qsa('input[name="meal"]', form).forEach(function (el) {
      el.disabled = !isYes;
      el.required = isYes;
    });
    qsa('input[name="bus"]', form).forEach(function (el) {
      el.disabled = !isYes;
      el.required = isYes;
    });
  }

  function initRsvp() {
    var modal = qs("[data-rsvp-modal]");
    if (!modal) return;

    if (modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }

    var privacy = qs("[data-rsvp-privacy-modal]");
    if (privacy && privacy.parentElement !== document.body) {
      document.body.appendChild(privacy);
    }

    var form = qs("[data-rsvp-form]", modal);
    var errorEl = qs("[data-rsvp-error]", modal);
    var submitBtn = qs("[data-rsvp-submit]", modal);

    qsa("[data-rsvp-close]", modal).forEach(function (btn) {
      btn.addEventListener("click", function () {
        closeModal(modal);
      });
    });

    if (window.weddingCommon && window.weddingCommon.bindSheetDragDismiss) {
      window.weddingCommon.bindSheetDragDismiss(modal, function () {
        closeModal(modal);
      });
    }

    qsa("[data-rsvp-open]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        setError(errorEl, "");
        showView(modal, "intro");
        openModal(modal);
      });
    });

    qsa("[data-rsvp-privacy-open]", modal).forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        openPrivacyModal();
      });
    });

    qsa("[data-rsvp-privacy-close]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        closePrivacyModal();
      });
    });

    document.addEventListener("keydown", function (e) {
      if (e.key !== "Escape") return;
      var privacyModal = qs("[data-rsvp-privacy-modal]");
      if (privacyModal && privacyModal.classList.contains("isOpen")) {
        closePrivacyModal();
        return;
      }
      if (modal.classList.contains("isOpen")) {
        closeModal(modal);
      }
    });

    var openForm = qs("[data-rsvp-open-form]", modal);
    if (openForm) {
      openForm.addEventListener("click", function () {
        showView(modal, "form");
        if (form) {
          syncAttendFields(form);
          var nameInput = form.querySelector('input[name="name"]');
          if (nameInput) setTimeout(function () { nameInput.focus(); }, 120);
        }
      });
    }

    var back = qs("[data-rsvp-back]", modal);
    if (back) {
      back.addEventListener("click", function () {
        setError(errorEl, "");
        showView(modal, "intro");
      });
    }

    if (form) {
      qsa('input[name="attending"]', form).forEach(function (el) {
        el.addEventListener("change", function () {
          syncAttendFields(form);
        });
      });
      syncAttendFields(form);

      var guestsInput = qs("[data-rsvp-guests]", form);
      if (guestsInput) {
        guestsInput.addEventListener("input", function () {
          var next = digitsOnly(guestsInput.value);
          if (guestsInput.value !== next) {
            guestsInput.value = next;
          }
        });
        guestsInput.addEventListener("keypress", function (e) {
          if (e.ctrlKey || e.metaKey || e.key.length !== 1) return;
          if (!/[0-9]/.test(e.key)) {
            e.preventDefault();
          }
        });
        guestsInput.addEventListener("paste", function (e) {
          e.preventDefault();
          var text = (e.clipboardData || window.clipboardData).getData("text");
          guestsInput.value = digitsOnly(text).slice(0, 2);
        });
      }

      form.addEventListener("submit", function (e) {
        e.preventDefault();
        if (submitting) return;

        setError(errorEl, "");
        var fd = new FormData(form);
        var attending = String(fd.get("attending") || "");
        var isYes = attending === "yes";
        var guestsRaw = digitsOnly(fd.get("guests"));
        var guests = guestsRaw === "" ? 0 : parseInt(guestsRaw, 10);
        var privacy = qs("[data-rsvp-privacy]", form);

        var payload = {
          website: fd.get("website") || "",
          name: String(fd.get("name") || "").trim(),
          side: String(fd.get("side") || ""),
          attending: attending,
          guests: isYes ? guests : 0,
          meal: isYes ? String(fd.get("meal") || "") : "none",
          bus: isYes ? String(fd.get("bus") || "") : "none",
          message: String(fd.get("message") || "").trim(),
          privacy: privacy && privacy.checked ? "1" : "",
        };

        if (!payload.name) {
          setError(errorEl, "성함을 입력해주세요.");
          return;
        }
        if (!payload.side) {
          setError(errorEl, "신랑측/신부측을 선택해주세요.");
          return;
        }
        if (!payload.attending) {
          setError(errorEl, "참석 여부를 선택해주세요.");
          return;
        }
        if (isYes) {
          if (!guestsRaw || guests < 1 || guests > 20) {
            setError(errorEl, "참석 인원은 1~20명으로 입력해주세요.");
            return;
          }
          if (payload.meal !== "yes" && payload.meal !== "no" && payload.meal !== "maybe") {
            setError(errorEl, "식사 여부를 선택해주세요.");
            return;
          }
          if (payload.bus !== "yes" && payload.bus !== "no") {
            setError(errorEl, "전세버스 탑승 여부를 선택해주세요.");
            return;
          }
        }
        if (!payload.privacy) {
          setError(errorEl, "개인정보 수집 및 활용에 동의해주세요.");
          return;
        }

        submitting = true;
        if (submitBtn) submitBtn.disabled = true;

        fetch(apiBase() + "create.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        })
          .then(function (res) {
            return res.json().then(function (data) {
              return { ok: res.ok, data: data };
            });
          })
          .then(function (result) {
            if (!result.ok || !result.data || !result.data.success) {
              throw new Error(
                (result.data && result.data.error) || "저장에 실패했습니다."
              );
            }
            markDone();
            form.reset();
            syncAttendFields(form);
            showDoneMessage(modal, attending === "yes" ? "yes" : "no");
            showView(modal, "done");
          })
          .catch(function (err) {
            setError(errorEl, err.message || "저장에 실패했습니다.");
          })
          .finally(function () {
            submitting = false;
            if (submitBtn) submitBtn.disabled = false;
          });
      });
    }

    if (shouldShow()) {
      var opened = false;
      function tryOpen() {
        if (opened || !shouldShow()) return;
        opened = true;
        openModal(modal);
      }

      document.addEventListener("wedding:post-hero", tryOpen);

      var menuRoot = document.querySelector("[data-quick-nav]");
      if (menuRoot && !menuRoot.classList.contains("isHidden")) {
        window.setTimeout(tryOpen, 200);
      }
    }
  }

  document.addEventListener("DOMContentLoaded", initRsvp);
})();
