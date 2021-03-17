<?php

namespace app\common\model\db;

use app\common\model\db\WorksiteMedia;
use think\db\Query;
use think\Model;

class WorkSiteInfo extends Model
{

    protected $autoWriteTimestamp = false;
    protected $table = 'qz_worksite_info';


    public function mediaList(){
        return $this->hasMany(WorksiteMedia::class, "info_id", "id")->order("sort asc");
    }

    public function getNewestWorkSiteInfo($live_ids)
    {
        $map[] = [
            'i.live_id', 'in', $live_ids
        ];
        $buildSql = $this->alias('i')->field('id,live_id,step')->where($map)->order('i.id desc')->buildSql();
        return $this->table($buildSql)->alias('t')
            ->group('t.live_id')
            ->select();
    }


    /**
     * 获取施工信息详情
     * 包括工地直播数据和发布人数据
     * @param $id
     * @return array|string|Model|null
     */
    public function getById($id){
        return $this->alias("a")->where("a.id", $id)
            ->join("worksite_live b", "b.id = a.live_id", "inner")
            ->join("worksite_user u", "u.id = a.user_id", "left")
            ->field([
                "a.*", "u.wx_openid", "u.wx_nickname", "u.wx_headimg",
                "b.code", "b.order_id", "b.order_type", "b.company_id", "b.state", "b.endtime"
            ])->find();
    }

    /**
     * 获取施工阶段
     * @param $live_id
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLiveStep($live_id){
        return $this->where("live_id", $live_id)
            ->field("id,live_id,step")
            ->group("step")
            ->select();
    }

    /**
     * 获取施工信息分页数量
     * @param $live_id
     * @param int $step
     * @return float|string
     */
    public function getPageCount($live_id, $step = 0){
        $map = $this->getPageMap($live_id, $step);

        return $this->alias("a")->where($map)
            ->count("a.id");
    }

    /**
     * 获取施工信息分页数据
     * @param $live_id
     * @param int $step
     * @param int $offset
     * @param int $limit
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPageList($live_id, $step = 0, $offset = 0, $limit = 0){
        $map = $this->getPageMap($live_id, $step);

        return $this->alias("a")->where($map)
            ->with(["mediaList"])
            ->join("worksite_live b", "b.id = a.live_id", "inner")
            ->join("worksite_user u", "a.user_id = u.id", "left")
            ->join("user c", "c.id = b.company_id", "left")
            ->field([
                "a.*", "u.wx_nickname", "u.wx_headimg", "b.company_id",
                "c.logo as company_logo", "c.qc as company_qc", "c.jc as company_jc"
            ])
            ->order("a.created_at desc")
            ->limit($offset, $limit)
            ->select();
    }

    /**
     * 获取施工信息分页查询条件
     * @param $live_id
     * @param int $step
     * @return Query
     */
    public function getPageMap($live_id, $step = 0){
        $map = new Query();
        $map->where("a.live_id", $live_id);

        if (!empty($step)) {
            $map->where("a.step", $step);
        }

        return $map;
    }
}