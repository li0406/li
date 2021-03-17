<?php
namespace Common\Model\Service;

use Common\Model\Db\WwwArticleModel;

class ElasticSearchServiceModel
{
    private $url;

    public function __construct()
    {
        $config = C("SERVICE.ELASTIC");
        $this->url = $config['PROTOCOL']."://".$config['DOMAIN'].":".$config['PORT'];
    }

    public function getThematicSearch($name,$pageIndex,$pageCount)
    {
        $url =  $this->url."/v1/themtic/thematicsearch?word=".$name."&page=".$pageIndex."&count=".$pageCount;

        $result = curl($url);
        $list = [];
        if ($result["error_code"] == 0) {
            $list = $result["data"];
        }
        return $list;

    }

    public function getThematicTuSearch($word, $page = 1, $limit = 40)
    {
        $param = array(
            "word" => trim($word),
            "page" => $page,
            "limit" => $limit,
        );

        $urlparam = http_build_query($param);
        $url = $this->url . "/v1/themtic/thematic_tu_search?" . $urlparam;

        $list = [];
        $result = curl($url);
        if (is_array($result) && $result["error_code"] == 0) {
            $list = $result["data"];
        }

        return $list;
    }

    public function getContentLabel($title,$type,$limit = 5)
    {
        $title = preg_replace('/\s/',"",$title);
        $param = [
            "title" => trim($title),
            "type" => $type,
            "limit" => $limit
        ];

        $urlparam = http_build_query($param);
        $url = $this->url . "/v1/themtic/getlabel?" . $urlparam;

        $result = curl($url);
        $list = [];
        if ($result["error_code"] == 0) {
            foreach ($result["data"] as $item) {
                $list[] = $item;
            }
        }
        return $list;
    }

    public function getContentWords($title,$type,$limit = 5)
    {
        $title = preg_replace('/\s/',",",$title);

        $param = [
            "title" => trim($title),
            "type" => $type,
            "limit" => $limit
        ];

        $urlparam = http_build_query($param);
        $url = $this->url . "/v1/themtic/getwords?" . $urlparam;

        $result = curl($url);

        $list = [];
        if ($result["error_code"] == 0) {
            foreach ($result["data"] as $item) {
                $list[] = $item;
            }
        }
        return $list;
    }

    public function getThemticLabels($title, $type, $limit = 5) {
        $title = preg_replace('/\s/', ",", $title);
        $param = [
            "title" => trim($title),
            "type" => $type,
            "limit" => $limit
        ];

        $urlparam = http_build_query($param);
        $url = $this->url . "/v1/themtic/getlabels?" . $urlparam;

        $result = curl($url);
        $list = [];
        if ($result["error_code"] == 0) {
            foreach ($result["data"] as $item) {
                $list[] = $item;
            }
        }
        return $list;
    }

    public function analyzeWord($word)
    {
        $url =  $this->url."/"."v1/wordsegmentation?word=".urlencode(trim($word));
        $result = curl($url);
        $list = [];
        if ($result["error_code"] == 0) {
            foreach ($result["data"] as $item) {
                if (mb_strlen($item["token"],"utf-8") > 1) {
                    $list[] = $item["token"];
                }
            }
        }
        return $list;
    }

    public function searchWwwArticle($word,$page = 1,$count = 6)
    {
        $param = [
            'word' => trim($word),
            'page' => $page,
            'limit' => $count
        ];
        $param = http_build_query($param);
        $url =  $this->url."/v1/www/search?".$param;

        $result = curl($url);
        $info = [];
        if ($result["error_code"] == 0) {
            $info = $result["data"]['list'];

            $ids = array_column($info, 'id');
            if (count($ids) > 0) {
                //获取文章的辅助信息
                $where = [
                    'a.id' => ['in', $ids],
                ];
                $article_info = (new WwwArticleModel())->getArticle($where, 'a.id,a.pv');
                $article_info = array_column($article_info, null, 'id');
                if (count($info) > 0) {
                    foreach ($info as $k => $v) {
                        $info[$k]['pv'] = isset($article_info[$v['id']]['pv']) ? $article_info[$v['id']]['pv'] : 0;
                    }
                }
            }
        }
        return $info;
    }

    public function searchBaike($word,$page = 1,$count = 6)
    {
        $param = [
            'word' => trim($word),
            'page' => $page,
            'limit' => $count
        ];
        $param = http_build_query($param);
        $url =  $this->url."/"."v1/baike/search?".$param;

        $result = curl($url);
        $info = [];
        if ($result["error_code"] == 0) {
            $info = $result["data"]['list'];
        }
        return $info;
    }

    public function searchWenda($word,$page = 1,$count = 6)
    {
        $param = [
            'word' => trim($word),
            'page' => $page,
            'limit' => $count
        ];
        $param = http_build_query($param);
        $url =  $this->url."/"."v1/wenda/search?".$param;

        $result = curl($url);
        $info = [];
        if ($result["error_code"] == 0) {
            $info = $result["data"]['list'];
        }
        return $info;
    }

    /**
     * 装修快讯场景词搜索
     * @param $word 【搜索词】
     * @param $page
     * @param $count
     */
    public function getZxkxList($word,$page,$count)
    {
        $param = [
            'word' => trim($word),
            'page' => $page,
            'limit' => $count
        ];
        $param = urldecode(http_build_query($param));
        $url =  $this->url."/"."v1/scene/scenecontentsearch?".$param;

        $result = curl($url);
        $list = [];
        if ($result["error_code"] == 0) {
            foreach ($result["data"]["list"] as $key => $item) {
                if (!empty($item["img_path"])) {
                    $item["img_path"] = "https://".C("QINIU_DOMAIN")."/".$item["img_path"]."-w400.jpg";
                }

                $item["createtime"] = date("Y-m-d");
                switch ($item["logtype"]){
                    case "qz_www_article":
                        $type = "gonglue";
                        $link = "/gonglue/".$item["three_short_name"]."/".$item["id"].".html";
                        break;
                    case "qz_baike":
                        $type = "baike";
                        $link = "/baike/".$item["id"].".html";
                        break;
                    case "qz_ask":
                        $type = "wenda";
                        $link = "/wenda/x".$item["id"].".html";
                        break;
                }
                $item["link"] = $link;
                $item["logtype"] = $type;
                $list[$item["id"]] = $item;
            }
        }
        return array("list" => $list,"page" => $result["data"]["page"]);
    }

    public function getSenceContentList($sence,$word,$page,$count)
    {
        $param = [
            'sence' => $sence,
            'word' => trim($word),
            'page' => $page,
            'limit' => $count
        ];
        $param = (http_build_query($param));
        $url =  $this->url."/"."v1/scene/scenegraphicsearch?".$param;

        $result = curl($url);
        $list = [];
        if ($result["error_code"] == 0) {
            foreach ($result["data"]["list"] as $key => $item) {
                if (!empty($item["img_path"])) {
                    $item["img_path"] = "https://".C("QINIU_DOMAIN")."/".$item["img_path"]."-w400.jpg";
                }

                $item["createtime"] = date("Y-m-d");
                switch ($item["logtype"]){
                    case "qz_www_article":
                        $type = "gonglue";
                        $link = "/gonglue/".$item["three_short_name"]."/".$item["id"].".html";
                        break;
                    case "qz_baike":
                        $type = "baike";
                        $link = "/baike/".$item["id"].".html";
                        break;
                    case "qz_ask":
                        $type = "wenda";
                        $link = "/wenda/x".$item["id"].".html";
                        break;
                }
                $item["link"] = $link;
                $item["logtype"] = $type;
                $list[$item["id"]] = $item;
            }
        }
        return array("list" => $list,"page" => $result["data"]["page"]);
    }

    public function getSenceArticleList($sence,$word,$page,$count){
        $param = [
            'sence' => $sence,
            'word' => trim($word),
            'page' => $page,
            'limit' => $count
        ];
        $param = (http_build_query($param));
        $url =  $this->url."/"."v1/scene/scenearticleearch?".$param;

        $result = curl($url);
        $list = [];
        if ($result["error_code"] == 0) {
            foreach ($result["data"]["list"] as $key => $item) {
                if (!empty($item["img_path"])) {
                    $item["img_path"] = "https://".C("QINIU_DOMAIN")."/".$item["img_path"]."-w400.jpg";
                }

                $item["createtime"] = date("Y-m-d");
                $item["logtype"] = "gonglue";
                $item["link"] = "/gonglue/".$item["three_short_name"]."/".$item["id"].".html";
                $list[$item["id"]] = $item;
            }
        }
        return array("list" => $list,"page" => $result["data"]["page"]);
    }

    public function getContentSubList($moudle,$word,$page,$count)
    {
        $param = [
            'module' => $moudle,
            'word' => trim($word),
            'page' => $page,
            'limit' => $count
        ];
        $param = urldecode(http_build_query($param));
        $url =  $this->url."/"."v1/scene/scenecontentsubsearch?".$param;
        $result = curl($url);
        $list = [];
        if ($result["error_code"] == 0) {
            foreach ($result["data"]["list"] as $key => $item) {
                if (!empty($item["img_path"])) {
                    $item["img_path"] = "https://".C("QINIU_DOMAIN")."/".$item["img_path"]."-w400.jpg";
                }

                $item["createtime"] = date("Y-m-d");
                switch ($item["logtype"]){
                    case "qz_www_article":
                        $type = "gonglue";
                        $link = "/gonglue/".$item["three_short_name"]."/".$item["id"].".html";
                        break;
                }
                $item["link"] = $link;
                $item["logtype"] = $type;
                $list[$item["id"]] = $item;
            }
        }
        return array("list" => $list,"page" => $result["data"]["page"]);
    }

    public function getWwwArticleList($word,$page,$count)
    {
        $param = [
            'word' => trim($word),
            'page' => $page,
            'limit' => $count
        ];
        $param = urldecode(http_build_query($param));
        $url =  $this->url."/v1/www/search?".$param;

        $result = curl($url);

        $list = [];
        if ($result["error_code"] == 0) {
            foreach ($result["data"]["list"] as $key => $item) {
                if (!empty($item["img_path"])) {
                    $item["img_path"] = "https://".C("QINIU_DOMAIN")."/".$item["img_path"]."-w400.jpg";
                }

                $item["createtime"] = date("Y-m-d");
                $item["logtype"] = "gonglue";
                $item["link"] = "/gonglue/".$item["three_short_name"]."/".$item["id"].".html";
                $list[$item["id"]] = $item;
            }
        }
        return array("list" => $list,"page" => $result["data"]["page"]);
    }


    public function getWendaList($word,$page,$count)
    {
        $param = [
            'word' => trim($word),
            'page' => $page,
            'limit' => $count
        ];
        $param = urldecode(http_build_query($param));
        $url =  $this->url."/"."v1/wenda/search?".$param;

        $result = curl($url);

        $list = [];
        if ($result["error_code"] == 0) {
            foreach ($result["data"]["list"] as $key => $item) {
                if (!empty($item["img_path"])) {
                    $item["img_path"] = "https://".C("QINIU_DOMAIN")."/".$item["img_path"]."-w400.jpg";
                }

                $item["createtime"] = date("Y-m-d");
                switch ($item["logtype"]){
                    case "qz_ask":
                        $type = "wenda";
                        $link = "/wenda/x".$item["id"].".html";
                        break;
                }
                $item["link"] = $link;
                $item["logtype"] = $type;
                $list[$item["id"]] = $item;
            }
        }
        return array("list" => $list,"page" => $result["data"]["page"]);
    }

    public function getBaikeList($word,$page,$count)
    {
        $param = [
            'word' => trim($word),
            'page' => $page,
            'limit' => $count
        ];
        $param = urldecode(http_build_query($param));
        $url =  $this->url."/"."v1/baike/search?".$param;

        $result = curl($url);
        $list = [];
        if ($result["error_code"] == 0) {
            foreach ($result["data"]["list"] as $key => $item) {
                if (!empty($item["img_path"])) {
                    $item["img_path"] = "https://".C("QINIU_DOMAIN")."/".$item["img_path"]."-w400.jpg";
                }

                $item["createtime"] = date("Y-m-d");
                switch ($item["logtype"]){
                    case "qz_baike":
                        $type = "baike";
                        $link = "/baike/".$item["id"].".html";
                        break;
                }
                $item["link"] = $link;
                $item["logtype"] = $type;
                $list[$item["id"]] = $item;
            }
        }
        return array("list" => $list,"page" => $result["data"]["page"]);
    }

    // 搜索美图
    public function searchTuHome(array $param, $page = 1, $limit = 6)
    {
        $params = array(
            "scene_word" => !empty($param["scene_word"]) ? $param["scene_word"] : "",
            "search_word" => !empty($param["search_word"]) ? $param["search_word"] : "",
            "cate_field" => !empty($param["cate_field"]) ? $param["cate_field"] : "",
            "cate_id" => !empty($param["cate_id"]) ? $param["cate_id"] : "",
            "limit" => $limit,
            "page" => $page
        );

        $urlparam = http_build_query($params);
        $url = $this->url . "/v1/meitu/home?".$urlparam;

        $result = curl($url);
        if (isset($result["error_code"]) && $result["error_code"] == 0) {
            $info = $result["data"];
        } else {
            $info = $this->getDefaultData($page, $limit);
        }
        return $info;
    }

    // 搜索美图
    public function searchTuPub(array $param, $page = 1, $limit = 6)
    {
        $params = array(
            "scene_word" => !empty($param["scene_word"]) ? $param["scene_word"] : "",
            "search_word" => !empty($param["search_word"]) ? $param["search_word"] : "",
            "cate_id" => !empty($param["cate_id"]) ? $param["cate_id"] : "",
            "limit" => $limit,
            "page" => $page
        );

        $urlparam = http_build_query($params);
        $url = $this->url . "/v1/meitu/pub?".$urlparam;

        $result = curl($url);
        if (isset($result["error_code"]) && $result["error_code"] == 0) {
            $info = $result["data"];
        } else {
            $info = $this->getDefaultData($page, $limit);
        }
        return $info;
    }

    // 搜索案例图
    public function searchCases(array $param, $page = 1, $limit = 6)
    {
        $params = array(
            "scene_word" => !empty($param["scene_word"]) ? $param["scene_word"] : "",
            "search_word" => !empty($param["search_word"]) ? $param["search_word"] : "",
            "classid" => !empty($param["type"]) ? $param["type"] : "",
            "huxing" => !empty($param["hx"]) ? $param["hx"] : "",
            "leixing" => !empty($param["lx"]) ? $param["lx"] : "",
            "fengge" => !empty($param["fg"]) ? $param["fg"] : "",
            "zaojia" => !empty($param["jg"]) ? $param["jg"] : "",
            "mianji_min" => !empty($param["mianji_min"]) ? $param["mianji_min"] : "",
            "mianji_max" => !empty($param["mianji_max"]) ? $param["mianji_max"] : "",
            "cs" => !empty($param["cs"]) ? $param["cs"] : "",
            "limit" => $limit,
            "page" => $page
        );

        $urlparam = http_build_query($params);
        $url = $this->url . "/v1/meitu/cases?".$urlparam;

        $result = curl($url);

        if (isset($result["error_code"]) && $result["error_code"] == 0) {
            $info = $result["data"];
        } else {
            $info = $this->getDefaultData($page, $limit);
        }
        return $info;
    }


    // 默认返回数据
    private function getDefaultData($page, $limit){
        return [
            "list" => [],
            "page" => [
                "page_current" => $page,
                "page_size" => $limit,
                "page_total_number" => 0,
                "total_number" => 0
            ]
        ];
    }
}