<?php
namespace User\Controller;
use User\Common\Controller\UserCenterBaseController;
class UploaderController extends UserCenterBaseController{
    /**
     * 上传图片
     * @return [type] [description]
     */
    public function upload(){
        //是否上传七牛
        if(OP('AUTO_QINIU')){
            $prefix ="qizuang";
            if(!empty($_POST["prefix"])){
                $prefix = I("post.prefix");
            }
            $file = $_FILES["file"];
            //设置偏移量
            $offset = empty($_POST["offset"])?0:$_POST["offset"];
            $width  = I("post.width");
            $height = I("post.height");
            $file_info = getimagesize($file["tmp_name"]);
            if(!empty($width)){
                $min_width = $width -$offset;
                $max_width = $width + $offset;
                if (!($file_info[0] >= $min_width && $file_info[0]<=$max_width))
                {
                    echo urldecode(json_encode(array("status"=>0,"info"=>urlencode("当前图片宽度不符,图片当前宽度为:".$file_info[0]." 像素"))));exit;
                }

            }

            if(!empty($height)){
                $max_height = $height +$offset;
                $min_height = $height -$offset;
                if (!($file_info[1] >= $min_height && $file_info[1]<=$max_height))
                {
                    echo urldecode(json_encode(array("status"=>0,"info"=>urlencode("当前图片高度不符,图片当前高度为:".$file_info[1]." 像素"))));exit;
                }
            }
            $file_info=getimagesize($file['tmp_name']);

            import('Library.Org.Util.Qiniu');
            $qiniu = new \Qiniu();
            $bucket = OP('QINIU_BUCKET');
            $accessKey = OP('QINIU_AK');
            $secretKey = OP('QINIU_CK');
            $result = $qiniu->upload($bucket,$accessKey,$secretKey,$prefix,$file["tmp_name"]);

            if(!isset($result->Err)){
                echo json_encode(array("status"=>1,"data"=>$result));
            }else{
                echo urldecode(json_encode(array("status"=>0,"info"=>urlencode("图片上传失败!"))));
            }
        }else{
            echo urldecode(json_encode(array("status"=>0,"info"=>urlencode("图片上传失败!"))));
        }
        die();
    }

    /**
     * 上传头像
     * @return [type] [description]
     */
    public function uplogo(){
        if($_POST){
            $file = $_FILES["__avatar1"];
            $prefix ="desLogo";
            import('Library.Org.Util.Qiniu');
            $qiniu = new \Qiniu();
            $bucket = OP('QINIU_BUCKET');
            $accessKey = OP('QINIU_AK');
            $secretKey = OP('QINIU_CK');
            $result = $qiniu->upload($bucket,$accessKey,$secretKey,$prefix,$file["tmp_name"]);
            if(!isset($result->Err)){
                echo json_encode(array("success"=>true,"data"=>$result));
            }else{
                echo urldecode(json_encode(array("status"=>0,"info"=>urlencode("图片上传失败!"))));
            }
            die();
        }
        echo json_encode(array("status"=>0,"info"=>"无效的上传!"));
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
        $_FILES[array_keys($_FILES)[0]]['name'] = $newfilename.'.jpg';

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
            if($result == 'folder is not exist !!'){
                $this->ajaxReturn(array('status'=>0, 'info' => '上传失败'));
            }
            $this->ajaxReturn(array('data'=>$result,"path"=>$path,'status'=>1));
        }
        $this->ajaxReturn(array('status'=>0, 'info' => '上传失败'));
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