<?php
// +----------------------------------------------------------------------
// | AuditOrderController 质检订单
// +----------------------------------------------------------------------
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\AuditCitysLogicModel;

class AuditCityController extends HomeBaseController
{
    /**
     * 质检账号城市列表
     */
    public function index()
    {
        $assign = AuditCitysLogicModel::getUserCitys();
        $this->assign(array_merge($assign,[
            'role' => AuditCitysLogicModel::$role
        ]));
        $this->display();
    }

    /**
     * 质检账号城市详情
     */
    public function detail()
    {
        $admin_id = I('get.id', 0, 'intval');
        if (empty($admin_id)) {
            $this->error('错误，请重新进入');
        }
        $citys = AuditCitysLogicModel::getJiheCity();
        $user = AuditCitysLogicModel::getOneUserCitys($admin_id);
        $user['audit_city_ids'] = array_column($user['audit_city'],'cid');
        $this->assign([
            'citys' => $citys,
            'user' => $user
        ]);

        $this->display();
    }

    /**
     * 质检账号城市保存
     */
    public function save()
    {
        if (IS_POST) {
            $city = I('post.city',[]);
            $admin_id = I('post.admin_id',0);
            if (empty($admin_id)) {
                $this->ajaxReturn(['error_msg' => '用户未选择', 'error_code' => 1]);
            }
            $flag = AuditCitysLogicModel::saveOneUserCitys($admin_id,$city);
            if ($flag !== false) {
                $this->ajaxReturn(['error_msg' => '保存成功', 'error_code' => 0]);
            }
            $this->ajaxReturn(['error_msg' => '保存失败', 'error_code' => 1]);
        }
        $this->ajaxReturn(['error_msg' => '请求错误', 'error_code' => 1]);
    }

    /**
     * 质检账号城市清除
     */
    public function clear()
    {
        if (IS_POST) {
            $admin_id = I('post.admin_id',0);
            if (empty($admin_id)) {
                $this->ajaxReturn(['error_msg' => '用户未选择', 'error_code' => 1]);
            }
            $flag = AuditCitysLogicModel::clearOneUserCitys($admin_id);
            if ($flag !== false) {
                $this->ajaxReturn(['error_msg' => '操作成功', 'error_code' => 0]);
            }
            $this->ajaxReturn(['error_msg' => '操作失败', 'error_code' => 1]);
        }
        $this->ajaxReturn(['error_msg' => '请求错误', 'error_code' => 1]);
    }
}