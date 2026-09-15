(function () {
  "use strict";

  var common = window.weddingCommon;

  function initGallery() {
    var viewer = document.querySelector("[data-gallery-viewer]");
    if (!viewer) return;

    var imgEl = viewer.querySelector("[data-gallery-viewer-image]");
    var captionEl = viewer.querySelector("[data-gallery-viewer-caption]");
    var items = Array.from(document.querySelectorAll("[data-gallery-index]"));
    var current = 0;
    var touchStartX = 0;

    function show(index) {
      if (!items.length) return;
      current = (index + items.length) % items.length;
      var item = items[current];
      var src = item.getAttribute("data-gallery-src");
      var label = item.querySelector(".stillFrame__label");
      imgEl.style.opacity = "0";
      imgEl.style.transform = "scale(0.97)";
      requestAnimationFrame(function () {
        imgEl.src = src;
        imgEl.alt = label ? label.textContent : "";
        if (captionEl && label) captionEl.textContent = label.textContent;
        imgEl.style.opacity = "1";
        imgEl.style.transform = "scale(1)";
      });
    }

    function open(index) {
      viewer.hidden = false;
      viewer.setAttribute("aria-hidden", "false");
      if (common) common.lockScroll(true);
      show(index);
    }

    function close() {
      viewer.hidden = true;
      viewer.setAttribute("aria-hidden", "true");
      if (common) common.lockScroll(false);
    }

    items.forEach(function (item) {
      item.addEventListener("click", function () {
        var idx = parseInt(item.getAttribute("data-gallery-index"), 10);
        open(idx);
      });
    });

    viewer.querySelectorAll("[data-gallery-close]").forEach(function (el) {
      el.addEventListener("click", close);
    });

    var prev = viewer.querySelector("[data-gallery-prev]");
    var next = viewer.querySelector("[data-gallery-next]");
    if (prev) prev.addEventListener("click", function () { show(current - 1); });
    if (next) next.addEventListener("click", function () { show(current + 1); });

    document.addEventListener("keydown", function (e) {
      if (viewer.hidden) return;
      if (e.key === "Escape") close();
      if (e.key === "ArrowLeft") show(current - 1);
      if (e.key === "ArrowRight") show(current + 1);
    });

    viewer.addEventListener("touchstart", function (e) {
      touchStartX = e.changedTouches[0].clientX;
    }, { passive: true });

    viewer.addEventListener("touchend", function (e) {
      var dx = e.changedTouches[0].clientX - touchStartX;
      if (Math.abs(dx) < 40) return;
      if (dx < 0) show(current + 1);
      else show(current - 1);
    }, { passive: true });
  }

  document.addEventListener("DOMContentLoaded", initGallery);
})();
