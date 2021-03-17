<?php
/**
 * Created by PhpStorm.
 * User: qzjsb
 * Date: 2019/5/21
 * Time: 17:10
 */

namespace Common\Model;


use Think\Model;

class CompanyImgModel extends Model
{
    protected $tableName = 'company_img';

    public function getHonerById($id){
        return M('company_img')->field("id,img as img_path,img_host")
            ->where(array('userid'=>$id))
            ->order('id desc')
            ->select();
    }
}