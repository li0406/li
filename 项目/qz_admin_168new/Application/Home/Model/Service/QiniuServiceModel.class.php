<?php

namespace Home\Model\Service;

use Think\Model;
use Qiniu;

class QiniuServiceModel
{

    /**
     * 删除七牛图片
     * @param  string $key 路径
     * @return bool
     */
    public function deleteQiniuFile($key = ''){
        import("Library.Org.Qiniu.rs",'','.php');
        $bucket =OP('QINIU_BUCKET');
        Qiniu_SetKeys(OP('QINIU_AK'),OP('QINIU_CK'));
        $client = new \Qiniu_MacHttpClient(null);
        $err = Qiniu_RS_Delete($client, $bucket, $key);

        if ($err !== null) {
            return false;
        } else {
           return true;
        }
    }

    public function refreshCDN($token,$path)
    {
        $accessKey = OP('QINIU_AK');
        $secretKey = OP('QINIU_CK');

        $auth = new Qiniu\Auth($accessKey,$secretKey);
        $urls = [$path];
        $cdnManager = new Qiniu\Cdn\CdnManager($auth);
        $dirs = [];
        list($refreshResult, $refreshErr) = $cdnManager->refreshUrlsAndDirs($urls, $dirs);

        if ($refreshErr == null) {
            return  true;
        }
        return false;
    }
}
