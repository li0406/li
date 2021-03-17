<?php

namespace Home\Model;

use Think\Model;

class WeixinMenuModel extends Model{

    /**
     * 新增微信菜单
     * @param array $save 内容
     */
    public function addWeixinMenu($save = array())
    {
        if (empty($save)) {
            return false;
        }
        return M('weixin_menu')->add($save);
    }

    /**
     * 编辑微信菜单
     * @param  [type] $id   菜单ID
     * @param  array  $save 内容
     * @return bool
     */
    public function editWeixinMenu($id = 0, $save = array())
    {
        if (empty($id) || empty($save)) {
            return false;
        }
        $map = array(
            'id' => $id
        );
        return M('weixin_menu')->where($map)->save($save);
    }

    /**
     * 根据父级菜单ID编辑菜单
     * @param  integer $parent 父级菜单ID
     * @param  array   $save   编辑内容
     * @return bool
     */
    public function editWeixinMenuByParent($parent = 0, $save = array()){
        if (empty($parent) || empty($save)) {
            return false;
        }
        $map = array(
            'parent' => $parent
        );
        return M('weixin_menu')->where($map)->save($save);
    }

    /**
     * 根据ID获取微信菜单
     * @param  integer $id 菜单ID
     * @return array
     */
    public function getWeixinMenuById($id = 0){
        if (empty($id)) {
            return false;
        }
        $map = array(
            'id' => $id
        );
        return M('weixin_menu')->where($map)->find();
    }


    /**
     * 获取微信专享菜单
     * @param  integer $category  分类
     * @param  integer $recommend 是否推荐
     * @param  integer $start     开始位置
     * @param  integer $each      获取数量
     * @return array
     */
    public function getWeixinMenuList($app = '', $parent = false)
    {
        $map['status'] = 1;
        if (empty($app)) {
            return false;
        }
        $map['app'] = array('EQ', $app);
        if ($parent !== false) {
            $map['parent'] = intval($parent);
        }
        return M('weixin_menu')->where($map)->order('sort ASC, id ASC')->select();
    }
}