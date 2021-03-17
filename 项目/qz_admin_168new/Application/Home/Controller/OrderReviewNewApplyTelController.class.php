<?php


namespace Home\Controller;


use Common\Enums\ApiConfig;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\OrderReviewNewApplyLogicModel;
use Think\Controller;

class OrderReviewNewApplyTelController extends HomeBaseController
{
    protected $logic;

    public function __construct()
    {
        parent::__construct();
        $this->logic = new OrderReviewNewApplyLogicModel();
    }

    /**
     * 申请显号
     * return void
     */
    public function tel_openeye()
    {
        if (IS_POST) {
            $post = I('post.');
            if (empty($post['text'])) {
                $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => '请填写申请理由']);
            }
            if (mb_strlen($post['text']) > 50) {
                $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => '申请理由50字符以内']);
            }
            $result = $this->logic->save($post);
            if (is_string($result) && !ctype_digit($result)) {
                $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => $result]);
            } else {
                if ($result !== false) {
                    $this->ajaxReturn(['error_code' => ApiConfig::REQUEST_SUCCESS, 'error_msg' => '申请成功']);
                } else {
                    $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_SAVE_ERROR, 'error_msg' => '申请失败']);
                }
            }
        }
        $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => '请求错误']);
    }

    /**
     * 获取显号记录列表
     * return void
     */
    public function getApplyTelList()
    {
        $id = I('get.id', '', 'trim');
        if (empty($id)) {
            $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => '缺少参数！']);
        }
        $result = $this->logic->getAll($id);
        if ($result !== false) {
            $this->assign('list', $result);
            $html = $this->fetch("orders_apply_tel_modal");
            $this->ajaxReturn(['error_code' => ApiConfig::REQUEST_SUCCESS, 'error_msg' => '获取成功', 'data' => $html]);
        } else {
            $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_SAVE_ERROR, 'error_msg' => '获取失败', 'data' => '']);
        }
    }

    /**
     * 显号审核操作
     * return void
     */
    public function pass_apply_tel()
    {
        if (IS_POST) {
            //判断是否有查看呼叫记录的权限
            if (check_user_menu_auth('/orderreviewnewapplytel/pass_apply_tel') === false) {
                $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => '您无权审核！']);
            }
            $id = I('post.id', '', 'trim');
            //检查参数
            if (empty($id)) {
                $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => '缺少参数！']);
            }
            $save['status'] = I('post.status', 1, 'intval');
            $save['passer_id'] = session('uc_userinfo.id');
            $save['pass_time'] = time();
            $result = $this->logic->saveOrdersApplyTel($id, $save);
            if ($result) {
                $this->ajaxReturn(['error_code' => ApiConfig::REQUEST_SUCCESS, 'error_msg' => '操作成功']);
            } else {
                $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_SAVE_ERROR, 'error_msg' => '操作失败']);
            }
        } else {
            $this->ajaxReturn(['error_code' => ApiConfig::REVIEW_ORDER_ERROR, 'error_msg' => '请求错误']);
        }
    }
}