<?php


namespace Home\Controller;


use Common\Enums\ApiConfig;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\LogAdminLogicModel;
use Home\Model\Logic\MallAddressLogicModel;
use Home\Model\Logic\SendSmsLogicModel;
use Home\Model\Logic\UcenterPlatformLogicModel;
use Home\Model\Logic\UcenterUserLogicModel;

class UserController extends HomeBaseController
{
    protected $ucenterUserLogic;
    protected $ucenterPlatformLogic;

    public function __construct()
    {
        parent::__construct();
        $this->ucenterUserLogic = new UcenterUserLogicModel();
        $this->ucenterPlatformLogic = new UcenterPlatformLogicModel();
    }

    // 用户管理
    public function userManagement()
    {
        $param = I('get.');
        $this->assign("getdata", $param);

        $param = $this->checktime($param);
        //列表
        $result = $this->ucenterUserLogic->getAllUserList($param);

        //产品线
        $platform = $this->ucenterPlatformLogic->getPlatformList();

        //城市
        //获取管辖城市信息
        $cityIds = getAdminCityIds(true, true, true);
        $city = getCityListByCityIds($cityIds);
        $nowpage = $param['p'] ? ($param['p'] - 1) : 0;
        $this->assign("nowpage", $nowpage);
        $this->assign("list", $result['list']);
        $this->assign("page", $result['page']);
        $this->assign("platform", $platform);
        $this->assign("city", $city);
        $this->display();
    }


    //齐装app
    public function qizhuangApp()
    {
        $param = I('get.');
        $this->assign("getdata", $param);
        $param['source2'] = "6d8fb8c330b43b3b67c399948f82c1f6";      //6d8fb8c330b43b3b67c399948f82c1f6 是齐装app的slot
        $param = $this->checktime($param);
        //列表
        $result = $this->ucenterUserLogic->getPlatformUserList($param);

        $allusercount = $this->ucenterUserLogic->getUcenterUserCount();     //账号总数
        $hadusercount = $this->ucenterUserLogic->getUcenterUserCount($param['source2']);     //账号总数

        $nowpage = $param['p'] ? ($param['p'] - 1) : 0;
        $this->assign("nowpage", $nowpage);
        $this->assign("allcount", $allusercount);
        $this->assign("hadcount", $hadusercount);
        $this->assign("list", $result['list']);
        $this->assign("page", $result['page']);
        $this->display();
    }


    //齐装PC
    public function qizhuangPc()
    {
        $param = I('get.');
        $this->assign("getdata", $param);

        $param['source2'] = "9f6910a77bc9c8d4cf9db31ed41af490";      //9f6910a77bc9c8d4cf9db31ed41af490 是齐装PC的slot
        $param = $this->checktime($param);
        //列表
        $result = $this->ucenterUserLogic->getPlatformUserList($param);

        $allusercount = $this->ucenterUserLogic->getUcenterUserCount();     //账号总数
        $hadusercount = $this->ucenterUserLogic->getUcenterUserCount($param['source2']);     //账号总数

        $nowpage = $param['p'] ? ($param['p'] - 1) : 0;
        $this->assign("nowpage", $nowpage);
        $this->assign("allcount", $allusercount);
        $this->assign("hadcount", $hadusercount);
        $this->assign("list", $result['list']);
        $this->assign("page", $result['page']);
        $this->display();
    }


    //装修说app
    public function zhuangxiushuoApp()
    {
        $param = I('get.');
        $this->assign("getdata", $param);

        $param['source2'] = "c50a5ed98f4b77f07f28d181e15566f7";      //c50a5ed98f4b77f07f28d181e15566f7 是装修说app的slot
        $param = $this->checktime($param);
        //列表
        $result = $this->ucenterUserLogic->getPlatformUserList($param);

        $allusercount = $this->ucenterUserLogic->getUcenterUserCount();     //账号总数
        $hadusercount = $this->ucenterUserLogic->getUcenterUserCount($param['source2']);     //账号总数

        $nowpage = $param['p'] ? ($param['p'] - 1) : 0;
        $this->assign("nowpage", $nowpage);
        $this->assign("allcount", $allusercount);
        $this->assign("hadcount", $hadusercount);
        $this->assign("list", $result['list']);
        $this->assign("page", $result['page']);
        $this->display();
    }


    //用户详情页
    public function userDetail()
    {
        $id = I('get.id');
        if (!$id) {
            $this->_error("未获取到用户id");
        }

        //基本信息
        $basicinfo = $this->ucenterUserLogic->getUserBasicInfoById($id);
        if (!$basicinfo) {
            $this->_error("id有误，无该数据！");
        }
        $basicinfo['state'] = intval($basicinfo['status']);


        //收货地址
        $malladdresslogic = new MallAddressLogicModel();
        $addresslist = $malladdresslogic->getAllAddressListByuuid($basicinfo['uuid']);

        if (count($addresslist) > 0) {
            $basicinfo['hadaddress'] = 1;
        } else {
            $basicinfo['hadaddress'] = 0;
        }

        $this->assign('addresslist', $addresslist);
        $this->assign('basicinfo', $basicinfo);

        //产品线信息
        $platforminfo = $this->ucenterUserLogic->getPlatformUserinfo($basicinfo['uuid']);
        $this->assign('platforminfo', $platforminfo);


        $this->display();
    }


    //验证
    private function checktime($param)
    {
        //注册时间验证
        if ($param["zhuce_start"] && $param["zhuce_end"]) {
            if ($param['zhuce_start'] > $param['zhuce_end']) {
                $this->_error("开始时间不能大于结束时间");
            }
            $oneyear = date('Y-m-d', strtotime($param["zhuce_end"] . ' -1 year'));      //结束日期一年前的日期
            if ($param["zhuce_start"] < $oneyear) {
                $this->_error("时间跨度不能超过1年");
            }
            $param["zhuce_start"] = strtotime($param["zhuce_start"]);
            $param["zhuce_end"] = strtotime($param["zhuce_end"] . " 23:59:59");
        } elseif ($param["zhuce_start"] && !$param["zhuce_end"]) {      //有开始时间-没有结束时间
            $today = date("Y-m-d", time());
            $oneyear = date('Y-m-d', strtotime($today . ' -1 year'));       //今日的 一年前日期
            if ($param["zhuce_start"] < $oneyear) {
                $this->_error("时间跨度不能超过1年");
            }
            $param["zhuce_start"] = strtotime($param["zhuce_start"]);
            $param["zhuce_end"] = strtotime($today . " 23:59:59");
        } elseif (!$param["zhuce_start"] && $param["zhuce_end"]) {      //有结束时间-没有开始时间
            $today = date("Y-m-d", time());
            $oneyear = date('Y-m-d', strtotime($param["zhuce_end"] . ' -1 year'));      //结束日期一年前的日期
            if ($today < $oneyear) {
                $this->_error("时间跨度不能超过1年");
            }
            $param["zhuce_start"] = strtotime($oneyear);
            $param["zhuce_end"] = strtotime($param["zhuce_end"] . " 23:59:59");
        } else {
            $param["zhuce_start"] = 0;
            $param["zhuce_end"] = 0;
        }

        //最后活跃时间验证
        if ($param["huoyue_start"] && $param["huoyue_end"]) {
            if ($param['huoyue_start'] > $param['huoyue_end']) {
                $this->_error("开始时间不能大于结束时间");
            }
            $oneyear = date('Y-m-d', strtotime($param["huoyue_end"] . ' -1 year'));      //结束日期一年前的日期
            if ($param["huoyue_start"] < $oneyear) {
                $this->_error("时间跨度不能超过1年");
            }
            $param["huoyue_start"] = strtotime($param["huoyue_start"]);
            $param["huoyue_end"] = strtotime($param["huoyue_end"] . " 23:59:59");
        } elseif ($param["huoyue_start"] && !$param["huoyue_end"]) {
            $today = date("Y-m-d", time());
            $oneyear = date('Y-m-d', strtotime($today . ' -1 year'));       //今日的 一年前日期
            if ($param["huoyue_start"] < $oneyear) {
                $this->_error("时间跨度不能超过1年");
            }
            $param["huoyue_start"] = strtotime($param["huoyue_start"]);
            $param["huoyue_end"] = strtotime($today . " 23:59:59");
        } elseif (!$param["huoyue_start"] && $param["huoyue_end"]) {
            $today = date("Y-m-d", time());
            $oneyear = date('Y-m-d', strtotime($param["huoyue_end"] . ' -1 year'));      //结束日期一年前的日期
            if ($today < $oneyear) {
                $this->_error("时间跨度不能超过1年");
            }
            $param["huoyue_start"] = strtotime($oneyear);
            $param["huoyue_end"] = strtotime($param["huoyue_end"] . " 23:59:59");
        } else {
            $param["huoyue_start"] = 0;
            $param["huoyue_end"] = 0;
        }


        //首次活跃时间
        if ($param["first_huoyue_start"] && $param["first_huoyue_end"]) {
            if ($param['first_huoyue_start'] > $param['first_huoyue_end']) {
                $this->_error("开始时间不能大于结束时间");
            }
            $oneyear = date('Y-m-d', strtotime($param["first_huoyue_end"] . ' -1 year'));      //结束日期一年前的日期
            if ($param["first_huoyue_start"] < $oneyear) {
                $this->_error("时间跨度不能超过1年");
            }
            $param["first_huoyue_start"] = strtotime($param["first_huoyue_start"]);
            $param["first_huoyue_end"] = strtotime($param["first_huoyue_end"] . " 23:59:59");
        } elseif ($param["first_huoyue_start"] && !$param["first_huoyue_end"]) {
            $today = date("Y-m-d", time());
            $oneyear = date('Y-m-d', strtotime($today . ' -1 year'));       //今日的 一年前日期
            if ($param["first_huoyue_start"] < $oneyear) {
                $this->_error("时间跨度不能超过1年");
            }
            $param["first_huoyue_start"] = strtotime($param["first_huoyue_start"]);
            $param["first_huoyue_end"] = strtotime($today . " 23:59:59");
        } elseif (!$param["first_huoyue_start"] && $param["first_huoyue_end"]) {
            $today = date("Y-m-d", time());
            $oneyear = date('Y-m-d', strtotime($param["first_huoyue_end"] . ' -1 year'));      //结束日期一年前的日期
            if ($today < $oneyear) {
                $this->_error("时间跨度不能超过1年");
            }
            $param["first_huoyue_start"] = strtotime($oneyear);
            $param["first_huoyue_end"] = strtotime($param["first_huoyue_end"] . " 23:59:59");
        } else {
            $param["first_huoyue_start"] = 0;
            $param["first_huoyue_end"] = 0;
        }

        return $param;

    }


    //拉黑操作
    public function pullBlack()
    {
        $param = I('post.');
        if (!$param['id']) {
            $this->ajaxReturn(array("error_code" => ApiConfig::CANNOT_GETDATA, "error_msg" => "id不能为空"));
        }

        if (!$param['remark']) {
            $this->ajaxReturn(array("error_code" => ApiConfig::CANNOT_GETDATA, "error_msg" => "拉黑原因不能为空"));
        }

        //获取用户状态
        $statusinfo = $this->ucenterUserLogic->getUcenterUserstatusById($param['id']);
        if (!$statusinfo) {
            $this->ajaxReturn(array("error_code" => ApiConfig::REQUEST_FAILL, "error_msg" => "id有误，未获取到用户数据！"));
        }

        if ($statusinfo['status'] == -1) {
            $this->ajaxReturn(array("error_code" => ApiConfig::REQUEST_FAILL, "error_msg" => "操作失败，该用户已被拉黑！"));
        }

        //拉黑action
        $result = $this->ucenterUserLogic->pullBlackAction($statusinfo['uuid'], $param['remark']);
        if ($result) {
            //发短信
            //获取手机号
            $phone = $this->ucenterUserLogic->getUserPhoneById($param['id']);
            $sendsmslogic = new SendSmsLogicModel();
            $sendstatus = $sendsmslogic->userPullBlackSendSms(1, $phone, $param['remark']);

            //记录操作日志？
            $logadminlogicmodel = new LogAdminLogicModel();
            $logadminlogicmodel->addLog('用户中心管理拉黑用户', 'pullblack', $param, $param['id']);

            //下线所有登陆
            $this->ucenterUserLogic->loginOutAll($statusinfo['uuid']);

            $this->ajaxReturn(array("error_code" => ApiConfig::REQUEST_SUCCESS, "error_msg" => "操作成功！"));
        } else {
            $this->ajaxReturn(array("error_code" => ApiConfig::REQUEST_FAILL, "error_msg" => "操作失败！"));
        }

    }


    //取消拉黑
    public function cancelPullBlack()
    {
        $param = I('post.');
        if (!$param['id']) {
            $this->ajaxReturn(array("error_code" => ApiConfig::CANNOT_GETDATA, "error_msg" => "id不能为空"));
        }

        //获取用户状态
        $statusinfo = $this->ucenterUserLogic->getUcenterUserstatusById($param['id']);
        if (!$statusinfo) {
            $this->ajaxReturn(array("error_code" => ApiConfig::REQUEST_FAILL, "error_msg" => "id有误，未获取到用户数据！"));
        }

        if ($statusinfo['status'] == 1) {
            $this->ajaxReturn(array("error_code" => ApiConfig::REQUEST_FAILL, "error_msg" => "操作失败，该用户未被拉黑！"));
        }
        $result = $this->ucenterUserLogic->cancelPullBlack($statusinfo['uuid']);
        if ($result) {
            //发短信
            //获取手机号
            $phone = $this->ucenterUserLogic->getUserPhoneById($param['id']);
            $sendsmslogic = new SendSmsLogicModel();
            $sendstatus = $sendsmslogic->userPullBlackSendSms(2, $phone, $param['remark']);
            //记录操作日志？
            $logadminlogicmodel = new LogAdminLogicModel();
            $logadminlogicmodel->addLog('取消用户中心管理拉黑用户', 'cancelpullblack', $param, $param['id']);
            $this->ajaxReturn(array("error_code" => ApiConfig::REQUEST_SUCCESS, "error_msg" => "操作成功！"));
        } else {
            $this->ajaxReturn(array("error_code" => ApiConfig::REQUEST_FAILL, "error_msg" => "操作失败！"));
        }
    }

    //修改用户类型
    public function changeUserType()
    {
        $param = I('post.');
        if (!$param['id']) {
            $this->ajaxReturn(array("error_code" => ApiConfig::CANNOT_GETDATA, "error_msg" => "id不能为空"));
        }

        //获取用户状态
        $statusinfo = $this->ucenterUserLogic->getUcenterUserstatusById($param['id']);
        if (!$statusinfo) {
            $this->ajaxReturn(array("error_code" => ApiConfig::REQUEST_FAILL, "error_msg" => "id有误，未获取到用户数据！"));
        }
        $result = $this->ucenterUserLogic->changeUserType($statusinfo['uuid'], $param['type']);
        if ($result) {

            //记录操作日志？
            $logadminlogicmodel = new LogAdminLogicModel();
            $logadminlogicmodel->addLog('修改用户中心用户类型', 'changeusertype', $param, $param['id']);
            $this->ajaxReturn(array("error_code" => ApiConfig::REQUEST_SUCCESS, "error_msg" => "操作成功！"));
        } else {
            $this->ajaxReturn(array("error_code" => ApiConfig::REQUEST_FAILL, "error_msg" => "操作失败！"));
        }
    }

    //根据用户uuid获取用户所有的收货地址
    public function getAddressListByUuid()
    {
        $malladdresslogic = new MallAddressLogicModel();
        $uuid = I('get.uuid');
        $addresslist = $malladdresslogic->getAllAddressListByuuid($uuid);
        $this->ajaxReturn(array("error_code" => ApiConfig::REQUEST_SUCCESS, "error_msg" => "操作成功！", "data" => $addresslist));

    }


}