<?php

namespace app\common\model\logic;

use app\common\model\db\OrderInfo;
use app\common\model\db\OrderStats;

class OrderStatsLogic {

    protected $orderStatsModel;

    public function __construct(){
        $this->orderStatsModel = new OrderStats();
    }

    // 订单跟踪状态统计
    public function setOrderLfStats($order_id){
        $orderInfoModel = new OrderInfo();
        $stats = $orderInfoModel->getOrderLfStats($order_id);

        $data = [
            "lf_first_time" => intval($stats["lf_first_time"]),
            "lf_num" => intval($stats["lf_num"]),
            "lf_un_num" => intval($stats["lf_un_num"]),
            "lf_jm_num" => intval($stats["lf_jm_num"]),
            "lf_qiandan_num" => intval($stats["lf_qiandan_num"]),
            "lf_unsign_num" => intval($stats["lf_unsign_num"]),
            "updated_at" => time(),
            "lf_status" => 1
        ];

        // 量房状态
        if ($data["lf_num"] || $data["lf_jm_num"] || $data["lf_qiandan_num"]){
            $data["lf_status"] = 2; // 已量房
        } else if ($data["lf_un_num"] && $data["lf_un_num"] >= $stats["count"] - 1) {
            $data["lf_status"] = 3; // 未量房
        }

        return $this->saveStatsInfo($order_id, $data);
    }

    // 保存数据
    public function saveStatsInfo($order_id, $data){
        $info = $this->orderStatsModel->getOrderStatsInfo($order_id);
        if (empty($info)) {
            $data["order_id"] = $order_id;
            $data["created_at"] = time();
            $ret = $this->orderStatsModel->addInfo($data);
        } else {
            $ret = $this->orderStatsModel->updateInfo($order_id, $data);
        }

        return $ret;
    }

}