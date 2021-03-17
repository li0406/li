<?php

namespace app\index\controller\v1;

use app\common\enum\BaseStatusCodeEnum;
use app\common\model\logic\CompanyLogic;
use app\common\model\logic\AdminuserLogic;
use app\common\model\logic\UserVipLogic;
use app\common\model\logic\OrderLogic;
use app\common\model\logic\OrdersLogic;
use app\common\model\logic\QuyuLogic;
use app\index\validate\ReportCompany as ReportCompanyValidate;
use app\index\validate\Company as CompanyValidate;
use think\Controller;
use think\Request;

Class Company extends Controller
{

    /**
     * 获取会员列表
     *
     * @param Request $request
     * @return array
     */
    public function company_list(Request $request,CompanyLogic $companyLogic)
    {
        if (empty($request->company)) {
            return [sys_response(4000002)];
        }
        $info = $companyLogic->getCompanys($request->company);
        if (count($info) > 0) {
            return sys_response(0,BaseStatusCodeEnum::CODE_0,$info);
        }else{
            return sys_response(4000001);
        }
    }

    /**
     * 获取客户维护列表
     *
     * @param Request $request
     * @return array
     */
    public function company_list_all(Request $request, CompanyLogic $companyLogic)
    {
        $validate = new ReportCompanyValidate();
        if (!$validate->scene('AllReportCompany')->check($request->get())) {
            return ['error_code' => 4000002, 'error_msg' => BaseStatusCodeEnum::CODE_4000002 . '：' . $validate->getError()];
        }
        return $companyLogic->getAllCompanys($request->get());
    }

    /**
     * 获取会员信息
     *
     * @param Request $request
     * @return array
     */
    public function company_info(Request $request, CompanyLogic $companyLogic)
    {
        if (empty($request->company_id)) {
            return sys_response(4000002);
        }
        $info = $companyLogic->getInfoById($request->company_id);
        if (count($info) > 0) {
            return sys_response(0,BaseStatusCodeEnum::CODE_0,$info);
        }else{
            return sys_response(4000001);
        }
    }

    /**
     * 获取老会员列表
     *
     * @param Request $request
     * @return array
     */
    public function company_old_list(Request $request,CompanyLogic $companyLogic)
    {
        if (empty($request->company_name)) {
            return sys_response(4000002);
        }
        $info = $companyLogic->getOldCompanys($request->company_name);
        if (count($info) > 0) {
            return sys_response(0,BaseStatusCodeEnum::CODE_0,$info);
        }else{
            return sys_response(4000001);
        }
    }

    /**
     * 获取销售人员列表
     *
     * @param Request $request
     * @return array
     */
    public function user_list_sale(Request $request,AdminuserLogic $adminLogic)
    {
        if (empty($request->user_name)) {
            return sys_response(4000002);
        }
        $info = $adminLogic->getSaleList($request->user_name);
        if (count($info) > 0) {
            return sys_response(0,BaseStatusCodeEnum::CODE_0,$info);
        }else{
            return sys_response(4000001);
        }
    }

    /**
     * 验证会员是否被绑定
     *
     * @param Request $request
     * @return array
     */
    public function company_is_repeat(Request $request,CompanyLogic $companyLogic)
    {
        if (empty($request->company_id)) {
            return sys_response(4000002);
        }
        $info = $companyLogic->getCompanyRepeat($request->company_id);
        if (count($info) > 0) {
            return sys_response(0,BaseStatusCodeEnum::CODE_0,$info);
        }else{
            return sys_response(4000001);
        }
    }

    /**
     * 获取无会员城市列表
     *
     * @param Request $request
     * @return array
     */
    public function no_vip_citys(Request $request, QuyuLogic $quyuLogic)
    {
        $p = $request->get('p', 1, 'intval');
        $psize = $request->get('psize', 20, 'intval');
        $down = $request->get('down', '');
        return $quyuLogic->getNoVip($request->get(),$down,$p,$psize);
    }

    /**
     * 获取无会员城市订单列表
     *
     * @param Request $request
     * @param OrderLogic $orderLogic
     * @return array
     */
    public function no_vip_city_orders(Request $request, OrderLogic $orderLogic)
    {
        $p = $request->get('p', '1', 'intval');
        $psize = $request->get('psize', '20', 'intval');
        $down = $request->get('down', '');
        return $orderLogic->getOrdersList($request->get(), $down, $p, $psize,true);
    }

    public function company_expire(Request $request)
    {
        //获取当前用户管辖城市
        $citys = AdminuserLogic::getCityIds();
        if(count($citys) > 0){
            $company = new CompanyLogic();
            return $company->getVipCompany($request->param(),$citys);
        }else{
            return sys_response(4000001);
        }
    }

    /**
     * 会员到期提醒PC端
     *
     * @param Request $request
     * @param OrderLogic $orderLogic
     * @return array
     */
    public function expire_remind_pc()
    {
        $p = $this->request->get('page', '1', 'intval');
        $psize = $this->request->get('page_count', '20', 'intval');
        $company = new CompanyLogic();
        return $company->getVipUserPc($this->request->get(), $p, $psize);
    }


    // 查询用户名是否被占用
    public function check_user(CompanyLogic $companylogic)
    {
        $user_name = input('get.user_name'); // 装修公司用户名
        if(empty($user_name)){
            return sys_response(4000002,BaseStatusCodeEnum::CODE_4000002,[]);
        }else{
            $result = $companylogic->check_user($user_name);
            if($result === true){
                return sys_response(0,BaseStatusCodeEnum::CODE_0,[]);
            }else{
                return sys_response(4000500,BaseStatusCodeEnum::CODE_4000500,[]);
            }
        }
    }

    // 装企注册
    public function registerCompany(CompanyLogic $companylogic,Request $request)
    {
        $param = input('post.');

        // 验证
        $validate = new CompanyValidate();
        if (!$validate->scene('AddCompany')->check($param)) {
            return ['error_code' => 4000019, 'error_msg' => BaseStatusCodeEnum::CODE_4000019 . '：' . $validate->getError()];
        }

        //验证图形验证码
        // if (empty($param['verify'])) {
        //     return sys_response(4000017,BaseStatusCodeEnum::CODE_4000017,[]);
        // } else {
        //     //验证图形验证码
        //     if (!captcha_check($param['verify'], $param['ssid'])) {
        //         return sys_response(4000018,BaseStatusCodeEnum::CODE_4000018,[]);
        //     }
        // }
        if(md5($param['password']) != md5($param['repassword'])){
            return ['error_code' => 4000019, 'error_msg' => BaseStatusCodeEnum::CODE_4000019 . '： 确认密码和密码不一致'];
        }

        //添加逻辑
        $registerdata = [];
        $registerdata['classid'] = (isset($param['classid']) && $param['classid']) ? $param['classid'] : 3;     //用户表示，默认为3  装修公司
        $registerdata['cs'] = $param['cs'];
        $registerdata['qx'] = $param['qx'];
        $registerdata['user'] = $param['account'];
        $registerdata['pass'] = md5($param['password']);
        $registerdata['jc'] = $param['jc'];
        $registerdata['qc'] = $param['qc'];
        $registerdata['name'] = $param['name'];
        $registerdata['tel'] = $param['tel'];
        $registerdata['tel_safe'] = $param['tel'];
        $registerdata['tel_safe_chk'] = 1;
        $registerdata['register_time'] = time();
        $registerdata['ip'] = get_client_ip();
        $registerdata['user_type'] = 3;     //用户注册来源类型：1 默认 2网编帐号 3装修公司后台注册  4管理后台(168)注册  5家具用户中心注册'
        $registerdata['qq'] = (isset($param['qq']) && $param['qq']) ? $param['qq'] : "";
        $registerdata['qq1'] = (isset($param['qq2']) && $param['qq2']) ? $param['qq2'] : "";
        $registerdata['logo'] = "https://".config("QINIU_DOMAIN")."/".OP('DEFAULT_COMPANY_LOGO');
        $registerdata['register_admin_id'] = $request->user['user_id'];
        $result = $companylogic->registerCompany($registerdata);
        if($result === true){
            return sys_response(0,BaseStatusCodeEnum::CODE_0,[]);
        }else{
            return sys_response(5000002,BaseStatusCodeEnum::CODE_5000002,[]);
        }
    }

    // 获取装修公司分单数
    public function getCompanyAllotOrders(Request $request){
        $company_id = $request->get("company_id");
        $date_begin = $request->get("date_begin");
        $date_end = $request->get("date_end");

        if (empty($company_id) || empty($date_begin) || empty($date_end)) {
            return json(sys_response(4000002));
        }

        $orderLogic = new OrderLogic();
        $orders = $orderLogic->getCompanyOrderAllotCount($company_id, $date_begin, $date_end);
        $response = sys_response(0, "", $orders);
        return json($response);
    }

    // 获取装修公司历史合同时间
    public function getCompanyVipHistoryList(Request $request, UserVipLogic $userVipLogic){
        $company_id = $request->get("company_id");
        if (empty($company_id)) {
            return json(sys_response(4000002));
        }

        $today = date("Y-m-d");
        $list = $userVipLogic->getCompanyContractList($company_id, $today);

        $response = sys_response(0, "", [
            "list" => $list
        ]);

        return json($response);
    }
}