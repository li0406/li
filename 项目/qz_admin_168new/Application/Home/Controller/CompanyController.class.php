<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\CompanyEmployeeLogicModel;
use Home\Model\Logic\AdminuserLogicModel;
use Home\Model\Logic\CompanyLogicModel;
use Home\Model\Service\MsgServiceModel;

use Common\Enums\MsgTemplateCodeEnum;
use Common\Enums\DepartmentEnum;

class CompanyController extends HomeBaseController
{
    /**
     * 非会员信息管理
     *
     * @return void
     */
    public function index()
    {
        ini_set("max_execution_time", "0");

        //获取城市信息
        $citys = A('Home/Api')->getAllCityInfo();
        $this->assign('citys', $citys);

        $adminLogic = new AdminuserLogicModel();
        $this->assign('seolist', $adminLogic->getSeoList());

        $post = I('get.','','trim');
        $logic = new CompanyLogicModel();
        $list = $logic->getAllCompanyList($post);

        $this->assign('list', $list['list']);
        $this->assign('page', $list['page']);

        $parse =  parse_url($_SERVER['REQUEST_URI']);
        $this->assign('query',isset($parse['query']) ? $parse['query'] : '');
        $this->display();
    }

    /**
     * 修改公司状态
     *
     * @return void
     */
    public function changeCompanyState()
    {
        $id = I('post.id');
        $state = I('post.state');
        if (!empty($id)) {
            $logic = new CompanyLogicModel();
            $flag = $logic->changeCompanyState($id, $state);
            if ($flag) {
                $logic->saveOpInfoLog(['company_id' => $id, 'status' => $state]);
                $this->ajaxReturn(array('data' => '', 'info' => '操作成功！', 'status' => 1));
            } else {
                $this->ajaxReturn(array('data' => '', 'info' => '操作失败', 'status' => 0));
            }
        }
        $this->ajaxReturn(array('data' => '', 'info' => '操作失败', 'status' => 0));
    }

    /**
     * 进入用户中心
     *
     * @return void
     */
    public function getInUser()
    {
        $id = I('post.id');
        if (!empty($id)) {
            $logic = new CompanyLogicModel();
            $is_real = $logic->getRealCompany($id);

            if ($is_real) {
                //实例化redis
                $redis = new \Redis();
                //连接
                $redis->connect(C('REDIS_HOST'), C('REDIS_PORT'));
                $redis->set('u_userInfo', $id);
                $redis->expire('u_userInfo', 60);
                $this->ajaxReturn(array('data' => $id, 'info' => '操作成功！', 'status' => 1));
            } else {
                $this->ajaxReturn(array('data' => '', 'info' => '操作失败！', 'status' => 0));
            }
        }
        $this->ajaxReturn(array('data' => '', 'info' => '操作失败！', 'status' => 0));
    }

    /**
     * 公司信息导出
     *
     * @return void
     */
    public function exportCompanyInfo()
    {
        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            ob_start();
            $writer = new \XLSXWriter();
            //标题
            $herder = array(
                '公司ID',
                '公司全称',
                '公司简称',
                '所属站点',
                '城市+区县',
                '状态',
                '创建时间',
                '完善状态',
                '操作人',
                '更新时间'
            );
            $writer->writeSheetRow('Sheet1', $herder);
            //数据
            $post = I('get.');
            $logic = new CompanyLogicModel();
            $list = $logic->getAllCompanyList($post,1);
            unset($list['page']);
            foreach ($list['list'] as $key => $value) {
                $dd['id'] = $value['id'];
                $dd['qc'] = !empty($value['qc']) ? $value['qc'] : '';
                $dd['jc'] = !empty($value['jc']) ? $value['jc'] : '';
                $dd['cname'] = !empty($value['cname']) ? $value['cname'] : '';
                $dd['qz_area'] = $value['cname'] . '-' . $value['qz_area'];
                if ($value['fake'] == 0 && $value['on'] == 2) {
                    $dd['status'] = '真会员';
                } elseif ($value['fake'] != 0 && $value['on'] == 2) {
                    $dd['status'] = '假会员';
                } else {
                    $dd['status'] = '非会员';
                }
                $dd['register_time'] = date('Y-m-d', $value['register_time']);
                $dd['state'] = $value['state'] == 1 ? '未修改' : ($value['state'] == 2 ? '修改中' : '已完善');
                $dd['op_info_name'] = !empty($value['op_info_name']) ? $value['op_info_name'] : '';
                $dd['op_info_last_time'] = !empty($value['op_info_last_time']) ? date('Y-m-d', $value['op_info_last_time']) : '';
                $wArr = array_values($dd);
                unset($list['list'][$key]);
                $writer->writeSheetRow('Sheet1', $wArr);
            }
            unset($list);
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="非会员公司信息列表.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut();
        }catch (\Exception $e){
            if($_SESSION["uc_userinfo"]["uid"] == 1){
                var_dump($e);
            }
        }
        exit();
    }

    public function banners()
    {
        $input = I('get.');
        $userinfo = session('uc_userinfo');

        $logic = new CompanyLogicModel();

        $cityList = $logic->getSelectedCitys();

        $banner_status = $logic->bannerStatus;
        $member_status = $logic->memberStatus;

        $result = $logic->getCompanyBannerList($input);

        //销售中心的不能操作按钮
        if ($userinfo["department_top_id"] != DepartmentEnum::DEPARTMENT_SALE_CENTER) {
            $this->assign('btn_show', 1);
        }else{
            $this->assign('btn_show', 0);
        }

        $this->assign('cityList', $cityList);
        $this->assign('banner_status', $banner_status);
        $this->assign('member_status', $member_status);
        $this->assign('banners', $result);
        $this->display();
    }

    // 装修公司banner审核
    public function makeBannerStatus() {
        $post = I('post.');

        $companyLogic = new CompanyLogicModel();
        $info = $companyLogic->getCompanyBannerInfo($post["banner_id"]);
        if (!empty($info)) {
            $result = $companyLogic->updateBannerStatus($post);
            if($result !== false){
                if ($post["mark"] == 2) {
                    // 查询有轮播图菜单权限的员工
                    $employeeLogic = new CompanyEmployeeLogicModel();
                    $employeeList = $employeeLogic->getCompanyMenuLinkEmployees($info["userid"], "/store-banner");
                    $employeeIds = array_column($employeeList, "employee_id");

                    // QZMSG 发送消息提醒
                    $msgService = new MsgServiceModel();
                    $template_id = MsgTemplateCodeEnum::EXAM_COMPANY_BANNER_FAIL;
                    $msgService->sendCompanyMsg($template_id, $info["userid"], "?", $post["banner_id"], "装修公司banner审核");

                    if (!empty($employeeIds)) {
                        $msgService->sendCompanyMsg($template_id, $info["userid"], "?", $post["banner_id"], "装修公司banner审核", [], $employeeIds);
                    }
                }

                $this->ajaxReturn(["status" => 1, "info" => ""]);
            }
        }

        $this->ajaxReturn(['status' => 0, 'info'=> '操作失败！']);
    }
}
