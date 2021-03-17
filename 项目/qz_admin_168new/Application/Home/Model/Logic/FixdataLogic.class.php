<?php

namespace Home\Model\Logic;

use Exception;
use Home\Model\OrderPoolModel;
use Home\Model\Db\CityOrderLackModel;

class FixdataLogic {

    // 修复城市缺单统计数据
    public function fixCityOrderLack(){

        try {
            $cityOrderLackModel = new CityOrderLackModel();
            $cityOrderLackModel->startTrans();
            $list = $cityOrderLackModel->getFixFenAvgAll();

            $success = 0;
            foreach ($list as $key => $item) {
                if (floatval($item["fen_avg"]) > $item["vip_total_count"]) {
                    if (floatval($item["fen_avg"]) != 1) {
                        $days = date("t", $item["datetime"]);
                        $tday = date("d", $item["datetime"]);
                        $lday = $days - $tday + 1;

                        $fen_avg = $item["vip_total_count"] > 1 ? $item["vip_total_count"] : 1;

                        // 计算城市订单承诺量
                        switch ($item["little"]) {
                            case '0': // A类城市
                            case '4': // S1类城市
                            case '5': // S2类城市
                                $promise_orders = 13;
                                if ($fen_avg > 1 && $fen_avg <= 1.9) {
                                    $promise_orders = 12;
                                } else if ($fen_avg > 1.9 && $fen_avg <= 2.8) {
                                    $promise_orders = 13.5;
                                } else if ($fen_avg > 2.8 && $fen_avg <= 3.5) {
                                    $promise_orders = 15;
                                } else if ($fen_avg > 3.5) {
                                    $promise_orders = 17;
                                }
                                break;

                            case '1': // B类城市
                                $promise_orders = 9.5;
                                if ($fen_avg > 1 && $fen_avg <= 1.9) {
                                    $promise_orders = 9.5 * 12 / 13;
                                } else if ($fen_avg > 1.9 && $fen_avg <= 2.8) {
                                    $promise_orders = 9.5 * 13.5 / 13;
                                } else if ($fen_avg > 2.8 && $fen_avg <= 3.5) {
                                    $promise_orders = 9.5 * 15 / 13;
                                } else if ($fen_avg > 3.5) {
                                    $promise_orders = 9.5 * 17 / 13;
                                }
                                break;

                            case '2': // C类城市
                                $promise_orders = 6;
                                if ($fen_avg > 1 && $fen_avg <= 1.9) {
                                    $promise_orders = 6 * 12 / 13;
                                } else if ($fen_avg > 1.9 && $fen_avg <= 2.8) {
                                    $promise_orders = 6 * 13.5 / 13;
                                } else if ($fen_avg > 2.8 && $fen_avg <= 3.5) {
                                    $promise_orders = 6 * 15 / 13;
                                } else if ($fen_avg > 3.5) {
                                    $promise_orders = 6 * 17 / 13;
                                }
                                break;
                            
                            default:
                                $promise_orders = 0;
                                break;
                        }

                        $promise_orders = round($promise_orders, 2);

                        // 每天的所需分单=每天的会员数*承诺单量*（1/31）/平均分几家
                        $predict_orders = $item["vip_total_num"] * $promise_orders / $days / $fen_avg;
                        $predict_month_orders = round($predict_orders * $lday, 2);
                        $predict_orders = round($predict_orders, 2); // 计算后再保留两位小数

                        $data = [
                            "id" => $item["id"],
                            "fen_avg" => $fen_avg,
                            "promise_orders" => $promise_orders,
                            "predict_orders" => $predict_orders,
                            "predict_month_orders" => $predict_month_orders,
                            "update_at" => time()
                        ];

                        $ret = $cityOrderLackModel->save($data);
                        $success += $ret;

                        unset($days, $tday, $lday);
                        unset($fen_avg, $promise_orders, $predict_orders, $predict_month_orders);
                        unset($data);
                    }
                }
            }

            $cityOrderLackModel->commit();
        } catch (Exception $e) {
            $cityOrderLackModel->rollback();
        }

        return $success;
    }

    // 订单分配给客服
    public function setOrderPoolToKefu($orderid, $op_uid, $op_name){
        try {
            $orderPoolModel = new OrderPoolModel();
            $orderPool = $orderPoolModel->getOrderPool($orderid);
            if (!empty($orderPool)) {
                if ($orderPool["status"] == 0) {
                    throw new Exception("状态不可用", 1);
                } else if ($orderPool["op_uid"]) {
                    throw new Exception("已有归属人", 1);
                }
            }
            
            $data = [
                "op_uid" => $op_uid,
                "op_name" => $op_name,
                "addtime" => time(),
                "status" => 0,
            ];

            if (empty($orderPool)) {
                $data["orderid"] = $orderid;
                $data["time"] = time();
                $res = $orderPoolModel->addOrder($data);
            } else {
                $res = $orderPoolModel->updateOrder($orderid, $data);
            }

            if ($res === false) {
                throw new Exception("操作失败", 1);
            }

            $ret = "操作成功";
        } catch (Exception $e) {
            $ret = $e->getMessage();
        }

        return $ret;
    }

}