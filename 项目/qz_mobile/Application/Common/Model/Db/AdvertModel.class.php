<?php

namespace Common\Model\Db;

use Think\Model;

class AdvertModel extends Model{

    protected $autoCheckFields = false;

    /**
     * 获取广告位置详情
     * @param  [type] $position_id [description]
     * @return [type]              [description]
     */
    public function getAdvertPosition($position_id){
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
    public function getActiveAdvert($position_id, $cs){
        $map = array(
            "a.location_id" => array("EQ", $position_id),
            "c.cs" => array("EQ", $cs),
            "a.status" => array("EQ", 1)
        );

        return M("advert")->alias("a")->where($map)
            ->join("inner join qz_advert_city as c on c.advert_id = a.id")
            ->join("left join qz_advert_img as i on i.advert_id = a.id")        // 这里改成了left join 因为js广告是没有图片的，
            ->join("left join qz_advert_js as j on j.advert_id = a.id")         //  关联qz_advert_js也是用的left join  因为非js广告是没有js的
            ->order("a.px asc,a.id desc")
            ->field([
                "a.id", "a.name", "a.url as link_url",
                "i.title", "i.url as img_url", "j.js", "j.type"
            ])->select();
    }


    /**
     *获取单图广告信息
     * @param $location_id
     * @return mixed
     */
     public function getSingleImgAdvInfo($location_id, $city = "")
     {
         $map = [
             "a.status" => array("EQ", 1),
             "a.location_id" => array("EQ", $location_id),
             "a.start_time" => array("ELT", strtotime(date("Y-m-d", time()))),
             "a.end_time" => array("EGT", strtotime(date("Y-m-d", time()) . "23:59:59")),
         ];

         if ($city) {
             $map["ac.cs"] = array("eq", $city);
         }

         return M("advert")->alias("a")
             ->where($map)
             ->field("a.id,a.`name` as title,a.url jump_url,ai.url img_url")
             ->join("INNER JOIN qz_advert_img as ai on ai.advert_id = a.id")
             ->join("INNER JOIN qz_advert_city ac on ac.advert_id = a.id")
             ->order("px")
             ->find();
     }

}