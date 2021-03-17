<?php

//会员订单设置

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\UserOrderOptionLogicModel;

class UserorderoptionController extends HomeBaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        //获取城市小区信息
        $citys = getUserCitys();
        //获取设置的装修公司
        $optionLogic = new UserOrderOptionLogicModel();
        $list = $optionLogic->getOrderOption(I('get.'));
        $this->assign('citys', $citys);
        $this->assign('list', $list['list']);
        $this->assign('page', $list['page']);
        $this->display();
    }

    /**
     * 保存数据
     */
    public function saveOrderOption()
    {
        $post = I('post.');
        $optionLogic = new UserOrderOptionLogicModel();
        $info = $optionLogic->saveOption($post, $_SESSION['uc_userinfo']);
        $this->ajaxReturn(['error_code' => $info['error_code'], 'error_msg' => $info['error_msg']]);
    }

    /**
     * 删除数据
     */
    public function delOrderOption()
    {
        $id = I('post.id');
        $optionLogic = new UserOrderOptionLogicModel();
        $info = $optionLogic->delOption($id);
        $this->ajaxReturn(['error_code' => $info['error_code'], 'error_msg' => $info['error_msg']]);
    }

    public function getCompanyOrderOptionInfo()
    {
        $optionLogic = new UserOrderOptionLogicModel();
        $id = I('post.id');
        if (!$id) {
            $this->ajaxReturn(['error_code' => 404, 'error_msg' => '未查找到数据! ']);
        }
        $company = $optionLogic->getCompanyOrderOptionInfo($id);
        if ($company) {
            $this->ajaxReturn(['error_code' => 0, 'info' => $company]);
        } else {
            $this->ajaxReturn(['error_code' => 404, 'error_msg' => '未查找到数据! ']);
        }
    }

    public function getCompanyInfo()
    {
        $company_id = I('post.company_id');
        if (!$company_id) {
            $this->ajaxReturn(['error_code' => 404, 'error_msg' => '未查找到数据! ']);
        }
        $company = D("User")->getCompanyInfoById($company_id);
        if ($company) {
            $returnData = [
                'jc' => $company['jc'],
                'cname' => $company['cname'],
            ];
            $this->ajaxReturn(['error_code' => 0, 'info' => $returnData]);
        } else {
            $this->ajaxReturn(['error_code' => 404, 'error_msg' => '未查找到数据! ']);
        }
    }
}