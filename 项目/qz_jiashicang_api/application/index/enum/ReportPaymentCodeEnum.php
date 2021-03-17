<?php
// +----------------------------------------------------------------------
// | ReportPaymentCodeEnum 小报备相关枚举值定义
// +----------------------------------------------------------------------

namespace app\index\enum;

use app\common\enum\BaseStatusCodeEnum;

class ReportPaymentCodeEnum extends BaseStatusCodeEnum {

    // 业绩档位
    protected static $achievement_grade = array(
        "1" => "5万以下",
        "2" => "5-8万",
        "3" => "8-10万",
        "4" => "10-13万",
        "5" => "13-15万",
        "6" => "15万以上",
    );

    // 业绩档位
    protected static $achievement_grade_list = array(
        [
            "id" => 1,
            "name" => "5万以下",
            "min" => 0,
            "max" => 50000,
            "number" => 0
        ],
        [
            "id" => 2,
            "name" => "5-8万",
            "min" => 50000,
            "max" => 80000,
            "number" => 0
        ],
        [
            "id" => 3,
            "name" => "8-10万",
            "min" => 80000,
            "max" => 100000,
            "number" => 0
        ],
        [
            "id" => 4,
            "name" => "10-13万",
            "min" => 100000,
            "max" => 130000,
            "number" => 0
        ],
        [
            "id" => 5,
            "name" => "13-15万",
            "min" => 130000,
            "max" => 150000,
            "number" => 0
        ],
        [
            "id" => 6,
            "name" => "15万以上",
            "min" => 150000,
            "max" => 0,
            "number" => 0
        ]
    );

    public static function getAchievementGradeList(){
        return static::$achievement_grade_list;
    }
}