<?php

namespace app\index\controller\v1;

use think\Request;
use think\Controller;
use app\index\model\logic\TeamLogic;
use app\common\enum\BaseStatusCodeEnum;

class Team extends Controller {

    // 获取销售顶级团队
    public function getTeamTopList(Request $request, TeamLogic $teamLogic) {
        $need_center = $request->get("need_center", 1);
        $list = $teamLogic->getTeamTopList(true);

        if ($need_center == 1) {
            array_unshift($list, [
                "team_id" => "0",
                "team_name" => "销售中心"
            ]);
        }

        $data = [
            "list" => $list
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }
}
