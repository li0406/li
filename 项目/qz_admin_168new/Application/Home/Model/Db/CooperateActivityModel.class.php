<?php
namespace Home\Model\Db;
use Think\Model;
class CooperateActivityModel extends Model
{
    protected $tableName = 'cooperate_activity';
    public function addActicity($data){
        return $this->add($data);
    }

    public function editActicity($id,$data){
        $map['id'] = ['eq',$id];
        return $this->where($map)->save($data);
    }

    public function getActivityListCount($data){
        $map = [];
        if(is_numeric($data['cid'])&&$data['cid']!=0){
            $map['_string'] = "find_in_set(".$data['cid'].",t.cids)";
        }

        if($data['cid'] === '0'){
            $map['t.cids'] = ['exp','is null'];
        }
        if(!empty($data['name'])){
            $map[]["_complex"] = array(
                "t.id" => array("EQ", $data['name']),
                "t.name" => array("LIKE", "%" .$data['name'] . "%"),
                "_logic" => "OR"
            );
        }

        if(!empty($data['start'])){
            $map['t.start_at']  = ['egt',strtotime($data['start'])];
        }

        if(!empty($data['end'])){
            $map['t.end_at']  = ['elt',strtotime($data['end'])];
        }

        if(!empty($data['status'])){
            $map['t.state']  = ['eq',$data['status']];
        }

        if(!empty($data['sj_name'])){
            $map['t.business_name']  =  ['like','%'.$data['sj_name'].'%'];
        }

        $buildSql = $this->alias('a')
            ->field('a.id,a.name,a.start_at,a.end_at,a.state,group_concat(ac.cid) as cids,a.business_name')
            ->join('left join qz_cooperate_activity_city ac on ac.aid = a.id')
            ->group('a.id')
            ->buildSql();

        return $this->table($buildSql)->alias('t')->where($map)->count(1);
    }

    public function getActivityList($data,$page,$pageSize){
        $map = [];
        if(is_numeric($data['cid'])&&!empty($data['cid'])){
            $map['_string'] = "find_in_set(".$data['cid'].",t.cids)";
        }
        if($data['cid'] === '0'){
            $map['t.cids'] = ['exp','is null'];
        }

        if(!empty($data['name'])){
            $map[]["_complex"] = array(
                "t.id" => array("EQ", $data['name']),
                "t.name" => array("LIKE", "%" .$data['name'] . "%"),
                "_logic" => "OR"
            );
        }

        if(!empty($data['start'])){
            $map['t.start_at']  = ['egt',strtotime($data['start'])];
        }

        if(!empty($data['end'])){
            $map['t.end_at']  = ['elt',strtotime($data['end'])];
        }

        if(!empty($data['status'])){
            $map['t.state']  = ['eq',$data['status']];
        }

        if(!empty($data['sj_name'])){
            $map['t.business_name']  = ['like','%'.$data['sj_name'].'%'];
        }

        $buildSql = $this->alias('a')
            ->field('a.id,a.name,a.start_at,a.end_at,a.state,group_concat(ac.cid) as cids,a.px,a.business_name,a.created_at')
            ->join('left join qz_cooperate_activity_city ac on ac.aid = a.id')
            ->group('a.id')
            ->buildSql();

        $buildSql = $this->table($buildSql)->alias('t')->where($map)->limit($page,$pageSize)->order('t.created_at desc')->buildSql();

        return $this->table($buildSql)->alias('t')
            ->field('t.*,group_concat(c.cname) as cnames')
            ->join('left join qz_cooperate_activity_city ac on ac.aid = t.id')
            ->join('left join qz_quyu c on c.cid = ac.cid')
            ->group('t.id')
            ->order('t.created_at desc')
            ->select();
    }

    public function getActivityInfo($id){
        $map['a.id'] = ['eq',$id];
        return $this->alias('a')->field('a.*,group_concat(ac.cid) as cids')
            ->join('left join qz_cooperate_activity_city ac on ac.aid = a.id')
            ->where($map)
            ->group('a.id')
            ->select();
    }

    public function deleteActivity($id){
        $map['id'] = ['eq',$id];
        return $this->where($map)->delete();
    }
}