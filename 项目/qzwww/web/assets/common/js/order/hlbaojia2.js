(function () {
  $(function(){
    $(".gl_box_form button").click(function(event) {
      var _this = $(this);
      var parents = _this.parents(".gl_box_form");
      $(".focus").removeClass('focus');
      if(!App.validate.run($("input[name=name]",parents).val())){
        $("input[name=name]",parents).focus().addClass('focus');
        $.pt({
          target: $("input[name=name]",parents),
          content: '请输入您的名称!',
          width: 'auto'
        });
        return false;
      } else {
        var reg = new RegExp("^[\u4e00-\u9fa5a-zA-Z]+$");
        if (!reg.test($("input[name=name]",parents).val())) {
          $("input[name=name]",parents).focus().addClass('focus');
          $("input[name=name]",parents).val('');
          $.pt({
            target: $("input[name=name]",parents),
            content: '请输入正确的名称，只支持中文和英文',
            width: 'auto'
          });
          return false;
        }
      }
      
      if(!App.validate.run($("input[name=tel]",parents).val())){
        $("input[name=tel]",parents).focus().addClass('focus');
        $.pt({
          target: $("input[name=tel]",parents),
          content: '请填写正确的手机号码 ^_^!',
          width: 'auto'
        });
        return false;
      } else {
        var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        if (!reg.test($("input[name=tel]",parents).val())) {
          $("input[name=tel]",parents).focus().addClass('focus');
          $("input[name=tel]",parents).val('');
          $.pt({
            target: $("input[name=tel]",parents),
            content: '请填写正确的手机号码 ^_^!',
            width: 'auto'
          });
          return false;
        }
      }
      if(!App.validate.run($("#gl_box_cs",parents).val())){
        $("#gl_box_cs",parents).focus().addClass('focus');
        $.pt({
          target: $("#gl_box_cs",parents),
          content: '请选择您所在的城市!',
          width: 'auto'
        });
        
        return false;
      }
      if(!checkDisclamer(".gl_t_box")){
        return false;
      }
      
      window.order({
        extra:{
          name:$("input[name=name]",parents).val(),
          tel: $("input[name=tel]",parents).val(),
          fb_type: $("input[name=fb_type]",parents).val(),
          hltime: $("select[name=start]",parents).val(),
          cs:$('#gl_box_cs').val(),
          qx:$('#gl_box_qx').val(),
          source: '183',
          step:99
        },
        error:function(){
          return true;
        },
        success:function(data, status, xhr){
          if (data.status == 1) {
            $(document.body).append(data.data.tmp);
          }
        },
        validate:function(item, value, method, info){
          return true;
        }
      });
    });
  });
})();