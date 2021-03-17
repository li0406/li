<?php

namespace app\index\controller\v1;

use think\Request;
use think\Controller;

use app\common\enum\BaseStatusCodeEnum;
use app\common\model\logic\AdminuserLogic;
use app\common\model\logic\CompanyVisitLogic;
use app\common\model\logic\OrderReviewNewLogic;
use app\index\validate\CompanyVisit as CompanyVisitValidate;

use app\index\enum\CompanyVisitCodeEnum;

class CompanyVisit extends Controller {

    // 获取列表查询选项
    public function getOptions(){
        $options = [
            "visit_step_list" => CompanyVisitCodeEnum::getList("visit_step"),
            "review_type_list" => array_slice(CompanyVisitCodeEnum::getList("review_type"),1),
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $options);
        return json($response);
    }

    // 获取回访单分页列表
    public function getList(Request $request, CompanyVisitLogic $companyVisitLogic, OrderReviewNewLogic $orderReviewNewLogic){
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 20);
        $input = $request->get();

        // 查询时间验证
        $cvValidate = new CompanyVisitValidate();
        $dateRet = $cvValidate->validateSearchDate($input);
        if ($dateRet !== true) {
            $response = sys_response(4000002, $dateRet);
            return json($response);
        }

        // 管辖城市
        if ($request->user["role_id"] != 1) {
            $input["citys"] = AdminuserLogic::getCityIds();
            if (empty($input["citys"])) {
                return sys_response(3000001, "暂无权限查看数据");
            } else if (!empty($input["cs"]) && !in_array($input["cs"], $input["citys"])) {
                return sys_response(3000001, "暂无权限查看数据");
            }
        }
        
        // 产品要求 ，订单号先查询新回访，没有数据在查 老回访
        if(isset($input['order_id']) && $input['order_id']){
            unset($input["valid_status"]); // 查订单时不限制回访状态
            $data = $orderReviewNewLogic->getPageList($input, $page, $limit);
            if(empty($data['list'])){
                $data = $companyVisitLogic->getPageList($input, $page, $limit);
            }
        } else {
            if(isset($input['visit_type']) && $input['visit_type'] == 1){
                $data = $companyVisitLogic->getPageList($input, $page, $limit);
            } else {
                $data = $orderReviewNewLogic->getPageList($input, $page, $limit);
            }
        }
        
        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, [
            "list" => $data["list"],
            "page" => sys_paginate($data["count"], $limit, $page),
        ]);

        return json($response);
    }

    // 回访单详情
    public function getDetail(Request $request, CompanyVisitLogic $companyVisitLogic){
        $id = $request->param("id");
        $order_id = $request->param("order_id");
        $order_type = $request->param("order_type");
        if (empty($id) || empty($order_id) || empty($order_type)) {
            return json(sys_response(4000002));
        }

        // 回访单详情
        $data = $companyVisitLogic->getDetail($id, $order_id, $order_type);
        if (empty($data)) {
            return json(sys_response(4000001));
        }

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 获取新增时下拉选项
    public function getAddOptions(Request $request, CompanyVisitLogic $companyVisitLogic){
        $order_id = $request->param("order_id");
        $options = $companyVisitLogic->getAddOptions($order_id);

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $options);
        return json($response);
    }

    // 新增回访单
    public function addInfo(Request $request, CompanyVisitLogic $companyVisitLogic){
        $input = array(
            "order_id" => $request->param("order_id"),
            "company_ids" => $request->param("company_ids"),
            "visit_step" => $request->param("visit_step"),
            "visit_need" => $request->param("visit_need"),
            "visit_user_need" => $request->param("visit_user_need", "")
        );

        // 验证
        $cvValidate = new CompanyVisitValidate();
        if ($cvValidate->scene("add")->check($input) == false) {
            return json(sys_response(4000002, $cvValidate->getError()));
        }

        $response = $companyVisitLogic->addInfo($input);

        return json($response);
    }

    // 回访单撤回（删除）
    public function deleteInfo(Request $request, CompanyVisitLogic $companyVisitLogic){
        $id = $request->param("id");
        if (empty($id)) {
            return json(sys_response(4000002));
        }

        $response = $companyVisitLogic->deleteInfo($id);
        return json($response);
    }

    // 回访单推送
    public function pushInfo(Request $request, CompanyVisitLogic $companyVisitLogic){
        $input = array(
            "id" => $request->param("id"),
            "push_company_ids" => $request->param("company_ids"),
            "visit_content" => $request->param("visit_content")
        );

        // 验证
        $cvValidate = new CompanyVisitValidate();
        if ($cvValidate->scene("push")->check($input) == false) {
            return json(sys_response(4000002, $cvValidate->getError()));
        }

        // 回访单推送
        $response = $companyVisitLogic->pushInfo($input);
        return json($response);
    }

    public function pushNewInfo(Request $request, CompanyVisitLogic $companyVisitLogic)
    {
        $input = array(
            "order_id" => $request->param("order_id"),
            "push_company_ids" => $request->param("company_ids"),
            "visit_content" => $request->param("visit_content")
        );

        // 验证
        $cvValidate = new CompanyVisitValidate();
        if ($cvValidate->scene("NewPush")->check($input) == false) {
            return json(sys_response(4000002, $cvValidate->getError()));
        }

        // 回访单推送
        $response = $companyVisitLogic->pushNewInfo($input,$request->user);
        return json($response);
    }

    // 导出
    public function exportList(Request $request, CompanyVisitLogic $companyVisitLogic,OrderReviewNewLogic $orderReviewNewLogic){
        $input = $request->param();

        //产品要求 ，如果只查询订单 订单编号 的时候, 再去旧的中查一次数据
        // FIXME
        if(isset($input['order_id']) && $input['order_id'] && empty($data['list'])){
            $data = $orderReviewNewLogic->getExportList($input);
            if(empty($data['list'])){
                $input['visit_type'] = [1,2];
                $data = $companyVisitLogic->getExportList($input);
            }
        }else {
            if(isset($input['visit_type']) && $input['visit_type']==1){
                $data = $companyVisitLogic->getExportList($input);
            }else {
                $data = $orderReviewNewLogic->getExportList($input);
            }
        }


        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, [
            "list" => $data["list"],
        ]);

        return json($response);
    }

}
