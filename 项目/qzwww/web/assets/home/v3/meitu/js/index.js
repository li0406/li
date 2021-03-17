/**
 * Created by Administrator on 2016/6/14.
 */

$(window).scroll(function(){
    var scrollTop = document.documentElement.scrollTop || document.body.scrollTop ;
    var height = $(".nav1,.nav").offset().top + $(".nav1").outerHeight()+10;
    if(scrollTop > height){
        $(".zb_footer_box").show();
        $(".zb_footer_box_little").show();
    }else{
        $(".zb_footer_box").hide();
        $(".zb_footer_box_little").hide();
    }
});


$(".zb_footer_box .zb_close").click(function(event) {
    animate($(".zb_footer_box"),$(".zb_footer_box_little"));
});

function animate(from,to){
    var length = from.outerWidth();
    from.animate({"left":"-="+length},800,function(){
        to.animate({"left":"0"},800);
    });
}
$(".zb_footer_box_little").click(function(event) {
    animate($(".zb_footer_box_little"),$(".zb_footer_box"));
});

//返回头部
$(".fanhui").click(function(){
    $('body,html').animate({scrollTop:0},1000);
    return false;
});

$(function(){
    var i=0;
    $(".lg-down").click(function(){
        i++;
        if(i%2!=0){
            $(this).css('transform','rotate(180deg)').parent(".lg-h").stop().animate({height: '80px'},600);
        }else{
            $(this).css('transform','rotate(0deg)').parent(".lg-h").stop().animate({"height":"41px"},600);
        }
    });
});

$(function(){
    $('.topic li').click(function(){
        $(this).addClass('color-border').siblings().removeClass('color-border');
        //var index = $(this).index();
        //$('.img-box').eq(index).show().siblings().hide();
    });
});



