(function () {
  var flag = false;
  setInterval(function () {
    var aa = parseInt(Math.random() * 90000 + 30000);
    $('.jisuanjg .shuzi').html(aa);
    
  }, 300)
  $(".right-now-btn").click(function (event) {
    var _this = $(this).parent().parent();
    
    var mianji = _this.find("input[name=mianji]");
    var cs = _this.find("select[name=cs]");
    var qy = _this.find("select[name=qy]");
    var xiaoqu = _this.find("input[name=xiaoqu]");
    var tel = _this.find("input[name=tel]");
    var comid = $("#companyid").val();
    
    var data = {
      mianji: mianji.val(),
      xiaoqu: xiaoqu.val(),
      tel: tel.val(),
      cs: cs.val(),
      qx: qy.val(),
      source: '187',
      fb_type: 'baojia',
    };
    
    window.order({
      extra: data,
      error: function () {
        alert("网络发生错误,请稍后重试！");
      },
      success: function (data, status, xhr) {
        if (data.status == 1) {
          $('.bj-form').hide();
          $('.jisuanjieguo').show()
          if (data.status == 1) {
            $.ajax({
              url: '/getdetailsbyajax/',
              type: 'GET',
              dataType: 'JSON'
            })
                .done(function (data) {
                  if (data.status == 1) {
                    calculate(data.data);
                  } else {
                    alert(data.info);
                  }
                })
                .fail(function (xhr) {
                  alert('获取报价失败,请刷新页面');
                });
          }
        } else {
          alert(data.info);
          if (data.info == "请填写正确的手机号码 ^_^!") {
            tel.focus();
          }
        }
      },
      validate: function (item, value, method, info) {
        if (item == "mianji" && method == "notempty") {
          $(".errorMianji-tit").html('面积不能为空');
          mianji.focus();
          return false;
        }
        if (item == "mianji" && method == "isnumber") {
          $(".errorMianji-tit").html('面积只能为数字');
          mianji.focus();
          return false;
        }
        if ($("input[name='mianji']").val() < 1 || $("input[name='mianji']").val() > 10000) {
          $(".errorMianji-tit").html('房屋面积请输入1-10000之间的数字^_^!');
          mianji.focus();
          return false;
        }else{
          $(".errorMianji-tit").html('');
        }
        if (item == "xiaoqu" && method == "notempty") {
          $(".errorXiaoqu-tit").html('小区不能为空');
          xiaoqu.focus();
          return false;
        }
        if (!isNaN($("[name=xiaoqu]").val())) {
          $(".errorXiaoqu-tit").html('请正确填写小区名称');
          $("[name=xiaoqu]").focus();
          return false;
        }else{
          $(".errorXiaoqu-tit").html('');
        }
        if (item == "tel" && method == "notempty") {
          $(".errorTel-tit").html('号码不能为空');
          tel.focus();
          return false;
        }
        if (item == "tel" && method == "ismobile") {
          $(".errorTel-tit").html('请输入正确的手机号码');
          tel.focus();
          return false;
        }else{
          $(".errorTel-tit").html('');
        }
        if(!checkDisclamer(".bj-content")){
          return false;
        }
        
        return true;
      }
    });
    return false;
  });
})();