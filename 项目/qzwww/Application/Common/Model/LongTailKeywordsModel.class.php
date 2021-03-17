<?php

namespace Common\Model;
use Think\Model;

/**
*   长尾词
*/
class LongTailKeywordsModel extends Model
{
    /**
     * 获取长尾词列表数量
     * @return [type] [description]
     */
    public function getLongTailKeyListCount()
    {
        return M("long_tail_keywords")->count();
    }

    /**
     * 获取长尾词列表
     * @param  [type] $pageIndex [description]
     * @param  [type] $pageCount [description]
     * @return [type]            [description]
     */
    public function getLongTailKeyList($pageIndex,$pageCount)
    {
        return M("long_tail_keywords")->limit($pageIndex.",".$pageCount)->order("id desc")->select();
    }

    /**
     * 查询长尾词信息
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function findWordInfo($id)
    {
        $map = array(
            "a.id" => array("EQ",$id)
        );

        $buildSql = M("long_tail_keywords")->where($map)->alias("a")
                               ->join("left join qz_long_tail_relete as b on b.long_tail_id = a.id")
                               ->field("a.id,a.words,group_concat(DISTINCT b.words) as fen_word")
                               ->group("a.id")
                               ->buildSql();
        return  M("long_tail_keywords")->table($buildSql)->alias("t")
                                       ->join("left join qz_long_tail_tdk_relete as c on c.long_tail_id = t.id")
                                       ->join("left join qz_long_tail_keywords_tdk d on d.id = c.tdk_id")
                                       ->field("t.*,d.title,d.keywords,d.description")
                                       ->find();
    }

    /**
     * 获取分词相关的长尾词列表
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getRelationWords($id,$fen_word,$limit)
    {
        if (!is_array($fen_word)) {
            return false;
        }

        foreach ($fen_word as $key => $value) {
            $words[] = array("LIKE","$value%");
        }

        $words[] = "OR";

        $map = array(
            "a.long_tail_id" => array("NEQ",$id),
            "a.words" => $words
        );

        return M("long_tail_keywords ")->where($map)->alias("b")
                                    ->join("join qz_long_tail_relete a FORCE INDEX(idx_words) on b.id = a.long_tail_id")
                                    ->field("b.id,b.words")
                                    ->group("a.long_tail_id,b.words")
                                    ->limit($limit)
                                    ->select();
    }


    /**
     * 获取最新的长尾词
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function getNewWords($limit)
    {
        return M("long_tail_keywords")->where($map)->limit($limit)->order("id desc")
                                      ->field("id,words")->select();
    }

}