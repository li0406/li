<?php

//城市会员

namespace Home\Model;
Use Think\Model;

class CitypointModel extends Model{

    protected $autoCheckFields = false;
    /**
     * XX实例函数
     * @param mixed    $name  xx作用，多种参数并存可以用mixed
     * @param string   $value  xx作用1
     * @param array    $options xx作用2
     * @param string   $tag 缓存标签
     * @return mixed|array|integer|void
    */

    //取城市会员数
    /*public function getCityVipCount(){
        $buildSql = M("user")->alias("a")
                            ->field("a.cs,sum(if(a.on = 2,b.viptype,null)) as vipcnt,sum(if(b.viptype > 1,(b.viptype-1),null)) as doublecnt,count(if(a.on = 2,a.id,null)) as vipnum")
                            ->join("inner join qz_user_company b on a.id = b.userid WHERE (a.classid = 3) AND (b.fake = 0) AND (a.on = '2' )")
                            ->group('a.cs')
                            ->order("vipcnt desc")
                            ->buildSql();

        return M()->table($buildSql)
                ->field('t.*,c.cname,c.manager,c.bm')
                ->alias("t")
                ->join("left join qz_quyu as c on c.cid = t.cs")
                ->order("vipcnt desc,bm")
                ->select();
    }*/


    /**
     * 获取城市会员指标数据
     * @param  [mixed]  $map            [查询条件]
     * @param  [string] $order          [排序]
     * @param  [string] $start          [分页开始]
     * @param  [string] $end            [分页结束]
     * @return [array]  $result         [城市会员指标数组]
     */
    public function getCityPoint($map,$order,$start=null,$end=null)
    {
        if(!empty($map['cityid'])){
            $data['c.cityid'] = $map['cityid'];
        }
        if(!empty($map['department'])){
            $data['q.dept'] = $map['department'];
        }
        if(!empty($map['time'])){
            $data['c.time'] = $map['time'];
        }
        if(!empty($map['manager'])){
            $data['c.manager'] = $map['manager'];
        }
        $data['c.status'] = 0;

        $list = M("sale_citypoint")->alias('c')
                                ->join("qz_sales_city_manage as q on c.cityid = q.id")
                                ->where($data)
                                ->field("c.*,q.dept,q.city,q.ratio,q.level")
                                ->limit($start .','. $end)
                                ->order($order)
                                ->select();
        return $list;
    }


    /**
     * 获取城市会员指标有效数据总条数
     * @param   [type]  $map      [查询条件]
     * @return  [array] $num      [会员指标条数] 
     */
    public function getCityPointCount($map)
    {
        if(!empty($map['cityid'])){
            $data['c.cityid'] = $map['cityid'];
        }
        if(!empty($map['department'])){
            $data['q.dept'] = $map['department'];
        }
        if(!empty($map['time'])){
            $data['c.time'] = $map['time'];
        }
        if(!empty($map['manager'])){
            $data['c.manager'] = $map['manager'];
        }
        $data['c.status'] = 0;
        $num = M("sale_citypoint")->alias('c')
                                ->join("qz_sales_city_manage as q on c.cityid = q.id")
                                ->where($data)
                                ->count();
        return $num;
    }

    /**
     * 获取城市会员指标数据
     * @param  [string] $cityid             [修改的城市ID]
     * @param  [array]  $data               [point城市指标  time时间]
     * @return [array]  $result             [受影响的行ID]
     */
    public function editcitypoint($id,$data)
    {
        $map['id'] = $id;
        $data['point'] = $data[2];
        $data['time'] = strtotime($data[3].'-01');
        $data['lasttime'] = time();
        $data['manager'] = $_SESSION['uc_userinfo']['name'];
        $result = M("sale_citypoint")->where($map)->save($data);
        //添加编辑重点系数 17-08-21
        $city = M("sale_citypoint")->where($map)->find();
        if($data[0] == '地级市'){
            $c_data['level'] = 1;
        }elseif($data[0] == '区'){
            $c_data['level'] = 2;
        }elseif($data[0] == '县城'){
            $c_data['level'] = 3;
        }elseif($data[0] == '县级市'){
            $c_data['level'] = 4;
        }else{
            $c_data['level'] = 0;
        }
        $c_data['ratio'] = $data[1];
        $c_where['id'] = $city['cityid'];
        M("sales_city_manage")->where($c_where)->save($c_data);
        return $result;
    }

    /**
     * 修改计划月分单量
     * @param  [string] $cityid             [修改的城市ID]
     * @param  [array]  $data               [point城市指标  time时间]
     * @return [array]  $result             [受影响的行ID]
     */
    public function editjhyfds($id,$data)
    {
        $map['id'] = $id;
        $map['module'] = 4;
        $data['point'] = $data[0];
        $data['start'] = $data[1].'-01';
        $data['lasttime'] = date("Y-m-d");
        $data['uid'] = $_SESSION['uc_userinfo']['id'];
        $result = M("sales_setting_value")->where($map)->save($data);
        return $result;
    }


    /*
    *  按城市名取城市信息（qz_sales_city_manage）
    * @param    string   $name         城市名称
    * @return   array    $city         城市信息
    */
    public function getCityInCityManage($name){
        $map = array(
            'city' => array("EQ",$name)
        );
        return M('sales_city_manage')->field('*')->where($map)->find();
    }

    /*
    *  获取可查询城市（qz_sales_city_manage）
    * @param    void
    * @return   array    $city         城市信息
    */
    public function getManageCitys(){
        $order = 'id asc';
        return M('sales_city_manage')->field('id,city')->order($order)->select();
    }

    /*
    *  获取所有的城市信息，导出模板中填充使用（qz_sales_city_manage）
    * @param    void
    * @return   array    $city         城市信息
    */
    public function getAllCityInManage(){
        $order = 'id asc';
        return M('sales_city_manage')->field('city')->order($order)->select();
    }

    /*
    *  按城市名取城市信息
    * @param    string   $name         城市名称
    * @return   array    $city         城市信息
    */
    public function getCityByCName($name){
        $map = array(
            'cname' => array("EQ",$name)
        );
        return M('quyu')->field('*')->where($map)->find();
    }
    

    /*
    *  根据传入的城市ID，将所有老的城市数据废弃
    * @param    string   $cityid 城市id
    * @return   void
    */
    public function setOldValues($cityid)
    {
        $map['cityid'] = $cityid;
        $data['status'] = 1;
        $data['lasttime'] = time();
        $data['manager'] = $_SESSION['uc_userinfo']['name'];
        M('sale_citypoint')->where($map)->save($data);
    }

    /*
    *  获取城市系数的操作人
    * @param    void
    * @return   array   $manager    城市系数操作人数组
    */
    public function getCityPointManagers()
    {
        $map['status'] = 0;
        $managers = M('sale_citypoint')->where($map)->group('manager')->field('manager')->select();
        foreach ($managers as $k => $v) {
            $manager[] = $v['manager'];
        }
        return $manager;
    }

    /**
     * 获取城市QQ群列表
     * @param  [array]   $map          [查询条件:cityid,time]
     * @param  [string]   $order        [排序]
     * @param  [string]   $start        [分页开始]
     * @param  [string]   $end          [分页结束]
     * @return [array]  $list         [城市QQ群数组]
     */
    public function getQQmember($map,$order,$start=null,$end=null)
    {
        if(!empty($map['cityid'])){
            $data['q.cityid'] = $map['cityid'];
        }
        if(!empty($map['time'])){
            $data['q.time'] = $map['time'];
        }
        $list = M("sales_city_manage")->alias('c')
                                ->join("left join qz_sale_qqgroup as q on q.cityid = c.id")
                                ->where($data)
                                ->field("q.*,c.dept,c.city,c.id as mid")
                                ->limit($start .','. $end)
                                ->order($order)
                                ->select();
        return $list;
    }

    /**
     * 获取城市QQ群数量
     * @param   [array] $map        [查询条件:cityid,time]
     * @return  [array] $list       [城市QQ群数量]
     */
    public function getQQmemberCount($map)
    {
        if(!empty($map['cityid'])){
            $data['q.cityid'] = $map['cityid'];
        }
        if(!empty($map['time'])){
            $data['q.time'] = $map['time'];
        }
        $list = M("sales_city_manage")->alias('c')
                                ->join("left join qz_sale_qqgroup as q on q.cityid = c.id")
                                ->where($data)
                                ->count();
        return $list;
    }

    /**
     * 获取所有城市QQ群
     * @param  [array]      $map      [查询条件:cityid,time]
     * @param  [string]     $order    [排序]
     * @return [array]      $list     [城市QQ群数组]
     */
    public function getQQGroups($map,$order)
    {
        if(!empty($map['cityid'])){
            $data['q.cityid'] = $map['cityid'];
        }
        if(!empty($map['time'])){
            $data['q.time'] = $map['time'];
        }
        $list = M("sales_city_manage")->alias('c')
                                ->join("left join qz_sale_qqgroup as q on q.cityid = c.id")
                                ->where($data)
                                ->order($order)
                                ->field("q.*,c.dept,c.city")
                                ->select();
        return $list;
    }

    /**
     * 查询城市QQ群是否存在
     * @param   [string]    $cityid   [查询条件:cityid城市ID]
     * @return  [array]     $city     [城市QQ群数组]
     */
    public function checkCityIdsExist($cityid)
    {
        $map['cityid'] = $cityid;
        $city = M('sale_qqgroup')->WHERE($map)->find();
        return $city;
    }

    /**
     * 编辑城市QQ群内容
     * @param   string      $data       写入库的数据：cityid、name、point、num、time、manager
     * @return  array       $result     受影响行ID
    */
    public function editqqgroup($data)
    {
        //$map['id'] = $data['id'];
        //INSERT INTO TABLE (a,c) VALUES (1,3),(1,7) ON DUPLICATE KEY UPDATE c=VALUES(c);
        
        $result = M("sale_qqgroup")->add($data,array(),true);
        return $result;
    }

    /**
     * 查询部门续费月度系数
     * @param   mixed    $map         查询条件：bm(部门)  cnyf(财年)
     * @param   string   $order       排序
     * @param   integer  $start       分页开始
     * @param   integer  $end         分页结束
     * @return  array    $result      部门续费月度系数数组
    */
    public function getBMXFContent($map,$order,$start=null,$end=null)
    {
        if(empty($map['bm'])){
            unset($map['bm']);
        }
        if(empty($map['cnyf'])){
            unset($map['cnyf']);
        }else{
            $nextyear = (intval($map['cnyf']) + 1);
            $like = $map['cnyf']."%";
            $map['_string'] = "(cnyf like '".$like."' and cnyf != '".$map['cnyf']."-01') or cnyf = '".$nextyear."-01'";
            unset($map['cnyf']);
        }
        $list = M("sale_renewpoint")
                                ->where($map)
                                ->limit($start .','. $end)
                                ->order($order)
                                ->select();
        return $list;
    }

    /**
     * 查询部门续费月度系数总数
     * @param   mixed    $map       查询条件：bm(部门)  cnyf(财年)
     * @return  array    $count     条数
    */
    public function getBMXFCount($map)
    {
        if(empty($map['bm'])){
            unset($map['bm']);
        }
        if(empty($map['cnyf'])){
            unset($map['cnyf']);
        }else{
            $like = $map['cnyf']."%";
            $map['cnyf'] = array('like',$like,'AND');
        }
        $count = M("sale_renewpoint")->where($map)->count();
        return $count;
    }

    /**
     * 查询中心续费月度系数
     * @param   mixed    $map         查询条件：cnyf(财年)
     * @param   string   $order       排序
     * @param   integer  $start       分页开始
     * @param   integer  $end         分页结束
     * @return  array    $result      部门续费月度系数数组
    */
    public function getZXContent($map,$order,$start=null,$end=null)
    {
        if(empty($map['cnyf'])){
            unset($map['cnyf']);
        }else{
            $like = $map['cnyf']."%";
            $map['cnyf'] = array('like',$like,'AND');
        }
        $list = M("sale_centerpoint")
                                ->where($map)
                                ->limit($start .','. $end)
                                ->order($order)
                                ->select();
        return $list;
    }

    /**
     * 查询中心续费月度系数总数
     * @param   mixed    $map       查询条件：cnyf(财年)
     * @return  array    $count     条数
    */
    public function getZXCount($map)
    {
        if(empty($map['cnyf'])){
            unset($map['cnyf']);
        }else{
            $like = $map['cnyf']."%";
            $map['cnyf'] = array('like',$like,'AND');
        }
        $count = M("sale_centerpoint")->where($map)->count();
        return $count;
    }


    /**
     * 查询月度续费系数是否存在
     * @param   string      $cnyf       财年月度
     * @param   string      $bm         部门
     * @param   string      $type       类型  1查询部门系数  2查询中心系数
     * @return  array       $content    
    */
    public function checkXSExist($cnyf, $bm, $type)
    {
        $map['cnyf']    = $cnyf;
        $map['bm']      = $bm;
        if($type == 1){
            $content = M('sale_renewpoint')->where($map)->find();
        }else{
            $content = M('sale_centerpoint')->where($map)->find();
        }
        return $content;
    }
    
    /**
     * 查询月度续费系数是否存在
     * @param   array       $map            中心系数ID
     * @param   string      $data           编辑的字段对应的值
     * @return  array       $content    
    */
    public function editZXpoint($map, $data)
    {
        $result = M('sale_centerpoint')->where($map)->save($data);
        return $result;
    }

    /**
     * 查询月度续费系数是否存在
     * @param   array       $map            中心系数ID
     * @param   string      $data           编辑的字段对应的值
     * @return  array       $content    
    */
    public function editBMpoint($map, $data)
    {
        $result = M('sale_renewpoint')->where($map)->save($data);
        return $result;
    }

    /**
     * 取销售帐号列表
     * @return     <array>  The sale users.
     */
    public function getSaleUsers(){
        /*
        7         督导
        56        品牌师
        61        品牌督导
        62        品牌经理
        36        商务拓展督导
        39        商务经理
        40        商务文员
        41        商务品牌督导
        42        商务品牌师
        43        商务拓展城市经理
        45        商务助理
        72        商务拓展经理
        77        商务品牌经理
        71        拓展经理
        58        外销区域经理
        59        外销经理
        65        外销实习
        67        外销助理
        3         销售
        46        销售副总
        73        销售培训
        60        招商经理
        */
        $map = array(
            'uid' => array("IN",'7,56,61,62,36,39,40,41,42,43,45,72,77,71,58,59,65,67,3,46,73,60'),
            'stat' => array('EQ',1)
        );
        return M('adminuser')->field('id,uid,name')->where($map)->order('uid')->select();
    }

    /**
     * 查询所有品牌职位相关
     * @param   void       
     * @return  array      $info     返回品牌师长、品牌经理、品牌师       
    */
    public function getBrandInfo()
    {
        //品牌师长
        $bd_s = M('sales_city_manage')->alias('m')
                                    ->join('qz_adminuser as a on a.id = m.brand_division')
                                    ->group('m.brand_division')
                                    ->field('a.id,a.name,a.uid')
                                    ->select();

        //品牌团长
        $bd_t = M('sales_city_manage')->alias('m')
                                    ->join('qz_adminuser as a on a.id = m.brand_regiment')
                                    ->group('m.brand_regiment')
                                    ->field('a.id,a.name,a.uid')
                                    ->select();

        //品牌师
        $bd_p = M('sales_city_manage')->alias('m')
                                    ->join('qz_adminuser as a on a.id = m.brand_manage')
                                    ->group('m.brand_manage')
                                    ->field('a.id,a.name,a.uid')
                                    ->select();
        $result['brand_division'] = $bd_s;
        $result['brand_regiment'] = $bd_t;
        $result['brand_manage'] = $bd_p;

        return $result;
    }

    /**
     * 查询所有拓展职位相关人员
     * @param   void       
     * @return  array      $info     返回品牌师长、品牌经理、品牌师       
    */
    public function getDevInfo()
    {
        //品牌师长
        $bd_s = M('sales_city_manage')->alias('m')
                                    ->join('qz_adminuser as a on a.id = m.dev_division')
                                    ->group('m.dev_division')
                                    ->field('a.id,a.name,a.uid')
                                    ->select();

        //品牌团长
        $bd_t = M('sales_city_manage')->alias('m')
                                    ->join('qz_adminuser as a on a.id = m.dev_regiment')
                                    ->group('m.dev_regiment')
                                    ->field('a.id,a.name,a.uid')
                                    ->select();

        //品牌师
        $bd_p = M('sales_city_manage')->alias('m')
                                    ->join('qz_adminuser as a on a.id = m.dev_manage')
                                    ->group('m.dev_manage')
                                    ->field('a.id,a.name,a.uid')
                                    ->select();
        $result['dev_division'] = $bd_s;
        $result['dev_regiment'] = $bd_t;
        $result['dev_manage'] = $bd_p;

        return $result;
    }

    /**
     * 查询到期数（按分页）
     * @param   array       $map            查询条件：city  department  pshizhang ptuanzhang  pinpai  time
     * @param   string      $order          排序
     * @param   string      $start          分页开始
     * @param   string      $end            分页结束
     * @return  array       $content    
    */
    public function getExpireContents($map, $order, $start, $end)
    {
        $baseyear = $map['time'];
        $nextyear = $baseyear + 1;
        if(!empty($map['city'])){
            $where['m.id'] = $map['city'];
        }
        if(!empty($map['department'])){
            $where['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $where['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $where['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $where['m.brand_manage'] = $map['pinpai'];
        }
        $result = M('sales_city_manage')->alias('m')
                                    ->where($where)
                                    ->field('m.id,m.city,m.dept,m.brand_division,m.brand_regiment,m.brand_manage')
                                    ->limit($start .','. $end)
                                    ->order($order)
                                    ->select();

        foreach ($result as $k => $v) {
            if($v['dept'] == 1){
                $result[$k]['department'] = '商务'; 
            }else{
                $result[$k]['department'] = '外销';
            }
            $ids[] = $v['id'];
            $result[$k]['point'] = [
                $baseyear.'-02' => '',
                $baseyear.'-03' => '',
                $baseyear.'-04' => '',
                $baseyear.'-05' => '',
                $baseyear.'-06' => '',
                $baseyear.'-07' => '',
                $baseyear.'-08' => '',
                $baseyear.'-09' => '',
                $baseyear.'-10' => '',
                $baseyear.'-11' => '',
                $baseyear.'-12' => '',
                $nextyear.'-01' => '',
            ];
        }
        if(!empty($ids)){
            $point_map['v.manage_id'] = array('in',$ids); 
        }
        $point_map['v.start'] = array('like',array($baseyear.'%',$nextyear.'%'),'OR');
        $point_map['v.status'] = 1;
        $point_map['v.module'] = 1;//1到期数  2实际续费数  3实际续费月数

        //查询当前分页内所有的财年月份系数
        $points = M('sales_setting_value')->alias('v')
                                    ->join("qz_adminuser as a on a.id = v.uid")
                                    ->where($point_map)
                                    ->field('v.id,v.module,v.manage_id,v.point,v.start,v.lasttime,a.id as uid,a.name')
                                    ->select();
        foreach ($points as $k => $v) {
            $points[$k]['start'] = date('Y-m',strtotime($v['start']));
        }
        foreach ($result as $k => $v) {
            foreach ($points as $key => $value) {
                if($value['manage_id'] == $v['id']){
                    foreach ($v['point'] as $ke => $val) {
                        if($value['start'] == $ke){
                            if($value['point'] !== null) $result[$k]['point'][$ke] = number_format($value['point'],1,'.','');
                        }
                    } 
                    $lasttime[] = $value['lasttime'];
                    if(!empty($value['uid'])){
                        $result[$k]['uid'] = $value['uid'];
                        $result[$k]['manager'] = $value['name'];
                        $result[$k]['lasttime'] = max($lasttime);
                    }else{
                        $result[$k]['uid'] = '';
                        $result[$k]['manager'] = '';
                        $result[$k]['lasttime'] = ''; 
                    }
                }    
            }
        }
        return $result;
    }

    /**
     * 查询到期数全部（导出用）
     * @param   array       $map            查询条件：city  department  pshizhang ptuanzhang  pinpai  time
     * @param   string      $order          排序
     * @param   string      $start          分页开始
     * @param   string      $end            分页结束
     * @return  array       $content    
    */
    public function getExpireCon($map)
    {
        $baseyear = $map['time'];
        $nextyear = $baseyear + 1;
        if(!empty($map['city'])){
            $where['m.id'] = $map['city'];
        }
        if(!empty($map['department'])){
            $where['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $where['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $where['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $where['m.brand_manage'] = $map['pinpai'];
        }
        $result = M('sales_city_manage')->alias('m')
                                    ->where($where)
                                    ->field('m.id,m.city,m.dept,m.brand_division,m.brand_regiment,m.brand_manage')
                                    ->select();

        foreach ($result as $k => $v) {
            if($v['dept'] == 1){
                $result[$k]['department'] = '商务'; 
            }else{
                $result[$k]['department'] = '外销';
            }
            $ids[] = $v['id'];
            $result[$k]['point'] = [
                $baseyear.'-02' => '',
                $baseyear.'-03' => '',
                $baseyear.'-04' => '',
                $baseyear.'-05' => '',
                $baseyear.'-06' => '',
                $baseyear.'-07' => '',
                $baseyear.'-08' => '',
                $baseyear.'-09' => '',
                $baseyear.'-10' => '',
                $baseyear.'-11' => '',
                $baseyear.'-12' => '',
                $nextyear.'-01' => '',
            ];
            //品牌信息
            $result[$k]['brand_division'] = getSaleUserName($v['brand_division']);
            $result[$k]['brand_regiment'] = getSaleUserName($v['brand_regiment']);
            $result[$k]['brand_manage']   = getSaleUserName($v['brand_manage']);
        }
        if(!empty($ids)){
            $point_map['v.manage_id'] = array('in',$ids); 
        }
        $point_map['v.start'] = array('like',array($baseyear.'%',$nextyear.'%'),'OR');
        $point_map['v.status'] = 1;
        $point_map['v.module'] = 1;//1到期数  2实际续费数  3实际续费月数

        //查询当前分页内所有的财年月份系数
        $points = M('sales_setting_value')->alias('v')
                                    ->join("qz_adminuser as a on a.id = v.uid")
                                    ->where($point_map)
                                    ->field('v.id,v.module,v.manage_id,v.point,v.start,v.lasttime,a.id as uid,a.name')
                                    ->select();
        foreach ($points as $k => $v) {
            $points[$k]['start'] = date('Y-m',strtotime($v['start']));
        }
        foreach ($result as $k => $v) {
            foreach ($points as $key => $value) {
                if($value['manage_id'] == $v['id']){
                    foreach ($v['point'] as $ke => $val) {
                        if($value['start'] == $ke){
                            $result[$k]['point'][$ke] = $value['point'];
                        }
                    }
                    if(!empty($value['uid'])){
                        $result[$k]['uid'] = $value['uid'];
                        $result[$k]['manager'] = $value['name'];
                        $result[$k]['lasttime'] = $value['lasttime'];
                    }else{
                       $result[$k]['uid'] = '';
                        $result[$k]['manager'] = '';
                        $result[$k]['lasttime'] = ''; 
                    } 
                }  
            }
        }
        return $result;
    }

    /**
     * 查询月度续费系数条数
     * @param   array       $map            查询条件：city  department  pshizhang ptuanzhang  pinpai  time
     * @return  integer      $num    数量
    */
    public function getExpireCount($map)
    {
        if(!empty($map['city'])){
            $where['m.id'] = $map['city'];
        }
        if(!empty($map['department'])){
            $where['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $where['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $where['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $where['m.brand_manage'] = $map['pinpai'];
        }

        $num = M('sales_city_manage')->alias('m')
                                    ->where($where)
                                    ->count();
        return $num;
    }

    /**
     * 查询城市对应的ID（sales_city_manage表）
     * @param   string       $city            查询条件：cityname
     * @return  integer      $id             id
    */
    public function getCityManageId($city)
    {
        $map['city'] = $city;
        $id = M('sales_city_manage')->where($map)->find();
        return $id;
    }

    /**
     * 写入单条到期数数据（sales_city_manage表）：到期数、实际续费数、实际续费月数通用
     * @param   array        $data           要写入的数组数组
     * @return  integer      $id             id
    */
    public function writeDQInfo($data)
    {
        //查询当前城市，当前月份的值是否存在
        $map['module'] = $data['module'];
        $map['manage_id'] = $data['manage_id'];
        $map['start'] = $data['start'].'-01';
        $info = M('sales_setting_value')->where($map)->find();
        if(empty($info)){
            //写入
            $data['start'] = $data['start'].'-01';
            $result = M('sales_setting_value')->add($data);
        }else{
            //更新
            $where['id'] = $info['id'];
            $data['start'] = $data['start'].'-01';
            $result = M('sales_setting_value')->where($where)->save($data);
        }
        return $result;
    }

    /**
     * 查询实际续费数
     * @param   array       $map            查询条件：city  department  pshizhang ptuanzhang  pinpai  time
     * @param   string      $order          排序
     * @param   string      $start          分页开始
     * @param   string      $end            分页结束
     * @return  array       $content    
    */
    public function getLOFContents($map, $order, $start, $end)
    {
        $baseyear = $map['time'];
        $nextyear = $baseyear + 1;
        if(!empty($map['city'])){
            $where['m.id'] = $map['city'];
        }
        if(!empty($map['department'])){
            $where['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $where['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $where['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $where['m.brand_manage'] = $map['pinpai'];
        }
        $result = M('sales_city_manage')->alias('m')
                                    ->where($where)
                                    ->field('m.id,m.city,m.dept,m.brand_division,m.brand_regiment,m.brand_manage')
                                    ->limit($start .','. $end)
                                    ->order($order)
                                    ->select();

        foreach ($result as $k => $v) {
            if($v['dept'] == 1){
                $result[$k]['department'] = '商务'; 
            }else{
                $result[$k]['department'] = '外销';
            }
            $ids[] = $v['id'];
            $result[$k]['point'] = [
                $baseyear.'-02' => '',
                $baseyear.'-03' => '',
                $baseyear.'-04' => '',
                $baseyear.'-05' => '',
                $baseyear.'-06' => '',
                $baseyear.'-07' => '',
                $baseyear.'-08' => '',
                $baseyear.'-09' => '',
                $baseyear.'-10' => '',
                $baseyear.'-11' => '',
                $baseyear.'-12' => '',
                $nextyear.'-01' => '',
            ];
        }
        if(!empty($ids)){
            $point_map['v.manage_id'] = array('in',$ids); 
        }
        $point_map['v.start'] = array('like',array($baseyear.'%',$nextyear.'%'),'OR');
        $point_map['v.status'] = 1;
        $point_map['v.module'] = 2;//1到期数  2实际续费数  3实际续费月数

        //查询当前分页内所有的财年月份系数
        $points = M('sales_setting_value')->alias('v')
                                    ->join("qz_adminuser as a on a.id = v.uid")
                                    ->where($point_map)
                                    ->field('v.id,v.module,v.manage_id,v.point,v.start,v.lasttime,a.id as uid,a.name')
                                    ->select();
        foreach ($points as $k => $v) {
            $points[$k]['start'] = date('Y-m',strtotime($v['start']));
        }
        foreach ($result as $k => $v) {
            foreach ($points as $key => $value) {
                if($value['manage_id'] == $v['id']){
                    foreach ($v['point'] as $ke => $val) {
                        if($value['start'] == $ke){
                            if($value['point'] !== null){
                                $result[$k]['point'][$ke] = number_format($value['point'],1,'.','');
                            } 
                        }
                    } 
                    $lasttime[] = $value['lasttime'];
                    if(!empty($value['uid'])){
                        $result[$k]['uid'] = $value['uid'];
                        $result[$k]['manager'] = $value['name'];
                        $result[$k]['lasttime'] = max($lasttime);
                    }else{
                        $result[$k]['uid'] = '';
                        $result[$k]['manager'] = '';
                        $result[$k]['lasttime'] = ''; 
                    }
                }    
            }
        }
        return $result;
    }

    /**
     * 查询实际续费数条数
     * @param   array       $map            查询条件：city  department  pshizhang ptuanzhang  pinpai  time
     * @return  integer      $num    数量
    */
    public function getLOFCount($map)
    {
        if(!empty($map['city'])){
            $where['m.id'] = $map['city'];
        }
        if(!empty($map['department'])){
            $where['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $where['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $where['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $where['m.brand_manage'] = $map['pinpai'];
        }
        $num = M('sales_city_manage')->alias('m')
                                    ->where($where)
                                    ->count();
        return $num;
    }

    /**
     * 查询实际续费数全部（导出用）
     * @param   array       $map            查询条件：city  department  pshizhang ptuanzhang  pinpai  time
     * @param   string      $order          排序
     * @param   string      $start          分页开始
     * @param   string      $end            分页结束
     * @return  array       $content    
    */
    public function getLOFCon($map)
    {
        $baseyear = $map['time'];
        $nextyear = $baseyear + 1;
        if(!empty($map['city'])){
            $where['m.id'] = $map['city'];
        }
        if(!empty($map['department'])){
            $where['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $where['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $where['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $where['m.brand_manage'] = $map['pinpai'];
        }
        $result = M('sales_city_manage')->alias('m')
                                    ->where($where)
                                    ->field('m.id,m.city,m.dept,m.brand_division,m.brand_regiment,m.brand_manage')
                                    ->select();

        foreach ($result as $k => $v) {
            if($v['dept'] == 1){
                $result[$k]['department'] = '商务'; 
            }else{
                $result[$k]['department'] = '外销';
            }
            $ids[] = $v['id'];
            $result[$k]['point'] = [
                $baseyear.'-02' => '',
                $baseyear.'-03' => '',
                $baseyear.'-04' => '',
                $baseyear.'-05' => '',
                $baseyear.'-06' => '',
                $baseyear.'-07' => '',
                $baseyear.'-08' => '',
                $baseyear.'-09' => '',
                $baseyear.'-10' => '',
                $baseyear.'-11' => '',
                $baseyear.'-12' => '',
                $nextyear.'-01' => '',
            ];
            //品牌信息
            $result[$k]['brand_division'] = getSaleUserName($v['brand_division']);
            $result[$k]['brand_regiment'] = getSaleUserName($v['brand_regiment']);
            $result[$k]['brand_manage']   = getSaleUserName($v['brand_manage']);
        }
        if(!empty($ids)){
            $point_map['v.manage_id'] = array('in',$ids); 
        }
        $point_map['v.start'] = array('like',array($baseyear.'%',$nextyear.'%'),'OR');
        $point_map['v.status'] = 1;
        $point_map['v.module'] = 2;//1到期数  2实际续费数  3实际续费月数

        //查询当前分页内所有的财年月份系数
        $points = M('sales_setting_value')->alias('v')
                                    ->join("qz_adminuser as a on a.id = v.uid")
                                    ->where($point_map)
                                    ->field('v.id,v.module,v.manage_id,v.point,v.start,v.lasttime,a.id as uid,a.name')
                                    ->select();
        foreach ($points as $k => $v) {
            $points[$k]['start'] = date('Y-m',strtotime($v['start']));
        }
        foreach ($result as $k => $v) {
            foreach ($points as $key => $value) {
                if($value['manage_id'] == $v['id']){
                    foreach ($v['point'] as $ke => $val) {
                        if($value['start'] == $ke){
                            $result[$k]['point'][$ke] = $value['point'];
                        }
                    }
                    if(!empty($value['uid'])){
                        $result[$k]['uid'] = $value['uid'];
                        $result[$k]['manager'] = $value['name'];
                        $result[$k]['lasttime'] = $value['lasttime'];
                    }else{
                       $result[$k]['uid'] = '';
                        $result[$k]['manager'] = '';
                        $result[$k]['lasttime'] = ''; 
                    } 
                }  
            }
        }
        return $result;
    }

    /**
     * 查询实际续费月数
     * @param   array       $map            查询条件：city  department  pshizhang ptuanzhang  pinpai  time
     * @param   string      $order          排序
     * @param   string      $start          分页开始
     * @param   string      $end            分页结束
     * @return  array       $content    
    */
    public function getLOFMonthContents($map, $order, $start, $end)
    {
        $baseyear = $map['time'];
        $nextyear = $baseyear + 1;
        if(!empty($map['city'])){
            $where['m.id'] = $map['city'];
        }
        if(!empty($map['department'])){
            $where['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $where['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $where['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $where['m.brand_manage'] = $map['pinpai'];
        }
        $result = M('sales_city_manage')->alias('m')
                                    ->where($where)
                                    ->field('m.id,m.city,m.dept,m.brand_division,m.brand_regiment,m.brand_manage')
                                    ->limit($start .','. $end)
                                    ->order($order)
                                    ->select();

        foreach ($result as $k => $v) {
            if($v['dept'] == 1){
                $result[$k]['department'] = '商务'; 
            }else{
                $result[$k]['department'] = '外销';
            }
            $ids[] = $v['id'];
            $result[$k]['point'] = [
                $baseyear.'-02' => '',
                $baseyear.'-03' => '',
                $baseyear.'-04' => '',
                $baseyear.'-05' => '',
                $baseyear.'-06' => '',
                $baseyear.'-07' => '',
                $baseyear.'-08' => '',
                $baseyear.'-09' => '',
                $baseyear.'-10' => '',
                $baseyear.'-11' => '',
                $baseyear.'-12' => '',
                $nextyear.'-01' => '',
            ];
        }
        if(!empty($ids)){
            $point_map['v.manage_id'] = array('in',$ids); 
        }
        $point_map['v.start'] = array('like',array($baseyear.'%',$nextyear.'%'),'OR');
        $point_map['v.status'] = 1;
        $point_map['v.module'] = 3;//1到期数  2实际续费数  3实际续费月数

        //查询当前分页内所有的财年月份系数
        $points = M('sales_setting_value')->alias('v')
                                    ->join("qz_adminuser as a on a.id = v.uid")
                                    ->where($point_map)
                                    ->field('v.id,v.module,v.manage_id,v.point,v.start,v.lasttime,a.id as uid,a.name')
                                    ->select();
        foreach ($points as $k => $v) {
            $points[$k]['start'] = date('Y-m',strtotime($v['start']));
        }
        foreach ($result as $k => $v) {
            foreach ($points as $key => $value) {
                if($value['manage_id'] == $v['id']){
                    foreach ($v['point'] as $ke => $val) {
                        if($value['start'] == $ke){
                            if($value['point'] !== null){
                                $result[$k]['point'][$ke] = number_format($value['point'],1,'.','');
                            } 
                        }
                    } 
                    $lasttime[] = $value['lasttime'];
                    if(!empty($value['uid'])){
                        $result[$k]['uid'] = $value['uid'];
                        $result[$k]['manager'] = $value['name'];
                        $result[$k]['lasttime'] = max($lasttime);
                    }else{
                        $result[$k]['uid'] = '';
                        $result[$k]['manager'] = '';
                        $result[$k]['lasttime'] = ''; 
                    }
                }    
            }
        }
        return $result;
    }

    /**
     * 查询实际续费月数条数
     * @param   array       $map            查询条件：city  department  pshizhang ptuanzhang  pinpai  time
     * @return  integer      $num    数量
    */
    public function getLOFMonthCount($map)
    {
        if(!empty($map['city'])){
            $where['m.id'] = $map['city'];
        }
        if(!empty($map['department'])){
            $where['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $where['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $where['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $where['m.brand_manage'] = $map['pinpai'];
        }
        $num = M('sales_city_manage')->alias('m')
                                    ->where($where)
                                    ->count();
        return $num;
    }

    /**
     * 查询实际续费月数全部（导出用）
     * @param   array       $map            查询条件：city  department  pshizhang ptuanzhang  pinpai  time
     * @param   string      $order          排序
     * @param   string      $start          分页开始
     * @param   string      $end            分页结束
     * @return  array       $content    
    */
    public function getLOFMonthCon($map)
    {
        $baseyear = $map['time'];
        $nextyear = $baseyear + 1;
        if(!empty($map['city'])){
            $where['m.id'] = $map['city'];
        }
        if(!empty($map['department'])){
            $where['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $where['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $where['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $where['m.brand_manage'] = $map['pinpai'];
        }
        $result = M('sales_city_manage')->alias('m')
                                    ->where($where)
                                    ->field('m.id,m.city,m.dept,m.brand_division,m.brand_regiment,m.brand_manage')
                                    ->select();

        foreach ($result as $k => $v) {
            if($v['dept'] == 1){
                $result[$k]['department'] = '商务'; 
            }else{
                $result[$k]['department'] = '外销';
            }
            $ids[] = $v['id'];
            $result[$k]['point'] = [
                $baseyear.'-02' => '',
                $baseyear.'-03' => '',
                $baseyear.'-04' => '',
                $baseyear.'-05' => '',
                $baseyear.'-06' => '',
                $baseyear.'-07' => '',
                $baseyear.'-08' => '',
                $baseyear.'-09' => '',
                $baseyear.'-10' => '',
                $baseyear.'-11' => '',
                $baseyear.'-12' => '',
                $nextyear.'-01' => '',
            ];
            //品牌信息
            $result[$k]['brand_division'] = getSaleUserName($v['brand_division']);
            $result[$k]['brand_regiment'] = getSaleUserName($v['brand_regiment']);
            $result[$k]['brand_manage']   = getSaleUserName($v['brand_manage']);
        }
        if(!empty($ids)){
            $point_map['v.manage_id'] = array('in',$ids); 
        }
        $point_map['v.start'] = array('like',array($baseyear.'%',$nextyear.'%'),'OR');
        $point_map['v.status'] = 1;
        $point_map['v.module'] = 3;//1到期数  2实际续费数  3实际续费月数

        //查询当前分页内所有的财年月份系数
        $points = M('sales_setting_value')->alias('v')
                                    ->join("qz_adminuser as a on a.id = v.uid")
                                    ->where($point_map)
                                    ->field('v.id,v.module,v.manage_id,v.point,v.start,v.lasttime,a.id as uid,a.name')
                                    ->select();
        foreach ($points as $k => $v) {
            $points[$k]['start'] = date('Y-m',strtotime($v['start']));
        }
        foreach ($result as $k => $v) {
            foreach ($points as $key => $value) {
                if($value['manage_id'] == $v['id']){
                    foreach ($v['point'] as $ke => $val) {
                        if($value['start'] == $ke){
                            $result[$k]['point'][$ke] = $value['point'];
                        }
                    }
                    if(!empty($value['uid'])){
                        $result[$k]['uid'] = $value['uid'];
                        $result[$k]['manager'] = $value['name'];
                        $result[$k]['lasttime'] = $value['lasttime'];
                    }else{
                       $result[$k]['uid'] = '';
                        $result[$k]['manager'] = '';
                        $result[$k]['lasttime'] = ''; 
                    } 
                }  
            }
        }
        return $result;
    }

    /**
     * 获取城市会员指标数据
     * @param  [array]  $map                [查询条件]
     * @param  [array]  $data               [point城市指标]
     * @return [array]  $result             [受影响的行ID]
     */
    public function editexpirepoint($map,$data)
    {
        //查询是否存在
        $info = M("sales_setting_value")->where($map)->find();
        if(empty($info)){
            $data['module']         = $map['module'];
            $data['manage_id']      = $map['manage_id'];
            $data['start']          = $map['start'];
            $data['status']         = 1;
            $result = M("sales_setting_value")->add($data);
        }else{
            $result = M("sales_setting_value")->where($map)->save($data);
        }   
        
        return $result;
    }

    /**
     * 查询实际续费月数
     * @param   array       $map            查询条件：city  department  pshizhang ptuanzhang  pinpai  time
     * @param   string      $order          排序
     * @param   string      $start          分页开始
     * @param   string      $end            分页结束
     * @return  array       $content    
    */
    public function getRenewMonthPointContents($map, $order, $start, $end)
    {
        $baseyear = $map['time'];
        $nextyear = $baseyear + 1;
        if(!empty($map['city'])){
            $where['m.id'] = $map['city'];
        }
        if(!empty($map['department'])){
            $where['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $where['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $where['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $where['m.brand_manage'] = $map['pinpai'];
        }
        $result = M('sales_city_manage')->alias('m')
                                    ->where($where)
                                    ->field('m.id,m.city,m.dept,m.brand_division,m.brand_regiment,m.brand_manage')
                                    ->limit($start .','. $end)
                                    ->order($order)
                                    ->select();

        foreach ($result as $k => $v) {
            if($v['dept'] == 1){
                $result[$k]['department'] = '商务'; 
            }else{
                $result[$k]['department'] = '外销';
            }
            $ids[] = $v['id'];
            $result[$k]['point'] = [
                $baseyear.'-02' => '',
                $baseyear.'-03' => '',
                $baseyear.'-04' => '',
                $baseyear.'-05' => '',
                $baseyear.'-06' => '',
                $baseyear.'-07' => '',
                $baseyear.'-08' => '',
                $baseyear.'-09' => '',
                $baseyear.'-10' => '',
                $baseyear.'-11' => '',
                $baseyear.'-12' => '',
                $nextyear.'-01' => '',
            ];
        }
        if(!empty($ids)){
            $point_map['v.manage_id'] = array('in',$ids); 
        }
        $point_map['v.start'] = array('like',array($baseyear.'%',$nextyear.'%'),'OR');
        $point_map['v.status'] = 1;
        $point_map['v.module'] = 6;//1到期数  2实际续费数  3实际续费月数 6续费月数指标

        //查询当前分页内所有的财年月份系数
        $points = M('sales_setting_value')->alias('v')
                                    ->join("qz_adminuser as a on a.id = v.uid")
                                    ->where($point_map)
                                    ->field('v.id,v.module,v.manage_id,v.point,v.start,v.lasttime,a.id as uid,a.name')
                                    ->select();
        foreach ($points as $k => $v) {
            $points[$k]['start'] = date('Y-m',strtotime($v['start']));
        }
        foreach ($result as $k => $v) {
            foreach ($points as $key => $value) {
                if($value['manage_id'] == $v['id']){
                    foreach ($v['point'] as $ke => $val) {
                        if($value['start'] == $ke){
                            if($value['point'] !== null){
                                $result[$k]['point'][$ke] = number_format($value['point'],1,'.','');
                            } 
                        }
                    } 
                    $lasttime[] = $value['lasttime'];
                    if(!empty($value['uid'])){
                        $result[$k]['uid'] = $value['uid'];
                        $result[$k]['manager'] = $value['name'];
                        $result[$k]['lasttime'] = max($lasttime);
                    }else{
                        $result[$k]['uid'] = '';
                        $result[$k]['manager'] = '';
                        $result[$k]['lasttime'] = ''; 
                    }
                }    
            }
        }
        return $result;
    }

    /**
     * 查询实际续费月数全部（导出用）
     * @param   array       $map            查询条件：city  department  pshizhang ptuanzhang  pinpai  time
     * @param   string      $order          排序
     * @param   string      $start          分页开始
     * @param   string      $end            分页结束
     * @return  array       $content    
    */
    public function getRenewMonthPointCon($map)
    {
        $baseyear = $map['time'];
        $nextyear = $baseyear + 1;
        if(!empty($map['city'])){
            $where['m.id'] = $map['city'];
        }
        if(!empty($map['department'])){
            $where['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $where['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $where['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $where['m.brand_manage'] = $map['pinpai'];
        }
        $result = M('sales_city_manage')->alias('m')
                                    ->where($where)
                                    ->field('m.id,m.city,m.dept,m.brand_division,m.brand_regiment,m.brand_manage')
                                    ->select();

        foreach ($result as $k => $v) {
            if($v['dept'] == 1){
                $result[$k]['department'] = '商务'; 
            }else{
                $result[$k]['department'] = '外销';
            }
            $ids[] = $v['id'];
            $result[$k]['point'] = [
                $baseyear.'-02' => '',
                $baseyear.'-03' => '',
                $baseyear.'-04' => '',
                $baseyear.'-05' => '',
                $baseyear.'-06' => '',
                $baseyear.'-07' => '',
                $baseyear.'-08' => '',
                $baseyear.'-09' => '',
                $baseyear.'-10' => '',
                $baseyear.'-11' => '',
                $baseyear.'-12' => '',
                $nextyear.'-01' => '',
            ];
            //品牌信息
            $result[$k]['brand_division'] = getSaleUserName($v['brand_division']);
            $result[$k]['brand_regiment'] = getSaleUserName($v['brand_regiment']);
            $result[$k]['brand_manage']   = getSaleUserName($v['brand_manage']);
        }
        if(!empty($ids)){
            $point_map['v.manage_id'] = array('in',$ids); 
        }
        $point_map['v.start'] = array('like',array($baseyear.'%',$nextyear.'%'),'OR');
        $point_map['v.status'] = 1;
        $point_map['v.module'] = 6;//1到期数  2实际续费数  3实际续费月数 6续费月数指标

        //查询当前分页内所有的财年月份系数
        $points = M('sales_setting_value')->alias('v')
                                    ->join("qz_adminuser as a on a.id = v.uid")
                                    ->where($point_map)
                                    ->field('v.id,v.module,v.manage_id,v.point,v.start,v.lasttime,a.id as uid,a.name')
                                    ->select();
        foreach ($points as $k => $v) {
            $points[$k]['start'] = date('Y-m',strtotime($v['start']));
        }
        foreach ($result as $k => $v) {
            foreach ($points as $key => $value) {
                if($value['manage_id'] == $v['id']){
                    foreach ($v['point'] as $ke => $val) {
                        if($value['start'] == $ke){
                            $result[$k]['point'][$ke] = $value['point'];
                        }
                    }
                    if(!empty($value['uid'])){
                        $result[$k]['uid'] = $value['uid'];
                        $result[$k]['manager'] = $value['name'];
                        $result[$k]['lasttime'] = $value['lasttime'];
                    }else{
                       $result[$k]['uid'] = '';
                        $result[$k]['manager'] = '';
                        $result[$k]['lasttime'] = ''; 
                    } 
                }  
            }
        }
        return $result;
    }

    /**
     * 写入日志记录
     * @param   array       $data        生成的日志信息数组
     * @return  void    
    */
    public function addLog($data)
    {
        return M('sales_log')->add($data);
    }

    /**
     * 写入日志记录
     * @param   string       $type        日志类型（按操作页面）
     * @return  array        $result      日志数组   
    */
    public function getHistoryLog($type)
    {
        $where['optype'] = $type;
        $order = 'id desc';
        $list = M('sales_log')->where($where)->order($order)->limit(10)->select();
        return $list;
    }

    /**
     * 写入城市重点系数和类型
     * @param   string       $city        城市名称
     * @param   array        $data        修改的值
     * @return  array        $result      日志数组   
    */
    public function setCityManagerValues($city,$data)
    {
        if(!empty($city)){
            $map['city'] = $city;
            M("sales_city_manage")->where($map)->save($data);
        }
    }

}

