<?php

/*
官方活动管理
 */

namespace User\Controller;
use User\Common\Controller\CompanyBaseController;

class EventController extends CompanyBaseController{

    public function index(){
        $info["user"] = $this->baseInfo;
        $uid = $_SESSION["u_userInfo"]["id"];
        if($info["user"]["on"] != 2){
            header("Location:".C('QZ_YUMING_SCHEME')."://u.qizuang.com/home/");
        }

        $cs = $info['user']['cs'];

        $vipNum = D('Event')->checkVipNum($cs);
        if($vipNum < 10){
            header("Location:".C('QZ_YUMING_SCHEME')."://u.qizuang.com/home/");
        }

        $template = 'index';


        $pageIndex = 1;
        $pageCount = 15;
        if(!empty($_GET["p"])){
            $pageIndex = remove_xss($_GET["p"]);
        }

        //过期活动
        $action = I('get.action');
        if($action == 'expired'){
            $condition['a.end_time'] = array("ELT",time());
            $template = 'expired';
        }else{
            $condition['a.end_time'] = array("EGT",time());
        }

        $condition['c.cid'] = array("EQ",$cs);
        $condition['c.status'] = array("EQ",'0');


        $result = $this->getList($condition,$pageIndex,$pageCount);
        $list = $result['list'];

        //取已报名的活动
        $aids = array_map(function($element){return $element['aid'];},$list);
        $aids = implode($aids,',');
        $tempEnrolls = D("Event")->getEnrollByAid($aids,$info['user']['id']);
        foreach ($tempEnrolls as $key => $value) {
            $enrolls[$value['aid']] = $value;
        }

        $timestr = time();

        /*
        已经报名参加且正在进行的活动，显示“已报名”
        报名截止时间还没到，且已经报名的，有[取消报名]
        报名截止时间还没到，有[去报名]

        显示规则：
        1、显示装修公司参与的活动
        2、没参与的显示报名截止时间还没到的活动
        排序规则：
        1、已经报名且正在进行的活动排在前面，正在进行的又按照报名截止时间排序，时间越靠前排序越靠前
        2、已经报名且还没开始的活动次之，这这部分也按照报名截止时间排序，时间越靠前排序越靠前
        还没报名且报名截止时间还没到的活动次之，这部分也按照报名截止时间排序，时间越靠前排序越靠前


         */
        foreach ($list as $key => $value) {
            //0招募中 1正在进行 2暂停 3结束 4删除 5已报名可取消 6已报名不可取消 7可报名 8已过期 9已过期已报名 10 已过期未报名

            //先检查是否已报名
            if(!empty($enrolls[$value['aid']])){
                //定义过期后状态
                $list[$key]['exp_status'] = '9';

                //已报名 活动未开始，可以取消报名
                if($timestr < $value['start_time'] && $timestr < $value['enroll_time']){
                    $list[$key]['new_status'] = '5';
                }else{
                    $list[$key]['new_status'] = '6';
                }
            }else{
                $list[$key]['exp_status'] = '10';

                //未报名 当前时间小于报名截止时间
                if($timestr < $value['enroll_time']){
                    $list[$key]['new_status'] = '7';
                }else{
                    $list[$key]['new_status'] = '8';
                }
            }
            //暂停状态
            if($value['status'] == '2'){
                $list[$key]['new_status'] = '2';
            }

            //活动总状态判断 ----------- 招募中 招募结束 正在进行 暂停


            //不是暂停 (状态2)
            if($value['status'] != '2'){
                //设个未知状态
                $list[$key]['status'] = '5';
                //招募中 当前时间小于报名截止时间，并且小于活动开始时间
                if($timestr < $value['enroll_time'] && $timestr < $value['start_time']){
                    $list[$key]['status'] = '0';
                }
                //结束    当前时间大于结束时间
                if($timestr > $value['end_time']){
                    $list[$key]['status'] = '3';
                }
                //正在进行 当前时间大于开始时间小于结束时间
                if($timestr > $value['start_time'] && $timestr < $value['end_time']){
                    $list[$key]['status'] = '1';
                }
            }
        }

        $info['allCount'] = $result['count'];

        //dump($list);

        $this->assign("list",$list);
        $this->assign('page',$result['page']);
        $this->assign("info",$info);
        $this->assign("nav",12);
        $this->display($template);
    }

    public function view(){

        $id = $_GET['id'];
        if(empty($id) || !is_numeric($id)){
            $this->_error('不是正确的活动 :(');
        }
        $Db = D('Event');
        $info = $Db->getById($id);
        if(empty($info)){
            $this->_error('不是正确的活动 :(');
        }
        $info["user"] = $this->baseInfo;



        //dump($info);

        //0招募中 1正在进行 2暂停 3结束 4删除 5已报名可取消 6已报名不可取消 7可报名

        //先检查是否已报名
        $uid = $_SESSION["u_userInfo"]["id"];
        $isEnrool = $Db->getEnrollByAid($id,$uid);
        if(!empty($isEnrool['0'])){
            //已报名 活动未开始，可以取消报名
            if(time() < $info['start_time']){
                $info['new_status'] = '5';
            }else{
                $info['new_status'] = '6';
            }
        }else{
            //未报名 当前时间小于报名截止时间
            if(time() < $info['enroll_time']){
                $info['new_status'] = '7';
            }
        }

        //暂停状态
        if($info['status'] == '2'){
            $info['new_status'] = '2';
        }

        $info['content'] = htmlspecialchars_decode($info['content']);
        $this->assign("info",$info);
        $this->assign("nav",12);
        $this->display();
    }

    //设置状态
    public function setStatus(){
        if(IS_POST){
            $id = I('post.id');
            $type = I('post.type');
            $user = $this->baseInfo;
            $uid = $user['id'];
            $cs = $user['cs'];

            if(empty($id) || empty($type)){
                $this->ajaxReturn(array('data'=>'','info'=>'缺少必要参数！','status'=>0));
            }

            //取消报名
            if($type == '1'){
                $map = array(
                    'aid' => array("EQ",$id),
                    'uid' => array("EQ",$uid),
                );
                M('activity_enroll')->where($map)->delete();
                $map = array(
                    "id" => array("EQ",$id),
                );
                M("activity")->where($map)->setDec('enroll_count');

                $maps = array(
                    'aid' => array("EQ",$id),
                    'cid' => array("EQ",$cs),
                );
                M("activity_city")->where($maps)->setDec('counts');
                $this->ajaxReturn(array('data'=>'','info'=>'取消报名成功!','status'=>1));
            }

            //报名
            if($type == '2'){
                $data = array(
                    'aid' => $id,
                    'uid' => $uid,
                    'cid' => $user['cs'],
                    'time'=>time()
                );
                M('activity_enroll')->add($data);
                $map = array(
                    "id" => array("EQ",$id),
                );
                M("activity")->where($map)->setInc('enroll_count');

                $maps = array(
                    'aid' => array("EQ",$id),
                    'cid' => array("EQ",$cs),
                );
                M("activity_city")->where($maps)->setInc('counts');
                $this->ajaxReturn(array('data'=>'','info'=>'报名成功!','status'=>1));
            }

        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败','status'=>0));
    }

    //获取报表
    public function getReport(){

        $id = $_GET['id'];
        if(empty($id)){
            $this->ajaxReturn(array('data'=>'','info'=>'参数不正确！','status'=>0));
        }

        $activity = M('activity')->where(array('id'=>$id))->find();
        $info = '浏览量：<strong style="font-weight: 700;">'.$activity['views'].'</strong>';

        $this->ajaxReturn(array('data'=>'','info'=>$info,'status'=>1));
    }

    //根据条件获取列表并分页
    private function getList($condition,$pageIndex = 1,$pageCount = 10){
        import('Library.Org.Page.Page');
        $result = D("Event")->getList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp,"count"=>$count);
    }

}