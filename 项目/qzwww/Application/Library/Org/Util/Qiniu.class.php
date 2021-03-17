<?php
class Qiniu {
    /**
     * 上传到七牛服务器
     * @param  [type] $prefix [图片名前缀]
     * @param  [type] $file   [文件路径]
     * @return [type]         [description]
     */
    public function upload($bucket,$accessKey,$secretKey,$prefix,$file){
        import("Library.Org.Qiniu.io",'','.php');
        import("Library.Org.Qiniu.rs",'','.php');
        Qiniu_SetKeys($accessKey, $secretKey);
        $putPolicy = new \Qiniu_RS_PutPolicy($bucket);

        $putPolicy->MimeLimit = 'image/jpeg;image/png;image/gif';
        $putPolicy->SaveKey = $prefix.'/$(year)$(mon)$(day)/$(etag)';
        $upToken = $putPolicy->Token(null);
        $putExtra = new \Qiniu_PutExtra();
        $putExtra->Crc32 = 1;
        list($ret, $err) = Qiniu_PutFile($upToken, null, $file, $putExtra);
        if($err == null){
            $result = array(
                    "hash"=>$ret["hash"],
                    "key"=> $ret["key"]
                            );
            return $result;
        }
        return $err;
    }

    // /**
    //  * 删除七牛服务器图片
    //  * @return [type] [description]
    //  */
    // public function delImg($bucket,$accessKey,$secretKey,$key){
    //     import('Library.Org.Qiniu.rs', '', '.php');
    //     Qiniu_SetKeys($accessKey,$secretKey);
    //     $client = new \Qiniu_MacHttpClient(null);
    //     $err = Qiniu_RS_Delete($client, $bucket, $key);
    //     if ($err !== null) {
    //         return false;
    //     } else {
    //        return true;
    //     }
    // }

    // /**
    //  * 下载七牛文件
    //  * @return [type] [description]
    //  */
    // public function downLoad($url){
    //     //获取七牛空间文件
    //     $domain = strstr($url,"//");

    //     if($domain){
    //         $domain = "http:".$url;
    //     }else{
    //         return false;
    //     }
    //     $filename = date("Ymdhis").".jpg";
    //     $domain = $domain."?attname=".$filename;
    //     $path = dirname(dirname(dirname(__FILE__)));
    //     $filename = "/upload/avatarcache/".date("Ymdhis").".jpg";
    //     $fp = fopen($path.$filename,"w+");
    //     $ch = curl_init();
    //     curl_setopt($ch,CURLOPT_URL,$domain);
    //     curl_setopt($ch,CURLOPT_FILE,$fp);
    //     curl_setopt($ch,CURLOPT_HEADER,0);
    //     curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
    //     curl_setopt($ch,CURLOPT_TIMEOUT,60);
    //     $hander =  curl_exec($ch);
    //     curl_close($ch);
    //     fclose($fp);
    //     return $filename;
    // }
}

?>