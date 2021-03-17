<?php

namespace Home\Model\Service;
/**
 * 百度服务接口
 */
class BaiduServiceModel
{
    /**
     * 根据地址获取坐标
     * @param $address
     * @return array
     */
    public function getCoordinatesByAddress($address)
    {
        if (count($address) == 0) {
            return [];
        }
        //API控制台申请得到的ak
        $ak = C("BAIDU_SERVER_AK");
        //应用类型为for server, 请求校验方式为sn校验方式时，系统会自动生成sk，可以在应用配置-设置中选择Security Key显示进行查看
        $sk = C("BAIDU_SERVER_SK");

        //地理编码的请求url，参数待填
        $url = "http://api.map.baidu.com/geocoder/v2/?address=%s&output=%s&ak=%s&sn=%s";

        //get请求uri前缀
        $uri = '/geocoder/v2/';

        //地理编码的请求output参数
        $output = 'json';

        $urls = [];
        foreach ($address as $k => $v) {
            //构造请求串数组
            $querystring_arrays = array (
                'address' => $v['address'],
                'output' => $output,
                'ak' => $ak
            );
            //调用sn计算函数，默认get请求
            $sn = $this->caculateAKSN($ak, $sk, $uri, $querystring_arrays);
            //请求参数中有中文、特殊字符等需要进行urlencode，确保请求串与sn对应
            $urls[$k] = sprintf($url, urlencode($v['address']), $output, $ak, $sn);
        }
        $data = curl_get_batch($urls);
        $responses = [];
        if (count($data['responses']) == 0 || count($data['param']) == 0) {
            return [];
        }
        $returnData = [];
        foreach ($data['responses'] as $k => $v) {
            $responses[$k] = json_decode($v, true);
        }
        foreach ($data['param'] as $k => $v) {
            $returnData[$v] = isset($responses[$k]) ? $responses[$k] : [];
        }
        return $returnData;
    }

    public function caculateAKSN($ak, $sk, $url, $querystring_arrays, $method = 'GET')
    {
        if ($method === 'POST'){
            ksort($querystring_arrays);
        }
        $querystring = http_build_query($querystring_arrays);
        return md5(urlencode($url.'?'.$querystring.$sk));
    }
}