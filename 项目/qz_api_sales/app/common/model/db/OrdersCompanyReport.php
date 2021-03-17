<?php
// +----------------------------------------------------------------------
// | OrdersCompanyReport
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\db\Where;
use think\Model;

class OrdersCompanyReport extends Model
{
    protected $table = 'qz_orders_company_report';

    /**
     * 关联城市
     *
     * @return \think\model\relation\HasOne
     */
    public function city()
    {
        return $this->hasOne('Quyu', 'cid', 'cs')->field('cid,cname');
    }

    /**
     * 关联地区
     *
     * @return \think\model\relation\HasOne
     */
    public function area()
    {
        return $this->hasOne('Area', 'qz_areaid', 'qx')->field('qz_areaid,qz_area');
    }

    /**
     * 关联户型
     *
     * @return \think\model\relation\HasOne
     */
    public function hx()
    {
        return $this->hasOne('Huxing', 'id', 'huxing')->field('id,name');
    }

    /**
     * 关联方式
     *
     * @return \think\model\relation\HasOne
     */
    public function fs()
    {
        return $this->hasOne('Fangshi', 'id', 'fangshi')->field('id,name');
    }

    /**
     * 关联风格
     *
     * @return \think\model\relation\HasOne
     */
    public function fg()
    {
        return $this->hasOne('Fengge', 'id', 'fengge')->field('id,name');
    }

    /**
     * 关联签单公司
     *
     * @return \think\model\relation\HasOne
     */
    public function qdcompany()
    {
        return $this->hasOne('User', 'id', 'order_company_id')->field('id,jc,qc');
    }

    /**
     * 获取列表
     *
     * @param array $map 查询条件
     * @param null $page 分页
     * @param null $pageSize 分页
     * @param array $with 关联信息
     * @param array $field 查询字段
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getList($map, $page = null, $pageSize = null, $with = [], $field = '*')
    {
        if ($page == 0) {
            $offset = 0;
        } else {
            $offset = $pageSize * ($page - 1);
        }
        return self::where(new Where($map))
            ->field($field)
            ->limit($offset, $pageSize)
            ->order('time_add desc')
            ->with($with)
            ->select();
    }

    /**
     * 获取列表
     *
     * @param array $map 查询条件
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getCount($map)
    {
        return self::where(new Where($map))->count();
    }

    /**
     * 获取详情
     *
     * @param $map
     * @param array $with
     * @param string $field
     * @return array|null|\PDOStatement|string|Model
     */
    public function getOne($map, $with = [], $field = '*')
    {
        return self::where(new Where($map))->field($field)->with($with)->find();
    }

    protected function getUnionAllBuildSql($start, $end, $cs)
    {
        $map['time_add'] = ['between', [$start, $end]];
        $map['order_on'] = ['=', 1];
        $buildSql = $this->table('qz_orders_company_report')
            ->field(['COUNT(`id`) consult_num', '0 qiandan_num', 'cs'])
            ->where(new Where($map))
            ->group('cs')
            ->unionAll(function ($query) use ($start, $end) {
                $orderMap['o.qiandan_companyid'] = ['>', 0];
                $orderMap['o.on'] = ['=', 4];
                $orderMap['o.type_fw'] = ['in', [1, 2]];
                // $orderMap['o.qiandan_status'] = ['=', 1];
                $orderMap['o.qiandan_addtime'] = ['between', [$start, $end]];
                $query->table('qz_orders')->alias('o')
                    ->join("order_info i", "i.`order` = o.id and i.com = o.qiandan_companyid", "inner")
                    ->join('user u', 'u.id = o.qiandan_companyid')
                    ->field(['0 consult_num', 'count(`o`.`id`) qiandan_num', 'o.cs'])
                    ->where(new Where($orderMap))
                    ->group('o.cs');
            })
            ->buildSql();

        return $this->table($buildSql)->alias('base')->field(
                [
                    'SUM(base.consult_num) consult_num',
                    'SUM(base.qiandan_num) qiandan_num',
                    'base.cs',
                    'q.cname',
                    'upper(left(q.px_abc,1)) px_abc'
                ]
            )->group('base.cs')
            ->join('qz_quyu q', 'q.cid = base.cs AND q.is_open_city = 1')
            ->where(new Where(!empty($cs) ? ['q.cid' => $cs] : []))
            ->buildSql();
    }

    public function getConsultList($start, $end, $cs, $order)
    {
        $orderBy = explode(',', $order);
        $buildSql = $this->getUnionAllBuildSql($start, $end, $cs);
        return $this->table($buildSql)->alias('t')
            ->field('t.consult_num,t.qiandan_num,(t.consult_num + t.qiandan_num) sum_num,t.cs,t.cname,t.px_abc')
            ->order(array_merge($orderBy, ['px_abc']))
            ->select();
    }
}