<?php

//装修公司活动管理

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\CardLogicModel;

class CardController extends HomeBaseController{
    //商家领用状态
    private $seller_status = [
        4=>'未生效',
        7=>'已禁用',
        5=>'可用',
        6=>'失效',
        1=>'待确认',
        3=>'不通过'
    ];

    //通用券状态
    private $common_status = [
        1 => '启用',
        2 => '禁用',
    ];

    //业主礼券类型
    private $user_type = [
        1=> '通用',
        2=> '专用'
    ];

    //业主使用状态
    private $user_status = [
        1=> '未使用',
        2=> '已使用',
        3=> '已失效'
    ];

    //专用礼券审核状态
    private $special_status = [
        1=> '待审核',
        2=> '审核通过',
        3=> '不通过'
    ];

    //专用礼券列表
    public function special(){
        //获取列表内容
        $cardlogic = new CardLogicModel();
        $result = $cardlogic->special(I('get.'));
        //城市信息
        $citys = getUserCitys();
        $this->assign('status',$this->special_status);
        $this->assign('citys',$citys);
        $this->assign('list',$result['list']);
        $this->assign('page',$result['page']);
        $this->display();
    }

    //通用礼券列表
    public function common(){
        $cardlogic = new CardLogicModel();
        $result = $cardlogic->common(I('get.'));
//        var_dump($result['list']);

        $this->assign('status',$this->common_status);
        $this->assign('list',$result['list']);
        $this->assign('page',$result['page']);
        $this->display();
    }

    //商家领用查询
    public function seller(){
        $cardlogic = new CardLogicModel();
        $result = $cardlogic->seller(I('get.'));
        //城市信息
        $citys = getUserCitys();
        $this->assign('status',$this->seller_status);
        $this->assign('citys',$citys);
        $this->assign('list',$result['list']);
        $this->assign('page',$result['page']);
        $this->display();
    }

    //礼券领用查询
    public function user(){
        $cardlogic = new CardLogicModel();
        $result = $cardlogic->user(I('get.'));
        //城市信息
        $citys = getUserCitys();

        $this->assign('user_type',$this->user_type);
        $this->assign('user_status',$this->user_status);
        $this->assign('citys',$citys);
        $this->assign('list',$result['list']);
        $this->assign('page',$result['page']);
        $this->display();
    }

    //审核
    public function examine(){
        $cardlogic = new CardLogicModel();
        if($_POST){
            $id = trim(I('post.id'));
            $data["check"] = I('post.status');
            $data["check_reason"] = trim(I('post.reason'));
            $data['check_userid'] = session("uc_userinfo.id");
            $data['check_username'] = session("uc_userinfo.name");
            $result = $cardlogic->updateCardComRecord($data,$id,I('post.type'));
            if($result>0){
                $this->ajaxReturn(['status' => 0 , 'info' => '操作成功']);
            }else{
                $this->ajaxReturn(['status' => 1 , 'info' => '操作失败']);
            }
        }

        $info = $cardlogic->getSpecialInfo(I('get.id'),1);

        if(empty($info)){
            $this->_error('该礼券已审核');
        }
        $this->assign('info',$info);
        $this->display();
    }


    //通用礼券领用
    public function receive(){
        $cardlogic = new CardLogicModel();
        if($_POST){
            $data['card_id'] = trim(I('post.cardid'));
            //判断是否可用
            $info = $cardlogic->getCardInfo($data['card_id']);
            if(!empty($info)&&isset($info)){
                if($info['enable'] == 2 && time()>$info['disable_time']){
                    $this->ajaxReturn(['status' => 1 , 'info' => '抱歉,该礼券已禁用,无法领取']);
                }
            }else{
                $this->ajaxReturn(['status' => 1 , 'info' => '抱歉,该礼券不存在']);
            }

            $data['com_id'] = trim(I('post.id'));
            //判断公司可用性
            $company = $cardlogic->getCompanyInfo( $data['com_id']);
            if(!$company){
                $this->ajaxReturn(['status' => 1 , 'info' => '抱歉,该公司不是真会员无法领取']);
            }


            $dataRecord['amount'] = trim(I('post.number'));
            $dataRecord['activity_start'] = strtotime(I('post.hdtime_start'));
            $dataRecord['activity_end'] = strtotime(I('post.hdtime_end'));
            $dataRecord['start'] = strtotime(I('post.time_start'));
            $dataRecord['end'] = strtotime(I('post.time_end'));

            //添加领用信息
            $result = $cardlogic->addCardCom($data['card_id'],$data['com_id'],$dataRecord);
            if($result>0){
                $this->ajaxReturn(['status' => 0 , 'info' => '操作成功']);
            }else{
                $this->ajaxReturn(['status' => 1 , 'info' => '操作失败']);
            }
        }

        $this->assign('cardid',I('get.id'));
        $this->display();
    }


    //添加/编辑通用券
    public function editcommon(){
        $cardlogic = new CardLogicModel();
        if($_POST){
            $data['name'] = trim(I('post.name'));
            $data['money1'] = trim(I('post.decrease'));
            $data['money2'] = trim(I('post.cut'));
//            $data['service'] = trim(I('post.condition'));
            $data['rule'] = trim(I('post.rule'));
            $data['enable'] = empty(I('post.now_enable'))?1:I('post.now_enable');
            $data['module'] = 1; //产品要求默认模板1
//            $data['img'] = I('post.img');
//            $data['img2'] = I('post.img2');
            $nowId  = I('post.id');
            if($data['enable']  == 2){
                if(!empty($nowId)&&isset($nowId)){
                    $data['disable_time'] = strtotime(I('post.fb_time'));
                }else{
                    $data['disable_time'] = time();
                }
            }else{
                $data['disable_time'] = 0;
            }

            //添加
            $result = $cardlogic->addCommon($data,I('post.id'));
            if($result>0){
                $this->ajaxReturn(array('status'=>0,'info'=>'操作成功'));
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'操作失败'));
            }
        }
        $id = I('get.id');
        if(!empty($id)&&isset($id)){
            $info = $cardlogic->getCardInfo($id);
            if(empty($info)){
                $this->_error('无效的路径');
            }
            if(empty($info['disable_time'])){
                $info['disable_time'] = '';
            }
            $this->assign('info',$info);
        }
        $this->display();
    }



    //搜索公司名称
    public function searchCom(){
        $cardlogic = new CardLogicModel();
        $result = $cardlogic->searchCom(I('get.name'));
        $this->ajaxReturn(['status'=>0,'data'=>$result]);
    }

    //启用/禁用通用券
    public function forbidden(){
        $id = trim(I('post.cardid'));
        $data['enable'] = empty(trim(I('post.qiyong')))?1:trim(I('post.qiyong'));

        if($data['enable']  == 2){
            $data['disable_time'] = strtotime(I('post.fb_time'));
        }else{
            $data['disable_time'] = 0;
        }
        $cardlogic = new CardLogicModel();
        $result = $cardlogic->addCommon($data,$id);
        if($result>0){
            $this->ajaxReturn(['status' => 0 , 'info' => '操作成功']);
        }else{
            $this->ajaxReturn(['status' => 1 , 'info' => '操作失败']);
        }
    }


    

}