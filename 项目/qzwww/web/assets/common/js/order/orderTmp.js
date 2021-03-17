(function () {
  $(function(){
    var container = $("#fd_box_form .new_xsc_fadan .fadan_form");
    
    $("#tijiao_fadan").click(function(event) {
      
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
          $(".fadan_form .two").html('房屋面积请输入1-10000之间的数字^_^!').css('display','block');
          return false;
        }
      }
      
      if (!App.validate.run($("input[name=tel]", container).val())) {
        $("input[name=tel]", container).addClass('focus').focus();
        $(".fadan_form .three").html('请填写手机号码 ^_^!').css('display','block');
        return false;
      } else {
        var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        if (!reg.test($("input[name=tel]", container).val())) {
          $("input[name=tel]", container).addClass('focus').focus();
          $(".fadan_form .three").html('请填写正确的手机号码 ^_^!').css('display','block');
          return false;
        }
      }
      if(!checkDisclamer(".secbox_form")){
        return false;
      }
      var data = {
        name:$("input[name=name]",container).val(),
        tel:$("input[name=tel]",container).val(),
        fb_type:$("input[name=fb_type]",container).val(),
        cs:$("select[name=cs]",container).val(),
        qx:$("select[name=qx]",container).val(),
        mianji:$("input[name=mianji]",container).val(),
        source: order_source,
        tpl:'miniStep',
        step:2
      };
      window.order({
        extra:data,
        error:function(){
          $("#f_bj_cs", container).addClass('focus').focus();
          $(".fadan_form .three").html('发送失败,请稍后重试！').css('display','block');
        },
        success:function(data, status, xhr){
          if (data.status == 1) {
            $("body").append(data.data.tmp);
          } else {
            $("#f_bj_cs", container).addClass('focus').focus();
            $(".fadan_form .three").html(data.info).css('display','block');
          }
        },
        validate:function(item, value, method, info){
          return true;
        }
      });
    });
  });
})();