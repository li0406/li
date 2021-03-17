<?php
namespace Common\Controller;
use Think\Controller;
class CollectController extends Controller{
    /**
     * 添加收藏
     */
    public function setCollect()
    {
        $data = $_POST;

        if (isset($_SESSION['u_userInfo'])) {

            if($_SESSION['u_userInfo']['classid'] != 1){
                $this->ajaxReturn(['data' => '', 'info' => '该账号无操作权限~', 'status' => 0]);
            }

            $saveData = array(
                'classtype' => remove_xss($data['classtype']),
                'classid' => remove_xss($data['classid']),
                'userid' => session('u_userInfo.id'),
                'time' => time()
            );

            $find = D('Usercollect')->getOne($saveData['classid'],$saveData['userid'],$saveData['classtype']);
            if (!empty($find)) {
                $this->ajaxReturn(['data' => '', 'info' => '已经收藏~', 'status' => 0]);
            }

            $i = D('Usercollect')->addCollect($saveData);

            if ($i !== false) {
                $this->ajaxReturn(['data' => '', 'info' => '收藏成功', 'status' => 1]);
            }
            $this->ajaxReturn(['data' => '', 'info' => '操作失败,请刷新重新！', 'status' => 0]);
        }
        $this->ajaxReturn(['data' => '', 'info' => '请先登录后收藏！', 'status' => 0]);
    }

    /**
     * 取消收藏
     * @return [type] [description]
     */
    public function cancelcollect()
    {
        if (IS_POST) {
            $id = I('post.classid');
            $type = I('post.classtype');
            $i = D('Usercollect')->delcollect($id, $_SESSION['u_userInfo']['id'], $type);
            if ($i !== false) {
                $this->ajaxReturn(['data' => '', 'info' => '取消收藏成功', 'status' => 1]);
            }
            $this->ajaxReturn(['data' => '', 'info' => '操作失败,请刷新重新！', 'status' => 0]);
        } else {
            $this->ajaxReturn(['data' => '', 'info' => '操作失败,请刷新重新！', 'status' => 0]);
        }
    }
}