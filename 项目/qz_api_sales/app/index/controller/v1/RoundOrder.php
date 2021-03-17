<?php

namespace app\index\controller\v1;

use think\App;
use think\Request;
use think\Controller;

use app\common\model\logic\UserLogic;
use app\common\model\logic\AdminuserLogic;
use app\common\model\logic\CompanyRoundOrderLogic;
use app\common\model\logic\CompanyRoundOrderApplyLogic;

class RoundOrder extends Controller {

    // 补轮申请列表
    public function getRoundApplyList(Request $request, CompanyRoundOrderApplyLogic $roundOrderApplyLogic){
        $input = $request->get();
        $page = $request->get("page", 1);
        $limit = $request->get("limit", 10);

        // 如果是销售查询管辖城市
        if ($request->user["is_saler"] == 1) {
            $input["citys"] = AdminuserLogic::getCityIds();
            if (empty($input["citys"])) {
                return sys_response(4000001, "暂无权限查看数据");
            } else if (!empty($input["city_id"]) && !in_array($input["city_id"], $input["citys"])) {
                return sys_response(4000001, "暂无权限查看数据");
            }
        }

        $data = $roundOrderApplyLogic->getRoundOrderApplyList($input, $page, $limit);
        $response = sys_response(0, "", $data);
        return json($response);
    }

    // 会员公司签单记录统计
    public function getCompanySignTotal(Request $request, CompanyRoundOrderLogic $companyRoundOrderLogic){
        $company_id = $request->get("company_id");
        if (empty($company_id)) {
            return json(sys_response(4000002));
        }

        $userLogic = new UserLogic();
        $uinfo = $userLogic->getUserInfo($company_id);
        if (empty($uinfo)) {
            return json(sys_response(4000001));
        }

        // 查询签单统计数据
        $total = $companyRoundOrderLogic->getCompanyRoundOrderSignTotal($company_id);

        $data = [
            "info" => [
                "company_id" => $uinfo["id"],
                "company_name" => $uinfo["jc"],
            ],
            "total" => [
                "sign_number" => $total["sign_number"] ?? 0,
                "qiandan_amount" => $total["qiandan_amount"] ?? 0
            ]
        ];

        return json(sys_response(0, "", $data));
    }
    
    // 会员公司签单记录
    public function getCompanySignList(Request $request, CompanyRoundOrderLogic $companyRoundOrderLogic){
        $page = $request->get("page", 1);
        $limit = $request->get("limit", 10);
        $company_id = $request->get("company_id");
        if (empty($company_id)) {
            return json(sys_response(4000002));
        }

        $data = $companyRoundOrderLogic->getCompanyRoundOrderSignList($company_id, $page, $limit);
        return json(sys_response(0, "", $data));
    }
}