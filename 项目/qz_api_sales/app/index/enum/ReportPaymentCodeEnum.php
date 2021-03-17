<?php

namespace app\index\enum;

class ReportPaymentCodeEnum {

    // 合作类型
    const COOPERATION_TYPE_ZX = 1; // 装修会员
    const COOPERATION_TYPE_DJ = 2; // 独家会员
    const COOPERATION_TYPE_LD = 3; // 垄断会员
    const COOPERATION_TYPE_SWJ = 4; // 三维家会员
    const COOPERATION_TYPE_SBACK = 6; // 签返会员
    const COOPERATION_TYPE_QWU = 7; // 全屋定制会员
    const COOPERATION_TYPE_UBACK = 8; // 会员返点
    const COOPERATION_TYPE_WL = 9; // 物料
    const COOPERATION_TYPE_SBACK_ACCOUNT = 10; // 签返会员（保证金转轮单费）
    const COOPERATION_TYPE_USER_REFUND = 11; // 会员退款
    const COOPERATION_TYPE_PLATFORM_USE = 12; // 平台使用费
    const COOPERATION_TYPE_PLATFORM_USE_TURN = 13; // 平台使用费（保证金转）
    const COOPERATION_TYPE_REGULAR_MODEL = 14;//常规标杆会员
    const COOPERATION_TYPE_SBACK_MODEL = 15;//新签返标杆会员

    // 收款类型
    const PAYMENT_TYPE_NEW = 1; // 新签
    const PAYMENT_TYPE_XUFEI = 2; // 续费
    const PAYMENT_TYPE_YUKUAN = 3; // 余款
    const PAYMENT_TYPE_DING = 4; // 定金
    const PAYMENT_TYPE_BACK = 5; // 返点
    const PAYMENT_TYPE_WL = 6; // 物料
    const PAYMENT_TYPE_NEW_DING = 9; // 新签定金
    const PAYMENT_TYPE_NEW_YUKUAN = 10; // 新签余款
    const PAYMENT_TYPE_XUFEI_DING = 11; // 续费定金
    const PAYMENT_TYPE_XUFEI_YUKUAN = 12; // 续费余款
    const PAYMENT_TYPE_PINTAI = 13;//平台使用费

    const REFUND_TYPE_VIPMONEY = 7; // 会员费退款
    const REFUND_TYPE_ROUND = 8; // 轮单费退款

    // 合作类型
    private static $code_cooperation_type = [
        self::COOPERATION_TYPE_ZX => "装修会员",
        self::COOPERATION_TYPE_DJ => "独家会员",
        self::COOPERATION_TYPE_LD => "垄断会员",
        self::COOPERATION_TYPE_SBACK => "签返会员",
        self::COOPERATION_TYPE_QWU => "全屋定制会员",
        self::COOPERATION_TYPE_UBACK => "会员返点",
        self::COOPERATION_TYPE_SWJ => "三维家会员",
        self::COOPERATION_TYPE_WL => "物料",
        self::COOPERATION_TYPE_SBACK_ACCOUNT => "签返会员（保证金转轮单费）",
        self::COOPERATION_TYPE_USER_REFUND => '会员退款',
        self::COOPERATION_TYPE_PLATFORM_USE => '平台使用费',
        self::COOPERATION_TYPE_PLATFORM_USE_TURN => '平台使用费（保证金转）',
        self::COOPERATION_TYPE_REGULAR_MODEL => '常规标杆会员',
        self::COOPERATION_TYPE_SBACK_MODEL =>'新签返标杆会员',
    ];

    // 合作类型
    private static $code_statistic_cooperation_type = [
        self::COOPERATION_TYPE_ZX => "装修",
        self::COOPERATION_TYPE_DJ => "独家",
        self::COOPERATION_TYPE_LD => "垄断",
        self::COOPERATION_TYPE_SWJ => "三维家",
        self::COOPERATION_TYPE_SBACK => "签约返点",
        self::COOPERATION_TYPE_QWU => "全屋定制",
        self::COOPERATION_TYPE_UBACK => "会员返点",
        self::COOPERATION_TYPE_WL => "物料",
        self::PAYMENT_TYPE_XUFEI_DING => "续费定金",
        self::PAYMENT_TYPE_XUFEI_YUKUAN => "续费余款",
        self::PAYMENT_TYPE_NEW_YUKUAN => "新签余款",
        self::PAYMENT_TYPE_PINTAI => "平台使用费（保证金转）",
        self::COOPERATION_TYPE_REGULAR_MODEL => '常规标杆会员',
        self::COOPERATION_TYPE_SBACK_MODEL =>'新签返标杆会员',
    ];

    // 收款类型
    private static $code_payment_type = [
        self::PAYMENT_TYPE_NEW => "新签",
        self::PAYMENT_TYPE_XUFEI => "续费",

        self::PAYMENT_TYPE_NEW_DING => "新签定金",
        self::PAYMENT_TYPE_NEW_YUKUAN => "新签余款",
        self::PAYMENT_TYPE_XUFEI_DING => "续费定金",
        self::PAYMENT_TYPE_XUFEI_YUKUAN => "续费余款",

        self::PAYMENT_TYPE_YUKUAN => "余款",
        self::PAYMENT_TYPE_DING => "定金",
        self::PAYMENT_TYPE_BACK => "返点",
        self::PAYMENT_TYPE_WL => "物料",

        self::REFUND_TYPE_VIPMONEY => '会员费退款',
        self::REFUND_TYPE_ROUND => '轮单费退款',
    ];
    
    // 收款方
    private static $code_payee_type = [
        "1" => "平安银行（公账）",
        "2" => "陈总建行",
        "3" => "陈总农行",
        "4" => "陈总工行（尾号1174）",
        "5" => "陈总交行",
        "6" => "齐装-云网通",
        "7" => "苏州云网通信息科技有限公司",       
        "8" => "齐装网（苏州云网通）",
        "9" => "齐装网",
        "10" => "POS机刷卡",
        "11" => "陈总工行（尾号非1174）",
        "12" => "蒋正君",
        "13" => "（齐装网）云网通信息科技",
        "14" => "陈世超(微信二维码）",
        "15" => "工商银行（公账）",
        "16" => "中国银行（公账）",
    ];

    // 状态
    private static $code_exam_status = [
        "1" => "待提交",
        "2" => "待审核",
        "3" => "审核通过",
        "4" => "审核不通过",
        "5" => "审核通过/待客服上传",
        "6" => "客服审核不通过",
    ];

    private static $payee_type_list = [
        [
            "id" => 1,
            "name" => "平安银行（公账）",
            "sort_pc" => 11,
            "sort_applet" => 1
        ],
        [
            "id" => 2,
            "name" => "陈总建行",
            "sort_pc" => 4,
            "sort_applet" => 4
        ],
        [
            "id" => 3,
            "name" => "陈总农行",
            "sort_pc" => 10,
            "sort_applet" => 5
        ],
        [
            "id" => 4,
            "name" => "陈总工行（尾号1174）",
            "sort_pc" => 9,
            "sort_applet" => 2
        ],
        [
            "id" => 5,
            "name" => "陈总交行",
            "sort_pc" => 5,
            "sort_applet" => 6
        ],
        [
            "id" => 6,
            "name" => "齐装-云网通",
            "sort_pc" => 2,
            "sort_applet" => 8
        ],
        [
            "id" => 7,
            "name" => "苏州云网通信息科技有限公司",
            "sort_pc" => 13,
            "sort_applet" => 9
        ],
        [
            "id" => 8,
            "name" => "齐装网（苏州云网通）",
            "sort_pc" => 12,
            "sort_applet" => 11
        ],
        [
            "id" => 9,
            "name" => "齐装网",
            "sort_pc" => 7,
            "sort_applet" => 12
        ],
        [
            "id" => 10,
            "name" => "POS机刷卡",
            "sort_pc" => 8,
            "sort_applet" => 7
        ],
        [
            "id" => 11,
            "name" => "陈总工行（尾号非1174）",
            "sort_pc" => 3,
            "sort_applet" => 3
        ],
        [
            "id" => 12,
            "name" => "蒋正君",
            "sort_pc" => 1,
            "sort_applet" => 13
        ],
        [
            "id" => 13,
            "name" => "（齐装网）云网通信息科技",
            "sort_pc" => 6,
            "sort_applet" => 10
        ],
        [
            "id" => 14,
            "name" => "陈世超(微信二维码）",
            "sort_pc" => 14,
            "sort_applet" => 14
        ],
        [
            "id" => 15,
            "name" => "工商银行（公账）",
            "sort_pc" => 15,
            "sort_applet" => 15
        ],
        [
            "id" => 16,
            "name" => "中国银行（公账）",
            "sort_pc" => 16,
            "sort_applet" => 16
        ]
    ];

    // 获取状态列表
    public static function getList($module){
        $key = "code_" . strtolower($module);
        
        $list = [];
        if (isset(static::$$key)) {
            foreach (static::$$key as $k => $val) {
                $list[] = [
                    "id" => $k,
                    "name" => $val
                ];
            }
        }

        return $list;
    }

    // 获取状态列表
    public static function getKList($module){
        $key = "code_" . strtolower($module);
        
        $list = [];
        if (isset(static::$$key)) {
            foreach (static::$$key as $k => $val) {
                $list[$k] = [
                    "id" => $k,
                    "name" => $val
                ];
            }
        }

        return $list;
    }

    /**
     * 获取状态值
     * @Author   liuyupeng
     * @DateTime 2019-05-20T15:28:28+0800
     */
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

    // 获取付款方列表
    public static function getPayeeList($actfrom = "pc"){
        $payeeList = static::$payee_type_list;

        $sortFields = [
            "pc" => "sort_pc",
            "applet" => "sort_applet"
        ];

        $field = $sortFields[$actfrom] ?? "id";
        $payeeSort = array_column($payeeList, $field);
        array_multisort($payeeSort, SORT_ASC, $payeeList);
        $payeeList = array_map(function($item) use ($field){
            return [
                "id" => $item["id"],
                "name" => $item["name"],
                "paixu" => $item[$field]
            ];
        }, $payeeList);

        return $payeeList;
    }

    // 获取付款方名称
    public static function getPayeeItem($payee_type, $default = ""){
        $payeeList = static::$payee_type_list;

        $payee_name = $default;
        foreach ($payeeList as $key => $item) {
            if ($item["id"] == $payee_type) {
                $payee_name = $item["name"];
                break;
            }
        }

        return $payee_name;
    }

    // 所有可以选择的收款类型
    public static function getPaymentTypeSelectList(){
        $paymentTypeList = static::$code_payment_type;

        $ding = self::PAYMENT_TYPE_DING;
        $yukuan = self::PAYMENT_TYPE_YUKUAN;
        unset($paymentTypeList[$ding], $paymentTypeList[$yukuan]);

        $vip = self::REFUND_TYPE_VIPMONEY;
        $round = self::REFUND_TYPE_ROUND;
        unset($paymentTypeList[$vip], $paymentTypeList[$round]);

        return $paymentTypeList;
    }

    // 常规收款类型
    public static function getPaymentTypeDefaultList(){
        $paymentTypeList = static::$code_payment_type;

        $wl = self::PAYMENT_TYPE_WL;
        $back = self::PAYMENT_TYPE_BACK;
        unset($paymentTypeList[$wl], $paymentTypeList[$back]);

        $ding = self::PAYMENT_TYPE_DING;
        $yukuan = self::PAYMENT_TYPE_YUKUAN;
        unset($paymentTypeList[$ding], $paymentTypeList[$yukuan]);

        $vip = self::REFUND_TYPE_VIPMONEY;
        $round = self::REFUND_TYPE_ROUND;
        unset($paymentTypeList[$vip], $paymentTypeList[$round]);

        return $paymentTypeList;
    }
 
    // 物料收款类型
    public static function getPaymentTypeWlList(){
        $paymentTypeList = static::$code_payment_type;

        $back = self::PAYMENT_TYPE_BACK;
        unset($paymentTypeList[$back]);

        $ding = self::PAYMENT_TYPE_DING;
        $yukuan = self::PAYMENT_TYPE_YUKUAN;
        unset($paymentTypeList[$ding], $paymentTypeList[$yukuan]);

        $vip = self::REFUND_TYPE_VIPMONEY;
        $round = self::REFUND_TYPE_ROUND;
        unset($paymentTypeList[$vip], $paymentTypeList[$round]);

        return $paymentTypeList;
    }

    // 返点收款类型
    public static function getPaymentTypeBackList(){
        $paymentTypeList = static::$code_payment_type;

        $wl = self::PAYMENT_TYPE_WL;
        unset($paymentTypeList[$wl]);

        $ding = self::PAYMENT_TYPE_DING;
        $yukuan = self::PAYMENT_TYPE_YUKUAN;
        unset($paymentTypeList[$ding], $paymentTypeList[$yukuan]);

        $vip = self::REFUND_TYPE_VIPMONEY;
        $round = self::REFUND_TYPE_ROUND;
        unset($paymentTypeList[$vip], $paymentTypeList[$round]);

        return $paymentTypeList;
    }

    public static function getRefundTypelList(){
        $paymentTypeList = static::$code_payment_type;

        return [
            self::REFUND_TYPE_VIPMONEY => $paymentTypeList[self::REFUND_TYPE_VIPMONEY],
            self::REFUND_TYPE_ROUND => $paymentTypeList[self::REFUND_TYPE_ROUND],
        ];
    }

    public static function toKeyValue($list){
        $ret = [];
        foreach ($list as $key => $item) {
            $ret[] = [
                "id" => $key,
                "name" => $item,
            ];
        }

        return $ret;
    }

    //会员费校验
    public static function getNormCooperationType(){
        return [
            ReportPaymentCodeEnum::COOPERATION_TYPE_ZX,
            ReportPaymentCodeEnum::COOPERATION_TYPE_DJ,
            ReportPaymentCodeEnum::COOPERATION_TYPE_LD,
            ReportPaymentCodeEnum::COOPERATION_TYPE_QWU,
            ReportPaymentCodeEnum::COOPERATION_TYPE_REGULAR_MODEL
        ];
    }
}