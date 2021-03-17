<?php
/**
 * 用户后台主页控制器
 * 装修公司、设计师、业主的主页都此控制器
 */
namespace User\Controller;

use Common\Model\Db\OrderRobPoolModel;
use Common\Model\Logic\RobOrdersLogicModel;
use Common\Model\Logic\UserLogicModel;
use User\Common\Controller\UserCenterBaseController;

class HomeController extends UserCenterBaseController{
    public function _initialize(){
        parent::_initialize();
        if(!isset($_SESSION["u_userInfo"])){
           header("LOCATION:".C('QZ_YUMING_SCHEME')."://u.qizuang.com");
        }
    }

    public function index(){
        //获取登录后的session
        $classid = $_SESSION["u_userInfo"]["classid"];
        switch ($classid) {
            case '1':
                //业主
                $tmp = "User/index";
                $user = $this->getUserInfoByAdmin($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"],1);
                $info["user"] = $user;
                $info["user"]["tel_safe"] =substr_replace($info["user"]["tel_safe"],"*****",3,5);
                $mail = strstr($info["user"]["mail_safe"],"@",true);
                $prefix = strstr($user["mail_safe"],"@");
                $info["user"]["mail_safe"] =substr_replace($mail,"*****",3,5).$prefix;
                $info["user"]["unreadsystem"] = $this->getUnSystemNoticeCount($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"],1);
                //如果城市字段为空，则先选择所在城市
                if(empty($_SESSION["u_userInfo"]["cs"])){
                    //获取城市信息
                    //$citys = getCityArray();
                    //$this->assign("citys", json_encode($citys));
                    $citytmp = $this->fetch("Home/citytmp");
                    $this->assign("citytmp",$citytmp);
                }
                //获取最新的发布订单
                $order = D("Orders")->getLastOrderInfoById($user["id"],$user["tel_safe"],$user["tel_safe_chk"]);
                if(count($order) > 0){
                    $info["order"] = $order;
                }
                break;
            case '2':
                //设计师
                $tmp = "Designer/index";
                $user = $this->getDesingerInfoByAdmin($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"],2);
                //如果城市字段为空，则先选择所在城市
                if(empty($_SESSION["u_userInfo"]["cs"])){
                    //获取城市信息
                    //$citys = getCityArray();
                    //$this->assign("citys", json_encode($citys));
                    $citytmp = $this->fetch("Home/citytmp");
                    $this->assign("citytmp",$citytmp);
                }
                $info["user"] = $user;
                $info["user"]["unreadsystem"] = $this->getUnSystemNoticeCount($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"],2);

                //获取邀请设计师的装修公司数量
                $team = D("Team")->getUserTeamInfo($_SESSION["u_userInfo"]["id"]);
                $info["user"]["unreadinvite"] = 0;
                if(count($team) <= 0){
                    $info["user"]["unreadinvite"] =  D("Team")->getInviteCompanyCount($_SESSION["u_userInfo"]["id"]);
                }
                break;
            case '3':
            case '6':
                //装修公司
                //获取装修公司的基本信息
                $user = $this->getCompanyInfoByAdmin($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"]);

                $beforeTime = strtotime("-7 day");
                if($beforeTime > $user["check_time"]){
                    $user["check_warning"] = true;
                }
                $info["user"] = $user;
                $info["user"]["tel_safe"] =substr_replace($info["user"]["tel_safe"],"*****",3,5);
                $mail = strstr($info["user"]["mail_safe"],"@",true);
                $prefix = strstr($user["mail_safe"],"@");
                $info["user"]["mail_safe"] =substr_replace($mail,"*****",3,5).$prefix;
                $info["user"]["unreadsystem"] = $this->getUnSystemNoticeCount($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"]);
                //获取公司的完成资料信息
                $baseInfo = $this->getCompanyStatusByAdmin($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"]);
                $info["perfect"] = $baseInfo["base"]["count"];
                //获取公司的操作日志,默认1个月内
                $logs = $this->getLogs($_SESSION["u_userInfo"]["id"]);
                $info["logs"] = $logs;
                $tmp ="index";
                //如果是非会员公司,查询最新订单
                if(!($user["on"] == 2 && $user["fake"] == 0)){
                    $orders = $this->getOrders(100);
                    foreach ($orders as $k => $v) {
                        $var = trim($v['xiaoqu']);
                        $var = preg_replace(array("/[a-zA-Z0-9]+/","/[[:punct:]]/"), "",$var);
                        if(empty($var)){
                            $orders[$k]['xiaoqu'] = '-';
                        }
                    }
                    $info["orders"] = $orders;
                }

                //自动更新活跃度
                /*
                算法规则：公式：  100 + 5*A + 30*B + 50*C + 10*D + E*2 + M
                统计项：登陆次数、发布的日记、百科、问答、自发活动、参与齐装网平台的活动

                统计规则：
                默认缺省值为100；
                A=近三十天登录次数（每天登陆多次即默认一次，A小于等于30）；
                B=参与齐装网组织的活动次数 30；
                C=装修公司 近三十天 内部自发组织活动次数，若C>2，则赋值C=2；
                D=装修公司发表的通过审核的百科次数
                E=装修公司 近三十天 发布的案例数 若E>50，则赋值E=50；
                M= 近三十天 5*装修公司提出问题数量+5*装修公司发表答案数量 ，若M大于等于300，则 M 赋值M=300
                */

                if(cookie('updateActives') == ''){
                    $uid = $_SESSION["u_userInfo"]['id'];
                    $DB = D('UserActive');
                    //获取30天内登录次数 x 5
                    $active['loginCount'] = $DB->getLoginCount($uid) * 5;
                    //参与齐装网组织的活动次数
                    $active['activityEnroll'] = $DB->getActivityEnroll($uid) * 30;
                    //取装修公司自发活动数
                    $active['events'] = $DB->getNewEvents($uid);//新版活动
                    $active['events'] = $active['events'] > 2 ? 2 : $active['events'];
                    $active['events'] = $active['events'] * 50;

                    //取装修公司通过审核的百科数
                    $active['baikeCount'] = $DB->getBaikeCount($uid) * 10;
                    //装修公司提出问题数量
                    $askCount = $DB->getAskCount($uid) * 5;
                    //装修公司发表答案数量
                    $answerCount = $DB->getAnswerCount($uid) * 5;

                    $wendaCount = $askCount + $answerCount;
                    $wendaCount = $wendaCount > 300 ? 300 : $wendaCount;
                    $activteScore = $active['loginCount']+$active['activityEnroll']+$active['events']+$active['baikeCount']+$wendaCount;

                    $tempdata['activte_score'] = 100 + $activteScore;
                    M("user")->where(array('id' => $uid))->save($tempdata);
                    cookie('updateActives','1',86400);
                }

                //查询装修公司是否已审核营业执照
                if (cookie("QZ_YYZZ") == null) {
                    //查询装修公司是否已审核营业执照
                    $count = D("SaleBusinessLicence")->findCompanyBusinessLicenceCount(session("u_userInfo.id"));
                    if ($count == 0) {
                        $BusinessLicenceTips = $this->fetch("Companyinfo:businesslicencetips");
                        cookie("QZ_YYZZ",1,time()+86400);
                        $this->assign("BusinessLicenceTips",$BusinessLicenceTips);
                    }
                }
                //获取装修公司排名
                if(!empty($info["user"]["cs"])&&isset($info["user"]["cs"])){
                    $paiming = $this->getPaiming($info["user"]["id"],$info["user"]["cs"]);
                    $this->assign("paiming",$paiming);
                }

				//查询公司微信绑定的信息
				$wechatList = D("Orderwechar")->getOrderNoticeList($_SESSION["u_userInfo"]["id"]);
				if (!empty($wechatList)) {
					$wx_unionid = [];
					foreach ($wechatList as $key => $value) {
						$wx_unionid[] = $value["wx_openid"];
					}
					//过滤重复的数据
					$wx_unionid = array_unique($wx_unionid);
					import('Library.Org.Util.Mywechat');
					$Mywechat = new \Mywechat();
					$wechatList = $Mywechat->getWecharUserInfo($wx_unionid);
					$this->assign('wechat_list', $wechatList); //取前两个用户
				}
                //查询可抢订单

                $deliver_area = D("User")->getCompanyDeliverAreaid($_SESSION['u_userInfo']['id'], 'area_id', true);
				if(!empty($deliver_area)){
                    $where = [
                        'qx' =>  ['in',$deliver_area],
                        'company' => $user['id']
                    ];

//                    if ($userInfo['mianji']) {
//                        $where['mianji'] = ['egt', $userInfo['mianji']];
//                    }
//                    if ($userInfo['lxs']) {
//                        if (in_array($userInfo['lxs'], [1, 2])) {
//                            $where['lxs'] = $userInfo['lxs'] == 1 ? $userInfo['lxs'] : ['in', [2, 3]];
//                        }
//                    }

                    if ($user['lx']) {
                        if (in_array($user['lx'], [1, 2])) {
                            $where['lx'] = $user['lx'];
                        }
                    }

                    //过滤当前公司在抢单池中已经处理过(抢单/赠单)的订单
                    $robDb = new OrderRobPoolModel();
                    $passOrder = $robDb->getPassOrderRobList($where);
                    if (count($passOrder) > 0) {
                        $where['passOrder'] = array_column($passOrder, 'id');
                    }
                    //过滤勾选了不抢的订单
                    $robOrderLogic = new RobOrdersLogicModel();
                    $robPass = $robOrderLogic->getNoRobOrder($user['id']);
                    if (count($robPass) > 0) {
                        if (!empty($where['passOrder'])) {
                            $where['passOrder'] = array_merge($where['passOrder'], array_column($robPass, 'order_id'));
                        } else {
                            $where['passOrder'] = array_column($robPass, 'order_id');
                        }
                    }

                    $count = $robDb->getOrderRobCount($where,$_SESSION["u_userInfo"]['id']);
                    $this->assign('rob_count',$count);
                }
				break;
            case "4":
                redirect("".C('QZ_YUMING_SCHEME')."://u.jiaju.qizuang.com");
                break;
            default:
                header("LOCATION:".C('QZ_YUMING_SCHEME')."://u.qizuang.com");
                die();
                break;
        }
        $info["ssid"] = authcode(session_id(),"");
        //添加修改密码提醒(每隔60天修改一次密码)
        $this->checkPassTime($info['user']);
        $this->assign("info",$info);
        $this->display($tmp);
    }

    /**
     * 获取最近签单
     */
    public function getRecentQd()
    {
        $recentQiandan = S('Cache:RecentQiandan:Neq:City:' . $_SESSION['u_userInfo']['cs']);
        if (!$recentQiandan) {
            $recentQiandan = \Common\Model\UserModel::getRecentQdList();
            if ($recentQiandan) {
                S('Cache:RecentQiandan:Neq:City:' . $_SESSION['u_userInfo']['cs'], $recentQiandan, 300);
            }
        }
        $this->ajaxReturn([
            'error_msg' => '获取成功',
            'error_code' => 0,
            'data' => ['list' => $recentQiandan]
        ]);
    }

    private function getPaiming($comid,$cs){
        //综合实力
        $result[1] = S('Cache:Sub:Company:Index:star:' . $cs);
        if(!$result[1]){
            $result[1] = D("Usercompany")->getShiliList($cs);
            S('Cache:Sub:Company:Index:star:' . $cs, $result[1], rand(600, 900));
        }

        foreach($result[1] as $key=>$val){
            $zonghe[$val['id']] = $key+1;
        }

        $paiming["shili"] = $zonghe[$comid];
        //签单
        $result[2] = D("Usercompany")->getQiandanList($cs);

        foreach($result[2] as $key=>$val){
            $qiandan[$val['id']] = $key+1;
        }
        
        $paiming["qiandan"] =  $qiandan[$comid];

        //量房
        $result[3] = D("Usercompany")->getLiangfangList($cs);
        foreach($result[3] as $key=>$val){
            $liangfang[$val['id']] = $key+1;
        }

        $paiming["liangfang"] = $liangfang[$comid];
        return $paiming;
    }

    /**
     * 成功页面
     * @return [type] [description]
     */
    public function success(){
        $type = I("get.type");
        $id = I("get.id");
        $info["header"] ="恭喜,操作成功！";
        switch ($type) {
            case 'cases':
                //查询案例的信息
                $case = $this->getCaseInfoAndImgs($id,$_SESSION["u_userInfo"]["id"]);
                $info["imgs"] = $case["imgs"];
                $info["title"] =$_SESSION["u_userInfo"]["jc"].date("Y")."年最新【".$case["title"]."】效果图";
                $info["url"] = C('QZ_YUMING_SCHEME')."://".$_SESSION["u_userInfo"]["bm"].".".C("QZ_YUMING")."/caseinfo/".$id.".shtml";
                $info["redirect"]  = C('QZ_YUMING_SCHEME')."://u.qizuang.com/cases/";
                $info["share"] = true;
                break;
             case 'descases':
                //查询案例的信息
                $case = $this->getCaseInfoAndImgs($id);
                $info["imgs"] = $case["imgs"];
                $info["title"] = $_SESSION["u_userInfo"]["name"].date("Y")."年最新【".$case["title"]."】效果图";
                $info["url"] = C('QZ_YUMING_SCHEME')."://".$_SESSION["u_userInfo"]["bm"].".".C("QZ_YUMING")."/caseinfo/".$id.".shtml";
                $info["redirect"]  = C('QZ_YUMING_SCHEME')."://u.qizuang.com/descase/";
                $info["share"] = true;
                break;
            case "designer":
                $info["redirect"]  = C('QZ_YUMING_SCHEME')."://u.qizuang.com/desinfo/";
                $info["share"] = false;
                break;
            case 'team':
                $info["redirect"]  = C('QZ_YUMING_SCHEME')."://u.qizuang.com/team/";
                $info["share"] = false;
                break;
            case 'article':
                //查询公司文章信息
                $article = D("Info")->getInfoById($id,$_SESSION["u_userInfo"]["id"]);
                $info["title"] = $_SESSION["u_userInfo"]["jc"].date("Y")."年最新资讯【".$article['title']."】";
                $info["url"] = C('QZ_YUMING_SCHEME')."://".$_SESSION["u_userInfo"]["bm"].".".C("QZ_YUMING")."/zixun_info/".$id.".shtml";
                $info["redirect"]  = C('QZ_YUMING_SCHEME')."://u.qizuang.com/article/";
                $info["share"] = true;
                break;
            case 'des_article':
                //查询设计师文章信息
                $article = M('article')->find($id);
                $info["title"] = $_SESSION["u_userInfo"]["name"].date("Y")."年最新博客【".$article['title']."】";
                $info["url"] = C('QZ_YUMING_SCHEME')."://".$_SESSION["u_userInfo"]["bm"].".".C("QZ_YUMING")."/zixun_info/".$id.".shtml";
                $info["redirect"]  = C('QZ_YUMING_SCHEME')."://u.qizuang.com/desblog/";
                $info["share"] = true;
                break;
             case 'activit':
                //查询公司优惠信息
                $article = D("Info")->getInfoById($id,$_SESSION["u_userInfo"]["id"]);
                $info["title"] = $_SESSION["u_userInfo"]["jc"].date("Y")."年最新优惠【".$article["title"]."】";
                $info["url"] = C('QZ_YUMING_SCHEME')."://".$_SESSION["u_userInfo"]["bm"].".".C("QZ_YUMING")."/zixun_info/".$id.".shtml";
                $info["redirect"]  = C('QZ_YUMING_SCHEME')."://u.qizuang.com/activityinfo/";
                $info["share"] = true;
                break;
            case 'order':
                //业主订单发布
                $info["redirect"]  = C('QZ_YUMING_SCHEME')."://u.qizuang.com/need/";
                $info["share"] = false;
                break;
            case 'preuser':
                //注册用户
                $info["title"] = '注册业主帐号成功';
                $info["redirect"]  = C('QZ_YUMING_SCHEME')."://u.qizuang.com/peruser/";
                $info["share"] = false;
                break;
            case 'baike':
                //注册用户
                $info["title"] = '百科提交成功，请等待管理员审核。';
                $info["redirect"]  = C('QZ_YUMING_SCHEME')."://u.qizuang.com/baike/";
                $info["share"] = false;
                break;
            default:
                $this->_error();
                break;
        }

        $this->assign("info",$info);
        $this->display();
    }

    /**
     * 修改体检信息
     * @return [type] [description]
     */
    public function editbaseinfo(){
        if($_POST){
           if(I("post.score") !== ""){
                $data["check_score"] = I("post.score");
                $data["check_time"] = time();
                if(I("post.time") !== ""){
                    $data["check_time"] = I("post.time");
                }
           }
           D("User")->edtiUserInfo($_SESSION["u_userInfo"]["id"],$data);
        }
    }

    /**
     * 检测帐号状态
     * @return [type] [description]
     */
    public function chkstatus(){
        $company_status = $this->getCompanyStatusByAdmin($_SESSION["u_userInfo"]["id"],$_SESSION["u_userInfo"]["cs"]);
        if(!empty($company_status) && isset($_SESSION["u_userInfo"])){
            $this->ajaxReturn(array("data"=>$company_status,"info"=>"","status"=>1));
        }
        $this->ajaxReturn(array("data"=>"","info"=>"检测失败","status"=>0));
    }

    /**
     * 绑定用户城市
     */
    public function setCity(){
         if($_POST){
            $data = array(
                    "qx"=>I("post.qx"),
                    "cs"=>I("post.cs")
                          );

            $i = D("User")->edtiUserInfo($_SESSION["u_userInfo"]["id"],$data);
            if($i!== false){
                $_SESSION["u_userInfo"]["cs"] = I("post.cs");
                $_SESSION["u_userInfo"]["qx"] = I("post.qx");
                //通过城市编号查询城市的BM
                $city = D("Area")->getCityInfoById(I("post.cs"));
                $_SESSION["u_userInfo"]["bm"] = $city["bm"];
                $_SESSION["u_userInfo"]["cname"] = $city["oldName"];
                $this->ajaxReturn(array("data"=>"","info"=>"","status"=>1));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"操作失败,请稍后再试！","status"=>0));
        }
    }

    /**
     * 获取最新订单
     * @return [type] [description]
     */
    private function getOrders($limit){
        $orders =  D("Orders")->getNewOrders(100);
        foreach ($orders as $key => $value) {
            //替换时间
            $min   =  rand(1,10);
            $orders[$key]["time"] = $min."分钟之前";
        }
        return $orders;
    }

    /**
     * 获取装修公司基本信息
     * @param  [type] $comid [description]
     * @param  [type] $cs    [description]
     * @return [type]        [description]
     */
    private function getCompanyInfoByAdmin($comid,$cs){
        $user =  D("User")->getCompanyInfoByAdmin($comid,$cs);
        return $user;
    }

    /**
     * 获取装修公司打分信息
     * @return [type] [description]
     */
    private function getCompanyStatusByAdmin($comid,$cs){
        $user =  D("User")->getCompanyStatusByAdmin($comid,$cs);
        if(count($user)>0){
            //上次的得分
            $status["check_score"] = $user["check_score"];
            //上次的检测时间
            $status["check_time"] = $user["check_time"];
            //获取基本信息数组
            $baseInfo = array("qc","jc","kouhao","name","cal","cals","sex","tel","qq","qq1","logo");
            //获取详细信息数组
            $detailsInfo = array("fuwu","quyu","fengge","jiawei","chengli","guimo","jianjie","qc",);
            //基础分数
            //基本信息分数
            $status['base'] = array("name"=>"企业信息","score"=>5,"percentage"=>20,"text"=>'完善了<em class="count">%s</em>%%的企业信息，得<em class="score">%s</em>分，共20分</span>',"icon"=>"icon-remove-circle","info"=>"立刻完善","href"=>"/info/basic");
            //详细信息分数
            $status['details_score'] = 10;
            //资质证书分数
            $status['fcount_score'] = 5;
            //家装分数
            $status['jz'] = array("name"=>"家装案例","score"=>10,"count"=>empty($user["jcount"])?0:$user["jcount"],"percentage"=>10,"text"=>'已上传<em class="count">%s</em>套符合要求的家装案例，得<em class="score">%s</em>分，共10分',"icon"=>"icon-remove-circle","info"=>"立刻上传","href"=>"/caseup/");
            //家装分数
            $status['gz'] = array("name"=>"公装案例","score"=>10,"count"=>empty($user["gcount"])?0:$user["gcount"],"percentage"=>10,"text"=>'已上传<em class="count">%s</em>套符合要求的公装案例，得<em class="score">%s</em>分，共10分',"icon"=>"icon-upload","info"=>"立刻上传","href"=>"/caseup/");
            //家装分数
            $status['zj'] = array("name"=>"在建工地案例","score"=>10,"count"=>empty($user["zcount"])?0:$user["zcount"],"percentage"=>10,"text"=>'已上传<em class="count">%s</em>套符合要求的在建工地案例，得<em class="score">%s</em>分，共10分',"icon"=>"icon-upload","info"=>"立刻上传","href"=>"/caseup/");
            //资讯分数
            $status['info'] = array("name"=>"文章资讯","score"=>10,"percentage"=>10,"text"=>'已发布<em class="count">%s</em>条文章资讯，得<em class="score">%s</em>分，共10分',"icon"=>"icon-upload","info"=>"立刻发布","href"=>"/articleup/");
            //设计师分数
            $status['designer'] = array("name"=>"推荐设计师","score"=>10,"percentage"=>10,"text"=>'已推荐<em class="count">%s</em>位优秀设计师，得<em class="score">%s</em>分，共10分',"icon"=>"icon-upload","info"=>"立刻推荐","href"=>"/teamup/");
            //案例更新频率分数
            $status['case'] = array("name"=>"案例更新","score"=>20,"percentage"=>20,"text"=>'依据您的案例更新频率，得<em class="score">%s</em>分，共20分',"icon"=>"icon-remove-circle","info"=>"立刻完善","href"=>"/caseup/");
            //最新活动分数
            $status['yhhd'] = array("name"=>"优惠活动","score"=>5,"percentage"=>5,"text"=>'依据您已发布的优惠活动，得<em class="score">%s</em>分，共5分',"icon"=>"icon-remove-circle","info"=>"立刻完善","href"=>"/activityinfo/");
            //绑定安全邮箱/电话
            $status["safe"] = array("name"=>"帐号安全","score"=>5,"percentage"=>5,"text"=>'绑定安全邮箱/安全手机，得<em class="score">%s</em>分，共5分',"icon"=>"icon-remove-circle","info"=>"立刻绑定","href"=>"#banging");
            if(!$user["tel_safe_chk"] || !$user["mail_safe_chk"] ){
                 $status["safe"]["score"] = 3;
            }
            $i = 0;

            //企业评分
            //1.基本资料评分
            foreach ($baseInfo as $key => $val) {
                if(empty($user[$val])){
                    $status['base']["score"] = 3;
                    $i ++;
                }
            }

            //2.详细资料评分
            foreach ($detailsInfo as $key => $val) {
               if(empty($user[$val])){
                    $status['details_score'] = 7;
                    $i ++;
               }
            }

            if($user["fcount"] < 10){
                $i += 3;
                $status['fcount_score'] = 3;
            }

            if($user["jcount"] < 10){
                $status['jz']["score"]  = empty($user["jcount"])?0:$user["jcount"];
            }

            if($user["gcount"] < 10){

                $status['gz']["score"]  = empty($user["gcount"])?0:$user["gcount"];
            }

            if($user["zcount"] < 10){
                $status['zj']["score"]  = empty($user["zcount"])?0:$user["zcount"];
            }

            if($user["infocount"] < 10){
                $status['info']["score"]  = empty($user["infocount"])?0:$user["infocount"];
            }

            if($user["dcount"] < 10){
               $status['designer']["score"]  = empty($user["dcount"])?0:$user["dcount"];
            }

            if(strtotime("-7 day") >= $user["lasttime"] ){
                //超过7天
                 $status['case']["score"]  = 0;
            }elseif(strtotime("-5 day") >= $user["lasttime"] ){
                //超过5天
                $status['case']["score"]  = 10;
            }elseif(strtotime("-3 day") >= $user["lasttime"] ){
                //超过3天
                 $status['case']["score"]  = 15;
            }

            if(strtotime("-30 day") >= $user["lastinfotime"] ){
                //超过30天
                $status['yhhd']["score"]  = 0;
            }

        }

        if($i == 0){
            $status['base']["count"] = 100;
        }else{
            $allcount = count($baseInfo)+count($detailsInfo);
            $status['base']["count"] = (1- round($i/$allcount,2))*100;
        }

        $status['base']["score"] +=  $status['details_score'] + $status['fcount_score'];

        //结算评分
        $status['base']["text"] = sprintf($status['base']["text"],$status['base']["count"],$status['base']["score"]);
        $status['jz']["text"]   = sprintf( $status['jz']["text"],$status['jz']["count"],$status['jz']["score"]);
        $status['gz']["text"]  = sprintf($status['gz']["text"],$status['gz']["count"],$status['gz']["score"]);
        $status['zj']["text"]  = sprintf($status['zj']["text"],$status['zj']["count"],$status['zj']["score"]);
        $status['info']["text"] = sprintf($status['info']["text"],$status['info']["count"],$status['info']["score"]);
        $status['designer']["text"] = sprintf($status['designer']["text"],$status['designer']["count"],$status['designer']["score"]);
        $status['case']["text"] = sprintf($status['case']["text"],$status['case']["score"]);
        $status['yhhd']["text"] = sprintf($status['yhhd']["text"],$status['yhhd']["score"]);
        $status["safe"]["text"] = sprintf($status['safe']["text"],$status['safe']["score"]);

        if($_SESSION["u_userInfo"]["on"] != 2){
            //如果非会员,去除优惠活动
            unset($status['yhhd']);
        }
        //获取模版
        $tmp = $this->fetch("companystatustmp");
        $status["tmp"] = $tmp;
        return $status;
    }

    private function getLogs($uid){
        $begin = date("Y-m-d H:i:s",strtotime("-1 week"));
        $end = date("Y-m-d H:i:s",time());
        $result = D("Loguser")->getLogList($uid,$begin,$end);
        $logs = array();
        foreach ($result as $key => $value) {
            if(strpos($value['remark'],'platform') !== false){
                $value['remark'] = '';
            }
            $time = date("Y-m-d",strtotime($value["time"]));
            if(!array_key_exists($time, $logs)){
                $logs[$time]["date"] = strtotime($value["time"]);
            }
            $value["time"] = strtotime($value["time"]);
            $logs[$time]["child"][] = $value;
        }
        return $logs;
    }

    private function getCaseInfoAndImgs($id,$cid){
        $result = D("Cases")->getCaseInfoAndImgs($id,$cid);
        $cases = array();
        foreach ($result as $key => $value) {
            if($key == 0){
                $cases["title"] = $value["title"];
            }
            if($value["img_host"] == "qiniu"){
                $cases["imgs"] .= C('QZ_YUMING_SCHEME')."://".C("QINIU_DOMAIN")."/".$value["img_path"]."|";
            }else{
                $cases["imgs"] .= C('QZ_YUMING_SCHEME')."://".C("STATIC_HOST1").$value["img_path"].$value["img"]."|";
            }
        }
        $cases["imgs"] = substr($cases["imgs"], 0,strlen($cases["imgs"])-1);
        return $cases;
    }

     /**
     * 获取用户的未读信息
     * @param  [type] $comid [公司编号]
     * @param  [type] $cs    [所在城市]
     * @return [type]        [description]
     */
    private function getUnSystemNoticeCount($id,$cs,$classid =3){
        return D("Usersystemnotice")->getUnSystemNoticeCount($id,$cs,$classid);
    }

    /**
     * 获取业主的信息
     * @return [type] [description]
     */
    public function getUserInfoByAdmin($id,$cs,$classid){
        $user = D("User")->getUserInfoByAdmin($id,$cs,$classid);
        return $user;
    }

    /**
     * 获取设计师的信息
     * @return [type] [description]
     */
    public function getDesingerInfoByAdmin($id,$cs){
        $user = D("User")->getDesingerInfoByAdmin($id,$cs);
        return $user;
    }


    //获取小区列表
    public function getjias(){
        $keyword = I("get.keyword");
        if(!empty($keyword)){
            $map['name'] = array("LIKE","%$keyword%");
            $map['cid'] = $_SESSION['u_userInfo']['cs'];
            $infos = M('xiaoqu')->field('id,name')->limit(15)->where($map)->select();
            if(!empty($infos)){
                $this->ajaxReturn(array("data"=>$infos,"info"=>"1","status"=>1));
            }else{
                $this->ajaxReturn(array("data"=>$infos,"info"=>"0","status"=>1));
            }
        }
    }

    /**
     * 获取订单包信息
     */
    public function loadpackageinfo()
    {
        if (isset($_SESSION["u_userInfo"])) {
            //签返会员逻辑
            //获取订单包的信息
            $qianLogic = new UserLogicModel();
            $package =  $qianLogic->getPackageInfo($_SESSION["u_userInfo"]["id"]);

            $package["qiandanInfo"] = $qianLogic->getPackageQianDanInfo($_SESSION["u_userInfo"]["id"]);

            //获取签单信息
            $this->assign("package",$package);
            $tmp = $this->fetch("packageinfo");
            $this->ajaxReturn(array("data"=>$tmp,"status"=>1));
        }
        $this->ajaxReturn(array("data"=>$tmp,"status"=>0));
    }

    public function loadpackageorderinfo()
    {
        if (isset($_SESSION["u_userInfo"])) {
            //签返会员逻辑
            //获取签单数据
            $qianLogic = new UserLogicModel();
            $package =  $qianLogic->loadpackageorderinfo($_SESSION["u_userInfo"]["id"]);

            //获取签单信息
            $this->assign("package",$package);
            $tmp = $this->fetch("packmoney");
            $this->ajaxReturn(array("data"=>$tmp,"status"=>1));
        }
        $this->ajaxReturn(array("data"=>$tmp,"status"=>0));
    }

    /**
     * 添加修改密码提醒(每隔30天提示修改一次密码)
     */
    private function checkPassTime($userInfo)
    {
        $check_alert = S('User:Home:index:CheckAlert:'.$userInfo['id']);
        if($check_alert !=1){
            S('User:Home:index:CheckAlert:'.$userInfo['id'],1,2592000);
            $this->assign('check_alert', 1);
        }
    }
}