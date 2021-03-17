<?php
/**
 * 未量房订单/二次回访订单
 */

namespace Home\Model\Db;

use Think\Model;

class CompanyLiangFangModel extends Model
{
    protected $tableName = "company_liangfang";

    public function addLiangfangInfo($data)
    {
        return M('company_liangfang')->add($data);
    }

    public function saveLiangfangInfo($where, $data)
    {
        return M('company_liangfang')->where($where)->save($data);
    }

    public function findLiangfangInfo($where)
    {
        return M('company_liangfang')->where($where)->find();
    }

    public function getPushCompanyInfo($where)
    {
        return M('company_liangfang_correlation')->where($where)->select();
    }

    public function addAllLfCompany($save){
        return M('company_liangfang_correlation')->addAll(array_values($save));
    }

    public function getOrdersLiangfangCount($map)
    {
        $where = [];
        //筛选出订单条件
        if($map['on']){
            $where['a.on'] = $map['on'];
        }
        if($map['type_fw']){
            $where['a.type_fw'] = $map['type_fw'];
        }
        if($map['time_real']){
            $where['a.time_real'] = $map['time_real'];
        }

        //1.筛选出 每一条分配给装修公司的数据 , 按 [已签单/4>已量房.3>待选择/2(只要有一个没有选就还有机会)>未量房/1] 排序出订单当前状态
        $buildSql = M('orders')->alias('a')
            ->field('a.id,a.on,a.type_fw,a.cs,a.time_real,c.status,IF(c.STATUS = 0, 2, 0) choose,IF(c.STATUS = 1, 3, 0) liangfang,IF(c.STATUS = 4, 4, 0) qianyue')
            ->join('join qz_order_company_review c ON c.orderid = a.id')
            ->where($where)
            ->order('qianyue desc,liangfang desc,choose desc')
            ->buildSql();
        //2.如果 装修公司选未量房数 等于 订单分配的装修公司数 , 那么订单则是未量房
        $buildSql = M('orders')->table($buildSql)->alias('a1')
            ->field('a1.*,count(IF(a1.status = 3,1,NULL)) AS choose_no_liangfang,count(a1.id) company_count')
            ->group('a1.id')
            ->buildSql();
        $buildSql = M('orders')->table($buildSql)->alias('t1')
            ->field('t1.id,t1.`on`,t1.type_fw,t1.cs,t1.time_real,t1.choose,t1.liangfang,t1.qianyue,IF(t1.choose_no_liangfang = t1.company_count,1,0) no_liangfang')
            ->buildSql();
        //3.筛选出附属表条件 , 查询附属数据
        $where = [];
        if($map['group_id']){
            $where['d.id'] = $map['group_id'];
        }
        if($map['src']){
            $where['c.src'] = $map['src'];
        }
        if($map['alias']){
            $where['c.alias'] = $map['alias'];
        }
        if($map['dept']){
            $where['c.dept'] = $map['dept'];
        }
        //筛选订单量房状态
        if($map['lf_status']){
            switch ($map['lf_status']){
                case '1':
                    //未量房
                    $where['t.no_liangfang'] = ['eq', 1];
                    break;
                case '2':
                    //待选择
                    $where['t.choose'] = ['eq', 2];
                    break;
                case '3':
                    //已量房
                    $where['t.liangfang'] = ['eq', 3];
                    break;
                case '4':
                    //已签约
                    $where['t.qianyue'] = ['eq', 4];
                    break;
            }
        }
        return M('orders')->table($buildSql)->alias('t')
            ->join('JOIN qz_yy_order_info as b on b.oid = t.id')
            ->join('JOIN qz_order_source as c on c.src = b.src')
            ->join('JOIN qz_order_source_group as d on c.groupid = d.id')
            ->join('JOIN qz_quyu as q on q.cid = t.cs')
            ->where($where)
            ->count(1);
    }

    public function getOrdersLiangfangList($map,$pageIndex,$pageCount)
    {
        $where = [];
        //筛选出订单条件
        if($map['on']){
            $where['a.on'] = $map['on'];
        }
        if($map['type_fw']){
            $where['a.type_fw'] = $map['type_fw'];
        }
        if($map['time_real']){
            $where['a.time_real'] = $map['time_real'];
        }

        //1.筛选出 每一条分配给装修公司的数据 , 按 [已签单/4>已量房.3>待选择/2(只要有一个没有选就还有机会)>未量房/1] 排序出订单当前状态
        $buildSql = M('orders')->alias('a')
            ->field('a.id,a.on,a.type_fw,a.cs,a.time_real,c.status,IF(c.STATUS = 0, 2, 0) choose,IF(c.STATUS = 1, 3, 0) liangfang,IF(c.STATUS = 4, 4, 0) qianyue')
            ->join('join qz_order_company_review c ON c.orderid = a.id')
            ->where($where)
            ->order('qianyue desc,liangfang desc,choose desc')
            ->buildSql();
        //2.如果 装修公司选未量房数 等于 订单分配的装修公司数 , 那么订单则是未量房
        $buildSql = M('orders')->table($buildSql)->alias('a1')
            ->field('a1.*,count(IF(a1.status = 3,1,NULL)) AS choose_no_liangfang,count(a1.id) company_count')
            ->group('a1.id')
            ->buildSql();
        $buildSql = M('orders')->table($buildSql)->alias('t1')
            ->field('t1.id,t1.`on`,t1.type_fw,t1.cs,t1.time_real,t1.choose,t1.liangfang,t1.qianyue,IF(t1.choose_no_liangfang = t1.company_count,1,0) no_liangfang')
            ->buildSql();
        //3.筛选出附属表条件 , 查询附属数据
        $where = [];
        if($map['group_id']){
            $where['d.id'] = $map['group_id'];
        }
        if($map['src']){
            $where['c.src'] = $map['src'];
        }
        if($map['alias']){
            $where['c.alias'] = $map['alias'];
        }
        if($map['dept']){
            $where['c.dept'] = $map['dept'];
        }
        //筛选订单量房状态
        if($map['lf_status']){
            switch ($map['lf_status']){
                case '1':
                    //未量房
                    $where['t.no_liangfang'] = ['eq', 1];
                    break;
                case '2':
                    //待选择
                    $where['t.choose'] = ['eq', 2];
                    break;
                case '3':
                    //已量房
                    $where['t.liangfang'] = ['eq', 3];
                    break;
                case '4':
                    //已签约
                    $where['t.qianyue'] = ['eq', 4];
                    break;
            }
        }
        return M('orders')->table($buildSql)->alias('t')
            ->field('t.*,c.src,c.name src_name,c.groupid,c.alias,d.name AS group_name,q.cname,dm.name AS dept_name')
            ->join('JOIN qz_yy_order_info as b on b.oid = t.id')
            ->join('JOIN qz_order_source as c on c.src = b.src')
            ->join('left JOIN qz_department_identify as dm on dm.id = c.dept')
            ->join('JOIN qz_order_source_group as d on c.groupid = d.id')
            ->join('JOIN qz_quyu as q on q.cid = t.cs')
            ->where($where)
            ->limit($pageIndex,$pageCount)
            ->select();
    }
}