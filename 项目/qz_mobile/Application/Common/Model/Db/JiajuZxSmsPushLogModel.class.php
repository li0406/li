<?php
// +----------------------------------------------------------------------
// | JiajuZxSmsPushLog
// +----------------------------------------------------------------------

namespace Common\Model\Db;

use Think\Model;

class JiajuZxSmsPushLogModel extends Model
{
    protected $trueTableName = 'qz_jiaju_zx_sms_push_log';

    /**
     * 添加新的日志信息
     * @param array $data 新增数据
     * @return mixed
     */
    public function addLog($data)
    {
        return $this->add($data);
    }

    /**
     * 修改日志信息
     * @param array $data 修改数据
     * @param array $map 修改条件
     * @return bool
     */
    public function editLog($data,$map = [])
    {
        return $this->where($map)->save($data);
    }

    /**
     * 查询单个日志信息
     * @param array $map 查询条件
     */
    public function getOne($map)
    {
        return $this->where($map)->find();
    }
}