<?php
// 访问来源追踪,来源进行cookie标记
// 逻辑当有get参数 src的时候,例如 ?src=video
// 打客户端浏览器cookie标记src_mark 有效时间为8小时


namespace Common\Behavior;

use Think\Behavior;

class MarkTrackingOrderSourceCheckBehavior extends Behavior
{
    static protected $expire = 28800; //设置src cookie的有效时间为 8 小时

    public function run(&$params)
    {
        self::srcMarkCookie();
    }

    static private function srcMarkCookie()
    {
        $expire = self::$expire;

        //UUID标识
        $uuid = create_guid();

        //get参数 src 标记
        $getsrc = I('get.src');

        // 如果不带get src参数进行其他匹配
        if (empty($getsrc)) {
            //根据header字段规则匹配
            $header                          = [];
            $header['HTTP_X_REQUESTED_WITH'] = $_SERVER['HTTP_X_REQUESTED_WITH'];
            $headerRuleSrc                   = self::getSrcByHeaderRule($header);
            //dump($headerRuleSrc);
            if (empty($headerRuleSrc)) {
                //根据referer规则匹配
                $referer        = $_SERVER['HTTP_REFERER'];
                $refererRuleSrc = self::getSrcByRefererRule($referer);
                //dump($refererRuleSrc);
                if (empty($refererRuleSrc)) {
                    //根据agent规则匹配
                    $agent        = $_SERVER["HTTP_USER_AGENT"];
                    $agentRuleSrc = self::getSrcByAgentRule($agent);
                    //dump($agentRuleSrc);
                    if (!empty($agentRuleSrc)) {
                        // src 为agent匹配结果
                        $getsrc = $agentRuleSrc;
                    }
                } else {
                    // src 为referer匹配结果
                    $getsrc = $refererRuleSrc;
                }

            } else {
                // src 为header匹配结果
                $getsrc = $headerRuleSrc;
            }
        }

        if (!empty($getsrc)) {
            //如果有src标记

            //设置cookie src_mark
            cookie('src_mark', $getsrc, ['expire' => $expire, 'domain' => '.' . APP_HTTP_DOMAIN]);

            //扩展cookie标记, 运营系统采集用
            //采集统计数据的标识
            cookie('QZUUID', $uuid, ['expire' => $expire, "path" => "/", 'domain' => '.' . APP_HTTP_DOMAIN]);
            cookie('QZSRC', $getsrc, ['expire' => $expire, "path" => "/", 'domain' => '.' . APP_HTTP_DOMAIN]);

            //统计
            self::urlStats($getsrc);

        } else {
            //如果没有src标记
            if (cookie('QZSRC') === null) {
                //如果cookie中没有QZSRC则标记none
                cookie('QZUUID', $uuid, ['expire' => $expire, "path" => "/", 'domain' => '.' . APP_HTTP_DOMAIN]);
                cookie('QZSRC', 'none', ['expire' => $expire, "path" => "/", 'domain' => '.' . APP_HTTP_DOMAIN]);
            }
        }
    }

    static private function urlStats($getsrc)
    {

        //判断是否是搜索引擎蜘蛛
        $robotIsTrue = B("Common\Behavior\RobotCheck");
        if (true === $robotIsTrue) {
            return false;
        }

        $expire = self::$expire;

        $ip        = get_client_ip('0', true);
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        //生成用户唯一信息
        $authKey  = md5('12345678Qizuangcom&stats' . $userAgent . $ip);
        $statsKey = cookie('statsKey_' . $getsrc);

        if (empty($statsKey) || $authKey != $statsKey) {

            //判断是否存在此Src
            $isHaveSrc = D('Common/TrackingOrderSource')->getPromoteSrc($getsrc);
            if (empty($isHaveSrc)) {
                return false;
            }

            $scheme_host = getSchemeAndHost();

            $time    = time();
            $referer = empty($_SERVER["HTTP_REFERER"]) ? '' : $_SERVER["HTTP_REFERER"];
            $url     = $scheme_host["scheme_full"] . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            //生成SQL数据
            $data = [
                'ip'      => $ip,
                'url'     => $url,
                'referer' => $referer,
                'ua'      => '',
                'src'     => $getsrc,
                'time'    => $time,
                'year'    => date('Y'),
                'month'   => date('m'),
                'days'    => date('d'),
            ];
            M("url_stats")->add($data);

            // +++++ 检查URL每日统计表 +++++++++
            //查询是否有此SRC今天统计
            $urlDayCount = [
                'src'   => $getsrc,
                'year'  => date('Y'),
                'month' => date('m'),
                'days'  => date('d'),
                'dates' => date('Y') . date('m') . date('d'),
            ];
            $isCount     = M("url_stats_count")->field('id')->where($urlDayCount)->find();
            if (empty($isCount)) {
                M("url_stats_count")->add($urlDayCount);
            } else {
                M('url_stats_count')->where(['id' => $isCount['id']])->setInc('num');
            }

            // +++++++ 按SRC统计当天IP数 ++++++++
            //判断是否有IP
            $ipMap      = [
                'ip'    => $ip,
                'src'   => $getsrc,
                'year'  => date('Y'),
                'month' => date('m'),
                'days'  => date('d'),
            ];
            $theIpCount = M('url_stats')->field('ip')->where($ipMap)->count();
            //如果IP数小于2就增加IP
            if ($theIpCount < 2) {
                unset($ipMap['ip']);
                M('url_stats_count')->where($ipMap)->setInc('ip_num');
            }

            cookie('statsKey_' . $getsrc, $authKey, $expire);
        }
    }

    /**
     *
     * 根据header rule 返回对应的src
     *
     * @return string|null 渠道来源 src 名称
     *
     */
    static private function getSrcByHeaderRule($header = "")
    {
        //  ["HTTP_X_REQUESTED_WITH"] => string(19) "com.baidu.searchbox"
        if (empty($header)) {
            $header                          = [];
            $header['HTTP_X_REQUESTED_WITH'] = $_SERVER['HTTP_X_REQUESTED_WITH'];
        }
        $xRequestedWith = $header["HTTP_X_REQUESTED_WITH"];

        // 百度APP中跳到H5
        if (!empty($xRequestedWith)) {
            if (stristr($xRequestedWith, "com.baidu.searchbox")) {
                return "zrss-bd-app";
            }
        }
        return null;
    }

    /**
     *
     * 根据referer rule 返回对应的src
     *
     * @return string|null 渠道来源 src 名称
     *
     */
    static private function getSrcByRefererRule($referer = "")
    {

        //处理referer
        if (empty($referer)) {
            $referer = $_SERVER['HTTP_REFERER'];
        }

        //dump($referer);

        if (empty($referer)) {
            //排除referer访问来源为空 返回null
            //dump('referer为空!');
            $status_refererIsEmpty = true; //状态定义
            return null;
        } else {
            //排除referer访问来源为齐装网本站
            $refererParseUrl = parse_url($referer);
            if (!empty($refererParseUrl)) {
                $refererHost = $refererParseUrl['host'];
                $yuming      = C('QZ_YUMING');
                $yumingFound = stristr($refererHost, $yuming);
                if ($yumingFound) {
                    //如果来源为齐装网本站 返回null
                    //dump('referer 为齐装网');
                    $status_refererIsQz = true; //状态定义
                    return null;
                }
            }
        }


        //自定义需要强制标记的src 在后台 渠道来源标识中已经增加好
        $srcDiyArr = [
            1  => 'zrss-bd-p',    //百度自然搜索 PC端
            2  => 'zrss-bd-m',    //百度自然搜索 M端
            3  => 'zrss-360-p',   //360自然搜索 PC端
            4  => 'zrss-360-m',   //360自然搜索 M端
            5  => 'zrss-sg-p',    //搜狗自然搜索 PC端
            6  => 'zrss-sg-m',    //搜狗自然搜索 M端
            7  => 'zrss-bing',    //bing自然搜索
            //8  => '',           //预留
            9  => 'zrss-sm-m',    //UC(神马)自然搜索 M端
            //10 => '',           //UC(神马)自然搜索
            //20 => '',           //预留
            10 => 'jrttapp',
        ];

        //如果cookie中已经有标记过了 就跳过下面的匹配过程
        // 暂时不要这个逻辑 2017年12月8日 14:32:49
        // $src_mark =  cookie('src_mark');
        // if (in_array($src_mark, array_values($srcDiyArr))) {
        //     //dump('入口来源于搜索引擎 src 已经标记过了');
        //     return null;
        // }

        //如果是搜索引擎自然搜索过来的给定指定的渠道来源src标记名称
        $searchEngineReferrer2src = [
            'www.baidu.com/link?url='           => $srcDiyArr[1],
            'm.baidu.com/from='                 => $srcDiyArr[2],
            'www.so.com/link?m='                => $srcDiyArr[3],
            'm.so.com/s?q='                     => $srcDiyArr[4],
            'www.sogou.com/link?url='           => $srcDiyArr[5],
            'm.sogou.com/web/searchList.jsp?'   => $srcDiyArr[6],
            'wap.sogou.com/web/searchList.jsp?' => $srcDiyArr[6],
            'cn.bing.com/search?q='             => $srcDiyArr[7],
            'm.sm.cn/s?q='                      => $srcDiyArr[9],
            'm.sm.cn/s?uc_param_str='           => $srcDiyArr[9],
            't.zijieimg.com'                    => $srcDiyArr[10],
        ];

        foreach ($searchEngineReferrer2src as $key => $value) {
            $urlFound = stristr($referer, $key);
            if (!empty($urlFound)) {
                //dump('标记搜索引擎src:'.$value);
                $status_isSearchEngine = true; //状态定义
                return $value;
            }
        }
        unset($value);

        //****外部连接处理上有较多问题暂时不做***
        //如果是外部连接来的给指定的渠道来源src标记名称
        //外部链接
        //满足所有条件:
        //1.没有标记的
        //2.有访问来源的(referer不为空)
        //3.访问来源 非搜索引擎自然搜索的
        //4.访问来源 非齐装网本身的
        /*if ( ($src_mark === null || $src_mark === 'none')
              && !$status_refererIsEmpty
              && !$status_isSearchEngine
              && !$status_refererIsQz ) {

                $status_isWblj = true; //状态定义
                return$srcDiyArr[20];
        }*/


        return null;

    }

    /**
     *
     * 根据agent rule 返回对应的src
     *
     * @return string|null 渠道来源 src 名称
     *
     */
    static private function getSrcByAgentRule($agent = "")
    {
        if (empty($agent)) {
            $agent = $_SERVER["HTTP_USER_AGENT"];
        }

        //今日头条APP跳转过来,并且是站外文章
        if (strpos($agent, "TTWebView") !== false && $_SERVER["HTTP_REFERER"] == "") {
            return "jrttapp";
        }

        return null;
    }

}
