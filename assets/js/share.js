(function () {
  "use strict";

  function ensureKakaoInit(appKey) {
    if (typeof Kakao === "undefined" || !Kakao.init) {
      return false;
    }
    if (!Kakao.isInitialized()) {
      Kakao.init(appKey);
    }
    return Kakao.isInitialized();
  }

  function shareViaKakao(templateId, appKey) {
    if (!ensureKakaoInit(appKey)) {
      return Promise.reject(new Error("Kakao SDK not ready"));
    }
    if (!Kakao.Share || typeof Kakao.Share.sendCustom !== "function") {
      return Promise.reject(new Error("Kakao.Share unavailable"));
    }

    try {
      Kakao.Share.sendCustom({
        templateId: Number(templateId),
      });
      return Promise.resolve();
    } catch (err) {
      return Promise.reject(err);
    }
  }

  function shareViaWeb(title, url) {
    if (!navigator.share) {
      return Promise.reject(new Error("Web Share unavailable"));
    }
    return navigator.share({ title: title, url: url });
  }

  function copyLink(btn, url) {
    var original = btn.textContent;
    function flash(msg) {
      btn.textContent = msg;
      setTimeout(function () {
        btn.textContent = original || "공유하기";
      }, 1600);
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
      return navigator.clipboard.writeText(url).then(function () {
        flash("링크가 복사되었습니다");
      });
    }

    return new Promise(function (resolve, reject) {
      var input = document.createElement("input");
      input.value = url;
      input.setAttribute("readonly", "");
      input.style.position = "fixed";
      input.style.opacity = "0";
      document.body.appendChild(input);
      input.select();
      try {
        var ok = document.execCommand("copy");
        document.body.removeChild(input);
        if (!ok) {
          reject(new Error("copy failed"));
          return;
        }
        flash("링크가 복사되었습니다");
        resolve();
      } catch (err) {
        document.body.removeChild(input);
        reject(err);
      }
    });
  }

  function waitForKakaoShareSdk(timeoutMs) {
    if (typeof Kakao !== "undefined" && Kakao.init) {
      return Promise.resolve();
    }
    if (!window.__weddingKakaoShareReady) {
      return Promise.reject(new Error("Kakao share not configured"));
    }
    return Promise.race([
      window.__weddingKakaoShareReady.then(function (err) {
        if (err) throw err;
      }),
      new Promise(function (_, reject) {
        setTimeout(function () {
          reject(new Error("Kakao share SDK timeout"));
        }, timeoutMs || 4000);
      }),
    ]);
  }

  function initShare() {
    var config = window.__weddingKakaoShare || {};
    var templateId = config.templateId;
    var appKey = config.appKey || "";

    document.querySelectorAll("[data-web-share]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var title = btn.getAttribute("data-share-title") || document.title;
        var url = window.location.href.split("#")[0].split("?")[0];
        var btnTemplateId =
          btn.getAttribute("data-kakao-template-id") || templateId;

        var chain = Promise.resolve();

        if (btnTemplateId && appKey) {
          chain = waitForKakaoShareSdk(4000).then(function () {
            return shareViaKakao(btnTemplateId, appKey);
          });
        } else {
          chain = Promise.reject(new Error("no kakao template"));
        }

        chain.catch(function () {
          return shareViaWeb(title, url);
        }).catch(function () {
          return copyLink(btn, url);
        }).catch(function () {
          btn.textContent = "공유에 실패했습니다";
          setTimeout(function () {
            btn.textContent = "공유하기";
          }, 1600);
        });
      });
    });
  }

  document.addEventListener("DOMContentLoaded", initShare);
})();
