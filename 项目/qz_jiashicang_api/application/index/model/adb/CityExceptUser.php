<?php
/**
 * @des:城市期望会员数模型
 * @author：tengyu
 * date:2020-11-13
 */

namespace app\index\model\adb;

use app\common\model\adb\BaseModel;
use think\Db;
use think\db\Query;

class CityExceptUser extends BaseModel
{

    /**
     * @des:获取城市期望会员数
     * @param array $param
     * @return mixed
     */
    public function getCityExceptUser($param = [])
    {
        $map = new Query();
        if (isset($param['cs']) && !empty($param['cs']) && is_array($param['cs'])) {
            $map->where('cs', 'in', $param['cs']);
        }
        $child = $this->link()->table('qz_city_except_user')
            ->where($map)
            ->order(["datetime" => "desc"])
            ->field(["except_user", "cs"])
            ->buildSql();
        $sql = $this->link()->table($child)->alias('t')
            ->field("except_user")
            ->group("cs")
            ->buildSql();
        return $this->link()->table($sql)->alias('basic')
            ->sum("except_user");
    }

    // 获取城市期望会员数
    public function getCityExceptUserCount($date, $city_ids = []){
        $map = new Query();
        $map->where("datetime", "<=", strtotime($date));

        if (!empty($city_ids)) {
            $map->where("cs", "in", $city_ids);
        }

        $subSql = $this->link()->name("city_except_user")->where($map)
            ->field(["cs as city_id", "except_user"])
            ->order("datetime desc")
            ->buildSql();

        return $this->link()->table($subSql)->alias("t")
            ->field(["t.city_id", "t.except_user"])
            ->group("t.city_id")
            ->select();
    }
}