<?php
/**
 *  极验证
 */
namespace Common\Model;
use Think\Model;

class VerifyserverModel extends Model{

    protected $autoCheckFields = false;
    /**
     * 使用Get的方式返回：challenge和capthca_id 此方式以实现前后端完全分离的开发模式 专门实现failback
     */
    public function startcaptchaservlet()
    {
        import('Library.Org.Util.geetestlib');
        $GtSdk = new \GeetestLib(OP('CAPTCHA_ID'), OP('PRIVATE_KEY'));
        session_start();
        $user_id = "test";
        $status = $GtSdk->pre_process($user_id);
        $_SESSION['gtserver'] = $status;
        $_SESSION['user_id'] = $user_id;
        echo $GtSdk->get_response_str();
    }

    /**
     * 输出二次验证结果,本文件示例只是简单的输出 Yes or No
     * @param string $value [description]
     */
    public function verifyloginservlet()
    {
        import('Library.Org.Util.geetestlib');
        session_start();
        $GtSdk = new \GeetestLib(OP('CAPTCHA_ID'), OP('PRIVATE_KEY'));
        $user_id = $_SESSION['user_id'];
        if ($_SESSION['gtserver'] == 1) {
            $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $user_id);
            return $result;

            /*if ($result) {
                $this->ajaxReturn(array('data' => $result,'info' => '成功','status'=>1 ));
            } else{
                $this->ajaxReturn(array('data' => '','info' => '验证错误，请刷新页面在尝试！','status'=>0 ));
            }*/
        }else{
            $result = $GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode']);
            return $result;

            /*if ($result) {
                $this->ajaxReturn(array('data' => $result,'info' => '宕机','status'=>1 ));
            }else{
                $this->ajaxReturn(array('data' => '','info' => '验证错误，请刷新页面在尝试！','status'=>0 ));
            }*/
        }
    }
}