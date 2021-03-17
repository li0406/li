<?php
return array(
	//'配置项'=>'配置值'
	'URL_ROUTER_ON'   => true,
	'URL_ROUTE_RULES'=>array(
        //微信小程序路由
        "login"                  =>  array("User/login"),  //login登陆
		
        "zxjt/likestore"                  =>  array("Wxjiangtang/likeStore"),  //我的收藏
        "zxjt/editfav"                  =>  array("Wxjiangtang/editfav"),  //编辑、删除收藏
        //找公司路由
        "gonglue/gongsi$"=>array("Appletcarousel/zxlclist",array("category"=>"gongsi")),
        //设计预算路由
        "gonglue/shejiyusuan$"=>array("Appletcarousel/zxlclist",array("category"=>"shejiyusuan")),
        //收房验收路由
        "gonglue/shoufang$"=>array("Appletcarousel/zxlclist",array("category"=>"shoufang")),
        //装修选材路由
        "gonglue/xuancai$"=>array("Appletcarousel/zxlclist",array("category"=>"xuancai")),
        //施工阶段 拆改路由
        "gonglue/chagai$"=>array("Appletcarousel/zxlclist",array("category"=>"chagai")),
        //施工阶段 水电路由
        "gonglue/shuidian$"=>array("Appletcarousel/zxlclist",array("category"=>"shuidian")),
        //入住阶段 检测路由
        "gonglue/jianche$"=>array("Appletcarousel/zxlclist",array("category"=>"jianche")),
        //攻略详情页
        "gonglue/:category$"=>array("Appletcarousel/zxlclist"),
        //局部装修小程序 接口
        "gongluejubu/:category$"=>array("Appletcarousel/zxlclistjubu"),

	),
);