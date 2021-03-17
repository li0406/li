<?php
// +----------------------------------------------------------------------
// | CityAreaCodeEnum 城市区域相关枚举值定义
// +----------------------------------------------------------------------

namespace app\index\enum;

class CityAreaCodeEnum {

    // 数据聚合维度
    const TAB_CITY = 1; // 按城市
    const TAB_AREA = 2; // 按区域

    // 字段隐藏显示
    const FIELD_SHOW = 1; // 显示
    const FIELD_HIDE = 2; // 隐藏

    public static $city_little = [
        "A" => "A类城市",
        "B" => "B类城市",
        "C" => "C类城市",
        "T" => "其它城市",
    ];

}