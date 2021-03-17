<?php
//ERP相关模块
namespace Home\Model\Logic;
use Common\Enums\OrderSource;

class YxbLogicModel
{
    private $logAction = array(
        1=>array('action'=>'申请开通','status'=>'待审核'),
        2=>array('action'=>'创建','status'=>'待审核'),
        3=>array('action'=>'驳回','status'=>'驳回'),
        4=>array('action'=>'终止','status'=>'终止'),
        5=>array('action'=>'取消申请','status'=>'未开通'),
        6=>array('action'=>'申请开通','status'=>'待审核'),
        7=>array('action'=>'申请开通','status'=>'待审核'),
        11=>array('action'=>'审核通过','status'=>'未生效'),
        12=>array('action'=>'审核通过','status'=>'已开通'),
        13=>array('action'=>'审核通过','status'=>'已过期'),
    );
    protected $type = array(
        "1" => "待审核",
        "3" => "驳回",
        "4" => "已终止",
        "5" => "未开通",
        "11" => "未生效",
        "12" => "已开通",
        "13" => "已到期"
    );
    protected $shenghe = array(
        "1"=>"待审核",
        "2"=>"审核通过",
        "3"=>"驳回",
        "4"=>"未开通"
    );
    //施工状态
    protected  $shiGongStatus = [
        2 => '1.开工大吉',
        3 => '2.主体拆改',
        4 => '3.水电整改',
        5 => '4.防水施工',
        6 => '5.泥瓦工程',
        7 => '6.木工进场',
        8 => '7.厨卫吊顶',
        9 => '8.油漆粉刷',
        10 => '9.铺贴壁纸',
        11 => '10.成品安装',
        12 => '11.保洁收尾',
        13 => '12.家具进场',
        14 => '13.家电进场',
        15 => '14.家居配饰',
        16 => '15.交付工程',
    ];

    //订单状态
    protected  $orderStatus = [
        1 => '未联系',
        2 => '已联系',
        3 => '预约量房',
        4 => '已见面',
        5 => '已量房',
        6 => '未成功量房',
        7 => '签订设计合同',
        8 => '签订装修合同',
        9 => '施工中',
        10 => '延期单',
        11 => '已竣工',
        12 => '废弃单'
    ];

    //订单类型
    protected $orderType = [
        1 => '分单',
        2 => '赠单'
    ];

    //订单来源
    protected $orderSource = [
        2 => '齐装订单',
        3 => '其他订单'
    ];

    //订单分类


    protected $orderStatusRange = [
        'reception' => ['list'=>[1],'last'=>1,'dengji'=>1],
        'follow' => ['list'=>[2,3,4,5,6,7],'last'=>7,'dengji'=>2],
        'order_sign' => ['list'=>[8],'last'=>8,'dengji'=>3],
        'order_building' => ['list'=>[9,10],'last'=>10,'dengji'=>4],
        'order_end' => ['list'=>[],'last'=>9,'dengji'=>5],
        'order_over' => ['list'=>[11,12],'last'=>12,'dengji'=>6]
    ];


    //获取审核状态
    public function getShengheType(){
        return $this->shenghe;
    }

    //获取订单类型
    public function getOrderType(){
        return $this->orderType;
    }

    //获取订单状态
    public function getOrderStatus(){
        return $this->orderStatus;
    }
    //获取施工状态
    public function getShiGongStatus(){
        return $this->shiGongStatus;
    }
    //获取订单来源
    public function getOrderSource(){
        return $this->orderSource;
    }

    //获取城市信息
    public function getAllCity(){
        $result = R("Api/getAllCity");
        $city = [];
        foreach ($result as $key => $value) {
            $city[$value['cid']]['cid'] = $value["cid"];
            $city[$value['cid']]['cname'] = $value["cname"];
        }
        return $city;
    }
    //获取账号状态
    public function getType(){
        return $this->type;
    }


    /**
     * 添加到qz_user表
     */
    public function addUser($data){
        $data['classid'] = 3;
        $data['user_type'] = 5;
        $data['register_time'] = time();

      return  D('Home/Db/YxbAccount')->addUser($data);
    }

    /**
     * @param int $userid
     *添加到qz_user_company表
     */
    public function addUserCompany($userid){
        $data['userid']  = $userid;
        $data['text'] = '该公司很懒,什么都没留下！';
        return D("Home/Db/YxbAccount")->addUserCompany($data);
    }

    /**
     * 添加到qz_yxb_account表
     * @param $data
     * @param $post
     * @return mixed
     */
    public function addUserErp($data,$post){
        $info['account'] = $post['account'];
        $info['contact_name'] = $post['contact_name'];
        $info['contact_tel'] = $post['contact_tel'];
        $info['contact_wx'] = $post['contact_wx'];
        if(!empty( $data['pass'])&&isset($data['pass'])){
            $info['pass'] = $data['pass'];
            $passInfo['nopass'] =  $data['nopass'];
        }else{
            $passInfo = make_password();
            $info['pass'] =  $passInfo['pass'];
        }
        $info['company_id'] = $data['userid'];
        $info['uptime'] = time();
        $result['erpid'] = D("Home/Db/YxbAccount")->addUserErp($info);
        //创建成功erp之后给 工种/岗位 添加公司默认数据
        if ($result['erpid']) {
            //工种
            D("Home/Db/YxbAccount")->addUserErpWorktype($this->getWorkerSave($info['company_id']));
            //岗位
            D("Home/Db/YxbAccount")->addUserErpStation($this->getStationSave($info['company_id']));
            //岗位添加默认角色
            D("Home/Db/YxbAccount")->addUserErpStationRule($this->getStationRuleSave($info['company_id']));
            //订单来源
            D("Home/Db/YxbAccount")->addUserErpOrdersource($this->getOrdersourceSave($info['company_id']));
            //修改已有订单的订单来源
            D("Home/Db/YxbAccount")->updateUserErpOrdersource($info['company_id'],(empty(OrderSource::getOrderSource($info['company_id'])) ? 0 :OrderSource::getOrderSource($info['company_id'])));
        }
        $result['nopass'] = $passInfo['nopass'];
        return $result;

    }


    /**
     * @param int $userid
     * 添加到qz_user_erp表
     */
    public function addUserErpTime($userid,$post){
        $info['company_id'] = $userid;
        $info['start_time'] = strtotime(date('Y-m-d 00:00:00',strtotime($post['start_time'])));
        $info['end_time'] = strtotime(date('Y-m-d 23:59:59',strtotime($post['end_time'])));
        $info['uptime'] = time();
        //e.1.7.1后台功能更新：去除168new后台ERP账号审核功能，在ERP装修公司管理模块中，创建/续费账号成功后，立即更新ERP账号状态为审核通过
        $info['type'] = 2;
        return D("Home/Db/YxbAccount")->addUserErpTime($info);
    }

    /**
     * @param int $userid
     * 添加到qz_user_erp表
     */
    public function editUserErpTime($time_id,$post){
        $where['id'] = array('EQ',$time_id);
        $info['start_time'] = strtotime(date('Y-m-d 00:00:00',strtotime($post['start_time'])));
        $info['end_time'] = strtotime(date('Y-m-d 23:59:59',strtotime($post['end_time'])));
        $info['uptime'] = time();
        //e.1.7.1后台功能更新：去除168new后台ERP账号审核功能，在ERP装修公司管理模块中，创建/续费账号成功后，立即更新ERP账号状态为审核通过
        $info['type'] = 2;
        return D("Home/Db/YxbAccount")->editUserErpTime($where,$info);
    }


    /**
     * 创建公司时添加日志
     * @param int $userid
     * 添加到qz_user_erp_log表
     */
    public function addUserErpLog($userId,$end_type,$start_type,$timeid,$remark){
        if(!empty($userId)){
            $data['company_id'] = $userId;
        }
        if(!empty($timeid)){
            $data['time_id'] = $timeid;
        }
        if(!empty($start_type)){
            $data['start_type'] = $start_type;
        }
        if(!empty($end_type)){
            $data['end_type'] = $end_type;
        }
        if(!empty($remark)){
            $data['remark'] = $remark;
        }
        $data['uptime'] = time();
        $data['name'] = session("uc_userinfo.name");
        $data['uid'] = session("uc_userinfo.id");
        return D("Home/Db/YxbAccount")->addUserErpLog($data);
    }

    /**
     * 公司为真会员
     * @param int $userid
     */
    public function getUserInfo($userid){
        $result =  D("Home/Db/YxbAccount")->getUserInfo($userid);
        if(!empty($result)&&isset($result)){
            $info['status'] = 1;
            $info['data'] = $result[0];
        }else{
            $info['status'] = 0;
            $info['info'] = '公司信息有误,请重新进入';
        }
        return $info;
    }

    public function getErpInfo($userid,$timeid){
        $result =  D("Home/Db/YxbAccount")->getErpInfo($userid);
        $nowDate = time();
        $nowTime = strtotime(date('Y-m-d 23:59:59'));
        //如果当前时间在time表的某一时间段内,取该段时间,若不在,取最新一条记录
        foreach($result as $key=>$val) {
            if($val['start_time']&&$val['end_time']){
                $result[$key]['sum_day'] = ( $result[$key]['end_time']+1- $result[$key]['start_time'])/86400;
                $result[$key]['sum_month'] = number_format( $result[$key]['sum_day']/30,2);
                $result[$key]['remain_day'] = ($result[$key]['end_time']-$nowTime)/86400+1;
                $result[$key]['start_time'] = date('Y-m-d',$result[$key]['start_time']);
                $result[$key]['end_time'] = date('Y-m-d',$result[$key]['end_time']);
            }else{
                $result[$key]['start_time'] = '';
                $result[$key]['end_time'] = '';
            }


            if ($val['start_time'] <= $nowDate && $val['end_time'] >= $nowDate) {
                if($val['type'] == 2){
                    $result[$key]['type'] = 12; //已开通
                }
            }
            else if ($val['start_time'] > $nowDate) {
                if($val['type'] == 2){
                    $result[$key]['type'] = 11; //未生效
                }

            } else {
                if($val['type'] == 2){
                    $result[$key]['type'] = 13; //已过期
                }
            }

            if($val['time_id'] == $timeid){
                $row =  $result[$key];
            }

        }

        if(empty($row)){
            return false;
        }

        $row['status'] = $this->type[ $row['type']];
        if($row['contact_tel']){
            if(strlen($row['contact_tel']) > 4){
                $row['contact_tel'] =  $this->substr_cut($row['contact_tel'],3,1);
            }
        }
        if($row['contact_wx']){
            if(strlen($row['contact_wx']) > 4){
                $row['contact_wx'] =  $this->substr_cut($row['contact_wx'],2,2);
            }
        }
        $result = multi_array_sort($result,'time_id');
        return array('row'=>$row,'list'=>$result);

    }

    /**
     * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
     * @param  [string] $user_name 字符串
     * @param  [int] $head      左侧保留位数
     * @param  [int] $foot      右侧保留位数
     * @return string 格式化后的姓名
     */
    public function substr_cut($user_name,$head,$foot){

        $strlen     = mb_strlen($user_name, 'utf-8');
        $firstStr     = mb_substr($user_name, 0, $head, 'utf-8');
        $lastStr     = mb_substr($user_name, -$foot, $foot, 'utf-8');
        return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - ($head+$foot)) . $lastStr;
    }



    /**
     * 数据唯一性
     * @param $post
     */
    public function getCompanyVerify($post){
        $where['classid'] = array('IN',array(3,6));
        $info['status'] = 1;
        if(!$post['userid']){
            $where['jc'] = array('EQ', $post['jc']);
            $sum = D('Home/Db/YxbAccount')->getExistCompany($where);
            unset($where['jc']);
            if ($sum > 0) {
                $info['status'] = -1;
                return $info;
            }
            $where['user'] = array('EQ', $post['account']);
            $sum = D('Home/Db/YxbAccount')->getExistCompany($where);
            unset($where['user']);
            if ($sum > 0) {
                $info['status'] = -2;
                return $info;
            }

            if (!empty($post['qc']) && isset($post['qc'])) {
                $where['qc'] = array('EQ', $post['qc']);
                $sum = D('Home/Db/YxbAccount')->getExistCompany($where);
                if ($sum > 0) {
                    $info['status'] = -3;
                    return $info;
                }
            }
        }

        $map['account'] = array('EQ',$post['account']);
        $sum = D('Home/Db/YxbAccount')->getExistErp($map);
        if($sum>0){
            $info['status'] = -2;
            return $info;
        }else{
            $map['user'] = array('EQ', $post['account']);
            $result = D('Home/Db/YxbAccount')->getExistCompany($map);
            if($result>0){
                $info['status'] = -2;
                return $info;
            }
        }
        return $info;
    }

    /**
     * 获取公司id
     * @param $userId
     * @return mixed
     */
    public function getUserId($post){
        if(empty($post['userid'])){
            //装修公司不存在


            //添加到qz_user表
            $data['jc'] =  $post['jc'];
            $data['qc'] =  $post['qc'];
            $data['cs'] =  $post['cs'];
            $data['user'] =  $post['account'];
            $data['dz'] =  $post['dz'];
            $passInfo =  make_password();
            $data['pass'] = $passInfo['pass'];
            $userId = $this->addUser($data);
            if($userId>0){
                //添加到qz_user_company表
                $this->addUserCompany($userId);
                //添加到qz_user_erp_log日志表
                $this->addUserErpLog($userId,8);
                $info['data'] = array('userid'=>$userId,'nopass'=> $passInfo['nopass'],'pass'=>$passInfo['pass']);
                $info['status'] = 1;
            }else{
                $info['status'] = 0;
                $info['info'] = '公司添加失败.请重试';
            }
        }else{
            //装修公司已存在
            $info['data'] =  array('userid'=>$post['userid']);
            $info['status'] = 1;
        }
//        var_dump( $info);
        return $info;
    }

    /**
     * 添加erp信息和续费信息
     * @param $userid
     */
    public function addErp($data,$post){
        //添加到qz_user_erp表
        $erp = $this->addUserErp($data,$post);
        //添加到qz_user_erp_time表
        if($erp['erpid']>0){
            //添加 创建erp公司 到日志表
            $this->addUserErpLog($data['userid'],10);

            $erpTime = $this->addUserErpTime($data['userid'],$post);

            if($erpTime>0){
                //添加 创建erp 到日志表
                $this->addUserErpLog($data['userid'],2,'',$erpTime);
                $info['status'] = 1;
                $info['nopass'] = $erp['nopass'];
            }else{
                $info['status'] = 0;
                $info['info'] = "创建erp失败,请重试";
            }

        }else{
            $info['status'] = 0;
            $info['info'] = "创建账号失败,请重试";
        }

        return $info;
    }

    /**
     * 获取ERP公司数据
     * @param $where
     * @return mixed
     */
    public function getErpCompanyList($where,$order, $count)
    {
        //强制数字整数
        if($count>0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = D('Home/Db/YxbAccount')->getErpCompanyList($where,$order,$p->firstRow, $p->listRows);
            //处理续费数据
            return ['list' => $list, 'page' => $show];
        }
    }

    public function getErpCompanyTimeList($where,$order, $count){
        //强制数字整数
        if($count>0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = D('Home/Db/YxbAccount')->getErpCompanyTimeList($where,$order,$p->firstRow, $p->listRows);
            //处理续费数据
            return ['list' => $list, 'page' => $show];
        }
    }

    /**
     * 重置密码
     * @param $id 公司ID
     */
    public function makeErpPsw($id){
        //生成
        $passInfo = make_password();
        $psw = $passInfo['pass'];
        $save['pass'] = $psw;
        $where['company_id'] = $id;
        $result = D('Home/Db/YxbAccount')->editErp($save,$where);

        if($result>0){
            $info['status'] = 1;
            $info['data'] = $passInfo['nopass'];
        }else{
            $info['status'] = 0;
            $info['info'] = '生成失败';
        }
        return $info;
    }

    public function getErpTimeList($result){
        foreach($result as $val){
            $list[$val['id']][]  = $val;
        }

        $nowDate = time();
        foreach($list as $key=>$val){
            $erpList[$key] = $val[0];
            if(count($val)>1){
                foreach($val as $v){
                    if($v['start_time']<=$nowDate && $v['end_time']>=$nowDate){
                        $erpList[$key] = $v;
                        break;
                    }
                }
            }
            if( $erpList[$key]['type'] == 2){
                if($nowDate>$erpList[$key]['end_time']){
                    $erpList[$key]['type'] = 13; //已过期
                }else if($nowDate<$erpList[$key]['start_time']){
                    $erpList[$key]['type'] = 11; //未生效
                }else{
                    $erpList[$key]['type'] = 12; //已开通
                }
            }

            if($erpList[$key]['start_time']&&$erpList[$key]['end_time'] ){
                $erpList[$key]['sum_day'] = ($erpList[$key]['end_time']+1-$erpList[$key]['start_time'])/86400;
                if($erpList[$key]['type'] == 12){
                    $nowTime = strtotime(date('Y-m-d 23:59:59'));
                    $erpList[$key]['remain_day'] = ( $erpList[$key]['end_time']-$nowTime)/86400+1;
                }
                $erpList[$key]['start_time'] = date('Y-m-d',$erpList[$key]['start_time']);
                $erpList[$key]['end_time'] = date('Y-m-d', $erpList[$key]['end_time']);
            }else{
                $erpList[$key]['start_time'] = '';
                $erpList[$key]['end_time'] = '';
            }

        }
        unset($result);
        unset($list);
        return $erpList;
    }


    /**
     * 获取城市订单数据个数
     * @param $where
     * @return mixed
     */
    public function getErpCompanyCount($where)
    {
        return D('Home/Db/YxbAccount')->getErpCompanyCount($where);
    }

    public function getErpCompanyTimeCount($where)
    {
        return D('Home/Db/YxbAccount')->getErpCompanyTimeCount($where);
    }

    /**
     * 编辑erp状态
     * @param $type
     * @param $time_id
     */
    public function editErpType($type,$old_type,$log_type,$time_id,$id,$remark){

        //type=5 取消申请时清空有效时间
        if($type == 5){
            $save['start_time'] = 0;
            $save['end_time'] = 0;
        }
        //type=4 终止erp 已开通时结束时间改成当前时间 未生效时 开始结束时间改为当天
        $where['id']  = array('EQ',$time_id);
        //获取该erp原有有效时间
        $nowErp = D('Home/Db/YxbAccount')->getNewErpTime($where);
        if($type == 4){
            //已开通
            if($old_type == 12){
                $save['end_time'] =strtotime(date('Y-m-d 23:59:59'));
                $remark = '有效结束时间:'.date('Y-m-d',$nowErp[0]['end_time']).'改为'.date('Y-m-d 23:59:59');
            }else if($old_type == 11){
                $save['start_time'] = strtotime(date('Y-m-d 00:00:00'));
                $save['end_time'] = strtotime(date('Y-m-d 23:59:59'));
                $remark = '有效时间:'.date('Y-m-d',$nowErp[0]['start_time']).'~'.date('Y-m-d',$nowErp[0]['end_time']).'改为'.date('Y-m-d').'~'.date('Y-m-d');
            }
        }

        $save['type'] = $type;
        $save['uptime'] = time();
        $result =  D('Home/Db/YxbAccount')->editErpType($save,$where);

        if($result>0){
            if($log_type == 2){
                $nowDate = time();
                if($nowDate>$nowErp[0]['end_time']){
                    $log_type = 13; //已过期
                }else if($nowDate<$nowErp[0]['start_time']){
                    $log_type = 11; //未生效
                }else{
                    $log_type= 12; //已开通
                }
            }
            //记录
            $this->addUserErpLog($id,$log_type,$old_type,$time_id,$remark);
            $info['status'] = 1;
        }else{
            $info['status'] = 0;
            $info['info'] = '操作失败';
        }
        return $info;
    }

    /**
     *编辑erp账号信息表
     * @param $name 联系人
     * @param $tel 联系电话
     * @param $wx 微信
     */
    public function editErp($name,$tel,$wx,$companyId){
        $data['contact_name'] = $name;
        $data['contact_tel'] = $tel;
        $data['contact_wx'] = $wx;
        $data['uptime'] = time();
        $where['company_id'] = $companyId;
        $result = D('Home/Db/YxbAccount')->editErp($data,$where);
        if($result>0){
            //记录
            $this->addUserErpLog($companyId,9);
            $info['status'] = 1;
        }else{
            $info['status'] = 0;
            $info['info'] = '操作失败';
        }
        return $info;
    }

    /**
     * 续费
     * @param $id
     */
    public function addErpTime($userid,$post){
        //判断开始时间必须上条记录的结束时间 开始日期必须大于等于今天
        $time_ok = $this->getTrueErpTime($userid,'',$post);
        if($time_ok['status']){
            //续费操作
            $erpTime = $this->addUserErpTime($userid,$post);
            if($erpTime>0){
                //添加 续费 到日志表
                $this->addUserErpLog($userid,7,'',$erpTime);
                $info['status'] = 1;
            }else{
                $info['status'] = 0;
                $info['info'] = "续费操作失败,请重试";
            }
        }else{
            $info['status'] = 0;
            $info['info'] = $time_ok['info'];
        }
        return $info;
    }

    /**
     * 已有ERP账号申请开通
     * @param $id
     */
    public function editErpTime($userid,$time_id,$post){
        //判断开始时间必须上条记录的结束时间 开始日期必须大于等于今天
        $time_ok = $this->getTrueErpTime($userid,$time_id,$post);
        if($time_ok['status']){
            //开通操作
            $erpTime = $this->editUserErpTime($time_id,$post);
            if($erpTime>0){
                //添加 申请开通 到日志表
                $this->addUserErpLog($userid,1,'',$time_id);
                $info['status'] = 1;
            }else{
                $info['status'] = 0;
                $info['info'] = "续费操作失败,请重试";
            }
        }else{
            $info['status'] = 0;
            $info['info'] = $time_ok['info'];
        }
        return $info;
    }


    /**
     * 开始时间判断
     * @param $userid
     * @param $post
     */
    public function getTrueErpTime($userid,$timeid,$post){
        //开始日期必须大于等于今天
        $nowtime = strtotime(date('Y-m-d 00:00:00'));
        $start_time =  strtotime(date('Y-m-d 00:00:00',strtotime($post['start_time'])));
        $end_time = strtotime(date('Y-m-d 23:59:59',strtotime($post['end_time'])));

        if($nowtime>$start_time){
            $info['status'] = 0;
            $info['info'] = '开始日期必须大于等于今天';
        }else{
            //获取该公司所有有效时间
            $where['company_id'] = array('EQ',$userid);
            $row = D('Home/Db/YxbAccount')->getNewErpTimeList($where);

            $lock = false;

           if(!empty($row)&&isset($row)){
                foreach($row as $val){
                    if($val['id'] != $timeid){
                       $lock = $this->is_time_cross($val['start_time'], $val['end_time'], $start_time, $end_time);
                        if($lock){
                            break;
                        }
                    }
                }

               if($lock){
                   $info['status'] = 0;
                    $info['info'] = 'ERP有效日期与该公司已存在的记录有重叠';
               }else{
                   $info['status'] = 1;
               }
           }else{
               $info['status'] = 1;
           }
        }
        return $info;
    }

    /**
     * 两个时间段是否有重叠
     * @param string $beginTime1
     * @param string $endTime1
     * @param string $beginTime2
     * @param string $endTime2
     * @return bool
     */
    public function is_time_cross($beginTime1 = '', $endTime1 = '', $beginTime2 = '', $endTime2 = '') {
        $status = $beginTime2 - $beginTime1;
        if ($status > 0) {
            $status2 = $beginTime2 - $endTime1;
            if ($status2 >= 0) {
                return false;
            } else {
                return true;
            }
        } else {
            $status2 = $endTime2 - $beginTime1;
            if ($status2 > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    //获取单条erp日志
    public function getErpLogInfo($time_id){
        if($time_id){
            $where['time_id'] = array('EQ',$time_id);
            $where['end_type'] = array('in',array(1,2,3,4,5,6,7,11,12,13));
            $list =  D('Home/Db/YxbAccount')->getErpLogInfo($where);
            foreach($list as $key=>$val){
                $list[$key]['action'] = $this->logAction[$val['end_type']]['action'];
                $list[$key]['status'] = $this->logAction[$val['end_type']]['status'];
                $list[$key]['time'] = date('Y-m-d H:i:s',$val['uptime']);
            }
            return $list;
        }
    }

    //获取公司所有erp日志
    public function getErpLog($company_id){
       $where['company_id'] = array('EQ',$company_id);
       $where['end_type'] = array('in',array(1,2,3,4,5,6,7,11,12,13));
        $list =  D('Home/Db/YxbAccount')->getErpLogAndCompanyInfo($where);
        foreach($list as $key=>$val){
            $list[$key]['action'] = $this->logAction[$val['end_type']]['action'];
            $list[$key]['start_time'] = empty($val['start_time'])?'':date('Y-m-d H:i:s',$val['start_time']);
            $list[$key]['end_time'] = empty($val['end_time'])?'':date('Y-m-d H:i:s',$val['end_time']);
            $list[$key]['time'] = date('Y-m-d H:i:s',$val['uptime']);
        }
        return $list;
    }

    /**
     * 订单页排序
     * @param $ordertype
     * @param $order
     * @return array
     */
    public function getPaixuInfo($ordertype,$order){
        //公司id 1 ERP单号 2 装修订单编号 3 接单时间 4 量房时间 5 开工日期
        switch($ordertype){
            case 1:
                if($order==1){
                    $dataOrder[1] = 2;
                    $orderStr = 't3.order_no';
                }else{
                    $dataOrder[1] = 1;
                    $orderStr = 't3.order_no desc';
                }
                break;
            case 2:
                if($order==1){
                    $dataOrder[2] = 2;
                    $orderStr = 't3.qz_order';
                }else{
                    $dataOrder[2] = 1;
                    $orderStr = 't3.qz_order desc';
                }
                break;
            case 3:
                if($order==1){
                    $dataOrder[3] = 2;
                    $orderStr = 't3.reception_time';
                }else{
                    $dataOrder[3] = 1;
                    $orderStr = 't3.reception_time desc';
                }
                break;
            case 4:
                if($order==1){
                    $dataOrder[4] = 2;
                    $orderStr = 't3.follow_time';
                }else{
                    $dataOrder[4] = 1;
                    $orderStr = 't3.follow_time desc';
                }
                break;
            case 5:
                if($order==1){
                    $dataOrder[5] = 2;
                    $orderStr = 't3.add_time';
                }else{
                    $dataOrder[5] = 1;
                    $orderStr = 't3.add_time desc';
                }
                break;
        }

        return array('order'=>$orderStr,'assign'=>$dataOrder);
    }


    public function getPaixuOrderInfo($ordertype,$order){
        //公司id 1 ERP单号 2 装修订单编号 3 分单时间 4 接单时间 5 量房时间
        switch($ordertype){
            case 1:
                if($order==1){
                    $dataOrder[1] = 2;
                    $orderStr = 't3.order_no';
                }else{
                    $dataOrder[1] = 1;
                    $orderStr = 't3.order_no desc';
                }
                break;
            case 2:
                if($order==1){
                    $dataOrder[2] = 2;
                    $orderStr = 't3.qz_order';
                }else{
                    $dataOrder[2] = 1;
                    $orderStr = 't3.qz_order desc';
                }
                break;
            case 3:
                if($order==1){
                    $dataOrder[3] = 2;
                    $orderStr = 't3.reception_time';
                }else{
                    $dataOrder[3] = 1;
                    $orderStr = 't3.reception_time desc';
                }
                break;
            case 4:
                if($order==1){
                    $dataOrder[4] = 2;
                    $orderStr = 't3.follow_time';
                }else{
                    $dataOrder[4] = 1;
                    $orderStr = 't3.follow_time desc';
                }
                break;
            case 5:
                if($order==1){
                    $dataOrder[5] = 2;
                    $orderStr = 't3.add_time';
                }else{
                    $dataOrder[5] = 1;
                    $orderStr = 't3.add_time desc';
                }
                break;
        }

        return array('order'=>$orderStr,'assign'=>$dataOrder);
    }
    /**
     * 账号页排序
     * @param $ordertype
     * @param $order
     * @return array
     */
    public function getCompanyPaixuInfo($ordertype,$order){
        //公司id 1 有效开始时间 2 结束时间3 总天数 4 总天数 5 剩余天数 6订单
        switch($ordertype){
            case 1:
                if($order==1){
                    $dataOrder[1] = 2;
                    $orderStr = 't2.id desc';
                }else{
                    $dataOrder[1] = 1;
                    $orderStr = 't2.id';
                }
                break;
            case 2:
                if($order==1){
                    $dataOrder[2] = 2;
                    $orderStr = 't2.start_tm desc';
                }else{
                    $dataOrder[2] = 1;
                    $orderStr = 't2.start_tm';
                }
                break;
            case 3:
                if($order==1){
                    $dataOrder[3] = 2;
                    $orderStr = 't2.end_tm desc';
                }else{
                    $dataOrder[3] = 1;
                    $orderStr = 't2.end_tm';
                }
                break;
            case 4:
                if($order==1){
                    $dataOrder[4] = 2;
                    $orderStr = 'sum_day desc';
                }else{
                    $dataOrder[4] = 1;
                    $orderStr = 'sum_day';
                }
                break;
            case 5:
                if($order==1){
                    $dataOrder[5] = 2;
                    $orderStr = 'remain_day desc';
                }else{
                    $dataOrder[5] = 1;
                    $orderStr = 'remain_day';
                }
                break;
            case 6:
                if($order==1){
                    $dataOrder[6] = 2;
                    $orderStr = 'order_number desc';
                }else{
                    $dataOrder[6] = 1;
                    $orderStr = 'order_number';
                }
                break;
        }
        return array('order'=>$orderStr,'assign'=>$dataOrder);
    }

    /**
     * 审核页排序
     * @param $ordertype
     * @param $order
     * @return array
     */
    public function getOrderTimeInfo($ordertype,$order){
        //公司id 1 有效开始时间 2 结束时间3 总天数 4 剩余天数 5
        switch($ordertype){
            case 1:
                if($order==1){
                    $dataOrder[1] = 2;
                    $orderStr = 't2.id desc';
                }else{
                    $dataOrder[1] = 1;
                    $orderStr = 't2.id';
                }
                break;
            case 2:
                if($order==1){
                    $dataOrder[2] = 2;
                    $orderStr = 't2.start_tm desc';
                }else{
                    $dataOrder[2] = 1;
                    $orderStr = 't2.start_tm';
                }
                break;
            case 3:
                if($order==1){
                    $dataOrder[3] = 2;
                    $orderStr = 't2.end_tm desc';
                }else{
                    $dataOrder[3] = 1;
                    $orderStr = 't2.end_tm';
                }
                break;
            case 4:
                if($order==1){
                    $dataOrder[4] = 2;
                    $orderStr = 't2.sum_day desc';
                }else{
                    $dataOrder[4] = 1;
                    $orderStr = 't2.sum_day';
                }
                break;
            case 5:
                if($order==1){
                    $dataOrder[5] = 2;
                    $orderStr = 't2.create_time';
                }else{
                    $dataOrder[5] = 1;
                    $orderStr = 't2.create_time desc';
                }
                break;
        }
        return array('order'=>$orderStr,'assign'=>$dataOrder);
    }



    /**
     * 获取所有订单列表
     */
    public function getOrdersList($post,$order,$count){
        if($count>0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = D('Home/Db/YxbAccount')->getOrdersList($post,$order,$p->firstRow, $p->listRows);
            foreach($list as $key=>$val){
                $list[$key]["source"] = $this->orderSource[$val["source"]];
                $list[$key]["build_state"] = $this->shiGongStatus[$val["build_state"]];
                $list[$key]["state"] = $this->orderStatus[$val["state"]];
                if(!empty($val["add_time"])&&isset($val["add_time"])){
                    $list[$key]["add_time"] = date('Y-m-d H:i',$val["add_time"]); //分单时间
                }else{
                    $list[$key]["add_time"] = '';
                }

                if(!empty($val["reception_time"])&&isset($val["reception_time"])){
                    $list[$key]["reception_time"] = date('Y-m-d H:i',$val["reception_time"]); //接单时间
                }else{
                    $list[$key]["reception_time"] = '';
                }

                if(!empty($val["follow_time"])&&isset($val["follow_time"])){
                    $list[$key]["follow_time"] = date('Y-m-d H:i',$val["follow_time"]); //量房时间
                }else{
                    $list[$key]["follow_time"] = '';
                }
            }

            //处理续费数据
            return ['list' => $list, 'page' => $show];
        }
    }

    /**
     * 获取公司的订单列表
     */
    public function getOrdersAccountList($post,$order,$count){
        if($count>0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = D('Home/Db/YxbAccount')->getOrdersAccountList($post,$order,$p->firstRow, $p->listRows);
            foreach($list as $key=>$val){
                $list[$key]["source"] = $this->orderSource[$val["source"]];
                $list[$key]["build_state"] = $this->shiGongStatus[$val["build_state"]];
                $list[$key]["state"] = $this->orderStatus[$val["state"]];
                if(!empty($val["add_time"])&&isset($val["add_time"])){
                    $list[$key]["add_time"] = date('Y-m-d H:i',$val["add_time"]); //分单时间
                }else{
                    $list[$key]["add_time"] = '';
                }

                if(!empty($val["reception_time"])&&isset($val["reception_time"])){
                    $list[$key]["reception_time"] = date('Y-m-d H:i',$val["reception_time"]); //接单时间
                }else{
                    $list[$key]["reception_time"] = '';
                }

                if(!empty($val["follow_time"])&&isset($val["follow_time"])){
                    $list[$key]["follow_time"] = date('Y-m-d H:i',$val["follow_time"]); //量房时间
                }else{
                    $list[$key]["follow_time"] = '';
                }
            }

            //处理续费数据
            return ['list' => $list, 'page' => $show];
        }
    }

    /**
     * 获取所有订单数量
     * @param $where
     * @return mixed
     */
    public function getOrdersCount($where){
        return D('Home/Db/YxbAccount')->getOrdersCount($where);
    }

    /**
     * 获取账号所属订单数量
     * @param $where
     * @return mixed
     */
    public function getOrdersAccountCount($where){
        return D('Home/Db/YxbAccount')->getOrdersAccountCount($where);
    }

    /**
     * 获取单条订单记录
     * @param $id
     */
    public function getOrderErpInfo($id,$company_id){

        $info = D('Home/Db/YxbAccount')->getOrderErpInfo($id,$company_id);
        if(!empty($info["source"])&&isset($info["source"])){
            $info["source"]  =  $this->orderSource[$info["source"]];
        }
        if(!empty($info["type_fw"])&&isset($info["type_fw"])){
            $info["type_fw"]  =  $this->orderType[$info["type_fw"]];
        }else{
            $info["type_fw"]  =  '';
        }
        if(!empty($info["area"])&&isset($info["area"])){
            $info["area"] = $info["area"] .'m²';
        }else{
            $info["area"] = '';
        }
        if(!empty($info["publish_time"])&&isset($info["publish_time"])){
            $info["publish_time"] = date("Y-m-d H:i",$info["publish_time"]);
        }else{
            $info["publish_time"] = '';
        }
        if(!empty($info["add_time"])&&isset($info["add_time"])){
            $info["add_time"] = date("Y-m-d H:i",$info["add_time"]);
        }else{
            $info["publish_time"] = '';
        }

        if(!empty($info["reception_time"])&&isset($info["reception_time"])){
            $info["reception_time"] = date("Y-m-d H:i",$info["reception_time"]);
        }else{
            $info["reception_time"] = '';
        }

        if(!empty($info["jiedan_time"])&&isset($info["jiedan_time"])){
            $info["jiedan_time"] = date("Y-m-d H:i",$info["jiedan_time"]);
        }else{
            $info["jiedan_time"] = '';
        }
        if(!empty($info["pname"])&&!empty($info["cname"])){
            if(!empty($info["aname"])){
                $info["pcname"] = $info["pname"].'/'.$info["cname"].'/'.$info["aname"];
            }else{
                $info["pcname"] = $info["pname"].'/'.$info["cname"];
            }

        }

        if(!empty($info['consumer_tel'])&&isset($info['consumer_tel'])){
            if(strlen($info['consumer_tel']) > 4){
                $info['consumer_tel']=  $this->substr_cut($info['consumer_tel'],3,1);
            }
        }

        if(!empty($info['consumer_wx_no'])&&isset($info['consumer_wx_no'])){
            if(strlen($info['consumer_wx_no']) > 4){
                $info['consumer_wx_no'] =  $this->substr_cut($info['consumer_wx_no'],2,2);
            }
        }

        return $info;
    }

    /**
     * 获取订单日志信息
     * @return array
     */
    public function getorderLog($order_no){
        $log["jiedan"] = D('Home/Db/YxbAccount')->getJieDanLog($order_no);
        foreach($log["jiedan"] as $key=>$val){
            $log["jiedan"][$key]["add_time"] =  date('Y-m-d H:i:s',$val['add_time']);
            $log["jiedan"][$key]["state"] =  '已接单';
        }

        $log["gengdan"] = D('Home/Db/YxbAccount')->getGengDanLog($order_no);
        foreach($log["gengdan"] as $key=>$val){
            $log["gengdan"][$key]["add_time"] =  date('Y-m-d H:i:s',$val['add_time']);
            $log["gengdan"][$key]["state"] =  $this->orderStatus[$val['state']];
        }

        $log["qiandan"] = D('Home/Db/YxbAccount')->getQianDanLog($order_no);
        foreach($log["qiandan"] as $key=>$val){
            $log["qiandan"][$key]["add_time"] =  date('Y-m-d H:i:s',$val['add_time']);
            $log["qiandan"][$key]["state"] =  '已签单';
        }

        //施工信息
        $result = D('Home/Db/YxbAccount')->getShigongLog($order_no);
        foreach($result as $val){
            $log['shigong'][$val['id']]['company_id'] = $val['company_id'];
            $log['shigong'][$val['id']]['order_no'] = $val['order_no'];
            $log['shigong'][$val['id']]['state'] = $this->shiGongStatus[$val['state']];
            $log['shigong'][$val['id']]['remark'] = $val['remark'];
            $log['shigong'][$val['id']]['add_time'] = date('Y-m-d H:i:s',$val['add_time']);
            $log['shigong'][$val['id']]['times'] = $val['times'];
            $log['shigong'][$val['id']]['build_group'] = $val['build_group'];
            $log['shigong'][$val['id']]['check_state'] = $val['check_state'];
            $log['shigong'][$val['id']]['contact_name'] = $val['contact_name'];
            $log['shigong'][$val['id']]['station'] = $val['station'];
            $log['shigong'][$val['id']]['list'][] = $val;
        }
        //施工log信息
        $log['oneshigong'] =  D('Home/Db/YxbAccount')->getShigongLogTwo($order_no);
        $log['oneshigong'][0]["add_time"] = isset($log['oneshigong'][0]["add_time"])? date('Y-m-d H:i:s',$log['oneshigong'][0]["add_time"]):'';
        $log["shouwei"] = D('Home/Db/YxbAccount')->getShouweiLog($order_no);
        foreach($log["shouwei"] as $key=>$val){
            $log["shouwei"][$key]["add_time"] =  date('Y-m-d H:i:s',$val['add_time']);
            $log["shouwei"][$key]["state"] =  '收尾';
        }

        $log["finish"] = D('Home/Db/YxbAccount')->getFinishLog($order_no);
        foreach($log["finish"] as $key=>$val){
            $log["finish"][$key]["add_time"] =  date('Y-m-d H:i:s',$val['add_time']);
            $log["finish"][$key]["state"] =  '完成';
        }

        return $log;
    }

    //获取订单状态阶段最后一个状态
    public function getorderStatusRange(){
        return $this->orderStatusRange;
    }

    /**
     * 获取erp装修公司员工数据
     * @param $company_id
     * @return array
     */
    public function getErpAccountList($company_id)
    {
        $where = [
            'a.company_id' => ['eq', $company_id],
            'a.class_type' => ['eq', 2],
            'a.status' => ['eq', 1],
            'a.is_del' => ['eq', 1],
        ];
        $count = D('Home/Db/YxbAccount')->getAccountListCount($where);
        $where['a.status'] = ['eq',1];
        $on_count = D('Home/Db/YxbAccount')->getAccountListCount($where);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $page = new \Page($count, 10);
            $show = $page->show();
            $list = D('Home/Db/YxbAccount')->getAccountList($where, $page->firstRow, $page->listRows);
            foreach ($list as $k=>$v){
                $list[$k]['contact_tel'] = $this->substr_cut($v['contact_tel'],3,1);
                if(strlen($v['contact_wx']) > 4){
                    $list[$k]['contact_wx'] = $this->substr_cut($v['contact_wx'],2,2);
                }
            }
            return ['page' => $show, 'list' => $list, 'total' => $on_count];
        }
    }

    /**
     * 获取erp装修公司施工组数据
     * @param $company_id
     * @return array
     */
    public function getErpGroupList($company_id)
    {
        $where = [
            'wg.company_id' => ['eq', $company_id],
            'wg.is_del' => ['eq', 1],
        ];
        $list = D('Home/Db/YxbAccount')->getErpGroupList($where);
        $returnData = [];
        foreach ($list as $k => $v) {
            $v['contact_tel'] = $this->substr_cut($v['contact_tel'], 3, 1);
            $v['contact_wx'] = strlen($v['contact_wx']) > 4 ? $this->substr_cut($v['contact_wx'], 2, 2) : $v['contact_wx'];
            $v['woker_tel'] = $this->substr_cut($v['woker_tel'], 3, 1);
            $v['woker_wx'] = strlen($v['woker_wx']) > 4 ? $this->substr_cut($v['woker_wx'], 2, 2) : $v['woker_wx'];
            $returnData[$v['gid']]['gid'] = $v['gid'];
            $returnData[$v['gid']]['group_name'] = $v['group_name'];
            $returnData[$v['gid']]['manager_worktype'] = $v['manager_worktype'];
            $returnData[$v['gid']]['status'] = $v['status'];
            $returnData[$v['gid']]['contact_name'] = $v['contact_name'];
            $returnData[$v['gid']]['contact_tel'] = $v['contact_tel'];
            $returnData[$v['gid']]['contact_wx'] = $v['contact_wx'];
            $returnData[$v['gid']]['account'] = $v['account'];
            $returnData[$v['gid']]['image'] = $v['image'];
            $returnData[$v['gid']]['worker_count']++;
            $returnData[$v['gid']]['worker'][] = $v;
            unset($list[$k]);
        }
        return $returnData;
    }

     /**
     * 添加默认工种数据
     * @param $company_id
     * @return array
     */
    private function getWorkerSave($company_id)
    {
        /**
         * name:    工种名称
         * default: 固定数据,不可删除 1.是 2.否
         */
        $worker = [
            ['name' => '水电工', 'default' => 1,'default_rule'=>1],
            ['name' => '瓦工', 'default' => 1,'default_rule'=>2],
            ['name' => '木工', 'default' => 1,'default_rule'=>3],
            ['name' => '油漆工', 'default' => 1,'default_rule'=>4],
        ];
        $save = [];
        foreach ($worker as $k => $v) {
            $save[] = [
                'name' => $v['name'],
                'company_id' => $company_id,
                'create_time' => time(),
                'update_time' => time(),
                'op_uid' => 0,
                'default' => $v['default'],
                'default_rule'=>$v['default_rule']
            ];
        }
        return $save;
    }

    /**
     * 添加默认岗位数据
     * @param $company_id
     * @return array
     */
    private function getStationSave($company_id)
    {
        /**
         * name:            岗位名称
         * default_rule:    固定角色: 1.设计师 2.项目经理 3.接待客服 4.施工工人
         * status:          状态:1.启用 2.禁用
         * default:         固定数据,不可删除 : 1.是 2.否
         */
        $station = [
            ['name' => '设计师', 'default_rule' => 1,'status'=>1,'default'=>1],
            ['name' => '项目经理', 'default_rule' => 2,'status'=>1,'default'=>1],
            ['name' => '接待客服', 'default_rule' => 3,'status'=>1,'default'=>1],
            ['name' => '施工工人', 'default_rule' => 4,'status'=>1,'default'=>1],
        ];
        $save = [];
        foreach ($station as $k => $v) {
            $save[] = [
                'name' => $v['name'],
                'company_id' => $company_id,
                'create_time' => time(),
                'update_time' => time(),
                'status' => $v['status'],
                'op_uid' => 0,
                'default' => $v['default'],
                'default_rule' => $v['default_rule'],
            ];
        }
        return $save;
    }

    public function getStationRuleSave($company_id){
       //获取固定岗位的id
        $where['name'] = ['in',['设计师','项目经理','接待客服']];
        $where['company_id'] = ['eq',$company_id];
        $list = D("Home/Db/YxbAccount")->getStationIdByname($where);
        //固定角色: 1.设计师 2.项目经理 3.接待客服
        $save = [];
        foreach($list as $key=>$val){
            $save[$key]['company_id'] = $company_id;
            $save[$key]['station_id'] = $val['id'];
            if($val['name'] == '设计师'){
                $save[$key]['default_rule'] = 1;
            }else if($val['name'] == '项目经理'){
                $save[$key]['default_rule'] = 2;
            }else{
                $save[$key]['default_rule'] = 3;
            }
        }
        return $save;
    }

    /**
     * 添加默认订单来源数据
     * @param $company_id
     * @return array
     */
    private function getOrdersourceSave($company_id)
    {
        /**
         * name:            订单来源名称
         * default_rule:    固定来源: 1.齐装订单
         * status:          状态:1.启用 2.禁用
         * default:         固定数据,不可删除 : 1.是 2.否
         */
        $ordersource = [
            ['name' => '齐装订单', 'default_rule' => 1,'status'=>1,'default'=>1],
            ['name' => '批量添加', 'default_rule' => 3,'status'=>1,'default'=>1],
            ['name' => '朋友圈分享', 'default_rule' => 4,'status'=>1,'default'=>1]
        ];
        $save = [];
        foreach ($ordersource as $k => $v) {
            $save[] = [
                'name' => $v['name'],
                'company_id' => $company_id,
                'create_time' => time(),
                'update_time' => time(),
                'status' => $v['status'],
                'op_uid' => 0,
                'default' => $v['default'],
                'default_rule' => $v['default_rule'],
            ];
        }
        return $save;
    }

    public function getButtonType($btype){

        if(!empty($btype)&&isset($btype)){
            $list = [
                2=>[1,2,3,4,5,6,7,8],
                3=>[9],
                4=>[11],
                6=>[10,12]
            ];
            return $list[$btype];
        }
    }

    /**
     * 更改状态名称
     */
    public function changeHandleStatus($data){
        foreach($data as $key=>$val){
            switch ($val['handle_status']) {
                case 1:
                    $data[$key]['status_handle'] = '待处理';
                    break;
                case 2:
                    $data[$key]['status_handle'] = '处理中';
                    break;
                case 3:
                    $data[$key]['status_handle'] = '已处理';
                    break;
                default:
                    break;
            }
        }
        return $data;
    }

    /**
     * 意见反馈处理提交保存
     */
    public function saveFeedbackHandle($data){
        $getRequert = D('Home/Db/YxbAccount')->saveFeedbackHandle($data);
        if($getRequert){
            return ['status' => 1, 'info' => '操作成功'];
        }else{
            return ['status' => 0, 'info' => '操作失败'];
        }
    }

    /**
     * 获取意见反馈信息
     */
    public function getFeedbackById($param){
        $model = D('Home/Db/YxbAccount');
        $feedbackinfo = $model->getFeedbackById($param);
        if($feedbackinfo['handle_status'] == 1){
            $feedbackinfo['status_name'] = '待处理';
        }elseif($feedbackinfo['handle_status'] == 2){
            $feedbackinfo['status_name'] = '处理中';
        }else{
            $feedbackinfo['status_name'] = '已处理';
        }
        return $feedbackinfo;
    }

}
