<?php

namespace Home\Model\Logic;

use Home\Model\Db\OrderReviewNewCompanyModel;

class OrderReviewCompanyLogicModel
{
    public function setReviewCompany($data, $user)
    {
        $companyDb = new OrderReviewNewCompanyModel();
        //删除原有数据
        $companyDb->delReviewCompanyInfo($data['company_id']);
        $save = [
            'company_id' => $data['company_id'],
            'review_info' => $data['info'],
            'review_uid' => $user['id'],
            'created_at' => time(),
            'updated_at' => time(),
        ];
        return $companyDb->addReviewCompanyInfo($save);
    }

    public function getReviewCompanyInfo($company_id)
    {
        $companyDb = new OrderReviewNewCompanyModel();
        return $companyDb->getReviewCompanyInfo($company_id);
    }
}
