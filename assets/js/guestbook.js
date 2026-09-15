(function () {
  "use strict";

  var PAGE_SIZE = 5;
  var offset = 0;
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

  function formatDate(iso) {
    if (!iso) return "";
    var d = new Date(String(iso).replace(" ", "T"));
    if (Number.isNaN(d.getTime())) return iso;
    var y = d.getFullYear();
    var m = String(d.getMonth() + 1).padStart(2, "0");
    var day = String(d.getDate()).padStart(2, "0");
    return y + "." + m + "." + day;
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

  function renderItem(item, prepend) {
    var list = qs("[data-guestbook-list]");
    var empty = qs("[data-guestbook-empty]");
    if (!list) return;

    if (empty) empty.hidden = true;

    var article = document.createElement("article");
    article.className = "guestbookNote";
    article.setAttribute("data-guestbook-id", String(item.id));
    article.innerHTML =
      '<div class="guestbookNote__pin" aria-hidden="true"></div>' +
      '<div class="guestbookNote__top">' +
      '<p class="guestbookNote__name"></p>' +
      '<button type="button" class="guestbookNote__menu touchBtn" data-guestbook-menu aria-label="메뉴">•••</button>' +
      "</div>" +
      '<p class="guestbookNote__message"></p>' +
      '<p class="guestbookNote__date"></p>' +
      '<div class="guestbookNote__actions" data-guestbook-actions hidden>' +
      '<button type="button" class="touchBtn" data-guestbook-delete>삭제</button>' +
      "</div>";

    article.querySelector(".guestbookNote__name").textContent = item.name;
    article.querySelector(".guestbookNote__message").textContent = item.message;
    article.querySelector(".guestbookNote__date").textContent = formatDate(item.created_at);

    if (prepend) {
      list.prepend(article);
      article.classList.add("isArrive");
    } else {
      list.appendChild(article);
    }

    requestAnimationFrame(function () {
      article.classList.add("isVisible");
    });
  }

  function loadMessages(reset) {
    if (reset) {
      offset = 0;
      var list = qs("[data-guestbook-list]");
      if (list) {
        list.querySelectorAll(".guestbookNote").forEach(function (n) {
          n.remove();
        });
      }
    }

    return fetch(apiBase() + "list.php?limit=" + PAGE_SIZE + "&offset=" + offset)
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (!data.success) throw new Error(data.error || "load failed");
        var empty = qs("[data-guestbook-empty]");
        var more = qs("[data-guestbook-more]");

        if (!data.items.length && offset === 0) {
          if (empty) empty.hidden = false;
        } else {
          data.items.forEach(function (item) {
            renderItem(item, false);
          });
        }

        offset += data.items.length;
        if (more) more.hidden = !data.hasMore;
      })
      .catch(function () {
        setError(qs("[data-guestbook-error]"), "방명록을 불러오지 못했습니다.");
      });
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

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && sheet.classList.contains("isOpen")) {
        closeWriteSheet();
      }
    });
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
            renderItem(res.data.item, true);
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

  function initListActions() {
    var list = qs("[data-guestbook-list]");
    var modal = qs("[data-guestbook-delete-modal]");
    if (!list || !modal) return;

    if (modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }

    list.addEventListener("click", function (e) {
      var menu = e.target.closest("[data-guestbook-menu]");
      if (menu) {
        var note = menu.closest(".guestbookNote");
        var actions = note && note.querySelector("[data-guestbook-actions]");
        if (actions) actions.hidden = !actions.hidden;
        return;
      }

      var del = e.target.closest("[data-guestbook-delete]");
      if (del) {
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
    });

    function closeDeleteModal() {
      modal.classList.remove("isOpen");
      lockScroll(false);
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
            var node = list.querySelector('[data-guestbook-id="' + deletingId + '"]');
            if (node) {
              node.classList.add("isLeave");
              window.setTimeout(function () {
                node.remove();
                if (!list.querySelector(".guestbookNote")) {
                  var empty = qs("[data-guestbook-empty]");
                  if (empty) empty.hidden = false;
                }
              }, 280);
            }
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
    initForm();
    initListActions();
    loadMessages(true);

    var more = qs("[data-guestbook-more]");
    if (more) {
      more.addEventListener("click", function () {
        loadMessages(false);
      });
    }
  });
})();
