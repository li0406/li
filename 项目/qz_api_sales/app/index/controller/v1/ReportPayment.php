<?php

namespace app\index\controller\v1;

use think\Request;
use think\Controller;

use app\common\model\logic\TeamLogic;
use app\common\model\logic\AdminuserLogic;
use app\common\model\logic\ReportPaymentLogic;
use app\common\model\logic\CompanyAccountLogic;
use app\index\validate\ReportPaymentValidate;
use app\index\enum\ReportPaymentCodeEnum;
use app\index\enum\SalesReportCodeEnum;

class ReportPayment extends Controller {
    
    /**
     * 小报备列表（销售用）
     * @param  Request            $request            [description]
     * @param  ReportPaymentLogic $reportPaymentLogic [description]
     * @return [type]                                 [description]
     */
    public function getList(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $input = $request->get();
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 20);

        // 管辖城市
        // $input["citys"] = AdminuserLogic::getCityIds();
        // if (empty($input["citys"])) {
        //     return sys_response(4000002, "暂无权限查看数据");
        // } else if (!empty($input["city_id"]) && !in_array($input["city_id"], $input["citys"])) {
        //     return sys_response(4000002, "暂无权限查看数据");
        // }

        $data = $reportPaymentLogic->getPaymentPageList($input, $page, $limit);

        $response = sys_response(0, "", [
            "list" => $data["list"],
            "page" => $data["page"]
        ]);

        return json($response);
    }

    /**
     * 小报备详情
     * @param  Request            $request            [description]
     * @param  ReportPaymentLogic $reportPaymentLogic [description]
     * @return [type]                                 [description]
     */
    public function getDetail(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $id = $request->get("id");
        $act_from = $request->get("act_from");
        if (empty($id)) {
            return json(sys_response(4000002));
        }

        $detail = $reportPaymentLogic->getPaymentDetail($id, true);
        if (empty($detail)) {
            return json(sys_response(4000001));
        }

        // 验证是否可以查看详情
        $ids = AdminuserLogic::getAuthUserids();
        // $ids = TeamLogic::getAccessAuthUsersIDs();
        if ($ids != 0 && !in_array($detail->creator, $ids) && $act_from != 1) {
            return json(sys_response(3000001));
        }

        // if ($detail->cooperation_type == ReportPaymentCodeEnum::COOPERATION_TYPE_USER_REFUND) {
        //     // 限制非总监或助理查看退款报备权限
        //     if(!AdminuserLogic::checkSaleManagerRole()){
        //         return json(sys_response(3000001));
        //     }
        // }

        // 查询余额
        $company_account = [];
        if (!empty($detail["company_id"])) {
            $companyAccountLogic = new CompanyAccountLogic();
            $accountInfo = $companyAccountLogic->getAccountInfo($detail["company_id"]);
            $company_account["account_amount"] = $accountInfo["account_amount"] ?? 0;
            $company_account["deposit_money"] = $accountInfo["deposit_money"] ?? 0;
            $company_account["company_id"] = $detail["company_id"];
        }

        // 报备人所属顶级团队
        $teamLogic = new TeamLogic();
        $topTeamList = $teamLogic->getUserTopTeamId([$detail->creator]);
        if (array_key_exists($detail->creator, $topTeamList)) {
            $topTeam = $topTeamList[$detail->creator];
            $detail["top_team_id"] = $topTeam["team_id"];
            $detail["top_team_name"] = $topTeam["team_name"];
        } else {
            $detail["top_team_id"] = "";
            $detail["top_team_name"] = "";
        }

        $response = sys_response(0, "", [
            "detail" => $detail,
            "company_account" => $company_account
        ]);

        return json($response);
    }

    /**
     * 获取编辑信息
     * @param  Request            $request            [description]
     * @param  ReportPaymentLogic $reportPaymentLogic [description]
     * @return [type]                                 [description]
     */
    public function getEditInfo(Request $request, ReportPaymentLogic $reportPaymentLogic){
        if ($id = $request->get("id")) {
            $detail = $reportPaymentLogic->getPaymentDetail($id);
        }

        // 收款类型
        $paymentTypeDefaultList = ReportPaymentCodeEnum::getPaymentTypeDefaultList();
        $paymentTypeBackList = ReportPaymentCodeEnum::getPaymentTypeBackList();
        $paymentTypeWlList = ReportPaymentCodeEnum::getPaymentTypeWlList();
        $payment_type_groups = [
            0 => ReportPaymentCodeEnum::toKeyValue($paymentTypeDefaultList),
            ReportPaymentCodeEnum::COOPERATION_TYPE_UBACK => ReportPaymentCodeEnum::toKeyValue($paymentTypeBackList),
            ReportPaymentCodeEnum::COOPERATION_TYPE_WL => ReportPaymentCodeEnum::toKeyValue($paymentTypeWlList),
        ];

        $paymentTypeSelectList = ReportPaymentCodeEnum::getPaymentTypeSelectList();

        $response = sys_response(0, "", [
            "detail" => $detail ?? [],
            "back_ratio_list" => SalesReportCodeEnum::getBackRatioList(),
            "payment_payee_list" => ReportPaymentCodeEnum::getPayeeList($request->actfrom),
            "payment_type_list" => ReportPaymentCodeEnum::toKeyValue($paymentTypeSelectList),
            "payment_type_groups" => $payment_type_groups
        ]);

        $cooperation_type_list = ReportPaymentCodeEnum::getKList("cooperation_type");

        if(AdminuserLogic::checkSaleManagerRole()){
            $refundTypeList = ReportPaymentCodeEnum::getRefundTypelList();
            $response['data']['refund_type_list'] = ReportPaymentCodeEnum::toKeyValue($refundTypeList);
        }else{
            unset($cooperation_type_list[ReportPaymentCodeEnum::COOPERATION_TYPE_USER_REFUND]);
        }

        $response['data']['cooperation_type_list'] = array_values($cooperation_type_list);

        return json($response);
    }

    /**
     * 小报备添加、编辑
     * @param  Request            $request            [description]
     * @param  ReportPaymentLogic $reportPaymentLogic [description]
     * @return [type]                                 [description]
     */
    public function saveInfo(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $input = $request->post();

        $scene = "save"; // 常规验证场景
        if ($request->cooperation_type == ReportPaymentCodeEnum::COOPERATION_TYPE_UBACK) {
            $scene = "uback"; // 会员返点验证场景
        } else if ($request->cooperation_type == ReportPaymentCodeEnum::COOPERATION_TYPE_PLATFORM_USE_TURN) {
            $scene = "platformTurn"; // 平台使用费（保证金转）验证场景
        }

        if($request->cooperation_type == ReportPaymentCodeEnum::COOPERATION_TYPE_USER_REFUND){
            $scene = 'refund'; // 退款报备验证
            if(!AdminuserLogic::checkSaleManagerRole()){
                return json(sys_response(5000002, '没有相应权限'));
            }
        }

        $reportPaymentValidate = new ReportPaymentValidate();
        if ($reportPaymentValidate->scene($scene)->check($input) == false) {
            return json(sys_response(4000002, $reportPaymentValidate->getError()));
        }

        // 验证其它报备人
        if (!empty($input["person_list"])) {
            if (!is_array($input["person_list"])) {
                $input["person_list"] = htmlspecialchars_decode($input["person_list"]);
                $input["person_list"] = json_decode($input["person_list"], true);
            }
            $checkret = $reportPaymentValidate->validatePersonList($input["person_list"], $input);
            if ($checkret !== true) {
                return json(sys_response(4000002, $checkret));
            }
        }

        // 验证收款方
        if (!empty($input["payee_list"]) && $request->cooperation_type != ReportPaymentCodeEnum::COOPERATION_TYPE_USER_REFUND) {
            if (!is_array($input["payee_list"])) {
                $input["payee_list"] = htmlspecialchars_decode($input["payee_list"]);
                $input["payee_list"] = json_decode($input["payee_list"], true);
            }

            $checkret = $reportPaymentValidate->validatePayeeList($input["payee_list"], $input);
            if ($checkret !== true) {
                return json(sys_response(4000002, $checkret));
            }
        }

        $ret = $reportPaymentLogic->savePaymentInfo($input);
        $response = sys_response($ret["errcode"], $ret["errmsg"], $ret["data"]);
        return json($response);
    }

    /**
     * 小报备删除
     * @param  Request            $request            [description]
     * @param  ReportPaymentLogic $reportPaymentLogic [description]
     * @return [type]                                 [description]
     */
    public function deleteInfo(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $id = $request->post("id");
        if (empty($id)) {
            return json(sys_response(4000002));
        }

        $ret = $reportPaymentLogic->deletePaymentInfo($id);
        $response = sys_response($ret["errcode"], $ret["errmsg"]);
        return json($response);
    }

    /**
     * 小报备提交
     * @param  Request            $request            [description]
     * @param  ReportPaymentLogic $reportPaymentLogic [description]
     * @return [type]                                 [description]
     */
    public function submitInfo(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $id = $request->post("id");
        if (empty($id)) {
            return json(sys_response(4000002));
        }
        
        $ret = $reportPaymentLogic->submitPaymentInfo($id);
        $response = sys_response($ret["errcode"], $ret["errmsg"]);
        return json($response);
    }

    /**
     * 小报备提交撤回
     * @param  Request            $request            [description]
     * @param  ReportPaymentLogic $reportPaymentLogic [description]
     * @return [type]                                 [description]
     */
    public function submitRecall(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $id = $request->post("id");
        if (empty($id)) {
            return json(sys_response(4000002));
        }
        
        $ret = $reportPaymentLogic->submitRecallPayment($id);
        $response = sys_response($ret["errcode"], $ret["errmsg"]);
        return json($response);
    }

    /**
     * 小报备审核撤回
     * 审核通过后操作，需回滚领导人业绩
     * @param  Request            $request            [description]
     * @param  ReportPaymentLogic $reportPaymentLogic [description]
     * @return [type]                                 [description]
     */
    public function examRollback(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $id = $request->post("id");
        if (empty($id)) {
            return json(sys_response(4000002));
        }
        
        $ret = $reportPaymentLogic->examRollbackPayment($id);
        $response = sys_response($ret["errcode"], $ret["errmsg"]);
        return json($response);
    }

    /**
     * 小报备审核列表
     * @param  Request            $request            [description]
     * @param  ReportPaymentLogic $reportPaymentLogic [description]
     * @return [type]                                 [description]
     */
    public function getExamList(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $input = $request->get();
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 20);

        $data = $reportPaymentLogic->getPaymentExamPageList($input, $page, $limit);

        $response = sys_response(0, "", [
            "list" => $data["list"],
            "page" => $data["page"]
        ]);

        return json($response);
    }

    /**
     * 小报备客服审核列表
     * @param  Request            $request            [description]
     * @param  ReportPaymentLogic $reportPaymentLogic [description]
     * @return [type]                                 [description]
     */
    public function getKfExamList(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $input = $request->get();
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 20);

        $data = $reportPaymentLogic->getPaymentKfExamPageList($input, $page, $limit);

        $response = sys_response(0, "", [
            "list" => $data["list"],
            "page" => $data["page"]
        ]);

        return json($response);
    }


    /**
     * 小报备审核通过
     * @param Request            $request            [description]
     * @param ReportPaymentLogic $reportPaymentLogic [description]
     */
    public function setExamPass(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $id = $request->post("id");
        $actfrom = $request->post("actfrom", 1);
        $isconfirm = $request->post("isconfirm", 1);
        if (empty($id)) {
            return json(sys_response(4000002));
        }
        
        $ret = $reportPaymentLogic->setPaymentExamPass($id, $actfrom, $isconfirm);
        $response = sys_response($ret["errcode"], $ret["errmsg"]);
        return json($response);
    }

    /**
     * 小报备审核不通过
     * @param Request            $request            [description]
     * @param ReportPaymentLogic $reportPaymentLogic [description]
     */
    public function setExamFail(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $id = $request->post("id");
        $reason = $request->post("reason");
        $actfrom = $request->post("actfrom", 1);
        if (empty($id)) {
            return json(sys_response(4000002));
        }
        
        $ret = $reportPaymentLogic->setPaymentExamFail($id, $reason, $actfrom);
        $response = sys_response($ret["errcode"], $ret["errmsg"]);
        return json($response);
    }

    /**
     * 小报备关联大报备
     * @param Request            $request            [description]
     * @param ReportPaymentLogic $reportPaymentLogic [description]
     */
    public function setRelated(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $id = $request->post("id");
        $report_id = $request->post("report_id");
        $report_cooperation_type = $request->post("report_cooperation_type");
        if (empty($id) || empty($report_id) || empty($report_cooperation_type)) {
            return json(sys_response(4000002));
        }

        $typeAll = SalesReportCodeEnum::getCooperationAll();
        if (!in_array($report_cooperation_type, $typeAll)) {
            return json(sys_response(4000002));
        }

        $ret = $reportPaymentLogic->setPaymentRelated($id, $report_id, $report_cooperation_type);
        $response = sys_response($ret["errcode"], $ret["errmsg"]);
        return json($response);
    }

    /**
     * 小报备关联大报备数据比较
     * @param  Request            $request            [description]
     * @param  ReportPaymentLogic $reportPaymentLogic [description]
     * @return [type]                                 [description]
     */
    public function getRelatedCompare(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $id = $request->get("id");
        $report_id = $request->get("report_id");
        $report_cooperation_type = $request->get("report_cooperation_type");
        if (empty($id) || empty($report_id) || empty($report_cooperation_type)) {
            return json(sys_response(4000002));
        }

        $ret = $reportPaymentLogic->getRelatedCompare($id, $report_id, $report_cooperation_type);
        $response = sys_response($ret["errcode"], $ret["errmsg"], $ret["data"]);
        return json($response);
    }

    /**
     * 大报备关联小报备列表
     * @param  Request            $request            [description]
     * @param  ReportPaymentLogic $reportPaymentLogic [description]
     * @return [type]                                 [description]
     */
    public function getRelatedReportList(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $report_id = $request->get("report_id");
        $report_cooperation_type = $request->get("report_cooperation_type");
        if (empty($report_id) || empty($report_cooperation_type)) {
            return json(sys_response(4000002));
        }

        $list = $reportPaymentLogic->getRelatedReportList($report_id, $report_cooperation_type);
        
        $response = sys_response(0, "", [
            "list" => $list
        ]);

        return json($response);
    }

    /**
     * 订单返点小报备列表
     * @param  Request            $request            [description]
     * @param  ReportPaymentLogic $reportPaymentLogic [description]
     * @return [type]                                 [description]
     */
    public function getOrderBackReportList(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $order_id = $request->get("order_id");
        if (empty($order_id)) {
            return json(sys_response(4000002));
        }

        $list = $reportPaymentLogic->getOrderBackReportList($order_id);
        
        $response = sys_response(0, "", [
            "list" => $list
        ]);

        return json($response);
    }


    /**
     * 获取其它业绩人
     * @param  Request            $request            [description]
     * @param  ReportPaymentLogic $reportPaymentLogic [description]
     * @return [type]                                 [description]
     */
    public function getSalerOtherPerson(Request $request, ReportPaymentLogic $reportPaymentLogic){
        $id = $request->get("id");
        $saler_ids = $request->get("saler_ids");
        $saler_name = $request->get("saler_name");

        if (!empty($saler_ids)) {
            if (!is_array($saler_ids)) {
                $saler_ids = explode(",", $saler_ids);
            }
        }

        if (empty($id)) {
            $saler_ids[] = $request->user["user_id"];
        } else {
            $paymentInfo = $reportPaymentLogic->getPaymentInfo($id);
            if (!empty($paymentInfo)) {
                $saler_ids[] = $paymentInfo["creator"];
            }
        }

        $adminuserLogic = new AdminuserLogic();
        $list = $adminuserLogic->getSalerOtherPerson($saler_ids, $saler_name);

        $response = sys_response(0, "", [
            "list" => $list
        ]);

        return json($response);
    }

    /**
     * 获取小报备收款方列表
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getOptions(Request $request){
        $payment_type_list = ReportPaymentCodeEnum::getList("payment_type");

        $response = sys_response(0, "", [
            "payment_payee_list" => ReportPaymentCodeEnum::getPayeeList($request->actfrom),
            "payment_type_list" => $payment_type_list
        ]);

        return json($response);
    }

    /**
     * 获取小报备审核记录
     * @param Request $request
     * @param ReportPaymentLogic $reportPaymentLogic
     * @return array
     */
    public function checkLog(Request $request, ReportPaymentLogic $reportPaymentLogic)
    {
        $id = $request->get("id");
        $list = $reportPaymentLogic->getCheckReportPaymentLog($id);
        return sys_response(0, '', ['list' => $list]);
    }
}