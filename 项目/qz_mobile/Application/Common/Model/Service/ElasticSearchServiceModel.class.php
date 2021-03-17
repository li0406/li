<?php
namespace Common\Model\Service;
use Common\Model\Db\WwwArticleModel;

/**
 *
 */
class ElasticSearchServiceModel
{
    private $domain,$port,$protocol,$url;

    protected $scheme_host;

    public function __construct() {
        $config = C("SERVICE.ELASTIC");
        $this->domain = $config["DOMAIN"];
        $this->port = $config["PORT"];
        $this->protocol = $config["PROTOCOL"];

        $this->url = $config['PROTOCOL']."://".$config['DOMAIN'].":".$config['PORT'];

        $this->scheme_host = getSchemeAndHost();
    }

    public function getThemticLabels($title, $type, $limit = 5) {
        $title = preg_replace('/\s/', "", $title);
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

    public function getThematicSearch($name,$pageIndex,$pageCount)
    {
        $url = $this->url."/v1/themtic/thematicsearch?word=".$name."&page=".$pageIndex."&count=".$pageCount;
        $result = curl($url);
        $list = [];
        if ($result["error_code"] == 0) {
            $list = $result["data"];
        }
        return $list;
    }

    public function getThematicTuSearch($word, $page = 1, $limit = 10)
    {
        $param = array(
            "word" => trim($word),
            "page" => $page,
            "limit" => $limit
        );

        $urlparam = http_build_query($param);
        $url = $this->url . "/v1/themtic/thematic_tu_search?" . $urlparam;

        $result = curl($url);

        $list = [];
        if ($result["error_code"] == 0) {
            $list = $result["data"];
        }
        return $list;
    }

    public function analyzeWord($word)
    {
        $url = $this->url."/v1/wordsegmentation?word=".trim($word);
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

    /**
     * 综合搜索
     * @param $keyword
     * @return array
     */
    public function getColligateSearch($keyword,$limit = 3)
    {
        $url = $this->url."/v1/colligate/search?word=".trim($keyword)."&limit=".$limit;
        $result = curl($url);

        $list = [];
        if ($result["error_code"] == 0) {
            foreach ($result["data"] as $item) {
                if (empty($item["img_path"])) {
                    $item["img_path"] = $this->scheme_host["scheme_full"].C("QINIU_DOMAIN")."/custom/20191012/FlDVSysFCowb1M9NDe6eUvH2ajZ2.png";
                } else {
                    $item["img_path"] = $this->scheme_host["scheme_full"].C("QINIU_DOMAIN")."/".$item["img_path"]."-w400.jpg";
                }

                $item["createtime"] = date("Y-m-d H:i:s");
                switch ($item["logtype"]){
                    case "qz_www_article":
                        $type = "gonglue";
                        break;
                    case "qz_baike":
                        $type = "baike";
                        break;
                    case "qz_ask":
                        $type = "wenda";
                        break;
                    case "qz_little_article":
                        $type = "little";
                        break;
                }
                $list[$type][] = $item;
            }
        }
        return array("list" => $list);
    }

    /**
     * gonglue搜索
     */
    public function getModuleSearch($module,$keyword,$pageIndex,$pageSize)
    {
        $url = $this->url."/v1/".$module."/search?word=".trim($keyword)."&page=".$pageIndex."&limit=".$pageSize;

        $result = curl($url);

        $list = [];
        if ($result["error_code"] == 0) {
            foreach ($result["data"]["list"] as $item) {
                if (empty($item["img_path"])) {
                    $item["img_path"] = $this->scheme_host["scheme_full"].C("QINIU_DOMAIN")."/custom/20191012/FlDVSysFCowb1M9NDe6eUvH2ajZ2.png";
                } else {
                    $item["img_path"] = $this->scheme_host["scheme_full"].C("QINIU_DOMAIN")."/".$item["img_path"]."-w400.jpg";
                }

                $item["createtime"] = date("Y-m-d H:i:s");
                switch ($item["logtype"]){
                    case "qz_www_article":
                        $type = "gonglue";
                        break;
                    case "qz_baike":
                        $type = "baike";
                        break;
                    case "qz_ask":
                        $type = "wenda";
                        break;
                    case "qz_little_article":
                        $type = "little";
                        break;
                }
                $list[$type][] = $item;
            }
        }
        return array("list" => $list,"page" => $result["data"]["page"]);
    }

    public function searchWwwArticle($word,$page = 1,$count = 6)
    {
        $param = [
            'word' => trim($word),
            'page' => $page,
            'limit' => $count
        ];
        $param = http_build_query($param);
        $url = $this->url."/v1/www/search?".$param;
        $result = curl($url);
        $info = [];
        if ($result["error_code"] == 0) {
            $info = $result["data"]['list'];
            $ids = array_column($info,'id');
            if(count($ids) > 0){
                //获取文章的辅助信息
                $where = [
                    'a.id'=>['in',$ids],
                ];
                $article_info = (new WwwArticleModel())->getArticle($where,'a.id,a.pv');
                $article_info = array_column($article_info,null,'id');
                if(count($info) > 0){
                    foreach ($info as $k=>$v){
                        $info[$k]['pv'] = isset($article_info[$v['id']]['pv'])?$article_info[$v['id']]['pv']:0;
                    }
                }
            }
        }
        return $info;
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
}