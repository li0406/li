<?php
// +----------------------------------------------------------------------
// | OrderSourceModel
// +----------------------------------------------------------------------
namespace Home\Model\Db;


use Think\Model;

class OrderSourceModel extends Model
{
    protected $trueTableName = 'qz_order_source';

    public function getSourceList($map, $offset = null, $length = null, $filed = 'a.src,a.name,a.alias,dept.name dept_name,dept.dept_belong,sg.name group_name') {
        return M('order_source')->alias('a')
            ->join('qz_department_identify dept on dept.id = a.dept')
            ->join('qz_order_source_group sg on sg.id = a.groupid')
            ->join('')
            ->where($map)
            ->field($filed)
            ->limit($offset, $length)
            ->select();
    }

    // 根据渠道标识获取发单量
    public function getOrderSourceStat($sourceIds, $begin = "", $end = ""){
        $map = array(
            "a.id" => array("IN", $sourceIds),
            "g.category" => array(
                array("EQ", 1),
                array("EXP", "IS NULL"),
                "OR"
            )
        );

        if (!empty($begin) && !empty($end)) {
            $map["o.time_real"] = array("BETWEEN", [
                strtotime($begin),
                strtotime($end) + 86399
            ]);
        }

        $subSql = $this->alias("a")->where($map)
            ->join("inner join qz_orders_source as b on b.source_src_id = a.id")
            ->join("inner join qz_orders as o on o.id = b.orderid")
            ->join("left join qz_order_source_group AS g on g.id = a.groupid")
            ->field(["a.id as sourceid", "o.id as order_id", "o.type_fw"])
            ->buildSql();

        // 订单总量、分单量、赠单量
        $list = M()->table($subSql)->alias("t2")
            ->field([
                "t2.*",
                "count(t2.sourceid) AS order_count",
                "count(IF(t2.type_fw = 1, 1, null)) AS order_fen_count",
                "count(IF(t2.type_fw = 2, 1, null)) AS order_zen_count"
            ])
            ->group("t2.sourceid")
            ->select();

        return $list;
    }

    // 根据渠道标识获取实际分单量
    public function getOrderSourceRealStat($sourceIds, $begin = "", $end = ""){
        $map = array(
            "a.id" => array("IN", $sourceIds),
            "o.`on`" => array("EQ", 4),
            "g.category" => array(
                array("EQ", 1),
                array("EXP", "IS NULL"),
                "OR"
            ),
            "o.is_settlement"=>array("EQ",1)
        );

        if (!empty($begin) && !empty($end)) {
            $map["new.lasttime"] = array("BETWEEN", [
                strtotime($begin),
                strtotime($end) + 86399
            ]);
        }

        $subSql = $this->alias("a")->where($map)
            ->join("inner join qz_orders_source as b on b.source_src_id = a.id")
            ->join("inner join qz_orders as o on o.id = b.orderid")
            ->join("inner join qz_order_csos_new as new on o.id = new.order_id")
            ->join("left join qz_order_source_group AS g on g.id = a.groupid")
            ->field(["a.id as sourceid", "o.id as order_id", "o.type_fw"])
            ->buildSql();

        // 实际分单量
        $list = M()->table($subSql)->alias("t2")
            ->field([
                "t2.*",
                "count(t2.sourceid) AS order_count",
                "count(IF(t2.type_fw = 1, 1, null)) AS order_real_fen"
            ])
            ->group("t2.sourceid")
            ->select();

        return $list;
    }

    // 根据渠道标识获取发单量
    public function getOrderSourceLfStat($sourceIds, $begin = "", $end = ""){
        $map = array(
            "a.id" => array("IN", $sourceIds),
            "g.category" => array(
                array("EQ", 1),
                array("EXP", "IS NULL"),
                "OR"
            )
        );

        if (!empty($begin) && !empty($end)) {
            $map["o.time_real"] = array("BETWEEN", [
                strtotime($begin),
                strtotime($end) + 86399
            ]);
        }

        $subSql = $this->alias("a")->where($map)
            ->join("inner join qz_orders_source as b on b.source_src_id = a.id")
            ->join("inner join qz_orders as o on o.id = b.orderid")
            ->join("left join qz_order_source_group AS g on g.id = a.groupid")
            ->field(["a.id as sourceid", "o.id as order_id", "o.type_fw"])
            ->buildSql();

        // 订单量房状态
        $subSql2 = M()->table($subSql)->alias("t1")
            ->join("left join qz_order_company_review as r on r.orderid = t1.order_id")
            ->field(["t1.*", "count(IF (r.`status` IN(1,2,4), 1, null)) as lf_count"])
            ->group("t1.order_id")
            ->buildSql();

        // 订单量房量
        $list = M()->table($subSql2)->alias("t2")
            ->field([
                "t2.*",
                "count(IF(t2.lf_count >= 1, 1, null)) AS order_lf_count"
            ])
            ->group("t2.sourceid")
            ->select();

        return $list;
    }
}