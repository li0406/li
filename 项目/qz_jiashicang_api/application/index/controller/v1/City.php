<?php

// +----------------------------------------------------------------------
// | 城市数据控制器
// +----------------------------------------------------------------------

namespace app\index\controller\v1;

use app\index\model\logic\UserLogic;
use app\index\validate\UserValidate;
use think\Request;
use think\Exception;
use think\Controller;
use app\index\model\logic\CityLogic;
use app\index\model\logic\AdminuserLogic;
use app\index\model\logic\CityReportLogic;

use app\index\validate\CityValidate;
use app\common\enum\BaseStatusCodeEnum;

class City extends Controller {

    // 获取所有已开站城市
    public function getCityAll(Request $request, CityLogic $cityLogic){
        $list = $cityLogic->getAllOpenCity();

        $data = [
            "list" => $list,
            "count" => count($list)
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 获取所有已开站城市
    public function getCityAreaList(Request $request, CityLogic $cityLogic){
        $city_ids = $request->get("city_ids");
        $list = $cityLogic->getCityAreaList($city_ids);

        $data = [
            "list" => $list,
            "count" => count($list)
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 公共数据分析-城市数据明细
    public function getCityOrderDetailed(Request $request, CityLogic $cityLogic) {
        $input = $request->get();

        // 验证并补全查询数据
        $checked = CityValidate::checkCityOrderDetailedData($input);
        if ($checked !== true) {
            return json(sys_response(4000002, $checked));
        }

        // 查询城市增加权限城市
        $input["city_ids"] = CityValidate::setAuthCity($input["city_ids"]);

        $list = $cityLogic->getCityOrderDetailedList($input["tab"], $input);

        $data = [
            "list" => $list,
            "count" => count($list),
            "options" => [
                "date_begin" => $input["date_begin"],
                "date_end" => $input["date_end"]
            ]
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 公共数据分析-城市分单数据明细
    public function getCityFendanDetailed(Request $request, CityLogic $cityLogic) {
        $input = $request->get();

        // 验证并补全查询数据
        $checked = CityValidate::checkCityFendanDetailedData($input);
        if ($checked !== true) {
            return json(sys_response(4000002, $checked));
        }

        // 查询城市增加权限城市
        $input["city_ids"] = CityValidate::setAuthCity($input["city_ids"]);

        $list = $cityLogic->getCityFendanDetailedList($input);

        $data = [
            "list" => $list,
            "options" => [
                "date_begin" => $input["date_begin"],
                "date_end" => $input["date_end"]
            ]
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 公共数据分析-城市会员数据明细
    public function getCityUserDetailed(Request $request, CityLogic $cityLogic){
        $input = $request->get();

        // 验证并补全查询数据
        $checked = CityValidate::checkCityUserDetailedData($input);
        if ($checked !== true) {
            return json(sys_response(4000002, $checked));
        }

        // 查询城市增加权限城市
        $input["city_ids"] = CityValidate::setAuthCity($input["city_ids"]);

        $list = $cityLogic->getCityUserDetailedList($input);

        $data = [
            "list" => $list,
            "options" => [
                "date_begin" => $input["date_begin"],
                "date_end" => $input["date_end"]
            ]
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }


    /**
     * @des:城市报表-签单排行榜
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function getSignRanking(Request $request)
    {
        $param = $request->get();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $logic = new CityReportLogic();
        $result = $logic->getSignRanking($city_ids);
        return sys_response(0, BaseStatusCodeEnum::CODE_0, $result);
    }

    /**
     * @des:签单率
     * @param Request $request
     * @return array
     */
    public function getSignRate(Request $request)
    {
        $param = $request->get();
        $logic = new CityReportLogic();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $result = $logic->getSignRate($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:获取签单距离占比相关
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function getSignDistance(Request $request)
    {
        $param = $request->get();
        $logic = new CityReportLogic();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $result = $logic->getSignDistance($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:总签单金额
     * @param Request $request
     * @return array
     */
    public function getSignMoneyTotal(Request $request)
    {
        $logic = new CityReportLogic();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);
        
        $result = $logic->getSignMoneyTotal($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    // 全国城市重点数据
    public function getCityImportantList(Request $request, CityLogic $cityLogic) {
        $tab_month = $request->get("tab_month", 1);
        if ($tab_month == 2) {
            $date_begin = date("Y-m-01", strtotime("-2 months"));
        } else {
            $date_begin = date("Y-m-01");
        }

        $date_end = date("Y-m-d");
        $list = $cityLogic->getCityImportantList($date_begin, $date_end);

        $data = [
            "list" => $list,
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 当月订单不足城市
    public function getCityOrderLackList(Request $request, CityLogic $cityLogic){
        $date_begin = date("Y-m-01");
        $date_end = date("Y-m-d");

        $list = $cityLogic->getCityOrderLackList($date_begin, $date_end);

        $data = [
            "list" => $list,
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 城市订单价格不足TOP20
    public function getCityOrderPriceInsuffList(Request $request, CityLogic $cityLogic){
        $tab_month = $request->get("tab_month", 1);
        if ($tab_month == 2) {
            $date_begin = date("Y-m-01", strtotime("-2 months"));
        } else {
            $date_begin = date("Y-m-01");
        }

        $date_end = date("Y-m-d");
        $list = $cityLogic->getCityOrderPriceInsuffList($date_begin, $date_end);

        $data = [
            "list" => $list,
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 主面板-发单浪费率
    public function getCityOrderFawaste(Request $request, CityLogic $cityLogic){
        $tab_month = $request->get("tab_month", 1);
        if ($tab_month == 2) {
            $date_begin = date("Y-m-01", strtotime("-2 months"));
        } else {
            $date_begin = date("Y-m-01");
        }

        $date_end = date("Y-m-d", strtotime("-1 days"));
        $list = $cityLogic->getCityOrderFawasteList($date_begin, $date_end);

        $data = [
            "series" => [
                [
                    "name" => "发单量",
                    "type" => "bar",
                    "data" => array_column($list, "fa_count"),
                ],
                [
                    "name" => "发单浪费量",
                    "type" => "bar",
                    "data" => array_column($list, "waste_count"),
                ],
                [
                    "name" => "发单浪费率",
                    "type" => "line",
                    "data" => array_column($list, "waste_lv"),
                ]
            ],
            "xAxis" => array_column($list, "city_little_name"),
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 主面板-分单浪费率
    public function getCityOrderFenwaste(Request $request, CityLogic $cityLogic){
        $tab_month = $request->get("tab_month", 1);
        if ($tab_month == 2) {
            $date_begin = date("Y-m-01", strtotime("-2 months"));
        } else {
            $date_begin = date("Y-m-01");
        }

        $date_end = date("Y-m-d", strtotime("-1 days"));
        $list = $cityLogic->getCityOrderFenwasteList($date_begin, $date_end);

        $data = [
            "series" => [
                [
                    "name" => "实际分单量",
                    "type" => "bar",
                    "data" => array_column($list, "csos_fennum"),
                ],
                [
                    "name" => "分配次数",
                    "type" => "bar",
                    "data" => array_column($list, "allot_fennum"),
                ],
                [
                    "name" => "浪费次数",
                    "type" => "bar",
                    "data" => array_column($list, "waste_fennum"),
                ],
                [
                    "name" => "分单浪费率",
                    "type" => "line",
                    "data" => array_column($list, "waste_lv"),
                ]
            ],
            "xAxis" => array_column($list, "city_little_name"),
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    /**
     * @des:城市企业潜力分析
     * @param CityLogic $logic
     * @return array
     */
    public function getCityPotential(CityLogic $logic)
    {
        $result = $logic->getCityPotential();
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:各省份会员数
     */
    public function getSfSignAnalysis(UserLogic $logic)
    {
        $result = $logic->getSfSignAnalysis();
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }
}
