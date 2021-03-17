<?php
/**
 *
 *
 * 订单导入
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/8 0008
 * Time: 17:43
 */

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class OrderimportController extends HomeBaseController
{
    /*
    *   后台订单导入
    */
    public function importTask()
    {
        //提交
        if (IS_POST) {
            self::importSave();
            die();
        }

        //展示

        //获取查询条件
        $param = I('get.');

        //处理时间
        if (empty($param['execute_start_time']) )  unset($param['execute_start_time']);
        if (empty($param['execute_end_time']) )  unset($param['execute_end_time']);

        $vdata = [];
        $vdata['param'] = $param;

        $OrderImportTask = D('OrderImportTask');
        $execute_status = $OrderImportTask->execute_status;

        $vdata['execute_status'] = $execute_status;


        $result = $this->getOrderImportTask($param);
        $vdata['list'] = $result['list'];
        $vdata['page'] = $result['page'];

        $this->assign('vdata', $vdata);
        $this->display('importTask');
    }

    /**
     * 查询渠道来源标识src信息
     * @return [type] [description]
     */
    public function findsrc()
    {
        if (IS_POST) {
            $src = I("post.q");
            $result = D("OrderSource")->findSrcByLike($src);
            $this->ajaxReturn(['data' => $result, 'msg' => '操作成功', 'code' => 0]);
        }
        $this->ajaxReturn(['data' => '', 'msg' => '失败，查询失败！', 'code' => 1]);
    }


    /**
     * 查询发单位置标识source信息
     * @return [type] [description]
     */
    public function findSourceLocation()
    {
        if (IS_POST) {
            $source = I("post.q");
            $result = D("OrderSource")->findSourceLocationByLikeName($source);
            $this->ajaxReturn(['data' => $result, 'msg' => '操作成功', 'code' => 0]);
        }
        $this->ajaxReturn(['data' => '', 'msg' => '失败，查询失败！', 'code' => 1]);
    }

    /**
     * 对任务设置多种标识信息
     */
    public function taskMarkSet()
    {
        if (IS_POST) {
            $task_id = I("post.task_id");
            $src = I("post.src");
            $source = I("post.source");
            $OrderImportTask = D('OrderImportTask');
            $result = $OrderImportTask->setTaskMark($task_id, $src, $source);
            $this->ajaxReturn(['data' => $result, 'msg' => '操作成功', 'code' => 0]);
        }
        $this->ajaxReturn(['data' => '', 'msg' => '失败，提交方式错误！', 'code' => 1]);
    }



    /**
     * 立即执行
     */
    public function taskDo()
    {
        if (IS_POST) {
            $task_id = I("post.task_id");
            $OrderImportTask = D('OrderImportTask');
            $result = $OrderImportTask->setTaskDo($task_id);
            $this->ajaxReturn(['data' => $result, 'msg' => '操作成功', 'code' => 0]);
        }
        $this->ajaxReturn(['data' => '', 'msg' => '失败，提交方式错误！', 'code' => 1]);
    }


    /**
     * 禁用（删除）导入任务
     */
    public function importTaskDisable()
    {
        //定义json返回
        $redata = [];
        $redata['code'] = 100;
        $redata['data'] = [];
        $redata['msg'] = '失败，未定义！';

        $task_id = I('post.task_id');
        $OrderImportTask = D('OrderImportTask');

        $taskOne = $OrderImportTask->getTaskBytaskId($task_id);
        //dump($taskOne);
        if ($taskOne['execute_status'] >= 3) {
            $redata['code'] = 2;
            $redata['data'] = [];
            $redata['msg'] = '失败,到立即执行后，任务不就可以删除了！';
            return $this->ajaxReturn($redata);
        }

        $disableTask = $OrderImportTask->disableTask($task_id);

        //清除上次的上传任务文件hash换成，从而可以让这个文件重新再来
        $op_id = session("uc_userinfo.id");
        $cache_key = 'C:168:oi:ledh:' . $op_id;
        S($cache_key,null);

        if ($disableTask) {
            $redata['code'] = 0;
            $redata['data'] = [];
            $redata['msg'] = '成功，已经删除！';
            return $this->ajaxReturn($redata);
        }

        return $this->ajaxReturn($redata);

    }


    /**
     *  某条导入task的详情
     */
    public function importList()
    {
        //展示

        //获取查询条件
        $param = I('get.');

        if (empty($param['task_id'])) {
            $this->_error('错误,未传入任务id参数！');
        }

        $vdata = [];
        $vdata['param'] = $param;

        $OrderImportList = D('OrderImportList');
        $task_status = $OrderImportList->task_status;

        $vdata['task_status'] = $task_status;

        $result = $this->getOrderImportList($param);

        $huxing = D("Huxing")->gethx(); //户型
        $yusuan = D("Jiage")->getJiage(); //预算
        $fangshi = D("Fangshi")->getfs(); //装修方式
        $fengge = D("Fengge")->getfg(); //风格

        foreach ($result['list'] as $k => &$v) {
            $v['lf_time'] = $OrderImportList->lf_time[$v['lf_time']];
            $v['start_time'] = $OrderImportList->start_time[$v['start_time']];
            $v['keys'] = $OrderImportList->keys[$v['keys']];
            $v['lx'] = $OrderImportList->lx[$v['lx']];
            $v['lxs'] = $OrderImportList->lxs[$v['lxs']];
            $v['task_status_h'] = $OrderImportList->task_status[$v['task_status']];
            if ('请选择'==$v['task_status_h']) $v['task_status_h'] = '无';

            // 户型 human
            $haveHuxing = false;
            foreach ($huxing as $k1 => $v1) {
                if ($v1['id'] == $v['huxing']) {
                    $v['huxing'] = $v1['name'];
                    $haveHuxing = true;
                    break;
                }
            }
            unset($v1);
            if (false == $haveHuxing) $v['huxing'] = '';

            // 喜欢风格 human
            $haveFengge = false;
            foreach ($fengge as $k1 => $v1) {
                if ($v1['id'] == $v['fengge']) {
                    $v['fengge'] = $v1['name'];
                    $haveFengge = true;
                    break;
                }
            }
            unset($v1);
            if (false == $haveFengge) $v['fengge'] = '';

            // 预算装修类型 human
            $haveFangshi = false;
            foreach ($fangshi as $k1 => $v1) {
                if ($v1['id'] == $v['fangshi']) {
                    $v['fangshi'] = $v1['name'];
                    $haveFangshi = true;
                    break;
                }
            }
            unset($v1);
            if (false == $haveFangshi) $v['fangshi'] = '';

            // 预算 human
            $haveYusuan = false;
            foreach ($yusuan as $k1 => $v1) {
                if ($v1['id'] == $v['yusuan']) {
                    $v['yusuan'] = $v1['name'];
                    $haveYusuan = true;
                    break;
                }
            }
            unset($v1);
            if (false == $haveYusuan) $v['yusuan'] = '';

        }
        unset($v);
        $vdata['list'] = $result['list'];
        $vdata['page'] = $result['page'];

        $this->assign('vdata', $vdata);
        $this->display('importList');
    }


    /**
     *  导出exel模板
     */
    public function exportOrderTemplate()
    {
        ini_set('memory_limit','512M');
        ini_set('max_execution_time',  60 * 3);
        $excelData = [];
        $excelData['header'] =   ['*联系电话' => 'string',
                                  '备用电话' => 'string',
                                  '联系人' => 'string',
                                  '性别' => 'string',
                                  '*城市' => 'string',
                                  '区域' => 'string',
                                  '小区名称' => 'string',
                                  '家装公装' => 'string',
                                  '新房旧房' => 'string',
                                  '用途' => 'string',
                                  '户型' => 'string',
                                  '室' => 'string',
                                  '面积' => 'string',
                                  '喜欢风格' => 'string',
                                  '预算装修类型' => 'string',
                                  '预算' => 'string',
                                  '拿房时间' => 'string',
                                  '钥匙' => 'string',
                                  '开工时间' => 'string',
                                  '量房时间' => 'string',
                                  '装修需求' => 'string',
                                  '数据类型' => 'string',
                              ];
        $excelData['sheet'] = 'sheet1';
        $excelData['row'] = [];
        // $row1 = [];
        //        // for($i = 0;$i<=20;$i++) {
        //        //     $row1[] = [''=>'string']; //空字符串
        //        // }
        //        // $rowAll = [];
        //        // for($i = 0;$i<10000;$i++) {
        //        //     //做10000行空字符串表
        //        //     $rowAll[] = $row1;
        //        // }
        //        // $excelData['row'] = $rowAll;
        $excelData['filename'] = '后台订单导入模板.xlsx';
        export_excel_download($excelData);
    }

    /**
     * 提交post excel文件保存
     */
    private function importSave()
    {
        //定义json返回
        $redata = [];
        $redata['code'] = 0;
        $redata['data'] = [];
        $redata['msg'] = '';

        //处理excel上传
        $ex = $_FILES['excel'];

        if (0 == $ex['size']) {
            $redata['code'] = 1;
            $redata['data'] = [];
            $redata['msg'] = '失败，请选择要上传的excel文件！';
            $this->ajaxReturn($redata);
        }

        $ex = $_FILES['excel'];
        $filename = TEMP_PATH . time() . substr($ex['name'], stripos($ex['name'], '.'));
        move_uploaded_file($ex['tmp_name'], $filename);
        $excelData = import_excel($filename);
        unlink($filename);
        foreach ($excelData as $k => &$v) {
            foreach ($v as $kf => $kv) {
                if (empty($kv)) unset($v[$kf]);
            }
            unset($kv);
        }
        unset($v);
        $excelData = array_filter($excelData);
        $excelDataHash = md5(json_encode($excelData)); //计算本次上传的数据hash

        //预处理数据
        $taskData = [];

        $taskData['import_time'] = time();// 导入时间
        $taskData['import_count'] = count($excelData) - 1; // 导入数量
        $taskData['src'] = '';// 渠道标识
        $taskData['execute_start_time'] = ''; // 执行开始时间
        $taskData['execute_end_time'] = '';// 执行结束时间
        $taskData['execute_status'] = 1;// 状态0默认 1标识未设置 2待执行 3执行中 4执行完成
        $taskData['op_id'] = session("uc_userinfo.id");// 操作人id
        $taskData['op_name'] = session("uc_userinfo.name"); // 操作人名称
        $taskData['status'] = 0;// 扩展状态0默认
        $taskData['create_at'] = $taskData['import_time']; // 创建时间
        $taskData['update_at'] = $taskData['import_time'];// 更新时间

        //处理没有数据
        if ($taskData['import_count'] <= 0) {
            //excel中没有数据
            $redata['code'] = 3;
            $redata['data'] = [];
            $redata['msg'] = '失败，excel中并没有要上传的数据！';
            $this->ajaxReturn($redata);
        }

        //处理同一个文件重复上传
        $cache_key = 'C:168:oi:ledh:' . $taskData['op_id'];
        $lastexcelDataHash = S($cache_key);
        if ($lastexcelDataHash == $excelDataHash) {
            //本次上传的excel data数据hash和上一次相同，就返回失败
            $redata['code'] = 2;
            $redata['data'] = [];
            $redata['msg'] = '失败，同一个excel文件重复上传啊亲！';
            $this->ajaxReturn($redata);
        }
        S($cache_key, $excelDataHash, 60 * 60 * 8);

        //入order_import_task 导入任务库
        $result1 = D('OrderImportTask')->addTask($taskData);

        //入order_import_list 导入任务详细订单库
        $taskId = $result1;
        $result2 = D('OrderImportList')->addList($excelData, $taskId);

        if ($result1 && $result2) {
            $redata['code'] = 0;
            $redata['data'] = [];
            $redata['msg'] = '成功，已经建立好导入任务了，你可以继续“设置标识”，“立即执行操作”！';
            $this->ajaxReturn($redata);
        } else {
            $redata['code'] = 99;
            $redata['data'] = [];
            $redata['msg'] = '失败，未知错误，可能是建立导入任务失败了！';
            $this->ajaxReturn($redata);
        }
    }


    /**
     *
     * 获取后台订单导入 任务列表翻页 支持 带参数 内建权限管控
     *
     * @param array $param 数组传入查询条件
     * @return mixed
     */
    private function getOrderImportTask($param)
    {
        import('Library.Org.Util.Page');
        $db = D('OrderImportTask');
        $count = $db->getOrderImportTaskCount($param);
        $Page = new \Page($count, 20);
        $param['limit']['start'] = $Page->firstRow;
        $param['limit']['end'] = $Page->listRows;
        $result['page'] = $Page->show();
        $result['list'] = $db->getOrderImportTask($param);
        return $result;
    }

    /**
     *
     * 获取后台订单导入 任务列表翻页 支持 带参数 内建权限管控
     *
     * @param array $param 数组传入查询条件
     * @return mixed
     */
    private function getOrderImportList($param)
    {
        import('Library.Org.Util.Page');
        $db = D('OrderImportList');
        $count = $db->getOrderImportListCount($param);
        $Page = new \Page($count, 20);
        $param['limit']['start'] = $Page->firstRow;
        $param['limit']['end'] = $Page->listRows;
        $result['page'] = $Page->show();
        $result['list'] = $db->getOrderImportList($param);
        return $result;
    }

}