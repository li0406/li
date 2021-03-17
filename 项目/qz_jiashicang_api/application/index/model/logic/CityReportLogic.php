<?php
/**
 * @des:城市报表业务处理
 * @author:tengyu
 * @date:2020-11-07
 */

namespace app\index\model\logic;


use app\common\enum\BaseStatusCodeEnum;
use app\http\middleware\AdminAuth;
use app\index\model\adb\Orders;
use think\Exception;

class CityReportLogic
{

    /**
     * @des:获取城市签单排行榜数据，时间维度：装企申请时间  #type_fw 单子类型: 0默认状态 1分 2问 3分没人跟 4问没人跟 问的不算单量 5未分配;qz_orders->qiandan_addtime签单申请时间
     * @param string $city_ids
     * @return mixed
     * @throws Exception
     */
    public function getSignRanking($city_ids = "")
    {
        //获取登录角色 当前可见的区域
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        $model = (new Orders());
        $param['start_time'] = strtotime(date('Y-m-01'));
        $param['end_time'] = time();
        return  $model->getSignRanking($param);
    }

    /**
     * @des:签单率获取
     * @param string $city_ids
     * @return array
     */
    public function getSignRate($city_ids = "")
    {
        //获取登录角色 当前可见的区域
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            $model = (new Orders());
            //获取当前月份签单量
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();
            $currentMonthNum = $model->getSignNum($param);//签单数量
            $currentMonthAssignNum = $model->getAssignTotalNum($param);//总分单数
            if ($currentMonthAssignNum == 0) {
                $signRate = ($currentMonthNum > 0) ? 100 : 0;
            } else {
                $signRate = $currentMonthNum / $currentMonthAssignNum * 100;
            }

            //获取上月数据
            $param['start_time'] = strtotime(date('Y-m-01', strtotime('-1 month')));
            $param['end_time'] =  strtotime(date('Y-m-01')) - 1;
            $beforeMonthNum = $model->getSignNum($param);

            //月环比
            if ($beforeMonthNum == 0) {
                $signMonthsCompare = ($currentMonthNum == 0) ? 0 : 100;
                $symbol = '';
            } else {
                $signMonthsCompare = (($currentMonthNum - $beforeMonthNum) / $beforeMonthNum) * 100;
                $symbol = $signMonthsCompare > 0 ? 'plus' : ($signMonthsCompare < 0 ? 'reduce' : '');
            }

            //总分单量 - 当月的签单量 = 当月未签单量
            //如果当月签单量 > 总分单量 则 当月未签单量为0
            $sign_amount_reduce = ($currentMonthNum > $currentMonthAssignNum)?0: $currentMonthAssignNum - $currentMonthNum;
            $result['now_rate'] = [
                'sign_rate' => round($signRate,2),
                'sign_amount' => (int)$currentMonthNum,
                'sign_amount_reduce' => $sign_amount_reduce,
            ];
            $result['month_percent'] = [
                'percent' => abs(round($signMonthsCompare, 2)),
                'symbol' => $symbol
            ];
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
        }
        return $return;
    }

    /**
     * @des:签单距离占比业务处理
     * @param string $city_ids
     * @return array
     */
    public function getSignDistance($city_ids = "")
    {
        //获取登录角色 当前可见的区域
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }


        try {
            $model = (new Orders());
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();
            $distanceInfo = $model->getSignNumByDistance($param);
            //总签单量
            $sumAmount = (float)($distanceInfo['amount'] ?? 0);

            if (!empty($distanceInfo)) {
                $range_data_3km_rate = $sumAmount > 0 ? round((float)$distanceInfo['qiandan_3km'] / $sumAmount * 100, 2) : 0;
                $range_data_5km_rate = $sumAmount > 0 ? round((float)$distanceInfo['qiandan_5km'] / $sumAmount * 100, 2) : 0;
                $range_data_10km_rate = $sumAmount > 0 ? round((float)$distanceInfo['qiandan_10km'] / $sumAmount * 100, 2) : 0;
                $range_data_15km_rate = $sumAmount > 0 ? round((float)$distanceInfo['qiandan_15km'] / $sumAmount * 100, 2) : 0;
                $range_data_20km_rate = $sumAmount > 0 ? round((float)$distanceInfo['qiandan_20km'] / $sumAmount * 100, 2) : 0;
                $range_data_extra_rate = $sumAmount > 0 ? round((float)$distanceInfo['qiandan_extra'] / $sumAmount * 100, 2) : 0;
                $result = [
                    ['range' => '3km以内', 'sign_amount' => (float)$distanceInfo['qiandan_3km'], 'sign_percentage' => $range_data_3km_rate],
                    ['range' => '3-5km', 'sign_amount' => (float)$distanceInfo['qiandan_5km'], 'sign_percentage' => $range_data_5km_rate],
                    ['range' => '5-10km', 'sign_amount' => (float)$distanceInfo['qiandan_10km'], 'sign_percentage' => $range_data_10km_rate],
                    ['range' => '10-15km', 'sign_amount' => (float)$distanceInfo['qiandan_15km'], 'sign_percentage' => $range_data_15km_rate],
                    ['range' => '15-20km', 'sign_amount' => (float)$distanceInfo['qiandan_20km'], 'sign_percentage' => $range_data_20km_rate],
                    ['range' => '其他', 'sign_amount' => (float)$distanceInfo['qiandan_extra'], 'sign_percentage' => $range_data_extra_rate],
                ];
            }else{
                $result = [];
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
        }
        return $return;
    }

    /**
     *  @des:总签单金额本月
     * @param string $city_ids
     * @return array
     */
    public function getSignMoneyTotal($city_ids = "")
    {
        //获取登录角色 当前可见的区域
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            $model = (new Orders());
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();

            $distanceInfo = $model->getSignMoneyTotal($param);
            $moneyTotal = $signAmount = $signAveragePrice = 0;
            //总签金额
            $moneyTotal = floatval($distanceInfo['amount'] ?? 0);
            $signAmount = floatval($distanceInfo['number'] ?? 0);
            $signAveragePrice = sys_devision($moneyTotal, $signAmount);

            $result = [
                'money_total' => round($moneyTotal, 2),
                'sign_amount' => round($signAmount, 2),
                'sign_average_price' => $signAveragePrice
            ];
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
        }
        return $return;

    }




}