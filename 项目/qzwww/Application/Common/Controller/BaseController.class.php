<?php
namespace Common\Controller;
use Common\Model\Logic\UcenterUserLogicModel;
use Monolog\Handler\IFTTTHandler;

use Think\Controller;
class BaseController extends Controller {
    public $bm = "www";
    public $cityInfo = null;
    protected $SCHEME_HOST = [];
    public function _initialize(){
        //过滤错误路由
        B("Common\Behavior\FilterErrorUrl");

        //静态文件路由地址
        $static_host = "";
        $this->assign("static_host",$static_host);

        $qizuang = array();
        //禁止 qq管家后台扫描
        B("Home\Behavior\BrowserScanCheck");

        // 访问来源追踪,来源进行cookie标记
        // 逻辑当有get参数 src的时候,例如 ?src=video
        // 打客户端浏览器cookie标记为  cookie('src_mark', I('get.src'), 3600 * 24); // 指定cookie保存时间为24小时
        B("Common\Behavior\MarkTrackingOrderSourceCheck");

        //设置合法的域名bm
        //设置后请更新Common/config文件的REGISTER_URL配置
        $bmList = array("www","u","oauthapi","m","meitu");

        // 站点多域名访问支持 动态化httphost
        $this->SCHEME_HOST = getSchemeAndHost();

        /** 'isSsl' => boolean false
         * 'scheme' => string 'http'
         * 'scheme_full' => string 'http://'
         * 'host' => string 'www.qizuang.com'
         * 'domain' => string 'qizuang.com'
         * 'scheme_host' => string 'http://www.qizuang.com'
         */

        $this->assign('SCHEME_HOST', $this->SCHEME_HOST);

        // 程序运行环境dev prod
        $this->assign('APP_ENV',getAppEnv());

        // 站点多域名访问显示控制
        // qizuang域名给个标识为1，非qizuang域名访问模板判断中会用到
        if (C("QZ_YUMING") == $this->SCHEME_HOST['domain']) {
            $FLAG_DOMAIN_QIZUANG = 1;
        } else {
            $FLAG_DOMAIN_QIZUANG = 0;
        }
        $this->assign("FLAG_DOMAIN_QIZUANG",$FLAG_DOMAIN_QIZUANG);

        //获取域名头
        $bm = explode(".", $_SERVER['HTTP_HOST']);

		$url_houst= $bm = $bm[0];
		//seo 添加 alternate 属性

		$uri_formate = trim($_SERVER['REQUEST_URI'],'/');
		$this->assign('uri_formate', $uri_formate);
		preg_match('/html$/',$uri_formate,$m);
		if (count($m) == 0) {
			$uri = empty($uri_formate)?'':$uri_formate.'/';
		}else{
			$uri = empty($uri_formate)?'':$uri_formate;
		}
		if($bm == 'www'){
			$alternate = $this->SCHEME_HOST['scheme_full'].'m.'.$this->SCHEME_HOST['domain'].'/'.$uri;
		}else{
			//历史遗留问题 ：招标页面 company页面，移动端不存在分站 特殊处理
			if($uri_formate == 'zhaobiao'){
				$alternate = $this->SCHEME_HOST['scheme_full'].'m.'.$this->SCHEME_HOST['domain'].'/'.$uri;
			}else{
				$alternate = $this->SCHEME_HOST['scheme_full'].'m.'.$this->SCHEME_HOST['domain'].'/'.$bm.'/'.$uri;
			}
		}

		$this->assign('alternate', $alternate);

        $this->bm = $bm;
        $cityId = S("Cache:Base:City:".$bm);
        if (!$cityId) {
            $cityId =  D("Common/Quyu")->getCityInfoByBm($bm);
            $cityId = $cityId["cid"];
            S("Cache:Base:City:".$bm,$cityId);
        }

        // 取出相关的城市
        if(empty($cityId) && !in_array($bm,$bmList)){
            $this->_empty();
            die(); //结束
        }else{
            if(empty($cityId)){
                $_SESSION["cityId"] = "000001";
                //更换城市
                if($_SESSION['u_userInfo']['id']){
                    $uuid = $_SESSION['u_userInfo']['id'];
                    S('Citycache:CityId:'.$uuid,'000001',60*24);
                }
            }else{
                $_SESSION["cityId"] = $cityId;
                //更换城市
                if($_SESSION['u_userInfo']['id']){
                    $uuid = $_SESSION['u_userInfo']['id'];
                    S('Citycache:CityId:'.$uuid,$cityId,60*24);
                }
            }
        }

        //首次访问之前并访问过其他分站的,主站首页访问跳转到分站首页
        if(cookie("w_cityid") != null && empty($_SERVER["HTTP_REFERER"])){
            $this->assign("redirect_url",$this->SCHEME_HOST['scheme_full'].cookie("w_cityid").".".$this->SCHEME_HOST['domain']."");
        }

        //如果当前的分站存在分站信息
        if (!empty($cityId)) {
            $city = getCityInfo($bm);
            cookie('w_cityid',$bm,array('expire'=>0,'domain' => '.'.APP_HTTP_DOMAIN));
        } elseif (cookie("w_cityid") != null) {
            //如果存在分站城市标识
            $bm = cookie("w_cityid");
            $city = getCityInfo($bm);
        } else {
            //域名是主站时,如果有推广参数，www.qizuang.com/zxbj/?src=p-bd-hz-abc&bm=hz,则按照推广参数
            $bm = I("get.ct");
            if (!empty($bm)) {
                $city = getCityInfo($bm);
                setcookie("w_cityid",$city["bm"],0,'/', '.'.APP_HTTP_DOMAIN);
            }
        }

        //非分站域名并且首次进入时
        if (in_array($bm,$bmList) && count($city) == 0) {
            if (cookie("w_cityid") != null) {
                $bm = cookie("w_cityid");
                $city = getCityInfo($bm);
            }
        }

        //如果没有获取到城市信息，IP定位
        if (count($city) == 0) {
            //ip定位
            $cityInfoByIp = json_decode(getCityInfoByIp(), true);
            if ($cityInfoByIp["status"] == 1) {
                $cookie_city = $cityInfoByIp['data'];
                $city = getCityInfo($cookie_city["bm"]);
            }
        }

        if (count($city) > 0) {
            //获取城市信息
            $cityInfo = array(
                "bm"=>$city["bm"],
                "name"=>$city["oldName"],
                "cname" => $city["oldName"],
                "id" =>$city["cid"],
                "adj_city"=>$city["adj_city"],
                "usercount"=>$city["usercount"],
                "lng"=>$city["lng"],
                "lat"=>$city["lat"],
                "province"=>str_replace("省","",$city["province"]),
                "child" => $city['child']
            );

            //添加cityInfo到cookie，用户中心使用
            $userCity = [
                "bm"=>$cityInfo["bm"],
                "name"=>$cityInfo["name"],
                "cname"=>$cityInfo["name"],
                "id" =>$cityInfo["id"]
            ];

            cookie('QZ_CITY',json_encode($userCity),array('expire'=>86400 * 7,'domain' => '.'.APP_HTTP_DOMAIN));
            cookie('QZ_USERCITY',json_encode($userCity),array('expire'=>86400,'domain' => '.'.APP_HTTP_DOMAIN));
            //记录PC端登陆的城市站点ID
            cookie('cityId_for_coldt',$cityInfo['id'],array('expire'=>86400,'domain' => '.'.APP_HTTP_DOMAIN));
            $this->assign("theCityId",$cityInfo["id"]);
        } else {
            setcookie("QZ_CITY",-1,time()-1,'/', '.'.APP_HTTP_DOMAIN);
        }

        //获取当前城市的城市信息
        if (in_array($this->bm,$bmList)) {
            $w_cityid = cookie('w_cityid');
            if ($w_cityid !== null) {
                $this->bm =  $w_cityid;
            }
        }

        $result = S("CACHE:CITY:BM:".$this->bm);
        if (!$result) {
            $result = D("Quyu")->getCityInfoByBm($this->bm);
            S("CACHE:CITY:BM:".$this->bm,$result,900);
        }

        if (count($result) > 0) {
            $city = array(
                "bm"=>$result["bm"],
                "name"=>$result["oldName"],
                "cname" => $result["oldName"],
                "id" =>$result["cid"],
                "adj_city"=>$result["adj_city"],
                "usercount"=>$result["usercount"],
                "lng"=>$result["lng"],
                "lat"=>$result["lat"],
                "province"=>str_replace("省","",$result["province"]),
                "child" => $result['child']
            );

            $this->cityInfo = $city;
            $this->assign("cityInfo",$city);
            //seo需求： 以www为前缀的页面，去除title中的“城市”
            if($url_houst === 'www'){
				$this->assign('title',OP('QZ_SITE_NAME'));
			}else{
				$this->assign('title',$city['name'].OP('QZ_SITE_NAME'));
			}
        }

        $this->assign('cityfile','//'.OP('QINIU_DOMAIN').'/common/js/'.OP('ALL_CITY_JSON'));

        //412 齐装C端用户账号体系 session数据重新获取
        //如果当前不是装修公司,设计师登录
        if(empty($_SESSION['u_userInfo']) || $_SESSION['u_userInfo']['classid'] == 1){
            $token = $_COOKIE['center_password_token'];

            if(empty($token)){
                session("u_userInfo",null);
            }else{
                //解析token,过期 清除session,否则拿redis值
                import('Library.Org.Passport.JwtToken','','.php');
                $jwt = new \JwtToken();
                $decode = $jwt->decode($token);
                $key = md5($decode['uuid'].$decode['addr'].$decode['device']);
                //获取到redis值,获取用户信息放入session,redis无记录,清除当前登录信息
                if(!empty(S('U_CENTER:PASSPORT:'.$key))){
                    //设置session用户基本信息
                    (new UcenterUserLogicModel())->setUserInfo($decode['uuid']);
                }else{
                    session("u_userInfo",null);
                    setcookie("center_password_token",1,time()-3600,'/', '.'.APP_HTTP_DOMAIN);
                }
            }
        }

    }

    /**
     * 获取标识是否打开弹窗
     * @return [type] [description]
     */
    public function getShowWindowTmp()
    {
        $src = I("get.src");
        if (!empty($src)) {
            //判断来源标识是否可以打开弹窗
            $OrderSources = D("OrderSource")->findSource($src);
            if ((array_key_exists($src, $OrderSources) && !$OrderSources[$src]["isshow"])) {
                return true;
            }
        }
        return false;
    }

    //空操作
    public function _empty() {
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        $this->display('Common@Public/404');
        die();
    }

    /**
     * [_error description]
     * @return [type] [description]
     */
    public function _error(){
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        $this->display('Common@Public/404');
        die();
    }


    private function getErrorPage(){
        //取美图，攻略，问答的图片
        $info['meitu'] = D("Common/Meitu")->getRankMeitu(10);
        $info['gonglue'] = D('Common/Article')->getArticle(8);
        $info['wenda'] = D('Common/Ask')->getImgQuestion(8);
        return $info;
    }

    /**
     * 获取顶部广告
     */
    private function getTopBanner($cityInfo)
    {
        //判断是否是分站
        if ($cityInfo['id']) {
            $topBannerF = D('Common/Advbanner')->getTopList('home_topbanner', $cityInfo['id']);
            if($topBannerF){
                return $topBannerF;
            }
        }
        //获取总站数据
        $topBannerZ = D('Common/Advbanner')->getTopList('home_topbanner', '000001');
        //获取全站数据
        $topBannerQ = D('Common/Advbanner')->getTopList('home_topbanner', '0');
        //同时有数据, 选开始时间后面的
        if ($topBannerZ && $topBannerQ) {
            if ($topBannerZ['start_time'] > $topBannerQ['start_time']) {
                return $topBannerZ;
            } else {
                return $topBannerQ;
            }
        }
        //一条有,一条没有
        if (!$topBannerZ && $topBannerQ) {
            return $topBannerQ;
        } else {
            return $topBannerZ;
        }
    }
}
