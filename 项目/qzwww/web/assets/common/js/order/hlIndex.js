(function () {
  $("#top-hl-butt").click(function(event){
    var _this = $(this);
    var disclamer =$(".top-hl-box .disclamer-check").attr("data-checked");
    if(!App.validate.run($("input[name=top_hl_tel]").val())){
      $("input[name=top_hl_tel]").focus();
      $.pt({
        target: $("input[name=top_hl_tel]"),
        content: '请填写您的电话',
        width: 'auto'
      });
      return false;
    } else {
      var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
      if (!reg.test($("input[name=top_hl_tel]").val())) {
        $("input[name=top_hl_tel]").focus();
        $.pt({
          target: $("input[name=top_hl_tel]"),
          content: '请填写正确的手机号码 ^_^!',
          width: 'auto'
        });
        return false;
      }
    }
    if(disclamer=="false"){
      alert("请勾选我已阅读并同意齐装网的《免责声明》！");
      return false;
    }
    
    
    window.order({
      extra:{
        tel: $("input[name=top_hl_tel]").val(),
        fb_type: $("input[name=top_hl_fb_type]").val(),
        hltime: $(".top-hl-box select[name=xztime]").val(),
        source: '179',
        cs:$("select[name=top_hl_cs]").val(),
        qx:$("select[name=top_hl_qx]").val(),
        safecode:$("#safecode").val(),
        safekey: $("#safekey").val(),
        ssid:"",
        step:99
      },
      error:function(){
        $.pt({
          target: _this,
          content: '发布失败,请刷新页面！',
          width: 'auto'
        });
      },
      success:function(data, status, xhr){
        $("#safecode").val(data.data.safecode);
        $("#safekey").val(data.data.safekey);
        if (data.status == 1) {
          $("body").append(data.data.tmp);
        } else {
          $.pt({
            target: _this,
            content: data.info,
            width: 'auto'
          });
        }
      },
      validate:function(item, value, method, info){
        return true;
      }
    });
  });
})();