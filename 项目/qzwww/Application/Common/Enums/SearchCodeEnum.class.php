<?php

namespace Common\Enums;

class SearchCodeEnum {

    private static $sceneword = array(
        "xfzx" => "新房装修",
        "esfzx" => "二手房装修",
        "bszx" => "别墅装修",
        "jbzx" => "局部装修",
        "syzx" => "办公室装修,写字楼装修,KTV装修,饭店装修,店面装修,酒店装修",
        "hxgz" => "户型改造",
        "dpzx" => "店铺装修",
        "xzlzx" => "写字楼装修",
    );

    private static $sceneword_show = array(
        "xfzx" => "新房",
        "esfzx" => "二手房",
        "bszx" => "别墅",
        "jbzx" => "局部",
        "syzx" => "商业"
    );

    public static function getSceneWord($module){
        if (array_key_exists($module, static::$sceneword)) {
            return static::$sceneword[$module];
        }
    }

    public static function getSceneWordShow($module){
        if (array_key_exists($module, static::$sceneword_show)) {
            return static::$sceneword_show[$module];
        }
    }

}