<?php

//装修公司前台

namespace Sub\Controller;
use Common\Enums\ApiConfig;
use Common\Model\Db\UserCompanyAccountLogModel;
use Common\Model\Db\UserCompanyEmployeeModel;
use Common\Model\Logic\CardLogicModel;
use Common\Model\Logic\CompanyLogicModel;
use Common\Model\Logic\SubthematicLogicModel;
use Common\Model\Logic\UserCompanyEmployeeLogicModel;
use Common\Model\Logic\UserLogicModel;
use Common\Model\UserModel;
use Sub\Common\Controller\SubBaseController;
use Common\Controller\SmsController;
class CompanyhomeController extends SubBaseController{

    public function _initialize(){
        parent::_initialize();
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);
        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location: ".$this->SCHEME_HOST['scheme_full'].$this->bm.".".C("QZ_YUMING").$uri."/");
            }
        }
        //添加顶部搜索栏信息
        $this->assign('serch_uri','companysearch');
        $this->assign('serch_type','装修公司');
        $this->assign('holdercontent','全国超过十万家装修公司为您免费设计');
        $this->assign("tabIndex",3);
        $this->assign("cityinfo",$this->cityInfo);
    }

    public function index(){
        I("get.id") == "" && $this->_error();
        $bm = $this->bm;
        //跳转到手机端
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location: ". $this->SCHEME_HOST['scheme_full']. C('MOBILE_DONAMES').'/'.$bm. $_SERVER['REQUEST_URI']);
            exit();
        }
        //若前台不显示
        $companyLogic  = new CompanyLogicModel();
        if(!$companyLogic->companyShow(I("get.id"))){
            $this->_error();
        }
        $cityInfo = $this->cityInfo;
        $id = I("get.id");
        $this->assign('comid',$id);

        //是否有登陆记录
        if(session('user_card_tel')){
            $this->assign('card_login',1);//1表示已登陆

            $this->assign('login_tel',session('user_card_tel.tel'));  //登陆手机号
            //查询订单记录
            $orderinfo = $this->getOrderByTel(session('user_card_tel.tel'),$id);

            if(count($orderinfo)){
                $this->assign('hadorder',1);//1表示有订单
            }else{
                $this->assign('hadorder',2);//2表示无订单
            }
            if($orderinfo['yz_lf_status']){
                $this->assign('order_lf_status',$orderinfo['yz_lf_status']); //量房状态
                $this->assign('orderinfo',$orderinfo);
            }else{
                $this->assign('order_lf_status',0);  //没有订单
                $this->assign('orderinfo',array());
            }
        }else {
            $this->assign('card_login', 0);//0表示未登陆
            $this->assign('login_tel', '18000000000'); //登陆手机号:默认给一个18000000000
            $this->assign('hadorder', 2);//2表示无订单
            $this->assign('order_lf_status',0); //量房状态
            $this->assign('orderinfo', array());  //订单信息为空
        }
        $userInfo = S('Cache:SubUserInfoUpdate:'.$id);
        if(!$userInfo){
            //用户信息
            $user = $this->getUserInfo($id, $cityInfo["id"]);
            if(count($user) == 0){
                $this->_error();
                die();
            }
            $userInfo["user"] = $user;

            //最新订单
            $orders = $this->getOrders($id,5,$user["on"]);
            $userInfo["orders"] = $orders;

            //获取设计师
            $designer = $this->getDesignerList($id, $user['cooperate_mode'], 5);
            $userInfo["designer"] = $designer;
            $userInfo['user']['designer_count'] = count($designer);

            //获取装修案例
            $userInfo['case'] = D("Cases")->getCaseById($id);
            $userInfo['user']['case_count'] = D("Cases")->getCaseCountById($id);

            if (!empty($userInfo['case'])) {
                foreach ($userInfo['case'] as $key => $item) {
                    $userInfo['case'][$key]["thumb"] = changeImgUrl($item["thumb"]);
                }
            }

            //获取在建工地数量
            $zjcount = D("Company")->getZjcountById($id);
            $userInfo['user']['zjcount'] = $zjcount[0]["zj_num"];

            //业主评论
            $comments = $this->getComments($id,$_SESSION["cityId"],1,3,true);
            $userInfo["comments"] = $comments["comments"];
            $userInfo["comments_count"] = $comments["count"];

            //问答
            $userInfo["wenda"] = D("Common/Ask")->getNewAnwserByCompany($id,3);

            //好评率,业主评价,案例数量
            $haoping = D("Company")->getHaopingById($id);
            $userInfo['user']["pinglun"] = $haoping[0]["ping"];

            //获取企业star
            $star = D("Company")->getStarById($id);
            //计算星星
            if($star['comment_score'] >= 9 ){
                $userInfo['user']["star"] = 5;
            }elseif($star['comment_score'] >= 8 && $star["comment_score"] < 9){
                $userInfo['user']["star"] = 4;
            }elseif($star['comment_score'] >= 6 && $star["comment_score"] < 8){
                $userInfo['user']["star"] = 3;
            }elseif($star['comment_score'] >= 4 && $star["comment_score"] < 6){
                $userInfo['user']["star"] = 2;
            }else{
                $userInfo['user']["star"] = 1;
            }

            //获取优惠活动
            $activity = $this->eventApi($id);
            $userInfo['activity'] = $activity['list'];
            $userInfo['user']['activity_count'] = $activity['num'];

            //获取公司标签
            $tags = D('CompanyTags')->getTagsById($id);
            $userInfo['tags'] = $tags;
            $userInfo['user']['tags_count'] = count($tags);

            //获取公司荣誉
            $honer = D('CompanyImg')->getHonerById($id);
            foreach ($honer as $key => $value)
            {
                //如果是七牛的图片 就把 //staticqn.qizuang.com/ 改为空字符串 统一保证无staticqn.qizuang.com域名
                if ($value['img_host']=="qiniu")
                {
                    $honer[$key]['img_path']=str_replace("//".C("QINIU_DOMAIN")."/","",$value['img_path']);
                }else
                {
                    $honer[$key]['img_path']="upload/company/m_".$value['img_path'];
                }
            }
            $userInfo['honer'] = $honer;
            $userInfo['user']['honer_count'] = count($honer);

            //获取其他5家公司
            $orderby = I('session.orderby');
            $map = I('session.condition');
            switch ($orderby) {
                case 'star'://信赖度 | 口碑
                    $result = D("Common/Company")->getStarList($map,$pagesize= 0,$pageRow = 10);
                    break;
                case 'liangfang'://量房
                    $result = D("Common/Company")->getLiangfangList($map,$pagesize= 0,$pageRow = 10);
                    break;
                case 'qiandan'://签单
                    $result = D("Common/Company")->getQiandanList($map,$pagesize= 0,$pageRow = 10);
                    break;
                default:
                    // 综合实力
                    $map['cs'] = $cityInfo["id"];
                    $result = D("Common/Company")->getShiliList($map,$pagesize= 0,$pageRow = 10);
            }
            $elsecompany = [];      //其他5家公司
            foreach ($result["result"] as $k => $v){
                if(empty($v['logo'])){
                   $v['logo'] = C("QINIU_SCHEME").'://'.C("QINIU_DOMAIN").'/Public/default/images/default_logo.png';
                }
                if(count($elsecompany) < 5 && $v['id'] != $id){
                    array_push($elsecompany,$v);
                }
            }
            $userInfo['elsecompany'] = $elsecompany;


            $userInfo['user']['cal'] = $this->getCalNumber($userInfo['user']['cal']);
            S('Cache:SubUserInfoUpdate:'.$id,$userInfo,900);

        }
        //装企宣传广告图
        $bannerInfo = (new CompanyLogicModel())->getBannerInfoByCompanyId($id);

        //如果当前的装修公司不属于当前定位城市，则跳转404
        if($userInfo['user']['cs'] != $cityInfo['id'] && $cityInfo['id'] !='000001'){
            $this->_error();
            die();
        }

        //最新入驻装修公司
        $newCompany = D("Company")->getNewCompanyByCid($userInfo['user']['cs']);
        $friendLink["newCompanyList"]["list"] = $newCompany;
        unset($newCompany);

        if($userInfo['user']['video'] == ''){
            $userInfo['user']['video'] = OP('videoQizuang480');
            $userInfo['user']['video_type'] = 'jw';
            $userInfo['user']['isautoplay'] = 'false';
            $userInfo['user']['video_image'] = '/assets/common/plugin/jwplayer/videoface640.jpg';
        }else{
            $filetype = trim(substr(strrchr($userInfo['user']['video'], '.'), 1));
            if($filetype == 'mp4'){
                $userInfo['user']['video_type'] = 'jw';
                $userInfo['user']['isautoplay'] = 'true';
            }
        }

        //获取3D效果列表
        $threedlist = D('Cases')->getThreedListByComId(0,6,$id,strval($_SESSION["cityId"]),array(4),'',1);
        //seo 标题/描述/关键字
        $citys = json_decode($userInfo["citys"],true);
        $keys["title"] = $userInfo["user"]["jc"]."_".$userInfo["user"]["qc"]."_".$cityInfo["name"]."齐装网";
        $keys["keywords"] = $userInfo["user"]["jc"].",".$userInfo["user"]["qc"];
        $keys["description"] = $userInfo["user"]["qc"]."为您提供" .$cityInfo["name"]."地区装修设计报价单与设计方案、免费装修咨询预约等服务,还有大量装修案例效果图供您参考,".$userInfo["user"]["jc"]."为您的装修保驾护航！";
        $this->assign("keys",$keys);


        //添加装修公司点击量
        D("Common/User")->editUvAndPv(I("get.id"),"pv");
        //添加公司礼券
        $cardLogic = new CardLogicModel();
        $cardList =  $cardLogic->getCardList(I("get.id"));
        // seo异步加载弹框
        if(IS_AJAX){
            $seohtml = $this->fetch("yhq_seo");
            $this->ajaxReturn(array("error_code"=>1,"info"=>$seohtml));
        }
        //公司标识对应专题页
        $zhuanti = (new SubthematicLogicModel())->getNameByCompanyTag(I("get.id"),$_SESSION["cityId"]);
        $friendLink['zhuanti_companyhome']['list'] = $zhuanti;
        $this->assign("friendLink", $friendLink);
       // $this->assign("zhuanti",$zhuanti);
        $this->assign("cardList",$cardList);
        $this->assign("userInfo",$userInfo);
        $this->assign("tabIndexOld",1);
        $this->assign('threedlist',$threedlist);
        $this->assign('bannerInfo',$bannerInfo);
        $this->display();
    }

    //装修公司案例列表页面
    public function cases(){
        $bm = $this->bm;
        if(I("get.id") == ""){
            $this->_error();
        }
        $id = I("get.id");
        //跳转到手机端
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location: ". $this->SCHEME_HOST['scheme_full']. C('MOBILE_DONAMES').'/'.$bm. $_SERVER['REQUEST_URI']);
            exit();
        }
        //若前台不显示
        $companyLogic  = new CompanyLogicModel();
        if(!$companyLogic->companyShow(I("get.id"))){
            $this->_error();
        }

        $cityInfo = $this->cityInfo;
        $userInfo = S('Cache:SubCompanyCase:'.$bm.':'.I("get.id"));
        if(!$userInfo){
            //用户信息
            $user = $this->getUserInfo($id, $cityInfo["id"]);

            if(count($user) == 0){
                $this->_error();
                die();
            }
            $userInfo["user"] = $user;
            //家装类型列表
            $types = D("Huxing")->gethx();
            $userInfo["jzTypes"] = $types;

            //公装类型列表
            $types = D('Leixing')->getlx();

//            unset($types[5]);
//            unset($types[6]);
            foreach ($types as $k=>$v){
                $types[$k]=str_replace('装修','',$v);
            }
            $userInfo["gzTypes"] = $types;

            //在建工地类型列表
            $types = D('Huxing')->gethx();
            $userInfo["zjTypes"] = $types;

            S('Cache:SubCompanyCase:'.$bm.':'.I("get.id"),$userInfo,900);
        }

        $content = $userInfo["user"]["qc"];
        //装修案例列表
        $pageIndex = 1;
        $pageCount = 12;
         if(I('get.p') !== ""){
            $pageIndex = I('get.p');
        }

        if(I('get.cl') !== ""){
            $classid = I('get.cl');
            switch ($classid) {
                case '1':
                    $content .="家装";
                    break;
                case '2':
                     $content .="公装";
                    break;
                case '3':
                     $content .="在建工地";
                    break;
                case '4':
                     $content .="3D效果图";
                    break;
            }
        }

        if(I('get.t') !== ""){
            $type = I('get.t');
            switch ($classid) {
                case '1':
                    foreach ($userInfo["jzTypes"] as $key => $value) {
                       if($value["huxing"] ==  $type){
                            $content .=$value["name"];
                            break;
                       }
                    }
                    break;
                case '2':
                    foreach ($userInfo["gzTypes"] as $key => $value) {
                       if($value["huxing"] ==  $type){
                            $content .=$value["name"];
                            break;
                       }
                    }
                    break;
                case '3':
                    foreach ($userInfo["zjTypes"] as $key => $value) {
                       if($value["huxing"] ==  $type){
                            $content .=$value["name"];
                            break;
                       }
                    }
                    break;
            }
        }else{
            $type = '';
        }

        //输出当前Base Url
        $userInfo['url'] = '/company_case/'.I("get.id").'?cl='.$classid.'&t='.$type;
         //添加默认选中状态
        $this->assign('choose_menu', 'cl='.$classid.'&t='.$type);
        if(__SELF__ == '/company_case/'.$userInfo['user']['id']){
            $info['mobileagent'] = $this->SCHEME_HOST['scheme_full'].C('MOBILE_DONAMES').'/'.$bm.'/company_case/'.$userInfo['user']['id'];
        }
        //seo 标题/描述/关键字
        $keys["title"] = $userInfo["user"]["jc"]."装修案例效果图_".$cityInfo["name"]."齐装网";
        $keys["keywords"] = $userInfo["user"]["jc"]."装修案例,".$userInfo["user"]["jc"]."装修效果图";
        $keys["description"] = $userInfo["user"]["jc"]."为您提供众多实景装修案例效果图,设计师匠心设计让您了解当下装修潮流风格趋势。";
        $this->assign("keys",$keys);
        //如果是3D效果图的话用另一个方法获取
        if($classid == 4){
            $cases = $this->getThreedListByComId($pageIndex,$pageCount,I("get.id"),strval($_SESSION["cityId"]),$classid,$type);
        }else{
            $cases = $this->getCasesListByComId($pageIndex,$pageCount,I("get.id"),strval($_SESSION["cityId"]),$classid,$type);
        }
        $userInfo["cases"] = $cases["cases"];
        $userInfo["page"] = $cases["page"];
        $this->assign("userInfo",$userInfo);
        $this->assign("tabIndexOld",3);
        $this->assign("info",$info);

        $this->display();
    }

     //装修公司设计师页面
    public function team(){
        $bm = $this->bm;
        if(I("get.id") == ""){
            $this->_error();
        }
        $id = I("get.id");
        //跳转到手机端
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location: ". $this->SCHEME_HOST['scheme_full']. C('MOBILE_DONAMES').'/'.$bm. $_SERVER['REQUEST_URI']);
            exit();
        }
        //若前台不显示
        $companyLogic  = new CompanyLogicModel();
        if(!$companyLogic->companyShow(I("get.id"))){
            $this->_error();
        }

        $type = I("get.type");
        $bm = $this->bm;
        $cityInfo = $this->cityInfo;

        if(!empty($type)){
            $url = $this->SCHEME_HOST['scheme_full'].$bm. '.' .C('QZ_YUMING').'/company_team/'.$id;
            header( "HTTP/1.1 301 Moved Permanently" );
            header( "Location:".$url);
            die();
        }
        $userInfo = S('Cache:SubCompanyTeam:'.$bm.':'.I("get.id"));
        if(!$userInfo){

            $id = I("get.id");
            //用户信息
            $user = $this->getUserInfo($id, $cityInfo["id"]);
            if(count($user) == 0){
                $this->_error();
                die();
            }
            $userInfo["user"] = $user;
            if ($user['cooperate_mode'] == 1) {
                //获取装修公司职位信息
                $zw = D("Team")->getZwInfo($id);
            } else {
                $userCompanyEmployeeLogic = new UserCompanyEmployeeLogicModel();
                $zw = $userCompanyEmployeeLogic->getZwInfoByComId($id);
            }
            $userInfo["zw"] = $zw;

            S('Cache:SubCompanyTeam:'.$bm.':'.I("get.id"),$userInfo,900);
        }

        if ($userInfo["user"]['cooperate_mode'] == 1) {
            $cooperate_mode = 1;
        } else {
            $cooperate_mode = 2;
        }

        //seo 标题/描述/关键字
        $keys["title"] = $userInfo["user"]["jc"]."设计师团队_".$cityInfo["name"]."齐装网";
        $keys["keywords"] = $userInfo["user"]["jc"]."室内设计师,".$userInfo["user"]["jc"]."设计团队";
        $keys["description"] = $cityInfo["name"]."齐装网为您挑选".$userInfo["user"]["jc"]."设计师团队的精品装修案例,为您选择设计师提供全面便携的服务资料。";
        $this->assign("keys",$keys);

        //获取设计师列表
        $pageIndex =1;
        $pageCount = 8;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }

        $team = $this->getTeamDesignerList($id, $cooperate_mode,"",2,$pageIndex,$pageCount);

        $userInfo["team"] = $team["team"];
        $userInfo["page"] = $team["page"];

        $this->assign("userInfo",$userInfo);
        $this->assign("cooperate_mode",$cooperate_mode);
        //菜单导航
        $this->assign("tabIndexOld",2);
        $this->display();
    }

    //装修公司优惠活动
    public function event(){
        $cityInfo = $this->cityInfo;
        $bm = $this->bm;
        $companyId = I('get.id');
        $info["user"] = $this->getUserInfo($companyId,$_SESSION["cityId"]);

        if(empty($companyId)){
            $this->error();
        }

        //若前台不显示
        $companyLogic  = new CompanyLogicModel();
        if(!$companyLogic->companyShow($companyId)){
            $this->_error();
        }

        $pageIndex = 1;
        $pageCount = 10;
        $condition['cid'] = $companyId;
        $condition['check'] = array('EQ','1');
        $condition['del'] = array('EQ','1');

        if(!empty($_GET["p"])){
            $pageIndex = remove_xss($_GET["p"]);
            $pageContent ="第".$pageIndex."页";
        }

        $result = $this->getEventList($condition,$pageIndex,$pageCount);
        $list = $result["list"];
        foreach ($list as $key => $value) {
            $list[$key]['text'] = htmlToText($value['text']);
            //0 暂停 1 等待中 2正在进行 3过期
            if($value['state'] == '0'){
                $list[$key]['status'] = '0';
            }else{
                //等待中
                if(time() <= $value['start']){
                    $list[$key]['status'] = '1';
                }
                //正在进行
                if(time() >= $value['start'] && time() <= $value['end']){
                    $list[$key]['status'] = '2';
                }
                //过期
                if(time() >= $value['end']){
                    $list[$key]['status'] = '3';
                }
            }
        }

        //seo 标题/描述/关键字
        $keys["title"] = $userInfo["user"]["qc"]."优惠活动";
        $keys["keywords"] = $userInfo["user"]["qc"]."优惠活动,".$userInfo["user"]["qc"]."优惠活动";
        $keys["description"] = $userInfo["user"]["qc"]."优惠活动";
        $this->assign("keys",$keys);

        //dump($list);
        $this->assign("list",$list);
        $this->assign("page",$result["page"]);
        $this->assign("userInfo",$info);
        $this->assign("tabIndexOld",4);
        $this->display();
    }

    private function eventApi($id)
    {
        $condition['cid'] = $id;
        $condition['check'] = array('EQ', '1');
        $condition['del'] = array('EQ', '1');
        $condition['types'] = array('EQ', '1');
        $condition['start'] = array('LT', time());
        $condition['end'] = array('GT', time());

        $result = $this->getEventList($condition, 1, 5);
        return $result;
    }

    //装修公司优惠活动详情页
    public function eventView(){
        $id = I('get.id');
        $event = D('Companys')->getEvent($id);
        $companyId = $event['cid'];
        if(empty($companyId)){
            $this->_error();
        }
        //若前台不显示
        $companyLogic  = new CompanyLogicModel();
        if(!$companyLogic->companyShow($companyId)){
            $this->_error();
        }
        $info["user"] = $this->getUserInfo($companyId,$_SESSION["cityId"]);

        //0 暂停 1 等待中 2正在进行 3过期
        if($event['state'] == '0'){
            $event['status'] = '0';
        }else{
            //等待中
            if(time() <= $event['start']){
                $event['status'] = '1';
            }
            //正在进行
            if(time() >= $event['start'] && time() <= $event['end']){
                $event['status'] = '2';
            }
            //过期
            if(time() >= $event['end']){
                $event['status'] = '3';
            }
        }

        D('Companys')->updateEventViews($id);

        //seo 标题/描述/关键字
        $keys["title"] = $event['title'];
        $keys["keywords"] = $info["user"]["qc"]."优惠活动,".$info["user"]["qc"]."优惠活动";
        $keys["description"] = $event['title']."优惠活动";
        $this->assign("keys",$keys);

        $this->assign("info",$event);
        $this->assign("userInfo",$info);
        $this->assign("tabIndexOld",4);
        $this->display('event_view');
    }

    //装修公司资讯详细页面
    public function article(){
        // P.2.17.4 需求装修公司的问答、资讯、资讯详情页显示404
        $this->_error();
        exit();

        $bm = $this->bm;
        if(I("get.id") !== ""){
            $articleInfo = S('Cache:SubCompanyArticle:'.$bm.':'.I("get.id"));
            if(!$articleInfo){
                $id = I("get.id"); //文章id
                //通过文章id获取本文章所属公司信息
                $companyinfo = D("Common/Info")->getCompanyInfoByArticleId($id);
                if(empty($companyinfo)){
                    $this->_error();
                    die();
                }
                $cid = $companyinfo['id']; // 公司id
                //公司用户信息
                $user = $this->getUserInfo($cid,$_SESSION["cityId"]);
                if(count($user) == 0){
                    $this->_error();
                    die();
                }

                //获取资讯内容
                $articleInfo["user"] = $user;
                $article = $this->getArticleInfo($cid,$id,$_SESSION["cityId"],$user["on"]);
                $articleInfo["article"] = $article;
                //获取资讯分类列表
                $types = $this->getZixunTypeList($cid,$_SESSION["cityId"]);
                $articleInfo["types"] = $types;
                //本文章分类
                if ($types['zxType']) {
                    foreach ($types['zxType'] as $key => $value) {
                        if ($value['id'] == $article['type']) {
                            $articleInfo["type"] = $value['name'];
                            break;
                        }
                    }
                }


                //文章描述  取正文前面 100个字符, 过滤html ,过滤换行
                $description = mbstr($article['text'],0,1000,"utf8"); //先取1000个字符,这样做的目的是避免前面都是空格或者换行取出来的数据不对
                $description = str_replace(array("\n","\r\n","\r"), '', strip_tags($description)); //过滤html php 过滤换行
                $description = trim(mbstr($description,0,100,"utf8"))."…"; //去首位空格 拼接 ...
                $articleInfo["description"] = $description;

                S('Cache:SubCompanyArticle:'.$bm.':'.$id,$articleInfo,900);
            }

            $articleInfo["collect"] = false;
            //查询用户是否关注过该案例
            if(isset($_SESSION["u_userInfo"])){
                $count =  D("Usercollect")->getCollectCount(I("get.id"),$_SESSION['u_userInfo']['id'],5);
                if($count > 0){
                    $articleInfo["collect"] = true;
                }
            }

            //seo 标题/描述/关键字
            $keys["title"] = $articleInfo["article"]["title"]. '_' . $articleInfo["type"];
            $keys["keywords"] = $articleInfo["article"]["gjz"] ? $articleInfo["article"]["gjz"] : $articleInfo["article"]["title"];
            $keys["description"] = $articleInfo["description"];
            $this->assign("keys",$keys);

            $this->assign("cityid",$this->cityInfo['id']);

            //如果是咨询文章，显示招标弹窗按钮
            if( $articleInfo["article"]["type"] == 1){
                 $this->assign("isshow",1);
            }

            //菜单导航
            $this->assign("tabIndexOld",6);
            $this->assign("userInfo",$articleInfo);
            $this->display();
        }else{
            $this->_error();
        }
    }

    //装修公司资讯列表页
    public function zixun(){

        // P.2.17.4 需求装修公司的问答、资讯、资讯详情页显示404
        $this->_error();
        exit();

        $bm = $this->bm;
        if(I("get.id") !== ""){
            $userInfo = S('Cache:SubCompanyComment:'.$bm.':'.I("get.id"));
            if(!$userInfo){
                $id = I("get.id");
                //用户信息
                $user = $this->getUserInfo($id,$_SESSION["cityId"]);
                if(count($user) == 0){
                    $this->_error();
                    die();
                }
                $userInfo["user"] = $user;
                S('Cache:SubCompanyComment:'.$bm.':'.I("get.id"),$userInfo,900);
            }

            $content = $userInfo["user"]["qc"];
            //获取资讯分类列表
            $types = $this->getZixunTypeList(I("get.id"),$_SESSION["cityId"]);

            $userInfo["zixun_types"] = $types;
            //获取资讯列表
            $pageIndex = 1;
            $pageCount = 10;
            if(I("get.p")!== ""){
                $pageIndex = I("get.p");
            }


            if(I("get.t")!== ""){
                $type = str_replace("/","",I("get.t"));
                foreach ($userInfo["types"]["zxType"] as $key => $value) {
                    if($value["id"] == $type){
                         $content .=$value["name"];
                        break;
                    }
                }
            }

            if(I("get.act")!== ""){
                $active = str_replace("/","",I("get.act"));
            }

            //seo 标题/描述/关键字
            $keys["title"] = $content."_装修资讯";
            $keys["keywords"] = $content;
            $keys["description"] = $content."发布的装修资讯,".$content."的装修优惠信息";
            $this->assign("keys",$keys);

            $articles = $this->getArticleList(I("get.id"),$_SESSION["cityId"],$pageIndex,$pageCount,$type,$active);
            $userInfo["articles"] = $articles["info"];
            $userInfo["page"] = $articles["page"];
            $this->assign("userInfo",$userInfo);

            //菜单导航
            $this->assign("tabIndexOld",6);

            //获取报价模版
            $this->assign("order_source",108);
            $t = T("Common@Order/orderTmp");
            $orderTmp = $this->fetch($t);
            $this->assign("orderTmp",$orderTmp);
            $this->display();
        }else{
            $this->_error();
        }
    }

    //装修公司评论页
    public function comment(){
        $bm = $this->bm;
        //跳转到手机端
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location: ". $this->SCHEME_HOST['scheme_full'].  C('MOBILE_DONAMES').'/'.$bm. $_SERVER['REQUEST_URI']);
            exit();
        }
        $cityInfo = $this->cityInfo;
        if(I("get.id") !== ""){
            //若前台不显示
            $companyLogic  = new CompanyLogicModel();
            if(!$companyLogic->companyShow(I("get.id"))){
                $this->_error();
            }

            $userInfo = S('Cache:SubCompanyComment:'.$bm.':'.I("get.id"));
            if(!$userInfo){
                $id = I("get.id");
                //用户信息
                $user = $this->getUserInfo($id,$_SESSION["cityId"]);

				if(count($user) == 0){
                    $this->_error();
                    die();
                }
                $userInfo["user"] = $user;
                S('Cache:SubCompanyComment:'.$bm.':'.I("get.id"),$userInfo,900);
            }
            $pageIndex = 1;
            $pageCount = 6;
            if(I("get.p") !== ""){
                $pageIndex = I("get.p");
            }

            //seo 标题/描述/关键字
            $keys["title"] = $userInfo["user"]["jc"]."怎么样_".$userInfo["user"]["jc"]."好不好_".$cityInfo["name"]."齐装网";
            $keys["keywords"] = $userInfo["user"]["jc"]."怎么样,".$userInfo["user"]["jc"]."好不好,".$userInfo["user"]["jc"]."口碑,".$userInfo["user"]["jc"]."评价";
            $keys["description"] = $cityInfo["name"]."齐装网为您提供业主对".$userInfo["user"]["jc"]."的真实评价,给您的装修选择以客观参考。";
            $this->assign("keys",$keys);

            //业主评论
            $comments = $this->getComments(I("get.id"),$_SESSION["cityId"],$pageIndex,$pageCount,true);

            //页面canonical
            $userInfo['canonical'] = $canonical = $this->SCHEME_HOST['scheme_full'].$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $locate = strpos($canonical,'p=');
            if($locate !== false){
                $replace = substr($canonical, $locate - 1, 3 + strlen(I('get.p')));
                $userInfo['canonical'] = str_replace($replace,'',$canonical);
            }

            $userInfo["comments"] = $comments["comments"];
            $userInfo["page"] = $comments["page"];

            //获取其他靠谱公司
            $ids = [I("get.id")];
            $kaopu1 = D("Company")->getKaopu($ids,$_SESSION["cityId"],2,0,0);     //同城市会员
            $kaopu = $kaopu1;
            foreach ($kaopu1 as $k=>$v){
                array_push($ids,$v['id']);
            }
            if(count($kaopu) < 10){
                $kaopu2 = D("Company")->getKaopu($ids,$_SESSION["cityId"],5,$userInfo['user']['qx']);     //同区县
                $kaopu = array_merge($kaopu1,$kaopu2);
            }
            foreach ($kaopu2 as $k=>$v){
                array_push($ids,$v['id']);
            }
            if(count($kaopu) < 10){
                $kaopu3 = D("Company")->getKaopu($ids,$_SESSION["cityId"],5,0);     //同站点
                $kaopu = array_merge($kaopu,$kaopu3);
            }
            $kaopu = array_slice($kaopu,0,10);
            $userInfo['kaopu'] = $kaopu;

            //热评公司
            $hot = D("Comment")->getReping(I("get.id"),$_SESSION["cityId"]);
            $rand = ["怎么样"=>0,"好不好"=>1];
            foreach ($hot as $k=>$v){
                $hot[$k]['jc'] = $v['jc'].array_rand($rand);
            }
            $userInfo['hot'] = $hot;

            $this->assign("userInfo",$userInfo);
            $this->display();
        }else{
            $this->_error();
        }
    }

    //装修公司关于我们
    public function about(){
        $bm = $this->bm;
        $cityInfo = $this->cityInfo;
        if(I("get.id") !== ""){
            //若前台不显示
            $companyLogic  = new CompanyLogicModel();
            if(!$companyLogic->companyShow(I("get.id"))){
                $this->_error();
            }

            $userInfo = S('Cache:SubCompanyAbout:'.$bm.':'.I("get.id"));
            if(!$userInfo){

                $id = I("get.id");
                //用户信息
                $user = $this->getUserInfo($id, $cityInfo["id"]);
                $companyInfo = $this->getCompanyInfo($id,$_SESSION["cityId"]);
                $user = array_merge($user,$companyInfo);

                if(count($user) == 0){
                    $this->_error();
                    die();
                }

                //非会员或假会员更改联系方式
                if(!($user["on"]  == 2 && $user["fake"] ==0)){
                    $user["tel"] = OP("QZ_CONTACT_TEL400");
                    $user["cals"] = "";
                    $user["cal"] = OP("QZ_CONTACT_TEL400");
                    $user["dz"] = "";
                    $user["luxian"] = "";
                    $user["qq"] = OP("QZ_CONTACT_QQ1");
                }
                $userInfo["user"] = $user;
                foreach ( $userInfo["user"]['pictures'] as $key => $value)
                {
                    //如果是七牛的图片 就把 //staticqn.qizuang.com/ 改为空字符串 统一保证无staticqn.qizuang.com域名
                    if ($value['img_host']=="qiniu")
                    {
                        $userInfo["user"]['pictures'][$key]['img_path']=str_replace("//".C("QINIU_DOMAIN")."/","",$value['img']);
                    }else
                    {
                        $userInfo["user"]['pictures'][$key]['img_path']="upload/company/m_".$value['img'];
                    }
                }
                S('Cache:SubCompanyAbout:'.$bm.':'.I("get.id"),$userInfo,900);
            }

            //seo 标题/描述/关键字
            $citys = json_decode($userInfo["citys"],true);
            $keys["title"] = $userInfo["user"]["jc"]."简介|地址_".$cityInfo["name"]."齐装网";
            $keys["keywords"] = $userInfo["user"]["jc"]."简介,".$userInfo["user"]["jc"]."地址";
            $keys["description"] = $cityInfo["name"]."齐装网为您提供".$userInfo["user"]["jc"]."公司的发展历程、荣誉资质、地址、服务范围等详细信息,让您对公司多一份了解,使您的选择多一份保障。";
            $this->assign("keys",$keys);

            if($userInfo['user']['video'] == ''){
                $userInfo['user']['video'] = OP('videoQizuang480');
                $userInfo['user']['video_type'] = 'jw';
                $userInfo['user']['isautoplay'] = 'false';
                $userInfo['user']['video_image'] = '/assets/common/plugin/jwplayer/videoface640.jpg';
            }else{
                $filetype = trim(substr(strrchr($userInfo['user']['video'], '.'), 1));
                if($filetype == 'mp4'){
                    $userInfo['user']['video_type'] = 'jw';
                    $userInfo['user']['isautoplay'] = 'true';
                }
            }

            $this->assign("userInfo",$userInfo);
            //菜单导航
            $this->assign("tabIndexOld",5);
            $this->display();
        }else{
            $this->_error();
        }
    }

    //装修公司问答
    public function wenda(){
        $bm = $this->bm;
        $id = I("get.id");

        // P.2.17.4 需求装修公司的问答、资讯、资讯详情页显示404
        $this->_error();
        exit();

        empty($id) && $this->_error();

        $user = $this->getCompanyInfo($id,$_SESSION["cityId"]);
        if(count($user) == 0){
            $this->_error();
        }

        $pageIndex = 1;
        $pageCount = 10;
        if(!empty($_GET["p"])){
            $pageIndex = remove_xss($_GET["p"]);
            $pageContent ="第".$pageIndex."页";
        }
        $userInfo["user"] = $user;

        //获取资讯分类列表
        $types = $this->getZixunTypeList($id,$_SESSION["cityId"]);
        $userInfo["zixun_types"] = $types;


        $condition['a.uid'] = $id;
        $wenda = $this->getAskList($condition,$pageIndex,$pageCount);
        $this->assign("wenda",$wenda['list']);
        $this->assign("page",$wenda['page']);

        //seo 标题/描述/关键字
        $keys["title"] = $userInfo["user"]["qc"].'装修问答';
        $keys["keywords"] = $userInfo["user"]["qc"].'装修问答';
        $keys["description"] = $userInfo["user"]["qc"].'装修问答';
        $this->assign("keys",$keys);
        $this->assign("userInfo",$userInfo);
        //菜单导航
        $this->assign("tabIndexOld",7);
        $this->display();
    }

    //保存评论
    public function setComment(){
        if($_POST){
            //用户登录后
            if(isset($_SESSION["u_userInfo"])){
                $verify = session("geetest_verify");
                if($verify === true){
                    session("geetest_verify",null);

                    //查询当前用户的user_type
                    $u_data = D("Companys")->getVipUserInfoById($_SESSION["u_userInfo"]['id']);
                    if($u_data['classid'] != 1){
                            $this->ajaxReturn(array("data"=>"","info"=>"当前账号不可评论！","status"=>0));
                            exit();
                    }
                    $result = D("Comment")->getLastPost(session("u_userInfo.id"));
                    if (count($result) > 0) {
                        $offset = floor((time() - $result["time"])%86400/60);
                        //发送间隔
                        if($u_data['user_type'] != 3){
                            if ($offset <= 3) {
                                $this->ajaxReturn(array("data"=>"","info"=>"您的操作过于频繁，请休息3分钟后再试！","status"=>0));
                                exit();
                            }
                        }
                    }


                    import('Library.Org.Util.App');
                    import('Library.Org.Util.Fiftercontact');
                    $filter = new \Fiftercontact();
                    $data = array(
                            "text"=>$filter->filter_common(I("post.content"),array("Sbc2Dbc","filter_script","filter_empty","filter_link","filter_url",array('filter_sensitive_words',array(2,3,5)))),
                            "comid"=>I("post.id"),
                            "time"=>time(),
                            "name"=>$_SESSION["u_userInfo"]["name"],
                            "userid"=>$_SESSION["u_userInfo"]["id"],
                            "isveritfy"=>0,//默认审核
                            "cs"=>I("post.cs"),
                            "count"=>10,
                            "ip"=>\App::get_client_ip(),
                            "sj"=>I("post.sj")*2,
                            "fw"=>I("post.fw")*2,
                            "sg"=>I("post.sg")*2,
                            "step"=>I("post.step"),
                            "lp"=>$filter->fifter_contact(I("post.lp"))
                    );

                    $badwords_array = array (
                      '顾海华' => '`&[顾海华]&`',
                      '320625195109242159' => '`&[320625195109242159]&`',
                      '绑架犯' => '`&[绑架犯]&`',
                      '断子绝孙' => '`&[断子绝孙]&`',
                      '王八蛋' => '`&[王八蛋]&`',
                      '小畜牲' => '`&[小畜牲]&`',
                      '顾文新' => '`&[顾文新]&`',
                      '贼羔子' => '`&[贼羔子]&`',
                      '贼羔子' => '`&[贼羔子]&`',
                      '操你' => '`&[操你]&`',
                      '傻逼' => '`&[傻逼]&`',
                    );

                    //关键词过滤
                    $data['text'] = strtr($data['text'],$badwords_array);

                    if(strstr($data['text'],'`&[') != ''){
                        //设状态为2
                        $data['isveritfy'] = '2';
                    }

                    $result = D("Comment")->setComment($data);

                    if($result !== false){
                        //更新评论数量
                        $comid = I("post.id");
                        $tempdata['comment_count'] = M('comment')->where(array('isveritfy' => '0','comid' => $comid))->count();
                        $comment_score = M('comment')->field('avg(if(fw != 0,((fw+sg+sj)/3),null)) as score')->
                        where(array('isveritfy' => '0','comid' => $comid))->find();
                        $tempdata['comment_score'] = $comment_score['score'];
                        M("user_company")->where(array('userid' => $comid))->save($tempdata);

                        $this->ajaxReturn(array("data"=>'',"info"=>"谢谢您的评论！","status"=>1));
                    }
                }
            }else{
                $this->ajaxReturn(array("data"=>"","info"=>"用户未登录,请先登录!","status"=>0));
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"非法提交","status"=>0));
    }

    //获取活动列表
    private function getEventList($condition,$pageIndex = 1,$pageCount = 10){
        import('Library.Org.Page.LitePage');
        $result = D("Companys")->getEventList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];
        $config  = array("prev","next");
        $page = new \LitePage($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp,"num"=>$count);
	}

    //取问答列表
    private function getAskList($condition,$pageIndex = 1,$pageCount = 10){
        $pageIndex = intval($pageIndex)<=0?1:intval($pageIndex);
		import('Library.Org.Page.LitePage');
        $result = D("Common/Ask")->getAnwsersByUid($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];
        $config  = array("prev","first","last","next");
        $page = new \LitePage($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp,"num"=>$count);
    }

    private function getArticleInfo($cid,$id,$cs,$on){
        import('Library.Org.Util.Fiftercontact');
        $filter = new \Fiftercontact();
        $article = D("Info")->getArticleInfo($cid,$id,$cs);
        //处理文章中的超链接
        if($on == 2){
            //会员公司不过滤联系方式
            $article["text"] =$filter->filter_common($article["text"],array("Sbc2Dbc","filter_script","filter_empty","filter_link","filter_url"));
        }else{
            $article["text"] =$filter->filter_text($article["text"]);
        }

        //代码废弃，暂时保留
        // $pattern ='/<a\s*href\s*=[\'|"](.*?)[\'|"]>(.*?)<\/a>/is';
        // preg_match_all($pattern,$article["text"], $matches);
        // if(!empty($matches[0])){
        //     foreach ($matches[0] as $key => $value) {
        //         if(!empty($matches[2])){
        //             foreach ($matches[2] as $k => $v) {
        //                 $article["text"] = str_ireplace($value,$v,$article["text"]);
        //             }
        //         }
        //     }
        // }
        $patt = '/<img\s*alt=[\'|"](.*?)[\'|"]\s*src=[\'|"](\/upload\/.*?)[\'|"]\s*\/>/is';
        preg_match_all($patt,$article["text"], $matches);
        if(!empty($matches[2])){
            foreach ($matches[2] as $key => $value) {
                $path = $this->SCHEME_HOST['scheme_full'].C("STATIC_HOST1")."/";
                $article["text"] = str_ireplace($value, $path.$value,$article["text"]);
            }
        }

        //$article["text"] = $filter->fifter_contact($article["text"]);
        return $article;
    }

    /**
     * [getZixunTypeList description]
     * @return [type] [description]
     */
    private function getZixunTypeList($id,$cs){
        $result = D("Info")->getZixunTypeList($id,$cs);
        $types = array();
        foreach ($result as $key => $value) {
            //将优惠活动信息分离
            if($value["id"] !== "1"){
                $types["zxType"][] = $value;
            }else{
                $types["yxhd"]["hd"] = $value["yxcount"];
                $types["yxhd"]["historyhd"] = $value["wxcount"];
            }
        }
        return $types;
    }

    /**
     * 获取案例图片
     * @param  string $comid   [description]
     * @param  string $cs      [description]
     * @param  string $classid [description]
     * @return [type]          [description]
     */
    private function getCasesListByComId($pageIndex,$pageCount,$comid ='',$cs ='',$classid='',$type ='')
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Cases")->getCasesListByComIdCount($comid,$cs,$classid,$type,1);
        if($count > 0){
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \LitePage($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->pindaoShow();
            $list =D("Cases")->getCasesListByComId(($page->pageIndex-1)*$pageCount,$pageCount,$comid,$cs,$classid,$type,1);

            return array("cases"=>$list,"page"=>$pageTmp);
       }
       return null;
    }

    /**
     * 获取装修公司案例类型
     * @return [type] [description]
     */
    private function getCasesClass($id,$cs,$classid){
        $result =  D("Cases")->getCasesClass($id,$cs,$classid);
        return $result;
    }

    /**
     * 获取装修公司所有信息
     * @return [type] [description]
     */
    private function getCompanyInfo($id,$cs){
          $result = D("User")->getCompanyInfo($id,$cs);
          import('Library.Org.Util.Fiftercontact');
          $filter = new \Fiftercontact();
          $content_q1 = OP("QZ_CONTACT_QQ1");
          $content_t400 = OP("QZ_CONTACT_TEL400");
          foreach ($result as $key => $value) {
                if($key == 0){
                    $value["dz"] = str_replace("#", "",$value["dz"]);
                    $reg ='/(.*?)[.\n]/i';
                    $value["jianjie"] = $value["jianjie"]."\n"; //避免编辑时候，有序换行最后不添加换行造成的漏掉一条记录的问题
                    preg_match_all($reg, $value["jianjie"], $matches);
                    if(count($matches[1]) > 0){
                      $matches = array_filter($matches[1]);
                    }else{
                        $matches = array($value["jianjie"]);
                    }
                    foreach ($matches as $k => $v) {
                        $matches[$k] = $filter->filter_common($v,array("Sbc2Dbc","filter_script","fifter_blank","filter_tel","filter_mobile","filter_link","filter_url"));
                    }
                    $value["jianjie"] = $matches;//
                    $user = $value;
                    $user["pictures"] = array();
                }

                if(!empty($value["img"])){
                    $sub = array(
                            "img"=>$value["img"],
                            "img_host"=>$value["img_host"]
                                 );
                    $user["pictures"][]  = $sub;
                }
                //判断当前用户是否是会员 如果不是会员公司 将该公司电话和qq替换为我们的电话和qq
                if ($value['on']!=2)
                {
                    #替换电话
                    $user['tel']=$content_t400;//换成我们的电话
                    $user['cal']=$content_t400;//换成我们的电话
                    $user['cals']="";//电话前缀清空
                    $user['qq']=$content_q1;//换成我们的qq
                }
          }
          return $user;
    }

    /**
     * 获取设计师列表
     * @param  [type] $id        [用户编号]
     * @param  [type] $zw      [职位名称]
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @return [type]            [description]
     */
    private function getTeamDesignerList($id, $cooperate_mode, $zw, $zt, $pageIndex, $pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        if ($cooperate_mode == 1) {
            $count = D("User")->getTeamDesignerListCount($id,$zw,$zt);
        } else {
            $userCompanyEmployeeLogic = new UserCompanyEmployeeLogicModel();
            $count = $userCompanyEmployeeLogic->getTeamDesignerListCount($id);
        }


        if($count > 0){
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \LitePage($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->pindaoShow();

            //查询设计师资料
            if ($cooperate_mode == 1) {
                $users = D("Companys")->getDesignerListByCompany($id,$zw,$zt,($page->pageIndex-1)*$pageCount,$pageCount);
            } else {
                $users = $userCompanyEmployeeLogic->getTeamDesignerList($id, ($page->pageIndex - 1) * $pageCount, $pageCount);
            }

            $ids = array();
            foreach ($users as $key => $value) {
                $ids[] = $value["uid"];
            }

            if(!empty($ids)){
                //查询设计师的设计作品
                $cases = D("Cases")->getDesinerCase($ids, $id);
                foreach ($users as $key => $value) {
                    foreach ($cases as $k => $val) {
                        if($value["uid"] == $val["userid"]){
                           $users[$key]["child"][]=$val;
                        }
                    }
                }
            }

            $dids = array();
            foreach ($users as $key => $value) {
                $dids[] = $value["id"];
            }
            if(!empty($dids)){
                //查询设计师的设计作品
                $cases = D("Cases")->getDesinerCase1($dids, $id);
                foreach ($users as $key => $value) {
                    foreach ($cases as $k => $val) {
                        if($value["id"] == $val["userid"]){
                            $users[$key]["case3"][]=$val;
                        }
                    }
                }
            }
            return array("team"=>$users,"page"=>$pageTmp);
        }
        return null;
    }

    /**
     * 获取业主评论
     * @param  [type] $id    [公司编号]
     * @param  [type] $cs    [所在城市]
     * @param  [type] $limit [显示数量]
     * @param  [type] $reply [是否显示回复]
     * @return [type]        [description]
     */
    private function getComments($id,$cs,$pageIndex,$pageCount,$reply)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Comment")->getCommentByComIdCount($id,$cs);
        if($count > 0){
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \LitePage($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();

            $comments = D("Comment")->getCommentByComId($id,$cs,($page->pageIndex-1)*$pageCount,$pageCount,$reply);
            $userModel = new UserModel();
            $idlist = [];
            foreach ($comments as $key => $value) {
                if(empty($value['logo'])){
                    $idlist[] = $value['userid'];
                }
                $total = $value["count"];
                if($value["sj"] != 0 && $value["fw"]!= 0 && $value["sg"]!= 0){
                    $total = round((($value["sj"]+$value["fw"]+$value["sg"])/3),1);

                }
                $comments[$key]["totalCount"] = $total;

                $comments[$key]['logo'] = changeImgUrl($value['logo'],2);

                $imglist = D("Comment")->getCommentImgsByCommentid($value['id']);
                $showlist = [];
                if($imglist){
                    foreach ($imglist as $k => $v){
                        $showlist[$k] = changeImgUrl($v['imgurl'],2);
                    }
                }
                $comments[$key]['comment_imgs'] = $showlist;
                $comments[$key]['comment_countimgs'] = count($showlist);

            }
            if(!empty($idlist)){
                $userlist = $userModel->getUserinfoListByIds($idlist);
                foreach ($comments as $key => $val) {
                    if(empty($val['logo'])){
                        foreach ($userlist as $k => $v){
                            if($v['id'] == $val['userid']){
                                $comments[$key]['logo'] = $v['logo'] ? $v['logo'] : '';
                                break;
                            }
                        }
                    }

                    // 用户默认头像
                    if (empty($comments[$key]['logo'])) {
                        $comments[$key]['logo'] = changeImgUrl('public/default/images/default_avator.jpg');
                    }
                }
            }

            return array("comments"=>$comments,"page"=>$pageTmp,"count"=>$count);
        }
        return null;
    }

    /**
     * 获取设计师列表
     * @param  [type] $id [公司编号]
     * @return [type]     [description]
     */
    private function getDesignerList($id, $cooperate_mode = 1, $limit){
        if ($cooperate_mode == 1) {
            $designer = D("User")->getDesignerListByCompany($id,$limit);
            foreach ($designer as $key => $val) {
                $designer[$key]['company_type'] = 1;
            }
        } elseif ($cooperate_mode == 2) {       // 新签返
            $userCompanyEmployeeModel = new UserCompanyEmployeeModel();
            $designer = $userCompanyEmployeeModel->getDesignerListByCompany($id, $limit);
            foreach ($designer as $key => $val) {
                switch ($val['zw']) {
                    case 2:
                        $designer[$key]['zw'] = '设计师';
                        break;
                    case 3:
                        $designer[$key]['zw'] = '首席设计师';
                        break;
                    case 4:
                        $designer[$key]['zw'] = '设计总监';
                        break;
                    default :
                        $designer[$key]['zw'] = '设计师';
                        break;
                }
                $designer[$key]['company_type'] = 2;
            }

        } else {
            $designer = [];
        }
        return $designer;
    }

    /**
     * 获取最新的订单信息
     * @param  [type] $id    [description]
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    private function getOrders($id,$limit,$on){
        $orders = D("Orders")->getOrdersByComId($id,$limit);
        if(count($orders) < 5){
            //如果实际订单小于5个的时候，拿全部订单补充
            //计算偏移量
            $offset = 5 - count($orders);
            //取全部50条最新订单
            $allOrders = D("Common/Orders")->getNewOrders(50,4);
            //打乱订单
            shuffle($allOrders);
            $allOrders = array_slice($allOrders,0, $offset);
            //合并数组
            for ($i=0; $i <count($allOrders); $i++) {
                //有些订单没有填写小区，在尺给一个默认的小区
                if (empty($allOrders[$i]["xiaoqu"])) {
                    $allOrders[$i]["xiaoqu"] = '世纪佳园';
                }
                $sub = array(
                    "addtime"=>$on == 2?$allOrders[$i]["time"]:time(),
                    "name"=>$allOrders[$i]["name"],
                    "mianji"=>$allOrders[$i]["mianji"],
                    "lx"=>$allOrders[$i]["lx"],
                    "huxing"=>$allOrders[$i]["hxname"],
                    "yusuan"=>$allOrders[$i]["yusuan"],
                    "xiaoqu"=>$allOrders[$i]["xiaoqu"]
                            );
                array_push($orders, $sub);
            }
        }
        return $orders;
    }

    //电话处理~
    private function getCalNumber($tel){
        if(strstr($tel,"-")){
            return $tel;
        }elseif(strstr($tel,"400")){
            if(strlen($tel) == 10){
                $s = str_split($tel);
                $s['2'] = $s['2'].'-';
                $s['5'] = $s['5'].'-';
                return implode($s);
            }
        }
        return $tel;
    }

    /**
     * 获取资讯列表
     * @param  [type] $id [公司编号]
     * @param  [type] $cs [所在城市]
     * @return [type]     [description]
     */
    private function getArticleList($id,$cs,$pageIndex,$pageCount,$type ='',$active = '',$notClass="")
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Info")->getArticlesByComIdCount($id,$cs,$type,$active,$notClass,false);
        if($count > 0){
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \LitePage($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();

            $info = D("Info")->getArticlesByComId($id,$cs,($page->pageIndex-1)*$pageCount,$pageCount,$type,$active,$notClass,false);
            return array("info"=>$info,"page"=>$pageTmp);
        }
        return null;
    }

    /**
     * 获取用户信息
     * @param  [type] $id [用户编号]
     * @param  [type] $cs [所在城市]
     * @return [type]     [description]
     */
    private function getUserInfo($id,$cs){
        $result =  D("User")->getUserInfoById($id,$cs);
        $fendianresult =  D("User")->getUserFendianInfoById($id,$cs);
        $pinglunresult =  D("User")->getUserPinlunInfoById($id,$cs);
		$user = array();
        if($result[0]["id"] != 0){
            $reg ='/[8|4][0]{2}\d+/';
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            $contact_q1 = OP('QZ_CONTACT_QQ1');
            $contact_q2 = OP('QZ_CONTACT_QQ2');
            $contact_t400 = OP("QZ_CONTACT_TEL400");
            $contact_address = OP("QZ_CONTACT_ADDRESS");

            foreach ($result as $key => $value) {
                if($key == 0){
                    $user["id"] = $value["id"];
                    $user["classid"] = $value["classid"];
                    $user["hengfu"] = $value["hengfu"];
                    $user["img_host"] = $value["img_host"];
                    $user["on"] = $value["on"];
                    $user["qc"] = $value["qc"];
                    $user["jc"] = $value["jc"];
                    $user["kouhao"] = $filter->filter_common($value["kouhao"],array("Sbc2Dbc","fifter_blank","filter_tel","filter_mobile","filter_link","filter_url"));
                    $user["logo"] = empty($value["logo"]) == true? C("QINIU_SCHEME").'://'.C("QINIU_DOMAIN"). '/Public/default/images/default_logo.png':$value["logo"];
                    $user["pv"] = $value["pv"];
                    $user["jianjie"] = $filter->filter_common($value["jianjie"],array("Sbc2Dbc","filter_script","fifter_blank","filter_tel","filter_mobile","filter_link","filter_url"));
                    $user["jiawei"] = $value["jiawei"];
                    $user["fake"] = $value["fake"];
                    $user["nickname"] = empty($value["nickname"])== true?"家装咨询顾问":$value["nickname"];
                    $user["nickname1"] = empty($value["nickname1"])== true?"公装咨询顾问":$value["nickname1"];
                    $user["area"] = $value["area"];
                    $user["fw"] = $value["fw"];
                    $user["fg"] = $value["fg"];
                    $user["dcount"] = $value["dcount2"] ? $value["dcount2"] : ($value["dcount"]);
                    $user["ccount"] = $value["ccount"];
                    $user["avgsj"] = round($value["avgsj"],1);
                    $user["avgfw"] = round($value["avgfw"],1);
                    $user["avgsg"] = round($value["avgsg"],1);
                    $user["avgcount"] = round($value["avgcount"],1) == 0?1:round($value["avgcount"],1);
                    $user["casecount"] = $value["casecount"];
                    $user["video"] = $value["video"];
                    $user["cooperate_mode"] = $value["cooperate_mode"];
                    preg_match($reg,$value["qq"],$m);
                    if(!empty($m)){
                      $user["qq400"] =  $value["qq"];
                    }

                    preg_match($reg,$value["qq1"],$m);
                    if(!empty($m)){
                      $user["qq401"] =  $value["qq1"];
                    }

                    $user["qq"] =  ($value["on"] == 2 && $value["fake"] ==0)?$value["qq"]:$contact_q1;
                    $user["qq1"] = ($value["on"] == 2 && $value["fake"] ==0)?$value["qq1"]:$contact_q2;
                    $user["dz"] = $value["dz"];
                    $user["cal"] = ($value["on"] == 2 && $value["fake"] ==0)?$value["cal"]:"";
                    $user["cals"] =($value["on"] == 2 && $value["fake"] ==0)?$value["cals"]:$contact_t400;
                    $user["tel"] = ($value["on"] == 2 && $value["fake"] ==0)?$value["tel"]:$contact_t400;

                    $user["cs"] = $value["cs"];
                    $user["qx"] = $value["qx"];
                    $user["cityname"] = $value["cityname"];
                    $user["area_name"] = $value["area_name"];
                    $user["gm"] = $value["gm"];
                    $user["chengli"] = date("Y年m月",strtotime($value["chengli"]));
                    //取计划任务中的数据计算好评率
					if( !empty($pinglunresult['ping']) ){
						$user['haopinglve'] = round(($pinglunresult['haoping']/$pinglunresult['ping'])*100,2);
					}
					//此处代码，茁壮性有问题导致产生 NAN 数据，使得数据无法被json_encode 到底后面bug
					if( !empty($value['oldcount']) ){
						$user["oldgood"] =round(($value["oldgood"]/$value["oldcount"])*100,2);
					}else{
						$user["oldgood"] = 100;
					}
					if( !empty($value['newcount']) ){
						$user["good"] = round(($value["good"]/$value["newcount"])*100,2);
					}else{
						$user["good"] = 100;
					}
					$user["evaluation"] = $user["avgcuont"];
                    if($value["avgsj"] != 0 && $value["avgfw"] != 0 && $value["avgsg"] != 0){
                        $user["evaluation"] = round(($value["avgsj"]+$value["avgfw"]+$value["avgsg"])/3,2);
                    }
                }
                if(!empty($fendianresult)){
                    $sub = array(
                        "name"=>$fendianresult["shortname"],
                        "tel" => ($value["on"] == 2 && $value["fake"] ==0)?$fendianresult["htel"]:$contact_t400,
                        "addr"=>($value["on"] == 2 && $value["fake"] ==0)?$fendianresult["addr"]:$contact_address,
                        "qq"=> ($value["on"] == 2 && $value["fake"] ==0)?$fendianresult["qq3"]:$contact_q1,
                        "qq1"=>($value["on"] == 2 && $value["fake"] ==0)?$fendianresult["qq4"]:$contact_q1,
                        "nickname"=>empty($fendianresult["nickname2"])== true?"家装咨询顾问":$fendianresult["nickname2"],
                        "nickname1"=>empty($fendianresult["nickname3"])== true?"家装咨询顾问":$fendianresult["nickname3"]
                             );
                    $user["child"][] = $sub;
                }
            }

            //查询签返公司订单包都情况
            $logic = new UserLogicModel();
            $packList = $logic->getPackageInfo([$id]);
            if (count($packList) > 0) {
                $user["deposit_money"] = (int)$packList[0]["deposit_money"];
            }
            //获取装修公司签返保证金
            $companyLogic = new CompanyLogicModel();
            $cashDepositInfo = $companyLogic->getStatisticsCashDeposit($user['id']);
            $user['cash_deposit'] = !empty($cashDepositInfo) ? intval($cashDepositInfo['cash_deposit']) : 0;
        }
        return $user;
    }

    /**
     * 获取3D效果图图片
     * @param  string $comid   [description]
     * @param  string $cs      [description]
     * @param  string $classid [description]
     * @return [type]          [description]
     */
    private function getThreedListByComId($pageIndex,$pageCount,$comid ='',$cs ='',$classid='',$type ='')
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $count = D("Cases")->getCasesListByComIdCount($comid,$cs,$classid,$type,1);
        if($count > 0){
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \LitePage($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->show();
            $list =D("Cases")->getThreedListByComId(($page->pageIndex-1)*$pageCount,$pageCount,$comid,$cs,$classid,$type,1);

            return array("cases"=>$list,"page"=>$pageTmp);
       }
       return null;
    }

    //根据id获取优惠券信息
    public function getCardInfoByCardid(){
        $cardid = I('get.cardid');
        $cardinfo = CardLogicModel::getCardInfoByCardid($cardid);
        //如果登陆，查询是否领取过该优惠券
        if(session('user_card_tel')){ //已登录
            $hadreceived = CardLogicModel::checkHadReceived($cardinfo['record_id'],session('user_card_tel.tel'));
            if($hadreceived){
                $cardinfo['hadreceived'] = 1; // 1表示已领取
            }else{
                $cardinfo['hadreceived'] = 0; // 0表示未领取
            }

        }else{
            $cardinfo['hadreceived'] = 0; // 0表示未领取
        }
        $cardinfo['start'] = date('Y.m.d',$cardinfo['start']);
        $cardinfo['end'] = date('Y.m.d',$cardinfo['end']);
        $this->ajaxReturn(['error_code' => ApiConfig::REQUEST_SUCCESS, 'error_msg' => '请求成功', 'data'=>$cardinfo]);
    }

    //手机号登陆
    public function login()
    {
        if($_POST){
            session('user_card_tel',null);
            $tel = $_POST['tel'];
            $getreturn = SmsController::verifysmscodeNew();//校验验证码
//            $getreturn['status'] = 1;
            if($getreturn['status'] == 1){
                $saveinfo['tel'] = $tel;
            }else{
                $this->ajaxReturn(['error_code'=>ApiConfig::VERIFY_CODE_ERROR,'error_msg'=>'验证码错误']);
            }
            //获取是否有订单
            $list = CardLogicModel::getOrdersCountByTel($tel,$_POST['comid']);
            if($list){  //表示有订单
                $saveinfo['hadorder'] = 1;
                session('user_card_tel',$saveinfo,60*30);
                if($list['yz_lf_status'] ==1){  //已量房， 查询是否领取过该礼券
                    //先查询是否已经领用过了
                    $hadreceived = CardLogicModel::checkHadReceived($_POST['cardid'],$tel);

                    if($hadreceived){  //已领取
                        $list['hadreceive_card'] = 1;  // 1表示已领取该优惠券
                    }else{
                        $list['hadreceive_card'] = 0; // 0表示未领取该优惠券
                    }
                }
                else{
                    $list['hadreceive_card'] = 0; // 0表示未领取该优惠券
                }

                $this->ajaxReturn(['error_code'=>ApiConfig::REQUEST_SUCCESS,'error_msg'=>'登陆成功','data'=>$list]);
            }else{  //表示没有订单
                $saveinfo['hadorder'] = 0;
                session('user_card_tel',$saveinfo,60*30);
                $this->ajaxReturn(['error_code'=>ApiConfig::USER_NO_ORDER,'error_msg'=>'登陆成功']);
            }
        }
    }


    //手机号和公司id查询是否有订单以及订单状态
    private function getOrderByTel($tel,$comid){
        $map = [];
        $map['o.tel_encrypt'] = array('EQ',md5($tel.C('QZ_YUMING')));
        $map['r.comid'] = array('EQ',$comid);
        $list = M('orders')->alias('o')
            ->where($map)
            ->field('o.id orderid,r.liangfang yz_lf_status,r.yz_lf_time')
            ->join('qz_order_company_review r on r.orderid = o.id')
            ->order('r.liangfang asc,r.time desc,r.lf_time desc')
            ->find();
        return $list;
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
        $cardinfo =  CardLogicModel::getCardinfoByRecordId($cardid);  //优惠券信息


        //获取小区信息
        $cardinfo['xiaoqu'] = D('Orders')->getXiaoQuByOrderId($orderid);


        //先查询是否已经领用过了
        $hadreceived = CardLogicModel::checkHadReceived($cardinfo['record_id'],$tel);

        if($hadreceived){
            $this->ajaxReturn(['error_code'=>ApiConfig::ERROR_FOR_RECEIVE_AGAIN,'error_msg'=>'无法重复领取该优惠券!']);
        }

        //再查询优惠券是否全部领完
        $receivecount = CardLogicModel::getReceiveCountByid($cardinfo['record_id']);
        if($receivecount == $cardinfo['amount']){
            $this->ajaxReturn(['error_code'=>ApiConfig::ERROR_FOR_CARD_OVER,'error_msg'=>'优惠券已领完！']);
        }
        while(1){
            $cardNo = getRandom(10);
            $weiyi = CardLogicModel::checkOnly($cardNo);
            //查询是否是唯一的
            if($weiyi){
                break;
            }
        }

        $return = CardLogicModel::addReceiveCardLog($tel,$cardid,$cardNo,$cardinfo['xiaoqu']);
        if($return){  //表示成功
            $type = 1;
            $cityinfo = $this->cityInfo;
            $cs = $cityinfo['id'];
            if($cardinfo['name']){
                $info['name'] = $cardinfo['name'];
            }
            $info['card_number'] = $cardNo;
            $info['tel'] = $tel;
            $info['xiaoqu'] = $cardinfo['xiaoqu'];
            $info['add_time'] = time();
            $this->sendMessage($info,$type,$cs,$comid);

            //获取装修公司的安全手机号
            $comsafetel = CardLogicModel::getCompanySafeTelByComId($comid);
            //发送短信给业主
            $this->sendSmsForGetCard(session('user_card_tel.tel'),1,$cardinfo['name'],$cardinfo['jc'],$cardNo);
            //发送 短信给装修公司安全手机号
            $this->sendSmsForGetCard($comsafetel,2,$cardinfo['name'],$cardinfo['jc'],$cardNo);

            $receivecardinfo = [];
            $receivecardinfo['id'] = $return;
            $this->ajaxReturn(['error_code'=>ApiConfig::REQUEST_SUCCESS,'error_msg'=>'领用成功','data'=>$receivecardinfo]);

        }else{   // 表示领用失败
            $this->ajaxReturn(['error_code'=>ApiConfig::ERROR_TO_ADD_MYSQL,'error_msg'=>'领用失败']);
        }
    }


    public function sendSmsForGetCard($tel,$type = 1,$cardName,$comJc,$cardNo){
        if(!$tel){
            return false;
        }
        if($type == 1){   // 1表示发送给用户的短信模板
            $muban =  '【齐装网】尊敬的用户您好！恭喜您成功抢到"'.$comJc.'"的优惠券“['.$cardName.']”！优惠券编号['.$cardNo.']，请在消费前向商家出示并使用。回t退订';
        }else{          //2表示发送给客户的短信模板
            $muban = '【齐装网】尊敬的客户您好！手机号为['.$tel.']的业主刚刚领取了您的优惠券['.$cardName.']。回t退订';
        }
        //发短信？？
        if (11 == strlen($tel)){
            $sms_tel = $tel;
            //发送短信
            $dataSmsYx = array(
                "tpl"         => '',
                "tel"         => $sms_tel,
                "type"        => "nil",
                "sms_channel" => "yunrongyx"
            );

            $smsXuanZhe = 'tz'; //标记通知
            if ('yx' == $smsXuanZhe){
                //营销类
                //dump($dataSmsYx); //生产环境必须是注释状态
                sendSmsQz($dataSmsYx);
            } else if ('tz' == $smsXuanZhe) {
                //通知类
                $smsdatav[]          = OP('QZ_CONTACT_TELNUM400');
                $smsdata['tel']      = $sms_tel;
                $smsdata['type']     = 'nil';
                $smsdata['tpl']     = $muban;
                $smsdata['sms_channel'] = 'yunrongyx';
//                $smsdata['variable'] = $smsdatav;
                sendSmsQz($smsdata);
            }
        }
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

        $title = '用户领取通知';
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
        return M("user_notice_related")->add(array('noticle_id'=>$noticle_id,'uid'=>$comid));
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
        $savedata['yz_channel'] = 4; //4表示PC端量房
        $return = CardLogicModel::signLiangFang($map,$savedata);
        if($return){
            $this->ajaxReturn(['error_code'=>ApiConfig::REQUEST_SUCCESS,'error_msg'=>'量房成功']);
        }else{
            $this->ajaxReturn(['error_code'=>ApiConfig::REQUEST_FAILL,'error_msg'=>'操作失败！']);
        }
    }


}
