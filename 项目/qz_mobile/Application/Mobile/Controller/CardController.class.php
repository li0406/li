<?php
/**
 *卡券领取
 */
namespace Mobile\Controller;
use Common\Model\Service\SmsServiceModel;
use Mobile\Common\Controller\MobileBaseController;
use Common\Enums\ApiConfig;
use Common\Controller\SmsController;
class CardController extends MobileBaseController{
    public function _initialize(){
        parent::_initialize();
        $cityinfo = session('m_cityInfo');//定位的城市信息
        //获取通用优惠券
        $tongyonglist = D('Card')->getCurrencyCardList($cityinfo['id']);
        if(count($tongyonglist) < 10){
            $tongyonglist = $this->getMoreCompanyByCityId($tongyonglist,$cityinfo['id']);
        }

        //获取可用通用优惠券数量
        $canuse = D('Card')->getOneTongyongCard();
        if($canuse>0){
            $showtongyong  = 1; //1表示显示通用模块
        }else{
            $showtongyong = 2;  //2表示不显示通用模块
        }

        $this->assign('showtongyong',$showtongyong);

        //获取商户专属优惠券
        $specialCardList = $this->getSpecialCardList($cityinfo['id']);
        $this->assign('bm',$cityinfo['bm']);
        $this->assign('tongyonglist',$tongyonglist);
        $this->assign('specialcardlist',$specialCardList);

    }
    //登录
    public function login()
    {
        session('user_card_tel',null);
        if($_POST){
            session('user_card_tel',null);
            $tel = $_POST['tel'];
            $getreturn = SmsController::verifysmscodeNew();//校验验证码
            // $getreturn['status'] = 1;
            if($getreturn['status'] == 1){
                $saveinfo['tel'] = $tel;
            }else{
                $this->ajaxReturn(['error_code'=>ApiConfig::VERIFY_CODE_ERROR,'error_msg'=>$getreturn['info']]);
            }
            //获取是否有订单
            $list = D('Card')->getOrdersCountByTel($tel);
            if($list){  //表示有订单
                $saveinfo['hadorder'] = 1;
                session('user_card_tel',$saveinfo,60*30);
                $this->ajaxReturn(['error_code'=>ApiConfig::REQUEST_SUCCESS,'error_msg'=>'登陆成功','data'=>$list]);
            }else{  //表示没有订单
                $saveinfo['hadorder'] = 0;
                session('user_card_tel',$saveinfo,60*30);
                $this->ajaxReturn(['error_code'=>ApiConfig::USER_NO_ORDER,'error_msg'=>'登陆成功']);
            }
        }
        $this->display();
    }

    //申请
    public function apply()
    {
        //未登陆则跳转到活动登陆页面
        if(!session('user_card_tel')){
            $this->redirect('cardlogin');
        }

        //获取该城市第一个区，用于显示默认城市
        $cityinfo['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign('cityinfo',$cityinfo);

        $tel =  session('user_card_tel.tel');
        $this->assign('tel',$tel);
        $this->display();
    }


     //优惠券进店
     public function couponin()
     {
         if(I('get.comid')){
             //通用券？
             $comid = I('get.comid');  //公司id
             //根据公司id获取通用券
             $tongyonginfo = D('Card')->getTongYongCardByComid($comid);
             if(!$tongyonginfo){
                 $tongyonginfo = D('Card')->getTongYongCardByJiaComid($comid);
             }
             $tongyonginfo['money1'] = $tongyonginfo['money1'] ?(int)$tongyonginfo['money1']:0;
             $tongyonginfo['money2'] = $tongyonginfo['money2'] ?(int)$tongyonginfo['money2']:0;
             if($tongyonginfo['money1'] > 0){
                 $tongyonginfo['name'] = '满'.$tongyonginfo['money1'].'元可用';
             }else{
                 $tongyonginfo['name'] = '立减'.$tongyonginfo['money2'].'元';
             }
             $this->assign('info',$tongyonginfo);
         }elseif(I('get.cardid')){
             //根据优惠券id获取优惠券信息
             //c.id
             $cardid = I('get.cardid');  //优惠券id
             $specialinfo = D('Card')->getCardinfoByRecordId($cardid);
             $specialinfo['money1'] = $specialinfo['money1']?(int)$specialinfo['money1']:0 ;
             $specialinfo['money2'] = $specialinfo['money2']?(int)$specialinfo['money2']:0;
             $specialinfo['money3'] = $specialinfo['money3']?(int)$specialinfo['money3']:0;
             if($specialinfo['active_type'] == 2) {
                 $specialinfo['name'] = '满' . $specialinfo['money3'] . '元可领';
             }else{
                 if($specialinfo['money1'] > 0){
                     $specialinfo['name'] = '满'.$specialinfo['money1'].'元可用';
                 }else{
                     $specialinfo['name'] = '立减'.$specialinfo['money2'].'元';
                 }
             }
             $this->assign('info',$specialinfo);
         }else{
             $this->_error();
         }
         $this->display();
     }
     //优惠券领取
     public function coupontake()
     {
         $cardid = I('get.cardid');
         $orderid = I('get.orderid');
         //获取优惠券信息
         $return = D('Card')->getCardInfoAndMoreByCardid($cardid);
         //查询该公司是否已量房
         $hadliangfang = D('Card')->searchLiangFangStatus(session('user_card_tel.tel'),$return['com_id']);
         if($hadliangfang == 1 ){  //1表示已量房
             $return['hadliangfang'] = 1; //1表示已量房
         }else{     //未量房
             $return['hadliangfang'] = 2; //2表示未量房
         }
         if($return['tel']){
             $return['hadreceive'] = 1;
             $return['receiveall'] = 0; //0表示优惠券未被领取完
         }else{
             $return['hadreceive'] = 0;
             //判断是否已领取结束
             if($return['amount'] <= $return['usenum']){
                 $return['receiveall'] = 1; //1表示优惠券已被领取完
             }else{
                 $return['receiveall'] = 0; //0表示优惠券未被领取完
             }
         }

         if($return['active_type'] == 2){
             $return['name'] = '满' . $return['money3'] . '元可领';
         }else{
             if($return['money1'] > 0){
                 $return['name'] = '满'.$return['money1'].'元可用';
             }else{
                 $return['name'] = '立减'.$return['money2'].'元';
             }

         }
         $this->assign('orderid',$orderid ? $orderid : '');

         $this->assign('info',$return);
         $this->display();
     }
     //优惠券领取成功
     public function coupontsuccess(){
         //未登陆则跳转到活动登陆页面
         if(!session('user_card_tel')){
             $this->redirect('/cardlogin/');
         }

        if(!I('get.id')){
            $this->_error();   //没有优惠券领取记录id，跳404页面
        }
        $receiveid = I('get.id');
        //根据领取记录的id，获取优惠券的recordid
         $recordid = D('Card')->getRecordidByReceiveId($receiveid);

         //根据recordid 获取优惠券信息
         $cardinfo = D('Card')->getCardinfoByRecordId($recordid['record_id']);
         $cardinfo['money1'] = $cardinfo['money1'] ? (int)$cardinfo['money1'] : 0;  //金额取整
         $cardinfo['money2'] = $cardinfo['money2'] ? (int)$cardinfo['money2'] : 0;  //金额取整
         $cardinfo['money3'] = $cardinfo['money3'] ? (int)$cardinfo['money3'] : 0;  //金额取整

         $this->assign('recordinfo',$recordid);  //领取的优惠券信息
         $this->assign('cardinfo',$cardinfo);
         $this->display();
     }
     //我的卡券包
     public function cardbag()
     {
         //未登陆则跳转到活动登陆页面
         if(!session('user_card_tel')){
             $this->redirect('/cardlogin/');
         }

         $tel = session('user_card_tel.tel');
         $hadspecialcard = 0; //0表示没有可用优惠券
         $hadouttimecard = 0;  //0表示没有过期的优惠券
         //根据手机号获取装修公司列表
         $comlist = D('Card')->getCompanyListByTel($tel);
         foreach ($comlist as $key => $val){
             $comlist[$key]['cardlist'] = D('Card')->getReceiveCardByCompanyId($val['com_id'],$tel,1);
             $comlist[$key]['outtimecardlist'] = D('Card')->getReceiveCardByCompanyId($val['com_id'],$tel,2);
             if($comlist[$key]['cardlist'] && $hadspecialcard == 0){
                 $hadspecialcard = 1;
             }
             if($hadouttimecard == 0 && $comlist[$key]['outtimecardlist']){
                 $hadouttimecard = 1;
             }
         }
//         dump($comlist[0]);die;
         $this->assign('list',$comlist);
         $this->assign('hadspecialcard',$hadspecialcard);
         $this->assign('hadouttimecard',$hadouttimecard);
         $this->display();
     }

     //优惠券详情
     public function coupondetails()
     {
//         if(!I('get.id')){
//             $this->_error();   //没有优惠券领取记录id，跳404页面
//         }
         $receiveid = I('get.id');
         //根据领取记录的id，获取优惠券的recordid
         $recordid = D('Card')->getRecordidByReceiveId($receiveid);

         //根据recordid 获取优惠券信息
         $cardinfo = D('Card')->getCardinfoByRecordId($recordid['record_id']);
         $cardinfo['money1'] = $cardinfo['money1'] ? (int)$cardinfo['money1'] : 0;  //金额取整
         $cardinfo['money2'] = $cardinfo['money2'] ? (int)$cardinfo['money2'] : 0;  //金额取整
         $cardinfo['money3'] = $cardinfo['money3'] ? (int)$cardinfo['money3'] : 0;  //金额取整

         $this->assign('recordinfo',$recordid);  //领取的优惠券信息
         $this->assign('cardinfo',$cardinfo);
         $this->display();
     }

      //量房待分配页面
      public function waitefenpei()
      {
          $this->display();
      }

      //量房已分配页面
      public function coupoinfenpei()
      {
          if(!session('user_card_tel')){
              $this->redirect('/cardlogin/');
          }

          $tel = session('user_card_tel');
          $list = D('Card')->getUserOrders($tel);
         // dump($list);die;

          $this->assign('list',$list);
          $this->assign('countlist',count($list));
          $this->assign('tel',$tel['tel']);
          $this->display();
      }

      //没有优惠券领取
      public function nocoupoin()
      {
          $this->display();
      }


    /**
     * getSpecialCardList   根据城市id获取专用礼券
     * @param $cityid
     * @return mixed
     */
    private function getSpecialCardList($cityid){
        $specialCardList = D('Card')->getSpecialCardList($cityid);
        if(!$specialCardList){
            $specialCardList = D('Card')->getSpecialCardList();
        }
        foreach ($specialCardList as $key => $val){
            $specialCardList[$key]['money1'] = $val['money1'] ? (int)$val['money1'] : 0;
            $specialCardList[$key]['money2'] = $val['money2'] ? (int)$val['money2'] : 0;
            $specialCardList[$key]['money3'] = $val['money3'] ? (int)$val['money3'] : 0;
            if($val['active_type'] == 2){
                $specialCardList[$key]['subtext'] = '满'.$specialCardList[$key]['money3'].'元可领'.$val['gift'];
            }else{
                if($specialCardList[$key]['money1'] > 0 ){
                    $specialCardList[$key]['subtext'] = '满'.$specialCardList[$key]['money1'].'元减'.$specialCardList[$key]['money2'].'元';
                }else{
                    $specialCardList[$key]['subtext'] = '立减'.$specialCardList[$key]['money2'].'元';
                }
            }
        }
        return $specialCardList;
    }



      //根据订单获取装装修公司,以及优惠券
    public function getSpecialCardByOrderId(){
        $orderid = $_POST['orderid'];
        if(!orderid){
            $this->ajaxReturn(['error_code'=>ApiConfig::LOSS_ORDER_ID,'error_msg'=>'缺少订单ID']);
        }else{
            $return = array();
            //获取装修公司
            $com_list = D('Card')->getCompanyListByOrderId($orderid);
            if(!$com_list){
                $this->ajaxReturn(['error_code'=>ApiConfig::NO_LIANGFANG_COMPANY,'error_msg'=>'未分配装修公司']);
            }
            $hadspecial = 0;  //0表示没有可领取的装修优惠
            foreach ($com_list as $key =>$val){
                $com_list[$key]['cardlist'] = D('Card')->getSpecialCardByCompanyId($val['comid']);
                if(empty($com_list[$key]['cardlist'])){
                    $com_list[$key]['hadcard'] = 2; //2表示该公司无可用优惠券
                }else{
                    $com_list[$key]['hadcard'] = 1; //1表示该公司有可用优惠券
                    $hadspecial = 1;
                }
            }
            $return['list'] = $com_list;
           // dump($com_list[0]);die;
            $html = $this->getContentOneHtml($com_list);
            $return['gethtml'] = $html ? $html : '';
            $return['hadspecialcard'] = $hadspecial;
            $this->ajaxReturn(['error_code'=>ApiConfig::REQUEST_SUCCESS,'error_msg'=>'请求成功','data'=>$return]);
        }
    }

    /**
     * getContentOneHtml  装修公司，优惠券列表
     * @param $com_list
     * @return string
     */
    public function getContentOneHtml($com_list){
        $this->assign('com_list',$com_list);
        $this->assign('session_tel',session('user_card_tel.tel'));
        $html = $this->fetch('Card:contentone');
        return $html;
    }


    /**
     * receiveCard      领用优惠券
     */
    public function receiveCard(){
        if(!$_POST){
            $this->ajaxReturn(['error_code'=>ApiConfig::LOSE_MISS_PARAMETERS,'error_msg'=>'缺少参数']);
        }
        if(empty(session('user_card_tel.tel'))){
            $this->ajaxReturn(['error_code'=>ApiConfig::NO_LOGIN_FOR_CARDLIST,'error_msg'=>'登陆状态有误，请重新登陆']);
        }
        $cardNo = 0;
        $cardid = $_POST['cardid'];
        $comid = $_POST['comid'];
        $orderid = $_POST['orderid'];
        $tel = session('user_card_tel.tel');
        $cardinfo = D('Card')->getCardinfoByRecordId($cardid);  //优惠券信息

        //获取小区信息
        $cardinfo['xiaoqu'] = D('Orders')->getXiaoQuByOrderId($orderid);

        //先查询是否已经领用过了
        $hadreceived = D('Card')->checkHadReceived($cardinfo['record_id'],$tel);

        if($hadreceived){
            $this->ajaxReturn(['error_code'=>ApiConfig::ERROR_FOR_RECEIVE_AGAIN,'error_msg'=>'无法重复领取该优惠券!']);
        }

        //再查询优惠券是否全部领完
        $receivecount = D('Card')->getReceiveCountByid($cardinfo['record_id']);
        if($receivecount == $cardinfo['amount']){
            $this->ajaxReturn(['error_code'=>ApiConfig::ERROR_FOR_CARD_OVER,'error_msg'=>'优惠券已领完！']);
        }


        while(1){
            $cardNo = getRandom(10);
            $weiyi = D('Card')->checkOnly($cardNo);
            //查询是否是唯一的
            if($weiyi){
                break;
            }
        }
        $return = D('Card')->addReceiveCardLog($tel,$cardid,$cardNo,$cardinfo['xiaoqu']);
        if($return){  //表示成功
            $type = 1;
            $cityinfo = session('m_cityInfo');//定位的城市信息
            $cs = $cityinfo['id'];
            if($cardinfo['name']){
                $info['name'] = $cardinfo['name'];
            }
            $info['card_number'] = $cardNo;
            $info['tel'] = $tel;
            $info['xiaoqu'] = $cardinfo['xiaoqu'];
            $info['add_time'] = time();
            $this->sendMessage($info,$type,$cs,$comid);

            //发送短信给业主
            $this->sendSmsForGetCard(session('user_card_tel.tel'),1,$cardinfo['name'],$cardinfo['jc'],$cardNo);
            //获取装修公司的安全手机号
            $comsafetel = D('Card')->getCompanySafeTelByComId($comid);
            $this->sendSmsForGetCard($comsafetel,2,$cardinfo['name'],$cardinfo['jc'],$cardNo);//发送 短信给装修公司安全手机号

            $receivecardinfo = [];
            $receivecardinfo['id'] = $return;
            $this->ajaxReturn(['error_code'=>ApiConfig::REQUEST_SUCCESS,'error_msg'=>'领用成功','data'=>$receivecardinfo]);

        }else{   // 表示领用失败
            $this->ajaxReturn(['error_code'=>ApiConfig::ERROR_TO_ADD_MYSQL,'error_msg'=>'领用失败']);
        }
    }


    public function sendSmsForGetCard($tel,$type = 1,$cardName,$comJc,$cardNo){
        if($type == 1){
            // 1表示发送给用户的短信模板
            $template_id = "201908271013";
            $param= [$tel,$cardName];
        }else{
            //2表示发送给客户的短信模板
            $template_id = "201908271012";
            $param= [$comJc,$cardName,$cardNo];
        }

        //发送短信
        $smsService = new SmsServiceModel();
        //参数必须是string，否则无法传参
        $smsService->sendSms($template_id,$tel,$param);
    }

    /**
     * 消息通知
     * @param $info 消息数组
     * @param $type 模板类型
     * @param string $cs 城市
     * @param int $cityid 公司号
     * @return mixed
     */
    public function sendMessage($info,$type,$cs='',$comid=0)
    {
        $html = '';

        $title = '用户领取';
        $html .= '礼券名称: '.$info['name'].'<br><br>';
        $html .= '礼券编号: '.$info['card_number'].'<br><br>';
        $html .= '业主手机号: '.$info['tel'].'<br><br>';
        $html .= '小区名称: '.$info['xiaoqu'].'<br><br>';
        $html .= '领取时间: '.date('Y-m-d H:i:s',$info['add_time']);

        $notice['title'] = $title;
        $notice['type'] = '2';
        $notice['cs'] = $cs;
        $notice['text'] = htmlspecialchars_decode($html);
        $notice['username'] = '系统';
        $notice['time'] = time();
        $noticle_id = M("user_system_notice")->add($notice);
        if(($type == 5) || ($type == 6) ){
            $companydb = new CompanyModel();
            $companyids = $companydb->getCompany();
            foreach($companyids as $key=>$val){
                $companyData[$key]['uid'] = $val['id'];
                $companyData[$key]['noticle_id'] = $noticle_id;
            }
            return D("Card")->addAllRelated($companyData);
        }else{
            return M("user_notice_related")->add(array('noticle_id'=>$noticle_id,'uid'=>$comid));
        }
    }

    /**
     * signLiangFang    标记量房
     */
    public function signLiangFang(){
        $data = $_POST;
        if(!$data['orderid'] || !$data['comid']){
            $this->ajaxReturn(['error_code'=>ApiConfig::LOSE_MISS_PARAMETERS,'error_msg'=>'缺少参数']);
        }

        $map = [];
        $map = $data;
        $savedata['liangfang'] = 1;
        $savedata['yz_lf_time'] = time();
        $savedata['yz_channel'] = 3; //3表示移动端量房
        $return = D('Card')->signLiangFang($map,$savedata);
        if($return){
            $this->ajaxReturn(['error_code'=>ApiConfig::REQUEST_SUCCESS,'error_msg'=>'量房成功']);
        }else{
            $this->ajaxReturn(['error_code'=>ApiConfig::REQUEST_FAILL,'error_msg'=>'操作失败！']);
        }
    }

    /**
     * 根据礼券id获取礼券信息
     */
    public function getspecialcardinfobyid(){
        $cardid = I('get.cardid');
        $cardinfo = D('Card')->getCardinfoByRecordId($cardid);  //优惠券信息
        $cardinfo['money1'] = $cardinfo['money1'] ? (int)$cardinfo['money1'] : 0;
        $cardinfo['money2'] = $cardinfo['money2'] ? (int)$cardinfo['money2'] : 0;
        $cardinfo['money3'] = $cardinfo['money3'] ? (int)$cardinfo['money3'] : 0;
        if($cardinfo['active_type'] == 2){
            $cardinfo['name'] = '满'.$cardinfo['money3'].'元可领';
        }else{
            if($cardinfo['money1'] > 0){
                $cardinfo['name'] = '满'.$cardinfo['money1'].'元可用';
            }else{
                $cardinfo['name'] = '立减'.$cardinfo['money2'].'元';
            }
        }
        $cardinfo['start'] = date('Y.m.d',$cardinfo['start']);
        $cardinfo['end'] = date('Y.m.d',$cardinfo['end']);
        $this->ajaxReturn(['error_code'=>ApiConfig::REQUEST_SUCCESS,'error_msg'=>'请求成功','data'=>$cardinfo]);
    }


    /**
     * getMoreCompanyByCityId  根据城市id补齐列表
     * @param $cityid
     */
    private function getMoreCompanyByCityId($list,$cityid){
        $count = count($list);
        if($count >= 10){
            return $list;
        }else{    //用假会员数据补全
            $neednum = 10 - $count; //需要补全的数量
            $jiadata = D('Common/company')->getMoreCompanyData($neednum,$cityid);
            foreach ($jiadata as $key => $val) {
                $newdata = [];
                $newdata['id'] = $val['id'];
                $newdata['jc'] = $val['jc'];
                $newdata['logo'] = $val['logo'];
                $newdata['apply_time'] = '';
                $newdata['countcard'] = 1;
                array_push($list,$newdata);
            }
            $count = count($list);
            if($count >= 10){
                return $list;
            }else{    //假会员不够，用全国所有的有通用券的公司补全
                $neednum = 10 - $count; //需要补全的数量
                $jiadata = D('Card')->getCurrencyCardList($cityid,2,$neednum);
                foreach ($jiadata as $key => $val) {
                    array_push($list,$val);
                }
            }
            return $list;
        }
    }



}