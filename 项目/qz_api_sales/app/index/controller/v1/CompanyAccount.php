<?php

namespace app\index\controller\v1;

use think\Request;
use think\Controller;

use app\common\model\logic\UserLogic;
use app\common\model\logic\AdminuserLogic;
use app\common\model\logic\CompanyAccountLogic;

use app\common\enum\BaseStatusCodeEnum;
use app\index\enum\CompanyAccountCodeEnum;

class CompanyAccount extends Controller {

    // 会员资金管理-列表
    public function getAccountList(Request $request, CompanyAccountLogic $companyAccountLogic){
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

        $data = $companyAccountLogic->getAccountPageList($input, $page, $limit);

        $response = sys_response(0, "", $data);
        return json($response);
    }

    // 会员资金管理-收支明细列表
    public function getAccountLogList(Request $request, CompanyAccountLogic $companyAccountLogic){
        $company_id = $request->get("company_id");
        if (empty($company_id)) {
            return json(sys_response(4000002));
        }

        $input = $request->get();
        $page = $request->get("page", 1);
        $limit = $request->get("limit", 10);

        $data = $companyAccountLogic->getCompanyAccountLogList($company_id, $input, $page, $limit);
        $response = sys_response(0, "", $data);
        return json($response);
    }

    // 会员资金管理-收支明细统计
    public function getAccountLogTotal(Request $request, CompanyAccountLogic $companyAccountLogic){
        $company_id = $request->get("company_id");
        if (empty($company_id)) {
            return json(sys_response(4000002));
        }

        $userLogic = new UserLogic();
        $uinfo = $userLogic->getUserInfo($company_id);
        if (empty($uinfo)) {
            return json(sys_response(4000001));
        }

        $total = $companyAccountLogic->getCompanyAccountLogTotal($company_id);

        $data = [
            "info" => [
                "company_id" => $uinfo["id"],
                "company_name" => $uinfo["jc"]
            ],
            "total" => $total
        ];

        $response = sys_response(0, "", $data);
        return json($response);
    }

}