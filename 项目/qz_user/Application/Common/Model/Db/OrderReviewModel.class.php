<?php
/**
 * 装修公司礼券表
 */

namespace Common\Model\Db;

use Think\Model;

class OrderReviewModel extends Model
{
    protected $tableName = 'order_review';

    /**
     * 获取一条记录
     *
     * @param $data
     * @return mixed
     */
    public function getOne($map)
    {
        return $this->where($map)->find();
    }

    /**
     * 新增一条记录
     *
     * @param $data
     * @return mixed
     */
    public function addOne($data)
    {
        return $this->add($data);
    }

    /**
     * 更新一条记录
     *
     * @param $data
     * @return mixed
     */
    public function upOne($map, $data)
    {
        return $this->where($map)->save($data);
    }
}