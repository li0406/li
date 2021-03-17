<?php

namespace Mobile\Controller;

use Common\Model\FenggeModel;
use Library\Qizuang\JWTAuth\Src\Facade\Auth;
use Mobile\Common\Controller\MobileBaseController;

class LuodiyeController extends MobileBaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }


    /**
     * 渠道落地页
     */
    public function qdbj1(){
        $basic["head"]["title"] = "0元抢装修设计方案 - 免费获取装修报价 - 齐装网";
        $basic["head"]["keywords"] = "装修设计方案，装修报价";
        $basic["head"]["description"] = "想准确知道装修预算，快来抢免费装修设计方案吧，装修提前准备，弄清预算再开搞。业主可获得装修公司免费上门量房，免费设计报价，更可尊享齐装网六大装修保障，让您轻松装修乐无忧";
        $this->assign("basic", $basic);

        $this->display();
    }


    /**
     * 新报价落地页
     */
    public function zxbjjs(){
        $model = new FenggeModel();
        $fengge = $model->getfg();
        $fenggelist = ['中式风格','现代简约','欧式风格','古典风格','田园风格','混搭风格'];
        $count = count($fengge);
        $showlist = [];
        $i = 0;
        foreach ($fenggelist as $key => $val){
            $k = 0;
            while($k < $count){
                if($fengge[$k]['name'] == $val){
                    $showlist[$i] = $fengge[$k];
                    $i++;
                    break;
                }else{
                    $k++;
                }
            }
        }
        $basic["head"]["title"] = "8秒计算装修报价 - 装修价格预算 - 齐装网";
        $basic["head"]["keywords"] = "装修报价，装修价格";
        $basic["head"]["description"] = "装修要花多少钱，8秒计算装修报价，弄清预算再开搞，装修省钱又省心。齐装网大数据帮您智能评估装修报价，各项装修费用一目了然，让您轻松装修乐无忧。";
        $this->assign("basic", $basic);


        $this->assign('fenggelist',$showlist ? $showlist : []);
        $this->display();
    }


    /**
     * 新增建行装修贷款落地页
     */
    public function jh_zxdk(){

        $this->display("jh-zxdk");
    }

    /**
     * 发单成功页面
     */
    public function zxbjjs_success()
    {
//        $orderid = I("get.orderno");
        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);
            if (count($order) > 0) {
                $result = $this->calculatePrice($order["mianji"], $order["huxing"]);
                $str = (string)$result['total'] ? $result['total'] : 0;  // 将int型转换成string
                $result['total2'] = str_split($str);  // 将字符串拆分成array
                $basic["head"]["title"] = $order["cname"] . "_" . $order["fengge"] . "_" . $order["hxname"] . "装修报价明细-齐装网";
                $this->assign("basic", $basic);
                $this->assign("order", $order);
                $this->assign("info", $result);
            }
            $this->display();
            die;
        }
        header("LOCATION:".$this->SCHEME_HOST['scheme_host']."/baojia4/");
    }

    /**
     * 计算价格
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
        $price = round( ($result["half_price_min"]+$result["half_price_max"])/2);
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
        $info['total'] = $total ? $total : 0;

        return $info;
    }
    public function bao()
    {
        $nowtime = time();
        /**
         * start_time: 开始时间，当前时间小于该时间将无法操作
         * expiration_time: 过期时间，当前时间大于该时间将无法操作
         */
        $info["start_time"] = $nowtime + 5;
        $info["expiration_time"] = $nowtime + 300;
        $info["token_type"] = "apifb";
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        $token = Auth::getToken($info);     // 生成jwt

        $this->assign("token", $token);
        $this->assign("info", $info);
        $this->display();
    }

    /**
     * 844 综合发单页
     */
    public function jisuanqi(){
        $cityInfo = $this->cityInfo;
        $bm = $cityInfo['bm']; // 当前城市bm
        $jumpCityArr = C('ZXDK_CITY_BM_LIST'); // 允许跳转的城市bm
        $jumpUrl = ""; // 贷款的跳转地址
        if (in_array($bm, $jumpCityArr)) {
            $jumpUrl =  C('ZXDK_LANDING_PAGE');
        }

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        $this->assign('info', $info);
        $this->assign('jumpUrl', $jumpUrl);
        $this->display();
    }

    public function jisuanqi_success(){
        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);

            if (count($order) > 0) {
                $result = $this->calculatePrice($order["mianji"], $order["huxing"]);
                $this->assign("order", $order);
                $this->assign("info", $result);
                $this->display();
                die();
            }
        }
        header("location:" ."/jisuanqi");
    }

    public function jisuanqi_result(){
        $this->display();
    }

    /**
     * 949 复制滑动落地页，按照新设计稿制作
     */
    public function zxbj(){
        $cityInfo = $this->cityInfo;
//        if(empty($cityInfo)){
//            //ip定位
//            $cityInfo = $this->getIpLocation();
//        }
        $bm = $cityInfo['bm']; // 当前城市bm
        $jumpCityArr = C('ZXDK_CITY_BM_LIST'); // 允许跳转的城市bm
        $jumpUrl = ""; // 贷款的跳转地址
        if (in_array($bm, $jumpCityArr)) {
            $jumpUrl =  C('ZXDK_LANDING_PAGE');
        }

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        $this->assign('info', $info);
        $this->assign('jumpUrl', $jumpUrl);
        $this->display();
    }

    // 报价成功
    public function zxbj_result(){
        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);

            if (count($order) > 0) {
                $result = $this->calculatePrice($order["mianji"], $order["huxing"]);
                $this->assign("order", $order);
                $this->assign("info", $result);
                $this->display();
                die();
            }
        }
        $referer = $_SERVER['HTTP_REFERER'];
        if (!empty($referer)) {
            header("location:" . $referer);
        } else {
            header("location:" ."/qzw/zxbj");
        }
    }

    /**
     * 955 制作0元户型设计落地页
     */
    public function xhxsj(){
        $cityInfo = $this->cityInfo;
//        if(empty($cityInfo)){
//            //ip定位
//            $cityInfo = $this->getIpLocation();
//        }
        $bm = $cityInfo['bm']; // 当前城市bm
        $jumpCityArr = C('ZXDK_CITY_BM_LIST'); // 允许跳转的城市bm
        $jumpUrl = ""; // 贷款的跳转地址
        if (in_array($bm, $jumpCityArr)) {
            $jumpUrl =  C('ZXDK_LANDING_PAGE');
        }

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        $this->assign('info', $info);
        $this->assign('jumpUrl', $jumpUrl);
        $this->display();
    }

    public function xhxsj_result(){
        $this->display();
    }
    public function zxfw(){
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        $this->assign('info', $info);
        $this->display();
    }

    public function getIpLocation(){
        //如果都不存在则使用IP定位
        import('Library.Org.Util.App');
        $app = new \App();
        $ip = $app->get_client_ip();
        if (C('APP_ENV') == 'dev') {
            $ip = '223.112.69.58';
        }
        $cacheCity = S("Cache:Quyu:".$ip);
        if(!empty($cacheCity)) {
            return $cacheCity;
        }
        //请求百度地址获取位置信息
        $sn = $this -> getLocationAKSN($ip);
        $locationUrl = "http://api.map.baidu.com/location/ip?ip={$ip}&ak=".OP('baidumap_ak_8643138')."&coor=bd09ll&sn={$sn}";
        //请求百度接口
        $location = curl($locationUrl);
        $cityName = str_replace('市', '', $location['content']['address_detail']['city']);
        if(!empty($cityName)){
            $cityInfo = D('Common/Quyu') -> getCityInfoByName($cityName);
            //区域
            $cityArr = D('Quyu')->getAreaByFatherId($cityInfo['cid'])[0];
            $cityInfo['cityarea'] = $cityArr['name'];
            $cityInfo['areaid'] = $cityArr['id'];

            //兼容页面参数
            $mapUseInfo = [
                'pid' => $cityInfo['uid'],
                'id' => $cityInfo['cid'],
                'areaid' => $cityInfo['areaid'],
                'provincefull' => $cityInfo['province'],
                'name' => $cityInfo['cname'],
                'cityarea' => $cityInfo['cityarea'],
            ];
            $this->ajaxReturn(['error_code'=>0,'info'=>$mapUseInfo]);
        }
        $this->ajaxReturn(['error_code'=>400,'error_msg'=>'获取失败']);
    }

    //获取SN校验的链接
    private function getLocationAKSN($ip)
    {
        //API控制台申请得到的ak（此处ak值仅供验证参考使用）
        $ak = OP('baidumap_ak_8643138');
        //应用类型为for server, 请求校验方式为sn校验方式时，系统会自动生成sk，可以在应用配置-设置中选择Security Key显示进行查看（此处sk值仅供验证参考使用）
        $sk = OP('baidumap_sk_8643138');
        //以Geocoding服务为例，地理编码的请求url，参数待填
        //get请求uri前缀
        $uri = '/location/ip';
        //地理编码的请求中address参数
        $address = $ip;
        //地理编码的请求output参数
        $coor = 'bd09ll';
        //构造请求串数组
        $querystring_arrays = array (
            'ip' => $address,
            'ak' => $ak,
            'coor' => $coor,
        );
        //调用sn计算函数，默认get请求
        $sn = caculateAKSN($ak, $sk, $uri, $querystring_arrays);
        return $sn;
    }

    // 1038 复制4个报价落地页并做相应调整
    public function xbaojia(){
        $SCHEME_HOST = $this->SCHEME_HOST;
        session("m_redirect", $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'].'/xdetails/');
        session("m_wanshan_tmp",'wanshan');
        //传入source，没有传入则默认为311(即本页)
        $source = $_GET['fi'];
        if(empty($source)){
            $source = 311;
        }
        $this->assign('source',$source);

        //seo 标题/描述/关键字
        $basic["head"]["title"] = "装修报价";
        $basic["head"]["keywords"] = "装修公司,装修网,齐装网";
        $basic["head"]["description"] = "齐装网是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign('cityInfo',session('m_cityInfo'));

        $city = session('m_cityInfo');
        if(empty($city['name'])){
            $city['name'] = '';
        }
        //获取src
        $src = $_GET['src'];

        $info['city'] = $city['name'];
        $this->assign("logoCctvDisable", 1); // 禁用cctv logo
        $this->assign("src", $src);
        $this->assign('info',$info);
        $this->assign("basic",$basic);
        $this->display();
    }

    /**
     * 装修报价详细页面
     * @return [type] [description]
     */
    public function xdetails () {
        $SCHEME_HOST = $this->SCHEME_HOST;
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
        if(!empty($result['img'])){
            $this->assign('weixin_img',$result['img']);
        }

        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);
            if(count($order) > 0){
                $result = $this->calculatePrice($order["mianji"],$order["cs"]);

                $basic["head"]["title"] = $order["cname"]."_".$order["fengge"]."_".$order["hxname"]."装修报价明细-齐装网";

                $this->assign("basic",$basic);
                $this->assign("order",$order);
                $this->assign("info",$result);
                $this->assign("tags",$location["tags"]);
                $this->display();
                die();
            }
        }
        $referer = $_SERVER['HTTP_REFERER'];
        if (!empty($referer)) {
            header("location:" . $referer);
        } else {
            header("LOCATION: ".$SCHEME_HOST['scheme_full'].$SCHEME_HOST['host']."/qzw/xbaojia/");
        }
        // header("LOCATION: ".$SCHEME_HOST['scheme_full'].$SCHEME_HOST['host']."/qzw/xbaojia/");
    }

    public function xbaojia1(){
        $cityInfo = $this->cityInfo;
        $bm = $cityInfo['bm']; // 当前城市bm

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        $this->assign('info', $info);
        $this->display();
    }

    public function xbaojia2(){
        $cityInfo = $this->cityInfo;
        $bm = $cityInfo['bm']; // 当前城市bm

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        $this->assign('info', $info);
        $this->display();
    }

    public function xbaojia3(){
        $cityInfo = $this->cityInfo;
        $cityInfo = $this->cityInfo;
        $bm = $cityInfo['bm']; // 当前城市bm

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        $this->assign('info', $info);
        $this->display();
    }

    // 1047 制作2个装小七发单落地页
    public function zxbaojia1(){
        $SCHEME_HOST = $this->SCHEME_HOST;
        session("m_redirect", $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'].'/zxdetails/');
        session("m_wanshan_tmp",'wanshan');
        //传入source，没有传入则默认为311(即本页)
        $source = $_GET['fi'];
        if(empty($source)){
            $source = 311;
        }
        $this->assign('source',$source);

        //seo 标题/描述/关键字
        $basic["head"]["title"] = "装修报价";
        $basic["head"]["keywords"] = "装修公司,装修网,装小七";
        $basic["head"]["description"] = "装小七是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign('cityInfo',session('m_cityInfo'));

        $city = session('m_cityInfo');
        if(empty($city['name'])){
            $city['name'] = '';
        }
        //获取src
        $src = $_GET['src'];

        $info['city'] = $city['name'];
        $this->assign("logoCctvDisable", 1); // 禁用cctv logo
        $this->assign("src", $src);
        $this->assign('info',$info);
        $this->assign("basic",$basic);
        $this->display();
    }
    public function zxdetails () {
        $SCHEME_HOST = $this->SCHEME_HOST;
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
        if(!empty($result['img'])){
            $this->assign('weixin_img',$result['img']);
        }

        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);
            if(count($order) > 0){
                $result = $this->calculatePrice($order["mianji"],$order["cs"]);

                $basic["head"]["title"] = $order["cname"]."_".$order["fengge"]."_".$order["hxname"]."装修报价明细-齐装网";

                $this->assign("basic",$basic);
                $this->assign("order",$order);
                $this->assign("info",$result);
                $this->assign("tags",$location["tags"]);
                $this->display();
                die();
            }
        }
        $referer = $_SERVER['HTTP_REFERER'];
        if (!empty($referer)) {
            header("location:" . $referer);
        } else {
            header("LOCATION: ".$SCHEME_HOST['scheme_full'].$SCHEME_HOST['host']."/qzw/zxbaojia1/");
        }
    }
    public function zxbaojia2(){
        $cityInfo = $this->cityInfo;
        $bm = $cityInfo['bm']; // 当前城市bm

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        $this->assign('info', $info);
        $this->display();
    }
    // 报价成功
    public function xzxbj_result(){
        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);

            if (count($order) > 0) {
                $result = $this->calculatePrice($order["mianji"], $order["huxing"]);
                $this->assign("order", $order);
                $this->assign("info", $result);
                $this->display();
                die();
            }
        }
        $referer = $_SERVER['HTTP_REFERER'];
        if (!empty($referer)) {
            header("location:" . $referer);
        } else {
            header("location:" ."/qzw/zxbaojia2");
        }
    }
    // 1056 齐装和装小七报价落地页优化
    public function zxbaojia3(){
        $cityInfo = $this->cityInfo;
        $bm = $cityInfo['bm']; // 当前城市bm

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        $this->assign('info', $info);
        $this->display();
    }
    // 1066 做一个渠道推广链接
    public function sheji_xy(){
        //传入source，没有传入则默认为311(即本页)
        $source = $_GET['fi'];
        if(empty($source)){
            $source = 311;
        }
        $this->assign('source',$source);

        //seo 标题/描述/关键字
        $basic["head"]["title"] = "户型设计";
        $basic["head"]["keywords"] = "装修公司,装修网,齐装网";
        $basic["head"]["description"] = "齐装网是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign('cityInfo',session('m_cityInfo'));
        $city = session('m_cityInfo');
        if(empty($city['name'])){
            $city['name'] = '';
        }
        //获取src
        $src = $_GET['src'];

        $info['city'] = $city['name'];
        $this->assign("src", $src);
        $this->assign('info',$info);
        $this->assign("basic",$basic);
        $this->display();
    }
    // 1092 复制一套设计发单页，修改顶部广告图
    public function sheji_xy1(){
        //传入source，没有传入则默认为311(即本页)
        $source = $_GET['fi'];
        if(empty($source)){
            $source = 311;
        }
        $this->assign('source',$source);

        $this->assign("FLAG_NAV_ICON", 0);

        //seo 标题/描述/关键字
        $basic["head"]["title"] = "户型设计";
        $basic["head"]["keywords"] = "装修公司,装修网,齐装网";
        $basic["head"]["description"] = "齐装网是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign('cityInfo',session('m_cityInfo'));
        $city = session('m_cityInfo');
        if(empty($city['name'])){
            $city['name'] = '';
        }
        //获取src
        $src = $_GET['src'];

        $info['city'] = $city['name'];
        $this->assign("src", $src);
        $this->assign('info',$info);
        $this->assign("basic",$basic);
        $this->display();
    }

    // 1126 复制2个齐装落地页，在taozuang项目上使用
    public function xbaojia5(){
        $SCHEME_HOST = $this->SCHEME_HOST;
        session("m_redirect", $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'].'/zx-details/');
        session("m_wanshan_tmp",'wanshan');
        //传入source，没有传入则默认为311(即本页)
        $source = $_GET['fi'];
        if(empty($source)){
            $source = 311;
        }
        $this->assign('source',$source);

        //seo 标题/描述/关键字
        $basic["head"]["title"] = "装修报价";
        $basic["head"]["keywords"] = "装修公司,装修网,齐装装修";
        $basic["head"]["description"] = "齐装装修是中国家居装修装饰门户网站，汇集了全国性价比较高的家居装修装饰公司，为您提供专业的装修服务以及全新的装修设计效果图、案例和装修知识；专业服务、品质保障，让您的装修更安心！";

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign('cityInfo',session('m_cityInfo'));

        $city = session('m_cityInfo');
        if(empty($city['name'])){
            $city['name'] = '';
        }
        //获取src
        $src = $_GET['src'];

        $info['city'] = $city['name'];
        $this->assign("logoCctvDisable", 1); // 禁用cctv logo
        $this->assign("src", $src);
        $this->assign('info',$info);
        $this->assign("basic",$basic);
        $this->display();
    }

    // 1126 复制2个齐装落地页，在taozuang项目上使用
    public function xbaojia6(){
        $cityInfo = $this->cityInfo;
        $bm = $cityInfo['bm']; // 当前城市bm

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        $this->assign('info', $info);
        $this->display();
    }

    // 1126 复制2个齐装落地页，在taozuang项目上使用   xbaojia5结果页
    public function zx_details () {
        $SCHEME_HOST = $this->SCHEME_HOST;
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
        if(!empty($result['img'])){
            $this->assign('weixin_img',$result['img']);
        }

        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);
            if(count($order) > 0){
                $result = $this->calculatePrice($order["mianji"],$order["cs"]);

                $basic["head"]["title"] = $order["cname"]."_".$order["fengge"]."_".$order["hxname"]."装修报价明细-齐装装修";

                $this->assign("basic",$basic);
                $this->assign("order",$order);
                $this->assign("info",$result);
                $this->assign("tags",$location["tags"]);
                $this->display();
                die();
            }
        }
        $referer = $_SERVER['HTTP_REFERER'];
        if (!empty($referer)) {
            header("location:" . $referer);
        } else {
            header("LOCATION: ".$SCHEME_HOST['scheme_full'].$SCHEME_HOST['host']."/qzw/xbaojia5/");
        }
    }

    // 1126 复制2个齐装落地页，在taozuang项目上使用   xbaojia6结果页
    public function zx_details1(){
        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);

            if (count($order) > 0) {
                $result = $this->calculatePrice($order["mianji"], $order["huxing"]);
                $this->assign("order", $order);
                $this->assign("info", $result);
                $this->display();
                die();
            }
        }
        $referer = $_SERVER['HTTP_REFERER'];
        if (!empty($referer)) {
            header("location:" . $referer);
        } else {
            header("location:" ."/qzw/xbaojia6");
        }
    }

    // 1103 制作齐装网报价落地页
    public function xbaojia4(){
        $cityInfo = $this->cityInfo;
        $bm = $cityInfo['bm']; // 当前城市bm

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        $this->assign('info', $info);
        $this->display();
    }
    // 1224 根据小程序“智能报价”页面制作h5报价落地页
    public function baojiabtl(){
        $this->display();
    }

    // 1246 API对接需求
    public function sheji_szxwt()
    {
        $SCHEME_HOST = $this->SCHEME_HOST;
        session("m_redirect", $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'].'/shejidone/');

        //传入source，没有传入则默认为302(即本页)
        $laiyuan = $_GET['fi'];
        if(empty($laiyuan)){
            $source['top'] = 302;
            $source['bottom'] = 301;
        }else{
            $source['top'] = $laiyuan;
            $source['bottom'] = $laiyuan;
        }
        $this->assign('source',$source);

        //SEO标题关键字描述
        $basic["head"]["title"] = "户型设计_装修招标_免费装修设计与报价-齐装网";
        $basic["head"]["keywords"] = "装修设计,户型设计,室内装修设计,装修报价,装修报价单";
        $basic["head"]["description"] = "齐装网是国内领先的专业的家装、公装项目招标平台,业主可以在齐装网免费发布装修招标,提供装修招标、免费装修设计与报价,免费为业主提供4份室内装修设计方案与报价,并免费获得多套装修设计与报价方案,让您装修省钱省力更省心！";

        //获取src
        $src = $_GET['src'];

        // 获取cy_reques_access_token
        $cy_reques_access_token = $_GET['cy_reques_access_token'];

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        $this->assign("src", $src);
        $this->assign("cy_reques_access_token", $cy_reques_access_token);
        $this->assign('cityInfo',session('m_cityInfo'));
        $this->assign('info',$info);
        $this->assign('basic',$basic);
        //加载招标页面设计方案模版
        $this->display();
    }

    // 1303 落地页设计需求
    public function baojiawqz(){
        $cityInfo = $this->cityInfo;
        $bm = $cityInfo['bm']; // 当前城市bm

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        $this->assign('info', $info);
        $this->display();
    }

    // 1303 落地页设计需求
    public function baojiawqz_result(){
        if (isset($_COOKIE["w_qizuang_n"])) {
            $orderid = cookie("w_qizuang_n");
            $order = D("Orders")->getOrderInfoById($orderid);

            if (count($order) > 0) {
                $result = $this->calculatePrice($order["mianji"], $order["huxing"]);
                $this->assign("order", $order);
                $this->assign("info", $result);
                $this->display();
                die();
            }
        }
        $referer = $_SERVER['HTTP_REFERER'];
        if (!empty($referer)) {
            header("location:" . $referer);
        } else {
            header("location:" ."/qzw/baojiawqz");
        }
    }
}
