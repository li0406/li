<?php
/**
 * 搜索引擎相关
 */
namespace Home\Model;

use Think\Model;

class SearchengineModel extends Model {

    //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。
    protected $autoCheckFields = false;

    /**
     * 插入待提交更新的站点以及这个站点的url地址
     * @param  str  $site 站点如 sz.qizuang.com
     * @param  str $url  url 如 http://www.qizuang.com/xgt/
     * @return msyql result
     */
    public function insrtTasklistPostBaidu($site,$url) {
        if (empty($site) || empty($url)) {
            return false;
        }
        $data             = array();
        $data['site']     = $site;
        $data['url']      = $url;
        $data['time_add'] = date('Y-m-d H:i:s');
        return M('tasklist_post_searchengine')->add($data);

    }

    /**
     * 删除任务列表中待更新的url
     * @param  str $site 站点
     * @return str       [description]
     */
    public function delTasklistPostBaidu($site = '') {
        if (!empty($site)) {
            $map = array('site'=>$site);
        }
        return M('tasklist_post_searchengine')->where($map)->delete();
    }

    /**
     * 获取待提交的任务列表
     * @return
     *
     *
     *array(3) {
     *  'mas.qizuang.com' =>
     *  array(1) {
     *    [0] =>
     *    string(22) "http://mas.qizuang.com"
     *  }
     *  'sz.qizuang.com' =>
     *  array(5) {
     *    [0] =>
     *    string(21) "http://sz.qizuang.com"
     *    [1] =>
     *    string(21) "http://sz.qizuang.com"
     *    [2] =>
     *    string(21) "http://sz.qizuang.com"
     *    [3] =>
     *    string(21) "http://sz.qizuang.com"
     *    [4] =>
     *    string(21) "http://sz.qizuang.com"
     *  }
     *  'wh.qizuang.com' =>
     *  array(11) {
     *    [0] =>
     *    string(21) "http://wh.qizuang.com"
     *    [1] =>
     *    string(21) "http://wh.qizuang.com"
     *    [2] =>
     *    string(21) "http://wh.qizuang.com"
     *    [3] =>
     *    string(21) "http://wh.qizuang.com"
     *    [4] =>
     *    string(21) "http://wh.qizuang.com"
     *    [5] =>
     *    string(21) "http://wh.qizuang.com"
     *    [6] =>
     *    string(21) "http://wh.qizuang.com"
     *    [7] =>
     *    string(21) "http://wh.qizuang.com"
     *    [8] =>
     *    string(21) "http://wh.qizuang.com"
     *    [9] =>
     *    string(21) "http://wh.qizuang.com"
     *    [10] =>
     *    string(21) "http://wh.qizuang.com"
     *  }
     *}
     */
    public function getTasklistPostBaidu() {
        $tasklist = M('tasklist_post_searchengine')->order('site asc')->select();
        $site = array();
        $urls = array();
        if ($tasklist) {
            foreach ($tasklist as $key => $value) {
                    $site[] = $value['site'];
            }
            sort($site);
            $site = array_unique($site);
            foreach ($site as $key => $value) {
                foreach ($tasklist as $key => $tvalue) {
                    if ($value == $tvalue['site']) {
                        $urls[$value][] = $tvalue['url'];
                    }
                }
            }
            return $urls;
        }
        return false;

    }

    /**
     * 把请求 百度网址更新api后 返回的数据打日志
     * @param  json
     * @return mysql执行结果
     */
    public function logSePostResult($result, $remark = '') {
        if (empty($result)) {
            return false;
        }
        $data             = array();
        $data['action']   = 'baiduposturl';
        $data['info']     = $result;
        $data['remark']   = $remark;
        $data['time_add'] = date('Y-m-d H:i:s');
        return M('log_post_searchengine')->add($data);

    }

    /**
     * 发布文章,发布案后通过 获取 生成的完整url地址
     * @param  str $site 站点如 www.qizuang.com
     * @param  str $type 类型如 定义后台主站文章发布为 wwwArticle
     * @param  int $id  id号码
     * @return 完整的url地址 or false
     */
    public function markUrl($site, $type, $id) {
        if (empty($site) || empty($type) || empty($id)) {
            return false;
        }
        $fullurl = '';
        switch ($type) {
            case 'wwwArticle':
                $idClassInfo = D("WwwArticleClass")->getArticleClassByArticleId($id,'new');
                if ($idClassInfo) {
                    $fullurl = 'http://'. C('QZ_YUMINGWWW')
                                .'/gonglue/' . $idClassInfo['shortname'] . '/' . $id . '.html';
                    return $fullurl;
                }else{
                    return false;
                }
                break;
            case 'CompanyArticle':
                    $fullurl = 'http://'. $site . '/zixun_info/' . $id . '.shtml';
                    return $fullurl;
                break;

            case 'CompanyCase':
                    $fullurl = 'http://'. $site . '/caseinfo/' . $id . '.shtml';
                    return $fullurl;
                break;

            default:
                # code...
                break;
        }
        return false;

    }

}
?>