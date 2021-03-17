<?php

/**
 * 分站标识装修公司关联表
 */

namespace app\common\model\db;

use think\Db;
use think\Model;

class SubTagCompany extends Model {

    protected $autoWriteTimestamp = false;

    public function delCompanyTag($company_id)
    {
        $map[] = [
            'company_id', '=', $company_id
        ];
        return $this->where($map)->delete();
    }
    public function addCompanyTag($save)
    {
        return $this->saveAll($save);
    }
}