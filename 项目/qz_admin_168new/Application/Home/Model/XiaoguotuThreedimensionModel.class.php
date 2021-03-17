<?php

namespace Home\Model;

use Common\Enums\ApiConfig;
use Think\Exception;
use Think\Model;

/**
 *   3D效果图
 */
class XiaoguotuThreedimensionModel extends Model
{
    /**
     * 新增3D效果图
     * @param array $save 存储数据
     * @return bool
     */
    public function insertThreedimension($save = array())
    {
        if (empty($save)) {
            return false;
        }
        return M('xiaoguotu_threedimension')->add($save);
    }

    /**
     * 编辑3D效果图
     * @param integer $id 3D效果图
     * @param array $save 存储数据
     * @return bool
     */
    public function updateThreedimension($id = 0, $save = array())
    {
        if (empty($id)) {
            return false;
        }
        return M('xiaoguotu_threedimension')->where(array('id' => $id))->save($save);
    }

    /**
     * 获取3D效果图
     * @param integer $id 3D效果图ID
     * @return array
     */
    public function getThreedimensionById($id = 0)
    {
        if (empty($id)) {
            return false;
        }
        $map = array(
            'x.id' => $id
        );
        return M('xiaoguotu_threedimension')->alias('x')
            ->field('x.*, group_concat(t.name) AS tagname')
            ->join('qz_tags AS t ON find_in_set(t.id, x.tags)')
            ->where($map)
            ->group('x.id')
            ->find();
    }

    /**
     * 获取3D效果图数量
     * @param integer $id 效果图ID
     * @param string $title 效果图标题
     * @param integer $fengge 风格
     * @param integer $huxing 户型
     * @param integer $adminuser_id 操作人
     * @return array
     */
    public function getCount($id = 0, $title = '', $fengge = 0, $huxing = 0, $adminuser_id = 0)
    {
        $map['x.status'] = array('NEQ', 2);
        if (!empty($id)) {
            $map['x.id'] = intval($id);
        }
        if (!empty($title)) {
            $map['x.title'] = array('LIKE', '%' . $title . '%');
        }
        if (!empty($fengge)) {
            $map['x.fengge'] = intval($fengge);
        }
        if (!empty($huxing)) {
            $map['x.huxing'] = intval($huxing);
        }
        if (!empty($adminuser_id)) {
            $map['x.adminuser_id'] = intval($adminuser_id);
        }
        return M('xiaoguotu_threedimension')->alias('x')->where($map)->count();
    }

    /**
     * 获取3D效果图数量
     * @param integer $id 效果图ID
     * @param string $title 效果图标题
     * @param integer $fengge 风格
     * @param integer $huxing 户型
     * @param integer $adminuser_id 操作人
     * @param integer $start 开始位置
     * @param integer $each 获取数量
     * @return array
     */
    public function getList($id = 0, $title = '', $fengge = 0, $huxing = 0, $adminuser_id = 0, $start = 0, $each = 10)
    {
        $map['x.status'] = array('NEQ', 2);
        if (!empty($id)) {
            $map['x.id'] = intval($id);
        }
        if (!empty($title)) {
            $map['x.title'] = array('LIKE', '%' . $title . '%');
        }
        if (!empty($fengge)) {
            $map['x.fengge'] = intval($fengge);
        }
        if (!empty($huxing)) {
            $map['x.huxing'] = intval($huxing);
        }
        if (!empty($adminuser_id)) {
            $map['x.adminuser_id'] = intval($adminuser_id);
        }

        return M('xiaoguotu_threedimension')->alias('x')
            ->field('x.*, group_concat(t.name) AS tagsname,u.name AS adminuser,u2.name as last_username')
            ->join('qz_tags AS t ON find_in_set(t.id, x.tags)')
            ->join('qz_adminuser AS u ON u.id = x.adminuser_id')
            ->join('LEFT JOIN qz_adminuser AS u2 ON u2.id = x.update_uid')
            ->where($map)
            ->group('x.id')
            ->limit($start, $each)
            ->order("x.id desc")
            ->select();
    }

    /**
     * 新增3D效果图图片
     * @param array $save 图片数据
     * @return bool
     */
    public function insertThreedimensionImg($save = array())
    {
        if (empty($save)) {
            return false;
        }
        return M('xiaoguotu_threedimension_img')->addAll($save);
    }

    /**
     * 根据图片ID删除3D效果图图片
     * @param integer $id 图片ID
     * @return bool
     */
    public function deleteThreedimensionImgById($id = 0)
    {
        if (empty($id)) {
            return false;
        }
        $map = array(
            'id' => $id
        );
        return M('xiaoguotu_threedimension_img')->where($map)->delete();
    }

    /**
     * 删除案例的效果图
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function deleteThreedimensionImg($id)
    {
        $map = array(
            'xiaoguotu_threedimension_id' => $id
        );
        return M('xiaoguotu_threedimension_img')->where($map)->delete();
    }

    /**
     * 根据3D效果图ID获取相关图片
     * @param integer $xiaoguotu_threedimension_id 3D效果图ID
     * @return array
     */
    public function getThreedimensionImgByXgtId($xiaoguotu_threedimension_id = 0)
    {
        if (empty($xiaoguotu_threedimension_id)) {
            return false;
        }
        $map = array(
            'xiaoguotu_threedimension_id' => $xiaoguotu_threedimension_id
        );
        return M('xiaoguotu_threedimension_img')->where($map)->order('sort ASC')->select();
    }

    /**
     * 批量更新数据
     * @param $ids
     * @param array $data
     * @return bool
     */
    public function multiPublish($ids, array $data)
    {
        $map['id'] = ['in', $ids];
        return $this->where($map)->data($data)->save();
    }

    public function getMulti($ids)
    {
        $map['id'] = ['in', $ids];
        return $this->where($map)->select();
    }

    /**
     * 重新存储图片
     * @param $newId
     * @param $oldId
     * @return bool
     */
    public function restore($newId, $oldId)
    {
        $map = [
            'xiaoguotu_threedimension_id' => $oldId
        ];
        $data = [
            'xiaoguotu_threedimension_id' => $newId
        ];
        return M('xiaoguotu_threedimension_img')->where($map)->save($data);
    }

    public function drop($id)
    {
        $map = [
            'id' => $id
        ];
        return $this->where($map)->delete();
    }

    public function hasValue($id)
    {
        $map = [
            'id' => $id
        ];
        return $this->where($map)->getField('id');
    }

}