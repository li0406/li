<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class IndexController extends HomeBaseController {
    public function index(){
        //检查客户端设备类型 移动版本跳转到
        B("Home\Behavior\MobileBrowserCheck");
        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        //$robotIsTrue = true; //debug 警告线上应该为注释状态
        if (true === $robotIsTrue) {
            $this->assign('robot',1);
        }

        $info = S('Cache:Home:Index');
        if($info){
            //获取注册公司数量
            $companyCount = $info["companyCount"];
            //获取注册设计师数量
            $designerCount = $info["designerCount"];
            //获取注册业主数量
            $userCount = $info["userCount"];
            //获取业主点评数量
            $commentCount = $info["commentCount"];
            //获取装修案例数量
            $casesCount = $info["casesCount"];
            //获取装修效果图数量
            $caseimgsCount  = $info["caseimgsCount"];
            //获取最新的发布订单信息
            $newOrderInfo = $info["newOrderInfo"];
            //获取城市信息
            $citys = $info["citys"];
            //获取最新装修案例
            $cases = $info["cases"];
            //获取热门城市
            $hotcitys = $info["hotcitys"];
            //获取装修案例图片总数
            $imgtotal = $caseimgsCount;
            //获取装修效果图案例
            //装修类型
            $caselx = $info["caselx"];
             //装修户型
            $casehx = $info["caseshx"];
            //装修风格
            $casefg = $info["casefg"];
            //
            $casecolor = $info["casecolor"];

            //获取装修攻略装修攻略
            //获取正文装修攻略
            $article = $info["article"];

            //装修经验文章
            $zxjy = $info["zxjy"];

            //省钱攻略文章
            $sqgl = $info["sqgl"];

            //居家风水文章
            $fengshui = $info["fengshui"];

            //吉祥物星座文章
            $xingzuo = $info["xingzuo"];

            //轮播广告
            $adv = $info["adv"];
            $friendLink = $info["friendLink"];
            $friendLinkTab = $info["friendLinkTab"];
        }else{
            //获取注册公司数量
            $info["companyCount"] = releaseCount("company");
            $companyCount = $info["companyCount"];

            //获取注册设计师数量
            $info["designerCount"] = releaseCount("designer");
            $designerCount = $info["designerCount"];

            //获取注册业主数量
            $info["userCount"] = releaseCount("user");
            $userCount = $info["userCount"];

            //获取业主点评
            $info["commentCount"] = releaseCount("comment");
            $commentCount =  $info["commentCount"];

            //获取装修案例数量
            $info["casesCount"] = releaseCount("cases");
            $casesCount = $info["casesCount"];

            //获取装修效果图数量
            $info["caseimgsCount"] = releaseCount("caseimgs");
            $caseimgsCount  = $info["caseimgsCount"];

            //获取最新装修案例
            $cases = $this->getNewCasesList(3);
            $info["cases"] = $cases;
            //获取热门城市
            $hotcitys = $this->getHotCitys(20);
            $info["hotcitys"] = $hotcitys;

            //获取装修案例图片总数
            $info["imgtotal"] = $caseimgsCount;
            $imgtotal = $info["imgtotal"];
            //获取装修效果图案例
            //局部
            $caselx = $this->getMeituList("location",3);
            foreach ($caselx as $key => $value) {
                $ids[] = $value["id"];
            }
            $caselx["child"] = array(
                             array("name"=>'儿童房','link'=>'/meitu/list-l12f0h0c0'),
                             array("name"=>'电视背景','link'=>'/meitu/list-l30f0h0c0'),
                             array("name"=>'吊顶','link'=>'/meitu/list-l21f0h0c0'),
                             array("name"=>'飘窗','link'=>'/meitu/list-l22f0h0c0'),
                             array("name"=>'鞋柜','link'=>'/meitu/list-l25f0h0c0'),
                             array("name"=>'餐厅','link'=>'/meitu/list-l6f0h0c0'),
                             array("name"=>'书房','link'=>'/meitu/list-l10f0h0c0'),
                             array("name"=>'玄关','link'=>'/meitu/list-l11f0h0c0'),
                             array("name"=>'阁楼','link'=>'/meitu/list-l17f0h0c0'),
                             array("name"=>'照片墙','link'=>'/meitu/list-l28f0h0c0'),
                             array("name"=>'更多','link'=>'/meitu/list')
                                     );
            $info["caselx"] = $caselx;

            //户型
            $casehx = $this->getMeituList("huxing",3,$ids);
            foreach ($casehx as $key => $value) {
                $ids[] = $value["id"];
            }
            $casehx["child"] = array(
                             array("name"=>'别墅 ','link'=>'/meitu/list-l0f0h8c0'),
                             array("name"=>'复式楼 ','link'=>'/meitu/list-l0f0h9c0'),
                             array("name"=>'跃层 ','link'=>'/meitu/list-l0f0h11c0'),
                             array("name"=>'70㎡ ','link'=>'/meitu/list-l0f0h12c0'),
                             array("name"=>'80㎡ ','link'=>'/meitu/list-l0f0h14c0'),
                             array("name"=>'90㎡ ','link'=>'/meitu/list-l0f0h15c0'),
                             array("name"=>'100㎡ ','link'=>'/meitu/list-l0f0h16c0'),
                             array("name"=>'110㎡ ','link'=>'/meitu/list-l0f0h17c0'),
                             array("name"=>'120㎡ ','link'=>'/meitu/list-l0f0h18c0'),
                             array("name"=>'自建别墅 ','link'=>'/meitu/list-l0f0h20c0'),
                             array("name"=>'更多','link'=>'/meitu/list')
                                     );

            $info["caseshx"] = $casehx;
            //风格
            $casefg = $this->getMeituList("fengge",3,$ids);
            foreach ($casefg as $key => $value) {
                $ids[] = $value["id"];
            }

            $casefg["child"] = array(
                             array("name"=>'中式 ','link'=>'/meitu/list-l0f4c0c0'),
                             array("name"=>'美式 ','link'=>'/meitu/list-l0f7c0'),
                             array("name"=>'新古典 ','link'=>'/meitu/list-l0f9c0'),
                             array("name"=>'乡村 ','link'=>'/meitu/list-l0f11c0'),
                             array("name"=>'田园 ','link'=>'/meitu/list-l0f13c0'),
                             array("name"=>'简欧 ','link'=>'/meitu/list-l0f15c0'),
                             array("name"=>'东南亚 ','link'=>'/meitu/list-l0f16c0'),
                             array("name"=>'混搭 ','link'=>'/meitu/list-l0f17c0'),
                             array("name"=>'韩式 ','link'=>'/meitu/list-l0f18c0'),
                             array("name"=>'北欧 ','link'=>'/meitu/list-l0f21c0'),
                             array("name"=>'后现代','link'=>'/meitu/list-l0f23c0'),
                             array("name"=>'更多','link'=>'/meitu/list')
                                     );

            $info["casefg"] = $casefg;
            //色彩
            $casecolor = $this->getMeituList("color",3,$ids);
            $casecolor["child"] = array(
                             array("name"=>'中性冷色 ','link'=>'/meitu/list-lf0h0c11'),
                             array("name"=>'明亮黄色 ','link'=>'/meitu/list-lf0h0c6'),
                             array("name"=>'浪漫粉红 ','link'=>'/meitu/list-lf0h0c4'),
                             array("name"=>'动感绿色 ','link'=>'/meitu/list-lf0h0c8'),
                             array("name"=>'海洋蓝色 ','link'=>'/meitu/list-lf0h0c9'),
                             array("name"=>'神秘紫色 ','link'=>'/meitu/list-lf0h0c10'),
                             array("name"=>'恬淡紫色 ','link'=>'/meitu/list-lf0h0c15'),
                             array("name"=>'沉稳深色 ','link'=>'/meitu/list-lf0h0c14'),
                             array("name"=>'缤纷彩色 ','link'=>'/meitu/list-lf0h0c13'),
                             array("name"=>'更多','link'=>'/meitu/list')
                                     );
            $info["casecolor"] = $casecolor;
            //获取装修攻略装修攻略
            //获取正文装修攻略
            $article = $this->getArticles();
            $article = $article[0];
            $info["article"] = $article;
            //装修经验文章
            $zxjy = $this->getArticles(87,8);
            $info["zxjy"] = $zxjy;
            //局部装修
            $sqgl = $this->getArticles(105,8);
            $info["sqgl"] = $sqgl;
            //装修风水
            $fengshui = $this->getArticles(114,8);
            $info["fengshui"] = $fengshui;

            //装修风格
            $xingzuo = $this->getArticles(121,8);
            $info["xingzuo"] = $xingzuo;

            //获取首页轮播g广告图片
            $adv = $this->getLunboAdv($_SESSION["cityId"]);
            $info["adv"] = $adv;
            //获取友情链接
            $friendLink["link"] = D("Friendlink")->getFriendLinkList($_SESSION["cityId"],1);
            //获取热门城市
            $result =D("Friendlink")->getFriendLinkList($_SESSION["cityId"],2);
            foreach ($result as $key => $value) {
               $hotCity[] = $value;
            }
            $friendLink["hotCity"] = $hotCity;
            if(count($friendLink["link"]) > 0 || count($friendLink["hotCity"]) > 0){
                $info["friendLink"] = $friendLink;
                if(count($friendLink["link"]) > 0){
                    $info["friendLinkTab"] = 0;
                }else{
                    $info["friendLinkTab"] = 1;
                }
                $friendLinkTab =$info["friendLinkTab"];
            }
            $info["friendLink"] = $friendLink;
            S("Cache:Home:Index",$info,900);
        }


        $this->assign("caselxNav",$caselx['child']);
        unset($caselx['child']);
        $this->assign("caseshxNav",$casehx['child']);
        unset($casehx['child']);
        $this->assign("casefgNav",$casefg['child']);
        unset($casefg['child']);
        $this->assign("casecolorNav",$casecolor['child']);
        unset($casecolor['child']);


        //取热门装修日记
        $this->assign("hotDiary",$this->getHotDiary(4));


        //精华问答
        $this->assign('distAsk',$this->getDistAsk());

        //热门问答
        $hotAsk = $this->getHotAskByWeek('6');
        $this->assign("hotAsk",$hotAsk);

        //最新问答
        $this->assign("newAsk",$this->getNewQuestion('6'));



        //获取最新的发布订单信息
        $newOrderInfo = $this->getNewOrders();

         //安全验证码
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);
        //获取是否显示获取报价弹层
        if(!isset($_COOKIE["w_index"])){
            $this->assign("isOpen",true);
        }

        //获取底部弹层
        $t = T("Common@Order/zb_bottom_s");
        $zb_bottom_s = $this->fetch($t);
        $this->assign("zb_bottom_s",$zb_bottom_s);



        //导航栏标识
        $this->assign("tabIndex",0);
        //header搜索框搜索条件绑定
        $this->assign("header_search",0);
        $this->assign("friendLink",$friendLink);
        $this->assign("friendLinkTab",$friendLinkTab);
        $this->assign("adv",$adv);
        $this->assign("cases",$cases);
        $this->assign("companyCount",$companyCount);
        $this->assign("designerCount",$designerCount);
        $this->assign("userCount",$userCount);
        $this->assign("commentCount",$commentCount);
        $this->assign("casesCount",$casesCount);
        $this->assign("caseimgsCount",$caseimgsCount);
        $this->assign("hotcitys",$hotcitys);
        $this->assign("newOrderInfo",$newOrderInfo);
        $this->assign("citys",json_encode($citys));
        $this->assign("imgtotal",$imgtotal);
        $this->assign("caselx",$caselx);
        $this->assign("caseshx",$casehx);
        $this->assign("casecolor",$casecolor);
        $this->assign("casefg",$casefg);
        $this->assign("article",$article);
        $this->assign("zxjy",$zxjy);
        $this->assign("sqgl",$sqgl);
        $this->assign("fengshui",$fengshui);
        $this->assign("xingzuo",$xingzuo);
        $this->display();
    }

    /**
     * 装修工具
     * @return [type] [description]
     */
    public function tools(){
        $target = empty($_GET["t"]) == true?"qz":I("get.t");
        $tags = array(
                array("key"=>"qz","value"=>"墙砖计算器","src"=>"/tools/qz/"),
                array("key"=>"dz","value"=>"地砖计算器","src"=>"/tools/dz/"),
                array("key"=>"db","value"=>"地板计算器","src"=>"/tools/db/"),
                array("key"=>"bz","value"=>"壁纸计算器","src"=>"/tools/bz/"),
                array("key"=>"tl","value"=>"涂料计算器","src"=>"/tools/tl/"),
                array("key"=>"cl","value"=>"窗帘计算器","src"=>"/tools/cl/"),
                array("key"=>"tfj","value"=>"填缝剂计算器","src"=>"/tools/tfj/")
                      );
        foreach ($tags as $key => $value) {
            if($value["key"] == $target){
                $this->assign("keys",$value);
            }
        }

        $cases = $this->getCasesList(1,3);
        $this->assign("cases",$cases);
        $this->assign("target",$target);
        $this->assign("tags",$tags);
        $this->display("Index/jsq");
    }
    /**
     * 获取验证码
     * @return [type] [description]
     */
    public function verify(){
        getVerify("",4,120,35);
    }

    public function company(){
        $p = I("get.p");
        $url = "https://".C("QZ_YUMINGWWW")."/company/";
        if(!empty($url)){
            $url .= "?p=".$p;
        }
        header( "HTTP/1.1 301 Moved Permanently" );
        header( "Location:".$url);
    }

     /**
     * 从www主站访问的装修公司文章301到对应的分站上去
     *
     */
    public function zixun(){
        $id = I("get.id");
        if(!empty($id)){
            $info = D("Info")->getSingleInfoById($id);
            if(count($info)>0){
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location: ".$this->SCHEME_HOST['scheme_full'] .$info["bm"].".".C("QZ_YUMING")."/zixun_info/".$id.".shtml");
                die();
            }
        }
        $this->_error();
    }

    /**
     * 老版设计师设计作品
     * @return [type] [description]
     */
    public function works(){
        $id = I("get.id");
        $user = D("User")->getSingleUserInfoById($id);

        if(count($user) > 0){
            $bm = $user["bm"];
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location: ".$this->SCHEME_HOST['scheme_full'] .$bm.".".C("QZ_YUMING")."/blog/".$id);
            die();
        }
        $this->_error();
        die();
    }

    /**
     * 老版装修公司案例路由
     * @return [type] [description]
     */
    public function company_case(){
        $id = I("get.id");
        $p = I("get.p");
        $user = D("User")->getSingleUserInfoById($id);
        if(count($user) > 0){
            $bm = $user["bm"];
            $url = $this->SCHEME_HOST['scheme_full'] .$bm.".".C("QZ_YUMING")."/company_case/".$id;
            if(!empty($p)){
                $url .= "?p=".$p;
            }
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }
        $this->_error();
        die();
    }


    /**
     * 老版设计师文章
     * @return [type] [description]
     */
    public function works_info(){
        $id = I("get.id");
        $article = D("Article")->getSingleArticle($id);

        if(count($article) > 0){
            $bm = $article["bm"];
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location: ".$this->SCHEME_HOST['scheme_full'] .$bm.".".C("QZ_YUMING")."/article_info/".$id.".html");
            die();
        }
        $this->_error();
        die();
    }

    /**
     * 老版主站效果图跳转方法
     * @return [type] [description]
     */
    public function caseinfo(){
        $id = I("get.id");
        $case = D("Cases")->getSingleById($id);
        if(count($case) > 0){
            $bm = $case["bm"];
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location: ".$this->SCHEME_HOST['scheme_full'] .$bm.".".C("QZ_YUMING")."/caseinfo/".$id.".shtml");
            die();
        }
        $this->_error();
    }

    /**
     * 老版装修公司路由
     * @return [type] [description]
     */
    public function company_home(){
        $id = I("get.id");
        $user = D("User")->getSingleUserInfoById($id);
        if(count($user) > 0){
            $bm = $user["bm"];
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location: ".$this->SCHEME_HOST['scheme_full'] .$bm.".".C("QZ_YUMING")."/company_home/".$id);
            die();
        }
        $this->_error();
    }

    /**
     * 老版装修公司路由
     * @return [type] [description]
     */
    public function blog(){
        $id = I("get.id");
        $user = D("User")->getSingleUserInfoById($id);
        if(count($user) > 0){
            $bm = $user["bm"];
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location: ".$this->SCHEME_HOST['scheme_full'] .$bm.".".C("QZ_YUMING")."/blog/".$id);
            die();
        }
        $this->_error();
    }

    /**
     * 验证验证码
     * @return [type] [description]
     */
    public function check_verify(){
        if($_POST){
            $code = strtolower($_POST["code"]);
            if(check_verify($code)){
                 $this->ajaxReturn(array("data"=>"","info"=>"验证码正确！","status"=>1));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"验证码不正确！","status"=>0));
        }
    }

      /**
     * 用户登录框
     * @return [type] [description]
     */
    public function login(){
        R("Common/Login/Login");
        die();
    }

    /**
     * 用户登录
     * @return [type] [description]
     */
    public function loginin(){
        R("Common/Login/Loginin");
        die();
    }
    /**
     * 用户退出
     * @return [type] [description]
     */
    public function loginout(){
         R("Common/Login/loginout");
          die();
    }
    /**
     * 用户收藏
     * @return [type] [description]
     */
    public function collect(){
         R("Common/Collect/setCollect");
          die();
    }

    //设置首页弹窗不显示的COOKIE
    public function setwindowswitch(){
        R("Common/Zbfb/setwindowswitch");
        die();
    }

    //设置首页弹窗不显示的COOKIE
    public function refresh(){
        R("Common/Zbfb/refresh");
        die();
    }

    //设置首页弹窗不显示的COOKIE
    public function dispatcher(){
        R("Common/Zbfb/dispatcher");
        die();
    }

    //反馈信息路由
    public function feedback(){
        R("Common/Zbfb/feedback");
        die();
    }


    public function getZbPrice(){
        R("Common/Zbfb/getZbPrice");
        die();
    }

    public function fb_order(){
        R("Common/Zbfb/fb_order");
        die();
    }

     /**
     * 短信发送
     * @return [type] [description]
     */
    public function sendsms(){
        R("Common/Sms/sendsms");
        die();
    }

    //验证验证码是否正确
    public function verifysmscode(){
        R("Common/Sms/verifysmscode");
        die();
    }

    public function run(){
        R("Common/Login/run");
        die();
    }

    //免费电话咨询
    public function freetel() {
        R("Common/Zbfb/freetel");
        die();
    }



    /**
     * 获取最新的订单发布信息
     * @return [type] [description]
     */
    private function getNewOrders($cs = '',$on=""){
        $str = "5分钟前 苏州市 王先生 普通户型 申请装修设计";
        $orders = S("Cache:Order:new");
        if(!$orders){
            $orders = D("Common/Orders")->getNewOrders(100,$on,$cs);
            if(count($orders) < 10 ){
                //如果是新开站或者订单案例数不足的,取全站的最新订单
                $orders = $this->getNewOrders();
            }
            S("Cache:Order:new",$orders,900);
        }

        $rand  = array_rand($orders,20);
        foreach ($rand as $key => $value) {
            $order = $orders[$value];
            $order["cname"] = $this->cityInfo["name"];
            $min   =  rand(1,10);
            $name  = mb_substr($order["name"],0,1,"utf-8").$order["sex"];
            $orderInfo[] = sprintf("%s分钟前 %s %s %s 申请装修设计",$min,$order["cname"],$name,$order["hxname"]);
        }
        return  $orderInfo;
    }

    /**
     * 获取主站最新装修案例
     * @return [type] [description]
     */
    private function getNewCasesList($limit=3){
        $cases = D("Common/Cases")->getIndexNewsCases($limit);
        if(count($cases) > 0){
            return $cases;
        }
        return null;
    }

    /**
     * 获取热门城市
     * @return [type] [description]
     */
    private function getHotCitys($limit=10){
        $citys = D("Common/Quyu")->getHotCitys($limit);
        if(count($citys) > 0){
            return $citys;
        }
        return null;
    }

    /**
     * 获取主站首页效果图数量
     * @return [type] [description]
     */
    private function getCaseImagesTotal(){
       $total = D('Common/Cases')->getIndexCaseImagesTotal();
       if(count($total) > 0){
            return $total[0]["sum"];
       }
       return 0;
    }

    /**
     * 获取装修案例效果图
     * @return [type] [description]
     */
    private function getCasesList($type ='',$limit = 20){
        //type 1.装修类型 2.户型 3.风格
        $result = D("Common/Cases")->getIndexCasesList($type,$limit);
        if(count($result) > 0){
            $rand  = array_rand($result,3);
            $pattern = array('\n','\\');
            foreach ($rand as $key => $value) {
                if(!empty($result[$value]["text"])){
                    $result[$value]["text"] = htmlspecialchars_decode($result[$value]["text"],ENT_QUOTES);
                    $result[$value]["text"] = str_replace($pattern,"",$result[$value]["text"]);
                    if(mb_strlen($result[$value]["text"],"utf-8") > 140){
                        $result[$value]["text"] = mb_substr( $result[$value]["text"],0,140,"utf-8")."...";
                    }
                }
                $cases[] = $result[$value];
            }
            return $cases;
        }
        return null;
    }

    private function getArticles($type ='',$limit = 1){
        //获取该类别的类型
        $result = D("WwwArticleClass")->getArticleClassById($type);
        $articles = D('Common/WwwArticle')->getIndexArticles(array_unique($result["ids"]),$limit);
        if(count($articles) > 0){
            if (1 == count($articles)) {
                //如果是有一篇,那么就是需要文章细节 保留80字
                $articles[0]["content"] = mbstr(strip_tags($articles[0]["content"]),0,80,"utf-8");
            } else {
                //如果大于1篇 文章细节都不保存
                foreach ($articles as $key => $value) {
                    unset($articles[$key]["content"]);
                }
            }
            return $articles;
        }
        return null;
    }

    //取精华提问和答案
    private function getDistAsk(){
        $homeDistAsk = S('Cache:Home:DistAsk');
        if(empty($homeDistAsk)){
            $Db = D('Common/Ask');
            $tempdist = $Db->getOption('ask_dist');
            $dist = $tempdist['0'];
            unset($tempdist);
            //取最佳答案ID
            $askinfo = $Db->getAskByid($dist['id']);
            $id = $askinfo['best_aid'];
            if(!empty($id)){
                $answer = $Db->getAnwser($id);
                $dist['answer'] = $answer['content'];
                $dist['answer'] = htmlspecialchars(strip_tags($dist['answer']));
                //只有存在采纳答案时才存储
                S('Cache:Home:DistAsk',$dist,900);
                return $dist;
            }
        }
        return $homeDistAsk;
    }

    //随机获取本周热门问题 60分钟缓存
    private function getHotAskByWeek($num){
        $hotAskByWeek = S('Cache:Ask:hotByWeek');
        if(empty($hotAskByWeek)){
            $hotAskByWeek = D('Common/Ask')->getHotAskByWeek('30');
            S('Cache:Ask:hotByWeek',$hotAskByWeek,3600);
        }
        //如果当前结果小于要取的结果
        if(count($hotAskByWeek) <= $num){
            return $hotAskByWeek;
        }
        $s = array_rand($hotAskByWeek,$num);
        foreach($s as $val){
            $result[] = $hotAskByWeek[$val];
        }
        return $result;
    }

    //获取最新问答
    private function getNewQuestion($num){
        $NewAsk = S('Cache:Ask:New');
        if (empty($NewAsk)) {
            $NewAsk = D('Common/Ask')->getNewQuestion($num);
            S('Cache:Ask:New',$NewAsk,3600);

        }
        return $NewAsk;

    }

    //获取装修日记（有缓存）
    private function getHotDiary($num){
        $allHotDiary = S('Cache:Diary:HotDiary');
        if(empty($allHotDiary)){
            $result = D('Diary')->getHotDiaryUser($num);
            //获取风格
            $fengge = '';
            $temp = D('Meitu')->getFengge(30,true);
            foreach ($temp as $key => $value) {
                $fengge[$value['id']] = $value;
            }
            //获取户型
            $huxing = '';
            $temp = D('Meitu')->getHuxing(30,true);
            foreach ($temp as $key => $value) {
                $huxing[$value['id']] = $value;
            }

            $allHotDiary = array();
            foreach($result as $key => $v){
                unset($v['img_list']);
                $v['mianji'] = $v['mianji'].'㎡';
                $v['huxing'] = $huxing[$v['huxing']]['name'];
                $tempFengge = explode(',',$v['fengge']);
                $v['fengge'] = $fengge[$tempFengge['0']]['name'];
                $allHotDiary[] = $v;
            }
            S('Cache:Diary:HotDiary',$allHotDiary,3600);
        }
        return $allHotDiary;
    }

    private function getLunboAdv($city = ""){
        $adv =  D("Common/Adv")->getIndexAdv($city);
        return $adv;
    }


    private function getMeituList($type,$limit,$not){
       $meitu = D("Meitu")->getMeituListByType($type,$limit,$not);
       return $meitu;
    }

}