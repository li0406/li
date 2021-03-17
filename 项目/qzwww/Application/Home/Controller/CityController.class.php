<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class CityController extends HomeBaseController {
    public function index(){

        //跳转到手机端
        if (ismobile()) {
            header( "HTTP/1.1 301 Moved Permanently");
            header("Location:  ".$this->SCHEME_HOST['scheme_full'] . C('MOBILE_DONAMES') . "/city/");
            exit();
        }

        $cityInfo = S('Cache:Home:SwitchCity');

        if(!$cityInfo){
            //获取热门城市
            $citys = $this->getHotCitys(10);
            $cityInfo["hotCitys"] = $citys;

            //按省份排序
            $allCity = $this->getAllProvinceAndCitys(true);
            //处理省份排序
            $shenpx = array();
            foreach ($allCity as $k => $v) {
                if($v['pname'] == '广西壮族自治区'){
                    $allCity[$k]['pname'] = '广西';
                }
                if($v['pname'] == '内蒙古自治区'){
                    $allCity[$k]['pname'] = '内蒙古';
                }
                if($v['pname'] == '宁夏回族自治区'){
                    $allCity[$k]['pname'] = '宁夏';
                }
                if($v['pname'] == '西藏自治区'){
                    $allCity[$k]['pname'] = '西藏';
                }
                if($v['pname'] == '新疆维吾尔自治区'){
                    $allCity[$k]['pname'] = '新疆';
                }
                // $shenpx[$v['abc']]['abc'] = $v['abc'];
                $shenpx[$v['abc']]['shen'][] = $allCity[$k];
                $shenpx[$v['abc']]['abc'] = $v['abc'];
            }
            $cityInfo["shenpx"] = $shenpx;
            //按首字母排序
            $cityInfo["accordCity"] = $this->getAllProvinceAndCitys();

            //快速查找城市
            foreach ($shenpx as $key => $value) {
                foreach ($value["shen"] as $k => $v) {
                    //按首字母取省份
                    $provinceBySX[] = array(
                        'pid'      => $v['pid'],
                        'pname'    => $v['pname'],
                        'abc'      => $v["abc"]
                    );

                    if ($key == "A" && $k == 0) {
                        $cityInfo['defaultCity'] = $v["child"];
                    }
                }
            }
            $cityInfo["provinceBySX"] = $provinceBySX;
            S("Cache:Home:SwitchCity",$cityInfo,15 * 60);
        }

        //根据 province ID 输出城市列表
        $cityid = intval(I('get.getcity'));
        if(!empty($cityid)){

            foreach ($cityInfo["shenpx"] as $key => $value) {
                foreach ($value["shen"] as $v) {
                    if ($v["pid"] == $cityid) {
                        $child = $v["child"];
                        break;
                    }
                }
            }

            $rt = '';
            foreach ($child as $key => $value) {
                $rt .= '<option value="'.$value["bm"].'">'.$value["cname"].'</option>';
            }
            exit($rt);
        }


        //获取referer连接
        $referer = $_SERVER['HTTP_REFERER'];
        if($referer){
            $refererinfo = parse_url($referer);
            $host = explode('.',$refererinfo['host']);  //space.qizuang.com
            $jump = $host[0];       // space、account

            $this->assign('jump',$jump);
            $this->assign('jumpurl',$referer);
        }else{
            $this->assign('jump','');
        }


        //顶部热门城市列表
        $hotcitytop = D("Common/Quyu")->getHotCityList();
        $this->assign('hotcitytop',$hotcitytop);

        //添加顶部搜索栏信息
        $this->assign('serch_uri','companysearch');
        $this->assign('serch_type','装修公司');
        $this->assign('holdercontent','全国超过十万家装修公司为您免费设计');

        $ip = get_client_ip();
        //dump($cityInfo['accordCity']['A']);
        $this->assign("cityInfo",$cityInfo);
        $this->assign("ip",$ip);
        $this->display();
    }

    /**
     * 获取热门城市 手动指定
     * @return [type] [description]
     */
    /*private function getHotCitys($citys){
        $citys = D("Common/Quyu")->getCityByName($citys);
        if(count($citys) > 0){
            return $citys;
        }
        return null;
    }*/


    /**
     * 获取热门城市
     * @return [type] [description]
     */
    private function getHotCitys($limit = 10){
        $citys = D("Common/Quyu")->getHotCitys($limit);
        if(count($citys) > 0){
            return $citys;
        }
        return null;
    }



    /**
     * 获取所有省份及城市
     * @return [type] [description]
     */
    private function getAllProvinceAndCitys($flag = false){
        $citys = D("Common/Quyu")->getAllProvinceAndCitys($flag);
        return $citys;
    }


    /**
     * 根据坐标获取当前城市
     * @return [type] [description]
     */
    public function getLocation(){
        //给定城市默认值
        $cityInfo = array(
                    "bm"=>"sz",
                    "id"=>"320500",
                    "cname"=>"苏州1",
                    "link"=>$this->SCHEME_HOST['scheme_full'] . "m.".C('QZ_YUMING')."/sz/"
                );
        if($_POST){
            $lat = $_POST["lat"];
            $lan = $_POST["lan"];
            $url = "https://api.map.baidu.com/geocoder?location=$lat,$lan&output=json&key=D61aab638db7b99b7633e73f02f4ff7b";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            $hander = curl_exec($ch);
            if($hander){
                $json = json_decode($hander,true);
                $fix = array("市","区","县");
                if(strtolower($json["status"]) == "ok"){
                   $json["result"]["addressComponent"]["city"] = str_replace($fix, "", $json["result"]["addressComponent"]["city"]);
                   //根据城市名称查询城市信息
                   $city = D("Common/Quyu")->getCityIdByCname($json["result"]["addressComponent"]["city"]);
                   $cityInfo = array(
                            "bm"=> $city["bm"],
                            "id"=>$city["cid"],
                            "cname"=>$city["cname"],
                            "link"=>$this->SCHEME_HOST['scheme_full'] ."m.".C('QZ_YUMING')."/".$city["bm"]."/"
                        );
                }
            }
            curl_close($hander);
            $this->ajaxReturn(array("data"=>$cityInfo,"info"=>"","status"=>1));
        }
        $this->ajaxReturn(array("data"=>"","info"=>"","status"=>0));
    }


    /**
     * 通过城市名称获取bm
     * @return [type] [description]
     */
    public function getbmbycityname(){

        $cname = I('get.city');

        $cacheKey = 'C:www:city:bmbycityname:'.md5($cname);
        $citys    = S($cacheKey);
        if (empty($citys)) {
            $citys = D("Common/Quyu")->getCityByName($cname);
            S($cacheKey, $citys, 60 * 15);
        }

        if(!empty($citys)){
            $this->ajaxReturn(array("data"=>"","info"=>$citys['0']['bm'],"status"=>1));
        }
        $this->ajaxReturn(array("data"=>"","info"=>"","status"=>0));
    }

    /**
     * 通过城市名称获取cid
     * @return [type] [description]
     */
    public function getCidByCname(){
        $cname = I('get.city');
        if(!empty($cname)){

            $cacheKey = 'C:www:city:cidbycname:'.md5($cname);
            $citys    = S($cacheKey);
            if (empty($citys)) {
                $citys = D("Common/Quyu")->getCityIdByCityName($cname);
                S($cacheKey, $citys, 60 * 15);
            }

            if(!empty($citys)){
                cookie('iplookup',$citys['0']['cid'],86400 * 7);
                $this->ajaxReturn(array("data"=>"","info"=>$citys['0']['cid'],"status"=>1));
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"","status"=>0));
    }

    /**
     * 根据IP获取城市定位(可替换 getCidByCname )
     * @return [type] [description]
     */
    public function getCityInfoByIp()
    {
        import('Library.Org.Util.App');
        $app = new \App();
        $ip = $app->get_client_ip();
        if (C('APP_ENV') == 'dev') {
            $ip = '223.112.69.58';
        }
        
        $iptocity = iptocity($ip);
        $cityName = $iptocity[2];

        if (!empty($cityName)) {
            $citys = D("Common/Quyu")->getCityIdByCityName($cityName);
            if(!empty($citys)){
                cookie('iplookup',$citys['0']['cid'],86400 * 7);
                //将城市信息保存到cooke中
                cookie('QZ_CITY',json_encode($citys['0']),array('expire'=>86400 * 7,'domain' => '.'.APP_HTTP_DOMAIN));
                $this->ajaxReturn(array("data"=>$citys['0'],"info"=>"","status"=>1));
            }
        }
        $this->ajaxReturn(array("data"=>"","info"=>"","status"=>0));
    }


    //输入城市JSON文件
    public function getCityJson(){
        header('Content-Type: application/x-javascript; charset=UTF-8');
        //获取数组形式的城市信息
        $citys = D("Common/Quyu")->getCityArray();
        $citys = json_encode($citys);
        $citys = str_replace(array('oldname','cname','key','oldName','qz_areai','qz_area','orders'),
                             array('n','c','k','N','i','a','o'),
                             $citys);
        $content =  "var citys = JSON.parse('".$citys."');";
        echo $content;
        die;
    }

    /**
     * 百度城市定位接口
     */
    public function BaiduLocation()
    {
        $cookieBm = cookie('m_city_area');
        if (!empty($cookieBm)) {
            $cityInfo = D('Common/Quyu')->getCityInfoByBm($cookieBm);
            if (empty($cityInfo)) {
                $cityCacheArray =  ['cid' => '000001', 'cname' => '全国', 'bm' => 'www'];
            }else{
                $cityCacheArray = ['cid' => $cityInfo['cid'], 'cname' => $cityInfo['oldName'], 'bm' => $cityInfo['bm']];
            }
            $this->ajaxReturn(["data" => $cityCacheArray, "info" => '定位成功', "status" => 1]);
        }
        //API控制台申请得到的ak（此处ak值仅供验证参考使用）
        $baiduAk = OP('baidumap_ak_8643138');
        //应用类型为for server, 请求校验方式为sn校验方式时，系统会自动生成sk，可以在应用配置-设置中选择Security Key显示进行查看（此处sk值仅供验证参考使用）
        $baiduSk = OP('baidumap_sk_8643138');
        //地理编码的请求中address参数
        import('Library.Org.Util.App');
        $app = new \App();
        $clientIp = $app->get_client_ip() == '127.0.0.1' ? '122.97.203.223' : $app->get_client_ip();
        if (C('APP_ENV') == 'dev') {
            $clientIp = '223.112.69.58';
        }
        $cacheCity = S("Cache:Quyu:" . $clientIp);
        if (!empty($cacheCity)) {
            $this->ajaxReturn(array("data" => $cacheCity, "info" => '定位成功', "status" => 1));
        }
        //地理编码的请求url，参数待填
        $url = "https://api.map.baidu.com/location/ip?ip=%s&ak=%s&coor=bd09ll&sn=%s";
        //get请求uri前缀
        $uri = '/location/ip';
        //构造请求串数组
        $querystring_arrays = array(
            'ip' => $clientIp,
            'ak' => $baiduAk,
            'coor' => 'bd09ll',
        );
        //调用sn计算函数，默认get请求
        $sn = $this->caculateAKSN($baiduAk, $baiduSk, $uri, $querystring_arrays);
        //请求参数中有中文、特殊字符等需要进行urlencode，确保请求串与sn对应
        $target = sprintf($url, urlencode($clientIp), $baiduAk, $sn);
        //输出完整请求的url（仅供参考验证，故不能正常访问服务）
        $baiduCityInfo = curl($target, [], 1);
        //返回参数为空，定位返回总站
        if (empty($baiduCityInfo)|| $baiduCityInfo['status'] != 0){
            $cityCacheArray = ['bm' => 'www', 'cid' => '000001', 'cname' => '全国'];
        } else {
            $cityName = str_replace('市', '', $baiduCityInfo['content']['address_detail']['city']);
            $cityInfo = D('Common/Quyu')->getCityInfoByName($cityName);
            if (empty($cityInfo)) {
                //分站参数不存在，定位返回总站
                $cityCacheArray = ['bm' => 'www', 'cid' => '000001', 'cname' => '全国'];
            } else {
                //将城市信息保存到cooke中
                cookie('iplookup',$cityInfo['cid'],86400 * 7);
                cookie('QZ_CITY',json_encode($cityInfo),array('expire'=>86400 * 7,'domain' => '.'.APP_HTTP_DOMAIN));
                $cityCacheArray = ['bm' => $cityInfo['bm'], 'cid' => $cityInfo['cid'], 'cname' => $cityInfo['cname']];
                S("Cache:Quyu:" . $clientIp, $cityCacheArray, 24 * 3600);
            }
        }
        $this->ajaxReturn(["data" => $cityCacheArray, "info" => '定位成功', "status" => 1]);
    }

    /**
     * 计算百度的SN
     * @param $ak
     * @param $sk
     * @param $url
     * @param $querystring_arrays
     * @param string $method
     * @return string
     */
    function caculateAKSN($ak, $sk, $url, $querystring_arrays, $method = 'GET')
    {
        if ($method === 'POST'){
            ksort($querystring_arrays);
        }
        $querystring = http_build_query($querystring_arrays);
        return md5(urlencode($url.'?'.$querystring.$sk));
    }


    //跳转
    public function jumpreferer()
    {
        $param = I('get.');
        $cityid = $param['cityid'];
        $jumpurl = $param['jumpurl'];

        //更换城市

        if($_SESSION['u_userInfo']['id']){
            $uuid = $_SESSION['u_userInfo']['id'];
            S('Citycache:CityId:'.$uuid,$cityid,60*24);
            $_SESSION["cityId"] = $cityid;
        }

        header( "HTTP/1.1 301 Moved Permanently");
        header("Location: " . $jumpurl);
        exit();
    }

}