<?php
// +----------------------------------------------------------------------
// | UserValidate 用户相关数据验证
// +----------------------------------------------------------------------

namespace app\index\validate;

use think\Validate;

class UserValidate extends Validate {

    // 验证并补全数据
    public static function checkUserDetailedData(&$input){
        if (empty($input["date_begin"]) && empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-01");
            $input["date_end"] = date("Y-m-d");
        } else if (!empty($input["date_begin"]) && empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-d", strtotime($input["date_begin"]));
            $input["date_end"] = date("Y-m-t", strtotime($input["date_begin"]));
        } else if (empty($input["date_begin"]) && !empty($input["date_end"])) {
            $input["date_end"] = date("Y-m-d", strtotime($input["date_end"]));
            $input["date_begin"] = date("Y-m-01", strtotime($input["date_end"]));
        } else {
            $input["date_begin"] = date("Y-m-d", strtotime($input["date_begin"]));
            $input["date_end"] = date("Y-m-d", strtotime($input["date_end"]));
        }

        $todaytime = strtotime(date("Y-m-d"));
        $input["datetime"] = strtotime($input["date_end"]);
        if ($input["datetime"] >= $todaytime) {
            $input["datetime"] = $todaytime;
        }

        // 城市查询参数
        $input["city_ids"] = sys_check_variable($input["city_ids"], []);
        if (!empty($input["city_ids"]) && is_string($input["city_ids"])) {
            $input["city_ids"] = explode(",", $input["city_ids"]);
        }

        // 处理默认参数
        $input["company_id"] = sys_check_variable($input["company_id"]);

        return true;
    }

    /**
     * @des:时间卡片参数校验
     * @param $input
     * @return bool|string
     */
    public static function checkSignVipAnalysis($input)
    {
        if (!isset($input['tab_month']) || strlen($input['tab_month']) < 0) {
            return '参数缺失!';
        }
        if (!in_array($input['tab_month'], [1, 2])) {
            return '参数不合法!';
        }
        return true;
    }

}