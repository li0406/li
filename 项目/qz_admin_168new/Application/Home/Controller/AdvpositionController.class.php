<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\AdvpositionLogicModel;
use Home\Model\Logic\LogAdminLogicModel;

class AdvpositionController extends HomeBaseController
{

    //投放广告
    public function adv_option()
    {
        $city = getUserCitys();
        $param = I('get.');
        $advpositionLogic = new AdvpositionLogicModel();

        $platform_list = $advpositionLogic->getPositonOptionByParentId(0);

        $module_list = [];
        if (!empty($param["platform_id"])) {
            $module_list = $advpositionLogic->getPositonOptionByParentId($param["platform_id"]);
        }

        $page_list = [];
        if (!empty($param["module_id"])) {
            $page_list = $advpositionLogic->getPositonOptionByParentId($param["module_id"]);
        }

        $this->assign("platform_list", $platform_list);
        $this->assign("module_list", $module_list);
        $this->assign("page_list", $page_list);

        $list = $advpositionLogic->getAdvert($param);

        $this->assign('page', $list['page']);
        $this->assign('list', $list['list']);
        $this->assign('city', $city);
        $this->assign('param', $param);
        $this->display();
    }

    //添加广告
    public function add_adv()
    {
        $advpositionLogic = new AdvpositionLogicModel();
        //广告位置
        $location = $advpositionLogic->getValidAdvertPosition();
        //城市
        $city = getUserCitys(false);
        if (I('get.id')) {
            //编辑
            $advLogic = new AdvpositionLogicModel();
            $info = $advLogic->getAdvInfo(I('get.id'));
            $this->assign('info', $info);
        }
        $this->assign('location', $location);
        $this->assign('city', $city);
        $this->display();
    }

    //查看广告
    public function adv_detail()
    {
        $advpositionLogic = new AdvpositionLogicModel();
        //广告位置
        $location = $advpositionLogic->getValidAdvertPosition();
        if (I('get.id')) {
            //编辑
            $advLogic = new AdvpositionLogicModel();
            $info = $advLogic->getAdvInfo(I('get.id'));
            $this->assign('info', $info);
        }
        $this->assign('location', $location);
        $this->display();
    }

    public function save_adv(){
        $advpositionLogic = new AdvpositionLogicModel();
        $info = $advpositionLogic->saveAdv(I('post.'));
        $this->ajaxReturn($info);
    }

    /**
     * 前端使用 与业务逻辑无关
     */
    public function del_delimg(){
        $this->ajaxReturn(['status'=>1,'info'=>'成功']);
    }

    //删除广告
    public function del_adv()
    {
        $id = I('post.id');
        $advpositionLogic = new AdvpositionLogicModel();
        $status = $advpositionLogic->delAdvert($id);
        if ($status) {
            $log = new LogAdminLogicModel();
            //添加日志
            $log->addLog('删除广告信息', 'del_advert', $id, $id);
            $this->ajaxReturn(['status' => 1, 'info' => '']);
        } else {
            $this->ajaxReturn(['status' => 0, 'info' => '删除失败! 刷新后再试!']);
        }
    }

    //获取广告位详细数据
    public function get_position_detail(){
        $id = I("post.advPosition");
        $advpositionLogic = new AdvpositionLogicModel();
        $info = $advpositionLogic->getPositionDetail($id);
        if ($info) {
            $this->ajaxReturn(['status' => 1, 'info' => $info]);
        } else {
            $this->ajaxReturn(['status' => 0, 'info' => '未查询到数据 , 刷新后再试!']);
        }
    }

    // 广告位列表
    public function position(){
        $param = I("get.");

        $advpositionLogic = new AdvpositionLogicModel();
        $list = $advpositionLogic->getPositionList($param);

        $platform_list = $advpositionLogic->getPositonOptionByParentId(0);

        $module_list = [];
        if (!empty($param["platform_id"])) {
            $module_list = $advpositionLogic->getPositonOptionByParentId($param["platform_id"]);
        }

        $page_list = [];
        if (!empty($param["module_id"])) {
            $page_list = $advpositionLogic->getPositonOptionByParentId($param["module_id"]);
        }

        $position_list = [];
        if (!empty($param["page_id"])) {
            $position_list = $advpositionLogic->getPositonOptionByParentId($param["page_id"]);
        }

        $this->assign("list", $list);
        $this->assign("param", $param);
        $this->assign("platform_list", $platform_list);
        $this->assign("module_list", $module_list);
        $this->assign("page_list", $page_list);
        $this->assign("position_list", $position_list);
        $this->display();
    }

    // 新增广告位
    public function add_position(){
        $id = I("param.id");

        if (IS_AJAX) {
            $advpositionLogic = new AdvpositionLogicModel();

            $data = I("param.");
            if (empty($data["name"])) {
                $this->ajaxReturn(["status" => 0, "info" => "请填写广告位名称"]);
            } else if (empty($data["position_id"])) {
                $this->ajaxReturn(["status" => 0, "info" => "请选择广告位位置"]);
            } else if (empty($data["demand"])) {
                $this->ajaxReturn(["status" => 0, "info" => "请填写广告要求"]);
            } else if (empty($data["preview"])) {
                $this->ajaxReturn(["status" => 0, "info" => "请上传预览图"]);
            } else if (mb_strlen($data["name"]) > 20) {
                $this->ajaxReturn(["status" => 0, "info" => "请填写正确广告位名称"]);
            } else if ($advpositionLogic->validatePositionName($data["name"], $id) == false) {
                $this->ajaxReturn(["status" => 0, "info" => "广告位名称不能重复"]);
            } else if ($advpositionLogic->validatePositionPositionId($data["position_id"], $id) == false) {
                $this->ajaxReturn(["status" => 0, "info" => "此位置已添加广告位,请选择其他位置"]);
            }

            if (!empty($id)) {
                $info = $advpositionLogic->getPositionDetail($id);
                if ($info["type"] != $data["type"]) {
                    $count = $advpositionLogic->getAdvertActiveCountByLocationId($id);
                    if ($count > 0) {
                        $this->ajaxReturn(["status" => 0, "info" => "存在有效广告，无法更改广告形式"]);
                    }
                }
            }

            if (I("param.confirm") == 1) {
                $result = $advpositionLogic->positionSave($id, $data);
                if (empty($result)) {
                    $this->ajaxReturn(["status" => 0, "info" => "保存失败"]);
                }

                $respData = ["id" => $result];
            }

            $this->ajaxReturn(["status" => 1, "info" => "成功", "data" => $respData]);
        }

        $advpositionLogic = new AdvpositionLogicModel();
        $platform_list = $advpositionLogic->getPositonOptionByParentId(0);
        $module_list = $page_list = $position_list = [];

        if (!empty($id)) {
            $info = $advpositionLogic->getPositionDetail($id);

            if (!empty($info["platform_id"])) {
                $module_list = $advpositionLogic->getPositonOptionByParentId($info["platform_id"]);
            }

            if (!empty($info["module_id"])) {
                $page_list = $advpositionLogic->getPositonOptionByParentId($info["module_id"]);
            }

            if (!empty($info["page_id"])) {
                $position_list = $advpositionLogic->getPositonOptionByParentId($info["page_id"]);
            }
        } else {
            $info = [
                "type" => 1,
                "enabled" => 1
            ];
        }

        $this->assign("platform_list", $platform_list);
        $this->assign("module_list", $module_list);
        $this->assign("page_list", $page_list);
        $this->assign("position_list", $position_list);
        $this->assign("info", $info);
        $this->display();
    }

    // 广告位详情
    public function position_detail(){
        $id = I("get.id");
        $advpositionLogic = new AdvpositionLogicModel();
        $info = $advpositionLogic->getPositionDetail($id);

        $this->assign("info", $info);
        $this->display();
    }

    // 广告位删除
    public function position_delete(){
        $id = I("param.id");
        $advpositionLogic = new AdvpositionLogicModel();
        $result = $advpositionLogic->validatePositionDelete($id);
        if ($result == false) {
            $this->ajaxReturn(["status" => 0, "info" => "此广告位存在广告,无法删除"]);
        }

        $confirm = I("param.confirm");
        if (I("param.confirm") == 1) {
            $result = $advpositionLogic->positionDelete($id);
            if ($result == false) {
                $this->ajaxReturn(["status" => 0, "info" => "删除失败"]);
            }
        }

        $this->ajaxReturn(["status" => 1, "info" => "成功"]);
    }

    /**
     * 获取数据联动
     * @return [type] [description]
     */
    public function get_adv_position_option(){
        $id = I("param.id");
        $advpositionLogic = new AdvpositionLogicModel();
        $list = $advpositionLogic->getPositonOptionByParentId($id);
        $this->ajaxReturn(["status" => 1, "info" => "", "data" => $list]);
    }

    /**
     * 广告位置设置
     * @return [type] [description]
     */
    public function adv_position_option(){
        $advpositionLogic = new AdvpositionLogicModel();
        $list = $advpositionLogic->getPositionOptionTree();

        $this->assign("list", $list);
        $this->display();
    }

    /**
     * 广告位置编辑页面获取
     * @return [type] [description]
     */
    public function adv_position_option_page(){
        $id = I("param.id");
        if (!empty($id)) {
            $advpositionLogic = new AdvpositionLogicModel();
            $info = $advpositionLogic->getPositionOptionDetail($id);
        } else {
            $info = [
                "level" => I("param.level"),
                "parentid" => I("param.parentid")
            ];
        }

        $this->assign("info", $info);
        $html = $this->fetch("adv_position_option_page");
        $this->ajaxReturn(["status" => 1, "info" => "", "data" => $html]);
    }

    /**
     * 广告位置编辑
     * @return [type] [description]
     */
    public function adv_position_option_save(){
        $id = I("param.id", 0);
        $name = I("param.name");
        $code = I("param.code");
        $sort = I("param.sort");
        $level = I("param.level", 1);
        $parentid = I("param.parentid", 0);

        $advpositionLogic = new AdvpositionLogicModel();

        if (empty($name)) {
            $this->ajaxReturn(["status" => 0, "info" => "请填写位置名称"]);
        } else if (empty($code)) {
            $this->ajaxReturn(["status" => 0, "info" => "请填写位置标识"]);
        } else if ($sort == "") {
            $this->ajaxReturn(["status" => 0, "info" => "请填写位置排序"]);
        } else if (mb_strlen($name) > 20) {
            $this->ajaxReturn(["status" => 0, "info" => "位置名称20个中文字符以内"]);
        } else if ($advpositionLogic->validatePositionOptionByName($name, $id, $parentid) == false) {
            $this->ajaxReturn(["status" => 0, "info" => "位置名称不能重复"]);
        } else if (!preg_match("/^[A-Za-z]+$/", $code)) {
            $this->ajaxReturn(["status" => 0, "info" => "位置标识只能英文字符"]);
        } else if (strlen($code) > 50) {
            $this->ajaxReturn(["status" => 0, "info" => "位置标识50位以内"]);
        } else if ($advpositionLogic->validatePositionOptionByCode($code, $id) == false) {
            $this->ajaxReturn(["status" => 0, "info" => "位置标识不能重复"]);
        } else if (!preg_match("/^[1-9][0-9]*$/", $sort)) {
            $this->ajaxReturn(["status" => 0, "info" => "位置排序只能正整数"]);
        } else if ($sort > 999) {
            $this->ajaxReturn(["status" => 0, "info" => "位置排序999以内"]);
        } else if ($level > 4) {
            $this->ajaxReturn(["status" => 0, "info" => "最大层级只能为4级"]);
        }

        $result = $advpositionLogic->positionOptionSave($id, $name, $code, $sort,$level, $parentid);
        if ($result == false) {
            $this->ajaxReturn(["status" => 0, "info" => "保存失败"]);
        }

        $this->ajaxReturn(["status" => 1, "info" => "保存成功"]);
    }

    /**
     * 广告位置删除
     * @return [type] [description]
     */
    public function adv_position_option_delete(){
        $id = I("param.id");
        if (!empty($id)) {
            // 验证下级数量
            $advpositionLogic = new AdvpositionLogicModel();
            $childrenNum = $advpositionLogic->getPositionOptionChildrenNum($id);
            if ($childrenNum > 0) {
                $this->ajaxReturn(["status" => 0, "info" => "此位置存在下级，无法删除"]);
            }

            // 验证广告位数量
            $positionNum = $advpositionLogic->getPositionNumByPositionId($id);
            if ($positionNum > 0) {
                $this->ajaxReturn(["status" => 0, "info" => "当前位置下已设有广告位，无法删除"]);
            }

            $result = $advpositionLogic->positionOptionDelete($id);
            if (!empty($result)) {
                $this->ajaxReturn(["status" => 1, "info" => "删除成功"]);
            }
        }

        $this->ajaxReturn(["status" => 0, "info" => "删除失败"]);
    }

}
