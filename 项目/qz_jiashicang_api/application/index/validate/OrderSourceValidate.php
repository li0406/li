<?php
// +----------------------------------------------------------------------
// | OrderSourceValidate 城市相关数据验证
// +----------------------------------------------------------------------

namespace app\index\validate;

use think\Validate;
use think\facade\Request;
use app\index\enum\OrderSourceCodeEnum;

class OrderSourceValidate extends Validate {

    // 验证并补全查询数据-渠道数据统计
    public static function checkOrderSourceDetailedData(&$input, $gtype = 1, $gdate = false){
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $minbegintime = strtotime("-3 months", strtotime($input["date_end"]));
            if ($minbegintime > strtotime($input["date_begin"])) {
                return "查询时间不能超过3个月";
            }
        }

        // 默认查询时间
        if (!empty($input["date_begin"]) && empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-d", strtotime($input["date_begin"]));
            $input["date_end"] = date("Y-m-t", strtotime($input["date_begin"]));
        } else if (empty($input["date_begin"]) && !empty($input["date_end"])) {
            $input["date_end"] = date("Y-m-d", strtotime($input["date_end"]));
            $input["date_begin"] = date("Y-m-01", strtotime($input["date_end"]));
        } else if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-d", strtotime($input["date_begin"]));
            $input["date_end"] = date("Y-m-d", strtotime($input["date_end"]));
        } else {
            $input["date_begin"] = date("Y-m-01");
            $input["date_end"] = date("Y-m-d");
        }

        // 处理默认数据
        $input["tab"] = $input["tab"] ?? OrderSourceCodeEnum::TAB_SRC;
        $input["group_id"] = sys_check_variable($input["group_id"]);
        $input["export"] = sys_check_variable($input["export"]) == 1;

        if ($gtype == OrderSourceCodeEnum::GROUP_TYPE_SOURCE) {
            $input["source"] = sys_check_variable($input["source"]);
        } else {
            $input["src"] = sys_check_variable($input["src"]);
            $input["dept_id"] = sys_check_variable($input["dept_id"]);
            $input["city_id"] = sys_check_variable($input["city_id"]);
        }

        $input["sort_field"] = sys_check_variable($input["sort_field"]);
        $input["sort_rule"] = sys_check_variable($input["sort_rule"]);
        if ($gdate == true) {
            $input["sort_field"] = $input["sort_field"] ? : "date";
            $input["sort_rule"] = $input["sort_rule"] == "asc" ? SORT_ASC : SORT_DESC;
        }

        // 按城市统计必须选择城市
        if ($input["tab"] == OrderSourceCodeEnum::TAB_SRC_CITY && empty($input["city_id"])) {
            return "请先选择城市";
        }

        return true;
    }
}