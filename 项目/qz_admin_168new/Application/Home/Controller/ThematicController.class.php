<?php
namespace Home\Controller;

use Common\Enums\ApiConfig;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\LogAdminLogicModel;
use Home\Model\Logic\SpecialkeywordLogicModel;
use Home\Model\Logic\ThematicWordsLogicModel;
use Home\Model\Service\ElasticsearchServiceModel;
use Home\Model\Service\QiniuServiceModel;

class ThematicController extends HomeBaseController{
    /**
     * [主站-专题页管理]
     * @return [type] [description]
     */
    public function index()
    {
        $p = intval(I('get.p'));
        $pageIndex = 1;
        if(empty(I('get.pagesize'))){
            $pageCount = 10;
        }else{
            $pageCount = I('get.pagesize');
        }
        if($p > 1){
            $pageIndex = intval($p);
        }

        $module = I('get.module');
        $name = I('get.name');
        $start =   I("get.start");
        $end =   I("get.end");
        $type =   I("get.type");

        $logic = new ThematicWordsLogicModel();
        $info = $logic->getWordsList($name,$module,$type,$start,$end,$pageIndex,$pageCount);
        $this->assign('info',$info);
        $this->display();
    }

    /**
     * [operate 文字编辑]
     * @return [type] [description]
     */
    public function operate()
    {

        //文章新增编辑
        $data = I('post.');
        $logic = new ThematicWordsLogicModel();
        if(!empty($data)){
            $id = $data['id'];

            if (empty($data["name"])) {
                $this->ajaxReturn(array('data'=>'','info'=>'请输入专题名称！','status'=>0));
            }else if (mb_strlen($data["name"],"utf-8") > 20){
                $this->ajaxReturn(array('data'=>'','info'=>'关键词不超过20个字符！','status'=>0));
            }

            if (empty($data['module'])) {
                $this->ajaxReturn(array('data'=>'','info'=>'请选择标签栏目！','status'=>0));
            }else if (!preg_match('/^\d+$/',$data["module"])){
                $this->ajaxReturn(array('data'=>'','info'=>'标签栏目规则不匹配！','status'=>0));
            }

            if (empty($data['type'])) {
                $this->ajaxReturn(array('data'=>'','info'=>'请选择标签类型！','status'=>0));
            }

            $logic = new ThematicWordsLogicModel();
            $info = $logic->findWordInfoByName($data["name"]);

            if (count($info) > 0 && $info["id"] != $id) {
                $this->ajaxReturn(array('data'=>'','info'=>'标签名称已存在,可以使用恢复操作！','status'=>0));
            }

            //调用分词服务
            $service = new ElasticsearchServiceModel();
            $thematicLogic = new ThematicWordsLogicModel();

            $words = $service->analyzeWord($data["name"]);
            $words = implode(",",$words);
            $save = array(
                "name"=>$data["name"],
                "module"=>$data["module"],
                "type"=>$data["type"],
                "words"=> $words,
                "title"=>$data["title"],
                "keywords"=>$data["keywords"],
                "description"=>$data["description"],
                "is_hot"=>$data["is_hot"],
                "op_uid" => session("uc_userinfo.id"),
                "op_uname" => session("uc_userinfo.name"),
                "uptime" => time(),
            );

            //新增关键字
            if(empty($id)){
                $save["createtime"] = time();
                $i = $id = $logic->addInfo($save);

                //添加关联词
                $result = $service->getContentLabel($data["name"], $data["type"], 20);
                if (count($result) > 0) {
                    $allLabel = [];
                    foreach ($result as $v) {
                        if ($v["id"] != $id) {
                            $allLabel[] = [
                                "thematic_id" => $v["id"],
                                "content_id" => $id,
                                "module" => 6
                            ];
                        }
                    }
                    if (count($allLabel) > 0) {
                        $thematicLogic->addThematicRelated($allLabel);
                    }
                }
            }else{
                $i = $logic->editInfo($id,$save);
            }
            //添加操作日志
            $log = array(
                'remark' => '专题页专题新增/编辑',
                'logtype' => 'thematic',
                'action_id' => $id,
                'info' => json_encode($data)
            );
            D('LogAdmin')->addLog($log);
            if($i !== false){
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'操作失败或未作修改！','status'=>0));
        } else {
            //关键字编辑获取关键字信息
            $id = I('get.id');
            if(!empty($id)){
                //获取关键字
                $info['info'] = $logic->findWordInfoById($id);
            }
            $this->assign('info',$info);
            $this->display();
        }
    }

    /**
     * [delete 删除关键字]
     * @return [type] [description]
     */
    public function delete(){
        $id = I('post.id');
        if (!empty($id)) {
            $logic = new ThematicWordsLogicModel();
            $info = $logic->findWordInfoById($id);
            if (count($info)> 0) {
                $logic->delInfo($id);
                $logLogic = new LogAdminLogicModel();
                $logLogic->addLog('删除标签库', 'del_thematic', $info, $id);
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
    }

    /**
     * 批量删除关键字
     */
    public function deleteAll()
    {
        if(IS_POST){
            $ids = I('post.ids');
            if (count($ids) > 0) {
                $logic = new ThematicWordsLogicModel();
                $info = $logic->findWordInfoById($ids);
                $logic->delInfo($ids);
                $logLogic = new LogAdminLogicModel();
                $logLogic->addLog('批量删除标签库', 'batch_del_thematic', $info, '');
                $this->ajaxReturn(array('data' => '', 'info' => '删除关键字成功~', 'status' => 1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'待删除关键字为空！！','status'=>0));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'删除关键字失败','status'=>0));
    }

    /**
     * 批量上传
     */
    public function uploadspecialkeyword(){
        ini_set('memory_limit', '1024M');
        set_time_limit(0);
        if ($_POST) {
            $service = new ElasticsearchServiceModel();
            $logic = new ThematicWordsLogicModel();
            //处理文件上传
            if (!empty($_FILES)) {
                $fileType = explode(".", $_FILES["file_data"]["name"]);
                $ext = $fileType[1];
                $filePath = TEMP_PATH;
                if(!is_dir($filePath)){
                    mkdir($filePath,0777);
                }
                $path = $_FILES["file_data"]["tmp_name"];
                $filePath = $filePath.time().".".$ext;
                move_uploaded_file($path, $filePath);
                import('Library.Org.Phpexcel.PHPExcel',"",".php");
                import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
                if($ext == "xls"){
                    import("Library.Org.PHPExcel.Reader.Excel5","",".php");
                    $objReader = \PHPExcel_IOFactory::createReader('Excel5');
                }elseif($ext == "xlsx"){
                    import("Library.Org.PHPExcel.Reader.Excel2007","",".php");
                    $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
                }
                $objPHPExcel = $objReader->load($filePath);
                $sheet = $objPHPExcel->getSheet(0);
                $highestRow = $sheet->getHighestRow();//总列数
                $highestColumn = $sheet->getHighestColumn(); //取得总列数
                $data = array();
                for($j=3; $j <= $highestRow; $j++) {
                    $str = "";
                    for($k = 'A'; $k <= $highestColumn; $k++) {
                        $str .= $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue().'|*|';//读取单元格
                    }
                    $data[] = array_filter(explode("|*|",$str));

                }
                for($k = 'A'; $k <= $highestColumn; $k++) {
                    $str .= $objPHPExcel->getActiveSheet()->getCell("$k"."1")->getValue().'|*|';//读取单元格
                }
                $data1[] = array_filter(explode("|*|",$str));

                //添加其他数据信息
                foreach ($data as $key => $value) {
                    if($value[0] != ""){
                        if (trim($value[1]) == "图片") {
                            $descriptiontemp = '齐装网'.$value[0].'专区,收集整理精美的'.$value[0].'图片大全,供广大业主装修时参考,更多的'.$value[0].'尽在齐装网装修效果图栏目。';
                        } else {
                            $descriptiontemp = '齐装网'.$value[0].'专区,提供'.$value[0].'相关的资讯，供广大业主装修时参考,更多的'.$value[0].'尽在齐装网栏目。';
                        }
                    }else{
                        $descriptiontemp = '';
                    }
                    $data[$key] = array(
                        "name" => isset($value[0])? $value[0] :"",
                        "type" => isset($value[1])? $value[1] :"",
                        "title" => isset($value[2])? $value[2] :"",
                        "keywords" => isset($value[3])? $value[3] :"",
                        "description" => $descriptiontemp,
                        "is_hot" => isset($value[4])? $value[4] :"",
                        "createtime" => time()
                    );
                }
                //删除本地的文件
                if(file_exists($filePath)){
                    unlink($filePath);
                }
            }
            $data = array_filter($data);

            if (!empty($data)) {
                $save = array();
                $yanchong = [];
                foreach ($data as $key => $value) {
                    $is_hot = 0;
                    switch ($value['type']){
                        case '图文':
                            $type = 1;
                            break;
                        case '图片':
                            $type = 2;
                            break;
                    }

                    switch ($value['is_hot']){
                        case '是':
                            $is_hot = 1;
                            break;
                        case '否':
                            $is_hot = 0;
                            break;
                    }

                    if (empty($value["name"])) {
                        $this->ajaxReturn(array('data'=>'','info'=>'第'.($key+3).'行请输入专题名称！','status'=>0));
                    }else if (mb_strlen($value["name"],"utf-8") > 20){
                        $this->ajaxReturn(array('data'=>'','info'=>'第'.($key+3).'行专题名称不超过20个字符！','status'=>0));
                    }

                    if ($type==0) {
                        $this->ajaxReturn(array('data'=>'','info'=>'第'.($key+3).'行标签类型数据错误！','status'=>0));
                    }elseif (empty($type)) {
                        $this->ajaxReturn(array('data'=>'','info'=>'第'.($key+3).'行请输入标签类型数！','status'=>0));
                    }else if (!preg_match('/^\d+$/',$type)){
                        $this->ajaxReturn(array('data'=>'','info'=>'第'.($key+3).'行标签类型数规则不匹配！','status'=>0));
                    }

                    if (empty($value["title"])) {
                        $this->ajaxReturn(array('data'=>'','info'=>'第'.($key+3).'行请输入标题Title！','status'=>0));
                    }else if (!preg_match('/^.{1,90}$/',$value["title"])){
                        $this->ajaxReturn(array('data'=>'','info'=>'第'.($key+3).'行标题Title不超过90个字符！','status'=>0));
                    }

                    if (empty($value["keywords"])) {
                        $this->ajaxReturn(array('data'=>'','info'=>'第'.($key+3).'行请输入关键词Keywords！','status'=>0));
                    }else if (!preg_match('/^.{1,180}$/',$value["keywords"])){
                        $this->ajaxReturn(array('data'=>'','info'=>'第'.($key+3).'行关键词Keywords不超过180个字符！','status'=>0));
                    }

                    if (empty($value["is_hot"])) {
                        $this->ajaxReturn(array('data'=>'','info'=>'第'.($key+3).'行未填写是否热门！','status'=>0));
                    }

                    if(!empty($value['name'])){
                        //调用分词服务
                        $words = $service->analyzeWord($value["name"]);
                        $words = implode(",",$words);
                        $data[$key]["words"] = $words;
                        $save[] = array(
                            'name' => $value['name'],
                            'words' => $words,
                            'title' => $value['title'],
                            'keywords' => $value['keywords'],
                            'description' => $value['description'],
                            'type' => $type,
                            'is_hot' => $is_hot,
                            'createtime' => $value['createtime'],
                            'uptime' => $value['createtime'],
                            'op_uid' => session("uc_userinfo.id"),
                            'op_uname' => session("uc_userinfo.name"),
                        );
                    }

                    if (count($yanchong[$type]) > 0 && in_array($value["name"],$yanchong[$type])) {
                        $this->ajaxReturn(array('data'=>'','info'=> "第".($key+3).'行关键词已重复！','status'=>0));
                    }
                    $yanchong[$type][] = trim($value['name']);
                }
                $yanchong = array_filter($yanchong);
                if(!empty($yanchong)){
                    foreach ($yanchong as $key => $value) {
                        $names = $logic->findWordInfoByNames($value,$key);
                        foreach ($names as $item) {
                            if(in_array($item["name"],$yanchong[$key])){
                                $this->ajaxReturn(array('data'=>'','info'=>$item["name"].'关键词已存在！','status'=>0));
                            }
                        }
                    }
                }

                if (!empty($save)) {
                    $sum = count($save);
                    $save = array_chunk($save,2000);
                    foreach ($save as $item) {
                        $result = $logic->addAllInfo($item);
                    }

                    if ($result) {
                        //添加操作日志
                        $log = array(
                            'remark' => '标签批量新增',
                            'logtype' => 'thematic',
                            'action_id' => $result,
                            'info' => json_encode($save)
                        );
                        D('LogAdmin')->addLog($log);
                        if (count($data) > 1) {
                            unset($data);
                        }
                        $this->ajaxReturn(array('data'=>$data,'info'=>'批量添加专题成功，已上传'.$sum.'条数据！','status'=>1));
                    } else {
                        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
                    }
                }
                $this->ajaxReturn(array('data'=>'','info'=>'操作失败,需要添加的专题为空！','status'=>0));
            }elseif(IS_POST){
                $this->ajaxReturn(array('data'=>'','info'=>'已上传0条数据！','status'=>0));
            }
        } else {
            $this->display();
        }
    }

    /**
     * 内容标签维护
     */
    public function labelMaintenance()
    {
        $name = I("get.title");
        $module = I("get.module");

        if (!empty($name) && !empty($module)) {
            $logic = new ThematicWordsLogicModel();
            $result = $logic->getContentThematicWords($name,$module);
            $this->assign("info",$result);
        }

        $this->display();
    }

    /**
     * 相关标签维护
     */
    public function labelRelevant()
    {
        $label = I("get.label");

        if (!empty($label)) {
            $logic = new ThematicWordsLogicModel();
            $result = $logic->getContentThematicWords($label,6);
            $this->assign("info",$result);
        }

        $this->display();
    }

    public function upfile()
    {
        $file = $_FILES[array_keys($_FILES)[0]];
        if (!empty($file['name'])) {
            $fromPath = $file["tmp_name"];

            $class = I("post.class");

            import('Library.Org.Qiniu.io', '', '.php');
            import('Library.Org.Qiniu.rs', '', '.php');
            $bucket = OP('QINIU_BUCKET');
            $accessKey = OP('QINIU_AK');
            $secretKey = OP('QINIU_CK');
            Qiniu_SetKeys($accessKey, $secretKey);

            $putPolicy = new \Qiniu_RS_PutPolicy($bucket);

            if ($class == 2) {
                $path = $putPolicy->SaveKey = "custom/thematic/stop.dic";
            } else {
                $path = $putPolicy->SaveKey = "custom/thematic/dict.dic";
            }

            //删除原文件
            $service = new QiniuServiceModel();
            $service->deleteQiniuFile($path);

            $upToken = $putPolicy->Token(null);
            $putExtra = new \Qiniu_PutExtra();
            $putExtra->Crc32 = 1;
            list($ret, $err) = Qiniu_PutFile($upToken, $path, $fromPath, $putExtra);

            if($err == null){
                $result = array(
                    "hash"=>$ret["hash"],
                    "key"=> $ret["key"],
                    "filename" => $file['name']
                );

                //刷新CDN
                $url = "http://".C("QINIU_DOMAIN")."/".$path;
                $service->refreshCDN($upToken,$url);

                $this->ajaxReturn(array('status'=>1, 'data' => $result));
            }
        }
        $this->ajaxReturn(array('status'=>0, 'info' => '上传失败，请清空文件后再上传！'));
    }

    /**
     * p.2.18.0 新增装修知识页面   推荐知识到知识页
     */
    public function recommendToZhishiOrCancel()
    {
        $logic = new SpecialkeywordLogicModel();
        $param = I('post.');
//        dump($param);die;
        if(!$param['id']){
            $return = [];
            $return['error_code'] = ApiConfig::REQUEST_FAILL;
            $return['error_msg'] = 'id不能为空';
            $this->ajaxReturn($return);
        }else{
            if(!$param['type']){
                $return = [];
                $return['error_code'] = ApiConfig::REQUEST_FAILL;
                $return['error_msg'] = 'type不能为空';
                $this->ajaxReturn($return);
            }
            $type = $param['type'] ? intval($param['type']) : 1;
            $info = $logic->getSpecialkeywordByIdV2($param['id'],$type);
            if($info){
                $result = $logic->recommendToZhishiOrCancel($info,$param['id']);
                if($result){
                    $return = [];
                    $return['error_code'] = ApiConfig::REQUEST_SUCCESS;
                    $return['error_msg'] = '操作成功';
                    $return['data'] = $result;
                    $this->ajaxReturn($return);
                }else{
                    $return = [];
                    $return['error_code'] = ApiConfig::REQUEST_FAILL;
                    $return['error_msg'] = '操作失败';
                    $this->ajaxReturn($return);
                }

            }else{
                $return = [];
                $return['error_code'] = ApiConfig::REQUEST_FAILL;
                $return['error_msg'] = '操作失败';
                $this->ajaxReturn($return);
            }

        }
    }

    public function findlabel()
    {
        $q = I("post.q");
        $logic = new ThematicWordsLogicModel();
        $list = $logic->findlabel($q);
        $this->ajaxReturn(array('status'=>1, "data" => $list));
    }

    public function labelrelateup()
    {
        if (IS_POST) {
            $id = I("post.id");
            $lables = I("post.labels");
            $module = I("post.module");
            $lables = array_unique($lables);
            $logic = new ThematicWordsLogicModel();
            //删除原有标签关联
            $logic->delThematicRelated($id,$module);

            foreach ($lables as $value) {
                $allLabel[] = [
                    "thematic_id" => $value,
                    "content_id" => $id,
                    "module" => $module
                ];
            }

            $logic->addThematicRelated($allLabel);
            $this->ajaxReturn(array('status'=>1));
        }
    }

    public function searchword()
    {
        $logic = new ThematicWordsLogicModel();
        $info = $logic->getSearchWordsList(I("get.date"),I("get.word"),I("get.exact"));

        $this->assign("info",$info);
        $this->display();
    }

    public function exportsearchword()
    {
        $logic = new ThematicWordsLogicModel();
        $result = $logic->exportsearchword(I("get.date"),I("get.word"),I("get.exact"));

        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        $writer = new \XLSXWriter();
        //标题
        $herder = array(
            '关键词',
            'pc+移动',
            'pc',
            '移动'
        );
        $writer->writeSheetRow('Sheet1', $herder);
        foreach ($result as $key => $value) {
            $dd = [
                "word" => $value["word"],
                "all" => $value["all_count"],
                "p" => $value["p_count"],
                "m" => $value["m_count"],
            ];
            $writer->writeSheetRow('Sheet1', $dd);
        }

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="搜索关键词统计.xlsx"');
        header("Content-Transfer-Encoding:binary");
        $writer->writeToStdOut("php://output");
    }
}