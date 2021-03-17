<?php
namespace Home\Model;
Use Think\Model;
/**
* 销售系统设置
*/
class SaleSettingModel extends Model
{
    protected $autoCheckFields = false;
    //取 value 列表
    public function getValueList($condition,$pagesize= 1,$pageRow = 10){
        if(isset($condition['typeid'])){
            $map['v.typeid']  = array("EQ",$condition['typeid']);
        }
        if(isset($condition['module'])){
            $map['v.module']  = array('EQ',$condition['module']);
        }
        if(isset($condition['status'])){
            $map['v.status']  = array("EQ",$condition['status']);
        }
        if(isset($condition['cid'])){
            $map['v.cid']  = array("EQ",$condition['cid']);
        }
        if(isset($condition['uid'])){
            $map['v.uid']  = array("EQ",$condition['uid']);
        }
        if(isset($condition['start'])){
            $map['v.start']  = array("EGT",$condition['start']);
        }
        if(empty($condition['orderBy'])){
            $condition['orderBy']  = "v.lasttime desc";
        }
        $Db = M('sales_setting_value');
        $count  = $Db->alias("v")->where($map)->count();
        $result = $Db->alias("v")
                      ->field('v.*,u.user')
                      ->join("inner join qz_adminuser as u on v.uid = u.id")
                      ->order($condition['orderBy'])
                      ->limit($pagesize.",".$pageRow)
                      ->where($map)
                      ->select();
        return array("result"=>$result,"count"=>$count);
    }
    //取单个分类信息
    public function getCategoryById($id){
        $map = array(
            'id' => array("EQ",$id),
            'status' => array("EQ",'0')
        );
        return M('sales_category')->field('*')->where($map)->find();
    }
    //按条件取分类列表
    public function getCategory($map){
        $map['status'] = array("EQ",'0');
        return M("sales_category")->field('*')->where($map)->order('id')->select();
    }
    //按条件取设置值表
    public function getSettingValue($map,$orderby=''){
        $map['status'] = array("EQ",'1');
        if(empty($orderby)){
            $orderby = 'id';
        }
        return M("sales_setting_value")->field('*')->where($map)->order($orderby)->select();
    }
    //按帐号取城市列表
    public function getCityByUid($map){
        $map['s.status'] = array("EQ",'1');
        return M("sales_setting_value")->alias("s")
                ->field("s.cid,q.cname,q.bm")
                ->join("left join qz_quyu as q on q.cid = s.cid")
                ->where($map)
                ->order('q.bm')
                ->select();
    }
    //取所有用户Uid列表
    public function getUidList($map){
        return M("sales_setting_value")->alias("s")
                ->field("s.uid,u.user")
                ->join("left join qz_adminuser as u on s.uid = u.id")
                ->where($map)
                ->group('s.uid')
                ->select();
    }
    /**
     * [getCityImportantCoefficient 获取城市重点系数]
     * @param  [type] $cityIds [description]
     * @return [type]          [description]
     */
    public function getCityImportantCoefficient($cityIds){
        if (empty($cityIds)) {
            return false;
        }
        if (!is_array($cityIds)) {
            $cityIds = array($cityIds);
        }
        $map = array(
            'c.type' => 4,
            'v.cid' => array('IN',$cityIds)
        );
        $result = M('sales_category')->alias('c')
                                     ->field('v.cid,c.name AS number')
                                     ->join('INNER JOIN qz_sales_setting_value AS v ON v.pid = c.id')
                                     ->where($map)
                                     ->order('v.id DESC')
                                     ->group('v.cid')
                                     ->select();
        return $result;
    }
    //写入操作日志
    public function getOperationLogIn($type,$content) {
        //字段  id   opid  optime  opip  optype opdes content
        $data['opid'] = $_SESSION['uc_userinfo']['id'];
        $data['optime'] = time();
        $data['opip'] = $_SESSION['uc_userinfo']['ip'];
        $data['optype'] = $type;
        switch ($type) {
            case '1':
                $data['opdes'] = '操作职能部门商务';
                break;
            case '5':
                $data['opdes'] = '操作职能部门外销';
                break;
            case '6':
                $data['opdes'] = '操作职能部门品牌';
                break;
            case '2':
                $data['opdes'] = '操作会员指标';
                break;
            case '3':
                $data['opdes'] = '操作分单指标';
                break;
            case '4':
                $data['opdes'] = '操作城市重点系数';
                break;
            default:
                $data['opdes'] = '';
                break;
        }
        $data['content'] = $content;
        $result = M("sales_log")->data($data)->add();
        return $result;
    }
    /*
    *   查询日志
    *
    */
    public function getOperationLogByType($map) {
        $result = M("sales_log")->alias('l')
                                ->join("qz_adminuser as u ON u.id = l.opid")
                                ->field("l.*,u.user")
                                ->where($map)
                                ->select();
        return $result;
    }
    /*
    *   查询sales_order_points表操作人
    *
    */
    public function getPointsManagers(){
        $result = M("sales_order_points")->where('status = 1')->field('manager')->group("manager")->select();
        //var_dump(M()->getLastSql());
        return $result;
    }
    /*
    *   根据关键词查询公司
    *
    */
    public function searchForCompanyUser($map)
    {
        $users = M('user')->alias('u')
                ->join("qz_sales_order_points as s on u.id = s.userid")
                ->field('u.id,u.user')
                ->limit('0,15')
                ->where($map)
                ->select();
        return $users;
    }
    /*
    *   查询会员分单需求总条数
    *
    */
    public function getCompanyListCount($map)
    {
        $map['p.status'] = 1;
        $num = M("sales_order_points")->alias("p")->where($map)->count();
        return $num;
    }
    /*
    *   获取公司分单系数列表
    *
    *
    */
    public function getCompanyList($map, $order = 'lasttime DESC', $start, $end)
    {
        $map['p.status'] = 1;
        if($start == 0 && $end == 0){
            $result = M("sales_order_points")->alias("p")
                                    ->join('LEFT JOIN qz_quyu AS q ON q.cid = p.cityid')
                                    ->join('LEFT JOIN qz_user AS u ON u.id = p.userid')
                                    ->field('u.user as companyname, q.cname AS city, p.point as point, p.start, p.manager, p.id, p.lasttime')
                                    ->where($map)
                                    ->order($order)
                                    ->select();
        }else{
            $result = M("sales_order_points")->alias("p")
                                    ->join('LEFT JOIN qz_quyu AS q ON q.cid = p.cityid')
                                    ->join('LEFT JOIN qz_user AS u ON u.id = p.userid')
                                    ->field('u.user as companyname, q.cname AS city, p.point as point, p.start, p.manager, p.id, p.lasttime')
                                    ->where($map)
                                    ->order($order)->limit($start, $end)
                                    ->select();
        }

        return $result;
    }
    /*
    *   根据ID获取公司分单系数
    *
    *
    */
    public function getPointContentById($id)
    {
        $map['p.id'] = $id;
        $result = M("sales_order_points")->alias("p")
                                    ->join('LEFT JOIN qz_quyu AS q ON q.cid = p.cityid')
                                    ->join('LEFT JOIN qz_user AS u ON u.id = p.userid')
                                    ->field('u.user as companyname,u.id as companyid, q.cname AS city, p.point as point, p.lasttime,p.start ,p.manager, p.id ,p.cityid')
                                    ->where($map)
                                    ->select();
        return $result;
    }
    /*
    *   设置新的公司分单系数列表
    *
    *
    */
    public function setNewCompanyPoint($map,$data)
    {
        $result = M("sales_order_points")->where($map)->save($data);
        return $result;
    }
    /*
    *   报备新的城市QQ群
    *   createNewQQgroup
    *
    *
    */
    public function createNewQQgroup($data)
    {
        $result = M("sales_city_qqgroup")->add($data);
        return $result;
    }
    /*
    *   修改城市QQ群
    *   createNewQQgroup
    *
    *
    */
    public function editQQgroup($map,$data)
    {
        $result = M("sales_city_qqgroup")->where($map)->save($data);
        return $result;
    }
    /*
    *   获取城市QQ群列表
    *
    */
    public function getQQgroups($map,$order='addtime DESC', $start, $end)
    {
        $order = "addtime desc";
        if($start == 0 && $end == 0){
            $result = M("sales_city_qqgroup")->alias("q")
                                        ->join("qz_quyu as d on q.cityid = d.cid")
                                        ->where($map)
                                        ->field("q.*,d.cname")
                                        ->order($order)
                                        ->select();
        }else{
            $result = M("sales_city_qqgroup")->alias("q")
                                        ->join("qz_quyu as d on q.cityid = d.cid")
                                        ->where($map)
                                        ->field("q.*,d.cname")
                                        ->order($order)
                                        ->limit($start, $end)
                                        ->select();
        }

        return $result;
    }
    /*
    *   查询城市QQ群列表总条数
    *
    */
    public function getQQgroupListCount($map)
    {
        $num = M("sales_city_qqgroup")->alias("p")->where($map)->count();
        return $num;
    }
    //取城市信息按城市名
    public function getCityByCName($name){
        $map = array(
            'cname' => array("EQ",$name)
        );
        return M('quyu')->field('*')->where($map)->find();
    }
    /**
     * [findUsers 获取adminuser用户]
     * @param  [string]  $name  [姓名]
     * @param  integer $limit   [获取条数]
     * @return [array] $result  [标签数组]
     */
    public function findUsers($name,$limit = 15){
        $map['uid'] = array('IN','1,7,56,61,62,36,39,40,41,42,43,45,72,77,71,58,59,65,67,3,46,73,60,37');
        $map['name'] =  array('EQ',$name);
        $map['stat'] =  array('EQ',1);
        //有限查看是否有完全匹配的数据，如果有完全匹配的数组，模糊匹配查询数量减少一个
        $complete = M('adminuser')->where($map)->find();
        if (!empty($complete)) {
            $limit = $limit - 1;
        }
        //重新定义名字查询条件
        $map['name'] =  array('like','%'.$name.'%');
        $result = M('adminuser')->where($map)->limit($limit)->select();
        if (!empty($complete)) {
            array_unshift($result, $complete);
        }
        return $result;
    }
    /**
     * [getManageUser 获取管理的用户]
     * @param  array    $map        [查询的部门]
     * @param  string    $field      [查询的字段]
     * @return array    $result     [查询结果]
     */
    public function getManageUser($map,$field)
    {
        /*if(!empty($field)){
            $str = implode(',', $field);
        }*/
        $result = M("sales_city_manage")->where($map)->field($field)->select();
        return $result;
    }
    /**
     * [addcitymanagers 获取管理的用户]
     * @param  array    $map            [where条件]
     * @param  array    $data           [data数据]
     * @return array    $result         [结果]
     */
    public function addcitymanagers($map,$data)
    {
        $result = M("sales_city_manage")->where($map)->save($data);
        return $result;
    }
    /**
     * [getAllCitys 获取所有城市]
     * @return array    $result         [所有城市数组]
     */
    public function getAllCitys($role = null)
    {
        if(!empty($role)){
            $where['a.dept'] =  array("EQ",$role);
        }
        $result = M("sales_city_manage")->alias("a")->where($where)
                                        ->join("left join qz_quyu q on q.cname = a.city")
                                        ->field('a.id,a.city,q.cid')->order("id asc")->select();
        return $result;
    }
    /**
     * [getManageCitys 获取所有城市]
     * @param  array    $where          查询条件
     * @return array    $result         返回城市数组
     */
    public function getManageCitys($where=null)
    {
        if(!empty($where) && $where != 0){
            $map = $where;
        }
        $result = M("sales_city_manage")->alias("a")->where($map)
                                        ->join("left join qz_quyu q on q.cname = a.city")
                                        ->field('a.id,a.city,q.cid')->order("id asc")->select();
        return $result;
    }
    /**
     * [addNewCity 写入新城市]
     * @param  array    $citys          城市名称数组
     * @return array    $result         写入结果
     */
    public function addNewCity($citys)
    {
        if(!empty($citys)){
            foreach ($citys as $k => $v) {
                $data['city'] = trim($v);
                $data['act_time'] = time();
                $data['act_uid'] = $_SESSION['uc_userinfo']['id'];
                $data['open_status'] = 1;
                $map['city'] = trim($v);
                $isHave = M("sales_city_manage")->where($map)->find();
                if(empty($isHave)){
                    $result[$k] = M("sales_city_manage")->add($data);
                    $px_data['cityid'] = $result[$k];
                    M("sales_city_paixu")->add($px_data);
                }else{
                    $result[$k] = $isHave['id'];
                }
            }
        }
        return $result;
    }
    /**
     * [editCity 编辑城市]
     * @param  array    $where          城市ID
     * @param  array    $data           修改的数据数组
     * @return array    $result         写入结果
     */
    public function editCity($where,$data)
    {
        $result = M("sales_city_manage")->where($where)->save($data);
        //$sql = M()->getLastSql();
        return $result;
    }
    /**
     * [resetAllManager 重置个人管辖城市数据]
     * @param  array    $name          用户ID
     * @return array    $result        修改结果
     */
    public function resetAllManager($name)
    {
        $where = [
            array('where'=>"corps = $name",'data'=>array('corps'=>0)),
            array('where'=>"dev_division = $name",'data'=>array('dev_division'=>0)),
            array('where'=>"dev_regiment = $name",'data'=>array('dev_regiment'=>0)),
            array('where'=>"dev_manage = $name",'data'=>array('dev_manage'=>0)),
            array('where'=>"brand_division = $name",'data'=>array('brand_division'=>0)),
            array('where'=>"brand_regiment = $name",'data'=>array('brand_regiment'=>0)),
            array('where'=>"brand_manage = $name",'data'=>array('brand_manage'=>0)),
            array('where'=>"assistant = $name",'data'=>array('assistant'=>0))
        ];
        foreach ($where as $k => $v) {
            $result = M("sales_city_manage")->where($v['where'])->save($v['data']);
        }
        return $result;
    }

    /**
     * [updatePaixu 修改城市排序]
     * @param  string    $id          城市ID
     * @param  string    $paixu       排序字段
     * @return array    $result       修改结果
     */
    public function updatePaixu($id,$type)
    {
        if(!empty($id)){
            $map['cityid'] = $id;
            $paixu = 'px'.$type;//要修改的字段
            //查出字段中的最大值
            $max = M("sales_city_paixu")->field("max($paixu) as max")->select();
            $value = $max[0]['max'] + 1;
            $data[$paixu] = $value;
            $result = M("sales_city_paixu")->where($map)->save($data);
        }
        return $result;
    }

}