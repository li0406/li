<?php
// +----------------------------------------------------------------------
// | OrderReviewApplyTelModel
// +----------------------------------------------------------------------
namespace Home\Model\Db;

use Think\Model;

class OrderReviewApplyTelModel extends Model
{
    /**
     * 根据条件获取显号申请记录
     * @param  $map array 查询条件
     * @return mixed
     */
    public function getUserApplyByOrderid($map)
    {
        return $this->where($map)->find();
    }

    /**
     * 添加/更新申请记录
     * @param $data
     * @param array $map
     * @return mixed
     */
    public function addOrdersApplyTel($data, $map = [])
    {
        if (empty($map)) {
            return $this->add($data);
        } else {
            return $this->where($map)->save($data);
        }
    }

    /**
     * 获取详细信息列表
     * @param array $map
     * @return mixed
     */
    public function getFullListByMap($map)
    {
        return $this->alias('t')
            ->field('t.id,t.status,z.name AS apply_name,t.apply_reason,t.apply_time,y.name AS passer_name,t.pass_time')
            ->join('left join qz_adminuser z on z.id = t.apply_id')
            ->join('left join qz_adminuser y on y.id = t.passer_id')
            ->where($map)
            ->order('status, id DESC')
            ->select();
    }

}