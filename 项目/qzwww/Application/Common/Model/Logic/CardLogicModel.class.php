<?php
/**
 *  公司礼券
 */
namespace Common\Model\Logic;

use Common\Model\Db\CardModel;
use Common\Model\CompanyModel;

class CardLogicModel
{
    //获取公司礼券
    public function getCardList($comid)
    {
        $cardDb = new CardModel();
        $list = $cardDb->getCardList($comid);
        //验证是否领取该礼券
        foreach ($list as $key => $value) {
            if(session('user_card_tel')){
                $getreturn = $cardDb->checkHadReceived($value['id'],session('user_card_tel.tel'));  //根据手机号和优惠券id获取是否领取
                if($getreturn){
                    $list[$key]['hadreceive'] = 1;
                }else{
                    $list[$key]['hadreceive'] = 0;
                }
            }else{
                $list[$key]['hadreceive'] = 0;
            }

            //显示修改
            $list[$key]['money1'] = $list[$key]['money1'] ? (int)$list[$key]['money1'] : 0;
            $list[$key]['money2'] = $list[$key]['money2'] ? (int)$list[$key]['money2'] : 0;
            $list[$key]['money3'] = $list[$key]['money3'] ? (int)$list[$key]['money3'] : 0;
            if($value['active_type'] == 2){
                $list[$key]['name'] = '满'.$list[$key]['money3'].'元可领'.$value['gift'];
            }else{
                if($list[$key]['money1'] > 0){
                    $list[$key]['name'] = '满'.$list[$key]['money1'].'元减'.$list[$key]['money2'].'元';
                }else{
                    $list[$key]['name'] = '立减'.$list[$key]['money2'].'元';
                }
            }

        }

        return $list;
    }


    /**
     * getCardInfoByCardid  根据优惠券id获取优惠券信息
     * @param $cardid
     * @return bool
     */
    public  function getCardInfoByCardid($cardid){
        $cardDb = new CardModel();
        if(!$cardid){
            return false;
        }
        $map = [];
        $map['c.id'] = $cardid;
        return $cardDb->getCardInfoByCardid($map);
    }


    /**
     * getOrdersCountByTel    根据手机号获取订单数量
     * @param $tel
     * @return mixed
     */
    public function getOrdersCountByTel($tel,$comid){
        $cardDb = new CardModel();
        return $cardDb->getOrdersCountByTel($tel,$comid);
    }

    //优惠券信息
    public function getCardinfoByRecordId($cardid){
        $cardDb = new CardModel();
        return $cardDb->getCardinfoByRecordId($cardid);
    }

    /**
     * checkHadReceived     根据优惠券id和手机号验证是否已领取过该优惠券
     * @param $record_id
     * @param $tel
     */
    public function checkHadReceived($record_id,$tel){
        $cardDb = new CardModel();
        return $cardDb->checkHadReceived($record_id,$tel);
    }


    /**
     * getReceiveCountByid      更具订单id获取订单的领用数量
     * @param $cardid
     * @return mixed
     */
    public function getReceiveCountByid($cardid){
        $cardDb = new CardModel();
        return $cardDb->getReceiveCountByid($cardid);
    }


    /**
     * checkOnly 验证优惠券编号的唯一性
     * @param $cardNo
     */
    public function checkOnly($cardNo){
        $cardDb = new CardModel();
        return $cardDb->checkOnly($cardNo);
    }

    /**
     * addReceiveCardLog   添加领用礼券记录
     * @param $tel    手机号
     * @param $cardid   礼券id
     * @param $cardNo   礼券编号
     */
    public function addReceiveCardLog($tel,$cardid,$cardNo,$xiaoqu){
        $cardDb = new CardModel();
        return $cardDb->addReceiveCardLog($tel,$cardid,$cardNo,$xiaoqu);
    }


    /**
     * 添加关联
     * @param [type] $data [description]
     */
    public function addAllRelated($companyData){
        $cardDb = new CardModel();
        return $cardDb->addAllRelated($companyData);
    }


    /**
     * getCompanySafeTelByComId 获取装修公司安全手机号
     * @param $comid
     */
    public function getCompanySafeTelByComId($comid){
        $companyDb = new CompanyModel();
        return $companyDb->getCompanySafeTelByComId($comid);
    }


    /**
     * signLiangFang  量房操作
     * @param $map
     * @param $savedata
     */
    public function signLiangFang($map,$savedata){
        $companyDb = new CompanyModel();
        return $companyDb->signLiangFang($map,$savedata);
    }




}