<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/2/13
 * Time: 14:12
 */

namespace Home\Model\Db;


class QcInfoModel
{
    //获取质检列表信息
    public function getqcinfolist($get,$page,$pageindex){
        $map["qc.status"] = ["in",[1,2]];
        //时间-开始
        if(!empty($get['start_time'])){
            $map["qc.time"] = ["EGT",strtotime($get['start_time'])];
        }
        //时间-结束
        if(!empty($get['end_time'])){
            $get['end_time'] = date('Y-m-d 23:59:59',strtotime($get['end_time']));
            $map["qc.time"] = ["ELT",strtotime($get['end_time'])];
        }
        if(!empty($get['start_time']) && !empty($get['end_time'])){
            $map["qc.time"] = ["between",[strtotime($get['start_time']),strtotime($get['end_time'])]];
        }
        //单号
        if(!empty($get['id'])){
            $map['qc.order_id'] = ['EQ',$get['id']];
        }
        //质检员
        if(!empty($get['op_uid'])){
            $map["qc.op_uid"] = $get['op_uid'];
        }
        //录音客服
        if(!empty($get['record_id'])){
            $map["qc.record_id"] = $get['record_id'];
        }
        //审核客服
        if(!empty($get['kf_id'])){
            $map["qc.kf_id"] = $get['kf_id'];
        }
        //审核团
        if(!empty($get['kf_group'])){
            $map["admin.kfgroup"] = $get['kf_group'];
        }
        //审核师
        if(!empty($get['kf_manager'])){
            $map["admin.kfmanager"] = intval($get['kf_manager']);
        }
        //质检类型
        if(!empty($get['type'])){
            $map["qc.type"] = $get['type'];
        }

        $buildSql =  M("qc_info")->alias('qc')
            ->field('qc.time,qc.order_id,qc.op_uid,qc.op_name,qc.type,o.type_fw,qc.record_id,qc.record_name,qc.kf_id,admin.name as kf_name,admin.kfgroup as kf_group,admin.kfmanager as kf_manager')
            ->join('qz_orders o on o.id = qc.order_id')
            ->join('left join qz_adminuser admin on admin.id = qc.kf_id')
            ->where($map)
            ->order('qc.time desc')
            ->limit($page,$pageindex)
            ->buildSql();

        $buildSql2 = M("qc_info")->table($buildSql)->alias('t')
            ->field('t.*,g.name as kf_group_name,m.name as kf_manager_name')
            ->join('left join qz_adminuser g on g.kfgroup = t.kf_group and g.uid=31 and g.kfgroup<>0')
            ->join('left join qz_adminuser m on m.id = substring_index(t.kf_manager,",",1)')
            ->group('t.order_id')
            ->buildSql();

        $result = M("qc_info")->table($buildSql2)->alias('t1')
            ->field('t1.*,score.qc_item_id,score.score,item.parentid')
            ->join('join qz_qc_info_item_score score on score.order_id = t1.order_id')
            ->join('join qz_qc_items_score item on item.id = score.qc_item_id')
            ->order('t1.time desc')
            ->select();
        return $result;
    }

    //获取质检列表信息-导出部分
    public function getqcinfolistexport($get){
        $map["qc.status"] = ["in",[1,2]];
        //时间-开始
        if(!empty($get['start_time'])){
            $map["qc.time"] = ["EGT",strtotime($get['start_time'])];
        }
        //时间-结束
        if(!empty($get['end_time'])){
            $get['end_time'] = date('Y-m-d 23:59:59',strtotime($get['end_time']));
            $map["qc.time"] = ["ELT",strtotime($get['end_time'])];
        }
        if(!empty($get['start_time']) && !empty($get['end_time'])){
            $map["qc.time"] = ["between",[strtotime($get['start_time']),strtotime($get['end_time'])]];
        }
        //单号
        if(!empty($get['id'])){
            $map['qc.order_id'] = ['EQ',$get['id']];
        }
        //质检员
        if(!empty($get['op_uid'])){
            $map["qc.op_uid"] = $get['op_uid'];
        }
        //录音客服
        if(!empty($get['record_id'])){
            $map["qc.record_id"] = $get['record_id'];
        }
        //审核客服
        if(!empty($get['kf_id'])){
            $map["qc.kf_id"] = $get['kf_id'];
        }
        //审核团
        if(!empty($get['kf_group'])){
            $map["admin.kfgroup"] = $get['kf_group'];
        }
        //审核师
        if(!empty($get['kf_manager'])){
            $map["admin.kfmanager"] = intval($get['kf_manager']);
        }
        //质检类型
        if(!empty($get['type'])){
            $map["qc.type"] = $get['type'];
        }

        $buildSql =  M("qc_info")->alias('qc')
            ->field('qc.time,qc.order_id,qc.op_uid,qc.op_name,qc.type,o.type_fw,qc.record_id,qc.record_name,qc.kf_id,admin.name as kf_name,admin.kfgroup as kf_group,admin.kfmanager as kf_manager')
            ->join('qz_orders o on o.id = qc.order_id')
            ->join('left join qz_adminuser admin on admin.id = qc.kf_id')
            ->where($map)
            ->order('qc.time desc')
            ->buildSql();

        $buildSql2 = M("qc_info")->table($buildSql)->alias('t')
            ->field('t.*,g.name as kf_group_name,m.name as kf_manager_name')
            ->join('left join qz_adminuser g on g.kfgroup = t.kf_group and g.uid=31 and kfgroup<>0')
            ->join('left join qz_adminuser m on m.id =substring_index(t.kf_manager,",",1)')
            ->group('t.order_id')
            ->buildSql();

        $result = M("qc_info")->table($buildSql2)->alias('t1')
            ->field('t1.*,score.qc_item_id,score.score,item.parentid')
            ->join('join qz_qc_info_item_score score on score.order_id = t1.order_id')
            ->join('join qz_qc_items_score item on item.id = score.qc_item_id')
            ->order('t1.time desc')
            ->select();

        return $result;
    }


    //获取质检列表数量
    public function getqcinfocount($get){
        $map["qc.status"] = ["in",[1,2]];
        //时间-开始
        if(!empty($get['start_time'])){
            $map["qc.time"] = ["EGT",strtotime($get['start_time'])];
        }
        //时间-结束
        if(!empty($get['end_time'])){
            $get['end_time'] = date('Y-m-d 23:59:59',strtotime($get['end_time']));
            $map["qc.time"] = ["ELT",strtotime($get['end_time'])];
        }

        if(!empty($get['start_time']) && !empty($get['end_time'])){
            $map["qc.time"] = ["between",[strtotime($get['start_time']),strtotime($get['end_time'])]];
        }

        //单号
        if(!empty($get['id'])){
            $map['qc.order_id'] = ['EQ',$get['id']];
        }
        //质检员
        if(!empty($get['op_uid'])){
            $map["qc.op_uid"] = $get['op_uid'];
        }
        //录音客服
        if(!empty($get['record_id'])){
            $map["qc.record_id"] = $get['record_id'];
        }
        //审核客服
        if(!empty($get['kf_id'])){
            $map["qc.kf_id"] = $get['kf_id'];
        }
        //审核团
        if(!empty($get['kf_group'])){
            $map["admin.kfgroup"] = $get['kf_group'];
        }
        //审核师
        if(!empty($get['kf_manager'])){
            $map["admin.kfmanager"] = intval($get['kf_manager']);
        }
        //质检类型
        if(!empty($get['type'])){
            $map["qc.type"] = $get['type'];
        }

        $buildSql =  M("qc_info")->alias('qc')
            ->field('qc.order_id')
            ->join('qz_orders o on o.id = qc.order_id')
            ->join('left join qz_adminuser admin on admin.id = qc.kf_id')
            ->where($map)
            ->buildSql();

        $buildSql2 =  M("qc_info")->table($buildSql)->alias('t1')
            ->field('t1.order_id')
            ->join('join qz_qc_info_item_score score on score.order_id = t1.order_id')
            ->join('join qz_qc_items_score item on item.id = score.qc_item_id')
            ->group('t1.order_id')
            ->buildSql();

        return M("qc_info")->table($buildSql2)->alias('t2')->count(1);

    }

    // 录音操作服务质量统计
    public function getqcinfoItem($get){
        $map["qc.status"] = ["in",[1,2]];
        //时间-开始
        if(!empty($get['start_time'])){
            $map["qc.time"] = ["EGT",strtotime($get['start_time'])];
        }
        //时间-结束
        if(!empty($get['end_time'])){
            $get['end_time'] = date('Y-m-d 23:59:59',strtotime($get['end_time']));
            $map["qc.time"] = ["ELT",strtotime($get['end_time'])];
        }

        if(!empty($get['start_time']) && !empty($get['end_time'])){
            $map["qc.time"] = ["between",[strtotime($get['start_time']),strtotime($get['end_time'])]];
        } elseif(empty($get['start_time']) && empty($get['end_time'])){
            $start = mktime(0,0,0,date("m"),1,date("Y"));
            $end = mktime(23,59,59,date("m"),date("d"),date("Y"));
            $map["qc.time"] = ["between",[$start,$end]];
        }

        $buildSql =  M("qc_info")->alias('qc')
            ->field('qc.time,qc.order_id,qc.record_id as kf_id,admin.name as kf_name,admin.kfgroup as kf_group,substring_index(admin.kfmanager, ",", 1) as kf_manager')
            ->join('left join qz_adminuser admin on admin.id = qc.record_id')
            ->where($map)
            ->order('qc.time desc')
            ->buildSql();
        $map = array();
        if (!empty($get['group'])) {
            $map["g.kfgroup"] = array("EQ",$get['group']);
        }
        if (!empty($get['manager'])) {
            $map["m.id"] = array("EQ",$get['manager']);
        }
        if (!empty($get['id'])) {
            $map["t.kf_id"] = array("EQ",$get['id']);
        }

        $buildSql2 =  M("qc_info")->table($buildSql)->alias('t')->where($map)
            ->field('t.*,g.name as kf_group_name,m.name as kf_manager_name')
            ->join('left join qz_adminuser g on g.kfgroup = t.kf_group and g.uid=31 and kfgroup<>0')
            ->join('left join qz_adminuser m on m.id = substring_index(t.kf_manager,",",1)')
            ->group('t.order_id')
            ->buildSql();

        $result = M("qc_info")->table($buildSql2)->alias('t1')
            ->field('t1.*,score.qc_item_id,score.score,item.parentid')
            ->join('join qz_qc_info_item_score score on score.order_id = t1.order_id')
            ->join('join qz_qc_items_score item on item.id = score.qc_item_id')
            ->select();

        return $result;
    }

    /**
     * 获取评分项平均值
     * @param $begin 开始时间
     * @param $end 结束时间
     */
    public function getRecordAvgScore($begin,$end)
    {
        $map = array(
            "time" => array("BETWEEN",array($begin,$end))
        );

        return M("qc_info")->alias('qc')->where($map)
                    ->join("join qz_qc_info_item_score s on s.order_id = qc.order_id")
                    ->join("join qz_qc_items_score i on i.id = s.qc_item_id")
                    ->join("join qz_qc_items_score q on q.id = i.parentid")
                    ->field("q.id, round(avg(s.score),2) as avg_score")
                    ->group("q.id")->order("field(q.id,1,6,11,16,21,26,31,36)")->select();


    }
}