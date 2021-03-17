/*
* @Author: qz_xsc
* @Date:   2018-05-05 12:09:09
* @Last Modified by:   qz_xsc
* @Last Modified time: 2018-05-25 11:32:14
*/
var curr=0;
var length=$(".lun_box li").length;
function lun_slider_prev(box){
     var curr=$(".curr");
     var banner_list=$(box).children("li");
     banner_list.attr({
         "class": ''
     });
     var length=banner_list.length;
     setPrev(curr);
     if(curr.next().html()){
        setCurr(curr.next());
        if(curr.next().next().html()){
            setNext(curr.next().next())
        }else{
            setNext(banner_list.eq(0));

        }
     }else{
        setCurr(banner_list.eq(0));
        setNext(banner_list.eq(1));
     }
     for(var i=0; i<banner_list.length;i++){
        var className=banner_list.eq(i).attr("class");
        if(className==""){
            banner_list.eq(i).attr("style","");
        }
     }

}

function lun_slider_next(box){
    var curr=$(".curr");
    var banner_list=$(box).children("li");
    var length=banner_list.length;
    banner_list.attr({
         "class": ''
    });
    setNext(curr);
    if(curr.prev().html()){
        setCurr(curr.prev());
        if(curr.prev().prev().html()){
            setPrev(curr.prev().prev());
        }else{
            setPrev(banner_list.eq(length-1));
        }
    }else{

        setCurr(banner_list.eq(length-1));
        setPrev(banner_list.eq(length-1).prev());
    }
    for(var i=0; i<banner_list.length;i++){
        var className=banner_list.eq(i).attr("class");
        if(className==""){
            banner_list.eq(i).attr("style","");
        }
     }
}

setCurr($(".lun_box li").eq(0));
setNext($(".lun_box li").eq(1));
setPrev($(".lun_box li").eq($(".lun_box li").length-1));
function setCurr(ele){
    ele.stop().animate({
        height:"340px",
        width:"930px",
        top:"0px",
        background:"black",
        left:"50%",
        right:"",
        marginLeft:"-465px"
    },100).fadeIn(100).attr("class","curr");
}
function setPrev(ele){
    ele.stop().animate({
        height:"300px",
        width:"320px",
        top:"20px",
        background:"blue",
        marginLeft:"0px",
        left:"0px",
        right:"890px"
    },100).fadeIn(100).attr("class","prev");
}
function setNext(ele){
     ele.stop().animate({
        height:"300px",
        width:"320px",
        top:"20px",
        background:"blue",
        marginLeft:"0px",
        right:"0px",
        left:"890px"
    },100).fadeIn(100).attr("class","next");
}

var flag=true;
var lunboTime=setInterval(function(){
    lun_slider_prev(".lun_box");
    flag=true;
},3000);
var lock=true;
var leftTime=0;
$(".next_banner").click(function(e) {

     lun_slider_prev(".lun_box");

});
$(".prev_banner").click(function() {
    lun_slider_next(".lun_box");
});

$(".prev_banner,.next_banner,.lun_box li").hover(
    function(){
        clearInterval(lunboTime);
    },
    function(){
    lunboTime=setInterval(function(){
        lun_slider_prev(".lun_box");
        flag=true;
    },3000);
})


