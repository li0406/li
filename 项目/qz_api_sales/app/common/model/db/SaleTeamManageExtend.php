<?php
namespace app\common\model\db;

use think\Model;
/**
 *
 */
class SaleTeamManageExtend extends Model
{
    public function delTeamManage($team_id)
    {
        $map[] = ["team_id","EQ",$team_id];
        return $this->where($map)->delete();
    }

    public function addALLInfo($data)
    {
        return $this->insertAll($data);
    }
}