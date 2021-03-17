<?php


namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\ThreeVJiaLogicModel;

class ThreevjiaController extends HomeBaseController
{
    public function registerStat()
    {
        $model = new ThreeVJiaLogicModel();
        $list = $model->getRegisterList();
        $this->assign("list",$list);
        $this->display();
    }
}