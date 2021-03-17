<?php
namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\CompanyLiangfangLogicModel;
use Home\Model\Logic\CompanyConsultLogicModel;
use Home\Model\Logic\RobOrdersLogicModel;
use Home\Model\Logic\ThematicWordsLogicModel;
use Home\Model\Service\ElasticsearchServiceModel;

class ExportController extends HomeBaseController{
    public function index(){
        $this->_error("访问错误,没有该文件！");
    }
    /**
     * 导出页面
     * @return [type] [description]
     */
    public function download(){
        if($_POST){
          import('Library.Org.Phpexcel.PHPExcel',"",".php");
          import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
          try {
            $data = json_decode($_POST["data"],true);
            $title = $_POST["title"];
            // 设置缓存方式，减少对内存的占用
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
            $cacheSettings = array ( 'cacheTime' => 300 );
            \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
            $phpExcel = new \PHPExcel();
            //设置表示属性
            //创建人
           // $phpExcel->getProperties()->setCreator($_SESSION["adminuser"]);
            //标题
            //$phpExcel->getProperties()->setTitle($title);
            $j = 1;
            foreach ($data as $key => $value) {
              $i = 65;//字母A的ASC值 65-95 A-Z 的ASC值
              $n = 65;
              $p = 65;
              foreach ($value as $k => $val) {
                  if(count($val) == 0){
                    continue;
                  }
                    $char = strtoupper(chr($i));
                    if ($char > "Z") {
                        $char = strtoupper(chr($n)).strtoupper(chr($p));
                        if ($char > "Z") {
                             $n++;
                        }
                        $p++;
                    }
                    //每行的单元格
                    $num = $char.$j;

                    //单元格内容，如果长度大约15位，并且类型是数字则指定类型为字符串，避免科学计数法导致数字丢失
                    if (strlen($val["text"]) > 15 && is_numeric($val["text"])) {
                        $phpExcel->getActiveSheet()->setCellValueExplicit($num,$val["text"]);
                    } else {
                        $phpExcel->getActiveSheet()->setCellValue($num,$val["text"]);
                    }

                   if (isset($val["col_span"])) {
                      $char = strtoupper(chr($i+$val["col_span"]-1));
                      $num = $num.":".$char.$j;
                      $phpExcel->getActiveSheet()->mergeCells($num);
                      $i = $i+$val["col_span"];
                    } else {
                        $i++;
                    }

                    //设置字体的水平居中
                    $phpExcel->getActiveSheet()->getStyle($num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

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
            header('Content-Disposition:attachment;filename="'.$title.'.xls"');
            header("Content-Transfer-Encoding:binary");
            $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
            $writer->save('php://output');
          }catch (Exception $e){
            if($_SESSION["uid"] == 37){
              var_dump($e);
            }
          }
        }
    }
    /**
     * 读取城市系数
     * @return [type] [description]
     */
    public function loadcitycoefficient()
    {
        if ($_POST) {
            if (I("post.date") != date("Y-m")) {
                $this->ajaxReturn(array("info"=>"只能覆盖当月的数据","status"=>0));
                die();
            }
            $fileType = explode(".", $_FILES["fileup"]["name"]);
            $ext = $fileType[1];
            $filePath = dirname(dirname(dirname(__FILE__)))."/upload/tmp/";
            if(!is_dir($filePath)){
                mkdir($filePath,0777);
            }
            $path = $_FILES["fileup"]["tmp_name"];
            $filePath = $filePath.time().".".$ext;
            import("Library.Org.Phpexcel.PHPExcel","",".php");
            import("Library.Org.Phpexcel.PHPExcel.IOFactory","",".php");
            if($ext == "xls"){
                import("Library.Org.Phpexcel.PHPExcel.Reader.Excel5","",".php");
                $objReader = \PHPExcel_IOFactory::createReader('Excel5');
            }elseif($ext == "xlsx"){
                import("Library.Org.Phpexcel.PHPExcel.Reader.Excel2007","",".php");
                $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
            }
            $objPHPExcel = $objReader->load($path);
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
            //删除所有的当月数据
            D("CityCoefficient")->delCity(I("post.date"));
            //获取所有的开站城市
            $result = D("Quyu")->getAllQuyuOnly();
            foreach ($result as $key => $value) {
                $quyu[$value["cname"]] = $value;
            }
            foreach ($data as $key => $value) {
                if (array_key_exists($value[0], $quyu)) {
                    $saveData[] = array(
                        "city_id" => $quyu[$value[0]]["cid"],
                        "date" => I("post.date"),
                        "city_name" => $quyu[$value[0]]["cname"],
                        "day" => $value[1],
                        "night" => $value[2],
                        "uid" => $this->User["id"],
                        "uname" => $this->User["name"],
                        "time" => time()
                    );
                }
            }
            D("CityCoefficient")->addCity($saveData);
            $this->ajaxReturn(array("info"=>"","status"=>1));
        }
    }
     /**
     * 下载城市列表
     * @return [type] [description]
     */
    public function downloadcity()
    {
        //获取全部城市
        $quyu = D("Quyu")->getAllQuyuOnly();
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        // 设置缓存方式，减少对内存的占用
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();
        $j = 1;
        foreach ($quyu as $key => $value) {
            $i = 65;
            $char = strtoupper(chr($i));
            //每行的单元格
            $num = $char.$j;
            if ($j == 1) {
              $phpExcel->getActiveSheet()->setCellValue($num,"城市");
            }else{
              //单元格内容
              $phpExcel->getActiveSheet()->setCellValue($num,$value["cname"]);
            }
            $j ++;
        }
        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="所有城市.xls"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
    }
    /**
     * 读取excel
     * @param  [type] $path [description]
     * @return [type]       [description]
     */
    public function loadFile($path,$ext){
        import("Library.ORG.phpexcel.PHPExcel.IOFactory","",".php");
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        if($ext == "xls"){
            import("Library.ORG.Phpexcel.PHPExcel.Reader.Excel5","",".php");
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        }elseif($ext == "xlsx"){
            import("Library.ORG.Phpexcel.PHPExcel.Reader.Excel2007","",".php");
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        }
        $objPHPExcel = $objReader->load($path);
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();//总列数
        $highestColumn = $sheet->getHighestColumn(); //取得总列数
        $data = array();
        for($j = 2; $j <= $highestRow; $j++) {
            $str = "";
            for($k = 'A'; $k <= $highestColumn; $k++) {
                $str .= $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue().'|*|';//读取单元格
            }
            $data[] = array_filter(explode("|*|",$str));
        }
        $area = D("Area");
        //查询城市信息
        foreach ($data as $key => $value) {
            $cityName = $value[3];
            $city = $area->getCityInfoByName($cityName,1);
            //查询城市信息，如果城市存在就替换城市的名称改为城市ID，如果不存在就删除该记录，防止出现城市为空的数据
            if(count($city) > 0){
                $data[$key][4] = $city[0]["cid"];
            }else{
                unset($data[$key]);
            }
        }
        //添加其他数据信息
        foreach ($data as $key => $value) {
            $data[$key] = array(
                "link_name" => $value[0],
                "link_url" => $value[1],
                "link_page" => empty($value[2])?"":$value[2],
                "cname" => $value[3],
                "cs" => $value[4],
                "addtime" => date("Y-m-d H:i:s"),
                "show_class" => 1
            );
        }
        //删除本地的文件
        if(file_exists($path)){
            unlink($path);
        }
        return $data;
    }
    /**
     * 批量导入长尾词
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function aggregation()
    {
        $fileType = explode(".", $_FILES["fileup"]["name"]);
        $ext = $fileType[1];
        $filePath = dirname(dirname(dirname(__FILE__)))."/upload/tmp/";
        if(!is_dir($filePath)){
            mkdir($filePath,0777);
        }
        $path = $_FILES["fileup"]["tmp_name"];
        $filePath = $filePath.time().".".$ext;
        import("Library.Org.Phpexcel.PHPExcel","",".php");
        import("Library.Org.Phpexcel.PHPExcel.IOFactory","",".php");
        if($ext == "xls"){
            import("Library.Org.Phpexcel.PHPExcel.Reader.Excel5","",".php");
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        }elseif($ext == "xlsx"){
            import("Library.Org.Phpexcel.PHPExcel.Reader.Excel2007","",".php");
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        }
        $objPHPExcel = $objReader->load($path);
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();//总列数
        $highestColumn = $sheet->getHighestColumn(); //取得总列数
        $data = array();
        for($j=1; $j <= $highestRow; $j++) {
            $str = "";
            for($k = 'A'; $k <= $highestColumn; $k++) {
                $str .= $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue().'|*|';//读取单元格
            }
            $data[] = array_filter(explode("|*|",$str));
        }
        //添加长尾词
        foreach ($data as $key => $value) {
            if (mb_strlen($value[0]) > 1) {
                $all[] = array(
                    "words" => $value[0],
                    "time" => time()
                );
            }
        }
        //插入数据
        $id = D("LongTailKeywords")->addAllWords($all);
        if ($id !== false) {
            foreach ($data as $key => $value) {
                $words = array_filter(explode(",", str_replace("，",",",$value[1])));
                foreach ($words as $k => $val) {
                    $sub[] = array(
                        "long_tail_id" => $id,
                        "words" => $val,
                        "time" => time()
                    );
                }
                $id ++ ;
            }
            //插入数据
            D("LongTailKeywords")->addAllChildWords($sub);
        }
        $this->ajaxReturn(array("info"=>"","status"=>1));
    }
    /**
     * 下载订单城市信息
     * @return [type] [description]
     */
    public function downordercity()
    {
        //查询所有有会员城市信息
        $vipCity = D("User")->getVipCity();
        //查询现有城市数据
        $cityInfo = D("OrderCityInfo")->getCityInfoList();
        $data = array();
        if (count($cityInfo) > 0) {
            //合并城市信息
            foreach ($vipCity as $key => $value) {
                foreach ($cityInfo as $k => $val) {
                    if ($val["city_id"] == $value["cid"]) {
                        $data[] = array(
                            "cid" => $value["cid"],
                            "cname" => $value["cname"],
                            "half_price_min" => $val["half_price_min"],
                            "half_price_max" => $val["half_price_max"],
                            "price_min" => $val["price_min"],
                            "price_max" => $val["price_max"],
                            "description" => $val["description"],
                            "description1" => $val["description1"]
                        );
                        unset($vipCity[$key]);
                        unset($cityInfo[$k]);
                        break;
                    }
                }
            }
            //如果cityInfo还有剩余城市，则表示该城市当前没有会员了
            //为了减少重复的操作，剩余城市不做删除
            if (count($cityInfo) > 0) {
                foreach ($cityInfo as $key => $val) {
                   $sub =  $data[] = array(
                        "cid" => $val["city_id"],
                        "cname" => $val["city_name"],
                        "half_price_min" => $val["half_price_min"],
                        "half_price_max" => $val["half_price_max"],
                        "price_min" => $val["price_min"],
                        "price_max" => $val["price_max"],
                        "description" => $val["description"],
                        "description1" => $val["description1"],
                        "isMark" => 1
                    );
                }
            }
        }
        //如果还有剩余城市，表示有新的城市
        if (count($vipCity) > 0) {
            foreach ($vipCity as $key => $value) {
                $sub = array(
                    "cid" => $value["cid"],
                    "cname" => $value["cname"]
                );
                array_push($data, $sub);
            }
        }
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        $title = "城市信息";
        // 设置缓存方式，减少对内存的占用
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();
        //设置第一行内容
        //合并单元格
        $phpExcel->getActiveSheet()->mergeCells('A1:G1');
        $phpExcel->getActiveSheet()->setCellValue("A1","城市价格表及注意事项");
        $phpExcel->getActiveSheet()->getStyle("A1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         //设置第二行内容
        $phpExcel->getActiveSheet()->setCellValue("A2","城市");
        $phpExcel->getActiveSheet()->getStyle("A2")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpExcel->getActiveSheet()->setCellValue("B2","半包最低价");
        $phpExcel->getActiveSheet()->getStyle("B2")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpExcel->getActiveSheet()->setCellValue("C2","半包最高价");
        $phpExcel->getActiveSheet()->getStyle("C2")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpExcel->getActiveSheet()->setCellValue("D2","全包最低价");
        $phpExcel->getActiveSheet()->getStyle("D2")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         $phpExcel->getActiveSheet()->setCellValue("E2","全包最高价");
        $phpExcel->getActiveSheet()->getStyle("E2")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpExcel->getActiveSheet()->setCellValue("F2","拨打注意事项");
        $phpExcel->getActiveSheet()->getStyle("F2")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $phpExcel->getActiveSheet()->setCellValue("G2","对接注意事项");
        $phpExcel->getActiveSheet()->getStyle("G2")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $j = 3;
        foreach ($data as $key => $value) {
            $phpExcel->getActiveSheet()->setCellValue("A".$j,$value["cname"]);
            $phpExcel->getActiveSheet()->setCellValue("B".$j,$value["half_price_min"]);
            $phpExcel->getActiveSheet()->setCellValue("C".$j,$value["half_price_max"]);
            $phpExcel->getActiveSheet()->setCellValue("D".$j,$value["price_min"]);
            $phpExcel->getActiveSheet()->setCellValue("E".$j,$value["price_max"]);
            $phpExcel->getActiveSheet()->setCellValue("F".$j,$value["description"]);
            $phpExcel->getActiveSheet()->setCellValue("G".$j,$value["description1"]);
            $phpExcel->getActiveSheet()->getStyle("A".$j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $phpExcel->getActiveSheet()->getStyle("B".$j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $phpExcel->getActiveSheet()->getStyle("C".$j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $phpExcel->getActiveSheet()->getStyle("D".$j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $phpExcel->getActiveSheet()->getStyle("E".$j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $phpExcel->getActiveSheet()->getStyle("F".$j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $phpExcel->getActiveSheet()->getStyle("G".$j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            if ($value["isMark"]) {
                $phpExcel->getActiveSheet()->getStyle("A".$j)->getFont()->getColor()->setRGB("#ff0000");
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
        header('Content-Disposition:attachment;filename="'.$title.'.xlsx"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
    }
    /**
     * 上传城市信息
     * @return [type] [description]
     */
    public function upordercityinfo()
    {
        $fileType = explode(".", $_FILES["fileup"]["name"]);
        $ext = $fileType[1];
        $filePath = dirname(dirname(dirname(__FILE__)))."/upload/tmp/";
        if(!is_dir($filePath)){
            mkdir($filePath,0777);
        }
        $path = $_FILES["fileup"]["tmp_name"];
        $filePath = $filePath.time().".".$ext;
        import("Library.Org.Phpexcel.PHPExcel","",".php");
        import("Library.Org.Phpexcel.PHPExcel.IOFactory","",".php");
        if($ext == "xls"){
            import("Library.Org.Phpexcel.PHPExcel.Reader.Excel5","",".php");
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        }elseif($ext == "xlsx"){
            import("Library.Org.Phpexcel.PHPExcel.Reader.Excel2007","",".php");
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        }
        $objPHPExcel = $objReader->load($path);
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
        //获取全部城市信息
        $result = D("Quyu")->getAllQuyuOnly();
        foreach ($result as $key => $value) {
            $citys[$value["cname"]] = $value;
        }
        foreach ($data as $key => $value) {
            if (count($citys[$value[0]]) > 0) {
                $all[] = array(
                    "city_id" => $citys[$value[0]]["cid"],
                    "city_name" => $citys[$value[0]]["cname"],
                    "half_price_min" => $value[1],
                    "half_price_max" => $value[2],
                    "price_min" => $value[3],
                    "price_max" => $value[4],
                    "description" => $value[5],
                    "description1" => $value[6]
                );
            }
        }
        //删除所有数据
        D("OrderCityInfo")->delAllInfo();
        //添加数据
        $i = D("OrderCityInfo")->addAllInfo($all);
        if ($i !== false) {
            $this->ajaxReturn(array("info"=>"","status"=>1));
        }
        $this->ajaxReturn(array("info"=>"上传失败！","status"=>0));
    }
    /**
     * 批量添加标签
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function tags()
    {
        $fileType = explode(".", $_FILES["fileup"]["name"]);
        $ext = $fileType[1];
        $filePath = dirname(dirname(dirname(__FILE__)))."/upload/tmp/";
        if(!is_dir($filePath)){
            mkdir($filePath,0777);
        }
        $path = $_FILES["fileup"]["tmp_name"];
        $filePath = $filePath.time().".".$ext;
        import("Library.Org.Phpexcel.PHPExcel","",".php");
        import("Library.Org.Phpexcel.PHPExcel.IOFactory","",".php");
        if($ext == "xls"){
            import("Library.Org.Phpexcel.PHPExcel.Reader.Excel5","",".php");
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        }elseif($ext == "xlsx"){
            import("Library.Org.Phpexcel.PHPExcel.Reader.Excel2007","",".php");
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        }
        $objPHPExcel = $objReader->load($path);
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();//总列数
        $highestColumn = $sheet->getHighestColumn(); //取得总列数
        $data = array();
        for($j=1; $j <= $highestRow; $j++) {
            $str = "";
            for($k = 'A'; $k <= $highestColumn; $k++) {
                $str .= $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue().'|*|';//读取单元格
            }
            $data[] = array_filter(explode("|*|",$str));
        }
        foreach ($data as $key => $value) {
            $all[] = array(
                "name" => $value[0],
                "time" => time()
            );
        }
        $i = D("Tags")->addAll($all);
        if ($i !== false) {
            $this->ajaxReturn(array("info"=>"","status"=>1));
        }
        $this->ajaxReturn(array("info"=>"上传失败！","status"=>0));
    }
    /**
     * 每月访客按城市分析
     * @return [type] [description]
     */
    public function downLoadCityOrder()
    {
        if($_POST){
            import('Library.Org.Phpexcel.PHPExcel',"",".php");
            import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
            $timeScale = array("02"=>"1","03"=>"2","04"=>"3","05"=>"4","06"=>"5","07"=>"6","08"=>"7","09"=>"8","10"=>"9","11"=>"10","12"=>"11","01"=>"12");
            $m = date("m",strtotime("-1 month"));
            $row = $timeScale[$m]+1;
            try {
                $data = json_decode($_POST["data"],true);
                $title = $_POST["title"];
                // 设置缓存方式，减少对内存的占用
                $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
                $cacheSettings = array ( 'cacheTime' => 300 );
                \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
                $phpExcel = new \PHPExcel();
                //设置表示属性
                //设置字体
                $phpExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName('宋体');
                //设置字体大小
                $phpExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
                //水平居中
                $phpExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $j = 2;
                $step = 0;
                $phpExcel->getActiveSheet()->setCellValue("A1",'每月访客按城市分析');
                $phpExcel->getActiveSheet()->mergeCells("A1:G1");
                //设置边框
                $phpExcel->getActiveSheet()->getStyle("A1:G1")->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

                                foreach ($data as $key => $value) {
                    $i = 65;//字母A的ASC值 65-95 A-Z 的ASC值
                    $n = 65;
                    $p = 65;
                    if ($j > 2 &&  ($j-3)%$row == 0 ) {
                        $step ++;
                    }
                    foreach ($value as $k => $val) {
                        if(count($val) == 0){
                           continue;
                        }
                        $char = strtoupper(chr($i));
                        if ($char > "Z") {
                           $char = strtoupper(chr($n)).strtoupper(chr($p));
                           if ($char > "Z") {
                               $n++;
                           }
                           $p++;
                        }
                        //每行的单元格
                        $num = $char.$j;
                        if ($char == "A" && $j > 2 &&  ($j-3)%$row == 0) {
                            $phpExcel->getActiveSheet()->mergeCells($num.":".$char.($step*$row+2));
                        }
                        if ($char == "C") {
                            $val["text"] = str_replace("-","",$val["text"]);
                            $phpExcel->getActiveSheet()->getStyle($num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                            $phpExcel->getActiveSheet()->getStyle($num)->getFill()->getStartColor()->setRGB("CCCCCC");
                        }
                        //单元格内容，如果长度大约15位，并且类型是数字则指定类型为字符串，避免科学计数法导致数字丢失
                        if (strlen($val["text"]) > 15 && is_numeric($val["text"])) {
                            $phpExcel->getActiveSheet()->setCellValueExplicit($num,$val["text"]);
                        } else {
                            $phpExcel->getActiveSheet()->setCellValue($num,$val["text"]);
                        }
                        $floor = ($step*$row+2);
                        if ($char == "D" && ($j >= 3 && ($floor == $j))) {
                            $phpExcel->getActiveSheet()->setCellValue($num,"=AVERAGE(D".($j-$row+1).":D".($j-1).")");
                            $phpExcel->getActiveSheet()->getStyle($num)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                        }
                        if ($char == "E" && ($j >= 3 && $floor == $j)) {
                            $phpExcel->getActiveSheet()->setCellValue($num,"=AVERAGE(E".($j-$row+1).":E".($j-1).")");
                            $phpExcel->getActiveSheet()->getStyle($num)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                        }
                        if ($char == "F" && $j >= 3) {
                            $phpExcel->getActiveSheet()->setCellValue($num,"=D".$j."/"."C".$j);
                            $phpExcel->getActiveSheet()->getStyle($num)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);//设置百分比
                        }
                        if ($char == "G" && $j >= 3) {
                            $phpExcel->getActiveSheet()->setCellValue($num,"=E".$j."/"."D".$j);
                            $phpExcel->getActiveSheet()->getStyle($num)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);//设置百分比
                        }
                        //设置字体的水平居中
                        // $phpExcel->getActiveSheet()->getStyle($num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $phpExcel->getActiveSheet()->getStyle($num)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                        //设置边框
                        $phpExcel->getActiveSheet()->getStyle($num)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
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
                header('Content-Disposition:attachment;filename="'.$title.'.xls"');
                header("Content-Transfer-Encoding:binary");
                $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
                $writer->save('php://output');
            }catch (Exception $e){
                if($_SESSION["uid"] == 37){
                  var_dump($e);
                }
            }
        }
    }

    /**
     * 页面指标分析
     * @return [type] [description]
     */
    public function exportOnlineanalysis()
    {
        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        // 设置缓存方式，减少对内存的占用
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();
        //设置表头


        $title = [
            '页面名称',
            '所属分类',
            '上线时间',
            '页面地址',
            '日均PV',
            '日均UV',
            '跳出率',
            '退出率',
            '访问时长',
            '日均发单量',
            '日均实际分单量',
            '发单转化率',
            '实际分单转化率',
            '入口页次数',
            '退出页次数'
        ];

        //生成表头
        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value);
        }

        $j = 2;
        $info = S("Cache:onlineanalysis:datatongji");
        $phpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $phpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $phpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $phpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        /*$phpExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $phpExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);*/
        foreach($info as $key => $value){
            //初始化$i
            $i = 0;
            // '页面名称',
            $num = \PHPExcel_Cell::stringFromColumnIndex(0) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['name']);
            $phpExcel->getActiveSheet()->mergeCells('A'.$j.':A'.($j+2));
            // '所属分类',
            $num = \PHPExcel_Cell::stringFromColumnIndex(1) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['cname']);
            $phpExcel->getActiveSheet()->mergeCells('B'.$j.':B'.($j+2));
            // '上线时间',
            $num = \PHPExcel_Cell::stringFromColumnIndex(2) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['online_time']);
            $phpExcel->getActiveSheet()->mergeCells('C'.$j.':C'.($j+2));
            // '页面地址',
            $num = \PHPExcel_Cell::stringFromColumnIndex(3) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['url']);
            $phpExcel->getActiveSheet()->mergeCells('D'.$j.':D'.($j+2));


            // '日均PV',
            if(empty($value['predata']['pv'])){
                $value['predata']['pv'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(4) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['predata']['pv']);
            // '日均UV',
            if(empty($value['predata']['uv'])){
                $value['predata']['uv'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(5) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['predata']['uv']);
            // '跳出率',
            if(empty($value['predata']['tiaochu'])){
                $value['predata']['tiaochu'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(6) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)($value['predata']['tiaochu'].'%'));
            // '退出率',
            if(empty($value['predata']['tuichu'])){
                $value['predata']['tuichu'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(7) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)($value['predata']['tuichu'].'%'));
            // '访问时长',
            if(empty($value['predata']['shichang'])){
                $value['predata']['shichang'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(8) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['predata']['shichang']);
            // '日均发单量',
            if(empty($value['predata']['fdl'])){
                $value['predata']['fdl'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(9) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['predata']['fdl']);
            // '日均实际分单量',
            if(empty($value['predata']['rfdl'])){
                $value['predata']['rfdl'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(10) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['predata']['rfdl']);
            // '发单转化率',
            if(empty($value['predata']['fdzhl'])){
                $value['predata']['fdzhl'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(11) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)($value['predata']['fdzhl'].'%'));
            // '实际分单转化率',
            if(empty($value['predata']['sjzhl'])){
                $value['predata']['sjzhl'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(12) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)($value['predata']['sjzhl'].'%'));
            // '入口页次数',
            if(empty($value['predata']['rukou'])){
                $value['predata']['rukou'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(13) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['predata']['rukou']);
            // '退出页次数'
            if(empty($value['predata']['tcycx'])){
                $value['predata']['tcycx'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(14) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['predata']['tcycx']);
            $j++;

            // '日均PV',
            if(empty($value['lastdata']['pv'])){
                $value['lastdata']['pv'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(4) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lastdata']['pv']);
            // '日均UV',
            if(empty($value['lastdata']['uv'])){
                $value['lastdata']['uv'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(5) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lastdata']['uv']);
            // '跳出率',
            if(empty($value['lastdata']['tiaochu'])){
                $value['lastdata']['tiaochu'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(6) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)($value['lastdata']['tiaochu'].'%'));
            // '退出率',
            if(empty($value['lastdata']['tuichu'])){
                $value['lastdata']['tuichu'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(7) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)($value['lastdata']['tuichu'].'%'));
            // '访问时长',
            if(empty($value['lastdata']['shichang'])){
                $value['lastdata']['shichang'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(8) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lastdata']['shichang']);
            // '日均发单量',
            if(empty($value['lastdata']['fdl'])){
                $value['lastdata']['fdl'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(9) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lastdata']['fdl']);
            // '日均实际分单量',
            if(empty($value['lastdata']['rfdl'])){
                $value['lastdata']['rfdl'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(10) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lastdata']['rfdl']);
            // '发单转化率',
            if(empty($value['lastdata']['fdzhl'])){
                $value['lastdata']['fdzhl'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(11) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)($value['lastdata']['fdzhl'].'%'));
            // '实际分单转化率',
            if(empty($value['lastdata']['sjzhl'])){
                $value['lastdata']['sjzhl'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(12) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)($value['lastdata']['sjzhl'].'%'));
            // '入口页次数',
            if(empty($value['lastdata']['rukou'])){
                $value['lastdata']['rukou'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(13) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lastdata']['rukou']);
            // '退出页次数'
            if(empty($value['lastdata']['tcycx'])){
                $value['lastdata']['tcycx'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(14) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['lastdata']['tcycx']);
            $j++;

            // '日均PV',
            if(empty($value['p_pv'])){
                $value['p_pv'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(4) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['p_pv']);
            // '日均UV',
            if(empty($value['p_uv'])){
                $value['p_uv'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(5) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['p_uv']);
            // '跳出率',
            if(empty($value['p_tiaochu'])){
                $value['p_tiaochu'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(6) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)($value['p_tiaochu'].'%'));
            // '退出率',
            if(empty($value['p_tuichu'])){
                $value['p_tuichu'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(7) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)($value['p_tuichu'].'%'));
            // '访问时长',
            if(empty($value['p_shichang'])){
                $value['p_shichang'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(8) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['p_shichang']);
            // '日均发单量',
            if(empty($value['p_fdl'])){
                $value['p_fdl'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(9) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['p_fdl']);
            // '日均实际分单量',
            if(empty($value['p_rfdl'])){
                $value['p_rfdl'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(10) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['p_rfdl']);
            // '发单转化率',
            if(empty($value['p_fdzhl'])){
                $value['p_fdzhl'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(11) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)($value['p_fdzhl'].'%'));
            // '实际分单转化率',
            if(empty($value['p_sjzhl'])){
                $value['p_sjzhl'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(12) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)($value['p_sjzhl'].'%'));
            // '入口页次数',
            if(empty($value['p_rukou'])){
                $value['p_rukou'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(13) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['p_rukou']);
            // '退出页次数'
            if(empty($value['p_tcycx'])){
                $value['p_tcycx'] = 0;
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex(14) . '' . ($j);
            $phpExcel->getActiveSheet()->setCellValue($num,(string)$value['p_tcycx']);

            $phpExcel->getActiveSheet()->getStyle('A2:D'.$j)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                )
            );
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
        header('Content-Disposition:attachment;filename="页面上线分析.xlsx"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }

    public function downLoadQcConclusionStat()
    {
        $start = mktime(0,0,0,date("m"),1,date("Y"));
        $end = mktime(23,59,59,date("m"),date("t"),date("Y"));

        if (I("post.start_time") != "" && I("post.end_time") != "") {
            $start =  strtotime(I("post.start_time"));
            $end = strtotime(I("post.end_time"))+86400-1;
        }

        if ( (($end - $start)/86400) > 31  ) {
            die();
        }

        $result = D("QcInfo")->getQcConclusionErrorDetailList(I("post.order_id"), $start,$end,  I("post.op_uid"), I("post.kf_manager"), I("post.kf_group"),  I("post.kf_id"), 0, I("post.docking_group"), I("post.docking_id"), I("post.remark"),  I("post.error"), I("post.type"), I("post.item"),I("post.qctype"), null, null);

        //质检时间  订单编号    质检类型    质检员 订单类型    审核客服    对接客服    客服团长    备注  问题项
        $list[] = array(
            "time" => '质检时间',
            "order_id" => '订单编号',
            "type" => '质检类型',
            "op_name" => '质检员',
            "type_fw" => '订单类型',
            "kf" => '审核客服',
            "dockging" => '对接客服',
            "group" => '客服团长',
            "remark" => '备注',
            "remark2" => '正确操作备注',
            "items" => '问题项'
        );
        foreach ($result as $key => $value) {
            $sub = array(
                "time" => $value["time"],
                "order_id" => $value["order_id"],
                "type" => $value["type"] == 1?"抽听":"回听",
                "op_name" => $value["op_name"],
                "type_fw" => "",
                "kf" => $value["kf_name"],
                "dockging" => $value["docking_name"],
                "group" => $value["group_name"],
                "remark" => $value["remark"],
                "remark2" => $value["remark2"]
            );
            switch ($value["type_fw"]) {
                case 1:
                    $sub["type_fw"] = "分单";
                    break;
                case 2:
                    $sub["type_fw"] = "赠单";
                    break;
                default:
                    $sub["type_fw"] = "无效单";
                    break;
            }

            $list[$value["order_id"]] = $sub;
            $list[$value["order_id"]]["items"] .= $value["items"] ;
        }

        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        // 设置缓存方式，减少对内存的占用
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();
        $j = 1;
        foreach ($list as $key => $value) {
            $i = 65;//字母A的ASC值 65-95 A-Z 的ASC值
            $n = 65;
            $p = 65;
            foreach ($value as $k => $val) {
                $char = strtoupper(chr($i));
                if ($char > "Z") {
                    $char = strtoupper(chr($n)).strtoupper(chr($p));
                    if ($char > "Z") {
                        $n++;
                    }
                    $p++;
                }
                //每行的单元格
                $num = $char.$j;
                //单元格内容，如果长度大约15位，并且类型是数字则指定类型为字符串，避免科学计数法导致数字丢失
                if (strlen($val) > 15 && is_numeric($val)) {
                    $phpExcel->getActiveSheet()->setCellValueExplicit($num,$val);
                } else {
                    $phpExcel->getActiveSheet()->setCellValue($num,$val);
                }
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
        header('Content-Disposition:attachment;filename=质检结论错误明细统计.xls');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
    }

    /*
     * 搜索关键词导出
     */
    public function exportSearchWords()
    {
        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            $writer = new \XLSXWriter();
            //写入日期

            $writer->writeSheetRow('Sheet1', array('日期：'.$_GET['time']));
            //标题
            $herder = array(
                '序号',
                '关键词',
                '搜索模块',
                '搜索次数'
            );
            $wArr = array_values($herder);
            $writer->writeSheetRow('Sheet1', $herder);

            //数据
            $info = S("Cache:KeyWordsInfo:searchKeyWords");
            $jsq = 1;
            $module = [
                1 => '装修公司',
                2 => '家居美图',
                3 => '案例',
                4 => '问答',
                5 => '百科',
                6 => '文章',
                7 => '视频'
            ];
            foreach ($info as $key => $value) {
                $v = array(
                    $jsq,
                    urldecode($value['word']),
                    $module[$value['module']],
                    $value['num']
                );
                $wArr = array_values($v);
                $writer->writeSheetRow('Sheet1', $v);
                $jsq ++;
            }
            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="搜索词统计.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        }catch (Exception $e){
            if($_SESSION["uc_userinfo"]["uid"] == 1){
                var_dump($e);
            }
        }
        exit();
    }

    public function downloadwenda()
    {
        if ($_POST) {
            ini_set('memory_limit','512M');
            //获取问题数
            $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
            $monthEnd = mktime(23,59,59,date("m"),date("d"),date("Y"))+1;
            if (I("post.begin") != "" && I("post.begin")  != "" ) {
                $monthStart = strtotime(I("post.begin"));
                $monthEnd = strtotime(I("post.end"))+86400;
            }
            //获取统计数据
            $result = D("Adminask")->getAskList($monthStart,$monthEnd,I("post.title"),I("post.type"),I("post.sub_type"));

            //编号    标题  发布时间    发布人 分类  IP量 发单量 实际分单量
            $data[0] = array(
                "id" => '编号',
                "title" => '标题',
                "post_time" => '发布时间',
                "username" => '发布人',
                "type" => '分类',
                "url" => 'pc端URL',
                "mobile" => "移动端URL",
                "uv" => 'IP量',
                "order_count" => '发单量',
                "real_order_count" => '实际分单量'
            );


            foreach ($result as $key => $value) {
                $data[$value["id"]] = array(
                    "id" => $value["id"],
                    "title" => $value["title"],
                    "post_time" =>date("Y-m-d H:i:s",$value["post_time"]),
                    "username" => $value["username"],
                    "type" => $value["category_name"]."-".$value["sub_category_name"],
                    "url" => "http://".C('QZ_YUMINGWWW')."/wenda/x".$value["id"].".html",
                    "mobile" => "http://".C('MOBILE_DONAMES')."/wenda/x".$value["id"].".html",
                     "uv" => 0,
                    "order_count" => 0,
                    "real_order_count" => 0
                );
            }

            //获取页面采集数据
            $result = D("MarketSummary")->getWenDaList($monthStart,$monthEnd);
            $reg = '/\d+/';
            foreach ($result as $key => $value) {
                preg_match($reg, $value["url"],$m);
                if (array_key_exists($m[0],$data)) {
                    $data[$m[0]]["order_count"] += $value["order_count"];
                    $data[$m[0]]["real_order_count"] += $value["real_order_count"];
                    $data[$m[0]]["uv"] += $value["uv"];
                }
            }

            import('Library.Org.Phpexcel.PHPExcel',"",".php");
            import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
            // 设置缓存方式，减少对内存的占用
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
            $cacheSettings = array ( 'cacheTime' => 300 );
            \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
            $phpExcel = new \PHPExcel();
            $j = 1;
            foreach ($data as $key => $value) {
                $i = 65;//字母A的ASC值 65-95 A-Z 的ASC值
                $n = 65;
                $p = 65;
                foreach ($value as $k => $val) {
                    $char = strtoupper(chr($i));
                    if ($char > "Z") {
                        $char = strtoupper(chr($n)).strtoupper(chr($p));
                        if ($char > "Z") {
                            $n++;
                        }
                        $p++;
                    }
                    //每行的单元格
                    $num = $char.$j;
                    //单元格内容，如果长度大约15位，并且类型是数字则指定类型为字符串，避免科学计数法导致数字丢失
                    if (strlen($val) > 15 && is_numeric($val)) {
                        $phpExcel->getActiveSheet()->setCellValueExplicit($num,$val);
                    } else {
                        $phpExcel->getActiveSheet()->setCellValue($num,$val);
                    }
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
            header('Content-Disposition:attachment;filename=问答业绩分析.xls');
            header("Content-Transfer-Encoding:binary");
            $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
            $writer->save('php://output');

        }
    }
    /*
   * 聚合页管理导出
   */
    public function exportAggregation()
    {
        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            //ob_start();
            $writer = new \XLSXWriter();
            //标题
            $herder = array(
                '序号',
                '标签名',
                '提交日期',
                'URL'
            );
            $writer->writeSheetRow('Sheet1', $herder);
            //数据
            $info = D("LongTailKeywords")->getWordsById(1, 1, ['a.id' => ['in', I('get.data')]]);
            foreach ($info as $key => $value) {
                $url = 'https://www.qizuang.com/biaoqian/tag' . $value['id'];
                $v = array(
                    ($key + 1),
                    urldecode($value['words']),
                    date('Y-m-d', $value['time']),
                    $url,
                );
                $wArr = array_values($v);
                $writer->writeSheetRow('Sheet1', $wArr);
            }
            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="聚合标签.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        }catch (Exception $e){
            if($_SESSION["uc_userinfo"]["uid"] == 1){
                var_dump($e);
            }
        }
        exit();
    }

    /*
     *运营微信模板导出
     */
    public function exportYYWXModule()
    {
        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            $writer = new \XLSXWriter();
            //标题
            $herder = array(
                '公众号AppID',
                '公众号AppSecret',
                '公众号名称',
                '类型',
                '所属部门',
                '运营者',
                '运营者微信号',
                '运营者手机号',
                '注册邮箱',
                '注册日期',
                '认证日期'
            );
            $wArr = array_values($herder);
            $writer->writeSheetRow('Sheet1', $herder);

            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="微信公众号导入模板.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        }catch (Exception $e){
            if($_SESSION["uc_userinfo"]["uid"] == 1){
                var_dump($e);
            }
        }
        exit();
    }

    public function downloadask()
    {
            ini_set('memory_limit','512M');
            // I("get.begin"),I("get.end"),I("get.category"),I("get.sub_category"),I("get.title"),I("get.uid")
            $monthStart = mktime(0,0,0,date("m"),1,date("Y"));
            $monthEnd = mktime(23,59,59,date("m"),date("d"),date("Y"))+1;
            if (I("get.begin") != "" && I("get.begin")  != "" ) {
                $monthStart = strtotime(I("get.begin"));
                $monthEnd = strtotime(I("get.end"))+86400-1;
            }

            //主站编辑/美图编辑
            $users = D("Adminuser")->getAdminuserListByUid(array(24, 26, 69, 151));
            foreach ($users as $key => $value) {
                $ids[] = $value["id"];
            }
            $result = D("Adminbaike")->getBaikeStatList($monthStart,$monthEnd,I("get.category"),I("get.sub_category"),I("get.title"),I("get.uid"),$ids, I("get.role_id"));

            $data[0] = array(
                "id" => '编号',
                "url" => 'url',
                "title" => '标题',
                "item" => '条目',
                "post_time" => '发布时间',
                "username" => '发布人',
                "category" => '分类',
                "uv" => 'IP量',
                "order_count" => '发单量',
                "real_order_count" => '实际分单量'
            );

             foreach ($result as $key => $value) {
                 $data[$value["id"]] = array(
                     "id" => $value["id"],
                     "url" => "http://" . C('QZ_YUMINGWWW') . "/baike/" . $value["id"] . ".html",
                    "title" => $value["title"],
                    "item" => $value["item"],
                    "post_time" => date("Y-m-d H:i:s",$value["post_time"]),
                    "username" => $value["name"],
                    "category" => $value["category"]."-".$value["sub_category"],
                    "uv" => 0,
                    "order_count" => 0,
                    "real_order_count" => 0
                );
            }

            //获取发单量、分单量、IP等数据
            $result = D("MarketSummary")->getBaikeList($monthStart,$monthEnd);
            foreach ($result as $key => $value) {
                $exp = array_filter(explode("/",$value["url"]));
                $baikeId = str_replace(".html", "", $exp[2]);
                if (array_key_exists($baikeId,$data)) {
                    $data[$baikeId]["uv"] += $value["uv"];
                    $data[$baikeId]["order_count"] += $value["order_count"];
                    $data[$baikeId]["real_order_count"] += $value["real_order_count"];
                }
            }

            import('Library.Org.Phpexcel.PHPExcel',"",".php");
            import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
            // 设置缓存方式，减少对内存的占用
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
            $cacheSettings = array ( 'cacheTime' => 300 );
            \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
            $phpExcel = new \PHPExcel();
            $j = 1;
            foreach ($data as $key => $value) {
                $i = 65;//字母A的ASC值 65-95 A-Z 的ASC值
                $n = 65;
                $p = 65;
                foreach ($value as $k => $val) {
                    $char = strtoupper(chr($i));
                    if ($char > "Z") {
                        $char = strtoupper(chr($n)).strtoupper(chr($p));
                        if ($char > "Z") {
                            $n++;
                        }
                        $p++;
                    }
                    //每行的单元格
                    $num = $char.$j;
                    //单元格内容，如果长度大约15位，并且类型是数字则指定类型为字符串，避免科学计数法导致数字丢失
                    if (strlen($val) > 15 && is_numeric($val)) {
                        $phpExcel->getActiveSheet()->setCellValueExplicit($num,$val);
                    } else {
                        $phpExcel->getActiveSheet()->setCellValue($num,$val);
                    }
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
            header('Content-Disposition:attachment;filename=百科业绩分析.xls');
            header("Content-Transfer-Encoding:binary");
            $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
            $writer->save('php://output');
    }

    public function fadandetail()
    {
        $begin = strtotime(I("post.start"));
        $end = strtotime(I("post.end"))+86400;
        $result = D("OrderSourceStats")->getSrcOrderDetailsList(I("post.src"),$begin,$end,I("post.state"));
        if (count($result) > 0) {
            import('Library.Org.PHP_XLSXWriter.xlsxwriter');

            $xlsxWriter = new \XLSXWriter();
            //标题
            $header = array(
                "发单日期"=>'string',  //1
                "渠道来源"=>'string',  //2
                "订单备注"=>'string',  //3
                "城市区县"=>'string',  //4
                "手机号码"=>'string',  //5
                "手机号重复"=>'string', //6
                "IP重复"=>'string',    //7
                "订单状态"=>'string',   //8
            );
            $xlsxWriter->writeSheetHeader('Sheet1', $header);

            foreach ($result as $key => $value) {
                 if ($value["on"] == 4 && $value["type_fw"] == 1) {
                    $value["type"] = "分单";
                } elseif ($value["on"] == 4 && $value["type_fw"] == 2) {
                    $value["type"] = "赠单";
                } elseif ($value["on"] == 5) {
                    $value["type"] = "无效";
                }else {
                    $value["type"] = "其他";
                }
                $list[$key] = array(
                    "id" => $value["id"],
                    "time_real" => date("Y-m-d H:i:s", $value["time_real"]),
                    "source" => $value["source_group_name"]."-".$value["source_src"],
                    "remark" => $value["remarks"],
                    "cs" => $value["cname"].$value["qz_area"],
                    "tel" => $value["tel"],
                    "repeat" => "不重复1",
                    "ip" => "不重复1",
                    "type" => $value["type"]
                );

                $ids[] = $value["tel8"];
                $orders[] = $value["id"];
            }

            $ids = array_filter($ids);

            //获取电话信息
            $begin = strtotime("-3 month",$end);
            $result = D("Orders")->getTelList($ids,$begin,$end);
            foreach ($result as $key => $value) {
                $tels[trim($value["tel8"])] = $value["count"];
            }

            //获取重复IP信息
            $result = D("Orders")->getIpRepaetCountByIds($orders);
            foreach ($result as $key => $value) {
                $ips[$value["id"]] = $value["repeat_count"];
            }

            foreach ($list as $key => $value) {
                $list[$key]["ip"] = ($ips[$value["id"]]["repeat_count"] > 1) ?"重复":"不重复";
                $list[$key]["repeat"] = ($tels[trim($value["tel8"])]["count"] > 1) ?"重复":"不重复";
                unset($list[$key]["id"]);
                unset($list[$key]["tel8"]);
            }

            foreach($list as $row){
                $xlsxWriter->writeSheetRow('Sheet1', $row);
            }

            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="渠道来源发单明细.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $xlsxWriter->writeToStdOut("php://output");
            die();
        }
    }

    public function downloadsrccityvip()
    {
        if ($_POST) {
            $date_maturity =  D("Options")->getOptionNoCache('DATE_MATURITY');
            $list = D("User")->getExpiringMemberList(I("post.date"),$date_maturity["option_value"],I("post.city"),I("post.sort"));

            if (count($list) > 0) {
                import('Library.Org.PHP_XLSXWriter.xlsxwriter');
                $xlsxWriter = new \XLSXWriter();
                //标题
                $herder = array(
                    "城市名称",
                    "会员数"
                );
                $xlsxWriter->writeSheetRow('Sheet1', $herder);

                foreach($list as $row){
                    $xlsxWriter->writeSheetRow('Sheet1', $row);
                }

                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
                header("Content-Type:application/force-download");
                header("Content-Type:application/vnd.ms-execl");
                header("Content-Type:application/octet-stream");
                header("Content-Type:application/download");;
                header('Content-Disposition:attachment;filename="渠道推广城市筛选.xlsx"');
                header("Content-Transfer-Encoding:binary");
                $xlsxWriter->writeToStdOut("php://output");
                die();
            }
        }
    }

    //渠道查询(质检)
    public function downLoadGetQcSrc()
    {
        $start_time = I('get.start_time');
        if(!empty($start_time)){
            $date = strtotime($start_time);
            $start_time = mktime(0,0,0,date("m",$date),date("d",$date),date("Y",$date));
        }else{
            $start_time = mktime(0,0,0,date("m"),date("1"),date("Y"));
        }
        $end_time = I('get.end_time');
        if(!empty($end_time)){
            $date = strtotime($end_time);
            $end_time   = mktime(23,59,59,date("m",$date),date("d",$date),date("Y",$date));
        }else{
            $end_time   = mktime(23,59,59,date("m"),date("d"),date("Y"));
        }

        //已选渠道标记
        if(I('get.alias')){
            $alias = I('get.alias');
        }
        if(I('get.type')){
            $type = intval(I('get.type'));
        }

        $result = D("Orders")->getQcOrderAliasList($start_time, $end_time,$depts,$alias,$type,'','');
        $list[] = array(
            'id' => '序号',
            'alias' =>  '渠道代号',
            'fen_order' =>  '分单',
            'zeng_order' => '赠单',
            'wuxiao_order' =>  '无效单',
            'no_zj_fen_order' =>  '未质检分单量',
            'no_zj_zeng_order' =>  '未质检赠单量',
            'no_zj_wuxiao_order' =>  '未质检无效单量',
            'zj_order' =>  '已质检订单量',
            'zj_order_percent' =>  '质检比例',
        );
        foreach ($result as $key => $value) {
            $list[$key + 1]['id'] = $key + 1;
            $list[$key + 1]['alias'] = $value['alias'];
            $list[$key + 1]['fen_order'] = $value['fen_order'];
            $list[$key + 1]['zeng_order'] = $value['zeng_order'];
            $list[$key + 1]['wuxiao_order'] = $value['wuxiao_order'];
            $list[$key + 1]['no_zj_fen_order'] = $value['no_zj_fen_order'];
            $list[$key + 1]['no_zj_zeng_order'] = $value['no_zj_zeng_order'];
            $list[$key + 1]['no_zj_wuxiao_order'] = $value['no_zj_wuxiao_order'];
            $list[$key + 1]['zj_order'] = $value['zj_order'];
            $list[$key + 1]['zj_order_percent'] = number_format($value['zj_order_percent'] * 100, 4).'%';
        }

        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        // 设置缓存方式，减少对内存的占用
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();
        $j = 1;
        foreach ($list as $key => $value) {
            $i = 65;//字母A的ASC值 65-95 A-Z 的ASC值
            $n = 65;
            $p = 65;
            foreach ($value as $k => $val) {
                $char = strtoupper(chr($i));
                if ($char > "Z") {
                    $char = strtoupper(chr($n)).strtoupper(chr($p));
                    if ($char > "Z") {
                        $n++;
                    }
                    $p++;
                }
                //每行的单元格
                $num = $char.$j;
                //单元格内容，如果长度大约15位，并且类型是数字则指定类型为字符串，避免科学计数法导致数字丢失
                if (strlen($val) > 15 && is_numeric($val)) {
                    $phpExcel->getActiveSheet()->setCellValueExplicit($num,$val);
                } else {
                    $phpExcel->getActiveSheet()->setCellValue($num,$val);
                }
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
        header('Content-Disposition:attachment;filename=渠道查询(质检).xls');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
    }

    //缺单统计
    public function downLoadOrderLack()
    {
        $quedan = S("Cache:168new:orderlack");
        if( !$quedan){
            //获取导入了重点系数的城市信息
            $result = D('SalesSetting')->getSalesCityRatio();
            $manage_id = array_column($result,'manage_id');
            $cid = array_column($result,'cs');
            //获取城市计划分单数
            $plan = D('SalesSetting')->getSettingPlanValueList($manage_id);
            foreach($plan as $val){
                $plans[$val['manage_id']] = number_format($val['point'],1);
            }

            //获取城市实际分单量
            $real = D('SalesSetting')->getSettinggFendanList($cid, strtotime(date('Y-m-01')),strtotime(date('Y-m-d')) +86400-1);
            foreach($real as $val){
                $reals[$val['manage_id']] = $val['fendan'];
            }

            foreach($result as $val){
                $list[$val['manage_id']] = $val;
                $list[$val['manage_id']]['point'] = $plans[$val['manage_id']];
                $list[$val['manage_id']]['fendan'] = $reals[$val['manage_id']];
            }

            // 会员分单数量 - 现有会员数 * 城市计划分单数
            foreach($list as $key=>$val){
                $diff = $val['fendan']-$val['huiyuan']*$val['point'];
                //缺单
                if($diff<0){
                    $quedan[$key] = $val;
                    $quedan[$key]['name'] = $val['cname'];
                }
            }
            unset($list);
        }

        $list[] = array(
            'name' => '城市',
            'ratio' =>  '重点系数'
        );
        foreach ($quedan as $key => $value) {
            $list[$key + 1]['name'] = $value['name'];
            $list[$key + 1]['alias'] = $value['ratio'];
        }

        import('Library.Org.Phpexcel.PHPExcel',"",".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007',"",".php");
        // 设置缓存方式，减少对内存的占用
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array ( 'cacheTime' => 300 );
        \PHPExcel_Settings::setCacheStorageMethod ( $cacheMethod, $cacheSettings );
        $phpExcel = new \PHPExcel();
        $j = 1;
        foreach ($list as $key => $value) {
            $i = 65;//字母A的ASC值 65-95 A-Z 的ASC值
            $n = 65;
            $p = 65;
            foreach ($value as $k => $val) {
                $char = strtoupper(chr($i));
                if ($char > "Z") {
                    $char = strtoupper(chr($n)).strtoupper(chr($p));
                    if ($char > "Z") {
                        $n++;
                    }
                    $p++;
                }
                //每行的单元格
                $num = $char.$j;
                //单元格内容，如果长度大约15位，并且类型是数字则指定类型为字符串，避免科学计数法导致数字丢失
                if (strlen($val) > 15 && is_numeric($val)) {
                    $phpExcel->getActiveSheet()->setCellValueExplicit($num,$val);
                } else {
                    $phpExcel->getActiveSheet()->setCellValue($num,$val);
                }
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
        header('Content-Disposition:attachment;filename=缺单城市.xls');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
    }

    /*
     * 搜索关键词导出
     */
    public function exportCityOrders()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(60 * 10);
        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            //ob_start();
            $writer = new \XLSXWriter();
            //标题
            $herder = array(
                '时间',
                '城市',
                '渠道来源组',
                '渠道名称',
                '渠道标识',
                '已发单',
                '分单',
                '分签单',
                '分签单率',
                '分-量房（去重）',
                '分-量房（未去重）',
                '分-实际量房',
                '赠单',
                '赠签单',
                '赠签单率',
                '赠-量房（去重）',
                '赠-量房（未去重）',
                '赠-实际量房',
                '总签单',
                '总签单率',
                '总量房',
            );
            $writer->writeSheetRow('Sheet1', $herder);
            //数据
            $map = D('Home/Logic/OrdersLogic')->getCityOrdersMap(I('get.'));
            $info = D('Home/Logic/OrdersLogic')->getExplodeCityOrdersList($map);
            foreach ($info as $key => $value) {
                $dd['time'] = $value['time'];
                $dd['cname'] = $value['cname'];
                $dd['group_name'] = $value['group_name'];
                $dd['name'] = $value['name'];
                $dd['src'] = $value['src'];
                $dd['fa_order'] = $value['fa_order'];
                $dd['fen_order'] = $value['fen_order'];
                $dd['fen_qian_order'] = $value['fen_qian_order'];
                $dd['fen_qian_lv'] = number_format($value['fen_qian_lv'], 1) . '%';
                $dd['fen_liang_order'] = $value['fen_liang_order'];
                $dd['fen_liang_all_order'] = $value['fen_liang_all_order'];
                $dd['fen_liang_rel_order'] = $value['fen_liang_rel_order'];
                $dd['zeng_order'] = $value['zeng_order'];
                $dd['zeng_qian_order'] = $value['zeng_qian_order'];
                $dd['zeng_qian_lv'] = number_format($value['zeng_qian_lv'], 1) . '%';
                $dd['zeng_liang_order'] = $value['zeng_liang_order'];
                $dd['zeng_liang_all_order'] = $value['zeng_liang_all_order'];
                $dd['zeng_liang_rel_order'] = $value['zeng_liang_rel_order'];
                $dd['qian_order'] = $value['qian_order'];
                $dd['qian_lv'] = number_format($value['qian_lv'], 1) . '%';
                $dd['all_liang_order'] = $value['all_liang_order'];
                $wArr = array_values($dd);
                unset($info[$key]);
                $writer->writeSheetRow('Sheet1', $wArr);
            }
            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="城市渠道发单统计.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        }catch (Exception $e){
            if($_SESSION["uc_userinfo"]["uid"] == 1){
                var_dump($e);
            }
        }
        exit();
    }

    /*
     * 渠道量房统计导出
     */
    public function exportLiangfangStatistics()
    {
        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            ob_start();
            $writer = new \XLSXWriter();
            //标题
            $herder = array(
                '时间',
                '渠道组',
                '渠道名称',
                '标记代号',
                '渠道代号',
                '所属部门',
                '城市',
                '订单号',
                '订单状态',
                '量房状态'
            );
            $writer->writeSheetRow('Sheet1', $herder);
            //数据
            $liangfangLogic = new CompanyLiangfangLogicModel();
            $list = $liangfangLogic->getOrdersLiangfang(I('get.'),1);
            unset($list['page']);
            foreach ($list['list'] as $key => $value) {
                $dd['time_real'] = date('Y-m-d',$value['time_real']);
                //质检部看不到 渠道标识 a.1.1.23
                if (in_array(session('uc_userinfo.uid'), [23, 66, 69,1])) {
                    $dd['group_name'] = $dd['src_name'] = $dd['src'] = '****';
                } else {
                    $dd['group_name'] = $value['group_name'];
                    $dd['src_name'] = $value['src_name'];
                    $dd['src'] = $value['src'];
                }
                $dd['alias'] = !empty($value['alias'])?$value['alias']:'';
                $dd['dept_name'] = !empty($value['dept_name'])?$value['dept_name']:'';
                $dd['cname'] = $value['cname'];
                $dd['id'] = $value['id'];
                switch ($value['type_fw']){
                    case '1':
                        $dd['type_fw'] = '分单';
                        break;
                    case '2':
                        $dd['type_fw'] = '赠单';
                        break;
                }
                if ($value['qianyue'] == 4) {
                    $dd['liangfang'] = '已签约';
                } else if ($value['liangfang'] == 3) {
                    $dd['liangfang'] = '已量房';
                } else if ($value['choose'] == 2) {
                    $dd['liangfang'] = '待选择';
                } else if ($value['no_liangfang'] == 1) {
                    $dd['liangfang'] = '未量房';
                } else {
                    $dd['liangfang'] = '已见面/已到店';
                }
                $wArr = array_values($dd);
                unset($list['list'][$key]);
                $writer->writeSheetRow('Sheet1', $wArr);
            }
            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="渠道量房统计.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        }catch (Exception $e){
            if($_SESSION["uc_userinfo"]["uid"] == 1){
                var_dump($e);
            }
        }
        exit();
    }

    /**
     * 导出装修公司联系方式访问或者黑名单
     *
     * @return void
     */
    public function exportContactOrBlack()
    {
        $type = I('get.type', '');
        try {
            ob_start();
            if ($type == 1) {
                $herder = ['序号', '手机号码', 'IP地址', '查询时间', '查询次数'];
                $fileName = '查看装修公司联系方式短信记录';
            } elseif ($type == 2) {
                $herder = ['序号', '手机号码', 'IP地址', '封禁时间', '查询次数', '封禁原因', '图片地址'];
                $fileName = '查看装修公司联系方式短信记录黑名单';
            } else {
                exit('系统错误，请检查');
            }

            $logic = new \Home\Model\Logic\CompanyContactViewsLogicModel();
            if ($type == 1) {
                $list = $logic->getLogListNoPage(I('get.tel', ''), I('get.start', ''), I('get.end'));
            } elseif ($type == 2) {
                $list = $logic->getBlackListNoPage(I('get.tel', ''), I('get.start', ''), I('get.end'));
            } else {
                exit('系统错误，请检查');
            }

            $list = array_merge([$herder], $list);

            import('Library.Org.Phpexcel.PHPExcel', '', '.php');
            import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007', '', '.php');
            // 设置缓存方式，减少对内存的占用
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
            $cacheSettings = array('cacheTime' => 300);
            \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
            $phpExcel = new \PHPExcel();
            $j = 1;
            foreach ($list as $key => $value) {
                $i = 65;//字母A的ASC值 65-95 A-Z 的ASC值
                $n = 65;
                $p = 65;
                if ($key > 0) {
                    array_unshift($value, $key);
                }
                foreach ($value as $k => $val) {
                    $char = strtoupper(chr($i));
                    if ($char > 'Z') {
                        $char = strtoupper(chr($n)) . strtoupper(chr($p));
                        if ($char > 'Z') {
                            $n++;
                        }
                        $p++;
                    }
                    //每行的单元格
                    $num = $char . $j;
                    //设置单元格宽度
                    $phpExcel->getActiveSheet()->getColumnDimension($char)->setWidth(20);

                    if (stripos($val, 'http') === 0) {
                        //如果是超链接，则进行超链接设置
                        $phpExcel->getActiveSheet()->setCellValue($num, '点击查看');
                        $phpExcel->getActiveSheet()->getCell($num)->getHyperlink()->setUrl($val);
                        $phpExcel->getActiveSheet()->getStyle($num)->getFont()->setUnderline(\PHPExcel_Style_Font::UNDERLINE_SINGLE);
                        $phpExcel->getActiveSheet()->getStyle($num)->getFont()->getColor()->setARGB(\PHPExcel_Style_Color::COLOR_BLUE);
                    } elseif (stripos($k, 'time') !== false && ctype_digit((string)$val) && $val <= 2147483647) {
                        //判断是否是时间戳，如果是转换成具体时间
                        $phpExcel->getActiveSheet()->setCellValueExplicit($num, date('Y-m-d H:i:s', $val));
                    } elseif (strlen($val) > 15 && is_numeric($val)) {
                        //单元格内容，如果长度大约15位，并且类型是数字则指定类型为字符串，避免科学计数法导致数字丢失
                        $phpExcel->getActiveSheet()->setCellValueExplicit($num, $val);
                    } else {
                        $phpExcel->getActiveSheet()->setCellValue($num, $val);
                    }
                    $phpExcel->getActiveSheet()->getStyle($num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $i++;
                }
                $j++;
            }

            ob_end_clean();
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control:must-revalidate, post-check=0, pre-check=0');
            header('Content-Type:application/force-download');
            header('Content-Type:application/vnd.ms-execl');
            header('Content-Type:application/octet-stream');
            header('Content-Type:application/download');;
            header('Content-Disposition:attachment;filename="'.$fileName.'.xlsx"');
            header('Content-Transfer-Encoding:binary');
            $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
            $writer->save('php://output');
        }catch (\Exception $e) {
            if ($_SESSION['uc_userinfo']['uid'] == 1) {
                echo $e->getMessage();
            }
        }
        exit();
    }
    /*
    * 所有订单导出
    */
    public function exportAllOrder()
    {
        ini_set('memory_limit', '256M');
        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            ob_start();
            $writer = new \XLSXWriter();
            //标题
            $herder = array(
                '编号',
                '发布日期',
                '业主名称',
                '地区',
                '小区名',
                '面积（㎡）',
                '预算',
                '分/赠送',
                '装修类型',
                '装修方式'
            );
            $writer->writeSheetRow('Sheet1', $herder);
            //数据
            $admin = getAdminUser();
            $cityIds = getAdminCityIds(true, true, true);
            //获取查询条件
            $param = I('get.');

            //初始化定义查询条件，目的是规范输入
            $cs = false;
            $qx = false;
            //修改后的订单发布时间筛选
            //不为待定或者没选时间段,限定查询最近30天的单子
            $time_start = empty($param['time_start']) ? strtotime("-30 day") : strtotime($param['time_start']);
            $time_end = empty($param['time_end']) ? '' : strtotime($param['time_end'] . ' 23:59:59');

            $yusuan = empty($param['yusuan']) ? false : $param['yusuan'];
            $type_fw = empty($param['type_fw']) ? false : $param['type_fw'];
            $lx = empty($param['lx']) ? false : $param['lx'];
            $fangshi = empty($param['fangshi']) ? false : $param['fangshi'];

            $order = 'time_real DESC';

            //默认城市
            if (1 != $admin['uid']) {
                $cs = array('IN', $cityIds);
            }

            //处理城市选择查询
            if (!empty($param['cs']) && $param['cs'] != 'null') {
                $city = intval($param['cs']) == 1 ? '000001' : intval($param['cs']);
                if (1 == $admin['uid'] || in_array($city, $cityIds) || $_GET['cs'] == '000001') {
                    $cs = $city;

                }
            }
            if (!empty($param['qx']) && $param['qx'] != 'null') {
                $qx = $param['qx'];
            }
            $db = D('Orders');
            $result = $db->getAllOrdersList($time_start, $time_end, $yusuan, $type_fw, $lx, $fangshi, $cs, $qx, $order, 0, 999999999);
            foreach ($result as $key => $value) {
                $dd['id'] = ' '.$value['id'];
                $dd['time'] = date('Y-m-d',$value['time']);
                $dd['name'] = !empty($value['name'])?$value['name'].$value['sex']:'';
                $dd['city'] = !empty($value['city'])?$value['city'].$value['area']:'';
                $dd['xiaoqu'] = !empty($value['xiaoqu'])?$value['xiaoqu']:'';
                $dd['mianji'] = !empty($value['mianji'])?$value['mianji']:'';
                $dd['yusuan_name'] = !empty($value['yusuan_name'])?$value['yusuan_name']:'';
                $dd['type_fw_name'] = !empty($value['type_fw_name'])?$value['type_fw_name']:'';
                $dd['lx_name'] = !empty($value['lx_name'])?$value['lx_name']:'';
                $dd['fangshi_name'] = !empty($value['fangshi_name'])?$value['fangshi_name']:'';
                $wArr = array_values($dd);
                unset($result[$key]);
                $writer->writeSheetRow('Sheet1', $wArr);
            }
            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="所有订单.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        }catch (\Exception $e){
            if($_SESSION["uc_userinfo"]["uid"] == 1){
                var_dump($e);
            }
        }
        exit();
    }

    public function sourcestat()
    {
        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
//            ob_start();
            $writer = new \XLSXWriter();
            //标题
            $herder = array(
                '咨询时间',
                '标记代码',
                '标记来源组',
                '标记名称',
                '城市',
                '姓名',
                '联系方式'
            );
            $writer->writeSheetRow('Sheet1', $herder);
            $model = new CompanyConsultLogicModel();
            $list = $model->getSourceList(I("get.begin"),I("get.end"),I("get.city"),I("get.src"),I("get.group"),true);

            foreach ($list['list'] as $key => $value) {
                $dd['add_time'] = date("Y-m-d",$value["add_time"]);
                $dd['src'] = $value["src"];
                $dd['group_name'] = $value["group_name"];
                $dd['source_name'] = $value["source_name"];
                $dd['city'] = $value["cname"]."/".$value["qz_area"];
                $dd['linkman'] = $value["linkman"];
                $dd['tel'] = formatTel($value["tel"]);
                $wArr = array_values($dd);
                unset($list['list'][$key]);
                $writer->writeSheetRow('Sheet1', $wArr);
            }
            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="公司信息列表.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        }catch (Exception $e){
            if($_SESSION["uc_userinfo"]["uid"] == 1){
                var_dump($e);
            }
        }
        exit();
    }

    /*
    * 所有专题关键词导出
    */
    public function exportthematic()
    {
        ini_set('memory_limit', '256M');
        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
            ob_start();
            $writer = new \XLSXWriter();
            //标题
            $herder = array(
                '标签名称',
                '标签栏目',
                '标签类型',
                '添加时间',
                '分词',
                '链接'
            );
            $writer->writeSheetRow('Sheet1', $herder);
            //获取查询条件
            $module = I('get.module');
            $type = I('get.type');
            $name = I('get.name');
            $start =   I("get.start");
            $end =   I("get.end");
            $logic = new ThematicWordsLogicModel();
            $result = $logic->getExportList($module,$type,$name,$start,$end);

            // foreach ($list as $key => $item) {
            //     if($item['type'] == 1){
            //         $list[$key]["link"] = "http://".C("QZ_YUMINGWWW")."/zhishi/".$item["id"]."/";
            //     } else if ($item['type'] == 2){
            //         $list[$key]["link"] = "http://".C("QZ_YUMINGWWW")."/tu/".$item["id"]."/";
            //     }
            // }

            foreach ($result as $key => $value) {
                $dd['name'] = !empty($value['name'])?$value['name']:'';
                switch ($value['module']){
                    case '1':
                        $module = '装修攻略';
                        break;
                    case 2:
                        $module = '装修百科';
                        break;
                    case 3:
                        $module = '装修问答';
                        break;
                    default:  $module = '其他'; break;
                }
                $dd['module'] = $module;
                switch ($value['type']){
                    case '1':
                        $type = '图文';
                        break;
                    case '2':
                        $type = '图片';
                        break;
                }

                $dd['type'] = $type;
                $dd['time'] = date('Y-m-d H:i:s',$value['createtime']);
                $dd['words'] = $value["words"];
                $dd["links"] = "--";
                if ($value["is_show"] == 1) {
                    switch ($value['type']){
                        case '1':
                            $dd["links"] = "http://".C("QZ_YUMINGWWW")."/zhishi/".$value["id"]."/";
                            break;
                        case '2':
                            $dd["links"] = "http://".C("QZ_YUMINGWWW")."/tu/".$value["id"]."/";
                            break;
                    }
                }

                $wArr = array_values($dd);
                unset($result[$key]);
                $writer->writeSheetRow('Sheet1', $wArr);
            }
            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="所有专题页关键词.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        }catch (\Exception $e){
            if($_SESSION["uc_userinfo"]["uid"] == 1){
                var_dump($e);
            }
        }
        exit();
    }

    /**
     * 抢单统计导出
     */
    public function robStatic()
    {
        import('Library.Org.PHP_XLSXWriter.xlsxwriter');
        try {
//            ob_start();
            $writer = new \XLSXWriter();
            //标题
            $herder = array(
                '订单发布时间',
                '订单号',
                '已分配会员数',
                '抢单数量',
                '订单状态',
                '量房状态',
                '城市',
                '客服组',
                '归属人'
            );
            $writer->writeSheetRow('Sheet1', $herder);
            $model = new RobOrdersLogicModel();
            $list = $model->getRobOrderList(I("get."),0);
            if(count($list['list']) > 0){
                foreach ($list['list'] as $key => $value) {
                    $dd['time_real'] = date("Y-m-d H:i:s",$value["time_real"]);
                    $dd['id'] = $value["id"] .' ';
                    $dd['rob_num'] = $value["rob_num"];
                    $dd['qiang_num'] = $value["qiang_num"];
                    //获取订单类型
                    if($value['type_fw'] == 1){
                        //如果订单池状态和原订单状态相同 , 则表示不是转单
                        if($value['status'] == $value['type_fw']){
                            //判断能不能抢
                            if($value['is_rob'] == 1){
                                $dd['order_type'] = '分单（可抢）';
                            }else{
                                $dd['order_type'] = '分单';
                            }
                        }else{
                            //判断能不能抢
                            if($value['is_rob'] == 1){
                                $dd['order_type'] = '分单（可抢）';
                            }else{
                                $dd['order_type'] = '赠转分';
                            }
                        }
                    }elseif ($value['type_fw'] == 2){
                        //判断能不能抢
                        if($value['is_rob'] == 1){
                            $dd['order_type'] = '赠单（可抢）';
                        }else{
                            $dd['order_type'] = '赠单';
                        }
                    }else{
                        $dd['order_type'] = '未知';
                    }
                    //量房状态
                    if($value['lf'] == 1){
                        $dd['lf_status'] = '已量房';
                    }else{
                        if($value['no_lf'] == 1){
                            $dd['lf_status'] = '未量房';
                        }else{
                            $dd['lf_status'] = '未知';
                        }
                    }
                    $dd['cname'] = $value["cname"];
                    $dd['kfgroup'] = $value["kfgroup"];
                    $dd['op_name'] = $value["op_name"];
                    $wArr = array_values($dd);
                    unset($list['list'][$key]);
                    $writer->writeSheetRow('Sheet1', $wArr);
                }
            }
            ob_end_clean();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");;
            header('Content-Disposition:attachment;filename="抢单统计.xlsx"');
            header("Content-Transfer-Encoding:binary");
            $writer->writeToStdOut("php://output");
        }catch (Exception $e){
            if($_SESSION["uc_userinfo"]["uid"] == 1){
                var_dump($e);
            }
        }
        exit();
    }

    public function exportlabelmaintenance()
    {
        $start = I("get.start");
        $end = I("get.end");
        if (!empty($start) && !empty($end)) {
            $start = strtotime($start);
            $end = strtotime($end) + 86400 -1;
            $module = I("get.module");
            if (!empty($module)) {
                switch ($module){
                    case 1:
                        //攻略
                        $list = D("WwwArticle")->getArticleListByTime($start,$end);
                        break;
                    case 2:
                        //百科
                        $map = array(
                            "post_time" => array(
                                array("EGT",$start),
                                array("ELT",$end),
                            )
                        );
                        $list = D("Home/Db/Baike")->getArticle("id,item as title",$map);
                        break;
                    case 3:
                        //问答
                        $list = D("WwwArticle")->getListByTime($start,$end);
                        break;
                    case 4:
                        //分站资讯
                        $list = D("LittleArticle")->getListByTime($start,$end);

                        break;
                }

                $service = new ElasticSearchServiceModel();

                foreach ($list as $key => $val) {
                    $result = $service->analyzeWord($val['title']);
                    $list[$key]["labels"] = implode(",",$result);
                }

                import('Library.Org.PHP_XLSXWriter.xlsxwriter');
                $writer = new \XLSXWriter();
                //标题
                $herder = array(
                    'id',
                    '标题',
                    '分词'
                );
                $writer->writeSheetRow('Sheet1', $herder);

                foreach ($list as $key => $item) {
                    $wArr = array_values($item);
                    unset($list['list'][$key]);
                    $writer->writeSheetRow('Sheet1', $wArr);
                }

                ob_end_clean();
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
                header("Content-Type:application/force-download");
                header("Content-Type:application/vnd.ms-execl");
                header("Content-Type:application/octet-stream");
                header("Content-Type:application/download");;
                header('Content-Disposition:attachment;filename="内容标签维护.xlsx"');
                header("Content-Transfer-Encoding:binary");
                $writer->writeToStdOut("php://output");
                die();

            }
        }
    }
}
