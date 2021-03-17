<?php
namespace Mobile\Common\Controller;
use Common\Model\Logic\JwtTokenLogicModel;
use Common\Model\Logic\UcenterUserLogicModel;
use Library\Qizuang\JWTAuth\Src\Facade\Auth;
use Think\Controller;
class MobileBaseController extends Controller {
    public $cityInfo = null;
    public $SCHEME_HOST;
    public function _initialize(){
        //禁止 qq管家后台扫描
        B("Mobile\Behavior\BrowserScanCheck");

        // 访问来源追踪,来源进行cookie标记
        // 逻辑当有get参数 src的时候,例如 ?src=video
        // 打客户端浏览器cookie标记为  cookie('src_mark', I('get.src'), 3600 * 24); // 指定cookie保存时间为24小时
        B("Common\Behavior\MarkTrackingOrderSourceCheck");
        //如果是包含bm参数的,获取城市信息
        $bm = I("get.bm");

        session("m_cityInfo",null);
        session("m_mapUseInfo",null);

        if (!empty($bm)) {
            $city = S("C:M:CITYINFO:V1:".$bm);
            if ($city === false) {
                $city = getCityInfo($bm);
                if ($city === false){
                    //没有城市信息，跳转404页面
                    $this->_error();
                }
            }

            if (count($city) > 0) {
                S("C:M:CITYINFO:V1:".$bm, $city, 15*60);
            }

        } else {
            //如果不包含BM头
            //1.如果包含ct参数
            if (I("get.ct") !== "") {
                //1. 根据BM获取城市信息
                $bm = I("get.ct");
                if (stripos($bm,'/')){
                    $bm = rtrim($bm,'/');
                }
                $cookie = cookie("m_city_area");
                if ($cookie !== null) {
                    $bm = $cookie;
                }

                $city = S("C:M:CITYINFO:V1:".$bm);
                if ($city === false) {
                    $city = getCityInfo($bm);
                    if ($city === false){
                        //没有城市信息，跳转404页面
                        $city = array();
                    }
                }
            } else {
                //不包含ct参数
                //是否包含分站bm的cookie
                $bm = cookie("m_city_area");
                if ($bm !== null) {
                    $city = getCityInfo($bm);
                }
            }
        }

        if ($city !== false && count($city) > 0) {
            $cityInfo = array(
                    "bm"=>$city["bm"],
                    "name"=>$city["oldName"],
                    "id" =>$city["cid"],
                    'cid' =>$city["cid"],
                    "pid"=>$city['uid'],
                    "adj_city"=>$city["adj_city"],
                    "lng"=>$city["lng"],
                    "lat"=>$city["lat"],
                    "province"=>str_replace("省","",$city["province"]),
                    "provincefull"=>$city["province"]
            );
            $cityArr = D('Quyu')->getAreaByFatherId($cityInfo['id'])[0];
            $cityInfo['cityarea'] = $cityArr['name'];
            $cityInfo['areaid'] = $cityArr['id'];
            $cityInfo['vipcount'] = $city['vipcount'];

            $_SESSION["m_cityInfo"] = $cityInfo;
            $_SESSION["m_mapUseInfo"] = $cityInfo;

            setcookie("m_city_area", $bm, 0, '/', '.'.APP_HTTP_DOMAIN);
            setcookie("cityId_for_coldt",$cityInfo['id'],0,'/', '.'.APP_HTTP_DOMAIN);
        }

        $city =  S("C:M:CURRENT:CITY:".$bm);
        if (!$city) {
            if (!empty($bm)) {
                $result = D("Common/Quyu")->getCityInfoByBm($bm);
                $city = array(
                    "bm"=>$result["bm"],
                    "name"=>$result["oldName"],
                    "id" =>$result["cid"],
                    'cid' =>$result["cid"],
                    "pid"=>$result['uid'],
                    "adj_city"=>$result["adj_city"],
                    "lng"=>$result["lng"],
                    "lat"=>$result["lat"],
                    "province"=>str_replace("省","",$result["province"]),
                    "provincefull"=>$result["province"]
                );
                S("C:M:CURRENT:CITY:".$bm,$city,15*60);
            }
        }

        $this->cityInfo = $city;
        $this->assign("cityInfo",$city);
        $this->assign("mapUseInfo",$cityInfo);
        //获取该城市第一个区，用于显示默认城市
        $defaultCityarea = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("defaultCityarea",$defaultCityarea);
        $showlocation = 2;
        $this->assign('showlocation',$showlocation);//是否显示location标签，2表示不显示，1表示显示

        //报价页弹窗页面
        if(cookie("w_index") == null){
            if (I("get.bm")  == "") {
                $uri =  parse_url($_SERVER["REQUEST_URI"]);
                $explode = array_filter(explode("/",$uri["path"]));
                $path = strtolower($explode[1]);
                $lc = $explode[2];
            }

            //产品需求变动取消网站内的发单 暂时注释发单代码
            if (!empty($path) && $path!='baike') {

                $arr = array("baike","riji","wenda");
                if(in_array($path,$arr) || (I("get.bm") !== "" && empty($lc))){
//                   $this->assign("showTmp",1);
                }

                if($path == 'xgt' || ($path == "gonglue" && $lc == 'lc')){
//                    $this->assign("showTmp",2);
                }

                if ($path == "meitu") {
                    $result = $this->getShowWindowTmp();
                    if ($result) {
//                        $this->assign("showTmp",2);
                    }
                }
            } else {
                $result = $this->getShowWindowTmp();
                if ($result) {
//                    $this->assign("showTmp",1);
                }
            }
        }

        //URL路径
        $geturl  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $geturl =  explode('/', $geturl)[1];
        $this->assign('getcommon_url',$geturl);
        $this->assign('uri_formate',parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

        // 站点多域名访问支持 动态化httphost
        $this->SCHEME_HOST = getSchemeAndHost();
        /** 'isSsl' => boolean false
            * 'scheme' => string 'http'
            * 'scheme_full' => string 'http://'
            * 'host' => string 'm.qizuang.com'
            * 'domain' => string 'qizuang.com'
            * 'scheme_host' => string 'http://m.qizuang.com'
         */
        $this->assign('SCHEME_HOST',$this->SCHEME_HOST);

        // 站点多域名访问显示控制
        // qizuang域名给个标识为1，非qizuang域名访问模板判断中会用到
        if (C("QZ_YUMING") == $this->SCHEME_HOST['domain']) {
            $FLAG_DOMAIN_QIZUANG = 1;
        } else {
            $FLAG_DOMAIN_QIZUANG = 0;
        }
        $this->assign("FLAG_DOMAIN_QIZUANG",$FLAG_DOMAIN_QIZUANG);

        // 站点多域名模式下底部信息控制开关
        if (C("QZ_YUMING") == $this->SCHEME_HOST['domain']) {
            $ShowControlN1 = 'on';
        } else {
            $ShowControlN1 = 'off';
        }
        $this->assign("ShowControlN1",$ShowControlN1);

        // 全站ICON导航开关，默认开 1。 在一些特定页面如落地页需要关闭 0
        $FLAG_NAV_ICON = 1;
        $this->assign("FLAG_NAV_ICON",$FLAG_NAV_ICON);

        // 程序运行环境dev prod
        $this->assign('APP_ENV',getAppEnv());

        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        //$robotIsTrue = true; //debug 警告线上应该为注释状态
        if (true === $robotIsTrue) {
            $this->assign('robot',1);
        }
        $this->assign('static_host',"");
        $this->assign('title',"齐装网");
        $this->assign('cityfile','//'.OP('QINIU_DOMAIN').'/common/js/'.OP('ALL_CITY_JSON'));
        //todo 识别用户信息
        if(empty($_SESSION['u_userInfo'])){
            $token = $_COOKIE['center_password_token'];
            if(empty($token)){
                session("u_userInfo",null);
            }else{
                //解析token
                $info = Auth::check($token);
                if (time() < $info['exp']) {
                    //设置session用户基本信息
                    $ucLogic = new UcenterUserLogicModel();
                    $ucLogic->setUserInfo($info['uuid']);
                }else{
                    session("u_userInfo",null);
                }
            }
        }
    }

     //空操作
    public function _empty() {
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        $this->display('Public:404');
        die();
    }

    /**
     * [_error description]
     * @return [type] [description]
     */
    public function _error(){
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        $this->display('Public:404');
        die();
    }

    /**
     * 获取标识是否打开弹窗
     * @return [type] [description]
     */
    private function getShowWindowTmp()
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
}
