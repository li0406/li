<?php
namespace Home\Model\Db;
use Think\Model;

class QuanWuRecordModel extends Model
{
    protected $tableName = "quanwu_record";
    /**
     * 根据条件获取全屋定制落地页发单数量
     * @param $map
     * @return mixed
     */
    public function getquanwuorderCount($map)
    {
        return M('quanwu_record')->alias('quanwu')
            ->where($map)
            ->count();
    }


    /**
     * 根据条件获取全屋定制落地页发单列表
     * @param $map      条件
     * @param int $pageIndex
     * @param int $pageCount
     * @return mixed
     */
    public function getquanwuorderList($map,$pageIndex,$pageCount)
    {
        return M('quanwu_record')->alias('quanwu')
            ->where($map)
            ->field('quanwu.id,quanwu.name,quanwu.tel,quanwu.source,quanwu.created_at,quyu.cname,area.qz_area quxian')
            ->join('left join qz_quyu quyu on quyu.cid = quanwu.cs')
            ->join('left join qz_area area on area.qz_areaid = quanwu.qx')
            ->order('quanwu.created_at desc')
            ->limit($pageIndex,$pageCount)
            ->select();

    }


}