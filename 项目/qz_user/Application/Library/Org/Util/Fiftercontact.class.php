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
        "ADD"=>"","addr"=>"","ADDR"=>"","add"=>"","地址"=>"","网址"=>"",'赌术揭秘' => '','赌术实战108招' => '',
        '莞式服务' => '','二八杠变牌技巧' => '','国税' => '','查询真伪' => '','商鞅变法用什么牌' => '',
        '淫女' => '','淫色' => '','色网' => '','色站' => '','色女' => '','邪淫' => '','买淫' => '','浴足' => '',
        '足浴按摩' => '','足浴用品' => '','足浴店服务项目' => '','出千扑克产品' => '','出千工具' => '',
        '毕业证编号查询' => '','包小姐' => '','小姐图片' => '','坐台小姐' => '','简单扑克牌魔术教学' => '',
        '怎样用扑克牌变魔术' => '','扑克牌透视眼镜' => '','普通扑克分析仪' => '','透视隐形眼镜' => '',
        '千术免费教学' => '','千术手法' => '','作弊器' => '','骗术大全' => '','透视器' => '',
        '扑克分析软件' => '','语音报牌器' => '','最新扑克感应变牌器' => '','自动麻将变牌' => '','变牌鬼手' => '',
        '扎金花变牌是真的吗' => '','扑克变牌器什么原理' => '','108张麻将打牌技巧' => '','开户' => '',
        '项目加盟' => '','加盟网' => '','找项目' => '','票据理财' => '','资金' => '','股权' => '','证券' => '',
        '暴力' => '','收益' => '','赚钱' => '','虚开发票' => '','真伪查询' => '','12366发票' => '',
        '增值税' => '','中奖查询' => '','认证' => '','税率' => '','农产品收购' => '','抽奖' => '',
        '真假查询' => '','租赁' => '','税控' => '','假职称证' => '','迷幻粉' => '','六级成绩单' => '',
        '英语四级假成绩单' => '','英语六级证书办理' => '','大冶做假签证' => '','办假会计证' => '',
        '刻章办证' => '','假存单' => '','办假护照' => '','乖乖粉' => '','GHB水' => '','观音土' => '',
        '催情粉' => '','強效曲馬多' => '','迷香' => '','昏睡粉' => '','拍肩粉昏睡水' => '',
        '云南情蛊在哪买' => '','傻子药配方' => '','赌博水' => '','医用乙醚价格' => '',
        '哪里有喷雾让人致癌的药出售' => '','什么药吃了可以不知不觉的让人死亡' => '','肉毒毒素效果' => '',
        '办假托福成绩单' => '','快速办证' => '','高防假证' => '','假存折' => '','迷倒烟专卖' => '',
        '性药如何购买' => '','假身份证' => '','发春的药' => '','KKK3' => '','无犯罪记录w4' => '',
        '失明的药' => '','香烟型迷幻药' => '','说真话的药' => '','致癌的药' => '','迷情的烟' => '',
        '英语专四证书' => '','失忆药' => '','假银行存折' => '','假道路运输从业资格证' => '','三唑崙' => '',
        '植物毒' => '','迷姦粉' => '','假大学文凭' => '','高仿房产证' => '','办假专八证书' => '',
        '假二代身份证' => '','小姐' => '','开户' => '','答案' => '','药' => '','少军高科技产品' => '',
        '怎么假洗牌' => '','保分' => '','听话药' => '','失忆水迷情药' => '','安乐死药' => '',
        '安眠药三唑仑' => '','阳痿药失去性功能的药' => '','托福 枪 手'=> '',
        '安眠药吃多少能昏迷' => '','昏迷' => '','催情药水' => '','闻了会晕倒的药' => '','昏倒的药' => '',
        '乙醚喷雾迷晕药' => '','吃了会昏睡的药' => '','让人听话的方法' => '','癌症水让人死亡的药' => '',
        '迷烟迷香药' => '','想赌博的药' => '','迷药水春药' => '','苍蝇水麻醉药' => '','13430296651' => '',
        '扑克牌' => '','真实服务' => '','扑克牌透视眼镜' => '','永平县鸡婆真实找嫖娼按摩一条龙服务上门' => '',
        '太阳城假网包杀' => '','替考' => '','www' => '','ab9999' => '','com开户【客服: 131123 - 99886】公司【 热线: 131123 - 99886】 正网'=> '',
        '替考' => '','枪手' => '','代考' => '','答案' => '','助考' => '','听话的药' => '','迷幻听话水' => '','迷幻药' => '','癌症药' => '','安乐药' => '','黄色' => '','援交' => '','色情 / 情色片色情片'=> '',
        '找服务' => '','上门服务' => '','学生妹' => '','全套服务 / 服务电话'=> '',
        '按摩' => '','洗脚妹' => '','足浴养生' => '','发票' => '','代开' => '','招商' => '','假毕业证' => '','加盟' => '','办证' => '','连锁' => '','赚钱' => '','投资' => '','代理' => '','创业' => '','宝子扑克扫描仪' => '','二八杠手机分析仪' => '','换牌的绝技' => '','扑克背面认牌绝技' => '','麻将监控扫描控牌技术' => '','全自动遥控麻将机' => '','打麻将怎么能赢钱' => '','扑克变牌衣扑克分析仪' => '','硬牌认牌法感应筒子' => '','全自动洗牌麻将机' => '','单人操作语音筒子牌九' => '','三公牛牛报牌器' => '','二八语音报牌器' => '','牌九变牌技巧' => '','牌九变牌技巧' => '','单人操作火机镜头' => '','广洲万博娱乐' => '','深圳赌牌用具' => '','新的科技扑克' => '','深圳高科技牌具' => '','三公必胜' => '','最新科技牌具' => '','赌三公大吃小技术' => '','第六代赌博分析仪' => '','智能报牌麻将机' => '','长沙牌技报牌震动器' => '','赌具扑克扫描仪' => '','gps分析仪' => '','手表透视赌具' => '','高科技赌博出千机器' => '','2016新科技产品' => '','推倒胡麻将技巧心得' => '','小九老秒千道具' => '','2016全新扑克分折仪' => '','夏天如何洗麻将牌' => '','akk夏天镜头' => '','滑袖变牌衣' => '','透视押宝' => '','扑克报牌器真的有用吗' => '','纸牌怎么出老千' => '','偷牌器使用方法' => '','姚记牌扑克解密码' => '','透视押宝' => '','普通麻将机和程序麻将机有什么区别' => '','高科技扑克分析仪镜头' => '','最新最实用的牌技' => '','镜头在哪批发' => '','麻将有变牌器吗' => '','练习千术需要注意哪些' => '','扑克分析仪长沙' => '','麻将变牌器视频' => '','最新出千赌具有什么' => '','德州扑克怎么出千' => '','016一体机扑克分析仪价格' => '','发票' => '','嫖娼' => '','包夜' => '','卖淫' => '','淫秽' => '','透视眼镜' => '','扑克牌' => '','鸡婆' => '','太阳城' => '','替考' => '','枪手' => '','代考' => '','听话' => '','迷幻' => '','安乐' => '','癌症' => '','扑克牌' => '','麻醉' => '','色情' => '','援交' => '','学生妹' => '','全套' => '','洗脚妹' => '','足浴养生' => '','代开' => '','招商' => '','假毕业' => '','连锁' => '','代理' => '','创业' => '','扫描' => '','二八' => '','换牌' => '','扑克' => '','麻将' => '','变牌' => '','筒子' => '','报牌' => '','变牌' => '','火机' => '','镜头' => '','赌牌' => '','扑克' => '','三公' => '','赌博' => '','牌技' => '','分析仪' => '','透视' => '','小九' => '','老千' => '','纸牌' => '','偷牌' => '','千术' => '','扑克' => '',"保障网" => "***","土狗网" => "***","猪八戒" => "***","19楼" => "***","保驾护航网" => "***","装修之家" => "***","58同城" => "***","艾逸网" => "***","装修梦工厂" => "***","好工长网" => "***","人人装修网" => "***","土巴兔" => "***","土拨鼠" => "***","装修宝" => "***","装修界" => "***","高老庄" => "***","装装网" => "***","赶集网" => "***","福牛网" => "***","优居客" => "***","我爱装修网" => "***","爱福窝" => "***","信用家" => "***","蜗牛网" => "***","房保网" => "***","有其屋" => "***","齐家网" => "***","百装网" => "***","搜房网" => "***","保家网" => "***","小蜜蜂装修网" => "***","启装网" => "***","团装网" => "***","比一比" => "***","91装修网" => "***","装酷网" => "***","妈妈网" => "***","房天下" => "***","金居网" => "***","装修123" => "***","啄木鸟装修网" => "***","装饰屋" => "***","家家优宝" => "***","丁丁装修网" => "***","装一网" => "***","好家中国" => "***","乐装网" => "***","防保网" => "***","省多多" => "***","参谋家" => "***","一起装修网" => "***","菜鸟装修网" => "***","便装网" => "***","八戒网" => "***","装修快车" => "***","爱及屋" => "***","装修315" => "***","优装美家" => "***","优保网" => "***","保家网" => "***","快车网" => "***","众易居" => "***","77装修网" => "***","悦装网" => "***","银装网" => "***","窝窝居" => "***","爱装网" => "***","美猴网" => "***","X团装修网" => "***","家家优保" => "***","芒果网" => "***"
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
        $str = $this->filter_sensitive_words($str,array(2,3,4,5));
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
        $str = $this->filter_sensitive_words($str,array(2,3,5));
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
     * 过滤敏感词
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    public function filter_sensitive_words($str,$type)
    {
        $words = S("C:sensitive:words");
        if (!$words) {
            $result = D("SensitiveWords")->getAllWords();
            foreach ($result as $key => $value) {
                $words[$value["type"]][] = $value["word"];
            }
            S("C:sensitive:words",$words,3600*24);
        }

        foreach ($words as $key => $value) {
            if (in_array($key,$type)) {
               foreach ($value as $val) {
                   $replace_words[$val] = "*";
               }
            }
        }
        return strtr(trim($str),$replace_words);
    }


    /**
     * [过滤回复 通用方法]
     * @param  array  $array [依次要执行的过滤方法]
     * @param  string $str   [要过滤的字符串]
     * @return [string]      [返回过滤后的字符串]
     */
    public function filter_common($str,$array=array())
    {
        $method_list = get_class_methods($this);//获取过滤类的所有方法
        $me_key = array_search(__FUNCTION__, $method_list);//找到当前函数的key
        unset($method_list[$me_key]);//去除当前函数 防止误传当前函数 形成死循环
        //对要调用的方法进行遍历调用 如果该方法不属于系统类的方法 则跳过
        foreach ($array as $key => $v)
        {
            if (is_array($v)) {
                $vfunc = $v[0];
                $vfuncv = $v[1];
                $str = $this->$vfunc($str,$vfuncv);//依次调用该类的方法去过滤str
            } else {
                if (!in_array($v,$method_list))
                {
                    //不在该类的方法里面 调用错了 直接跳过
                    continue;
                }
                $str = $this->$v($str);//依次调用该类的方法去过滤str
            }


        }
        return $str;
    }

    /**
     * 过滤网址内容
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    public function filter_html_url($str)
    {
        $reg = '/((http|https)\:\/\/)?[a-z0-9]+\.[a-z0-9]+\.[a-z0-z]+\/?([a-z0-9]+)?/i';
        preg_match_all($reg, $str, $m);

        $str = preg_replace_callback($reg,function(){
            return "";
        },$str);
        return $str;
    }

    /**
     * 过滤文字间的空格
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    public function filter_space($str)
    {
        $pattern = '/\s/';
        $replaceText = preg_replace_callback($pattern, function($matches){
            return "";
        }, $str);
        return $replaceText;
    }
}

?>