<?php

namespace Cpa\Common\Controller;

use Think\Controller;

class JiajuBaseController extends Controller
{
    public function _initialize()
    {
        if (!isset($_SESSION['u_userInfo'])) {
            if (IS_AJAX) {
                $this->ajaxReturn(array('data' => '', 'info' => '您的登录已超时,请重新登录', 'status' => 0));
            } else {
                header('LOCATION:http://u.qizuang.com');
            }
            die();
        }
        if ($_SESSION['u_userInfo']['classid'] != 4) {
            if (IS_AJAX) {
                $this->ajaxReturn(array('data' => '', 'info' => '无效的请求,请返回用户中心首页', 'status' => 0));
            } else {
                header('LOCATION:http://u.qizuang.com/home/');
            }
            die();
        }

        if ($_SESSION['u_userInfo']['classid'] == 4) {
            if (IS_AJAX) {
                $this->ajaxReturn(['data' => ['url' => 'http://u.jiaju.qizuang.com/user/index'], 'info' => '请访问家具商用户中心', 'status' => 0]);
            } else {
                header('LOCATION:http://u.jiaju.qizuang.com/user/index');
            }
            die();
        }
    }
}
