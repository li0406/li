<?php
/**
 *  自然访问相关
 *  无直接对应的数据库表
 *  用户从搜索引擎搜索内容,从非付费链接点击进入的叫做自然流量
 *  用户从微信等三方平台点击进入的也叫自然流量
 *  判断原理为,从HTTP_REFERER识别访问来源
 */
namespace Common\Model\Logic;

class NatureSearchVisitLogicModel {

    protected $autoCheckFields = false;

    /**
     * 获取referer
     * @return mixed
     */
    public function getReferer($referer="")
    {
        if(empty($referer)){
            return $_SERVER['HTTP_REFERER'];
        }
        return $referer;
    }

    /**
     * 解析referer
     * 按照url结构
     * @return mixed
     */
    public function parseReferer($referer="")
    {
        if(empty($referer)) {
            $referer = self::getReferer();
        }
        return parse_url($referer);
    }


    /**
     *
     * 定义搜索引擎的referer
     *
     * @param int $index
     *
     * @return array|mixed
     */
    public function searchEngineReferers($index=-1)
    {
       $searchEngineReferers = array(
            'www.baidu.com/link?url=',
            'm.baidu.com/from=',
            'www.so.com/link?m=',
            'm.so.com/s?q=',
            'www.sogou.com/link?url=',
            'm.sogou.com/web/searchList.jsp?',
            'wap.sogou.com/web/searchList.jsp?',
            'cn.bing.com/search?q=',
            'm.sm.cn/s?q=',
            'm.sm.cn/s?uc_param_str=',
        );

        if($index >= 0){
            return $searchEngineReferers[$index];
        }
        return $searchEngineReferers;
    }

    /**
     * 通过预定义referer判断是否来自搜索引擎自然搜索
     *
     * @return bool
     */
    public function isFromNatureSearch($referer="")
    {

        //处理referer
        $referer = self::getReferer($referer);

        // 排除操作
        if (empty($referer)) {
            //排除referer访问来源为空 返回null
            //dump('referer为空!');
            //$status_refererIsEmpty = true; //状态定义
            return false;
        } else {
            //排除referer访问来源为齐装网本站
            $refererParseUrl = self::parseReferer($referer);
            if (!empty($refererParseUrl)) {
                $refererHost = $refererParseUrl['host'];
                $yuming    = C('QZ_YUMING');
                $yumingFound = stristr($refererHost, $yuming);
                if ($yumingFound) {
                    //如果来源为齐装网本站 返回null
                    //dump('referer 为齐装网');
                    //$status_refererIsQz = true; //状态定义
                    return false;
                }
            }
        }


        // 把预定义的规则列表拿去和referer对比
        $searchEngineReferers = self::searchEngineReferers();
        $isSearchFlag = false;
        foreach ($searchEngineReferers as $key => $value) {
            $pos = strpos($referer,$value);
            if ($pos !== false) {
                $isSearchFlag = true;
                break;
            }
        }
        if($isSearchFlag){
            return true;
        }

        return false;

    }

}
