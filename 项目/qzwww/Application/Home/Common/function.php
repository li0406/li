<?php
/*Home的通用方法*/

//高亮搜索关键词
function highlightWords($str,$keywords,$color = "#DE4348") {
    if (empty($keywords)) {
        return $str;
    }
    $keywords = preg_split("/[ \t\r\n,]+/", $keywords);
    foreach($keywords as $val) {
        $tvar = preg_match('/'.$val.'/', $str, $regs);
        $finalrep    = '<font style="color:'.$color.';font-weight:bold">' . $regs[0] . '</font>';
    }
    $str = str_ireplace($regs[0], $finalrep, $str);
    return $str;
}

/**
 * 评分百分比
 *
 * @param      integer  $val    The value
 *
 * @return     integer 评分百分比
 */
function commentPercent($val){
    if(!is_numeric($val)){
        return 0;
    }
    return round($val * 10, 2);
}

if (!function_exists('curl')){

    /**
     * curl请求函数封装
     * @param $curllink
     * @param null $data
     * @param int $type
     * @return mixed
     */
    function curl($curllink, $data = null, $type = 0)
    {
        //初始化一个curl对象
        $ch = curl_init($curllink);
        //设置需要抓取的url
        curl_setopt($ch, CURLOPT_URL, $curllink);
        //跳过SSL证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //连接超时10秒
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        //超时设置10秒
        curl_setopt($ch, CURLOPT_TIMEOUT,10);
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定是否显示头信息
        $output = curl_exec($ch);//执行
        curl_close($ch);//关闭
        if ($type == 0) {
            $jsoninfo = json_decode($output);//解析json格式为对象
        } else {
            $jsoninfo = json_decode($output, true);//解析json格式为数组
        }

        return $jsoninfo;
    }
}

if (!function_exists('qccFormatTime')) {
    function qccFormatTime($time)
    {
        if (empty($time) || strtotime($time) == false) {
            return '-';
        }
        return date('Y-m-d', strtotime($time));
    }
}