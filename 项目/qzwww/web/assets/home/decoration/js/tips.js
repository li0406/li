$(function () {
    var width = $(window).width()
    if(width > 1660){
        $(".carslist-box").animate({
            'left':'16%'
        },10000,function () {
            $(".cars-list img").removeClass('img');
            $(".carslist-box .cars-list").eq(0).find('.cars-jiantou').show();
            $(".tips-des1").show();
        });
    }else{
        $(".carslist-box").animate({
            'left':'6%'
        },10000,function () {
            $(".cars-list img").removeClass('img');
            $(".carslist-box .cars-list").eq(0).find('.cars-jiantou').show();
            $(".tips-des1").show();
        });
    }
    move()
    //banner箭头循环上线移动
    function move(){
        $(".lc-jiantou img").animate({
            marginTop:-10,
            marginBottom:10
        },1000,function () {
            $(".lc-jiantou img").animate({
                marginBottom:-10,
                marginTop:10
            },1000,function () {
                move()
            })
        })
    }

    //小车点击效果
    $(".carslist-box .cars-list").click(function () {
        var index = $(this).index();
        $(".carslist-box .cars-list").find('.cars-jiantou').hide();
        $(".tips-des").hide();
        $(".carslist-box .cars-list").eq(index).find('.cars-jiantou').show();
        $(".tips-des").eq(index).show();
    });
    //立即预约
    $(".tips-btn").click(function () {
        var cs = $(".tips-cs").val();
        var qy = $(".tips-qy").val();
        var tel = $(".tele").val();
        var reg_tel = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
        if(cs == '' || cs === null){
            $(".errorArea-tit").html('请选择城市');
            return false
        }else{
            $(".errorArea-tit").html('');
        }
        if(qy == '' || qy === null){
            $(".errorArea-tit").html('请选择区域');
            return false
        }else{
            $(".errorArea-tit").html('');
        }
        if(tel == ''){
            $(".errorTel-tit").html('请输入您的手机号码');
            return false
        }else if(!reg_tel.test(tel)){
            $(".errorTel-tit").html('请输入正确的11位手机号');
            return false
        }else{
            $(".errorTel-tit").html('');
        }
        window.order({
            extra:{
                tel:tel,
                cs:cs,
                qx:qy,
                source:19121158
            },
            success:function (data, status, xhr) {
                if(data.status == 1){
                    $.ajax({
                        url: '/poplayer/pop',
                        type: 'GET',
                        dataType: 'JSON',
                        data: {
                            type: 'sqsuccess'
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
    })
})
