<?php

namespace Common\Enums;

class XgtCodeEnum {

    private static $mianji = array(
        array(
            "id" => "1",
            "name" => "60㎡以下",
            "min_value" => "",
            "max_value" => "60",
        ),
        array(
            "id" => "2",
            "name" => "60~80㎡",
            "min_value" => "60",
            "max_value" => "80",
        ),
        array(
            "id" => "3",
            "name" => "80~100㎡",
            "min_value" => "80",
            "max_value" => "100",
        ),
        array(
            "id" => "4",
            "name" => "100~130㎡",
            "min_value" => "100",
            "max_value" => "130",
        ),
        array(
            "id" => "5",
            "name" => "130~160㎡",
            "min_value" => "130",
            "max_value" => "160",
        ),
        array(
            "id" => "6",
            "name" => "160~190㎡",
            "min_value" => "160",
            "max_value" => "190",
        ),
        array(
            "id" => "7",
            "name" => "190㎡以上",
            "min_value" => "190",
            "max_value" => "",
        )
    );
    
    public static function getMianji($id = ""){
        $list = static::$mianji;

        if (!empty($id)) {
            foreach ($list as $key => $item) {
                if ($item["id"] == $id) {
                    return $item;
                }
            }

            return false;
        }

        return $list;
    }

    public static function getMjSearchMap($mj){
        $map = [
            "min_value" => "",
            "max_value" => ""
        ];

        if (!empty($mj)) {
            $map = static::getMianji($mj);
        }

        return $map;
    }
}