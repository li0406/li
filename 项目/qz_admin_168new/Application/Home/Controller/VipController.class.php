<?php
// +----------------------------------------------------------------------
// | VipController  VIP会员
// +----------------------------------------------------------------------

namespace Home\Controller;


use Home\Common\Controller\HomeBaseController;
use Home\Model\Db\UserModel;
use Home\Model\Db\YgjAccountLockModel;
use Home\Model\Logic\CompanyConsultLogicModel;
use Home\Model\Logic\CompanyConsultRecordLogicModel;
use Home\Model\Logic\ManageCompanyLogicModel;
use Home\Model\Logic\CompanyExpendLogicModel;
use Library\Org\Page\CPage;
use Think\Exception;

class VipController extends HomeBaseController
{
    protected $viptype = array(
        "0.5" => "0.5倍",
        "1" => "1倍",
        "1.5" => "1.5倍",
        "2" => "2倍",
        "2.5" => "2.5倍",
        "3" => "3倍",
        "3.5" => "3.5倍",
        "4" => "4倍",
        "4.5" => "4.5倍",
        "5" => "5倍",
        "5.5" => "5.5倍",
        "6" => "6倍",
        "6.5" => "6.5倍",
        "7" => "7倍",
        "7.5" => "7.5倍",
        "8" => "8倍",
        "8.5" => "8.5倍",
        "9" => "9倍",
        "9.5" => "9.5倍",
        "10" => "10倍",
    );

    protected $cooperationType = array(
        "1" => "常规",
        "2" => "独家",
        "3" => "垄断"

    );

    protected $cooperateMode = array(
        "1" => "常规会员",
        "2" => "新签返",
        "3" => "常规标杆会员",
        "4" => "新签返标杆会员"
    );

    /**
     * 装修合作来源统计
     * @return [type] [description]
     */
    public function sourcestat()
    {
        //获取所有城市信息
        $cities = D("Quyu")->getAllQuyuOnly();
        //来源组
        $groups = D('OrderSource')->getSourceGroup(1, null, 3, false);

        $model = new CompanyConsultLogicModel();
        $list = $model->getSourceList(I("get.begin"), I("get.end"), I("get.city"), I("get.src"), I("get.group"));
        $this->assign("list", $list);
        $this->assign("citys", $cities);
        $this->assign("groups", $groups);
        $this->display();
    }


    /**
     * 无会员城市统计
     * @return [type] [description]
     */
    public function nomembercity()
    {

        $type = I("get.type", 1);
        if ($type == 2) {
            // 获取从未有过会员城市列表
            $list = D("user")->nevermembercity();
        } else {
            // 获取无真会员城市列表
            $list = D("User")->nomembercity();
        }

        $this->assign("type", $type);
        $this->assign("list", $list);
        $this->display();
    }

    /**
     * 无会员城市详细
     * @return [type] [description]
     */
    public function nomembercitydetails()
    {
        //获取无真会员城市列表
        $list = D("User")->nomembercitydetails(I("get.cs"));
        $this->assign("list", $list);
        $this->display();
    }

    /**
     * vip会员管理
     * @return [type] [description]
     */
    public function vipaccount()
    {
        $citys = getUserCitys();

        //获取管辖城市用户列表
        $list = $this->getVipList($this->city, I('get.'));
        $companyTags = D('Home/Logic/CompanyTagsLogic')->getTags();
        $this->assign("city", $citys);
        $this->assign("list", $list["list"]);
        $this->assign("page", $list["page"]);
        $this->assign("tags", $companyTags);
        $this->assign("viptype", $this->viptype);
        $this->assign("cooperate_mode", $this->cooperateMode);
        $this->assign("status", array(
            "4" => "暂停",
            "8" => "续约",
            "6" => "延期",
            "7" => "退费"
        ));
        $this->display();
    }

    /**
     * 会员操作
     * @return [type] [description]
     */
    public function vipup()
    {
        $id = I("get.id");
        if (!empty($id)) {
            //编辑会员
            //查询会员基础信息
            $model = D('Home/User');
            $info = $this->getVipCompanyInfo($id);

            if ($info['fake'] == 1) {
                $this->error('假会员无法编辑操作');
                die();
            }
            //获取合同信息
            $list = $this->getContractList($id);

            foreach ($list as $k => $v) {
                $advLog = D('Advbanner')->countBannerShowLog($v['start_time'], $v['end_time'], $id);
                $list[$k]['advShowLog'] = $advLog;
            }

            $this->assign("list", $list);
            $this->assign("cooperationType", $this->cooperationType);
            $this->assign("viptype", $this->viptype);
            $this->assign("info", $info);
            $this->display("vipedit");
        } else {
            //新增用户
            $this->assign("viptype", $this->viptype);
            $tmp = $this->fetch("contractNewAllTmp");
            $this->assign("tmp", $tmp);
            $this->display();
        }
    }

    /**
     * 编辑会员的扩展信息
     * @return [type] [description]
     */
    public function extendinfo()
    {
        if ($_POST) {
            $id = I("post.company_id");
            //编辑会员信息
            $result = $this->editCompanyExtendInfo($id);
            $this->ajaxReturn(array("code" => $result["code"], "errmsg" => $result["errmsg"]));
        }
    }

    /**
     * 查询装修公司当前合同
     * @param string $value [description]
     * @return [type]        [description]
     */
    public function findcontract()
    {
        if ($_POST) {
            $id = I("post.id");
            if (I("post.fake") == 0) {
                //不是新加的本次合同
                if (I("post.action") != 1) {
                    //获取最新的合同信息
                    // $user = D('home/logic/User');
                    $result = $this->getNewContractInfo($id);
                    //获取未开始合同的信息
                    $noInfo = $this->getNoStartContractList($id);
                    //加载合同更改项模板
                    foreach ($result["child"] as $key => $value) {
                        switch ($value["type"]) {
                            case '3':
                                $this->assign("viptype", $this->viptype);
                                $this->assign("read", 1);
                                $this->assign("info", $value);
                                $this->assign("index", $key);
                                $this->assign("order", $key + 1);
                                //多倍
                                $tmp = $this->fetch("multiTmp");
                                break;
                            case '4':
                            case '6':
                                $this->assign("read", 1);
                                $this->assign("info", $value);
                                $this->assign("index", $key);
                                $this->assign("order", $key + 1);
                                //暂停
                                $tmp = $this->fetch("pauseTmp");
                                break;
                            case '5':
                                //延期
                                $this->assign("read", 1);
                                $this->assign("info", $value);
                                $this->assign("index", $key);
                                $this->assign("order", $key + 1);
                                $tmp = $this->fetch("delayTmp");
                                break;
                            case '7':
                                //退费
                                $this->assign("read", 1);
                                $this->assign("info", $value);
                                $this->assign("index", $key);
                                $this->assign("order", $key + 1);
                                $tmp = $this->fetch("refundTmp");
                                break;
                            case '9':
                                //退费
                                $this->assign("read", 1);
                                $this->assign("info", $value);
                                $this->assign("index", $key);
                                $this->assign("order", $key + 1);
                                $tmp = $this->fetch("returnTmp");
                                break;
                        }
                        $result["child"][$key]["tmp"] = $tmp;
                    }

                    //获取当前合同广告报备的信息
                    $report = $this->getadvreport($id, $result["now"]["start_time"]);
                }

                //获取当前合同的广告报备
                $this->assign("advInfo", $report);
                $advTmp = $this->fetch("advTmp");

                $this->assign("list", $result);
                $this->assign("cooperationType", $this->cooperationType);
                $this->assign("viptype", $this->viptype);
                $this->assign("mark", count($result) > 0 ? 1 : 0);
                $this->assign("editMark", $result["now"]["editMark"] == 0 ? 0 : 1);
                $this->assign("noInfo", $noInfo);
                $this->assign("advtmp", $advTmp);
                $tmp = $this->fetch("contractTmp");

            } else {
                //获取最新的合同信息
                $result = $this->getVipCompanyInfo($id);
                $this->assign("info", $result);
                $tmp = $this->fetch("contractFakeTmp");
            }
            $this->ajaxReturn(array("tmp" => $tmp));
        }
    }

    /**
     * 编辑VIP用户信息
     * @return [type] [description]
     */
    public function vipadd()
    {
        if ($_POST) {
            //编辑会员合同信息
            $result = $this->editCompanyContract();
            $this->ajaxReturn(array("code" => $result["code"], "errmsg" => $result["errmsg"]));
        }
    }

    /**
     * 查询装修公司信息
     * @return [type] [description]
     */
    public function findcomnapnyinfo()
    {
        if ($_POST) {
            $id = I("post.q");
            $result = $this->getCompanyList($id);
            $this->ajaxReturn(array("items" => $result));
        }
    }

    /**
     * 查询销售信息
     * @param string $value [description]
     * @return [type]        [description]
     */
    public function findsellerinfo()
    {
        if ($_POST) {
            $name = I("post.q");
            $result = $this->getSellerList($name);
            $this->ajaxReturn(array("items" => $result));
        }
    }

    /**
     * 查找会员操作信息
     * @param string $value [description]
     */
    public function getviplog()
    {
        if ($_POST) {
            $result = $this->getVipLogList(I("post."));
            $this->assign("list", $result);
            $tmp = $this->fetch("logTmp");
            $this->ajaxReturn(array("tmp" => $tmp));
        }
    }


    /**
     * 合同预览
     * @return [type] [description]
     */
    public function preview()
    {
        if ($_POST) {
            $result = $this->getPreview(I("post."));
            $this->assign("list", $result);
            $tmp = $this->fetch("previewTmp");
            $this->ajaxReturn(array("tmp" => $tmp));
        }
    }

    /**
     * 获取合同分支模版
     * @param string $value [description]
     * @return [type]        [description]
     */
    public function getcontractchange()
    {
        if ($_POST) {
            $type = I("post.type");
            switch ($type) {
                case '3':
                    //多倍
                    $this->assign("viptype", $this->viptype);
                    $this->assign("index", 'new_' . mt_rand(100, 999));
                    $tmp = $this->fetch("multiTmp");
                    break;
                case '4':
                    //暂停
                    $this->assign("index", 'new_' . mt_rand(100, 999));
                    $tmp = $this->fetch("pauseTmp");
                    break;
                case '5':
                    //延期
                    $this->assign("index", 'new_' . mt_rand(100, 999));
                    $tmp = $this->fetch("delayTmp");
                    break;
                case '7':
                    //退费
                    $this->assign("index", 'new_' . mt_rand(100, 999));
                    $tmp = $this->fetch("refundTmp");
                    break;
                case '9':
                    //转让VIP
                    $this->assign("index", 'new_' . mt_rand(100, 999));
                    $tmp = $this->fetch("returnTmp");
                    break;
            }
            $this->ajaxReturn(array("tmp" => $tmp));
        }
    }

    /**
     * 多倍会员操作
     * @param string $value [description]
     * @return [type]        [description]
     */
    public function multi()
    {
        if ($_POST) {
            //多倍会员合同操作
            $result = $this->vipMulti();
            $this->ajaxReturn(array("code" => $result["code"], "errmsg" => $result["errmsg"]));
        }
    }

    /**
     * 暂停会员操作
     * @return [type] [description]
     */
    public function pause()
    {
        if ($_POST) {
            //暂停会员合同操作
            $result = $this->vipPause();
            $this->ajaxReturn(array("code" => $result["code"], "errmsg" => $result["errmsg"]));
        }
    }

    /**
     * 退费操作
     * @return [type] [description]
     */
    public function refund()
    {
        if ($_POST) {
            //退费会员合同操作
            $result = $this->vipRefund();
            $this->ajaxReturn(array("code" => $result["code"], "errmsg" => $result["errmsg"]));
        }
    }

    /**
     * 转让VIP操作
     * @return [type] [description]
     */
    public function returnvip()
    {
        if ($_POST) {
            $result = $this->vipReturn();
            $this->ajaxReturn(array("code" => $result["code"], "errmsg" => $result["errmsg"]));
        }
    }

    /**
     * 编辑会员操作
     * @return [type] [description]
     */
    public function editvip()
    {
        if ($_POST) {
            $result = $this->setEditvip(I("post."));
            $this->ajaxReturn(array("code" => $result["code"], "errmsg" => $result["errmsg"]));
        }
    }

    /**
     * 编辑多倍操作
     * @param string $value [description]
     * @return [type]        [description]
     */
    public function editmulti($value = '')
    {
        if ($_POST) {
            $result = $this->setEditMulti();
            $this->ajaxReturn(array("code" => $result["code"], "errmsg" => $result["errmsg"]));
        }
    }

    /**
     * 编辑暂停操作
     * @return [type] [description]
     */
    public function editpause()
    {
        if ($_POST) {
            $result = $this->setEditPause();
            $this->ajaxReturn(array("code" => $result["code"], "errmsg" => $result["errmsg"]));
        }
    }

    /**
     * 编辑广告报备
     * @return [type] [description]
     */
    public function editadvreport()
    {
        if ($_POST) {
            $result = $this->setEditadvreport(I("post."));
            $this->ajaxReturn(array("code" => $result["code"], "errmsg" => $result["errmsg"]));
        }
    }

    /**
     * 获取本次合同模板
     * @return [type] [description]
     */
    public function getnewcontracttmp()
    {
        $this->assign("cooperationType", $this->cooperationType);
        $this->assign("viptype", $this->viptype);
        $tmp = $this->fetch("contractNewTmp");
        $this->ajaxReturn(array("tmp" => $tmp));
    }

    /**
     * 添加本次合同操作
     * @return [type] [description]
     */
    public function addnewcontract()
    {
        if ($_POST) {
            $result = $this->editCompanyNewContract();
            $this->ajaxReturn(array("code" => $result["code"], "errmsg" => $result["errmsg"]));
        }
    }

    /**
     * 获取总合同模板
     * @param string $value [description]
     * @return [type]        [description]
     */
    public function getcontractall()
    {

        $advTmp = $this->fetch("advTmp");
        $this->assign("cooperationType", $this->cooperationType);
        $this->assign("viptype", $this->viptype);
        $this->assign("advTmp", $advTmp);
        $tmp = $this->fetch("contractNewAllTmp");
        $this->ajaxReturn(array("tmp" => $tmp));
    }

    /**
     * 编辑假会员信息
     * @param string $value [description]
     * @return [type]        [description]
     */
    public function vipfake()
    {
        if ($_POST) {
            //编辑会员合同信息
            $result = $this->editFakeCompanyContract();
            $this->ajaxReturn(array("code" => $result["code"], "errmsg" => $result["errmsg"]));
        }
    }

    /**
     * 延期会员操作
     * @return [type] [description]
     */
    public function delay()
    {
        if ($_POST) {
            //暂停会员合同操作
            $result = $this->vipDelay();
            $this->ajaxReturn(array("code" => $result["code"], "errmsg" => $result["errmsg"]));
        }
    }

    /**
     * 上会员统计
     * @param string $value [description]
     * @return [type]        [description]
     */
    public function vipstatistics($value = '')
    {
        if (I("get.begin") !== "") {
            $begin = I("get.begin");
        }

        if (I("get.end") !== "") {
            $end = I("get.end");
        }

        if (I("get.city") !== "") {
            $cs = I("get.city");
        }

        //获取所有城市信息
        $citys = D("Quyu")->getAllQuyuOnly();
        $this->assign("citys", $citys);
        $list = $this->getVipStatiitics($begin, $end, $cs);
        $this->assign("list", $list);
        $this->display();
    }

    /**
     * 转移会员公司城市
     */
    public function moveMemberCity()
    {
        if (IS_POST) {
            //获取参数
            $id = intval(I('post.id'));
            $target_city_id = I('post.target_city_id');
            $target_area_id = I('post.target_area_id');
            $remark = I('post.remark');
            if (empty($id) || empty($target_city_id) || empty($target_area_id) || empty($remark)) {
                $this->ajaxReturn(array('status' => 0, 'info' => '必要参数不能为空！'));
            }

            //获取装修公司
            $company = D('User')->getCompanyInfoById($id);
            if ($company['cs'] == $target_city_id) {
                $this->ajaxReturn(array('status' => 0, 'info' => '目标城市不能和原城市一致！'));
            }

            //日志info字段
            $logInfo = "ID: " . $id . "\n原城市: " . $company['cs'] . "\n原城市区县: " . $company['qx'] . "\n新城市: " . $target_city_id . "\n新城市区县: " . $target_area_id . " \n备注:" . $remark . "\n";

            //帐号
            $save = array(
                'cs' => $target_city_id,
                'qx' => $target_area_id
            );
            $result = M('user')->where(array('id' => $id))->save($save);

            //设计师
            $designer = M('team')->field("GROUP_CONCAT(userid) AS userids")
                ->where(array('comid' => $id))
                ->find();
            $designer = $designer['userids'];
            if (!empty($designer)) {
                $save = array(
                    'cs' => $target_city_id,
                    'qx' => $target_area_id
                );
                $result = M('user')->where(array('id' => array('IN', $designer)))->save($save);
                $logInfo .= '处理设计师帐号:' . $result . "个\n";
            }

            //案例
            $save = array(
                'cs' => $target_city_id,
                'qx' => $target_area_id
            );
            $result = M('cases')->where(array('uid' => $id))->save($save);
            $logInfo .= '处理案例:' . $result . "个\n";

            //文章资讯
            $save = array(
                'cs' => $target_city_id
            );
            $result = M('info')->where(array('uid' => $id))->save($save);
            $logInfo .= '处理文章:' . $result . "个\n";

            //评价
            $save = array(
                'cs' => $target_city_id
            );
            $result = M('comment')->where(array('comid' => $id))->save($save);
            $logInfo .= '处理业主评价:' . $result . "个\n";

            //轮显广告
            $save = array(
                'city_id' => $target_city_id
            );
            $result = M('adv')->where(array('comid' => $id))->save($save);
            $logInfo .= '处理轮显广告:' . $result . "个\n";

            //通栏广告
            $save = array(
                'city_id' => $target_city_id
            );
            $result = M('bigadv')->where(array('comid' => $id))->save($save);
            $logInfo .= '处理通栏广告:' . $result . "个\n";

            //logo广告
            $save = array(
                'city_id' => $target_city_id
            );
            $result = M('advs')->where(array('company_id' => $id))->save($save);
            $logInfo .= '处理logo广告:' . $result . "个\n";

            //新版首页广告
            $save = array(
                'city_id' => $target_city_id
            );
            $result = M('adv_banner')->where(array('company_id' => $id))->save($save);
            $logInfo .= '处理新版首页广告:' . $result . "个\n";

            //新版首页设计师
            $save = array(
                'city_id' => $target_city_id
            );
            $result = M('adv_designer')->where(array('company_id' => $id))->save($save);
            $logInfo .= '处理新版首页设计师:' . $result . "个\n";

            //打日志
            $save = array(
                'operate_member_id' => $id,
                'operate_adminuser_id' => getAdminUser('id'),
                'origin_city_id' => $company['cs'],
                'origin_area_id' => $company['qx'],
                'target_city_id' => $target_city_id,
                'target_area_id' => $target_area_id,
                'remark' => $remark,
                'info' => $logInfo,
                'add_time' => date('Y-m-d H:i:s')
            );
            D('LogMoveMemberCity')->addLogMoveMemberCity($save);

            $this->ajaxReturn(array('status' => 1, 'info' => $logInfo));
        }

        $vars['cityAndArea'] = json_encode(D("Quyu")->getQuyuAndAreaForCxSelect());
        $vars['log'] = D('LogMoveMemberCity')->getLogMoveMemberCityList();
        $this->assign('vars', $vars);
        $this->display();
    }

    /**
     * 更加公司ID获取公司信息
     */
    public function getMemberById()
    {
        $id = I('get.id');
        if (empty($id)) {
            $this->ajaxReturn(array('status' => 0, 'info' => '非法请求'));
        }
        $data = D('User')->getCompanyInfoById($id);
        if (empty($data)) {
            $this->ajaxReturn(array('status' => 0, 'info' => '装修公司不存在或已被删除'));
        }
        $this->ajaxReturn(array('status' => 1, 'data' => $data));
    }

    /**
     * 初始化帐号信息
     * @param string $value [description]
     * @return [type]        [description]
     */
    public function initaccount()
    {
        $this->display();
    }

    /**
     * 初始化帐号密码
     * @return [type] [description]
     */
    public function initpass()
    {
        if ($_POST) {
            $id = I("post.id");
            if (empty($id)) {
                $this->ajaxReturn(array('status' => 0, 'info' => "操作失败,请输入重置条件！"));
            }
            $data = array(
                "pass" => md5('qz123hy456'),//默认初始密码qz123hy456
            );
            $i = D("User")->editCompanyInfo($id, $data);
            if ($i !== false) {
                //添加操作日志
                $log = array(
                    'remark' => I("post.remark"),
                    'logtype' => 'initpass',
                    'action_id' => $id,
                    'info' => json_encode($data)
                );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(array('status' => 1, 'info' => "会员密码重置成功"));
            }
            $this->ajaxReturn(array('status' => 0, 'info' => "操作失败,请输入重置条件！"));
        }
    }

    /**
     * 重置订单查看密码
     * @return [type] [description]
     */
    public function initorderpass()
    {
        if ($_POST) {
            $id = I("post.id");
            if (empty($id)) {
                $this->ajaxReturn(array('status' => 0, 'info' => "操作失败,请输入重置条件！"));
            }
            $data = array(
                "pass" => md5('qz123456'),//默认123456
            );
            $i = D("User")->editOrderPass($id, $data);
            if ($i !== false) {
                //添加操作日志
                $log = array(
                    'remark' => I("post.remark"),
                    'logtype' => 'initorderpass',
                    'action_id' => $id,
                    'info' => json_encode($data)
                );
                D('LogAdmin')->addLog($log);

                //同步将APP端的TOKEN状态改成不可用
                $model = new YgjAccountLockModel();
                $data = [
                    "status" => 2
                ];
                $model->updateAccount($id,$data);

                $this->ajaxReturn(array('status' => 1, 'info' => "订单查看密码重置成功"));
            }
            $this->ajaxReturn(array('status' => 0, 'info' => "操作失败,请输入重置条件！"));
        }
    }

    /**
     * 重置安全手机/邮箱
     * @return [type] [description]
     */
    public function initsafeaccount()
    {
        if ($_POST) {
            $id = I("post.id");
            if (empty($id)) {
                $this->ajaxReturn(array('status' => 0, 'info' => "操作失败,请输入重置条件！"));
            }

            $type = I("post.type");
            switch ($type) {
                case '1':
                    $data = array(
                        "tel_safe" => "",
                        "tel_safe_chk" => 0
                    );
                    break;
                case '2':
                    $data = array(
                        "mail_safe" => "",
                        "mail_safe_chk" => 0
                    );
                    break;
                default:
                    $data = array(
                        "tel_safe" => "",
                        "tel_safe_chk" => 0,
                        "mail_safe" => "",
                        "mail_safe_chk" => 0
                    );
                    break;
            }

            $i = D("User")->editCompanyInfo($id, $data);
            if ($i !== false) {
                //添加操作日志
                $log = array(
                    'remark' => I("post.remark"),
                    'logtype' => 'initsafecompany',
                    'action_id' => $id,
                    'info' => json_encode($data)
                );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(array('status' => 1, 'info' => "安全信息重置成功"));
            }
            $this->ajaxReturn(array('status' => 0, 'info' => "操作失败,请输入重置条件！"));
        }
    }

    public function companyaccount($value = '')
    {
        $this->display();
    }

    public function initCompanyAccount()
    {
        if ($_POST) {
            $id = I("post.id");
            if (empty($id)) {
                $this->ajaxReturn(array('status' => 0, 'info' => "操作失败,请输入重置条件！"));
            }

            //检查修改的帐号是否可用
            $count = D("User")->findCompanyAccountByName(I("post.account"));
            if ($count > 0) {
                $this->ajaxReturn(array('status' => 0, 'info' => "该帐号已被使用,请重新提交！"));
            }

            $data = array(
                "user" => I("post.account")
            );

            $i = D("User")->editCompanyInfo($id, $data);
            if ($i !== false) {
                //添加操作日志
                $log = array(
                    'remark' => I("post.remark"),
                    'logtype' => 'inituseruser',
                    'action_id' => $id,
                    'info' => json_encode($data)
                );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(array('status' => 1, 'info' => "会员帐号重置成功"));
            }
            $this->ajaxReturn(array('status' => 0, 'info' => "操作失败,请输入重置条件！"));
        }
    }

    public function resetcompanyinfo()
    {
        if ($_POST) {
            $id = I("post.id");
            $del_type = I("post.type");//删除类型
            if (empty($id)) {
                $this->ajaxReturn(array('status' => 0, 'info' => "操作失败,请输入重置条件！"));
            }

            //检查修改的帐号是否可用
            $user = D("User")->findCompanyInfo($id);
            if (count($user) == 0) {
                $this->ajaxReturn(array('status' => 0, 'info' => "该帐号不存在,请重新提交！"));
            }

            //获取分公司信息
            $data = array(
                'id' => $user['id'],
                'on' => $user['on'],
                'classid' => $user['classid'],
                'user' => $user['user'],
                'pass' => $user['pass'],
                'cs' => $user['cs'],
                'qx' => $user['qx'],
                'logo' => 'https://staticqn.qizuang.com/file/default/default_logo.png',
                'register_time' => $user['register_time'],
                'ip' => $user['ip'],
                'user_type' => $user['user_type'],
                'register_admin_id' => $user['register_admin_id'],
                'uv' => $user['uv'], //咨询量
                'pj' => $user['pj'], //评论数
                'uptime' => $user['uptime'], //案例更新时间
                'case_count' => $user['case_count'] //案例数量，历史最多的案例
            );

            //查询装修公司扩展信息
            $usercompany = $user = D("User")->findCompanyExpandInfo($id);
            $subData = array(
                'id' => $usercompany['id'],
                'userid' => $usercompany['userid'],
                'viptype' => $usercompany['viptype'],
                'chengli' => $usercompany['chengli'],
                'fuwu' => $usercompany['fuwu'],
                'fengge' => $usercompany['fengge'],
                'quyu' => $usercompany['quyu'],
                'time' => $usercompany['time'],
                'fake' => $usercompany['fake'],
                'team_count' => $usercompany['team_count'],
                'comment_score' => $usercompany['comment_score'],
                'comment_count' => $usercompany['comment_count'],
            );

            $userModel = D("User");

            switch ($del_type) {
                case 1:
                    //删除案例
                    $userModel->delCompanyCase($id);
                    break;
                case 2:
                    //删除评论
                    $userModel->delCompanyComment($id);
                    break;
                default:
                    //删除全部
                    $i = $userModel->delCompany($id);
                    if ($i !== false) {
                        //删除扩展信息
                        $userModel->delCompanyExpand($id);
                        //删除案例
                        $userModel->delCompanyCase($id);
                        //删除评论
                        $userModel->delCompanyComment($id);
                        //删除分店信息
                        $userModel->delCompanyBranchstore($id);
                        // //添加数据
                        $userModel->addCompany($data);
                        $userModel->addCompanyExpand($subData);
                    }
            }
            //添加操作日志
            $log = array(
                'remark' => I("post.remark"),
                'logtype' => 'resetuserinfo',
                'action_id' => $id,
                'info' => $_POST
            );
            D('LogAdmin')->addLog($log);
            $this->ajaxReturn(array('status' => 1, 'info' => "清空信息操作成功"));
        }
    }

    /**
     * 保存公司标签
     */
    public function saveTags()
    {
        D("Home/Logic/CompanyTagsLogic")->saveCompanyTags(I('post.'));
        $this->ajaxReturn(['status' => 1, 'info' => '添加成功']);
    }

    /**
     * 保存公司标签
     */
    public function getTags()
    {
        $data = D("Home/Logic/CompanyTagsLogic")->getCompanyTags(I('get.'));
        if ($data) {
            $this->ajaxReturn(['status' => 1, 'info' => array_column($data, 'tag')]);
        } else {
            $this->ajaxReturn(['status' => 0, 'info' => '获取失败!']);
        }
    }

    /**
     * 撤销VIP
     * @return [type] [description]
     */
    public function vipReset()
    {
        if ($_POST) {
            $id = I("post.id");
            if (empty($id)) {
                $this->ajaxReturn(array('status' => 0, 'info' => "操作失败,请输入重置条件！"));
            }
            $data = array(
                'on' => '0',
                'start' => '',
                'end' => ''
            );
            $i = D("User")->editCompanyInfo($id, $data);
            if ($i !== false) {
                //添加操作日志
                $log = array(
                    'remark' => I("post.remark"),
                    'logtype' => 'vipcompanyreset',
                    'action_id' => $id
                );
                D('LogAdmin')->addLog($log);
                $this->ajaxReturn(array('status' => 1, 'info' => "VIP重置成功"));
            }
            $this->ajaxReturn(array('status' => 0, 'info' => "操作失败,请输入重置条件！"));
        }
    }

    /**
     * 获取VIP用户列表
     * @param  [type] $cs   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function getVipList($cs, $param)
    {
        if (count($cs) == 0 && session("uc_userinfo.uid") != 1) {
            return false;
        }
        import('Library.Org.Util.Page');
        //正常的查询列表
        if (I("get.status") == "") {
            $model = D('Home/User');
            $count = $model->getVipListCount($cs, $param);
            if ($count > 0) {
                $p = new \Page($count, 20);
                $p->setConfig('prev', "上一页");
                $p->setConfig('next', '下一页');
                $show = $p->show();
                $list = $model->getVipList($cs, $param, $p->firstRow, $p->listRows);
            }

        } else {
            $model = D('Home/UserVip');
            $count = $model->getVipListCount($cs, $param);
            if ($count > 0) {
                $p = new \Page($count, 20);
                $p->setConfig('prev', "上一页");
                $p->setConfig('next', '下一页');
                $show = $p->show();
                $list = $model->getVipList($cs, $param, $p->firstRow, $p->listRows);
            }
        }
        return array("page" => $show, "list" => $list);
    }

    /**
     * 获取合同详细列表
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function getVipLogList($data)
    {
        $vip = D('Home/UserVip');
        $result = $vip->getVipLog($data["id"], $data["contract_type"]);
        foreach ($result as $key => $value) {
            $list["company_name"] = $value["company_name"];
            if (in_array($value["type"], array(2, 8)) && !array_key_exists($value["id"], $list)) {
                $list["child"][$value["id"]]["start_time"] = $value["start_time"];
                $list["child"][$value["id"]]["end_time"] = $value["end_time"];
                $list["child"][$value["id"]]["op_uname"] = $value["op_uname"];
                $list["child"][$value["id"]]["type"] = $value["type"];
            } else {
                $list["child"][$value["parentid"]]["child"][$value["id"]]["type"] = $value['type'];
                $list["child"][$value["parentid"]]["child"][$value["id"]]["start_time"] = $value["start_time"];
                $list["child"][$value["parentid"]]["child"][$value["id"]]["end_time"] = $value["end_time"];
                $list["child"][$value["parentid"]]["child"][$value["id"]]["delay_time"] = $value["delay_time"];
                $list["child"][$value["parentid"]]["child"][$value["id"]]["delay_day"] = $value["delay_day"];
                $list["child"][$value["parentid"]]["child"][$value["id"]]["to_name"] = $value["to_name"];
                $list["child"][$value["parentid"]]["child"][$value["id"]]["to_company"] = $value["to_company"];
                $list["child"][$value["parentid"]]["child"][$value["id"]]["op_uname"] = $value["op_uname"];
            }
        }
        return $list;
    }

    /**
     * 获取装修公司信息列表
     * @param string $value [description]
     * @return [type]        [description]
     */
    private function getCompanyList($id)
    {
        $model = D('Home/User');
        $info = $model->getCompanyList($id, [3,6]);
        foreach ($info as $key => $value) {
            $info[$key]["mark"] = 0;
            if ($value["end"] >= date("Y-m-d") && $value["fake"] == 0) {
                $info[$key]["mark"] = 1;
            }
        }
        return $info;
    }

    /**
     * 查询销售信息
     * @return [type] [description]
     */
    private function getSellerList($name)
    {
        if (empty($name)) {
            return false;
        }
        $model = D('Home/Adminuser');
        return $model->getSellerList($name);
    }

    /**
     * 编辑用户扩展信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function editCompanyExtendInfo($id)
    {
        if (empty($id)) {
            return array("code" => "404", "errmsg" => "请先选择会员公司！");
        }
        //查询原公司的状态信息
        $userModel = D('Home/User');
        //查询本次会员信息
        $info = $userModel->findCompanyInfo($id, $this->city);

        if (I("post.fake") == 1 && ($info["fake"] == 0 && $info["on"] <> 0)) {
            return array("code" => "404", "errmsg" => "该会员有过VIP记录，不能操作为假会员！");
        }

        $data = array(
            "jd_tel_1" => I("post.jd_tel1"),
            "jd_tel_name_1" => I("post.jd_name1"),
            "jd_tel_2" => I("post.jd_tel2"),
            "jd_tel_name_2" => I("post.jd_name2"),
            "saler" => I("post.saler_name") == "" ? "" : I("post.saler_name"),
            "saler_id" => I("post.saler_id") == "" ? "" : I("post.saler_id"),
            "fake" => I("post.fake")
        );

        if (!empty($data["jd_tel_1"])) {
            if (!validate_mobile($data["jd_tel_1"])) {
                return array("code" => "404", "errmsg" => "请输入正确的手机号码！");
            }
        }

        if (!empty($data["jd_tel_2"])) {
            if (!validate_mobile($data["jd_tel_2"])) {
                return array("code" => "404", "errmsg" => "请输入正确的手机号码！");
            }
        }

        $model = D('Home/UserCompany');
        $result = $model->editCompanyExtendInfo($id, $data);

        $code = 404;
        $msg = '编辑失败,请稍后再试！';
        if ($result !== false) {
            $code = 200;
            $msg = '';

            //同步修改接单报备表的接单人信息
            //查询报备信息
            $jdbb = $userModel->getJdbb($id);
            $data = array(
                "jdbb_time" => time(),
                "receive_order_tel1" => $data["jd_tel_1"],
                "receive_order_tel2" => $data["jd_tel_2"],
                "receive_order_tel1_remark" => $data["jd_tel_name_1"],
                "receive_order_tel2_remark" => $data["jd_tel_name_2"]
            );
            if (count($jdbb) == 0) {
                $data["jdbb_comid"] = $id;
                $userModel->setJdbb($data);
            } else {
                $userModel->upJdbb($id, $data);
            }
        }
        return array("code" => $code, "errmsg" => $msg);
    }

    /**
     * 获取装修公司当前的合同
     * @return [type] [description]
     */
    private function getNewContractInfo($id)
    {
        //获取最新的合同信息
        $vip = D('Home/UserVip');
        $result = $vip->getNewContractInfo($id);

        if (count($result) > 0) {
            foreach ($result as $key => $value) {
                if (!array_key_exists("all", $list)) {
                    $list["all"]["id"] = $value["id"];
                    $list["all"]["start_time"] = $value["start_time"];
                    $list["all"]["end_time"] = $value["end_time"];
                    $list["all"]["cooperate_mode"] = $value["cooperate_mode"];
                    $list["all"]["day"] = (strtotime($value["end_time"]) - strtotime($value["start_time"])) / 86400 + 1;
                }

                if (!array_key_exists("now", $list) && !empty($value["bid"])) {
                    $list["now"]["id"] = $value["bid"];
                    $list["now"]["start_time"] = $value["b_start"];
                    $list["now"]["end_time"] = $value["b_end"];
                    $list["now"]["viptype"] = $value["viptype"];
                    $list["now"]["cooperation_type"] = $value["cooperation_type"];
                    $list["now"]["day"] = (strtotime($value["b_end"]) - strtotime($value["b_start"])) / 86400 + 1;
                    $list["now"]["editMark"] = 1;
                    if ($list["now"]["end_time"] < date("Y-m-d")) {
                        $list["now"]["editMark"] = 0;
                    }
                }

                if (!empty($value["cid"])) {
                    $sub = array(
                        "start_time" => $value["c_start"],
                        "end_time" => $value["c_end"] == "0000-00-00" ? "" : $value["c_end"],
                        "type" => $value["c_type"],
                        "viptype" => $value["c_viptype"],
                        "delay_day" => $value["delay_day"],
                        "delay_time" => $value["delay_time"],

                    );
                    if ($value["c_end"] != "0000-00-00") {
                        $sub["day"] = (strtotime($value["c_end"]) - strtotime($value["c_start"])) / 86400 + 1;
                    }

                    if (in_array($value["c_type"], array(7, 9))) {
                        $list["now"]["editMark"] = 0;
                    }

                    $list["child"][] = $sub;
                }
            }
        }
        return $list;
    }

    /**
     * 获取未开始合同
     * @return [type] [description]
     */
    private function getNoStartContractList($id)
    {
        $vip = D('Home/UserVip');
        $result = $vip->getNoStartContractList($id);
        return $result;
    }

    /**
     * 获取当前合同的广告报备
     * @param  [type] $company_id [公司ID]
     * @param  [type] $start_time [本次合同开始时间]
     * @return [type]             [description]
     */
    private function getadvreport($company_id, $start_time)
    {
        $vip = D('Home/AdvertisingReport');
        $result = $vip->getNowAdvReport($company_id, $start_time);
        foreach ($result as $key => $value) {
            $list[$value["type"]][$value["location"]]["total"] = $value["total"];
            $list[$value["type"]][$value["location"]]["use_day"] = $value["use_day"];
            if (in_array($value["location"], array(3, 4))) {
                $list["flag"] = 1;
            }
        }
        return $list;
    }

    /*  获取装修公司信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getVipCompanyInfo($id)
    {
        //获取基础信息
        $user = D('Home/user');
        $info = $user->findCompanyInfo($id, $this->city);
        if (count($info) > 0) {
            return $info;
        }
    }


    /**
     * 编辑VIP用户的合同
     * @return [type] [description]
     */
    private function editCompanyContract()
    {
        $id = I("post.company_id");

        $vip = D('Home/UserVip');
        $model = D('Home/UserCompany');

        if (I("post.allbegin") > I("post.allend")) {
            return array("code" => "404", "errmsg" => "总合同开始时间不能小于结束时间");
        }

        if (I("post.begin") > I("post.end")) {
            return array("code" => "404", "errmsg" => "本次合同开始时间不能小于结束时间");
        }

        if (I("post.begin") < I("post.allbegin")) {
            return array("code" => "404", "errmsg" => "本次合同开始时间不能小于总合同开始时间");
        }

        if (I("post.allend") < I("post.end")) {
            return array("code" => "404", "errmsg" => "本次合同结束时间不能大于总合同的结束时间");
        }

        if (I("post.cooperate_mode") == "") {
            return array("code" => "404", "errmsg" => "请先选择合作模式");
        }

        $vipType = I("post.viptype");

        if (I("post.cooperationType") == 2) {
            $vipType = 1;
        }

        if ($vipType == "") {
            return array("code" => "404", "errmsg" => "请选择几倍会员！");
        }

        //获取最后的一份总合同
        // $user = $vip->getLastAllContract($id);

        //查询最新的一份本次合同信息
        $nowContract = $vip->getLastNewContract($id);

        //如果最新的合同未开始，则提交的合同覆盖最新的合同
        if (count($nowContract) > 0) {
            //最新的合同还未开始
            if ($nowContract["start_time"] <= date("Y-m-d")) {
                //如果存在上一份合同，判断新合同开始时间是否大于旧合同的结束时间
                if ($nowContract["end_time"] >= I("post.begin")) {
                    return array("code" => "404", "errmsg" => "本次合同开始时间不能小于上一份合同的结束时间");
                }
            } else if (I("post.erci_confirm") != "1") {
                return array("code" => "405", "errmsg" => "该会员已有预合同，确定覆盖上一份合同吗？");
            }
        }

        $userModel = D('Home/User');
        $advReport = D('Home/AdvertisingReport');
        //查询本次会员信息
        $info = $userModel->findCompanyInfo($id, $this->city);

        //如果最新的合同未开始，则删除最新的合同
        if ($nowContract["start_time"] > date("Y-m-d")) {
            //删除未开始的本次合同
            $vip->delContract($nowContract["id"]);
            //删除和本次合同相关的广告报备信息
            $advReport->delReport($id, $nowContract["start_time"]);
            //如果是总合同也是未开始的，则删除总合同
            $allContract = $vip->getContractInfo($nowContract["parentid"]);
            if ($allContract["start_time"] > date("Y-m-d")) {
                //删除总合同
                $vip->delContract($allContract["id"]);
            }
        }

        //添加VIP用户记录
        //总合同添加
        $data = array(
            "company_id" => $id,
            "company_name" => I("post.company_name"),
            "cooperate_mode" => I("post.cooperate_mode"),
            "type" => 1,
            "contract_type" => 3,
            "start_time" => I("post.allbegin"),
            "end_time" => I("post.allend"),
            "saler_id" => I("post.saler_id") == "" ? "" : I("post.saler_id"),
            "saler_name" => I("post.saler_name") == "" ? "" : I("post.saler_name"),
            "op_uid" => session("uc_userinfo.id"),
            "op_uname" => session("uc_userinfo.name"),
            "time" => time()
        );

        $vipid = $vip->addVip($data);

        if ($vipid !== false) {
            $type = 2;
            if (I("post.isnew") == 1) {
                $type = 8;
            }

            //添加本次合同时间
            $data = array(
                "company_id" => $id,
                "company_name" => I("post.company_name"),
                "cooperate_mode" => I("post.cooperate_mode"),
                "type" => $type,
                "contract_type" => 3,
                "start_time" => I("post.begin"),
                "end_time" => I("post.end"),
                "op_uid" => session("uc_userinfo.id"),
                "op_uname" => session("uc_userinfo.name"),
                "saler_id" => I("post.saler_id") == "" ? "" : I("post.saler_id"),
                "saler_name" => I("post.saler_name") == "" ? "" : I("post.saler_name"),
                "viptype" => $vipType,
                "cooperation_type" => I("post.cooperationType"),
                "parentid" => $vipid,
                "time" => time()
            );

            $result = $vip->addVip($data);
            $code = 404;
            $msg = '编辑失败,请稍后再试！';

            if ($result !== false) {
                $code = 200;
                $msg = '';

                //添加操作日志
                $log = array(
                    'remark' => '添加会员【' . $id . '】总合同和本次合同',
                    'logtype' => 'addcompanycontract',
                    'action_id' => $id,
                    'info' => $data
                );
                D('LogAdmin')->addLog($log);

                //如果是新的本次合同，则修改用户的VIP信息
                //最新的合同还未开始，新的合同是当天怎修改会员信息
                if (empty($nowContract["end_time"]) || $nowContract["end_time"] < date("Y-m-d") || ($nowContract["start_time"] > date("Y-m-d") && I("post.begin") == date("Y-m-d"))) {
                    //编辑会员总合同信息
                    $data = array(
                        "contract_start" => strtotime(I("post.allbegin")),
                        "contract_end" => strtotime(I("post.allend")),
                        "viptype" => $vipType,
                        "cooperation_type" => I("post.cooperationType"),
                        "cooperate_mode" => I("post.cooperate_mode"),
                    );

                    $model->editCompanyExtendInfo($id, $data);

                    //添加日志
                    $log = D('Home/LogVip');
                    $data = array(
                        "comid" => $id,
                        "old_state" => $info["on"],
                        "new_state" => 2,
                        "opid" => session("uc_userinfo.id"),
                        "optime" => time(),
                        "operator" => session("uc_userinfo.name"),
                        "start" => I("post.begin"),
                        "end" => I("post.end"),
                        "contract_start" => strtotime(I("post.allbegin")),
                        "contract_end" => strtotime(I("post.allend")),
                        "viptype" => $vipType
                    );
                    $log->addLog($data);

                    //如果合同时间是当天,更新会员状态
                    //如果合同开始时间是第二天并且上会员是在当天12点以后，更新会员状态
                    $time = strtotime(date("Y-m-d"));
                    if (I("post.begin") <= date("Y-m-d") || (I("post.begin") == date("Y-m-d", strtotime("+1 day", $time)) && date("G") >= 12)) {
                        $data = array(
                            "start" => I("post.begin"),
                            "end" => I("post.end"),
                        );

                        if (I("post.end") >= date("Y-m-d")) {
                            $data["classid"] = 3;
                            $data["on"] = 2;
                        }

                        $result = $userModel->editCompanyInfo($id, $data);
                    }
                }

                //添加轮显广告报备
                $data = array(
                    "comid" => $id,
                    "type" => 1,
                    "total" => I("post.lunbo"),
                    "start" => I("post.begin"),
                    "end" => I("post.end"),
                    "uid" => session("uc_userinfo.id"),
                    "uname" => session("uc_userinfo.name"),
                    "time" => time()
                );
                $advReport->addReport($data);

                //添加通栏A广告报备
                $data = array(
                    "comid" => $id,
                    "type" => 2,
                    "location" => 1,
                    "total" => I("post.tl_A"),
                    "start" => I("post.begin"),
                    "end" => I("post.end"),
                    "uid" => session("uc_userinfo.id"),
                    "uname" => session("uc_userinfo.name"),
                    "time" => time()
                );
                $advReport->addReport($data);

                //添加通栏B广告报备
                $data = array(
                    "comid" => $id,
                    "type" => 2,
                    "location" => 2,
                    "total" => I("post.tl_B"),
                    "start" => I("post.begin"),
                    "end" => I("post.end"),
                    "uid" => session("uc_userinfo.id"),
                    "uname" => session("uc_userinfo.name"),
                    "time" => time()
                );
                $advReport->addReport($data);

                //添加通栏C广告报备
                $data = array(
                    "comid" => $id,
                    "type" => 2,
                    "location" => 3,
                    "total" => I("post.tl_C"),
                    "start" => I("post.begin"),
                    "end" => I("post.end"),
                    "uid" => session("uc_userinfo.id"),
                    "uname" => session("uc_userinfo.name"),
                    "time" => time()
                );
                $advReport->addReport($data);

                //老站添加通栏D
                if (I("post.type") == 1) {

                    //添加通栏D广告报备
                    $data = array(
                        "comid" => $id,
                        "type" => 2,
                        "location" => 101,
                        "total" => I("post.tl_D"),
                        "start" => I("post.begin"),
                        "end" => I("post.end"),
                        "uid" => session("uc_userinfo.id"),
                        "uname" => session("uc_userinfo.name"),
                        "time" => time()
                    );
                    $advReport->addReport($data);
                }
            }
        }

        return array("code" => $code, "errmsg" => $msg);
    }

    /**
     * 多倍会员处理
     * @return [type] [description]
     */
    private function vipMulti()
    {
        $vip = D('Home/UserVip');
        //获取本次合同的详细信息
        $info = $vip->getContractInfo(I("post.parentid"));

        if (I("post.start") == "") {
            return array("code" => "404", "errmsg" => "请选择多倍会员开始时间");
        }

        if (I("post.end") == "") {
            return array("code" => "404", "errmsg" => "请选择多倍会员结束时间");
        }

        if ($info["start_time"] > I("post.start")) {
            return array("code" => "404", "errmsg" => "多倍开始时间要大于本次合同开始时间");
        }

        if ($info["end_time"] < I("post.end")) {
            return array("code" => "404", "errmsg" => "多倍结束时间要小于本次合同结束时间");
        }

        if (I("post.viptype") == "") {
            return array("code" => "404", "errmsg" => "请选择多倍条件");
        }

        $data = array(
            "company_id" => I("post.company_id"),
            "company_name" => I("post.company_name"),
            "type" => 3,
            "contract_type" => $info["contract_type"],
            "cooperate_mode" => $info["cooperate_mode"],
            "start_time" => I("post.start"),
            "end_time" => I("post.end"),
            "viptype" => I("post.viptype"),
            "saler_id" => I("post.saler_id") == "" ? "" : I("post.saler_id"),
            "saler_name" => I("post.saler_name") == "" ? "" : I("post.saler_name"),
            "parentid" => I("post.parentid"),
            "op_uid" => session("uc_userinfo.id"),
            "op_uname" => session("uc_userinfo.name"),
            "time" => time(),
        );
        $vip = D('Home/UserVip');
        $vipid = $vip->addVip($data);
        $code = 404;
        $errmsg = '编辑失败,请稍后再试！';
        if ($vipid !== false) {
            $errmsg = "";
            $code = 200;
            //添加操作日志
            $log = array(
                'remark' => '添加会员【' . I("post.company_id") . '】多倍操作',
                'logtype' => 'addvipmulti',
                'action_id' => I("post.company_id"),
                'info' => $data
            );
            D('LogAdmin')->addLog($log);

            //判断开始时间是否是当天
            if (I("post.start") == date("Y-m-d")) {
                //更新用户数据
                $userCompany = D('Home/UserCompany');
                $data = array(
                    "viptype" => I("post.viptype")
                );
                $result = $userCompany->editCompanyExtendInfo($info["company_id"], $data);
            }
        }

        return array("code" => $code, "errmsg" => $errmsg);
    }

    /**
     * 暂停会员操作
     * @param string $value [description]
     * @return [type]        [description]
     */
    private function vipPause()
    {
        $vip = D('Home/UserVip');
        //获取本次合同的详细信息
        $info = $vip->getContractInfo(I("post.parentid"));

        if ($info["start_time"] > I("post.start")) {
            return array("code" => "404", "errmsg" => "暂停开始时间要大于本次合同开始时间");
        }

        //if ($info["end_time"] < I("post.start")) {
        //    return array("code" => "404", "errmsg" => "暂停开始时间要小于本次合同结束时间");
        //}

        if ($info["end_time"] < I("post.start")) {
            return array("code" => "404", "errmsg" => "暂停开始时间要小于本次合同结束时间");
        }

        if (I("post.start") < date("Y-m-d")) {
            return array("code" => "404", "errmsg" => "暂停开始时间不能小于当天");
        }

        //如果有确定的结束时间
        if (I("post.end") != "") {
            if (I("post.end") < I("post.start")) {
                return array("code" => "404", "errmsg" => "暂停开始时间要小于暂停结束时间");
            }

            $pause_offset = (strtotime(I("post.end")) - strtotime(I("post.start"))) / 86400 + 1;

            if (I("post.delay_day") != "" && $pause_offset < I("post.delay_day")) {
                return array("code" => "404", "errmsg" => "暂停时间要大于延期时间");
            }

            $delay_day = I("post.delay_day");
            if ($delay_day != "" && I("post.delay_time") == "") {
                return array("code" => "404", "errmsg" => "请输入延期时间");
            }

            if (I("post.delay_day") != "" && !is_numeric($delay_day)) {
                return array("code" => "404", "errmsg" => "请输入正确的延期天数");
            }

            if (I("post.delay_time") != "" && $delay_day != "") {
                $dealy_time = I("post.delay_time");
                $offset = (strtotime($dealy_time) - strtotime($info["end_time"])) / 86400;

                if ($offset < $delay_day) {
                    return array("code" => "404", "errmsg" => "延期时间不能大于延期天数！");
                }
            }

            //如果延长时间不填写，则按照 暂停合同的时间延补日期
            //如果有延长时间，则按照延长时间延补日期
            $end_day = $pause_offset;
            if (!empty($delay_day)) {
                $end_day = $delay_day;
            }

            $end = date("Y-m-d", strtotime("+" . $end_day . " day", strtotime($info['end_time'])));
        }

        $data = array(
            "company_id" => I("post.company_id"),
            "company_name" => I("post.company_name"),
            "type" => 4,
            "viptype" => $info["viptype"],
            "contract_type" => $info["contract_type"],
            "cooperate_mode" => $info["cooperate_mode"],
            "start_time" => I("post.start"),
            "end_time" => I("post.end") == "" ? "" : I("post.end"),
            "saler_id" => I("post.saler_id") == "" ? "" : I("post.saler_id"),
            "saler_name" => I("post.saler_name") == "" ? "" : I("post.saler_name"),
            "parentid" => I("post.parentid"),
            "op_uid" => session("uc_userinfo.id"),
            "op_uname" => session("uc_userinfo.name"),
            "delay_day" => empty($delay_day) ? "" : $delay_day,
            "delay_time" => I("post.delay_time") == "" ? "" : $end,
            "time" => time(),
        );

        $vip = D('Home/UserVip');
        $vipid = $vip->addVip($data);
        $code = 404;
        $errmsg = '编辑失败,请稍后再试！';
        if ($vipid !== false) {
            $code = 200;
            $errmsg = '';

            //添加操作日志
            $log = array(
                'remark' => '添加会员【' . I("post.company_id") . '】暂停操作',
                'logtype' => 'addvippause',
                'action_id' => I("post.company_id"),
                'info' => $data
            );
            D('LogAdmin')->addLog($log);


            //获取总合同信息
            $allInfo = $vip->getContractInfo($info["parentid"]);

            //同步更新合同表的总合同和本次合同
            if (I("post.end") != "") {
                //如果暂停延长时间大于总合同的结束时间，则延补总合同结束时间
                if ($allInfo["end_time"] < $end) {
                    $offset = (strtotime($end) - strtotime($allInfo['end_time'])) / 86400;
                    $allEnd = date("Y-m-d", strtotime("+" . $offset . " day", strtotime($allInfo['end_time'])));

                    $data = array(
                        "end_time" => $allEnd
                    );
                    $vip->editVip($info["parentid"], $data);

                    //更新扩展中的总合同时间
                    $model = D('Home/UserCompany');
                    $data = array(
                        "contract_end" => strtotime($allEnd)
                    );
                    $model->editCompanyExtendInfo(I("post.company_id"), $data);
                }

                //更新本次合同的结束时间
                $data = array(
                    "end_time" => $end
                );
                $vip->editVip($info["id"], $data);
            }

            //判断暂停开始时间是否是当天，如果是当天则修改VIP会员状态
            if (I("post.start") == date("Y-m-d")) {
                //更新用户数据
                $user = D('Home/User');
                $data = array(
                    "on" => "-4"
                );

                if (I("post.end") != "") {
                    $data["end"] = $end;
                }
                $user->editCompanyInfo($info["company_id"], $data);

                // 暂停时间为当天需要更新会员日消耗会员VIP状态
                CompanyExpendLogicModel::editCompanyVipStatus($info["company_id"]);
            }
        }

        return array("code" => $code, "errmsg" => $errmsg);
    }

    /**
     * 退费操作
     * @return [type] [description]
     */
    private function vipRefund()
    {
        $vip = D('Home/UserVip');
        //获取本次合同的详细信息
        $info = $vip->getContractInfo(I("post.parentid"));
        //获取总合同信息
        $allInfo = $vip->getContractInfo($info["parentid"]);
        //计算退费时间
        //退费时间不能超过总合同的结束时间
        if (I("post.delay_time") == "") {
            return array("code" => "404", "errmsg" => "请选择退费时间");
        }

        if (I("post.delay_time") != date("Y-m-d")) {
            return array("code" => "404", "errmsg" => "退费功能只能当天操作");
        }

        if (I("post.delay_time") > $allInfo["end_time"]) {
            return array("code" => "404", "errmsg" => "退费时间不能超过总合同的结束时间");
        }

        if (I("post.delay_time") != I("post.confirm_delay_time")) {
            return array("code" => "404", "errmsg" => "二次输入的退费时间不一致！");
        }

        $data = array(
            "company_id" => I("post.company_id"),
            "company_name" => I("post.company_name"),
            "type" => 7,
            "viptype" => $info["viptype"],
            "contract_type" => $info["contract_type"],
            "cooperate_mode" => $info["cooperate_mode"],
            "saler_id" => I("post.saler_id") == "" ? "" : I("post.saler_id"),
            "saler_name" => I("post.saler_name") == "" ? "" : I("post.saler_name"),
            "parentid" => I("post.parentid"),
            "op_uid" => session("uc_userinfo.id"),
            "op_uname" => session("uc_userinfo.name"),
            "delay_time" => I("post.delay_time"),
            "time" => time(),
        );

        $vip = D('Home/UserVip');
        $vipid = $vip->addVip($data);
        $code = 404;
        $errmsg = '编辑失败,请稍后再试！';
        if ($vipid !== false) {
            $code = 200;
            $errmsg = '';

            //添加操作日志
            $log = array(
                'remark' => '添加会员【' . I("post.company_id") . '】退费操作',
                'logtype' => 'addvipdelay',
                'action_id' => I("post.company_id"),
                'info' => $data
            );
            D('LogAdmin')->addLog($log);

            //更新总合同时间
            // $data = array(
            //     "end_time" => I("post.delay_time")
            // );
            // $vip->editVip($info["parentid"], $data);

            //更新本次合同时间
            $data = array(
                "end_time" => I("post.delay_time")
            );
            $vip->editVip($info["id"], $data);

            //更新VIP会员的结束时间
            $user = D('Home/User');
            $data = array(
                "on" => "-1",
                "end" => I("post.delay_time")
            );
            $user->editCompanyInfo($info["company_id"], $data);
            

            //更新扩展中的总合同时间
            // $model = D('Home/UserCompany');
            // $data = array(
            //     "contract_end" => strtotime(I("post.delay_time"))
            // );
            // $model->editCompanyExtendInfo(I("post.company_id"), $data);
            

            // 退费需要删除会员日消耗当天落档记录
            CompanyExpendLogicModel::deleteCompanyExpendInfo($info["company_id"]);

        }
        return array("code" => $code, "errmsg" => $errmsg);
    }


    /**
     * 转让操作
     * @return [type] [description]
     */
    private function vipReturn()
    {
        //获取编辑前合同的合同信息
        $vip = D('Home/UserVip');
        //查询本次合同时间
        $info = $vip->getContractInfo(I("post.parentid"));

        if (I("post.delay_time") == "") {
            return array("code" => "404", "errmsg" => "请选择转让开始日期");
        }

        if (I("post.delay_time") != date("Y-m-d")) {
            return array("code" => "404", "errmsg" => "转让时间必须是当天");
        }

        if ($info["end_time"] < date("Y-m-d")) {
            return array("code" => "404", "errmsg" => "本次合同时间已结束，无法转让");
        }

        //转让时间不能大于本次合同结束时间
        if (I("post.delay_time") > $info["end_time"]) {
            return array("code" => "404", "errmsg" => "转让时间不能大于本次合同结束时间");
        }

        if (I("post.to_company_id") == "") {
            return array("code" => "404", "errmsg" => "选择转让公司");
        }

        if (I("post.to_company_id") == $info["company_id"]) {
            return array("code" => "404", "errmsg" => "转让操作不能自己转让自己");
        }

        if (I("post.delay_time") < date("Y-m-d")) {
            return array("code" => "404", "errmsg" => "转让时间不能小于当天");
        }

        //计算转让天数
        $offset = (strtotime($info["end_time"]) - strtotime(I("post.delay_time"))) / 86400;

        //计算到期时间
        $end = date("Y-m-d", strtotime("+" . $offset . " day", strtotime(I("post.delay_time"))));

        //查询被转让装修公司信息
        $user = D('Home/user');
        $vip = D('Home/UserVip');
        //被转让装修公司信息
        $companyInfo = $user->findCompanyInfo(I("post.to_company_id"));
        //被装让装修公司的合同信息
        $contract = $vip->getLastAllContract(I("post.to_company_id"));

        // 如果转让会员公司是会员状态并且会员类型和被转让的合同类型不一致
        if ($companyInfo["on"] == 2 && $contract["contract_type"] != $info["contract_type"]) {
            return array("code" => "404", "errmsg" => "当前公司和转让公司类型不一致");
        }

        //添加转让记录
        $data = array(
            "company_id" => I("post.company_id"),
            "company_name" => I("post.company_name"),
            "type" => 9,
            "viptype" => $info["viptype"],
            "contract_type" => $info["contract_type"],
            "cooperate_mode" => $info["cooperate_mode"],
            "delay_day" => $offset,
            "delay_time" => I("post.delay_time"),
            "end_time" => I("post.end") == "" ? "" : I("post.end"),
            "saler_id" => I("post.saler_id") == "" ? "" : I("post.saler_id"),
            "saler_name" => I("post.saler_name") == "" ? "" : I("post.saler_name"),
            "parentid" => $info["id"],
            "op_uid" => session("uc_userinfo.id"),
            "op_uname" => session("uc_userinfo.name"),
            "to_company" => I("post.to_company_id"),
            "to_name" => I("post.to_compnay_name"),
            "time" => time()
        );

        $vipid = $vip->addVip($data);

        $code = 404;
        $errmsg = '编辑失败,请稍后再试！';
        if ($vipid !== false) {
            $code = 200;
            $errmsg = '';

            //解除微信绑定
            D("Home/Logic/OrderWechatLogic")->unBindWechatAccount($data["company_id"]);

            //添加操作日志
            $log = array(
                'remark' => '添加会员【' . I("post.company_id") . '】转让操作',
                'logtype' => 'addvipdelay',
                'action_id' => I("post.company_id"),
                'info' => $data
            );
            D('LogAdmin')->addLog($log);

            //如果是当天转让，则修改VIP用户的状态
            if (I("post.delay_time") == date("Y-m-d")) {

                //修改总合同的结束时间
                $data = array(
                    "end_time" => I("post.delay_time")
                );
                $vip->editVip($info["parentid"], $data);

                //修改本次合同时间
                $data = array(
                    "end_time" => I("post.delay_time")
                );
                $vip->editVip($info["id"], $data);

                //更新用户数据
                $user = D('Home/User');
                $data = array(
                    "on" => "-1",
                    "end" => I("post.delay_time")
                );
                $user->editCompanyInfo($info["company_id"], $data);

                //更新扩展中的总合同时间
                $model = D('Home/UserCompany');
                $data = array(
                    "contract_end" => strtotime(I("post.delay_time"))
                );
                $model->editCompanyExtendInfo($info["company_id"], $data);

                //查询总合同时间
                $allInfo = $vip->getContractInfo($info["parentid"]);

                //添加日志
                $log = D('Home/LogVip');
                $data = array(
                    "comid" => $info["company_id"],
                    "old_state" => 2,
                    "new_state" => 9,
                    "opid" => session("uc_userinfo.id"),
                    "optime" => time(),
                    "operator" => session("uc_userinfo.name"),
                    "start" => $info["start_time"],
                    "end" => I("post.delay_time"),
                    "contract_start" => strtotime($allInfo["start_time"]),
                    "contract_end" => strtotime($allInfo["end_time"]),
                    "viptype" => $info["viptype"]
                );

                // 转让需要删除会员日消耗当天落档记录
                CompanyExpendLogicModel::deleteCompanyExpendInfo($info["company_id"]);

                //如果被转让的公司是会员公司
                if ($companyInfo["on"] == 2 && $companyInfo["fake"] == 0) {
                    //查询最新的一份本次合同信息
                    $nowContract = $vip->getLastNewContract(I("post.to_company_id"));
                    //如果最新的总合同和最新的本次合同不是同一份合同，重新查询总合同
                    if ($contract["id"] != $nowContract["parentid"]) {
                        $contract = $vip->getLastAllContract($nowContract["parentid"]);
                    }

                    //如果本次合同的结束时间小于总合同的时间
                    if ($nowContract["end_time"] < $contract["end_time"]) {
                        //结算合同时间差
                        $contract_offset = (strtotime($contract["end_time"]) - strtotime($nowContract["end_time"])) / 86400;

                        //如果合同的时间差小于转让的时间差
                        if ($contract_offset < $offset) {
                            $contract_offset = $offset - $contract_offset;

                            $contract_end = date("Y-m-d", strtotime("+" . $contract_offset . " day", strtotime($contract["end_time"])));
                        }
                    }

                    //如果有时间差异，则修改总合同
                    if (!empty($contract_end)) {
                        $data = array(
                            "end_time" => $contract_end,
                            "op_uid" => session("uc_userinfo.id"),
                            "op_uname" => session("uc_userinfo.name"),
                            "time" => time()
                        );
                        $vip->editVip($contract["id"], $data);
                    }
                    //计算本次合同到期时间
                    $end = date("Y-m-d", strtotime("+" . $offset . " day", strtotime($nowContract["end_time"])));

                    //修改本次合同信息
                    $data = array(
                        "end_time" => $end,
                        "op_uid" => session("uc_userinfo.id"),
                        "op_uname" => session("uc_userinfo.name"),
                        "time" => time()
                    );
                    $vipId = $vip->editVip($nowContract["id"], $data);

                    //修改会员的信息
                    $data = array(
                        "end" => $end
                    );
                    $user->editCompanyInfo($companyInfo["id"], $data);

                    //更新扩展中的总合同时间
                    $model = D('Home/UserCompany');
                    $data = array(
                        "contract_end" => strtotime($end)
                    );
                    $model->editCompanyExtendInfo($companyInfo["id"], $data);

                    //添加日志
                    $log = D('Home/LogVip');
                    $data = array(
                        "comid" => $companyInfo["id"],
                        "old_state" => $companyInfo["on"],
                        "new_state" => $companyInfo["on"],
                        "opid" => session("uc_userinfo.id"),
                        "optime" => time(),
                        "operator" => session("uc_userinfo.name"),
                        "start" => $companyInfo["start"],
                        "end" => $end,
                        "contract_start" => strtotime($contract["start_time"]),
                        "contract_end" => strtotime($contract_end),
                        "viptype" => $companyInfo["viptype"]
                    );

                    $log->addLog($data);
                } else {

                    //被转让的公司是非会员公司
                    //添加被转让公司的总合同
                    $data = array(
                        "company_id" => $companyInfo["id"],
                        "company_name" => $companyInfo["jc"],
                        "type" => 1,
                        "viptype" => $info["viptype"],
                        "contract_type" => $info["contract_type"],
                        "cooperate_mode" => $info["cooperate_mode"],
                        "start_time" => I("post.delay_time"),
                        "end_time" => $end,
                        "saler_id" => I("post.saler_id") == "" ? "" : I("post.saler_id"),
                        "saler_name" => I("post.saler_name") == "" ? "" : I("post.saler_name"),
                        "op_uid" => session("uc_userinfo.id"),
                        "op_uname" => session("uc_userinfo.name"),
                        "time" => time()
                    );

                    $vipId = $vip->addVip($data);

                    //添加本次合同
                    $type = 2;
                    if (count($contract) > 0) {
                        $type = 8;
                    }

                    $data = array(
                        "company_id" => $companyInfo["id"],
                        "company_name" => $companyInfo["jc"],
                        "type" => $type,
                        "viptype" => $info["viptype"],
                        "contract_type" => $info["contract_type"],
                        "cooperate_mode" => $info["cooperate_mode"],
                        "start_time" => I("post.delay_time"),
                        "end_time" => $end,
                        "saler_id" => I("post.saler_id") == "" ? "" : I("post.saler_id"),
                        "saler_name" => I("post.saler_name") == "" ? "" : I("post.saler_name"),
                        "parentid" => $vipId,
                        "op_uid" => session("uc_userinfo.id"),
                        "op_uname" => session("uc_userinfo.name"),
                        "time" => time()
                    );
                    $vip->addVip($data);


                    //调整被转让VIP的会员状态
                    $data = array(
                        "on" => "2",
                        "start" => I("post.delay_time"),
                        "end" => $end
                    );
                    $user->editCompanyInfo($companyInfo["id"], $data);

                    //更新扩展中的总合同时间
                    $model = D('Home/UserCompany');
                    $data = array(
                        "contract_start" => strtotime(I("post.delay_time")),
                        "contract_end" => strtotime($end),
                        "viptype" => $info["viptype"],
                        "fake" => 0
                    );

                    $model->editCompanyExtendInfo($companyInfo["id"], $data);

                    //添加日志
                    $log = D('Home/LogVip');
                    $data = array(
                        "comid" => $companyInfo["id"],
                        "old_state" => $companyInfo["on"],
                        "new_state" => 2,
                        "opid" => session("uc_userinfo.id"),
                        "optime" => time(),
                        "operator" => session("uc_userinfo.name"),
                        "start" => I("post.delay_time"),
                        "end" => $end,
                        "contract_start" => strtotime(I("post.delay_time")),
                        "contract_end" => strtotime($end),
                        "viptype" => $companyInfo["viptype"]
                    );
                    $log->addLog($data);
                }
            }
        }

        return array("code" => $code, "errmsg" => $errmsg);
    }

    /**
     * 获取合同信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function getContractList($id)
    {
        $date = date("Y-m-d");
        $vip = D('Home/UserVip');
        $reportModel = D('Home/AdvertisingReport');
        //获取所有的合同信息
        $result = $vip->getAllContractList($id);

        foreach ($result as $key => $value) {
            //总合同封装
            if (!array_key_exists($value["id"], $list)) {
                $list[$value["id"]]["id"] = $value["id"];
                $list[$value["id"]]["start_time"] = $value["start_time"];
                $list[$value["id"]]["end_time"] = $value["end_time"];
                $list[$value["id"]]["cooperate_mode"] = $value["cooperate_mode"];
                $list[$value["id"]]["day"] = (strtotime($value["end_time"]) - strtotime($value["start_time"])) / 86400 + 1;
            }
            //本次合同封装
            if (!array_key_exists($value["bid"], $list[$value["id"]]["item"])) {
                $list[$value["id"]]["item"][$value["bid"]]["id"] = $value["bid"];
                $list[$value["id"]]["item"][$value["bid"]]["start_time"] = $value["b_start"];
                $list[$value["id"]]["item"][$value["bid"]]["end_time"] = $value["b_end"];
                $list[$value["id"]]["item"][$value["bid"]]["viptype"] = $value["viptype"];
                $list[$value["id"]]["item"][$value["bid"]]["cooperate_mode"] = $value["cooperate_mode"];
                $list[$value["id"]]["item"][$value["bid"]]["day"] = (strtotime($value["b_end"]) - strtotime($value["b_start"])) / 86400 + 1;
                $list[$value["id"]]["item"][$value["bid"]]["company_id"] = $id;
                $list[$value["id"]]["item"][$value["bid"]]["cooperation_type"] = $value["cooperation_type"];
                if ($value["b_start"] > $date) {
                    //可编辑标识
                    $list[$value["id"]]["item"][$value["bid"]]["editMark"] = 1;
                }

                //获取合同中的广告报备信息
                $reportList = $reportModel->getNowAdvReport($id, $value["b_start"]);
                $report = array();

                foreach ($reportList as $k => $val) {
                    $report[$val["type"]][$val["location"]]["total"] = $val["total"];
                    $report[$val["type"]][$val["location"]]["use_day"] = $val["use_day"];
                    $report["flag"] = 0;

                    if (in_array($val["location"], array(4, 101))) {
                        $report["flag"] = 1;
                    }

                }

                if ($value["b_end"] > $date) {
                    $list[$value["id"]]["item"][$value["bid"]]["reportEditMark"] = 1;
                }
                $list[$value["id"]]["item"][$value["bid"]]["report"] = $report;
            }

            //合同状态封装
            if (!empty($value["cid"])) {
                $sub = array(
                    "id" => $value["cid"],
                    "start_time" => $value["c_start"],
                    "end_time" => $value["c_end"] == "0000-00-00" ? "" : $value["c_end"],
                    "type" => $value["c_type"],
                    "viptype" => $value["c_viptype"],
                    "delay_day" => $value["delay_day"] == 0 ? "" : $value["delay_day"],
                    "delay_time" => $value["delay_time"] == "0000-00-00" ? "" : $value["delay_time"],
                );

                if ($value["c_end"] != "0000-00-00") {
                    $sub["day"] = (strtotime($value["c_end"]) - strtotime($value["c_start"])) / 86400 + 1;
                }

                //1.时间未开始
                //2.时间已开始，但结束时间未结束
                //3.结束时间未定
                if ($value["c_start"] > $date || ($value["c_start"] <= $date && $value["c_end"] >= $date) || $value["c_end"] == "0000-00-00") {
                    //可编辑标识
                    $sub["editMark"] = 1;
                }
                $list[$value["id"]]["item"][$value["bid"]]["child"][] = $sub;
            }
        }
        return $list;
    }

    /**
     * 合同预览
     * @param string $value [description]
     * @return [type]        [description]
     */
    private function getPreview($data)
    {
        //获取本次合同信息
        if (!empty($data["id"])) {
            $vip = D('Home/UserVip');
            $user = D('Home/User');
            $reportModel = D('Home/AdvertisingReport');
            //获取当前的合同信息
            $info = $vip->getContractInfo($data["id"]);
            //获取总合同信息
            $allInfo = $vip->getContractInfo($info["parentid"]);
            //获取合同中的广告报备信息
            $reportList = $reportModel->getNowAdvReport($info["company_id"], $info["start_time"]);
            foreach ($reportList as $k => $val) {
                $report[$val["type"]][$val["location"]]["total"] = $val["total"];
                $report[$val["type"]][$val["location"]]["use_day"] = $val["use_day"];
            }
            //获取装修公司基本信息
            $userInfo = $user->findCompanyInfo($info["company_id"]);
        } else {
            $allInfo["company_name"] = $data["company_name"];
            $allInfo["start_time"] = $data["allbegin"];
            $allInfo["end_time"] = $data["allend"];
            $info["start_time"] = $data["begin"];
            $info["end_time"] = $data["end"];
            $info["saler_name"] = $data["saler_name"];
            $userInfo["jd_tel_name_1"] = $data["jd_tel_name_1"];
            $userInfo["jd_tel_1"] = $data["jd_tel_1"];
            $userInfo["jd_tel_name_2"] = $data["jd_tel_name_2"];
            $userInfo["jd_tel_2"] = $data["jd_tel_2"];
            $report[1][0]["total"] = $data["lunbo"];
            $report[2][1]["total"] = $data["tl_A"];
            $report[2][2]["total"] = $data["tl_B"];
            $report[2][3]["total"] = $data["tl_C"];
            $report[2][4]["total"] = $data["tl_D"];
        }


        $list["parent"] = $allInfo;
        $list["now"] = $info;
        $list["adv"] = $report;
        $list["userInfo"] = $userInfo;

        return $list;
    }

    /**
     * 编辑会员
     * @return [type] [description]
     */
    private function setEditvip($data)
    {
        //获取本次合同信息
        $vip = D('Home/UserVip');
        //获取当前的合同信息
        $info = $vip->getContractInfo($data["id"]);
        //获取总合同信息
        $allInfo = $vip->getContractInfo($info["parentid"]);

        //获取最后一份合同信息
        $prevInfo = $vip->getLastNewContract($info["company_id"], $info["id"]);


        if ($data["begin"] > $data["end"]) {
            return array("code" => "404", "errmsg" => "本次合同的开始时间不能大于本次合同的结束时间");
        }

        if (count($prevInfo) > 0) {
            if ($data["begin"] <= $prevInfo["end_time"]) {
                return array("code" => "404", "errmsg" => "本次合同的开始时间不能小于上一份合同的结束时间");
            }
        }

        if ($data["begin"] < $allInfo["start_time"]) {
            return array("code" => "404", "errmsg" => "本次合同的开始时间不能小于总合同的开始时间");
        }

        if ($data["end"] > $allInfo["end_time"]) {
            return array("code" => "404", "errmsg" => "本次合同的结束时间不能大于总合同的结束时间");
        }

        $data = array(
            "start_time" => I("post.begin"),
            "end_time" => I("post.end") == "" ? "" : I("post.end"),
            "op_uid" => session("uc_userinfo.id"),
            "op_uname" => session("uc_userinfo.name"),
            "time" => time()
        );

        $vip = D('Home/UserVip');
        $code = 404;
        $errmsg = '编辑失败,请稍后再试！';
        $result = $vip->editVip($info["id"], $data);
        if ($result !== false) {
            $code = 200;
            $errmsg = '';
        }
        return array("code" => $code, "errmsg" => $errmsg);
    }

    /**
     * 编辑会员多倍操作
     * @param string $value [description]
     * @return [type]        [description]
     */
    private function setEditMulti()
    {
        //获取编辑前合同的合同信息
        $vip = D('Home/UserVip');
        $info = $vip->getContractInfoById(I("post.id"));
        $date = date("Y-m-d");
        //如果更改的结束时间小于原结束时间，并且结束时间小于当天
        if (I("post.end") < $date) {
            return array("code" => "404", "errmsg" => "多倍的结束时间不能小于当天！");
        }

        $data = array(
            "end_time" => I("post.end"),
            "viptype" => I("post.viptype"),
            "op_uid" => session("uc_userinfo.id"),
            "op_uname" => session("uc_userinfo.name"),
            "time" => time()
        );

        $vip = D('Home/UserVip');
        $vipid = $vip->editVip(I("post.id"), $data);
        $code = 404;
        $errmsg = '编辑失败,请稍后再试！';
        if ($vipid !== false) {
            $code = 200;
            $errmsg = '';
            //添加操作日志
            $log = array(
                'remark' => '编辑会员【' . $info["company_id"] . '】暂停操作',
                'logtype' => 'editvippause',
                'action_id' => $info["company_id"],
                'info' => $data
            );
            D('LogAdmin')->addLog($log);

            //更新VIP会员的其他信息
            $model = D('Home/UserCompany');
            $data = array(
                "viptype" => I("post.viptype")
            );
            $model->editCompanyExtendInfo($info["company_id"], $data);
        }
        return array("code" => $code, "errmsg" => $errmsg);
    }

    /**
     * 编辑暂停操作
     * @return [type] [description]
     */
    private function setEditPause()
    {
        //获取编辑前合同的合同信息
        $vip = D('Home/UserVip');
        $info = $vip->getContractInfoById(I("post.id"));
        //获取本次合同信息
        $nowInfo = $vip->getContractInfoById($info["parentid"]);
        $date = date("Y-m-d");

        if (I("post.end") < $info["start_time"]) {
            return array("code" => "404", "errmsg" => "暂停结束时间不能小于开始时间！");
        }

        if (I("post.end") < $date) {
            return array("code" => "404", "errmsg" => "暂停结束时间不能小于当天！");
        }

        $pause_offset = (strtotime(I("post.end")) - strtotime($info["start_time"])) / 86400 + 1;

        //推算出原合同的合同结束时间
        if ($info["delay_day"] == 0) {
            $old_end = $nowInfo["end_time"];
        } else {
            $old_end = date("Y-m-d", strtotime("-" . $info["delay_day"] . " day", strtotime($info['delay_time'])));
        }

        if (I("post.end") != "") {
            if (I("post.delay_day") == "") {
                //没有填写延期天数
                $end = date("Y-m-d", strtotime("+" . $pause_offset . " day", strtotime($old_end)));
                $delay_day = $pause_offset;
                $delay_time = $end;
            } else {
                //填写了延期天数
                $delay_day = I("post.delay_day");
                $delay_time = I("post.delay_time");

                if (I("post.delay_day") != "" && !is_numeric($delay_day)) {
                    return array("code" => "404", "errmsg" => "请输入正确的延期天数");
                }

                if ($delay_day != "" && I("post.delay_time") == "") {
                    return array("code" => "404", "errmsg" => "请输入延期时间");
                }

                if ($delay_day > $pause_offset) {
                    return array("code" => "404", "errmsg" => "延期天数不能大于暂停实际天数！");
                }

                $end = date("Y-m-d", strtotime("+" . $delay_day . " day", strtotime($old_end)));

                if ($end != I("post.delay_time")) {
                    return array("code" => "404", "errmsg" => "延期时间和延期天数不一致，请重新计算填写！");
                }
            }
        }

        $data = array(
            "end_time" => I("post.end") == "" ? "" : I("post.end"),
            "op_uid" => session("uc_userinfo.id"),
            "op_uname" => session("uc_userinfo.name"),
            "delay_day" => empty($delay_day) ? "" : $delay_day,
            "delay_time" => $delay_time,
            "time" => time()
        );

        if (I("post.delay_day") == "") {
            $data["delay_day"] = null;
            $data["delay_time"] = null;
        }

        $vip = D('Home/UserVip');
        $vipid = $vip->editVip(I("post.id"), $data);
        $code = 404;
        $errmsg = '编辑失败,请稍后再试！';

        if ($vipid !== false) {
            $code = 200;
            $errmsg = '';

            //添加操作日志
            $log = array(
                'remark' => '编辑会员【' . $nowInfo["company_id"] . '】暂停操作',
                'logtype' => 'editvippause',
                'action_id' => $nowInfo["company_id"],
                'info' => $data
            );
            D('LogAdmin')->addLog($log);

            //如果有时间差异，更新本次合同的结束时间
            if (!empty($end)) {
                $data = array(
                    "end_time" => $end
                );
                $vipid = $vip->editVip($nowInfo["id"], $data);

                //同时更新VIP用户
                $data = array(
                    "end" => $end
                );
                $data["on"] = -4;
                //如果暂停结束时间是今天，怎么修改会员状态
                if (I("post.end") == date("Y-m-d")) {
                    $data["on"] = 2;
                }

                $user = D('Home/User');
                $user->editCompanyInfo($nowInfo["company_id"], $data);

                //获取总合同的时间
                //如果总合同的结束时间小于的修改后的结束时间，修改总合同的结束时间
                //查询总合同时间
                $allInfo = $vip->getContractInfo($nowInfo["parentid"]);

                if ($allInfo["end_time"] < $end) {
                    $offset = (strtotime($end) - strtotime($allInfo["end_time"])) / 86400;
                    $end = strtotime("+" . $offset . " day", strtotime($allInfo["end_time"]));
                    //更新VIP会员扩展信息中的总合同结束时间
                    $model = D('Home/UserCompany');
                    $data = array(
                        "contract_end" => strtotime($end)
                    );
                    $model->editCompanyExtendInfo($info["company_id"], $data);
                }


            }
        }
        return array("code" => $code, "errmsg" => $errmsg);
    }

    /**
     * 编辑广告报备
     * @return [type] [description]
     */
    public function setEditadvreport($data)
    {
        $id = $data["id"];

        $vip = D('Home/UserVip');
        $advReport = D('Home/AdvertisingReport');
        //获取当前的合同信息
        $info = $vip->getContractInfo($data["contract_id"]);
        //获取原有的广告报备
        $result = $advReport->getNowAdvReport($data["id"], $data["date"]);
        foreach ($result as $key => $value) {
            $list[$value["type"]][$value["location"]]["total"] = $value["total"];
            $list[$value["type"]][$value["location"]]["use_day"] = $value["use_day"];
            if (in_array($value["location"], array(3, 4))) {
                $list["flag"] = 1;
            }
        }

        //删除原有的广告报备信息
        $advReport = D('Home/AdvertisingReport');
        $advReport->delReport($data["id"], $data["date"]);

        //添加新的广告报备
        //添加轮显广告报备
        $data = array(
            "comid" => $id,
            "type" => 1,
            "total" => I("post.lunbo"),
            "start" => $info["start_time"],
            "end" => $info["end_time"],
            "uid" => session("uc_userinfo.id"),
            "uname" => session("uc_userinfo.name"),
            "use_day" => $list[1][0]["use_day"],
            "time" => time()
        );
        $advReport->addReport($data);

        //添加通栏A广告报备
        $data = array(
            "comid" => $id,
            "type" => 2,
            "location" => 1,
            "total" => I("post.tl_A"),
            "start" => $info["start_time"],
            "end" => $info["end_time"],
            "use_day" => $list[2][1]["use_day"],
            "uid" => session("uc_userinfo.id"),
            "uname" => session("uc_userinfo.name"),
            "time" => time()
        );
        $advReport->addReport($data);

        //添加通栏B广告报备
        $data = array(
            "comid" => $id,
            "type" => 2,
            "location" => 2,
            "total" => I("post.tl_B"),
            "start" => $info["start_time"],
            "end" => $info["end_time"],
            "use_day" => $list[2][2]["use_day"],
            "uid" => session("uc_userinfo.id"),
            "uname" => session("uc_userinfo.name"),
            "time" => time()
        );
        $advReport->addReport($data);

        //添加通栏C广告报备
        $data = array(
            "comid" => $id,
            "type" => 2,
            "location" => 3,
            "total" => I("post.tl_C"),
            "start" => $info["start_time"],
            "end" => $info["end_time"],
            "use_day" => $list[2][3]["use_day"],
            "uid" => session("uc_userinfo.id"),
            "uname" => session("uc_userinfo.name"),
            "time" => time()
        );
        $advReport->addReport($data);

        //老站添加通栏D
        if (I("post.type") == 1) {
            //添加通栏D广告报备
            $data = array(
                "comid" => $id,
                "type" => 2,
                "location" => 101,
                "total" => I("post.tl_D"),
                "start" => $info["start_time"],
                "end" => $info["end_time"],
                "use_day" => $list[2][4]["use_day"],
                "uid" => session("uc_userinfo.id"),
                "uname" => session("uc_userinfo.name"),
                "time" => time()
            );
            $advReport->addReport($data);
        }

        return array("code" => 200, "errmsg" => "");
    }

    /**
     * 添加新合同操作
     * @param string $value [description]
     * @return [type]        [description]
     */
    private function editCompanyNewContract()
    {
        $id = I("post.company_id");
        //获取上一份合同的合同信息
        $vip = D('Home/UserVip');
        $info = $vip->getLastNewContract($id);
        $allInfo = $vip->getContractInfo($info["parentid"]);

        //判断当前的新合同时间是否大于上一份合同的结束时间
        //上一份合同已开始的情况下
        if ($info["start_time"] <= date("Y-m-d")) {
            if ($info["end_time"] >= I("post.begin")) {
                return array("code" => "404", "errmsg" => "合同的开始时间不能小于上一份合同的结束时间！");
            }
        }

        if (I("post.end") < I("post.begin")) {
            return array("code" => "404", "errmsg" => "合同的结束时间不能小于开始时间！");
        }

        if (I("post.end") > $allInfo["end_time"]) {
            return array("code" => "404", "errmsg" => "合同的结束时间不能大于总合同的结束时间！");
        }

        $vipType = I("post.viptype");
        if (I("post.cooperationType") == 2) {
            $vipType = 1;
        }

        if (empty($vipType)) {
            return array("code" => "404", "errmsg" => "请选择几倍会员！");
        }

        $vip = D('Home/UserVip');
        $advReport = D('Home/AdvertisingReport');
        //如果上一份合同还为开始，则删除上一份合同
        if ($info["start_time"] > date("Y-m-d")) {
            //删除未开始的本次合同
            $vip->delContract($info["id"]);
            //删除和本次合同相关的广告报备信息
            $advReport->delReport($id, $info["start_time"]);
        }

        $data = array(
            "company_id" => I("post.company_id"),
            "company_name" => I("post.company_name"),
            "type" => 8,
            "viptype" => $vipType,
            "start_time" => I("post.begin"),
            "end_time" => I("post.end") == null ? "" : I("post.end"),
            "saler_id" => I("post.saler_id") == null ? "" : I("post.saler_id"),
            "saler_name" => I("post.saler_name") == null ? "" : I("post.saler_name"),
            "contract_type" => $allInfo["contract_type"],
            "cooperate_mode" => $allInfo["cooperate_mode"],
            "parentid" => $allInfo["id"],
            "op_uid" => session("uc_userinfo.id"),
            "op_uname" => session("uc_userinfo.name"),
            "cooperation_type" => I("post.cooperationType"),
            "time" => time()
        );

        $vipid = $vip->addVip($data);
        $code = 404;
        $errmsg = '编辑失败,请稍后再试！';
        if ($vipid !== false) {
            $code = 200;
            $errmsg = '';

            //添加操作日志
            $log = array(
                'remark' => '添加会员【' . $id . '】新本次合同',
                'logtype' => 'addnewcontract',
                'action_id' => $id,
                'info' => $data
            );
            D('LogAdmin')->addLog($log);

            $user = D('Home/User');
            //查询本次会员信息
            $userInfo = $user->findCompanyInfo($id, $this->city);
            //添加vip日志
            $log = D('Home/LogVip');
            $data = array(
                "comid" => $id,
                "old_state" => $userInfo["on"],
                "new_state" => 2,
                "opid" => session("uc_userinfo.id"),
                "optime" => time(),
                "operator" => session("uc_userinfo.name"),
                "start" => I("post.begin"),
                "end" => I("post.end"),
                "contract_start" => strtotime($allInfo["start_time"]),
                "contract_end" => strtotime($allInfo["end_time"]),
                "viptype" => $vipType
            );
            $log->addLog($data);

            //判断合同开始时间是否是当天，如果是修改会员状态
            if (I("post.begin") == date("Y-m-d")) {
                $data = array(
                    "start" => I("post.begin"),
                    "end" => I("post.end"),
                    "classid" => 3,
                    "on" => 2
                );
                //更新VIP会员的结束时间
                $user = D('Home/User');
                $user->editCompanyInfo($info["company_id"], $data);

                //更新VIP会员的其他信息
                $model = D('Home/UserCompany');
                $data = array(
                    "viptype" => $vipType,
                    "cooperation_type" => I("post.cooperationType"),
                );
                $model->editCompanyExtendInfo($id, $data);
            }

            //添加轮显广告报备
            $data = array(
                "comid" => $id,
                "type" => 1,
                "total" => I("post.lunbo"),
                "start" => I("post.begin"),
                "end" => I("post.end"),
                "uid" => session("uc_userinfo.id"),
                "uname" => session("uc_userinfo.name"),
                "time" => time()
            );
            $advReport->addReport($data);

            //添加通栏A广告报备
            $data = array(
                "comid" => $id,
                "type" => 2,
                "location" => 1,
                "total" => I("post.tl_A"),
                "start" => I("post.begin"),
                "end" => I("post.end"),
                "uid" => session("uc_userinfo.id"),
                "uname" => session("uc_userinfo.name"),
                "time" => time()
            );
            $advReport->addReport($data);

            //添加通栏B广告报备
            $data = array(
                "comid" => $id,
                "type" => 2,
                "location" => 2,
                "total" => I("post.tl_B"),
                "start" => I("post.begin"),
                "end" => I("post.end"),
                "uid" => session("uc_userinfo.id"),
                "uname" => session("uc_userinfo.name"),
                "time" => time()
            );
            $advReport->addReport($data);

            //老站添加通栏C和通栏D
            if (I("post.type") == 1) {
                //添加通栏C广告报备
                $data = array(
                    "comid" => $id,
                    "type" => 2,
                    "location" => 3,
                    "total" => I("post.tl_C"),
                    "start" => I("post.begin"),
                    "end" => I("post.end"),
                    "uid" => session("uc_userinfo.id"),
                    "uname" => session("uc_userinfo.name"),
                    "time" => time()
                );
                $advReport->addReport($data);

                //添加通栏D广告报备
                $data = array(
                    "comid" => $id,
                    "type" => 2,
                    "location" => 101,
                    "total" => I("post.tl_D"),
                    "start" => I("post.begin"),
                    "end" => I("post.end"),
                    "uid" => session("uc_userinfo.id"),
                    "uname" => session("uc_userinfo.name"),
                    "time" => time()
                );
                $advReport->addReport($data);
            }
        }
        return array("code" => $code, "errmsg" => $errmsg);
    }

    /**
     * 编辑假会员
     * @return [type] [description]
     */
    public function editFakeCompanyContract()
    {
        //同时更新VIP用户
        $data = array(
            "start" => I("post.begin"),
            "end" => I("post.end"),
            "on" => 2
        );
        if (I("post.end") < date("Y-m-d")) {
            $data["on"] = -1;
        }

        $user = D('Home/User');
        $result = $user->editCompanyInfo(I("post.id"), $data);
        $code = 404;
        $errmsg = '编辑失败,请稍后再试！';
        if ($result !== false) {
            $code = 200;
            $errmsg = '';
        }
        return array("code" => $code, "errmsg" => $errmsg);
    }

    /**
     * 装修公司咨询审核
     *
     * @return void
     */
    public function consultCheck()
    {
        $param_data = I('get.');
        $param = array_filter($param_data, 'trim');

        $companyConsultLogic = new CompanyConsultLogicModel();

        //表单验证
        $vaildate = $companyConsultLogic->_consultCheckVaildate($param);
        if ($vaildate['result'] === false) {
            $this->error('筛选条件不合法：' . $vaildate['mes'], '', 1);
        }
        $where = $companyConsultLogic->getMap($param);

        //判断是否是导出操作
        if (isset($param['down']) && $param['down'] == 1) {
            $companyConsultLogic->exportConsult($where);
        }

        $count = $companyConsultLogic->consultCount($where);;
        if ($count) {
            //分页参数
            $page_size = empty($param['psize']) ? 20 : $param['psize'];
            $this->assign('psize', $page_size);

            $page = new CPage($count, $page_size);
            $this->assign('page', $page->show());
            //获取咨询记录
            $list = $companyConsultLogic->consultList($where, [], $page->nowPage, $page_size);
            $list = $companyConsultLogic->telCount($list);
            $this->assign('list', $list);
            //获取咨询记录对应的处理记录
            $consultRecordLogic = new CompanyConsultRecordLogicModel();
            $consultRecordData = $consultRecordLogic->selectConsultDealRecord(array_column($list, 'id'));
            $this->assign('consult_record', $consultRecordData);
        }

        //获取城市信息
        $this->assign('citys', D('quyu')->getAllQuyuOnly());

        $parse = parse_url($_SERVER['REQUEST_URI']);
        $this->assign('query', isset($parse['query']) ? $parse['query'] : '');

        $this->display();
    }

    /**
     * 根据IP获取装修公司咨询
     *
     * @return void
     */
    public function ipconsult()
    {
        $ip = I('get.ip', '', 'trim');
        if (empty($ip)) {
            $this->error('查询IP地址不能为空');
        }
        $where['d.ip'] = $ip;
        $companyConsultLogic = new CompanyConsultLogicModel();
        $count = $companyConsultLogic->consultCount($where);
        if ($count) {
            $page = new CPage($count, 20);
            //获取咨询记录
            $list = $companyConsultLogic->consultList($where, [], $page->nowPage, 20);
            $this->assign([
                'page' => $page->show(),
                'list' => $list,
            ]);
        }
        $this->display('Vip/consultcheck/ip');
    }

    /**
     * 根据手机号码获取装修公司咨询
     *
     * @return void
     */
    public function telconsult()
    {
        $ip = I('get.tel', '', 'trim');
        if (empty($ip)) {
            $this->error('查询手机号码不能为空');
        }
        $where['a.tel'] = $ip;
        $companyConsultLogic = new CompanyConsultLogicModel();
        $count = $companyConsultLogic->consultCount($where);
        if ($count) {
            $page = new CPage($count, 20);
            //获取咨询记录
            $list = $companyConsultLogic->consultList($where, [], $page->nowPage, 20);
            $this->assign([
                'page' => $page->show(),
                'list' => $list,
            ]);
        }
        $this->display('Vip/consultcheck/tel');
    }

    /**
     * 添加咨询处理记录接口
     */
    public function addDealRecord()
    {
        $user = getAdminUser();
        $param_data = I('post.');
        $param = array_filter($param_data, 'trim');
        $param['communication'] = htmlspecialchars($param['communication']);
        //表单验证
        $vaildate = $this->_addDealRecordVaildate($param);
        if ($vaildate['result'] === false) {
            $this->error($vaildate['mes'], '', 1);
        }
        if (!D("Home/Logic/CompanyConsultRecordLogic")->insertRecord($param, $user)) {
            $this->error('录入信息失败', '', 1);
        }
        $this->success('新增记录成功', '', 1);
    }

    private function _addDealRecordVaildate($data)
    {
        //咨询记录id
        if (empty($data['consult_id']) || !is_numeric($data['consult_id'])) {
            return ['result' => false, 'mes' => '找不到咨询记录'];
        }
        //验证跟踪人
        if (empty($data['deal_man']) || mb_strlen($data['deal_man'], 'utf-8') > 50) {
            return ['result' => false, 'mes' => '跟踪人信息格式不对'];
        }
        //公司地址
        if (!empty($data['address'])) {
            if (mb_strlen($data['address'], 'utf-8') > 255) {
                return ['result' => false, 'mes' => '公司地址异常'];
            }
        }
        //跟踪方式
        if (empty($data['deal_type']) || !in_array($data['deal_type'], [1, 2, 3])) {
            return ['result' => false, 'mes' => '跟踪方式异常'];
        }
        //意向等级
        if (empty($data['success_level']) || !in_array($data['success_level'], [1, 2, 3])) {
            return ['result' => false, 'mes' => '意向等级异常'];
        }
        //谈话内容
        if (empty($data['communication'])) {
            return ['result' => false, 'mes' => '谈话内容异常'];
        }
        //合作状态
        if (empty($data['status']) || !in_array($data['status'], [1, 2, 3, 4])) {
            return ['result' => false, 'mes' => '意向等级异常'];
        }
        return ['result' => true, 'mes' => '验证通过'];
    }


    /**
     * 延期会员操作
     * @return [type] [description]
     */
    private function vipDelay()
    {
        $vip = D('Home/UserVip');
        //获取本次合同的详细信息
        $info = $vip->getContractInfo(I("post.parentid"));
        $delay_day = I("post.day");
        if (I("post.day") == "" || !is_numeric($delay_day)) {
            return array("code" => "404", "errmsg" => "请输入正确的延期天数");
        }

        //计算延期时间
        $end = date("Y-m-d", strtotime("+" . $delay_day . " day", strtotime($info["end_time"])));

        if ($end != I("post.delay_time")) {
            return array("code" => "404", "errmsg" => "您输入的延期时长与延期截止日期不一致");
        }

        $data = array(
            "company_id" => I("post.company_id"),
            "company_name" => I("post.company_name"),
            "type" => 5,
            "viptype" => $info["viptype"],
            "contract_type" => $info["contract_type"],
            "cooperate_mode" => $info["cooperate_mode"],
            "saler_id" => I("post.saler_id") == null ? "" : I("post.saler_id"),
            "saler_name" => I("post.saler_name") == null ? "" : I("post.saler_name"),
            "parentid" => I("post.parentid"),
            "op_uid" => session("uc_userinfo.id"),
            "op_uname" => session("uc_userinfo.name"),
            "delay_day" => empty($delay_day) ? "" : $delay_day,
            "delay_time" => empty($end) ? "" : $end,
            "time" => time(),
        );

        $vip = D('Home/UserVip');
        $vipid = $vip->addVip($data);
        $code = 404;
        $errmsg = '编辑失败,请稍后再试！';
        if ($vipid !== false) {
            $code = 200;
            $errmsg = '';
            //添加操作日志
            $log = array(
                'remark' => '添加会员【' . I("post.company_id") . '】延期操作',
                'logtype' => 'addvipdelay',
                'action_id' => I("post.company_id"),
                'info' => $data
            );
            D('LogAdmin')->addLog($log);

            //获取总合同信息
            $allInfo = $vip->getContractInfo($info["parentid"]);
            //如果延期时间大于的总合同的结束时间，则总合同结束时间延补
            if ($end > $allInfo["end_time"]) {
                $data = array(
                    "end_time" => $end
                );
                $vip->editVip($info["parentid"], $data);

                //更新扩展中的总合同时间
                $model = D('Home/UserCompany');
                $data = array(
                    "contract_end" => strtotime($end)
                );
                $model->editCompanyExtendInfo(I("post.company_id"), $data);
            }

            //更新本次合同的结束时间
            $data = array(
                "end_time" => $end
            );
            $vip->editVip($info["id"], $data);

            //更新VIP会员的结束时间
            $user = D('Home/User');
            $data = array(
                "end" => $end
            );
            $user->editCompanyInfo($info["company_id"], $data);
        }
        return array("code" => $code, "errmsg" => $errmsg);
    }

    /**
     * 获取上会员统计
     * @param  [type] $begin [description]
     * @param  [type] $end   [description]
     * @param  [type] $cs    [description]
     * @return [type]        [description]
     */
    private function getVipStatiitics($begin, $end, $cs)
    {
        $dayStart = strtotime(date("Y-m-d"));
        $dayEnd = $dayStart + 86400 - 1;

        if (!empty($begin) && !empty($end)) {
            $dayStart = strtotime($begin);
            $dayEnd = strtotime($end) + 86400 - 1;
        }
        $result = D("UserVip")->getVipStatiitics($dayStart, $dayEnd, $cs);

        foreach ($result as $key => $value) {
            //新上会员
            if (in_array($value["type"], array(2, 8))) {
                $all["new"]["child"][] = $value;
                $all["new"]["count"]++;
            }

            //暂停会员
            if ($value["type"] == 4) {
                $all["parse"]["child"][] = $value;
                $all["parse"]["count"]++;
            }

            //延期会员
            if ($value["type"] == 5) {
                $all["delay"]["child"][] = $value;
                $all["delay"]["count"]++;
            }

            //退费会员
            if ($value["type"] == 7) {
                $all["back"]["child"][] = $value;
                $all["back"]["count"]++;
            }

            //转让会员
            if ($value["type"] == 9) {
                $all["turn"]["child"][] = $value;
                $all["turn"]["count"]++;
            }
        }
        return $all;
    }

    /**
     * 前台会员删除
     * @return [type] [description]
     */
    public function companydel()
    {
        $citys = getUserCitys();
        $where = I('get.');
//        $where["is_show"] = 1;
        //获取管辖城市用户列表
        $list = $this->getVipList($this->city, $where);
        $this->assign("city", $citys);
        $this->assign("list", $list["list"]);
        $this->assign("page", $list["page"]);
        $this->display();
    }

    public function changeshow()
    {
        if ($_POST) {
            $id = I("post.id");
            if (empty($id)) {
                $this->ajaxReturn(array("code" => 1, "error_msg" => "缺少参数！"));
            }

            $showtype = I("post.showtype") ? I("post.showtype") : 2;

            if($showtype != 1 && $showtype != 2){   //1表示恢复  2表示删除
                $this->ajaxReturn(array("code" => 1, "error_msg" => "参数错误！"));
            }
            $log['logtype'] = 'changeshow';
            $log['action_id'] = $id;
            if($showtype == 2){
                //若是有效会员不可删除
                if (D("Home/Logic/CompanyLogic")->checkCompanyOn($id)) {
                    $this->ajaxReturn(array("error_code" => 1, "error_msg" => "有效会员不可删除！"));
                }

                //添加删除操作日志
                $log['info'] = array(
                    "userid" => session("uc_userinfo.id"),
                    "username" => session("uc_userinfo.name"),
                    'logtype' => 'changeshow',
                    'action_id' => $id,
                    'info' => '前台会员信息删除'
                );

            }else{
                //添加恢复操作日志
                $log['info'] = array(
                    "userid" => session("uc_userinfo.id"),
                    "username" => session("uc_userinfo.name"),
                    'logtype' => 'changeshow',
                    'action_id' => $id,
                    'info' => '前台会员信息恢复'
                );
            }
            //修改前台展示
            $result = D("Home/Logic/CompanyLogic")->changeCompanyShow($id,$showtype);
            if($result !== false){
                //添加日志
                D('LogAdmin')->addLog($log);
            }

            $this->ajaxReturn(array("error_code" => 0));
        }
    }

    //258 装修公司咨询审核--添加“处理”操作、“处理人”、“处理时间”信息
    public function operate()
    {
        try {
            $id = I('post.id');
            if (empty($id)) {
                throw new Exception('无效的参数', 401);
            }
            if (empty($this->User['id'])) {
                throw new Exception('请登录', 403);
            }
            $companyConsultLogic = new CompanyConsultLogicModel();
            $ret = $companyConsultLogic->operate($id, $this->User['id']);
            if(empty($ret)){
                throw new Exception('处理失败', 501);
            }
            $code = 0;
            $msg = '处理成功';
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }
        $data = ['error_code' => $code, 'error_msg' => $msg,];
        $this->ajaxReturn($data);
    }

    /**
     * 公司内容替换
     */
    public function manage_company()
    {
        set_time_limit(0);    //无限制延长 php 执行时间
        $post = I('post.');
        if (empty($post['id']) || empty($post['keywords'])) {
            $this->ajaxReturn(['status' => 0, 'info' => '替换的关键字 格式/内容不正确！']);
        }
        //获取装修公司详情
        $user = (new UserModel())->getUserInfoById('id,jc,qc,dz', $post['id']);
        if (empty($user)) {
            $this->ajaxReturn(['status' => 0, 'info' => '选择的公司不存在,请重新选择!']);
        }
        $logic = new ManageCompanyLogicModel();
        //获取关键字
        $keywords = $logic->getKeywords($post['keywords']);
        if (count($keywords) > 0) {
            //替换公司信息
            $logic->replaceUser($user,$keywords);
            //替换公司附加信息
            $logic->replaceUserCompany($user['id'],$keywords);
            //替换案例
            $logic->replaceCases($user['id'], $keywords);
            $this->ajaxReturn(['status' => 1, 'info' => '替换成功! ']);
        } else {
            $this->ajaxReturn(['status' => 0, 'info' => '替换的关键字 格式/内容不正确！']);
        }
    }
}
