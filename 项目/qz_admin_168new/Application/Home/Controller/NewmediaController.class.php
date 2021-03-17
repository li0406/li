<?php

//新媒体操作

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\NewMediaLogicModel;

class NewmediaController extends HomeBaseController
{
    // 考核人员管理
    public function user()
    {
        $get = I('get.', [], 'trim');

        $assign = NewMediaLogicModel::getUser($get);
        $this->assign($assign);
        //根据当前用户获取团信息
        $team = NewMediaLogicModel::getTeamByUser();
        $this->assign(['team' => $team]);

        $team_id = I('get.team', '', 'trim');
        $group = [];
        if (!empty($team_id)) {
            $group = NewMediaLogicModel::getGroupByTeam($team_id);
        }
        $this->assign(['group' => $group]);
        $this->display();
    }

    public function adduser()
    {
        if (IS_POST) {
            $post = I('post.', [], 'trim');
            if (empty($post['assess_admin_id'])) {
                $this->ajaxReturn(['error_msg' => '请选择考核人员', 'error_code' => 404]);
            }
            if (empty($post['group_id']) || empty($post['team_admin_id'])) {
                $this->ajaxReturn(['error_msg' => '请选择团/组', 'error_code' => 404]);
            }
            $flag = NewMediaLogicModel::addUser($post);
            if (is_string($flag) && !ctype_digit((string)$flag)) {
                $this->ajaxReturn(['error_msg' => $flag, 'error_code' => 404]);
            }
            if ($flag !== false) {
                $this->ajaxReturn(['error_msg' => '保存成功', 'error_code' => 0]);
            }
            $this->ajaxReturn(['error_msg' => '保存失败', 'error_code' => 404]);
        }
        $this->ajaxReturn(['error_msg' => '请求错误', 'error_code' => 404]);
    }

    // 考核人员转移（使用iframe操作）
    public function usertransfer()
    {
        $list = NewMediaLogicModel::getTransferUser();
        $this->assign(['list' => $list]);
        //根据当前用户获取团信息
        $team = NewMediaLogicModel::getTeamByUser();
        $this->assign(['team' => $team]);
        $group = NewMediaLogicModel::getGroupByTeam($team[0]['id']);
        $this->assign(['group' => $group]);
        $this->display();
    }

    // 考核人员转移
    public function usertransferdetail()
    {
        $reletion_id = I('get.reletion_id');
        $user_id = I('get.user_id');
        $transfer = I('get.transfer');

        // 获取记录
        $detail = NewMediaLogicModel::getTransferUserdetail($user_id, $reletion_id);
        $this->assign(['data' => $detail]);
        // 根据团信息
        $team = NewMediaLogicModel::getTeamByUser();
        $this->assign(['team' => $team]);
        // 获取组信息
        if (!empty($detail['team_admin_id'])) {
            $group = NewMediaLogicModel::getGroupByTeam($detail['team_admin_id']);
        }
        $this->assign(['group' => $group]);
        //获取未删除的离职人员
        $dimissionList = NewMediaLogicModel::getDimissionTransferUser();
        $this->assign(['dimission' => $dimissionList]);
        //区分页面
        $this->assign('is_transfer', $transfer);
        $this->display();
    }

    // 考核人员组管理
    public function usergroup(){
        $assign = NewMediaLogicModel::getGroupList();
        $this->assign($assign);
        //根据当前用户获取团信息
        $team = NewMediaLogicModel::getTeamByUser();
        $this->assign(['team' => $team]);
        $this->display();
    }

    // 删除人员组
    public function delusergroup()
    {
        if (IS_POST) {
            $postid = I('post.id', 0, 'intval');
            $flag = NewMediaLogicModel::delGroup($postid);
            if($flag === true) {
                $this->ajaxReturn(['error_msg' => '删除成功', 'error_code' => 0]);
            }
            $this->ajaxReturn(['error_msg' => '删除失败', 'error_code' => 404]);
        }
        $this->ajaxReturn(['error_msg' => '请求错误', 'error_code' => 404]);
    }

    // 添加人员组
    public function addusergroup()
    {
        if (IS_POST) {
            $post = I('post.', [], 'trim');
            if (empty($post['name'])) {
                $this->ajaxReturn(['error_msg' => '组名称不能为空', 'error_code' => 404]);
            }
            if (empty($post['team_admin_id'])) {
                $this->ajaxReturn(['error_msg' => '请选择团长', 'error_code' => 404]);
            }
            $flag = NewMediaLogicModel::addGroup($post);
            if (is_string($flag) && !ctype_digit((string)$flag)) {
                $this->ajaxReturn(['error_msg' => $flag, 'error_code' => 404]);
            }
            if ($flag !== false) {
                $this->ajaxReturn(['error_msg' => '保存成功', 'error_code' => 0]);
            }
            $this->ajaxReturn(['error_msg' => '保存失败', 'error_code' => 404]);
        }
        $this->ajaxReturn(['error_msg' => '请求错误', 'error_code' => 404]);
    }

    // 考核人员/渠道设置
    public function usersrc()
    {
        $get = I('get.', [], 'trim');
        $assign = NewMediaLogicModel::getUser($get, 10);
        $assign['list'] = NewMediaLogicModel::bindSrc($assign['list']);
        $this->assign($assign);

        $source = NewMediaLogicModel::getSource(false);
        $this->assign('source',$source);

        $user = NewMediaLogicModel::getRelationUsers();
        $this->assign('user',$user);
        $this->display();
    }

    // 考核人员/渠道设置编辑页面
    public function usersrcset()
    {
        $id = I('get.id');
        // 获取记录
        $detail = NewMediaLogicModel::getTransferUserdetail(0, $id);
        if (empty($detail['group_id'])){
            $this->error('当前人员未绑定团组');
        }
        $detail = NewMediaLogicModel::bindSrc([$detail]);

        $data = $detail[0];
        $data['src_check'] = array_column($data['source'], 'src');
        $this->assign(['data' => $data]);

        $source = NewMediaLogicModel::getSource(true,$data['assess_admin_id']);
        $this->assign('source',$source);
        $this->display();
    }

    // 设置用户SRC
    public function saveusersrc()
    {
        if (IS_POST) {
            $id = I('post.id', 0, 'intval');
            if (empty($id)) {
                $this->ajaxReturn(['error_msg' => '参数错误', 'error_code' => 404]);
            }
            // 获取记录
            $detail = NewMediaLogicModel::getTransferUserdetail(0, $id);
            if (empty($detail['group_id'])) {
                $this->ajaxReturn(['error_msg' => '当前人员未绑定团组', 'error_code' => 404]);
            }
            $src_list = I('post.src_list', [], 'trim');
            $flag = NewMediaLogicModel::setSrc($detail['assess_admin_id'], $src_list, $detail['user']);
            if ($flag === true) {
                $this->ajaxReturn(['error_msg' => '操作成功', 'error_code' => 0]);
            }
            $this->ajaxReturn(['error_msg' => '操作失败', 'error_code' => 404]);
        }
        $this->ajaxReturn(['error_msg' => '请求错误', 'error_code' => 404]);
    }

    // 考核人员/渠道设置日志列表页面
    public function usersrclog()
    {
        $assess_admin_id = I('get.user_id');
        $assign = NewMediaLogicModel::getSrcLog($assess_admin_id);
        $this->assign($assign);
        $this->display();
    }

    //公摊渠道设置
    public function common_src(){
        //渠道
        $source = NewMediaLogicModel::getSource(false);
        $this->assign('source', $source);
        //自己管辖人员的列表数据
        $list = NewMediaLogicModel::getUserCommonSrcList(I('get.src'));
        $this->assign('list', $list['list']);
        $this->assign('page', $list['page']);
        $this->display();
    }

    //公摊渠道设置
    public function add_common_src(){
        $src = I('get.src','');
        //所有渠道
        $source = NewMediaLogicModel::getSource(false);
        $this->assign('source',$source);
        if(!empty($src)){
            //所有管辖人员
            $user = NewMediaLogicModel::getNewMediaUsers(false);
            $this->assign('user',$user);
            //获取选中的渠道的考核人员
            $user_ids = NewMediaLogicModel::getUserByCommonSrc($src);
            $this->assign('assess_admin',$user_ids);
        }
        $this->display();
    }

    public function save_common_src()
    {
        $status = NewMediaLogicModel::saveCommonSrc(I('post.'));
        $this->ajaxReturn($status);
    }
    public function del_common_src()
    {
        $status = NewMediaLogicModel::delCommonSrc(I('post.'));
        $this->ajaxReturn($status);
    }

    /**
     * 月目标设置
     */
    public function target()
    {
        $media = new NewMediaLogicModel();
        //根据当前用户获取团信息
        $team = $media->getTeamByUser();
        $list = $media->getTargetList(I('get.'), $team);
        $this->assign('team', $team);

        $this->assign('group', !empty($list['group']) ? $list['group'] : []);
        $this->assign('list', $list['list']);
        $this->assign('page', $list['page']);
        $this->display();
    }

    /**
     * 编辑月目标
     */
    public function edit_target()
    {
        $id = I('get.id');
        $media = new NewMediaLogicModel();
        $info = $media->getTargetInfo($id);
        if ($info) {
            $this->ajaxReturn(['status' => 1, 'info' => $info]);
        } else {
            $this->ajaxReturn(['status' => 0, 'info' => '查询失败! 刷新后再试! ']);
        }
    }

    /**
     * 更根据选择的团获取所有组
     */
    public function team_group()
    {
        $team_id = I('team_id');
        $media = new NewMediaLogicModel();
        $team = $media->getGroupByTeam($team_id);
        $this->ajaxReturn(['status' => 1, 'info' => $team]);
    }

    /**
     * 保存月目标设置
     */
    public function save_target()
    {
        $data = I('post.');
        $media = new NewMediaLogicModel();
        $team = $media->saveTargetInfo($data, $this->User);
        $this->ajaxReturn(['status' => $team['status'], 'info' => $team['info']]);
    }

    /**
     * 删除月目标
     */
    public function del_target()
    {
        $id = I('post.edit_id');
        $media = new NewMediaLogicModel();
        $info = $media->delTarget($id);
        if ($info) {
            $this->ajaxReturn(['status' => 1, 'info' => '']);
        } else {
            $this->ajaxReturn(['status' => 0, 'info' => '删除失败! 刷新后再试! ']);
        }
    }

    /**
     * 新媒体业绩统计 - 按组统计
     * @return [type] [description]
     */
    public function statistics_group(){
        $team_id = I("team_id");
        $group_id = I("group_id");
        $date_begin = I("begin");
        $date_end = I("end");
        $export = I("export");

        if (!empty($date_begin) && !empty($date_end)) {
            $begin_month = date("Y-m", strtotime($date_begin));
            $end_month = date("Y-m", strtotime($date_end));
            if ($begin_month != $end_month) {
                // 提示日期月份不一致
                return $this->error("选择日期不能跨月");
            }
        } else {
            $date_begin = date("Y-m-01");
            $date_end = date("Y-m-d");
        }

        $newMediaLogic = new NewMediaLogicModel();
        $result = $newMediaLogic->getStatisticsByGroup($team_id, $group_id, $date_begin, $date_end);

        // 导出
        if (!empty($export)) {
            $this->statistics_group_export($result);
            exit();
        }

        $team_list = $newMediaLogic->getTeamByUser();

        $this->assign("all", $result["all"]);
        $this->assign("list", $result["list"]);
        $this->assign("team_list", $team_list);
        $this->assign("date_begin", $date_begin);
        $this->assign("date_end", $date_end);
        $this->assign("group_id", $group_id);
        $this->assign("team_id", $team_id);
        $this->display();
    }

    /**
     * 新媒体业绩统计 - 按人统计
     * @return [type] [description]
     */
    public function statistics_user(){
        $user_id = I("get.user_id");
        $team_id = I("get.team_id");
        $group_id = I("get.group_id");
        $date = I("get.date");
        if (empty($date)) {
            $date = date("Y-m-d");
        }

        $newMediaLogic = new NewMediaLogicModel();
        $result = $newMediaLogic->getStatisticsByUser($user_id, $team_id, $group_id, $date);

        // 导出
        if (I("get.export") == 1) {
            $this->statistics_user_export($result);
            exit();
        }

        $team_list = $newMediaLogic->getTeamByUser();
        $user_list = $newMediaLogic->getSearchUsers();

        $this->assign("list", $result["list"]);
        $this->assign("all", $result["all"]);
        $this->assign("team_list", $team_list);
        $this->assign("user_list", $user_list);
        $this->assign("user_id", $user_id);
        $this->assign("team_id", $team_id);
        $this->assign("group_id", $group_id);
        $this->assign("date", $date);
        $this->display();
    }

    /**
     * 新媒体业绩统计 - 业绩排行榜
     * @return [type] [description]
     */
    public function statistics_ranking(){
        $month = I("get.month");
        if (empty($month)) {
            $month = date("Y-m");
        }

        $page = I("p", 1);
        $limit = I("limit", 20);
        $export = I("get.export");

        $newMediaLogic = new NewMediaLogicModel();
        $result = $newMediaLogic->getStatisticsRanking($month, $page, $limit, $export);

        if ($export == true) {
            $this->statistics_ranking_export($result["list"]);
            exit();
        }

        $this->assign("list", $result["list"]);
        $this->assign("all", $result["all"]);
        $this->assign("month", $month);
        $this->display();
    }

    /**
     * 新媒体业绩统计 - 按组统计导出
     * @param  [type] $list [description]
     * @return [type]       [description]
     */
    private function statistics_group_export($result){
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.IOFactory',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Style.Alignment',"",".php");

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();
        // Set properties
        $objPHPExcel->getProperties()->setCreator("ctos")
            ->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet(0);

        $activeSheet->getColumnDimension('A')->setWidth(40);
        $activeSheet->getColumnDimension('B')->setWidth(15);
        $activeSheet->getColumnDimension('C')->setWidth(15);
        $activeSheet->getColumnDimension('D')->setWidth(15);
        $activeSheet->getColumnDimension('E')->setWidth(15);
        $activeSheet->getColumnDimension('F')->setWidth(15);
        $activeSheet->getColumnDimension('G')->setWidth(15);
        $activeSheet->getColumnDimension('H')->setWidth(15);

        $TYPE_STRING = \PHPExcel_Cell_DataType::TYPE_STRING;
        $VERTICAL_CENTER = \PHPExcel_Style_Alignment::VERTICAL_CENTER;
        $HORIZONTAL_LEFT = \PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
        $HORIZONTAL_CENTER = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
        $activeSheet->getStyle('A:H')->getAlignment()->setHorizontal($HORIZONTAL_CENTER)->setVertical($VERTICAL_CENTER);

        // 对齐方式
        $activeSheet->getStyle('A')->getAlignment()->setHorizontal($HORIZONTAL_LEFT);

        // 表头
        $activeSheet->setCellValue('A1', '小组')
            ->setCellValue('B1', '本月目标量')
            ->setCellValue('C1', '实际完成量')
            ->setCellValue('D1', '发单量')
            ->setCellValue('E1', '分单率')
            ->setCellValue('F1', '完成率')
            ->setCellValue('G1', '时间进度')
            ->setCellValue('H1', '进度差');

        $activeSheet->freezePaneByColumnAndRow(0, 2);
        $activeSheet->getRowDimension(1)->setRowHeight(20);

        // 表头加粗
        $activeSheet->getStyle('A1:H1')->applyFromArray(array(
            'font' => array (
                'name' => 'Microsoft Yahei',
                'bold' => true
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array (
                   'argb' => 'E7E6E6E6'
                )
            )
        ));


        $indexXls = 2;
        foreach ($result["list"] as $key => $li) {
            $activeSheet->setCellValue('A' . $indexXls, $li["team_name"] . " - " . $li["name"]);
            $activeSheet->setCellValue('B' . $indexXls, $li['target_num']);
            $activeSheet->setCellValue('C' . $indexXls, $li['finish_count']);
            $activeSheet->setCellValue('D' . $indexXls, $li['send_count']);
            $activeSheet->setCellValue('E' . $indexXls, $li['send_rate']);
            $activeSheet->setCellValue('F' . $indexXls, $li["finish_rate"]);
            $activeSheet->setCellValue('G' . $indexXls, $li["date_rate"]);
            $activeSheet->setCellValue('H' . $indexXls, $li["diff_rate"]);
            $activeSheet->getRowDimension($indexXls)->setRowHeight(20);
            $indexXls++;
        }

        if (count($result["list"]) > 0) {
            $activeSheet->setCellValue('A' . $indexXls, "合计");
            $activeSheet->setCellValue('B' . $indexXls, $result["all"]['target_num']);
            $activeSheet->setCellValue('C' . $indexXls, $result["all"]['finish_count']);
            $activeSheet->setCellValue('D' . $indexXls, $result["all"]['send_count']);
            $activeSheet->setCellValue('E' . $indexXls, $result["all"]['send_rate']);
            $activeSheet->setCellValue('F' . $indexXls, $result["all"]['finish_rate']);
            $activeSheet->setCellValue('G' . $indexXls, $result["all"]["date_rate"]);
            $activeSheet->setCellValue('H' . $indexXls, $result["all"]["diff_rate"]);
            $activeSheet->getRowDimension($indexXls)->setRowHeight(20);
        } else {
            $indexXls--;
        }

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,//细边框
                    'color' => array("argb" => "55555555")
                )
           )
        );

        $activeSheet->getStyle('A1:H'.$indexXls)->applyFromArray($styleArray);

        ob_end_clean();//清除缓冲区,避免乱码

        // 输出
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="新媒体业绩统计-按组统计.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }

    /**
     * 新媒体业绩统计 - 按人统计导出
     * @param  [type] $result [description]
     * @return [type]         [description]
     */
    private function statistics_user_export($result){
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.IOFactory',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Style.Alignment',"",".php");

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();
        // Set properties
        $objPHPExcel->getProperties()->setCreator("ctos")
            ->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet(0);

        $activeSheet->getColumnDimension('A')->setWidth(20);
        $activeSheet->getColumnDimension('B')->setWidth(30);
        $activeSheet->getColumnDimension('C')->setWidth(15);
        $activeSheet->getColumnDimension('D')->setWidth(15);
        $activeSheet->getColumnDimension('E')->setWidth(15);
        $activeSheet->getColumnDimension('F')->setWidth(16);
        $activeSheet->getColumnDimension('G')->setWidth(15);
        $activeSheet->getColumnDimension('H')->setWidth(15);

        $TYPE_STRING = \PHPExcel_Cell_DataType::TYPE_STRING;
        $VERTICAL_CENTER = \PHPExcel_Style_Alignment::VERTICAL_CENTER;
        $HORIZONTAL_LEFT = \PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
        $HORIZONTAL_CENTER = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
        $activeSheet->getStyle('A:H')->getAlignment()->setHorizontal($HORIZONTAL_CENTER)->setVertical($VERTICAL_CENTER);

        // 对齐方式
        // $activeSheet->getStyle('A')->getAlignment()->setHorizontal($HORIZONTAL_LEFT);

        // 表头
        $activeSheet->setCellValue('A1', '姓名')
            ->setCellValue('B1', '小组/团')
            ->setCellValue('C1', '当天完成')
            ->setCellValue('D1', '当天发单')
            ->setCellValue('E1', '当天分单率')
            ->setCellValue('F1', '剩余每天完成量')
            ->setCellValue('G1', '当月分单预估')
            ->setCellValue('H1', '当月剩余天数');

        $activeSheet->freezePaneByColumnAndRow(0, 2);
        $activeSheet->getRowDimension(1)->setRowHeight(20);

        // 表头加粗
        $activeSheet->getStyle('A1:H1')->applyFromArray(array(
            'font' => array (
                'name' => 'Microsoft Yahei',
                'bold' => true
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array (
                   'argb' => 'E7E6E6E6'
                )
            )
        ));


        $indexXls = 2;
        foreach ($result["list"] as $key => $li) {
            $activeSheet->setCellValue('A' . $indexXls, $li["user_name"]);
            $activeSheet->setCellValue('B' . $indexXls, $li["group_name"] . "/" . $li["team_name"]);
            $activeSheet->setCellValue('C' . $indexXls, $li['finish_count']);
            $activeSheet->setCellValue('D' . $indexXls, $li['send_count']);
            $activeSheet->setCellValue('E' . $indexXls, $li['send_rate']);
            $activeSheet->setCellValue('F' . $indexXls, $li['left_day_count']);
            $activeSheet->setCellValue('G' . $indexXls, $li["month_send_yugu"]);
            $activeSheet->setCellValue('H' . $indexXls, $li["left_days"]);
            $activeSheet->getRowDimension($indexXls)->setRowHeight(20);
            $indexXls++;
        }

        if (count($result["list"]) > 0) {
            $activeSheet->setCellValue('A' . $indexXls, "");
            $activeSheet->setCellValue('B' . $indexXls, "合计");
            $activeSheet->setCellValue('C' . $indexXls, $result["all"]['finish_count']);
            $activeSheet->setCellValue('D' . $indexXls, $result["all"]['send_count']);
            $activeSheet->setCellValue('E' . $indexXls, $result["all"]['send_rate']);
            $activeSheet->setCellValue('F' . $indexXls, $result["all"]['left_day_count']);
            $activeSheet->setCellValue('G' . $indexXls, $result["all"]["month_send_yugu"]);
            $activeSheet->setCellValue('H' . $indexXls, "");
            $activeSheet->getRowDimension($indexXls)->setRowHeight(20);
        } else {
            $indexXls--;
        }

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,//细边框
                    'color' => array("argb" => "55555555")
                )
           )
        );

        $activeSheet->getStyle('A1:H'.$indexXls)->applyFromArray($styleArray);

        ob_end_clean();//清除缓冲区,避免乱码

        // 输出
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="新媒体业绩统计-按人统计.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }

    /**
     * 新媒体业绩统计 - 业绩排行榜导出
     * @param  [type] $list [description]
     * @return [type]       [description]
     */
    private function statistics_ranking_export($list){
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.IOFactory',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Style.Alignment',"",".php");

        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();
        // Set properties
        $objPHPExcel->getProperties()->setCreator("ctos")
            ->setLastModifiedBy("ctos")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        
        $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet = $objPHPExcel->getActiveSheet(0);

        $activeSheet->getColumnDimension('A')->setWidth(12);
        $activeSheet->getColumnDimension('B')->setWidth(30);
        $activeSheet->getColumnDimension('C')->setWidth(18);
        $activeSheet->getColumnDimension('D')->setWidth(18);
        
        $TYPE_STRING = \PHPExcel_Cell_DataType::TYPE_STRING;
        $VERTICAL_CENTER = \PHPExcel_Style_Alignment::VERTICAL_CENTER;
        $HORIZONTAL_LEFT = \PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
        $HORIZONTAL_CENTER = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
        $activeSheet->getStyle('A:D')->getAlignment()->setHorizontal($HORIZONTAL_CENTER)->setVertical($VERTICAL_CENTER);

        // 表头
        $activeSheet->setCellValue('A1', '序号')
            ->setCellValue('B1', '姓名')
            ->setCellValue('C1', '业绩')
            ->setCellValue('D1', '月份');
        
        $activeSheet->freezePaneByColumnAndRow(0, 2);
        $activeSheet->getRowDimension(1)->setRowHeight(20);

        // 表头加粗
        $activeSheet->getStyle('A1:D1')->applyFromArray(array(
            'font' => array (
                'name' => 'Microsoft Yahei',
                'bold' => true
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,  
                'startcolor' => array (
                   'argb' => 'E7E6E6E6'
                )
            )
        ));

        $indexXls = 2;
        $k = 1;
        foreach ($list as $key => $li) {
            $activeSheet->setCellValue('A' . $indexXls, $k);
            $activeSheet->setCellValue('B' . $indexXls, $li['user_name']);
            $activeSheet->setCellValue('C' . $indexXls, $li['finish_orders']);
            $activeSheet->setCellValue('D' . $indexXls, $li['month']);
            $activeSheet->getRowDimension($indexXls)->setRowHeight(20);
            $indexXls++;
            $k++;
        }

        $styleArray = array(  
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,//细边框  
                    'color' => array("argb" => "55555555") 
                )
           )
        );

        $activeSheet->getStyle('A1:D'.($indexXls - 1))->applyFromArray($styleArray);

        ob_end_clean();//清除缓冲区,避免乱码
        
        // 输出
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="新媒体业绩统计-业绩排行榜.xls"');
        header('Cache-Control: max-age=0');
        
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }
}