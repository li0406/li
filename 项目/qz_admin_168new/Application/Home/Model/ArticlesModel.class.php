<?php
namespace Home\Model;

Use Think\Model;
class ArticlesModel extends Model{

    protected $autoCheckFields = false;

    public function getArticlescount($map){
        return M("erp_articles")->where($map)->alias("a")
                              ->field("a.article_id")
                              ->count();
    }

    /**
     * 编辑通知信息内容
     * @param  [type] $id   [通知信息ID]
     * @param  [type] $data [通知信息内容]
     * @return [type]       [description]
     */
    public function editArticlesInfo($id,$data){
        $m = M("erp_articles");
        $map = array(
                "article_id"=>array("EQ",$id)
                     );
        return M("erp_articles")->where($map)->save($data);
    }

    /**
     * 获取部门业务通知
     * @param  [type] $id [uid]
     * @param  [type] $type [文章类型]
     * @return [type]      [description]
     */
    public function getArticles($map,$pagefirst=1,$pagerows=10){
        return M("erp_articles")->where($map)
            ->field('article_id,title')
            ->order("add_time desc")
            ->page($pagefirst, $pagerows)
            ->group("article_id")
            ->select();
    }

    /**
     * 保存业务通知数据
     * @param [type] $data [description]
     */
    public function setArticlesInfo($data){
        $m = M("erp_articles");
        return $m->add($data);
    }

    /**
     * 获取文章信息
     * @param  [type] $id [文章id]
     * @return [type]     [description]
     */
    public function getArticleInfo($id){
            $map = array(
                "article_id"=>array("EQ",$id)
                         );
            return M("erp_articles")->where($map)->find();
    }

    /**
     * 添加阅读通知日志
     * @param  [type] $admin_id [用户id]
     * @param  [type] $id [消息id]
     * @return [type] [description]
     */
    public function setArticlelog($id,$admin_id){
        $map = array(
                "admin_id"=>array("EQ",$admin_id),
                "article_id"=>array("EQ",$id)
                     );
        $count = M("erp_articles_log")->where($map)->count();
        if($count == 0){
            $data = array(
                "admin_id"=>$admin_id,
                "article_id"=>$id,
                "addtime"=>time()
                     );
            return M("erp_articles_log")->add($data);
        }
    }
    /**
     * 删除通知信息
     * @param  [type] $article [文章对象]
     * @return [type]          [description]
     */
    public function delArticleInfo($article){
        $map = array(
                "article_id"=>$article["article_id"],
                "author_id"=>$article["author_id"]
                     );
        $data = array(
                "status" =>0
                      );
        return M("erp_articles")->where($map)->save($data);
    }

    /**
     * 获取阅读日志
     *  @param  [type] $id [通知ID]
     * @return [type] [description]
     */
    public function getNoticelog($id){
        $map["a.article_id"] = array("EQ",$id);
        return M("erp_articles")->alias("a")->where($map)
              ->join('left join qz_adminuser as b on FIND_IN_SET(b.id,a.admin_ids)')
              ->join("left join qz_erp_articles_log as c on b.id = c.admin_id and c.article_id = a.article_id")
              ->field("a.article_id,b.name,b.id,c.addtime")
              ->group("b.id")
              ->select();
    }

}
?>