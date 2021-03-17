<?php
return array(
    //'配置项'=>'配置值'
    'URL_ROUTER_ON' => true,
    'URL_ROUTE_RULES' => array(

        /**  搜索引擎相关 **/
        // robots.txt
        'robots$' => array("Sub/SearchEngine/robots", '', array('ext' => 'txt')),
        "index" => array("https://" . $_SERVER['HTTP_HOST']),//主站带有index方法的路由
        ":a/index" => array("https://" . $_SERVER['HTTP_HOST'] . "/:1/"),//带有INDEX方法的路由
        "xiaoguotu" => array("https://" . $_SERVER['HTTP_HOST'] . "/xgt/"),//原xiaotutu访问路由
        "city" => array("Home/City/index"),//城市导航路由
        "zhaobiao$" => array("Sub/Zb/index",'', array('ext' => '')),//招标页路由
        "sheji$" => array("Sub/Zb/sheji"),//招标页路由
        "caseinfo/:id" => array("Sub/Case/index", '', array('ext' => 'shtml')),//案例显示路由
        "getComment" => array("Sub/Case/getComment"),//案例显示查询评论的路由
        "verify" => array("Sub/Index/Verify"),//验证码路由
        "verifyzx" => array("Sub/Index/verifyzx"),//验证码路由
        "casecomment" => array("Sub/Case/addCaseComment"),//提交评论路由

        "companycomment" => array("Sub/Companyhome/setComment"),//装修公司主页评论路由
        '/company_team\/(\d+)\-(\d+)$/' => array("Sub/Companyhome/team?id=:1&type=:2",'',array('ext' => '')),//装修公司主页设计师页面路由
        '/company_team\/(\d+)$/' => array("Sub/Companyhome/team?id=:1",'',array('ext' => '')),//装修公司主页设计师页面路由
        '/company_about\/(\d+)$/' => array("Sub/Companyhome/about?id=:1",'',array('ext' => '')),//装修公司主页关于我们页面路由
        '/company_case\/(\d+)$/' => array("Sub/Companyhome/cases?id=:1",'',array('ext' => '')),//装修公司装修案例页面路由
        "company_message/:id" => array("Sub/Companyhome/comment",'',array('ext' => '')),//装修公司评论页面路由
        // "company_zixun/:id/[:t]/[:act]"=>array("Sub/Companyhome/zixun"),//装修公司资讯页面路由
        '/company_zixun\/(\d+)(\/\d+)?(\/\d+)?$/' => array("Sub/Companyhome/zixun?id=:1&t=:2&act:3"),//装修公司资讯页面路由
        "company_event/:id" => array("Sub/Companyhome/event",'',array('ext' => '')),//装修公司优惠活动
        "event_view/:id" => array("Sub/Companyhome/eventView", '', array('ext' => 'html')),//装修公司优惠活动详细页面
        '/company_home\/(\d+)$/' => array("Sub/Companyhome/index?id=:1", '', array('ext' => '')),//装修公司主页路由
        "wenda/quickask" => array("Sub/Index/wenda"),//快速提问路由
        '/wenda\/(\d+)$/' => array("Sub/Companyhome/wenda?id=:1"),//装修公司问答
        "blog/[:id]$" => array("Sub/Blog/index", "extra=1"),//普通公司的设计师博客路由
        "blog_new/[:id]$" => array("Sub/Blog/designerEmployeeInfo", "extra=1"),//新签返公司的设计师博客路由

        '/^xgt\/list-(h[\d+]+f[\d+]+z[\d+]+(t[\d+]+)?(p[\d+]+)?)$/' => array("Sub/Xiaoguotu/index"),//效果图无参数路由
        '/^xgt\/list-(lx[\d+]+f[\d+]+z[\d+]+(t[\d+]+)?(p[\d+]+)?)$/' => array("Sub/Xiaoguotu/index"),//效果图无参数路由
        // "/^xgt$/" => array("Sub/Xiaoguotu/index"),//效果图路由
        "/^xgt$/" => array("Sub/Xiaoguotu/xgt"),//效果图路由
        "zixun_index" => array("https://www.qizuang.com/gonglue/", 301),//老站资讯首页路由
        "zixun_info/:id" => array("Sub/Companyhome/article", '', array('ext' => 'shtml')),//装修公司资讯详细页面
        "company_article/:cid/:id" => array("Sub/Index/zixun"),// 用于处理 /company_article/23985/25636.html 为 /zixun_info/25636.shtml
        "works/:id" => array("Sub/Index/works"),//老版设计师案例列表路由
        "article/:id" => array("Sub/Index/article"),//老版设计师文章列表路由
        "article_info/:id" => array("Sub/Articleinfo/index"),//设计师的文章详情路由
        "goodcase" => array("Sub/Index/goodcase"),//老版装修效果图路由
        "fujin" => array("Sub/Index/fujin"),//请求附近招标模版路由

        /***********************异业广告获取*******************************/
        'getPartnerad$' => array('Sub/Partner/getPartneradInfo'),
        'addPartneradClick$' => array('Sub/Partner/addPartneradClick'),

        'zxinfo$' => array("Zxinfo/index"),//小站资讯首页路由(改版后)
        'zxinfo/[:category]$' => array("Zxinfo/category",'', array('ext' => '')),//小站咨询文章分类页路由(改版后)
        "/^zxinfo\/list\-daogou\-([1-9]\d*)$/" => array(
            "Zxinfo/category?page=:1",
            array("category" => "daogou"),
            array('ext' => 'html')
        ),
        "/^zxinfo\/list\-gongsi\-([1-9]\d*)$/" => array(
            "Zxinfo/category?page=:1",
            array("category" => "gongsi"),
            array('ext' => 'html')
        ),
        "/^zxinfo\/list\-jingyan\-([1-9]\d*)$/" => array(
            "Zxinfo/category?page=:1",
            array("category" => "jingyan"),
            array('ext' => 'html')
        ),
        "/^zxinfo\/list\-bendi\-([1-9]\d*)$/" => array(
            "Zxinfo/category?page=:1",
            array("category" => "bendi"),
            array('ext' => 'html')
        ),
        "/^zxinfo\/list\-baojia\-([1-9]\d*)$/" => array(
            "Zxinfo/category?page=:1",
            array("category" => "baojia"),
            array('ext' => 'html')
        ),
        "/^zxinfo\/list\-xuetang\-([1-9]\d*)$/" => array(
            "Zxinfo/category?page=:1",
            array("category" => "xuetang"),
            array('ext' => 'html')
        ),
        "/^zxinfo\/list\-wenwen\-([1-9]\d*)$/" => array(
            "Zxinfo/category?page=:1",
            array("category" => "wenwen"),
            array('ext' => 'html')
        ),
        "/^zxinfo\/list\-zxsj\-([1-9]\d*)$/" => array(
            "Zxinfo/category?page=:1",
            array("category" => "zxsj"),
            array('ext' => 'html')
        ),
        "/^zxinfo\/list\-([1-9]\d*)$/" => array(
            "Zxinfo/category?page=:1",
            array("category" => ""),
            array('ext' => 'html')
        ),

        'zxinfo/:category/:id$' => array("Zxinfo/remoteArticle"),//小站文章详情页路由
        'zxinfo/[:id\d]$' => array("Zxinfo/article", '', array('ext' => 'html')),//小站文章详情页路由
        '/^zxinfo\/(\d+)$/' => array("Zxinfo/remoteArticle?id=:1"),//小站文章详情页废弃文章类的路由

//        'zixun$' => array("Zxinfo/index"),//小站文章页路由（兼容历史）
//        'zixun/[:category]$' => array("Zxinfo/category",'', array('ext' => '')),//小站咨询文章分类页路由(改版后)

        'mzb' => array("Tiaozhuan/tiaozhuan_mzb"), //兼容处理老板的移动版版本招标页,做跳转
        'city' => array("https://www.qizuang.com/city/", 301),//老版的选择城市的路由
        "about/contactus" => array("https://www.qizuang.com/about/contactus", 301),//老版关于我们

        '/^company\/list-(fu[\d+]+f[\d+]+g[\d+]+(p[\d+]+)?)$/' => array("Sub/Company/oldindex"),//效果图 老路由(兼容百度收录)
        '/^company\/list-(fu[\d+]+f[\d+]+g[\d+]+bz[\d+]+(p[\d+]+)?)$/' => array("Sub/Company/index"),//效果图路由(兼容百度收录)
        '/^company\/list-(fu[\d+]+f[\d+]+g[\d+]+bz[\d+]+(o[\d+]+)?(i[\d+]+)?(p[\d+]+)?)$/' => array("Sub/Company/index"),//效果图路由
        '/^company$/' => array("Sub/Company/index"),//效果图路由
        'company/companysearch' => array("Sub/Company/companysearch"),//效果图路由
        '/^companysearch\/list-(fu[\d+]+f[\d+]+g[\d+]+bz[\d+]+(p[\d+]+)?)$/' => array("Sub/Companysearch/index"),//效果图路由

        "zxgstj" => array("Sub/Company/companylandpage"),//装修公司新版落地页
        "login" => array("Sub/Index/login"),//用户登录
        "loginin" => array("Sub/Index/loginin"),//用户登录
        "loginout" => array("Sub/Index/loginout"),//用户退出
        "run" => array("Sub/Index/run"),//登录轮询
        "collect" => array("Sub/Index/collect"),//收藏的路由
        "setwindowswitch" => array("Sub/Index/setwindowswitch"),//设置首页弹窗的cookie路由
        "refresh" => array("Sub/Index/refresh"),//刷新验证码路由
        "dispatcher" => array("Sub/Index/dispatcher"),//选择弹窗路由
        "guide" => array("Home/Index/guide"),//装修小贴士路由
        "feedback" => array("Sub/Index/feedback"),//订单报价结果反馈
        "zbprice" => array("Sub/Index/getZbPrice"),//获取订单报价
        "fb_order" => array("Sub/Index/fb_order"),//发布订单路由
        "sendsms" => array("Home/Index/sendsms"),//发送短信路由
        "sendsmstx" => array("Sub/Index/sendsmstx"),//发送短信路由
        "verifysmscode" => array("Sub/Index/verifysmscode"),//验证码路由
        "run" => array("Home/Index/run"),//轮询查询用户是否登录路由
        "bjresult"=>array("Sub/Index/getBJResult"),//获取订单报价
        "bjdata" => array("Sub/Index/getBJData"),//获取订单报价

        "allcity" => array("Home/Index/allCityJson", '', array('ext' => 'js')),

        "freetel" => array("Sub/Index/freetel"),//免费电话咨询

        '/^jia\/(\d+)$/' => array("Jia/jiainfo?id=:1"),//找我家详情页路由

        '/^designer\/([a-z0-9]{2}-[a-z0-9]{2}-[a-z0-9]{2})$/' => array("Sub/designer/index"),//设计师无参数路由

        /**
         * 品牌榜路由
         */
        'paihangbang' => array("Sub/Companybrands/index"),//品牌榜主页路由

        '/^zt\/([a-z]+)$/' => array("Sub/Zt/special?name=:1"),                                        //专题详细页
        'complain' => array("Sub/Zt/complain"),//提交投诉类

        'getcidbycity' => array('Sub/Zb/getCidByCname'),
        'getcityinfobyip' => array('Sub/Zb/getCityInfoByIp'),
        // 'hm' => array("Sub/Index/hm"),//采集路由
        "getdetailsbyajax" => array("Sub/Zxbj/getDetailsByAjax"),//获取详细报价页面路由
        "get_city_baojia" => array("Sub/Zxbj/get_city_baojia"),//获取多城市详细报价页面路由

        //3D效果图案例模块
        '/3d-case(\d+)$/'   =>   array("Threedimension/caseThreedShow?id=:1",'',array('ext'=>'html')),


        //优惠券
        'getcardinfobyid' => array("Sub/Companyhome/getCardInfoByCardid"),
        'cardlogin' => array('Sub/Companyhome/login'),
        'receivecard'=>array('Sub/Companyhome/receiveCard'),
        'signliangfang'=>array('Sub/Companyhome/signLiangFang'),

        //分站报价页
        "baojia" => array("Sub/Quote/index",'',array('ext'=>'html')),//报价页面

        ':category'=> array("Sub/Topic/index",'',array('ext'=>'html')),

        /**
         * 活动
         */
        "huodong"=> array("Sub/Huodong/juranzhijia"), // 居然之家

        "hfzx"=>array("Sub/Decoration/marriageDecorate"),//婚房装修
        "jbzx"=>array("Sub/Decoration/partDecorate"),//局部装修
        "header_city"=>array("Home/Header/headerCity"), //头部城市

        "jszx"=>array("Sub/Decoration/speedDecorate"),//极速装修
        "gzds"=>array("Sub/Decoration/reformMaster"),//改造大师
        "cysj"=>array("Sub/Decoration/originDesign"),//创意设计
        "esfzx"=>array("Sub/Decoration/secondDecorate"),//二手房装修
        "xfzx"=>array("Sub/Decoration/newDecorate"),//新房装修
        "syzx"=>array("Sub/Decoration/businessDecorate"),//商业装修
        "tu/dzp" => array("Special/tuTopLayer",'',array('ext'=>'')),//美图首页大转盘弹层

        //装修地图
        'zxdt/map'=> array("Sub/Decorationmap/getCityMap"),
        'zxdt'=> array("Sub/Decorationmap/index"),
    ),
);

