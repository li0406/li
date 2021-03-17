<?php
return array(
    //'配置项'=>'配置值'
    'URL_ROUTER_ON' => true,
    'URL_ROUTE_RULES' => array(
        /**********************搜索***********************/
        "search/getsearchlist"  =>  array("Mobile/Search/getSearchList"), //搜索分页
        '/^search\/(\w+)$/' => array("Mobile/Search/search?module=:1"),//搜索结果页
        '/^search$/' =>  array("Mobile/Search/search"), //搜索页

        /**********************搜索***********************/

        // 广告位广告路由
        "pixiushow$" => array("Mobile/Advert/positionAdvert"),

        /**  搜索引擎相关 **/
        // robots.txt
        'robots$' => array("Mobile/SearchEngine/robots", '', array('ext' => 'txt')),
        "zhaoshang$" =>  array("ZhaoShang/index"),       //招商落地页


        "ruzhu$" => array("CompanyConsult/zhaoshang"),//装修公司入驻落地页页面
        'zhaoshang/consult$'=>array('Home/CompanyConsult/consult','',array('method'=>'post')),//装修公司入驻落地页发起咨询接口

        /*** 熊掌号 S **/
        "xzh$" => array("Xiongzhanghao/index"),
        "xzh/baojia$" => array("Xiongzhanghao/baojia"),
        "xzh/details$" => array("Xiongzhanghao/baojia_details"),
        "xzh/company_home/:id\d$" => array("Xiongzhanghao/company_home"),
        "xzh/company_case/:id\d$" => array("Xiongzhanghao/company_case"),
        "xzh/company_team/:id\d$" => array("Xiongzhanghao/company_team"),
        "xzh/company_message/:id\d$" => array("Xiongzhanghao/company_comment"),
        /*** 熊掌号 E **/

        //引导关注微信公众号落地页
        "weixinhuodong" => array("Special/wxGuide"),

        //局部落地页
        "jb" => array("Special/juBuLuoDiYe"),
        "dazhuanpan" => array("Index/dazhuanpan", "verify=1"),//大转盘活动路由
        "prize" => array("Index/prize"),//获取奖品路由
        "city" => array("Index/city", array("go" => "1")), //城市选择页路由
        "action" => array("Index/action"), //移动版切换到主站,加cookie标识
        "fb_order" => array("Zb/fb_order"),//发布订单路由
        "fb_order_sms" => array("Zb/fb_order_sms"),//发布订单路由
        "fb_order_second" => array("Zb/fb_order_second"),//发布订单第二步路由
        "fb_order_company" => array("Zb/fb_order_company"),//招商发布路由
        "fb_order_loan" => array("Zb/fb_order_loan"),//发布订单路由
        "getroundnum" => array("Zb/getroundnum"),//生成随机数
        "muser/orderinfo/id/:id" => array("Muser/index"),
        "gonglue/:category/:id$" => array("Article/index", '', array('ext' => 'html')), //文章详细页路由

        /*******************移动端家具报价发单落地页*******************/
        "qwdzbj$"=> array("JiajuZb/baojia"),//报价页
        "qwdzbj-jsxs$"=> array("JiajuZb/qwdzbj_jsxs"),//2019/02/19家具报价落地页面参数
        "qwdzbj/douyinjiaju$"=> array("JiajuZb/douyinjiaju"),//20190216报价页
        "jiajufb"=> array("JiajuZb/baojiaCalculate"),//报价发单请求
        "qwdbjjg"=> array("JiajuZb/baojiaDetail"),//报价结果页
        "jjqwbj$"=> array("JiajuZb/qwdzbaojia"),//前台移动端-家具全屋定制 报价发单页
        "qwdzbj/gdtfive"=> array("JiajuZb/gdtfive"),//报价页,新增模板 a.18.11.16
        "qwdzbj/toutiaojiaju" => array("JiajuZb/toutiaojiaju"),

        'zxzq_xf' => array('Mobile/Zhixuan/zxzq_xf'),   //新房   
        'zxzq_jf' => array('Mobile/Zhixuan/zxzq_jf'),   // 旧房
        'zxzq_bs' => array('Mobile/Zhixuan/zxzq_bs'),   //别墅
        'zxzq_sy' => array('Mobile/Zhixuan/zxzq_sy'),   //商业办公
        'zxzq' => array('Mobile/Zhixuan/zxzq'),   // 智能首选
        'zxzq_fb_order' => array('Mobile/Zhixuan/fb_order'),   // 智选发单
        
        "gonglue/zs$"=>function(){
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            die();
        },                           //攻略关键词汇总页
        '/^gonglue\/(\d+)$/' => array("Mobile/Zixun/thematic?id=:1"),//攻略关键词页
        "gonglue/lc$" => array("Zixun/zxlc", '', array('ext' => '')), //收房验收路由
        "/^gonglue\/list\-lc\-([1-9]\d*)$/" => array(
            "Zixun/zxlc",
            "",
            array('ext' => 'html')
        ),
        //收房验收路由
        "gonglue/shoufang$" => array("Zixun/zxlclist", array("category" => "shoufang", "type" => "1"), array('ext' => '')),
        "/^gonglue\/list\-shoufang\-([1-9]\d*)$/" => array(
            "Zixun/zxlclist",
            array("category" => "shoufang", "type" => "1"),
            array('ext' => 'html')
        ),
        //找公司路由
        "gonglue/gongsi$" => array("Zixun/zxlclist", array("category" => "gongsi", "type" => "1"), array('ext' => '')),
        "/^gonglue\/list\-gongsi\-([1-9]\d*)$/" => array(
            "Zixun/zxlclist",
            array("category" => "gongsi", "type" => "1"),
            array('ext' => 'html')
        ),
        //量房路由
        "gonglue/liangfang$" => array("Zixun/zxlclist", array("category" => "liangfang", "type" => "1"), array('ext' => '')),
        "/^gonglue\/list\-liangfang\-([1-9]\d*)$/" => array(
            "Zixun/zxlclist",
            array("category" => "liangfang", "type" => "1"),
            array('ext' => 'html')
        ),

        //看风水
        'gonglue/kanfengshui$' => array('Zixun/empty'),
        "/^gonglue\/list\-kanfengshui\-([1-9]\d*)$/" => array('Zixun/empty'),
        //房产知识路由
        "gonglue/fangchan$" => array("Zixun/zxlclist", array("category" => "fangchan", "type" => "1"), array('ext' => '')),
        "/^gonglue\/list\-fangchan\-([1-9]\d*)$/" => array(
            "Zixun/zxlclist",
            array("category" => "fangchan", "type" => "1"),
            array('ext' => 'html')
        ),

        //设计预算路由
        "gonglue/shejiyusuan$" => array("Zixun/zxlclist", array("category" => "shejiyusuan", "type" => "1"), array('ext' => '')),
        "/^gonglue\/list\-shejiyusuan\-([1-9]\d*)$/" => array(
            "Zixun/zxlclist",
            array("category" => "shejiyusuan", "type" => "1"),
            array('ext' => 'html')
        ),
        //装修选材路由
        "gonglue/xuancai$" => array("Zixun/zxlclist", array("category" => "xuancai", "type" => "1"), array('ext' => '')),
        "/^gonglue\/list\-xuancai\-([1-9]\d*)$/" => array(
            "Zixun/zxlclist",
            array("category" => "xuancai", "type" => "1"),
            array('ext' => 'html')
        ),
        //施工阶段 拆改路由
        "gonglue/chagai$" => array("Zixun/zxlclist", array("category" => "chagai", "type" => "2"), array('ext' => '')),
        "/^gonglue\/list\-chagai\-([1-9]\d*)$/" => array(
            "Zixun/zxlclist",
            array("category" => "chagai", "type" => "2"),
            array('ext' => 'html')
        ),
        //施工阶段 水电路由
        "gonglue/shuidian$" => array("Zixun/zxlclist", array("category" => "shuidian", "type" => "2"), array('ext' => '')),
        "/^gonglue\/list\-shuidian\-([1-9]\d*)$/" => array(
            "Zixun/zxlclist",
            array("category" => "shuidian", "type" => "2"),
            array('ext' => 'html')
        ),
        //施工阶段 防水路由
        "gonglue/fangshui$" => array("Zixun/zxlclist", array("category" => "fangshui", "type" => "2"), array('ext' => '')),
        "/^gonglue\/list\-fangshui\-([1-9]\d*)$/" => array(
            "Zixun/zxlclist",
            array("category" => "fangshui", "type" => "2"),
            array('ext' => 'html')
        ),
        //施工阶段 泥瓦路由
        "gonglue/niwa$" => array("Zixun/zxlclist", array("category" => "niwa", "type" => "2"), array('ext' => '')),
        "/^gonglue\/list\-niwa\-([1-9]\d*)$/" => array(
            "Zixun/zxlclist",
            array("category" => "niwa", "type" => "2"),
            array('ext' => 'html')
        ),
        //施工阶段 木工路由
        "gonglue/mugong$" => array("Zixun/zxlclist", array("category" => "mugong", "type" => "2"), array('ext' => '')),
        "/^gonglue\/list\-mugong\-([1-9]\d*)$/" => array(
            "Zixun/zxlclist",
            array("category" => "mugong", "type" => "2"),
            array('ext' => 'html')
        ),
        //施工阶段 油漆路由
        "gonglue/youqi$" => array("Zixun/zxlclist", array("category" => "youqi", "type" => "2"), array('ext' => '')),
        "/^gonglue\/list\-youqi\-([1-9]\d*)$/" => array(
            "Zixun/zxlclist",
            array("category" => "youqi", "type" => "2"),
            array('ext' => 'html')
        ),
        //入住阶段 检测路由
        "gonglue/jianche$" => array("Zixun/zxlclist", array("category" => "jianche", "type" => "3"), array('ext' => '')),
        "/^gonglue\/list\-jianche\-([1-9]\d*)$/" => array(
            "Zixun/zxlclist",
            array("category" => "jianche", "type" => "3"),
            array('ext' => 'html')
        ),
        //入住阶段 配饰路由
        "gonglue/peishi$" => array("Zixun/zxlclist", array("category" => "peishi", "type" => "3"), array('ext' => '')),
        "/^gonglue\/list\-peishi\-([1-9]\d*)$/" => array(
            "Zixun/zxlclist",
            array("category" => "peishi", "type" => "3"),
            array('ext' => 'html')
        ),
        //入住阶段 保养路由
        "gonglue/baoyang$" => array("Zixun/zxlclist", array("category" => "baoyang", "type" => "3"), array('ext' => '')),
        "/^gonglue\/list\-baoyang\-([1-9]\d*)$/" => array(
            "Zixun/zxlclist",
            array("category" => "baoyang", "type" => "3"),
            array('ext' => 'html')
        ),
        //入住阶段 家具生活
        "gonglue/jjsh$" => array("Zixun/zxlclist", array("category" => "jjsh", "type" => "3"), array('ext' => '')),
        "/^gonglue\/list\-jjsh\-([1-9]\d*)$/" => array(
            "Zixun/zxlclist",
            array("category" => "jjsh", "type" => "3"),
            array('ext' => 'html')
        ),


        //全屋定制
        "gonglue/qwdz" => array("Zixun/zxlclist", array("category" => "qwdz", "type" => "3"), array('ext' => '')),
        //智能家居
        "gonglue/znjj" => array("Zixun/zxlclist", array("category" => "znjj", "type" => "3"), array('ext' => '')),


        //选材导购
        "gonglue/xcdg$" => array("Zixun/xcdg", array("category" => "xcdg"), array('ext' => '')),//seo需求-选材导购-new
        "/^gonglue\/list\-xcdg\-([1-9]\d*)$/" => array(
            "Zixun/xcdg",
            array("category" => "xcdg"),
            array('ext' => 'html')
        ),

        //新家选材分类路由列表
        "gonglue/diban$" => array("Zixun/xcdg", array("category" => "diban", 'categoryId' => 145), array('ext' => '')),
        "/^gonglue\/list\-diban\-([1-9]\d*)$/" => array(
            "Zixun/xcdg",
            array("category" => "diban", 'categoryId' => 145),
            array('ext' => 'html')
        ),
        //装修选材导购，要放到下面的路由前面
        "/^gonglue\/list\-([a-zA-Z]+)\-([1-9]\d*)$/" => array(
            "Zixun/lclist?category=:1",
            "",
            array('ext' => 'html')
        ),

        //353 P2.19.0 活动专题页及后台管理
        "hdzt$"=>array("Zt/hdzt"),  //列表页
//        "hdzt/:id"=>array("Zt/hdztdetail"),     //详情页
        "hdzt/:id"=>array("Zt/hdztdetail",'',array('ext'=>'html')),     //详情页

        //1097 PC&M&APP新增活动专题页
        //"activity818$"=>array("Zt/activity818"),      //1249 隐藏PC、M端818活动相关内容
        //"activityrule"=>array("Zt/activityrule"),     //1249 隐藏PC、M端818活动相关内容

        // 1117 移动端平台活动制作新落地页
        //"hd818$"     =>array('Zt/hd818'),      //1249 隐藏PC、M端818活动相关内容

        // 1145 复制818活动页面，并添加js代码
        "qzw/hd818/tteight$"     =>array('Zt/tteight'),      //1249 隐藏PC、M端818活动相关内容

        // 1325 全民家装双11活动页面
        "activity11$"     =>array('Zt/activity11'),

        //装修指南
        'zhinan$' => ['Zixun/zxzn', [], ['ext' => '']],
        '/^zhinan\/list\-([1-9]\d*)$/' => ['Zixun/zxzn', [], ['ext' => 'html']],

        "gonglue/:category$" => array("Zixun/lclist", '', array('ext' => '')),//装修选材导购
        "gonglue$" => array("Zixun/index", '', array('ext' => '')), //移动版资讯页路由

        ":bm/xgt$" => array("Xiaoguotu/index", '', array('ext' => '')), //移动版效果图页面
        ":bm/company_message/:id" => array("Companyhome/commenttwo"), //移动版装修公司评论页面
        ":bm/blog/[:id]$" => array("Blog/indextwo"), //移动版设计师博客页
        ":bm/blog_new/[:id]$" => array("Blog/indextwonew"), //新签返设计师博客页
        ":bm/company_team/:id" => array("Companyhome/teamtwo"), //移动版装修公司案例页路由

        "getcommentlistbyid" => array("Companyhome/getCommentById"), //移动版装修公司案例页路由
        "getteamlistbyid" => array("Companyhome/getTeamById"), //移动版装修公司案例页路由
        "getbloglistbyid" => array("Blog/getBlogById"), //移动版装修公司案例页路由

        "zxgstj" => array("Company/companylandpage"), //装修公司新版落地页
        "jxxgt" => array("Company/jxxgtlandpage"),//效果图落地页
        "getLandList" => array("company/getLandList"),//效果图落地页
        "addNum" => array("company/addNum"),//效果图落地页

        "zxbaojia" => array("Company/spacelandpage"),//空间装修落地页
        "zhuangxiu" => array("Company/plateformlandpage"),//平台落地页
        "pinpai" => array("Company/pinpai"),//品牌落地页
        ":bm/caseinfo/:id" => array("Cases/indextwo", '', array('ext' => 'shtml')), //移动版装修公司案例页路由
        ":bm/company_case/[:id]$" => array("Companyhome/casestwo"), //移动版装修公司案例页路由
        ":bm/company_home/:id\d$" => array("Companyhome/indextwo", '', array('ext' => '')), //移动版装修公司主页路由
        ":bm/company_home/:id/baojia$" => array("Companyhome/details",'', array('ext' => '')),//装修公司装修报价详细页面
        ":bm/company" => array("Company/indextwo"), //移动版装修公司列表路由
//        ':bm/^company\/list-(fu[\d+]+f[\d+]+g[\d+]+bz[\d+]+(o[\d+]+)?(i[\d+]+)?(p[\d+]+)?)$/' => array("Company/indextwo"), //移动版装修公司列表路由题
        ":bm/gonglue" => array("Zixun/index"), //移动版文章页路由
        "xgt" => array("Xiaoguotu/index"), //移动版效果图页面路由
        "company$" => array("Company/index", '', array('ext' => '')), //移动版装修公司列表路由
         //m.2.12.1m站装修公司改版 新增路由
        ":bm/company_about/:id\d$" => array("Companyhome/about" ), //移动版公司信息
        ":bm/event_view/:id" => array("Company/event", '', array('ext' => 'html')), //移动版活动详情

        // ---老版本的报价和设计---
        "mzb" => array("Zb/index"), //移动端招标页路由
        "zhaobiao$" => array("Zb/index"), //新版本移动端招标页报价方案路由

        // --- 设计相关 ---
        "sheji$" => array("Zb/sheji", '', array('ext' => '')), //新版本移动端招标页设计方案路由
        "sheji-djwl$" => array("Zb/sheji_djwl", '', array('ext' => '')), //565 落地页进行数据上报 2019/12/3
        "sheji-xcx$" => array("Zb/sheji_xcx", '', array('ext' => '')), //复制设计页面 删除定位功能
        "sheji-1" => array("Zb/sheji_1", '', array('ext' => '')),
        "shejidone" => array("Zb/shejidone"), //新版本移动端招标页设计方案路由
        "shejidone-dyqd" => array("Zb/shejidone_dyqd"), //sheji-dyqd-2 设计完成页面
        "shejidone-noapp" => array("Zb/shejidone_noapp"), //新版本移动端招标页设计方案路由
        "shejidone2" => array("Zb/shejidone2"),//设计弹窗完成页面
        "sheji-2"                 =>    array("Zb/sheji_paste",'',array('ext'=>'')),
        "sheji-zjlj$" => array("Zb/sheji_zjlj", '', array('ext' => '')), //20190117 新增设计页面（banner更换）
        "sheji-szxwt$" => array("Zb/sheji_szxwt", '', array('ext' => '')), // 718 复制设计落地页，并添加数据回传功能
        "mjsj$" => array("Zb/mjsj", '', array('ext' => '')), //20190117 新增设计页面（banner更换）
        "qzw/sheji-zfb$" => array("Zb/sheji_zfb", '', array('ext' => '')), //980 移动端落地页复制，并修改部分内容
        //a.19.07.14
        "mjsj1$"=>array("Zb/mjsj1", '', array('ext' => '')),// 新增设计页面（banner更换）
        "sheji-jzrk-gzf"=>array("Zb/sheji_gzf","",array('ext'=>'')), // a.19.08.13 复制生成户型设计渠道落地页
        // --- 卡券相关 ---
        "cardlogin" => array("Card/login"),
        "cardapply" => array("Card/apply"),
        "cardorder" => array("Card/order"),
        "couponin" => array("Card/couponin"),
        "coupontake/:cardid" => array("Card/coupontake"),
        "getcompanybyorderid"=>array("Card/getSpecialCardByOrderId"),
        "card/coupontsuccess" => array("Card/coupontsuccess"),
        "getspecialcardinfobyid" => array('Card/getspecialcardinfobyid'),

        // --- 报价相关 ---
        "baojia$"       => array("Zb/baojia", '', array('ext' => '')),        //移动端智能报价路由
        "baojia-ywwx$"       => array("Zb/baojia_ywwx", '', array('ext' => '')),        //新增报价去除外链 2019/10/25
        "baojia-ywwx1$"       => array("Zb/baojia_ywwx1", '', array('ext' => '')),        //984 移动端复制报价页+短信验证 2020/7/4
        "baojia-zswx$"       => array("Zb/baojia_zswx", '', array('ext' => '')),        //新增报价,数据上报 2019/10/26
        "baojia-gzf$"       => array("Zb/baojia_gzf", '', array('ext' => '')),        //20190814 报价渠道落地页
        "baojia-zhtwo"       => array("Zb/baojia_zhtwo", '', array('ext' => '')),        //知乎推广报价页，用模板
        "baojia-zst$"       => array("Zb/baojia_zst", '', array('ext' => '')),        //移动端智能报价路由
        "qudao-baojia$" => array("Zb/baojia_qd", '', array('ext' => '')),        //移动端渠道报价路由
        "baojia_dm$"    => array("Zb/baojia_dm", '', array('ext' => '')),          //移动端智能报价路由
        "baojia-1$"     => array("Zb/baojia_1",'',array('ext'=>'')),
        "baojia-2$"     => array("Zb/baojia_2",'',array('ext'=>'')),
        "baojia-dx"     => array("Zb/baojia_dx"),//0元报价落地页  m.2.10.0 0元报价落地页
        "baojia-dx-1"     => array("Zb/baojia_dx_1"),//0元报价落地页  m.2.10.0 0元报价落地页
        "baojiawanshan" => array("Zb/wanshan"),//报价完善页面
        "details" => array("Zb/details"),//装修报价详细页面
        "details-ywwx" => array("Zb/details_ywwx"),//新增报价去除外链 2019/10/25
        "details-zst" => array("Zb/details_a18102"),//装修报价详细页面
        "getdetailsinfo" => array("Zb/getdetailsinfo"),// 老版 ajax获取装修报价详细
        "getdetailsbyajax"=>array("Zb/getDetailsByAjax"),// ajax获取详细报价页面路由
        "baojia-hzhjbj"=>array("zb/hzhjbj"), // 350 配合渠道商新落地页
        //P.2.18.0 （PC+m）新增装修知识页面
        '/^zhishi(\/p\d+)?$/' => array("Home/Thematic/index"),    //标签页路由
        '/^zhishi\/(\d+)(\/p\d+)?$/' => array("Home/Thematic/thematicList?id=:1&p=:2"),    //标签内容列表页
        '/^tu\/(\d+)(\/p\d+)?$/' => array("Home/Thematic/thematictulist?id=:1&p=:2"),    //图片标签内容列表页

        //新报价
        "newbaojia$"=>array("Zb/newbaojia", '', array('ext' => '')),// 新增报价 首页
        "jznewbaojia"=>array("Zb/jznewbaojia", '', array('ext' => '')),// 新增精准报价 中间页
        "newbaojiasuccess"=>array("Zb/newbaojiasuccess", '', array('ext' => '')),// 新增精准报价 结果页

        //a.19.4.1 报价2
        "baojia2$"=>array("Zb/baojia2", '', array('ext' => '')),// 新增报价 首页
        "jzbaojia2"=>array("Zb/jzbaojia2", '', array('ext' => '')),// 新增精准报价 中间页
        "baojia2success"=>array("Zb/baojia2success", '', array('ext' => '')),// 新增精准报价 结果页

        //m2.11.31 落地页
        "baojia3$"=>array("Zb/baojia3", '', array('ext' => '')),// 新增报价 首页

        // 1078 复制一套报价页面 （报价页—完善信息页—报价完成页）
        "baojia1cj$"=>array("Zb/baojia1cj", '', array('ext' => '')),// 新增报价 首页
        "jzbaojia1-cj"=>array("Zb/jzbaojia1_cj", '', array('ext' => '')),// 新增精准报价 中间页
        "baojiasuccess1-cj"=>array("Zb/baojiasuccess1_cj", '', array('ext' => '')),// 新增精准报价 结果页

        //新报价20181119
        "baojia1$"=>array("Zb/baojia1", '', array('ext' => '')),// 新增报价 首页
        "baojia1-yw$"=>array("Zb/baojia1_yw", '', array('ext' => '')),// 新增报价去除外链 2019/10/25
        "baojia1-ng$"=>array("Zb/baojia1_ng", '', array('ext' => '')),// 325  复制报价落地页：去掉APP链接，更改底部信息
        "baojia1-wuapp$"=>array("Zb/baojia1_wuapp", '', array('ext' => '')),// 新增报价(去除app入口) 20190415
        "details-wuapp$"=>array("Zb/details_wuapp", '', array('ext' => '')),// 新增报价发单成功页(去除app入口) 20190415
        "jzbaojia1"=>array("Zb/jzbaojia1", '', array('ext' => '')),// 新增精准报价 中间页
        "jzbaojia1-yw"=>array("Zb/jzbaojia1_yw", '', array('ext' => '')),// 新增报价去除外链 2019/10/25 中间页
        "baojiasuccess1"=>array("Zb/baojiasuccess1", '', array('ext' => '')),// 新增精准报价 结果页
        "baojiasuccess1-yw"=>array("Zb/baojiasuccess1_yw", '', array('ext' => '')),// 新增报价去除外链 2019/10/25 结果页
        "baojia1-jzrk$"=>array("Zb/baojia1jzrk", '', array('ext' => '')),         //底部按钮发单成功页 20181211
        "baojia1-details"=>array("Zb/baojia1details", '', array('ext' => '')),         //新增报价完善页 20181224
        "baojia1success"=>array("Zb/baojia1success", '', array('ext' => '')),// 新增报价 结果页  20181224
        "baojia-result"=>array("Zb/baojia_result", '', array('ext' => '')),//  报价结果页
        "sheji-result"=>array("Zb/sheji_result", '', array('ext' => '')),//  设计结果页
        "xgs-result"=>array("Zb/xgs_result", '', array('ext' => '')),//  选择装修公司结果页
        "ruzhu-result"=>array("Zb/ruzhu_result", '', array('ext' => '')),//  商家入驻结果页         // 新设计页20181123
        "sheji-jzrk$"=>array("Zb/shejijzrk", '', array('ext' => '')),// 新增设计 首页
        "sheji-jzrk-yh$"=>array("Zb/shejijzrk_yh", '', array('ext' => '')),// 619 复制落地页，并修改部分内容
        "sheji-jzrk-yw$"=>array("Zb/shejijzrk_yw", '', array('ext' => '')),// 464 移动端相关报价页面去除部分文字及部分页面复制
        "sheji-jzrk1$"=>array("Zb/shejijzrk1", '', array('ext' => '')),// 567 复制落地页
        "details-jzrk"=>array("Zb/details_jzrk", '', array('ext' => '')),// 新增设计发单成功页  中间页
        "details-jzrk-shxs"=>array("Zb/details_jzrk", '', array('ext' => '')),// 新增设计发单成功页  中间页
        "sheji-jzrk-shxs$"=>array("Zb/shejijzrk_shxs", '', array('ext' => '')),// 新增设计 首页
        "sheji-jzrk-zswx$"=>array("Zb/shejijzrk_zswx", '', array('ext' => '')),// 新增设计

        // 新设计页20181128
        "sheji-dyqd$"=>array("Zb/shejidyqd", '', array('ext' => '')),// 新增设计 首页
        // 新设计页20181217
        "sheji-dyqd-2$"=>array("Zb/shejidyqd_2", '', array('ext' => '')),// 新增设计 首页
        "sheji-bjlt$" => array("Zb/sheji_bjlt", '', array('ext' => '')),// 新增设计 首页 a.19.03.06
        "shejidone-bjlt$" => array("Zb/shejidone_bjlt", '', array('ext' => '')),// 新增设计 首页 a.19.03.06
        // 新设计页20181123
        "baojia-jzrk$"=>array("Zb/baojiajzrk", '', array('ext' => '')),// 新增报价 首页


        "getLocation" => array("Index/getLocation"), //获取地理信息路由
        "getcitybm" => array("Index/getcitybm"), //获取地理信息路由
        "getbmbycityname" => array("Index/getbmbycityname"), //获取地理信息路由
        "sendsms" => array("Index/sendsms", "verify=1"),//发送短信路由
        "sendsmscode$" => array("Index/sendsmscode", "verify=1"),//发送短信验证码路由
        "verifysmscode" => array("Index/verifysmscode", "verify=1"),//验证码路由
        "specialuser" => array("Index/specialuser", "verify=1"),
        "prize" => array("Index/prize", "verify=1"),
        "refresh" => array("Index/refresh"),//刷新验证码路由
        "getCityInfoByName" => array("Index/getCityByCityName"),//百度地图获取地址信息

        "wenda$" => array("Wenda/index", '', array('ext' => '')),
        '/^wenda\/ask-([0-9]+)$/' => array("Wenda/index?id=:1"),//问答列表路由
        '/^wenda\/ask-([0-9]+)(\w+)$/' => array("Wenda/index?id=:1&sort=:2"),   //问答列表路由 可以优化一下
        '/^wenda\/x([0-9]+)$/' => array("Wenda/show?id=:1", '', array('ext' => 'html')),      //问答详细页
        "wenda/search" => array("Wenda/index"),                   //搜索

        'baike/zs$' => function(){
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            die();
        },             //百科关键词汇总页
        "baike$" => array("Baike/index", '', array('ext' => '')),                   //搜索
        "baike/rss$" => array("Baikerss/category", '', array('ext' => '')),     //百科RSS订阅发布
        "baike/indexdev" => array("Baike/indexdev"),                   //搜索
        '/^baike\/bk([0-9]+)$/'=>array("Mobile/Baike/thematic?id=:1"),
        '/^baike\/([0-9]+)$/' => array("Baike/show?id=:1", '', array('ext' => 'html')),          //百科详细页
        '/^baike\/([a-z]+)$/' => array("Baike/category?id=:1"),      //百科分类页
                 //百科关键词页

        "riji$" => array("Riji/index", '', array('ext' => '')),
        '/^riji\/c([0-9]+)$/' => array("Riji/index?category=:1"),
        '/^riji\/d([0-9]+)$/' => array("Riji/show?id=:1", '', array('ext' => 'html')),
        "rijidev" => array("Riji/rijidev"),
        "rijidetail" => array("Riji/rijidetail"),

        'huangli$' => array("Huangli/index", '', array('ext' => '')),
        'hl$' => array("Huangli/zxhl", '', array('ext' => '')),
        ":bm/hl" => array("Huangli/zxhl"), //移动版装修公司列表路由
        '/^huangli\/bj([0-9]+)$/' => array("Huangli/show?type=:1"),

//        'meitu$' => array("Meitu/index", '', array('ext' => '')),//美图列表路由
//        '/^meitu\/p(\d+)$/' => array("Meitu/show?p=:1", '', array('ext' => 'html')),//美图详情页路由
//        '/^meitu\/list-([a-z0-9]+)$/' => array("Meitu/meitulist", '', array('ext' => '')),//美图列表路由
//        'meitu/list' => array("Meitu/meitulist"), //美图列表路由
        '/^meitu\/newMeiTuList-([a-z0-9]+)$/' => array("Meitu/newMeiTuList"),//新的美图列表路由
        'meitu/newMeiTuList' => array("Meitu/newMeiTuList"),//新的美图列表路由
//        'meitu/gongzhuang$' => array("Pubmeitu/pubMeituList"),//公装美图列表路由
//        '/^meitu\/gongzhuang-(l[\d+]+f[\d+]+m[\d+]+(p[\d+]+)?+(q[\d+]+)?)$/' => array("Pubmeitu/pubMeituList"),//公装美图列表路由
//        '/^meitu\/g(\d+)$/' => array("Pubmeitu/show?id=:1", '', array('ext' => 'html')),     //工装美图详情页路由
        'meitu/like' => array("Meitu/like"),

        "about" => array("Special/about", '', array('ext' => 'html')),//微信专页


        'getPartnerad$' => array('Mobile/Partner/getPartneradInfo'),
        'addPartneradClick$' => array('Home/Partner/addPartneradClick'),
//        'zt$' => array("Meitu/meituztlist"), (a.18.06.02 需求删除,已认证)
//        'meitu/meituztlist$' => array("Meitu/meituztlist"),
//        'meitu/zt$' => array("Meitu/meituztlist"),
//        'zt/[:id]' => array("Meitu/meituztdetails"),
        'zt/baoming' => array("Zt/baoming"),//老客户活动报名页
        "zt/baomingok" => array("Zt/baomingok"),  //报名结果页
        "zt/complain" => array("Zt/complain"),  //报名结果页
        "zt/complain" => array("Zt/complain"),  //报名结果页 2018-3-19
        '/^zt\/([a-z]+)$/' => array("Zt/special?name=:1"),//专题详细页

        "video/jiangtang" => array("https://m.qizuang.com/video/", 301),//视频讲堂301
        "video/toutiao" => array("https://m.qizuang.com/video/", 301),//视频头条路由
        '/^video\/v(\d+)$/' => array("Mobile/Video/terminal?id=:1", '', array('ext' => 'html')),//工装美图详情页路由
        "video/likeaction" => array("Video/likeAction"),  //报名结果页
        "video/:category$" => array("Mobile/Video/index", '', array('ext' => '')),//视频分类
        "video$" => array("Mobile/Video/index", '', array('ext' => '')),//视频讲堂路由

        '/^zhuanti\/([0-9]+)$/' => array("Articlespecial/module?id=:1"),//专题模块路由
        "zhuanti/:category/:id" => array("Mobile/Articlespecial/terminal", '', array('ext' => 'html')),//文章路由
        "zhuanti/likeaction" => array("Mobile/Articlespecial/likeAction"),//文章路由
        'dolike' => array("Article/dolike"),//文章喜欢路由
        'go' => array("Go/index"),//推广跳转路由
        // 'hm' => array('Index/hm'),
        "act/qixi" => array("Mobile/Special/qixi"),//七夕
        "act/guoqing" => array("Mobile/Special/guoqing"),//国庆
        "act/zhuli" => array("Mobile/Special/zhuli"),//助力
        "act/zhulijin" => array("Mobile/Special/zhulijin"),//助力金
        "act/rules" => array("Mobile/Special/rules"),//助力规则
        "act/xinyunqiang" => array("Mobile/Special/xinyunqiang"),//幸运抢
        "act/updateUserInfo" => array("Mobile/Special/updateUserInfo"),//更新用户信息
        "act/updateMoney" => array("Mobile/Special/updateMoney"),//更新用户金钱
        "act/funcBack" => array("Mobile/Special/funcBack"),//回调
        "act/xinyunqiangFuncBack" => array("Mobile/Special/xinyunqiangFuncBack"),//幸运抢回调
        "activity/huanxinjia" => array("Mobile/Special/huanxinjia"),//幸运抢回调
        "activity/travel" => array("Mobile/Special/travel", '', array('ext' => '')),//港澳豪华游
        "activity/coupon$" => array("Zb/sheji", '', array('ext' => '')),//大礼包免费领
        "activity/qudao/:id" => array("Mobile/Special/qudao", '', array('ext' => 'html')),//流量活动推广
        "activity/zxj" => array("Mobile/Special/zxj"),//12月活动
        "activity/coupon-2" => array("Zb/sheji", '', array('ext' => '')),//12月活动,2万元装修礼包
        "activity/coupon-3" => array("Zb/sheji", '', array('ext' => '')),//移动端活动广告落地页增加效果优化JS
        "activity/coupon-4" => array("Zb/sheji", '', array('ext' => '')),//移动端活动广告落地页增加效果优化JS
        "activity/coupon-5" => array("Zb/sheji", '', array('ext' => '')),//移动端活动广告落地页增加效果优化JS
        "activity/coupon-6" => array("Zb/sheji", '', array('ext' => '')),//移动端活动广告落地页增加效果优化JS
        "activity/result" => array("Mobile/Special/successResult"),//移动端活动广告落地页增加效果优化JS
        "activity/zxj-1" => array("Mobile/Special/zxj_zhengshi"),//十二月活动(正式)
        "activity/voucher-1"=>array("Mobile/Activity/voucher_paste"),
        "activity/voucher-hgj" => array("Mobile/Activity/voucher_hgj"),
        "activity/hengda" => array("Mobile/Activity/hengda"),
        ":bm/activity/suning" => array("Mobile/Special/suning"),//12月苏宁活动
        "fh2019/fx1" => array("Mobile/Special/fx1"),// 20190114新增2019装企未来分享会
        "baojiazk$" => array("Mobile/Special/zk"), //复制生成腾讯新闻媒体落地页
        "yuding$"  => array("Home/Special/yuding"),    //短信推送落地页
        "addyuding$"  => array("Home/Special/addyuding"),    //短信推送落地页提交接口
        "mthd$" => array("Mobile/Special/mthd"), //238 渠道落地页上线美团合作落地页
        "lyzxhd$" => array("Mobile/Special/lyzxhd"), //479
        "qzjzj$" => array("Mobile/Special/qzjzj"), // 齐装家装季
        "qzjzj1$" => array("Mobile/Special/qzjzj1"), // 1296 复制页面&修改落地页面
        "qzjzj_zfb$" => array("Mobile/Special/qzjzj_zfb"), // 549 复制落地页并删除部分内容
        "qzjzj-zswx$" => array("Mobile/Special/qzjzj_zswx"), // 455复制落地页，并进行数据上报  2019/10/26
        "qzjzj-shxs$" => array("Mobile/Special/qzjzj_shxs"), // 20191016复制落地页，在新落地页上添加手机验证码功能
        "qzjzj-zfbzj$" => array("Mobile/Special/qzjzj_zfbzj"), // 777 M端复制落地页去除定位授权
        "qzjzj-details" => array("Mobile/Special/qzjzj_detail"), // 齐装家装季
        "qzjzj1-details" => array("Mobile/Special/qzjzj1_details"), // 1296 复制页面&修改落地页面
        "qzjzj-zfbdetails" => array("Mobile/Special/qzjzj_zfbdetail"), // 549 复制落地页并删除部分内容
        "czjzj" => array("Mobile/Special/czjzj"), // 超值家装季
        "jzdlb" => array("Mobile/Special/jzdlb"), // 超值家装季 广告修正版
        "czjzj-yw$" => array("Mobile/Special/czjzj_yw"), // 464 移动端相关报价页面去除部分文字及部分页面复制
        "czjzj-details" => array("Mobile/Special/czjzj_detail"), // 超值家装季
        "jzdlb-details" => array("Mobile/Special/jzdlb_detail"), // 超值家装季 广告修正版
        "about/disclaimer" => array("Mobile/About/getLegal"),//免责声明
        "about/disclaimer-no" => array("Mobile/About/getLegal_no"),//复制无链接无下载免责声明
        "about/disclaimer-new" => array("Mobile/About/getLegal_new"),  // 1047 制作2个装小七发单落地页（免责声明页面）
        "about/disclaimer-zx" => array("Mobile/About/getLegal_zx"),//1126 复制2个齐装落地页，在taozuang项目上使用

        "about/quanyi" => array("Mobile/About/quanyi"),//权益保护
        "about/disclaimer-zst" => array("Mobile/About/getLegal_zst"),//复制无链接免责声明
        "about/about" => array("Mobile/About/about"),// 关于我们

        "activity$" => array("Activity/index", '', array('ext' => '')),
        "xzh/baojia" => array("Mobile/Xiongzhanghao/baojia"),//百度熊掌号报价页
        "xzh/details" => array("Mobile/Xiongzhanghao/baojia_details"),//百度熊掌号报价页
        ":bm/xzh/company$" => array("Mobile/Xiongzhanghao/index"),//熊掌号装修公司列表页
        ":bm/xzh/company_home/:id" => array("Mobile/Xiongzhanghao/company_home"),//熊掌号装修公司主页
        ":bm/xzh/company_case/:id" => array("Mobile/Xiongzhanghao/company_case"),//熊掌号装修公司案例页
        ":bm/xzh/company_team/:id" => array("Mobile/Xiongzhanghao/company_team"),//熊掌号装修公司设计团队页
        ":bm/xzh/company_message/:id" => array("Mobile/Xiongzhanghao/company_comment"),//熊掌号装修公司设计团队页
        "qud" => array("Mobile/Newfadan/index"),//发单2.0

        "gzsjbj$" => array("Mobile/GongZ/index"),//公装发单页
        "gzbj$" => array("GongZ/gzbj"), //m端公装报价落地页 p.2.17.7
        "gzbjjg$" => array("GongZ/gzbjjg"), //m端公装报价落地页报价结果页


        ':bm/zxinfo/:id\d' => array("Zxinfo/details", '', array('ext' => 'html')),
        ":bm/zxinfo/:category$" => array("Zxinfo/index", '', array('ext' => '')),
        //":bm/zxinfo/list-/:category-:\d"           =>  array("Zxinfo/index",'',array('ext'=>'html')),

        "/^(\w+)\/zxinfo\/list-(\d+)$/" => array("Zxinfo/index?bm=:1&page=:2", '', array('ext' => 'html')),
        "/^(\w+)\/zxinfo\/list-(\w+)-(\d+)$/" => array("Zxinfo/index?bm=:1&category=:2&page=:3", '', array('ext' => 'html')),


        ":bm/zxinfo$" => array("Zxinfo/index"),
        ":bm/baojia$" => array("Zb/ctbaojia",'', array('ext' => 'html')), // 分站报价页
        "hongbao" => array("Zb/hongbao"),

        /**************************qzlab 线上实验室 一般用于线上的直接测试******************************/
        "qzlab/bdt1"  => array("Mobile/QzLab/bdt1", '', array('ext' => 'html')), // 用于商务DB测试动态js 广告 运营劫持加入新广告 391 m站装修攻略详情页底部增加广告入口
        "qzlab/pixiu" => array("Mobile/QzLab/pixiu", '', array('ext' => 'html')), // 一页集中展示不同的广告


        /**静态设计师页面**/
        '/^designer\/(\d+)$/' => array("Mobile/Designer/index?id=:1", '', array('ext' => 'html')),
        //局部落地页
        "quanbao"              =>    array("Company/quanbao"), //装修类型落地页

        //问答知识  p.2.16.20
        "wenda/zs$" => function(){
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            die();
        },  //问答知识频道

        /**p.2.16.20 问答知识专题页*/
        '/^wenda\/(\d+)$/'  => array('Mobile/BaikeSubject/index?id=:1'),

        //全屋定制落地页
        "jjqwdz$"              =>    array("Qwdz/index"), //全屋定制落地页
        "jjqwdz-dx$"           =>    array("Qwdz/jjqwdz_dx"), //全屋定制落地页(2019/3/7删除顶部的【咨询】和【菜单】按钮)
        "jjqwdzqd$"              =>    array("Qwdz/jjqwdzqd"), //全屋定制落地页
        'qwdz_fb' =>array("Qwdz/qwdzCreateOrder"),  //全屋定制发单

        //服务端定位
        'ldyiplocation'=>['Luodiye/getIpLocation'],

        // 844 综合发单页
        "jisuanqi$"     =>array('Luodiye/jisuanqi'),
        "jisuanqi-success"     =>array('Luodiye/jisuanqi_success'),
        "jisuanqi-result"     =>array('Luodiye/jisuanqi_result'),

        // 949 复制滑动落地页，按照新设计稿制作
        "qzw/zxbj$"     =>array('Luodiye/zxbj'),
        "zxbj-result"     =>array('Luodiye/zxbj_result'),

        // 950 八大服务保障落地页
        "zxfw"     =>array('Luodiye/zxfw','',array('ext'=>'html')),

        // 955 制作0元户型设计落地页
        "qzw/xhxsj$"     =>array('Luodiye/xhxsj'),
        "xhxsj-result"     =>array('Luodiye/xhxsj_result'),

        // 1066 做一个渠道推广链接
        "qzw/sheji-xy$"     =>array('Luodiye/sheji_xy'),

        // 1092 复制一套设计发单页，修改顶部广告图。
        "qzw/sheji-xy1$"     =>array('Luodiye/sheji_xy1'),

        // 1038 复制4个报价落地页并做相应调整
        "qzw/xbaojia$"       => array("Luodiye/xbaojia", '', array('ext' => '')),    //新装修报价页面
        "xdetails" => array("Luodiye/xdetails"),           //新装修报价详细页面
        "qzw/xbaojia1$"       => array("Luodiye/xbaojia1", '', array('ext' => '')),    //新装修报价页面
        "qzw/xbaojia2$"       => array("Luodiye/xbaojia2", '', array('ext' => '')),    //新装修报价页面
        "qzw/xbaojia3$"       => array("Luodiye/xbaojia3", '', array('ext' => '')),    //新装修报价页面

        // 1047 制作2个装小七发单落地页
        "qzw/zxbaojia1$"       => array("Luodiye/zxbaojia1", '', array('ext' => '')),    //新装修报价页面
        "qzw/zxbaojia2$"       => array("Luodiye/zxbaojia2", '', array('ext' => '')),    //新装修报价页面
        "zxdetails" => array("Luodiye/zxdetails"),           //新装修报价详细页面
        "xzxbj-result"     =>array('Luodiye/xzxbj_result'),
        // 1056 齐装和装小七报价落地页优化
        "qzw/zxbaojia3$"       => array("Luodiye/zxbaojia3", '', array('ext' => '')),    //新装修报价页面

        // 1126 复制2个齐装落地页，在taozuang项目上使用
        "qzw/xbaojia5$"       => array("Luodiye/xbaojia5", '', array('ext' => '')),
        "qzw/xbaojia6$"       => array("Luodiye/xbaojia6", '', array('ext' => '')),
        "zx-details" => array("Luodiye/zx_details"),           //xbaojia5详细页面
        "zx-details1" => array("Luodiye/zx_details1"),           //xbaojia6详细页面

        // 1103 制作齐装网报价落地页
        "qzw/xbaojia4$"       => array("Luodiye/xbaojia4", '', array('ext' => '')),    //新装修报价页面

        // 1224 根据小程序“智能报价”页面制作h5报价落地页
        "qzw/baojia-btl$"       => array("Luodiye/baojiabtl", '', array('ext' => '')),    //新装修报价页面

        // 1246 API对接需求
        "qzw/sheji-szxwt"       => array("Luodiye/sheji_szxwt", '', array('ext' => '')),    // 复制sheji页面

        // 1303 落地页设计需求
        "qzw/baojiawqz$"       => array("Luodiye/baojiawqz", '', array('ext' => '')), // 1303发单页
        "baojiawqz-result" => array("Luodiye/baojiawqz_result"),            // 1303结果页

        //p.2.17.18 移动端发单落地页
        "qdsj1$"     =>array('Luodiye/qdbj1'),
        "baojia4$"     =>array('Luodiye/zxbjjs'),
        "baojia4done"     =>array('Luodiye/zxbjjs_success'),
        "fadan/jh-zxdk$"     =>array('Luodiye/jh_zxdk'),  //新增建行装修贷款落地页

        // 858 APP齐装&齐装云管家&管理后台新增齐装保业务
        "bao$"     =>array('Luodiye/bao'),

        //2019/2/26复制家具设计和报价页面，并分别添加JS代码
        "jjqwdz/jrttjj2"              =>    array("Qwdz/jrttjj2"), //全屋定制落地页
        "qwdzbj/toutiaojiaju2"=> array("JiajuZb/toutiaojiaju2"),//全屋定制报价页
        //  a.19.2.8 前台m端家具推广落地页复制 （由于家具渠道来源组未添加，临时未用模板方式）
        "jjqwdzdyjj" => array("Qwdz/jjqwdzdyjj"),
        "jjqwdzjtttjj" => array("Qwdz/jjqwdzjtttjj"),

        'jjqwsj$' => array('Qwdz/jjqwsj'),                              //全屋定制设计主题落地页
        "liangfang$"              =>    array("Company/liangfang"), //免费量房落地页
        "erp/jieshao"            =>    array("Erp/erpjieshao"), //介绍ERP系统
        "erp"                   =>  array("Erp/index"),       //ERP 营销落地页

        "worksite/qrcode"=> array("Mobile/Worksite/workSiteWxQrcode"), //工地直播二维码
        "worksite/fans"=> array("Mobile/Worksite/workSiteFans"), //工地直播粉丝
        "worksite/detail"=> array("Mobile/Worksite/workSiteDetail"), //工地直播详情
        "worksite$"=> array("Mobile/Worksite/workSiteList"), //工地直播

        "company/recommend" => array("Mobile/Company/huanYiHuan", '', array('method' => 'post')), //推荐公司换一换
        "/^(baojia|zhaobiao|sheji|liangfang|newbaojia|baojia1|baojia2|baojia3|sheji-jzrk|baojia-zst|sheji-dyqd|sheji-dyqd-2|baojia-jzrk|baojia1-jzrk|jjqwdzqd|jjqwsj|qwdzbj|jjqwbj|sheji-xcx|qdsj1|baojia4|qzjzj|mthd|baojia-ywwx|lyzxhd|sheji-jzrk-shxs|zxbj|xhxsj|baojia10|baojia11|baojia13|baojia14|baojia15|zxbaojia1|zxbaojia2|zxbaojia3|baojia16|xbaojia4|xbaojia5|xbaojia6|sheji-xy1|baojiawqz|shuang11)\/([a-z]+)(\/)?$/" => array("Tui/index", '', array('ext' => '')), // 落地页生成 渠道JS代码 渠道推广
        "/^(zhaoshang|gzsjbj|qdsj1-xcx|mjsj|mjsj1|baojiazk)\/([a-z]+)(\/)?$/" => array("Tui/index", '', array('ext' => '')), // 落地页生成 渠道JS代码 渠道推广 不是发订单  招商信息 普通表单

        /**************************全屋定制落地页******************************/
        "/^qwdzjj$/"=>array("Mobile/Quanwu/index"),
        "fb_qwdz"=>array("Mobile/Quanwu/addRecord"),//发布订单路由
        /***********************风格测试**********************/
        "meitu/fengge"=>array("Mobile/Meitu/getFenggeMeitu"),//发布订单路由

        // 美图重构
        "tu$"=>array("Mobile/Tu/index"),     //美图首页
        "tu/like$"=>array("Mobile/Tu/like"),     //美图首页
        '/tu\/j(\d+)$/'=>array("Mobile/Tu/getHomeDetail?id=:1",'',array('ext'=>'html')),//美图家装详情页路由
        '/tu\/g(\d+)$/'=>array("Mobile/Tu/getPubDetail?id=:1",'',array('ext'=>'html')),//美图公装详情页路由
        "tu/:category/[:p\d]$/"=>"Mobile/Tu/getList?category=:1&p=:2",     //美图栏目

        //企查查
        "qcc/:key" => array("Mobile/Qcc/qccDetail"),
        "qcc" => array("Mobile/Qcc/index"),


        //1076  地图
        ":bm/zxdt" => array("Mobile/Company/zxdt"),//地图
        'zxdt/map' => array("Mobile/Decorationmap/getCityMap"), // 搜索


        ':bm/:category$'=> array("Mobile/SubTopic/index",'',array('ext'=>'html')),
        ":bm/huodong"=> array("Mobile/SubHuodong/juranzhijia"), // 活动 居然之家
         ":bm$" => array("SubIndex/index", '', array('ext' => '')), //分站主页路由 -- 这个一定要放在最后！！
        "appxiazai" => array("Mobile/Activity/play_bill"),//六月APP引流活动



    ),
    'WX_APPID' => 'wx051e36a624bd7c2c',
    'WX_APPSECRET' => '14e18528a889e35e8d08d27d8331cf7b',
    'WX_URL' => 'https://m.qizuang.com/',
);
