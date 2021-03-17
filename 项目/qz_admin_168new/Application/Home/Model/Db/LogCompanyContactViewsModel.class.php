<?php
// +----------------------------------------------------------------------
// | LogCompanyContactViews 装修公司联系方式查看记录表
// +----------------------------------------------------------------------

namespace  Home\Model\Db;

use Think\Model;

class LogCompanyContactViewsModel extends Model
{
    protected $autoCheckFields = false;

    /**
     * 获取装修公司联系方式查看短信数量
     *
     * @param array $data 查看日志数据
     * @return mixed
     */
    public function getCount($where)
    {
        $table = $this->alias('a')
            ->field('a.*')
            ->where($where)
            ->join('left join qz_company_contact_views_black b on b.tel = a.tel AND b.ip = a.ip')
            ->group('a.tel,a.ip')
            ->buildSql();
        return $this->table($table)->alias('t')->count(1);
    }

    /**
     * 获取装修公司联系方式查看短信列表
     *
     * @param $where
     * @param int $page
     * @param int $pageSize
     */
    public function getList($where, $page = 1, $pageSize = 10)
    {
        return $this->alias('a')
            ->where($where)
            ->field('a.tel,a.ip,a.create_time,count(a.tel) as num')
            ->join('left join qz_company_contact_views_black b on b.tel = a.tel AND b.ip = a.ip')
            ->group('tel,ip')
            ->page($page,$pageSize)
            ->order('a.create_time desc,a.id desc')
            ->select();
    }

    /**
     * 获取装修公司联系方式查看短信所有列表
     *
     * @param $where
     */
    public function getListNoPage($where)
    {
        return $this->alias('a')
            ->where($where)
            ->field('a.tel,a.ip,a.create_time,count(a.tel) as num')
            ->join('left join qz_company_contact_views_black b on b.tel = a.tel AND b.ip = a.ip')
            ->group('tel,ip')
            ->order('a.create_time desc,a.id desc')
            ->select();
    }
}