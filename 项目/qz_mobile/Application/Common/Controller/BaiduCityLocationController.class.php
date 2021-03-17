<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2018/8/14
 * Time: 9:00
 */

namespace Common\Controller;
use Think\Controller;
class BaiduCityLocationController extends Controller
{
    public function getLocationByBaidu()
    {
        //1.0判断cookiej是存在
        //2.0不存在，先判断IP缓存是否存在
        //3.0不存在,调用百度IP定位城市，根据城市名称查询城市信息
        //如果cookie中存在bm头
        $cookieBm = cookie('m_city_area');
        if(!empty($cookieBm)){
           $cityInfo = D('Common/Quyu') -> getCityInfoByBm($cookieBm);
           if(empty($cityInfo)){
               return ['cid' => '000001', 'cname' => '总站', 'bm' => 'www'];
           }
           $cityInfo = ['cid' => $cityInfo['cid'], 'cname' => $cityInfo['oldName'], 'bm' => $cityInfo['bm']];
           return $cityInfo;
        }
        //如果cookie和缓存中都不存在则使用IP定位
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
        $location = get_curl($locationUrl);
        $cityName = str_replace('市', '', $location['content']['address_detail']['city']);
        $cityInfo = D('Common/Quyu') -> getCityInfoByName($cityName);
        if(empty($cityInfo)){
            return ['cid' => '000001', 'cname' => '总站', 'bm' => 'www'];
        }
        $cityCacheArray = ['bm' => $cityInfo['bm'], 'cid' => $cityInfo['cid'], 'cname' => $cityInfo['cname']];
        S("Cache:Quyu:".$ip, $cityCacheArray, 24 * 3600);
        return $cityCacheArray;
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
}