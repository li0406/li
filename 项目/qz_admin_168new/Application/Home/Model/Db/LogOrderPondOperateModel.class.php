<?php
// +----------------------------------------------------------------------
// | OrderPondModel  订单池模型
// +----------------------------------------------------------------------

namespace Home\Model\Db;

use Think\Model;

class LogOrderPondOperateModel extends Model
{
    protected $autoCheckFields = false;
    protected $table = 'qz_log_order_pond_operate';
    /**
     * 新增编辑操作
     *
     * @param $data
     * @param array $map
     */
    public function addLog($data,$map = [])
    {
        if (empty($map)){
            //新增操作
            $result = $this->add($data);
        } else {
            //修改操作
            $result = $this->where($map)->save($data);
        }
        return $result;
    }

    /**
     * 增加多条日志
     *
     * @param $data
     * @param array $map
     */
    public function addMuchLog($data)
    {
        return $this->addAll($data);
    }
}