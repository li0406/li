<?php
namespace Common\Model\Db;

use Think\Model;

class CompanyImgModel extends Model
{
	protected $tableName =  'company_img';

    public function getCompanyImg($id){
        $where['userid'] = ['eq',$id];
        return $this->where($where)->find();
    }

}