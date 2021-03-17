<?php

//取城市信息
//本函数限制使用,如需使用需要得到技术部最高负责人的使用授权
use Think\Verify;

function getCityArray()
{
    return D("Common/Quyu")->getCityArray();
}

//检测关键词
function checkKeyword($keyword)
{
    $isVerify = str_ireplace(array(
        '.com', '.net', '.cn', '.org', '.me', '.cc', '.in', '.asia', '.pw', '.biz', '.so', '.tv', '.cm', '.co',
        '.co.uk', '.la', '.info', '.at', '.de,', '.ch', '.us', '.be', '.de', '.es', '.name', '.tw', '.xyz',
        '.club', '.top', '.wang'), '#', $keyword);
    if (stripos($isVerify, '#') !== false || $isVerify == '#') {
        return false;
    }
    return ture;
}

/**
 * [addImgExt 为图片添加后缀名,默认后缀为b.jpg]
 * @param [type] $value  [图片路径]
 * @param string $extend [图片后缀]
 */
function addImgExt($value, $extend = 'b.jpg')
{
    if (empty($value)) {
        return '';
    }
    $path = $value;
    $ext = array('.jpg', '.png', '.gif', '.jpeg');
    $path = str_ireplace($ext, '#ext#', $path);
    if (false === strpos($path, '#ext#')) {
        $value = $value . '-' . $extend;
    }
    return $value;
}

//取外链模块数据
function getInLink($update = false)
{
    $link = S('Cache:InLinkModule');
    if (empty($link) || $update == true) {
        //获取最新的业主点评装修公司
        $link["comments"] = D("Common/Comment")->getNewComment(5);
        foreach ($link["comments"] as $key => $value) {
            $link["comments"][$key]['text'] = mbstr($link["comments"][$key]['text'], 0, 30, "utf-8", false);
        }

        //装修公司排行榜
        $link["company"] = D("Common/User")->getQiandanList(5);

        //推荐百科
        $link["baike"] = D("Common/Baike")->getTopBaike(25);
        foreach ($link["baike"] as $key => $value) {
            $link["baike"][$key]['description'] = mbstr($link["baike"][$key]['description'], 0, 25, "utf-8", false);
        }


        //获取最新标签
        $link['tags'] = [];
        $taggonglue = M('Tags')->field('id,name')
            ->where(['article_count' => ['GT', 0], 'istop' => '1'])
            ->order('time DESC')
            ->limit('0,4')
            ->select();
        foreach ($taggonglue as $key => $value) {
            $value['type_url'] = 'gonglue';
            $link['tags'][] = $value;
        }
        $tagmeitu = M('Tags')->field('id,name')
            ->where(['meitu_count' => ['GT', 0], 'istop' => '1'])
            ->order('time DESC')
            ->limit('0,3')
            ->select();
        foreach ($tagmeitu as $key => $value) {
            $value['type_url'] = 'meitu';
            $link['tags'][] = $value;
        }
        $tagwenda = M('Tags')->field('id,name')
            ->where(['ask_count' => ['GT', 0], 'istop' => '1'])
            ->order('time DESC')
            ->limit('0,2')
            ->select();
        foreach ($tagwenda as $key => $value) {
            $value['type_url'] = 'wenda';
            $link['tags'][] = $value;
        }

        //取热门装修日记
        $link["diary"] = D("Common/Diary")->getHotDiary(24);

        //装修必知
        $link['gonglue'] = D('Common/Article')->getArticle(36);

        //新增回答
        $link['newAnwser'] = D("Common/Ask")->getNewAnwsers(15);
        foreach ($link["newAnwser"] as $key => $value) {
            $link["newAnwser"][$key]['content'] = mbstr($link["newAnwser"][$key]['content'], 0, 30, "utf-8", false);
        }

        //无人回答
        $link['noAnwser'] = D("Common/Ask")->getQuestionList(array("adopt_time" => array("EQ", '0')), 'rand()', 0, 36);

        //装修效果图
        $link['meitu'] = D("Common/Meitu")->getNewMeitu(24);

        //更新缓存
        S("Cache:InLinkModule", $link, 3600);
    }

    shuffle($link["baike"]);
    $link["baike"] = array_slice($link["baike"], 0, 5);

    //shuffle($link["tags"]);
    //$link["tags"] = array_slice($link["tags"],0,9);

    shuffle($link["diary"]);
    $link["diary"] = array_slice($link["diary"], 0, 5);

    shuffle($link["gonglue"]);
    $link["gonglue"] = array_slice($link["gonglue"], 0, 12);

    shuffle($link["newAnwser"]);
    $link["newAnwser"] = array_slice($link["newAnwser"], 0, 3);

    shuffle($link["noAnwser"]);
    $link["noAnwser"] = array_slice($link["noAnwser"], 0, 12);

    shuffle($link["meitu"]);
    $link["meitu"] = array_slice($link["meitu"], 0, 8);

    //$tagsType = array(1=>'gonglue',2=>'meitu',3=>'riji',4=>'wenda',5=>'fzgonglue',6=>'baike');

    return $link;
}

/**
 * 发布数量
 * @param  [type] $type [发布类型]
 * @return [type]       [description]
 */
function releaseCount($type, $cs = '')
{

    // 修改记录
    /*
     * p.2.14.12 PC端发单入口优化 新逻辑 2018年9月15日
     *
     *
    */

    $releaseCount = S('Cache:releaseCount:' . $type . ':' . $cs); //定义缓存取值
    if ($releaseCount) { //判断是否存在
        return $releaseCount; //返回
    }

    $timeBase = 1397193573; //基数时时间戳
    $timeNow = time(); //现在时间
    // $hour = date("H");//当前时间

    // //定义发布人数缓存
    // $fbrs = S("Cache:".date("Y-m-d")."fbrs");
    // $fbzrs = S("Cache:".date("Y-m")."fbzrs");
    // if(!$fbrs){
    //   //如果缓存不存在,查询options表的数据

    //   $fbrs = 0;
    //   //清除上一天的发布人数
    //   S("Cache:".date("Y-m-d",strtotime("-1 day"))."fbrs",null);
    // }

    // //定义发布的总人数
    // if(!$fbzrs){
    //     $fbzrs = 0;
    //     //清除上一个月的总发布人数
    //     S("Cache:".date("Y-m",strtotime("-1 month"))."fbzrs",null);
    // }

    // import('Library.Org.Util.App');
    // $app = new \App();
    // //设置概率数组
    // $arr_fbrs = range(1,100);
    // //打乱数组排序
    // $arr_fbrs =  $app->rand_array($arr_fbrs);
    //如果没有该类型,返回0
    switch ($type) {
        case 'company':
            $rsNow = 51489; //注册装修公司数量
            $count = D("Common/User")->getRegisterCount(3, $cs);
            if (!empty($cs)) {
                $rsNow = $rsNow / 10;
            }
            $rsNow += $count;
            $rsNow += 75341 - 65972;      //修正装修公司数量
            break;
        case 'rel_company':
            //真实注册装修公司数量
            $count = D("Common/User")->getRegisterCount(3, $cs);
            $rsNow = $count;
            break;
        case 'designer':
            $rsNow = 598673;//注册设计师人数
            $count = D("Common/User")->getRegisterCount(2, $cs);
            if (!empty($cs)) {
                $rsNow = $rsNow / 10;
            }
            $rsNow += $count;
            $rsNow += 1025006 - 623221;   //修正设计师数量
            break;
        case 'user':
            $rsNow = 4809156; //注册的业主
            $count = D("Common/User")->getRegisterCount(1, $cs);
            if (!empty($cs)) {
                $rsNow = $rsNow / 10;
            }
            $rsNow += $count;
            $rsNow += 20132142 - 4847807; //修正注册的业主数量
            break;
        case 'rel_user':
            //取真实的 注册用户数
            $count = D("Common/User")->getRegisterCount(1, $cs);
            $rsNow = $count;
            break;
        case 'comment':
            $rsNow = 4562148; //业主点评
            $count = D("Common/Comment")->getCommentCount($cs);
            if (!empty($cs)) {
                $rsNow = $rsNow / 10;
            }
            $rsNow += $count;
            $rsNow += 10590006 - 4607184;  //修正业主点评数量
            break;
        case 'cases':
            $rsNow = 1506101; //装修案例数量  现有案例数
            $count = D("Common/Cases")->getCasesCount($cs);
            if (!empty($cs)) {
                $rsNow = $rsNow / 10;
            }
            $rsNow += $count;
            $rsNow += 3025830 - 1871218;  //修正案例数量
            break;
        case 'dbfdnum':
            //p.2.16.8  PC端-底部发单入口  随机数
            $rsNow = mt_rand(1000,2000);
            break;
        case 'caseimgs':
            $rsNow = 1912841; //装修效果图数量
            $count = D("Common/Cases")->getIndexCaseImagesTotal($cs);
            if (!empty($cs)) {
                $rsNow = $rsNow / 10;
            }
            $rsNow += $count;
            $rsNow += 6520684 - 3761185;  //修正装修效果图
            break;
        case 'fbrs':
            //当前发布订单人数
            //判断当前时间段
            // mt_srand(microtime(true) * 1000);
            // $rand = rand(1,100);
            // $h = 0;
            // if($hour >= 0  && $hour < 10){
            //     //凌晨0-6点
            //     if($arr_fbrs[$rand] < 10){
            //         ++$fbrs;
            //         $fbzrs ++;
            //     }
            // }elseif($hour >= 10 && $hour < 22){
            //     //早晨10点至晚间22点
            //     if($arr_fbrs[$rand] < 30){
            //         ++$fbrs;
            //         $fbzrs ++;
            //     }
            //     //将356的基数平均分摊到10点到22点内
            //     $arr = range(10,22);
            //     $arr = array_flip($arr);
            //     $h = ceil(733/13*($arr[$hour]+1));
            //     $fbrs = $h+$fbrs;
            // }elseif($hour >= 22 && $hour < 0){
            //     //晚上22点至0点
            //     if($arr_fbrs[$rand] < 20){
            //         ++$fbrs;
            //         $fbzrs ++;
            //     }
            // }
            // $rsNow = $fbrs;
            // S("Cache:".date("Y-m-d")."fbrs",$fbrs-$h);
            $timeBase = strtotime(date('Y-m-d', time() . " 00:00:00")) - 1;
            $rsNow = $rsBase[$type] + (($timeNow - $timeBase) / (60 * 2.5));
            break;
        case 'fbsyrs':
            // 今日剩余人数
            // 今日还剩x个免费名额
            $timeBase = strtotime(date('Y-m-d', time() . " 00:00:00")) - 1;
            $rsNow = 600 - (($timeNow - $timeBase) / (60 * 5));
            if ($rsNow < 300) {
                $rsNow = 301;
            }
            if ($rsNow > 600) {
                $rsNow = 599;
            }
            break;
        case 'zbsjs':
            //当前发布设计需求数量
            $start = 200;
            $end = 500;
            $rsNow = rand($start, $end);// $s 为返回1到15之间的随机数
            break;
        case 'fbzrs':
            // 发布总人数
            // 累计帮助过x位业主
            $baseFbzrsNum = 1201896; //基础数
            $daysNum      = 0; //天数累计
            $todayNum     = 0; //当天数

            $startTimeDay = '2018-09-15';
            $endTimeDay   = date('Y-m-d', time());
            $diffDay      = ceil((strtotime($endTimeDay)-strtotime($startTimeDay))/86400);
            $daysNum      = $diffDay * 1999;

            $todayMax = 0;
            if ((date('d',time()) % 2) == 0) {
                $todayMax = 2688;
            } else {
                $todayMax = 1999;
            }

            $timeBase     = strtotime(date('Y-m-d', time() . " 00:00:00")) - 1;
            $todayNum     = ceil( ( $todayMax / 96  )  * (($timeNow - $timeBase) / (60 * 15)) );
            //dump($todayNum);

            $rsNow        = $baseFbzrsNum + $daysNum + $todayNum;
            break;
        case 'zbrs':
            $timeBase = strtotime(date('Y-m-d', time() . " 00:00:00")) - 1;
            $fbrs = 409561 + (($timeNow - $timeBase) / (60 * 5));
            $rsNow = $fbrs;
            break;
        default:
            return 0;
            break;
    }
    $rsNow = ceil($rsNow);
    S('Cache:releaseCount:' . $type . ':' . $cs, $rsNow, 3600);
    return $rsNow;
}

/**
 * 加密解密函数
 * @return [type] [description]
 */
function authcode($str, $operation = "DECODE")
{
    import('Library.Org.Util.Authcode');
    $auth = new \Authcode();
    $code = $auth->DEcode($str, strtoupper($operation));
    return $code;
}

/**
 * 中文字符串截取
 * @param  [type]  $str     [description]
 * @param  integer $start [description]
 * @param  [type]  $length  [description]
 * @param  string $charset [description]
 * @param  boolean $suffix [description]
 * @return [type]           [description]
 */
function mbstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{
    if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    $fix = '';
    if (strlen($slice) < strlen($str)) {
        $fix = '...';
    }
    return $suffix ? $slice . $fix : $slice;
}

/**
 * 中英文混合文字符串截取
 * @param  [type]  $str     [description]
 * @param  integer $start [description]
 * @param  [type]  $length  [description]
 * @param  string $charset [description]
 * @param  boolean $suffix [description]
 * @return [type]           [description]
 */
function mbestr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{
    $fix = '';
    if ($suffix) {
        $fix = '...';
    }
    // 只有宽度大于10才截取
    if(mb_strwidth($str, 'utf8')>$length){
        $str = mb_strimwidth($str, $start, $length, $fix, $charset);
    }

    return $str;
}

/**
 * xss安全过滤
 * @param  [type] $val [description]
 * @return [type]      [description]
 */
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
 * 获取验证码
 * @param  string $id [验证码标识信息]
 * @param  integer $length [验证码长度]
 * @param  integer $width [图片宽度]
 * @param  integer $height [图片高度]
 * @return [type]          [description]
 */
function getVerify($id = '', $length = 4, $width = 0, $height = 0, $fontSize = 19, $useNoise = true, $useImgBg = false, $useCurve = true)
{
    $code = substr(md5(time()), 0, 20);

    $fonts = array(
        0 => array('useCurve' => true, 'size' => 19, 'font' => 'Ding-DongDaddyO.ttf'),
        1 => array('useCurve' => false, 'size' => 19, 'font' => 'StayPuft.ttf'),
        2 => array('useCurve' => true, 'size' => 20, 'font' => 'TimesNewRomanBold.ttf'),
        3 => array('useCurve' => true, 'size' => 19, 'font' => 'VeraSansBold.ttf'),
        4 => array('useCurve' => true, 'size' => 19, 'font' => '2.ttf'),
        5 => array('useCurve' => true, 'size' => 19, 'font' => '4.ttf'),
        6 => array('useCurve' => true, 'size' => 17, 'font' => '5.ttf'),
        7 => array('useCurve' => true, 'size' => 19, 'font' => 'Candice.ttf'),
        8 => array('useCurve' => true, 'size' => 19, 'font' => 'Jura.ttf'),
        9 => array('useCurve' => true, 'size' => 19, 'font' => '6.ttf'),
    );
    $rand = mt_rand(0, 6);
    //$rand = 9;

    //验证码配置
    $config = array(
        // 验证码字体大小
        'fontSize' => $fonts[$rand]['size'],
        // 验证码位数
        'length' => $length,
        // 关闭验证码杂点
        'useNoise' => $useNoise,
        //切换背景图片
        'useImgBg' => $useImgBg,
        //选择字体
        'fontttf' => $fonts[$rand]['font'],
        'imageW' => $width,
        'imageH' => $height,
        'codeSet' => $code,
        //是否使用混淆曲线 默认为true
        'useCurve' => $fonts[$rand]['useCurve'],
        'bg' => array(243, 251, 254)
    );
    $Verify = new Verify($config);
    $Verify->entry($id);
}

// 检测输入的验证码是否正确，$code为用户输入的验证码字符串
function check_verify($code, $id = '')
{
    $verify = new Verify();
    return $verify->check($code, $id);
}

function OP($key, $status = 'yes')
{
    if (empty($key)) {
        return false;
    }

    static $OPKeysList = array();
    $Options = D("Common/Options");

    if (isset($OPKeysList[$key])) {
        return $OPKeysList[$key];
    } else {
        $value = $Options->getOptionName($key, $status);
        $OPKeysList[$key] = $value['option_value'];
        return $OPKeysList[$key];
    }
}

function getSafeCode()
{
    import('Library.Org.Util.App');
    $app = new \App();
    $code = substr(md5(time()), 0, 10);
    $safeKey = $app->getSafeCode(10, "CHAR");
    $safecode = $code;
    $_SESSION[$safeKey] = $code;
    // $ssid = authcode(session_id(),"");
    return array("safecode" => $safecode, "safekey" => $safeKey, "ssid" => $ssid);
}

/**
 * 判断是否是本站上传的图片
 * @param  [type] $str [description]
 * @return [type]      [description]
 */
function check_imgPath($path)
{
    $path = remove_xss($path);
    //判断是否包含HTTP
    if (strstr($path, "http:") === false) {
        $path = str_replace("//", "http://", $path);
    }
    $pathUrl = parse_url($path);
    //判断QQ头像，微信头像，新浪头像
    if ((strstr(strtolower($pathUrl["host"]), "qizuang") === false) && (strstr(strtolower($pathUrl["host"]), "wx.qlogo.cn") === false) && (strstr(strtolower($pathUrl["host"]), "q.qlogo.cn") === false) && (strstr(strtolower($pathUrl["host"]), "tp1.sinaimg.cn") === false)) {
        return false;
    }
    return true;
}


//格式化时间
function timeFormat($times)
{
    $limit = time() - $times;
    if ($limit < 60) {
        return '刚刚';
    } elseif ($limit < 3600) {
        return floor($limit / 60) . '分钟前';
    } elseif ($limit >= 3600 && $limit < 86400) {
        return floor($limit / 3600) . '小时前';
    } elseif ($limit >= 86400 && $limit < 86400 * 2) {
        return '1天前';
    }
    return date('Y-m-d', $times);
}

//二维数组排序
function multi_array_sort($multi_array, $sort_key, $sort = SORT_ASC)
{
    if (is_array($multi_array)) {
        foreach ($multi_array as $row_array) {
            if (is_array($row_array)) {
                $key_array[] = $row_array[$sort_key];
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
    array_multisort($key_array, $sort, $multi_array);
    return $multi_array;
}

//HTML转为纯文本
function htmlToText($str, $len = 0)
{
    $str = htmlspecialchars(strip_tags($str));
    $str = preg_replace("@\s@is", '', $str);
    $str = str_replace('　', '', $str);
    if ($len > 1) {
        return mbstr($str, 0, $len);
    }
    return $str;
}

/**
 *  转换xml到数组 利用转换成json做中间环节
 * @param  xml文档 $xml
 * @return bool false or array
 */
function xmltoarray($xml)
{
    if (empty($xml)) {
        return false;
    }
    return json_decode(json_encode((array)simplexml_load_string($xml)), true);
}

//老版本判断手机号函数 已经废除  暂时保留新函数稳定后删除
//function ismobile() {
//    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
//    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
//        return true;
//
//    //此条摘自TPM智能切换模板引擎，适合TPM开发
//    if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
//        return true;
//    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
//    if (isset ($_SERVER['HTTP_VIA']))
//        //找不到为flase,否则为true
//        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
//    //判断手机发送的客户端标志,兼容性有待提高
//    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
//        $clientkeywords = array(
//            'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
//        );
//        //从HTTP_USER_AGENT中查找手机浏览器的关键字
//        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
//            return true;
//        }
//    }
//    //协议法，因为有可能不准确，放到最后判断
//    if (isset ($_SERVER['HTTP_ACCEPT'])) {
//        // 如果只支持wml并且不支持html那一定是移动设备
//        // 如果支持wml和html但是wml在html之前则是移动设备
//        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
//            return true;
//        }
//    }
//    return false;
//}

/**
 * 引用composer组件，由于框架不依赖composer 该类手动升级
 * 来源 https://github.com/serbanghita/Mobile-Detect
 * author mcj
 * @return bool
 */
//todo 后期封装为单例
function ismobile()
{
    import('Library.Org.Tool.Mobile_Detect', "", ".php");
    $detect = new Mobile_Detect;
    return $detect->isMobile();
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
    if (!in_array($data['type'], array('yzm', 'password', 'fborder_work',
        'fborder_rest', 'fborder', 'toyz_wjt', 'toyz_fpgs', 'toyz_fpgs2', 'toyz_fpgs1',
        'nil'))) {
        //如果传入的发送短信类型未知,那么设置为nil
        $data['type'] = 'nil';
    }

    //处理短信发送通道
    if (!in_array($data['sms_channel'], array('yuntongxun', 'ihuyi', 'yunrongt', "yunrongyx"))) {
        //如果传入的通道是未知的 那么使用全局配置
        $data['sms_channel'] = OP('sms_channel', 'yes') ?: 'yuntongxun';
    }

    import('Library.Org.Util.App');
    $app = new \App();

    $smsMessageSid = '';
    $dateCreated = '';
    $remark = '';
    $status = 0;

    //$data['sms_channel'] = 'yuntongxun'; //调试用 线上生产模式必须是注释状态

    //判断短信通道并发送短信
    switch ($data['sms_channel']) {
        case 'yuntongxun':
            //短信通道为 容联云通讯
            import('Library.Org.yuntongxun.Telytx'); //引入类
            $Telytx = new \Telytx(); //实例化

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
            $result = $Telytx->sendTemplateSMS($data['tel'], $data['variable'], $data['tplid']); //发送
            $smsMessageSid = $result["smsMessageSid"];
            $dateCreated = $result["dateCreated"];
            $remark = $result["msg"];
            $reArr['data'] = $result;  //接口结果信息
            $reArr['errcode'] = 1; //错误码, 0为无错误成功
            $reArr['errmsg'] = '调用失败!';  //错误信息
            if ($result["status"] == 1) {
                $reArr['errcode'] = 0; //错误码, 0为无错误成功
                $reArr['errmsg'] = '成功!';  //错误信息

                $status = 1;
            }
            break;
        case 'ihuyi':

            switch ($data['type']) {
                case 'yzm':
                    $data['tpl'] = OP('sms_ihuyi_56869');
                    //做发送短信内容
                    $tpls = str_replace("【变量】", "%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0], $data['variable'][1]); // 做模版
                    break;
                case 'password':
                    $data['tpl'] = OP('sms_ihuyi_141830');
                    //做发送短信内容
                    $tpls = str_replace("【变量】", "%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
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
                    $tpls = str_replace("【变量】", "%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0]); // 做模版
                    break;
                case 'toyz_wjt':
                    $data['tpl'] = OP('sms_ihuyi_141828');
                    //做发送短信内容
                    $tpls = str_replace("【变量】", "%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0]); // 做模版
                    break;
                case 'toyz_fpgs':
                    $data['tpl'] = OP('sms_ihuyi_119008');
                    //做发送短信内容
                    $tpls = str_replace("【变量】", "%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
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
            $result = xmltoarray($app->SmsSend($data['tel'], $data['tpld'])); //发送
            $smsMessageSid = $result["smsid"];
            $remark = $result["msg"];
            $reArr['data'] = $result;  //接口结果信息
            $reArr['errcode'] = 1; //错误码, 0为无错误成功
            $reArr['errmsg'] = '调用失败!';  //错误信息
            if ($result["code"] == 2) {
                $reArr['errcode'] = 0; //错误码, 0为无错误成功
                $reArr['errmsg'] = '成功!';  //错误信息

                $status = 1;
            }
            break;
        case 'yunrongt':
            import('Library.Org.Util.Yunrongt');
            $Yunrongt = new \Yunrongt();

            switch ($data['type']) {
                case 'yzm':
                    $data['tpl'] = OP('yunrongt_tpl_yzm');
                    //做发送短信内容
                    $tpls = str_replace("【变量】", "%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0], $data['variable'][1]); // 做模版
                    break;
                case 'password':
                    $data['tpl'] = OP('yunrongt_tpl_password');
                    //做发送短信内容
                    $tpls = str_replace("【变量】", "%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0]); // 做模版
                    break;
                case 'fborder_work':
                    $data['tpl'] = OP('yunrongt_tpl_fborder_work');
                    //做发送短信内容
                    $tpls = str_replace("【变量】", "%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0], $data['variable'][1]); // 做模版
                    break;
                case 'fborder_rest':
                    $data['tpl'] = OP('yunrongt_tpl_fborder_rest');
                    //做发送短信内容
                    $tpls = str_replace("【变量】", "%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0], $data['variable'][1]); // 做模版
                    break;
                case 'fborder':
//                    $data['tpl'] = OP('yunrongt_tpl_fborder');
                    //做发送短信内容
//                    $tpls = str_replace("【变量】", "%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
//                    $data['tpld'] = sprintf($tpls, $data['variable'][0]); // 做模版
                    $data['tpld'] = OP('yunrongt_tpl_fborder');
                    break;
                case 'toyz_wjt':
                    $data['tpl'] = OP('yunrongt_tpl_wjt');
                    //做发送短信内容
                    $tpls = str_replace("【变量】", "%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0]); // 做模版
                    break;
                case 'toyz_fpgs':
                    $data['tpl'] = OP('yunrongt_tpl_fpgs');
                    //做发送短信内容
                    $tpls = str_replace("【变量】", "%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0], $data['variable'][1]); // 做模版
                    break;
                case 'toyz_fpgs2':
                    $data['tpl'] = OP('yunrongt_tpl_fpgs2');
                    //做发送短信内容
                    $tpls = str_replace("【变量】", "%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0], $data['variable'][1], $data['variable'][2],
                        $data['variable'][3], $data['variable'][4], $data['variable'][5]); // 做模版
                    break;
                case 'toyz_fpgs1':
                    $data['tpl'] = OP('yunrongt_tpl_fpgs1');
                    //做发送短信内容
                    $tpls = str_replace("【变量】", "%s", $data['tpl']); //取短信模版并把 【变量】 替换成 s%
                    $data['tpld'] = sprintf($tpls, $data['variable'][0], $data['variable'][1], $data['variable'][2]); // 做模版
                    break;
                case 'nil':
                    //做发送短信内容
                    $data['tpld'] = $data['tpl']; // 直接发送传入的模版
                    break;
            }

            //做提交信息
//            $smsdata['cmd'] = 'sendMessage'; //单条发送短信
            $smsdata['mobile'] = $data['tel']; //手机号码
            $smsdata['content'] = $data['tpld']; //短信内容
            $result = $Yunrongt->sendMessage($smsdata);
            // dump($smsdata); //调试用,线上生产环境必须是注释状态

            $reArr['data'] = $result;  //接口结果信息
            $reArr['errcode'] = 1; //错误码, 0为无错误成功
            $reArr['errmsg'] = '调用失败!';  //错误信息
            if ($result["errcode"] > 0) {
                $reArr['errcode'] = 0; //错误码, 0为无错误成功
                $reArr['errmsg'] = '成功!';  //错误信息

                $status = 1;
            }
            break;
        case 'yunrongyx':
            import('Library.Org.Util.Yunrongt');
            $Yunrongt = new \Yunrongt();
            switch ($data['type']) {
                case 'nil':
                    //做发送短信内容
                    $data['tpld'] = $data['tpl']; // 直接发送传入的模版
                    break;
            }
//            $smsdata['cmd'] = 'sendMessage'; //单条发送短信
            $smsdata['mobile'] = $data['tel']; //手机号码
            $smsdata['content'] = $data['tpld']; //短信内容
            $result = $Yunrongt->sendYxMessage($smsdata);

            $reArr['data'] = $result;  //接口结果信息
            $reArr['errcode'] = 1; //错误码, 0为无错误成功
            $reArr['errmsg'] = '调用失败!';  //错误信息
            if ($result["errcode"] > 0) {
                $reArr['errcode'] = 0; //错误码, 0为无错误成功
                $reArr['errmsg'] = '成功!';  //错误信息

                $status = 1;
            }
            break;
    }

    $tel_encrypt = substr_replace($data['tel'], "*****", 3, 5); //做一个中间为星号的号码
    $tel_md5 = $app->order_tel_encrypt($data['tel']); //做一个加密后的号码
    $time = time(); //取当前时间

    //做一个日志
    $smslog = array(
        "cid" => $_SESSION["cityId"] ?: 0,
        "ip" => $app->get_client_ip(),
        "tel" => $tel_encrypt, //为了隐私记录打引号的
        "tel_encrypt" => $tel_md5, //记录 电话号码加密 为了便于查找
        "smsMessageSid" => $smsMessageSid,
        "dateCreated" => $dateCreated,
        "remark" => $remark . ";" . $data['type'],
        "addtime" => $time,
        "sms_channel" => $data['sms_channel'],
        "status" => $status
    );
    M("log_sms_user_send")->add($smslog); //写日志

    return $reArr;
}

//获取Referer
function getReferer()
{
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : getenv('HTTP_REFERER');
    if (empty($referer) && isset($GLOBALS['_SERVER']['HTTP_REFERER'])) {
        $referer = '';
    }
    return $referer;
}

//格式化时间-最大值到三个月
//传入参数 $times 发布时间
//返回值 <60 -> 刚刚   ， <3600
function timeFormatToMouth($times)
{
    $limit = time() - $times;
    if ($limit < 60) {
        return '刚刚';
    } elseif ($limit < 3600) {
        return floor($limit / 60) . '分钟前';
    } elseif ($limit >= 3600 && $limit < 86400) {
        return floor($limit / 3600) . '小时前';
    } elseif ($limit >= 86400 && $limit < 86400 * 30) {
        return floor($limit / 86400) . '天前';
    } elseif ($limit >= 86400 * 30 && $limit < 86400 * 30 * 3) {
        return floor($limit / (86400 * 30)) . '个月前';
    } elseif ($limit >= 86400 * 30 * 3) {
        return '3个月前';
    }
    return date('Y-m-d', $times);
}

/**
 *  判断用户是否封禁
 * @param  bool $isReturn 是否需要返回布尔值
 * @return mixed | bool
 */
function isBlocked($isReturn = true)
{
    $uid = $_SESSION["u_userInfo"]['id'];
    $block = $_SESSION["u_userInfo"]['blocked'];
    //如果正在禁封状态
    if ($block >= time()) {
        session('blocked', '您的帐号已经被系统禁止发言，解禁时间：' . date('Y-m-d H:i:s', $block));
    } else {
        if ($block == '1') {
            session('blocked', '您的帐号已经被系统永久封禁！');
        } else {
            //实时检查是否封禁
            $userInfo = M('user')->field('blocked')->where(array('id' => $uid))->find();
            if ($userInfo['blocked'] > time()) {
                session('blocked', '您的帐号已经被系统禁止发言，解禁时间：' . date('Y-m-d H:i:s', $block));
            } else {
                //更新封禁时间
                if ($userInfo['blocked'] > 10) {
                    $tempdata['blocked'] = '0';
                    M("user")->where(array('id' => $uid))->save($tempdata);
                    $_SESSION["u_userInfo"]['blocked'] = 0;
                }
                return true;
            }
        }
    }
    if ($isReturn == false) {
        redirect('http://u.' . C('QZ_YUMING') . '/blocked');
    }
    return false;
}

/**
 * 产生UUID
 * @param  string $namespace [description]
 * @return [type]            [description]
 */
function create_guid($namespace = '')
{
    $guid = '';
    $uid = uniqid("", true);
    $data = $namespace;
    $data .= $_SERVER['REQUEST_TIME'];
    $data .= $_SERVER['HTTP_USER_AGENT'];
    $data .= $_SERVER['LOCAL_ADDR'];
    $data .= $_SERVER['LOCAL_PORT'];
    $data .= $_SERVER['REMOTE_ADDR'];
    $data .= $_SERVER['REMOTE_PORT'];
    $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
    $guid =
        substr($hash, 0, 8) .
        '-' .
        substr($hash, 8, 4) .
        '-' .
        substr($hash, 12, 4) .
        '-' .
        substr($hash, 16, 4) .
        '-' .
        substr($hash, 20, 12);
    return str_replace("-", "", $guid);
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


/**
 * 获取GET参数
 * @param  [type] $key [参数名]
 * @return [type]      [description]
 */
function getUrlParams($key)
{
    if (isSsl()) {
        $http = "https://";
    } else {
        $http = "http://";
    }

    $parse = parse_url($http . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);

    if (count($parse['query']) > 0) {
        parse_str($parse['query'], $parr);
        if (isset($parr[$key])) {
            $str = $parr[$key];
            return $str;
        }
    }
    return null;
}

/**
 * 二层缓存管理
 * TP框架默认的S缓存作为第一层
 * SL2缓存指定驱动为文件缓存作为第二层
 * 一层为底层,二层为高层
 * 层级越高暴露给使用方就越靠前
 * 使用二层缓存将会分摊缓存压力,将二层缓存用在请求频繁,数据又不太变更的地方
 * 例如OP设置项目
 * @param mixed $name 缓存名称，如果为数组表示进行缓存设置
 * @param mixed $value 缓存值
 * @param mixed $options 缓存参数
 * @return mixed
 */
function SL2($name, $value = '', $options = 15)
{
    //缓存时间
    if (is_array($options)) {
        $expire = isset($options['expire']) ? $options['expire'] : NULL;
    } else {
        $expire = is_numeric($options) ? $options : NULL;
    }
    //定义SL2的options
    $SL2Options = array('type' => 'file', 'prefix' => 'think', 'expire' => $expire);
    //定义配置文件默认的options
    $SL1Options = array('type' => C('DATA_CACHE_TYPE'), 'prefix' => C('DATA_CACHE_PREFIX'), 'expire' => C('DATA_CACHE_TIME'));
    S($SL2Options); //切换到SL2缓存
    if ('' === $value) {
        //获取缓存
        $SL2V = S($name); // 获取SL2缓存
        if (empty($SL2V)) {
            S($SL1Options); //切换SL1缓存
            $SL1V = S($name) ? : false; //从SL1读取
            $SL2V = $SL1V; //把SL1的也存到SL2上
            S($SL2Options); //切换到SL2缓存
            SL2($name, $SL1V); //存到SL2上
            S($SL1Options); //因为S是单例模式,这里恢复框架默认S
            return $SL2V;
        }
        S($SL1Options); //因为S是单例模式,这里恢复框架默认S
        return $SL2V;
    } elseif (is_null($value)) {
        S($name, null); // 删除缓存
        S($SL1Options); //因为S是单例模式,这里恢复框架默认S
        return true;
    } else {
        S($name, $value); // 设置缓存;
        S($SL1Options); //因为S是单例模式,这里恢复框架默认S
        return true;
    }
}

/**
 * 延迟加载图片
 * @param  string $src 正常链接
 * @param  string $extraClass 额外其它样式
 * @param  string $lazyclass 延迟加载样式
 * @param  string $lazySrc 延迟加载默认图片
 * @param  string $lazyAttribute 正常链接存放属性名称
 * @return string
 */
function lazy_load_image($src = '', $extraClass = '', $lazyclass = 'lazy', $lazySrc = '/assets/home/xiaoguotu/img/one.gif', $lazyAttribute = 'data-original')
{
    if (empty($src) || empty($lazySrc)) {
        return '';
    }
    static $robot = '';
    if (!is_bool($robot)) {
        $robot = B("Common\Behavior\RobotCheck");
    }
    if (true === $robot) {
        if (empty($extraClass)) {
            return ' src="' . $src . '" ';
        } else {
            return ' class="' . $extraClass . '" ' . 'src="' . $src . '" ';
        }
    } else {
        if (empty($extraClass)) {
            return ' class="' . $lazyclass . '" ' . 'src="' . $lazySrc . '" ' . $lazyAttribute . '="' . $src . '" ';
        } else {
            return ' class="' . $lazyclass . ' ' . $extraClass . '" ' . 'src="' . $lazySrc . '" ' . $lazyAttribute . '="' . $src . '" ';
        }
    }
}

/**
 * 根据IP获取城市定位(可替换 getCidByCname )
 * @return [type] [description]
 */
function getCityInfoByIp()
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
        $citys = D("Common/Quyu")->getCityInfoByName($cityName);
        if (!empty($citys)) {
            cookie('iplookup', $citys['cid'], 86400 * 7);
            return json_encode(array("data" => $citys, "info" => "", "status" => 1));
        }
    }
    return json_encode(array("data" => "", "info" => "", "status" => 0));
}

/**
 * 通过ip定位到城市 （ipip.net库）
 *
 * @param string $ip
 * @return void
 */
function iptocity($ip)
{
    //S测试用
    // $ip = '61.155.18.12';
    // $ip = '153.35.215.166';//公司联通固定ip
    //E测试用

    import('Library.Org.ipipdotnet.City', '', '.php');
    $ipip_City = new \ipip\datx\City("");
    return $ipip_City->find($ip);
}

/**
 * 获取文章内容中所有图片ALT数组
 * @param  string $str 含有图片链接的字符串
 * @return array       匹配的图片数组
 */
function get_all_image_alt($str)
{

    //$preg='/<img.*?\/>/i';
    $preg = '/alt\s*=[\'|\"](.*?)[\'|\"]/';
    preg_match_all($preg, $str, $data);
    return next($data);
}

/**
 * 城市定位
 * @param  [type] $bm [description]
 * @return [type]     [description]
 */
function getCityInfo($bm)
{
    $city = S("Cache:location:city:".$bm);
    if (!$city) {
        //1. 根据BM获取城市信息
        $city = D("Common/Quyu")->getCityInfoByBm($bm);
        //2.判断是否有城市信息
        if (!empty($city["cid"])) {
            //存在城市信息
            //2.1 判断该城市是否有会员
            $vipcount = D('User')->getRealComapnyCount($city["cid"]);
            $city["vipcount"] = $vipcount;

            if ($vipcount == 0) {
                //2.1.1如果没有真实会员,查询上一级城市信息
                $prevCity = D("Common/Quyu")->getAreaInfos($city["cid"]);
                if (count($prevCity) > 0) {
                    //如果有上一级城市
                    //查询是否有真会员
                    $vipcount = D('User')->getRealComapnyCount($prevCity["fatherid"]);
                    if ($vipcount > 0) {
                        //如果有会员取上一级城市的城市信息
                        $city = D("Common/Quyu")->getCityInfoByBm($prevCity["bm"]);
                        $city["vipcount"] = $vipcount;
                    }
                }
            }
        }
        S("Cache:location:city:".$bm,$city,8*3600);
    }
    return $city;

}

/**
 * 违禁词替换函数
 * @param $str
 * @return mixed
 */
function changDenyWord($str)
{
    $words = file_get_contents(APP_PATH . '/../web/assets/denyword.txt');
    $wordArray = explode('|', $words);
    return str_ireplace($wordArray, '', $str);
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
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $custom 是否使用进行自定义,使用取值自定义配置的方式否则为直接取REMOTE_ADDR
 * @return mixed
 */
function get_client_ip($type = 0, $custom = true)
{
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL) return $ip[$type];
    if ($custom) {
        //IP获取函数get_client_ip()取值顺序自定义配置 从配置文件读取
        //样例 HTTP_CF_CONNECTING_IP,HTTP_X_REAL_IP,HTTP_X_FORWARDED_FOR,HTTP_CLIENT_IP,REMOTE_ADDR
        $GET_IP_VARS_ORDER_ARR = explode(',', C('GET_IP_VARS_ORDER'));
        $GET_IP_VARS_ORDER_ARR = array_filter($GET_IP_VARS_ORDER_ARR);
        if (empty($GET_IP_VARS_ORDER_ARR)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        foreach ($GET_IP_VARS_ORDER_ARR as $ik => $iv) {
            if (isset($_SERVER[$iv])) {
                $ip = $_SERVER[$iv];
                //HTTP_X_FORWARDED_FOR包含多个IP需要单独处理
                if ('HTTP_X_FORWARDED_FOR' == $iv) {
                    $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                    $pos = array_search('unknown', $arr);
                    if (false !== $pos) unset($arr[$pos]);
                    $ip = trim($arr[0]);
                }
                break;
            }
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 去除头尾空格函数，默认使用ltrim函数，可换成trim和rtrim
 * @param $str
 * @param string $position
 * @return mixed
 */
function mytrim($str, $position = 'ltrim')
{
    if (stripos($str,'\'') === 0||stripos($str,'"') === 0){
        $str = $position($str,'\'');
        $str = $position($str,'"');
    }
    $str = str_ireplace('	　　','',$str); //最为特殊的首行空两位，trim函数去除不掉，使用str_ireplace替换
    if(mb_stripos($str,'&nbsp;') !== false && mb_stripos($str,'&nbsp;') < 5){
        $str = str_ireplace('&nbsp;','',$str);  //去除符号实体&nbsp;，trim函数去除不掉，使用str_ireplace替换
    }
    $str = $position($str,'	　　'); //第一种特殊空格
    if (mb_stripos($str,' ')!==false) {
        $str = $position($str, '　');  //第二种特殊空格
    }
    if (mb_stripos($str,' ')!==false) {
        $str = $position($str, '　');  //第三种特殊空格
    }
    if (mb_stripos($str,' ')!==false){
        $str = $position($str,' ');   //第四种特殊空格
    }

    $str = $position($str);
    return $str;

}
/**
 *  获取后台配置的TDK
 * @param $oldTdk 原有的tdk
 * @param $config 配置文件
 * 范例:
    $config = [
    'cs' => '320500', //城市id
    'model' => 1, //模块 1.首页 2.装修公司 3.装修资讯
    'category' => 'bendi', //装修资讯子频道栏目
    'location' => 1, //位置 1.pc端 2.移动端
    ];
 */
function getCommonManageTdk($oldTdk, $config)
{
    $caseLogo = [
        1 => 'Index',
        2 => 'Company',
        3 => 'Zxinfo',
    ];
    if (empty($config['area'])) {
        $config['area'] = '';
    }
    $manageTdk = S('Pc:Sub:' . $caseLogo[$config['model']] . ':managetdk:' . $config['cs'] . $config['category'] . $config['page'].$config['area']);
    if (!$manageTdk) {
        $where = [
            'm.cs' => $config['cs'],
            'm.model' => $config['model'], //1.首页 2.装修公司 3.装修资讯
        ];
        if ($config['category']) {
            $where['i.shortname'] = ['eq', $config['category']];
        }
        if ($config['location']) {
            $where['m.location'] = ['eq', $config['location']];
        }
        $manageTdk = D('Managetdk')->getPcTdk($where);
        //如果有配置就取 配置的TDK
        if ($manageTdk) {
            $oldTdk['title'] = replace_parameter(empty($manageTdk['title']) ? $oldTdk['title'] : $manageTdk['title'], $config['cs'], $config['page'],$config['area']);
            $oldTdk['description'] = replace_parameter(empty($manageTdk['description']) ? $oldTdk['description'] : $manageTdk['description'],$config['cs'], $config['page'],$config['area']);
            $oldTdk['keywords'] = replace_parameter(empty($manageTdk['keywords']) ? $oldTdk['keywords'] : $manageTdk['keywords'],$config['cs'], $config['page'],$config['area']);
            S('Pc:Sub:' . $caseLogo[$config['model']] . ':managetdk:' . $config['cs'] . $config['category'] . $config['page'].$config['area'], $oldTdk, 900);
        }
    } else {
        $oldTdk['title'] = replace_parameter(empty($manageTdk['title']) ? $oldTdk['title'] : $manageTdk['title'],$config['cs'], $config['page'],$config['area']);
        $oldTdk['description'] = replace_parameter(empty($manageTdk['description']) ? $oldTdk['description'] : $manageTdk['description'],$config['cs'], $config['page'],$config['area']);
        $oldTdk['keywords'] = replace_parameter(empty($manageTdk['keywords']) ? $oldTdk['keywords'] : $manageTdk['keywords'],$config['cs'], $config['page'],$config['area']);
    }
    return $oldTdk;
}


/**
 * 替换tdk中字符串
 * @param $data 要替换的字符串
 * @param $city 替换的城市
 * @param $city 替换的分页
 * @return mixed
 */
function replace_parameter($data, $city, $page, $area = '')
{
    $parameter = array(
        ['logo' => 'bm', 'name' => '城市'],
        ['logo' => 'pagenumber', 'name' => '分页'],
        ['logo' => 'suffix', 'name' => '通用后缀', 'value' => '-齐装网'],
    );
    $cityInfo = D("Common/Quyu")->getCityById($city);

    foreach ($parameter as $k => $v) {
        switch ($v['logo']) {
            case 'bm':
                $bmstring = $cityInfo['cname'];
                if (!empty($area)) {
                    $bmstring = $cityInfo['cname'] . $area;
                }
                $data = str_replace('{' . $v['logo'] . '}', $bmstring, $data);
                break;
            case 'suffix':
                $data = str_replace('{' . $v['logo'] . '}', $v['value'], $data);
                break;
            case 'pagenumber':
                if ($page) {
                    $data = str_replace('{' . $v['logo'] . '}', '第' . $page . '页', $data);
                } else {
                    $data = str_replace('{' . $v['logo'] . '}', '', $data);
                }
                break;
        }
    }
    return $data;
}

/**
 * getRandom    随机生成$length位的整数
 * @param $length
 * @param string $chars
 * @return string
 */
function getRandom($length, $chars = '0123456789') {
    $hash = '';
    $max = strlen($chars) - 1;
    for($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
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
        $httpHost = $_SERVER['HTTP_HOST'];
        // 强制过滤掉 HTTP_HOST 中的端口号
        $httpHostArr = explode(':', $httpHost);
        if (count($httpHostArr) > 1) {
            $httpHost = $httpHostArr[0];
        }
        $reArr['host'] = $httpHost;
        $hostArr = explode('.',$reArr['host']);
        $hostDomain = implode('.',array_slice($hostArr,-2));
        $reArr['domain'] = $hostDomain;
        $reArr['scheme_host'] = $reArr['scheme_full'] . $httpHost;
        return $reArr;
    }
}

if (!function_exists('getOPKeySwitchByDomain')) {

    /**
     *
     * 获取配置项，通过访问域名自动增加配置项后缀取到对应域名的配置项
     *
     * 用于解决不同域名访问需要给不同的公司名称和不同的备案号码等
     *
     * @param string $key key
     * @param bool   $all 是否返回完整的数据结构 默认false不返回
     *
     * @return bool|mixed
     */
    function getOPKeySwitchByDomain($key = "", $all = false)
    {
        $reArr = getSchemeAndHost();
        $nowSuffix = '';

        $suffix_qizuang  = C('QZ_YUMING');
        $suffix_taozuang = C('QZ_YUMING_TAOZUANG');
        $suffix_zwdzjj   = C('QZ_YUMING_ZWDZJJ');

        switch ($reArr['domain']) {
            case $suffix_qizuang :
                $nowSuffix = '';
                break;
            case $suffix_taozuang:
                $nowSuffix = '_TAOZUANG';
                break;
            case $suffix_zwdzjj:
                $nowSuffix = '_ZWDZJJ';
                break;
            default:
                $nowSuffix = '';
                break;

        }

        $nowkey = $key . $nowSuffix;

        $OPV = OP($nowkey);

        return $OPV;

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
        } else if (strpos($url, "//") === 0) {
            return "https:" . $url;
        } else {
            if($type == 1){
                return "https://". C('QINIU_DOMAIN') . '/' .$url;
            }elseif($type == 2){
                return C('ZXS_QINIU_DOMAIN') . '/' .$url;
            }
        }
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

if (!function_exists('urlDomainReplace')) {
    /**
     * @param $url 原始url
     * @param $domain 需要替换的目标域名
     *
     * 需要支持qizuang.com taozuang.com 多域名
     * 轮播广告数据库中使用了  http://www.qizuang.com/zhaobiao 这种全路径
     * 当用taozuang.com去访问的时候 期望替换为 http://www.taozuang.com/zhaobiao
     *
     * @return mixed
     */
    function urlDomainReplace($url, $domain) {
        $qzYuming = C('QZ_YUMING'); // 基础域名qizuang.com
        $qzYumingAppArr = C('QZ_YUMING_APP_ARR'); // APP使用的域名
        $flagNoReplace = false; // 不需要替换 默认要替换
        if (strpos($url, $domain) !== FALSE) {
            // 1. 如果当前域名是 qizuang.com 就不进行替换操作
            $flagNoReplace = true;
        } else {
            // 2. 其他不替换的情况
            // $url 中包括APP使用的域名不替换
            foreach($qzYumingAppArr as $index => $string) {
                if (strpos($url, $string) !== FALSE) {
                    $flagNoReplace = true;
                }
            }
        }
        if ($flagNoReplace === false) {
            return str_replace($qzYuming, $domain, $url);
        }
        return $url;
    }
}

if (!function_exists('assoc_unique')) {
    /**
     * 根据key 二维数组去重
     * @param $arr  数组
     * @param $key  key值
     * @return mixed
     */
    function assoc_unique($arr, $key) {
        $tmp_arr = array();
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }

        }
        sort($arr); //sort函数对数组进行排序
        return $arr;
    }
}

if (!function_exists('random_int')) {
    function random_int($min, $max)
    {
        return mt_rand($min, $max);
    }
}

// 缓存时间
// 随机10-20分钟
if (!function_exists('get_cache_time')) {
    function get_cache_time(){
        return random_int(600, 1200);
    }
}

if (!function_exists('getAppEnv')) {
    /**
     * 获取应用运行环境模式 dev test prod
     * @param string $default 默认prod
     *
     * @return array|mixed|string|null
     */
    function getAppEnv($default = 'prod') {
        $APP_ENV = C('APP_ENV');
        if($APP_ENV) {
            return $APP_ENV;
        }
        return $default;
    }
}
