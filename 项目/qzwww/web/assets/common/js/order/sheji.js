(function () {
  $('.btn-submit').click(function(event) {
    var container = $(".order-box");
    var data = {
      cs:$("[name=cs]",container).val(),
      qx:$("[name=qx]",container).val(),
      mianji:$("[name=mianji]",container).val(),
      xiaoqu:$("[name=xiaoqu]",container).val(),
      tel:$("[name=tel]",container).val(),
      fb_type:$("[name=fb_type]",container).val(),
      step:3,
      source:163
    }
    $(".error-info").html('')
    $("#close_window").click(function(){$("#success_fadan").fadeOut();});
    window.order({
      extra:data,
      error:function(){},
      success:function(data, status, xhr){
        if(data.status == 1){
          
          $("#success_fadan").fadeIn();
        } else {
          alert(data.info);
        }
      },
      validate:function(item, value, method, info){
        if ('cs' == item && '' != method) {
          $(".error-city").html(info)
          return false;
        }
        if ('mianji' == item && '' != method) {
          $(".error-mianji").html(info)
          return false;
        }
        if ('xiaoqu' == item && '' != method) {
          $(".error-xiaoqu").html(info)
          return false;
        }
        
        if ('tel' == item && '' != method) {
          $(".error-phone").html(info)
          return false;
        }
        
        if(!checkDisclamer(".order-box")){
          return false;
        }
        return true;
      }
    });
  });
})();