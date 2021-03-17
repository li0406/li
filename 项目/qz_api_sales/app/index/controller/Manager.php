<?php
// +----------------------------------------------------------------------
// | Manager 管理员
// +----------------------------------------------------------------------
namespace app\index\controller;

use app\common\model\logic\LogAdminLogic;
use app\common\model\logic\AdminuserLogic;
use app\common\model\logic\PermissionLogic;
use Qizuang\JWTAuth\Src\Facade\Auth;
use think\Controller;

class Manager extends controller
{
    /**
     * 获取用户菜单
     *
     * @param PermissionLogic $permissionLogic
     * @return array
     */
    public function getMyMenu(PermissionLogic $permissionLogic)
    {
        $user = $this->request->param('user');
        //获取目录权限
        $auth_menu_result = $permissionLogic->getMyMenu($user['role_id']);
        return sys_response(0, '', [
            $auth_menu_result
        ]);

    }

    /**
     * 退出登录
     *
     * @return array
     */
    public function loginOut()
    {
        Auth::killToken($this->request->header('token'));
        return sys_response(0, '');
    }

    /**
     * 获取token
     *
     * @return array
     */
    public function accountToken(AdminuserLogic $adminuserLogic)
    {
        $post = $this->request->post();
        //表单验证
        if (true !== ($error_msg = $this->validate($post, '\app\index\validate\Passport.account_login'))) {
            return sys_response(4000002, $error_msg);
        }

        $admin = $adminuserLogic->getAmdin($post);
        if (!$admin) {
            return sys_response(3000003, '账号密码错误,请重新输入');
        }
        if (md5($post['user_pwd']) !== $admin['pass']) {
            return sys_response(3000003, '账号密码错误,请重新输入');
        }
        if ($admin['state'] != 1 || $admin['stat'] != 1) {
            return sys_response(3000003, '账号已被封禁，请联系后台管理员');
        }

        // 查询是否绑定微信
        $appid = $this->request->post("appid");
        $bind_status = $adminuserLogic->checkBindOpenid($admin["id"], $appid);

        $info = [
            'nick_name' => $admin['name'],
            'user_name' => $admin['user'],
            'role_name' => $admin['roler']['role_name'],
            'logo' => $admin['logo'],
            'user_id' => $admin['id'],
            'role_id' => $admin['uid'],
        ];

        //下发Token
        $info['token_type'] = 2;
        $jwt_token = Auth::getToken($info);

        // 登录日志
        $info["jwt_token"] = $jwt_token;
        $info["cs"] = strval($admin["cs"]);
        LogAdminLogic::addLoginLog($info);

        return sys_response(0,'',[
            'token' => $jwt_token,
            'bind_status' => $bind_status
        ]);
    }

    public function clearCache()
    {
        $user = $this->request->param('user');
        if (count($user) > 0) {
            $managerLogic = new PermissionLogic();
            $managerLogic->clearCache($user["role_id"]);
            return sys_response(0);
        }
        return sys_response(4000002);
    }
}