$(function () {
    // 城市选择插件
    selectQz.init({
        province: $("input[name=province]").attr("data-id"),
        city: $("input[name=city]").attr("data-id"),
        area: $("input[name=area]").attr("data-id")
    });
    // 获取随机数的方法
    function GetRandomNum(Min,Max){
        var Range = Max - Min;
        var Rand = Math.random();
        return(Min + Math.round(Rand * Range));
    }
    // 客户随机数
    // var people = GetRandomNum(20000,30000);
    // $('.people').html(people+'位');
    // 装修总价随机数
    var timer = setInterval(function(){
        var num = GetRandomNum(40000,200000)+'';
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
    $("#style").change(function(){
        $(this).css('color','#000');
    })
    $('.save-submit').on('click', function () {
         
        
        var checked = $(".disclamer-check").attr('data-checked');
        var mianji = $(".m-bj-edit input[name=mianji]").val();
        var style = $("#style").val();
        var tel = $(".m-bj-edit input[name=tel-number]").val();
        var cs = $("input[name=city]").attr('data-id');
        var qy = $("input[name=area]").attr('data-id');
        if(cs ==''||qy ==''){
            alert("请选择城市");
            return false;
        };
        if(style == ""||style == null){
            alert("请选择公装类型");
            return false;
        };
        if(mianji == ""){
            $("input[name='mianji']").focus();
            alert("请输入面积");
            return false;
        }else if(mianji < 1 || mianji > 1000 || isNaN(mianji)){
            $("input[name='mianji']").focus();
            alert("房屋面积请输入1-1000之间的数字");
            return false;
        };
        if(tel==''){
            $("input[name='tel-number']").focus();
            alert("请输入您的手机号码");
            return false;
        };
        if(tel.length < 11){
            alert("请输入11位手机号码");
            return false;
        }
        var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        if (!reg.test(tel)) {
            $("input[name=tel-number]").focus().val('');
            alert("请填写正确的手机号码");
            return false;
        }
        if (checked == 'false') {
            alert('请勾选我已阅读并同意齐装网的《免责声明、》！')
            return false;
        };
        window.order({
            extra: {
                cs: $('input[name=city]').attr('data-id'),
                qx: $('input[name=area]').attr('data-id'),
                mianji: mianji,
                tel: tel
            },
            submitBtn:{
                className:$(this),
                type:'btn'
            },
            error: function () {
                 
                alert("发生了未知的错误,请稍后再试！");
            },
            success: function (data, status, xhr) {
                 
                if (data.status == 1) {
                    // people = people + 3;
                    window.location.href = "/gzbjjg?leixing=" + $("#style").find("option:selected").text();
                } else {
                    alert('网络异常，请稍后再试');
                }
            },
            validate:function(item, value, method, info){
                return true;
            }
        });
    });
    var mySwiper = new Swiper('.swiper-one', {
        autoplay: false, //可选选项，自动滑动
        loop: true,
        observer: true,//修改swiper自己或子元素时，自动初始化swiper
        observeParents: true,//修改swiper的父元素时，自动初始化swiper
        autoplayDisableOnInteraction: false,
        onSlideChangeStart: function (swiper) {
            index = swiper.activeIndex - 1;
            if (index == 3) {
                index = 0;
            }
            $(".txt-p01").css({'color':'#fff',"border-bottom":'none'});
            $(".txt-p01:eq(" + index + ")").css({"color":"#476A6A","border-bottom":'3px solid #476A6A'});
        },
    });
    $('.threelis').on('click','li',function(){
        var index = $(this).index();
        $(".txt-p01").css({'color':'#fff',"border-bottom":'none'});
        $(".txt-p01:eq(" + index + ")").css({"color":"#476A6A","border-bottom":'3px solid #476A6A'});
        mySwiper.slideTo(index+1, 1000, false);
    })
});