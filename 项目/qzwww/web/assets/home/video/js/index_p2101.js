/*
* @Author: qz_xsc
* @Date:   2018-04-03 08:48:14
* @Last Modified by:   qz_xsc
* @Last Modified time: 2018-04-10 14:05:38
*/
var num=0;
var hoverMenu=null;
function bannerSlide(banner,menu,timeout){
    var speed=timeout;
    hoverMenu=setInterval(function(){
        autoPlay(banner,menu)
    },speed);
    $(menu).hover(function(){
        clearInterval(hoverMenu);
        var index=$(this).index();
        hoverPlay(banner,menu,index);
    },function(){
        num=$(this).index();
        hoverMenu=setInterval(function(){
            autoPlay(banner,menu)
        },speed);
    });

    $("#banner_ul li").hover(function(){
        clearInterval(hoverMenu);
        var index=$(this).index();

        hoverPlay(banner,menu,index);
    },function(){
        num=$(this).index();
        hoverMenu=setInterval(function(){
            autoPlay(banner,menu)
        },speed);
    });

}

function autoPlay(banner,menu){
    var length=$(banner).length;
    var i=num%length;
    $(banner).eq(i).siblings().fadeOut();
    $(banner).eq(i).fadeIn();
    $(menu).eq(i).addClass('tab_active');
    $(menu).eq(i).siblings().removeClass('tab_active');
    $(".banner_win").attr("href",$(banner).eq(i).children('a').attr("href"))
    num++;

}
function hoverPlay(banner,menu,index){
    $(banner).eq(index).siblings().fadeOut();
    $(banner).eq(index).fadeIn();
    $(menu).eq(index).addClass('tab_active');
    $(menu).eq(index).siblings().removeClass('tab_active');
    $(".banner_win").attr("href",$(banner).eq(index).children('a').attr("href"))
}