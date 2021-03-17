(function () {
  $(".zb-bg1 #btnSave").click(function(event) {
    var container = $(".zb-bg1 .order-box");
    var data = {
      name:$("[name=name]",container).val(),
      tel:$("[name=tel]",container).val(),
      fb_type:$("[name=fb_type]",container).val(),
      cs:$("[name=cs]",container).val(),
      qx:$("[name=qx]",container).val(),
      step:2,
      source:163
    }
    $(".focus", container).removeClass('focus');
    $(".height_auto", container).removeClass('height_auto');
    $(".valdate-info", container).remove();
    
    window.order({
      extra:data,
      error:function(){},
      success:function(data, status, xhr){
        if(data.status == 1){
          
          $("body").append(data.data.tmp);
          $('input[name="mianji"]').blur();// 让其失去焦点
        } else {
          alert(data.info);
        }
      },
      validate:function(item, value, method, info){
        if ('name' == item) {
          $("[name=name]",container).parent().addClass('height_auto');
          $("[name=name]",container).addClass('focus').focus();
          var span = $("<span class='valdate-info'></span>");
          span.html(info);
          $("[name=name]",container).parent().append(span);
          return false;
        };
        if ('tel' == item) {
          $("[name=tel]",container).parent().addClass('height_auto');
          $("[name=tel]",container).addClass('focus').focus();
          var span = $("<span class='valdate-info'></span>");
          span.html(info);
          $("[name=tel]",container).parent().append(span);
          return false;
        };
        if(!checkDisclamer(".zb-bg1")){
          return false;
        }
        return true;
      }
    });
  });
  //光标自动定位
  $(".zb-bg1 .order-box input[name=name]").focus();
})();