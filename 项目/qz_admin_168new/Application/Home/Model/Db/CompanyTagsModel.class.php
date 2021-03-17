<?php

namespace Home\Model\Db;
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

    public function getCompanyTags($company_ids){
        $map = array(
            "t.company_id" => array("IN", $company_ids)
        );
        
        return M("company_tags")->alias("t")->where($map)
            ->join("join qz_company_relation_tag as r on r.id = t.tag")
            ->field("t.company_id,t.tag as tag_id,r.tag as tag_name")
            ->select();
    }
}
