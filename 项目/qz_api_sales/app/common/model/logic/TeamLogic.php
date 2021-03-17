<?php

namespace app\common\model\logic;

use app\common\model\db\SaleIndicators;
use app\common\model\db\SaleReportPayment;
use app\common\model\db\SaleReportZxCompany;
use app\common\model\db\SaleTeam;
use app\common\model\db\SaleTeamAchievement;
use app\common\model\db\SaleTeamManageExtend;
use app\common\model\db\SaleTeamMap;
use app\index\enum\ReportPaymentCodeEnum;
use app\index\enum\SalesReportCodeEnum;
use app\common\enum\CacheKeyCodeEnum;
use Util\Page;
use think\facade\Request;
/**
 *
 */
class TeamLogic
{

    public function getTeamInfoByName($name)
    {
        $model = new SaleTeam();
        return $model->getTeamInfoByName($name);
    }

    public function getTeamInfoById($id,$module = 1)
    {
        $model = new SaleTeam();
        $result = $model->getTeamInfoById($id,$module);
        $info = [];
        if (count($result) > 0) {
            $info = $result->toArray();
            $info["manager"] = [];

            if (!empty($info["manager_id"])) {
                $exp = explode(",",$info["manager_id"]);
                $exp1 = explode(",",$info["manager_name"]);

                foreach ($exp as $key => $item) {
                    $info["manager"][] = [
                        "id" => $item,
                        "name" => $exp1[$key]
                    ];
                }

                unset($info["manager_id"]);
                unset($info["manager_name"]);
            }

        }
        return $info;
    }

    public function updateTeam($id,$data)
    {
        try {
            $this->clearTeamCache();
            $model = new SaleTeam();
            return $model->updateTeam($id,$data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function addTeam($data)
    {
        try {
            $this->clearTeamCache();
            $model = new SaleTeam();
            return $model->addTeam($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 删除团队和团队之间的关联关系
     * @param $top_id 上级团队
     * @param $current_id 当前团队
     * @return int
     * @throws \Exception
     */
    public function delTeamMap($current_id)
    {
        try {
            $this->clearTeamCache();
            $model = new SaleTeamMap();
            return $model->delTeamMap($current_id);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function addTeamMap($data)
    {
        try {
            $this->clearTeamCache();
            $model = new SaleTeamMap();
            return $model->addTeamMap($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getTopTeamList()
    {
        try {
            $model = new SaleTeam();
            $result = $model->getTopTeamList();
            $result = $result->toArray();

            $list = [];
            array_walk($result, function($value)use(&$list){
                $list[] = [
                    "team_name" =>$value["team_name"],
                    "id" => $value["id"]
                ];
            });
            return $list;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getTeamList($name = "",$leader = "",$team = "",$status = "",$pageIndex)
    {
        try {
            $model = new SaleTeam();
            $teamMember = $this->getAllTeamMember();

            $team_group = [];

            if (!empty($team) && isset($teamMember[$team]["team_group_id"])) {
                $team_group  = $teamMember[$team]["team_group_id"];
                array_push($team_group,$team);

            }

            $count = $model->getTeamListCount($name,$leader,$team_group,$status);
            $list = [];
            //分页
            $page = new Page($pageIndex,20,$count);
            $p = $page->default_show();
            if ($count > 0) {
                $p = $page->show();
                $list = $model->getTeamList($name,$leader,$team_group,$status,$page->firstrow,$page->pageSize);

                if (count($list) > 0) {
                    $list = $list->toArray();

                    //获取团队人数
                    array_walk($list,function(&$value,$key,$teamMember){
                        unset($value["team_leader"]);
                        $value["team_count"] = 0;
                        if (isset($teamMember[$value["id"]])) {
                            $value["team_count"] = count($teamMember[$value["id"]]["users"]);
                        }
                    },$teamMember);
                }
            }

            return ["list" => $list,"page" => $p];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getAllTeamLeaderInfo()
    {
        //获取所有的团队
        $result = $this->getAllTeamMember();
        $list = [];
        array_walk($result,function($value)use (&$list){
            $list[] = [
                "id" => $value["current_id"],
                "name" => $value["current_name"],
            ];
        });
        return $list;
    }

    public function getTeamTree($team_id = "")
    {
        //获取所有的团队
        $result = $this->getAllTeamMember();
        $expand = $checked = [];
        $tree = $this->setTeamTree($result,0,$team_id,$expand,$checked);
        $root = [
            "id" => 0,
            "name" => "销售中心",
            "children" => $tree,
            "expand" => $expand,
            "checked" => $checked
        ];
        return $root;
    }

    public function getTeamMemberList($team_id)
    {
        //获取所有的团队
        $result = $this->getAllTeamMember();
        $list = [];
        if (isset($result[$team_id])) {
           $list = [
               "top_name" => $result[$team_id]["top_name"],
               "team_name" => $result[$team_id]["current_name"],
               "leader" => $result[$team_id]["team_leader"],
               "team_count" => count($result[$team_id]["users"]),
               "team_status" => $result[$team_id]["team_status"],
               "description" => $result[$team_id]["current_description"],
               "users" => []
           ];
           array_walk($result[$team_id]["users"],function($value) use (&$list){
               $list["users"][] = [
                   "id" => $value["current_id"],
                   "user_name" => $value["user_name"],
                   "role_name" => $value["role_name"],
                   "team_name" => $value["top_name"],
                   "status" => $value["status"],
                   "state" => $value["state"],
               ];
           });
        }
        return $list;
    }

    /**
     * 删除团队成员和团队的关联
     * @param $current_id
     * @return int
     * @throws \Exception
     */
    public function delTeamMemberMap($current_id)
    {
        try {
            $this->clearTeamCache();
            $model = new SaleTeamMap();
            return $model->delTeamMemberMap($current_id);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getTeamMemberInfo($id)
    {
        try {
            $model = new SaleTeamMap();
            return $model->getTeamMemberInfo($id);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function teamStatusUp($team_id)
    {
        try {
            //获取所有的团队
            $result = $this->getAllTeamMember();
            $list = [];
            if (isset($result[$team_id])) {
                $this->clearTeamCache();
                $model = new SaleTeam();
                //查询当前团队信息
                $info = $model->getTeamInfoById($team_id,1);

                $team_group_id = $result[$team_id]["team_group_id"];
                $model = new SaleTeam();
                $data = [
                    "status" => $info["status"] == 1?2:1,
                    "update_at" => time()
                ];
                return $model->updateGroupTeam($team_group_id,$data);
            }
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 树状图团队信息
     * @param $team_id
     */
    public function getTeamTreeInfoById($team_id)
    {
        //获取所有的团队
        $result = $this->getAllTeamMember();
        $info = [];
        if (isset($result[$team_id])) {
            $info = $result[$team_id];
        }
        return $info;
    }

    /**
     * 获取团队结构
     */
    public function getAllTeamMember()
    {
        $cache_key = CacheKeyCodeEnum::TEAM_MEMBER_ALL;
        $team = cache($cache_key);
        if (!$team) {
            $model = new SaleTeamMap();
            $result = $model->getAllTeamMember();
            if (count($result) > 0) {
                $result = $result->toArray();
                $team = $user = [];
                array_walk($result,function($value) use (&$team,&$user){
                    if ($value["module"] == 2) {
                        $user[] = $value;
                    } else {
                        $value["users"] = [];
                        $value["children"] = [];
                        $team[$value["current_id"]] = $value;
                    }
                });

                $this->teamRange($team);
                foreach ($team as $key=>$value) {
                    foreach ($user as $k => $val) {
                        // 所有下级
                        if (isset($value["team_group_id"]) && in_array($val["top_id"],$value["team_group_id"])) {
                            $team[$key]["users"][] = $val;
                        }

                        // 直属下级
                        if ($val["top_id"] == $value["current_id"]) {
                            $team[$key]["children"][] = $val;
                        }
                    }
                }

                //将团队人员进行排序
                foreach ($team as $key => $value) {
                    $sort1 = $sort2 = [];
                    foreach ($value["users"] as $user) {
                        $sort1[] = $user["status"];
                        $sort2[] = $user["state"];
                    }

                    array_multisort($sort1,SORT_DESC,$sort2,SORT_DESC,$value["users"]);
                    $team[$key]["users"] = $value["users"];
                    $team[$key]["team_group_id"] = array_reverse($value["team_group_id"]);
                }
            }
            
            if (!empty($team)) {
                cache($cache_key, $team, 86400);
            }
        }

        return $team ? : [];
    }

    public function getAllPerformance($begin,$end,$offset)
    {
        $dataset = [
            "date" =>[],
            "chart" => [],
            "achievement" => 0
        ];
        $week_array = array("日","一","二","三","四","五","六");
        //计算时间差
        for ($i=0; $i < $offset ; $i++) {
            $date = date("m-d",strtotime("+$i day",$begin));
            $dataset["chart"][$date] = [
                "day" => $date,
                "achievement" => 0,
                "week" => "周".$week_array[date("w",strtotime("+$i day",$begin))]
            ] ;
            $dataset["date"][] = $date;
        }

        //查询销售人员业绩
        $model = new SaleReportPayment();
        $result = $model->getSalesPaymentMoney(null, $begin, $end);

        foreach ($result as $item) {
            if (array_key_exists($item["payment_day"], $dataset["chart"])) {
                $pay_money = round($item["payment_money"] * ($item['share_ratio'] / 100),2);
                $dataset["chart"][$item["payment_day"]]["achievement"] += round($pay_money/10000,2);
                $dataset["achievement"] += $pay_money;
            }
        }
        $dataset["achievement"] = round($dataset["achievement"]/10000,2);
        return $dataset;
    }

    // 获取下拉顶级栏目列表
    public function getSelectTeamList($select_type = 1){

        $teamModel = new SaleTeam();
        $list = $teamModel->getTopTeamList($select_type, true);

        $list = array_map(function($item){
            return [
                "current_id" => $item["id"],
                "current_name" => $item["team_name"],
                "is_top" => $item["is_top"]
            ];
        }, $list->toArray());

        return $list;
    }

    /**
     * 获取团队下用户ID
     * type = 1 时获取所有用户
     * type = 2 时获取直属用户
     * @param  [type]  $team_id [description]
     * @param  integer $type    [description]
     * @return [type]           [description]
     */
    public function getTeamUserList($team_id, $type = 1){
        $teamList = $this->getAllTeamMember();

        $userList = [];
        foreach ($teamList as $key => $team) {
            if ($team["current_id"] == $team_id) {
                if ($type == 1) {
                    $userList = $team["users"];
                } else if ($type == 2) {
                    $userList = $team["children"];
                }
                break;
            }
        }

        return $userList;
    }
    
    // 获取用户所属顶级团队
    public function getUserTopTeamId($userIds){
        $teamList = $this->getAllTeamMember();

        $list = [];
        foreach ($teamList as $key => $team) {
            if (empty($team["top_id"])) {
                $teamuserids = array_column($team["users"], "current_id");
                foreach ($userIds as $k => $uid) {
                    if (in_array($uid, $teamuserids)) {
                        $list[$uid] = [
                            "team_id" => $team["current_id"],
                            "team_name" => $team["current_name"],
                        ];
                    }
                }
            }
        }

        return $list;
    }

    public function getTeamChart($team_id,$begin,$end,$status = 1)
    {
        //默认三个月的时间
        $month_start = date("Ym",strtotime("-2 month",strtotime(date("Y-m-01"))));
        $month_end = date("Ym");

        if (!empty($begin) && !empty($end)) {
            $month_start = str_replace("-","",$begin) ;
            $month_end =  str_replace("-","",$end) ;
        }

        $dataset = [
            "date" => [],
            "table_date" => [],
            "chart" => [],
            "table" => []
        ];

        //获取顶级团队信息
        $model = new SaleTeam();
        $result = $model->getTeamInfoById($team_id,1,$status);

        if (count($result) > 0) {
            $result = $result->toArray();
            $teamMember = $this->getAllTeamMember();
            $team = $user_ids = $team_ids = [];
            $team_leader = $result["team_leader"];

            //获取当前团队中给所有包含的下级团队
            if (!array_key_exists($result["current_id"], $teamMember)) {
                return false;
            }

            $team_ids = $teamMember[$result["current_id"]]["team_group_id"];

            //获取团队中下级销售的信息
            foreach ($team_ids as $item) {
                if (array_key_exists($item, $teamMember)) {
                    $team[$item]["current_id"] = $item;
                    $team[$item]["team_leader"] = $teamMember[$item]["team_leader"];
                    $team[$item]["top_id"] = $teamMember[$item]["top_id"];
                    $team[$item]["top_name"] = $teamMember[$item]["top_name"];
                    $team[$item]["team_name"] = $teamMember[$item]["current_name"];
                    $team[$item]["team_status"] = $teamMember[$item]["team_status"];
                    $team[$item]["user_ids"] = [];
                    foreach ($teamMember[$item]["users"] as $value) {
                        $user_ids[] = $value["current_id"];
                        $team[$item]["user_ids"][] = $value["current_id"];
                    }

                    $team[$item]["date"] = [];
                }
            }

            //查询团队的团队指标
            $model = new SaleIndicators();
            $result = $model->getTeamIndicators($team_ids, $month_start, $month_end);

            //查询团队的业绩
            $saleTeamAchievement = new SaleTeamAchievement();
            $achievement = $saleTeamAchievement->getTeamAchievement($team_ids, $begin, $end);
            $achievement = $achievement->toArray();
            $achArr = [];
            foreach ($achievement as $v) {
                $achArr[$v['team_id']][$v['reportdate']] = $v;
            }

            foreach ($result as $item) {
                if (!in_array($item["date"],$dataset["date"])) {
                    $dataset["date"][] = $item["date"];
                    $dataset["table_date"][] =  +date("m",strtotime($item["date"]."01"))."月";
                }

                $performance = $achArr[$item["current_id"]][$item["date"]]['achievement'] ?? 0;
                // 统计合计数据 start >>>
                if(!($team[$item["current_id"]]["date"]['合计'] ?? '')){
                    $team[$item["current_id"]]["date"]['合计'] = [
                        'indicators' => 0,
                        'date' => '合计',
                        'performance' => 0,
                        'percentage' => 0
                    ];
                }
                $team[$item["current_id"]]["date"]['合计']['indicators'] += floatval($item["value"]);
                $team[$item["current_id"]]["date"]['合计']['performance'] += $performance;
                // <<< 统计合计数据 end
                
                //将团队指标赋值到团队中去
                $team[$item["current_id"]]["date"][$item["date"]] = [
                    "indicators" => floatval($item["value"]),
                    "date" => $item["date"],
                    "performance" => $performance,
                    "percentage" => 0
                ];
            }

            //汇总业绩基表
            foreach ($team as $key => $value) {
                if (count($value["date"]) == 0) {
                    //删除未设置指标的团队
                    unset($team[$key]);
                    continue;
                }

                //如果没启用 , 则不计算
                if ($status == 1 && $value['team_status'] == 2) {
                    continue;
                }
                if ($status == 2 && $value['team_status'] == 1) {
                    continue;
                }

                //图标封装
                $team[$key]["indicators"] = 0;//指标
                $team[$key]["performance"] = 0;//业绩
                $team[$key]["percentage"] = 0;//占比

                foreach ($value["date"] as $k => $item) {
                    $team[$key]["indicators"] += $item["indicators"];
                    if($k != '合计'){
                        $team[$key]["performance"] += $item["performance"];    
                    }

                    if ($item["indicators"] > 0) {
                        $team[$key]["date"][$k]["percentage"] = round($item["performance"]/$item["indicators"],2)*100;
                    }

                    // 时间进度
                    $percentdate = 100;
                    if (date("Ym") == $k) {
                        $percentdate = date("d") / date("t") * 100;
                        $percentdate = round($percentdate, 2);
                    }

                    if($k != '合计'){
                        $percentdate_over = round($team[$key]["date"][$k]["percentage"] - $percentdate, 2);
                    }else{
                        // 统计合计数据 start >>>
                        $beginTime = strtotime($begin . '-01 00:00:00');
                        $endTime = strtotime("$end +1 month");
                        $nowTime = time();
                        $checkeDays = ceil( ($endTime - $beginTime)/86400 );

                        if($nowTime > $endTime){
                            $nowDays = $checkeDays;
                        }else{
                            $nowDays = ceil( ($nowTime - $beginTime)/86400 );
                        }

                        $percentdate_over = round($team[$key]["date"][$k]["percentage"]/100 - ($nowDays / $checkeDays), 2) * 100;
                        // <<< 统计合计数据 end
                    }

                    //表格数据封装
                    $dataset["table"][$k][] =  [
                        "team_leader" => $value["team_leader"],
                        "top_id" => $value["top_id"],
                        "top_name" => $value["top_name"],
                        "team_name" => $value["team_name"],
                        "current_id" => $value["current_id"],
                        "indicators" => $team[$key]["date"][$k]["indicators"],
                        "performance" => floatval($team[$key]["date"][$k]["performance"]),
                        "percentage" => $team[$key]["date"][$k]["percentage"],
                        "percentdate_over" => $percentdate_over,
                        "nofinish_amount" => $team[$key]["date"][$k]["indicators"] - $team[$key]["date"][$k]["performance"]
                    ];
                    
                    $team[$key]["date"][$k]["indicators"] = round($item["indicators"]/10000,2);
                    $team[$key]["date"][$k]["performance"] = round($item["performance"]/10000,2);
                }

                if($team[$key]["indicators"] > 0){
                    $team[$key]["percentage"] = round($team[$key]["performance"]/$team[$key]["indicators"],2)*100;
                }

                unset($team[$key]["user_ids"]);
                unset($team[$key]['date']['合计']);
                $dataset["chart"][] = $team[$key];
            }

            // 顶级团队数据在下方显示
            foreach ($dataset["table"] as $key => $item) {
                foreach ($dataset["table"][$key] as $k => $val) {
                    if ($val["current_id"] == $team_id) {
                        unset($dataset["table"][$key][$k]);
                        $dataset["table"][$key][] = $val;
                        $dataset["table"][$key] = array_values($dataset["table"][$key]);
                        break;
                    }
                }
            }

            // 查询销售中心业绩
            $total = $this->getSaleCenterAchievement($begin, $end);

            foreach ($total["table"] as $date => $item) {
                if (!array_key_exists($date, $dataset["table"])) {
                    $dataset["table"][$date] = [];
                }

                // 销售中心数据放最下方
                $dataset["table"][$date][] = $item;
                // array_unshift($dataset["table"][$date], $item);
            }

            if($dataset['table_date']){
                $dataset['table_date'][] = '合计';
            }

            //获取部门下直接归属人员ID
            $model = new SaleTeamMap();
            $result = $model->getTeamListByTop($team_id,2);

            $persons = $ids = [];

            if (count($result) > 0) {
                $result = $result->toArray();

                foreach ($result as $item) {
                    $persons = [
                        "team_leader" => $item["name"],
                        "team_name" => $item["team_name"],
                        "top_name" => $item["name"]
                    ];
                }

                $ids = array_column($result,"current_id");

                //获取团队部门领导人个人业绩
                $model = new SaleTeamAchievement();
                $month_start = strtotime($month_start."01");
                $month_end =  strtotime("+1 month",strtotime($month_end."01")) -1;
                $result = $model->getPersonAchievement($ids,$month_start,$month_end);

                if (count($result) > 0) {
                    $result = $result->toArray();

                    foreach ($result as $item) {
                        $end_first = array_pop($dataset["table"][$item["date"]]);
                        $end_second = array_pop($dataset["table"][$item["date"]]);

                        $dataset["table"][$item["date"]][] = [
                            "team_leader" => $persons["team_leader"],
                            "top_id" => 0,
                            "top_name" => $persons["top_name"],
                            "team_name" => '其他',
                            "current_id" => 0,
                            "indicators" => '',
                            "performance" => $item["achievement"],
                            "percentage" => 0,
                            "percentdate_over" => 0,
                            "nofinish_amount" => 0
                        ];
                        $dataset["table"][$item["date"]][] = $end_second;
                        $dataset["table"][$item["date"]][] = $end_first;
                    }
                }
            }
        }

        return $dataset;
    }

    // 销售中心业绩统计
    public function getSaleCenterAchievement($begin, $end){
        //默认三个月的时间
        $month_start = date("Ym",strtotime("-2 month",strtotime(date("Y-m-01"))));
        $month_end = date("Ym");

        if (!empty($begin) && !empty($end)) {
            $month_start = str_replace("-","",$begin) ;
            $month_end =  str_replace("-","",$end) ;
        }

        $dataset = [
            "date" => [],
            "table" => []
        ];

        $teamMember = $this->getAllTeamMember();
        $team = $user_ids = $team_ids = [];

        //获取顶级团队的信息
        foreach ($teamMember as $team_id => $item) {
            if (empty($item["top_id"])) {
                $team_ids[] = $item["current_id"];
                $team[$team_id]["current_id"] = $item["current_id"];
                $team[$team_id]["team_leader"] = $item["team_leader"];
                $team[$team_id]["top_id"] = $item["top_id"];
                $team[$team_id]["top_name"] = $item["top_name"];
                $team[$team_id]["team_name"] = $item["current_name"];
                $team[$team_id]["user_ids"] = [];
                $team[$team_id]["date"] = [];
                foreach ($item["users"] as $value) {
                    $user_ids[] = $value["current_id"];
                    $team[$team_id]["user_ids"][] = $value["current_id"];
                }
            }
        }

        //查询团队的团队指标
        $model = new SaleIndicators();
        $result = $model->getTeamIndicators($team_ids, $month_start, $month_end);

        //查询团队的业绩
        $saleTeamAchievement = new SaleTeamAchievement();
        $achievement = $saleTeamAchievement->getTeamAchievement($team_ids, $begin, $end);
        $achievement = $achievement->toArray();
        $achArr = [];
        foreach ($achievement as $v) {
            $achArr[$v['team_id']][$v['reportdate']] = $v;
        }

        foreach ($result as $item) {
            if (!in_array($item["date"],$dataset["date"])) {
                $dataset["date"][] = $item["date"];
                $dataset["table_date"][] =  +date("m",strtotime($item["date"]."01"))."月";
            }

            $performance = $achArr[$item["current_id"]][$item["date"]]['achievement'] ?? 0;
            // 统计合计数据 start >>>
            if(!($team[$item["current_id"]]["date"]['合计'] ?? '')){
                $team[$item["current_id"]]["date"]['合计'] = [
                    'indicators' => 0,
                    'date' => '合计',
                    'performance' => 0,
                    'percentage' => 0
                ];
            }

            $team[$item["current_id"]]["date"]['合计']['indicators'] += floatval($item["value"]);
            $team[$item["current_id"]]["date"]['合计']['performance'] += $performance;
            // <<< 统计合计数据 end

            //将团队指标赋值到团队中去
            $team[$item["current_id"]]["date"][$item["date"]] = [
                "indicators" => floatval($item["value"]),
                "date" => $item["date"],
                "performance" => $performance,
                "percentage" => 0
            ];


        }

        //汇总业绩基表
        foreach ($team as $key => $value) {
            if(!$value["date"]){
                continue;
            }
            $indicators = 0;
            $performance = 0;
            foreach ($value["date"] as $k => $item) {
                if (!array_key_exists($k, $dataset["table"])) {
                    $dataset["table"][$k] = [
                        "team_leader" => "———",
                        "top_id" => "0",
                        "top_name" => "销售中心",
                        "team_name" => "———",
                        "indicators" => 0,
                        "performance" => 0,
                        "percentage" => 0,
                        "percentdate_over" => 0,
                        "nofinish_amount" => 0
                    ];
                }

                // 时间进度
                $percentdate = 100;
                if (date("Ym") == $k) {
                    $percentdate = date("d") / date("t") * 100;
                    $percentdate = sprintf("%.2f", $percentdate);
                }

                $dataset["table"][$k]["indicators"] += $item["indicators"];
                $dataset["table"][$k]["performance"] += $item["performance"];
                $dataset["table"][$k]["nofinish_amount"] = $dataset["table"][$k]["indicators"] - $dataset["table"][$k]["performance"];

                if ($dataset["table"][$k]["indicators"] > 0) {
                    $dataset["table"][$k]["percentage"] = round($dataset["table"][$k]["performance"] / $dataset["table"][$k]["indicators"], 2) * 100;
                }

                if($k != '合计'){
                    $dataset["table"][$k]["percentdate_over"] = round($dataset["table"][$k]["percentage"] - $percentdate, 2);
                }
            }
        }

        // 统计合计数据 start >>>
        if($dataset["table"]['合计'] ?? ''){
            $beginTime = strtotime($begin . '-01 00:00:00');
            $endTime = strtotime("$end +1 month");
            $nowTime = time();
            $checkeDays = ceil( ($endTime - $beginTime)/86400 );

            if($nowTime > $endTime){
                $nowDays = $checkeDays;
            }else{
                $nowDays = ceil( ($nowTime - $beginTime)/86400 );
            }

            $dataset["table"]['合计']["percentdate_over"] = round($dataset["table"]['合计']["percentage"]/100 - ($nowDays / $checkeDays), 2) * 100;
        }
        // <<< 统计合计数据 end

        return $dataset;
    }


    public function getTeamReportPaymentCountByDay($begin,$end)
    {
        $model = new SaleReportPayment();
        return $model->getTeamReportPaymentCountByDay($begin,$end);
    }

    public function getReportCountByDay($begin,$end)
    {
        $model = new SaleReportZxCompany();
        return $model->getReportCountByDay($begin,$end);
    }

    public function getMemberChart($begin,$end)
    {
        $model = new SaleReportZxCompany();
        $dataset = [
            "pie_type" => [],
            "pie_money" => []
        ];
        $all = [
            "count" => 0,
            "money" => 0
        ];
        //合作类型
        $result = $model->getMemberChart($begin,$end);

        if (count($result) > 0) {
            $result = $result->toArray();
            foreach ($result as $item) {
                $all["count"] +=  $item["count"];
            }

            foreach ($result as $item) {
                $type_name = SalesReportCodeEnum::getItem("statistic_cooperation_type", $item["cooperation_type"]);
                $dataset["pie_type"][$item["cooperation_type"]]["type_name"] = $type_name;

                $dataset["pie_type"][$item["cooperation_type"]]["type"] = $item["cooperation_type"];
                $dataset["pie_type"][$item["cooperation_type"]]["count"] = $item["count"];
                $dataset["pie_type"][$item["cooperation_type"]]["count_rate"] = 0;

                if ($all["count"] > 0) {
                    $list[$item["cooperation_type"]]["count_rate"] = round($dataset["pie_type"][$item["cooperation_type"]]["count"]/$all["count"],2)*100;
                }
            }
        }

        //合作金额
        $model = new SaleReportPayment();
        $result = $model->getMemberChart($begin,$end);
        if (count($result) > 0) {
            $result = $result->toArray();
            foreach ($result as $item) {
                $all["money"] +=  $item["money"];
            }
            $all["money"] = round($all["money"]/10000,2);
            foreach ($result as $item) {
                $type_name = ReportPaymentCodeEnum::getItem("statistic_cooperation_type", $item["cooperation_type"]);
                $dataset["pie_money"][$item["cooperation_type"]]["type_name"] = $type_name;

                // switch ($item["cooperation_type"]){
                //     case "1": $dataset["pie_money"][$item["cooperation_type"]]["type_name"] = "常规"; break;
                //     case "2": $dataset["pie_money"][$item["cooperation_type"]]["type_name"] = "独家";break;
                //     case "3": $dataset["pie_money"][$item["cooperation_type"]]["type_name"] = "垄断";break;
                //     case "4": $dataset["pie_money"][$item["cooperation_type"]]["type_name"] = "三维家";break;
                //     case "6": $dataset["pie_money"][$item["cooperation_type"]]["type_name"] = "签约返点";break;
                //     case "7": $dataset["pie_money"][$item["cooperation_type"]]["type_name"] = "全屋定制";break;
                //     case "8": $dataset["pie_money"][$item["cooperation_type"]]["type_name"] = "会员返点";break;
                //     case "9": $dataset["pie_money"][$item["cooperation_type"]]["type_name"] = "物料";break;
                // }
                $dataset["pie_money"][$item["cooperation_type"]]["type"] = $item["cooperation_type"];
                $dataset["pie_money"][$item["cooperation_type"]]["money"] = round($item["money"]/10000,2);
                $dataset["pie_money"][$item["cooperation_type"]]["money_rate"] = 0;
                if ($all["money"] > 0) {
                    $dataset["pie_money"][$item["cooperation_type"]]["money_rate"] = round($dataset["pie_money"][$item["cooperation_type"]]["money"]/$all["money"],2)*100;
                }
            }
        }

        return $dataset;
    }

    public function getNowPerformance($begin,$end)
    {
        //查询销售人员业绩
        $model = new SaleReportPayment();
        $result = $model->getSalesPaymentMoney(null, $begin, $end);

        if (count($result) > 0) {
            $result = $result->toArray();
            $pay_money = 0;
            foreach ($result as $item) {
                $pay_money += round($item["payment_money"] * ($item['share_ratio'] / 100),2);
            }
            return round($pay_money/10000,2);
        }
        return 0;
    }


    public function getTeamIndicatorsChart($begin,$end)
    {
        $date = $user_ids = $team = [];
        $dataset = [
            "date" => [],
            "legend" => [],
            "series" => [],
            "pie" => [
                "type" => [],//收款类型占比
                "payee" => []//收款方占比
            ]
        ];
        $offset = ceil(($end - $begin)/86400);
        //获取时间轴
        $week_array = array("日","一","二","三","四","五","六");
        for ($i=0; $i < $offset ; $i++) {
            $day = date("m-d",strtotime("+$i day",$begin));
            $date[$day] = [
                "date" => $day,
                "week" => "周".$week_array[date("w",strtotime("+$i day",$begin))],
                "performance" => 0
            ];

            $dataset["date"][] = [
                "date" => $day,
                "week" => "周".$week_array[date("w",strtotime("+$i day",$begin))],
            ];
        }

        //获取顶级团队信息
        $model = new SaleTeam();
        $result = $model->getTopTeamList(1,1);

        if ($result) {
            $result = $result->toArray();
            foreach ($result as $item) {
                $team[$item["id"]] = [
                    "team_name" => $item["team_name"],
                    "date" => $date
                ];
            }

            //获取团队的人员信息
            $teamMember = $this->getAllTeamMember();
            foreach ($team as $key => $item) {
               if (array_key_exists($key,$teamMember)) {
                   foreach ($teamMember[$key]["users"] as $value) {
                       $user_ids[] = $value["current_id"];
                       $team[$key]["user_ids"][] = $value["current_id"];
                   }
               } else {
                   //删除不存在的KEY
                   unset($team[$key]);
               }
            }

            //查询销售人员业绩
            $model = new SaleReportPayment();
            $result = $model->getSalesPaymentMoney($user_ids, $begin, $end);

            $all_count = 0;
            if (count($result) > 0) {
                $result = $result->toArray();
                $paymentIds = array_column($result, "payment_id");

                $reportPaymentLogic = new ReportPaymentLogic();
                $paymentPayeeList = $reportPaymentLogic->getPaymentPayeeList($paymentIds);

                $payment_type_alreadys = [];
                $payment_payee_alreadys = [];
                foreach ($team as $key => $value) {
                    foreach ($result as $val) {
                        if (isset($value["user_ids"]) && in_array($val["saler_id"], $value["user_ids"])) {
                            if (array_key_exists($val["payment_day"], $team[$key]["date"])) {
                                $all_count ++;
                                $pay_money = round($val["payment_money"] * ($val['share_ratio'] / 100)/10000,2);
                                $team[$key]["date"][$val["payment_day"]]["performance"] += $pay_money;

                                // 收款类型占比
                                if (!in_array($val["payment_id"], $payment_type_alreadys)) {
                                    $payment_type_alreadys[] = $val["payment_id"];
                                    if (!array_key_exists($val["payment_type"],$dataset["pie"]["type"])) {
                                        $dataset["pie"]["type"][$val["payment_type"]] = [
                                            "name" => "",
                                            "count" => 0
                                        ];
                                    }

                                    $type = ReportPaymentCodeEnum::getItem("payment_type", $val["payment_type"]);
                                    $dataset["pie"]["type"][$val["payment_type"]]["name"] = $type;
                                    $dataset["pie"]["type"][$val["payment_type"]]["count"] +=  1 ;
                                }

                                // 收款方占比
                                if (!in_array($val["payment_id"], $payment_payee_alreadys)) {
                                    $payment_payee_alreadys[] = $val["payment_id"];
                                    if (array_key_exists($val["payment_id"], $paymentPayeeList)) {
                                        foreach ($paymentPayeeList[$val["payment_id"]] as $payee) {
                                            if (!array_key_exists($payee["payee_type"], $dataset["pie"]["payee"])) {
                                                $dataset["pie"]["payee"][$payee["payee_type"]] = [
                                                    "name" => "",
                                                    "count" => 0
                                                ];
                                            }
                                            $dataset["pie"]["payee"][$payee["payee_type"]]["name"] = $payee["payee_type_name"];
                                            $dataset["pie"]["payee"][$payee["payee_type"]]["count"] ++ ;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($team as $item) {
            $dataset["legend"][] = $item["team_name"];
            $sub = [
                "name" =>  $item["team_name"],
                "data" => [],
            ];
            foreach ($item["date"] as $value) {
                $sub["data"][] = $value["performance"];
            }
            $dataset["series"][] = $sub;
        }

        return $dataset;
    }

    /**
     * 团队每日业绩统计
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    public function getTopTeamDailyAchievement($date_begin, $date_end){
        $result = [
            "team_list" => [],
            "achievement_total" => 0, // 合计总业绩
            "refund_achievement_total" => 0, //退款业绩
            "team_achievement_total" => 0, // 团队总业绩
            "person_max_achievement" => 0, // 最高个人业绩金额
            "person_list" => [] // 最高业绩人
        ];

        $topTeamList = [];
        $teamList = $this->getAllTeamMember();
        foreach ($teamList as $team) {
            if (empty($team["top_id"]) && $team["team_status"] == 1) {
                $userList = $team["users"] ? : [];
                $topTeamList[$team["current_id"]] = [
                    "current_id" => $team["current_id"],
                    "current_name" => $team["current_name"],
                    "users" => array_column($userList, "current_id")
                ];
            }
        }

        // 查询业绩
        $reportPaymentModel = new SaleReportPayment();
        $list = $reportPaymentModel->getSalerAchievement($date_begin, $date_end);
        if (count($list) > 0) {
            // 最高个人业绩金额
            $result["person_max_achievement"] = floatval($list[0]["achievement"]);

            foreach ($list as $key => $item) {
                $item["achievement"] = floatval($item["achievement"]); // 业绩金额
                $result["achievement_total"] += $item["achievement"]; // 合计总业绩
                $result['refund_achievement_total'] += floatval($item['refund_achievement']); // 合计退款业绩

                // 最高业绩人
                if ($item["achievement"] == $result["person_max_achievement"]) {
                    $result["person_list"][] = $item;
                }

                // 团队业绩
                foreach ($topTeamList as $team_id => $team) {
                    if (in_array($item["saler_id"], $team["users"])) {
                        if (!array_key_exists($team_id, $result["team_list"])) {
                            $result["team_list"][$team_id] = [
                                "current_id" => $team["current_id"],
                                "current_name" => $team["current_name"],
                                "achievement" => 0
                            ];
                        }
                        $result["team_list"][$team_id]["achievement"] += $item["achievement"];
                        $result["team_achievement_total"] += $item["achievement"];
                    }
                }
            }

            $result["team_list"] = array_values($result["team_list"]);
        }

        return $result;
    }

    protected function clearTeamCache($key = "")
    {
        if (empty($key)) {
            $key = CacheKeyCodeEnum::TEAM_MEMBER_ALL;
        }

        cache($key, null);
    }

    //递归
    protected function teamRange(&$data,$top_id = 0)
    {
        foreach ($data as $key =>$value) {
            if ($value["top_id"] == $top_id) {
                $this->teamRange($data,$value["current_id"]);
                if (!isset($data[$top_id]["team_group_id"]) && $top_id != 0) {
                    $data[$top_id]["team_group_id"] = [];
                }
                //封装对应的团队IDid
                $data[$value["current_id"]]["team_group_id"][] =  $value["current_id"];

                if ($top_id != 0) {
                    //将ID合并到父类的团队中
                    $data[$top_id]["team_group_id"] = array_merge($data[$value["current_id"]]["team_group_id"],$data[$top_id]["team_group_id"]);
                }
            }
        }
    }

    //封装树状图
    protected function setTeamTree($data,$top_id,$team_id,&$expand = [],&$checked = [])
    {
        $tree = [];
        foreach ($data as $value) {
            if ($value["top_id"] == $top_id) {
                if (in_array($team_id,$value["team_group_id"]) && $value["current_id"] != $team_id) {
                    array_push($expand,$value["current_id"]);
                }
                if ($value["current_id"] == $team_id) {
                    array_push($checked,$value["current_id"]);
                }
                $sub = [
                    "id" => $value["current_id"],
                    "name" => $value["current_name"],
                    "children" => []
                ];
                $sub["children"] = $this->setTeamTree($data,$value["current_id"],$team_id,$expand,$checked);
                $tree[] = $sub;
            }
        }
        return $tree;
    }

    /**
    * 获取销售及其团队下的所有成员
    */
    public static function getAccessAuthUsersIDs()
    {
        $user = Request::instance()->user;

        if($user['is_saler'] == 1){
            $allTeamMember = (new self())->getAllTeamMember();

            $salers = [];
            foreach ($allTeamMember as $key => $value) {
                if($value['team_leader_id'] == $user['user_id'] || $value['team_assistant_id'] == $user['user_id']){
                    $salers = array_merge($salers, array_column($value['users'], 'current_id'));
                }
            }
            $salers[] = $user['user_id'];
            $userIds = array_filter(array_unique($salers));
        }else{
            $userIds = 0;
        }

        return $userIds;
    }

    /*
    * 获取销售顶级团队领导人
    */
    public function getTeamTopLeaderByUser($user_id){
        $team = $this->getAllTeamMember();

        $leader = [];
        foreach ($team as $key => $value) {
            $userIDs = array_column($value['users'], 'current_id');
            if(in_array($user_id, $userIDs) && $value['top_id'] === 0){
                $leader['id'] = $value['team_leader_id'];
                $leader['name'] = $value['team_leader'];
                $leader['team_id'] = $value['current_id'];
                $leader['team_name'] = $value['current_name'];
                break;
            }
        }

        return $leader;
    }

    public function delTeamManage($id)
    {
        $model = new SaleTeamManageExtend();
         return $model->delTeamManage($id);
    }

    public function addAllManager($data)
    {
        $model = new SaleTeamManageExtend();
        return $model->addALLInfo($data);
    }
}