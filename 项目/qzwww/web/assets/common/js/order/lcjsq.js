(function () {
  setInterval(function(){
    var randomResult=parseInt(Math.random() * 90000+ 30000);
    $('.number_result').html(randomResult);
  },300)
  $(function(){
    var container = $("#fd_box_form .new_xsc_fadan .fadan_form");
    
    $("#tijiao_fadan").click(function(event) {
      var disclamer =$(".new_xsc_fadan .disclamer-check").attr("data-checked");
      $(".valdatemsg").html('').css('display','none');
      
      if (!App.validate.run($("#f_bj_cs", container).val())) {
        $("#f_bj_cs", container).addClass('focus').focus();
        $(".fadan_form .one").html('请选择您的城市').css('display','block');
        return false;
      }
      if (!App.validate.run($("#f_bj_qx", container).val())) {
        $("#f_bj_qx", container).addClass('focus').focus();
        $(".fadan_form .one").html('请选择您的区域').css('display','block');
        return false;
      }
      
      if (!App.validate.run($("input[name=mianji]", container).val())) {
        $("input[name=mianji]", container).addClass('focus').focus();
        $(".fadan_form .two").html('请输入您的住房面积').css('display','block');
        return false;
      }else{
        if(isNaN($("input[name=mianji]", container).val())){
          $("input[name=mianji]", container).addClass('focus').focus();
          $(".fadan_form .two").html('面积必须是数字').css('display','block');
          return false;
        }
        if ($("input[name=mianji]", container).val()>10000||$("input[name=mianji]", container).val()<1) {
          $("input[name=mianji]", container).addClass('focus').focus();
          $(".fadan_form .two").html('面积填写1~10000的数字!').css('display','block');
          return false;
        }
      }
      
      if (!App.validate.run($("input[name=tel]", container).val())) {
        $("input[name=tel]", container).addClass('focus').focus();
        $(".fadan_form .three").html('请填写手机号码 ^_^!').css('display','block');
        return false;
      } else {
        var reg = new RegExp("^(130|131|132|155|156|185|186|145|176|175" +
            "|139|138|137|136|135|134|147|150|151|152|157|158|159|178|182|183|184|187|188" +
            "|133|153|177|173|180|181|189)[0-9]{8}$");
        if (!reg.test($("input[name=tel]", container).val())) {
          $("input[name=tel]", container).addClass('focus').focus();
          $(".fadan_form .three").html('请填写正确的手机号码 ^_^!').css('display','block');
          return false;
        }
      }
      if(!App.validate.run($("input[name=xiaoqu]", container).val())){
        $("input[name=xiaoqu]", container).addClass('focus').focus();
        $(".fadan_form .four").html('请填写小区名称 ^_^!').css('display','block');
        return false;
      }else{
        var reg= /^[0-9]+(.[0-9]{1,2})?$/gi;
        if(reg.test($("input[name=xiaoqu]", container).val())){
          $(".fadan_form .four").html('请填写正确的小区名称 ^_^!').css('display','block');
          return false;
        }
      }
      
      if(disclamer=="false"){
        alert("请勾选我已阅读并同意齐装网的《免责声明》！");
        return false;
      }
      var data = {
        tel:$("input[name=tel]",container).val(),
        fb_type:$("input[name=fb_type]",container).val(),
        cs:$("select[name=cs]",container).val(),
        qx:$("select[name=qx]",container).val(),
        mianji:$("input[name=mianji]",container).val(),
        xiaoqu:$("input[name=xiaoqu]",container).val()
      };
      window.order({
        extra : data,
        error:function(data){ alert("网络发生错误,请稍后重试！");},
        success:function(data, status, xhr){
          if (data.status == 1) {
            $.ajax({
              url: '/getdetailsbyajax/',
              type: 'GET',
              dataType: 'JSON'
            })
                .done(function (data) {
                  if (data.status == 1) {
                    calculate(data.data);
                    
                    $('.new_xsc_fadan').hide();
                    $('.jisuanjieguo').show()
                  } else {
                    alert(data.info);
                  }
                })
                .fail(function (xhr) {
                  alert('获取报价失败,请刷新页面');
                });
          }else{
            alert("发生错误,请稍后重试！");
          }
        },
        validate:function(item, value, method, info){
          return true;
        }
      });
      
      function calculate(data) {
        $('#kt-price').text(data.kt);
        $('#zw-price').text(data.zw);
        $('#wsj-price').text(data.wsj);
        $('#cf-price').text(data.cf);
        $('#sd-price').text(data.sd);
        $('#other-price').text(data.other);
        $('#total-price').text(data.total / 10000);
      }
    });
  });
})();