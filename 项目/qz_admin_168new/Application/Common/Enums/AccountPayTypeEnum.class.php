<?php

namespace Common\Enums;

class AccountPayTypeEnum {

    private static $pay_type_list = array(
        [
            "id" => 1,
            "name" => "支付宝支付",
            "show_type" => 1
        ],
        [
            "id" => 2,
            "name" => "微信支付",
            "show_type" => 1
        ],
        [
            "id" => 3,
            "name" => "工商银行（聚合支付）",
            "show_type" => 1
        ],
        [
            "id" => 4,
            "name" => "平安银行苏州分行营业部",
            "show_type" => 2
        ],
        [
            "id" => 5,
            "name" => "中国建设银行苏州万达广场支行",
            "show_type" => 2
        ],
        [
            "id" => 6,
            "name" => "中国工商银行苏州万达广场支行",
            "show_type" => 2
        ],
        [
            "id" => 7,
            "name" => "中国农业银行苏州万达广场支行",
            "show_type" => 2
        ],
        [
            "id" => 8,
            "name" => "交通银行苏州姑苏支行",
            "show_type" => 2
        ]
    );

    public static function getPayTypeAll(){
        return static::$pay_type_list;
    }

    public static function getPayTypeList($show_type = 1){
        $payTypeList = [];
        foreach (static::$pay_type_list as $key => $item) {
            if ($item["show_type"] == $show_type) {
                $payTypeList[] = $item;
            }
        }

        return $payTypeList;
    }

    // 获取支付方式名称
    public static function getPayTypeItem($pay_type, $default = ""){
        foreach (static::$pay_type_list as $key => $item) {
            if ($item["id"] == $pay_type) {
                $default = $item["name"];
                break;
            }
        }

        return $default;
    }

}