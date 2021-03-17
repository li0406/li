<?php

// 统一返回格式
function sys_rt($msg = '', $status = 2, $data = null, $errorcode = 0){
    return [
        'msg' => $msg,
        'data' => $data,
        'status' => $status,
        'errorcode' => $errorcode
    ];
}

function sys_page_format($page, $default = 1){
    return intval($page) <= 0 ? $default : intval($page);
}

// 除法
if(!function_exists('sys_devision')) {
    function sys_devision($numerator, $denominator, $decimal = 2) {
        $ret = 0;
        if ($numerator != 0 && $denominator != 0) {
            $ret = round($numerator / $denominator, $decimal);
        }

        return $ret;
    }
}


// 获取两个日期之间的天数
function getTwoDateDays($date1, $date2){
  $time1 = strtotime(date("Y-m-d", strtotime($date1)));
  $time2 = strtotime(date("Y-m-d", strtotime($date2)));

  $diff_time = abs($time2 - $time1);
  $days = ($diff_time / 86400) + 1;
  return $days;
}

/**
 * [获取完整访问地址 description]
 * @return [type] [description]
 */
function getViewUrl() {
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
    return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
}

//取用户信息
function getAdminUser($val = '')
{
    if (!empty($val)) {
        return session("uc_userinfo." . $val);
    }
    return session("uc_userinfo");
}

/**
 * 加密解密函数
 * @return [type] [description]
 */
function authcode($str,$operation="DECODE",$key = ""){
     import('Library.Org.Util.Authcode');
     $auth = new \Authcode();
     $code = $auth->DEcode($str,strtoupper($operation),$key);
     return $code;
}

/**
 * 中文字符串截取
 * @param  [type]  $str     [description]
 * @param  integer $start   [description]
 * @param  [type]  $length  [description]
 * @param  string  $charset [description]
 * @param  boolean $suffix  [description]
 * @return [type]           [description]
 */
function mbstr($str, $start=0, $length, $charset="utf-8", $suffix=true,$fix="") {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }

    if(strlen($slice) < strlen($str)){
        if(empty($fix)){
            $fix='...';
        }
    }
    return $suffix ? $slice.$fix : $slice;
}

 /**
  * 替换字符串
  * @param  [type] $str       [description]
  * @param  string $placement [替换字符串位置 左 中 右]
  * @param  [type] $length    [替换字符串的长度]
  * @param  [type] $start     [开始的位置]
  * @param  [type] $end       [结束的位置]
  * @return [type]            [description]
  */
function mbreplace($str,$placement ="left",$start,$end,$length,$placeholder="*"){
    for ($i=0; $i < $length; $i++) {
        $mark .= $placeholder;
    }
    switch ($placement) {
        case 'left':
            $str = substr_replace($str,$mark,0,$end);
            break;
        case 'middle':
            $str = substr_replace($str,$mark,$start,$end);
            break;
        case 'right':
            $str = substr_replace($str,$mark,"-".$length);
            break;
    }
    return $str;
}

/**
 * 获取URl的域名
 * @param  string $url url链接
 * @return
 */
function get_domain($url = '') {
    $pattern = "/[/w-]+/.(com|net|org|gov|biz|com.tw|com.hk|com.ru|net.tw|net.hk|net.ru|info|cn|com.cn|net.cn|org.cn|gov.cn|mobi|name|sh|ac|la|travel|tm|us|cc|tv|jobs|asia|hn|lc|hk|bz|com.hk|ws|tel|io|tw|ac.cn|bj.cn|sh.cn|tj.cn|cq.cn|he.cn|sx.cn|nm.cn|ln.cn|jl.cn|hl.cn|js.cn|zj.cn|ah.cn|fj.cn|jx.cn|sd.cn|ha.cn|hb.cn|hn.cn|gd.cn|gx.cn|hi.cn|sc.cn|gz.cn|yn.cn|xz.cn|sn.cn|gs.cn|qh.cn|nx.cn|xj.cn|tw.cn|hk.cn|mo.cn|org.hk|is|edu|mil|au|jp|int|kr|de|vc|ag|in|me|edu.cn|co.kr|gd|vg|co.uk|be|sg|it|ro|com.mo)(/.(cn|hk))*/";
    preg_match($pattern, $url, $matches);
    if (count($matches) > 0) {
        return $matches[0];
    } else {
        $rs = parse_url($url);
        $main_url = $rs["host"];
        if (!strcmp(long2ip(sprintf("%u", ip2long($main_url))) , $main_url)) {
            return $main_url;
        } else {
            $arr = explode(".", $main_url);
            $count = count($arr);
            $endArr = array(
                "com",
                "net",
                "org"
            ); //com.cn net.cn 等情况
            if (in_array($arr[$count - 2], $endArr)) {
                $domain = $arr[$count - 3] . "." . $arr[$count - 2] . "." . $arr[$count - 1];
            } else {
                $domain = $arr[$count - 2] . "." . $arr[$count - 1];
            }
            return $domain;
        }
    }
}

/**
 * xss安全过滤
 * @param  [type] $val [description]
 * @return [type]      [description]
 */
function remove_xss($val) {
   // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
   // this prevents some character re-spacing such as <java\0script>
   // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
   $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

   // straight replacements, the user should never need these since they're normal characters
   // this prevents like <IMG SRC=@avascript:alert('XSS')>
   $search = 'abcdefghijklmnopqrstuvwxyz';
   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
   $search .= '1234567890!@#$%^&*()';
   $search .= '~`";:?+/={}[]-_|\'\\';
   for ($i = 0; $i < strlen($search); $i++) {
      // ;? matches the ;, which is optional
      // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

      // @ @ search for the hex values
      $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
      // @ @ 0{0,7} matches '0' zero to seven times
      $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
   }

   // now the only remaining whitespace attacks are \t, \n, and \r
   $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
   $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
   $ra = array_merge($ra1, $ra2);

   $found = true; // keep replacing as long as the previous round replaced something
   while ($found == true) {
      $val_before = $val;
      for ($i = 0; $i < sizeof($ra); $i++) {
         $pattern = '/';
         for ($j = 0; $j < strlen($ra[$i]); $j++) {
            if ($j > 0) {
               $pattern .= '(';
               $pattern .= '(&#[xX]0{0,8}([9ab]);)';
               $pattern .= '|';
               $pattern .= '|(&#0{0,8}([9|10|13]);)';
               $pattern .= ')*';
            }
            $pattern .= $ra[$i][$j];
         }
         $pattern .= '/i';
         $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
         $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
         if ($val_before == $val) {
            // no replacements were made, so exit the loop
            $found = false;
         }
      }
   }
   return $val;
}

/**
 * 获取验证码
 * @param  string  $id     [验证码标识信息]
 * @param  integer $length [验证码长度]
 * @param  integer $width  [图片宽度]
 * @param  integer $height [图片高度]
 * @return [type]          [description]
 */
function getVerify($id ='',$length = 4,$width =0,$height=0,$fontSize=16,$useNoise = true,$useImgBg = false,$useCurve = true){
    $code = substr(md5(time()), 0, 20);
    //验证码配置
    $config =  array(
              // 验证码字体大小
              'fontSize'    =>  $fontSize,
              // 验证码位数
              'length'      =>  $length,
              // 关闭验证码杂点
              'useNoise'    =>  $useNoise,
              //切换背景图片
              'useImgBg'    =>  $useImgBg,
              //选择字体
              'fontttf'     => '5.ttf',
              'imageW'      =>$width,
              'imageH'      =>$height,
              'codeSet'     =>$code,
              //是否使用混淆曲线 默认为true
              'useCurve'    =>$useCurve,
              'bg' => array(243,251,254)
    );
    $Verify =  new \Think\Verify($config);
    $Verify->entry($id);
}

// 检测输入的验证码是否正确，$code为用户输入的验证码字符串
function check_verify($code, $id = ''){
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

function OP($key,$status='yes') {
    if (empty($key)) {
        return false;
    }
    $value = D("Home/Options")->getOptionName($key,$status);
    return $value['option_value'];
}

/**
 * 判断是否是本站上传的图片
 * @param  [type] $str [description]
 * @return [type]      [description]
 */
function check_imgPath($path){
    $path = remove_xss($path);
    //判断是否包含HTTP
    if(strstr($path,"http:") === false){
        $path = str_replace("//", "http://", $path);
    }
    $pathUrl = parse_url($path);
    //判断QQ头像，微信头像，新浪头像
    if((strstr(strtolower($pathUrl["host"]),"qizuang") === false)&&(strstr(strtolower($pathUrl["host"]),"wx.qlogo.cn") === false)&&(strstr(strtolower($pathUrl["host"]),"q.qlogo.cn") === false)&&(strstr(strtolower($pathUrl["host"]),"tp1.sinaimg.cn") === false)){
        return false;
    }
    return true;
}


//格式化时间
function timeFormat($times){
    $limit = time() - $times;
    if($limit < 60){
        return '刚刚';
    }elseif($limit < 3600){
        return floor($limit/60).'分钟前';
    }elseif($limit >= 3600 && $limit < 86400){
        return floor($limit/3600).'小时前';
    }elseif($limit >= 86400 && $limit < 86400*2){
        return '1天前';
    }
    return date('Y-m-d',$times);
}



//二维数组排序
function multi_array_sort($multi_array,$sort_key,$sort=SORT_ASC){
    if(is_array($multi_array)){
        foreach ($multi_array as $row_array){
            if(is_array($row_array)){
                $key_array[] = $row_array[$sort_key];
            }else{
                return false;
            }
        }
    }else{
        return false;
    }
    array_multisort($key_array,$sort,$multi_array);
    return $multi_array;
}

/**
* 生成从开始月份到结束月份的月份数组
* @param int $start 开始时间戳
* @param int $end 结束时间戳
*/
function get_month_list($start,$end){
    if(!is_numeric($start) || !is_numeric($end) || ($end < $start)){
        return false;
    }
    $start = date('Y-m',$start);
    $end = date('Y-m',$end);
    //转为时间戳
    $start = strtotime($start.'-01');
    $end = strtotime($end.'-01');
    $i = 0;
    $d = array();
    while($start <= $end){
        //这里累加每个月的的总秒数 计算公式：上一月1号的时间戳秒数减去当前月的时间戳秒数
        $d[$i]=trim(date('Y-m',$start),' ');
        $start+=strtotime('+1 month',$start)-$start;
        $i++;
    }
    return $d;
}

function get_day_list($start,$end)
{
    if(!is_numeric($start) || !is_numeric($end) || ($end < $start)){
        return false;
    }
    $result = array();
    while($start <= $end){
        //这里累加每个月的的总秒数 计算公式：上一月1号的时间戳秒数减去当前月的时间戳秒数
        $result[] = date('Y-m-d',$start);
        $start+=strtotime('+1 day',$start)-$start;
    }
    return $result;
}


/**
 * 求两个日期之间相差的天数
 * (针对1970年1月1日之后，求之前可以采用泰勒公式)
 * @param string $day1
 * @param string $day2
 * @return number
 */
function diffBetweenTwoDays ($day1, $day2)
{
    $second1 = strtotime($day1);
    $second2 = strtotime($day2);

    if ($second1 < $second2) {
        $tmp = $second2;
        $second2 = $second1;
        $second1 = $tmp;
    }
    return ($second1 - $second2) / 86400;
}

/**
 * 流水号
 * @return [type] [description]
 */
function sn(){
    return date('Ymd').substr(time(),-5).substr(microtime(),2,5);
}

//urlsafe_base64_encode函数
function urlsafe_base64_encode($data) {
    $data = str_replace(array('+','/'),array('-','_'),$data);
    return $data;
}

function hmac_sha1($str, $key){

    if (strlen($key) > 64) {
        $key = str_pad(sha1($key, true), 64, chr(0));
    }
    if (strlen($key) < 64) {
        $key = str_pad($key, 64, chr(0));
    }

    // Outter and Inner pad
    $opad = str_repeat(chr(0x5C), 64);
    $ipad = str_repeat(chr(0x36), 64);

    // Xor key with opad & ipad
    for ($i = 0; $i < strlen($key); $i++) {
        $opad[$i] = $opad[$i] ^ $key[$i];
        $ipad[$i] = $ipad[$i] ^ $key[$i];
    }

    $hash = sha1($opad.sha1($ipad.$str, true));
    return base64_encode($hash);
}

/**
 * 获取七牛请求图片的url
 * @param  [type] $url [description]
 * @return [type]      [description]
 */
function qiniu_file_url($str,$limit=10){
    $str = urldecode($str);
    $url = $str."?e=".(time()+$limit);
    $token = OP("QINIU_AK").":".urlsafe_base64_encode(hmac_sha1($url,OP("QINIU_CK")));
    $url .= "&token=".$token;
    return $url;
}

function timediff($value){
    //计算时间差
    $day = floor($value/86400);
    $hour = floor($value%86400/3600);
    $min = floor($value%86400%3600/60);
    $sec = floor($value%86400%3600%60);
    $str = "";
    if (!empty($day)) {
      $str .= $day."天";
    }

    if (!empty($hour)) {
      $str .= $hour."时";
    }

    if (!empty($min)) {
      $str .= $min."分";
    }

    if (!empty($sec)) {
      $str .= $sec."秒";
    }

    if (empty($str)){
        $str = '0秒';
    }

    return $str;
}

function timediff2($value){
    //计算时间差
    $day = floor($value/86400);
    $hour = floor($value%86400/3600);
    $min = floor($value%86400%3600/60);
    $sec = floor($value%86400%3600%60);
    $str = "";
    if (!empty($day)) {
        if ($day < 10) {
            $day = "0".$day;
        }
        $str .= $day."天 ";
    }

    if ($hour < 10) {
        $hour = "0".$hour;
    }
    $str .=  empty($hour)?"00":$hour;

    if ($min < 10) {
        $min = "0".$min;
    }
    $str .= empty($min)?"00":":".$min;

    if ($sec < 10) {
        $sec = "0".$sec;
    }

    $str .= empty($sec)?"00":":".$sec;

    return $str;
}

/**
 * 简单验证手机
 * @param  [type] $tel [description]
 * @return [type]      [description]
 */
function validate_mobile($tel){
    $pattern = '/^\d{11}$/';
    preg_match($pattern, $tel,$m);
    return count($m) == 0 ? 0 : 1;
}

/**
 * 验证手机
 * @param  [type] $tel [description]
 * @return [type]      [description]
 */
function validate_mobile2($tel){
    $pattern = '/^(13|14|15|16|17|18|19)[0-9]{9}$/';
    preg_match($pattern, $tel,$m);
    return count($m) == 0 ? 0 : 1;
}

/**
 *
 * [check_menu_auth 检查是否有菜单权限]
 * 请勿使用该函数，如有需要请使用check_user_menu_auth函数
 * @param  [type]  $link    [菜单地址，如 /order/]
 * @param  integer $uid     [用户uid，不传则默认为当前登录用户uid]
 * @param  integer $version [菜单版本，1：老版后台，2：新版后台，默认新版]
 * @return [type]           [description]
 */
function check_menu_auth($link, $uid = 0, $version = 2){

    //初始化uid
    if (empty($uid)) {
        $uid = getAdminUser('uid');
    }

    $link = trim($link, '/');
    //如果链接为空或者是管理员则通过验证
    if (empty($link) || $uid  == 1) {
        return true;
    }
    //兼容性
    $link = '/' . $link . '/';

    $menu = S('AUTH:MENU');
    if (empty($menu)) {
        $map = array(
            'enabled' => 1
        );
        $results = M('rbac_system_menu')->alias('m')
                                        ->field('m.nodeid, m.link, m.version, GROUP_CONCAT(role_id) AS uids')
                                        ->join('LEFT JOIN qz_rbac_node_role AS n ON n.node_id = m.nodeid')
                                        ->where($map)
                                        ->group('m.nodeid')
                                        ->select();
        foreach ($results as $key => $value) {
            if ($value['version'] == 1) {
                $str = array('http://168.qizuang.com', '#');
            } else {
                $str = array('http://168new.qizuang.com', '#');
            }
            $menuLink = trim(str_replace($str, ['', ''], $value['link']), '/');
            if (!empty($menuLink)) {
                $menuLink = $value['version'] . '-/' . strtolower($menuLink) . '/';
                $menu[$menuLink] = array_filter(explode(',', $value['uids']));
            }
        }
        S('AUTH:MENU', $menu, 600);
    }

    //如果该菜单存在，且该角色不拥有该菜单，则返回false，其他情况返回true
    if (!empty($menu[$version . '-' . $link]) && !in_array($uid, $menu[$version . '-' . $link])) {
        return false;
    }
    return true;
}
// 判断IPv4地址
function is_ip($ip) {
    $ip = ip2long(trim($ip));
    return  !(FALSE === $ip || -1 === $ip);
}

//验证IP
function is_internal_ip($ip) {
    $ip = ip2long($ip);
    $net_a = ip2long('10.255.255.255') >> 24; //A类网预留ip的网络地址
    $net_b = ip2long('172.31.255.255') >> 20; //B类网预留ip的网络地址
    $net_c = ip2long('192.168.255.255')>> 16; //C类网预留ip的网络地址
    return  $ip >> 24 === $net_a || $ip >> 20 === $net_b || $ip >> 16 === $net_c;
}
//验证IP
function is_external_ip($ip) {
    return  is_ip($ip) && !is_internal_ip($ip);
}

/**
 *  转换xml到数组 利用转换成json做中间环节
 * @param  xml文档 $xml
 * @return bool false or array
 */
function xmltoarray($xml) {
    if (empty($xml)) {
        return false;
    }
    return json_decode(json_encode((array) simplexml_load_string($xml)), true);
}

function ismobile() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        return true;

    //此条摘自TPM智能切换模板引擎，适合TPM开发
    if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
        return true;
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
        //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    //判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
        );
        //从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

/**
 *
 * 齐装网发送短信
 *
 * @param array $data
 * tel电话 必须
 * variable变量数组用户替换模版中变量,具有和模版对应的顺序性 不传入模版不会得到替换  必须
 * type发送信息类型 yzm,password,fborder_work,fborder_rest,fborder,toyz_wjt,toyz_fpgs,toyz_fpgs2,toyz_fpgs1,nil 为nil的时候不进行模版变量替换variable数组可以不传入 必须
 * sms_channel 短信通道 yuntongxun,huyi,yunrongt 默认OP全局配置
 * extend 扩展信息 用于传入额外的数据
 *
 * @return  array
 * array['errcode'] 错误码,0为无错误成功
 * array['errmsg'] 错误信息
 * array['data']   发送短信后返回数据
 */
function sendSmsQz($data)
{
    //dump($data); //调试用,线上生产环境必须是注释状态
    //定义返回数组
    $reArr = array();
    $reArr['errcode']; //错误码, 0为无错误成功
    $reArr['errmsg'];  //错误信息
    $reArr['data'];    //数据
    //判断传入参数验证
    if (empty($data['tel'])) {
        //必须要有号码
        $reArr['errcode'] = '-721';
        $reArr['errmsg'] = '必须要有接收号码';
        return $reArr;
    }
    /*//判断需要要有 短信模版或者模版id
    if ( empty($data['tpl'])  && empty($data['tplid']) ) {
        //必须要有短信模版或者模版id (某些短信通道不用模版而是要模版id)
        $reArr['errcode'] = '-722';
        $reArr['errmsg'] = '短信模版或者短信模版id必须2选1';
        return $reArr;
    }*/
    //处理 type类型
    if (!in_array($data['type'],array('yzm','password','fborder_work',
        'fborder_rest','fborder','toyz_wjt','toyz_fpgs','toyz_fpgs2','toyz_fpgs1',
        'nil','toyz_wjt_review') ) ) {
        //如果传入的发送短信类型未知,那么设置为nil
        $data['type'] = 'nil';
    }

    //处理短信发送通道
    if (!in_array($data['sms_channel'],array('yuntongxun','ihuyi','yunrongt',"yunrongyx") ) ) {
        //如果传入的通道是未知的 那么使用全局配置
        $data['sms_channel'] = OP('sms_channel','yes') ? : 'yuntongxun';
    }

    import('Library.Org.Util.App');
    $app    = new \App();

    $smsMessageSid = '';
    $dateCreated= '';
    $remark = '';
    $status = 0;

    //$data['sms_channel'] = 'yuntongxun'; //调试用 线上生产模式必须是注释状态

    //判断短信通道并发送短信
    switch ($data['sms_channel']) {
        case 'yuntongxun':
            //短信通道为 容联云通讯
            import('Library.Org.yuntongxun.Telytx'); //引入类
            $Telytx        = new \Telytx(); //实例化

            switch ($data['type']) {
                case 'yzm':
                    $data['tplid'] = OP('SMS_ORDERFB_INDEX');
                    break;
                case 'password':
                    $data['tplid'] = OP('SMS_REGISTER_INDEX');
                    break;
                case 'fborder_work':
                    $data['tplid'] = OP('sms_temp_id_zbfb_83021');
                    break;
                case 'fborder_rest':
                    $data['tplid'] = OP('sms_temp_id__zbfb_21830');
                    break;
                case 'fborder':
                    $data['tplid'] = OP('sms_temp_id_zbfb_89700');
                    break;
                case 'toyz_wjt':
                    $data['tplid'] = OP('sms_temp_id_123338');
                    break;
                case 'toyz_fpgs':
                    //发送给业主已经分配的装修公司  本渠道暂不支持
                    $reArr['errcode'] = '-2';
                    $reArr['errmsg'] = '本渠道暂不支持发送本信息';
                    return $reArr;
                    break;
                case 'toyz_fpgs2':
                    //发送给业主已经分配的装修公司  本渠道暂不支持
                    $reArr['errcode'] = '-2';
                    $reArr['errmsg'] = '本渠道暂不支持发送本信息';
                    return $reArr;
                    break;
                case 'toyz_fpgs1':
                    //发送给业主已经分配的装修公司  本渠道暂不支持
                    $reArr['errcode'] = '-2';
                    $reArr['errmsg'] = '本渠道暂不支持发送本信息';
                    return $reArr;
                    break;
                case 'nil':
                    //未指定发送短信类型
                    $reArr['errcode'] = '-1';
                    $reArr['errmsg'] = '未指定短信发送类型';
                    return $reArr;
                    break;
            }
            $result        =  $Telytx->sendTemplateSMS($data['tel'],$data['variable'],$data['tplid']); //发送
            $smsMessageSid =  $result["smsMessageSid"];
            $dateCreated   =  $result["dateCreated"];
            $remark        =  $result["msg"];
            $reArr['data']    = $result;  //接口结果信息
            $reArr['errcode'] = 1; //错误码, 0为无错误成功
            $reArr['errmsg']  = '调用失败!';  //错误信息
            if ($result["status"] == 1) {
                $reArr['errcode'] = 0; //错误码, 0为无错误成功
                $reArr['errmsg']  = '成功!';  //错误信息

                $status = 1;
            }
            break;
        case 'ihuyi':

            switch ($data['type']) {
                case 'yzm':
                    $data['tpl'] = OP('sms_ihuyi_56869');
                    //做发送短信内容
                    $tpls         = str_replace("【变量】","%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0],  $data['variable'][1]); // 做模版
                    break;
                case 'password':
                    $data['tpl'] = OP('sms_ihuyi_141830');
                    //做发送短信内容
                    $tpls         = str_replace("【变量】","%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0]); // 做模版
                    break;
                case 'fborder_work':
                    //本渠道暂不支持
                    $reArr['errcode'] = '-2';
                    $reArr['errmsg'] = '本渠道暂不支持发送本信息';
                    return $reArr;
                    break;
                case 'fborder_rest':
                    //本渠道暂不支持
                    $reArr['errcode'] = '-2';
                    $reArr['errmsg'] = '本渠道暂不支持发送本信息';
                    return $reArr;
                    break;
                case 'fborder':
                    $data['tpl'] = OP('sms_ihuyi_141829');
                    //做发送短信内容
                    $tpls         = str_replace("【变量】","%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0]); // 做模版
                    break;
                case 'toyz_wjt':
                    $data['tpl'] = OP('sms_ihuyi_141828');
                    //做发送短信内容
                    $tpls         = str_replace("【变量】","%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0]); // 做模版
                    break;
                case 'toyz_fpgs':
                    $data['tpl'] = OP('sms_ihuyi_119008');
                    //做发送短信内容
                    $tpls         = str_replace("【变量】","%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0], $data['variable'][1]); // 做模版
                    break;
                case 'toyz_fpgs2':
                    //发送给业主已经分配的装修公司  本渠道暂不支持
                    $reArr['errcode'] = '-2';
                    $reArr['errmsg'] = '本渠道暂不支持发送本信息';
                    return $reArr;
                    break;
                case 'toyz_fpgs1':
                    //发送给业主已经分配的装修公司  本渠道暂不支持
                    $reArr['errcode'] = '-2';
                    $reArr['errmsg'] = '本渠道暂不支持发送本信息';
                    return $reArr;
                    break;
                case 'nil':
                    //未指定发送短信类型
                    $reArr['errcode'] = '-1';
                    $reArr['errmsg'] = '未指定短信发送类型';
                    return $reArr;
                    break;
            }
            $result           = xmltoarray($app->SmsSend($data['tel'], $data['tpld'])); //发送
            $smsMessageSid    = $result["smsid"];
            $remark           = $result["msg"];
            $reArr['data']    = $result;  //接口结果信息
            $reArr['errcode'] = 1; //错误码, 0为无错误成功
            $reArr['errmsg']  = '调用失败!';  //错误信息
            if ($result["code"] == 2) {
                $reArr['errcode'] = 0; //错误码, 0为无错误成功
                $reArr['errmsg']  = '成功!';  //错误信息

                $status = 1;
            }
            break;
        case 'yunrongt':
            import('Library.Org.Util.Yunrongt');
            $Yunrongt    = new \Yunrongt();

            switch ($data['type']) {
                case 'yzm':
                    $data['tpl'] = OP('yunrongt_tpl_yzm');
                    //做发送短信内容
                    $tpls         = str_replace("【变量】","%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0],  $data['variable'][1]); // 做模版
                    break;
                case 'password':
                    $data['tpl'] = OP('yunrongt_tpl_password');
                    //做发送短信内容
                    $tpls         = str_replace("【变量】","%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0]); // 做模版
                    break;
                case 'fborder_work':
                    $data['tpl'] = OP('yunrongt_tpl_fborder_work');
                    //做发送短信内容
                    $tpls         = str_replace("【变量】","%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0],  $data['variable'][1]); // 做模版
                    break;
                case 'fborder_rest':
                    $data['tpl'] = OP('yunrongt_tpl_fborder_rest');
                    //做发送短信内容
                    $tpls         = str_replace("【变量】","%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0],  $data['variable'][1]); // 做模版
                    break;
                case 'fborder':
                    $data['tpl'] = OP('yunrongt_tpl_fborder');
                    //做发送短信内容
                    $tpls         = str_replace("【变量】","%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0]); // 做模版
                    break;
                case 'toyz_wjt':
                    $data['tpl'] = OP('yunrongt_tpl_wjt');
                    //做发送短信内容
                    $tpls         = str_replace("【变量】","%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0], $data['variable'][1]); // 做模版
                    break;
                case 'toyz_fpgs':
                    $data['tpl'] = OP('yunrongt_tpl_fpgs');
                    //做发送短信内容
                    $tpls         = str_replace("【变量】","%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0],  $data['variable'][1]); // 做模版
                    break;
                case 'toyz_fpgs2':
                    $data['tpl'] = OP('yunrongt_tpl_fpgs2');
                    //做发送短信内容
                    $tpls         = str_replace("【变量】","%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0],  $data['variable'][1],  $data['variable'][2],
                                            $data['variable'][3],  $data['variable'][4],  $data['variable'][5]); // 做模版
                    break;
                case 'toyz_fpgs1':
                    $data['tpl'] = OP('yunrongt_tpl_fpgs1');
                    //做发送短信内容
                    $tpls         = str_replace("【变量】","%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0],  $data['variable'][1],  $data['variable'][2]); // 做模版
                    break;
                case 'nil':
                    //做发送短信内容
                    $data['tpld'] = $data['tpl']; // 直接发送传入的模版
                    break;
                case 'toyz_wjt_review':
                    $data['tpl'] = OP('yunrongt_tpl_wjt_review');
                    //客服回访订单-做发送短信内容
                    $data['tpld'] =  $data['tpl']; // 做模版
                    break;
            }

            //做提交信息
//            $smsdata['cmd']         = 'sendMessage'; //单条发送短信
            $smsdata['mobile']        = $data['tel']; //手机号码
            $smsdata['content']       = $data['tpld']; //短信内容

            $result                 =  $Yunrongt->sendMessage($smsdata);
            // dump($smsdata); //调试用,线上生产环境必须是注释状态

            $reArr['data']    = $result;  //接口结果信息
            $reArr['errcode'] = 1; //错误码, 0为无错误成功
            $reArr['errmsg']  = '调用失败!';  //错误信息
            if ($result["errcode"] > 0) {
                $reArr['errcode'] = 0; //错误码, 0为无错误成功
                $reArr['errmsg']  = '成功!';  //错误信息

                $status = 1;
            }
            break;
        case 'yunrongyx':
            import('Library.Org.Util.Yunrongt');
            $Yunrongt    = new \Yunrongt();
            switch ($data['type']) {
                case 'nil':
                //做发送短信内容
                $data['tpld'] = $data['tpl']; // 直接发送传入的模版
                break;
            }
//            $smsdata['cmd']         = 'sendMessage'; //单条发送短信
            $smsdata['mobile'] = $data['tel']; //手机号码
            $smsdata['content']        = $data['tpld']; //短信内容
            $result                 = $Yunrongt->sendYxMessage($smsdata);

            $reArr['data']    = $result;  //接口结果信息
            $reArr['errcode'] = 1; //错误码, 0为无错误成功
            $reArr['errmsg']  = '调用失败!';  //错误信息
            if ($result["errcode"] > 0) {
                $reArr['errcode'] = 0; //错误码, 0为无错误成功
                $reArr['errmsg']  = '成功!';  //错误信息

                $status = 1;
            }
            break;
    }

    $tel_encrypt   =  substr_replace($data['tel'],"*****",3,5); //做一个中间为星号的号码
    $tel_md5       =  $app->order_tel_encrypt($data['tel']); //做一个加密后的号码
    $time          =  date('Y-m-d H:i:s'); //取当前时间

    //做一个日志
    $smslog = array(
        "type"          =>  $data['extend']['type'] ? : 0,
        "op_user"       =>  $data['extend']['op_user'] ? :'',
        "op_id"         =>  $data['extend']['op_id'] ? :'',
        "orderid"       =>  $data['extend']['orderid'] ? :'',
        "ip"            =>  $app->get_client_ip(),
        "tel"           =>  $tel_encrypt, //为了隐私记录打引号的
        "tel_encrypt"   =>  $tel_md5, //记录 电话号码加密 为了便于查找
        "smsMessageSid" =>  $smsMessageSid,
        "dateCreated"   =>  $dateCreated,
        "remark"        =>  $remark . ";" . $data['type'],
        "addtime"       =>  $time,
        "api_type"      =>  $data['sms_channel'],
        "api_code"      =>  $reArr['errcode'],
        "status"        =>  $status
    );
    M("log_sms_send")->add($smslog); //写日志
    return $reArr;
}

//获取Referer
function getReferer() {
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : getenv('HTTP_REFERER') ;
    if(empty($referer) && isset($GLOBALS['_SERVER']['HTTP_REFERER'])) {
        $referer = '';
    }
    return $referer;
}

/**
 * [build_tree 递归获取多级分类树形结构]
 * @param  [type] $root    [根节点ID]
 * @param  [type] $results [需要获取的全部结果集]
 * @param  string $node    [父节点ID的字段名]
 * @author lliie
 */
function build_tree($root, $results, $node = 'parent',$children = 'children',$level=1){
    $childs=array();
    foreach ($results as $k => $v){
        if($v[$node] == $root){
            $v['level'] = $level;
            $childs[] = $v;
        }
    }

    if(empty($childs)){
        return null;
    }

    foreach ($childs as $k => $v){
        $rescurTree = build_tree($v["id"], $results, $node ,$children, $level+1);
        if( null != $rescurTree){
            $childs[$k][$children] = $rescurTree;
        }
    }

    return $childs;
}

/**
 * 校验日期格式是否正确
 * @param string $date 日期
 * @param string $formats 需要检验的格式
 * @return boolean
 */
function check_date($date, $format = 'Y-m-d') {
    $time = strtotime($date);
    if (!$time) { //strtotime转换不对，日期格式显然不对。
        return false;
    }
    //校验日期的有效性，只要满足格式就OK
    if (date($format, $time) == $date) {
        return true;
    }
    return false;
}

/**
 * 获取token
 * @return [type] [description]
 */
function getToken($sign,$time)
{
    //md5加密
    $key = OP("AU_KEY");
    $sign = $sign.$time.$key;
    $token = md5($sign);
    return $token;
}

/**
 * 主动推送文章
 * @param  [string]     $url    [文章的URL]
 * @return [void]               [推送结果（不提供返回值）]
 */
function sentURLToBaidu($url,$original=false)
{
    if($original){
        // $api = "http://data.zz.baidu.com/urls?site=https://m.qizuang.com/&token=QCxknCDaTZ9LZ4M6&type=original";
        $api = "http://data.zz.baidu.com/urls?appid=1575859058891466&token=ojDF37fDJcgtw5jm&type=realtime,original";
        //主动推送原创文章
    }else{
        $api = "http://data.zz.baidu.com/urls?site=https://m.qizuang.com&token=QCxknCDaTZ9LZ4M6";
        //主动推送普通文章
    }
    $urls = array(
            $url,
        );
    $ch = curl_init();
    $options =  array(
        CURLOPT_URL => $api,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => implode("\n", $urls),
        CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
    );
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    curl_close($ch);
    //echo $result;
    return $result;
}

/**
 * 主动推送文章
 * @param  [string]     $url    [文章的URL]
 * @return [void]               [推送结果（不提供返回值）]
 */
function sentURLToBaiduKeyword($url,$is_pc=false)
{
    if($is_pc){
        $api = "http://data.zz.baidu.com/urls?site=www.qizuang.com&token=sz3gomc42qz4im2L";
    }else{
        $api = "http://data.zz.baidu.com/urls?site=m.qizuang.com&token=sz3gomc42qz4im2L";
    }

    //主动推送普通文章
    $urls = array(
        $url,
    );
    $ch = curl_init();
    $options =  array(
        CURLOPT_URL => $api,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => implode("\n", $urls),
        CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
    );
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    curl_close($ch);
    //echo $result;
    return $result;
}

/**
 * 主动推送熊掌号
 * @param  [string]     $url    [文章的URL]
 * @return [void]
 */
function sentURLToXiongZhang($url)
{
    $api = "http://data.zz.baidu.com/urls?site=https://m.qizuang.com/&token=oUF1qxHVNpg04aKG&type=daily";
    $urls = array(
        $url,
    );
    $ch = curl_init();
    $options =  array(
        CURLOPT_URL => $api,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => implode("\n", $urls),
        CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
    );
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

/**
 * 主动推送美图
 * @param  [string]     $url    [文章的URL]
 * @return [void]               [推送结果（不提供返回值）]
 */
function sentMeituToBaidu($url,$original=false)
{
    $api = "http://data.zz.baidu.com/urls?site=http://meitu.qizuang.com&token=QCxknCDaTZ9LZ4M6";//主动推送普通文章
    $urls = array(
            $url,
        );
    $ch = curl_init();
    $options =  array(
        CURLOPT_URL => $api,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => implode("\n", $urls),
        CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
    );
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    //echo $result;
    return $result;
}


/**
 * 返回当天的原创文章数量
 * @param  [int]      $num       [原创文章数量]
 * @param  [bool]     $flag      [原创标识 false为非原创 true 为原创]
 * @return [int]      $num       [原创数量]
 */
function returnOriginal($num,$flag=false)
{
    //1,初始化
    $ch = curl_init();
    //2.设置URL和相应的选项
    curl_setopt($ch, CURLOPT_URL, "http://task.qizuang.com/index/returnOriginal");
    //设置返回值
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if($flag){
        //设置参数
        $arr = array('num' => $num+1);
        $data = json_encode($arr);
        curl_setopt($ch, CURLOPT_POST, 1 );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    //3.抓取URL并把它传递给浏览器
    $num = curl_exec($ch);
    //关闭cURL资源，并且释放系统资源
    curl_close($ch);

    return intval($num);
}

/**
 * 返回当天的推送剩余量
 * @param  [int]      $num       [数量]
 * @param  [string]   $type      [类型  original-原创  normal-主动]
 * @param  [bool]     $flag      [原创标识 false为非原创 true 为原创]
 * @return [int]      $num       [原创数量]
 */
function returnRemainingNumber($num,$type,$flag=false)
{
    //1,初始化
    $ch = curl_init();
    //2.设置URL和相应的选项
    curl_setopt($ch, CURLOPT_URL, "http://task.qizuang.com/index/returnRemainingNumber");
    //设置返回值
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if($flag){
        //设置参数
        $arr = array('num' => $num , 'type' => $type);
        $data = json_encode($arr);
        curl_setopt($ch, CURLOPT_POST, 1 );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    //3.抓取URL并把它传递给浏览器
    $num = curl_exec($ch);
    //关闭cURL资源，并且释放系统资源
    curl_close($ch);
    return $num;
}

/**
 * 导入excel文件
 * @param  string $file excel文件路径
 * @return array        excel文件内容数组
 */
function import_excel($file){
    $type = pathinfo($file);
    $type = strtolower($type["extension"]);

    if ($type == 'xlsx') {
        $type = 'Excel2007';
    } elseif ($type == 'xls') {
        $type = 'Excel5';
    }

    ini_set('max_execution_time', '0');
    import('Library.Org.Phpexcel.PHPExcel',"",".php");
    import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");

    $objReader = PHPExcel_IOFactory::createReader($type);
    return $objReader->load($file)->getActiveSheet()->toArray();
}

/**
 * 新版 导出excel文件 浏览器下载
 * @param  array $excelData 数据数组
 *
 * $excelData['header']    = []; //表头
 * $excelData['sheet']     = 'Sheet1'; //sheet名
 * $excelData['row']       = []; //记录
 * $excelData['filename']  = []; //导出的文件名
 *
 * @return file        excel文件
 */
function export_excel_download($excelData)
{
    ini_set('memory_limit','512M');
    ini_set('max_execution_time', 60 * 3);
    import('Library.Org.PHP_XLSXWriter.xlsxwriter');
    $writer = new \XLSXWriter();
    if (empty($excelData['filename'])) {
        $excelData['filename'] = 'excel'.date('YmdHis').'xlsx';
    }

    if (!empty($excelData['header']) && !empty($excelData['sheet'])) {
        $writer->writeSheetHeader($excelData['sheet'], $excelData['header'] );
        foreach($excelData['row'] as $row) {
            $writer->writeSheetRow($excelData['sheet'], $row );
        }
    } else {
        $writer->writeSheet($excelData['row']);
    }
    ob_end_clean();
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
    header("Content-Type:application/force-download");
    header("Content-Type:application/vnd.ms-execl");
    header("Content-Type:application/octet-stream");
    header("Content-Type:application/download");;
    header('Content-Disposition:attachment;filename="'.$excelData['filename'].'"');
    header("Content-Transfer-Encoding:binary");
    $writer->writeToStdOut("php://output");
}


/**
 *
 *  输入一个 把float为    INF  NAN 的情况, 处理为返回0
 *
 * @param $input float(NAN) float(INF)
 * @return int
 */
function setInfNanToN($input) {
    if (is_nan($input) || is_infinite($input)) {
        return 0;
    }
    return $input;
}


/**
 *
 * 处理src 过滤掉src中的特殊字符
 *
 * 采用正则提取的方式过滤src中的特殊字符，提取符合正则的src
 *
 * @param $src_orgin
 * @return string
 */
function srcPure($src_orgin)
{

    $removeArr = [
        'http://',
        'https://',
        'file://',
        'ftp://',
    ];
    //如果src中含有非法字符,那么直接返回src为空字符
    foreach ($removeArr as $k => $v) {
        $pos = strpos($src_orgin, $v);
        if ($pos !== false) {
            return '';
        }
    }
    unset($v);


    $reg = '/([a-z0-9]+\-?)+/';
    preg_match($reg, trim($src_orgin), $matches);
    if ($matches > 0) {
        return $matches[0];
    }
    return $src_orgin;
}

/**
 * 生成加密密码
 * @param int $length
 * @return string
 */
function make_password( $length = 8 ) {
    // 密码字符集，可任意添加你需要的字符
    $chars = 'abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ123456789';
    $password = '';
    for ( $i = 0; $i < $length; $i++ ){
        $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
    }

    $pass = md5($password);//初始密码
    return array('pass'=>$pass,'nopass'=>$password);
}

/**
 * 取数组中指定建，详细请看laravel函数文档
 * @param $array
 * @param $keys
 * @return array
 */
if (!function_exists('array_only')) {
    function array_only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array)$keys));
    }
}

if (!function_exists('array_column')) {
    function array_column(array $array, $column_key, $index_key = null)
    {
        $result = [];
        foreach ($array as $arr) {
            if (!is_array($arr)) continue;

            if (is_null($column_key)) {
                $value = $arr;
            } else {
                $value = $arr[$column_key];
            }

            if (!is_null($index_key)) {
                $key = $arr[$index_key];
                $result[$key] = $value;
            } else {
                $result[] = $value;
            }
        }
        return $result;
    }
}
/**
 * author: mcj
 * 生成erp订单号
 * $source 2齐装自动给的单，3营销宝系统自主录入
 * A(auto)开头为齐装自动派单生成的订单号
 * M(M)开头为装修公司自己活得的订单，生成的单号
 */
if (!function_exists('getOrderNo')) {
    function getOrderNo($source = 2)
    {
        $prefix = 'A';
        if ($source === 3) {
            $prefix = 'M';
        }
        $order_no = $prefix . date('Ymd') . sprintf("%05d%03d", time() % 86400, mt_rand(1, 1000));
        return $order_no;
    }
}

/**
 *      把秒数转换为时分秒的格式
 *      @param Int $times 时间，单位 秒
 *      @return String
 */
function secToTime($times){
    $result = '00:00:00';
    if ($times>0) {
        $hour = floor($times/3600);//时
        $minute = floor(($times-3600 * $hour)/60);//分
        $second = floor((($times-3600 * $hour) - 60 * $minute) % 60);//秒
        if($hour > 0){
            $result = $hour.'小时'.$minute.'分钟';
        }else{
            $result = $minute.'分钟';
        }
    }
    return $result;
}

function getToday($date){
    if (!empty($date)) {
        return $date;
    }
    return date("Y-m-d");
}

if (!function_exists('get_city_by_ip')) {
    function get_city_by_ip($ip = ''){
        if(!empty($ip)){
            $ipSource = new \Library\Org\ipipdotnet\City();
            return $ipSource->find($ip);
        }
        return false;
    }
}

/**
 * 批量发送post请求 , 结果会将请求的url作为索引
 * $urls 发送的url数组
 * $post 发送的请求数组
 */
if (!function_exists('curl_post_batch')) {
    function curl_post_batch($urls = [],$post = []){
        if(empty($urls) || empty($post)){
            return [];
        }
        $queue = curl_multi_init();
        $map = array();
        foreach ($urls as $key => $url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);//在尝试连接时等待的秒数
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);//允许 cURL 函数执行的最长秒数
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($url, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post[$key]);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_NOSIGNAL, true);
            curl_multi_add_handle($queue, $ch);
            $map[(string)$ch] = $url;
        }
        $responses = array();
        do {
            while (($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM) ;
            if ($code != CURLM_OK) { break; }
            while ($done = curl_multi_info_read($queue)) {
                $error = curl_error($done['handle']);
                $results = curl_multi_getcontent($done['handle']);
                if (empty($error)) {
                    $responses[] = $results;
                } else {
                    $responses[] = compact('error', 'results');
                }
                curl_multi_remove_handle($queue, $done['handle']);
                curl_close($done['handle']);
            }
            if ($active > 0) {
                curl_multi_select($queue, 0.5);
            }
        } while ($active);
        curl_multi_close($queue);
        return $responses;
    }
}


if (!function_exists('curl')){
    /**
     * curl请求函数封装
     * @param mixed $curllink
     * @param mixed $data
     * @param mixed $type
     * @return mixed
     */
    function curl($curllink, $data = [],$headers = null, $timeout = 3)
    {
        //初始化一个curl对象
        $ch = curl_init();
        //设置需要抓取的url
        curl_setopt($ch, CURLOPT_URL, $curllink);
        //跳过SSL证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //连接超时15秒
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        //超时设置60秒
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        if (is_array($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设定是否显示头信息
        $output = curl_exec($ch);//执行

        curl_close($ch);//关闭
        //判断能否解析成json
        return isJson($output) !== false ? isJson($output,true) : $output;
    }
}

if (!function_exists('isJson')) {
    /**
     * 判断字符串是否为Json格式
     * @param  string $data Json 字符串
     * @param  bool $assoc 是否返回关联数组。默认返回对象
     * @return bool|array 成功返回转换后的对象或数组，失败返回 false
     */
    function isJson($jsonString = '', $assoc = true)
    {
        $data = json_decode($jsonString, $assoc);
        if (is_array($data)) {
            return $data;
        }
        return false;
    }
}


/**
 * PHP计算两个时间段是否有交集（边界重叠不算）
 *
 * @param string $beginTime1 开始时间1
 * @param string $endTime1 结束时间1
 * @param string $beginTime2 开始时间2
 * @param string $endTime2 结束时间2
 * @return bool
 */
if (!function_exists('is_time_cross')) {
    function is_time_cross($beginTime1 = '', $endTime1 = '', $beginTime2 = '', $endTime2 = '')
    {
        $status = $beginTime2 - $beginTime1;
        if ($status > 0) {
            $status2 = $beginTime2 - $endTime1;
            if ($status2 >= 0) {
                return false;
            } else {
                return true;
            }
        } else {
            $status2 = $endTime2 - $beginTime1;
            if ($status2 > 0) {
                return true;
            } else {
                return false;
            }
        }
    }
}


/**
 * 图片地址拼接判断 如果有完整地址了则不拼接
 * $url  图片地址
 * $type  图片来源， type为1表示来源于PC或者M端，   type为2表示图片来自装修说/齐装APP
 */
if(!function_exists('changeImgUrl')) {
    function changeImgUrl($url,$type = 1)
    {
        if(empty($url) || $url == ''){
            return '';
        }
        $preg = "/^http(s)?:\\/\\/.+/";
        if (preg_match($preg, $url)) {
            return $url;
        } else {
            if($type == 1){
                return C('QINIU_DOMAIN') . '/' .$url;
            }elseif($type == 2){
                return C('ZXS_QINIU_DOMAIN') . '/' .$url;
            }
        }
    }
}

/**
 * 图片地址拼接判断 如果有完整地址了则不拼接
 * $url  图片地址
 * $type  图片来源， type为1表示来源于PC或者M端   ，   type为2表示图片来自装修说/齐装APP
 */
if(!function_exists('changeImgUrl2')) {
    function changeImgUrl2($url,$type = 1)
    {
        if(empty($url) || $url == ''){
            return '';
        }
        $preg = "/^http(s)?:\\/\\/.+/";
        if (preg_match($preg, $url)) {
            return $url;
        } else {
            if($type == 1){
                return 'https://' . C('QINIU_DOMAIN') . '/' .$url;      //C('QINIU_DOMAIN')没有带协议，  这里返回的时候补全了
            }elseif($type == 2){
                return C('ZXS_QINIU_DOMAIN') . '/' .$url;
            }
        }
    }
}

/**
 * 电话加密
 * @param  [type] $tel [description]
 * @return [type]      [description]
 */
if(!function_exists('tel_encrypt')) {
    function tel_encrypt($tel)
    {
        return md5($tel . C('QZ_YUMING'));
    }
}


/**
 * 判断是否SSL协议
 * @return boolean
 */
function isSsl()
{
    $server = $_SERVER;
    if (isset($server['HTTPS']) && ('1' == $server['HTTPS'] || 'on' == strtolower($server['HTTPS']))) {
        return true;
    } elseif (isset($server['REQUEST_SCHEME']) && 'https' == $server['REQUEST_SCHEME']) {
        return true;
    } elseif (isset($server['SERVER_PORT']) && ('443' == $server['SERVER_PORT'])) {
        return true;
    } elseif (isset($server['HTTP_X_FORWARDED_PROTO']) && 'https' == $server['HTTP_X_FORWARDED_PROTO']) {
        return true;
    }
    return false;
}


if (!function_exists('getSchemeAndHost')) {
    /**
     * 获取访问协议和httphost
     *
     * 'isSsl' => boolean false
     * 'scheme' => string 'http'
     * 'scheme_full' => string 'http://'
     * 'host' => string 'm.qizuang.com'
     * 'domain' => string 'qizuang.com'
     * 'scheme_host' => string 'http://m.qizuang.com'
     *
     * @return array
     */
    function getSchemeAndHost()
    {
        $reArr = [];
        $isSsl = isSsl() ? true : false; // 是否是https
        $reArr['isSsl'] = $isSsl;
        $reArr['scheme'] = $isSsl ? 'https' : 'http';
        $reArr['scheme_full'] = $reArr['scheme'].'://';
        $reArr['host'] = $_SERVER['HTTP_HOST'];
        $hostArr = explode('.',$reArr['host']);
        $hostDomain = implode('.',array_slice($hostArr,-2));
        $reArr['domain'] = $hostDomain;
        $reArr['scheme_host'] = $reArr['scheme_full'] . $_SERVER['HTTP_HOST'];
        return $reArr;
    }
}

if (!function_exists('getAppEnv')) {

    function getAppEnv($default = 'prod') {
        $APP_ENV = C('APP_ENV');
        if($APP_ENV) {
            return $APP_ENV;
        }
        return $default;
    }
}
