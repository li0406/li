<?php
// +----------------------------------------------------------------------
// | DeliveryPoolController  派单池
// +----------------------------------------------------------------------
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\OrderPondLogicModel;
use Home\Model\Logic\OrderSourceInLogicModel;

class OrderpondController extends HomeBaseController
{
    /**
     * 派单池配置页面
     */
    public function config()
    {
        $model = new OrderPondLogicModel();
        //获取客服名单
        $kfList = D('Adminuser')->getKfList();
        //获取城市信息
        $cityList = A('Home/Api')->getAllCityInfo();
        $kfNum = count($kfList);
        $cityNum = count($cityList);
        //获取订单池列表
        $pondList = $model->getPondList();
        $all = [];

        //计算剩余城市和客服数量
        foreach ($pondList as $key=>$value) {
              $all["city"][$value["pond_type"]] += $value["city_num"];
              $all["kf"] += $value["kf_num"];
              $pondId[] = $value["id"];
        }

        foreach ($pondList as $key => $value) {
            $pondList[$key]["un_city_num"] = $cityNum - $all["city"][$value["pond_type"]];
            $pondList[$key]["un_kf_num"] = $kfNum - $all["kf"];
        }

        //获取剩余可派单数量
        $pondList = $model->getRemainingOrder($pondId,$pondList);

        //获取订单标识
        $inLogic = new OrderSourceInLogicModel();
        $orderType = $inLogic->getSourceInSelect();

        $this->assign('orderType', $orderType);
        $this->assign('list',$pondList);
        $this->display();
    }

    /**
     * 订单池添加
     */
    public function addPond()
    {
        if (IS_POST) {
            $data = I('post.');
            $vali = D('Home/Logic/OrderPondLogic')->validatePond($data);
            if ($vali !== true) {
                $this->ajaxReturn($vali);
            }
            $data['create_user'] = session('uc_userinfo.id');
            $data['update_time'] = $data['create_time'] = time();
            $flag = D('Home/Logic/OrderPondLogic')->addOrderPond($data);
            if ($flag !== false) {
                $this->ajaxReturn(['status' => 1, 'info' => '订单池新增成功']);
            } else {
                $this->ajaxReturn(['status' => 0, 'info' => '订单池保存失败']);
            }
        }
        $this->ajaxReturn(['status' => 0, 'info' => '请求错误']);
    }

    /**
     * 订单池编辑
     */
    public function editPond()
    {
        if (IS_POST) {
            $data = I('post.');
            $vali = D('Home/Logic/OrderPondLogic')->validatePond($data, 1);
            if ($vali !== true) {
                $this->ajaxReturn($vali);
            }
            $data['sort'] = intval($data['sort']);
            $data['update_time'] = time();
            $flag = D('Home/Logic/OrderPondLogic')->addOrderPond($data, ['id' => $data['id']]);

            if ($flag !== false) {
                $this->ajaxReturn(['status' => 1, 'info' => '订单池操作成功']);
            } else {
                $this->ajaxReturn(['status' => 0, 'info' => '订单池保存失败']);
            }
        }
        $this->ajaxReturn(['status' => 0, 'info' => '请求错误']);
    }

    /**
     * 删除订单池
     */
    public function delPond()
    {
        if (IS_POST) {
            $ids = I('post.id');
            if (empty($ids)) {
                $this->ajaxReturn(['status' => 0, 'info' => '参数错误']);
            }
            if ($ids == 1 || stripos('1',$ids)) {
                $this->ajaxReturn(['status' => 0, 'info' => '主订单池不能删除']);
            }
            $flag = D('Home/Logic/OrderPondLogic')->delOrderPond($ids);
            if ($flag !== false) {
                $this->ajaxReturn(['status' => 1, 'info' => '订单池删除成功']);
            } else {
                $this->ajaxReturn(['status' => 0, 'info' => '订单池删除失败']);
            }
        }
        $this->ajaxReturn(['status' => 0, 'info' => '请求错误']);
    }

    /**
     * 派单池详情页面
     */
    public function configdetail()
    {
        $id = I('get.id');
        $pondModel = new OrderPondLogicModel();

        //获取城市信息
        $cityList = A('Home/Api')->getAllCityInfo();
        //获取订单池详情
        $pondDetail = $pondModel->getPondDetail($id);

        //获取当前可分配的城市信息
        $cityList = $pondModel->getPondUseCityList($cityList,$id,$pondDetail["detail"]["pond_type"]);

        //获取客服名单
        $kfList = D('Adminuser')->getKfList();
        //获取当前可分配的客服信息
        $kfList =$pondModel->getPondUseServiceList($kfList,$id);

        //获取订单标识
        $inLogic = new OrderSourceInLogicModel();
        $orderType = $inLogic->getSourceInSelect();

        $this->assign('orderType', $orderType);
        $this->assign('kfList',$kfList);
        $this->assign('cityList',$cityList);
        $this->assign('detail',$pondDetail['detail']);
        $this->assign('checkkf',array_column($pondDetail['kflist'],'kf_id'));
        $this->assign('checkcity',array_column($pondDetail['cityList'],'city_id'));
        $this->display();
    }

    /**
     * 添加客服和城市
     */
    public function addCityAndServ()
    {
        if (IS_POST) {
            $data = I('post.');
            $vali = D('Home/Logic/OrderPondLogic')->validatePondDetail($data);
            if ($vali !== true) {
                $this->ajaxReturn($vali);
            }
            $flag = D('Home/Logic/OrderPondLogic')->addCityAndServ($data);
            if ($flag !== false) {
                if (empty($flag)) {
                    $flag = "分配成功";
                }
                $this->ajaxReturn(['status' => 1, 'info' => $flag]);
            } else {
                $this->ajaxReturn(['status' => 0, 'info' => '分配失败']);
            }
        }
        $this->ajaxReturn(['status' => 0, 'info' => '请求错误']);
    }
}