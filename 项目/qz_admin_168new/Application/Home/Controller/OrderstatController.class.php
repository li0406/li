<?php
//订单统计
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\AdminuserLogicModel;
use Home\Model\Logic\ClinkTelcenterLogicModel;
use Home\Model\Logic\CityGeneralizationCoefficientLogicModel;
use Home\Model\Logic\CityMissingOrderLogicModel;
use Home\Model\Logic\CityOrderLackLogicModel;
use Home\Model\Logic\JiajuOrdersLogicModel;
use Home\Model\Logic\OrdersLogicModel;
use Home\Model\Logic\QuyuLogicModel;
use Home\Model\OrdersModel;

class OrderStatController extends HomeBaseController
{

    public function _initialize(){
        parent::_initialize();
    }

    private $time_interval = array(
        "7:30~8:30" => "1",
        "8:30~9:30" => "2",
        "9:30~10:30" => "3",
        "10:30~11:30" => "4",
        "11:30~12:30" => "5",
        "12:30~13:30" => "6",
        "13:30~14:30" => "7",
        "14:30~15:30" => "8",
        "15:30~16:30" => "9",
        "16:30~17:30" => "10",
        "17:30~18:30" => "11"
    );

     /**
        * 城市审单数量统计表
        */
    public function review(){
        if (I("get.begin") !== "") {
            $begin = I("get.begin");
        }

        if (I("get.end") !== "") {
            $end = I("get.end");
        }

        if (I("get.group") !== "") {
            $group = I("get.group");
        }

        if (I("get.manager") !== "") {
            $manager = I("get.manager");
        }

        if (I("get.city_id") !== "") {
            $cs = array_filter(explode(",", I("get.city_id")));
            $this->assign("cs",$cs);
        }

        if (I("get.kf") !== "") {
            $id = I("get.kf");
        }

        if ((!empty($begin) && (strtotime($begin) === false || strtotime($begin) < strtotime('1970-01-01 01:00:00'))) || (!empty($end) && (strtotime($end) === false || (strtotime($end) < strtotime('1970-01-01 01:00:00'))))) {
            $this->error('时间选择错误,请选择1970年以后时间');
            die();
        }

        if ((!empty($begin) && !empty($end) && strtotime($begin) > strtotime($end))) {
            $this->error('时间范围错误');
            die();
        }
        
        //获取客服列表
        $kfList = D("Adminuser")->getKfList();
        //获取客服组长列表
        $users = $this->getKfGroupInfo();
        //获取有会员的城市
        $citys = $this->getCitys($begin, $end);

        $list = $this->getReviewList($begin,$end,$cs,$group,$manager,$id);
        $this->assign("users",$users);
        $this->assign("list",$list);
        $this->assign("kfList",$kfList);
        $this->assign("citys",$citys);
        $this->display();
    }

    /**
     * [orderstattongji 订单状态统计]
     * @return [type] [description]
     */
    public function orderStatTongji()
    {
        /*
        获取组对应的客服ID数组
        获取师对应的客服ID数组
        getAdminuserIdsByKfgroup
        getAdminuserIdsByKfmanager
        getKfDetailInfoList

        1.获取每个人所在的组，师
                格式为： array(
                        '用户ID' => array(
                                '所在组ID' => '所在师ID'
                        )
                )
        2.获取每个城市的ID
        3.遍历获取的原始菜单，一次性获取四种维度的统计(人，组，师，城市)
        */
        //获取用户列表
        $user = array();
        $user_ids = array();
        $temp = D('Adminuser')->getKfList(true);
        foreach ($temp as $key => $value) {
            $value['kfmanager'] = array_filter(explode(',', $value['kfmanager']))[0];
            $user[$value['id']] = $value;
            $user_ids[] = $value['id'];
        }
        unset($temp);

        //获取团列表
        $group = array();
        $manager = array();
        $temp = D("Adminuser")->getKfGroupInfo();
        foreach ($temp as $key => $value) {
            $group[$value['kfgroup']] = $value;
            $manager[$value['manager_id']]['id'] = $value['manager_id'];
            $manager[$value['manager_id']]['name'] = $value['manager'];
            $manager[$value['manager_id']]['group_count'] ++;
        }
        unset($temp);

        //获取全部城市
        $temp = D("Quyu")->getQuyuList();
        foreach ($temp as $key => $value) {
            $city[$value["cid"]] = $value;
        }
        unset($temp);

        /*获取筛选人，筛选人为false的情况则搜索不到信息*/
        $select_user_ids = array();
        $select_user = I('get.user');
        if (!empty($select_user)) {
            $select_user_ids = array($select_user);
        }

        $select_group = I('get.group');
        if (!empty($select_group)) {
            $select_group_ids = D('Adminuser')->getAdminuserIdsByKfgroup($select_group);
            if (empty($select_user_ids)) {
                $select_user_ids = $select_group_ids;
            } else {
                $select_user_ids = array_intersect($select_user_ids, $select_group_ids);
            }
            if (empty($select_user_ids)) {
                $select_user_ids = false;
            }
        }

        $select_manager = I('get.manager');
        if (!empty($select_manager)) {
            $select_manager_ids = D('Adminuser')->getAdminuserIdsByKfmanager($select_manager);
            if (empty($select_user_ids)) {
                $select_user_ids = $select_manager_ids;
            } else {
                $select_user_ids = array_intersect($select_user_ids, $select_manager_ids);
            }
            if (empty($select_user_ids)) {
                $select_user_ids = false;
            }
        }

        if (empty($select_user_ids) && $select_user_ids !== false) {
            $select_user_ids = $user_ids;
        }

        //城市
        $select_city_id = I('get.city');

        //时间
        $time_real_start = strtotime("-1 day",mktime(17,30,00,date("m"),date("d"),date("Y")));
        $time_real_end = mktime(17,30,00,date("m"),date("d"),date("Y"));
        if ( I('get.time_real_start') !== "") {
            $time_real_start =  strtotime(date('Y-m-d 17:30:00', strtotime( I('get.time_real_start'))));
            $time_real_start =  strtotime("-1 day",$time_real_start);
        }

        if (I('get.time_real_end') !== "") {
            $time_real_end = strtotime(date('Y-m-d 17:30:00', strtotime(I('get.time_real_end'))));
        }

        $temp = D('OrderPool')->orderStatList($select_user_ids, $select_city_id, $time_real_start, $time_real_end);

        $main = array();
        /*订单状态判断参考OrdersModel的getOrderStatusDescription方法*/
        foreach ($temp as $key => $value) {
            if (!empty($value['op_uid'])) {
                //新单
                if ($value['on'] == 0 && $value['on_sub'] == 10) {
                    $main['user'][$value['op_uid']]['xin'] ++;
                    $main['group'][$user[$value['op_uid']]['kfgroup']]['xin'] ++;
                    $main['manager'][$user[$value['op_uid']]['kfmanager']]['xin'] ++;
                    $main['city'][$value['cs']]['xin'] ++;
                    $all["xin"] ++;
                }
                //次新单
                if ($value['on'] == 0 && $value['on_sub'] == 9) {
                    $main['user'][$value['op_uid']]['cixin'] ++;
                    $main['group'][$user[$value['op_uid']]['kfgroup']]['cixin'] ++;
                    $main['manager'][$user[$value['op_uid']]['kfmanager']]['cixin'] ++;
                    $main['city'][$value['cs']]['cixin'] ++;
                    $all["cixin"] ++;
                }
                //扫单
                if ($value['on'] == 0 && $value['on_sub'] == 8) {
                    $main['user'][$value['op_uid']]['sao'] ++;
                    $main['group'][$user[$value['op_uid']]['kfgroup']]['sao'] ++;
                    $main['manager'][$user[$value['op_uid']]['kfmanager']]['sao'] ++;
                    $main['city'][$value['cs']]['sao'] ++;
                    $all["sao"] ++;
                }
                //待定单
                if ($value['on'] == 2) {
                    $main['user'][$value['op_uid']]['daiding'] ++;
                    $main['group'][$user[$value['op_uid']]['kfgroup']]['daiding'] ++;
                    $main['manager'][$user[$value['op_uid']]['kfmanager']]['daiding'] ++;
                    $main['city'][$value['cs']]['daiding'] ++;
                    $all["daiding"] ++;
                }
                //有效单
                if ($value['on'] == 4) {
                    $main['user'][$value['op_uid']]['youxiao'] ++;
                    $main['group'][$user[$value['op_uid']]['kfgroup']]['youxiao'] ++;
                    $main['manager'][$user[$value['op_uid']]['kfmanager']]['youxiao'] ++;
                    $main['city'][$value['cs']]['youxiao'] ++;
                    $all["youxiao"] ++;
                }
                //无效单
                if (in_array($value['on'], ['5','6','7','8','9'])) {
                    $main['user'][$value['op_uid']]['wuxiao'] ++;
                    $main['group'][$user[$value['op_uid']]['kfgroup']]['wuxiao'] ++;
                    $main['manager'][$user[$value['op_uid']]['kfmanager']]['wuxiao'] ++;
                    $main['city'][$value['cs']]['wuxiao'] ++;
                    $all["wuxiao"] ++;
                }
                //暂时无效
                if ($value['on'] == 98) {
                    $main['user'][$value['op_uid']]['zanshiwuxiao'] ++;
                    $main['group'][$user[$value['op_uid']]['kfgroup']]['zanshiwuxiao'] ++;
                    $main['manager'][$user[$value['op_uid']]['kfmanager']]['zanshiwuxiao'] ++;
                    $main['city'][$value['cs']]['zanshiwuxiao'] ++;
                    $all["zanshiwuxiao"] ++;
                }
                //撤回单
                $main['user'][$value['op_uid']]['chehui'] += $value['ch_count'];
                $main['group'][$user[$value['op_uid']]['kfgroup']]['chehui'] += $value['ch_count'];
                $main['manager'][$user[$value['op_uid']]['kfmanager']]['chehui'] += $value['ch_count'];
                $main['city'][$value['cs']]['chehui'] += $value['ch_count'];
                $all["chehui"] += $value['ch_count'];

                //发单量
                $main['user'][$value['op_uid']]['count'] ++;
                $main['group'][$user[$value['op_uid']]['kfgroup']]['count'] ++;
                $main['manager'][$user[$value['op_uid']]['kfmanager']]['count'] ++;
                $main['city'][$value['cs']]['count'] ++;
                $all["count"] ++;

                if (!isset($main['user'][$value['op_uid']]['info'])) {
                    $main['user'][$value['op_uid']]['info'] = array(
                        'name' => $user[$value['op_uid']]['name'],
                        'group_id' => $user[$value['op_uid']]['kfgroup']
                    );
                }

                if (!isset($main['group'][$user[$value['op_uid']]['kfgroup']]['info'])) {
                    $main['group'][$user[$value['op_uid']]['kfgroup']]['info'] = array(
                        'group_id' => $user[$value['op_uid']]['kfgroup'],
                        'group_name' => $group[$user[$value['op_uid']]['kfgroup']]['name'],
                        'manager_id' => $user[$value['op_uid']]['kfmanager'],
                        'manager_name' => $manager[$user[$value['op_uid']]['kfmanager']]['name']
                    );
                }

                if (!isset($main['manager'][$user[$value['op_uid']]['kfmanager']]['info'])) {
                    $main['manager'][$user[$value['op_uid']]['kfmanager']]['info'] = array(
                        'manager_name' => $manager[$user[$value['op_uid']]['kfmanager']]['name'],
                        'group_count' => $manager[$user[$value['op_uid']]['kfmanager']]['group_count']
                    );
                }
            }
        }

        $this->assign('all', $all);
        $this->assign('city', $city);
        $this->assign('user', $user);
        $this->assign('group', $group);
        $this->assign('manager', $manager);
        $this->assign('main', $main);
        $this->display();
    }

    /**
     * 月度订单统计
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function monthorderstatistics()
    {
        //获取管辖城市信息
        $citys = getUserCitys(false);
        $this->assign("citys",$citys);

        switch ($this->User["uid"]) {
            case 2:
                     //客服
                     $id[] = $this->User["id"];
                     $kfList[] = array(
                                "name" => $this->User["name"],
                                "id" => $this->User["id"]
                     );
                     break;
            /*case 31:
                     //组长
                     //获取本组的客服人员
                     $result = D("Adminuser")->findMyGroupUser($this->User["id"],array(2));
                     foreach ($result as $key => $value) {
                                $id[] = $value["id"];
                                $kfList[] = array(
                                            "name" => $value["name"],
                                            "id" => $value["id"]
                                );
                     }
                     break;
            case 30:
                     //客服主管、客服经理
                     $result = D("Adminuser")->findMyManageUser($this->User["id"]);
                     foreach ($result as $key => $value) {
                                $id[] = $value["id"];
                                $kfList[] = array(
                                            "name" => $value["name"],
                                            "id" => $value["id"]
                                );
                     }
                     break;
            */default :
                $result = D("Adminuser")->getKfList();
                    foreach ($result as $key => $value) {
                    $id[] = $value["id"];
                    $kfList[] = array(
                        "name" => $value["name"],
                        "id" => $value["id"]
                    );
                }
                break;
        }

        if (count($id) == 0) {
            $this->_error("您的客服管辖尚未设置！");
        }

        $id = implode(",",$id);
        $this->assign("kfList",$kfList);

        if (I("get.name") !== "") {
            $name = I("get.name");
            $this->assign("kf",$name);
            if (!empty($id)) {
                $ids = array_flip(array_filter( explode(",", $id)));
                if (!array_key_exists($name,$ids)) {
                $this->_error("您无权查看该客服！");
                }
            }
        }

        if (I("get.city") !== "") {
            $cs = I("get.city");
            $this->assign("cs",$cs);

            if (!in_array($cs,$this->city)) {
                $this->_error("您无权查看该城市！");
            }
        }

        if (I("get.begin") !== "") {
            $begin = I("get.begin");
            $this->assign("begin",$begin);
        }

        if (I("get.end") !== "") {
            $end = I("get.end");
            $this->assign("end",$end);
        }

        if (I("get.group") !== "") {
            $group = I("get.group");
            $this->assign("group",$group);
        }


        $list = $this->getMonthOrderStatistics($begin,$end,$name,$cs,$fen,$fenrate,$id,$group);
        $this->assign("list",$list[0]);
        $tmp = $this->fetch("monthordettmp");
        //个人汇总
        $this->assign("tmp",$tmp);
        $tmp = $this->fetch("summarytmp");
        $this->assign("summary",$tmp);
        //团队汇总
        $this->assign("list",$list[1]);
        $this->assign("department",$list[2]);
        $tmp = $this->fetch("grouptmp");
        $this->assign("grouplist",$tmp);

        $this->display();
    }

     /**
        * 每日订单统计信息
        * @return [type] [description]
        */
    public function orderdaystatistics()
    {
        $begin = date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")));
        $end = date("Y-m-d");

        if (I("get.begin") !== "") {
            $begin = I("get.begin");
        }

        if (I("get.end") !== "") {
            $end = I("get.end");
        }

        $list = D("Orders")->orderDayStatistics($begin,$end);
        $this->assign("list",$list);
        $this->display();
    }

     /**
        * 有效单统计
        * @return [type] [description]
        */
     public function ordereffective()
     {
        //获取每天的分单量和赠单量
        $time = time();
        if (I("get.month") !== "") {
            $month = I("get.month");
            $time = strtotime($month);
        }

        //开始、结束时间
        $begin = mktime(0,0,0,date("m",$time),1,date("Y",$time));
        $end = strtotime("+1 day",mktime(0,0,0,date("m",$time),date("t",$time),date("Y",$time)));
        $list = D("Orders")->getOrderEffective($begin,$end);

        //获取每天的发单量
        $result = D("Orders")->getDayOfOrderCount(date("Y-m-d",$begin),date("Y-m-d",$end));
        foreach ($result as $key => $value) {
            $result[$value["date"]] = $value["count"];
        }

        foreach ($list as $key => $value) {
            $all["fen"] += $value["fen"];
            $all["zen"] += $value["zen"];
            $all["all"] += $result[$value["date"]];
            $list[$key]["count"] = $result[$value["date"]];
        }

        $this->assign("list",$list);
        $this->assign("all",$all);
        $this->display();
     }

     /**
        * 有效订单详细页面
        * @return [type] [description]
        */
     public function ordereffectivedetails()
     {
        if (!(I("get.type") != "" && I("get.date") != "")) {
                    $this->_error("暂时没有改数据");
        }

        $date = I("get.date");
        $type = I("get.type");
        //获取预算列表
        $yusuan  = D("Jiage")->getJiage();
        $begin = strtotime($date);
        $end = strtotime("+1 day",$begin);

        if (I("get.cid") !== "") {
                    $city = I("get.cid");
        }

        if (I("get.fangshi") !== "") {
                    $fangshi = I("get.fangshi");
        }

        if (I("get.lx") !== "") {
                    $lx = I("get.lx");
        }

        if (I("get.lxs") !== "") {
                    $lxs = I("get.lxs");
        }

        if (I("get.yusuan") !== "") {
                    $jiage = I("get.yusuan");
        }

        //获取分单详细信息
        $list = D("Orders")->getOrderEffectiveDetails($begin,$end,$type,$city,$fangshi,$lx,$lxs,$jiage);
        //获取城市信息
        //如果是当天
        if ($date == date("Y-m-d")) {
                    //取真实会员信息
                    $citys = D("User")->getRealVipCity();
        }else{
                    $citys = $this->getCitys($date, $date);
        }

        $this->assign("date",$date);
        $this->assign("type",$type);
        $this->assign("citys",$citys);
        $this->assign("list",$list);
        $this->assign("yusuan",$yusuan);
        $this->display();
     }

     /**
        * 客服每日
        * @return [type] [description]
        */
    public function customerorderstatistics()
    {
        $date = date("Y/m/d");
        if (I("get.date") !== "") {
            $date = I("get.date");
        }

        if (I("get.time") !== "") {
            $time = array_filter(explode(",", I("get.time")));
            $this->assign("time", $time);
        }

        if (I("get.group") !== "") {
            $group = array_filter(explode(",", I("get.group")));
            $this->assign("group", $group);
        }

        //获取所有客服
        $kfList = D("Adminuser")->getKfList();
        //获取客服组长列表
        $kfGroup = $this->getKfGroupInfo();

        //获取汇总数据
        $list = $this->getCustomerOrderStatisticsList(I("get.id"), $group, $date, $time);

        $this->assign("list",$list[0]);
        $this->assign("all",$list[1]);
        $this->assign("groups",$list[2]);
        $this->assign("timeInterval",$this->time_interval);
        $this->assign("date",$date);
        $this->assign("kfGroup",$kfGroup["groups"]);
        $this->assign("kfList",$kfList);
        $this->display();
     }

     /**
     * 客服新分单统计
     * @return void
     */
    public function customerordereffective()
    {
        $id = I('get.kf', 0);
        $begin = I('get.begin', '');
        $end = I('get.end', '');

        $monthStart = mktime(0,0,0,date('m'),1,date('Y'));
        $monthEnd = mktime(23,59,59,date('m'),date('t'),date('Y'));
        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $endDay = $monthEnd = strtotime('+1 day', strtotime($end))-1;
        } else {
            $endDay = strtotime('+1 day', strtotime(date('Y-m-d')));
        }
        $dayCount = ceil(($endDay - $monthStart) / 86400);
        if ($dayCount > 31) {
            $this->_error('查询时间不能大于31天');
        }

        //获取客服组长列表
        $users = $this->getKfGroupInfo();
        $ordersLogic = new OrdersLogicModel();
        $list = $ordersLogic->getCustomerOrdereffectiveList($id, I("get.groups"), I("get.manager"), $monthStart, $monthEnd);
        $this->assign("groups",$users["groups"]);
        $this->assign("manager",$users["manager"]);
        $this->assign("list",$list[0]);
        $this->assign("grouplist",$list[1]);
        $this->assign("managerlist",$list[2]);
        $this->assign("total",$list[3]);
        $this->assign("dayList",$list[4]);
        $this->assign("center",$list[5]);
        $adminUserLogic = new AdminuserLogicModel();
        $this->assign("kflist",$adminUserLogic->getKfList());
        $this->display();
    }

    /**
     * 客服量房统计
     * @return [type] [description]
     */
    public function customerorderliangfang()
    {
        //获取管辖客服信息
        $result = D("Adminuser")->getKfLFList();
        foreach ($result as $key => $value) {
            $kfList[] = array(
                "name" => $value["name"],
                "id" => $value["id"]
            );
        }

        if (I("get.kf") !== "") {
            $id = I("get.kf");
        }

        //获取客服组长列表
        $users = $this->getKfGroupInfo();
        $list = $this->getCustomerOrderliangfangList($id,I("get.groups"),I("get.manager"),I("get.begin"),I("get.end"));

        $this->assign("groups", $users["groups"]);
        $this->assign("manager", $users["manager"]);
        $this->assign("kflist", $kfList);
        
        $this->assign("list", $list[0]);
        $this->assign("grouplist", $list[1]);
        $this->assign("managerlist", $list[2]);
        $this->assign("total", $list[3]);
        $this->assign("dayList", $list[4]);
        $this->assign("center", $list[5]);
        $this->display();
    }

    /**
     * 客服有效量房统计
     * @return [type] [description]
     */
    public function customerorderyouxiaoliangfang()
    {
        //获取管辖客服信息
        $result = D("Adminuser")->getKfList();
        foreach ($result as $key => $value) {
            $kfList[] = array(
                "name" => $value["name"],
                "id" => $value["id"]
            );
        }

        if (I("get.kf") !== "") {
            $id = I("get.kf");
        }

        //获取客服组长列表
        $users = $this->getKfGroupInfo();
        $list = $this->getCustomerOrderyouxiaoliangfangList($id,I("get.groups"),I("get.manager"),I("get.begin"),I("get.end"));
        $this->assign("groups",$users["groups"]);
        $this->assign("manager",$users["manager"]);
        $this->assign("list",$list[0]);
        $this->assign("grouplist",$list[1]);
        $this->assign("managerlist",$list[2]);
        $this->assign("total",$list[3]);
        $this->assign("center",$list[4]);
        $this->assign("kflist",$kfList);
        $this->display();
    }
    /**
     * 客服有效量房统计详情
     * @return [type] [description]
     */
    public function getKfOrderyouxiaoliangfangdetail()
    {
        header("Content-type:text/html;charset=utf-8");
        $result = $this->getCustomerOrderyouxiaoliangfangDetailList(I("get.kf"),I("get.begin"));

        if (!empty($result)){
            $resMes = ['data'=>$result,'status'=>1,'info'=>'获取成功'];
        }else{
            $resMes = ['data'=>$result,'status'=>0,'info'=>'数据为空'];
        }
        $this->ajaxReturn($resMes);
    }
    /**
     * 签单电话统计
     * @return [type] [description]
     */
    public function orderqiandantelstat()
    {
        //获取全部城市
        $result = D("Quyu")->getAllQuyuOnly();
        foreach ($result as $key => $value) {
                $quyu[$value["cid"]] = $value;
        }

        if (I("get.city") !== "") {
                $cs = I("get.city");
        }

        if (I("get.type") !== "") {
                $type = I("get.type");
        }

        if (I("get.begin") !== "") {
                $begin = I("get.begin");
        }

        if (I("get.end") !== "") {
                $end = I("get.end");
        }

        //获取列表数据
        $result = $this->orderQiandanTelList($cs,$begin,$end,$type);
        $this->assign("citys",$quyu);
        $this->assign("list",$result["list"]);
        $this->assign("chart",$result["chart"]);
        $this->assign("page",$result["page"]);
        $this->display();
    }

    /**
     * 新客服呼叫行为统计
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function customerordertelstat()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);
        //获取管辖客服信息
        $result = D("Adminuser")->getKfList();
        foreach ($result as $key => $value) {
            $kfList[] = array(
                "name" => $value["name"],
                "id" => $value["id"]
            );
        }
        //获取客服组长列表
        $users = $this->getKfGroupInfo();
        //获取全部城市
        $result = D("Quyu")->getAllQuyuOnly();
        foreach ($result as $key => $value) {
            $quyu[$value["cid"]] = $value;
        }
        $this->assign("city", $quyu);
        $this->assign("kflist", $kfList);
        $this->assign("groups", $users["groups"]);
        $this->assign("manager", $users["manager"]);

        $list = $this->getCustomerOrderTelstatList(I("get.begin"), I("get.end"), I("get.kf"), I("get.groups"), I("get.manager"), I("get.city"));
        
        $this->assign("user", $list["user"]);
        $this->assign("group", $list["group"]);
        $this->assign("managers", $list["manager"]);
        $this->assign("all", $list["all"]);
        $this->assign("citys", $list["city"]);
        $this->display();
    }

    /**
     * 异步获取客服下午呼叫行为
     */
    public function ajaxGetCallAction()
    {
        header("Content-type:text/html;charset=utf-8");
        $kf = I("get.kf",'');
        $group = I("get.groups",'');
        $manager = I("get.manager",'');
        $time = I("get.begin",'');

        // 查询第一次呼叫时间
        $list = D("Orders")->getOrderCallActionList($kf, $group, $manager, $time);

        if (!empty($list)) {
            // 查询第一次接通时间
            $connectList = D("Orders")->getOrderCallConnectActionList($kf, $group, $manager, $time);
            $connectList = array_column($connectList, null, "kfid");

            foreach ($list as $key => $value){
                $list[$key]["starttime"] = "----";
                $list[$key]["endtime"] = "----";
                $list[$key]["time_diff"] = 0;

                if (array_key_exists($value["kfid"], $connectList)) {
                    $list[$key]["starttime"] = $connectList[$value["kfid"]]["starttime"];
                    $list[$key]["endtime"] = $connectList[$value["kfid"]]["endtime"];
                    $list[$key]["time_diff"] = $connectList[$value["kfid"]]["time_diff"];
                }

                $list[$key]["time_diff"] = timediff($list[$key]["time_diff"]);
            }
        }

        if (!empty($list)){
            $resMes = ["data" => $list, "status" => 1, "info" => "获取成功"];
        } else {
            $resMes = ["data" => $list, "status" =>0, "info" => "数据为空"];
        }

        $this->ajaxReturn($resMes);
    }

    /**
     * 异步获取客服下午呼叫行为
     */
    public function ajaxGetKfGroup()
    {
        header("Content-type:text/html;charset=utf-8");
        $kfgroup = M("adminuser")->where(['uil'=>30,'stat'=>1])->field('kfgroup,name');
        if (!empty($kfgroup)){
            $resMes = ['data'=>$kfgroup,'status'=>1,'info'=>'获取成功'];
        }else{
            $resMes = ['data'=>$kfgroup,'status'=>0,'info'=>'数据为空'];
        }
        $this->ajaxReturn($resMes);
    }
    /**
     * 客服对接行为分析
     * @return [type] [description]
     */
    public function customerorderdockingstat()
    {
        //获取对接客服列表信息
        $result = D("Adminuser")->getDockingKfList();
        foreach ($result as $key => $value) {
            $kfList[] = array(
                "name" => $value["name"],
                "id" => $value["id"]
            );
        }
         //获取全部城市
        $result = D("Quyu")->getAllQuyuOnly();
        foreach ($result as $key => $value) {
            $quyu[$value["cid"]] = $value;
        }

        $result = $this->getCustomerOrderDockingStat(I("get.id"),I("get.city"),I("get.begin"),I("get.end"));

        $this->assign("city",$quyu);
        $this->assign("kflist",$kfList);
        $this->assign("users",$result["users"]);
        $this->assign("citys",$result["citys"]);
        $this->assign("userAll",$result["userAll"]);
        $this->assign("cityAll",$result["cityAll"]);
        $this->display();
    }

    /**
     * 客服对接行为统计
     * @return [type] [description]
     */
    public function customerdockingstat()
    {
        //获取对接客服列表信息
        $result = D("Adminuser")->getRuleAdminuserList(false,[97,31,66,23,99]);
        foreach ($result as $key => $value) {
            $kfList[] = array(
                    "name" => $value["name"],
                    "id" => $value["id"]
            );
        }
         //获取全部城市
        $result = D("Quyu")->getAllQuyuOnly();
        foreach ($result as $key => $value) {
                $quyu[$value["cid"]] = $value;
        }
        $result = $this->getCustomerDockingStat(I("get.kf"),I("get.city"),I("get.begin"),I("get.end"));
        $this->assign("city",$quyu);
        $this->assign("kflist",$kfList);
        $this->assign("users",$result["users"]);
        $this->assign("citys",$result["city"]);
        $this->assign("userAll",$result["userAll"]);
        $this->assign("cityAll",$result["cityAll"]);
        $this->display();
    }

    /**
     * 客服订单数据分析
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function customerorderanalysis()
    {
            ini_set('memory_limit','256M');
            //定义3个选项 按人 按组 按师
            //获取对接客服列表信息
            $result = D("Adminuser")->getKfList();
            foreach ($result as $key => $value) {
                    $kfList[] = array(
                            "name" => $value["name"],
                            "id" => $value["id"]
                    );
            }

            //获取全部城市
            $result = D("Quyu")->getAllQuyuOnly();
            foreach ($result as $key => $value) {
                    $quyu[$value["cid"]] = array(
                                 "id" => $value["cid"],
                                 "name" => $value["cname"]
                    );
            }

            //获取客服组长列表
            $users = $this->getKfGroupInfo();
            $item["4"] = $quyu;
            $item["3"] = $kfList;
            $item["2"] = $users["groups"];
            $item["1"] = $users["manager"];

            $list = S("Cache:COA:".I("get.begin")."|".I("get.end"));
            if (!$list) {
                    $list = $this->getcustomerorderanalysis(I("get.begin"),I("get.end"));
                    S("Cache:COA:".I("get.begin")."|".I("get.end"),$list,86400*3);
            }
            $begin = I("get.begin") == ""?date("m/d/Y"):I("get.begin");
            $end = I("get.end") == ""?date("m/d/Y"):I("get.end");

            $maxDate = $end;
            $minDate = date("m/d/Y",strtotime("-3 month",strtotime($end)));

            $this->assign("maxDate",$maxDate);
            $this->assign("minDate",$minDate);
            $this->assign("begin",$begin);
            $this->assign("end",$end);
            $this->assign("list",json_encode($list));
            $this->assign("item",json_encode($item));
            $this->display();
    }

    /**************** S 数据分析****************************/
    /**
     * 客服发单量数据分析-发单量走势
     *
     */
    public function sendordertrend(){
        //当日数据
        $result = $this->getSendOrderData(date("Y-m-d"),date("Y-m-d"));
        $data["nowDay"] = $result["data"];
        //本月数据
        $result = $this->getSendOrderData();
        $data["nowMonth"] = $result["data"];
        //上月数据
        $lastMonth = date("Y-m-d",strtotime("-1 month",mktime(0,0,0,date("m"),1,date("Y"))));
        $time = strtotime($lastMonth);
        $lastEndMonth = date("Y-m-d",mktime(0,0,0,date("m",$time),date("t",$time),date("Y",$time)));

        $result = $this->getSendOrderData($lastMonth,$lastEndMonth);
        $data["lastMonth"] = $result["data"];

        $this->assign("data",$data);
        $this->display();
    }

    /**
     * 客服发单量数据分析-发单量总览
     *
     */
    public function sendorderoverview(){
        //本月数据
        $result = $this->getSendOrderData(I("get.begin"),I("get.end"));
        $this->assign("list",$result["list"]);
        $this->display();
    }

    /**
     * 客服发单量数据分析-发单量总览-按平均值图表界面
     *
     */
    public function sendorderaveragechart(){
        //当前数据量
        $result = $this->getSendOrderData(I("get.begin"),I("get.end"));
        $data["now"] = $result["avg"];

        $monthStart = date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")));
        $monthEnd = date("Y-m-d",mktime(0,0,0,date("m"),date("t"),date("Y")));
        if (date("d") < date("t")) {
            $monthEnd = date("Y-m-d");
        }

        if (I("get.begin") !== "" && I("get.end") !== "") {
            $monthStart = I("get.begin");
            $monthEnd = I("get.end");
        }

        //环比
        $offset = (strtotime($monthEnd) - strtotime($monthStart))/86400+1;
        $begin =  date("Y-m-d",strtotime("-$offset day",strtotime($monthStart)));
        $end =  date("Y-m-d",strtotime("-$offset day",strtotime($monthEnd)));

        $result = $this->getSendOrderData($begin,$end);
        $data["before"] = $result["avg"];
        $data["date"]["before"]["begin"] = $result["begin"];
        $data["date"]["before"]["end"] = $result["end"];

        //同比
        $begin =  date("Y-m-d",strtotime("last year",strtotime($monthStart)));
        $end =  date("Y-m-d",strtotime("last year",strtotime($monthEnd)));
        $result = $this->getSendOrderData($begin,$end);
        $data["lastyear"] = $result["avg"];
        $data["date"]["lastyear"]["begin"] = $result["begin"];
        $data["date"]["lastyear"]["end"] = $result["end"];

        //自定义
        $substart = date("m/d/Y");
        $subend = date("m/d/Y");

        if (I("get.substart") !== "" && I("get.subend") !== "") {
            $substart = I("get.substart");
            $subend = I("get.subend");
            $result = $this->getSendOrderData( $substart,$subend);
            $data["custom"] = $result["avg"];
            $data["date"]["custom"]["begin"] = $result["begin"];
            $data["date"]["custom"]["end"] = $result["end"];
        }

        $this->assign("substart",$substart);
        $this->assign("subend",$subend);
        $this->assign("data",$data);
        $this->display();
    }
    /**
     * 客服发单量数据分析-发单量总览-按时间段图表界面
     *
     */
    public function sendordertimechart(){
        //当前数据量
        $result = $this->getSendOrderData(I("get.begin"),I("get.end"));

        $time = array(
            '00：00-01：00' => 'time1' ,
            '01：00-02：00' => 'time2' ,
            '02：00-03：00' => 'time3' ,
            '03：00-04：00' => 'time4' ,
            '04：00-05：00' => 'time5' ,
            '05：00-06：00' => 'time6' ,
            '06：00-07：00' => 'time7' ,
            '07：00-08：00' => 'time8' ,
            '08：00-09：00' => 'time9' ,
            '09：00-10：00' => 'time10',
            '10：00-11：00' => 'time11',
            '11：00-12：00' => 'time12',
            '12：00-13：00' => 'time13',
            '13：00-14：00' => 'time14',
            '14：00-15：00' => 'time15',
            '15：00-16：00' => 'time16',
            '16：00-17：00' => 'time17',
            '17：00-18：00' => 'time18',
            '18：00-19：00' => 'time19',
            '19：00-20：00' => 'time20',
            '20：00-21：00' => 'time21',
            '21：00-22：00' => 'time22',
            '22：00-23：00' => 'time23',
            '23：00-24：00' => 'time24');

        if (I("get.time") != "") {
            $explode = array_filter(explode(",",I("get.time")));
            foreach ($explode as $key => $value) {
                if (array_key_exists($value,$time)) {
                    $time_array[$value] = $value;
                }
            }
        }

        $this->assign("time_array",$time_array);
        $this->assign("time",$time);
        $this->assign("data",$result["time"]);
        $this->display();
    }


    /**
     * 客服分单量数据分析-分单量总览
     */
    public function branchorderoverview(){
        //判断是平均还是总数
        $average = I('get.average') == 1? 1: 0;

        //获取筛选条件
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if (empty($start_time) || empty($end_time)) {
            $start_time = date('Y-m-01');
            $end_time = date('Y-m-d');
        }

        //获取座席数
        $attendance = $this->getAttendance($start_time, $end_time);

        //获取分单基本数据
        $result = $this->getFenDanStatArrayKeyDateTime($start_time, $end_time, $average);
        $info['list'] = $result;

        //获取时间段和每天的数据
        foreach ($result as $key => $value) {
            foreach ($value as $k => $v) {
                //获取时间段数据
                $info['time'][$k] = $info['time'][$k] + $v;
                //获取每天的数据
                $info['date'][$key] = $info['date'][$key] + $v;
            }
        }

        $main['info'] = $info;
        $main['time'] = $this->getStatTime();
        $main['date'] = $this->getStatDate($start_time, $end_time);
        $main['average'] = $average;
        $main['attendance'] = $attendance;
        $this->assign('main', $main);
        $this->display();
    }

    /**
     * 客服分单量数据分析-分单量总览-按平均值图表界面
     */
    public function branchorderaveragechart(){
        //判断是平均还是总数
        $average = I('get.average') == 1? 1: 0;

        //获取筛选条件
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if (empty($start_time) || empty($end_time)) {
            $start_time = date('Y-m-01');
            $end_time = date('Y-m-d');
        }

        //获取分单基本数据
        $result = $this->getFenDanStatArrayKeyDateTime($start_time, $end_time, $average);

        $info = array();
        foreach ($result as $key => $value) {
            foreach ($value as $k => $v) {
                $info[$k] = $info[$k] + $v;
            }
        }
        //获取查询天数
        $count = count($this->getStatDate($start_time, $end_time));
        //获取需要统计的时间
        $result = $this->getStatTime();
        $time = array();
        foreach ($result as $key => $value) {
            $main['info'][$value["begin"] . '-' . $value["end"]] = number_format($info[$value["begin"] . '-' . $value["end"]] / $count, 2);
            $time[] = $value["begin"] . '-' . $value["end"];
        }

        //生成图表数据
        $chart = array();
        foreach ($main['info'] as $key => $value) {
            $chart[] = $value;
        }

        //如果是ajax请求
        if (IS_AJAX) {
            $this->ajaxReturn(array(
                'data'=>array(
                    'chart' => json_encode($chart),
                    'date' => $start_time. ' 到 ' . $end_time
                ),
                'status'=>1
            ));
        }

        //生成同比时间
        $tong_start = date('Y', strtotime($start_time . ' -1 year')) . '-' . date('m-d',strtotime($start_time));
        if (!check_date($tong_start)) {
            $tong_start = date('Y', strtotime($start_time . ' -1 year')) . '-' . date('m',strtotime($start_time));
            $tong_start = date('Y-m-d', (strtotime($tong_start . ' +1 month') - 1));
        }
        $tong_end = date('Y', strtotime($end_time . ' -1 year')) . '-' . date('m-d',strtotime($end_time));
        if (!check_date($tong_end)) {
            $tong_end = date('Y', strtotime($end_time . ' -1 year')) . '-' . date('m',strtotime($end_time));
            $tong_end = date('Y-m-d', (strtotime($tong_end . ' +1 month') - 1));
        }

        $main['tong'] = array('start' => $tong_start, 'end' => $tong_end);
        //生成环比时间
        $huan_start = date('Y-m-d', strtotime($start_time . ' -' . $count . ' days'));
        $huan_end = date('Y-m-d', strtotime($end_time . ' -' . $count . ' days'));
        $main['huan'] = array('start' => $huan_start, 'end' => $huan_end);

        $main['time'] = json_encode($time);
        $main['chart'] = json_encode($chart);
        $this->assign('main', $main);
        $this->display();
    }

    /**
    * 分单量数据分析——按时间段图表界面
    */
    public function dansdfdzxt(){
        //判断是平均还是总数
        $average = I('get.average') == 1? 1: 0;

        //获取筛选条件
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if (empty($start_time) || empty($end_time)) {
            $start_time = date('Y-m-01');
            $end_time = date('Y-m-d');
        }
        //获取分单基本数据
        $info = $this->getFenDanStatArrayKeyDateTime($start_time, $end_time, $average);

        //获取需要统计的时间
        $result = $this->getStatTime();
        $time = array();
        foreach ($result as $key => $value) {
            $time[] = $value[0] . '-' . $value[1];
        }
        //获取需要统计的日期
        $series = $legend = $xAxis = array();
        $date = $this->getStatDate($start_time, $end_time);
        foreach ($date as $key => $value) {
            $xAxis[] = $value;
        }

        foreach ($time as $key => $value) {
            $chart_data = array();
            foreach ($date as $k => $v) {
                $chart_data[] = empty($info[$v][$value]) ? 0 : number_format($info[$v][$value], 2);
            }
            $series[] = array(
                'name' => $value,
                'type' => 'line',
                'data' => $chart_data
            );
            $legend[] = $value;
        }
        $main['echarts'] = array(
            'series' => json_encode($series),
            'legend' => json_encode($legend),
            'xAxis'  => json_encode($xAxis)
        );
        $this->assign('main', $main);
        $this->display();
    }

    /**
     * 获取分单统计基本信息
     * @param  string  $start_time 开始时间
     * @param  string  $end_time   结束时间
     * @param  integer $average    是否取平均值
     * @return array               分单统计基本信息数组
     */
    public function getFenDanStatArrayKeyDateTime($start_time = '', $end_time = '', $average = 0)
    {
        if (empty($start_time) || empty($end_time)) {
            return false;
        }
        $saveKey = md5($start_time) . md5($end_time) . md5($average);
        // $info = S('Orderstat:getFenDanStatArrayKeyDateTime:v2:' . $saveKey);
        if (empty($info)) {
            if ($average == 1) {
                $attendance = $this->getAttendance($start_time, $end_time);
            }
            $info   = array();
            $time = $this->getStatTime();
            $result = D('OrderCsosNew')->getFenDanDataAnalysisList($start_time, $end_time);
            foreach ($result as $key => $value) {
                foreach ($time as $k => $v) {
                    $current = strtotime(date('H:i', $value['lasttime']));
                    if (($current >= strtotime($v["begin"])) && ($current < strtotime($v["end"]))) {
                        if ($average == 1) {
                            $avg = 1/$attendance[date('Y-m-d', $value['lasttime'])]['all'];
                            $info[date('Y-m-d', $value['lasttime'])][$v["begin"] . '-' . $v["end"]] = $info[date('Y-m-d', $value['lasttime'])][$v["begin"] . '-' . $v["end"]] + $avg;
                        } else {
                            $info[date('Y-m-d', $value['lasttime'])][$v["begin"] . '-' . $v["end"]]++;
                        }
                        break;
                    }
                }
            }
            S('Orderstat:getFenDanStatArrayKeyDateTime:v2:' . $saveKey, $info, 600);
        }

        return $info;
    }

    /**
     * 分单量数据分析-团队分析
     */
    public function fenDanTeamAnalysisSheet()
    {

        //判断是平均还是总数
        $average = I('get.average') == 1? 1: 0;
        $result = $this->getOrderStatArrayKeyTimeKfGroupList(I('get.start_time'), I('get.end_time'), $average);

        $main['list'] = $result["list"];
        // $main['time']             = $time;
        $main['group'] = $result["group"];
        // $main['attendance_count'] = $attendance_count;
        $this->assign('main', $main);
        $this->display();
    }

    /**
    * 分单量数据分析——多时段团队人均分单量柱状图
    */
     public function fenDanTeamAnalysisChart(){
        //判断是平均还是总数
        $average = I('get.average') == 1? 1: 0;

        //获取需要统计的时间
        $result = $this->getStatTime();
        foreach ($result as $key => $value) {
            $time[] = $value["begin"] . '-' . $value["end"];
        }

        //获取分单基本数据
        $result = $this->getOrderStatArrayKeyTimeKfGroupList(I('get.start_time'), I('get.end_time'), $average);

        $start_time = $result["start_time"];
        $end_time = $result["end_time"];
        $group = $result["group"];
        foreach ($result["list"] as $key => $value) {
            $data = array();
            foreach ($value["group"] as $k => $val) {
               $data[] = $val["avg"];
            }
            $data[] = $value["avg"];

            $temp = array(
                'name' => $time[$key],
                'type' => 'bar',
                'stack' => '总量',
                'barWidth' => 25,
                'label' => array(
                    'normal' => array(
                        'show' => true,
                        'position' => 'insideRight'
                    )
                ),
                'data' => $data
            );
            $series[] = $temp;
        }

        foreach ($group as $key => $value) {
            $yAxis[] = $value . '团';
        }
        if ($average == 1) {
            $yAxis[] = '日人均值';
        } else {
            $yAxis[] = '团队均值';
        }


        $main['echarts'] = array(
            'legend' => json_encode($time),
            'yAxis'  => json_encode($yAxis),
            'series' => json_encode($series)
        );

        $main['start_time']       = $start_time;
        $main['end_time']         = $end_time;
        $this->assign('main', $main);
        $this->display();
    }

    /**
     * 获取分单统计基本信息
     * @param  string  $start_time 开始时间
     * @param  string  $end_time   结束时间
     * @param  integer $average    是否取平均值
     * @return array               分单统计基本信息数组
     */
    public function getFenDanStatArrayKeyTimeKfGroup($start_time = '', $end_time = '', $average = 0)
    {
        if (empty($start_time) || empty($end_time)) {
            return false;
        }
        $saveKey = md5($start_time) . md5($end_time) . md5($average);
        $info = S('Orderstat:getFenDanStatArrayKeyTimeKfGroup:v2:' . $saveKey);
        if (empty($info)) {
            if ($average == 1) {
                $attendance = $this->getAttendance($start_time, $end_time);
            }
            $info   = array();
            $time = $this->getStatTime();
            $result = D('OrderCsosNew')->getFenDanDataAnalysisList($start_time, $end_time);
            foreach ($result as $key => $value) {
                //如果kfgroup为空，则跳过处理下一条
                if (empty($value['kfgroup'])) {
                    continue;
                }
                foreach ($time as $k => $v) {
                    $current = strtotime(date('H:i', $value['lasttime']));
                    if (($current >= strtotime($v[0])) && ($current < strtotime($v[1]))) {
                        if ($average == 1) {
                            $avg = 1/$attendance[date('Y-m-d', $value['lasttime'])]['group' . $value['kfgroup']];
                            $info[$v[0] . '-' . $v[1]][$value['kfgroup']] = $info[$v[0] . '-' . $v[1]][$value['kfgroup']] + $avg;
                        } else {
                            $info[$v[0] . '-' . $v[1]][$value['kfgroup']]++;
                        }
                        break;
                    }
                }
            }
            S('Orderstat:getFenDanStatArrayKeyTimeKfGroup:v2:' . $saveKey, $info, 600);
        }
        return $info;
    }

    /**
     * 获取订单统计基本信息
     * @param  string  $start_time 开始时间
     * @param  string  $end_time   结束时间
     * @param  integer $average    是否取平均值
     * @return array               分单统计基本信息数组
     */
    public function getOrderStatArrayKeyTimeKfGroupList($start_time = '', $end_time = '', $average = 0)
    {
        //获取筛选条件
        $start = mktime(0,0,0,date("m"),1,date("Y"));
        $end= mktime(23,59,59,date("m"),date("t"),date("Y"));

        if (!empty($start_time) || !empty($end_time)) {
            $start = strtotime($start_time);
            $end = strtotime($end_time)+86400-1;
        }

        //时间间隔
        $dayOffset = ceil(( $end - $start ) / 86400);

        $timeRange = $this->getStatTime();

        //获取每个时段的客服座席数
        $result = D("OrderPool")->getKfRseatByTimeRange($start, $end);

        foreach ($result as $key => $value) {
            $users["group"][$value["mark"]][$value["kfgroup"]] = setInfNanToN(round($value["count"]/$dayOffset,1));
            $users["all"][$value["mark"]] += $users["group"][$value["mark"]][$value["kfgroup"]];
            $group[$value["kfgroup"]] = $value["kfgroup"];
        }

        //客服团队数
        $groupCount = count($group);

        foreach ($timeRange as $key => $value) {
            foreach ($group as $k => $val) {
                $timeRange[$key]["group"][$val]["fen"] = 0;
            }
        }

        $result = D('Orders')->getOrderStatDataAnalysisDataList($start, $end);

        foreach ($result as $key => $value) {
            if ($value["on"] == 4 && $value["type_fw"] == 1 && $value["kfgroup"] != 0) {
                $index = $value["mark"];
                if (array_key_exists($index, $timeRange)) {
                    $timeRange[$index]["group"][$value["kfgroup"]]["fen"] ++;
                    $timeRange[$index]["all"] ++;
                }
            }
        }

        //日人均 = 时间段内总的分单量/坐席平均数/天数
        //坐席平均数  = 时间段内总的坐席人数/天数
        //团队均值  = 时间段内总的分单量/团队数/天数

        foreach ($timeRange as $key => $value) {
            if ($average == 1) {
                //按人
                foreach ($value["group"] as $k => $val) {
                    $timeRange[$key]["group"][$k]["avg"] = setInfNanToN(round($val["fen"]/$users["group"][$key][$k]/$dayOffset,2));
                }

                $timeRange[$key]["avg"] = setInfNanToN(round($timeRange[$key]["all"]/$users["all"][$key]/$dayOffset,2));
            } else {
                //按团队，按总分单量/时间
                foreach ($value["group"] as $k => $val) {
                    $timeRange[$key]["group"][$k]["avg"] = setInfNanToN(round($val["fen"]/$dayOffset,2));
                }
                $timeRange[$key]["avg"] = setInfNanToN(round($timeRange[$key]["all"]/$groupCount/$dayOffset,2));
            }
        }

        return array("list" => $timeRange,"group" => $group,"start_time"=> date("Y-m-d",$start),"end_time"=> date("Y-m-d",$end));
    }

    /**
     * 获取每天的座席数
     * @param  string $start_time 需要获取的开始时间
     * @param  string $end_time   需要获取的结束时间
     * @return array              座席数数组
     */
    public function getAttendance($start_time, $end_time)
    {
        if (empty($start_time) || empty($end_time)) {
            return false;
        }
        $saveKey = md5($start_time) . md5($end_time);
        $attendance = S('Orderstat:getAttendance:v2:' . $saveKey);
        if (empty($attendance)) {
            $attendance = array();
            $result = D("OrderPool")->customerseatsstat($start_time, date('Y-m-d', strtotime($end_time . ' +1 day')));

            $attendance = array();
            foreach ($result as $key => $value) {
                $attendance[$value['date']] = $value;
            }
            S('Orderstat:getAttendance:v2:' . $saveKey, $attendance, 600);
        }
        return $attendance;
    }

    /**
     * 获取需要统计的时间段
     */
    public function getStatTime()
    {
        $result = array();
        for ($i = 8; $i < 18; $i++) {
            $result[] = array(
               "begin" => strlen($i + '') == 2 ? $i . ':30' : ('0' . $i) . ':30',
               "end" => strlen($i + 1 + '') == 2 ? ($i + 1) . ':30' : '0' . ($i + 1) . ':30'
            );
        }
        return $result;
    }

    /**
     * 给出一个开始日期和结束日期，获取中间的日期列表
     * @param  string $start_time 开始时间
     * @param  string $end_time   结束时间
     * @return array              日期数组
     */
    public function getStatDate($start_time, $end_time)
    {
        $result     = array();
        if (empty($start_time) || empty($end_time)) {
            return false;
        }
        $start_time = strtotime($start_time);
        $end_time   = strtotime($end_time);
        while($start_time <= $end_time){
            $result[] = trim(date('Y-m-d',$start_time),' ');
            $start_time += strtotime('+1 day',$start_time) - $start_time;
        }
        return $result;
    }

     /**
     * 赠送单原因分析
     * @param string $value [description]
     */
    public function kfzsdtj()
    {
        //获取赠单数据列表
        $result = $this->getKfZenStat(I("get.begin"),I("get.end"));
        $this->assign("list",$result["list"]);
        $this->assign("all",$result["all"]);
        $this->assign("item",$result["item"]);
        $this->assign("data",$result["data"]);
        $this->display();
    }

        /**
     * 分单量总览
     * @return [type] [description]
     */
    public function  ordertrend()
     {
         $list = $this->getOrderTrend(I("get.begin"),I("get.end"));
         $this->assign("list",$list["list"]);
         $this->display();
     }

     /**
      * 客服电话统计
      * @param  string $value [description]
      * @return [type]        [description]
      */
    public function telStat() {
        $input = [
            "kf" => I("get.kf"),
            "group" => I("get.group"),
            "begin" => I("get.begin"),
            "end" => I("get.end"),
            "manager" => I("get.manager")
        ];

        // 获取客服列表
        $kfList = D("Adminuser")->getKfList();

        // 获取客服组长列表
        $groups = $this->getKfGroupInfo();

        // 获取客服师长列表
        $managers = D("Adminuser")->getKfManagerList();

        // 统计数据
        $list = $this->getTelStat($input["kf"], $input["group"], $input["begin"], $input["end"], $input["manager"]);

        $this->assign("managers", $managers);
        $this->assign("kfList", $kfList);
        $this->assign("users", $groups);
        $this->assign("input", $input);
        $this->assign("list", $list);
        $this->display();
    }

    /**
     * 客服撤回单统计
     * @return [type] [description]
     */
    public function orderRevokeStat()
    {
        $list = $this->getOrderRevokeStat(I("get.begin"),I("get.end"));
        $this->assign("list",$list);
        $this->display();
    }

    /**
     * 订单预警
     * @return [type] [description]
     */
    public function orderWarning()
    {
        //预计全天新单、目前人均新单
        $info = $this->getEstimatedOrderInfo();
        //获取数据统计列表
        $list = $this->getEstimatedOrderList();
        $this->assign("list",$list);
        $this->assign("info",$info);
        $this->display();
    }

     /**
     * 订单预警通知
     * @return [type] [description]
     */
    public function orderWarningManager()
    {
        //获取通知名单
        $list = D("OrdersExceptionManager")->getManagerList();
        $this->assign("list",$list);
        $this->display();
    }

    public function editException()
    {
        if ($_POST) {
            $param = I("post.data");
            $type = I("post.type");

            $day = date("Y-m-d",time());
            $result = D("OrdersExceptionStatistics")->getExceptionStatisticsByDay($day);

            foreach ($result as $key => $value) {
                $time = (int)$value["time"];
                if ($type == "first") {
                    //近三月发单均值
                    $data = array(
                        "month_order" => is_numeric($param[$time])?$param[$time]:0
                    );
                } elseif ($type == "three") {
                    //近三月平均坐席分时段发单
                    $data = array(
                        "customer_history_order" => is_numeric($param[$time])?$param[$time]:0
                    );
                }
                D("OrdersExceptionStatistics")->editException($value["day"],$value["time"],$data);
            }

            $this->ajaxReturn(array("status"=> 1,"data" =>$info, "info" => "操作成功"));
        }
    }

    public function findManagerInfo()
    {
        if ($_POST) {
            $id = I("post.id");
            $info = D("OrdersExceptionManager")->findUserInfo($id);
            if ($i !== false) {
                $this->ajaxReturn(array("status"=> 1,"data" =>$info, "info" => "操作成功"));
            }
            $this->ajaxReturn(array("status"=> 0,"info" => "操作失败"));
        }
    }

    public function exceptionUp()
    {
        if ($_POST) {
            $option_name = I("post.name");
            $option_value = I("post.value");
            $reg = '/^\d+$/';

            if (!preg_match($reg,$option_value)) {
                $this->ajaxReturn(array("status"=> 0,"info"=>"预警值只能为数字"));
            }
            $result = D("Options")->getOptionByGroup("ORDER_EXCEPTION");
            foreach ($result as $key => $value) {
               $options[$value["option_name"]] = $value;
            }

            if ($option_name == "order_min") {
                if ($option_value >= $options["order_max"]["option_value"] && isset($options[$value["order_max"]])) {
                    $this->ajaxReturn(array("status"=> 0,"info"=>"预警最小值不能大于等于预警最大值"));
                }
            }

            if ($option_name == "order_max") {
                 if ($option_value <= $options["order_min"]["option_value"] && isset($options[$value["order_min"]])) {
                    $this->ajaxReturn(array("status"=> 0,"info"=>"预警最小值不能小于等于预警最小值"));
                }
            }

            if (count($options[$option_name]) > 0) {
               $i = D("Options")->setOption($option_name,$option_value);
            } else {
                $data = array(
                    "option_name" => $option_name,
                    "option_value" => $option_value,
                    "option_group" => "ORDER_EXCEPTION",
                    "option_remark" => $name."客服预警订单设置项"
                );
                $i = D("Options")->addOption($data);
            }
            if ($i !== false) {
                $this->ajaxReturn(array("status"=> 1));
            }
            $this->ajaxReturn(array("status"=> 0));
        }
    }

    public function exceptionManagerUp()
    {
        if ($_POST) {
            $id = I("post.id");

            if (I("post.user") == "" && I("post.id") == "") {
                $this->ajaxReturn(array("status"=> 0,"info" => "角色人员不存在或角色未填写！"));
            }

            if (I("post.tel") == "") {
                $this->ajaxReturn(array("status"=> 0,"info" => "请输入手机号码"));
            }

            //验证手机号码
            $reg = '/(13|14|15|16|17|18|19)[0-9]{9}/i';
            if (!preg_match($reg, I("post.tel"))) {
                $this->ajaxReturn(array("status"=> 0,"info" => "请输入正确的手机号码"));
            }

            //查询是否已保存
            $count = D("OrdersExceptionManager")->findUserInfoCount($id);

            if ($count > 0) {
                $data = array(
                    "tel" => trim(I("post.tel"))
                );

                if (I('post.enabled') !== ""){
                    $data["enabled"] = I('post.enabled');
                }
                $i = D("OrdersExceptionManager")->editInfo($id,$data);

            } else {
                $data = array(
                    "user_id" => $id,
                    "tel" => trim(I("post.tel"))
                );
                $i = D("OrdersExceptionManager")->addInfo($data);
            }


            if ($i !== false) {
                $this->ajaxReturn(array("status"=> 1,"info" => "操作成功"));
            }
            $this->ajaxReturn(array("status"=> 0,"info" => "操作失败"));
        }
    }

    public function delManager()
    {
        if ($_POST) {
            $id = I("post.id");
            $i = D("OrdersExceptionManager")->delInfo($id);
            if ($i !== false) {
                $this->ajaxReturn(array("status"=> 1,"info" => "操作成功"));
            }
            $this->ajaxReturn(array("status"=> 0,"info" => "操作失败"));
        }
    }

    public function mediaOrderExcetpion()
    {
        //获取媒介订单数据
        $result = $this->getMediaOrderInfo();
        $this->assign("info",$result);
        $this->display();
    }

    public function orderFieldStat()
    {
        //获取所有城市
        $info['city'] = D("Quyu")->getAllQuyuOnly();
        $info['list'] = D("Home/Logic/OrdersLogic")->getOrderFieldStatList(I("get.begin"),I("get.end"),I("get.city"),I("get.state"));
        $info['all_count'] = $info['list']['cs']["all_count"];
        $this->assign("info",$info);
        $this->display();
    }

    public function customerLfStat()
    {
        //客服列表
        $users = D("Home/Logic/AdminuserLogic")->getKfList();
        //客服组列表
        $group = $this->getKfGroupInfo();
        $list = D("Home/Logic/OrdersLogic")->getCustomerLfStat(I("get.kf"),I("get.groups"),I("get.manager"),I("get.begin"),I("get.end"));

        $this->assign("group",$group);
        $this->assign("users",$users);
        $this->assign("list",$list);
        $this->display();
    }

    /*****************E 数据分析****************************/

    /**
     * 获取撤回单统计
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    private function getOrderRevokeStat($begin,$end)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"))+1;
        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime("+1 day", strtotime($end));
        }

        $result = D("Orders")->getOrderRevokeStat($monthStart,$monthEnd);

        foreach ($result as $key => $value) {
            //个人
            if (!array_key_exists($value["id"],$list["user"])) {
                $list["user"][$value["id"]]["name"] = $value["name"];
            }
            //组
            if (!array_key_exists($value["kfgroup"],$list["group"])) {
                $list["group"][$value["kfgroup"]]["name"] = $value["kfgroup"]."团";
            }
            //师
            if (!array_key_exists($value["kfmanager"],$list["manager"])) {
                $list["manager"][$value["kfmanager"]]["name"] = $value["manager"];
            }

            $list["user"][$value["id"]]["all_count"] ++;
            $list["group"][$value["kfgroup"]]["all_count"] ++;
            $list["manager"][$value["kfmanager"]]["all_count"] ++;
            $list["user"][$value["id"]]["push_count"] += $value["push_count"];
            $list["group"][$value["kfgroup"]]["push_count"] += $value["push_count"];
            $list["manager"][$value["kfmanager"]]["push_count"] += $value["push_count"];

            //汇总
            $list["all"]["all_count"] ++;
            $list["all"]["push_count"] += $value["push_count"];


            //撤回单量
            if ($value["mark"] == 1) {
                $list["user"][$value["id"]]["revoke_count"] ++;
                $list["group"][$value["kfgroup"]]["revoke_count"] ++;
                $list["manager"][$value["kfmanager"]]["revoke_count"] ++;
                $list["all"]["revoke_count"] ++;
            }

            //分单量
            if ($value["mark"] == 1 && $value["on"] == 4 && $value["type_fw"] == 1) {
                $list["user"][$value["id"]]["fen_count"] ++;
                $list["group"][$value["kfgroup"]]["fen_count"] ++;
                $list["manager"][$value["kfmanager"]]["fen_count"] ++;
                $list["all"]["fen_count"] ++;
            }

            //赠单量
            if ($value["mark"] == 1 && $value["on"] == 4 && $value["type_fw"] == 2) {
                $list["user"][$value["id"]]["zen_count"] ++;
                $list["group"][$value["kfgroup"]]["zen_count"] ++;
                $list["manager"][$value["kfmanager"]]["zen_count"] ++;
                $list["all"]["zen_count"] ++;
            }

            //撤回单率
            $list["user"][$value["id"]]["revoke_rate"] = setInfNanToN(round($list["user"][$value["id"]]["revoke_count"]/$list["user"][$value["id"]]["all_count"],3))*100;
            $list["group"][$value["kfgroup"]]["revoke_rate"] = setInfNanToN(round($list["group"][$value["kfgroup"]]["revoke_count"]/$list["group"][$value["kfgroup"]]["all_count"],3))*100;
            $list["manager"][$value["kfmanager"]]["revoke_rate"] = setInfNanToN(round($list["manager"][$value["kfmanager"]]["revoke_count"]/$list["manager"][$value["kfmanager"]]["all_count"],3))*100;
            $list["all"]["revoke_rate"] = setInfNanToN(round($list["all"]["revoke_count"]/$list["all"]["all_count"],3))*100;

            //分转赠
            if ($value["before_fen_mark"] == 1 && $value["after_zen_mark"] == 1) {
                $list["user"][$value["id"]]["turn_zen_count"] ++;
                $list["group"][$value["kfgroup"]]["turn_zen_count"] ++;
                $list["manager"][$value["kfmanager"]]["turn_zen_count"] ++;
                $list["all"]["turn_zen_count"] ++;
            }

            //赠转分
            if ($value["before_zen_mark"] == 1 && $value["after_fen_mark"] == 1) {
                $list["user"][$value["id"]]["turn_fen_count"] ++;
                $list["group"][$value["kfgroup"]]["turn_fen_count"] ++;
                $list["manager"][$value["kfmanager"]]["turn_fen_count"] ++;
                $list["all"]["turn_fen_count"] ++;
            }

            //更改单平均修改次数
            $list["user"][$value["id"]]["avg_push"] = setInfNanToN(round($list["user"][$value["id"]]["push_count"]/$list["user"][$value["id"]]["revoke_count"],2));
            $list["group"][$value["kfgroup"]]["avg_push"] = setInfNanToN(round($list["group"][$value["kfgroup"]]["push_count"]/$list["group"][$value["kfgroup"]]["revoke_count"],2));
            $list["manager"][$value["kfmanager"]]["avg_push"] = setInfNanToN(round($list["manager"][$value["kfmanager"]]["push_count"]/$list["manager"][$value["kfmanager"]]["revoke_count"],2));

            $list["all"]["avg_push"] = setInfNanToN(round($list["all"]["push_count"]/$list["all"]["revoke_count"],2));
        }

        return $list;
    }



     /**
        * 月度订单统计数据
        * @return [type] [description]
        */
     private function getMonthOrderStatistics($begin,$end,$name,$cs,$fen,$fenrate,$id,$group)
     {
        if (empty($begin)) {
            $begin = date("Y-m-d H:i:s",mktime(0,0,0,date("m"),1,date("Y")));
        }

        if (empty($end)) {
            $end = date("Y-m-d H:i:s");
        }

        $time = strtotime($end);
        $monthStart = date("Y-m-d",mktime(0,0,0,date("m",$time),1,date("Y",$time)));
        $monthEnd = date("Y-m-d",mktime(0,0,0,date("m",$time),date("t",$time),date("Y",$time)));

        if (!empty($cs)) {
            $cs = $cs;
        }

        //统计每个城市每天的发单量
        $result = D("Orders")->getMonthOrderStatistics($monthStart,$monthEnd,$begin,$end,$cs,$name,$fen,$fenrate,$id,$group);

        foreach ($result as $key => $value) {
            if (!array_key_exists($value["id"], $list)) {
                     $list[$value["id"]]["date"] = $end;
                     $list[$value["id"]]["name"] = $value["name"];
                     $list[$value["id"]]["kfgroup"] = $value["kfgroup"];
            }
            //客服统计
            $value["rate"] = setInfNanToN(round($value["rate"],2));
            $value["fen_rate"] = setInfNanToN(round($value["fen_other"]/$value["count"],4))*100;
            //累计分单量(分+赠) 公式:发单量*城市系数
            $value["fen_zen_count"] = setInfNanToN(round($value["count"]*$value["rate"]/100,2));
            if ($value["fen"] == 0 && $value["count"] > 0) {
                     $value["fen_zen_count"] = 0;
            }
            $list[$value["id"]]["city"][$value["cs"]] = $value;

            //累计计算
            $list[$value["id"]]["fen"] += $value["fen"];
            $list[$value["id"]]["zen"] += $value["zen"];
            $list[$value["id"]]["fen_other"] += $value["fen_other"];
            $list[$value["id"]]["fen_rate"] = setInfNanToN(round($list[$value["id"]]["fen_other"]/$list[$value["id"]]["count"],4))*100;
            $list[$value["id"]]["count"] += $value["count"];
            $list[$value["id"]]["fen_zen_count"] += $value["fen_zen_count"];
            $list[$value["id"]]["fen_zen_rate"] = setInfNanToN(round($list[$value["id"]]["fen_zen_count"]/$list[$value["id"]]["count"]*100,4));

            //统计单个团队
            $fen = $grouplist[$value["kfmanager"]]["group"][$value["kfgroup"]]["fen"] += $value["fen"];
            $zen = $grouplist[$value["kfmanager"]]["group"][$value["kfgroup"]]["zen"] += $value["zen"];
            $count = $grouplist[$value["kfmanager"]]["group"][$value["kfgroup"]]["count"] += $value["count"];
            //累计分+赠数量
            $fen_zen_other = $grouplist[$value["kfmanager"]]["group"][$value["kfgroup"]]["fen_other"] += $value["fen_other"];

            $fen_zen_count = $grouplist[$value["kfmanager"]]["group"][$value["kfgroup"]]["fen_zen_count"] += $value["fen_zen_count"];
            $grouplist[$value["kfmanager"]]["group"][$value["kfgroup"]]["fen_rate"] = setInfNanToN(round($fen_zen_other/$count*100,4));
            $grouplist[$value["kfmanager"]]["group"][$value["kfgroup"]]["fen_zen_rate"] = setInfNanToN(round($fen_zen_count/$count*100,4));

            //汇总整个团队
            $grouplist[$value["kfmanager"]]["date"] = $end;
            $grouplist[$value["kfmanager"]]["kfmanager"] = $value["kfmanager"];
            $grouplist[$value["kfmanager"]]["fen"] += $value["fen"];
            $grouplist[$value["kfmanager"]]["zen"] += $value["zen"];
            $grouplist[$value["kfmanager"]]["fen_other"] += $value["fen_other"];
            $grouplist[$value["kfmanager"]]["fen_zen_count"] += $value["fen_zen_count"];
            $grouplist[$value["kfmanager"]]["count"] += $value["count"];
            $grouplist[$value["kfmanager"]]["fen_rate"] = setInfNanToN(round($grouplist[$value["kfmanager"]]["fen_other"]/$grouplist[$value["kfmanager"]]["count"]*100,4));
            $grouplist[$value["kfmanager"]]["fen_zen_rate"] = setInfNanToN(round($grouplist[$value["kfmanager"]]["fen_zen_count"]/$grouplist[$value["kfmanager"]]["count"]*100,4));
            //汇总部门
            $department["fen"] += $value["fen"];
            $department["zen"] += $value["zen"];
            $department["fen_other"] += $value["fen_other"];
            $department["fen_zen_count"] += setInfNanToN(round($value["fen_zen_count"],2));
            $department["count"] += $value["count"];
            $department["fen_rate"] = setInfNanToN(round($department["fen_other"]/$department["count"]*100,4));
            $department["fen_zen_rate"] = setInfNanToN(round($department["fen_zen_count"]/$department["count"]*100,4));
         }

        return array($list,$grouplist,$department);
     }

    private function getReviewList($begin,$end,$cs,$kfgroup,$manager,$id)
    {

        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));
        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime("+1 day", strtotime($end))-1;
        }

        //获取全部城市
        $result = D("Quyu")->getAllQuyuOnly();
        foreach ($result as $key => $value) {
            $quyu[$value["cs"]] = $value;
        }

        //获取客服的发单量
        $poolList = D("OrderPool")->getCityOrdereffective($id,$cs,$kfgroup,$manager,$monthStart,$monthEnd);

        //获取客服分单量
        $fenList = D("OrderPool")->getKFOrderOperate($id,$cs,$kfgroup,$manager,$monthStart,$monthEnd);

        foreach ($quyu as $key => $value) {
            foreach ($kfList as $val) {
                if ($value["cid"] == $val["cs"]) {
                        $city["child"][$val["cs"]]["cname"] = $value["cname"];
                }
            }
        }

        //获取全部城市
        $result = D("Quyu")->getAllQuyuOnly();
        foreach ($result as $key => $value) {
            $quyu[$value["cid"]] = $value;
        }


        if (count($cs) == 1) {
            //如果有城市参数则按照客服明细显示
            $list["cname"] = $quyu[$cs]["cname"];
            //获取全部客服
            $kfList = D("Adminuser")->getKfList(false,true);

            //将客服信息归类
            foreach ($kfList as $key => $value) {
                foreach ($poolList as $key => $val) {
                    if ($value["id"] == $val["op_uid"]) {
                        $list["child"][$val["op_uid"]]["all"] = $val["all"];
                        $list["child"][$val["op_uid"]]["name"] = $value["name"];
                        $list["child"][$val["op_uid"]]["kfgroup"] = $value["kfgroup"];
                    }
                }
            }

            foreach ($kfList as $key => $value) {
                foreach ($fenList as $key => $val) {
                    if ($value["id"] == $val["user_id"]) {
                        $list["child"][$val["user_id"]]["fen"] = $val["fen"];
                        $list["child"][$val["user_id"]]["zen"] = $val["zen"];
                        $list["child"][$val["user_id"]]["fen_other"] = $val["fen_other"];
                        $list["child"][$val["user_id"]]["zen_other"] = $val["zen_other"];
                        $list["child"][$val["user_id"]]["name"] = $value["name"];
                    }
                }
            }
        } else {
            //归类统计城市的发单量
            foreach ($poolList as $key => $value) {
                $list["child"][$value["cs"]]["all"] += $value["all"];
                $list["child"][$value["cs"]]["cname"] = $value["cname"];
            }

            //归类统计城市的分单量
            foreach ($fenList as $key => $value) {
                if (!array_key_exists($value["cs"],$list["child"])) {
                        $list["child"][$value["cs"]]["cname"] = $quyu[$value["cs"]]["cname"];
                }
                $list["child"][$value["cs"]]["fen"] += $value["fen"];
                $list["child"][$value["cs"]]["zen"] += $value["zen"];
                $list["child"][$value["cs"]]["fen_other"] += $value["fen_other"];
                $list["child"][$value["cs"]]["zen_other"] += $value["zen_other"];
            }
        }

        //计算城市的分单量和分单率
        foreach ($list["child"] as $key => $value) {
            //汇总总数据
            $list["fen"] += $value["fen"];
            $list["zen"] += $value["zen"];
            $list["all"] += $value["all"];
            $list["other"] += $value["fen_other"]+$value["zen_other"];
            $list["fen_other"] += $value["fen_other"];
            $list["zen_other"] += $value["zen_other"];

            //汇总每个城市的数据
            $list["fen_rate"] = setInfNanToN(round($list["fen"]/$list["all"],4))*100;
            $list["fen_zen_rate"] = setInfNanToN(round(($list["fen"]+$list["zen"]/10)/$list["all"],4))*100;
            $list["all_rate"] = setInfNanToN(round(($list["fen"]+$list["zen"]+$list["other"])/$list["all"],4))*100;

            $list["child"][$key]["fen_rate"] = setInfNanToN(round($value["fen"]/$value["all"],4))*100;
            $list["child"][$key]["fen_zen_rate"] = setInfNanToN(round(($value["fen"]+$value["zen"]/10)/$value["all"],4))*100;
            $list["child"][$key]["all_rate"] = setInfNanToN(round(($value["fen"]+$value["zen"]+$value["fen_other"]+$value["zen_other"])/$value["all"],4))*100;
        }
        return $list;
     }

     private function getKfGroupInfo($uid = 31)
     {

        $edition = array();
        $result = D("Adminuser")->getKfGroupInfo($uid);
        foreach ($result as $key => $value) {
            if (!empty($value["kfgroup"])) {
                $list["groups"][$value["id"]] = $value;
                $edition[] = $value["kfgroup"];
                if (!array_key_exists($value["manager_id"],$list["manager"])) {
                    $list["manager"][$value["manager_id"]] = array(
                        "id" => $value["manager_id"],
                        "name" => $value["manager"]
                    );
                }
            }
        }
        // array_multisort($edition, SORT_ASC,$list["groups"]);
        return $list;
     }

     /**
        * 获取有会员的城市
        * @param  [type] $begin [description]
        * @param  [type] $end   [description]
        * @return [type]        [description]
        */
     private function getCitys($begin,$end)
     {
        $monthStart = date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")));
        $monthEnd = date("Y-m-d");

        //每个月的1号18点之前只能看上个月的统计
        if (date("d") == "01" && date("H") < 18) {
                    $time = strtotime($monthStart);
                    $monthStart = date("Y-m-d",strtotime("-1 month",mktime(0,0,0,date("m",$time),1,date("Y",$time))));
                    $monthEnd = date("Y-m-d",strtotime("-1 month",mktime(0,0,0,date("m",$time),date("t",$time),date("Y",$time))));
        }

        if (!empty($begin) && !empty($end)) {
                    $monthStart = $begin;
                    $monthEnd = $end;
        }

        $list = D("User")->getCitys($monthStart,$monthEnd);
        return $list;
     }

    private function getCustomerOrderStatisticsList($id, $group, $date, $time)
    {
        if (count($time) > 0) {
            foreach ($time as $key => $value) {
                $step[] = $this->time_interval[$value];
            }
        }

        $result = D("Orders")->getCustomerOrderStatisticsList($id, $group, $date, $step);

        foreach ($result as $key => $value) {
            if (count($group) == 0) {
                $list[$value["kfgroup"]]["child"][$value["time"]]["time"] = $value["time"];
                $list[$value["kfgroup"]]["child"][$value["time"]]["fen"] += $value["fen"];
                $list[$value["kfgroup"]]["child"][$value["time"]]["zen"] += $value["zen"];
                $list[$value["kfgroup"]]["child"][$value["time"]]["all"] += $value["all"];

                $list[$value["kfgroup"]]["child"][$value["time"]]["fen_other"] = $list[$value["kfgroup"]]["child"][$value["time"]]["fen"]+$list[$value["kfgroup"]]["child"][$value["time"]]["zen"]/10;
                $list[$value["kfgroup"]]["child"][$value["time"]]["fen_rate"] = setInfNanToN(round(($list[$value["kfgroup"]]["child"][$value["time"]]["fen"]/$list[$value["kfgroup"]]["child"][$value["time"]]["all"])*100,4));
                $list[$value["kfgroup"]]["child"][$value["time"]]["fen_other_rate"] = setInfNanToN(round(($list[$value["kfgroup"]]["child"][$value["time"]]["fen_other"]/$list[$value["kfgroup"]]["child"][$value["time"]]["all"])*100,4));

                //汇总团队数据
                $list[$value["kfgroup"]]["kfgroup"] = $value['kfgroup'];
                $list[$value["kfgroup"]]["all"]["fen"] += $value["fen"];
                $list[$value["kfgroup"]]["all"]["zen"] += $value["zen"];
                $list[$value["kfgroup"]]["all"]["all"] += $value["all"];
                $list[$value["kfgroup"]]["all"]["fen_other"] = $list[$value["kfgroup"]]["all"]["fen"]+$list[$value["kfgroup"]]["all"]["zen"]/10;
                $list[$value["kfgroup"]]["all"]["fen_rate"] = setInfNanToN(round(($list[$value["kfgroup"]]["all"]["fen"]/$list[$value["kfgroup"]]["all"]["all"])*100,4));
                $list[$value["kfgroup"]]["all"]["fen_other_rate"] = setInfNanToN(round(($list[$value["kfgroup"]]["all"]["fen_other"]/$list[$value["kfgroup"]]["all"]["all"])*100,4));

                if (count($group) == 1) {
                    //只有1个组的时候，统计客服的详细数据
                    if (!array_key_exists($value["id"],$list)) {
                        $list[$value["id"]]["name"] = $value["name"];
                        $list[$value["id"]]["kfgroup"] = $value["kfgroup"];
                    }
                    $list[$value["id"]]["child"][$value["time"]]["time"] = $value["time"];
                    $list[$value["id"]]["child"][$value["time"]]["fen"]  = $value["fen"];
                    $list[$value["id"]]["child"][$value["time"]]["zen"]  = $value["zen"];
                    $list[$value["id"]]["child"][$value["time"]]["all"]  = $value["all"];
                    $list[$value["id"]]["child"][$value["time"]]["fen_other"]  = setInfNanToN(round($value["fen"]+$value["zen"]/10,2));
                    $list[$value["id"]]["child"][$value["time"]]["fen_rate"]  = setInfNanToN(round(($value["fen"]/$value["all"])*100,4));
                    $list[$value["id"]]["child"][$value["time"]]["fen_other_rate"]  = setInfNanToN(round(($value["fen"]+$value["zen"]/10)/$value["all"]*100,4));

                    //个人汇总
                    $list[$value["id"]]["all"]["fen"] += $value["fen"];
                    $list[$value["id"]]["all"]["zen"] += $value["zen"];
                    $list[$value["id"]]["all"]["all"] += $value["all"];
                    $list[$value["id"]]["all"]["fen_other"]  =  $list[$value["id"]]["all"]["fen"]+ $list[$value["id"]]["all"]["zen"]/10;
                    $list[$value["id"]]["all"]["fen_rate"] = setInfNanToN(round($list[$value["id"]]["all"]["fen"]/$list[$value["id"]]["all"]["all"]*100,4));
                    $list[$value["id"]]["all"]["fen_other_rate"] = setInfNanToN(round($list[$value["id"]]["all"]["fen_other"]/$list[$value["id"]]["all"]["all"]*100,4));
                }
            } else {
                if (!array_key_exists($value["id"],$list)) {
                    $list[$value["id"]]["name"] = $value["name"];
                    $list[$value["id"]]["kfgroup"] = $value["kfgroup"];
                }
                $list[$value["id"]]["child"][$value["time"]]["time"] = $value["time"];
                $list[$value["id"]]["child"][$value["time"]]["fen"]  = $value["fen"];
                $list[$value["id"]]["child"][$value["time"]]["zen"]  = $value["zen"];
                $list[$value["id"]]["child"][$value["time"]]["all"]  = $value["all"];
                $list[$value["id"]]["child"][$value["time"]]["fen_other"]  = setInfNanToN(round($value["fen"]+$value["zen"]/10,2));
                $list[$value["id"]]["child"][$value["time"]]["fen_rate"]  = setInfNanToN(round(($value["fen"]/$value["all"])*100,4));
                $list[$value["id"]]["child"][$value["time"]]["fen_other_rate"]  = setInfNanToN(round(($value["fen"]+$value["zen"]/10)/$value["all"]*100,4));

                //个人汇总
                $list[$value["id"]]["all"]["fen"] += $value["fen"];
                $list[$value["id"]]["all"]["zen"] += $value["zen"];
                $list[$value["id"]]["all"]["all"] += $value["all"];
                $list[$value["id"]]["all"]["fen_other"]  =  $list[$value["id"]]["all"]["fen"]+ $list[$value["id"]]["all"]["zen"]/10;
                $list[$value["id"]]["all"]["fen_rate"] = setInfNanToN(round($list[$value["id"]]["all"]["fen"]/$list[$value["id"]]["all"]["all"]*100,4));
                $list[$value["id"]]["all"]["fen_other_rate"] = setInfNanToN(round($list[$value["id"]]["all"]["fen_other"]/$list[$value["id"]]["all"]["all"]*100,4));
            }

            //全部汇总
            $all["fen"] += $value["fen"];
            $all["zen"] += $value["zen"];
            $all["all"] += $value["all"];
            $all["fen_other"] = $all["fen"]+$all['zen']/10;
            $all["fen_rate"] = setInfNanToN(round($all["fen"]/$all["all"]*100,4));
            $all["fen_other_rate"] = setInfNanToN(round($all["fen_other"]/$all["all"]*100,4));
        }

        return array($list, $all, count($group));
    }

    /**
     * 获取客服数据图标数据
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function getcustomerorderanalysis($begin,$end)
    {
            $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
            $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));
            if (!empty($begin) && !empty($end)) {
                    $monthStart = strtotime($begin);
                    $endDay = $monthEnd = strtotime("+1 day", strtotime($end))-1;
            } else {
                    $endDay = strtotime("+1 day", strtotime(date('Y-m-d')));
            }
            $dayCount = ceil(($endDay - $monthStart) / 86400);

            //获取客服的发单量
            $result = D("OrderPool")->getcustomerorderanalysis($monthStart,$monthEnd);

            foreach ($result[0] as $key => $value) {
                    //按人统计
                    $user[$value["op_uid"]]["name"] = $value["op_name"];
                    $user[$value["op_uid"]]["id"] = $value["op_uid"];
                    $user[$value["op_uid"]]["kfgroup"] = $value["kfgroup"];
                    $user[$value["op_uid"]]["kfmanager"] = $value["kfmanager"];
                    $user[$value["op_uid"]]["date"][$value["date"]]["date"] = $value["date"];
                    $user[$value["op_uid"]]["date"][$value["date"]]["all"] += $value["all"];

                    //按组统计
                    $group[$value["kfgroup"]]["id"] = $value["op_uid"];
                    $group[$value["kfgroup"]]["name"] = "客服".$value["kfgroup"]."团";
                    $group[$value["kfgroup"]]["date"][$value["date"]]["all"] += $value["all"];
                    $group[$value["kfgroup"]]["date"][$value["date"]]["date"] = $value["date"];

                    //按师统计
                    $manager[$value["kfmanager"]]["name"] = $value["manager"];
                    $manager[$value["kfmanager"]]["date"][$value["date"]]["all"] += $value["all"];
                    $manager[$value["kfmanager"]]["date"][$value["date"]]["date"] = $value["date"];

                    //按城市统计
                    $city[$value["cs"]]["name"] = $value["cname"];
                    $city[$value["cs"]]["date"][$value["date"]]["all"] += $value["all"];
                    $city[$value["cs"]]["date"][$value["date"]]["date"] = $value["date"];

            }

            foreach ($result[1] as $key => $value) {
                    if (!isset($user[$value["user_id"]])) {
                            $user[$value["user_id"]]["name"] = $value["name"];
                            $user[$value["user_id"]]["id"] = $value["user_id"];
                            $user[$value["user_id"]]["kfgroup"] = $value["kfgroup"];
                            $user[$value["user_id"]]["kfmanager"] = $value["kfmanager"];
                    }

                    if (!isset($group[$value["kfgroup"]])) {
                            $group[$value["kfgroup"]]["name"] = "客服".$value["kfgroup"]."团";
                            $group[$value["kfgroup"]]["id"] = $value["user_id"];
                    }

                    if (!isset($manager[$value["kfmanager"]])) {
                            $manager[$value["kfmanager"]]["name"] = $value["manager"];
                    }

                    if (!isset($manager[$value["kfmanager"]])) {
                            $city[$value["cs"]]["name"] = $value["cname"];
                    }

                    //按人统计
                    $user[$value["user_id"]]["date"][$value["date"]]["date"] = $value["date"];
                    $user[$value["user_id"]]["date"][$value["date"]]["fen"] += $value["fen"];
                    $user[$value["user_id"]]["date"][$value["date"]]["zen"] += $value["zen"];
                    $user[$value["user_id"]]["date"][$value["date"]]["fen_zen"] = $user[$value["user_id"]]["date"][$value["date"]]["fen"] + $user[$value["user_id"]]["date"][$value["date"]]["zen"]/10;
                    $user[$value["user_id"]]["date"][$value["date"]]["fen_rate"] = setInfNanToN(round($user[$value["user_id"]]["date"][$value["date"]]["fen"]/$user[$value["user_id"]]["date"][$value["date"]]["all"],6))*100;
                    $user[$value["user_id"]]["date"][$value["date"]]["fen_zen_rate"] = setInfNanToN(round($user[$value["user_id"]]["date"][$value["date"]]["fen_zen"]/$user[$value["user_id"]]["date"][$value["date"]]["all"],4))*100;

                    //按组统计
                    $group[$value["kfgroup"]]["date"][$value["date"]]["date"] = $value["date"];
                    $group[$value["kfgroup"]]["date"][$value["date"]]["fen"] += $value["fen"];
                    $group[$value["kfgroup"]]["date"][$value["date"]]["zen"] += $value["zen"];
                    $group[$value["kfgroup"]]["date"][$value["date"]]["fen_zen"] = $group[$value["kfgroup"]]["date"][$value["date"]]["fen"] + $group[$value["kfgroup"]]["date"][$value["date"]]["zen"]/10;
                    $group[$value["kfgroup"]]["date"][$value["date"]]["fen_rate"] = setInfNanToN(round($group[$value["kfgroup"]]["date"][$value["date"]]["fen"]/$group[$value["kfgroup"]]["date"][$value["date"]]["all"],6))*100;
                    $group[$value["kfgroup"]]["date"][$value["date"]]["fen_zen_rate"] = setInfNanToN(round($group[$value["kfgroup"]]["date"][$value["date"]]["fen_zen"]/$group[$value["kfgroup"]]["date"][$value["date"]]["all"],4))*100;

                    //按师统计
                    $manager[$value["kfmanager"]]["date"][$value["date"]]["date"] = $value["date"];
                    $manager[$value["kfmanager"]]["date"][$value["date"]]["fen"] += $value["fen"];
                    $manager[$value["kfmanager"]]["date"][$value["date"]]["zen"] += $value["zen"];
                    $manager[$value["kfmanager"]]["date"][$value["date"]]["fen_zen"] = $manager[$value["kfmanager"]]["date"][$value["date"]]["fen"] + $manager[$value["kfmanager"]]["date"][$value["date"]]["zen"]/10;
                    $manager[$value["kfmanager"]]["date"][$value["date"]]["fen_rate"] = setInfNanToN(round($manager[$value["kfmanager"]]["date"][$value["date"]]["fen"]/$manager[$value["kfmanager"]]["date"][$value["date"]]["all"],6))*100;
                    $manager[$value["kfmanager"]]["date"][$value["date"]]["fen_zen_rate"] = setInfNanToN(round($manager[$value["kfmanager"]]["date"][$value["date"]]["fen_zen"]/$manager[$value["kfmanager"]]["date"][$value["date"]]["all"],4))*100;

                    //按城市统计
                    $city[$value["cs"]]["date"][$value["date"]]["date"] = $value["date"];
                    $city[$value["cs"]]["date"][$value["date"]]["fen"] += $value["fen"];
                    $city[$value["cs"]]["date"][$value["date"]]["zen"] += $value["zen"];
                    $city[$value["cs"]]["date"][$value["date"]]["fen_zen"] = $city[$value["cs"]]["date"][$value["date"]]["fen"] + $city[$value["cs"]]["date"][$value["date"]]["zen"]/10;
                    $city[$value["cs"]]["date"][$value["date"]]["fen_rate"] = setInfNanToN(round($city[$value["cs"]]["date"][$value["date"]]["fen"]/$city[$value["cs"]]["date"][$value["date"]]["all"],6))*100;
                    $city[$value["cs"]]["date"][$value["date"]]["fen_zen_rate"] = setInfNanToN(round($city[$value["cs"]]["date"][$value["date"]]["fen_zen"]/$city[$value["cs"]]["date"][$value["date"]]["all"],4))*100;

            }
            unset($result);
            for ($i=0; $i < $dayCount; $i++) {
                    $date = date("Y-m-d",strtotime("+$i day",$monthStart));
                    $dateArr[] = $date;
                    foreach ($group as $key => $value) {
                            if (!isset($value["date"][$date])) {
                                    $group[$key]["date"][$date]["date"] = $date;
                                    $group[$key]["date"][$date]["all"] = 0;
                                    $group[$key]["date"][$date]["fen"] = 0;
                                    $group[$key]["date"][$date]["zen"] = 0;
                                    $group[$key]["date"][$date]["fen_zen"] = 0;
                                    $group[$key]["date"][$date]["fen_rate"] = 0;
                                    $group[$key]["date"][$date]["fen_zen_rate"] = 0;
                            }
                    }

                    foreach ($user as $key => $value) {
                            if (!isset($value["date"][$date])) {
                                    $user[$key]["date"][$date]["date"] = $date;
                                    $user[$key]["date"][$date]["all"] = 0;
                                    $user[$key]["date"][$date]["fen"] = 0;
                                    $user[$key]["date"][$date]["zen"] = 0;
                                    $user[$key]["date"][$date]["fen_zen"] = 0;
                                    $user[$key]["date"][$date]["fen_rate"] = 0;
                                    $user[$key]["date"][$date]["fen_zen_rate"] = 0;
                            }
                    }

                    foreach ($manager as $key => $value) {
                            if (!isset($value["date"][$date])) {
                                    $manager[$key]["date"][$date]["date"] = $date;
                                    $manager[$key]["date"][$date]["all"] = 0;
                                    $manager[$key]["date"][$date]["fen"] = 0;
                                    $manager[$key]["date"][$date]["zen"] = 0;
                                    $manager[$key]["date"][$date]["fen_zen"] = 0;
                                    $manager[$key]["date"][$date]["fen_rate"] = 0;
                                    $manager[$key]["date"][$date]["fen_zen_rate"] = 0;
                            }
                    }

                    foreach ($city as $key => $value) {
                            if (!isset($value["date"][$date])) {
                                    $city[$key]["date"][$date]["date"] = $date;
                                    $city[$key]["date"][$date]["all"] = 0;
                                    $city[$key]["date"][$date]["fen"] = 0;
                                    $city[$key]["date"][$date]["zen"] = 0;
                                    $city[$key]["date"][$date]["fen_zen"] = 0;
                                    $city[$key]["date"][$date]["fen_rate"] = 0;
                                    $city[$key]["date"][$date]["fen_zen_rate"] = 0;
                            }
                    }
            }

            //按时间重新排序
            foreach ($user as $key => $value) {
                    $edition = array();
                    foreach ($value["date"] as $k =>$val) {
                            $edition[] = $val["date"];
                    }
                    array_multisort($edition, SORT_ASC, $user[$key]["date"]);
            }
            unset($edition);
            foreach ($group as $key => $value) {
                    $edition = array();
                    foreach ($value["date"] as $k =>$val) {
                            $edition[] = $val["date"];
                    }
                    array_multisort($edition, SORT_ASC,$group[$key]["date"]);
            }
            unset($edition);
            foreach ($manager as $key => $value) {
                    $edition = array();
                    foreach ($value["date"] as $k =>$val) {
                            $edition[] = $val["date"];
                    }
                    array_multisort($edition, SORT_ASC,$manager[$key]["date"]);
            }
            unset($edition);
            foreach ($city as $key => $value) {
                    $edition = array();
                    foreach ($value["date"] as $k =>$val) {
                            $edition[] = $val["date"];
                    }
                    array_multisort($edition, SORT_ASC,$city[$key]["date"]);
            }
            unset($edition);

            return array("user"=>$user,"group"=>$group,"date"=>$dateArr,"manager"=>$manager,"city"=>$city);
    }

    //客服量房统计
    private function getCustomerOrderliangfangList($id,$group,$manager,$begin,$end)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));
        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $endDay = $monthEnd = strtotime("+1 day", strtotime($end))-1;
        } else {
            $endDay = strtotime("+1 day", strtotime(date('Y-m-d')));
        }

        $dayCount = ceil(($endDay - $monthStart) / 86400);
        if ($dayCount > 31) {
            $this->_error("查询时间不能大于31天");
            return false;
        }

        $getIndex = I("get.index");
        if (empty($getIndex) || $getIndex == "1" || $getIndex == "2" || $getIndex == "3") {
            // 获取客服日期和发单
            // $kfList = D("Adminuser")->getKfLoginDay($id,$group,$manager,$monthStart,$monthEnd);
            $kfList = D("Adminuser")->getKfLoginDayByLFPool($id,$group,$manager,$monthStart,$monthEnd);
            $userids = array_column($kfList, "id");

            //获取客服的发单量,分赠单量
            $result = D("OrderPool")->getKfOrderliangfang($id,$group,$manager,$monthStart,$monthEnd,1);

            //获取全部量房数据
            $orderLogic = new OrdersLogicModel();
            $lfList = $orderLogic->getAllLiangFangList($id,$group,$manager,$monthStart,$monthEnd,$userids);

            foreach ($result as $key => $value) {
                $list[$value["op_uid"]] = $value;
            }

            foreach ($kfList as $key => $value) {
                $value["kfmanager"] = str_replace(",", "", $value["kfmanager"]);

                $kfList[$key]["all"] = $list[$value["id"]]["all"];
                $kfList[$key]["fen"] = $list[$value["id"]]["fen"];
                $kfList[$key]["zen"] = $list[$value["id"]]["zen"];

                //全部量房量
                $kfList[$key]["lf_count"] = 0;
                if (array_key_exists($value["id"],$lfList["kflist"])) {
                    $kfList[$key]["lf_count"] = $lfList["kflist"][$value["id"]];
                }

                if ($kfList[$key]["all"] == 0 && $kfList[$key]["fen"] == 0 && $kfList[$key]["zen"] == 0 && $kfList[$key]["lf_count"] == 0) {
                    unset($kfList[$key]);
                }

                //组统计
                $groups[$value["kfgroup"]]["groupmanager"] = $value["groupmanager"];
                $groups[$value["kfgroup"]]["kfgroup"] = $value["kfgroup"];
                $groups[$value["kfgroup"]]["count"] ++;
                $groups[$value["kfgroup"]]["manager"] = $value["manager"];
                $groups[$value["kfgroup"]]["day"] += $value["day"];
                $groups[$value["kfgroup"]]["all"] += $kfList[$key]["all"];
                $groups[$value["kfgroup"]]["fen"] += $kfList[$key]["fen"];
                $groups[$value["kfgroup"]]["zen"] += $kfList[$key]["zen"];

                //全部量房量
                $groups[$value["kfgroup"]]["lf_count"] = $lfList["group"][$value["kfgroup"]];

                //按师统计
                $managers[$value["kfmanager"]]["manager"] = $value["manager"];
                $managers[$value["kfmanager"]]["count"] ++;
                $managers[$value["kfmanager"]]["day"] += $value["day"];
                $managers[$value["kfmanager"]]["all"] += $kfList[$key]["all"];
                $managers[$value["kfmanager"]]["fen"] += $kfList[$key]["fen"];
                $managers[$value["kfmanager"]]["zen"] += $kfList[$key]["zen"];

                $managers[$value["kfmanager"]]["child"][$value["kfgroup"]] = $groups[$value["kfgroup"]];

                //全部量房量
                // $managers[$value["kfmanager"]]["lf_count"] = $lfList["manager"][$value["kfmanager"]];
            }

            foreach ($managers as $key => $item) {
                foreach ($item["child"] as $k => $val) {
                    $managers[$key]["lf_count"] += $val["lf_count"];
                }
            }

            //总计，直接从按师统计里获取
            foreach ($managers as $key => $value) {
                $total['all'] = $total['all'] + $value['all'];
                $total['fen'] = $total['fen'] + $value['fen'];
                $total['zen'] = $total['zen'] + $value['zen'];

                $total['lf_count'] += $value['lf_count'];
            }
        }


        //按日统计
        if ($getIndex == "4") {
            if (!empty($id) || !empty($group) || !empty($manager)) {
                $result = D("OrderPool")->getKfOrderliangfangByDay($id,$group,$manager,$monthStart,$monthEnd);

                $count = 0;
                foreach ($result as $key => $value) {
                    $count ++;
                    $dayList["child"][$key]["op_name"] = $value["op_name"];
                    if (!empty($group)) {
                        $dayList["child"][$key]["op_name"] = $value["group_name"];
                    } elseif (!empty($manager)) {
                        $dayList["child"][$key]["op_name"] = $value["name"];
                    }
                    $dayList["child"][$key]["date"] = $value["date"];
                    $dayList["child"][$key]["fen"] = $value["fen"];
                    $dayList["child"][$key]["zen"] = $value["zen"];
                    $dayList["child"][$key]["all"] = $value["all"];
                    $dayList["child"][$key]["lf_count"] = $value["lf_count"];

                    $dayList["all"]["fen"] += $value["fen"];
                    $dayList["all"]["zen"] += $value["zen"];
                    $dayList["all"]["all"] += $value["all"];
                    $dayList["all"]["lf_count"] += $value["lf_count"];
                }
            }
        }

        if ($getIndex == "5") {
            //按中心
            $result = D("OrderPool")->getKfOrderliangfangByDay("","","",$monthStart,$monthEnd);

            $count = 0;
            foreach ($result as $key => $value) {
                $count ++;
                $center["child"][$value["date"]]["date"] = $value["date"];
                $center["child"][$value["date"]]["fen"] += $value["fen"];
                $center["child"][$value["date"]]["zen"] += $value["zen"];
                $center["child"][$value["date"]]["all"] += $value["all"];
                $center["child"][$value["date"]]["lf_count"] += $value["lf_count"];

                $center["all"]["fen"] += $value["fen"];
                $center["all"]["zen"] += $value["zen"];
                $center["all"]["all"] += $value["all"];
                $center["all"]["lf_count"] += $value["lf_count"];
            }
        }

        $kfList = array_values($kfList);
        return array($kfList,$groups,$managers,$total,$dayList,$center);
    }

    //客服有效量房统计
    private function getCustomerOrderyouxiaoliangfangList($id,$group,$manager,$begin,$end)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));
        $monthThreeStart = mktime(0,0,0,date("m")-3,date("t"),date("Y"));
        $monthThreeEnd = $monthEnd;
        $month = date('Y年m月');
        $begintemp = date('Y-m');
        $monthTotalEnd = $monthEnd;
        if (!empty($begin) && empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime('+1 month', strtotime($begin))-1;
            $monthThreeStart = strtotime('-2 month', strtotime($begin));
            $monthThreeEnd = $monthEnd;
            $month =  date("Y年m月",strtotime($begin));
            $begintemp = date('Y-m',strtotime($begin));
            $monthTotalEnd = $monthEnd;
        }else if(!empty($begin) && !empty($end)){
            $monthStart = strtotime($begin);
            $monthEnd = strtotime('+1 month', strtotime($begin))-1;
            $monthThreeStart = strtotime('-2 month', strtotime($begin));
            $monthThreeEnd = $monthEnd;
            $month =  date("Y年m月",strtotime($begin));
            $begintemp = date('Y-m',strtotime($begin));
            $monthTotalEnd = strtotime('+1 month', strtotime($end))-1;
        }


        // 获取客服登录天数
        $kfList = D("Adminuser")->getKfLoginDayByLFPool($id,$group,$manager,$monthThreeStart,$monthThreeEnd);

        //获取客服的发单量,分赠单量
        $result = D("OrderPool")->getKfOrderyouxiaoliangfang($id,$group,$manager,$monthStart,$monthEnd,$monthThreeStart,$monthThreeEnd,1);
        //获取客服的量房数
        $result2 = D("OrderPool")->getKfOrderyouxiaoliangfang($id,$group,$manager,$monthStart,$monthEnd,$monthThreeStart,$monthThreeEnd,2);

        foreach ($result as $key => $value) {
            $list[$value["op_uid"]] = $value;
        }
        foreach ($result2 as $key => $value) {
            $list2[$value["user_id"]] = $value;
        }
        foreach ($kfList as $key => $value) {
            $value["kfmanager"] = str_replace(",", "", $value["kfmanager"]);
            $kfList[$key]["kf"] = $value["id"];
            $kfList[$key]["all"] = $list[$value["id"]]["all"];
            $kfList[$key]["fen"] = $list[$value["id"]]["fen"];
            $kfList[$key]["zen"] = $list[$value["id"]]["zen"];
            //有效量房数
            $kfList[$key]["youxiao"] = $list2[$value["id"]]["youxiao"];
            //有效量房率合计
            $kfList[$key]["youxiao_rate"] = setInfNanToN(round( $kfList[$key]["youxiao"]/($kfList[$key]["fen"]+$kfList[$key]["zen"]),4))*100;
            $kfList[$key]["month"] = $month;
            $kfList[$key]["begin"] = $begintemp;

            //组统计
            $groups[$value["kfgroup"]]["groupmanager"] = $value["groupmanager"];
            $groups[$value["kfgroup"]]["kfgroup"] = $value["kfgroup"];
            $groups[$value["kfgroup"]]["count"] ++;
            $groups[$value["kfgroup"]]["manager"] = $value["manager"];
            $groups[$value["kfgroup"]]["day"] += $value["day"];
            $groups[$value["kfgroup"]]["all"] += $kfList[$key]["all"];
            $groups[$value["kfgroup"]]["fen"] += $kfList[$key]["fen"];
            $groups[$value["kfgroup"]]["zen"] += $kfList[$key]["zen"];
            //有效量房数
            $groups[$value["kfgroup"]]["youxiao"] += $kfList[$key]["youxiao"];
            //有效量房率合计
            $groups[$value["kfgroup"]]["youxiao_rate"] = setInfNanToN(round(  $groups[$value["kfgroup"]]["youxiao"]/($groups[$value["kfgroup"]]["fen"]+$groups[$value["kfgroup"]]["zen"]),4))*100;
            $groups[$value["kfgroup"]]["month"] = $month;

            //按师统计
            $managers[$value["kfmanager"]]["manager"] = $value["manager"];
            $managers[$value["kfmanager"]]["count"] ++;
            $managers[$value["kfmanager"]]["day"] += $value["day"];
            $managers[$value["kfmanager"]]["all"] += $kfList[$key]["all"];
            $managers[$value["kfmanager"]]["fen"] += $kfList[$key]["fen"];
            $managers[$value["kfmanager"]]["zen"] += $kfList[$key]["zen"];

            //有效量房数
            $managers[$value["kfmanager"]]["youxiao"] += $kfList[$key]["youxiao"];
            //有效量房率合计
            $managers[$value["kfmanager"]]["youxiao_rate"] = setInfNanToN(round(  $managers[$value["kfmanager"]]["youxiao"]/($managers[$value["kfmanager"]]["fen"]+$managers[$value["kfmanager"]]["zen"]),4))*100;
            $managers[$value["kfmanager"]]["month"] = $month;
            $managers[$value["kfmanager"]]["child"][$value["kfgroup"]]= $groups[$value["kfgroup"]];
        }

        //总计，直接从按师统计里获取
        foreach ($managers as $key => $value) {
            $total['all'] = $total['all'] + $value['all'];
            $total['fen'] = $total['fen'] + $value['fen'];
            $total['zen'] = $total['zen'] + $value['zen'];

            //有效量房数
            $total["youxiao"] += $value["youxiao"];
        }
        //有效量房率合计
        $total["youxiao_rate"] = setInfNanToN(round(  $total["youxiao"]/($total["fen"]+$total['zen']),4))*100;

        //重新排序
        $edition = array();
        foreach ($kfList as $key => $value) {
            $edition[] = $value["youxiao_rate"];
        }
        array_multisort($edition, SORT_DESC,$kfList);

        $edition = array();
        foreach ($groups as $key => $value) {
            $edition[] = $value["youxiao_rate"];
        }
        array_multisort($edition, SORT_DESC,$groups);

        //按中心
        $startY= date('Y',$monthThreeStart);
        $endY = date('Y',$monthTotalEnd);
        $startm= date('m',$monthThreeStart);
        $endm = date('m',$monthTotalEnd);
        $tempMonth = ($startY-$endY)*12+$endm-$startm+1;
        $tempStart = strtotime('-1 month', $monthThreeStart);

        for($i=0;$i<$tempMonth;$i++){
            $tempStart= strtotime('+1 month', $tempStart);
            //$tempEnd = strtotime('+1 month', $tempEnd);
            $tempEnd = mktime(23, 59, 59, date('m', $tempStart)+1, 00);
            $tempThreeStart= strtotime('-2 month', $tempStart);
            $result = D("OrderPool")->getKfOrderyouxiaoliangfangByDay("","","",$tempStart,$tempEnd,$tempThreeStart,$monthThreeEnd,$tempEnd);
            $tempCenter[$i] = $result[0];
            $tempCenter[$i]["date"] = date('Y-m',$tempStart);
        }
        for($i = 2;$i<count($tempCenter);$i++){
                $center["child"][$i-2]["date"] = $tempCenter[$i]["date"];
                $center["child"][$i-2]["fen"]  = $tempCenter[$i]["fen"]+$tempCenter[$i-1]["fen"]+$tempCenter[$i-2]["fen"];
                $center["child"][$i-2]["zen"]  = $tempCenter[$i]["zen"]+$tempCenter[$i-1]["zen"]+$tempCenter[$i-2]["zen"];
                $center["child"][$i-2]["all"]  = $tempCenter[$i]["all"]+$tempCenter[$i-1]["all"]+$tempCenter[$i-2]["all"];
                //有效量房数
                $center["child"][$i-2]["youxiao"] = $tempCenter[$i]["youxiao"];
                //有效量房率合计
                $center["child"][$i-2]["youxiao_rate"] = setInfNanToN(round( $center["child"][$i-2]["youxiao"]/($center["child"][$i-2]["fen"]+$center["child"][$i-2]["zen"]),4))*100;
            //有效量房数
            $center["all"]["youxiao"] += $tempCenter[$i]["youxiao"];
        }
        for($i = 0;$i<count($tempCenter);$i++){
            $center["all"]["fen"] += $tempCenter[$i]["fen"];
            $center["all"]["zen"] += $tempCenter[$i]["zen"];
            $center["all"]["all"] += $tempCenter[$i]["all"];
            //有效量房率合计
            $center["all"]["youxiao_rate"] = setInfNanToN(round( ($center["all"]["youxiao"]/($center["all"]["fen"]+$center["all"]["zen"])),4))*100;
        }
        return array($kfList,$groups,$managers,$total,$center);
    }
    private function getCustomerOrderyouxiaoliangfangDetailList($id,$begin)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));
        $monthThreeStart = mktime(0,0,0,date("m")-3,date("t"),date("Y"));
        $monthThreeEnd = $monthEnd;
        if (!empty($begin)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime('+1 month', strtotime($begin))-1;
            $monthThreeStart = strtotime('-2 month', strtotime($begin));
            $monthThreeEnd = $monthEnd;
        }
        $list = D("OrderPool")->OrderyouxiaoliangfangDetailList($id,$monthStart,$monthEnd,$monthThreeStart,$monthThreeEnd);
        return $list;
    }
    /**
     * 获取签单统计列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    private function orderQiandanTelList($cs,$begin,$end,$type)
    {
        //$monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        //$monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));

        /*V7.1.8管理后台-签单呼叫次数统计新增需求,修改默认时间*/
        $monthEnd = strtotime(date('Y-m-d', strtotime('+1 day')));
        $monthStart = strtotime(date('Y-m-d', strtotime(date('Y-m-d', strtotime('-29 day')))));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end);
        }

        $count = D("Orders")->orderQiandanTelListCount($cs,$monthStart,$monthEnd,$type);

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $show = $p->show();//,$p->firstRow,$p->listRows

            $list = D("Orders")->orderQiandanTelList($cs,$monthStart,$monthEnd,$type);

            foreach ($list as $key => $value) {
                if (!array_key_exists(trim($value["count"]),$chart["legend"])) {
                    $chart[$value["count"]]["name"] = "呼叫".$value["count"]."次";
                }

                $chart[$value["count"]]["count"] ++;
                $all ++;
            }

            foreach ($chart as $key => $value) {
                $chart[$key]["name"] .= " (".(round($value["count"] / $all, 4) * 100)."%)";
            }

            $list = array_slice($list, $p->firstRow ,$p->listRows);
        }

        return array("list"=>$list,"page"=>$show,"chart"=>$chart);
    }

    /**
     * 客服电话行为统计
     */
    private function getCustomerOrderTelstatList($begin, $end, $kf, $groups, $managers, $citys)
    {
        $monthStart =  strtotime(  date("Y-m-d" ,strtotime("-1 day", mktime(0,0,0,date("m"),1,date("Y"))))." 17:30:00");
        $monthEnd = mktime(17,29,59,date("m"),date("t"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime("-1 day" , strtotime($begin." 17:30:00"));
            $monthEnd = strtotime($end." 17:29:59");
        }

        $result = D("Orders")->getCustomerOrderTelstatList($monthStart,$monthEnd,$kf,$groups,$managers,$citys);
        $user = array();
        foreach ($result as $key => $value) {
            /**
             * 按人统计
             */
            $user[$value["op_uid"]]["ordercount"] ++;
            $user[$value["op_uid"]]["kfgroup"] = $value["kfgroup"];
            $user[$value["op_uid"]]["op_name"] = $value["op_name"];
            //新单呼叫量
            $user[$value["op_uid"]]["count"] +=  $value["count"];
            //呼通量
            if ( $value["overcount"] > 0) {
                $user[$value["op_uid"]]["overCount"] ++;
            }


            //呼通率 新单呼通量/新单量
            $user[$value["op_uid"]]["telRate"] = setInfNanToN(round(($user[$value["op_uid"]]["overCount"]/$user[$value["op_uid"]]["ordercount"])*100,4));
            //分单量，分单时长
            if ($value["on"] == 4 && $value["type_fw"] == 1) {
                $user[$value["op_uid"]]["fen_count"] ++;
                $user[$value["op_uid"]]["fen_time"] += $value["all_time"];
            }
            //分单率 新单分单量/新单呼通量
            $user[$value["op_uid"]]["fenRate"] = setInfNanToN(round( ($user[$value["op_uid"]]["fen_count"]/$user[$value["op_uid"]]["overCount"])*100,4));
            //赠单量
            if ($value["on"] == 4 && $value["type_fw"] == 2) {
                $user[$value["op_uid"]]["zen_count"] ++;
            }
            //赠单率 新单赠单量/新单呼通量
            $user[$value["op_uid"]]["zenRate"] = setInfNanToN(round( ($user[$value["op_uid"]]["zen_count"]/$user[$value["op_uid"]]["overCount"])*100,4));
            //新单呼通拒绝率：1-（新单呼通分单率+新单呼通赠单率）
            $user[$value["op_uid"]]["rejectRate"] = 100 - ($user[$value["op_uid"]]["fenRate"]+$user[$value["op_uid"]]["zenRate"]);
            //新单通话总时长
            $user[$value["op_uid"]]["all_time"] += $value["all_time"];
            //通话平均时长
            $user[$value["op_uid"]]["avg_time"] = setInfNanToN(round($user[$value["op_uid"]]["all_time"]/$user[$value["op_uid"]]["count"],2));
            //平均分单 时长
            $user[$value["op_uid"]]["fen_avg_time"] = setInfNanToN(round($user[$value["op_uid"]]["fen_time"]/$user[$value["op_uid"]]["fen_count"],2));
            // 新单呼通有效单率=（新单呼通分单量+新单呼通赠单量）/新单呼通量
            $user[$value["op_uid"]]["rate"] = setInfNanToN(round(($user[$value["op_uid"]]["fen_count"]+$user[$value["op_uid"]]["zen_count"])/$user[$value["op_uid"]]["overCount"],4))*100;


            /**
            * 按组统计
            */
            $group[$value["kfgroup"]]["ordercount"] ++;
            $group[$value["kfgroup"]]["kfgroup"] = $value["kfgroup"];
            $group[$value["kfgroup"]]["manager"] = $value["manager"];
            $group[$value["kfgroup"]]["count"] +=  $value["count"];

            //呼通量
            if ( $value["overcount"] > 0) {
                $group[$value["kfgroup"]]["overCount"]++;
            }

            //呼通率 新单呼通量/新单量
            $group[$value["kfgroup"]]["telRate"] = setInfNanToN(round(($group[$value["kfgroup"]]["overCount"]/$group[$value["kfgroup"]]["ordercount"])*100,4));
            //分单量，分单时长
            if ($value["on"] == 4 && $value["type_fw"] == 1) {
                    $group[$value["kfgroup"]]["fen_count"] ++;
                    $group[$value["kfgroup"]]["fen_time"] += $value["all_time"];
            }
            //分单率 新单分单量/新单呼通量
            $group[$value["kfgroup"]]["fenRate"] = setInfNanToN(round( ($group[$value["kfgroup"]]["fen_count"]/$group[$value["kfgroup"]]["overCount"])*100,4));
            //赠单量
            if ($value["on"] == 4 && $value["type_fw"] == 2) {
                    $group[$value["kfgroup"]]["zen_count"] ++;
            }
            //赠单率 新单赠单量/新单呼通量
            $group[$value["kfgroup"]]["zenRate"] = setInfNanToN(round( ($group[$value["kfgroup"]]["zen_count"]/$group[$value["kfgroup"]]["overCount"])*100,4));
            //新单呼通拒绝率：1-（新单呼通分单率+新单呼通赠单率）
            $group[$value["kfgroup"]]["rejectRate"] = 100 - ($group[$value["kfgroup"]]["fenRate"]+$group[$value["kfgroup"]]["zenRate"]);
            //新单通话总时长
            $group[$value["kfgroup"]]["all_time"] += $value["all_time"];
            //通话平均时长
            $group[$value["kfgroup"]]["avg_time"] = setInfNanToN(round($group[$value["kfgroup"]]["all_time"]/$group[$value["kfgroup"]]["count"],2));
            //平均分单 时长
            $group[$value["kfgroup"]]["fen_avg_time"] = setInfNanToN(round($group[$value["kfgroup"]]["fen_time"]/$group[$value["kfgroup"]]["fen_count"],2));



            /**
            * 按师统计
            */
            $value["kfmanager"] = str_replace(",", "", $value["kfmanager"]);
            if (!array_key_exists($value["kfgroup"],$manager[$value["kfmanager"]]["groupcount"])) {
                $manager[$value["kfmanager"]]["groupcount"][$value["kfgroup"]] = $value["kfgroup"];
                $manager[$value["kfmanager"]]["gcount"] ++;
            }
            $manager[$value["kfmanager"]]["ordercount"] ++;
            $manager[$value["kfmanager"]]["manager"] = $value["manager"];

            $manager[$value["kfmanager"]]["count"] += $value["count"];

            //呼通量
            if ( $value["overcount"] > 0) {
                $manager[$value["kfmanager"]]["overCount"] ++;
            }

            //呼通率 新单呼通量/新单呼叫量
            $manager[$value["kfmanager"]]["telRate"] = setInfNanToN(round(($manager[$value["kfmanager"]]["overCount"]/$manager[$value["kfmanager"]]["ordercount"])*100,4));
            //分单量，分单时长
            if ($value["on"] == 4 && $value["type_fw"] == 1) {
                $manager[$value["kfmanager"]]["fen_count"] ++;
                $manager[$value["kfmanager"]]["fen_time"] += $value["all_time"];
            }
            //分单率 新单分单量/新单呼通量
            $manager[$value["kfmanager"]]["fenRate"] = setInfNanToN(round( ($manager[$value["kfmanager"]]["fen_count"]/$manager[$value["kfmanager"]]["overCount"])*100,4));
            //赠单量
            if ($value["on"] == 4 && $value["type_fw"] == 2) {
                    $manager[$value["kfmanager"]]["zen_count"] ++;
            }
            //赠单率 新单赠单量/新单呼通量
            $manager[$value["kfmanager"]]["zenRate"] = setInfNanToN(round( ($manager[$value["kfmanager"]]["zen_count"]/$manager[$value["kfmanager"]]["overCount"])*100,4));
            //新单呼通拒绝率：1-（新单呼通分单率+新单呼通赠单率）
            $manager[$value["kfmanager"]]["rejectRate"] = 100 - ($manager[$value["kfmanager"]]["fenRate"]+$manager[$value["kfmanager"]]["zenRate"]);
            //新单通话总时长
            $manager[$value["kfmanager"]]["all_time"] += $value["all_time"];
            //通话平均时长
            $manager[$value["kfmanager"]]["avg_time"] = setInfNanToN(round($manager[$value["kfmanager"]]["all_time"]/$manager[$value["kfmanager"]]["count"],2));
            //平均分单 时长
            $manager[$value["kfmanager"]]["fen_avg_time"] = setInfNanToN(round($manager[$value["kfmanager"]]["fen_time"]/$manager[$value["kfmanager"]]["fen_count"],2));

            // 新单呼通有效单率=（新单呼通分单量+新单呼通赠单量）/新单呼通量
            $manager[$value["kfmanager"]]["rate"] = setInfNanToN(round(($manager[$value["kfmanager"]]["fen_count"]+$manager[$value["kfmanager"]]["zen_count"])/$manager[$value["kfmanager"]]["overCount"],4))*100;

            /**
             * 按师汇总
             */
            $managerAll["ordercount"] ++;
            $managerAll["count"] +=  $value["count"];
            //呼通量
            if ( $value["overcount"] > 0) {
                $managerAll["overCount"] ++;
            }
            //呼通率 新单呼通量/新单量
            $managerAll["telRate"] = setInfNanToN(round(($managerAll["overCount"]/$managerAll["ordercount"])*100,4));
            //分单量，分单时长
            if ($value["on"] == 4 && $value["type_fw"] == 1) {
                $managerAll["fen_count"] ++;
                $managerAll["fen_time"] += $value["all_time"];
            }
            //分单率 新单分单量/新单呼通量
            $managerAll["fenRate"] = setInfNanToN(round( ($managerAll["fen_count"]/$managerAll["overCount"])*100,4));
            //赠单量
            if ($value["on"] == 4 && $value["type_fw"] == 2) {
                $managerAll["zen_count"] ++;
            }
            //赠单率 新单赠单量/新单呼通量
            $managerAll["zenRate"] = setInfNanToN(round( ($managerAll["zen_count"]/$managerAll["overCount"])*100,4));
            //新单呼通拒绝率：1-（新单呼通分单率+新单呼通赠单率）
            $managerAll["rejectRate"] = 100 - ($managerAll["fenRate"]+$managerAll["zenRate"]);
            //新单通话总时长
            $managerAll["all_time"] += $value["all_time"];
            //通话平均时长
            $managerAll["avg_time"] = setInfNanToN(round($managerAll["all_time"]/$managerAll["count"],2));
            //平均分单 时长
            $managerAll["fen_avg_time"] = setInfNanToN(round($managerAll["fen_time"]/$managerAll["fen_count"],2));



            /**
             * 按城市统计
             */
            $city[$value["cs"]]["cname"] = $value["cname"];
            $city[$value["cs"]]["count"] += $value["count"];

            //呼通量
            if ( $value["overcount"] > 0) {
                $city[$value["cs"]]["overCount"] ++;
            }

            //呼通率 新单呼通量/新单呼叫量
            $city[$value["cs"]]["telRate"] = setInfNanToN(round(($city[$value["cs"]]["overCount"]/$city[$value["cs"]]["count"])*100,4));
            //分单量，分单时长
            if ($value["on"] == 4 && $value["type_fw"] == 1) {
                    $city[$value["cs"]]["fen_count"] ++;
                    $city[$value["cs"]]["fen_time"] += $value["all_time"];
            }
            //分单率 新单分单量/新单呼通量
            $city[$value["cs"]]["fenRate"] = setInfNanToN(round( ($city[$value["cs"]]["fen_count"]/$city[$value["cs"]]["overCount"])*100,4));
            //赠单量
            if ($value["on"] == 4 && $value["type_fw"] == 2) {
                $city[$value["cs"]]["zen_count"] ++;
            }
            //赠单率 新单赠单量/新单呼通量
            $city[$value["cs"]]["zenRate"] = setInfNanToN(round( ($city[$value["cs"]]["zen_count"]/$city[$value["cs"]]["overCount"])*100,4));
            //新单呼通拒绝率：1-（新单呼通分单率+新单呼通赠单率）
            $city[$value["cs"]]["rejectRate"] = 100 - ($city[$value["cs"]]["fenRate"]+$city[$value["cs"]]["zenRate"]);
            //新单通话总时长
            $city[$value["cs"]]["all_time"] += $value["all_time"];
            //通话平均时长
            $city[$value["cs"]]["avg_time"] = setInfNanToN(round($city[$value["cs"]]["all_time"]/$city[$value["cs"]]["count"],2));
            //平均分单 时长
            $city[$value["cs"]]["fen_avg_time"] = setInfNanToN(round($city[$value["cs"]]["fen_time"]/$city[$value["cs"]]["fen_count"],2));
            // 新单呼通有效单率=（新单呼通分单量+新单呼通赠单量）/新单呼通量
            $city[$value["cs"]]["rate"] = setInfNanToN(round(($city[$value["cs"]]["fen_count"]+$city[$value["cs"]]["zen_count"])/$city[$value["cs"]]["overCount"],4))*100;
        }

        return array(
            "user" => $user,
            "group" => $group,
            "manager" => $manager,
            "city" => $city,
            "all"  =>  $managerAll
        );
    }

    /**
     *  对接客服行为分析
     * @param  [type] $id    [对接客服ID]
     * @param  [type] $group [客服组]
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @param  [type] $end   [是否用于统计]
     * @return [type]        [description]
     */
    private function getCustomerOrderDockingStat($id,$city,$begin,$end)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end)+86400-1;
        }

        $result = D("Orders")->getCustomerOrderDockingStat($monthStart,$monthEnd,$id,$city);

        if (count($result) > 0) {
            foreach ($result as $key => $value) {
                //单次对接统计
                $users[$value["id"]]["name"] = $value["name"];
                $users[$value["id"]]["kfgroup"] = $value["kfgroup"];
                $users[$value["id"]]["manager"] = $value["manager"];
                if (!empty($value["orderid"])) {
                    $users[$value["id"]]["count"] ++;
                    $userAll["once"]["count"] ++;
                    $userAll["back"]["count"] ++;
                    if ($value["back_mark"] == 0) {
                        //单次成功次数
                        $users[$value["id"]]["once_array"]["count"] ++;
                        $userAll["once"]["once_count"] ++;
                        //单次对接成功率
                        $users[$value["id"]]["once_array"]["once_rate"] = setInfNanToN(round($users[$value["id"]]["once_array"]["count"]/$users[$value["id"]]["count"],2))*100;
                        $userAll["once"]["once_rate"] = setInfNanToN(round($userAll["once"]["once_count"]/$userAll["once"]["count"],2))*100;

                        //单次对接成功分单量
                        if ($value["type_fw"] == 1) {
                            $users[$value["id"]]["once_array"]["fen"] ++;
                            $users[$value["id"]]["once_array"]["fen_time"] += $value["offset_time"];
                            $userAll["once"]["fen"] ++;
                            $userAll["once"]["fen_time"] += $value["offset_time"];
                        }

                        //单次对接成功赠单量
                        if ($value["type_fw"] == 2) {
                            $users[$value["id"]]["once_array"]["zen"] ++;
                            $users[$value["id"]]["once_array"]["zen_time"] += $value["offset_time"];
                            $userAll["once"]["zen"] ++;
                            $userAll["once"]["zen_time"] += $value["offset_time"];
                        }

                        //总分配时长
                        $users[$value["id"]]["once_array"]["offset_time"] += $value["offset_time"];
                        $userAll["once"]["offset_time"] += $value["offset_time"];

                        //单次成功平均分配时长
                        $users[$value["id"]]["once_array"]["avg_time"] = setInfNanToN(round($users[$value["id"]]["once_array"]["offset_time"]/$users[$value["id"]]["once_array"]["count"],2));
                        $userAll["once"]["avg_time"] = setInfNanToN(round($userAll["once"]["offset_time"]/ $userAll["once"]["once_count"],2));


                        //平均分单分配时长
                        $users[$value["id"]]["once_array"]["avg_fen_time"] = setInfNanToN(round($users[$value["id"]]["once_array"]["fen_time"]/$users[$value["id"]]["once_array"]["fen"],2));
                        $userAll["once"]["avg_fen_time"] = setInfNanToN(round($userAll["once"]["fen_time"]/$userAll["once"]["fen"],2));


                        //平均赠单分配时长
                        $users[$value["id"]]["once_array"]["avg_zen_time"] = setInfNanToN(round($users[$value["id"]]["once_array"]["zen_time"]/$users[$value["id"]]["once_array"]["zen"],2));
                        $userAll["once"]["avg_zen_time"] = setInfNanToN(round($userAll["once"]["zen_time"]/$userAll["once"]["zen"],2));

                    } else {
                        //撤回订单数
                        $users[$value["id"]]["back_array"]["count"] ++;
                        $userAll["back"]["back_count"] ++;
                        //撤回单率
                        $users[$value["id"]]["back_array"]["back_rate"]  = setInfNanToN(round( $users[$value["id"]]["back_array"]["count"]/$users[$value["id"]]["count"],2))*100 ;
                        $userAll["back"]["back_rate"] = setInfNanToN(round($userAll["back"]["back_count"]/$userAll["back"]["count"],2))*100;

                        if ($value["type_fw"] == 1) {
                            //撤回单分单量
                            $users[$value["id"]]["back_array"]["fen"] ++;
                            $userAll["back"]["fen"] ++;
                        }

                        if ($value["type_fw"] == 2) {
                            //撤回单赠单量
                            $users[$value["id"]]["back_array"]["zen"] ++;
                            $userAll["back"]["zen"] ++;
                        }

                    }
                }


                //按城市统计
                $citys[$value["cs"]]["cname"] = $value["cname"];
                //已分配的订单
                $citys[$value["cs"]]["count"] ++;
                $cityAll["count"] ++;
                //全部对接时长
                $citys[$value["cs"]]["offset_time"] += $value["offset_time"];
                $cityAll["offset_time"] += $value["offset_time"];
                //平均对接时长
                $citys[$value["cs"]]["avg_time"] =  setInfNanToN(round($citys[$value["cs"]]["offset_time"]/$citys[$value["cs"]]["count"],0));
                $cityAll["avg_time"] = setInfNanToN(round($cityAll["offset_time"]/$cityAll["count"],0));

                //一次对接成功的订单
                if ($value["once_count"] == 1) {
                    $citys[$value["cs"]]["once_count"] ++;
                    $cityAll["once_count"] ++;
                }
                //一次对接成功率
                $citys[$value["cs"]]["once_rate"] = setInfNanToN(round(($citys[$value["cs"]]["once_count"]/$citys[$value["cs"]]["count"])*100,4));
                $cityAll["once_rate"] = setInfNanToN(round(($cityAll["once_count"]/$cityAll["count"])*100,4));

                //分单时长
                if ($value["on"] == 4 && $value["type_fw"] == 1) {
                    $citys[$value["cs"]]["fen_time"] += $value["offset_time"];
                    $citys[$value["cs"]]["fen_count"] ++;
                    $cityAll["fen_time"] += $value["offset_time"];
                    $cityAll["fen_count"] ++;
                }

                //赠单时长
                if ($value["on"] == 4 && $value["type_fw"] == 2) {
                    $citys[$value["cs"]]["zen_time"] += $value["offset_time"];
                    $citys[$value["cs"]]["zen_count"] ++;
                    $cityAll["zen_time"] += $value["offset_time"];
                    $cityAll["zen_count"] ++;
                }

                //平均分单时长
                $citys[$value["cs"]]["avg_fen_time"] = setInfNanToN(round($citys[$value["cs"]]["fen_time"]/$citys[$value["cs"]]["fen_count"],2));
                $cityAll["avg_fen_time"] = setInfNanToN(round($cityAll["fen_time"]/$cityAll["fen_count"],0));

                //平均赠单时长
                $citys[$value["cs"]]["avg_zen_time"] = setInfNanToN(round($citys[$value["cs"]]["zen_time"]/$citys[$value["cs"]]["zen_count"],2));
                $cityAll["avg_zen_time"] = setInfNanToN(round($cityAll["zen_time"]/$cityAll["zen_count"],0));

                //已分配订单分赠比
                $citys[$value["cs"]]["fen_zen_percent"] = setInfNanToN(round($citys[$value["cs"]]["fen_count"]/$citys[$value["cs"]]["zen_count"],4))*100;
                $cityAll["fen_zen_percent"] = setInfNanToN(round($cityAll["fen_count"]/$cityAll["zen_count"],4))*100;

                $citys[$value["cs"]]["no_fen_count"] ++;
                $cityAll["no_fen_count"] ++;

                //退回的订单
                if ($value["back_mark"] == 1) {
                    $citys[$value["cs"]]["back_count"] ++;
                    $cityAll["back_count"] ++;
                }
           }
        }

        return array("users"=>$users,"citys"=>$citys,"userAll" => $userAll,"cityAll" => $cityAll);
    }

    /**
     * 客服对接统计
     * @param  [int] $kf    [客服ID]
     * @param  [string] $city  [城市ID]
     * @param  [string] $begin [开始时间]
     * @param  [string] $end   [结束时间]
     * @return array
     */
    public function getCustomerDockingStat($kf, $city, $begin, $end)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end)+86400-1;
        }
        //按客服统计
        $users = D("Orders")->getCustomerDockingStat($kf, $monthStart, $monthEnd);

        foreach ($users as $key => $value) {
            $users[$key]["fen_zen_percent"] = setInfNanToN(round($value["fen_count"]/$value["zen_count"],4))*100;
            $allUser["count"] += $value["count"];
            $allUser["fen_count"] += $value["fen_count"];
            $allUser["zen_count"] += $value["zen_count"];
            $allUser["fen_zen_percent"] = setInfNanToN(round($allUser["fen_count"]/$allUser["zen_count"],4))*100;
            $allUser["no_fen_count"] += $value["no_fen_count"];
        }
        //按城市统计
        $city = D("Orders")->getCustomerDockingStatByCity($city, $monthStart, $monthEnd);
        foreach ($city as $key => $value) {
            $city[$key]["fen_zen_percent"] = setInfNanToN(round($value["fen_count"]/$value["zen_count"],4))*100;
            $allCity["count"] += $value["count"];
            $allCity["fen_count"] += $value["fen_count"];
            $allCity["zen_count"] += $value["zen_count"];
            $allCity["fen_zen_percent"] = setInfNanToN(round($allCity["fen_count"]/$allCity["zen_count"],4))*100;
            $allCity["no_fen_count"] += $value["no_fen_count"];
        }
        return array("users"=>$users,"city"=>$city,"userAll"=>$allUser,"cityAll"=>$allCity);
    }

    public function fenOrdertRend()
    {
        $list = $this->getOtherPrderRend(I("get.begin"),I("get.end"),1);
        $this->assign("list",$list);
        $this->assign("type_fw",1);
        $this->display("otherordertrend");
    }

    public function zenOrdertRend()
    {
        $list = $this->getOtherPrderRend(I("get.begin"),I("get.end"),2);
        $this->assign("list",$list);
        $this->assign("type_fw",2);
        $this->display("otherordertrend");
    }

    /**
     * 获取发单数据
     * @param  [date] $begin [开始时间]
     * @param  [date] $end   [结束时间]
     * @return array
     */
    private function getSendOrderData($begin,$end)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));

        if (date("d") < date("t")) {
            $monthEnd = mktime(23,59,59,date("m"),date("d"),date("Y"));
        }

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end)+86400-1;
        }

        $result =  D("Orders")->getSendOrderData($monthStart,$monthEnd);

        $count = 0;
        foreach ($result as $key => $value) {
            $count ++;
            $list["child"][$value["date"]] = $value;
            foreach ($value as $k => $val) {
                if ($k != "date") {
                    $data[$k] += $val;
                    $list["child"][$value["date"]]["all"] += $val;
                    $list["all"]["all"] += $val;
                }
            }

            $list["all"]["time1"] += $value["time1"];
            $list["all"]["time2"] += $value["time2"];
            $list["all"]["time3"] += $value["time3"];
            $list["all"]["time4"] += $value["time4"];
            $list["all"]["time5"] += $value["time5"];
            $list["all"]["time6"] += $value["time6"];
            $list["all"]["time7"] += $value["time7"];
            $list["all"]["time8"] += $value["time8"];
            $list["all"]["time9"] += $value["time9"];
            $list["all"]["time10"] += $value["time10"];
            $list["all"]["time11"] += $value["time11"];
            $list["all"]["time12"] += $value["time12"];
            $list["all"]["time13"] += $value["time13"];
            $list["all"]["time14"] += $value["time14"];
            $list["all"]["time15"] += $value["time15"];
            $list["all"]["time16"] += $value["time16"];
            $list["all"]["time17"] += $value["time17"];
            $list["all"]["time18"] += $value["time18"];
            $list["all"]["time19"] += $value["time19"];
            $list["all"]["time20"] += $value["time20"];
            $list["all"]["time21"] += $value["time21"];
            $list["all"]["time22"] += $value["time22"];
            $list["all"]["time23"] += $value["time23"];
            $list["all"]["time24"] += $value["time24"];

            $list["avg"]["time1"] = setInfNanToN(round($list["all"]["time1"]/$count,2));
            $list["avg"]["time2"] = setInfNanToN(round($list["all"]["time2"]/$count,2));
            $list["avg"]["time3"] = setInfNanToN(round($list["all"]["time3"]/$count,2));
            $list["avg"]["time4"] = setInfNanToN(round($list["all"]["time4"]/$count,2));
            $list["avg"]["time5"] = setInfNanToN(round($list["all"]["time5"]/$count,2));
            $list["avg"]["time6"] = setInfNanToN(round($list["all"]["time6"]/$count,2));
            $list["avg"]["time7"] = setInfNanToN(round($list["all"]["time7"]/$count,2));
            $list["avg"]["time8"] = setInfNanToN(round($list["all"]["time8"]/$count,2));
            $list["avg"]["time9"] = setInfNanToN(round($list["all"]["time9"]/$count,2));
            $list["avg"]["time10"] = setInfNanToN(round($list["all"]["time10"]/$count,2));
            $list["avg"]["time11"] = setInfNanToN(round($list["all"]["time11"]/$count,2));
            $list["avg"]["time12"] = setInfNanToN(round($list["all"]["time12"]/$count,2));
            $list["avg"]["time13"] = setInfNanToN(round($list["all"]["time13"]/$count,2));
            $list["avg"]["time14"] = setInfNanToN(round($list["all"]["time14"]/$count,2));
            $list["avg"]["time15"] = setInfNanToN(round($list["all"]["time15"]/$count,2));
            $list["avg"]["time16"] = setInfNanToN(round($list["all"]["time16"]/$count,2));
            $list["avg"]["time17"] = setInfNanToN(round($list["all"]["time17"]/$count,2));
            $list["avg"]["time18"] = setInfNanToN(round($list["all"]["time18"]/$count,2));
            $list["avg"]["time19"] = setInfNanToN(round($list["all"]["time19"]/$count,2));
            $list["avg"]["time20"] = setInfNanToN(round($list["all"]["time20"]/$count,2));
            $list["avg"]["time21"] = setInfNanToN(round($list["all"]["time21"]/$count,2));
            $list["avg"]["time22"] = setInfNanToN(round($list["all"]["time22"]/$count,2));
            $list["avg"]["time23"] = setInfNanToN(round($list["all"]["time23"]/$count,2));
            $list["avg"]["time24"] = setInfNanToN(round($list["all"]["time24"]/$count,2));
            $list["avg"]["all"] = setInfNanToN(round($list["all"]["all"]/$count,2));

            $time["date"][] = $value["date"];
            $time["child"]['00：00-01：00']["child"][] =  $value["time1"];
            $time["child"]['01：00-02：00']["child"][] =  $value["time2"];
            $time["child"]['02：00-03：00']["child"][] =  $value["time3"];
            $time["child"]['03：00-04：00']["child"][] =  $value["time4"];
            $time["child"]['04：00-05：00']["child"][] =  $value["time5"];
            $time["child"]['05：00-06：00']["child"][] =  $value["time6"];
            $time["child"]['06：00-07：00']["child"][] =  $value["time7"];
            $time["child"]['07：00-08：00']["child"][] =  $value["time8"];
            $time["child"]['08：00-09：00']["child"][] =  $value["time9"];
            $time["child"]['09：00-10：00']["child"][] =  $value["time10"];
            $time["child"]['10：00-11：00']["child"][] =  $value["time11"];
            $time["child"]['11：00-12：00']["child"][] =  $value["time12"];
            $time["child"]['12：00-13：00']["child"][] =  $value["time13"];
            $time["child"]['13：00-14：00']["child"][] =  $value["time14"];
            $time["child"]['14：00-15：00']["child"][] =  $value["time15"];
            $time["child"]['15：00-16：00']["child"][] =  $value["time16"];
            $time["child"]['16：00-17：00']["child"][] =  $value["time17"];
            $time["child"]['17：00-18：00']["child"][] =  $value["time18"];
            $time["child"]['18：00-19：00']["child"][] =  $value["time19"];
            $time["child"]['19：00-20：00']["child"][] =  $value["time20"];
            $time["child"]['20：00-21：00']["child"][] =  $value["time21"];
            $time["child"]['21：00-22：00']["child"][] =  $value["time22"];
            $time["child"]['22：00-23：00']["child"][] =  $value["time23"];
            $time["child"]['23：00-24：00']["child"][] =  $value["time24"];
        }

        foreach ($data as $key => $value) {
            $data[$key] =  round($value/$count,2);
        }
        $data = array_values($data);
        $avg  = $list["avg"];
        array_pop($avg);
        $avg = array_values($avg);
        return array("list"=>$list,"data"=>$data,"avg"=>$avg,"time"=>$time,"begin"=>date("Y-m-d",$monthStart),"end"=>date("Y-m-d",$monthEnd));
    }

    /**
     * 赠单原因分析
     * @param  date $begin [开始日期]
     * @param  date $end   [结束日期]
     * @return array
     */
    private function getKfZenStat($begin,$end)
    {
        $monthStart = strtotime("-1 day",mktime(17,30,00,date("m"),1,date("Y")));
        $monthEnd = mktime(17,30,00,date("m"),date("t"),date("Y"));

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime(date("Y-m-d",strtotime("-1 day",strtotime($begin)))." 17:30:00");
            $monthEnd = strtotime($end." 17:30:00");
        }
        $item = array(  '1' => '距离远',
                        '2' => '预算低',
                        '3' => '面积小',
                        '4' => '交房时间长',
                        '5' => '开工时间长',
                        '6' => '城市未开',
                        '7' => '需要垫资',
                        '8' => '不能量房',
                        '9' => '改动项目少',
                        '10' => '与装修相关',
                        '11' => '只要设计',
                        '12' => '意向不强');
        $color = array('#ff6633', '#ff3366', '#336699', '#009933', '#9999cc', '#cc9933', '#99cc99', '#2f2ff5', '#133e21', '#bf1b2a', '#cfea17', '#bd3407');
        $result = D("Orders")->getKfZenStat($monthStart, $monthEnd);

        foreach ($result as $key => $value) {
            $list[$value["kfgroup"]][$value["mark"]]["count"] = $value["count"];
            $list[$value["kfgroup"]]["all"] +=  $value["count"];
            $all['all'] += $value["count"];
            $all[$value["mark"]] += $value["count"];

            $data[$value["kfgroup"]]["child"][] = array(
                                            "name" => $value["remarks"],
                                            "value" => $value["count"]
                                        );
            $data[$value["kfgroup"]]["item"][] = $item[$value["mark"]];
            $data[$value["kfgroup"]]["color"][] = $color[$value["mark"]-1];

            $data["all"]["child"][$value['mark']]["name"] = $value["remarks"];
            $data["all"]["child"][$value['mark']]["value"] += $value["count"];

            if (!in_array($item[$value["mark"]], $data["all"]["item"])) {
                $data["all"]["item"][] = $item[$value["mark"]];
                $data["all"]["color"][] = $color[$value["mark"]-1];
            }
        }
        $data["all"]["child"] = array_values($data["all"]["child"]);
        return array("list" => $list, "all" => $all,"item" => $item,"data" => $data);
    }

    /**
     * 发单量数据分析
     * @param  string $begin [开始时间]
     * @param  string $end   [结束时间]
     * @return array
     */
    private function getOrderTrend($begin,$end)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));

        if (date("d") < date("t")) {
             $monthEnd = mktime(23,59,59,date("m"),date("d"),date("Y"));
        }

        if (!empty($begin) && !empty($end)) {
             $monthStart = strtotime($begin);
             $monthEnd = strtotime($end)+86400-1;
        }
         $result = D("Orders")->getOrderTrend($monthStart,$monthEnd);
        foreach ($result as $key => $value) {
            $list["child"][$value["date"]] = $value;
            foreach ($value as $k => $val) {
                if ($k != "date") {
                     if (strpos($k,"_") !== false) {
                        $list["child"][$value["date"]]["all_fen"] += $val;
                        $list["all"]["all_fen"] += $val;
                     } else {
                        $list["child"][$value["date"]]["all"] += $val;
                        $list["all"]["all"] += $val;
                    }
                }
            }

           $list["child"][$value["date"]]["rate1"] = setInfNanToN(round( $value["time_fen1"]/$value["time1"],3))*100;
           $list["child"][$value["date"]]["rate2"] = setInfNanToN(round( $value["time_fen2"]/$value["time2"],3))*100;
           $list["child"][$value["date"]]["rate3"] = setInfNanToN(round( $value["time_fen3"]/$value["time3"],3))*100;
           $list["child"][$value["date"]]["rate4"] = setInfNanToN(round( $value["time_fen4"]/$value["time4"],3))*100;
           $list["child"][$value["date"]]["rate5"] = setInfNanToN(round( $value["time_fen5"]/$value["time5"],3))*100;
           $list["child"][$value["date"]]["rate6"] = setInfNanToN(round( $value["time_fen6"]/$value["time6"],3))*100;
           $list["child"][$value["date"]]["rate7"] = setInfNanToN(round( $value["time_fen7"]/$value["time7"],3))*100;
           $list["child"][$value["date"]]["rate8"] = setInfNanToN(round( $value["time_fen8"]/$value["time8"],3))*100;
           $list["child"][$value["date"]]["rate9"] = setInfNanToN(round( $value["time_fen9"]/$value["time9"],3))*100;
           $list["child"][$value["date"]]["rate10"] = setInfNanToN(round( $value["time_fen10"]/$value["time10"],3))*100;
           $list["child"][$value["date"]]["rate11"] = setInfNanToN(round( $value["time_fen11"]/$value["time11"],3))*100;
           $list["child"][$value["date"]]["rate12"] = setInfNanToN(round( $value["time_fen12"]/$value["time12"],3))*100;
           $list["child"][$value["date"]]["rate13"] = setInfNanToN(round( $value["time_fen13"]/$value["time13"],3))*100;
           $list["child"][$value["date"]]["rate14"] = setInfNanToN(round( $value["time_fen14"]/$value["time14"],3))*100;
           $list["child"][$value["date"]]["rate15"] = setInfNanToN(round( $value["time_fen15"]/$value["time15"],3))*100;
           $list["child"][$value["date"]]["rate16"] = setInfNanToN(round( $value["time_fen16"]/$value["time16"],3))*100;
           $list["child"][$value["date"]]["rate17"] = setInfNanToN(round( $value["time_fen17"]/$value["time17"],3))*100;
           $list["child"][$value["date"]]["rate18"] = setInfNanToN(round( $value["time_fen18"]/$value["time18"],3))*100;
           $list["child"][$value["date"]]["rate19"] = setInfNanToN(round( $value["time_fen19"]/$value["time19"],3))*100;
           $list["child"][$value["date"]]["rate20"] = setInfNanToN(round( $value["time_fen20"]/$value["time20"],3))*100;
           $list["child"][$value["date"]]["rate21"] = setInfNanToN(round( $value["time_fen21"]/$value["time21"],3))*100;
           $list["child"][$value["date"]]["rate22"] = setInfNanToN(round( $value["time_fen22"]/$value["time22"],3))*100;
           $list["child"][$value["date"]]["rate23"] = setInfNanToN(round( $value["time_fen23"]/$value["time23"],3))*100;
           $list["child"][$value["date"]]["rate24"] = setInfNanToN(round( $value["time_fen24"]/$value["time24"],3))*100;

            $list["child"][$value["date"]]["all_rate"] = setInfNanToN(round( $list["child"][$value["date"]]["all_fen"]/$list["child"][$value["date"]]["all"],3))*100;

            $list["all"]["time_fen1"] += $value["time_fen1"];
            $list["all"]["time_fen2"] += $value["time_fen2"];
            $list["all"]["time_fen3"] += $value["time_fen3"];
            $list["all"]["time_fen4"] += $value["time_fen4"];
            $list["all"]["time_fen5"] += $value["time_fen5"];
            $list["all"]["time_fen6"] += $value["time_fen6"];
            $list["all"]["time_fen7"] += $value["time_fen7"];
            $list["all"]["time_fen8"] += $value["time_fen8"];
            $list["all"]["time_fen9"] += $value["time_fen9"];
            $list["all"]["time_fen10"] += $value["time_fen10"];
            $list["all"]["time_fen11"] += $value["time_fen11"];
            $list["all"]["time_fen12"] += $value["time_fen12"];
            $list["all"]["time_fen13"] += $value["time_fen13"];
            $list["all"]["time_fen14"] += $value["time_fen14"];
            $list["all"]["time_fen15"] += $value["time_fen15"];
            $list["all"]["time_fen16"] += $value["time_fen16"];
            $list["all"]["time_fen17"] += $value["time_fen17"];
            $list["all"]["time_fen18"] += $value["time_fen18"];
            $list["all"]["time_fen19"] += $value["time_fen19"];
            $list["all"]["time_fen20"] += $value["time_fen20"];
            $list["all"]["time_fen21"] += $value["time_fen21"];
            $list["all"]["time_fen22"] += $value["time_fen22"];
            $list["all"]["time_fen23"] += $value["time_fen23"];
            $list["all"]["time_fen24"] += $value["time_fen24"];

            $list["all"]["time1"] += $value["time1"];
            $list["all"]["time2"] += $value["time2"];
            $list["all"]["time3"] += $value["time3"];
            $list["all"]["time4"] += $value["time4"];
            $list["all"]["time5"] += $value["time5"];
            $list["all"]["time6"] += $value["time6"];
            $list["all"]["time7"] += $value["time7"];
            $list["all"]["time8"] += $value["time8"];
            $list["all"]["time9"] += $value["time9"];
            $list["all"]["time10"] += $value["time10"];
            $list["all"]["time11"] += $value["time11"];
            $list["all"]["time12"] += $value["time12"];
            $list["all"]["time13"] += $value["time13"];
            $list["all"]["time14"] += $value["time14"];
            $list["all"]["time15"] += $value["time15"];
            $list["all"]["time16"] += $value["time16"];
            $list["all"]["time17"] += $value["time17"];
            $list["all"]["time18"] += $value["time18"];
            $list["all"]["time19"] += $value["time19"];
            $list["all"]["time20"] += $value["time20"];
            $list["all"]["time21"] += $value["time21"];
            $list["all"]["time22"] += $value["time22"];
            $list["all"]["time23"] += $value["time23"];
            $list["all"]["time24"] += $value["time24"];


            $list["all"]["rate1"] = setInfNanToN(round( $list["all"]["time_fen1"]/$list["all"]["time1"],3))*100;
            $list["all"]["rate2"] = setInfNanToN(round( $list["all"]["time_fen2"]/$list["all"]["time2"],3))*100;
            $list["all"]["rate3"] = setInfNanToN(round( $list["all"]["time_fen3"]/$list["all"]["time3"],3))*100;
            $list["all"]["rate4"] = setInfNanToN(round( $list["all"]["time_fen4"]/$list["all"]["time4"],3))*100;
            $list["all"]["rate5"] = setInfNanToN(round( $list["all"]["time_fen5"]/$list["all"]["time5"],3))*100;
            $list["all"]["rate6"] = setInfNanToN(round( $list["all"]["time_fen6"]/$list["all"]["time6"],3))*100;
            $list["all"]["rate7"] = setInfNanToN(round( $list["all"]["time_fen7"]/$list["all"]["time7"],3))*100;
            $list["all"]["rate8"] = setInfNanToN(round( $list["all"]["time_fen8"]/$list["all"]["time8"],3))*100;
            $list["all"]["rate9"] = setInfNanToN(round( $list["all"]["time_fen9"]/$list["all"]["time9"],3))*100;
            $list["all"]["rate10"] = setInfNanToN(round( $list["all"]["time_fen10"]/$list["all"]["time10"],3))*100;
            $list["all"]["rate11"] = setInfNanToN(round( $list["all"]["time_fen11"]/$list["all"]["time11"],3))*100;
            $list["all"]["rate12"] = setInfNanToN(round( $list["all"]["time_fen12"]/$list["all"]["time12"],3))*100;
            $list["all"]["rate13"] = setInfNanToN(round( $list["all"]["time_fen13"]/$list["all"]["time13"],3))*100;
            $list["all"]["rate14"] = setInfNanToN(round( $list["all"]["time_fen14"]/$list["all"]["time14"],3))*100;
            $list["all"]["rate15"] = setInfNanToN(round( $list["all"]["time_fen15"]/$list["all"]["time15"],3))*100;
            $list["all"]["rate16"] = setInfNanToN(round( $list["all"]["time_fen16"]/$list["all"]["time16"],3))*100;
            $list["all"]["rate17"] = setInfNanToN(round( $list["all"]["time_fen17"]/$list["all"]["time17"],3))*100;
            $list["all"]["rate18"] = setInfNanToN(round( $list["all"]["time_fen18"]/$list["all"]["time18"],3))*100;
            $list["all"]["rate19"] = setInfNanToN(round( $list["all"]["time_fen19"]/$list["all"]["time19"],3))*100;
            $list["all"]["rate20"] = setInfNanToN(round( $list["all"]["time_fen20"]/$list["all"]["time20"],3))*100;
            $list["all"]["rate21"] = setInfNanToN(round( $list["all"]["time_fen21"]/$list["all"]["time21"],3))*100;
            $list["all"]["rate22"] = setInfNanToN(round( $list["all"]["time_fen22"]/$list["all"]["time22"],3))*100;
            $list["all"]["rate23"] = setInfNanToN(round( $list["all"]["time_fen23"]/$list["all"]["time23"],3))*100;
            $list["all"]["rate24"] = setInfNanToN(round( $list["all"]["time_fen24"]/$list["all"]["time24"],3))*100;
            $list["all"]["rate_all"] = setInfNanToN(round( $list["all"]["all_fen"]/$list["all"]["all"],3))*100;
        }

        return array("list"=>$list);
    }

    /**
     * 每日订单统计
     */
    public function everyDayOrdersStat()
    {
        //默认一个月时间
        $starttime   = strtotime(date('Y-m-d', (time() - 3600 * 24 * 31))); //一个月前
        $endtime     = strtotime(date('Y-m-d') . ' 23:59:59'); //到今天结束
        //处理查询
        $getlist = array();
        if (!empty($_GET['cs'])) {
            $getlist['cs'] = $_GET['cs'];
            $sqlcs = 'AND cs=' . +$getlist['cs'].' ';
            if ('000001' == $getlist['cs']) { //请选择是000001就给所有城市
                unset($sqlcs);
            }
        }
        if (!empty($_GET['datefrom']) && !empty($_GET['dateto'])) {
            $starttime = strtotime($_GET['datefrom']);
            $endtime   = strtotime($_GET['dateto'] . ' 23:59:59');
        }
        $getlist['starttime'] = date('Y-m-d', $starttime);
        $getlist['endtime']   = date('Y-m-d', $endtime);
        $this->assign('getlist', $getlist);
        //查询语句中未审核暂时不删除
        $sqltmp = "
            SELECT
                SUBSTRING(FROM_UNIXTIME(o.time_real), 1, 10) AS '日期',
              SUM(CASE o.type_fw WHEN 1 THEN 1 ELSE 0 END) AS '分',
              SUM(CASE o.type_fw WHEN 2 THEN 1 ELSE 0 END) AS '问',
              SUM(CASE o.type_fw WHEN 3 THEN 1 ELSE 0 END) AS '分没人跟',
              SUM(CASE o.type_fw WHEN 4 THEN 1 ELSE 0 END) AS '问没人跟',
                SUM(CASE o.`on` WHEN 4 THEN 1 ELSE 0 END) AS '总的有效',
              SUM(CASE o.`on` WHEN 2 THEN 1 ELSE 0 END) AS '待定',
                SUM(CASE o.`on` WHEN 0 THEN 1 ELSE 0 END) AS '未审核',
                SUM(
                  IF( o.`on` = 0 AND o.on_sub = 10, 1, 0)
                ) AS '新单',
              SUM(
                  IF( o.`on` = 0 AND o.on_sub = 9, 1, 0)
                ) AS '次新单',
                SUM(
                  IF( o.`on` = 0 AND o.on_sub = 8, 1, 0)
                ) AS '扫单',
              SUM(CASE o.`on` WHEN 5 THEN 1 ELSE 0 END) AS '无效',
                SUM(CASE o.`on` WHEN 6 THEN 1 ELSE 0 END) AS '空号',
              SUM(CASE o.`on` WHEN 7 THEN 1 ELSE 0 END) AS '装修公司',
                SUM(CASE o.`on` WHEN 8 THEN 1 ELSE 0 END) AS '无效咨询',
                COUNT(o.id) as '总数'
            FROM
                qz_orders AS o
            WHERE
                time_real BETWEEN  %s AND %s " . $sqlcs .
            "GROUP BY
                SUBSTRING(
                    FROM_UNIXTIME(o.time_real),
                    1,
                    10
                )
            ORDER BY
                SUBSTRING(
                    FROM_UNIXTIME(o.time_real),
                    1,
                    10
                ) DESC
            ";
        $sql = sprintf($sqltmp, $starttime, $endtime);
        $result = M()->query($sql);

        $novip_order_count =  D('Quyu')->getNoVipOrderCount($starttime, $endtime);

        foreach ($novip_order_count as $key => $value) {
            $no_vip_count_array[$value['timeday']] = $value['ordercount'];
        }

        foreach ($result as $key => &$value) {
            if (array_key_exists($value['日期'],$no_vip_count_array)) {
                $value['无会员订单'] = $no_vip_count_array[$value['日期']];
            } else {
                $value['无会员订单'] = 0;
            }
            $value['有会员加总站订单'] = $value['总数'] - $value['无会员订单'];
            $value['分单率'] =  round(($value['分'] / $value['有会员加总站订单'])*100, 2);
            $allfenwen          = $value['分'] + $value['问'];
            $value['分问单率'] =  round(($allfenwen / $value['有会员加总站订单'])*100, 2);
        }

        import('Library.Org.Util.App');
        $app = new \app();
        $avg = $app->fieldavg($result); //计算多行记录的平均值,返回平均值数组
        //重新计算下 分单率 合计当中的 分单率是需要重新计算的
        $avg['分单率']   =  round(($avg['分'] / $avg['有会员加总站订单'])*100, 2);
        $allfenewnavg    = $avg['分'] + $avg['问'];
        $avg['分问单率'] =  round(($allfenewnavg / $avg['有会员加总站订单'])*100, 2);
        foreach ($avg as $key => &$value) {
            $value = setInfNanToN(round($value, 2));
        }
        $first = $result[0];
        $end   = end($result);
        $avg['日期'] = ceil((strtotime($first['日期']) - strtotime($end['日期']))/86400 + 1) . '天' ;
        array_push($result, $avg);

        $this->assign('list',$result);
        $this->display();
    }

    /**
     * 客服呼叫量统计
     * @param  [type] $id    [客服ID]
     * @param  [type] $group [客服组]
     * @param  [type] $begin [开始时间]
     * @param  [type] $end   [结束时间]
     * @return [type]        [description]
     */
    private function getTelStat($id, $group, $begin, $end, $manager)
    {
        $monthStart = date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")));
        $monthEnd = date("Y-m-d",mktime(0,0,0,date("m"),date("t"),date("Y"))+86400);
        if (!empty($begin) && !empty($end)) {
            $monthStart = $begin;
            $monthEnd = $end;
        }

        $list = [];
        $userids = "";

        $ordersModel = new OrdersModel();
        $result = $ordersModel->getTelStat($id, $group, $monthStart, $monthEnd, $manager, $userids);

        if (count($result) > 0) {
            foreach ($result as $key => $value) {
                $value["avg_time"] = timediff($value["sum_time"]/$value["tel_count"]);
                $value["all_time"] = $value["sum_time"];
                $value["sum_time"] = timediff($value["sum_time"]);
                $list[$value["id"]] = $value;
            }
        }

        // 获取合力的通话统计
        $monthStart = strtotime($monthStart);
        $monthEnd = strtotime($monthEnd);
        $result = $ordersModel->getHollyTelStat($id, $group, $monthStart, $monthEnd, $manager, $userids);

        foreach ($result as  $key => $value) {
            $list[$value["id"]]["name"] = $value["name"];
            $list[$value["id"]]["count"] += $value["count"];
            $list[$value["id"]]["tel_count"] += $value["tel_count"];
            $list[$value["id"]]["un_tel_count"] += $value["count"] - $value["tel_count"];
            $list[$value["id"]]["all_time"] += $value["sum_time"];
            $list[$value["id"]]["sum_time"] = empty($list[$value["id"]]["all_time"])?"0秒":timediff($list[$value["id"]]["all_time"]);
            $list[$value["id"]]["avg_time"] = empty($list[$value["id"]]["all_time"])?"0秒":timediff($list[$value["id"]]["all_time"]/$list[$value["id"]]["tel_count"]);
        }

        // 回访客服通话统计
        $result = $ordersModel->getHollyReviewTelStat($id, $group, $monthStart, $monthEnd, $manager, $userids);

        foreach ($result as  $key => $value) {
            $list[$value["id"]]["name"] = $value["name"];
            $list[$value["id"]]["count"] += $value["count"];
            $list[$value["id"]]["tel_count"] += $value["tel_count"];
            $list[$value["id"]]["un_tel_count"] += $value["count"] - $value["tel_count"];
            $list[$value["id"]]["all_time"] += $value["sum_time"];
            $list[$value["id"]]["sum_time"] = empty($list[$value["id"]]["all_time"])?"0秒":timediff($list[$value["id"]]["all_time"]);
            $list[$value["id"]]["avg_time"] = empty($list[$value["id"]]["all_time"])?"0秒":timediff($list[$value["id"]]["all_time"]/$list[$value["id"]]["tel_count"]);
        }

        // 天润电话通话统计
        $clinkTelcenterLogic = new ClinkTelcenterLogicModel();
        $result = $clinkTelcenterLogic->getClinkOrderReviewTelStat($id, $group, $monthStart, $monthEnd, $manager, $userids);

        foreach ($result as $key => $value) {
            $list[$value["id"]]["name"] = $value["name"];
            $list[$value["id"]]["count"] += $value["count"];
            $list[$value["id"]]["tel_count"] += $value["tel_count"];
            $list[$value["id"]]["un_tel_count"] += $value["count"] - $value["tel_count"];
            $list[$value["id"]]["all_time"] += $value["sum_time"];
            $list[$value["id"]]["sum_time"] = empty($list[$value["id"]]["all_time"])?"0秒":timediff($list[$value["id"]]["all_time"]);
            $list[$value["id"]]["avg_time"] = empty($list[$value["id"]]["all_time"])?"0秒":timediff($list[$value["id"]]["all_time"]/$list[$value["id"]]["tel_count"]);
        }

        $info = ["name" => "合计"];
        foreach ($list as $key => $item) {
            if ($item["count"] <= 0) {
                unset($list[$key]);
                continue;
            }

            $list[$key]["tel_rate"] = "0%";
            if ($list[$key]["count"] > 0) {
                $rate = $list[$key]["tel_count"] / $list[$key]["count"];
                $list[$key]["tel_rate"] = sprintf("%.2f", $rate * 100) . "%";
            }

            // 计算统计信息
            $info["count"] += $item["count"];
            $info["tel_count"] += $item["tel_count"];
            $info["un_tel_count"] += $item["un_tel_count"];
            $info["all_time"] += $item["all_time"];

            $info["sum_time"] = timediff($info["all_time"]);
            $info["avg_time"] = "0秒";
            if ($info["tel_count"] > 0) {
                $info["avg_time"] = timediff($info["all_time"] / $info["tel_count"]);
            }

            $info["tel_rate"] = "0%";
            if ($info["count"] > 0) {
                $rate = $info["tel_count"] / $info["count"];
                $info["tel_rate"] = sprintf("%.2f", $rate * 100) . "%";
            }
        }

        if (count($list) > 0) {
            // 把统计信息插入数组第一项
            array_unshift($list, $info);
        }

        return array_values($list);
    }

    private function getOtherPrderRend($begin,$end,$typw_fw)
    {
        $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
        $monthEnd = mktime(23,59,59,date("m"),date("t"),date("Y"));

        if (date("d") < date("t")) {
            $monthEnd = mktime(23,59,59,date("m"),date("d"),date("Y"));
        }

        if (!empty($begin) && !empty($end)) {
            $monthStart = strtotime($begin);
            $monthEnd = strtotime($end)+86400-1;
        }

        $result = D("Orders")->getOtherOrderTrend($monthStart,$monthEnd,$typw_fw);

        foreach ($result as $key => $value) {
            $list["child"][$value["date"]] = $value;
            foreach ($value as $k => $val) {
                if ($k != "date") {
                    $list["child"][$value["date"]]["all"] += $val;
                    $list["all"]["all"] += $val;
                }
            }

            $list["all"]["time1"] += $value["time1"];
            $list["all"]["time2"] += $value["time2"];
            $list["all"]["time3"] += $value["time3"];
            $list["all"]["time4"] += $value["time4"];
            $list["all"]["time5"] += $value["time5"];
            $list["all"]["time6"] += $value["time6"];
            $list["all"]["time7"] += $value["time7"];
            $list["all"]["time8"] += $value["time8"];
            $list["all"]["time9"] += $value["time9"];
            $list["all"]["time10"] += $value["time10"];
            $list["all"]["time11"] += $value["time11"];
            $list["all"]["time12"] += $value["time12"];
            $list["all"]["time13"] += $value["time13"];
            $list["all"]["time14"] += $value["time14"];
            $list["all"]["time15"] += $value["time15"];
            $list["all"]["time16"] += $value["time16"];
            $list["all"]["time17"] += $value["time17"];
            $list["all"]["time18"] += $value["time18"];
            $list["all"]["time19"] += $value["time19"];
            $list["all"]["time20"] += $value["time20"];
            $list["all"]["time21"] += $value["time21"];
            $list["all"]["time22"] += $value["time22"];
            $list["all"]["time23"] += $value["time23"];
            $list["all"]["time24"] += $value["time24"];
        }

        return $list;
    }

    private function getEstimatedOrderInfo()
    {
        $start_time = strtotime("-1 day",mktime(17,30,00,date("m"),date("d"),date("Y")));
        $end_time = strtotime("+1 hour",strtotime(date("Y-m-d H:00:00",time())));
        //获取当前客服数量
        $kf_num = D("options")->getOptionNoCache("kf_num")["option_value"];
        $new_kf_num = D("options")->getOptionNoCache("new_kf_num")["option_value"];
        $info['kfCount'] = $kf_num + $new_kf_num;
        //获取客服新人数量
        $info['kfNewCount'] = $new_kf_num;


        //今日已有新单=上一日17：30以后+当前时间段已有发单
        $info['orderCount'] = D("Orders")->getNowOrderCount($start_time,$end_time);
        $hour = date("H");

        //获取预警值
        $day = date("Y-m-d",time());
        $result = D("OrdersExceptionStatistics")->getExceptionStatisticsByDay($day);
        foreach ($result as $key => $value) {
            if ($value["time"] >= $hour && $value["time"] < "17") {
                $allCount += $value["forecast_order"];
            }

            if ($value["time"] == "17") {
                $allCount += $value["forecast_order"]/2;
            }
        }

        //预计全天新单=今日已有新单+当天剩余时段至17：30发单量估值
        $info["estimatedCount"] = setInfNanToN(round($info['orderCount'] + $allCount));

        //新单冒出速度=前2个小时段发单量的均值
        //获取前2个时段的发单量
        $end_time = strtotime(date("Y-m-d H:00:00",time()));
        $start_time = strtotime("-2 hour",$end_time);
        $threeCount = D("Orders")->getNowOrderCount($start_time,$end_time);

        $info["orderSpeed"] = setInfNanToN(round($threeCount/2));

        //预计全天人均新单=预计全天新单/今日坐席数
        $info["estimatedAvgCount"] = setInfNanToN(round($info["estimatedCount"]/$info["kfCount"],1));

        //目前人均新单=今日已有新单/今日坐席数
        $info['avgCount'] = setInfNanToN(round($info['orderCount']/$info['kfCount'],1));

        //获取设置项
        $result = D("Options")->getOptionByGroup("ORDER_EXCEPTION");
        foreach ($result as $key => $value) {
            $info[$value["option_name"]] = $value["option_value"];
        }

        //下班剩余时长
        $hour_diff = setInfNanToN(round((mktime(17,30,00,date("m"),date("d"),date("Y")) - time())/3600,1));

        //标准差距 = 调整后的全天新单-预计全天新单
        $info["order_diff"] = $info["order_avg"]*$info["kfCount"] - $info["estimatedCount"];

        //当人均新单小于预警最小值
        //调整后的全天新单=人均新单标准*今日坐席数
        //调整后的新单速度=（调整后的全天新单-已有新单）/目前至下班剩余时长+新单冒出速度
        $info["speed_mark"] = 0;
        if ( $info["estimatedAvgCount"] < $info["order_min"]) {
            $info["after_order_speed"] = setInfNanToN(round($info["order_diff"]/ $hour_diff + $info["orderSpeed"]));
            $info["speed_mark"] = 1;
        } elseif( $info["estimatedAvgCount"] > $info["order_max"]) {
            $info["speed_mark"] = 2;
        }

        return $info;
    }

    private function getEstimatedOrderList()
    {
        //获取当前客服数量
        $kf_num = D("options")->getOptionNoCache("kf_num")["option_value"];
        $new_kf_num = D("options")->getOptionNoCache("new_kf_num")["option_value"];
        $info['kfCount'] = $kf_num + $new_kf_num;

        //获取预警值
        $day = date("Y-m-d",time());
        $result = D("OrdersExceptionStatistics")->getExceptionStatisticsByDay($day);

        foreach ($result as $key => $value) {
            $list["month_order"][$value["time"]] =  (int)$value["month_order"];
            $list["customer_history_order"][] = $value["customer_history_order"];
            $list["customer_order"][$value["time"]] = $value["customer_order"] == 0?"-":$value["customer_order"];
            $list["forecast_order"][$value["time"]]["value"] = $value["forecast_order"];
            $list["forecast_order"][$value["time"]]["mark"] = 0;
            if ($value["forecast_order"] > $value["month_order"]) {
                if ($value["time"] >= date("H")) {
                    $list["forecast_order"][$value["time"]]["mark"] = 2;
                }
            } elseif ($value["forecast_order"] < $value["month_order"]) {
                if ($value["time"] >= date("H")) {
                    $list["forecast_order"][$value["time"]]["mark"] = 1;
                }

            }
            $list["avg_count"][] = (int)$value["month_order"];
        }
        //获取当天的发单量
        $month_start = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $month_end = strtotime("+1 day",$month_start)-1;
        $result = D("Orders")->getOrderByTime($month_start,$month_end);
        foreach ($result as $key => $value) {
            $list['time'][] = $value["day_time"];
            $list["order_count"][] = $value["count"];
            $list["order_list"][] = $value["count"];
        }

        if (count($list["order_list"]) < 23) {
            $diff = 23 - date("H");
            for ($i=0; $i < $diff; $i++) {
                 $list["order_list"][] = "-";
            }
        }

        return $list;
    }

    private function getMediaOrderInfo()
    {
        $step = array(
            "08" => "0",
            "09" => "1",
            "10" => "2",
            "11" => "3",
            "13" => "4",
            "14" => "5",
            "15" => "6",
            "16" => "7",
            "17" => "8"
        );
        $hour = date("H");
        $info['now_hour'] = $hour;

        if (array_key_exists($hour,$step)) {
            $after_hour = $hour + 1;
            $info['after_hour'] =  $after_hour;

            $time_step = D("options")->getOptionNoCache("time_step")["option_value"];
            $time_step = json_decode( $time_step ,true );

             //获取媒介渠道标识
            $result = D("OrderSource")->getSrcListByDept(1,2);
            $src = array_reduce($result, function ($src, $value) {
                return array_merge($src, array_values($value));
            }, array());

            //最近一个月时段的来源均值
            $start_time = strtotime("-1 month", strtotime("-1 day",mktime(17,30,00,date("m"),date("d"),date("Y"))));
            $end_time = strtotime("-1 day",mktime(17,30,00,date("m"),date("d"),date("Y")));
            $day_diff = ($end_time - $start_time)/86400;
            $result = D("Orders")->getOrderAvgTimeCountBySrc($start_time,$end_time,$src);

            foreach ($result as $key => $value) {
                if ($after_hour == $value["hour"]) {
                    $media_count += $value["count"];
                }

                if ($value["hour"] >= $after_hour && $value["hour"] <= "18") {
                    $all_media_count += $value["count"];
                }
            }

            $media_avg_count = setInfNanToN(round($media_count/$day_diff));
            $all_media_avg_count = setInfNanToN(round($all_media_count/$day_diff));

            //客服数量
            $kf_num = D("options")->getOptionNoCache("kf_num")["option_value"];
            $new_kf_num = D("options")->getOptionNoCache("new_kf_num")["option_value"];
            $kfCount = $kf_num + $new_kf_num;
            //客服每小时订单量
            $kf_order_count = $time_step[$step[$hour+1]];


            //获取预警值
            $day = date("Y-m-d",time());
            $result = D("OrdersExceptionStatistics")->getExceptionStatisticsByDay($day);
            foreach ($result as $key => $value) {
                $exception[$value["time"]] = $value;
            }
            //需求量最小值 = 下一时段个人发单量 * 今日客服数 - 下一时段预测发单
            $info["min"] = setInfNanToN(round($kf_order_count * $kfCount - $exception[ $hour + 1 ]["forecast_order"]));

            if ($info['min'] < 0) {
                $info['min'] = 0;
            }

            // 求量最大值 = 今日客服数 * 人均新单标准 - 今日已有新单
            //人均新单标准
            $order_avg = D("options")->getOptionNoCache("order_avg")["option_value"];

            //今日已有新单=上一日17：30以后+当前时间段已有发单
            $start_time = strtotime("-1 day",mktime(17,30,00,date("m"),date("d"),date("Y")));
            $end_time = strtotime("+1 hour",strtotime(date("Y-m-d H:00:00",time())));
            $orderCount = D("Orders")->getNowOrderCount($start_time,$end_time);

            $info["max"] = setInfNanToN(round($kfCount *  $order_avg - $orderCount));

            if ($info['max'] < 0) {
                $info['max'] = 0;
            }

            if ($info['min'] > $info['max']) {
                $info['min'] = 0;
            }

            //过去一周媒介新单供给量
            $start_time = strtotime("-8 day",mktime(17,30,00,date("m"),date("d"),date("Y")));
            $end_time = strtotime("-1 day",mktime(17,30,00,date("m"),date("d"),date("Y")));
            $result = D("Orders")->getMediaOrderCount( $start_time,$end_time,$src);

            foreach ($result as $key => $value) {
                $data[$value["id"]]["name"] = $value["name"];
                $data[$value["id"]]["date"][$value["date"]] = $value["count"];
            }

            foreach ($data as $key => $value) {
                $sub = array();
                $sub["name"] = $value["name"];
                $sub['type'] = 'line';
                $sub['stack'] = '总量';
                for ($i = 0; $i < 7; $i++) {
                    $time = date("Y-m-d",strtotime("+$i day",$start_time));
                    if (!in_array($time,$info["weekDate"])) {
                         $info["weekDate"][] = $time;
                    }
                    $sub["data"][] = empty($value["date"][$time])?0:$value["date"][$time];
                }
                $info['weekData'][] = $sub;
                $info["weekLegend"][] = $value["name"];

            }

            //最近6小时媒介供给情况
            $start_time = strtotime("-6 hour",mktime($hour,0,00,date("m"),date("d"),date("Y")));
            $end_time = mktime($hour,0,00,date("m"),date("d"),date("Y"));
            $result = D("Orders")->getMediaHourOrderCount( $start_time,$end_time,$src);
            $data = array();
            foreach ($result as $key => $value) {
                $data[$value["id"]]["name"] = $value["name"];
                $data[$value["id"]]["hour"][$value["hour"]] = $value["count"];
            }

            foreach ($data as $key => $value) {
                $sub = array();
                $sub["name"] = $value["name"];
                $sub['type'] = 'line';
                $sub['stack'] = '总量';
                for ($i = 0; $i < 6; $i++) {
                    $time = date("H",strtotime("+$i hour",$start_time));
                    $secondTime = date("H",strtotime("+".($i+1)." hour",$start_time));
                    $range = $time.":".$secondTime;
                    if (!in_array($range,$info["hour"])) {
                        $info["hour"][] = $range;
                    }
                    $sub["data"][] = empty($value["hour"][$time])?0:$value["hour"][$time];
                }
                $info['hourData'][] = $sub;
                $info["hourLegend"][] = $value["name"];
            }
            return $info;
        }
    }

    /**
     * 城市缺单统计
     * @return [type] [description]
     */
    public function orderLack(){
        //获取月份
        //当前月往前推12个月
        $time = strtotime(date("Y-m-d"));
        $info['month'] = [];
        for ($i = 1; $i <= 12; $i++) {
            $now = strtotime("-$i month",$time);
            $info['month'][] = array(
                "key" => date("Y-m",$now),
                "value" => date("Y年m月",$now)
            );
        }

        //获取所有城市
        $info['city'] = D("Quyu")->getAllQuyuOnly();

        //获取缺单城市数据
        $result = D("Home/Logic/CityMissingOrderLogic")->getCityList(I("get.month"),I("get.city_id"),I("get.city_level"),I("get.whole_month"),I("get.ismark"));
        $info["list"] = $result["list"];
        $info["newList"] = $result["newList"];
        $info["all_count"] = $result["all_count"];
        $this->assign("info",$info);
        $this->display();
    }

    /**
     * 城市缺单
     * @return [type] [description]
     */
    public function citygeneralization()
    {
        $date = I("get.date");
        if (empty($date)) {
            $date = date("Ymd");
            if ($date == date("Ymd") && date("H") < 12) {
                $date = date("Ymd",strtotime("-1 day", strtotime($date)));
            }
        } else {
            $date = str_replace("-","",I("get.date"));
            $time = strtotime($date."01");
            $date = $date.date("t",$time);
            //如果是当月，结束时间为当天
            if (date("m",$time) == date("m")) {
                $date = date("Ymd");
                if (date("H") < 12) {
                    $date = date("Ymd",strtotime("-1 day", strtotime($date)));
                }
            }
        }

        //获取城市基本数据
        $missLogic = new CityMissingOrderLogicModel();
        $cityLogic = new CityGeneralizationCoefficientLogicModel();
        $orderLoigc = new OrdersLogicModel();
        $city = $missLogic->getCityBasicList($date,I("get.city"),I("get.level"),I("get.whole"));

        //获取城市系数数据
        $city = $cityLogic->getCityGeneralization($city,$date,I("get.coefficient"));

        //当月所需发单、当月新增分单、有效分单
        $city = $cityLogic->getCityOrderList($city,$date);

        //发单量、分单量
        $city = $orderLoigc->getAllCityOrderList($city,$date);

        //实际分单量
        $city = $orderLoigc->getAllCityRealOrderList($city,$date);

        //当月缺单、缺单率、当前缺单、当前所需分单
        $city = $cityLogic->getCityOrderNeed($city,$date);

        //获取上个月城市基本数据
        $historyDate = strtotime("-1 month",strtotime($date));
        $historyDate = date("Ymd",mktime(0,0,0,date("m",$historyDate),date("t",$historyDate),date("Y",$historyDate)));
        $historyCity = $missLogic->getCityBasicList($historyDate,I("get.city"),I("get.level"),I("get.whole"));
        $historyCity = $cityLogic->getCityOrderList($historyCity,$historyDate);

        //当前平均缺单
        $city = $cityLogic->getHistoryCity($city,$historyCity);

        //签返会员数量
        $city = $cityLogic->getCityNewQianList($city,$date);

        //汇总数据
        $all = $cityLogic->setSummaryData($city);

        //获取城市信息
        $cittList = $missLogic->getMissCityList($date);

        $this->assign("all",$all);
        $this->assign("cittList",$cittList);
        $this->assign("city",$city);
        $this->display();
    }
    /**
    * 渠道详情
    */
    public function contractpandect()
    {
        if (I("get.id") == "") {
            $this->_error("缺失访问参数,请重新操作");
        }

        $city_id = I("get.id");
        $date = date("Ymd");
        if (!empty(I("get.date"))) {
            $date = str_replace("-","",I("get.date"));
            $time = strtotime($date."01");
            $date = $date.date("t",$time);
        }

        if ($date == date("Ymd") && date("H") < 12) {
            $date = date("Ymd",strtotime("-1 day", strtotime($date)));
        }

        $result = D("DepartmentIdentify")->findAllDept();
        $department = [];
        $dept_arr = array(
            "总裁办" => "zcb",
            "推广二部" => "tg2",
            "推广一部" => "tg1",
            "渠道部" => "qd",
            "公装部" => "gz",
            "新媒体部" => "media",
            "线下广告" => "online",
            "品牌部" => 'brand'
        );
        foreach ($result as $key => $value) {
            if (!array_key_exists($value["dept_belong"], $department)) {
                $department[$value["dept_belong"]]["name"] = $value["dept_belong"];
            }
            $tag = $dept_arr[$value["dept_belong"]];
            if (!array_key_exists($tag, $department[$value["dept_belong"]]["child"])) {
                $department[$value["dept_belong"]]["child"][$tag] = array(
                    "id" => $tag,
                    "name" => $value["dept_belong"].'全部'
                );
            }

            if (!array_key_exists($value["id"], $department[$value["dept_belong"]]["child"])) {
                if ($value["id"] != 15) {
                    $department[$value["dept_belong"]]["child"][$value["id"]] = array(
                        "id" => $value["id"],
                        "name" => $value["name"]
                    );
                }
            }
        }
        unset($department["总裁办"]);

        if (I("get.dept") !== "") {
            $dept = array();
            if (!is_numeric(I("get.dept"))) {
                foreach ($dept_arr as $key => $value) {
                    if (I("get.dept") == $value) {
                        $belong = $key;
                        break;
                    }
                }
                foreach ($department[$belong]["child"] as $key => $value) {
                    $dept[] = $value["id"];
                }
                unset($dept[0]);
                //手动添加客服发单
                $dept[] = '15';
            } else {
                $dept[] = I("get.dept");
            }
        }

        //获取该城市所有渠道订单
        $orderLogic = new OrdersLogicModel();
        $list = $orderLogic->getCityOrderByAllSource($city_id,$date,I('get.begin'),I('get.end'),I('get.group'),I('get.src'),$dept);

        //获取有效分单
        $list = D("Home/Logic/CityGeneralizationCoefficientLogic")->getCityOrderYxOrder($list,$date,$city_id,I('get.begin'),I('get.end'),I('get.group'),I('get.src'));
        
        // 获取该城市所有订单渠道
        $sourceList = $orderLogic->getCityAllSource($city_id, $date, I('get.begin'), I('get.end'));

        // 获取城市信息
        $quyuLogic = new QuyuLogicModel();
        $cityInfo = $quyuLogic->getCityInfoByCid($city_id);

        $this->assign("department", $department);
        $this->assign("sourceList", $sourceList);
        $this->assign("cityInfo", $cityInfo);
        $this->assign("list", $list);
        $this->display();
    }
    /**
         * 渠道来源发单明细
    */
    public function qudaoresource()
    {
        if (I("get.id") == "") {
            $this->_error("缺失访问参数,请重新操作");
        }

        if (I("get.src") == "") {
            $this->_error("缺失访问参数,请重新操作");
        }

        $date = date("Y-m-d");
        if (!empty(I("get.date"))) {
            $date = str_replace("-","",I("get.date"));
            $time = strtotime($date."01");
            $date = $date.date("t", $time);
            if ( $date == date("Ymd") &&  date("H") < 12) {
                $date = date("Ymd",strtotime("-1 day", strtotime($date)));
            }
        }

        $city_id = I("get.id");
        $src = I("get.src");
        //获取渠道发单明细
        $list =  D("Home/Logic/CityGeneralizationCoefficientLogic")->getCityOrderDetailsListWithSrc($city_id,$date,$src,I("get.begin"),I("get.end"),I("get.status"));

        $this->assign("list",$list);
        $this->display();
    }
    /**
         * 城市新增分单统计
    */
    public function fendanorder()
    {
        //获取新增分单量列表
        if (I("get.month") == "") {
            $date = date("Ymd");
        } else {
            $date = str_replace("-","",I("get.month"));
            $time = strtotime($date."01");
            $date = $date.date("t", $time);
        }

        if ($date == date("Ymd") && date("H") < 12) {
            $date = date("Ymd",strtotime("-1 day", strtotime($date)));
        }

        //新增分单城市
        $city = D("Home/Logic/CityGeneralizationCoefficientLogic")->getCityNewVip($date,I("get.city"),I("get.level"),I("get.whole"));

        //当月所需发单、当月新增分单
        $city = D("Home/Logic/CityGeneralizationCoefficientLogic")->getCityOrderList($city,$date);
         //实际分单量
        $city = D("Home/Logic/OrdersLogic")->getAllCityRealOrderList($city,$date);

        //城市列表
        $cityList = D("Home/Logic/CityGeneralizationCoefficientLogic")->getCityNewVipList($date);

        $this->assign("cityList",$cityList);
        $this->assign("city",$city);
        $this->display();
    }

    public function cityVipDetails()
    {
        if ($_POST) {
            $date = I("post.date");
            if (empty($date)) {
                $date = date("Ymd");
                if ( $date == date("Ymd") &&  date("H") < 12) {
                    $date = date("Ymd",strtotime("-1 day", strtotime($date)));
                }
            } else {
                $time = strtotime($date."01");
                $date = $date.date("t", $time);
            }

            if (I("post.month") !== "") {
                $date = str_replace("-","",I("post.month"));
                $time = strtotime($date."01");
                $date = $date.date("t", $time);
            }

            $city_id = I("post.city_id");
            $result =  D("Home/Logic/CityGeneralizationCoefficientLogic")->getCityNewVipDetails($city_id,$date);

            $this->assign("list",$result);
            $tmp = $this->fetch("cityvipdetails");
        }
        $this->ajaxReturn(array("data"=> $tmp,"status" => 1,"info"=>""));
    }
    /**
    * 重点系数统计
    */
    public function coefficientstatistics()
    {
        $month = $date = date("Ymd");
        if (!empty(I("get.date"))) {
            $date = I("get.date");
        }

        if (!empty(I("get.month"))) {
            $month = I("get.month");
        }

        //重点系数变化统计
        $list = D("Home/Logic/CityGeneralizationCoefficientLogic")->getCityCoefficientStatistics($date);

        $detailsList = D("Home/Logic/CityGeneralizationCoefficientLogic")->getCityCoefficientDetailsStatistics($month,I("get.city"),I("get.level"));
        //当月的重点城市
        $cityList =  D("Home/Logic/CityGeneralizationCoefficientLogic")->getCoefficientDetailsCityList($month);

        $this->assign("cityList",$cityList);
        $this->assign("detailsList",$detailsList);
        $this->assign("list",$list);
        $this->display();
    }

    /**
     * 新开站/过期城市
     */
    public function orderLackneworoverdue(){
        $this->display();
    }

    /**
     * 城市重点系数 上传excel
     */
    public function orderLackUploadExcel(){
        //分析Excel内容
        $ex = $_FILES['excel'];
        $filename = TEMP_PATH.'/'.time().substr($ex['name'],stripos($ex['name'],'.'));
        move_uploaded_file($ex['tmp_name'],$filename);
        $excel = importExcel($filename);
        //逐行导入数据
        foreach ($excel as $k => $v) {
            if(empty($v)){
                continue;
            }
            //判断城市是否存在getManageCitys($where
            $where = array(
                'city' => array("EQ",trim($v['2']))
            );
            $result = D('SaleSetting')->getManageCitys($where);
            $city = $result[0];
            if(!$city['cid'] || !$city['id'] ){
                continue;
            }
            //构造数据
            $data['manage_id'] = $city['id'];
            $data['cid'] = $city['cid'];
            $data['cname'] = trim($v['2']);
            $data['ratio'] = trim($v['3']);
            $data['lasttime'] = time();
            $map['manage_id'] = $city['id'];

            $isExist = D('SalesSetting')->hasSalesCityRatio($map);
            if(!empty($isExist)){
                D('SalesSetting')->editSalesCityRatio($isExist['manage_id'],$data);
            }else{//不存在
                D('SalesSetting')->addSalesCityRatio($data);
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作成功','status'=>1));
    }

    /**
     * 超出20单来自渠道推广的订单
     */
    public function getMoreSrcOrder(){
        if($_POST){
            $num = intval(I('post.diff'));
            $cs = trim(I('post.cs'));

            $result = D('SalesSetting')->getMoreSrcOrder($num,$cs,strtotime(date('Y-m-01')),strtotime(date('Y-m-d')) +86400-1);//当月
            if(!empty($result)){
                $data['list'] = $result;
                $data['rows'] = count($result);
                $this->ajaxReturn(array('data'=>$data,'status'=>1));
            }else{
                $this->ajaxReturn(array('status'=>0));
            }

        }
    }

    // 装修客服打出家具单
    public function getJiajuOrder(){
        $where = I('get.');
        //客服列表
        $kflist = D("Home/Logic/AdminuserLogic")->getKfList();

        //客服组列表
        $user = $this->getKfGroupInfo();

        //权限控制
        //如果是客服登录
        if(session("uc_userinfo.uid") == 2){
            $where['kf_id'] = session("uc_userinfo.id");
            $user['groups'] = [];
            $kflist = [['name'=> session("uc_userinfo.name"),'id'=> session("uc_userinfo.id")]];
        }
        //如果是客服组长
        if(session("uc_userinfo.uid") == 31){
            $where['kfgroup'] = session("uc_userinfo.kfgroup");
            $user['groups'] = [["kfgroup"=>session("uc_userinfo.kfgroup"),'name'=>session("uc_userinfo.name")]];
            $kflist = D("Home/Logic/AdminuserLogic")->getKFListByGroup(2,session("uc_userinfo.kfgroup"));
        }

        //获取客服分单列表
        $ordersLogic  = new OrdersLogicModel();
        $result = $ordersLogic->getZhuangxiuOrders($where);
        $this->assign("groups",$user['groups']);
        $this->assign("kflist",$kflist);
        $this->assign("result",$result);

        $this->display();
    }

    public function jiajustat()
    {
        $begin = I("get.begin") == ""?date("Y-m-1"):I("get.begin");
        $end = I("get.end") == ""?date("Y-m-d"):I("get.end");
        $id = I("get.id");
        $group = I("get.group");

        //添加排序方式
        $sort = I('get.sort')?:'all';
        $key = 'quanwu_count';
        if($sort=='fen'){
            $key = 'fen_count';
        }elseif ($sort=='zen'){
            $key = 'zen_count';
        }

        $order = I('get.order')?:'desc';
        //获取客服列表
        $kfList = D("Adminuser")->getKfList();

        //获取客服团/师
        $manager = $this->getKfGroupInfo();

        //统计列表
        $logic = new JiajuOrdersLogicModel();
        $list = $logic->jiajustat($kfList,$manager,$id,$group,$begin,$end);
        //排序
        $list = $logic->sortStat($list,$key,$order);

        $this->assign("sort",$sort);
        $this->assign("order",$order);
        $this->assign("list",$list);
        $this->assign("begin",$begin);
        $this->assign("end",$end);
        $this->assign("kfList",$kfList);
        $this->assign("manager",$manager);
        $this->display();
    }

    public function quanwustat()
    {
        $begin = I("get.begin") == ""?date("Y-m-d",strtotime("-1 month")):I("get.begin");
        $end = I("get.end") == ""?date("Y-m-d"):I("get.end");
        $city = I("get.city")?:'';
        //添加排序方式
        $sort = I('get.sort')?:'all';
        $key = ($sort == 'all') ? 'count' : 'quanwu_count';
        $order = I('get.order')?:'desc';

        $quyuLogic = new QuyuLogicModel();
        $cityList = $quyuLogic->getAllCity();

        //统计列表
        $logic = new JiajuOrdersLogicModel();
        $list = $logic->quanwustat($cityList,$city,$begin,$end);

        $list = $logic->sortStat($list,$key,$order);

        $this->assign("sort",$sort);
        $this->assign("order",$order);
        $this->assign("begin",$begin);
        $this->assign("end",$end);
        $this->assign("list",$list);
        $this->assign("city",$cityList);
        $this->display();
    }

    // 城市缺单统计
    public function citylackorder(){
        $input = I("get.");

        $maxdate = date("Y-m-d");
        $limittime = strtotime(date("Y-m-d") . " 12:00:00");
        if (time() <= $limittime) {
            $maxdate = date("Y-m-d", strtotime("-1 day"));
        }

        // 默认查询时间
        if (empty($input["end"])) {
            $input["end"] = $maxdate;
        }

        // 开始时间只能是查询月1号
        $input["begin"] = date("Y-m-01", strtotime($input["end"]));

        $cityOrderLackLogic = new CityOrderLackLogicModel();
        $data = $cityOrderLackLogic->getCityLackOrderStat($input);

        // 城市下拉数据
        $quyuLogic = new QuyuLogicModel();
        $cityList = $quyuLogic->getAllCity();
        $this->assign("citylist", $cityList);

        // 判断是否有渠道详情权限
        $detail_power = check_user_menu_auth('/orderstat/citylackorder_detail_btn');

        $this->assign("input", $input);
        $this->assign("maxdate", $maxdate);
        $this->assign("detail_power", $detail_power);
        $this->assign("list", $data["list"]);
        $this->assign("all", $data["all"]);
        $this->display();
    }

    // 设置实际所需分单
    public function cityorder_actual(){
        $userinfo = session("uc_userinfo");

        if (IS_AJAX) {
            $city_id = I("post.city_id");
            $datetime = I("post.datetime");
            $actual_orders = I("post.actual_orders");

            $cityOrderLackLogic = new CityOrderLackLogicModel();
            $ret = $cityOrderLackLogic->setCityOrderActualOrders($city_id, $datetime, $actual_orders, $userinfo["id"]);
            if ($ret !== false) {
                $this->ajaxReturn([
                    "error_code" => 0,
                    "error_msg" => "请求成功"
                ]);
            }

            $this->ajaxReturn([
                "error_code" => 404,
                "error_msg" => "设置失败"
            ]);
        }
    }

    // 实际所需分单操作日志
    public function cityorder_actual_log(){
        $city_id = I("get.city_id");
        $datetime = I("get.datetime");

        $cityOrderLackLogic = new CityOrderLackLogicModel();
        $list = $cityOrderLackLogic->getCityOrderActualLog($city_id, $datetime);

        $this->ajaxReturn([
            "error_code" => 0,
            "error_msg" => "请求成功",
            "data" => [
                "list" => $list
            ]
        ]);
    }

    public function pauseOrderStat()
    {
        $kf = I("get.kf");
        $group = I("get.group");
        $manager = I("get.manager");
        $state = I("get.state");

        //获取客服列表
        $kfList = D("Adminuser")->getKfList(false,true);
        //获取客服组长列表
        $users = $this->getKfGroupInfo();
        //获取数据列表
        $logic = new OrdersLogicModel();
        $list = $logic->getPauseOrderStat($kf,$group,$manager,$state);
        $this->assign("list", $list);
        $this->assign("groups", $users["groups"]);
        $this->assign("manager", $users["manager"]);
        $this->assign("kfList",$kfList);
        $this->display();
    }

    public function pauseOrderSummary()
    {

        $begin = I("get.begin");
        $end = I("get.end");

        $month_start = date("Y-m-d",mktime(0,0,0,date("m"),1,date("Y")));
        $month_end = date("Y-m-d");

        if (!empty($begin) && !empty($end)) {
            $month_start = $begin;
            $month_end = $end;
        }

        $logic = new OrdersLogicModel();
        $info = $logic->pauseOrderSummary($month_start,$month_end);
        $this->assign("begin",$month_start);
        $this->assign("end",$month_end);
        $this->assign("info",$info);
        $this->display();
    }

    public function  customerMediumTelStat()
    {
        $begin = I("get.begin",date("Y-m-1"));
        $end = I("get.end",date("Y-m-d"));
        $kf = I("get.kf");
        $group = I("get.group");

        if (empty($begin) || empty($end)) {
            $begin = date("Y-m-01");
            $end = date("Y-m-d");
        }

        //获取所有客服
        $kfList = D("Adminuser")->getKfList(false,false,[],false,[154]);

        //获取客服组长列表
        $kfGroup = $this->getKfGroupInfo(155);

        $logic = new OrdersLogicModel();
        $list = $logic->getCustomerMediumTelStat($begin,$end,$kf,$group);

        $this->assign("begin",$begin);
        $this->assign("end",$end);

        $this->assign("list",$list[0]);
        $this->assign("all",$list[1]);
        $this->assign("kfList",$kfList);
        $this->assign("users",$kfGroup);
        $this->display();
    }

    public function customerMediumCityStat()
    {
        $begin = I("get.begin",date("Y-m-1"));
        $end = I("get.end",date("Y-m-d"));
        $city = I("get.city");


        if (empty($begin) || empty($end)) {
            $begin = date("Y-m-01");
            $end = date("Y-m-d");
        }

        //获取全部城市
        $result = D("Quyu")->getAllQuyuOnly();
        foreach ($result as $key => $value) {
            $quyu[$value["cid"]] = $value;
        }

        $logic = new OrdersLogicModel();
        $list = $logic->customerMediumCityStat($begin,$end,$city);

        $this->assign("begin",$begin);
        $this->assign("end",$end);
        $this->assign("list",$list[0]);
        $this->assign("all",$list[1]);
        $this->assign("city",$quyu);
        $this->display();
    }
}