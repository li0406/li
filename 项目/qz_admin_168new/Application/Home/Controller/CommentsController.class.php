<?php
// +----------------------------------------------------------------------
// | ConmentsController
// +----------------------------------------------------------------------
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\CommentsLogicModel;

class CommentsController extends HomeBaseController
{
    protected $commentsLogicModel;
    public function __construct()
    {
        parent::__construct();
        $this->commentsLogicModel = new CommentsLogicModel();
    }

    public function statistics()
    {
        $result = $this->commentsLogicModel->getStatisticsByCompany(I('get.'));
        $this->assign($result);
        $this->assign('citys',D('Quyu')->getQuyuList());
        $this->display();
    }

}