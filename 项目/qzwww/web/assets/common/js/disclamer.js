/*
* @Author: qz_xsc
* @Date:   2018-05-31 14:42:07
* @Last Modified by:   qz_xsc
* @Last Modified time: 2018-06-07 09:30:04
* 免责声明js
*/
$(function(){
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
});
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
