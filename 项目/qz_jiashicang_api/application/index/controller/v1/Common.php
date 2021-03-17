<?php
namespace app\index\controller\v1;

use think\Request;
use think\Controller;
use think\Cache;

use app\index\model\logic\TeamLogic;
use app\index\model\logic\AdminuserLogic;
use app\common\enum\BaseStatusCodeEnum;
use app\index\enum\CacheKeyCodeEnum;
/**
 * 公共模块快
 */
class Common extends Controller
{
    public function getMenu(Request $request)
    {
        $user = $request->user;

        //获取当前用户的节点
        $logic = new AdminuserLogic();
        $data = $logic->getMenu($user["role_id"]);

        //获取当前用户的菜单权限

        return sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
    }

    public function getUserInfo(Request $request)
    {
        $data = [
            "role_name" => $request->user["role_name"],
            "nick_name" => $request->user["nick_name"],
            "logo" => $request->user["logo"],
        ];
        return sys_response(0, BaseStatusCodeEnum::CODE_0, $data);
    }

    // 清除缓存
    public function cleanupCache(Request $request){
        $authuser = $request->user;

        $cachekeyList = [
            sprintf(CacheKeyCodeEnum::AUTH_TEAM_USERS, $authuser["user_id"]),
            sprintf(CacheKeyCodeEnum::AUTH_TEAM_CITYS, $authuser["user_id"]),
            CacheKeyCodeEnum::TEAM_ALL,
        ];

        $teamLogic = new TeamLogic();
        $teamTopList = $teamLogic->getTeamTopList();
        foreach ($teamTopList as $team) {
            $cachekeyList[] = sprintf(CacheKeyCodeEnum::TEAM_MEMBERS, $team["team_id"]);
        }

        foreach ($cachekeyList as $key => $cachekey) {
            cache($cachekey, null);
        }

        $response = sys_response(0, BaseStatusCodeEnum::CODE_0, false);
        return json($response);
    }
}