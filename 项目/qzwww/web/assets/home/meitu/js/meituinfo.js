//参数绑定
function setCaseSelectParams() {
  if ($.cookie) {
    $.cookie('case_terminal_params', $('#case_terminal_params').val(), {expires: 7, path: '/'});
  }
  ;
}

$(function () {
  $(".piclistshow li").hover(function () {
        $(this).css("background", "#FAFAFA")
      },
      function () {
        $(this).css("background", "white")
      });
  $(".piclistshow li").last().css("border-bottom", "none");
  $(".piclistshow li").each(function () {
    var curindex = $(this).index(".piclistshow li") + 1;
    if (curindex % 4 == 0) {
      $(this).css({
        "border-right": "none",
        "width": "251px"
      })
    }
  });
  $(document).keydown(function (event) {
    var key = event.keyCode;
    //Left Arrow 37
    if (key == 37) {
      preclick()
    } else {
      //Right Arrow	39
      if (key == 39) {
        nextclick()
      }
    }

  });

  var firstpic = $(".picmidmid ul li").first().find("img");
  var firstsrc = firstpic.attr("bigimg");
  var firsttxt = firstpic.attr("text");
  $("#pic1").attr("src", firstsrc);
  firstpic.addClass("selectpic");
  firstpic.parent().parent().addClass('selectli');
  $(".picshowtxt_right").text(firsttxt);
  $("#preArrow").hover(function () {
        $("#preArrow").css("cursor", "url('/assets/home/meitu/img/cursorleft.ico'),auto");
        //$("#preArrow_A").css("display", "block")
      },
      function () {
        $("#preArrow_A").css("display", "none")
      });
  $("#nextArrow").hover(function () {
        $("#nextArrow").css("cursor", "url('/assets/home/meitu/img/cursorright.ico'),auto");
        //$("#nextArrow_A").css("display", "block")
      },
      function () {
        $("#nextArrow_A").css("display", "none")
      });
  var getli = $(".picmidmid ul li");

  $("#nextArrow_B").click(function () {
    nextclick()
  });
  $("#nextArrow").click(function () {
    nextclick()
  });
  $("#preArrow_B").click(function () {
    preclick()
  });
  $("#preArrow").click(function () {
    preclick()
  });

  function preclick() {
    var currrentindex = parseFloat($(".picmidmid .selectpic").attr("curindex"));
    if (currrentindex != 0) {
      var curli = getli.eq(currrentindex);
      var length = getli.length;
      var picshowtop = $(".picshowtop");
      if (currrentindex <= 2) {
        getli.eq(currrentindex + 9).css("display", "none");
        getli.eq(currrentindex - 1).css("width", "116px").css("display", "block")
      }
      $(".picmidmid .selectpic").removeClass('selectpic');
      curli.removeClass('selectli');
      getli.eq(currrentindex - 1).find("img").addClass("selectpic");
      getli.eq(currrentindex - 1).addClass('selectli');

      picshowtop.find(".bigshow").hide().eq(currrentindex - 1).show();

      var curnextli = getli.eq(currrentindex - 1);
      var curnextsrc = curnextli.find("img").attr("bigimg");
      $("#pic1").attr("src", curnextsrc);
      $("#pic1").attr("curindex", currrentindex - 1);

      $(".picshowtxt_left span").text(currrentindex);
    } else {
      setCaseSelectParams();
      window.location.href = $(".picshowlist_left a").attr("href");
    }
  }

  function nextclick() {
    var currrentindex = parseFloat($(".picmidmid .selectpic").attr("curindex"));
    var length = getli.length;
    var picshowtop = $(".picshowtop");
    if (currrentindex != (length - 1)) {
      var curli = getli.eq(currrentindex);
      if (currrentindex >= 9) {
        getli.eq(currrentindex - 9).css("display", "none");
        getli.eq(currrentindex + 1).css("width", "116px").css("display", "block");
      }
      var curnextli = getli.eq(currrentindex + 1);
      var curnextsrc = curnextli.find("img").attr("bigimg");
      var curnexttxt = curnextli.find("img").attr("text");
      curli.find("img").removeClass("selectpic");
      curli.removeClass("selectli");
      curnextli.find("img").addClass("selectpic");
      curnextli.addClass('selectli');

      picshowtop.find(".bigshow").hide().eq(currrentindex + 1).show();

      $("#pic1").attr("src", curnextsrc);
      $("#pic1").attr("curindex", currrentindex + 1);
      $(".picshowtxt_right").text(curnexttxt);
      $(".picshowtxt_left span").text(currrentindex + 2);
    } else {
      setCaseSelectParams();
      window.location.href = $(".picshowlist_right a").attr("href");
    }
  }

  getli.click(function () {
    var currentliindex = $(this).index(".picmidmid ul li");
    var picshowtop = $(".picshowtop");
    picshowtop.find(".bigshow").hide().eq(currentliindex).show();


    $(".picmidmid ul li img[class='selectpic']").removeClass("selectpic");
    var currentli = getli.eq(currentliindex);
    currentli.find("img").addClass("selectpic");
    var bigimgsrc = currentli.find("img").attr("bigimg");
    var curnexttxt = currentli.find("img").attr("text");
    $("#pic1").attr("src", bigimgsrc);
    $("#pic1").attr("curindex", currentliindex);
    $(".picshowtxt_right").text(curnexttxt);
    $(".picshowtxt_left span").text(currentliindex + 1);
    reSetImg();
  });
  $(".piclistshow li").click(function () {
    var curli = $(this).index(".piclistshow li");
    showgaoqing();
    $(".picmidmid ul li img[class='selectpic']").removeClass("selectpic");
    var currentli = getli.eq(curli);
    currentli.find("img").addClass("selectpic");
    var bigimgsrc = currentli.find("img").attr("bigimg");
    var curnexttxt = currentli.find("img").attr("text");
    $("#pic1").attr("src", bigimgsrc);
    $("#pic1").attr("curindex", curli);
    $(".picshowtxt_right").text(curnexttxt);
    $(".picshowtxt_left span").text(curli + 1);
    $(".picmidmid li").css("display", "block");
    // if (curli >= 5) {
    // 	var cha = curli - 5;
    // 	for (var i = 0; i <= cha; i++) {
    // 		getli.eq(i).css("display", "block")
    // 	}
    // }

  });
  setblock();

  $(".imgbox a:first ").show();

  function reSetImg() {
    var ratio = 0;
    var Img = $(".imgbox a:first ").find("img");
    var width = Img.width();
    var height = Img.height();
    var theImage = new Image();
    theImage.src = Img.attr("src");
    var w = theImage.width;
    var h = theImage.height;

    //$('#imgWidthShow').html('宽度：' + width + ' 高度：' + height + ' 原始宽度：' + w + ' 原始高度：' + h);

    if (width > w) {
      ratio = w / width;
      Img.css("width", w);
      height = height * ratio;
      Img.css("height", height * ratio);
    }

    if (height > h) {
      ratio = h / height;
      Img.css("height", h);
      width = width * ratio;
      Img.css("width", width * ratio);
    }

  }

  function setblock() {
    var left = $(window).width() / 2 - 300;
    $(".firsttop").css("left", left);
    $(".endtop").css("left", left);
    $(".picshowtop").css("width", $(".picshow").width() - 100);
    $(".picshowtop").css("height", $(".picshow").height() - 100);
    reSetImg();
  }

  $(window).resize(function () {
    setblock();
  });

  $(".replaybtn2").click(function () {
    $(".endtop").css("display", "none");
    $(".bodymodal").css("display", "none");
    $(".detail_picbot_mid ul li img[class='selectpic']").removeClass("selectpic");
    $(".detail_picbot_mid ul li").eq(0).find("img").addClass("selectpic");
    var bigimgsrc = $(".detail_picbot_mid ul li").eq(0).find("img").attr("bigimg");
    $("#pic1").attr("src", bigimgsrc);
    $("#pic1").attr("curindex", 0)
  });
  $(".list").click(function () {
    $(".picshow").css("display", "none");
    $(".piclistshow").css("display", "block");
    $(".source_right").css("display", "none");
    $(".source_right1").css("display", "block")
  });
  $(".gaoqing").click(function () {
    showgaoqing();
  });

  function showgaoqing() {
    $(".picshow").css("display", "block");
    $(".piclistshow").css("display", "none");
    $(".source_right").css("display", "block");
    $(".source_right1").css("display", "none")
  }

});