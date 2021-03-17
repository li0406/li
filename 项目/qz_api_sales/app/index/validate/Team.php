<?php

namespace app\index\validate;

use think\Validate;

class Team extends Validate
{
    protected $message = [
        "name.require" => "请输入团队名称",
        "leader.require" => "请选择负责人",
        "id.require" => "请选择团队成员",
        "team_id.require" => "请选择团队"
    ];

    public function sceneTeamUp() {
        return $this->append("name","require")
                    ->append("leader","require") ;
    }

    public function sceneTeamMemberUp()
    {
        return $this->append("id","require")
            ->append("team_id","require") ;
    }

}