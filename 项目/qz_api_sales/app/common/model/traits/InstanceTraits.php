<?php

namespace app\common\model\traits;

trait InstanceTraits {

    protected static $_instance;

    public static function instance($reload = false){
        if ($reload == true || empty(static::$_instance)) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

}