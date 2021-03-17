<?php

namespace Home\Controller;

use Common\Enums\ApiConfig;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\ScoreSignRuleLogicModel;
use Think\Exception;
use Think\Template\TagLib;

class ScoreController extends HomeBaseController
{
    protected $scoreSignRuleLogic;

    /**
     * excel允许的后缀
     * @var array
     */
    protected $allowExcelExt = [
        'alt', 'xls', 'xml', 'xlsx', 'csv',
    ];

    /**
     * excel允许的mime type
     * @var array
     */
    protected $allowExcelMimeType = [
        'application/vnd.ms-excel',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
        'application/octet-stream', //个别客户端上传excel的mine-type 类型不一样，无法通过 bug时间:2019-7-18
    ];

    public function __construct()
    {
        parent::__construct();
        $this->scoreSignRuleLogic = new ScoreSignRuleLogicModel();
    }

    // 模板

    // 单人签到规则列表
    public function signRule()
    {
        //获取
        $data = $this->scoreSignRuleLogic->getList(1);
        $this->assign('data', $data);
        $this->display();
    }

    public function addSingleSignRule()
    {
        //获取
        $id = I('get.id')?:'';
        $actionName = '添加';
        $data = [];
        if (!empty($id)) {
            $data = $this->scoreSignRuleLogic->getDetail($id);
            $actionName = '编辑';
        }
        $this->assign('id', $id);
        $this->assign('data', $data);
        $this->assign('actionName', $actionName);
        $this->display();
    }

//    public function addGroupSignRule()
//    {
//        //获取
//        $this->display();
//    }

//    public function signGroup()
//    {
//        //获取
//        $data = $this->scoreSignRuleLogic->getList(2);
//        $this->assign('data', $data);
//        $this->display();
//    }


    // api 接口

    /**
     * 获取详情
     */
    public function getRuleDetail()
    {
        try {
            $id = I('get.id');
            if (empty($id)) {
                throw new Exception('参数有误', ApiConfig::PARAMETER_ILLEGAL);
            }
            $data = $this->scoreSignRuleLogic->getDetail($id);
            $code = ApiConfig::REQUEST_SUCCESS;
            $msg = '操作成功';
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
            $data = [];
        }
        $data = [
            'error_code' => $code,
            'error_msg' => $msg,
            'data' => $data,
        ];
        $this->ajaxReturn($data);
    }

    public function delSignRule()
    {
        try {
            $ruleId = (int)I('post.id');
            if (empty($ruleId)) {
                throw new Exception('参数有误', ApiConfig::PARAMETER_ILLEGAL);
            }

            //有效的无法删除
            if ($this->scoreSignRuleLogic->hasEnableRule($ruleId)) {
                throw new Exception('此规则正在生效，无法删除', ApiConfig::CANNOT_DELETE);
            }

            $this->scoreSignRuleLogic->delSignRule($ruleId);
            $code = ApiConfig::REQUEST_SUCCESS;
            $msg = '成功删除';
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }
        $data = [
            'error_code' => $code,
            'error_msg' => $msg,
        ];
        $this->ajaxReturn($data);
    }

    /**
     * 保存签到规则
     */
    public function saveSignRule()
    {
        try {

            $data = I('post.');
            $ruleData = $data['rule'];
            $ruleId = $ruleData['rule_id'];
            $ruleListData = $data['rules'];

            $ruleData['start_time'] = $ruleData['start_time'] ? strtotime($ruleData['start_time']) : 0;

            if (empty($ruleData) || empty($ruleListData) || empty($ruleData['name'])) {
                throw new Exception('参数有误', ApiConfig::PARAMETER_ILLEGAL);
            }

            if (empty($ruleData['type'])) {
                throw new Exception('参数有误', ApiConfig::PARAMETER_ILLEGAL);
            }

//            $enableRule = $this->scoreSignRuleLogic->getEnableRule($ruleData['type']);
//            if(!empty($enableRule) && $ruleData['start_time']){
//                $enableRuleStartTime = new \DateTime(date('Y-m-d', $enableRule['start_time']), new \DateTimeZone('Asia/Shanghai'));
//                $startDate = new \DateTime(date('Y-m-d',$ruleData['start_time']), new \DateTimeZone('Asia/Shanghai'));
//                $interval = $enableRuleStartTime->diff($startDate)->days;
//
//                if ($interval % $enableRule['circle'] != 0) {
//                    throw new Exception('与其他规则开始时间重叠，请重新设置', ApiConfig::PARAMETER_ILLEGAL);
//                }
//
//            }
            if (empty($ruleId)) {

                //名称不能重复
                if ($this->scoreSignRuleLogic->hasName($ruleData['type'], $ruleData['name'])) {
                    throw new Exception('规则名重复，请重新输入', ApiConfig::PARAMETER_MISSING);
                }

                //添加，判断开始时间是否已存在
                if ($ruleData['start_time'] && $this->scoreSignRuleLogic->hasStartTime($ruleData['type'], $ruleData['start_time'])) {
                    throw new Exception('与其他规则开始时间重叠，请重新设置', ApiConfig::PARAMETER_ILLEGAL);
                }

                $this->scoreSignRuleLogic->saveData($ruleData, $ruleListData);
            } else {
                //名称不能重复
                if ($this->scoreSignRuleLogic->hasNameExceptId($ruleId, $ruleData['type'], $ruleData['name'])) {
                    throw new Exception('规则名重复，请重新输入', ApiConfig::PARAMETER_MISSING);
                }
                //添加，判断开始时间是否已存在
                if ($ruleData['start_time'] && $this->scoreSignRuleLogic->hasStartTimeExceptId($ruleId, $ruleData['type'], $ruleData['start_time'])) {
                    throw new Exception('与其他规则开始时间重叠，请重新设置', ApiConfig::PARAMETER_ILLEGAL);
                }

                $this->scoreSignRuleLogic->editData($ruleId, $ruleData, $ruleListData);
            }

            $code = ApiConfig::REQUEST_SUCCESS;
            $msg = '操作成功';
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }
        $data = [
            'error_code' => $code,
            'error_msg' => $msg,
        ];
        $this->ajaxReturn($data);
    }

    /**
     * 导入签到规则
     */
    public function importSignRule()
    {
        try {
            //处理excel上传
            $ex = $_FILES['excel'];
            $type = 1;
            if (0 == $ex['size']) {
                throw new Exception('导入失败，请选择要上传的excel文件！', ApiConfig::PARAMETER_MISSING);
            }

            if (empty($ex['name'])) {
                throw new Exception('导入失败，规则名称不能为空！', ApiConfig::PARAMETER_MISSING);
            }

            $pos = mb_strripos($ex['name'], '.', 0, 'utf8');
            $ext = mb_substr($ex['name'], $pos + 1);

            if (!in_array($ext, $this->allowExcelExt, true)
                || !in_array($ex['type'], $this->allowExcelMimeType)) {
                throw new Exception('仅支持上传excel！', ApiConfig::PARAMETER_MISSING);
            }

            $filename = TEMP_PATH . time() . substr($ex['name'], stripos($ex['name'], '.'));
            move_uploaded_file($ex['tmp_name'], $filename);
            $excelData = import_excel($filename);
            unlink($filename);

            if (empty($excelData)) {
                throw new Exception('导入失败，规则数据不能为空', ApiConfig::PARAMETER_MISSING);
            }

            $ruleList = $this->scoreSignRuleLogic->formatImportSignRule($excelData);

            $ruleData = [
                'name' => mb_substr($ex['name'], 0, mb_strlen($ex['name']) - 5),
                'type' => $type,
                'start_time' => 0,
            ];


            //名称不能重复
            if ($this->scoreSignRuleLogic->hasName($type, $ruleData['name'])) {
                throw new Exception('规则名称重复，请修改模板文件名称', ApiConfig::PARAMETER_MISSING);
            }

            $this->scoreSignRuleLogic->saveData($ruleData, $ruleList);

            $code = ApiConfig::REQUEST_SUCCESS;
            $msg = '操作成功';
        } catch (\Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }
        $data = [
            'error_code' => $code,
            'error_msg' => $msg,
        ];
        $this->ajaxReturn($data);
    }


}
