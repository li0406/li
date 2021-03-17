<?php
// +----------------------------------------------------------------------
// | OrderSourceAccountValidate 城市相关数据验证
// +----------------------------------------------------------------------

namespace app\index\validate;

use think\Validate;
use think\facade\Request;

class OrderSourceRoiValidate extends Validate {

    // 验证并补全查询数据
    public static function checkRoiStatsData(&$input){
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $minbegintime = strtotime("-3 months", strtotime($input["date_end"]));
            if ($minbegintime > strtotime($input["date_begin"])) {
                return "查询时间不能超过3个月";
            }
        }

        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-d", strtotime($input["date_begin"]));
            $input["date_end"] = date("Y-m-d", strtotime($input["date_end"]));
        } else if (!empty($input["date_begin"]) && empty($input["date_end"])) {
            $begintime = strtotime($input["date_begin"]);
            $input["date_begin"] = date("Y-m-d", $begintime);
            $input["date_end"] = date("Y-m-t", $begintime);
        } else if (empty($input["date_begin"]) && !empty($input["date_end"])) {
            $endtime = strtotime($input["date_end"]);
            $input["date_begin"] = date("Y-m-01", $endtime);
            $input["date_end"] = date("Y-m-d", $endtime);
        } else {
            $input["date_begin"] = date("Y-m-01");
            $input["date_end"] = date("Y-m-d");
        }

        // 账号查询参数
        $input["account_ids"] = sys_check_variable($input["account_ids"], []);
        if (!empty($input["account_ids"]) && is_string($input["account_ids"])) {
            $input["account_ids"] = explode(",", $input["account_ids"]);
        }

        // 导出参数
        $input["export"] = sys_check_variable($input["export"]) == 1;

        return true;
    }
}