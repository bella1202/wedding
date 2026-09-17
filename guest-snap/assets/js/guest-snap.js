(function () {
  "use strict";

  function qs(sel, root) {
    return (root || document).querySelector(sel);
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

  function initGuestSnap() {
    var root = qs("[data-guest-snap]");
    if (!root) return;

    var canUpload = root.getAttribute("data-can-upload") === "1";
    var form = qs("[data-guest-snap-form]", root);
    var fileInput = qs("[data-guest-snap-files]", root);
    var preview = qs("[data-guest-snap-preview]", root);
    var errorEl = qs("[data-guest-snap-error]", root);
    var submitBtn = qs("[data-guest-snap-submit]", root);

    if (fileInput && preview) {
      fileInput.addEventListener("change", function () {
        var files = Array.from(fileInput.files || []);
        preview.innerHTML = "";
        if (!files.length) {
          preview.hidden = true;
          return;
        }
        preview.hidden = false;
        files.slice(0, 8).forEach(function (file) {
          var li = document.createElement("li");
          li.textContent = file.name;
          preview.appendChild(li);
        });
        if (files.length > 8) {
          var more = document.createElement("li");
          more.textContent = "외 " + (files.length - 8) + "개";
          preview.appendChild(more);
        }
      });
    }

    if (!form) return;

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      setError(errorEl, "");

      if (!canUpload) {
        setError(errorEl, "예식 당일부터 업로드할 수 있어요.");
        return;
      }

      var nameInput = form.querySelector('input[name="name"]');
      var name = nameInput ? String(nameInput.value || "").trim() : "";
      if (!name || name.length > 20) {
        setError(errorEl, "성함은 1~20자로 입력해주세요.");
        return;
      }

      var files = fileInput ? Array.from(fileInput.files || []) : [];
      if (!files.length) {
        setError(errorEl, "사진 또는 영상을 선택해주세요.");
        return;
      }

      // Placeholder: Google Drive API upload will be wired here later.
      if (submitBtn) submitBtn.disabled = true;
      window.setTimeout(function () {
        setError(
          errorEl,
          "업로드 연동은 준비 중이에요. 예식 당일 Google Drive 업로드가 연결됩니다."
        );
        if (submitBtn) submitBtn.disabled = false;
      }, 400);
    });
  }

  document.addEventListener("DOMContentLoaded", initGuestSnap);
})();
