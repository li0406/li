<?php

namespace Home\Model\Db;
Use Think\Model;

class OrderCompanyReviewModel extends Model
{
    protected $tableName = "order_company_review";
    /**
     * 根据订单信息获取反馈信息
     * @param  [type] $order_id [description]
     * @return [type]           [description]
     */
    public function getReviewInfoByOrderId($order_id,$field = 'a.*')
    {
        $map = array(
            "a.orderid" => array("EQ",$order_id)
        );
        return M("order_company_review")->alias("a")
            ->join("join qz_user u on u.id = a.comid")
            ->field($field)->where($map)->order('a.comid')->select();
    }

    /**
     * 根据订单ID删除反馈信息
     * @param  [type] $order_id [description]
     * @return [type]           [description]
     */
    public function delReviewInfoByOrderId($order_id)
    {
        $map = array(
            "orderid" => array("EQ",$order_id)
        );
        return M("order_company_review")->where($map)->delete();
    }

    public function addAllInfo($data)
    {
        return M("order_company_review")->addAll($data);
    }

    public function editReviewInfo($order_id,$company_id,$data)
    {
        $map = array(
            "orderid" => array("EQ",$order_id),
            "comid" => array("EQ",$company_id),
        );
        return M("order_company_review")->where($map)->save($data);
    }

    /**
     * 获取未量房订单总数(该订单分配的所有公司都勾选了未量房)
     */
    public function getNotLfListCount($map)
    {
        $where = [];
        if($map['lf_status']){
            //数据结构中添加了标识, 1:已回访的数据 2.未回访的数据
            switch ($map['lf_status']){
                case '-1':
                    $where['t2.lf_status'] = '2';
                    break;
                case '-2':
                    $where['t2.lf_status'] = '1';
                    break;
            }
            unset($map['lf_status']);
        }
        //查出订单勾选未量房数量和分配公司数
        $buildSql = M('order_company_review')
            ->field('orderid,reason,count(if(`status`=3,1,null)) as unliangfang_count,count(comid) as com_count')
            ->group('orderid')
            ->buildSql();
        //给符合条件的订单打标识
        $buildSql = M('order_company_review')->table($buildSql)->alias('t')
            ->field('t.*,if(t.com_count=t.unliangfang_count,1,0) as xianshi')
            ->buildSql();
        $buildSql = M('order_company_review')->table($buildSql)->alias('t1')
            ->field('t1.orderid,t1.reason,o.time_real,o.wzd,o.mianji,o.cs,c.cname AS city,
            a.qz_area AS area,o.tel,p.op_name,l.*,l.status as back_status,if(l.id is null,2,1) as lf_status')
            ->join("join qz_orders as o on o.id = t1.orderid")
            ->join("JOIN qz_order_pool AS p ON p.orderid = o.id")
            ->join("left join qz_quyu as c on o.cs = c.cid")
            ->join("LEFT JOIN qz_area a ON a.qz_areaid = o.qx")
            ->join("LEFT JOIN qz_company_liangfang l ON l.order_id = o.id")
            ->where($map)
            ->buildSql();
        return M('order_company_review')->table($buildSql)->alias('t2')->where($where)->count();

    }
    /**
     * 获取未量房订单(分配的所有公司都勾选了未量房)
     */
    public function getNotLfList($map,$page, $pageCount)
    {
        $where = [];
        if($map['lf_status']){
            //数据结构中添加了标识, 1:已回访的数据 2.未回访的数据
            switch ($map['lf_status']){
                case '-1':
                    $where['t2.lf_status'] = '2';
                    break;
                case '-2':
                    $where['t2.lf_status'] = '1';
                    break;
            }
            unset($map['lf_status']);
        }
        //查出订单勾选未量房数量
        $buildSql = M('order_company_review')
            ->field('orderid,reason,count(if(`status`=3,1,null)) as unliangfang_count,count(comid) as com_count')
            ->group('orderid')
            ->buildSql();
        //给符合条件的订单打标识
        $buildSql = M('order_company_review')->table($buildSql)->alias('t')
            ->field('t.*,if(t.com_count=t.unliangfang_count,1,0) as xianshi')
            ->buildSql();
        $buildSql = M('order_company_review')->table($buildSql)->alias('t1')
            ->field('t1.orderid,t1.reason,o.time_real,o.wzd,o.mianji,o.cs,c.cname AS city,
            a.qz_area AS area,o.tel,p.op_name,l.update_time,l.back_remark as remark_status,l.status as back_status,if(l.id is null,2,1) as lf_status,GROUP_CONCAT(lt.ordercall_id) as backtel,l.remark as two_remark')
            ->join("join qz_orders as o on o.id = t1.orderid")
            ->join("JOIN qz_order_pool AS p ON p.orderid = o.id")
            ->join("left join qz_quyu as c on o.cs = c.cid")
            ->join("LEFT JOIN qz_area a ON a.qz_areaid = o.qx")
            ->join("LEFT JOIN qz_company_liangfang l ON l.order_id = o.id")
            ->join("LEFT JOIN qz_company_liangfang_telback lt ON lt.order_id = o.id")
            ->where($map)
            ->group('t1.orderid')
            ->order('t1.orderid desc')
            ->buildSql();
        return M('order_company_review')->table($buildSql)->alias('t2')->where($where)
            ->limit($page,$pageCount)->select();

    }
    /**
     * 获取未量房订单(分配的所有公司都勾选了未量房)
     */
    public function getNotLfCompanyList($id)
    {
        $where['t.orderid'] = ['eq',$id];
        //查出订单分配几家装修公司和选择勾选未量房数量
        return M('order_company_review')->alias('t')
            ->field('t.*,u.jc,GROUP_CONCAT(a.tag) as tags')
            ->join("join qz_user as u on u.id = t.comid")
            ->join("left join qz_company_tags as a on a.company_id = t.comid")
            ->where($where)
            ->group('comid')
            ->select();

    }

    public function getNoLfReasonList($orderno){
        $where['r.orderid'] = ['eq',$orderno];
        $where['r.status'] = ['eq',3];
        return M('order_company_review')->alias('r')
            ->field('u.jc,r.comid,r.reason,r.remark')
            ->join('qz_user as u on u.id = r.comid')
            ->where($where)
            ->select();
    }

	/**
	 * 根据订单id获取最新量房时间
	 * @param $orderId
	 * @return mixed
	 */
	public function getLastestLfTime($orderId)
	{
		$map = [
			'status'=>1,
			'orderid'=>$orderId
		];
	    $ret = $this->alias('a')->where($map)->order('time desc')->getField('lf_time');
	    return $ret;
	}

    /**
     * 是否量房
     * @param $orderId
     * @return mixed
     */
	public function isLf($orderId)
	{
        $map['status'] = ['in', [1, 2, 4]];
        $map['orderid'] = ['eq', $orderId];
        $ret = $this->alias('a')->where($map)->count();
        return $ret;
	}

    // 获取订单量房状态
    public function getOrdersLfStatus($orderIds){
        $map = array(
            "a.orderid" => array("IN", $orderIds)
        );

        return M("order_company_review")->alias("a")->where($map)
            ->join("join qz_user u on u.id = a.comid")
            ->field([
                "a.orderid",
                "count(IF(a.status IN(1,2,4), 1, null)) as lf_count",
                "count(IF(a.status = 3, 1, null)) as nolf_count",
            ])
            ->group("a.orderid")
            ->select();
    }

    public function delReviewInfoByOrderIdAndCompanyId($company = [],$order_id = 0)
    {
        $map = array(
            "orderid" => array("EQ",$order_id),
            "comid" => array("IN",$company)
        );
        return M("order_company_review")->where($map)->delete();
    }
}