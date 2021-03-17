<?php
// +----------------------------------------------------------------------
// | NewMediaTargetModel
// +----------------------------------------------------------------------
namespace Home\Model\Db;

use Think\Model;

class NewMediaTargetModel extends Model
{
    protected $trueTableName = 'qz_new_media_target';

    public function getTargetCount($where)
    {
        return $this->alias('t')
            ->join('qz_new_media_group g on g.id = t.group_id')
            ->where($where)
            ->count();
    }

    public function getTargetList($where,$page,$page_count)
    {
        return $this->alias('t')
            ->field("t.id,t.target_num,t.op_name,FROM_UNIXTIME(t.add_time,'%Y/%m/%d %H:%i:%s') add_time,FROM_UNIXTIME(t.`month`,'%m') month,g.`name` group_name,u.`user` team_name")
            ->join('qz_new_media_group g on g.id = t.group_id')
            ->join('qz_adminuser u on u.id = g.team_admin_id')
            ->where($where)
            ->limit($page,$page_count)
            ->order('t.id desc')
            ->select();
    }

    public function getTargetInfo($where){
        return $this->alias('t')
            ->field("t.id,t.target_num,FROM_UNIXTIME(t.`month`,'%Y-%m') month,t.`month` month_int,t.group_id,g.team_admin_id,g.name group_name")
            ->join('qz_new_media_group g on g.id = t.group_id')
            ->where($where)
            ->find();
    }

    public function addTargetInfo($data = '')
    {
        return $this->add($data);
    }

    public function saveTargetInfo($data = '', $id)
    {
        return $this->where(['id' => ['eq', $id]])->save($data);
    }

    public function delTarget($condition)
    {
        if(is_array($condition)){
            $where = $condition;
        }else{
            $where = ['id' => ['eq', $condition]];
        }
        return $this->where($where)->delete();
    }

    public function delTargetByMap($map)
    {
        return M('new_media_target')->where($map)->delete();
    }
}