<?php
namespace app\index\model\logic;
use app\common\enum\BaseStatusCodeEnum;
use app\index\enum\AdminuserCodeEnum;
use app\index\enum\CompanyCodeEnum;
use app\index\enum\MianjiCodeEnmu;
use app\index\enum\OrderCodeEnum;
use app\index\model\adb\Adminuser;
use app\index\model\adb\CityOrderActual;
use app\index\model\adb\CityOrderLack;
use app\index\model\adb\Department;
use app\index\model\adb\DepartmentIdentify;
use app\index\model\adb\OrderBackRecord;
use app\index\model\adb\OrderCompanyReview;
use app\index\model\adb\Orders;
use app\index\model\adb\Quyu;
use app\index\model\adb\SaleIndicators;
use app\index\model\adb\SaleTeamAchievement;
use app\index\model\adb\UserCompanyAccountLog;
use app\index\model\adb\UserCompanyExpend;
use app\index\model\adb\User;
use app\index\model\adb\UserCompanyRoundOrderApply;
use app\index\model\adb\UserVip;
use think\Db;
use think\Exception;
use think\validate\ValidateRule;
use Util\Page;

/**
 *
 */
class OrderLogic
{
    public function getCityOrderCount($begin,$end,$type,$city)
    {
        $model = new Orders();
        return $model->getCityOrderCount($begin,$end,$type,$city);
    }

    public function getOrderLiangFangRate($begin,$end,$city)
    {
        $rate = 0;
        $model = new Orders();
        $count = $model->getCityOrderCount($begin,$end,null,$city);

        $model = new OrderCompanyReview();
        $result = $model->getLiangFangCount($begin,$end,$city);

        //量房率
        //量房量/总单量
        if ($count > 0) {
            $rate = round($result["count"]/$count,4) * 100;
        }
        return ["count" => +$result["count"],"un_count" => $count - $result["count"],"rate" => $rate];
    }

    public function getOrderData($begin,$end,$city)
    {
        $model = new Orders();
        $result = $model->getOrderData($begin,$end,$city);
        $data = [
            "all_count" => +$result["all_count"],
            "p_count" => +$result["p_count"],
            "yx_count" => +$result["yx_count"],
            "fen_count" => +$result["fen_count"],
            "qian_count" => +$result["qian_count"],
            "p_rate" => $result["all_count"] > 0?round($result["p_count"]/$result["all_count"],4) * 100 :0,
            "yx_rate" => $result["all_count"] > 0?round($result["yx_count"]/$result["p_count"],4) * 100 :0,
            "fen_rate" => $result["all_count"] > 0?round($result["fen_count"]/$result["yx_count"],4) * 100 :0,
            "qian_rate" => $result["all_count"] > 0?round($result["qian_count"]/$result["fen_count"],4) * 100 :0,
        ];

        return $data;
    }

    public function getOrderApplyCount($begin,$end,$city)
    {
        //申请补轮量
        $model =  new UserCompanyRoundOrderApply();
        $count = $model->getOrderApplyCount($begin,$end,$city);

        //分给2.0会员的分单数
        $model = new Orders();
        $fen_count = $model->getOrderFenCount(2,$begin,$end,$city);

        //补轮率
        $rate = 0;
        if ($fen_count > 0) {
            $rate = round($count/$fen_count,4) * 100;
        }
        return ["count" => $count,"rate" => $rate];
    }

    public function getApplyPassOrder($begin,$end,$city)
    {
        //申请补轮量
        $model =  new UserCompanyRoundOrderApply();
        $count = $model->getApplyPassOrderCount($begin,$end,$city);

        //分给2.0会员的分单数
        $model = new Orders();
        $fen_count = $model->getOrderFenCount(2,$begin,$end,$city);

        //通过率
        $rate = 0;
        if ($fen_count > 0) {
            $rate = round($count/$fen_count,4) * 100;
        }
        return ["count" => $count,"rate" => $rate];
    }

    public function getFullOrderFen($begin,$end,$city)
    {
        $model = new Orders();
        $result = $model->getFullOrderFen($begin,$end,$city);

        $list = [];
        foreach ($result as $item) {
            $list[$item["month"]] = +$item["count"];
        }

        return $list;
    }

    public function getApplyPassFull($begin,$end,$city)
    {
        $model = new UserCompanyRoundOrderApply();
        $result = $model->getApplyPassFull($begin,$end,$city);

        $list = [];
        foreach ($result as $item) {
            $list[$item["month"]] = +$item["count"];
        }

        return $list;
    }

    public function getOrderWaste($begin,$end,$city)
    {
        $model = new Orders();
        //订单分单数据
        $result = $model->getOrderAllocation($begin,$end,$city);
        $list = [
            1=> [],
            2=> [],
            3=> [],
            4=> [],
            5=> [],
        ];

        foreach ($result as $item) {
            $list[$item["order_type"]][$item["month"]]["allot_fen_num"] = $item['allot_fen_num'];
            $list[$item["order_type"]][$item["month"]]["city_vip_num"] = $item['city_vip_num'];
            if (in_array($item["order_type"],[1,2,3,4])) {
                if (!isset($list[5][$item["month"]])) {
                    $list[5][$item["month"]]["allot_fen_num"] = 0;
                    $list[5][$item["month"]]["city_vip_num"] = 0;
                }

                $list[5][$item["month"]]["allot_fen_num"] += $item['allot_fen_num'];
                $list[5][$item["month"]]["city_vip_num"] += $item['city_vip_num'];
            }
        }

        foreach ($list as $key => $item) {
            foreach ($item as $k => $v) {
                $rate = 0;
                if ($v['city_vip_num'] > 0) {
                    $rate = round(($v['city_vip_num'] -$v['allot_fen_num'])/$v['city_vip_num'],4) * 100;
                }
                $list[$key][$k]["rate"] = $rate;
            }
        }

        return $list;
    }

    public function getOrderAreaRate($begin,$end,$city)
    {
        $model = new Orders();
        $result = $model->getOrderAreaRate($begin,$end,$city);
        $list = [];

        foreach ($result as $item) {
            $list[] = [
                "name" => MianjiCodeEnmu::getMianji($item["type"]),
                "count" => +$item["count"]
            ];
        }

        if (count($list) == 0) {
            $list[] = [
                "name" => '无数据',
                "count" => 0
            ];
        }
        return $list;
    }

    public function getCityOrderActual($begin,$end,$city)
    {
        $model = new Orders();
        $result = $model->getCityOrderActual($begin,$end,$city);
        $list = [];

        if (count($result) > 0) {
            foreach ($result as $item) {
                if (!array_key_exists($item["city_id"],$list)) {
                    $list[$item["city_id"]] = [
                        "city_id" => $item["city_id"],
                        "month" => [
                        ]
                    ];
                }
                $list[$item["city_id"]]["month"][$item["month"]]["theoretical_orders"] = $item["actual_orders"];
                $list[$item["city_id"]]["month"][$item["month"]]["actual_orders"] = 0;
            }
        }

        return $list;
    }

    public function getMissCityOrderActual($begin,$end,$city,$list)
    {
        $model = new Orders();
        $result = $model->getMissCityOrderActual($begin,$end,$city);
        if (count($result) > 0) {
            foreach ($result as $item) {
                if (!array_key_exists($item["city_id"],$list)) {
                    $list[$item["city_id"]] = [
                        "city_id" => $item["city_id"],
                        "month" => [
                        ]
                    ];

                    $list[$item["city_id"]]["month"][$item["month"]]["theoretical_orders"] = $item["predict_whole_month_orders"];
                    $list[$item["city_id"]]["month"][$item["month"]]["actual_orders"] = 0;
                } else {
                    if (!array_key_exists($item["month"], $list[$item["city_id"]]["month"])) {
                        $list[$item["city_id"]]["month"][$item["month"]]["theoretical_orders"] = $item["predict_whole_month_orders"];
                        $list[$item["city_id"]]["month"][$item["month"]]["actual_orders"] = 0;
                    }
                }
            }
        }
        return $list;
    }

    public function getOrderMonthActual($begin,$end,$city,$list)
    {
        $model = new Orders();
        $result = $model->getOrderMonthActual($begin,$end,$city);

        foreach ($result as $item) {
            if (array_key_exists($item["city_id"],$list)) {
                $list[$item["city_id"]]["month"][$item["month"]]["actual_orders"] = $item["count"];
                if (!isset( $list[$item["city_id"]]["month"][$item["month"]]["theoretical_orders"])) {
                    $list[$item["city_id"]]["month"][$item["month"]]["theoretical_orders"] = 0;
                }
            }
        }

        $all = [];
        foreach ($list as $item) {
            foreach ($item["month"] as $key => $val) {
                if (!array_key_exists($key,$all)) {
                    $all[$key]["theoretical_orders"] = $val["theoretical_orders"];
                    $all[$key]["actual_orders"] = $val["actual_orders"];
                } else {
                    $all[$key]["theoretical_orders"] += $val["theoretical_orders"];
                    $all[$key]["actual_orders"] += $val["actual_orders"];
                }
            }
        }


        foreach ($all as $key => $item) {
            $all[$key]["rate"] = 100;
            if ($item["theoretical_orders"] > 0) {
                $all[$key]["rate"] = round($item["actual_orders"]/$item["theoretical_orders"],4) * 100;
            }
        }
        return $all;
    }


    /**
     * @des:总消耗金额
     * @param string $city_ids
     * @return array
     */
    public function getConsumptionTotal($city_ids = "")
    {
        $param = array();
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            //1.0会员本月总消耗
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();
            $model = new UserCompanyExpend();
            $consumptionV1 = $model->getUserConsumptionV1($param);
            $result['consumption_v1'] = $consumptionV1;
            //2.0会员本月总消耗
            $model = new Orders();
            $consumptionV2 = $model->getUserConsumptionV2($param);
            $result['consumption_v2'] = $consumptionV2;
            //本月总消耗金额
            $result['consumption_total'] = round(($consumptionV1 + $consumptionV2),2);

            $return = [
                'error_code' => 0,
                'error_msg' => BaseStatusCodeEnum::CODE_0,
                'data' => $result
            ];
        } catch (Exception $exception) {
            $return = [
                'error_code' => 5000003,
                'error_msg' => BaseStatusCodeEnum::CODE_5000003,
                'data' => ''
            ];
        } finally {
            return $return;
        }
    }

    /**
     * @des:分配分单量
     * @param string $city_ids
     * @return array
     */
    public function getDistributeOrder($city_ids = "")
    {
        $param = array();
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            //实时分配数
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();
            $model = new Orders();
            $distributionSum = $model->getOrderNumByAddTime($param);
            //分单量 实际分单量
            $distributionAmount = $model->getDistributeOrderNumber($param);
            $result['distribute_number'] = $averageDistribute = ($distributionAmount != 0) ? round($distributionSum / $distributionAmount, 2) : 0;

            //获取上月分配分单量
            $param['start_time'] = strtotime(date('Y-m-01', strtotime('-1 months')));
            $param['end_time'] = strtotime(date('Y-m-01')) - 1;
            $monthsBeforeDistributionSum = $model->getOrderNumByAddTime($param);
            $monthsBeforeDistributionAmount = $model->getDistributeOrderNumber($param);
            //上月平均分配次数
            $monthsAverageDistribute = ($monthsBeforeDistributionAmount != 0) ? round($monthsBeforeDistributionSum / $monthsBeforeDistributionAmount, 2) : 0;

            //月环比
            if ($monthsAverageDistribute == 0) {
                $result['month_percent']['percent'] = ($averageDistribute > 0) ? 100 : 0;
                $result['month_percent']['symbol'] = ($averageDistribute > 0) ? 'plus' : '';
            } else {
                $gap = $averageDistribute - $monthsAverageDistribute;
                $result['month_percent']['percent'] = round(abs($gap) / (float)$monthsAverageDistribute * 100, 2);
                $result['month_percent']['symbol'] = $gap > 0 ? 'plus' : ($gap < 0 ? 'reduce' : '');
            }
            $return = [
                'error_code' => 0,
                'error_msg' => BaseStatusCodeEnum::CODE_0,
                'data' => $result
            ];
        } catch (Exception $exception) {
            $return = [
                'error_code' => 5000003,
                'error_msg' => BaseStatusCodeEnum::CODE_5000003,
                'data' => ''
            ];
        } finally {
            return $return;
        }
    }

    /**
     * @des:订单撤回次数
     * @param string $city_ids
     * @return array
     */
    public function getOrderRebut($city_ids = "")
    {
        $param = array();
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            //当前订单撤销次数(订单号去重)
            $backModel = new OrderBackRecord();
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();
            $showBackCount = $backModel->getOrderBackCountByCreated($param);

            //当前月订单数
            $model = new Orders();
            $orderSum = $model->getAssignTotalNum($param);

            //订单状态撤回数
            $backStatusCount = $backModel->getBackStatusCount($param);
            
            $result['rebut_number'] = $showBackCount;
            $result['percent'] = $percent = (($orderSum + $backStatusCount) == 0) ? (($showBackCount > 0) ? 100 : 0) : round($showBackCount / ($orderSum + $backStatusCount) * 100, 2);
            $result['rebut_number_reduce'] = (($orderSum + $backStatusCount) > $showBackCount) ? ($orderSum + $backStatusCount - $showBackCount) : 0;
            $return = [
                'error_code' => 0,
                'error_msg' => BaseStatusCodeEnum::CODE_0,
                'data' => $result
            ];
        } catch (Exception $exception) {
            $return = [
                'error_code' => 5000003,
                'error_msg' => BaseStatusCodeEnum::CODE_5000003,
                'data' => ''
            ];
        } finally {
            return $return;
        }
    }

    /**
     * @des:订单撤回详情，撤回时间维度
     * @param array $param
     * @return array
     */
    public function getOrderRebutDetail($param = [])
    {
        if (isset($param['city_ids']) && !empty($param['city_ids']) && is_string($param['city_ids'])) {
            $param['cs'] = explode(",", $param['city_ids']);
        } else if (!empty($param['city_ids'])) {
            $param['cs'] = $param['city_ids'];
        }

        try {
            //当前订单撤销详情
            $backModel = new OrderBackRecord();
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();
            $param['page_size'] = (int)($param['page_size'] ?? 20);
            $param['page'] = (int)($param['page'] ?? 1);
            $backCountInfo = $backModel->getOrderRebutDetail($param)->toArray();
            if (empty($backCountInfo['data'])) {
                $return = [
                    'error_code' => 0,
                    'error_msg' => BaseStatusCodeEnum::CODE_0,
                    'data' => [
                        "list" => array(),
                        "page" => array(),
                        "options" => [
                            'date_begin' => date('Y.m.d', $param['start_time']),
                            'date_end' => date('Y.m.d', $param['end_time'])
                        ]
                    ]
                ];
            } else {
                $data = [];
                foreach ($backCountInfo['data'] as $k => $v) {
                    $data[$k]['trade_no'] = $v['order_id'];
                    $data[$k]['city_name'] = $v['cname'];
                    $data[$k]['operate_name'] = $v['user'];
                    $data[$k]['remark'] = OrderCodeEnum::$reason[$v['back_reason']] ?? '未知原因';
                    $data[$k]['time'] = date('Y-m-d', $v['created_at']);
                }
                $result['list'] = $data;
                $result['page']['page_total_number'] = $backCountInfo['last_page'];
                $result['page']['total_number'] = $backCountInfo['total'];
                $result['page']['page_size'] = $backCountInfo['per_page'];
                $result['page']['page_current'] = $backCountInfo['current_page'];
                $result['options'] = [
                    'date_begin' => date('Y.m.d', $param['start_time']),
                    'date_end' => date('Y.m.d', $param['end_time'])
                ];
                $return = [
                    'error_code' => 0,
                    'error_msg' => BaseStatusCodeEnum::CODE_0,
                    'data' => $result
                ];
            }
        } catch (Exception $exception) {
            $return = [
                'error_code' => 5000003,
                'error_msg' => BaseStatusCodeEnum::CODE_5000003,
                'data' => ''
            ];
        } finally {
            return $return;
        }
    }

    /**
     * @des:违规补轮次数
     * @param string $city_ids
     * @return array
     */
    public function getViolateApply($city_ids = "")
    {
        $param = array();
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            //当前订单撤销次数
            $model = new Orders();
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();
            $rebutSum = $model->getViolateApply($param);
            $result['amount'] = $rebutSum;
            $return = [
                'error_code' => 0,
                'error_msg' => BaseStatusCodeEnum::CODE_0,
                'data' => $result
            ];
        } catch (Exception $exception) {
            $return = [
                'error_code' => 5000003,
                'error_msg' => BaseStatusCodeEnum::CODE_5000003,
                'data' => ""
            ];
        } finally {
            return $return;
        }
    }

    public function getMembershipNoramlPrice($begin,$end,$city)
    {
        $model = new User();
        $result = $model->getMembershipNoramlPrice($begin,$end,$city);

        $list = [];
        foreach ($result as $item) {
            $list[$item["date"]] = $item["expend_amount"];
        }
        return $list;
    }

    public function getMembershipQianPrice($begin,$end,$city)
    {
        $model = new User();
        $result = $model->getMembershipQianPrice($begin,$end,$city);

        $list = [];
        foreach ($result as $item) {
            $list[$item["date"]] = $item["order_amount"];
        }
        return $list;
    }

    public function getOrderActualCountWithMonth($begin,$end,$city)
    {
        $model = new Orders();
        $result = $model->getOrderActualCountWithMonth($begin,$end,$city);

        $list = [];
        foreach ($result as $item) {
            $list[$item["date"]] =[
                'qian_count' => $item["qian_count"],
                'all_count' => $item["count"]
            ];
        }
        return $list;
    }

    /**
     * @des:违规补轮详情
     * @param array $param
     * @return array
     */
    public function getViolateApplyDetail($param = [])
    {
        if (isset($param['city_ids']) && !empty($param['city_ids']) && is_string($param['city_ids'])) {
            $param['cs'] = explode(",", $param['city_ids']);
        } else if (!empty($param['city_ids'])) {
            $param['cs'] = $param['city_ids'];
        }

        try {
            //当前订单撤销次数
            $model = new Orders();
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();
            $param['page_size'] = $param['page_size']??20;
            $param['page'] = $param['page'] ?? 1;
            $violateInfo = $model->getViolateApplyDetail($param)->toArray();
            if (empty($violateInfo['data'])) {
                $return = [
                    'error_code' => 0,
                    'error_msg' => BaseStatusCodeEnum::CODE_0,
                    'data' => [
                        "list" => array(),
                        "page" => array(),
                        "options" => [
                            'date_begin' => date('Y.m.d', $param['start_time']),
                            'date_end' => date('Y.m.d', $param['end_time'])
                        ]
                    ]
                ];
            } else {
                $data = [];
                foreach ($violateInfo['data'] as $k => $v) {
                    if (empty($v['cs'])) {
                        continue;
                    }
                    $data[] = [
                        'city_name' => $v['cname'],
                        'number' => $v['number'],
                        'detail' => $this->getCompanyInfo($v['cs'], $param['start_time'], $param['end_time'])
                    ];
                }
                $result['list'] = $data;
                $result['page']['page_total_number'] = $violateInfo['last_page'];
                $result['page']['total_number'] = $violateInfo['total'];
                $result['page']['page_size'] = $violateInfo['per_page'];
                $result['page']['page_current'] = $violateInfo['current_page'];
                $result['options'] = [
                    'date_begin' => date('Y.m.d', $param['start_time']),
                    'date_end' => date('Y.m.d', $param['end_time'])
                ];
                $return = [
                    'error_code' => 0,
                    'error_msg' => BaseStatusCodeEnum::CODE_0,
                    'data' => $result
                ];
            }
        } catch (Exception $exception) {
            $return = [
                'error_code' => 5000003,
                'error_msg' => BaseStatusCodeEnum::CODE_5000003,
                'data' => ''
            ];
        } finally {
            return $return;
        }
    }

    /**
     * @des:获取违规城市的公司信息
     * @param $cs
     * @param $start_time
     * @param $end_time
     * @return mixed
     */
    protected function getCompanyInfo($cs, $start_time, $end_time)
    {
        $model = new Orders();
        return $violateOrderCompany = $model->getViolateOrderCompanyByCs($cs,$start_time,$end_time);
    }

    /**
     * @des:企业量房/签单走势(近一年数据)
     */
    public function getLiangFangAndSignAnalysis()
    {
        $time = (new UserLogic())->getCurrentOneYearMonth();
        $model = new OrderCompanyReview();
        //获取企业量房量
        $liangfang = $model->getLiangfangDataByMonth(strtotime($time['start_time']), strtotime($time['end_time']) + 86399);
        $liangfang = array_column($liangfang,null,'time');
        //总单量
        $orderModel = new Orders();
        $orderCount = $orderModel->getOrderCountByMonth(strtotime($time['start_time']), strtotime($time['end_time']) + 86399);
        $orderCount = array_column($orderCount,null,'time');
        //签单量
        $orderSignCount = $orderModel->getOrderSignCountByMonth(strtotime($time['start_time']), strtotime($time['end_time']) + 86399);
        $orderSignCount = array_column($orderSignCount,null,'time');

        //x轴日期显示
        $result['xAxis'] = $time['show_date'];

        $seriesV1 = ['name' => '量房率', 'type' => 'line', 'data' => []];
        $seriesV2 = ['name' => '签单率', 'type' => 'line', 'data' => []];
        foreach ($time['year_month'] as $v) {
            if (isset($orderCount[$v]) && $orderCount[$v]['count'] > 0) {
                array_push($seriesV1['data'], round(($liangfang[$v]['count'] ?? 0) / $orderCount[$v]['count'], 4) * 100);
            } else {
                array_push($seriesV1['data'], (($liangfang[$v]['count'] ?? 0) > 0 ? 1 : 0) * 100);
            }
            if (isset($orderCount[$v]) && $orderCount[$v]['count'] > 0) {
                array_push($seriesV2['data'], round(($orderSignCount[$v]['count'] ?? 0) / $orderCount[$v]['count'], 4) * 100);
            } else {
                array_push($seriesV2['data'], (($orderSignCount[$v]['count'] ?? 0) > 0 ? 1 : 0) * 100);
            }
        }

        $result['series'] =[];
        array_push($result['series'], $seriesV1,$seriesV2);

        return [
            'error_code' => 0,
            'error_msg' => BaseStatusCodeEnum::CODE_0,
            'data' => $result
        ];
    }

    /**
     * @des:销售中心驾驶舱，中心板块数据(当月维度)
     */
    public function getDepartAchievement()
    {
        $start_time = date('Ym01');
        $end_time = date('Ymd');

        //开站城市
        $openCity = (new Quyu())->getAllOpenCityCount();
        //有会员城市数
        $userModel = new User();
        $userVipCity = $userModel->getVipCityCount();
        //总签约企业数
        $totalZxCompany = $userModel->getVipCount();
        //当月新签企业数
        $userVipModel = new UserVip();
        $currentSignZxCompany = $userVipModel->getNewSignVipAmount(strtotime($start_time), strtotime($end_time));
        //月所需分单量
        $cityOrderActualModel = new CityOrderActual();
        $actualList = $cityOrderActualModel->getCityMonthList($start_time);
        $actualListData = array_column($actualList,'actual_orders','city_id');
        $cityOrderLackModel = new CityOrderLack();
        $lack_date = $end_time;
        if (date('H') < 12) {
            $lack_date = date('Y-m-d', strtotime("-1 day"));
        }
        $cityLack = $cityOrderLackModel->getCityOrderLackDateInfo($lack_date);
        $cityLackData = array_column($cityLack,'predict_whole_month_orders','city_id');
        unset($actualList,$cityLack);
        $needFendan = array_sum($actualListData + $cityLackData);
        $needFendan = round($needFendan,2);
        //实际分单量
        $param['start_time'] = strtotime($start_time);
        $param['end_time'] = strtotime($end_time)+86399;
        $realFendan = (new Orders())->getDistributeOrderNumber($param);
        //缺单量
        $cityLogic = new CityLogic();
        $cityLogicData = $cityLogic->getCityOrderLackList($start_time, $end_time);
        $requireOrder = end($cityLogicData)['lack_orders'] ?? 0;

        //所有销售的顶级
        $departmentDetail = $this->department(17);
        $department_id = array_column($departmentDetail,'id');
        $adminUser = new Adminuser();
        $mvp = $adminUser->getSaleMvp($start_time, $end_time, array_merge($department_id, [17]));
        //销售中心数据汇总
        $teamLogic = new TeamLogic();
        $topTeamList = $teamLogic->getTeamTopList(true);

        $teamId = array_column($topTeamList,  "team_id");
        $teamAchievementModel = new SaleTeamAchievement();
        $achievementList = $teamAchievementModel->getTeamAchievementTotal($start_time, $end_time, $teamId);
        $amount = current($achievementList)['achievement'] ?? 0;
        //战区数据
        $data = $sale_depart = [];
        //业绩模型
        $indicatorModel = new SaleIndicators();
        foreach (@$topTeamList as $k => $v) {
            $value = current($teamAchievementModel->getTeamAchievementTotal($start_time, $end_time, $v['team_id']))['achievement'] ?? 0;
            $indicator = current($indicatorModel->getTeamIndicatorsTotal(substr($start_time,0,6), substr($end_time,0,6), $v['team_id']))['indicators'] ?? 0;
            $data[] = [
                'name' => $v['team_name'],
                'value' => $value,
                'percent' => ($indicator > 0) ? round($value / $indicator, 4) * 100 : 0
            ];
            $sale_depart[] = [
                'name' => $v['team_name'].'业绩',
                'value' => $value.'元',
            ];
        }

        //地球数据指标
        $result['list'] = [
            'amount' => $amount,
            'data' => $data
        ];

        //切换详情数据
        $result['detail'] = [
            'current_month_achievement' => $amount.'元',
            'current_mvp_sale' => $mvp['saler_name'] ?? '暂未出现',
            'current_mvp_money' => round($mvp['saler_achievement'] ?? 0,2) . '元',
            'sale_depart' => $sale_depart,
            'open_city' => $openCity . '个',
            'user_vip_city' => $userVipCity . '个',
            'total_zx_company' => $totalZxCompany.'家',
            'current_sign_zx_company' => $currentSignZxCompany.'家',
            'need_fendan' => $needFendan,
            'real_fendan' => $realFendan,
            'require_order' => $requireOrder
        ];


        return [
            'error_code' => 0,
            'error_msg' => BaseStatusCodeEnum::CODE_0,
            'data' => $result
        ];
    }

    /**
     * @des:根据顶级部门ID（销售）获取下级所有部门
     * @param $parent_id
     * @param array $return
     * @return array
     */
    protected function department($parent_id, $return = [])
    {
        $adminUser = new Adminuser();
        $list = $adminUser->getChildDepartment($parent_id);
        if ($list) {
            foreach ($list as $key => $val) {
                array_push($return, $val);
            }
            $this->department(array_column($list, 'id'));
        }
        return array_unique($return, SORT_REGULAR);
    }

    /**
     * @des:各部门订单转化(部门：市场中心下的所有渠道部门)
     * @param $input
     * @return array
     */
    public function getDepartTransFormOrder($input)
    {
        if (isset($input['tab_month']) && $input['tab_month'] == 1) {
            $input['start_time'] = strtotime(date('Y-m-01'));
        } else {
            $input['start_time'] = strtotime(date('Y-m-01', strtotime('-2 months')));
        }
        $input['end_time'] = time();

        $result = [];
        $group_id = [];//渠道部门id
        $model = new Orders();
        $departmentModel = new DepartmentIdentify();
        //获取x轴展示数据
        $dept = $departmentModel->getDepartmentList($input['auth_user_id']);
        $deptName = array_column($dept,'dept_belong','dept_belong');
        if ($input['auth_user_id']) {
            $group_id = array_column($dept, 'id');
        }
        $xData = array_values($deptName);
        $result['xAxis'] = $xData;
        $defaultData = array_pad([],count($xData),0);
        $result['title'] = ['发单量', '实际分单量', '量房量', '签单量'];
        $list = $model->getDepartTransFormOrder($input['start_time'], $input['end_time'], $group_id);
        $data = [
            ['name' => '发单量', 'type' => 'line', 'data' => $defaultData],
            ['name' => '实际分单量', 'type' => 'line', 'data' => $defaultData],
            ['name' => '量房量', 'type' => 'line', 'data' => $defaultData],
            ['name' => '签单量', 'type' => 'line', 'data' => $defaultData]
        ];
        $keyName = array_flip($xData);
        foreach (@$list as $k => $v) {
            $data[$v['value_type']]['data'][$keyName[$v['deptname']]] = $v['count'];
        }

        $result['series'] = $data;
        return [
            'error_code' => 0,
            'error_msg' => BaseStatusCodeEnum::CODE_0,
            'data' => $result
        ];
    }

    /**
     * @des:各部门订单浪费(部门：市场中心下的所有渠道部门)
     * @param $input
     * @return array
     */
    public function getDepartWaste($input)
    {
        if (isset($input['tab_month']) && $input['tab_month'] == 1) {
            $input['start_time'] = strtotime(date('Y-m-01'));
        } else {
            $input['start_time'] = strtotime(date('Y-m-01', strtotime('-2 months')));
        }
        $input['end_time'] = strtotime(date("Y-m-d", strtotime("-1 days")))+86399;

        $result = [];
        $group_id = [];//渠道部门id
        $model = new Orders();
        $departmentModel = new DepartmentIdentify();
        //获取x轴展示数据
        $dept = $departmentModel->getDepartmentList($input['auth_user_id']);
        if ($input['auth_user_id']) {
            $group_id = array_column($dept, 'id');
        }
        $deptName = array_column($dept,'dept_belong','dept_belong');
        $xData = array_values($deptName);
        $result['xAxis'] = $xData;
        $list = $model->getDepartWaste($input['start_time'], $input['end_time'], $group_id);
        $defaultData = array_pad([],count($xData),0);
        $data = [
            ['name' => '发单量', 'type' => 'bar', 'data' => $defaultData],
            ['name' => '未拨打量', 'type' => 'bar', 'data' => $defaultData],
            ['name' => '未开站发单', 'type' => 'bar', 'data' => $defaultData],
            ['name' => '无会员发单', 'type' => 'bar', 'data' => $defaultData],
            ['name' => '发单浪费率', 'type' => 'line', 'data' => $defaultData]
        ];
        $keyName = array_flip($xData);
        foreach (@$list as $k => $v) {
            //柱状数据
            $data[$v['value_type']]['data'][$keyName[$v['deptname']]] = $v['count'];
        }
       //计算浪费率
        foreach (@$xData as $k => $v) {
            $total = $data[0]['data'][$k] ?? 0;
            $langfei = intval($data[1]['data'][$k] ?? 0) + intval($data[2]['data'][$k] ?? 0) + intval($data[3]['data'][$k] ?? 0);
            $data[4]['data'][$k] = ($total > 0) ? round($langfei / $total, 4) * 100 : 0;
        }
        $result['title'] = ['发单量','未拨打量','未开站发单','无会员发单'];
        $result['series'] = $data;

        return [
            'error_code' => 0,
            'error_msg' => BaseStatusCodeEnum::CODE_0,
            'data' => $result
        ];
    }

    /**
     * @des:城市发单浪费率取排名前20
     */
    public function getFaDanWasteCity($input)
    {
        if ($input['tab_month'] == 1) {
            $input['start_time'] = strtotime(date('Y-m-01'));
        } else {
            $input['start_time'] = strtotime(date('Y-m-01', strtotime('-2 months')));
        }
        $input['end_time'] = time();

        $result = [];
        $group_id = [];//渠道部门id
        $departmentModel = new DepartmentIdentify();
        $dept = $departmentModel->getDepartmentList($input['auth_user_id']);
        if ($input['auth_user_id']) {
            $group_id = array_column($dept, 'id');
        }
        $model = new Orders();
        $list = $model->getFaDanWasteCity($input['start_time'], $input['end_time'], $group_id);

        //x轴数据返回设置(默认x轴长度)
        $xData = [];
        $xLength = (current($list)['waste_rate'] ?? 0);
        if ($xLength > 0) {
            $length = ceil($xLength / 5);
            for ($i = 0; $i <= 5; $i++) {
                array_push($xData,$i*$length);
            }
        }
        $result['xAxis'] = $xData;

        //Y轴数据返回设置
        $yData = [];
        foreach ($list as $v) {
            $yData[] = [
                'name' => $v['cname'],
                'value' => round($v['waste_rate'],2)
            ];
        }

        $result['yAxis'] = $yData;
        return [
            'error_code' => 0,
            'error_msg' => BaseStatusCodeEnum::CODE_0,
            'data' => $result
        ];
    }

    /**
     * @des:发单时间分布（发单时间维度，查看发单，分单）
     * @param $input
     * @return array
     */
    public function getFaDanTimeAnalysis($input)
    {
        if ($input['tab_month'] == 1) {
            $input['start_time'] = strtotime(date('Y-m-01'));
        } else {
            $input['start_time'] = strtotime(date('Y-m-01', strtotime('-2 months')));
        }
        $input['end_time'] = time();
        $result = [];
        $group_id = [];//渠道部门id
        $departmentModel = new DepartmentIdentify();
        $dept = $departmentModel->getDepartmentList($input['auth_user_id']);
        if ($input['auth_user_id']) {
            $group_id = array_column($dept, 'id');
        }
        $model = new Orders();
        $list = $model->getFaDanTimeAnalysis($input['start_time'], $input['end_time'], $group_id);
        //x轴数据返回设置
        $xData = ['00:00-2:00'];
        for ($i=2,$j=4;$i<=22;$i+=2,$j+=2){
            array_push($xData,$i.':00-'.$j.':00');
        }
        $result['xAxis'] = $xData;
        $defaultData = array_pad([],12,0);
        //返回数据整理
        $listData = array_column($list,null,'time');
        unset($list);
        $data = [
            ['name' => '发单量', 'type' => 'bar', 'data' => $defaultData],
            ['name' => '分单量', 'type' => 'bar', 'data' => $defaultData]
        ];
        if ($listData) {
            for ($m = 0, $j = 0; $m < 24; $m += 2, $j++) {
                $data[0]['data'][$j] = intval($listData[str_pad($m, 2, 0, STR_PAD_LEFT)]['fadan_count'] ?? 0) + intval($listData[str_pad
                    ($m + 1, 2, 0, STR_PAD_LEFT)]['fadan_count'] ?? 0);
                $data[1]['data'][$j] = intval($listData[str_pad($m, 2, 0, STR_PAD_LEFT)]['fendan_count'] ?? 0) + intval($listData[str_pad
                    ($m + 1, 2, 0, STR_PAD_LEFT)]['fendan_count'] ?? 0);
            }
        }
        $result['title'] = ['发单量', '分单量'];
        $result['series'] = $data;
        return [
            'error_code' => 0,
            'error_msg' => BaseStatusCodeEnum::CODE_0,
            'data' => $result
        ];
    }

    /**
     * @des:异常单分析
     * @param $input
     * @return array
     */
    public function getAbnormalAnalysis($input)
    {
        $input['end_time'] = strtotime(date('Y-m-d')) - 1;
        if ($input['tab_month'] == 1) {
            $input['start_time'] = strtotime(date('Y-m-01', $input['end_time']));
        } else {
            $input['start_time'] = strtotime(date('Y-m-01', strtotime('-2 months', $input['end_time'])));
        }
        $result = [];
        $group_id = [];//渠道部门id
        $departmentModel = new DepartmentIdentify();
        $dept = $departmentModel->getDepartmentList($input['auth_user_id']);
        if ($input['auth_user_id']) {
            $group_id = array_column($dept, 'id');
        }
        $model = new Orders();
        $list = $model->getAbnormalAnalysis($input['start_time'], $input['end_time'], $group_id);
        $list_key = array_column($list, 'count_id', 'remarks_status');
        $series = OrderCodeEnum::$abnormal_group;
        $data = [];
        foreach ($series as $k => $v) {
            $data[] = [
                'name' => $v,
                'value' => $list_key[$k] ?? 0
            ];
        }

        $result['series'] = $data;

        return [
            'error_code' => 0,
            'error_msg' => BaseStatusCodeEnum::CODE_0,
            'data' => $result
        ];
    }
}