<?php

namespace app\index\validate;

use think\Validate;
use think\Exception;
use think\facade\Request;
use app\index\enum\ReportPaymentCodeEnum;

class ReportPaymentValidate extends Validate {

    protected $rule = [
        
    ];

    protected $message = [
        "cooperation_type.require" => "请选择合作类型",
        "cooperation_type.number" => "请选择合作类型",
        "cooperation_type.gt" => "请选择合作类型",
        "cooperation_type.in" => "请选择合作类型",
        "company_id.requireIf" => "请先填写网店代码",
        "company_name.require" => "请先填写公司名称",
        "company_name.length" => "公司名称过长",
        "city_id.require" => "请先选择城市",
        "city_id.number" => "请先选择城市",
        "city_id.gt" => "请先选择城市",
        "viptype.requireIf" => "请先选择单倍/几倍",
        "back_ratio.requireIf" => "请先选择返点比例",
        "back_ratio.require" => "请先选择返点比例",
        "payment_uname.require" => "请先填写汇款方名称",
        "payment_date.require" => "请先选择汇款时间",
        "payment_total_money.require" => "请先填写收款金额",
        "payment_total_money.float" => "请先填写收款金额",
        "payment_total_money.gt" => "请先填写收款金额必须大于0",
        "payment_type.require" => "请先选择收款类型",
        "payment_type.number" => "请先选择收款类型",
        "payment_type.gt" => "请先选择收款类型",
        "payment_type.in" => "请先选择收款类型",
        "payee_list.require" => "请先选择收款方名称",
        "auth_imgs.require" => "请先上传汇款凭证",
        "order_id.require" => "请先选择订单",
        "deposit_to_platform_money.require" => "请输入抵扣金额",
        "deposit_to_platform_money.float" => "请输入抵扣金额",
        "deposit_to_platform_money.gt" => "抵扣金额不能为0",
        "platform_money_start_date.require" => "请选择有效期开始日期",
        "platform_money_end_date.require" => "请选择有效期结束日期",
    ];

    protected $field = [
        
    ];

    // 常规编辑
    public function sceneSave() {
        return $this->only([
                "cooperation_type", "company_id", "company_name", "city_id", "viptype", "back_ratio", "payment_date", "payment_total_money", "payment_type", "payee_list", "auth_imgs", "round_order_money", "deposit_money", "other_money", "deposit_to_round_money", "platform_money"
            ])
            ->append("cooperation_type", "require|number|gt:0|in:1,2,3,4,6,7,8,9,10,11,12,13,14,15")
            ->append("company_id", "requireIf:cooperation_type,10")
            ->append("company_name", "require|length:1,20")
            ->append("city_id", "require|number|gt:0")
            ->append("viptype", "requireIf:cooperation_type,1|requireIf:cooperation_type,2|requireIf:cooperation_type,3|requireIf:cooperation_type,7")
            ->append("back_ratio", "requireIf:cooperation_type,6|requireIf:cooperation_type,10")
            ->append("payment_date", "require")
            ->append("payment_total_money", function($value, $item){
                if($item['cooperation_type'] == ReportPaymentCodeEnum::COOPERATION_TYPE_SBACK_ACCOUNT){
                    return true;
                }

                if(!$value || !filter_var($value, FILTER_VALIDATE_FLOAT)){
                    return '请先填写收款金额';
                }else if($value < 0){
                    return '收款金额必须大于0';
                }

                return true;
            })
            ->append("payment_type", "require|number|gt:0")
            ->append("payee_list", function($value, $item){
                if($item['cooperation_type'] == ReportPaymentCodeEnum::COOPERATION_TYPE_SBACK_ACCOUNT){
                    return true;
                }
                if(!$value){
                    return '请先选择收款方名称';
                }

                return true;
            })
            ->append("auth_imgs", function($value, $item){
                if($item['cooperation_type'] == ReportPaymentCodeEnum::COOPERATION_TYPE_SBACK_ACCOUNT){
                    return true;
                }
                if(!$value){
                    return '请先上传汇款凭证';
                }

                return true;
            })
            ->append("round_order_money", function($value, $item){
                $normType = ReportPaymentCodeEnum::getNormCooperationType();
                if(in_array($item["cooperation_type"], $normType)){
                    if(empty($value)){
                        return "请填入会员费";
                    }else if (!is_numeric($value)) {
                        return "会员费不合法";
                    } else if ($value > $item["payment_total_money"]) {
                        return "会员费不可大于收款金额";
                    }
                }

                if (in_array($item["cooperation_type"], [ReportPaymentCodeEnum::COOPERATION_TYPE_SBACK, ReportPaymentCodeEnum::COOPERATION_TYPE_SBACK_MODEL]) && !empty($value)) {
                    if (!is_numeric($value)) {
                        return "轮单费不合法";
                    } else if ($value > $item["payment_total_money"]) {
                        return "轮单费不可大于收款金额";
                    }
                }

                return true;
            })
            ->append("platform_money", function($value, $item){
                if($value > 0 || $item["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_PLATFORM_USE){
                    if(empty($item['platform_money_start_date'])){
                        return '请选择开始日期';
                    }
                    if(empty($item['platform_money_end_date'])){
                        return '请选择结束日期';
                    }
                    if(strtotime($item['platform_money_start_date']) > strtotime($item['platform_money_end_date'])){
                        return '结束日期不得小于开始日期';   
                    }
                }
                return true;
            })
            ->append("deposit_money", function($value, $item){
                if (in_array($item["cooperation_type"], [ReportPaymentCodeEnum::COOPERATION_TYPE_SBACK, ReportPaymentCodeEnum::COOPERATION_TYPE_SBACK_MODEL]) && !empty($value)) {
                    if (!is_numeric($value)) {
                        return "保证金不合法";
                    } else if ($value > $item["payment_total_money"]) {
                        return "保证金不可大于收款金额";
                    }
                }
                
                return true;
            })
            ->append("other_money", function($value, $item){
                $other_money = $item["other_money"] ?? 0;
                $other_money_remark = $item["other_money_remark"] ?? "";
                $payment_total_money = $item["payment_total_money"] ?? 0;

                if (empty($other_money) && !empty($other_money_remark)) {
                    return "请输入其他金额";
                } else if (!empty($other_money) && empty($other_money_remark)) {
                    return "请输入其他金额的类型";
                }

                if ($other_money > $payment_total_money) {
                    return "其他金额合计不得超出收款金额";
                }

                $deposit_money = $item["deposit_money"] ?? 0;
                $round_order_money = $item["round_order_money"] ?? 0;
                $platform_money = $item['platform_money'] ?? 0;

                $checkPlat = [
                    ReportPaymentCodeEnum::COOPERATION_TYPE_ZX,
                    ReportPaymentCodeEnum::COOPERATION_TYPE_DJ,
                    ReportPaymentCodeEnum::COOPERATION_TYPE_LD,
                    ReportPaymentCodeEnum::COOPERATION_TYPE_QWU,
                    ReportPaymentCodeEnum::COOPERATION_TYPE_SBACK,
                    ReportPaymentCodeEnum::COOPERATION_TYPE_REGULAR_MODEL,
                    ReportPaymentCodeEnum::COOPERATION_TYPE_SBACK_MODEL
                ];

                if(in_array($item['cooperation_type'], $checkPlat)){
                    if ($other_money + $deposit_money + $round_order_money + $platform_money != $payment_total_money) {
                        return "合计金额不等于收款金额";
                    }
                }

                return true;
            })
            ->append('deposit_to_round_money', function ($value, $item){
                if ($item["cooperation_type"] == ReportPaymentCodeEnum::COOPERATION_TYPE_SBACK_ACCOUNT) {
                    if(empty($value)){
                        return '请先填写保证金抵扣轮单费';
                    }else if (!is_numeric($value)) {
                        return "保证金抵扣轮单费不合法";
                    } else if ($value > $item["deposit_money"]) {
                        return "保证金抵扣轮单费不可大于保证金余额";
                    }
                }
                
                return true;
            });
    }

    // 会员返点编辑
    public function sceneUback() {
        return $this->only([
                "cooperation_type", "order_id", "back_ratio", "payment_date", "payment_total_money", "payment_type", "payee_list", "auth_imgs", "round_order_money", "deposit_money", "other_money"
            ])
            ->append("cooperation_type", "require|number|gt:0|in:8")
            ->append("order_id", "require")
            ->append("back_ratio", "require")
            ->append("payment_date", "require")
            // ->append("payment_total_money", "require|float")
            ->append("payment_type", "require|number|gt:0")
            ->append("auth_imgs", function($auth_imgs, $item){
                $payment_total_money = $item["payment_total_money"] ?? 0;
                if ($payment_total_money > 0 && empty($auth_imgs)) {
                    return "请先上传汇款凭证";
                }
                return true;
            })
            ->append("payee_list", function($payee_list, $item){
                $payment_total_money = $item["payment_total_money"] ?? 0;
                if ($payment_total_money > 0 && empty($payee_list)) {
                    return "请先选择收款方名称";
                }
                return true;
            })
            ->append("other_money", function($value, $item){
                $other_money = $item["other_money"] ?? 0;
                $other_money_remark = $item["other_money_remark"] ?? "";
                $payment_total_money = $item["payment_total_money"] ?? 0;

                if (empty($other_money) && !empty($other_money_remark)) {
                    return "请输入其他金额";
                } else if (!empty($other_money) && empty($other_money_remark)) {
                    return "请输入其他金额的类型";
                }

                if ($other_money > $payment_total_money) {
                    return "其他金额合计不得超出收款金额";
                }

                $deposit_money = $item["deposit_money"] ?? 0;
                $round_order_money = $item["round_order_money"] ?? 0;
                if ($deposit_money == 0 && $round_order_money == 0 && $payment_total_money == 0) {
                    return "收款金额、轮单费抵扣、保证金额不得同时为0！";
                }

                return true;
            });
    }

    // 退款报备验证
    public function sceneRefund() {
        return $this->only([
                "cooperation_type", "company_name", "city_id", "payment_date", "payment_type", "refund_money","person_list","auth_imgs"
            ])
            ->append("cooperation_type", "require|eq:11")
            ->append("company_name", "require|length:1,20")
            ->append("city_id", "require|number|gt:0")
            ->append("payment_date", function($value, $item){
                if($value == ''){
                    return '请选择退款日期';
                }

                return true;
            })
            ->append("payment_type", "require|number|in:7,8")
            ->append("refund_money", function($value, $item){
                if($item['cooperation_type'] != ReportPaymentCodeEnum::COOPERATION_TYPE_USER_REFUND){
                    return true;
                }

                if(!$value || !filter_var($value, FILTER_VALIDATE_FLOAT)){
                    return '请先填写退款金额';
                }else if($value < 0){
                    return '退款金额请填正数';
                }

                return true;
            })
            ->append('person_list', function($value, $item){
                if(empty($value)){
                    return '请选择相关业绩人';
                }
                return true;
            })
            ->append("auth_imgs", function($value, $item){
                if($item['cooperation_type'] == ReportPaymentCodeEnum::COOPERATION_TYPE_SBACK_ACCOUNT){
                    return true;
                }
                if(!$value){
                    return '请先上传退款凭证';
                }

                return true;
            });
    }
	
    // 平台使用费（保证金转）编辑
    public function scenePlatformTurn() {
        return $this->only([
                "cooperation_type", "company_id", "company_name", "city_id", "deposit_to_platform_money", "platform_money_start_date", "platform_money_end_date", "payment_date", "payment_type", "auth_imgs"
            ])
            ->append("cooperation_type", "require|number|gt:0|in:13")
            ->append("company_id", "require")
            ->append("company_name", "require")
            ->append("city_id", "require|number|gt:0")
            ->append("payment_date", "require")
            ->append("payment_type", "require|number|gt:0")
            ->append("deposit_to_platform_money", "require|float|gt:0")
            ->append("platform_money_start_time", "require")
            ->append("platform_money_end_date", function($platform_money_end_date, $data){
                if (empty($platform_money_end_date)) {
                    return "请选择有效期结束日期";
                }

                if (strtotime($platform_money_end_date) < strtotime($data["platform_money_start_date"])) {
                    return "结束日期不得早于开始日期";
                }

                return true;
            });
    }


    // 其它业绩人数据验证
    public function validatePersonList($person_list, $data){
        try {
            if (!empty($person_list)) {
                if (!is_array($person_list)) {
                    throw new Exception("其它业绩人格式不正确", 1);
                }

                $back_sum = 0;
                $person_ids = [];
                foreach ($person_list as $key => $item) {
                    // 业绩人是否重复验证
                    if (in_array($item["saler_id"], $person_ids)) {
                        throw new Exception("其它业绩人不能重复添加", 1);
                    }

                    // 业绩人和分成比例非空验证
                    if (empty($item["saler_id"]) || empty($item["share_ratio"])) {
                        throw new Exception("其它业绩人格式不正确", 1);
                    }

                    $share_ratio = intval($item["share_ratio"]);
                    if ($share_ratio != $item["share_ratio"]) {
                        throw new Exception("其它业绩人分成比例格式不正确", 1);
                    } else if ($share_ratio <= 0 || $share_ratio > 100) {
                        throw new Exception("其它业绩人分成比例格式不正确", 1);
                    }

                    $person_ids[] = $item["saler_id"];
                    $back_sum = $back_sum + $share_ratio;
                }

                if ($back_sum > 100) {
                    throw new Exception("其他业绩人分成比例不能超过100%", 1);
                }
            }

            $ret = true;
        } catch (Exception $e) {
            $ret = $e->getMessage();
        }
        
        return $ret;
    }

    // 验证收款方
    public function validatePayeeList($payee_list, $data){
        $payment_total_money = $data["payment_total_money"] ?? 0;

        try {
            if (!empty($payee_list)) {
                if (!is_array($payee_list)) {
                    throw new Exception("其它业绩人格式不正确", 1);
                }

                $payee_money = 0;
                $payee_types = [];
                foreach ($payee_list as $key => $item) {
                    // 业绩人是否重复验证
                    if (in_array($item["payee_type"], $payee_types)) {
                        throw new Exception("收款方不能重复添加", 1);
                    }

                    // 业绩人和分成比例非空验证
                    if (empty($item["payee_type"]) || empty($item["payee_money"])) {
                        throw new Exception("收款方格式不正确", 1);
                    }

                    $payee_types[] = $item["payee_type"];
                    $payee_money = $payee_money + $item["payee_money"];
                }

                if ($payee_money > $payment_total_money) {
                    throw new Exception("收款方金额合计不得超出收款金额", 1);
                }
            }

            $ret = true;
        } catch (Exception $e) {
            $ret = $e->getMessage();
        }

        return $ret;
    }

}
