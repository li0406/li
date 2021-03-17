<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/3/18
 * Time: 9:27
 */
namespace Home\Model\Logic;

use Home\Model\Db\BaikeModel;
use Home\Model\Db\CheckKeywordModel;
use Home\Model\Db\WwwArticleModel;
use Home\Model\Db\AskModel;

class MarketContentLogicModel{
    /**
     * 获取攻略标题
     * @param $start
     * @param $end
     * @return array
     */
    public function getArticleTitle($start,$end){
        $wwwarticle = new WwwArticleModel();
        $map['state'] = 2;
        if(!empty($start)){
            $map["addtime"] = ["EGT",strtotime($start)];
        }

        if(!empty($end)){
            $map["addtime"] = ["ELT",strtotime($end)];
        }
        if(!empty($start) && !empty($end)){
            $map["addtime"] = ["between",[strtotime($start),strtotime($end)]];
        }

        $list = $wwwarticle->getArticle('title',$map);
       return array_column($list,'title');
    }

    /**
     * 获取问答标题
     * @param $start
     * @param $end
     * @return array
     */
    public function getAskTitle($start,$end){
        $ask = new AskModel();
        $map['visible'] = 0;
        if(!empty($start)){
            $map["create_time"] = ["EGT",strtotime($start)];
        }

        if(!empty($end)){
            $map["create_time"] = ["ELT",strtotime($end)];
        }
        if(!empty($start) && !empty($end)){
            $map["create_time"] = ["between",[strtotime($start),strtotime($end)]];
        }

        $list = $ask->getAsk('title',$map);
        return array_column($list,'title');
    }

    /**
     * 获取问答标题
     * @param $start
     * @param $end
     * @return array
     */
    public function getBaikeTitle($start, $end)
    {
        $baikeModel = new BaikeModel();
        $map['visible'] = 0;
        $map['remove'] = 0;
        if (!empty($start)) {
            $map["post_time"] = ["EGT", strtotime($start)];
        }

        if (!empty($end)) {
            $map["post_time"] = ["ELT", strtotime($end)];
        }
        if (!empty($start) && !empty($end)) {
            $map["post_time"] = ["between", [strtotime($start), strtotime($end)]];
        }
        //获取百科文章
        $list = $baikeModel->getArticle('title', $map);
        return array_column($list, 'title');
    }

    /**
     * 获取问答标题
     * @param $start
     * @param $end
     * @return array
     */
    public function getBaikeItem($start, $end)
    {
        $baikeModel = new BaikeModel();
        $map['visible'] = 0;
        $map['remove'] = 0;
        if (!empty($start)) {
            $map["post_time"] = ["EGT", strtotime($start)];
        }

        if (!empty($end)) {
            $map["post_time"] = ["ELT", strtotime($end)];
        }
        if (!empty($start) && !empty($end)) {
            $map["post_time"] = ["between", [strtotime($start), strtotime($end)]];
        }
        //获取百科文章
        $list = $baikeModel->getArticle('item', $map);
        return array_column($list, 'item');
    }

    /**
     * 获取文章子标题
     * @param $start
     * @param $end
     * @return array
     */
    public function getBaikeChildTitle($start,$end){
        $baikeModel  = new BaikeModel();
        $map['visible'] = 0;
        $map['remove'] = 0;
        if(!empty($start)){
            $map["post_time"] = ["EGT",strtotime($start)];
        }

        if(!empty($end)){
            $map["post_time"] = ["ELT",strtotime($end)];
        }
        if(!empty($start) && !empty($end)){
            $map["post_time"] = ["between",[strtotime($start),strtotime($end)]];
        }
        //获取百科文章
        $list = $baikeModel->getArticle('content',$map);
        //匹配子目录
        $reg ='/<strong[^>]*>([\w\W]*?)<\/strong\s*>/';
        $childTitle = [];
        foreach($list as $val){
            $matches = [];
            preg_match_all($reg, $val['content'], $matches);
            // 有些情况没有子目录,已下进行过滤(逻辑参照www部分)
            if(!empty($matches[1])){
                $temp = array_filter(preg_split($reg, $val['content']));
                //如果目录数量和分割的数量一致，则说明内容的最后面有strong标签，此种情况不算子目录
                if( count($matches[1]) !=  count($temp)){
                    $i = 1;
                    $content = [];
                    foreach ($temp as $key => $value) {
                        $value = trim($value);
                        if($i !=1 ){
                            $content[] = $value;
                        }
                        $i++;
                    }
                    //如果目录和内容不匹配，不算子目录
                    if(count($content) == count($matches[1])){
                        $childTitle = array_merge($childTitle,$matches[1]);
                    }
                }
            }
        }
        return $childTitle;
    }

    public function getBaikeChildTitle2($start, $end)
    {
        $baikeModel = new BaikeModel();
        $map['visible'] = 0;
        $map['remove'] = 0;
        if (!empty($start)) {
            $map["post_time"] = ["EGT", strtotime($start)];
        }

        if (!empty($end)) {
            $map["post_time"] = ["ELT", strtotime($end)];
        }
        if (!empty($start) && !empty($end)) {
            $map["post_time"] = ["between", [strtotime($start), strtotime($end)]];
        }
        //获取百科文章
        $list = $baikeModel->getArticle('content', $map);
        //匹配子目录
        $reg = '/<strong[^>]*>([\w\W]*?)<\/strong\s*>/';
        $childTitle = [];
        foreach ($list as $val) {
            $matches = [];
            preg_match_all($reg, $val['content'], $matches);
            // 有些情况没有子目录,已下进行过滤(逻辑参照www部分)
            if (!empty($matches[1])) {
                $temp = array_filter(preg_split($reg, $val['content']));
                //如果目录数量和分割的数量一致，则说明内容的最后面有strong标签，此种情况不算子目录
                if (count($matches[1]) != count($temp)) {
                    $i = 1;
                    $content = [];
                    foreach ($temp as $key => $value) {
                        $value = trim($value);
                        if ($i != 1) {
                            $content[] = $value;
                        }
                        $i++;
                    }
                    //如果目录和内容不匹配，不算子目录
                    if (count($content) == count($matches[1])) {
                        $matchJoin = [];
                        if (is_array($matches)) {
                            $matchJoin = [join('', $matches[1])];
                        }
                        $childTitle = array_merge($childTitle, $matchJoin);
                    }
                }
            }
        }
        return $childTitle;
    }

    //获取关键字
    public function getKeyWords(){
        $checkKeyModel = new CheckKeywordModel();
        return $checkKeyModel->getkeywords();
    }

    /**
     * 获取关键字出现次数
     * @param $arr
     */
    public function getKeyWordCount($keylist,$arr){
        foreach($arr as $val){
            foreach($keylist as $key=>$keywords){
                if(stripos($val,$keywords['keyword']) !== false){     //使用绝对等于
                    $keylist[$key]['count'] += 1;
                }
            }
        }

        return $keylist;
    }

    public function getKeyWordCount2($keylist, $arr)
    {
        foreach ($arr as $val) {
            foreach ($keylist as $key => $keywords) {
                if(stripos($val,$keywords['keyword']) !== false){     //使用绝对等于
                    $keylist[$key]['count'] += 1;
                }
            }
        }

        return $keylist;
    }

    public function getKeyWordCount3($keylist, $arr)
    {
        foreach ($arr as $val) {
            foreach ($keylist as $key => $keywords) {
                if(stripos($val,$keywords['keyword']) !== false){     //使用绝对等于
                    $keylist[$key]['count'] += 1;
                }
            }
        }

        return $keylist;
    }

    /**
     * 文章关键词数量
     * @param $start
     * @param $end
     * @return mixed
     */
    public function getArticleKeywords($start, $end)
    {
        $checkKeyModel = new CheckKeywordModel();
        $map['a.state'] = 2;
        if (!empty($start)) {
            $map["a.addtime"] = ["EGT", strtotime($start)];
        }

        if (!empty($end)) {
            $map["a.addtime"] = ["ELT", strtotime($end)];
        }
        if (!empty($start) && !empty($end)) {
            $map["a.addtime"] = ["between", [strtotime($start), strtotime($end)]];
        }
        $ret = $checkKeyModel->getArticleKeywordCount($map);
        return $ret;
    }

    /**
     * 问答关键词数量
     * @param $start
     * @param $end
     * @return mixed
     */
    public function getAskKeywords($start, $end)
    {
        $checkKeyModel = new CheckKeywordModel();
        $map['a.visible'] = 0;
        if (!empty($start)) {
            $map["a.create_time"] = ["EGT", strtotime($start)];
        }

        if (!empty($end)) {
            $map["a.create_time"] = ["ELT", strtotime($end)];
        }
        if (!empty($start) && !empty($end)) {
            $map["a.create_time"] = ["between", [strtotime($start), strtotime($end)]];
        }
        $ret = $checkKeyModel->getAskKeywordCount($map);
        return $ret;
    }

    public function getBaikeTitleKeywords($start, $end)
    {
        $checkKeyModel = new CheckKeywordModel();
        $map['a.visible'] = 0;
        $map['remove'] = 0;
//		if (!empty($start)) {
//			$map["a.post_time"] = ["EGT", strtotime($start)];
//		}
//
//		if (!empty($end)) {
//			$map["a.post_time"] = ["ELT", strtotime($end)];
//		}
//		if (!empty($start) && !empty($end)) {
//			$map["a.post_time"] = ["between", [strtotime($start), strtotime($end)]];
//		}

        $ret = $checkKeyModel->getBaikeTitleKeywordCount($map);
        return $ret;
    }

    public function getBaikeItemKeywords($start, $end)
    {
        $checkKeyModel = new CheckKeywordModel();
        $map['a.visible'] = 0;
        $map['remove'] = 0;
        if (!empty($start)) {
            $map["a.post_time"] = ["EGT", strtotime($start)];
        }

        if (!empty($end)) {
            $map["a.post_time"] = ["ELT", strtotime($end)];
        }
        if (!empty($start) && !empty($end)) {
            $map["a.post_time"] = ["between", [strtotime($start), strtotime($end)]];
        }

        $ret = $checkKeyModel->getBaikeItemKeywordCount($map);
        return $ret;
    }

    /**
     * 计算关键词的次数
     * @param array $keywords
     * @param array $article
     * @param array $baikeItem
     * @param array $wenda
     * @param array $baikeTitle
     * @return array
     */
    public function calcKeywordCount(array $keywords, array $article, array $baikeItem, array $wenda, array $baikeTitle)
    {

        $formatArticle = array_combine(array_column($article,'id'),array_column($article,'count'));
        $formatBaikeItem = array_combine(array_column($baikeItem,'id'),array_column($baikeItem,'count'));
        $formatWenda = array_combine(array_column($wenda,'id'),array_column($wenda,'count'));
        $formatBaikeTitle = array_combine(array_column($baikeTitle,'id'),array_column($baikeTitle,'count'));

        foreach ($keywords as $key => $value) {
            $keyword = $value['keyword'];
            $id = $value['id'];
            $articleCount = isset($formatArticle[$id]) ? $formatArticle[$id]: 0;
            $baikeItemCount = isset($formatBaikeItem[$id]) ? $formatBaikeItem[$id] : 0;
            $wendaCount = isset($formatWenda[$id]) ? $formatWenda[$id]: 0;
            $baikeTitleCount = isset($formatBaikeTitle[$id]) ? $formatBaikeTitle[$id] : 0;

            $ret[$key]['count'] = $articleCount + $baikeItemCount + $wendaCount + $baikeTitleCount;
            $ret[$key]['keyword'] = $keyword;
        }
        return $ret;
    }
}