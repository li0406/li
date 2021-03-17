<?php
// +----------------------------------------------------------------------
// |  ERP营销落地页
// +----------------------------------------------------------------------
namespace Home\Controller;

use Common\Model\Logic\QuyuLogicModel;
use Common\Model\Logic\ErpUserRegisterLogicModel;
use Home\Common\Controller\HomeBaseController;
use Common\Validate\ErpUserValidateModel;

class ErpMarketController extends HomeBaseController
{
    /**
     * ERP营销落地页
     *
     * @return void
     */
    public function index()
    {
        //获取所有省份
//        $model = new QuyuLogicModel();
//
//        $province = $model->getAllProvince();
//        if (count($province) > 0) {
//            $province_id = $province[0]['id'];
//            $city = $model->getProvinceCityList($province_id);
//            $this->assign('city', $city);
//        }
//
//        $this->assign('province', $province);
        $this->display();
    }

    /**
     * ERP营销落地页注册
     *
     * @return void
     */
    public function register()
    {
        if (IS_POST) {
            $data = [
                'name' => I('post.name'),
                'company_name' => I('post.company_name','','remove_xss'),
                'tel' => I('post.tel','','remove_xss'),
                'city_id' => I('post.city_id','','remove_xss'),
                'province_id' => I('post.province_id','','remove_xss'),
                'time' => time(),
                'source' => "pc端",
            ];

            $validate = new ErpUserValidateModel();
            //验证规则
            $rules = [
                ['name', 'require', '姓名未填写', 1],
                ['company_name', 'require', '公司名称未填写', 1],
                ['tel', '/1\d{10}/', '请输入正确的手机号', 1, 'regex'],
//                ['city_id', 'require', '请选择省', 1],
//                ['province_id', 'require', '请选择市', 1],
            ];

            if (!$validate->validate($rules)->create($data)) {
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->ajaxReturn(array('error_code' => 9000001, 'error_msg' => $validate->getError()));
            }

            $model = new ErpUserRegisterLogicModel();
            $i = $model->register($data);

            if ($i !== false) {
                $this->ajaxReturn(array('error_code' => 0, 'error_msg' => '操作成功'));
            }
            $this->ajaxReturn(array('error_code' => 9000001, 'error_msg' => '服务器出去旅游了，稍后回来！~'));
        }
    }

    /**
     * ERP营销落地页城市获取
     *
     * @return void
     */
    public function getCity()
    {
        if (IS_POST) {
            $province_id = I('post.id');
            $model = new QuyuLogicModel();
            $city = $model->getProvinceCityList($province_id);
            if (count($city) > 0) {
                $this->ajaxReturn(array('error_code' => 0, 'error_msg' => '操作成功', 'data' => $city));
            }
        }
        $this->ajaxReturn(array('error_code' => 9000001, 'error_msg' => '服务器出去旅游了，稍后回来！~'));
    }
}
    
