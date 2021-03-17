<?php

namespace app\index\enum;

class BusinessLicenceCodeEnum {

    // 审核项目
    private static $code_type = [
        "1" => "营业执照",
        "2" => "开票资料",
        "3" => "装修资质",
        "4" => "工商总局"
    ];

    // 审核状态
    private static $code_state = [
        "1" => "待审核",
        "2" => "未通过",   // 初审
        "3" => "通过",   // 初审
        "4" => "通过",   // 终审
        "5" => "未通过"   // 终审
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