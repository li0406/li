<?php

namespace Common\Enums;

class PnpCodeEnum {

    // 呼叫类型
    const CODE_CALLTYPE_CALLING = 10; // 通话主叫
    const CODE_CALLTYPE_CALLED = 11; // 通话被叫

    protected static $calltype = array(
        self::CODE_CALLTYPE_CALLING => "通话主叫",
        self::CODE_CALLTYPE_CALLED => "通话被叫",
    );

    protected static $releasedir = array(
        "1" => "主叫挂断",
        "2" => "被叫挂断",
        "0" => "平台释放"
    );

    public static function getItem($field, $key, $default = ""){
        if (isset(static::$$field)) {
            $variable = static::$$field;
            if (array_key_exists($key, $variable)) {
                $default = $variable[$key];
            }
        }

        return $default;
    }
}