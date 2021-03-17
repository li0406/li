<?php

/**
 * 加密解密函数
 * @return [type] [description]
 */
function authcode($str,$operation="DECODE"){
	 import('Library.Org.Util.Authcode');
	 $auth = new \Authcode();
	 $code = $auth->DEcode($str,strtoupper($operation));
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
    $value = D("Common/Options")->getOptionName($key,$status);
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
    $signature = "";
    if (function_exists('hash_hmac')) {
        $signature = base64_encode(hash_hmac("sha1", $str, $key, true));
    } else {
        $blocksize = 64;
        $hashfunc = 'sha1';
        if (strlen($key) > $blocksize) {
            $key = pack('H*', $hashfunc($key));
        }
        $key = str_pad($key, $blocksize, chr(0x00));
        $ipad = str_repeat(chr(0x36), $blocksize);
        $opad = str_repeat(chr(0x5c), $blocksize);
        $hmac = pack(
                'H*', $hashfunc(
                        ($key ^ $opad) . pack(
                                'H*', $hashfunc(
                                        ($key ^ $ipad) . $str
                                )
                        )
                )
        );
        $signature = base64_encode($hmac);
    }
    return $signature;
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
        'nil') ) ) {
        //如果传入的发送短信类型未知,那么设置为nil
        $data['type'] = 'nil';
    }

    //处理短信发送通道
    if (!in_array($data['sms_channel'],array('yuntongxun','ihuyi','yunrongt') ) ) {
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
                    $data['tpld'] = sprintf($tpls, $data['variable'][0]); // 做模版
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
            }

            //做提交信息
//            $smsdata['cmd']         = 'sendMessage'; //单条发送短信
            $smsdata['mobile'] = $data['tel']; //手机号码
            $smsdata['content']        = $data['tpld']; //短信内容
            $result                 =  $Yunrongt->sendMessage($smsdata);
            //dump($smsdata); //调试用,线上生产环境必须是注释状态

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

if (!function_exists('env')) {
    /**
     * 获取环境变量值
     * @access public
     * @param  string    $name 环境变量名（支持二级 .号分割）
     * @param  string    $default  默认值
     * @return mixed
     */
    function env($name = null, $default = null)
    {
        return Env::get($name, $default);
    }
}
