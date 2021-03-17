<?php
namespace app\common\model\logic;
use app\common\model\db\SaleIndicators;
use app\common\model\db\SaleReportPayment;
use app\common\model\db\SaleTeam;
use app\index\enum\MonthEnmu;
use Util\Page;

/**
 *
 */
class IndicatorsLogic
{
    public function delIndicatorsByUserIds($user_id,$year,$module = 1)
    {
        $model = new SaleIndicators();
        return $model->delIndicatorsByUserIds($user_id,$year,$module);
    }

    public function addAllInfo($data)
    {
        $model = new SaleIndicators();
        return $model->addAllInfo($data);
    }

    public function getTeamIndicatorsList($year,$team_name,$pageIndex)
    {
        if (empty($year)) {
            $year = date("Y");
        }

        $model = new SaleIndicators();
        $count = $model->getTeamIndicatorsListCount($year,$team_name);
        //分页
        $page = new Page($pageIndex,20,$count);
        $p = $page->default_show();
        $list = [];
        if ($count > 0) {
            $p = $page->show();
            $list = $model->getTeamIndicatorsList($year,$team_name,$page->firstrow,$page->pageSize);
        }
        return ["list" => $list,"page" => $p];
    }

    public function getTableHeadDate($year)
    {
        if (empty($year)) {
            $year = date("Y");
        }

        $list = [];
        for ($i = 1; $i <= 12; $i++) {
            if ($i < 10) {
                $i = "0".$i;
            }
            $short = MonthEnmu::ConvertToShort($i);
            $list[] =[
               "full" => $year."-".$i,
               "short" => $short
            ];
        }
        return $list;
    }

    public function getTeamMemberIndicators($year,$name,$pageIndex)
    {
        if (empty($year)) {
            $year = date("Y");
        }

        $model = new SaleIndicators();
        $count = $model->getTeamMemberIndicatorsCount($year,$name);
        //分页
        $page = new Page($pageIndex,20,$count);
        $p = $page->default_show();
        $list = [];
        if ($count > 0) {
            $p = $page->show();
            $list = $model->getTeamMemberIndicators($year,$name,$page->firstrow,$page->pageSize);
        }
        return ["list" => $list,"page" => $p];
    }

    public function editIndicators($current_id,$date,$module,$data)
    {
        $model = new SaleIndicators();
        return $model->editIndicators($current_id,$date,$module,$data);
    }
}