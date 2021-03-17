<?php


namespace Home\Controller;


use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\ScoreLevelLogicModel;
use Home\Model\Logic\UcenterProfileLogicModel;

class MemberstatisticsController extends HomeBaseController
{
    //会员统计列表页
    public function index()
    {
        $param = I('get.');
        $this->assign("getdata", $param);
        $ucenterprofileLogic = new UcenterProfileLogicModel();
        $scorelevellogic = new ScoreLevelLogicModel();

        $param = $this->checktime($param);

        //获取会员列表
        $list = $ucenterprofileLogic->get_member($param);


        //获取会员等级
        $levellist = $scorelevellogic->getLevelInfo();

        $nowpage = $param['p'] ? ($param['p'] - 1) : 0;
        $this->assign("nowpage", $nowpage);
        $this->assign('list', $list['list']);
        $this->assign("page", $list['page']);
        $this->assign("levellist", $levellist);
        $this->display();
    }

    //积分记录列表页
    public function integral()
    {
        $this->display();
    }

    //推荐记录列表页
    public function recommend()
    {
        $this->display();
    }

    //兑换记录列表页
    public function exchange()
    {
        $this->display();
    }

    //签到统计列表页
    public function attendance()
    {

        $this->display();
    }


    //验证
    private function checktime($param)
    {
        //入会时间验证
        if ($param["join_start"] && $param["join_end"]) {
            if ($param['join_start'] > $param['join_end']) {
                $this->_error("开始时间不能大于结束时间");
            }
            $oneyear = date('Y-m-d', strtotime($param["join_end"] . ' -1 year'));      //结束日期一年前的日期
            if ($param["join_start"] < $oneyear) {
                $this->_error("时间跨度不能超过1年");
            }
            $param["join_start"] = strtotime($param["join_start"]);
            $param["join_end"] = strtotime($param["join_end"] . " 23:59:59");
        } elseif ($param["join_start"] && !$param["join_end"]) {      //有开始时间-没有结束时间
            $today = date("Y-m-d", time());
            $oneyear = date('Y-m-d', strtotime($today . ' -1 year'));       //今日的 一年前日期
            if ($param["join_start"] < $oneyear) {
                $this->_error("时间跨度不能超过1年");
            }
            $param["join_start"] = strtotime($param["join_start"]);
            $param["join_end"] = strtotime($today . " 23:59:59");
        } elseif (!$param["join_start"] && $param["join_end"]) {      //有结束时间-没有开始时间
            $today = date("Y-m-d", time());
            $oneyear = date('Y-m-d', strtotime($param["join_end"] . ' -1 year'));      //结束日期一年前的日期
            if ($today < $oneyear) {
                $this->_error("时间跨度不能超过1年");
            }
            $param["join_start"] = strtotime($oneyear);
            $param["join_end"] = strtotime($param["join_end"] . " 23:59:59");
        } else {
            $param["join_start"] = 0;
            $param["join_end"] = 0;
        }

        if ($param["level1"] && $param["level2"]) {
            if ($param['level2'] < $param['level1']) {
                $this->_error("最高等级必须大于等于最低等级");
            }
        }


        return $param;

    }


    public function exportexcel()
    {

        $param = I('get.');
        $ucenterprofileLogic = new UcenterProfileLogicModel();
        $scorelevellogic = new ScoreLevelLogicModel();

        $param = $this->checktime($param);

        //获取会员列表
        $list = $ucenterprofileLogic->getAllMember($param);

        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 60 * 3);
        $excelData = [];
        $excelData['header'] = [
            '排名' => 'string',
            '会员' => 'string',
            '入会时间' => 'string',
            '等级 ' => 'string',
            '成长值' => 'string',
            '总现金券' => 'string',
            '当前现金券' => 'string',
            '兑换次数' => 'string',
            '推荐人数' => 'string',
        ];
        $excelData['sheet'] = 'sheet1';
        $excelData['row'] = [];
        $rowAll = [];
        $i = 0;
        foreach ($list as $key => $value) {
            $rowAll[$i][0] = $key + 1;
            $rowAll[$i][1] = $value['nickname'];
            $rowAll[$i][2] = date("Y-m-d H:i:d",$value['first_score_time']);
            $rowAll[$i][3] = "LV".$value['level'];
            $rowAll[$i][4] = $value['growth'];
            $rowAll[$i][5] = $value['all_score'];
            $rowAll[$i][6] = $value['score'];
            $rowAll[$i][7] = $value['meo_count'];
            $rowAll[$i][8] = $value['sr_count'];
            $i++;
        }

        $excelData['row'] = $rowAll;
        $excelData['filename'] = '会员统计.xlsx';
        export_excel_download($excelData);
    }


}