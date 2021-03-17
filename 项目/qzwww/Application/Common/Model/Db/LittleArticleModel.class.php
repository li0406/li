<?php
/**分站专题页*/
namespace Common\Model\Db;
use Think\Model;
class LittleArticleModel extends Model
{
    protected $tableName = 'little_article';

    public function getArticleList($tagid,$cs,$limit=6){
        $where['state'] = ['eq',2];
        $where['cs'] = ['eq',$cs];
        $where["_string"] = "FIND_IN_SET($tagid,tags)";
        return $this->field('id,title,content,face,addtime')->where($where)->order('id desc')->limit($limit)->select();
    }

    public function getArticleListV2($tagid,$cs,$limit=6){
        $where['a.state'] = ['eq',2];
        $where['a.cs'] = ['eq',$cs];
        $where['b.tag_id'] = ['eq',$tagid];
        return $this->alias('a')->field('a.id,a.title,a.content,a.face,a.addtime')
            ->join('qz_sub_tag_article as b on b.article_id = a.id')
            ->where($where)
            ->order('addtime desc')
            ->limit($limit)
            ->select();
    }
}