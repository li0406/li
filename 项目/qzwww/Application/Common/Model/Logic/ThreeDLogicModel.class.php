<?php

namespace Common\Model\Logic;

use Common\Model\Db\ThreeDModel;
use Common\Model\Db\TuCategoryModel;

class ThreeDLogicModel
{
    protected $threeDModel;

    public function __construct()
    {
        $this->threeDModel = new ThreeDModel();
    }

    public function getHome()
    {
        $data = $this->threeDModel->getHomeThreeDItemList();
        return $data;
    }
    public function getThreeDList($id, $title, $fengge, $huxing, $limit){
        return D('Common/XiaoguotuThreedimension')->getCustomList($id, $title, $fengge, $huxing, $limit,'x.create_time DESC','x.face,x.id,x.title');
    }
}
