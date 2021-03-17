<?php

namespace app\index\controller\v1;

use app\common\enum\BaseStatusCodeEnum;
use think\Controller;

Class Index extends Controller
{
    public function index()
    {
        return ["error_code" => "0","error_msg" => BaseStatusCodeEnum::CODE_0];
    }

    /**
     * 统一接口返回格式使用示例
     * @Author   lovenLiu
     * @DateTime 2019-05-09T17:55:58+0800
     */
    public function example(){

        // 获取接口返回格式
        $response = sys_response(0, '', [
                'page' => sys_paginate(10, $limit = 20, $page = 1) #分页信息
            ]);

        return json($response);
    }


    public function requestLog(){
        $redis = new \Redis();
        $redis->connect('192.168.8.28', 6379);

        $query_string = $redis->get('SALES:API:QIZUANG:QUERYSTRING');
        return json(json_decode($query_string, true));
    }
}