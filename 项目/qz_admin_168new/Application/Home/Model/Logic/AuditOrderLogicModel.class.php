<?php
// +----------------------------------------------------------------------
// | AuditOrderLogicModel 质检订单逻辑
// +----------------------------------------------------------------------
namespace Home\Model\Logic;

use Home\Model\Db\AuditOrderModel;
use Home\Model\Db\OrderRobPoolModel;
use Home\Model\OrdersModel;
use Home\Model\Service\ElasticsearchServiceModel;
use Home\Model\UserModel;

class AuditOrderLogicModel
{
    public static $timediff = [
        1=>'≤15分钟',
        2=>'＞15分钟'
    ];


    public static $docking_status = [
        2 => [
            'name' => '撤回',
            'id' => '2',
            'child' => [
                '更改订单编辑内容',
                '需要再次电话确认',
                '订单分赠性质更改',
                '上下备注不一致',
                '转到下属城市'
            ]
        ],
        5 => [
            'name' => '分单',
            'id' => '5',
            'child' => [
                'A级分配',
                'S级分配'
            ]
        ],
        9=>[
            'name' => '赠单',
            'id' => '9',
            'child' => [
                '距离远',
                '预算低',
                '面积小',
                '交房时间长',
                '开工时间长',
                '城市未开',
                '需要垫资',
                '不能量房',
                '改动项目少',
                '与装修相关',
                '只要设计',
                '意向不强'
            ]
        ],
        6 => [
            'name' => '推送至抢分池',
            'id' => '6',
            'child' => []
        ]
    ];

    public static $lf_time = array(
        '随时,下班,今明' => '随时,下班,今明',
        '1周内,本周末' => '1周内,本周末',
        '2周内,下周末' => '2周内,下周末',
        '3周内' => '3周内',
        '4周内' => '4周内',
        '1个月以上' => '1个月以上',
        '其他' => '其他'
    );

    public static $start_time = array(
        '1个月内开工' => '1个月内开工',
        '2个月内开工' => '2个月内开工',
        '3个月内开工' => '3个月内开工',
        '4个月内开工' => '4个月内开工',
        '5个月内开工' => '5个月内开工',
        '6个月内开工' => '6个月内开工',
        '6个月以上开工' => '6个月以上开工',
        '方案满意开工' => '方案满意开工',
        '满意拿房后开工' => '满意拿房后开工',
        '面议' => '面议'
    );

    public static $keys = array(
        '1' => '有钥匙',
        '0' => '无钥匙',
        '3' => '其他'
    );

    public static $lxs = array(
        '1' => '新房装修',
        '2' => '整体翻新',
        '3' => '局部改造'
    );

    public static $shi = array(
        0 => "-",
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        8 => 8,
        9 => 9,
        10 => 10
    );
    /**
     * 获取订单信息
     *
     * @param  string $id 订单号
     * @return mixed
     */
    public static function getOrderInfo($id)
    {
        //查询订单信息
        $info = D('Home/Orders')->findOrderInfo($id);

        if (empty($info)) {
            return $info;
        }
        //如果经度纬度存在
        if (!empty(floatval($info['lng'])) && !empty(floatval($info['lat']))) {
            $info['jingwei'] = $info['lng'] . ',' . $info['lat'];
        }

        if ($info['nf_time'] == '0000-00-00') {
            $info['nf_time'] = '';
        }

        //页面显示微信
        if (!empty($info['wx'])) {
            if (strlen($info['wx']) > 6) {
                $info['wx_show'] = substr_cut($info['wx'], 3, 3);
            } else {
                $info['wx_show'] = '****';
            }
        }
        // 计算订单状态
        if ($info['on'] == 0 && $info['on_sub'] == 9) {
            $info['orderstatus'] = 1;
        } elseif ($info['on'] == 2) {
            $info['orderstatus'] = 2;
        } elseif ($info['on'] == 4 && $info['type_fw'] == 0) {
            $info['orderstatus'] = 3;
        } elseif ($info['on'] == 4 && $info['type_fw'] == 1) {
            $info['orderstatus'] = 4;
        } elseif ($info['on'] == 4 && $info['type_fw'] == 2) {
            $info['orderstatus'] = 6;
        } elseif ($info['on'] == 4 && $info['type_fw'] == 3) {
            $info['orderstatus'] = 5;
        } elseif ($info['on'] == 4 && $info['type_fw'] == 4) {
            $info['orderstatus'] = 7;
        } elseif ($info['on'] == 5) {
            $info['orderstatus'] = 8;
        } elseif ($info['on'] == 98) {
            $info['orderstatus'] = 98;
        }


        if ($info['lng'] > 0 && $info['lat'] > 0) {
            $info['coordinate'] = $info['lng'] . ',' . $info['lat'];
        }

        $exp = array_filter(explode('；', $info['text']));
        $info['text_array'] = $exp;

        // 获取审核显号
        $info['apply_tel'] = D('OrdersApplyTel')->getApplyTelByOrdersIdAndApplyId($info['id'],session('uc_userinfo.id'));
        if ($info['apply_tel']['status'] == 2) {
            $info['tel'] = $info['tel8'];
        }

        $info['lasttime'] = empty($info['lasttime']) ? '' : $info['lasttime'];

        // 获取家居订单
        $info['jiaju_order_id'] = '';
        $logLogic = new LogOrderRouteLogicModel();
        if (($route = $logLogic->getOrderLog('', $id))) {
            $info['jiaju_order_id'] = $route['jiaju_order_id'];
        }
        $info['cname_ip'] = $info['cname'];
        // 查询ip城市, 如果没有, 就用订单城市
        if ($info['ip']) {
            $city = get_city_by_ip($info['ip']);
            if ($city[2]) {
                $info['cname_ip'] = $city[2];
            }
        }
        $robModel = new OrderRobPoolModel();
        $info['is_rob'] = $robModel->getRobInfo($id);
        return $info;
    }


    /**
     * 订单状态改变
     *
     * @param  string $orderid  订单号
     * @param  string $on       订单状态
     * @param  string $on_sub   订单子状态
     * @param  string $on_sub_wuxiao 订单无效子状态
     * @return void
     */
    public static function orderStatusChange($orderid, $on, $on_sub, $on_sub_wuxiao)
    {
        if (empty($orderid)) {
            die();
        }
        $orders = D('Home/Orders');

        //修改订单的状态
        $data['chk_customer'] = session('uc_userinfo.id');

        if (!empty($on)) {
            $data['on'] = $on;
        }

        if (!empty($on_sub_wuxiao)) {
            $data['on_sub_wuxiao'] = $on_sub_wuxiao;
        }

        if (!empty($on_sub)) {
            $data['on_sub'] = $on_sub;
        }

        $orders->editOrder($orderid, $data);

        //获取订单信息
        $orderInfo = $orders->findOrderInfo($orderid);
        //获取订单状态数据
        $orderStatusInfo = D('OrdersStatusChange')->findOrderStatus($orderid, $orderInfo['on'], $orderInfo['on_sub'], $orderInfo['on_sub_wuxiao']);

        // 添加 orders_status_change
        if (!empty($orderStatusInfo)) {
            $statusdata['user_id'] = session('uc_userinfo.id');
            $statusdata['user_user'] = session('uc_userinfo.name');
            $statusdata['time_add'] = time();
            D('OrdersStatusChange')->editOrderStatus($orderStatusInfo['orderid'], $statusdata);
        } else {
            //添加orders_status_change
            $data = [
                'orderid' => $orderInfo['id'] ,
                'on' => $orderInfo['on'],
                'on_sub' =>$orderInfo['on_sub'],
                'user_id' => session('uc_userinfo.id'),
                'user_user' =>  session('uc_userinfo.name'),
                'cs' => $orderInfo['cs'],
                'time_add' =>time()
            ];
            D('OrdersStatusChange')->addOrderStatus($data);
        }

        // 订单状态改变记录表
        // ---- 获取上一次订单的日志信息
        $lastLog = D('Home/LogOrderSwitchstatus')->getLastOrderLog($orderInfo['id']);
        $LogData = array(
            'orderid' => $orderInfo['id'],
            'last_on' => $lastLog['on'],
            'last_on_sub' => $lastLog['on_sub'],
            'last_on_sub_wuxiao' => $lastLog['on_sub_wuxiao'],
            'on' => $orderInfo['on'],
            'on_sub' => $orderInfo['on_sub'],
            'on_sub_wuxiao' => $orderInfo['on_sub_wuxiao'],
            'last_user_id' => $lastLog['user_id'],
            'user_id' => session('uc_userinfo.id'),
            'last_name' => $lastLog['name'],
            'name' => session('uc_userinfo.name'),
            'addtime' => time()
        );
        D('Home/LogOrderSwitchstatus')->addLog($LogData);
    }

    /**
     * 获取装修公司详细信息
     * 
     * @param  string $companys
     * @param  string $on
     * @return mixed
     */
    public static function getCompanyDetailsList($cs, $on, $companys = [], $getGiftOrder = '')
    {
        $companys = D('User')->getCompanyDetailsList($cs, $on, $companys, $getGiftOrder);

        //给公司数据添加设置接单区域
        $comLogic = new CompanyLogicModel();
        $companys = $comLogic->setCompanyDeliverArea($companys);
        foreach ($companys as $key => $value) {
            //计算到期时间
            $offset = (strtotime($value['end']) - strtotime(date('Y-m-d'))) / 86400 + 1;

            if ($offset <= 15 && empty($value['start_time'])) {
                $companys[$key]['end_time'] = $offset;
            }

            //合同开始时间如果大于本月1号显示新
            if ($value['start'] >= date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')))) {
                $companys[$key]['newMark'] = 1;
            }

            if ($value['cooperation_type'] != 1) {
                $companys[$key]['cooperation_name'] = $comLogic->cooperationType[$value['cooperation_type']];
            }

            // 此处处理质检人员，需要查看的装修公司备注信息
            $moreText[] = $companys[$key]['lx'] = $value['lx'] == 1 ? '家装' : ($value['lx'] == 2 ? '公装' : '公装/家装');
            $moreText[] = $companys[$key]['lxs'] = $value['lxs'] == 1 ? '新房' : ($value['lxs'] == 2 ? '旧房' : '新房/旧房');
            $moreText[] = $companys[$key]['contract'] = $value['contract'] == 1 ? '半包' : ($value['contract'] == 2 ? '全包' : '半包/全包');
            $companys[$key]['fen_mianji'] = empty($value['fen_mianji']) ? '' : $moreText[] = '>=' . $value['fen_mianji'] . 'm²';
            $inverseCompnay = $comLogic->trueUserNameByIds($value['other_id']);
            $companys[$key]['inverse'] = $inverseCompnay;
            !empty($inverseCompnay) ? ($moreText[] = $inverseCompnay) : null;
            $notDeliverArea = $comLogic->getNotDeliverArea($value['cs'], $value['deliver_area']);
            $companys[$key]['not_area'] = $notDeliverArea ? ($moreText[] = '不接{'.implode(',',array_column($notDeliverArea,'area')).'}单') : '';
            !empty($value['fen_special_needs']) ? $moreText[] = $value['fen_special_needs'] : null;
            $companys[$key]['more_extra'] = implode('、',$moreText);
            unset($moreText);
        }

        //获取装修公司暂停信息
        if (!empty($ids = array_column($companys,'id'))) {
            $result = D('UserVip')->getParseContractList($ids);
            $parseList = [];
            foreach ($result as $key => $value) {
                //计算到期时间
                $offset = (strtotime(date('Y-m-d')) - strtotime($value['end_time'])) / 86400 + 1;
                if ($offset <= 15 && empty($value['start_time'])) {
                    $parseList[$value['company_id']]['parseMark'] = 1;
                }
            }

            foreach ($companys as $key => $value) {
                if (array_key_exists($value['id'], $parseList)) {
                    $companys[$key]['parseMark'] = $parseList[$value['id']];
                }
            }
        }
        return $companys;
    }

    /**
     * 发送订单分配后通知业主的短信
     *
     * @param  string $id
     * @return mixed
     */
    public static function sendOrderSms($id)
    {
        //查询订单信息
        $info = self::getOrderInfo($id);
        if ($info['on'] != 4 && !in_array($info['type_fw'], [1, 2])) {
            return ['errmsg' => '订单尚未分配,请审核后通知业主', 'code' => 404];
        }

        //获取业主电话信息
        $tel = D('Orders')->findOrderInfoAndTel($info['id']);

        if (empty($tel['tel8'])) {
            return ['errmsg' => '业主联系电话未知，发送失败', 'code' => 404];
        }

        //获取分单装修公司
        $company = D('OrderInfo')->getOrderComapny($info['id']);
        // ---- 查询装修公司接单报备信息,装修公司网店填写的电话优先
        $jdbbList = D('User')->getJdbbList(array_column($company, 'id'));

        $datacompany = [];
        foreach ($jdbbList as $key => $value) {
            if (empty($value['tel']) && empty($value['cal']) && empty($value['receive_order_tel1'])) {
                return ['errmsg' => '装修公司【 ' . $value['jc'] . ' (' . $value['comid'] . ') 】 未设置接单联系方式,请设置后重新发送！', 'code' => 404];
            }

            $datacompany[$key]['jc'] = $value['jc'] ?: '装修公司'; //装修公司简称
            $datacompany[$key]['receive_order_tel1_remark'] = str_replace(['总', '经理', '老板'], '', $value['receive_order_tel1_remark']);
            if (!empty($value['tel'])) {
                $datacompany[$key]['receive_order_tel1'] = $value['tel'];
            } elseif (!empty($value['cal'])) {
                $datacompany[$key]['receive_order_tel1'] = $value['cal'] . $value['cals'];
            } else {
                $datacompany[$key]['receive_order_tel1'] = $value['receive_order_tel1'];
            }
        }

        // 发送已分配的公司给业主
        // 如果全局配置了 yunrongt  云融正通
        // 为什么不直接调用sendSmsQz()?  目前容联云通讯不支持本类短信发送
        // 为什么ihuyi 互亿无线要用redis队列发? 对同一个手机号码同一分钟内有发送限制,所以采用的redis队列的方式发送
        if ('yunrongt' == OP('sms_channel', 'yes')) {
            $result = A('Home/Sms')->send_yunrongt_sms($datacompany, $tel['tel8'], $info['id']);
        } else {
            // 否则就走 ihuyi 互亿无线
            $result = A('Home/Sms')->send_redis_sms($datacompany, $tel['tel8'], $info['id']);
        }
        return $result;
    }

    /**
     * 添加三方对接订单分配记录
     *
     * @param $order_id
     * @param $company_id
     * @param $type_fw
     * @param int $op_type
     * @return mixed
     */
    public static function addThirtpartLog($order_id, $company_id, $type_fw, $op_type = 1)
    {
        $data['order_id'] = $order_id;
        $data['company_id'] = $company_id;
        $data['type_fw'] = $type_fw;
        $data['op_uid'] = session('uc_userinfo.id');
        $data['op_name'] = session('uc_userinfo.name');
        $data['op_type'] = $op_type;
        $data['add_time'] = time();
        return M('order_thirdparty_log')->add($data);
    }

    /**
     * 获取质检人员对接列表
     *
     * @param $page_type
     * @param $zj_id
     * @param $cs
     * @param $search_id
     * @param $begin
     * @param $end
     * @param $type_fw
     * @param $qx
     * @return array
     */
    public static function getZjDockingList($page_type, $zj_id, $cs, $search_id, $begin, $end, $type_fw, $qx,$rob_type)
    {
        $model = new AuditOrderModel();
        import('Library.Org.Util.Page');
        switch ($page_type) {
            //已分配订单
            case 2:
                $count = $model->getAllotAuditOrdersCount($cs, $qx, $type_fw, $zj_id, $begin, $end, $search_id);
                if ($count > 0) {
                    $p = new \Page($count, 10);
                    $show = $p->show();
                    $list = $model->getAllotAuditOrdersLists($cs, $qx, $type_fw, $zj_id, $begin, $end, $search_id, $p->firstRow, $p->listRows);
                    foreach ($list as $key => $value) {
                        if ($value['time_diff'] >= 0) {
                            $list[$key]['date_diff'] = timediff($value['time_diff']);
                        }
                    }
                }
                break;
            case 3:
                //如果选择的是抢单tab列表
                $count = (new OrdersModel())->getOrderRobListCount($zj_id, $search_id, $cs, $type_fw, $begin, $end, '', '', '', $rob_type, $qx);
                if ($count > 0) {
                    $p = new \Page($count, 10);
                    $p->setConfig('prev', "上一页");
                    $p->setConfig('next', '下一页');
                    $show = $p->show();
                    $list = (new OrdersModel())->getOrderRobList($zj_id, $search_id, $cs, $type_fw, $begin, $end, $p->firstRow, $p->listRows, '', '', '', $rob_type, $qx);
                }
//                dump($list);exit;
                break;
            default:
                // 获取未分配订单，按照最后一次操作时间排序
                $result = $model->getUnAllotAuditOrdersList($cs, $qx, $type_fw, null, $begin, $end, $search_id, null, null);

                $p = new \Page(count($result), count($result));
                $show = $p->show();

                // 按照订单城市ID，进行订单分组，组内按照订单的最后一次操作时间排序
                foreach ($result as $key => $value) {
                    $city[$value['cs']]['child'][] = $value;
                }

                // 按照每个城市订单的最早的一个订单推送时间，重新正序排序
                foreach ($city as $key => $value) {
                    $lasttime = '';
                    if (!empty($value['child'])) {
                        $lasttime = min(array_column($value['child'],'csos_time'));
                    }
                    $city[$key]['lasttime'] = $lasttime;
                }
                $last_column = array_column($city, 'lasttime');
                array_multisort($last_column, SORT_ASC, $city);

                // 循环分组后结果，排序规则，先按照城市订单推送时间正序，然后按照订单的最后一次操作时间排序
                $list = array();
                foreach ($city as $key => $value) {
                    $list = array_merge($list, $value['child']);
                }
        }

        if (!empty($list)) {
            $ids = array_column($list, 'id');

            //获取每个订单的电话号码的重复次数
            $telRepeatsResult = D('Orders')->getTelnumberRepaetCountByIds($ids);
            $phoneRepeats = array_column($telRepeatsResult, 'repeat_count', 'id');

            //获取每个订单的IP的重复次数
            $ipRepeatsResult = D('Orders')->getIpRepaetCountByIds($ids);
            $ipRepeats = array_column($ipRepeatsResult, 'repeat_count', 'id');

            //是否显示'修'
            $applyresult = D('Orders')->getOrderApplyEditList($ids);
            $applyList = array_column($applyresult, 'status', 'orders_id');

            foreach ($list as $key => $value) {
                $list[$key]['phone_repeat_count'] = $phoneRepeats[$value['id']];
                $list[$key]['ip_repeat_count'] = $ipRepeats[$value['id']];
                $list[$key]['applystatus'] = $applyList[$value['id']];
            }
        }
        return ['page' => empty($show) ? '' : $show, 'list' => empty($list) ? [] : $list];
    }

    /**
     * 获取当前城市装修公司和相邻的装修公司
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    public static function getCompanyList($cs, $lng, $lat,$order_info = [])
    {
        //获取订单城市会员列表
        $list = self::getCompanyDetailsList(array($cs), 2);
        $new_qian = $other = $now = [];

        foreach ($list as $key => $value) {
            if (!array_key_exists($value['qx'], $now)) {
                $now[$value['qx']]['area_id'] = $value['qx'];
                $now[$value['qx']]['qz_area'] = $value['qz_area'];
            }

            if ((int)$lat != 0 && (int)$lng != 0 && (int)$value['lat'] != 0 && (int)$value['lng'] != 0) {
                $value['distance'] = get_distance(array($lng, $lat), array($value['lat'], $value['lng']), true, 0);
            } else {
                $value['distance'] = '无';
            }

            if (in_array($value["cooperate_mode"],[2,4])) {
                //新签返会员
                $new_qian[] = $value["id"];
            }

            $now[$value['qx']]['child'][] = $value;
        }

        //获取当前城市的签单返点会员
        $userLogic = new UserLogicModel();
        $qiandanList = $userLogic->getQianDanUserList($cs,2);

        foreach ($qiandanList as $value) {
            if (!array_key_exists($value["qx"],$now)) {
                $now[$value["qx"]]['area_id'] = $value["qx"];
                $now[$value["qx"]]['qz_area'] = $value["qz_area"];
            }
            $value["qianMark"] = 1;
            $now[$value["qx"]]["child"][] = $value;
        }

        $amountLogic = new CompanyRoundOrderAmountLogicModel();
        //新签返会员账户信息
        if (count($new_qian) > 0) {
            $result = $userLogic->getUserAccountList($new_qian);

            foreach ($now as $key => $item) {
                foreach ($item["child"] as $k => $value) {
                    $now[$key]["child"][$k]["round_order_number"] = $result[$value["id"]]["round_order_number"];
                    if (array_key_exists($value["id"],$result)) {
                        //判断是否可以勾选
                        if (isset($result[$value["id"]]["un_new_qian_change"])) {
                            $now[$key]["child"][$k]["un_new_qian_change"] = $result[$value["id"]]["un_new_qian_change"];
                        } else {
                            //再去验证 装修公司多轮单单价配置是否符合 订单要求
                            $status = $amountLogic->checkCompanyAmountOrderInfo($order_info, $result[$value["id"]]['round_order_amount']);
                            if($status['error_code'] != 0){
                                $now[$key]["child"][$k]["un_new_qian_change"] = 1;
                            }
                        }
                    }
                }
            }
        }

        //获取相邻城市
        $result = D('OrderCityRelation')->getRelationCity($cs);

        if (!empty($result)) {
            $new_qian = [];

            foreach ($result as $key => $value) {
                if ($cs != $value['cid']) {
                    $adjacentCity[] = $value['cid'];
                }
            }

            if (count($adjacentCity) > 0) {
                //相邻城市会员
                $result = self::getCompanyDetailsList($adjacentCity, 2);

                foreach ($result as $key => $value) {
                    if (!array_key_exists($value['cs'], $other)) {
                        $other[$value['cs']]['cid'] = $value['cs'];
                        $other[$value['cs']]['cname'] = $value['cname'];
                    }

                    if ((int)$lat != 0 && (int)$lng != 0 && (int)$value['lat'] != 0 && (int)$value['lng'] != 0) {
                        $value['distance'] = get_distance(array($lng, $lat), array($value['lat'], $value['lng']), true, 0);
                    } else {
                        $value['distance'] = '无';
                    }

                    if ($value["cooperate_mode"] == 2) {
                        //新签返会员
                        $new_qian[] = $value["id"];
                    }

                    $other[$value['cs']]['child'][] = $value;
                }

                //获取相邻城市的签单返点会员
                $userLogic = new UserLogicModel();
                $qiandanList = $userLogic->getQianDanUserList($adjacentCity,2);

                foreach ($qiandanList as $value) {
                    if (!array_key_exists($value["cs"],$other)) {
                        $other[$value["cs"]]["cid"] = $value["cs"];
                        $other[$value["cs"]]["cname"] = $value["cname"];
                    }
                    $value["qianMark"] = 1;
                    $other[$value["cs"]]["child"][] = $value;
                }

                //新签返会员账户信息
                if (count($new_qian) > 0) {
                    $result = $userLogic->getUserAccountList($new_qian);

                    foreach ($other as $key => $item) {
                        foreach ($item["child"] as $k => $value) {
                            $other[$key]["child"][$k]["round_order_number"] = $result[$value["id"]]["round_order_number"];
                            if (array_key_exists($value["id"],$result)) {
                                //判断是否可以勾选
                                if (isset($result[$value["id"]]["un_new_qian_change"])) {
                                    $other[$key]["child"][$k]["un_new_qian_change"] = $result[$value["id"]]["un_new_qian_change"];
                                } else {
                                    //再去验证 装修公司多轮单单价配置是否符合 订单要求
                                    $status = $amountLogic->checkCompanyAmountOrderInfo($order_info, $result[$value["id"]]['round_order_amount']);
                                    if($status['error_code'] != 0){
                                        $other[$key]["child"][$k]["un_new_qian_change"] = 1;
                                    }
                                }
                            }
                        }
                    }
                }

            }
        }

        return array($now, $other);
    }


    /**
     * 获取历史签单小区信息
     * @param  [type] $xiaoqu [description]
     * @return [type]         [description]
     */
    public static function orderHistory($xiaoqu, $cs)
    {
        if (!empty($xiaoqu)) {
            //获取分词
            //获取分词
            $server = new ElasticsearchServiceModel();
            $result = $server->analyzeWord($xiaoqu);
            $fxList[] = $xiaoqu;
            if (count($result) > 0) {
                $fxList = array_merge($fxList,$result);
            }

            //查询小区签单历史
            $result = D('Orders')->getQianDanHistory($fxList, $cs);

            if (!empty($result)) {
                $list[$xiaoqu] = array();
                foreach ($result as $key => $value) {
                    if ($value['xiaoqu'] == $xiaoqu) {
                        $list[$xiaoqu]['child'][] = array(
                            'jc' => $value['jc'],
                            'count' => $value['count'],
                            'on' => $value['on'],
                            'time' => date('Y-m-d', $value['qiandan_addtime'])
                        );
                    } else {
                        $list[$value['xiaoqu']]['child'][] = array(
                            'jc' => $value['jc'],
                            'count' => $value['count'],
                            'on' => $value['on'],
                            'time' => date('Y-m-d', $value['qiandan_addtime'])
                        );
                    }
                }
            }

        }
        return $list;
    }

    public static function getLastExpireCompanyList($cs, $date)
    {
        $model = new UserModel();
        $lostCompany = $model->getLastExpireCompanyList($cs, $date);
        foreach ($lostCompany as $key => $value) {
            $offset = (strtotime($date) - strtotime($value["end"])) / 86400 + 1;
            $lostCompany[$key]["day"] = $offset;
        }

        //2.0过期会员
        $result =  $model->getQianLastExpireCompanyList($cs,$date);
        if (count($result) > 0) {
            foreach($result as $item) {
                $offset = ceil((strtotime($date) - $item["end"])/86400)+1;
                $sub = [
                    "jc" => $item["jc"],
                    "id" => $item["id"],
                    "day" => $offset,
                    "end" => $item["end"],
                    "cooperate_mode" => $item["cooperate_mode"],
                ];
                $lostCompany[] = $sub;
            }
        }
        return $lostCompany;
    }
}