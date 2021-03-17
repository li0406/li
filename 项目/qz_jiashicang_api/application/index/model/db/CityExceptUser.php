<?php
/**
 * @des:城市期望会员数模型
 * @des:注释：每个城市每月只会有一条预期会员数
 * @author:tengyu
 * @date:2020-11-16
 */

namespace app\index\model\db;


use think\Db;
use think\db\Query;
use think\Exception;
use think\Model;

class CityExceptUser extends Model
{
    /**
     * @des:执行插入期望城市会员数操作
     * @param $param
     * @return mixed
     */
    public function batchInsertCityExcept($param)
    {
        return Db::table('qz_city_except_user')->insertAll($param,false,1000);
    }

    /**
     * @des:更新期望会员数据
     * @param $param
     * @return bool
     * @throws Exception
     */
    public function batchUpdateCityExcept($param)
    {
        try {
            foreach ($param as $value) {
                $id = $value['id'];
                unset($value['id']);
                $update = $value;
                $res = Db::table('qz_city_except_user')->where(['id' => $id])->update($update);
            }
            return true;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * @des:获取当月最新的期望会员数
     * @param $start_time
     * @param $end_time
     * @param array $param
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCityExceptUserInfoNew($start_time, $end_time, $param = [])
    {
        $map = new Query();
        $map->where('datetime', 'between', [$start_time, $end_time]);
        if (isset($param['cs']) && !empty($param['cs']) && is_array($param['cs'])) {
            $map->where('cs', 'in', $param['cs']);
        }
        return  Db::table('qz_city_except_user')->where($map)
            ->field(['cs,id,cname,except_user,datetime'])
            ->order(["datetime" => "desc"])
            ->group("cs")
            ->select();
    }
}