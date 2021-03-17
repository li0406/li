<?php
// +----------------------------------------------------------------------
// | QuyuLogic
// +----------------------------------------------------------------------
namespace app\common\model\logic;

use think\Db;
use think\Collection;
use app\common\model\db\Area;
use app\common\model\db\Quyu;
use app\common\model\db\User;
use app\common\model\db\SalesQuyu;
use app\common\model\db\AdminAuditCitys;

use app\common\enum\BaseStatusCodeEnum;
use app\common\enum\CacheKeyCodeEnum;

class QuyuLogic
{
    /**
     * 获取所有城市信息
     *
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getAllCitys($cid = '')
    {
        $quyuModel = new Quyu();
        $map = [];
        if ($cid) {
            if (is_array($cid)) {
                $map['cid'] = array("in", $cid);
            } else {
                $map['cid'] = $cid;
            }
        }

        $list = $quyuModel->getList($map,[], null, null, 'cid,cname,px_abc');
        return $list;
    }

    /**
     * 根据城市ID获取下属地区
     *
     * @param $cid
     * @param $area
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getArea($cid)
    {
        if (empty($cid)) {
            return [];
        }
        $areaModel = new Area();
        $map['fatherid'] = $cid;
        $list = $areaModel->getList($map);
        return $list;
    }

    /**
     * 根据城市ID获取下属地区
     *
     * @param $cid
     * @param $area
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getQuyuTrue($cname,$aname)
    {
        $areaModel = new Area();
        $quyuModel = new Quyu();
        $mapQuyu[] = ['cname','eq',$cname];
        $mapArea[] = ['qz_area','eq',$aname];

        $listquyu = $quyuModel->getInfoByName($mapQuyu);
        $listarea = $areaModel->getInfoByName($mapArea);

        if(!$listquyu){
            return  ['error_code' => 4000004, 'error_msg' => BaseStatusCodeEnum::CODE_4000004];
        }
        if(!$listarea){
            return  ['error_code' => 4000004, 'error_msg' => BaseStatusCodeEnum::CODE_4000004];
        }
        if($listarea['fatherid'] != $listquyu['cid']){
            return  ['error_code' => 4000005, 'error_msg' => BaseStatusCodeEnum::CODE_4000005];
        }
        return ['error_code' => 0, 'data' => $listarea];
    }

    /**
     * 获取所有城市信息
     *
     * @return array|\PDOStatement|string|\think\Collection
     */
    public function getAllCitysByCids($cids)
    {
        $cache_key = sprintf(CacheKeyCodeEnum::CITY_PART_CIDS, md5(serialize($cids)));
        $list = cache($cache_key);
        if (empty($list)) {
            $list = [];
            if (!empty($cids)) {
                $quyuModel = new Quyu();
                $map = ['cid' => ['in', $cids]];
                $list = $quyuModel->getList($map,[], null, null, 'cid,cname,px_abc');
            }

            cache($cache_key, $list);
        }

        return $list;
    }

    /**
     * 获取无会员城市列表
     *
     * @return mixed
     */
    public function noVipCitys($cid = '')
    {
        $map = [];
        if ($cid) {
            $map['cid'] = $cid;
        }
        $cityModel = new Quyu();
        return $cityModel->getNoVipCitys($map);
    }

    /**
     * 获取无会员城市列表
     *
     * @param $page
     * @param int $pageSize
     * @return array
     */
    public function getNoVip($param,$down,$p,$psize)
    {
        $quyuMap = [];
        if (!empty($param['cs'])) {
            $quyuMap['q.cid'] = $param['cs'];
        }
        if (isset($param['isopen']) && $param['isopen'] !== '' && $param['isopen'] !== null) {
            $quyuMap['q.is_open_city'] = $param['isopen'];
        }

        if (!empty($param['start']) && !empty($param['end'])) {
            if (strtotime($param['start']) < strtotime('-6 month', strtotime($param['end']))) {
                return sys_response(4000002, '请选择六个月以内时间段',[]);
            }
            $orderMap['time_real'] = ['between', [strtotime($param['start']), strtotime($param['end'].' 23:59:59')]];
        } elseif (!empty($param['start'])) {
            $orderMap['time_real'] = ['between', [strtotime($param['start']), time()]];
        } elseif (!empty($param['end'])) {
            $orderMap['time_real'] = ['between', [strtotime('-6 month'), strtotime($param['end'].' 23:59:59')]];
        } else {
            $orderMap['time_real'] = ['between', [strtotime('-6 month'), time()]];
        }

        $orderby = 'fa desc,fengandzeng desc,px_abc asc';
        if (!empty($param['order'])) {
            $orderby = $param['order'];
        }

        // 获取无会员城市
        $cityModel = new Quyu();
        $allNoneVipCitys = $cityModel->getNoVipList($quyuMap, $orderMap, $orderby);

        if ($down) {
            return sys_response(0, '', [
                'list' => $this->getRelationCity($allNoneVipCitys),
            ]);
        } else {
            $list = array_slice($allNoneVipCitys, ($p - 1) * $psize, $psize);
            $list = $this->getRelationCity($list);
            return sys_response(0, '', [
                'list' => $list,
                'timestamp' => $orderMap['time_real'][1],
                'page' => sys_paginate(count($allNoneVipCitys), $psize, $p),
            ]);
        }
    }

    /**
     * 获取相邻城市列表,并绑定到原城市列表上
     *
     * @param $cityslist
     * @return mixed
     */
    public function getRelationCity($cityslist)
    {
        $city1 = array_column($cityslist, 'parent_city');
        $city2 = array_column($cityslist, 'parent_city1');
        $city3 = array_column($cityslist, 'parent_city2');
        $city4 = array_column($cityslist, 'parent_city3');
        $city5 = array_column($cityslist, 'parent_city4');
        $city6 = array_column($cityslist, 'other_city');

        $relacids = array_filter(array_unique(array_merge($city1, $city2, $city3, $city4, $city5, $city6)));
        if ($relacids) {
            $relacitys = $this->getAllCitysByCids($relacids);
            foreach ($cityslist as $k => $v) {
                $cityslist[$k]['parent_city_list'] = [];
                array_walk($cityslist[$k], function (&$val) {
                    $val = is_null($val) ? '' : $val;
                });
                foreach ($relacitys as $v2) {
                    if (!empty($v['parent_city']) && $v['parent_city'] === $v2['cid']) {
                        $cityslist[$k]['parent_city_list'][$v2['cid']] = $v2;
                    }
                    if (!empty($v['parent_city1']) && $v['parent_city1'] === $v2['cid']) {
                        $cityslist[$k]['parent_city_list'][$v2['cid']] = $v2;
                    }
                    if (!empty($v['parent_city2']) && $v['parent_city2'] === $v2['cid']) {
                        $cityslist[$k]['parent_city_list'][$v2['cid']] = $v2;
                    }
                    if (!empty($v['parent_city3']) && $v['parent_city3'] === $v2['cid']) {
                        $cityslist[$k]['parent_city_list'][$v2['cid']] = $v2;
                    }
                    if (!empty($v['parent_city4']) && $v['parent_city4'] === $v2['cid']) {
                        $cityslist[$k]['parent_city_list'][$v2['cid']] = $v2;
                    }
                    if (!empty($v['other_city']) && $v['other_city'] === $v2['cid']) {
                        $cityslist[$k]['parent_city_list'][$v2['cid']] = $v2;
                    }
                }
            }
        }

        return $cityslist;
    }


    /**
     * 获取城市会员数量
     * @return [type] [description]
     */
    public function getCityUserCount(){
        $cache_key = CacheKeyCodeEnum::CITY_USER_COUNT;
        $cityusers = cache($cache_key);
        if (empty($cityusers)) {
            $userModel = new User();
            $cityusers = $userModel->getCityUserCount();
            $cityusers = array_column($cityusers->toArray(), "user_count", "cs");
            cache($cache_key, $cityusers, 900);
        }

        return $cityusers;
    }

    /**
     * 获取销售分配城市页面数据
     * 方案一：左右两边数据分开返回
     * @return [type] [description]
     */
    public function getSalecityInfo($to = 'jihebu'){
        $cityusers = $this->getCityUserCount();

        $mapFunction = function($item) use ($cityusers) {
            $item["user_count"] = 0;
            if (array_key_exists($item["cid"], $cityusers)) {
                $item["user_count"] = $cityusers[$item["cid"]];
            }

            $item["first_abc"] = strtoupper(substr($item["px_abc"], 0, 1));
            $item["value"] = $item["first_abc"] . " " . $item["cname"];
            unset($item["px_abc"]);
            return $item;
        };

        // 已分配城市
        $salecitys = SalesQuyu::getRelationAll($to);
        $salecitys = array_map($mapFunction, $salecitys->toArray());

        // 未分配城市
        $cityList = SalesQuyu::getNoRelationAll($to);
        $cityList = array_map($mapFunction, $cityList->toArray());

        return ["sale_citys" => $salecitys, "city_list" => $cityList];
    }

    /**
     * 获取销售分配城市页面数据
     * 方案二：只返回一个列表
     * @param  string $to [description]
     * @return [type]     [description]
     */
    public function getSalecityAll($to = 'jihebu'){
        $cityusers = $this->getCityUserCount();

        $mapFunction = function($item) use ($cityusers) {
            $item["user_count"] = 0;
            if (array_key_exists($item["cid"], $cityusers)) {
                $item["user_count"] = $cityusers[$item["cid"]];
            }

            $item["first_abc"] = strtoupper(substr($item["px_abc"], 0, 1));
            $item["value"] = $item["first_abc"] . " " . $item["cname"];
            unset($item["px_abc"]);
            return $item;
        };

        $cityList = SalesQuyu::getCityAll($to);
        $cityList = array_map($mapFunction, $cityList->toArray());
        return ["city_all" => $cityList];
    }

    /**
     * 设置销售分配城市
     * @param [type] $sale_citys [description]
     */
    public function setRelationSaleCitys($sale_citys, $to = "jihebu"){
        $salecityIds = array_filter(array_unique(explode(",", $sale_citys)));

        Db::startTrans();
        try {
            // 根据ID获取城市
            $cityList = $this->getAllCitys($salecityIds);
            $cityIds = array_column($cityList->toArray(), "cid");

            // 获取已关联的城市
            $relationCitys = SalesQuyu::getRelationAll($to);
            $relationIds = array_column($relationCitys->toArray(), "cid");

            // 比较差异 
            $newIds = array_diff($cityIds, $relationIds); // 获取新增的城市
            $delIds = array_diff($relationIds, $cityIds); // 获取删除的城市

            // 删除
            if (!empty($delIds)) {
                $ret = SalesQuyu::delRecords($to, $delIds);
                if (empty($ret)) {
                    throw new \Exception("保存失败");
                }
            }

            // 新增
            if (!empty($newIds)) {
                $rows = array_map(function($val) use ($to) {
                    return array(
                        "to" => $to,
                        "cs" => $val,
                        "created_at" => time()
                    );
                }, array_values($newIds));

                $ret = SalesQuyu::addRecords($rows);
                if (empty($ret)) {
                    throw new \Exception("保存失败");
                }

                // 同步删除质检人员和质检城市关联数据
                $ret = AdminAuditCitys::deleteByCids($newIds);
            }


            Db::commit();
            $result = true;
        } catch (\Exception $e) {
            Db::rollback();
            $result = $e->getMessage();
        }

        return $result;
    }

    /**
     * 获取质检操作城市
     * @return [type] [description]
     */
    public static function getAuditCitys(){
        $cache_key = CacheKeyCodeEnum::CITY_AUDIT_CITYS;
        $cityIds = cache($cache_key);
        if (empty($cityIds)) {
            $quyuModel = new Quyu();
            $cityList = $quyuModel->getAuditCitys();
            $cityIds = array_column($cityList->toArray(), "cid");
            cache($cache_key, $cityIds);
        }

        return $cityIds;
    }
}