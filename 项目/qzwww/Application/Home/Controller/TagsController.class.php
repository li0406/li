<?php
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class TagsController extends HomeBaseController{

    private $redirct = array(
                                '15639' =>'2391',
                                '21189' =>'19429',
                                '24226' =>'19671',
                                '16065' =>'8372',
                                '17073' =>'5126',
                                '7713'  =>'21848',
                                '6031'  =>'18476',
                                '961'   =>'15260',
                                '15615' =>'15260',
                                '20590' =>'20856',
                                '21658' =>'3513',
                                '22250' =>'2586',
                                '17484' =>'1532',
                                '14532' =>'14511',
                                '15415' =>'14511',
                                '1056'  =>'13794',
                                '15610' =>'131',
                                '15623' =>'590',
                                '15589' =>'2468',
                                '14821' =>'2761',
                                '13826' =>'14013',
                                '3478'  =>'13904',
                                '13751' =>'146',
                                '15598' =>'586',
                                '14626' =>'2060',
                                '13748' =>'50',
                                '14500' =>'50',
                                '14627' =>'50',
                                '15184' =>'50',
                                '14464' =>'2485',
                                '15100' =>'2485',
                                '15196' =>'2485',
                                '14929' =>'14942',
                                '2731'  =>'15607',
                                '13795' =>'835',
                                '14354' =>'600',
                                '14482' =>'600',
                                '14574' =>'600',
                                '15205' =>'600',
                                '15554' =>'600',
                                '15504' =>'1542',
                                '14509' =>'468',
                                '15028' =>'468',
                                '15525' =>'468',
                                '15648' =>'468',
                                '15536' =>'5857',
                                '15526' =>'2484',
                                '22139' =>'459',
                                '1394'  =>'15203',
                                '15175' =>'15203',
                                '14186' =>'1272',
                                '15458' =>'67',
                                '15564' =>'67',
                                '15642' =>'554',
                                '2096'  =>'13872',
                                '23936' =>'16604',
                                '15463' =>'2783',
                                '1849'  =>'13766',
                                '15530' =>'13766',
                                '15204' =>'2669',
                                '925'   =>'14507',
                                '1465'  =>'14053',
                                '4775'  =>'13943',
                                '14315' =>'1964',
                                '892'   =>'13986',
                                '15073' =>'1938',
                                '461'   =>'13799',
                                '15593' =>'1535',
                                '13950' =>'2016',
                                '789'   =>'13969',
                                '15263' =>'1233',
                                '22752' =>'22736',
                                '14974' =>'2217',
                                '15548' =>'901',
                                '15596' =>'53',
                                '14964' =>'602',
                                '15631' =>'602',
                                '15638' =>'602',
                                '54'    =>'14004',
                                '14205' =>'2583',
                                '3688'  =>'15645',
                                '13915' =>'15645',
                                '14257' =>'1338',
                                '15467' =>'1338',
                                '3347'  =>'21973',
                                '1504'  =>'15599',
                                '15406' =>'4332',
                                '14430' =>'3821',
                                '14182' =>'2738',
                                '15594' =>'2884',
                                '15520' =>'170',
                                '22593' =>'170',
                                '14416' =>'1746',
                                '14410' =>'4517',
                                '5158'  =>'14002',
                                '2199'  =>'14515',
                                '14583' =>'2491',
                                '14351' =>'1996',
                                '21194' =>'542',
                                '2825'  =>'21928',
                                '13901' =>'551',
                                '1829'  =>'13726',
                                '13744' =>'1369',
                                '14223' =>'538',
                                '15455' =>'15444',
                                '19928' =>'1923',
                                '25518' =>'1360',
                                '495'   =>'14040',
                                '552'   =>'15103',
                                '13878' =>'1011',
                                '15216' =>'313',
                                '733'   =>'14092',
                                '15015' =>'548',
                                '2191'  =>'13742',
                                '663'   =>'14658',
                                '14646' =>'1748',
                                '14719' =>'15136',
                                '6776'  =>'13749',
                                '21083' =>'2257',
                                '14445' =>'2830',
                                '49'    =>'14003',
                                '1206'  =>'15630',
                                '7087'  =>'13753',
                                '764'   =>'14219',
                                '14187' =>'15063',
                                '424'   =>'13879'
                             );

    public function _initialize(){
        parent::_initialize();
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);
        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            if (count($m) == 0) {
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C("QZ_YUMINGWWW").$uri."/");
            }
        }

       //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        if (true === $robotIsTrue) {
            $this->assign('robot',1);
        }

        //添加顶部搜索栏信息
        $this->assign('serch_uri','meitu/list');
        $this->assign('serch_type','装修效果图');
        $this->assign('holdercontent','海量精美家居美图任你选');

        if(empty($this->cityInfo["bm"])){
            $t = T("Home@Index:header");
        }else{
            if(!$robotIsTrue){
                $t = T("Sub@Index:header");
            }
            //安全验证码
            $safe = getSafeCode();
            $this->assign("safecode",$safe["safecode"]);
            $this->assign("safekey",$safe["safekey"]);
            $this->assign("ssid",$safe["ssid"]);
        }

        $headerTmp = $this->fetch($t);
        $this->assign("headerTmp",$headerTmp);
    }

    //首页
    public function index(){
        // 2020-08-14 11:13:06 扬成： tags 可以直接删，404
        $this->_error();

        $DB = D("Common/Tags");
        //标签分类  1.文章 2.家居美图 3.装修日记 4.问答
        $articeTags = $DB->getTopTags(1,60);
        $this->assign("articeTags",$articeTags);
        //问答
        $askTags = $DB->getTopTags(4,60);
        $this->assign("askTags",$askTags);

        $this->assign("info",$info);
        $this->display();
    }

    public function category(){
        // 2020-08-14 11:13:06 扬成： tags 可以直接删，404
        $this->_error();
        $category = I('get.cate');//取分类
        $id = I('get.id');//取Tags ID
        //访问到美图做301跳转 到meitu.qizuang.com/tags/
        if($category == 'meitu'){
            $url = $this->SCHEME_HOST['scheme_full'] .'meitu.qizuang.com/tags/'.I('get.cate').I('get.id');
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }
        if(!empty($this->redirct[$id])){
            $url = 'https://'.C('QZ_YUMINGWWW').'/tags/'.$category.$this->redirct[$id];
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }

        $DB = D('Common/Tags');

        $tags = $DB->getTags($id);
        if(empty($tags)){
            $this->_error();
        }

        $title = $tags['name'];
        $keyword = $tags['name'];

        $pageIndex = 1;
        $pageCount = 10;
        if(!empty($_GET["p"])){
            $pageIndex = remove_xss($_GET["p"]);
            $pageContent ="第".$pageIndex."页";
        }
        $condition['tags'] = $id;

        //攻略
        if($category == 'gonglue'){
            $result = $this->getArticleList($condition,$pageIndex,$pageCount);
            foreach ($result['list'] as $k => $v) {
                if(!empty($v['imgs'])){
                    $exp = explode(",", $v["imgs"]);
                    $exp = array_filter($exp);
                    foreach ($exp as $key => $val) {
                         if(!strpos($val,C('QINIU_DOMAIN'))){
                            $path = $this->SCHEME_HOST['scheme_full'] .C('STATIC_HOST1')."/".$val;
                            $exp[$key]  = $path;
                        }
                    }
                    $result['list'][$k]["imgs"] = $exp;
                }
            }
            $hotTags = S('C:HotTag:1:9');
            if(empty($hotTags)){
                $hotTags = $DB->getHotTags(1,9);
                S('C:HotTag:1:9',$hotTags,10800);
            }
            $newTags = $DB->getNewTags(1,9);

            $description = '齐装网'.$this->mbrtrim($tags['name'],'标签').'标签页分享了各种'.$this->mbrtrim($tags['name'],'装修').'装修经验，帮助广大业主快速了解'.$tags['name'].'相关知识，解决了广大业主装修遇到的'.$tags['name'].'相关难题。';
        }

        //问答
        if($category == 'wenda'){
            $result = $this->getAskList($condition,$pageIndex,$pageCount);
            $hotTags = S('C:HotTag:4:9');
            if(empty($hotTags)){
                $hotTags = $DB->getHotTags(4,9);
                S('C:HotTag:4:9',$hotTags,10800);
            }
            $newTags = $DB->getNewTags(4,9);
            $description = '齐装网'.$this->mbrtrim($tags['name'],'问答').'问答标签页汇集了各种关于'.$tags['name'].'相关问题，并为您提供了'.$this->mbrtrim($tags['name'],'问题').'问题的专业解答。如果您对'.$tags['name'].'还有任何疑惑，来齐装网装修问答平台吧！';
        }

        //日记
        if($category == 'riji'){
            $result = $this->getDiaryList($condition,$pageIndex,$pageCount);
            $hotTags = S('C:HotTag:3:9');
            if(empty($hotTags)){
                $hotTags = $DB->getHotTags(3,9);
                S('C:HotTag:3:9',$hotTags,10800);
            }
            $newTags = $DB->getNewTags(3,9);
        }

        //百科
        if($category == 'baike'){
            $result = $this->getBaikeList($condition,$pageIndex,$pageCount);
            $hotTags = S('C:HotTag:6:9');
            if(empty($hotTags)){
                $hotTags = $DB->getHotTags(6,9);
                S('C:HotTag:6:9',$hotTags,10800);
            }
            $newTags = $DB->getNewTags(6,9);
        }

        $request_uri = I('server.REQUEST_URI');
        if(!empty($request_uri)){
            $staticURI = explode('?',$request_uri);
        }

        //获取canonical属性
        if(!empty($staticURI)){
            $info['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').$staticURI['0'];
        }
        //若标签下没有百科/问答/日记/攻略的内容,跳转到404页面;
        if($result["num"] == 0){
            $this->_error();
            die();
        }

        $this->assign("list",$result["list"]);
        $this->assign("tagid",$id);
        $this->assign("num",$result["num"]);
        $this->assign("hotTags",$hotTags);
        $this->assign("newTags",$newTags);
        $this->assign("page",$result["page"]);
        if($category != 'meitu'){
            $this->assign("title",$title);
            $this->assign("keyword",$keyword);
            $this->assign("description",$description);
        } else {
            $this->assign("tkd",$tkd);
        }
        $this->assign("info",$info);
        $this->display($category);
    }

    /**
     * [mbrtrim 去掉字符串右边相同部分]
     * @param  [type] $string  [字符串]
     * @param  [type] $replace [去掉的部分]
     * @return [type]          [description]
     */
    private function mbrtrim($string, $replace){
        $location = mb_strrpos($string, $replace);
        if($location === mb_strlen($string) - mb_strlen($replace)){
            $string = mb_substr($string, 0, $location);
        }
        return $string;
    }

    //取问答列表
    private function getAskList($condition,$pageIndex = 1,$pageCount = 10){
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        import('Library.Org.Page.Page');
        $result = D("Common/Tags")->getAskList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp,"num"=>$count);
    }

    //取攻略列表
    private function getArticleList($condition,$pageIndex = 1,$pageCount = 10){
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        import('Library.Org.Page.Page');
        $result = D("Common/Tags")->getArticleList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp,"num"=>$count);
    }

    //取日记列表
    private function getDiaryList($condition,$pageIndex = 1,$pageCount = 10){
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        import('Library.Org.Page.Page');
        $result = D("Common/Tags")->getDiaryList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp,"num"=>$count);
    }

    //取百科列表
    private function getBaikeList($condition,$pageIndex = 1,$pageCount = 10){
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        import('Library.Org.Page.Page');
        $result = D("Common/Tags")->getBaikeList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp,"num"=>$count);
    }

}
