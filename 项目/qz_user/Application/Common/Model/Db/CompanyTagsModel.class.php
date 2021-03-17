<?php

namespace Common\Model\Db;
Use Think\Model;

class CompanyTagsModel extends Model
{
    public function add($data){
        return M("company_tags")->addAll($data);
    }

    public function del($where){
        return M("company_tags")->where($where)->delete();
    }

    public function select($where){
        return M("company_tags")->where($where)->select();
    }
    public function selectTags(){
        return M("company_relation_tag")->select();
    }
}
