<?php
//检查是否是搜索引擎的访问
//用于 瀑布流 以及 图片延迟加载的时候  由于搜索引擎不解析js
//检查到是搜索引擎就给完整的html页面 利于 seo
/**
 * 机器人检测
 * @author wwek
 */

namespace Common\Behavior;
use Think\Behavior;

class RobotCheckBehavior extends Behavior {

    public function run(&$params) {
        // 机器人访问检测
        if(self::isRobot()) {
            return true; //如果是蜘蛛机器人访问就返回真
        }else{
            return false; //如果不是机器人访问就返回假
        }
    }

    static private function isRobot() {
        static $_robot = null;
        if(is_null($_robot)) {
            $kw_spiders  = array('bot', 'crawl', 'spider' ,'slurp', 'sohu-search', 'lycos', 'robozilla');
            $kw_browsers = array('msie', 'netscape', 'opera', 'konqueror', 'mozilla');
            $useragent   = strtolower($_SERVER['HTTP_USER_AGENT']);
            if(strpos($useragent, 'http://') === false && self::dstrpos($useragent, $kw_browsers)) {
                $_robot = false;
            };
            if(self::dstrpos($useragent, $kw_spiders)){
                $_robot = true;
            };
        }
        return $_robot;
    }

    static private function dstrpos($string, $arr, $returnvalue = false) {
        if(empty($string)) return false;
        foreach((array)$arr as $v) {
            if(strpos($string, $v) !== false) {
                $return = $returnvalue ? $v : true;
                return $return;
            }
        }
        return false;
    }


}