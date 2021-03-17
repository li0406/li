<?php
/**
 * v2美图,分类
 */

namespace Home\Model\Logic;


use Home\Model\Db\MeituV2CategoryModel;
use Home\Model\Db\MeituV2ImageModel;
use Home\Model\Db\MeituV2Model;
use Think\Db;
use Think\Exception;

class MeituV2ImageLogicModel
{
    protected $meituV2ImageModel;

    public function __construct()
    {
        $this->meituV2ImageModel = new MeituV2ImageModel();
    }

    /**
     * 清除某个美图下的所有图片
     * @param $id
     * @return mixed
     */
    public function drop($id)
    {
        $map['meitu_id'] = $id;
        $ret = $this->meituV2ImageModel->where($map)->delete();
        return $ret;
    }

    /**
     * 保存图片数据
     * @param $id
     * @param $src
     * @param $title
     * @param $width
     * @param $height
     * @return mixed
     */
    public function save($id, $src, $title, $width, $height)
    {
        $data = [
            'meitu_id' => (int)$id,
            'src' => $src,
            'title' => $title,
            'width' => (int)$width ?: 0,
            'height' => (int)$height ?: 0,
            'updated_at' => time(),
            'created_at' => time(),
        ];
        return $this->meituV2ImageModel->data($data)->add();
    }

    /**
     * 更新美图图片
     * @param $id
     * @param $src
     * @param $title
     * @param $weight
     * @param $height
     * @return bool
     */
    public function update($id, $src, $title, $weight, $height)
    {
        $data = [
            'src' => $src,
            'title' => $title,
            'weight' => $weight,
            'height' => $height,
            'updated_at' => time(),
        ];
        return $this->meituV2ImageModel->where("meitu_id=$id")->save($data);
    }

    /**
     * 重新存储
     * @param $newId
     * @param $oldId
     * @return bool
     */
    public function restore($newId, $oldId)
    {
        $data = [
            'meitu_id'=>$newId,
        ];
        return $this->meituV2ImageModel->where("meitu_id=$oldId")->save($data);
    }
}