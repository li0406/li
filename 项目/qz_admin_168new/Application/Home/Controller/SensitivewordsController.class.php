<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
class SensitivewordsController extends HomeBaseController{

    //敏感词列表页
    public function index(){
        if($_GET['start_time']){
            $time = I("get.start_time").' 00:00:00';
            $start_time = strtotime($time); 
            $map['w.time'] = array("GT",strtotime($time)); 
        }
        if($_GET['end_time']){
            $time = I("get.end_time").' 23:59:59';
            $end_time = strtotime($time); 
            $map['w.time'] = array("LT",strtotime($time));
        }
        if(!empty($start_time) && !empty($end_time)){
            $map['w.time'] = array('between',"$start_time,$end_time");
        }
        if($_GET['word']){
            $word = I("get.word");
            $map['w.word'] = $word;
        }
        if($_GET['type']){
            $type = I("get.type");
            $type = array("EQ",$type); 
            $map['w.type'] = $type;
        }
        $map['order'] = '';
        if($_GET['idorder'] == 1){
            $map['order'] .= 'w.id asc,';
        }elseif($_GET['idorder'] == 2){
            $map['order'] .= 'w.id desc,';
        }
        if($_GET['timeorder'] == 1){
            $map['order'] .= 'w.time asc,';
        }elseif($_GET['timeorder'] == 2){
            $map['order'] .= 'w.time desc,';
        }
        //推荐状态
        if(!empty($_GET['status']) || intval($_GET['status']) === 0){
            $status = I("get.status");
            if($status != 2 && $status != ''){
                $status = array('EQ',$status);
                $map['w.status'] = $status;
            }
        }


        $pageIndex = 1;
        if(!empty($_GET['p'])){
            $pageIndex = I("get.p");
        }
        $pageSize = 20;
        if(!empty($_GET['pagecount'])){
            $pageSize = I("get.pagecount");
        }
        $search = $_GET;
        $search['pagecount'] = $pageSize;
        //查询所有ke用的敏感词分类
        $types = D("SensitiveWords")->getAllTypes(1);

        //查询所有的敏感词
        $words = $this->getAllWords($map,$pageIndex,$pageSize);
        S("Cache:Sensitivewords:list",$words['list'],3600);
        //$words = D("SensitiveWords")->getAllWords();
        //$words = $this->getAllWords();、

        //var_dump($words);
        $this->assign('search',$search);
        $this->assign('words',$words);
        $this->assign('types',$types);
        $this->display();
    }

    //添加敏感词
    public function addWords()
    {
        if(!empty($_POST['words']) && !empty($_POST['type'])){
            $data['type']           = I("post.type");
            $data['creator']        = $_SESSION['uc_userinfo']['name'];
            $data['time']           = time();
            $data['words']           = I("post.words");

            $id = D("SensitiveWords")->addWords($data);

            //清除APP敏感词缓存
			$redis_logic = D('Home/Logic/RedisLogic');
			$redis_logic->del('Cache:ZXS:sensitive');

            $this->ajaxReturn(array('data'=>$id,'info'=>'添加成功！','status'=>1));
        }else{
            $this->ajaxReturn(array('data'=>'','info'=>'添加失败，请重试！','status'=>0));
        }
    }

    //删除敏感词（按照需求说明，此处为物理删除，未保留备份）
    public function delWord()
    {
        $id = I("post.id");
        if(!empty($id)){
            $where['id'] = array('IN',$id);
            
            $result = D("SensitiveWords")->delWord($where);
			//清除APP敏感词缓存
			$redis_logic = D('Home/Logic/RedisLogic');
			$redis_logic->del('Cache:ZXS:sensitive');
            $this->ajaxReturn(array('data'=>$result,'info'=>'删除成功！','status'=>1));
        }else{
            $this->ajaxReturn(array('data'=>'','info'=>'删除失败，请重试！','status'=>0));
        }
    }

    //编辑敏感词
    public function editWord()
    {
        $id = I("post.id");
        if(!empty($id)){
            $where['id'] = $id;

            $word   = I("post.word");
            $type   = I("post.type");
            $status = I("post.status");

            if($status == ''){
                $data['status'] = 0;//编辑完成，默认启用
            }else{
                $data['status'] = $status;
            }

            if(!empty($word)){
                $data['word']               = $word;
            }
            if(!empty($type)){
                $data['type']        = $type;
            }

            $id = D("SensitiveWords")->editWord($where,$data);
			//清除APP敏感词缓存
			$redis_logic = D('Home/Logic/RedisLogic');
			$redis_logic->del('Cache:ZXS:sensitive');

            $this->ajaxReturn(array('data'=>$status,'info'=>'编辑成功！','status'=>1));
        }else{
            $this->ajaxReturn(array('data'=>'','info'=>'编辑失败，请重试！','status'=>0));
        }
    }


    //批量编辑敏感词
    public function editallwords()
    {
        $id = I("post.id");
        if(!empty($id)){
            $where['id'] = array('IN',$id);

            $type   = I("post.type");
            $status = I("post.status");//编辑完成，默认启用

            if(!empty($type)){
                $data['type']        = $type;
            }
            $data['status']          = $status;

            $id = D("SensitiveWords")->editWord($where,$data);
			//清除APP敏感词缓存
			$redis_logic = D('Home/Logic/RedisLogic');
			$redis_logic->del('Cache:ZXS:sensitive');
            $this->ajaxReturn(array('data'=>$id,'info'=>'编辑成功！','status'=>1));
        }else{
            $this->ajaxReturn(array('data'=>'','info'=>'编辑失败，请重试！','status'=>0));
        }
    }

    /*
    * 批量导入敏感词
    *
    */
    public function uploadExcel(){

        $ex = $_FILES['excel'];
        $filename = TEMP_PATH.time().substr($ex['name'],stripos($ex['name'],'.'));
        move_uploaded_file($ex['tmp_name'],$filename);
        $excel = import_excel($filename);

        //逐行导入数据  word   type  creator  num   status time
        $nuin = '';
        //获取所有的分类
        $types = D("SensitiveWords")->getAllTypes(1);
        foreach ($types as $k => $v) {
            $key = '"'.$v['name'].'"';
            $type_arr[$key] = $v['id'];
        }
        $count = 0;
        $str = '';
        foreach ($excel as $k => $v) {
            if(empty($v) || $k == 0){
                continue;
            }

            $data['word'] = $v[0];
            $type = '"'.$v[1].'"';
            $data['type'] = $type_arr[$type];
            $data['creator'] = $_SESSION['uc_userinfo']['name'];
            $data['num'] = 0;
            $data['status'] = 0;
            $data['time'] = time();

            
            if($data['type']){
                M('sensitive_words')->add($data);
                $count++;
            }else{
                $str .= $data['word'].',';
            }
        }
		//清除APP敏感词缓存
		$redis_logic = D('Home/Logic/RedisLogic');
		$redis_logic->del('Cache:ZXS:sensitive');

        $this->ajaxReturn(array("data"=> '',"info"=>"导入成功".$count."条。由于系统中没有对应的敏感词类别，以下敏感词未被成功导入：".$str,"status"=>1));
    }

    /*
     * 敏感词查询结果下载
     * 
    */
    public function downLoadModule(){
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        // 设置缓存方式，减少对内存的占用
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();
        //设置表头

        //岗位城市权限
        $title = [
            '编号',
            '敏感词',
            '敏感词类别',
            '创建者',
            '创建时间',
            '启用状态',
            '屏蔽次数'
        ];
          
        //生成表头
        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }
        //有data=1 时，填写表格内容
        
        //设置表内容
        $j = 1;
        $info = S("Cache:Sensitivewords:list");
        $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $phpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $phpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $phpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $phpExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $phpExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        foreach($info as $key => $value){
            //初始化$i
            $i = 0;
            //'编号',
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['id']);
            //'敏感词',
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['word']);
            //'敏感词类别',
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['typename']);
            //'创建者',
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['creator']);
            //'创建时间',
            $time = date("Y-m-d H:i:s",$value['time']);
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$time);
            //'启用状态',
            if($value['status'] == 0){
                $status = "已启用";
            }else{
                $status = "未启用";
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$status);
            //'屏蔽次数',
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['num']);

            $j++;
        }
        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="敏感词.xlsx"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }


    //-------------------------------敏感词分类管理页-------------------------------------
    //敏感词分类管理
    public function types(){
        //查询所有的敏感词分类
        $types = D("SensitiveWords")->getAllTypes();

        $this->assign("list",$types);
        $this->display();
    }

    //添加分类
    public function addType()
    {
        if(!empty($_POST['name']) && !empty($_POST['description'])){
            $data['name']           = I("post.name");
            $data['description']    = I("post.description");
            $data['creator']        = $_SESSION['uc_userinfo']['name'];
            $data['time']           = time();
            $data['status']         = 0;

            $id = D("SensitiveWords")->addType($data);

            $this->ajaxReturn(array('data'=>$id,'info'=>'添加成功！','status'=>1));
        }else{
            $this->ajaxReturn(array('data'=>'','info'=>'添加失败，请重试！','status'=>0));
        }
    }

    //编辑分类
    public function editType()
    {
        $id = I("post.id");
        if(!empty($id)){
            $where['id'] = $id;
            $data['creator']        = $_SESSION['uc_userinfo']['name'];
            $data['time']           = time();

            $name = I("post.name");
            $description = I("post.description");
            $status = I("post.status");
            $status = intval($status);

            if(!empty($name)){
                $data['name']               = $name;
            }
            if(!empty($description)){
                $data['description']        = $description;
            }
            if( $status === 0){
                $data['status']             = 0;
            }else{
                $data['status']             = 1;
            }
            

            $id = D("SensitiveWords")->editType($where,$data);

            $this->ajaxReturn(array('data'=>$data,'info'=>'编辑成功！','status'=>1));
        }else{
            $this->ajaxReturn(array('data'=>'','info'=>'编辑失败，请重试！','status'=>0));
        }
    }

    //删除分类（按照需求说明，此处为物理删除，未保留备份）
    public function delType()
    {
        $id = I("post.id");
        if(!empty($id)){
            $where['id'] = $id;
            
            $result = D("SensitiveWords")->delType($where);

            $this->ajaxReturn(array('data'=>$result,'info'=>'删除成功！','status'=>1));
        }else{
            $this->ajaxReturn(array('data'=>'','info'=>'删除失败，请重试！','status'=>0));
        }
    }


    

    /**
     * 获取评论列表
     * @param  array            $map             查询条件
     * @param  string           $page            页码
     * @param  string           $count           分页长度 
     * @return array            $result          修改结果
     */
    private function getAllWords($map,$pageIndex,$pageCount)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 20 : intval($pageCount);

        $count = D("SensitiveWords")->getAllWordsCount($map);//原查询有默认条件on=2
        //$result['list'] = D('Comment')->getCommentList($map,($pageIndex-1)*$pageCount,$pageCount);
        $result['list'] = D("SensitiveWords")->getAllWords($map,($pageIndex-1)*$pageCount,$pageCount);
        //var_dump(M()->getLastSql());

        if($count > $pageCount){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        $result['totalnum'] = $count;
        //var_dump($result);
        return $result;
    }

   
}


