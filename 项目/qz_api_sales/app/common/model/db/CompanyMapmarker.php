<?php


namespace app\common\model\db;


use think\Model;

class CompanyMapmarker extends Model
{
    protected $table = "qz_company_mapmarker";

    public function getMapMarkers($ids)
    {
        $map = [];
        $map[] = ["cityid", "in", $ids];

        return $this->where($map)
            ->field("id, lng, lat, address, last_modified_by, companies")
            ->order("id desc")
            ->select();

    }

    /**
     * 添加新内容
     * @param $savedata
     * @return int|string
     */
    public function insertInfo($savedata)
    {
        return $this->insertGetId($savedata);
    }

    /**
     * 编辑内容
     * @param $map
     * @param $changeInfo
     * @return bool|int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function changeInfo($map, $changeInfo)
    {
        if (empty($map)) {
            return false;
        }
        return $this->where($map)->update($changeInfo);
    }


    /**
     * 根据条件删除数据
     * @param $map
     */
    public function delInfo($map)
    {
        return $this->where($map)->delete();
    }

}