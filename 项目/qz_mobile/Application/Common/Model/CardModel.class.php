<?php
/**
 *  优惠券
 */
namespace Common\Model;
use Think\Model;
class CardModel extends Model{

    /**
     * 根据手机号获取订单数量
     */
    public function getOrdersCountByTel($tel){
        $map['o.tel_encrypt'] = array('EQ',md5($tel.C('QZ_YUMING')));
        $map['o.`on`'] = array('IN',array(0,2,4,6,7,98,99));

        $count = M('orders')->alias('o')
            ->where($map)
            ->count();
        return $count ? $count : 0;

    }

    /**
     * getUserOrders 根据条件获取订单
     * @param $data  手机号
     */
    public function  getUserOrders($data){
        $tel = $data['tel'];
        $map['o.tel_encrypt'] = array('EQ',md5($tel.C('QZ_YUMING')));
        $map['o.`on`'] = array('IN',array(0,2,4,6,7,98,99));

        $list = M('orders')->alias('o')
            ->where($map)
            ->field('o.id,o.`on`,o.type_fw,o.xiaoqu,o.time_real,o.dz,o.userid')
            ->order('o.time_real desc')
            ->select();
        return $list ? $list : array();
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
            ->field('a.id,a.name,a.type,a.active_type,a.active_type,a.money1,a.money2,a.money3,a.gift,a.rule,b.id as card_com_id,b.com_id,c.id as record_id,c.amount,c.start,c.end,u.jc,q.bm')
            ->join('qz_card_com b on b.id = c.card_com_id')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_user u on u.id = b.com_id')
            ->join('qz_quyu q on q.cid = u.cs')
            ->find();
    }


    /**
     * getCurrencyCardList      根据城市id获取通用券
     * @param $cs
     */
    public function getCurrencyCardList($cs,$type = 1,$limit=10){
        $map = [];
        if($type == 1){
            if($cs){                    //以获取到城市id， 获取该城市的装修公司的通用券    如未获取到城市id， 获取全国的通用券
                $map['u.cs'] = array('EQ',$cs);
            }
        }else{
            if($cs){                    //以获取到城市id， 获取该城市的装修公司的通用券    如未获取到城市id， 获取全国的通用券
                $map['u.cs'] = array('NEQ',$cs);
            }
        }

        $map['c.activity_start'] = array('ELT',time());
        $map['c.activity_end'] = array('EGT',time());
//        $map['a.enable'] = array('EQ',1);           //1表示可用
        $map['c.check'] = array('EQ',2);            //2表示审核通过
        $map['c.apply_state'] = array('EQ',1);
        $map['a.type'] = 1;
        $map['_string'] = '(a.enable =1 or (a.enable = 2 and a.disable_time >'.time().'))'; //未禁用或禁用时间未到
        $list = M('user')->alias('u')
            ->field('u.id,u.jc,u.logo,c.apply_time,count(u.id) as countcard')
            ->where($map)
            ->join('qz_card_com b on b.com_id = u.id')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->group('id')
            ->order('countcard desc,apply_time desc')
            ->limit($limit)
            ->select();
//        dump($list);die;
        return $list;
    }

    /**
     * getSpecialCardList   根据城市id获取专用券
     * @param $cs
     * @return mixed
     */
    public function getSpecialCardList($cs){
        $map = [];
        if($cs){                    //以获取到城市id， 获取该城市的装修公司的专用券   如未获取到城市id， 获取全国的专用券
            $map['u.cs'] = $cs;
        }
        $map['a.type'] = 2;
        $map['c.activity_start'] = array('ELT',time());
        $map['c.activity_end'] = array('EGT',time());
//        $map['a.enable'] = array('EQ',1);           //1表示可用
        $map['c.check'] = array('EQ',2);            //2表示审核通过
        $map['c.apply_state'] = array('EQ',1);    //1表示上架
        $map['_string'] = '(a.enable =1 or (a.enable = 2 and a.disable_time >'.time().'))'; //未禁用或禁用时间未到
        $list = M('user')->alias('u')
            ->field('u.id,u.jc,u.logo,a.active_type,a.money1,a.money2,a.money3,a.gift,c.activity_start,c.id card_id')
            ->where($map)
            ->join('qz_card_com b on b.com_id = u.id')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->order('c.activity_start desc')
            ->limit(10)
            ->select();
        return $list;
    }


    /**
     * getCompanyListByOrderId    根据订单id获取装修公司列表
     * @param $orderid
     */
    public function getCompanyListByOrderId($orderid){
        $map = [];
        $map['r.orderid'] = $orderid;
        return M('order_company_review')->alias('r')
            ->where($map)
            ->field('r.orderid,r.comid,r.liangfang,r.lf_time,r.yz_lf_time,u.jc,u.logo')
            ->join('qz_user u on u.id = r.comid')
            ->select();
    }

    /**
     * getSpecialCardByCompanyId  根据公司id获取礼券  （登陆）
     * @param $comid
     * @return array
     */
    public function getSpecialCardByCompanyId($comid){
        $map = [];
        $map['a.type'] = array('eq',2);
        $map['c.activity_start'] = array('ELT',time());
        $map['c.activity_end'] = array('EGT',time());
//        $map['a.enable'] = array('EQ',1);
        $map['c.check'] = array('EQ',2);
        $map['c.apply_state'] = array('EQ',1);
        $map['b.com_id'] = array('EQ',$comid);
        $map['_string'] = '(a.enable =1 or (a.enable = 2 and a.disable_time >'.time().'))';
        if(session('user_card_tel.tel')){
            $join = 'left join qz_card_user d on d.record_id = c.id and d.tel ='.session('user_card_tel.tel');
        }else{
            $join = 'left join qz_card_user d on d.record_id = c.id';
        }

        $buildSql = M('card_com')->alias('b')
            ->where($map)
            ->field('a.`name`,a.type,a.active_type,FLOOR(a.money1) as money1,FLOOR(a.money2) as money2,FLOOR(a.money3) as money3,a.gift,b.com_id,c.`start`,c.`end`,c.id AS record_id,c.amount,d.tel,c.apply_time')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->join($join)
            ->group('a.id')
            ->buildSql();
        $list = M('card_com')->table($buildSql)->alias('k')
            ->field('k.*,count(u.id) as usenum')
            ->join('left join qz_card_user u ON u.record_id = k.record_id')
            ->group('k.record_id')
            ->order('apply_time desc')
            ->select();
//        dump($list);die;
        return $list ?  $list : array();
    }

    /**
     * 添加关联
     * @param [type] $data [description]
     */
    public function addAllRelated($data)
    {
        return M("user_notice_related")->addAll($data);
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
       $mao['xiaoqu'] = $xiaoqu;
       $map['add_time'] = time();
       return  M('card_user')->add($map);
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
     * signLiangFang    标记量房
     * @param $map
     * @param $savedata
     */
    public function signLiangFang($map,$savedata){
        $return = M('order_company_review')->where($map)->Save($savedata);
        if($return === false){
            return false;
        }else{
            return true;
        }
    }

    /**
     * getTongYongCardByComid   根据公司id获取通用礼券
     * @param $comid
     */
    public function getTongYongCardByComid($comid){
        $map['a.type'] = 1;
        $map['b.com_id'] = $comid;
        $map['c.activity_start'] = array('ELT',time());
        $map['c.activity_end'] = array('EGT',time());
//        $map['a.enable'] = array('EQ',1);           //1表示可用
        $map['c.check'] = array('EQ',2);            //2表示审核通过
        $map['c.apply_state'] = array('EQ',1);    //1表示上架
        $map['_string'] = '(a.enable =1 or (a.enable = 2 and a.disable_time >'.time().'))'; //未禁用或禁用时间未到
        $info =  M('card_com')->alias('b')
            ->where($map)
            ->field('a.*,b.*,c.*,u.jc,q.bm')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->join('qz_user u on u.id = b.com_id')
            ->join('qz_quyu q on q.cid = u.cs')
            ->order('c.apply_time desc')
            ->find();

        $map_b = array();
        $map_b['u.id'] = array('EQ',$comid);
        $cominfo = M('user')->alias('u')
            ->where($map_b)
            ->field('u.id,u.jc,q.bm')
            ->join('qz_quyu q on q.cid = u.cs')
            ->find();
        if($cominfo){
            $info['jc'] = $cominfo['jc'];
            $info['bm'] = $cominfo['bm'];
            $info['com_id'] = $cominfo['id'];
        }
        return $info;
    }


    /**
     * secrchSpecialCardHadGot  查询是否已经领取过该优惠券
     * @param $cardid
     */
    public function secrchSpecialCardHadGot($cardid){
        $tel = session('user_card_tel.tel');
        $record_id = $cardid;
        $map = [];
        $map['tel'] = $tel;
        $map['record_id'] = $record_id;
        return M('card_user')->where($map)->find();
    }


    /**
     * getCardInfoAndMoreByCardid  根据优惠券id查询优惠券的信息和领用次数以及是否已领取过
     * @param $cardid
     * @return array
     */
    public function getCardInfoAndMoreByCardid($cardid){
        $map=[];
        $map['c.id'] = $cardid;
        if(session('user_card_tel.tel')){
            $join = 'left join qz_card_user d on d.record_id = c.id and d.tel = '. session('user_card_tel.tel');
        }else{
            $join = 'left join qz_card_user d on d.record_id = c.id';
        }
        $buildSql = M('card_com')->alias('b')
            ->where($map)
            ->field('a.`name`,a.type,a.active_type,FLOOR(a.money1) as money1,FLOOR(a.money2) as money2,FLOOR(a.money3) as money3,a.gift,a.rule,b.com_id,c.`start`,c.`end`,c.id AS record_id,c.amount,d.tel,u.jc,q.bm')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->join($join)
            ->join('qz_user u on u.id = b.com_id')
            ->join('qz_quyu q on q.cid = u.cs')
            ->buildSql();
        $list = M('card_com')->table($buildSql)->alias('k')
            ->field('k.*,count(u.id) as usenum')
            ->join('left join qz_card_user u ON u.record_id = k.record_id')
            ->group('k.record_id')
            ->find();
        return $list ?  $list : array();
    }

    /**
     * searchLiangFangStatus   查询该装修公司是否量房
     */
    public function searchLiangFangStatus($tel = '',$comid = ''){
        if(empty($tel) || empty($comid)){
            return 2;  //2表示未量房
        }
        $map = [];
        $map['o.tel_encrypt'] = array('EQ',md5($tel.C('QZ_YUMING')));
        $map['r.liangfang'] = array('EQ',1); //1表示已量房
        $map['r.comid'] = array('EQ',$comid);
        $list = M('orders')->alias('o')
            ->where($map)
            ->field('r.*')
            ->join('qz_order_company_review r on r.orderid = o.id')
            ->select();
//        echo M()->_sql();
//        dump($list);die;
        if($list){
            return 1;   //1表示已量房
        }else{
            return 2;   //2表示未量房
        }
    }


    /**
     * getRecordidByReceiveId  根据优惠券领取记录id获取优惠券信息
     * @param $receiveid
     */
    public function getRecordidByReceiveId($receiveid){
        $map = [];
        $map['id'] = array('EQ',$receiveid);
        $map['tel'] = array('EQ',session('user_card_tel.tel'));
        return M('card_user')->where($map)
                ->field('id,tel,record_id,card_number')
                ->find();
    }


    /**
     * getCompanyListByTel   根据手机号获取优惠券商家列表
     * @param $tel
     */
    public function getCompanyListByTel($tel){
        $map = [];
        $map['d.tel'] = array('EQ',$tel);
        $map['c.activity_start'] = array('ELT',time());
        $map['c.activity_end'] = array('EGT',time());
        $comlist = M('card_user')->alias('d')
            ->where($map)
            ->field('b.com_id,u.id,u.jc')
            ->join('qz_card_com_record c on c.id = d.record_id')
            ->join('qz_card_com b on b.id = c.card_com_id')
            ->join('qz_user u on u.id = b.com_id')
            ->group('b.com_id')
            ->select();
        return $comlist;
    }


    /**
     * getReceiveCardByCompanyId  根据公司id和手机号获取已领取的订单
     * @param $comid
     * @return array
     */
    public function getReceiveCardByCompanyId($comid,$tel,$type=1){
        $map = [];
        if($type == 1){  //1表示可用优惠券
            $map['c.activity_start'] = array('ELT',time());
            $map['c.activity_end'] = array('EGT',time());
        }else{       //2表示已过期的优惠券
            $map['c.activity_end'] = array('lt',time());
        }
        $map['b.com_id'] = array('EQ',$comid);
        $map['d.tel'] = array('EQ',$tel);

        $list = M('card_com')->alias('b')
            ->where($map)
            ->field('a.`name`,a.type,a.active_type,FLOOR(a.money1) as money1,FLOOR(a.money2) as money2,FLOOR(a.money3) as money3,a.gift,b.com_id,c.`start`,c.`end`,c.id AS record_id,c.amount,d.tel,d.id as receiveid')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->join('join qz_card_user d on d.record_id = c.id ')
            ->select();
        return $list ?  $list : array();
    }


    /**
     *
     */
    public function getTongYongByCompanyId($comid){
        $map = [];
        $map['c.activity_start'] = array('ELT',time());
        $map['c.activity_end'] = array('EGT',time());
//        $map['a.enable'] = array('EQ',1);
        $map['c.check'] = array('EQ',2);
        $map['c.apply_state'] = array('EQ',1);
        $map['a.type'] = array('EQ',1);
        $map['_string'] = '(a.enable =1 or (a.enable = 2 and a.disable_time >'.time().'))'; //未禁用或禁用时间未到

        if(session('user_card_tel.tel')){
            $map_b['u.tel'] = array('EQ',session('user_card_tel.tel'));
        }else{
//            $map_b['u.id'] = array('NEQ','');
        }
        $buildSql = M('card_com')->alias('b')
            ->where($map)
            ->field('a.`name`,a.type,FLOOR(a.money1) as money1,FLOOR(a.money2) as money2,b.com_id,c.`start`,c.`end`,c.id AS record_id,c.amount,d.tel,c.apply_time,a.rule')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->join('left join qz_card_user d on d.record_id = c.id')
            ->buildSql();
        $list = M('card_com')->table($buildSql)->alias('k')
            ->field('k.*,count(u.id) as usenum')
            ->where($map_b)
            ->join('left join qz_card_user u ON u.record_id = k.record_id')
            ->group('k.record_id')
            ->order('apply_time desc')
            ->limit(1)
            ->select();
        return $list ?  $list : array();
    }


    /**
     * getCompanySafeTelByComId  根据装修公司id获取安全手机号
     * @param $comid
     */
    public function getCompanySafeTelByComId($comid){
        if(!$comid){
            return false;
        }
        return M('user')->where(array('id'=>$comid))->getfield('tel_safe');
    }


    /**
     * getTongYongCardByJiaComid 假会员获取通用券（获取最新的一张可用的通用优惠券）
     * @param $comid
     * @return mixed
     */
    public function getTongYongCardByJiaComid($comid){
        $map['a.type'] = 1;
        $map['c.activity_start'] = array('ELT',time());
        $map['c.activity_end'] = array('EGT',time());
//        $map['a.enable'] = array('EQ',1);           //1表示可用
        $map['c.check'] = array('EQ',2);            //2表示审核通过
//        $map['c.apply_state'] = array('EQ',1);    //1表示上架
        $map['_string'] = '(a.enable =1 or (a.enable = 2 and a.disable_time >'.time().'))'; //未禁用或禁用时间未到

        $map_b = [];
        $map_b['u.id'] = array('EQ',$comid);
        $info =  M('card_com')->alias('b')
            ->where($map)
            ->field('a.*,b.*,c.*')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->order('c.apply_time desc')
            ->find();
//        dump($info);die;
        $cominfo = M('user')->alias('u')
            ->where($map_b)
            ->field('u.id,u.jc,q.bm')
            ->join('qz_quyu q on q.cid = u.cs')
            ->find();

        if($cominfo && $info){
            $info['jc'] = $cominfo['jc'];
            $info['bm'] = $cominfo['bm'];
            $info['com_id'] = $cominfo['id'];
        }
        return $info;

    }

    /**
     * 获取可用通用券的数量
     * @return mixed
     */
    public function getOneTongyongCard(){
        $map['a.type'] = 1;
        $map['c.activity_start'] = array('ELT',time());
        $map['c.activity_end'] = array('EGT',time());
        $map['c.check'] = array('EQ',2);            //2表示审核通过
        $map['_string'] = '(a.enable =1 or (a.enable = 2 and a.disable_time >'.time().'))'; //未禁用或禁用时间未到

        $info =  M('card_com')->alias('b')
            ->where($map)
            ->field('a.*,b.*,c.*')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->order('c.apply_time desc')
            ->count();
        return $info;
    }



}