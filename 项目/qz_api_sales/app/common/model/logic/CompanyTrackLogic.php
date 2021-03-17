<?php

namespace app\common\model\logic;

use Exception;
use think\facade\Request;
use app\common\model\db\CompanyTrack;

use app\common\enum\BaseStatusCodeEnum;
use app\index\enum\CompanyTrackCodeEnum;

class CompanyTrackLogic {

    /**
     * 获取订单跟单情况
     * 根据会员公司分组
     * @param $order_id
     * @return array
     */
    public function getOrderTrackGroupByCompany($order_id){
        $companyTrackModel = new CompanyTrack();
        $list = $companyTrackModel->getOrderTrackList($order_id);

        $company_track_list = [];
        foreach ($list as $key => $item) {
            $item["track_date"] = date("Y-m-d H:i", $item["track_time"]);
            $item["track_step_name"] = CompanyTrackCodeEnum::getItem("track_step", $item["track_step"]);

            $company_id = $item["company_id"];
            if (!array_key_exists($company_id, $company_track_list)) {
                $company_track_list[$company_id] = [
                    "company_id" => $company_id,
                    "company_qc" => $item["company_qc"],
                    "company_jc" => $item["company_jc"],
                    "track_list" => []
                ];
            }

            $company_track_list[$company_id]["track_list"][] = $item;
        }

        return array_values($company_track_list);
    }

    public function getTrackCountByOrderIds(array $orderIds){
        $companyTrackModel = new CompanyTrack();
        $trackList = $companyTrackModel->getTrackCountByOrderIds($orderIds);
        $trackList = array_column($trackList->toArray(), null, "order_id");

        return $trackList;
    }

}
