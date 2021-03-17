<?php
/**
 *  礼券表信息
 */
namespace Common\Model\Db;
use Think\Model;
class CardModel extends Model
{
    /**
     * 获取公司可用礼券
     * @param $comid 公司id
     */
    public function getCardList($comid){
        $map['com.com_id'] = $comid;
        $map['r.check'] = array('EQ',2);
//        $map['c.enable'] = array('EQ',1);
        $map['r.activity_start'] = array('LT',time());
        $map['r.activity_end'] = array('GT',time());
        $map['r.apply_state'] = array('EQ',1);
        $map['_string'] = '(c.enable =1 or (c.enable = 2 and c.disable_time >'.time().'))'; //未禁用或禁用时间未到
        $buildSql =  M('card_com_record')->alias('r')
            ->join('qz_card_com com on com.id = r.card_com_id')
            ->join('qz_card c on c.id = com.card_id')
            ->field('c.name,r.start,r.end,c.type,c.active_type,c.money1,c.money2,c.money3,c.gift,c.img2,c.module,r.id,r.amount')
            ->where($map)
            ->limit(4)
            ->buildSql();
        $list = M('card_com_record')->table($buildSql)->alias('k')
            ->field('k.*,count(u.id) as usenum')
            ->join('left join qz_card_user u ON u.record_id = k.id')
            ->group('k.id')
            ->select();
        return $list;
    }

    //获取优惠券
    public function getSpecialCardById($comid, $limit)
    {
        if (!$comid) {
            return array();
        }
        $map = [];
        $map['b.com_id'] = array('EQ', $comid);
        $map['c.activity_start'] = array('ELT', time());
        $map['c.activity_end'] = array('EGT', time());
        $map['c.check'] = array('EQ', 2);
        $map['c.apply_state'] = array('EQ', 1);
//        $map['a.enable'] = array('EQ',1);
        $map['_string'] = '(a.enable =1 or (a.enable = 2 and a.disable_time >' . time() . '))'; //未禁用或禁用时间未到

        $list = M('card_com')->alias('b')
            ->field('a.id,a.`name`')
            ->where($map)
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->limit($limit)
            ->select();
        return $list ? $list : array();
    }

    /**
     * getCardInfoByCardid  根据条件获取优惠券信息
     * @param $map
     */
    public function getCardInfoByCardid($map){

        $buildSql = M('card_com')->alias('b')
            ->where($map)
            ->field('a.`name`,a.type,a.active_type,FLOOR(a.money1) as money1,FLOOR(a.money2) as money2,FLOOR(a.money3) as money3,a.gift,a.rule,b.com_id,c.`start`,c.`end`,c.id AS record_id,c.amount')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->buildSql();
        $list = M('card_com')->table($buildSql)->alias('k')
            ->field('k.*,count(u.id) as usenum')
            ->join('left join qz_card_user u ON u.record_id = k.record_id')
            ->group('k.record_id')
            ->find();
        return $list ?  $list : array();
    }

    /**
     * 根据手机号获取订单数量
     */
    public function getOrdersCountByTel($tel,$comid){
        $map['o.tel_encrypt'] = array('EQ',md5($tel.C('QZ_YUMING')));
        $map['o.`on`'] = array('IN',array(0,2,4,6,7,98,99));
        $map['r.comid'] = array('EQ',$comid);

        $count = M('orders')->alias('o')
            ->where($map)
            ->field('o.id orderid,r.liangfang yz_lf_status,r.yz_lf_time')
            ->join('qz_order_company_review r on r.orderid = o.id')
            ->order('r.time desc,r.lf_time desc')
            ->find();

        return $count ? $count : array();
    }


    /**
     * getCardinfoByRecordId  根据recordid 获取优惠券信息
     * @param $cardid
     */
    public function getCardinfoByRecordId($cardid){
        $map = [];
        $map['c.id'] = $cardid;
        return M('card_com_record')->alias('c')
            ->where($map)
            ->field('a.id,a.name,a.type,a.money1,a.money2,a.rule,b.id as card_com_id,b.com_id,c.id as record_id,c.amount,c.start,c.end,u.jc,q.bm')
            ->join('qz_card_com b on b.id = c.card_com_id')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_user u on u.id = b.com_id')
            ->join('qz_quyu q on q.cid = u.cs')
            ->find();
    }

    /**
     * checkHadReceived     根据优惠券id和手机号验证是否已领取过该优惠券
     * @param $record_id
     * @param $tel
     */
    public function checkHadReceived($record_id,$tel){
        $map = [];
        $map['record_id'] = $record_id;
        $map['tel'] = $tel;
        $had = M('card_user')->where($map)->find();
        if($had){
            return true;
        }else{
            return false;
        }
    }


    /**
     * getReceiveCountByid      更具订单id获取订单的领用数量
     * @param $receive_id
     */
    public function getReceiveCountByid($receive_id){
        $map = [];
        $map['record_id'] = $receive_id;
        return M('card_user')->where($map)->count();
    }


    /**
     * checkOnly 验证优惠券编号的唯一性
     * @param $cardNo
     */
    public function checkOnly($cardNo){
        $map = [];
        $map['card_number'] = $cardNo;
        $had = M('card_user')
            ->where($map)
            ->getfield('id');
        if($had){
            return false;
        }else{
            return true;
        }
    }

    /**
     * addReceiveCardLog   添加领用礼券记录
     * @param $tel    手机号
     * @param $cardid   礼券id
     * @param $cardNo   礼券编号
     * @return mixed
     */
    public function addReceiveCardLog($tel,$cardid,$cardNo,$xiaoqu){
        $map = [];
        $map['tel'] = $tel;
        $map['record_id'] = $cardid;
        $map['card_number'] = $cardNo;
        $map['xiaoqu'] = $xiaoqu;
        $map['add_time'] = time();
        return  M('card_user')->add($map);
    }


    /**
     * 添加关联
     * @param [type] $data [description]
     */
    public function addAllRelated($data)
    {
        return M("user_notice_related")->addAll($data);
    }



}