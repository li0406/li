<?php

namespace Home\Model\Logic;
use Home\Model\OptionsModel;

/**
 *
 */
class OptionsLogicModel
{
    public function getMyTelCenterChannelByid($user_id)
    {
        $model = new OptionsModel();
        return $model->getMyTelCenterChannelByid($user_id);
    }
}