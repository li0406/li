<?php
// +----------------------------------------------------------------------
// | WwwArticleLogicModel
// +----------------------------------------------------------------------

/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/9/9
 * Time: 10:27
 */

namespace Home\Model\Logic;


use Home\Model\Db\ContentCategoryParticipleModel;
use Home\Model\Db\WwwArticleModel;

class WwwArticleLogicModel
{
    protected $articleModel;

    public function __construct()
    {
        $this->articleModel = new WwwArticleModel();
    }

    /**
     * 验证文章数据
     *
     * @param $data
     * @return bool|string
     */
    public function articleValidate($data)
    {
        if (empty($data['title'])) {
            return '该填写标题';
        }
        if ($this->articleModel->findArticle(array_merge(!empty($data['id']) ? ['id' => ['NEQ', $data['id']]] : [], ['title' => $data['title'], 'state' => ['NEQ', -1]]))
        ) {
            return '已存在该标题的文章！';
        }
        if (empty($data['keywords'])) {
            return '该填写关键字';
        }
        if (mb_strlen((string)$data['keywords']) > 256) {
            return '关键字过长';
        }
        if (empty($data['face'])) {
            return '请上传封面图';
        }
        if (empty($data['content'])) {
            return '请填写文章详情';
        }
        if (empty($data['item_name'])) {
            return '请填写文章词条名';
        }
        if (mb_strlen((string)$data['item_name']) > 15) {
            return '文章词条名不超过15个字符';
        }
        if ($this->articleModel->findArticle(array_merge(!empty($data['id']) ? ['id' => ['NEQ', $data['id']]] : [], ['item_name' => $data['item_name'], 'state' => ['NEQ', -1]]))
        ) {
            return '已有相同词条数据，请重新输入';
        }
        return true;
    }

    /**
     * 验证文章数据
     *
     * @param $data
     * @return bool|string
     */
    public function partArticleValidate($data)
    {
        if (empty($data['title'])) {
            return '该填写标题';
        }
        if ($this->articleModel->findArticle(array_merge(!empty($data['id']) ? ['id' => ['NEQ', $data['id']]] : [], ['title' => $data['title'], 'state' => ['NEQ', -1]]))
        ) {
            return '已存在该标题的文章！';
        }
        if (empty($data['keywords'])) {
            return '该填写关键字';
        }
        if (mb_strlen((string)$data['keywords']) > 256) {
            return '关键字过长';
        }
        if (empty($data['face'])) {
            return '请上传封面图';
        }
        if (empty($data['content'])) {
            return '请填写文章详情';
        }
        if (empty($data['item_name'])) {
            return '请填写文章词条名';
        }
        if (mb_strlen((string)$data['item_name']) > 15) {
            return '文章词条名不超过15个字符';
        }
        if ($this->articleModel->findArticle(array_merge(!empty($data['id']) ? ['id' => ['NEQ', $data['id']]] : [], ['item_name' => $data['item_name'], 'state' => ['NEQ', -1]]))
        ) {
            return '已有相同词条数据，请重新输入';
        }
        return true;
    }


    public function setClassParticiple($category_id, $words)
    {
        $all = [];
        foreach ($words as $word) {
            $all[] = [
                "category_id" => $category_id,
                "word" => $word,
                "module" => 1,
                "created_at" => time()
            ];
        }
        $model = new ContentCategoryParticipleModel();
        return $model->addAllInfo($all);
    }

    public function delClassParticiple($category_id,$module)
    {
        $model = new ContentCategoryParticipleModel();
        return $model->delInfo($category_id,$module);
    }

    /**
     * 推送专业问答
     * @param $url
     */
    public function pushWenda($param)
    {
        $param['imgs'] = explode(",",$param["imgs"]);
        foreach ($param['imgs'] as &$v) {
            $v = str_replace('-s3.jpg', '-s5.jpg', $v);
        }

        $data = [
            "@context" => "https://ziyuan.baidu.com/contexts/cambrian.jsonld",
            "@id" => $param["url"],
            "appid" => "1575859058891466",
            "title" => $param["title"],
            "images"=>[
                "https:". $param["imgs"][0],
            ],
            "description" => $param["subtitle"],
            "pubDate" => date("Y-m-d\TH:i:s",$param["addtime"]),
            "upDate" => date("Y-m-d\TH:i:s",$param["addtime"]),
            "data" => [
                "WebPage" => [
                    "headline" =>  $param["title"],
                    "fromSrc" => "齐装网",
                    "domain" => "房产家居",
                    "category" => ["问答"],
                    "isDeleted" => 0,
                ],
                "Question" => [
                    [
                        "acceptedAnswer" =>  $param["short_introduction"],
                    ]
                ],
                "ImageObject" => [
                    [
                        "contentUrl" =>  "https:". $param["imgs"][0],
                        "scale" => "5:2"
                    ]
                ],
                "Author" => [
                    [
                        "name" => "齐装网",
                        "jobTitle" => [
                            "有房要装，就上齐装"
                        ],
                        "headPortrait" => "https://".C("QINIU_DOMAIN")."/custom/20200909/FoFbLWKwMWfcHLK65U40wYlsW2jW.jpg"
                    ]
                ]
            ]
        ];

        $api = 'http://ziyuan-data.baidu.com/qa?site=https://m.qizuang.com&token=o7MDQHNT7Mf3CWP2&domain=房产家居';
        $headers = [
            'Content-Type: text/plain',
        ];

        $result = curl($api,json_encode($data,320),$headers);
        //添加操作日志
        $log = array(
            'remark' => '专业问答推送',
            'logtype' => 'pushwenda',
            'action_id' => $param["id"],
            'info' => json_encode($data),
            'data' => json_encode($result)
        );
        D('LogAdmin')->addLog($log);
        return $result;
    }

    /**
     * 推送科技问答
     * @param $url
     */
    public function pushKejiWenda($param)
    {
        $param['imgs'] = explode(",",$param["imgs"]);
        foreach ($param['imgs'] as &$v) {
            $v = str_replace('-s3.jpg', '-s5.jpg', $v);
        }

        $data = [
            "@context" => "https://ziyuan.baidu.com/contexts/cambrian.jsonld",
            "images"=>[
                "https:". $param["imgs"][0],
            ],
            "description" => $param["subtitle"],
            "pubDate" => date("Y-m-d\TH:i:s",$param["addtime"]),
            "upDate" => date("Y-m-d\TH:i:s",$param["addtime"]),
            "@id" => $param["url"],
            "appid" => "1575859058891466",
            "title" => $param["title"],
            "data" => [
                "WebPage" => [
                    "fromSrc" => "齐装网",
                    "category" => ["问答"],
                    "isDeleted" => 0,
                    "domain" => "电子科技",
                    "headline" =>  $param["title"]
                ],
                "Question" => [
                    [
                        "acceptedAnswer" =>  $param["short_introduction"],
                    ]
                ],
                "ImageObject" => [
                    [
                        "contentUrl" =>  "https:". $param["imgs"][0],
                        "scale" => "5:2"
                    ]
                ],
                "Author" => [
                    [
                        "name" => "齐装网",
                        "jobTitle" => [
                            "有房要装，就上齐装"
                        ],
                        "headPortrait" => "https://".C("QINIU_DOMAIN")."/custom/20200909/FoFbLWKwMWfcHLK65U40wYlsW2jW.jpg"
                    ]
                ],
                "TechInfo" => [
                    "systemVersion" => $param["system_version"] ?: "NULL",
                    "appVersion" => $param["app_version"] ?: "NULL",
                    "brandModel" => $param["brand_model"] ?: "NULL"
                ]
            ]
        ];

        $api = 'http://ziyuan-data.baidu.com/qa?site=https://m.qizuang.com&token=oUF1qxHVNpg04aKG&domain=电子科技';
        $headers = [
            'Content-Type: text/plain',
        ];

        $result = curl($api,json_encode($data,320),$headers);

        //添加操作日志
        $log = array(
            'remark' => '科技问答推送',
            'logtype' => 'pushkejiwenda',
            'action_id' => $param["id"],
            'info' => json_encode($data),
            'data' => json_encode($result)
        );
        D('LogAdmin')->addLog($log);
        return $result;
    }
}