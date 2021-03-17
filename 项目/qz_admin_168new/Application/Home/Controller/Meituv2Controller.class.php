<?php

namespace Home\Controller;

use Think\Exception;
use Common\Enums\ApiConfig;
use Home\Common\Controller\HomeBaseController;

use Home\Model\Db\MeituV2HomeModel;
use Home\Model\Logic\MeituV2LogicModel;
use Home\Model\Logic\MeituV2HomeLogicModel;
use Home\Model\Logic\ThematicWordsLogicModel;
use Home\Model\Logic\MeituV2CategoryLogicModel;
use Home\Model\Service\ElasticsearchServiceModel;

class Meituv2Controller extends HomeBaseController
{
    protected $meituV2CategoryLogic;

    protected $meityV2Logic;
    protected $meituV2HomeLogic;

    public function __construct()
    {
        parent::__construct();
        $this->meituV2CategoryLogic = new MeituV2CategoryLogicModel();
        $this->meityV2Logic = new MeituV2LogicModel();
        $this->meituV2HomeLogic = new MeituV2HomeLogicModel();
    }

    //美图首页管理
    public function index()
    {
        // 获取推荐到首页的数据
        //公装
        $gongzhuanglist = $this->meituV2HomeLogic->getMeituListByType(1);
        //家装
        $jiazhuanglist = $this->meituV2HomeLogic->getMeituListByType(2);

        //3D效果图
        $map['h.type'] = 3;
        //获取结果
        $threeDList = $this->meituV2HomeLogic->getThreeDItemList($map);

        //装修风格推荐
        $recommend = $this->meituV2HomeLogic->getTuHomeList(4);

        $modulelist = [];
        $modulelist[0]['module'] = "meituv2-1";
        $modulelist[1]['module'] = "pubmeitu-2";
        $modulelist[2]['module'] = "3dmeitu-3";

        $this->assign('recommend', $recommend);
        $this->assign('modulelist', $modulelist);
        $this->assign('gongzhuanglist', $gongzhuanglist);
        $this->assign('jiazhuanglist', $jiazhuanglist);
        $this->assign('threedlist', $threeDList);
        $this->display();
    }

    //美图管理-选取家装弹窗
    public function meituBox()
    {
        $params = I('get.');
        $params['type'] = 2;

        $module = 'meituv2-1';
        $info['module'] = $module;

        $ids = $this->getIdsList($module);
        $list = $this->meityV2Logic->getMeiluBoxList($params);
        $list['data'] = $this->getChoiceList($list['data'], $ids);


        //获取家装下的二级分类
        $level2Category = $this->meituV2CategoryLogic->getMeituLv2Category(2);

        //获取查询分类
        $categorylist = $this->meituV2CategoryLogic->getMeituCategoryGroupByType(2);
        $this->assign('categorylist', $categorylist);


        $this->assign('info', $info);

        $this->assign('list', $list);
        $this->assign('level2Category', $level2Category);
        $this->assign('requestData', $params);

        $this->display();
    }

    //美图管理-选取公装弹窗
    public function gzBox()
    {
        $params = I('get.');
        $params['type'] = 1;


        $module = 'pubmeitu-2';
        $info['module'] = $module;


        $list = $this->meityV2Logic->getPubMeiluBoxList($params);
        $ids = $this->getIdsList($module);
        $list['data'] = $this->getChoiceList($list['data'], $ids);


        //获取查询分类
        $categorylist = $this->meituV2CategoryLogic->getMeituCategoryGroupByType(1);
        $this->assign('categorylist', $categorylist);


        $this->assign('info', $info);

        $this->assign('list', $list);
        $this->assign('requestData', $params);

        $this->display();
    }

    //公装美图管理-选取3D弹窗
    public function threeBox()
    {

        $is_choice = I('get.is_choice');
        $params = I('get.');

        $pageIndex = I('get.p') > 0 ? intval(I('get.p')) : 1;
        $pageCount = 10;

        $module = '3dmeitu-3';

        $ids = $this->getIdsList($module);
        if (!empty($ids)) {
            if ($is_choice == '1') {
                $condition['id'] = array('IN', $ids);
            } elseif ($is_choice == '2') {
                $condition['id'] = array('NOT IN', $ids);
            }
        } else {
            if ($is_choice == '1') {
                $condition['id'] = array('eq', 0);
            }
        }

        $condition['fengge'] = I('get.fengge');
        $condition['huxing'] = I('get.huxing');
//        $condition['adminuser_id'] = I('get.adminuser_id');
        $condition['title'] = I('get.title');
        $info = $this->meituV2HomeLogic->get3DMeituList($condition, $pageIndex, $pageCount);


        $info['list'] = $this->getChoiceList($info['list'], $ids);
        $info['category'] = M('xiaoguotu_threedimension_category')->where(array(
            'status' => 1
        ))->select();
        $info['adminuser'] = D('Adminuser')->getAdminuserListByUid(26);
        $info['module'] = $module;
        $this->assign('info', $info);

        $module = '3dmeitu-3';
        $info['module'] = $module;
        $this->assign('info', $info);

        $this->assign('requestData', $params);

        $this->display();
    }

    //获取选择状态后列表
    public function getChoiceList($list, $ids)
    {
        foreach ($list as $k => $v) {
            if (in_array($v['id'], $ids)) {
                $list[$k]['toggleIcon'] = 'fa-toggle-on';
            } else {
                $list[$k]['toggleIcon'] = 'fa-toggle-off';
            }
        }
        return $list;
    }

    //公装美图管理-列表页
    public function publicMeiTuList()
    {
        $params = I('get.');
        $params['type'] = 1;
        $list = $this->meityV2Logic->getPubList($params);


        //获取查询分类
        $categorylist = $this->meituV2CategoryLogic->getMeituCategoryGroupByType(1);
        $this->assign('categorylist', $categorylist);

        $this->assign('list', $list);
        $this->assign('requestData', $params);
        $this->display();
    }

    //公装美图管理-美图添加/编辑页
    public function publicMeituDetail()
    {
        $imaggHost = C('QINIU_DOMAIN');
        //获取查询分类
        $categorylist = $this->meituV2CategoryLogic->getMeituCategoryGroupByType(1);
        $this->assign('categorylist', $categorylist);
        $this->assign('imageHost', $imaggHost);
        $this->display();
    }


    //公装美图管理-公装分类管理
    public function publicMeituCategory()
    {
        $param = I('get.');

        $pubcategory = $this->meituV2CategoryLogic->getMeituLv2Category(1); // 1表示获取公装二级分类
        $this->assign('pubcategory', $pubcategory);

        $list = $this->meituV2CategoryLogic->getMeituCategoryList($param, 1);        //1表示获取公装三级分类

        $this->assign('list', $list['list']);
        $this->assign('page', $list['page']);
        $this->assign("getdata", $param);
        $this->display();
    }


    //家装美图列表页
    public function meituList()
    {
        $params = I('get.');
        $params['type'] = 2;
        $list = $this->meityV2Logic->getHomeList($params);

        //获取家装下的二级分类
        $level2Category = $this->meituV2CategoryLogic->getMeituLv2Category(2);

        //获取查询分类
        $categorylist = $this->meituV2CategoryLogic->getMeituCategoryGroupByType(2);
        $this->assign('categorylist', $categorylist);

        $this->assign('list', $list);
        $this->assign('level2Category', $level2Category);
        $this->assign('requestData', $params);
        $this->display();
    }

    //家装美图管理-美图添加/编辑页
    public function meituDetail()
    {
        $imaggHost = C('QINIU_DOMAIN');
        //获取查询分类
        $categorylist = $this->meituV2CategoryLogic->getMeituCategoryGroupByType(2);
        $this->assign('categorylist', $categorylist);
        $this->assign('imageHost', $imaggHost);

        $this->display();
    }


    //家装美图管理-家装分类管理
    public function meituCategory()
    {
        $param = I('get.');

        $meitucategory = $this->meituV2CategoryLogic->getMeituLv2Category(2); // 1表示获取公装二级分类
        $categorylist = [];
        foreach ($meitucategory as $key => $val) {
            $categorylist[] = intval($val['id']);
        }
        $this->assign('meitucategory', $meitucategory);
        if (!in_array($param['type'], $categorylist)) {
            $this->_error("错误的类型类别");
        }

        $list = $this->meituV2CategoryLogic->getMeituCategoryList($param, 2);        //1表示获取公装三级分类


        $this->assign('list', $list['list']);
        $this->assign('page', $list['page']);
        $this->assign("getdata", $param);
        $this->display();
    }

    //添加分类（家装/公装公用）
    public function changeCategoryAction()
    {
        $param = I('post.');

        if ($param['type'] && ($param['type'] == 1 || $param['type'] == 2)) {
            $type = $param['type'];
        } else {
            $this->ajaxReturn(['error_code' => ApiConfig::CANNOT_GETDATA, 'error_msg' => "类型错误，请检查后重试"]);
        }

        if ($param['name']) {
            $save['name'] = $param['name'];
        } else {
            $this->ajaxReturn(['error_code' => ApiConfig::CANNOT_GETDATA, 'error_msg' => "属性名不能为空"]);
        }

        if ($param['url']) {
            $save['url'] = $param['url'];
            //正则验证url
            $preg = '/^[a-zA-Z0-9]+$/u';
            if (!preg_match($preg, $save['url'])) {
                $this->ajaxReturn(['error_code' => ApiConfig::PARAMETER_ILLEGAL, 'error_msg' => "url格式错误"]);
            }
        } else {
            $this->ajaxReturn(['error_code' => ApiConfig::CANNOT_GETDATA, 'error_msg' => "url不能为空"]);
        }

        if ($param['parent']) {
            $save['parent'] = $param['parent'];
        } else {
            $this->ajaxReturn(['error_code' => ApiConfig::CANNOT_GETDATA, 'error_msg' => "类别不能为空"]);
        }

        if ($param['title']) {
            $save['title'] = $param['title'];
        } else {
            $this->ajaxReturn(['error_code' => ApiConfig::CANNOT_GETDATA, 'error_msg' => "标题不能为空"]);
        }

        if ($param['keyword']) {
            $save['keyword'] = $param['keyword'];
        } else {
            $this->ajaxReturn(['error_code' => ApiConfig::CANNOT_GETDATA, 'error_msg' => "关键字不能为空"]);
        }

        if ($param['description']) {
            $save['description'] = $param['description'];
        } else {
            $this->ajaxReturn(['error_code' => ApiConfig::CANNOT_GETDATA, 'error_msg' => "描述不能为空"]);
        }


        if (isset($param['sort'])) {
            $save['sort'] = $param['sort'];
        }

        if ($param['is_recommend']) {
            $save['is_recommend'] = $param['is_recommend'];
        }

        if ($param['is_enable']) {
            $save['is_enable'] = $param['is_enable'];
        }
        //数据验证
        $result = $this->meituV2CategoryLogic->checkCategoryData($param, $type);
        if ($result !== true) {
            $this->ajaxReturn($result);
        }

        if ($param['id']) {
            //编辑
            $save['updated_at'] = time();
            $save['operator'] = $_SESSION['uc_userinfo']['id'];
            $result = $this->meituV2CategoryLogic->editCategory($param['id'], $save);
        } else {
            //添加
            $save['created_at'] = time();
            $save['updated_at'] = time();
            $save['creator'] = $_SESSION['uc_userinfo']['id'];
            $save['operator'] = $_SESSION['uc_userinfo']['id'];
            $result = $this->meituV2CategoryLogic->addCategory($save);
        }
        if ($result) {
            $this->ajaxReturn(['error_code' => ApiConfig::REQUEST_SUCCESS, 'error_msg' => "操作成功"]);
        } else {
            $this->ajaxReturn(['error_code' => ApiConfig::REQUEST_FAILL, 'error_msg' => "操作失败"]);
        }

    }


    /**
     * 保存美图数据(添加&更新)
     */
    public function saveMeitu()
    {
        try {
            $params = I('post.');

            if (empty($params)) {
                throw new Exception('请填写数据', ApiConfig::PARAMETER_ILLEGAL);
            }
            $type = (int)$params['type'];
            if (!in_array($type, [1, 2], true)) {
                throw new Exception('保存失败', ApiConfig::PARAMETER_ILLEGAL);
            }

            if (empty($params['title'])) {
                throw new Exception('请填写标题', ApiConfig::PARAMETER_ILLEGAL);
            }

            //公装分类
            if ($type == 1 && empty($params['pub_category'])) {
                throw new Exception('请选择分类', ApiConfig::PARAMETER_ILLEGAL);
            }

            //家装分类

            //是家装,分类必须为4个
            if ($params['type'] == 2 && (empty($params['space_category']) || empty($params['part_category']) || empty($params['style_category']) || empty($params['layout_category']))) {
                throw new Exception('请选择分类', ApiConfig::PARAMETER_ILLEGAL);
            }

            if (empty($params['keyword'])) {
                throw new Exception('请填写关键字', ApiConfig::PARAMETER_ILLEGAL);
            }

            if (empty($params['description'])) {
                throw new Exception('请填写描述', ApiConfig::PARAMETER_ILLEGAL);
            }

            $params['publish_time'] = strtotime($params['publish_time']) ?: 0;

            //图片
            if (empty($params['image_src'])) {
                throw new Exception('请上传图片', ApiConfig::PARAMETER_ILLEGAL);
            }

            if (empty($params['image_width']) || empty($params['image_height'])) {
                throw new Exception('未发现图片宽高数据', ApiConfig::PARAMETER_ILLEGAL);
            }

            $params['image_title'] = $params['image_title'] ?: '';

            //编辑/添加
            if ($params['id']) {
                //是否有重复标题
                $map['title'] = $params['title'];
                $map['type'] = $params['type'];
                $map['id'] = ['neq', $params['id']];
                if ($this->meityV2Logic->hasValue($map)) {
                    throw new Exception('标题不能重复哦', ApiConfig::PARAMETER_ILLEGAL);
                }
                $this->meityV2Logic->update($params);
            } else {
                //是否有重复标题
                $map['title'] = $params['title'];
                $map['type'] = $params['type'];
                if ($this->meityV2Logic->hasValue($map)) {
                    throw new Exception('标题不能重复哦', ApiConfig::PARAMETER_ILLEGAL);
                }

                $id = $this->meityV2Logic->add($params);

                // 关联标签
                $searchService = new ElasticsearchServiceModel();
                $result = $searchService->getContentLabel($params["title"], 2, 10);
                if (count($result) > 0) {
                    $allLabel = [];
                    foreach ($result as $v) {
                        $allLabel[] = [
                            "thematic_id" => $v["id"],
                            "content_id" => $id,
                            "module" => $type == 1 ? 7 : 5
                        ];
                    }
                    if (count($allLabel) > 0) {
                        $thematicLogic = new ThematicWordsLogicModel();
                        $thematicLogic->addThematicRelated($allLabel);
                    }
                }
            }

            $code = 0;
            $msg = 'ok';
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }
        $data = [
            'code' => $code,
            'msg' => $msg,
        ];
        $this->ajaxReturn($data);
    }

    /**
     *
     * 删除美图
     */
    public function delMeitu()
    {
        try {
            $id = I('post.id');
            if (empty($id)) {
                throw new Exception('未发现美图内容', ApiConfig::PARAMETER_ILLEGAL);
            }
            $this->meityV2Logic->delMeitu($id);

            $code = 0;
            $msg = 'ok';
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }
        $data = [
            'code' => $code,
            'msg' => $msg,
        ];
        $this->ajaxReturn($data);
    }

    /**
     * 获取详情
     */
    public function getMeituDetail()
    {
        $data = [];
        try {
            $id = I('get.id');
            $type = I('get.type');
            if (empty($id) || empty($type)) {
                throw new Exception('未发现美图内容', ApiConfig::PARAMETER_ILLEGAL);
            }
            $data = $this->meityV2Logic->getMeituDetail($id, $type);
            if (empty($data)) {
                throw new Exception('没有数据', ApiConfig::CANNOT_FIND_DATA);
            }
            $code = 0;
            $msg = 'ok';
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }
        $ret = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];
        $this->ajaxReturn($ret);
    }

    /**
     *  根据id获取分类信息
     */
    public function getCategoryInfoById()
    {
        $id = I('get.id');
        if ($id) {
            $info = $this->meituV2CategoryLogic->getCategoryInfoById($id);
            if ($info) {
                $this->ajaxReturn(['error_code' => ApiConfig::REQUEST_SUCCESS, 'error_msg' => "操作成功", "data" => $info]);
            } else {
                $this->ajaxReturn(['error_code' => ApiConfig::CANNOT_FIND_DATA, 'error_msg' => "分类不存在"]);
            }
        } else {
            $this->ajaxReturn(['error_code' => ApiConfig::CANNOT_GETDATA, 'error_msg' => "分类id不能为空"]);
        }

    }

    /**
     * 美图删除操作
     */
    public function deleteCategoryAction()
    {
        $id = I('get.id');
        if ($id) {
            $hadMeitu = $this->meituV2CategoryLogic->checkCategoryHadMeitu($id);
            if ($hadMeitu) {
                $this->ajaxReturn(['error_code' => ApiConfig::DELETE_FALE, 'error_msg' => "该类型已有数据，不可删除！"]);
            } else {
                $result = $this->meituV2CategoryLogic->deleteCategory($id);
                if ($result === false) {
                    $this->ajaxReturn(['error_code' => ApiConfig::REQUEST_FAILL, 'error_msg' => "删除失败"]);
                } else {
                    $this->ajaxReturn(['error_code' => ApiConfig::REQUEST_SUCCESS, 'error_msg' => "删除成功"]);
                }
            }
        } else {
            $this->ajaxReturn(['error_code' => ApiConfig::CANNOT_GETDATA, 'error_msg' => "分类id不能为空"]);
        }
    }


    //更新数据并返回标题
    public function saveMeituHomeData()
    {
        $module = I('get.module');

        $nowtime = time();
        if (empty($module)) {
            return false;
        }
        $idList = str_replace(array('[', ']', '&quot;'), '', I('cookie.' . $module));
        $idList = array_unique(array_filter(explode(',', $idList)));
        if ($module != '3dmeitu-3') {
            if (count($idList) != 8) {
                $this->ajaxReturn(array('error_code' => 0, 'error_msg' => '选取数量必须为8'));
            }
        } else {
            if (count($idList) != 5) {
                $this->ajaxReturn(array('error_code' => 0, 'error_msg' => '选取数量必须为5'));
            }
        }

        if ($module == '3dmeitu-3') {     //3D美图设置
            $type = 3;
        } elseif ($module == 'meituv2-1') {
            $type = 2;          //家装美图
        } else {
            $type = 1;      //公装美图
        }
        $oldlist = $this->meituV2HomeLogic->getHomeMeituId($type);
        $oldItemList = [];
        if ($oldlist) {
            foreach ($oldlist as $k => $v) {
                $oldItemList[$k] = $v['link_id'];
            }
            //多余
            $destroyDiff = array_diff($oldItemList, $idList);

            $linklist = [];
            foreach ($destroyDiff as $k => $v) {
                $linklist[] = intval($v);
            }
            if (count($linklist) > 0) {
                $this->meituV2HomeLogic->deletedata($type, $linklist);
            }


            //新增
            $newDiff = array_diff($idList, $oldItemList);
            //新增
            $adddata = [];
            $kk = 0;
            foreach ($newDiff as $k => $v) {
                $adddata[$kk]['type'] = $type;
                $adddata[$kk]['link_id'] = intval($v);
                $adddata[$kk]['creator'] = $_SESSION['uc_userinfo']['id'];
                $adddata[$kk]['operator'] = $_SESSION['uc_userinfo']['id'];
                $adddata[$kk]['created_at'] = $nowtime;
                $adddata[$kk]['updated_at'] = $nowtime;
                $kk++;
            }
            $result = $this->meituV2HomeLogic->addAlldata($adddata);
            if ($result === false) {
                $this->ajaxReturn(array('error_code' => ApiConfig::REQUEST_FAILL, 'error_msg' => '操作失败'));
            } else {
                $this->ajaxReturn(array('error_code' => ApiConfig::REQUEST_SUCCESS, 'error_msg' => '操作成功'));
            }


        } else {
            //新增
            $adddata = [];
            $k = 0;
            foreach ($idList as $key => $val) {
                $adddata[$k]['type'] = $type;
                $adddata[$k]['link_id'] = intval($val);
                $adddata[$k]['creator'] = $_SESSION['uc_userinfo']['id'];
                $adddata[$k]['operator'] = $_SESSION['uc_userinfo']['id'];
                $adddata[$k]['created_at'] = $nowtime;
                $adddata[$k]['updated_at'] = $nowtime;
                $k++;
            }
            $result = $this->meituV2HomeLogic->addAlldata($adddata);
            if ($result === false) {
                $this->ajaxReturn(array('error_code' => ApiConfig::REQUEST_FAILL, 'error_msg' => '操作失败'));
            } else {
                $this->ajaxReturn(array('error_code' => ApiConfig::REQUEST_SUCCESS, 'error_msg' => '操作成功'));
            }

        }

    }

    //获取 Cookie中选择的ID
    public function getIdsList($module)
    {
        $ids = str_replace(array('[', ']', '&quot;'), '', I('cookie.' . $module));
        if (empty($ids)) {
            return '';
        }
        return explode(',', $ids);
    }

    public function recommentUp()
    {
        if (IS_AJAX && IS_POST) {
            $data = [
                "title" => I("post.title"),
                "thumb" => I("post.img"),
                "url" => I("post.url"),
                "updated_at" => time(),
                "operator" => session("uc_userinfo.name"),
            ];
            $logic = new MeituV2HomeLogicModel();
            $result = $logic->editTuHomeInfo(I("post.id"), $data);
            if ($result != false) {
                $this->ajaxReturn(array('error_code' => ApiConfig::REQUEST_SUCCESS, 'error_msg' => '操作成功'));
            }
            $this->ajaxReturn(array('error_code' => ApiConfig::REQUEST_FAILL, 'error_msg' => '操作失败'));
        }
    }

    /**
     * 单个/批量重新发布
     * 删除旧数据，重新发布
     */
    public function multiPublish()
    {
        try {
            $ids = I('post.ids');
            if (empty($ids)) {
                throw new Exception('参数有误', ApiConfig::PARAMETER_ILLEGAL);
            }

            //更新 创建时间以及发布时间更新为当前日期

            $this->meityV2Logic->multiPublish($ids);

            $code = 0;
            $msg = 'ok';
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }
        $ret = [
            'code' => $code,
            'msg' => $msg,
        ];
        $this->ajaxReturn($ret);
    }

    /**
     * 重新存储
     */
    public function restore()
    {
        try {
            $ids = I('post.ids');
            if (empty($ids)) {
                throw new Exception('参数有误', ApiConfig::PARAMETER_ILLEGAL);
            }

            //更新 创建时间以及发布时间更新为当前日期

            $this->meityV2Logic->restore($ids);

            $code = 0;
            $msg = 'ok';
        } catch (Exception $e) {
            $code = $e->getCode();
            $msg = $e->getMessage();
        }
        $ret = [
            'code' => $code,
            'msg' => $msg,
        ];
        $this->ajaxReturn($ret);
    }

}