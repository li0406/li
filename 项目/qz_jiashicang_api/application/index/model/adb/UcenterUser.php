<?php


namespace app\index\model\adb;


use app\common\model\adb\BaseModel;
use think\db\Query;

class UcenterUser extends BaseModel
{
    /**
     * @des:注册用户发单周期
     * @param $begin
     * @param $end
     * @param array $group_id
     * @return mixed
     */
    public function getFaDanCycleAnalysis($begin, $end, $group_id = [])
    {
        $map = new Query();
        $mapOne = new Query();
        $mapTwo = new Query();

        $map->where('created_at', 'between', [$begin, $end]);
        $mapOne->where("FROM_UNIXTIME(a.created_at,'%Y%m%d') <= FROM_UNIXTIME(o.time_real,'%Y%m%d')");
        $mapTwo->where("FROM_UNIXTIME(a.created_at,'%Y%m%d') > FROM_UNIXTIME(o.time_real,'%Y%m%d')");
        if (!empty($group_id)) {
            $mapOne->where('sr.dept', 'in', $group_id);
            $mapTwo->where('sr.dept', 'in', $group_id);
        }
        //查询期间注册的用户
        $commonSql = $this->link()->table('qz_ucenter_user')->where($map)
            ->field("MD5(CONCAT(phone,'qizuang.com')) as phone,created_at")
            ->buildSql();

        //注册用户在注册当天及之后发布订单的数据
        $subSqlOne = $this->link()->table($commonSql)->alias('a')->where($mapOne)
            ->join('qz_orders o', 'o.tel_encrypt=a.phone','inner');
        if (!empty($group_id)) {
            $subSqlOne->join('qz_orders_source s', 's.orderid=o.id', 'inner')
                ->join('qz_order_source sr', 'sr.id=s.source_src_id', 'inner')
                ->join('qz_department_identify d', 'd.id=sr.dept', 'inner');
        }
        $subSqlOne = $subSqlOne->field("a.phone,count(o.id) as count,FROM_UNIXTIME(a.created_at,'%Y%m%d') as created,FROM_UNIXTIME(o.time_real,'%Y%m%d') as time, 1 as sign_value")
            ->group("FROM_UNIXTIME(o.time_real,'%Y%m%d'),o.tel")
            ->buildSql();

        //注册用户注册当天前发布的订单(规划为当天首发)
        $subSqlTwo = $this->link()->table($commonSql)->alias('a')->where($mapTwo)
            ->join('qz_orders o', 'o.tel_encrypt=a.phone', 'inner');
        if (!empty($group_id)) {
            $subSqlTwo->join('qz_orders_source s', 's.orderid=o.id', 'inner')
                ->join('qz_order_source sr', 'sr.id=s.source_src_id', 'inner')
                ->join('qz_department_identify d', 'd.id=sr.dept', 'inner');
        }
        $subSqlTwo = $subSqlTwo->field("DISTINCT(o.tel_encrypt) as phone,1 as count,0 as created, 0 as time, 0 as sign_value")
            ->buildSql();
        return $this->link()->table($subSqlOne)->alias('t')
            ->unionAll($subSqlTwo)
            ->field(["phone","count","created","time","sign_value"])
            ->select();
    }
}