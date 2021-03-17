<?php
/**
 * 公司标签
 */

namespace Common\Model\Logic;

class CompanyTagsLogicModel
{
    public function saveCompanyTags($data,$company_id){
        //先删除原有数据
        D("Common/Db/CompanyTags")->del(['company_id'=>$company_id]);
        if($data['tag_ids']){
            $ids = explode(',', $data['tag_ids']);
            $save = [];
            foreach ($ids as $v) {
                if ($v) {
                    $save[] = ['company_id' => $company_id, 'tag' => $v];
                }
            }
            //再添加数据
            return D("Common/Db/CompanyTags")->addAll($save);
        }
    }

    public function getCompanyTags($company_id){
        if(!$company_id){
            return [];
        }
        return D("Common/Db/CompanyTags")->select(['company_id'=>$company_id]);
    }

    public function getTags(){
        return D("Home/Db/CompanyTags")->selectTags();
    }
}