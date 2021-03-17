<?php
/**
 * 移动版 - 问答首页
 */
namespace Mobile\Controller;
use Common\Enums\CompanyHomeEnum;
use Common\Model\Logic\SpecialkeywordLogicModel;
use Common\Model\Logic\ThematicWordsLogicModel;
use Common\Model\Logic\UserLogicModel;
use Mobile\Common\Controller\MobileBaseController;

class WendaController extends MobileBaseController{
    public function _initialize(){
        parent::_initialize();
        $uri = $_SERVER['REQUEST_URI'];
        $SCHEME_HOST = $this->SCHEME_HOST;
        $parse = parse_url($SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'].$uri);
        if (empty($parse["query"])) {
            preg_match('/html$/',$uri,$m);
            if (count($m) == 0) {
                preg_match('/\/$/',$uri,$m);
                if (count($m) == 0) {
                    header( "HTTP/1.1 301 Moved Permanently");
                    header("Location: ". $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'].$uri."/");
                }
            }
        }
    }

    public function index(){

        $pageIndex = I('get.p');

        $cateId = I('get.id');
        $url = I('get.');

        //取所有分类
        $Data = D("Common/Ask");
        $category = $Data->getCategorys();

        //如果分类不为空
        if(!empty($cateId)){

            if(!is_numeric($cateId)){
                $this->_error();
            }

            //取当前分类信息
            $theCategory = $Data->getCategorys($cateId);
            //取当前分类的父分类,如果父分类是0，说明本身就是父分类
            $parentId = $theCategory['pid'] == '0' ? $cateId : $theCategory['pid'];
            //根据父分类取子分类并高亮显示
            $subCategory = $category[$parentId]['sub_cate'];
            foreach ($subCategory as $k => $v){
                if($v['cid'] == $cateId){
                    $subCategory[$k]['cls'] = ' class="ask-active"';
                }

                $subCategory[$k]['name'] = rtrim(mb_substr($v['name'],0,5),'/');
            }

            //取所有父分类
            foreach ($category as $k => $v){
                if($v['cid'] == $parentId){
                    $info['category_name'] = $v['name'];
                    $category[$k]['cls'] = ' class="ask-active"';
                }
                unset($category[$k]['sub_cate']);
            }
            $info['category_id'] = $theCategory['pid'];
            $info['sub_category_name'] = $theCategory['name'];
            $info['sub_category_id'] = $theCategory['cid'];

            if(empty($info['category_name'])){
                $this->_error();
            }

            $info['SEO_title'] = $theCategory['title'];
            $info['SEO_keywords'] = $theCategory['keywords'];
            $info['SEO_description'] = $theCategory['description'];


            //根据徐交SEO方案更改
            if($info['category_name'] == $info['sub_category_name']){
                $info['SEO_category'] = $info['category_name'];
                $info['SEO_level'] = 1;
            }else{
                $info['SEO_category'] = $info['sub_category_name'];
            }
            $condition['cateId'] = $cateId;
            $this->assign("subCategory",$subCategory);
        }


        //如果关键字不为空
        $keyword = I('get.keyword');
        if(!empty($keyword) || isset($url['keyword'])){
            if(!checkKeyword($keyword)){
                $this->_error();
            }
            $condition['keyword'] = $keyword;
            $info['title'] = $keyword;
            $info['position'] = '“'.$keyword.'”的搜索结果';
            $template = 'list';
        }

        //如果动作不为空
        $action = I('get.action');
        //如果请求为：热门，新增，无人，隐藏筛选条件
        if(!empty($action)){
            if($action == 'dist'){
                $condition['orderBy'] = 'a.post_time DESC';
                $condition['dist'] = array("EQ",1);
                $info['title'] = '精华问题';
            }
            if($action == 'hot'){
                $condition['orderBy'] = 'a.anwsers DESC';
                $condition['anwsers'] = array("GT",1);
                $info['title'] = '热门回答';
            }
            if($action == 'new'){
                $condition['orderBy'] = 'a.post_time DESC';
                $info['title'] = '新提问';
            }
            if($action == 'unanswer'){
                $condition['adopt_time'] = array("EQ",0);
                $condition['orderBy'] = 'a.post_time DESC';
                $info['title'] = '等您来回答';
            }

            $template = 'list';

            if($action == 'newanswer'){
                $condition['anwsers'] = array("NEQ",0);
                $condition['orderBy'] = 'a.last_time DESC';
                $info['title'] = '新回答';
                $template = 'newanswer';
            }

            $info['position'] = $info['title'];
        }else{
            //对问题排序进行处理
            $orderBy = I('get.sort');
            if(!empty($orderBy) and $orderBy =='anwsers'){
                $condition['orderBy'] = 'a.anwsers DESC';
                $info['orderBy'] = '<span><a rel="nofollow" href="'.$this->url('orderby','anwsers','').'">按时间排序<i class="ml10 icon-sort"></i></a></span>
                <span><a rel="nofollow" class="active" href="'.$this->url('orderby','anwsers','anwsers').'">按回答数排序<i class="ml10 icon-sort"></i></a></span>';
            }else{
                $condition['orderBy'] = 'a.post_time DESC ';
                $info['orderBy'] = '<span><a rel="nofollow" class="active" href="'.$this->url('orderby','time','').'">按时间排序<i class="ml10 icon-sort"></i></a></span>
                <span><a rel="nofollow" href="'.$this->url('orderby','time','anwsers').'">按回答数排序<i class="ml10 icon-sort"></i></a></span>';
            }
            //对问题状态进行处理
            $status = I('get.status');
            //状态不为空
            if(is_numeric($status) && isset($status) && $status <= 3 && $status >= 0){
                if ($status =='1'){
                    $condition['status'] = '0';
                    $info['status_menu'] = '<option value="0">全部</option><option value="1" selected>待解决</option><option value="2">已解决</option>';
                } elseif ($status =='2'){
                    $condition['status'] = '1';
                    $info['status_menu'] = '<option value="0">全部</option><option value="1" >待解决</option><option value="2"  selected>已解决</option>';
                } else {
                    $info['status_menu'] = '<option value="0">全部</option><option value="1" >待解决</option><option value="2" >已解决</option>';
                }
            }else{
                if (!empty($status)) {
                    $this->_error();
                }else{
                    $info['status_menu'] = '<option value="0" selected>全部</option><option value="1">待解决</option><option value="2">已解决</option>';
                }
            }
        }

        $template = 'index';

        if(empty($pageIndex)){
            $pageIndex = 1;
        }
        $pageContent ="第".$pageIndex."页";
        $pageCount = 10;

        if(!empty($keyword) || isset($url['keyword'])){
            if(!checkKeyword($keyword)){
                $this->_error();
            }
            $condition['keyword'] = $keyword;
            $template = 'searchresult';
            $info['keyword'] = $keyword;
        }



        // feature-917  调用逻辑从回答数最多调整为时间最新
        $condition['orderBy'] = 'a.post_time DESC';

        //如果分类不为空
        if(!empty($cateId)){
            if(is_numeric($cateId)){
                $condition['cateId'] = $cateId;
                $condition['orderBy'] = 'a.post_time DESC';
            }
        }

        $result = $this->getAskList($condition,$pageIndex,$pageCount);
        foreach ($result['list'] as $key => $value) {
            if(
                strpos($value["logo"], CompanyHomeEnum::COMPANY_DEFAULT_LOGO_ONE) !== false ||
                strpos($value["logo"], CompanyHomeEnum::COMPANY_DEFAULT_LOGO_TWO) !== false ||
                strpos($value["logo"], CompanyHomeEnum::COMPANY_DEFAULT_LOGO_THREE) !== false ||
                !$value['logo']
            ){
                $result['list'][$key]['logo'] = changeImgUrl(CompanyHomeEnum::COMPANY_DEFAULT_LOGO);
            }else{
                $result['list'][$key]['logo'] = changeImgUrl($value['logo'], 2);
            }
        }

        //Ajax Return
        if(I('get.act') == 'ajax'){
            $list = $result['list'];
            if(empty($list)){
                die('empty');
            }

            foreach ($list as $k => $v) {
                $html .= '
                <div class="zx-ask-item">
                    <div class="item-content">
                        <div class="item-top oflow">
                            <div class="user-img fl"><img src="'.$v['logo'].'" alt="'.$v['username'].'" /></div>
                            <div class="user-name fl">'.$v['username'].'</div>
                        </div>
                        <p class="item-body">
                            <a href="/wenda/x'.$v['id'].'.html">'.$v['title'].'</a>
                        </p>
                        <div class="item-foot">
                            <span class="fl">'.date('Y-m-d',$v['post_time']).'</span>
                            <span class="fr"><a href="/wenda/ask-'.$v['cid'].'">'.$v['name'].'</a><i class="sm-line-y"></i><i>'.$v['anwsers'].'</i>人回答</span>
                        </div>
                    </div>
                </div>';
            }
            echo $html;
            die();
        }

        if(empty($result['list'])){
            $map['orderBy'] = 'a.views DESC';
            $hotAsk = D('Ask')->getCategoryAsk($map,4);
            $this->assign("hotAsk",$hotAsk);
            $template = 'searchno';
        }

        //设置canonical属性
        if(!empty($action)){
            if(I('get.p') == '' || I('get.p') == 1){
                switch ($action) {
                    case 'dist':
                        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').'/wenda/dist';
                        break;
                    case 'newanswer':
                        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').'/wenda/newanswer';
                        break;
                    case 'unanswer':
                        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').'/wenda/unanswer';
                        break;
                    case 'hot':
                        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').'/wenda/hot';
                        break;
                    case 'new':
                        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').'/wenda/new';
                        break;
                    default:
                        break;
                }
            }
        }elseif('search' != $template && !isset($_GET['sort']) && !isset($_GET['status'])){
            if(I('get.p') == '' || I('get.p') == 1){
                $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').'/wenda/ask-'.$cateId;
            }else{
                $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').'/wenda/ask-'.$cateId.'?p='.I('get.p');
            }
        }

        if(empty($cateId)){
            $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').'/wenda/';
        }

        if(empty($info['SEO_title'])){
            $info['SEO_title'] = '齐装网装修问答平台 - 中国领先的装修问答网';
            $info['SEO_keywords'] = '装修问答,装修问答网,装修问题';
            $info['SEO_description'] = '齐装网装修问答平台为业主提供家庭室内装修以及公共装修过程中遇到的各种问题及解决办法。为业主提供即时便捷及专业的装修问题解决方法。';
        }
        $info['pageid'] = $pageIndex;
        $info['count'] = $result['num'];
        $this->assign("cateId", $cateId);
        $this->assign('source',324);
        $this->assign("category",$category);
        $this->assign("list",$result['list']);
        $this->assign("page",$result['page']);
        $this->assign("info",$info);
        $this->display($template);
    }

    //问题查看页
    public function show(){
        $qid = I('get.id');
        if(empty($qid) || !is_numeric($qid)){
            $this->_error();
        }

        $Data = D("Common/Ask");

        $ask = S('Cache:Mobile:Wenda:question:'.$qid);
        if(!$ask){
            $ask = $Data->getAskById($qid);
            if (empty($ask)) {
                $this->_error();
            }
            S('Cache:Mobile:Wenda:question:' . $qid, $ask, 900);
        }

        //处理头像
        $ask['logo'] = changeImgUrl($ask['logo'], 2);
        if(
            strpos($ask['logo'], CompanyHomeEnum::COMPANY_DEFAULT_LOGO_ONE) !== false ||
            strpos($ask['logo'], CompanyHomeEnum::COMPANY_DEFAULT_LOGO_TWO) !== false ||
            strpos($ask['logo'], CompanyHomeEnum::COMPANY_DEFAULT_LOGO_THREE) !== false ||
            empty($ask['logo'])
        ){
            $ask['logo'] = changeImgUrl(CompanyHomeEnum::COMPANY_DEFAULT_LOGO);
        }else{
            $ask['logo'] = changeImgUrl($ask['logo'], 2);
        }

        $userLogic = new UserLogicModel();
        //获取老用户信息,兼容C端用户中心
        if (empty($ask['logo']) || empty($ask['username'])) {
            $ask = $userLogic->compatibilityOldUserInfo('uid', $ask);
        }

        //取一级和二级分类
        $categorys = $Data->getCategorys();
        $category = $categorys[$ask['cid']];
        unset($categorys);
        $ask['category_name'] = $category['name'];
        foreach ($category['sub_cate'] as $key => $value) {
            if($value['cid'] == $ask['sub_category']){
                $ask['sub_category_name'] = $value['name'];
                break;
            }
        }

        //取问题图片列表
        $ask['img'] = S('Cache:Mobile:Wenda:question:img:'.$qid);
        if(!$ask['img']){
            $ask['img'] = $Data->getQuestionImg($qid);
            S('Cache:Mobile:Wenda:question:img:'.$qid,$ask['img'],900);
        }

        //根据问题ID取所有答案图片
        $tempImgList = $Data->getAnwserImg($qid);
        foreach ($tempImgList as $k => $v) {
            $imgList[$v['fid']][] = $v;
        }
        unset($tempImgList);

        //取最佳答案
        //此处不应查询数据库，应根据QID取所有答案后再处理
        if($ask['best_aid'] !== null){
            $bestAnwser = $Data->getAnwserById($ask['best_aid']);
            //如果最佳答案没有被删除
            if(!empty($bestAnwser)){
                $bestAnwser['post_time'] = timeFormat($bestAnwser['post_time']);
                $bestAnwser['name'] = empty($bestAnwser['jc']) ? $bestAnwser['name'] : $bestAnwser['jc'];
                $bestAnwser['url'] = $bestAnwser['classid'] == '3' ? $this->SCHEME_HOST['scheme_full'].$bestAnwser['bm'].'.'. C('QZ_YUMING') .'/wenda/'.$bestAnwser['uid'] : 'javascript:;';
                //取图片列表
                $bestAnwser['img'] = $imgList[$bestAnwser['id']];
                $this->assign("bestAnwser",$bestAnwser);
            }
        }

        //取答案 - 排除最佳答案
        $anwserList = $Data->getAnwserList($qid,'a.post_time DESC',$ask['best_aid']);
        foreach ($anwserList as $k => $v) {
            $anwserList[$k]['post_time'] = timeFormat($v['post_time']);
            $anwserList[$k]['img'] = $imgList[$v['id']];
            $anwserList[$k]['url'] = 'javascript:;';
        }

        $anwserList = $userLogic->compatibilityOldUserInfo('old_userid', $anwserList);
        foreach ($anwserList as $k => $v) {
            if(
                strpos($v["logo"], CompanyHomeEnum::COMPANY_DEFAULT_LOGO_ONE) !== false ||
                strpos($v["logo"], CompanyHomeEnum::COMPANY_DEFAULT_LOGO_TWO) !== false ||
                strpos($v["logo"], CompanyHomeEnum::COMPANY_DEFAULT_LOGO_THREE) !== false ||
                !$v['logo']
            ){
                $anwserList[$k]['logo'] = changeImgUrl(CompanyHomeEnum::COMPANY_DEFAULT_LOGO);
            }else{
                $anwserList[$k]['logo'] = changeImgUrl($v['logo'], 2);
            }
        }

        //总答案数
        $ask['anwsers'] = $ask['best_aid'] == null ? count($anwserList) : count($anwserList) + 1;
        if(empty($ask['description'])){
            $ask['description'] = mbstr($ask['content'],0,120);
        }
        $ask['post_time'] = date('Y-m-d H:i:s',$ask['post_time']);
        $ask['anwserCount'] = count($anwserList);
        $ask['modify_time'] = empty($ask['modify_time']) ? '' : '<li>最后修改：'.date('Y-m-d H:i:s',$ask['modify_time']).'</li>';

        //相关问题
        //$relatedAnwser = $this->getRelatedAnwser($ask['cid'],6);
        $relatedAnwser = D('Ask')->getRelationQuestion($ask['tags'],$ask['keywords'],$ask['sub_category'],$ask['cid'],6,$ask['id']);

        //为您推荐
        //调取新内容管理-视频管理里面的最新推荐视频
        $video = S('Cache:Mobile:Wenda:Show:Video');
        if(!$video){
            $video = D('Ask')->getNewVideo();
            S('Cache:Mobile:Wenda:Show:Video',$video,900);
        }


        // 最新问答
        $newestList = $this->getNewestAsk(6, $qid);
        $this->assign("newestlist", $newestList);

        //增加查看数
        $Data->updateViews($qid);

        //熊掌号
        $baidu['optime'] = date("Y-m-d",strtotime($ask['post_time']))."T".date("H:i:s",strtotime($ask['post_time']));
        $this->assign('baidu',$baidu);

        //dump($relatedAnwser);
        $info['header']['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').'/wenda/x'.$qid.'.html';


        //获取先关标签
        $keywords = S('Cache:Mobile:W:Q:Keywords:'.$qid);
        if(!$keywords){
            //获取先关标签
            $thematicLogic = new ThematicWordsLogicModel();
            $keywords = $thematicLogic->getContentLabels($qid,1,5);
            S('Cache:Mobile:W:Q:Keywords:'.$qid,$keywords,900);
        }
        $this->assign("keywords",$keywords);

        $this->assign("info", $info);
        $this->assign("category",$category);
        $this->assign("ask",$ask);
        $this->assign("video",$video);
        $this->assign("anwserList",$anwserList);
        $this->assign("related",$relatedAnwser);
        $this->display('details');
    }

    // 获取最新问答
    private function getNewestAsk($limit, $id)
    {
        $map = [];
        if ($id > 0) {
            $map["id"] = array("neq", $id);
        }
        $newestAnswerList = D("Ask")->getQuestionList($map,'post_time Desc',0,$limit);
        return $newestAnswerList ? $newestAnswerList : [];
    }

    //取问答列表
    private function getAskList($condition,$pageIndex = 1,$pageCount = 10){
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $pageIndex = intval($pageIndex)<=0?1:intval($pageIndex);
        import('Library.Org.Page.Page');
        $result = D("Common/Ask")->getQUCList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];

        //获取老用户信息,兼容C端用户中心
        $userLogic = new UserLogicModel();
        $list = $userLogic->compatibilityOldUserInfo('old_userid',$list);
        $config  = array("prev","first","last","next");
        $page = new \Page($pageIndex,$pageCount,$count,$config);
        $pageTmp =  $page->show();
        return array("list"=>$list,"page"=>$pageTmp,"num"=>$count);
    }

    //获取相关问题，按分类ID缓存15分钟
    private function getRelatedAnwser($cid,$num){
        $result = S('Cache:Ask:related:cate'.$cid);
        if(empty($result)){
            S('Cache:Ask:related:cate'.$cid,null);
            $condition['orderBy'] = 'a.post_time DESC';
            $condition['cateId'] = $cid;
            $relatedAnwser = D("Common/Ask")->getQListByCategory($condition,0,40);
            $result =$relatedAnwser['result'];
            unset($relatedAnwser);
            S('Cache:Ask:related:cate'.$cid,$result,900);
        }
        //如果当前结果小于要取的结果
        if(count($result) <= $num){
            foreach($result as $k->$v){
                $result[$k]['post_time'] = date('Y-m-d H:i:s',$v['post_time']);
            }
            return $result;
        }
        $s = array_rand($result,$num);
        foreach($s as $val){
            $val['post_time'] = date('Y-m-d H:i:s',$val['post_time']);
            $newResult[] = $result[$val];
        }
        return $newResult;
    }

    //生成链接URL
    private function url($type='orderby',$str1='',$str2=''){
        $url = $_SERVER['REQUEST_URI'];

        if($type == 'orderby'){
            if(strpos($url,'anwsers') || strpos($url,'time')){
                $url = str_replace($str1,$str2,$url);
            }else{
                if(strpos($url,'?p=')){
                    //带有页码参数时，按时间和按回答数排序会拼接在页码前面
                    $old = '?p=';
                    $new = $str2.'?p=';
                    $url = str_replace($old,$new,$url);
                }else{
                    $parseUrl = parse_url($url);
                    if (!empty($parseUrl["query"])) {
                        $url = $parseUrl["path"].$str2."?".$parseUrl["query"];
                    }else{
                        $url = $url.''.$str2;
                    }
                }
            }

        }
        return $url;
    }
}