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

  function pad2(n) {
    return String(Math.max(0, n)).padStart(2, "0");
  }

  function setCountdownText(el, next) {
    if (!el || el.textContent === next) return;
    el.textContent = next;
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
    var units = wrap.querySelector("[data-countdown-units]");
    var caption = wrap.querySelector("[data-countdown-caption]");
    var statusEl = wrap.querySelector("[data-countdown-status]");
    var daysEl = wrap.querySelector('[data-countdown="days"]');
    var hoursEl = wrap.querySelector('[data-countdown="hours"]');
    var minsEl = wrap.querySelector('[data-countdown="mins"]');
    var secsEl = wrap.querySelector('[data-countdown="secs"]');
    var daysTextEl = wrap.querySelector("[data-countdown-days-text]");

    if (ms <= 0) {
      if (units) units.hidden = true;
      if (caption) caption.hidden = true;
      if (statusEl) {
        statusEl.hidden = false;
        statusEl.textContent = ms < -86400000 ? "결혼식을 올렸습니다" : "오늘은 결혼식 날입니다";
      }
      return;
    }

    if (units) units.hidden = false;
    if (caption) caption.hidden = false;
    if (statusEl) statusEl.hidden = true;

    var totalSec = Math.floor(ms / 1000);
    var days = Math.floor(totalSec / 86400);
    var hours = Math.floor((totalSec % 86400) / 3600);
    var mins = Math.floor((totalSec % 3600) / 60);
    var secs = totalSec % 60;
    var remainDays = Math.max(1, Math.ceil(ms / 86400000));

    setCountdownText(daysEl, pad2(days));
    setCountdownText(hoursEl, pad2(hours));
    setCountdownText(minsEl, pad2(mins));
    setCountdownText(secsEl, pad2(secs));
    setCountdownText(daysTextEl, String(remainDays));
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
    setInterval(updateDDay, 1000);
    initScrollCinematic();
  });
})();
