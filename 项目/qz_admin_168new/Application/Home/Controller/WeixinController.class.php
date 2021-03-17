<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

/**
*   微信类
*/

class WeixinController extends HomeBaseController
{
    /**
     * 菜单列表
     */
    public function menu()
    {
        //获取菜单配置
        $result = D('WeixinMenu')->getWeixinMenuList('ZGZX');
        $menu = array();
        foreach ($result as $key => $value) {
            //释放存储为json的菜单配置
            $value['info'] = json_decode($value['info'], true);
            //生成带有ID的菜单树
            if ($value['parent'] == 0) {
                $menu[$value['id']] = $value;
            } else {
                $menu[$value['parent']]['children'][$value['id']] = $value;
            }
        }
        $vars['menu'] = $menu;
        $this->assign('vars', $vars);
        $this->display();
    }

    /**
     * 新增编辑菜单
     */
    public function operateMenu()
    {
        if (IS_POST) {
            $id = I('post.id');
            $type = I('post.type');
            $name = I('post.name');
            $parent = I('post.parent');

            //存储数据
            $save = array(
                'app' => 'ZGZX',
                'parent' => I('post.parent'),
                'status' => 1,
                'sort' => I('post.sort'),
                'update_time' => date('Y-m-d H:i:s')
            );
            if (empty($name)) {
                $this->ajaxReturn(array('status' => 0, 'info' => '菜单名称必须填写'));
            }
            //如果是二级菜单，需判断该菜单的父级菜单的类型是否是请选择(即info字段属性只有name的值)
            if ($parent != '0') {
                $temp = json_decode(D('WeixinMenu')->getWeixinMenuById($parent)['info'], true);
                if (count($temp) > 1) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '父级菜单（' . $temp['name'] . '）的类型必须为"请选择"'
                    ));
                }
            }
            //如果是主菜单，父级菜单必须选择主菜单
            if ($type == '0') {
                if ($parent != '0') {
                    $this->ajaxReturn(array('status' => 0, 'info' => '类型为请选择时，父级菜单必须为主菜单'));
                }
                $info = array(
                    'name' => I('post.name')
                );
            }
            //添加跳转URL
            if ($type == 'view') {
                $info = array(
                    'type' => 'view',
                    'name' => I('post.name'),
                    'url'  => I('post.view_url')
                );
            }
            //添加小程序
            if ($type == 'miniprogram') {
                $info = array(
                    'type'     => 'miniprogram',
                    'name'     => I('post.name'),
                    'url'      => I('post.miniprogram_url'),
                    'appid'    => I('post.miniprogram_appid'),
                    'pagepath' => I('post.miniprogram_pagepath')
                );
            }
            $save['info'] = json_encode($info, JSON_UNESCAPED_UNICODE);
            if (empty($id)) {
                $save['add_time'] = date('Y-m-d H:i:s');
                $result = D('WeixinMenu')->addWeixinMenu($save);
            } else {
                $result = D('WeixinMenu')->editWeixinMenu($id, $save);
            }
            if ($result) {
                $this->ajaxReturn(array('status' => 1));
            } else {
                $this->ajaxReturn(array('status' => 0, 'info' => '操作失败'));
            }
        }
        $id = I('get.id');
        $vars['info'] = D('WeixinMenu')->getWeixinMenuById($id);
        $vars['info']['info'] = json_decode($vars['info']['info'], true);
        $vars['parent'] = D('WeixinMenu')->getWeixinMenuList('ZGZX', '0');
        $this->assign('vars', $vars);
        $data = $this->fetch();
        $this->ajaxReturn(array('status' => 1, 'data' => $data));
    }

    /**
     * 删除微信菜单
     */
    public function deleteMenu()
    {
        $id = I('post.id');
        if (empty($id)) {
            $this->ajaxReturn(array('status' => 0, 'info' => '待删除菜单ID为空'));
        }
        $save = array(
            'status' => 2,
            'update_time' => date('Y-m-d H:i:s')
        );
        $result = D('WeixinMenu')->editWeixinMenu($id, $save);
        if ($result) {
            D('WeixinMenu')->editWeixinMenuByParent($id, $save);
            $this->ajaxReturn(array('status' => 1, 'info' => '删除成功！'));
        }
        $this->ajaxReturn(array('status' => 0, 'info' => '操作失败'));
    }

    /**
     * 发布微信菜单
     * @return [type] [description]
     */
    public function publishMenu()
    {
        //获取菜单配置
        $result = D('WeixinMenu')->getWeixinMenuList('ZGZX');
        $temp = array();
        foreach ($result as $key => $value) {
            //释放存储为json的菜单配置
            $value['info'] = json_decode($value['info'], true);
            //生成带有ID的菜单树
            if ($value['parent'] == 0) {
                $temp[$value['id']] = $value;
            } else {
                $temp[$value['parent']]['children'][$value['id']] = $value;
            }
        }

        //一级菜单不能超过三个
        if (count($temp) > 3) {
            $this->ajaxReturn(array('status' => 0, 'info' => '一级菜单数量不能超过三个'));
        }

        //生成菜单按钮
        $button = array();
        foreach ($temp as $key => $value) {
            if (!empty($value['children'])) {
                if (count($value['children']) > 5) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '二级菜单数量不能超过五个'));
                }
                foreach ($value['children'] as $k => $v) {
                    $value['info']['sub_button'][] = $v['info'];
                }
            } else {
                if (count($value['info']) == 1) {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'info' => '发布失败：主菜单（' . $value['info']['name'] . '）没有子菜单'
                    ));
                }
            }
            $button[] = $value['info'];
        }
        $menu['button'] = $button;
        import('Library.Org.Util.Weixin');
        $weixin = new \Weixin('ZGZX');
        $result = json_decode($weixin->setMenu($menu), true);
        if ($result['errcode'] == 0) {
            $this->ajaxReturn(array('status' => 1, 'info' => '发布成功！'));
        }
        $this->ajaxReturn(array('status' => 0, 'info' => '发布失败：' . $result['errmsg']));
    }

}