/*
* @Author: qz_xsc
* @Date:   2018-03-02 15:22:46
* @Last Modified by:   qz_xsc
* @Last Modified time: 2018-03-05 13:37:00
* 消息提示js
*/
var reload=false;
function alert_info_x150(data,isReload){
    if(isReload){
        reload=true;
    }
    $("body").append("<div class='alert_info_mask_x150'><div class='alert_box_x150'><table class='alert_text_x150'><tr><td>"+data+"</td></tr></table><button class='button' id='close_info_x150'>&nbsp;&nbsp;确定&nbsp;&nbsp;</button></div></div>");
}
$("body").on("click","#close_info_x150",function(){
    if(reload){
        $(this).parent().parent(".alert_info_mask_x150").remove();
         window.location.href = window.location.href;
    }else {
        $(this).parent().parent(".alert_info_mask_x150").remove();
    }

});