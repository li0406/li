<?php
/**
 * 检查移动设备
 */
namespace Home\Behavior;
use Think\Behavior;
class MobileBrowserCheckBehavior extends Behavior {
    public function run(&$params) {
        //域名 *.qizuang.com 判断移动设备访问，跳转到移动页
        //例如移动设备访问 sz.qizuang.com
        //就会跳转到 m.qizuang.com/mzb/sz
        //如果发送了cookie  mobile_on = off 移动设备不会跳转
        if (ismobile()) {
            //是移动设备

            // 站点多域名访问支持 动态化httphost
            $schemeAndHost = getSchemeAndHost();
            $SCHEME_HOST   = $schemeAndHost;
            /** 'isSsl' => boolean false
             * 'scheme' => string 'http'
             * 'scheme_full' => string 'http://'
             * 'host' => string 'www.qizuang.com'
             * 'domain' => string 'qizuang.com'
             * 'scheme_host' => string 'http://www.qizuang.com'
             */

            //获取url后面的参数
            $param = explode("?", $_SERVER['REQUEST_URI']);

            //if (false == $mobile['m_to_pc']) {  //没有m_to_pc = on 标识才跳转
            // 得到访问域名, 别名
            list($csbm)   = explode('.', $_SERVER['HTTP_HOST']);
            if(empty($_COOKIE["m_to_pc"])){
                //seo需求:PC端-分站首页做301跳转
                if($csbm != 'www' && $_SERVER['REQUEST_URI'] == '/'){
                    header('HTTP/1.1 301 Moved Temporarily');
                }else{
                    header('HTTP/1.1 302 Moved Temporarily');
                }
                if($csbm != "www"){

                    //header("Location: http://m.".C('QZ_YUMING')."/".$csbm."/");
                    //header("Location: http://www.qizuang.com/mobile/zb?cs=". $csbm); //新版招标单页
                    header("Location: " . $SCHEME_HOST['scheme_full'] . 'm.' . $SCHEME_HOST['domain'] . "/" . $csbm . "/"); //新移动版招标 带城市

                }else{
                    //header("Location: http://m.".C('QZ_YUMING')."/");
                    //header("Location: http://www.qizuang.com/mobile/zb"); //新版招标单页
                    if($_SERVER['PATH_INFO'] == 'zxbj'){
                        header("Location: " . $SCHEME_HOST['scheme_full'] . 'm.' . $SCHEME_HOST['domain'] . "/baojia?" . $param[count($param)-1]); //新移动版招标
                    }else{
                        header("Location: " . $SCHEME_HOST['scheme_full'] . 'm.' . $SCHEME_HOST['domain'] . "/"); //新移动版招标
                    }
                    
                }
                die();
            }
        }
    }
}