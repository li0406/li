<?php
namespace Home\Model\Logic;

class CpaQuyuLogicModel
{
    /**
     * 获取城市信息
     * @param  [type] $city_id [description]
     * @return [type]          [description]
     */
    public function getCityInfo($city_id)
    {
       return  D("Home/Db/CpaQuyu")->getCityInfo($city_id);
    }

}
