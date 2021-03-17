<?php
// +----------------------------------------------------------------------
// | OrderSourceAccountValidate 城市相关数据验证
// +----------------------------------------------------------------------

namespace app\index\validate;

use think\Validate;
use think\facade\Request;

class OrderSourceAccountValidate extends Validate {

    protected $rule = [
    ];

    protected $message = [
        "account_id.require" => "请先选择渠道账户",
        "account_id.number" => "请先选择渠道账户",
        "date.require" => "请先选择日期",
        "expend_amount.require" => "请输入消耗金额",
        "expend_amount.float" => "消耗金额不合法",
    ];

    public function sceneAdd() {
        return $this->only([
            "account_id", "date", "expend_amount"
        ])
            ->append("account_id", "require|number")
            ->append("date", "require")
            ->append("expend_amount", "require|float");
    }

    public function sceneEdit() {
        return $this->only([
            "expend_amount"
        ])->append("expend_amount", "require|float");
    }


    // 验证并补全查询数据
    public static function checkAccountData(&$input){
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-d", strtotime($input["date_begin"]));
            $input["date_end"] = date("Y-m-d", strtotime($input["date_end"]));
        } else if (!empty($input["date_begin"]) && empty($input["date_end"])) {
            $begintime = strtotime($input["date_begin"]);
            $input["date_begin"] = date("Y-m-d", $begintime);
            $input["date_end"] = date("Y-m-t", $begintime);
        } else if (empty($input["date_begin"]) && !empty($input["date_end"])) {
            $endtime = strtotime($input["date_end"]);
            $input["date_begin"] = date("Y-m-01", $endtime);
            $input["date_end"] = date("Y-m-d", $endtime);
        } else {
            $input["date_begin"] = date("Y-m-01");
            $input["date_end"] = date("Y-m-d");
        }

        return $input;
    }
}