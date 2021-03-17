;$(function(){
    $('.bottom-fadan-guoqing-box .bottom_order-guoqing_close_btn').on('click', function() {
        $('.bottom-fadan-guoqing-box').animate({
          left: '-1127px'
        }, 200).fadeOut(0)
        $('.bottom-order-icon').fadeIn(0)
    })
    $('.bottom-order-icon').on('click', function() {
      $('.bottom-fadan-guoqing-box').fadeIn(0).animate({
        left: '0px'
      }, 200)
      $('.bottom-order-icon').fadeOut(0)
    })
    $('.guoqing-suucess-close').on('click', function(){
      $('.bottom-order-guoqing-success').fadeOut(0)
      $('.bottom-fadan-guoqing-box').find('input').val('')
    })


  
    // 初始化城市选择
    var bottomSheng = citys["shen"];
    var bottomCitys = citys["shi"];
    initCity($(".bottom-fadan-btn").data('cityid'));
    function initCity(cityId){
        App.citys.init(".bottom-fadan-city", ".bottom-fadan-area", bottomSheng, bottomCitys, cityId);
    }
  
    // 提交发单
    $(".bottom-fadan-guoqing-box .bottom-fadan-btn").click(function(){
      setTips();
      var cs = $(".bottom-fadan-city").val();
      var qx = $(".bottom-fadan-area").val();
      var mianji = $(".bottom-fadan-mianji").val();
      var tel = $(".bottom-fadan-tel").val();
      var user = $(".bottom-fadan-name").val();
      var source = $("input[name='source']").val();
      var fb_type = 'baojia';
      var isDisclaimer = $(".bottom-fadan-guoqing-box .disclamer-check").attr("data-checked")
      if (cs==="" || qx === "") {
        setTips($(".bottom-fadan-city"),"请选择城市");
        return false;
      }
      if (user === "") {
        setTips($(".bottom-fadan-name"),"请输入您的称呼");
        return false;
      }
      var regTel = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
      if (tel === "") {
        setTips($(".bottom-fadan-tel"),"请输入您的手机号");
        return false;
      }
      if (!regTel.test(tel)) {
        setTips($(".bottom-fadan-tel"),"请填写正确的手机号码");
        return false;
      }
      if (isDisclaimer == 'false') {
        alert("请勾选我已阅读并同意齐装网的《免责声明》！");
        return false;
      }
      // 发单
      $.ajax({
        url:'/fb_order',
        method:'post',
        data:{
          cs: cs,
          qx: qx,
          name: user,
          tel: tel,
          source: source,
          fb_type:fb_type
        },
        success:function(res){
          if (res.status == 1) {
            $('.bottom-order-guoqing-success').fadeIn(0)
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
        $(".bottom-fadan-guoqing-box").find("select").blur();
        $(".bottom-fadan-guoqing-box").find("input").blur();
        $(".bottom-fadan-guoqing-box").find(".fadan-tips-box").text("");
        if (obj && text) {
            obj.parents(".order-ele-box").find(".fadan-tips-box").text(text);
            obj.focus();
        }
    }
  
});
  