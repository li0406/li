<?php

namespace app\index\controller;

use Exception;
use think\Request;
use think\Controller;
use think\facade\Config;

use app\common\model\logic\OptionsLogic;
use Util\QiniuUploader;

class Upload extends Controller {

    /**
     * 图片上传允许的mime type
     * @var array
     */
    protected $allowImageMimeType = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/bmp',
    ];

    /**
     * 上传图片到七牛云
     * @return [type] [description]
     */
    public function uploadImg(Request $request){
        $optionsLogic = new OptionsLogic();
        $options = $optionsLogic->getQiniuOptions();

        $accessKey = $options["QINIU_AK"];
        $secretKey = $options["QINIU_CK"];
        $bucket = $options["QINIU_BUCKET"];

        try {
            $file = $request->file('file');
            $prefix = $request->param("prefix");
            if (empty($file) || empty($prefix)) {
                throw new Exception('', 4000002);
            }

            $fileInfo = $file->getInfo();
            if (!in_array($fileInfo['type'], $this->allowImageMimeType)) {
                throw new Exception("非法的文件", 1000002);
            }
            
            // 要上传图片的本地路径
            // if ($fileInfo['size'] > 1024 * 1024 * 0.6) {
            //     throw new Exception('上传图片大小不能超过600k！', 1000002);
            // }

            // 上传到七牛后保存的文件名
            $uploader = new QiniuUploader($accessKey, $secretKey, $bucket);
            $result = $uploader->uploader($fileInfo["tmp_name"], null, null, $prefix);
            if (empty($result)) {
                throw new Exception('', 1000002);
            }

            $code = 0;
            $msg = "";
            $data = [
                "img_path" => $result["key"],
                "img_full" => config("QINIU_HOST") . "/" . $result["key"]
            ];
        } catch (Exception $e) {
            $code = $e->getCode() ? : 1000001;
            $msg = $e->getMessage();
            $data = [];
        }

        $response = sys_response($code, $msg, $data);
        return json($response);
    }

}