(function () {
  "use strict";

  function initPolaroidFlip() {
    document.querySelectorAll("[data-polaroid-flip]").forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.stopPropagation();
        btn.classList.toggle("isFlipped");
      });
    });
  }

  function initFilmStrip() {
    var track = document.querySelector("[data-film-track]");
    if (!track) return;
    var isDown = false;
    var startX = 0;
    var scrollLeft = 0;

    track.addEventListener("mousedown", function (e) {
      isDown = true;
      startX = e.pageX - track.offsetLeft;
      scrollLeft = track.scrollLeft;
    });
    track.addEventListener("mouseleave", function () {
      isDown = false;
    });
    track.addEventListener("mouseup", function () {
      isDown = false;
    });
    track.addEventListener("mousemove", function (e) {
      if (!isDown) return;
      e.preventDefault();
      var x = e.pageX - track.offsetLeft;
      track.scrollLeft = scrollLeft - (x - startX);
    });
  }

  function initEndingMerge() {
    var ending = document.querySelector("[data-ending-merge]");
    if (!ending) return;

    function onScroll() {
      var rect = ending.getBoundingClientRect();
      var progress = 1 - Math.min(1, Math.max(0, rect.top / (window.innerHeight * 0.85)));
      var g = ending.querySelector("[data-merge-groom]");
      var b = ending.querySelector("[data-merge-bride]");
      if (g && b) {
        g.style.transform = "translateX(" + (1 - progress) * 36 + "px)";
        b.style.transform = "translateX(" + -(1 - progress) * 36 + "px)";
      }
      if (progress > 0.82) {
        ending.classList.add("isMerged");
      }
    }

    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
  }

  function initLetterReveal() {
    var section = document.querySelector("[data-scene='letter']");
    if (!section) return;
    var lines = Array.from(section.querySelectorAll("[data-letter-line]"));
    if (!lines.length) return;

    var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    function reveal() {
      section.classList.add("isLetterVisible");
      if (reduced) {
        lines.forEach(function (line) {
          line.classList.add("isVisible");
        });
        return;
      }
      lines.forEach(function (line, i) {
        setTimeout(function () {
          line.classList.add("isVisible");
        }, i * 160);
      });
    }

    if (!("IntersectionObserver" in window)) {
      reveal();
      return;
    }

    var io = new IntersectionObserver(
      function (entries) {
        if (!entries[0].isIntersecting) return;
        reveal();
        io.disconnect();
      },
      { threshold: 0.35 }
    );
    io.observe(section);
  }

  document.addEventListener("DOMContentLoaded", function () {
    initPolaroidFlip();
    initFilmStrip();
    initEndingMerge();
    initLetterReveal();
  });
})();
