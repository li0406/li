<?php

namespace app\index\enum;

class CompanyTrackCodeEnum {

    // 回访类型
    private static $code_track_step = [
        "1" => "初次跟进",
        "2" => "量房",
        "3" => "方案",
        "4" => "签单"
    ];

    // 获取状态列表
    public static function getList($module){
        $key = "code_" . strtolower($module);

        $list = [];
        if (isset(static::$$key)) {
            foreach (static::$$key as $k => $val) {
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