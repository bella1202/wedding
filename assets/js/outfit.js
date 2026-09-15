(function () {
  "use strict";

  var api = {
    setState: function () {},
  };

  function initFloatingCouple() {
    var root = document.querySelector("[data-floating-couple]");
    if (!root) return;

    var layers = Array.from(root.querySelectorAll("[data-outfit-layer]"));
    var currentStage = "";

    function setStage(stage) {
      if (!stage || stage === currentStage) return;
      currentStage = stage;

      layers.forEach(function (layer) {
        var key = layer.getAttribute("data-outfit-layer");
        layer.classList.toggle("isActive", key === stage);
      });

      root.className = root.className
        .split(/\s+/)
        .filter(function (c) {
          return c && c.indexOf("isStage") !== 0;
        })
        .join(" ");

      var stageClass =
        "isStage" + stage.charAt(0).toUpperCase() + stage.slice(1);
      root.classList.add(stageClass);
    }

    api.setState = setStage;
    setStage("casual");
  }

  window.weddingCouple = api;

  document.addEventListener("DOMContentLoaded", initFloatingCouple);
})();
