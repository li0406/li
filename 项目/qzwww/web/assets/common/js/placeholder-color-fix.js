/**
 * 该文件主要解决IE8,9下，placeholder导致的颜色不符合公司要求及发单成功后清除数据导致的文本框内容为空的情况
 */

/**
 * IE8文字被placeholder插件改变颜色
 */
$(function () {
    $("html,body").on("keyup","input",function(){
        if($(this).val().length>0){
            $(this).css('color',"#333"); // 输入文字后的颜色值
        }else{
            $(this).css('color',"#999"); // 默认状态下的颜色值
        }
    });
    if( checkIE89 ){
        $("html,body").on("blur","input",function(){
            if($(this).val().length<=0){
                $(this).css('color',"#999"); // 输入文字后的颜色值
            }
        });
    }
})

function checkIE89(){
    var browser=navigator.appName
    var b_version=navigator.appVersion
    var version=b_version.split(";");
    var trim_Version=version[1].replace(/[ ]/g,"");
    if(browser=="Microsoft Internet Explorer" && trim_Version=="MSIE8.0")
    {
        return true;
    }
    else if(browser=="Microsoft Internet Explorer" && trim_Version=="MSIE9.0")
    {
        return true;
    }
    return false;
}

function fixPlaceholder($ele){
    if(checkIE89()){
        var txt = $ele.attr("placeholder");
        $ele.val(txt).css("color","#999");
        $ele.on("click",function(){
            $(this).val("").css("color","");
        });
    }else{
        $ele.val("");
    }
    $ele.css("color","#999");
}