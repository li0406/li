<?php
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\JiajuOrdersLogicModel;
use Home\Model\Logic\OrdersLogicModel;
use Home\Model\Logic\OrderSourceLogicModel;
use Home\Model\Logic\QuyuLogicModel;
use Home\Model\OrdersModel;

class OrderController extends HomeBaseController
{

    private $source_in = array(
        "0" => "-请选择-",
        "4" => "业主发布",
        "1" => "在线客服",
        "2" => "400电话",
        "3" => "QQ咨询",
        "10" => "微信咨询",
        "11" => "推广部",
        "5" => "赠送单生成",
        "100" => "非业主发布",
        "12" => "微信已审核",
        "13" => "微信未审核",
    );

    private $lf_time = array(
        "随时,下班,今明" => "随时,下班,今明",
        "1周内,本周末" => "1周内,本周末",
        "2周内,下周末" => "2周内,下周末",
        "3周内" => "3周内",
        "4周内" => "4周内",
        "1个月以上" => "1个月以上",
        "其他" => "其他"
    );

    private $start_time = array(
        "1个月内开工" => "1个月内开工",
        "2个月内开工" => "2个月内开工",
        "3个月内开工" => "3个月内开工",
        "4个月内开工" => "4个月内开工",
        "5个月内开工" => "5个月内开工",
        "6个月内开工" => "6个月内开工",
        "6个月以上开工" => "6个月以上开工",
        "方案满意开工" => "方案满意开工",
        "满意拿房后开工" => "满意拿房后开工",
        "面议" => "面议"
    );

    private $keys = array(
        "1" => "有钥匙",
        "0" => "无钥匙",
        "3" => "其他"
    );

    private $lxs = array(
        "1" => "新房装修",
        "2" => "旧房装修",
        "3" => "旧房改造"
    );

    private static $type_fw = array(
        "1" => "分单",
        "2" => "赠单",
        "3" => "分没人跟",
        "4" => "赠没人跟"

    );
    private static $lx = array(
        "1" => "家装",
        "2" => "公装"
    );

    private $status = array(
        array("name" => "状态未变", "id" => "-99", "child" => array(
            "无人接听",
            "未接通",
            "通话中",
            "空号",
            "装修公司",
            "拒绝服务",
            "开场挂",
            "核实一半挂机",
            "拒接",
            "只是看看",
            "寻求其他服务",
            "重复订单",
            "已开站无真会员",
            "地级市50公里以外面积200平以下",
            "关机",
            "停机",
            "假订单",
            "测试单",
            "否认发单",
            "其他"
        )
        ),
        array("name" => "次新单", "id" => "0", "child" => array(
            "无人接听",
            "未接通",
            "通话中",
            "空号",
            "装修公司",
            "拒绝服务",
            "开场挂",
            "核实一半挂机",
            "拒接",
            "只是看看",
            "寻求其他服务",
            "重复订单",
            "已开站无真会员",
            "地级市50公里以外面积200平以下",
            "关机",
            "停机",
            "假订单",
            "测试单",
            "否认发单",
            "过段时间联系",
            "其他"
        )
        ),
        array("name" => "待定单", "id" => "1", "child" => array(
            "无人接听",
            "未接通",
            "通话中",
            "空号",
            "装修公司",
            "拒绝服务",
            "开场挂",
            "核实一半挂机",
            "拒接",
            "只是看看",
            "寻求其他服务",
            "重复订单",
            "已开站无真会员",
            "地级市50公里以外面积200平以下",
            "关机",
            "停机",
            "假订单",
            "测试单",
            "否认发单",
            "过段时间联系",
            "其他"
        )
        ),
        array("name" => "有效未分配", "id" => "2", "child" => array()),
        array("name" => "分单", "id" => "3", "child" => array()),
        array("name" => "分没人跟", "id" => "4", "child" => array()),
        array("name" => "赠单", "id" => "5", "child" => array(
            "距离远",
            "预算低",
            "面积小",
            "交房时间长",
            "开工时间长",
            "城市未开",
            "需要垫资",
            "不能量房",
            "其他"
        )
        ),
        array("name" => "赠没人跟", "id" => "6", "child" => array()),
        array("name" => "无效", "id" => "7", "child" => array(
            "空号",
            "装修公司",
            "拒绝服务",
            "开场挂",
            "核实一半挂机",
            "拒接",
            "只是看看",
            "寻求其他服务",
            "重复订单",
            "已开站无真会员",
            "地级市50公里以外面积200平以下",
            "关机",
            "停机",
            "假订单",
            "测试单",
            "否认发单",
            "精装房",
            "其他"
        )
        )
    );
    private $shi = array(
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

    private $other = array(
        "装修公司做过同样小区",
        "要求装修公司规模大，有品牌",
        "施工过程中监控",
        "需要提供齐装网对装修公司的监管内容",
        "要看样板房或案例图片",
        "有公装装修经验",
        "只接本地电话号码，或提供装修公司号码业主自己联系",
        "只需要1-2家装修公司",
        "装修公司在小区有样板房",
        "设计师要有经验",
        "报价与实际内容相符",
        "年前装修好",
        "联系前先发短信"
    );

    public function index()
    {
        $admin = getAdminUser();
        $cityIds = getAdminCityIds(true, true, true);
        $arr = OP("open_type_list");
        $arr = array_filter(explode(",",$arr));
        //订单状态,包含各个状态对于的数据字段的值，以及各个状态下的排序规则
        $main['status'] = D('Orders')->getOrderStatusDescription(true,1);

        //获取订单备注
        $main['remarks'] = D('Orders')->getOrdersRemarks();

        //获取查询条件
        $param = I('get.');

        //初始化定义查询条件，目的是规范输入
        $id = 0;
        $cs = 0;
        $xiaoqu = '';
        $ip = '';
        $tel_encrypt = '';
        $isactivity = $param["isactivity"];

        //真实订单发布时间限制
        $time_real_start = empty($param['time_real_start']) ? '' : strtotime($param['time_real_start']);
        $time_real_end = empty($param['time_real_end']) ? '' : strtotime($param['time_real_end'] . ' 23:59:59');

        //修改后的订单发布时间筛选
        $time_start = empty($param['time_start']) ? '' : strtotime($param['time_start']);
        $time_end = empty($param['time_end']) ? '' : strtotime($param['time_end'] . ' 23:59:59');

        //不为待定或者没选时间段,限定查询最近90天的单子
        if (14 != $param['status'] && empty($param['time_real_start']) && empty($param['time_start'])) {
            $time_start = strtotime("-90 day");
        }

        //拿房时间限制
        $nf_time_start = empty($param['nf_time_start']) ? '' : $param['nf_time_start'];
        $nf_time_end = empty($param['nf_time_end']) ? '' : $param['nf_time_end'];
        $on = false;
        $on_sub = false;
        $type_fw = false;
        $remarks = empty($param['remarks']) ? '' : $param['remarks'];
        $openeye_st = false;
        $order = 'time_real DESC';

        //默认城市
        if (1 != $admin['uid']) {
            $cs = array('IN', $cityIds);
        }

        //订单号，手机号，IP，小区名字查询处理
        if (!empty($param['condition'])) {
            $condition = trim($param['condition']);
            if (is_numeric($condition)) {
                if (strlen($condition) > 15) {
                    //订单号的长度都大于15位
                    $id = addslashes($condition);
                } else {
                    //手机号码
                    $tel_encrypt = D('Orders')->getOrdersTelEncrypt($condition);
                }
            } elseif (preg_match('/(\d{1,3}\.){4}/', "$condition.")) {
                //IP号码
                $ip = $condition;
            } else {
                //小区名字
                $xiaoqu = addslashes($condition);
            }
        }

        //处理城市选择查询
        if (!empty($param['city']) && $param['city'] != 'null') {
            $city = intval($param['city']) == 1 ? '000001' : intval($param['city']);
            if (1 == $admin['uid'] || in_array($city, $cityIds) || $_GET['cityid'] == '000001') {
                $cs = $city;
            }
        }

        //处理订单状态筛选，使用isset引用上面定义的数组条件
        if (!empty($param['status'])) {
            $status = $param['status'];
            if (isset($main['status'][$status]['param']['on'])) {
                $on = $main['status'][$status]['param']['on'];
            }
            if (isset($main['status'][$status]['param']['on_sub'])) {
                $on_sub = $main['status'][$status]['param']['on_sub'];
            }
            if (isset($main['status'][$status]['param']['type_fw'])) {
                $type_fw = $main['status'][$status]['param']['type_fw'];
            }
        }

        //处理订单显号
        if (!empty($param['displaynumber'])) {
            switch ($param['displaynumber']) {
                case '1':
                    $openeye_st = 'null';
                    break;
                case '2':
                    $openeye_st = 0;
                    break;
                case '3':
                    $openeye_st = 1;
                    break;
                default:
                    break;
            }
        }

        //处理排序规则，没有排序规则使用默认排序，否则按照不同订单状态进行不同的排序
        $order = 'priority DESC,on_sub DESC,`on` ASC,visitime ASC,case when on_sub = 9 then time end ASC ,case when on_sub <>9 then time end DESC,time DESC, id DESC';
        if (!empty($main['status'][$status]['order'])) {
            $order = $main['status'][$status]['order'];
        }

        //获取订单列表和分页信息
        $main['info'] = $this->getOrdersList($id, $cs, $xiaoqu, $ip, $tel_encrypt, $time_start, $time_end, $time_real_start, $time_real_end, $nf_time_start, $nf_time_end, $on, $on_sub, $type_fw, $remarks, $openeye_st, $order,10,$arr,$isactivity);

        $list = $main['info']['list'];
        //获取订单ID数组
        $ids = array();
        foreach ($list as $key => $value) {
            $ids[] = $value['id'];
        }

        //获取每个订单的电话号码的归属地
        $result = D('Home/Db/Phonelocation')->getOrderTelLocationByOrderIds($ids);
        foreach ($result as $key => $value) {
            $phoneLocation[$value['id']] = $value['cname'];
        }

        //获取每个订单的电话号码的重复次数
        $result = D('Orders')->getTelnumberRepaetCountByIds($ids);
        foreach ($result as $key => $value) {
            $phoneRepeats[$value['id']] = $value['repeat_count'];
        }

        //获取每个订单的通话次数
        $result = D('LogTelcenterOrdercall')->getOrderCallCountByOrderIds($ids);
        foreach ($result as $key => $value) {
            $callRepeats[$value['id']] = $value['repeat_count'];
        }

        //获取每个订单最近的订单备注添加时间
        $result = D('LogOrderRemarkTime')->getLastLogOrderRemarkTimeByOrderIds($ids);
        foreach ($result as $key => $value) {
            $remarkTime[$value['order_id']] = $value['remark_time'];
        }

        //处理每条记录
        $dateArray = array('年' => 31536000, '月' => 2592000, '周' => 604800, '天' => 86400, '时' => 3600, '分' => 60, '秒' => 1);
        foreach ($list as $key => $value) {
            //电话号码位处理成星号
            $list[$key]['tel'] = substr($value['tel'], 0, 3) . "*****" . substr($value['tel'], -3);
            //重复订单处理
            $list[$key]['phone_repeat_count'] = $phoneRepeats[$value['id']];
            $list[$key]['call_repeat_count'] = $callRepeats[$value['id']];

            //电话号码归属地处理，如果和发单填写的城市不同则显示电话号码归属地
            if (!empty($phoneLocation[$value['id']])) {
                if (false === strpos($phoneLocation[$value['id']], $value['city']) && false === strpos($value['city'], $phoneLocation[$value['id']]) && $value['city'] != '总站') {
                    $list[$key]['phone_location'] = $phoneLocation[$value['id']];
                }
            }

            //处理订单发布时间记录
            $list[$key]['remark_time'] = $remarkTime[$value['id']];

            //当前时间和订单发布时间差
            $time_diff = time() - $list[$key]['time'];
            $list[$key]['timex_ori'] = $time_diff;//记录原时间戳时间差
            foreach ($dateArray as $k => $v) {
                if ($time_diff >= $v) {
                    $num = intval($time_diff / $v);
                    $time_diff -= $num * $v;
                    $real_diff .= $num . $k;
                }
            }
            $list[$key]['timex'] = $real_diff;
            unset($time_diff);
            unset($real_diff);

            //订单及时度
            $time_diff = $list[$key]['callfast_time'];
            foreach ($dateArray as $k => $v) {
                if ($time_diff >= $v) {
                    $num = intval($time_diff / $v);
                    $time_diff -= $num * $v;
                    $real_diff .= $num . $k;
                }
            }
            $list[$key]['timef'] = $real_diff;
            unset($time_diff);
            unset($real_diff);

            //最长呼叫
            $time_diff = $list[$key]['calllong_time'];
            foreach ($dateArray as $k => $v) {
                if ($time_diff >= $v) {
                    $num = intval($time_diff / $v);
                    $time_diff -= $num * $v;
                    $real_diff .= $num . $k;
                }
            }
            $list[$key]['timel'] = $real_diff;
            unset($time_diff);
            unset($real_diff);
        }
        $main['info']['list'] = $list;

        //获取管辖城市信息
        $city = getCityListByCityIds($cityIds);
        $main['city'] = $city;

        //判断是否有转单的权限，因为这个判断要放到循环外，在模板中要写两个循环，故直接在后端判断
        if (check_menu_auth('/order/turnorder/') == true) {
            $main['auth']['turnorder'] = '1';
        }
        //判断是否有查看呼叫记录的权限
        if (check_menu_auth('/voip/voiprecord/') == true) {
            $main['auth']['checkcall'] = '1';
        }


        $this->assign("showList",$arr);
        $this->assign('main', $main);
        $this->display();
    }

    //备用联系方式与发单号码重复
    public function tel_repeat(){
        if($_POST){
            $phone = trim(I('post.phone'));
            $phone = order_tel_encrypt($phone);
            $order = D('Orders')->getRowsByTel($phone);
            if($order>0){
                $this->ajaxReturn(array( 'info' => '该手机号有重复发单信息', 'status' =>0)); // 已存在
            }else{
                $this->ajaxReturn(array(  'status' => 1));
            }
        }
    }
    //申请显示 电话号码 审核操作
    public function displayNumberCheck()
    {
        $admin = getAdminUser();
        $id = I('post.id');
        //检查
        if (empty($id)) {
            $this->ajaxReturn(array('data' => '', 'info' => '缺少参数！', 'status' => 0));
        }
        $save['openeye_st'] = 1;
        $save['openeye_passer'] = $admin['name'];
        $result = M('orders')->where(array('id' => $id))->save($save);
        if ($result) {
            //打日志
            $logdata['op_user'] = $admin['user'];
            $logdata['op_id'] = $admin['id'];
            $logdata['orderid'] = $id;
            $logdata['action'] = 'tongguo';
            $logdata['openeye_st'] = 1;
            $logdata['addtime'] = date('Y-m-d H:m:s');
            M('log_teleye')->add($logdata);
            $this->ajaxReturn(array('data' => '', 'info' => '操作成功！', 'status' => 1));
        }
        $this->ajaxReturn(array('data' => '', 'info' => '审核失败！', 'status' => 0));
    }

    /**
     * [getRepeatOrderListByPhone 手机号重复订单]
     * @return [type] [description]
     */
    public function getRepeatOrderListByPhone()
    {
        $id = I('post.id');
        //检查
        if (empty($id)) {
            $this->ajaxReturn(array('data' => '', 'info' => '缺少参数！', 'status' => 0));
        }
        $order = D('Orders')->getOrdersById($id);
        if (!empty($order)) {
            $result = D('Orders')->getOrdersByTelEncrypt($order['tel_encrypt']);
            if (!empty($result)) {
                $this->assign('list', $result);
                $html = $this->fetch("order_repeat_modal");
                $this->ajaxReturn(array('data' => $html, 'info' => '操作成功！', 'status' => 1));
            } else {
                $this->ajaxReturn(array('data' => '', 'info' => '重复订单列表为空！', 'status' => 0));
            }
        }
        $this->ajaxReturn(array('data' => '', 'info' => '获取失败！', 'status' => 0));
    }

    /**
     * [getRepeatOrderListByIp IP重复订单]
     * @return [type] [description]
     */
    public function getRepeatOrderListByIp()
    {
        $id = I('post.id');
        //检查
        if (empty($id)) {
            $this->ajaxReturn(array('data' => '', 'info' => '缺少参数！', 'status' => 0));
        }
        $order = D('Orders')->getOrdersById($id);
        if (!empty($order)) {
            $result = D('Orders')->getOrdersByIp($order['ip']);
            if (!empty($result)) {
                $this->assign('list', $result);
                $html = $this->fetch("order_repeat_modal");
                $this->ajaxReturn(array('data' => $html, 'info' => '操作成功！', 'status' => 1));
            } else {
                $this->ajaxReturn(array('data' => '', 'info' => '重复订单列表为空！', 'status' => 0));
            }
        }
        $this->ajaxReturn(array('data' => '', 'info' => '获取失败！', 'status' => 0));
    }

    /**
     * [getRecentOperateLog 获取最近操作记录]
     * @return [type] [description]
     */
    public function getRecentOperateLog()
    {
        $id = getAdminUser('id');
        if (!empty($id)) {
            $result = D('LogOrderCsos')->getRecentOperateLogByUserId($id);
            if (!empty($result)) {
                $this->assign('list', $result);
                $html = $this->fetch("recent_operate_log");
                $this->ajaxReturn(array('data' => $html, 'info' => '操作成功！', 'status' => 1));
            } else {
                $this->ajaxReturn(array('data' => '', 'info' => '重复订单列表为空！', 'status' => 0));
            }
        }
        $this->ajaxReturn(array('data' => '', 'info' => '获取失败！', 'status' => 0));
    }

    public function editOrderRemark()
    {
        $id = I('post.id');
        $remarks = I('post.remarks');
        if (!empty($id) && !empty($remarks)) {
            $result = D('Orders')->editOrder($id, array('remarks' => $remarks));
            if ($result) {
                $save = array(
                    'order_id' => $id,
                    'remark_time' => date('Y-m-d H:i:s', time())
                );
                D('LogOrderRemarkTime')->addLogOrderRemarkTime($save);
                $this->ajaxReturn(array('data' => $html, 'info' => '操作成功！', 'status' => 1));
            } else {
                $this->ajaxReturn(array('data' => '', 'info' => '重复订单列表为空！', 'status' => 0));
            }
        }
        $this->ajaxReturn(array('data' => '', 'info' => '修改失败，订单号或订单备注为空！', 'status' => 0));
    }

    /**
     * [modifyOrderTime 修改订单发布时间]
     * @return [type] [description]
     */
    public function modifyOrderTime()
    {
        $data = I('post.');
        if (!empty($data)) {
            //确认是否进行修改该信息
            if ($data['confirm'] == 'false') {
                $orderid = trim($data['orderid']);
                $map['o.id'] = array("EQ", $orderid);
                $result = M('orders')->alias('o')
                    ->field('o.name,o.sex,q.cname,a.qz_area,o.xiaoqu,o.time')
                    ->join('qz_area AS a on o.qx = a.qz_areaid')
                    ->join('qz_quyu AS q on q.cid = o.cs')
                    ->where($map)
                    ->find();
                if ($result) {
                    $date = date("Y-m-d H:i:s", $result['time']);
                    $info =
                        "是否确认修改该订单发布时间<br>业主：" . $result['name'] .
                        "<br>性别：" . $result['sex'] .
                        "<br>城市：" . $result['cname'] .
                        "<br>区县：" . $result['qz_area'] .
                        "<br>小区：" . $result['xiaoqu'] .
                        "<br>原发布时间：" . $date .
                        "<br>修改后发布时间：" . $data['time'];
                    $this->ajaxReturn(array('data' => $info, 'info' => '获取信息成功！', 'status' => 1));
                } else {
                    $this->ajaxReturn(array('data' => $data, 'info' => '无效的订单号码！', 'status' => 0));
                }
            } else {
                $user = getAdminUser('user');
                $orderid = trim($data['orderid']);
                $order = M('orders')->field('id,time,time_real')->where(array('id' => $orderid))->find();
                $pre_time = $order['time'];
                $time_real = $order['time_real'];
                $save['addtime'] = time();
                $save['name'] = $user;
                $save['order_id'] = $orderid;
                $save['now_time'] = strtotime($data['time']);
                $save['pre_time'] = $pre_time;
                //查询是否有此订单号码
                if (empty($order)) {
                    $this->ajaxReturn(array('data' => '', 'info' => '无效的订单号码！', 'status' => 0));
                } else {
                    //判断订单发布时间是否大于真实时间（订单出现时间）
                    if (intval($save['now_time']) < $time_real) {
                        $this->ajaxReturn(array('data' => '', 'info' => '订单发布时间不能低于订单真实发布时间！', 'status' => 0));
                    } else {
                        //修改订单发布时间
                        $res = M('orders')->where(array('id' => $orderid))->save(array('time' => $save['now_time']));
                        if ($res) {
                            //写入日志
                            $result = M('log_orders_change_order_time')->add($save);
                            if ($result) {
                                $this->ajaxReturn(array('data' => '', 'info' => '修改成功!', 'status' => 1));
                            } else {
                                $this->ajaxReturn(array('data' => '', 'info' => '修改成功，写入记录失败，请联系管理员', 'status' => 0));
                            }
                        } else {
                            $res = M('orders')->where(array('id' => $orderid, 'time' => $save['now_time']))->find();
                            if ($res) {
                                $this->ajaxReturn(array('data' => '', 'info' => '修改成功!', 'status' => 1));
                            } else {
                                $this->ajaxReturn(array('data' => '', 'info' => '修改失败，请联系管理员!', 'status' => 0));
                            }
                        }
                    }
                }
            }
        }
        //获取列表
        $orderid = I('get.orderid');
        if (!empty($orderid)) {
            $map['order_id'] = $orderid;
        }
        $count = M('log_orders_change_order_time')->where($map)->count();
        $pageCount = 20;
        if ($count > $pageCount) {
            import('Library.Org.Util.Page');
            $page = new \Page($count, $pageCount);
            $info['page'] = $page->show();
        }
        $info['list'] = M('log_orders_change_order_time')->where($map)->order("addtime desc")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        $main['info'] = $info;
        $this->assign('main', $main);
        $this->display();
    }

    /**
     * 订单详细页面
     * @return [type] [description]
     */
    public function operate()
    {
        if ($_POST) {
            //查询订单信息
            $info = D('Home/Logic/OrdersLogic')->getOrderInfo(I("post.id"));
            //生成手机访问的短网址
            $orderdwz = url_getdwz($info['id']);
            $info['dwz'] = $orderdwz;


            //页面显示微信
            if(!empty($info['wx'])){
                if(strlen($info['wx']) > 6){
                    $info['wx_show'] =  substr_cut($info['wx'],3,3);
                }else{
                    $info['wx_show'] = '****';
                }
            }

            $this->assign("info", $info);
            $this->assign("source_in", $this->source_in);


            //后台转发人数组
            $ids = D("Options")->getOptionNameCC("kf_admin_order_users");
            $names = D("User")->getAdminNamesById($ids['option_value']);
            foreach ($names as $k => $v) {
                $zhuanfaren[] = $v['name'];
            }

            $this->assign("zhuanfaren", $zhuanfaren);
            //获取订单城市和区县
            $city = D("Quyu")->getCityInfoById($info["cs"]);
            $this->assign("city", $city);
            //户型
            $huxing = D("Huxing")->gethx();
            //预算
            $yusuan = D("Jiage")->getJiage();
            //装修方式
            $fangshi = D("Fangshi")->getfs();
            //风格
            $fengge = D("Fengge")->getfg();
            //获取最后审核人信息
            $csos_new = D("OrderCsosNew")->getCsosInfo(I("post.id"));
            //获取 未接通电话短信通知 短信记录
            $logCount = D("LogSmsSend")->getOrderSendLogCount($info["id"], 2);

            //获取 通知业主分配的装修公司 短信记录
            $smsCount = D("LogSmsSend")->getOrderSendLogCount($info["id"], 1);

            //获取订单分配信息
            $company = D("OrderInfo")->getOrderComapny($info["id"]);

            //有分配订单的情况下，查询微信是否发送
            if (count($company) > 0) {
                //获取订单微信发送记录
                $wx = D("LogWxOrdersend")->getWeixinLog($info["id"]);
                if (count($wx) > 0) {
                    $this->assign("wxMark", 1);
                }
            }

            //获取该订单所在城市的的会员公司
            $result = $this->getCompanyList($info["cs"]);

            //如果是已分配公司,默认选中
            foreach ($company as $key => $value) {
                $compnay_id[$value["id"]] = $value["id"];
            }

            foreach ($result[0] as $key => $value) {
                foreach ($value["child"] as $k => $val) {
                    if (array_key_exists($val["id"], $compnay_id)) {
                        $result[0][$key]["child"][$k]["ischeck"] = 1;
                    }
                }
            }

            foreach ($result[1] as $key => $value) {
                foreach ($value["child"] as $k => $val) {
                    if (array_key_exists($val["id"], $compnay_id)) {
                        $result[1][$key]["child"][$k]["ischeck"] = 1;
                    }
                }
            }

            //查询上次分配装修公司
            $fenpei_company = D("OrderInfo")->getLastTypeFw($info["id"], $info["cs"]);

            //本地装修公司
            foreach ($fenpei_company as $k => $val) {
                if ($val["cs"] == $info["cs"]) {
                    $fenpei_now_company[] = $val;
                    unset($fenpei_company[$k]);
                }
            }

            //其他城市
            foreach ($result[1] as $key => $value) {
                foreach ($fenpei_company as $k => $val) {
                    if ($val["cs"] == $key) {
                        $result[1][$key]["fen_company"][] = $val;
                        unset($fenpei_company[$k]);
                    }
                }
            }

            //查询小区历史签单公司
            $history =  D('Home/Logic/OrdersLogic')->orderHistory($info["xiaoqu"], $info['cs']);
            if (count($history) > 0) {
                $this->assign("history", $history);
            }
            //获取城市信息模版
            $this->getCityInfoTmp($info["cs"],true);
            //获取最近30过期的会员信息
            $lostCompany = $this->getLastExpireCompanyList($info["cs"], date("Y-m-d"));

            //获取订单IP是否重复
            $ipCount = D("Orders")->getIpRepaetCountByIds(array($info["id"]));

            if ($ipCount[0]["repeat_count"] > 0) {
                $this->assign("ipMark", $ipCount[0]["repeat_count"]);
            }


            $this->assign("lostCompany", $lostCompany);
            $this->assign("company", $company);
            $this->assign("smsCount", $smsCount);
            $this->assign("fenpei_now_company", $fenpei_now_company);
            $this->assign("nowCompanys", $result[0]);
            $this->assign("otherCompanys", $result[1]);
            $this->assign("logCount", $logCount);
            $this->assign("csos_new", $csos_new);
            $this->assign("status", $this->status);
            $this->assign("keys", $this->keys);
            $this->assign("lf_time", $this->lf_time);
            $this->assign("start_time", $this->start_time);
            $this->assign("shi", $this->shi);
            $this->assign("lxs", $this->lxs);
            $this->assign("fengge", $fengge);
            $this->assign("fangshi", $fangshi);
            $this->assign("yusuan", $yusuan);
            $this->assign("huxing", $huxing);
            if(I('post.is_show') == 1) {
                //获取城市信息
                $quyu = D('Quyu')->getQuyuList();
                $this->assign('quyu', $quyu);

                $tmp = $this->fetch("operateforold");
            }else {
                $tmp = $this->fetch("operate");
            }
            $this->ajaxReturn(['data' => $tmp, 'info' => $info, 'status' => 1]);
        }
    }

    /**
     * 获取订单城市信息页模版
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    private function getCityInfoTmp($cs)
    {
        //获取订单的城市信息
        $info = D("OrderCityInfo")->findCityInfo($cs);
        if (!empty($info)) {
            $this->assign("cityInfo",$info);
        }
    }
    /**
     * 赠单不能生成新单
     * @return [type] [description]
     */
    public function ordertonewchange()
    {
        if ($_POST) {
            if (I("post.text") == "") {
                $this->ajaxReturn(array('errmsg' => "请填写原因", 'code' => 404));
            }
            $order = D("Orders");
            $data = array(
                "order_to_new_remak" => I("post.text"),
                "order_to_new" => 2
            );
            $i = $order->editOrder(I("post.id"), $data);
            if ($i !== false) {
                $this->ajaxReturn(array('code' => 200));
            }
            $this->ajaxReturn(array('errmsg' => "操作失败！", 'code' => 404));
        }
    }

    /**
     * 赠单生成新单
     * @return [type] [description]
     */
    public function ordernftoneworder()
    {
        if ($_POST) {
            //查询订单信息
            $info = D('Home/Logic/OrdersLogic')->getOrderInfo(I("post.id"));
            if ('4' != $info['on'] || !in_array($info['type_fw'], array('2', '4'))) {
                $this->ajaxReturn(array('errmsg' => "只有赠单才允许生成新单!", 'code' => 404));
            }

            if (!empty($info['from_old_orderid'])) {
                $this->ajaxReturn(array('errmsg' => "本订单已经是生成的订单!", 'code' => 404));
            }

            if ('0' != ($info['order_to_new'])) {
                $this->ajaxReturn(array('errmsg' => "本订单已经被处理过了,不能够生成新订单!", 'code' => 404));
            }

            //处理新订单的基本数据，过滤掉无关的数据
            $newInfo = $info;
            unset($newInfo["id"]);
            unset($newInfo['source']);
            unset($newInfo['tel8']);
            $newInfo["tel_encrypt"] = order_tel_encrypt($info["tel8"]);
            $newInfo["customer"] = session("uc_userinfo.id");
            $newInfo["on"] = 0;
            $newInfo['type_fw'] = 0;
            $newInfo['time_real'] = time();
            $newInfo['on_sub'] = 10;
            $newInfo['time'] = time();
            $newInfo['remarks'] = "赠送单再访生成";
            $newInfo['source_type'] = 5;
            $newInfo['lasttime'] = time();
            $newInfo['from_old_orderid'] = $info["id"];
            $newInfo["id"] = date('Ymd') . sprintf("%05d%03d", time() % 86400, mt_rand(1, 1000));
            $newInfo['tel'] = $info["tel_sensitive"];

            $orders = D("Orders");
            $i = $orders->addOrder($newInfo);

            if ($i !== false) {
                //更新原订单标识
                $data = array(
                    "order_to_new" => 1
                );
                $orders->editOrder($info["id"], $data);

                //订单电话表插入数据
                $data = array(
                    "orderid" => $newInfo["id"],
                    "tel8" => $info["tel8"]
                );
                $orders->addTelEncrypt($data);

                //添加订单状态改变日志logorderStatusChange
                $this->orderStatusChange($newInfo["id"], 0, 10);

                //添加原订单的电话数据给新订单
                //获取最近一天的原订单电话记录
                $logTel = D("LogTelcenterOrdercall");
                $date = date("Y-m-d", strtotime("-1 day", strtotime(date("Y-m-d"))));
                $logs = $logTel->getOrderLastLogOneDay($info["id"], $date);

                if (count($logs) > 0) {
                    foreach ($logs as $key => $value) {
                        unset($value["id"]);
                        $value["orderid"] = $newInfo["id"];
                        $all[] = $value;
                    }
                    //添加电话记录
                    $logTel->addAllLog($all);
                }
                $this->ajaxReturn(array('errmsg' => "新订单生成,订单号码： " . $newInfo["id"], 'code' => 200));
            }
            $this->ajaxReturn(array('errmsg' => "生成新单失败!", 'code' => 404));
        }
    }

    /**
     * 签单小区历史
     * @return [type] [description]
     */
    public function history()
    {
        //查询小区历史签单公司
        $history = D('Home/Logic/OrdersLogic')->orderHistory(I("get.xiaoqu"), I("get.cs"));
        $this->assign("list", $history);
        $this->display();
    }

    /**
     * 保存订单部分信息
     * @param  string $value [description]
     * @return void
     */
    public function orderup()
    {
        if (IS_POST) {
            $this->editOrder(I("post."));
        }
        $this->ajaxReturn(['errmsg' => '请求错误', 'code' => 400]);
    }

    /**
     * 申请显号
     * @return [type] [description]
     */
    public function tel_openeye()
    {
        if ($_POST) {
            $result = $this->findOrderEyeInfo(I("post."));
            $this->ajaxReturn(array('errmsg' => $result["errmsg"], 'code' => $result["code"]));
        }
    }

    /**
     * 查询IP真人概率
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function searchrtbasia()
    {
        if ($_GET['ipstr']) {
            $ipstr = $_GET['ipstr'];
            $info['ipstr'] = $ipstr;

            $apikey = OP('APISTORE_BAIDU');

            /*S-获取真人概率信息*/
            $ch = curl_init();
            $url = 'http://apis.baidu.com/rtbasia/non_human_traffic_screening/nht_query?ip=' . $ipstr;
            $header = array(
                "apikey: $apikey",
            );
            // 添加apikey到header
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //设置超时只需要设置一个秒的数量就可以
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            // 执行HTTP请求
            curl_setopt($ch, CURLOPT_URL, $url);
            $res = curl_exec($ch);
            $result = json_decode($res, true);
            if ('Success' == $result['msg']) {
                $istrue = ($result['data']['score'] * 10) . '%';
            } else {
                $istrue = '真人概率未知!';
            }
            $info['istrue'] = $istrue;
            /*E-获取真人概率信息*/

            /*S-获取IP地区信息*/
            $ch = curl_init();
            $url = 'http://apis.baidu.com/apistore/iplookupservice/iplookup?ip=' . $ipstr;
            $header = array(
                "apikey: $apikey",
            );
            // 添加apikey到header
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            //设置超时只需要设置一个秒的数量就可以
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            // 执行HTTP请求
            curl_setopt($ch, CURLOPT_URL, $url);
            $res = curl_exec($ch);
            $result = json_decode($res, true);
            if ('success' == $result['errMsg']) {
                if ('None' == $result['retData']['province']) {
                    $info['position'] = '未知地区';
                } else {
                    $info['position'] = $result['retData']['province'] . $result['retData']['city'] . $result['retData']['district'];
                }
                $info['type'] = $result['retData']['carrier'];
            } else {
                $info['position'] = '未知地区';
                $info['type'] = '未知网络类型';
            }
            /*S-获取IP地区信息*/

            $this->assign('info', $info);
            $this->display();
        }
    }

    //安全百度搜索号码
    public function tel_baidusearch()
    {
        //获取订单号码
        $model = D("Home/Orders");
        $result = $model->findOrderInfoAndTel(I("get.id"));

        if (empty($result["tel8"])) {
            $this->_error("该订单发布电话不存在");
        }

        $baidu = GetPhoneBaiduPageInfoNoNum($result["tel8"]);
        $this->assign('baidu', $baidu);
        $this->assign('type','baidu');
        $this->display();
    }

    /**
     * 360号码搜索
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function tel_360search()
    {
        //获取订单号码
        $model = D("Home/Orders");
        $result = $model->findOrderInfoAndTel(I("get.id"));

        if (empty($result["tel8"])) {
            $this->_error("该订单发布电话不存在");
        }

        $search360 = GetPhone360searchPageInfoNoNum($result["tel8"]);
        $this->assign('search360', $search360);
        $this->assign('type','360');
        $this->display();
    }

    /**
     * 转单操作
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function turnorder()
    {
        if ($_POST) {
            $result = D('Home/Logic/OrdersLogic')->turn_order(I("post."));
            $this->ajaxReturn(array('errmsg' => $result["errmsg"], 'code' => $result["code"]));
        }
        $this->ajaxReturn(array('errmsg' => '请求错误', 'code' => 404));
    }

    /**
     * 定单审核状态
     * @return [type] [description]
     */
    public function orderstatus()
    {
        if ($_POST) {
            $orders = D("Home/Orders");
            $csosModel = D("OrderCsosNew");

            $data = array(
                "chk_customer" => session("uc_userinfo.id")
            );
            $status = I("post.status");
            $status = str_replace("remark_", "", $status);
            $time = time();
            switch ($status) {
                case '1':
                    //次新单
                    $data["on"] = 0;
                    $data["on_sub"] = 9;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 0;
                    break;
                case '2':
                    //待定
                    $data["on"] = 2;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 0;
                    break;
                case '3':
                    //有效未分配
                    $data["on"] = 4;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 0;
                    break;
                case '4':
                    //分单
                    $data["on"] = 4;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 1;
                    break;
                case '5':
                    //分没人跟
                    $data["on"] = 4;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 3;
                    break;
                case '6':
                    //赠单
                    $data["on"] = 4;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 2;
                    break;
                case '7':
                    //赠没人跟
                    $data["on"] = 4;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 4;
                    break;
                case '8':
                    //无效单
                    $data["on"] = 5;
                    $data["on_sub"] = 0;
                    $data["on_sub_wuxiao"] = 0;
                    $data["type_fw"] = 0;
                    break;
            }

            $data["remarks"] = I("post.sub_status");
            //查询订单信息
            $info = D('Home/Logic/OrdersLogic')->getOrderInfo(I("post.id"));

            $orderLogic = new OrdersLogicModel();

            //进行订单信息验证
            if (($message = $orderLogic->valiOrder($info, $status)) !== false) {
                $this->ajaxReturn(['errmsg' => $message, 'code' => 404]);
            }

            //扫单审核为有效单 发布订单时间 字段time 修改为当前审核有效时间
            if ($info["on"] == 0 && $info["on_sub"] == 8 && in_array($status, array(3, 4, 5, 6, 7))) {
                $data["time"] = $time;
            }

            //无效审核为有效单 发布订单时间 字段time 修改为当前审核有效时间
            if ($info["on"] == 5 && in_array($status, array(3, 4, 5, 6, 7))) {
                $data["time"] = $time;
            }

            //没有下次联系时间的 待定单 审核为 有效 修改time订单发布时间为当前审核时间
            if (empty($info["visitime"]) && $info["on"] == 2 && in_array($status, array(3, 4, 5, 6, 7))) {
                $data["time"] = $time;
            }
            $data["lasttime"] = $time;
            $result = $orders->editOrder(I("post.id"), $data);

            if ($result !== false) {
                //获取客服信息
                $kfInfo = D("Adminuser")->findKfInfo(session("uc_userinfo.id"));
                //记录操作统计表
                $csosData = array(
                    "order_id" => I("post.id"),
                    "order_on" => $data["on"],
                    "order_on_sub" => $data["on_sub"],
                    "order_on_sub_wuxiao" => $data["on_sub_wuxiao"],
                    "order_new_type" => $data["on"] == 2 ? 2 : 1,
                    "user_id" => session("uc_userinfo.id"),
                    "user_uid" => session("uc_userinfo.uid"),
                    "kftype" => $kfInfo["kftype"],
                    "kfgroup" => $kfInfo["kfgroup"],
                    "user_name" => session("uc_userinfo.name"),
                    "lasttime" => $time
                );

                //记录操作统计表
                $csos_new = $csosModel->getCsosInfo(I("post.id"));
                if (count($csos_new) > 0) {
                    //订单已审有效，但未分配
                    //已审核分配的订单
                    //依照谁审核有效算谁的原则
                    if (($info["on"] == 4 && $info["type_fw"] == 0 && in_array($status, array(3, 4, 5, 6, 7))) || ($info["on"] == 4 && in_array($info["type_fw"], array(1, 2)))) {
                        unset($csosData['user_id'], $csosData['user_uid'],$csosData['kftype'], $csosData['kfgroup'], $csosData['user_name'], $csosData['lasttime']);
                        if (in_array($status, array(5, 7))) {
                            //删除已分配装修公司
                            D("OrderInfo")->delOrderInfo(I("post.id"));
                        }
                    } elseif ($csos_new["order_on"] == 4 && $info["type_fw"] != 0 && $status == 8) {
                        //以审有效已分配，审核为无效
                        //删除已分配装修公司
                        D("OrderInfo")->delOrderInfo(I("post.id"));
                    }
                    $csosModel->editCsos(I("post.id"), $csosData);
                } else {
                    //添加新记录
                    $csosData["addtime"] = $time;
                    $csosModel->addCsos($csosData);
                }

                $this->orderStatusChange($info["id"], $data["on"], $data["on_sub"], $data["on_sub_wuxiao"]);

                //添加审单日志
                $logData = array(
                    "orderid" => $info["id"],
                    "old_on" => $info["on"],
                    "new_on" => $data["on"],
                    "old_on_sub" => $info["on_sub"],
                    "new_on_sub" => $data["on_sub"],
                    "old_on_sub_wuxiao" => $info["on_sub_wuxiao"],
                    "new_on_sub_wuxiao" => $data["on_sub_wuxiao"],
                    "old_type_fw" => $info["type_fw"],
                    "new_type_fw" => $data["type_fw"],
                    "old_type_zs_sub" => $info["type_zs_sub"],
                    "new_type_zs_sub" => $data["type_zs_sub"],
                    "user_id" => session("uc_userinfo.id"),
                    "user_uid" => session("uc_userinfo.uid"),
                    "old_name" => $csos_new["user_name"],
                    "name" => session("uc_userinfo.name"),
                    "time" => $time,
                );
                $csosModel->addLog($logData);
                if ((I("post.sub_status") !== "") && ($info["remarks"] != I("post.sub_status"))) {
                    $save = array(
                        'order_id' => $info["id"],
                        'remark_time' => date('Y-m-d H:i:s', time())
                    );
                    D('LogOrderRemarkTime')->addLogOrderRemarkTime($save);
                }
                $this->ajaxReturn(array('errmsg' => "订单审核成功！", 'code' => 200));
            }

            $this->ajaxReturn(array('errmsg' => "订单审核失败！", 'code' => 404));
        }
    }

    /**
     * 发送微信
     * @return [type] [description]
     */
    public function sendwx()
    {
        //查询订单信息
        $info = D('Home/Logic/OrdersLogic')->getOrderInfo(I("post.id"));
        if ($info["on"] != 4 && !in_array($info["type_fw"], array(1, 2))) {
            $this->ajaxReturn(array("code" => 404, "errmsg" => "订单尚未分配,请审核后通知装修公司"));
        }

        $wechat_compnay = array_filter(explode(",", I("post.companys")));
        //发送装修公司
        if (count($wechat_compnay) > 0) {
            $weixin = A("Home/Orderweixin");
            $result = $weixin->send_order_to_compnay($wechat_compnay, $info["id"], 2);
            $this->ajaxReturn(array('errmsg' => empty($result) ? "微信推送成功" : $result, 'code' => 200));
        }
        $this->ajaxReturn(array('errmsg' => '请选择装修公司', 'code' => 404));
    }

    /**
     * 发送短信
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function sendsms()
    {
        $result = $this->sendOrderSms(I("post.id"));
        if ($result) {
            $this->ajaxReturn(array('code' => 200));
        }
        $this->ajaxReturn(array('errmsg' => "发送失败", 'code' => 404));
    }

    /**
     * 订单分单操作
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function orderchange()
    {
        if ($_POST) {
            //分单公司
            $companys = I("post.companys");
            $time = time();
            if (count($companys) == 0) {
                $this->ajaxReturn(array('errmsg' => "请选择分单装修公司", 'code' => 404));
            }

            //查询订单信息
            $info = D('Home/Logic/OrdersLogic')->getOrderInfo(I("post.id"));
            if ($info["on"] != 4) {
                $this->ajaxReturn(array("code" => 404, "errmsg" => "订单尚未审核有效,审核后再分配！"));
            }

            foreach ($companys as $key => $value) {
                if (empty($value["type_fw"])) {
                    $this->ajaxReturn(array('errmsg' => "请选择分单状态", 'code' => 404));
                }
                $fen_status = $value["type_fw"];
                if ($value["type_fw"] == 1) {
                    $status = 1;
                }
            }

            if (isset($status)) {
                $fen_status = $status;
            }

            if (count($companys) > 0) {
                //查询订单已分单情况
                $orderCompnay = D("OrderInfo")->getOrderComapny(I("post.id"));
                //删除原有分单记录
                D("OrderInfo")->delOrderInfo(I("post.id"));
                //检查原有分配装修公司和新分配装修公司
                if (count($orderCompnay) > 0) {
                    foreach ($companys as $key => $value) {
                        foreach ($orderCompnay as $k => $val) {
                            if ($value['compnay_id'] == $val["id"]) {
                                $companys[$key]["readtime"] = $val["readtime"];
                                $companys[$key]["isread"] = $val["isread"];
                                unset($orderCompnay[$k]);
                            }
                        }
                    }
                }

                //新的装修公司
                if (count($companys) > 0) {
                    foreach ($companys as $key => $value) {
                        $subData = array(
                            "com" => $value["compnay_id"],
                            "order" => I("post.id"),
                            "type_fw" => $value['type_fw'],
                            "addtime" => $time,
                            "opid" => session("uc_userinfo.id"),
                            "opname" => session("uc_userinfo.name")
                        );

                        if (!empty($value["isread"])) {
                            $subData["isread"] = $value["isread"];
                        }

                        if (!empty($value["readtime"])) {
                            $subData["readtime"] = $value["readtime"];
                        }
                        D("OrderInfo")->addInfo($subData);
                    }
                    $orders = D("Orders");
                    //更新订单状态，分/赠单算分单
                    $data = array(
                        "on" => 4,
                        "type_fw" => $fen_status,
                        "customer" => session("uc_userinfo.id"),
                        "lasttime" => time()
                    );
                    $i = $orders->editOrder($info["id"], $data);
                    if ($i !== false) {
                        //获取 通知业主分配的装修公司 短信记录
                        $smsCount = D("LogSmsSend")->getOrderSendLogCount($info["id"], 1);
                        if ($smsCount == 0) {
                            //自动发送短信
                            $result = $this->sendOrderSms($info["id"]);
                            if ($result !== true) {
                                $result = 0;
                                $errmsg = $result;
                            }
                        }

                        //添加操作日志
                        $source = array(
                            "username" => session("uc_userinfo.name"),
                            "admin_id" => session("uc_userinfo.id"),
                            "orderid" => $info["id"],
                            "type" => $fen_status,
                            "postdata" => json_encode($data),
                            "addtime" => time()
                        );
                        D("LogEditorders")->addLog($source);
                        //获取订单分配信息
                        $company = D("OrderInfo")->getOrderComapny($info["id"]);

                        $this->ajaxReturn(array('code' => 200, "data" => $company, "info" => $result, "msg" => "订单分配成功！ " . $errmsg));
                    }
                }
            }
            $this->ajaxReturn(array('code' => 404, "errmsg" => "分单操作失败！"));
        }
    }

    /**
     * 订单状态改变
     * @param  [type] $orderid       [订单ID]
     * @param  [type] $on            [订单状态]
     * @param  [type] $on_sub        [订单子状态]
     * @param  [type] $on_sub_wuxiao [订单无效子状态]
     * @return [type]                [description]
     */
    public function orderStatusChange($orderid, $on, $on_sub, $on_sub_wuxiao)
    {
        if (empty($orderid)) {
            die();
        }
        $orders = D("Home/Orders");

        //修改订单的状态
        $data = array(
            "chk_customer" => session("uc_userinfo.id")
        );

        if (!empty($on)) {
            $data["on"] = $on;
        }

        if (!empty($on_sub_wuxiao)) {
            $data["on_sub_wuxiao"] = $on_sub_wuxiao;
        }

        if (!empty($on_sub)) {
            $data["on_sub"] = $on_sub;
        }

        $orders->editOrder($orderid, $data);

        //获取订单信息
        $orderInfo = $orders->findOrderInfo($orderid);

        //获取订单状态数据
        $orderStatusChange = D("OrdersStatusChange");
        $orderStatusInfo = $orderStatusChange->findOrderStatus($orderid, $orderInfo["on"], $orderInfo["on_sub"], $orderInfo["on_sub_wuxiao"]);

        if (count($orderStatusInfo) > 0) {
            //添加orders_status_change
            $data = array(
                "user_id" => session("uc_userinfo.id"),
                "user_user" => session("uc_userinfo.name"),
                "time_add" => time()
            );
            $orderStatusChange->editOrderStatus($orderStatusInfo["orderid"], $data);
        } else {
            //添加orders_status_change
            $data = array(
                "orderid" => $orderInfo["id"],
                "on" => $orderInfo["on"],
                "on_sub" => $orderInfo["on_sub"],
                "user_id" => session("uc_userinfo.id"),
                "user_user" => session("uc_userinfo.name"),
                "cs" => $orderInfo["cs"],
                "time_add" => time()
            );
            $orderStatusChange->addOrderStatus($data);
        }

        //获取上一次订单的日志信息
        $log = D("Home/LogOrderSwitchstatus");
        $lastLog = $log->getLastOrderLog($orderInfo["id"]);

        //订单状态改变记录表
        $data = array(
            "orderid" => $orderInfo["id"],
            "last_on" => $lastLog["on"],
            "last_on_sub" => $lastLog["on_sub"],
            "last_on_sub_wuxiao" => $lastLog["on_sub_wuxiao"],
            "on" => $orderInfo["on"],
            "on_sub" => $orderInfo["on_sub"],
            "on_sub_wuxiao" => $orderInfo["on_sub_wuxiao"],
            "last_user_id" => $lastLog["user_id"],
            "user_id" => session("uc_userinfo.id"),
            "last_name" => $lastLog["name"],
            "name" => session("uc_userinfo.name"),
            "addtime" => time()
        );
        $log->addLog($data);
    }

    /**
     * 订单黑名单
     * @return [type] [description]
     */
    public function orderblack()
    {
        if ($_POST) {
            //获取订单编号
            $ids = I("post.id");
            $ids = array_filter(explode(",", str_replace("，", ",", $ids)));
            if (count($ids) == 0) {
                return $this->ajaxReturn(array('code' => 404, "errmsg" => "请添加订单号"));
            }
            $model = D("Orders");

            //查询订单信息
            $result = $model->getOrderInfoList($ids);

            foreach ($result as $key => $value) {
                if ($value['on'] == 4 ) {
                    return $this->ajaxReturn(array('code' => 404, "errmsg" => "输入的订单【". $value["id"]."】是有效单，不可转入黑名单！"));
                }

                $sub = array();
                foreach ($value as $k => $val) {
                    $sub[$k] = $val;
                }
                $data[] = $sub;

                if ($sub['on'] == 4 && $sub["type_fw"] == 1) {
                    $fen_order[] =  $value["id"];
                }
            }

            //删除黑名单数据
            $model->delAllBlack($ids);
            //插入到黑名单表
            $result = $model->addAllBlack($data);
            if ($result !== false) {
                $sub = array();
                //获取实际分单数据
                if (count($fen_order) > 0) {
                    $result = D("Orders")->getOrderCsosNewListById($fen_order);
                    foreach ($result as $key => $value) {
                        $real_order[] = $value;
                    }

                    //更新采集数的分单量
                    foreach ($real_order as $key => $value) {
                        if ($value["src"] == "") {
                            $value["src"] = "none";
                        }

                        //更新分单量
                        D("Yy")->editYyStatisticsOrderCount(date("Y-m-d",$value["lasttime"]),$value["urlid"],$value["src"],2,"order_fen_count");
                        D("Yy")->editYySrcStatisticsOrderCount(date("Y-m-d",$value["lasttime"]),$value["src"],2);

                        //更新实际分单量
                        D("Yy")->editYyStatisticsOrderCount(date("Y-m-d",$value["lasttime"]),$value["urlid"],$value["src"],2,"order_real_fen_count");
                        D("Yy")->editYySummaryOrderCount(date("Y-m-d",$value["lasttime"]),$value["ref"],2,"real_order_count");
                        D("Yy")->editYySrcStatisticsOrderCount(date("Y-m-d",$value["lasttime"]),$value["src"],2,"order_real_fen_count");
                    }
                }

                //删除订单信息
                $model->delAllOrders($ids);
                //获取订单池数据
                $result = D("OrderPool")->getOrderByIds($ids);
                foreach ($result as $key => $value) {
                    $sub[] = array(
                        "order_id" => $value["orderid"],
                        "type" => 3,
                        "data" => json_encode($value)
                    );
                }
                //处理订单池中的数据
                D("OrderPool")->delOrder($ids);

                //获取订单渠道数据
                $result = D("Orders")->getSrcListByIds($ids);
                foreach ($result as $key => $value) {
                    $sub[] = array(
                        "order_id" => $value["orderid"],
                        "type" => 1,
                        "data" => json_encode($value)
                    );
                }
                //删除订单渠道数据
                D("Orders")->delSrcListByIds($ids);

                //获取采集渠道数据
                $result = D("Orders")->getYYSrcListByIds($ids);
                foreach ($result as $key => $value) {
                    if ($value["src"] == "") {
                        $value["src"] = "none";
                    }

                    $sub[] = array(
                        "order_id" => $value["oid"],
                        "type" => 2,
                        "data" => json_encode($value)
                    );
                    //更新采集数据发单数据
                    D("Yy")->editYyStatisticsOrderCount(date("Y-m-d",$value["time"]),$value["urlid"],$value["src"],2,"order_count");
                    D("Yy")->editYySummaryOrderCount(date("Y-m-d",$value["time"]),$value["ref"],2,"order_count");
                    D("Yy")->editYySrcStatisticsOrderCount(date("Y-m-d",$value["time"]),$value["src"],2,"order_count");
                }

                //删除采集渠道数据
                D("Orders")->delSrcListByIds($ids);
                D("Yy")->delSrcListByIds($ids);
                //删除备份表数据
                D("OrdersBlackData")->delData($ids);
                //添加删除数据至备份表
                D("OrdersBlackData")->addAll($sub);

                //添加操作记录
                import('Library.Org.Util.App');
                $app = new \App();
                $ip = $app->get_client_ip();
                foreach ($ids as $key => $value) {
                    $logData[] = array(
                        "remark" => "订单转移黑名单，ID【" . $value . "】",
                        "username" => session("uc_userinfo.name"),
                        "userid" => session("uc_userinfo.id"),
                        "action_id" => $value,
                        "action" => CONTROLLER_NAME . '/' . ACTION_NAME,
                        "logtype" => "orderblack",
                        "time" => date("Y-m-d H:i:s"),
                        "ip" => $ip,
                        'user_agent' => $_SERVER["HTTP_USER_AGENT"]
                    );
                }
                D('LogAdmin')->addAllLog($logData);
                return $this->ajaxReturn(array('code' => 200));
            }
            return $this->ajaxReturn(array('code' => 404, "errmsg" => "操作失败！"));

        } else {
            if (I("get.restore") !== "") {
                //查询黑名单订单信息
                $ids = I("get.restore");
                $ids = array_filter(explode(",", str_replace("，", ",", $ids)));
                if (count($ids) > 0) {
                    $model = D("Orders");
                    $result = $model->getBlackOrderList($ids);
                    $this->assign("list", $result);
                }
            }
            $this->display();
        }
    }

    /**
     * 恢复订单
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function orderrestore()
    {
        if ($_POST) {
            $id = I("post.id");
            //查询黑名单订单信息
            $model = D("Orders");
            $result = $model->getBlackOrderInfo($id);
            if (count($result) > 0) {
                //获取客服分单数据
                $csosData = D("Orders")->getOrderCsosNewListById(array($id));
                $csosData = $csosData[0];
                //添加到订单表中
                $model->addOrder($result);
                // //删除黑名单
                $model->delOrderBlack($id);
                //获取删除原数据
                $blackData = D("OrdersBlackData")->getOrderInfo($id);

                foreach ($blackData as $key => $value) {
                    $list[$value["type"]] = json_decode($value["data"],true);
                }

                //恢复订单数据
                foreach ($list as $key => $value) {
                    $sub = array();
                    foreach ($value as $k => $val) {
                        $sub[$k] = $val;
                    }
                    if ($key == 1) {
                        //orders_source
                        D("Orders")->addOrdersSource($sub);
                    }elseif ($key == 2){
                        //yy_order_info
                        $sourceData = $sub;
                        D("Yy")->addOrdersInfo($sub);
                    }elseif ($key == 3){
                        //order_pool
                        D("OrderPool")->addOrder($sub);
                    }
                }

                if ($sourceData["src"] == "") {
                    $sourceData["src"] = "none";
                }

                //恢复发单数据
                if ($result["on"] == 4 && $result["type_fw"] == 1) {
                    //更新分单量
                    D("Yy")->editYyStatisticsOrderCount(date("Y-m-d",$csosData["lasttime"]),$sourceData["urlid"],$sourceData["src"],1,"order_fen_count");
                    D("Yy")->editYySrcStatisticsOrderCount(date("Y-m-d",$csosData["lasttime"]),$sourceData["src"],1);

                    //更新实际分单量
                    D("Yy")->editYyStatisticsOrderCount(date("Y-m-d",$csosData["lasttime"]),$sourceData["urlid"],$sourceData["src"],1,"order_real_fen_count");
                    D("Yy")->editYySummaryOrderCount(date("Y-m-d",$csosData["lasttime"]),$sourceData["ref"],1,"real_order_count");
                    D("Yy")->editYySrcStatisticsOrderCount(date("Y-m-d",$csosData["lasttime"]),$sourceData["src"],1,"order_real_fen_count");
                }

                //恢复发单数据
                D("Yy")->editYyStatisticsOrderCount(date("Y-m-d",$sourceData["time"]),$sourceData["urlid"],$sourceData["src"],1,"order_count");
                D("Yy")->editYySummaryOrderCount(date("Y-m-d",$sourceData["time"]),$sourceData["ref"],1,"order_count");
                D("Yy")->editYySrcStatisticsOrderCount(date("Y-m-d",$sourceData["time"]),$sourceData["src"],1,"order_count");
                return $this->ajaxReturn(array('code' => 200));
            }
            return $this->ajaxReturn(array('code' => 404, "errmsg" => "操作失败！"));
        }
    }

    /**
     * [getOrdersList 获取订单列表]
     * @param  integer $id [订单ID]
     * @param  integer $cs [订单城市]
     * @param  string $xiaoqu [订单小区]
     * @param  string $ip [订单IP]
     * @param  string $tel_encrypt [订单加密后电话号码]
     * @param  string $time_real_start [真实发布开始时间]
     * @param  string $time_real_end [真实发布结束时间]
     * @param  string $nf_time_start [拿房开始时间]
     * @param  string $nf_time_end [拿房结束时间]
     * @param  boolean $on [订单状态]
     * @param  boolean $on_sub [订单子状态]
     * @param  boolean $type_fw [分单问单]
     * @param  boolean $remarks [订单备注]
     * @param  boolean $openeye_st [显示号码状态]
     * @param  string $order [排序]
     * @param  string $each [每页查询]
     * @return [type]                   [description]
     */
    private function getOrdersList($id = 0, $cs = 0, $xiaoqu = '', $ip = '', $tel_encrypt = '', $time_start = '', $time_end = '', $time_real_start = '', $time_real_end = '', $nf_time_start = '', $nf_time_end = '', $on = false, $on_sub = false, $type_fw = false, $remarks = false, $openeye_st = false, $order = 'time_real DESC', $each = '10',$optn_type_list,$isactivity)
    {
        //查询活动发单位置
        $list = D("Activity")->getActivityIds();
        $ids = array();
        foreach ($list as $key => $value) {
            if ($value["source_id"] != 0) {
                $source = array_filter(explode(",",$value["source_id"]));
                $ids = array_merge($ids,$source);
            }
        }

        import('Library.Org.Util.Page');
        $db = D('Orders');
        $count = $db->getOrdersListCount($id, $cs, $xiaoqu, $ip, $tel_encrypt, $time_start, $time_end, $time_real_start, $time_real_end, $nf_time_start, $nf_time_end, $on, $on_sub, $type_fw, $remarks, $openeye_st,$isactivity,$ids);
        $Page = new \Page($count, $each);
        $result['page'] = $Page->show();
        $result['list'] = $db->getOrdersList($id, $cs, $xiaoqu, $ip, $tel_encrypt, $time_start, $time_end, $time_real_start, $time_real_end, $nf_time_start, $nf_time_end, $on, $on_sub, $type_fw, $remarks, $openeye_st, $order, $Page->firstRow, $Page->listRows,$isactivity,$ids);

        if (in_array(session("uc_userinfo.uid"),$optn_type_list)) {

            foreach ($result['list'] as $key => $value) {
                if (in_array($value["source"],$ids)) {
                    $result['list'][$key]['sourceMark'] = 1;
                }
            }
        }

        return $result;
    }

    /**
     * 申请显号
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function findOrderEyeInfo($data)
    {
        //查询该订单是否已经申请显号
        $model = D("Orders");
        $count = $model->findOrderEyeInfo($data["id"]);
        if ($count == 1) {
            return array("code" => "404", "errmsg" => "该订单已经申请显号或已申请成功,请勿重新申请");
        }

        $subData = array(
            "openeye_st" => 0,
            "openeye_reger" => session("uc_userinfo.name"),
            "openeye_sqly" => $data["text"]
        );

        $result = $model->editOrder($data["id"], $subData);

        if ($result !== false) {
            return array("code" => "200");
        }
        return array("code" => "404", "errmsg" => "操作失败,请重新申请");
    }

    private function editOrder($data)
    {
        $model = D("Orders");
        $id = $data["id"];
        unset($data["id"]);

        //2018-12-20 增加小区落库需求新增start
        if(!empty($data["qx"])&&!empty($data["xiaoqu"])&&!empty($data["zuobiao"])){
            $jingwei = explode(',',$data["zuobiao"]);
            $data["lng"] = $jingwei[0];
            $data["lat"] = $jingwei[1];

            if(count($jingwei)>2){
                $this->ajaxReturn(array("errmsg" => "坐标填写错误", "code" => 400));
            }
            $lng = '/^(\-|\+)?(((\d|[1-9]\d|1[0-7]\d|0{1,3})\.\d{0,6})|(\d|[1-9]\d|1[0-7]\d|0{1,3})|180\.0{0,6}|180)$/';
            $lan = '/^(\-|\+)?([0-8]?\d{1}\.\d{0,6}|90\.0{0,6}|[0-8]?\d{1}|90)$/';
            if(!preg_match($lng,$jingwei[0])||!preg_match($lan,$jingwei[1])){
                $this->ajaxReturn(array("errmsg" => "坐标填写错误!", "code" => 400));
            }

            $existXiaoqu = D("Home/Logic/AuthLogic")->getExistXiaoqu($data["cs"],$data["xiaoqu"]);
            //若区域内不存在该小区,对小区落库做新增操作
            if(!$existXiaoqu){
                D("Home/Logic/AuthLogic")->addCommunityfromOrder($data['cs'],$data["qx"],$data["xiaoqu"], $data["lng"],$data["lat"],$data["xiaoqutype"]);
            }
        }
        unset($data["zuobiao"]);
        $data["xiaoqu_type"] = $data["xiaoqutype"];
        unset($data["xiaoqutype"]);
        //2018-12-20 增加小区落库需求新增end

        if (!empty($data["lftime_other"])) {
            $data["lftime"] = $data["lftime_other"];
            unset($data["lftime_other"]);
        }

        if (!empty($data["start_other"])) {
            $data["start"] = $data["start_other"];
            unset($data["start_other"]);
        }
        $data["lasttime"] = time();
        $data["customer"] = session("uc_userinfo.id");

        if (empty($data["nf_time"])) {
            unset($data['nf_time']);
        }
        if (empty($data["lng"])) {
            unset($data['lng']);
        }
        if (empty($data["lat"])) {
            unset($data['lat']);
        }

        //编辑订单 微信/备用联系人
        if (isset($data['same_tel']) && $data['same_tel'] == 1) {
            //将订单手机号添加到微信字段
            $oDb = new OrdersModel();
            $order_info = $oDb->findOrderInfoAndTel($id);
            $data['wx'] = $order_info['tel8'];
            unset($data['same_tel']);
            unset($order_info);
        } else {
            //验证微信是否更改过
            if (strpos($data['contact_wx'], '*') === false) {
                $data['wx'] = empty($data['contact_wx']) ? '' : $data['contact_wx'];
                unset($data['contact_wx']);
            }
        }
        if (!isset($data['is_wx']) || $data['is_wx'] != 1) {
            $data['is_wx'] = 2;
        }

        //保存订单信息
        $i = $model->editOrder($id, $data);
        if ($i !== false) {
            //添加订单状态操作日志
            $this->orderStatusChange($id);

            //添加操作日志
            $source = array(
                "username" => session("uc_userinfo.name"),
                "admin_id" => session("uc_userinfo.id"),
                "orderid" => $id,
                "type" => 0,
                "postdata" => json_encode($data),
                "addtime" => time()
            );
            D("LogEditorders")->addLog($source);

            $this->ajaxReturn([
                "code" => 200,
                "errmsg" => '保存成功',
                'success_id' => $data['id']
            ]);
        } else {
            $this->ajaxReturn([
                "code" => 400,
                "errmsg" => '保存失败',
                'success_id' => $data['id']
            ]);
        }
    }

    /**
     * 获取当前城市装修公司和相邻的装修公司
     * @param  [type] $cs [description]
     * @return [type]     [description]
     */
    private function getCompanyList($cs)
    {
        //获取相邻装城市
        $citys = D("Quyu")->getCityInfoByIds($cs);
        //获取相邻城市
        $result = D("OrderCityRelation")->getRelationCity($cs);

        //获取订单城市会员列表
        $list = $this->getCompanyDetailsList(array($cs), 2);

        foreach ($list as $key => $value) {
            if (!array_key_exists($value["qx"], $now)) {
                $now[$value["qx"]]['area_id'] = $value["qx"];
                $now[$value["qx"]]['qz_area'] = $value["qz_area"];
            }
            $now[$value["qx"]]["child"][] = $value;
        }

        if (count($result) > 0) {
            foreach ($result as $key => $value) {
                if ($cs != $value["cid"]) {
                    $adjacentCity[] = $value["cid"];
                }
            }
            if (count($adjacentCity) > 0) {
                //相邻城市会员
                $result = $this->getCompanyDetailsList($adjacentCity, 2);

                foreach ($result as $key => $value) {
                    if (!array_key_exists($value["cs"], $other)) {
                        $other[$value["cs"]]["cid"] = $value["cs"];
                        $other[$value["cs"]]["cname"] = $value["cname"];
                    }
                    $other[$value["cs"]]["child"][] = $value;
                }
            }
        }

        return array($now, $other);
    }

    /**
     * 获取装修公司详细信息
     * @param  [type] $companys [description]
     * @param  [type] $on       [description]
     * @return [type]           [description]
     */
    private function getCompanyDetailsList($cs, $on, $companys)
    {
        $companys = D("User")->getCompanyDetailsList($cs, $on, $companys);

        foreach ($companys as $key => $value) {
            //计算到期时间
            $offset = (strtotime($value["end"]) - strtotime(date("Y-m-d"))) / 86400 + 1;

            if ($offset <= 15) {
                $companys[$key]["end_time"] = $offset;
            }

            //计算开始时间是否大于15天
            $offset = (strtotime(date("Y-m-d")) - strtotime($value["start"])) / 86400 + 1;
            if ($offset <= 15) {
                $companys[$key]["newMark"] = 1;
            }
        }
        return $companys;
    }

    private function getLastExpireCompanyList($cs, $date)
    {
        $lostCompany = D("User")->getLastExpireCompanyList($cs, $date);
        foreach ($lostCompany as $key => $value) {
            $offset = (strtotime($date) - strtotime($value["end"])) / 86400 + 1;
            $lostCompany[$key]["day"] = $offset;
        }
        return $lostCompany;
    }

    private function sendOrderSms($id)
    {
        //查询订单信息
        $info = D('Home/Logic/OrdersLogic')->getOrderInfo($id);
        if ($info["on"] != 4 && !in_array($info["type_fw"], array(1, 2))) {
            $this->ajaxReturn(array("code" => 404, "errmsg" => "订单尚未分配,请审核后通知业主"));
        }
        $orderModel = D("Orders");
        //获取业主电话信息
        $tel = $orderModel->findOrderInfoAndTel($info["id"]);

        if (empty($tel["tel8"])) {
            $this->ajaxReturn(array('errmsg' => "业主联系电话未知，发送失败", 'code' => 404));
        }

        //获取分单装修公司
        $company = D("OrderInfo")->getOrderComapny($info["id"]);
        foreach ($company as $key => $value) {
            $ids[] = $value["id"];
        }

        //查询装修公司接单报备信息
        $jdbbList = D("User")->getJdbbList($ids);

        foreach ($jdbbList as $key => $value) {
            if (empty($value["receive_order_tel1"])) {
                $this->ajaxReturn(array('errmsg' => "装修公司【 " . $value["jc"] . " (" . $value["comid"] . ") 】 未设置接单联系方式,请设置后重新发送！", 'code' => 404));
            }
            $datacompany[$key]["jc"] = $value["jc"] ?: '装修公司'; //装修公司简称
            $datacompany[$key]["receive_order_tel1_remark"] = str_replace(array('总', '经理', '老板'), '', $value['receive_order_tel1_remark']);
            $datacompany[$key]["receive_order_tel1"] = $value["receive_order_tel1"];
        }

        $sms = A("Home/Sms");
        $sms_channel = OP('sms_channel', 'yes');
        //发送已分配的公司给业主
        //如果全局配置了 yunrongt  云融正通
        //为什么不直接调用sendSmsQz()?  目前容联云通讯不支持本类短信发送
        //为什么ihuyi 互亿无线要用redis队列发? 对同一个手机号码同一分钟内有发送限制,所以采用的redis队列的方式发送
        if ('yunrongt' == $sms_channel) {
            $result = $sms->send_yunrongt_sms($datacompany, $tel["tel8"], $info["id"]);
        } else {
            //否则就走 ihuyi 互亿无线
            $result = $sms->send_redis_sms($datacompany, $tel["tel8"], $info["id"]);
        }

        return $result;
    }


    /**
     * 新后台发单（客服人员搜索订单）
     * @param  [type] $companys [description]
     * @param  [type] $on       [description]
     * @return [type]           [description]
     */
    public function getoldorders()
    {
        //订单号，手机号查询处理
        if (count(I("get.")) > 0) {
            $condition = I('get.condition');
            $city = I("get.city");
            $begin = I("get.begin");
            $end = I("get.end");
            $pageIndex = I("get.p") == ""?1:I("get.p");
            $pageCount = 10;
            if (empty($condition) && empty($city) && empty($begin) && empty($end)) {
                $this->_error("请输入查询条件");
            }


            if (empty($condition)) {
                if ( empty($city) && empty($begin) && empty($end)) {
                    $this->_error("请输入查询条件");
                }

                if ( !empty($city) && (empty($begin) || empty($end))) {
                    //选择区域时，默认查询时间段为一周。
                    $begin = date("Y-m-d",strtotime("-1 week"));
                    $end = date('Y-m-d');
                }
//
//                if (empty($city) && (!empty($begin) || !empty($end))) {
//                    $this->_error("请选择城市");
//                }
            }

            $condition = trim($condition);
            if (is_numeric($condition)) {
                if (strlen($condition) > 15) {
                    //订单号的长度都大于15位
                    $id = addslashes($condition);
                } else {
                    //手机号码
                    $tel_encrypt = D('Orders')->getOrdersTelEncrypt($condition);
                }
            }

            if (!empty($begin) && !empty($end)) {
                $month_start = strtotime($begin);
                $month_end = strtotime($end) + 86400 -1;
            }

            $count = D('Orders')->getKeFuOrdersListCount($id, $tel_encrypt,$city,$month_start,$month_end);
            if ($count > 0) {
                //获取订单列表和分页信息
                import('Library.Org.Util.Page');
                $page = new \Page($count, $pageCount);
                $show = $page->show();
                $list = D('Orders')->getKeFuOrdersList($id, $tel_encrypt,$city,$month_start,$month_end,$page->firstRow,$page->listRows);
            }


            //获取订单ID数组
            $ids = array();
            foreach ($list as $key => $value) {
                $ids[] = $value['id'];
            }

            //获取每个订单的电话号码的归属地
            $result = D('Home/Db/Phonelocation')->getOrderTelLocationByOrderIds($ids);
            foreach ($result as $key => $value) {
                $phoneLocation[$value['id']] = $value['cname'];
            }

            //获取每个订单的电话号码的重复次数
            $result = D('Orders')->getTelnumberRepaetCountByIds($ids);
            foreach ($result as $key => $value) {
                $phoneRepeats[$value['id']] = $value['repeat_count'];
            }

            //获取每个订单的通话次数
            $result = D('LogTelcenterOrdercall')->getOrderCallCountByOrderIds($ids);
            foreach ($result as $key => $value) {
                $callRepeats[$value['id']] = $value['repeat_count'];
            }

            //获取每个订单最近的订单备注添加时间
            $result = D('LogOrderRemarkTime')->getLastLogOrderRemarkTimeByOrderIds($ids);
            foreach ($result as $key => $value) {
                $remarkTime[$value['order_id']] = $value['remark_time'];
            }

            //获取每个订单的分配公司  feature-650
            $result = D('Orders')->getCompanysByIds($ids);
            foreach ($result as $key => $value) {
                $fenCompanyInfo[$value['id']] = $value['jc'];
            }

            //处理每条记录
            $dateArray = array('年' => 31536000, '月' => 2592000, '周' => 604800, '天' => 86400, '时' => 3600, '分' => 60, '秒' => 1);
            foreach ($list as $key => $value) {
                //电话号码位处理成星号
                $list[$key]['tel'] = substr($value['tel'], 0, 3) . "*****" . substr($value['tel'], -3);
                //重复订单处理
                $list[$key]['phone_repeat_count'] = $phoneRepeats[$value['id']];
                $list[$key]['call_repeat_count'] = $callRepeats[$value['id']];
                // 分单/赠单 显示装修公司名称
                $list[$key]['fen_company_jc'] = $fenCompanyInfo[$value['id']];

                //电话号码归属地处理，如果和发单填写的城市不同则显示电话号码归属地
                if (!empty($phoneLocation[$value['id']])) {
                    if (false === strpos($phoneLocation[$value['id']], $value['city']) && false === strpos($value['city'], $phoneLocation[$value['id']]) && $value['city'] != '总站') {
                        $list[$key]['phone_location'] = $phoneLocation[$value['id']];
                    }
                }

                //处理订单发布时间记录
                $list[$key]['remark_time'] = $remarkTime[$value['id']];

                //当前时间和订单发布时间差
                $time_diff = time() - $list[$key]['time'];
                $list[$key]['timex_ori'] = $time_diff;//记录原时间戳时间差
                foreach ($dateArray as $k => $v) {
                    if ($time_diff >= $v) {
                        $num = intval($time_diff / $v);
                        $time_diff -= $num * $v;
                        $real_diff .= $num . $k;
                    }
                }
                $list[$key]['timex'] = $real_diff;
                unset($time_diff);
                unset($real_diff);

                //订单及时度
                $time_diff = $list[$key]['callfast_time'];
                foreach ($dateArray as $k => $v) {
                    if ($time_diff >= $v) {
                        $num = intval($time_diff / $v);
                        $time_diff -= $num * $v;
                        $real_diff .= $num . $k;
                    }
                }
                $list[$key]['timef'] = $real_diff;
                unset($time_diff);
                unset($real_diff);

                //最长呼叫
                $time_diff = $list[$key]['calllong_time'];
                foreach ($dateArray as $k => $v) {
                    if ($time_diff >= $v) {
                        $num = intval($time_diff / $v);
                        $time_diff -= $num * $v;
                        $real_diff .= $num . $k;
                    }
                }
                $list[$key]['timel'] = $real_diff;
                unset($time_diff);
                unset($real_diff);
            }
        }

        //判断是否有查看呼叫记录的权限
        if (check_menu_auth('/voip/voiprecord/') == true) {
            $checkcall = '1';
        }
        //获取所有城市
        $qLogic = new QuyuLogicModel();
        $cities = $qLogic->getAllCity();

        $this->assign('cities', $cities);
        $this->assign('checkcall', $checkcall);
        $this->assign('keyword', $condition);
        $this->assign('page', $show);
        $this->assign('info', $list);
        $this->display();
    }

    /**
     * 新后台发单（客服发单/视频人员发单）
     * @param  [type] $companys [description]
     * @param  [type] $on       [description]
     * @return [type]           [description]
     */
    public function adpostorder()
    {
        $citys = D("Area")->get_level_select_city();//城市信息
        $info['hx'] = D('Huxing')->gethx();//户型
        $info['fg'] = D('Fengge')->getfg();//风格
        $info['fs'] = D('Fangshi')->getfs();//装修方式
        $info['jg'] = D('Jiage')->getJiage();//可接受价格
        $action = A("Orders");
        $this->assign("lxs", $action->lxs);
        $this->assign("lf_time", $this->lf_time);//量房时间
        $this->assign("start_time", $this->start_time);//开工时间
        $this->assign("info", $info);
        $this->assign('user', session("uc_userinfo"));
        $this->assign("city", json_encode($citys));
        $this->display();
    }

    //后台订单ajax写入数据库，并同步发单
    public function ajaxAdminIn()
    {
        $data = $_POST;
        //写入订单表
        $orderId = $this->fbOrder($data);

        //时间处理
        if (!empty($orderId)) {
            $data['posttime'] = time();
            $data['kaigong'] = !empty($_POST['kaigong']) ? $_POST['kaigong'] : '';
            $data['liangfang'] = !empty($_POST['liangfang']) ? $_POST['liangfang'] : '';
            $data['nfsj'] = !empty($_POST['nfsj']) ? strtotime($_POST['nfsj']) : '';
            $data['orderid'] = $orderId;
            $info = D("OrderInfo")->adminPostAddContent($data);
            if (!empty($info)) {
                //非推广订单自动派送
                if ($data["from"] != 11) {
                    //自动分配订单
                    //获取订单池数据
                    $ponds = D("Home/Logic/OrderPondServiceLogic")->getOrderPondCityList();

                    //分配订单
                    D("Home/Logic/OrderPondServiceLogic")->setToOrder($orderId,$data["cs"],$ponds);
                }
                $this->ajaxReturn(array("data" => $orderId, "info" => "发布成功", "status" => 1));
            } else {
                $this->ajaxReturn(array("data" => "", "info" => "发布失败", "status" => 0));
            }
        } else {
            $this->ajaxReturn(array("data" => $orderId, "info" => "失败", "status" => 0));
        }
    }

    //发布订单
    public function fbOrder($source)
    {
        //客户端IP地址
        $data['ip'] = get_client_ip();
        if (!empty($source['from'])) {
            $data['source_type'] = $source['from'];//来源 1是53客服 2是400电话 3是QQ咨询  11是推广部发单 ,12是微信已审核 ,13是微信未审核 ,14回访客服
        }
        if (!empty($source['source'])) {
            if (intval($source['source']) == 1) {//
                $data['source_src'] = 'kffd';
                $data['source_src_id'] = 164;//测试158
            } else if (intval($source['source']) == 2) {
                $data['source_src'] = 'spjd';//推广视频接待1
                $data['source_src_id'] = 155;
            } else if (intval($source['source']) == 3) {
                $data['source_src'] = 'spjd1';
                $data['source_src_id'] = 163;//测试159，//推广视频接待2
            } else if (intval($source['source']) == 4) {
                $data['source_src'] = 'qz-dh-a2';
                $data['source_src_id'] = 158267;//APP电话咨询
            } else if (intval($source['source']) == 6) {
                $data['source_src'] = 'gzh-zxsw';
                $data['source_src_id'] = 165006;//公众号已审
            } else if (intval($source['source']) == 7) {
                $data['source_src'] = 'gzh-wsw';
                $data['source_src_id'] = 165007;//公众号未审
            }
        }
        //编辑订单 微信/备用联系人
        if (isset($source['same_tel']) && $source['same_tel'] == 1) {
            //将订单手机号添加到微信字段
            $data['wx'] = $source['tel'];
        } else {
            //验证微信是否更改过
            if (strpos($source['contact_wx'], '*') === false) {
                $data['wx'] = empty($source['contact_wx']) ? '' : $source['contact_wx'];
            }
        }
        if (!empty($source['is_wx'])) {
            $data['is_wx'] = $source['is_wx'];//是否微信联系
        }

        if (!empty($source['standby_user'])) {
            $data['standby_user'] = $source['standby_user'];//城市
        }

        if (!empty($source['cs'])) {
            $data['cs'] = $source['cs'];//城市
        }
        if (!empty($source['qx'])) {
            $data['qx'] = $source['qx'];//区县
        }
        if (!empty($source['name'])) {
            $data['name'] = $source['name'];//业主姓名
        }
        if (!empty($source['sex'])) {
            $data['sex'] = $source['sex'];//性别，先生/女士
        }
        if (!empty($source['tel'])) {
            $data['tel'] = trim($source['tel']);//电话
        }
        if (!empty($source['other_contact'])) {
            $data['other_contact'] = $source['other_contact'];//备用联系方式
        }
        if (!empty($source['xiaoqu'])) {
            $data['xiaoqu'] = $source['xiaoqu'];//小区
        }
        if (!empty($source['mianji'])) {
            $data['mianji'] = $source['mianji'];//面积
        }
        if (!empty($source['lxs'])) {
            $data['lxs'] = $source['lxs'];//装修类型详细 1新房 2旧房
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
        if (isset($source["shi"]) && !empty($source["shi"])) {
            $data['shi'] = $source["shi"];//室
        }
        if (isset($source["ting"]) && !empty($source["ting"])) {
            $data['ting'] = $source["ting"];//厅
        }
        if (isset($source["wei"]) && !empty($source["wei"])) {
            $data['wei'] = $source["wei"];//卫
        }
        if (!empty($source['fengge'])) {
            $data['fengge'] = $source['fengge'];//喜欢风格
        }
        if (isset($source["fangshi"]) && !empty($source["fangshi"])) {
            $data['fangshi'] = $source["fangshi"];//装修方式  全包 半包
        }
        if (isset($source["yusuan"]) && !empty($source["yusuan"])) {
            $data['yusuan'] = $source["yusuan"];//预算
        }
        if (isset($source["nfsj"]) && !empty($source["nfsj"])) {
            $data['nf_time'] = $source["nfsj"];//拿房时间
        }
        if (!empty($source['keys'])) {
            $data['keys'] = $source['keys'];//是否有钥匙
        }
        if (!empty($source['kaigong'])) {
            $data['start'] = $source['kaigong'];//开工时间
        }
        if (!empty($source['liangfang'])) {
            $data['lftime'] = $source['liangfang'];//量房时间
        }
        if (!empty($source['xuqiu'])) {
            $data['text'] = $source['xuqiu'];//需求描述
        }

        $data['wzd'] = $this->testOrderComplete($data);//完整度

        //判断 蜘蛛
        import('Library.Org.Util.App');
        $app = new \App();
        if ($app->GetRobot()) {
            $this->ajaxReturn('', 'robot not access！', 0);
            exit();
        }

        //简单判断电话号码 允许数字和短杠的组合  7-18位
        $chktel = D('OrderInfo')->order_chk_tel($data['tel']);
        if ($chktel['status'] == 0) {
            $this->ajaxReturn('', $chktel['info'], 0);
            exit();
        }

        $data['source'] = 164;//来源设置为164，此处对应发单位置设置的值

        $uid = $source["adminuser"];//来源adminuserID
        $userdata = D("OrderInfo")->getAdminUserByUserID($uid);
        $data['zhuanfaren'] = $userdata['name'];

        //检查电话号码是否在黑名单,如果在黑名单就不给生成单子
        $checkTelIsBlock = D('OrderInfo')->checkTelIsBlock($data['tel']);
        if ($checkTelIsBlock) {
            $this->ajaxReturn('', '黑名单号码！0x01', 0);
        }

        //单子入库 新增插入
        $orderadd = D('OrderInfo')->orderpublish($data, "insert"); //传入插入新订单


        //新单子 发送通知业主短信
        //发送订单申请成功 短信
        /*$sms_tel = $data['tel'];
        if (11 == strlen($sms_tel)) {  //如果是11位号码
            $phonesms  = str_replace('{{tel}}', substr($sms_tel,0,3) .'*****'
                                     .  substr($sms_tel,-3), OP('SMS_ORDERFB1'));
            $smsreinfo = $Wwek->SmsSend($sms_tel,$phonesms);
            unset($sms_tel);
        }*/

        if (!empty($orderadd)) {
            return $orderadd;
        }

    }

    //查询订单手机是否注册
    public function checkOrderPhone()
    {
        $phone = trim($_POST['phone']);

        if (!empty($phone)) {
            if (is_numeric($phone)) {
                //获得号码加密值
                $encryptPhone = D('Orders')->order_tel_encrypt($phone);

                $map['tel_encrypt'] = array('EQ', $encryptPhone);
                $map['time_real'] = array('GT', time() - 86400 * 15);

                $orders = M('orders')->where($map)->select();
                if (count($orders) >= 1) {
                    $this->ajaxReturn(array("data" => '1', "info" => "", "status" => 1));
                } else {
                    $this->ajaxReturn(array("data" => '0', "info" => "", "status" => 0));
                }
            }
            $this->ajaxReturn(array("data" => '', "info" => "错误的提交!", "status" => 2));
        } else {
            $this->display();
        }
        die;
    }

    /**
     * ajax查询城市提示信息
     * @param    string $cityid 要查询的城市ID
     * @return    mixed    返回城市信息数组或null
     */
    public function checkOrderCityInfo()
    {
        $cityid = I("post.cs");
        $info = D("OrderCityInfo")->checkOrderCityInfo($cityid);

        if (!empty($info)) {
            $this->ajaxReturn(array("data" => $info, "info" => "查询成功！", "status" => 1));
        } else {
            $this->ajaxReturn(array("data" => $info, "info" => "查询失败！", "status" => 0));
        }
    }

    /*订单完整度测算*/
    public function testOrderComplete($data)
    {
        //定义需要评估的项
        $info = array(
            'name' => trim($data['name']),     //1.联系人 20%
            'sex' => trim($data['sex']),      //2.性别 10%
            'cs' => trim($data['cs']),       //3.城市 10%
            'qx' => trim($data['qx']),       //4.区域  10%
            'xiaoqu' => trim($data['xiaoqu']),   //5.小区名称 20%
            'mianji' => trim($data['mianji']),   //6.面积 10%
            'yusuan' => trim($data['yusuan']),   //7.预算 10%
            'start' => trim($data['start']),    //8.开工时间 10%
        );
        $info = array_filter($info); //过滤评估项中的空项
        $sumweight = 0; //定义初始权重
        foreach ($info as $key => $value) {
            if ('name' == $key || 'xiaoqu' == $key) {
                $sumweight += 20;
            } else {
                $sumweight += 10;
            }
        }
        $complete = $sumweight / 100 * 100;
        return $complete;
    }

    /**
     * 新后台发单统计（统计在线客服订单数据）
     * @param  [type] $companys [description]
     * @param  [type] $on       [description]
     * @return [type]           [description]
     */
    public function countadorder()
    {

        //if($this->check_is_new_server())
        //{
        //    $this->error("您没有相应的权限！");//检测是不是客服新人
        //}

        //load('extend');
        //查询orders表，只查source_type 为 1 2 3 的
        //('白宁宁','','夏秀秀','刘玉洁','解丹丹','孟淑畅')
        $ids = D("Options")->getOptionNameCC("kf_admin_order_users");
        $names = D("User")->getAdminNamesById($ids['option_value']);
        $zhuanfaren = $names;

        if (!empty($_GET['kfname'])) {
            $info = [
                array('name' => $_GET['kfname'])
            ];
        } else {
            $info = $names;
        }

        if (!empty($_GET['start'])) {
            $map['start'] = strtotime($_GET['start'] . ' 00:00:00');//有开始时间
        } else {
            $y = date("Y");
            $m = date("m");
            $d = date("d");
            $map['start'] = mktime(0, 0, 0, $m, $d, $y); //没有开始时间，默认当天00:00:00
        }
        if (!empty($_GET['end'])) {
            $timeend = $_GET['end'] . ' 23:59:59';
            $map['end'] = strtotime($timeend); //有结束时间，结束天的23:59:59
        } else {
            $map['end'] = time();//没有结束时间：当前时间
        }
        //新查询逻辑
        //主要参数 source_type  1为53客服  2为400客服  3为QQ客服
        //转发人 zhuanfaren  in ($info)
        foreach ($info as $k => $v) {
            $zhuanfa[$k] = $v['name'];
        }
        $zhuanfastr = implode(',', $zhuanfa);
        //根据上述2条件查询订单总数组  如果存在时间条件 加入时间条件

        //总
        $total = [];
        foreach ($zhuanfa as $k => $v) {
            $total[$v]['53kefu']['fen'] = 0;
            $total[$v]['53kefu']['zeng'] = 0;
            $total[$v]['400kefu']['fen'] = 0;
            $total[$v]['400kefu']['zeng'] = 0;
            $total[$v]['qqkefu']['fen'] = 0;
            $total[$v]['qqkefu']['zeng'] = 0;
            $total[$v]['wxcheck']['fen'] = 0;
            $total[$v]['wxcheck']['zeng'] = 0;
            $total[$v]['wxnocheck']['fen'] = 0;
            $total[$v]['wxnocheck']['zeng'] = 0;
            $total[$v]['gzhcheck']['fen'] = 0;
            $total[$v]['gzhcheck']['zeng'] = 0;
            $total[$v]['gzhnocheck']['fen'] = 0;
            $total[$v]['gzhnocheck']['zeng'] = 0;
        }
        $orders = D("OrderInfo")->getAllOrders($zhuanfastr, $map);
        foreach ($orders as $k => $v) {
            //统计客服的总分单，总赠单
            foreach ($zhuanfa as $key => $value) {
                if ($value == $v['zhuanfaren']) {
                    if ($v['type_fw'] == 1) {
                        //type_fw 1为分单
                        //主要参数 source_type  1为53客服  2为400客服  3为QQ客服 12. 微信已审核 13.微信未审核
                        //分单总计
                        //$total['fen'][$value] = ($total['fen'][$value]+1);
                        if ($v['source_type'] == 1) {
                            $total[$value]['53kefu']['fen'] = ($total[$value]['53kefu']['fen'] + 1);
                        } elseif ($v['source_type'] == 2) {
                            $total[$value]['400kefu']['fen'] = ($total[$value]['400kefu']['fen'] + 1);
                        } elseif ($v['source_type'] == 3) {
                            $total[$value]['qqkefu']['fen'] = ($total[$value]['qqkefu']['fen'] + 1);
                        }elseif ($v['source_type'] == 12) {
                            $total[$value]['wxcheck']['fen'] = ($total[$value]['wxcheck']['fen'] + 1);
                        } elseif ($v['source_type'] == 13) {
                            $total[$value]['wxnocheck']['fen'] = ($total[$value]['wxnocheck']['fen'] + 1);
                        } elseif ($v['source_type'] == 16) {
                            $total[$value]['gzhcheck']['fen'] = ($total[$value]['gzhcheck']['fen'] + 1);
                        } elseif ($v['source_type'] == 17) {
                            $total[$value]['gzhnocheck']['fen'] = ($total[$value]['gzhnocheck']['fen'] + 1);
                        }
                    } elseif ($v['type_fw'] == 2) {
                        //type_fw 2为赠单
                        //赠单总计
                        //$total['zeng'][$value] = ($total['zeng'][$value]+1);
                        if ($v['source_type'] == 1) {
                            $total[$value]['53kefu']['zeng'] = ($total[$value]['53kefu']['zeng'] + 1);
                        } elseif ($v['source_type'] == 2) {
                            $total[$value]['400kefu']['zeng'] = ($total[$value]['400kefu']['zeng'] + 1);
                        } elseif ($v['source_type'] == 3) {
                            $total[$value]['qqkefu']['zeng'] = ($total[$value]['qqkefu']['zeng'] + 1);
                        }elseif ($v['source_type'] == 12) {
                            $total[$value]['wxcheck']['zeng'] = ($total[$value]['wxcheck']['zeng'] + 1);
                        } elseif ($v['source_type'] == 13) {
                            $total[$value]['wxnocheck']['zeng'] = ($total[$value]['wxnocheck']['zeng'] + 1);
                        } elseif ($v['source_type'] == 16) {
                            $total[$value]['gzhcheck']['zeng'] = ($total[$value]['gzhcheck']['zeng'] + 1);
                        } elseif ($v['source_type'] == 17) {
                            $total[$value]['gzhnocheck']['zeng'] = ($total[$value]['gzhnocheck']['zeng'] + 1);
                        }
                    }
                }
            }
        }
        //后台
        $adtotal = [];
        foreach ($zhuanfa as $k => $v) {
            $adtotal[$v]['53kefu']['fen'] = 0;
            $adtotal[$v]['53kefu']['zeng'] = 0;
            $adtotal[$v]['400kefu']['fen'] = 0;
            $adtotal[$v]['400kefu']['zeng'] = 0;
            $adtotal[$v]['qqkefu']['fen'] = 0;
            $adtotal[$v]['qqkefu']['zeng'] = 0;
            $adtotal[$v]['wxcheck']['fen'] = 0;
            $adtotal[$v]['wxcheck']['zeng'] = 0;
            $adtotal[$v]['wxnocheck']['fen'] = 0;
            $adtotal[$v]['wxnocheck']['zeng'] = 0;
            $adtotal[$v]['gzhcheck']['fen'] = 0;
            $adtotal[$v]['gzhcheck']['zeng'] = 0;
            $adtotal[$v]['gzhnocheck']['fen'] = 0;
            $adtotal[$v]['gzhnocheck']['zeng'] = 0;
        }
        //根据上述2条件 关联qz_admin_post_order 如果存在时间条件则加入 查询出后台发单总数
        $adorders = D("OrderInfo")->getAllAdOrders($zhuanfastr, $map);
        foreach ($adorders as $k => $v) {
            //统计客服后台的总分单，总赠单
            foreach ($zhuanfa as $key => $value) {
                if ($value == $v['zhuanfaren']) {
                    if ($v['type_fw'] == 1) {
                        //主要参数 source_type  1为53客服  2为400客服  3为QQ客服
                        //分单总计
                        //$adtotal['fen'][$value] = ($adtotal['fen'][$value]+1);
                        if ($v['source_type'] == 1) {
                            $adtotal[$value]['53kefu']['fen'] = ($adtotal[$value]['53kefu']['fen'] + 1);
                        } elseif ($v['source_type'] == 2) {
                            $adtotal[$value]['400kefu']['fen'] = ($adtotal[$value]['400kefu']['fen'] + 1);
                        } elseif ($v['source_type'] == 3) {
                            $adtotal[$value]['qqkefu']['fen'] = ($adtotal[$value]['qqkefu']['fen'] + 1);
                        } elseif ($v['source_type'] == 12) {
                            $adtotal[$value]['wxcheck']['fen'] = ($adtotal[$value]['wxcheck']['fen'] + 1);
                        } elseif ($v['source_type'] == 13) {
                            $adtotal[$value]['wxnocheck']['fen'] = ($adtotal[$value]['wxnocheck']['fen'] + 1);
                        } elseif ($v['source_type'] == 16) {
                            $adtotal[$value]['gzhcheck']['fen'] = ($adtotal[$value]['gzhcheck']['fen'] + 1);
                        } elseif ($v['source_type'] == 17) {
                            $adtotal[$value]['gzhnocheck']['fen'] = ($adtotal[$value]['gzhnocheck']['fen'] + 1);
                        }
                    } elseif ($v['type_fw'] == 2) {
                        //赠单总计
                        //$adtotal['zeng'][$value] = ($adtotal['zeng'][$value]+1);
                        if ($v['source_type'] == 1) {
                            $adtotal[$value]['53kefu']['zeng'] = ($adtotal[$value]['53kefu']['zeng'] + 1);
                        } elseif ($v['source_type'] == 2) {
                            $adtotal[$value]['400kefu']['zeng'] = ($adtotal[$value]['400kefu']['zeng'] + 1);
                        } elseif ($v['source_type'] == 3) {
                            $adtotal[$value]['qqkefu']['zeng'] = ($adtotal[$value]['qqkefu']['zeng'] + 1);
                        } elseif ($v['source_type'] == 12) {
                            $adtotal[$value]['wxcheck']['zeng'] = ($adtotal[$value]['wxcheck']['zeng'] + 1);
                        } elseif ($v['source_type'] == 13) {
                            $adtotal[$value]['wxnocheck']['zeng'] = ($adtotal[$value]['wxnocheck']['zeng'] + 1);
                        } elseif ($v['source_type'] == 16) {
                            $adtotal[$value]['gzhcheck']['zeng'] = ($adtotal[$value]['gzhcheck']['zeng'] + 1);
                        } elseif ($v['source_type'] == 17) {
                            $adtotal[$value]['gzhnocheck']['zeng'] = ($adtotal[$value]['gzhnocheck']['zeng'] + 1);
                        }
                    }
                }
            }
        }
        //算积分
        foreach ($zhuanfa as $k => $v) {
            $info[$k]['name'] = $v;
            $info[$k]['53_houtai_fen'] = $adtotal[$v]['53kefu']['fen'];
            $info[$k]['53_qiantai_fen'] = ($total[$v]['53kefu']['fen'] - $adtotal[$v]['53kefu']['fen']);
            $info[$k]['53_houtai_zeng'] = $adtotal[$v]['53kefu']['zeng'];
            $info[$k]['53_qiantai_zeng'] = ($total[$v]['53kefu']['zeng'] - $adtotal[$v]['53kefu']['zeng']);
            $info[$k]['400_houtai_fen'] = $adtotal[$v]['400kefu']['fen'];
            $info[$k]['400_qiantai_fen'] = ($total[$v]['400kefu']['fen'] - $adtotal[$v]['400kefu']['fen']);
            $info[$k]['400_houtai_zeng'] = $adtotal[$v]['400kefu']['zeng'];
            $info[$k]['400_qiantai_zeng'] = ($total[$v]['400kefu']['zeng'] - $adtotal[$v]['400kefu']['zeng']);
            $info[$k]['QQ_houtai_fen'] = $adtotal[$v]['qqkefu']['fen'];
            $info[$k]['QQ_qiantai_fen'] = ($total[$v]['qqkefu']['fen'] - $adtotal[$v]['qqkefu']['fen']);
            $info[$k]['QQ_houtai_zeng'] = $adtotal[$v]['qqkefu']['zeng'];
            $info[$k]['QQ_qiantai_zeng'] = ($total[$v]['qqkefu']['zeng'] - $adtotal[$v]['qqkefu']['zeng']);
            //微信 分/赠
            $info[$k]['wxcheck_houtai_fen'] = $adtotal[$v]['wxcheck']['fen'];
            $info[$k]['wxcheck_houtai_zeng'] = $adtotal[$v]['wxcheck']['zeng'];
            $info[$k]['wxnocheck_houtai_fen'] = $adtotal[$v]['wxnocheck']['fen'];
            $info[$k]['wxnocheck_houtai_zeng'] = $adtotal[$v]['wxnocheck']['zeng'];
            $info[$k]['wx_houtai_fen'] = $adtotal[$v]['wxcheck']['fen'] + $adtotal[$v]['wxnocheck']['fen'];
            $info[$k]['wx_houtai_zeng'] = $adtotal[$v]['wxcheck']['zeng'] + $adtotal[$v]['wxnocheck']['zeng'];

            $info[$k]['wxcheck_qiantai_zeng'] = ($total[$v]['wxcheck']['zeng']-$adtotal[$v]['wxcheck']['zeng']);
            $info[$k]['wxcheck_qiantai_fen'] = ($total[$v]['wxcheck']['fen']-$adtotal[$v]['wxcheck']['fen']);
            $info[$k]['wxnocheck_qiantai_zeng'] = ($total[$v]['wxnocheck']['zeng']-$adtotal[$v]['wxnocheck']['zeng']);
            $info[$k]['wxnocheck_qiantai_fen'] = ($total[$v]['wxnocheck']['fen']-$adtotal[$v]['wxnocheck']['fen']);
            $info[$k]['wx_qiantai_zeng'] = ($total[$v]['wxnocheck']['zeng'] + $total[$v]['wxcheck']['zeng']-$adtotal[$v]['wxnocheck']['zeng'] - $adtotal[$v]['wxcheck']['zeng']);
            $info[$k]['wx_qiantai_fen'] = ($total[$v]['wxnocheck']['fen'] + $total[$v]['wxcheck']['fen']-$adtotal[$v]['wxnocheck']['fen'] - $adtotal[$v]['wxcheck']['fen']);

            $info[$k]['wxcheck_total_zeng'] = $total[$v]['wxcheck']['zeng'];
            $info[$k]['wxcheck_total_fen'] = $total[$v]['wxcheck']['fen'];
            $info[$k]['wxnocheck_total_zeng'] = $total[$v]['wxnocheck']['zeng'];
            $info[$k]['wxnocheck_total_fen'] = $total[$v]['wxnocheck']['fen'];
            $info[$k]['wx_total_zeng'] = ($total[$v]['wxnocheck']['zeng'] + $total[$v]['wxcheck']['zeng']) * 0.1;
            $info[$k]['wx_total_fen'] = $total[$v]['wxnocheck']['fen'] * 0.2 + $total[$v]['wxcheck']['fen'];

            // 公众号
            $info[$k]['gzhcheck_houtai_fen'] = $adtotal[$v]['gzhcheck']['fen'];
            $info[$k]['gzhcheck_houtai_zeng'] = $adtotal[$v]['gzhcheck']['zeng'];
            $info[$k]['gzhnocheck_houtai_fen'] = $adtotal[$v]['gzhnocheck']['fen'];
            $info[$k]['gzhnocheck_houtai_zeng'] = $adtotal[$v]['gzhnocheck']['zeng'];
            $info[$k]['gzh_houtai_fen'] = $adtotal[$v]['gzhcheck']['fen'] + $adtotal[$v]['gzhnocheck']['fen'];
            $info[$k]['gzh_houtai_zeng'] = $adtotal[$v]['gzhcheck']['zeng'] + $adtotal[$v]['gzhnocheck']['zeng'];

            $info[$k]['gzhcheck_qiantai_zeng'] = ($total[$v]['gzhcheck']['zeng']-$adtotal[$v]['gzhcheck']['zeng']);
            $info[$k]['gzhcheck_qiantai_fen'] = ($total[$v]['gzhcheck']['fen']-$adtotal[$v]['gzhcheck']['fen']);
            $info[$k]['gzhnocheck_qiantai_zeng'] = ($total[$v]['gzhnocheck']['zeng']-$adtotal[$v]['gzhnocheck']['zeng']);
            $info[$k]['gzhnocheck_qiantai_fen'] = ($total[$v]['gzhnocheck']['fen']-$adtotal[$v]['gzhnocheck']['fen']);
            $info[$k]['gzh_qiantai_zeng'] = ($total[$v]['gzhnocheck']['zeng'] + $total[$v]['gzhcheck']['zeng']-$adtotal[$v]['gzhnocheck']['zeng'] - $adtotal[$v]['gzhcheck']['zeng']);
            $info[$k]['gzh_qiantai_fen'] = ($total[$v]['gzhnocheck']['fen'] + $total[$v]['gzhcheck']['fen']-$adtotal[$v]['gzhnocheck']['fen'] - $adtotal[$v]['gzhcheck']['fen']);

            $info[$k]['gzhcheck_total_zeng'] = $total[$v]['gzhcheck']['zeng'];
            $info[$k]['gzhcheck_total_fen'] = $total[$v]['gzhcheck']['fen'];
            $info[$k]['gzhnocheck_total_zeng'] = $total[$v]['gzhnocheck']['zeng'];
            $info[$k]['gzhnocheck_total_fen'] = $total[$v]['gzhnocheck']['fen'];
            $info[$k]['gzh_total_zeng'] = ($total[$v]['gzhnocheck']['zeng'] + $total[$v]['gzhcheck']['zeng']) * 0.1;
            $info[$k]['gzh_total_fen'] = $total[$v]['gzhnocheck']['fen'] * 0.2 + $total[$v]['gzhcheck']['fen'];

            $info[$k]['houtai_fen_zong'] = ($info[$k]['53_houtai_fen'] + $info[$k]['400_houtai_fen'] + $info[$k]['QQ_houtai_fen'] );//白宁宁 后台发单 分单合计 积分1 后台合计分*1
            $info[$k]['qiantai_fen_zong'] = ($info[$k]['53_qiantai_fen'] + $info[$k]['QQ_qiantai_fen']) * 0.2 + $info[$k]['400_qiantai_fen'] * 1;//白宁宁 前台发单 分单合计 前台合计分 53和QQ客服*0.2/400客服*1
            $info[$k]['houtai_zeng_zong'] = ($info[$k]['53_houtai_zeng'] + $info[$k]['400_houtai_zeng'] + $info[$k]['QQ_houtai_zeng']) * 0.1;//白宁宁 后台赠单 赠单合计 积分0.1   后台合计赠 合计*0.1
            $info[$k]['qiantai_zeng_zong'] = ($info[$k]['53_qiantai_zeng'] + $info[$k]['400_qiantai_zeng'] + $info[$k]['QQ_qiantai_zeng']) * 0.1;//白宁宁 前台赠单 赠单合计 积分0.1 前台合计赠  合计*0.1
            $info[$k]['fen'] = $info[$k]['53_houtai_fen'] + $info[$k]['53_qiantai_fen'] + $info[$k]['400_houtai_fen'] + $info[$k]['400_qiantai_fen'] + $info[$k]['QQ_houtai_fen'] + $info[$k]['QQ_qiantai_fen'] + $info[$k]['wxcheck_houtai_fen'] + $info[$k]['wxnocheck_houtai_fen']+ $info[$k]['wxcheck_qiantai_fen'] + $info[$k]['wxnocheck_qiantai_fen'] + $info[$k]['gzhcheck_houtai_fen'] + $info[$k]['gzhcheck_qiantai_fen'] + $info[$k]['gzhnocheck_houtai_fen'] + $info[$k]['gzhnocheck_qiantai_fen'];//总分单
            $info[$k]['zeng'] = $info[$k]['53_houtai_zeng'] + $info[$k]['53_qiantai_zeng'] + $info[$k]['400_houtai_zeng'] + $info[$k]['400_qiantai_zeng'] + $info[$k]['QQ_houtai_zeng'] + $info[$k]['QQ_qiantai_zeng'] + $info[$k]['wxcheck_houtai_zeng'] + $info[$k]['wxnocheck_houtai_zeng']+$info[$k]['wxcheck_qiantai_zeng'] + $info[$k]['wxnocheck_qiantai_zeng']  + $info[$k]['gzhcheck_houtai_zeng'] + $info[$k]['gzhcheck_qiantai_zeng'] + $info[$k]['gzhnocheck_houtai_zeng'] + $info[$k]['gzhnocheck_qiantai_zeng'];//总赠单
            $info[$k]['zong'] = $info[$k]['houtai_fen_zong'] + $info[$k]['qiantai_fen_zong'] + $info[$k]['houtai_zeng_zong'] + $info[$k]['qiantai_zeng_zong'] + $info[$k]['wx_total_fen'] + $info[$k]['wx_total_zeng'] + $info[$k]['gzh_total_fen'] + $info[$k]['gzh_total_zeng'];
        }
        $heji = [];
        foreach ($info as $k => $v) {
            $heji['fen'] += $v['fen'];
            $heji['zeng'] += $v['zeng'];
            $heji['zong'] += $v['zong'];
            $heji['53_houtai_fen'] += $v['53_houtai_fen'];
            $heji['53_qiantai_fen'] += $v['53_qiantai_fen'];
            $heji['53_houtai_zeng'] += $v['53_houtai_zeng'];
            $heji['53_qiantai_zeng'] += $v['53_qiantai_zeng'];
            $heji['400_houtai_fen'] += $v['400_houtai_fen'];
            $heji['400_qiantai_fen'] += $v['400_qiantai_fen'];
            $heji['400_houtai_zeng'] += $v['400_houtai_zeng'];
            $heji['400_qiantai_zeng'] += $v['400_qiantai_zeng'];
            $heji['QQ_houtai_fen'] += $v['QQ_houtai_fen'];
            $heji['QQ_qiantai_fen'] += $v['QQ_qiantai_fen'];
            $heji['QQ_houtai_zeng'] += $v['QQ_houtai_zeng'];
            $heji['QQ_qiantai_zeng'] += $v['QQ_qiantai_zeng'];
            $heji['houtai_fen_zong'] += $v['houtai_fen_zong'];
            $heji['qiantai_fen_zong'] += $v['qiantai_fen_zong'];
            $heji['houtai_zeng_zong'] += $v['houtai_zeng_zong'];
            $heji['qiantai_zeng_zong'] += $v['qiantai_zeng_zong'];
            $heji['wxcheck_houtai_fen'] += $v['wxcheck_houtai_fen'];
            $heji['wxcheck_houtai_zeng'] += $v['wxcheck_houtai_zeng'];
            $heji['wxnocheck_houtai_fen'] += $v['wxnocheck_houtai_fen'];
            $heji['wxnocheck_houtai_zeng'] += $v['wxnocheck_houtai_zeng'];
            $heji['wx_houtai_fen'] += $v['wxcheck_houtai_fen'] + $v['wxnocheck_houtai_fen'];
            $heji['wx_houtai_zeng'] += $v['wxcheck_houtai_zeng'] + $v['wxnocheck_houtai_zeng'];

            $heji['wxcheck_qiantai_zeng'] += $v['wxcheck_qiantai_zeng'];
            $heji['wxcheck_qiantai_fen'] += $v['wxcheck_qiantai_fen'];
            $heji['wxnocheck_qiantai_zeng'] += $v['wxnocheck_qiantai_zeng'];
            $heji['wxnocheck_qiantai_fen'] += $v['wxnocheck_qiantai_fen'];
            $heji['wx_qiantai_zeng'] += $v['wx_qiantai_zeng'];
            $heji['wx_qiantai_fen'] += $v['wx_qiantai_fen'];

            $heji['wxcheck_total_zeng'] += $v['wxcheck_total_zeng'];
            $heji['wxcheck_total_fen'] += $v['wxcheck_total_fen'];
            $heji['wxnocheck_total_zeng'] += $v['wxnocheck_total_zeng'];
            $heji['wxnocheck_total_fen'] += $v['wxnocheck_total_fen'];
            $heji['wx_total_zeng'] += $v['wx_total_zeng'];
            $heji['wx_total_fen'] += $v['wx_total_fen'];

            $heji['gzhcheck_total_zeng'] += $v['gzhcheck_total_zeng'];
            $heji['gzhcheck_total_fen'] += $v['gzhcheck_total_fen'];
            $heji['gzhnocheck_total_zeng'] += $v['gzhnocheck_total_zeng'];
            $heji['gzhnocheck_total_fen'] += $v['gzhnocheck_total_fen'];
            $heji['gzh_total_zeng'] += $v['gzh_total_zeng'];
            $heji['gzh_total_fen'] += $v['gzh_total_fen'];
        }
        if (!empty($_GET['start'])) {
            $time = $_GET['start'];
            if (!empty($_GET['end'])) {
                $time .= ' -- ' . $_GET['end'];
            } else {
                $time .= ' -- ' . '至今';
            }
        } else {
            $time = date("Y-m-d", time());
            if (!empty($_GET['end'])) {
                $time .= ' -- ' . $_GET['end'];
            } else {
                $time .= ' -- ' . date("Y-m-d", time());
            }
        }
        $this->assign('zhuanfaren', $zhuanfaren);
        $this->assign('thing', $_GET);
        $this->assign('heji', $heji);
        $this->assign('time', $time);
        $this->assign('info', $info);

        $this->display();
    }

    /*
     * 新后台发单统计（统计在线客服订单每日数据）
    */
    public function countadorderday()
    {
        $ids = D("Options")->getOptionNameCC("kf_admin_order_users");
        $names = D("User")->getAdminNamesById($ids['option_value']);
//        $info = $names;

        foreach ($names as $k => $v) {
            $zhuanfa[$k] = $v['name'];
        }
        $zhuanfastr = implode(',', $zhuanfa);
         //此月份天数&时间段
        if (!empty($_GET['month'])) {
            $date = explode('-', $_GET['month']);//获取的月份
            $y = date("Y");//当前年
            $m = date("m");//当前月
            if ($date[0] == $y and $date[1] == $m) {
                $days = date("d");//当前月：天数等于当前月份天数
                $timestart = $_GET['month'] . '-01' . ' 00:00:00';
                $time['start'] = strtotime($timestart);
                $time['end'] = time();
            } else {
//                $days = cal_days_in_month(CAL_GREGORIAN, $date[1], $date[0]);//此月份的天数
                $days = date("t", strtotime($_GET['month']));
                $timestart = $_GET['month'] . '-01' . ' 00:00:00';
                $time['start'] = strtotime($timestart);
                $timeend = $_GET['month'] . '-' . $days . ' 23:59:59';
                $time['end'] = strtotime($timeend);
            }
        } else {
            $y = date("Y");//当前年
            $m = date("m");//当前月
            $days = date("d");//当前月：天数等于当前月份天数
            $timestart = $y . '-' . $m . '-01' . ' 00:00:00';
            $time['start'] = strtotime($timestart);
            $time['end'] = time();
        }

        //总
        $orders = D("OrderInfo")->getAllOrder($zhuanfastr, $time);
        $total = [];
        for ($i = 1; $i <= $days; $i++) {
            $total[$i]['53kefu']['fen'] = 0;
            $total[$i]['53kefu']['zeng'] = 0;
            $total[$i]['400kefu']['fen'] = 0;
            $total[$i]['400kefu']['zeng'] = 0;
            $total[$i]['qqkefu']['fen'] = 0;
            $total[$i]['qqkefu']['zeng'] = 0;
            $total[$i]['wxcheck']['fen'] = 0;
            $total[$i]['wxcheck']['zeng'] = 0;
            $total[$i]['wxnocheck']['fen'] = 0;
            $total[$i]['wxnocheck']['zeng'] = 0;
            $total[$i]['gzhcheck']['fen'] = 0;
            $total[$i]['gzhcheck']['zeng'] = 0;
            $total[$i]['gzhnocheck']['fen'] = 0;
            $total[$i]['gzhnocheck']['zeng'] = 0;
        }
        for ($i = 1; $i <= $days; $i++) {
            if (!empty($_GET['month'])) {
                $start = $_GET['month'] . '-' . $i . ' 00:00:00';
                $end = $_GET['month'] . '-' . $i . ' 23:59:59';
                $starttime = strtotime($start);
                $endtime = strtotime($end);
            } else {
                $start = date("Y-m") . '-' . $i . ' 00:00:00';
                $end = date("Y-m") . '-' . $i . ' 23:59:59';
                $starttime = strtotime($start);
                $endtime = strtotime($end);
            }
            foreach ($orders as $order) {
                //统计客服的总分单，总赠单
                $lasttime = $order['lasttime'];
                if ($lasttime >= $starttime && $lasttime <= $endtime) {
                    if ($order['type_fw'] == 1) {
                        //type_fw 1为分单
                        //主要参数 source_type  1为53客服  2为400客服  3为QQ客服
                        //分单总计
                        //$total['fen'][$value] = ($total['fen'][$value]+1);
                        if ($order['source_type'] == 1) {
                            $total[$i]['53kefu']['fen'] = ($total[$i]['53kefu']['fen'] + 1);
                        } elseif ($order['source_type'] == 2) {
                            $total[$i]['400kefu']['fen'] = ($total[$i]['400kefu']['fen'] + 1);
                        } elseif ($order['source_type'] == 3) {
                            $total[$i]['qqkefu']['fen'] = ($total[$i]['qqkefu']['fen'] + 1);
                        }elseif ($order['source_type'] == 12) {
                            $total[$i]['wxcheck']['fen'] = ($total[$i]['wxcheck']['fen'] + 1);
                        } elseif ($order['source_type'] == 13) {
                            $total[$i]['wxnocheck']['fen'] = ($total[$i]['wxnocheck']['fen'] + 1);
                        } elseif ($order['source_type'] == 16) {
                            $total[$i]['gzhcheck']['fen'] = ($total[$i]['gzhcheck']['fen'] + 1);
                        } elseif ($order['source_type'] == 17) {
                            $total[$i]['gzhnocheck']['fen'] = ($total[$i]['gzhnocheck']['fen'] + 1);
                        }
                    } elseif ($order['type_fw'] == 2) {
                        //type_fw 2为赠单
                        //赠单总计
                        //$total['zeng'][$value] = ($total['zeng'][$value]+1);
                        if ($order['source_type'] == 1) {
                            $total[$i]['53kefu']['zeng'] = ($total[$i]['53kefu']['zeng'] + 1);
                        } elseif ($order['source_type'] == 2) {
                            $total[$i]['400kefu']['zeng'] = ($total[$i]['400kefu']['zeng'] + 1);
                        } elseif ($order['source_type'] == 3) {
                            $total[$i]['qqkefu']['zeng'] = ($total[$i]['qqkefu']['zeng'] + 1);
                        }elseif ($order['source_type'] == 12) {
                            $total[$i]['wxcheck']['zeng'] = ($total[$i]['wxcheck']['zeng'] + 1);
                        } elseif ($order['source_type'] == 13) {
                            $total[$i]['wxnocheck']['zeng'] = ($total[$i]['wxnocheck']['zeng'] + 1);
                        } elseif ($order['source_type'] == 16) {
                            $total[$i]['gzhcheck']['zeng'] = ($total[$i]['gzhcheck']['zeng'] + 1);
                        } elseif ($order['source_type'] == 17) {
                            $total[$i]['gzhnocheck']['zeng'] = ($total[$i]['gzhnocheck']['zeng'] + 1);
                        }
                    }
                }
            }
        }

        //后
        $adorders = D("OrderInfo")->getAllAdOrder($zhuanfastr, $time);
        $adtotal = [];
        for ($i = 1; $i <= $days; $i++) {
            $adtotal[$i]['53kefu']['fen'] = 0;
            $adtotal[$i]['53kefu']['zeng'] = 0;
            $adtotal[$i]['400kefu']['fen'] = 0;
            $adtotal[$i]['400kefu']['zeng'] = 0;
            $adtotal[$i]['qqkefu']['fen'] = 0;
            $adtotal[$i]['qqkefu']['zeng'] = 0;
            $adtotal[$i]['wxcheck']['fen'] = 0;
            $adtotal[$i]['wxcheck']['zeng'] = 0;
            $adtotal[$i]['wxnocheck']['fen'] = 0;
            $adtotal[$i]['wxnocheck']['zeng'] = 0;
            $adtotal[$i]['gzhcheck']['fen'] = 0;
            $adtotal[$i]['gzhcheck']['zeng'] = 0;
            $adtotal[$i]['gzhnocheck']['fen'] = 0;
            $adtotal[$i]['gzhnocheck']['zeng'] = 0;
        }
        for ($i = 1; $i <= $days; $i++) {
            if (!empty($_GET['month'])) {
                $start = $_GET['month'] . '-' . $i . ' 00:00:00';
                $end = $_GET['month'] . '-' . $i . ' 23:59:59';
                $starttime = strtotime($start);
                $endtime = strtotime($end);
            } else {
                $start = date("Y-m") . '-' . $i . ' 00:00:00';
                $end = date("Y-m") . '-' . $i . ' 23:59:59';
                $starttime = strtotime($start);
                $endtime = strtotime($end);
            }

            foreach ($adorders as $k => $v) {
                $lasttime = $v['lasttime'];
                //统计客服后台的总分单，总赠单
                if ($lasttime > $starttime && $lasttime < $endtime) {
                    if ($v['type_fw'] == 1) {
                        //主要参数 source_type  1为53客服  2为400客服  3为QQ客服
                        //分单总计
                        //$adtotal['fen'][$value] = ($adtotal['fen'][$value]+1);
                        if ($v['source_type'] == 1) {
                            $adtotal[$i]['53kefu']['fen'] = ($adtotal[$i]['53kefu']['fen'] + 1);
                        } elseif ($v['source_type'] == 2) {
                            $adtotal[$i]['400kefu']['fen'] = ($adtotal[$i]['400kefu']['fen'] + 1);
                        } elseif ($v['source_type'] == 3) {
                            $adtotal[$i]['qqkefu']['fen'] = ($adtotal[$i]['qqkefu']['fen'] + 1);
                        } elseif ($v['source_type'] == 12) {
                            $adtotal[$i]['wxcheck']['fen'] = ($adtotal[$i]['wxcheck']['fen'] + 1);
                        } elseif ($v['source_type'] == 13) {
                            $adtotal[$i]['wxnocheck']['fen'] = ($adtotal[$i]['wxnocheck']['fen'] + 1);
                        } elseif ($v['source_type'] == 16) {
                            $adtotal[$i]['gzhcheck']['fen'] = ($adtotal[$i]['gzhcheck']['fen'] + 1);
                        } elseif ($v['source_type'] == 17) {
                            $adtotal[$i]['gzhnocheck']['fen'] = ($adtotal[$i]['gzhnocheck']['fen'] + 1);
                        }
                    } elseif ($v['type_fw'] == 2) {
                        //赠单总计
                        //$adtotal['zeng'][$value] = ($adtotal['zeng'][$value]+1);
                        if ($v['source_type'] == 1) {
                            $adtotal[$i]['53kefu']['zeng'] = ($adtotal[$i]['53kefu']['zeng'] + 1);
                        } elseif ($v['source_type'] == 2) {
                            $adtotal[$i]['400kefu']['zeng'] = ($adtotal[$i]['400kefu']['zeng'] + 1);
                        } elseif ($v['source_type'] == 3) {
                            $adtotal[$i]['qqkefu']['zeng'] = ($adtotal[$i]['qqkefu']['zeng'] + 1);
                        } elseif ($v['source_type'] == 12) {
                            $adtotal[$i]['wxcheck']['zeng'] = ($adtotal[$i]['wxcheck']['zeng'] + 1);
                        } elseif ($v['source_type'] == 13) {
                            $adtotal[$i]['wxnocheck']['zeng'] = ($adtotal[$i]['wxnocheck']['zeng'] + 1);
                        } elseif ($v['source_type'] == 16) {
                            $adtotal[$i]['gzhcheck']['zeng'] = ($adtotal[$i]['gzhcheck']['zeng'] + 1);
                        } elseif ($v['source_type'] == 17) {
                            $adtotal[$i]['gzhnocheck']['zeng'] = ($adtotal[$i]['gzhnocheck']['zeng'] + 1);
                        }
                    }
                }
            }
        }

        //遍历时间
        for ($i = 1; $i <= $days; $i++) {
            if (!empty($_GET['month'])) {
                $times = $_GET['month'] . '-' . $i;
                $info[$i]['name'] = date("Y-m-d", strtotime($times));
            } else {
                $times = date("Y-m") . '-' . $i;
                $info[$i]['name'] = date("Y-m-d", strtotime($times));
            }
            $info[$i]['53_houtai_fen'] = $adtotal[$i]['53kefu']['fen'];
            $info[$i]['53_qiantai_fen'] = ($total[$i]['53kefu']['fen'] - $adtotal[$i]['53kefu']['fen']);
            $info[$i]['53_houtai_zeng'] = $adtotal[$i]['53kefu']['zeng'];
            $info[$i]['53_qiantai_zeng'] = ($total[$i]['53kefu']['zeng'] - $adtotal[$i]['53kefu']['zeng']);
            $info[$i]['400_houtai_fen'] = $adtotal[$i]['400kefu']['fen'];
            $info[$i]['400_qiantai_fen'] = ($total[$i]['400kefu']['fen'] - $adtotal[$i]['400kefu']['fen']);
            $info[$i]['400_houtai_zeng'] = $adtotal[$i]['400kefu']['zeng'];
            $info[$i]['400_qiantai_zeng'] = ($total[$i]['400kefu']['zeng'] - $adtotal[$i]['400kefu']['zeng']);
            $info[$i]['QQ_houtai_fen'] = $adtotal[$i]['qqkefu']['fen'];
            $info[$i]['QQ_qiantai_fen'] = ($total[$i]['qqkefu']['fen'] - $adtotal[$i]['qqkefu']['fen']);
            $info[$i]['QQ_houtai_zeng'] = $adtotal[$i]['qqkefu']['zeng'];
            $info[$i]['QQ_qiantai_zeng'] = ($total[$i]['qqkefu']['zeng'] - $adtotal[$i]['qqkefu']['zeng']);

            //微信渠道
            $info[$i]['wxcheck_houtai_zeng'] = $adtotal[$i]['wxcheck']['zeng'];
            $info[$i]['wxcheck_houtai_fen'] = $adtotal[$i]['wxcheck']['fen'];
            $info[$i]['wxnocheck_houtai_zeng'] = $adtotal[$i]['wxnocheck']['zeng'];
            $info[$i]['wxnocheck_houtai_fen'] = $adtotal[$i]['wxnocheck']['fen'];
            $info[$i]['wx_houtai_zeng'] = $adtotal[$i]['wxnocheck']['zeng'] + $adtotal[$i]['wxcheck']['zeng'];
            $info[$i]['wx_houtai_fen'] = $adtotal[$i]['wxnocheck']['fen'] + $adtotal[$i]['wxcheck']['fen'];

            $info[$i]['wxcheck_qiantai_zeng'] = ($total[$i]['wxcheck']['zeng']-$adtotal[$i]['wxcheck']['zeng']);
            $info[$i]['wxcheck_qiantai_fen'] = ($total[$i]['wxcheck']['fen']-$adtotal[$i]['wxcheck']['fen']);
            $info[$i]['wxnocheck_qiantai_zeng'] = ($total[$i]['wxnocheck']['zeng']-$adtotal[$i]['wxnocheck']['zeng']);
            $info[$i]['wxnocheck_qiantai_fen'] = ($total[$i]['wxnocheck']['fen']-$adtotal[$i]['wxnocheck']['fen']);
            $info[$i]['wx_qiantai_zeng'] = ($total[$i]['wxnocheck']['zeng'] + $total[$i]['wxcheck']['zeng']-$adtotal[$i]['wxnocheck']['zeng'] - $adtotal[$i]['wxcheck']['zeng']);
            $info[$i]['wx_qiantai_fen'] = ($total[$i]['wxnocheck']['fen'] + $total[$i]['wxcheck']['fen']-$adtotal[$i]['wxnocheck']['fen'] - $adtotal[$i]['wxcheck']['fen']);

            $info[$i]['wxcheck_total_zeng'] = $total[$i]['wxcheck']['zeng'];
            $info[$i]['wxcheck_total_fen'] = $total[$i]['wxcheck']['fen'];
            $info[$i]['wxnocheck_total_zeng'] = $total[$i]['wxnocheck']['zeng'];
            $info[$i]['wxnocheck_total_fen'] = $total[$i]['wxnocheck']['fen'];
            $info[$i]['wx_total_zeng'] = ($total[$i]['wxnocheck']['zeng'] + $total[$i]['wxcheck']['zeng']) * 0.1;
            $info[$i]['wx_total_fen'] = $total[$i]['wxnocheck']['fen'] * 0.2 + $total[$i]['wxcheck']['fen'];

            // 公众号
            $info[$i]['gzhcheck_houtai_fen'] = $adtotal[$i]['gzhcheck']['fen'];
            $info[$i]['gzhcheck_houtai_zeng'] = $adtotal[$i]['gzhcheck']['zeng'];
            $info[$i]['gzhnocheck_houtai_fen'] = $adtotal[$i]['gzhnocheck']['fen'];
            $info[$i]['gzhnocheck_houtai_zeng'] = $adtotal[$i]['gzhnocheck']['zeng'];
            $info[$i]['gzh_houtai_fen'] = $adtotal[$i]['gzhcheck']['fen'] + $adtotal[$i]['gzhnocheck']['fen'];
            $info[$i]['gzh_houtai_zeng'] = $adtotal[$i]['gzhcheck']['zeng'] + $adtotal[$i]['gzhnocheck']['zeng'];

            $info[$i]['gzhcheck_qiantai_zeng'] = ($total[$i]['gzhcheck']['zeng']-$adtotal[$i]['gzhcheck']['zeng']);
            $info[$i]['gzhcheck_qiantai_fen'] = ($total[$i]['gzhcheck']['fen']-$adtotal[$i]['gzhcheck']['fen']);
            $info[$i]['gzhnocheck_qiantai_zeng'] = ($total[$i]['gzhnocheck']['zeng']-$adtotal[$i]['gzhnocheck']['zeng']);
            $info[$i]['gzhnocheck_qiantai_fen'] = ($total[$i]['gzhnocheck']['fen']-$adtotal[$i]['gzhnocheck']['fen']);
            $info[$i]['gzh_qiantai_zeng'] = ($total[$i]['gzhnocheck']['zeng'] + $total[$i]['gzhcheck']['zeng']-$adtotal[$i]['gzhnocheck']['zeng'] - $adtotal[$i]['gzhcheck']['zeng']);
            $info[$i]['gzh_qiantai_fen'] = ($total[$i]['gzhnocheck']['fen'] + $total[$i]['gzhcheck']['fen']-$adtotal[$i]['gzhnocheck']['fen'] - $adtotal[$i]['gzhcheck']['fen']);

            $info[$i]['gzhcheck_total_zeng'] = $total[$i]['gzhcheck']['zeng'];
            $info[$i]['gzhcheck_total_fen'] = $total[$i]['gzhcheck']['fen'];
            $info[$i]['gzhnocheck_total_zeng'] = $total[$i]['gzhnocheck']['zeng'];
            $info[$i]['gzhnocheck_total_fen'] = $total[$i]['gzhnocheck']['fen'];
            $info[$i]['gzh_total_zeng'] = ($total[$i]['gzhnocheck']['zeng'] + $total[$i]['gzhcheck']['zeng']) * 0.1;
            $info[$i]['gzh_total_fen'] = $total[$i]['gzhnocheck']['fen'] * 0.2 + $total[$i]['gzhcheck']['fen'];

            $info[$i]['houtai_fen_zong'] = ($info[$i]['53_houtai_fen'] + $info[$i]['400_houtai_fen'] + $info[$i]['QQ_houtai_fen']);//白宁宁 后台发单 分单合计 积分1 后台合计分*1
            $info[$i]['qiantai_fen_zong'] = ($info[$i]['53_qiantai_fen'] + $info[$i]['QQ_qiantai_fen']) * 0.2 + $info[$i]['400_qiantai_fen'] * 1;//白宁宁 前台发单 分单合计 前台合计分 53和QQ客服*0.2/400客服*1
            $info[$i]['houtai_zeng_zong'] = ($info[$i]['53_houtai_zeng'] + $info[$i]['400_houtai_zeng'] + $info[$i]['QQ_houtai_zeng']) * 0.1;//白宁宁 后台赠单 赠单合计 积分0.1   后台合计赠 合计*0.1
            $info[$i]['qiantai_zeng_zong'] = ($info[$i]['53_qiantai_zeng'] + $info[$i]['400_qiantai_zeng'] + $info[$i]['QQ_qiantai_zeng']) * 0.1;//白宁宁 前台赠单 赠单合计 积分0.1 前台合计赠  合计*0.1
            $info[$i]['fen'] = $info[$i]['53_houtai_fen'] + $info[$i]['53_qiantai_fen'] + $info[$i]['400_houtai_fen'] + $info[$i]['400_qiantai_fen'] + $info[$i]['QQ_houtai_fen'] + $info[$i]['QQ_qiantai_fen'] + $info[$i]['wxcheck_houtai_fen'] + $info[$i]['wxnocheck_houtai_fen']+ $info[$i]['wxcheck_qiantai_fen'] + $info[$i]['wxnocheck_qiantai_fen']  + $info[$i]['gzhcheck_houtai_fen'] + $info[$i]['gzhcheck_qiantai_fen'] + $info[$i]['gzhnocheck_houtai_fen'] + $info[$i]['gzhnocheck_qiantai_fen'];//总分单
            $info[$i]['zeng'] = $info[$i]['53_houtai_zeng'] + $info[$i]['53_qiantai_zeng'] + $info[$i]['400_houtai_zeng'] + $info[$i]['400_qiantai_zeng'] + $info[$i]['QQ_houtai_zeng'] + $info[$i]['QQ_qiantai_zeng'] + $info[$i]['wxcheck_houtai_zeng'] + $info[$i]['wxnocheck_houtai_zeng']+$info[$i]['wxcheck_qiantai_zeng'] + $info[$i]['wxnocheck_qiantai_zeng']  + $info[$i]['gzhcheck_houtai_zeng'] + $info[$i]['gzhcheck_qiantai_zeng'] + $info[$i]['gzhnocheck_houtai_zeng'] + $info[$i]['gzhnocheck_qiantai_zeng'];//总赠单
            $info[$i]['zong'] = $info[$i]['houtai_fen_zong'] + $info[$i]['qiantai_fen_zong'] + $info[$i]['houtai_zeng_zong'] + $info[$i]['qiantai_zeng_zong'] + $info[$i]['wx_total_fen'] + $info[$i]['wx_total_zeng'] + $info[$i]['gzh_total_fen'] + $info[$i]['gzh_total_zeng'];
        }
        //合计
        $heji = [];
        foreach ($info as $k => $v) {
            $heji['fen'] += $v['fen'];
            $heji['zeng'] += $v['zeng'];
            $heji['zong'] += $v['zong'];
            $heji['53_houtai_fen'] += $v['53_houtai_fen'];
            $heji['53_qiantai_fen'] += $v['53_qiantai_fen'];
            $heji['53_houtai_zeng'] += $v['53_houtai_zeng'];
            $heji['53_qiantai_zeng'] += $v['53_qiantai_zeng'];
            $heji['400_houtai_fen'] += $v['400_houtai_fen'];
            $heji['400_qiantai_fen'] += $v['400_qiantai_fen'];
            $heji['400_houtai_zeng'] += $v['400_houtai_zeng'];
            $heji['400_qiantai_zeng'] += $v['400_qiantai_zeng'];
            $heji['QQ_houtai_fen'] += $v['QQ_houtai_fen'];
            $heji['QQ_qiantai_fen'] += $v['QQ_qiantai_fen'];
            $heji['QQ_houtai_zeng'] += $v['QQ_houtai_zeng'];
            $heji['QQ_qiantai_zeng'] += $v['QQ_qiantai_zeng'];
            $heji['houtai_fen_zong'] += $v['houtai_fen_zong'];
            $heji['qiantai_fen_zong'] += $v['qiantai_fen_zong'];
            $heji['houtai_zeng_zong'] += $v['houtai_zeng_zong'];
            $heji['qiantai_zeng_zong'] += $v['qiantai_zeng_zong'];
            //微信
            $heji['wxcheck_houtai_zeng'] += $v['wxcheck_houtai_zeng'];
            $heji['wxcheck_houtai_fen'] += $v['wxcheck_houtai_fen'];
            $heji['wxnocheck_houtai_zeng'] += $v['wxnocheck_houtai_zeng'];
            $heji['wxnocheck_houtai_fen'] += $v['wxnocheck_houtai_fen'];
            $heji['wx_houtai_zeng'] += $v['wx_houtai_zeng'];
            $heji['wx_houtai_fen'] += $v['wx_houtai_fen'];

            $heji['wxcheck_qiantai_zeng'] += $v['wxcheck_qiantai_zeng'];
            $heji['wxcheck_qiantai_fen'] += $v['wxcheck_qiantai_fen'];
            $heji['wxnocheck_qiantai_zeng'] += $v['wxnocheck_qiantai_zeng'];
            $heji['wxnocheck_qiantai_fen'] += $v['wxnocheck_qiantai_fen'];
            $heji['wx_qiantai_zeng'] += $v['wx_qiantai_zeng'];
            $heji['wx_qiantai_fen'] += $v['wx_qiantai_fen'];

            $heji['wxcheck_total_zeng'] += $v['wxcheck_total_zeng'];
            $heji['wxcheck_total_fen'] += $v['wxcheck_total_fen'];
            $heji['wxnocheck_total_zeng'] += $v['wxnocheck_total_zeng'];
            $heji['wxnocheck_total_fen'] += $v['wxnocheck_total_fen'];
            $heji['wx_total_zeng'] += $v['wx_total_zeng'];
            $heji['wx_total_fen'] += $v['wx_total_fen'];

            $heji['gzhcheck_total_zeng'] += $v['gzhcheck_total_zeng'];
            $heji['gzhcheck_total_fen'] += $v['gzhcheck_total_fen'];
            $heji['gzhnocheck_total_zeng'] += $v['gzhnocheck_total_zeng'];
            $heji['gzhnocheck_total_fen'] += $v['gzhnocheck_total_fen'];
            $heji['gzh_total_zeng'] += $v['gzh_total_zeng'];
            $heji['gzh_total_fen'] += $v['gzh_total_fen'];
        }

        $this->assign('info', $info);
        $this->assign('thing', $_GET);
        $this->assign('heji', $heji);

        $this->display();
    }

    /**
     * 新后台发单统计（编辑在线客服成员）
     * @param  [type]        [description]
     * @param  [type]            [description]
     * @return [type]           [description]
     */
    public function editkefuusers()
    {
        /*"夏秀秀",
         "解丹丹",
         "白宁宁",
         "孟淑畅",
         "刘玉洁"*/
        if ($_POST['idstr']) {
            $idstr = I("post.idstr");
            //把idsstr存入options项
            $names = D("Adminuser")->upKfNameByIds($idstr);
            //根据idstr查询
            /*foreach ($names as $k => $v) {
                 $namesstr .= $v['name'].',';
             }
             $namesstr = substr($namesstr, 0,-1);*/
            if ($names) {
                $this->ajaxReturn(array('data' => $names, 'info' => "操作成功", 'status' => 1));
            } else {
                $this->ajaxReturn(array('data' => $names, 'info' => "操作失败", 'status' => 0));
            }

        }
        $ids = D("Options")->getOptionNameCC("kf_admin_order_users");
        $idarr = explode(',', $ids['option_value']);

        //查询会员
        $users = D("Adminuser")->getKfNames();

        $this->assign('checked', $idarr);
        $this->assign('user', $users);
        $this->display();
    }

    /*
    *	后台发单-检测城市是否有真会员
    */
    public function checkRealCompany()
    {
        $cid = I("post.cs");
        $companys = D("User")->getRealVipCompanys($cid);
        $num = count($companys);
        if ($num > 0) {
            $this->ajaxReturn(array("data" => '1', "info" => "", "status" => 1));
        } else {
            $this->ajaxReturn(array("data" => '1', "info" => "", "status" => 0));
        }

    }

    public function jiajuorders()
    {
        //订单号，手机号查询处理
        $condition = I('get.condition');
        if (!empty($condition)) {
            $condition = trim($condition);
            //获取订单列表和分页信息
            $jiajuOrder = new JiajuOrdersLogicModel();
            $info = $jiajuOrder->getOrdersInfo($condition);
        }
        $this->assign('info', $info);
        $this->display();
    }

    public function addjiajuorders(){
        $orderid = I("get.id");
        if($orderid){
            //查询订单信息
            $jiajuLogic = new JiajuOrdersLogicModel();
            $data = $jiajuLogic->getOrdersInfo($orderid);
            $this->assign('list',$data[0]);
        }
        $citys = D("Area")->get_level_select_city();//城市信息
        $info['hx'] = D('Huxing')->gethx();//户型
        $info['fg'] = D('Fengge')->getfg();//风格
        $info['fs'] = D('Fangshi')->getfs();//装修方式
        $info['jg'] = D('Jiage')->getJiajuJiage();//可接受价格
        $this->assign("lf_time", $this->lf_time);//量房时间
        $this->assign("start_time", $this->start_time);//开工时间
        $this->assign("info", $info);
        $this->assign('user', session("uc_userinfo"));
        $this->assign("city", json_encode($citys));
        //获取城市信息
        $city = D('Quyu')->getJiajuQuyuList();
        foreach ($city as $key => $value) {
            $city[$key]["char"] = strtoupper(substr($value["px_abc"],0,1));
            $city[$key]["char_name"] = $city[$key]["char"]." ".$value["cname"];
        }
        $this->assign('city', $city);
        //获取订单城市和区县
        $quyu = D("Quyu")->getJiajuCityInfoByIds($data[0]["cs"]);
        $this->assign("quyu",$quyu);
        $this->display();
    }

    //后台订单ajax写入数据库，并同步发单
    public function saveJiajuOrder()
    {
        //判断 蜘蛛
        import('Library.Org.Util.App');
        $app = new \App();
        if ($app->GetRobot()) {
            $this->ajaxReturn('', 'robot not access！', 0);
            exit();
        }

        $data = I('post.');
        //简单判断电话号码 允许数字和短杠的组合  7-18位
        if(!$data['edit_id']){
            $chktel = D('OrderInfo')->order_chk_tel($data['mobilemain']);
            if ($chktel['status'] == 0) {
                $this->ajaxReturn('', $chktel['info'], 0);
                exit();
            }
        }
        //检查电话号码是否在黑名单,如果在黑名单就不给生成单子
        $checkTelIsBlock = D('OrderInfo')->checkTelIsBlock($data['mobilemain']);
        if ($checkTelIsBlock) {
            $this->ajaxReturn('', '黑名单号码！0x01', 0);
        }
        //写入订单表
        $jiajuLogic = new JiajuOrdersLogicModel();
        $orderId = $jiajuLogic->saveJiajuOrder($data);
        //时间处理
        if (!empty($orderId)) {
            if ($data['edit_id']) {
            //编辑
                $this->ajaxReturn(array("data" => '', "info" => "编辑成功", "status" => 1));
            } else {
                //新增
                $data['posttime'] = time();
                $data['kaigong'] = !empty($_POST['kaigong']) ? $_POST['kaigong'] : '';
                $data['liangfang'] = !empty($_POST['liangfang']) ? $_POST['liangfang'] : '';
                $data['nfsj'] = !empty($_POST['nfsj']) ? strtotime($_POST['nfsj']) : '';
                $data['orderid'] = $orderId;
                $info = D("OrderInfo")->adminPostAddContent($data);
                if (!empty($info)) {
                    $this->ajaxReturn(array("data" => $orderId, "info" => "发布成功", "status" => 1));
                } else {
                    $this->ajaxReturn(array("data" => "", "info" => "发布失败", "status" => 0));
                }
            }
        } else {
            $this->ajaxReturn(array("data" => $orderId, "info" => "失败", "status" => 0));
        }
    }

    /**
     * 新后台所有订单页面
     */
    public function allorder()
    {
        $admin = getAdminUser();
        $cityIds = getAdminCityIds(true, true, true);

        //户型
        $main['huxing'] = D("Huxing")->gethx();
        //预算
        $main['yusuan'] = D("Jiage")->getJiage();
        //装修方式
        $main['fangshi'] = D("Fangshi")->getfs();
        //分赠单
        $main['type_fw'] = self::$type_fw;
        //装修类型
        $main['lx'] = self::$lx;

        //获取查询条件
        $param = I('get.');

        //初始化定义查询条件，目的是规范输入
        $cs = false;
        $qx = false;
        //修改后的订单发布时间筛选
        //不为待定或者没选时间段,限定查询最近90天的单子
        $time_start = empty($param['time_start']) ? strtotime("-90 day") : strtotime($param['time_start']);
        $time_end = empty($param['time_end']) ? '' : strtotime($param['time_end'] . ' 23:59:59');

        $yusuan = empty($param['yusuan']) ? false : $param['yusuan'];
        $type_fw = empty($param['type_fw']) ? false : $param['type_fw'];
        $lx = empty($param['lx']) ? false : $param['lx'];
        $fangshi = empty($param['fangshi']) ? false : $param['fangshi'];

        $order = 'time_real DESC';

        //默认城市
        if (1 != $admin['uid']) {
            $cs = array('IN', $cityIds);
        }

        //处理城市选择查询
        if (!empty($param['cs']) && $param['cs'] != 'null') {
            $city = intval($param['cs']) == 1 ? '000001' : intval($param['cs']);
            if (1 == $admin['uid'] || in_array($city, $cityIds) || $_GET['cs'] == '000001') {
                $cs = $city;

            }
        }
        if (!empty($param['qx']) && $param['qx'] != 'null') {
            $qx = $param['qx'];
        }

        //获取订单列表和分页信息
        $main['info'] = $this->getAllOrdersList($time_start,$time_end,$yusuan,$type_fw,$lx,$fangshi,$cs,$qx,$order,20);

        $list = $main['info']['list'];
        //获取订单ID数组
        $ids = array();
        foreach ($list as $key => $value) {
            $ids[] = $value['id'];
        }

        //获取每个订单的通话次数
        $result = D('LogTelcenterOrdercall')->getOrderCallCountByOrderIds($ids);
        foreach ($result as $key => $value) {
            $callRepeats[$value['id']] = $value['repeat_count'];
        }

        //处理每条记录
        foreach ($list as $key => $value) {
            //重复订单处理
            $list[$key]['call_repeat_count'] = $callRepeats[$value['id']];
        }
        $main['info']['list'] = $list;

        //获取管辖城市信息
        $city = getCityListByCityIds($cityIds);
        $main['cs'] = $city;

        //判断是否有查看呼叫记录的权限
        if (check_user_menu_auth('/voip/voiprecord/') == true) {
            $main['auth']['checkcall'] = '1';
        }

        $this->assign('main', $main);
        $this->display();
    }

    /**
     * [getOrdersList 获取订单列表]
     * @param  integer $id [订单ID]
     * @param  integer $cs [订单城市]
     * @param  string $xiaoqu [订单小区]
     * @param  string $ip [订单IP]
     * @param  string $tel_encrypt [订单加密后电话号码]
     * @param  string $time_real_start [真实发布开始时间]
     * @param  string $time_real_end [真实发布结束时间]
     * @param  string $nf_time_start [拿房开始时间]
     * @param  string $nf_time_end [拿房结束时间]
     * @param  boolean $on [订单状态]
     * @param  boolean $on_sub [订单子状态]
     * @param  boolean $type_fw [分单问单]
     * @param  boolean $remarks [订单备注]
     * @param  boolean $openeye_st [显示号码状态]
     * @param  string $order [排序]
     * @param  string $each [每页查询]
     * @return [type]                   [description]
     */
    private function getAllOrdersList($time_start = false, $time_end = false,$yusuan = false, $type_fw = false, $lx = false, $fangshi = false, $cs = false, $qx = false,$order = 'time_real DESC', $each = '20')
    {
        import('Library.Org.Util.Page');
        $db = D('Orders');
        $count = $db->getAllOrdersListCount($time_start, $time_end, $yusuan, $type_fw, $lx, $fangshi, $cs, $qx, $order, $each);
        $Page = new \Page($count, $each);
        $result['page'] = $Page->show();
        $result['list'] = $db->getAllOrdersList($time_start, $time_end, $yusuan, $type_fw, $lx, $fangshi, $cs, $qx, $order, $Page->firstRow, $Page->listRows);

        foreach ($result['list'] as $key => $value) {
            $result['list'][$key]["mj_mark"] = $value["mianji"] >= 150?1:0;
            $result['list'][$key]["ys_mark"] = ($value["yusuan"] >= 22 && $value["yusuan"] <= 25)?1:0;
            $result['list'][$key]["companys"] = str_replace(",","   ",$value["companys"]);
        }
        
        return $result;
    }
    /**
     * 获取城市区域信息
     */
    public function getCityInfoById()
    {
        $city = D("Quyu")->getCityInfoById(I('cs'));
        $this->ajaxReturn($city);
    }

    /**
     * 渠道查询
     *
     * @return void
     */
    public function srcsearch()
    {
        $order = I('get.order');
        $tel = I('get.tel');
        $list = [];
        if (!empty($order) || !empty($tel)) {
            $logic = new OrderSourceLogicModel();
            $list = $logic->srcsearch($order,$tel);
        }
        $this->assign(['list' => $list]);
        $this->display();
    }

    public function importantCitySet()
    {
        $input = I("get.");
        
        $logic = new OrdersLogicModel();
        $cityList = $logic->getSelectedCitys();

        // 获取城市分页列表
        $CitysSetList = $logic->getCitysSetList($input);
        
        $this->assign('citys', $cityList);
        $this->assign('cityList', $CitysSetList);
        $this->display('important_city_set');
    }

    public function makeImportantCity()
    {
        $post = I('post.');

        $logic = new OrdersLogicModel();
        $result = $logic->updateCityImportant($post);

        if($result){
            $this->ajaxReturn(['status' => 1, 'info'=> '']);
        }else{
            $this->ajaxReturn(['status' => 0, 'info'=> '操作失败！']);
        }
    }

    public function importExcelCitys($value='')
    {
        import("Library.Org.Phpexcel.PHPExcel","",".php");
        import("Library.Org.Phpexcel.PHPExcel.IOFactory","",".php");

        $fileType = explode(".", $_FILES["fileup"]["name"]);
        $ext = $fileType[1];

        $path = $_FILES["fileup"]["tmp_name"];

        if($ext == "xls"){
            import("Library.Org.Phpexcel.PHPExcel.Reader.Excel5","",".php");
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        }elseif($ext == "xlsx"){
            import("Library.Org.Phpexcel.PHPExcel.Reader.Excel2007","",".php");
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        }

        $objPHPExcel = $objReader->load($_FILES["fileup"]["tmp_name"]);
        $data = $objPHPExcel->getActiveSheet()->toArray();

        if(!$data) $this->ajaxReturn(['info' => '文件读取失败', 'status' => 0]);

        //if(count($data) < 3) $this->ajaxReturn(['info' => '请不要使用空数据excel', 'status' => 0]);

        if($data[1][0] != '重点城市') $this->ajaxReturn(['info' => '模板错误', 'status' => 0]);

        unset($data[0]);
        unset($data[1]);

        $logic = new OrdersLogicModel();
        $result = $logic->batchSetImportantCity($data);

        $this->ajaxReturn($result);
    }
}