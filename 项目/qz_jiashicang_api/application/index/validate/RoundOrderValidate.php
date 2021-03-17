<?php
// +----------------------------------------------------------------------
// | RoundOrderValidate 轮单相关数据验证
// +----------------------------------------------------------------------

namespace app\index\validate;

use think\Validate;

class RoundOrderValidate extends Validate {

    // 验证并补全数据
    public static function checkApplyDetailedData(&$input){

        // 分单时间处理
        if (!empty($input["fen_begin"]) && !empty($input["fen_end"])) {
            $input["fen_begin"] = date("Y-m-d", strtotime($input["fen_begin"]));
            $input["fen_end"] = date("Y-m-d", strtotime($input["fen_end"]));
        } else if (!empty($input["fen_begin"]) && empty($input["fen_end"])) {
            $input["fen_begin"] = date("Y-m-d", strtotime($input["fen_begin"]));
            $input["fen_end"] = date("Y-m-t", strtotime($input["fen_begin"]));
        } else if (empty($input["fen_begin"]) && !empty($input["fen_end"])) {
            $input["fen_end"] = date("Y-m-d", strtotime($input["fen_end"]));
            $input["fen_begin"] = date("Y-m-01", strtotime($input["fen_end"]));
        } else {
            $input["fen_begin"] = "";
            $input["fen_end"] = "";
        }

        // 申请时间处理
        if (!empty($input["apply_begin"]) && !empty($input["apply_end"])) {
            $input["apply_begin"] = date("Y-m-d", strtotime($input["apply_begin"]));
            $input["apply_end"] = date("Y-m-d", strtotime($input["apply_end"]));
        } else if (!empty($input["apply_begin"]) && empty($input["apply_end"])) {
            $input["apply_begin"] = date("Y-m-d", strtotime($input["apply_begin"]));
            $input["apply_end"] = date("Y-m-t", strtotime($input["apply_begin"]));
        } else if (empty($input["apply_begin"]) && !empty($input["apply_end"])) {
            $input["apply_end"] = date("Y-m-d", strtotime($input["apply_end"]));
            $input["apply_begin"] = date("Y-m-01", strtotime($input["apply_end"]));
        } else {
            $input["apply_begin"] = "";
            $input["apply_end"] = "";
        }

        // 城市查询参数
        $input["city_ids"] = sys_check_variable($input["city_ids"], []);
        if (!empty($input["city_ids"]) && is_string($input["city_ids"])) {
            $input["city_ids"] = explode(",", $input["city_ids"]);
        }

        // 处理默认参数
        $input["order_id"] = sys_check_variable($input["order_id"]);
        $input["company_id"] = sys_check_variable($input["company_id"]);
        $input["export"] = empty($input["export"]) ? false : true;

        if (empty($input["order_id"]) && empty($input["company_id"])) {
            if (empty($input["fen_begin"]) && empty($input["fen_end"])) {
                if (empty($input["apply_begin"]) && empty($input["apply_end"])) {
                    return "请先选择查询时间";
                }
            }
        }

        return true;
    }

}