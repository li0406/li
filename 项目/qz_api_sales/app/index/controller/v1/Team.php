<?php

namespace app\index\controller\v1;

use app\common\enum\BaseStatusCodeEnum;
use app\common\model\logic\IndicatorsLogic;
use app\common\model\logic\TeamLogic;
use app\common\model\logic\UserLogic;
use think\Db;
use think\Exception;
use think\Request;
use think\Controller;

class Team extends Controller
{
    public function getTeamList()
    {
        try {
            $param = input("get.","");
            $name = empty($param["name"])?"":$param["name"];
            $leader = empty($param["leader"])?"":$param["leader"];
            $team = empty($param["team"])?"":$param["team"];
            $status = empty($param["status"])?"":$param["status"];
            $page = empty($param["page"])?1:$param["page"];

            $logic = new TeamLogic();
            $result = $logic->getTeamList($name,$leader,$team,$status,$page);
            return  sys_response(0,BaseStatusCodeEnum::CODE_0,$result);
        } catch (\Exception $e) {
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
    }

    public function getTeamUp()
    {
        try {
            $team_id = input("get.team_id");
            if (empty($team_id)) {
                return sys_response(4000002, BaseStatusCodeEnum::CODE_4000002);
            }
            $logic = new TeamLogic();
            $result = $logic->getTeamInfoById($team_id,1);
            return sys_response(0, BaseStatusCodeEnum::CODE_0, $result);
        } catch (\Exception $e) {
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
    }

    public function setTeamUp(Request $request)
    {
        if ($request->isPost()) {
            $param = input("post.");

            $validate = new \app\index\validate\Team();
            if (!$validate->scene("TeamUp")->check($param)) {
                return sys_response(4000019,$validate->getError());
            }
            $id = 0;

            if (isset($param["id"])) {
                $id = $param["id"];
            }

            $manage = [];
            if (isset($param["manage"])) {
                $manage = $param["manage"];
                unset($param["manage"]);
            }


            $logic = new TeamLogic();
            //验证团队名称是否重复
            $info = $logic->getTeamInfoByName($param["name"]);

            if (empty($id)) {
                if (count($info) > 0) {
                    return sys_response(4000019,"已存在该团队");
                }
            } elseif (!empty($id) && count($info) > 0 && $info["id"] != $id) {
                return sys_response(4000019,"已存在该团队");
            }

            if (!empty($id) && $param["top_team"] ==  $id) {
                return sys_response(4000019,"无法添加当前团队");
            }

            //编辑状态下，验证上级团队是否是自己包含的子集团队
            if (!empty($id)) {
                $result = $logic->getTeamTreeInfoById($id);
                $team_group_id = $result["team_group_id"];
                if (count($team_group_id) > 0 && in_array($param["top_team"],$team_group_id)) {
                    return sys_response(4000019,"无法添加下属团队成员为上级团队");
                }
            }

            $data = [
                'team_name' => $param["name"],
                'team_leader' => $param["leader"],
                'description' => $param["description"],
                'px' => $param["px"],
                'update_at' => time(),
            ];

            $data["is_top"] = empty($param["top_team"])?1:2;

            try {
                Db::startTrans();
                if (empty($id)) {
                    //新增
                    $data['create_at'] = time();
                    $id = $logic->addTeam($data);
                } else {
                    //编辑
                    $logic->updateTeam($id,$data);
                }

                $top_id = empty($param["top_team"])?0:$param["top_team"];
                //删除当前的关联关系
                $logic->delTeamMap($id);

                //添加关联关系
                $data = [
                    "top_id" => $top_id,
                    "current_id" => $id,
                    "module" => 1
                ];
                $logic->addTeamMap($data);

                //删除副负责人的数据
                $logic->delTeamManage($id);

                //添加负责人数据
                if (count($manage) > 0) {
                    $all = [];
                    foreach ($manage as $item) {
                        $all[] = [
                            "team_id" => $id,
                            "user_id" => $item
                        ];
                    }
                    $logic->addAllManager($all);
                }

                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
            }
            return  sys_response(0,BaseStatusCodeEnum::CODE_0);
        }

        return sys_response(4000001,BaseStatusCodeEnum::CODE_4000001);
    }

    public function getTopTeamList()
    {
        try {
            $logic = new TeamLogic();
            $result = $logic->getTopTeamList();
            return sys_response(0, BaseStatusCodeEnum::CODE_0, $result);
        } catch (\Exception $e) {
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
    }

    public function getTeamTree()
    {
        try {
            $team_id = input("get.team_id");
            $logic = new TeamLogic();
            $result = $logic->getTeamTree($team_id);
            return sys_response(0, BaseStatusCodeEnum::CODE_0, $result);
        } catch (\Exception $e) {
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
    }

    public function getTeamMember()
    {
        try {
            $team_id = input("get.team_id");
            if (empty($team_id)) {
                return sys_response(4000002, BaseStatusCodeEnum::CODE_4000002);
            }
            $logic = new TeamLogic();
            $result = $logic->getTeamMemberList($team_id);
            return sys_response(0, BaseStatusCodeEnum::CODE_0, $result);
        } catch (\Exception $e) {
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
    }

    public function setTeamMemberUp()
    {
        try {
            Db::startTrans();
            $param = input("post.");

            $validate = new \app\index\validate\Team();
            if (!$validate->scene("TeamMemberUp")->check($param)) {
                return sys_response(4000019,$validate->getError());
            }

            $logic = new TeamLogic();
            //删除团队成员
            $logic->delTeamMemberMap( $param["id"]);
            //添加团队成员
            $data = [
                "top_id" =>  $param["team_id"],
                "current_id" => $param["id"],
                "module" => 2
            ];
            $logic->addTeamMap($data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
        return sys_response(0, BaseStatusCodeEnum::CODE_0);
    }

    public function getTeamMemberInfo()
    {
        try {
            $id = input("get.id");
            if (empty($id)) {
                return sys_response(4000002, BaseStatusCodeEnum::CODE_4000002);
            }
            $logic = new TeamLogic();
            $result = $logic->getTeamMemberInfo($id);
            return sys_response(0, BaseStatusCodeEnum::CODE_0, $result);
        } catch (\Exception $e) {
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
    }

    public function removeTeamMember()
    {
        try {
            $id = input("post.id");
            if (empty($id)) {
                return sys_response(4000002, BaseStatusCodeEnum::CODE_4000002);
            }
            $logic = new TeamLogic();
            //删除团队成员
            $logic->delTeamMemberMap($id);
            return sys_response(0, BaseStatusCodeEnum::CODE_0);
        } catch (\Exception $e) {
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
    }

    public function teamStatusUp()
    {
        try {
            $id = input("post.id");
            if (empty($id)) {
                return sys_response(4000002, BaseStatusCodeEnum::CODE_4000002);
            }
            $logic = new TeamLogic();
            $logic->teamStatusUp($id);
            return sys_response(0, BaseStatusCodeEnum::CODE_0);
        } catch (\Exception $e) {
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
    }

    /**
     * 获取团队下拉列表
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getSelectList(Request $request){
        $select_type = $request->get("select_type", 1);

        $teamLogic = new TeamLogic();
        $teamList = $teamLogic->getSelectTeamList($select_type);

        $response = sys_response(0, "", [
                "list" => $teamList
            ]);
        
        return json($response);
    }

    public function getPerformanceChart()
    {
        try {
            $end = strtotime(date("Y-m-d")) + 86400 -1;
            $begin = mktime(0,0,0,date("m",$end),1,date("Y"));

            if (input("get.begin") != "" && input("get.end") != "") {
                $begin = strtotime(input("get.begin"));
                $end = strtotime(input("get.end")) + 86400 -1;
            }

            $offset = ceil(($end - $begin)/86400);

            if ($offset > 31) {
                return  sys_response(4000019,"查询时间不超过31天");
            }

            $logic = new TeamLogic();
            //折线图数据
            $result = $logic->getAllPerformance($begin,$end,$offset);
            //今日业绩
            $end = strtotime(date("Y-m-d")) + 86400 -1;
            $begin = strtotime(date("Y-m-d"));
            $result["now"] = $logic->getNowPerformance($begin,$end);

            return  sys_response(0,BaseStatusCodeEnum::CODE_0,$result);

        } catch (\Exception $e) {
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
    }

    public function getTeamChart()
    {
        try {
            $begin = input("get.begin");
            $end = input("get.end");
            $team_id = input("get.team_id");
            $status = input("get.status",1);

            if (empty($team_id)) {
                return  sys_response(4000019,BaseStatusCodeEnum::CODE_4000019);
            }

            if (!empty($begin) && !empty($end)) {
                if (strpos($begin,"-") == false) {
                    $begin = date("Y-m",strtotime($begin."01"));
                }
                
                if (strpos($end,"-") == false) {
                    $end = date("Y-m",strtotime($end."01"));
                }

                if (strtotime("-5 month",strtotime($end)) > strtotime($begin) ) {
                    return  sys_response(4000019,"最长查询时间不能超过6个月");
                }
            }

            //获取团队信息
            $logic = new TeamLogic();
            $result = $logic->getTeamChart($team_id,$begin,$end,$status);
            return  sys_response(0,BaseStatusCodeEnum::CODE_0,$result);
        } catch (\Exception $e) {
            echo $e->getMessage();
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
    }

    public function getOtherChart()
    {
        try {
            $end = strtotime(date("Y-m-d")) + 86400 -1;
            $begin = mktime(0,0,0,date("m",$end),1,date("Y"));

            if (input("get.begin") != "" && input("get.end") != "") {
                $begin = strtotime(input("get.begin"));
                $end = strtotime(input("get.end")) + 86400 -1;
            }
            $offset = ceil(($end - $begin)/86400);

            if ($offset > 31) {
                return  sys_response(4000019,"查询时间不超过31天");
            }

            $result = [];
            $logic = new TeamLogic();
            //小报备次数
            $data["payment_count"] = $logic->getTeamReportPaymentCountByDay($begin,$end);
            //大报备/新会员/老会员
            $result = $logic->getReportCountByDay($begin,$end);
            $data["report_count"] = +$result["all_count"];
            $data["new_count"] = +$result["all_new_count"];
            $data["un_new_count"] = +$result["all_un_new_count"];

            //上会员
            $logic = new UserLogic();
            $data["member_count"] = $logic->getMemberCountByDay($begin,$end);

            return  sys_response(0,BaseStatusCodeEnum::CODE_0,$data);

        } catch (\Exception $e) {
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
    }

    public function getMemberChart()
    {
        try {
            $end = strtotime(date("Y-m-d")) + 86400 -1;
            $begin = mktime(0,0,0,date("m",$end),1,date("Y"));

            if (input("get.begin") != "" && input("get.end") != "") {
                $begin = strtotime(input("get.begin"));
                $end = strtotime(input("get.end")) + 86400 -1;
            }

            $offset = ceil(($end - $begin)/86400);

            if ($offset > 31) {
                return  sys_response(4000019,"查询时间不超过31天");
            }

            $logic = new TeamLogic();
            $data = $logic->getMemberChart($begin,$end);
            return  sys_response(0,BaseStatusCodeEnum::CODE_0,$data);
        } catch (\Exception $e) {
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
    }

    public function getTeamIndicatorsChart()
    {
        try {
            $end = strtotime(date("Y-m-d")) + 86400 -1;
            $begin = mktime(0,0,0,date("m",$end),1,date("Y"));

            if (input("get.begin") != "" && input("get.end") != "") {
                $begin = strtotime(input("get.begin"));
                $end = strtotime(input("get.end")) + 86400 -1;
            }

            $offset = ceil(($end - $begin)/86400);

            if ($offset > 31) {
                return  sys_response(4000019,"查询时间不超过31天");
            }

            $logic = new TeamLogic();
            $data = $logic->getTeamIndicatorsChart($begin,$end);
            return  sys_response(0,BaseStatusCodeEnum::CODE_0,$data);
        } catch (\Exception $e) {
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
    }

    /**
     * 获取团队日统计
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getTeamDailyAchievement(Request $request, TeamLogic $teamLogic){
        $date = $request->get("date");
        if (empty($date)) {
            $date = date("Y-m-d", strtotime("-1 day"));
        }

        $result = $teamLogic->getTopTeamDailyAchievement($date, $date);
        $result["date"] = $date;
        return sys_response(0, "", $result);
    }
}