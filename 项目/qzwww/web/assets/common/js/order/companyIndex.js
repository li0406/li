(function () {
//马上提交看结果
  $(".kjg").click(function(event) {
    var _this = $(this).parents(".bj-form");
    var mianji = _this.find("input[name=mianji]");
    var cs = _this.find("select[name=cs]");
    var qy = _this.find("select[name=qy]");
    var xiaoqu = _this.find("input[name=xiaoqu]");
    var tel = _this.find("input[name=tel]");
    var comid = $("#companyid").val();
    
    window.order({
      wrap:'.bj-form',
      extra:{
        mianji:mianji.val(),
        xiaoqu:xiaoqu.val(),
        tel:tel.val(),
        cs:cs.val(),
        qx:qy.val(),
        source: "159" || 30,
        step:2
      },
      error:function(){
        alert("网络发生错误,请稍后重试！");
      },
      success:function(data, status, xhr){
        if (data.status == 1) {
          $('.pop-bj').fadeOut(300).find('.ten-bj').fadeOut(300).find('.free-bj').fadeOut(300);
          $("body").append(data.data.tmp);
        }else{
          alert("发生错误,请稍后重试！");
        }
      },
      validate:function(item, value, method, info){
        if ('mianji' == item) {
          if ("" == value) {
            mianji.focus();
            $(".errorMianji-tit").html('亲，您还没有填写房屋面积！');
            return false;
          }
          var re = /^[0-9]+(.[0-9]{1,2})?$/gi;
          if (!re.test(value)) {
            mianji.focus();
            $(".errorMianji-tit").html('亲，房屋面积只能填纯数字！');
            return false;
          }
        }else{
          $(".errorMianji-tit").html('');
        }
        
        if ('xiaoqu' == item) {
          if ("" == value) {
            xiaoqu.focus();
            $(".errorXiaoqu-tit").html('亲，您还没有填写小区名称！');
            return false;
          }
          var re = /^[0-9]+(.[0-9]{1,2})?$/gi;
          if (re.test(value)) {
            xiaoqu.focus();
            $(".errorXiaoqu-tit").html('亲，请填写正确的小区名称！');
            return false;
          }
        }else{
          $(".errorXiaoqu-tit").html('');
        }
        
        if ('tel' == item && 'notempty' == method) {
          tel.focus();
          $(".errorTel-tit").html('亲，您还没有填写手机号码！');
          return false;
        }else if ('tel' == item && 'ismobile' == method) {
          tel.focus();
          $(".errorTel-tit").html('亲，请输入11位手机号码！');
          return false;
        }else{
          $(".errorTel-tit").html('');
        }
        
        if ('cs' == item && 'notempty' == method) {
          $(".errorArea-tit").html('您还没有选择所在城市噢 ^_^!');
          cs.focus();
          return false;
        }
        
        if ('qx' == item && 'notempty' == method) {
          $(".errorArea-tit").html('您还没有选择所在区域噢 ^_^!');
          qy.focus();
          return false;
        }else{
          $(".errorArea-tit").html('');
        }
        
        if(!checkDisclamer(".new-box-r")){
          return false;
        }
        return true;
      }
    });
    return false;
  });
})();