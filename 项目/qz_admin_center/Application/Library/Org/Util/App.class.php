<?php
class App{
    //定义生僻字私有属性，避免多次读取缓存，造成IO过高
    private $hard_unknow_word = '';

    //php获取中文字符拼音首字母
    public function getFirstCharter($str){
        //如果是空的城市名 返回空字符串
        if(empty($str)){
            return '';
        }
        //下面要做一部分城市的处理  因为这部分城市的名称是偏僻字或者是因为多音字问题 就会带来排序不规则的问题
        //比如重庆 返回的是Z  衢州返回空字符串 因为无法匹配  一级城市主要发现  重庆  溧水 溧阳 衢州 等等

        //定义生僻城市对应首字母的数组序列 以城市名=>首字母形式组成
        $hard_unknow_word = $this->hard_unknow_word;
        if(empty($hard_unknow_word)){
            $hard_unknow_word = S('Cache:HardUnknowWord');
            if(empty($hard_unknow_word)){
                $res = M('specialword')->select();
                foreach ($res as $k => $v) {
                    $hard_unknow_word[$v['word']] = $v['character'];
                }
                S('Cache:HardUnknowWord',$hard_unknow_word,7200);
            }
            $this->hard_unknow_word = $hard_unknow_word;
        }

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
        if($fchar>=ord('A')&&$fchar<=ord('z')){
            return strtoupper($str{0});
        }
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
    *  判断是否是搜索引擎
    *
    * @return true fasle
    */
    //去掉最后一个, getrobot()依赖函数
    private function strexists($string, $find) {
        return !(strpos($string, $find) === FALSE);
    }

    /**
     * [order_tel_encrypt 电话号码加密 手动加盐]
     * @param  [type] $tel [电话号码]
     * @return [type]      [md5($tel.$salt)密文]
     */
    public function order_tel_encrypt($tel) {
        return md5($tel . C('QZ_YUMING'));

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

    /**
     * 打乱数组排序
     * @param  [type] $arr [description]
     * @return [type]      [description]
     */
    function rand_array($arr)
    {
        //获得数组大小
        $arr_size=sizeof($arr);
        //初始化结果数组
        $tmp_arr=array();
        //开始乱序排列
        for($i=0;$i<$arr_size;$i++){
            //随机配置种子，减少重复率
            mt_srand((double) microtime()*1000000);
            //获得被配置的下标
            $rd=mt_rand(0,$arr_size-1);
            //下标是否已配置
            if($tmp_arr[$rd]=="")  //未配置
            {
                //进行配置
                $tmp_arr[$rd]=$arr[$i];
            }
            else  //已配置
            {
                //返回
                $i=$i-1;
            }
        }
        return $tmp_arr;
    }

    /**
    * [GetSinaDwz 新浪短网址接口]
    * @param [type] $url [返回短网址]
    */
    public function GetSinaDwz($url) {
            $url = 'http://api.t.sina.com.cn/short_url/shorten.json?source=2688381633&url_long='.$url;

            $options = array(
                             'http' => array(
                                         'timeout' => 3, //设置一个超时时间，单位为秒
                              )
                       );
            $context = stream_context_create($options);
            $jsondata = file_get_contents($url, false, $context);
            $data = json_decode($jsondata,true);
            return $data[0]['url_short'];
    }

    public function shorturl($input) {
        $base32 = array (
            "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j",
            "k", "l", "m", "n", "o", "p", "q", "r", "s", "t",
            "u", "v", "w", "x", "y", "z",
            "A", "B", "C", "D", "E", "F", "G", "H", "I", "J",
            "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T",
             "U", "V", "W", "X", "Y", "Z"
        );

         $hex = md5($input);
         $hexLen = strlen($hex);
         $subHexLen = $hexLen / 8;
         $output = array();

         for ($i = 0; $i < $subHexLen; $i++) {
          $subHex = substr ($hex, $i * 8, 8);
          $int = 0x3FFFFFFF & (1 * ('0x'.$subHex));
          $out = '';

          for ($j = 0; $j < 6; $j++) {
           $val = 0x0000001F & $int;
           $out .= $base32[$val];
           $int = $int >> 5;
          }

          $output[] = $out;
         }

         return $output;
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
   * 获取随机姓名
   * @return [type] [description]
   */
  public function getRandXing(){
    $array = array('赵','钱','孙','李','周','吴','郑','王','冯','陈','褚','卫','蒋','沈','韩','杨','朱','秦','尤','许','何','吕','施','张','孔','曹','严','华','金','魏','陶','姜','戚','谢','邹','喻','柏','水','窦','章','云','苏','潘','葛','奚','范','彭','郎','鲁','韦','昌','马','苗','凤','花','方','任','袁','柳','鲍','史','唐','费','薛','雷','贺','倪','汤','滕','殷','罗','毕','郝','安','常','傅','卞','齐','元','顾','孟','平','黄','穆','萧','尹','姚','邵','湛','汪','祁','毛','狄','米','伏','成','戴','谈','宋','茅','庞','熊','纪','舒','屈','项','祝','董','梁','杜','阮','蓝','闵','季','贾','路','娄','江','童','颜','郭','梅','盛','林','钟','徐','邱','骆','高','夏','蔡','田','樊','胡','凌','霍','虞','万','支','柯','管','卢','莫','柯','房','裘','缪','解','应','宗','丁','宣','邓','单','杭','洪','包','诸','左','石','崔','吉','龚','程','嵇','邢','裴','陆','荣','翁','荀','于','惠','甄','曲','封','储','仲','伊','宁','仇','甘','武','符','刘','景','詹','龙','叶','幸','司','黎','溥','印','怀','蒲','邰','从','索','赖','卓','屠','池','乔','胥','闻','莘','党','翟','谭','贡','劳','逄','姬','申','扶','堵','冉','宰','雍','桑','寿','通','燕','浦','尚','农','温','别','庄','晏','柴','瞿','阎','连','习','容','向','古','易','廖','庾','终','步','都','耿','满','弘','匡','国','文','寇','广','禄','阙','东','欧','利','师','巩','聂','关','荆','司马','上官','欧阳','夏侯','诸葛','闻人','东方','赫连','皇甫','尉迟','公羊','澹台','公冶','宗政','濮阳','淳于','单于','太叔','申屠','公孙','仲孙','轩辕','令狐','徐离','宇文','长孙','慕容','司徒','司空');

    shuffle($array);
    $rand = array_rand($array,1);
    return $array[0];
  }

}

