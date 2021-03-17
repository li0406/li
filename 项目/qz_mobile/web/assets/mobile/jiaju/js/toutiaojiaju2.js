$(function(){
  // 获取随机数的方法
  function GetRandomNum(Min,Max){
    var Range = Max - Min;
    var Rand = Math.random();
    return(Min + Math.round(Rand * Range));
  }
  // 随机数
  var timer = setInterval(function(){
    var num = GetRandomNum(10000,40000)+'';
    if(num<99999){
        var num1 = 'num num-gray',
            num2 = 'num num-' + num.charAt(0),
            num3 = 'num num-' + num.charAt(1),
            num4 = 'num num-' + num.charAt(2),
            num5 = 'num num-' + num.charAt(3),
            num6 = 'num num-' + num.charAt(4);
    }else{
        var num1 = 'num num-' + num.charAt(0),
            num2 = 'num num-' + num.charAt(1),
            num3 = 'num num-' + num.charAt(2),
            num4 = 'num num-' + num.charAt(3),
            num5 = 'num num-' + num.charAt(4),
            num6 = 'num num-' + num.charAt(5);
    }
    $('#num-1').removeClass().addClass(num1);
    $('#num-2').removeClass().addClass(num2);
    $('#num-3').removeClass().addClass(num3);
    $('#num-4').removeClass().addClass(num4);
    $('#num-5').removeClass().addClass(num5);
    $('#num-6').removeClass().addClass(num6);
  },400);

  var mySwiper = new Swiper('.lunbo-banner', {
    //移动端轮播
    loop : true,
    pagination: {
      el: '.swiper-pagination',
    },
    autoplay: true,
  })

  // 8秒立即免费计算
  $('.form-once').on('click','.save-submit',function(){
    var mianji = $(".m-bj-edit input[name=mianji]").val();
    var style = $(".m-bj-edit input[name=style]").val();
    var tel= $(".m-bj-edit input[name=tel-number]").val();
    var cs = $("input[name=city]").attr('data-id');
    var qy = $("input[name=area]").attr('data-id');
    var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
     
    if(cs==''||qy==''){
      alert("请选择城市~");
      return false;
    }
    if(mianji==""){
      $("input[name='mianji']").focus();
      alert("请输入面积~");
      return false;
    }else if(mianji < 5 || mianji > 1000 || isNaN(mianji)){
        $("input[name='mianji']").focus();
        alert("请输入5-1000之间的面积~");
        return false;
    }
    if(style == ""){
      alert("请至少选择一个户型~");
      return false;
    }
    if(tel==''){
      $("input[name='tel-number']").focus();
      alert("请输入手机号~");
      return false;
    }
    if(!reg.test(tel)){
        $("input[name='tel-number']").focus();
        alert("请输入正确的手机号~");
        return false;
    }
     
    window.order({
        url:'/jiajufb',
        extra:{
        mianji:mianji,
        tel:tel,
        source: '18112821',
        cs:cs,
        qy:qy,
        huxing:$("[name=style]").val(),
        source:18112821
      },
      submitBtn:{
        className:$(this),
        type:'btn'
      },
      error:function(){
         
      },
      success:function(data, status, xhr){
         
        if(data.error_code == 0){
            meteor.track("form", {convert_id: "1626501665442819"})
            window.location.href = "/qwdbjjg";
        }else{
            alert(data.error_msg);
        }
      },
      validate:function(item, value, method, info){
          return true;
      }
    })
  })

  $('.ljjs').click(function(){
    $('input[name=mianji]').focus()
  })

  $('#choose').click(function(){
    $('#choose').blur();
    $('.bg').show();
    $('.choose-box').show();
  })
  var wsStr='1卧',ktStr='1客',ctStr='1餐',cfStr='1厨',allStr='';
  $('.woshi li').click(function(){
    $(this).addClass('active').siblings('li').removeClass('active')
    wsStr = $(this).text() + $(this).parent().prev('div').text()
    wsStr = wsStr.substring(0,2)
  })
  $('.keting li').click(function(){
    $(this).addClass('active').siblings('li').removeClass('active')
    ktStr = $(this).text() + $(this).parent().prev('div').text()
    ktStr = ktStr.substring(0,2)
  })
  $('.canting li').click(function(){
    $(this).addClass('active').siblings('li').removeClass('active')
    ctStr = $(this).text() + $(this).parent().prev('div').text()
    ctStr = ctStr.substring(0,2)
  })
  $('.chufang li').click(function(){
    $(this).addClass('active').siblings('li').removeClass('active')
    cfStr = $(this).text() + $(this).parent().prev('div').text()
    cfStr = cfStr.substring(0,2)
  })
  $('.sure').click(function(){
    allStr = ktStr + ctStr + wsStr + cfStr
    if(allStr=='0客0餐0卧0厨'){
      alert('请至少选择一个户型');
      return false;
    }
    $('.bg').hide()
    $('.choose-box').hide()
    $('#choose').val(allStr)
  })
  $('.bg').click(function(){
    $(this).hide()
    $('.choose-box').hide()
  })
})
