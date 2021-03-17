<?php

namespace Common\Model\Db;

use Think\Model;

class CardModel extends Model {


    // 获取装修公司的一个卡券
    public function getCompanyFirstCard($companyIds){
        $time = time();

        $map = array(
            "b.com_id" => array("IN", $companyIds),
            "a.`enable`" => array("EQ", 1),
            "c.`check`" => array("EQ", 2),
            "c.activity_start" => array("ELT", $time),
            "c.activity_end" => array("EGT", $time),
            "c.`start`" => array("ELT", $time),
            "c.`end`" => array("EGT", $time),
        );

        $sql = M("card")->alias("a")->where($map)
            ->join("inner join qz_card_com as b on b.card_id = a.id")
            ->join("inner join qz_card_com_record as c on c.card_com_id = b.id")
            ->join("left join qz_card_user as d on d.record_id = c.id")
            ->field([
                "a.id", "a.`name`", "b.com_id", "c.amount",
                "count(d.id) as send_num",
            ])
            ->group("c.id")
            ->order("a.add_time desc")
            ->having("c.amount > send_num")
            ->buildSql();

        return M()->table($sql)->alias("t")
            ->group("t.com_id")
            ->select();
    }

}