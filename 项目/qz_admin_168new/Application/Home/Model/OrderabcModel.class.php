<?php

/**
 * 每月ABC订单分析
 */

namespace Home\Model;
use Think\Model;

class OrderabcModel extends Model {

    protected $autoCheckFields = false;


    /**
     * 获取上下会员数据
     * 
     * @param  [type] $start 开始时间
     * @param  [type] $end   结束时间
     * 
     * @return [type]        array
     */
    public function getUserVip($start,$end){

        $map['v.type'] = array('IN','2,8');
        $map['v.start_time'] = array('ELT',date('Y-m-t',$start));
        $map['v.end_time'] = array('EGT',date('Y-m'.'-01',$start));

        return M('user_vip')->alias('v')
                        ->field('v.id,v.company_id,v.company_name,v.type,v.start_time,v.end_time,v.time,u.cs,if(b.viptype > 1,(b.viptype-1),0) as doublecnt')
                        ->join('INNER JOIN qz_user as u ON u.id = v.company_id')
                        ->join('INNER JOIN qz_user_company b on b.userid = v.company_id')
                        ->where($map)
                        ->order('u.cs desc')
                        ->select();
    }


    /**
     * 获取 城市某个时间段 订单/分单 数
     *
     * @param      <type>  $start  The start time
     * @param      <type>  $end    The end time
     *
     * @return     <type>  The order number by month.
     */
    public function getCityOrderByTime($start,$end){

        $map['time_real'][] = array('EGT',$start);
        $map['time_real'][] = array('ELT',$end);

        $sql = M('orders')->field('FROM_UNIXTIME(time_real,"%Y") AS years,count(id) as num,SUM(CASE type_fw WHEN 1 THEN 1 ELSE 0 END) AS fendan,cs')
                            ->where($map)
                            ->group('years,cs')
                            ->buildSql();

        $_result = M()->table($sql)->alias('o')
                        ->field('o.*,q.cname,q.little,q.manager')
                        ->join('LEFT JOIN qz_quyu as q on q.cid = o.cs')
                        ->select();

        foreach ($_result as $k => $v) {          
            $result[$v['cs']] = $v;
        }
        unset($_result);
        return $result;
    }

    /**
     * 获取 所有后台可见区域
     *
     * @return     array  The quyu list.
     */
    public function getQuyu(){
        $map['type'] = array('EQ',1);
        return M('quyu')->field('cid,cname,bm,px,px_abc,little,mark_red,manager,point')->where($map)->order('px_abc')->select();
    }

}