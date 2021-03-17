<?php

namespace Home\Model;
Use Think\Model;

/**
* 销售系统 统计
*/

class SaleStatsModel extends Model
{
    protected $autoCheckFields = false;



    //按帐号取城市列表
    public function getHyfzCitys($map){
        return M("quyu")->alias("q")
                ->field("q.cid,q.cname,q.bm,v.typeid as city_type,v.pid,v.id as valueId,c.name as catename,xs.pid as xsPid")
                ->join("LEFT JOIN qz_sales_setting_value as v on v.cid = q.cid AND v.typeid IN ('1','5')")
                ->join("LEFT JOIN qz_sales_category as c on c.id = v.pid")
                ->join("LEFT JOIN qz_sales_setting_value as xs on xs.cid = q.cid AND xs.typeid = '4'")
                ->where($map)
                ->order('q.bm desc')
                ->select();
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

    //获取会员数量
    public function getMemberCount(){
        $map['a.classid'] = array('IN',array(3,6));
        $map['b.fake'] = '0';
        $map['a.on'] = '2';
        return M("user")->alias("a")
                ->field("a.cs,sum(if(a.on = 2,b.viptype,null)) as vipcnt,sum(if(b.viptype > 1,(b.viptype-1),null)) as doublecnt,count(if(a.on = 2,a.id,null)) as vipnum,b.viptype")
                ->join("inner join qz_user_company b on a.id = b.userid")
                ->where($map)
                ->group('a.cs')
                ->order('vipcnt desc')
                ->select();
    }

    //取本月订单数
    public function getCityOrders($start,$end){
        //dump($start);
        if(empty($start)){
            $start = strtotime(date("Y-m-01"));
        }
        if(empty($end)){
            $end = time();
        }
        $sql = "select t1.cs,count(t1.cs) as count from(
                    select a.id,a.cs from (
                        select id,cs from qz_orders where time_real between $start and $end
                    ) as a LEFT join qz_order_info as b on a.id = b.order group by a.id
                ) as t1 group by t1.cs";
        $model = new Model();
        return $model->query($sql);
    }

    //查询会员开始或结束时间
    public function getMemberTime($map){
        $map['on'] = '2';
        return M('user')->field('id as uid, DATE(start) AS start_time,DATE(end) AS end_time,cs')
        ->where($map)
        ->order('start,end')
        ->select();
    }











    //取单个分类信息
    public function getCategoryById($id){
        $map = array(
            'id' => array("EQ",$id),
            'status' => array("EQ",'0')
        );
        return M('sales_category')->field('*')->where($map)->find();
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

    //取当前时段总数据
    public function getOrderLoactionsByDay($condition){
        return M("orders")->field('FROM_UNIXTIME(time_real,"%Y-%m-%d") AS days,source,count(*) AS num')
                            ->where($condition)
                            ->group('days,source')
                            ->order('days')
                            ->select();
    }




}