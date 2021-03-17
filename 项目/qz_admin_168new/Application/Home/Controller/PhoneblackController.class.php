<?php
// +----------------------------------------------------------------------
// | PhoneblackController
// +----------------------------------------------------------------------
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\OrderBlackLogicModel;

class PhoneblackController extends HomeBaseController
{
    protected $orderBlackLogic;

    public function __construct()
    {
        parent::__construct();
        $this->orderBlackLogic = new OrderBlackLogicModel();
    }

    public function index()
    {
        $assign = $this->orderBlackLogic->getList(I('get.'),20);
        $this->assign($assign);
        $this->assign('citys',D('Quyu')->getQuyuList());
        $this->display();
    }

    public function saveblack()
    {
        $post = I('post.');
        if (!empty($post['tel'])) {
            $find = $this->orderBlackLogic->getOneBlack($post['tel']);
            if (!empty($find)) {
                $this->ajaxReturn([
                    'error_code' => 500900,
                    'error_msg' => '手机号码已存在',
                    'data' => []
                ]);
            }
        }
        $flag = $this->orderBlackLogic->saveBlack($post);
        if ($flag === false) {
            $this->ajaxReturn([
                'error_code' => 500900,
                'error_msg' => '保存失败',
                'data' => []
            ]);
        }
        $this->ajaxReturn([
            'error_code' => 0,
            'error_msg' => '保存成功',
            'data' => []
        ]);
    }

    public function delblack()
    {
        $id = I('post.id', 0, 'intval');
        if (empty($id)) {
            $this->ajaxReturn([
                'error_code' => 500900,
                'error_msg' => '参数错误，请刷新重试',
                'data' => []
            ]);
        }
        $flag = $this->orderBlackLogic->delOneBlack($id);
        if ($flag === false) {
            $this->ajaxReturn([
                'error_code' => 500900,
                'error_msg' => '解禁失败',
                'data' => []
            ]);
        }
        $this->ajaxReturn([
            'error_code' => 0,
            'error_msg' => '解禁成功',
            'data' => []
        ]);
    }


    /**
     * 提交post excel文件保存
     */
    public function import()
    {
        $ex = $_FILES['excel'];
        if (empty($ex['size'])) {
            $this->ajaxReturn([
                'error_code' => 500900,
                'error_msg' => '请选择需要上传的文件',
                'data' => []
            ]);
        }
        $file = pathinfo($ex['name']);
        $extension = isset($file['extension']) ? $file['extension'] : '';
        if (!in_array(strtolower($extension), ['xlsx', 'xls'])) {
            $this->ajaxReturn([
                'error_code' => 500900,
                'error_msg' => '选择的文件格式不正确',
                'data' => []
            ]);
        }

        $filename = TEMP_PATH . time() . substr($ex['name'], stripos($ex['name'], '.'));
        move_uploaded_file($ex['tmp_name'], $filename);
        $excelData = import_excel($filename);
        unlink($filename);
        if (empty($excelData[0][0]) || empty($excelData[0][1]) ||
            stripos($excelData[0][0], '手机号码') === false || stripos($excelData[0][1], '装修公司') === false) {
            $this->ajaxReturn([
                'error_code' => 500900,
                'error_msg' => '导入模板错误',
                'data' => []
            ]);
        }
        unset($excelData[0]);

        $result = $this->orderBlackLogic->importData($excelData);
        if ($result['fail_line'] !== false) {
            $this->ajaxReturn([
                'error_code' => 500900,
                'error_msg' => '第' . $result['fail_line'] . '行数据出错',
                'data' => []
            ]);
        }
        if ($result['flag'] === false) {
            $this->ajaxReturn([
                'error_code' => 500900,
                'error_msg' => '导入数据失败',
                'data' => []
            ]);
        }
        $this->ajaxReturn([
            'error_code' => 0,
            'error_msg' => '导入成功',
            'data' => []
        ]);
    }

    public function synchronizedata()
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit','512M');
        echo '开始导入装修公司的手机进入黑名单<br/>';
        $this->orderBlackLogic->synchronizeData();
        echo '完成导入装修公司的手机进入黑名单<br/>';
    }
}