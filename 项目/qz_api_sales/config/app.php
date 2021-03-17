<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用名称
    'app_name' => 'app',
    // 应用地址
    'app_host' => '',
    // 应用调试模式
    'app_debug' => env('APP_DEBUG', false),
    // 应用Trace
    'app_trace' => env('APP_TRACE', false),
    // 应用模式状态
    'app_status' => '',
    // 是否支持多模块
    'app_multi_module' => true,
    // 入口自动绑定模块
    'auto_bind_module' => false,
    // 注册的根命名空间
    'root_namespace' => [],
    // 默认输出类型
    'default_return_type' => 'json',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return' => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler' => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler' => 'callback',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',
    // 是否开启多语言
    'lang_switch_on' => false,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter' => 'htmlspecialchars,trim',
    // 默认语言
    'default_lang' => 'zh-cn',
    // 应用类库后缀
    'class_suffix' => false,
    // 控制器类后缀
    'controller_suffix' => false,

    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module' => 'index',
    // 禁止访问模块
    'deny_module_list' => ['common'],
    // 默认控制器名
    'default_controller' => 'Index',
    // 默认操作名
    'default_action' => 'index',
    // 默认验证器
    'default_validate' => '',
    // 默认的空模块名
    'empty_module' => '',
    // 默认的空控制器名
    'empty_controller' => 'Error',
    // 操作方法前缀
    'use_action_prefix' => false,
    // 操作方法后缀
    'action_suffix' => '',
    // 自动搜索控制器
    'controller_auto_search' => false,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo' => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch' => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr' => '/',
    // HTTPS代理标识
    'https_agent_name' => '',
    // IP代理获取标识
    'http_agent_ip' => 'X-REAL-IP',
    // URL伪静态后缀
    'url_html_suffix' => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param' => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type' => 0,
    // 是否开启路由延迟解析
    'url_lazy_route' => false,
    // 是否强制使用路由
    'url_route_must' => true,
    // 合并路由规则
    'route_rule_merge' => false,
    // 路由是否完全匹配
    'route_complete_match' => false,
    // 使用注解路由
    'route_annotation' => false,
    // 域名根，如thinkphp.cn
    'url_domain_root' => 'qizuang.com',
    // 是否自动转换URL中的控制器和操作名
    'url_convert' => true,
    // 默认的访问控制器层
    'url_controller_layer' => 'controller',
    // 表单请求类型伪装变量
    'var_method' => '_method',
    // 表单ajax伪装变量
    'var_ajax' => '_ajax',
    // 表单pjax伪装变量
    'var_pjax' => '_pjax',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache' => false,
    // 请求缓存有效期
    'request_cache_expire' => null,
    // 全局请求缓存排除规则
    'request_cache_except' => [],
    // 是否开启路由缓存
    'route_check_cache' => false,
    // 路由缓存的Key自定义设置（闭包），默认为当前URL和请求类型的md5
    'route_check_cache_key' => '',
    // 路由缓存类型及参数
    'route_cache_option' => [],

    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => Env::get('think_path') . 'tpl/dispatch_jump.tpl',
    'dispatch_error_tmpl'    => Env::get('think_path') . 'tpl/dispatch_jump.tpl',

    // 异常页面的模板文件
    'exception_tmpl'         => Env::get('think_path') . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message' => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg' => false,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle' => '\Util\ExceptionHandler',
    'jwt_secret' => env('JWT_SECRET'),
    'jwt_use_limit' => '2678400',//token过期时间 31天
    'jwt_refresh_limit' => '2678400',//token可以用时间，（可刷新）31天
    //七牛服务器
    'qiniu' => [
        //域名
        'domain' => 'https://zxsqn.qizuang.com',
        //bucket
        'bucket' => 'qizuang-zxs',
        //ak
        'ak' => 'Wt0BTnEFDP2HEj8KaTAOWZ1_V9SbrEIx4EOkylr2',
        //ck
        'ck' => '2fhPo2--sy9Sxu-nOTcddGU9qQQJuYiS6IYfCiP9',
    ],
    //七牛视频服务器
    'qiniu_vedio' => [
        //域名
        'domain' => 'https://video.qizuang.com',
        //bucket
        'bucket' => 'qizuang-video',
        //ak
        'ak' => 'Wt0BTnEFDP2HEj8KaTAOWZ1_V9SbrEIx4EOkylr2',
        //ck
        'ck' => '2fhPo2--sy9Sxu-nOTcddGU9qQQJuYiS6IYfCiP9',
    ],
    //短信验证码配置 名称单词摘自齐装原始代码，意思相同
    'yunrongt' => [
        'tpl_yzm' => '【齐装网】您的验证码是:【变量】，请于【变量】分钟内正确输入！',
        'sms_step' => 10,
        'user_name' => 'qzwhy',
        'password' => 'cS7oJ9oZ',
    ],
    'yunrongtyx' => [
        'user_name' => 'qzwyx',
        'password' => 'yU9xJ8bD',
    ],
    'ip_white_list' => ['222.92.114.186','223.112.69.58','192.168.8.2','192.168.8.159','192.168.8.66','192.168.8.17','127.0.0.1'],
    'QZ_YUMING'         => 'qizuang.com',
    // 自定义参数, 网站信息
    'OA_URL'            => 'https://168oa.qizuang.com',
    'UC_URL'            => 'https://168uc.qizuang.com',
    '168NEW_URL'        => 'https://168new.qizuang.com',
    'editpasskey' => 'qizuangygj789',
    'SMS_HOST' => 'http://qzsms:12000',
    'SMS_APP_SLOT' => '85649387155369643ee199035012b487f0322700',
    "WORKSITE_APPLET_APPID" => "wxbd6cab509c23991c",
    "WORKSITE_APPLET_APPID_TEST"=>"wxef2238ec5c7452f3",
    'SERVICE' => [
        "QZMSG" => [
            "PROTOCOL" => "http",
            "DOMAIN" => "qzmsg",
            "PORT" => "18100"
        ],
    ],

    // 友盟
    'umeng' => [
        'android' => [
            'appkey' => '5c7e093061f5647333000446',
            'appsecret' => 'lqmjiflctsybeiptjm7cuuznloeza0uw',
            'mi_activity' => 'com.qizuang.sjd.push.MiPushActivity'
        ],
        'ios' => [
            'appkey' => '5c7e0bd03fc195571c0010c2',
            'appsecret' => 'q0wfktvdzdmp7itw0ed2z5trzxeu0x79'
        ]
    ],

    /*定义IP获取函数get_client_ip()取值顺序自定义配置*/
    //当前使用百度云加速
    //HTTP_CF_CONNECTING_IP 百度云加速 和 CloudFlare 会把客户端IP存入本项  如果(CDN、LB、GATEWAY)未重写会有伪造
    //HTTP_X_REAL_IP 大多数CDN会把客户端IP存入本项  如果(CDN、LB、GATEWAY)未重写会有伪造
    //HTTP_X_FORWARDED_FOR 可为多个以","分割的IP,第一个IP是客户端IP,后面的为代理服务器IP,多个代理服务器IP顺序增加 如果(CDN、LB、GATEWAY)未重写会有伪造
    //HTTP_CLIENT_IP 代理服务器传过来的客户端IP 如果(CDN、LB、GATEWAY)未重写会有伪造
    //REMOTE_ADDR 对于运行本函数的应用服务器来说的远程地址,如果前面有(CDN、LB、GATEWAY)则是(CDN、LB、GATEWAY)的节点IP地址
    'GET_IP_VARS_ORDER' => env('GET_IP_VARS_ORDER','HTTP_CF_CONNECTING_IP,HTTP_X_FORWARDED_FOR,HTTP_X_REAL_IP,HTTP_CLIENT_IP,REMOTE_ADDR'),

    //七牛 qiniu
    'QINIU_DOMAIN'  => 'staticqn.qizuang.com', //调用七牛的域名
    'QINIU_HOST'  => 'https://staticqn.qizuang.com', //调用七牛的域名

    'APP_ENV' => env('APP_ENV', 'prod'),
];
