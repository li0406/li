(function () {
  setInterval(function(){
    var aa=parseInt(Math.random() * 90000+ 30000);
    $('.shuzi').html(aa);
    
  },300);
  $('.right-img').click(function(){
    var title = $(this).siblings('img').attr('data-fulltitle');
    var src = $(this).siblings('img').attr('src');
    $('.zxmoney-info').html(title);
    $('#zxmoneyPic').attr('src',src);
    
    $('.zxmoney').show();
    $('.zxmoney-box').show();
  });
  $(".zxmoney .fa-close").click(function(event) {
    $('.zxmoney').hide();
    $('.zxmoney-box').hide();
  });
  $("#getBaoJia").click(function(event) {
    var container = $(".xgt-jsq");
    $(".focus", container).removeClass('focus');
    $('.valdate-info').html('');
    if (!App.validate.run($("input[name=mianji]").val())) {
      $("input[name=mianji]").addClass('focus').focus();
      $("input[name=mianji]").siblings('.valdate-info').html('请填写房屋面积');
      return false;
    };
    
    if (!App.validate.run($("input[name=mianji]").val(), "num")) {
      $("input[name=mianji]").addClass('focus').focus();
      $("input[name=mianji]").siblings('.valdate-info').html('无效的房屋面积');
      return false;
    };
    
    if ($("#meitu-bj-cs").val() == '') {
      $("#meitu-bj-cs").siblings('.valdate-info').html('您还没有选择所在城市噢 ^_^!');
      $("#meitu-bj-cs").addClass('fal').focus();
      return false;
    };
    
    $("#meitu-bj-cs").removeClass('fal');
    
    if ($("#meitu-bj-qy").val() == '') {
      $("#meitu-bj-qy").siblings('.valdate-info').html('您还没有选择所在区域噢 ^_^!');
      $("#meitu-bj-qy").addClass('fal').focus();
      return false;
    };
    
    if ($(".freeBaojia input[name=name]").val() == "") {
      $("input[name=name]").siblings('.valdate-info').html('请填写您的称呼噢 ^_^!');
      $(".freeBaojia input[name=name]").addClass('fal').focus();
      return false;
    };
    
    $(".freeBaojia input[name=name]").removeClass('fal');
    
    var tel = $(".freeBaojia input[name=tel]").val();
    if (tel == "" || tel.length == 0) {
      $(".freeBaojia input[name=tel]").siblings('.valdate-info').html('亲,您还没有填写手机号码!');
      $(".freeBaojia input[name=tel]").addClass('fal').focus();
      return false;
    };
    
    var reg = /^[0-9]{7}|[0-9]{8}|[0-9]{11}$/gi;
    if (!$(".freeBaojia input[name=tel]").val().match(reg)) {
      $(".freeBaojia input[name=tel]").siblings('.valdate-info').html('请填写7位或11位纯数字的联系电话 ^_^!');
      $(".freeBaojia input[name=tel]").addClass('fal').focus();
      return false;
    };
    
    $(".freeBaojia input[name=tel]").removeClass('fal');
    
    if(!checkDisclamer(".xgt-jsq")){
      return false;
    }
    
    $("#meitu-bj-qy").removeClass('fal');
    
    var data = {
      name:$(".freeBaojia input[name=name]").val(),
      tel:$(".freeBaojia input[name=tel]").val(),
      fb_type:$(".freeBaojia input[name=fb_type]").val(),
      mianji:$(".freeBaojia input[name=mianji]").val(),
      cs:$("#meitu-bj-cs").val(),
      qx:$("#meitu-bj-qy").val(),
      source: '174'
    };
    
    window.order({
      extra:data,
      error:function(){
        alert('发布失败,请刷新页面！');
      },
      success:function(data, status, xhr){
        if (data.status == 1) {
          $.ajax({
            url: '/bjdata/',
            type: 'POST',
            dataType: 'JSON',
            data:{
              ssid:""
            }
          })
              .done(function(data) {
                if(data.status == 1){
                  $(".xgt-jsq-fens-main p").text(Math.round((data.data.allTotal /10000) * 100) / 100);
                  $(".freeBaojia").html('<div class="bjresult"><p>* 本价格为毛坯房半包估算价格（不包水电报价），旧房价格由实际工程量决定。</p>* 稍后客服将致电您，为您提供免费装修咨询服务。<p class="center"><img src="/assets/common/img/DY-ewm.png" /></p><p class="center">扫“码”上有惊喜！</p><p class="center">关注齐装网官方微信号，体验“微装修”服务</p></div>');
                }else{
                  alert(data.info);
                }
              })
              .fail(function(xhr) {
                alert('获取报价失败,请稍后再试！');
              });
        } else {
          alert(data.info);
        }
      },
      validate:function(item, value, method, info){
        return true;
      }
    });
  });
  
  $("#getFreeDesign").click(function(event) {
    if ($(".freeDesign input[name=name]").val() == "") {
      alert("请填写您的称呼噢 ^_^!");
      $(".freeDesign input[name=name]").addClass('fal').focus();
      return false;
    }
    $(".freeDesign input[name=name]").removeClass('fal');
    
    var tel = $(".freeDesign input[name=tel]").val();
    if (tel == "" || tel.length == 0) {
      alert("亲,您还没有填写手机号码!");
      $(".freeDesign input[name=tel]").addClass('fal').focus();
      return false;
    }
    
    var reg = /^[0-9]{7}|[0-9]{8}|[0-9]{11}$/gi;
    if (!$(".freeDesign input[name=tel]").val().match(reg)) {
      alert("请填写7位或11位纯数字的联系电话 ^_^!");
      $(".freeDesign input[name=tel]").addClass('fal').focus();
      return false;
    }
    
    $(".freeDesign input[name=tel]").removeClass('fal');
    
    if ($("#meitu-sj-cs").val() == '') {
      alert("您还没有选择所在城市噢 ^_^!");
      $("#meitu-sj-cs").addClass('fal').focus();
      return false;
    }
    $("#meitu-sj-cs").removeClass('fal');
    
    if ($("#meitu-sj-qy").val() == '') {
      alert("您还没有选择所在区域噢 ^_^!");
      $("#meitu-sj-qy").addClass('fal').focus();
      return false;
    }
    $("#meitu-sj-qy").removeClass('fal');
    
    var data ={
      name:$(".freeDesign input[name=name]").val(),
      tel:tel,
      fb_type:$(".freeDesign input[name=fb_type]").val(),
      cs:$("#meitu-sj-cs").val(),
      qx:$("#meitu-sj-qy").val(),
      source: 175,
      step:2
    };
    
    window.order({
      extra:data,
      error:function(){
        alert("网络发生错误,请稍后重试！");
      },
      success:function(data, status, xhr){
        if (data.status == 1) {
          $("body").append(data.data.tmp);
          $(".freeDesign input[name=name]").val('');
          $(".freeDesign input[name=tel]").val('')
        }else{
          alert(data.info);
        }
      },
      validate:function(item, value, method, info){
        return true;
      }
    });
    return false;
  });
  
  $("#getSJ").click(function(event) {
    var _this = $(this);
    $.ajax({
      url: '/dispatcher/',
      type: 'POST',
      dataType: 'JSON',
      data: {
        type: "sj",
        source: 184,
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
  
  $("#getBJ").click(function(event) {
    var _this = $(this);
    $.ajax({
      url: '/dispatcher/',
      type: 'POST',
      dataType: 'JSON',
      data: {
        type:"bj",
        source: 184,
        action:"load"
      }
    })
        .done(function(data) {
          if (data.status == 1) {
            $("body").append(data.data);
            // $(".zxfb").show();
            $(".zxfb").fadeIn(400,function(){
              $(this).find("input[name='bj-xiaoqu']").focus();
            });
            $(".zxbj_content").removeClass('smaller');
          }
        });
  });
})();