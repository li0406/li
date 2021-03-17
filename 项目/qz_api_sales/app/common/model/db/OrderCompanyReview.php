<?php
// +----------------------------------------------------------------------
// | OrderCompanyReview  装修公司订单跟踪表
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\db\Where;
use think\Model;

class OrderCompanyReview extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_order_company_review';

    /**
     * 获取具体公司信息
     *
     * @return \think\model\relation\HasOne
     */
    public function company()
    {
        return $this->hasOne('User','id','comid')->field('id,jc as company_name,jc,qc')->bind('jc,qc');
    }

    /**
     * 添加时间获取器
     *
     * @param $value
     * @return false|string
     */
    public function getTimeAttr($value)
    {
        if ($value) {
            return date('Y-m-d H:i:s',$value);
        } else {
            return '';
        }
    }

    /**
     * 量房时间获取器
     *
     * @param $value
     * @return false|string
     */
    public function getLfTimeAttr($value)
    {
        if ($value) {
            return date('Y-m-d H:i:s',$value);
        } else {
            return '';
        }
    }

    /**
     * 修改装修公司订单跟踪信息
     *
     * @param $map
     * @param $data
     * @return bool
     */
    public function editReview($map, $data)
    {
        return self::allowField(true)->isUpdate(true)->save($data,$map);
    }

    /**
     * 获取订单量房状态
     *
     * @param array $map 查询条件
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getOrderLiangfangStatus($map)
    {
        return self::table(self::field('orderid,status')->where(new Where($map))->buildSql())
            ->alias('a1')
            ->field([
                'a1.orderid',
                'count(i.com)' => 'company_count',
                'count(IF(a1.status in (1,2,4),1,NULL))' => 'choose_liangfang',
                'count(IF(a1.status = 3,1,NULL))' => 'choose_no_liangfang'
            ])
            ->join('order_info i','i.order = a1.orderid')
            ->group('a1.orderid')
            ->select();
    }

    public function getReviewInfoByCompany($where,$field = '*'){
        return $this->field($field)->where(new Where($where))->select();
    }

    public function delOrderCompanyReview($where)
    {
        return $this
            ->where(new Where($where))
            ->delete();
    }


    public function getNewestLfOrder($city, $limit = 5, $order = 'r.order_time desc', $field = 'r.orderid order_id,r.lf_time order_time')
    {
        if (empty($city)) {
            return [];
        }
        $where[] = [
            'r.lf_time', 'between', strtotime('-3 day') . ',' . time(),
            'r.status', 'in', [1, 2]
        ];

        $buildSql = $this->alias('r')
            ->field($field)
            ->join('qz_order_info i','i.`order` = r.orderid')
            ->where($where)
            ->group('r.orderid')
            ->buildSql();
        return $this->table($buildSql)->alias('r')
            ->order($order)
//            ->limit($limit)
            ->select();
    }
}