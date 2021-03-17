<?php
/**
 * Home 模块总控制器
 */

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;
use Home\Model\Db\OrderReviewNewModel;
use Home\Model\Db\OrdersModel;
use Home\Model\Logic\OrderReviewLogicModel;
use Home\Model\Logic\ThematicWordsLogicModel;
use Home\Model\Service\ElasticsearchServiceModel;
use Think\Controller;

class MainController extends HomeBaseController {
    public function index(){
        $this->display();
    }

    /**
     * 批量生成渠道标识
     * @return [type] [description]
     */
    public function importsrc()
    {
        ini_set('memory_limit', '512M');
        if (!in_array(session("uc_userinfo.uid"),array(1,37))) {
            $this->_error();
        }

        $channel = array(
            array(
                "name" =>  "百度",
                "src" => "bd"
            ),
            array(
                "name" =>  "360",
                "src" => "360"
            ),
            array(
                "name" =>  "搜狗",
                "src" => "sg"
            ),
            array(
                "name" =>  "神马",
                "src" => "sm"
            )
        );

        $groups = array(
            "bd"  => "8",
            "360" => "26",
            "sg" => "27",
            "sm" => "24"
        );

        //获取已开站城市信息
        $citys = D("Quyu")->getOpenCityList();

        foreach ($citys as $key => $value) {
            foreach ($channel as $k => $val) {
                 if ($val["src"] != "sm") {
                    $src = "p-".$val["src"]."-".$value["bm"];
                    $all[] = array(
                        "type" => "1",
                        "src" => $src,
                        "name" => $val["name"].$value["cname"]."pc端",
                        "groupid" => $groups[$val["src"]],
                        "dept" => 5,
                        "charge" => 2,
                        "description" => "系统批量添加",
                        "visible" => 0,
                        "addtime" => time()
                    );
                    $src = "m-".$val["src"]."-".$value["bm"];
                    $all[] = array(
                        "type" => "1",
                        "src" => $src,
                        "name" => $val["name"].$value["cname"]."移动端",
                        "groupid" =>  $groups[$val["src"]],
                        "dept" => 5,
                        "charge" => 2,
                        "description" => "系统批量添加",
                        "visible" => 0,
                        "addtime" => time()
                    );
                 } else {
                    $src = "m-".$val["src"]."-".$value["bm"];
                    $all[] = array(
                        "type" => "1",
                        "src" => $src,
                        "name" => $val["name"].$value["cname"]."移动端",
                        "groupid" =>  $groups[$val["src"]],
                        "dept" => 5,
                        "charge" => 2,
                        "description" => "系统批量添加",
                        "visible" => 0,
                        "addtime" => time()
                    );
                 }
            }
        }

        echo "插入数据量：".count($all)."<br/>";
        echo "插入数据开始".date("Y-m-d H:i:s")."<br/>";
        $all = array_chunk($all, 1000);
        foreach ($all as $key => $value) {
            D("OrderSource")->addAllSource($value);
        }
        echo "插入数据结束".date("Y-m-d H:i:s")."<br/>";

    }

    public function importSourceSrc()
    {
        $folderPath = dirname(dirname(dirname(dirname(__FILE__))))."/upload/";

        if(!is_dir($folderPath)){
            mkdir($folderPath,0777);
        }

        $filePath = $folderPath."file.xlsx";
        import("Library.Org.Phpexcel.PHPExcel","",".php");
        import("Library.Org.Phpexcel.PHPExcel.IOFactory","",".php");
        import("Library.Org.Phpexcel.PHPExcel.Reader.Excel2007","",".php");
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
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

        foreach ($data as $key => $value) {
            $all[] = array(
                "type" => "1",
                "src" => $value[0],
                "name" => $value[1],
                "groupid" =>  $value[3],
                "dept" => $value[4],
                "charge" => $value[5],
                "description" => "系统批量添加",
                "visible" => 0,
                "addtime" => time(),
                "alias" => "QD".unique_rand(),
            );
        }
        echo "插入数据量：".count($all)."<br/>";
        echo "插入数据开始".date("Y-m-d H:i:s")."<br/>";
        $all = array_chunk($all, 1000);
        foreach ($all as $key => $value) {
            D("OrderSource")->addAllSource($value);
        }
        echo "插入数据结束".date("Y-m-d H:i:s")."<br/>";
        //删除本地的文件
        if(file_exists($filePath)){
            unlink($filePath);
            rmdir($folderPath);
        }
        die();

    }

    public function importCityGeneralization()
    {
        $folderPath = dirname(dirname(dirname(dirname(__FILE__))))."/upload/";

        if(!is_dir($folderPath)){
            mkdir($folderPath,0777);
        }

        $filePath = $folderPath."file.xlsx";
        import("Library.Org.Phpexcel.PHPExcel","",".php");
        import("Library.Org.Phpexcel.PHPExcel.IOFactory","",".php");
        import("Library.Org.Phpexcel.PHPExcel.Reader.Excel2007","",".php");
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel = $objReader->load($filePath);
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
            $n =  intval(($value[0] - 25569) * 3600 * 24);
            $d = gmdate('Y-m-d H:i:s',$n);
            $list[$d.$value[1]] = $value[2];
        }

        //获取每天真实的会员城市
        $result = D("Home/Db/LogUserRealCompany")->getCityVipListByDay("2019-03-01","2019-04-30");
        foreach ($result as $key => $value) {
            $k =  $value["time"].$value["cname"];
            $all[] =  array(
                "date" =>  $value["time"],
                "city_id" => $value["city_id"],
                "city_name" => $value["cname"],
                "coefficient" => isset($list[$k])?$list[$k]:0
            );
        }

        echo "插入数据量：".count($all)."<br/>";
        echo "插入数据开始".date("Y-m-d H:i:s")."<br/>";
        $all = array_chunk($all, 1000);
        foreach ($all as $key => $value) {
            D("Home/Db/CityGeneralizationCoefficient")->addAllInfo($value);
        }
        echo "插入数据结束".date("Y-m-d H:i:s")."<br/>";
        //删除本地的文件
//        if(file_exists($filePath)){
//            unlink($filePath);
//            rmdir($folderPath);
//        }
        die();

    }

    public function importCity()
    {
        $folderPath = dirname(dirname(dirname(dirname(__FILE__))))."/upload/";

        $filePath = $folderPath."file.xlsx";
        import("Library.Org.Phpexcel.PHPExcel","",".php");
        import("Library.Org.Phpexcel.PHPExcel.IOFactory","",".php");
        import("Library.Org.Phpexcel.PHPExcel.Reader.Excel2007","",".php");
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        $objPHPExcel = $objReader->load($filePath);
        $sheet = $objPHPExcel->getSheet(1);
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

        //获取所有省份
        $result = D("Quyu")->getProvince();
        foreach ($result as $value) {
            $value["qz_province"] = str_replace("省","",$value["qz_province"]);
            $province[$value["qz_province"]] = $value["qz_provinceid"];
        }

        //获取每个字母最后一个排序
        $result = D("Quyu")->getProvinceLastCityByChar();
        foreach ($result as $value) {
            $abcList[$value["word"]] = preg_replace('/[a-z]/','', $value["px_abc"]);
        }

        //获取每个字母最后一个排序
        $result = D("Quyu")->getProvinceLastCityPx();
        foreach ($result as $value) {
            $pxList[$value["uid"]] = $value["px"];
        }
        

        foreach ($data as $key => $value) {
            $value[8] = strtoupper($value[8]);
            switch ($value[8]) {
                case "A":
                    $little = 0;
                    break;
                case "B":
                    $little = 1;
                    break;
                case "C":
                    $little = 2;
                    break;
                case "S1":
                    $little = 4;
                    break;
                case "S2":
                    $little = 5;
                    break;
            }

            $px = strtolower(mb_substr($value[9],0,1));
            $num = $abcList[$px]+1;

            if ($num < 100) {
                $num = "0".$num;
            }

            $abcList[$px] = $num;
            $px_abc = $px.$num;

            $px = $pxList[$province[$value[2]]];
            $px =$pxList[$province[$value[2]]]  = $px+1;

            switch ($value[10]) {
                case "商务":
                    $manager = 0;
                    break;
                case "外销":
                    $manager = 1;
                    break;
                default:
                    $manager = 2;
                    break;
            }

            $all[] = [
                "cid" => $value[6],
                "cname" => $value[0],
                "uid" => $province[$value[2]],
                "plate" => $value[7],
                "bm" =>  $value[9],
                "little" => $little,
                "px_abc" => $px_abc,
                "px" => $px,
                "manager" => $manager,
                "time_add" => time()
            ];
        }

        echo "插入数据量：".count($all)."<br/>";
        echo "插入数据开始".date("Y-m-d H:i:s")."<br/>";
        $all = array_chunk($all, 1000);
        foreach ($all as $key => $value) {
            D("Home/Quyu")->addAllQuyu($value);
        }

        //添加区域信息
        foreach ($data as $value) {
            $value[5] = str_replace("，",",",$value[5]);
            $exp = array_filter(explode(",",$value[5]));
            $order = 1;
            $num = 1;
            foreach ($exp as $val) {
                if ($num < 10) {
                    $num = "0".$num;
                }
                $subAll[] = [
                    "qz_areaid" => $value[6].$num,
                    "qz_area" => $val,
                    "fatherid" => $value[6],
                    "orders" => $order
                ];
                $order ++;
                $num++;
            }
        }
        D("Home/Quyu")->addAllArea($subAll);
        echo "插入数据结束".date("Y-m-d H:i:s")."<br/>";
        //删除本地的文件
//        if(file_exists($filePath)){
//            unlink($filePath);
//            rmdir($folderPath);
//        }
        die();

    }

    public function exportArticle()
    {
        ini_set('memory_limit','512M');
        $pageCount = 5000;

        if (I("get.sitemap") == 1) {
            $count =  D("WwwArticle")->getAllArticleCount();
            $totalPage = ceil($count/$pageCount);
            $filename = "sitemap.json";
            $sitemap = [
                "sitemapindex" =>  []
            ];
            for ($i=1; $i <= $totalPage ; $i++) {
                $sitemap["sitemapindex"][] = [
                    "sitemap" => [
                        "loc" => C("MPAPI")."/bdmp/baidu_program_article_".$i.".json",
                        "lastmod" => date("Y-m-d")
                    ]
                ];
            }
        } else {
            $pageIndex = 1;
            if (I("get.page") != "") {
                $pageIndex = I("get.page");
            }
            $filename = "baidu_program_article_".$pageIndex.".json";
            $sitemap = [];
            //获取所有的主站文章
            $result = D("WwwArticle")->getAllArticle(($pageIndex-1) * $pageCount, $pageCount);
            foreach ($result as $item) {
                $url = "/pages/gonglue/detail/detail?type=" . $item["shortname"] . "&id=" . $item["id"];
                $sitemap[] = [
                    "app_id" => "16242256",
                    "title" => $item["title"],
                    "body" => $item["subtitle"],
                    "path" => $url,
                    "mapp_type" => "1000",
                    "mapp_sub_type" => "1001",
                    "feed_type" => '家居',
                    "feed_sub_type" => "家居"
                ];
            }
        }
        $sitemap = json_encode($sitemap, 320);
        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header ("Content-Type: application/text" );
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header ("Content-Disposition: attachment; filename=".$filename );
        header("Content-Transfer-Encoding:binary");
        echo $sitemap;
        die();
    }

    public function setContentLabel()
    {
        set_time_limit(0);
        echo "执行开始<br/>";
        $type = I("get.type");
        if (!empty($type)) {
            switch ($type){
                case 1:
                    //攻略
                    $result = M("www_article")->where(["state"=>["EQ",2]])->field("id,title")->select();
                    foreach ($result as $item) {
                        $all[] = [
                            "id" => $item["id"],
                            "title" => $item["title"],
                            "module" => 1,
                            "type" => 1,
                            "limit" => 5
                        ];
                    }
                    break;
                case 2:
                    //百科
                    $result = M("baike")->where(["visible"=>["EQ",0]])->field("id,item")->select();

                    foreach ($result as $item) {
                        $all[] = [
                            "id" => $item["id"],
                            "title" => $item["item"],
                            "module" => 2,
                            "type" => 1,
                            "limit" => 5
                        ];
                    }
                    break;
                case 3:
                    //问答
                    $result = M("ask")->where(["visible"=>["EQ",0]])->field("id,title")->select();
                    foreach ($result as $item) {
                        $all[] = [
                            "id" => $item["id"],
                            "title" => $item["title"],
                            "module" => 3,
                            "type" => 1,
                            "limit" => 5
                        ];
                    }
                    break;
                case 5:
                    //美图
                    break;
                case 6:
                    //标签
                    $result = M("thematic_words")->where(["is_delete"=>["EQ",1]])->field("id,name as title")->select();
                    foreach ($result as $item) {
                        $all[] = [
                            "id" => $item["id"],
                            "title" => $item["title"],
                            "module" => 6,
                            "limit" => 20
                        ];
                    }
                    break;
            }

            //添加标签关联
            $service = new ElasticsearchServiceModel();
            $thematicLogic = new ThematicWordsLogicModel();

            $arr = array_chunk($all,2000);
            foreach ($arr as $item) {
                $allLabel = [];
                foreach ($item as $val) {
                    $result = $service->getContentLabel($val['title'],$val['type'],$val['limit']);
                    if (count($result) > 0) {
                        foreach ($result as $v) {
                            if ($v["id"] != $val["id"]) {
                                $allLabel[] = [
                                    "thematic_id" => $v["id"],
                                    "content_id" => $val["id"],
                                    "module" => $val["module"]
                                ];
                            }
                        }
                    }
                }
                $thematicLogic->addThematicRelated($allLabel);
            }
        }
        echo "执行结束<br/>";
    }


    /**
     * 老回访数据导入新回访
     * @throws \PHPExcel_Reader_Exception
     */
    public function importReviewToNew()
    {
        $folderPath = dirname(dirname(dirname(dirname(__FILE__))))."/upload/";

        if(!is_dir($folderPath)){
            mkdir($folderPath,0777);
        }

        $filePath = $folderPath."review_to_new.xlsx";
        import("Library.Org.Phpexcel.PHPExcel","",".php");
        import("Library.Org.Phpexcel.PHPExcel.IOFactory","",".php");
        import("Library.Org.Phpexcel.PHPExcel.Reader.Excel2007","",".php");
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
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

        $ids = [];
        foreach ($data as $key => $value) {
            if(!empty($value[0])){
                $ids[] = $value[0];
            }
        }

        echo "插入数据开始" . date("Y-m-d H:i:s") . "<br/>";
        $all = array_chunk($ids, 500);
        $reviewLogic = new OrderReviewLogicModel();
        $reviewNewDb = new OrderReviewNewModel();
        $orderDb = new OrdersModel();
        foreach ($all as $key => $value) {
            $reviewData = $reviewLogic->getOrderReviewInfo($value);
            //获取新回访订单
            $reviewNew = $reviewNewDb->getOrderReviewNew($value);
            $reviewNew = array_column($reviewNew, null, 'order_id');
            if (count($reviewData) > 0) {
                $insertData = [];
                $updateData = [];
                foreach ($reviewData as $v) {
                    if (!isset($reviewNew[$v['order_id']])) {
                        $insertData[] = [
                            'order_id' => !empty($v['order_id']) ? $v['order_id'] : '',
                            'review_type' => 1,
                            'review_record' => !empty($v['review_record']) ? $v['review_record'] : '',
                            'review_feedback' => !empty($v['feedback']) ? $v['feedback'] : '',
                            'review_uid' => !empty($v['review_uid']) ? $v['review_uid'] : '',
                            'review_name' => !empty($v['review_name']) ? $v['review_name'] : '',
                            'created_at' => time(),
                            'updated_at' => time(),
                        ];
                        //默认早上9点
                        $updateData[$v['order_id']] = [
                            'review_time' => 1590800400
                        ];
                    }
                }

                if (count($insertData) > 0) {
                    //添加新回访
                    echo "插入数据量：".count($insertData)."<br/>";
                    $reviewNewDb->addAllReviewInfo($insertData);
                    //更新订单
                    $sql = $this->produceUpdateSql('qz_orders',['review_time'],'id',$updateData);
                    $orderDb->updateOrders($sql);
                }
            }
        }
        echo "插入数据结束".date("Y-m-d H:i:s")."<br/>";
        //删除本地的文件
        if(file_exists($filePath)){
            unlink($filePath);
            rmdir($folderPath);
        }
        die();

    }

    /**
     * 批量更新多个字段
     * @param string $table 表名
     * @param string|array $update_field [a,b]
     * @param string $where_field 条件字段
     * @param array $data 更新数据
     * @return string
     * $data = [
    85(条件字段的值)=>[a(更新的字段)=>1(字段更新值),b(更新的字段)=>2(字段更新值)],
    86=>[a=>3,b=>4],
     * ]
     */
    public function produceUpdateSql($table, $update_field, $where_field, $data)
    {
        //初始化
        $sql = 'UPDATE ' . $table . ' set';
        //字段
        $set = [];
        foreach ($update_field as $v) {
            $set[$v] = ' ' . $v . ' = CASE ' . $where_field;
        }
        //条件值
        $whereVal = '';
        foreach ($data as $k => $v) {
            //设置set参数
            foreach ($set as $kk => $vv) {
                $set[$kk] .= ' WHEN ' . $k . ' THEN ' . $v[$kk] . ' ';
            }
            $whereVal .= $k . ',';
        }
        $whereVal = substr($whereVal, 0, -1);
        //更新多个字段拼接
        $setSql = '';
        foreach ($set as $v) {
            $setSql .= $v . ' END,';
        }
        //条件
        $where = ' WHERE ' . $where_field . ' in (' . $whereVal . ')';
        //拼接sql
        return $sql . substr($setSql, 0, -1) . $where;
    }
}