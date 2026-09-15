(function () {
  "use strict";

  var INTRO_KEY = "cinematicIntroSeen";

  function finishIntro(intro) {
    intro.classList.add("isDone");
    sessionStorage.setItem(INTRO_KEY, "1");
    setTimeout(function () {
      intro.remove();
      startHeroHandwrite();
    }, 520);
  }

  function typeHandwriteLine(textEl, caretEl, text, speed) {
    return new Promise(function (resolve) {
      var i = 0;
      if (caretEl) caretEl.hidden = false;
      var timer = setInterval(function () {
        i += 1;
        textEl.textContent = text.slice(0, i);
        if (i >= text.length) {
          clearInterval(timer);
          if (caretEl) caretEl.hidden = true;
          resolve();
        }
      }, speed);
    });
  }

  function startHeroHandwrite() {
    var root = document.querySelector("[data-hero-handwrite]");
    if (!root || root.getAttribute("data-started") === "1") return;
    root.setAttribute("data-started", "1");

    var lines = Array.from(root.querySelectorAll("[data-handwrite-text]"));
    if (!lines.length) return;

    var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (reduced) {
      lines.forEach(function (el) {
        el.textContent = el.getAttribute("data-handwrite-text") || "";
      });
      return;
    }

    var chain = Promise.resolve();
    lines.forEach(function (textEl, index) {
      var text = textEl.getAttribute("data-handwrite-text") || "";
      var caretEl = textEl.parentElement
        ? textEl.parentElement.querySelector("[data-handwrite-caret]")
        : null;
      chain = chain.then(function () {
        return typeHandwriteLine(textEl, caretEl, text, index === 0 ? 95 : 78);
      }).then(function () {
        return new Promise(function (resolve) {
          setTimeout(resolve, index === 0 ? 280 : 0);
        });
      });
    });
  }

  function initHeroHandwrite() {
    var intro = document.querySelector("[data-cinematic-intro]");
    if (!intro || intro.classList.contains("isDone")) {
      // small delay so first paint settles after intro skip/removal
      setTimeout(startHeroHandwrite, 180);
    }
  }

  function runIntro(intro) {
    var lines = Array.from(intro.querySelectorAll("[data-intro-line]"));
    var index = 0;
    var timer = setInterval(function () {
      lines[index].classList.remove("isActive");
      index += 1;
      if (index >= lines.length) {
        clearInterval(timer);
        finishIntro(intro);
        return;
      }
      lines[index].classList.add("isActive");
    }, 480);
    intro._introTimer = timer;
  }

  function updateDDay() {
    var wrap = document.querySelector("[data-wedding-countdown]");
    var app = document.getElementById("filmApp");
    if (!wrap || !app) return;

    var at = app.getAttribute("data-wedding-at");
    if (!at) return;

    var target = new Date(at);
    var now = new Date();
    var ms = target - now;
    var digitsWrap = wrap.querySelector("[data-flip-digits]");
    var digitEls = wrap.querySelectorAll("[data-flip-digit] .flipDigit__face");
    var statusEl = wrap.querySelector("[data-flip-status]");
    var row = wrap.querySelector(".flipClock__row");

    function setDigits(str) {
      var chars = String(str).padStart(3, "0").slice(-3).split("");
      digitEls.forEach(function (el, i) {
        var next = chars[i] || "0";
        if (el.textContent === next) return;
        var digit = el.closest(".flipDigit");
        if (digit) digit.classList.remove("isFlip");
        // force reflow for flip animation
        void digit.offsetWidth;
        el.textContent = next;
        if (digit) digit.classList.add("isFlip");
      });
    }

    if (ms <= 0) {
      if (row) row.hidden = true;
      if (statusEl) {
        statusEl.hidden = false;
        statusEl.textContent = ms < -86400000 ? "WE GOT MARRIED" : "TODAY";
      }
      return;
    }

    if (row) row.hidden = false;
    if (statusEl) statusEl.hidden = true;

    var days = Math.ceil(ms / 86400000);
    if (digitsWrap && digitEls.length) {
      // grow digit count if needed (4+ digits rare but possible)
      var dayStr = String(days);
      if (dayStr.length > digitEls.length) {
        var needed = dayStr.length - digitEls.length;
        for (var i = 0; i < needed; i++) {
          var span = document.createElement("span");
          span.className = "flipDigit";
          span.setAttribute("data-flip-digit", "");
          span.innerHTML = '<span class="flipDigit__face">0</span>';
          digitsWrap.insertBefore(span, digitsWrap.firstChild);
        }
        digitEls = wrap.querySelectorAll("[data-flip-digit] .flipDigit__face");
      }
      var padded = dayStr.padStart(Math.max(3, dayStr.length), "0");
      digitEls.forEach(function (el, i) {
        var offset = digitEls.length - padded.length;
        var next = i < offset ? "0" : padded[i - offset];
        if (el.textContent === next) return;
        var digit = el.closest(".flipDigit");
        if (digit) {
          digit.classList.remove("isFlip");
          void digit.offsetWidth;
          digit.classList.add("isFlip");
        }
        el.textContent = next;
      });
    }
  }

  function initScrollCinematic() {
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      document.querySelectorAll("[data-couple-panel]").forEach(function (el) {
        el.classList.add("isVisible");
      });
      document.querySelectorAll("[data-letter-line]").forEach(function (el) {
        el.classList.add("isVisible");
      });
      return;
    }

    if (typeof gsap === "undefined" || typeof ScrollTrigger === "undefined") {
      return;
    }

    gsap.registerPlugin(ScrollTrigger);

    var heroImg = document.querySelector(".heroStill__img");
    if (heroImg) {
      gsap.to(heroImg, {
        scale: 1,
        scrollTrigger: {
          trigger: ".scene--hero",
          start: "top top",
          end: "bottom top",
          scrub: 0.6,
        },
      });
    }

    gsap.utils.toArray("[data-couple-panel]").forEach(function (panel) {
      gsap.to(panel, {
        scrollTrigger: {
          trigger: panel,
          start: "top 85%",
          onEnter: function () {
            panel.classList.add("isVisible");
          },
        },
      });
    });

    // Date scene progressive stages handled in scenes.js
    return;
  }

  document.addEventListener("DOMContentLoaded", function () {
    var intro = document.querySelector("[data-cinematic-intro]");
    if (intro) {
      if (sessionStorage.getItem(INTRO_KEY) === "1") {
        intro.remove();
        initHeroHandwrite();
      } else {
        runIntro(intro);
        intro.addEventListener("click", function (e) {
          if (intro._introTimer) clearInterval(intro._introTimer);
          finishIntro(intro);
        });
        var skip = document.querySelector("[data-intro-skip]");
        if (skip) {
          skip.addEventListener("click", function (e) {
            e.stopPropagation();
            if (intro._introTimer) clearInterval(intro._introTimer);
            finishIntro(intro);
          });
        }
      }
    } else {
      initHeroHandwrite();
    }

    updateDDay();
    setInterval(updateDDay, 60000);
    initScrollCinematic();
  });
})();
