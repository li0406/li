<?php

return array(

    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES'=>array(

        /**  搜索引擎相关 **/
        // robots.txt
        'robots$' => array("Meitu/SearchEngine/robots", '', array('ext' => 'txt')),

        //美图标签
        "tags$"                   =>array("Meitu/Tags/index"), //标签首页
        '/tags-p(\d+)$/'          =>array("Tags/index?id=:1",'',array('ext'=>'html')),
        '/^tags\/meitu([0-9]+)p-(\d+)$/' =>array("Meitu/Tags/category?cate=meitu&id=:1&page=:2",'',array('ext'=>'html')),        //美图分类
        '/^tags\/meitu([0-9]+)$/' =>array("Meitu/Tags/category?cate=meitu&id=:1"),        //美图分类

        '/zt-p(\d+)$/' =>      array("Zt/index?page=:1",'',array('ext'=>'html')),
        'list$'        =>      array("Index/meitulist"),//美图列表路由

        'setselectcookie'           =>      array("Index/setSelectCookie"),


        '/p(\d+)$/'                 =>      array("Index/caseinfo?p=:1",'',array('ext'=>'html')),//美图详情页路由
        '/list-(l[\d+]+f[\d+]+h[\d+]+c[\d+]+(p[\d+]+)?+(q[\d+]+)?)$/'    =>  array("Index/meitulist"),//美图列表路由

        "mingshi/[:shortname]/"     =>      array("Index/mingshi"),//美图大师列表


        'like'                      =>      array("Index/like"),
        'closetip'                  =>      array("Index/closetip"),
        'download'                  =>      array("Index/download"),
        'pubmeitu/like'             =>      array("Pubmeitu/like"),
        'dolike'                    =>      array("Threedimension/dolike"),//3D效果图点赞
        "gongzhuang-l0f0m0"         =>      array("http://meitu.qizuang.com/gongzhuang/",301),//美图
        'gongzhuang$'               =>      array("Pubmeitu/pubMeituList"),//公装美图列表路由
        '/gongzhuang-(l[\d+]+f[\d+]+m[\d+]+(p[\d+]+)?+(q[\d+]+)?)$/'=>array("Pubmeitu/pubMeituList"),//公装美图列表路由
        '/g(\d+)$/'         =>      array("Pubmeitu/pubMeituInfo?id=:1",'',array('ext'=>'html')),     //工装美图详情页路由

        /*"xgt/list-h0f0z0t0"=>array("http://meitu.qizuang.com/xgt/",301),//效果图301
        '/^xgt\/list-(h[\d+]+f[\d+]+z[\d+]+(t[\d+]+)?(p[\d+]+)?)$/'=>array("Xiaoguotu/index"),//效果图无参数路由
        '/^xgt\/list-(lx[\d+]+f[\d+]+z[\d+]+(t[\d+]+)?(p[\d+]+)?)$/'=>array("Xiaoguotu/index"),//效果图无参数路由
        "/^xgt$/"=>array("Xiaoguotu/index"),//效果图路由*/

        "fb_order"                  =>      array("Index/fb_order"),//发布订单路由
        "dispatcher"                =>      array("Index/dispatcher"),//选择弹窗路由
        "guide"                     =>      array("Index/guide"),//选择弹窗路由
        'getcidbycity'              =>      array('Index/getCidByCname'),
        "setwindowswitch"           =>      array("Index/setwindowswitch"),//设置首页弹窗的cookie路由
        "bjresult"                  =>      array("Index/getBJResult"),//获取订单报价
        "details"                   =>      array("Index/getdetails"),//获取详细报价页面路由
        "bjdata"                    =>      array("Index/getBJData"),//获取订单报价
        "getdetailsbyajax"          =>      array("Index/getDetailsByAjax"),//获取详细报价页面路由

        'login'                     =>      array("Meitu/Index/login"),//弹出登录框
        "loginin"                   =>      array("Meitu/Index/loginin"),//用户登录
        "collect"                   =>      array("Meitu/Index/collect"),//收藏的路由
        "cancelcollect"                   =>      array("Meitu/Index/cancelcollect"),//收藏的路由
        'hm' => array("Index/hm"),//采集

        //3D效果图模块
        '/3d$/'                  =>      array("Threedimension/category?type=1"),
        '/3d(\d+)$/'            =>      array("Threedimension/category?p=:1&type=1"),
        '/3d(\d+)?-(\d+)?$/'            =>      array("Threedimension/category?p=:1&category=:2&type=2"),
        '/3d(\d+)?-(\d+)?-(\d+)?$/'            =>      array("Threedimension/category?p=:1&fengge=:2&huxing=:3&type=3"),
        '/3d-conten(\d+)$/'      =>      array("Threedimension/terminal?id=:1",'',array('ext'=>'html')),

        //3D效果图案例模块
        '/3d-case(\d+)$/'      =>      array("Threedimension/caseThreedShow?id=:1",'',array('ext'=>'html')),

        'zt$'                   =>       array("Zt/index"),
        'zt/[:id]'              =>       array("Zt/detail"),

        'loginout'               =>      array("Home/Index/loginout"),//用户退出
        'meitutag$'    =>  array("Index/getMeituTags"),//获取美图tag接口
    ),
);