<?php
/**
 *  装修公司团队表 qz_team
 */
namespace Common\Model;
use Think\Model;
class TeamModel extends Model{
    /**
     * 获取装修公司职位信息
     * @return [type] [description]
     */
    public function getZwInfo($comid = ''){
        $map = array(
            "comid"=>array("EQ",$comid)
                     );
        return M("team")->where($map)
                        ->order("px desc")
                        ->group("zw")
                        ->field("count(id) as count,zw")
                        ->select();
    }

    /**
     * 添加数据
     */
    public function addTeam($data){
        return M("team")->add($data);
    }

    /**
     * 编辑数据
     */
    public function editTeam($id,$comid ="",$data){
        $map = array(
                "userid"=>array("EQ",$id)
                     );
        if(!empty($comid)){
            $map["comid"] = array("EQ",$comid);
        }
        return M("team")->where($map)->save($data);
    }

    /**
     * 删除关联
     * @param  [type] $id    [description]
     * @param  [type] $comid [description]
     * @return [type]        [description]
     */
    public function deleteTeam($id,$comid){
        $map = array(
                "userid"=>array("EQ",$id),
                "comid"=>array("EQ",$comid)
                     );
        return M("team")->where($map)->delete();
    }

    /**
     * 查询团队信息
     * @param  [type] $id    [设计师编号]
     * @param  [type] $comid [装修公司编号]
     * @param  [type] $zw    [入住状态]
     * @return [type]        [description]
     */
    public function getTeamInfo($id,$comid,$zt =""){
         $map = array(
                "userid"=>array("EQ",$id),
                "comid"=>array("EQ",$comid)
                     );
         if(!empty($zt)){
            $map["zt"] = array("EQ",$zt);
         }
        return  M("team")->where($map)->count();
    }

    /**
     * 获取用户的团队信息
     * @param  [type] $id [用户编号]
     * @return [type]     [description]
     */
    public function getUserTeamInfo($id){
        $map = array(
                "userid"=>array("EQ",$id),
                "zt"=>array("EQ",2)//状态为入住
                     );
        return  M("team")->where($map)->find();
    }

    /**
     * 获取邀约的装修公司数量
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getInviteCompanyCount($id){
        $map = array(
                "userid"=>array("EQ",$id),
                "zt"=>array("EQ",0)//状态为未入住状态 0 未处理 1 拒绝
                     );
        return M("team")->where($map)->count();
    }

    /**
     * 获取邀约的装修公司
     * @return [type] [description]
     */
    public function getInviteCompany($id,$pageIndex,$pageCount)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 0 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $map = array(
                "userid"=>array("EQ",$id),
                "zt"=>array("EQ",0)//状态为未入住状态 0 未处理 1 拒绝
                     );
        //1.查询出所有的邀请公司
        $buildSql = M("team")->where($map)->field("comid")->buildSql();
        //2.查询出公司的相关信息
        $buildSql = M("team")->table($buildSql)->alias("t")
                             ->join("INNER JOIN qz_user as a on a.id = t.comid")
                             ->field("a.id,a.qc,a.logo,a.cs,a.on")
                             ->limit($pageIndex,$pageCount)->order("id desc")
                             ->buildSql();
        //3.查询公司的其他信息
        return M("team")->table($buildSql)->alias("t1")
                        ->join("INNER JOIN qz_quyu as b on b.cid = t1.cs")
                        ->order("t1.on desc,t1.id desc")
                        ->field("t1.*,b.bm,b.cname")
                        ->select();
    }
}