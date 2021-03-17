$(function () {

    //banner图片动画
    $(".banner3").animate({
        top:280
    },500,function () {
        $(".banner2").animate({
            top:183
        },500,function () {
            $('.banner1').animate({
                top:25
            },500);
        })
    });

    //别墅装修案例
    $(".tit-fenlei ul li").click(function () {
        var index = $(this).index();
        $(".tit-fenlei ul li").removeClass('active');
        $(this).addClass('active');
        $(".anli-img-list img").hide();
        $(".anli-img-list").find('.imglist'+(index+1)).show()
    });
    var index = 0;
    var timer = '';
    //别墅保障服务
    $(window).scroll(function () {
        $(".fuwu-list1").animate({
            opacity:1
        },1000,function () {
            $(".fuwu-list2").animate({
                opacity:1
            },1000,function () {
                $(".fuwu-list3").animate({
                    opacity:1
                },1000,function () {
                    $(".fuwu-list4").animate({
                        opacity:1
                    },1000,function () {
                        $(".fuwu-list5").animate({
                            opacity:1
                        },1000,function () {
                            $(".fuwu-list6").animate({
                                opacity:1
                            },1000)
                        })
                    })
                })
            })
        })
    });

    $(".designer-list").mouseenter(function () {
        $(".designer-list").removeClass('active');
        $(this).addClass('active');
        $(this).find('.designer-mask').css('display','block');
    }).mouseleave(function () {
        $(".designer-list").removeClass('active');
        $(this).find('.designer-mask').css('display','none');
    });
    //点击预约设计
    $(".designer-btn").click(function () {
        $('html, body').animate({
            scrollTop: 1400
        }, 1000)
    });
    //预约定制
    $(".order-btn").click(function () {
        var cs = $("#order-cs").val();
        var qy = $('#order-qy').val();
        var tele = $(".telephone").val();
        var reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        if(cs == ''){
            $(".error-city").html('请选择城市');
            return false
        }else{
            $(".error-city").html('');
        }
        if(qy == ''){
            $(".error-area").html('请选择区域');
            return false
        }else{
            $(".error-area").html('');
        }
        if(tele == ''){
            $(".error-tel").html('请输入您的手机号码');
            return false
        }else if(!reg.test(tele)){
            $(".error-tel").html('请输入正确的11位手机号');
            return false
        }else{
            $(".error-tel").html('');
        }
        window.order({
            extra:{
                tel:tele,
                cs:cs,
                qx:qy,
                source:19121107
            },
            success:function (data, status, xhr) {
                if(data.status == 1){
                    $.ajax({
                        url: '/poplayer/pop',
                        type: 'GET',
                        dataType: 'JSON',
                        data: {
                            type: 'yysuccess'
                        }
                    })
                        .done(function (data) {
                            if (data.status == 0) {
                                $("body").append(data.tmp);
                            }
                        });
                }
            },
            error:function (data) {
                alert(data.info)
            }
        })
    });
})
