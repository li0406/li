

$(function(){
  $(".search-box ul li").click(function(event) {
      $(".options em").html($(this).html());
      $(".search-box input[type=text]").attr("placeholder", $(this).attr("placeholder"));
      var url = $(this).attr("target");
      $(".search-form").attr("action",url);
      $(".search-box ul li").first().html($(this).html());
  });

  $(".search-box button").click(function(event) {
      var url = $(".search-form").attr("action");
      var keyword = $(".search-box input[name=keyword]").val();
      url += "?keyword="+keyword;
      window.open(url);
  });

  $(".pub-nav ul li").removeClass('active');
  $(".pub-nav ul li").eq(tabIndex).addClass('active');

  $(".pub-city").hover(function() {
      $(this).addClass('active');
  }, function() {
      $(this).removeClass('active');
  });

  $(".loginout").click(function(event) {
    var _this = $(this);
      $.ajax({
          url: '/loginout/',
          type: 'GET',
          dataType: 'JSON',
          data: {
            ssid:"{$ssid}"
          }
      })
      .done(function(data) {
          if(data.status == 1){
              window.location.href = window.location.href;
          }else{
            $.pt({
                target: _this,
                content: data.info,
                width: 'auto'
            });
          }
      })
      .fail(function(xhr) {
          $.pt({
              target: _this,
              content: '操作失败,请稍后再试！',
              width: 'auto'
          });
      });
  });

  //返回头部
  $(".fanhui").click(function(){
      $('body,html').animate({scrollTop:0},1000);
      return false;
  });

  $("#s-zxsj").click(function(event) {
      var _this = $(this);
      $.ajax({
        url: '/dispatcher/',
        type: 'POST',
        dataType: 'JSON',
        data: {
            type: "sj",
            source:'158',
            action: "load"
        }
      })
      .done(function(data) {
          if (data.status == 1) {
              $("body").append(data.data);
              $(".zb_box_sj").fadeIn(400, function() {
                  $(this).find("input[name=lf-name]").focus();
              });
          }
      });
  });

  $("#s-zxzb").click(function(event) {
    var _this = $(this);
    $.ajax({
          url: '/dispatcher/',
          type: 'POST',
          dataType: 'JSON',
          data: {
              type:"bj",
              source:'159',
              action:"load"
          }
      })
      .done(function(data) {
          if (data.status == 1) {
            $("body").append(data.data);
            if(navigator.appName == "Microsoft Internet Explorer"){
              $(".zxfb").show();
              _this.hide();
            }else{
              $(".zxfb").fadeIn(400,function(){
                  $(this).find("input[name='bj-xiaoqu']").focus();
              });
              $(".zxbj_content").removeClass('smaller');
            }
          }
      });
  });

  //客服挂件
  $(".fix_nav").hover(function(){
      $(".fix_nav .moqu_header").addClass('moqu_header_active');
  },function(){
      $(".fix_nav .moqu_header").removeClass('moqu_header_active');
  });
});
