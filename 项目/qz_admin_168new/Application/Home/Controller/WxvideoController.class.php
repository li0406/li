<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
*  小程序视频管理
*/
class WxvideoController extends HomeBaseController
{
    /**
     * 小程序视频--首页
     *
     */
    public function index()
    {
        if (I("get.title") !== "") {
            $map['title'] = I("get.title");
            $search["title"] = $map['title'];
        }
        if(I("get.theme") !== ""){
            $map['theme'] = I("get.theme");
            $search["theme"] = $map['theme'];
        }
        if(I("get.istop") !== ""){
            $map['istop'] = I("get.istop");
            $search["istop"] = $map['istop'];
        }
        if(I("get.isdelete") !== ""){
            $map['isdelete'] = I("get.isdelete");
            $search["isdelete"] = $map['isdelete'];
        }
        $map['order'] = '';
        if($_GET['viewsorder'] == 1){
            $map['order'] .= 'pv desc,';
        }
        if($_GET['pnumorder'] == 1){
            $map['order'] .= 'pnum desc,';
        }
        $result = $this->getVedioList($map);

        $i = 1;
        foreach ($result['list'] as $k => $v) {
            $result['list'][$k]['count'] = $i;
            $i++;
        }
        $this->assign("search",$search);
        $this->assign("list",$result);
        $this->display();
    }

    /**
     * 添加编辑视频
     * @return [type] [description]
     */
    public function vedioup()
    {
        if ($_POST) {
            $id = I("post.id");

            $wxapp_url = I("post.wxapp_url");
            if(!empty($wxapp_url)){
                /*
                http://www.miaopai.com/show/7TQOBWoFUeNt8M7xqQl5jxazeCxeq44z.htm
                http://gslb.miaopai.com/stream/7TQOBWoFUeNt8M7xqQl5jxazeCxeq44z.mp4
                */
                $parseUrl = parse_url($wxapp_url);
                if($parseUrl['host'] == 'www.miaopai.com'){
                    $wxapp_url = str_replace(array('http://www.miaopai.com/show/','.htm'),'',$wxapp_url);
                    $wxapp_url = 'http://gslb.miaopai.com/stream/'.$wxapp_url.'.mp4';
                }
            }

            $data = array(
                "title" => I("post.title"),
                "description" => I("post.description"),
                "theme" => I("post.theme"),
                "teacher" => I("post.teacher"),
                "cover_img" => I("post.bigimg"),
                "wxapp_url" => $wxapp_url,
                "istop" => I("post.recommend"),
                "isdelete" => I("post.newStatus"),
                "uid" => $this->User["id"],
                "uname" => $this->User["name"],
                "time" => time()
            );

            if (!empty($id)) {
                $id = D("ArticleVedio")->editWXVedio($id,$data);
            } else {
                $id = D("ArticleVedio")->addWxVedio($data);
            }
            if($id){
                $this->ajaxReturn(array('data'=>$id,"info"=>'发布成功！',"status"=>1));
            }else{
                $this->ajaxReturn(array('data'=>$id,"info"=>'发布失败，请重试！',"status"=>0));
            }
        } else {
            if (I("get.id") !== "") {
                $id = I("get.id");
                $result = D("ArticleVedio")->getWXVedioInfo($id);
                foreach ($result as $key => $value) {
                    if (!isset($info)) {
                        if (!empty($value["cover_img"])) {
                            $imgs = array('<img src="//'.OP("QINIU_DOMAIN")."/".$value["cover_img"].'"/>');
                            $json = json_encode($imgs);
                            $value["bigimg"] =  $json;
                        }

                        $info = $value;
                    }
                }
                $this->assign("info",$info);
            }
            //查询讲师
            $teachers = D("ArticleVedio")->getUsers();
            $this->assign('teachers',$teachers);
            $this->display();
        }
    }

    /**
     * 编辑视频
     *
     */
    public function editWXvedio()
    {
        if ($_POST) {
            $id = I("post.id");
            $value = I("post.value");
            if($value !== ''){
                $data = array(
                    "istop" => $value
                );
            }

            $istop = I("post.istop");
            if($istop !== ''){
                $data['istop'] = $istop;
            }
            $isdelete = I("post.isdelete");
            if($isdelete !== ''){
                $data['isdelete'] = $isdelete;
            }
            $i = D("ArticleVedio")->editWXVedio($id,$data);
            if ($i !== false) {
                $this->ajaxReturn(array("info"=>"操作成功！","status"=>1));
            }
            $this->ajaxReturn(array("info"=>"操作失败！","status"=>0));
        }
    }

    public function delvideo($value='')
    {
        if ($_POST) {
            $id = I("post.id");

            $i = D("ArticleVedio")->delWXVedio($id);
            if ($i !== false) {
                $this->ajaxReturn(array("info"=>"操作成功！","status"=>1));
            }
            $this->ajaxReturn(array("info"=>"操作失败！","status"=>0));
        }
    }

    /**
     * 查询微信视频
     * @param  array    $map       查询条件
     * @return array    $result    查询结果（带分页）
     */
    private function getVedioList($map)
    {
        $count = D("ArticleVedio")->getWXVedioCount($map);

        if(count($count) > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,10);
            $p->setConfig('header','个申请');
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $show = $p->show();
            $list = D("ArticleVedio")->getWXVedioList($p->firstRow,$p->listRows,$map);
            return array("page"=>$show,"list"=>$list);
        }
    }

    //小程序视频评论管理
    public function comment()
    {
        if (I("get.title") !== "") {
            $map['title'] = I("get.title");
            $search["title"] = $map['title'];
        }
        if(I("get.recommend") !== ""){
            $map['recommend'] = I("get.recommend");
            $search["recommend"] = $map['recommend'];
        }
        $result = $this->getWXCommentList($map);
        $i = 1;
        foreach ($result['list'] as $k => $v) {
            $result['list'][$k]['count'] = $i;
            $i++;
        }
        //var_dump($result);
        $this->assign("search",$search);
        $this->assign("list",$result);
        $this->display();
    }

    /**
     * 查询微信视频评论
     * @param  array    $map       查询条件
     * @return array    $result    查询结果（带分页）
     */
    private function getWXCommentList($map)
    {
        $count = D("ArticleVedio")->getWXCommentCount($map);

        if(count($count) > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,10);
            $p->setConfig('header','个申请');
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $show = $p->show();
            $list = D("ArticleVedio")->getWXCommentList($p->firstRow,$p->listRows,$map);
            return array("page"=>$show,"list"=>$list);
        }
    }

    /**
     * 微信视频评论通过审核
     * @param  array    $map       查询条件
     * @return array    $result    查询结果（带分页）
     */
    public function checkcomment()
    {
        if ($_POST) {
            $id = I("post.id");

            $data = array(
                "recommend" => I("post.value")
            );
            $i = D("ArticleVedio")->editWXComment($id,$data);
            if ($i !== false) {
                $this->ajaxReturn(array("status"=>1));
            }
            $this->ajaxReturn(array("info"=>"操作失败,请联系技术部门！","status"=>0));
        }
    }

    //小程序视频评论详情
    public function wxVideoComment()
    {
        $id = I("get.id");
        if(empty($id)){
            $this->_error('发布活动失败 :)');
        }
        if(I("get.title") !== "") {
            $map['title'] = I("get.title");
            $search["title"] = $map['title'];
        }
        if(I("get.recommend") !== ""){
            $map['recommend'] = I("get.recommend");
            $search["recommend"] = $map['recommend'];
        }
        $map['ref_id'] = $id;
        $result = $this->getWXComments($map);
        $i = 1;
        foreach ($result['list'] as $k => $v) {
            $result['list'][$k]['count'] = $i;
            $title = $v['title'];
            $i++;
        }
        //var_dump($result);
        $this->assign('title',$title);
        $this->assign("search",$search);
        $this->assign("list",$result);
        $this->display();
    }

    /**
     * 查询微信视频评论
     * @param  array    $map       查询条件
     * @return array    $result    查询结果（带分页）
     */
    private function getWXComments($map)
    {
        $count = D("ArticleVedio")->getWXVideoCommentCount($map);

        if(count($count) > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,10);
            $p->setConfig('header','个申请');
            $p->setConfig('prev', "上一页");
            $p->setConfig('next', '下一页');
            $p->setConfig('theme','%UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
            $show = $p->show();
            $list = D("ArticleVedio")->getWXVideoCommentList($p->firstRow,$p->listRows,$map);
            return array("page"=>$show,"list"=>$list);
        }
    }

}
