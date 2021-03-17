<?php

namespace Home\Model\Logic;

class CpaOrdersLogicModel
{
    /**
     * 家具订单号
     * @param string $value [description]
     */
    public function getOrderId()
    {
        $num =  "J".date('Ymd'). sprintf("%05d%03d", microtime()*1000000, rand(10,99));
        return $num;
    }

    /**
     * 添加订单
     * @param [type] $data [description]
     */
    public function addOrder($data)
    {
        return D("Home/Db/CpaOrders")->addOrder($data);
    }

    public function editOrder($order_id,$data)
    {
        return D("Home/Db/CpaOrders")->editOrder($order_id,$data);
    }

    /**
     * 添加电话
     * @param [type] $data [description]
     */
    public function addSafeTel($data)
    {
        return D("Home/Db/CpaOrders")->addSafeTel($data);
    }

    /**
     * 查询订单生成的家具订单数量
     * @param  [type] $order_id [description]
     * @return [type]           [description]
     */
    public function findOrderCount($order_id)
    {
        return D("Home/Db/CpaOrders")->findOrderCount($order_id);
    }


    /**
     * 设置订单和装修公司的距离
     * @param [type] $cs  [城市ID]
     * @param [type] $lng [经度]
     * @param [type] $lat [纬度]
     */
    public function setOrderDistance($order_id,$cs,$lng,$lat)
    {
        $company = [];
        $all = [];
        $coordinate = array($lng,$lat);

        //获取当前城市装修公司
        $result = $this->getCityCompany($cs);
        foreach ($result as $key => $value) {
            foreach ($value["child"] as $k => $val) {
                $company[$val["id"]] = [
                    "cs" => $val["cs"],
                    "coordinate" => [$val["lng"],$val["lat"]]
                ];
            }
        }

        //相邻装修公司
        $result = $this->getOtherCityCompany($cs);

        foreach ($result as $key => $value) {
            foreach ($value["child"] as $k => $val) {
                $company[$val["id"]] = [
                    "cs" => $val["cs"],
                    "coordinate" => [$val["lng"],$val["lat"]]
                ];
            }
        }

        foreach ($company as $key => $value) {
            $all[] = [
                "city_id" => $value["cs"],
                "company_id" => $key,
                "distance" => get_distance($coordinate,$value["coordinate"],true),
                "order_id" => $order_id
            ];
        }

        if (count($all) > 0) {
            //删除之前的记录
            D("Home/Db/CpaOrderDistance")->delAllDistance($order_id);
            //添加数据
            D("Home/Db/CpaOrderDistance")->addAllDistance($all);
        }
        return $company;
    }

    /**
     * 获取城市装修公司
     * @param  [type] $city_id [description]
     * @return [type]          [description]
     */
    public function getCityCompany($city_id)
    {
        $model = D("User");
        $result = $model->getCapCityCompany($city_id);
        $list = [];
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["qx"], $list)) {
                $list[$value["qx"]]["cname"] = $value["qz_area"];
                $list[$value["qx"]]["child"] = [];
            }
            $list[$value["qx"]]["child"][] = [
                "qc" => $value["qc"],
                "lng" => $value["lng"],
                "lat" => $value["lat"],
                "id" => $value["id"],
                "cs" => $city_id
            ];
        }
        return $list;
    }

    /**
     * 获取相邻城市信息
     * @param  [type] $city_id [城市ID]
     * @return [type]          [description]
     */
    public function getOtherCityCompany($city_id)
    {
        $model = D("User");
        $cpaQuyu = D("Home/Db/CpaQuyu");
        $list = [];
        $other_cs = [];
        //获取相邻城市信息
        $info = $cpaQuyu->getCityInfo($city_id);

        if(!empty($info["parent_city"])){
            $other_cs[] = $info["parent_city"];
        }

        if(!empty($info["parent_city1"])){
           $other_cs[] = $info["parent_city1"];
        }

        if(!empty($info["parent_city2"])){
           $other_cs[] = $info["parent_city2"];
        }

        if(!empty($info["parent_city3"])){
           $other_cs[] = $info["parent_city3"];
        }

        if(!empty($info["parent_city4"])){
           $other_cs[] = $info["parent_city4"];
        }
        if (count($other_cs) > 0) {
            $result = $model->getCapCityCompany($other_cs);

            foreach ($result as $key => $value) {
                if (!array_key_exists($value["cs"],$list)) {
                    $list[$value["cs"]]["cname"] = $value["cname"];
                    $list[$value["cs"]]["child"] = [];
                }
                $list[$value["cs"]]["child"][] = [
                    "qc" => $value["qc"],
                    "lng" => $value["lng"],
                    "lat" => $value["lat"],
                    "id" => $value["id"],
                    "cs" => $value["cs"]
                ];
            }
        }


        return $list;
    }
}
