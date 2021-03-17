<?php
// +----------------------------------------------------------------------
// | Area
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\db\Where;
use think\Model;

class Area extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_area';

    /**
     * 获取列表
     *
     * @param array $map 查询条件
     * @param array $with 关联信息
     * @param null $page 分页
     * @param null $pageSize 分页
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getList($map, $page = null, $pageSize = null, $field = 'qz_areaid as area_id,qz_area as area')
    {
        if ($page == 0) {
            $offset = 0;
        } else {
            $offset = $pageSize * ($page - 1);
        }
        return self::field($field)->where(new Where($map))->limit($offset, $pageSize)->order('orders asc')->select();
    }

    /**
     * 根据名称获取
     *
     * @param array $map 查询条件
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getInfoByName($map)
    {
        return $this->where($map)->find();
    }

    /**
     * 获取相邻城市区域
     * @param  [type] $company_id [description]
     * @param  [type] $citys      [description]
     * @return [type]             [description]
     */
    public function getDeliverArea($company_id, $citys){
        return static::alias("a")
            ->where("a.fatherid", "in", $citys)
            ->join("user_company_deliver_area d", "d.area_id = a.qz_areaid and d.company_id = $company_id", "left")
            ->field("a.qz_areaid as area_id,a.qz_area as area,a.fatherid,IF(d.company_id is not null, 1, 0) as checked")
            ->order("a.orders asc")
            ->select();
    }


    // 获取所有区县
    public function getAllArea()
    {
        return $this->order('orders')->field('qz_areaid, qz_area, fatherid')->select();
    }

}