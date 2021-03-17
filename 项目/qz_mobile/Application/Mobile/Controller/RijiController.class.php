<?php
/**
 * 移动版 - 日记首页
 */
namespace Mobile\Controller;
use Mobile\Common\Controller\MobileBaseController;
class RijiController extends MobileBaseController{
    public function _initialize(){
        parent::_initialize();
        $uri = $_SERVER['REQUEST_URI'];
        preg_match('/html$/',$uri,$m);
        if (count($m) == 0) {
            preg_match('/\/$/',$uri,$m);
            $parse = parse_url($uri);
            if (count($m) == 0 && empty($parse["query"])) {
                $SCHEME_HOST = $this->SCHEME_HOST;
                header( "HTTP/1.1 301 Moved Permanently");
                header("Location: ". $SCHEME_HOST['scheme_full'].$SCHEME_HOST['host'].$uri."/");
            }
        }
    }

    public function index(){
        $Data = D("Common/Diary");

        $cid = I('get.category');

        //获取日记列表信息
        $diaryList = $Data->get_all_diary_list_api('0,8',$cid);
        foreach ($diaryList['list'] as $k => $v) {
            $imgNum = count($v['img_list']);
            if($imgNum > 4){
                $diaryList['list'][$k]['img_list'] = array_slice($v['img_list'], 0, 4);
            }
        }
        if (IS_AJAX) {
            $this->assign("list",$diaryList['list']);
            $content = $this->fetch('list-content');
            echo $content;
            die();
        }
        //dump($diaryList['list']);

        $category = $Data->get_diary_type();
        foreach ($category as $k => $v) {
            $category[$k]['px'] = 'px'.$v['id'];
        }
        $info['title'] = '全部';
        $info['px'] = 'px0';
        if(!empty($cid)){
            foreach ($category as $k => $v) {
                if($v['id'] == $cid){
                    $info['title'] = $v['type_name'];
                    $info['px'] = 'px'.$v['id'];
                }
            }

        }

        $p = intval(I("get.p"));
        $pageIndex = $p>=1 ? $p : 1;

        /*if(I('get.act') == 'ajax'){

            foreach ($diaryList['list'] as $k => $v) {
                $img = '';
                foreach ($v['img_list'] as $ko => $vo) {
                    $img .= '<img src="'.$this->SCHEME_HOST['scheme_full'].'staticqn.qizuang.com/'.$vo['img_path'].'" alt=""/>';
                }
                if($v['stage'] == '1'){
                    $diary_type = '<div class="diary-t1">准备阶段</div>';
                }elseif($v['stage'] == '2'){
                    $diary_type = '<div class="diary-t2">施工阶段</div>';
                }else{
                    $diary_type = '<div class="diary-t3">入住阶段</div>';
                }
                $html .= '<li>'.$diary_type.'<div class="riji-title"><a href="/riji/d'.$v['id'].'.html">'.$v['title'].'</a></div>
                    <div class="user-info"><img src="'.$v['user_info']['logo'].'-w240.jpg" alt=""/><span>'.$v['user_info']['name'].'</span><span>'.$v['pcount'].' 篇</span><span>'.timeFormat($v['diary_time']).' 更新</span></div>
                    <div class="diary-list-main">'.$v['content'].'</div>
                    <div class="diary-list-img">'.$img.'</div>
                    </li>
                ';
            }
            echo $html;
            die();
        }*/

        $info['pageid'] = $pageIndex;

        if($_SERVER['REQUEST_URI'] == '/riji/'||$_SERVER['REQUEST_URI'] == '/riji'){
            $info['canonical'] = '1';
        }
        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];
        //var_dump($info);
        $this->assign('source',323);//设置发单入口标识
        $this->assign("category",$category);
        $this->assign("list",$diaryList['list']);
        $this->assign("info",$info);
        $this->assign("pageid",$pageIndex);
        $this->assign("totalpage",$diaryList['totalpage']);
        $this->assign("redPacket",array('source' => 317));
        $this->display();
    }

    //日记查看页
    public function show(){
        $id = I('get.id');
        if(empty($id) || !is_numeric($id)){
            $this->_error();
        }

        $Data = D("Common/Diary");

        $info = $Data->get_one_diary_info($id);

        $info = $info[0];

        //根据风格查询美图最新
        $fengge = $info['fengge'];
        $meitu = D("Meitu")->getOneMeituByFengGe($fengge);
        $info['meitu'] = $meitu[0];


        //查询装修公司
        if(!empty($info['company_name'])){
            $company = $Data->getDiaryCompanyByName($info['company_name']);
            $info['company'] = $company[0];
        }

        //查询相关日记,同parent_id
        $p_map['id'] = array('NEQ',$info['id']);
        $p_map['parent_id'] = array('EQ',$info['parent_id']);
        $other_diary = $Data->get_diarys_by_parent_id($p_map);
        //var_dump($other_diary);

        if(empty($info) || $info["parent_id"] == 0){
            $this->_error();
            die();
        }

        $info['img_list'] = $Data->get_diary_img_by_id($id);
        //$info['diary_type_name'] = $Data->get_diary_type_name_by_id($info['second_type']);

        $info['user_info'] = M('user')->find($info['user_id']);

        /*//获取回复
        //$diary_comment=$Data->get_one_diary_comment($info['id']);
        //获取热门日记推荐日记
        $result = $Data->getOtherDiary($diary_detail_info['parent_id'],$id,6);
        if(count($result) > 0){
            foreach ($result as $key => $value) {
                $value['content'] = strip_tags($content);
                if(!array_key_exists($value["parent_id"], $other_diary_list)){
                    $other_diary_list[$value["parent_id"]] = $value;
                }
                $other_diary_list[$value["parent_id"]]["child"][] = $value;
            }
        }
        //如果其它日记为空
        if(empty($other_diary_list)){
            $other_diary_list = $Data->getMRandDiary($id,6);
        }
        //dump($other_diary_list);
        $this->assign("otherDiary",$other_diary_list);*/

        $keys["title"] = $info["title"]." - 齐装网装修日记";
        $keys["keywords"] = $info["title"];
        $keys["description"] =mb_substr($info["content"], 0,50,"utf-8");
        //日记访问量加1
        $Data->diary_page_view_add($id);

        //获取该城市第一个区，用于显示默认城市
        $info['cityarea'] = D('Quyu')->getAreaByFatherId(session('m_mapUseInfo.id'))[0];

        //百度官方熊掌号
        $baidu['url'] = $this->SCHEME_HOST['scheme_full'].$this->SCHEME_HOST['host'].$_SERVER['REQUEST_URI'];
        $baidu['title'] = mb_substr($info['title'], 0, 20, "utf-8");
        $baidu['description'] = mb_substr($info['content'], 0, 120, "utf-8");
        foreach($info['img_list'] as $key => $value){
            if($key < 3){
                $baidu['img'][] = $this->SCHEME_HOST['scheme_full'] . C('QINIU_DOMAIN') . '/' . $value['img_path'];
            }
        }
        $baidu['pubDate'] = date("Y-m-d H:i:s", $info['add_time']);
        $baidu['pubDate'] = substr($baidu['pubDate'], 0, 10) . "T" . substr($baidu['pubDate'], 11);
        $this->assign("baidu", $baidu);

        $info['canonical'] = $this->SCHEME_HOST['scheme_full'].C('QZ_YUMINGWWW').$_SERVER['REQUEST_URI'];

        $this->assign('info',$info);
        $this->assign('user_info',$user_info);
        $this->assign('otherdiary',$other_diary);
        $this->assign("keys",$keys);
        $this->display();
    }



    //取问答列表
    private function getAskList($condition,$pageIndex = 1,$pageCount = 10)
    {
        //过滤
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $pageIndex = intval($pageIndex)<=0?1:intval($pageIndex);
        import('Library.Org.Page.Page');
        $result = D("Common/Ask")->getQUCList($condition,($pageIndex-1) * $pageCount,$pageCount);
        $count = $result['count'];
        $list = $result['result'];
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

    //日记改版页面
    public function rijiDev()
    {
        $this->display();
    }
    //日记改版详情页面
    public function rijidetail()
    {
        $this->display();
    }

}
