<?php

namespace app\index\controller\v1;

use think\Request;
use think\Controller;
use app\common\enum\BaseStatusCodeEnum;

class Index extends Controller {

    // 路由验证
    public function index(Request $request) {
        $data = [
            "client_ip" => $request->ip(),
            "user_agent" => $request->header("user-agent")
        ];

        return sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
    }
}
