<?php

//城市会员

namespace Home\Model;
Use Think\Model;

class SalecountModel extends Model{

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
     * 查询所有品牌职位相关
     * @param   void       
     * @return  array      $info     返回品牌师长、品牌经理、品牌师       
    */
    public function getManagerInfo()
    {
        //品牌师长
        $bd_s = M('sales_city_manage')->alias('m')
                                    ->join('LEFT JOIN qz_adminuser as a on a.id = m.brand_division')
                                    ->where('m.brand_division != ""')
                                    ->group('m.brand_division')
                                    ->field('a.id,a.name,a.uid')
                                    ->select();

        //品牌团长
        $bd_t = M('sales_city_manage')->alias('m')
                                    ->join('LEFT JOIN qz_adminuser as a on a.id = m.brand_regiment')
                                    ->where('m.brand_regiment != ""')
                                    ->group('m.brand_regiment')
                                    ->field('a.id,a.name,a.uid')
                                    ->select();

        //品牌师
        $bd_p = M('sales_city_manage')->alias('m')
                                    ->join('LEFT JOIN qz_adminuser as a on a.id = m.brand_manage')
                                    ->where('m.brand_manage != ""')
                                    ->group('m.brand_manage')
                                    ->field('a.id,a.name,a.uid')
                                    ->select();
        //城市经理
        $dev_m = M('sales_city_manage')->alias('m')
                                    ->join('LEFT JOIN qz_adminuser as a on a.id = m.dev_manage')
                                    ->where('m.dev_manage != ""')
                                    ->group('m.dev_manage')
                                    ->field('a.id,a.name,a.uid')
                                    ->select();
        //拓师长
        $dev_d = M('sales_city_manage')->alias('m')
                                    ->join('LEFT JOIN qz_adminuser as a on a.id = m.dev_division')
                                    ->where('m.dev_division != ""')
                                    ->group('m.dev_division')
                                    ->field('a.id,a.name,a.uid')
                                    ->select();
        //拓团长
        $dev_r = M('sales_city_manage')->alias('m')
                                    ->join('LEFT JOIN qz_adminuser as a on a.id = m.dev_regiment')
                                    ->where('m.dev_regiment != ""')
                                    ->group('m.dev_regiment')
                                    ->field('a.id,a.name,a.uid')
                                    ->select();
        $result['brand_division'] = $bd_s;
        $result['brand_regiment'] = $bd_t;
        $result['brand_manage'] = $bd_p;
        $result['dev_division'] = $dev_d;
        $result['dev_regiment'] = $dev_r;
        $result['dev_manage'] = $dev_m;

        return $result;
    }

    /**
     * 根据当前登录的账号职位查询所有下属职位相关信息
     * @param   void       
     * @return  array      $info     返回当前及下属职位信息       
    */
    public function getAdminsByPosition()
    {
        $map['role_name'] = $_SESSION['uc_userinfo']['role_name'];
        $map['dept'] = getUserDepartment();
        $position = getUserPosition($map['dept'],$map['role_name']);
        $partArr = [
            'corps'             => array('brand_division','brand_regiment','brand_manage','dev_division','dev_regiment','dev_manage'),
            'brand_division'    => array('brand_division','brand_regiment','brand_manage'),
            'brand_regiment'    => array('brand_regiment','brand_manage'),
            'brand_manage'      => array('brand_manage'),
            'dev_division'      => array('dev_division','dev_regiment','dev_manage'),
            'dev_regiment'      => array('dev_regiment','dev_manage'),
            'dev_manage'        => array('dev_manage')
        ];
        if ($map['dept'] == 0) {
            //超级管理员权限
            $result = $this->getManagerInfo();//查询所有的已经设置的
        }else{
            $part = $partArr[$position];//要查询的字段
            $where_str = 'm.'.$position;//岗位查询条件
            $where[$where_str] = $_SESSION['uc_userinfo']['id'];
            $where['m.dept'] = $map['dept'];
            foreach ($part as $k => $v) {
                if($position != $v){
                    $where['m.'.$v] = array('NEQ','');
                }
                $group = 'm.'.$v;
                $data = M('sales_city_manage')->alias('m')
                                    ->join('LEFT JOIN qz_adminuser as a on a.id = m.'.$v)
                                    ->where($where)
                                    ->group($group)
                                    ->field('a.id,a.name,a.uid')
                                    ->select();
                $result[$v] = $data;
                if($position != $v){
                    unset($where['m.'.$v]);
                }
            }
        }

        $last_data['brand_division']    = $result['brand_division'];
        $last_data['brand_regiment']    = $result['brand_regiment'];
        $last_data['brand_manage']      = $result['brand_manage'];
        $last_data['dev_division']      = $result['dev_division'];
        $last_data['dev_regiment']      = $result['dev_regiment'];
        $last_data['dev_manage']        = $result['dev_manage'];

        return $last_data;    
    }

    /**
     * 获取城市会员合作时长数据
     * @param  [mixed]  $map            [查询条件]
     * @param  [string] $order          [排序表里的排序字段]
     * @param  [string] $start          [分页开始]
     * @param  [string] $end            [分页结束]
     * @return [array]  $result         [城市会员指标数组]
     */
    public function getCityVipConn($map,$order=null,$start=null,$end=null,$type=null)
    {
        //1,根据城市名称，查询城市的会员数量
        //2,关联查询城市的职位信息 qz_sales_city_manage
        //3,查询会员合同时长qz_sale_contract
        //4,计算出占比
        $search_time = date('Y-m-d',time());

        //if(!empty($map['city'])){
            $map2['g.city'] = $map['city'];
        //}
        if(!empty($map['department'])){
            $map2['g.dept'] = $map['department'];
        }
        if(!empty($map['tshizhang'])){
            $map2['g.dev_division'] = $map['tshizhang'];
        }
        if(!empty($map['ttuanzhang'])){
            $map2['g.dev_regiment'] = $map['ttuanzhang'];
        }
        if(!empty($map['csjl'])){
            $map2['g.dev_manage'] = $map['csjl'];
        }
        if(!empty($map['pshizhang'])){
            $map2['g.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $map2['g.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $map2['g.brand_manage'] = $map['pinpai'];
        }

        $buildSql1 = M("sales_city_manage")->alias("g")
                            ->join('LEFT JOIN qz_adminuser as a1 on a1.id = g.dev_division')
                            ->join('LEFT JOIN qz_adminuser as a2 on a2.id = g.dev_regiment')
                            ->join('LEFT JOIN qz_adminuser as a3 on a3.id = g.dev_manage')
                            ->join('LEFT JOIN qz_adminuser as a4 on a4.id = g.brand_division')
                            ->join('LEFT JOIN qz_adminuser as a5 on a5.id = g.brand_regiment')
                            ->join('LEFT JOIN qz_adminuser as a6 on a6.id = g.brand_manage')
                            ->join('LEFT JOIN qz_sales_city_paixu b ON g.id = b.cityid')
                            ->join('LEFT JOIN qz_quyu c ON g.city = c.cname ')
                            ->where($map2)
                            ->field("g.id,g.city,g.dept,c.cid,a1. NAME AS dev_division_name,a2. NAME AS dev_regiment_name,a3. NAME AS dev_manage_name,a4. NAME AS brand_division_name,a5. NAME AS brand_regiment_name,a6. NAME AS brand_manage_name,".$order)
                            ->buildSql();
        $result = M("sales_city_manage")->table($buildSql1)->alias("m")
                        ->join('LEFT JOIN qz_user AS d ON d.cs = m.cid  and d.classid in (3,6) and d.`on` = 2')
                        ->join('LEFT JOIN qz_user_company AS e ON d.id = e.userid')
                        ->field("m.*,sum(if(e.fake = 0,e.viptype,0)) as vipcnt,count(if(e.fake = 0,d.id,null)) as realvipnum")
                        ->order("m.px desc,realvipnum desc")
                        ->group("m.id")
                        ->limit($start .','. $end)
                        ->select();
        
        foreach ($result as $k => $v) {
            $city_ids[] = $v['cid'];
        }
        //3,按照合同时长，分别统计每个城市的会员数量
        $com_num_map['c.time'] = $search_time;//'a.cs' => array('in',$city_ids)   
        $com_num_map['c.city_id'] = array('in',$city_ids);
        if(!empty($type)){
            $com_num_map['contract_type'] = $type;
        }
        if(!empty($city_ids)){
            $com_num = M('sale_contract')->alias('c')
                                    ->join("inner join qz_user_company b on c.company_id = b.userid")
                                    ->where($com_num_map)
                                    ->field('c.city_id,sum(if(c.contract_time > 364,b.viptype,null)) as gtyear,sum(if(c.contract_time < 365 and c.contract_time > 181,b.viptype,null)) as gthalfyear,sum(if(c.contract_time < 182 and c.contract_time > 89,b.viptype,null)) as lthalfyear,sum(if(c.contract_time < 90,b.viptype,null)) as ltmonth')
                                    ->group('city_id')
                                    ->select();
        }
        //合并数据
        foreach ($result as $k => $v) {
            foreach ($com_num as $key => $value) {
                if($value['city_id'] == $v['cid']){
                    $result[$k]['gtyear'] = $value['gtyear'];
                    $result[$k]['gthalfyear'] = $value['gthalfyear'];
                    $result[$k]['lthalfyear'] = $value['lthalfyear'];
                    $result[$k]['ltmonth'] = $value['ltmonth'];
                }
            }
            if($v['dept'] == 1){
                $result[$k]['department'] = '商务';
            }else{
                $result[$k]['department'] = '外销';
            }
            $result[$k]['gtyear_percent'] = ($result[$k]['gtyear']/$result[$k]['realvipnum'])*100;
            $result[$k]['gthalfyear_percent'] = ($result[$k]['gthalfyear']/$result[$k]['realvipnum'])*100;
            $result[$k]['lthalfyear_percent'] = ($result[$k]['lthalfyear']/$result[$k]['realvipnum'])*100;
            $result[$k]['ltmonth_percent'] = ($result[$k]['ltmonth']/$result[$k]['realvipnum'])*100;
            if(empty($result[$k]['gtyear'])){
                $result[$k]['gtyear'] = 0;
            }
            if(empty($result[$k]['gthalfyear'])){
               $result[$k]['gthalfyear'] = 0;
            }
            if(empty($result[$k]['lthalfyear'])){
                $result[$k]['lthalfyear'] = 0;
            }
            if(empty($result[$k]['ltmonth'])){
                $result[$k]['ltmonth'] = 0;
            }
            $result[$k]['gtyear_percent'] = (number_format($result[$k]['gtyear_percent'],1,'.','')).'%';
            $result[$k]['gthalfyear_percent'] = (number_format($result[$k]['gthalfyear_percent'],1,'.','')).'%';
            $result[$k]['lthalfyear_percent'] = (number_format($result[$k]['lthalfyear_percent'],1,'.','')).'%';
            $result[$k]['ltmonth_percent'] = (number_format($result[$k]['ltmonth_percent'],1,'.','')).'%';
        }
        //2,查询城市会员数
        return $result;
    }

    /**
     * 获取所有城市会员合作时长数据（导出用）
     * @param  [mixed]  $map            [查询条件]
     * @param  [string] $order          [排序]
     * @return [array]  $result         [城市会员指标数组]
     */
    public function getCityVipAll($map,$order,$type=null,$condition=null)
    {
        if(!empty($map['city'])){
            $a_map['m.city'] = $map['city'];
        }else{
            //if(!empty($condition)){
                $a_map['m.city'] = $condition['city'];
            //}   
        }
        if(!empty($map['department'])){
            $a_map['m.dept'] = $map['department'];
        }
        if(!empty($map['tshizhang'])){
            $a_map['m.dev_division'] = $map['tshizhang'];
        }
        if(!empty($map['ttuanzhang'])){
            $a_map['m.dev_regiment'] = $map['ttuanzhang'];
        }
        if(!empty($map['csjl'])){
            $a_map['m.dev_manage'] = $map['csjl'];
        }
        if(!empty($map['pshizhang'])){
            $a_map['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $a_map['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $a_map['m.brand_manage'] = $map['pinpai'];
        }
        
        //合同状态  2 -> 本次合同时间
        //1,查询城市，及拓展、品牌人员信息
        $result = M('sales_city_manage')->alias('m')
                                        ->join('LEFT JOIN qz_quyu as q on q.cname = m.city')
                                        ->join('LEFT JOIN qz_adminuser as a1 on a1.id = m.dev_division')
                                        ->join('LEFT JOIN qz_adminuser as a2 on a2.id = m.dev_regiment')
                                        ->join('LEFT JOIN qz_adminuser as a3 on a3.id = m.dev_manage')
                                        ->join('LEFT JOIN qz_adminuser as a4 on a4.id = m.brand_division')
                                        ->join('LEFT JOIN qz_adminuser as a5 on a5.id = m.brand_regiment')
                                        ->join('LEFT JOIN qz_adminuser as a6 on a6.id = m.brand_manage')
                                        ->where($a_map)
                                        ->field('m.*,q.cid,a1.name as dev_division_name,a2.name as dev_regiment_name,a3.name as dev_manage_name,a4.name as brand_division_name,a5.name as brand_regiment_name,a6.name as brand_manage_name')
                                        ->select();
        //var_dump(M()->getLastSql());
        //2,查询城市会员数
        foreach ($result as $k => $v) {
            $city_ids[] = $v['cid'];
            if($v['dept'] == 1){
                $result[$k]['department'] = '商务';
            }else{
                $result[$k]['department'] = '外销';
            }
        }

        if(!empty($city_ids)){

            $map = array(
                "a.classid" =>array('IN',array(3,6)),
                "b.fake" => array("EQ",0),
                "a.on" => '2',
                'a.cs' => array('in',$city_ids)    
            );
            $users = M("user")->where($map)->alias("a")
                 ->join("inner join qz_user_company b on a.id = b.userid")
                 ->field("a.cs,sum(if(a.on = 2,b.viptype,null)) as vipcnt,count(if(a.on = 2,a.id,null)) as realvipnum")
                 ->order("vipcnt desc")
                 ->group("a.cs")
                 ->select();
        
            foreach ($users as $key => $value) {
                $data[$value['cs']] = $value; 
            }
            foreach ($result as $k => $v) {
                $result[$k]['realvipnum'] = $data[$v['cid']]['vipcnt'];
                if(empty($result[$k]['realvipnum'])){
                    $result[$k]['realvipnum'] = 0;
                }
            }
            //3,按照合同时长，分别统计每个城市的会员数量
            if(empty($search_time)){
                $search_time = date('Y-m-d',time());
            }
            $com_num_map['c.time'] = $search_time;//'a.cs' => array('in',$city_ids)   
            $com_num_map['c.city_id'] = array('in',$city_ids);
            if(!empty($type)){
                $com_num_map['c.contract_type'] = $type;
            }
            /*$com_num = M('sale_contract')->where($com_num_map)
                                        ->field('city_id,count(if(contract_time > 364,id,null)) as gtyear,count(if(contract_time < 365 and contract_time > 181,id,null)) as gthalfyear,count(if(contract_time < 182 and contract_time > 89,id,null)) as lthalfyear,count(if(contract_time < 90,id,null)) as ltmonth')
                                        ->group('city_id')
                                        ->select();*/
            $com_num = M('sale_contract')->alias('c')
                                        ->join("inner join qz_user_company b on c.company_id = b.userid")
                                        ->where($com_num_map)
                                        ->field('c.city_id,sum(if(c.contract_time > 364,b.viptype,null)) as gtyear,sum(if(c.contract_time < 365 and c.contract_time > 181,b.viptype,null)) as gthalfyear,sum(if(c.contract_time < 182 and c.contract_time > 89,b.viptype,null)) as lthalfyear,sum(if(c.contract_time < 90,b.viptype,null)) as ltmonth')
                                        ->group('city_id')
                                        ->select();
            foreach ($result as $k => $v) {
                foreach ($com_num as $key => $value) {
                    if($value['city_id'] == $v['cid']){
                        $result[$k]['gtyear'] = $value['gtyear'];
                        $result[$k]['gthalfyear'] = $value['gthalfyear'];
                        $result[$k]['lthalfyear'] = $value['lthalfyear'];
                        $result[$k]['ltmonth'] = $value['ltmonth'];
                    }
                }
                $result[$k]['gtyear_percent'] = round(($result[$k]['gtyear']/$result[$k]['realvipnum']),3)*100;
                $result[$k]['gthalfyear_percent'] = round(($result[$k]['gthalfyear']/$result[$k]['realvipnum']),3)*100;
                $result[$k]['lthalfyear_percent'] = round(($result[$k]['lthalfyear']/$result[$k]['realvipnum']),3)*100;
                $result[$k]['ltmonth_percent'] = round(($result[$k]['ltmonth']/$result[$k]['realvipnum']),3)*100;
                if(empty($result[$k]['gtyear'])){
                    $result[$k]['gtyear'] = 0;
                }
                if(empty($result[$k]['gthalfyear'])){
                   $result[$k]['gthalfyear'] = 0;
                }
                if(empty($result[$k]['lthalfyear'])){
                    $result[$k]['lthalfyear'] = 0;
                }
                if(empty($result[$k]['ltmonth'])){
                    $result[$k]['ltmonth'] = 0;
                }
                $result[$k]['gtyear_percent']       = number_format($result[$k]['gtyear_percent'],1).'%';
                $result[$k]['gthalfyear_percent']   = number_format($result[$k]['gthalfyear_percent'],1).'%';
                $result[$k]['lthalfyear_percent']   = number_format($result[$k]['lthalfyear_percent'],1).'%';
                $result[$k]['ltmonth_percent']      = number_format($result[$k]['ltmonth_percent'],1).'%';
            }
        }
        return $result;
    }


    /**
     * 获取城市会员合作时长数据总条数
     * @param  [mixed]  $map            [查询条件]
     * @return [array]  $result         [城市会员指标数量]
     */
    public function getCityVipCount($map)
    {
        //if(!empty($map['city'])){
            $a_map['city'] = $map['city'];
        //}
        if(!empty($map['department'])){
            $a_map['dept'] = $map['department'];
        }
        if(!empty($map['tshizhang'])){
            $a_map['dev_division'] = $map['tshizhang'];
        }
        if(!empty($map['ttuanzhang'])){
            $a_map['dev_regiment'] = $map['ttuanzhang'];
        }
        if(!empty($map['csjl'])){
            $a_map['dev_manage'] = $map['csjl'];
        }
        if(!empty($map['pshizhang'])){
            $a_map['brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $a_map['brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $a_map['brand_manage'] = $map['pinpai'];
        }
        $result = M('sales_city_manage')->where($a_map)->count();
        //var_dump(M()->getLastSql());
        return $result;
    }

    /**
     * 获取历史会员合作时长数据（来自qz_sale_cityvips）
     * @param  [string] $start          [分页开始]
     * @param  [string] $end            [分页结束]
     * @param  [mixed]  $map            [查询条件]
     * @param  [string] $type           [数据类型 1合作时长 2新签时长 3续费时长]
     * @return [array]  $result         [城市会员指标数组]
     */
    public function getHistoryCityVip($start,$end,$map,$type)
    {
        //if(!empty($map['city'])){
            $where['c.city'] = $map['city'];
        //}
        if(!empty($map['department'])){
            $where['c.dept'] = $map['department'];
        }
        if(!empty($map['tshizhang'])){
            $where['c.dev_division'] = $map['tshizhang'];
        }
        if(!empty($map['ttuanzhang'])){
            $where['c.dev_regiment'] = $map['ttuanzhang'];
        }
        if(!empty($map['csjl'])){
            $where['c.dev_manage'] = $map['csjl'];
        }
        if(!empty($map['pshizhang'])){
            $where['c.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $where['c.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $where['c.brand_manage'] = $map['pinpai'];
        }
        if(!empty($map['time'])){
            $where['c.time'] = strtotime(($map['time'].'-01'));
        }
        $where['c.type'] = $type;//1为会员合作时长  2为新签合作时长  3为续费合作时长
        if($type == 1){
            $order = 'p.px2 desc,c.realvipnum+0 desc';
        }elseif($type == 2){
            $order = 'p.px3 desc,c.realvipnum+0 desc';
        }elseif($type == 3){
            $order = 'p.px4 desc,c.realvipnum+0 desc';
        }
        $result = M('sale_cityvips')->alias('c')
                                    ->join("qz_sales_city_manage as m on m.city = c.city")
                                    ->join("qz_sales_city_paixu as p on m.id = p.cityid")
                                    ->where($where)
                                    ->field("c.*,m.ratio")
                                    ->limit($start .','. $end)
                                    ->order($order)
                                    ->select();
        return $result;
    }

    /**
     * 获取历史会员合作时长数据（来自qz_sale_cityvips）
     * @param  [mixed]  $map            [查询条件]
     * @param  [string] $type           [数据类型 1合作时长 2新签时长 3续费时长]
     * @return [array]  $result         [城市会员指标数组]
     */
    public function getHistoryVipAll($map,$type)
    {
        //if(!empty($map['city'])){
            $where['c.city'] = $map['city'];
        //}
        if(!empty($map['department'])){
            $where['c.dept'] = $map['department'];
        }
        if(!empty($map['tshizhang'])){
            $where['c.dev_division'] = $map['tshizhang'];
        }
        if(!empty($map['ttuanzhang'])){
            $where['c.dev_regiment'] = $map['ttuanzhang'];
        }
        if(!empty($map['csjl'])){
            $where['c.dev_manage'] = $map['csjl'];
        }
        if(!empty($map['pshizhang'])){
            $where['c.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $where['c.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $where['c.brand_manage'] = $map['pinpai'];
        }
        if(!empty($map['time'])){
            $where['c.time'] = strtotime(($map['time'].'-01'));
        }
        $where['c.type'] = $type;//1为会员合作时长  2为新签合作时长  3为续费合作时长
        $result = M('sale_cityvips')->alias('c')
                                    ->join("qz_sales_city_manage as m on m.city = c.city")
                                    ->where($where)
                                    ->field("c.*,m.ratio")
                                    ->order('m.ratio desc,c.realvipnum+0 desc')
                                    ->select();
        return $result;
    }

    /**
     * 获取城市会员合作时长数据总条数
     * @param  [mixed]  $map            [查询条件]     
     * @param  [string] $type           [数据类型 1合作时长 2新签时长 3续费时长]
     * @return [array]  $result         [城市会员指标数量]
     */
    public function getHistoryVipNum($map,$type)
    {
        //if(!empty($map['city'])){
            $where['c.city'] = $map['city'];
        //}
        if(!empty($map['department'])){
            $where['c.dept'] = $map['department'];
        }
        if(!empty($map['tshizhang'])){
            $where['c.dev_division'] = $map['tshizhang'];
        }
        if(!empty($map['ttuanzhang'])){
            $where['c.dev_regiment'] = $map['ttuanzhang'];
        }
        if(!empty($map['csjl'])){
            $where['c.dev_manage'] = $map['csjl'];
        }
        if(!empty($map['pshizhang'])){
            $where['c.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $where['c.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $where['c.brand_manage'] = $map['pinpai'];
        }
        if(!empty($map['time'])){
            $where['c.time'] = strtotime(($map['time'].'-01'));
        }
        $where['c.type'] = $type;//1为会员合作时长  2为新签合作时长  3为续费合作时长
        $result = M('sale_cityvips')->alias('c')->where($where)->count();
        //var_dump(M()->getLastSql());
        return $result;
    }

    /**
     * 获取城市会员合作时长数据总条数
     * @param  [string]  $time              [查询条件:时间精确到月2017-06]  
     * @param  [string]  $dept              [查询条件:部门 0全部 1商务 2外销]   
     * @param  [string]  $type              [数据类型 1合作时长 2新签时长 3续费时长]
     * @param  [array]   $condition         个人城市权限数组
     * @return [array]   $result            [城市会员指标数量]
     */
    public function getVipTableInfo($time,$dept=null,$type=null,$condition=null)
    {
        $where['time'] = strtotime($time.'-01');
        $where['type'] = $type;
        if(!empty($dept) && $dept != 0 ){
            $where['dept'] = $dept;
        }
        //if(!empty($condition)){
            $where['city'] = $condition['city'];
        //}
        $arr = M('sale_cityvips')->where($where)->field('time,sum(realvipnum) as realvipnum,sum(gtyear) as gtyear,sum(gthalfyear) as gthalfyear,sum(lthalfyear) as lthalfyear,sum(ltmonth) as ltmonth')->select();
        $result['month'] = $time;
        if(!empty($arr[0]['time'])){
            $result['realvipnum'] = $arr[0]['realvipnum'];

            $result['gtyear'] = $arr[0]['gtyear'];
            $result['gtyear_percent'] = ($arr[0]['gtyear']/$arr[0]['realvipnum'])*100;
            $result['gtyear_percent'] = (number_format($result['gtyear_percent'],1,'.','')).'%';

            $result['gthalfyear'] = $arr[0]['gthalfyear'];
            $result['gthalfyear_percent'] = ($arr[0]['gthalfyear']/$arr[0]['realvipnum'])*100;
            $result['gthalfyear_percent'] = (number_format($result['gthalfyear_percent'],1,'.','')).'%';

            $result['lthalfyear'] = $arr[0]['lthalfyear'];
            $result['lthalfyear_percent'] = ($arr[0]['lthalfyear']/$arr[0]['realvipnum'])*100;
            $result['lthalfyear_percent'] = (number_format($result['lthalfyear_percent'],1,'.','')).'%';

            $result['ltmonth'] = $arr[0]['ltmonth'];
            $result['ltmonth_percent'] = ($arr[0]['ltmonth']/$arr[0]['realvipnum'])*100;
            $result['ltmonth_percent'] = (number_format($result['ltmonth_percent'],1,'.','')).'%';

            $result['realnewsigningvip'] = ($result['gtyear'] + $result['gthalfyear'] + $result['lthalfyear'] + $result['ltmonth']);
        }
        return $result;
    }

    /**
     * 获取城市续费率数据
     * @param  [mixed]  $map            [查询条件]
     * @param  [string] $order          [排序]
     * @param  [string] $start          [分页开始]
     * @param  [string] $end            [分页结束]
     * @return [array]  $result         [城市会员指标数组]
     */
    public function getCityReNewConn($map,$order,$start=null,$end=null,$type=null)
    {
        //1,查询城市，及拓展、品牌人员信息
        //if(!empty($map['city'])){
            $a_map['m.city'] = $map['city'];
        //}
        if(!empty($map['department'])){
            $a_map['m.dept'] = $map['department'];
        }
        if(!empty($map['tshizhang'])){
            $a_map['m.dev_division'] = $map['tshizhang'];
        }
        if(!empty($map['ttuanzhang'])){
            $a_map['m.dev_regiment'] = $map['ttuanzhang'];
        }
        if(!empty($map['csjl'])){
            $a_map['m.dev_manage'] = $map['csjl'];
        }
        if(!empty($map['pshizhang'])){
            $a_map['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $a_map['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $a_map['m.brand_manage'] = $map['pinpai'];
        }
        if(!empty($map['time'])){
            $a_map['m.time'] = $map['time'];
        }
        $result = M('sale_cityrenew')->alias('m')
                    ->join('left join qz_sales_city_paixu as p on p.cityid = m.id')
                    ->where($a_map)
                    ->field('m.*')
                    ->limit($start .','. $end)
                    ->order($order)
                    ->select();
        return $result;
    }

    /**
     * 获取城市续费率数据总条数
     * @param  [mixed]  $map            [查询条件]
     * @return [array]  $result         [城市会员指标数组]
     */
    public function getCityReNewCount($map)
    {
        //1,查询城市，及拓展、品牌人员信息
        //if(!empty($map['city'])){
            $a_map['m.city'] = $map['city'];
        //}
        if(!empty($map['department'])){
            $a_map['m.dept'] = $map['department'];
        }
        if(!empty($map['tshizhang'])){
            $a_map['m.dev_division'] = $map['tshizhang'];
        }
        if(!empty($map['ttuanzhang'])){
            $a_map['m.dev_regiment'] = $map['ttuanzhang'];
        }
        if(!empty($map['csjl'])){
            $a_map['m.dev_manage'] = $map['csjl'];
        }
        if(!empty($map['pshizhang'])){
            $a_map['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $a_map['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $a_map['m.brand_manage'] = $map['pinpai'];
        }
        if(!empty($map['time'])){
            $a_map['m.time'] = $map['time'];
        }
        $result = M('sale_cityrenew')->alias('m')->where($a_map)->count();
        return $result;
    }

    /**
     * 获取城市续费率全部数据，导出用
     * @param  [mixed]  $map            [查询条件]
     * @param  [string] $order          [排序]
     * @return [array]  $result         [城市会员指标数组]
     */
    public function getCityReNewAll($map,$order)
    {
        //1,查询城市，及拓展、品牌人员信息
        //if(!empty($map['city'])){
            $a_map['m.city'] = $map['city'];
        //}
        if(!empty($map['department'])){
            $a_map['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $a_map['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $a_map['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $a_map['m.brand_manage'] = $map['pinpai'];
        }
        if(!empty($map['time'])){
            $a_map['m.time'] = $map['time'];
        }
        $result = M('sale_cityrenew')->alias('m')
                    ->where($a_map)
                    ->field('m.*')
                    ->order($order)
                    ->select();
        foreach ($result as $k => $v) {
            if($v['dept'] == 1){
                $result[$k]['department'] = '商务';
            }else{
                $result[$k]['department'] = '外销';
            }
            $result[$k]['brand_division_name'] = getSaleUserName($v['brand_division']);
            $result[$k]['brand_regiment_name'] = getSaleUserName($v['brand_regiment']);
            $result[$k]['brand_manage_name'] = getSaleUserName($v['brand_manage']);
            $result[$k]['dev_division_name'] = getSaleUserName($v['dev_division']);
            $result[$k]['dev_regiment_name'] = getSaleUserName($v['dev_regiment']);
            $result[$k]['dev_manage_name'] = getSaleUserName($v['dev_manage']);
        }
        return $result;
    }


    /**
     * 获取城市续费月数完成率数据
     * @param  [mixed]  $map            [查询条件]
     * @param  [string] $order          [排序]
     * @param  [string] $start          [分页开始]
     * @param  [string] $end            [分页结束]
     * @return [array]  $result         [城市会员指标数组]
     */
    public function getCityReNewMonthConn($map,$order,$start=null,$end=null,$type=null)
    {
        //1,查询城市，及拓展、品牌人员信息
        //if(!empty($map['city'])){
            $a_map['m.city'] = $map['city'];
        //}
        if(!empty($map['department'])){
            $a_map['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $a_map['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $a_map['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $a_map['m.brand_manage'] = $map['pinpai'];
        }
        if(!empty($map['time'])){
            $a_map['m.time'] = $map['time'];
        }
        $result = M('sale_cityrenew_months')->alias('m')
                    ->join('left join qz_sales_city_paixu as p on p.cityid = m.id')
                    ->where($a_map)
                    ->field('m.*')
                    ->limit($start .','. $end)
                    ->order($order)
                    ->select();
        return $result;
    }

    
    /**
     * 获取城市续费月数完成率总条数
     * @param  [mixed]  $map            [查询条件]
     * @return [array]  $result         [城市会员指标数量]
     */
    public function getCityReNewMonthCount($map)
    {
        //if(!empty($map['city'])){
            $a_map['m.city'] = $map['city'];
        //}
        if(!empty($map['department'])){
            $a_map['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $a_map['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $a_map['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $a_map['m.brand_manage'] = $map['pinpai'];
        }
        if(!empty($map['time'])){
            $a_map['m.time'] = $map['time'];
        }
        $result = M('sale_cityrenew_months')->alias('m')->where($a_map)->count();
        return $result;
    }

    /**
     * 获取城市续费月数完成率全部数据，导出用
     * @param  [mixed]  $map            [查询条件]
     * @param  [string] $order          [排序]
     * @return [array]  $result         [城市会员指标数组]
     */
    public function getCityReNewMonthAll($map,$order)
    {
        //1,查询城市，及拓展、品牌人员信息
        //if(!empty($map['city'])){
            $a_map['m.city'] = $map['city'];
        //}
        if(!empty($map['department'])){
            $a_map['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $a_map['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $a_map['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $a_map['m.brand_manage'] = $map['pinpai'];
        }
        if(!empty($map['time'])){
            $a_map['m.time'] = $map['time'];
        }
        $result = M('sale_cityrenew_months')->alias('m')
                    ->where($a_map)
                    ->field('m.*')
                    ->order($order)
                    ->select();
        foreach ($result as $k => $v) {
            if($v['dept'] == 1){
                $result[$k]['department'] = '商务';
            }else{
                $result[$k]['department'] = '外销';
            }
            $result[$k]['brand_division_name'] = getSaleUserName($v['brand_division']);
            $result[$k]['brand_regiment_name'] = getSaleUserName($v['brand_regiment']);
            $result[$k]['brand_manage_name'] = getSaleUserName($v['brand_manage']);
        }
        return $result;
    }



    /**
     * 获取城市续费率数据
     * @param  [string]  $time              [查询条件:时间精确到月2017-06]  
     * @param  [string]  $dept              [查询条件:部门 0全部 1商务 2外销] 
     * @param  [array]   $condition         个人城市权限数组  
     * @return [array]   $result            [城市会员指标数量]
     */
    public function getRenewPercentTableInfo($time,$dept=null,$condition=null)
    {
        $where['time'] = strtotime($time.'-01');
        if(!empty($dept) && $dept != 0 ){
            $where['dept'] = $dept;
        }
        //if(!empty($condition)){
            $where['city'] = $condition['city'];
        //}
        //如果没有department，则默认为0（营销中心）:系数由中心续费月度系数提供、数据来自qz_sale_cityrenew
        //查询sale_cityrenew，获取到期数和实际续费数
        $arr = M('sale_cityrenew')->where($where)->field('id,dept,daoqi,realnum,renew_percent,renew_point,renew_compass,year_renew_num,renew_rare,renew_max,over_vip')->select();
        //var_dump(M()->getLastSql());
        //根据部门查询系数
        if(empty($dept) && $dept == 0 ){
            //营销中心
            $p_where['cnyf'] = $time; 
            $points = M('sale_centerpoint')->where($p_where)->field('xflzb,qnxfjz,xfxs,xflzg')->select();
        }else{
            //按部门查询
            $p_where['cnyf'] = $time;
            if($dept == 1){
                $p_where['bm'] = '商务';
            }else{
                $p_where['bm'] = '外销';
            } 
            $points = M('sale_renewpoint')->where($p_where)->field('xflzb,qnxfjz,xfxs,xflzg')->select();
        }
        //查询部门系数
        $total_daoqi = 0;
        $total_realnum = 0;
        $total_renew_point = 0;
        //续费率达成 renew_compass
        $total_renew_compass = 0;
        //全年续费率均值 year_renew_num
        $total_year_renew_num = 0;
        //系数后续费率 renew_rare
        $total_renew_rare = 0;
        //续费率封顶值 renew_max
        $total_renew_max = 0;
        $total_over_vip = 0;
        $num = 0;
        foreach ($arr as $k => $v) {
            if($dept == 0){
                //营销中心数据
                $total_daoqi += $v['daoqi'];
                $total_realnum += $v['realnum'];
                //续费率指标 renew_point
                $total_renew_point += $v['renew_point'];
                //续费率达成 renew_compass
                $total_renew_compass += $v['renew_compass'];
                //全年续费率均值 year_renew_num
                $total_year_renew_num += $v['year_renew_num'];
                //系数后续费率 renew_rare
                $total_renew_rare += $v['renew_rare'];
                //续费率封顶值 renew_max
                $total_renew_max += $v['renew_max'];
                //超出会员数over_vip
                $total_over_vip += $v['over_vip'];
                $num += 1;
            }elseif($dept == 1){
                if($v['dept'] == $dept){
                    $total_daoqi += $v['daoqi'];
                    $total_realnum += $v['realnum'];
                    //续费率指标 renew_point
                    $total_renew_point += $v['renew_point'];
                    //续费率达成 renew_compass
                    $total_renew_compass += $v['renew_compass'];
                    //全年续费率均值 year_renew_num
                    $total_year_renew_num += $v['year_renew_num'];
                    //系数后续费率 renew_rare
                    $total_renew_rare += $v['renew_rare'];
                    //续费率封顶值 renew_max
                    $total_renew_max += $v['renew_max'];
                    //超出会员数over_vip
                    $total_over_vip += $v['over_vip'];
                    $num += 1;
                }
            }elseif($dept == 2){
                if($v['dept'] == $dept){
                    $total_daoqi += $v['daoqi'];
                    $total_realnum += $v['realnum'];
                    //续费率指标 renew_point
                    $total_renew_point += $v['renew_point'];
                    //续费率达成 renew_compass
                    $total_renew_compass += $v['renew_compass'];
                    //全年续费率均值 year_renew_num
                    $total_year_renew_num += $v['year_renew_num'];
                    //系数后续费率 renew_rare
                    $total_renew_rare += $v['renew_rare'];
                    //续费率封顶值 renew_max
                    $total_renew_max += $v['renew_max'];
                    //超出会员数over_vip
                    $total_over_vip += $v['over_vip'];
                    $num += 1;
                }
            }            
        }
        if($dept == 0){
            $result['department'] = '营销中心';
        }elseif($dept == 1){
            $result['department'] = '商务';
        }else{
            $result['department'] = '外销';
        }
        $result['time']             = $time;
        $result['total_daoqi']      = round($total_daoqi,1);
        $result['total_realnum']    = round($total_realnum,1);
        if($total_daoqi == 0){
            $result['xufeilv']  = '0.0%';
        }else{
            $result['xufeilv']  = (round(($total_realnum/$total_daoqi),3))*100;
            $result['xufeilv']  = number_format($result['xufeilv'],1,'.','');
            $result['xufeilv']  = $result['xufeilv'].'%';
        }
        $result['xflzb']        = number_format($points[0]['xflzb'],1,'.','').'%';//续费率指标
        $result['xufeilvdc']    = number_format(($result['xufeilv']/$result['xflzb'])*100,1,'.','').'%';//续费率达成
        $result['qnxfjz']       = number_format($total_year_renew_num/$num,1,'.','').'%';//全年续费率均值
        $result['xishuhouxfl']  = round(($total_realnum/$total_daoqi)/$points[0]['xfxs'],3)*100;
        $result['xishuhouxfl']  = number_format($result['xishuhouxfl'],1,'.','');
        $result['xishuhouxfl']  = $result['xishuhouxfl'].'%';
        $result['xflzg']        = number_format($total_renew_max/$num,1,'.','').'%';//续费率封顶值
        $result['over_vip']     = number_format($total_over_vip,1,'.','');
        if($result['over_vip'] <= 0){
            $result['over_vip'] = '0.0';
        }
        return $result;
    }

    /**
     * 获取城市续费月数数据
     * @param  [string]  $time              [查询条件:时间精确到月2017-06]  
     * @param  [string]  $dept              [查询条件:部门 0全部 1商务 2外销]
     * @param  [array]   $condition         个人城市权限数组     
     * @return [array]   $result            [城市会员指标数量]
     */
    public function getRenewMonthRateInfo($time,$dept=null,$condition=null)
    {
        $where['time'] = strtotime($time.'-01');
        if(!empty($dept) && $dept != 0 ){
            $where['dept'] = $dept;
        }
        //if(!empty($condition)){
            $where['city'] = $condition['city'];
        //}
        //如果没有department，则默认为0（营销中心）:系数由中心续费月度系数提供、数据来自qz_sale_cityrenew
        //查询sale_cityrenew_months，获取到期数、实际续费月数、城市续费月数指标
        $arr = M('sale_cityrenew_months')->where($where)->field('id,dept,daoqi,renew_month,renew_month_point')->select();
        //根据部门查询系数：续费月数完成率指标、全年续费月数完成率均值、续费率月数完成率封顶值
        if(empty($dept) && $dept == 0 ){
            //营销中心
            $p_where['cnyf'] = $time; 
            $points = M('sale_centerpoint')->where($p_where)->field('xfyswczb,qnxfyswcljz,xflyswczg,xfywclxs')->select();
        }else{
            //按部门查询
            $p_where['cnyf'] = $time;
            if($dept == 1){
                $p_where['bm'] = '商务';
            }else{
                $p_where['bm'] = '外销';
            } 
            $points = M('sale_renewpoint')->where($p_where)->field('xfyswczb,qnxfyswcljz,xflyswczg,xfywclxs')->select();
        }
        //var_dump($points);
        $total_daoqi = 0;
        $total_renew_month = 0;
        $total_renew_month_point = 0; 
        foreach ($arr as $k => $v) {
            //if($dept == 0){
                $total_daoqi += $v['daoqi'];
                $total_renew_month += $v['renew_month'];
                $total_renew_month_point += $v['renew_month_point'];
            /*}elseif($dept == 1){
                if($v['dept'] == $dept){
                    $total_daoqi += $v['daoqi'];
                    $total_renew_month += $v['renew_month'];
                    $total_renew_month_point += $v['renew_month_point'];
                }
            }elseif($dept == 2){
                if($v['dept'] == $dept){
                    $total_daoqi += $v['daoqi'];
                    $total_renew_month += $v['renew_month'];
                    $total_renew_month_point += $v['renew_month_point'];
                }
            }        */    
        }
        if($dept == 0){
            $result['department'] = '营销中心';//部门
        }elseif($dept == 1){
            $result['department'] = '商务';
        }else{
            $result['department'] = '外销';
        }
        $result['time']             = $time;//月份
        if($total_daoqi !== null){
            $result['total_daoqi'] = number_format($total_daoqi,1,'.','');//到期数
        }
        //$result['total_daoqi']      = $total_daoqi;
        if($total_renew_month !== null){
            $result['total_renew_month'] = number_format($total_renew_month,1,'.','');//续费月数
        }
        //$result['total_renew_month']    = $total_renew_month;//续费月数
        if($total_renew_month_point !== null){
            $result['total_renew_month_point']    =  number_format($total_renew_month_point,1,'.','');//续费月数指标
        }
        if($total_daoqi == 0){
            $result['xufeilyuewanchenglv']  = '0%';//续费月数完成率
        }else{
            $result['xufeilyuewanchenglv']  = (round(($total_renew_month/$total_renew_month_point),3))*100;
            $result['xufeilyuewanchenglv']  = number_format($result['xufeilyuewanchenglv'],1,'.','');
            $result['xufeilyuewanchenglv']  = $result['xufeilyuewanchenglv'].'%';//续费月数完成率
        }
        $result['xfyswczb']         = (number_format($points[0]['xfyswczb'],1,'.','')).'%';//续费月数完成率指标
        $result['xfyswcldc']        = (round(($result['xufeilyuewanchenglv']/$result['xfyswczb']),3))*100;
        $result['xfyswcldc']        = number_format($result['xfyswcldc'],1,'.','');
        $result['xfyswcldc']        = $result['xfyswcldc'].'%';//续费月数完成率达成
        $result['qnxfyswcljz']      = $points[0]['qnxfyswcljz'];//全年续费月数完成率均值
        $result['xshxfyswcl']       = (number_format(($result['xufeilyuewanchenglv']/$points[0]['xfywclxs']),1,'.','')).'%';//系数后续费月数完成率
        $result['xflyswczg']        = $points[0]['xflyswczg'];//续费月数完成率封顶值
        $result['over_month']     = (($result['xufeilyuewanchenglv'] - $result['xfyswczb'])*$total_renew_month_point)/100;
        if($result['over_month'] <= 0){
            $result['over_month'] = 0;
        }
        if($result['over_month'] !== 0){
            $result['over_month'] = number_format($result['over_month'],1,'.','');//续费月数
        }else{
            $result['over_month'] = '0.0';
        }
        //var_dump($result);
        return $result;
    }



    /**
     * 查询出所有会员的合同类型和时长，写入qz_sale_contract
     * TO DO
     *  计划于每天凌晨执行一次，记录前一天会员合同信息，具体时间参考每天会员操作的时间来定
     * 查询已有历史，如果无记录，即时去查并写入user_vip 和 log_user_real_company
     * @param  [void]  
     * @return [void] 
     */
    public function getCompanyContracts()
    {
        //此处不考虑多倍会员的情况，只考虑实际会员数量
        //1,查询出所有的会员公司id  ，所属城市id
        $map = array(
                "a.classid" => array('IN',array(3,6)),
                "b.fake" => array("EQ",0),
                "a.on" => '2'
            );
        $users = M("user")->where($map)->alias("a")
                 ->join("inner join qz_user_company b on a.id = b.userid")
                 ->field("a.cs,a.id")
                 ->select();
        //2,根据会员公司ID，查询出最新的合同信息
        foreach ($users as $k => $v) {
            $c_map['type'] = array('IN',array('2','8'));
            $c_map['company_id'] = $v['id'];
            $users[$k]['contracts'] = M('user_vip')->where($c_map)->order('id desc')->limit(2)->select();
            if(count($users[$k]['contracts']) < 2){
                //只有一条记录，肯定是新签
                $users[$k]['contract_type'] = 1;  //1是新签
                $start_time = strtotime($users[$k]['contracts'][0]['start_time']);
                $end_time = strtotime($users[$k]['contracts'][0]['end_time']);
                $users[$k]['contract_time'] = ceil(($end_time-$start_time)/86400);//合同时长
            }else{
                //有两条记录，根据合同两份合同间隔时间，判断是新签还是续费
                $start_time = strtotime($users[$k]['contracts'][0]['start_time']);
                $end_time = strtotime($users[$k]['contracts'][0]['end_time']);
                $users[$k]['contract_time'] = ceil(($end_time-$start_time)/86400);//合同时长
                $start_time = strtotime($users[$k]['contracts'][1]['end_time']);
                $end_time = strtotime($users[$k]['contracts'][0]['start_time']);
                $time = ceil(($end_time-$start_time)/86400);//合同间隔时间 >180是新签  <180是续费
                if($time > 180){
                    $users[$k]['contract_type'] = 1;  //1是新签
                }else{
                    $users[$k]['contract_type'] = 2;  //2是续费
                }     
            }
        }


        //3,整理合同数据，写入qz_sale_contract表
        foreach ($users as $key => $value) {
            $data['time']           = date('Y-m-d',time());
            $data['contract_type']  = $value['contract_type'];
            $data['contract_time']  = $value['contract_time'];
            $data['company_id']     = $value['id'];
            $data['city_id']        = $value['cs'];
            $c_map['company_id']    = $data['company_id'];
            $c_map['city_id']       = $data['city_id'];
            $c_map['time']          = $data['time'];
            $info = M('sale_contract')->where($c_map)->find();
            if(empty($info)){
                $id = M('sale_contract')->add($data);
            }
        }
        
        return $users;
    }

    /**
     * 记录每个月每个城市的会员合作时长信息（只在每月1号的凌晨0:10执行）qz_sale_contract
     * TO DO
     *  计划于每月1号凌晨0:10执行一次，记录当天天会员合同信息，具体时间参考每天会员操作的时间来定
     * 查询已有历史，如果无记录，即时去查并写入user_vip 和 log_user_real_company
     * @param  [array]  $data  查询的城市会员合作信息、新签合作信息、续费合作信息
     * @return [void] 
     */
    public function setCityVips($data)
    {
        //$data['all']  会员合作时长数据    type = 1
        //逻辑为每月1号凌晨0:10写入，数据作为上月的数据，此处手动设置保存的时间为上月1号，查询时查询当月即可（例如查询2017-05-01）
        $month = date('Y-m',strtotime("-1 month")).'-01';
        $time = strtotime($month);
        foreach ($data['all'] as $k => $v) {
            $add_data['type']                   = 1;
            $add_data['dept']                   = $v['dept'];
            $add_data['city']                   = $v['city'];
            $add_data['cid']                    = $v['cid'];
            $add_data['brand_division']         = $v['brand_division'];
            $add_data['brand_regiment']         = $v['brand_regiment'];
            $add_data['brand_manage']           = $v['brand_manage'];
            $add_data['dev_division']           = $v['dev_division'];
            $add_data['dev_regiment']           = $v['dev_regiment'];
            $add_data['dev_manage']             = $v['dev_manage'];
            $add_data['realvipnum']             = $v['realvipnum'];
            $add_data['gtyear']                 = $v['gtyear'];
            $add_data['gthalfyear']             = $v['gthalfyear'];
            $add_data['lthalfyear']             = $v['lthalfyear'];
            $add_data['ltmonth']                = $v['ltmonth'];
            $add_data['gtyear_percent']         = $v['gtyear_percent'];
            $add_data['gthalfyear_percent']     = $v['gthalfyear_percent'];
            $add_data['lthalfyear_percent']     = $v['lthalfyear_percent'];
            $add_data['ltmonth_percent']        = $v['ltmonth_percent'];
            $add_data['time']                   = $time;
            M("sale_cityvips")->add($add_data);
        }
        //$data['xq']   新签合作时长数据    type = 2
        foreach ($data['xq'] as $k => $v) {
            $add_data['type']                   = 2;
            $add_data['dept']                   = $v['dept'];
            $add_data['city']                   = $v['city'];
            $add_data['cid']                    = $v['cid'];
            $add_data['brand_division']         = $v['brand_division'];
            $add_data['brand_regiment']         = $v['brand_regiment'];
            $add_data['brand_manage']           = $v['brand_manage'];
            $add_data['dev_division']           = $v['dev_division'];
            $add_data['dev_regiment']           = $v['dev_regiment'];
            $add_data['dev_manage']             = $v['dev_manage'];
            $add_data['realvipnum']             = $v['realvipnum'];
            $add_data['gtyear']                 = $v['gtyear'];
            $add_data['gthalfyear']             = $v['gthalfyear'];
            $add_data['lthalfyear']             = $v['lthalfyear'];
            $add_data['ltmonth']                = $v['ltmonth'];
            $add_data['gtyear_percent']         = $v['gtyear'];
            $add_data['gthalfyear_percent']     = $v['gthalfyear_percent'];
            $add_data['lthalfyear_percent']     = $v['lthalfyear_percent'];
            $add_data['ltmonth_percent']        = $v['ltmonth_percent'];
            $add_data['time']                   = $time;
            M("sale_cityvips")->add($add_data);
        }
        //$data['xf']   续费合作时长数据    type = 3
        foreach ($data['xf'] as $k => $v) {
            $add_data['type']                   = 3;
            $add_data['dept']                   = $v['dept'];
            $add_data['city']                   = $v['city'];
            $add_data['cid']                    = $v['cid'];
            $add_data['brand_division']         = $v['brand_division'];
            $add_data['brand_regiment']         = $v['brand_regiment'];
            $add_data['brand_manage']           = $v['brand_manage'];
            $add_data['dev_division']           = $v['dev_division'];
            $add_data['dev_regiment']           = $v['dev_regiment'];
            $add_data['dev_manage']             = $v['dev_manage'];
            $add_data['realvipnum']             = $v['realvipnum'];
            $add_data['gtyear']                 = $v['gtyear'];
            $add_data['gthalfyear']             = $v['gthalfyear'];
            $add_data['lthalfyear']             = $v['lthalfyear'];
            $add_data['ltmonth']                = $v['ltmonth'];
            $add_data['gtyear_percent']         = $v['gtyear'];
            $add_data['gthalfyear_percent']     = $v['gthalfyear_percent'];
            $add_data['lthalfyear_percent']     = $v['lthalfyear_percent'];
            $add_data['ltmonth_percent']        = $v['ltmonth_percent'];
            $add_data['time']                   = $time;
            M("sale_cityvips")->add($add_data);
        }
    }

    /**
     * 记录每个月每个城市的续费率信息（只在每月11号的凌晨执行）qz_sale_cityrenew
     * TO DO
     *  计划于每月11号凌晨执行一次，记录当月的续费信息，具体时间参考每天会员操作的时间来定
     * 查询当月的续费数（qz_sales_setting_value表module=2）和到期数（qz_sales_setting_value表module=1），如果无记录，放弃增加记录
     * @param  [void]
     * @return [void] 
     */
    public function setCityReNewCounts($start, $end, $order, $map)
    {
        //$data['all']  会员合作时长数据    type = 1
        //逻辑为每月1号凌晨0:10写入，数据作为上月的数据，此处手动设置保存的时间为上月1号，查询时查询当月即可（例如查询2017-05-01）
        //$today = date('Y-m',strtotime('-1 month'));
        //$today = $today.'-01'; 
        //1,查询城市，及拓展、品牌人员信息
        //if(!empty($map['city'])){
            $a_map['m.city'] = $map['city'];
        //}
        if(!empty($map['department'])){
            $a_map['m.dept'] = $map['department'];
        }
        if(!empty($map['tshizhang'])){
            $a_map['m.dev_division'] = $map['tshizhang'];
        }
        if(!empty($map['ttuanzhang'])){
            $a_map['m.dev_regiment'] = $map['ttuanzhang'];
        }
        if(!empty($map['csjl'])){
            $a_map['m.dev_manage'] = $map['csjl'];
        }
        if(!empty($map['pshizhang'])){
            $a_map['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $a_map['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $a_map['m.brand_manage'] = $map['pinpai'];
        }

        $result = M('sales_city_manage')->alias('m')
                                        ->join('qz_sales_city_paixu as p on p.cityid = m.id')
                                        ->where($a_map)
                                        ->field('m.*,p.px5')
                                        ->order($order)
                                        ->limit($start .','. $end)
                                        ->select();

        if(!empty($result)){
            foreach ($result as $k => $v) {
                $ids[] = $v['id'];
                if($v['dept'] == 1){
                    $result[$k]['department'] = '商务';
                }else{
                    $result[$k]['department'] = '外销';
                }
            }
        }
        //查看所有到期数、实际续费数，并添加到$result
        $map1['module'] = 1;
        $map1['status'] = 1;
        if(!empty($ids)){
            $map1['manage_id'] = array('IN',$ids);
        }
        $map1['point'] = array('NEQ','');
        $order1 = 'start desc';
        $buildSql1 = M('sales_setting_value')
                    ->where($map1)
                    ->field('id,manage_id,point,start')
                    ->order($order1)
                    ->buildSql();
        $daoqi = M('sales_setting_value')->table($buildSql1)->alias('a')
                    ->join("qz_sales_city_paixu as p on p.cityid = a.manage_id")
                    ->field("a.*,p.px5 as px")
                    ->group('a.manage_id')
                    ->select();
        $map2['module'] = 2;
        $map2['status'] = 1;
        $order2 = 'start desc';
        if(!empty($ids)){
            $map2['manage_id'] = array('IN',$ids);
        }
        $map2['point'] = array('NEQ','');
        $buildSql2 = M('sales_setting_value')->where($map2)->field('id,manage_id,point,start')->order($order2)->buildSql();
        $renew = M('sales_setting_value')->table($buildSql2)->alias('a')
                    ->join("qz_sales_city_paixu as p on p.cityid = a.manage_id")
                    ->field("a.*,p.px5 as px")
                    ->group('a.manage_id')
                    ->select();
        //查询部门续费系数表，获取续费率指标、全年续费率均值、续费率月度系数、续费率最高值
        $map3['cnyf'] = array('ELT',date('Y-m',time()));
        $xishu = M('sale_renewpoint')
                        ->WHERE($map3)
                        ->field('bm,GROUP_CONCAT(`xflzb` order by cnyf desc) as xflzb,GROUP_CONCAT(`qnxfjz` order by cnyf desc) as qnxfjz,GROUP_CONCAT(`xfxs` order by cnyf desc) as xfxs,GROUP_CONCAT(`xflzg` order by cnyf desc) as xflzg')
                        ->group('bm')
                        ->select();
        foreach ($xishu as $k => $v) {
            $xflzb = explode(',',$v['xflzb']);
            $xishu[$k]['xflzb'] = $xflzb[0];//续费率指标
            $qnxfjz = explode(',',$v['qnxfjz']);
            $xishu[$k]['qnxfjz'] = $qnxfjz[0];//全年续费率均值
            $xfxs = explode(',',$v['xfxs']);
            $xishu[$k]['xfxs'] = $xfxs[0];//部门续费系数
            $xflzg = explode(',',$v['xflzg']);
            $xishu[$k]['xflzg'] = $xflzg[0];//续费率最高值
        }
        foreach ($result as $k => $v) {
            foreach ($daoqi as $key => $value) {
                if($v['id'] == $value['manage_id']){
                    $result[$k]['daoqi'] = $value['point'];//到期数
                }
            }
            foreach ($renew as $key => $value) {
                if($v['id'] == $value['manage_id']){
                    $result[$k]['realnum'] = $value['point'];//实际续费数
                }
            }
            if(empty($result[$k]['daoqi'])){
                $result[$k]['daoqi'] = 0;
            }
            if(empty($result[$k]['realnum'])){
                $result[$k]['realnum'] = 0;
            }
            if($result[$k]['daoqi'] == 0){
                $result[$k]['renew_percent'] = '0.0%';//续费率
            }else{
                $result[$k]['renew_percent'] = round(($result[$k]['realnum']/$result[$k]['daoqi']),3)*100;
                $result[$k]['renew_percent'] = number_format($result[$k]['renew_percent'],'1','.','');
                $result[$k]['renew_percent'] = $result[$k]['renew_percent'].'%';
            }
            if(!empty($xishu)){
                foreach ($xishu as $key => $value) {
                    if($value['bm'] == $v['department']){
                        $result[$k]['renew_point'] = (number_format($value['xflzb'],1,'.','')).'%';//续费率指标 xflzb
                        $result[$k]['year_renew_num'] = (number_format($value['qnxfjz'],1,'.','')).'%';//全年续费率均值 qnxfjz 
                        $result[$k]['xfxs'] = $value['xfxs'];//续费率月度系数 xfxs
                        $result[$k]['renew_max'] = (number_format($value['xflzg'],1,'.','')).'%';//续费率封顶值 xflzg
                    }
                }
            }else{
                $result[$k]['renew_point'] = '';//续费率指标 xflzb
                $result[$k]['year_renew_num'] = '';//全年续费率均值 qnxfjz 
                $result[$k]['xfxs'] = '';//续费率月度系数 xfxs
                $result[$k]['renew_max'] = '';//续费率封顶值 xflzg
            }
        }
        //到期数 、 实际续费数
        //续费率  续费率：部门合计实际续费数/部门合计到期数*100%
        foreach ($result as $k => $v) {
            $result[$k]['renew_compass'] = ($result[$k]['renew_percent']/$result[$k]['renew_point'])*100;
            $result[$k]['renew_compass'] = (number_format($result[$k]['renew_compass'],1,'.','')).'%';//续费率达成
            $result[$k]['renew_rare'] = number_format((($result[$k]['renew_percent']/$result[$k]['xfxs'])/100),1,'.','').'%';//系数后续费率
            $result[$k]['over_vip'] = round((($result[$k]['renew_percent'] - $result[$k]['renew_point'])*$result[$k]['daoqi'])/100,1);//超出会员数
            if($result[$k]['over_vip'] <= 0){
                $result[$k]['over_vip'] = 0;
            }
            $result[$k]['time'] = $today;
        }   
        return $result;
    }

    /**
     * 查询当月所有续费系数（导出用）
     * @param  array     $map     搜索条件
     * @param  string    $order   排序
     * @return array     $result  查询结果数组 
     */
    public function setCityReNewCountsAll($map,$order)
    {
        //$data['all']  会员合作时长数据    type = 1
        //逻辑为每月1号凌晨0:10写入，数据作为上月的数据，此处手动设置保存的时间为上月1号，查询时查询当月即可（例如查询2017-05-01）
        //$today = date('Y-m',strtotime('-1 month'));
        //$today = $today.'-01'; 
        //1,查询城市，及拓展、品牌人员信息
        //if(!empty($map['city'])){
            $a_map['m.city'] = $map['city'];
        //}
        if(!empty($map['department'])){
            $a_map['m.dept'] = $map['department'];
        }
        if(!empty($map['tshizhang'])){
            $a_map['m.dev_division'] = $map['tshizhang'];
        }
        if(!empty($map['ttuanzhang'])){
            $a_map['m.dev_regiment'] = $map['ttuanzhang'];
        }
        if(!empty($map['csjl'])){
            $a_map['m.dev_manage'] = $map['csjl'];
        }
        if(!empty($map['pshizhang'])){
            $a_map['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $a_map['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $a_map['m.brand_manage'] = $map['pinpai'];
        }

        $result = M('sales_city_manage')->alias('m')
                                        ->join('qz_sales_city_paixu as p on p.cityid = m.id')
                                        ->where($a_map)
                                        ->field('m.*,p.px5')
                                        ->order($order)
                                        ->select();

        if(!empty($result)){
            foreach ($result as $k => $v) {
                $ids[] = $v['id'];
                if($v['dept'] == 1){
                    $result[$k]['department'] = '商务';
                }else{
                    $result[$k]['department'] = '外销';
                }
            }
        }
        //查看所有到期数、实际续费数，并添加到$result
        $map1['module'] = 1;
        $map1['status'] = 1;
        if(!empty($ids)){
            $map1['manage_id'] = array('IN',$ids);
        }
        $map1['point'] = array('NEQ','');
        $order1 = 'start desc';
        $buildSql1 = M('sales_setting_value')
                    ->where($map1)
                    ->field('id,manage_id,point,start')
                    ->order($order1)
                    ->buildSql();
        $daoqi = M('sales_setting_value')->table($buildSql1)->alias('a')
                    ->join("qz_sales_city_paixu as p on p.cityid = a.manage_id")
                    ->field("a.*,p.px5 as px")
                    ->group('a.manage_id')
                    ->select();
        $map2['module'] = 2;
        $map2['status'] = 1;
        $order2 = 'start desc';
        if(!empty($ids)){
            $map2['manage_id'] = array('IN',$ids);
        }
        $map2['point'] = array('NEQ','');
        $buildSql2 = M('sales_setting_value')->where($map2)->field('id,manage_id,point,start')->order($order2)->buildSql();
        $renew = M('sales_setting_value')->table($buildSql2)->alias('a')
                    ->join("qz_sales_city_paixu as p on p.cityid = a.manage_id")
                    ->field("a.*,p.px5 as px")
                    ->group('a.manage_id')
                    ->select();
        //查询部门续费系数表，获取续费率指标、全年续费率均值、续费率月度系数、续费率最高值
        $map3['cnyf'] = array('ELT',date('Y-m',time()));
        $xishu = M('sale_renewpoint')
                        ->WHERE($map3)
                        ->field('bm,GROUP_CONCAT(`xflzb` order by cnyf desc) as xflzb,GROUP_CONCAT(`qnxfjz` order by cnyf desc) as qnxfjz,GROUP_CONCAT(`xfxs` order by cnyf desc) as xfxs,GROUP_CONCAT(`xflzg` order by cnyf desc) as xflzg')
                        ->group('bm')
                        ->select();
        foreach ($xishu as $k => $v) {
            $xflzb = explode(',',$v['xflzb']);
            $xishu[$k]['xflzb'] = $xflzb[0];//续费率指标
            $qnxfjz = explode(',',$v['qnxfjz']);
            $xishu[$k]['qnxfjz'] = $qnxfjz[0];//全年续费率均值
            $xfxs = explode(',',$v['xfxs']);
            $xishu[$k]['xfxs'] = $xfxs[0];//部门续费系数
            $xflzg = explode(',',$v['xflzg']);
            $xishu[$k]['xflzg'] = $xflzg[0];//续费率最高值
        }
        foreach ($result as $k => $v) {
            foreach ($daoqi as $key => $value) {
                if($v['id'] == $value['manage_id']){
                    $result[$k]['daoqi'] = $value['point'];//到期数
                }
            }
            foreach ($renew as $key => $value) {
                if($v['id'] == $value['manage_id']){
                    $result[$k]['realnum'] = $value['point'];//实际续费数
                }
            }
            if(empty($result[$k]['daoqi'])){
                $result[$k]['daoqi'] = 0;
            }
            if(empty($result[$k]['realnum'])){
                $result[$k]['realnum'] = 0;
            }
            if($result[$k]['daoqi'] == 0){
                $result[$k]['renew_percent'] = '0.0%';//续费率
            }else{
                $result[$k]['renew_percent'] = round(($result[$k]['realnum']/$result[$k]['daoqi']),3)*100;
                $result[$k]['renew_percent'] = $result[$k]['renew_percent'].'%';
            }
            if(!empty($xishu)){
                foreach ($xishu as $key => $value) {
                    if($value['bm'] == $v['department']){
                        $result[$k]['renew_point'] = $value['xflzb'];//续费率指标 xflzb
                        $result[$k]['year_renew_num'] = $value['qnxfjz'];//全年续费率均值 qnxfjz 
                        $result[$k]['xfxs'] = $value['xfxs'];//续费率月度系数 xfxs
                        $result[$k]['renew_max'] = $value['xflzg'];//续费率封顶值 xflzg
                    }
                }
            }else{
                $result[$k]['renew_point'] = '';//续费率指标 xflzb
                $result[$k]['year_renew_num'] = '';//全年续费率均值 qnxfjz 
                $result[$k]['xfxs'] = '';//续费率月度系数 xfxs
                $result[$k]['renew_max'] = '';//续费率封顶值 xflzg
            }
        }
        //到期数 、 实际续费数
        //续费率  续费率：部门合计实际续费数/部门合计到期数*100%
        foreach ($result as $k => $v) {
            $result[$k]['renew_compass'] = round(($result[$k]['renew_percent']/$result[$k]['renew_point']),3)*100;
            $result[$k]['renew_compass'] = $result[$k]['renew_compass'].'%';//续费率达成
            $result[$k]['renew_rare'] = round((($result[$k]['renew_percent']/$result[$k]['xfxs'])/100),3);//系数后续费率
            $result[$k]['over_vip'] = (($result[$k]['renew_percent'] - $result[$k]['renew_point'])*$result[$k]['daoqi'])/100;//超出会员数
            if($result[$k]['over_vip'] <= 0){
                $result[$k]['over_vip'] = 0;
            }
            $result[$k]['time'] = $today;
        }   
        return $result;
    }

    /**
     * 全瞰续费率--查询当月所有续费系数
     * @param  array     $map     搜索条件
     * @param  string    $order   排序
     * @param  array     $idarr   限制城市数组
     * @return array     $result  查询结果数组 
     */
    public function getThisMonthRenewPercentTableInfo($map,$condition,$order,$idarr)
    {
        //$data['all']  会员合作时长数据    type = 1
        //逻辑为每月1号凌晨0:10写入，数据作为上月的数据，此处手动设置保存的时间为上月1号，查询时查询当月即可（例如查询2017-05-01）
        //$today = date('Y-m',strtotime('-1 month'));
        //$today = $today.'-01'; 
        //1,查询城市，及拓展、品牌人员信息
        if(!empty($map['department'])){
            $a_map['m.dept'] = $map['department'];
        }
        //if(!empty($idarr)){
            $a_map['m.city'] = $idarr['city'];
        //}
        $result = M('sales_city_manage')->alias('m')
                                        ->join('qz_sales_city_paixu as p on p.cityid = m.id')
                                        ->where($a_map)
                                        ->field('m.*,p.px5')
                                        ->order($order)
                                        ->select();
        //var_dump($result);
        if(!empty($result)){
            foreach ($result as $k => $v) {
                $ids[] = $v['id'];
                if($v['dept'] == 1){
                    $result[$k]['department'] = '商务';
                }else{
                    $result[$k]['department'] = '外销';
                }
            }
        }
        //查看所有到期数、实际续费数，并添加到$result
        $map1['module'] = 1;
        $map1['status'] = 1;
        if(!empty($ids)){
            $map1['manage_id'] = array('IN',$ids);
        }
        $map1['point'] = array('NEQ','');
        $order1 = 'start desc';
        $buildSql1 = M('sales_setting_value')
                    ->where($map1)
                    ->field('id,manage_id,point,start')
                    ->order($order1)
                    ->buildSql();
        $daoqi = M('sales_setting_value')->table($buildSql1)->alias('a')
                    ->join("qz_sales_city_paixu as p on p.cityid = a.manage_id")
                    ->field("a.*,p.px5 as px")
                    ->group('a.manage_id')
                    ->select();
        $map2['module'] = 2;
        $map2['status'] = 1;
        $order2 = 'start desc';
        if(!empty($ids)){
            $map2['manage_id'] = array('IN',$ids);
        }
        $map2['point'] = array('NEQ','');
        $buildSql2 = M('sales_setting_value')->where($map2)->field('id,manage_id,point,start')->order($order2)->buildSql();
        $renew = M('sales_setting_value')->table($buildSql2)->alias('a')
                    ->join("qz_sales_city_paixu as p on p.cityid = a.manage_id")
                    ->field("a.*,p.px5 as px")
                    ->group('a.manage_id')
                    ->select();
        //查询部门续费系数表，获取续费率指标、全年续费率均值、续费率月度系数、续费率最高值
        //如果部门是 营销中心，查qz_sale_centerpoint
        //如果是部门 商务/外销，查qz_sale_renewpoint
        if(!empty($map['department'])){
            //如果是部门 商务/外销
            if($map['department'] == 1){
                $p_where['bm'] = '商务';
            }else{
                $p_where['bm'] = '外销';
            } 
            $map3['cnyf'] = array('EQ',date('Y-m',time()));
            $xishu = M('sale_renewpoint')
                            ->WHERE($map3)
                            ->field('bm,GROUP_CONCAT(`xflzb` order by cnyf desc) as xflzb,GROUP_CONCAT(`qnxfjz` order by cnyf desc) as qnxfjz,GROUP_CONCAT(`xfxs` order by cnyf desc) as xfxs,GROUP_CONCAT(`xflzg` order by cnyf desc) as xflzg')
                            ->group('bm')
                            ->order('cnyf asc')
                            ->select();
        }else{
            //如果部门是 营销中心
            $xishu = M('sale_centerpoint')
                            ->WHERE($map3)
                            ->field('bm,GROUP_CONCAT(`xflzb` order by cnyf desc) as xflzb,GROUP_CONCAT(`qnxfjz` order by cnyf desc) as qnxfjz,GROUP_CONCAT(`xfxs` order by cnyf desc) as xfxs,GROUP_CONCAT(`xflzg` order by cnyf desc) as xflzg')
                            ->group('bm')
                            ->order('cnyf asc')
                            ->select();
        }
        foreach ($xishu as $k => $v) {
            
            if($map['department'] == 1){
                //商务
                if($v['bm'] == '商务'){
                    $xflzb = explode(',',$v['xflzb']);
                    $bmxs['xflzb'] = $xflzb[0];//续费率指标
                    $qnxfjz = explode(',',$v['qnxfjz']);
                    $bmxs['qnxfjz'] = $qnxfjz[0];//全年续费率均值
                    $xfxs = explode(',',$v['xfxs']);
                    $bmxs['xfxs'] = $xfxs[0];//部门续费系数
                    $xflzg = explode(',',$v['xflzg']);
                    $bmxs['xflzg'] = $xflzg[0];//续费率最高值
                }
            }else if($map['department'] == 2){
                //外销
                if($v['bm'] == '外销'){
                    $xflzb = explode(',',$v['xflzb']);
                    $bmxs['xflzb'] = $xflzb[0];//续费率指标
                    $qnxfjz = explode(',',$v['qnxfjz']);
                    $bmxs['qnxfjz'] = $qnxfjz[0];//全年续费率均值
                    $xfxs = explode(',',$v['xfxs']);
                    $bmxs['xfxs'] = $xfxs[0];//部门续费系数
                    $xflzg = explode(',',$v['xflzg']);
                    $bmxs['xflzg'] = $xflzg[0];//续费率最高值
                }
            }else{
                $xflzb = explode(',',$v['xflzb']);
                $bmxs['xflzb'] = $xflzb[0];//续费率指标
                $qnxfjz = explode(',',$v['qnxfjz']);
                $bmxs['qnxfjz'] = $qnxfjz[0];//全年续费率均值
                $xfxs = explode(',',$v['xfxs']);
                $bmxs['xfxs'] = $xfxs[0];//部门续费系数
                $xflzg = explode(',',$v['xflzg']);
                $bmxs['xflzg'] = $xflzg[0];//续费率最高值
            }
        }
        foreach ($result as $k => $v) {
            foreach ($daoqi as $key => $value) {
                if($v['id'] == $value['manage_id']){
                    $result[$k]['daoqi'] = $value['point'];//到期数
                }
            }
            foreach ($renew as $key => $value) {
                if($v['id'] == $value['manage_id']){
                    $result[$k]['realnum'] = $value['point'];//实际续费数
                }
            }
            if(empty($result[$k]['daoqi'])){
                $result[$k]['daoqi'] = 0;
            }
            if(empty($result[$k]['realnum'])){
                $result[$k]['realnum'] = 0;
            }
        }
        $total_daoqi = 0;
        $total_realnum = 0;
        foreach ($result as $k => $v) {
            if($dept == 0){
                $total_daoqi += $v['daoqi'];
                $total_realnum += $v['realnum'];
            }elseif($dept == 1){
                if($v['dept'] == $dept){
                    $total_daoqi += $v['daoqi'];
                    $total_realnum += $v['realnum'];
                }
            }elseif($dept == 2){
                if($v['dept'] == $dept){
                    $total_daoqi += $v['daoqi'];
                    $total_realnum += $v['realnum'];
                }
            }            
        }
        if($map['department'] == 0){
            $arr['department'] = '营销中心';
        }elseif($map['department'] == 1){
            $arr['department'] = '商务';
        }else{
            $arr['department'] = '外销';
        }
        $arr['time']             = date('Y-m',time());
        $arr['total_daoqi']      = round($total_daoqi,1);
        $arr['total_realnum']    = round($total_realnum,1);
        if($total_daoqi == 0){
            $arr['xufeilv']  = '0%';
        }else{
            $arr['xufeilv']  = (round(($total_realnum/$total_daoqi),3))*100;
            $arr['xufeilv']  = $arr['xufeilv'].'%';
        }
        $arr['xflzb']        = (number_format($bmxs['xflzb'],1,'.','')).'%';//续费率指标
        $arr['xufeilvdc']    = ($arr['xufeilv']/$arr['xflzb'])*100;
        $arr['xufeilvdc']    = (number_format($arr['xufeilvdc'],1,'.','')).'%';//续费率达成
        $arr['qnxfjz']       = (number_format($bmxs['qnxfjz'],1,'.','')).'%';//全年续费率均值
        $arr['xishuhouxfl']  = (number_format(($arr['xufeilv']/$bmxs['xfxs']),1,'.','')).'%';
        $arr['xflzg']        = (number_format($bmxs['xflzg'],1,'.','')).'%';//续费率封顶值
        $arr['over_vip']     = number_format((($arr['xufeilv'] - $arr['xflzb'])*$arr['total_daoqi'])/100,1,'.','');
        if($arr['over_vip'] <= 0){
            $arr['over_vip'] = 0;
        }
        return $arr;

    }

    /**
     * 获取当月城市续费率数据总条数
     * @param  [mixed]  $map            [查询条件]
     * @return [array]  $result         [城市会员指标数组]
     */
    public function getThisMonthCityReNewCount($map)
    {
        //1,查询城市，及拓展、品牌人员信息
        //if(!empty($map['city'])){
            $a_map['m.city'] = $map['city'];
        //}
        if(!empty($map['department'])){
            $a_map['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $a_map['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $a_map['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $a_map['m.brand_manage'] = $map['pinpai'];
        }
        if(!empty($map['tshizhang'])){
            $a_map['m.dev_division'] = $map['tshizhang'];
        }
        if(!empty($map['ttuanzhang'])){
            $a_map['m.dev_regiment'] = $map['ttuanzhang'];
        }
        if(!empty($map['csjl'])){
            $a_map['m.dev_manage'] = $map['csjl'];
        }
        $result = M('sales_city_manage')->alias('m')->where($a_map)->count();
        return $result;
    }

    /**
     * 查询的城市续费率信息写入数据库（只在每月11号的凌晨执行）qz_sale_cityrenew_months
     * @param  [array]  $data  查询统计的续费率信息
     * @return [void] 
     */    
    public function writeCityReNewIn($data)
    {
        foreach ($data as $k => $v) {
            $value['dept']              = $v['dept'];
            $value['city']              = $v['city']; 
            $value['cid']               = $v['cid'];
            $value['ratio']          = $v['ratio'];
            $value['brand_division']    = $v['brand_division'];
            $value['brand_regiment']    = $v['brand_regiment'];
            $value['brand_manage']      = $v['brand_manage'];
            $value['dev_division']      = $v['dev_division'];
            $value['dev_regiment']      = $v['dev_regiment'];
            $value['dev_manage']        = $v['dev_manage'];
            $value['daoqi']             = $v['daoqi'];
            $value['realnum']           = $v['realnum'];
            $value['renew_percent']     = $v['renew_percent'];
            $value['renew_point']       = $v['renew_point'];
            $value['renew_compass']     = $v['renew_compass'];
            $value['year_renew_num']    = $v['year_renew_num'];
            $value['renew_rare']        = $v['renew_rare'];
            $value['renew_max']         = $v['renew_max'];
            $value['over_vip']          = $v['over_vip'];
            $value['time']              = strtotime($v['time']);

            M('sale_cityrenew')->add($value);
        }
    }

    /**
     * 记录每个月每个城市的续费月数完成率信息（只在每月11号的凌晨执行）qz_sale_cityrenew
     * TO DO
     *  计划于每月11号凌晨执行一次，记录当月的续费信息，具体时间参考每天会员操作的时间来定
     * 查询当月的续费数（qz_sales_setting_value表module=2）和到期数（qz_sales_setting_value表module=1），如果无记录，放弃增加记录
     * @param  [void]
     * @return [void] 
     */
    public function getThisMonthCityReNewMonthConn($map,$order,$start='',$end='')
    {
        //if(!empty($map['city'])){
            $a_map['m.city'] = $map['city'];
        //}
        if(!empty($map['department'])){
            $a_map['m.dept'] = $map['department'];
        }
        if(!empty($map['pshizhang'])){
            $a_map['m.brand_division'] = $map['pshizhang'];
        }
        if(!empty($map['ptuanzhang'])){
            $a_map['m.brand_regiment'] = $map['ptuanzhang'];
        }
        if(!empty($map['pinpai'])){
            $a_map['m.brand_manage'] = $map['pinpai'];
        }
        if(!empty($map['tshizhang'])){
            $a_map['m.dev_division'] = $map['tshizhang'];
        }
        if(!empty($map['ttuanzhang'])){
            $a_map['m.dev_regiment'] = $map['ttuanzhang'];
        }
        if(!empty($map['csjl'])){
            $a_map['m.dev_manage'] = $map['csjl'];
        }
        if(!empty($end)){
            $limit = $start .','. $end;
        }
        $result = M('sales_city_manage')->alias('m')
                                        ->join('qz_sales_city_paixu as p on p.cityid = m.id')
                                        ->where($a_map)
                                        ->field('m.*,p.px6')
                                        ->order($order)
                                        ->limit($limit)
                                        ->select();
        if(!empty($result)){
            foreach ($result as $k => $v) {
                $ids[] = $v['id'];
                if($v['dept'] == 1){
                    $result[$k]['department'] = '商务';
                }else{
                    $result[$k]['department'] = '外销';
                }
            }
        }
        //到期数          sales_setting_value 表moudle = 1   
        //实际续费月数    sales_setting_value 表moudle = 3
        //续费月数指标    sales_setting_value 表moudle = 6
        //查看所有到期数、实际续费月数，并添加到$result
        $order1 = 'start desc';
        $map1['module'] = 1;
        $map1['status'] = 1;
        if(!empty($ids)){
            $map1['manage_id'] = array('IN',$ids);
        }
        $map1['point'] = array('NEQ','');
        $buildSql1 = M('sales_setting_value')
                    ->where($map1)
                    ->field('id,manage_id,point,start')
                    ->order($order1)
                    ->buildSql();
        $daoqi = M('sales_setting_value')->table($buildSql1)->alias('a')
                    ->join("qz_sales_city_paixu as p on p.cityid = a.manage_id")
                    ->field("a.*,p.px6 as px")
                    ->group('a.manage_id')
                    ->select();
        $map2['module'] = 3;
        $map2['status'] = 1;
        if(!empty($ids)){
            $map2['manage_id'] = array('IN',$ids);
        }
        $map2['point'] = array('NEQ','');
        $buildSql2 = M('sales_setting_value')
                    ->where($map2)
                    ->field('id,manage_id,point,start')
                    ->order($order1)
                    ->buildSql();
        $renew = M('sales_setting_value')->table($buildSql2)->alias('a')
                    ->join("qz_sales_city_paixu as p on p.cityid = a.manage_id")
                    ->field("a.*,p.px6 as px")
                    ->group('a.manage_id')
                    ->select();
        $map4['module'] = 6;
        $map4['status'] = 1;
        if(!empty($ids)){
            $map4['manage_id'] = array('IN',$ids);
        }
        $map4['point'] = array('NEQ','');
        $buildSql3 = M('sales_setting_value')
                    ->where($map4)
                    ->field('id,manage_id,point,start')
                    ->order($order1)
                    ->buildSql();
        $renew_point = M('sales_setting_value')->table($buildSql3)->alias('a')
                    ->join("qz_sales_city_paixu as p on p.cityid = a.manage_id")
                    ->field("a.*,p.px6 as px")
                    ->group('a.manage_id')
                    ->select();
        //查询部门续费系数表，获取续费月数完成率指标、
        $map3['cnyf'] = array('ELT',date('Y-m',time()));
        $xishu = M('sale_renewpoint')
                        ->WHERE($map3)
                        ->field('bm,GROUP_CONCAT(`xfyswczb` order by cnyf desc) as xfyswczb,GROUP_CONCAT(`qnxfyswcljz` order by cnyf desc) as qnxfyswcljz,GROUP_CONCAT(`xfywclxs` order by cnyf desc) as xfywclxs,GROUP_CONCAT(`xflyswczg` order by cnyf desc) as xflyswczg')
                        ->group('bm')
                        ->select();
        foreach ($xishu as $k => $v) {
            $xfyswczb = explode(',',$v['xfyswczb']);
            $xishu[$k]['xfyswczb'] = $xfyswczb[0];//续费率指标
            $qnxfyswcljz = explode(',',$v['qnxfyswcljz']);
            $xishu[$k]['qnxfyswcljz'] = $qnxfyswcljz[0];//全年续费率均值
            $xfywclxs = explode(',',$v['xfywclxs']);
            $xishu[$k]['xfywclxs'] = $xfywclxs[0];//部门续费系数
            $xflyswczg = explode(',',$v['xflyswczg']);
            $xishu[$k]['xflyswczg'] = $xflyswczg[0];//续费率最高值
        }
        foreach ($result as $k => $v) {
            foreach ($daoqi as $key => $value) {
                if($v['id'] == $value['manage_id']){
                    $result[$k]['daoqi'] = $value['point'];//到期数
                }
            }
            foreach ($renew as $key => $value) {
                if($v['id'] == $value['manage_id']){
                    $result[$k]['renew_month'] = $value['point'];//实际续费月数
                }
            }
            foreach ($renew_point as $key => $value) {
                if($v['id'] == $value['manage_id']){
                    $result[$k]['renew_month_point'] = $value['point'];//续费月数指标
                }
            }
            if(empty($result[$k]['daoqi'])){
                $result[$k]['daoqi'] = 0;
            }
            if(empty($result[$k]['renew_month'])){
                $result[$k]['renew_month'] = 0;
            }
            if(empty($result[$k]['renew_month_point'])){
                $result[$k]['renew_month_point'] = 0;
            }
            if($result[$k]['renew_month_point'] == 0){
                $result[$k]['renew_month_rate'] = '0.0%';//续费月数完成率：实际续费月数/续费月数指标*100%
            }else{
                $result[$k]['renew_month_rate'] = number_format(($result[$k]['renew_month']/$result[$k]['renew_month_point'])*100,1,'.','');
                $result[$k]['renew_month_rate'] = $result[$k]['renew_month_rate'].'%';//续费月数完成率：实际续费月数/续费月数指标*100%
            }
            
            if(!empty($xishu)){
                foreach ($xishu as $key => $value) {
                    if($value['bm'] == $v['department']){
                        $result[$k]['renew_month_rate_point'] = $value['xfyswczb'];//续费月数完成率指标 xfyswczb
                        $result[$k]['renew_year_value'] = $value['qnxfyswcljz'];//全年续费月数完成率均值 qnxfyswcljz
                        $result[$k]['xfywclxs'] = $value['xfywclxs'];//续费月数完成率月度系数  xfywclxs
                        $result[$k]['renew_max'] = $value['xflyswczg'];//续费率月数完成率封顶值 xflyswczg
                    }
                }
            }else{
                $result[$k]['renew_month_rate_point'] = '';//续费月数完成率指标 xfyswczb
                $result[$k]['renew_year_value'] = '';//全年续费月数完成率均值 qnxfyswcljz
                $result[$k]['xfywclxs'] = '';//续费月数完成率月度系数  xfywclxs
                $result[$k]['renew_max'] = '';//续费率月数完成率封顶值 xflyswczg
            }  
            $result[$k]['renew_month_rate_point'] = number_format($result[$k]['renew_month_rate_point'],1,'.','').'%';
            $result[$k]['renew_year_value'] = number_format($result[$k]['renew_year_value'],1,'.','').'%';
            $result[$k]['renew_max'] = number_format($result[$k]['renew_max'],1,'.','').'%';

            $result[$k]['renew_month_rate_complete'] = number_format(($result[$k]['renew_month_rate']/$result[$k]['renew_month_rate_point'])*100,1,'.','');
            $result[$k]['renew_month_rate_complete'] = $result[$k]['renew_month_rate_complete'].'%';//续费月数完成率达成:续费月数完成率/续费月数完成率指标*100%

            //$result[$k]['renew_monthly_rate'] = number_format(($result[$k]['renew_month_rate']/$result[$k]['xfywclxs']),1,'.','').'%';//系数后续费月数完成率:续费月数完成率/续费月数完成率月度系数

            //超出月数:（续费月数完成率-续费月数完成率指标）*续费月数指标，≤0的情况下均为0
            $result[$k]['over_month'] = (($result[$k]['renew_month_rate'] - $result[$k]['renew_month_rate_point'])*$result[$k]['renew_month_point'])/100;
            if($result[$k]['over_month'] <= 0){
                $result[$k]['over_month'] = 0;
            }
        }

        return $result;
    }

    /**
     * 全瞰续费月数完成率--查询当月
     * @param  array     $map     搜索条件
     * @param  string    $order   排序
     * @param  array     $condition   
     * @return array     $result  查询结果数组 
     */
    public function getRenewMonthTableInfo($map,$order,$condition)
    {
        //if(!empty($condition)){
            $map['city'] = $condition['city'];
        //}
        //1,查询城市，及拓展、品牌人员信息
        $result = $this->getThisMonthCityReNewMonthConn($map,'');
        if(!empty($map['department'])){
            //如果是部门 商务/外销
            $map3['cnyf'] = array('ELT',date('Y-m',time()));
            //xfyswczb,qnxfyswcljz,xflyswczg,xfywclxs
            $xishu = M('sale_renewpoint')
                            ->WHERE($map3)
                            ->field('bm,GROUP_CONCAT(`xfyswczb` order by cnyf desc) as xfyswczb,GROUP_CONCAT(`qnxfyswcljz` order by cnyf desc) as qnxfyswcljz,GROUP_CONCAT(`xflyswczg` order by cnyf desc) as xflyswczg,GROUP_CONCAT(`xfywclxs` order by cnyf desc) as xfywclxs')
                            ->group('bm')
                            ->order('cnyf asc')
                            ->select();
        }else{
            //如果部门是 营销中心
            $xishu = M('sale_centerpoint')
                            ->WHERE($map3)
                            ->field('bm,GROUP_CONCAT(`xfyswczb` order by cnyf desc) as xfyswczb,GROUP_CONCAT(`qnxfyswcljz` order by cnyf desc) as qnxfyswcljz,GROUP_CONCAT(`xflyswczg` order by cnyf desc) as xflyswczg,GROUP_CONCAT(`xfywclxs` order by cnyf desc) as xfywclxs')
                            ->group('bm')
                            ->order('cnyf asc')
                            ->select();
        }
        foreach ($xishu as $k => $v) {
            $xfyswczb = explode(',',$v['xfyswczb']);
            $xishu[$k]['xfyswczb'] = $xfyswczb[0];//续费率指标
            $qnxfyswcljz = explode(',',$v['qnxfyswcljz']);
            $xishu[$k]['qnxfyswcljz'] = $qnxfyswcljz[0];//全年续费率均值
            $xfywclxs = explode(',',$v['xfywclxs']);
            $xishu[$k]['xfywclxs'] = $xfywclxs[0];//部门续费系数
            $xflyswczg = explode(',',$v['xflyswczg']);
            $xishu[$k]['xflyswczg'] = $xflyswczg[0];//续费率最高值
        }

        $total_daoqi = 0;
        $total_renew_month = 0;
        $total_renew_month_point = 0; 
        foreach ($result as $k => $v) {
                $total_daoqi += $v['daoqi'];
                $total_renew_month += $v['renew_month'];
                $total_renew_month_point += $v['renew_month_point']; 
        }
        if($map['department'] == 0){
            $arr['department'] = '营销中心';//部门
        }elseif($map['department'] == 1){
            $arr['department'] = '商务';
        }else{
            $arr['department'] = '外销';
        }
        $arr['time']             = date('Y-m',time());//月份
        if($total_daoqi !== null){
            $arr['total_daoqi'] = number_format($total_daoqi,1,'.','');//到期数
        }
        //$result['total_daoqi']      = $total_daoqi;
        if($total_renew_month !== null){
            $arr['total_renew_month'] = number_format($total_renew_month,1,'.','');//续费月数
        }
        if($total_renew_month_point !== null){
            $arr['total_renew_month_point'] = number_format($total_renew_month_point,1,'.','');//续费月数指标
        }
        if($total_daoqi == 0){
            $arr['xufeilyuewanchenglv']  = '0.0%';//续费月数完成率
        }else{
            $arr['xufeilyuewanchenglv']  = (round(($total_renew_month/$total_renew_month_point),3))*100;
            $arr['xufeilyuewanchenglv']  = number_format($arr['xufeilyuewanchenglv'],1,'.','');
            $arr['xufeilyuewanchenglv']  = $arr['xufeilyuewanchenglv'].'%';//续费月数完成率
        }
        $arr['xfyswczb']         = (number_format($xishu[0]['xfyswczb'],1,'.','')).'%';//续费月数完成率指标
        $arr['xfyswcldc']        = (round(($arr['xufeilyuewanchenglv']/$arr['xfyswczb']),3))*100;
        $arr['xfyswcldc']        = number_format($arr['xfyswcldc'],1,'.','');
        $arr['xfyswcldc']        = $arr['xfyswcldc'].'%';//续费月数完成率达成
        $arr['qnxfyswcljz']      = (number_format($xishu[0]['qnxfyswcljz'],1,'.','')).'%';//全年续费月数完成率均值
        $arr['xshxfyswcl']       = (number_format(($arr['xufeilyuewanchenglv']/$xishu[0]['xfywclxs']),1,'.','')).'%';//系数后续费月数完成率
        $arr['xflyswczg']        = (number_format($xishu[0]['xflyswczg'],1,'.','')).'%';//续费月数完成率封顶值
        $arr['over_month']     = (($arr['xufeilyuewanchenglv'] - $arr['xfyswczb'])*$total_renew_month_point)/100;
        if($arr['over_month'] <= 0){
            $arr['over_month'] = 0;
        }
        // if($arr['over_month'] !== 0){
        //     $arr['over_month'] = round($arr['over_month'],1);//续费月数
        // }
        $arr['over_month'] = number_format($arr['over_month'],1,'.','');//续费月数
        return $arr;
    }

    /**
     * 查询的城市续费月数完成率信息写入数据库（只在每月11号的凌晨执行）qz_sale_cityrenew_months
     * @param  [array]  $data  查询统计的续费月数完成率信息
     * @return [void] 
     */    
    public function writeCityReNewMonthIn($data)
    {
        foreach ($data as $k => $v) {
            $value['dept'] = $v['dept'];
            $value['city'] = $v['city']; 
            $value['cid'] = $v['cid'];
            $value['brand_division'] = $v['brand_division'];
            $value['brand_regiment'] = $v['brand_regiment'];
            $value['brand_manage'] = $v['brand_manage'];
            $value['daoqi'] = $v['daoqi'];
            $value['renew_month_point'] = $v['renew_month_point'];
            $value['renew_month'] = $v['renew_month'];
            $value['renew_month_rate'] = $v['renew_month_rate'];
            $value['renew_month_rate_point'] = $v['renew_month_rate_point'];
            $value['renew_month_rate_complete'] = $v['renew_month_rate_complete'];
            $value['renew_year_value'] = $v['renew_year_value'];
            $value['renew_monthly_rate'] = $v['renew_monthly_rate'];
            $value['renew_max'] = $v['renew_max'];
            $value['over_month'] = $v['over_month'];
            $value['time'] = strtotime($v['time']);

            M('sale_cityrenew_months')->add($value);
        }
    }


    /**
     * 查询历史会员
     */
    public function getCityCompanys($time)
    {
        //查询2017-02会员ID
        $search_time = $time;
        $time_start = strtotime($time.'-1 month');
        $time_end = strtotime($time)-1;
        $contracts_time = date('Y-m-d',(strtotime($search_time)));
        $citys = M('sales_city_manage')->alias('m')
                                    ->join('left join qz_quyu as q on m.city = q.cname')
                                    ->join("left join qz_log_user_real_company as c on q.cid = c.city_id and c.time = '$search_time'")
                                    ->field("m.id,m.dept,m.city,q.cid,IF (c.vip_num, c.vip_num, 0) AS totalvip,IF (c.vip_count, c.vip_count, 0) AS realvip,c.companys")
                                    ->select();
        foreach ($citys as $k => $v) {

            $city_fen = $city_zeng = 0;
            
            if($v['realvip'] > 0){
                //时间段内有会员
                $companys_arr = explode(',', $v['companys']);
                array_pop($companys_arr);
                
                foreach ($companys_arr as $key => $value) {
                    $c_map['type'] = array('IN',array('2','8'));
                    $c_map['company_id'] = $value;
                    $c_map['start_time'] = array('ELT',$contracts_time);
                    $c_map['end_time'] = array('EGT',$contracts_time);
                    $user_contracts = M('user_vip')->where($c_map)->order('id desc')->select();
                    //$citys[$k]['company_content'][$key] = $user_contracts[0];

                    $map    = array(
                        'i.com'     => array('EQ',  $user_contracts[0]['company_id']),
                        'i.addtime' =>array('BETWEEN', array($time_start, $time_end)),
                    );
                    $odr    = M('order_info')->alias('i')
                        ->join('INNER JOIN qz_orders o ON o.id = i.`order` and o.`on` = 4')
                        ->where($map)->group('i.com')
                        ->field(array('i.com',
                            'sum(if(i.type_fw = 2, 1, 0))' => 'cntw',
                            'sum(if(i.type_fw = 1, 1, 0))' => 'cntf',
                        ))->select();
                    
                    //所有的会员合同日期
                    $company['company_id'] = $user_contracts[0]['company_id'];
                    $company['company_name'] = $user_contracts[0]['company_name'];
                    $company['start_time'] = $user_contracts[0]['start_time'];
                    $company['end_time'] = $user_contracts[0]['end_time'];
                    $company['fendan'] = $odr[0]['cntf'];
                    $company['zengdan'] = $odr[0]['cntw'];
                    $company_contract[] = $company;

                    $city_fen = $city_fen + $company['fendan']; 
                    $city_zeng = $city_zeng + $company['zengdan'];
                }
            }
            $citys[$k]['fendan'] = $city_fen;
            $citys[$k]['zengdan'] = $city_zeng;
        }
        return $citys;
    }

}

