/*
* @Author: qz_xsc
* @Date:   2018-07-16 08:39:52
* @Last Modified by:   qz_chk
* @Last Modified time: 2018-07-19 10:04:25
*/




;$(function(){

    if($(".menu-one").length<3){
        $("#add-menu").css({
            "left":$(".menu-one").length*$("#add-menu").outerWidth()+"px"
        })
    }else{
        $("#add-menu").css({
            "display":"none"
        })

    }



    //排序按钮
    sortAble=false
    $("#sort-order").click(function(event) {
        sortAble=true;
        var status=$(this).attr("data-status");
        if(status=="0"){//排序
            $(".content-box").fadeOut();//隐藏右边编辑
            $(this).attr("class","btn btn-defalut").text("保存");
            $("#add-menu,.menu-two-plus").fadeOut(0);

            //横向初始化
            var lengthX=$(".leve-one-box .menu-one").length;
            var itemWidthX=$(".menu-one").outerWidth();
            $(".leve-one-box").css("position","relative");

            for(var i=0; i<lengthX;i++){
                $(".menu-one").eq(i).css({
                    "left":itemWidthX*i+"px",
                    "position":"absolute"
                }).addClass('waitMove').removeClass('moveStatus').attr({
                    "data-left":itemWidthX*i
                });
                //纵向初始化
                var moveItemY=$(".menu-one").eq(i).find("li");
                var height=50;
                $(".menu-one").eq(i).find("ul").css({
                    "height":height*moveItemY.length,
                    "position":"relative"
                });
                for(var m=0; m<moveItemY.length;m++){//纵向初始化
                    moveItemY.eq(m).css({
                        "top":height*m+"px",
                        "position":"absolute"
                    }).addClass('waitMove').removeClass('moveStatus').attr({
                        "data-top":height*m
                    });
                }
            }
            $(this).attr("data-status","1")
        }else{//保存
            $("#add-menu,.content-box").fadeIn(0);
            $(this).attr("class","btn btn-primary").text("菜单排序");
            $(this).attr("data-status","0");
            $(".menu-one,.menu-two-box li").removeClass('moveStatus').removeClass('waitMove');
            if($(".menu-one").length<3){
                $("#add-menu").css({
                    "left":$(".menu-one").length*$("#add-menu").outerWidth()+"px"
                })
            }else{
                $("#add-menu").css({
                    "display":"none"
                })

            }
            sortAble=false;
        }
    });




    //拖动相关代码
    var initX=0;
    var initY=0;
    var sportX=0;
    var sportY=0;
    var isDown=false;
    var childMove=false;
    var once=true;
    $(document).on("mousedown",".menu-one",function(event){
        if(sortAble){
            isDown=true;
            childMove=false;
            $(this).addClass("moveStatus").removeClass("waitMove");
            $(this).siblings().removeClass('moveStatus').addClass('waitMove')
            initX=event.clientX;
            event.stopPropagation();
        }
    });

    // 横向排序
    $(document).on("mousemove",".menu-one",function(event){
        if(isDown && sortAble){//如果已按下
            var moveX=event.clientX;//滑动坐标
            var sportX=moveX - initX; //计算滑动距离
            var width=$(this).outerWidth();
            var nowLeft=parseInt($(this).attr("data-left"));
            var maxLeft=$(this).siblings().length*width;
            if(Math.abs(sportX)<width*0.6){//滑动距离小于自身宽度一半
                $(this).css({
                    "left":sportX+nowLeft+"px"
                })
            }else{
                var dir=sportX>=0?1:-1;
                if((nowLeft==0&&dir<0)||(nowLeft==maxLeft&&dir>0)){
                    $(this).stop().animate({
                        "left":nowLeft
                    }, 50)

                }else{
                    $(this).stop().animate({
                        "left":dir*width+nowLeft+"px"
                    }, 50).attr("data-left",dir*width+nowLeft);
                    //寻找换位目标

                    var dataLeft=parseInt($(this).attr("data-left"));
                    for(var i=0; i<$(".menu-one").length;i++){
                        var targetLeft=parseInt($(".menu-one").eq(i).attr("data-left"));
                        var index=$(this).index();
                        if(dataLeft==targetLeft && index!=i){//锁定换位目标
                            $(".menu-one").eq(i).animate({//换位目标移动位置
                                "left":-dir*width+targetLeft+"px"
                            }).attr("data-left",-dir*width+targetLeft);
                        }
                    }
                }

                isDown=false;
                var that=$(this);
                setTimeout(function(){
                    that.removeClass("moveStatus").addClass('waitMove');
                },50);
            }
            event.stopPropagation();
        }
    })


    //拖拽
    $(document).on("mousedown",".menu-two-box li",function(event){
        if(sortAble){
            isDown=false;
            childMove=true;
            $(this).addClass("moveStatus").removeClass("waitMove");
            $(this).siblings().removeClass('moveStatus').addClass('waitMove')
            initY=event.clientY;
            event.stopPropagation();
        }
    })

    $(document).on("mousemove",".menu-two-box li", function(){
        if(childMove && sortAble){
            var moveY=event.clientY;//滑动坐标
            var sportY=moveY - initY; //计算滑动距离
            var height=50;
            var nowTop=parseInt($(this).attr("data-top"));
            var maxTop=$(this).siblings().length*height;
            if(Math.abs(sportY)<height*0.6){//滑动距离小于自身宽度一半
                $(this).css({
                    "top":sportY+nowTop+"px"
                })
            }else{
                var dir=sportY>=0?1:-1;
                if((nowTop==0&&dir<0)||(maxTop==nowTop&&dir>0)){
                    $(this).stop().animate({
                        "top":nowTop
                    }, 50)

                }else{

                    $(this).stop().animate({
                        "top":dir*height+nowTop+"px"
                    }, 50).attr("data-top",dir*height+nowTop);

                    //寻找换位目标
                    var dataTop=parseInt($(this).attr("data-top"));
                    var tagertLi=$(this).parent().find("li");
                    for(var i=0; i<tagertLi.length;i++){
                        var targetTop=parseInt(tagertLi.eq(i).attr("data-top"));
                        var index=$(this).index();
                        if(dataTop==targetTop && index!=i){//锁定换位目标
                            tagertLi.eq(i).animate({//换位目标移动位置
                                "top":-dir*height+targetTop+"px"
                            }).attr("data-top",-dir*height+targetTop);
                        }
                    }
                }
                childMove=false;
                var that=$(this);
                setTimeout(function(){
                    that.removeClass("moveStatus").addClass('waitMove');
                },50);
            }
        }
    })

    $(document).on("mouseup",".menu-one, .menu-two-box li", function(event){
        if(sortAble){
            isDown=false;
            childMove=false;
            event.stopPropagation();
        }
    })

    $(document).on("mouseout",".menu-one, .menu-two-box li", function(event){
        if(sortAble){
            isDown=false;
            childMove=false;
            event.stopPropagation();
        }
    })

});