<?php
/**
 * @des:订单撤销备注表模型
 * @author:tengyu
 * @date:2020-11-21
 */

namespace app\index\model\adb;


use app\common\model\adb\BaseModel;
use think\Db;
use think\db\Query;

class OrderBackRecord extends BaseModel
{
    /**
     * @des:撤销时间维度获取撤销订单
     * @param array $param
     * @return mixed
     */
    public function getOrderBackCountByCreated($param=[])
    {
        $map = new Query();
        $map->where('b.allot_state', '=', 1);
        if (isset($param['start_time']) && isset($param['end_time'])) {
            $map->where('c.lasttime', 'between', [$param['start_time'], $param['end_time']]);
        }
        if (isset($param['cs']) && is_array($param['cs']) && !empty($param['cs'])) {
            $map->where('o.cs', 'in', $param['cs']);
        }
        return $this->link()->table('qz_order_back_record')->alias('b')->where($map)
            ->join("qz_orders o", "o.id = b.order_id", "inner")
            ->join("qz_order_csos_new c", "c.order_id = o.id", "inner")
            ->count("distinct o.id");
    }

    /**
     * @des:撤回时间维度，获取撤回订单信息
     * @param array $param
     * @return mixed
     */
    public function getOrderRebutDetail($param=[])
    {
        $map = new Query();
        $map->where('r.allot_state','=',1);
        if (isset($param['start_time']) && isset($param['end_time'])) {
            $map->where('c.lasttime', 'between', [$param['start_time'], $param['end_time']]);
        }
        if (isset($param['cs']) && is_array($param['cs']) && !empty($param['cs'])) {
            $map->where('o.cs', 'in', $param['cs']);
        }
        $pageSize = $param['page_size'] ?? 20;
        $page['page'] = $param['page'] ?? 1;

        return $this->link()->table('qz_order_back_record')->alias('r')->where($map)
            ->join('qz_orders o', 'o.id=r.order_id','inner')
            ->join("qz_order_csos_new c", "c.order_id = o.id", "inner")
            ->join('qz_quyu q', 'q.cid=o.cs', 'left')
            ->join('qz_adminuser u', 'u.id=r.push_uid', 'left')
            ->field(['distinct(r.id)', 'r.order_id', 'u.user', 'q.cname', 'r.created_at', 'c.lasttime','r.back_reason'])
            ->order(['r.created_at' => 'desc'])
            ->paginate($pageSize, false, $page);
    }

    // 城市订单撤回次数
    public function getCityBackCount($date_begin, $date_end, $city_ids = []){
        $map = new Query();
        $map->where("b.allot_state", 1);
        $map->where("c.lasttime", ">=", strtotime($date_begin));
        $map->where("c.lasttime", "<=", strtotime($date_end) + 86399);

        // 城市查询
        if (!empty($city_ids)) {
            $map->where("o.cs", "in", $city_ids);
        }

        $list = $this->link()->name("order_back_record")->alias("b")->where($map)
            ->join("orders o", "o.id = b.order_id", "inner")
            ->join("order_csos_new c", "c.order_id = o.id", "inner")
            ->field([
                "o.cs as city_id",
                "count(DISTINCT o.id) as back_num",
            ])
            ->group("o.cs")
            ->select();

        return $list;
    }

    /**
     * @des:获取订单状态撤回次数
     * @param array $param
     * @return mixed
     */
    public function getBackStatusCount($param = [])
    {
        $map = new Query();
        $map->where('o.on', '=', 99);
        if (isset($param['start_time']) && isset($param['end_time'])) {
            $map->where('c.lasttime', 'between', [$param['start_time'], $param['end_time']]);
        }
        if (isset($param['cs']) && is_array($param['cs']) && !empty($param['cs'])) {
            $map->where('o.cs', 'in', $param['cs']);
        }
        return  $this->link()->name("orders")->alias("o")->where($map)
            ->join("order_csos_new c", "c.order_id = o.id", "inner")
            ->count();
    }
}