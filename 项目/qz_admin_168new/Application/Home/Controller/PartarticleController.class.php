<?php

//兼职文章

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;
use Home\Model\Logic\PartarticleLogicModel;
use Home\Model\Logic\ThematicWordsLogicModel;
use Home\Model\Service\ElasticsearchServiceModel;

class PartarticleController extends HomeBaseController {

    private $partarticleLogic;

    public function _initialize(){
        parent::_initialize();

        $this->partarticleLogic = new PartarticleLogicModel();
    }

    /**
     * 文章列表
     */
    public function index()
    {
        $data = I('get.');
        $pageIndex = 1;
        if(!empty($data['p'])){
            $pageIndex = $data['p'];
        }
        // 获取兼职编辑用户列表
        $data['user_id'] = [$this->User["id"]];
        if ($this->User["uid"] != PartarticleLogicModel::PART_ROLE_ID) {
            $info["editUsers"] = $this->partarticleLogic->getPartUser();
            $data['user_id'] = array_column($info["editUsers"], "id");
        }
        if(count($data['user_id']) > 0){
            $map = $this->partarticleLogic->getPartArticleMap($data);
            $info = $this->partarticleLogic->getPartArticleList($map, $pageIndex);
        }
        $info['articleclass'] = json_encode(D('WwwArticleClass')->getArticleClassTree(false));
        //获取全部定制的分类
        $result =  D('WwwArticleClass')->getArticleClassIdsByClass(418);
        $qwdz = array_column($result,"id");
        $info['qwdz'] = $qwdz;
        $this->assign('info',$info);
        $this->display();
    }

    // 兼职编辑文章统计分析
    public function articleanalysis(){
        // 获取一级分类
        $categorytree = D("WwwArticleClass")->getArticleClassListByLevel(0, 1, 1);
        $this->assign("bigCate", $categorytree);

        // 获取兼职编辑用户列表
        $userIds = [$this->User["id"]];
        $info["ispart"] = 1;
        if ($this->User["uid"] != PartarticleLogicModel::PART_ROLE_ID) {
            $info["editUsers"] = $this->partarticleLogic->getPartUser();
            $userIds = array_column($info["editUsers"], "id");
            $info["ispart"] = 0;
        }

        // 分页
        $pageIndex = 1;
        $pageCount = 20;
        if (!empty($_GET["p"])) {
            $pageIndex = $_GET["p"];
        }

        $querymaps = $this->partarticleLogic->getArticleanalysisMap($userIds);
        $map = $querymaps["map"];
        $this->assign("orders", $querymaps["order"]);

        if (I("get.dl") == "1") {
            $pageCount = 3000;
            $result = $this->partarticleLogic->getArticleAnalysisList($map, $pageIndex, $pageCount);
            $result = $this->partarticleLogic->setAnalysisFormatter($result);
            $this->downExcel($result);
            die;
        }

        $result = $this->partarticleLogic->getArticleAnalysisList($map, $pageIndex, $pageCount);
        $result = $this->partarticleLogic->setAnalysisFormatter($result);

        // 按时间取订单信息
        $orderNums = D("MarketContent")->getOrdersNumByTime($map);
        $info["orderNum"] = $orderNums["order_num"];
        $info["fendanNum"] = $orderNums["fendan_num"];
        $info['real_fen_num'] = $result['real_fen_num'];

        $info['firstType'] = I('get.firstType');
        $info['secondType'] = I('get.secondType');
        $info['thirdType'] = I('get.thirdType');

        // 子分类
        if (!empty($info['firstType'])) {
            $secondCate = D('WwwArticleClass')->getArticleClassListByLevel($info['firstType'], 2, 1);
        }

        if (!empty($info['secondType'])) {
            $thirdCate = D('WwwArticleClass')->getArticleClassListByLevel($info['secondType'], 3, 1);
        }

        if (empty($result['totaluv'])) {
            $result['totaluv'] = 0;
        }
        if (empty($result['totalview'])) {
            $result['totalview'] = 0;
        }
        if (empty($result['totalnum'])) {
            $result['totalnum'] = 0;
        }
        if (empty($info['orderNum'])) {
            $info['orderNum'] = 0;
        }
        if (empty($info['fendanNum'])) {
            $info['fendanNum'] = 0;
        }

        if (empty($info['real_fen_num'])) {
            $info['real_fen_num'] = 0;
        }

        $info["uid"] = I("get.uid");
        $info["release"] = I("get.release");

        $this->assign('secondCate', $secondCate);
        $this->assign('thirdCate', $thirdCate);
        $this->assign("totaluv", $result['totaluv']);
        $this->assign("totalview", $result['totalview']);
        $this->assign("totalnum", $result['totalnum']);
        $this->assign("list", $result['list']);
        $this->assign('page', $result['page']);
        $this->assign('info', $info);
        $this->display();
    }

    /**
     * 新增与编辑文章
     */
    public function operate()
    {
        if (IS_POST) {
            //文章新增编辑
            $data = I('post.');
            $partLogic = new PartarticleLogicModel();
            if (!empty($data)) {
                $admin = getAdminUser();
                $save['tags'] = D('Tags')->getTagIdsByTagNames($data['tagnames']);
                $save['title'] = $data['title'];
                $save['item_name'] = $data['item_name'];
                $save['face'] = $data['face'];
                $save['keywords'] = str_replace('，', ',', $data['keywords']);
                $save['subtitle'] = $data['subtitle'];
                $save['isTop'] = $data['istop'];
                $save['opid'] = $admin['id'];
                $save['content'] = htmlspecialchars_decode($data['content']);
                $save['isxiongzhang'] = $data['isxiongzhang'];
                $save['short_introduction'] = $data['article-intro'];
                $save['is_wenda'] = $data['is_wenda'];
                $save['is_kejiwenda'] = $data['is_kejiwenda'];
                $save['brand_model'] = $data['brand_model'];
                $save['app_version'] = $data['app_version'];
                $save['system_version'] = $data['system_version'];

                $id = $data['id'];
                $class = $data['classid'];

                $wwwArticleLogic = new \Home\Model\Logic\WwwArticleLogicModel();
                if (($valiMessage = $wwwArticleLogic->partArticleValidate($data)) !== true) {
                    $this->ajaxReturn(['error_msg' => $valiMessage, 'error_code' => 400]);
                }
                $save = $partLogic->disposeArticle($save);
                if($save['error_code'] == 400){
                    $this->ajaxReturn($save);
                }
                //新增文章
                if (empty($id)) {
                    $status = $partLogic->disposeAddArticle($admin['id'],$class,$save);
                } else {
                    $status = $partLogic->disposeEditArticle($id,$class,$save);
                }
                $this->ajaxReturn($status);
            }
        }else{
            $id = I('get.id');
            $info = [];
            if(!empty($id)){
                $info['info'] = D('WwwArticle')->getWwwArticleById($id);
                if(!empty($info['info']['face'])){
                    $info['info']['cover'] = "'".'<img src="//'.C('QINIU_DOMAIN').'/'.$info['info']['face'].'">'."'";
                }
                //验证用户能否看到当前文章
                if($this->User["uid"] == PartarticleLogicModel::PART_ROLE_ID){
                    if($info['info']['userid'] != $this->User['id']){
                        $this->error('无权查看其他账户的文章，如有疑问请联系齐装管理人员');
                    }
                    if($info['info']['state'] == 3){
                        $info['save_btn'] = 1;
                    }
                }
            }else{
                if($this->User["uid"] == PartarticleLogicModel::PART_ROLE_ID){
                    $info['save_btn'] = 1;
                }
            }
            if(!empty($info)){
                import('Library.Org.Util.Fiftercontact');
                $filter = new \Fiftercontact();
                $info['info']['content'] = $filter->filter_empty($info['info']['content']);
            }
            $info['categorytree'] = json_encode(D('WwwArticleClass')->getArticleClassTree());
            $this->assign('info',$info);
        }

        $this->display();
    }

    public function verifyTitle()
    {
        $title = I('get.title');
        $id = I('get.id');
        if(!empty($title)){
            $result = D('WwwArticle')->getWwwArticleByTitle($title,$id);
            if(count($result) > 0){
                $this->ajaxReturn(array('data'=>'','info'=>'已存在该标题的文章！','status'=>1));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'暂无此文章！','status'=>0));
    }

    /**
     * 编辑文章状态
     */
    public function editState()
    {
        $id = I('post.id');
        $state = I('post.state');
        if(in_array($state, array('-1','1','2','3'))){
            $info = D('WwwArticle')->getWwwArticleById($id);
            //预发布改为发布，则发布时间更改为当前时间
            if ('3' == $info['state'] && '3' != $state) {
                $save = array(
                    'addtime' => time(),
                    'state' => $state
                );
            } else {
                $save = array(
                    'state' => $state
                );
            }

            $result = D('WwwArticle')->editWwwArticleById($id, $save);
            if($result){
                //修改标签数量
                if (in_array($info['state'], array('-1', '1', '3'))) {
                    //不可见->可见
                    if (in_array($state, array('2'))) {
                        D('Tags')->editTagCountByTagIds('', $info['tags'], 'article_count');
                    }
                    //初始可见
                } else {
                    //可见->不可见
                    if (in_array($state, array('-1', '1', '3'))) {
                        D('Tags')->editTagCountByTagIds($info['tags'], '', 'article_count');
                    }
                }
                //添加操作日志
                $log = array(
                    'remark' => '主站文章改变审核状态',
                    'logtype' => 'wwwarticle',
                    'action_id' => $id,
                    'action' => __CONTROLLER__."/".__ACTION__,
                    'info' => $state,
                    'time' => time(),
                    'ip' => $ip
                );
                D('LogAdmin')->addLog($log);

                $this->ajaxReturn(array('data'=>'','info'=>'操作成功！','status'=>1));
            }
        }
        $this->ajaxReturn(array('data'=>'','info'=>'操作失败！','status'=>0));
    }


    private function downExcel($list){
        import('Library.Org.Phpexcel.PHPExcel', "", ".php");
        import('Library.Org.Phpexcel.PHPExcel.Writer.Excel2007', "", ".php");
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
        $cacheSettings = array('cacheTime' => 300);
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        $phpExcel = new \PHPExcel();

        //设置表头
        $title = array(
            '编号',
            '标题',
            '关键词',
            'URL',
            '创建时间',
            '正式发布时间',
            '发布人',
            '分类',
            'UV量',
            'IP量',
            '发单量',
            '分单量',
            '实际分单量',
            '发布状态'
        );

        $i = 0;
        foreach ($title as $key => $value) {
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . 1;
            $phpExcel->getActiveSheet()->setCellValue($num, (string)$value);
        }
        //设置表内容
        $j = 1;
        foreach ($list['list'] as $k => $v) {
            //初始化$i
            $i = 0;

            $url = 'http://www.' . C('QZ_YUMING') . '/gonglue/' . $v['shortname'] . '/' . $v['id'] . '.html';

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num, (string)$v['id']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num, (string)$v['title']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num, (string)$v['keywords']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num, (string)$url);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num, (string)date('Y-m-d H:i:s', $v['createtime']));

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num, (string)date('Y-m-d H:i:s', $v['addtime']));

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num, (string)$v['uname']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num, (string)$v['classname']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num, (string)$v['search_ip']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num, (string)$v['realview']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num, (string)$v['order_num']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num, (string)$v['fendans']);

            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num, (string)$v['real_fen_num']);

            if ($v['state'] == 2) {
                $type = '已发布';
            } else if ($v['state'] == 3) {
                $type = '预发布';
            } else {
                $type = '未发布';
            }
            $num = \PHPExcel_Cell::stringFromColumnIndex($i++) . '' . ($j + 1);
            $phpExcel->getActiveSheet()->setCellValue($num, (string)$type);

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
        header('Content-Disposition:attachment;filename="兼职业绩统计.xls"');
        header("Content-Transfer-Encoding:binary");
        $writer = new \PHPExcel_Writer_Excel2007($phpExcel);
        $writer->save('php://output');
        exit();
    }
}
