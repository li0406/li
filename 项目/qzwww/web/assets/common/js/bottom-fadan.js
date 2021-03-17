;$(function(){
  // 展开/关闭 效果
  var hasResult = true;
  $(".bottom-main-header").click(function(){
    setAnimate();
  });
  $(".bottom-fadan-mask").click(function(){
    if(!hasResult){
      return
    }
    setAnimate();
  });
  function setAnimate () {
    var dir = $(".bottom-main-arrow").data("dir");
    if (!dir) {
      $(".bottom-dibu-radius").animate({
        'bottom':'0px'
      }, 300);
      $(".bottom-fadan-sideradius").animate({
        'bottom':'25px'
      }, 300);
      $(".bottom-main-raidus").animate({
        'bottom':'17px'
      }, 300);
      $(".bottom-fadan-mask").animate({
        'bottom':'-20px'
      }, 300);
      $(".bottom-main-arrow").attr("src",'/assets/common/img/bt-fd-arrow-down.png');
    } else {
      $(".bottom-dibu-radius").animate({
        'bottom':'-45px'
      }, 300);
      $(".bottom-fadan-sideradius").animate({
        'bottom':'-180px'
      }, 300);
      $(".bottom-main-raidus").animate({
        'bottom':'-450px'
      }, 300);
      $(".bottom-fadan-mask").animate({
        'bottom':'-443px'
      }, 300);
      $(".bottom-main-arrow").attr("src",'/assets/common/img/bt-fd-arrow-up.png');
    }
    $(".bottom-main-arrow").data("dir",!dir)
  }

  // 初始化城市选择
  var bottomSheng = citys["shen"];
  var bottomCitys = citys["shi"];
  initCity($(".fadan-btn").data('cityid'));
  function initCity(cityId){
      App.citys.init(".bottom-fadan-city", ".bottom-fadan-area", bottomSheng, bottomCitys,cityId);
  }

  // 提交发单
  $(".fadan-btn").click(function(){
    setTips();
    var cs = $(".bottom-fadan-city").val();
    var qx = $(".bottom-fadan-area").val();
    var mianji = $(".bottom-fadan-mianji").val();
    var tel = $(".bottom-fadan-tel").val();
    var name = $(".bottom-fadan-name").val();
    var source = $("input[name='source']").val();
    var fb_type = 'baojia';
    var isDisclaimer = $(".fadan-input-box .disclamer-check").attr("data-checked")
    if (cs==="" || qx === "") {
      setTips($(".bottom-fadan-city"),"请选择您所在的区域");
      return false;
    }
    if (mianji === "") {
      setTips($(".bottom-fadan-mianji"),"请输入房屋面积！");
      return false;
    }
    if (isNaN(mianji)) {
      setTips($(".bottom-fadan-mianji"),"房屋面积请输入1-1000之间的数字 ^_^!");
      return false;
    }
    if (mianji<=0 || mianji>1000) {
      setTips($(".bottom-fadan-mianji"),"房屋面积请输入1-1000之间的数字 ^_^!");
      return false;
    }
    var regTel = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
    if (tel === "") {
      setTips($(".bottom-fadan-tel"),"请输入您的手机号码 ^_^!");
      return false;
    }
    if (!regTel.test(tel)) {
      setTips($(".bottom-fadan-tel"),"请输入正确的手机号码 ^_^!");
      return false;
    }
    if(name === ''){
      setTips($(".bottom-fadan-name"),"请输入您的姓名 ^_^!");
      return false;
    } else {
      var reg = /^[a-zA-Z\u4e00-\u9fa5]+$/
      if(!reg.test(name)){
        setTips($(".bottom-fadan-name"),"请输入正确的姓名，只支持中文和英文 ^_^!");
        return false;
      }
    }
    if (isDisclaimer=='false') {
      setTips($(".fadan-input-box .disclamer-check"),"请勾选我已阅读并同意齐装网的《免责声明》！");
      return false;
    }
  
    // 发单
    $.ajax({
      url:'/fb_order',
      method:'post',
      data:{
        cs: cs,
        qx: qx,
        mianji: mianji,
        tel: tel,
        name: name,
        source: source,
        fb_type:fb_type
      },
      success:function(res){
        if (res.status == 1) {
          hasResult = false
          $(".bottom-main-result").animate({
            "bottom":"4px"
          },300);
          $(".bottom-main-raidus").animate({
            'bottom':'-640px'
          }, 300);
          $(".bottom-fadan-mask").css({
            'width':'100%',
            'height':'3000px'
          })
        } else {
          alert(res.info)
        }
      },
      fail:function(res){
        alert(res.info)
      }
    })
  });

  // 验证提示语
function setTips(obj,text){
  $(".fadan-form").find("select").blur();
  $(".fadan-form").find("input").blur();
  $(".fadan-form").find(".fadan-tips-box").text("");
  if (obj && text) {
    obj.parents(".fadan-input-box").next(".fadan-tips-box").text(text);
    obj.focus();
  }
}
// 关闭结果页
$(".bottom-fadan-close").click(function(){
  var sendData={
    cs: $(".bottom-fadan-city").val(),
    qx: $(".bottom-fadan-area").val(),
    tel: $(".bottom-fadan-tel").val(),
    text:"跟业主联系时间：" + $(".select-radio").find(".fdtime-active p").text()
  }
  // 发单
  $.ajax({
    url:'/fb_order',
    method:'post',
    data:sendData,
    success:function(res){
      if (res.status == 1) {
        hasResult = true
        $(".bottom-main-result").animate({
          "bottom":"-650px"
        },300);
        $(".bottom-main-raidus").animate({
          'bottom':'-450px'
        }, 300);
        $(".bottom-dibu-radius").animate({
          'bottom':'-45px'
        }, 300);
        $(".bottom-fadan-sideradius").animate({
          'bottom':'-180px'
        }, 200);
        $(".bottom-fadan-mask").css({
          'width':'100%',
          'height':'492px',
          'bottom':'-450px'
        })
        $(".bottom-main-arrow").attr("src",'/assets/common/img/bt-fd-arrow-up.png').data("dir",false);
      }else{
        alert(res.info)
      }
    },
    fail:function(res){
      alert(res.info)
    }
  })
})
$(".select-radio").find("li").click(function(){
  $(this).addClass("fdtime-active");
  $(this).siblings("li").removeClass("fdtime-active")
})

});
