<?php

namespace Common\Model\Logic;

use Common\Model\BaikeModel;

class BaikeLogicModel
{
    /**
     * 获取最新的百科列表
     */
    public function getNewList($limit = 10,$id)
    {
        $model = new BaikeModel();
        return $model->getNewList($limit,$id);
    }
}

