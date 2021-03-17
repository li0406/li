<?php

namespace Common\Model\Db;

use Think\Model;

class QccSearchModel extends Model
{
    protected $tableName = 'qcc_search';

    public function setSearchContent($save)
    {
        return $this->add($save);
    }
}
