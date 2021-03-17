<?php

//合作商表

namespace Home\Model;
Use Think\Model;

class PartnerModel extends Model{

    protected $autoCheckFields = false;
    /**
     * 添加合作商
     * @param [type] $data [description]
     */
    public function addCompany($data)
    {
        if(!empty($data["cooperate_endtime"])){
            $data["cooperate_endtime"]  = $data["cooperate_endtime"]+86399;
        }
        if(!empty($data["test_endtime"])){
            $data["test_endtime"] = $data["test_endtime"]+86399;
        }
        $result = M("hzs_company")->add($data);
//        echo M('has_company')->getLastSql();
        return $result;
    }

    /**
     * 编辑合作商
     * @param  [type] $id   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editCompany($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        if(!empty($data["cooperate_endtime"])){
            $data["cooperate_endtime"]  = $data["cooperate_endtime"]+86399;
        }
        if(!empty($data["test_endtime"])){
            $data["test_endtime"] = $data["test_endtime"]+86399;
        }
        $result = M("hzs_company")->where($map)->save($data);
        return $result;
    }

    /**
     * 获取对接人信息
     */
    public function getButtmanInfo($type = 1)
    {
        if ($type == 1) {
            //角色为渠道专员的 测试上uid=90 线上88
            $map["uid"] = array('EQ', 88);
        } else {
            //todo
            //获取微信部/渠道部 的角色
            $deptDb = new RoleDepartmentModel();
            $role = $deptDb->getDepartmentRoleById([26,27,28, 30]);
            if (count($role) == 0) {
                return [];
            }
            $map["uid"] = array('IN', array_column($role,'role_id'));
        }
        $map['stat'] = array('EQ', 1);
        $map['state'] = array('EQ', 1);
        return M('adminuser')->field('id ,id as uid,name as uname')->where($map)->select();
    }

    /**
     * 获取合作商信息
     * @param  [type] $name [合作商id]
     * @param  [type] $cooperate_mode [合作模式]
     * @param  [type] $create_time [加入时间]
     * @param  [type] $yy_id [对接人id]
     * @param  [type] $state [合作状态]
     */
    public function getCompany($api_state, $name, $cooperate_mode, $yy_id, $state, $starttime, $endtime, $company_id, $start, $end)
    {

        if(!empty($end)){
            $limit = $start .','. $end;
        }
        $map['c.status'] = 1;
        $map['c.classify'] = 2;
        if(!empty($name)){
            $map['c.id'] = $name;
        }
        if(!empty($cooperate_mode)){
            $map['c.cooperate_mode'] = $cooperate_mode;

        }

        if(!empty($yy_id)){
            $map['c.yy_id'] = $yy_id;
        }
        if (!empty($company_id)) {
            $map['c.parent_id'] = $company_id;
        }

        if(!empty($starttime)&&!empty($endtime)){
            $endtime = $endtime+86399;
            $map['c.create_time'] = array(array('egt',$starttime),array('elt',$endtime));
        }else if(!empty($starttime)&&empty($endtime)){
            $map['c.create_time'] = array(array('egt',$starttime));
        }else if(!empty($endtime)&&empty($starttime)){
            $endtime = $endtime+86399;
            $map['c.create_time'] = array(array('elt',$endtime));
        }

        //检查在合作时间内的查询start
        if(!empty($state)){
            $nowTime = time();
            if($state == 1){//合作中
                $map['_string'] = '(c.cooperate_starttime !=0  and  c.cooperate_endtime !=0 AND ('.$nowTime.' BETWEEN c.cooperate_starttime AND c.cooperate_endtime)) or (c.cooperate_starttime=0  and  c.cooperate_endtime=0 AND c.test_starttime=0 and c.test_endtime=0)';
            }else if($state == 2){//测试中
                $map['_string'] ='(c.test_starttime !=0 and c.test_endtime !=0 AND ('.$nowTime.' BETWEEN c.test_starttime AND c.test_endtime)) or (c.test_starttime !=0 and c.test_endtime !=0 AND '.$nowTime.' < c.test_starttime) or (c.cooperate_starttime !=0  and  c.cooperate_endtime !=0 AND '.$nowTime.' < cooperate_starttime)';

            }else if($state == 3){//合作终止
                $map['_string'] =  '(test_starttime !=0 and test_endtime !=0 and cooperate_starttime=0 and cooperate_endtime=0 AND '.$nowTime.' > c.test_endtime) or (c.cooperate_starttime !=0  and  cooperate_endtime !=0 AND '.$nowTime.' > c.cooperate_endtime)';
            }
        }
        //end
        if(!empty($api_state)){
            $result = M('hzs_company')->alias('c')
                ->join('left join qz_adminuser as a on a.id = c.yy_id')
                ->join('left join qz_hzs_api as api on api.companyid = c.id and api.status = 1')
                ->join('left join qz_hzs_company as cp on cp.id = c.parent_id')
                ->field('c.*,a.name as aname,if(api.is_use is null, 1, api.is_use) as api_state,cp.name as parent_name')
                ->where($map)
                ->having('api_state = '.$api_state)
                ->limit($limit)->order('c.create_time desc,c.id desc')->select();
        }else{
            $result = M('hzs_company')->alias('c')
                ->join('left join qz_adminuser as a on a.id = c.yy_id')
                ->join('left join qz_hzs_api as api on api.companyid = c.id and api.status = 1')
                ->join('left join qz_hzs_company as cp on cp.id = c.parent_id')
                ->field('c.*,a.name as aname,if(api.is_use is null, 1, api.is_use) as api_state,cp.name as parent_name')
                ->where($map)
                ->limit($limit)->order('c.create_time desc,c.id desc')->select();
        }
        return $result;
    }


    public function getCompanyCount($api_state,$name,$cooperate_mode,$yy_id ,$state,$starttime,$endtime,$company_id){
        $map['c.status'] = 1;
        $map['c.classify'] = 2;
        if(!empty($name)){
            $map['c.id'] = $name;
        }
        if(!empty($cooperate_mode)){
            $map['c.cooperate_mode'] = $cooperate_mode;
        }

        if(!empty($yy_id)){
            $map['c.yy_id'] = $yy_id;
        }
        if (!empty($company_id)) {
            $map['c.parent_id'] = $company_id;
        }

        if(!empty($starttime)&&!empty($endtime)){
            $endtime = $endtime+86399;
            $map['c.create_time'] = array(array('egt',$starttime),array('elt',$endtime));
        }else if(!empty($starttime)&&empty($endtime)){
            $map['c.create_time'] = array(array('egt',$starttime));
        }else if(!empty($endtime)&&empty($starttime)){
            $endtime = $endtime+86399;
            $map['c.create_time'] = array(array('elt',$endtime));
        }


        //检查在合作时间内的查询start
        if(!empty($state)){
            $nowTime = time();
            if($state == 1){//合作中
                $map['_string']  = '(c.cooperate_starttime !=0  and  c.cooperate_endtime !=0 AND ('.$nowTime.' BETWEEN c.cooperate_starttime AND c.cooperate_endtime)) or (c.cooperate_starttime=0  and  c.cooperate_endtime=0 AND c.test_starttime=0 and c.test_endtime=0)';
            }else if($state == 2){//测试中
                $map['_string']  ='(c.test_starttime !=0 and c.test_endtime !=0 AND ('.$nowTime.' BETWEEN c.test_starttime AND c.test_endtime)) or (c.test_starttime !=0 and c.test_endtime !=0 AND '.$nowTime.' < c.test_starttime) or (c.cooperate_starttime !=0  and  c.cooperate_endtime !=0 AND '.$nowTime.' < cooperate_starttime)';

            }else if($state == 3){//合作终止
                $map['_string']  =  '(test_starttime !=0 and test_endtime !=0 and cooperate_starttime=0 and cooperate_endtime=0 AND '.$nowTime.' > c.test_endtime) or (c.cooperate_starttime !=0  and  cooperate_endtime !=0 AND '.$nowTime.' > c.cooperate_endtime)';
            }
        }

        //end
        if(!empty($api_state)){
            $result = M('hzs_company')->alias('c')
                ->join('left join qz_hzs_api as a on a.companyid = c.id and a.status = 1')
                ->field('if(a.is_use is null, 1, a.is_use) as api_state')
                ->having('api_state = '.$api_state)
                ->where($map)->select();

            $result = count($result);
        }else{
            $result = M('hzs_company')->alias('c')
                ->join('left join qz_hzs_api as a on a.companyid = c.id and a.status = 1')
                ->where($map)->count();
        }
        return $result;
    }

    public function getCompanyByAccount($account){
        if(!empty($account)){
            $map["status"] = 1;
            $map["account"] = $account;
            return M('hzs_company')->where($map)->find();
        }
    }

    /**
     * 获取合作商名称
     */
    public function getCompanyAll($type=true){
        $map['status'] = 1;
        if ($type == false) {
            $map['classify'] = 2;
        }
        $result = M('hzs_company')->where($map)->field('id,name')->select();
        return $result;
    }

    /**
     * 获取合作商名称
     */
    public function getCompanyByYyId($yy_id, $type = true)
    {
        $map['status'] = 1;
        $map['yy_id'] = $yy_id;
        if ($type == false) {
            $map['classify'] = 2;
        }
        $result = M('hzs_company')->where($map)->field('id,name')->select();
        return $result;
    }



    /**
     * [getCompanyById 根据ID查询合作商的信息]
     * @param  [string]     $id         合作商ID
     * @return [array]      $result     查询结果数组
     */
    public function getCompanyById($id,$field='*')
    {
        if($id){
            $map['id'] = $id;
            $result = M('hzs_company')
                ->where($map)->field($field)->find();
            return $result;
        }
    }

    /**
     * [delCompany 删除合作商]
     * @param  [string]     $id   要删除的用户ID
     * @return [type]
     */
    public function delCompany($id)
    {
        if(!empty($id)){
            $data['status'] = 0;
            $map['id'] = $id;
            $result = M('hzs_company')->where($map)->save($data);
        }
        return $result;
    }

    /**
     * [delCompanyAndSource 删除合作商]
     * @param  [string]     $id   要删除的用户ID
     * @return [type]
     */
    public function delCompanyAndSource($id)
    {
        if(!empty($id)){
            $map['companyid'] = $id;
            $result = M('hzs_source')->where($map)->delete();
        }
        return $result;
    }

    /**
     * [delCompany 删除合作商]
     * @param  [string]     $id   要删除的用户ID
     * @return [type]
     */
    public function delSource($id)
    {
        if(!empty($id)){
            $data['status'] = 0;
            $map['id'] = $id;
            $result = M('hzs_source')->where($map)->save($data);
        }
        return $result;
    }


    //获取渠道标识表
    public function getSourceList($id = 10,$field = '*',$name = '',$limit = []){
        if($id){
            if(is_array($id)){
                $map['a.dept'] = ['in',$id];
            }else{
                $map['a.dept'] = ['eq','10']; // 默认取推广二部
            }
        }
        if (!empty($name)) {
            $map['a.name'] = ['like', $name . '%'];
        }
        $map['a.visible'] = 0;
        $map['a.type'] = 1;
        $Db = M('order_source');
        $result = $Db->alias("a")->field($field)->where($map)->limit($limit)->order('a.id DESC')->select();
        return $result;
    }

    //获取当前人员所在组
    public function getUserDeptList($uid){
        if(!$uid){
            return [];
        }
        $where['user_id'] = ['eq',$uid];
        return M('order_source_relate')->field('department_id')->where($where)->select();
    }

//    public function
    //添加匹配标识
    public function addSource($companyId,$sourceId,$uv,$ip,$zhuce,$fendan,$real_zhuce){
        $data['status'] = 1;
        $data['create_time'] = time();
        $data['companyid'] = $companyId;
        $data['sourceid'] = $sourceId;
        //$data['uv_show'] = $uv;
        //$data['ip_show'] = $ip;
        $data['order_show'] = $zhuce;
        $data['fendan_show'] = $fendan;
        $data['real_order_show'] = $real_zhuce;
        $result = M("hzs_source")->add($data);
        return $result;
    }
    //修改匹配标识
    public function editSource($sid,$uv,$ip,$zhuce,$fendan,$real_zhuce){
        if($sid){
            //$data['uv_show'] = $uv;
            //$data['ip_show'] = $ip;
            $data['order_show'] = $zhuce;
            $data['fendan_show'] = $fendan;
            $data['real_order_show'] = $real_zhuce;
            $result = M("hzs_source")->where(array('id'=>$sid))->save($data);
//            echo M('hsz_source')->getLastSql();
            return $result;
        }
    }

    public function getCountSource($id){
        if(!empty($id)){
            if (is_array($id)) {
                $where = [
                    ['companyid' => array('in', $id)],
                    ['status' => array('EQ', 1)]
                ];
                $result = M('hzs_source')->where($where)->count();
            } else {
                $result = M('hzs_source')->where(array('companyid' => $id, 'status' => 1))->count();
            }

//            echo M('hzs_source')->getLastSql();
            return $result;
        }

    }
    //获取api接口信息
    public function getApiInfo($id){
        if(!empty($id)){
            $result = M('hzs_api')->where(array('companyid'=>$id,'status'=>1))->find();
            return $result;
        }

    }

    public function getSource($id,$source_src,$source_name,$start,$end){
        if(!empty($id)){
            if(!empty($end)){
                $limit = $start .','. $end;
            }

            $map['s.status'] = 1;
            $map['s.companyid'] = $id;
            if(!empty($source_src)){
                $map['o.src'] = $source_src;
            }
            if(!empty($source_name)){
                $map['o.name'] = $source_name;
            }
            $result = M('hzs_source')->alias('s')
                ->join('left join qz_order_source as o on o.id = s.sourceid')
                ->field('o.* , s.uv_show,s.ip_show,s.order_show,s.fendan_show,s.real_order_show,s.id as sid')
                ->where($map)->limit($limit)->select();
            return $result;
        }

    }

    public function getSourceSearch($id){
        if(!empty($id)){
            $map['s.status'] = 1;
            $map['s.companyid'] = $id;
            $result = M('hzs_source')->alias('s')
                ->join('left join qz_order_source as o on o.id = s.sourceid')
                ->field('o.* ')
                ->where($map)->select();
            return $result;
        }
    }

    public function getSourceCount($id,$source_src,$source_name){
        if(!empty($id)){
            $map['s.status'] = 1;
            $map['s.companyid'] = $id;
            if(!empty($source_src)){
                $map['o.src'] = $source_src;
            }
            if(!empty($source_name)){
                $map['o.name'] = $source_name;
            }
            $result = M('hzs_source')->alias('s')
                ->join('left join qz_order_source as o on o.id = s.sourceid')
                ->field('o.* , s.uv_show,s.ip_show,s.order_show,s.real_order_show,s.id as sid')
                ->where($map)->count();
            return $result;
        }

    }


    public function isExistSrc($source){
        $map['sourceid'] = $source;
        $map['status'] = 1;
        $complete = M('hzs_source')->where($map)->find();

        if (!empty($complete)){
            return true;
        }else{
            return false;
        }
    }

    public function isExistAccount($account,$type=true){
        $map["account"] = $account;
        if ($type == true) {
            $map["status"] = 1;
        }
        $complete = M('hzs_company')->where($map)->find();
        if (!empty($complete)){
            return true;
        }else{
            return false;
        }
    }

    public function addLog($remark,$logtype,$infos,$id){

        $info["info"] = json_encode($infos);
        $admin = getAdminUser();
        import('Library.Org.Util.App');
        $app = new \App();
        $ip =  $app->get_client_ip();
        $extra = array(
            'logtype'=> $logtype,
            'time' => date("Y-m-d H:i:s"),
            'username' => $admin['name'],
            'userid' => $admin['id'],
            'action' => CONTROLLER_NAME.'/'.ACTION_NAME,
            'ip' => $ip,
            'user_agent' => $_SERVER["HTTP_USER_AGENT"],
            'action_id'=>$id,
            'remark'=>$remark
        );
        $data = array_merge($info,$extra);

        return M('log_admin')->add($data);
    }

    /**
     * 查询已被添加到匹配标识表的数据
     */
    public function getExistSource(){
        $map['status'] = 1;
        $result = M('hzs_source')->alias('s')
            ->join('left join qz_order_source as o on o.id = s.sourceid')
            ->field('o.src ')
            ->where($map)->select();
        return $result;
    }

    public function getHzsApiListCount($where){
        if(!empty($where['hzs_name'])){
            $map["c.name"] = array("LIKE","%".$where['hzs_name']."%");
        }
        if(!empty($where['api_state'])){
            $map["a.is_use"] = $where['api_state'];
        }
        $map["a.status"] = 1;
        return M('hzs_api')->alias('a')
            ->join('inner join qz_hzs_company as c on c.id = a.companyid')
            ->where($map)->alias("a")
            ->field("a.id")
            ->count();
    }

    public function getHzsApiList($where,$pageIndex,$pageCount){
        if(!empty($where['hzs_name'])){
            $map["c.name"] = array("LIKE","%".$where['hzs_name']."%");
        }
        $tempWhere = "";
        if (!empty($where['start_time'])){
            $tempWhere.= " and o.add_time >= ".strtotime($where['start_time']);
        }
        if (!empty($where['end_time'])){
            $tempWhere.= " and o.add_time <= ".strtotime($where['end_time']);
        }
        if(!empty($where['api_state'])){
            $map["a.is_use"] = $where['api_state'];
        }
        $map["a.status"] = 1;
        $buildSql = M('hzs_api')->alias('a')
            ->join('inner join qz_hzs_company as c on c.id = a.companyid')
            ->join('left join qz_hzs_order_pool as o on o.company_id = a.companyid'.$tempWhere)
            ->where($map)
            ->field("o.id,a.companyid,c.name,if(o.state=0,1,0) as apino,if(o.state=1,1,0) as apisuccess,a.source,if(o.state=2,1,0) as apifail,a.is_use as api_state")
            ->buildSql();
        return M('hzs_api')->table($buildSql)->alias('t1')
            ->field("t1.companyid,t1.name,t1.source,sum(t1.apisuccess) as apisuccess,sum(t1.apifail) as apifail,count(t1.id) as apiall,t1.api_state")
            ->order("t1.companyid")
            ->group("t1.companyid")
            ->limit($pageIndex . "," . $pageCount)
            ->select();
    }

    public function getHzsInfoBySource($where, $field = 's.*')
    {
        return M('hzs_source')->alias('s')
            ->field($field)
            ->join('qz_hzs_company c on c.id = s.companyid')
            ->join('qz_order_source src on src.id = s.sourceid')
            ->where($where)
            ->select();
    }
}

