<?php


namespace Home\Model\Db;


use Think\Model;

class MallExchangeOrderModel extends Model
{
    protected $tableName = "mall_exchange_order";

    public function getExchangeCount($data){
        $map = [];
        if(!empty($data['name'])){
            $map['b.name'] = ['like','%'.$data['name'].'%'];
        }

        if(!empty($data['start_time'])){
            $map['a.created_at'] = ['EGT',$data['start_time']];
        }

        if(!empty($data['end_time'])){
            $map['a.created_at'] = ['ELT',$data['end_time']];
        }

        $buildSql = $this->field('goods_id')->alias('a')
            ->join('qz_mall_goods as b on b.id = a.goods_id')
            ->group('goods_id')
            ->where($map)
            ->buildSql();

        return $this->table($buildSql)->alias('t')->count(1);
    }

    public function getExchangeList($data,$page,$size){
        $map = [];
        if(!empty($data['name'])){
            $map['b.name'] = ['like','%'.$data['name'].'%'];
        }

        if(!empty($data['start_time'])){
            $map['a.created_at'] = ['EGT',$data['start_time']];
        }else{
            $map['a.created_at'] = ['EGT',strtotime(date('Y-m-d',strtotime("-1 year")))];
        }

        if(!empty($data['end_time'])){
            $map['a.created_at'] = ['ELT',$data['end_time']];
        }

        $order = 'goods_sum desc';
        $order2 = 't.goods_sum desc';
        //积分 1,2 等级 3,4 兑换量
        if(!empty($data['order'])){
            switch($data['order']){
                case 1:
                    $order = 'c.point';
                    $order2 = 't.point';
                    break;
                case 2:
                    $order = 'c.point desc';
                    $order2 = 't.point desc';
                    break;
                case 3:
                    $order = 'c.grade';
                    $order2 = 't.grade';
                    break;
                case 4:
                    $order = 'c.grade desc';
                    $order2 = 't.grade desc';
                    break;
                case 5:
                    $order = 'goods_sum';
                    $order2 = 't.goods_sum';
                    break;
                case 6:
                    $order = 'goods_sum desc';
                    $order2 = 't.goods_sum desc';
                    break;
                default:
                    $order = 'goods_sum desc';
                    $order2 = 't.goods_sum desc';
                    break;
            }
        }

            $buildSql = $this->alias('a')
                ->field('sum(num) as goods_sum,c.grade,c.point,b.name,b.id')
                ->join('qz_mall_goods as b on b.id = a.goods_id')
                ->join('qz_mall_goods_score as c on c.goods_id =  a.goods_id')
                ->group('a.goods_id')
                ->where($map)
                ->limit($page,$size)
                ->order($order)
                ->buildSql();

            return $this->table($buildSql)->alias('t')
                ->field('t.*,d.img')
                ->join("left join qz_mall_goods_img as d on d.goods_id  = t.id")
                ->field('t.*,d.img')
                ->group('t.id')
                ->order($order2)
                ->select();
    }

}