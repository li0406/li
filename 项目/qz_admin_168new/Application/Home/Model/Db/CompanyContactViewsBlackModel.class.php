<?php
// +----------------------------------------------------------------------
// | LogCompanyContactViews 装修公司联系方式查看记录表
// +----------------------------------------------------------------------

namespace  Home\Model\Db;

use Think\Model;

class CompanyContactViewsBlackModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 添加装修公司联系方式访问黑名单
     *
     * @param array $data 黑名单数据
     * @return mixed
     */
    public function saveBlack($data,$map = [])
    {
        if (empty($map)) {
            return $this->add($data);
        } else {
            return $this->where($map)->save($data);
        }
    }

    /**
     * 删除装修公司联系方式访问黑名单
     *
     * @param array $data 黑名单数据
     * @return mixed
     */
    public function removeBlack($map)
    {
        return $this->where($map)->delete();
    }

    /**
     * 获取单个装修公司联系方式访问黑名单
     *
     * @param array $data 查看日志数据
     * @return mixed
     */
    public function getOne($where)
    {
        return $this->where($where)->find();
    }

    /**
     * 获取装修公司联系方式访问黑名单数量
     *
     * @param array $data 查看日志数据
     * @return mixed
     */
    public function getCount($where)
    {
        return $this->alias('a')->where($where)->count(1);
    }

    /**
     * 获取装修公司联系方式访问黑名单列表
     *
     * @param $where
     * @param int $page
     * @param int $pageSize
     */
    public function getList($where, $page = 1, $pageSize = 10)
    {
        return $this
            ->alias('a')
            ->join('left join qz_log_company_contact_views b on b.tel = a.tel')
            ->field('a.*,count(b.tel) as num')
            ->group('a.tel,a.ip')
            ->where($where)
            ->page($page,$pageSize)
            ->order('a.create_time desc,a.id desc')
            ->select();
    }

    /**
     * 获取装修公司联系方式访问黑名单所有列表
     *
     * @param $where
     */
    public function getListNoPage($where)
    {
        return $this
            ->alias('a')
            ->field('a.tel,a.ip,a.create_time,count(b.tel) as num,a.mark,a.mark_img')
            ->join('left join qz_log_company_contact_views b on b.tel = a.tel')
            ->group('a.tel,a.ip')
            ->where($where)
            ->order('a.create_time desc,a.id desc')
            ->select();
    }
}