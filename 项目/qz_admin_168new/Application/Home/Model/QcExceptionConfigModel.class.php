<?php
namespace Home\Model;
Use Think\Model;

class QcExceptionConfigModel extends Model
{
    /**
     * 获取设置项信息
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function getExcetpionConfig($type) {
        if (!empty($type)) {
            $map = array(
                "config_type" => array("EQ",$type)
            );
            return M("qc_exception_config")->where($map)->select();
        }
    }

    /**
     * 删除
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function delExceptionConfig($type,$ids)
    {
        $map = array(
            "config_type" => array("EQ",$type)
        );

        if (is_array($ids) && count($ids) > 0) {
            $map["id"] = array("IN",$ids);
        }
        return M("qc_exception_config")->where($map)->delete();
    }

    /**
     * 添加
     * @param [type] $data [description]
     */
    public function addExceptionConfig($data)
    {
        return M("qc_exception_config")->addAll($data);
    }

    public function updateExceptionConfig($id,$data)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M("qc_exception_config")->where($map)->save($data);
    }
}