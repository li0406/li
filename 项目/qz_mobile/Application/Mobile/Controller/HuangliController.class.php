<?php
/**
 * 移动版 - 黄历频道
 */
namespace Mobile\Controller;
use Mobile\Common\Controller\MobileBaseController;
class HuangliController extends MobileBaseController{

    public function index(){
        $Data = D("Common/Diary");

        //获取城市信息
        //$citys = getCityArray();
        $cityid = $_SESSION['m_mapUseInfo']['id'];
       // $info["citys"] =  json_encode($citys);
        //装修问答模块，5条数据
        $condition['cateId'] = '7';
        $condition['orderBy'] = 'a.post_time DESC';
        //$list = $this->getQList($condition,1,5);

        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);
        $this->assign('source',326);//设置发单入口标识
        $this->assign('cityid',$cityid);
        //$this->assign("list",$list);
        $this->display();
    }

    public function show(){
        $year = I('get.year');
        $month = I('get.month');
        $day = I('get.day');
        $type = I('get.type');

        $map['y'] = $year;
        $map['m'] = $month;

        //输出月份列表
        if(empty($day) || !empty($type)){
            if(!empty($type)){
                unset($map);
                $nowTime = time();
                //选近期吉日
                //如果类型不为空
                //半年后
                /*if($type =='3'){
                    $hlTime = strtotime('+6 month');
                    $map["y"] = array("EQ",date('Y',$hlTime));
                    $thisMonty = date('m',$hlTime);
                    $lastMonty = $thisMonty + 2;

                    $today['title'] = '半年后';
                    $today['url'] = '3';
                }*/
                //3个月以内
                if($type =='2'){
                    $hlTime = $nowTime;

                    $map["y"] = array("EQ",date('Y',$hlTime));
                    $thisMonty = date('m',$hlTime);
                    $lastMonty = $thisMonty + 3;

                    $today['title'] = '三个月内';
                    $today['url'] = '3';
                }elseif($type =='3'){
                //半年内
                    $hlTime = $nowTime;

                    $map["y"] = array("EQ",date('Y',$hlTime));
                    $thisMonty = date('m',$hlTime);
                    $lastMonty = $thisMonty + 6;

                    $today['title'] = '半年内';
                    $today['url'] = '2';
                }else{
                //一个月内
                    $hlTime = $nowTime;

                    $map["y"] = array("EQ",date('Y',$hlTime));
                    $thisMonty = date('m',$hlTime);
                    $lastMonty = $thisMonty + 1;
                    $today['title'] = '一个月内';
                    $today['url'] = '1';
                }
                $map['m']  = array('between',"$thisMonty,$lastMonty");
            }

            $monthList = M('huangli')->field('*')->where($map)->select();
            //dump($monthList);
            foreach ($monthList as $k => $v) {
                $monthList[$k]['week'] = $this->getWeek($v['week']);
                if(strlen($v['m']) == '1'){
                    $monthList[$k]['m'] = '0'.$v['m'];
                }
                if(strlen($v['d']) == '1'){
                    $monthList[$k]['d'] = '0'.$v['d'];
                }
                $monthList[$k]['yi'] = $this->getYiJi($v['yi'],'1');
                if(empty($monthList[$k]['yi'])){
                    unset($monthList[$k]);
                }
            }

            $this->assign("monthList",$monthList);
        }

        $today['canonical'] = $_SERVER['REQUEST_URI'];
        //dump($today);

        $today['time'] = time();
        $today['y'] = $year;
        $today['m'] = $month;
        $today['d'] = $day;
        $this->assign("today",$today);
        $this->display();
    }

    public function zxhl()
    {
        $basic['head']['title'] = '装修吉日查询_装修吉日测算-齐装网';
        $basic['head']['description'] = '齐装网装修黄历查询频道为业主提供装修吉日查询，选择合适的装修吉日，装修才会更顺利！更多黄道吉日详细信息，尽在齐装网装修黄历频道。';
        $basic['head']['keywords'] = '装修吉日,装修吉日查询,黄历装修吉日查询,黄历吉日查询2016';

        $safe = getSafeCode();
        $this->assign("ssid",$safe["ssid"]);
        $this->assign("basic",$basic);
        $this->display();
    }

    //取周
    private function getWeek($str){
        if(!is_numeric($str) || $str > 6){
            return '未知';
        }
        $weekName = array('日','一','二','三','四','五','六');
        return $weekName[$str];
    }

    //取装修指数
    private function getYiJi($str,$type){
        $map = array('修造','安床','解除','入宅','拆卸','坏垣','动土','移徙','起基','安门','开池');
        $str = explode('、',$str);
        $result = '';
        foreach ($str as $k => $v) {
            $v = trim($v);
            if(in_array($v,$map)){
                $result[] = $v;
            }
        }
        if(!empty($result)){
            if($type == '1'){
                return '<span class="good">宜：'.implode('、',$result).'</span>';
            }else{
                return '<span class="bad">忌：'.implode('、',$result).'</span>';
            }
        }
    }

    //根据条件获取列表并分页
    private function getQList($condition,$pageIndex = 1,$pageCount = 5)
    {

        $result = D("Common/Ask")->getQListByCategory($condition,($pageIndex-1) * $pageCount,$pageCount);

        $qList = $result['result'];
        foreach ($qList as $k => $v) {
            //$qList[$k]['description'] = mbstr(htmlspecialchars(strip_tags($v['description'])),0,200);
            $qList[$k]['post_time'] = timeFormat($v['post_time']);
        }

        return $qList;
    }

}