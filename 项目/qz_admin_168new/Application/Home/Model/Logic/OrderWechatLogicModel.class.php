<?php

namespace Home\Model\Logic;

class OrderWechatLogicModel
{
    /**
     * 解除微信账号绑定
     * @param  string $company_id [公司ID]
     * @return [type]        [description]
     */
    public function unBindWechatAccount($company_id)
    {
        $data = array(
            "is_delete" => 1
        );
        return D("Home/Db/OrderWechat")->editWechat($company_id,$data);
    }

}
