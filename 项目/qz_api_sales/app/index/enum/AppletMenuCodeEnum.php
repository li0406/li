<?php

namespace app\index\enum;

class AppletMenuCodeEnum {

    // 审核项目
    private static $code_version = [
        "1" => "老后台",
        "2" => "新后台",
        "3" => "运营系统",
        "4" => "家具系统",
        "5" => "家具会员系统",
        "6" => "装修说管理系统",
        "7" => "销售系统",
        "8" => "短信管理平台",
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
                    'name' => $val,
                    'value' => $val
                ];
            }
        }

        return $list;
    }

    // 获取状态列表
    public static function getVersionList($versions = "1,2,7"){
        if (is_string($versions)) {
            $versions = explode(",", $versions);
        }
        
        $list = [];
        if (isset(static::$code_version)) {
            $modules = static::$code_version;
            foreach ($modules as $k => $val) {
                if (in_array($k, $versions)) {
                    $list[] = [
                        'id' => $k,
                        'name' => $val,
                        'value' => $val
                    ];
                }
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