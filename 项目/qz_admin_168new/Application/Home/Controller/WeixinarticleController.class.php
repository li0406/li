<?php

namespace Home\Controller;

use Home\Common\Controller\HomeBaseController;

class WeixinarticleController extends HomeBaseController{

    /**
     * 文章列表
     */
    public function index()
    {
        $start = I('get.p') > 0 ? intval(I('get.p')) : 1;
        $each = 20;
        $count = D('WeixinArticle')->getWeixinArticleCount(1, 0);
        if($count > $each){
            import('Library.Org.Util.Page');
            $page = new \Page($count,$each);
            $vars['page'] =  $page->show();
            $start = $page->nowPage;
        }
        $vars['info'] = D('WeixinArticle')->getWeixinArticleList(1, 0, ($start - 1)*$each,$each);
        $vars['count'] = $count;
        $this->assign('vars', $vars);
        $this->display();
    }

    /**
     * 编辑文章
     */
    public function operate(){
        if (IS_POST) {
            $id = I('post.id');
            $save = array(
                'category' => 1,
                'title' => I('post.title'),
                'description' => I('post.description'),
                'url' => I('post.url'),
                'face' => I('post.face'),
                'update_time' => date('Y-m-d H:i:s')
            );
            if (empty($id)) {
                $save['recommend'] = 2;
                $save['status'] = 1;
                $save['add_time'] = date('Y-m-d H:i:s');
                $result = D('WeixinArticle')->addWeixinArticle($save);
                if ($result) {
                    $this->ajaxReturn(array('status' => 1, 'info' => '新增成功！'));
                }
            } else {
                $result = D('WeixinArticle')->editWeixinArticle($id, $save);
                if ($result) {
                    $this->ajaxReturn(array('status' => 1, 'info' => '编辑成功！'));
                }
            }
            $this->ajaxReturn(array('status' => 0, 'info' => '操作失败'));
        }
        $id = intval(I('get.id'));
        if (!empty($id)) {
            $info = D('WeixinArticle')->getWeixinArticleById($id);
            if(!empty($info['face'])){
                $info['face_url'] = "'".'<img src="//'.C('QINIU_DOMAIN').'/'.$info['face'].'-w240.jpg">'."'";
            }
            $this->assign('info', $info);
        }
        $html = $this->fetch();
        $this->ajaxReturn(array('status' => 1, 'data' => $html));
    }

    /**
     * 删除文章
     */
    public function delete()
    {
        $id = I('post.id');
        if (empty($id)) {
            $this->ajaxReturn(array('status' => 0, 'info' => '待删除文章ID为空'));
        }
        $save = array(
            'status' => 2,
            'update_time' => date('Y-m-d H:i:s')
        );
        $result = D('WeixinArticle')->editWeixinArticle($id, $save);
        if ($result) {
            $this->ajaxReturn(array('status' => 1, 'info' => '编辑成功！'));
        }
        $this->ajaxReturn(array('status' => 0, 'info' => '操作失败'));
    }

    /**
     * 推荐文章
     */
    public function recommend()
    {
        $id = I('post.id');
        $recommend = I('post.recommend');
        if (empty($id)) {
            $this->ajaxReturn(array('status' => 0, 'info' => '文章ID为空'));
        }
        //新增推荐判断之前的推荐是否大于2个
        if ($recommend == 1) {
            $info = D('WeixinArticle')->getWeixinArticleList(1, 1, 0, 1000, 'update_time ASC');
            if (count($info) >= 2) {
                foreach ($info as $key => $value) {
                    if (($key + 1) == count($info)) {
                        break;
                    }
                    $save = array(
                        'recommend' => 2,
                        'update_time' => date('Y-m-d H:i:s')
                    );
                    D('WeixinArticle')->editWeixinArticle($value['id'], $save);
                }
            }
        }
        $save = array(
            'recommend' => $recommend,
            'update_time' => date('Y-m-d H:i:s')
        );
        $result = D('WeixinArticle')->editWeixinArticle($id, $save);
        if ($result) {
            $this->ajaxReturn(array('status' => 1, 'info' => '操作成功！'));
        }
        $this->ajaxReturn(array('status' => 0, 'info' => '操作失败'));
    }
}
