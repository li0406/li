<?php

namespace Home\Model\Logic;

class CompanyRoundOrderAmountLogicModel
{
    /**
     * 验证当前公司是否符合订单类型
     * @param $order_info
     * @param $user_amount
     * @return array|int[]
     */
    public function checkCompanyAmountOrderInfo($order_info, $user_amount)
    {
        //如果有补录次数则不验证多轮单单价
        if (empty($order_info)) {
            return ['error_code' => 0];
        }
        //查询设置的面积(最大,最小相同)
        $mianji = $user_amount['mjmin_mianji'] ?: $user_amount['mjmax_mianji'];

        if ($user_amount['round_order_number'] == 0) {
            //没有补轮的情况下
            //1.验证公装
            if ($order_info['lx'] == 2 && empty(ceil($user_amount['gz_price']))) {
                return ['error_code' => 400, 'error_msg' => $user_amount['jc'] . "未设置轮单费\r\n"];
            }
            if ($order_info['lx'] == 2 && $user_amount['gz_price'] > $user_amount['account_amount']) {
                return ['error_code' => 401, 'error_msg' => $user_amount["jc"] . "账户余额不足,余额:" . $user_amount["account_amount"] . "\r\n"];
            }

            //2.验证局部改造
            if ($order_info['lx'] == 1 && $order_info['lxs'] == 3 && empty(ceil($user_amount['jg_price']))) {
                return ['error_code' => 402, 'error_msg' => $user_amount['jc'] . "未设置轮单费\r\n"];
            }
            if ($order_info['lx'] == 1 && $order_info['lxs'] == 3 && $user_amount['jg_price'] > $user_amount['account_amount']) {
                return ['error_code' => 403, 'error_msg' => $user_amount["jc"] . "账户余额不足,余额:" . $user_amount["account_amount"] . "\r\n"];
            }

            //3.验证面积
            //3.2验证是否有面积的单价
            if ($order_info['lxs'] != 3 && $order_info['lx'] == 1 && empty(ceil($user_amount['mjmin_price'])) && empty(ceil($user_amount['mjmax_price']))) {
                return ['error_code' => 405, 'error_msg' => $user_amount['jc'] . "未设置轮单费\r\n"];
            }

            if ($order_info['lxs'] != 3 && $order_info['lx'] == 1 && ($order_info['mianji'] <= $mianji) && empty(ceil($user_amount['mjmin_price']))) {
                return ['error_code' => 406, 'error_msg' => $user_amount['jc'] . "未设置轮单费\r\n"];
            }
            if ($order_info['lxs'] != 3 && $order_info['lx'] == 1 && ($order_info['mianji'] <= $mianji) && $user_amount['mjmin_price'] > $user_amount['account_amount']) {
                return ['error_code' => 407, 'error_msg' => $user_amount["jc"] . "账户余额不足,余额:" . $user_amount["account_amount"] . "\r\n"];
            }
            if ($order_info['lxs'] != 3 && $order_info['lx'] == 1 && ($order_info['mianji'] > $mianji) && empty(ceil($user_amount['mjmax_price']))) {
                return ['error_code' => 408, 'error_msg' => $user_amount['jc'] . "未设置轮单费\r\n"];
            }
            if ($order_info['lxs'] != 3 && $order_info['lx'] == 1 && ($order_info['mianji'] > $mianji) && $user_amount['mjmax_price'] > $user_amount['account_amount']) {
                return ['error_code' => 409, 'error_msg' => $user_amount["jc"] . "账户余额不足,余额:" . $user_amount["account_amount"] . "\r\n"];
            }

        } else {
            if ($order_info['lx'] == 2 && empty(ceil($user_amount['gz_price']))) {
                return ['error_code' => 400, 'error_msg' => $user_amount['jc'] . "未设置轮单费\r\n"];
            }

            if ($order_info['lx'] == 1 && $order_info['lxs'] == 3 && empty(ceil($user_amount['jg_price']))) {
                return ['error_code' => 402, 'error_msg' => $user_amount['jc'] . "未设置轮单费\r\n"];
            }

            if ($order_info['lxs'] != 3 && $order_info['lx'] == 1 && empty(ceil($user_amount['mjmin_price'])) && empty(ceil($user_amount['mjmax_price']))) {
                return ['error_code' => 405, 'error_msg' => $user_amount['jc'] . "未设置轮单费\r\n"];
            }

            if ($order_info['lxs'] != 3 && $order_info['lx'] == 1 &&  $order_info['mianji'] > $mianji && empty(ceil($user_amount['mjmax_price']))) {
                return ['error_code' => 402, 'error_msg' => $user_amount['jc'] . "未设置轮单费\r\n"];
            }
            if ($order_info['lxs'] != 3 && $order_info['lx'] == 1 && $order_info['mianji'] <= $mianji && empty(ceil($user_amount['mjmin_price']))) {
                return ['error_code' => 402, 'error_msg' => $user_amount['jc'] . "未设置轮单费\r\n"];
            }
        }

        return ['error_code' => 0];
    }

    public function getCompanyAmountOrderInfo($order_info, $user_amount)
    {
        $returnData = [
            'error_code' => 0,
            'info' => []
        ];
        //验证公装
        if ($order_info['lx'] == 2) {
            return $returnData['info'] = [
                'price' => $user_amount['gz_price'],
                'mianji' => '',
                'type' => 1
            ];
        }
        //2.验证局部改造
        if ($order_info['lxs'] == 3) {
            return $returnData['info'] = [
                'price' => $user_amount['jg_price'],
                'mianji' => '',
                'type' => 2
            ];
        }
        //3.验证面积
        //查询设置的面积(最大,最小相同)
        $mianji = $user_amount['mjmin_mianji'] ?: $user_amount['mjmax_mianji'];
        if ($order_info['mianji'] <= $mianji) {
            return $returnData['info'] = [
                'price' => $user_amount['mjmin_price'],
                'mianji' => $user_amount['mjmin_mianji'],
                'type' => 3
            ];
        }
        if ($order_info['mianji'] > $mianji) {
            return $returnData['info'] = [
                'price' => $user_amount['mjmax_price'],
                'mianji' => $user_amount['mjmax_mianji'],
                'type' => 4
            ];
        }

        return ['error_code' => 400];
    }

    /**
     * @param $company
     * @param $amount
     * @return int 1绿色 2灰色
     */
    public function checkCompanyAmountColor($company,$order_info,$amount)
    {
        //如果已经存在标识，直接返回
        if ($company["new_qian_color"] == 2) {
           return $company["new_qian_color"];
        }

        $mianji = $amount['mjmin_mianji'] ?: $amount['mjmax_mianji'];
        $round_money_flag = 1; //轮单费标识 1.满足 2.不满足
        $price = 0;
        //家装符合面积最大面积不是居改
        if ($order_info['lxs'] != 3 && $order_info["lx"] == 1 && $order_info["mianji"] > $mianji ) {
            $price = $amount["mjmax_price"];
            if (empty(+$amount["mjmax_price"])) {
                $round_money_flag = 2;
            }elseif ($amount["mjmax_price"] > $amount["account_amount"]) {
                //余额小于的轮单价
                $round_money_flag = 2;
            }
        }

        //家装符合面积最小面积不是居改
        if ($order_info['lxs'] != 3 && $order_info["lx"] == 1 && $order_info["mianji"] <= $mianji ) {
            $price = $amount["mjmin_price"];
            if (empty(+$amount["mjmin_price"])) {
                $round_money_flag = 2;
            }elseif ( $amount["mjmin_price"] > $amount["account_amount"]) {
                //余额小于的轮单价
                $round_money_flag = 2;
            }
        }

        //公装符合面积最小面积
        if ($order_info["lx"] == 2) {
            $price = $amount["gz_price"];
            if (empty(+$amount["gz_price"])) {
                $round_money_flag = 2;
            } elseif ($amount["gz_price"] == 0 ||  $amount["gz_price"] > $amount["account_amount"]) {
                //余额小于的轮单价
                $round_money_flag = 2;
            }
        }

        //居改符合面积最小面积
        if ($order_info["lx"] == 1 && $order_info["lxs"] == 3 ) {
            $price = $amount["jg_price"];
            if (empty(+$amount["jg_price"])) {
                $round_money_flag = 2;
            }elseif ($amount["jg_price"] > $amount["account_amount"]) {
                //余额小于的轮单价
                $round_money_flag = 2;
            }
        }

        //有轮单费，但是没有轮单价，返回1
        if ($amount["account_amount"] > 0 && empty(+$price)) {
            return 1;
        }

        if ($amount["account_amount"] > 0 && $round_money_flag == 1) {
            return 1;
        }

        //有补轮但是轮单条件不足
        if ($amount["round_order_number"] > 0 && $round_money_flag == 2) {
            return 2;
        }

        //有轮单费，但不满足轮单条件
        if ($amount["account_amount"] > 0 && $round_money_flag == 2) {
            return 2;
        }

        return 1;
    }
}
