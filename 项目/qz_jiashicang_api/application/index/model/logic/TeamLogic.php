<?php
// +----------------------------------------------------------------------
// | TeamLogic 销售团队逻辑层
// +----------------------------------------------------------------------

namespace app\index\model\logic;

use think\facade\Request;
use app\index\model\adb\SaleTeam;
use app\index\model\adb\Adminuser;
use app\index\enum\CacheKeyCodeEnum;

class TeamLogic {

    protected $teamModel;

    protected $teamList;

    public function __construct(){
        $this->teamModel = new SaleTeam();
        $this->teamList = $this->getTeamAll();
    }

    // 获取销售团队管理下级用户
    public function getTeamAll(){
        $cachekey = CacheKeyCodeEnum::TEAM_ALL;
        $teamList = cache($cachekey);

        if (empty($teamList)) {
            $teamList = $this->teamModel->getTeamAll();
            if (count($teamList) > 0) {
                foreach ($teamList as $key => $item) {
                    $manage_ids = explode(",", $item["manage_ids"]);
                    $manage_ids[] = $item["team_leader"];

                    $teamList[$key]["manage_ids"] = array_filter(array_unique($manage_ids));
                }

                cache($cachekey, $teamList, rand(300, 600));
            }
        }

        return $teamList;
    }

    // 获取用户直接管辖的团队ID
    public function getAuthTeamIds($user_id) {
        $teamIds = [];
        foreach ($this->teamList as $key => $item) {
            if (in_array($user_id, $item["manage_ids"])) {
                $teamIds[] = $item["id"];
            }
        }

        return $teamIds;
    }

    // 获取团队下级团队ID（无限级团队需要递归）
    public function getTeamChildIds($teamIds){
        $childIds = [];
        foreach ($this->teamList as $key => $item) {
            if (in_array($item["top_id"], $teamIds)) {
                $childIds[] = $item["id"];
            }
        }

        if (count($childIds) > 0) {
            $cIds = $this->getTeamChildIds($childIds);
            $childIds = array_merge($childIds, $cIds);
        }

        return $childIds;
    }

    // 获取团队成员ID
    public function getTeamMemberIds($teamIds, $state = false, $need_manage = true){
        $memberList = $this->teamModel->getTeamMemberList($teamIds, $state);
        $memberIds = array_column($memberList, "saler_id");
        if ($need_manage == true) {
            foreach ($this->teamList as $key => $item) {
                if (in_array($item["id"], $teamIds)) {
                    $memberIds = array_merge($memberIds, $item["manage_ids"]);
                }
            }
        }

        return array_filter(array_unique($memberIds));
    }

    // 获取顶级团队列表
    public function getTeamTopList(){
        $topList = [];
        foreach ($this->teamList as $key => $team) {
            if (empty($team["top_id"])) {
                $topList[] = [
                    "team_id" => $team["id"],
                    "team_name" => $team["team_name"],
                ];
            }
        }

        return $topList;
    }
}