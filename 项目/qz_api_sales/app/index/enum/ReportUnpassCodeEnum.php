<?php

namespace app\index\enum;

class ReportUnpassCodeEnum {

    const REPORT_TYPE_DEFAULT = 1; // 大报备
    const REPORT_TYPE_PAYMENT = 2; // 小报备
    const REPORT_TYPE_INVOICE = 3; // 发票报备

    // 报备类型
    public static $code_report_type = [
        self::REPORT_TYPE_DEFAULT => "大报备",
        self::REPORT_TYPE_PAYMENT => "小报备",
        self::REPORT_TYPE_INVOICE => "发票报备"
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
}