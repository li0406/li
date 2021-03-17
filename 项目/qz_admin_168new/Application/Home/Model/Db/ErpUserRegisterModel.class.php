<?php
// +----------------------------------------------------------------------
// |  ERP营销用户注册表
// +----------------------------------------------------------------------
namespace Home\Model\Db;

use Think\Model;

class ErpUserRegisterModel extends Model
{
    protected $tableName = 'erp_user_register';

    /**
     * 根据条件查询注册数量
     * @param array $where
     * @return mixed
     */
    public function getRegisterListCount($where = [])
    {
        return $this
            ->alias('a')
            ->join('left join qz_quyu q on q.cid = a.city_id')
            ->join('left join qz_province p on p.qz_provinceid = a.province_id')
            ->where($where)
            ->count();
    }

    /**
     * 根据条件查询列表数据
     * @param array $where
     * @param int $pageIndex
     * @param int $pageCount
     * @return mixed
     */
    public function getRegisterList($where = [], $pageIndex = 1, $pageCount = 10)
    {
        return $this->alias('a')
            ->field('a.*,q.cname,p.qz_province')
            ->join('left join qz_quyu q on q.cid = a.city_id')
            ->join('left join qz_province p on p.qz_provinceid = a.province_id')
            ->where($where)
            ->page($pageIndex,$pageCount)
            ->order('time desc')
            ->select();
    }
}