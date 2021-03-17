<?php

namespace app\index\controller\v1;

use app\index\enum\AdminuserCodeEnum;
use think\Request;
use app\index\model\logic\UserLogic;
use app\index\model\logic\CompanyLogic;
use app\index\validate\UserValidate;
use app\index\validate\CityValidate;

use app\common\enum\BaseStatusCodeEnum;

class User
{
    private $auth_user_id = 0;

    public function initialize(){
        $user = request()->user;
        if ($user["dept_top_id"] == AdminuserCodeEnum::DEPT_MARKET_CENTER) {
            $this->auth_user_id = $user["user_id"];
        }
    }

    /**
     * @des:获取1.0会员数
     * @param Request $request
     * @return array
     */
    public function getUserAmountV1(Request $request)
    {
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $logic = new UserLogic();
        $result = $logic->getUserAmountV1($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:获取2.0会员数
     * @param Request $request
     * @return array
     */
    public function getUserAmountV2(Request $request)
    {
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $logic = new UserLogic();
        $result = $logic->getUserAmountV2($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }


    /**
     * @des:获取会员总数=1.0会员数+2.0会员数
     * @param Request $request
     * @return array
     */
    public function getUserAmount(Request $request)
    {
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $logic = new UserLogic();
        $result = $logic->getUserAmount($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:1.0会员消耗金额
     * @param Request $request
     * @return array
     */
    public function getUserConsumptionV1(Request $request)
    {
        $logic = new UserLogic();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $result = $logic->getUserConsumptionV1($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:2.0会员消费总额（2.0会员轮单扣费类型，支出）
     * @param Request $request
     * @return array
     * @throws \think\Exception
     */
    public function getUserConsumptionV2(Request $request)
    {
        $logic = new UserLogic();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $result = $logic->getUserConsumptionV2($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:会员总消耗金额
     * @param Request $request
     * @return array
     */
    public function getUserConsumptionTotal(Request $request)
    {
        $logic = new UserLogic();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $result = $logic->getUserConsumptionTotal($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     *  @des:会员数预期差距
     * @param Request $request
     * @return array
     * @throws \think\Exception
     */
    public function getUserExceptGap(Request $request)
    {
        $logic = new UserLogic();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $result = $logic->getUserExceptGap($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }


    /**
     * @des:1.0会员客单价
     * @param Request $request
     * @return array
     */
    public function getUserCustomerPriceV1(Request $request)
    {
        $logic = new UserLogic();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $result = $logic->getUserCustomerPriceV1($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:2.0会员客单价
     * @param Request $request
     * @return array
     */
    public function getUserCustomerPriceV2(Request $request)
    {
        $logic = new UserLogic();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $result = $logic->getUserCustomerPriceV2($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:会员客单价
     * @param Request $request
     * @return array
     */
    public function getUserCustomerPrice(Request $request)
    {
        $logic = new UserLogic();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $result = $logic->getUserCustomerPrice($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }


    /**
     * @des:1.0装企投入产出比
     * @param Request $request
     * @return array
     */
    public function getInputAndOutputV1(Request $request)
    {
        $logic = new UserLogic();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);

        $result = $logic->getInputAndOutputV1($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:2.0装企投入产出比
     * @param Request $request
     * @return array
     */
    public function getInputAndOutputV2(Request $request)
    {
        $logic = new UserLogic();
        $city_ids = $request->get("city_ids");
        $city_ids = CityValidate::setAuthCity($city_ids);
        
        $result = $logic->getInputAndOutputV2($city_ids);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    public function getMembershipOverview()
    {
        try {
            $logic = new UserLogic();
            $city = CityValidate::setAuthCity();

            $result = $logic->getMembershipOverview($city);
            $data["series"] = [
                "data" => []
            ];
            $data["legend"] = [];
            foreach ($result["list"] as $item) {
               $data["legend"][] = $item["name"];
                $data["series"]["data"][] = [
                    "value" => $item["count"],
                    "name" => $item["name"]
                ];
            }
            $data["all_count"] = $result["all"];
            return sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        }   catch (\Exception $e) {
            return sys_response(5000003,BaseStatusCodeEnum::CODE_5000003);
        }
    }

    public function getMembershipChange()
    {
        try {
            $end = mktime(23,59,59,date("m"),date("d"),date("Y"));
            $city = CityValidate::setAuthCity();

            $step = input("get.step",1);

            if (empty($step)) {
                $step = 1;
            }

            switch ($step){
                default:
                    $begin = strtotime("-1 month",$end);
                    $offset = ($end - $begin)/86400;
                    break;
                case 2:
                    $begin = strtotime("-3 month",$end);
                    $offset = 3;
                    break;
                case 3:
                    $begin = strtotime("-6 month",$end);
                    $offset = 6;
                    break;
                case 4:
                    $begin = strtotime("-1 year",$end);
                    $offset = 12;
                    break;
            }

            $logic = new UserLogic();
            $list = $logic->getMembershipChange($begin,$end,$step,$city);

            $data['legend'] = [
                "总会员数","1.0会员数","2.0会员数"
            ];

            $data['xAxis'] = [];

            $data["series"] = [
                [
                    "name" => "总会员数",
                    "type" => "line",
                    'data' => []
                ],
                [
                    "name" => "1.0会员数",
                    "type" => "line",
                    'data' => []
                ],
                [
                    "name" => "2.0会员数",
                    "type" => "line",
                    'data' => []
                ]
            ];

            for ($i = 1; $i <= $offset; $i++) {
                if ($step == 1) {
                    $day = date("Y-m-d",strtotime("+".$i." day",$begin));
                } else {

                    $day = date("Y-m",strtotime("last day of +".$i." months",$begin));
                }

                $data['xAxis'][] = $day;

                if (array_key_exists($day,$list)) {
                    $data["series"][0]["data"][] = +$list[$day]["all_count"];
                    $data["series"][1]["data"][] = +$list[$day]["normal_count"];
                    $data["series"][2]["data"][] = +$list[$day]["qian_count"];
                } else {
                    $data["series"][0]["data"][] = 0;
                    $data["series"][1]["data"][] = 0;
                    $data["series"][2]["data"][] = 0;
                }
            }

            return sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        }  catch (\Exception $e) {
            return sys_response(5000003,BaseStatusCodeEnum::CODE_5000003);
        }
    }

    public function getMembershipRenewal()
    {
        try {
            $begin = date("Y-m-d", mktime(0,0,0,1,1,date("Y")));
            $end = date("Y-m-d",mktime(23,59,59,date("m"),date("t"),date("Y")));
            $city = CityValidate::setAuthCity();

            $logic = new UserLogic();
            //1.0会员续费
            $list =  $logic->getMembershipNoramlRenewal($begin,$end,$city);

            //2.0会员续费
            $two_list = $logic->getMembershipQianRenewal($begin,$end,$city);

            $data['xAxis'] = [];

            $data["series"] = [
                [
                    "name" => "1.0会员",
                    "type" => "line",
                    'data' => []
                ],
                [
                    "name" => "2.0会员",
                    "type" => "line",
                    'data' => []
                ],
            ];

            for ($i = 1; $i <= date("m"); $i++) {
                $data['xAxis'][] = $i."月";
                if ($i < 10 ) {
                    $i = "0".$i;
                }
                $index = date("Y").$i;

                if (array_key_exists($index,$list)) {
                    $data["series"][0]["data"][] = $list[$index]["rate"];
                } else {
                    $data["series"][0]["data"][] =  0;
                }

                if (array_key_exists($index,$two_list)) {
                    $offset = 0;
                    if ( $two_list[$index]["un_count"] - $two_list[$index]["pay_count"] > 0) {
                        $offset = $two_list[$index]["un_count"] - $two_list[$index]["pay_count"];
                    }
                    $data["series"][1]["data"][] = round($two_list[$index]["pay_count"]/($offset + $two_list[$index]["pay_count"]),4) * 100;
                } else {
                    $data["series"][1]["data"][] =  0;
                }
            }
            return sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        } catch (\Exception $e) {
            echo $e->getMessage();
            return sys_response(5000003,BaseStatusCodeEnum::CODE_5000003);
        }
    }

    // 会员数据分析-会员详情
    public function getCompanyDetailed(Request $request, CompanyLogic $companyLogic){
        $input = $request->get();
        $page = sys_page_format($request->page, 1);
        $limit = sys_page_format($request->limit, 20);

        $checked = UserValidate::checkUserDetailedData($input);
        if ($checked !== true) {
            $response = sys_response(4000002, $checked);
            return json($response);
        }

        // 查询城市增加权限城市
        $input["city_ids"] = CityValidate::setAuthCity($input["city_ids"]);

        $data = $companyLogic->getCompanyDetailedList($input, $page, $limit);

        $data["options"] = [
            "date_begin" => $input["date_begin"],
            "date_end" => $input["date_end"],
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    // 获取搜索公司列表
    public function getSearchList(Request $request, CompanyLogic $companyLogic){
        $input = $request->get();
        if (empty($input["keyword"])) {
            $response = sys_response(4000002, BaseStatusCodeEnum::CODE_4000002);
            return json($response);
        }

        $input["city_ids"] = CityValidate::setAuthCity();
        $list = $companyLogic->getCompanySearchList($input);

        $data = [
            "list" => $list,
            "count" => count($list)
        ];

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
        return json($response);
    }

    /**
     * @des:签约企业分析
     * @param Request $request
     * @param UserLogic $logic
     * @return array
     */
    public function getSignVipAnalysis(Request $request,UserLogic $logic)
    {
        $input = $request->get();

        //数据校验
        $checked = UserValidate::checkSignVipAnalysis($input);
        if ($checked !== true) {
            return sys_response(4000002, $checked);
        }

        $result = $logic->getSignVipAnalysis($input);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:近一年企业续费率
     * @param UserLogic $logic
     * @return array
     */
    public function getRenewAnalysis(UserLogic $logic)
    {
        $result = $logic->getRenewAnalysis();
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

    /**
     * @des:注册用户发单周期
     * @param Request $request
     * @param UserLogic $logic
     * @return array
     */
    public function getFaDanCycleAnalysis(Request $request,UserLogic $logic)
    {
        $input = $request->get();
        $input['auth_user_id'] = $this->auth_user_id;
        //数据校验
        $checked = UserValidate::checkSignVipAnalysis($input);
        if ($checked !== true) {
            return sys_response(4000002, $checked);
        }
        $result = $logic->getFaDanCycleAnalysis($input);
        return sys_response($result['error_code'], $result['error_msg'], $result['data']);
    }

}