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
    public function uplogo(){
        if($_POST){
            $setting = C('UPLOAD_IMG_QINIU');
            $setting["saveName"] =  array('uniqid','');
            $setting["savePath"] = "adminlogo/";
            $setting["subName"] = array('date', 'Ymd');
            $setting["driverConfig"]["secretKey"] = OP('QINIU_CK');
            $setting["driverConfig"]["accessKey"] =  OP("QINIU_AK");
            $setting["driverConfig"]["domain"] = OP("QINIU_DOMAIN");
            $setting["driverConfig"]["bucket"] = OP('QINIU_BUCKET');
            $Upload = new \Think\Upload($setting);
            $info = $Upload->upload($_FILES);
            if($info !== false){
                $this->ajaxReturn(array("data"=>$info["Filedata"],"info"=>"","status"=>1));
            }
            $this->ajaxReturn(array("data"=>"","info"=>"上传头像失败,请检查上传文件！","status"=>0));
        }
        die();
    }
}