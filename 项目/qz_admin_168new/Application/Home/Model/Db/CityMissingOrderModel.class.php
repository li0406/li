<?php
namespace Home\Model\Db;
use Think\Model;
class CityMissingOrderModel extends Model
{
    public function getCityList($date,$city_id,$city_level,$whole_month,$is_end,$is_new)
    {
        $map = array(
            "date" => array("EQ",$date)
        );

        if (!empty($city_id)) {
            $map["city_id"] = array("EQ",$city_id);
        }

        if (!empty($city_level)) {
            $map["city_level"] = array("EQ",$city_level);
        }

        if (!empty($whole_month)) {
            $map["whole_month"] = array("EQ",$whole_month);
        }

        if (!empty($is_end)) {
            $map["isend"] = array("EQ",1);
        }

        if (!empty($is_new)) {
            $map["isnew"] = array("EQ",1);
        }

        return M("city_missing_order")->where($map)->order("city_id")->select();
    }

    public function getCityBasicList($begin,$end,$city_id,$level,$whole = "")
    {
        $map = array(
            "date" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );

        if (!empty($city_id)) {
            $map["city_id"] = array("EQ",$city_id);
        }

        if (!empty($level)) {
            $map["city_level"] = array("EQ",$level);
        }

        if (!empty($whole)) {
            $map["whole_month"] = array("EQ",$whole);
        }

        $buildSql = M("city_missing_order")->where($map)
                                      ->field("city_id,city_name,whole_month,order_count,vip_count,city_level")
                                      ->order("city_id,date desc")->buildSql();
        return  M("city_missing_order")->table($buildSql)->alias("t")
                                       ->field("t.*")->group("t.city_id")->select();
    }

    /**
     * 获取城市缺单天数
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getCityMissOrderDayList($begin,$end)
    {
        $map = array(
            "a.date" => array(
                array("EGT",$begin),
                array("ELT",$end)
            ),
            "a.warnning" => array("EQ",1)
        );

        return M("city_missing_order")->where($map)->alias("a")
                               ->field("city_id,date")
                               ->order("city_id,date")
                               ->select();
    }

    public function getMissCityList($begin,$end)
    {
        $map = array(
            "a.date" => array(
                array("EGT",$begin),
                array("ELT",$end)
            )
        );
        return M("city_missing_order")->where($map)->alias("a")
                               ->join("join qz_quyu q on q.cid = a.city_id")
                               ->field("city_id,q.cname,q.px_abc")
                               ->group("a.city_id")
                               ->order("px_abc")
                               ->select();
    }
}
