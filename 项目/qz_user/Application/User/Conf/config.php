<?php
return array(

    //'配置项'=>'配置值'
    'URL_ROUTER_ON' => true,
    'URL_ROUTE_RULES' => array(
        "index$" => array("https://" . $_SERVER['HTTP_HOST']),//主站带有index方法的路由
        ":a/index" => array("https://" . $_SERVER['HTTP_HOST'] . "/:1/"),//带有INDEX方法的路由
        "reg" => array("User/Register/index"),//注册页面路由
        "save" => array("User/Register/save"),//用户注册路由
        "verify" => array("User/Index/Verify"),//验证码路由
        "verify/:id" => array("User/Index/Verify"),//验证码路由,多验证码
        "checkverify" => array("User/index/check_verify"),//验证码验证路由
        // "success"=>array("User/Register/success"),//注册成功提示页面路由
        // "loginout"=>array("User/Index/loginout"),//退出登录路由
        "chkstatus" => array("User/Home/chkstatus"),//检测装修公司状态路由
        "editbaseinfo" => array("User/Home/editbaseinfo"),//修改用户信息路由
        "recent_qd" => array("User/Home/getRecentQd"),   //获取最近签单

        "orders/change/state/complete" => array("User/Orders/changeStateComplete", '', array('method' => 'post')),
        "orders/ignore" => array("User/Orders/ignoreOrder", '', array('method' => 'post')),
        "orders/change/revisit/state$" => array("User/Orders/changeRevisitState"),//装修公司二次回访订单状态切换路由

        "orders/sendsms$" => array('User/Orders/sendSms'),  //发送短信
        "orders/checksms$" => array('User/Orders/checkSms'),  //短信验证
        "orders/changestate" => array("User/Orders/changestate"),
        "orders/wechatlogin$" => array("User/Orders/logining"),//装修公司微信查看订单轮询路由
        "orders/newnums" => array("User/Orders/orderNewNums"), // 轮询查询分/赠订单数量
        "orders/track" => array("User/Orders/ordertrack"), // 跟单小计
        "orders/savetrack" => array("User/Orders/orderTrackSave"), // 添加跟单小计
        "orders/deltrack" => array("User/Orders/orderTrackDelete"), // 删除跟单小计
        "orders/[:isread]/[:state]/[:keyword]" => array("User/Orders/index"),//装修公司搜索订单路由
        "ordersbac/[:isread]/[:keyword]" => array("User/Orders/huiFang"),//装修公司二次回访订单路由

        "applyorder" => array("User/Orders/applyorder"),//申请签单路由
        "unapplyorder" => array("User/Orders/unapplyorder"),//取消签单申请
        "unqiandanorder" => array("User/Orders/unqiandanorder"),//取消已签单
        "orderdetails" => array("User/Orders/lookorder"),//查看订单详情路由
        "initiative/[:status]/[:keyword]" => array("User/Orders/initiative"),//装修公司主动签单路由
        "delinitiativeorder" => array("User/Orders/delinitiativeorder"),//删除自主签单路由
        "lookinitiativeorder" => array("User/Orders/lookinitiativeorder"),//查看自主签单信息路由
        "addinitiativeorder" => array("User/Orders/addinitiativeorder"),//添加自主签单路由
        "wechat" => array("User/Orders/wechat"),//装修公司微信订单路由
        "unbindwx" => array("User/Orders/unbindwx"),//解除微信绑定路由
        "polling" => array("User/Orders/polling"),//装修公司绑定微信轮询路由
        "confirmbindaccount" => array("User/Index/confirmbindaccount"),//用户确认绑定帐号路由
        "orderchange" => array("User/Orders/orderchange"),//修改订单查看密码
        "cases/[:class]" => array("User/Cases/index"),//装修公司案例列表路由
        "caseup/[:caseid]" => array("User/Cases/caseup"),//上传/编辑案例的路由
        "uploader/uploadthreedimensional" => array("User/Uploader/uploadthreedimensional"),//上传图片路由
        "uploader$" => array("User/Uploader/upload"),//上传图片路由
        "removeImg" => array("User/Cases/removeImg"),//删除图片路由,removeImg位于common下的usercentercontroller

        "removedesImg" => array("User/designercase/removeImg"),//删除图片路由,removeImg位于common下的usercentercontroller
        "success/[:type]/[:id]" => array("User/Home/success"),//添加成功页面
        "delcase" => array("User/Cases/delcase"),//删除案例路由
        "deletecase" => array("User/Cases/deleteCaseById"),//删除3D效果图路由
        "resetcase" => array("User/Cases/resetcase"),//恢复案例路由
        "team" => array("User/Team/index"),//设计团队路由
        "teamup/[:id]" => array("User/Team/teamup"),//新增/编辑设计师信息路由
        "uplogo" => array("User/Uploader/uplogo"),//上传头像路由
        "removedesigner" => array("User/Team/removeDes"),//删除设计师路由
        "teamsearch/[:keyword]" => array("User/Team/teamsearch"),//搜索设计师路由
        "invite" => array("User/Team/invite"),//邀请设计师入住路由
        "uninvite" => array("User/Team/uninvite"),//取消邀请设计师路由
        "article" => array("User/Companyarticle/index"),//装修公司文章管理路由
        "articleup/[:id]" => array("User/Companyarticle/articleup"),//新增、编辑装修公司文章路由
        "ueditor" => array("User/Ueditor/upload"),//富文本编辑器路由
        "delarticle" => array("User/Companyarticle/delarticle"),//删除文章路由
        "activityinfo" => array("User/Companyarticle/activityinfo"),//优惠活动路由
        "activityup/[:id]" => array("User/Companyarticle/activityup"),//添加、编辑优惠活动
        "recomment" => array("User/Comment/recomment"),//评论回复路由
        "getcommentimgs" => array("User/Comment/getCommentContengAndImgsById"),//根据评论id获取评论内容和图片列表
        "message/unread" => array("User/Message/index", "isread=1"),//装修公司未读信息路由
        "message/[:id]" => array("User/Message/index"),//查看消息信息路由
        "history/[:begin]/[:end]" => array("User/Message/history"),// 操作记录路由
        "needlist" => array("User/Need/needlist"),//装修需求管理
        "setcomment" => array("User/Mycomment/setcomment"),//编辑评论信息路由
        "editInfo" => array("User/Userinfo/editInfo"),//编辑用户信息路由
        "mymessage/unread" => array("User/Mymessage/index", "isread=1"),//用户未读信息路由
        "mymessage/[:id]" => array("User/Mymessage/index"),//用户查看系统消息路由
        "mycollect/[:type]" => array("User/Mycollect/index"),//用户收藏路由
        "info/basic" => array("User/Companyinfo/index"),//企业信息路由
        "info/detail" => array("User/Companyinfo/detail_info"),//企业详细信息路由
        "info/img" => array("User/Companyinfo/company_img"),//企业形象图片路由
        "info/tonglan" => array("User/Companyinfo/tonglan"),//企业通栏路由
        "info/store" => array("User/Companyinfo/branchstore"),//企业分店路由
        "info/save_store" => array("User/Companyinfo/save_branchstore"),//企业分店路由
        "info/del_store" => array("User/Companyinfo/del_branchstore"),//企业删除分店路由
        "info/businesslicence" => array("User/Companyinfo/businesslicence"),//企业营业执照路由
        "info/delbusinesslicence" => array("User/Companyinfo/delbusinesslicence"),//删除企业营业执照路由
        "delcomment" => array("User/Comment/delcomment"),//删除评论路由
        "setCity" => array("User/Home/setCity"),//用户保存城市信息路由
        "desinfo" => array("User/Designerinfo/index"),//设计师详细信息路由
        "descase" => array("User/Designercase/index"),//设计师案例路由
        "descaseup/[:id]" => array("User/Designercase/descaseup"),//设计师发布案例、修改案例路由
        "deldescase" => array("User/Designercase/deldescase"),//设计师删除案例路由
        "desblog" => array("User/Designerblog/index"),//设计师博客信息路由
        "desblog_add" => array("User/Designerblog/add_blog"),//设计师博客信息路由
        "save_blog" => array("User/Designerblog/save_blog"),//设计师博客信息路由
        "edit_blog/:id" => array("User/Designerblog/edit_blog"),//设计师博客信息路由
        "del_blog" => array("User/Designerblog/del_blog"),//设计师博客信息路由
        "desteam" => array("User/Designerteam/index"),//设计公司管理路由
        "release" => array("User/Designerteam/release"),//解除绑定公司路由
        "joinus" => array("User/Designerteam/joinus"),//设计师同意/拒绝加入装修公司路由
        "desmessage/unread" => array("User/Designermessage/index", "isread=1"),//设计师未读信息路由
        "desmessage/[:id]" => array("User/Designermessage/index"),//设计师系统消息路由
        "descollect/[:type]" => array("User/Designercollect/index"),//设计师收藏路由
        "registersuccess" => array("User/Register/registersuccess"),//装修公司注册成功跳转页面路由
        "repass" => array("User/Register/repass"),//重新获取装修公司注册密码路由
        "getjias" => array("User/Home/getjias"),//获取小区路由
        //装修日记路由
        "add_diary/[:id]" => array("User/Diary/add_diary"),//添加日记第一步
        "add_diary_second" => array("User/Diary/add_diary_second"),//添加日记第一步
        "write_diary/[:id]" => array("User/Diary/write_diary"),//写日记第一步
        "write_diary_second" => array("User/Diary/write_diary_second"),//写日记第二步
        "diary_edit_list/[:id]" => array("User/Diary/diary_edit_list"),//单篇日记列表
        "add_follow_diary/[:id]" => array("User/Diary/add_follow_diary"),//单篇日记列表
        "add_follow_diary_do" => array("User/Diary/Add_follow_diary_do"),//添加后续日记
        "edit_diary/[:id]" => array("User/Diary/edit_diary"),//编辑日记的内容
        "edit_diary_do" => array("User/Diary/edit_diary_do"),//编辑日记的内容
        //3d效果图
        "threed" => array("User/Cases/threed"),//装修公司案例列表路由
        "threedup" => array("User/Cases/threedup"),//上传/编辑案例的路由


        "back/orderdetails$" => array("User/Orders/viewBackOrder"),//二次回访查看订单详情路由

        "saveorderpass" => array("User/Orders/saveorderpass"),//保存订单查看密码路由

        "peruser$" => array("User/Peruser/index"),//装修公司的业主帐号
        "peruser/add" => array("User/Peruser/add"),//装修公司的业主帐号
        "peruser/remove" => array("User/Peruser/remove"),//装修公司的业主帐号

        "sendsms" => array("User/Index/sendsms"),//短信发送模版
        "sendemail" => array("User/Index/sendemail"),//短信发送模版
        "verifysmscode" => array("User/Index/verifysmscode"),//验证码验证
        "login" => array("User/Index/login"),//用户登录
        "backLogin" => array("User/Index/backLogin"),//用户登录
        "loginout" => array("User/Index/loginout"),//用户退出
        "login_userinfo" => array("User/Index/login_userinfo"),//用户信息
        "login_sendsms" => array("User/Index/login_sendsms"),//登陆发送短信
        "login_verifysmscode" => array("User/Index/login_verifysmscode"),//登陆验证短信

        "delreview" => array("User/Orders/delreview"),//删除公司回访

        "account" => array("User/Index/account"),
        "confirmaccount" => array("User/Index/confirmAccount"),
        "fb_order" => array("User/Index/fb_order"),
        "cancelcollect" => array("User/Index/cancelcollect"),
        "bindaccount" => array("User/Index/bindaccount"),

        //封禁用户
        "blocked" => array("User/Index/blocked"),

        /**
         * 系统信息
         */
        "delmessage" => array("User/Message/delmessage"),//删除装修公司站内信
        "deldesmessage" => array("User/Designermessage/deldesmessage"),//删除设计师站内信
        "delmymessage" => array("User/Mymessage/delmymessage"),//删除用户站内信
        "info/tag"=> array("User/Companyinfo/tag" ),//企业公司标签路由

        //专用礼券
        "specialdetail/[:id]" => array("User/Oneselfevent/specialdetail"), //专用礼券详情
        "editgift/[:id]" =>array("User/Oneselfevent/editgift"),

        "add_norob" => array("User/Roborder/setNoRobOrder"),//不抢操作
        "company/sendsms" => array("Companyinfo/companysms"),//装修公司绑定安全手机
        "company/sendemail" => array("User/Companyinfo/sendemail"),//短信发送模版
        "designer/sendsms" => array("User/Designerinfo/sendsms"),//装修公司绑定安全手机
        "designer/sendemail" => array("User/Designerinfo/sendemail"),//短信发送模版
    ),
);
