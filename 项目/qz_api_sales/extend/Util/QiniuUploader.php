<?php
namespace Util;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Exception as tException;

require_once(dirname(dirname(__DIR__))."/extend/Qiniu/autoload.php");
/**
 * 七牛上传工具类
 */
class QiniuUploader
{
    private $accessKey;
    private $secretKey;
    private $bucket;
    private $expires = 3600;

    public function __construct($accessKey = "",$secretKey = "",$bucket = "",$expires = "")
    {
    	if(empty($accessKey)||empty($secretKey)||empty($bucket)){
			throw new tException('七牛云配置未设置好');
		}
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->bucket = $bucket;
    }

    public function getToken($policy = null)
    {

        $auth = new Auth($this->accessKey, $this->secretKey);
        $token = $auth->uploadToken($this->bucket,null,$this->expires,$policy,false);
        return $token;
    }

    public function uploader($file_path = "",$file_name = "",$policy = null,$prefix = "sales")
    {
        if (empty($policy)) {
            $policy = array(
                'returnBody' => '{"key":$(key)}',
                'saveKey' => $prefix . '/$(year)$(mon)$(day)/$(etag)'
            );
        }

        $token = $this->getToken($policy);
        $upload = new UploadManager();
        list($ret, $err) = $upload->putFile($token, $file_name, $file_path);
        if($err == null){
            return $ret;
        }
        return null;
    }

}