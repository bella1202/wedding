(function () {
  "use strict";

  var MODAL_PAGE_SIZE = 10;
  var modalOffset = 0;
  var modalLoading = false;
  var deletingId = null;
  var submitting = false;

  function apiBase() {
    var path = window.location.pathname;
    if (path.endsWith(".php")) {
      path = path.replace(/\/[^/]*$/, "/");
    }
    if (!path.endsWith("/")) path += "/";
    return path + "api/guestbook/";
  }

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function qsa(sel, root) {
    return Array.from((root || document).querySelectorAll(sel));
  }

  function formatDate(iso) {
    if (!iso) return "";
    var d = new Date(String(iso).replace(" ", "T"));
    if (Number.isNaN(d.getTime())) return iso;
    var y = d.getFullYear();
    var m = String(d.getMonth() + 1).padStart(2, "0");
    var day = String(d.getDate()).padStart(2, "0");
    var h = String(d.getHours()).padStart(2, "0");
    var min = String(d.getMinutes()).padStart(2, "0");
    return y + "." + m + "." + day + " " + h + ":" + min;
  }

  function lockScroll(on) {
    if (window.weddingCommon && window.weddingCommon.lockScroll) {
      window.weddingCommon.lockScroll(on);
      return;
    }
    document.body.classList.toggle("isScrollLocked", !!on);
  }

  function showToast() {
    var toast = qs("[data-guestbook-toast]");
    if (!toast) return;
    toast.hidden = false;
    toast.classList.add("isShow");
    setTimeout(function () {
      toast.classList.remove("isShow");
      toast.hidden = true;
    }, 1600);
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

  function openWriteSheet() {
    var sheet = qs("[data-guestbook-sheet]");
    if (!sheet) return;
    sheet.hidden = false;
    sheet.setAttribute("aria-hidden", "false");
    requestAnimationFrame(function () {
      sheet.classList.add("isOpen");
    });
    lockScroll(true);
    var nameInput = sheet.querySelector('input[name="name"]');
    if (nameInput) setTimeout(function () { nameInput.focus(); }, 280);
  }

  function closeWriteSheet() {
    var sheet = qs("[data-guestbook-sheet]");
    if (!sheet) return;
    sheet.classList.remove("isOpen");
    lockScroll(false);
    window.setTimeout(function () {
      if (!sheet.classList.contains("isOpen")) {
        sheet.hidden = true;
        sheet.setAttribute("aria-hidden", "true");
      }
    }, 320);
  }

  function openAllModal() {
    var modal = qs("[data-guestbook-all]");
    if (!modal) return;
    if (modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }
    modal.hidden = false;
    modal.setAttribute("aria-hidden", "false");
    requestAnimationFrame(function () {
      modal.classList.add("isOpen");
    });
    lockScroll(true);
    loadModalMessages(true);
  }

  function closeAllModal() {
    var modal = qs("[data-guestbook-all]");
    if (!modal) return;
    modal.classList.remove("isOpen");
    lockScroll(false);
    window.setTimeout(function () {
      if (!modal.classList.contains("isOpen")) {
        modal.hidden = true;
        modal.setAttribute("aria-hidden", "true");
      }
    }, 320);
  }

  function playDeliverAnimation(done) {
    var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (reduced) {
      done();
      return;
    }

    var couple = qs("[data-floating-couple]");
    var writeBtn = qs("[data-guestbook-write]");
    var box = qs(".guestbookBox");
    var startEl = writeBtn || box;
    if (!startEl) {
      done();
      return;
    }

    var start = startEl.getBoundingClientRect();
    var targetEl = couple && !couple.classList.contains("isHidden") ? couple : box;
    if (!targetEl) {
      done();
      return;
    }
    var end = targetEl.getBoundingClientRect();

    var flyer = document.createElement("div");
    flyer.className = "guestbookFlyer";
    flyer.innerHTML =
      '<div class="guestbookFlyer__note">' +
      '<span class="guestbookFlyer__fold"></span>' +
      "<span>♥</span>" +
      "</div>";
    document.body.appendChild(flyer);

    var startX = start.left + start.width / 2 - 28;
    var startY = start.top + start.height / 2 - 28;
    var endX = end.left + end.width / 2 - 28;
    var endY = end.top + end.height * 0.35 - 28;

    flyer.style.left = startX + "px";
    flyer.style.top = startY + "px";

    requestAnimationFrame(function () {
      flyer.classList.add("isFold");
      window.setTimeout(function () {
        flyer.classList.add("isFly");
        flyer.style.left = endX + "px";
        flyer.style.top = endY + "px";
      }, 220);
    });

    window.setTimeout(function () {
      flyer.classList.add("isGone");
      if (box) {
        box.classList.add("isCatch");
        window.setTimeout(function () {
          box.classList.remove("isCatch");
        }, 500);
      }
      window.setTimeout(function () {
        flyer.remove();
        done();
      }, 280);
    }, 980);
  }

  function buildNote(item) {
    var article = document.createElement("article");
    article.className = "guestbookNote";
    article.setAttribute("data-guestbook-id", String(item.id));
    article.innerHTML =
      '<div class="guestbookNote__top">' +
      '<div class="guestbookNote__meta">' +
      '<p class="guestbookNote__name"></p>' +
      '<p class="guestbookNote__date"></p>' +
      "</div>" +
      '<div class="guestbookNote__menuWrap">' +
      '<button type="button" class="guestbookNote__menu touchBtn" data-guestbook-menu aria-label="메뉴" aria-expanded="false">•••</button>' +
      '<div class="guestbookNote__actions" data-guestbook-actions hidden>' +
      '<button type="button" class="guestbookNote__actionBtn touchBtn" data-guestbook-delete>삭제</button>' +
      "</div>" +
      "</div>" +
      "</div>" +
      '<p class="guestbookNote__message"></p>';

    article.querySelector(".guestbookNote__name").textContent = item.name;
    article.querySelector(".guestbookNote__message").textContent = item.message;
    article.querySelector(".guestbookNote__date").textContent = formatDate(item.created_at);
    return article;
  }

  function syncModalEmpty() {
    var list = qs("[data-guestbook-all-list]");
    var empty = qs("[data-guestbook-all-empty]");
    if (!list || !empty) return;
    var hasNotes = !!list.querySelector(".guestbookNote");
    empty.hidden = hasNotes;
  }

  function renderModalItem(item, prepend) {
    var list = qs("[data-guestbook-all-list]");
    if (!list) return;
    var empty = qs("[data-guestbook-all-empty]");
    if (empty) empty.hidden = true;

    var article = buildNote(item);
    if (prepend) {
      if (empty && empty.parentElement === list) {
        list.insertBefore(article, empty.nextSibling);
      } else {
        list.prepend(article);
      }
      article.classList.add("isArrive");
    } else {
      list.appendChild(article);
    }
    requestAnimationFrame(function () {
      article.classList.add("isVisible");
    });
  }

  function loadModalMessages(reset) {
    if (modalLoading) return Promise.resolve();
    var list = qs("[data-guestbook-all-list]");
    var more = qs("[data-guestbook-all-more]");
    var empty = qs("[data-guestbook-all-empty]");
    if (!list) return Promise.resolve();

    if (reset) {
      modalOffset = 0;
      qsa(".guestbookNote", list).forEach(function (n) {
        n.remove();
      });
      if (empty) empty.hidden = true;
    }

    modalLoading = true;
    return fetch(
      apiBase() + "list.php?limit=" + MODAL_PAGE_SIZE + "&offset=" + modalOffset
    )
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (!data.success) throw new Error(data.error || "load failed");

        if (!data.items.length && modalOffset === 0) {
          if (empty) empty.hidden = false;
        } else {
          data.items.forEach(function (item) {
            renderModalItem(item, false);
          });
        }

        modalOffset += data.items.length;
        if (more) more.hidden = !data.hasMore;
        syncModalEmpty();
      })
      .catch(function () {
        if (empty && modalOffset === 0) empty.hidden = false;
      })
      .finally(function () {
        modalLoading = false;
      });
  }

  function openLegalModal(kind) {
    var modal = qs('[data-guestbook-legal="' + kind + '"]');
    if (!modal) return;
    if (modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }
    modal.hidden = false;
    modal.setAttribute("aria-hidden", "false");
    requestAnimationFrame(function () {
      modal.classList.add("isOpen");
    });
  }

  function closeLegalModal(modal) {
    if (!modal) return;
    modal.classList.remove("isOpen");
    window.setTimeout(function () {
      modal.hidden = true;
      modal.setAttribute("aria-hidden", "true");
    }, 320);
  }

  function initWriteSheet() {
    var sheet = qs("[data-guestbook-sheet]");
    if (!sheet) return;

    if (sheet.parentElement !== document.body) {
      document.body.appendChild(sheet);
    }

    var writeBtn = qs("[data-guestbook-write]");
    if (writeBtn) writeBtn.addEventListener("click", openWriteSheet);

    sheet.querySelectorAll("[data-guestbook-sheet-close]").forEach(function (el) {
      el.addEventListener("click", closeWriteSheet);
    });

    if (window.weddingCommon && window.weddingCommon.bindSheetDragDismiss) {
      window.weddingCommon.bindSheetDragDismiss(sheet, closeWriteSheet);
    }

    qsa("[data-guestbook-legal-open]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        openLegalModal(btn.getAttribute("data-guestbook-legal-open") || "privacy");
      });
    });

    qsa("[data-guestbook-legal]").forEach(function (modal) {
      if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
      }
      qsa("[data-guestbook-legal-close]", modal).forEach(function (el) {
        el.addEventListener("click", function () {
          closeLegalModal(modal);
        });
      });
    });
  }

  function initAllModal() {
    var modal = qs("[data-guestbook-all]");
    if (!modal) return;

    if (modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }

    var openBtn = qs("[data-guestbook-more]");
    if (openBtn) {
      openBtn.addEventListener("click", openAllModal);
    }

    qsa("[data-guestbook-all-close]", modal).forEach(function (el) {
      el.addEventListener("click", closeAllModal);
    });

    if (window.weddingCommon && window.weddingCommon.bindSheetDragDismiss) {
      window.weddingCommon.bindSheetDragDismiss(modal, closeAllModal);
    }

    var allMore = qs("[data-guestbook-all-more]", modal);
    if (allMore) {
      allMore.addEventListener("click", function () {
        loadModalMessages(false);
      });
    }
  }

  function initForm() {
    var form = qs("[data-guestbook-form]");
    if (!form) return;

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      if (submitting) return;

      var errorEl = qs("[data-guestbook-error]");
      setError(errorEl, "");

      var fd = new FormData(form);
      var payload = {
        name: String(fd.get("name") || "").trim(),
        message: String(fd.get("message") || "").trim(),
        password: String(fd.get("password") || ""),
        website: String(fd.get("website") || ""),
      };

      if (!payload.name || payload.name.length > 20) {
        setError(errorEl, "이름은 1~20자로 입력해주세요.");
        return;
      }
      if (!payload.message || payload.message.length > 300) {
        setError(errorEl, "메시지는 1~300자로 입력해주세요.");
        return;
      }
      if (payload.password.length < 4) {
        setError(errorEl, "비밀번호는 4자 이상 입력해주세요.");
        return;
      }

      submitting = true;
      var btn = qs("[data-guestbook-submit]");
      if (btn) btn.disabled = true;

      fetch(apiBase() + "create.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      })
        .then(function (r) {
          return r.json().then(function (data) {
            return { ok: r.ok, data: data };
          });
        })
        .then(function (res) {
          if (!res.ok || !res.data.success) {
            setError(errorEl, (res.data && res.data.error) || "저장에 실패했습니다.");
            return;
          }

          form.reset();
          closeWriteSheet();

          playDeliverAnimation(function () {
            var allModal = qs("[data-guestbook-all]");
            if (allModal && allModal.classList.contains("isOpen")) {
              renderModalItem(res.data.item, true);
              modalOffset += 1;
              syncModalEmpty();
            }
            showToast();
          });
        })
        .catch(function () {
          setError(errorEl, "저장에 실패했습니다.");
        })
        .finally(function () {
          submitting = false;
          if (btn) btn.disabled = false;
        });
    });
  }

  function removeNoteEverywhere(id) {
    qsa('[data-guestbook-id="' + id + '"]').forEach(function (node) {
      node.classList.add("isLeave");
      window.setTimeout(function () {
        node.remove();
        syncModalEmpty();
      }, 280);
    });
  }

  function initListActions() {
    var allList = qs("[data-guestbook-all-list]");
    var modal = qs("[data-guestbook-delete-modal]");
    if (!modal) return;

    if (modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }

    function closeAllMenus(except) {
      qsa("[data-guestbook-actions]").forEach(function (actions) {
        if (except && actions === except) return;
        actions.hidden = true;
        var wrap = actions.closest(".guestbookNote__menuWrap");
        var btn = wrap && wrap.querySelector("[data-guestbook-menu]");
        if (btn) btn.setAttribute("aria-expanded", "false");
      });
    }

    function onListClick(e) {
      var menu = e.target.closest("[data-guestbook-menu]");
      if (menu) {
        e.stopPropagation();
        var note = menu.closest(".guestbookNote");
        var actions = note && note.querySelector("[data-guestbook-actions]");
        if (!actions) return;
        var willOpen = actions.hidden;
        closeAllMenus(willOpen ? actions : null);
        actions.hidden = !willOpen;
        menu.setAttribute("aria-expanded", willOpen ? "true" : "false");
        return;
      }

      var del = e.target.closest("[data-guestbook-delete]");
      if (del) {
        e.stopPropagation();
        closeAllMenus();
        var note2 = del.closest(".guestbookNote");
        deletingId = note2 ? parseInt(note2.getAttribute("data-guestbook-id"), 10) : null;
        modal.hidden = false;
        modal.setAttribute("aria-hidden", "false");
        requestAnimationFrame(function () {
          modal.classList.add("isOpen");
        });
        lockScroll(true);
        setError(qs("[data-guestbook-delete-error]"), "");
        var pw = qs("[data-guestbook-delete-password]");
        if (pw) pw.value = "";
      }
    }

    if (allList) {
      allList.addEventListener("click", onListClick);
    }

    document.addEventListener("click", function (e) {
      if (e.target.closest(".guestbookNote__menuWrap")) return;
      closeAllMenus();
    });

    function closeDeleteModal() {
      modal.classList.remove("isOpen");
      var allOpen = qs("[data-guestbook-all]");
      if (!(allOpen && allOpen.classList.contains("isOpen"))) {
        lockScroll(false);
      }
      window.setTimeout(function () {
        if (!modal.classList.contains("isOpen")) {
          modal.hidden = true;
          modal.setAttribute("aria-hidden", "true");
        }
      }, 280);
      deletingId = null;
    }

    modal.querySelectorAll("[data-guestbook-delete-close]").forEach(function (el) {
      el.addEventListener("click", closeDeleteModal);
    });

    var confirm = qs("[data-guestbook-delete-confirm]");
    if (confirm) {
      confirm.addEventListener("click", function () {
        var pw = qs("[data-guestbook-delete-password]");
        var password = pw ? pw.value : "";
        var err = qs("[data-guestbook-delete-error]");
        if (!deletingId) return;
        if (password.length < 4) {
          setError(err, "비밀번호는 4자 이상 입력해주세요.");
          return;
        }

        fetch(apiBase() + "delete.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ id: deletingId, password: password, website: "" }),
        })
          .then(function (r) {
            return r.json().then(function (data) {
              return { ok: r.ok, data: data };
            });
          })
          .then(function (res) {
            if (!res.ok || !res.data.success) {
              setError(err, (res.data && res.data.error) || "삭제에 실패했습니다.");
              return;
            }
            var id = deletingId;
            removeNoteEverywhere(id);
            if (modalOffset > 0) modalOffset = Math.max(0, modalOffset - 1);
            closeDeleteModal();
          })
          .catch(function () {
            setError(err, "삭제에 실패했습니다.");
          });
      });
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    if (!qs("[data-guestbook-write]") && !qs("[data-guestbook-form]")) return;
    initWriteSheet();
    initAllModal();
    initForm();
    initListActions();

    document.addEventListener("keydown", function (e) {
      if (e.key !== "Escape") return;
      var openLegal = qs("[data-guestbook-legal].isOpen");
      if (openLegal) {
        closeLegalModal(openLegal);
        return;
      }
      var deleteModal = qs("[data-guestbook-delete-modal]");
      if (deleteModal && deleteModal.classList.contains("isOpen")) return;
      var allModal = qs("[data-guestbook-all]");
      if (allModal && allModal.classList.contains("isOpen")) {
        closeAllModal();
        return;
      }
      var sheet = qs("[data-guestbook-sheet]");
      if (sheet && sheet.classList.contains("isOpen")) {
        closeWriteSheet();
      }
    });
  });
})();
