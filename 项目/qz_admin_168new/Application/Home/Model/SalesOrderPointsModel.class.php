<?php

namespace Home\Model;
use Think\Model;

class SalesOrderPointsModel extends Model
{
    public function getSalesOrderPointsByCompanyIds($companyIds){
        if (empty($companyIds)) {
            return false;
        }
        if (!is_array($companyIds)) {
            $companyIds = array($companyIds);
        }
        //开始时间小于等于当前时间，且排序按照开始时间从大到小
        $map['p.userid'] = array('IN', $companyIds);
        $map['start'] = array('ELT', date('Y-m-d'));
        $build = M('sales_order_points')->alias('p')->field('userid,point')->where($map)->order('p.start DESC')->buildSql();
        $result = M()->table($build)->alias('z')->group('z.userid')->select();
        return $result;
    }
}