<?php

namespace Common\Model\Logic;

use Common\Enums\ArticleCodeEnum;
use Common\Enums\ThematicWordsCodeEnum;
use Common\Model\AskModel;
use Common\Model\Db\ThematicWordsModel;
use Common\Model\Db\WwwArticleModel;

class ArticleLogicModel
{
    protected $wwwArticleModel;

    public function __construct()
    {
        $this->wwwArticleModel = new WwwArticleModel();
    }

    public function getAskListByKeywords($keywords, $num = 5)
    {
        $askIds = $askList = [];
        $thematicDb = new ThematicWordsModel();
        if (count($keywords) > 0) {
            //多个标签,直到查询到指定数据
            foreach ($keywords as $k => $v) {
                $askIdsCount = count($askIds);
                if ($askIdsCount == $num) {
                    break;
                }
                //查询当前标签对应的ask
                $where = [
                    'a.thematic_id' => ['eq', $v['id']],
                    'a.module' => ['eq', ThematicWordsCodeEnum::MODULE_CODE_WD]
                ];
                $ask = $thematicDb->getRelated($where, 'a.content_id', $num);
                if (count($ask) > 0) {
                    foreach ($ask as $vv) {
                        if (count($askIds) == $num) {
                            break;
                        }
                        $askIds[] = $vv['content_id'];
                    }
                }
            }
        }
        //获取指定的问答数据
        if (count($askIds) > 0) {
            $askDb = new AskModel();
            $askList = $askDb->getAskList(['id' => ['in', $askIds]], 'id,title,anwsers');
        }
        return $askList;
    }

    /**
     * 通过别名层级来获取数据
     * @param string $class_short
     * @param int $num
     * @param int $level 类别的层级 文章直接关联的是第三层
     * @return mixed
     */
    public function getArticleByPidClass($class_short = ArticleCodeEnum::ARTICLE_CLASS_Q, $num = 4, $level = 2, $field = '')
    {
        $db = new WwwArticleModel();
        if (empty($field)) {
            $field = 'a.id,a.title,c.shortname,c.classname,a.face,a.subtitle';
        }
        $class = [];
        $classDb = new WwwArticleModel();
        switch ($level) {
            case 1:
                //todo 暂时未用到查询一级保留
                break;
            case 2:
                //获取当前分类的子分类
                $class = $classDb->getClassIdByPShort($class_short);
                break;
            case 3:
                $class = $classDb->getClassIdByShort($class_short);
                break;
        }
        if (empty($class)) {
            return [];
        }
        $list = $db->getArticleByShort(array_column($class, 'id'), $field, 0, $num);
        foreach ($list as $k => $v) {
            $list[$k]['subtitle'] = trim(str_replace('&nbsp;', '', $v['subtitle']));
        }
        return $list;
    }

    public function getArticleInfo($id, $category)
    {
        $articleDb = new WwwArticleModel();
        $article = $articleDb->getArticleInfo($id, $category);
        if (empty($article["shortname"])) {
            $article["shortname"] = "history";
            $article["classname"] = "历史资讯";
            $article["title"] = str_replace("_齐装网", "", $article["title"]);
        }

        // 如果文章不是来自于软文则作以下处理
        if (empty($article["from_ruanwen"])) {
            //处理文章中的图片
            $pattern = '/<img.*?\/>/is';
            preg_match_all($pattern, $article["content"], $matches);
            if (count($matches[0]) > 0) {
                foreach ($matches[0] as $k => $val) {
                    $pattern = '/src=[\"|\'](.*?)[\"|\']/is';
                    preg_match_all($pattern, $val, $m);
                    foreach ($m[1] as $j => $v) {
                        //判断是否有七牛的域名前缀,没有就补上
                        if (!strpos($v, C('QINIU_DOMAIN'))) {
                            $path = "https://" . C('STATIC_HOST1') . $v;
                            $article["content"] = str_replace($v, $path, $article["content"]);
                        }

                        //去水印
                        if (strpos($v, '-s3.')) {
                            $path = str_replace('-s3.', '-s5.', $v);
                            $article["content"] = str_replace($v, $path, $article["content"]);
                        }
                    }
                }
            }
        }

        //查询文章关键字，替换成内链
        $keywords = D("Wwwarticlekeywords")->getKeywordsRelate($id, "wwwarticle");
        if (count($keywords) > 0) {
            foreach ($keywords as $key => $value) {
                $list[] = "/" . trim($value["name"]) . "/";
            }
        }

        //抽出文章中的所有链接，避免替换链接出现重叠现象(链接套链接)
        $linkPattern = '/<a.*?>.*?<\/a>/i';
        preg_match_all($linkPattern, $article["content"], $linkMatches);
        if (count($linkMatches[0]) > 0) {
            foreach ($linkMatches[0] as $key => $value) {
                //将图片替换成变量占位符
                $article["content"] = str_replace($value, "#&!&#", $article["content"]);
                $replaceLink[] = $value;
            }
        }

        //抽出文章中的图片
        $pattern = '/<img.*?\/>/i';
        preg_match_all($pattern, $article["content"], $matches);
        if (count($matches[0]) > 0) {
            foreach ($matches[0] as $key => $value) {
                //将图片替换成变量占位符
                $article["content"] = str_replace($value, "%s", $article["content"]);
                $replaceImg[] = $value;
            }
        }

        foreach ($list as $key => $value) {
            preg_match_all($value, $article["content"], $matches);
            if (count($matches[0]) > 0) {
                $link = "<a href='" . $keywords[$key]["href"] . "' target='_blank' class='inlink-word-color'>" . $keywords[$key]["name"] . "</a>";
                $article["content"] = preg_replace($value, $link, $article["content"], 1);
            }
        }
        //将所有的图片依次填充到原来位置
        foreach ($replaceImg as $key => $value) {
            $article["content"] = preg_replace("/\%s/", $value, $article["content"], 1);
        }

        //将所有的链接依次填充到原来位置
        foreach ($replaceLink as $key => $value) {
            $article["content"] = preg_replace("/#&!&#/", $value, $article["content"], 1);
        }
        return $article;
    }

    /**
     * 根据 第一级的分类的id获取三级分类下的文章列表
     * @param $grandClass
     * @param $count
     * @return mixed
     */
    public function getByGrandClass($grandClass, $count)
    {
        $data = $this->wwwArticleModel->getByGrandClass($grandClass, $count);
        foreach ($data as $key => $value) {
            $data[$key] = $this->transformListData($value);
        }
        return $data;
    }

    /**
     * 全屋定制首页-根据全屋定制的id获取不在某些中的数据
     * @param $grandClass
     * @param $count
     * @return mixed
     */
    public function getHotNotIdByGrandClass($grandClass, $ids, $count)
    {
        //获取不在该id中的分类
        $data = $this->wwwArticleModel->getHotNotIdByGrandClass($grandClass, $ids, $count);

        foreach ($data as $key => $value) {
            $data[$key] = $this->transformListData($value);
        }
        return $data;
    }

    /**
     * 根据第一级分类获取数据
     * @param $grandClass
     * @param $count
     * @return mixed
     */
    public function getHotByGrandClass($grandClass, $count)
    {
        $data = $this->wwwArticleModel->getHotByGrandClass($grandClass, $count);
        foreach ($data as $key => $value) {
            $data[$key] = $this->transformListData($value);
        }
        return $data;
    }

    /**
     * 根据第一级分类获取今日数据
     * @param $grandClass
     * @param $count
     * @return array|mixed
     */
    public function getTodayHotByGrandClass($grandClass, $count)
    {
        $data = $this->wwwArticleModel->getTodayHotByGrandClass($grandClass, $count);
        if (count($data) < $count) {
            $leftCount = $count - count($data);
            $leftData = $this->wwwArticleModel->getHotByGrandClass($grandClass, $leftCount);
            $data = array_merge($data, $leftData);
        }
        foreach ($data as $key => $value) {
            $data[$key] = $this->transformListData($value);
        }
        return $data;
    }

    /**
     * 根据一级和第二级分类获取数据
     * @param $grandClass
     * @param $parentClass
     * @param $count
     * @return mixed
     */
    public function getByParentCLass($grandClass, $parentClass, $count)
    {
        $data = $this->wwwArticleModel->getByParentCLass($grandClass, $parentClass, $count);
        foreach ($data as $key => $value) {
            $data[$key] = $this->transformListData($value);
        }
        return $data;
    }

    /**
     * 根据第一级分类和第三级分类获取文章的数据
     * @param $p
     * @param $perCount
     * @param $params
     * @return array
     */
    public function getListByGrandClassAndClass($p, $perCount, $params)
    {
        $count = $this->wwwArticleModel->getCountByGrandClassAndClass($params);
        $ret = [
            'page' => '',
            'list' => [],
        ];
        if ($count > 0) {
            $schemeHost = getSchemeAndHost();

            import('Library.Org.Page.LitePage');
            $config = array("prev", "next");
            $extra = [
                'baseURL' => $schemeHost["scheme_full"] . C('QZ_YUMINGWWW') . '/qwdz/news/' . $params['shortname'] . '/',
                'params' => $params,
            ];
            $page = new \LitePage($p, $perCount, $count, $config, $extra);
            $show = $page->tuShow();
            $data = $this->wwwArticleModel->getListByGrandClassAndClass(($p - 1) * $perCount, $perCount, $params);
            $formattedData = [];
            if (!empty($data)) {
                foreach ($data as $value) {
                    $formattedData[] = $this->transformListData($value);
                }
            }
            if ($count < $perCount) {
                $show = '';
            }
            $ret['page'] = $show;
            $ret['list'] = $formattedData;
        }
        return $ret;
    }

    /**
     * 根据第一级分类和第二级分类后去下面的所有子分类的文章数据
     * @param $p
     * @param int $perCount
     * @param $params
     * @return array
     */
    public function getListByGrandClassAndParentClass($p, int $perCount, $params)
    {
        $count = $this->wwwArticleModel->getCountByGrandClassAndParentClass($params);
        $ret = [
            'page' => '',
            'list' => [],
        ];
        if ($count > 0) {
            $schemeHost = getSchemeAndHost();
            import('Library.Org.Page.LitePage');
            $config = array("prev", "next");
            $extra = [
                'baseURL' => $schemeHost["scheme_full"] . C('QZ_YUMINGWWW') . '/qwdz/news/' . $params['shortname'] . '/',
                'params' => $params,
            ];
            $page = new \LitePage($p, $perCount, $count, $config, $extra);
            $show = $page->tuShow();
            $data = $this->wwwArticleModel->getListByGrandClassAndParentClass(($p - 1) * $perCount, $perCount, $params);
            $formattedData = [];
            if (!empty($data)) {
                foreach ($data as $value) {
                    $formattedData[] = $this->transformListData($value);
                }
            }
            if ($count < $perCount) {
                $show = '';
            }
            $ret['page'] = $show;
            $ret['list'] = $formattedData;
        }
        return $ret;
    }

    /**
     * 增加某个字段的数量
     * @param $id
     * @param $field
     * @param $step
     * @return bool
     */
    public function incField($id, $field, $step)
    {
        return $this->wwwArticleModel->incField($id, $field, $step);
    }

    /**
     * 清洗数据
     * @param $value
     * @return mixed
     */
    private function transformListData($value)
    {
        if (empty($value)) {
            return [];
        }
        if (isset($value['imgs'])) {
            $value['images'] = $value['imgs'];
            $images = explode(',', $value['imgs']);
            $value['imgs'] = str_replace('-s3.jpg', '', $images[0]);
        }

        if (isset($value['face'])) {
            $value['imgs'] = changeImgUrl($value['face']);
        }

        if (isset($value['content'])) {
            $value['content'] = strip_tags($value['content']);
        }

        if (isset($value['addtime'])) {
            $value['date'] = date('Y-m-d H:i', $value['addtime']);
        }

        return $value;
    }
}