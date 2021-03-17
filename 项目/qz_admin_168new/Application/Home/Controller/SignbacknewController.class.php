<?php
// +----------------------------------------------------------------------
// | SignbackuserController  签单返点会员
// +----------------------------------------------------------------------

namespace Home\Controller;

use Exception;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\CompanyAccountLogicModel;
use Home\Model\Logic\CompanyRoundOrderLogicModel;
use Home\Model\Logic\SignbacknewLogicModel;

use Common\Enums\AccountPayTypeEnum;
use Common\Enums\DepartmentEnum;

class SignbacknewController extends HomeBaseController {

    // 新签单返点会员列表
    public function accountlist(){
        $input = array(
            "jc" => I("get.jc"),
            "city" => I("get.city"),
            "user_id" => I("get.user_id"),
            "begin" => I("get.begin"),
            "end" => I("get.end"),
            "on_status" => I("get.on_status"),
            "amount_max" => I("get.amount_max"),
            "amount_min" => I("get.amount_min"),
            "mode" => I("get.mode")
        );

        $sort_column = I("get.column");
        $sort_value = I("get.sort");
        $sort = "";

        if ($sort_value != "asc"  && $sort_value != "desc") {
            $sort_value = "asc";
        }

        switch ($sort_column) {
            case "round_money":
                $sort = "a.account_amount ".$sort_value;
                break;
            case "round_count":
                $sort = "a.round_order_number ".$sort_value;
                break;
            case "deposit_money":
                $sort = "a.deposit_money ".$sort_value;
                break;
            case "back_ratio":
                $sort = "s.back_ratio ".$sort_value;
                break;
            case "account_start":
                $sort = "u.`start` ".$sort_value;
                break;
        }

        $signbacknewLogic = new SignbacknewLogicModel();
        $data = $signbacknewLogic->getAccountList($input, $sort);

        $citys = getUserCitys(false);
        
        $prower = [];
        $userinfo = session("uc_userinfo");
        if ($userinfo["department_top_id"] != DepartmentEnum::DEPARTMENT_SALE_CENTER) {
            $prower["account_operator"] = true;
        }

        //统计数据
        $info = $signbacknewLogic->getAccountStat($input);
        $this->assign("info", $info);
        $this->assign("data", $data);
        $this->assign("citys", $citys);
        $this->assign("input", $input);
        $this->assign("prower", $prower);
        $this->display();
    }

    // 轮单记录
    public function round_order_list(){
        $user_id = I("get.user_id");

        $signbacknewLogic = new SignbacknewLogicModel();
        $userinfo = $signbacknewLogic->getInfo($user_id, true);
        $this->assign("userinfo", $userinfo);
        
        $companyRoundOrderLogic = new CompanyRoundOrderLogicModel();
        $data = $companyRoundOrderLogic->getCompanySignOrderList($user_id, 10);

        $this->assign("data", $data);
        $this->display();
    }

    // 签单核销收款
    public function round_order_receive_money(){
        if (IS_AJAX) {
            $id = I("post.id");
            $pay_money = I("post.pay_money", 0, "floatval");

            if ($pay_money <= 0) {
                $this->ajaxReturn(array(
                    "error_code" => 404,
                    "error_msg" => "请先填写收款金额"
                ));
            }

            $companyRoundOrderLogic = new CompanyRoundOrderLogicModel();
            $ret = $companyRoundOrderLogic->setSignPayMoney($id, $pay_money);
            $this->ajaxReturn([
                "error_code" => $ret["errcode"],
                "error_msg" => $ret["errmsg"]
            ]);
        }
    }

    // 签单核销收款审核
    public function round_order_check(){
        if (IS_AJAX) {
            $id = I("post.id");
            $exam_status = I("post.exam_status");

            $companyRoundOrderLogic = new CompanyRoundOrderLogicModel();
            $ret = $companyRoundOrderLogic->setSignOrderExam($id, $exam_status);
            $this->ajaxReturn([
                "error_code" => $ret["errcode"],
                "error_msg" => $ret["errmsg"]
            ]);
        }
    }

    // 签单核销收款日志
    public function round_order_log(){
        if (IS_AJAX) {
            $id = I("get.id");
            $companyRoundOrderLogic = new CompanyRoundOrderLogicModel();
            $list = $companyRoundOrderLogic->getSignOrderLog($id);

            $this->assign("list", $list);
            $context = $this->fetch("round_order_log");

            $this->ajaxReturn([
                "error_code" => 0,
                "error_msg" => "",
                "data" => [
                    "template" => $context
                ]
            ]);
        }
    }


    // 加款/扣款弹窗日志
    public function account_log(){
        $user_id = I("get.user_id");

        $begin = date("Y-m-d", strtotime("-3 month"));
        $end = date("Y-m-d");

        $signbacknewLogic = new SignbacknewLogicModel();
        $userinfo = $signbacknewLogic->getInfo($user_id);

        $companyAccountLogic = new CompanyAccountLogicModel();
        $list = $companyAccountLogic->getAccountLogList($user_id, $begin, $end);

        $this->assign("userinfo", $userinfo);
        $this->assign("list", $list);
        $context = $this->fetch("account_log");

        $this->ajaxReturn([
            "error_code" => 0,
            "error_msg" => "",
            "data" => [
                "template" => $context
            ]
        ]);
    }

    // 获取加款/扣款模板
    public function get_account_temp(){
        $user_id = I("get.user_id");
        $operation_type = I("get.operation_type", 1);

        $signbacknewLogic = new SignbacknewLogicModel();
        $userinfo = $signbacknewLogic->getInfo($user_id);

        $this->assign("userinfo", $userinfo);
        if ($operation_type == 2) {
            $context = $this->fetch("account_deduction");
        } else {
            $payTypeList = AccountPayTypeEnum::getPayTypeList(1);
            $payTypeSelectList = AccountPayTypeEnum::getPayTypeList(2);
            $backRatioList = $signbacknewLogic->getBackRatioList();
            $this->assign("payTypeList", $payTypeList);
            $this->assign("backRatioList", $backRatioList);
            $this->assign("payTypeSelectList", $payTypeSelectList);

            $context = $this->fetch("account_recharge");
        }

        $this->ajaxReturn([
            "error_code" => 0,
            "error_msg" => "",
            "data" => [
                "template" => $context
            ]
        ]);
    }

    // 设置返点比例
    public function account_back_ratio(){
        if (IS_AJAX) {
            $user_id = I("post.user_id");
            $back_ratio = I("post.back_ratio", 0, "intval");

            if (empty($user_id)) {
                $this->ajaxReturn([
                    "error_code" => 4000002,
                    "error_msg" => "缺少数据拒绝请求"
                ]);
            }

            $signbacknewLogic = new SignbacknewLogicModel();
            $ret = $signbacknewLogic->setAccountBackRatio($user_id, $back_ratio);
            if (empty($ret)) {
                $this->ajaxReturn([
                    "error_code" => 5000001,
                    "error_msg" => "操作失败"
                ]);
            }

            $this->ajaxReturn([
                "error_code" => 0,
                "error_msg" => "操作成功"
            ]);
        }
    }

    // 账户充值
    public function account_recharge(){
        if (IS_AJAX) {
            $userinfo = session("uc_userinfo");
            if ($userinfo["department_top_id"] == DepartmentEnum::DEPARTMENT_SALE_CENTER) {
                $this->ajaxReturn([
                    "error_code" => 404,
                    "error_msg" => "您没有权限进行此操作"
                ]);
            }

            $user_id = I("post.user_id");
            $account_type = I("post.account_type");
            $trade_amount = I("post.trade_amount");
            $trade_remark = I("post.trade_remark", "");
            $trade_child_type = I("post.trade_child_type", 0);

            try {
                if (empty($user_id) || !in_array($account_type, CompanyAccountLogicModel::$account_type)) {
                    throw new Exception("缺少数据拒绝请求", 4000002);
                } else if (empty($trade_amount)) {
                    throw new Exception("请输入加款金额", 4000002);
                } else if ($trade_child_type == 13 && empty($trade_remark)) {
                    throw new Exception("请输入加款备注", 4000002);
                }

                $companyAccountLogic = new CompanyAccountLogicModel();
                $ret = $companyAccountLogic->setAccountRecharge($user_id, $account_type, $trade_amount, $trade_remark, 0, $trade_child_type);

            } catch (Exception $e) {
                $ret = [
                    "errcode" => $e->getCode(),
                    "errmsg" => $e->getMessage()
                ];
            }

            $this->ajaxReturn([
                "error_code" => $ret["errcode"],
                "error_msg" => $ret["errmsg"]
            ]);
        }
    }

    // 账户扣款
    public function account_deduction(){
        if (IS_AJAX) {
            $userinfo = session("uc_userinfo");
            if ($userinfo["department_top_id"] == DepartmentEnum::DEPARTMENT_SALE_CENTER) {
                $this->ajaxReturn([
                    "error_code" => 404,
                    "error_msg" => "您没有权限进行此操作"
                ]);
            }
            
            $user_id = I("post.user_id");
            $trade_type = I("post.trade_type");
            $account_type = I("post.account_type");
            $trade_amount = I("post.trade_amount", 0);
            $trade_remark = I("post.trade_remark", "");
            
            try {
                if (empty($user_id) || !in_array($account_type, CompanyAccountLogicModel::$account_type)) {
                    throw new Exception("缺少数据拒绝请求", 4000002);
                } else if (empty($trade_type)) {
                    throw new Exception("请先选择交易类型", 4000002);
                } else if ($trade_amount && floatval($trade_amount) != $trade_amount) {
                    throw new Exception("扣款金额不合法", 4000002);
                }

                $companyAccountLogic = new CompanyAccountLogicModel();
                $ret = $companyAccountLogic->setAccountDeduction($user_id, $account_type, $trade_amount, $trade_type, $trade_remark);

            } catch (Exception $e) {
                $ret = [
                    "errcode" => $e->getCode(),
                    "errmsg" => $e->getMessage()
                ];
            }

            $this->ajaxReturn([
                "error_code" => $ret["errcode"],
                "error_msg" => $ret["errmsg"]
            ]);
        }
    }


    // 账户交易明细
    public function account_trade_log(){
        $user_id = I("get.user_id");
        $input = array(
            "order_id" => I("get.order_id"),
            "trade_type" => I("get.trade_type"),
            "operation_type" => I("get.operation_type"),
            "begin" => I("get.begin"),
            "end" => I("get.end"),
        );

        // 用户信息查询
        $signbacknewLogic = new SignbacknewLogicModel();
        $userinfo = $signbacknewLogic->getInfo($user_id, true);
        $this->assign("userinfo", $userinfo);

        $companyAccountLogic = new CompanyAccountLogicModel();
        $data = $companyAccountLogic->getTradeLogList($user_id, $input, 20);

        // 账单类型
        $trade_type = CompanyAccountLogicModel::$trade_type;
        $this->assign("trade_type", $trade_type);
        
        // 收支类型
        $operation_type = CompanyAccountLogicModel::$operation_type;
        $this->assign("operation_type", $operation_type);

        $this->assign("input", $input);
        $this->assign("data", $data);
        $this->display();
    }

    // 账户信息导出
    public function account_export(){
        $input = array(
            "jc" => I("get.jc"),
            "city" => I("get.city"),
            "user_id" => I("get.user_id"),
            "begin" => I("get.begin"),
            "end" => I("get.end"),
            "on_status" => I("get.on_status"),
            "amount_max" => I("get.amount_max"),
            "amount_min" => I("get.amount_min")
        );

        $signbacknewLogic = new SignbacknewLogicModel();
        $list = $signbacknewLogic->getAccountExportList($input);

        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            $writer = new \XLSXWriter();

            //标题
            $header = array(
                '序号',
                '会员ID',
                '会员名称',
                '会员状态',
                '城市',
                '轮单总金额',
                '轮单余额',
                '剩余补轮次数',
                '上次加款时间',
                '负责人',
                '保证金',
                '返点比例',
                '合同开始时间'
            );
            $wArr = array_values($header);
            $writer->writeSheetRow('Sheet1', $header);

            foreach ($list as $key => $value) {
                switch ($value["on"]) {
                    case '2':
                        $on_name = "有效会员";
                        break;
                    case '-1':
                        $on_name = "过期会员";
                        break;
                    case '-4':
                        $on_name = "暂停会员";
                        break;
                    case '0':
                        $on_name = "注册会员";
                        break;
                }

                $v = array(
                    $value["index"],
                    $value["id"],
                    $value["jc"],
                    $on_name,
                    $value["city_name"],
                    $value["order_amount"],
                    $value["account_amount"],
                    $value["round_order_number"],
                    $value["last_recharge_date"],
                    $value["saler"],
                    $value["deposit_money"],
                    $value["back_ratio"] . "%",
                    $value["start"]
                );
                $wArr = array_values($v);
                $writer->writeSheetRow('Sheet1', $v);
            }
            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="新会员充值.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        }catch (Exception $e){
            // echo $e->getMessage();exit;
        }
        exit();
    }

    // 交易明细导出
    public function account_trade_export(){
        $user_id = I("get.user_id");
        $input = array(
            "order_id" => I("get.order_id"),
            "trade_type" => I("get.trade_type"),
            "operation_type" => I("get.operation_type"),
            "begin" => I("get.begin"),
            "end" => I("get.end"),
        );

        $companyAccountLogic = new CompanyAccountLogicModel();
        $data = $companyAccountLogic->getTradeLogList($user_id, $input, 0, true);

        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            $writer = new \XLSXWriter();

            //标题
            $header = array(
                '序号' => "integer",
                '流水号' => "string",
                '交易时间' => "string",
                '金额' => "string",
                '账单类型' => "string",
                '收支类型' => "string",
                '订单编号' => "string",
                '业主' => "string",
                '区域' => "string",
                '小区' => "string",
                '装修方式' => "string",
                '房屋类型' => "string",
                '装修类型' => "string",
                '面积' => "string",
                '操作人' => "string",
                '备注' => "string"
            );

            $wArr = array_values($header);
            $writer->writeSheetHeader('Sheet1', $header);

            foreach ($data["list"] as $key => $value) {
                $v = array(
                    $value["index"],
                    $value["trade_no"],
                    $value["created_date"],
                    $value["trade_amount"],
                    $value["trade_type_name"],
                    $value["operation_type_name"],
                    $value["order_id"],
                    $value["yz_name"],
                    $value["area_name"],
                    $value["xiaoqu"],
                    $value["fangshi_name"],
                    $value["huxing_name_show"],
                    $value["round_amount_type_name"],
                    $value["mianji"],
                    $value["operator_name"],
                    $value["trade_remark"]
                );
                $wArr = array_values($v);
                $writer->writeSheetRow('Sheet1', $v);
            }
            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="收支明细-'.$user_id.'.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        }catch (Exception $e){
            // echo $e->getMessage();exit;
        }
        exit();
    }
}