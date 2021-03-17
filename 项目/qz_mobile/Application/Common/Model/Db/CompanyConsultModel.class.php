<?php

namespace Common\Model\Db;
use Think\Model;


class  CompanyConsultModel extends Model
{
    public function addInfo($data)
    {
        return M("company_consult")->add($data);
    }
}