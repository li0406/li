<?php

namespace app\common\model\logic;

use think\Db;
use think\Exception;
use think\facade\Request;

use app\common\model\db\SaleReportPayment;
use app\common\model\db\SaleReportPaymentImg;
use app\common\model\db\SaleReportPaymentLog;
use app\common\model\db\SaleReportPaymentPayee;
use app\common\model\db\SaleReportPaymentPerson;
use app\common\model\db\UserCompanyRoundOrder;
use app\common\model\db\SaleTeamAchievement;
use app\common\model\db\SaleReportSwjCompany;
use app\common\model\db\SaleReportZxCompany;
use app\common\model\db\SaleReportRelated;
use app\common\model\db\Invoice;
use app\common\model\service\MsgService;
use app\index\enum\ReportPaymentCodeEnum;
use app\index\enum\SalesReportCodeEnum;
use Util\Page;

class ReportPaymentLogic {

    protected $reportPaymentModel;

    const EXAM_STATUS_UNSUBMIT = 1; // 未提交状态值
    const EXAM_STATUS_SUBMIT = 2; // 已提交状态值
    const EXAM_STATUS_PASS = 3; // 审核通过状态值
    const EXAM_STATUS_FAIL = 4; // 审核不通过状态值
    const EXAM_STATUS_PASS_TO_KF = 5; // 初审通过状态值
    const EXAM_STATUS_FAIL_KF = 6; // 客服审核不通过状态值

    public function __construct(){
        $this->reportPaymentModel = new SaleReportPayment();
    }

    public function getPaymentInfo($id){
        return $this->reportPaymentModel->getById($id);
    }

    /**
     * 获取小报备列表数据（销售用）
     * @param  [type]  $input [description]
     * @param  integer $page  [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function getPaymentPageList($input, $page = 1, $limit = 10){
        $input["saler_ids"] = AdminuserLogic::getAuthUserids();
        // $input["saler_ids"] = TeamLogic::getAccessAuthUsersIDs();

        $input['fix_status'] = [];
        if($input['pass_status'] ?? ''){
            if($input['pass_status'] == 1){
                $input['fix_status'] = [self::EXAM_STATUS_PASS, self::EXAM_STATUS_PASS_TO_KF];
            }else if($input['pass_status'] == 2){
                $input['fix_status'] = [self::EXAM_STATUS_FAIL, self::EXAM_STATUS_FAIL_KF];
            }
        }

        // 限制非总监或助理查看退款报备权限
        if(!AdminuserLogic::checkSaleManagerRole()){
            $input['limit_refund'] = ReportPaymentCodeEnum::COOPERATION_TYPE_USER_REFUND;
        }

        $count = $this->reportPaymentModel->getPageCount($input);
        $pageobj = new Page($page, $limit, $count);

        if ($count > 0) {
            $list = $this->reportPaymentModel->getPageList($input, $pageobj->firstrow, $pageobj->pageSize);
            if (count($list) > 0) {
                $reportIds = array_column($list->toArray(), "id");
                $paymentPayeeList = $this->getPaymentPayeeList($reportIds); // 查询收款方

                $invoiceModel = new Invoice();
                $invoiceReport = $invoiceModel->getInvoiceByReportPaymentId($reportIds)->toArray();
                $invoiceReport = array_column($invoiceReport, null, 'report_payment_id'); //查询关联的发票

                foreach ($list as $key => $item) {
                    $list[$key] = $this->setPaymentFormatter($item);
                    $list[$key]["time_deposit_money"] = floatval($item["time_deposit_money"]);
                    $list[$key]["payee_list"] = $paymentPayeeList[$item->id] ?? [];

                    if(!empty($invoiceReport[$item->id])){
                        $list[$key]['has_invoice'] = 1;
                    }else{
                        $list[$key]['has_invoice'] = 2;
                    }
                }
            }
        }

        return [
            "count" => $count,
            "list" => $list ?? [],
            "page" => $pageobj->show()
        ];
    }

    /**
     * 小报备审核列表
     * @param  [type]  $input [description]
     * @param  integer $page  [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function getPaymentExamPageList($input, $page = 1, $limit = 10){
        $count = $this->reportPaymentModel->getExamPageCount($input);
        $pageobj = new Page($page, $limit, $count);

        if ($count > 0) {
            $list = $this->reportPaymentModel->getExamPageList($input, $pageobj->firstrow, $pageobj->pageSize);
            if (count($list) > 0) {
                $reportIds = array_column($list->toArray(), "id");

                $paymentImgList = $this->getPaymentAuthImgList($reportIds); // 查询凭证
                $paymentPayeeList = $this->getPaymentPayeeList($reportIds); // 查询收款方
                $companyRepeatCount = $this->getCompanyRepeatCount($reportIds); // 装修公司名称重复次数

                foreach ($list as $key => $item) {
                    $list[$key] = $this->setPaymentFormatter($item);

                    $list[$key]["auth_imgs"] = $paymentImgList[$item->id] ?? []; // 收款凭证
                    $list[$key]["payee_list"] = $paymentPayeeList[$item->id] ?? []; // 收款方
                    $list[$key]["repeat_count"] = $companyRepeatCount[$item->id] ?? 0; // 重复次数
                    
                    $list[$key]["report_finished"] = intval($item->report_status == SalesReportCodeEnum::REPORT_STATUS_KFWC);

                    if($item->report_id > 0 && ($item->cooperation_type == ReportPaymentCodeEnum::COOPERATION_TYPE_PLATFORM_USE)){
                        $list[$key]["report_finished"] = 1;
                    }
                }
            }
        }

        return [
            "count" => $count,
            "list" => $list ?? [],
            "page" => $pageobj->show()
        ];
    }

    /**
     * 小报备客服审核列表
     * @param  [type]  $input [description]
     * @param  integer $page  [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function getPaymentKfExamPageList($input, $page = 1, $limit = 10){
        $count = $this->reportPaymentModel->getKfExamPageCount($input);
        $pageobj = new Page($page, $limit, $count);

        if ($count > 0) {
            $list = $this->reportPaymentModel->getKfExamPageList($input, $pageobj->firstrow, $pageobj->pageSize);
            if (count($list) > 0) {
                $reportIds = array_column($list->toArray(), "id");

                foreach ($list as $key => $item) {
                    $list[$key] = $this->setPaymentFormatter($item);
                }
            }
        }

        return [
            "count" => $count,
            "list" => $list ?? [],
            "page" => $pageobj->show()
        ];
    }


    
    /**
     * 获取小程序详情
     * @return [type] [description]
     */
    public function getPaymentDetail($id, $formatter = false){
        $detail = $this->reportPaymentModel->getPaymentDetail($id);
        if (!empty($detail)) {
            $detail["payment_date"] = date("Y-m-d", $detail["payment_time"]);
            $detail["payment_money"] = floatval($detail["payment_money"]);
            $detail["payment_total_money"] = floatval($detail["payment_total_money"]);
            $detail["order_sign_money"] = floatval($detail["order_sign_money"]);
            $detail["order_back_money"] = floatval($detail["order_back_money"]);
			$detail["other_money"] = floatval($detail["other_money"]);
            $detail['deposit_to_round_money'] = floatval($detail['deposit_to_round_money']);
            $detail['refund_money'] = floatval($detail['refund_money']);
            $detail['platform_money'] = floatval($detail['platform_money']);
            $detail['deposit_to_platform_money'] = floatval($detail['deposit_to_platform_money']);
            $detail['platform_money_start_date'] = $detail['platform_money_start_time'] ? date('Y/m/d', $detail['platform_money_start_time']) : '';
            $detail['platform_money_end_date'] = $detail['platform_money_end_time'] ? date('Y/m/d', $detail['platform_money_end_time']) : '';
            
            if ($formatter == true) {
                $detail = $this->setPaymentFormatter($detail);
            }

            // 判断会员返点类型的小报备收款金额和返点金额是否相等
            $detail["uback_money_compare"] = 2;
            if ($detail["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_UBACK) {
                $detail["total_money"] = $detail["payment_total_money"] + $detail["deposit_money"] + $detail["round_order_money"];
                if ($detail["order_back_money"] == $detail["total_money"]) {
                    $detail["uback_money_compare"] = 1;
                }
            }

            if($detail['cooperation_type'] == ReportPaymentCodeEnum::COOPERATION_TYPE_SBACK_ACCOUNT){
                $companyAccountLogic = new CompanyAccountLogic();
                $accountInfo = $companyAccountLogic->getAccountInfo($detail['company_id']);
                //保证金展示实时余额
                $detail['deposit_money'] = $accountInfo['deposit_money'];
            }

            // 收款方
            $payeeList = $this->getPaymentPayeeList($id);
            $payee_list = $payeeList[$id] ?? [];
            if (count($payee_list) == 1 && $payee_list[0]["payee_money"] == 0) {
                $payee_list[0]["payee_money"] = $detail["payment_total_money"];
            }
            $detail["payee_list"] = $payee_list;

            // 收款凭证
            $authImgList = $this->getPaymentAuthImgList($id);
            $detail["auth_imgs"] = $authImgList[$id] ?? [];

            // 其它业绩人
            $paymentPersonList = $this->getPaymentPersonList($id, SaleReportPaymentPerson::TYPE_PERSON_OTHER);
            $detail["person_list"] = $paymentPersonList[$id] ?? [];

            //审核记录
            $detail['check_log'] = $this->getCheckReportPaymentLog($detail['id']);

            // 兼容老数据：会员费为空时，显示收款金额
            $normType = ReportPaymentCodeEnum::getNormCooperationType();
            if(in_array($detail['cooperation_type'], $normType)){
                if(($detail['platform_money'] + $detail['round_order_money']) == 0){
                    $detail['round_order_money'] = $detail['payment_money'];
                }
            }
        }

        return $detail;
    }

    /**
     * 报备审核通过
     * @param [type] $id [description]
     */
    public function setPaymentExamPass($id, $actfrom = 1, $isconfirm = 1){
        try {
            Db::startTrans();
            $info = $this->reportPaymentModel->getInfo($id);
            if (empty($info) || $info["is_delete"] == 2) {
                throw new Exception("报备信息不存在", 4000001);
            } else if ($actfrom == 1 && $info["exam_status"] != static::EXAM_STATUS_SUBMIT) {
                throw new Exception("该报备不能操作审核", 1000001);
            } else if ($actfrom == 2 && $info["exam_status"] != static::EXAM_STATUS_PASS_TO_KF) {
                throw new Exception("该报备不能操作审核", 1000001);
            }

            // 审核通过操作
            $data = [
                "exam_status" => static::EXAM_STATUS_PASS,
                "updated_at" => time(),
                "exam_time" => time()
            ];

            // 返点小报备客服审核验证保证金轮单费
            if ($actfrom == 2 && $info["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_UBACK) {
                if ($isconfirm == 1) {
                    $companyAccountLogic = new CompanyAccountLogic();
                    $accountInfo = $companyAccountLogic->getAccountInfo($info["company_id"]);
                    $account_amount = $accountInfo["account_amount"] ?? 0;
                    $deposit_money = $accountInfo["deposit_money"] ?? 0;

                    // 验证保证金余额
                    if ($info["deposit_money"] > $deposit_money) {
                        throw new Exception("保证金余额不足", 1100001);
                    }

                    // 验证轮单费余额
                    if ($info["round_order_money"] > $account_amount) {
                        throw new Exception("轮单费余额不足", 1100001);
                    }
                }
            }

            // 平台使用费（保证金转）小报备客服审核验证保证金
            if ($actfrom == 2 && $info["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_PLATFORM_USE_TURN) {
                $companyAccountLogic = new CompanyAccountLogic();
                $accountInfo = $companyAccountLogic->getAccountInfo($info["company_id"]);
                $deposit_money = $accountInfo["deposit_money"] ?? 0;

                // 验证保证金余额
                if ($isconfirm == 1 && $info["deposit_to_platform_money"] > $deposit_money) {
                    throw new Exception("保证金余额不足", 1100001);
                }

                // 客服审核通过记录保证金余额
                $data["deposit_money"] = $deposit_money;
            }

            $notice_saler = 1;
            if ($actfrom == 2) {
                $log_action_type = SaleReportPaymentLog::ACTION_TYPE_EXAMPASS_KF;
                $log_action_remark = "小报备审核通过/客服完成上传";
            } else {
                $log_action_type = SaleReportPaymentLog::ACTION_TYPE_EXAMPASS;
                $log_action_remark = "小报备通过审核";
            }

            // 如果是管理员审核并且需要客服审核则更改审核状态
            if ($actfrom == 1 && $info["need_kf_exam"] == 1) {
                $data["exam_status"] = static::EXAM_STATUS_PASS_TO_KF;
                $log_action_type = SaleReportPaymentLog::ACTION_TYPE_EXAM_TO_KF;
                $log_action_remark = "小报备审核通过/待客服上传";
                $notice_saler = 0;
            }

            $ret = $this->reportPaymentModel->updateInfo($id, $data);
            if ($ret === false) {
                throw new Exception("审核失败", 5000002);
            }

            // 增加操作日志
            $data["id"] = $id;
            $this->addActionLog($id, $log_action_type, $log_action_remark, $data);

            // 审核通过通知销售
            if ($notice_saler == 1) {
                $msgService = new MsgService();
                $msgService->sendSystemMsg("202004141014", $info["creator"], "?", $id, "小报备");
            }

            $errcode = 0;
            $errmsg = "";
            Db::commit();
        } catch (Exception $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
            Db::rollback();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    /**
     * 报备审核不通过
     * @param [type] $id [description]
     */
    public function setPaymentExamFail($id, $reason, $actfrom = 1){
        try {
            Db::startTrans();
            $info = $this->reportPaymentModel->getInfo($id);
            if (empty($info) || $info["is_delete"] == 2) {
                throw new Exception("报备信息不存在", 4000001);
            } else if ($actfrom == 1 && $info["exam_status"] != static::EXAM_STATUS_SUBMIT) {
                throw new Exception("该报备不能操作审核", 1000001);
            } else if ($actfrom == 2 && $info["exam_status"] != static::EXAM_STATUS_PASS_TO_KF) {
                throw new Exception("该报备不能操作审核", 1000001);
            }

            // 审核通过操作
            $data = [
                "exam_status" => static::EXAM_STATUS_FAIL,
                "exam_reason" => $reason ? : "",
                "updated_at" => time(),
                "exam_time" => time()
            ];

            $log_action_type = SaleReportPaymentLog::ACTION_TYPE_EXAMFAIL;
            $log_remark = "小报备审核不通过：". $data["exam_reason"];

            // 客服审核
            if ($info["need_kf_exam"] == 1 && $actfrom == 2) {
                $data["exam_status"] = static::EXAM_STATUS_FAIL_KF;
                $log_action_type = SaleReportPaymentLog::ACTION_TYPE_EXAMFAIL_KF;
                $log_remark = "小报备客服审核不通过：". $data["exam_reason"];
            }

            $ret = $this->reportPaymentModel->updateInfo($id, $data);
            if ($ret === false) {
                throw new Exception("审核失败", 5000002);
            }

            // 增加操作日志
            $data["id"] = $id;
            $this->addActionLog($id, $log_action_type, $log_remark, $data);

            // 报备不通过记录
            $reportUnpassLogLogic = new ReportUnpassLogLogic();
            $reportUnpassLogLogic->addPaymentLog($info, $data);

            // 审核不通过通知销售
            $msgService = new MsgService();
            $msgService->sendSystemMsg("202004141013", $info["creator"], "?", $id, "小报备");

            $errcode = 0;
            $errmsg = "";
            Db::commit();
        } catch (Exception $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
            Db::rollback();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    /**
     * 报备删除
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function deletePaymentInfo($id){
        try {
            Db::startTrans();
            $info = $this->reportPaymentModel->getInfo($id);
            if (empty($info) || $info["is_delete"] == 2) {
                throw new Exception("报备信息不存在", 4000001);
            } else if (!in_array($info["exam_status"], [static::EXAM_STATUS_UNSUBMIT, static::EXAM_STATUS_FAIL])) {
                throw new Exception("该报备不能删除", 1000001);
            }

            // 验证操作人是否有当前操作权限
            $userIds = AdminuserLogic::getAuthUserids();
            // $userIds = TeamLogic::getAccessAuthUsersIDs();
            if (!empty($userIds) && !in_array($info["creator"], $userIds)) {
                throw new Exception("您没有权限进行此操作", 1000001);
            }

            if($info->cooperation_type == ReportPaymentCodeEnum::COOPERATION_TYPE_USER_REFUND){
                if(!AdminuserLogic::checkSaleManagerRole()){
                    throw new Exception("您没有权限进行此操作", 5000002);
                }
            }

            // 删除操作
            $data = [
                "is_delete" => 2,
                "updated_at" => time()
            ];
            $ret = $this->reportPaymentModel->updateInfo($id, $data);
            if ($ret === false) {
                throw new Exception("删除失败", 5000002);
            }

            // 增加删除日志
            $data["id"] = $id;
            $this->addActionLog($id, SaleReportPaymentLog::ACTION_TYPE_DELETE, "小报备删除", $data);

            $errcode = 0;
            $errmsg = "";
            Db::commit();
        } catch (Exception $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
            Db::rollback();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    /**
     * 小报备添加、编辑
     * @param  [type] $input [description]
     * @return [type]        [description]
     */
    public function savePaymentInfo($input){
        try {
            Db::startTrans();

            $input["other_money"] = $input["other_money"] ?? 0;
            $input["deposit_money"] = $input["deposit_money"] ?? 0;
            $input["round_order_money"] = $input["round_order_money"] ?? 0;
            $input["payment_total_money"] = $input["payment_total_money"] ?? 0;
            $input["payment_money"] = $input["payment_total_money"] - $input["deposit_money"] - $input["other_money"];
            $input["payment_time"] = strtotime($input["payment_date"]);
            $input["updated_at"] = time();
            $input["need_kf_exam"] = 2;
            $input['refund_money'] = $input['refund_money'] ?? 0;
            $input['platform_money'] = $input['platform_money'] ?? 0;

            // 处理返点比例（前端传的有undefined需要处理掉）
            if (!empty($input["back_ratio"]) && $input["back_ratio"] != "undefined") {
                $input["back_ratio"] = intval(trim($input["back_ratio"], "%")) . "%";
            } else {
                $input["back_ratio"] = "";
            }

			// 物料类型数据处理
            if ($input["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_WL) {
                $input["payment_type"] = ReportPaymentCodeEnum::PAYMENT_TYPE_WL; // 固定首款类型为物料
                $input["payment_money"] = 0; // 物料类型小报备业绩金额为0

                // 去除合作类型和返点比例
                unset($input["viptype"], $input["back_ratio"]);
            }

            // 会员返点数据补充
            if ($input["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_UBACK) {
                $companyRoundOrderModel = new UserCompanyRoundOrder();
                $roundOrderInfo = $companyRoundOrderModel->getRoundOrderSignInfo($input["order_id"]);
                if (empty($roundOrderInfo)) {
                    throw new Exception("订单不存在", 4000001);
                }

                // 返点比例处理（前端传的带%，计算时不能带）
                $back_ratio = intval(trim($input["back_ratio"], "%"));
                $input["back_ratio"] = $back_ratio . "%";

                $input["city_id"] = $roundOrderInfo["user_city"];
                $input["company_id"] = $roundOrderInfo["company_id"];
                $input["company_name"] = $roundOrderInfo["company_name"];
                $input["order_sign_money"] = $roundOrderInfo["qiandan_jine"] * 10000;
                $input["order_back_money"] = $input["order_sign_money"] * $back_ratio / 100;
                $input["payment_type"] = ReportPaymentCodeEnum::PAYMENT_TYPE_BACK;

                // 会员返点类型的小报备业绩金额等于收款金额+保证金抵扣-其他金额
                $input["payment_money"] = $input["payment_total_money"] + $input["deposit_money"] - $input["other_money"];

                // 如使用了保证金和轮单费抵扣则验证余额是否充足
                if ($input["deposit_money"] > 0 || $input["round_order_money"] > 0) {
                    $input["need_kf_exam"] = 1; // 需要客服审核

                    // 验证余额是否充足
                    $checked = $this->checkAccountAmount($input["company_id"], $input["deposit_money"], $input["round_order_money"]);
                    if ($checked !== true) {
                        throw new Exception($checked, 1000001);
                    }
                }
            }

            // 签返会员（保证金转轮单费）
            if($input["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_SBACK_ACCOUNT){
                $companyAccountLogic = new CompanyAccountLogic();
                $accountInfo = $companyAccountLogic->getAccountInfo($input["company_id"]);
                $deposit_money = $accountInfo['deposit_money'] ?? 0;
                if($deposit_money < $input['deposit_to_round_money']){
                    throw new Exception('保证金抵扣轮单费不可大于保证金余额', 1000001);
                }

                $input['payment_total_money'] = 0;
                $input['deposit_money'] = $accountInfo['deposit_money'];
                $input['payment_money'] = $input['deposit_to_round_money'];
                $input['payment_type'] = ReportPaymentCodeEnum::PAYMENT_TYPE_XUFEI;
                $report = $this->reportPaymentModel->getAlreadyReport($input['company_id'], $input['id'] ?? '')->toArray();
                if($report){
                    throw new Exception('该会员已有相同业务在审核流程中,请仔细核对', 1000001);
                }
            }

            // 会员退款
            if ($input["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_USER_REFUND) {
                $input['payment_money'] = $input["refund_money"] * -1;
            }

            // 平台使用费（保证金转）
            if ($input["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_PLATFORM_USE_TURN) {
                $input['payment_money'] = $input["deposit_to_platform_money"]; // 业绩金额为保证金转平台使用费金额
                $input["payment_type"] = ReportPaymentCodeEnum::PAYMENT_TYPE_XUFEI; // 强制收款类型为续费
                $input["payment_total_money"] = 0; // 收款金额为0
                $input["deposit_money"] = 0; // 保证金余额为0（客服审核通过后记录）
                $input["need_kf_exam"] = 1; // 需要客服审核

                $companyAccountLogic = new CompanyAccountLogic();
                $accountInfo = $companyAccountLogic->getAccountInfo($input["company_id"]);
                $deposit_money = $accountInfo['deposit_money'] ?? 0;
                if($deposit_money < $input['deposit_to_platform_money']){
                    throw new Exception('抵扣金额不得超出保证金余额', 1000001);
                }
            }

            // 平台使用费
            if ($input["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_PLATFORM_USE) {
                $input["platform_money"] = $input['payment_total_money'];
                $input['payment_money'] = $input['payment_total_money'];
            }

            // 处理平台使用费有效期
            if($input['platform_money'] == 0 && empty($input["deposit_to_platform_money"])){
                $input['platform_money_start_time'] = 0;
                $input['platform_money_end_time'] = 0;
            }else{
                $input['platform_money_start_time'] = isset($input['platform_money_start_date']) ? strtotime($input["platform_money_start_date"]) : 0;
                $input['platform_money_end_time'] = isset($input['platform_money_end_date']) ? strtotime($input["platform_money_end_date"]) : 0;
            }

            if (!empty($input["id"])) {
                $can_edit_status = [
                    static::EXAM_STATUS_UNSUBMIT,
                    static::EXAM_STATUS_FAIL,
                    static::EXAM_STATUS_FAIL_KF
                ];

                $info = $this->reportPaymentModel->getInfo($input["id"]);
                if (empty($info) || $info["is_delete"] == 2) {
                    throw new Exception("报备信息不存在", 4000001);
                } else if (!in_array($info["exam_status"], $can_edit_status)) {
                    throw new Exception("该报备信息已经提交不能编辑", 1000001);
                }

                $ret = $this->reportPaymentModel->updateInfo($input["id"], $input);
                $input["creator"] = $info["creator"];
            } else {
                $input["created_at"] = time();
                $input["creator"] = Request::instance()->user["user_id"];
                $ret = $input["id"] = $this->reportPaymentModel->addInfo($input);
            }

            if ($ret === false) {
                throw new Exception("保存失败", 5000002);
            }

            // 处理收款方
            $input["payee_list"] = $input["payee_list"] ?? [];
            $retPayee = $this->setReportPaymentPayee($input["id"], $input["payee_list"]);
            if (empty($retPayee)) {
                throw new Exception("保存失败", 5000002);
            }

            // 处理汇款凭证
            $input["auth_imgs"] = $input["auth_imgs"] ?? "";
            $retImg = $this->setReportPaymentImg($input["id"], $input["auth_imgs"]);
            if (empty($retImg)) {
                throw new Exception("保存失败", 5000002);
            }
        
            // 处理业绩人
            $input["person_list"] = $input["person_list"] ?? [];
            $retPerson = $this->setReportPaymentPerson($input["id"], $input["creator"], $input["person_list"]);
            if (empty($retPerson)) {
                throw new Exception("保存失败", 5000002);
            }

            // 是否需要提交
            if (empty($input["is_submit"])) {
                $this->addActionLog($input["id"], SaleReportPaymentLog::ACTION_TYPE_SAVE, "小报备保存", $input);
            } else {
                $ret = $this->setSubmitPaymentInfo($input["id"], $input);
                if ($ret == false) {
                    throw new Exception("保存失败", 5000002);
                }
            }
            
            $errcode = 0;
            $errmsg = "";
            $data = ["id" => $input["id"]];
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
            $data = null;
        }

        return [
            "errcode" => $errcode,
            "errmsg" => $errmsg,
            "data" => $data
        ];
    }

    /**
     * 报备信息提交
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function submitPaymentInfo($id){
        try {
            Db::startTrans();

            $can_edit_status = [
                static::EXAM_STATUS_UNSUBMIT,
                static::EXAM_STATUS_FAIL,
                static::EXAM_STATUS_FAIL_KF
            ];

            $info = $this->reportPaymentModel->getInfo($id);
            if (empty($info) || $info["is_delete"] == 2) {
                throw new Exception("报备信息不存在", 4000001);
            } else if (!in_array($info["exam_status"], $can_edit_status)) {
                throw new Exception("该报备信息已经提交", 1000001);
            }

            // 验证操作人是否有当前操作权限
            $userIds = AdminuserLogic::getAuthUserids();
            // $userIds = TeamLogic::getAccessAuthUsersIDs();
            if (!empty($userIds) && !in_array($info["creator"], $userIds)) {
                throw new Exception("您没有权限进行此操作", 1000001);
            }

            if($info->cooperation_type == ReportPaymentCodeEnum::COOPERATION_TYPE_USER_REFUND){
                if(!AdminuserLogic::checkSaleManagerRole()){
                    throw new Exception("您没有权限进行此操作", 5000002);
                }
            }

            // 如果是会员返点类型的报备并且需要客服审核则验证余额是否充足
            if ($info["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_UBACK && $info["need_kf_exam"] == 1) {
                $checked = $this->checkAccountAmount($info["company_id"], $info["deposit_money"], $info["round_order_money"]);
                if ($checked !== true) {
                    throw new Exception($checked, 1000001);
                }
            }

            // 签返会员（保证金转轮单费）
            if($info["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_SBACK_ACCOUNT){
                $companyAccountLogic = new CompanyAccountLogic();
                $accountInfo = $companyAccountLogic->getAccountInfo($info['company_id']);
                $info['deposit_money'] = $accountInfo['deposit_money'] ?? 0;
                if($info['deposit_money'] < $info['deposit_to_round_money']){
                    throw new Exception('保证金抵扣轮单费不可大于保证金余额', 1000001);
                }

                $report = $this->reportPaymentModel->getAlreadyReport($info['company_id'], $id)->toArray();
                if($report){
                    throw new Exception('该会员已有相同业务在审核流程中,请仔细核对', 1000001);
                }
            }

            // 平台使用费（保证金转）
            if($info["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_PLATFORM_USE_TURN){
                $companyAccountLogic = new CompanyAccountLogic();
                $accountInfo = $companyAccountLogic->getAccountInfo($info['company_id']);
                $deposit_money = $accountInfo['deposit_money'] ?? 0;
                if($deposit_money < $info['deposit_to_platform_money']){
                    throw new Exception('抵扣金额不得超出保证金余额', 1000001);
                }
            }

            // 提交操作
            $ret = $this->setSubmitPaymentInfo($id, [], $info->toArray());
            if ($ret === false) {
                throw new Exception("提交失败", 5000002);
            }
            
            $errcode = 0;
            $errmsg = "";
            Db::commit();
        } catch (Exception $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
            Db::rollback();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    /**
     * 提交撤回
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function submitRecallPayment($id){
        try {
            Db::startTrans();
            $info = $this->reportPaymentModel->getInfo($id);
            if (empty($info) || $info["is_delete"] == 2) {
                throw new Exception("报备信息不存在", 4000001);
            } else if ($info["exam_status"] != static::EXAM_STATUS_SUBMIT) {
                throw new Exception("该报备信息不能撤回", 1000001);
            }

            // 验证操作人是否有当前操作权限
            $userIds = AdminuserLogic::getAuthUserids();
            // $userIds = TeamLogic::getAccessAuthUsersIDs();
            if (!empty($userIds) && !in_array($info["creator"], $userIds)) {
                throw new Exception("您没有权限进行此操作", 1000001);
            }

            if($info->cooperation_type == ReportPaymentCodeEnum::COOPERATION_TYPE_USER_REFUND){
                if(!AdminuserLogic::checkSaleManagerRole()){
                    return json(sys_response(5000002, '您没有权限进行此操作'));
                }
            }

            // 撤回提交操作
            $data = [
                "submit_time" => 0,
                "updated_at" => time(),
                "exam_status" => static::EXAM_STATUS_UNSUBMIT
            ];
            $ret = $this->reportPaymentModel->updateInfo($id, $data);
            if ($ret === false) {
                throw new Exception("撤回失败", 5000002);
            }

            // 增加提交撤回日志
            $data["id"] = $id;
            $this->addActionLog($id, SaleReportPaymentLog::ACTION_TYPE_RECALL, "小报备撤回提交", $data);

            $errcode = 0;
            $errmsg = "";
            Db::commit();
        } catch (Exception $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
            Db::rollback();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    /**
     * 驳回审核（需回滚领导人业绩）
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function examRollbackPayment($id){
        try {
            Db::startTrans();
            $info = $this->reportPaymentModel->getById($id);
            if (empty($info) || $info["is_delete"] == 2) {
                throw new Exception("报备信息不存在", 4000001);
            } else if ($info["exam_status"] != static::EXAM_STATUS_PASS) {
                throw new Exception("该报备信息不能驳回", 1000001);
            }

            // 验证操作人是否有当前操作权限
            // $userIds = AdminuserLogic::getAuthUserids();
            // if (!empty($userIds) && !in_array($info["creator"], $userIds)) {
            //     throw new Exception("您没有权限进行此操作", 1000001);
            // }

            // 撤回提交操作
            $data = [
                "is_statistic" => 0,
                "exam_time" => time(),
                "updated_at" => time(),
                "exam_status" => static::EXAM_STATUS_FAIL,
            ];
            $ret = $this->reportPaymentModel->updateInfo($id, $data);
            if ($ret === false) {
                throw new Exception("驳回失败", 5000002);
            }

            $logData = $data;
            $logData["id"] = $id;

            // 如果已经统计过业绩则业绩回滚
            if ($info["is_statistic"] == 1 && $info["payment_money"] > 0) {
                // 查询业绩人业绩
                $reportPaymentPersonModel = new SaleReportPaymentPerson();
                $personList = $reportPaymentPersonModel->getPaymentPerson($id);

                // 实例化业绩-下面循环使用
                $teamAchievementModel = new SaleTeamAchievement();

                foreach ($personList as $key => $item) {
                    $achievement = $info["payment_money"] * $item["share_ratio"] / 100;
                    if ($achievement > 0) {
                        // 个人业绩回滚
                        $teamAchievementModel->salerAchievementRollback($item["saler_id"], $achievement, $info["payment_time"]);

                        // 记录个人业绩回滚
                        $logData["saler_avievement"][] = [
                            "saler_id" => $item["saler_id"],
                            "achievement" => $achievement
                        ];

                        // 团队领导人业绩回滚
                        $team_leaders = explode(",", $item["team_leaders"]);
                        $team_leaders = array_filter(array_unique($team_leaders));
                        if (count($team_leaders) > 0) {
                            $teamAchievementModel->teamLeadersAchievementRollback($team_leaders, $achievement, $info["payment_time"]);

                            // 记录领导人业绩回滚
                            $logData["team_leader_avievement"][] = [
                                "saler_id" => $item["saler_id"],
                                "team_leaders" => $item["team_leaders"],
                                "achievement" => $achievement
                            ];
                        }

                        // 团队业绩回滚
                        $team_ids = explode(",", $item["team_ids"]);
                        $team_ids = array_filter(array_unique($team_ids));
                        if (count($team_ids) > 0) {
                            $teamAchievementModel->teamAchievementRollback($team_ids, $achievement, $info["payment_time"]);

                            // 记录团队业绩回滚
                            $logData["team_avievement"][] = [
                                "saler_id" => $item["saler_id"],
                                "team_ids" => $item["team_ids"],
                                "achievement" => $achievement
                            ];
                        }
                    }

                    // 内存回收
                    unset($achievement, $team_leaders);
                }
            }

            // 增加提交撤回日志
            $this->addActionLog($id, SaleReportPaymentLog::ACTION_TYPE_ROLLBACK, "小报备审核驳回", $logData);

            $errcode = 0;
            $errmsg = "";
            Db::commit();
        } catch (Exception $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
            Db::rollback();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }


    /**
     * 小报备关联大报备
     * @param [type] $id        [description]
     * @param [type] $report_id [description]
     */
    public function setPaymentRelated($id, $report_id, $report_cooperation_type){
        try {
            Db::startTrans();
            $info = $this->reportPaymentModel->getInfo($id);
            if (empty($info) || $info["is_delete"] == 2) {
                throw new Exception("报备信息不存在", 4000001);
            } else if ($info["exam_status"] != static::EXAM_STATUS_PASS) {
                throw new Exception("该报备信息不能报备会员", 1000001);
            } else if ($info["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_UBACK) {
                throw new Exception("会员返点类型的小报备不能关联大报备", 1000001);
            }

            // 验证大报备是否存在
            if ($report_cooperation_type == SalesReportCodeEnum::REPORT_COOPERATION_SWJ) {
                $reportSwjCompanyModel = new SaleReportSwjCompany();
                $reportInfo = $reportSwjCompanyModel->getById($report_id, ["id", "status"]);
            } else {
                $reportZxCompanyModel = new SaleReportZxCompany();
                $reportInfo = $reportZxCompanyModel->getById($report_id, ["id", "status"]);
            }

            if (empty($reportInfo)) {
                throw new Exception("大报备信息不存在", 4000001);
            } else if (!in_array($reportInfo->status, SalesReportCodeEnum::getPyamentRelatedStatus())) {
                throw new Exception("该大报备不能被关联", 1000001);
            }

            // 验证原关联大报备状态
            $reportRelatedModel = new SaleReportRelated();
            $statusLock = SalesReportCodeEnum::getLockRelatedStatus();
            $relatedInfo = $reportRelatedModel->getReportRelatedInfo($id);
            if (!empty($relatedInfo) && in_array($relatedInfo["status"], $statusLock)) {
                throw new Exception("小报备关联的大报备已审核通过，无法二次关联其他大报备", 1000001);
            }

            // 删除小报备的原有关联并增加新的关联
            $reportRelatedModel->deletePaymentRelated($id);
            $ret = $reportRelatedModel->addRelated($id, $report_id, $report_cooperation_type);
            if (empty($ret)) {
                throw new Exception("操作失败", 5000002);
            }

            $data = [
                "is_related" => 2,
                "updated_at" => time()
            ];
            $ret = $this->reportPaymentModel->updateInfo($id, $data);
            if ($ret === false) {
                throw new Exception("操作失败", 5000002);
            }

            // 增加操作日志
            $data["id"] = $id;
            $data["report_id"] = $report_id;
            $this->addActionLog($id, SaleReportPaymentLog::ACTION_TYPE_RELATED, "小报备会员报备", $data);

            $errcode = 0;
            $errmsg = "";
            Db::commit();
        } catch (Exception $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
            Db::rollback();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    /**
     * 小报备关联大报备数据比较
     * @param [type] $id        [description]
     * @param [type] $report_id [description]
     */
    public function getRelatedCompare($id, $report_id, $report_cooperation_type){
        try {
            Db::startTrans();
            $info = $this->reportPaymentModel->getPaymentDetail($id);
            if (empty($info) || $info["is_delete"] == 2) {
                throw new Exception("报备信息不存在", 4000001);
            } else if ($info["exam_status"] != static::EXAM_STATUS_PASS) {
                throw new Exception("该报备信息不能报备会员", 1000001);
            }

            // 验证大报备是否存在
            if ($report_cooperation_type == SalesReportCodeEnum::REPORT_COOPERATION_SWJ) {
                $reportSwjCompanyModel = new SaleReportSwjCompany();
                $reportInfo = $reportSwjCompanyModel->getById($report_id);
            } else {
                $reportZxCompanyModel = new SaleReportZxCompany();
                $reportInfo = $reportZxCompanyModel->getById($report_id);
            }

            if (empty($reportInfo)) {
                throw new Exception("大报备信息不存在", 4000001);
            } else if (!in_array($reportInfo->status, SalesReportCodeEnum::getPyamentRelatedStatus())) {
                throw new Exception("该大报备不能被关联", 4000001);
            }

            $data = [
                "city_compare" => 1,
                "cooperation_type_compare" => 1,
                "viptype_compare" => 1,
                "amount_compare" => 1,
                "all_compare" => 1,
                "msg_info" => "",
                "msgs" => []
            ];

            // 城市是否一致（三维家没有城市）
            if ($reportInfo["cooperation_type"] != SalesReportCodeEnum::REPORT_COOPERATION_SWJ) {
                if ($info["city_id"] != $reportInfo["cs"] && $info["city_name"] != $reportInfo["city_name"]) {
                    $data["city_compare"] = 2;
                    $data["msgs"][] = "城市";
                }
            }

            // 合作类型是否一致
            if ($info["cooperation_type"] != $reportInfo["cooperation_type"]) {
                $data["cooperation_type_compare"] = 2;
                $data["msgs"][] = "合作类型";
            }

            // 倍数是否一致（三维家没有倍数）
            if ($reportInfo["cooperation_type"] != SalesReportCodeEnum::REPORT_COOPERATION_SWJ) {
                if ($info["viptype"] != $reportInfo["viptype"]) {
                    $data["viptype_compare"] = 2;
                    $data["msgs"][] = "倍数";
                }
            }

            // 收款是否一致
            if ($info["payment_money"] != $reportInfo["current_contract_amount"]) {
                $data["amount_compare"] = 2;
                $data["msgs"][] = "收款";
            }

            if (count($data["msgs"]) > 0) {
                $data["all_compare"] = 2;
                $msgs = implode("、", $data["msgs"]);
                $data["msg_info"] = sprintf("小报备与大报备中%s不一致，确定要关联吗？", $msgs);
            }
            
            unset($data["msgs"]);
            
            $errcode = 0;
            $errmsg = "";
            Db::commit();
        } catch (Exception $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
            $data = null;
            Db::rollback();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg, "data" => $data];
    }

    /**
     * 订单返点小报备列表
     * @param  [type] $order_id [description]
     * @return [type]           [description]
     */
    public function getOrderBackReportList($order_id){
        $list = $this->reportPaymentModel->getOrderBackReportList($order_id);
        if (!empty($list)) {
            $reportIds = array_column($list->toArray(), "id");

            $paymentPayeeList = $this->getPaymentPayeeList($reportIds); // 查询收款方

            foreach ($list as $key => $item) {
                $list[$key]["deposit_money"] = floatval($item["deposit_money"]);
                $list[$key]["round_order_money"] = floatval($item["round_order_money"]);
                $list[$key]["payment_total_money"] = floatval($item["payment_total_money"]);
                $list[$key]["payment_date"] = date("Y-m-d", $item["payment_time"]);

                $list[$key]["back_money"] = $item["payment_total_money"] + $item["round_order_money"] + $item["deposit_money"];
                $list[$key]["payee_list"] = $paymentPayeeList[$item->id] ?? [];
            }
        }

        return $list;
    }

    /**
     * 订单返点小报备数量
     * @param  [type] $order_id [description]
     * @return [type]           [description]
     */
    public function getOrderBackPaymentCount($order_id){
        $list = $this->reportPaymentModel->getOrderBackReportCount($order_id);
        $orderCountList = array_column($list->toArray(), "count", "order_id");
        return $orderCountList;
    }

    /**
     * 大报备关联小报备列表
     * @param  [type] $report_id  [description]
     * @return [type]             [description]
     */
    public function getRelatedReportList($report_id, $report_cooperation_type){
        $list = $this->reportPaymentModel->getRelatedReportList($report_id, $report_cooperation_type);
        if (!empty($list)) {
            $reportIds = array_column($list->toArray(), "id");

            $paymentImgList = $this->getPaymentAuthImgList($reportIds); // 查询凭证
            $paymentPayeeList = $this->getPaymentPayeeList($reportIds); // 查询收款方

            foreach ($list as $key => $item) {
                $list[$key] = $this->setPaymentFormatter($item);

                $list[$key]["auth_imgs"] = $paymentImgList[$item->id] ?? [];
                $list[$key]["payee_list"] = $paymentPayeeList[$item->id] ?? [];
            }
        }

        return $list;
    }

    /**
     * 获大报备关联小报备情况
     * @return [type] [description]
     */
    public function getPaymentRelatedReport($report_id, $report_cooperation_type){
        $reportRelatedModel = new SaleReportRelated();
        $statistic = $reportRelatedModel->getPaymentStatistic($report_id, $report_cooperation_type);
        $statistic["report_payment_money"] = floatval($statistic["report_payment_money"]);
        return $statistic;
    }

    /**
     * 大报备解除关联
     * @param [type] $report_id [description]
     */
    public function setRelatedRemove($report_id, $report_cooperation_type){
        $reportRelatedModel = new SaleReportRelated();
        $relatedList = $reportRelatedModel->getReportRelated($report_id, $report_cooperation_type);
        if (!empty($relatedList)) {
            $reportPaymentIds = array_column($relatedList->toArray(), "report_payment_id");

            // 删除关联记录
            $reportRelatedModel->deletePeportRelated($report_id, $report_cooperation_type);

            // 释放小报备关联状态
            $data = array(
                "is_related" => 1,
                "updated_at" => time()
            );
            $this->reportPaymentModel->updateAllInfo($reportPaymentIds, $data);

            // 添加小报备解除关联日志
            foreach ($reportPaymentIds as $key => $report_payment_id) {
                $data["report_id"] = $report_id;
                $data["id"] = $report_payment_id;
                $this->addActionLog($report_payment_id, SaleReportPaymentLog::ACTION_TYPE_RELEASE, "大报备删除解除关联", $data);
            }
        }

        return true;
    }

    /**
     * 获取小报备收款凭证
     * @return [type] [description]
     */
    public function getPaymentAuthImgList($payment_id){
        $reportPaymentImgModel = new SaleReportPaymentImg();
        $authImgList = $reportPaymentImgModel->getPaymentAuthImgs($payment_id);

        $paymentImgList = [];
        foreach ($authImgList as $key => $item) {
            $paymentImgList[$item->report_payment_id][] = [
                "img_full" => config("QINIU_HOST") . "/" . $item->img_src,
                "img_src" => $item->img_src,
                "img_px" => $item->img_px
            ];
        }

        return $paymentImgList;
    }

    /**
     * 获取报备业绩人
     * @param  [type]  $payment_id [description]
     * @param  integer $type_id    [description]
     * @return [type]              [description]
     */
    public function getPaymentPersonList($payment_id, $type_id = 0){
        $reportPaymentPersonModel = new SaleReportPaymentPerson();
        $personList = $reportPaymentPersonModel->getPaymentPerson($payment_id, $type_id);

        $paymentPersonList = [];
        foreach ($personList as $key => $item) {
            if ($item->share_ratio > 0) {
                $paymentPersonList[$item->report_payment_id][] = [
                    "saler_id" => $item->saler_id,
                    "saler_name" => $item->saler_name,
                    "share_ratio" => floatval($item->share_ratio),
                    "share_ratio_text" => floatval($item->share_ratio) . "%",
                    "type_id" => $item->type_id
                ];
            }
        }

        return $paymentPersonList;
    }

    /**
     * 获取小报备收款方
     * @param  [type] $payment_id [description]
     * @return [type]             [description]
     */
    public function getPaymentPayeeList($payment_id){
        $reportPaymentPayeeModel = new SaleReportPaymentPayee();
        $payeeList = $reportPaymentPayeeModel->getPaymentPayeeList($payment_id);

        $paymentPayeeList = [];
        foreach ($payeeList as $key => $item) {
            $paymentPayeeList[$item->report_payment_id][] = [
                "payee_type" => $item->payee_type,
                "payee_money" => floatval($item->payee_money),
                "payee_type_name" => ReportPaymentCodeEnum::getPayeeItem($item->payee_type),
                "payee_px" => $item->payee_px
            ];
        }

        return $paymentPayeeList;
    }

    /**
     * 查询装修公司名称重复次数
     * @param  [type] $company_names [description]
     * @return [type]                [description]
     */
    public function getCompanyRepeatCount($reportIds, $days = 3){
        $list = $this->reportPaymentModel->getCompanyRepeatCount($reportIds, $days);
        $companyRepeatCount = array_column($list->toArray(), "repeat_count", "id");
        return $companyRepeatCount;
    }

    /**
     * @param $id
     * @return array
     */
    public function getCheckReportPaymentLog($id)
    {
        $logDb = new SaleReportPaymentLog();
        $list = $logDb->getCheckPaymentLogList($id)->toArray();
        $returnData = [];
        if (count($list) > 0) {
            foreach ($list as $k => $v) {
                $paramter = json_decode($v['paramter'],true);
                $returnData[$k] = [
                    'created_at' => $v['created_at'],
                    'action_type' => SaleReportPaymentLog::getActionType($v['action_type']),
                    'status' => $v['action_type'],
                    'op_name' => $v['operator_name'],
                    'remark' => isset($paramter['exam_reason'])?$paramter['exam_reason']:'',
                ];
            }
        }
        return $returnData;
    }

    /**
     * 报备信息提交操作
     * 调用此方法之前保证数据是校验过的
     * @param [type] $id   [description]
     */
    private function setSubmitPaymentInfo($id, $input = [], $info=[]){
        $data = [
            "updated_at" => time(),
            "submit_time" => time(),
            "exam_status" => static::EXAM_STATUS_SUBMIT
        ];

        $ret = $this->reportPaymentModel->updateInfo($id, $data);
        if ($ret !== false) {
            // 增加提交日志
            $data["id"] = $id;
            $input = array_merge($input, $data);
            $this->addActionLog($id, SaleReportPaymentLog::ACTION_TYPE_SUBMIT, "小报备提交", $input);

            $sendusers = [];
            $msgService = new MsgService();
            $reportMsgSendLogic = new ReportMsgSendLogic();

            // $info = array_merge($info, $input);
            
            // if($info['cooperation_type'] == ReportPaymentCodeEnum::COOPERATION_TYPE_USER_REFUND){
            //     // 退款报备提交至相应副总审核
            //     $reportPaymentPersonModel = new SaleReportPaymentPerson();
            //     $personList = $reportPaymentPersonModel->getPaymentPerson($id, 2);

            //     $sendusers = $reportMsgSendLogic->getExamSalerUsers($personList);
            // }else{
            //     // 小报备提交审核通知蒋总
            //     $sendusers = $reportMsgSendLogic->getExamFirstUsers();
            // }

            // 小报备提交审核通知蒋总
            $sendusers = $reportMsgSendLogic->getExamFirstUsers();

            $msgService->sendSystemMsg("202004141015", $sendusers, "?", $id, "小报备");
        }
        
        return $ret;
    }

    // 处理数据
    private function setPaymentFormatter($item){
        if (!empty($item)) {
            $item["exam_status_name"] = ReportPaymentCodeEnum::getItem("exam_status", $item["exam_status"]);

            if($item["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_USER_REFUND){
                $item["payment_total_money"] = floatval($item["payment_money"]); // 退款报备显示退款金额
            }else{
                $item["payment_total_money"] = floatval($item["payment_total_money"]);
            }

            $item["payment_type_name"] = ReportPaymentCodeEnum::getItem("payment_type", $item["payment_type"]);
            $item["cooperation_type_name"] = ReportPaymentCodeEnum::getItem("cooperation_type", $item["cooperation_type"]);

            if ($item["exam_status"] == static::EXAM_STATUS_PASS && $item["need_kf_exam"] == 1) {
                $item["exam_status_name"] = "审核通过/客服完成上传";
            }

            $item["payment_money"] = floatval($item["payment_money"]);
            $item["round_order_money"] = floatval($item["round_order_money"]);
            $item["deposit_money"] = floatval($item["deposit_money"]);
            $item["deposit_to_round_money"] = floatval($item["deposit_to_round_money"]);
            $item["other_money"] = floatval($item["other_money"]);
            $item["order_sign_money"] = floatval($item["order_sign_money"]);
            $item["order_back_money"] = floatval($item["order_back_money"]);
            $item["order_qiandan_jine"] = floatval($item["order_sign_money"]) / 10000;
            $item['refund_money'] = floatval($item['refund_money']);
            $item['platform_money'] = floatval($item['platform_money']);
            $item["deposit_to_platform_money"] = floatval($item["deposit_to_platform_money"]);
            $item['platform_money_start_date'] = $item['platform_money_start_time'] ? date('Y/m/d', $item['platform_money_start_time']) : '';
            $item['platform_money_end_date'] = $item['platform_money_end_time'] ? date('Y/m/d', $item['platform_money_end_time']) : '';

            $item["payment_date"] = date("Y-m-d", $item["payment_time"]);
            $item["created_date"] = date("Y/m/d H:i", $item["created_at"]);
            $item["updated_date"] = date("Y/m/d H:i", $item["updated_at"]);
            $item["payment_date_show"] = date("Y/m/d", $item["payment_time"]);
            $item["submit_date"] = $item["submit_time"] ? date("Y/m/d", $item["submit_time"]) : "";
        }

        return $item;
    }

    // 处理业绩人
    private function setReportPaymentPerson($report_payment_id, $creator, $person_list){
        // 删除原有业绩人
        $reportPaymentPersonModel = new SaleReportPaymentPerson();
        $reportPaymentPersonModel->deletePaymentPerson($report_payment_id);

        $personRows = [];
        $share_ratio = 100; // 默认报备业绩人分成比例为100%
        if (!empty($person_list) && is_array($person_list)) {
            foreach ($person_list as $key => $item) {
                if (!empty($item["saler_id"]) && !empty($item["share_ratio"])) {
                    if ($item["saler_id"] != $creator) {
                        $share_ratio = $share_ratio - intval($item["share_ratio"]);
                        $personRows[$item["saler_id"]] = [
                            "report_payment_id" => $report_payment_id,
                            "saler_id" => $item["saler_id"],
                            "share_ratio" => intval($item["share_ratio"]),
                            "list_order" => $key + 1,
                            "created_at" => time(),
                            "updated_at" => time(),
                            "type_id" => 2
                        ];
                    }
                }
            }
        }

        if ($share_ratio < 0) {
            throw new Exception("其他业绩人分成比例不能超过100%", 1000001);
        }

        // 添加新的其他业绩人
        if (count($personRows) > 0) {
            $personRows = array_values($personRows);
            $reportPaymentPersonModel->insertAll($personRows);
        }

        // 添加报备业绩人
        $ret = $reportPaymentPersonModel->addPerson([
                "report_payment_id" => $report_payment_id,
                "saler_id" => $creator,
                "share_ratio" => $share_ratio,
                "created_at" => time(),
                "updated_at" => time(),
                "list_order" => 0,
                "type_id" => 1
            ]);

        return $ret;
    }

    // 处理汇款凭证
    private function setReportPaymentImg($report_payment_id, $auth_imgs){
        // 删除原有汇款凭证
        $reportPaymentImgModel = new SaleReportPaymentImg();
        $reportPaymentImgModel->deletePaymentAuthImg($report_payment_id);

        // 添加新的汇款凭证
        $authImgs = explode(",", $auth_imgs);
        $authImgs = array_filter(array_unique($authImgs));

        $ret = true;
        if (!empty($authImgs)) {
            $imgList = [];
            foreach ($authImgs as $key => $value) {
                $imgList[] = [
                    "report_payment_id" => $report_payment_id,
                    "img_src" => $value,
                    "img_px" => $key,
                    "img_type" => 1,
                    "created_at" => time()
                ];
            }
            $ret = $reportPaymentImgModel->addAll($imgList);
        }

        return $ret;
    }

    // 处理收款方
    private function setReportPaymentPayee($report_payment_id, $payee_list){
        // 删除原有收款方
        $reportPaymentPayeeModel = new SaleReportPaymentPayee();
        $reportPaymentPayeeModel->deletePaymentPayee($report_payment_id);

        // 添加新的收款方
        $ret = true;
        if (is_array($payee_list) && count($payee_list) > 0) {
            $rowList = [];
            foreach ($payee_list as $key => $item) {
                $rowList[] = [
                    "report_payment_id" => $report_payment_id,
                    "payee_type" => $item["payee_type"],
                    "payee_money" => $item["payee_money"],
                    "payee_px" => $key,
                    "created_at" => time()
                ];
            }
            
            $ret = $reportPaymentPayeeModel->addAll($rowList);
        }

        return $ret;
    }

    // 检查余额是否充足
    private function checkAccountAmount($company_id, $deposit_money, $round_order_money){
        $companyAccountLogic = new CompanyAccountLogic();
        $accountInfo = $companyAccountLogic->getAccountInfo($company_id);

        $ret = true;
        if (empty($accountInfo)) {
            $ret = "该会员账户信息异常";
        } else if ($accountInfo["deposit_money"] < $deposit_money) {
            $ret = "该会员保证金余额不足！";
        } else if ($accountInfo["account_amount"] < $round_order_money) {
            $ret = "该会员轮单费余额不足！";
        }

        return $ret;
    }

    // 添加日志
    private function addActionLog($id, $action_type, $action_remark = "", $paramter = ""){
        $user = Request::instance()->user;

        return SaleReportPaymentLog::addLog([
                "report_payment_id" => $id,
                "action_type" => $action_type,
                "action_remark" => $action_remark,
                "operator" => $user["user_id"],
                "operator_name" => $user["user_name"],
                "paramter" => is_array($paramter) ? json_encode($paramter) : $paramter,
                "created_at" => time()
            ]);
    }
}
