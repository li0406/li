<?php
/**
 * 公司标签
 */

namespace Home\Model\Logic;

class CompanyTagsLogicModel
{
    public function saveCompanyTags($data){
        //先删除原有数据
        D("Home/Db/CompanyTags")->del(['company_id'=>$data['company_id']]);
        if($data['tag_ids']){
            $ids = explode(',', $data['tag_ids']);
            $save = [];
            foreach ($ids as $v) {
                if ($v) {
                    $save[] = ['company_id' => $data['company_id'], 'tag' => $v];
                }
            }
            //再添加数据
            D("Home/Db/CompanyTags")->add($save);
        }
    }

    public function getCompanyTags($data){
        if(!$data['company_id']){
            return [];
        }
        return D("Home/Db/CompanyTags")->select(['company_id'=>$data['company_id']]);
    }

    public function getTags(){
        return D("Home/Db/CompanyTags")->selectTags();
    }
}