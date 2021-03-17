$(function () {
  function GetRandomNum(Min,Max){
    var Range = Max - Min;
    var Rand = Math.random();
    return(Min + Math.round(Rand * Range));
  }
  var tTimer = setInterval(function (){
    var total_price = GetRandomNum(60000,180000)
    $("#total-price").html(total_price)
    $('#kt-price').html(parseInt(total_price*0.3))
    $('#zw-price').html(parseInt(total_price*0.3))
    $('#wsj-price').html(parseInt(total_price*0.15))
    $('#cf-price').html(parseInt(total_price*0.15))
    $('#other-price').html(parseInt(total_price*0.1))
  },300)
  $(".baojia-jisuanqi-wrap input[name=mianji]").blur(function(event) {
    var _this = $(this);
    var container = $(".baojia-jisuanqi-wrap");
    $(".focus", container).removeClass('focus');
    $(".error-info", container).html("");
    if (!App.validate.run($(this, container).val())) {
      $(this, container).addClass('focus');
      _this.siblings('.error-info').html("请填写房屋面积");
      return false;
    }
    if ($(this, container).val() !="" && !App.validate.run($(this, container).val(),"num")) {
      $(this, container).addClass('focus');
      _this.siblings('.error-info').html("无效的房屋面积");
      return false;
    }
  });
  $(".baojia-btn").click(function(event) {
    var container = $(".baojia-jisuanqi-wrap");
    var that=$(this);
    $(".focus", container).removeClass('focus');
    $(".error-info", container).html("");
    window.order({
      wrap: '.baojia-jisuanqi-wrap',
      extra: {
        cs: $("select[name=cs]", container).val(),
        qx: $("select[name=qx]", container).val(),
        mianji: $("input[name=mianji]", container).val(),
        tel: $("input[name=money_tel]", container).val(),
        fb_type: $("input[name=fb_type]", container).val(),
        source: source
      },
      error: function () {
        alert('获取报价失败,请刷新页面');
      },
      success: function (data, status, xhr) {
        if (data.status == 1) {
          $.ajax({
            url: '/getdetailsbyajax/',
            type: 'GET',
            dataType: 'JSON'
          }).done(function (data) {
            if (data.status == 1) {
              clearInterval(tTimer)
              $('#kt-price').text(data.data.kt);
              $('#zw-price').text(data.data.zw);
              $('#wsj-price').text(data.data.wsj);
              $('#cf-price').text(data.data.cf);
              $('#other-price').text(data.data.other);
              $('#total-price').text(data.data.total);
              $('.baojia-btn').addClass("btnSave");
            } else {
              alert(data.info);
            }
          }).fail(function (xhr) {
            alert('获取报价失败,请刷新页面');
          });
        } else {
          alert(data.info);
          if (data.info == "请填写正确的手机号码 ^_^!") {
            $("#baojia").addClass("focus").focus();
            return false;
          }
        }
      },
      validate: function (item, value, method, info) {
        if ('cs' == item && 'notempty' == method) {
          $("select[name=cs]", container).addClass('focus').focus();
          $("select[name=cs]", container).siblings('.error-info').html("请选择城市");
          return false;
        }
        
        if ('mianji' == item) {
          if (!App.validate.run($("input[name=mianji]", container).val())) {
            $("input[name=mianji]", container).addClass('focus').focus();
            $("input[name=mianji]", container).siblings('.error-info').html("请填写房屋面积");
            return false;
          }
          if (!App.validate.run($("input[name=mianji]", container).val(), "num")) {
            $("input[name=mianji]", container).addClass('focus').focus();
            $("input[name=mianji]", container).siblings('.error-info').html("无效的房屋面积");
            return false;
          }
          if($("input[name=mianji]",container).val()>10000 || $("input[name=mianji]",container).val() <= 0){
            $("input[name=mianji]", container).addClass('focus').focus();
            $("input[name=mianji]", container).siblings('.error-info').html("房屋面积请输入1-10000之间的数字^_^!");
            return false;
          }
        }
        
        if (!App.validate.run($("input[name=money_tel]", container).val())) {
          $("input[name=money_tel]", container).siblings('.error-info').html("请填写您的手机号码 ^_^!");
          return false;
        } else {
          var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
          if (!reg.test($("input[name=money_tel]", container).val())) {
            $("input[name=money_tel]", container).siblings('.error-info').html("请填写正确的手机号码 ^_^!");
            return false;
          }
        }
        
        if (!checkDisclamer(".miaosuan_mz")) {
          return false;
        }
        return true;
      }
    });
  });
});