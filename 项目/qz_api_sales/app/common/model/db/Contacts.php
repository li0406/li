<?php

namespace app\common\model\db;

use think\db\Where;
use think\Model;

class Contacts extends Model
{
    protected $table = 'qz_report_user';

    public function insertContacts($data)
    {
        return $this->insertGetId($data);
    }

    public function delContacts($company_id,$ids = [])
    {
        $where[] = ['company_id','=',$company_id];
        if(count($ids) > 0){
            $where[] = ['id','not in',$ids];
        }
        $where[] = ['company_id','=',$company_id];
        return $this->where($where)->delete();
    }
    public function saveContacts($data)
    {
        return $this->saveAll($data);
    }
}
