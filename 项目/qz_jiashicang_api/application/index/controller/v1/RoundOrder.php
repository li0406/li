<?php

// +----------------------------------------------------------------------
// | 轮单数据控制器
// +----------------------------------------------------------------------

namespace app\index\controller\v1;

use think\Request;
use think\Exception;
use think\Controller;
use app\index\model\logic\RoundOrderApplyLogic;

use app\index\validate\CityValidate;
use app\index\validate\RoundOrderValidate;
use app\common\enum\BaseStatusCodeEnum;

class RoundOrder extends Controller {

    // 会员数据分析-会员申请补轮明细
    public function getRoundApplyDetailed(Request $request, RoundOrderApplyLogic $applyLogic){
        $input = $request->get();
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 20);

        $checked = RoundOrderValidate::checkApplyDetailedData($input);
        if ($checked !== true) {
            $response = sys_response(4000002, $checked);
            return json($response);
        }

        // 查询城市增加权限城市
        $input["city_ids"] = CityValidate::setAuthCity($input["city_ids"]);

        $data = $applyLogic->getRoundApplyDetailed($input, $page, $limit);

        $data["options"] = [
            "fen_begin" => $input["fen_begin"],
            "fen_end" => $input["fen_end"],
            "apply_begin" => $input["apply_begin"],
            "apply_end" => $input["apply_end"],
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }
}
