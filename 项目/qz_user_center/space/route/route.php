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

Route::group('', function () {
    Route::get('/', 'index/Index/index');       //个人中心首页
    Route::get('/collect', 'index/Collect/index');       //个人收藏页面
})->middleware(['UcenterAuthCheck']);



// miss路由
Route::miss(function () {
    return redirect(config('SPACE_HOST'));
});
