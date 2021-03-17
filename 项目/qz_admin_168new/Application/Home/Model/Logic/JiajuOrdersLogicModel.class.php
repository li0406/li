<?php
// +----------------------------------------------------------------------
// | 家居
// +----------------------------------------------------------------------
namespace Home\Model\Logic;

use Home\Model\Db\JiajuCsosNewModel;
use Home\Model\Db\JiajuOrderPool;
use Home\Model\Db\JiajuOrderPoolModel;
use Home\Model\Db\JiajuOrdersModel;
use Home\Model\Db\LogOrderRouteModel;
use Home\Model\Db\OrdersModel;
use Home\Model\Db\SafeOrderTel8Model;

class JiajuOrdersLogicModel
{
    public function getOrdersInfo($id)
    {
        if (empty($id)) {
            return [];
        }
        $jiajuOrderModel = new JiajuOrdersModel();
        //搜索的手机号
        if (strlen($id) < 12) {
            $info = $jiajuOrderModel->getOrdersInfoByTel($id);
            if ($info) {
                $id = $info['id'];
            }
        }
        $map['o.id'] = ['eq', $id];
        $map['o.on'] = ['eq', 0];
        $map['o.on_sub'] = ['eq', 10];
        return $jiajuOrderModel->getOrderInfo($map);
    }

    public function getJiajuOrdersInfo($id)
    {
        if (empty($id)) {
            return [];
        }
        $jiajuOrderModel = new JiajuOrdersModel();
        //搜索的手机号
        if (strlen($id) < 12) {
            $info = $jiajuOrderModel->getOrdersInfoByTel($id);
            if ($info) {
                $id = $info['id'];
            }
        }
        $map['o.id'] = ['eq', $id];
        return $jiajuOrderModel->getOrderInfo($map);
    }

    public function saveJiajuOrder($source)
    {
        $data['ip'] = get_client_ip();//客户端IP地址
        $data['edit_id'] = $source['edit_id'];
        if (!empty($source['from'])) {
            $data['source_type'] = $source['from'];//来源 1是53客服 2是400电话 3是QQ咨询  11是推广部发单
        }

        if (!empty($source['source'])) {
            //客服发单
            if (intval($source['source']) == 1) {
                $data['source_src'] = 'jiajukffd';
            }
        }

        if (!empty($source['cs'])) {
            $data['cs'] = $source['cs'];//城市
        }
        if (!empty($source['qy'])) {
            $data['qx'] = $source['qy'];//区县
        }
        if (!empty($source['name'])) {
            $data['name'] = $source['name'];//业主姓名
        }
        if (!empty($source['sex'])) {
            $data['sex'] = $source['sex'];//性别，先生/女士
        }
        if (!empty($source['mobilemain'])) {
            $data['tel'] = trim($source['mobilemain']);//电话
        }
        if (!empty($source['secoundmobile'])) {
            $data['other_contact'] = $source['secoundmobile'];//备用联系方式
        }
        if (!empty($source['xiaoqu'])) {
            $data['xiaoqu'] = $source['xiaoqu'];//小区
        }
        if (!empty($source['dizhi'])) {
            $data['dz'] = $source['dizhi'];//地址
        }
        if (!empty($source['mianji'])) {
            $data['mianji'] = $source['mianji'];//面积
        }
        if (!empty($source['lx'])) {
            $data['lx'] = $source['lx'];//装修类型
        }
        if (!empty($source['yt'])) {
            $data['yt'] = $source['yt'];//用途
        }
        if (!empty($source['huxing'])) {
            $data['huxing'] = $source['huxing'];//户型
        }
        if (!empty($source['fengge'])) {
            $data['fengge'] = $source['fengge'];//喜欢风格
        }
        if (isset($source["yusuan"]) && !empty($source["yusuan"])) {
            $data['yusuan'] = $source["yusuan"];//预算
        }
        if (!empty($source['view_time'])) {
            $data['view_time'] = $source['view_time'];//到店时间
        }
        if (!empty($source['special_remarks'])) {
            $data['special_remarks'] = $source['special_remarks'];//需求描述
        }
        if (!empty($source['jjlx'])) {
            $data['furniture_type'] = $source['jjlx'];//家具类型
            $furniture_type_child = empty($source['furniture_type_child']) ? '' : $source['furniture_type_child'];//不限选择数据
            if (is_array($furniture_type_child)) {
                foreach ($furniture_type_child as $k => $v) {
                    $data['furniture_type_child'] .= $v . ',';
                }
            } else {
                $data['furniture_type_child'] = $furniture_type_child;
            }
        }
        if (!empty($source['step'])) {
            $data['step'] = $source['step'];//装修进度
        }
        if (!empty($source['orderfrom'])) {
            $data['source_type'] = $source['orderfrom'];//装修进度
        }

        $data['source'] = 164;//来源设置为164，此处对应发单位置设置的值

        $uid = $source["adminuser"];//来源adminuserID
        $userdata = D("OrderInfo")->getAdminUserByUserID($uid);
        $data['zhuanfaren'] = $userdata['name'];

        $jiajuLogic = new JiajuOrdersModel();
        //单子入库 新增插入
        $orderadd = $jiajuLogic->orderpublish($data); //传入插入新订单

        if (!empty($orderadd)) {
            return $orderadd;
        }
    }

    /**
     * 家具订单号
     * @param string $value [description]
     */
    public function getOrderId()
    {
        $num = "J" . date('Ymd') . sprintf("%05d%03d", microtime() * 1000000, rand(10, 99));
        return $num;
    }

    /**
     * 添加订单
     * @param [type] $data [description]
     */
    public function addOrder($data)
    {
        $jiajuOrderModel = new JiajuOrdersModel();
        return $jiajuOrderModel->addOrder($data);
    }

    public function editOrder($order_id, $data)
    {
        $jiajuOrderModel = new JiajuOrdersModel();
        return $jiajuOrderModel->editOrder($order_id, $data);
    }

    /**
     * 添加电话
     * @param [type] $data [description]
     */
    public function addSafeTel($data)
    {
        $jiajuOrderModel = new JiajuOrdersModel();
        return $jiajuOrderModel->addSafeTel($data);
    }

    /**
     * 查询订单生成的家具订单数量
     * @param  [type] $order_id [description]
     * @return [type]           [description]
     */
    public function findOrderCount($order_id)
    {
        $jiajuOrderModel = new JiajuOrdersModel();
        return $jiajuOrderModel->findOrderCount($order_id);
    }

    public function getOrderIpDetails($order_id)
    {
        $jiajuOrderModel = new JiajuOrdersModel();
        $list = $jiajuOrderModel->getOrderIpDetails($order_id);
        foreach ($list as $key => $value) {
            $value["type"] = "家具订单";
            $list[$key] = $value;
        }
        return $list;
    }

    public function getOrderTelDetails($order_id)
    {
        $jiajuOrderModel = new JiajuOrdersModel();
        $list = $jiajuOrderModel->getOrderTelDetails($order_id);
        foreach ($list as $key => $value) {
            $value["type"] = "家具订单";
            $list[$key] = $value;
        }
        return $list;
    }

    public function checkZxOrder($order_id)
    {
        if (empty($order_id)) {
            return ['error_code' => 400, 'error_msg' => '缺少订单号! '];
        }
        //验证是否生成过 家具订单
        $jiajuOrderModel = new JiajuOrdersModel();
        $info = $jiajuOrderModel->getJiajuOrderInfo(['o.order_id' => ['eq', $order_id]], 'o.id');
        if (count($info) > 0) {
            return ['error_code' => 400, 'error_msg' => '已经生成过家具订单! '];
        }
        return ['error_code' => 0, 'error_msg' => '通过验证! '];
    }

    public function directCreateJiajuOrderByZx($id)
    {
        //查询装修订单信息
        $info = (new OrdersModel())->getInfoByOrderId('*', ['id' => ['eq', $id]]);

        //添加默认操作人
        $user = [
            'id' => session("uc_userinfo.id"),
            'name' => session("uc_userinfo.name"),
            'uid' => session("uc_userinfo.uid")
        ];
        //添加家具订单基础信息
        $info = $this->saveDirectJiaju($info, $user, 4, 0, 1, 0);
        if (empty($info)) {
            return ['error_code' => 400, 'error_msg' => '生成失败! '];
        }
        //添加记录表
        $this->saveJiajuOrderLog($info, $user);
        return ['error_code' => 0, 'error_msg' => '家具订单生成成功'];
    }

    public function saveDirectJiaju($order_info, $user, $on = 0, $on_sub = 10, $type_fw = 0, $status = 1, $furniture_type = '全屋定制')
    {
        $jiajuOrderModel = new JiajuOrdersModel();
        $jiajuOrderPoolModel = new JiajuOrderPoolModel();
        $telModel = new SafeOrderTel8Model();
        $routeModel = new LogOrderRouteModel();
        //1.添加订单基础信息
        $save = [
            'id' => $this->getOrderId(),
            'order_id' => $order_info['id'],
            'on' => $on,
            'on_sub' => $on_sub,
            'type_fw' => $type_fw,
            'type' => 2,
            'ip' => $order_info['ip'] ?: '',
            'name' => $order_info['name'] ?: '',
            'sex' => $order_info['sex'] ?: '',
            'tel' => $order_info['tel'] ?: '',
            'tel_encrypt' => $order_info['tel_encrypt'] ?: '',
            'other_contact' => $order_info['other_contact'] ?: '',
            'xiaoqu' => $order_info['xiaoqu'] ?: '',
            'dz' => $order_info['dz'] ?: '',
            'lng' => $order_info['lng'] ?: '',
            'lat' => $order_info['lat'] ?: '',
            'huxing' => $order_info['huxing'] ?: '',
            'mianji' => $order_info['mianji'] ?: '',
            'lx' => $order_info['lx'] ?: '',
            'lxs' => $order_info['lxs'] ?: '',
            'customer' => $user['id'] ?: '',
            'chk_customer' => $user['id'] ?: '',
            'time_real' => time(),
            'time' => time(),
            'furniture_type' => $furniture_type,
            'yusuan' => 6,
            'cs' => $order_info['cs'],
            'qx' => $order_info['qx'],
            'special_remarks' => $order_info['text'] ?: '',
            'lasttime' => time(),
        ];
        $i = $jiajuOrderModel->addJiajuOrder($save);
        if ($i == 1) {
            //2.添加订单池
            $pool_save = [
                'orderid' => $save['id'],
                'time' => time(),
                'addtime' => time(),
                'status' => $status,
                'type' => 1,
                'op_uid' => empty($user['id']) ? 0 : $user['id'],
                'op_name' => empty($user['name']) ? "" : $user['name']
            ];
            $jiajuOrderPoolModel->saveOrderPool($pool_save);
            //3.添加手机号
            $tel = $telModel->getOrderTel(['orderid' => ['eq', $save['order_id']]]);
            $tel_save = [
                'orderid' => $save['id'],
                'tel8' => $tel['tel8'],
            ];
            $telModel->saveOrderTel($tel_save);
            //4.添加转换记录
            $where = [
                'order_id' => $save['order_id'],
                'jiaju_order_id' => $save['id'],
                'type' => 2,
                'type_sub' => 3,
            ];
            $route = $routeModel->getRouteInfo($where);
            if (count($route) == 0) {
                $where['addtime'] = time();
                $routeModel->saveRouteInfo($where);
            }
            return $save;
        } else {
            return [];
        }
    }

    public function saveJiajuOrderLog($data, $user)
    {
        $csosDb = new JiajuCsosNewModel();
        $now = time();
        //1.记录操作统计表
        $csosData = array(
            'order_id' => $data['id'],
            'order_on' => $data['on'],
            'order_on_sub' => $data['on_sub'],
            'order_on_sub_wuxiao' => $data['on_sub_wuxiao'],
            'order_new_type' => 1,
            'user_id' => 0,
            'user_uid' => 0,
            'kftype' => '',
            'kfgroup' => '',
            'user_name' => $user['name'],
            'lasttime' => $now
        );
        $csosDb->saveCsos($csosData);
        //2.添加审单日志
        $logData = array(
            'orderid' => $data['id'],
            'old_on' => $data['on'],
            'new_on' => $data['on'],
            'old_type_fw' => $data['type_fw'],
            'new_type_fw' => $data['type_fw'],
            'user_id' => $user['id'],
            'user_uid' => $user['uid'],
            'name' => $user['name'],
            'time' => $now,
        );
        $csosDb->addJiajuLogOrderCsos($logData);
        //3.添加orders_status_change
        $statusData = array(
            'orderid' => $data['id'],
            'on' => $data['on'],
            'on_sub' => $data['on_sub'],
            'on_sub_wuxiao' => 0,
            'user_id' => $user['id'],
            'user_user' => $user['name'],
            'cs' => $data['cs'],
            'time_add' => time()
        );
        $csosDb->addOrderStatus($statusData);
        //订单状态改变记录表
        $switchstatuslogData = array(
            'orderid' => $data['id'],
            'last_on' => $data['on'],
            'last_on_sub' => $data['on'],
            'last_on_sub_wuxiao' => $data['on'],
            'on' => $data['on'],
            'on_sub' => $data['on_sub'],
            'on_sub_wuxiao' => 0,
            'last_user_id' => $user['id'],
            'user_id' => $user['id'],
            'last_name' => $user['name'],
            'name' => $user['name'],
            'addtime' => time()
        );
        $csosDb->addJiajuSwitchStatusLog($switchstatuslogData);
    }

    public function jiajustat($kfList,$manager, $id, $group, $begin, $end)
    {
        $month_start = strtotime($begin);
        $month_end = strtotime($end) + 86400 - 1;

        $model = new JiajuOrdersModel();
        //全屋定制数据发单数据
        $result = $model->getQuanWuOrderListStat($id, $group, $month_start, $month_end);

        $quanwu = [];
        array_walk($result, function ($value) use (&$quanwu) {
            if (!empty($value['user_id'])) {
                $quanwu[$value['user_id']] = $value;
            }
        });

        //全屋定制有效单数据
        $result = $model->getQuanWuOrderYxListStat($id, $group, $month_start, $month_end);

        $quanwu_yx = [];
        array_walk($result, function ($value) use (&$quanwu_yx) {
            if (!empty($value['user_id'])) {
                $quanwu_yx[$value['user_id']] = $value;
            }
        });


        //家具数据
        $result = $model->getJiajuOrderListStat($id, $group, $month_start, $month_end);

        $orders = [];
        array_walk($result, function ($value) use (&$orders) {
            if (!empty($value['user_id'])) {
                $orders[$value['user_id']] = $value;
            }
        });

        //添加团长数据到客服
        array_walk($manager["groups"], function ($value) use (&$kfList) {
            $kfList[] = [
                'name' => $value["name"],
                'id' =>  $value["id"],
                'kfgroup' =>  $value["kfgroup"],
                'kfmanager' =>  $value["kfmanager"]
            ];
        });

        foreach ($kfList as $key => $value) {
            $kfList[$key]["quanwu_count"] = 0;
            if (array_key_exists($value["id"], $quanwu)) {
                $kfList[$key]["quanwu_count"] = $quanwu[$value["id"]]["count"];
            }

            $kfList[$key]["quanwu_yx_count"] = 0;
            if (array_key_exists($value["id"], $quanwu_yx)) {
                $kfList[$key]["quanwu_yx_count"] = $quanwu_yx[$value["id"]]["count"];
            }

            $kfList[$key]["fen_count"] = 0;
            $kfList[$key]["zen_count"] = 0;
            if (array_key_exists($value["id"], $orders)) {
                $kfList[$key]["fen_count"] = $orders[$value["id"]]["fen_count"];
                $kfList[$key]["zen_count"] = $orders[$value["id"]]["zen_count"];
            }

            // 当全屋定制单、家具（分单）、家具（赠单）的数据全为0时，不展示该员工的信息。
            if(empty($kfList[$key]['quanwu_count'])
                && empty($kfList[$key]['fen_count'])
                && empty($kfList[$key]['zen_count'])
            ){
                unset($kfList[$key]);
                continue;
            }

            if (!empty($id) && empty($group)) {
                if ($value["id"] != $id) {
                    unset($kfList[$key]);
                }
            } elseif (!empty($id) && !empty($group)) {
                if (!($value["id"] == $id && $value["kfgroup"] == $group)) {
                    unset($kfList[$key]);
                }
            } elseif (empty($id) && !empty($group)) {
                if ($value["kfgroup"] != $group) {
                    unset($kfList[$key]);
                }
            }
        }

        return $kfList;
    }

    public function quanwustat($cityList, $city, $begin, $end)
    {
        $month_start = strtotime($begin);
        $month_end = strtotime($end) + 86400 - 1;

        //获取城市发单数据
        $model = new OrdersModel();
        $result = $model->getCityOrderList($city, $month_start, $month_end);
        $order = [];
        array_walk($result, function ($value) use (&$order) {
            if (!empty($value['cs'])) {
                $order[$value['cs']] = $value;
            }
        });

        $model = new JiajuOrdersModel();
        //全屋定制数据
        $result = $model->getCityQuanWuStat($city, $month_start, $month_end);

        $quanwu = [];
        array_walk($result, function ($value) use (&$quanwu) {
            if (!empty($value['cs'])) {
                $quanwu[$value['cs']] = $value;
            }
        });

        $list = [];
        foreach ($cityList as $key => $value) {
            $list[$key] = [
                "cname" => $value["cname"]
            ];
            $list[$key]["count"] = 0;
            $list[$key]["quanwu_count"] = 0;
            if (array_key_exists($value["cid"], $order)) {
                $list[$key]["count"] = $order[$value["cid"]]["count"];
            }
            if (array_key_exists($value["cid"], $quanwu)) {
                $list[$key]["quanwu_count"] = $quanwu[$value["cid"]]["count"];
            }

            if (!empty($city)) {
                if ($value["cid"] != $city) {
                    unset($list[$key]);
                }
            }
        }
        return $list;
    }

    /**
     * 根据参数，自定义排序
     * @param array $list
     * @param $sort
     * @param $order
     * @return array
     */
    public function sortStat(array $list, $key, $order)
    {
        if (empty($list)) {
            return [];
        }
        $abs = $order == 'desc' ? 1 : -1;

        usort($list, function ($a, $b) use ($key,$abs) {
            if ($a[$key] == $b[$key]) {
                return 0;
            }
            return ($a[$key] < $b[$key]) ? 1*$abs : -1*$abs;
        });
        return $list;
    }
}