<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
Route::domain('account', function () {

    //已登录无法访问
    Route::group('', function () {
        Route::get('/login', 'index/Login/index');       //登陆页面
        Route::get('/register', 'index/Login/register');       //注册页面
        Route::get('/three', 'index/Login/three');       //三方登录-账号绑定页面
        Route::get('/qrcode', 'index/Qrcode/index');       //二维码登陆-非app登陆时页面
        Route::get('/resetpwd', 'index/Login/resetpwd');   //忘记密码

    })->middleware(['UcenterAuthRedirect']);

    //未登录访问
    Route::group('', function () {

        //账号中心
        Route::get('/account/home', 'index/Account/index');       //账号中心首页
        Route::get('/account/setting', 'index/Account/setting');       //基本信息设置页面
        Route::get('/account/security', 'index/Account/security');       //密保认证页面
        Route::get('/account/setpassword', 'index/Account/setpassword');       //修改密码页面

        //账号中心---帮助中心
        Route::get('/blackboard/help', 'index/Help/index');       //帮助中心页面
    })->middleware(['UcenterAuthCheck']);

});


// miss路由
Route::miss(function () {
    return redirect(config('SPACE_HOST'));
});



Route::domain('oauthtmp', function () {
    // 动态注册域名的路由规则
    Route::get('/loginfromsina', 'index/Login/sinalogin');       //微博页面
    Route::get('/loginfromqq', 'index/Login/qqlogin');       //qq页面
    Route::get('/loginfromwechat', 'index/Login/wxlogin');
});

