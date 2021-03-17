<?php


namespace app\index\validate;


use think\Validate;

class TemplateValidate extends Validate
{
    /**
     * @des:校验上传的城市预期会员数文件
     * @param $file
     */
   public static function checkFile($file)
   {
       if (empty($file)) {
           return '参数缺失 file';
       }
       if (pathinfo($file['name'])['extension'] != 'xlsx') {
           return '上传文件类型不支持xlsx外的文件';
       }
       if ($file['size'] > 1024 * 1024 * 2) {
           return '上传文件不能大于2M';
       }
       return true;
   }

}