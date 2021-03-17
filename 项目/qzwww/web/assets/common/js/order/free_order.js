$('#f_Save').click(function () {
  var cs = $('[name=f_cs]').val(),
      qx = $('[name=f_qx]').val(),
      name = $('[name=o_name]').val(),
      tel = $('[name=o_tel]').val();
  if (cs == '' || qx == '') {
    $(".f-errorArea-tit").html('请选择城市');
    return false
  }else{
    $(".f-errorArea-tit").html('');
  }
  var reg = /^[\u4e00-\u9fa5a-zA-Z]+$/i;
  var regTel = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
  
  if (tel == '') {
    $(".f-errorTel-tit").html('请输入您的手机号码');
    return false
  }else if (tel.length < 11) {
    $(".f-errorTel-tit").html('请输入11位手机号码');
    return false
  }else if (!regTel.test(tel)) {
    $(".f-errorTel-tit").html('请填写正确的手机号码');
    return false
  }else{
    $(".f-errorTel-tit").html('');
  }
  if (name == '') {
    $(".f-errorName-tit").html('请输入联系人');
    return false
  }else if (!reg.test(name)) {
    $(".f-errorName-tit").html('请输入正确的名称，只支持中文和英文');
    return false
  }else{
    $(".f-errorName-tit").html('');
  }
  if (!checkDisclamer(".free-box")) {
    return false
  }
  $.ajax({
    url: '/fb_qwdz',
    type: 'POST',
    dataType: 'JSON',
    data: {
      cs: cs,
      qx: qx,
      name: name,
      tel: tel
    }
  }).done(function (data) {
    if (data.error_code == 0) {
      $('.mask').show()
      $('.success-box').delay(500).show()
      $('[name=o_name]').val('')
      $('[name=o_tel]').val('')
      $('.free-order').hide().delay(500)
    } else {
      alert('未知错位,请重试')
    }
  })
})