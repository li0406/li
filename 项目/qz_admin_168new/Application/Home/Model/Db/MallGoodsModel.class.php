<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/11/22
 * Time: 10:40
 */

namespace Home\Model\Db;


use Think\Model;

class MallGoodsModel extends Model
{
    protected $tableName = "mall_goods";
    public function getGoodsCount($data){
        $map['a.is_show'] = ['eq',1];
        $map['a.is_gift'] = ['eq',1];
        //礼品名称
        if(!empty($data['name'])){
            $map['a.name'] = ['like','%'.$data['name'].'%'];
        }
        //类型
        if(!empty($data['type'])){
            if($data['type'] == 1){
                $map['a.type'] = 1;
            }else if($data['type'] == 2){
                $map['a.type'] = 2;
                $map['c.use_type'] = 1;
            }
        }
        //消耗积分
        if(!empty($data['min_score'])){
            $map['b.point'][] = ['egt',$data['min_score']];
        }

        if(!empty($data['max_score'])){
            $map['b.point'][]  =  ['elt',$data['max_score']];
        }


        //兑换等级
        if(!empty($data['grade'])){
            $map['b.grade'] = ['eq',$data['grade']];
        }
        //状态
        if(!empty($data['status'])){
            $map['a.status'] = ['eq',$data['status']];
        }

        return $this->alias('a')
            ->join('left join qz_mall_goods_score as b on b.goods_id = a.id')
            ->join('left join qz_mall_coupon as c on c.goods_id = a.id')
            ->where($map)
            ->count(1);
    }

    public function getGoodsList($data,$page,$pageSize){
        $map['a.is_show'] = ['eq',1];
        $map['a.is_gift'] = ['eq',1];
        //礼品名称
        if(!empty($data['name'])){
            $map['a.name'] = ['like','%'.$data['name'].'%'];
        }
        //类型
        if(!empty($data['type'])){
            if($data['type'] == 1){
                $map['a.type'] = 1;
            }else if($data['type'] == 2){
                $map['a.type'] = 2;
                $map['c.use_type'] = 1;
            }
        }
        //消耗积分
        if(!empty($data['min_score'])){
            $map['b.point'][] = ['egt',$data['min_score']];
        }
        if(!empty($data['max_score'])){
            $map['b.point'][] = ['elt',$data['max_score']];
        }

        //兑换等级
        if(!empty($data['grade'])){
            $map['b.grade'] = ['eq',$data['grade']];
        }
        //状态
        if(!empty($data['status'])){
            $map['a.status'] = ['eq',$data['status']];
        }

        /*************排序**************/
        $order = 'a.created_at desc';
        $order2 = 't.created_at desc';
        // 1:积分正序 2:积分倒序 3:库存正序  4:库存倒序  5.等级正序 6.等级倒序 7.添加时间正序 8.添加时间倒序
        if(!empty($data['order'])){
             switch($data['order']){
                 case 1:
                     $order = 'b.point';
                     $order2 = 't.point';
                     break;
                 case 2:
                     $order = 'b.point desc';
                     $order2 = 't.point desc';
                     break;
                 case 3:
                     $order = 'stock';
                     $order2 = 't.stock';
                     break;
                 case 4:
                     $order = 'stock desc';
                     $order2 = 't.stock desc';
                     break;
                 case 5:
                     $order = 'b.grade';
                     $order2 = 't.grade';
                     break;
                 case 6:
                     $order = 'b.grade desc';
                     $order2 = 't.grade desc';
                     break;
                 case 7:
                     $order = 'a.created_at';
                     $order2 = 't.created_at';
                     break;
                 default:
                     $order = 'a.created_at desc';
                     $order2 = 't.created_at desc';
                     break;
             }
        }

        $buildSql = $this->alias('a')
            ->field('a.id ,a.name ,a.type,c.use_type,a.px,b.point,(a.stock-a.exchange_num-a.real_exchange_num) as stock,b.grade,FROM_UNIXTIME(a.created_at , "%Y/%m/%d %H:%i:%s") as created_at,a.created_at as create_time,a.status')
            ->join('left join qz_mall_goods_score as b on b.goods_id = a.id')
            ->join('left join qz_mall_coupon as c on c.goods_id = a.id')
            ->where($map)
            ->limit($page,$pageSize)
            ->order($order)
            ->buildSql();
        return $this->table($buildSql)->alias('t')
            ->join("left join qz_mall_goods_img as d on d.goods_id  = t.id")
            ->field('t.*,d.img')
            ->group('t.id')
            ->order($order2)
            ->select();
    }

    public function getGoodsById($id){
        $map['a.id'] = ['eq',$id];
        $map['a.is_show'] = ['eq',1];
        $map['a.is_gift'] = ['eq',1];
        $buildSql = $this->alias('a')
            ->field('a.id,a.type,a.name,a.status,a.stock,a.exchange_num,a.real_exchange_num,a.is_repeat,a.per_num,a.px')
            ->where($map)
            ->buildSql();
        return $this->table($buildSql)->alias('t')
            ->field('
            t.*,
            b.point,b.grade,b.know,
            c.price as gift_price,c.content as gift_content,
            d.use_type,d.type as coupon_type,d.start_time,d.end_time,d.full_money as coupon_full,d.sub_money as coupon_sub,d.money as coupon_money,d.gift as coupon_gift_name,d.rule as coupon_rule,
            group_concat(e.img) as img_list
            ')
            ->join('left join qz_mall_goods_score as b on b.goods_id  = t.id')
            ->join('left join qz_mall_gift as c on c.goods_id  = t.id')
            ->join('left join qz_mall_coupon as d on d.goods_id  = t.id')
            ->join('left join qz_mall_goods_img as e on e.goods_id = t.id')
            ->group('t.id')
            ->select();
    }

    public function insertInfo($data){
        $data['created_at'] = time();
        $data['updated_at'] = time();
        return $this->add($data);
    }

    public function saveInfo($data,$goods_id){
        if(!empty($goods_id)){
            $data['updated_at'] = time();
            $map['id'] = ['eq',$goods_id];
            $map['is_show'] = ['eq',1];
            return $this->where($map)->save($data);
        }
    }

    public function findGoodsCount($map){
        return $this->where($map)->count(1);
    }

    public function getStockInfo($id){
        $map['id'] = ['eq',$id];
        return $this->where($map)->field('real_exchange_num')->lock(true)->find();
    }
}