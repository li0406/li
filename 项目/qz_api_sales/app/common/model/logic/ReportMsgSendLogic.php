<?php

namespace app\common\model\logic;

use think\Db;
use think\db\Query;
use think\facade\Request;

use app\common\model\db\Adminuser;
use app\common\model\service\MsgService;
use app\common\model\logic\AdminuserLogic;
use app\common\model\logic\TeamLogic;

class ReportMsgSendLogic {

    // 一审角色（蒋总审核）
    const EXAM_FIRST_ROLE = 1;
    
    // 终审用户 （远程中心-支撑部-在线客服）
    const EAXM_FINAL_ROLE = 81; // 终审角色
    const EXAM_FINAL_DEPARTMENT = 32; // 终审部门

    public function __construct(){
        $this->adminuserModel = new Adminuser();
    }

    /**
     * 获取一审用户（蒋总【超级管理员】）
     * @return [type] [description]
     */
    public function getExamFirstUsers(){
        $list = $this->adminuserModel->getListByUids(static::EXAM_FIRST_ROLE);
        return array_column($list, "id");
    }

    /**
     * 获取终审用户（远程中心-支撑部-在线客服）
     * @return [type] [description]
     */
    public function getExamFinalUsers(){
        $role = static::EAXM_FINAL_ROLE;
        $dept = static::EXAM_FINAL_DEPARTMENT;
        $list = $this->adminuserModel->getUserByDeptAndRole($dept, $role);
        return array_column($list, "id");
    }

    public function getExamSalerUsers($data){
        $teamLogic = new TeamLogic();

        $user = [];
        $leader = [];
        foreach ($data as $k => $v) {
            $leader[] = $teamLogic->getTeamTopLeaderByUser($v['saler_id']);
        }

        if($leader) $user = array_column($leader, 'id');

        return $user;
    }
}