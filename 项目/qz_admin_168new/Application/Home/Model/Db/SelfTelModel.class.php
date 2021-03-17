<?php

namespace Home\Model\Db;

use Think\Model;

class SelfTelModel extends Model
{

    protected $autoCheckFields = false;
    protected $tableName = 'log_holly_diy_telcenter';

    public function getTelListCount($map)
    {
        $where = $map;

        //主叫号码
        if (!empty($where['ht.call_no'])) {
            $where['ht.cust_callee_clid'] = $where['ht.call_no'];
            unset($where["ht.call_no"]);
        }
        //被叫号码
        if (!empty($where['ht.called_no_encrypt'])) {
            $where['ht.cdr_customer_number_encrypt'] = $where['ht.called_no_encrypt'];
            unset($where["ht.called_no_encrypt"]);
        }

        $nextSql = M("log_clink_diy_telcenter")->alias("hd")->where($where)
                                               ->join("left join qz_log_clink_telcenter ht on  ht.call_sid = hd.call_sid")
                                                ->field("hd.id")->buildSql();
        $buildSql = $this->alias('hd')->field('hd.id')
            ->join('left join qz_log_holly_telcenter ht on ht.call_sid = hd.call_sid')
            ->where($map)
            ->buildSql();

        $buildSql = $this->table($buildSql)->alias("t")
             ->union($nextSql)
             ->buildSql();
        return $this->table($buildSql)->alias("t")->count();
    }

    public function getTelList($map, $page, $page_count)
    {
        $where = $map;

        //主叫号码
        if (!empty($where['ht.call_no'])) {
            $where['ht.cust_callee_clid'] = $where['ht.call_no'];
            unset($where["ht.call_no"]);
        }
        //被叫号码
        if (!empty($where['ht.called_no_encrypt'])) {
            $where['ht.cdr_customer_number_encrypt'] = $where['ht.called_no_encrypt'];
            unset($where["ht.called_no_encrypt"]);
        }

        $nextSql = M("log_clink_diy_telcenter")->alias("hd")->where($where)
            ->join("left join qz_log_clink_telcenter ht on  ht.call_sid = hd.call_sid")
            ->field("'clink' as channel,hd.id,ht.cust_callee_clid as fromtel,hd.call_obj as calltowho,hd.op_uname as admin_user,hd.call_sid as callsid,FROM_UNIXTIME(hd.time,'%Y-%m-%d %H:%i:%s') time_add,cdr_start_time as begin,cdr_end_time as emd, if(cdr_bridge_time <> 0,cdr_end_time - cdr_bridge_time,0) as duration")->buildSql();

        $buildSql = $this->alias('hd')
            ->field('"holly" as channel,hd.id,ht.call_no fromtel,hd.call_obj calltowho,hd.op_uname admin_user,hd.call_sid callSid,FROM_UNIXTIME(hd.time,"%Y-%m-%d %H:%i:%s") time_add,ht.begin starttime,ht.end endtime,ht.call_time_length duration')
            ->join('left join qz_log_holly_telcenter ht on ht.call_sid = hd.call_sid')
            ->where($map)->buildSql();

        $buildSql = $this->table($buildSql)->alias("t")
             ->union($nextSql)
             ->buildSql();

        return $this->table($buildSql)->alias("t")
            ->order('t.time_add desc')
            ->limit($page, $page_count)
            ->select();
    }

    public function getDiyTelByCallSid($call_sid)
    {
        $where = [
            'call_sid' => ['eq', $call_sid]
        ];
        return M('log_holly_telcenter')->field('id', 'call_sid', 'call_no', 'called_no', 'call_time_length', 'state', 'call_state', 'hanguper', 'createtime')
            ->where($where)
            ->order('createtime asc')
            ->select();
    }
}