<?php

namespace Common\Model\Logic;

use Common\Model\Db\AdvertModel;

class AdvertLogicModel
{

    private $advertModel;

    public function __construct()
    {
        $this->advertModel = new AdvertModel();
    }

    /**
     * 获取广告位详情
     * @return [type] [description]
     */
    public function getAdvertPosition($position_id)
    {
        return $this->advertModel->getAdvertPosition($position_id);
    }

    /**
     * 查询广告位下的有效广告
     * @return [type] [description]
     */
    public function getActiveAdvert($position_id, $cs)
    {
        return $this->advertModel->getActiveAdvert($position_id, $cs);
    }

    /**
     * @param $positionId
     * @param $count
     * @return mixed
     */
    public function getAdByPosition($positionId,$count)
    {
        $data =  $this->advertModel->getAdByPosition($positionId,$count);
        foreach ($data as $key=>$value) {
            $data[$key]['source'] = changeImgUrl($value['source'],1);
        }
        return $data;
    }
}