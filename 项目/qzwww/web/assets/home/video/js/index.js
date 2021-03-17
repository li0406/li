/**
 * Created by Administrator on 2016/6/14.
 */
//<!--报价表单-->
$(function(){
    $('.f_box .f_item .switch_box').click(function(){
        var type = $(this).attr('data-type');
        $('.f_t_box').removeClass('show');
        $('.f_t_box[data-type="'+type+'"]').addClass('show');
        switch(type)
        {
            case "sj":
                $('.f_box').css('background-color',"#64647c");
                break;
            case "bj":
                $('.f_box').css('background-color',"#8485b7");
                break;
        }
    })
});

$(function(){
    $(".fix_nav").hover(function(){
        $(".fix_nav .moqu_header").addClass('moqu_header_active');
    },function(){
        $(".fix_nav .moqu_header").removeClass('moqu_header_active');
    });

});

//返回头部
$(".fanhui").click(function(){
    $('body,html').animate({scrollTop:0},1000);
    return false;
});
// 顶部banner轮播
$('.newswiperbanner').bxSlider({
    displaySlideQty:1,//显示li的个数
    moveSlideQty: 1,//移动li的个数
    captions: true,//自动控制
    auto: true,
    controls: false//隐藏左右按钮
});
//通栏轮播
$('.zxjt-lunbo').bxSlider({
    displaySlideQty:1,//显示li的个数
    moveSlideQty: 1,//移动li的个数
    captions: true,//自动控制
    auto: true,
    controls: false//隐藏左右按钮
});

$(".lg-down").click(function(){
    var touch = $(this).attr('data-on');
    if('show' == touch){
        $(this).attr('data-on','hidden');
        $(this).css('transform','rotate(0deg)').parent(".lg-h").stop().animate({"height":"41px"},600);
    }else{
        $(this).attr('data-on','show');
        var type= $(this).attr('data-type');
        var count = $(this).parent().find('a').length;
        var height = Math.ceil((count+1)/11) * 40 + 1 + 'px';
        $(this).css('transform','rotate(180deg)').parent(".lg-h").stop().animate({height: height},600);
    }
});
$(function(){
    $('.topic li').click(function(){
        $(this).addClass('color-border').siblings().removeClass('color-border');
        //var index = $(this).index();
        //$('.img-box').eq(index).show().siblings().hide();
    });
});

//头部轮播
$(function(){

    //事件模块
    var mytop=100;
    var sx=0;
    var speed=800
    var timer=null;

    $('.all ol li').click(function(e) {
        mytop++
        //角标的工作应用类样式;
        $(this).addClass('current').siblings().removeClass()

        //图片的工作跟角标对应显示；
        var index=$(this).index();
        $('.all ul li').eq(index).css('z-index',mytop).hide().fadeIn()
        //$('.all ul li').eq(index).css({zIndex:num}).hide().fadeIn(speed);
        sx=index;
    });


    //箭头工作
    $('.all .right').click(function(e) {
        sx++;
        mytop++
        if(sx>4){sx=0}
        //都有谁需要跟着这个顺序走。
        $('.all ol li').eq(sx).addClass('current').siblings().removeClass();//角标

        $('.all ul li').eq(sx).css({zIndex:mytop}).hide().fadeIn(speed);
    });

    $('.all .left').click(function(e) {
        sx--;
        mytop++;
        if(sx<0){sx=4}
        //都有谁需要跟着这个顺序走。
        $('.all ol li').eq(sx).addClass('current').siblings().removeClass();

        $('.all ul li').eq(sx).css({zIndex:mytop}).hide().fadeIn(speed);
    });




    //自动播放模块
    timer=setInterval(autoplay,2000)
    function autoplay(){
        mytop++
        sx++
        if(sx>4){sx=0}
        $('.all ol li').eq(sx).addClass('current').siblings().removeClass();
        $('.all ul li').eq(sx).css({zIndex:mytop}).hide().fadeIn(speed);
    }

    //鼠标移上停止定时器模块
    $('.all').hover(function(e) {
        //停止自动播放，清除定时器；
        clearInterval(timer)
        //$('.all span').fadeIn();
    },function(e){
        clearInterval(timer)
        timer=setInterval(autoplay,2000)
        //$('.all span').fadeOut()
    });

})






