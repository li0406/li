<?php
return array(

    //'配置项'=>'配置值'

    //定义运行环境 方便不同的运行环境执行不同的逻辑
    //比如开发环境用不同的三方API TOKEN,微信开发环境,发单IP不受限制等。
    //具体那些地方有调用直接搜索代码可找到
    //空、不配置、false、prod都为生成环境,dev为开发环境,test为测试环境
    'APP_ENV'=> env('APP_ENV', 'prod'),

    // 定义静态文件版本 代码发布后需被替换成时间 例如 20180912164602
    'STATIC_VERSION' => env('STATIC_VERSION', '20200722170805'),

    /* 默认模块 */
    'DEFAULT_MODULE' => 'User',

    /* URL设置 */
    'URL_MODEL'             => 1, //设置路由模式

    /* 调试&&错误信息设置 */
    'SHOW_PAGE_TRACE'       => env('APP_TRACE', false),   // 显示页面Trace信息
    'SHOW_ERROR_MSG'        => env('APP_DEBUG', false),   // 显示错误信息

    /* SESSION设置 */
    'SESSION_AUTO_START'    =>  True,   //自动打开session
    // session 配置数组 支持type name id path expire domain 等参数
    'SESSION_OPTIONS'       =>  array(
                          'domain' => '.qizuang.com',
                          ),

    /* 数据库设置 */
    'DB_DEPLOY_TYPE'        => 1, //启用分布式数据库
    'DB_RW_SEPARATE'        => env('MYSQL_RW_SEPARATE', true), //启用读写分离
    'DB_TYPE'               => 'mysql', // 数据库类型
    'DB_HOST'               => env('MYSQL_HOST', '127.0.0.1'), // msyql地址,第一个是master,配置替换,不留空格是因为方便替换
     //'DB_HOST'=>'10.10.43.169,10.10.38.75', //第一个是master
    'DB_NAME'               => env('MYSQL_DATABASE', 'sq_qizuang'), // 数据库名
    'DB_USER'               => env('MYSQL_USERNAME', 'root'), // 用户名,配置替换,不留空格是因为方便替换
    'DB_PWD'                => env('MYSQL_PASSWORD', 'root'), // 密码,配置替换,不留空格是因为方便替换
    'DB_PORT'               => env('MYSQL_PORT', 3306), // 端口
    'DB_PREFIX'             => 'qz_', // 数据库表前缀
    'DB_CHARSET'            => 'utf8', // 字符集
    'READ_DATA_MAP'         => true,//开启字段自动映射

    /* 数据缓存设置 */
    //'DATA_CACHE_TYPE'       =>  'File',      // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
    //'DATA_CACHE_TIME'       =>  60*30,     // 数据缓存有效期 0表示永久缓存
    //'DATA_CACHE_SUBDIR'     =>  true,        // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
    //'DATA_PATH_LEVEL'       =>  1,           // 子目录缓存级别


    //S方法使用 redis作为底层缓存
    'DATA_CACHE_TYPE' => 'Redis',  // 使用Redis做为S方法底层驱动支持
    'REDIS_HOST'      => env('REDIS_HOST', '127.0.0.1'), //  配置替换,不留空格是因为方便替换
    //'REDIS_HOST'      => '10.10.167.207', //备用
    'REDIS_PORT'      => env('REDIS_PORT', 6379),
    'DATA_CACHE_TIME' => 60 * 60 * 8,  //过期的秒数*/


    //S方法使用 ssd 作为缓存
    //'DATA_CACHE_TYPE' => 'ssdb',  //默认是file方式进行缓存的，修改为memcache
    //'SSDB_HOST'       => '127.0.0.1',  //ssdb服务器地址
    //'SSDB_PORT'       => 8888, //ssdb服务器端口
    //'DATA_CACHE_TIME' => 60 * 60 * 8,  //过期的秒数*/


    // 子域名部署
    'APP_SUB_DOMAIN_DEPLOY' => 1, // 开启子域名配置
    'APP_SUB_DOMAIN_RULES' => array(
            'u'   => 'User', // u.qizuang.com域名指向User模块
            'old' => 'Muser',//装修公司移动版后台
            'f'   => 'Cpa',  //CPA项目
    ),

    //ip白名单
    //起作用的地方审核为无效咨询
    'IP_WHITE_LIST'        => array(
                                    '222.92.114.186',
                                    '223.112.69.58',
                                    '153.35.215.162',
                                    '112.81.16.122',
                                    '192.168.8.2',
                                    '172.19.0.1',
                                     ),

    //注册的域名
    'REGISTER_URL'   => array("https://www.qizuang.com","https://u.qizuang.com","https://oauthtmp.qizuang.com"),

    // 调用七牛的域名
    'QINIU_DOMAIN'   => 'staticqn.qizuang.com',
    'QINIU_SCHEME'  => 'https',
    //静态文件服务器
    'STATIC_HOST1'   => 'static.qizuang.com',

    // 域名
    'QZ_YUMING'      => 'qizuang.com',                 //网站域名 不包含 www
    'QZ_YUMINGWWW'   => 'www.qizuang.com',             //网站域名 包含 www
    'QZ_YUMING_SCHEME'  => 'https',
    //定义移动版访问 域名
    'MOBILE_DONAMES' => 'm.qizuang.com',

    //order.js版本号
    'ORDER_JS_VERSION' => env('STATIC_VERSION', '20200722170805'),

    /*定义IP获取函数get_client_ip()取值顺序自定义配置*/
    //当前使用百度云加速
    //HTTP_CF_CONNECTING_IP 百度云加速 和 CloudFlare 会把客户端IP存入本项  如果(CDN、LB、GATEWAY)未重写会有伪造
    //HTTP_X_REAL_IP 大多数CDN会把客户端IP存入本项  如果(CDN、LB、GATEWAY)未重写会有伪造
    //HTTP_X_FORWARDED_FOR 可为多个以","分割的IP,第一个IP是客户端IP,后面的为代理服务器IP,多个代理服务器IP顺序增加 如果(CDN、LB、GATEWAY)未重写会有伪造
    //HTTP_CLIENT_IP 代理服务器传过来的客户端IP 如果(CDN、LB、GATEWAY)未重写会有伪造
    //REMOTE_ADDR 对于运行本函数的应用服务器来说的远程地址,如果前面有(CDN、LB、GATEWAY)则是(CDN、LB、GATEWAY)的节点IP地址
    'GET_IP_VARS_ORDER' => env('GET_IP_VARS_ORDER','HTTP_CF_CONNECTING_IP,HTTP_X_FORWARDED_FOR,HTTP_X_REAL_IP,HTTP_CLIENT_IP,REMOTE_ADDR'),

    'SMS_HOST' => 'http://qzsms:12000',//短信服务网关地址
    'SMS_APP_SLOT' => 'f27114399b31b5a777e0309c5526832070f46546',//项目可用slot
    "WS_HOST" => "ws://wsapi.qizuang.com",

    //app图片地址前缀
    'ZXS_QINIU_DOMAIN' => 'https://zxsqn.qizuang.com',
    //jwt
    'jwt_secret' => env('JWT_SECRET', ''),
    'jwt_use_limit' => '2678400',//token过期时间 31天
    'jwt_refresh_limit' => '2678400',//token可以用时间，（可刷新）31天

    "ZXS_API" => "https://api.qizuang.com",
    "CP_HOST" => "cp.qizuang.com", //新签返后台
    'SERVICE' => [
        "QZPNP" => [
            "PROTOCOL" => "http",
            "DOMAIN" => "qzpnp",
            "PORT" => "18600"
        ],
    ],
);
