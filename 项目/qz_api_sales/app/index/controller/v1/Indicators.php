<?php

namespace app\index\controller\v1;

use app\common\enum\BaseStatusCodeEnum;
use app\common\model\logic\AdminuserLogic;
use app\common\model\logic\IndicatorsLogic;
use app\common\model\logic\TeamLogic;
use think\Controller;
use think\Db;

class Indicators extends Controller
{
    public function upTeamIndicators()
    {
        $file = $_FILES[array_keys($_FILES)[0]];
        if (count($file) > 0) {
            try {
                Db::startTrans();
                $tmp = $file["tmp_name"];
                $option = [];
                $data = read_execl($tmp,0,0,$option,8);
                //获取头部信息，获取年份和月份
                $head = array_shift($data);
                $reg = '/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\-([0-9]{2})/';
                preg_match($reg,$head["B"],$m);
                if (count($m) == 0) {
                    return  sys_response(5000002,"表格头部格式错误，请按照原表格格式上传，导入失败");
                }

                $year = "20".$m["2"];
                $month_arr = [
                    "B" => "01",
                    "C" => "02",
                    "D" => "03",
                    "E" => "04",
                    "F" => "05",
                    "G" => "06",
                    "H" => "07",
                    "I" => "08",
                    "J" => "09",
                    "K" => "10",
                    "L" => "11",
                    "M" => "12",
                ];

                if (count($data) == 0) {
                    return  sys_response(5000002,"请填充内容，导入失败");
                }

                //获取团队负责人信息
                $teamLogic = new TeamLogic();
                $team = $teamLogic->getAllTeamLeaderInfo();
                $name = array_unique(array_column($team,"id","name"));

                $ids = $all = [];
                foreach ($data as $key => $value) {
                    if (count($value) != 13) {
                        $all= [];
                        break;
                    }

                    $current_id = 0;
                    foreach ($value as $k => $val) {
                       $sub = [];
                       if ($k == "A") {
                           //查询团队是否包含再内
                            if (!array_key_exists($val,$name)) {
                               break;
                            }
                            $current_id = $name[$val];
                            $ids[] =   $current_id;
                       } else {
                           $sub["current_id"] = $current_id;
                           $sub["year"] = $year;
                           $sub["month"] = $month_arr[$k];
                           $sub["date"] = $year.$sub["month"];
                           $sub["value"] = empty($val)?0:+$val;
                           $sub["module"] = 1;
                           $sub["create_at"] = time();
                       }

                       if (count($sub) > 0) {
                            $all[] = $sub;
                       }
                    }
                }

                if (count($all) == 0) {
                    return  sys_response(5000002,"表格头部格式错误，请按照原表格格式上传，导入失败");
                }
                //删除当前的数据
                $logic = new IndicatorsLogic();
                $logic->delIndicatorsByUserIds($ids,$year);

                //添加数据
                $logic->addAllInfo($all);
                Db::commit();
                return  sys_response(0,BaseStatusCodeEnum::CODE_0);
            } catch (\Exception $e) {
                echo $e->getMessage();
                Db::rollback();
                return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
            }
        }
        return sys_response(4000019,BaseStatusCodeEnum::CODE_4000019);
    }

    public function upTeamMemberIndicators()
    {
        $file = $_FILES[array_keys($_FILES)[0]];
        if (count($file) > 0) {
            try {
                Db::startTrans();
                $tmp = $file["tmp_name"];
                $option = [];
                $data = read_execl($tmp,0,0,$option,8);
                //获取头部信息，获取年份和月份
                $head = array_shift($data);
                $reg = '/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\-([0-9]{2})/';
                preg_match($reg,$head["B"],$m);
                if (count($m) == 0) {
                    return  sys_response(5000002,"表格头部格式错误，请按照原表格格式上传，导入失败");
                }

                $year = "20".$m["2"];
                $month_arr = [
                    "B" => "01",
                    "C" => "02",
                    "D" => "03",
                    "E" => "04",
                    "F" => "05",
                    "G" => "06",
                    "H" => "07",
                    "I" => "08",
                    "J" => "09",
                    "K" => "10",
                    "L" => "11",
                    "M" => "12",
                ];

                if (count($data) == 0) {
                    return  sys_response(5000002,"请填充内容，导入失败");
                }
                  //获取销售中心所有人员信息
                $logic = new AdminuserLogic();
                $depts = $logic->department();
                $ids = array_column($depts,"id");
                $role = $logic->getRoleByDepartment($ids);
                $ids = array_column($role,"id");
                $user = $logic->getUserListByUid($ids);

                $name = array_unique(array_column($user,"id","user"));

                $ids = $all = [];
                foreach ($data as $key => $value) {
                    if (count($value) != 13) {
                        $all= [];
                        break;
                    }
                    $current_id = 0;
                    foreach ($value as $k => $val) {
                        $sub = [];
                        if ($k == "A") {
                            //查询团队是否包含再内
                            if (!array_key_exists($val,$name)) {
                               break;
                            }
                            $current_id = $name[$val];
                            $ids[] =   $current_id;
                        } else {
                            $sub["current_id"] = $current_id;
                            $sub["year"] = $year;
                            $sub["month"] = $month_arr[$k];
                            $sub["date"] = $year.$sub["month"];
                            $sub["value"] = empty($val)?0:+$val;
                            $sub["module"] = 2;
                            $sub["create_at"] = time();
                        }

                        if (count($sub) > 0) {
                            $all[] = $sub;
                        }
                    }
                }
                
                if (count($all) == 0) {
                    return  sys_response(5000002,"表格头部格式错误，请按照原表格格式上传，导入失败");
                }

                //删除当前的数据
                $logic = new IndicatorsLogic();
                $logic->delIndicatorsByUserIds($ids,$year,2);

                //添加数据
                $logic->addAllInfo($all);

                Db::commit();
                return  sys_response(0,BaseStatusCodeEnum::CODE_0);
            } catch (\Exception $e) {
                Db::rollback();
                return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
            }
        }
        return sys_response(4000019,BaseStatusCodeEnum::CODE_4000019);
    }

    public function getTeamIndicators()
    {
        try {
            $year = input("get.year");
            $team_name = input("get.team_name");
            $page = input("get.page");
            $logic = new IndicatorsLogic();
            $result = $logic->getTeamIndicatorsList($year,$team_name,$page);
            //表格头部的时间
            $result["head
            "] = $logic->getTableHeadDate($year);

            return  sys_response(0,BaseStatusCodeEnum::CODE_0,$result);
        } catch (\Exception $e) {
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
    }

    public function getTeamMemberIndicators()
    {
        try {
            $year = input("get.year");
            $name = input("get.name");
            $page = input("get.page");
            $logic = new IndicatorsLogic();
            $result = $logic->getTeamMemberIndicators($year,$name,$page);
            //表格头部的时间
            $result["head"] = $logic->getTableHeadDate($year);

            return  sys_response(0,BaseStatusCodeEnum::CODE_0,$result);
        } catch (\Exception $e) {
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
    }

    public function setIndicatorsUp()
    {
        try {
            Db::startTrans();
            $year = input("post.year");
            $current_id  = input("post.current_id");
            $module = input("post.module");
            $data =  input("post.data");
            $logic = new IndicatorsLogic();

            foreach ($data as $key => $value) {
                $sub = [
                    "value" => $value
                ];
                $m = $key +1;
                if ($m < 10) {
                    $m = "0".$m;
                }
                $month = $year.$m;
                $logic->editIndicators($current_id,$month,$module,$sub);
            }
            Db::commit();
            return  sys_response(0,BaseStatusCodeEnum::CODE_0);
        } catch (\Exception $e) {
            Db::rollback();
            return  sys_response(5000002,BaseStatusCodeEnum::CODE_5000002);
        }
    }
}