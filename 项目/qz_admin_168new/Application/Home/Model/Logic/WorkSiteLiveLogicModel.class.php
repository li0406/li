<?php

namespace Home\Model\Logic;

use Home\Model\Db\WorkSiteLiveModel;

class WorkSiteLiveLogicModel
{
    public function getWorkSiteInfo($order_info)
    {
        if ($order_info['on'] == 4) {
            $where = [
                'order_id' => ['eq', $order_info['id']],
                'order_type' => ['eq', 1],
            ];
            return (new WorkSiteLiveModel())->getWorkInfo($where, 'id');
        }
    }

    public function delWorkSite($order_id, $order_type = 1)
    {
        //查询是否有施工信息
        $where = [
            'l.order_id' => ['eq', $order_id],
            'l.order_type' => ['eq', $order_type],
        ];
        $db = new WorkSiteLiveModel();

        //获取当前订单的直播信息
        $live_info = $db->getWorkSiteInfo(['order_id' => ['eq', $order_id], 'order_type' => ['eq', $order_type]], 'id');
        if (count($live_info) == 0) {
            return true;
        }

        $work_info = $db->getWorkInfo($where);
        if (count($work_info) > 0) {
            //有施工信息
            $infoIds = array_column($work_info, 'id');
            //删除多媒体表
            $db->delWorkSiteMedia(['info_id'=>['in', $infoIds]]);
            //删除施工信息表
            $db->delWorkSiteInfo(['id'=>[ 'in', $infoIds]]);
        }
        //删除直播用户关联表
        $db->delWorkSiteUserRelate(['live_id' => ['eq', $live_info['id']]]);
        //删除直播表
        $db->delWorkSiteLive(['id' => ['eq', $live_info['id']]]);
        return true;
    }

}
