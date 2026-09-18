(function () {
  "use strict";

  function initSceneReveals() {
    var scenes = Array.from(document.querySelectorAll(".scene[data-scene]"));
    if (!scenes.length || !("IntersectionObserver" in window)) {
      scenes.forEach(function (s) {
        s.classList.add("isInView");
      });
      return;
    }

    var reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("isInView");
          }
        });
      },
      {
        threshold: reduced ? 0.05 : 0.28,
        rootMargin: "0px 0px -8% 0px",
      }
    );

    scenes.forEach(function (scene) {
      io.observe(scene);
    });
  }

  function initDateSceneStages() {
    var section = document.querySelector("[data-scene='weddingDate']");
    if (!section) return;

    var calendar = section.querySelector('[data-date-stage="calendar"]');
    var countdown = section.querySelector('[data-date-stage="countdown"]');
    var hero = section.querySelector('[data-date-stage="hero"]');

    function update() {
      var rect = section.getBoundingClientRect();
      var view = window.innerHeight || 1;
      // 0 when section top at bottom of viewport, 1 when section mostly scrolled through
      var progress = Math.min(1, Math.max(0, (view - rect.top) / (view + rect.height * 0.35)));

      if (hero) {
        hero.classList.toggle("isActive", progress > 0.12);
      }
      if (calendar) {
        calendar.classList.toggle("isActive", progress > 0.28);
      }
      if (countdown) {
        countdown.classList.toggle("isActive", progress > 0.52);
      }

      // soft exit toward gallery
      section.classList.toggle("isExiting", progress > 0.88);
    }

    var ticking = false;
    window.addEventListener(
      "scroll",
      function () {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(function () {
          ticking = false;
          update();
        });
      },
      { passive: true }
    );
    update();
  }

  function initCoupleBySection() {
    if (typeof window.weddingCouple === "undefined") return;

    var coupleRoot = document.querySelector("[data-floating-couple]");
    var menuRoot = document.querySelector("[data-quick-nav]");
    var nodes = Array.from(document.querySelectorAll("[data-couple-state]"));
    if (!nodes.length || !("IntersectionObserver" in window)) return;

    var order = { casual: 0, date: 1, wedding: 2, heart: 3, ending: 4 };
    var ratios = new Map();

    function setPostHeroChrome(visible) {
      if (coupleRoot) coupleRoot.classList.toggle("isHidden", !visible);
      if (menuRoot) {
        menuRoot.classList.toggle("isHidden", !visible);
        // 히어로로 돌아갈 때 열린 메뉴는 닫기
        if (!visible && menuRoot.classList.contains("isOpen")) {
          menuRoot.classList.remove("isOpen");
          document.body.classList.remove("isMenuOpen");
          var toggle = menuRoot.querySelector("[data-menu-toggle]");
          var panel = menuRoot.querySelector("[data-menu-panel]");
          if (toggle) {
            toggle.setAttribute("aria-expanded", "false");
            toggle.setAttribute("aria-label", "메뉴 열기");
          }
          if (panel) panel.setAttribute("aria-hidden", "true");
        }
      }
      if (visible) {
        document.dispatchEvent(new CustomEvent("wedding:post-hero"));
      }
    }

    function pickActive() {
      var best = null;
      var bestScore = -1;

      ratios.forEach(function (ratio, el) {
        if (ratio < 0.18) return;
        var state = el.getAttribute("data-couple-state") || "";
        var rank = order[state];
        if (typeof rank !== "number") rank = 0;
        var score = ratio * 10 + rank * 0.01;
        if (score > bestScore) {
          bestScore = score;
          best = el;
        }
      });

      // 첫 화면(hero)에서는 숨기고, 다음 섹션부터 일러스트·메뉴 표시
      if (!best) {
        setPostHeroChrome(false);
        return;
      }

      setPostHeroChrome(true);
      var state = best.getAttribute("data-couple-state");
      if (state) window.weddingCouple.setState(state);
    }

    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          ratios.set(entry.target, entry.isIntersecting ? entry.intersectionRatio : 0);
        });
        pickActive();
      },
      {
        threshold: [0.18, 0.35, 0.55, 0.75],
        rootMargin: "-10% 0px -15% 0px",
      }
    );

    nodes.forEach(function (n) {
      io.observe(n);
    });

    setPostHeroChrome(false);
  }

  function initQuickNav() {
    var nav = document.querySelector("[data-quick-nav]");
    if (!nav) return;

    var toggle = nav.querySelector("[data-menu-toggle]");
    var backdrop = nav.querySelector("[data-menu-close]");
    var panel = nav.querySelector("[data-menu-panel]");
    var buttons = Array.from(nav.querySelectorAll("[data-nav-target]"));
    var links = Array.from(nav.querySelectorAll("[data-nav-href]"));

    function isOpen() {
      return nav.classList.contains("isOpen");
    }

    function openMenu() {
      nav.classList.add("isOpen");
      if (panel) panel.setAttribute("aria-hidden", "false");
      if (toggle) {
        toggle.setAttribute("aria-expanded", "true");
        toggle.setAttribute("aria-label", "메뉴 닫기");
      }
      document.body.classList.add("isMenuOpen");
    }

    function closeMenu() {
      nav.classList.remove("isOpen");
      if (panel) panel.setAttribute("aria-hidden", "true");
      if (toggle) {
        toggle.setAttribute("aria-expanded", "false");
        toggle.setAttribute("aria-label", "메뉴 열기");
      }
      document.body.classList.remove("isMenuOpen");
    }

    function toggleMenu() {
      if (isOpen()) closeMenu();
      else openMenu();
    }

    if (toggle) {
      toggle.addEventListener("click", function (e) {
        e.stopPropagation();
        toggleMenu();
      });
    }

    if (backdrop) {
      backdrop.addEventListener("click", closeMenu);
    }

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && isOpen()) closeMenu();
    });

    function setActive(id) {
      buttons.forEach(function (btn) {
        btn.classList.toggle(
          "isActive",
          !!id && btn.getAttribute("data-nav-target") === id
        );
      });
      links.forEach(function (link) {
        link.classList.remove("isActive");
      });
    }

    buttons.forEach(function (btn) {
      btn.addEventListener("click", function () {
        var id = btn.getAttribute("data-nav-target");
        var el =
          document.getElementById(id) ||
          document.querySelector('[data-scene="' + id + '"]');
        closeMenu();
        if (!el) return;
        setActive(id);
        window.setTimeout(function () {
          el.scrollIntoView({ behavior: "smooth", block: "start" });
        }, 80);
      });
    });

    links.forEach(function (link) {
      link.addEventListener("click", function () {
        closeMenu();
      });
    });

    var sectionIds = [
      "invitation",
      "weddingDate",
      "gallery",
      "location",
      "guestbook",
    ];
    var sections = sectionIds
      .map(function (id) {
        return (
          document.getElementById(id) ||
          document.querySelector('[data-scene="' + id + '"]')
        );
      })
      .filter(Boolean);

    var hero = document.querySelector('[data-scene="hero"]');
    if (hero) sections.push(hero);

    if (!sections.length || !("IntersectionObserver" in window)) return;

    var ratios = new Map();
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          ratios.set(entry.target, entry.isIntersecting ? entry.intersectionRatio : 0);
        });

        var best = null;
        var bestRatio = 0;
        ratios.forEach(function (ratio, el) {
          if (ratio > bestRatio) {
            bestRatio = ratio;
            best = el;
          }
        });
        if (!best || bestRatio < 0.2) return;

        if (best.getAttribute("data-scene") === "hero") {
          setActive("");
          return;
        }

        var bestId = best.id || best.getAttribute("data-scene");
        // 계좌/공유는 방명록 구간에 이어지므로 같은 메뉴 활성화
        if (
          bestId === "account" ||
          best.getAttribute("data-scene") === "account" ||
          best.getAttribute("data-scene") === "share" ||
          best.getAttribute("data-scene") === "guestSnap"
        ) {
          setActive("guestbook");
          return;
        }

        var matched = sectionIds.find(function (id) {
          return best.id === id || best.getAttribute("data-scene") === id;
        });
        if (matched) setActive(matched);
      },
      {
        threshold: [0.2, 0.4, 0.6],
        rootMargin: "-20% 0px -35% 0px",
      }
    );

    // 계좌·공유·게스트스냅 소개도 관찰해서 마음 전하기 메뉴 활성화 유지
    ["account", "share", "guestSnap"].forEach(function (id) {
      var el =
        document.getElementById(id) ||
        document.querySelector('[data-scene="' + id + '"]');
      if (el) sections.push(el);
    });

    sections.forEach(function (section) {
      io.observe(section);
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    initSceneReveals();
    initDateSceneStages();
    initCoupleBySection();
    initQuickNav();
  });
})();
