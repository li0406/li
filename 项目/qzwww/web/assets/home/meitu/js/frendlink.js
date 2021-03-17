/**
 * +----------------------------------------------------------------------
 * | frendlink
 * +----------------------------------------------------------------------
 * | Copyright (c) 2018 http://gitee/honoryao All rights reserved.
 * +----------------------------------------------------------------------
 * | Author: 姚荣<honoryao@qq.com> 2851986856@qq.com
 * +----------------------------------------------------------------------
 */
$(function(){
    $('.j-tab-nav-link a:first').addClass('current');
    $('.j-tab-con-link div:first').css('display','block');
    //友链
    $("#friend").rTabs({
        btnClass:'.j-tab-nav-link',  /*按钮的父级Class*/
        conClass:'.j-tab-con-link',  /*内容的父级Class*/
        speed:2000,
        auto:false
    });
    $("#friend").find(".tab-nav a:first").addClass('current');
    $("#friend").find(".j-tab-con-link .tab-con-item:first").css("display","block");
});