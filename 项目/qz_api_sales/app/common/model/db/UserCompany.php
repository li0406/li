<?php
// +----------------------------------------------------------------------
// | UserCompany 用户会员表
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;

class UserCompany extends Model
{
    protected $table = 'qz_user_company';


    /**
     * 获取真会员数量
     * @param  [type] $userids [description]
     * @return [type]          [description]
     */
    public function getVipCompanyCount($userids)
    {
        return static::alias("c")
            ->join("user u", "u.id = c.userid")
            ->where("u.on", 2)
            ->where("c.fake", 0)
            ->where("u.id", "in", $userids)
            ->field("u.id")
            ->count();
    }

    /**
     * 添加表数据
     * @param $adddata
     * @return int|string
     */
    public function addUserCompanyInfo($adddata)
    {
        return $this->insert($adddata);
    }

    // 获取延期会员信息
    public function getDelayCompanyInfo($user_id){
        $map = new Query();
        $map->where("c.userid", $user_id);

        return $this->alias("c")->where($map)
            ->join("user u", "u.id = c.userid", "inner")
            ->join("user_company_signback s", "s.user_id = u.id", "left")
            ->join("quyu q", "q.cid = u.cs", "left")
            ->field([
                "c.userid as company_id", "u.jc as company_jc", "u.qc as company_qc",
                "u.classid", "c.cooperate_mode", "c.viptype", "s.back_ratio", "u.cs", "q.cname as city_name"
            ])
            ->find();
    }


    public function getAllTwoUserCount($mode = 2){
        $map = [
            ['cooperate_mode', '=',$mode],
            ['fake', '=', 0]
        ];
        return $this->where($map)->count();
    }

    /**
     * @des:获取装修公司会员详情
     * @param $userid
     */
    public function getUserCompanyInfoByUserId($userid)
    {
        $where['userid'] = $userid;
        return $this->where($where)->find();
    }

    // 获取未开启号码保护的公司
    public function getCityPnpOffCompany($city_id){
        $map = new Query();
        $map->where("uc.fake", 0);
        $map->where("uc.pnp_on", 2);
        $map->where("u.classid", "in", [3, 6]);
        $map->where("u.cs", $city_id);

        $list = $this->alias("uc")->where($map)
            ->join("user u", "u.id = uc.userid")
            ->field(["u.id", "u.jc", "u.qc", "uc.pnp_on", "u.tel_safe"])
            ->select();

        return $list;
    }

    // 批量开启/关闭城市公司号码保护
    public function setCityCompanyPnpSwitch($city_id, $pnp_on, $data) {
        $map = new Query();
        $map->where("fake", 0);
        $map->where("pnp_on", $pnp_on);
        $map->where("userid", "in", function($query) use ($city_id) {
            $subMap = new Query();
            $subMap->where("cs", $city_id);
            $subMap->where("classid", "in", [3, 6]);
            $query->table("qz_user")->where($subMap)->field("id");
        });

        return $this->where($map)->update($data);
    }
}
