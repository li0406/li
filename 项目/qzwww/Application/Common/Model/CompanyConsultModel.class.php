<?php
/**
 * 公司入驻咨询信息表
 * Created by PhpStorm.
 * User: mcj
 * Date: 2018/6/22
 * Time: 16:07
 */
namespace Common\Model;
use Think\Model;
class CompanyConsultModel extends Model{

    public function insertConsult($data){
        return M("company_consult")->add($data);
    }

}