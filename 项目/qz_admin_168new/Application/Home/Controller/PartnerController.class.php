<?php

//合作商管理

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Db\HzsCompanyModel;
use Home\Model\Logic\HzsLogicModel;
use Home\Validate\PartnerValidateModel;
use Common\Enums\RbacRoleEnum;

class PartnerController extends HomeBaseController{
    protected $autoCheckFields = false;
    //首页
    public function index(){

        $buttmans = D('Partner')->getButtmanInfo(1);

        //权限start
        $user = $this->User;
        $userId = $user['id'];
        $buttman = array();
        $userWhole = $this->array_column($buttmans,'id');//可以查看所有数据的人员id
        $userWhole = array_unique($userWhole);
        if(in_array($userId,$userWhole)){
            foreach($buttmans as $val){
                if($val["id"] == $userId){
                    $yy_id = $val["id"];
                    array_push($buttman,$val);
                    break;
                }
            }
            //获取合作商名称
            $hzs_info = D('Partner')->getCompanyByYyId($buttman[0]["id"],false);
        }else{
            $buttman = D('Partner')->getButtmanInfo(2);
            //获取所有名称
            $hzs_info = D('Partner')->getCompanyAll(false);
        }
        //权限end
        $map['name'] = I('get.name');
        $map['cooperate_mode'] = I('get.cooperate_mode');
        $map['starttime'] = I('get.starttime');
        $map['endtime'] = I('get.endtime');
        $map['yy_id'] = I('get.yy_id');
        $map['state'] = I('get.state');//合作状态 1测试中 2合作中 3停止合作
        $map['api_state'] = I('get.api_state');
        $map['company_id'] = I('get.company_id');//所属合作商
        //权限start
        if(in_array($userId,$userWhole)){
            $map['yy_id'] = $yy_id;
        }
        //权限end
        $subend = date("m/d/Y");
        if(!empty($map['starttime'])){
            $starttime  = strtotime($map['starttime']);
            $this->assign("starttime",$map['starttime']);
        }else{
            $this->assign("starttime",$subend);
        }

        if(!empty($map['endtime'])){
            $endtime  = strtotime($map['endtime']);
            $this->assign("endtime",$map['endtime']);
        }else{
            $this->assign("endtime",$subend);
        }

        //获取推二渠道部
        $sourceList = D('Partner')->getSourceList();

        $count = D('Partner')->getCompanyCount($map['api_state'],$map['name'],$map['cooperate_mode'],$map['yy_id'] ,$map['state'],$starttime,$endtime,$map['company_id']);
        if($count > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $page = $p->show();

            $list = D('Partner')->getCompany($map['api_state'],$map['name'],$map['cooperate_mode'],$map['yy_id'] ,$map['state'],$starttime,$endtime,$map['company_id'],$p->firstRow, $p->listRows);
        }


        $nowtime = time();
        $lists = array();
        foreach($list as $val){
            $val['number'] = D('Partner')->getCountSource($val['id']);
            $val['hzs_mode'] = $this->getCooperateMode($val['cooperate_mode']);
            $val['pay_mode'] = $this->getPayMode($val['pay_mode']);

            $val['psw'] =authcode($val['psw'],'DECODE','asdfasdfasdflkas;ldfkjs;dlfkj');

            $val['state'] = '合作中';// 未选择测试时间和合作时间的
            if(!empty($val['test_starttime'])&&!empty($val['test_endtime'])){
                if($nowtime <= $val['test_endtime']){
                    $val['state'] = '测试中'; // 选择了测试时间且处于该时间,测试中 选择了测试时间 但尚未到测试时间  测试中
                }else{

                    if(empty($val['cooperate_starttime'])||empty($val['cooperate_endtime'])){
                        $val['state'] = '停止合作'; // 选择且超过了测试日期但未选择合作日期
                    }
                }
            }
            if(!empty($val['cooperate_starttime'])&&!empty($val['cooperate_endtime'])){
                if($nowtime>=$val['cooperate_starttime'] && $nowtime<=$val['cooperate_endtime']){
                    $val['state'] = '合作中'; // 选择了合作日期且处于该时间段
                }else if($nowtime<$val['cooperate_starttime']){
                    $val['state'] = '测试中'; // 选择了合作日期但尚未到达合作日期
                }else if($nowtime>$val['cooperate_endtime']){
                    $val['state'] = '停止合作';     // 选择了日期但已超过日期
                }
            }

            array_push($lists,$val);
        }

        $option = '';
        foreach($sourceList as $val){
            $option.='<option value="'.$val['id'].'">'.$val['name'].'</option>';
        }

        //所属合作商
        $hzsModel = new HzsCompanyModel();
        $hzsList = $hzsModel->getHzsList(['status' => 1]);

        $this->assign('hzs_company',$hzsList);
        $this->assign('option',$option);
        $this->assign('sourceList',$sourceList);
        $this->assign('hzs_info',$hzs_info);
        $this->assign('map',$map);
        $this->assign('page',$page);
        $this->assign('list',$lists);
        $this->assign('buttman',$buttman);
        $this->display();
    }

    private function getCooperateMode($cooperate_mode){
        switch ($cooperate_mode) {
            case 1:
                $hzs_mode = 'CPA（分单）';
                break;
            case 2:
                $hzs_mode = 'CPT';
                break;
            case 3:
                $hzs_mode = 'CPM';
                break;
            case 4:
                $hzs_mode = 'CPC';
                break;
            case 5:
                $hzs_mode = 'CPD';
                break;
            case 6:
                $hzs_mode = 'CPS';
                break;
            case 7:
                $hzs_mode = '免费';
                break;
            case 8:
                $hzs_mode = 'CPA（发单）';
                break;
            default:
                break;
        }
        return $hzs_mode;
    }

    private function getPayMode($mode){
        $pay_mode = '';
        switch ($mode) {
            case 1:
                $pay_mode = '预付';
                break;
            case 2:
                $pay_mode = '周结';
                break;
            case 3:
                $pay_mode = '半月结';
                break;
            case 4:
                $pay_mode = '月结';
                break;
            default:
                break;
        }
        return $pay_mode;

    }



    //添加/编辑页
    public function add(){
       if($_GET){
           $id = I('get.id');
           $user = D('Partner')->getCompanyById($id);
           $this->assign('user',$user);
           $this->assign('edit',1);
       }
        //获取运营系统中推广二部渠道部人员信息
        $buttman = D('Partner')->getButtmanInfo(2);
        //所属合作商
        $hzsModel = new HzsCompanyModel();
        $hzsList = $hzsModel->getHzsList();

        $this->assign('hzsList',$hzsList);
        $this->assign('buttman',$buttman);
        $this->display();

    }

    public function getDetail(){
        if(IS_AJAX){
            $id = I('post.id');
            $info = D('Partner')->getCompanyById($id);
            $info['cooperate_mode'] = $this->getCooperateMode($info['cooperate_mode']);
            $info['pay_mode'] = $this->getPayMode($info['pay_mode']);
            if($info["join_time"] == 0){
                $info["join_time"] = '';
            }else{
                $info["join_time"] = date('Y-m-d', $info['join_time']);
            }
            $info['create_time'] = date('Y-m-d', $info['create_time']);
            if(!empty($info['join_capital'])&&isset($info['join_capital'])){
                $info['join_capital'] = $info['join_capital'].'万';
            }
            $this->ajaxReturn(array("data"=>$info,"status"=>1));
        }
    }

    public function getApiDetail(){
        if(IS_AJAX){
            $id = I('post.id');
            $info = D('Partner')->getApiInfo($id);
            if($info){
                $this->ajaxReturn(array("data"=>$info,"status"=>1));
            }
            $this->ajaxReturn(array("status"=>0));
        }
    }

    public function saveApiDetail(){
        if($_POST){
            $id = I('post.api_id');
            $data['id'] = I('post.api_id');
            $data['companyid'] = I('post.companyid');
            $data['akey'] = I('post.akey');
            $data['ckey'] = I('post.ckey');
            $data['source'] = I('post.source');
            $data['is_use'] = I('post.is_use');
            $data['create_time'] = time();
            $data['update_time'] = time();
            $data['status'] = 1;
            if(empty($id)){
                $data['akey'] = make_password(16)["nopass"];
                $data['ckey'] = make_password(16)["nopass"];
                $info = M('hzs_api')->add($data);
            }else{
                $info = M('hzs_api')->save($data);
            }
            if($info){
                $this->ajaxReturn(array("info"=>"保存成功","status"=>1));
            }
            $this->ajaxReturn(array("info"=>'保存失败',"status"=>0));
        }
    }

    public function addajax(){
        if($_POST){
            $data['name'] = I('post.name');
            $data['account'] = I('post.account', '', 'trim');

            $data['short'] = I('post.short');
            $data['join_time'] = strtotime(I('post.join_time'));
            $data['join_capital'] = I('post.join_capital');
            $data['channel'] = I('post.channel');
            $data['test_starttime'] = strtotime(I('post.test_starttime'));
            $data['test_endtime'] = strtotime(I('post.test_endtime'));
            if(empty(  $data['test_starttime'] )){
                $data['test_endtime'] = 0;
            }
            $data['cooperate_starttime'] = strtotime(I('post.cooperate_starttime'));
            $data['cooperate_endtime'] = strtotime(I('post.cooperate_endtime'));
            if(empty($data['cooperate_starttime'])){
                $data['cooperate_endtime']  = 0;
            }
            $data['cooperate_mode'] = I('post.cooperate_mode');
            $data['pay_mode'] = I('post.pay_mode');
            $data['cooperate_price'] = I('post.cooperate_price');
            $data['cooperate_link'] = I('post.cooperate_link');
            $data['licence_logo'] = I('post.licence_logo'); //营业执照
            $data['linkman'] = I('post.linkman');
            $data['linktel'] = I('post.linktel');
            $data['linkposition'] = I('post.linkposition');
            $data['linkwx'] = I('post.linkwx');
            $data['linkqq'] = I('post.linkqq');
            $data['remark'] = I('post.remark');
            $data['yy_id'] = I('post.yy_id');
            $data['parent_id'] = I('post.hzs_value');
            if(empty($data['name'])){
                $this->ajaxReturn(array('info'=>'合作商名称不能为空','status'=>0));
            }
            if(empty($data['account'])){
                $this->ajaxReturn(array('info'=>'合作商账号不能为空','status'=>0));
            }
            if(empty($data['cooperate_mode'])){
                $this->ajaxReturn(array('info'=>'合作模式不能为空','status'=>0));
            }
            if(empty($data['pay_mode']) && $data['cooperate_mode'] != 7){
                $this->ajaxReturn(array('info'=>'结款方式不能为空','status'=>0));
            }
            // if(empty($data['linkman'])){
            //     $this->ajaxReturn(array('info'=>'联系人不能为空','status'=>0));
            // }
            // if(empty($data['linktel'])){
            //     $this->ajaxReturn(array('info'=>'联系电话不能为空','status'=>0));
            // }
            if(!empty($data['test_starttime'])&&!empty($data['test_endtime'] )){
                if($data['test_starttime']>$data['test_endtime']){
                    $this->ajaxReturn(array('info'=>'测试开始时间不得大于测试结束时间','status'=>0));
                }
            }
            if(!empty($data['cooperate_starttime'])&&!empty($data['cooperate_endtime'] )){
                if($data['cooperate_starttime']>$data['cooperate_endtime']){
                    $this->ajaxReturn(array('info'=>'合作开始时间不得大于合作结束时间','status'=>0));
                }
            }
            if(!empty($data['test_endtime'] )&&!empty($data['cooperate_starttime'])){
                if($data['test_endtime']>$data['cooperate_starttime']){
                    $this->ajaxReturn(array('info'=>'合作日期不得早于测试日期','status'=>0));
                }
            }
            if(!empty($data['test_starttime'] )&&!empty($data['cooperate_starttime'])){
                if($data['cooperate_starttime']<$data['test_starttime']){
                    $this->ajaxReturn(array('info'=>'合作日期不得早于测试日期','status'=>0));
                }
            }
            $data['classify'] = 2;
            $id = I('post.editId');
            if(!empty($id)){
                //编辑
                $data['update_time'] = time();
                $result = D('Partner')->editCompany($id,$data);
                if($result){
                    D('Partner')->addLog("编辑合作商",'hzsedit',$data,$id);
                }
                $this->ajaxReturn(array('status'=>1));
            }else{
                $isExist = D('Partner')->isExistAccount($data['account']);
                if($isExist){
                    $this->ajaxReturn(array('info'=>'该账号已存在','status'=>0));
                }
                $data['psw'] = authcode($this->make_password(),'ENCODE','asdfasdfasdflkas;ldfkjs;dlfkj');//初始密码
                //新增
                $data['create_time'] = time();
                $data['update_time'] = time();
                $result = D('Partner')->addCompany($data);
                if($result){
                    D('Partner')->addLog("添加合作商",'hzsadd',$data,$result);
                    $this->ajaxReturn(array('status'=>1));
                }else{
                    $this->ajaxReturn(array('info'=>'添加失败','status'=>0));
                }
            }

        }

    }
    public function detail(){
        $id = I('get.id');
        if (!empty($id)) {
            $company = D('Partner')->getCompanyById($id, array('id,name'));
            $map['source_src'] = I('get.source_src');
            $map['source_name'] = I('get.source_name');
            $count = D('Partner')->getSourceCount($id, $map['source_src'], $map['source_name']);
            if ($count > 0) {
                $search = D('Partner')->getSourceSearch($id);
                $this->assign('search', $search);
                import('Library.Org.Util.Page');
                $p = new \Page($count, 20);
                $page = $p->show();
                $list = D('Partner')->getSource($id, $map['source_src'], $map['source_name'], $p->firstRow, $p->listRows);
                $lists = array();
                foreach ($list as $val) {
                    $html = array();
                    //if ($val['uv_show']) {
                    //    $html[] = 'uv';
                    //}
                    //if ($val['ip_show']) {
                    //    $html[] = 'ip';
                    //}
                    if ($val['order_show']) {
                        $html[] = '注册量';
                    }
                    if ($val['fendan_show']) {
                        $html[] = '有效注册量';
                    }
                    if ($val['real_order_show']) {
                        $html[] = '实际有效注册量';
                    }
                    $val['html'] = implode(',', $html);
                    if ($val['charge'] == 1) {
                        $val['charge'] = '<span style="color:red;">免费</span>';
                    } else if ($val['charge'] == 2) {
                        $val['charge'] = '<span style="color:green;">付费</span>';
                    } else {
                        $val['charge'] = '未知';
                    }
                    array_push($lists, $val);
                }
            }

        }
        //过滤掉已被选择的数据


//        $this->assign('option', $option);
        $this->assign('company', $company);
        $this->assign('map', $map);
        $this->assign('id', $id);
//        $this->assign('sourceList', $sourceList);
        $this->assign('page', $page);
        $this->assign('list', $lists);
        $this->display();
    }

    public function getSource(){
        if (empty(I('post.condition'))) {
            $this->ajaxReturn(['status' => 0, 'info' => '缺少参数 !']);
        }
        //获取当前用户所在组
        $deptList = D('Partner')->getUserDeptList($this->User['id']);
        //获取用户所在组的所有渠道来源
        $sourceList = D('Partner')->getSourceList($this->array_column($deptList, 'department_id'),'id,name as text',I('post.condition'),20);
        $this->ajaxReturn(['status' => 1, 'data' => $sourceList]);
    }

    /**
     * [delCompany 删除合作商]
     * @return [type]
     */
    public function delCompany(){
        if($_POST){
            $id = I('post.id');
            $result = D('Partner')->delCompany($id);
            D('Partner')->delCompanyAndSource($id);
            if($result){
                $this->ajaxReturn(array("data"=>$result,"status"=>1,'info'=>'删除成功！'));
            }else{
                $this->ajaxReturn(array("data"=>$result,"status"=>0,'info'=>'删除失败，请重试！'));
            }
        }
     }

    /**
     * [delCompany 删除合作商]
     * @return [type]
     */
    public function delSource(){
        if($_POST){
            $id = I('post.id');
//            echo $id;
            $result = D('Partner')->delSource($id);

            if($result){
                $this->ajaxReturn(array("status"=>1,'info'=>'删除成功！'));
            }else{
                $this->ajaxReturn(array("status"=>0,'info'=>'删除失败，请重试！'));
            }
        }
    }


    //匹配渠道
    public function addSource(){
        if(IS_AJAX){
            $lists = I('post.data');
            $list = $this->array_unset_tt($lists, 'source');
            //验证渠道是否被添加过
            $info = (new HzsLogicModel())->checkSource(array_column($list,'source'));
            if ($info['error_code'] != 0) {
                $this->ajaxReturn(['status' => 0, 'info' => $info['error_msg']]);
            }
            foreach($list as $val){
                if(!empty($val['source'])){
                    $isExist = D('Partner')->isExistSrc($val['source']);
                    if(!$isExist){
                       $id =  D('Partner')->addSource($val['company'],$val['source'],$val['uv'],$val['ip'],$val['zhuce'],$val['fendan'],$val['real_zhuce']);
                       if($id>0){
                           D('Partner')->addLog("添加合作商渠道",'hzssourceadd',$val,$id);
                       }
                    }
                }
            }
            $this->ajaxReturn(array('status'=>1));

        }
     }

    public function array_unset_tt($arr,$key){
        //建立一个目标数组
        $res = array();
        foreach ($arr as $value) {
            //查看有没有重复项

            if(isset($res[$value[$key]])){
                //有：销毁
                unset($value[$key]);
            }
            else{
                $res[$value[$key]] = $value;
            }
        }
        return $res;
    }

    public function editSource(){
         if(IS_AJAX){
             $info = I('post.data');
             $data  = $info[0];
             $result = D('Partner')->editSource($data['sid'],$data['uv'],$data['ip'],$data['zhuce'],$data['fendan'],$data['real_zhuce']);
            if($result){
                D('Partner')->addLog("编辑合作商渠道",'hzssourceedit',$data,$data['sid']);
            }
            $this->ajaxReturn(array('status'=>1));
         }
     }

    private function make_password( $length = 8 ) {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789';
        $password = '';
        for ( $i = 0; $i < $length; $i++ ){
            $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $password;
    }



    public function gotoHzs(){
        if(IS_AJAX){
            $account = I('post.account', '', 'trim');
            $result  = D('Partner')->getCompanyByAccount($account);
            if (empty($result)) {
                $this->ajaxReturn(array('status'=>200, 'info' => '合作商不存在或未开启'));
            }

            $userInfo = [
                'id' => $result['id'],
                'account' => $result['account'],
                'adminId' => $this->User['id'],
                'classify' => $result['classify'],
                'parent_id' => $result['parent_id'],
            ];
            session("168hzs_userinfo", $userInfo);

            //添加logo
            $logoInfo = [
                //如果是168new直接跳转的,则记录168new登陆人
                "uid" => $result['id'],
                "username" => $result['name'],
                "status" => 4, //168new直接进入
                'info' => '账号为' . $this->User['id'] . '的' . $this->User['name'] . '登录' . $result['name'] . '后台'
            ];
            $this->addLogo($logoInfo);
            $this->ajaxReturn(array('status'=>1));
        }
    }

    public function addLogo($result){
        //记录登录日志到logging
        import('Library.Org.Util.App');
        $app = new \App();
        $log = array(
            "uid" => $result["uid"],
            "username" => $result['username'],
            "ip" => $app->get_client_ip(),
            "user_agent" => $_SERVER['HTTP_USER_AGENT'],
            "time" => time(),
            "status" => $result['status'],
            'info' => $result['info']
        );
        D("HzsLogging")->addLog($log);
    }

    private function array_column(array $array, $column_key, $index_key = null)
    {
        $result = [];
        foreach ($array as $arr) {
            if (!is_array($arr)) continue;

            if (is_null($column_key)) {
                $value = $arr;
            } else {
                $value = $arr[$column_key];
            }

            if (!is_null($index_key)) {
                $key = $arr[$index_key];
                $result[$key] = $value;
            } else {
                $result[] = $value;
            }
        }
        return $result;
    }

    // 异业合作广告管理
    public function partnerad(){
        $post = I('get.');
        $list = D("Home/Logic/PartneradLogic")->getPartneradList($post);
        $this->assign('list',$list["list"]);
        $this->assign('page',$list["page"]);
        $this->display();
    }

    // 新增异业合作广告
    public function partneradadd(){
        if($_POST) {
            $post = I('post.id');
            if(!empty($post)){
                $data["title"] =  I('post.adName');
                $data["partner"] =  I('post.cooperater');
                $data['start'] = I('post.start_time', 0, 'strtotime');
                $data['end'] = I('post.end_time', 0, 'strtotime');
                $data["pc_img"] =  I('post.pcAdImg');
                $data['pc_url'] =  I('post.pcAdLink','','htmlspecialchars_decode');
                $data['m_url'] =  I('post.mAdLink','','htmlspecialchars_decode');
                $data["pc_coefficient"] =  I('post.pcAdRatio');
                $data["m_img"] =  I('post.mAdImg');
                $data["m_coefficient"] =  I('post.mAdRatio');
                $data["pc_zx"] =  implode(",",I('post.dropZxpc'));
                $data["m_zx"] =  implode(",",I('post.dropZxm'));
                $data["pc_jiaju"] =  implode(",",I('post.dropJjxm'));
                $data["m_jiaju"] =  implode(",",I('post.dropJjm'));
                $data["is_use"] =  I('post.enabled');
                D("Home/Logic/PartneradLogic")->savePartnerad($data,$post);
                $this->ajaxReturn(array("info"=>"修改异业广告成功","error_code"=>0));
            }else{
                $data["title"] =  I('post.adName');
                $data["partner"] =  I('post.cooperater');
                $data['start'] = I('post.start_time', 0, 'strtotime');
                $data['end'] = I('post.end_time', 0, 'strtotime');
                $data['pc_img'] =  I('post.pcAdImg','');
                $data['pc_url'] =  I('post.pcAdLink','','htmlspecialchars_decode');
                $data['m_url'] =  I('post.mAdLink','','htmlspecialchars_decode');
                $data["pc_coefficient"] =  I('post.pcAdRatio');
                $data["m_img"] =  I('post.mAdImg');
                $data["m_coefficient"] =  I('post.mAdRatio');
                $data["pc_zx"] =  implode(",",I('post.dropZxpc'));
                $data["m_zx"] =  implode(",",I('post.dropZxm'));
                $data["pc_jiaju"] =  implode(",",I('post.dropJjxm'));
                $data["m_jiaju"] =  implode(",",I('post.dropJjm'));
                $data["is_use"] =  I('post.enabled');
                $data["time"] =  time();
                $result = D("Home/Logic/PartneradLogic")->savePartnerad($data);
                if($result){
                    $this->ajaxReturn(array("info"=>"新增异业广告成功","error_code"=>0));
                }else{
                    $this->ajaxReturn(array("info"=>"新增异业广告失败","error_code"=>1));
                }
            }
        }else{

            $post = I('get.id');
            if(!empty($post)){
                $info = D("Home/Logic/PartneradLogic")->getPartneradById($post);
                $this->assign('info',$info["info"]);
                $this->assign('location',$info["location"]);
                $this->assign('locationtemp',$info["locationtemp"]);
            }else{
                $location = D("Home/Logic/PartneradLogic")->getPartneradLocationList();
                $this->assign('location',$location);
            }
        }
        $this->display();
    }

    /**
     * [delete 删除异业广告]
     * @return [type] [description]
     */
    public function deletePartnerad(){
        $id = I('post.id');
        if (!empty($id)) {
            $result = D('Home/Logic/PartneradLogic')->deletePartnerad($id);
            if ($result) {
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>0));
            }
        }else{
            $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>1));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>1));
    }

    // 广告位置管理
    public function positionad(){
        $post = I('get.');
        $list = D("Home/Logic/PartneradLogic")->getLocationList($post);
        $this->assign('list',$list["list"]);
        $this->assign('page',$list["page"]);
        $this->display();
    }

    // 编辑广告位置
    public function positioneditad(){
        if($_POST) {
            $post = I('post.id');
            if(!empty($post)){
                $data["name"] =  I('post.pageName');
                $data["url"] =  I('post.website');
                $data["module"] =  I('post.bsite');
                $data["is_use"] =  I('post.enabled');
                $result = D("Home/Logic/PartneradLogic")->saveLocation($data,$post);
                if($result){
                    $this->ajaxReturn(array("info"=>"修改异业广告位置成功","error_code"=>0));
                }else{
                    $this->ajaxReturn(array("info"=>"修改异业广告位置失败","error_code"=>1));
                }
            }else{
                $data["name"] =  I('post.pageName');
                $data["url"] =  I('post.website');
                $data["module"] =  I('post.bsite');
                $data["is_use"] =  I('post.enabled');
                $result = D("Home/Logic/PartneradLogic")->saveLocation($data);
                if($result){
                    $this->ajaxReturn(array("info"=>"新增异业广告位置成功","error_code"=>0));
                }else{
                    $this->ajaxReturn(array("info"=>"新增异业广告位置失败","error_code"=>1));
                }
            }
        }else{
            $post = I('get.id');
            if(!empty($post)){
                $info = D("Home/Logic/PartneradLogic")->getLocationById($post);
                $this->assign('info',$info);
            }
        }
        $this->display();
    }

    /**
     * [delete 删除异业广告位置]
     * @return [type] [description]
     */
    public function deleteLocation(){
        $id = I('post.id');
        if (!empty($id)) {
            $result = D('Home/Logic/PartneradLogic')->deleteLocation($id);
            if ($result) {
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>0));
            }
        }else{
            $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>1));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>1));
    }

    //api数据统计
    public function apitongji(){
        $post = I('get.');
        $list = D("Home/Logic/PartneradLogic")->getHzsApiList($post);
        $this->assign('list',$list["list"]);
        $this->assign('page',$list["page"]);
        $this->display();
    }

    public function findHzsList()
    {
        if ($_POST) {
            $query = I("post.q");
            $hzsLogic = new HzsLogicModel();
            $result = $hzsLogic->findHzsList($query);

            $this->ajaxReturn(array('data'=>$result,'error_msg'=>'','error_code'=>1));
        }
    }

    //根据名称查询所属合作商
    public function findHzsParentList()
    {
        if ($_POST) {
            $name = I("post.q")??'';
            $account = I("post.a")??'';
            $hzsLogic = new HzsLogicModel();
            if ($name || $account) {
                $result = $hzsLogic->findHzsParentList($name, $account);
                $this->ajaxReturn(array('data' => $result, 'error_msg' => '', 'error_code' => 0));
            }
        }
    }

    //合作商管理新
    public function hzsList()
    {
        $get = I('get.');

        // 渠道专员要限制权限
        if (session("uc_userinfo.uid") == RbacRoleEnum::ROLE_QDZY) {
            $get["yy_id"] = session("uc_userinfo.id");
        }

        $logic = new HzsLogicModel();
        $count = $logic->getHzsListCount($get);
        $page = null;
        $list = [];
        if($count > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $page = $p->show();
            $list = $logic->getHzsList($get,$p->firstRow, $p->listRows);
        }

        //对接人
        $pick = D('Partner')->getButtmanInfo(2);
        //获取合作商筛选项
        $model = new HzsCompanyModel();
        $hzsName = $model->getHzsList();
        $hzsName = array_unique(array_column($hzsName,'name'));
        $this->assign('input', $get);
        $this->assign('hzs_name', $hzsName);
        $this->assign('pick', $pick);
        $this->assign('list', $list);
        $this->assign('page',$page);
        $this->display();
    }

    //合作商管理心中
    public function hzsSave()
    {
        $input = $_POST;
        $validate = new PartnerValidateModel();
        $check = $validate::checkAddHzs($input);
        if ($check !== true) {
            $this->ajaxReturn(array('data' => '', 'error_msg' => $check, 'error_code' => 400));
        }
        $logic = new HzsLogicModel();
        if (isset($input['id']) && !empty($input['id'])) {
            $id = $input['id'];
            unset($input['id'],$input['account']);
            $result = $logic->hzsUpdate($id, $input);
            $this->ajaxReturn(array('data' => $result, 'error_msg' => '', 'error_code' => 0));
        } else {
            $input['psw'] = authcode($this->make_password(), 'ENCODE', 'asdfasdfasdflkas;ldfkjs;dlfkj');
            $result = $logic->hzsSave($input);
            $this->ajaxReturn(array('data'=>$result,'error_msg'=>'','error_code'=>0));
        }
    }

    public function getHzsInfoById()
    {
        if($_POST['id']){
            $id= I("post.id");
            $hzsLogic = new HzsLogicModel();
            $result = $hzsLogic->getHzsInfo($id);

            $this->ajaxReturn(array('data'=>$result,'error_msg'=>'','error_code'=>0));
        }
    }
}
