<?php
/*
 * 订单分配装修公司表
 */

namespace Home\Model\Db;

use Think\Model;

class OrdersModel extends Model
{
	protected $tableName = "orders";
	
	public function getInfoByOrderId($field = '*', $where)
	{
		return $this->field($field)->where($where)->find();
	}



    public function getLastOrder($tellist,$date)
    {
        if(is_array($tellist)){
            $map['tel_encrypt'] = array('in',$tellist);
        }elseif($tellist){
            $map['tel_encrypt'] = array('EQ',$tellist);
        }

        if (!empty($date)) {
            $map['time_real'] = array('EGT',$date);
        }

        return$this->where($map)->field("id,tel_encrypt")->order("time_real desc")->group("tel_encrypt")->select();
    }

    public function getCityOrderList($city,$begin,$end)
    {
        $map = array(
            "o.time_real" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        if (!empty($city)) {
            $map['o.cs'] = array('EQ',$city);
        }


        return $this->where($map)->alias("o")
             ->field("o.cs,count(o.id) as count")
             ->group("o.cs")->select();

    }

    public function updateOrders($sql)
    {
        return $this->execute($sql);
    }

    public function getAllOpenCitys()
    {
        $city =  S("168new:OpenCity:orderImportant");

        if(!$city){
            $map = array(
                'is_open_city' => array('EQ', 1),
                'cid' => array('NEQ', "000001")
            );

            $city = M('quyu')->where($map)->field('cid,cname,upper(left(px_abc,1)) AS first_abc')->order("bm,xh")->select();
            S("168new:OpenCity:orderImportant", $city, 300);
        }

        return $city;
    }

    public function getCityListCount($input)
    {
        $map = array(
            'q.is_open_city' => array('EQ', 1),
            'q.cid' => array('NEQ', "000001")
        );

        $this->cityListMap($input, $map);

        $count = M('quyu')->alias('q')->join('LEFT JOIN qz_order_important_city c ON q.cid=c.city_id')->where($map)->count('q.cid');
        
        return $count ?: 0;
    }

    public function getCitysList($input, $start=0, $limit=20)
    {
        $map = array(
            'q.is_open_city' => array('EQ', 1),
            'q.cid' => array('NEQ', "000001")
        );

        $this->cityListMap($input, $map);

        $list = M('quyu')->alias('q')->field('cid,cname,CASE WHEN c.city_id IS NULL THEN 2 ELSE 1 END is_important,c.created_at')->join('LEFT JOIN qz_order_important_city c ON q.cid=c.city_id')->where($map)->limit($start, $limit)->order('is_important,q.bm')->select();

        return $list;
    }

    public function cityListMap($input, &$map)
    {
        if($input['cid']){
            $map['q.cid'] = $input['cid'];
        }

        if($input['is_important']){
            $map['c.city_id'] = $input['is_important'] == 1 ? array('EXP', 'IS NOT NULL') : array('EXP', 'IS NULL');
        }

        return $map;
    }

    /*
    * 查询订单签约公司
    */
    public function getQiandanCompany($order_id){
        $map['o.id'] = ['eq', $order_id];
        $map['o.qiandan_companyid'] = ['gt', 0];
        $map['i.com'] = ['exp', 'IS NOT NULL'];

        return $this->alias('o')->field('o.id,u.jc,qiandan_addtime,o.qiandan_jine')->join('qz_order_info i ON o.id=i.`order`')->join('qz_user u ON o.qiandan_companyid=u.id')->where($map)->find();
    }

    // 获取会员公司分单量
    public function getCompanyFenOrders($companyIds, $begin, $end){
        $map = array(
            "o.`on`" => array("EQ", 4),
            "i.type_fw" => array("EQ", 1),
            "i.addtime" => array("BETWEEN", [
                strtotime($begin), strtotime($end) + 86399
            ])
        );

        return M("orders")->alias("o")->where($map)
            ->join("inner join qz_order_info as i on o.id = i.`order`")
            ->field("i.com,count(i.com) as order_count")
            ->group("i.com")
            ->select();
    }

    // 订单详情
    public function getOrderDetail($order_id){
        $map = array(
            "o.id" => array("EQ", $order_id)
        );

        $detail = M("orders")->alias("o")->where($map)
            ->join("left join qz_order_csos_new as csos on csos.order_id = o.id")
            ->join("left join qz_quyu as q on q.cid = o.cs")
            ->join("left join qz_area as area on area.qz_areaid = o.qx")
            ->join("left join qz_huxing as hx on hx.id = o.huxing")
            ->join("left join qz_fangshi as fs on fs.id = o.fangshi")
            ->join("left join qz_jiage as jg on jg.id = o.yusuan")
            ->join("left join qz_fengge as fg on fg.id = o.fengge")
            ->field([
                "o.id", "o.type_fw", "o.mianji", "o.xiaoqu", "o.name", "o.sex", "o.time_real", "o.lx", "o.lxs",
                "o.tel", "o.standby_user", "o.other_contact", "o.wx", "o.is_wx", "o.ip", "o.keys", "o.dz",
                "o.nf_time", "o.lftime", "o.start", "o.text", "o.shi",
                "o.cs as city_id", "o.qx as area_id", "q.cname as city_name", "area.qz_area as area_name",
                "o.huxing", "hx.`name` as huxing_name", "o.fangshi", "fs.`name` as fangshi_name",
                "o.yusuan", "jg.`name` as yusuan_name", "o.fengge", "fg.`name` as fengge_name",
                "csos.lasttime as csos_time"
            ])
            ->find();

        return $detail;
    }
}