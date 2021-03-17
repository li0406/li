<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class HuangliController extends HomeBaseController{
    public function _initialize(){
        parent::_initialize();
       //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        if (true === $robotIsTrue){
            $this->assign('robot',1);
        }
        //开工吉日默认选 中
        $this->assign('choose_gonglue', 'hl');
        $headerTmp = "";
        if(empty($this->cityInfo["bm"])){
            $t = T("Home@Index:header");
        }else{
            if(!$robotIsTrue){
                $t = T("Sub@Index:header");
            }
        }

        $this->assign('holdercontent','全国超过十万家装修公司为您免费设计');
        $this->assign('serch_uri','companysearch');
        $this->assign('serch_type','装修公司');

        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp",$headerTmp);
    }

    //首页
    public function index(){
        //选近期吉日
        $map["y"] = array("EQ",date('Y'));
        $thisMonty = date('m');
        $lastMonty = $thisMonty + 1;
        $map['m']  = array('between',"$thisMonty,$lastMonty");
        $map['yi']  = array('like','%修造%');
        $jiriList = M('huangli')->field('y,m,d')->where($map)->select();
        $this->assign('jiriList',$jiriList);

        //取今天数据
        unset($map['yi']);
        unset($map['m']);
        $map['m']  = date('m');
        $map['d']  = date('d');
        $todayInfo = M('huangli')->field('*')->where($map)->find();

        //装修指数
        $todayYiTmp = explode('、', $todayInfo['yi']);
        $todayYi = $s = '0';
        foreach ($todayYiTmp as $k => $v) {
            $s = $this->getScore($v);
            $todayYi = $todayYi + $s;
        }

        $todayInfo['zxStar'] = $this->getStar($todayYi);

        //最新有最佳答案的问答
        $link = D("Common/Ask")->getNewQuestionAndBest(4);
        foreach ($link as $key => $value) {
            if(strlen($value['title']) > 48){
                $link[$key]['title'] = mb_substr($value['title'],'0','15','utf-8')."...";
            }
            if(strlen($value['answer']) > 240){
                $link[$key]['answer'] = mb_substr($value['answer'],'0','79','utf-8')."...";
            }
        }
        ################ 资讯列表 ###############
        /*$map['pid'] = '114';
        $map['isTop'] = '0';
        //装修风水
        $articleList = D('WwwArticle')->getListByCid($map,'a.addtime DESC','6');
        $this->assign('articleList',$articleList);

        //推荐风水
        $map['isTop'] = '1';
        $topArticleList = D('WwwArticle')->getListByCid($map,'a.addtime DESC','5');
        $topArticle = $topArticleList['4'];
        unset($topArticleList['4']);
        $topArticle['content'] = htmlspecialchars(strip_tags($topArticle['content']));
        $topArticle['content'] = preg_replace("/\s|　/","",$topArticle['content']);

        $this->assign('topArticles',$topArticleList);
        $this->assign('topArticle',$topArticle);*/

        $todayInfo['y'] = date('Y');
        $todayInfo['m'] = date('m');
        $todayInfo['d'] = date('d');

        $this->assign("today",$todayInfo);


        //本年装修黄历查询
        $thisYear = date('Y');
        for($i = 1 ;$i <= 12 ;$i++){
            $thisYearList[] = array(
                'url' => $thisYear.'-'.$i,
                'text' => $thisYear.'年'.$i.'月'
            );
        }
        $this->assign("thisYearList",$thisYearList);

        $this->assign("order_source",12);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("citys",$citys);
        $this->assign("orderTmp",$orderTmp);
        $this->assign("link",$link);
        $this->display();
    }

    public function showList(){
        $year = I('get.year');
        $month = I('get.month');
        $day = I('get.day');
        $type = I('get.type');


        $map['y'] = $year;
        $map['m'] = $month;

        //输出月份列表
        if(empty($day) || !empty($type)){
            $template = 'month';

            if(!empty($type)){
                unset($map);
                $nowTime = time();
                //选近期吉日
                //如果类型不为空
                //半年后
                if($type =='3'){
                    $hlTime = $nowTime;
                    $map["y"] = array("EQ",date('Y',$hlTime));
                    $thisMonty = date('m',$hlTime);
                    $lastMonty = $thisMonty + 6;

                    $today['title'] = '半年内';
                    $today['url'] = '3';
                }elseif($type =='2'){
                //半年内
                    $hlTime = $nowTime;

                    $map["y"] = array("EQ",date('Y',$hlTime));
                    $thisMonty = date('m',$hlTime);
                    $lastMonty = $thisMonty + 3;

                    $today['title'] = '3个月内';
                    $today['url'] = '2';
                }else{
                //一个月内
                    $hlTime = $nowTime;

                    $map["y"] = array("EQ",date('Y',$hlTime));
                    $thisMonty = date('m',$hlTime);
                    $lastMonty = $thisMonty + 1;

                    $today['title'] = '1个月内';
                    $today['url'] = '1';
                }

                $template = 'baojia';

                $map['m']  = array('between',"$thisMonty,$lastMonty");
                $map['yi'] = array('like','%修造%');
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
                $monthList[$k]['ji'] = $this->getYiJi($v['ji'],'2');
            }

            $this->assign("hotDiary",$this->getHotDiary(3));
            $this->assign("monthList",$monthList);
            //dump($monthList);
        }else{
            $map['d'] = $day;
            $dayInfo = M('huangli')->field('*')->where($map)->find();
            $dayInfo['week'] = $this->getWeek($dayInfo['week']);
            //dump($dayInfo);

            //取节气
            unset($map['d']);
            $map['jq'] = array('NEQ','0');
            $jqList = M('huangli')->field('*')->where($map)->select();
            $dayInfo['jq'] = '';
            foreach ($jqList as $k => $v) {
                $dayInfo['jq'] .= $this->getJQ($v['jq']).'('.$v['n_month'].$v['n_day'].')&nbsp;&nbsp;';
            }

            //输出宜忌
            $dayInfo['yi'] = explode('、',$dayInfo['yi']);
            $dayInfo['ji'] = explode('、',$dayInfo['ji']);

            $this->assign("day",$dayInfo);
            $template = 'day';
        }

        //今天的时间
        $today['time'] = time();
        $today['y'] = $year;
        $today['m'] = $month;
        $today['d'] = $day;
        $this->assign("today",$today);


        //本年装修黄历查询
        $thisYear = date('Y');
        for($i = 1 ;$i <= 12 ;$i++){
            $thisYearList[] = array(
                'url' => $thisYear.'-'.$i,
                'text' => $thisYear.'年'.$i.'月'
            );
        }
        $this->assign("thisYearList",$thisYearList);


        //最新有最佳答案的问答
        $link = D("Common/Ask")->getNewQuestionAndBest(4);
        foreach ($link as $key => $value) {
            if(strlen($value['title']) > 48){
                $link[$key]['title'] = mb_substr($value['title'],'0','15','utf-8')."...";
            }
            if(strlen($value['answer']) > 240){
                $link[$key]['answer'] = mb_substr($value['answer'],'0','79','utf-8')."...";
            }
        }
        $this->assign("link",$link);

        //本月装修记日
        $jiriList = M('huangli')->field('y,m,d')->where($map)->select();
        $this->assign('jiriList',$jiriList);


        /*//取没有回答的问题列表
        $map = array("adopt_time" => array("EQ",'0'));
        $noAnwserList = D("Common/Ask")->getQuestionList($map,'rand()',0,'12');
        $this->assign("noAnwserList",$noAnwserList);

        //新增回答 - 包括用户和分类信息
        $newAnwser = D("Common/Ask")->getNewAnwsers(3);
        $this->assign("newAnwser",$newAnwser);

        $this->assign("rankImg", $this->getRankMeitu(4));

        $article = D('Common/Article')->getArticle('12');
        $this->assign("gonglue",$article);
*/


        $this->assign("order_source",12);
        $t = T("Common@Order/orderTmp");
        $orderTmp = $this->fetch($t);
        $this->assign("citys",$citys);
        $this->assign("orderTmp",$orderTmp);

        $this->assign("hotAsk",$this->getHotAsk(4));

        $this->display($template);
    }

    /**
     * 装修黄历
     * @return [type] [description]
     */
    public function zxhl()
    {
        $SCHEME_HOST = $this->SCHEME_HOST;

        $url = $SCHEME_HOST['scheme_full'].'m.'.$SCHEME_HOST['domain'] . '/hl/';
        if (ismobile()) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location:" . $url);
            die;
        }

        // a.19.04.07 taozuang页面修改 多域名模式下，非qizuang的域名给404
        $FLAG_DOMAIN_QIZUANG = $this->FLAG_DOMAIN_QIZUANG;
        if (0 === $FLAG_DOMAIN_QIZUANG) {
            $this->_error();
        }

        $this->display();
    }


    //获取热门回答
    private function getHotAsk($num){
        $result = S('Cache:Huangli:HotAsk');
        if(empty($result)){
            S('Cache:Huangli:HotAsk',null);
            $map = array("q.visible"=>'0',"a.visible"=>'0',"q.sub_category"=>'7');
            $result = M('ask')->alias("q")
                          ->field('q.id,q.views,q.title,a.content')
                          ->join("inner join qz_ask_anwser as a on q.id = a.qid")
                          ->order('q.views DESC')
                          ->group('q.id')
                          ->where($map)
                          ->limit("0,20")
                          ->select();
            S('Cache:Huangli:HotAsk',$result,900);
        }
        shuffle($result);
        return array_slice($result,0,$num);
    }

    //获取装修日记
    private function getHotDiary($num){
        $result = S('Cache:Huangli:HotDiary');
        if(empty($result)){
            S('Cache:Huangli:HotDiary',null);
            $result = D('Diary')->getHotDiaryUser(30);
            S('Cache:Huangli:HotDiary',$result,900);
        }
        shuffle($result);
        return array_slice($result,0,$num);
    }

    //取装修案例
    private function getRankMeitu($limit){
        $result = S('Cache:Article:RankMeitu');
        if(empty($result)){
            S('Cache:Article:RankMeitu',null);
            $result = D("Meitu")->getNewMeitu(30);
            S('Cache:Article:RankMeitu',$result,900);
        }
        shuffle($result);
        return array_slice($result,0,$limit);
    }

    public function getzxstar(){
        $time = explode('-',I('get.time'));
        if($time['1']['0'] === '0'){
            $time['1'] = substr($time['1'],1);
        }
        if($time['2']['0'] === '0'){
            $time['2'] = substr($time['2'],1);
        }
        $map['y'] = $time['0'];
        $map['m'] = $time['1'];
        $map['d'] = $time['2'];
        $dayInfo = M('huangli')->field('*')->where($map)->find();

         //装修指数
        $todayYiTmp = explode('、', $dayInfo['yi']);
        $todayYi = $s = '0';
        foreach ($todayYiTmp as $k => $v) {
            $s = $this->getScore($v);
            $todayYi = $todayYi + $s;
        }

        for ($i=1 ; $i <= 5; $i++) {
            if($i <= $todayYi){
                $html .= '<i class="icon-star"></i>';
            }else{
                $html .= '<i class="icon-star-empty"></i>';
            }
        }
        echo $html;
        die();
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
                return implode('、',$result);
                //return '<span class="good">宜：'.implode('、',$result).'</span>';
            }else{
                return implode('、',$result);
                //return '<span class="bad">忌：'.implode('、',$result).'</span>';
            }
        }
    }

    //取节气
    private function getJQ($str){
        $jieqi = array(
            "立春","雨水","惊蛰","春分","清明","谷雨",
            "立夏","小满","芒种","夏至","小暑","大暑",
            "立秋","处暑","白露","秋分","寒露","霜降",
            "立冬","小雪","大雪","冬至","小寒","大寒"
        );
        return $jieqi[$str];
    }

    //取装修指数
    private function getScore($str){
        $str = trim($str);
        $map = array(
           '修造'=>'2','安床'=>'1','解除'=>'0.5','入宅'=>'1','拆卸'=>'1','坏垣'=>'0.5',
           '动土'=>'2','移徙'=>'1','起基'=>'0.5','安门'=>'1','开池'=>'0.5',
        );
        if(!in_array($str,$map)){
            return $map[$str];
        }
    }

    //取星级     1星<=1分    1分<2星<=2分    2分<3星<=3分    3分<4星<=5分    5分<5星
    private function getStar($star){
        for ($i=1 ; $i <= 5; $i++) {
            if($i <= $star){
                $html .= '<i class="icon-star"></i>';
            }else{
                $html .= '<i class="icon-star-empty"></i>';
            }
        }
        return $html;
    }

}