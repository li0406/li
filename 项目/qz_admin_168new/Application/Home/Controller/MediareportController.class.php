<?php
// +----------------------------------------------------------------------
// | Mediareport  齐装网：媒体报道记录管理功能
// +----------------------------------------------------------------------
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\MediaReportLogicModel;

class MediareportController extends HomeBaseController
{
    public function index()
    {
        $assign = MediaReportLogicModel::getMediaReports(I('get.', '', 'trim'));
        $this->assign($assign);
        $this->display();
    }

    public function detail()
    {
        $id = I('get.id', 0, 'intval');
        if (!empty($id)) {
            $data = MediaReportLogicModel::getMediaReport($id);
            $data['full_image'] = empty($data['image']) ? null : '"<img src=\"http://'.C('QINIU_DOMAIN').'/'.$data['image'].'\" class=\"img-responsive\">"';
            if (empty($data)) {
                $this->error('请求错误，请返回重新操作');
            }
            $this->assign(['data' => $data]);
        }
        $this->display();
    }

    public function save()
    {
        if (IS_POST) {
            $post = I('post.', [], 'trim');
            if (($error_msg = MediaReportLogicModel::filterMediaReport($post)) !== true) {
                $this->ajaxReturn(['error_code' => 404, 'error_msg' => $error_msg]);
            }

            $flag = MediaReportLogicModel::saveMediaReport($post);
            if ($flag !== false) {
                $this->ajaxReturn(['error_code' => 0, 'error_msg' => '保存成功']);
            }
            $this->ajaxReturn(['error_code' => 404, 'error_msg' => '保存失败']);
        }
        $this->ajaxReturn(['error_code' => 404, 'error_msg' => '请求错误']);
    }

    public function deletes()
    {
        if (IS_POST) {
            $id = I('post.id', '', 'trim');
            $flag = MediaReportLogicModel::delMediaReport($id);
            if ($flag !== false) {
                $this->ajaxReturn(['error_code' => 0, 'error_msg' => '删除成功']);
            }
            $this->ajaxReturn(['error_code' => 404, 'error_msg' => '删除失败']);
        }
        $this->ajaxReturn(['error_code' => 404, 'error_msg' => '请求错误']);
    }
}