<?php
// +----------------------------------------------------------------------
// | CityQuote
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\db\Where;
use think\Model;

class SaleReportQuoteLog extends Model
{
    /**
     * 是否需要自动写入时间戳 如果设置为字符串 则表示时间字段的类型
     * @var bool|string
     */
    protected $autoWriteTimestamp = true;

    /**
     * 创建时间字段 false表示关闭
     * @var false|string
     */
    protected $createTime = 'create_time';

    /**
     * 更新时间字段 false表示关闭
     * @var false|string
     */
    protected $updateTime = false;

    protected $type = [
        'content' => 'array'
    ];

    public function getCount($map)
    {
        return $this->where(new Where($map))->count('id');
    }

    public function getList($map, $page = null, $pageSize = null, $field = '*', $orderby = 'create_time desc')
    {
        if ($page == 0) {
            $offset = 0;
        } else {
            $offset = $pageSize * ($page - 1);
        }
        return $this->field($field)->where(new Where($map))->limit($offset, $pageSize)->order($orderby)->select();
    }

    public function addOne($data)
    {
        return $this->save($data);
    }

    public function getNoneLogCityQuote($map)
    {
        return self::name('sale_report_city_quote')->alias('a')
            ->leftJoin('sale_report_quote_log b','b.quote_id = a.id')
            ->field('a.id,a.city_name,a.parent_city_name,a.little,a.quarter_quote,a.half_year_quote,a.year_quote,
            a.month_promise_order,a.year_member_time,a.is_consist,a.consist_price,a.consist_fen,a.consist_zeng,
            a.is_exclusive,a.exclusive_price,a.exclusive_fen_max,a.exclusive_fen_min,a.exclusive_zeng,a.rebates_ratio,a.round_order_money,
            a.grade_a_price,a.grade_a_order,a.grade_b_price,a.grade_b_order,a.grade_c_price,a.grade_c_order,a.grade_d_price,a.grade_d_order')
            ->where(new Where($map))
            ->group('a.id')
            ->having('COUNT(b.id) is null or COUNT(b.id) = 0')
            ->select();
    }

    public function delLog($map)
    {
        return $this->where(new Where($map))->delete();
    }
}