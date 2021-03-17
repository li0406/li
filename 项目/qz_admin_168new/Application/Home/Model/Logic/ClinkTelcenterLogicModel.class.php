<?php

namespace Home\Model\Logic;

use Home\Model\Db\LogClinkTelcenterModel;
use Home\Model\Db\LogClinkOrderTelcenterModel;
use Home\Model\Db\LogClinkReviewTelcenterModel;

class ClinkTelcenterLogicModel {

    protected $telModel;
    protected $orderTelModel;
    protected $reviewTelModel;

    public function __construct(){
        $this->telModel = new LogClinkTelcenterModel();
        $this->orderTelModel = new LogClinkOrderTelcenterModel();
        $this->reviewTelModel = new LogClinkReviewTelcenterModel();
    }

    // 获取订单电话统计
    public function getClinkOrderReviewTelStat($id, $group, $monthStart, $monthEnd, $manager, $userids){
        $orderTelList = $this->orderTelModel->getClinkOrderTelStat($id, $group, $monthStart, $monthEnd, $manager, $userids);
        $orderTelList = array_column($orderTelList, null, "id");

        $reviewTelList = $this->reviewTelModel->getClinkReviewTelStat($id, $group, $monthStart, $monthEnd, $manager, $userids);
        $reviewTelList = array_column($reviewTelList, null, "id");

        $list = $orderTelList;
        foreach ($reviewTelList as $userid => $item) {
            if (array_key_exists($userid, $list)) {
                $list[$userid]["count"] += $item["count"];
                $list[$userid]["tel_count"] += $item["tel_count"];
                $list[$userid]["sum_time"] += $item["sum_time"];
            } else {
                $list[$userid] = $item;
            }
        }

        return $list;
    }

}