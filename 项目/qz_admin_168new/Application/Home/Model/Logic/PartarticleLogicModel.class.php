<?php

namespace Home\Model\Logic;

use Home\Model\AdminuserModel;
use Home\Model\Service\ElasticsearchServiceModel;
use Home\Model\WwwArticleModel;
use Home\Model\MarketContentModel;

class PartarticleLogicModel {

    const PART_ROLE_ID = 151;

    protected $wwwArticleModel;

    public function __construct(){
        $this->wwwArticleModel = new WwwArticleModel();
    }

    // 获取兼职编辑用户
    public function getPartUser(){
        $adminuserModel = new AdminuserModel();
        $userList = $adminuserModel->getUserListByUid([static::PART_ROLE_ID], 1);
        return $userList;
    }

    // 统计分析查询条件
    public function getArticleanalysisMap(array $partUserIds){
        // 创建时间
        $crate_start = date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y")));
        $create_end = date("Y-m-d");
        $map["a.createtime"] = array(
            array("EGT", strtotime($crate_start)),
            array("LT", strtotime($create_end) + 86400)
        );

        if (I("get.create_start") !== "" && I("get.create_end") !== "") {
            $map["a.createtime"] = array(
                array("EGT", strtotime(I("get.create_start"))),
                array("LT", strtotime(I("get.create_end")) + 86400)
            );
        } elseif (I("get.create_start") !== "" && I("get.create_end") === "") {
            $map["a.createtime"] = array("EGT", strtotime(I("get.create_start")));
        } elseif (I("get.create_start") === "" && I("get.create_end") !== "") {
            $map["a.createtime"] = array("LT", strtotime(I("get.create_end")) + 86400);
        }

        // 正式发布时间
        $start_time = strtotime(I("get.start"));
        $end_time = strtotime(I("get.end"));
        
        if (!empty($start_time) && !empty($end_time)) {
            $end_time = mktime(23, 59, 59, date("m", $end_time), date("d", $end_time), date("Y", $end_time));
            $map["a.addtime"] = array("between", array($start_time, $end_time));
            if (I("get.create_start") == "" && I("get.create_end") == "") {
                unset($map["a.createtime"]);
            }
        }

        // 用户
        $userid = I("get.uid");
        if (!empty($userid)) {
            $map["a.userid"] = array("EQ", $userid);
        } else if (!empty($partUserIds)) {
            $map["a.userid"] = array("IN", $partUserIds);
        } else {
            $map["a.userid"] = array("EQ", "-1");
        }
        
        // 发布类型
        $release = I("get.release");
        if (!empty($release)) {
            $map["a.pre_release"] = array("EQ", $release);
            if ($release == 2) {
                $map["a.state"] = array("NEQ", "-1");
            }
        }
        
        // 发布状态
        if (I("get.state") !== "") {
            $map["a.state"] = array("EQ", I("get.state"));
        }

        // 2017-11-06 添加搜索条件:id、title、分类，增加分类查询
        $title = I("get.title");
        if (!empty($title)) {
            $map["_complex"] = array(
                "a.id" => array("EQ", trim($title)),
                "a.title" => array("LIKE", trim($title). "%"),
                "_logic" => "or"
            );
        }

        if (!empty($_GET['thirdType'])) { //三级分类
            $map['r.class_id'] = I('get.thirdType');
        } else if (!empty($_GET['secondType'])) { //二级分类
            $map['r.class_id'] = I('get.secondType');
        } else if (!empty($_GET['firstType'])) { //一级分类
            $map['r.class_id'] = I('get.firstType');
        }

        // 查询下级分类  下下级分类
        $childCate = D('WwwArticleClass')->getChildClassList($map['r.class_id'], 1);
        foreach ($childCate as $k => $v) {
            $ids[] = $v["id"];
            $grandChildCate = D('WwwArticleClass')->getChildClassList($v['id'], 1);
            foreach ($grandChildCate as $key => $value) {
                $ids[] = $value['id'];
            }
        }

        if (!empty($ids)) {
            $map["r.class_id"] = array("IN", $ids);
        }

        // 排序
        $order = [
            "orderU" => 1,
            "orderF" => 1,
            "orderS" => 1,
            "orderT" => 1,
            "orderO" => 1
        ];

        // UV排序
        if (!empty($_GET["orderU"])) {
            $orderU = I("get.orderU");
            if ($orderU == 1) {
                $map["order"] = "b.search_ip desc";//1倒序
                $order["orderU"] = 2;
            } elseif ($orderU == 2) {
                $map["order"] = "b.search_ip asc";//2正序
            }
        }
        
        // IP排序
        if (!empty($_GET["orderF"])) {
            $orderF = I("get.orderF");
            if ($orderF == 1) {
                $map["order"] = "b.realview desc";//1倒序
                $order["orderF"] = 2;
            } elseif ($orderF == 2) {
                $map["order"] = "b.realview asc";//2正序
            }
        }
        
        // 发单量排序
        if (!empty($_GET["orderS"])) {
            $orderS = I("get.orderS");
            if ($orderS == 1) {
                $map["order"] = "order_num desc";//1倒序
                $order["orderS"] = 2;
            } elseif ($orderS == 2) {
                $map["order"] = "order_num asc";//2正序
            }
        }
        
        // 分单量排序
        if (!empty($_GET["orderT"])) {
            $orderT = I("get.orderT");
            if ($orderT == 1) {
                $map["order"] = "fendans desc";//1倒序
                $order["orderT"] = 2;
            } elseif ($orderT == 2) {
                $map["order"] = "fendans asc";//2正序
            }
        }
        
        // 实际分单量排序
        if (!empty($_GET["orderO"])) {
            $orderO = I("get.orderO");
            if ($orderO == 1) {
                $map["order"] = "real_num desc";//1倒序
                $order["orderO"] = 2;
            } elseif ($orderO == 2) {
                $map["order"] = "real_num asc";//2正序
            }
        }

        return ["map" => $map, "order" => $order];
    }

    // 统计分析列表
    public function getArticleAnalysisList($map, $pageIndex, $pageCount){
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);
        
        $marketContentModel = new MarketContentModel();
        $count = $marketContentModel->getWwwArticleCount($map);
        $result['list'] = $marketContentModel->getWwwArticleList($map, ($pageIndex - 1) * $pageCount, $pageCount, '');
        
        $totalnum = $this->wwwArticleModel->getTotalNum($map);
        $totalSearchIPNum = $this->wwwArticleModel->getTotalSearchIP($map);
        
        //查询所有实际分单数
        $realnum = $marketContentModel->getRealFenByTime($map);
        
        if ($count > $pagecount) {
            import('Library.Org.Util.Page');
            $page = new \Page($count, $pageCount);
            $result['page'] = $page->show();
        }

        $result['fen_num'] = $realnum;
        $result['totaluv'] = $totalSearchIPNum[0]['totalnum'];
        $result['totalview'] = $totalnum[0]['totalnum'];
        $result['totalnum'] = $count;
        return $result;
    }

    // 统计数据处理
    public function setAnalysisFormatter($result){
        foreach ($result["list"] as $k => $v) {
            foreach ($result["fen_num"] as $key => $value) {
                if ($v["id"] == $value["id"]) {
                    $result["list"][$k]["real_fen_num"] = $value["real_num"];
                }
            }
            if (empty($result["list"][$k]["real_fen_num"])) {
                $result["list"][$k]["real_fen_num"] = 0;
            }
        }
        foreach ($result["fen_num"] as $k => $v) {
            $result["real_fen_num"] += $v["real_num"];
        }
        if (empty($result["real_fen_num"])) {
            $result["real_fen_num"] = 0;
        }

        return $result;
    }

    public function getPartArticleMap($data)
    {
        $map = [];
        //查询兼职用户
        if (count($data['user_id']) > 0) {
            $map['a.userid'] = ['in', $data['user_id']];
        }
        if (!empty($data['onelevel']) && $data['onelevel'] != 'null') {
            $cid = $data['onelevel'];
        }
        if (!empty($data['twolevel']) && $data['twolevel'] != 'null') {
            $cid = $data['twolevel'];
        }
        if (!empty($data['threelevel']) && $data['threelevel'] != 'null') {
            $cid = $data['threelevel'];
        }

        // 按分类查询
        if (!empty($cid)) {
            $arr = D('WwwArticleClass')->getArticleClassIdsByClass($cid);
            $id = array();
            foreach ($arr as $row) {
                $id[] = $row['id'];
            }
            if (!empty($id)) {
                $map['class_id'] = array('IN', $id);
            } else {
                //文章分类为空
                $map['class_id'] = array('EQ', '');
            }
        }

        //发布时间范围
        if (!empty($data['addtimeStart'])) {
            $map['addtime'][] = array('EGT', strtotime($data['addtimeStart']));
        }
        if (!empty($data['addtimeEnd'])) {
            $map['addtime'][] = array('LT', strtotime($data['addtimeEnd']) + 86400);
        }

        // 查询词
        if (!empty($data['condition'])) {
            if (ctype_digit((string)$data['condition'])) {
                $map['a.id'] = ['EQ', intval($data['condition'])];
            } else {
                $map['a.title'] = ['LIKE', '%' . $data['condition'] . '%'];
            }
        }
        if (!empty($data['item'])) {
            $map['a.item_name'] = ['LIKE', '%' . $data['item'] . '%'];
        }
        //未审核
        if (!empty($data['state']) && in_array($data['state'], ['-1', '1', '2', '3'])) {
            $map['a.state'] = $data['state'];
        }
        return $map;
    }

    //获取列表并分页
    public function getPartArticleList($map, $pageIndex = 1, $pageCount = 16)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 16 : intval($pageCount);
        $count = D('WwwArticle')->getWwwArticleCount($map);
        $result = [];
        if($count > 0) {
            $result['list'] = D('WwwArticle')->getWwwArticleList($map, ($pageIndex - 1) * $pageCount, $pageCount, 'a.addtime DESC');
            foreach ($result['list'] as $key => $value) {
                $result['list'][$key]["tagname"] = str_replace(",", " ", $value["tagname"]);
            }
            import('Library.Org.Util.Page');
            $page = new \Page($count,$pageCount);
            $result['page'] =  $page->show();
        }
        return $result;
    }

    public function disposeArticle($save){
        //将内容的前100个字作为描述
        $save['subtitle'] = mb_substr(strip_tags($save['content']), 0, 100, 'utf-8');

        // 处理关键字
        $save['keywords']  = implode(' ', array_unique(preg_split('/\s+/', trim($save['keywords']))));

        //处理文章中的图片
        $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png])?)[\'|\"].*?[\/]?>/";
        preg_match_all($pattern, $save['content'], $matches);
        $imgs = [];
        if ($matches !== false) {
            foreach ($matches[1] as $key => $value) {
                $imgs[] = urldecode($value);
            }
        }
        //请在文章内容中插入图片
        if (empty($imgs)) {
            return ['error_msg' => '请在文章内容中插入图片！', 'error_code' => 400];
        }
        $save['imgs'] = implode(',',$imgs);

        //判断是否有封面图，没有封面图默认取文章中的第一个图片
        if(empty($save['face'])){
            //替换掉域名和图片后缀
            $replace = ['http://'.C('QINIU_DOMAIN').'/','-s3.jpg'];
            $save['face'] = str_replace($replace, '', explode(',', $save['imgs'])[0]);
        }

        return $save;
    }

    public function disposeAddArticle($user_id, $class, $save)
    {
        $save['userid'] = $user_id;
        $save['optime'] = time();
        $save['pv'] = rand(1000, 2000);
        $save['likes'] = rand(500, 800);
        $save['createtime'] = time();
        $save['addtime'] = strtotime('+3 day');
        $save['state'] = 3;

        //记录初始状态
        $save['init_state'] = $save['state'];

        //新增操作
        $id = D('WwwArticle')->addWwwArticle($class, $save);

        if ($id) {
            /**S--处理文章中的关键字(内链)--**/
            //1-抽出所有的图片
            $pattern = '/<img.*?\/>/i';
            preg_match_all($pattern, $save['content'], $matches);
            if (count($matches[0]) > 0) {
                foreach ($matches[0] as $key => $value) {
                    //将图片替换成变量占位符
                    $save['content'] = str_replace($value, "##!&&", $save['content']);
                    $replaceImg[] = $value;
                }
            }
            //2-处理文章的关键字
            $keywords = D("WwwArticleKeywords")->getAllKeywords(1);
            shuffle($keywords);
            foreach ($keywords as $key => $value) {
                $arr[] = "/" . trim($value["name"]) . "/";
            }
            $i = 0;
            foreach ($arr as $key => $value) {
                if ($i == 8) {
                    break;
                }
                preg_match_all($value, $save["content"], $matches);
                if (count($matches[0]) > 0) {
                    $keywordsList[] = $keywords[$key]["id"];
                    $i++;
                }
            }
            //3-将所有的图片依次填充到原来位置
            foreach ($replaceImg as $key => $value) {
                $save['content'] = preg_replace("/##!&&/", $value, $save['content'], 1);
            }
            //4-添加关键字到关联表中
            if (count($keywordsList) > 0) {
                foreach ($keywordsList as $key => $value) {
                    $subData[] = array(
                        "article_id" => $id,
                        "keyword_id" => $value,
                        "module" => "wwwarticle"
                    );
                }
                M("keyword_relate")->addAll($subData);
            }
            /**E--处理文章中的关键字(内链)--**/

            //添加标签关联
            $service = new ElasticsearchServiceModel();
            $result = $service->getContentLabel($save['title'], 1);

            if (count($result) > 0) {
                foreach ($result as $item) {
                    $allLabel[] = [
                        "thematic_id" => $item["id"],
                        "content_id" => $id,
                        "module" => 1
                    ];
                }

                $thematicLogic = new ThematicWordsLogicModel();
                $thematicLogic->addThematicRelated($allLabel);
            }

            //添加操作日志
            $log = array(
                'remark' => '主站文章编辑',
                'logtype' => 'wwwarticle',
                'action_id' => $id,
                'info' => json_encode($save)
            );
            D('LogAdmin')->addLog($log);

            return ['error_code' => 0, 'error_msg' => '新增操作成功！'];
        }
    }

    public function disposeEditArticle($id, $class, $save)
    {
        //重新编辑则更新发布时间
        $save['addtime'] = strtotime('+3 day');
        //记录操作时间
        $save['optime'] = time();
        $result = D('WwwArticle')->editWwwArticle($id, $class, $save);
        if ($result) {
            //添加操作日志
            $log = array(
                'remark' => '主站文章发布',
                'logtype' => 'wwwarticle',
                'action_id' => $id,
                'action' => __CONTROLLER__ . "/" . __ACTION__,
                'info' => json_encode($save),
                'time' => time(),
                'ip' => get_client_ip()
            );
            D('LogAdmin')->addLog($log);
            return ['error_code' => 0, 'error_mas' => '编辑操作成功！'];
        }
        return ['error_code' => 400, 'error_mas' => '操作失败！'];
    }
}
