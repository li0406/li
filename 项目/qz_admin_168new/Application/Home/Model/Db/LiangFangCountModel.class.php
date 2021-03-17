<?php

namespace Home\Model\Db;

Use Think\Model;

class LiangFangCountModel extends Model
{
    protected $autoCheckFields = false;

    public function countLiangFang($map)
    {
        $result = $this->_getLiangFangBulidSql($map);
        return  M()
            ->strict(true)
            ->table($result['build_sql'])
            ->alias("d")
            ->field("d.*,e.*,f.order_id as lf_order_id")
            ->join("inner JOIN qz_orders as e on e.id = d.`order_id`")
            ->join("LEFT JOIN qz_company_liangfang as f on f.order_id =d.`order_id`")
            ->join('LEFT JOIN qz_order_pool AS p ON p.orderid = d.`order_id`')
            ->where($result['condition'])
            ->count();
    }

    protected function _getLiangFangBulidSql($map){
        $buildSql_order_info_all = M("order_info")
            ->alias("a")
            ->strict(true)
            ->field("a.addtime,a.`order` as order_id,case b.status when 3 then 2 when 1 then 3 when 4 then 3 else 1 end as review_status,b.vip_channel,if(b.liangfang,b.liangfang,3) as yzstatus,b.yz_channel")
            ->join("LEFT JOIN qz_order_company_review as b on b.orderid = a.`order` and b.comid=a.com")
            ->where(['a.addtime'=>$map['time']])
            ->buildSql();
        $buildSql_order_info = M()
            ->table($buildSql_order_info_all)
            ->alias("c")
            ->field("MIN(c.addtime) as give_time,c.order_id,MAX(c.review_status) as lf_status,c.vip_channel,c.yzstatus,c.yz_channel")
            ->group("c.order_id")
            ->buildSql();
        $condition = [];
        if(isset($map['type_fw'])){
            $condition['e.type_fw'] = $map['type_fw'];
        }
        if(isset($map['measure'])){   //会员量房状态
            $condition['d.lf_status'] = $map['measure'];
        }
        if(isset($map['vip_channel'])){   //会员量房渠道
            $condition['d.vip_channel'] = $map['vip_channel'];
        }
        //业主量房状态
        if(isset($map['yzstatus'])){
            $condition['d.yzstatus'] = $map['yzstatus'];
        }
        //业主量房渠道
        if(isset($map['yz_channel'])){
            $condition['d.yz_channel'] = $map['yz_channel'];
        }

        if(isset($map['revisit'])){
            $condition['f.order_id'] = $map['revisit'];
        }
        if(isset($map['op_uid'])){
            $condition['p.op_uid'] = $map['op_uid'];
        }
        return ['build_sql' => $buildSql_order_info,'condition'=>$condition];
    }

    public function getLiangfang($map = [], $skip = 0, $limit = 20)
    {
       $result = $this->_getLiangFangBulidSql($map);
        return  M()
            ->strict(true)
            ->table($result['build_sql'])
            ->alias("d")
            ->field("d.*,e.type_fw,f.order_id as lf_order_id,p.op_name")
            ->join("inner JOIN qz_orders as e on e.id = d.`order_id`")
            ->join("LEFT JOIN qz_company_liangfang as f on f.order_id =d.`order_id`")
            ->join('LEFT JOIN qz_order_pool AS p ON p.orderid = d.`order_id`')
            ->where($result['condition'])
            ->limit($skip,$limit)
            ->order('d.give_time desc')
            ->select();
    }

    public function getLiangfangAll($map = [])
    {
        $result = $this->_getLiangFangBulidSql($map);
        return  M()
            ->strict(true)
            ->table($result['build_sql'])
            ->alias("d")
            ->field("d.*,e.type_fw,f.order_id as lf_order_id,p.op_name")
            ->join("inner JOIN qz_orders as e on e.id = d.`order_id`")
            ->join("LEFT JOIN qz_company_liangfang as f on f.order_id =d.`order_id`")
            ->join('LEFT JOIN qz_order_pool AS p ON p.orderid = d.`order_id`')
            ->where($result['condition'])
            ->order('d.give_time desc')
            ->select();
    }
}