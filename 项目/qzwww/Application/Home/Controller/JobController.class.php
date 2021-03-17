<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class JobController extends HomeBaseController{

    public function index()
    {

        //客服挂件，回顶按钮开关
        //1--关闭, 不为1 -- 打开
        $this->assign("is_top","0");
        $this->display();
    }
}