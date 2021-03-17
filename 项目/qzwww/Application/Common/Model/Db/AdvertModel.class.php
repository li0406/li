<?php

namespace Common\Model\Db;

use Think\Model;

class AdvertModel extends Model
{

    protected $autoCheckFields = false;

    /**
     * 获取广告位置详情
     * @param  [type] $position_id [description]
     * @return [type]              [description]
     */
    public function getAdvertPosition($position_id)
    {
        $map = array(
            "id" => array("EQ", $position_id)
        );

        $field = ["id", "name", "position_id", "type", "enabled"];
        return M("advert_position")->where($map)->field($field)->find();
    }

    /**
     * 获取城市广告位下的有效广告
     * @param  [type] $position_id [description]
     * @param  [type] $cs          [description]
     * @return [type]              [description]
     */
    public function getActiveAdvert($position_id, $cs)
    {
        $map = array(
            "a.location_id" => array("EQ", $position_id),
            "c.cs" => array("EQ", $cs),
            "a.status" => array("EQ", 1)
        );

        return M("advert")->alias("a")->where($map)
            ->join("inner join qz_advert_city as c on c.advert_id = a.id")
            ->join("left join qz_advert_img as i on i.advert_id = a.id")
            ->join("left join qz_advert_js as j on j.advert_id = a.id")         //  关联qz_advert_js也是用的left join  因为非js广告是没有js的
            ->order("a.px asc,a.id desc")
            ->field([
                "a.id", "a.name", "a.url as link_url",
                "i.title", "i.url as img_url", "j.js", "j.type"
            ])->select();
    }

    /**
     * 根据qz_advert_position 的id获取广告数据
     * @param $positionId
     * @param $count
     * @return mixed
     */
    public function getAdByPosition($positionId,$count)
    {
        $now = time();
        $map['a.location_id'] = ['eq', $positionId];
        $map['a.start_time'] = ['elt', $now];
        $map['a.end_time'] = ['egt', $now,];
        $map['a.status'] = ['eq', 1];
        $field = 'a.id,a.name,a.url,ai.url as source,ai.title';
        $ret = $this->alias('a')
            ->field($field)
            ->join('qz_advert_position p on p.id = a.location_id')
            ->join('qz_advert_img ai on ai.advert_id=a.id')
            ->where($map)
            ->order('a.px asc,a.created_at desc')
            ->limit($count)
            ->select();
        return $ret;
    }
}