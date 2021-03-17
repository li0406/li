<?php
/**
 * 装修公司礼券操作
 */
namespace Common\Model\Logic;

use Common\Model\Db\CardModel;
use Think\Exception;

class CardLogicModel
{
    /**
     * addSpecialCard 添加专用礼券
     * @param $postdata
     * @return bool
     */
    public function addSpecialCard($postdata){
        $carddata = [];
        $carddata['name'] = $postdata['tname']; // 礼券名称
        $carddata['type'] = 2; // 2 表示专用礼券
        $carddata['active_type'] = $postdata['active-style-item'];
        if($carddata['active_type'] == 1){
            $carddata['money1'] = $postdata['cals_1']?$postdata['cals_1']:0; //满足优惠的金额
            $carddata['money2'] = $postdata['cal_1']; // 优惠金额
        }else{
            $carddata['money3'] = $postdata['cals_2']?$postdata['cals_2']:0;
            $carddata['gift'] = $postdata['cal_2'];
        }
        // $carddata['service'] = $postdata['jc']?$postdata['jc']:''; //使用条件
        $carddata['rule'] = $postdata['rule']?$postdata['rule']:''; //礼券使用规则
        $carddata['module'] = $postdata['modul']; //礼券模板类型
        $carddata['add_time'] = time();
        $carddata['update_time'] = time();
        //添加到qz_card 表
        $cardid = D("Common/Db/Card")->addSpecialCard($carddata);  //添加并返回id
        if($cardid){
            //添加数据到qz_card_com表
            $cardcom = [];
            $cardcom['card_id'] = $cardid;
            $cardcom['com_id']= session('u_userInfo.id');
            $cardcom['add_time'] = time();
            $cardcom_id = D("Common/Db/Card")->addCardCom($cardcom);  //添加并返回id

            //添加数据到qz_card_com_record表中
            $recorddata = [];
            $recorddata['card_com_id'] = $cardcom_id;
            $recorddata['activity_start'] = strtotime($postdata['active_start']);  //活动开始时间
            $recorddata['activity_end'] = strtotime($postdata['active_end']);  //活动结束时间
            $recorddata['start'] = strtotime($postdata['valid_start']);  //有效开始时间
            $recorddata['end'] = strtotime($postdata['valid_end']);  //有效结束时间
            $recorddata['amount'] = $postdata['tnum']; //发放数量
            $recorddata['apply_time'] = time(); //公司申请时间
            D("Common/Db/Card")->addCardComRecord($recorddata);  //添加并返回id
            return true;
        }else{
            return false;
        }
    }


    /**
     * editSpecialCard 重新申请专用礼券
     * @param $postdata
     * @return bool
     */
    public function editSpecialCard($postdata){
        $carddata = [];
        $carddata['id'] = $postdata['id']; //礼券id
        $carddata['name'] = $postdata['tname']; // 礼券名称
        $carddata['active_type'] = $postdata['active-style-item'];
        if( $carddata['active_type']  == 1){
            $carddata['money1'] = $postdata['cals_1']?$postdata['cals_1']:0; //满足优惠的金额
            $carddata['money2'] = $postdata['cal_1']; // 优惠金额
        }else{
            $carddata['money3'] = $postdata['cals_2']?$postdata['cals_2']:0;
            $carddata['gift'] = $postdata['cal_2'];
        }
        // $carddata['service'] = $postdata['jc']?$postdata['jc']:''; //使用条件
        $carddata['rule'] = $postdata['rule']; //礼券使用规则
        $carddata['module'] = $postdata['modul']; //礼券模板类型
        $carddata['update_time'] = time();

        //添加到qz_card 表
        $cardid = D("Common/Db/Card")->saveSpecialCard($carddata);  //添加并返回id
        if($cardid){
            //获取qz_card_com_record表的id
            $map = [];
            $map['card_id'] = array('EQ',$postdata['id']);
            $map['com_id'] = array('EQ',session('u_userInfo.id'));
            $cardrecordid = D("Common/Db/Card")->getCardComRecordIdByCardId($map);
            if($cardrecordid){  //可修改
                //修改qz_card_com_record表中的礼券信息
                $recorddata = [];
                $recorddata['id'] = $cardrecordid;
                $recorddata['activity_start'] = strtotime($postdata['active_start']);  //活动开始时间
                $recorddata['activity_end'] = strtotime($postdata['active_end']);  //活动结束时间
                $recorddata['start'] = strtotime($postdata['valid_start']);  //有效开始时间
                $recorddata['end'] = strtotime($postdata['valid_end']);  //有效结束时间
                $recorddata['amount'] = $postdata['tnum']; //发放数量
                $recorddata['check'] = 1;   //审核状态改为待审核
                $recorddata['check_userid'] = '';
                $recorddata['check_username'] = '';
                $recorddata['check_reason'] = '';
                $recorddata['apply_state'] = 2; //改为下架？
                $recorddata['update_time'] = time();
                D("Common/Db/Card")->saveCardComRecord($recorddata);  //修改专用券信息
            }
            return true;
        }else{
            return false;
        }
    }


    /**
     * getSpecialCardByIdCount  根据条件获取当前装修公司的专用礼券的总数量
     * @param $comid
     * @param $getdata
     * @return int
     */
    public function  getSpecialCardByIdCount($comid,$getdata){
        $map = [];
        $map['b.com_id'] = array('eq',$comid);
        $map['a.type'] = array('eq',2); //2表示专用礼券
        if($getdata){
            //礼券状态  ：1表示可用，2表示未生效，3表示失效,4表示下架
            if($getdata['cardstatus']){
                if($getdata['cardstatus'] == 1){
                    $map['c.start'] = array('ELT',time());
                    $map['c.end'] = array('EGT',time());
                    $map['c.apply_state'] = array('EQ',1);
                }elseif($getdata['cardstatus'] == 2){
                    $map['c.start'] = array('EGT',time());
                }elseif($getdata['cardstatus'] == 3){
                    $map['c.end'] = array('ELT',time());
                }elseif($getdata['cardstatus'] == 4){
                    $map['c.start'] = array('ELT',time());
                    $map['c.end'] = array('EGT',time());
                    $map['c.apply_state'] = array('EQ',2);
                }
            }
            //审核状态 ： 1表示待审核。2表示审核通过， 3表示审核未通过，4表示撤回
            if($getdata['checkstatus']){
                $map['c.check'] = array('EQ',$getdata['checkstatus']);
            }
        }
        $count = D("Common/Db/Card")->getSpecialCardByIdCount($map);
        return $count?$count:0;
    }


    /**
     * getSpecialCardById  根据条件获取当前装修公司的专用礼券
     * @param $comid
     */
    public function getSpecialCardById($comid,$getdata,$pageIndex = 1,$pageCount = 10){
        if(!$comid){
            return false;
        }else{
            $map = [];
            $map['b.com_id'] = array('eq',$comid);
            $map['a.type'] = array('eq',2); //2表示专用礼券
            if($getdata){
                //礼券状态  ：1表示可用，2表示未生效，3表示失效,4表示下架
                if($getdata['cardstatus']){
                    if($getdata['cardstatus'] == 1){
                        $map['c.start'] = array('elt',time());
                        $map['c.end'] = array('egt',time());
                        $map['c.apply_state'] = array('EQ',1);
                    }elseif($getdata['cardstatus'] == 2){
                        $map['c.start'] = array('egt',time());
                    }elseif($getdata['cardstatus'] == 3){
                        $map['c.end'] = array('elt',time());
                    }elseif($getdata['cardstatus'] == 4){
                        $map['c.start'] = array('ELT',time());
                        $map['c.end'] = array('EGT',time());
                        $map['c.apply_state'] = array('EQ',2);
                    }
                }
                //审核状态 ： 1表示待审核。2表示审核通过， 3表示审核未通过，4表示撤回
                if($getdata['checkstatus']){
                    $map['c.check'] = array('EQ',$getdata['checkstatus']);
                }
            }
            $list = D("Common/Db/Card")->getSpecialCardById($map,($pageIndex-1)*$pageCount,$pageCount);
            if($list){
                foreach ($list as $key => $value){
                    if($value['active_type'] == 1){
                        if($value['money1'] && $value['money1'] >0){
                            $list[$key]['realt_discount'] = '满'.(int)$value['money1'].'减'.(int)$value['money2'].'元';
                        }else{
                            $list[$key]['realt_discount'] = '立减'.(int)$value['money2'].'元';
                        }
                    }else{
                        $list[$key]['realt_discount'] = '满'.(int)$value['money3'].'送'.$value['gift'];
                    }

                    //礼券状态： 1表示未开始，2表示可用，3表示下架，4表示失效
                    if($value['start'] > time()){
                        $list[$key]['card_status'] = 1;
                    }elseif($value['start'] < time() && $value['end'] >  time()){
                        $list[$key]['card_status'] = 2;
                        if($value['apply_state'] == 2){
                            $list[$key]['card_status'] = 3;
                        }
                    }elseif($value['end'] < time()){
                        $list[$key]['card_status'] = 4;
                    }
                    //操作状态显示   //1表示重新申请 2表示撤回 3表示下架操作  4表示上架操作
                    if($value['check'] == 3 || $value['check'] == 4){
                        $list[$key]['action_status'] = 1;
                    }elseif($value['check'] == 1){
                        $list[$key]['action_status'] = 2;
                    }elseif ($value['check'] == 2 && $list[$key]['card_status'] == 2){  //上架状态
                        $list[$key]['action_status'] = 3;
                    }elseif($value['check'] == 2 && $list[$key]['card_status'] == 3){  //下架状态
                        $list[$key]['action_status'] = 4; // 显示 上架
                    }elseif($value['check'] == 2 && $list[$key]['card_status'] == 1){   //未生效状态
                        if($value['apply_state'] == 1){
                            $list[$key]['action_status'] = 3;
                        }else{
                            $list[$key]['action_status'] = 4;
                        }
                    }

                    //获取领取数量
                    $list[$key]['receive_num'] = D("Common/Db/Card")->getReceiveCardNum($value['recordid']);
                }
            }
            return $list;
        }

    }

    /**
     * 根据专用券id获取专用券信息
     * @param $cardId
     */
    public function getSpecialCardInfoById($cardId){
        if($cardId){
            $map = [];
            $map['a.id'] = $cardId;
            $map['a.type'] = 2;
            $map['b.com_id'] = session('u_userInfo.id');
            $info = D("Common/Db/Card")->getSpecialCardInfo($map);  //获取专用券信息
            // dump($info);die;
            if($info){
                $info['money1'] = $info['money1'] ? intval($info['money1']) : 0;
                $info['money2'] = $info['money2'] ? intval($info['money2']) : 0;
                $info['money3'] = $info['money3'] ? intval($info['money3']) : 0;
                if($info['active_type'] == 2){
                    $info['activetext'] = '满'.$info['money3'].'元'.'送'.$info['gift'];
                }elseif($info['active_type'] == 1 && $info['money1']){
                    $info['activetext'] = '满'.$info['money1'].'元'.'减'.$info['money2'].'元';
                }else{
                    $info['activetext'] = '立减'.$info['money2'].'元';
                }

//                $info['activity_start'] = $info['activity_start'] ? date('Y-m-d H:i:s',$info['activity_start']) : '';
//                $info['activity_end'] = $info['activity_end'] ? date('Y-m-d H:i:s',$info['activity_end']) : '';
//                $info['start'] = $info['start'] ? date('Y-m-d H:i:s',$info['start']) : '';
//                $info['end'] = $info['end'] ? date('Y-m-d H:i:s',$info['end']) : '';
            }
            return $info;
        }else{
            return array();
        }
    }

    /**
     * 通用列表
     * @param $where
     * @return array
     */
    public function common($where){
        $cardDb = new CardModel();
        $count = $cardDb->getCommonCount($where);
        $pageIndex = I("get.p") === ""?1:I("get.p");//如果没有传页码 就是第一页
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = 10;//每页分10个
        if($count>0){
            import('Library.Org.Page.Page');
            $p = new \Page($pageIndex,$pageCount,$count,['prev', 'next']);
            $show = $p->show();
            $start=($p->pageIndex-1)*$pageCount;//起始页
            $list = $cardDb->getCommonList($where,$start,$pageCount);
            return ['list' => $list, 'page' => $show];
        }
    }

    /**
     * regressionAction    撤回操作
     * @param $data
     */
    public function regressionAction($data){
        $map = [];
        $map['card_id'] = $data['cardid'];
        $map['com_id'] = session('u_userInfo.id');
        $cardrecordid = D("Common/Db/Card")->getCardComRecordIdByCardId($map);
        if($cardrecordid){
            $newdata = [];
            $newdata['id'] = $cardrecordid;
            $newdata['check'] = 4;
            $resturn = D("Common/Db/Card")->saveCardComRecord($newdata);  //修改专用券审核状态为撤回
            if($resturn === false){
                return false;
            }else{
                return true;
            }
        }else{
            return false;
        }
    }


    /**
     * 上架/下架专用券
     * @param $data
     * @return bools
     */
    public function showOrHidenCard($data){
        $map = [];
        $map['card_id'] = $data['cardid'];
        $map['com_id'] = session('u_userInfo.id');
        $cardrecordid = D("Common/Db/Card")->getCardComRecordIdByCardId($map);
        if($cardrecordid){
            $newdata = [];
            $newdata['id'] = $cardrecordid;
            if($data['type'] == 1){  //从下架转变为上架
                $newdata['apply_state'] = 1;
            }else{ //从上架转变为下架
                $newdata['apply_state'] = 2;
            }
            $resturn = D("Common/Db/Card")->saveCardComRecord($newdata);  //修改专用券申请状态
            if($resturn === false){
                return false;
            }else{
                return true;
            }
        }else{
            return false;
        }
    }


    /**
     * getReceiveCardsCount 根据条件获取获取领用券的的总数
     * @param $comid
     * @param $getdata
     * @return int
     */
    public function getReceiveCardsCount($comid,$getdata){
        $map = [];
        if($getdata['cardcode']){  //如有礼券编号，增加查询条件
            $map['d.card_number'] = array('like','%'.$getdata['cardcode'].'%');
        }
        if($getdata['yz_mobile']){ //如有手机号， 增加查询条件
            $map['d.tel'] = array('like','%'.$getdata['yz_mobile'].'%');
        }
        if($getdata['id']){
            $map['a.id'] = array('EQ',$getdata['id']);
        }
        $count = D("Common/Db/Card")->getReceiveCardsCount($comid,$map);
        return $count?$count:0;
    }

    /**
     * getReceiveCards   根据条件查询领取礼券列表
     * @param $comid
     * @param $getdata
     * @param int $pageIndex
     * @param int $pageCount
     */
    public function getReceiveCards($comid,$getdata,$pageIndex = 1,$pageCount = 10){
        $map = [];
        if($getdata['cardcode']){  //如有礼券编号，增加查询条件
            $map['d.card_number'] = array('like','%'.$getdata['cardcode'].'%');
        }
        if($getdata['yz_mobile']){ //如有手机号， 增加查询条件
            $map['d.tel'] = array('like','%'.$getdata['yz_mobile'].'%');
        }
        if($getdata['id']){
            $map['a.id'] = array('EQ',$getdata['id']);
        }
        $map['b.com_id'] = $comid;
        $list = array();
        $list = D("Common/Db/Card")->getReceiveCards($map,($pageIndex-1)*$pageCount,$pageCount);
        return $list;
    }

/**
     * 可领用列表
     */
    public function receivegift(){
        $cardDb = new CardModel();
        $count = $cardDb->getCommonUseCount();
        $pageIndex = I("get.p") === ""?1:I("get.p");//如果没有传页码 就是第一页
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = 10;//每页分10个
        if($count>0){
            import('Library.Org.Page.Page');
            $p = new \Page($pageIndex,$pageCount,$count,['prev', 'next']);
            $show = $p->show();
            $start=($p->pageIndex-1)*$pageCount;//起始页
            $list = $cardDb->getCommonUseList($start,$pageCount);
            return ['list' => $list, 'page' => $show];
        }
    }

    /**
     * 获取通用礼券信息
     * @param $id
     */
    public function getCardInfo($id){
        $cardDb = new CardModel();
        return $cardDb->getCardInfo($id);
    }


    /**
     * 添加公司领用通用券信息
     * @param $data
     * @return mixed
     */
    public function addCardCom($cardid,$comid,$dataRecord){
        $cardDb = new CardModel();
        //是否有该公司礼券信息
        $exist = $cardDb->existComCard($cardid,$comid);
        if(empty($exist['id'])){
            //添加公司礼券
            $exist['id'] = $cardDb->addCardCom(['card_id'=>$cardid,'com_id'=>$comid,'add_time'=>time()]);
        }
        //添加公司礼券领用
        $dataRecord['card_com_id'] = $exist['id'];
        $dataRecord['apply_time'] = time();
        return $cardDb->addCardComRecord($dataRecord);
    }


    /**
     * 获取通用券详细信息
     */
    public function getCommonInfo($id){
        $cardDb = new CardModel();
        $result = $cardDb->getCommonInfo($id);
        if(!empty($result['apply_time'])&&isset($result['apply_time'])){
            $result['add_time'] = $result['apply_time'];
        }
        return $result;
    }

    /**
     * 领用查询
     * @param $where
     * @return array
     */
    public function getcarduser($where){
        $cardDb = new CardModel();
        $count = $cardDb->getcardusercount($where);
        $pageIndex = I("get.p") === ""?1:I("get.p");//如果没有传页码 就是第一页
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = 10;//每页分10个
        if($count>0){
            import('Library.Org.Page.Page');
            $p = new \Page($pageIndex,$pageCount,$count,['prev', 'next']);
            $show = $p->show();
            $start=($p->pageIndex-1)*$pageCount;//起始页
            $list = $cardDb->getcarduserlist($where,$start,$pageCount);
            return ['list' => $list, 'page' => $show];
        }
    }

    public function changeRecord($cardid,$status){
        $cardDb = new CardModel();
        if($status == 4){
            //审核状态改为撤回
            $data['check'] = $status;
        }else{
            //更改申请状态
            $data['apply_state'] = $status;
        }
        return $cardDb->changeRecord($data,$cardid);
    }


}
