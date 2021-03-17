<?php
/**
 * 订单相关模块
 */

namespace Home\Model\Logic;

use Boris\DumpInspector;
use Common\Enums\LiangFangInfo;
use Common\Enums\OrdersInfo;
use Common\Enums\StatusCodeEnum;
use Home\Model\Db\HzsCompanyModel;
use Home\Model\Db\HzsRealOrderModel;
use Home\Model\Db\LogClinkReviewTelcenterModel;
use Home\Model\Db\LogHollyOrderTelcenterModel;
use Home\Model\Db\LogHollyOtherTelcenterModel;
use Home\Model\Db\OrderBackRecordImgModel;
use Home\Model\Db\OrderBackRecordModel;
use Home\Model\Db\OrderCompanyDialRelationModel;
use Home\Model\Db\OrderCompanyReviewModel;
use Home\Model\Db\OrderPauseListModel;
use Home\Model\Db\OrderPausePoolModel;
use Home\Model\Db\OrderRobPoolModel;
use Home\Model\Db\OrdersSourceModel;
use Home\Model\Db\SafeOrderTel8Model;
use Home\Model\Db\UmengRecordModel;
use Home\Model\Db\UserCompanyRoundOrderAmountModel;
use Home\Model\LogAdminModel;
use Home\Model\LogSmsSendModel;
use Home\Model\LogTelcenterOrdercallModel;
use Home\Model\OrderCsosNewModel;
use Home\Model\OrderDockingModel;
use Home\Model\OrdersModel;
use Home\Model\Db\OrdersModel as OrdersDbModel;
use Home\Model\Service\ElasticsearchServiceModel;
use Library\Org\Umeng\Umeng;
use Home\Model\OrderInfoModel;
use Home\Model\AdminuserModel;
use Home\Model\OrderPoolModel;
use Think\Exception;
use Think\Log;

class OrdersLogicModel
{
    protected $orderBackReason = [
        1 => '销售分赠错误修改',
        2 => '工种、设计师、材料商发布',
        3 => '同行、装修公司，施工队发布',
        4 => '订单质量问题',
        5 => '重复订单',
        6 => '更改订单编辑内容',
        7 => '需要再次电话确认',
        8 => '订单分赠性质更改',
        9 => '上下备注不一致',
        10 => '转到下属城市',
        11 => '压单',
        99 => '其它',
    ];
    /**
     * 获取城市订单 条件
     * @param $data
     * @param $cityIds
     * @return array
     */
    public function getCityOrdersMap($data)
    {
        //设置默认时间 , 前31天至今的数据
        $map['t.time_real'] = [
            ['EGT', empty($data['start']) ? strtotime(date("Y-m-d") . ' 00:00:00') : strtotime($data['start'] . ' 00:00:00')],
            ['ELT', empty($data['end']) ? time() : strtotime($data['end'] . ' 23:59:59')],
        ];

        //查询城市
        if ($data['city']) {
            $map['t.cs'] = ['eq', $data['city']];
        }
        //查询渠道来源组
        if ($data['group']) {
            $map['d.id'] = ['eq', $data['group']];
        }
        //查询渠道来源标识
        if ($data['src']) {
            $map['c.src'] = ['eq', $data['src']];
        }
        $order = '';
        if ($data['sort']) {
            $order = $data['sort'] . ' ' . $data['sort_type'];
        }
        //取出查询时间
        $search['start'] = $map['t.time_real'][0][1];
        $search['end'] = $map['t.time_real'][1][1];
        $error = null;
        if (($search['end'] - $search['start']) > (3600 * 24 * 31)) {
            $error = '查询时间不能大于31天';
        }
        //分页
        $page = 1;
        if ($data['p']) {
            $page = $data['p'];
        }
        return ['map' => $map, 'search' => $search, 'error' => $error, 'order' => $order, 'page' => $page];
    }

    /**
     * 获取时间范围所有订单 条件
     * @param $data
     * @return array
     */
    public function getOrdersDetailMap($data)
    {
        //设置默认时间 , 前31天至今的数据
        $map['b.time'] = [
            ['EGT', empty($data['start']) ? strtotime(date("Y-m-d") . ' 00:00:00') : strtotime($data['start'] . ' 00:00:00')],
            ['ELT', empty($data['end']) ? time() : strtotime($data['end'] . ' 23:59:59')],
        ];
        $map['t.on'] = ['eq', 4];
        //查询城市
        if ($data['city']) {
            $map['t.cs'] = ['eq', $data['city']];
        }
        //查询渠道来源标识
        if ($data['src']) {
            $map['b.src'] = ['eq', $data['src']];
        }
        //筛选订单状态
        if ($data['orders_type']) {
            switch ($data['orders_type']) {
                //分单
                case '1':
                    $map['t.type_fw'] = ['eq', 1];
                    $complex[] = array("_string" => "(t.qiandan_status is null or t.qiandan_status = 0)");
                    $map["_complex"] = $complex;
                    break;
                //赠单
                case '2':
                    $map['t.type_fw'] = ['eq', 2];
                    $complex[] = array("_string" => "(t.qiandan_status is null or t.qiandan_status = 0)");
                    $map["_complex"] = $complex;
                    break;
                //签单
                case '3':
                    $map['t.qiandan_status'] = ['eq', 1];
                    break;
            }
        }
        //筛选显号状态
        if ($data['openeye_st']) {
            switch ($data['openeye_st']) {
                //显号
                case '1':
                    $map['t.openeye_st'] = ['eq', 1];
                    break;
                //不显号
                case '2':
                    $complex[] = array("_string" => "(t.openeye_st is null or t.openeye_st = 0)");
                    $map["_complex"] = $complex;
                    break;
            }
        }
        $search = [];
        return ['map' => $map, 'search' => $search];
    }

    /**
     * 获取城市订单数据个数
     * @param $where
     * @return mixed
     */
    public function getCityOrdersCount($where)
    {
        return D('orders')->getCityOrdersCount($where);
    }

    /**
     * 获取城市订单数据
     * @param $where
     * @return mixed
     */
    public function getCityOrdersList($where, $count, $pageIndex, $pageCount = '20')
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $list = D('orders')->getCityOrdersList($where['map'], $where['order'], '', ($pageIndex - 1) * $pageCount, $pageCount);
        foreach ($list as $k => $v) {
            $list[$k]['time'] = date("Y", $where['search']['start']) . '年' . date("m", $where['search']['start']) . '月' . date("d", $where['search']['start']) . '日' . '-'
                . date("Y", $where['search']['end']) . '年' . date("m", $where['search']['end']) . '月' . date("d", $where['search']['end']) . '日';
            $list[$k]['fen_qian_lv'] = is_nan($v['fen_qian_order'] / $v['fen_order']) ? 0 : $v['fen_qian_order'] / $v['fen_order'] * 100;
            $list[$k]['zeng_qian_lv'] = is_nan($v['zeng_qian_order'] / $v['zeng_order']) ? 0 : $v['zeng_qian_order'] / $v['zeng_order'] * 100;
            $list[$k]['qian_lv'] = is_nan($v['qian_order'] / $v['fa_order']) ? 0 : $v['qian_order'] / $v['fa_order'] * 100;
            $list[$k]['all_liang_order'] = (int)$v['fen_liang_order'] + (int)$v['zeng_liang_order'];
        }
        import('Library.Org.Util.Page');
        $p = new \Page($count, 20);
        $show = $p->show();
        return ['list' => $list, 'page' => $show];
    }

    /**
     * 获取 导出城市订单数据
     * @param $where
     * @return mixed
     */
    public function getExplodeCityOrdersList($where)
    {
        $list = D('orders')->getCityOrdersList($where['map'], $where['order']);
        foreach ($list as $k => $v) {
            $list[$k]['time'] = date("Y", $where['search']['start']) . '年' . date("m", $where['search']['start']) . '月' . date("d", $where['search']['start']) . '日' . '-'
                . date("Y", $where['search']['end']) . '年' . date("m", $where['search']['end']) . '月' . date("d", $where['search']['end']) . '日';
            $list[$k]['fen_qian_lv'] = $v['fen_qian_order'] / $v['fen_order'] * 100;
            $list[$k]['zeng_qian_lv'] = $v['zeng_qian_order'] / $v['zeng_order'] * 100;
            $list[$k]['qian_lv'] = $v['qian_order'] / $v['fa_order'] * 100;
            $list[$k]['all_liang_order'] = (int)$v['fen_liang_order'] + (int)$v['zeng_liang_order'];
        }
        return $list;
    }

    /**
     * 获取城市各类型  订单统计数据
     * @param $where
     * @return mixed
     */
    public function getCityOrdersAllList($where, $group = '')
    {
        $list = D('orders')->getCityOrdersAllList($where['map'], $group);
        foreach ($list as $k=>$v){
            $list[$k]['all_liang_order'] = (int)$v['fen_liang_order'] + (int)$v['zeng_liang_order'];
            $list[$k]['all_liang_rel_order'] = (int)$v['fen_liang_rel_order'] + (int)$v['zeng_liang_rel_order'];
        }
        return $list;
    }

    /**
     * 获取当前渠道时间段内 所有信息
     */
    public function getOrdersDetailList($where)
    {
        $list = D('orders')->getOrdersDetailList($where['map'], 't.id');
        return $list;
    }

    /**
     * 获取签单审核列表
     * @return [type] [description]
     */
    public function getQianDanList($city_id,$id,$begin,$end,$status,$state,$city,$company,$qian)
    {
        $monthEnd = mktime(23,59,59,date("m"),date("d"),date("Y"));
        $monthStart = strtotime("-3 month",$monthEnd);

        if (!empty($begin) && !empty($end)) {
           $monthStart = strtotime($begin);
           $monthEnd = strtotime($end) + 86400 -1;
        }

        $count = D("Orders")->getQianDanListCount($city_id,$id,$monthStart,$monthEnd,$status,$state,$city,$company,$qian);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $page = new \Page($count,20);
            $result = D("Orders")->getQianDanList($city_id,$id,$monthStart,$monthEnd,$status,$state,$city,$company,$qian,$page->firstRow, $page->listRows);
            $show = $page->show();
            foreach ($result as $key => $value) {
                $ids[] = $value["id"];
            }

            //获取订单电话记录数量
            $logs = D("Home/Logic/LogTelcenterOtherordercallLogic")->getOrderTelRecordCount($ids);

            //获取合力电话记录数量
            $model = new LogHollyOtherTelcenterModel();
            $newLogs = $model->getOrderTelRecordCount($ids);
            if (count($newLogs) > 0) {
                if (count($logs) > 0) {
                    $logs = array_merge($logs,$newLogs);
                } else {
                    $logs = $newLogs;
                }
            }

            foreach ($result as $key => $value) {
                foreach ($logs as $val) {
                    if ($value["id"] == $val["orderid"]) {
                        $result[$key]["tel_count"] = $val["count"];
                    }
                }
            }


        }

        return array("list" => $result, "page" => $show);
    }

    public function editOrder($id,$data)
    {
        return D("Orders")->editOrder($id,$data);
    }

    public function getOrderInfo($id)
    {
        //查询订单信息
        $order = D('Home/Orders');
        $info = $order->findOrderInfo($id);
        if (count($info) == 0) {
            $this->ajaxReturn(array("code" => 404, 'info' => '该订单不存在'));
        }
        //如果经度纬度存在
        if(!empty(floatval($info["lng"]))&&!empty(floatval($info["lat"]))){
            $info["jingwei"] = $info["lng"].",".$info["lat"];
        }

        if ($info["nf_time"] == "0000-00-00") {
            $info["nf_time"] = "";
        }

        //计算订单状态
        if ($info['on'] == 0 && $info['on_sub'] == 9) {
            $info["orderstatus"] = 1;
        } elseif ($info['on'] == 2) {
            $info["orderstatus"] = 2;
        } elseif ($info['on'] == 4 && $info['type_fw'] == 0) {
            $info["orderstatus"] = 3;
        } elseif ($info['on'] == 4 && $info['type_fw'] == 1) {
            $info["orderstatus"] = 4;
        } elseif ($info['on'] == 4 && $info['type_fw'] == 2) {
            $info["orderstatus"] = 6;
        } elseif ($info['on'] == 4 && $info['type_fw'] == 3) {
            $info["orderstatus"] = 5;
        } elseif ($info['on'] == 4 && $info['type_fw'] == 4) {
            $info["orderstatus"] = 7;
        } elseif ($info['on'] == 5) {
            $info["orderstatus"] = 8;
        }

        $exp = array_filter(explode("；", $info["text"]));
        $info["text_array"] = $exp;

        $info["tel_sensitive"] = $info["tel"];
        if ($info["openeye_st"] == 1) {
            $info["tel"] = $info["tel8"];
        }

        $info["lasttime"] = empty($info["lasttime"]) ? "" : $info["lasttime"];
        $info["xiaoqu"] = trim($info["xiaoqu"]);
        return $info;
    }

    public function updateOrderInfo($data)
    {
        $save = [
            'lasttime' => time()
        ];
        if ($data['visitime']) {
            $save['visitime'] = $data['visitime'];
        }
        if ($data['huifan']) {
            $save['huifan'] = $data['huifan'];
        }
        if ($data['visitime']) {
            return D("Orders")->editOrder($data['orderid'], $save);
        } else {
            return [];
        }
    }

    /**
     * 获取订单信息
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getSingleOrderInfo($order_id)
    {
        return D("Orders")->getOrderInfo($order_id);
    }

    /**
     * 获取分单信息
     * @param  [type] $order_id [description]
     * @return [type]           [description]
     */
    public function getOrderComapny($order_id)
    {
        $result = D("OrderInfo")->getOrderComapny($order_id);
        foreach ($result as $key => $value) {
            $company[] = $value["jc"];
        }
        return $company;
    }

    public function addQiandanLog($type, $orderid, $action, $data='')
    {
       return D("Home/Db/LogQiandanchk")->add_log($type, $orderid, $action, $data='');
    }

    /**
     * 转订单城市
     * @param  [array] $data [转单信息]
     * @return mixed
     */
    public function turn_order($data)
    {
        if (empty($data['city'][0]) || empty($data['city'][1]) || empty($data['city'][2])) {
            return ['code' => '404', 'errmsg' => '请选择转单城市'];
        }
        //获取订单信息
        $info = D('Home/Orders')->findOrderInfo($data['id']);
        $subData = array(
            'sf' => $data['city'][0],
            'cs' => $data['city'][1],
            'qx' => $data['city'][2]
        );
        $result = D('Home/Orders')->editOrder($data['id'], $subData);
        if ($result !== false) {
            //添加转单日志
            $source = array(
                'username' => session('uc_userinfo.name'),
                'admin_id' => session('uc_userinfo.id'),
                'orderid' => $data['id'],
                'type' => 1,
                'postdata' => json_encode($info),
                'addtime' => time()
            );
            D('LogEditorders')->addLog($source);
            return ['code' => '200','errmsg' => '操作成功！'];
        }
        return ['code' => '404', 'errmsg' => '操作失败！'];
    }


    /**
     * 获取历史签单小区信息
     * @param  [string] $xiaoqu [小区]
     * @param  [string] $cs [城市ID]
     * @return mixed
     */
    public function orderHistory($xiaoqu, $cs)
    {
        $list = [];
        if (!empty($xiaoqu)) {
            //获取分词
            $server = new ElasticsearchServiceModel();
            $result = $server->analyzeWord($xiaoqu);
            $fxList[] = $xiaoqu;
            if (count($result) > 0) {
                $fxList = array_merge($fxList,$result);
            }

            //查询小区签单历史
            $result = D("Orders")->getQianDanHistory($fxList, $cs);
            if (count($result) > 0) {
                $list[$xiaoqu] = [];
                foreach ($result as $key => $value) {
                    if ($value["xiaoqu"] == $xiaoqu) {
                        $list[$xiaoqu]["child"][] = array(
                            "jc" => $value["jc"],
                            "count" => $value["count"],
                            "on" => $value["on"],
                            "time" => date("Y-m-d", $value["qiandan_addtime"])
                        );
                    } else {
                        $list[$value["xiaoqu"]]["child"][] = array(
                            "jc" => $value["jc"],
                            "count" => $value["count"],
                            "on" => $value["on"],
                            "time" => date("Y-m-d", $value["qiandan_addtime"])
                        );
                    }
                }
            }
        }
        return $list;
    }

    /**
     * 字段统计列表
     * @return [type] [description]
     */
    public function getOrderFieldStatList($begin,$end,$city,$state)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end)+86400-1;
        }

        if (!empty($state)) {
            $on = 4;
        }

        $result = D("Home/Orders")->getOrderFieldStatList($monthStart,$monthEnd,$city,$on);

        $list = [];
        $field = [
            "name" => "联系人",
            "cs" => "城市",
            "qx" => "区域",
            "xiaoqu" => "小区名称",
            "mianji" => "面积(m2)",
            "yusuan" => "预算"
        ];

        foreach ($result as $key => $value) {
            if (!array_key_exists($value["field"],$list)) {
                $list[$value["field"]] = $value;
            }

            $list[$value["field"]]["name"] = $field[$value["field"]];
            //自填率=自填数量/订单总数
            $list[$value["field"]]["before_rate"] = setInfNanToN(round($value["before_count"]/$value["all_count"],4))*100;

            //修改率 = 修改数量/自填数量
            $list[$value["field"]]["update_rate"] = setInfNanToN(round($value["update_count"]/$value["before_count"],4))*100;

            //代填率=代填数量/订单总数
            $list[$value["field"]]["after_rate"] = setInfNanToN(round($value["after_count"]/$value["all_count"],4))*100;
        }

        return $list;
    }

    public function getOrderFieldStat($order_id)
    {
        $field = ["name","cs","qx","xiaoqu","mianji","yusuan"];
        $list = D("Home/Orders")->getOrderField($order_id,$field);
        foreach ($list as $key => $value) {
            $list[$key]["time"] = empty($value["time"])?"--":date("Y-m-d H:i:s",$value["time"]);
        }
        return $list;
    }

    public function updateFieldStat($order_id,$field,$data)
    {
        return D("Home/Orders")->editOrderField($order_id,$field,$data);
    }

    public function getCustomerLfStat($id,$group,$manager,$begin,$end)
    {
        $month_start = mktime(00,00,00,date("m"),1,date("Y"));
        $month_end =   mktime(23,59,59,date("m"),date("t"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $month_start = strtotime($begin);
            $month_end =   strtotime($end)+86400-1;
        }
        $all = [];
        //1.获取客服数据
        $users = D("Adminuser")->getKfLoginDayByPool($id,$group,$manager,$month_start,$month_end);

        //获取发单量/实际分单量
        $result = D("OrderPool")->getKfOrdereffective($id,$group,$manager,$month_start,$month_end);

        foreach ($result as $key => $value) {
            $orders[$value["op_uid"]] = array(
                "all" => $value["all"],
                "fen" => $value["fen"],
                "zen" => $value["zen"]
            );
        }

        //获取量房数据
        $result = D('OrderPool')->getCustomerLfList($month_start,$month_end);

        foreach ($result as $key => $value) {
            $lfList[$value["user_id"]] = array(
                "fen_lf_count" => $value["fen_lf_count"],
                "fen_un_lf_count" => $value["fen_un_lf_count"],
                "zen_lf_count" => $value["zen_lf_count"],
                "zen_un_lf_count" => $value["zen_un_lf_count"]
            );
        }

        //合并数据
        foreach ($users as $key => $value) {
            $value["kfmanager"] = str_replace(",", "", $value["kfmanager"]);
            //统计人
            $users[$key]["all"] = $orders[$value["id"]]["all"];
            $users[$key]["fen"] = $orders[$value["id"]]["fen"];
            $users[$key]["zen"] = $orders[$value["id"]]["zen"];
            $users[$key]["fen_lf_count"] = $lfList[$value["id"]]["fen_lf_count"];
            $users[$key]["fen_un_lf_count"] = $lfList[$value["id"]]["fen_un_lf_count"];
            $users[$key]["zen_lf_count"] = $lfList[$value["id"]]["zen_lf_count"];
            $users[$key]["zen_un_lf_count"] = $lfList[$value["id"]]["zen_un_lf_count"];

            //按组
            $groups[$value["kfgroup"]]["name"] = $value["groupmanager"];
            $groups[$value["kfgroup"]]["manager"] = $value["manager"];
            $groups[$value["kfgroup"]]["all"] += $orders[$value["id"]]["all"];
            $groups[$value["kfgroup"]]["fen"] += $orders[$value["id"]]["fen"];
            $groups[$value["kfgroup"]]["zen"] += $orders[$value["id"]]["zen"];
            $groups[$value["kfgroup"]]["fen_lf_count"] += $lfList[$value["id"]]["fen_lf_count"];
            $groups[$value["kfgroup"]]["fen_un_lf_count"] += $lfList[$value["id"]]["fen_un_lf_count"];
            $groups[$value["kfgroup"]]["zen_lf_count"] += $lfList[$value["id"]]["zen_lf_count"];
            $groups[$value["kfgroup"]]["zen_un_lf_count"] += $lfList[$value["id"]]["zen_un_lf_count"];

            //按师
            $managers[$value["manager"]]["child"][$value["kfgroup"]]["name"] = $value["kfgroup"];
            $managers[$value["manager"]]["child"][$value["kfgroup"]]["all"] += $orders[$value["id"]]["all"];
            $managers[$value["manager"]]["child"][$value["kfgroup"]]["fen"] += $orders[$value["id"]]["fen"];
            $managers[$value["manager"]]["child"][$value["kfgroup"]]["zen"] += $orders[$value["id"]]["zen"];
            $managers[$value["manager"]]["child"][$value["kfgroup"]]["fen_lf_count"] += $lfList[$value["id"]]["fen_lf_count"];
            $managers[$value["manager"]]["child"][$value["kfgroup"]]["fen_un_lf_count"] += $lfList[$value["id"]]["fen_un_lf_count"];
            $managers[$value["manager"]]["child"][$value["kfgroup"]]["zen_lf_count"] += $lfList[$value["id"]]["zen_lf_count"];
            $managers[$value["manager"]]["child"][$value["kfgroup"]]["zen_un_lf_count"] += $lfList[$value["id"]]["zen_un_lf_count"];

            $managers[$value["manager"]]["name"] = $value["manager"];
            $managers[$value["manager"]]["all"] += $orders[$value["id"]]["all"];
            $managers[$value["manager"]]["fen"] += $orders[$value["id"]]["fen"];
            $managers[$value["manager"]]["zen"] += $orders[$value["id"]]["zen"];
            $managers[$value["manager"]]["fen_lf_count"] += $lfList[$value["id"]]["fen_lf_count"];
            $managers[$value["manager"]]["fen_un_lf_count"] += $lfList[$value["id"]]["fen_un_lf_count"];
            $managers[$value["manager"]]["zen_lf_count"] += $lfList[$value["id"]]["zen_lf_count"];
            $managers[$value["manager"]]["zen_un_lf_count"] += $lfList[$value["id"]]["zen_un_lf_count"];

            //总计
            $all["all"] += $orders[$value["id"]]["all"];
            $all["fen"] += $orders[$value["id"]]["fen"];
            $all["zen"] += $orders[$value["id"]]["zen"];
            $all["fen_lf_count"] += $lfList[$value["id"]]["fen_lf_count"];
            $all["fen_un_lf_count"] += $lfList[$value["id"]]["fen_un_lf_count"];
            $all["zen_lf_count"] += $lfList[$value["id"]]["zen_lf_count"];
            $all["zen_un_lf_count"] += $lfList[$value["id"]]["zen_un_lf_count"];
        }
        return array("users"=>$users,"group" => $groups,"manager" => $managers,"all" => $all);
    }

    public function getOrderIpDetails($order_id)
    {
        $model = new OrdersModel();
        $list = $model->getOrderIpDetails($order_id);
        foreach ($list as $key => $value) {
            $value["type"] = "装修订单";
            $list[$key] = $value;
        }
    }

   /**
    * 获取城市订单数据
    * @param  [type] $city    [城市数据数组]
    * @param  [type] $date    [日期]
    * @return [type]          [description]
    */
    public function getAllCityOrderList($city,$date)
    {
        $time = strtotime($date);
        $monthStart =  strtotime("-1 day", mktime(12,0,0,date("m",$time),date("1",$time),date("Y",$time)));
        $monthEnd = mktime(11,59,59,date("m",$time),date("d",$time),date("Y",$time));
        $result = D("Home/Orders")->getAllCityOrderList($monthStart, $monthEnd);

        foreach ($result as $key => $value) {
            if (array_key_exists($value["city_id"],$city)) {
                $city[$value["city_id"]]["all_count"] = $value["all_count"];
                $city[$value["city_id"]]["fen_count"] = $value["fen_count"];
            }
        }
        return $city;
    }

    public function getAllCityRealOrderList($city,$date)
    {
        $time = strtotime($date);
        $monthStart =  mktime(0,0,0,date("m",$time),date("1",$time),date("Y",$time));
        $monthEnd = mktime(23,59,59,date("m",$time),date("d",$time),date("Y",$time));
        $result = D("Home/Orders")->getAllCityRealOrderList($monthStart, $monthEnd);

        foreach ($result as $key => $value) {
            if (array_key_exists($value["city_id"],$city)) {
                $city[$value["city_id"]]["real_fen_count"] = $value["fen_count"];
            }
        }
        return $city;
    }

    public function getCityOrderByAllSource($city_id,$date,$begin,$end,$group,$src,$dept)
    {
        $time = strtotime($date);
        $monthStart =  strtotime("-1 day", mktime(12,00,0,date("m",$time),1,date("Y",$time)));
        $monthEnd = mktime(11,59,59,date("m",$time),date("d",$time),date("Y",$time));

        if (!empty($begin) && !empty($end)) {
            $begin = strtotime($begin);
            $monthStart =  strtotime("-1 day", mktime(12,00,0,date("m",$begin),date("d",$begin),date("Y",$begin)));
            $end = strtotime($end);
            $monthEnd = mktime(11,59,59,date("m",$end),date("d",$end),date("Y",$end));
        }

        //分单
        $result = D("Home/Orders")->getCityOrderByAllSource($city_id,$monthStart,$monthEnd,$group,$src,$dept);

        foreach ($result as $key => $value) {
            $list["cname"] = $value["cname"];
            if (empty($value["src"])) {
               $value["source_name"] = "自然流量";
               $value["src"] = "normal";
            }
            $value["fen_rate"] = setInfNanToN(round(($value["fen_count"]/$value["all_count"])*100,2));
            $list["list"][$value["src"]] = $value;
            //订单汇总
            $list["all"]["all_count"] += $value["all_count"];
            $list["all"]["fen_count"] += $value["fen_count"];
            $list["all"]["yx_count"] += $value["yx_count"];
            $list["all"]["fen_rate"] = setInfNanToN(round(($list["all"]["fen_count"]/$list["all"]["all_count"])*100,2));
        }

        //实际分单
        $result = D("Home/Orders")->getCityRealOrderByAllSource($city_id,$monthStart,$monthEnd,$group,$src);

        foreach ($result as $key => $value) {
            if (empty($value["src"])) {
                $value["src"] = "normal";
                $value["source_name"] = "自然流量";
            }

            if (!array_key_exists($value["src"], $list["list"])) {
                $list["list"][$value["src"]]["src"] = $value["src"];
                $list["list"][$value["src"]]["source_name"] = $value["source_name"];
                $list["list"][$value["src"]]["group_id"] = $value["group_id"];
                $list["list"][$value["src"]]["group_name"] = $value["group_name"];
            }
            $list["list"][$value["src"]]["real_yx_count"] = $value["yx_count"];
            $list["list"][$value["src"]]["real_fen_count"] = $value["fen_count"];
            $list["list"][$value["src"]]["real_fen_rate"] = setInfNanToN(round(($value["fen_count"]/$list["list"][$value["src"]]["all_count"])*100,2));

            //订单汇总
            $list["all"]["real_yx_count"] += $value["yx_count"];
            $list["all"]["real_fen_count"] += $value["fen_count"];
            $list["all"]["real_fen_rate"] = setInfNanToN(round(($list["all"]["real_fen_count"]/$list["all"]["all_count"])*100,2));
        }

        return $list;
    }

    public function getOrderTelDetails($order_id)
    {
        $model = new OrdersModel();
        $list = $model->getOrderTelDetails($order_id);
        foreach ($list as $key => $value) {
            $value["type"] = "装修订单";
            $list[$key] = $value;
        }
    }

    public function getCityAllSource($city_id,$date,$begin,$end)
    {
        $time = strtotime($date);
        $monthStart =  strtotime("-1 day", mktime(12,00,0,date("m",$time),1,date("Y",$time)));
        $monthEnd = mktime(11,59,59,date("m",$time),date("d",$time),date("Y",$time));

        if (!empty($begin) && !empty($end)) {
            $begin = strtotime($begin);
            $monthStart =  strtotime("-1 day", mktime(12,00,0,date("m",$begin),date("d",$begin),date("Y",$begin)));
            $end = strtotime($end);
            $monthEnd = mktime(11,59,59,date("m",$end),date("d",$end),date("Y",$end));
        }

        $result = D("Home/Orders")->getCityAllSource($city_id,$monthStart,$monthEnd);

        foreach ($result as $key => $value) {
            $list["group"][$value["groupid"]]["name"] = $value["name"];
            $list["group"][$value["groupid"]]["id"] = $value["groupid"];
            $list["src"][$value["src"]] = $value;
        }

        $result2 = D("Home/Orders")->getCityRealOrderByAllSource($city_id, $monthStart, $monthEnd);
        if (count($result2) > 0) {
            foreach ($result2 as $key => $value) {
                if ($value["group_id"] && !array_key_exists($value["group_id"], $list["group"])) {
                    $list["group"][$value["group_id"]]["id"] = $value["group_id"];
                    $list["group"][$value["group_id"]]["name"] = $value["group_name"];
                }

                if ($value["src"] && !array_key_exists($value["src"], $list["src"])) {
                    $list["src"][$value["src"]]["src"] = $value["src"];
                    $list["src"][$value["src"]]["source_name"] = $value["source_name"];
                    $list["src"][$value["src"]]["groupid"] = $value["group_id"];
                    $list["src"][$value["src"]]["name"] = $value["group_name"];
                }
            }
        }

        return $list;
    }

    /**
     * 获取客服装修订单信息
     */
    public function getZhuangxiuOrders($where)
    {
        //默认30天数据
        if (empty($where['start']) && empty($where['end'])) {
            $where['start'] = date("Y-m-d", strtotime("-30 day"));
            $where['end'] = date('Y-m-d 23:59:59');
        }

        $model = new OrdersModel();

        $result = $model->getZhuangxiuOrder($where);
        $total['fem_count'] = 0;
        $total['zeng_count'] = 0;

        if (count($result) > 0) {
            foreach ($result as $key => $val) {
                $result[$key]['zonghe'] = $val['fen_count'] + $val['zeng_count'] / 10;
                $total['fen_count'] += $val['fen_count'];
                $total['zeng_count'] += $val['zeng_count'];
            }
            $total['zonghe'] = $total['fen_count'] + $total['zeng_count'] / 10;
            return ['list' => $result, 'total' => $total];
        }
    }

    //获取分赠单统计的总数量
    public function getOrdersListCount($map)
    {
        $model = new OrderInfoModel();
        $getreturn = $model->getOrdersListCount($map);
        return count($getreturn);
    }

    //获取分赠单统计
    public function getOrdersList($map, $pageIndex, $pageCount)
    {
        $model = new OrderInfoModel();
        return $model->getOrdersList($map, $pageIndex, $pageCount);
    }

    /**
     * 根据条件获取分/增单列表
     *
     * @param string $comid
     * @param int $startime
     * @param int $endtime
     * @param string $orderid
     * @param int $fz_type
     * @param string $xiaoqu
     * @param int $fs
     * @param int $lx
     * @param string $cs
     * @return mixed
     */
    public function getFenOrZengOrdersList($comid = '', $startime = 0, $endtime = 0, $orderid = '', $fz_type = 0, $xiaoqu = '', $fs = 0, $lx = 0, $cs = '', $lf_status = 0, $kefu_id = 0, $order_lftime = 0)
    {
        $map['i.addtime'] = array('BETWEEN', array($startime, $endtime + 86400 - 1));
        if ($comid) {
            $map['i.com'] = $comid;
        }
        if ($orderid) {
            $map['i.order'] = $orderid;
        }
        if ($fz_type) {
            if ($fz_type == 3) {
                $map['i.type_fw'] = 1;
                $map['i.allot_source'] = 3;
            } else if ($fz_type == 1) {
                $map['i.type_fw'] = 1;
                $map['i.allot_source'] = ['neq', 3];
            } else {
                $map['i.type_fw'] = $fz_type;
            }
        }
        if ($xiaoqu) {
            $map['o.xiaoqu'] = ['like','%'.$xiaoqu.'%'];
        }
        if ($lx) {
            $map['o.lx'] = $lx;
        }
        if ($fs) {
            $map['o.fangshi'] = $fs;
        }

        // 管辖城市
        if (!in_array(session('uc_userinfo.uid'),getAllCityManager())) {
            $citys = getMyCityIds();
            if (empty($citys)) {
                return ['list' => [], 'page' => ''];
            }

            if (empty($cs)) {
                $map['o.cs'] = array("in", $citys);
            } else if (in_array($cs, $citys)) {
                $map['o.cs'] = $cs;
            } else {
                return ['list' => [], 'page' => ''];
            }
        } else if ($cs) {
            $map['o.cs'] = $cs;
        }

        // 客服(订单归属人)
        if (!in_array(session('uc_userinfo.uid'), [1, 33, 63])) {
            $adminuserLogic = new AdminuserLogicModel();
            $userIds = $adminuserLogic->getChildrenIds(session('uc_userinfo.uid'));
            $userIds[] = session('uc_userinfo.id');

            if (empty($kefu_id)) {
                $map['p.op_uid'] = array("in", $userIds);
            } else if (in_array($kefu_id, $userIds)) {
                $map['p.op_uid'] = $kefu_id;
            } else {
                return ['list' => [], 'page' => ''];
            }
        } else if ($kefu_id) {
            $map["p.op_uid"] = array("EQ", $kefu_id);
        }

        $map2 = [];
        if ($lf_status) {
            $lf_status = $lf_status == 3 ? 0 : $lf_status;
            $map2['t.liangfang_status'] = array("EQ", $lf_status);
        }

        $model = new OrderInfoModel();
        $count = $model->getFenOrZengOrdersListCount($map, $map2);
        if ($count) {
            //分页
            import('Library.Org.Util.Page');
            $page = new \Page($count, 20);
            $show = $page->show();
            $list = $model->getFenOrZengOrdersList($map, $map2, $page->firstRow, $page->listRows, $order_lftime);
        }
        $return['list'] = isset($list) ? $list : [];
        $return['page'] = isset($show) ? $show : '';
        return $return;
    }

    //根据公司id获取公司的签单信息
    public function getQianDanInfoByComid($comid,$startime,$endtime)
    {
        $ordermodel = new OrdersModel();
        if(empty($comid)){
            $return['qiandancount'] = 0;
            $return['qiandanjine'] = '';
        }else{
            $info = $ordermodel->getQianDanInfoByComid($comid,$startime,$endtime);
            $return['qiandancount'] =$info['qiandancount'];
            $return['qiandanjine'] = round($info['qiandanjine'],2);
        }
        return $return;
    }

    //根据订单id获取订单信息
    public function getOrderInfoById($orderid)
    {
        $model = new OrderInfoModel();
        $orderinfo = $model->getOrderInfoById($orderid);    //订单信息
        if (!$orderinfo) {
            return [];
        }
        $fpCom = $model->getAllotComByOrderid(['o.id' => $orderid, 'i.allot_source' => ['in', [1, 2]]]);     //手动+自动分单公司列表
        $robCom = $model->getAllotComByOrderid(['o.id' => $orderid, 'i.allot_source' => ['eq', 3]]);         //抢单公司列表
        $lfCom = $model->getAllLFComByOrderid(['o.id' => $orderid]);         //量房公司列表

        $orderinfo['time_real'] = $orderinfo['time_real'] ? date('Y-m-d H:i:s',$orderinfo['time_real']) : '';
        $return['orderinfo'] = $orderinfo;
        $return['comlist'] = $fpCom;
        $return['robcoms'] = $robCom;
        $return['lfcom'] = $lfCom;
        return $return;
    }

    //会员分单统计导出
    public function exportOrderTongji($list)
    {
        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            $writer = new \XLSXWriter();
            //写入日期
            $writer->writeSheetRow('Sheet1', array('日期：' . date('Y-m-d H:i',time())));
            //标题
            $herder = array(
                '序号',
                '城市',
                '公司名称',
                '会员状态',
                '分单',
                '抢单',
                '赠单',
                '签单',
                '签单金额',
            );
            $writer->writeSheetRow('Sheet1', $herder);
            //数据
            $jsq = 1;
            foreach ($list as $key => $value) {
                if($value['on'] == 2){
                    $value['on'] = '会员';
                }elseif($value['on'] == -1){
                    $value['on'] = '过期会员';
                }elseif($value['on'] == -4){
                    $value['on'] = '暂停会员';
                }else{
                    $value['on']='';
                }
                if(!$value['qiandancount']){
                    $value['qiandancount'] = '';
                }
                if(!$value['qiandanjine']){
                    $value['qiandanjine'] = '';
                }
                $v = array(
                    $jsq,
                    $value['cname'],
                    $value['jc'],
                    $value['on'],
                    $value['cntf'],
                    $value['cntqf'],
                    $value['cntw'],
                    $value['qiandancount'],
                    $value['qiandanjine'],
                );
                $writer->writeSheetRow('Sheet1', $v);
                $jsq++;
            }
            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="会员分单统计.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut();
        } catch (\Exception $e) {
            echo 'something error';
        }
    }

    /**
     * 会员分单明细导出
     *
     * @param $list
     * @throws \Exception
     */
    public function exportOrderMingxi($list)
    {
        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            $writer = new \XLSXWriter();
            //写入日期
            $writer->writeSheetRow('Sheet1', ['日期：' . date('Y-m-d H:i', time())]);
            //标题
            $herder = [
                '订单号',
                '业主姓名',
                '城市',
                '区域',
                '预算',
                '面积',
                '装修类型',
                '装修方式',
                '量房时间',
                '装修公司',
                '小区',
                '分/赠单',
                '分单时间',
                '量房状态',
                '量房时间',
                '签单时间',
                '签单金额（万）',
                '是否量房',
                '订单归属人'
            ];
            $writer->writeSheetRow('Sheet1', $herder);
            //数据
            foreach ($list as $key => $value) {
                if ($value['type_fw'] == 1 && $value['allot_source'] != 3) {
                    $value['type_fw'] = '分单';
                } elseif ($value['type_fw'] == 1 && $value['allot_source'] == 3) {
                    $value['type_fw'] = '分单（抢）';
                } elseif ($value['type_fw'] == 2) {
                    $value['type_fw'] = '赠单';
                } else {
                    $value['type_fw'] = '';
                }

                $value['addtime'] = date('Y-m-d', $value['addtime']);
                if ($value['qiandan_companyid'] == $value['comid']) {
                    $value['qiandan_addtime'] = date('Y-m-d', $value['qiandan_addtime']);
                } else {
                    $value['qiandan_addtime'] = '—';
                    $value['qiandan_jine'] = '';
                }
                if ($value['liangfang_status'] == 0){
                    $liangfang_status = " 未知";
                    $is_liangfang = "否";
                }elseif($value['liangfang_status'] == 1){
                    $liangfang_status = " 未量房";
                    $is_liangfang = "否";
                }elseif($value['liangfang_status'] == 2){
                    $liangfang_status = "已量房";
                    $is_liangfang = "是";
                }else{
                    $liangfang_status = "未知";
                    $is_liangfang = "否";
                }

                $liangfang_time = "--";
                if ($value["lf_time"]) {
                    $liangfang_time = date("Y-m-d", $value["lf_time"]);
                }

                $hang = [
                    $value['order']? $value['order'].' ' : '',
                    $value['name']? : '' . $value['sex'] ? : '',
                    $value['cname']? : '',
                    $value['qz_area']? : '',
                    $value['yusuan']? : '',
                    $value['mianji']? : '',
                    $value['lx'] == 1 ? '家装' :($value['lx']  == 2 ? '公装' : ''),
                    $value['fangshi']? : '',
                    $value['lftime']? : '',
                    $value['jc']? : '',
                    $value['xiaoqu']? : '',
                    $value['type_fw'],
                    $value['addtime'],
                    $liangfang_status,
                    $liangfang_time,
                    $value['qiandan_addtime'],
                    $value['qiandan_jine'],
                    $is_liangfang,
                    $value["op_name"],
                ];
                $writer->writeSheetRow('Sheet1', $hang);
            }
            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="会员分单明细.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut();
        } catch (\Exception $e) {
            echo 'something error';
        }
    }

    public function saveOrderAllotNum($order_id, $num)
    {
        $orderDb = new OrdersModel();
        $info = $orderDb->getOrderAllotNum($order_id);
        if (!$info) {
            $save = [
                'order_id' => $order_id,
                'allot_num' => (int)$num,
            ];
            return $orderDb->saveOrderAllotNum($save);
        } else {
            $save = [
                'allot_num' => (int)$num,
            ];
            $map = [
                'order_id' => ['eq', $order_id]
            ];
            return $orderDb->updateOrderAllotNum($map, $save);
        }
    }

    /**
     * 获取未接通订单分页列表
     *
     * @return array
     */
    public function getOrderUncallList($cs, $user, $start, $end)
    {
        $poolmap = [];
        $ordermap = ['remarks' => ['EQ', '未接通']];

        if (!empty($cs)) {
            $ordermap['cs'] = ['EQ', $cs];
        }
        if (!empty($user)) {
            $poolmap['p.op_uid'] = ['EQ', $user];
        }
        if (empty($start) && empty($end)) {
            $ordermap['time'] = ['BETWEEN', [strtotime('-90 day', strtotime('tomorrow')), strtotime('tomorrow')]];
        } else {
            if (!empty($start) && !empty($end)) {
                $ordermap['time'] = ['BETWEEN', [strtotime($start), strtotime($end . ' 23:59:59')]];
            } elseif (!empty($end)) {
                $ordermap['time'] = ['BETWEEN', [strtotime('-90 day', strtotime($end . ' 23:59:59')), strtotime($end . ' 23:59:59')]];
            } else {
                $ordermap['time'] = ['BETWEEN', [strtotime($start), strtotime('+90 day', strtotime($start))]];
            }
        }

        $orderModel = new OrdersModel();
        $count = $orderModel->getOrderUncallCount($ordermap, $poolmap);

        if ($count) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $show = $p->show();
            $list = $orderModel->getOrderUncallList($ordermap, $poolmap, $p->nowPage, $p->listRows);
        }
        return ['page' => isset($show) ? $show : '', 'list' => isset($list) ? $list : []];
    }

    /**
     * 获取未接通订单分页列表
     *
     * @return array
     */
    public function getOrderUncallListNoPage($cs, $user, $start, $end)
    {
        $poolmap = [];
        $ordermap = ['remarks' => ['EQ', '未接通']];

        if (!empty($cs)) {
            $ordermap['cs'] = ['EQ', $cs];
        }
        if (!empty($user)) {
            $poolmap['p.op_uid'] = ['EQ', $user];
        }
        if (empty($start) && empty($end)) {
            $ordermap['time'] = ['BETWEEN', [strtotime('-90 day', strtotime('tomorrow')), strtotime('tomorrow')]];
        } else {
            if (!empty($start) && !empty($end)) {
                $ordermap['time'] = ['BETWEEN', [strtotime($start), strtotime($end . ' 23:59:59')]];
            } elseif (!empty($end)) {
                $ordermap['time'] = ['BETWEEN', [strtotime('-90 day', strtotime($end . ' 23:59:59')), strtotime($end . ' 23:59:59')]];
            } else {
                $ordermap['time'] = ['BETWEEN', [strtotime($start), strtotime('+90 day', strtotime($start))]];
            }
        }

        $orderModel = new OrdersModel();
        return $orderModel->getOrderUncallListNoPage($ordermap, $poolmap);
    }

    /**
     * 根据公司id列表获取device_token
     *
     * @param $comidlist
     * @return array
     */
    public function getDeviceToken($comidlist)
    {
        if (!empty($comidlist)) {
            $model = new UmengRecordModel();
            $getlist = $model->getDeviceTokenByComidlist($comidlist);
            if (!empty($getlist)) {
                $list = array();
                foreach ($getlist as $key => $val) {
                    $list[$key] = $val['device_token'];
                }
            } else {
                $list = array();
            }
        } else {
            $list = array();
        }
        return $list;
    }


    /**
     * 发送友盟消息
     *
     * @param $data
     * @param $orderid
     * @param $type
     * @param $alert
     * @return void
     */
    public function sendUmeng($data, $orderid, $type, $alert, $comid)
    {
        $model = new UmengRecordModel();
        $orderinfomodel = new \Home\Model\Db\OrderInfoModel();
        $appkey_ios = C('umeng_appkey_ios');
        $secret_ios = C('ument_appMasterSecret_ios');

        $appkey_android = C('umeng_appkey_android');
        $secret_android = C('ument_appMasterSecret_android');
        $activity_android = C("umeng_activity_android");

        import('Library.Org.Umeng.Umeng', "", ".php");

        $getlist = $model->getDeviceTokenByComidlist($comid);   //获取devicetokenlist

        if (!empty($data)) {
            $comorder = $orderinfomodel->getCompanyNotReadOrder($comid);
            if($getlist){
                foreach ($getlist as $key => $val)
                {
                    $notreadcount = 0;
                    foreach($comorder as $k => $v){
                        if($val['comid'] == $v['com']){
                            $notreadcount = $v['ordercount'];
                            break;
                        }
                    }
                    if($notreadcount){
                        //ios推送
                        $send = new Umeng($appkey_ios, $secret_ios);
                        $send->sendIOSListcast($val['device_token'], $orderid, $type, $alert, $notreadcount);

                        //android  推送
                        $send2 = new Umeng($appkey_android, $secret_android);
                        $send2->sendAndroidListcast($val['device_token'], $orderid, $type, '您有新的装修订单，立即查看~', $alert, '您有新的装修订单，立即查看~', 'go_app', $notreadcount, true, $activity_android);
                    }
                }

            }
        }
    }


    /**
     * 客服新分单统计
     *
     * @param $id
     * @param $group
     * @param $manager
     * @param $begin
     * @param $end
     * @return array
     */
    public function getCustomerOrdereffectiveList($id, $group, $manager, $begin, $end)
    {
        $adminModel = new AdminuserModel();
        // 获取客服登录天数
        $result = $adminModel->getKfLoginDayByPool($id, $group, $manager, $begin, $end);
        $kfList = array_column($result,null,'id');

        $poolModel = new OrderPoolModel();
        //获取客服的发单量
        $result = $poolModel->getKfOrdereffective($id, $group, $manager, $begin, $end);
        $list = array_column($result,null,'op_uid');

        $managers =  $groups = [];
        foreach ($kfList as $key => $value) {
            $value['kfmanager'] = str_replace(',', '', $value['kfmanager']);

            $kfList[$value['id']]['all'] = $list[$value['id']]['all'];
            $kfList[$value['id']]['fen'] = $list[$value['id']]['fen'];
            $kfList[$value['id']]['zen'] = $list[$value['id']]['zen'];
            $kfList[$value['id']]['fen_zen'] = $list[$value['id']]['fen'] + $list[$value['id']]['zen'] / 10;
            $kfList[$value['id']]['zentofen'] = $list[$value['id']]['zentofen'];
            $kfList[$value['id']]['fen_rate'] = setInfNanToN(round($list[$value['id']]['fen'] / $list[$value['id']]['all'], 4)) * 100;
            $kfList[$value['id']]['fen_zen_rate'] = setInfNanToN(round($kfList[$value['id']]['fen_zen'] / $list[$value['id']]['all'], 4)) * 100;
            $kfList[$value['id']]['yx_all'] = $list[$value['id']]['fen'] + $list[$value['id']]['zen'];
            $kfList[$value['id']]['yx'] = setInfNanToN(round($kfList[$value['id']]['yx_all'] / $list[$value['id']]['all'],4 )) * 100;
            $kfList[$value['id']]['fen_yx'] = setInfNanToN(round($list[$value['id']]['fen'] /$kfList[$value['id']]['yx_all'],4)) * 100;
            $kfList[$value['id']]['zen_yx'] = setInfNanToN(round($list[$value['id']]['zen'] / $kfList[$value['id']]['yx_all'],4)) * 100;

            //组统计
            $groups[$value['kfgroup']]['groupmanager'] = $value['groupmanager'];
            $groups[$value['kfgroup']]['kfgroup'] = $value['kfgroup'];
            $groups[$value['kfgroup']]['count']++;
            $groups[$value['kfgroup']]['manager'] = $value['manager'];
            $groups[$value['kfgroup']]['day'] += $value['day'];
            $groups[$value['kfgroup']]['all'] += $kfList[$value['id']]['all'];
            $groups[$value['kfgroup']]['fen'] += $kfList[$value['id']]['fen'];
            $groups[$value['kfgroup']]['zen'] += $kfList[$value['id']]['zen'];
            $groups[$value['kfgroup']]['zentofen'] += $kfList[$value['id']]['zentofen'];
            $groups[$value['kfgroup']]['fen_zen'] += $kfList[$value['id']]['fen_zen'];
            $groups[$value['kfgroup']]['fen_rate'] = setInfNanToN(round($groups[$value['kfgroup']]['fen'] / $groups[$value['kfgroup']]['all'], 4)) * 100;
            $groups[$value['kfgroup']]['fen_zen_rate'] = setInfNanToN(round($groups[$value['kfgroup']]['fen_zen'] / $groups[$value['kfgroup']]['all'], 4)) * 100;
            $groups[$value['kfgroup']]['yx_all'] += $list[$value['id']]['fen'] + $list[$value['id']]['zen'];
            $groups[$value['kfgroup']]['yx'] = setInfNanToN(round($groups[$value['kfgroup']]['yx_all'] / $groups[$value['kfgroup']]['all'],4)) * 100;
            $groups[$value['kfgroup']]['fen_yx'] = setInfNanToN(round( $groups[$value['kfgroup']]['fen'] / $groups[$value['kfgroup']]['yx_all'],4)) * 100;
            $groups[$value['kfgroup']]['zen_yx'] = setInfNanToN(round( $groups[$value['kfgroup']]['zen'] / $groups[$value['kfgroup']]['yx_all'],4)) * 100;



            //按师统计
            $managers[$value['kfmanager']]['manager'] = $value['manager'];
            $managers[$value['kfmanager']]['count']++;
            $managers[$value['kfmanager']]['day'] += $value['day'];
            $managers[$value['kfmanager']]['all'] += $kfList[$value['id']]['all'];
            $managers[$value['kfmanager']]['fen'] += $kfList[$value['id']]['fen'];
            $managers[$value['kfmanager']]['zen'] += $kfList[$value['id']]['zen'];
            $managers[$value['kfmanager']]['zentofen'] += $kfList[$value['id']]['zentofen'];
            $managers[$value['kfmanager']]['fen_zen'] += $kfList[$value['id']]['fen_zen'];
            $managers[$value['kfmanager']]['fen_rate'] = setInfNanToN(round($managers[$value['kfmanager']]['fen'] / $managers[$value['kfmanager']]['all'], 4)) * 100;
            $managers[$value['kfmanager']]['fen_zen_rate'] = setInfNanToN(round($managers[$value['kfmanager']]['fen_zen'] / $managers[$value['kfmanager']]['all'], 4)) * 100;

            $managers[$value['kfmanager']]['yx_all'] += $list[$value['id']]['fen'] + $list[$value['id']]['zen'];
            $managers[$value['kfmanager']]['yx'] = setInfNanToN(round($managers[$value['kfmanager']]['yx_all']  /  $managers[$value['kfmanager']]['all'],4)) * 100;
            $managers[$value['kfmanager']]['fen_yx'] = setInfNanToN(round($managers[$value['kfmanager']]['fen'] / $managers[$value['kfmanager']]['yx_all'],4)) * 100;
            $managers[$value['kfmanager']]['zen_yx'] = setInfNanToN(round($managers[$value['kfmanager']]['zen'] / $managers[$value['kfmanager']]['yx_all'],4)) * 100;

            $managers[$value['kfmanager']]['child'][$value['kfgroup']] = $groups[$value['kfgroup']];


        }

        if (I("get.debug") == "d1") {
            dump($groups);
        }

        $edition = [];
        //按组统计根据分单率排序
        foreach ($groups as $item) {
            $edition[] = +$item["fen_rate"];
        }
        array_multisort($edition, SORT_DESC,SORT_NUMERIC, $groups);

        if (I("get.debug") == "d2") {
            dump($groups);
        }

        $total = $center = $dayList = [];

        //总计，直接从按师统计里获取
        foreach ($managers as $key => $value) {
            $total['all'] = $total['all'] + $value['all'];
            $total['fen'] = $total['fen'] + $value['fen'];
            $total['zen'] = $total['zen'] + $value['zen'];
            $total['zentofen'] = $total['zentofen'] + $value['zentofen'];
        }
        $total['fen_zen'] = $total['fen'] + $total['zen'] / 10;
        $total['fen_rate'] = setInfNanToN(round($total['fen'] / $total['all'], 4)) * 100;
        $total['fen_zen_rate'] = setInfNanToN(round($total['fen_zen'] / $total['all'], 4)) * 100;

        $total['yx_all'] = $total['fen'] + $total['zen'];
        $total['yx'] = setInfNanToN(round($total['yx_all']  /  $total['all'],4)) * 100;
        $total['fen_yx'] = setInfNanToN(round($total['fen'] / $total['yx_all'],4)) * 100;
        $total['zen_yx'] = setInfNanToN(round($total['zen'] / $total['yx_all'],4)) * 100;

        //按日统计
        if (!empty($id) || !empty($group) || !empty($manager)) {
            $result = $poolModel->getKfOrdereffectiveByDay($id, $group, $manager, $begin, $end);

            foreach ($result as $key => $value) {
                $dayList['child'][$key]['key'] = $value['op_uid'];
                $dayList['child'][$key]['op_name'] = $value['op_name'];
                if (!empty($group)) {
                    $dayList['child'][$key]['key'] = $value['kfgroup'];
                    $dayList['child'][$key]['op_name'] = $value['group_name'];
                } elseif (!empty($manager)) {
                    $value['kfmanager'] = str_replace(',', '', $value['kfmanager']);
                    $dayList['child'][$key]['key'] = $value['kfmanager'];
                    $dayList['child'][$key]['op_name'] = $value['name'];
                }
                $dayList['child'][$key]['date'] = $value['date'];
                $dayList['child'][$key]['fen'] = $value['fen'];
                $dayList['child'][$key]['zen'] = $value['zen'];
                $dayList['child'][$key]['zentofen'] = $value['zentofen'];
                $dayList['child'][$key]['all'] = $value['all'];
                $dayList['child'][$key]['fen_rate'] = setInfNanToN(round($value['fen'] / $value['all'], 4)) * 100;
                $dayList['child'][$key]['fen_zen'] = $value['fen'] + $value['zen'] / 10;
                $dayList['child'][$key]['fen_zen_rate'] = setInfNanToN(round($dayList['child'][$key]['fen_zen'] / $value['all'], 4)) * 100;

                $dayList['child'][$key]['yx_all'] = $value['fen'] +  $value['zen'];
                $dayList['child'][$key]['yx'] =  setInfNanToN(round($dayList['child'][$key]['yx_all'] / $value['all'],2)) * 100;
                $dayList['child'][$key]['fen_yx'] = setInfNanToN(round($dayList['child'][$key]['fen'] / $dayList['child'][$key]['yx_all'],4)) * 100;
                $dayList['child'][$key]['zen_yx'] = setInfNanToN(round($dayList['child'][$key]['zen'] / $dayList['child'][$key]['yx_all'],4)) * 100;

                $dayList['all']['fen'] += $value['fen'];
                $dayList['all']['zen'] += $value['zen'];
                $dayList['all']['zentofen'] += $value['zentofen'];
                $dayList['all']['all'] += $value['all'];
                $dayList['all']['fen_rate'] = setInfNanToN(round($dayList['all']['fen'] / $dayList['all']['all'], 4)) * 100;
                $dayList['all']['fen_zen'] = $dayList['all']['fen'] + $dayList['all']['zen'] / 10;
                $dayList['all']['fen_zen_rate'] = setInfNanToN(round($dayList['all']['fen_zen'] / $dayList['all']['all'], 4)) * 100;
                $dayList['all']['yx_all'] +=  $value['fen'] +  $value['zen'];
                $dayList['all']['yx'] =  setInfNanToN(round($dayList['all']['yx_all'] / $dayList['all']['all'],4)) * 100;
                $dayList['all']['fen_yx'] = setInfNanToN(round($dayList['all']['fen'] / $dayList['all']['yx_all'],4)) * 100;
                $dayList['all']['zen_yx'] = setInfNanToN(round($dayList['all']['zen'] / $dayList['all']['yx_all'],4)) * 100;


            }
        }

        //按中心
        $result = $poolModel->getKfOrdereffectiveByDay('', '', '', $begin, $end);
        foreach ($result as $key => $value) {
            $center['child'][$value['date']]['date'] = $value['date'];
            $center['child'][$value['date']]['fen'] += $value['fen'];
            $center['child'][$value['date']]['zen'] += $value['zen'];
            $center['child'][$value['date']]['zentofen'] += $value['zentofen'];
            $center['child'][$value['date']]['all'] += $value['all'];
            $center['child'][$value['date']]['fen_rate'] = setInfNanToN(round($center['child'][$value['date']]['fen'] / $center['child'][$value['date']]['all'], 4)) * 100;
            $center['child'][$value['date']]['fen_zen'] = $center['child'][$value['date']]['fen'] + $center['child'][$value['date']]['zen'] / 10;
            $center['child'][$value['date']]['fen_zen_rate'] = setInfNanToN(round($center['child'][$value['date']]['fen_zen'] / $center['child'][$value['date']]['all'], 4)) * 100;
            $center['child'][$value['date']]['yx_all'] +=  $value['fen'] + $value['zen'];
            $center['child'][$value['date']]['yx'] =  setInfNanToN(round($center['child'][$value['date']]['yx_all'] / $center['child'][$value['date']]['all'],4)) * 100;
            $center['child'][$value['date']]['fen_yx'] = setInfNanToN(round($center['child'][$value['date']]['fen'] / $center['child'][$value['date']]['yx_all'],4)) * 100;
            $center['child'][$value['date']]['zen_yx'] = setInfNanToN(round($center['child'][$value['date']]['zen'] / $center['child'][$value['date']]['yx_all'],4)) * 100;



            $center['all']['fen'] += $value['fen'];
            $center['all']['zen'] += $value['zen'];
            $center['all']['zentofen'] += $value['zentofen'];
            $center['all']['all'] += $value['all'];
            $center['all']['fen_rate'] = setInfNanToN(round($center['all']['fen'] / $center['all']['all'], 4)) * 100;
            $center['all']['fen_zen'] = $center['all']['fen'] + $center['all']['zen'] / 10;
            $center['all']['fen_zen_rate'] = setInfNanToN(round($center['all']['fen_zen'] / $center['all']['all'], 4)) * 100;
            $center['all']['yx_all'] += $value['fen'] + $value['zen'];
            $center['all']['yx'] =   setInfNanToN(round($center['all']['yx_all'] / $center['all']['all'],4)) * 100;
            $center['all']['fen_yx'] = setInfNanToN(round( $center['all']['fen'] /  $center['all']['yx_all'],4)) * 100;
            $center['all']['zen_yx'] = setInfNanToN(round( $center['all']['zen'] /  $center['all']['yx_all'],4)) * 100;
        }
        array_multisort(array_column($kfList,'fen_zen_rate'), SORT_DESC, $kfList);
        array_multisort(array_column($groups,'fen_zen_rate'), SORT_DESC, $groups);

        return array($kfList, $groups, $managers, $total, $dayList, $center);
    }

    /**
     * 验证订单信息
     *
     * @param $orderInfo
     * @param $status
     * @return bool|string
     */
    public function valiOrder($orderInfo, $status)
    {
        if (empty($orderInfo)) {
            return '该订单不存在!';
        }
        switch ($status) {
            case 2:
                //待定
            case 3:
                //有效未分配
            case 4:
                //分单
            case 6:
                //赠单
            case -99:
                //状态不变
                if ($orderInfo['on'] != 2 || $orderInfo['on_sub'] != 0) {
                    return false;
                }
                //待订单状态不变
                if (empty($orderInfo['name'])) {
                    return '请填写联系人并保存';
                }
                if (empty($orderInfo['mianji'])) {
                    return '请填写面积并保存';
                }
                if (empty($orderInfo['cs']) || empty($orderInfo['qx'])) {
                    return '请选择所属区域并保存';
                }
                if (empty($orderInfo['lx']) || empty($orderInfo['lxs']) ) {
                    return '请选择装修类型并保存';
                }
                if (empty($orderInfo['fangshi']) || empty($orderInfo['yusuan'])) {
                    return '请选择预算并保存';
                }
                if ($status == 2 || ($status == -99 && $orderInfo['on'] == 2 && $orderInfo['on_sub'] == 0)) {
                    //审核为待定单，需要检查是否填写了下次联系时间 如果未填写就不给审核
                    if (empty($orderInfo['visitime'])) {
                        return '请填写下次联系时间后审核为待定单';
                    }
                }
                break;
            default:
                break;
        }
        return false;
    }

    public function checkOrdersInfo($data,$info)
    {
        $status = str_replace('remark_', '', $data['status']);
        //如果选择待定单、分单、赠单时，则检查“联系人”、“所属区域”、“装修类型”、“面积”、“预算”这些必填项
        if ($status == 2 || $status == 4 || $status == 6) {
            $data = $data['check_data'];
            if (empty($data['cs']) || empty($info['cs'])) {
                return ['code' => 400, 'errmsg' => '请选择所属区域并保存！', 'err_field' => 'select[name="cs"]'];
            }
            if (empty($data['qx']) || empty($info['qx'])) {
                return ['code' => 400, 'errmsg' => '请选择所属区域并保存！', 'err_field' => 'select[name="qx"]'];
            }
            if (empty($data['name']) || empty($info['name'])) {
                return ['code' => 400, 'errmsg' => '请填写联系人并保存！', 'err_field' => 'input[name="name"]'];
            }
            if (empty($data['lx']) || empty($info['lx'])) {
                return ['code' => 400, 'errmsg' => '请选择装修类型并保存！', 'err_field' => 'select[name="lx"]'];
            }
//            if(empty($data['lxs']) || empty($info['lxs'])){
//                return ['code' => 400, 'errmsg' => '请选择装修类型并保存！', 'err_field' => 'select[name="lxs"]'];
//            }
            if (empty($data['mianji']) || empty($info['mianji'])) {
                return ['code' => 400, 'errmsg' => '请填写面积并保存！', 'err_field' => 'input[name="mianji"]'];
            }
            if (empty($data['fangshi']) || empty($info['fangshi'])) {
                return ['code' => 400, 'errmsg' => '请选择预算并保存！', 'err_field' => 'select[name="fangshi"]'];
            }
            if (empty($data['yusuan']) || empty($info['yusuan'])) {
                return ['code' => 400, 'errmsg' => '请选择预算并保存！', 'err_field' => 'select[name="yusuan"]'];
            }

            if ($status == 2) {
                if (empty($data['visitime']) || empty($info['visitime'])) {
                    return ['code' => 400, 'errmsg' => '请选择待定时间！', 'err_field' => 'input[name="visitime"]'];
                }
            }
            //分单/赠单不验证量房时间
            if ($status == 6) {
                if (empty($data['lftime']) || empty($info['lftime'])) {
                    return ['code' => 400, 'errmsg' => '请选择量房时间并保存！', 'err_field' => 'select[name="lftime"]'];
                }
            }
        }
        if ($status == 4 || $status == 6) {
            if (empty($data['zuobiao']) || empty($info['jingwei'])) {
                return ['code' => 400, 'errmsg' => '请填写坐标！', 'err_field' => 'input[name="zuobiao"]'];
            }
            //验证回访时间
            if (empty($info['review_time'])) {
                return ['code' => 400, 'errmsg' => '请填回访时间！', 'err_field' => 'input[name="review_time"]'];
            }
        }
        return ['code' => 200];
    }

    public function checkDockingOrdersInfo($data,$info)
    {
        $status = str_replace('remark_','',$data['status']);
        //如果选择分单、赠单时，则检查必填项
        if($status == 4 || $status == 6){
            $data = $data['check_data'];
            if(empty($data['lftime']) || empty($info['lftime'])){
                return ['code' => 400, 'errmsg' => '请选择量房时间并保存！', 'err_field' => 'select[name="lftime"]'];
            }
        }
        return ['code' => 200];
    }

    public function findOrderInfoAndTel($order_id)
    {
        $model = new OrdersModel();
        return $model->findOrderInfoAndTel($order_id);
    }

    public function getUnfenCountByUser($user)
    {
        $where = [
            'o.time_real' => ['BETWEEN', [strtotime('-90 day'), time() + 86400]],
            'o.on' => ['eq', 4],
            'o.type_fw' => ['in', [1, 2]],
            'o.fen_customer' => ['eq', 0],
            'o.deleted' => ['neq', 1],
        ];
        //审核客服只能看到自己的
        if(in_array($user['uid'],[2])){
            $where['p.op_uid'] = ['eq',$user['id']];
        }
        $db = new OrdersModel();
        $count = $db->getUnfenCountByUser($where);
        return $count;
    }

    public function getOrderHistoryCount()
    {
        $time = strtotime("-1 day", time());
        $end = mktime(17, 30, 0, date("m", $time), date("d", $time), date("Y", $time));
        $begin = strtotime("-90 day", $end);

        $model = new OrdersModel();
        return $model->getOrderHistoryCount($begin, $end);
    }

    public function getAllLiangFangList($id,$group,$manager,$monthStart,$monthEnd,$by = "")
    {
        $model = new OrdersModel();
        $result = $model->getAllLiangFangList($id,$group,$manager,$monthStart,$monthEnd,$by);
        foreach ($result as $item) {
            $list[$item["id"]] = $item["lf_count"];
            $groups[$item["kfgroup"]] += $item["lf_count"];
            $managers[$item["kfmanager"]] += $item["lf_count"];
        }

        return array("kflist" => $list,"group" => $groups ,"manager" => $managers);
    }

    public function setOrderSmsLog($order_id,$type,$tel)
    {
        import('Library.Org.Util.App');
        $app    = new \App();

        $tel_encrypt   =  substr_replace($tel,"*****",3,5); //做一个中间为星号的号码
        $tel_md5       =  $app->order_tel_encrypt($tel); //做一个加密后的号码
        $time          =  date('Y-m-d H:i:s'); //取当前时间
        //添加后台短信发送日志
        //做一个日志
        $smslog = array(
            "type"          =>  $type,
            "op_user"       =>  session("uc_userinfo.name"),
            "op_id"         =>  session("uc_userinfo.id"),
            "orderid"       =>  !empty($order_id)?$order_id:'',
            "ip"            =>  $app->get_client_ip(),
            "tel"           =>  $tel_encrypt, //为了隐私记录打引号的
            "tel_encrypt"   =>  $tel_md5, //记录 电话号码加密 为了便于查找
            "addtime"       =>  $time
        );
        $model = new LogSmsSendModel();
         return $model->addLog($smslog);
    }

    public function getZixunCompanyInfo($order_id)
    {
        $model = new OrderCompanyDialRelationModel();
        $result = $model->getZixunCompanyInfo($order_id);
        return "已咨询公司（APP业主）：".implode(" ",array_column($result,"jc"));
    }

    // 修改订单已读状态
    public function updateOrderComReadAll($order_id, $old_status = 0){
        $order2com_allread = 0;
        $orderInfoModel = new OrderInfoModel();
        $total = $orderInfoModel->getOrderReadStatistic($order_id);
        if ($total["allot_num"] > 0 && $total["allot_num"] == $total["read_num"]) {
            $order2com_allread = 1;
        }

        if ($order2com_allread != $old_status) {
            $ordersModel = new OrdersModel();
            $ordersModel->editOrder($order_id, ["order2com_allread" => $order2com_allread]);
        }
    }

    public function getQiandanFailList($order_id,$pageIndex,$pageCount)
    {
        if (empty($pageIndex)) {
            $pageIndex = 1;
        }
        $model = new OrdersModel();

        $count = $model->getQiandanFailListCount($order_id);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, $pageCount);
            $show = $p->show();

            $result = $model->getQiandanFailList($order_id, $p->firstRow, $p->listRows);

            foreach ($result as $item) {
                $sub = json_decode($item["orign_data"], true);
                if (empty($sub["qiandan_addtime"])) {
                    $sub["qiandan_addtime"] = "-";
                } else {
                    $sub["qiandan_addtime"] = date("Y-m-d", $sub["qiandan_addtime"]);
                }

                if (empty($sub["cname"])) {
                    $sub["cname"] = "-";
                }

                if (empty($sub["qz_area"])) {
                    $sub["qz_area"] = "-";
                }

                $sub["id"] = $item["orderid"];
                $list[] = $sub;
            }
        }
        return array("list" => $list, "page" => $show);
    }

    public function getOrderRealTel($id)
    {
        $model = new SafeOrderTel8Model();
        $map = array(
            "orderid" => array("EQ",$id)
        );
        return $model->getOrderTel($map);
    }

    public function getInfoByOrderId($order_id, $field = "*"){
        $ordersModel = new OrdersModel();
        return $ordersModel->getInfoByOrderId($order_id, $field);
    }

    public function getPolicyOrderInfo($order_id)
    {
        $ordersModel = new OrdersModel();
        $info = $ordersModel->getPolicyOrderInfo($order_id);

        if (!empty($info["qiandan_company"])) {
            $info["qiandan_status"] = OrdersInfo::getQiandanStatus($info["qiandan_status"]);
        } else {
            $info["qiandan_status"] = "";
        }

        $info["lxs"] =OrdersInfo::getLxs()[$info["lxs"]];
        $info["lx"] = $info["lx"] == 1?"家装":"公装";
        return $info;
    }

    public function getPolicyOrderLfInfo($order_id,$company_id)
    {
        $model = new OrdersModel();
        $info = $model->getPolicyOrderLfInfo($order_id,$company_id);
        $status = LiangFangInfo::getCompanyLfStats($info["status"]);
        return $status;
    }

    public function editOrderCsosNew($order_id, $save)
    {
        $csosDb = new OrderCsosNewModel();
        return $csosDb->editCsos($order_id, $save);
    }

    public function delOrderDocking($order_id)
    {
        $dockingDb = new OrderDockingModel();
        return $dockingDb->delDocking($order_id);
    }

    public function delOrderRob($order_id)
    {
        $robDb = new OrderRobPoolModel();
        return $robDb->delRobInfo($order_id);
    }

    public function addCsosNewLog($save){
        $csosDb = new OrderCsosNewModel();
        return $csosDb->addLog($save);
    }

    public function addOrderBackRecord($input, $user)
    {
        $save = [
            'order_id' => $input['id'],
            'allot_state' => $input['allot_state']?:2,
            'back_reason' => $input['reason']?:0,
            'back_remark' => $input['remark']?:'',
            'push_uid' => $input['push_uid']?:'',
            'op_uid' => $user['id'],
            'op_name' => $user['user'],
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $backDb = new OrderBackRecordModel();
        $id =  $backDb->addRecord($save);
        if(!empty($input['img'])){
            $img = explode(',',$input['img']);
            $img_save = [];
            foreach ($img as $v){
                if(!empty($v)){
                    $img_save[] = [
                        'record_id'=>$id,
                        'img'=>$v,
                    ];
                }
            }
            $backImgDb = new OrderBackRecordImgModel();
            $backImgDb->addImg($img_save);
        }
    }

    public function getOrderBackRecord($order_id)
    {
        $backDb = new OrderBackRecordModel();
        $info = $backDb->getOrderLastNewRecord($order_id);
        if(!empty($info)){
            $info['reason'] = $this->orderBackReason[$info['back_reason']];
        }
        return $info;
    }


    /**
     * 齐装保审核通过修改订单的签单信息
     * @param $order_id
     * @param $policy_info
     * @return bool
     */
    public function PolicyQianDanOrders($order_id, $policy_info)
    {
        $ordersModel = new OrdersModel();
        $orderInfo = $ordersModel->getOrdersById($order_id);

        $nowtime = time();
        if ($orderInfo) {
            $changeOrdersInfo = [];
            // 如果没有装修公司签约 或者申请签约的装修公司就是齐装保申请的装修公司则可进行下面的操作
            if (($orderInfo["qiandan_companyid"] == 0) || ($orderInfo["qiandan_companyid"] == $policy_info["company_id"])) {
                if ($orderInfo["qiandan_status"] != 1) {

                    $changeOrdersInfo["qiandan_companyid"] = $policy_info["company_id"];

                    $changeOrdersInfo["qiandan_jine"] = $policy_info["amount"] / 10000;     //单位转换  元->万元
                    $changeOrdersInfo["qiandan_status"] = 1;

                    if (empty($orderInfo["qiandan_addtime"])) {
                        $changeOrdersInfo["qiandan_addtime"] = $nowtime;
                    }

                    if (empty($orderInfo["qiandan_chktime"])) {
                        $changeOrdersInfo["qiandan_chktime"] = $nowtime;
                    }

                    if (empty($orderInfo["qiandan_remark"])) {
                        $changeOrdersInfo["qiandan_remark"] = "齐装保审核通过签单";
                        $changeOrdersInfo["qiandan_remark_lasttime"] = $nowtime;
                    }
                    if (!empty($changeOrdersInfo)) {
                        $changeOrdersInfo["lasttime"] = $nowtime;
                        $ordersModel->editOrder($order_id, $changeOrdersInfo);

                        $changeData = [];
                        $changeData["status"] = 4;
                        $orderCompanyReviewModel = new OrderCompanyReviewModel();
                        $orderCompanyReviewModel->editReviewInfo($order_id, $policy_info['company_id'], $changeData);

                    }

                }
            }

            return true;
        }
        return false;
    }

    public function getSelectedCitys()
    {
        $model = new \Home\Model\Db\OrdersModel();
        $citys = $model->getAllOpenCitys();
        return $citys;
    }

    public function getCitysSetList($input, $limit=20)
    {
        $model = new \Home\Model\Db\OrdersModel();
        $count = $model->getCityListCount($input);

        if(!$count) return ['list' => '', 'count' => 0, 'pageshow' => null];

        import("Library.Org.Util.Page");
        $pageClass = new \Page($count, $limit);
        $pageshow = $pageClass->show();

        $list = $model->getCitysList($input, $pageClass->firstRow, $pageClass->listRows);

        return ['list' => $list, 'count' => $count, 'pageshow' => $pageshow];
    }

    public function updateCityImportant($post)
    {
        $map['city_id'] = $post['cid'];

        $isExist = M('order_important_city')->where($map)->find();

        if($isExist && $post['mark'] == 1){
            return false;
        }

        if($post['mark'] == '1'){
            $data = $map;
            $data['city_name'] = $post['cname'];
            $data['created_at'] = time();
            $res = M('order_important_city')->add($data);
            $this->recordSetCityLog($data, '手动设置重点城市');
        }else{
            $res = M('order_important_city')->where($map)->delete();
            $this->recordSetCityLog($map, '手动取消重点城市设置');
        }

        if(!$res) return false;

        return true;
    }

    public function batchSetImportantCity($data)
    {
        $date = date('Y-m');

        M()->startTrans();

        // 1.删除已有城市
        M('order_important_city')->where('1')->delete();

        // --- 1.1 记录删除日志
        $this->recordSetCityLog([], '导入新数据时删除所有旧数据，info为删除条件');

        if(empty($data)){
            //空数据删除后直接提交并返回
            M()->commit();
            return ['info' => '', 'status' => 1];
        }

        // 2.插入新设置的城市
        $citysInfo = $this->getCitysInfo();

        foreach ($data as $v) {
            if(!isset($citysInfo[$v[0]]) || !$citysInfo[$v[0]]){
                continue;
            }
            $insert[] = [
                'city_id' => $citysInfo[$v[0]],
                'city_name' => $v[0],
                'created_at' => time()
            ];
        }

        if(!$insert){
            M()->rollback();
            return ['info' => '没有可用的数据', 'status' => 0];
        }

        $res = M('order_important_city')->addAll($insert);

        if(!$res){
            M()->rollback();
            return ['info' => '导入失败', 'status' => 0];
        }

        // --- 2.1 记录插入日志
        $this->recordSetCityLog($insert, '导入新的重点城市');

        M()->commit();
        return ['info' => '', 'status' => 1];
    }

    private function getCitysInfo()
    {
        $model = new \Home\Model\Db\OrdersModel();
        $citys = $model->getAllOpenCitys();

        $newArr = [];
        foreach ($citys as $v) {
            $newArr[$v['cname']] = $v['cid'];
        }

        return $newArr;
    }

    private function recordSetCityLog($data, $remark)
    {
        return M('log_admin')->add([
            'logtype' => 'important_city_set',
            'time' => date('Y-m-d H:i:s'),
            'username' => session('uc_userinfo.name'),
            'userid' => session('uc_userinfo.id'),
            'action' => CONTROLLER_NAME . '/' . ACTION_NAME,
            'ip' => get_client_ip(),
            'info' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'remark' => $remark,
            'user_agent' => $_SERVER["HTTP_USER_AGENT"]
        ]);
    }

    public function pushPauseOrderPool($order_info)
    {

        //计算时间周期
        $visittime = strtotime(date_format(date_create($order_info["visitime"]),"Y-m-d"));

        //获取订单审核有效的时间
        $csosModel = new OrderCsosNewModel();
        $csos_new = $csosModel->getCsosInfo($order_info["id"]);
        $lasttime = strtotime(date("Y-m-d",$csos_new["lasttime"]));
        //计算总天数
        $dayDiff = ceil(($visittime - $lasttime) / 86400)  ;

        //计算拨打间隔
        if ($dayDiff >= 15 && $dayDiff <= 60) {
            $offset = 15;
        } elseif($dayDiff > 60 && $dayDiff <= 180) {
            $offset = 30;
        } elseif($dayDiff > 180 && $dayDiff <= 365) {
            $offset = 60;
        } elseif($dayDiff > 365) {
            $offset = 90;
        } else {
            $offset = 15;
        }

        $count =  ceil($dayDiff/$offset);

        $start_time = time();
        $all = [];
        for ($i = 1; $i <= $count ; $i++) {
            $appointed_time = strtotime("+".($offset * $i) ." day",$start_time);

            $sub = [
                "visitime" => date("Y-m-d",$visittime),
                "order_id" => $order_info["id"],
                "step" => $i ,
                "appointed_time" => date("Y-m-d",$appointed_time),
                "owner_id" => session("uc_userinfo.id"),
                "owner_name" => session("uc_userinfo.name"),
                "op_uid" => session("uc_userinfo.id"),
                "op_uname" => session("uc_userinfo.name"),
                "created_at" => time(),
                "updated_at" => time()
            ];

            if ($i == $count ) {
                //最后一次计算一下和待定时间的时间差是否小于15天
                $diff = ceil(($visittime - $appointed_time) / 86400);
                if ($diff <= 15) {
                    //将约定时间改成待定时间
                    $sub["appointed_time"] = date("Y-m-d",$visittime);
                }
            }

            $all[] = $sub;
        }

        $model = new OrderPausePoolModel();

        try {
            $model->startTrans();
            //查询是否有添加记录
            $count = $model->getPauseOrderCount($order_info["id"],date("Y-m-d",$visittime));

            if ($count == 0) {
                //如果没有记录
                //客服调整的待定时间
                //删除之前的压单记录
                $model->delPauseOrder($order_info["id"]);
                //添加操作日志
                $logData = array(
                    "remark" => "删除压单操作",
                    "action_id" =>  $order_info["id"],
                    "logtype" => "delpauseorder",
                    "info"=> json_encode($all,320)
                );
                (new LogAdminModel())->addLog($logData);
            }

            $model->addAllInfo($all);

            //添加到压单记录表
            $data = [
                "order_id" => $order_info["id"],
                "visitime" => date("Y-m-d",$visittime),
                "pause_time" => date("Y-m-d",$lasttime),
                "undetermined_day" => $dayDiff,
                "op_uid" => session("uc_userinfo.id"),
                "op_uname" => session("uc_userinfo.name"),
                "created_at" => time(),
            ];
            (new OrderPauseListModel())->addInfo($data);

            //添加操作日志
            $logData = array(
                "remark" => "压单操作",
                "action_id" =>  $order_info["id"],
                "logtype" => "pauseorder",
                "info"=> json_encode($all,320)
            );

            (new LogAdminModel())->addLog($logData);

            $model->commit();
        } catch (\Exception $e) {
            $model->rollback();
        }
    }


    public function getPauseOrderPoolList($ids,$condition,$start,$end,$time_real_start,$time_real_end,$city,$area,$remarks,$op_uid,$source,$orderlx)
    {
        if ($remarks == "全部") {
            $remarks = "";
        }

        $model = new OrderPausePoolModel();
        $count =  $model->getPauseOrderPoolListCount($ids,$condition,$start,$end,$time_real_start,$time_real_end,$city,$area,$remarks,$op_uid,$source,$orderlx);

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $page = $p->show();
            $result = $model->getPauseOrderPoolList($ids,$condition,$start,$end,$time_real_start,$time_real_end,$city,$area,$remarks,$op_uid,$source,$orderlx,$p->firstRow, $p->listRows);

            $info = $list = [];
            foreach ($result as $item) {
                $item["is_edit"] = 2;
                //当全部联系完成后
                if ($item["status"] == 1) {
                    //把最大的约定时间赋值到当前约定时间
                    $item["appointed_time"] = $item["max_appointed_time"];
                }

                if ($item["status"] == 2 && $item["appointed_time"] <= date("Y-m-d")) {
                    $item["is_edit"] = 1;
                }

                $list[$item["order_id"]] = $item;
                $order_ids[] = $item["order_id"];
            }
            $info["now_count"] =  $model->getPauseListCountByStatus($ids,2);
            $info["all_count"] = $count;

            //获取每个订单的电话号码的重复次数
            $result = D('Orders')->getTelnumberRepaetCountByIds($order_ids);
            foreach ($result as $key => $item) {
                $list[$item['id']]["phone_repeat_count"] = $item['repeat_count'];
            }

            //查询订单最后一次时间
            //获取每个订单的通话记录
            $model = new LogTelcenterOrdercallModel();
            $result = $model->getOrderCallCountAndLastTimeByOrderIds($order_ids);

            $callRepeats = array_column($result,'call_repeats','orderid');
            $callLasttime = array_column($result,'time_add','orderid');

            foreach ($list as &$item) {
                $item["call_lasttime"] = $callLasttime[$item['order_id']];
            }
        }

        return ["list" => $list,"page" => $page,"info" => $info];

    }

    public function getOrderPauseInfo($order_id)
    {
        $model = new OrderPausePoolModel();
        return $model->getOrderPauseInfo($order_id);
    }

    public function getOrderMaxPauseInfo($order_id)
    {
        $model = new OrderPausePoolModel();
        return $model->getOrderMaxPauseInfo($order_id);
    }

    public function pauseOrderUp($id,$data)
    {
        $model = new OrderPausePoolModel();
        $result = $model->pauseOrderUp($id,$data);

        //添加操作日志
        $logData = array(
            "remark" => "压单编辑操作",
            "action_id" =>  $id,
            "logtype" => "pauseorderup",
            "info"=> json_encode($data,320)
        );

        (new LogAdminModel())->addLog($logData);

        return $result;
    }

    public function getPauseOrderStat($kf,$group,$manager,$state)
    {
        $model = new OrderPausePoolModel();
        $result = $model->getPauseOrderStat($kf,$group,$manager,$state);

        foreach ($result as &$item) {
            $item["other_count"] = $item["all_count"] - $item["pause_count"] -$item["fen_count"] - $item["zen_count"] - $item["wx_count"];
        }
        return $result;
    }

    public function pauseOrderSummary($begin,$end)
    {
        $model = new OrderPauseListModel();
        $result = $model->getPauseSummary($begin,$end);
        $result["other_count"] = $result["all_count"] - $result["pause_count"] - $result["fen_count"] - $result["zen_count"] - $result["wx_count"];
        return $result;
    }

    public function getCustomerMediumTelStat($begin,$end,$kf,$group)
    {
        $begin = strtotime($begin);
        $end = strtotime($end) + 86400 -1;

        //新回访电话统计
        $model = new LogClinkReviewTelcenterModel();
        //订单处理次数、实际处理订单量，呼出量，平均呼叫次数
        $result = $model->getCustomerMediumTelStat($begin,$end,$kf,$group);

        $all = $list = [];
        foreach ($result as $item) {
            $list[$item['op_uid']] = [
                "name" => $item["op_uname"],
                "day_order_count" => $item["day_order_count"],//订单处理量
                "order_count" => $item["order_count"],//实际订单量
                "tel_count" => $item["sum_count"],//呼出量
                "tel_rate" => 0
            ];

            if ($item["day_order_count"] > 0) {
                $list[$item['op_uid']]["tel_rate"] = round($item["sum_count"]/$item["day_order_count"],1);//平均呼叫次数
            }

            $all["order_count"] += $item["order_count"];
            $all["day_order_count"] += $item["day_order_count"];
            $all["tel_count"] += $item["sum_count"];
            $all["tel_count"] += $item["tel_count"];
        }


        //老回访
        //订单处理次数、实际处理订单量，呼出量，平均呼叫次数
        $result = $model->getCustomerMediumOldTelStat($begin,$end,$kf,$group);

        foreach ($result as $item) {
            if (!array_key_exists($item["op_uid"],$list)) {
                $list[$item['op_uid']] = [
                    "name" => $item["op_uname"],
                    "order_count" => 0,//实际订单量
                    "tel_count" =>0,//呼出量
                    "tel_rate" => 0 ,//平均呼叫次数
                ];
            }

            $list[$item['op_uid']]["day_order_count"] += $item["day_order_count"];//订单处理量
            $list[$item['op_uid']]["order_count"] += $item["order_count"];//实际订单量
            $list[$item['op_uid']]["tel_count"] += $item["sum_count"];//呼出量
            if ($list[$item['op_uid']]["day_order_count"] > 0 ) {
                $list[$item['op_uid']]["tel_rate"] = round( $list[$item['op_uid']]["tel_count"]/ $list[$item['op_uid']]["day_order_count"],1);//平均呼叫次数
            }

            $all["order_count"] += $item["order_count"];
            $all["day_order_count"] += $item["day_order_count"];
            $all["tel_count"] += $item["sum_count"];
            $all["tel_count"] += $item["tel_count"];
        }

        //呼通量、通话时长
        $result = $model->getCustomerMediumTelTimeStat($begin,$end,$kf,$group);
        foreach ($result as $item) {
            //呼通量
            $list[$item["op_uid"]]["call_count"] = $item["call_count"];
            $list[$item["op_uid"]]["call_avg_time"] = 0;
            $list[$item["op_uid"]]["call_time"] = 0;

            $all["call_count"] += $item["call_count"];

            //呼通率
            $list[$item["op_uid"]]["call_rate"] = round($item["call_count"]/ $list[$item["op_uid"]]["tel_count"],4) * 100;
            //总通话时长
            if ($item["call_time"] > 0) {
                $list[$item["op_uid"]]["call_time"] = timediff($item["call_time"]);
            }

            $all["call_time"] += $item["call_time"];

            //平均通话时长
            if ($item["call_count"] > 0) {
                $list[$item["op_uid"]]["call_avg_time"] = timediff(round($item["call_time"]/$item["call_count"]));
            }
        }

        if ($all["call_count"] > 0) {
            $all["call_avg_time"] = timediff(round($all["call_time"]/$all["call_count"]));
        }

        if ($all["call_time"] > 0) {
            $all["call_time"] = timediff($all["call_time"]);
        }

        if ($all["day_order_count"] > 0) {
            $all["tel_rate"] = ceil($all["tel_count"]/$all["day_order_count"]) ;
        }

        if ($all["tel_count"] > 0) {
            $all["call_rate"] = round($all["call_count"]/ $all["tel_count"],4) * 100;
        }

        return [$list,$all];
    }

    public function customerMediumCityStat($begin,$end,$city)
    {
        $begin = strtotime($begin);
        $end = strtotime($end) + 86400 -1;

        //获取城市分单量
        $model = new OrderInfoModel();
        $result = $model->getCityByOrderCount($begin,$end,$city);
        $all = $list = [];

        foreach ($result as $item) {
            $list[$item["cid"]] = [
                "cname" => $item["cname"],
                "order_count" => $item["count"],
                "tel_count" => 0,
                "call_count" => 0,
                "call_rate" => 0
            ];
            $all["order_count"] += $item["count"];
        }

        //呼出量、呼通量、呼通率
        $model = new LogClinkReviewTelcenterModel();
        $result = $model->getCityByTelCount($begin,$end,$city);
        foreach ($result as $item) {
            if (!array_key_exists($item["cid"],$list)) {
                $list[$item["cid"]] = [
                    "cname" => $item["cname"],
                    "order_count" => 0
                ];
            }

            $list[$item["cid"]]["tel_count"] = $item["tel_count"];
            $list[$item["cid"]]["call_count"] = $item["call_count"];
            $list[$item["cid"]]["call_rate"] = round($item["call_count"]/ $item["tel_count"],4) * 100;

            $all["tel_count"] += $item["tel_count"];
            $all["call_count"] += $item["call_count"];
        }

        if ($all["tel_count"] > 0) {
            $all["call_rate"] = round($all["call_count"] / $all["tel_count"], 4) * 100;
        }

        return [$list,$all];
    }

    // 删除订单分配落档信息
    public function deleteOrderAllotInfo($order_id){
        $orderInfoModel = new OrderInfoModel();
        return $orderInfoModel->deleteOrderAllotInfo($order_id);
    }

    /**
     * 订单分配 装修公司验证
     * @param array $companys
     * @return int
     */
    public function checkFenCompanyInfo($companys = [])
    {
        if (count($companys) == 0) {
            throw new Exception(StatusCodeEnum::CODE_4000003,4000003);
        }
        $type_fw = 2;
        foreach ($companys as $key => $value) {
            if (empty($value["type_fw"])) {
                throw new Exception(StatusCodeEnum::CODE_4000004,4000004);
            }

            if ($value["type_fw"] == 1) {
                $type_fw = 1;
            }
        }

        return $type_fw;
    }

    /**
     * 订单分配 订单验证
     * @param $order_id
     * @param $fen_status
     * @return array
     */
    public function checkFenOrderInfo($order_id,$fen_status)
    {
        try {
            //查询订单信息
            $info = $this->getOrderDetails($order_id);

            if ($info["on"] != 4 ) {
                throw new Exception(StatusCodeEnum::CODE_4000005,4000005);
            }

            //如果是分单必须有一个公司是分单
            if ($info["on"] == 4 && $info["type_fw"] == 1) {
                if ($fen_status == 2) {
                    throw new Exception(StatusCodeEnum::CODE_4000006,4000006);
                }
            }

            //如果是赠单/待分配赠单 必须全部是赠单,
            if ($info["on"] == 4 && ($info["type_fw"] == 2 || $info["type_fw"] == 6)) {
                if ($fen_status == 1) {
                    throw new Exception(StatusCodeEnum::CODE_4000007,4000007);
                }
            }

            //如果是待分配分单 , 则必须有一个是分单
            if ($info["on"] == 4 && $info["type_fw"] == 5) {
                if ($fen_status == 2) {
                    throw new Exception(StatusCodeEnum::CODE_4000006,4000006);
                }
            }

            if (floatval($info["mianji"]) == 0) {
                throw new Exception('订单面积数据检测异常',4000009);
            }

            return $info;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(),$e->getCode());
        }
    }

    /**
     * 装修公司分类
     * @param array $companys
     * @return array
     */
    public function getComapnyClassified($companys = [])
    {
        //获取签返会员信息
        $all =  $normal = $old_qian = $new_qian = [];

        foreach ($companys as $key => $value) {
            if ($value["classid"] == 3) {
                if (in_array($value["mode"],[1,3])) {
                    //常规
                    $normal[] = $value;
                } elseif (in_array($value["mode"],[2,4])){
                    //新签返
                    $new_qian[] = $value;
                }
            }else if ($value["classid"] == 6){
                //老签返
                $old_qian[] = $value;
            }
            $all[] = $value;
        }

        return ["normal" => $normal,"old_qian" => $old_qian,"new_qian" => $new_qian,"all" => $all ];
    }

    /**
     * 订单分配公司信息
     * @param $order_id 订单ID
     * @return mixed
     */
    public function getOrderFenComapny($order_id)
    {
        try {
            $model = new OrderInfoModel();
            return $model->getOrderComapny($order_id);
        } catch (\Exception $e) {
            throw new Exception(StatusCodeEnum::CODE_5000003,5000003);
        }
    }

    /**
     * 处理常规会员订单分配逻辑
     * @param $company 装修公司
     * @param $order_info 订单信息
     * @param $order_fen_info 订单分配信息
     */
    public function handlingNormalBusiness($company = [],$order_info = [],$order_fen_info = [])
    {
        try {
            $orderInfoModel = new OrderInfoModel();

            //分配的公司ID
            $company_ids = array_column($company,"company_id");
            $save_company = [];//需要保存的公司
            $un_del_company_id = []; //不需删除的公司
            $del_company_id = [];//需要删除的公司
            $order_type_fw_company = array_column($order_fen_info,"type_fw","id");//已分配的公司状态
            $un_change_company_id = [];
            //已分配装修公司
            foreach ($order_fen_info as $item) {
                //如果是抢单的数据状态不能变
                if($item['allot_source'] != 3){
                    //将变更的会员找出来
                    $cooperate_mode = empty($item["order_cooperate_mode"])?$item["cooperate_mode"]:$item["order_cooperate_mode"];
                    if ($item["classid"] == 3 &&  in_array($cooperate_mode ,[1,3]) && !in_array($item["id"],$company_ids) ) {
                        $del_company_id[] = $item["id"];
                    } elseif($item["classid"] == 3 &&  in_array($cooperate_mode ,[1,3]) && in_array($item["id"],$company_ids)) {
                        //未变更的公司ID
                        $un_del_company_id[] = $item["id"];
                    }
                } else {
                    //抢单的公司
                    $un_change_company_id[] = $item["id"];
                }
            }

            //如果未变更，从分配公司列表中剔除
            foreach ($company as $key => $item) {
                //抢单的公司不能变更
                if (in_array($item["company_id"],$un_change_company_id)) {
                    continue;
                }

                if (in_array($item["company_id"],$un_del_company_id) && $item["type_fw"] == $order_type_fw_company[$item["company_id"]]) {
                    continue;
                }

                //订单状态变更的公司
                if (in_array($item["company_id"],$un_del_company_id) && $item["type_fw"] != $order_type_fw_company[$item["company_id"]]) {
                    $change_company[] = $item;
                    continue;
                }

                if (in_array($item["mode"],[1,3])) {
                    $save_company[] = $item;
                }
            }

            //删除更换的装修公司分配信息
            if (count($del_company_id) > 0) {
                $orderInfoModel->delOrderCompany($order_info["id"],$del_company_id);
            }

            if (count($save_company) > 0) {
                //添加新的公司信息
                $all = [];
                foreach ($save_company as $item) {
                    $all[] = [
                        "com" => $item["company_id"],
                        "order" => $order_info["id"],
                        "type_fw" => $item['type_fw'],
                        "allot_source" => 1,
                        "addtime" => time(),
                        "opid" => session("uc_userinfo.id"),
                        "opname" => session("uc_userinfo.name"),
                        "cooperate_mode" => $item["mode"]
                    ];
                }
                $orderInfoModel->addAllInfo($all);
            }

            //订单状态变更处理
            if (count($change_company) > 0) {
                foreach ($change_company as $item) {
                    $data = [
                        "type_fw" => $item["type_fw"]
                    ];
                    $orderInfoModel->editOrderInfo([$item["company_id"]],$order_info["id"],$data);
                }
            }

        } catch(\Exception $e) {
            throw new Exception($e->getMessage(),$e->getCode());
        }
    }

    /**
     * 新签返会员订单分配逻辑
     * @param array $company 装修公司
     * @param array $order_info 订单信息
     * @param array $order_fen_info 订单分配信息
     */
    public function handlingNewQianBusiness($company = [],$order_info = [],$order_fen_info = [])
    {
        try {
            $orderInfoModel = new OrderInfoModel();
            $qianLogic =  new UserLogicModel();
            //分配的公司ID
            $company_ids = array_column($company,"company_id");
            $save_company = [];//需要保存的公司
            $un_del_company_id = []; //不需删除的公司
            $del_company = [];//需要删除的公司
            $order_type_fw_company = array_column($order_fen_info,"type_fw","id");//已分配的公司状态
            $deductionCompany = []; //需求扣费的装修公司
            $change_company= [];//状态变更的公司
            $refundCompanyIds = [];//退费的公司ID
            $deductionCompanyIds = [];//需要扣费的公司ID
            $refundCompany = [];//退费的公司
            $fen_to_zen_comapny_ids = [];//分改赠的公司

            //已分配装修公司
            foreach ($order_fen_info as $item) {
                //将变更的会员找出来
                $cooperate_mode = empty($item["order_cooperate_mode"])?$item["cooperate_mode"]:$item["order_cooperate_mode"];
                if ($item["classid"] == 3 &&  in_array($cooperate_mode ,[2,4]) && !in_array($item["id"],$company_ids) ) {
                    $del_company[] = $item;
                } elseif($item["classid"] == 3 &&  in_array($cooperate_mode ,[2,4]) && in_array($item["id"],$company_ids) ) {
                    //未变更的公司ID
                    $un_del_company_id[] = $item["id"];
                }
            }

            //如果未变更，从分配公司列表中剔除
            foreach ($company as $key => $item) {
                //如果分配状态未变更
                if (in_array($item["company_id"],$un_del_company_id) && $item["type_fw"] == $order_type_fw_company[$item["company_id"]]) {
                    continue;
                }

                //如果状态变更了
                if (in_array($item["company_id"],$un_del_company_id) && $item["type_fw"] != $order_type_fw_company[$item["company_id"]]) {
                    $item["old_type_fw"] = $order_type_fw_company[$item["company_id"]];
                    $change_company[] = $item;
                    continue;
                }

                if (in_array($item["mode"],[2,4])) {
                    $save_company[] = $item;
                }
            }

            //订单分配状态变更的公司
            if (count($change_company) > 0) {
                foreach ($change_company as $item) {
                    //赠改分
                    if ($item["old_type_fw"] == 2 && $item["type_fw"] == 1) {
                        $deductionCompany[] = $item;
                        $deductionCompanyIds[] = $item["company_id"];
                        //删除分配的轮单记录
                        $qianLogic->delNewQianOrderMap([$item],$order_info["id"]);
                    }

                    //分改赠
                    if ($item["old_type_fw"] == 1 && $item["type_fw"] == 2) {
                        $refundCompanyIds[] = $item["company_id"];
                        $fen_to_zen_comapny_ids[] = $item["company_id"];
                    }

                    //修改原记录的订单分配状态
                    $data = [
                        "type_fw" => $item["type_fw"]
                    ];
                    $orderInfoModel->editOrderInfo([$item["company_id"]],$order_info["id"],$data);
                }
            }
       
            //新增的公司
            if (count($save_company) > 0) {
                //添加新的公司信息
                $all = [];
                foreach ($save_company as $item) {
                    $all[] = [
                        "com" => $item["company_id"],
                        "order" => $order_info["id"],
                        "type_fw" => $item['type_fw'],
                        "allot_source" => 1,
                        "addtime" => time(),
                        "opid" => session("uc_userinfo.id"),
                        "opname" => session("uc_userinfo.name"),
                        "cooperate_mode" => $item["mode"]
                    ];
                }
                $orderInfoModel->addAllInfo($all);

                //将分单状态的公司进行扣费操作
                foreach ($save_company as $item) {
                    $deductionCompany[] = $item;
                    //需要扣费的公司
                    if ($item["type_fw"] == 1) {
                        $deductionCompanyIds[] = $item["company_id"];
                    }
                }
            }
           
            //扣费操作
            if (count($deductionCompanyIds) > 0) {
                //检测账户状态
                $result = $qianLogic->getNewQianCompanyAccountInfo($deductionCompanyIds,$order_info);

                if ($result["error_code"] != 0) {
                    throw new Exception($result["error_msg"],$result["error_code"]);
                }
                //执行扣费操作
                $result =  $qianLogic->setCompanyDeduction($deductionCompanyIds,$order_info);

                //添加轮单扣费标识
                foreach ($deductionCompany as $key => $item) {
                    if (in_array($item["company_id"], $result["round_number_companys"])) {
                        $deductionCompany[$key]["use_number"] = 1; // 使用了次数抵扣
                    }
                }
            }

            if (count($deductionCompany) > 0) {
                //添加轮单记录表数据
                $qianLogic->addNewQianOrderMap($deductionCompany,$order_info);
            }

            //退费操作
            if (count($del_company) > 0) {
                //删除分配信息
                foreach ($del_company as $item) {
                    $refundCompany[] = [
                        "company_id" => $item["id"],
                        "type_fw" => $item["type_fw"]
                    ];
                    //如果是分单公司，记录公司ID
                    if ($item["type_fw"] == 1) {
                        $refundCompanyIds[] = $item["id"];
                    }
                }
            }

            //返还订单的轮单金额/数量
            if (count($refundCompanyIds) > 0) {
                $qianLogic->setCompanyRefund($refundCompanyIds,$order_info["id"]);
            }

            if (count($refundCompany) > 0) {
                //删除分配的订单记录
                $qianLogic->delNewQianOrderMap($refundCompany,$order_info["id"]);

                //删除分配关系
                $company_ids = array_column($refundCompany,"company_id");
                $orderInfoModel->delOrderCompany($order_info["id"],$company_ids);
            }

            //逻辑执行顺序问题，分改赠的轮单记录字后修改,否则影响轮单费退还
            if (count($fen_to_zen_comapny_ids) > 0) {
                $data = [
                    "round_money" => 0
                ];
                $qianLogic->editNewQianOrderMap($fen_to_zen_comapny_ids,$order_info["id"],$data);
            }

        }  catch(\Exception $e) {
            throw new Exception($e->getMessage(),$e->getCode());
        }
    }

    /**
     * 老签返会员订单分配逻辑
     * @param array $company 装修公司
     * @param array $order_info 订单信息
     * @param array $order_fen_info 订单分配信息
     */
    public function handlingOldQianBusiness($company = [],$order_info = [],$order_fen_info = [])
    {
        try {
            $qianLogic =  new UserLogicModel();
            $orderInfoModel = new OrderInfoModel();
            $order_type_fw_company = array_column($order_fen_info,"type_fw","id");//已分配的公司状态
            $company_ids = array_column($company,"company_id");
            $qian_company = [];//签返公司
            $save_company = [];//添加的新公司
            $del_company = [];//删除的公司

            foreach ($order_fen_info as $item) {
                if ($item["classid"] == 6 && !in_array($item["id"],$company_ids)) {
                    //需要返还的公司
                    $item["return_order_count"] = 1;
                    $item["company_id"] = $item["id"];
                    $qian_company[$item["id"]] = $item;
                    $del_company[] = $item;
                }
            }

            foreach ($company as $key => $item) {
                //分配未改变的公司
                if (array_key_exists($item["company_id"],$order_type_fw_company)) {
                    unset($qian_company[$item["company_id"]]);
                    continue;
                }

                if (!array_key_exists($item["company_id"],$order_type_fw_company)) {
                    //扣除订单标识
                    $item["return_order_count"] = -1;
                    $qian_company[$item["company_id"]] = $item;
                }

                if ($item["classid"] == 6) {
                    $save_company[] = $item;
                }
            }
          
            if (count($save_company) > 0) {
                //添加新的公司信息
                $all = [];
                foreach ($save_company as $item) {
                    $all[] = [
                        "com" => $item["company_id"],
                        "order" => $order_info["id"],
                        "type_fw" => $item['type_fw'],
                        "allot_source" => 1,
                        "addtime" => time(),
                        "opid" => session("uc_userinfo.id"),
                        "opname" => session("uc_userinfo.name"),
                        "cooperate_mode" => $item["mode"]
                    ];
                }
                $orderInfoModel->addAllInfo($all);
            }

            if (count($del_company) > 0) {
                //删除分配关系
                $company_ids = array_column($del_company,"id");
                $orderInfoModel->delOrderCompany($order_info["id"],$company_ids);
            }

            $qianLogic->qianDanCompanyOrder($qian_company,$order_info["id"]);


        }   catch(\Exception $e) {
            throw new Exception($e->getMessage(),$e->getCode());
        }

    }

    /**
     * 获取订单信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getOrderDetails($id, $is_jiaju = false)
    {
        //查询订单信息
        if($is_jiaju){
            //如果是家具订单 , 则查询家具数据
            $jiajuLogic = new JiajuOrdersLogicModel();
            $info = $jiajuLogic->getJiajuOrdersInfo($id);
            if(count($info) > 0){
                $info = $info[0];
                $info['time_real'] = date("Y-m-d H:i:s",$info['time_real']);
                $info['time'] = date("Y-m-d H:i:s",$info['time']);
                $info['lasttime'] = date("Y-m-d H:i:s",$info['lasttime']);
            }
        }else{
            $order = D('Home/Orders');
            $info = $order->findOrderInfo($id);
        }
        //如果经度纬度存在
        if(!empty(floatval($info["lng"]))&&!empty(floatval($info["lat"]))){
            $info["jingwei"] = $info["lng"].",".$info["lat"];
        }

        if (count($info) == 0) {
            return false;
        }

        if ($info["nf_time"] == "0000-00-00") {
            $info["nf_time"] = "";
        }

        //页面显示微信
        if(!empty($info['wx'])){
            if(strlen($info['wx']) > 6){
                $info['wx_show'] =  substr_cut($info['wx'],3,3);
            }else{
                $info['wx_show'] = '****';
            }
        }
        //计算订单状态
        if($is_jiaju){
            //家具转装修 , 订单状态就是新单
            $info["on"] = 0;
        }else{
            if ($info['on'] == 0 && $info['on_sub'] == 9) {
                $info["orderstatus"] = 1;
            }
            elseif ( $info['on'] == 2){
                $info["orderstatus"] = 2;
            }
            elseif ( $info['on'] == 4 && $info['type_fw'] == 0){
                $info["orderstatus"] = 3;
            }
            elseif ( $info['on'] == 4 && $info['type_fw'] == 1){
                $info["orderstatus"] = 4;
            }
            elseif ( $info['on'] == 4 && $info['type_fw'] == 2){
                $info["orderstatus"] = 6;
            }
            elseif ( $info['on'] == 4 && $info['type_fw'] == 3){
                $info["orderstatus"] = 5;
            }
            elseif ( $info['on'] == 4 && $info['type_fw'] == 4){
                $info["orderstatus"] = 7;
            }
            elseif ( $info['on'] == 5){
                $info["orderstatus"] = 8;
            }elseif ( $info['on'] == 98){
                $info["orderstatus"] = 98;
            }
        }

        if ($info["lng"] > 0 && $info["lat"] > 0) {
            $info["coordinate"] = $info["lng"].",".$info["lat"];
        }

        $exp = array_filter(explode("；",$info["text"]));
        $info["text_array"] = $exp;

        $info["tel_sensitive"] = $info["tel"];
        //获取审核显号
        $admin = getAdminUser();
        $info['apply_tel'] = D('OrdersApplyTel')->getApplyTelByOrdersIdAndApplyId($info['id'], $admin['id']);

        if ($info["apply_tel"]['status'] == 2) {
            $info["tel"] = $info["tel8"];
        }

        $info["lasttime"] = empty($info["lasttime"])?"":$info["lasttime"];

        //获取家居订单
        $logLogic = new LogOrderRouteLogicModel();
        $route = $logLogic->getOrderLog('',$id);
        $info["jiaju_order_id"] = '';
        if($route){
            $info["jiaju_order_id"] = $route['jiaju_order_id'];
        }
        $info['cname_ip'] = $info['cname'];
        //查询ip城市 , 如果没有 , 就用订单城市
        if($info['ip']){
            $city = get_city_by_ip($info['ip']);
            if($city[2]){
                $info['cname_ip'] = $city[2];
            }
        }
        return $info;
    }

    public function setOrderCompanyReview($company,$order_id)
    {
        try {
            //查询是否已经公司回访数据
            $reviewLogic = new OrderCompanyReviewLogicModel();
            $result = $reviewLogic->getReviewInfoByOrderId($order_id);
            $history_ids = array_column($result,"comid");
            $now_ids = array_column($company,"company_id");
            $save_list = [];
            $del_list = [];

            foreach ($company as $item) {
                //新增的公司ID
                if (!in_array($item["company_id"],$history_ids)) {
                    $save_list[] = [
                        "comid" => $item["company_id"],
                        "orderid" => $order_id,
                        "time" => time()
                    ];
                }
            }

            //需要删除的公司ID
            foreach ($result as $item) {
                if (!in_array($item["comid"],$now_ids)) {
                    $del_list[] = $item["comid"];
                }
            }

            if (count($save_list) > 0) {
                $reviewLogic->addReviewInfo($save_list);
            }

            //删除变更的数据
            if (count($del_list) > 0) {
                $reviewLogic->delReviewInfoByOrderIdAndCompanyId($del_list,$order_id);
            }

        }  catch(\Exception $e) {
            throw new Exception($e->getMessage(),$e->getCode());
        }
    }

    public function setOrderSrc($order_id)
    {
        try {
            //查询订单的渠道数据
            $model = new OrdersSourceModel();
            $hzsModel = new HzsCompanyModel();
            $result = $model->getOrderSrc($order_id);

            if (count($result) > 0 && !empty($result["source_src"])) {
                //查找渠道标识的渠道商
                $arrears_order = $result["arrears_order"];
                //订单是否记录标识
                $state = 1;
                if ($arrears_order > 0) {
                    //渠道商有欠订单
                    $state = 2;
                    //渠道商欠费订单 -1
                    $hzsModel->setArrearsOrderDec($result["hzs_id"]);
                }

                //添加到合作商分单表
                $data = [
                    "order_id" => $order_id,
                    "src" => $result["source_src"],
                    "real_time" => empty($result["lasttime"])?0:$result["lasttime"],
                    "state" => $state,
                    "created_at" => time(),
                    "updated_at" => time()
                ];
                (new HzsRealOrderModel())->addInfo($data);
                Log::write("合作商渠道数据：【".$result["account"]."】【".json_encode($result)."】【".$order_id."】 【".$state."】",Log::INFO);
            }
        } catch(\Exception $e) {
            throw new Exception("渠道数据异常",5000003);
        }
    }

    public function setOrderSrcInc($order_id)
    {
        try {
            //查询订单的渠道数据
            $model = new OrdersSourceModel();
            $hzsModel = new HzsCompanyModel();
            $result = $model->getOrderSrc($order_id);
            if (count($result) > 0 && !empty($result["source_src"])) {
                //渠道商欠单数据 +1
                $hzsModel->setArrearsOrderInc($result["hzs_id"]);
                Log::write("合作商渠道数据：【".$result["account"]."】【".json_encode($result)."】【".$order_id."】【欠单 +1】",Log::INFO);
            }
        } catch(\Exception $e) {
            throw new Exception("渠道数据异常",5000003);
        }
    }

    // 获取订单详情
    public function getOrderDetail($order_id){
        $ordersModel = new OrdersDbModel();
        $detail = $ordersModel->getOrderDetail($order_id);
        
        if (!empty($detail)) {
            $detail["csos_date"] = date("Y-m-d H:i:s", $detail["csos_time"]);
            $detail["date_real"] = date("Y-m-d H:i:s", $detail["time_real"]);
            $detail["lx_name"] = OrdersInfo::getItem("lx", $detail["lx"]);
            $detail["lxs_name"] = OrdersInfo::getItem("lxs", $detail["lxs"]);
            $detail["keys_name"] = OrdersInfo::getItem("keys", $detail["keys"]);
            $detail["tel"] = substr_replace($detail["tel"], "*****", 3, 5);
            
            // 查询ip城市 , 如果没有 , 就用订单城市
            $detail["ip_city"] = $detail["city_name"];
            if($detail["ip"]) {
                $ipcity = get_city_by_ip($detail["ip"]);
                if($ipcity[2]){
                    $detail["ip_city"] = $ipcity[2];
                }
            }
        }

        return $detail;
    }
}