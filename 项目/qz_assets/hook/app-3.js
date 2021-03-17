"v0.0.1 app webview hook";
;(function (window) {
  var gconfigH5Sheji = "https://h5.qizuang.com/sheji?from=app&source=qz";
  var gconfigTel = "4006969336";
  var classBottom = "leftbottom";
  var timer = setInterval(function () {
    if (document.getElementsByClassName(classBottom)[0]) {
      var leftbottom = document.getElementsByClassName(classBottom)[0];
      leftbottom.onclick = function () {
        var dialogMake = document.getElementsByClassName("make")[0];
        var dialogTel = document.getElementsByClassName("tel")[0];
        var dialogIetm = document.getElementsByClassName("ietm")[2].getElementsByTagName("p")[1];
        dialogMake.onclick = function () {
          window.open(gconfigH5Sheji, "_self");
        };
        dialogTel.onclick = function () {
          window.open("tel:" + gconfigTel, "_self");
        };
        dialogIetm.replaceWith(gconfigTel);
      };
      clearInterval(timer);
    }
  }, 1000);
})(window);
