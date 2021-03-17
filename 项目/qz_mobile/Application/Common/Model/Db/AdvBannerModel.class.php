<?php


namespace Common\Model\Db;


use Think\Model;

class AdvBannerModel extends Model
{
    protected $autoCheckFields = false;
    protected $tableName = "adv_banner";


    public function getHomeCases($cityId = "", $limit = 5)
    {
        $todaystart = time();
        $todayend = strtotime(date("Y-m-d", time()) . " 23:59:59");
        $map = [
            "start_time" => array("elt", $todaystart),
            "end_time" => array("egt", $todayend),
            "module" => array("like", "%home_cases%"),
            "status" => array("eq", 1)
        ];

        if (!empty($cityId)) {
            $map["city_id"] = array("eq", $cityId);
        }

        $buildSql = $this->where($map)
            ->field("title,img_url,img_url_mobile,url,url_mobile,company_name,sort,op_time")
            ->order("sort,op_time desc")
            ->buildSql();

        return $this->table($buildSql)->alias("t")
            ->field("t.*")
            ->group("t.sort")
            ->order("sort,op_time desc")
            ->limit($limit)
            ->select();

    }

}