<?php
// +----------------------------------------------------------------------
// | Common
// +----------------------------------------------------------------------
namespace app\index\controller;

use app\common\enum\BaseStatusCodeEnum;
use app\common\model\logic\AdminuserLogic;
use app\common\model\logic\CompanyLogic;
use app\common\model\logic\OrdersLogic;
use app\common\model\logic\QuyuLogic;
use think\Collection;
use think\Controller;
use think\Request;

class Common extends Controller
{

    /**
     * 检索 城市（默认所有城市，可查询具体城市）
     *
     * @param QuyuLogic $quyuLogic
     * @return array
     */
    public function citys(QuyuLogic $quyuLogic)
    {
        $cid = $this->request->get('cid', '');
        $lists = $quyuLogic->getAllCitys($cid);
        $lists = array_map(function ($value) {
            return [
                'cid' => $value['cid'],
                'cname' => strtoupper(substr($value['px_abc'], 0, 1)) . ' ' . $value['cname'],
                'value' => strtoupper(substr($value['px_abc'], 0, 1)) . ' ' . $value['cname'],
                'true_cname' => $value['cname'],
                'city_name' => strtoupper(substr($value['px_abc'], 0, 1)) . ' ' . $value['cname'],
            ];
        }, Collection::make($lists)->toArray());

        return sys_response(0, '', [
            $lists
        ]);
    }

    /**
     * 检索 地区（根据城市ID获取管辖地址）
     *
     * @param QuyuLogic $quyuLogic
     * @return array
     */
    public function areas(QuyuLogic $quyuLogic)
    {
        $cid = $this->request->get('cid', '');
        $lists = $quyuLogic->getArea($cid);
        $lists = array_map(function ($value) {
            $value['value'] = $value['area'];
            return $value;
        }, Collection::make($lists)->toArray());

        return sys_response(0, '', [
            $lists
        ]);
    }

    /**
     * 检索 装修公司（根据装修公司ID/简称/全称 获取装修公司）
     *
     * @param QuyuLogic $quyuLogic
     * @return array
     */
    public function finduser(CompanyLogic $userLogic)
    {
        $id = $this->request->post('uid','');
        $pageSize = $this->request->post('psize', 10,'intval');
        if (empty($id)) {
            return sys_response(4000002);
        }
        $lists = $userLogic->getListUser($id, $pageSize);
        return sys_response(0, '', [
            $lists
        ]);
    }

    /**
     * 检索 管理员（根据管理员 ID/名称/账号 获取管理员）
     *
     * @param Request $request
     * @param QuyuLogic $quyuLogic
     * @return array
     */
    public function findadmin(AdminuserLogic $userLogic)
    {
        $id = $this->request->post('uid','');
        $status = $this->request->post('status');
        $pageSize = $this->request->post('psize', 10,'intval');
        if (empty($id)) {
            return sys_response(4000002);
        }
        $lists = $userLogic->getListUser($id, $pageSize, false, $status);
        return sys_response(0, '', [
            $lists
        ]);
    }

    /**
     * 获取无会员城市列表
     *
     * @return mixed
     */
    public function novipCitys(QuyuLogic $quyuLogic)
    {
        $cid = $this->request->get('cid', '');
        $lists = $quyuLogic->noVipCitys($cid);
        $lists = array_map(function ($value) {
            return [
                'cid' => $value['cid'],
                'cname' => $value['cname'],
                'city_name' => $value['first_abc'] . ' ' . $value['cname'],
                'value' => $value['first_abc'] . ' ' . $value['cname'],
            ];
        }, Collection::make($lists)->toArray());
        return sys_response(0, '', [
            $lists
        ]);
    }


    /**
     * 获取登陆用户的管辖城市
     * @param QuyuLogic $quyuLogic
     * @return array
     */
    public function getAdminCitys(QuyuLogic $quyuLogic,Request $request)
    {
        $adminuserlogic = new AdminuserLogic();
        $cid = $this->request->get('cid', '');
        if(!$cid){
            $cid = $adminuserlogic->getCityIds();
        }
        $lists = $quyuLogic->getAllCitys($cid);
        $lists = array_map(function ($value) {
            return [
                'cid' => $value['cid'],
                'cname' => strtoupper(substr($value['px_abc'], 0, 1)) . ' ' . $value['cname'],
                'value' => strtoupper(substr($value['px_abc'], 0, 1)) . ' ' . $value['cname'],
                'true_cname' => $value['cname'],
                'city_name' => strtoupper(substr($value['px_abc'], 0, 1)) . ' ' . $value['cname'],
            ];
        }, Collection::make($lists)->toArray());

        return sys_response(0, '', [
            $lists
        ]);
    }

    // 权限城市
    public function getPrivCitys(QuyuLogic $quyuLogic, Request $request){
        $adminuserlogic = new AdminuserLogic();
        $citys = $adminuserlogic->getCityIds();

        if (empty($citys)) {
            return sys_response(0, '', [
                "list" => []
            ]);
        }

        if ($cid = $this->request->get('cid', '')) {
            if (!in_array($cid, $citys)) {
                return sys_response(0, '', [
                    "list" => []
                ]);
            }
            $citys = $cid;
        }

        $lists = $quyuLogic->getAllCitys($citys);
        $lists = array_map(function ($value) {
            $px_abc = strtoupper(substr($value['px_abc'], 0, 1));
            return [
                'cid' => $value['cid'],
                'cname' => $px_abc . ' ' . $value['cname'],
                'value' => $px_abc . ' ' . $value['cname'],
                'true_cname' => $value['cname'],
                'city_name' => $px_abc . ' ' . $value['cname'],
            ];
        }, Collection::make($lists)->toArray());

        return sys_response(0, '', [
            "list" => $lists
        ]);
    }


	public function getYuSuan()
    {
        $logic = new OrdersLogic();
        $result = $logic->getYuSuan();
        return sys_response(0, BaseStatusCodeEnum::CODE_0, [
            $result
        ]);
    }

    public function getFangshi()
    {
        $logic = new OrdersLogic();
        $result = $logic->getFangshi();
        return sys_response(0, BaseStatusCodeEnum::CODE_0, [
            $result
        ]);
    }
}