<?php

namespace Home\Model;
Use Think\Model;

/**
*
*/
class OrderCityInfoModel extends Model
{
    /**
     * 添加城市信息
     * @param [type] $data [description]
     */
    public function addAllInfo($data){
        return M("order_city_info")->addAll($data);
    }

    /**
     * 查询城市信息
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    public function findCityInfo($cs)
    {
        $map = array(
            "city_id" => array("EQ",$cs)
        );
        return M("order_city_info")->where($map)->find();
    }

    /**
     * 获取城市信息
     * @return [type] [description]
     */
    public function getCityInfoList()
    {
        return M("order_city_info")->order("city_id")->select();
    }

    /**
     * 删除所有数据
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function delAllInfo()
    {
        return M("order_city_info")->where(1)->delete();
    }

    /**
     * 查询单个城市提示信息
     * @param   string   $cityid  要查询的城市ID
     * @return  mixed   返回城市信息数组或null
    */
    public function checkOrderCityInfo($cityid)
    {
        $map['city_id'] = $cityid;
        $info = M("order_city_info")->where($map)->find();

        return $info;
    }
}