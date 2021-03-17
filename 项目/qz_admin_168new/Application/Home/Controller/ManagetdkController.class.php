<?php

//管理TDK

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class ManagetdkController extends HomeBaseController
{
    //装修资讯
    const INFO_TYPE_COMPANY = 1;
    const INFO_TYPE_BM = 2;
    //tdk位置 1.pc端 2.移动端
    const LOCATION_PC = 1;
    const LOCATION_MOB = 2;

    //暂时模块位置
    private $location = array(
        1 => ['model' => '首页', 'url' => ''],
        2 => ['model' => '装修公司', 'url' => 'company'],
        3 => ['model' => '装修资讯', 'url' => 'zxinfo'],
    );

    //暂时 tdk参数
    private $parameter = array(
        ['logo' => 'bm', 'name' => '城市'],
        ['logo' => 'pagenumber', 'name' => '分页'],
        ['logo' => 'suffix', 'name' => '通用后缀'],
    );

    //首页
    public function index()
    {
        $pageIndex = max(1, I('get.p'));
        $pageCount = 20;
        $get = I('get.');
        $map = [];
        if ($get['search_location']) {
            $map['m.location'] = ['eq', $get['search_location']];
        }
        if ($get['search_cs']) {
            $map['m.cs'] = ['eq', $get['search_cs']];
        }
        if ($get['search_model']) {
            $map['m.model'] = ['eq', $get['search_model']];
            //目前只有选择 装修资讯 了才有子频道
            if ($get['search_model'] == 3) {
                if ($get['search_zxinfo']) {
                    $map['m.child_model'] = ['eq', $get['search_zxinfo']];
                }
            }
        }
        //获取PC端装修资讯的子栏目
        $infoType = D('Infotype')->getTypes(self::INFO_TYPE_BM);
        //获取页面数据
        $list = $this->getTdkList($map, $pageIndex, $pageCount, $infoType);
        //将城市排序
        $citys = getUserCitys();
        $this->assign("model", $this->location);
        $this->assign("parameter", $this->parameter);
        $this->assign("zxinfo_child", $infoType);
        $this->assign("citys", $citys);
        $this->assign("info", $list);
        $this->display();
    }

    /**
     * 删除操作
     */
    public function deltdk()
    {
        $id = I('get.id');
        $infoType = D('Managetdk')->del($id);
        if ($infoType) {
            $this->ajaxReturn(['info' => '', 'status' => 1]);
        }
        $this->ajaxReturn(['info' => '删除失败 ! 请刷新再试 ! ', 'status' => 0]);
    }

    /**
     * 获取单个tdk数据
     */
    public function getTdkInfo()
    {
        $id = I('get.id');
        $infoType = D('Managetdk')->selectOneTdk($id);
        if ($infoType) {
            $this->ajaxReturn(['info' => $infoType, 'status' => 1]);
        }
        $this->ajaxReturn(['info' => '删除失败 ! 请刷新再试 ! ', 'status' => 0]);
    }

    /**
     * 保存tdk数据
     */
    public function saveTdk()
    {
        $data = I('post.');
        $ids = $data['edit_id'];
        //默认修改的保存数据
        $save = [
            'last_uid' => session('uc_userinfo.id'),
            'last_time' => time(),
        ];
        $save['title'] = str_replace('*', ',', remove_xss(str_replace(',', '*', $data['edit_title'])));
        $save['keywords'] = str_replace('*', ',', remove_xss(str_replace(',', '*', $data['edit_keywords'])));
        $save['description'] = str_replace('*', ',', remove_xss(str_replace(',', '*', $data['edit_description'])));
        if (!$data['edit_id']) {
            $this->ajaxReturn(['status' => 0, 'info' => '未查找到对应编号数据, 请查询后再试 !']);
        }
        //判断是否存在
        $status = D('Managetdk')->selectOneTdkByMap(['id' => $ids]);
        if (!$status) {
            $this->ajaxReturn(['info' => '编辑失败 ! 未查找到当前数据 ! 请刷新再试 ! ', 'status' => 0]);
        }
        $infoType = D('Managetdk')->saveEditData($ids, $save);
        if ($infoType) {
            $this->ajaxReturn(['info' => '', 'status' => 1]);
        }
        $this->ajaxReturn(['info' => '编辑失败 ! 请刷新再试 ! ', 'status' => 0]);
    }

    public function addTdk()
    {
        $data = I('post.');
        $save = [
            'cs' => $data['add_cs'],
            'location' => $data['add_location'],
            'title' => str_replace('*', ',', remove_xss(str_replace(',', '*', $data['add_title']))),
            'keywords' => str_replace('*', ',', remove_xss(str_replace(',', '*', $data['add_keywords']))),
            'description' => str_replace('*', ',', remove_xss(str_replace(',', '*', $data['add_description']))),
            'child_model' => $data['add_zxinfo'],
            'model' => $data['add_model'],
            'uid' => session('uc_userinfo.id'),
            'last_uid' => session('uc_userinfo.id'),
            'add_time' => time(),
            'last_time' => time(),
        ];
        //判断是否已经存在数据
        $map = [
            'cs' => $save['cs'],
            'location' => $save['location'],
            'model' => $save['model'],
            'child_model' => $save['child_model'],
        ];
        $status = D('Managetdk')->selectOneTdkByMap($map);
        if ($status) {
            $this->ajaxReturn(['info' => '已存在数据,请查询后再试 ! ', 'status' => 0]);
        }
        $infoType = D('Managetdk')->saveAddData($save);
        if ($infoType) {
            $this->ajaxReturn(['info' => '', 'status' => 1]);
        }
        $this->ajaxReturn(['info' => '新增失败 ! 请刷新再试 ! ', 'status' => 0]);
    }

    /**
     * 获取
     * @param $map
     * @param int $pageIndex
     * @param int $pageCount
     * @param string $info
     * @return array
     */
    private function getTdkList($map, $pageIndex = 1, $pageCount = 20, $info = '')
    {
        $count = D('Managetdk')->getInfoCount($map);
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $page = new \Page($count, $pageCount);
            $show = $page->show();
            $result = D('Managetdk')->getInfo($map, ($pageIndex - 1) * $pageCount, $pageCount);
            //取出 装修资讯的子频道
            $child_type = [];
            if ($info) {
                foreach ($info as $i => $o) {
                    $child_type[$o['id']] = $o;
                }
            }
            foreach ($result as $k => $v) {
                //pc端
                if ($v['location'] == self::LOCATION_PC) {
                    switch ($v['model']) {
                        case 2:
                            $jump_url = 'http://' . $v['bm'] . '.' . C('QZ_YUMING') . '/company/';//装修公司跳转页面
                            break;
                        case 3:
                            //目前只有装修资讯才有子频道
                            $result[$k]['child_name'] = $child_type[$v['child_model']]['name'];
                            $result[$k]['child_shortname'] = $child_type[$v['child_model']]['shortname'];
                            $jump_url = 'http://' . $v['bm'] . '.' . C('QZ_YUMING') . '/zxinfo/';
                            //如果是子频道就添加 子频道地址
                            if ($v['child_model']) {
                                $jump_url = 'http://' . $v['bm'] . '.' . C('QZ_YUMING') . '/zxinfo/' . $child_type[$v['child_model']]['shortname'] . '/';
                            }
                            break;
                        default:
                            $jump_url = 'http://' . $v['bm'] . '.' . C('QZ_YUMING') . '/';//对应tdk的跳转地址默认跳转为分站首页
                    }
                }
                //移动端
                if ($v['location'] == self::LOCATION_MOB) {
                    switch ($v['model']) {
                        case 2:
                            $jump_url = 'http://' . C('MOBILE_DONAMES') . '/' . $v['bm'] . '/company/';//装修公司跳转页面
                            break;
                        case 3:
                            //目前只有装修资讯才有子频道
                            $result[$k]['child_name'] = $child_type[$v['child_model']]['name'];
                            $result[$k]['child_shortname'] = $child_type[$v['child_model']]['shortname'];
                            $jump_url = 'http://' . C('MOBILE_DONAMES') . '/' . $v['bm'] . '/zxinfo/';
                            //如果是子频道就添加 子频道地址
                            if ($v['child_model']) {
                                $jump_url = 'http://' . C('MOBILE_DONAMES') . '/' . $v['bm'] . '/zxinfo/' . $child_type[$v['child_model']]['shortname'] . '/';
                            }
                            break;
                        default:
                            $jump_url = 'http://' . C('MOBILE_DONAMES') . '/' . $v['bm'] . '/';//对应tdk的跳转地址默认跳转为分站首页
                    }
                }
                $result[$k]['jump_url'] = $jump_url;
            }
        }
        return array("list" => $result, "page" => $show);
    }
}