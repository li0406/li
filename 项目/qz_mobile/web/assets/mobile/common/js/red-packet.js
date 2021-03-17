// 红包部分
$(function(){
    var hongbao = localStorage.hongbao;
    if(hongbao == 1){
        $('.red-packet .red_box').css('display','none');
    }
    var timer = setTimeout(function(){
        $('.red-packet .red_box').addClass('animated wobble show');
        var timer1 = setTimeout(function(){
            clearTimeout(timer1);
        },1000)
        clearTimeout(timer);
    },800);
    $('.red-packet .red_box').on('touchend', function(){
        $('.red-packet .red_box_pop').addClass('show').find('.start_box').addClass('show')
    });
    $('.red-packet .close_red_box').on('touchend', function(e){
        $('.red_box').hide();
        return false;
    });
    $('.red-packet .start_box .close_start').on('touchend', function(){
        $('.red-packet .start_box').removeClass('show').parent().removeClass('show');
        return false;
    });
    $('.red-packet .end_box .close_start,.red-packet .close_ok').on('touchend', function(){
        $('.red-packet .end_box').removeClass('show').parent().removeClass('show');
        $('.red-packet .red_box').css('display','none');
        return false;
    });
    //城市选择插件
    selectQz.init({
        province:$(".red-packet input[name=province]").attr("data-id"),
        city:$(".red-packet input[name=city]").attr("data-id"),
        area:$(".red-packet input[name=area]").attr("data-id")
    });
    //红包发单
    $(".red-packet .home-zb .get_free").click(function(event) {
        var container = $(this).parents(".red-packet");
        var name = $(".m-bj-edit input[name=name]").val();
        var tel = $(".m-bj-edit input[name=tel]").val();
        var cs = $('input[name=city]').attr('data-id');
        var qx = $('input[name=area]').attr('data-id');
         
        
        if (!App.validate.run(name)) {
            $(".m-bj-edit input[name=name]").focus();
            alert("请输入您的称呼");
            return false;
        } else {
            var reg = new RegExp("^[\u4e00-\u9fa5a-zA-Z]+$");
            if (!reg.test(name)) {
                $(".m-bj-edit input[name=name]").focus();
                alert("请输入正确的名称，只支持中文和英文");
                return false;
            }
        }
        if (!App.validate.run(tel)) {
            $(".m-bj-edit input[name=tel]").focus();
            alert("请填写正确的手机号码 ^_^!");
            return false;
        } else {
            var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
            if (!reg.test(tel)) {
                $(".m-bj-edit input[name=tel]").focus();
                $(".m-bj-edit input[name=tel]").val('');
                alert("请填写正确的手机号码 ^_^!");
                return false;
            }
        }
        if ('' == cs || '' == qx) {
            alert('请选择您所在的区域 ≧▽≦');
            return false;
        }
         
        window.order({
            extra:{
                cs: cs,
                qx: qx,
                name: $("input[name=name]", container).val(),
                tel: $("input[name=tel]", container).val(),
                fb_type: $("input[name=fb_type]", container).val(),
                source: $("input[name=source]", container).val()
            },
            submitBtn:{
                className:$(this),
                type:'btn'
            },
            error:function(){
                 
            },
            success:function(data, status, xhr){
                 
                if (data.status == 1) {
                    localStorage.hongbao = 1;
                    $('.start_box').removeClass('show');
                    $('.end_box').addClass('show bounceIn animated')
                    var timer2 = setTimeout(function(){
                        $('.end_box').removeClass('bounceIn animated');
                        clearTimeout(timer2)
                    },800)
                } else {
                    alert(data.info);
                }
            },
            validate:function(item, value, method, info){
                return true;
            }
        });
    });
});