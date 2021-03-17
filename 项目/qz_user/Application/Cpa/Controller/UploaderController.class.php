<?php

namespace Cpa\Controller;

use Cpa\Common\Controller\JiajuBaseController;

class UploaderController extends JiajuBaseController
{
    /**
     * 上传图片
     * @return [type] [description]
     */
    public function upload()
    {
        //是否上传七牛
        if (OP('AUTO_QINIU')) {
            $prefix = "qizuang";
            if (!empty($_POST["prefix"])) {
                $prefix = I("post.prefix");
            }
            $file = $_FILES["file"];
            //设置偏移量
            $offset = empty($_POST["offset"]) ? 0 : $_POST["offset"];
            $width = I("post.width");
            $height = I("post.height");
            $file_info = getimagesize($file["tmp_name"]);
            if (!empty($width)) {
                $min_width = $width - $offset;
                $max_width = $width + $offset;
                if (!($file_info[0] >= $min_width && $file_info[0] <= $max_width)) {
                    echo urldecode(json_encode(array("status" => 0, "info" => urlencode("当前图片宽度不符,图片当前宽度为:" . $file_info[0] . " 像素"))));
                    exit;
                }

            }

            if (!empty($height)) {
                $max_height = $height + $offset;
                $min_height = $height - $offset;
                if (!($file_info[1] >= $min_height && $file_info[1] <= $max_height)) {
                    echo urldecode(json_encode(array("status" => 0, "info" => urlencode("当前图片高度不符,图片当前高度为:" . $file_info[1] . " 像素"))));
                    exit;
                }
            }
            $file_info = getimagesize($file['tmp_name']);

            import('Library.Org.Util.Qiniu');
            $qiniu = new \Qiniu();
            $bucket = OP('QINIU_BUCKET');
            $accessKey = OP('QINIU_AK');
            $secretKey = OP('QINIU_CK');
            $result = $qiniu->upload($bucket, $accessKey, $secretKey, $prefix, $file["tmp_name"]);

            if (!isset($result->Err)) {
                echo json_encode(array("status" => 1, "data" => $result));
            } else {
                echo urldecode(json_encode(array("status" => 0, "info" => urlencode("图片上传失败!"))));
            }
        } else {
            echo urldecode(json_encode(array("status" => 0, "info" => urlencode("图片上传失败!"))));
        }
        die();
    }

    /**
     * 上传头像
     * @return [type] [description]
     */
    public function uplogo()
    {
        if (IS_POST) {
            $file = $_FILES["__avatar1"];
            $prefix = "desLogo";
            import('Library.Org.Util.Qiniu');
            $qiniu = new \Qiniu();
            $bucket = OP('QINIU_BUCKET');
            $accessKey = OP('QINIU_AK');
            $secretKey = OP('QINIU_CK');
            $result = $qiniu->upload($bucket, $accessKey, $secretKey, $prefix, $file["tmp_name"]);
            if (!isset($result->Err)) {
                echo json_encode(array("success" => true, "data" => C('QINIU_DOMAIN') . '/' . $result['key']));
            } else {
                echo urldecode(json_encode(array("status" => 0, "info" => urlencode("图片上传失败!"))));
            }
            die();
        }
        echo json_encode(array("status" => 0, "info" => "无效的上传!"));
        die();
    }


}
