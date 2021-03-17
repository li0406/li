/**
 * Created by Administrator on 2016/6/14.
 */
//头部轮播
$(function() {
    //事件模块
    var mytop = 100;
    var sx = 0;
    var speed = 800
    var timer = null;
    $('.all ol li').click(function(e) {
        mytop++
        //角标的工作应用类样式;
        $(this).addClass('current').siblings().removeClass()

        //图片的工作跟角标对应显示；
        var index = $(this).index();
        $('.all ul li').eq(index).css('z-index', mytop).hide().fadeIn();
        sx = index;
    });


    //箭头工作
    $('.all .right').click(function(e) {
        sx++;
        mytop++
        if (sx > 4) { sx = 0 }
        //都有谁需要跟着这个顺序走。
        $('.all ol li').eq(sx).addClass('current').siblings().removeClass(); //角标

        $('.all ul li').eq(sx).css({ zIndex: mytop }).hide().fadeIn(speed);
    });

    $('.all .left').click(function(e) {
        sx--;
        mytop++;
        if (sx < 0) { sx = 4 }
        //都有谁需要跟着这个顺序走。
        $('.all ol li').eq(sx).addClass('current').siblings().removeClass();

        $('.all ul li').eq(sx).css({ zIndex: mytop }).hide().fadeIn(speed);
    });

    //自动播放模块
    timer = setInterval(autoplay, 2000)

    function autoplay() {
        mytop++
        sx++
        if (sx > 4) { sx = 0 }
        $('.all ol li').eq(sx).addClass('current').siblings().removeClass();
        $('.all ul li').eq(sx).css({ zIndex: mytop }).hide().fadeIn(speed);
    }

    //鼠标移上停止定时器模块
    $('.all').hover(function(e) {
        //停止自动播放，清除定时器；
        clearInterval(timer)
            //$('.all span').fadeIn();
    }, function(e) {
        clearInterval(timer)
        timer = setInterval(autoplay, 2000)
            //$('.all span').fadeOut()
    });

})
