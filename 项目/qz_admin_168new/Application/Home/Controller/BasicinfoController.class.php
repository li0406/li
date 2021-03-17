<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2018/10/22
 * Time: 13:55
 */
//ERP装修公司管理
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Common\Enums\RbacRoleEnum;
use Common\Enums\ApiConfig;

use Home\Model\AreaModel;
use Home\Model\Logic\AuthLogicModel;
use Home\Model\Logic\CompanyLogicModel;
use Home\Model\Logic\DesignLogicModel;
use Home\Model\Logic\LogAdminLogicModel;
use Home\Model\Logic\QuyuLogicModel;
use Home\Model\Logic\AdminuserLogicModel;

use Home\Model\Service\MsgServiceModel;

class BasicinfoController extends HomeBaseController{
    //城市区域管理
    public function index(){
        $fen = trim(I('get.fen'));
        $ptype = trim(I('get.ptype'));
        $dujia = trim(I('get.dujia'));
        //智能分单城市
        if($fen == 1){
            $map['a.fen_type'] = 2; //智能分单
        }
        if($ptype == 1){
            $map['c.type'] = 1; // 南方
        }
        if($dujia == 1){
            $map['a.is_exclusive'] = 1;
        }

        $result = D('Home/Db/Adminquyu')->getList($map);
        $this->assign("fen",$fen);
        $this->assign("ptype",$ptype);
        $this->assign("quyuList",$result);
        $this->display();
    }

    //增加/编辑城市
    public function edit(){
        $id = trim(I('get.id'));
        if(!empty($id)){
            $quyuInfo  = D('Home/Db/Adminquyu')->getQuyu(array('cid'=>$id));
            $this->assign("quyuInfo",$quyuInfo[0]);
        }

        $provinceList = D('quyu')->getProvince(); // 省份
        $level = D("Home/Logic/QuyuLogic")->getlittle(); //城市级别
        $quyuList = D("Quyu")->getAllQuyuOnly("cid,cname");

        $this->assign("level", $level);
        $this->assign("quyuList", $quyuList);
        $this->assign("provinceList", $provinceList);
        $this->display();
    }

    public function editcity(){
        if($_POST){
            $post = I("post.");
            //城市信息
            $data['cname']        = trim($_POST['cname']);
            $data['plate']        = trim($_POST['plate']);
            $data['map_name']     = trim($_POST['map_name']);
            $data['little']       = trim($_POST['little']);
            $data['is_open_city'] = $_POST['is_open_city'];
            $data['manager']      = trim($_POST['manager']);
            $data['fen_type']      = $_POST['fen_type'];
            $data['mark_red']      = $_POST['mark_red'];
            $data['type']         = '1';
            $data['is_exclusive']  = $_POST['exclusive'];

            $data['relation_cs'] = I("post.relation_cs");
            if (!empty($data['relation_cs'])) {
                $data['relation_qx'] = I("post.relation_qx", 0, "intval");
                $data['is_relation'] = 1;
            } else {
                $data['relation_qx'] = 0;
                $data['is_relation'] = 2;
            }

            if(!empty($post["id"])){
                //编辑
                $map['action'] = 'add_city';
                $quyuInfo  = D('Home/Db/Adminquyu')->getQuyu(array('cid'=>$post["id"]));
                $result = D("Home/Db/Adminquyu")->editQuyu($post["id"],$data);
                if($result){
                    /*S-修改城市管辖权限*/
                    $manager = $quyuInfo[0]['manager'];
                    if ($manager != $data['manager']) {
                        // 从外销变为商务
                        if ($manager == 1 && $data['manager'] != 1) {
                            $this->editManager($post["id"], 2);
                            //从商务变为外销
                        } elseif ($manager != 1 && $data['manager'] == 1) {
                            $this->editManager($post["id"], 1);
                        }
                    }
                    /*E-修改城市管辖权限*/
                }

                $data['cid'] = $post["id"];
            }else{
                //添加
                //判断区划代码或BM是否存在
                $data['cid']          = trim($_POST['cid']);
                $data['bm']           = trim($_POST['bm']);
                $data['px_abc']       = substr($data['bm'],0,1).'099';

                $map['bm'] = $data['bm'];
                $map['cid'] = $data['cid'];
                $map['_logic'] = 'or';
                $isHave  = D('Home/Db/Adminquyu')->getQuyu($map);
                if($isHave){
                    $this->ajaxReturn(array('status'=>1,'info'=>'该城市或二级域名或区划代码已存在，增加失败！'));
                }
                $data['uid']          = trim($_POST['provinceid']);
                $data['px']           = substr($data['uid'], 0, 2).'99';

                $result = D("Home/Logic/QuyuLogic")->addQuyu($data);
                $map['action'] = 'add_city';
            }

            //修改省份信息
            $province['type'] = $_POST['province_type'];
            $data['uid'] = isset($data['uid'])?$data['uid']:trim($_POST['province']);
            D("Home/Logic/QuyuLogic")->editProvince($province,$data['uid']);

            $map['id'] = $data['cid'];
            D('Home/Db/Adminquyu')->addLog($map,$data); //打日志

            // 新开站提醒
            if ($data['is_open_city'] == 1) {
                $userIds = [];
                $adminuserLogicModel = new AdminuserLogicModel();

                // 如果是新增开站城市或者原城市未开站变为已开站提醒质检
                if (empty($quyuInfo) || empty($quyuInfo['is_open_city'])) {
                    // 获取需要通知的用户ID
                    $zhijianRoleIds = RbacRoleEnum::getNewOpenCityNoticeRoles();
                    $userzjNoticeList = $adminuserLogicModel->getUserListByUid($zhijianRoleIds);
                    if (!empty($userzjNoticeList)) {
                        $userIds = array_column($userzjNoticeList, "id");
                    }
                }

                // 如果是新增开站城市提醒客服
                if (empty($quyuInfo)) {
                    // 获取需要通知的用户ID
                    $kfRoleIds = RbacRoleEnum::getNewOpenCityNoticeKfRoles();
                    $userkfNoticeList = $adminuserLogicModel->getUserListByUid($kfRoleIds);
                    if (!empty($userkfNoticeList)) {
                        $userkfIds = array_column($userkfNoticeList, "id");
                        $userIds = array_merge($userIds, $userkfIds);
                    }
                }

                // 发送提醒
                if (count($userIds) > 0) {
                    $msgService = new MsgServiceModel();
                    $replaceParam = array($data["cname"]);
                    $msgService->sendSystemMsg("202005121018", $userIds, "?", $data["cid"], "城市管理", $replaceParam);
                }
            }

            $this->ajaxReturn(array('status'=>0,'info'=>"操作成功"));
        }
    }

    /**
     * [editManager 编辑城市管辖]
     * @param  integer $cid     [城市ID]
     * @param  integer $operate [操作，1:将城市管辖改为外销，2:将城市管辖改为商务]
     * @return [type]           [description]
     */
    public function editManager($cid = 0, $operate = 0){
        if (empty($cid) || empty($operate)) {
            return false;
        }
        // 1:将城市管辖改为外销
        if ($operate == 1) {
            // 去除商务部的城市
            D('Home/Db/Adminuser')->delUserCitysByDepartment($cid,6);
            // 给外销经理，外销助理增加城市
            D('Home/Db/Adminuser')->addUserCitysByUid(['59','67'],[$cid]);
            // 2:将城市管辖改为商务
        } elseif ($operate == 2) {
            // 去除外销部的城市
            D('Home/Db/Adminuser')->delUserCitysByDepartment($cid,5);
            // 给商务经理，商务助理增加城市
            D('Home/Db/Adminuser')->addUserCitysByUid(['39','45'],[$cid]);
        }
        return true;
    }
    
    // g.1.5.16 增加关联城市管理
    public function relationcity(){
        $areaAll = D("Quyu")->getAllArea();

        $quyuList = D("Quyu")->getAllQuyuOnly("cid,cname,is_open_city");
        $quyuList = array_column($quyuList, null, "cid");

        $areaGroup = [];
        foreach ($areaAll as $key => $area) {
            $areaGroup[$area["fatherid"]][$area["qz_areaid"]] = $area;
        }

        $cid = I("cid");
        $relation_cs = I("relation_cs");
        $relation_qx = I("relation_qx");
        $is_relation = I("is_relation");
        $is_exclusive = I("is_exclusive");
        $list = D("Quyu")->getRelationCityList($cid, $is_exclusive, $relation_cs, $relation_qx, $is_relation);

        $this->assign("list", $list);
        $this->assign("quyuList", $quyuList);
        $this->assign("areaGroup", $areaGroup);
        $this->assign("is_exclusive", $is_exclusive);
        $this->assign("relation_cs", $relation_cs);
        $this->assign("relation_qx", $relation_qx);
        $this->assign("is_relation", $is_relation);
        $this->assign("cid", $cid);
        $this->display();
    }

    /**
     * 修改关联城市
     * @return [type] [description]
     */
    public function editRelationcity(){
        $resp = sys_rt("ok", 1);

        $cid = I("post.cid");
        $relation_cs = I("post.relation_cs");
        $relation_qx = I("post.relation_qx");

        $map = array(
            "cid" => array("EQ", $relation_cs)
        );
        $city = M("Quyu")->where($map)->find();
        if (empty($city)) {
            $resp = sys_rt("关联城市不存在", 2);
        }

        if ($resp["status"] == 1) {
            $map = array(
                "fatherid" => array("EQ", $relation_cs),
                "qz_areaid" => array("EQ", $relation_qx)
            );
            $area = M("area")->where($map)->find();
            if (empty($area)) {
                $resp = sys_rt("关联区域不存在", 2);
            }
        }

        if ($resp["status"] == 1) {
            $map = array("cid" => array("EQ", $cid));
            $quyu = M("quyu")->where($map)->find();
            if (empty($quyu)) {
                $resp = sys_rt("城市不存在", 2);
            } else if ($quyu["is_relation"] == 1 && $quyu["relation_qx"] == $relation_qx) {
                $resp = sys_rt("城市已经与该区域关联无需重复操作", 2);
            }
        }

        if ($resp["status"] == 1) {
            $data = array(
                "is_relation" => 1,
                "relation_cs" => $relation_cs,
                "relation_qx" => $relation_qx
            );
            $result = D("Quyu")->editQuyu($cid, $data);
            if (empty($result)) {
                $resp = sys_rt("修改失败", 2);
            }
        }

        if ($resp["status"] == 1) {
            $resp = sys_rt("ok", 1, array(
                "is_open_city" => $city["is_open_city"],
                "cname" => $city["cname"],
                "area" => $area["qz_area"]
            ));
        }

        $this->ajaxReturn($resp);
    }

    //排序
    public function paixu(){
        $cid = $_GET['id'];
        $quyuInfo  = D('Home/Db/Adminquyu')->getQuyu(array('cid'=>$cid));
        $areaList = D('Home/Db/Adminquyu')->getArea(array('fatherid'=>$cid),'orders','100');
        //dump($areaList);

        if($_POST){

            $ids = $_POST['id'];
            $orders = $_POST['orders'];

            foreach ($ids as $k => $v) {
                $data['orders'] = $orders[$k];
                M("area")->where(array('id'=>$v))->save($data);
                //日志
                $logData[] = array(
                    'id' => $v,'orders'=>$data['orders']
                );
            }
            $map['id'] = $data['qz_areaid'];
            $map['action'] = 'edit_area_orders';
            D('Home/Db/Adminquyu')->addLog($map,$logData); //打日志
            $this->ajaxReturn(array('status'=>0));
        }else{
            $quyuInfo['0']['count'] = count($areaList);
            $this->assign("quyuInfo",$quyuInfo['0']);
            $this->assign("areaList",$areaList);
            $this->assign('title',$quyuInfo['0']['cname'].'区域管理 - ');
            $this->display();
        }

    }
    //区域管理
    public function quyu(){
        $cid = empty(I('get.id'))?I('post.id'):I('get.id');
        $quyuInfo  = D('Home/Db/Adminquyu')->getQuyu(array('cid'=>$cid));
        $areaList = D('Home/Db/Adminquyu')->getArea(array('fatherid'=>$cid),'orders','100');
        if($_POST){
            //取上级邮编
            //取已有区域数
            $areaCount = count($areaList);
            $areaCount = $areaCount + 1;
            $qz_area = $_POST['qz_area'];

            if(!empty($_POST['qz_area_all'])){
                $areas = str_replace(array('，',',','、'),',',$_POST['qz_area_all']);
                $areas = array_unique(array_filter(explode(",",$areas))); //数组形式
                foreach ($areas as $key => $value) {
                    if(!empty($value)){
                        $value = preg_replace('/\s+/', '', $value);
                        $qz_area[] = $value;
                    }
                }
            }
            //获取目前最大的areaid值-start
            $areaList_sort = multi_array_sort($areaList,'qz_areaid',SORT_DESC);
            $max_areaid = $areaList_sort[0]['qz_areaid']+1;
            $areaList_order_sort = multi_array_sort($areaList,'orders',SORT_DESC);
            $max_order = $areaList_order_sort[0]['orders']+1;
            //end
            $areaNumber = count($qz_area);
            for ($i=0; $i <= $areaNumber; $i++) {
                if(empty($qz_area[$i])){
                    continue;
                }
                $data = array();

                if(empty(count($areaList))){
                    $theNumber = str_pad($areaCount,2,"0",STR_PAD_LEFT);
                    $data['qz_areaid'] = $cid.$theNumber;
                    $data['orders'] = intval($areaCount);
                }else{
                    $data['qz_areaid'] = $max_areaid;
                    $data['orders'] = intval($max_order);
                }

                $data['qz_area'] = htmlspecialchars($qz_area[$i]);
                $data['fatherid'] = $cid;

                $result = D('Home/Db/Adminquyu')->addArea($data);
                if (false == $result) {
                    //操作失败可能是区域ID重复的问题，发现桂阳的区域ID不是从1开始算的，故这样修改
                    $maxaid = M('area')->where(['fatherid' => $cid])->order('qz_areaid DESC')->find()['qz_areaid'];
                    if (!empty($maxaid)) {
                        $data['qz_areaid'] = $maxaid + 1;
                        $result = D('Home/Db/Adminquyu')->addArea($data);
                    }
                }
                $map['id'] = $data['qz_areaid'];
                $map['action'] = 'add_area';
                D('Home/db/Adminquyu')->addLog($map,$data); //打日志
                $areaCount++;
                $max_order++;
                $max_areaid++;
            }
            $this->ajaxReturn(array('status'=>0,'info'=>'操作成功'));
            
        }else{
            $quyuInfo['0']['count'] = count($areaList);
            $this->assign("quyuInfo",$quyuInfo['0']);
            $this->assign("areaList",$areaList);
            $this->assign('title',$quyuInfo['0']['cname'].'区域管理 - ');
            $this->display();
        }
    }

    /**
     * 会员公司管理
     *
     * @return void
     */
    public function company()
    {
        $authLogic = new AuthLogicModel();
        $companyLogic = new CompanyLogicModel();
        $post = I('get.');
        $css = $authLogic->getCitysByAminId(session('uc_userinfo.id'));
        if (!empty($css)) {
            $post['css'] = $css;
            $list = $companyLogic->getTrueCompany($post);
            //获取管辖城市小区信息
            $citys = $authLogic->getCityArray('', true, session('uc_userinfo.id'));
        } elseif (session('uc_userinfo.uid') == 1) {
            //获取管辖城市小区信息
            $citys = $authLogic->getCityArray('', true);
            $list = $companyLogic->getTrueCompany($post);
        }
        $this->assign('citys', isset($citys) ? $citys : []);
        $this->assign('list', isset($list['list']) ? $list['list'] : []);
        $this->assign('page', isset($list['page']) ? $list['page'] : '');
        $this->display();
    }

    public function syncarea()
    {
        $companyModel = new \Home\Model\Db\CompanyModel();
        $list = $companyModel->getCompanyList([],null, null);
        echo '同步派单区域，共'.count($list).'条数据';
        $i = 0;
        foreach ($list as $value) {
            if ($value['cs']) {
                $quyuLogic = new QuyuLogicModel();
                $arealist = $quyuLogic->getArea($value['cs']);
                $areaStr = implode(',',array_filter(array_column($arealist,'areaid')));
                $old = $companyModel->getCompanyAreaCount(['company_id' => $value['id']]);
                if ($old == 0) {
                    $areaArray = array_map(function ($val) use ($value) {
                        return [
                            'company_id' => $value['id'],
                            'area_id' => $val,
                            'create_time' => time(),
                        ];
                    }, explode(',', $areaStr));
                    $companyModel->saveCompanyArea($areaArray);
                    $i++;
                }
            }
        }
        echo '同步派单区域结束,共'.$i.'条数据成功';
    }
    /**
     * 获取编辑公司数据
     *
     * @return void
     */
    public function getvipcompany()
    {
        if ($_POST) {
            $id = I('post.id');
            $companyLogic = new CompanyLogicModel();
            $data = $companyLogic->getvipcompany($id);
            $data['adjacent_cid'] = trim($data['adjacent_cid'], ',');
            $quyuLogic = new QuyuLogicModel();
            $allCity = trim($data['cs'].','.$data['adjacent_cid'], ',');
            $data['adjacent_citys'] = $quyuLogic->getAdjacentWithArea($allCity);
            if (!empty($data)) {
                $this->ajaxReturn(array('status' => 0, 'data' => $data));
            } else {
                $this->ajaxReturn(array('status' => 1, 'info' => '操作失败'));
            }
        }
    }

    /**
     * 保存公司信息
     *
     * @return void
     */
    public function savevipcompany()
    {
        if ($_POST) {
            $post = I('post.');
            $id = $post['id'];
            $user['cs'] = $post['model-city'];
            $user['qx'] = $post['model-area'];
            $user['dz'] = $post['dz'];
            $company['contract'] = $post['qb'];
            $company['lx'] = $post['zx'];
            $company['other_id'] = $post['other_id'];
            $company['get_gift_order'] = $post['get_gift_order'];

            //经纬度不能为空
            $post['jingwei'] = str_replace(array('，', ',', '、'), ',', $post['jingwei']);
            $jingwei = explode(',', $post['jingwei']);
            if (empty($jingwei[0]) || empty($jingwei[1])) {
                $this->ajaxReturn(array('info' => '坐标填写错误!', 'status' => 1));
            } else {
                if (count($jingwei) > 2) {
                    $this->ajaxReturn(array('info' => '坐标填写错误!', 'status' => 1));
                }
                $lng = '/^(\-|\+)?(((\d|[1-9]\d|1[0-7]\d|0{1,3})\.\d{0,6})|(\d|[1-9]\d|1[0-7]\d|0{1,3})|180\.0{0,6}|180)$/';
                $lan = '/^(\-|\+)?([0-8]?\d{1}\.\d{0,6}|90\.0{0,6}|[0-8]?\d{1}|90)$/';
                if (!preg_match($lng, $jingwei[0]) || !preg_match($lan, $jingwei[1])) {
                    $this->ajaxReturn(array('info' => '坐标填写错误!', 'status' => 1));
                }
            }
            $company['lng'] = $jingwei[0];
            $company['lat'] = $jingwei[1];
            $company['deliver_area'] = is_string($post['deliver_area']) ? $post['deliver_area'] : implode(',', $post['deliver_area']);
            $company['mianji'] = intval($post['mianji']);
            $company['lxs'] = intval($post['lxs']);
            //对立公司id必须填写已存在的-start
            $other_id = str_replace(array('，', ','), ',', $post['other_id']);
            $other_id = explode(',', $other_id);
            foreach ($other_id as $key => $val) {
                if (empty($val)) {
                    unset($other_id[$key]);
                } else {
                    $other_id[$key] = trim($val);
                }

            }
            $other_now = count($other_id);
            $companyLogic = new CompanyLogicModel();
            if ($other_now > 0) {
                if (in_array($id, $other_id)) {
                    $this->ajaxReturn(array('status' => 1, 'info' => '不能输入本公司id'));
                }
                $other_exist = $companyLogic->getvipcompanycount($other_id);
                if ($other_now != $other_exist) {
                    $this->ajaxReturn(array('status' => 1, 'info' => '请输入已存在真会员公司id'));
                }
            }

            $result = $companyLogic->savevipcompany($user, $company, $id);

            if ($result) {
                $logLogic = new LogAdminLogicModel();
                $logLogic->addLog('编辑装修公司配置', 'editvipcompany', $company, $id);
                $this->ajaxReturn(array('status' => 0, 'info' => '操作成功'));
            } else {
                $this->ajaxReturn(array('status' => 1, 'info' => '操作失败'));
            }
        }
    }

    /**
     * 模板下载
     */
   public function dowmmodule(){
       //设置表头
       $title = array(
           '会员ID','会员简称','所属城市','所在区域','详细地址','坐标','半包/全包','公装/家装','对立公司ID','所属销售'
       );
       D("Home/Logic/CompanyLogic")->downExcel($title,'会员公司模板');
       die;
   }

    /**
     * 导入数据
     */
    public function companyUploadExcel(){
        //分析Excel内容
        $ex = $_FILES['excel'];
        $filename = TEMP_PATH.'/'.time().substr($ex['name'],stripos($ex['name'],'.'));
        move_uploaded_file($ex['tmp_name'],$filename);
        $excel = importExcel($filename);

        if(count($excel['0'])  != 10 ){
            $this->ajaxReturn(array('data' => '','info' => '数据格式不正确','status' => 'error'));
        }
        unset($excel['0']);
        $lng = '/^(\-|\+)?(((\d|[1-9]\d|1[0-7]\d|0{1,3})\.\d{0,6})|(\d|[1-9]\d|1[0-7]\d|0{1,3})|180\.0{0,6}|180)$/';
        $lan = '/^(\-|\+)?([0-8]?\d{1}\.\d{0,6}|90\.0{0,6}|[0-8]?\d{1}|90)$/';
        //逐行导入数据
        foreach ($excel as $k => $v) {
            if(empty($v)){
                continue;
            }

            //检查经纬度
            $v[5] = str_replace(array(' ','，',',','、'),',',$v[5]);

            $jingwei = explode(',', $v[5]);
            if(!preg_match($lng,$jingwei[0])||!preg_match($lan,$jingwei[1])){
                continue;
            }

            //判断会员公司是否存在
            $city = D("Home/Logic/CompanyLogic")->getvipcompany(trim($v[0]));
            if(empty($city)){
                continue;
            }

            // '会员ID','会员简称1','所属城市2','所在区域3','详细地址4','坐标5','半包/全包6','公装家装7','对立公司ID8','所属销售9'
            $id = $v[0];
            $user["cs"] = D('Home/Db/Adminquyu')->getCityInfo($v[2]);
            $user["qx"] =  D('Home/Db/Adminquyu')->getAreaInfo($v[3]);
            $user["dz"] = trim($v[4]);

            $company['lng'] = $jingwei[0];
            $company['lat'] = $jingwei[1];
            if(trim($v[6]) == '半包'){
                $company['contact'] = 1;
            }else if(trim($v[6]) == '全包'){
                $company['contact'] = 2;
            }else{
                $company['contact'] = 3;
            }
            if(trim($v[7]) == "家装"){
                $company['lx'] = 1;
            }else if(trim($v[7]) == "公装"){
                $company['lx'] = 2;
            }else{
                $company['lx'] = 3;
            }
            $company['other_id'] = $v[8];
            $company['saler'] = $v[9];
            D("Home/Logic/CompanyLogic")->savevipcompany($user,$company,$id);
        }

        $this->ajaxReturn(array('data'=>'','info'=>'操作成功','status'=>0));
    }

    public function editquyu(){
        $id = $_GET['id'];
        $areaInfo = D('Home/db/Adminquyu')->getArea(array('qz_areaid'=>$id));
        if($_POST){
            $data['qz_area'] = trim($_POST['qz_area']);
            $data['orders'] = trim($_POST['orders']);
            //排序不能有冲突
            if($areaInfo[0]['orders'] !=  $data['orders']){
                $count = D('Home/db/Adminquyu')->getExistOrder($areaInfo[0]['fatherid'],$data['orders']);
                if($count>0){
                    $this->ajaxReturn(array('status'=>1,'info'=>'排序冲突'));
                }
            }
             D('Home/db/Adminquyu')->editArea($id,$data);
            $map['id'] = $id;
            $map['action'] = 'edit_area';
            D('Home/db/Adminquyu')->addLog($map,$data); //打日志
            $this->ajaxReturn(array('status'=>0,'info'=>'操作成功'));
        }else{

            $cid = $areaInfo['0']['fatherid'];
            $quyuInfo  = D('Home/db/Adminquyu')->getQuyu(array('cid'=>$cid));
            $areaList = D('Home/db/Adminquyu')->getArea(array('fatherid'=>$cid),'orders','100');

            $quyuInfo['0']['count'] = count($areaList);
            $this->assign("quyuInfo",$quyuInfo['0']);
            $this->assign("areaList",$areaList);
            $this->assign("areaInfo",$areaInfo['0']);
            $this->display();
        }
    }

    //强制更新前台城市缓存 生成 City JSON 文件
    public function updateVipCityData(){
        //更新PC版城市JS文件
        $citys = D("Home/db/Buildcity")->getCityArray();

        $citys = json_encode($citys);
        $content =  "var citys = JSON.parse('".$citys."');";
        $filename = date('YmdHi').'.js';

        $result = $this->uploadContentToQiNiu('common/js/allcity'.$filename,$content);

        $filename = $result['key'];
        $filename = str_replace('common/js/','',$filename);

        $data['option_value'] = $filename;
        $result = M("options")->where(array('option_name' => 'ALL_CITY_JSON'))->save($data);

        //更新移动版城市JS文件
        $citys = D('Home/db/Buildcity')->getRealVipProvinceCityAndArea();
        $citys = 'var rlpca = '.json_encode($citys);

        $filename = 'common/js/rlpca' . date('YmdHis') . '.js';
        $result = $this->uploadContentToQiNiu($filename,$citys);

        $mdata['option_value'] = $filename;
        $result2 =  M("options")->where(array('option_name' => 'ALL_REAL_VIP_PCA_JSON'))->save($mdata);
        //清空OP缓存
        $redis_logic = D('Home/Logic/RedisLogic');
        $redis_logic->del('C:OP:N:ALL_CITY_JSON');
        $redis_logic->del('C:OP:N:ALL_REAL_VIP_PCA_JSON');

        $this->success('更新PC端和移动端城市JS操作成功！');
    }




    //上传内容到七牛
    private function uploadContentToQiNiu($filename,$content){
        import('Library.Org.Qiniu.io', '', '.php');
        import('Library.Org.Qiniu.rs', '', '.php');
        $bucket = OP('QINIU_BUCKET');
        $accessKey = OP('QINIU_AK');
        $secretKey = OP('QINIU_CK');
        Qiniu_SetKeys($accessKey, $secretKey);
        $putPolicy = new \Qiniu_RS_PutPolicy($bucket);
        $putPolicy->SaveKey = $filename;
        $upToken = $putPolicy->Token(null);
        $putExtra = new \Qiniu_PutExtra();
        $putExtra->Crc32 = 1;
        list($ret, $err) = Qiniu_Put($upToken, null, $content, $putExtra);
        if($err == null){
            $result = array(
                "hash"=>$ret["hash"],
                "key"=> $ret["key"]
            );
            return $result;
        }
        return $err;
    }
    //g.1.3.6  新后台管理-信息基础设置-新增设计师管理
    //设计师列表页
    public function design(){
        $designmodel = new DesignLogicModel();

        $cityid = I('get.city');            //城市id
        $area = I('get.area');              //区域id
        $sjsname = I('get.design_name');    //设计师姓名/ID
        $comname = I('get.company_name');   //公司名称
        $truevip = I('get.truevip');        //真/假会员
        $show = I('get.show_home');         //是否首页显示


        //获取城市小区信息
        $citys = D('Home/Logic/AuthLogic')->getCityArray('',true);
        $this->assign("citys",$citys);

        //获取当前城市的所属区域
        $model = new AreaModel();
        $arealist = $model->getCityById($cityid);
        if($arealist){
            $arealist = $arealist['child'];
        }else{
            $arealist = array();
        }
        $this->assign('arealist',$arealist);

        //根据条件获取设计师列表
        $list = $designmodel->getDesignList($cityid,$area,$sjsname,$comname,$truevip,$show);
        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->assign('shi',$cityid);
        $this->assign('area',$area);
        $this->display();
    }

    //设计师添加/编辑页
    public function editdesign(){
        $logic = new DesignLogicModel();
        //将城市排序
        $citys = getUserCitys();
        $this->assign("citys",$citys);
        if(I('get.id')){
            $info = $logic->getDesignInfoById(I('get.id'));
            //如果有cs则去获取所属区域
            if($info['cs']){
                //获取当前城市的所属区域
                $areamodel = new AreaModel();
                $arealist = $areamodel->getCityById($info['cs']);
                if($arealist){
                    $arealist = $arealist['child'];
                }else{
                    $arealist = array();
                }
                $this->assign('arealist',$arealist);
            }
            if(!empty($info['fengge'])){
                $info['fgold'] = 1; //1表示有选择风格
            }
            if(!empty($info['lingyu'])){
                $info['lyold'] = 1; //1表示有选择领域
            }

            $info['fengge'] = explode(',',$info['fengge']);
            $info['lingyu'] = explode(',',$info['lingyu']);
            //风格
            $fengge=[];
            if(!empty($info['fengge'])){
                if(in_array('现代简约',$info['fengge'])){
                    $fengge['s_jy'] = 1;
                }
                if(in_array('欧式风格',$info['fengge'])){
                    $fengge['s_os'] = 1;
                }
                if(in_array('中式风格',$info['fengge'])){
                    $fengge['s_zs'] = 1;
                }
                if(in_array('古典风格',$info['fengge'])){
                    $fengge['s_gd'] = 1;
                }
                if(in_array('田园风格',$info['fengge'])){
                    $fengge['s_ty'] = 1;
                }
                if(in_array('地中海风格',$info['fengge'])){
                    $fengge['s_dzh'] = 1;
                }
                if(in_array('美式风格',$info['fengge'])){
                    $fengge['s_ms'] = 1;
                }
                if(in_array('混搭风格',$info['fengge'])){
                    $fengge['s_hd'] = 1;
                }
                if(in_array('其它',$info['fengge'])){
                    $fengge['s_other'] = 1;
                }
            }
            //领域
            $lingyu = [];
            if(!empty($info['lingyu'])){
                if(in_array('住宅公寓',$info['lingyu'])){
                    $lingyu['f_zz'] = 1;
                }
                if(in_array('写字楼',$info['lingyu'])){
                    $lingyu['f_xzl'] = 1;
                }
                if(in_array('别墅',$info['lingyu'])){
                    $lingyu['f_bs'] = 1;
                }
                if(in_array('专卖展示店',$info['lingyu'])){
                    $lingyu['f_zmd'] = 1;
                }
                if(in_array('酒店宾馆',$info['lingyu'])){
                    $lingyu['f_jd'] = 1;
                }
                if(in_array('餐饮酒吧',$info['lingyu'])){
                    $lingyu['f_cy'] = 1;
                }
                if(in_array('歌舞迪',$info['lingyu'])){
                    $lingyu['f_dwb'] = 1;
                }
                if(in_array('其他',$info['lingyu'])){
                    $lingyu['f_other'] = 1;
                }
                $this->assign('fengge',$fengge);
                $this->assign('lingyu',$lingyu);
//                dump($info);die;
            }
            $this->assign('info',$info);
        }
        $this->display();
    }
    
    
    //添加/编辑设计师
    public function AddOrEditDesign(){
        $logic = new DesignLogicModel();
        $data = I('post.');
        if($data){
            $userinfo = [];
            $desinfo = [];
            $teaminfo = [];
            if(empty($data['id'])){    //表示添加设计师
                if(empty($data['user'])){
                    $return['error_code'] = ApiConfig::ACCOUNT_CANNOT_NULL;
                    $return['error_msg'] = '账号不能为空';
                    $this->ajaxReturn($return);
                }else{
                    //验证账号是否存在
                    $hadaccount = $logic->checkHadAccount($data['user']);
                    if($hadaccount === true){
                        $return['error_code'] = ApiConfig::ACCOUNT_HAD_INTABLE;
                        $return['error_msg'] = '该账号已存在';
                        $this->ajaxReturn($return);
                    }
                }
                if(empty($data['pass'])){
                    $return['error_code'] = ApiConfig::PASSWORD_CANNOT_NULL;
                    $return['error_msg'] = '密码不能为空';
                    $this->ajaxReturn($return);
                }
                $userinfo['user'] = $data['user'];                                          //新增账号
                $userinfo['pass'] = md5($data['pass']);                                     //密码
                $userinfo['logo'] = $data['logo'] ? $data['logo'] : '';                     //设计师头像
                $userinfo['name'] = $data['desginer_name'] ? $data['desginer_name'] : '';   //设计师姓名
                $userinfo['cs'] = $data['cs'] ? $data['cs'] : '';                           //所属城市
                $userinfo['qx'] = $data['qx'] ? $data['qx'] : '';                           //所属区域
                $userinfo['register_time'] = time();                                        //注册时间
                $userinfo['classid'] = 2;                                                   //2表示设计师

                $desinfo['school'] = $data['graduation'] ? $data['graduation'] : '';        //毕业院校
                $desinfo['jobtime'] = $data['job_year'] ? $data['job_year'] :'';            //从业时间
                $desinfo['fengge'] = $data['style'] ? $data['style'] : '';                  //擅长风格
                $desinfo['lingyu'] = $data['skill_at_field'] ? $data['skill_at_field'] :''; //擅长领域
                $desinfo['linian'] = $data['linian'] ? $data['linian'] : '';                //设计理念
                $desinfo['text'] = $data['text'] ? $data['text'] : '';                      //个人简介
                $desinfo['cost'] = $data['cost'] ? $data['cost'] : '';                      //收费标准
                $desinfo['cases'] = $data['cases'] ? $data['cases'] : '';                   //代表作

                if($data['comid']){         //表示选择了匹配公司
                    $teaminfo['zw'] = $data['position'] ? $data['position'] : '';               //职位
                    $teaminfo['xs'] = $data['show_index_page'] ? $data['show_index_page'] : 0; //是否显示
                    $teaminfo['px'] = $data['sort'] ? $data['sort'] : '';                       //排序
                    $teaminfo['zt'] = 2;                                                        //状态 入驻
                    $teaminfo['comid'] = $data['comid'];
                }
                //新增设计师
                $getreturn = $logic->addNewDesign($userinfo,$desinfo,$teaminfo,CONTROLLER_NAME . "/" . ACTION_NAME);
                if($getreturn === true){
                    $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
                    $return['error_msg'] = '保存成功';
                    $this->ajaxReturn($return);
                }else{
                    $return['error_code'] = ApiConfig::REQUEST_FAILL;
                    $return['error_msg'] = '保存失败';
                    $this->ajaxReturn($return);
                }
            }else{
                //编辑设计师
                $userinfo['logo'] = $data['logo'] ? $data['logo'] : '';                     //设计师头像
                $userinfo['name'] = $data['desginer_name'] ? $data['desginer_name'] : '';   //设计师姓名
                $userinfo['cs'] = $data['cs'] ? $data['cs'] : '';                           //所属城市
                $userinfo['qx'] = $data['qx'] ? $data['qx'] : '';                           //所属区域

                $desinfo['school'] = $data['graduation'] ? $data['graduation'] : '';        //毕业院校
                $desinfo['jobtime'] = $data['job_year'] ? $data['job_year'] :'';            //从业时间
                $desinfo['fengge'] = $data['style'] ? $data['style'] : '';                  //擅长风格
                $desinfo['lingyu'] = $data['skill_at_field'] ? $data['skill_at_field'] :''; //擅长领域
                $desinfo['linian'] = $data['linian'] ? $data['linian'] : '';                //设计理念
                $desinfo['text'] = $data['text'] ? $data['text'] : '';                      //个人简介
                $desinfo['cost'] = $data['cost'] ? $data['cost'] : '';                      //收费标准
                $desinfo['cases'] = $data['cases'] ? $data['cases'] : '';                   //代表作

                if($data['comid']){         //表示选择了匹配公司
                    $teaminfo['zw'] = $data['position'] ? $data['position'] : '';               //职位
                    $teaminfo['xs'] = $data['show_index_page'] ? $data['show_index_page'] : 0; //是否显示
                    $teaminfo['px'] = $data['sort'] ? $data['sort'] : '';                       //排序
                    $teaminfo['comid'] = $data['comid'];
                }
                //编辑设计师信息
                $getreturn = $logic->editDesigninfo($data['id'],$userinfo,$desinfo,$teaminfo,CONTROLLER_NAME . "/" . ACTION_NAME);
                if($getreturn === true){
                    $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
                    $return['error_msg'] = '保存成功';
                    $this->ajaxReturn($return);
                }else{
                    $return['error_code'] = ApiConfig::REQUEST_FAILL;
                    $return['error_msg'] = '保存失败';
                    $this->ajaxReturn($return);
                }
            }
        }else{
            $return['error_code'] = ApiConfig::REQUEST_FAILL;
            $return['error_msg'] = '未获取到参数';
            $this->ajaxReturn($return);
        }
    }

    //根据选择城市id获取区域列表
    public function getQuyuListByCityid(){
        $model = new AreaModel();

        $cityid = I('get.cityid');      //城市id
        if(empty($cityid)){
            $return['error_code'] = ApiConfig::REQUEST_FAILL;
            $return['error_msg'] = '获取失败';
            $return['data']['list'] = array();
            $this->ajaxReturn($return);
        }
        $list = $model->getCityById($cityid);
        if(count($list) > 0){
            $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
            $return['error_msg'] = '获取成功';
        }else{
            $return['error_code'] = ApiConfig::REQUEST_FAILL;
            $return['error_msg'] = '获取失败';
        }
        $return['data']['list'] = $list['child'];
        $this->ajaxReturn($return);
    }

    //删除设计师
    public function deleteDesign(){
        $logic = new DesignLogicModel();
        $id = I('post.designid');
        if(empty($id)){
            $return['error_code'] = ApiConfig::PARAMETER_ILLEGAL;
            $return['error_msg'] = '未获取到设计师ID';
        }else{
            $return = $logic->deleteDesignById($id,CONTROLLER_NAME . "/" . ACTION_NAME);
            if($return['error_code'] == 1){
                $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
                $return['error_msg'] = '删除成功';
            }else{
                $return['error_msg'] = '删除失败';
            }
        }
        $this->ajaxReturn($return);
    }

    //批量删除
    public function batchDeleteDesign(){
        $logic = new DesignLogicModel();
        $id = I('post.idlist');
        if(empty($id)){
            $return['error_code'] = ApiConfig::PARAMETER_ILLEGAL;
            $return['error_msg'] = '未获取到设计师ID';
        }else{
            $return = $logic->batchDeleteDesign($id,CONTROLLER_NAME . "/" . ACTION_NAME);
            if($return['error_code'] == 1){
                $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
                $return['error_msg'] = '删除成功';
            }else{
                $return['error_msg'] = '删除失败';
            }
        }
        $this->ajaxReturn($return);
    }


    //显示日志
    public function getDesignLog(){
        $logic = new DesignLogicModel();
        $id = I('get.id');
        if(empty($id)){
            $return['error_code'] = ApiConfig::PARAMETER_ILLEGAL;
            $return['error_msg'] = '未获取到设计师ID';
        }else{
            $list = $logic->getDesignLogListById($id);
            $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
            $return['error_msg'] = '获取日志成功';
            $return['data']['list'] = $list ? $list : array();
        }
        $this->ajaxReturn($return);
    }

    //批量导出
    public function downExcel()
    {
        $designmodel = new DesignLogicModel();
        $cityid = I('get.city');            //城市id
        $area = I('get.area');              //区域id
        $sjsname = I('get.design_name');    //设计师姓名/ID
        $comname = I('get.company_name');   //公司名称
        $truevip = I('get.truevip');        //真/假会员
        $show = I('get.show_home');         //是否首页显示
        //根据条件获取设计师列表
        $list = $designmodel->getDesignListAll($cityid,$area,$sjsname,$comname,$truevip,$show);
        $designmodel->downExcel('title','filename',$list['list']);

    }

    /**
     * 切换城市排序
     * 按照拼音情况下排序
     * 按照省份情况下排序
     */
    public function switchCitysSort(){

        //获取所有省份及城市 按首字母
        $accordCity  =  D("Area")->getAllProvinceAndCitys(true);
        $cityInfo["accordCity"] = $accordCity;

        //获取所有省份及城市 按省份
        $allCity =  D("Area")->getAllProvinceAndCitys();
        $cityInfo["allCity"] = $allCity;

        //默认是安徽省
        $cityInfo['defaultCity'] = $allCity['340000']['child'];

        foreach ($cityInfo['allCity'] as $k => $v) {
            //按首字母取省份
            $provinceBySX[] = array(
                'pid'      => $v['pid'],
                'pname'    => $v['pname'],
                'abc'      => $v['abc'],
            );

        }

        foreach ($cityInfo['allCity'] as $key => &$value) {
            //做mark_red的数组用于把标记红色放前面
            //做px的数组用于城市的排序
            $px       = array();
            //$mark_red = array();
            foreach ($value['child'] as $keyc => $valuec) {
                //$mark_red[$keyc] = $valuec['mark_red'];
                $px[$keyc]       = $valuec['px'];
            }
            array_multisort($px, SORT_ASC, $value['child']);
        }
        $cityInfo['provinceBySX'] = multi_array_sort($provinceBySX,'abc');

        $this->assign("cityInfo",$cityInfo);
        $this->display();
    }

    /**
     *  切换城市排序 保存数据
     * @return json
     */
    public function switchCitysSortSave() {
        $redata   = '';
        $reinfo   = '0x00;失败!';
        $restatus = 0;
        if ($_POST) {
            if ('py' == $_POST['type']) {
                foreach ($_POST['px'] as $key => $value) {
                    $map['cid']     = $key;
                    $data['px_abc'] = $value;
                    $result         = D('Quyu')->saveQuyu($map, $data); //写入数据库
                    //dump(M()->getlastsql());
                    if ($result) {
                        D('Quyu')->addLog($map,$data); //打日志
                    }

                }
            }

            if ('sf' == $_POST['type']) {
                foreach ($_POST['px'] as $key => $value) {
                    $map['cid'] = $key;
                    $data['px'] = $value;
                    $result     = D('Quyu')->saveQuyu($map, $data); //写入数据库
                    //dump(M()->getlastsql());
                    if ($result) {
                        D('Quyu')->addLog($map,$data); //打日志
                    }

                }
            }
            S('Cache:Area',null); //清理缓存
            $redata   = '';
            $reinfo   = '0x01;成功保存!';
            $restatus = 1;
            $this->ajaxReturn($redata,$reinfo,$restatus);
        }

        $redata   = '';
        $reinfo   = '0x03;提交失败!';
        $restatus = 0;
        $this->ajaxReturn($redata,$reinfo,$restatus);
    }

    /**
     * 设置相邻城市
     */
    public function set_upon_city()
    {
        $cid = I("get.city");
        $quyuInfo = D('Home/Db/Adminquyu')->getQuyu(array('cid'=>$cid));

        //获取城市列表
        $city = D('Quyu')->getQuyuList();
        //获取城市相邻信息
        $quyu = new QuyuLogicModel();
        $list = $quyu->getUponCity($cid);
        $this->assign('quyuInfo', $quyuInfo[0]);
        $this->assign('list', $list);
        $this->assign('city', $city);
        $this->display('upon_city');
    }

    public function save_upon_city()
    {
        $quyu = new QuyuLogicModel();
        $quyu->saveUponCity(I('post.'));
        $this->ajaxReturn(['status' => 1,'info'=>'操作成功!']);
    }

    /**
     * 获取区域列表
     * @return [type] [description]
     */
    public function get_area_list(){
        $cid = I("post.cid");
        $list = D("Quyu")->getAreaByQuyu($cid);

        $response = sys_rt("fetch:ok", 1, ["list" => $list]);
        $this->ajaxReturn($response);
    }


    //公司注册页面
    public function companyRegister()
    {
        $param = I('get.');
        $this->assign('getdata',$param);
        $companyLogic = new CompanyLogicModel();
        // 获取城市
        $citys = getUserCitys();
        $result = $companyLogic->companyRegisterList($param,$citys);

        $this->assign('list', $result['list']);
        $this->assign('page', $result['page']);

        $this->assign("citys", $citys);
        $this->display();
    }

    //根据公司id获取装修公司信息
    public function getCompanyDetailById()
    {
        $id = I('get.id');
        $companyLogic = new CompanyLogicModel();
        if($id){
            $info = $companyLogic->getCompanyDetailById($id);
            if(!empty($info)){
                $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
                $return['error_msg'] = '请求成功';
                $return['data'] = $info;
            }else{
                $return['error_code'] = ApiConfig::CANNOT_FIND_DATA;
                $return['error_msg'] = '未查询到装修公司信息';
            }

        }else{
            $return['error_code'] = ApiConfig::REQUEST_FAILL;
            $return['error_msg'] = '公司id不能为空';
        }

        $this->ajaxReturn($return);

    }

    //编辑装修公司
    public function editCompanyInfo()
    {
        $companyLogic = new CompanyLogicModel();
        $param = I('post.');
        if($param['id']){
            $result = $companyLogic->editCompanyInfo($param);
            if($result == false){
                $return['error_code'] = ApiConfig::REQUEST_FAILL;
                $return['error_msg'] = '操作失败';
            }else{
                $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
                $return['error_msg'] = '操作成功';
            }
        }else{
            $return['error_code'] = ApiConfig::CANNOT_GETDATA;
            $return['error_msg'] = '公司id不能为空';
        }

        $this->ajaxReturn($return);

    }






}