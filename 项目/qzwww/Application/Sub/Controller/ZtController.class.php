<?php

namespace Sub\Controller;
use Sub\Common\Controller\SubBaseController;

class ZtController extends SubBaseController{

    public function _initialize(){
        parent::_initialize();
    }

    public function special(){
        $name = I('get.name');


        $info = D('Zt')->getActivity($name);
        if(empty($info)){
            $this->error();
        }

        $info['lefttime'] = ceil(($info['end_time'] - time()) / 86400);
        $info['lefttime'] = str_split($info['lefttime']);

        //状态：0报名中 1正在进行 2 暂停 3结束 5未知
        $status = $this->getStatus($info);

        if($info['name'] == 'crazyjuly'){

            if($status == '0'){
                $this->error('活动还未开始');
            }
            if($status == '2'){
                $this->error('活动已暂停');
            }
            if($status == '3'){
                $this->error('活动已结束');
            }

            //每5分钟更新活动虚拟报名人数
            $crazyjulybmrs = S('Cache:Zt:crazyjuly:bmrs');
            if(empty($crazyjulybmrs)){
                $crazyjulybmrs = $info['fake_enroll_count'];
                $this->updateEnrollCount($info['id']);
                S('Cache:Zt:crazyjuly:bmrs',$crazyjulybmrs,300);
            }
            $info['splitEnrolls'] = str_split($info['fake_enroll_count']);
            $info['cityid'] = $this->cityInfo['id'];
        }



        $this->assign("info",$info);
        $this->display($info['name']);
    }

    public function complain(){
        $post = I('post.');

        if($post['action'] == 'show'){
            $cid = $post['cityid'];
            $aid = $post['eventid'];

            //查询当前有哪些公司参加活动
            $var['company'] = D('Zt')->getEventCompanys($cid,$aid);

            $this->assign("var",$var);
            $arr["tmp"] = $this->fetch(T("Sub@Zt/complainbox"));
            $this->ajaxReturn(array("data"=>$arr,"info"=>"","status"=>1));
            die;
        }

        $data['tel'] = $post['tel'];
        $data['name'] = $post['name'];
        $data['company_id'] = $post['company'];
        $data['type'] = $post['reason'];
        $data['note'] = $post['note'];

        $isOrder = D('Zt')->isPostOrder($data['tel']);

        if(empty($isOrder)){
            $var['title'] = '投诉失败！';
            $var['content'] = '您还没有参加活动，无法进行投诉！';
            $this->assign("var",$var);
            $arr["tmp"] = $this->fetch(T("Sub@Zt/complain_result"));
            $this->ajaxReturn(array("data"=>$arr,"info"=>"投诉失败，请重新投诉","status"=>0));
            die;
        }

        $add = D('Zt')->addComplain($data);
        if($add){
            $var['title'] = '投诉成功！';
            $var['content'] = '我们将在10分钟内处理您的投诉信息，并在1个工作日内告知您处理结果！<br>请您耐心等待！';
            $this->assign("var",$var);
            $arr["tmp"] = $this->fetch(T("Sub@Zt/complain_result"));
            $this->ajaxReturn(array("data"=>$arr,"info"=>"投诉成功","status"=>1));
            die;
        }
        $var['title'] = '投诉失败！';
        $var['content'] = '投诉失败，请重新投诉！';
        $this->assign("var",$var);
        $arr["tmp"] = $this->fetch(T("Sub@Zt/complain_result"));

        $this->ajaxReturn(array("data"=>$arr,"info"=>"投诉失败，请重新投诉","status"=>0));
    }

    //状态：0报名中 1正在进行 2 暂停 3结束 5未知
    private function getStatus($info){
        $time = time();

        //不是暂停 (状态2)
        if($info['status'] != '2'){
            //设个未知状态
            $status = '5';
            //招募中 当前时间小于报名截止时间，并且小于活动开始时间
            if($time < $info['enroll_time'] && $time < $info['start_time']){
                $status = '0';
            }
            //结束    当前时间大于结束时间
            if($time > $info['end_time']){
                $status = '3';
            }
            //正在进行 当前时间大于开始时间小于结束时间
            if($time > $info['start_time'] && $time < $info['end_time']){
                $status = '1';
            }
        }

        if(!empty($status)){
            $info['status'] = $status;
        }

        return $info['status'];
    }


    //更新活动虚拟报名人数
    public function updateEnrollCount($id){

        //随机生成一个数
        $data = mt_rand(1,8);
        D('Zt')->updateEnrollCount($id,$data);
    }


}