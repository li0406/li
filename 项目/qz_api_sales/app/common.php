<?php
// 应用公共文件


/**
 * 统一接口返回格式
 * @Author   lovenLiu
 * @DateTime 2019-05-09T17:55:58+0800
 */
if(!function_exists('sys_response')) {
    function sys_response($error_code = 0, $error_msg = '', $data = null){
        // 如果$error_msg为空则自动获取
        if (empty($error_msg)) {
            $key = 'CODE_' . $error_code;
            $reflectionClass = new ReflectionClass('\app\common\enum\BaseStatusCodeEnum');
            if ($reflectionClass->hasConstant($key)) {
                $error_msg = $reflectionClass->getConstant($key);
            }
        }

        $response = [
            'error_code' => $error_code,
            'error_msg' => $error_msg
        ];

        if ($data !== false) {
            $response["data"] = $data;
        }
        
        return $response;
    }
}

/**
 * 统一分页信息返回格式
 * @param array $total  总条数
 * @param array $limit  每页显示条数
 * @param array $page   当前页
 * @Author   lovenLiu
 * @DateTime 2019-05-09T17:55:58+0800
 */
if(!function_exists('sys_paginate')) {
    function sys_paginate($total = 0, $limit = 20, $page = 1){
        return [
            'total_number' => intval($total),
            'page_total_number' => $limit > 0 ? intval(ceil($total / $limit)) : 0,
            'page_size' => intval($limit),
            'page_current' => intval($page)
        ];
    }
}

/**
 * 分页参数处理
 * @param  [type]  $page    [description]
 * @param  integer $default [description]
 * @return [type]           [description]
 */
if (!function_exists('sys_page_format')) {
    function sys_page_format($page, $default = 1){
        return intval($page) <= 0 ? $default : intval($page);
    }
}

// 二维数组排序
if(!function_exists('sys_array_multisort')) {
    function sys_array_multisort($list, $field, $rule = SORT_ASC) {
        $fieldList = array_column($list, $field);
        array_multisort($fieldList, $rule, $list);
        return $list;
    }
}


if (!function_exists('getSafeCode')) {
    /**
     * 获取随机验证码
     * @param  integer $len [字符数量]
     * @return [type]       [description]
     */
    function get_safe_code($len = 4, $chartype = "ALL")
    {
        // 随机字符
        $alpha = array(
            'ABCDEFGHIJKLMNPQRSTUVWXYZ',
            'abcdefghjkmnpqrstuvwxyz'
        );
        $ch_set = array();
        $lA = strlen($alpha[0]);
        $la = strlen($alpha[1]);
        for ($i = 0; $i < $lA || $i < $la; $i++) {
            if ($i < $lA)
                $ch_set[] = $alpha[0]{$i};
            if ($i < $la)
                $ch_set[] = $alpha[1]{$i};
        }
        switch (strtoupper($chartype)) {
            case 'CHAR':
                break;
            case 'NUMBER':
                $ch_set = str_split('0123456789');
                break;
            default:
                for ($i = 1; $i++ < 9;)
                    $ch_set[] = $i;
        }
        // 生成验证码
        $code = '';
        for ($i = 0, $n = count($ch_set); $i < $len; $i++) {
            $code .= $ch_set[mt_rand() % $n];
        }
        return $code;
    }
}


if(!function_exists('timeFormat')){
    //格式化时间
    function timeFormat($times)
    {
        $limit = time() - $times;
        if ($limit < 600) {
            return '刚刚';
        } elseif ($limit >600 && $limit < 3600) {
            return floor($limit / 60) . '分钟前';
        } elseif ($limit >= 3600 && $limit < 86400) {
            return floor($limit / 3600) . '小时前';
        } elseif ($limit >= 86400 && $limit < 86400 * 3) {
            return floor($limit / 86400).'天前';
        }
        return date('Y-m-d', $times);
    }
}

/**
 * xss安全过滤
 * @param  [type] $val [description]
 * @return [type]      [description]
 */
if(!function_exists('remove_xss')) {
    function remove_xss($val)
    {
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
            $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
            // @ @ 0{0,7} matches '0' zero to seven times
            $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
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
                $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
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
     * 获取配置参数
     */
    if (!function_exists('OP')) {
        function OP($name)
        {
            $opLogic = new \app\common\model\logic\OptionsLogic();
            return $opLogic->getOptionName($name)['option_value'];
        }
    }
    if (!function_exists('timediff')) {
        function timediff($value)
        {
            //计算时间差
            $day = floor($value / 86400);
            $hour = floor($value % 86400 / 3600);
            $min = floor($value % 86400 % 3600 / 60);
            $sec = floor($value % 86400 % 3600 % 60);
            $str = "";
            if (!empty($day)) {
                $str .= $day . "天";
            }

            if (!empty($hour)) {
                $str .= $hour . "时";
            }

            if (!empty($min)) {
                $str .= $min . "分";
            }

            if (!empty($sec)) {
                $str .= $sec . "秒";
            }

            if (empty($str)) {
                $str = '0秒';
            }

            return $str;
        }
    }
}

if (!function_exists('array_only')) {
    /**
     * 获取以为数组中的key
     */
    function array_only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array)$keys));
    }
}

if (!function_exists('arrange_category')) {
    /**
     * 递归非常耗费性能，请勿泛滥使用！！！
     * 递归 类目 将类目整理成树状结构
     * @param array $arr 类目数组
     * @param int $id 根节点ID
     * @param int $level 根节点等级
     * @return array
     */
    function arrange_category($arr, $id = 0, $level = 1)
    {
        $list = array();
        foreach ($arr as $k => $v) {
            if ($v['parent_id'] == $id) {
                $v['level'] = $level;
                $v['children'] = arrange_category($arr, $v['id'], $level + 1);
                $list[] = $v;
            }
        }
        return $list;
    }
}


if (!function_exists('authcode')) {
    /**
     * 加密解密函数
     * @return [type] [description]
     */
    function authcode($str,$operation="DECODE"){
        $auth = new \Util\Authcode();
        $code = $auth->DEcode($str,strtoupper($operation));
        return $code;
    }
}

function telEncrypt($tel)
{
    return md5($tel . config('QZ_YUMING'));
}

if (!function_exists('get_city_by_ip')) {
    function get_city_by_ip($ip = ''){
        if(!empty($ip)){
            $ipSource = new \Ipipdotnet\City();
            return $ipSource->find($ip);
        }
        return false;
    }
}

/**
 * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
 * @param  [string] $user_name 字符串
 * @param  [int] $head      左侧保留位数
 * @param  [int] $foot      右侧保留位数
 * @return string 格式化后的姓名
 */
if (!function_exists('substr_cut')) {
    function substr_cut($user_name,$head,$foot){
        $strlen     = mb_strlen($user_name, 'utf-8');
        $firstStr     = mb_substr($user_name, 0, $head, 'utf-8');
        $lastStr     = mb_substr($user_name, -$foot, $foot, 'utf-8');
        return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - ($head+$foot)) . $lastStr;
    }
}

/**
 * 根据起点坐标和终点坐标测距离
 * @param  [array]   $from  [起点坐标(经纬度),例如:array(118.012951,36.810024)]
 * @param  [array]   $to    [终点坐标(经纬度)]
 * @param  [bool]    $km        是否以公里为单位 false:米 true:公里(千米)
 * @param  [int]     $decimal   精度 保留小数位数
 * @return [string]  距离数值
 */
if (!function_exists('get_distance')) {
    function get_distance($from,$to,$km = true,$decimal = 2){
        sort($from);
        sort($to);
        $EARTH_RADIUS = 6370.996; // 地球半径系数

        $distance = $EARTH_RADIUS*2*asin(sqrt(pow(sin( ($from[0]*pi()/180-$to[0]*pi()/180)/2),2)+cos($from[0]*pi()/180)*cos($to[0]*pi()/180)* pow(sin( ($from[1]*pi()/180-$to[1]*pi()/180)/2),2)))*1000;

        if($km){
            $distance = $distance / 1000;
        }
        return round($distance, $decimal);
    }
}

/**
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

if (!function_exists('read_execl')) {
    /**
     * PHPEXECL导入
     *
     * @param string $file 文件地址
     * @param int $sheet 工作表sheet(传0则获取第一个sheet)
     * @param int $columnCnt 列数(传0则自动获取最大列)
     * @param array $options 操作选项
     *                          array mergeCells 合并单元格数组
     *                          array formula    公式数组
     *                          array format     单元格格式数组
     *
     * @return array
     * @throws Exception
     */
    function read_execl($file = '', $sheet = 0, $columnCnt = 0, &$options = [],$row_index = 2)
    {
        try {
            /* 转码 */
            $file = iconv("utf-8", "gb2312", $file);

            if (empty($file) OR !file_exists($file)) {
                throw new \Exception('请选择导入文件');
            }

            //Xlsx $objRead
            $objRead = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            if (!$objRead->canRead($file)) {
                //Xls $objRead
                $objRead = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xls');
                if (!$objRead->canRead($file)) {
                    throw new \Exception('请导入Excel文件');
                }
            }

            /* 如果不需要获取特殊操作，则只读内容，可以大幅度提升读取Excel效率 */
            empty($options) && $objRead->setReadDataOnly(true);
            /* 建立excel对象 */
            $obj = $objRead->load($file);

            /* 获取指定的sheet表 */
            $currSheet = $obj->getSheet($sheet);
            if (isset($options['mergeCells'])) {
                /* 读取合并行列 */
                $options['mergeCells'] = $currSheet->getMergeCells();
            }

            /* 取得最大的列号 */
            $columnH = $currSheet->getHighestColumn();
            /* 兼容原逻辑，循环时使用的是小于等于 */
            $columnMax = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($columnH);
            if (0 == $columnCnt) {
                $columnCnt = $columnMax;
            } else {
                if($columnMax != $columnCnt) {
                    throw new \Exception('导入文件与模板格式不一致');
                }
            }

            /* 获取总行数 */
            $rowCnt = $currSheet->getHighestRow();
            $data = [];

            /* 读取内容，从第二行读取真实数据 */
            for ($_row = $row_index; $_row <= $rowCnt; $_row++) {
                $isNull = true;
                for ($_column = 1; $_column <= $columnCnt; $_column++) {
                    $cellName = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($_column);
                    $cellId = $cellName . $_row;
                    $cell = $currSheet->getCell($cellId);
                    if (isset($options['format'])) {
                        /* 获取格式 */
                        $format = $cell->getStyle()->getNumberFormat()->getFormatCode();
                        /* 记录格式 */
                        $options['format'][$_row][$cellName] = $format;
                    }
                    if (isset($options['formula'])) {
                        /* 获取公式，公式均为=号开头数据 */
                        $formula = $currSheet->getCell($cellId)->getValue();

                        if (0 === strpos($formula, '=')) {
                            $options['formula'][$cellName . $_row] = $formula;
                        }
                    }

                    if (isset($format) && 'm/d/yyyy' == $format) {
                        /* 日期格式翻转处理 */
                        $cell->getStyle()->getNumberFormat()->setFormatCode('yyyy/mm/dd');
                    }
                    $data[$_row - 2][$cellName] = trim($currSheet->getCell($cellId)->getFormattedValue());

                    if (!empty($data[$_row - 2][$cellName])) {
                        $isNull = false;
                    }
                }
                /* 判断是否整行数据为空，是的话删除该行数据 */
                if ($isNull) {
                    unset($data[$_row - 2]);
                }
            }
            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

/**
 * 验证变量
 * @param $variate
 * @param string $default
 * @return string
 */
if (!function_exists('check_variate')) {
    function check_variate(&$variate, $default = '')
    {
        if (!isset($variate) || empty($variate)) {
            $variate = $default;
        }
        
        return $variate;
    }
}

if (!function_exists('curl')){
    /**
     * curl请求函数封装
     * @param $curllink
     * @param null $data
     * @param int $type
     * @return mixed
     */
    function curl($curllink, $data = [],$headers = null)
    {
        //初始化一个curl对象
        $ch = curl_init();
        //设置需要抓取的url
        curl_setopt($ch, CURLOPT_URL, $curllink);
        //跳过SSL证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //连接超时15秒
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        //超时设置60秒
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
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
 * 获取10位不同字符
 * @param int $length
 * @return string
 */
if (!function_exists('randomkeys')) {
    function randomkeys()
    {
        $card_no = encodeID(time());
        $card_pre = '';
        $card_vc = substr(md5($card_pre . $card_no), 0, 2);
        $card_vc = strtoupper($card_vc);
        return $card_pre . $card_no . $card_vc;
    }
}

function encodeID($int, $format = 8)
{
    $dics = [
        0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8', 9 => '9',
        10 => 'A', 11 => 'B', 12 => 'C', 13 => 'D', 14 => 'E', 15 => 'F', 16 => 'G', 17 => 'H', 18 => 'I',
        19 => 'J', 20 => 'K', 21 => 'L', 22 => 'M', 23 => 'N', 24 => 'O', 25 => 'P', 26 => 'Q', 27 => 'R',
        28 => 'S', 29 => 'T', 30 => 'U', 31 => 'V', 32 => 'W', 33 => 'X', 34 => 'Y', 35 => 'Z'
    ];
    $dnum = 36; //进制数
    $arr = array();
    $loop = true;
    while ($loop) {
        $arr[] = $dics[bcmod($int, $dnum)];
        $int = bcdiv($int, $dnum, 0);

        if ($int == '0') {
            $loop = false;
        }
    }

    if (count($arr) < $format)
        $arr = array_pad($arr, $format, $dics[array_rand($dics)]);

    return implode('', array_reverse($arr));
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $custom 是否使用进行自定义,使用取值自定义配置的方式否则为直接取REMOTE_ADDR
 * @return mixed
 */
function get_client_ip($type = 0,$custom = true) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($custom){
        //IP获取函数get_client_ip()取值顺序自定义配置 从配置文件读取
        //样例 HTTP_CF_CONNECTING_IP,HTTP_X_REAL_IP,HTTP_X_FORWARDED_FOR,HTTP_CLIENT_IP,REMOTE_ADDR
        $GET_IP_VARS_ORDER_ARR = explode(',', config('GET_IP_VARS_ORDER'));
        $GET_IP_VARS_ORDER_ARR = array_filter($GET_IP_VARS_ORDER_ARR);
        if (empty($GET_IP_VARS_ORDER_ARR)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        foreach ($GET_IP_VARS_ORDER_ARR as $ik => $iv) {
            if (isset($_SERVER[$iv])) {
                $ip = $_SERVER[$iv];
                //HTTP_X_FORWARDED_FOR包含多个IP需要单独处理
                if ('HTTP_X_FORWARDED_FOR' == $iv) {
                    $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                    $pos    =   array_search('unknown',$arr);
                    if(false !== $pos) unset($arr[$pos]);
                    $ip     =   trim($arr[0]);
                }
                break;
            }
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}


/**
 * 图片地址拼接判断 如果有完整地址了则不拼接
 */
if(!function_exists('changeImgUrl')) {
    function changeImgUrl($url,$type = 1)
    {
        if(empty($url)){
            return '';
        }
        $preg = "/^http(s)?:\/\/.+/";
        if (preg_match($preg, $url)) {
            return $url;
        }
        if (strpos($url, "//") === 0) {
            return "https:". $url;
        }
        if($type == 1){
            return config('app.QINIU_HOST') . '/' .$url;
        }else{
            return config('app.qiniu.domain') . '/' .$url;
        }
    }
}

// 获取两个日期之间的天数
if(!function_exists('getTwoDateDays')) {
    function getTwoDateDays($date_begin, $date_end) {
        $time_begin = strtotime($date_begin);
        $time_end = strtotime($date_end);

        $time_diff = abs($time_end - $time_begin);
        $days = ceil($time_diff / 86400) + 1;
        return $days;
    }
}


//二维数组排序
function multi_array_sort($multi_array,$sort_key,$sort=SORT_ASC){
    $key_array = [];
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