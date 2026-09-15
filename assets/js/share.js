(function () {
  "use strict";

  function initShare() {
    document.querySelectorAll("[data-web-share]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var title = btn.getAttribute("data-share-title") || document.title;
        var url = window.location.href.split("?")[0];

        if (navigator.share) {
          navigator.share({ title: title, url: url }).catch(function () {});
          return;
        }

        var copyText = url;
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(copyText).then(function () {
            btn.textContent = "링크가 복사되었습니다";
            setTimeout(function () {
                btn.textContent = "공유하기";
            }, 1500);
          });
        }
      });
    });
  }

  document.addEventListener("DOMContentLoaded", initShare);
})();
