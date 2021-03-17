<?php

namespace app\index\enum;

class CompanyAccountCodeEnum {

    // 账户类型
    const ACCOUNT_TYPE = 1; // 轮单费
    const ACCOUNT_TYPE_BZJ = 2; // 保证金
    const ACCOUNT_TYPE_LDS = 3; // 轮单数

    // 轮单费交易类型
    const LOG_TYPE_RECHARGE = 1; // 加款
    const LOG_TYPE_DEDUCTION = 2; // 扣款
    const LOG_TYPE_ROUND_ORDER = 3; // 轮单扣款
    const LOG_TYPE_OTHER = 4; // 其它类型扣款
    const LOG_TYPE_ROUND_BACK= 5; // 轮单费返还

    // 保证金交易类型
    const LOG_TYPE_BZJ_RECHARGE = 11; // 保证金加款
    const LOG_TYPE_BZJ_DEDUCTION = 12; // 保证金扣款
    const LOG_TYPE_BZJ_BACK_PART = 13; // 保证金部分返还
    const LOG_TYPE_BZJ_BACK_ALL = 14; // 保证金全部返还
    const LOG_TYPE_BZJ_OTHER = 15; // 保证金其它类型扣款

    // 补轮数交易类型
    const LOG_TYPE_LDS_INC = 21; // 轮单数增加 
    const LOG_TYPE_LDS_DEC = 22; // 补单数扣除
    const LOG_TYPE_LDS_BACK = 23; // 补单数返还

    // 多轮单类型
    const ROUND_AMOUNT_TYPE_PUB = 1; // 公装
    const ROUND_AMOUNT_TYPE_PART = 2; // 局改
    const ROUND_AMOUNT_TYPE_HOME_MIN = 3; // 家装小于等于
    const ROUND_AMOUNT_TYPE_HOME_MAX = 4; // 家装大于

    // 账单类型
    public static $code_trade_type = [
        self::LOG_TYPE_RECHARGE => "账户充值",
        self::LOG_TYPE_ROUND_ORDER => "轮单扣费",
        self::LOG_TYPE_DEDUCTION => "其它扣费",
        self::LOG_TYPE_ROUND_BACK => "轮单费返还",
        self::LOG_TYPE_BZJ_RECHARGE => "保证金充值",
        self::LOG_TYPE_BZJ_DEDUCTION => "保证金扣费",
        self::LOG_TYPE_BZJ_BACK_PART => "保证金部分退还",
        self::LOG_TYPE_BZJ_BACK_ALL => "保证金全部退还",
        self::LOG_TYPE_LDS_INC => "补轮成功",
        self::LOG_TYPE_LDS_DEC => "补轮扣单",
        self::LOG_TYPE_LDS_BACK => "补轮返单"
    ];

    // 收支类型
    private static $code_operation_type = [
        1 => "收入",
        2 => "支出",
    ];

    // 会员状态映射关系
    private static $code_user_on_map = [
        "1" => "2", // vip
        "2" => "-4", // 暂停
        "3" => "-1", // 到期
    ];

    // 多轮单类型
    private static $code_round_amount_type = [
        "1" => "公装",
        "2" => "局改",
        "3" => "家装",
        "4" => "家装"
    ];

    // 获取状态列表
    public static function getList($module){
        $key = "code_" . strtolower($module);
        
        $list = [];
        if (isset(static::$$key)) {
            $modules = static::$$key;
            foreach ($modules as $k => $val) {
                $list[] = [
                    "id" => $k,
                    "name" => $val
                ];
            }
        }

        return $list;
    }

    // 获取状态值
    public static function getItem($module, $code, $default = ''){
        $key = "code_" . strtolower($module);
        $code = strtolower($code);

        if (isset(static::$$key)) {
            $modules = static::$$key;
            if (array_key_exists($code, $modules)) {
                return $modules[$code];
            }
        }

        return $default;
    }
}