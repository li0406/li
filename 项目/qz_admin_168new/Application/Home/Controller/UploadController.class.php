<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class UploadController extends HomeBaseController{
    public function index(){
        die();
    }

    /**
     * 上传图片
     * @return [type] [description]
     */
    public function uploadImg(){
        if($_POST){
            if (count($_FILES) == 0) {
                $this->ajaxReturn(
                    array(
                        'error_code'=> 0,
                        'error_msg'=>'未获取到图片数据',
                        'status'=>0
                    )
                );
                die();
            }

            $setting = C('UPLOAD_IMG_QINIU');
            $setting["saveName"] =  array('uniqid','');

            $file = $_FILES[array_keys($_FILES)[0]];

            if(!empty($_POST['chars'])){
                $setting["saveName"] = $this->getFilePinYinName($file['name']);
            }
            $setting["savePath"] = "";
            $info = '';
            if(!empty($_POST['prefix'])){
                $setting["savePath"] = $_POST['prefix'].'/';
            }
            $setting["subName"] = array('date', 'Ymd');
            $setting["driverConfig"]["domain"] = OP("QINIU_DOMAIN");
            $setting["driverConfig"]["bucket"] = OP('QINIU_BUCKET');
            $setting["driverConfig"]["secretKey"] = OP("QINIU_CK");
            $setting["driverConfig"]["accessKey"] =  OP("QINIU_AK");
            $Upload = new \Think\Upload($setting);
            $data = $Upload->upload($_FILES);
            $imageData = $data[array_keys($_FILES)[0]];
            if($imageData['url']) {
               if(strpos($imageData['url'],'//') === 0) {
                   $imageData['url']  = 'https:' .  $imageData['url'];
               }
            }
            $imageData['image_size'] = @getimagesize($imageData['url'])?:[];
            if($data !== false){
                $this->ajaxReturn(array('data'=>$imageData,'info'=>$info,'status'=>1));
            }
        }
        $this->ajaxReturn(
            array(
                    'data'=>'',
                    'info'=>'上传失败，请重新上传，如多次失败请联系技术部门！',
                    'status'=>0
                )
        );
        die();
    }

    /**
     * 上传3D效果图
     */
    public function uploadthreedimensional()
    {
        if (empty($_POST)) {
            $this->ajaxReturn(array('status'=>0, 'info' => '非法请求'));
        }
        if (count($_FILES) == 0) {
            header("Content-type:text/html;charset=utf-8");
            header("HTTP/1.1 405 Picture not uploaded");
            die();
        }

        if (empty($_POST['prefix'])) {
            $this->ajaxReturn(array('status'=>0, 'info' => '非法请求'));
        }

        //修改文件名
        $endname = substr(strrchr($_FILES[array_keys($_FILES)[0]]['name'], '.'), 1);
        $newfilename = $this->getnewrand(10);
        $_FILES[array_keys($_FILES)[0]]['name'] = $newfilename.'.'.$endname;

        $file = $_FILES[array_keys($_FILES)[0]];
        $imginfo = getimagesize($file['tmp_name']);
        if($imginfo[0] / $imginfo[1] == 2){
        }else{
            $this->ajaxReturn(array('status'=>0, 'info' => '请上传长宽比为2：1的图片'));
        }
        import("Library.Org.Util.Krpano");

        //操作系统环境
        $krpano = new \Org\Util\Krpano('linux', $file["tmp_name"]);
        $folderPath = $krpano->exec();

        if (!empty($folderPath)) {
            $path = date('YmdHis') . rand(100000, 999999);
            $prefix = "threedimensional/".$path;
            $result = $krpano->upload_qiniu($folderPath, $prefix);
        }

        if(count($result) > 0){
            $this->ajaxReturn(array('data'=>$result,"path"=>$path,'status'=>1));
        }
        $this->ajaxReturn(array('status'=>0, 'info' => '上传失败'));
    }


    /**
     * 上传单个文件
     * @param  [type] $file [文件源]
     * @return [type]       [description]
     */
    public function upSingleFile()
    {
        $file = $_FILES[array_keys($_FILES)[0]];
        if (!empty($file['name'])) {
            $path = trim(I("post.path")) == ""?null:trim(I("post.path"));
            $fileExt = I("post.fileExt");
            $fromPath = $file["tmp_name"];

            import('Library.Org.Qiniu.io', '', '.php');
            import('Library.Org.Qiniu.rs', '', '.php');
            $bucket = OP('QINIU_BUCKET');
            $accessKey = OP('QINIU_AK');
            $secretKey = OP('QINIU_CK');
            Qiniu_SetKeys($accessKey, $secretKey);
            $putPolicy = new \Qiniu_RS_PutPolicy($bucket);

            if (empty($path)) {
                $putPolicy->SaveKey = 'custom/$(year)$(mon)$(day)/$(etag)';
            }

            if ($fileExt) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $putPolicy->SaveKey .= ".".$ext;
                if (!empty($path)) {
                    $path .= ".".$ext;
                }
            }

            $upToken = $putPolicy->Token(null);
            $putExtra = new \Qiniu_PutExtra();
            $putExtra->Crc32 = 1;
            list($ret, $err) = Qiniu_PutFile($upToken, $path, $fromPath, $putExtra);

            if($err == null){
                $result = array(
                        "hash"=>$ret["hash"],
                        "key"=> $ret["key"],
                        "filename" => $file['name']
                                );
                $this->ajaxReturn(array('status'=>1, 'data' => $result));
            }
            $this->ajaxReturn(array('status'=>0, 'info' => '上传失败，请清空文件后再上传！'));
        }
        $this->ajaxReturn(array('status'=>0, 'info' => '文件未上传或清空文件后缀再上传！'));
    }

    /**
     * [getFilePinYinName 获取汉子拼音字符串，备注，有的获取不到]
     * @param  [type] $string [description]
     * @return [type]         [description]
     */
    private function getFilePinYinName($string){
        $name = substr($string,0,strrpos($string, '.'));
        import("Library.Org.PinYin.PinYin");
        $PinYin = new \PinYin();
        $name = $PinYin->getAllPY($name).'-'.substr(time(),-6);
        //替换掉文件名中间的空格
        $pattern = array(' ','.');
        $name = str_replace($pattern, '', $name);
        return $name;
    }


    /**
     * [getImageColor 获取图片主要颜色]
     * @param  [type] $file [图片路径]
     * @return [type]       [description]
     */
    private function getImageColor($file){
        $img=imagecreatefromjpeg($file);
        $rgb = imagecolorat($img,20,20);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        $hex = "#";
        $hex .= str_pad(dechex($r), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($g), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($b), 2, "0", STR_PAD_LEFT);
        return $hex;
    }

    //生成唯一字符串
    public function getnewrand($len)
    {
        $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $string=time();
        for(;$len>=1;$len--)
        {
            $position=rand()%strlen($chars);
            $position2=rand()%strlen($string);
            $string=substr_replace($string,substr($chars,$position,1),$position2,0);
        }
        return $string;
    }

}
