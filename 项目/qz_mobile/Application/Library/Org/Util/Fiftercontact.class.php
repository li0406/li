<?php
/**
 * 过滤联系方式 qq 电话号码 网址 href 等的模型
 */

class Fiftercontact
{
    private $titleKeywords = array(
                    "联系电话"=>"","咨询电话"=>"","联系方式"=>"","免费咨询"=>"","公司地址"=>"","公司网址"=>"",
                    "联系"=>"","电话"=>"","号码"=>"","tel"=>"","方式"=>"","咨询"=>"","contact"=>"","touch"=>"",
                    "connection"=>"","relation"=>"","免费"=>"","lianxidianhua"=>"","dianhua"=>"","phone"=>"","moblie"=>"",
                    "ADD"=>"","addr"=>"","ADDR"=>"","add"=>"","地址"=>"","网址"=>"",
    );
    /**
     * [fifter_contact 过滤联系方式包括手机号码 QQ 邮箱 连接等]
     * @param  [string] $str [过滤之前的字符串]
     * @return [string] $str [过滤之后的字符串]
     */
    public function fifter_contact($str,$type="")
    {
        if (empty($str)||trim($str)=="") {return $str;}//为空不过滤
		$str=strtolower($str);				 //字符串转小写
        if (!in_array($type,array("company_qc","company_jc"))) {
            $str=$this->Sbc2Dbc($str);//如果不是编辑公司全称和简称 那么就过滤全角转半角
        }
        $str=$this->fifter_blank($str);
        $str=$this->fifter_cell($str);       //过滤手机号码
        $str=$this->fifter_email($str);      //过滤邮箱
        $str=$this->fifter_qq($str);         //过滤qq号码
        // $str=$this->fifter_number($str);     //过滤五位及其以上
        $str=$this->fifter_a_href($str);     //过滤a标签里面的href值
        //$str=$this->fifter_url_Suffix($str); //过滤文本中的网址后缀
        return $str;
    }

    //过滤空格
    public function fifter_blank($str){
        $pattern = '/\s/';
        $replaceText = preg_replace_callback($pattern, function($matches){
        return "";
        }, $str);
        return $replaceText;
    }
    /**
     * [fifter_text 过滤富文本编辑器文章信息中的联系方式 QQ 手机号 网址等]
     * @param  [string] $str [过滤之前的字符串]
     * @return [string] $str [过滤之后的字符串]
     */
    public function fifter_text1($str)
    {
        # 针对富文本编辑器中内容的过滤 因为涉及到图片地址 样式 span等标签的限制
        # 有可能因为过滤手机号或QQ号码将文章中图片的路径中数字也过滤掉了 如src="/upload/editor/image/20150106/20150106104956_25558.jpg"
        # 给正则带来一些比较复杂的问题 暂时不做处理 预留方法在这里
        if (empty($str)||trim($str)==""){return $str;}
        $str=strtolower($str);//转小写
        //$str=$this->Sbc2Dbc($str);           //全角转半角
        // $str=$this->fifter_cell($str);       //过滤手机号码
        //$str=$this->fifter_email($str);      //过滤邮箱
        // $str=$this->fifter_qq($str);         //过滤qq号码
        // $str=$this->fifter_number($str);     //过滤五位及其以上
         $str=$this->fifter_a_href($str);     //过滤a标签里面的href值
        // $str=$this->fifter_url_Suffix($str); //过滤文本中的网址后缀
        return $str;
    }
    /**
     * [Sbc2Dbc 将全角字符转为半角字符
     * @param string $str 你需要转换的文本字符串
     * return string $str 转换后的文本字符串
     */
    public function Sbc2Dbc($str)
    {
        //全角变半角 数字特殊形式转阿拉伯数字
        $arr = array(
                    '０'=>'0',
                    '１'=>'1',
                    '２'=>'2',
                    '３'=>'3',
                    '４'=>'4',
                    '５'=>'5',
                    '６'=>'6',
                    '７'=>'7',
                    '８'=>'8',
                    '９'=>'9',
                    'Ａ'=>'A',
                    'Ｂ'=>'B',
                    'Ｃ'=>'C',
                    'Ｄ'=>'D',
                    'Ｅ'=>'E',
                    'Ｆ'=>'F',
                    'Ｇ'=>'G',
                    'Ｈ'=>'H',
                    'Ｉ'=>'I',
                    'Ｊ'=>'J',
                    'Ｋ'=>'K',
                    'Ｌ'=>'L',
                    'Ｍ'=>'M',
                    'Ｎ'=>'N',
                    'Ｏ'=>'O',
                    'Ｐ'=>'P',
                    'Ｑ'=>'Q',
                    'Ｒ'=>'R',
                    'Ｓ'=>'S',
                    'Ｔ'=>'T',
                    'Ｕ'=>'U',
                    'Ｖ'=>'V',
                    'Ｗ'=>'W',
                    'Ｘ'=>'X',
                    'Ｙ'=>'Y',
                    'Ｚ'=>'Z',
                    'ａ'=>'a',
                    'ｂ'=>'b',
                    'ｃ'=>'c',
                    'ｄ'=>'d',
                    'ｅ'=>'e',
                    'ｆ'=>'f',
                    'ｇ'=>'g',
                    'ｈ'=>'h',
                    'ｉ'=>'i',
                    'ｊ'=>'j',
                    'ｋ'=>'k',
                    'ｌ'=>'l',
                    'ｍ'=>'m',
                    'ｎ'=>'n',
                    'ｏ'=>'o',
                    'ｐ'=>'p',
                    'ｑ'=>'q',
                    'ｒ'=>'r',
                    'ｓ'=>'s',
                    'ｔ'=>'t',
                    'ｕ'=>'u',
                    'ｖ'=>'v',
                    'ｗ'=>'w',
                    'ｘ'=>'x',
                    'ｙ'=>'y',
                    'ｚ'=>'z',
                    '（'=>'(',
                    '）'=>')',
                    '〔'=>'(',
                    '〕'=>')',
                    '【'=>'[',
                    '】'=>']',
                    '〖'=>'[',
                    '〗'=>']',
                    '“'=>'"',
                    '”'=>'"',
                    '‘'=>'\'',
                    "'"=>"\'",
                    '｛'=>'{',
                    '｝'=>'}',
                    '《'=>'<',
                    '》'=>'>',
                    '％'=>'%',
                    '＋'=>'+',
                    '—'=>'-',
                    '～'=>'~',
                    '。'=>'.',
                    '、'=>',',
                    '、'=>',',
                    '；'=>';',
                    '？'=>'?',
                    '！'=>'!',
                    '…'=>'-',
                    '‖'=>'|',
                    '”'=>'"',
                    "'"=>"`",
                    '‘'=>'`',
                    '｜'=>'|',
                    '〃'=>'"',
                    '　'=>' ',
                    '×'=>'*',
                    '￣'=>'~',
                    '．'=>'.',
                    '＊'=>'*',
                    '＆'=>'&',
                    '＜'=>'<',
                    '＞'=>'>',
                    '＄'=>'$',
                    '＠'=>'@',
                    '＾'=>'^',
                    '＿'=>'_',
                    '＂'=>'"',
                    '￥'=>'$',
                    '＝'=>'=',
                    '＼'=>'\\',
                    '／'=>'/',
                    '①'=>'1',
                    '②'=>'2',
                    '③'=>'3',
                    '④'=>'4',
                    '⑤'=>'5',
                    '⑥'=>'6',
                    '⑦'=>'7',
                    '⑧'=>'8',
                    '⑨'=>'9',
                    // '零'=>'0',
                    // '壹'=>'1',
                    // '贰'=>'2',
                    // '叁'=>'3',
                    // '肆'=>'4',
                    // '伍'=>'5',
                    // '陆'=>'6',
                    // '柒'=>'7',
                    // '捌'=>'8',
                    // '玖'=>'9'
                    );
        return strtr($str, $arr);
    }
    /**
     * [fifter_url_Suffix 过滤常见的网址后缀
     * @param  [string] $str [过滤前的带网址的字符串]
     * @return [string] $str [过滤后的无网址的字符串]
     */
    public function fifter_url_Suffix($str)
    {
        //过滤网址后缀 .com http qq tel 联系方式
        $arr = array(
                "com.cn"   =>"",
                "net.cn"   =>"",
                "gov.cn"   =>"",
                ".com"     =>"",
                ".net"     =>"",
                ".cn"      =>"",
                ".me"      =>"",
                ".net"     =>"",
                ".org"     =>"",
                ".info"    =>"",
                ".club"    =>"",
                "http://"  =>"",
                "https://" =>"",
                "www."     =>"",
                "www"      =>"",
                "tel"      =>"",
                "Tel"      =>"",
                "qq"       =>"",
                "QQ"       =>"",
                "联系"     =>"",
                "邮箱"     =>"",
                "邮箱地址" =>"",
                "联系方式" =>"",
                );
        return strtr($str,$arr);
    }
    /**
     * [fifter_a_href 过滤a标签
     * @param  [string] $str [过滤前的有a标签href的字符串]
     * @return [string] $str [过滤后的标签href变为#的字符串]
     */
    public function fifter_a_href($str)
    {
        $str= preg_replace("/(?<=href=)([^>]*)(?=>)/i","#", $str);//过滤href
        $str= preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '', $str);//过滤连接
        // $str= preg_replace('/<a[^>].+<\/a>/','',$str);
        return $str;
    }

    /**
     * [fifter_number 过滤5个及其以上的数字
     * @param  [string] $str [过滤前的带有5个及其以上数字的字符串]
     * @return [string] $str [过滤后的带有5个及其以上数字的字符串]
     */
    public function fifter_number($str)
    {
        //过滤qq号码 手机号码
        return preg_replace("/[1-9]{5,11}/","*****",$str);
    }
    /**
     * [fifter_cell 过滤手机号码字符串]
     * @param  [string] $str [过滤前的带有手机号码的字符串]
     * @return [string] $str [过滤后的不带手机号码的字符串]
     */
    public function fifter_cell($str)
    {
        return preg_replace("/1[3,5,8]{1}[0-9]{1}[0-9]{8}|0[0-9]{2,3}-[0-9]{7,8}(-[0-9]{1,4})?/",'', $str);
    }
    /**
     * [fifter_email 过滤邮箱字符串]
     * @param  [string] $str [过滤前的带有邮箱的字符串]
     * @return [string] $str [过滤后的不带邮箱的字符串]
     */
    public function fifter_email($str)
    {
        //过滤电子邮箱
        return preg_replace("/[a-z0-9_]{1,20}[@|#]([a-z0-9]+([\-\_][a-z0-9]+)?)\.+[a-z]{2,3}/i", '',$str);
    }

    /**
     * [fifter_qq 过滤QQ]
     * @param  [string] $str [过滤前的带有QQ的字符串]
     * @return [string] $str [过滤后的不带QQ的字符串]
     */
    public function fifter_qq($str)
    {
        // return preg_replace('/([1-9]{1}[0-9]{7,12})/','', $str);//过滤QQ
        return preg_replace('/([1-9]{1}[0-9]{7,12}+|(2(?!011|012|013|014|015|016|017|018|019|020)\d+))$/','', $str);//过滤QQ
    }

    /**
     * 过滤标题
     */
    public function filter_title($str){
        //转换为半角字符
        // $str = $this->Sbc2Dbc($str);
        //转换为小写
        // $str = strtolower($str);
        //过滤关键字
        $str = strtr($str,$this->titleKeywords);
        //替换特殊字符
        $reg = '/[\`~!@#$%^&*\(\)+<>?"{},.\/;]/';
        $str = preg_replace($reg,"",$str);
        //替换空格
        $reg = '/\s*/i';
        $str = preg_replace($reg,"",$str);
        //替换超过6位长度的数字
        $reg = '/\s*[0-9]{7,}\s*/i';
        $str = preg_replace($reg,"",$str);
        //过滤超链接
        $str = $this->filter_link($str);
        //过滤网址
        $str = $this->filter_url($str);
        return $str;
    }

    /**
     * 过滤特殊字符
     * @param  [type] $reg [description]
     * @return [type]      [description]
     */
    public function filter_specialChar($reg){
        $reg = '/[\`~!@#$%^&*\(\)_+<>?:"{},.\/;\[\]\-]/';
        $str = preg_replace($reg,"",$str);
        return $str;
    }

    /**
     * 过滤400/800电话电话
     * @return [type] [description]
     */
    public function filter_400($str){
        //过滤400电话必须在过滤数字，否侧可能无法过滤干净
        $reg = '/400\-?[0-9]{3}\-?[0-9]{4}|800\-?[0-9]{3}\-?[0-9]{4}/i';
        $str = preg_replace($reg,"",$str);
        return $str;
    }

    /**
     * 过滤超链接
     * @return [type] [description]
     */
    public function filter_link($str){
        //抽出所有的超链接
        $reg = '/<a.*?>(.*?)<\/a>/i';
        preg_match_all($reg, $str, $matches);

        if(count($matches[0]) > 0){
            foreach ($matches[0] as $key => $val) {
                preg_match_all($reg, $val, $m);
                if(count($m[0]) > 0){
                    foreach ($m[0] as $k => $v) {
                        $replaceText = str_replace($v,$m[1][$k], $val);
                    }
                }else{
                    $replaceText = $matches[1][$key];
                }
                $str = str_replace( $val,$replaceText, $str);
            }
        }
        return $str;
    }

    /**
     * 过滤移动电话
     * @return [type] [description]
     */
    public function filter_mobile($str){
        preg_match_all('/\d+/',$str,$matches);
        if(count($matches[0]) > 0){
            //$reg = '/1[3578][0-9]{9}/';
            foreach ($matches[0] as $key => $value) {
               if(strlen($value) == 11){
                  $str = preg_replace('/'.$value.'/',"",$str);
               }
            }
        }

       //
        return $str;
    }

    /**
     * 过滤邮箱
     * @return [type] [description]
     */
    public function filter_mail($str){
        $reg = '/^[A-Za-z\d]+([-_.][A-Za-z\d]+)*@([A-Za-z\d]+[-.])+[A-Za-z\d]{2,5}$/i';
        $str = preg_replace($reg,"",$str);
        return $str;
    }

    /**
     * 过滤QQ
     * @return [type] [description]
     */
    public function filter_qq($str){
        $reg = '/[1-9][0-9]{5,13}/';
        $str = preg_replace($reg,"",$str);
        return $str;
    }

    /**
     * 过滤电话
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    public function filter_tel($str){
        $reg ='/(0[1-9]{3})?\-?[1-9]{7,8}\-?([0-9]{1,4})?/';
        $str = preg_replace($reg,"",$str);
        preg_match_all($reg, $str, $matches);
        return $str;
    }

    /**
     * 过滤网址
     * @return [type] [description]
     */
    public function filter_url($str){
        //先要过滤超链接再过滤网址，否则超链接会被过滤
        //匹配所有的带有网址性质的字符串,因未找到更好的解决办法,暂时这么解决
        //把所有带有网址性质的字符串匹配出来
        $reg = '/(src=[\"|\'])?\s*(http:\/\/|https:\/\/)?[\w]+\.[\w]+\.[\w]{2,3}(\.[\w]{2})?/';
        preg_match_all($reg, $str, $matches);
        if(count($matches[0])){
            foreach ($matches[0] as $key => $value) {
                //查找匹配值是否含有src属性
                if(strpos($value, "src") === false){
                    $str = str_replace( $value,"", $str);
                }
            }
        }
        return $str;
    }

    /**
     * 过滤空格、换行
     * @return [type] [description]
     */
    public function filter_empty($str){
        //替换\R\N换行符号
        $str = preg_replace("/[\r\n|\n|\r]/i", " ", $str);
        //抽出所有的标签
        $reg ='/<.*?\>(.*?)<\/.*?>/i';
        preg_match_all($reg, $str, $matches);
        if(count($matches[0])>0){
            foreach ($matches[0] as $key => $value) {
                //过滤空格
                $pattern = '/\s/';
                // $replaceText = preg_replace_callback($pattern, function($matches){
                //     return "";
                // }, $value);
                //过滤\r\n
                $pattern = '/\r\n/';
                $replaceText = preg_replace_callback($pattern, function($matches){
                    return "";
                }, $value);

                //将所有的单引号替换为空
                $pattern = '/\'/';
                $replaceText = preg_replace_callback($pattern, function($matches){
                    return "";
                }, $replaceText);



                $str = str_replace($value,$replaceText,$str);
            }
        }
        return $str;
    }

    /**
     * 过滤脚本
     * @return [type] [description]
     */
    public function filter_script($str){
        $reg = '/<script.*?>.*?<\/script>/';
        $str = preg_replace_callback($reg,function(){
            return "";
        },$str);
        return $str;
    }

    /**
     * 过滤文本
     * @return [type] [description]
     */
    public function filter_text($str){
        //转换为半角字符
        $str = $this->Sbc2Dbc($str);
        //过滤关键字
        $str = strtr($str,$this->titleKeywords);
        //过滤脚本
        $str = $this->filter_script($str);
        $str = $this->filter_empty($str);
        //过滤超链接
        $str = $this->filter_link($str);
        //过滤400电话
        $str = $this->filter_400($str);
        //过滤手机号码
        $str = $this->filter_mobile($str);
        //过滤电话
        //$str = $this->filter_tel($str);
        //过滤网址
        $str = $this->filter_url($str);
        return $str;
    }

    /**
     * 过滤关键字
     * @return [type] [description]
     */
    public function filter_keywords($str){
        $str = strtr($str,$this->titleKeywords);
        return $str;
    }

    /**
     * 过滤回复的内容
     * @return [type] [description]
     */
    public function filter_recomment($str){
        //过滤空格
        $pattern = '/\s/';
        $str = preg_replace($pattern,"",$str);
        //转换为半角字符
        $str = $this->Sbc2Dbc($str);
        //过滤脚本
        $str = $this->filter_script($str);
        //过滤关键字
        $str = strtr($str,$this->titleKeywords);
        //过滤超链接
        $str = $this->filter_link($str);
        //过滤400电话
        $str = $this->filter_400($str);
        //过滤手机号码
        $str = $this->filter_mobile($str);
        //过滤电话
        $str = $this->filter_tel($str);
        //过滤网址
        $str = $this->filter_url($str);
        return $str;
    }


    /**
     * [过滤回复 通用方法]
     * @param  array  $array [依次要执行的过滤方法]
     * @param  string $str   [要过滤的字符串]
     * @return [string]      [返回过滤后的字符串]
     */
    public function filter_common($str,$array=array())
    {
        $method_list=get_class_methods($this);//获取过滤类的所有方法
        $me_key=array_search(__FUNCTION__, $method_list);//找到当前函数的key
        unset($method_list[$me_key]);//去除当前函数 防止误传当前函数 形成死循环
        //对要调用的方法进行遍历调用 如果该方法不属于系统类的方法 则跳过
        foreach ($array as $key => $v)
        {
            if (!in_array($v,$method_list))
            {
                //不在该类的方法里面 调用错了 直接跳过
                continue;
            }
            $str=$this->$v($str);//依次调用该类的方法去过滤str
        }
        return $str;
    }
}

?>