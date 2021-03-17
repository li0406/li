<?php

namespace app\index\controller\v1;

use think\Request;
use think\Controller;

use app\common\model\logic\WeixinLogLogic;

class Wxlog extends Controller {

    /**
     * 微信通知发送记录
     * @param  Request        $request        [description]
     * @param  WeixinLogLogic $weixinLogLogic [description]
     * @return [type]                         [description]
     */
    public function ordersend(Request $request, WeixinLogLogic $weixinLogLogic){
        $page = $request->param("page");
        $page_size = $request->param("page_size");
        $input = $request->param();

        $page = sys_page_format($page, 1);
        $page_size = sys_page_format($page_size, 10);
        if (empty($input["orderid"]) && empty($input["company_id"]) && empty($input["company_jc"])) {
            $result = ["list" => [], "count" => 0]; // 筛选条件为空时不查询
        } else {
            $result = $weixinLogLogic->getOrdersendPageList($input, $page, $page_size);
        }

        $response = sys_response(0, "", [
            "list" => $result["list"],
            "page" => sys_paginate($result["count"], $page_size, $page)
        ]);

        return json($response);
    }

}