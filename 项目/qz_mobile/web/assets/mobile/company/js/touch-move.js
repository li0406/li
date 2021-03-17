/*
* @Author: qz_xsc
* @Date:   2017-08-22 11:22:12
* @Last Modified by:   qz_xsc
* @Last Modified time: 2017-08-24 16:33:52
*/
$("document").ready(function(){
   var boxWidth=$(".case-list").width();
   var moveWidth=$("#move-box").width();
   var startX=0,endX=0,moveX=0,positionX=0,moveLong=0;
   $("#move-box").on('touchstart',function(e){
        var touch = e.originalEvent.targetTouches[0];
        startX=touch.pageX;
        positionX=$(this)[0].offsetLeft;
   });
    $("#move-box").on('touchmove',function(e){
        var touch = e.originalEvent.targetTouches[0];
        moveX=touch.pageX;
        moveLong=moveX-startX;
        moveLength=positionX+moveLong;
        $(this).css('left',moveLength+'px');
    });
    $("#move-box").on('touchend',function(e){
        var maxLeft=boxWidth-moveWidth;
        var moveLength=positionX+moveX-startX;
        positionX=$(this)[0].offsetLeft;
        if(moveLong!=0){
            if(positionX>0){
               moveLong=0;
               $(this).css('left',0+'px');
            }else if(positionX<maxLeft){
                moveLong=0;
                 $(this).css('left',maxLeft+'px');
            }else{
                moveLong=0;
                 $(this).css('left',moveLength+'px');
            }
        }else{
            $(this).css('left',positionX+'px');
            moveLong=0;
        }
    })
})