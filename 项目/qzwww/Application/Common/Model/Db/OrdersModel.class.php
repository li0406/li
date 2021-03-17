<?php
/**
 *  订单表
 */

namespace Common\Model\Db;

use Think\Model;

class OrdersModel extends Model
{
    protected $tableName = "orders";


    //获取订单信息
    public function getBaojiaOrdersList($field = "*", $where, $limit)
    {
        $result = $this->field($field)
            ->where($where)
            ->order('time_real desc')
            ->limit($limit)
            ->select();
        return $result;
    }

    //订单池中最新发单的20条数据,取姓名首字后密文显示
    public function getLastAppoint($count)
    {
        $mapBase = [
            'o.name' => ['neq', ''],
        ];
        $field = 'cs,qx,o.time_real';
        $buildSql = $this->alias('o')
            ->where($mapBase)
            ->field($field)
            ->order('time_real desc')
            ->limit($count)
            ->buildSql();
        $ret = $this->table($buildSql)->alias('t')
            ->join('qz_quyu AS city on city.cid = t.cs')
            ->join('qz_area AS area on area.qz_areaid = t.qx')
            ->field('city.cname city,area.qz_area area,t.time_real')
            ->select();
        return $ret;
    }

}