<?php

namespace app\index\controller\v1;

use app\common\enum\BaseStatusCodeEnum;
use app\index\enum\AdminuserCodeEnum;
use app\index\model\logic\OrderLogic;
use app\index\validate\CityValidate;
use app\index\validate\UserValidate;
use think\Controller;
use think\Request;

/**
 *
 */
class Order extends Controller
{
    private $auth_user_id = 0;

    public function initialize(){
        $user = request()->user;
        if ($user["dept_top_id"] == AdminuserCodeEnum::DEPT_MARKET_CENTER) {
            $this->auth_user_id = $user["user_id"];
        }
    }

    public function getAllOrderCount()
    {
        try {
            $type = input("get.type");
            $city = CityValidate::setAuthCity();

            $begin = mktime(0,0,0,date("m"),1,date("Y"));
            $end = mktime(0,0,0,date("m"),date("t"),date("Y")) + 86400;

            //获取当月的总单量
            $logic = new OrderLogic();
            $now_month = $logic->getCityOrderCount($begin,$end,$type,$city);

            //获取上个月的总单量
            $prev_begin = strtotime("-1 month",$begin);
            $prev_end = strtotime("-1 month",$end);
            $prev_month = $logic->getCityOrderCount($prev_begin,$prev_end,$type,$city);

            //获取上一年的总单量
            $begin = strtotime("-1 Year",$begin);
            $end = strtotime("-1 Year",$end);
            $prev_year = $logic->getCityOrderCount($begin,$end,$type,$city);

            $data = conversion_data($now_month,$prev_month,$prev_year);

            return sys_response(0,BaseStatusCodeEnum::CODE_0,$data);
        } catch (\Exception $e) {
            return sys_response(5000003,BaseStatusCodeEnum::CODE_5000003);
        }
    }

    public function getOrderLiangFangRate()
    {
        try {
            $begin = mktime(0,0,0,date("m"),1,date("Y"));
            $end = mktime(0,0,0,date("m"),date("t"),date("Y")) + 86400;
            $city = CityValidate::setAuthCity();

            //获取当月的总单量
            $logic = new OrderLogic();
            $now_month = $logic->getOrderLiangFangRate($begin,$end,$city);

            //获取上个月的总单量
            $logic = new OrderLogic();
            $begin = strtotime("-1 month",$begin);
            $end = strtotime("-1 month",$end);
            $prev_month = $logic->getOrderLiangFangRate($begin,$end,$city);

            $result = conversion_data($now_month["count"],$prev_month["count"]);

            $data = [
                "now_percent" => [
                    "rate" => $now_month["rate"],
                    "count" => $now_month["count"],
                    "reverse_count" => $now_month["un_count"]
                ] ,
                "month_percent" => $result["month_percent"]
            ];

            return sys_response(0,BaseStatusCodeEnum::CODE_0,$data);

        } catch (\Exception $e) {
            return sys_response(5000003,BaseStatusCodeEnum::CODE_5000003);
        }
    }

    public function getCityTransformingData()
    {
        try {
            $step = input("get.step",1);
            $end = strtotime(date("Y-m-d")) + 86400 - 1;
            $city = CityValidate::setAuthCity();

            switch ($step) {
                default:
                    return sys_response(4000001,BaseStatusCodeEnum::CODE_4000001);
                case 1:
                    //近一个月
                    $begin = strtotime("-1 month",$end);
                    break;
                case 2:
                    //近三个月
                    $begin = strtotime("-3 month",$end);
                    break;
                case 3:
                    //近半年
                    $begin = strtotime("-6 month",$end);
                    break;
                case 4:
                    //近一年
                    $begin = strtotime("-1 year",$end);
                    break;
            }

            $logic = new OrderLogic();
            $data = $logic->getOrderData($begin,$end,$city);
            return sys_response(0,BaseStatusCodeEnum::CODE_0,$data);
        } catch (\Exception $e) {
            return sys_response(5000003,BaseStatusCodeEnum::CODE_5000003);
        }
    }

    public function getApplyOrder()
    {
        try {
            $begin = mktime(0,0,0,date("m"),1,date("Y"));
            $end = mktime(0,0,0,date("m"),date("t"),date("Y")) + 86400;
            $city = CityValidate::setAuthCity();

            $logic = new OrderLogic();
            //当月申请补轮
            $now_month =  $logic->getOrderApplyCount($begin,$end,$city);

            //上个月申请补轮
            $begin = strtotime("-1 month",$begin);
            $end = strtotime("-1 month",$end);
            $prev_month =  $logic->getOrderApplyCount($begin,$end,$city);

            $result = conversion_data($now_month["count"],$prev_month["count"]);

            $data = [
                "now_percent" => [
                    "rate" => $now_month["rate"],
                    "count" => $now_month["count"]
                ] ,
                "month_percent" => $result["month_percent"]
            ];
            return sys_response(0,BaseStatusCodeEnum::CODE_0,$data);
        } catch (\Exception $e) {
            return sys_response(5000003,BaseStatusCodeEnum::CODE_5000003);
        }
    }

    public function getApplyPassOrder()
    {
        try {
            $begin = mktime(0,0,0,date("m"),1,date("Y"));
            $end = mktime(0,0,0,date("m"),date("t"),date("Y")) + 86400;
            $city = CityValidate::setAuthCity();

            $logic = new OrderLogic();
            //当月申请补轮
            $now_month =  $logic->getApplyPassOrder($begin,$end,$city);

            //上个月申请补轮
            $begin = strtotime("-1 month",$begin);
            $end = strtotime("-1 month",$end);
            $prev_month =  $logic->getApplyPassOrder($begin,$end,$city);

            $result = conversion_data($now_month["count"],$prev_month["count"]);

            $data = [
                "now_percent" => [
                    "rate" => $now_month["rate"],
                    "count" => $now_month["count"]
                ] ,
                "month_percent" => $result["month_percent"]
            ];
            return sys_response(0,BaseStatusCodeEnum::CODE_0,$data);

        } catch (\Exception $e) {
            return sys_response(5000003,BaseStatusCodeEnum::CODE_5000003);
        }
    }

    public function getApplyPassOrderFull()
    {
        try {
            $begin = mktime(0,0,0,1,1,date("Y"));
            $end = mktime(0,0,0,12,31,date("Y")) + 86400;
            $city = CityValidate::setAuthCity();

            //2.0会员的实际分单量
            $model = new OrderLogic();
            $fen_data = $model->getFullOrderFen($begin,$end,$city);

            //审核通过补轮数
            $pass_data = $model->getApplyPassFull($begin,$end,$city);

            $data['legend'] = [
                "2.0会员的实际分单量","补轮量","补轮通过率"
            ];

            $data["series"] = [
                [
                    "name" => "2.0会员的实际分单量",
                    "type" => "bar",
                    'data' => []
                ],
                [
                    "name" => "补轮量",
                    "type" => "bar",
                    'data' => []
                ],
                [
                    "name" => "补轮通过率",
                    "type" => "line",
                    'data' => []
                ]
            ];
            $data['xAxis'] = [];

            for ($i=1; $i <= date("m") ; $i++) {
                $data['xAxis'][] = $i."月";
                if ($i < 10 ) {
                    $i = "0".$i;
                }
                //2.0会员的实际分单量
                if (array_key_exists($i,$fen_data)) {
                    $data["series"][0]["data"][] = $fen_data[$i];
                } else {
                    $data["series"][0]["data"][] = 0;
                }

                //审核通过补轮数
                if (array_key_exists($i,$pass_data)) {
                    $data["series"][1]["data"][] = $pass_data[$i];
                } else {
                    $data["series"][1]["data"][] = 0;
                }

                //补轮通过率
                if (array_key_exists($i,$fen_data) && array_key_exists($i,$pass_data)) {
                    $data["series"][2]["data"][] = round($pass_data[$i]/$fen_data[$i],4) * 100;
                } else {
                    $data["series"][2]["data"][] = 0;
                }
            }


            $data["yAxis"] = [
                [
                    "max" => max( $data["series"][0]["data"]),
                    "min" => 0,
                    "interval" => 1000
                ],
                [
                    "max" => 100,
                    "min" => 0,
                    "interval" => 5
                ]
            ];
            return sys_response(0,BaseStatusCodeEnum::CODE_0,$data);
        } catch (\Exception $e) {
            return sys_response(5000003,BaseStatusCodeEnum::CODE_5000003);
        }
    }

    public function getOrderWaste()
    {
        try {
            $begin = mktime(0,0,0,1,1,date("Y"));
            $end = mktime(0,0,0,12,31,date("Y")) + 86400;
            $city = CityValidate::setAuthCity();

            //获取分单的详细数据
            $model = new OrderLogic();
            $waste = $model->getOrderWaste($begin,$end,$city);

            $data['legend'] = [
                "正常单浪费率","<60㎡浪费率","局改单浪费率","公装单浪费率","分单浪费率"
            ];
            $data["series"] = [
                [

                    'name' => "公装单浪费率",
                    'type' => "bar",
                    'data' => []
                ],
                [

                    'name' => "局改单浪费率",
                    'type' => "bar",
                    'data' => []
                ],
                [

                    'name' => "60㎡浪费率",
                    'type' => "bar",
                    'data' => []
                ],
                [
                    'name' => "正常单浪费率",
                    'type' => "bar",
                    'data' => []
                ],
                [

                    'name' => "分单浪费率",
                    'type' => "line",
                    'data' => []
                ]
            ];

            $data['xAxis'] = [];

            for ($i=1; $i <= date("m") ; $i++) {
                $data['xAxis'][] = $i."月";
                if ($i < 10 ) {
                    $i = "0".$i;
                }
                $index = date("Y").$i;

                //正常单浪费率
                if (array_key_exists($index,$waste[4])) {
                    $data["series"][3]["data"][] = $waste[4][$index]["rate"];
                } else {
                    $data["series"][3]["data"][] = 0;
                }

                //60m2浪费率
                if (array_key_exists($index,$waste[3])) {
                    $data["series"][2]["data"][] = $waste[3][$index]["rate"];
                } else {
                    $data["series"][2]["data"][] = 0;
                }

                //居改
                if (array_key_exists($index,$waste[2])) {
                    $data["series"][1]["data"][] = $waste[2][$index]["rate"];
                } else {
                    $data["series"][1]["data"][] = 0;
                }

                //公装
                if (array_key_exists($index,$waste[1])) {
                    $data["series"][0]["data"][] = $waste[1][$index]["rate"];
                } else {
                    $data["series"][0]["data"][] = 0;
                }

                //特殊单
                if (array_key_exists($index,$waste[5])) {
                    $data["series"][4]["data"][] = $waste[5][$index]["rate"];
                } else {
                    $data["series"][4]["data"][] = 0;
                }
            }

            $data["yAxis"] = [
                [
                    "max" => 100,
                    "min" => 0,
                    "interval" => 10
                ]
            ];
            return sys_response(0,BaseStatusCodeEnum::CODE_0,$data);
        }  catch (\Exception $e) {
            return sys_response(5000003,BaseStatusCodeEnum::CODE_5000003);
        }
    }

    public function getOrderAreaRate()
    {
        try {
            $begin = mktime(0,0,0,date("m"),1,date("Y"));
            $end = mktime(0,0,0,date("m"),date("t"),date("Y")) + 86400;
            $city = CityValidate::setAuthCity();

            $model = new OrderLogic();
            $data = $model->getOrderAreaRate($begin,$end,$city);

            return sys_response(0,BaseStatusCodeEnum::CODE_0,$data);
        }  catch (\Exception $e) {
            return sys_response(5000003,BaseStatusCodeEnum::CODE_5000003);
        }
    }

    public function getOrderFill()
    {
        try {
            $begin = mktime(0,0,0,1,1,date("Y"));
            $end = mktime(0,0,0,12,31,date("Y")) + 86400;
            $city = CityValidate::setAuthCity();

            $model = new OrderLogic();
            //获取月所需分单量有数据的城市
            $list = $model->getCityOrderActual($begin,$end,$city);

            //获取缺单月所需分单
            $list = $model->getMissCityOrderActual($begin,$end,$city,$list);

            //获取每月实际分单量/汇总数据
            $list = $model->getOrderMonthActual($begin,$end,$city,$list);

            //汇总每个月的数值
            $data['xAxis'] = [];

            $data["series"] = [
                [
                    'data' => []
                ],
            ];

            for ($i=1; $i <= date("m") ; $i++) {
                $data['xAxis'][] = $i."月";
                if ($i < 10 ) {
                    $i = "0".$i;
                }
                $index = date("Y").$i;
                if (array_key_exists($index,$list)) {
                    $data["series"][0]["data"][] = $list[$index]["rate"];
                } else {
                    $data["series"][0]["data"][] = 0;
                }
            }

            return sys_response(0,BaseStatusCodeEnum::CODE_0,$data);
        }  catch (\Exception $e) {
            return sys_response(5000003,BaseStatusCodeEnum::CODE_5000003);
        }
    }

    public function getOrderPrice()
    {
        try {
            $begin = mktime(0,0,0,1,1,date("Y"));
            $end = mktime(0,0,0,date("m"),date("t"),date("Y")) + 86400;
            $city = CityValidate::setAuthCity();

            $logic = new OrderLogic();
            //1.0会员消耗金额
            $normal = $logic->getMembershipNoramlPrice($begin,$end,$city);

            //2.0会员消耗金额
            $qian = $logic->getMembershipQianPrice($begin,$end,$city);

            //获取实际分单次数
            $orders = $logic->getOrderActualCountWithMonth($begin,$end,$city);

            $offset = date("m");

            $data['xAxis'] = [];

            $data["series"] = [
                [

                    'name' => "分单客单价",
                    'type' => "bar",
                    'data' => []
                ],
                [

                    'name' => "2.0会员分单客单价",
                    'type' => "bar",
                    'data' => []
                ],
            ];

            for ($i=1; $i <= $offset ; $i++) {
                $data['xAxis'][] = $i."月";
                if ($i < 10 ) {
                    $i = "0".$i;
                }
                $index = date("Y").$i;

                $normal_price = 0;
                if (array_key_exists($index,$normal)) {
                    $normal_price = $normal[$index];
                }

                $qian_price = 0;
                if (array_key_exists($index,$qian)) {
                    $qian_price = $qian[$index];
                }

                $fen_count = 0;
                $qian_count = 0;
                if (array_key_exists($index,$orders)) {
                    $fen_count = $orders[$index]['all_count'];
                    $qian_count = $orders[$index]['qian_count'];
                }

                if ($fen_count > 0) {
                    $data["series"][0]["data"][] =  round(($normal_price + $qian_price)/$fen_count,2);
                } else {
                    $data["series"][0]["data"][] = 0;
                }

                if ($qian_count > 0) {
                    $data["series"][1]["data"][] =  round($qian_price/$qian_count,2);
                } else {
                    $data["series"][1]["data"][] = 0;
                }
            }

            return sys_response(0,BaseStatusCodeEnum::CODE_0,$data);
        }  catch (\Exception $e) {
            return sys_response(5000003,BaseStatusCodeEnum::CODE_5000003);
        }
    }


    /**
     * @des:总消耗金额 1.0会员消耗（订单单价*总分单数）+2.0会员消耗，单位元
     * @param Request $request
     * @return array
     */
    public function getConsumptionTotal(Request $request)
    {
        $logic = new OrderLogic();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $result = $logic->getConsumptionTotal($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:分单平均次数
     * @param Request $request
     * @return array
     */
    public function getDistributeOrder(Request $request)
    {
        $logic = new OrderLogic();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $result = $logic->getDistributeOrder($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:订单撤回次数
     * @param Request $request
     * @return array
     */
    public function getOrderRebut(Request $request)
    {
        $logic = new OrderLogic();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $result = $logic->getOrderRebut($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:订单撤回详情
     * @param Request $request
     * @return array
     */
    public function getOrderRebutDetail(Request $request)
    {
        $logic = new OrderLogic();
        $param = $request->get();

        $city_ids = sys_check_variable($param["city_ids"]);
        $param["city_ids"] = CityValidate::setAuthCity($city_ids);

        $result = $logic->getOrderRebutDetail($param);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:违规补轮次数
     * @param Request $request
     * @return array
     */
    public function getViolateApply(Request $request)
    {
        $logic = new OrderLogic();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);
        
        $result = $logic->getViolateApply($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:违规补轮详情
     * @param Request $request
     * @return array
     */
    public function getViolateApplyDetail(Request $request)
    {
        $logic = new OrderLogic();
        $param = $request->get();

        $city_ids = sys_check_variable($param["city_ids"]);
        $param["city_ids"] = CityValidate::setAuthCity($city_ids);

        $result = $logic->getViolateApplyDetail($param);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:获取企业量房量/签单走势分析【近一年数据】
     * @param OrderLogic $logic
     * @return array
     */
    public function getLiangFangAndSignAnalysis(OrderLogic $logic)
    {
        $result = $logic->getLiangFangAndSignAnalysis();
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:驾驶舱中心板块
     * @param OrderLogic $logic
     * @return array
     */
    public function getDepartAchievement(OrderLogic $logic)
    {
        $result = $logic->getDepartAchievement();
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:各部门订单转化
     * @param Request $request
     * @param OrderLogic $logic
     * @return array
     */
    public function getDepartTransFormOrder(Request $request,OrderLogic $logic)
    {
        $input = $request->get();
        $input['auth_user_id'] = $this->auth_user_id;
        //数据校验
        $checked = UserValidate::checkSignVipAnalysis($input);
        if ($checked !== true) {
            return sys_response(4000002, $checked);
        }
        $result = $logic->getDepartTransFormOrder($input);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:各部门订单浪费率
     * @param Request $request
     * @param OrderLogic $logic
     * @return array
     */
    public function getDepartWaste(Request $request,OrderLogic $logic)
    {
        $input = $request->get();
        $input['auth_user_id'] = $this->auth_user_id;
        //数据校验
        $checked = UserValidate::checkSignVipAnalysis($input);
        if ($checked !== true) {
            return sys_response(4000002, $checked);
        }
        $result = $logic->getDepartWaste($input);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:城市发单浪费率，取排名靠前的前20个城市
     * @param Request $request
     * @param OrderLogic $logic
     * @return array
     */
    public function getFaDanWasteCity(Request $request, OrderLogic $logic)
    {
        $input = $request->get();
        $input['auth_user_id'] = $this->auth_user_id;
        //数据校验
        $checked = UserValidate::checkSignVipAnalysis($input);
        if ($checked !== true) {
            return sys_response(4000002, $checked);
        }
        $result = $logic->getFaDanWasteCity($input);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:发单时间段分布
     * @param Request $request
     * @param OrderLogic $logic
     * @return array
     */
    public function getFaDanTimeAnalysis(Request $request,OrderLogic $logic)
    {
        $input = $request->get();
        $input['auth_user_id'] = $this->auth_user_id;
        //数据校验
        $checked = UserValidate::checkSignVipAnalysis($input);
        if ($checked !== true) {
            return sys_response(4000002, $checked);
        }
        $result = $logic->getFaDanTimeAnalysis($input);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:异常单分析
     * @param Request $request
     * @param OrderLogic $logic
     * @return array
     */
    public function getAbnormalAnalysis(Request $request,OrderLogic $logic)
    {
        $input = $request->get();
        $input['auth_user_id'] = $this->auth_user_id;
        //数据校验
        $checked = UserValidate::checkSignVipAnalysis($input);
        if ($checked !== true) {
            return sys_response(4000002, $checked);
        }
        $result = $logic->getAbnormalAnalysis($input);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

}