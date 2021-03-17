<?php
/**
 * 移动版装修公司主页
 */
namespace Mobile\Controller;
use Common\Model\Db\CommentModel;
use Common\Model\Logic\CasesLogicModel;
use Common\Model\Logic\CompanyLogicModel;
use Common\Model\Logic\SubthematicLogicModel;
use Mobile\Common\Controller\MobileBaseController;
use Common\Enums\CompanyHomeEnum;

class CompanyhomeController extends MobileBaseController{
    public $jieduan = array(
        "3" => "开工阶段",
        "4" => "水电阶段",
        "5" => "泥木阶段",
        "6" => "油漆阶段",
        "7" => "竣工阶段",
    );

    //新签返员工职位
    private $companyEmployeeZw = [
        1 => '客服',
        2 => '设计师',
        3 => '首席设计师',
        4 => '设计总监',
    ];

    //新签返员工经验
    private $companyEmployeeExperience = [
        1 => '应届',
        2 => '1年',
        3 => '2年',
        4 => '3-5年',
        5 => '5-8年',
        6 => '8-10年',
        7 => '10年以上',
    ];
    private $user = null;
    public function _initialize(){
        parent::_initialize();
        $cityInfo = $this->cityInfo;
        $SCHEME_HOST = $this->SCHEME_HOST;

        if(I("get.id") == ""){
            $this->_error();
        }

        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);
        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location: ". $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'].$uri."/");
            }
        }

        //获取装修公司信息
        $user = $this->getUserInfo(I("get.id"),$cityInfo["id"]);
        if(count($user) > 0){
            $this->user = $user;
        }else{
            $this->_error();
        }
    }

    public function index(){
        $id = I("get.id");

        //若前台不显示
        $companyLogic  = new CompanyLogicModel();
        if(!$companyLogic->companyShow(I("get.id"))){
            $this->_error();
        }

        $info["user"] =  $this->user;

        $cache_key = $id . ':' . $this->cityInfo["id"];
        $info['cases'] = S('Cache:M:Companyhome:Index:cases:' . $cache_key);
        if (!$info['cases']) {
            $info['cases'] = D("Cases")->getCasesListByComId(0,3,$id,$this->cityInfo["id"]);
            S('Cache:M:Companyhome:Index:cases:' . $cache_key, $info['cases'], 120);
        }

        $cityInfo = $this->cityInfo;
        //获取装修公司的文化信息图片
        $info["imgs"] = S('Cache:M:Companyhome:Index:imgs:' . $id);
        if (!$info['imgs']) {
            $info["imgs"] = D("User")->getCompanyImg($id);
            S('Cache:M:Companyhome:Index:imgs:' . $id, $info['imgs'], 900);
        }

        //关键字、描述、标题
        $basic["head"]["title"] = $info["user"]["qc"]."-";
        $basic["head"]["keywords"] =  $info["user"]["qc"].",".$info["user"]["qc"]."怎么样";
        $basic["head"]["description"] =  $info["user"]["qc"].$cityInfo["name"]."为您提供"
                                .$cityInfo["name"]."装修设计方案与报价、".$cityInfo["name"]."装修优惠、免费装修咨询预约以及装修案例效果图。";
        $basic["body"]["title"] = $info['user']['jc'];
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("basic",$basic);
        $info['canonical'] = $this->SCHEME_HOST['scheme_full'] . $cityInfo['bm'].'.'.C('QZ_YUMING'). str_ireplace('/'.$cityInfo['bm'], '', $_SERVER['REQUEST_URI']);
        $this->assign("fi", 321);
        $this->assign("info",$info);


        //获取店铺优惠券列表
        $cardlist = S('Cache:M:Companyhome:Index:cardlist:' . $id);
        if ($cardlist === false) {
            $cardlist = D('Card')->getSpecialCardByCompanyId($id);
            foreach ($cardlist as $key => $value){
                if($value['active_type'] == 2){
                    $cardlist[$key]['name'] = '满' . $value['money3'] . '元可领';
                }else{
                    if($value['money1'] > 0){
                        $cardlist[$key]['name'] = '满'.$value['money1'].'元可用';
                    }else{
                        $cardlist[$key]['name'] = '立减'.$value['money2'].'元';
                    }
                }
            }
            S('Cache:M:Companyhome:Index:cardlist:' . $id, $cardlist, 900);
        }

        $cardCount = count($cardlist);
        $this->assign('cardlist',$cardlist);
        $this->assign('cardcount',$cardCount);

        //公司是否是假会员？
        if($info['user']['on'] == 2 && $info['user']['fake'] == 1){  // on为2表示会员状态。 fake为1表示假会员
            $cardlists = S('Cache:M:Companyhome:Index:cardlists:' . $id);
            if ($cardlists === false) {
                $cardlists = D('Card')->getTongYongCardByJiaComid($id);
                S('Cache:M:Companyhome:Index:cardlists:' . $id, $cardlists, 900);
            }

            if($cardlists){
                $cardlists['money1'] = $cardlists['money1'] ? (int)$cardlists['money1'] : 0;
                $cardlists['money2'] = $cardlists['money2'] ? (int)$cardlists['money2'] : 0;
                $cardlists['money3'] = $cardlists['money3'] ? (int)$cardlists['money3'] : 0;
                if($cardlists['active_type'] == 2){
                    $cardlists['name'] = '满'.$cardlists['money3'].'元可领';
                }else{
                    if($cardlists['money1'] > 0){
                        $cardlists['name'] = '满'.$cardlists['money1'].'元可用';
                    }else{
                        $cardlists['name'] = '立减'.$cardlists['money2'].'元';
                    }
                }
                $cardlist[0] = $cardlists;
                $cardCount = count($cardlist);
                $this->assign('cardlist',$cardlist);
                $this->assign('cardcount',$cardCount);
            }else{
                $this->assign('cardlist',array());
                $this->assign('cardcount',0);
            }
        }

        //获取src
        $src = I('get.src','');
        $this->assign("src", $src);
        $this->assign("nav_index",'bh');
        $this->display();
    }

    public function indextwo(){
        $id = I("get.id");

        //若前台不显示
        $companyLogic  = new CompanyLogicModel();
        if(!$companyLogic->companyShow(I("get.id"))){
            $this->_error();
        }

        $info["user"] =  $this->user;
        $cityInfo = $this->cityInfo;

        //关键字、描述、标题
        $basic["head"]["title"] = $info["user"]["jc"]."_".$info["user"]["qc"]."_".$cityInfo['name'].'齐装网';
        $basic["head"]["keywords"] =  $info["user"]["jc"].",".$info["user"]["qc"];
        $basic["head"]["description"] = $info["user"]["qc"]."为您提供".$cityInfo["name"]."地区装修设计报价单与设计方案、免费装修咨询预约等服务,还有大量装修案例效果图供您参考,".$info["user"]["jc"]."为您的装修保驾护航！";

        $basic["body"]["title"] = $info['user']['jc'];
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("basic",$basic);
        $info['canonical'] = $this->SCHEME_HOST['scheme_full'] . $cityInfo['bm'].'.'.C('QZ_YUMING'). str_ireplace('/'.$cityInfo['bm'], '', $_SERVER['REQUEST_URI']);

        // 获取店铺优惠券列表
        $cardlist = S('Cache:M:Companyhome:Index:cardlist:' . $id);
        if (!$cardlist) {
            $cardlist = D('Card')->getSpecialCardByCompanyId($id);
            foreach ($cardlist as $key => $value){
                if($value['active_type'] == 2){
                    $cardlist[$key]['name'] = '满' . $value['money3'] . '元可领';
                }else{
                    if($value['money1'] > 0){
                        $cardlist[$key]['name'] = '满'.$value['money1'].'元可用';
                    }else{
                        $cardlist[$key]['name'] = '立减'.$value['money2'].'元';
                    }
                }
            }
            S('Cache:M:Companyhome:Index:cardlist:' . $id, $cardlist, 900);
        }

        $cardCount = count($cardlist);
        $this->assign('cardlist',$cardlist);
        $this->assign('cardcount',$cardCount);

        // 公司是否是假会员？
        if($info['user']['on'] == 2 && $info['user']['fake'] == 1){  // on为2表示会员状态。 fake为1表示假会员
            $cardlists = S('Cache:M:Companyhome:Index:cardlists:' . $id);
            if ($cardlists === false) {
                $cardlists = D('Card')->getTongYongCardByJiaComid($id);
                S('Cache:M:Companyhome:Index:cardlists:' . $id, $cardlists, 900);
            }

            if($cardlists){
                $cardlists['money1'] = $cardlists['money1'] ? (int)$cardlists['money1'] : 0;
                $cardlists['money2'] = $cardlists['money2'] ? (int)$cardlists['money2'] : 0;
                $cardlists['money3'] = $cardlists['money3'] ? (int)$cardlists['money3'] : 0;
                if($cardlists['active_type'] == 2){
                    $cardlists['name'] = '满'.$cardlists['money3'].'元可领';
                }else{
                    if($cardlists['money1'] > 0){
                        $cardlists['name'] = '满'.$cardlists['money1'].'元可用';
                    }else{
                        $cardlists['name'] = '立减'.$cardlists['money2'].'元';
                    }
                }
                $cardlist[0] = $cardlists;
                $cardCount = count($cardlist);
                $this->assign('cardlist',$cardlist);
                $this->assign('cardcount',$cardCount);
            }else{
                $this->assign('cardlist',array());
                $this->assign('cardcount',0);
            }
        }
        //获取公司最新活动
        $logic = new CompanyLogicModel();
        $info['activity'] = S('Cache:M:Companyhome:Index:activity:' . $id);
        if(!$info['activity'] ){
            $info['activity'] = $logic->getCompanyActivity($id);
            if(!empty( $info['activity'])){
                $info['activity']['url'] = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].'/'.$cityInfo['bm'].'/event_view/'. $info['activity']['id'].'.html';
            }
            S('Cache:M:Companyhome:Index:activity:' . $id,$info['activity'],900);
        }
        // 营业执照
        $info['licence'] = S('Cache:M:Companyhome:Index:licence:' . $id);
        if(!$info['licence']){
            $info['licence'] = $logic->getBusinessLicence($id);
            S('Cache:M:Companyhome:Index:licence:' . $id, $info['licence'],900);
        }

        $companyBase = S('mobile:company:baseInfo:'.$id);
        if(!$companyBase){
            $companyBase = $logic->getCompanyBaseInfo($id);
            $companyBase && S('mobile:company:baseInfo:'.$id, $companyBase, 900);
        }

        // 已缴纳保证金
        if($companyBase['classid'] == 3 && $companyBase['cooperate_mode'] == 2 && floatval($companyBase['deposit_money']) > 0){
            $info['showCer'] = 1;
        }else{
            $info['showCer'] = 0;
        }

        //  获取设计师
        $info['designer'] = S('Cache:M:Companyhome:Index:designer:' . $id);
        if(!$info['designer']){
            $employee_count = $companyLogic->getCompanyEmployee($info['user']['id']);
            if ($employee_count > 0) {
                //获取新签返公司设计师数据
                $info['designer'] = $logic->getNewDesignerList($id, 1, 4);
                $info['designer']['designer_type'] = 2;
            }else{
                $info['designer'] = $logic->getDesignerList($id,4);
                $info['designer']['designer_type'] = 1;
            }
            $info['designer']['url'] = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].'/'.$cityInfo['bm'].'/company_team/'. $id ;
            S('Cache:M:Companyhome:Index:designer:' . $id,$info['designer'],900);
        }
        //  获取案例
        $cache_key = $id . ':' . $this->cityInfo["id"];
        $info['cases'] = S('Cache:M:Companyhome:Index:cases:' . $cache_key);
        if (!$info['cases']) {
            $caselogic = new CasesLogicModel();
            $info['cases'] = $caselogic->getCasesListByComId (0,4,$id,$this->SCHEME_HOST,$this->cityInfo);
            if(!empty( $info['cases'] )){
                $info['cases']['url'] = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].'/'.$cityInfo['bm'].'/company_case/'. $id ;
            }
            S('Cache:M:Companyhome:Index:cases:' . $cache_key, $info['cases'], 120);
        }

        // 获取公司封面图
        if($info['user']['img_host'] == 'qiniu' && !empty($info['user']['img'])){
            $info['user']['img'] = $this->SCHEME_HOST['scheme_full'].C('QINIU_DOMAIN')."/".$info['user']['img'];
        }else{
            //最新案例封面
            $info['user']['img'] = isset($info['cases']['list'][0]['img_path'])?$info['cases']['list'][0]['img_path']:'';
        }

        //获取公司轮播图
        $info['banners'] = S('mobile:company:banners:'.$id);
        if(!$info['banners']){
            $info['banners'] = $logic->getCompanyBannersList($id);
            $info['banners'] && S('mobile:company:banners:'.$id, $info['banners']);
        }

        //  获取荣誉
        $info["rongyu"] = S('Cache:M:Companyhome:Index:rongyu:' . $id);
        if (!$info['rongyu']) {
            $info["rongyu"] = D("User")->getCompanyImg($id);
            S('Cache:M:Companyhome:Index:rongyu:' . $id, $info['imgs'], 900);
        }
        $info['rongyu_count'] = count($info["rongyu"]);
        $info['rongyu_url'] = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].'/'.$cityInfo['bm'].'/company_about/'. $id;

        //  获取服务标签
        $info['service_tags'] = S('Cache:M:Companyhome:Index:tags:2:' . $id);
        if(! $info['service_tags']){
            $info['service_tags'] = $logic->getCompanyTags($id, 2);
            S('Cache:M:Companyhome:Index:tags:2:' . $id, $info['service_tags'], 900);
        }

        //  获取公司标签
        $info['tags'] = S('Cache:M:Companyhome:Index:tags:1:' . $id);

        if(! $info['tags']){
            $info['tags'] = $logic->getCompanyTags($id);
            S('Cache:M:Companyhome:Index:tags:1:' . $id, $info['tags'], 900);
        }
        $info['tags_count'] = count($info['tags']);


        // 业主点评
        $info['user']['star'] = $logic->getStar($info['user']['evaluation']);
        //业主评论
        $info['comments'] = S('Cache:M:Companyhome:Index:comments:' . $id);
        if( !$info['comments']){
            $info['comments'] = $logic->getComments($id,$cityInfo['id'],1,2,true);
            if(!empty( $info['comments'])){
                $info['comments']['url'] =  $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].'/'.$cityInfo['bm'].'/company_message/'. $id;
            }
            S('Cache:M:Companyhome:Index:comments:' . $id,$info['comments'],900);
        }
        if(IS_AJAX){
            $seo_html = $this->fetch("yhq_seo");
            $this->ajaxReturn(array("error_code"=>1,"info"=>$seo_html));
        }
        //公司标识对应专题页
        $zhuanti = (new SubthematicLogicModel())->getNameByCompanyTag(I("get.id"),$this->cityInfo["id"]);
        $this->assign("zhuanti",$zhuanti);
        //获取src
        $src = I('get.src','');

        //获取装修公司其他信息
        $info['other'] = $logic->getCompanyOther($id);

        $this->assign("src", $src);
        $this->assign("nav_index",'bh');
        $this->assign("fi", 321);
        $this->assign("info",$info);
        $this->display();
    }
    /**
     * 装修公司案例列表页
     * @return [type] [description]
     */
    public function cases(){
        $cityInfo = $this->cityInfo;
        $SCHEME_HOST = $this->SCHEME_HOST;
        $pageIndex = 1;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }
        $pageCount = 10;

        $id = I("get.id");
        //若前台不显示
        $companyLogic  = new CompanyLogicModel();
        if(!$companyLogic->companyShow(I("get.id"))){
            $this->_error();
        }

        //获取装修公司信息
        $info["user"] =  $this->user;
        $result = $this->getCasesListByComId($pageIndex,$pageCount,$id,$cityInfo["id"]);
        $info["cases"] = $result["cases"];
        $info["page"] = $result["page"];
        //关键字、描述、标题
        $basic["head"]["title"] = $info["user"]["qc"]."装修案例效果图";
        $basic["head"]["keywords"] = $info["user"]["qc"]."装修案例效果图";
        $basic["head"]["description"] = $info["user"]["qc"]."装修案例效果图";
        $basic["body"]["title"] = $info['user']['jc'];

        //页面canonical
        $info['canonical'] = $canonical = $SCHEME_HOST['scheme_full']. $cityInfo['bm'].'.'.$SCHEME_HOST['domain']. str_ireplace('/'.$cityInfo['bm'], '', $_SERVER['REQUEST_URI']);
        $locate = strpos($canonical,'p=');
        if($locate !== false){
            $replace = substr($canonical, $locate - 1, 3 + strlen(I('get.p')));
            $info['canonical'] = str_replace($replace,'',$canonical);
        }

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("fi", 321);
        $this->assign("basic",$basic);
        $this->assign("info",$info);
        $this->assign("nav_cases",'bh');
        $this->display();
    }

    /**
     * 装修公司案例列表页
     * @return [type] [description]
     */
    public function casestwo(){
        $cityInfo = $this->cityInfo;
        $SCHEME_HOST = $this->SCHEME_HOST;
        $pageIndex = 1;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }
        $pageCount = 12;

        $id = I("get.id");
        //若前台不显示
        $companyLogic  = new CompanyLogicModel();
        if(!$companyLogic->companyShow(I("get.id"))){
            $this->_error();
        }

        //获取装修公司信息
        $info["user"] =  $this->user;
        $logic = new CasesLogicModel();

        $result = $logic->getCasesListByComId($pageIndex,$pageCount,$id,$SCHEME_HOST,$cityInfo,I("get.category"),I("get.class"));
        $data["list"] = $result["list"];
        $data["page"] = $result["page"];
//        dump($data["page"]);
        $this->assign("data",$data);
//        if(IS_AJAX){
//            if(!empty( $data["list"] )){
//                $this->ajaxReturn(array("data"=>$data,"error_code"=>0));
//            }
//            $this->ajaxReturn(array("error_code"=>1));
//        }
        //关键字、描述、标题
        $basic["head"]["title"] = $info["user"]["jc"]."装修案例效果图_".$cityInfo['name']."齐装网";
        $basic["head"]["keywords"] = $info["user"]["jc"]."装修案例,".$info["user"]["jc"]."装修效果图";
        $basic["head"]["description"] = $info["user"]["jc"]."为您提供众多实景装修案例效果图,设计师匠心设计让您了解当下装修潮流风格趋势";
        $basic["body"]["title"] = $info['user']['jc'];

        //页面canonical
        $info['canonical'] = $canonical = $SCHEME_HOST['scheme_full']. $cityInfo['bm'].'.'.$SCHEME_HOST['domain']. str_ireplace('/'.$cityInfo['bm'], '', $_SERVER['REQUEST_URI']);

        $locate = strpos($canonical,'p=');
        if($locate !== false){
            $replace = substr($canonical, $locate - 1, 3 + strlen(I('get.p')));
            $info['canonical'] = str_replace($replace,'',$canonical);
        }

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        //获取顶部分类
        $info['category'] = $logic->getFenggeCategory($id,$cityInfo["id"]);
        //获取底部类型
        $info['class'] = array(
            array("name"=>"家装","id"=>"1"),
            array("name"=>"公装","id"=>"2"),
            array("name"=>"工地","id"=>"3")
        );
        $info['url'] = '/'.$cityInfo['bm'].'/company_case/'.$id;

        $this->assign("fi", 321);
        $this->assign("basic",$basic);
        $this->assign("info",$info);
        $this->assign("nav_cases",'bh');
        $this->display();
    }

    /**
     * 设计团队页
     * @return [type] [description]
     */
    public function team(){
        $info["title"] = "设计师团队";
        $cityInfo = $this->cityInfo;
        $SCHEME_HOST = $this->SCHEME_HOST;
        $id = I("get.id");
        //若前台不显示
        $companyLogic  = new CompanyLogicModel();
        if(!$companyLogic->companyShow(I("get.id"))){
            $this->_error();
        }

        $info["user"] =  $this->user;
        //获取设计师团队信息
        $pageIndex = 1;
        $pageCount = 10;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }
        $team = $this->getTeamDesignerList($id,"",2,$pageIndex,$pageCount);
        $info["team"] = $team["team"];
        $info["page"] = $team["page"];

        //seo 标题/描述/关键字
        $basic["head"]["title"] = $info["user"]["qc"]."设计团队";
        $basic["head"]["keywords"] = $info["user"]["qc"]."设计团队,".$info["user"]["qc"]."设计团师, 装修设计师";
        $basic["head"]["description"] = $info["user"]["qc"]."设计团队";
        $basic["body"]["title"] = $info['user']['jc'];

        //页面canonical
        $info['canonical'] = $canonical = $SCHEME_HOST['scheme_full']. $cityInfo['bm'].'.'.$SCHEME_HOST['domain']. str_ireplace('/'.$cityInfo['bm'], '', $_SERVER['REQUEST_URI']);
        $locate = strpos($canonical,'p=');
        if($locate !== false){
            $replace = substr($canonical, $locate - 1, 3 + strlen(I('get.p')));
            $info['canonical'] = str_replace($replace,'',$canonical);
        }

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("fi", 321);
        $this->assign("basic",$basic);
        $this->assign("info",$info);
        $this->assign("nav_team",'bh');
        $this->display();
    }

    /**
     * 设计团队页
     * @return [type] [description]
     */
    public function teamtwo(){
        $info["title"] = "设计师团队";
        $cityInfo = $this->cityInfo;
        $SCHEME_HOST = $this->SCHEME_HOST;
        $id = I("get.id");
        $page = (int)I("get.p")?:1;
        $pageCount = 8;
        //若前台不显示
        $companyLogic  = new CompanyLogicModel();
        if(!$companyLogic->companyShow(I("get.id"))){
            $this->_error();
        }

        //验证是否是新签返
        $info["user"] = $this->user;
        $employee_count = $companyLogic->getCompanyEmployee($info['user']['id']);
        if ($employee_count > 0) {
            $team = $this->getNewTeamDesignerList($id,$page, $pageCount);
        } else {
            $team = $this->getTeamDesignerList2($id, "", 2, $page, $pageCount);
        }

        //seo 标题/描述/关键字
        $basic["head"]["title"] = $info["user"]["jc"]."设计师团队_".$cityInfo['name']."齐装网";
        $basic["head"]["keywords"] = $info["user"]["jc"]."室内设计师,".$info["user"]["jc"]."装饰设计团队";
        $basic["head"]["description"] = $cityInfo['name']."齐装网为您挑选".$info["user"]["jc"]."设计师团队的精品装修案例,为您选择设计师提供全面便携的服务资料。";
        $basic["body"]["title"] = $info['user']['jc'];

        //页面canonical
        $info['canonical'] = $canonical = $SCHEME_HOST['scheme_full']. $cityInfo['bm'].'.'.$SCHEME_HOST['domain']. str_ireplace('/'.$cityInfo['bm'], '', $_SERVER['REQUEST_URI']);
        $locate = strpos($canonical,'p=');
        if($locate !== false){
            $replace = substr($canonical, $locate - 1, 3 + strlen(I('get.p')));
            $info['canonical'] = str_replace($replace,'',$canonical);
        }
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("fi", 321);
        $this->assign("basic",$basic);
        $this->assign("team",$team);
        $this->assign("info",$info);
        $this->assign("company_id",$id);
        $this->assign("nav_team",'bh');
        $this->display();
    }

    public function getTeamById(){
        $pageIndex = 1;
        $pageCount = 10;
        if(I("get.page") !== ""){
            $pageIndex = intval(I("get.page"));
        }
        $team = $this->getTeamDesignerList(I("get.id"),"",2,$pageIndex,$pageCount);
        $data["data"] = $team["team"];
        $data["page"] = $team["page"];
        $this->ajaxReturn(array("info"=>"获取成功","error_code"=>0,"data"=>$data));
    }

    /**
     * 装修公司评论页面
     * @return [type] [description]
     */
    public function comment(){
        $cityInfo = $this->cityInfo;
        $SCHEME_HOST = $this->SCHEME_HOST;
        $info["title"] = "业主牛评";
        $info["user"] =  $this->user;

        $id = I("get.id");
        //若前台不显示
        $companyLogic  = new CompanyLogicModel();
        if(!$companyLogic->companyShow(I("get.id"))){
            $this->_error();
        }

        //业主评论
        $pageIndex = 1;
        $pageCount = 10;
        if(I("get.p") !== ""){
            $pageIndex = I("get.p");
        }
        $comments = $this->getComments($id,$cityInfo["id"],$pageIndex,$pageCount,true);



        import('Library.Org.Page.MobilePage');
        //自定义配置项
        $config = array("prev", "next");
        //移动端分页
        $page = new \MobilePage($pageIndex, $pageCount, $count, $config, "html");
        $pageTmp = $page->show2();



        $info["comments"] = $comments["comments"];
        $info["page"] = $comments["page"];

        //seo 标题/描述/关键字
        $basic["head"]["title"] = $info["user"]["qc"]."评价_业主点评";
        $basic["head"]["keywords"] = $info["user"]["qc"]."评价,业主点评";
        $basic["head"]["description"] = $info["user"]["qc"]."评价";
        $basic["body"]["title"] = $info['user']['jc'];

        //页面canonical
        $info['canonical'] = $canonical = $SCHEME_HOST['scheme_full'] . $cityInfo['bm'].'.'.$SCHEME_HOST['domain']. str_ireplace('/'.$cityInfo['bm'], '', $_SERVER['REQUEST_URI']);
        $locate = strpos($canonical,'p=');
        if($locate !== false){
            $replace = substr($canonical, $locate - 1, 3 + strlen(I('get.p')));
            $info['canonical'] = str_replace($replace,'',$canonical);
        }

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("fi", 321);
        $this->assign("basic",$basic);
        $this->assign("info",$info);
        $this->assign("nav_comment",'bh');
        $this->display();
    }

    /**
     * 装修公司评论页面
     * @return [type] [description]
     */
    public function commenttwo(){
        $cityInfo = $this->cityInfo;
        $SCHEME_HOST = $this->SCHEME_HOST;
        $info["title"] = "业主牛评";
        $info["user"] =  $this->user;

        $id = I("get.id");
        //若前台不显示
        $companyLogic  = new CompanyLogicModel();
        if(!$companyLogic->companyShow(I("get.id"))){
            $this->_error();
        }


        //阶段
        $jieduan = $this->jieduan;
        $steplist = CommentModel::getCommentStepList($id,$cityInfo["id"]);
        $showjieduanlist = [];
        $i = 0;
        foreach ($steplist as $key => $val){
            foreach ($jieduan as $k => $v){
                if($val['step'] == $v){
                    $showjieduanlist[$i]['id'] = $k;
                    $showjieduanlist[$i]['name'] = $v;
                    $i++;
                }
            }
        }
        //获取该公司是否有精选的评论
        $model = new \Common\Model\CommentModel();
        $hadjingxuan = $model->getCommentByComIdCount($id,$cityInfo["id"],1);   // 1表示精选
        //seo 标题/描述/关键字
        $basic["head"]["title"] = $info["user"]["jc"]."怎么样_".$info["user"]["jc"]."好不好_".$cityInfo['name']."齐装网";
        $basic["head"]["keywords"] = $info["user"]["jc"]."怎么样,".$info["user"]["jc"]."好不好,".$info["user"]["jc"]."口碑,".$info["user"]["jc"]."评价";
        $basic["head"]["description"] = $cityInfo['name']."齐装网为您提供业主对".$info["user"]["jc"]."的真实评价,给您的装修选择以客观参考。";
        $basic["body"]["title"] = $info['user']['jc'];

        //页面canonical
        $info['canonical'] = $canonical = $SCHEME_HOST['scheme_full'] . $cityInfo['bm'].'.'.$SCHEME_HOST['domain']. str_ireplace('/'.$cityInfo['bm'], '', $_SERVER['REQUEST_URI']);
        $locate = strpos($canonical,'p=');
        if($locate !== false){
            $replace = substr($canonical, $locate - 1, 3 + strlen(I('get.p')));
            $info['canonical'] = str_replace($replace,'',$canonical);
        }


        //分页开始
        $cityInfo = $this->cityInfo;
        $pageIndex = 1;
        $pageCount = 20;
        if(I("get.p") !== ""){
            $pageIndex = intval(I("get.p"));
        }
        $type = '';
        if(I("get.type") !== ""){
            $type = intval(I("get.type"));
        }
        //业主评论
        /*********************************************/
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $id = I("get.id");
        $cs = $cityInfo["id"];
        $reply = true;
//        $type = I('get.type');
        $count = D("Comment")->getCommentByComIdCount($id,$cs,$type);
        if($count > 0){
            $totalpage = $count / $pageCount;

            import('Library.Org.Page.MobilePage');
            //自定义配置项
            $config = array("prev", "next");
            //移动端分页
            $page = new \MobilePage($pageIndex, $pageCount, $count, $config, "html");
            $pageTmp = $page->show2();

            $comments = D("Comment")->getCommentByComId($id,$cs,($page->pageIndex-1)*$pageCount,$pageCount,$reply,$type);
            foreach ($comments as $key => $value) {
                $total = $value["count"];
                if($value["sj"] != 0 && $value["fw"]!= 0 && $value["sg"]!= 0){
                    $total = round((($value["sj"]+$value["fw"]+$value["sg"])/3),1);
                }
                $comments[$key]["text"] = trim(preg_replace('# #','',$comments[$key]["text"]));
                $comments[$key]["textcount"] = mb_strlen($comments[$key]["text"]);
                $comments[$key]["text2"] = trim(preg_replace('# #','',mbstr($comments[$key]["text"],0,100)));
                $comments[$key]["name"] = mb_substr($value['name'],0,15,"UTF-8");
                $comments[$key]["totalCount"] = $total;
                $comments[$key]["avgcount"] = (int)($total/2);
                $comments[$key]["time"] = date( "Y-m-d H:i:s",$value['time']);
                $comments[$key]["reply_time"] = date( "Y-m-d H:i:s",$value['reply_time']);
                if(
                    strpos($value["logo"], CompanyHomeEnum::COMPANY_DEFAULT_LOGO_ONE) !== false ||
                    strpos($value["logo"], CompanyHomeEnum::COMPANY_DEFAULT_LOGO_TWO) !== false ||
                    strpos($value["logo"], CompanyHomeEnum::COMPANY_DEFAULT_LOGO_THREE) !== false ||
                    !$value['logo']
                ){
                    $comments[$key]['logo'] = changeImgUrl(CompanyHomeEnum::COMPANY_DEFAULT_LOGO);
                }else{
                    $comments[$key]['logo'] = changeImgUrl($value['logo'], 2);
                }

                // 评论图片
                $imglist = D("Db\Comment")->getCommentImgsByCommentid($value['id']);
                $showlist = [];
                if($imglist){
                    foreach ($imglist as $k => $v){
                        $showlist[$k] = changeImgUrl($v['imgurl'],2);
                    }
                }
                $comments[$key]['comment_imgs'] = $showlist;
                $comments[$key]['comment_countimgs'] = count($showlist);

            }

            if (IS_AJAX) {
                $this->assign('list', $comments);
                $content = $this->fetch('list-content');
                echo $content;
                die();
            }
        }
        /*********************************************/
        //分页结束
        $this->assign('list',$comments);
        $this->assign('page',$pageTmp);

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("fi", 321);
        $this->assign("basic",$basic);
        $this->assign("bm",$cityInfo["bm"]);
        $this->assign("showtype",$type);
        $this->assign("userInfo",$info);
        $this->assign("showjieduan",$showjieduanlist);
        $this->assign("hadjingxuan",$hadjingxuan);
        $this->assign("nav_comment",'bh');
        $this->assign("company_id",I("get.id"));
        $this->assign("pageid",$pageIndex);
        $this->assign("totalpage",ceil($totalpage));
        $this->display();
    }

    public function getCommentById(){
        $cityInfo = $this->cityInfo;
        $pageIndex = 1;
        $pageCount = 10;
        if(I("get.page") !== ""){
            $pageIndex = intval(I("get.page"));
        }
        $type = '';
        if(I("get.type") !== ""){
            $type = intval(I("get.type"));
        }

        //业主评论
        $comments = $this->getComments(I("get.id"),$cityInfo["id"],$pageIndex,$pageCount,true,$type);
        if($comments){
            $data["data"] = $comments["comments"];
            $data["page"] = $comments["page"];
        }else{
            $data["data"] = [];
            $data["page"] = ["page_total_number" => 0,"total_number" => 0,"page_size" => 0,"page_current" =>0];
        }
        $this->ajaxReturn(array("info"=>"获取成功","error_code"=>0,"data"=>$data));
    }

    /**
     * 获取用户信息
     * @param  [type] $id [用户编号]
     * @param  [type] $cs [所在城市]
     * @return [type]     [description]
     */
    private function getUserInfo($id,$cs){
        $cache_key = $id . ':' . $cs;
        $user = [];//S('Cache:M:Companyhome:userInfo:' . $cache_key);
        if (!empty($user)) {
            return $user;
        }

        $result =  D("User")->getUserInfoById($id,$cs);
        $user = array();
        if($result[0]["id"] != 0){
            import('Library.Org.Util.Fiftercontact');
            $filter = new \Fiftercontact();
            $contact_q1 = OP('QZ_CONTACT_QQ1');
            $contact_q2 = OP('QZ_CONTACT_QQ2');
            $contact_t400 = OP("QZ_CONTACT_TEL400");
            foreach ($result as $key => $value) {
                if($key == 0){
                    $user["id"] = $value["id"];
                    $user["hengfu"] = $value["hengfu"];
                    $user["img_host"] = $value["img_host"];
                    $user["img"] = $value["img"];
                    $user["on"] = $value["on"];
                    $user["qc"] = $value["qc"];
                    $user["jc"] = $value["jc"];
                    $user["kouhao"] = $value["kouhao"];
                    $user["logo"] = $value["logo"];
                    $user["pv"] = $value["pv"];
                    $user["jianjie"] =$filter->fifter_contact($value["jianjie"]);
                    $user["about_jianjie"] = $value["jianjie"];
                    $user["jiawei"] = $value["jiawei"];
                    $user["fake"] = $value["fake"];
                    $user["nickname"] = empty($value["nickname"])== true?"家装咨询顾问":$value["nickname"];
                    $user["nickname1"] = empty($value["nickname1"])== true?"公装咨询顾问":$value["nickname1"];
                    $user["area"] = $value["area"];
                    $user["fw"] = $value["fw"];
                    $user["fg"] = $value["fg"];
                    $user["dcount"] = $value["dcount"];
                    $user["ccount"] = $value["ccount"];
                    $user["avgsj"] = round($value["avgsj"],1);
                    $user["avgfw"] = round($value["avgfw"],1);
                    $user["avgsg"] = round($value["avgsg"],1);
                    $user["avgscore"] = round(($value["avgsj"]+$value["avgfw"]+$value["avgsg"])/3,1);
                    $user["avgcount"] = round($value["avgcount"],1) == 0?1:round($value["avgcount"],1);
                    $user["casecount"] = $value["casecount"];
                    $user["video"] = $value["video"];
                    $user["qq"] =  empty($value["qq"]) ==true?$contact_q1:($value["on"] != 2 || $value["fake"] !=0)?$contact_q1:$value["qq"];
                    $user["qq1"] = empty($value["qq1"])==true?$contact_q2:($value["on"] != 2 || $value["fake"] !=0)?$contact_q2:$value["qq1"];
                    $user["dz"] = $value["dz"];
                    $user["cal"] = empty($value["cal"])?"":($value["on"] != 2 || $value["fake"] !=0)?"":$value["cal"];
                    $user["cals"] = empty($value["cals"])?$contact_t400:($value["on"] != 2 || $value["fake"] !=0)?$contact_t400:$value["cals"];
                    $user["tel"] = empty($value["tel"])?$contact_t400:($value["on"] != 2 || $value["fake"] !=0)?$contact_t400:$value["tel"];
                    $user["cs"] = $value["cs"];
                    $user["gm"] = $value["gm"];
                    $user["chengli"] = date("Y年m月",strtotime($value["chengli"]));
                    $user["good"] = round(($value["good"]/$value["newcount"])*100,2);
                    $user["oldgood"] =round(($value["oldgood"]/$value["oldcount"])*100,2);
                    $user["evaluation"] = $user["avgcuont"];
                    if($value["avgsj"] != 0 && $value["avgfw"] != 0 && $value["avgsg"] != 0){
                        $user["evaluation"] = round(($value["avgsj"]+$value["avgfw"]+$value["avgsg"])/3,2);
                    }
                    $user["cooperate_mode"] = $value["cooperate_mode"];
                }
                if(!empty($value["hid"])){
                    $sub = array(
                        "name"=>$value["shortname"],
                        "tel" =>$value["htel"],
                        "addr"=>$value["addr"],
                        "qq"=> empty($value["qq3"]) ==true?$contact_q1:($value["on"] != 2 || $value["fake"] !=0)?$contact_q1:$value["qq3"],
                        "qq1"=>empty($value["qq4"]) ==true?$contact_q1:($value["on"] != 2 || $value["fake"] !=0)?$contact_q1:$value["qq4"],
                        "nickname"=>empty($value["nickname2"])== true?"家装咨询顾问":$value["nickname2"],
                        "nickname1"=>empty($value["nickname3"])== true?"家装咨询顾问":$value["nickname3"]
                             );
                    $user["child"][] = $sub;
                }
            }
        }

        S('Cache:M:Companyhome:userInfo:' . $cache_key, $user, 900);

        return $user;
    }

     /**
     * 获取案例图片
     * @param  string $comid   [description]
     * @param  string $cs      [description]
     * @param  string $classid [description]
     * @return [type]          [description]
     */
    private function getCasesListByComId($pageIndex,$pageCount,$comid ='',$cs ='')
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Cases")->getCasesListByComIdCount($comid,$cs,'');
        if($count > 0){
            import('Library.Org.Page.MobilePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \MobilePage($pageIndex,$pageCount,$count,$config);
            $pageTmp =  $page->showpage();
            $list =D("Cases")->getCasesListByComId(($page->pageIndex-1)*$pageCount,$pageCount,$comid,$cs,'');
            return array("cases"=>$list,"page"=>$pageTmp);
       }
       return null;
    }

    /**
     * 获取设计师列表
     * @param  [type] $id        [用户编号]
     * @param  [type] $zw      [职位名称]
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @return [type]            [description]
     */
    private function getTeamDesignerList($id,$zw,$zt,$pageIndex,$pageCount)
    {
        //过滤
        $SCHEME_HOST = $this->SCHEME_HOST;
        $cityInfo = $this->cityInfo;
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("User")->getTeamDesignerListCount($id,$zw,$zt);
        if($count > 0){
            import('Library.Org.Page.MobilePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \MobilePage($pageIndex,$pageCount,$count,$config,"html");
            $pageTmp = $page->showpage();
            //查询设计师资料
            $users = D("Company")->getDesignerListByCompany($id,$zw,$zt,($page->pageIndex-1)*$pageCount,$pageCount);

            foreach ($users as $key => $value) {
                $ids[] = $value["userid"];
            }

            //获取设计师最新的2个案例数
            $cases = D("Cases")->getDesinerCase($ids,1);
            foreach ($users as $key => $value) {
                foreach ($cases as $k => $val) {
                    if($value["userid"] == $val["userid"]){
                        if($val['img_host'] == 'qiniu' && !empty($val['img_path'])){
                            $users[$key]["caseimg"] = array(
                                'url'=>$this->SCHEME_HOST['scheme_full'].C('QINIU_DOMAIN').'/'.$val['img_path'],
                                'alt'=>$users[$key]["name"] . '设计作品'
                            );
                        }elseif(!empty($val['img_path'])){
                            $users[$key]["caseimg"] = array(
                                'url'=>$this->SCHEME_HOST['scheme_full'].C('STATIC_HOST1').$val['img_path'].'s_'.$val['img'],
                                'alt'=>$users[$key]["name"] . '设计作品'
                            );
                        }
                    }
                }
                $users[$key]['blog_url'] = $this->SCHEME_HOST['scheme_full'].C('MOBILE_DONAMES').'/'.$cityInfo['bm'].'/blog/'.$value['id'];
            }
            $dids = array();
            foreach ($users as $key => $value) {
                $dids[] = $value["id"];
            }

            if(!empty($dids)){
                //查询设计师的设计作品
                $cases = D("Cases")->getDesinerCase1($dids,$id);
                foreach ($users as $key => $value) {
                    foreach ($cases as $k => $val) {
                        if($val['img_host'] == 'qiniu' && !empty($val['img_path'])){
                            $val['img_path'] = $this->SCHEME_HOST['scheme_full'].OP('QINIU_DOMAIN').'/'.$val['img_path'].'-w640.jpg';
                        }elseif(!empty($val['img_path'])){
                            $val["img_path"] = $this->SCHEME_HOST['scheme_full'].C('STATIC_HOST1').$val['img_path'].'s_'.$val['img'];
                        }
                        $val['url'] = $this->SCHEME_HOST['scheme_full'].C('MOBILE_DONAMES').'/'.$cityInfo['bm'].'/caseinfo/'.$val['caseid'].'.shtml';
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
     * 获取设计师列表 有分页
     * @param  [type] $id        [用户编号]
     * @param  [type] $zw      [职位名称]
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @return [type]            [description]
     */
    private function getTeamDesignerList2($id,$zw,$zt,$pageIndex,$pageCount)
    {
        //过滤
        $SCHEME_HOST = $this->SCHEME_HOST;
        $cityInfo = $this->cityInfo;
        //强制数字整数

        $count = D("User")->getTeamDesignerListCount($id,$zw,$zt);
        if($count > 0){
            import('Library.Org.Page.MobilePage');
            //自定义配置项
            $config = array("prev", "next");
            //移动端分页
            $page = new \MobilePage($pageIndex, $pageCount, $count, $config, "html");
            $pageTmp = $page->show2();
            //查询设计师资料
            $users = D("Company")->getDesignerListByCompany($id,$zw,$zt,($page->pageIndex-1)*$pageCount,$pageCount);

            foreach ($users as $key => $value) {
                $ids[] = $value["userid"];
            }

            //获取设计师最新的2个案例数
            $cases = D("Cases")->getDesinerCase($ids,1);
            foreach ($users as $key => $value) {
                foreach ($cases as $k => $val) {
                    if($value["userid"] == $val["userid"]){
                        if($val['img_host'] == 'qiniu' && !empty($val['img_path'])){
                            $users[$key]["caseimg"] = array(
                                'url'=>$this->SCHEME_HOST['scheme_full'].C('QINIU_DOMAIN').'/'.$val['img_path'],
                                'alt'=>$users[$key]["name"] . '设计作品'
                            );
                        }elseif(!empty($val['img_path'])){
                            $users[$key]["caseimg"] = array(
                                'url'=>$this->SCHEME_HOST['scheme_full'].C('STATIC_HOST1').$val['img_path'].'s_'.$val['img'],
                                'alt'=>$users[$key]["name"] . '设计作品'
                            );
                        }
                    }
                }
                $users[$key]['blog_url'] = $SCHEME_HOST['scheme_full'].C('MOBILE_DONAMES').'/'.$cityInfo['bm'].'/blog/'.$value['id'];
            }
            $dids = array();
            foreach ($users as $key => $value) {
                $dids[] = $value["id"];
            }

            if(!empty($dids)){
                //查询设计师的设计作品
                $cases = D("Cases")->getDesinerCase1($dids,$id);
                foreach ($users as $key => $value) {
                    foreach ($cases as $k => $val) {
                        if($val['img_host'] == 'qiniu' && !empty($val['img_path'])){
                            $val['img_path'] = $this->SCHEME_HOST['scheme_full'].OP('QINIU_DOMAIN').'/'.$val['img_path'].'-w640.jpg';
                        }elseif(!empty($val['img_path'])){
                            $val["img_path"] = $this->SCHEME_HOST['scheme_full'].C('STATIC_HOST1').$val['img_path'].'s_'.$val['img'];
                        }
                        $val['url'] = $this->SCHEME_HOST['scheme_full'].C('MOBILE_DONAMES').'/'.$cityInfo['bm'].'/caseinfo/'.$val['caseid'].'.shtml';
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
     * 获取设计师列表 有分页
     * @param  [type] $id        [用户编号]
     * @param  [type] $zw      [职位名称]
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @return [type]            [description]
     */
    private function getNewTeamDesignerList($id, $pageIndex, $pageCount)
    {
        //过滤
        $SCHEME_HOST = $this->SCHEME_HOST;
        $cityInfo = $this->cityInfo;
        //强制数字整数

        $logic = new CompanyLogicModel();
        $info = $logic->getNewDesignerList($id, $pageIndex, $pageCount);
        if (count($info['list']) > 0) {
            //查询设计师的设计作品
            $cases = D("Cases")->getDesinerCase1(array_column($info['list'], 'id'));

            foreach ($info['list'] as $key => $value) {
                $info['list'][$key]['zw'] = $this->companyEmployeeZw[$value['position']];
                //兼容页面数据
                $info['list'][$key]['case_counts'] = $value['casecount'];
                foreach ($cases as $k => $val) {
                    if ($val['img_host'] == 'qiniu' && !empty($val['img_path'])) {
                        $val['img_path'] = $this->SCHEME_HOST['scheme_full'] . OP('QINIU_DOMAIN') . '/' . $val['img_path'] . '-w640.jpg';
                    } elseif (!empty($val['img_path'])) {
                        $val["img_path"] = $this->SCHEME_HOST['scheme_full'] . C('STATIC_HOST1') . $val['img_path'] . 's_' . $val['img'];
                    }
                    $val['url'] = $this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . '/' . $cityInfo['bm'] . '/caseinfo/' . $val['caseid'] . '.shtml';
                    if ($value["id"] == $val["userid"]) {
                        $info['list'][$key]["case3"][] = $val;
                    }
                }
                $info['list'][$key]['blog_url'] = $SCHEME_HOST['scheme_full'].C('MOBILE_DONAMES').'/'.$cityInfo['bm'].'/blog_new/'.$value['id'];
            }
        }
        return ["team" => $info['list'], "page" => $info['page']];
    }

     /**
     * 获取业主评论
     * @param  [type] $id    [公司编号]
     * @param  [type] $cs    [所在城市]
     * @param  [type] $limit [显示数量]
     * @param  [type] $reply [是否显示回复]
     * @return [type]        [description]
     */
    private function getComments($id,$cs,$pageIndex,$pageCount,$reply,$type='')
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $count = D("Comment")->getCommentByComIdCount($id,$cs,$type);
        if($count > 0){
            import('Library.Org.Page.MobilePage');
            //自定义配置项
            $config  = array("prev","next");
            $page = new \MobilePage($pageIndex,$pageCount,$count,$config);
            $pageTmp = $page->showpage();
            $comments = D("Comment")->getCommentByComId($id,$cs,($page->pageIndex-1)*$pageCount,$pageCount,$reply,$type);
            foreach ($comments as $key => $value) {
                $total = $value["count"];
                if($value["sj"] != 0 && $value["fw"]!= 0 && $value["sg"]!= 0){
                    $total = round((($value["sj"]+$value["fw"]+$value["sg"])/3),1);

                }
                $comments[$key]["text"] = trim(preg_replace('# #','',$comments[$key]["text"]));
                $comments[$key]["name"] = mb_substr($value['name'],0,15,"UTF-8");
                $comments[$key]["totalCount"] = $total;
                $comments[$key]["time"] = date( "Y-m-d H:i:s",$value['time']);
                $comments[$key]["reply_time"] = date( "Y-m-d H:i:s",$value['reply_time']);
            }
            return array("comments"=>$comments,"page"=>$pageTmp);
        }
        return null;
    }


    /**
     * 装修公司装修报价详细页面
     * @return [type] [description]
     */
    public function details()
    {
        $SCHEME_HOST = $this->SCHEME_HOST;
        $info["user"] =  $this->user;
        $src = $_GET['src'];
        $orderSourceResult = D("OrderSource")->getOne($src);

        //根据sourceid获取微信管理信息
        $result = D("YySrcWeixin")->getOneBySourceid($orderSourceResult['id']);

        if(!$result || empty($result['name'])){
            $result = D("YySrcWeixin")->getDefaultData();
        }
        if(!empty($result['name'])){
            $this->assign("name", $result['name']);
        }

        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = $_COOKIE["w_qizuang_n"];
            $order = D("Orders")->getOrderInfoById($orderid);
            if(!empty($order)){
                $result = $this->calculatePrice($order["mianji"],$order["huxing"]);
                $basic["head"]["title"] = $order["cname"]."_".$order["fengge"]."_".$order["hxname"].$info['user']['jc']."装修报价明细-齐装网";
                $this->assign("basic",$basic);
                $this->assign("order",$order);
                $this->assign("result",$result);
                $this->assign("info",$info);
                $this->display();
                die();
            }else{
                header("LOCATION: ".$SCHEME_HOST['scheme_full'].$SCHEME_HOST['host']."/company/");
                die();
            }
        }else{
            header("LOCATION: ".$SCHEME_HOST['scheme_full'].$SCHEME_HOST['host']."/company/");
            die();
        }
    }


    /**
     * 报价计算器
     * @param  [type] $mianji [面积]
     * @param  [type] $cs [城市]
     * @return [type]         [description]
     */
    private function calculatePrice($mianji,$cs)
    {
        //占比：客厅25% 卧室 18% 厨房 8% 卫生间16% 水电25% 其他 8%
        //计算公式 （城市最低半包单价*120%）*房子的面积

        //获取改订单城市的最低半包价格
        $result = D("Orders")->getCityPrice($cs);
        $price = $result["half_price_min"];
        if (empty($price)) {
            $price = 300;
        }

        $total = $price*1.2*$mianji;
        $info["child"]['kt'] = $total*0.25 ;
        $info["child"]['zw'] = $total*0.18;
        $info["child"]['wsj'] = $total*0.16;
        $info["child"]['cf'] = $total*0.08;
        $info["child"]['sd'] = $total*0.25;
        $info["child"]['other'] = $total*0.08;
        $info['total'] = $total;

        return $info;
    }

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
                $companyInfo = $this->getCompanyInfo($id,$cityInfo["id"]);
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
            $basic['head']["title"] = $userInfo["user"]["jc"]."简介|地址_".$cityInfo["name"]."齐装网";
            $basic['head']["keywords"] = $userInfo["user"]["jc"]."简介,".$userInfo["user"]["jc"]."地址";
            $basic['head']["description"] = $cityInfo["name"]."齐装网为您提供".$userInfo["user"]["jc"]."公司的发展历程、荣誉资质、地址、服务范围等详细信息,让您对公司多一份了解,使您的选择多一份保障。";
            $this->assign("basic",$basic);
            $userInfo['canonical'] = $canonical = $this->SCHEME_HOST['scheme_full']. $cityInfo['bm'].'.'.$this->SCHEME_HOST['domain']. str_ireplace('/'.$cityInfo['bm'], '', $_SERVER['REQUEST_URI']);
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



    /**
     * 获取装修公司所有信息
     * @return [type] [description]
     */
    private function getCompanyInfo($id,$cs){
        $result = D("User")->getCompanyInfo($id,$cs);

        if (!empty($result)) {
            $company_ids = $result[0]['id'];
        }
        // 获取公司标签
        $companyLogic = new CompanyLogicModel();
        $company_tag = $companyLogic->getCompanyTags($company_ids);

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
        $user['tags'] = $company_tag;

        return $user;
    }
}
