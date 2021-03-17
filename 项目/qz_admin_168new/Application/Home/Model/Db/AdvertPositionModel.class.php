<?php

namespace Home\Model\Db;
use Think\Model;

class AdvertPositionModel extends Model {

    /**
     * 验证名称唯一性
     * @param  [type]  $name [description]
     * @param  integer $id   [description]
     * @return [type]        [description]
     */
    public function validateByName($name, $id = 0){
        $map = array(
            "name" => array("EQ", $name),
            "id" => array("NEQ", $id)
        );

        return M("advert_position")->where($map)->field("id")->find();
    }

    /**
     * 验证广告位位置唯一性
     * @param  [type]  $position_id [description]
     * @param  integer $id          [description]
     * @return [type]               [description]
     */
    public function validateByPositionId($position_id, $id = 0){
        $map = array(
            "position_id" => array("EQ", $position_id),
            "id" => array("NEQ", $id)
        );

        return M("advert_position")->where($map)->field("id")->count();
    }


    /**
     * 获取广告位置下广告位数量
     * @param  [type] $position_id [description]
     * @return [type]              [description]
     */
    public function getNumByPositionId($position_id){
        $map = array(
            "position_id" => array("EQ", $position_id)
        );

        return M("advert_position")->where($map)->count("id");
    }

    /**
     * 获取广告位列表
     * @param  [type] $param [description]
     * @return [type]        [description]
     */
    public function getList($param){
        $map = array();

        // 广告位名称/id
        if (!empty($param["keywords"])) {
            $where[] = sprintf("position('%s' IN p.`name`)", $param["keywords"]);
            $where["p.id"] = array("EQ", $param["keywords"]);
            $where["_logic"] = "or";
            $map["_complex"] = $where;
        }

        // 平台
        if (!empty($param["platform_id"])) {
            $map["plat.id"] = array("EQ", $param["platform_id"]);
        }

        // 模块
        if (!empty($param["module_id"])) {
            $map["module.id"] = array("EQ", $param["module_id"]);
        }

        // 页面
        if (!empty($param["page_id"])) {
            $map["page.id"] = array("EQ", $param["page_id"]);
        }

        // 位置
        if (!empty($param["position_id"])) {
            $map["pos.id"] = array("EQ", $param["position_id"]);
        }

        // 状态
        if (!empty($param["enabled"])) {
            $map["p.enabled"] = array("EQ", $param["enabled"]);
        }

        return M("advert_position")->alias("p")->where($map)
            ->join("left join qz_advert_position_option as pos on pos.id = p.position_id and pos.`level` = 4")
            ->join("left join qz_advert_position_option as page on page.id = pos.parentid and page.`level` = 3")
            ->join("left join qz_advert_position_option as module on module.id = page.parentid and module.`level` = 2")
            ->join("left join qz_advert_position_option as plat on plat.id = module.parentid and plat.`level` = 1")
            ->field([
                "p.id", "p.name", "p.type", "p.enabled","p.demand",
                "plat.id as platform_id", "plat.`name` as platform_name",
                "module.id as module_id", "module.`name` as module_name",
                "page.id as page_id", "page.`name` as page_name",
                "pos.id as position_id", "pos.`name` as position_name"
            ])
            ->order("p.id asc")
            ->select();
    }

    /**
     * 获取广告位详情
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getDetail($id){
        $map = array(
            "p.id" => array("EQ", $id)
        );

        return M("advert_position")->alias("p")->where($map)
            ->join("left join qz_advert_position_option as pos on pos.id = p.position_id and pos.`level` = 4")
            ->join("left join qz_advert_position_option as page on page.id = pos.parentid and page.`level` = 3")
            ->join("left join qz_advert_position_option as module on module.id = page.parentid and module.`level` = 2")
            ->join("left join qz_advert_position_option as plat on plat.id = module.parentid and plat.`level` = 1")
            ->field([
                "p.*", "pos.`name` as position_name",
                "plat.id as platform_id", "plat.`name` as platform_name",
                "module.id as module_id", "module.`name` as module_name",
                "page.id as page_id", "page.`name` as page_name",
            ])->find();
    }

    public function getValidPositionList($map, $field = '*')
    {
        return M("advert_position")->alias("p")
            ->field($field)
            ->where($map)
            ->select();
    }

    public function checkAdvPosition($map,$field = 'p.*'){
        return M("advert_position")->alias("p")
            ->field($field)
            ->join('qz_advert a on a.location_id = p.id')
            ->join('qz_advert_city cs on cs.advert_id = a.id')
            ->where($map)
            ->select();
    }
}