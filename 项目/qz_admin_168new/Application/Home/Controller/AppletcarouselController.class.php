<?php

//小程序轮播管理

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class AppletcarouselController extends HomeBaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        $map = [];
        $list = $this->getAppletCarouselList($map);
        $this->assign('lists', $list['list']);
        $this->assign('page', $list['page']);
        $this->display();
    }

    /**
     * 编辑
     */
    public function edit()
    {
        if (IS_POST) {
            //添加数据
            $post = I('post.');
            $id = I("post.id");
            $data = array(
                'name' => trim(str_replace('，', ',', remove_xss(str_replace(',', '，', $post['content'])))),
                'order' => (int)trim($post['order']),
                'img_url' => $post['img'],
                'status' => (int)$post['enable'],
                'url' => trim(remove_xss($post['inputAddr']))
            );
            //编辑/添加
            if ($id != '' && !empty($id)) {
                $data['update_time'] = time();
                $where['id'] = array('eq', $id);
                $statu = D('applet_carousel')->updateAppletCarousel($where, $data);
            } else {
                $data['add_time'] = time();
                $statu = D('applet_carousel')->addAppletCarousel($data);
            }
            //返回信息
            if ($statu) {
                $this->ajaxReturn(array("info" => "", "status" => 1));
            } else {
                $this->ajaxReturn(array("info" => "操作失败!", "status" => 0));
            }
        } else {
            //跳转页面
            $id = I('get.id');
            $this->assign('title', '新增轮播');
            if (!empty($id)) {
                $list = D('applet_carousel')->getDataById($id);
                $list['imgs'] = [0 => "<img style='width:750px;height:292px' src='https://staticqn.qizuang.com/" . $list['img_url'] . "'>"];
                $this->assign('title', '编辑轮播');
                $this->assign('list', $list);
            }
            $this->display();
        }
    }

    public function changeStatu()
    {
        $id = (int)I('post.id');
        if (!empty($id)) {
            $list = D('applet_carousel')->getDataById($id);
            $data = array();
            $where['id'] = array('eq', $id);
            if ($list['status'] == 1) {
                $data['status'] = 0;
            } else {
                $data['status'] = 1;
            }
            $data['update_time'] = time();
            $statu = D('applet_carousel')->updateAppletCarousel($where, $data);
            //返回信息
            if ($statu) {
                $this->ajaxReturn(array("info" => "", "status" => 1));
            } else {
                $this->ajaxReturn(array("info" => "操作失败!", "status" => 0));
            }
        }
    }

    public function delData()
    {
        $id = (int)I('post.id');
        if (!empty($id)) {
            $statu = D('applet_carousel')->delDataById($id);
            //返回信息
            if ($statu) {
                $this->ajaxReturn(array("info" => "", "status" => 1));
            } else {
                $this->ajaxReturn(array("info" => "操作失败!", "status" => 0));
            }
        }
    }

    /**
     * 获取小程序轮播图列表
     */
    private function getAppletCarouselList($map)
    {
        $count = D('applet_carousel')->getAppletCarouselCount();
        import('Library.Org.Util.Page');
        $p = new \Page($count, 10);
        $p->setConfig('header', '个申请');
        $p->setConfig('prev', "上一页");
        $p->setConfig('next', '下一页');
        $p->setConfig('theme', '%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
        $show = $p->show();
        $list = D('applet_carousel')->getList($map);
        if (count($count) > 0) {
            return array("page" => $show, "list" => $list);
        }
    }
}
