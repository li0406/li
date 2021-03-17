<?php
// +----------------------------------------------------------------------
// | OrderPondLogicModel  订单池逻辑层
// +----------------------------------------------------------------------

namespace Home\Model\Logic;
use Home\Model\Db\OrderPondCityModel;
use Home\Model\Db\OrderPondServiceModel;
use Home\Model\Db\OrderPondModel;
use Home\Model\OrderPoolModel;

class OrderPondLogicModel
{
    /**
     * 订单池信息
     */
    public function getPondList()
    {
        return D('Home/Db/OrderPond')->getPondList();
    }

    /**
     * 订单池具体信息
     */
    public function getPondDetail($id)
    {
        return D('Home/Db/OrderPond')->getPondDetail($id);
    }

    /**
     * 验证订单池信息
     * @param $data
     */
    public function validatePond($data, $flag = 0)
    {
        if ($flag != 0) {
            if (empty($data['id'])) {
                return ['status' => 0, 'info' => '参数错误，请重试'];
            }
            if ($data['id'] == 1) {
                return ['status' => 0, 'info' => '主池暂时不支持修改'];
            }
        }
        if (empty($data['pond_name'])) {
            return ['status' => 0, 'info' => '订单池名称为空'];
        }
        if (!empty(D('Home/Db/OrderPond')->getDetail(['pond_name' => $data['pond_name']]))) {
            return ['status' => 0, 'info' => '订单池名称重复'];
        }
        return true;
    }

    /**
     * 验证订单池信息
     * @param $data
     */
    public function validatePondDetail($data)
    {
        if (empty($data['pond_id'])) {
            return ['status' => 0, 'info' => '订单池未选择'];
        }

        //派单维度是订单类型时必须设置订单类型
        if ($data['dimension'] == 2) {
            if ($data['type'] == 0) {
                return ['status' => 0, 'info' => '派单维度是订单类型时不能选择默认类型'];
            }
        } else {
            if (empty($data['city'])) {
                return ['status' => 0, 'info' => '城市未选择'];
            }
        }

        if ($data['pond_id'] == 1) {
            if ($data['dimension'] == 2 || $data["type"] != 0) {
                return ['status' => 0, 'info' => '池1默认设置不允许修改'];
            }
        }

        if (empty($data['service'])) {
            return ['status' => 0, 'info' => '客服未选择'];
        }

        return true;
    }

    /**
     * 新增/编辑订单池
     * @param $data
     * @param array $map
     * @return mixed
     */
    public function addOrderPond($data,$map = [])
    {
        //增加修改日志
        $logData = [
            'admin_id' => session('uc_userinfo.id'),
            'admin_name' => session('uc_userinfo.name'),
            'operate_style' => '订单池',
            'operate_type' => empty($map) ? '新增' : '修改',
            'operate_value' => json_encode($data),
            'create_time' => time(),
        ];
        D('Home/Db/LogOrderPondOperate')->addLog($logData);
        return D('Home/Db/OrderPond')->addOrderPond($data,$map);
    }

    /**
     * 删除订单池
     * @param $id
     * @return mixed
     */
    public function delOrderPond($id)
    {
        $map['id'] = ['EQ', $id];
        //分池城市返回主池
        D('Home/Db/OrderPondCity')->delOrderPondCity(['pond_id' => $map['id']]);
        //删除分池的客服
        D('Home/Db/OrderPondService')->delOrderPondService(['pond_id' => $map['id']]);

        //增加修改日志
        $logData = [
            'admin_id' => session('uc_userinfo.id'),
            'admin_name' => session('uc_userinfo.name'),
            'operate_style' => '订单池',
            'operate_type' => '删除',
            'operate_value' => json_encode(['id' => $id]),
            'create_time' => time(),
        ];
        D('Home/Db/LogOrderPondOperate')->addLog($logData);
        return D('Home/Db/OrderPond')->delOrderPond($map);
    }

    /**
     * 添加客服和城市
     * @param $data
     */
    public function addCityAndServ($data)
    {
        $now = time();
        $city = $service = [];
        $pondModel = new OrderPondModel();
        $cityModel = new OrderPondCityModel();
        $serviceModel = new OrderPondServiceModel();
        
        try {
            $pondData = [
                "pond_dimension" => $data["dimension"],
                "pond_type" => $data["type"]
            ];
            $pondModel->startTrans();

            //删除当前池子的城市和客服信息
            $cityModel->delOrderPondCity(['pond_id' => $data['pond_id']]);
            D('Home/Db/OrderPondService')->delOrderPondService(['pond_id' => $data['pond_id']]);

            //查询同派单类型的城市是否已被占用
            $result = $cityModel->getPondCityList($data['city'],$data["type"]);
            $filter_info = "被过滤的城市：";
            $data["city"] = array_flip($data["city"]);
            foreach ($result as $key => $value) {
                if (array_key_exists($value["city_id"], $data["city"])) {
                    //删除掉已经被占用的城市ID
                    unset($data["city"][$value["city_id"]]);
                    $filter_info .= $value["pond_name"].":".$value["cname"]."\r\n";
                }
            }
            $data["city"] = array_flip($data["city"]);

            //查询当前客服是否已被分配
            $result = $serviceModel->getPondServiceList($data["service"]);
            $data["service"] = array_flip($data["service"]);
            $filter_info .= "被过滤的客服：";
            foreach ($result as $item) {
                if (array_key_exists($value["kf_id"], $data["service"])) {
                    //删除掉已经被占用的客服ID
                    unset($data["service"][$value["kf_id"]]);
                    $filter_info .= $value["pond_name"].":".$value["name"]."\r\n";
                }
            }

            $data["service"] = array_flip($data["service"]);

            if (str_replace(array("被过滤的城市：","被过滤的客服："),"",$filter_info) == "") {
                unset($filter_info);
            }

            //修改数据
            $map = array(
                "id" => array("EQ",$data["pond_id"])
            );
            $result = $pondModel->addOrderPond($pondData,$map);

            if ($result !== false) {
                //修改城市
                foreach ($data['city'] as $key => $value) {
                    $city[] = [
                        'pond_id' => $data['pond_id'],
                        'city_id' => $value,
                        'create_time' => $now,
                    ];
                }
                D('Home/Db/OrderPondCity')->addOrderPondCity($city);

                //修改客服
                foreach ($data['service'] as $key => $value) {
                    $service[] = [
                        'pond_id' => $data['pond_id'],
                        'kf_id' => $value,
                        'create_time' => $now,
                    ];
                }
                D('Home/Db/OrderPondService')->addOrderPondService($service);

                $json = json_encode(array_merge($city,$service));
                //增加修改日志
                $logData = [
                    'admin_id' => session('uc_userinfo.id'),
                    'admin_name' => session('uc_userinfo.name'),
                    'operate_style' => '城市',
                    'operate_type' =>  '修改',
                    'operate_value' => $json,
                    'create_time' => time(),
                ];
                D('Home/Db/LogOrderPondOperate')->addLog($logData);
                $pondModel->commit();
                return $filter_info;
            }
        } catch (\Exception $e) {
            $pondModel->rollback();
            return false;
        }
    }

    /**
     * 获取已分配城市
     * @param int $except_pond_id 去除的订单池编号
     * @return mixed
     */
    public function getUsedCity($except_pond_id = false)
    {
        return D('Home/Db/OrderPondCity')->getCityUsed($except_pond_id);
    }

    /**
     * 获取已分配客服
     * @param int $except_pond_id 去除的订单池编号
     * @return mixed
     */
    public function getUsedService($except_pond_id = false)
    {
        return D('Home/Db/OrderPondService')->getServerUsed($except_pond_id);
    }

    /**
     * 根据客服ID获取订单池列表详细信息
     * @param $kfid
     * @param bool $withMainPond
     * @return mixed
     */
    public function getPondCityByKf($kfid, $withMainPond = false)
    {
        $list = D('Home/Db/OrderPond')->getPondCityByKf($kfid, $withMainPond);
        if (empty($list)) {
            $result = [];
        } else {
            $result['id'] = array_unique(array_column($list,'id'));
            $result['city_ids'] = array_unique(array_column($list,'city_id'));
            $result['kf_id'] = array_unique(array_column($list,'kf_id'));
        }
        return $result;
    }


    /**
     * 根据客服ID检测客服是否分配
     */
    public function findKfInPond($kfid)
    {
        $isFind = D('Home/Db/OrderPondService')->isFind($kfid);
        return !empty($isFind) ? true : false;
    }

    public function getPondUseCityList($cities,$id,$type)
    {
        $cityModel = new OrderPondCityModel();
        $result = $cityModel->getPondUseCityList($id,$type);
        $list =  array_flip( array_column($result,"city_id"));
        foreach ($cities as $key => $value) {
            if (array_key_exists($value["cid"],$list)) {
                //过滤掉已分配城市
                unset($cities[$key]);
            }
        }
        return $cities;
    }

    public function getPondUseServiceList($kfList,$id)
    {
        $cityModel = new OrderPondServiceModel();
        $result = $cityModel->getPondUseServiceList($id);
        $result =  array_flip( array_column($result,"kf_id"));
        foreach ($kfList as $key => $value) {
            if (array_key_exists($value["id"],$result)) {
                //过滤掉已分配城市
                unset($kfList[$key]);
            }
        }

        foreach ($kfList as $key => $value) {
            $list[$value["kfgroup"]][] = $value;
        }
        
        return $list;
    }

    /**
     * 剩余可派单数量
     * @param $pondList
     */
    public function getRemainingOrder($pondId,$pondList)
    {
        if (count($pondId) <= 0) {
            return null;
        }
        //获取当前池子的派单详细信息
        $pondModel = new OrderPondModel();
        $result = $pondModel->getPondListById($pondId);

        foreach ($result as $key => $value) {
            $value["cities"] = array_filter(explode(",",$value["cities"]));
            $list[$value["id"]] = $value;
        }
        //查询剩余可派单数量
        $poolModel = new OrderPoolModel();
        $orders = $poolModel->getRemainingOrderList();

        foreach ($list as $key => $value) {
            if ($value["id"] != 1 && $value["pond_type"] == 0 && count($value["cities"]) == 0) {
                continue;
            }
            foreach ($orders as $k => $val) {
                if ($value["id"] != 1) {
                    if ($value["pond_type"] == $val["source_in"]) {
                        //类型不是全部并且有城市的池子
                        if ( count($value["cities"]) > 0) {
                            if (in_array($val["cs"],$value["cities"])) {
                                $list[$key]["un_order_num"] += $val["count"];
                                unset($orders[$k]);
                            }
                        } else {
                            $list[$key]["un_order_num"] += $val["count"];
                            unset($orders[$k]);
                        }
                    }
                } else  {
                    if (in_array($val["cs"],$value["cities"])) {
                        $list[$key]["un_order_num"] += $val["count"];
                        unset($orders[$k]);
                    }
                }
            }
        }

        foreach ($pondList as $key => $value) {
            $pondList[$key]["un_order_num"] = $list[$value["id"]]["un_order_num"];
        }
        return $pondList;
    }
}