<?php

namespace Home\Model\Db;
use Think\Model;

class CityGeneralizationCoefficientModel extends Model
{
    public function addAllInfo($data)
    {
        return M("city_generalization_coefficient")->addAll($data);
    }

    public function getCityGeneralization($date)
    {
        $map = array(
            "date" => array("EQ",$date),
        );
        return M("city_generalization_coefficient")->where($map)->field("city_id,coefficient,before_coefficient,now_coefficient")->select();
    }

    /**
     * 获取当月每个城市每天是否计算分单量
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    public function getCityGeneralizationList($begin,$end,$city_id)
    {
        $map = array(
            "date" => array(
                array("EGT",$begin),
                array("ELT",$end),
            )
        );

        if (!empty($city_id)) {
            $map["city_id"] = array("EQ",$city_id);
        }

        $buildSql = M("city_generalization_coefficient")->where($map)
                    ->field("city_id,coefficient,date,DATE_SUB(date,INTERVAL 1 day) as sub_one,DATE_SUB(date,INTERVAL 2 day) as sub_two,DATE_SUB(date,INTERVAL 3 day) as sub_three")->buildSql();
        $buildSql = M("city_generalization_coefficient")->table($buildSql)->alias("t")
                   ->join("left join qz_city_generalization_coefficient b on b.city_id = t.city_id and b.date = t.sub_one")
                   ->join("left join qz_city_generalization_coefficient c on c.city_id = t.city_id and c.date = t.sub_two")
                   ->join("left join qz_city_generalization_coefficient d on d.city_id = t.city_id and d.date = t.sub_three")
                   ->field("t.city_id,t.date,t.coefficient,if(isnull(b.coefficient),0,b.coefficient) as one_coefficient,if(isnull(c.coefficient),0,c.coefficient) as two_coefficient,if(isnull(d.coefficient),0,d.coefficient) as three_coefficient")->buildSql();
        return  M("city_generalization_coefficient")->table($buildSql)->alias("t")
                ->join("left join qz_city_missing_order m on m.city_id = t.city_id and m.date = t.date")
                ->field("t.city_id,t.date,t.coefficient,if((t.one_coefficient + two_coefficient + three_coefficient) > 0,1,0 )as mark,
                        if(isnull(m.warnning),3,m.warnning) as warnning")->order("t.city_id, t.date")
                ->select();
    }

    public function getCityCoefficientStatistics($date)
    {
        $map = array(
            "a.date" => array("EQ",$date)
        );

        $buildSql = M("city_generalization_coefficient")->where($map)->alias("a")
                    ->join("join qz_quyu q on q.cid = a.city_id")
                    ->field("city_id,coefficient,date,DATE_SUB(date,INTERVAL 1 day) as sub_one,q.cname,q.little")->buildSql();
        return M("city_generalization_coefficient")->table($buildSql)->alias("t")
                   ->join("left join qz_city_generalization_coefficient b on b.city_id = t.city_id and b.date = t.sub_one")
                   ->field("t.city_id,t.coefficient,if(isnull(b.coefficient),0,b.coefficient) as one_coefficient,t.cname,t.little")->select();
    }

    public function getCityCoefficientDetailsStatistics($begin,$end,$city_id,$level)
    {
        $map = array(
            "a.date" => array(
                array("EGT",$begin),
                array("ELT",$end),
            )
        );

        if (!empty($city_id)) {
            $map["a.city_id"] = array("EQ",$city_id);
        }

        if (!empty($level)) {
            $map["q.little"] = array("EQ",$level);
        }

        return M("city_generalization_coefficient")->where($map)->alias("a")
                                            ->join("join qz_quyu q on q.cid = a.city_id")
                                            ->field("a.city_name,a.date,little,coefficient,a.city_id")
                                            ->order("a.city_id,a.date")
                                            ->select();

    }

    public function getCoefficientDetailsCityList($begin,$end)
    {
        $map = array(
            "a.date" => array(
                array("EGT",$begin),
                array("ELT",$end),
            )
        );
        return M("city_generalization_coefficient")->where($map)->alias("a")
                                            ->join("join qz_quyu q on q.cid = a.city_id")
                                            ->field("a.city_name,a.city_id")
                                            ->group("a.city_id")
                                            ->order("q.px_abc,a.city_id")
                                            ->select();

    }
}
