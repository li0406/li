<?php
/**
 * Created by PhpStorm.
 * User: mcj
 * Date: 2018/7/17
 * Time: 13:33
 */
namespace Mobile\Controller;

use Common\Enums\ApiConfig;
use Mobile\Common\Controller\MobileBaseController;

class CompanyConsultController extends MobileBaseController
{
    public function _initialize()
    {
        parent::_initialize();
    }


    public function zhaoshang()
    {
        $this->display();
    }

    public function consult()
    {
        $data = I('post.');
        $vaildate = $this->_consultVaildate($data);
        if ($vaildate['result'] === false) {
            $this->ajaxReturn(['status' => ApiConfig::PARAMETER_ILLEGAL, 'info' => $vaildate['mes']]);
        }
        $ip_info_id = D('CompanyIPInfo', 'Logic')->getIPInfo();
        $company_consult_logic = D('CompanyConsult', 'Logic');
        if (!$company_consult_logic->touchUs($data, $ip_info_id)) {
            $this->ajaxReturn(['status' => ApiConfig::REQUEST_FAILL, 'info' => '录入信息失败']);
        }
        $this->ajaxReturn(['status' => ApiConfig::REQUEST_SUCCESS, 'info' => '操作成功']);
    }

    private function _consultVaildate($data)
    {
        //验证城市
        if (!empty($data['cs'])) {
            if (!is_numeric($data['cs'])) {
                return ['result' => false, 'mes' => '城市数据异常'];
            }
        }
        //验证区域
        if (!empty($data['qx'])) {
            if (!is_numeric($data['qx'])) {
                return ['result' => false, 'mes' => '区数据异常'];
            }
        }
        //公司名称
        if (empty($data['name']) || mb_strlen($data['name'], 'utf-8') > 50) {
            return ['result' => false, 'mes' => '装修公司数据有误'];
        }
        //联系电话
        if (empty($data['tel']) || !preg_match('/^1\d{10}$/', $data['tel'])) {
            return ['result' => false, 'mes' => '手机号格式不正确'];
        }
        //合作类型
        if (!empty($data['cooperation_type']) && $data['cooperation_type'] != 2) {
            return ['result' => false, 'mes' => '合作类型异常'];
        }
        //联系人
        if (empty($data['linkman']) || mb_strlen($data['linkman'], 'utf-8') > 50) {
            return ['result' => false, 'mes' => '联系人格式不正确'];
        }
        //手输地址
        if (!empty($data['custom_address'])) {
            if (mb_strlen($data['custom_address'], 'utf-8') > 255 || !empty($data['cs'])) {
                return ['result' => false, 'mes' => '输入地址格式不对'];
            }
        }
        return ['result' => true, 'mes' => '验证通过'];
    }

}