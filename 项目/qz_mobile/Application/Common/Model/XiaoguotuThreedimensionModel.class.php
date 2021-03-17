<?php

namespace Common\Model;

use Think\Model;

/**
*   3D效果图
*/
class XiaoguotuThreedimensionModel extends Model
{

    /**
     * 获取3D效果图分类
     * @param  integer $type      分类属性
     * @param  integer $recommend 是否推荐
     * @return array
     */
    public function getThreedimensionCategory($type = 0, $recommend = 0)
    {
        $map['status'] = 1;
        if (!empty($type)) {
            $map['type'] = $type;
        }
        if (!empty($recommend)) {
            $map['recommend'] = $recommend;
        }
        return M('xiaoguotu_threedimension_category')->where($map)->select();
    }

    /**
     * 根据ID获取3D效果图
     * @param  integer $id 效果图ID
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
     * @param  integer $id     效果图ID
     * @param  string  $title  效果图标题
     * @param  integer $fengge 效果图风格
     * @param  integer $huxing 效果图户型
     * @return array
     */
    public function getCount($id = 0, $title = '', $fengge = 0, $huxing = 0)
    {
        $map['x.status'] = 1;
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
        return M('xiaoguotu_threedimension')->alias('x')->where($map)->count();
    }

    /**
     * 获取3D效果图列表
     * @param  integer $id     效果图ID
     * @param  string  $title  效果图标题
     * @param  integer $fengge 效果图风格
     * @param  integer $huxing 效果图户型
     * @param  integer $start  开始位置
     * @param  integer $each   获取数量
     * @return array
     */
    public function getList($id = 0, $title = '', $fengge = 0, $huxing = 0, $start = 0, $each = 10)
    {
        $map['x.status'] = 1;
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

        return M('xiaoguotu_threedimension')->alias('x')
                                            ->field('x.*, group_concat(t.name) AS tagsname, u.name AS adminuser')
                                            ->join('qz_tags AS t ON find_in_set(t.id, x.tags)')
                                            ->join('qz_adminuser AS u ON u.id = x.adminuser_id')
                                            ->where($map)
                                            ->group('x.id')
                                            ->order('x.id DESC')
                                            ->limit($start, $each)
                                            ->select();
    }

    /**
     * 随机获取3D效果图列表
     * @param  integer $id     效果图ID
     * @param  string  $title  效果图标题
     * @param  integer $fengge 效果图风格
     * @param  integer $huxing 效果图户型
     * @param  integer $limit  随机数量
     * @return array
     */
    public function getOtherList($id = 0, $title = '', $fengge = 0, $huxing = 0, $limit=3)
    {
        $map['x.status'] = 1;
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

        return M('xiaoguotu_threedimension')->alias('x')
            ->field('x.*, group_concat(t.name) AS tagsname, u.name AS adminuser')
            ->join('qz_tags AS t ON find_in_set(t.id, x.tags)')
            ->join('qz_adminuser AS u ON u.id = x.adminuser_id')
            ->where($map)
            ->group('x.id')
            ->order('rand()')
            ->limit($limit)
            ->select();
    }

    /**
     * 点赞
     * @param string $value [description]
     */
    public function setLike($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return M('xiaoguotu_threedimension')->where($map)->setInc("likes",1);
    }
}