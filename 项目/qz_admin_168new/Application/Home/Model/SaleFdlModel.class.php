<?php

namespace Home\Model;
Use Think\Model;

/**
* 销售系统统计 - 分单量
*/

class SaleFdlModel extends Model
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
            'cs' => array("EQ",$id),
            'status' => array("EQ",'0')
        );
        return M('orders')->field('id,cs')->where($map)->find();
    }

    //按条件取分类列表
    public function getCategory($map){
        $map['status'] = array("EQ",'0');
        return M("sales_category")->field('*')->where($map)->order('id')->select();
    }

    //取城市分单量 按时间
    public function getOrderNumByTime($start,$end){
        $map['start'] = $start;
        $map['end'] = $end;
        $sql = 'select t1.cs,count(t1.cs) as count from(
        select a.cs,b.type_fw from (
            select id,cs from qz_orders where time_real between '.$map['start'].' and '.$map['end'].'
        ) as a
        LEFT join qz_order_info as b on a.id = b.order group by a.id
        ) as t1 group by t1.cs';
        return M()->query($sql);
    }


    //按城市取会员数列表
    public function getCityList($condition,$pagesize= 1,$pageRow = 10){

        $map['s.typeid'] = '1';
        $map['s.module'] = '1';

        if(!empty($condition['cs'])){
            $map['q.cid'] = array('IN',$condition['cs']);
        }

        $result = M("quyu")->alias("q")
                ->field("q.cid,q.bm,q.cname,s.point")
                ->join("left join qz_sales_setting_value s on s.cid = q.cid ")
                ->where($map)
                ->order('bm DESC')
                ->limit($pagesize.",".$pageRow)
                ->select();

        $count  = M("quyu")->alias("q")
                ->join("left join qz_sales_setting_value s on s.cid = q.cid ")
                ->where($map)->count();

        return array("result" => $result,"count" => $count);
    }

    //获取分单需求数据
    public function getAllFendanNum($start){
        $start = strtotime(date('Y-m-01',$start));
        $map['start'] = date('Y-m-d',$start);
        return M('sales_order_points')
                ->field('cityid,sum(point) as point_num')
                ->where($map)
                ->group('cityid')
                ->select();
    }

    //取实际会员数 - 按时间
    public function getCityVipNumByDate($start,$end){
        $map['time'] = date('Y-m-d 00:00:00',$end);
        return M('log_user_real_company')
                ->field('city_id,vip_count,vip_num')
                ->where($map)
                ->select();
    }



    //获取每个城市会员数
    public function getAllCityVipNum(){
        $map['u.classid'] = array('IN',array(3,6));
        $map['u.on'] = '2';
        $map['b.fake'] = '0';
        return  M("user")->alias("u")
                ->field("u.cs,sum(if(u.on = 2,b.viptype,null)) as vipnum")
                ->join("inner join qz_user_company b on u.id = b.userid")
                ->where($map)
                ->group('u.cs')
                ->order('vipnum desc')
                ->limit($pagesize.",".$pageRow)
                ->select();
    }


}