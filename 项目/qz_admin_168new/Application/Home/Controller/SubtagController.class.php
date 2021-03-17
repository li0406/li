<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\SubtagLogicModel;
use Home\Model\QuyuModel;

class SubtagController extends HomeBaseController{

    /**
     * 分站标识列表
     * @return [type] [description]
     */
    public function taglist(){
        $page = I("get.p", 1);
        $input = array(
            "cs" => I("get.cs"),
            "name" => I("get.name"),
            "enabled" => I("get.enabled"),
            "date_begin" => I("get.date_begin"),
            "date_end" => I("get.date_end")
        );

        $subtagLogic = new SubtagLogicModel();
        $data = $subtagLogic->getPageList($input, $page, $limit = 20);

        if ($data["count"] > 0) {
            import('Library.Org.Util.Page');
            $page = new \Page($data["count"], $limit);
            $data["page"] = $page->show();
        }
        
        $quyuModel = new QuyuModel();
        $quyuList = $quyuModel->getAllQuyuOnly("cid,cname");
        
        $this->assign("data", $data);
        $this->assign("quyuList", $quyuList);
        $this->assign("input", $input);
        $this->display();
    }

    /**
     * 分站标识编辑
     * @return [type] [description]
     */
    public function tagsave(){
        if (IS_AJAX) {
            $id = I("id");
            $input = array(
                "cs" => I("cs"),
                "name" => trim(I("name")),
                "enabled" => I("enabled", 2)
            );

            $subtagLogic = new SubtagLogicModel();

            if (empty($input["name"])) {
                $this->ajaxReturn(["status" => 0, "info" => "请输入标识名"]);
            } else if (empty($input["cs"])) {
                $this->ajaxReturn(["status" => 0, "info" => "请选择所属城市"]);
            } else if ($subtagLogic->validateName($input["name"], $input["cs"], $id) == false) {
                $this->ajaxReturn(["status" => 0, "info" => "标识名重复，请重新输入"]);
            }

            $result = $subtagLogic->saveInfo($id, $input);
            if (empty($result)) {
                $this->ajaxReturn(["status" => 0, "info" => "编辑失败"]);
            }

            $this->ajaxReturn(["status" => 1, "info" => "编辑成功"]);
        }
    }

    /**
     * 设置标识启用、禁用
     * @return [type] [description]
     */
    public function setEnabled(){
        if (IS_AJAX) {
            $id = I("id");
            $enabled = I("enabled", 2);

            $subtagLogic = new SubtagLogicModel();
            $result = $subtagLogic->setEnabled($id, $enabled);
            if (empty($result)) {
                $this->ajaxReturn(["status" => 0, "info" => "编辑失败"]);
            }

            $this->ajaxReturn(["status" => 1, "info" => "编辑成功"]);
        }
    }

    /**
     * 删除标识（同步删关联数据）
     * @return [type] [description]
     */
    public function delete(){
        if (IS_AJAX) {
            $id = I("id");
            $subtagLogic = new SubtagLogicModel();
            $result = $subtagLogic->deleteInfo($id);
            if (empty($result)) {
                $this->ajaxReturn(["status" => 0, "info" => "删除失败"]);
            }

            $this->ajaxReturn(["status" => 1, "info" => "删除成功"]);
        }
    }

    /**
     * 选择装修公司列表
     * @return [type] [description]
     */
    public function relation_company(){
        $page = I("get.p", 1);
        $tag_id = I("get.tag_id");

        $input = array(
            "qx" => I("get.qx"),
            "keyword" => I("get.keyword"),
            "checked" => I("get.checked")
        );

        $subtagLogic = new SubtagLogicModel();
        $tagInfo = $subtagLogic->getInfo($tag_id);

        $data = $subtagLogic->getCompanyList($tag_id, $tagInfo["cs"], $input, $page, $limit = 15);
        
        if ($data["count"] > 0) {
            import('Library.Org.Util.Page');
            $page = new \Page($data["count"], $limit);
            $data["page"] = $page->show();
        }

        $quyuModel = new QuyuModel();
        $areaList = $quyuModel->getAreaByQuyu($tagInfo["cs"]);

        $this->assign("data", $data);
        $this->assign("areaList", $areaList);
        $this->assign("tagInfo", $tagInfo);
        $this->assign("input", $input);
        $this->display();
    }

    /**
     * 选择装修案例列表
     * @return [type] [description]
     */
    public function relation_case(){
        $page = I("get.p", 1);
        $tag_id = I("get.tag_id");

        $input = array(
            "classid" => I("get.classid"),
            "huxing" => I("get.huxing"),
            "leixing" => I("get.leixing"),
            "fengge" => I("get.fengge"),
            "xiaoqu" => I("get.xiaoqu"),
            "checked" => I("get.checked")
        );

        $subtagLogic = new SubtagLogicModel();
        $tagInfo = $subtagLogic->getInfo($tag_id);

        $data = $subtagLogic->getCaseList($tag_id, $tagInfo["cs"], $input, $page, $limit = 10);
        
        if ($data["count"] > 0) {
            import('Library.Org.Util.Page');
            $page = new \Page($data["count"], $limit);
            $data["page"] = $page->show();
        }
        
        $options = [];
        $options["fengge"] = D("fengge")->getFg();
        $options["huxing"] = D("huxing")->getHxByIds([10,11,12,14,15]);
        $options["leixing"] = M("leixing")->where(["type" => "leixing"])->order("px asc")->select();

        $this->assign("data", $data);
        $this->assign("tagInfo", $tagInfo);
        $this->assign("options", $options);
        $this->assign("input", $input);
        $this->display();
    }

    /**
     * 选择文章列表
     * @return [type] [description]
     */
    public function relation_article(){
        $page = I("get.p", 1);
        $tag_id = I("get.tag_id");

        $input = array(
            "state" => I("get.state"),
            "keyword" => I("get.keyword"),
            "checked" => I("get.checked")
        );

        $subtagLogic = new SubtagLogicModel();
        $tagInfo = $subtagLogic->getInfo($tag_id);

        $data = $subtagLogic->getArticleList($tag_id, $tagInfo["cs"], $input, $page, $limit = 10);
        
        if ($data["count"] > 0) {
            import('Library.Org.Util.Page');
            $page = new \Page($data["count"], $limit);
            $data["page"] = $page->show();
        }
        
        $this->assign("data", $data);
        $this->assign("tagInfo", $tagInfo);
        $this->assign("input", $input);
        $this->display();
    }

    /**
     * 选择设计师列表
     * @return [type] [description]
     */
    public function relation_designer(){
        $page = I("get.p", 1);
        $tag_id = I("get.tag_id");

        $input = array(
            "fengge" => I("get.fengge"),
            "lingyu" => I("get.lingyu"),
            "keyword" => I("get.keyword"),
            "checked" => I("get.checked")
        );

        $subtagLogic = new SubtagLogicModel();
        $tagInfo = $subtagLogic->getInfo($tag_id);

        $data = $subtagLogic->getDesignerList($tag_id, $tagInfo["cs"], $input, $page, $limit = 10);
        
        if ($data["count"] > 0) {
            import('Library.Org.Util.Page');
            $page = new \Page($data["count"], $limit);
            $data["page"] = $page->show();
        }
        
        $options["fengge"] = D("fengge")->getfg();
        $options["lingyu"] = array("住宅公寓", "写字楼", "别墅", "专卖展示店", "酒店宾馆", "餐饮酒吧", "歌舞迪", "其他");

        $this->assign("data", $data);
        $this->assign("tagInfo", $tagInfo);
        $this->assign("options", $options);
        $this->assign("input", $input);
        $this->display();
    }

    /**
     * 分站标识数据关联、解除关联
     * @return [type] [description]
     */
    public function set_relation(){
        if (IS_AJAX) {
            $type = I("type");
            $tag_id = I("tag_id");
            $relation_id = I("relation_id");
            $is_relation = I("is_relation");

            $subtagLogic = new SubtagLogicModel();
            $result = $subtagLogic->setRelation($type, $tag_id, $relation_id, $is_relation);
            if (empty($result)) {
                $this->ajaxReturn(["status" => 0, "info" => "操作失败"]);
            }

            $this->ajaxReturn(["status" => 1, "info" => "操作成功"]);
        }
    }

    /**
     * 标识导入
     * @return [type] [description]
     */
    public function importExcel(){
        if (!empty($_FILES)) {
            $fname = $_FILES["fileup"]["name"];
            $filename = $_FILES["fileup"]["tmp_name"];
            $ftype = pathinfo($fname, PATHINFO_EXTENSION);
            if (!in_array($ftype, ["xls", "xlsx"])) {
                $this->ajaxReturn(["status" => 0, "info" => "上传文件格式必须为.xlsx或.xls"]);
            }

            ini_set("max_execution_time", "0");
            import("Library.Org.Phpexcel.PHPExcel","",".php");
            import("Library.Org.Phpexcel.PHPExcel.Writer.Excel2007","",".php");

            //建立reader对象
            $PHPReader = new \PHPExcel_Reader_Excel2007();
            if(!$PHPReader->canRead($filename)){
                $PHPReader = new \PHPExcel_Reader_Excel5();
                if(!$PHPReader->canRead($filename)){
                    $this->ajaxReturn(["status" => 0, "info" => "读取文件错误"]);
                }
            }

            $objPHPExcel = $PHPReader->load($filename);
            ob_clean();
            $sheetData = $objPHPExcel->getActiveSheet()->toArray();

            $subtagLogic = new SubtagLogicModel();
            $res = $subtagLogic->saveExcelData($sheetData);
            $this->ajaxReturn(["status" => 0, "info" => "导入成功"]);
        }

        $this->ajaxReturn(["status" => 0, "info" => "请先选择要上传的文件"]);
    }

    /**
     * 模板下载
     * @param  string $file_url [description]
     * @return [type]           [description]
     */
    public function downloadTemplate($file_url = ""){
        $file_url = "./assets/home/subtag/template/subtag_template.xlsx";
        if(!file_exists($file_url)){ //检查文件是否存在
            $this->error("模板文件不存在");
            exit();
        }

        //打开文件
        $file_type = fopen($file_url, "r");

        //输入文件标签
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . filesize($file_url));
        header("Content-Disposition: attachment; filename=分站标识模板.xlsx");

        ob_clean();
        //输出文件内容
        echo fread($file_type, filesize($file_url));
        fclose($file_type);
    }
}