<?php
// +----------------------------------------------------------------------
// | LogCompanyContactViews 装修公司联系方式查看记录表
// +----------------------------------------------------------------------

namespace Common\Model\Db;

use Think\Model;

class LogCompanyContactViewsModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 添加装修公司联系方式查看记录
     *
     * @param array $data 查看日志数据
     * @return mixed
     */
    public function saveLog($data)
    {
        return $this->add($data);
    }

    /**
     * 添加装修公司联系方式查看记录
     *
     * @param array $data 查看日志数据
     * @return mixed
     */
    public function getCount($where)
    {
        return $this->where($where)->count('id');
    }
}