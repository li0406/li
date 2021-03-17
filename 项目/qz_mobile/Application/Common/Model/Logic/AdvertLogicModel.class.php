<?php

namespace Common\Model\Logic;

use Common\Model\Db\AdvertModel;

class AdvertLogicModel {

    /**
     * 获取广告位详情
     * @return [type] [description]
     */
    public function getAdvertPosition($position_id) {
        $advertModel = new AdvertModel();
        return $advertModel->getAdvertPosition($position_id);
    }

    /**
     * 查询非JS广告位下的有效广告
     * @return [type] [description]
     */
    public function getActiveAdvert($position_id, $cs){
        $advertModel = new AdvertModel();
        return $advertModel->getActiveAdvert($position_id, $cs);
    }

}