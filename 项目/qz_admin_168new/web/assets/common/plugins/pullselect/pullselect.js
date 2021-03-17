/*
* @Author: Administrator
* @Date:   2018-09-25 14:26:53
* @Last Modified by:   qz_xsc
* @Last Modified time: 2018-09-26 09:16:36
*/


function pullSelect(option){
    $(".pull-select-container").remove();
    var defaultOptions={
        position:"bottom",
        data:[],
        searchFun:function(){},
        cancelFun:function(){}
    }
    var parms= $.extend(defaultOptions,option);
    var dataList=parms.data;
    var leftPx=$(window).width()-160>parms.target.offset().left+parms.target.width()?parms.target.offset().left+parms.target.width():$(window).width()-160,
    topPx=parms.target.offset().top+parms.target.height();

    htmlNode="<div class='pull-select-container'><ul>";
    $.each(dataList,function(index,value){
        htmlNode=htmlNode+"<li  class='pull-select-item'><input type='checkbox' value='"+value.id+"' /><span>"+value.name+"</span></li>";
    });
    htmlNode=htmlNode+"</ul><div class='pull-select-button'><div><span class='ok-select'>确定</span></div><div><span class='cancel-select'>取消</span></div></div></div>";
    $("body").append(htmlNode);

    $(".pull-select-container").css({
        "display":"block",
        "left":leftPx+"px",
        "top":topPx+"px"
    });

     $(".pull-select-item").on("click",function(event){
        var isChecked=$(this).children("input").is(':checked');
        if(isChecked){
            // $(this).removeClass('pull-select-active');
            $(this).children("input").prop("checked",false);
        }else{
            // $(this).addClass('pull-select-active');
            $(this).children("input").prop("checked",true);
        }
    });

    $(".pull-select-item input").on("click",function(event){

        var isChecked=$(this).is(':checked');
        if(isChecked){
            // $(this).parent().removeClass('pull-select-active');
            $(this).prop("checked",false);
        }else{
            // $(this).parent().addClass('pull-select-active');
            $(this).prop("checked",true);
        }
    });

    $(".ok-select").on("click",function(){
        var selectData=[],element=$(".pull-select-item").find('input');
        $.each(element, function(index,value){
            if($(value).is(':checked')){
                var checkData=$(value).val();
                selectData.push(checkData);
            }
        });
        parms.searchFun(selectData);
        $(".pull-select-container").css({
            "display":"none"
        });
    });


    $(".cancel-select").on("click",function(){
        var selectData=[],element=$(".pull-select-item").find('input');
         $(".pull-select-container").css({
            "display":"none"
        });
    });
}

