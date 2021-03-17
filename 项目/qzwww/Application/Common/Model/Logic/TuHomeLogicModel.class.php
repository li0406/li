<?php

namespace Common\Model\Logic;

use Common\Model\Db\TuHomeModel;

class TuHomeLogicModel
{
    protected $tuHomeModel;

    public function __construct()
    {
        $this->tuHomeModel = new TuHomeModel();
    }

    private function transformImage(string $url)
    {
        //有qizuang.com 和协议头
        if (strstr($url, 'http:') !== false || strstr($url, 'https:') !== false) {
            return $url;
        } else {
            $url = 'https://' . C('QINIU_DOMAIN') . '/' . $url;
            return $url;
        }
    }

    private function transformData(array $data)
    {
        $formatted = $data;
        $formatted['image_src'] = $this->transformImage($formatted['image_src']);
        return $formatted;
    }

    public function getPub($count)
    {
        $data = $this->tuHomeModel->getPub($count);
        $formatted = [];
        foreach ($data as $value) {
            $formatted[] = $this->transformData($value);
        }
        return $formatted;
    }

    /**
     * 获取家装数据
     * @param $count
     * @return mixed
     */
    public function getHome($count)
    {
        $data = $this->tuHomeModel->getHome($count);
        $formatted = [];
        foreach ($data as $value) {
            $formatted[] = $this->transformData($value);
        }
        return $formatted;
    }

    public function getRecommentList()
    {
        $list = $this->tuHomeModel->getRecommentList();
        foreach ($list as $key => $item) {
            $list[$key]["thumb"] = "https://".C("QINIU_DOMAIN")."/".$item["thumb"]."-w660.jpg";
        }
        return $list;
    }
}
