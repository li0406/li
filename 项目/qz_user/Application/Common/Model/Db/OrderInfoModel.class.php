<?php
/**
 * 装修公司回访记录表
 */

namespace Common\Model\Db;

use Think\Model;

class OrderInfoModel extends Model
{
    protected $tableName = "order_info";

    /**
     * 获取装修公司需要提示的订单
     * author: mcj
     * @param int $company_id
     * @return mixed
     */
    public function getRemindOrder($map = [], $day_limit = 15)
    {
        $today = date('Y-m-d');
        return M('order_info')
            ->alias('a')
            ->strict(true)
            ->field("a.`order`,a.addtime,b.sex,b.qiandan_status,b.name,b.xiaoqu,b.mianji,q.cname,d.qz_area as qx")
            ->where($map)
            ->where("c.status = 0 and (DATEDIFF('$today',FROM_UNIXTIME(a.addtime)) - a.ignore_day) >= $day_limit")
            ->join("inner join qz_orders as b on b.id = a.`order`")
            ->join("left join qz_order_company_review as c on c.orderid = a.`order` and c.comid = a.com")
            ->join("left join qz_area as d on d.qz_areaid = b.qx")
            ->join('left join qz_quyu AS q ON q.cid = b.cs')
            ->order('a.addtime desc')
            ->select();
    }

    /**
     *计算需要处理的公司分单
     * author: mcj
     * @param $map
     * @return mixed
     */
    public function getNeedToDo($map,$condition=[]){
        return M('order_info')
            ->alias('a')
            ->strict(true)
            ->where($map)
            ->join("inner join qz_orders as b on b.id = a.`order`")
            ->join("left join qz_order_company_review as c on c.orderid = a.`order` and c.comid = a.com")
            ->where($condition)
            ->count();
    }

    public function getOrder($map){
        return M('order_info')
            ->field("*")
            ->strict(true)
            ->where($map)
            ->select();
    }

    public function updateInfo($map=[],$data){
        return M('order_info')->where($map)->save($data);
    }

    public function addOrderCompany($save)
    {
        return $this->add($save);
    }

    /**
     * 获取订单已经分配的装修公司
     * @param $order_id
     * @return mixed
     */
    public function getOrderCompanyInfoByWhere($where){
        return $this->where($where)->select();
    }
}