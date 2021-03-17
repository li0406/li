<?php
/**
 * 装修公司详细信息 qz_user_company
 */
namespace Common\Model;
use Think\Model;
class UsercompanyModel extends Model{
    protected $tableName ="user_company";
    /**
     * 添加装修公司详细信息
     */
    public function AddCompanyDetails($data){
        return M("user_company")->add($data);
    }
}