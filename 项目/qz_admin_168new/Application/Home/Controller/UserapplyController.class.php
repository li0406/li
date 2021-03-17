<?php
// +----------------------------------------------------------------------
// | UserApplyController  用户申请记录
// +----------------------------------------------------------------------

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\CompanyContactViewsLogicModel;

class UserapplyController extends HomeBaseController
{
    /**
     * 装修公司联系方式访问记录列表(参数type是2则查询以及显示黑名单)
     *
     * @return void
     */
    public function contact()
    {
        $tel = I('get.tel','');
        $start = I('get.start','');
        $end = I('get.end','');
        $type = I('get.type',1);
        $logic = new CompanyContactViewsLogicModel();
        if ($type == 2) {
            $list = $logic->getBlackList($tel, $start, $end);
        } else {
            $list = $logic->getLogList($tel, $start, $end);
        }
        $this->assign(compact('list'));
        $parse =  parse_url($_SERVER['REQUEST_URI']);
        $this->assign('query',isset($parse['query']) ? $parse['query'] : '');
        if (I('get.type',1) == 2) {
            $this->display('contactblcak');
        } else {
            $this->display();
        }
    }

    /**
     * 添加装修公司联系方式访问黑名单
     *
     * @return void
     */
    public function saveblack()
    {
        $logic = new CompanyContactViewsLogicModel();
        $data = I('post.');

        if (empty($data['tel']) && empty($data['ip'])) {
            $this->ajaxReturn(['error_msg' => '手机号与IP地址必须填一项', 'error_code' => 400001]);
        }

        $map = ['tel' => $data['tel'], 'ip' => $data['ip']];
        $result = $logic->saveBlack($data, $map);
        if ($result) {
            $this->ajaxReturn(['error_msg' => '保存成功', 'error_code' => 0]);
        } else {
            $this->ajaxReturn(['error_msg' => '保存失败', 'error_code' => 400001]);
        }
    }

    /**
     * 删除装修公司联系方式访问黑名单
     *
     * @return void
     */
    public function removeblack()
    {
        $logic = new CompanyContactViewsLogicModel();
        $id = I('post.id', '');
        if (empty($id)) {
            $this->ajaxReturn(['error_msg' => '数据错误', 'error_code' => 400001]);
        }

        $result = $logic->removeBlack($id);
        if ($result) {
            $this->ajaxReturn(['error_msg' => '恢复成功', 'error_code' => 0]);
        } else {
            $this->ajaxReturn(['error_msg' => '恢复失败', 'error_code' => 400001]);
        }
    }
}