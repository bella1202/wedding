(function () {
  "use strict";

  var common = window.weddingCommon;

  function initGallery() {
    var root = document.querySelector("[data-gallery]");
    var viewer = document.querySelector("[data-gallery-viewer]");
    if (!root || !viewer) return;

    var photos;
    try {
      photos = JSON.parse(root.getAttribute("data-gallery-items") || "[]");
    } catch (_) {
      photos = [];
    }
    if (!photos.length) return;

    var featureBtn = root.querySelector("[data-gallery-feature]");
    var featureImg = root.querySelector("[data-gallery-feature-image]");
    var stripTrack = root.querySelector("[data-gallery-strip]");
    var imgEl = viewer.querySelector("[data-gallery-viewer-image]");
    var captionEl = viewer.querySelector("[data-gallery-viewer-caption]");
    var featureId = photos[0].id;
    var viewerIndex = 0;
    var touchStartX = 0;

    function photoById(id) {
      for (var i = 0; i < photos.length; i++) {
        if (photos[i].id === id) return photos[i];
      }
      return null;
    }

    function indexById(id) {
      for (var i = 0; i < photos.length; i++) {
        if (photos[i].id === id) return i;
      }
      return 0;
    }

    function renderFeature() {
      var photo = photoById(featureId);
      if (!photo || !featureBtn || !featureImg) return;

      featureBtn.setAttribute("data-gallery-id", String(photo.id));
      featureBtn.setAttribute("aria-label", photo.caption + " 크게 보기");
      featureImg.src = photo.src;
      featureImg.alt = "";
    }

    function renderStrip() {
      if (!stripTrack) return;
      stripTrack.replaceChildren();

      photos.forEach(function (photo) {
        if (photo.id === featureId) return;

        var button = document.createElement("button");
        button.type = "button";
        button.className = "filmStrip__frame touchBtn";
        button.setAttribute("data-gallery-strip-item", "");
        button.setAttribute("data-gallery-id", String(photo.id));
        button.setAttribute("aria-label", photo.caption + " 선택");

        var image = document.createElement("img");
        image.src = photo.src;
        image.alt = "";
        image.loading = "lazy";

        button.append(image);
        button.addEventListener("click", function () {
          promoteToFeature(photo.id);
        });
        stripTrack.append(button);
      });
    }

    function promoteToFeature(id) {
      if (id === featureId || !photoById(id)) return;
      featureId = id;
      renderFeature();
      renderStrip();
    }

    function showViewer(index) {
      viewerIndex = (index + photos.length) % photos.length;
      var photo = photos[viewerIndex];
      imgEl.style.opacity = "0";
      imgEl.style.transform = "scale(0.97)";
      requestAnimationFrame(function () {
        imgEl.src = photo.src;
        imgEl.alt = "";
        if (captionEl) captionEl.textContent = "";
        imgEl.style.opacity = "1";
        imgEl.style.transform = "scale(1)";
      });
    }

    function openViewer(index) {
      viewer.hidden = false;
      viewer.setAttribute("aria-hidden", "false");
      if (common) common.lockScroll(true);
      showViewer(index);
    }

    function closeViewer() {
      viewer.hidden = true;
      viewer.setAttribute("aria-hidden", "true");
      if (common) common.lockScroll(false);
    }

    if (featureBtn) {
      featureBtn.addEventListener("click", function () {
        openViewer(indexById(featureId));
      });
    }

    root.querySelectorAll("[data-gallery-strip-item]").forEach(function (item) {
      item.addEventListener("click", function () {
        var id = parseInt(item.getAttribute("data-gallery-id"), 10);
        promoteToFeature(id);
      });
    });

    viewer.querySelectorAll("[data-gallery-close]").forEach(function (el) {
      el.addEventListener("click", closeViewer);
    });

    var prev = viewer.querySelector("[data-gallery-prev]");
    var next = viewer.querySelector("[data-gallery-next]");
    if (prev) prev.addEventListener("click", function () { showViewer(viewerIndex - 1); });
    if (next) next.addEventListener("click", function () { showViewer(viewerIndex + 1); });

    document.addEventListener("keydown", function (e) {
      if (viewer.hidden) return;
      if (e.key === "Escape") closeViewer();
      if (e.key === "ArrowLeft") showViewer(viewerIndex - 1);
      if (e.key === "ArrowRight") showViewer(viewerIndex + 1);
    });

    viewer.addEventListener("touchstart", function (e) {
      touchStartX = e.changedTouches[0].clientX;
    }, { passive: true });

    viewer.addEventListener("touchend", function (e) {
      var dx = e.changedTouches[0].clientX - touchStartX;
      if (Math.abs(dx) < 40) return;
      if (dx < 0) showViewer(viewerIndex + 1);
      else showViewer(viewerIndex - 1);
    }, { passive: true });
  }

  document.addEventListener("DOMContentLoaded", initGallery);
})();
