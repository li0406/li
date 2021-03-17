<?php

namespace Home\Model;
Use Think\Model;
/**
*
*/
class CompanyMapmarkerModel extends Model
{
    /**
     * 获取城市装修公司
     * @param  [type] $cid [description]
     * @return [type]      [description]
     */
    public function getCityCompanys($cid)
    {
        $map = array(
            "cityid" => array("IN",$cid)
        );

        return M("company_mapmarker")->where($map)->select();
    }

    /**
     * 删除标记
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function delMark($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );

        return M("company_mapmarker")->where($map)->delete();
    }

    /**
     * 添加标注
     */
    public function addMark($data)
    {
        return M("company_mapmarker")->add($data);
    }

    /**
     * 编辑标记
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function editMark($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );

        return M("company_mapmarker")->where($map)->save($data);
    }


    /**
     * 查询标记
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function findMark($lng,$lat)
    {
        $map = array(
            "lng" => array("EQ",$lng),
            "lat" => array("EQ",$lat)
        );

        return M("company_mapmarker")->where($map)->find();
    }
}