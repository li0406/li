/*
* @Author: qz_dc
* @Date:   2018-08-01 14:59:53
* @Last Modified by:   qz_dc
* @Last Modified time: 2018-08-17 17:06:09
*/
$(function(){
    $("#check").addClass('fa-check');
    var check = $("#mianze").is(':checked');
    if(!check){
        $("#check").removeClass('fa-check');

    }
    $("#check2").addClass('fa-check');
    var check = $("#mianze2").is(':checked');
    if(!check){
        $("#check2").removeClass('fa-check');

    }

    $("body").on("click",".disclamer-check",function(){
        var hasChecked=$(this).attr("data-checked");
         if(hasChecked=="true"){
            $(this).html("");
            $(this).attr("data-checked",false);
         }else{
            $(this).html("<i class='fa fa-check'></i>");
            $(this).attr("data-checked",true);
         }
    });
})
function checkDisclamer(parent_box){
    if(!parent_box){
        console.error("请选择父容器");
        return false;
    }
    var disclamer =$(parent_box+" .disclamer-check").attr("data-checked");
    if(disclamer=="false"){
        alert("请勾选我已阅读并同意齐装网的《免责声明》！");
        return false;
    }
    return true;
}


/**
 * 发单按钮置灰处理
 * btn: 发单按钮对象
 * btnType:发单按钮类型，img为图片
 * btnStatus：发单状态。success 请求完毕
 *  
 * */ 
function orderButtonChangeStatus(btn, btnType,btnStatus){
    if(!btn){
        return
    }
    var activeClass = btnType&&btnType=="img"?'disable-order-status-opactivity':'disable-order-status'
    if(btnStatus&&btnStatus=="success"){
        setTimeout(function(){
            btn.removeClass(activeClass)
        },2000)
        return
    }
    btn.addClass(activeClass)
}