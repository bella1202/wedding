(function () {
  "use strict";

  var isDev =
    location.hostname === "127.0.0.1" ||
    location.hostname === "localhost";

  function debug() {
    if (!isDev || !window.console) return;
    console.log.apply(console, ["[wedding-map]"].concat([].slice.call(arguments)));
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function showFallback(root, message) {
    var canvas = root.querySelector("[data-kakao-map-canvas]");
    var fallback = root.querySelector("[data-kakao-map-fallback]");
    var hint = root.querySelector("[data-kakao-map-hint]");
    if (canvas) canvas.hidden = true;
    if (fallback) fallback.hidden = false;
    if (hint && message) hint.textContent = message;
    root.classList.add("isFallback");
    debug("fallback:", message || "unknown");
  }

  function waitForKakao(timeoutMs) {
    return new Promise(function (resolve, reject) {
      function ready() {
        if (
          typeof kakao !== "undefined" &&
          kakao.maps &&
          typeof kakao.maps.load === "function"
        ) {
          resolve();
          return true;
        }
        return false;
      }

      if (ready()) return;

      if (window.__weddingKakaoReady) {
        window.__weddingKakaoReady.then(function (err) {
          if (err) {
            reject(err instanceof Error ? err : new Error(String(err)));
            return;
          }
          if (ready()) return;
          reject(new Error("Kakao object missing after script load"));
        }).catch(reject);
      }

      var started = Date.now();
      var timer = setInterval(function () {
        if (ready()) {
          clearInterval(timer);
        } else if (Date.now() - started > timeoutMs) {
          clearInterval(timer);
          reject(new Error("Kakao SDK timeout"));
        }
      }, 50);
    });
  }

  function renderMap(root) {
    var lat = parseFloat(root.getAttribute("data-lat") || "");
    var lng = parseFloat(root.getAttribute("data-lng") || "");
    var canvas = root.querySelector("[data-kakao-map-canvas]");

    if (!canvas || Number.isNaN(lat) || Number.isNaN(lng)) {
      showFallback(root, "지도를 불러오지 못했습니다.");
      return;
    }

    debug("init", { lat: lat, lng: lng, hasKakao: typeof kakao !== "undefined" });

    kakao.maps.load(function () {
      try {
        canvas.hidden = false;
        var fallback = root.querySelector("[data-kakao-map-fallback]");
        if (fallback) fallback.hidden = true;
        root.classList.remove("isFallback");

        // Force layout before Kakao measures container
        canvas.style.width = "100%";
        canvas.style.height = "100%";
        void canvas.offsetHeight;

        var center = new kakao.maps.LatLng(lat, lng);
        var map = new kakao.maps.Map(canvas, {
          center: center,
          level: 5, // 한 단계 축소 (숫자가 클수록 멀리 보임)
          // 모바일에서 페이지 세로 스크롤이 지도에 먹히지 않도록
          draggable: false,
          scrollwheel: false,
          disableDoubleClickZoom: true,
          keyboardShortcuts: false,
        });

        // 지도 위를 한 번 길게 누르거나 더블탭하면 이동 가능 (선택적)
        var mapUnlocked = false;
        function unlockMapDrag() {
          if (mapUnlocked) return;
          mapUnlocked = true;
          map.setDraggable(true);
          root.classList.add("isMapInteractive");
        }
        kakao.maps.event.addListener(map, "click", unlockMapDrag);

        new kakao.maps.Marker({
          position: center,
          map: map,
          zIndex: 4,
        });

        function relayout() {
          map.relayout();
          map.setCenter(center);
        }

        setTimeout(relayout, 80);
        kakao.maps.event.addListener(map, "tilesloaded", relayout);

        if ("IntersectionObserver" in window) {
          var seen = false;
          var io = new IntersectionObserver(
            function (entries) {
              if (!entries[0].isIntersecting) return;
              relayout();
              if (!seen) {
                seen = true;
                debug("visible + relayout");
              }
            },
            { threshold: 0.15 }
          );
          io.observe(root);
        }

        window.addEventListener("resize", relayout);
        root._kakaoMap = map;
        debug("init success");
      } catch (err) {
        debug("init error", err && err.message);
        showFallback(root, "지도를 불러오지 못했습니다.");
      }
    });
  }

  function initKakaoMap() {
    var root = document.querySelector("[data-kakao-map]");
    if (!root) return;

    var hasKey = root.getAttribute("data-has-key") === "1";
    debug("hasKey", hasKey);

    if (!hasKey) {
      showFallback(root, "지도를 불러오지 못했습니다.");
      return;
    }

    waitForKakao(6000)
      .then(function () {
        debug("sdk ready");
        renderMap(root);
      })
      .catch(function (err) {
        debug("sdk wait failed", err && err.message);
        var host = location.hostname || "test.local";
        var msg = "지도를 불러오지 못했습니다.";
        if (isDev) {
          msg =
            "카카오 웹 도메인 미등록: Developers → 앱 설정 → 플랫폼 → Web에 「" +
            host +
            "」를 추가하세요.";
        }
        showFallback(root, msg);
      });
  }

  document.addEventListener("DOMContentLoaded", initKakaoMap);
})();
