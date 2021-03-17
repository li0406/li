<?php
namespace Sub\Controller;
use Sub\Common\Controller\SubBaseController;
class GtverifyController extends SubBaseController{
    /**
     * 调用极验证，初始化验证参数
     * @return [type] [description]
     */
    public function getstartverify()
    {
        return D('Common/verifyserver')->startcaptchaservlet();
    }
    /**
     * 极验证二次验证
     * @return [type] [description]
     */
    public function verifylogin()
    {
        $result = D('Common/verifyserver')->verifyloginservlet();
        if ($result) {
            session("geetest_verify",true);
            $this->ajaxReturn(array('data' => '','info' => '验证成功','status'=>1 ));
        }else{
            $this->ajaxReturn(array('data' => '','info' => '验证错误，请刷新页面在尝试！','status'=>0 ));
        }
    }
}

