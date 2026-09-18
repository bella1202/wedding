(function () {
  "use strict";

  var MAX_FILES = 8;

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

  function revokePreviewUrls(preview) {
    if (!preview) return;
    Array.prototype.forEach.call(preview.querySelectorAll("[data-preview-url]"), function (el) {
      var url = el.getAttribute("data-preview-url");
      if (url) URL.revokeObjectURL(url);
    });
  }

  function syncInputFiles(fileInput, files) {
    if (!fileInput || typeof DataTransfer === "undefined") return;
    var dt = new DataTransfer();
    files.forEach(function (file) {
      dt.items.add(file);
    });
    fileInput.files = dt.files;
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
    var selectedFiles = [];

    function renderPreview() {
      if (!preview || !fileInput) return;
      revokePreviewUrls(preview);
      preview.innerHTML = "";

      if (!selectedFiles.length) {
        preview.hidden = true;
        syncInputFiles(fileInput, []);
        return;
      }

      preview.hidden = false;
      selectedFiles.forEach(function (file, index) {
        var li = document.createElement("li");
        li.className = "guestSnapDrop__thumb";

        var url = URL.createObjectURL(file);
        var isVideo = file.type.indexOf("video/") === 0;
        var media;

        if (isVideo) {
          media = document.createElement("video");
          media.src = url;
          media.muted = true;
          media.playsInline = true;
          media.preload = "metadata";
          media.setAttribute("data-preview-url", url);
          li.appendChild(media);

          var badge = document.createElement("span");
          badge.className = "guestSnapDrop__videoBadge";
          badge.setAttribute("aria-hidden", "true");
          badge.innerHTML =
            '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M8 6.8v10.4L18 12 8 6.8z"/></svg>';
          li.appendChild(badge);
        } else {
          media = document.createElement("img");
          media.src = url;
          media.alt = "";
          media.setAttribute("data-preview-url", url);
          li.appendChild(media);
        }

        var removeBtn = document.createElement("button");
        removeBtn.type = "button";
        removeBtn.className = "guestSnapDrop__remove touchBtn";
        removeBtn.setAttribute("aria-label", "삭제");
        removeBtn.innerHTML =
          '<svg viewBox="0 0 24 24" width="8" height="8" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>';
        removeBtn.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();
          selectedFiles.splice(index, 1);
          syncInputFiles(fileInput, selectedFiles);
          renderPreview();
        });
        li.appendChild(removeBtn);
        preview.appendChild(li);
      });

      syncInputFiles(fileInput, selectedFiles);
    }

    if (fileInput && preview) {
      fileInput.addEventListener("change", function () {
        var incoming = Array.from(fileInput.files || []);
        if (!incoming.length) return;

        incoming.forEach(function (file) {
          if (selectedFiles.length >= MAX_FILES) return;
          selectedFiles.push(file);
        });

        if (incoming.length && selectedFiles.length >= MAX_FILES) {
          setError(errorEl, "최대 " + MAX_FILES + "개까지 업로드할 수 있어요.");
        } else {
          setError(errorEl, "");
        }

        renderPreview();
      });
    }

    if (!form) return;

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      setError(errorEl, "");

      if (!canUpload) {
        setError(errorEl, "아직 업로드 오픈 전이에요.");
        return;
      }

      var nameInput = form.querySelector('input[name="name"]');
      var name = nameInput ? String(nameInput.value || "").trim() : "";
      if (!name || name.length > 20) {
        setError(errorEl, "성함은 1~20자로 입력해주세요.");
        return;
      }

      if (!selectedFiles.length) {
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
