<?php
// +----------------------------------------------------------------------
// | CityValidate 城市相关数据验证
// +----------------------------------------------------------------------

namespace app\index\validate;

use think\Validate;
use think\facade\Request;
use app\index\enum\CityAreaCodeEnum;
use app\index\enum\AdminuserCodeEnum;
use app\index\model\logic\AdminAuthLogic;

class CityValidate extends Validate {

    // 验证并补全销售-城市数据明细查询数据
    public static function checkCityOrderDetailedData(&$input){
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $minbegintime = strtotime("-3 months", strtotime($input["date_end"]));
            if ($minbegintime > strtotime($input["date_begin"])) {
                return "查询时间不能超过3个月";
            }
        }

        // 默认查询时间
        if (!empty($input["date_begin"]) && empty($input["date_end"])) {
            $input["date_end"] = date("Y-m-t", strtotime($input["date_begin"]));
        } else if (empty($input["date_begin"]) && !empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-01", strtotime($input["date_end"]));
        } else if (empty($input["date_begin"]) && empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-01");
            $input["date_end"] = date("Y-m-d");
        }

        // 城市查询参数
        $input["city_ids"] = sys_check_variable($input["city_ids"], []);
        if (!empty($input["city_ids"]) && is_string($input["city_ids"])) {
            $input["city_ids"] = explode(",", $input["city_ids"]);
        }

        // 区域查询参数
        $input["area_ids"] = sys_check_variable($input["area_ids"], []);
        if (!empty($input["area_ids"]) && is_string($input["area_ids"])) {
            $input["area_ids"] = explode(",", $input["area_ids"]);
        }

        // 处理默认数据
        $input["tab"] = $input["tab"] ?? CityAreaCodeEnum::TAB_CITY;
        $input["show_fadan"] = $input["show_fadan"] ?? CityAreaCodeEnum::FIELD_SHOW;
        $input["show_fendan"] = $input["show_fendan"] ?? CityAreaCodeEnum::FIELD_SHOW;
        $input["show_achievement"] = $input["show_achievement"] ?? CityAreaCodeEnum::FIELD_SHOW;
        $input["show_liangfang"] = $input["show_liangfang"] ?? CityAreaCodeEnum::FIELD_SHOW;
        $input["show_qiandan"] = $input["show_qiandan"] ?? CityAreaCodeEnum::FIELD_SHOW;
        $input["show_jugai"] = $input["show_jugai"] ?? CityAreaCodeEnum::FIELD_SHOW;
        $input["show_mianji"] = $input["show_mianji"] ?? CityAreaCodeEnum::FIELD_HIDE;
        $input["show_fangshi"] = $input["show_fangshi"] ?? CityAreaCodeEnum::FIELD_HIDE;
        $input["show_leixing"] = $input["show_leixing"] ?? CityAreaCodeEnum::FIELD_HIDE;

        $input["sort_field"] = sys_check_variable($input["sort_field"]);
        $input["sort_rule"] = sys_check_variable($input["sort_rule"], "desc");
        $input["sort_rule"] = strtolower($input["sort_rule"]) == "asc" ? SORT_ASC : SORT_DESC;

        return true;
    }

    // 验证并补全数据
    public static function checkCityFendanDetailedData(&$input){
        $todaytime = strtotime(date("Y-m-d"));
        if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
            $minbegintime = strtotime("-3 months", strtotime($input["date_end"]));
            if ($minbegintime > strtotime($input["date_begin"])) {
                return "查询时间不能超过3个月";
            }
        }

        // if (!empty($input["date_begin"]) && !empty($input["date_end"])) {
        //     $begin_month = date("Y-m-01", strtotime($input["date_begin"]));
        //     $end_month = date("Y-m-01", strtotime($input["date_end"]));
        //     if (strtotime($begin_month) != strtotime($end_month)) {
        //         return "查询时间不能跨月";
        //     }
        // }

        // 默认查询时间
        if (!empty($input["date_begin"]) && empty($input["date_end"])) {
            $input["date_end"] = date("Y-m-t", strtotime($input["date_begin"]));
            if (strtotime($input["date_end"]) > $todaytime) {
                $input["date_end"] = date("Y-m-d");
            }
        } else if (empty($input["date_begin"]) && !empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-01", strtotime($input["date_end"]));
        } else if (empty($input["date_begin"]) && empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-01");
            $input["date_end"] = date("Y-m-d");
        }

        if (strtotime($input["date_end"]) > $todaytime) {
            return "查询时间不能超过当前日期";
        }

        // 是否跨月
        $month_begin = date("Y-m", strtotime($input["date_begin"]));
        $month_end = date("Y-m", strtotime($input["date_end"]));
        $input["in_month"] = $month_begin == $month_end;

        // 城市查询参数
        $input["city_ids"] = sys_check_variable($input["city_ids"], []);
        if (!empty($input["city_ids"]) && is_string($input["city_ids"])) {
            $input["city_ids"] = explode(",", $input["city_ids"]);
        }

        // 城市缺单统计查询日期
        $input["lack_date"] = $input["date_end"];
        if (strtotime($input["date_end"]) >= $todaytime) {
            $input["lack_date"] = date("Y-m-d");
            if (date("H") < 12) {
                $input["lack_date"] = date("Y-m-d", strtotime("-1 day"));
            }
        }

        $input["sort_field"] = sys_check_variable($input["sort_field"]);
        $input["sort_rule"] = sys_check_variable($input["sort_rule"], "desc");
        $input["sort_rule"] = strtolower($input["sort_rule"]) == "asc" ? SORT_ASC : SORT_DESC;

        return true;
    }

    // 验证并补全数据
    public static function checkCityUserDetailedData(&$input){
        if (!empty($input["date_end"])) {
            $input["date_begin"] = date("Y-m-01", strtotime($input["date_end"]));
        } else {
            $input["date_begin"] = date("Y-m-01");
            $input["date_end"] = date("Y-m-d");
        }

        $todaytime = strtotime(date("Y-m-d"));
        if (strtotime($input["date_end"]) > $todaytime) {
            return "查询时间不能超过当前日期";
        }

        // 城市查询参数
        $input["city_ids"] = sys_check_variable($input["city_ids"], []);
        if (!empty($input["city_ids"]) && is_string($input["city_ids"])) {
            $input["city_ids"] = explode(",", $input["city_ids"]);
        }

        $input["sort_field"] = sys_check_variable($input["sort_field"]);
        $input["sort_rule"] = sys_check_variable($input["sort_rule"], "desc");
        $input["sort_rule"] = strtolower($input["sort_rule"]) == "asc" ? SORT_ASC : SORT_DESC;

        return true;
    }

    // 补充权限城市
    public static function setAuthCity($city_ids = []){
        if ($city_ids && !is_array($city_ids)) {
            $city_ids = explode(",", $city_ids);
        }
        
        $userInfo = Request::instance()->user;
        if ($userInfo["dept_top_id"] == AdminuserCodeEnum::DEPT_SALES_CENTER){
            $cityIds = AdminAuthLogic::getSalerAuthCitys();
            if (!empty($city_ids)) {
                $city_ids = array_intersect($city_ids, $cityIds);
            } else {
                $city_ids = $cityIds;
            }

            if (empty($city_ids)) {
                $city_ids = ["none"];
            }
        }

        return $city_ids;
    }

}