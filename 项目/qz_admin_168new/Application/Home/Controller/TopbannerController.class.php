<?php

//顶部广告

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class TopbannerController extends HomeBaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }

    //首页
    public function index()
    {
        //分页
        $pageIndex = 1;
        $pageCount = 16;
        if (!empty($_GET["p"])) {
            $pageIndex = $_GET["p"];
        }
        //将城市排序
        $citys = getUserCitys();
        $this->assign("citys", $citys);
        //城市
        $cityId = I('get.city_id');
        if (!empty($cityId) || $cityId == '0') {
            $condition['a.city_id'] = array("EQ", I('get.city_id'));
        }
        //搜索
        if (!empty($_GET["title"])) {
            $condition['a.title'] = array('like', '%' . I('get.title') . '%');
        }
        $condition['a.module'] = 'home_topbanner';
        $condition['orderBy']  = 'a.status desc,start_time desc,op_time desc';
        $result = $this->getList($condition, $pageIndex, $pageCount);
        //计算剩余天数
        $time = strtotime(date("Y-m-d"));
        foreach ($result['list'] as $key => $value) {
            $remainder = $value['end_time'] - $time;
            $result['list'][$key]['date'] = 0;
            if ($remainder > 0) {
                $result['list'][$key]['date'] = ceil($remainder / 86400);
            }
        }
        $this->assign("list", $result['list']);
        $this->assign('page', $result['page']);
        $this->display();
    }

    public function edit()
    {
        if (IS_POST) {
            $Db = D('Advbanner');
            $post = I('post.');
            $data['title'] = $post['title'];
            $data['status'] = $post['status'];
            $data['company_id'] = '';
            $data['company_name'] = '';
            $data['city_id'] = $post['city_name'];
            $data['img_host'] = 'qiniu';
            $data['img_url'] = $post['img_url'];
            $data['url'] = $post['url'];
            $data['img_url_mobile'] = '';
            $data['url_mobile'] = '';
            $data['description'] = $post['description'];
            $data['sort'] = 0;
            $data['module'] = 'home_topbanner';
            $data['start_time'] = strtotime($post['start_time']);
            $data['end_time'] = strtotime($post['end_time']);
            $data['op_time'] = time();
            $data['op_uid'] = session('uc_userinfo.id');
            $data['op_uname'] = session('uc_userinfo.name');
            //编辑
            if ($post['edit_id']) {
                if ($Db->editBanner($post['edit_id'], $data)) {
                    $log = array(
                        'remark' => '编辑顶部广告',
                        'logtype' => 'home_topbanner',
                        'action_id' => $post['edit_id'],
                        'info' => $data
                    );
                    D('LogAdmin')->addLog($log);
                    $this->ajaxReturn(['status' => 1, 'info' => '']);
                } else {
                    $this->ajaxReturn(['status' => 0, 'info' => '修改顶部广告失败']);
                }
            } else {
                $result = $Db->addBanner($data);
                if ($result) {
                    $log = array(
                        'remark' => '增加顶部广告',
                        'logtype' => 'home_topbanner',
                        'action_id' => $result,
                        'info' => $data
                    );
                    D('LogAdmin')->addLog($log);
                    $this->ajaxReturn(['status' => 1, 'info' => '']);
                } else {
                    $this->ajaxReturn(['status' => 0, 'info' => '修改顶部广告失败']);
                }
            }
        }
        //区分编辑 / 新增
        $id = $_GET['id'];
        if ($id) {
            if (empty($id) || !is_numeric($id)) {
                $this->_error('不是正确的轮播 :(');
            }
            $Db = D('Advbanner');
            $advBanner = $Db->getBannerById($id, 'home_topbanner');
            if (empty($advBanner)) {
                $this->_error('不是正确的轮播 :(');
            }
            $advBanner['company'] = empty($advBanner['company_id']) ? '' : $advBanner['company_id'] . '|' . $advBanner['company_name'];
            $this->assign("info", $advBanner);
        }
        $citys = getUserCitys();
        $this->assign("citys", $citys);
        $this->display();
    }

    //更改状态
    public function status()
    {
        $id = $_GET['id'];
        if (empty($id) || !is_numeric($id)) {
            $this->_error('数据错误！');
        }
        $Db = D('Advbanner');
        $advBanner = $Db->getBannerById($id);
        if (empty($advBanner)) {
            $this->_error('不是正确的轮播 :(');
        }

        if ($advBanner['status'] == '1') {
            $type = '0';
            $action = '停用';
        } else {
            $type = '1';
            $action = '启用';
        }
        if ($Db->setStatus($id, $type)) {
            $log = array(
                'remark' => $action . '顶部广告',
                'logtype' => 'home_topbanner',
                'action_id' => $id,
                //'info' => $data
            );
            D('LogAdmin')->addLog($log);
            $this->ajaxReturn(['status' => 1, 'info' => '状态修改成功']);
        } else {
            $this->ajaxReturn(['status' => 0, 'info' => '状态修改失败']);
        }
    }

    //获取列表并分页
    private function getList($condition, $pageIndex = 1, $pageCount = 10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        import('Library.Org.Page.Page');
        $result = D("Advbanner")->getAdvBannerList($condition, ($pageIndex - 1) * $pageCount, $pageCount);
        $count = $result['count'];
        $list = $result['result'];
        $config = array("prev", "first", "last", "next");
        $page = new \Page($pageIndex, $pageCount, $count, $config);
        $pageTmp = $page->show();
        return array("list" => $list, "page" => $pageTmp);
    }
}