<?php
/**
 * 装修公司礼券表
 */

namespace Common\Model\Db;

use Think\Model;

class CardModel extends Model
{
    /**
     * 添加专用礼券
     * @param $data
     * @return
     */
    public function addSpecialCard($data){
        return M('card')->add($data);
    }

    /**
     * saveSpecialCard  修改礼券信息
     * @param $data
     * @return bool
     */
    public function saveSpecialCard($data){
        $return = M('card')->save($data);
        if($return === false){
            return false;
        }else{
            return true;
        }
    }

    /**
     * getCardComRecordIdByCardId  根据条件获取qz_card_com_record表中的id
     * @param $cardid
     * @return mixed
     */
    public function getCardComRecordIdByCardId($map){
        $cardComid =  M('card_com')->where($map)->getfield('id');
        if($cardComid){
            return M('card_com_record')->where(array('card_com_id'=>$cardComid))->getfield('id');
        }else{
            return false;
        }
    }

    /**
     * 添加礼券相关信息到qz_card_com表中
     * @param $cardcom
     * @return mixed
     */
    public function addCardCom($cardcom){
        $data['add_time'] = time();
        return M('card_com')->add($cardcom);
    }

    /**
     * 添加礼券相关信息到qz_card_com_record
     * @param $recorddata
     * @return mixed
     */
    public function addCardComRecord($recorddata){
        return M('card_com_record')->add($recorddata);
    }


    /**
     * saveCardComRecord        修改qz_card_com_record表中的数据
     * @param $recorddata
     */
    public function saveCardComRecord($recorddata){
        return M('card_com_record')->save($recorddata);
    }

    /**
     * getSpecialCardByIdCount  获取满足条件的专用券总数量
     * @param $map
     * @return mixed
     */
    public function  getSpecialCardByIdCount($map){
        return M('card_com')->where($map)->alias('b')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->count();
    }

    /**
     * getSpecialCardById  获取满足条件的专用券列表
     * @param $map
     * @param $pageIndex
     * @param $pageCount
     * @return mixed
     */
    public function getSpecialCardById($map,$pageIndex,$pageCount){
        return M('card_com')->where($map)->alias('b')
            ->field('a.id,a.`name`,a.active_type,a.money1,a.money2,a.money3,a.gift,a.`enable`,c.`id` recordid,c.activity_start,c.activity_end,c.`start`,c.`end`,c.amount,c.`check`,c.apply_state')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->limit($pageIndex,$pageCount)
            ->order('a.add_time desc')
            ->select();
    }

    /**
     * 通用券数量
     * @param $where
     */
    public function getCommonCount($where){
        $map['c.type'] = array('EQ',1);
        $map['com.com_id'] = array('EQ',$where['id']);

        $buildSql = M('card_com_record')->alias('r')
            ->join('qz_card_com com on com.id = r.card_com_id')
            ->join('qz_card c on c.id = com.card_id')
            ->field('
                r.id,c.name,r.activity_start,r.activity_end,r.start,r.end,c.money1,c.money2,r.amount,r.check,c.enable,r.apply_time,
                case
                when c.enable = 2 and unix_timestamp() >= c.disable_time then 5
                when r.check = 4 then 1
                when r.check = 3 then 8
                when r.check = 1 then 2
                when r.apply_state = 2 then 3
                when r.check = 2 and unix_timestamp() > r.end then 4
                when r.check = 2 and unix_timestamp() < r.start then 6
                when r.check = 2 and unix_timestamp()>=r.start and unix_timestamp()<=r.end then 7
                end status
           ')
            ->where($map)
            ->buildSql();

        if(!empty($where['status'])){
            $map2['t.status'] = array('EQ',$where['status']);
        }
        return M('card_com_record')->table($buildSql)->alias('t')->where($map2)->order('t.apply_time desc')->count();
    }

    /**
     * 通用券列表
     * @param $where
     * @param $page
     * @param $count
     * @return mixed
     */
    public function getCommonList($where,$page,$count){
        $map['c.type'] = array('EQ',1);
        $map['com.com_id'] = array('EQ',$where['id']);

        $buildSql = M('card_com_record')->alias('r')
            ->join('qz_card_com com on com.id = r.card_com_id')
            ->join('qz_card c on c.id = com.card_id')
            ->join('left join qz_card_user cu on cu.record_id  = r.id')
            ->field('
           r.id,c.name,r.activity_start,r.activity_end,r.start,r.end,c.money1,c.money2,r.amount,r.check,c.enable,r.apply_time,count(cu.id) as get_count,
             case
                when c.enable = 2 and unix_timestamp() >= c.disable_time then 5
                when r.check = 4 then 1
                when r.check = 3 then 8
                when r.check = 1 then 2
                when r.apply_state = 2 then 3
                when r.check = 2 and unix_timestamp() > r.end then 4
                when r.check = 2 and unix_timestamp() < r.start then 6
                when r.check = 2 and unix_timestamp()>=r.start and unix_timestamp()<=r.end then 7
                end status
           ')
            ->where($map)
            ->group('r.id')
            ->buildSql();

        if(!empty($where['status'])){
            $map2['t.status'] = array('EQ',$where['status']);
        }
        return M('card_com_record')->table($buildSql)->alias('t')->where($map2)->order('t.apply_time desc')->limit($page,$count)->select();
    }

 /**
     * getSpecialCardInfo  获取满足条件的专用券信息（单张）
     * @param $map
     */
    public function getSpecialCardInfo($map){
        return M('card_com')->alias('b')
            ->where($map)
            ->field('a.id,a.`name`,a.money1,a.money2,a.money3,a.gift,a.active_type,a.add_time,a.`rule`,a.module,a.`enable`,c.activity_start,c.activity_end,c.`start`,c.`end`,c.amount,c.`check`,c.apply_state,c.check_reason')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->find();
    }

    /**
     * 可领用券数量
     * @return mixed
     */
    public function getCommonUseCount(){
        $map['type'] = array('EQ',1);
        $map["_complex"] = array(
            "enable" => array("EQ",1),
            "_complex" => array("disable_time" =>  array("GT",time()),
                "enable"=>array("EQ",2),
                "_logic" => "AND"
            ),
            "_logic" => "OR"
        );
        return M('card')->where($map)->count();
    }

    /**
     * 可领用券列表
     * @return mixed
     */
    public function getCommonUseList($start,$pageCount){
        $map['type'] = array('EQ',1);
        $map["_complex"] = array(
            "enable" => array("EQ",1),
            "_complex" => array("disable_time" =>  array("GT",time()),
                "enable"=>array("EQ",2),
                "_logic" => "AND"
            ),
            "_logic" => "OR"
        );

        return M('card')->where($map)
            ->field('id,name,money1,money2')
            ->order('add_time desc')
            ->limit($start,$pageCount)
            ->select();
    }

    /**
     * 通用券编辑页信息
     */
    public function getCardInfo($id){
        $map['id'] = array('EQ',$id);
        $map['type'] = array('EQ',1);
        return M('card')->where($map)->field('id,name,money1,money2,rule,img,img2,enable,disable_time,add_time')->find();
    }

    /**
     * 判断公司是否是真会员
     */
    public function searchid($id){
        $map['u.id'] = array('EQ',$id);
        $map['u.on'] = array('EQ',2);
        $map['u.classid'] = array('EQ',3);
        $map['c.fake'] = array('EQ',0);
        return M('user')->where($map)->alias('u')
            ->field('u.id')
            ->join('qz_user_company c on c.userid = u.id')
            ->find();
    }

    /**
     * 公司是否领取过券
     * @param $cardid
     * @param $comid
     */
    public function existComCard($cardid,$comid){
        $map['card_id'] = array("EQ",$cardid);
        $map['com_id'] = array("EQ",$comid);
        return M('card_com')->where($map)->field('id')->find();
    }

    /**
     * 获取通用券详细信息
     */
    public function getCommonInfo($id){
        $map['r.id'] = array('EQ',$id);
        return M('card_com_record')->alias('r')
            ->join('qz_card_com com on com.id = r.card_com_id')
            ->join('qz_card c on c.id=com.card_id')
            ->where($map)
            ->field('c.name,c.money1,c.money2,r.amount,c.rule,r.apply_time,c.rule,c.module,c.img,c.img2,r.activity_start,r.activity_end,r.start,r.end')
            ->find();
    }

    public function getcarduserlist($where){
        $map['r.id'] = array('EQ',$where['id']);
        $map['com.com_id'] = session('u_userInfo.id');
        $map['c.type']  = 1;
        //礼券编号
        if(!empty($where['number'])){
            $map['u.card_number'] = array('like','%'.trim($where['number']).'%');
        }
        //业主手机号
        if(!empty($where['tel'])){
            $map['u.tel'] = array('like','%'.trim($where['tel']).'%');
        }

        return M('card_user')->alias('u')
            ->join('qz_card_com_record r on r.id = u.record_id')
            ->join('qz_card_com com on com.id = r.card_com_id')
            ->join('qz_card c on c.id = com.card_id')
            ->where($map)
            ->field('u.card_number,u.tel,u.xiaoqu,u.add_time,c.name')
            ->select();
    }

    public function getcardusercount($where){
        $map['r.id'] = array('EQ',$where['id']);
        $map['com.com_id'] = session('u_userInfo.id');
        $map['c.type']  = 1;
        //礼券编号
        if(!empty($where['number'])){
            $map['u.card_number'] = array('like','%'.trim($where['number']).'%');
        }
        //业主手机号
        if(!empty($where['tel'])){
            $map['u.tel'] = array('like','%'.trim($where['tel']).'%');
        }

        return M('card_user')->alias('u')
            ->join('qz_card_com_record r on r.id = u.record_id')
            ->join('qz_card_com com on com.id = r.card_com_id')
            ->join('qz_card c on c.id = com.card_id')
            ->where($map)
            ->count();
    }

    /**
     * getReceiveCardsCount 根据条件获取获取领用券的的总数
     * @param $comid
     * @param $map
     * @return mixed
     */
    public function getReceiveCardsCount($comid,$map){
        $map['b.com_id'] = $comid;

        return M('card_com')->alias('b')
            ->where($map)
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->join('qz_card_user d on d.record_id = c.id')
            ->count();
    }

    /**
     * getReceiveCards  根据条件查询领取礼券列表
     * @param $map
     * @param $pageIndex
     * @param $pageCount
     */
    public function  getReceiveCards($map,$pageIndex,$pageCount){
        return M('card_com')->alias('b')
            ->where($map)
            ->field('a.`name`,b.com_id,c.card_com_id,d.*')
            ->join('qz_card a on a.id = b.card_id')
            ->join('qz_card_com_record c on c.card_com_id = b.id')
            ->join('qz_card_user d on d.record_id = c.id')
            ->limit($pageIndex,$pageCount)
            ->select();
    }


    public function getReceiveCardNum($recordid){
        if(!$recordid){
            return 0;
        }
        return M('card_user')->where(array('record_id'=>$recordid))->count();
    }

    //更新状态
    public function changeRecord($data,$cardid){
        $map['id'] = array('EQ',$cardid);
        $data['update_time'] = time();
        return M('card_com_record')->where($map)->save($data);
    }

}