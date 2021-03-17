<?php

namespace app\index\enum;

class CompanyConsultCodeEnum {

    // 意向等级描述
    const SUCCESS_LEVEL_DESC = "A代表意向很大；B代表意向还行；C代表意向很一般";

    // 合作状态
    private static $code_record_status = [
        "1" => "--",
        "2" => "谈判中",
        "3" => "不合作",
        "4" => "已合作",
    ];

    // 合作状态
    private static $code_cooperation_type = [
        "1" => "其它",
        "2" => "装修公司入驻"
    ];

    // 处理状态
    private static $code_operate_status = [
        "1" => "未处理",
        "2" => "已处理"
    ];

    // 跟踪方式
    private static $code_deal_type = [
        "1" => "上门",
        "2" => "电话",
        "3" => "QQ/微信",
    ];

    // 意向等级
    private static $code_success_level = [
        "1" => "A",
        "2" => "B",
        "3" => "C",
    ];

    // 获取状态列表
    public static function getList($module){
        $key = "code_" . strtolower($module);
        
        $list = [];
        if (isset(static::$$key)) {
            $modules = static::$$key;
            foreach ($modules as $k => $val) {
                $list[] = [
                    'id' => $k,
                    'name' => $val
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