<?php
/**
 * @des:获取数据分析业务处理
 * @author:tengyu
 * @date:2020-11-09
 */

namespace app\index\model\logic;


use app\common\enum\BaseStatusCodeEnum;
use app\index\model\adb\CityExceptUser;
use app\index\model\adb\CityOrderLack;
use app\index\model\adb\DepartmentIdentify;
use app\index\model\adb\Orders;
use app\index\model\adb\Quyu;
use app\index\model\adb\SaleReportCityQuote;
use app\index\model\adb\SaleReportPayment;
use app\index\model\adb\UcenterUser;
use app\index\model\adb\User;
use app\index\model\adb\UserCompany;
use app\index\model\adb\UserCompanyAccountLog;
use app\index\model\adb\UserCompanyExpend;
use app\index\model\adb\UserCompanyVipStatus;
use app\index\model\adb\UserVip;
use mysql_xdevapi\BaseResult;
use think\Exception;

class UserLogic
{

    /**
     * @des:获取1.0会员数 (1.0实时会员总数，当月1.0会员数 去年同月1.0会员数；上月1.0会员数)
     * @param string $city_ids
     * @return array
     */
    public function getUserAmountV1($city_ids = "")
    {
        //获取登录角色 当前可见的区域
        $param = array();
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            $model = new UserCompany();
            //获取1.0实时会员数
            $amount = $model->getUserAmountv1($param);
            $result['amount'] = (float)$amount;

            //获取上月新增1.0会员数, 从日消耗表获取
            $expend = new UserCompanyExpend();
            $param['datetime'] = strtotime(date('Y-m-01')) - 86400;
            $monthBeforeAmount = $expend->getUserAmountV1($param);

            //获取去年同月新增1.0会员数
            $param['datetime'] = strtotime(date('Y-m-01', strtotime('-1 years +1 months'))) - 86400;
            $monthBeforeYearAmount = $expend->getUserAmountV1($param);

            //月环比
            if ($monthBeforeAmount == 0) {
                $result['month_percent']['percent'] = ((float)$amount > 0) ? 100 : 0;
                $result['month_percent']['symbol'] = ((float)$amount > 0) ? 'plus' : '';
            } else {
                $gap = (float)$amount - (float)$monthBeforeAmount;
                $result['month_percent']['percent'] = round(abs($gap) / (float)$monthBeforeAmount * 100, 2);
                $result['month_percent']['symbol'] = $gap > 0 ? 'plus' : ($gap < 0 ? 'reduce' : '');
            }
            //月同比
            if ($monthBeforeYearAmount == 0) {
                $result['month_current_compare']['percent'] = ((float)$amount > 0) ? 100 : 0;
                $result['month_current_compare']['symbol'] = ((float)$amount > 0) ? 'plus' : '';
            } else {
                $current_gap = (float)$amount - (float)$monthBeforeYearAmount;
                $result['month_current_compare']['percent'] = round(abs($current_gap) / (float)$monthBeforeYearAmount * 1000, 2);
                $result['month_current_compare']['symbol'] = $current_gap > 0 ? 'plus' : ($current_gap < 0 ? 'reduce' : '');
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
     * @des:获取2.0会员总数（2.0实时会员总数，当月2.0会员数，去年同月2.0会员数；上月2.0会员数）
     * @param string $city_ids
     * @return array
     */
    public function getUserAmountV2($city_ids = "")
    {
        //获取登录角色 当前可见的区域
        $param = array();
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            //2.0实时会员
            $model = new UserCompany();
            $amount = $model->getUserAmountV2($param);
            $result['amount'] = (int)$amount;

            //获取上月新增2.0会员数
            $vipStatus = new UserCompanyVipStatus();
            $param['datetime'] = strtotime(date('Y-m-01')) - 86400;
            $monthBeforeAmount = $vipStatus->getUserAmountV2($param);

            //获取去年同月新增2.0会员数
            $param['datetime'] = strtotime(date('Y-m-01', strtotime('-1 years +1 months'))) - 86400;
            $monthBeforeYearAmount = $vipStatus->getUserAmountV2($param);

            //月环比
            if ($monthBeforeAmount == 0) {
                $result['month_percent']['percent'] = ((int)$amount > 0) ? 100 : 0;
                $result['month_percent']['symbol'] = ((int)$amount > 0) ? 'plus' : '';
            } else {
                $gap = (int)$amount - (int)$monthBeforeAmount;
                $result['month_percent']['percent'] = round(abs($gap) / (int)$monthBeforeAmount * 100, 0);
                $result['month_percent']['symbol'] = $gap > 0 ? 'plus' : ($gap < 0 ? 'reduce' : '');
            }
            //月同比
            if ($monthBeforeYearAmount == 0) {
                $result['month_current_compare']['percent'] = ((int)$amount > 0) ? 100 : 0;
                $result['month_current_compare']['symbol'] = ((int)$amount > 0) ? 'plus' : '';
            } else {
                $current_gap = (int)$amount - (int)$monthBeforeYearAmount;
                $result['month_current_compare']['percent'] = round(abs($current_gap) / (int)$monthBeforeYearAmount * 100, 0);
                $result['month_current_compare']['symbol'] = $current_gap > 0 ? 'plus' : ($current_gap < 0 ? 'reduce' : '');
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
     * @des:获取会员总数量
     * @param string $city_ids
     * @return array
     */
    public function getUserAmount($city_ids="")
    {
        //获取登录角色 当前可见的区域
        $param = array();
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            //实时总会员数
            $model = new UserCompany();
            $amountV1 = $model->getUserAmountV1($param);
            $amountV2 = $model->getUserAmountV2($param);
            $result['amount'] = $amount = (float)$amountV1 + (float)$amountV2;

            /**上个月总会员数*/
            //获取上月新增1.0会员数, 从日消耗表获取
            $expend = new UserCompanyExpend();
            $param['datetime'] = strtotime(date('Y-m-01')) - 86400;
            $monthBeforeV1 = $expend->getUserAmountV1($param);
            //获取上月新增2.0会员数
            $vipStatus = new UserCompanyVipStatus();
            $monthBeforeV2 = $vipStatus->getUserAmountV2($param);
            $monthBeforeAmount = $monthBeforeV1 + $monthBeforeV2;


            /**去年同月会员数*/
            $param['datetime'] = strtotime(date('Y-m-01', strtotime('-1 years +1 months'))) - 86400;
            $monthBeforeYearV1 = $expend->getUserAmountV1($param);
            $monthBeforeYearV2 = $vipStatus->getUserAmountV2($param);
            $monthBeforeYearAmount = $monthBeforeYearV1 + $monthBeforeYearV2;

            //月环比
            if ($monthBeforeAmount == 0) {
                $result['month_percent']['percent'] = ((float)$amount > 0) ? 100 : 0;
                $result['month_percent']['symbol'] = ((float)$amount > 0) ? 'plus' : '';
            } else {
                $gap = (float)$amount - (float)$monthBeforeAmount;
                $result['month_percent']['percent'] = round(abs($gap) / (float)$monthBeforeAmount * 100, 0);
                $result['month_percent']['symbol'] = $gap > 0 ? 'plus' : ($gap < 0 ? 'reduce' : '');
            }
            //月同比
            if ($monthBeforeYearAmount == 0) {
                $result['month_current_compare']['percent'] = ((float)$amount > 0) ? 100 : 0;
                $result['month_current_compare']['symbol'] = ((float)$amount > 0) ? 'plus' : '';
            } else {
                $current_gap = (float)$amount - (float)$monthBeforeYearAmount;
                $result['month_current_compare']['percent'] = round(abs($current_gap) / (float)$monthBeforeYearAmount * 1000, 0);
                $result['month_current_compare']['symbol'] = $current_gap > 0 ? 'plus' : ($current_gap < 0 ? 'reduce' : '');
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
     * @des:1.0会员消耗金额
     * @param string $city_ids
     * @return array
     */
    public function getUserConsumptionV1($city_ids = "")
    {
        //获取登录角色 当前可见的区域
        $param = array();
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            $model = new UserCompanyExpend();
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();
            $result['amount'] = $amount = (float)$model->getUserConsumptionV1($param);

            //上月1.0会员消耗金额
            $param['start_time'] = strtotime(date('Y-m-01', strtotime('-1 months')));
            $param['end_time'] = strtotime(date('Y-m-01')) - 1;
            $monthBeforeAmount = (float)$model->getUserConsumptionV1($param);

            //去年同月1.0会员消耗金额
            $param['start_time'] = strtotime(date('Y-m-01', strtotime('-1 year')));
            $param['end_time'] = strtotime(date('Y-m-01', strtotime('-1 year +1 months'))) - 1;
            $monthBeforeYearAmount = $model->getUserConsumptionV1($param);

            //月环比
            if ($monthBeforeAmount == 0) {
                $result['month_percent']['percent'] = ($amount > 0) ? 100 : 0;
                $result['month_percent']['symbol'] = ($amount > 0) ? 'plus' : '';
            } else {
                $gap = $amount - $monthBeforeAmount;
                $result['month_percent']['percent'] = round(abs($gap) / $monthBeforeAmount * 100, 2);
                $result['month_percent']['symbol'] = $gap > 0 ? 'plus' : ($gap < 0 ? 'reduce' : '');
            }
            //月同比
            if ($monthBeforeYearAmount == 0) {
                $result['month_current_compare']['percent'] = ($amount > 0) ? 100 : 0;
                $result['month_current_compare']['symbol'] = ($amount > 0) ? 'plus' : '';
            } else {
                $current_gap = $amount - $monthBeforeYearAmount;
                $result['month_current_compare']['percent'] = round(abs($current_gap) / $monthBeforeYearAmount * 100, 2);
                $result['month_current_compare']['symbol'] = $current_gap > 0 ? 'plus' : ($current_gap < 0 ? 'reduce' : '');
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
     * @des:2.0会员消耗金额(除销售外所有人看到的都是一样的，全部会员;销售只看到自己权限内的)
     * @param string $city_ids
     * @return array
     */
    public function getUserConsumptionV2($city_ids = "")
    {
        //获取登录角色 当前可见的区域
        $param = array();
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            $model = new Orders();

            //当前月会员2.0消费金额
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();
            $result['amount'] = $consumption = $model->getUserConsumptionV2($param);

            //上个月会员2.0消费金额
            $param['start_time'] = strtotime(date('Y-m-01', strtotime('-1 month')));
            $param['end_time'] = strtotime(date('Y-m-01')) - 1;
            $beforeMonthsConsumption = $model->getUserConsumptionV2($param);
            //月环比
            if ($beforeMonthsConsumption == 0) {
                $result['month_percent']['percent'] = ($consumption > 0) ? 100 : 0;
                $result['month_percent']['symbol'] = ($consumption > 0) ? 'plus' : '';
            } else {
                $gap = $consumption - $beforeMonthsConsumption;
                $result['month_percent']['percent'] = round(abs($gap) / $beforeMonthsConsumption * 100, 2);
                $result['month_percent']['symbol'] = $gap > 0 ? 'plus' : ($gap < 0 ? 'reduce' : '');
            }

            //去年同月会员2.0消费金额
            $param['start_time'] = strtotime(date('Y-m-01', strtotime('-1 years')));
            $param['end_time'] = strtotime(date('Y-m-01', strtotime('-1 years +1 month'))) - 1;
            $beforeMonthsYearConsumption = $model->getUserConsumptionV2($param);
            //月同比
            if ($beforeMonthsYearConsumption == 0) {
                $result['month_current_compare']['percent'] = ($consumption > 0) ? 100 : 0;
                $result['month_current_compare']['symbol'] = ($consumption > 0) ? 'plus' : '';
            } else {
                $current_gap = $consumption - $beforeMonthsYearConsumption;
                $result['month_current_compare']['percent'] = round(abs($current_gap) / $beforeMonthsYearConsumption * 1000, 2);
                $result['month_current_compare']['symbol'] = $current_gap > 0 ? 'plus' : ($current_gap < 0 ? 'reduce' : '');
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
     * @des:会员总消耗金额
     * @param string $city_ids
     * @return array
     */
    public function getUserConsumptionTotal($city_ids = "")
    {
        //获取登录角色 当前可见的区域
        $param = array();
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            //1.0会员消耗落档表
            $modelV1 = new UserCompanyExpend();
            //2.0会员消耗计算主表
            $modelV2 = new Orders();

            //当前月会员消费金额
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();
            $consumptionV1 = $modelV1->getUserConsumptionV1($param);
            $consumptionV2= $modelV2->getUserConsumptionV2($param);
            $result['amount'] = $amount = $consumptionV1 + $consumptionV2;

            //上个月会员消费金额
            $param['start_time'] = strtotime(date('Y-m-01', strtotime('-1 month')));
            $param['end_time'] = strtotime(date('Y-m-01')) - 1;
            $beforeMonthsV1 = $modelV1->getUserConsumptionV1($param);
            $beforeMonthsV2 = $modelV2->getUserConsumptionV2($param);
            $beforeMonthsConsumption = $beforeMonthsV1 + $beforeMonthsV2;
            //月环比
            if ($beforeMonthsConsumption == 0) {
                $result['month_percent']['percent'] = ($amount > 0) ? 100 : 0;
                $result['month_percent']['symbol'] = ($amount > 0) ? 'plus' : '';
            } else {
                $gap = $amount - $beforeMonthsConsumption;
                $result['month_percent']['percent'] = round(abs($gap) / $beforeMonthsConsumption * 100, 2);
                $result['month_percent']['symbol'] = $gap > 0 ? 'plus' : ($gap < 0 ? 'reduce' : '');
            }

            //去年同月会员消费金额
            $param['start_time'] = strtotime(date('Y-m-01', strtotime('-1 years')));
            $param['end_time'] = strtotime(date('Y-m-01', strtotime('-1 years +1 month'))) - 1;
            $beforeMonthsYearV1 = $modelV1->getUserConsumptionV1($param);
            $beforeMonthsYearV2 = $modelV2->getUserConsumptionV2($param);
            $beforeMonthsYearConsumption = $beforeMonthsYearV1 + $beforeMonthsYearV2;
            //月同比
            if ($beforeMonthsYearConsumption == 0) {
                $result['month_current_compare']['percent'] = ((float)$amount > 0) ? 100 : 0;
                $result['month_current_compare']['symbol'] = ((float)$amount > 0) ? 'plus' : '';
            } else {
                $current_gap = $amount - $beforeMonthsYearConsumption;
                $result['month_current_compare']['percent'] = round(abs($current_gap) / $beforeMonthsYearConsumption * 100, 2);
                $result['month_current_compare']['symbol'] = $current_gap > 0 ? 'plus' : ($current_gap < 0 ? 'reduce' : '');
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
     * @des:会员数预期差距
     * @param string $city_ids
     * @return array
     */
    public function getUserExceptGap($city_ids = "")
    {
        $param = array();
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            //实时总会员数
            $model = new UserCompany();
            $amountV1 = $model->getUserAmountV1($param);
            $amountV2 = $model->getUserAmountV2($param);
            $amount = $amount = (float)$amountV1 + (float)$amountV2;

            //获取期望会员数
            $exceptModel = new CityExceptUser();
            $exceptAmount = $exceptModel->getCityExceptUser($param);

            $result = [
                'user_gap' => ($exceptAmount - (float)$amount),
                'except_user' => $exceptAmount
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
        } finally {
            return $return;
        }
    }

    /**
     * @des:1.0会员客单价
     * @param string $city_ids
     * @return mixed
     */
    public function getUserCustomerPriceV1($city_ids = "")
    {
        $param = array();
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            //获取1.0实时会员数
            $user = new UserCompany();
            $userAmount = $user->getUserAmountV1($param);

            //获取当月所有小报备审核通过的总收款
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();
            $saleReportZxCompany = new SaleReportPayment();
            $money = $saleReportZxCompany->getSaleReportPaymentV1($param);
            $money = $money['money'] ?? 0;
            //当月1.0会员客单价
            $result['amount'] = $price = ($userAmount == 0) ? 0 : round($money / $userAmount, 2);

            //获取上月小报备审核通过的总收款
            $param['start_time'] = strtotime(date('Y-m-01',strtotime('-1 months')));
            $param['end_time'] = strtotime(date('Y-m-01'))-1;
            $monthBeforeMoney= $saleReportZxCompany->getSaleReportPaymentV1($param);
            $monthBeforeMoney = $monthBeforeMoney['money'] ?? 0;
            //获取上月1.0会员数
            $expend = new UserCompanyExpend();
            $param['datetime'] = strtotime(date('Y-m-01')) - 86400;
            $monthBeforeUser = $expend->getUserAmountV1($param);

            //上月1.0客单价
            $monthBeforePrice =  ($monthBeforeUser == 0) ? 0 : round($monthBeforeMoney / $monthBeforeUser, 2);

            //获取去年同月小报备审核通过的总收款
            $param['start_time'] = strtotime(date('Y-m-01', strtotime('-1 years')));
            $param['end_time'] = strtotime(date('Y-m-01', strtotime('-1 years +1 months'))) - 1;
            $monthBeforeYearMoney = $saleReportZxCompany->getSaleReportPaymentV1($param);
            $monthBeforeYearMoney = $monthBeforeYearMoney['money'] ?? 0;
            //获取去年同月1.0会员数
            $param['datetime'] = strtotime(date('Y-m-01', strtotime('-1 year +1 months'))) - 86400;
            $monthBeforeYearUser = $expend->getUserAmountV1($param);
            //去年同月1.0客单价
            $monthBeforeYearPrice = ($monthBeforeYearUser == 0) ? 0 : round($monthBeforeYearMoney / $monthBeforeYearUser, 2);

            //月环比
            if ($monthBeforePrice == 0) {
                $result['month_percent']['percent'] = ((float)$price > 0) ? 100 : 0;
                $result['month_percent']['symbol'] = ((float)$price > 0) ? 'plus' : '';
            } else {
                $gap = (float)$price - (float)$monthBeforePrice;
                $result['month_percent']['percent'] = round(abs($gap) / (float)$monthBeforePrice * 100, 0);
                $result['month_percent']['symbol'] = $gap > 0 ? 'plus' : ($gap < 0 ? 'reduce' : '');
            }
            //月同比
            if ($monthBeforeYearPrice == 0) {
                $result['month_current_compare']['percent'] = ((float)$price > 0) ? 100 : 0;
                $result['month_current_compare']['symbol'] = ((float)$price > 0) ? 'plus' : '';
            } else {
                $current_gap = (float)$price - (float)$monthBeforeYearPrice;
                $result['month_current_compare']['percent'] = round(abs($price) / (float)$monthBeforeYearPrice * 100, 0);
                $result['month_current_compare']['symbol'] = $current_gap > 0 ? 'plus' : ($current_gap < 0 ? 'reduce' : '');
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
     * @des:2.0会员客单价
     * @param string $city_ids
     * @return array
     */
    public function getUserCustomerPriceV2($city_ids = "")
    {
        $param = array();
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            //获取2.0实时会员数
            $user = new UserCompany();
            $userAmount = $user->getUserAmountV2($param);

            //获取当月所有小报备审核通过的总收款
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();
            $saleReportZxCompany = new SaleReportPayment();
            $money = $saleReportZxCompany->getSaleReportPaymentV2($param);
            $money = ($money['money'] ?? 0);
            //当月2.0会员客单价
            $result['amount'] = $price = ($userAmount == 0) ? 0 : round($money / $userAmount, 2);

            //获取上月小报备审核通过的总收款
            $param['start_time'] = strtotime(date('Y-m-01',strtotime('-1 months')));
            $param['end_time'] = strtotime(date('Y-m-01'))-1;
            $monthBeforeMoney = $saleReportZxCompany->getSaleReportPaymentV2($param);
            $monthBeforeMoney = ($monthBeforeMoney['money'] ?? 0);
            //获取上月2.0会员数
            $vipStatus = new UserCompanyVipStatus();
            $param['datetime'] = strtotime(date('Y-m-01')) - 86400;
            $monthBeforeUser = $vipStatus->getUserAmountV2($param);
            //上月2.0客单价
            $monthBeforePrice =  ($monthBeforeUser == 0) ? 0 : round($monthBeforeMoney / $monthBeforeUser, 2);

            //获取去年同月小报备审核通过的总收款
            $param['start_time'] = strtotime(date('Y-m-01', strtotime('-1 years')));
            $param['end_time'] = strtotime(date('Y-m-01', strtotime('-1 years +1 months'))) - 1;
            $monthBeforeYearMoney = $saleReportZxCompany->getSaleReportPaymentV2($param);
            $monthBeforeYearMoney = ($monthBeforeYearMoney['money'] ?? 0);
            //获取去年同月2.0会员数
            $param['datetime'] = strtotime(date('Y-m-01', strtotime('-1 year +1 months'))) - 86400;
            $monthBeforeYearUser = $vipStatus->getUserAmountV2($param);
            //去年同月1.0客单价
            $monthBeforeYearPrice = ($monthBeforeYearUser == 0) ? 0 : round($monthBeforeYearMoney / $monthBeforeYearUser, 2);

            //月环比
            if ($monthBeforePrice == 0) {
                $result['month_percent']['percent'] = ((float)$price > 0) ? 100 : 0;
                $result['month_percent']['symbol'] = ((float)$price > 0) ? 'plus' : '';
            } else {
                $gap = (float)$price - (float)$monthBeforePrice;
                $result['month_percent']['percent'] = round(abs($gap) / (float)$monthBeforePrice * 100, 2);
                $result['month_percent']['symbol'] = $gap > 0 ? 'plus' : ($gap < 0 ? 'reduce' : '');
            }
            //月同比
            if ($monthBeforeYearPrice == 0) {
                $result['month_current_compare']['percent'] = ((float)$price > 0) ? 100 : 0;
                $result['month_current_compare']['symbol'] = ((float)$price > 0) ? 'plus' : '';
            } else {
                $current_gap = (float)$price - (float)$monthBeforeYearPrice;
                $result['month_current_compare']['percent'] = round(abs($price) / (float)$monthBeforeYearPrice * 100, 2);
                $result['month_current_compare']['symbol'] = $current_gap > 0 ? 'plus' : ($current_gap < 0 ? 'reduce' : '');
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

    public function getMembershipOverview($city)
    {
        $model = new User();
        $result = $model->getMembershipCount($city);
        $list = [
            "list" => [],
            "all" => 0
        ];
        foreach ($result as $item) {
            if ($item["classid"] == 3) {
                if ($item["cooperate_mode"] == 1) {
                    //1.0 有效会员
                    if ($item["on"] == 2) {
                        $list["list"][] = [
                            "name" => "1.0有效会员",
                            "count" => $item["count"],
                            "mode" => 1, //会员类型 1 1.0会员 2.2.0会员 3.全屋定制会员
                            "state" => 1 //有效状态 1有效 2无效
                        ];
                        $list["all"] += $item["count"];
                    }
                } else if ($item["cooperate_mode"] == 2) {
                    //2.0 有效会员
                    if ($item["on"] == 2) {
                        $list["list"][] = [
                            "name" => "2.0有效会员",
                            "count" => $item["count"],
                            "mode" => 2, //会员类型 1 1.0会员 2.2.0会员 3.全屋定制会员
                            "state" => 1 //有效状态 1有效 2无效
                        ];
                        $list["all"] += $item["count"];
                    } elseif ($item["on"] == -1) {
                        //2.0 无效会员
                        $list["list"][] = [
                            "name" => "2.0无效会员",
                            "count" => $item["count"],
                            "mode" => 2, //会员类型 1 1.0会员 2.2.0会员 3.全屋定制会员
                            "state" => 2 //有效状态 1有效 2无效
                        ];
                    }
                }

            } elseif ($item["classid"] == 4) {
                //全屋定制
                $list["list"][] = [
                    "name" => "全屋定制会员",
                    "count" => $item["count"],
                    "mode" => 3, //会员类型 1 1.0会员 2.2.0会员 3.全屋定制会员
                    "state" => 1 //有效状态 1有效 2无效
                ];
                $list["all"] += $item["count"];
            }
        }
        return $list;
    }

    public function getMembershipChange($begin,$end,$step,$city)
    {
        $model = new User();
        $result = $model->getMembershipChange($begin,$end,$step,$city);

        $list = [];
        foreach ($result as $item) {
            $list[$item["day"]] = $item;
        }

        return $list;
    }

    public function getMembershipNoramlRenewal($begin,$end,$city)
    {
        $model = new User();
        //续费的数量
        $reuslt = $model->getMembershipNoramlRenewal($begin,$end,$city);

        $list = [];
        foreach ($reuslt as $item) {
            $list[$item["date"]]["in_count"] = $item["count"];
            $list[$item["date"]]["out_count"] = 0;
        }

        //到期的数量
        $reuslt = $model->getMembershipNoramlEndRenewal($begin,$end,$city);

        foreach ($reuslt as $item) {
            if (array_key_exists($item["in_start_time"],$list)) {
                $list[$item["in_start_time"]]["out_count"] = $item["count"];
            } else {
                $list[$item["in_start_time"]]["in_count"] = 0;
                $list[$item["in_start_time"]]["out_count"] = $item["count"];
            }
        }

        //计算续费率
        foreach ($list as &$item) {
            $item["rate"] = 100;
            if ($item["out_count"] > 0) {
                $item["rate"] = round($item["in_count"]/$item["out_count"],4) * 100;
            }
        }

        return $list;
    }

    public function getMembershipQianRenewal($begin,$end,$city)
    {
        $begin = strtotime($begin);
        $end = strtotime($end);
        $model = new User();
        //当月续费的2.0会员数
        $result = $model->getMembershipPayment($begin,$end,$city);
        $list = [];
        foreach ($result as $item) {
            $list[$item["now_at"]]["pay_count"] = $item["count"];
            $list[$item["now_at"]]["un_count"] = 0;
        }

        //月余额不足3000的次数
        $result = $model->getMembershipAccountInsufficientBalance($begin,$end,$city);
        
        foreach ($result as $item) {
            if (!array_key_exists($item["date"],$list)) {
                $list[$item["date"]]["pay_count"] = 0;
            }
            //不足3000的次数
            $list[$item["date"]]["un_count"] = $item["count"];
        }

        return $list;
    }

    /**
     * @des:会员客单价
     * @param string $city_ids
     * @return array
     */
    public function getUserCustomerPrice($city_ids = "")
    {
        $param = array();
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try{
            //获取实时会员数
            $user = new UserCompany();
            $userAmountV1 = $user->getUserAmountV1($param);
            $userAmountV2 = $user->getUserAmountV2($param);
            $userAmount = (float)$userAmountV1 + (float)$userAmountV2;

            //获取本月当前账户负责城市的小报备所有类型的总收款
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();
            $saleReportZxCompany = new SaleReportPayment();
            $money = $saleReportZxCompany->getSaleReportPayment($param);
            $money = $money['money'] ?? 0;
            //当月会员客单价
            $result['amount'] = $price = ($userAmount == 0) ? 0 : round($money / $userAmount, 2);

            //获取上月小报备审核通过的总收款
            $param['start_time'] = strtotime(date('Y-m-01',strtotime('-1 months')));
            $param['end_time'] = strtotime(date('Y-m-01'))-1;
            $monthBeforeMoney= $saleReportZxCompany->getSaleReportPayment($param);
            $monthBeforeMoney = $monthBeforeMoney['money'] ?? 0;
            //获取上月总会员数
            $cityOrderLackModel = new CityOrderLack();
            $param['datetime'] = strtotime(date('Y-m-01'))-86400;
            $monthBeforeUser = $cityOrderLackModel->getUserAmount($param)['amount'] ?? 0;
            //上月客单价
            $monthBeforePrice =  ($monthBeforeUser == 0) ? 0 : round($monthBeforeMoney / $monthBeforeUser, 2);

            //获取去年同月小报备审核通过的总收款
            $param['start_time'] = strtotime(date('Y-m-01', strtotime('-1 years')));
            $param['end_time'] = strtotime(date('Y-m-01', strtotime('-1 years +1 months'))) - 1;
            $monthBeforeYearMoney = $saleReportZxCompany->getSaleReportPayment($param);
            $monthBeforeYearMoney = $monthBeforeYearMoney['money'] ?? 0;
            //获取去年同月总会员数
            $param['datetime'] = strtotime(date('Y-m-01', strtotime('-1 year +1 months'))) - 86400;
            $monthBeforeYearUser = $cityOrderLackModel->getUserAmount($param)['amount'] ?? 0;
            //去年同月1.0客单价
            $monthBeforeYearPrice = ($monthBeforeYearUser == 0) ? 0 : round($monthBeforeYearMoney / $monthBeforeYearUser, 2);

            //月环比
            if ($monthBeforePrice == 0) {
                $result['month_percent']['percent'] = ((float)$price > 0) ? 100 : 0;
                $result['month_percent']['symbol'] = ((float)$price > 0) ? 'plus' : '';
            } else {
                $gap = (float)$price - (float)$monthBeforePrice;
                $result['month_percent']['percent'] = round(abs($gap) / (float)$monthBeforePrice * 100, 2);
                $result['month_percent']['symbol'] = $gap > 0 ? 'plus' : ($gap < 0 ? 'reduce' : '');
            }
            //月同比
            if ($monthBeforeYearPrice == 0) {
                $result['month_current_compare']['percent'] = ((float)$price > 0) ? 100 : 0;
                $result['month_current_compare']['symbol'] = ((float)$price > 0) ? 'plus' : '';
            } else {
                $current_gap = (float)$price - (float)$monthBeforeYearPrice;
                $result['month_current_compare']['percent'] = round(abs($price) / (float)$monthBeforeYearPrice * 100, 2);
                $result['month_current_compare']['symbol'] = $current_gap > 0 ? 'plus' : ($current_gap < 0 ? 'reduce' : '');
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
     * @des:1.0装企投入产出比  1.0装企投入产出比=1.0总签单金额/1.0总消耗金额
     * @param string $city_ids
     * @return array
     */
    public function getInputAndOutputV1($city_ids = "")
    {
        $param = array();
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            //获取当前1.0会员本月的签单总金额
            $model = new Orders();
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();
            //当前月总签单额
            $amount = $model->getSignMoneyV1($param)['amount'] ?? 0;

            //获取当月1.0会员总消耗金额
            $expend = new UserCompanyExpend();
            $currentExpend = $expend->getUserConsumptionV1($param);

            //获取上月1.0会员签单总金额
            $param['start_time'] = strtotime(date('Y-m-01', strtotime('-1 months')));
            $param['end_time'] = strtotime(date('Y-m-01')) - 1;
            $monthBeforeAmount = $model->getSignMoneyV1($param)['amount'] ?? 0;
            //获取上月1.0会员总消耗金额
            $monthBeforeExpend = $expend->getUserConsumptionV1($param);

            //去年同月1.0会员总签单金额
            $param['start_time'] = strtotime(date('Y-m-01', strtotime('-1 years')));
            $param['end_time'] = strtotime(date('Y-m-01', strtotime('-1 year +1 months'))) - 1;
            $yearBeforeYearAmount = $model->getSignMoneyV1($param)['amount'] ?? 0;
            //去年同月1.0会员总消耗金额
            $yearBeforeExpend = $expend->getUserConsumptionV1($param);

            //本月投入产出比
            $result['amount'] = $currentPercent = ($currentExpend != 0) ? round($amount * 10000 / $currentExpend * 100, 2) : (($amount > 0) ? 100 : 0);
            //上月投入产出比
            $monthBeforePercent = ($monthBeforeExpend != 0) ? round($monthBeforeAmount * 10000 / $monthBeforeExpend * 100, 2) : (($monthBeforeAmount > 0) ? 100 : 0);
            //去年同月产出比
            $yearBeforePercent = ($yearBeforeExpend != 0) ? round($yearBeforeYearAmount * 10000 / $yearBeforeExpend * 100, 2) : (($yearBeforeYearAmount > 0) ? 100 : 0);
            
            //计算月环比
            if ($monthBeforePercent == 0) {
                $result['month_percent']['percent'] = ($currentPercent > 0) ? 100 : 0;
                $result['month_percent']['symbol'] = ($currentPercent > 0) ? 'plus' : '';
            } else {
                $gap = $currentPercent - $monthBeforePercent;
                $result['month_percent']['percent'] = round(abs($gap) / (float)$monthBeforePercent * 100, 2);
                $result['month_percent']['symbol'] = $gap > 0 ? 'plus' : ($gap < 0 ? 'reduce' : '');
            }

            //计算月同比
            if ($yearBeforePercent == 0) {
                $result['month_current_compare']['percent'] = ($currentPercent > 0) ? 100 : 0;
                $result['month_current_compare']['symbol'] = ($currentPercent > 0) ? 'plus' : '';
            } else {
                $gap = $currentPercent - $yearBeforePercent;
                $result['month_current_compare']['percent'] = round(abs($gap) / $yearBeforePercent * 100, 2);
                $result['month_current_compare']['symbol'] = $gap > 0 ? 'plus' : ($gap < 0 ? 'reduce' : '');
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
     * @des:2.0装企投入产出比
     * @param string $city_ids
     * @return array
     */
    public function getInputAndOutputV2($city_ids = "")
    {
        $param = array();
        if (!empty($city_ids) && is_string($city_ids)) {
            $param['cs'] = explode(",", $city_ids);
        } else if (!empty($city_ids)) {
            $param['cs'] = $city_ids;
        }

        try {
            //当月2.0总签单金额
            $model = new Orders();
            $param['start_time'] = strtotime(date('Y-m-01'));
            $param['end_time'] = time();
            //当前月总签单额
            $amount = $model->getSignMoneyV2($param)['amount'] ?? 0;
            //当月总消耗金额
            $accountModel = new Orders();
            $consumption = $accountModel->getUserConsumptionV2($param);
            //当月投入产出占比
            $result['amount'] = $percent = ($consumption != 0) ? round($amount * 10000 / $consumption * 100, 2) : 0;

            //获取上月2.0总签单金额
            $param['start_time'] = strtotime(date('Y-m-01', strtotime('-1 months')));
            $param['end_time'] = strtotime(date('Y-m-01')) - 1;
            $beforeMonthAmount = $model->getSignMoneyV2($param)['amount'] ?? 0;
            //上月总消耗金额
            $beforeMonthsConsumption = $accountModel->getUserConsumptionV2($param);
            //上月投入产出比
            $beforeMonthPercent = ($beforeMonthAmount != 0) ? round($beforeMonthAmount * 10000 / $beforeMonthsConsumption * 100, 2) : 0;
            //月环比
            if ($beforeMonthPercent == 0) {
                $result['month_percent']['percent'] = ($percent > 0) ? 100 : 0;
                $result['month_percent']['symbol'] = ($percent > 0) ? 'plus' : '';
            } else {
                $gap = $percent - $beforeMonthPercent;
                $result['month_percent']['percent'] = round(abs($gap) / (float)$beforeMonthPercent * 100, 2);
                $result['month_percent']['symbol'] = $gap > 0 ? 'plus' : ($gap < 0 ? 'reduce' : '');
            }

            //获取去年同比2.0总签单金额
            $param['start_time'] = strtotime(date('Y-m-01', strtotime('-1 years')));
            $param['end_time'] = strtotime(date('Y-m-01', strtotime('-1 year +1 months'))) - 1;
            $beforeYearAmount = $model->getSignMoneyV2($param)['amount'] ?? 0;
            //上月总消耗金额
            $beforeYearConsumption = $accountModel->getUserConsumptionV2($param);
            //去年同月投入产出比
            $beforeYearPercent = ($beforeYearConsumption != 0) ? round($beforeYearAmount / $beforeYearConsumption, 2) : 0;
            //月同比
            if ($beforeYearPercent == 0) {
                $result['month_current_compare']['percent'] = ($percent > 0) ? 100 : 0;
                $result['month_current_compare']['symbol'] = ($percent > 0) ? 'plus' : '';
            } else {
                $gap = $percent - $beforeYearPercent;
                $result['month_current_compare']['percent'] = round(abs($gap) / $beforeYearPercent * 100, 2);
                $result['month_current_compare']['symbol'] = $gap > 0 ? 'plus' : ($gap < 0 ? 'reduce' : '');
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
     * @des:签约企业分析
     * @新签 合同会员首次出现 续费操作时间  到期本次合同到期
     * @param $input
     * @return array
     */
    public function getSignVipAnalysis($input)
    {
        try {
            if (isset($input['tab_month']) && $input['tab_month'] == 1) {
                $input['start_time'] = strtotime(date('Y-m-01'));
            } else {
                $input['start_time'] = strtotime(date('Y-m-01', strtotime('-2 months')));
            }
            $input['end_time'] = time();

            //合同表模型
            $userVipModel = new UserVip();
            //2.0会员消耗金额表
            $userCompanyAccountLogModel = new UserCompanyAccountLog();
            //2.0落档
            $modelVipStatusModel = new UserCompanyVipStatus();

            //新签企业数量
            $signAmount= $userVipModel->getNewSignVipAmount($input['start_time'],$input['end_time']);
            $result['target'][] = [
                'name' => '新签企业',
                'count' => $signAmount
            ];

            #续费企业，合同续费的操作时间是当前查询时间内1.0+2.0
            $renewV1 = $renewV2 = 0;
            $renewV1 = $userVipModel->getRenewVipAmount($input['start_time'],$input['end_time']);
            $renewV2 = $userCompanyAccountLogModel->getRenewCompanyAmount($input['start_time'],$input['end_time']);
            $result['target'][] = [
                'name' => '续费企业',
                'count' => $renewV1 + $renewV2
            ];

            #到期企业
            $matureV1 = $matureV2 = 0;
            $matureV1 = $userVipModel->getMatureAmount($input['start_time'], $input['end_time']);
            $matureV2 = $modelVipStatusModel->getMatureAmount($input['start_time'], $input['end_time']);
            $result['target'][] = [
                'name' => '到期企业',
                'count' => $matureV1 + $matureV2
            ];

           //$openCityCount = $quyuModel->getAllOpenCityCount();//所有开站城市
            $signCity =  $userVipModel->getNewSignVipCity($input['start_time'],$input['end_time']);//新签企业数城市维度
            $allSign = array_sum(array_column($signCity,'count'));//有签约企业的城市数
            //城市新签企业数量占比
            $result['sign_city'] = [
                //['name' => '0家', 'count' => ($openCityCount - $allSign)],
                ['name' => '1家', 'count' => 0],
                ['name' => '2家', 'count' => 0],
                ['name' => '3家', 'count' => 0],
                ['name' => '4家', 'count' => 0],
                ['name' => '>4家', 'count' => 0],
            ];
            //签约企业数范围一致的城市整理
            if (!empty($allSign)) {
                foreach ($signCity as $k => $v) {
                    if ($v['company_amount'] > 4) {
                        $result['sign_city'][4]['count'] += intval($v['count']);
                    } else {
                        $result['sign_city'][$v['company_amount']-1]['count'] += intval($v['count']);
                    }
                }
            }

            //城市到期企业数量占比
            $matureCityV1 = $userVipModel->getMatureCity($input['start_time'], $input['end_time']);
            $matureCityV2 = $modelVipStatusModel->getMatureCity($input['start_time'], $input['end_time']);
            $v2City = array_column($matureCityV2??[],null,'cs');
            //按城市把1.0和2.0新签的企业数规整
            $city = [];
            foreach (@$matureCityV1 as $k => $v) {
                $city[$v['cs']] = [
                    'cs' => $v['cs'],
                    'company_amount' => $v['company_amount']
                ];
                if (isset($v2City[$v['cs']])) {
                    $city[$v['cs']]['company_amount'] += $v2City[$v['cs']]['company_amount'];
                    unset($v2City[$k['cs']]);
                }
            }
            //剩余的2.0到期的城市企业未在1.0里面的整合
            if (!empty($v2City)) {
                array_push($city, $v2City);
            }
            //到期会员数一致的城市规整
            $city = array_values($city);
            //到期的所有城市数
            $matureCityAll = count($city);
            //到期企业数范围一致的城市整理
            $result['mature_city'] = [
               // ['name' => '0家', 'count' => ($openCityCount-$matureCityAll)],
                ['name' => '1家', 'count' => 0],
                ['name' => '2家', 'count' => 0],
                ['name' => '3家', 'count' => 0],
                ['name' => '4家', 'count' => 0],
                ['name' => '>4家', 'count' => 0],
            ];
            if (!empty($matureCityAll)) {
                foreach ($city as $k => $v) {
                    if ($v['company_amount'] > 4) {
                        $result['mature_city'][4]['count'] += 1;
                    } else {
                        $result['mature_city'][$v['company_amount']-1]['count'] += 1;
                    }
                }
            }

            //签约企业分析
            $return = [
                'error_code' => 0,
                'error_msg' => BaseStatusCodeEnum::CODE_0,
                'data' => $result
            ];
        } catch (Exception $exception) {
            $return = [
                'error_code' => 5000003,
                'error_msg' =>$exception->getMessage(),
                'data' => ''
            ];
        } finally {
            return $return;
        }
    }


    /**
     * @des:省份会员分析
     */
    public function getSfSignAnalysis()
    {
        $return = [
            'error_code' => 0,
            'error_msg' => BaseStatusCodeEnum::CODE_0,
            'data' => ""
        ];
        $userModel = new User();
        //省份会员数据 前10%赋值1，前20%赋值2，前70%赋值3，其他赋值0
        $sfCity = $userModel->getSfVipAmount();
        if (empty($sfCity)) {
            return $return;
        }
        //省份总个数
        $sfTotal = count($sfCity);
        $top10 = round($sfTotal*0.1);
        $top20 = $top10 + round($sfTotal * 0.2);
        $top70 = round($sfTotal * 0.7);

        //省份数据规整
        $result['sf_city'] = $sfCity;
        foreach ($sfCity as $k => $value) {
            if ($k < $top10) {
                $result['sf_city'][$k]['color'] = 1;
            } else if ($k >= $top10 && $k < $top20) {
                $result['sf_city'][$k]['color'] = 2;
            } else if ($k >= $top20 && $k < $top70) {
                $result['sf_city'][$k]['color'] = 3;
            }
            continue;
        }
        unset($sfCity);

        $quyuModel = new Quyu();
        $vipAmount = $quyuModel->getAllOpenCityCount();//所有开站城市
        //获取各省份开站城市总数
        $cityOpenCountByUid = (new Quyu())->getOpenCityCountByUid();
        $sfCityNumber = array_column($cityOpenCountByUid,'amount','uid');
        //城市会员数据 数据结构待定
        $csCity = $userModel->getCsVipAmount();
        $csCityFormat = $this->getCsCityAmountReact($csCity);
        $csDetail = $key_arr = array_pad([],6,0);
        //返回数据整理
        foreach (@$result['sf_city'] as $k => $v) {
            $result['sf_city'][$k]['value']['vip_amount'] = $sfCityNumber[$v['sf']];
            $result['sf_city'][$k]['value']['detail'] = isset($csCityFormat[$v['sf']]) ? $csCityFormat[$v['sf']]['data'] + $key_arr : $key_arr;
            $result['sf_city'][$k]['value']['detail'][0] = intval($sfCityNumber[$v['sf']] ?? 0) - array_sum($result['sf_city'][$k]['value']['detail']);
            array_multisort(array_keys($result['sf_city'][$k]['value']['detail']), SORT_ASC, $result['sf_city'][$k]['value']['detail']);
            unset($result['sf_city'][$k]['vip_amount']);
            //全国数据
            $csDetail[0] += $result['sf_city'][$k]['value']['detail'][0];
            $csDetail[1] += $result['sf_city'][$k]['value']['detail'][1];
            $csDetail[2] += $result['sf_city'][$k]['value']['detail'][2];
            $csDetail[3] += $result['sf_city'][$k]['value']['detail'][3];
            $csDetail[4] += $result['sf_city'][$k]['value']['detail'][4];
            $csDetail[5] += $result['sf_city'][$k]['value']['detail'][5];
        }
        //全国数据
        $result['country'] = [
            'vip_amount' => $vipAmount ?? 0,
            'cs_detail'=> $csDetail
        ];

        $return['data'] = $result;
        return $return;
    }

    /**
     * @des:近一年企业续费率分析
     */
    public function getRenewAnalysis()
    {
       $time = $this->getCurrentOneYearMonth();

        $result['xAxis'] = $time['show_date'];
        $model = new User();
        //1.0会员续费的数量
        $renewV1 = $model->getMembershipNoramlRenewal($time['start_time'], $time['end_time'], null);
        $renewV1 = array_column($renewV1,null,'date');
        //1.0会员到期数
        $matureV1 = $model->getMembershipNoramlEndRenewal($time['start_time'], $time['end_time'], null);
        $matureV1 = array_column($matureV1,null,'in_start_time');

        //当月2.0续费会员
        $renewV2 = $model->getMembershipPayment(strtotime($time['start_time']), strtotime($time['end_time'])+8639, null);
        $renewV2 = array_column($renewV2,null,'now_at');
        //余额不足3000的次数
        $matureV2 = $model->getMembershipAccountInsufficientBalance(strtotime($time['start_time']), strtotime($time['end_time'])+8639, null);
        $matureV2 = array_column($matureV2,null,'date');


        $seriesV1 = ['name' => '1.0会员', 'type' => 'line', 'data' => []];
        $seriesV2 = ['name' => '2.0会员', 'type' => 'line', 'data' => []];
        foreach ($time['year_month'] as $v) {
            if (isset($matureV1[$v]) && $matureV1[$v]['count'] > 0) {
                array_push($seriesV1['data'], round(($renewV1[$v]['count'] ?? 0) / $matureV1[$v]['count'], 4) * 100);
            } else {
                array_push($seriesV1['data'], (($renewV1[$v]['count'] ?? 0) > 0 ? 1 : 0) * 100);
            }
            if (isset($matureV2[$v]) && $matureV2[$v]['count'] > 0) {
                array_push($seriesV2['data'], round(($renewV2[$v]['count'] ?? 0) / $matureV2[$v]['count'], 4) * 100);
            } else {
                array_push($seriesV2['data'], (($renewV2[$v]['count'] ?? 0) > 0 ? 1 : 0) * 100);
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

    protected function getCsCityAmountReact($cs_city)
    {
        $result = array();
        if (!empty($cs_city)) {
            foreach ($cs_city as $k => $v) {
                $result[$v['sf_id']]['sf_id'] = $v['sf_id'];
                if ((int)$v['vip_amount'] > 4) {
                    if (isset($result[$v['sf_id']]['data'][5])) {
                        $result[$v['sf_id']]['data'][5] += (int)$v['cs_amount'];
                    } else {
                        $result[$v['sf_id']]['data'][5] = (int)$v['cs_amount'];
                    }
                } else {
                    $result[$v['sf_id']]['data'][$v['vip_amount']] = (int)(isset($result[$v['sf_id']]['data'][$v['vip_amount']]) ?? 0) + (int)$v['cs_amount'];
                }
                unset($cs_city[$k]);
            }
        }
        return $result;
    }

    public function getCurrentOneYearMonth()
    {
        $start_time = date('Y-m-01',strtotime('-11 months'));
        $end_time = date('Y-m-d');
        //是否需要显示年
        $showDate = $yearMonth = [];
        $time = date('Y-m-01');
        for ($i = -11; $i < 1; $i++) {
            $yearMonth[] = date('Ym', strtotime($i . " months", strtotime($time)));
            array_push($showDate, date('Y-m', strtotime($i . " months", strtotime($time))));
        }
        return [
            'start_time' => $start_time,
            'end_time' => $end_time,
            'show_date' => $showDate,
            'year_month' => $yearMonth
        ];
    }

    /**
     * @des:注册用户发单周期 (t-1)
     * @param $input
     * @return array
     */
    public function getFaDanCycleAnalysis($input)
    {
        $input['end_time'] = strtotime(date('Y-m-d 23:59:59'));
        if ($input['tab_month'] == 1) {
            $input['start_time'] = strtotime(date('Y-m-01', $input['end_time']));
        } else {
            $input['start_time'] = strtotime(date('Y-m-01', strtotime('-2 months', $input['end_time'])));
        }

        //渠道部门权限相关
        $group_id = [];
        if ($input['auth_user_id']) {
            $departmentModel = new DepartmentIdentify();
            $dept = $departmentModel->getDepartmentList($input['auth_user_id']);
            $group_id = array_column($dept, 'id');
        }
        //注册前的发单算到当天中，注册后的根据天来算
        $model = new UcenterUser();
        $list = $model->getFaDanCycleAnalysis($input['start_time'], $input['end_time'], $group_id);

        $allData = [];
        foreach (@$list as $k => $v) {
            if (isset($allData[$v['phone']])) {
                array_push($allData[$v['phone']], $v);
            } else {
                $allData[$v['phone']][] = $v;
            }
        }
        //xData设置x轴数据集合，yData设置y轴数据集合
        $xData = $yData = $valueData = [];

        //获取发单数据整理
        $faDanData = $this->getFaDanDetailByRegisterTime($allData);

        //获取首次发单和多次发单天数最长的一天，填充xData数据
        if (!empty($faDanData[0]['data']) || !empty($faDanData[1]['data'])) {
            $firstDayMax = max(array_keys($faDanData[0]['data'] ?? [0]));
            $moneyDayMax = max(array_keys($faDanData[1]['data'] ?? [0]));
            if ($firstDayMax > $moneyDayMax) {
                $valueData = array_pad([], $firstDayMax + 1, 0);
            } else {
                $valueData = array_pad([], $moneyDayMax + 1, 0);
            }
            $line1 = $line2 = [];
            for ($i = 0; $i < count($valueData); $i++) {
                array_push($line1, $faDanData[0]['data'][$i] ?? 0);
                array_push($line2, $faDanData[1]['data'][$i] ?? 0);
            }
            //设置x轴
            $xData = array_keys($valueData);
            $faDanData[0]['data'] = $line1;
            $faDanData[1]['data'] = $line2;
        }
        $reactXData = [];
        foreach (@$xData as $k => $v) {
            if ($v==0) {
                $reactXData[$k] = '当天';
            } else {
                $reactXData[$k] = $v . '天';
            }
        }
        $result['xAxis'] = $reactXData;
        $result['series'] = $faDanData;
        return [
            'error_code' => 0,
            'error_msg' => BaseStatusCodeEnum::CODE_0,
            'data' => $result
        ];
    }

    /**
     * @des:整理发单数据
     * @des:数据整理 分情况判断，
     * 注册前有发单，算到注冊当天+首次发单里面
     * 注册当天发单的 ，之前没有发单 ，算到注册当天+首次发单；之前有发单的，注册当天也有发单算到 当天+多次发单
     * 注册当天后的天维度里面有发单的，查看注册前，注册当天有发单的，算到 （发单日期-注册日期）n天+多次发单；否则算在n天里面的首次发单
     * @param $data
     * @return array
     */
    protected function getFaDanDetailByRegisterTime($data)
    {
        $return = [
            ['name' => '首次发单', 'type' => 'line', 'data' => []],
            ['name' => '多次发单', 'type' => 'line', 'data' => []]
        ];
        foreach (@$data as $key => $value) {
            $sort_key = array_column($value, 'time');
            array_multisort($sort_key, SORT_ASC, $value);
            foreach ($value as $k => $v) {
                if ($v['sign_value'] == 0) { //注册前的发单，计算到注册当天首单数据
                    $return[0]['data'][0] = (int)($return[0]['data'][0] ?? 0) + 1;
                    $key_phone = $v['phone']; //记录下循环体的key
                }
                if ($v['sign_value'] == 1) { //注册当天及之后数据
                    $n = (strtotime($v['time']) - strtotime($v['created'])) / 86400; //发单到注册的天数差值
                    if (isset($key_phone) && $key_phone == $v['phone']) { //当天+首单有值,计算到n天+多次中
                        $return[1]['data'][$n] = (int)($return[1]['data'][$n] ?? 0) + 1;
                    } else {
                        $key_phone = $v['phone'];
                        $return[0]['data'][$n] = (int)($return[0]['data'][$n] ?? 0) + 1;
                    }
                }
            }
        }
        return $return;
    }

}