<?php

namespace app\index\controller\v1;

use think\Request;
use think\Controller;

use app\common\model\logic\AdminuserLogic;
use app\common\model\logic\CompanyConsultLogic;
use app\index\validate\CompanyConsultValidate;

use app\common\enum\BaseStatusCodeEnum;
use app\index\enum\CompanyConsultCodeEnum;

class CompanyConsult extends Controller {

    // 装企咨询列表
    public function getConsultList(Request $request, CompanyConsultLogic $companyConsultLogic){
        $input = $request->get();
        $page = $request->get("page", 1);
        $limit = $request->get("limit", 10);

        // 管辖城市
        if ($request->user["role_id"] != 1) {
            $input["citys"] = AdminuserLogic::getCityIds();
            if (empty($input["citys"])) {
                return sys_response(4000001, "暂无权限查看数据");
            } else if (!empty($input["city_id"]) && !in_array($input["city_id"], $input["citys"])) {
                return sys_response(4000001, "暂无权限查看数据");
            }
        }

        // 默认查询时间
        if (empty($input["date_begin"]) && empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-d", strtotime("-6 months"));
            $input["date_end"] = date("Y-m-d");
        }

        $data = $companyConsultLogic->getConsultPageList($input, $page, $limit);

        $response = sys_response(0, "", [
            "list" => $data["list"],
            "page" => $data["page"],
            "options" => [
                "date_begin" => $input["date_begin"],
                "date_end" => $input["date_end"]
            ]
        ]);
        
        return json($response);
    }

    // 装企咨询详情
    public function getConsultDetail(Request $request, CompanyConsultLogic $companyConsultLogic){
        $id = $request->get("id");
        if (empty($id)) {
            return json(sys_response(4000002));
        }

        $detail = $companyConsultLogic->getConsultDetail($id);
        if (empty($detail)) {
            return json(sys_response(4000001));
        }

        $response = sys_response(0, "", [
            "detail" => $detail
        ]);
        return json($response);
    }

    // 装企咨询处理
    public function setConsultDeal(Request $request, CompanyConsultLogic $companyConsultLogic){
        $id = $request->post("id");
        if (empty($id)) {
            return json(sys_response(4000002));
        }

        $ret = $companyConsultLogic->setConsultDeal($id, $request->user);
        $response = sys_response($ret["errcode"], $ret["errmsg"]);
        return $response;
    }


    // 装企咨询添加记录
    public function addConsultRrecord(Request $request, CompanyConsultLogic $companyConsultLogic){
        $input = $request->post();
        $consultValidate = new CompanyConsultValidate();
        if ($consultValidate->scene("addRecord")->check($input) == false) {
            return json(sys_response(4000002, $consultValidate->getError()));
        }

        $input["user_id"] = $request->user["user_id"];
        $input["role_id"] = $request->user["role_id"];
        $ret = $companyConsultLogic->addConsultRecord($input["consult_id"], $input);

        $response = sys_response($ret["errcode"], $ret["errmsg"]);
        return $response;
    }

    // 装企咨询历史记录
    public function getConsultRrecordList(Request $request, CompanyConsultLogic $companyConsultLogic){
        $page = $request->get("page", 1);
        $limit = $request->get("limit", 10);
        $consult_id = $request->get("consult_id");
        if (empty($consult_id)) {
            return json(sys_response(4000002));
        }

        $data = $companyConsultLogic->getRecordPageList($consult_id, $page, $limit);
        $response = sys_response(0, "", $data);
        return json($response);
    }


    // 装企咨询选项
    public function getOptions(Request $request){
        $actfrom = $request->get("actfrom", 1);

        $data = [
            "record_status_list" => CompanyConsultCodeEnum::getList("record_status"),
        ];

        if ($actfrom == 2) {
            unset($data["record_status_list"][0]);
            $data["record_status_list"] = array_values($data["record_status_list"]);

            $data["deal_type_list"] = CompanyConsultCodeEnum::getList("deal_type");
            $data["success_level_list"] = CompanyConsultCodeEnum::getList("success_level");
            $data["success_level_desc"] = CompanyConsultCodeEnum::SUCCESS_LEVEL_DESC;
        } else {
            $data["cooperation_type_list"] = CompanyConsultCodeEnum::getList("cooperation_type");
            $data["operate_status_list"] = CompanyConsultCodeEnum::getList("operate_status");
        }

        $response = sys_response(0, "", $data);
        return json($response);
    }
    
}