<?php
// +----------------------------------------------------------------------
// | OrderController  家具商订单管理
// +----------------------------------------------------------------------
// | Author: 2851986856@qq.com
// +----------------------------------------------------------------------
namespace Cpa\Controller;

use Cpa\Common\Controller\JiajuBaseController;

class OrderController extends JiajuBaseController
{
    //查看订单页面
    public function index()
    {
        if (IS_POST){
            $this->checkOrderPass();
        }
        $user = session('u_userInfo');
        //计算装修公司未处理单的数
        $page = I('get.p', 1);
        $pageSize = 10;
        $keyword = remove_xss(I('get.keyword'));
        //获取数据列表
        $orders = $this->getOrders($user['id'], $keyword, $page, $pageSize);
        $this->assign('list',$orders['list']);
        $this->assign('page',$orders['page']);
        //添加修改密码提醒(每隔60天修改一次密码)
        $this->checkPassTime($user['id']);
        //检测是否需要添加短信验证
        $this->checkSmsBlock();
        //渲染页面
        $this->display();
    }

    /**
     * AJAX异步获取订单数据
     */
    public function orderInfo()
    {
        if (IS_POST){
            $id = I('post.id');
            //修复老代码bug
            $user = session('u_userInfo');
            //查询该订单的分配信息
            $myfind = D('Common/Logic/JiajuOrderLogic')->getJiajuOrder($id, $user['id']);
            if (!empty($myfind)) {
                $order = D('Common/Logic/JiajuOrderLogic')->getOrderInfoById($id);
                if ($myfind['isread'] == 0) {
                    //修改阅读状态
                    D('Common/Logic/JiajuOrderLogic')->setRead($id, $user['id']);
                }

                //记录 装修公司查看订单信息信息 日志
                import('Library.Org.Util.App');
                $app = new \App();
                $data = array(
                    'username' => $user['qc'],
                    'userid' => $user['id'],
                    'ip' => $app->get_client_ip(),
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'info' => '查看订单详细信息',
                    'time' => date('Y-m-d H:i:s'),
                    'action' => CONTROLLER_NAME.'/'.ACTION_NAME,
                    'remark' => '订单号: ' . $id,
                    'status' => 1
                );
                D('Loguser')->addLog($data);

                //如果都已读 orders表 order2com_allread 字段记录下分配的所有订单都已读
                if ($order['order2com_allread'] == 0) {
                    $isAllRead = D('Common/Logic/JiajuOrderLogic')->getJiajuOrderFenpeiAllIsRead($order['id']);
                    if ($isAllRead) {
                        //如果都已读,那么修改字段order2com_allread 状态 为1
                        $editdata['order2com_allread'] = 1;
                        D('Common/Logic/JiajuOrderLogic')->editJiajuOrder($order['id'], $editdata);
                    }
                }

                unset($order['customer']);
                unset($order['chk_customer']);
                unset($order['fen_customer']);
                unset($order['deleted']);
                unset($order['fg']);
                unset($order['huxing']);
                unset($order['ip']);
                unset($order['lx']);
                unset($order['lxs']);
                unset($order['lxs']);
                unset($order['on']);
                unset($order['on_sub']);
                unset($order['on_sub']);
                unset($order['on_sub_wuxiao']);
                unset($order['openeye_passer']);
                unset($order['openeye_reason']);
                unset($order['openeye_status']);
                unset($order['openeye_user']);
                unset($order['priority']);
                unset($order['source']);
                unset($order['source_in']);
                unset($order['source_type']);
                unset($order['tel_encrypt']);
                unset($order['tel']);
                unset($order['type']);
                unset($order['type_fw']);
                unset($order['type_zs_sub']);
                unset($order['zhuanfaren']);
                if ($order['step'] == 1){
                    $order['step'] = '未施工';
                }elseif($order['step'] == 2) {
                    $order['step'] = '施工中';
                } elseif($order['step'] == 3) {
                    $order['step'] = '已完工';
                } else {
                    $order['step'] = '';
                }
                $order['allocation_time'] = date('Y-m-d h:i:s',$order['allocation_time']);
                $order['time'] = date('Y-m-d h:i:s',$order['time']);
                $order['time_real'] = date('Y-m-d h:i:s',$order['time_real']);
                if ($order['furniture_type'] == '不限') {
                    $order['furniture_type'] = $order['furniture_type'].': '.$order['furniture_type_child'];
                }
                $this->ajaxReturn(['status' => 1, 'info' => '获取成功', 'data' => $order]);
            } else {
                $this->ajaxReturn(['status' => 0, 'info' => '家具订单不存在', 'data' => []]);
            }
        }
        $this->ajaxReturn(['status' => 0, 'info' => '请求错误', 'data' => []]);
    }

    /**
     * 添加修改密码提醒(每隔30天提示修改一次密码)
     */
    private function checkPassTime($userId)
    {
        $check_alert = S('User:User:Orders:CheckAlert:' . $userId);
        if ($check_alert != 1) {
            S('User:User:Orders:CheckAlert:' . $userId, 1, 2592000);
            $this->assign('check_alert', 1);
        }
    }

    /**
     * 检测是否需要添加短信验证
     */
    private function checkSmsBlock()
    {
        if (session('u_userInfo.cpa_order_pass') != 1) {
            import('Library.Org.Util.App');
            $app = new \App();
            $ip = $app->get_client_ip();
            //获取当前用户两天内查看订单次数最多的3个ip作为常用ip
            $start = date("Y-m-d", strtotime(date("Y-m-d H:i:s", strtotime('-1 day')))) . ' 00:00:00';//开始
            $end = date("Y-m-d H:i:s");

            //获取当前用户两天内查看订单次数最多的3个ip作为常用ip
            $log_list = D("Common/Loguser")->getLoginSucceedList(session('u_userInfo.id'), $start, $end, 1, 'count(id) count,ip', 'count desc,id desc', 'ip', 3, 'Orders/index');
            //判断是否要短信发送按钮
            if (!in_array($ip, array_column($log_list, 'ip')) && !in_array($ip, C('IP_WHITE_LIST'))) {
                //判断是否有绑定安全手机
                if (session('u_userInfo.tel_safe_chk') == 1) {
                    $this->assign('smsblock', 1);
                }
            }
        }
    }

    /**
     * 查询订单信息
     * @param  [int] $comid [公司ID]
     * @param  [int] $page [第几页]
     * @param  [int] $pageSize [每页几条]
     * @return array
     */
    private function getOrders($comid, $keyword, $page, $pageSize)
    {
        $count = D('Common/Logic/JiajuOrderLogic')->getJiajuOrderCountByComid($comid, $keyword);
        if ($count > 0) {
            //进行数据分页
            import('Library.Org.Page.Page');
            $p = new \Page($page, $pageSize, $count, ['prev', 'next']);
            $list = D('Common/Logic/JiajuOrderLogic')->getJiajuOrderListByComid($comid, $keyword, $page, $pageSize);
            return array("list" => $list, "page" => $p->show());
        }
        return array("list" => [], "page" => '');
    }

    //验证订单密码
    private function checkOrderPass()
    {
        $user = session('u_userInfo');
        $pass = I("post.pass");
        //查看该用户目前是否是被锁状态
        $log = D("Logorderpass")->getLastLockLog($user["id"]);
        if (count($log) > 0) {
            if ($log["is_lock"] == 1 && $log["unlock_time"] > time()) {
                $this->ajaxReturn(["data" => "", "info" => "订单查看功能被锁中,预计可看订单时间为" . date("Y-m-d H:i:s", $log["unlock_time"]), 'status' => -1]);
            }
        }
        //检查是否需要验证短信验证码
        if (isset($_POST['code'])) {
            $result = R('Common/Login/smsCheck', [$_POST['code'], $user["tel_safe"]]);
            if ($result !== true) {  //此处验证码验证不通过
                $this->ajaxReturn($result);
            }
        }

        //查询装修公司的订单查看密码
        $orderPass = D("Orderpass")->getOrderPassById($user["id"]);
        if (!empty($orderPass)) {
            //记录查看订单日志
            $logData = [
                'company_id' => $user["id"],
                'company_name' => $user['qc'],
                'act_name' => '输入密码查看订单',
                'act_status' => 'success',
                'act_time' => time(),
                'is_lock' => 0
            ];

            //导入扩展文件
            import('Library.Org.Util.App');
            $app = new \App();
            $ip = $app->get_client_ip();
            //记录用户日志
            $data = array(
                "username" => $user["jc"],
                "userid" => $user["id"],
                "ip" => $ip,
                "status" => 2,
                "user_agent" => $_SERVER["HTTP_USER_AGENT"],
                "time" => date("Y-m-d H:i:s"),
                "action" => CONTROLLER_NAME . "/" . ACTION_NAME
            );
            $status = 1;
            $msg = '订单密码验证成功';
            $data["info"] = '用户查看订单成功';
            if ($orderPass["pass"] != md5($pass)) {
                $logData["act_status"] = 'failed';
                //查询上两次的查看状态
                $logCount = D("Logorderpass")->getLastTwiceLockStatus($user["id"]);
                if ($logCount[0]["lockcount"] == 2) {
                    $logData["is_lock"] = 1;
                    //锁定时间
                    $logData["lock_time"] = time();
                    //解锁时间
                    $logData["unlock_time"] = strtotime("+10 minutes");
                }
                $status = 2;
                $msg = '查看订单密码不正确,请重新输入';
                $data["info"] = "订单密码不正确";
            }
            //写入日志
            D("Logorderpass")->saveLog($logData);
            D("Loguser")->addLog($data);
            //修改用户查看标识
            session('u_userInfo.cpa_order_pass', 1);
            $this->ajaxReturn(array("data" => "", "info" => $msg, "status" => $status));
        } else {
            $this->ajaxReturn(array("data" => "", "info" => "尚未设置订单密码,请先设置密码", "status" => -1));
        }
        die();
    }

}