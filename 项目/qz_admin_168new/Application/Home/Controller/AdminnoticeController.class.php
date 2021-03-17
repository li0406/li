<?php

    /**
     * @Author: qz_chk
     * @Date:   2017-10-23 08:35:50
     * @Last Modified by:   qz_chk
     * @Last Modified time: 2017-12-11 08:41:34
     */
    //标准

    namespace Home\Controller;

    use Home\Common\Controller\HomeBaseController;

    class AdminnoticeController extends HomeBaseController
    {


        public function index()
        {
            $where = [];
            $page = max(1, I('get.p'));
            $pageSize = 20;
            if (I('get.title')) {
                $where['title'] = ['like', '%' . trim(remove_xss(I('get.title'))) . '%'];
            }
            $data = $this->getArticle($where, $page, $pageSize);
            $this->assign("info", $data);
            $this->display();
        }

        public function showArticle()
        {
            $id = I('post.id');
            $articleInfo = D("Articles")->getArticleInfo($id);
            if ($articleInfo) {
                $articleInfo['content'] = htmlspecialchars_decode($articleInfo['content']);
                $this->assign("notice",$articleInfo);
                $tmp = $this->fetch("noticetmp");
                $this->ajaxReturn(['status' => 1, 'info' => $tmp, 'head' => $articleInfo['title']]);
            } else {
                $this->ajaxReturn(['status' => 0, 'info' => '未查询到数据!']);
            }
        }

        public function notice(){
            if(IS_POST){
                $post = I('post.');
                $data = $this->checkData($post);
                if($post['edit_id']){
                    $status = D("Articles")->editArticlesInfo($post['edit_id'],$data);
                }else{
                    $status = D("Articles")->setArticlesInfo($data);
                }
                if ($status) {
                    $this->ajaxReturn(['status' => 1, 'info' => '操作成功!']);
                } else {
                    $this->ajaxReturn(['status' => 0, 'info' => '操作失败!']);
                }

            }else{
                //编辑
                $edit_id = I('get.edit_id');
                if($edit_id){
                    $id = I('post.id');
                    $articleInfo = D("Articles")->getArticleInfo($edit_id);
                    if($articleInfo){
                        $this->assign('article',$articleInfo);
                    }
                }
                $this->display();
            }

        }

        private function checkData($data)
        {
            return array(
                "title" => remove_xss(str_replace('，', ',', $data["title"])),
                "keywords" => '',
                "content" => htmlspecialchars_decode($data["content"]),
                "add_time" => time(),
                "author_id" => $_SESSION['uc_userinfo']["id"],
                "status" => 1,
                "author" => $_SESSION['uc_userinfo']["name"],
                "admin_ids" => '',
                "article_type" => 4
            );
        }

        private function getArticle($where, $p, $pageSize)
        {
            $count = D("Articles")->getArticlescount($where);
            if (count($count) > 0) {
                import('Library.Org.Page.Page');
                $config = array("prev", "first", "last", "next");
                $page = new \Page($p, $pageSize, $count, $config);
                $pageTmp = $page->show();
                $articles = D("Articles")->getArticles($where, $p, $pageSize);
                return ['page' => $pageTmp, 'articles' => $articles];
            }
        }
    }


