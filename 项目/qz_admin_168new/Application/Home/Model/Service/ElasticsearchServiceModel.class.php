<?php

namespace Home\Model\Service;
/**
 *
 */
class ElasticsearchServiceModel
{
    private  $domain = "";
    private  $port = 0;
    private  $protocol = "";

    public function __construct()
    {
        $config = C("SERVICE.ELASTIC");
        $this->domain = $config["DOMAIN"];
        $this->protocol = $config["PROTOCOL"];
        $this->port = $config["PORT"];
    }


    public function analyzeWord($word)
    {
        $word = preg_replace('/\s/',"",$word);
        $url = sprintf("%s://%s:%s/v1/wordsegmentation?", $this->protocol, $this->domain, $this->port);
        $param = [
            "word" => trim($word)
        ];
        $url .= http_build_query($param);
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

    public function getContentLabel($title,$type,$limit = 5)
    {
        $title = preg_replace('/\s/',",",$title);
        $url = sprintf("%s://%s:%s/v1/themtic/getlabel?", $this->protocol, $this->domain, $this->port);
        $param = [
            "title" => trim($title),
            "type" => $type,
            "limit" => $limit
        ];
        $url .= http_build_query($param);
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