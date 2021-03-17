<?php

namespace Home\Model;
Use Think\Model;

/**
*
*/
class CityCoefficientModel extends Model
{
    protected $autoCheckFields = false;

    public function addCity($data)
    {
        return M("city_coefficient")->addAll($data);
    }

    public function delCity($date)
    {
        $map = array(
            "date" => array("EQ",$date)
        );
        return M("city_coefficient")->where($map)->delete();
    }

    /**
     * 获取城市分单系数
     * @param  [type] $year [年份]
     * @param  [type] $cs   [城市]
     * @return [type]       [description]
     */
    public function getCoefficientList($year,$cs)
    {
        if (count($cs) > 0) {
            $cs = implode(",", $cs);
            $where = " and city_id in (".$cs.")";
        }

        $sql = 'select
                city_id,city_name,
                max(one) as "'.$year.'-01",
                max(two) as "'.$year.'-02",
                max(three) as "'.$year.'-03",
                max(four) as "'.$year.'-04",
                max(five) as "'.$year.'-05",
                max(six) as "'.$year.'-06",
                max(seven) as "'.$year.'-07",
                max(eight) as "'.$year.'-08",
                max(nine) as "'.$year.'-09",
                max(ten) as "'.$year.'-10",
                max(eleven) as "'.$year.'-11",
                max(twelve) as "'.$year.'-12"
                from (
                        select city_id,city_name,
                        if(date = "'.$year.'-01",day,"-") as one,
                        if(date = "'.$year.'-02",day,"-") as two,
                        if(date = "'.$year.'-03",day,"-") as three,
                        if(date = "'.$year.'-04",day,"-") as four,
                        if(date = "'.$year.'-05",day,"-") as five,
                        if(date = "'.$year.'-06",day,"-") as six,
                        if(date = "'.$year.'-07",day,"-") as seven,
                        if(date = "'.$year.'-08",day,"-") as eight,
                        if(date = "'.$year.'-09",day,"-") as nine,
                        if(date = "'.$year.'-10",day,"-") as ten,
                        if(date = "'.$year.'-11",day,"-") as eleven,
                        if(date = "'.$year.'-12",day,"-") as twelve
                        from qz_city_coefficient where date >= "'.$year.'-01" and date <= "'.$year.'-12"
                        '.$where.'
                ) t group BY city_id ';

        return M()->query($sql);

    }
}