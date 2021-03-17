<?php
// 访问来源追踪,来源进行cookie标记
// 逻辑当有get参数 src的时候,例如 ?src=video
// 打客户端浏览器cookie标记src_mark 有效时间为8小时


namespace Common\Behavior;
use Think\Behavior;

class MarkTrackingOrderSourceCheckBehavior extends Behavior
{
    static protected $expire  = 28800; //设置src cookie的有效时间为 8 小时

    public function run(&$params)
    {
        self::srcMarkCookie();
    }

    static private function srcMarkCookie()
    {
        //get参数 src 标记
        $getsrc = I('get.src');

        if (empty($getsrc)) {
            //如果没带get src的访问就进行referer规则匹配
            $rulesrc = self::getSrcByRule();
            if ($rulesrc !== null) {
                //如果强制匹配referer标记的src不为空,就重写$getsrc
                $getsrc = $rulesrc;
            }
        }

        if (!empty($getsrc)) {
            //如果有src标记

            //设置cookie src_mark
            cookie('src_mark', $getsrc, array('expire' => self::$expire, 'domain' => '.' . C('QZ_YUMING')));

        }
    }

    /**
     * 根据规则生成需要强制标记的src
     * @return string|null 渠道来源 src 名称
     */
    static private function getSrcByRule()
    {

        //处理referer
        $referer = $_SERVER['HTTP_REFERER'];

        if (empty($referer)) {
            //排除referer访问来源为空 返回null
            return null;
        } else {
            //排除referer访问来源为齐装网本站
            $refererParseUrl = parse_url($referer);
            if (!empty($refererParseUrl)) {
                $refererHost = $refererParseUrl['host'];
                $yuming = C('QZ_YUMING');
                $yumingFound = stristr($refererHost, $yuming);
                if ($yumingFound) {
                    //如果来源为齐装网本站 返回null
                    return null;
                }
            }
        }

        //自定义需要强制标记的src 在后台 渠道来源标识中已经增加好
        $srcDiyArr = array(
            1 => 'zrss-bd-p',    //百度自然搜索 PC端
            2 => 'zrss-bd-m',    //百度自然搜索 M端
            3 => 'zrss-360-p',   //360自然搜索 PC端
            4 => 'zrss-360-m',   //360自然搜索 M端
            5 => 'zrss-sg-p',    //搜狗自然搜索 PC端
            6 => 'zrss-sg-m',    //搜狗自然搜索 M端
            7 => 'zrss-bing',    //bing自然搜索
            //8  => '',           //预留
            9 => 'zrss-sm-m',    //UC(神马)自然搜索 M端
            //20 => '',           //预留
        );

        //如果是搜索引擎自然搜索过来的给定指定的渠道来源src标记名称
        $searchEngineReferrer2src = array(
            'www.baidu.com/link?url=' => $srcDiyArr[1],
            'm.baidu.com/from=' => $srcDiyArr[2],
            'www.so.com/link?m=' => $srcDiyArr[3],
            'm.so.com/s?q=' => $srcDiyArr[4],
            'www.sogou.com/link?url=' => $srcDiyArr[5],
            'm.sogou.com/web/searchList.jsp?' => $srcDiyArr[6],
            'wap.sogou.com/web/searchList.jsp?' => $srcDiyArr[6],
            'cn.bing.com/search?q=' => $srcDiyArr[7],
            'm.sm.cn/s?q=' => $srcDiyArr[9],
            'm.sm.cn/s?uc_param_str=' => $srcDiyArr[9],
        );

        foreach ($searchEngineReferrer2src as $key => $value) {
            $urlFound = stristr($referer, $key);
            if (!empty($urlFound)) {
                return $value;
            }
        }
        unset($value);
        return null;

    }

}
