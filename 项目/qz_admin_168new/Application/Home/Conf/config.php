<?php
return array(
    //'配置项'=>'配置值'
    //文件上传七牛配置
    'UPLOAD_IMG_QINIU'    =>    array(
            'maxSize'  => 10 * 1024 * 1024,//文件大小
            'rootPath' => './',
            'saveName' => '',
            'driver'   => 'Qiniu',
            'exts'     => array("jpg","jpeg","png","gif"),
            'driverConfig'   => array (
                'secretKey' => '',
                'accessKey'  => '',
                'domain'     => '',
                'bucket'     => ''
            )
    ),

    'UPLOAD_FILE_QINIU'    =>    array(
            'maxSize'  => 10 * 1024 * 1024,//文件大小
            'rootPath' => './',
            'saveName' => '',
            'driver'   => 'Qiniu',
            'exts'     => array("txt","zip","doc","docx","xls","xlsx"),
            'driverConfig'   => array (
                'secretKey' => '',
                'accessKey'  => '',
                'domain'     => '',
                'bucket'     => ''
            )
    ),
    //路由配置
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES'=>array(
        "meitu/location"=>array("Meitu/attribute",array('attribute' => 'location')),//
        "meitu/huxing"=>array("Meitu/attribute",array('attribute' => 'huxing')),//
        "meitu/fengge"=>array("Meitu/attribute",array('attribute' => 'fengge')),//
        "meitu/color"=>array("Meitu/attribute",array('attribute' => 'color')),//
        "/fdlink"=>array("Fdlink/index"),

        /** 路由 联通云总机 应用服务器的回调 **/
        "callreq"       => array("Apicuct/callreq"), //呼叫请求、鉴权或被叫查询回调
        "callestablish" => array("Apicuct/callestablish"), //呼叫建立通知回调
        "keyfeedback"   => array("Apicuct/keyfeedback"), //呼叫过程按键反馈
        "callhangup"    => array("Apicuct/callhangup"), //呼叫失败或挂机计费通知
        "voicecode"     => array("Apicuct/voicecode"), //语音验证码状态通知接口
        "voicenotify"   => array("Apicuct/voicenotify"), //语音通知状态通知回调
        'consult/deal$'=>array('Vip/addDealRecord','',array('method'=>'post')),//装修公司咨询处理记录录入接口


    ),

    'ALL_CITY_MANAGER' => array('1','37','51'),
    //友盟参数配置 云管家
    'umeng_appkey_android' => '5c7e093061f5647333000446',
    'ument_appMasterSecret_android' => 'lqmjiflctsybeiptjm7cuuznloeza0uw',
    'umeng_activity_android' => 'com.qizuang.sjd.push.MiPushActivity',

    'umeng_appkey_ios' => '5c7e0bd03fc195571c0010c2',
    'ument_appMasterSecret_ios' => 'q0wfktvdzdmp7itw0ed2z5trzxeu0x79',

    'umeng_timestamp' => NULL,
    'umeng_validation_token' => NULL,

    //齐装
    'qz_umeng_appkey_android' => '5cef315b4ca357521d000e64',
    'qz_ument_appMasterSecret_android' => 'z2alubu1jeqgjbgnkimxxqzsrqvjrsyl',
    'qz_umeng_activity_android' => 'com.qizuang.zxs.push.MiPushActivity',

    'qz_umeng_appkey_ios' => '5cef31870cafb27a460010fd',
    'qz_ument_appMasterSecret_ios' => 'tvyybexq8gow1nv1meyaadokhviysbuv',
);