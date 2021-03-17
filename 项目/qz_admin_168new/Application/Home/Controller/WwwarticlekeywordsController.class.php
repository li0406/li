<?php
namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\WwwArticleKeywordsLogicModel;

class WwwarticlekeywordsController extends HomeBaseController{
    /**
     * [index 主站文章列表]
     * @return [type] [description]
     */
    public function index()
    {
        //获取计划任务日志
        $info['keywordbatch'] = D('KeywordBatch')->getKeywordBatchGroupByModule();
        //获取列表
        $p = intval(I('get.p'));
        $pageIndex = 1;
        $pageCount = 30;
        if($p > 1){
            $pageIndex = intval($p);
        }
        $keyword_module = I('get.keyword_module');
        $keyword = I('get.keyword');
        $info['info'] = $this->getWwwarticlekeywordsList($keyword_module, $keyword,$pageIndex,$pageCount);

        $this->assign('info',$info);
        $this->assign('listPageIndex',$pageIndex - 1);
        $this->assign('listPageCount',$pageCount);
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
        if(!empty($data)){
            $id = $data['id'];
            if (empty($data['keyword_module'])) {
                $this->ajaxReturn(array('data'=>'','info'=>'请选择模块！','status'=>0));
            }
            if (empty($data["name"])) {
                $this->ajaxReturn(array('data'=>'','info'=>'请填写链接名称！','status'=>0));
            }
            if (empty($data["href"])) {
                $this->ajaxReturn(array('data'=>'','info'=>'请填写链接地址！','status'=>0));
            }
            $save = array(
                "name"=>$data["name"],
                "href"=>$data["href"]
            );
            //新增文章
            if(empty($id)){
                $save['keyword_module'] = $data['keyword_module'];
                $save['time'] = time();
                $id = D('WwwArticleKeywords')->addWwwarticlekeywords($save);
                if($id){
                    $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
                }
            }else{
                $result = D('WwwArticleKeywords')->editWwwArticleKeywords($id,$save);
                if($result){
                    $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
                }
            }
            $this->ajaxReturn(array('data'=>'','info'=>'操作失败或未作修改！','status'=>0));
        }
        //文章编辑获取文章信息
        $id = I('get.id');
        if(!empty($id)){
            //获取文章的推荐标签
            $info['info'] = D('WwwArticleKeywords')->getWwwArticleKeywordsById($id);
        }
        $this->assign('info',$info);
        $this->display();
    }

    /**
     * [delete 删除文章内链关键字]
     * @return [type] [description]
     */
    public function delete(){
        $id = I('post.id');
        if (!empty($id)) {
            $result = D('WwwArticleKeywords')->deleteWwwArticleKeywordsById($id);
            if ($result) {
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
    }

    /**
     * 批量删除文章内链关键字
     */
    public function deleteAll()
    {
        if(IS_POST){
            $data = I('post.allde');
            if(!empty($data)){
                foreach ($data as $k => $v) {
                    D('WwwArticleKeywords')->deleteWwwArticleKeywordsById($v['id']);
                }
                $this->ajaxReturn(array('data'=>'','info'=>'删除文章内链关键字成功~','status'=>1));
            }
            $this->ajaxReturn(array('data'=>'','info'=>'待删除文章内链关键字为空！！','status'=>0));
        }
        $this->ajaxReturn(array('data'=>'','info'=>'删除文章内链关键字失败','status'=>0));
    }

    /**
     * [addKeywordsByUploadFile 通过上传文件添加内链关键字]
     */
    public function addKeywordsByUploadFile(){
        if($_FILES){
            $fileType = explode(".", $_FILES["fileup"]["name"]);
            $ext = $fileType[1];
            $filePath = dirname(dirname(dirname(__FILE__)))."/upload/tmp/";
            if(!is_dir($filePath)){
                mkdir($filePath,0777);
            }
            $path = $_FILES["fileup"]["tmp_name"];
            $filePath = $filePath.time().".".$ext;
            move_uploaded_file($path, $filePath);
            $action = A("Export");
            $data = $action->loadkeyword($filePath,$ext);
            $this->ajaxReturn($data,"",1);
        }
    }

    /**
     * 新增计划任务批量更新
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function addTask()
    {
        $module = I('post.module');
        if (!empty($module)) {
            $keywordBatch = D('KeywordBatch')->getKeywordBatchListByModule($module)[0];
            if (!empty($keywordBatch)) {
                $offset = time() - $keywordBatch["time"];
                if ($offset <= (86400*10)) {
                    $this->ajaxReturn(array('data'=>'','info'=>'离上次批量操作时间不满10天,晴稍后再试！','status'=>0));
                }
                if ($keywordBatch["status"] == 0) {
                    $this->ajaxReturn(array('data'=>'','info'=>'上次批量操作尚未执行,请勿重复添加！','status'=>0));
                }
            }
            //添加关键字执行记录
            $admin = getAdminUser();
            $save = array(
                "module" => $module,
                "status" => 0,
                "time" => time(),
                "uid" => $admin["id"],
                "uname" => $admin["name"]
            );
            $result = D('KeywordBatch')->addKeywordBatch($save);
            if ($result) {
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
            } else {
                $this->ajaxReturn(array('data'=>'','info'=>'操作失败,ERROR！','status'=>0));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
    }

    /**
     * [cancel 删除计划任务批量更新]
     * @return [type] [description]
     */
    public function deleteTask(){
        $module = I('post.module');
        if (!empty($module)) {
            $result = D('KeywordBatch')->deleteKeywordBatchByModule($module);
            if ($result) {
                $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
            } else {
                $this->ajaxReturn(array('data'=>'','info'=>'操作失败,该计划任务已被取消！','status'=>0));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
    }

    public function uploadArticleKeywords(){
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
            for($j=2; $j <= $highestRow; $j++) {
                $str = "";
                for($k = 'A'; $k <= $highestColumn; $k++) {
                    $str .= $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue().'|*|';//读取单元格
                }
                $data[] = array_filter(explode("|*|",$str));

            }
            //添加其他数据信息
            foreach ($data as $key => $value) {
                if (!empty($value[0]) && !empty($value[1]) && !empty($value[2]) && in_array($value[2], array('攻略', '分站', '百科'))) {
                    $data[$key] = array(
                        "name" => $value[0],
                        "href" => $value[1],
                        "keyword_module" => $value[2],
                        "time" => time()
                    );
                }
            }
            //删除本地的文件
            if(file_exists($filePath)){
                unlink($filePath);
            }
            $this->ajaxReturn(array('data'=>$data,'info'=>'解析EXCEL文件成功！','status'=>1));
        }

        //保存数据
        $data = I('post.data');
        if (!empty($data)) {
            $save = array();
            foreach ($data as $key => $value) {
                if (!empty($value['name']) && !empty($value['href']) && !empty($value['keyword_module'])) {
                    $module = 0;
                    switch ($value['keyword_module']){
                        case '攻略':
                            $module = 1;
                            break;
                        case '分站':
                            $module = 2;
                            break;
                        case '百科':
                            $module = 3;
                            break;
                    }
                    if($module != 0){
                        $save[] = array(
                            'name' => $value['name'],
                            'href' => $value['href'],
                            'keyword_module' => $module,
                            'time' => $value['time']
                        );
                    }
                }
            }
            if (!empty($save)) {
                $result = D('WwwArticleKeywords')->addAllWwwarticlekeywords($save);
                if ($result) {
                    $this->ajaxReturn(array('data'=>'','info'=>'批量添加文章内链关键字成功！','status'=>1));
                } else {
                    $this->ajaxReturn(array('data'=>'','info'=>'操作失败,KEYWORDS-SAVE-ERROR！','status'=>0));
                }
            }
            $this->ajaxReturn(array('data'=>'','info'=>'操作失败,需要添加的内链关键字为空！','status'=>0));
        }
        $this->assign('info',$info);
        $this->display();
    }

    public function findword()
    {
        if ($_POST) {
            $word = I("post.word");
            $module = I("post.module");
            $logic = new WwwArticleKeywordsLogicModel();
            $result = $logic->findWord($word,$module);
            if (count($result) > 0) {
                $this->ajaxReturn(array('error_msg'=>'已有重复数据，请重新输入','error_code'=> 1 ));
            }
            $this->ajaxReturn(array('error_code'=>0));
        }
    }

    /**
     * [downloadKeywords 下载文件内链关键字]
     * @return [type] [description]
     */
    public function downloadArticleKeywords(){
        $info = D('WwwArticleKeywords')->getAllKeywords();
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        // 设置缓存方式，减少对内存的占用
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();
        //将数据封装成EXCEL解析的格式
        foreach ($info as $key => $value) {
            $sub = array();
            $style = '';
            switch ($value["keyword_module"]) {
                case 1:
                    $style = '攻略';
                    break;
                case 2:
                    $style = '分站';
                    break;
                case 3:
                    $style = '百科';
                    break;
            }
            $sub[] = array(
                "text" => $value["name"]
            );
            $sub[] = array(
                "text" => $value["href"]
            );
            $sub[] = array(
                "text" => $style
            );
            unset($style);
            $data[] = $sub;
        }
        $j = 1;
        foreach ($data as $key => $value) {
            $i =65;//字母A的ASC值 65-95 A-Z 的ASC值
            foreach ($value as $k => $val) {
                if(count($val) == 0){
                    continue;
                }
                $char = strtoupper(chr($i));
                //每行的单元格
                $num = $char.$j;
                //单元格内容
                $phpExcel->getActiveSheet()->setCellValue($num,$val["text"]);

                //设置字体的水平居中
                $phpExcel->getActiveSheet()->getStyle($num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $i++;
            }
            $j++;
        }
        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="文章内链关键字.xls"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
    }

    //获取列表并分页
    private function getWwwarticlekeywordsList($keyword_module, $keyword,$pageIndex=1,$pageCount = 30)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        $count = D('WwwArticleKeywords')->getWwwArticleKeywordsCount($keyword_module, $keyword);
        $result['list']=D('WwwArticleKeywords')->getWwwArticleKeywordsList($keyword_module, $keyword,($pageIndex-1)*$pageCount,$pageCount);
        if($count > $pageCount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        return $result;
    }
}