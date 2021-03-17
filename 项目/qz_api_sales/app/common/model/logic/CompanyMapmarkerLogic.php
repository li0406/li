<?php


namespace app\common\model\logic;


use app\common\model\db\CompanyMapmarker;
use app\common\model\logic\LogAdminLogic;
use think\Db;
use think\Exception;

class CompanyMapmarkerLogic
{
    public function getMapMarkers($ids)
    {
        $model = new CompanyMapmarker();
        if (is_array($ids)) {
            $list = $model->getMapMarkers($ids);
            return $list ? $list->toArray() : [];
        } else {
            return [];
        }
    }


    /**
     * 添加/编辑地图标记信息
     * @param $param
     * @param $user
     * @return int|mixed|string
     * @throws Exception
     */
    public function markAction($param, $user)
    {
        $model = new CompanyMapmarker();

        Db::startTrans();
        try {
            $data = [
                "cityid" => $param["cityid"],
                "lng" => trim($param["lng"]),
                "lat" => trim($param["lat"]),
                "address" => $param["addr"],
                "companies" => $param["com"],
                "last_modified_by" => $user["user_name"],
                "last_modified_time" => time(),
            ];


            if (isset($param["id"]) && $param["id"] > 0) {  // 编辑
                $map = [];
                $map[] = ["id", "=", $param["id"]];
                $model->changeInfo($map, $data);
                $dataId = $param["id"];
            } else {
                $dataId = $model->insertInfo($data);
            }

            Db::commit();
            return $dataId;

        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), $e->getCode());
        }
        return false;

    }

    /**
     * 根据id删除地图标记
     * @param $id
     * @return bool
     * @throws Exception
     */
    public function delMark($id)
    {
        $model = new CompanyMapmarker();

        Db::startTrans();
        try {
            $map = [];
            $map[] = ["id", "=", $id];

            $model->delInfo($map);

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return false;
    }


}