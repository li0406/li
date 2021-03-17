<?php
// +----------------------------------------------------------------------
// | CompanyAccountLogicModel 会员公司账户逻辑层
// +----------------------------------------------------------------------

namespace Home\Model\Logic;

use Exception;
use Home\Model\Db\UserCompanyAccountLogRoundRelationModel;
use Home\Model\Db\UserModel;
use Home\Model\Db\CompanyModel;
use Home\Model\Db\UserCompanyAccountModel;
use Home\Model\Db\UserCompanyAccountLogModel;
use Home\Model\Service\MsgServiceModel;

use Common\Enums\AccountPayTypeEnum;
use Common\Enums\MsgTemplateCodeEnum;

class CompanyAccountLogicModel {

    const ACCOUNT_TYPE = 1; // 轮单费
    const ACCOUNT_TYPE_BZJ = 2; // 保证金
    const ACCOUNT_TYPE_LDS = 3; // 轮单数

    private $companyAccountModel;

    public static $account_type = [
        self::ACCOUNT_TYPE,
        self::ACCOUNT_TYPE_BZJ
    ];

    // 账户扣款合法交易类型
    private static $trade_account_type = [
        UserCompanyAccountLogModel::LOG_TYPE_DEDUCTION,
        UserCompanyAccountLogModel::LOG_TYPE_ROUND_ORDER,
        UserCompanyAccountLogModel::LOG_TYPE_OTHER,
        UserCompanyAccountLogModel::LOG_TYPE_DEDUCTION_NOTICE,
        UserCompanyAccountLogModel::LOG_TYPE_DEDUCTION_VIOLATION
    ];

    // 保证金扣款合法交易类型
    private static $trade_bzj_type = [
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_DEDUCTION,
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_BACK_PART,
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_BACK_ALL,
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_TO_ROUND,
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_TO_PLATMONEY,
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_DEC_REBATE,
    ];

    // 账单类型
    public static $trade_type = [
        UserCompanyAccountLogModel::LOG_TYPE_RECHARGE => "账户充值",
        UserCompanyAccountLogModel::LOG_TYPE_ROUND_ORDER => "轮单扣费",
        UserCompanyAccountLogModel::LOG_TYPE_DEDUCTION => "其它扣费",
        UserCompanyAccountLogModel::LOG_TYPE_ROUND_BACK => "轮单费返还",
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_RECHARGE => "保证金充值",
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_DEDUCTION => "保证金扣费",
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_BACK_PART => "保证金部分退还",
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_BACK_ALL => "保证金全部退还",
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_TO_ROUND => "保证金转轮单费",
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_TO_PLATMONEY => "保证金转平台使用费",
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_DEC_REBATE => "保证金扣返点",
        UserCompanyAccountLogModel::LOG_TYPE_LDS_INC => "补轮成功",
        UserCompanyAccountLogModel::LOG_TYPE_LDS_DEC => "补轮扣单",
        UserCompanyAccountLogModel::LOG_TYPE_LDS_BACK => "补轮返单",
        UserCompanyAccountLogModel::LOG_TYPE_DEDUCTION_NOTICE => "补轮主动告知扣费",
        UserCompanyAccountLogModel::LOG_TYPE_DEDUCTION_VIOLATION => "补轮违规扣费",
    ];

    public static $operation_type = [
        1 => "收入",
        2 => "支出",
    ];

    // 加款类型
    public static $trade_recharge_type = [
        UserCompanyAccountLogModel::LOG_TYPE_RECHARGE => "账户充值",
        UserCompanyAccountLogModel::LOG_TYPE_ROUND_BACK => "轮单费返还",
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_RECHARGE => "保证金充值"
    ];

    // 扣款类型
    public static $trade_deduction_type = [
        UserCompanyAccountLogModel::LOG_TYPE_ROUND_ORDER => "轮单扣费",
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_BACK_ALL => "保证金全部退还",
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_BACK_PART => "保证金部分退还",
        UserCompanyAccountLogModel::LOG_TYPE_DEDUCTION => "其它扣费",
        UserCompanyAccountLogModel::LOG_TYPE_BZJ_DEDUCTION => "保证金扣费"
    ];

    public function __construct(){
        $this->companyAccountModel = new UserCompanyAccountModel();
    }
    
    // 余额充值
    public function setAccountRecharge($user_id, $account_type, $trade_amount, $trade_remark = "", $gift_amount = 0, $trade_child_type = 0){
        try {
            $this->companyAccountModel->startTrans();
            $accountInfo = $this->getAccountInfo($user_id);

            // 更改修改时间（锁住记录行）
            $this->companyAccountModel->saveData($user_id, ["updated_at" => time()]);

            $ret = false;
            if ($account_type == static::ACCOUNT_TYPE) {
                $trade_type = UserCompanyAccountLogModel::LOG_TYPE_RECHARGE; // 轮单加款类型
                $account_amount = $accountInfo["account_amount"] + $trade_amount; // 交易后轮单余额
                $gift_amount_total = $accountInfo["gift_amount"] + $gift_amount; // 交易后赠送金余额
                $account_amount_total = $account_amount + $gift_amount_total; // 交易后总余额（轮单余额 + 赠送金）
                $ret = $this->companyAccountModel->saveData($user_id, [
                    "account_amount" => $account_amount,
                    "gift_amount" => $gift_amount_total,
                    "last_recharge_time" => time()
                ]);

                // 轮单费到帐提醒模板ID
                $msg_active_name = "first";
                $msg_template_id = MsgTemplateCodeEnum::COMPANY_ACCOUNT_RECHARGE;
            } else if ($account_type == static::ACCOUNT_TYPE_BZJ) {
                $gift_amount = 0;
                $trade_type = UserCompanyAccountLogModel::LOG_TYPE_BZJ_RECHARGE; // 保证金加款类型
                $account_amount_total = $accountInfo["deposit_money"] + $trade_amount; // 交易后保证金余额
                $ret = $this->companyAccountModel->setDepositInc($user_id, $trade_amount);

                // 保证金到账提醒模板ID
                $msg_active_name = "second";
                $msg_template_id = MsgTemplateCodeEnum::COMPANY_DEPOSIT_RECHARGE;
            }

            if (empty($ret)) {
                throw new Exception("操作失败", 5000001);
            }

            // 交易日志
            $log_id = $this->addAccountLog($user_id, $account_type, $trade_amount, $trade_type, $trade_remark, 1, "", $account_amount_total, $gift_amount, $trade_child_type);

            // QZMSG 发送消息提醒
            if (!empty($msg_template_id)) {
                $params = [$trade_amount];
                $msgService = new MsgServiceModel();
                $linkparam = sprintf("?active_name=%s", $msg_active_name);
                $msgService->sendCompanyMsg($msg_template_id, $user_id, $linkparam, $log_id, "新会员充值", $params);
            }

            $errcode = 0;
            $errmsg = "操作成功";
            $this->companyAccountModel->commit();
        } catch (Exception $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
            $this->companyAccountModel->rollback();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    // 余额扣款
    public function setAccountDeduction($user_id, $account_type, $trade_amount, $trade_type, $trade_remark = "", $order_id = "")
    {
        try {
            $this->companyAccountModel->startTrans();
            $accountInfo = $this->getAccountInfo($user_id);

            // 交易类型验证(操作账户类型，补轮扣费对账户)
            if ($account_type == static::ACCOUNT_TYPE && !in_array($trade_type, static::$trade_account_type)) {
                throw new Exception("交易类型不合法", 4000002);
            } else if ($account_type == static::ACCOUNT_TYPE_BZJ && !in_array($trade_type, static::$trade_bzj_type)) {
                throw new Exception("交易类型不合法", 4000002);
            }

            // 验证
            if ($trade_type != UserCompanyAccountLogModel::LOG_TYPE_BZJ_BACK_ALL) {
                if (empty($trade_amount)) {
                    throw new Exception("扣款金额不能为空", 4000002);
                } else if ($account_type == static::ACCOUNT_TYPE && $trade_amount > $accountInfo["account_amount"]) {
                    throw new Exception("扣款金额不得大于轮单余额", 1000001);
                } else if ($account_type == static::ACCOUNT_TYPE_BZJ && $trade_amount > $accountInfo["deposit_money"]) {
                    throw new Exception("扣款金额不得大于保证金余额", 1000001);
                }
            } else if ($accountInfo["deposit_money"] == 0) {
                throw new Exception("保证金已全部返还", 1000001);
            }

            // 更改修改时间（锁住记录行）
            $this->companyAccountModel->saveData($user_id, ["updated_at" => time()]);

            $ret = false;
            if ($account_type == static::ACCOUNT_TYPE) {
                $account_amount = $accountInfo["account_amount"] - $trade_amount; // 交易后轮单余额
                $account_amount_total = $account_amount + $accountInfo["gift_amount"]; // 交易后总余额（轮单金额 + 赠送金额）
                $ret = $this->companyAccountModel->setAccountDec($user_id, $trade_amount); // 轮单余额扣除
                $account_type_name = "轮单费";
                $msg_active_name = "first";
            } else if ($account_type == static::ACCOUNT_TYPE_BZJ) {
                $account_type_name = "保证金";
                $msg_active_name = "second";
                if ($trade_type == UserCompanyAccountLogModel::LOG_TYPE_BZJ_BACK_ALL) {
                    $account_amount_total = 0; // 交易后保证金余额
                    $trade_amount = $accountInfo["deposit_money"]; // 交易金额
                    $ret = $this->companyAccountModel->saveData($user_id, ["deposit_money" => 0]); // 保证金扣除
                } else {
                    $account_amount_total = $accountInfo["deposit_money"] - $trade_amount; // 交易后保证金余额
                    $ret = $this->companyAccountModel->setDepositDec($user_id, $trade_amount); // 保证金扣除
                }
            }

            if ($ret === false) {
                throw new Exception("操作失败", 5000001);
            }

            // 交易日志
            $log_id = $this->addAccountLog($user_id, $account_type, $trade_amount, $trade_type, $trade_remark, 2, $order_id, $account_amount_total);

            // QZMSG 发送消息提醒
            if (!empty($account_type_name)) {
                $params = [$trade_amount, $trade_remark, $account_type_name];
                $msgService = new MsgServiceModel();
                $linkparam = sprintf("?active_name=%s", $msg_active_name);
                $msg_template_id = MsgTemplateCodeEnum::COMPANY_ACCOUNT_DEDUCTION;
                $msgService->sendCompanyMsg($msg_template_id, $user_id, $linkparam, $log_id, "新会员充值", $params);
            }

            $errcode = 0;
            $errmsg = "操作成功";
            $this->companyAccountModel->commit();
        } catch (Exception $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
            $this->companyAccountModel->rollback();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    /**
     * 余额轮单扣款
     * @param $user_id
     * @param $trade_amount
     * @param array $orderInfo 订单信息
     * @param string $trade_remark
     * @param array $amountData 轮单数据
     * @return array
     */
    public function setAccountRoundOrderDeduction($user_id, $trade_amount, $orderInfo, $trade_remark = "", $back_ratio = '', $roundInfo = [])
    {
        try {
            $accountInfo = $this->getAccountInfo($user_id);

            // 验证
            if (empty($trade_amount)) {
                throw new Exception("扣款金额不能为空", 4000002);
            } else if ($trade_amount > $accountInfo["account_amount"] + $accountInfo["gift_amount"]) {
                throw new Exception("扣款金额不得大于账户余额", 1000001);
            }

            $data = ["updated_at" => time()];
            if ($accountInfo["gift_amount"] >= $trade_amount) {
                $data["gift_amount"] = $accountInfo["gift_amount"] - $trade_amount;
                $account_amount_total = $accountInfo["account_amount"] + $data["gift_amount"];
                $gift_amount = $trade_amount;
            } else {
                $data["gift_amount"] = 0;
                $data["account_amount"] = $accountInfo["account_amount"] - ($trade_amount - $accountInfo["gift_amount"]);
                $account_amount_total = $data["account_amount"];
                $gift_amount = $accountInfo["gift_amount"];
            }

            $ret = $this->companyAccountModel->saveData($user_id, $data);
            if ($ret === false) {
                throw new Exception("操作失败", 5000001);
            }

            // 交易日志
            $account_type = static::ACCOUNT_TYPE;
            $trade_type = UserCompanyAccountLogModel::LOG_TYPE_ROUND_ORDER;
            $log_id = $this->addAccountLog($user_id, $account_type, $trade_amount, $trade_type, $trade_remark, 2, $orderInfo['id'], $account_amount_total, $gift_amount);
            //添加轮单交易类型记录
            if ($log_id) {
                if ($trade_type == UserCompanyAccountLogModel::LOG_TYPE_ROUND_ORDER) {
                    $this->addAccountLogRound($log_id, $roundInfo['type'], $roundInfo['price'], $orderInfo['mianji'], $roundInfo['mianji']);
                }
            }

            $errcode = 0;
            $errmsg = "操作成功";
        } catch (Exception $e) {
            throw  new \Think\Exception($e->getMessage(),$e->getCode());
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    /**
     * 余额轮单费返还
     * @param $user_id
     * @param $trade_amount
     * @param $order_id
     * @param string $trade_remark
     * @return array
     */
    public function setAccountRoundOrderBack($user_id, $trade_amount, $order_id, $trade_remark = "轮单费返还"){
        try {
            $this->companyAccountModel->startTrans();
            $accountInfo = $this->getAccountInfo($user_id);

            // 验证
            if (empty($trade_amount)) {
                throw new Exception("返还金额不能为空", 4000002);
            }

            // 更改修改时间（锁住记录行）
            $this->companyAccountModel->saveData($user_id, ["updated_at" => time()]);
            $ret = $this->companyAccountModel->setAccountInc($user_id, $trade_amount);

            if ($ret === false) {
                throw new Exception("操作失败", 5000001);
            }

            // 交易日志
            $account_type = static::ACCOUNT_TYPE;
            $trade_type = UserCompanyAccountLogModel::LOG_TYPE_ROUND_BACK;
            $account_amount_total = $accountInfo["account_amount"] + $trade_amount + $accountInfo["gift_amount"];
            $log_id = $this->addAccountLog($user_id, $account_type, $trade_amount, $trade_type, $trade_remark, 1, $order_id, $account_amount_total);
            //添加轮单交易类型记录
            if($log_id){
                if ($trade_type == UserCompanyAccountLogModel::LOG_TYPE_ROUND_BACK) {
                    //获取上一次轮单扣费的信息,用于记录本次退费的轮单交易类型记录
                    $accountLogDb = new UserCompanyAccountLogModel();
                    $log = $accountLogDb->getNewestAccountLog($user_id,$order_id);
                    if(!empty($log)){
                        $this->addAccountLogRound($log_id, $log['round_amount_type'], $log['price'], $log['mianji'],$log['round_amount_mianji']);
                    }
                }
            }
            $errcode = 0;
            $errmsg = "操作成功";
            $this->companyAccountModel->commit();
        } catch (Exception $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
            $this->companyAccountModel->rollback();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    // 轮单数增加
    public function setAccountRoundOrderNumberInc($user_id, $order_id = "", $trade_remark = "补轮单次数加1", $is_back = false){
        try {
            $number = 1;
            $accountInfo = $this->getAccountInfo($user_id);

            // 更改修改时间
            $this->companyAccountModel->saveData($user_id, ["updated_at" => time()]);
            $ret = $this->companyAccountModel->setRoundOrderNumberInc($user_id, $number);

            if ($ret === false) {
                throw new Exception("操作失败", 5000001);
            }

            // 交易类型
            $trade_type = UserCompanyAccountLogModel::LOG_TYPE_LDS_INC;
            if ($is_back == true) {
                $trade_type = UserCompanyAccountLogModel::LOG_TYPE_LDS_BACK;
            }

            // 交易日志
            $account_type = static::ACCOUNT_TYPE_LDS;
            $this->addAccountLog($user_id, $account_type, 0, $trade_type, $trade_remark, 1, $order_id, $accountInfo["account_amount"]);

            $errcode = 0;
            $errmsg = "操作成功";
        } catch (Exception $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    // 轮单数扣除
    public function setAccountRoundOrderNumberDec($user_id, $order_id = "", $trade_remark = "补轮单次数减1"){
        try {
            $number = 1;
            $accountInfo = $this->getAccountInfo($user_id);
            if ($accountInfo["round_order_number"] < $number) {
                throw new Exception("补轮单剩余数量不足", 1000001);
            }

            // 更改修改时间
            $this->companyAccountModel->saveData($user_id, ["updated_at" => time()]);
            $ret = $this->companyAccountModel->setRoundOrderNumberDec($user_id, $number);

            if ($ret === false) {
                throw new Exception("操作失败", 5000001);
            }

            // 交易日志
            $account_type = static::ACCOUNT_TYPE_LDS;
            $trade_type = UserCompanyAccountLogModel::LOG_TYPE_LDS_DEC;
            $this->addAccountLog($user_id, $account_type, 0, $trade_type, $trade_remark, 2, $order_id, $accountInfo["account_amount"]);

            $errcode = 0;
            $errmsg = "操作成功";
        } catch (Exception $e) {
            $errcode = $e->getCode();
            $errmsg = $e->getMessage();
        }

        return ["errcode" => $errcode, "errmsg" => $errmsg];
    }

    // 账户交易明细列表
    public function getTradeLogList($user_id, $input, $limit = 20, $export = false){
        $trade_type = static::$trade_type;
        $input["trade_types"] = array_keys($trade_type);

        $accountLogModel = new UserCompanyAccountLogModel();

        $count = 0;
        $offset = 0;
        $listRows = 0;
        if ($export == false) {
            $count = $accountLogModel->getTradeLogCount($user_id, $input);

            import("Library.Org.Util.Page");
            $pageClass = new \Page($count, $limit);
            $pageshow = $pageClass->show();
            $offset = $pageClass->firstRow;
            $listRows = $pageClass->listRows;
        }

        $list = $accountLogModel->getTradeLogList($user_id, $input, $offset, $listRows);

        if (!empty($list)) {
            foreach ($list as $key => &$item) {
                $log_id = $item["id"];
                $item["index"] = $offset + $key + 1;
                $item["trade_amount"] = floatval($item["trade_amount"]);
                $item["created_date"] = date("Y-m-d H:i:s", $item["created_at"]);
                $item["trade_type_name"] = $trade_type[$item["trade_type"]];
                $item["operation_type_name"] = static::$operation_type[$item["operation_type"]];
                $item["operation_mark"] = $item["operation_type"] == 1 ? "+" : "-";

                $item["huxing_name_show"] = "";
                if (!empty($item["order_id"])) {
                    $item["huxing_name_show"] = sprintf("%s %s室%s厅%s卫", $item["huxing_name"], $item["shi"], $item["ting"], $item["wei"]);
                }
                if (!empty($item['round_amount_type'])) {
                    $item['mianji'] = $item['initial_mianji'];
                    switch ($item['round_amount_type']){
                        case 1:
                            $item['round_amount_type_name'] = '公装';
                            break;
                        case 2:
                            $item['round_amount_type_name'] = '局改';
                            break;
                        case 3:
                            $item['round_amount_type_name'] = '家装';
                            $item['mianji'] = $item['mianji'] . '(<=' . $item['round_amount_mianji'] . ')';
                            break;
                        case 4:
                            $item['round_amount_type_name'] = '家装';
                            $item['mianji'] = $item['mianji'] . '(>' . $item['round_amount_mianji'] . ')';
                            break;
                    }
                }else{
                    //如果不是多轮单分配 , 则不显示面积
//                    $item['mianji'] = '';
                    //兼容页面数据
                    if ($item['lx'] == 2) {
                        $item['round_amount_type_name'] = '公装';
                    } elseif ($item['lxs'] == 3) {
                        $item['round_amount_type_name'] = '局改';
                    } elseif ($item['lx'] == 1) {
                        $item['round_amount_type_name'] = '家装';
                    }
                }

            }
        }

        return [
            "list" => $list,
            "count" => $count,
            "pageshow" => $pageshow
        ];
    }


    // 获取账户操作日志
    public function getAccountLogList($user_id, $begin = "", $end = ""){
        $trade_type_list = array(
            UserCompanyAccountLogModel::LOG_TYPE_RECHARGE => "余额加款",
            UserCompanyAccountLogModel::LOG_TYPE_BZJ_RECHARGE => "保证金加款",
            UserCompanyAccountLogModel::LOG_TYPE_DEDUCTION => "余额扣款",
            UserCompanyAccountLogModel::LOG_TYPE_BZJ_DEDUCTION => "保证金扣款",
            UserCompanyAccountLogModel::LOG_TYPE_BZJ_BACK_PART => "保证金部分退还",
            UserCompanyAccountLogModel::LOG_TYPE_BZJ_BACK_ALL => "保证金全部退还",
            UserCompanyAccountLogModel::LOG_TYPE_BZJ_TO_ROUND => "保证金转轮单费",
            UserCompanyAccountLogModel::LOG_TYPE_BZJ_TO_PLATMONEY => "保证金转平台使用费",
            UserCompanyAccountLogModel::LOG_TYPE_BZJ_DEC_REBATE => "保证金扣返点",
            UserCompanyAccountLogModel::LOG_TYPE_DEDUCTION_NOTICE => "补轮主动告知扣费",
            UserCompanyAccountLogModel::LOG_TYPE_DEDUCTION_VIOLATION => "补轮违规扣费",
        );

        $trade_types = array_keys($trade_type_list);
        $accountLogModel = new UserCompanyAccountLogModel();
        $list = $accountLogModel->getAccountLogList($user_id, $trade_types, $begin, $end);

        if (!empty($list)) {
            foreach ($list as $key => &$item) {
                $item["trade_amount"] = floatval($item["trade_amount"]);
                $item["created_date"] = date("Y-m-d", $item["created_at"]);
            }
        }

        return $list;
    }


    /**
     * 获取用户账户信息
     * 不存在时新增
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    private function getAccountInfo($user_id){
        $accountInfo = $this->companyAccountModel->getAccountInfo($user_id);
        if (empty($accountInfo)) {
            $this->companyAccountModel->addInfo([
                "user_id" => $user_id,
                "created_at" => time(),
                "updated_at" => time(),
            ]);

            $accountInfo = $this->getAccountInfo($user_id);
        }

        return $accountInfo;
    }
    
    /**
     * 交易日志
     * @param [type] $user_id      [description]
     * @param [type] $account_type [description]
     * @param [type] $trade_amount [description]
     * @param [type] $trade_type   [description]
     * @param [type] $trade_remark [description]
     * @param [type] $order_id     [description]
     */
    private function addAccountLog($user_id, $account_type, $trade_amount, $trade_type, $trade_remark, $operation_type, $order_id = "", $account_amount = 0, $gift_amount = 0, $trade_child_type = 0){
        $userinfo = session("uc_userinfo");
        $trade_no = $this->getLogTradeNo();
        $log_id = UserCompanyAccountLogModel::addAccountLog([
            "user_id" => $user_id,
            "trade_no" => $trade_no,
            "trade_type" => $trade_type,
            "trade_amount" => $trade_amount,
            "trade_remark" => $trade_remark,
            "account_type" => $account_type,
            "account_amount" => $account_amount,
            "operation_type" => $operation_type,
            "trade_child_type" => $trade_child_type,
            "gift_amount" => $gift_amount,
            "order_id" => $order_id,
            "operator" => $userinfo["id"],
            "created_at" => time(),
            "updated_at" => time()
        ]);

        // if (!empty($log_id)) {
        //     $trade_no = date("Ymd") . sprintf("%03d", $log_id);
        //     UserCompanyAccountLogModel::updateTradeNo($log_id, $trade_no);
        // }

        return $log_id;
    }

    // 获取交易流水号
    private function getLogTradeNo(){
        $trade_no = date("YmdH") . sprintf("%04d", rand(1, 9999));
        $exist = UserCompanyAccountLogModel::validateTradeNo($trade_no);
        if ($exist > 0) {
            $trade_no = $this->getLogTradeNo();
        }

        return $trade_no;
    }

    /**
     * 添加轮单记录类型关联表
     * @param $log_id 日志id
     * @param $type 轮单类型
     * @param $price 价格
     * @param $mianji 面积
     * @param $round_amount_mianji 轮单验证的面积
     */
    private function addAccountLogRound($log_id, $type, $price,$mianji,$round_amount_mianji)
    {
        $save = [
            'log_id' => $log_id,
            'round_amount_type' => $type,
            'mianji' => $mianji,
            'round_amount_mianji' => $round_amount_mianji,
            'price' => $price,
            'created_at' => time(),
        ];
        $relationDb = new UserCompanyAccountLogRoundRelationModel();
        return $relationDb->addLogRound($save);
    }
}