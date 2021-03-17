<?php
// +----------------------------------------------------------------------
// | IndexController  分站首页控制器
// +----------------------------------------------------------------------
namespace Sub\Controller;

use Common\Model\Logic\CompanyLogicModel;
use Common\Model\Logic\SubthematicLogicModel;
use Sub\Common\Controller\SubBaseController;
use Common\Model\Db\CompanyModel;

class IndexController extends SubBaseController
{
    public function _initialize()
    {
        parent::_initialize();
        //添加顶部搜索栏信息
        $this->assign('serch_uri','companysearch');
        $this->assign('serch_type','装修公司');
        $this->assign('holdercontent','全国超过十万家装修公司为您免费设计');
    }

    /**
     * 分站首页
     *
     * @return void
     */
    public function index()
    {
        $SCHEME_HOST = $this->SCHEME_HOST;
        //处理为0的控制器页面为404
        if (0 === strrpos($_SERVER['REQUEST_URI'], '/0')) {
            $this->_error();
        }
        //检查客户端设备类型 移动版本跳转到
        B("Home\Behavior\MobileBrowserCheck");
        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        //$robotIsTrue = true; //debug 警告线上应该为注释状态
        if (true === $robotIsTrue) {
            $this->assign('robot',1);
        }
        $cityInfo = $this->cityInfo;
        //获取head头关键字keywords描述description标题title
        $keys["title"] =$cityInfo["name"]."装修_".$cityInfo["name"]."装修公司_".$cityInfo["name"]."装修网-齐装网";
        $keys["keywords"] = $cityInfo["name"].'装修,'.$cityInfo["name"].'装修公司,'.$cityInfo["name"].'装修网';
        $keys["description"] = $cityInfo["name"].'齐装网致力于为广大业主提供透明、保障、省心的装修服务，汇集众多优秀的'.$cityInfo["name"].'装修装饰公司和家装工装设计师，并提供权威的'.$cityInfo["name"].'装修公司排名供您参考';

        //获取注册业主数量
        $info["userCount"] = releaseCount("user");
        //获取装修案例数量
        $info["casesCount"] = releaseCount("cases");
        //获取装修效果图数量
        $info["caseimgsCount"] = releaseCount("caseimgs");
        //获取注册公司数量
        $info["companyCount"] = releaseCount("company");
        //获取注册设计师数量
        $info["designerCount"] = releaseCount("designer");

        //获取轮播,不限制数量
        $info["lunbo"] = S('Cache:Sub:Index:lunbo:v3:'.$cityInfo["bm"]);
        if(!$info["lunbo"]){

            //1.第一张是全站理轮播
            //2.后面5张分站轮播
            //3.最后一张是全站
            //4.如果分站不够全站补

            //获取分站轮播图片
            $subLunbo = D("Advbanner")->getAdvBannerList($cityInfo["id"]);

            //获取全站轮播图片 上限5张
            $mainLunbo = D("Advbanner")->getAdvBannerList('0',5);

            $info["lunbo"][] = $mainLunbo[0];
            unset($mainLunbo[0]);
            foreach ($subLunbo as $key => $value) {
                $info["lunbo"][] = $value;
            }

            foreach ($mainLunbo as $key => $value) {
                $info["lunbo"][] = $value;
            }

            //每三小时统计一次轮播展示
            $isUpdateLunboLog = S('Cache:Sub:Index:lunboLog:'.$cityInfo["bm"]);
            if(!$isUpdateLunboLog){
                foreach ($info["lunbo"] as $k => $v) {
                    if(!empty($v['company_id'])){
                        $this->advBannerShowLog($v['id'],$v['company_id'],$v['city_id']);
                    }
                }
                S('Cache:Sub:Index:lunboLog:'.$cityInfo["bm"],time(),10800);
            }
            //装修公司如果不填写链接，默认公司主页
            foreach ($info["lunbo"] as $key => $value) {
                if (empty($value['url']) && !empty($value['company_id'])) {
                    $value['url'] = $this->SCHEME_HOST['scheme_full'].$value["bm"].".".C('QZ_YUMING')."/company_home/".$value["company_id"]."/";
                }
                $info["lunbo"][$key]['url'] = $value['url'];
            }
            S('Cache:Sub:Index:lunbo:v3:'.$cityInfo["bm"],$info["lunbo"],900);
        }

        //获取案例
        $info["cases"] = S('Cache:Sub:Index:cases:v2:'.$cityInfo["bm"]);
        if(!$info["cases"]){
            $info["cases"] = D("Advbanner")->getAdvList("home_cases",$cityInfo["id"],5);
            $count = count($info["cases"]);
            if($count < 5){
                $info["cases"] = D("Advbanner")->getAdvList("home_cases",'000001',(5-$count));
            }
            S('Cache:Sub:Index:cases:v2:'.$cityInfo["bm"],$info["cases"],900);
        }

        //获取通栏A
        $info["bigbanner_a"] = S('Cache:Sub:Index:bigbanner_a:'.$cityInfo["bm"]);
        if(!$info["bigbanner_a"]){
            $info["bigbanner_a"] = D("Advbanner")->getAdvList("home_bigbanner_a",$cityInfo["id"],3);
            S('Cache:Sub:Index:bigbanner_a:'.$cityInfo["bm"],$info["bigbanner_a"],900);
        }

        //获取通栏B
        $info["bigbanner_b"] = S('Cache:Sub:Index:bigbanner_b:'.$cityInfo["bm"]);
        if(!$info["bigbanner_b"]){
            $info["bigbanner_b"] = D("Advbanner")->getAdvList("home_bigbanner_b",$cityInfo["id"],3);
            S('Cache:Sub:Index:bigbanner_b:'.$cityInfo["bm"], $info["bigbanner_b"],900);
        }

        //效果图和效果图类别
        $info["effect_meitu"] = S('Cache:Sub:Index:effect_meitu:v2:'. $cityInfo["id"]);
        if(!$info["effect_meitu"]){
            $subdata = D("SubhomeEffect")->getSubEffect($cityInfo["id"]);

            $imrecommend = $wdrecommend = [];
            foreach ($subdata as $key => $value) {
                switch ($value['type']) {
                    case '1':
                        $imrecommend[] = $value;
                        break;
                    case '2':
                        $wdrecommend[] = $value;
                        break;
                    default:
                        break;
                }
            }

            // 如果图片推荐少于8张或者文字推荐少于6个则使用公共推荐
            if (count($imrecommend) < 8 || count($wdrecommend) < 6) {
                $subdata = unserialize(D('Options')->getOptionNoCache('subhome_effect_meitu_dist')['option_value']);
                $imrecommend2 = $wdrecommend2 = [];
                foreach ($subdata as $key => $value) {
                    switch ($value['type']) {
                        case '1':
                            $imrecommend2[] = $value;
                            break;
                        case '2':
                            $wdrecommend2[] = $value;
                            break;
                        default:
                            break;
                    }
                }

                if (count($imrecommend) < 8) {
                    $imrecommend = $imrecommend2;
                }

                if (count($wdrecommend) < 6) {
                    $wdrecommend = $wdrecommend2;
                }
            }

            $info["effect_meitu"] = ['imrecommend' => $imrecommend, 'wdrecommend' => $wdrecommend];
            foreach ($info["effect_meitu"]["imrecommend"] as $key => $value) {
                    if(strpos($value["url"],$this->SCHEME_HOST['scheme_full']) === false){
                        $info["effect_meitu"]["imrecommend"][$key]["url"] = $this->SCHEME_HOST['scheme_full'].$value["url"];
                    }
                    $info["effect_meitu"]["imrecommend"][$key]["img"] = C("QINIU_SCHEME").'://'.C("QINIU_DOMAIN")."/".$value["img"];
            }

            S('Cache:Sub:Index:effect_meitu:v2:' . $cityInfo["id"], $info["effect_meitu"], 900);
        }

        //装修攻略
        $info["steps"] = S('Cache:Sub:Index:steps:v2');
        if(!$info["steps"]){
            $info["steps"] = $this->getStep(87,6,true);
            S('Cache:Sub:Index:steps:v2',$info["steps"],900);
        }

        $cityid = session('cityId');
        $info['cityname'] = $this->cityInfo['name'];

        //装修公司排行榜
        $info["rankCompany"] = S('Cache:Sub:Index:rankCompany:v2:'.$cityInfo["bm"]);
        $companyLogic = new CompanyLogicModel();
        if (!$info['rankCompany']) {
            $map['cs'] = $cityInfo['id'];
            $map['sale'] = 3;
            $infoLiangfang = D('Common/Company')->getLiangfangList($map, 0, 5);
            $info['rankCompany']['liangfang'] = $infoLiangfang['result'];
            $infoQiandan = D('Common/Company')->getQiandanList($map, 0, 5, "subIndex");
            $info['rankCompany']['qiandan'] = $infoQiandan['result'];
            $trueCompanyLiangfangNumber = count($info['rankCompany']['liangfang']);
            if ($trueCompanyLiangfangNumber < 5) {
                $fakeLiangfangCompany = $companyLogic->getTrueFakeCompany($cityInfo['id'], 5 - $trueCompanyLiangfangNumber, ['sort' => 'asc', 'a.register_time' => 'desc']);
                $info['rankCompany']['liangfang'] = array_merge($info['rankCompany']['liangfang'], $fakeLiangfangCompany);
            }
            $trueCompanyQiandanNumber = count($info['rankCompany']['qiandan']);
            if ($trueCompanyQiandanNumber < 5) {
                $fakeQiandanCompany = $companyLogic->getTrueFakeCompany($cityInfo['id'], 5 - $trueCompanyQiandanNumber);
                $info['rankCompany']['qiandan'] = array_merge($info['rankCompany']['qiandan'], $fakeQiandanCompany);
            }
            S('Cache:Sub:Index:rankCompany:v2:'.$cityInfo['bm'],$info['rankCompany'],900);
        }

        //品牌榜 - 热门装修公司
        $info['brands'] = S('Cache:Sub:Index:hotBrands:v3:'.$cityid);
        if(empty($info['brands'])){
            $info['brands'] = $companyLogic->getSubstationRecommend($cityid);
            S('Cache:Sub:Index:hotBrands:v3:'.$cityid, $info['brands'], 900);
        }

        //装修流程
        $info['liucheng'] = S('Cache:Sub:Index:lc:v2');
        if (!$info['liucheng']) {
            $arr = D('WwwArticleClass')->getArticleClassIdsByClass('87');
            $id = array();
            foreach ($arr as $row) {
                $id[] = $row['id'];
            }
            if (!empty($id)) {
                $liucheng['class_id'] = array('IN', $id);
            } else {
                //文章分类为空
                $liucheng['class_id'] = array('EQ', '');
            }
            $liucheng['a.recommend'] = 1; //推荐
            $liucheng['a.state'] = 2; //审核
            $info['liucheng'] = D('WwwArticle')->getWwwArticleList($liucheng, 0, 6, 'a.addtime DESC');
            S('Cache:Sub:Index:lc:v2', $info['liucheng'], 900);
        }

        //装修问答
        $info['wenda'] = S('Cache:Sub:Index:wenda:v2');
        if (!$info['wenda']) {
            //最多回答
            $info['wenda'] = M("ask")->field('id,title,views,anwsers')->where(['visible'=>'0'])->order('id desc,create_time desc')->limit('0,6')->select();
            S('Cache:Sub:Index:wenda:v2',$info['wenda'],900);
        }

        //装修百科
        $info['baike'] = S('Cache:Sub:Index:baike:v2');
        if (!$info['baike']) {
            $baike['_string'] = 'a.review != 0 AND a.visible = 0';
            //只获取删除状态为0的
            $baike['remove'] = 0;
            $info['baike'] = D("Adminbaike")->getDetailList($baike,0,6,'a.id,a.title, z.name AS category,c.name sub_category,c.url');
            S('Cache:Sub:Index:baike:v2',$info['baike'],900);
        }

        //装修日记
        $info['riji'] = S('Cache:Sub:Index:riji:v2');
        if (!$info['riji']) {
            $info['riji'] = D("Diary")->getNewList(6);
            S('Cache:Sub:Index:riji:v2',$info['riji'],900);
        }
        //文章分类和文章内容
        $info["cateGory"] = S('Cache:Sub:Index:cateGory:v2:'.$cityInfo["bm"]);
        if(!$info["cateGory"]){
            $cateGoryList = D('Infotype')->getTypes(2);
            foreach ($cateGoryList as $key => $value) {
                $content = D('Littlearticle')->getArticleListNew($value['id'], $cityInfo['id'], 0, 3, '', 'addtime desc');
                if (empty($content)) {
                    unset($cateGoryList[$key]);
                    continue;
                }

                foreach ($content as $k=>$v){
                    $cateGoryList[$key]['content'][$k]['id']  = $v["id"];
                    $cateGoryList[$key]['content'][$k]['title']  = $v["title"];
                    $cateGoryList[$key]['content'][$k]['addtime']  = $v["addtime"];

                    $desc = trim($v['description']);
                    if (empty($desc)){
                        $cateGoryList[$key]['content'][$k]['description'] = mbstr(strip_tags(htmlspecialchars_decode($v['content'])), 0, 45, 'utf-8',false);
                    } else {
                        $cateGoryList[$key]['content'][$k]['description'] = $desc;
                    }
                }
                switch ($value['shortname'])
                {
                    case "bendi":
                        $cateGoryList[$key]['px'] = 1;
                        break;
                    case "xuetang":
                        $cateGoryList[$key]['px'] = 2;
                        break;
                    case "gongsi":
                        $cateGoryList[$key]['px'] = 3;
                        break;
                    case "daogou":
                        $cateGoryList[$key]['px'] = 4;
                        break;
                    case "baojia":
                        $cateGoryList[$key]['px'] = 5;
                        break;
                    case "wenwen":
                        $cateGoryList[$key]['px'] = 6;
                        break;
                    case "jingyan":
                        $cateGoryList[$key]['px'] = 7;
                    break;
                    case "zxsj":
                        $cateGoryList[$key]['px'] = 8;
                        break;
                    default:
                        break;
                }
            }

            if (empty($cateGoryList)) {
                //重新排序
                $edition = array();
                foreach ($cateGoryList as $key => $value) {
                    $edition[] = $value["px"];
                }
                array_multisort($edition, SORT_ASC,$cateGoryList);
            }

            $info["cateGory"] = $cateGoryList;
            S('Cache:Sub:Index:cateGory:v2:'.$cityInfo["bm"],$info["cateGory"],900);
        }

        //获取当前城市设计案例最多的设计师,会员公司设计师优先
        $info["designer"] = S('Cache:Sub:Index:designer:v2:'.$cityInfo["bm"]);
        if(!$info["designer"]){
            $info["designer"] = D("Advdesigner")->getDesignerList($cityInfo["id"],6);
            S('Cache:Sub:Index:designer:v2:'.$cityInfo["bm"],$info["designer"],900);
        }
        $info['designercount'] = 6-count($info["designer"]);

        //获取最新的业主点评
        $info["comment"] = S('Cache:Sub:Index:comment:v2:'.$cityInfo["bm"]);
        if(!$info["comment"]){
            $info["comment"]  = D("Comment")->getRemovalCommentsByCity(4,$cityInfo["id"]);
            S('Cache:Sub:Index:comment:v2:'.$cityInfo["bm"],$info["comment"],900);
        }

        //获取友情链接
        $friendLink = S('Cache:Sub:Index:friendLink:v2:'.$cityInfo["bm"]);
        if(!$friendLink){
            $friendLink["link"] = D("Friendlink")->getFriendLinkList($cityInfo["id"],1);
            //获取热门城市
            $result =D("Friendlink")->getFriendLinkList($cityInfo["id"],2);
            foreach ($result as $key => $value) {
                $hotCity[] = $value;
            }
            $friendLink["hotCity"] = $hotCity;

            //获取附近城市
            $result =D("Friendlink")->getFriendLinkList($cityInfo["id"],4);
            foreach ($result as $key => $value) {
                $recentCity[] = $value;
            }
            $friendLink["recentCity"] = $recentCity;

            //获取区域装修公司
            $result =D("Friendlink")->getFriendLinkList($cityInfo["id"],5);
            foreach ($result as $key => $value) {
                $areaCompany[] = $value;
            }
            $friendLink["areaCompany"] = $areaCompany;

            //获取地域装修服务
            $logic = new SubthematicLogicModel();
            $friendLink["zhuanti"] = $logic->getIndexZhuantiList($cityInfo["id"],50);
            S('Cache:Sub:Index:friendLink:v2:'.$cityInfo["bm"],$friendLink,900);
        }

        //获取是否显示获取报价弹层
        if(!isset($_COOKIE["w_index"])){
            $result = $this->getShowWindowTmp();
            if ($result) {
                $this->assign("isOpen",true);
            }
        }

        //添加返回总站按钮
        $info['returnhome'] = '<span class="returnhome"></span><div style="padding-left:2px; float:left;"><span><a href="'.$SCHEME_HOST['scheme_full'].'www.'.$SCHEME_HOST['domain'].'" title="齐装网">返回总站</a></span></div>';

        //获取最新视屏的前四条
        $videos = S('Cache:Sub:Index:video:v3');
        if(!$videos){
            $videos = D('ArticleVedio')->getArticleVedioList(1, 0, 4);
            S('Cache:Sub:Index:video:v3',$videos,900);
        }

        //获取推荐视频
        $vRecommend = S('Cache:Sub:Index:recommend:v2:'.$cityInfo["bm"]);
        if(!$vRecommend){
            $vRecommend = D('ArticleVedio')->getRecommendArticleVedio(1)[0];
            S('Cache:Sub:Index:recommend:v2:'.$cityInfo["bm"],$vRecommend,900);
        }


        // 底部二维码显示控制
        if (C("QZ_YUMING") == $SCHEME_HOST['domain']) {
            $ShowControlQRcode_n1 = 'on';
        } else {
            $ShowControlQRcode_n1 = 'off';
        }
        $this->assign("ShowControlQRcode",$ShowControlQRcode_n1);

        //导航栏标识
        //$this->assign("companys",$companys);

        //获取后台配置的TDK
        $config = [
            'cs' => $this->cityInfo['id'], //城市id
            'model' => 1, //模块 1.首页 2.装修公司 3.装修资讯
            'category' => '', //装修资讯子频道栏目
            'location' => 1, //位置 1.pc端 2.移动端
        ];
        $keys = getCommonManageTdk($keys,$config);

        $this->assign("videos", $videos);
        $this->assign("vRecommend", $vRecommend);
        $this->assign("tabIndex",0);
        $this->assign("keys",$keys);
        $this->assign("info",$info);
        $this->assign("friendLink",$friendLink);
        $this->display("index_509");
    }

    /**
     * 增加轮播显示日志
     * @param  [type] $bid [description]
     * @param  [type] $cid [description]
     * @return [type]      [description]
     */
    private function advBannerShowLog($bid,$cid,$cityid){
        $isCount = array(
            'bid' => $bid,
            'cid' => $cid,
            'dates' => date('Y').date('m').date('d')
        );
        $isCount = M("adv_banner_showlog")->field('id')->where($isCount)->find();
        if(empty($isCount)){
            //插入轮播日志
            $advLog = array(
                'bid' => $bid,
                'cid' => $cid,
                'city_id' => $cityid,
                'year' => date('Y'),
                'month' => date('m'),
                'days' => date('d'),
                'dates' => date('Y').date('m').date('d')
            );
            M("adv_banner_showlog")->add($advLog);
        }
    }

    /**
     * 获取验证码
     * @return [type] [description]
     */
    public function verify(){
        getVerify("",4,120,35);
    }

    /**
     * 装修公司获取验证码
     * @return [type] [description]
     */
    public function verifyzx(){
        getVerify("",4,150,40,15,false,false,true,false,'');

    }

    public function company(){
        $p = I("get.p");
        $url = $this->SCHEME_HOST['scheme_full'].C("QZ_YUMINGWWW")."/company/";
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
                header("Location:".$this->SCHEME_HOST['scheme_full'].$info["bm"].".".C("QZ_YUMING")."/zixun_info/".$id.".shtml");
                die();
            }
        }
        $this->_error();
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
            $url = $this->SCHEME_HOST['scheme_full'].$bm.".".C("QZ_YUMING")."/company_case/".$id;
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
            header( "Location:".$this->SCHEME_HOST['scheme_full'].$bm.".".C("QZ_YUMING")."/article_info/".$id.".html");
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
            header( "Location:".$this->SCHEME_HOST['scheme_full'].$bm.".".C("QZ_YUMING")."/caseinfo/".$id.".shtml");
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
            header( "Location:".$this->SCHEME_HOST['scheme_full'].$bm.".".C("QZ_YUMING")."/company_home/".$id);
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
            header( "Location:".$this->SCHEME_HOST['scheme_full'].$bm.".".C("QZ_YUMING")."/blog/".$id);
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
     * 老版设计师案例列表
     * @return [type] [description]
     */
    public function works(){
        $id = I("get.id");
        if(!empty($id)){
            $info = D("User")->getSingleUserInfoById($id);
            if(count($info)>0){
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location:".$this->SCHEME_HOST['scheme_full'].$info["bm"].".".C("QZ_YUMING")."/blog/".$id);
                die();
            }
        }
        $this->_error();
    }

    /**
     * 附近招标模版
     * @return [type] [description]
     */
    public function fujin(){
        //安全验证码
        $safe = getSafeCode();
        $this->assign("safecode",$safe["safecode"]);
        $this->assign("safekey",$safe["safekey"]);
        $this->assign("ssid",$safe["ssid"]);

        $tmp = $this->fetch("fujin");
        $this->ajaxReturn(array("data"=>$tmp,"info"=>"","status"=>1));
    }

    /**
     * 老版设计师列表页
     * @return [type] [description]
     */
    public function article(){
        $id = I("get.id");
        if(!empty($id)){
            $info = D("User")->getSingleUserInfoById($id);
            if(count($info)>0){
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location:".$this->SCHEME_HOST['scheme_full'].$info["bm"].".".C("QZ_YUMING")."/blog/".$id."?extra=2");
                die();
            }
        }
        $this->_error();
    }

    public function goodcase(){
        $cityInfo = $this->cityInfo;
        header( "HTTP/1.1 301 Moved Permanently");
        header("Location:".$this->SCHEME_HOST['scheme_full'].$cityInfo["bm"].".".C("QZ_YUMING")."/xgt/");
        die();
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

    public function getBJData(){
        R("Home/Zxbj/getBJData");
        die();
    }

    public function getBJResult(){
        R("Common/Zbfb/getBJResult");
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
    //装修小贴士路由
    public function guide(){
        R("Common/Zbfb/guide");
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

    /**
     * 短信发送
     *
     * @return [type] [description]
     */
    public function sendsmstx()
    {
        if (IS_POST) {
            $code = strtolower($_POST['code']);
            if (check_verify($code)) {
                //如果是查看装修公司联系方式的短信发送
                if (!empty($_POST['company_id']) && !empty($_POST['tel'])) {
                    $ip = get_client_ip();

                    //是否在查看黑名单当中
                    $logic = new \Common\Model\Logic\CompanyContactViewsLogicModel();
                    if ($logic->hasBlack($_POST['tel'], $ip)) {
                        $this->ajaxReturn(['info' => '查看失败，当前手机/IP已被封禁，请联系客服后再次尝试', 'status' => 2]);
                    }

                    //是否查看次数超过了3次
                    $count = $logic->getCount($_POST['tel']);
                    if ($count >= 2) {
                        $logic->saveBlack(['tel' => $_POST['tel'], 'ip' => $ip]);
                        $this->ajaxReturn(['info' => '查看失败，当前手机/IP已被封禁，请联系客服后再次尝试', 'status' => 2]);
                    }

                    $logData['cid'] = $_SESSION['cityId'];
                    $logData['company_id'] = $_POST['company_id'];
                    $logData['ip'] = $ip;
                    $logData['tel'] = $_POST['tel'];
                    $logic->saveLog($logData);
                }

                R('Common/Sms/sendsms');
            }
            $this->ajaxReturn(['info' => '图形验证码不正确！', 'status' => 9]);
        }
        die();
    }

    //验证验证码是否正确
    public function verifysmscode()
    {
        if (!empty($_POST['company_id']) && !empty($_POST['tel'])) {
            $userModel = new \Common\Model\UserModel();
            $company = $userModel->getUserInfoById($_POST['company_id'], $_SESSION['cityId']);
            $user['qq'] = ($company[0]['on'] == 2 && $company[0]['fake'] == 0) ? $company[0]['qq'] : OP('QZ_CONTACT_QQ1');
            $user['qq1'] = ($company[0]['on'] == 2 && $company[0]['fake'] == 0) ? $company[0]['qq1'] : OP('QZ_CONTACT_QQ2');
            $user['dz'] = $company[0]['dz'];
            $user['cal'] = ($company[0]['on'] == 2 && $company[0]['fake'] == 0) ? $company[0]['cal'] : '';
            $user['cals'] = ($company[0]['on'] == 2 && $company[0]['fake'] == 0) ? $company[0]['cals'] : OP('QZ_CONTACT_TEL400');
            $user['tel'] = ($company[0]['on'] == 2 && $company[0]['fake'] == 0) ? $company[0]['tel'] : OP('QZ_CONTACT_TEL400');
            $user['nickname'] = empty($company[0]['nickname']) == true ? '家装咨询顾问' : $company[0]['nickname'];
            $user['nickname1'] = empty($company[0]['nickname1']) == true ? '公装咨询顾问' : $company[0]['nickname1'];
            $_POST['return_data'] = $user;
        }
        R('Common/Sms/verifysmscode');
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

    public function wenda(){
        R("Home/Wenda/quickask");
        die();
    }

    /**
     * 采集数据
     * @return [type] [description]
     */
    public function hm()
    {
        die();
        $state = 0;
        $engine = "";
        if (I("get.ref") !== "") {
            //判断是否是外链
            $result = preg_match('/qizuang.com/',I("get.ref"));
            if (!$result) {
               //不是本站点击的都算是外链
               $state = 2;
            }

            //查看是否是搜索引擎访问的
            //主流搜索引擎域名
            $engineArray = array("baidu.com"=>"百度","sogou.com"=>"搜狗","so.com"=>"360","youdao.com"=>"有道","soso.com"=>"搜搜","cn.bing.com"=>"必应中国","sm.cn"=>"神马");

            foreach ($engineArray as $key => $value) {
                $result = preg_match('/'.$key.'/',I("get.ref"));
                if ($result) {
                    $engine = $value;
                    break;
                }
            }

            if (!empty($engine)) {
                $state = 1;
                cookie('QZENGINE', $engine, array('expire'=> 3600 ,'domain' => '.'.APP_HTTP_DOMAIN));
            }

            if ($state != 0) {
               cookie('QZSTATE', $state, array('expire'=> 3600 ,'domain' => '.'.APP_HTTP_DOMAIN));
            }

        }

        $host = I("get.host") != ""? I("get.host"):cookie("QZHOST");
        // cookie('QZHOST',$host, array('expire'=> 3600,'path'=>'/','domain' => '.'.APP_HTTP_DOMAIN));
        $engine = cookie('QZENGINE') == null?$engine:cookie('QZENGINE');
        $state = cookie('QZSTATE') == null?$state:cookie('QZSTATE');

        $uuid = I("get.uuid");
        if (empty($uuid)) {
            $tag = cookie("QZUUID") == null?"":cookie("QZUUID");
        }

        $tag =  I("get.tag");
        if (empty($tag)) {
            $tag = cookie("QZSRC") == null?"":cookie("QZSRC");
        }
        $tag = explode("#",$tag);
        $tag = explode("?",$tag[0]);
        $tag = $tag[0];
        //导入扩展文件
        import('Library.Org.Util.App');
        $ip = \App::get_client_ip();
        //获取友链信息
        $data = array(
            "user_agent" => $_SERVER["HTTP_USER_AGENT"],
            "ip" => $ip,
            "host" => $host,
            "time_in" => I("get.timeIn"),
            "time_out" => I("get.timeOut"),
            "visit_time" => I("get.visit_time"),
            "ck" => I("get.ck"),
            "referer" => I("get.ref"),
            "tag" => $tag,
            "ua" => md5($ip.$_SERVER["HTTP_USER_AGENT"]),
            "uuid" => $uuid,
            "engine" => $engine,
            "state" => $state,
            "time" => time(),
            "date" => date("Y-m-d")
        );
        M("market_acquisition")->add($data);

        header('HTTP/1.1 200 OK');
        header ('Content-Type: image/gif');
        header('Cache-Control:no-cache');
        $im = imagecreate(1, 1);
        $bg_color = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im, 0, 0, 1, 1, $bg_color);
        echo imagegif($im);
        imagedestroy($im);
    }

     /**
     * 获取装修三步骤
     * @return [type] [description]
     */
    private function getStep($id,$limit,$isTop){
        //根据ID查询相对应的文章类别
        $result =  D("WwwArticleClass")->getArticleClassById($id);
        $category = array();

        foreach ($result["child"] as $key => $value) {
            //获取子类的文章
            $articles = D("WwwArticle")->getArticleListByIds($value["ids"],0,$limit,"",true);

            foreach ($articles as $i => $j) {
              $value["child"][$j["cid"]]["articles"][] = $j;
            }

            foreach ($value["child"] as $k => $val) {
                $category["child"][$key]["classnames"][] = $val["classname"];
                foreach ($val["articles"] as $n => $v) {
                    $category["child"][$key]["child"][$k]["classname"] = $val["classname"];
                    $category["child"][$key]["child"][$k]["shortname"] = $val["shortname"];
                    $category["child"][$key]["child"][$k]["articles"][$n+1]["title"] = $v["title"];
                    $category["child"][$key]["child"][$k]["articles"][$n+1]["id"] = $v["id"];
                    $category["child"][$key]["child"][$k]["articles"][$n+1]["shortname"] = $v["shortname"];
                    if($n == 0){
                      $category["child"][$key]["child"][$k]["articles"][$n+1]["face"] = $v["face"];
                      $category["child"][$key]["child"][$k]["articles"][$n+1]["subtitle"] = $v["subtitle"];
                    }
                }
            }
        }

        return $category;
    }
}
