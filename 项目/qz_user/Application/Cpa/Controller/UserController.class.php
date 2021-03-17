<?php
// +----------------------------------------------------------------------
// | UserController  家具商个人中心
// +----------------------------------------------------------------------
// | Author: 2851986856@qq.com
// +----------------------------------------------------------------------
namespace Cpa\Controller;

use Cpa\Common\Controller\JiajuBaseController;

class UserController extends JiajuBaseController
{
    //用户个人中心首页
    public function index()
    {
        //用户详情信息
        $user = D('Common/Logic/JiajuUserCompanyLogic')->getCompanyInfoByAdmin(session('u_userInfo.id'), session('u_userInfo.cs'));
        $this->assign('user_data',$user);
        //用户最近10条日志信息
        $userLog = D('Common/Logic/JiajuUserCompanyLogic')->getUserLogByUid(session('u_userInfo.id'));
        $this->assign('user_log',$userLog);
        $this->display();
    }

    //用户个人中心基本信息页面
    public function info()
    {
        //用户详情信息
        $user = D('Common/Logic/JiajuUserCompanyLogic')->getCompanyInfoByAdmin(session('u_userInfo.id'), session('u_userInfo.cs'));
        $this->assign('user_data',$user);
        $this->display();
    }

    //用户中心个人信息更新
    public function editInfo()
    {
        //导入扩展文件
        import('Library.Org.Util.App');
        $app = new \App();
        $ip = $app->get_client_ip();
        if (IS_POST) {
            $logo = I('post.logo');
            if (check_imgPath($logo)) {
                import('Library.Org.Util.Fiftercontact');
                $filter = new \Fiftercontact();
                $data['name'] = $filter->filter_common(I('post.name'), array('Sbc2Dbc', 'filter_script', array('filter_sensitive_words', array(2, 3, 5)), 'filter_link', 'filter_html_url'));
                $data['sex'] = I('post.sex');
                $data['tel'] = I('post.tel');
                $data['qq'] = I('post.qq');
                $data['jc'] = I('post.jc');
                $data['qc'] = I('post.qc');
                $data['logo'] = I('post.logo');
                $data['cals'] = I('post.cals'); //区号
                $data['cal'] = I('post.cal');   //座机号码
                $data['dz'] = I('post.dz');
                $data['sale_type'] = I('post.sale_type');
                $data['sale_range'] = I('post.sale_range');
                $data['furniture_category'] = I('post.furniture_category');
                $data['furniture_style'] = I('post.furniture_style');
                $data['furniture_level'] = I('post.furniture_level');
                $data['furniture_brand'] = I('post.furniture_brand');

                $i = D('User')->edtiUserInfo($_SESSION['u_userInfo']['id'], $data);
                $inf = D('Common/Logic/JiajuUserCompanyLogic')->editUserCompany($_SESSION['u_userInfo']['id'], $data);

                if ($i !== false && $inf !== false) {
                    //记录日志
                    $LogData = array(
                        'username' => $data['qc'],
                        'userid' => session('u_userInfo.id'),
                        'ip' => $ip,
                        'status' => 1,
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                        'info' => '用户更新基本信息成功',
                        'time' => date('Y-m-d H:i:s'),
                        'action' => CONTROLLER_NAME . '/' . ACTION_NAME
                    );
                    D('Loguser')->addLog($LogData);
                    //判断是否需要更新session
                    if (session('u_userInfo.qc') != $data['qc']){
                        session('u_userInfo.qc',$data['qc']);
                    }
                    if (session('u_userInfo.jc') != $data['jc']){
                        session('u_userInfo.jc',$data['jc']);
                    }
                    if (session('u_userInfo.logo') != $data['logo']){
                        session('u_userInfo.logo',$data['logo']);
                    }
                    if (session('u_userInfo.name') != $data['name']){
                        session('u_userInfo.name',$data['name']);
                    }
                    $this->ajaxReturn(array('data' => '', 'info' => '修改成功', 'status' => 1));
                } else {
                    //记录日志
                    $LogData = array(
                        'username' => session('u_userInfo.qc'),
                        'userid' => session('u_userInfo.id'),
                        'ip' => $ip,
                        'status' => 2,
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                        'info' => '用户更新基本信息失败',
                        'time' => date('Y-m-d H:i:s'),
                        'action' => CONTROLLER_NAME . '/' . ACTION_NAME
                    );
                    D('Loguser')->addLog($LogData);
                    $this->ajaxReturn(array('data' => '', 'info' => '修改失败', 'status' => 0));
                }
            } else {
                //记录日志
                $LogData = array(
                    'username' => session('u_userInfo.qc'),
                    'userid' => session('u_userInfo.id'),
                    'ip' => $ip,
                    'status' => 2,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'info' => '用户更新基本信息失败',
                    'time' => date('Y-m-d H:i:s'),
                    'action' => CONTROLLER_NAME . '/' . ACTION_NAME
                );
                D('Loguser')->addLog($LogData);
                $this->ajaxReturn(array('data' => '', 'info' => '操作失败,请稍后再试！', 'status' => 0));
            }
        } else {
            //记录日志
            $LogData = array(
                'username' => session('u_userInfo.qc'),
                'userid' => session('u_userInfo.id'),
                'ip' => $ip,
                'status' => 2,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'info' => '用户更新基本信息失败',
                'time' => date('Y-m-d H:i:s'),
                'action' => CONTROLLER_NAME . '/' . ACTION_NAME
            );
            D('Loguser')->addLog($LogData);
            $this->ajaxReturn(array('data' => '', 'info' => '操作失败,请稍后再试！', 'status' => 0));
        }
    }

    /**
     * 修改安全手机
     */
    public function addTelSafe()
    {
        if (IS_POST) {
            //导入扩展文件
            import('Library.Org.Util.App');
            $app = new \App();
            $ip = $app->get_client_ip();
            if (I('post.bind') == 1) {
                $tel = I('post.tel');
                $code = I('post.code');
                if (empty($tel)) {
                    $this->ajaxReturn(['data' => '', 'info' => '手机号码为空', 'status' => 0]);
                }
                $result = R('Common/Login/smsCheck', [$code, $tel]);
                if ($result !== true) {  //此处验证码验证不通过
                    $this->ajaxReturn($result);
                }
                $data['tel_safe'] = $tel;
                $data['tel_safe_ck'] = 1;

                $i = D('User')->edtiUserInfo(session('u_userInfo.id'), $data);
                if ($i !== false) {
                    //记录日志
                    $LogData = array(
                        'username' => session('u_userInfo.qc'),
                        'userid' => session('u_userInfo.id'),
                        'ip' => $ip,
                        'status' => 1,
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                        'info' => '用户手机绑定成功',
                        'time' => date('Y-m-d H:i:s'),
                        'action' => CONTROLLER_NAME . '/' . ACTION_NAME
                    );
                    D('Loguser')->addLog($LogData);
                    //绑定同时更新session
                    session('u_userInfo.tel_safe',$data['tel_safe']);
                    session('u_userInfo.tel_safe_ck',1);
                    $this->ajaxReturn(array('data' => '', 'info' => '绑定成功', 'status' => 1));
                } else {
                    //记录日志
                    $LogData = array(
                        'username' => session('u_userInfo.qc'),
                        'userid' => session('u_userInfo.id'),
                        'ip' => $ip,
                        'status' => 2,
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                        'info' => '用户手机绑定失败',
                        'time' => date('Y-m-d H:i:s'),
                        'action' => CONTROLLER_NAME . '/' . ACTION_NAME
                    );
                    D('Loguser')->addLog($LogData);
                    $this->ajaxReturn(array('data' => '', 'info' => '绑定失败', 'status' => 0));
                }
            } elseif (I('post.bind') == 2) {
                $data['tel_safe'] = '';
                $data['tel_safe_ck'] = 0;

                $i = D('User')->edtiUserInfo(session('u_userInfo.id'), $data);
                if ($i !== false) {
                    $_SESSION['u_userInfo']['tel_safe'] = '';
                    $_SESSION['u_userInfo']['tel_safe_ck'] = 0;
                    //记录日志
                    $LogData = array(
                        'username' => session('u_userInfo.qc'),
                        'userid' => session('u_userInfo.id'),
                        'ip' => $ip,
                        'status' => 1,
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                        'info' => '用户解除手机绑定成功',
                        'time' => date('Y-m-d H:i:s'),
                        'action' => CONTROLLER_NAME . '/' . ACTION_NAME
                    );
                    D('Loguser')->addLog($LogData);
                    //解除绑定同时解除session
                    session('u_userInfo.tel_safe','');
                    session('u_userInfo.tel_safe_ck',0);

                    $this->ajaxReturn(array('data' => '', 'info' => '解除绑定成功', 'status' => 1));
                } else {
                    //记录日志
                    $LogData = array(
                        'username' => session('u_userInfo.qc'),
                        'userid' => session('u_userInfo.id'),
                        'ip' => $ip,
                        'status' => 2,
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                        'info' => '用户解除手机绑定失败',
                        'time' => date('Y-m-d H:i:s'),
                        'action' => CONTROLLER_NAME . '/' . ACTION_NAME
                    );
                    D('Loguser')->addLog($LogData);
                    $this->ajaxReturn(array('data' => '', 'info' => '解除绑定失败', 'status' => 0));
                }
            } else {
                $this->ajaxReturn(array('data' => '', 'info' => '操作失败,请稍后再试！', 'status' => 0));
            }

        } else {
            $this->ajaxReturn(array('data' => '', 'info' => '操作失败,请稍后再试！', 'status' => 0));
        }
    }

    //用户个人中心账号设置页面
    public function account()
    {
        //用户详情信息
        $user = D('Common/Logic/JiajuUserCompanyLogic')->getCompanyInfoByAdmin(session('u_userInfo.id'), session('u_userInfo.cs'));
        $this->assign('user_data',$user);
        $this->display();
    }

    //修改密码
    public function editPwd()
    {
        if (IS_POST) {
            //导入扩展文件
            import('Library.Org.Util.App');
            $app = new \App();
            $ip = $app->get_client_ip();
            $pwd = I('post.pwd');
            $rpwd = I('post.rpwd');
            if (empty($_SESSION['u_userInfo']['id'])) {
                $this->ajaxReturn(['data' => '', 'info' => '登录过期，请重新登录', 'status' => 0]);
            }
            if (empty($pwd) || empty($rpwd)) {
                $this->ajaxReturn(['data' => '', 'info' => '请输入新密码', 'status' => 0]);
            }
            if (strlen($pwd) > 20 ||strlen($pwd) < 6 ) {
                $this->ajaxReturn(['data' => '', 'info' => '请输入6-18位新密码', 'status' => 0]);
            }
            if(!preg_match('/(?!^(\d+|[a-zA-Z]+|[~!@#$%^&*?]+)$)^[\w~!@#$%\^&*?]/',$pwd)) {
                $this->ajaxReturn(['data' => '', 'info' => '请填写由数字,字母,特殊字符组合的密码', 'status' => 0]);
            }
            if ($pwd !== $rpwd) {
                $this->ajaxReturn(['data' => '', 'info' => '两次密码不一致', 'status' => 0]);
            }

            $model = D('User');

            $check_user_compare_password = $model->check_user_compare_password($_SESSION['u_userInfo']['id'], md5($pwd));
            if ($check_user_compare_password === 'same') {
                $this->ajaxReturn(['data' => '', 'info' => '新密码与原始密码一致', 'status' => 0]);
            }
            if ($check_user_compare_password === false) {
                $this->ajaxReturn(['data' => '', 'info' => '数据错误请重试', 'status' => 0]);
            }
            $data['pass'] = md5($pwd);
            if ($model->create($data, 6)) {
                $i = D('User')->edtiUserInfo($_SESSION['u_userInfo']['id'], $data);
                if ($i !== false) {
                    //记录日志
                    $LogData = array(
                        'username' => session('u_userInfo.qc'),
                        'userid' => session('u_userInfo.id'),
                        'ip' => $ip,
                        'status' => 1,
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                        'info' => '用户修改密码成功',
                        'time' => date('Y-m-d H:i:s'),
                        'action' => CONTROLLER_NAME . '/' . ACTION_NAME
                    );
                    D('Loguser')->addLog($LogData);
                    $this->ajaxReturn(array('data' => '', 'info' => '修改成功', 'status' => 1));
                } else {
                    //记录日志
                    $LogData = array(
                        'username' => session('u_userInfo.qc'),
                        'userid' => session('u_userInfo.id'),
                        'ip' => $ip,
                        'status' => 2,
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                        'info' => '用户修改密码失败',
                        'time' => date('Y-m-d H:i:s'),
                        'action' => CONTROLLER_NAME . '/' . ACTION_NAME
                    );
                    D('Loguser')->addLog($LogData);
                    $this->ajaxReturn(array('data' => '', 'info' => '修改失败', 'status' => 0));
                }
            } else {
                $this->ajaxReturn(array('data' => '', 'info' => $model->getError(), 'status' => 0));
            }
        } else {
            $this->ajaxReturn(array('data' => '', 'info' => '操作失败,请稍后再试！', 'status' => 0));
        }

    }

    //用户个人中心账单查看页面
    public function bill()
    {
        $page = I('post.p');
        $startTime = I('get.first');  //查询开始时间
        $endTime = I('get.end');      //查询结束时间
        $time = [strtotime('-90 day'), time()];
        if (!empty($startTime)) {
            if (strtotime($startTime) < strtotime('-91 day')) {
                $this->error('最多查询90天以内数据');
            }
            if (empty($endTime)) {
                $time = [strtotime($startTime), time()];
            } else {
                $time = [strtotime($startTime), strtotime($endTime . ' 23:59:59')];
            }
        }

        //获取用户账单列表
        $result = D('Common/Logic/CpaUserExpensesRecordLogic')->getUserExpensesRecordByPage(intval($_SESSION['u_userInfo']['id']), $time, $page, 20);
        $this->assign('list', $result['list']);
        $this->assign('page', $result['page']);
        //获取用户充值/消费/退费
        $billDetail = D('Common/Logic/CpaUserExpensesRecordLogic')->getUserSumBill(intval($_SESSION['u_userInfo']['id']), $time);
        $this->assign('bill_detail', $billDetail);
        //获取用户钱包
        $wallet = D('Common/Logic/CpaUserInformationLogic')->getUserWallet(intval($_SESSION['u_userInfo']['id']));
        $this->assign('wallet', $wallet);
        $this->assign('times', $time);
        $this->display();
    }

    /**
     * 用户退出
     * @return [type] [description]
     */
    public function loginout()
    {
        R('Common/Login/loginout');
        die();
    }

    /**
     * 短信发送
     * @return [type] [description]
     */
    public function sendsms()
    {
        if (IS_POST) {
            $code = strtolower($_POST["code"]);
            //判断是否验证 验证码
            if (!empty($code)) {
                //验证码需要成功
                if (check_verify($code, "", false)) {
                    R("Common/Sms/sendsms");
                }
            } else {
                if (S("Cache:Geetest:".session_id()) == 1) {
                    S("Cache:Geetest:".session_id(),null);
                    R("Common/Sms/sendsms");
                }
            }
            $this->ajaxReturn(array('data' => '', 'info' => '图形验证码不正确！', 'status' => 9));
        }
        die();
    }

    /**
     * 验收手机验证码
     */
    public function verifysmscode()
    {
        R('Common/Sms/verifysmscode');
    }
}
