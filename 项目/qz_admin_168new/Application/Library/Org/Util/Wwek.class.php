<?php
/*~

/**
 * Wwek - Wwek class
 * NOTE: Designed for use with PHP version 5 and up
 * @package Wwek
 * @author wwek http://www.iamle.com QQ 121901634
 * @copyright 2013
 */
/**
 *获得ip信息 GetIpInfo,获得真实ip GetIP
 *
 *
 *
 *
 *
 *
 *
 */

class Wwek {

  /////////////////////////////////////////////////
  // PROPERTIES, PUBLIC
  /////////////////////////////////////////////////

  /**
   * Email priority (1 = High, 3 = Normal, 5 = low).
   * @var int
   */
  // public $Priority          = 3;

  /**
   * Sets the CharSet of the message.
   * @var string
   */


  /////////////////////////////////////////////////
  // PROPERTIES, PRIVATE
  /////////////////////////////////////////////////

  // private $smtp            = NULL;
  // private $to              = array();
  // private $LE              = "\n";

  /////////////////////////////////////////////////
  // METHODS, VARIABLES
  /////////////////////////////////////////////////






  /////////////////////////////////////////////////
  // FUNCTIONS 函数
  /////////////////////////////////////////////////
  /**
  * 获取 IP  地理位置
  * 淘宝IP接口
  * @Return: array
  * Array ( [country] => 中国 [country_id] => CN [area] => 华东 [area_id] => 300000
  * [region] => 江苏省 [region_id] => 320000 [city] => 苏州市 [city_id] => 320500
  * [county] => [county_id] => -1 [isp] => 电信 [isp_id] => 100017 [ip] => 117.82.156.174 )
  */
  public function GetIpInfo($ip){
    $url="http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
    $options = array(
                     'http' => array(
                                    'timeout' => 6, //设置一个超时时间，单位为秒
                              )
              );
    $context = stream_context_create($options);
    $ip=json_decode(file_get_contents($url, false, $context));
    if((string)$ip->code=='1'){
      return false;
    }
    $smspost_data = (array)$ip->data;
    return $smspost_data;
  }

  /**
   * [GetPhoneInfo 获取手机号码归属地信息 淘宝接口]
   * @param [type] $phone [description]
   * @return  arr  province   ispname [description]
   */
  public function GetPhoneInfoTaobao($phone){
    $url = 'http://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel=' . $phone;
    $options = array(
                     'http' => array(
                                    'timeout' => 3, //设置一个超时时间，单位为秒
                              )
               );
    $context   = stream_context_create($options);
    $phoneinfo =  file_get_contents($url, false, $context);
    $phoneinfo =  explode('=', $phoneinfo);
    $phoneinfo =  trim(iconv('GB18030', 'UTF-8', $phoneinfo[1]));
    $phoneinfo =  explode(',', $phoneinfo);
    $province  =  explode(':', $phoneinfo[1]);
    $ispname   =  explode(':', $phoneinfo[2]);
    unset($phoneinfo);
    $phoneinfo['province'] = $province[1];
    $phoneinfo['ispname']  = $ispname[1];
    return $phoneinfo;
  }

  /**
   * [GetPhoneInfo 获取手机号码归属地信息 360接口]
   * @param  $phone  手机号
   * @return  arr
   * array(2) {
    * 'code' =>
    * int(0)
    * 'data' =>
    * array(3) {
    *   'province' =>
    *   string(6) "重庆"
    *   'city' =>
    *   string(0) ""
    *   'sp' =>
    *   string(12) "阿里通信"
    * }
    *
   */
  public function GetPhoneInfo360($phone){
    $url = 'http://cx.shouji.360.cn/phonearea.php?number=' . $phone;
    $options = array(
                     'http' => array(
                                    'timeout' => 3, //设置一个超时时间，单位为秒
                              )
               );
    $context       =  stream_context_create($options);
    $phoneinfojson =  file_get_contents($url, false, $context);
    $phoneinfoarr  =  json_decode($phoneinfojson, true);
    return $phoneinfoarr;
  }

  /**
   * [GetPhoneInfo 获取手机号码归属地信息 百度搜索接口]
   * @param  $phone 手机号
   * @return  arr  province   ispname [description]
   */
  public function GetPhoneInfoBaidu($phone){
    if (empty($phone)) {
      return false;
    }
    $url = 'http://www.baidu.com/s?wd=' . $phone;
    $options = array(
                     'http' => array(
                                 'timeout' => 3, //设置一个超时时间，单位为秒
                              )
               );
    $context = stream_context_create($options);
    $file    = file_get_contents($url, false, $context);
    $file    = substr($file,strpos($file,'class="op_mobilephone_r"'));
    $file    = substr($file,0,strpos($file,'<div class="c-row">'));
    $file    = substr($file,0,strpos($file, '</span> </div> </div> </div>'));
    $file    = substr($file,strpos($file,'</span> <span>')+14);
    $file    = explode('&nbsp;',  $file);
    $phoneinfo['province'] = $file[0] . $file[1] . $file[2];
    $phoneinfo['ispname']  = $file[3];
    return $phoneinfo;
  }

  public function GetPhoneBaiduPageInfoNoNum($phone){
    //$starttime = microtime();
    if (empty($phone)) {
      return false;
    }
    $url = 'http://www.baidu.com/s?wd=' . $phone . '&rn=50&ie=utf-8';
    $page    = get_content_by_curl($url);
    $page    = substr($page,strpos($page,'<div id="content_left">'));
    $page    = substr($page,0,strpos($page, '<div id="page" >'));

    //载入phpquery  来处理dom
    import('@.ORG.phpQuery');
    $doc = phpQuery::newDocument($page);
    $lc  = pq('.c-container');
    foreach ($lc as $key => $value) {
       //得到标题
       $list[$key]['t'] = pq($value)->find('.t > a')->text();  //网页标题
       if (empty($list[$key]['t'])) {
          $list[$key]['t'] = pq($value)->find('.op_mobilephone_r')->text(); //正常号码归属地
          if (empty($list[$key]['t'])) {
            $list[$key]['t'] = pq($value)->find('.op_liarphone2_word')->text(); //出来的是搜狗接口的归属地
          }
       }

       //得到描述
       $list[$key]['c'] = pq($value)->find('.c-abstract')->text();  //网页描述
       if (empty($list[$key]['c'])) {
          $list[$key]['c'] = "号码归属地";
       }

    }
    unset($lc);
    unset($doc);

    //替换号码 达到隐藏目的
    foreach ($list as $key => &$value) {
      $search = array(
                      "/$phone/",
                      '/\d{7}/',
                      );
      $replace = array(
                       substr($phone,0,3).'*****'.substr($phone,-3),
                       '8888888',
                       );
       $value['t'] = preg_replace($search,$replace, $value['t']);
       $value['c'] = preg_replace($search,$replace, $value['c']);
       $value['t'] = htmlspecialchars($value['t']);
       $value['c'] = htmlspecialchars($value['c']);

    }
    return $list;
    // var_dump($list);
    // $endtime = microtime();
    // echo $endtime - $starttime;
    // die();

  }


  public function GetPhone360searchPageInfoNoNum($phone){
    //$starttime = microtime();
    if (empty($phone)) {
      return false;
    }
    $url = 'http://www.haosou.com/s?&q=' . $phone;
    $options = array(
                     'http' => array(
                                 'timeout' => 3, //设置一个超时时间，单位为秒
                                 //'proxy'   => 'tcp://10.10.174.223:7777', //设置一个http代理服务器
                              )
               );
    $context = stream_context_create($options);
    $page    = file_get_contents($url, false, $context);
    $page    = substr($page,strpos($page,'<div id="container">'));
    $page    = substr($page,0,strpos($page, '<div id="page">'));

    //载入phpquery  来处理dom
    import('@.ORG.phpQuery');
    $doc = phpQuery::newDocument($page);
    $lc  = pq('ul > li');
    foreach ($lc as $key => $value) {
       //得到标题
       $list[$key]['t'] = pq($value)->find('.res-title > a')->text();  //网页标题
       if (empty($list[$key]['t'])) {
          $list[$key]['t'] = pq($value)->find('.mh-detail')->text(); //正常号码归属地
          $list[$key]['img'] = pq($value)->find('.mohe-tips'); //360为了防止抓取 号码信息为 base64图片
       }

       //得到描述
       $list[$key]['c'] = pq($value)->find('.res-desc')->text();  //网页描述
       if (empty($list[$key]['c'])) {
          $list[$key]['c'] = "号码归属地";
       }

    }
    unset($lc);
    unset($doc);

    //替换号码 达到隐藏目的
    foreach ($list as $key => &$value) {
      $search = array(
                      "/$phone/",
                      '/\d{7}/',
                      );
      $replace = array(
                       substr($phone,0,3).'*****'.substr($phone,-3),
                       '8888888',
                       );
       $value['t'] = preg_replace($search,$replace, $value['t']);
       $value['c'] = preg_replace($search,$replace, $value['c']);
       $value['t'] = htmlspecialchars($value['t']);
       $value['c'] = htmlspecialchars($value['c']);

    }
    return $list;
    // var_dump($list);
    // $endtime = microtime();
    // echo $endtime - $starttime;
    // die();

  }

  /**
   * [GetSinaDwz 新浪短网址接口]
   * @param [type] $url [返回短网址]
   */
  public function GetSinaDwz($url) {
    //$url = 'https://api.weibo.com/2/short_url/shorten.json?source=2688381633&url_long='.$url;//该链接已无法使用
    $url = 'http://api.t.sina.com.cn/short_url/shorten.json?source=2688381633&url_long='.$url;
    $options = array(
                     'http' => array(
                                 'timeout' => 3, //设置一个超时时间，单位为秒
                      )
               );
    $context = stream_context_create($options);
    $jsondata = file_get_contents($url, false, $context);
    $data = json_decode($jsondata,true);
    // $data = $data->urls;
    // $data = (array)$data[0];
    return $data[0]['url_short'];
  }

  /**
   * [GetBaiduDwz 百度短网址生成]
   * @param [type] $url [description]
   */
  public function GetBaiduDwz($url) {
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,"http://dwz.cn/create.php");
    curl_setopt($ch,CURLOPT_POST,true);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_TIMEOUT,120);
    $data=array('url'=>$url);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    $strRes=curl_exec($ch);
    curl_close($ch);
    $arrResponse=json_decode($strRes,true);
    /*if($arrResponse['status']==0)
    {
    echo iconv('UTF-8','GB18030',$arrResponse['err_msg'])."\n";
    }*/
    /** tinyurl */
    var_dump( $arrResponse);
    return $arrResponse['tinyurl'];
  }

  /**
   * 获取分词
   * @param  [str] $str 目标词组
   * @return [array] $result    返回分词词组
   */
  public function getFenCi($str){
        $result = array();
        //新浪分词失效,弃用
        //type : json str 默认array
       /* $url ="http://5.tbip.sinaapp.com/api.php?str=$str&type=json";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        $hander = curl_exec($ch);
        curl_close($ch);
        if($hander !==false){
              $result = json_decode($hander);
        }*/
        //scws4在线分词
        $url = 'http://www.xunsearch.com/scws/api.php';
        $data = array(
                      "data"=>$str,
                      "respond"=>"json",
                      "charset"=>"utf8",
                      "ignore"=>"yes",
                      );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_POST, 1 );
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data );
        $hander = curl_exec($ch);
        curl_close($ch);
        if($hander !==false){
              $json = json_decode($hander, true);
              if($json['status'] == "ok"){
                  $result = $json['words'];
              }

        }else{
          return false;
        }

        return $result;
  }

  /**
   * 获取用户真实 IP
   */
  public function GetIP(){
      static $realip;
      if (isset($_SERVER)){
          if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
              $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
          } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
              $realip = $_SERVER["HTTP_CLIENT_IP"];
          } else {
              $realip = $_SERVER["REMOTE_ADDR"];
          }
      } else {
          if (getenv("HTTP_X_FORWARDED_FOR")){
              $realip = getenv("HTTP_X_FORWARDED_FOR");
          } else if (getenv("HTTP_CLIENT_IP")) {
              $realip = getenv("HTTP_CLIENT_IP");
          } else {
              $realip = getenv("REMOTE_ADDR");
          }
      }
      return $realip;
  }


  /**
   * 发送短信sms 互亿无线
   * @param $PhoneNumber 手机号码
   * @param $PhoneSms 短信内容
   */
  public function SmsSend($PhoneNumber,$PhoneSms) {
      $PhoneSms      = rawurlencode($PhoneSms);
      $smspost_data  = "account=".OP('sms_ihuyi_account','yes')."&password=".OP('sms_ihuyi_password','yes')."&mobile=".$PhoneNumber."&content=".$PhoneSms;

      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, "http://106.ihuyi.cn/webservice/sms.php?method=Submit");
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_NOBODY, true);
      curl_setopt($curl, CURLOPT_TIMEOUT, 3);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $smspost_data);

      $return_str = curl_exec($curl);
      curl_close($curl);

      return $return_str;
  }

    /**
   * 帐号信息查询 互亿无线
   */
  public function getIHuYiInfo() {
      $PhoneSms      = rawurlencode($PhoneSms);
      $smspost_data  = "account=".OP('sms_ihuyi_account','yes')."&password=".OP('sms_ihuyi_password','yes');

      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, "http://106.ihuyi.cn/webservice/sms.php?method=GetNum");
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_NOBODY, true);
      curl_setopt($curl, CURLOPT_TIMEOUT, 3);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $smspost_data);

      $return_str = curl_exec($curl);
      curl_close($curl);

      return $return_str;
  }

  public function SmsSend_old($PhoneNumber,$PhoneSms) {
    $PhoneSms      = rawurlencode($PhoneSms);
    //$PhoneNumber = intval($PhoneNumber);
    $smsapitarget  = "http://60.28.200.150/submitdata/service.asmx/g_Submit";
    $smspost_data  = "sname=lhszqzwl&spwd=87654321&scorpid=&sprdid=1012818&sdst=".$PhoneNumber."&smsg=".$PhoneSms;
    $url_info      = parse_url($smsapitarget);
    $httpheader    = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
    $httpheader    .= "Host:" . $url_info['host'] . "\r\n";
    $httpheader    .= "Content-Type:application/x-www-form-urlencoded\r\n";
    $httpheader    .= "Content-Length:" . strlen($smspost_data) . "\r\n";
    $httpheader    .= "Connection:close\r\n\r\n";
    //$httpheader  .= "Connection:Keep-Alive\r\n\r\n";
    $httpheader    .= $smspost_data;
    $fd            = fsockopen($url_info['host'], 80);
    fwrite($fd, $httpheader);
    $gets = "";
    while(!feof($fd)) {
        $gets .= fread($fd, 128);
    }
    fclose($fd);
    return $gets;
  }

  /**
   *  判断是否是搜索引擎
   *
   * @return true fasle
   */
  //去掉最后一个, getrobot()依赖函数
  private function strexists($string, $find) {
    return !(strpos($string, $find) === FALSE);
  }
  //判断访问者是不是robot搜索蜘蛛
  public function GetRobot() {
    if(!defined('IS_ROBOT')) {
      $kw_spiders = 'Bot|Crawl|Spider|slurp|sohu-search|lycos|robozilla';
      $kw_browsers = 'MSIE|Netscape|Opera|Konqueror|Mozilla';
      if(!$this->strexists($_SERVER['HTTP_USER_AGENT'], 'http://') && preg_match("/($kw_browsers)/i", $_SERVER['HTTP_USER_AGENT'])) {
      define('IS_ROBOT', FALSE);
      } elseif(preg_match("/($kw_spiders)/i", $_SERVER['HTTP_USER_AGENT'])) {
        define('IS_ROBOT', TRUE);
      } else {
        define('IS_ROBOT', FALSE);
      }
    }
    return IS_ROBOT;
  }


    /**
     * qq 类型 在线状态判断
     *
     * @param int  $qqnumber
     * @return array $qqstatus['status'] 未启用 0,  启用未在线 1, 启用在线 2
     * @return array $qqstatus['type']   为空 -1, 非qq号码 0, 普通号码 1, 400/800企业营销QQ 2,  企业办公专用QQ 3 (目前无法判断)
     */
    public static function qqstatus($qqnumber){
        //直接读缓存
        if ($qqstatus = S("Cache:QqStatus:$qqnumber"))
            return  $qqstatus;

        //定义
        $qqstatus = array();

        //检查qq类型
        if (is_numeric($qqnumber)) {
          if (substr($qqnumber, 0, 3) == 400 ||substr($qqnumber, 0, 3) == 800) {
            $qqstatus['type'] = 2;
          }else{
            $qqstatus['type'] = 1;
          }
        }elseif (empty($qqnumber)) {
          $qqstatus['type'] = -1;
        }else{
          $qqstatus['type'] = 0;
        }

        //如果$qqstatus['type'] = 1 普通qq
        if ($qqstatus['type'] == 1) {
            // http://webpresence.qq.com/getonline?Type=1&123456:23456:
            //检查在线状态 普通qq通过跳转的图片名字判断
            $qqurl = 'http://wpa.qq.com/pa?p=2:'.$qqnumber.':41';
            $headers = get_headers($qqurl, true);
            $headers = $headers['Location'];
            //echo $headers.'<br>';
            $headers = explode("/",$headers);
            switch (end($headers) ) {
                case '41_stop.gif':
                    $qqstatus['status'] = 0; //未启用
                    break;
                case 'button_10.gif':
                    $qqstatus['status'] = 1; //未在线
                    break;
                case 'button_11.gif':
                    $qqstatus['status'] = 2; //在线
                    break;
                default:
                    $qqstatus['status'] = 0;  //默认
                    break;
            }
        }


        //缓存
        S("Cache:QqStatus:$qqnumber", $qqstatus, 86400);

        return $qqstatus;
    }

    // 给文本加链接
    public static function addLink($content, $rules)
    {
        include_once(dirname(__FILE__) .'/simple_html_dom.php');
        if ($content=="") {
          return "";
        }
        // 1. 处理替换规则
        $replace = array();
        foreach ($rules as $kwd=>$a) {
            $target = '_blank';
            $title  = '';
            $href   = '#';
            if (is_array($a) && isset($a['href'])) {
                $href   = $a['href'];
                if (isset($a['target']))
                    $target = $a['target'];
                if (isset($a['title']))
                    $title  = $a['title'];
            } else if (is_string($a)) {
                $href   = $a;
            } else
                return  false;

            $replace["$kwd"]= sprintf(
                    '<a href="%s" target="%s" title="%s" style="font-size: inherit;">$0</a>',
                    $href, $target, $title);
        }
        $pattern= '/'. implode('|',array_keys($replace)) .'/u';
        $replace= array_pop($replace);

        // 2. 替换链接
        $dom    = str_get_html($content);
        $stack  = array($dom->root);
        while (count($stack)) {
            $node = array_pop($stack);
            if ('text' === $node->tag) {
                $node->innertext = preg_replace($pattern, $replace, $node);
            }
            if ($children = $node->nodes) {
                foreach ($children as $node)
                    if ('a' !== $node->tag)
                        $stack[] = $node;
            }
        }

        return  $dom->save();
    }

    // 重要数据记录log
    public static function log_vidata($table, $id, $action='',$auto = false) {
        // 只记录删除、修改等动作
        if (! in_array($action, explode(' ','D U I')))
            return  FALSE;

        $map = array( 'id' => $id, );
        if ($data = M($table)->where($map)->select()) {
            //查询当前公司的会员日期
            $map = array(
                    "id"=>array("EQ",$data[0]["comid"])
                         );
            $userInfo = M("user")->where($map)->field("start,end")->find();
            if(count($userInfo) == 0){
               return false;
            }
            $log = array(
                    'action'    => $action,
                    'table'     => $table,
                    'ref_id'    => $id,
                    'opid'      => $_SESSION['admin_id'],
                    'opname'    => $_SESSION['adminuser']
                    );
            if($auto){
              $log["opid"] = 0;
              $log["opname"] = '系统';
            }
            $obj = M('log_vidata');
            foreach ($data as $orign) {
                $orign["vip_start"] = $userInfo["start"];
                $orign["vip_end"]  = $userInfo["end"];
                $log['orign_data']  = json_encode($orign);
                if (FALSE === $obj->add($log))
                    return  FALSE;
            }

            return  TRUE;
        }
        return  FALSE;
    }

    /**
     * 获取操作记录日志
     * @return [type] [description]
     */
    public function get_log_vidata($table,$id="",$comid = "",$position =""){
        $list = array();
        $m = M('log_vidata');
        $map = array(
                  "a.table"=>array("EQ",$table)
                     );
        if(!empty($id)){
           $map["a.ref_id"] = array("EQ",$id);
        }



        if(!empty($comid)){
          switch ($table) {
            case 'bigadv':
                $map["b.position"] = array("EQ",$position);
                $map["b.comid"] = array("EQ",$comid);
            break;
            case 'adv':
              $map["b.comid"] = array("EQ",$comid);
              break;
            case "advs":
              $map["b.company_id"] = array("EQ",$comid);
              break;
          }
        }
        $table_name = "qz_".$table;
        $list = $m->where($map)->alias("a")
                  ->join("inner join $table_name as b on b.id = a.ref_id")
                  ->field("a.id,action,opname,optime,orign_data,ref_id")->order("optime desc")->select();
        if(count($list)>0){
          foreach ($list as $key => $value) {
                $action ="编辑" ;
                switch (strtolower($value["action"])) {
                  case 'u':
                    $action ="编辑";
                    break;
                  case 'i':
                    $action ="新增" ;
                    break;

                 case 'd':
                    $action ="停用" ;
                    break;
                }
                $list[$key]["action"] = $action;
                $list[$key]["orign_data"] =json_decode($value["orign_data"],true);
          }
        }
        return $list;
    }

    public static function getTel($tel_str) {
        $tel = preg_replace('/\D/g', '', $tel_str);
        if ($tel && ($len = strlen($tel))) {
            if ($len >6 && $len <= 18)
                return  $tel;
        }
        return  FALSE;
    }


    // 自动代理发送 http请求
    public static function curl($url, $opt=array()) {
        $debug  = isset($opt['debug']) && $opt['debug'];

        // 设置访问代理
        $ua = $proxy = '';
        if (isset($opt['ua']) && isset($opt['proxy'])) {
            $ua     = $opt['ua'];
            $proxy  = $opt['proxy'];
        } else {
            $redis  = new Redis();
            if ($redis->connect(C('REDIS_HOST'), C('REDIS_PORT'), 1)) {
                $redis->select(1);
                $ua     = isset($opt['ua']) ? $opt['ua'] : $redis->sRandMember('user_agent');
                $proxy  = isset($opt['proxy']) ? $opt['proxy'] : $redis->sRandMember('proxy_ip');
            }
        }

        // curl request options
        $options    = array(
                CURLOPT_TIMEOUT => isset($opt['timeout']) && $opt['timeout'] > 0 ? $opt['timeout'] : 120,
                CURLOPT_HEADER  => FALSE,
                CURLOPT_RETURNTRANSFER  => TRUE,
                );
        if (isset($opt['referer']))
            $options[CURLOPT_REFERER]   = $opt['referer'];
        if (!empty($ua))
            $options[CURLOPT_USERAGENT] = $ua;
        if (!empty($proxy))
            $options[CURLOPT_PROXY]     = $proxy;

        $ch     = curl_init();
        // set curl options
        curl_setopt_array($ch, $options);
        curl_setopt($ch, CURLOPT_URL, $url);

        if ($debug)
            $debug = array(
                    'cost'  => 0,
                    'time1' => gettimeofday(TRUE),
                    'proxy' => $proxy,
                    'ua'    => $ua,
                    );

        $ret    = curl_exec($ch);
        if ($debug) {
            $debug['time2'] = gettimeofday(TRUE);
            $debug['ret']   = $ret;
            $debug['cost']  = $debug['time2'] - $debug['time1'];

            var_dump($debug);
        }

        // 收尾
        curl_close($ch);
        if (isset($redis)) {
            if (isset($opt['compact']) && $opt['compact'] &&
                    FALSE === $ret && !empty($proxy)) {
                $redis->sRem('proxy_ip', $proxy);
            }
            $redis->close();
        }

        return  $ret;
    }

    // 同时下载多个文件
    public  static function mget($url_list, $opt=array()) {
        $retSet = array();

        $prefix = '/tmp';           // 下载文件的保存路径
        if (isset($opt['prefix'])) {
            $prefix = $opt['prefix'];
        }
        // curl request options
        $options    = array(
                CURLOPT_TIMEOUT => isset($opt['timeout']) && $opt['timeout'] > 0 ? $opt['timeout'] : 120,
                CURLOPT_HEADER  => FALSE,
                CURLOPT_RETURNTRANSFER  => TRUE,
                );
        if (isset($opt['referer']))
            $options[CURLOPT_REFERER]   = $opt['referer'];
        if (isset($opt['ua']) && !empty($opt['ua']))
            $options[CURLOPT_USERAGENT] = $opt['ua'];
        if (isset($opt['proxy']) && !empty($opt['proxy']))
            $options[CURLOPT_PROXY]     = $opt['proxy'];

        // init request queue
        $mh = curl_multi_init();
        $running = NULL;
        foreach ($url_list as $url) {
            $err = 'OK';
            $ch  = NULL;
            if ($a  = parse_url($url)) {
                $ch = curl_init();
                curl_setopt_array($ch, $options);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_multi_add_handle($mh, $ch);
            } else {
                $err = 'URL PARSE ERROR';
            }
            $retSet[]= array(
                    'url'   => $url,
                    'err'   => $err,
                    'fname' => 'OK' == $err ? md5(basename($a['path'])) : '',
                    '_ch'   => $ch,
                    );
        }

        // downloading
        do {
            $mrc = curl_multi_exec($mh, $running);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        while ($running && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $running);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        foreach ($retSet as &$row) {
            // save files
            if ($ch = $row['_ch']) {
                $fname = uniqid("$prefix/".$row['fname']);
                if (file_put_contents($fname, curl_multi_getcontent($ch))) {
                    $row['fname'] = $fname;
                } else {
                    $row['fname'] = '';
                    $row['err']   = 'FILE SAVE ERROR';
                }
                curl_multi_remove_handle($mh, $ch);
                unset($row['_ch']);
            }
        }
        unset($row);
        curl_multi_close($mh);

        return  $retSet;
    }

    // 中文分词
    public  static function token($str, $enc='utf-8') {
        static  $pscws = NULL;
        if ($pscws === NULL) {
            $base   = 'Lib/ORG/pscws4';
            include_once "$base/pscws4.class.php";

            $pscws  = new PSCWS4('gbk');
            $pscws->set_dict("$base/etc/dict.xdb");
            $pscws->set_rule("$base/etc/rules.ini");
        }
        $retSet = array();

        if (strcasecmp($enc, 'GB18030')) {
            $str= iconv($enc, 'GB18030', $str);
        }
        $pscws->send_text($str);

        while ($piece = $pscws->get_result()) {
            foreach ($piece as $word) {
                $retSet[] = iconv('GB18030', 'UTF-8', $word['word']);
            }
        }
        return  $retSet;
    }


    //计算一个表的列平均值
    /**
     * 传入一个mysql结果集。计算字段的平均值。 返回一行记录
     * @param    $arr 多行记录二维数组
     * @return 返回一行记录 or false
     */
    public static function fieldavg($arr) {
      if (is_array($arr)) {
         $hang     = count($arr); //多少行记录
         $rearr    = array(); //生成的平均数据
         $rearrsum = array(); //生成的列的和
         foreach ($arr as $key => $value) { //循环外层多条记录
             foreach ($value as $key1 => $value1) { //循环内层某一个条记录
                $rearrsum[$key1] += $value1;
             }
         }
         foreach ($rearrsum as $key2 => $value2) {
            $rearr[$key2] = $value2 / $hang;
         }
         return $rearr;
      }
      return false;
    }

    /**
   * 上传图片到本地
   * @return [type] [description]
   */
  public function uploadImageFile(){
    if (!$_FILES['Filedata']) {
          die ( '文件不存在!' );
        }
        if ($_FILES['Filedata']['error'] > 0) {
          switch ($_FILES ['Filedata'] ['error']) {
            case 1 :
              $error_log = '文件太大了';
              break;
            case 2 :
              $error_log = '文件太大了';
              break;
            case 3 :
              $error_log = '只有部分文件上传完成';
              break;
            case 4 :
              $error_log = '没有上传文件';
              break;
            default :
              break;
          }
          die ( 'upload error:' . $error_log );
        } else {
          $img_data = $_FILES['Filedata']['tmp_name'];
          $size = getimagesize($img_data);
          $file_type = $size['mime'];
          if (!in_array($file_type, array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'))) {
            $error_log = 'only allow jpg,png,gif';
            die ( 'upload error:' . $error_log );
          }
          switch($file_type) {
            case 'image/jpg' :
            case 'image/jpeg' :
            case 'image/pjpeg' :
              $extension = 'jpg';
              break;
            case 'image/png' :
              $extension = 'png';
              break;
            case 'image/gif' :
              $extension = 'gif';
              break;
          }
        }
        if (!is_file($img_data)) {
          die ( 'Image upload error!' );
        }
        //图片保存路径,默认保存在该代码所在目录(可根据实际需求修改保存路径)
        $save_path = dirname(dirname(dirname(__FILE__)))."/upload/avatarcache";
        if(!is_dir($save_path)){
            mkdir($save_path,0777);
        }
        $uinqid = uniqid();
        $filename = $save_path . '/' . $uinqid . '.' . $extension;
        $result = move_uploaded_file( $img_data, $filename );
        if ( ! $result || ! is_file( $filename ) ) {
          die ( '文件上传失败!' );
        }

        return array("filePath"=>'/upload/avatarcache',"filename"=> $uinqid . '.' . $extension);
  }

    /**
   * 获取随机验证码
   * @param  integer $len [字符数量]
   * @return [type]       [description]
   */
  public function getSafeCode($len = 4,$chartype = "ALL"){
      // 随机字符
      $alpha  = array(
          'ABCDEFGHIJKLMNPQRSTUVWXYZ',
          'abcdefghjkmnpqrstuvwxyz'
          );
      $ch_set = array();
      $lA     = strlen($alpha[0]);
      $la     = strlen($alpha[1]);
      for ($i = 0; $i < $lA || $i < $la; $i++) {
          if ($i < $lA)
              $ch_set[]   = $alpha[0]{$i};
          if ($i < $la)
              $ch_set[]   = $alpha[1]{$i};
      }
      switch (strtoupper($chartype)) {
          case 'CHAR':
              break;
          case 'NUMBER':
              $ch_set = str_split('0123456789');
              break;
          default:
              for ($i = 1; $i++ < 9; )
                  $ch_set[]   = $i;
      }
      // 生成验证码
      $code   = '';
      for ($i = 0, $n = count($ch_set); $i < $len; $i++) {
          $code  .= $ch_set[mt_rand()%$n];
      }

      return $code;
  }
}


// 标准时间处理
function uniformDate($date, $placeholder='')  {
    if (!is_numeric($date))
        $date   = @strtotime($date);

    return  $date ? date('Y-m-d', $date) : $placeholder;
}
function uniformTime($time, $placeholder='')  {
    if (!is_numeric($time))
        $date   = @strtotime($time);

    return  $time ? date('Y-m-d H:i:s', $time) : $placeholder;
}

// 统一称谓
function uniformName($user) {
    return empty($user['sex']) || preg_match('/^\w+$/',$user['name']) ? $user['name'] :
            mb_substr($user['name'],0,1,'utf-8') .$user['sex'];
}


// 汉字拼音第一个字母
function getPinYin($str) {
    $fchar = ord(substr($str, 0, 1));
    if (($fchar >= ord("a") and $fchar <= ord("z"))or($fchar >= ord("A") and $fchar <= ord("Z"))) return strtoupper(chr($fchar));
    $s1=iconv('UTF-8','GB18030',$str);
    $s2=iconv('GB18030','UTF-8',$s1);
    $s=$s2==$str?$s1:$str;
    $asc = ord($s{0}) * 256 + ord($s{1})-65536;
    if ($asc >= -20319 and $asc <= -20284)return "A";
    if ($asc >= -20283 and $asc <= -19776)return "B";
    if ($asc >= -19775 and $asc <= -19219)return "C";
    if ($asc >= -19218 and $asc <= -18711)return "D";
    if ($asc >= -18710 and $asc <= -18527)return "E";
    if ($asc >= -18526 and $asc <= -18240)return "F";
    if ($asc >= -18239 and $asc <= -17923)return "G";
    if ($asc >= -17922 and $asc <= -17418)return "H";
    if ($asc >= -17417 and $asc <= -16475)return "J";
    if ($asc >= -16474 and $asc <= -16213)return "K";
    if ($asc >= -16212 and $asc <= -15641)return "L";
    if ($asc >= -15640 and $asc <= -15166)return "M";
    if ($asc >= -15165 and $asc <= -14923)return "N";
    if ($asc >= -14922 and $asc <= -14915)return "O";
    if ($asc >= -14914 and $asc <= -14631)return "P";
    if ($asc >= -14630 and $asc <= -14150)return "Q";
    if ($asc >= -14149 and $asc <= -14091)return "R";
    if ($asc >= -14090 and $asc <= -13319)return "S";
    if ($asc >= -13318 and $asc <= -12839)return "T";
    if ($asc >= -12838 and $asc <= -12557)return "W";
    if ($asc >= -12556 and $asc <= -11848)return "X";
    if ($asc >= -11847 and $asc <= -11056)return "Y";
    if ($asc >= -11055 and $asc <= -10247)return "Z";
    return null;
}


// 汉字拼音第一个字母 old
function getPinYin_old($char) {
    // http://ir.hit.edu.cn/~taozi/Gb2312.TXT

    $gb = array_map(function ($char) {
                return  iconv('UTF-8', 'GB2312', $char);
            }, array(
                '啊', '芭', '擦', '搭', '蛾', '发', '噶', '哈',
                '击', '喀', '垃', '妈', '拿', '哦', '啪', '期', '然',
                '撒', '塌', '挖', '昔', '压', '匝',             '亍'
                ));
    $map= array_combine($gb, array(
                'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
                'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
                'S', 'T', 'W', 'X', 'Y', 'Z',                   '_'
                ));
    $py     = '';
    $char   = iconv('UTF-8', 'GB2312', $char);

    if ($char) foreach ($map as $gb => $ch) {
        if ($char >= $gb)
            $py = $ch;
        else
            return  $py;
    }

    return  '';
}

/**
 * 获取两个时间戳之间的容易让人看的时间差
 * @param int $starttime
 * @param int $endtime
 * @return  false  or 1年1月1天18时59分34秒
 */
function TimeDiffToymdhis($starttime, $endtime) {
    if (empty($starttime) || empty($endtime)) {
       return false;
    }
    $time_diff = $endtime - $starttime;
    $date_array=array('年'=>31536000,'月'=>2592000,'周'=>604800,'天'=>86400,'时'=>3600,'分'=>60,'秒'=>1);
    $real_diff = '';
    foreach ($date_array as $key => $v) {
        if ($time_diff>=$v) {
            $num=intval($time_diff/$v);
            $time_diff-=$num*$v;
            $real_diff.=$num.$key;
        }
    }
    return  $real_diff;
}

/**
 * [uniformCity 返回 城市名称 首字母]
 * @param  [arr] $quyu bm 别名  cname 城市名  如: arr['sz'], arr['苏州']
 * @return [arr] 大写首字母 + 空格 + 城市名称  arr['S 苏州']
 */
function uniformCity($quyu) {
    $pref   = ' ';
    if (!empty($quyu['bm'])) switch ($bm = strtoupper($quyu['bm'])) {
        case 'WWW':
            $pref   = 'Q';  break;
        case 'WHHT':
            $pref   = 'H';  break;
        default:
            $pref   = $bm{0};
    }
    return  $pref .' '. $quyu['cname'];
}
function uniformHour($data) {
    return  $data['time'] ? date('Y-m-d H:00', $data['time']): (
                ! $data['addtime'] ? '' : date('Y-m-d', $data['addtime'])
                );
}

// 生成 html select option 标签
function build_options($options, $opt='') {
    $frags  = array();
    if (is_string($options)) {
        if (FALSE !== strpos($options, ',')) {
            $options = explode(',', $options);
        } else if (FALSE !== strpos($options, ' ')) {
            $options = explode(' ', $options);
        } else
            return  '';
        if ($opt && !in_array($opt, $options))
            array_unshift($options, $opt);

        $options = array_combine($options, $options);
    }
    if (is_array($options)) {
        foreach ($options as $key=>$val) {
            $frags[] = sprintf('<option value="%s" %s>%s</option>',
                    $key, $opt != '' && $key == $opt ? 'selected' : '', $val);
        }
    }

    return  implode("\n", $frags);
}

//php获取中文字符拼音首字母
function getFirstCharter($str){
    if(empty($str)){
        return '';
    }
    //定义生僻城市对应首字母的数组序列 以城市名=>首字母形式组成
    $hard_unknow_word = S('Cache:HardUnknowWord');
    if(empty($hard_unknow_word)){
        $res = M('specialword')->select();
        foreach ($res as $k => $v) {
            $hard_unknow_word[$v['word']] = $v['character'];
        }
        S('Cache:HardUnknowWord',$hard_unknow_word,900);
    }

  //下面要做一部分城市的处理  因为这部分城市的名称是偏僻字或者是因为多音字问题 就会带来排序不规则的问题
  //比如重庆 返回的是Z  衢州返回空字符串 因为无法匹配  一级城市主要发现  重庆  溧水 溧阳 衢州 等等
  $hard_unknow_word_city=array_keys($hard_unknow_word);
  if (in_array($str,$hard_unknow_word_city)) {
    #是生僻字或多音字
    return $hard_unknow_word[$str];
  }else{
      //如果是单个生僻字
      $char = mb_substr($str,0,1,"utf-8");
      if(in_array($char,$hard_unknow_word_city)){
          return $hard_unknow_word[$char];
      }
  }
  $fchar=ord($str{0});
  if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
  $s1=iconv('UTF-8','GB18030',$str);
  $s2=iconv('GB18030','UTF-8',$s1);
  $s=$s2==$str?$s1:$str;
  $asc=ord($s{0})*256+ord($s{1})-65536;
  if($asc>=-20319&&$asc<=-20284) return 'A';
  if($asc>=-20283&&$asc<=-19776) return 'B';
  if($asc>=-19775&&$asc<=-19219) return 'C';
  if($asc>=-19218&&$asc<=-18711) return 'D';
  if($asc>=-18710&&$asc<=-18527) return 'E';
  if($asc>=-18526&&$asc<=-18240) return 'F';
  if($asc>=-18239&&$asc<=-17923) return 'G';
  if($asc>=-17922&&$asc<=-17418) return 'H';
  if($asc>=-17417&&$asc<=-16475) return 'J';
  if($asc>=-16474&&$asc<=-16213) return 'K';
  if($asc>=-16212&&$asc<=-15641) return 'L';
  if($asc>=-15640&&$asc<=-15166) return 'M';
  if($asc>=-15165&&$asc<=-14923) return 'N';
  if($asc>=-14922&&$asc<=-14915) return 'O';
  if($asc>=-14914&&$asc<=-14631) return 'P';
  if($asc>=-14630&&$asc<=-14150) return 'Q';
  if($asc>=-14149&&$asc<=-14091) return 'R';
  if($asc>=-14090&&$asc<=-13319) return 'S';
  if($asc>=-13318&&$asc<=-12839) return 'T';
  if($asc>=-12838&&$asc<=-12557) return 'W';
  if($asc>=-12556&&$asc<=-11848) return 'X';
  if($asc>=-11847&&$asc<=-11056) return 'Y';
  if($asc>=-11055&&$asc<=-10247) return 'Z';
  return null;
}
?>