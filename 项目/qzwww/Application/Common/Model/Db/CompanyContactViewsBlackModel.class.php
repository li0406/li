<?php
// +----------------------------------------------------------------------
// | LogCompanyContactViews 装修公司联系方式查看记录表
// +----------------------------------------------------------------------

namespace Common\Model\Db;

use Think\Model;

class CompanyContactViewsBlackModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 添加装修公司联系方式查看黑名单
     *
     * @param array $data 黑名单数据
     * @return mixed
     */
    public function saveBlack($data)
    {
        return $this->add($data);
    }

    /**
     * 添加装修公司联系方式查看记录
     *
     * @param array $data 查看日志数据
     * @return mixed
     */
    public function getOne($where)
    {
        return $this->where($where)->count('id');
    }
}