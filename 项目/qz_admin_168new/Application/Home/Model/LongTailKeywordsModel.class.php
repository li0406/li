<?php

namespace Home\Model;
Use Think\Model;

/**
*   长尾词管理
*/
class LongTailKeywordsModel extends Model
{
    /**
     * 添加长尾词
     * @param [type] $data [description]
     */
    public function addWords($data)
    {
        return M("long_tail_keywords")->add($data);
    }
    /**
     * 批量添加
     * @param [type] $data [description]
     */
    public function addAllWords($data)
    {
        return M("long_tail_keywords")->addAll($data);
    }

    /**
     * 批量修改长尾词信息
     * @param  [type] $ids  [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function editAllWords($ids,$data)
    {
        $map = array(
            "id" => array("IN",$ids)
        );
        return M("long_tail_keywords")->where($map)->save($data);
    }

    /**
     * 批量删除长尾词
     * @param  [type] $ids [description]
     * @return [type]      [description]
     */
    public function delAllWords($ids)
    {
        $map = array(
            "id" => array("IN",$ids)
        );
        return M("long_tail_keywords")->where($map)->delete();
    }

    /**
     * 批量添加长尾词TAG
     * @param [type] $data [description]
     */
    public function addAllChildWords($data)
    {
        return M("long_tail_relete")->addAll($data);
    }

    /**
     * 获取长尾词列表数量
     * @param  [type] $words [description]
     * @return [type]        [description]
     */
    public function getWordsCount($words,$istdk,$tdk_flag,$map = '',$location = "")
    {
        $map['istdk'] = array("EQ",$istdk);
        if (!empty($words)) {
            $map["a.words"] = array("like","%".$words."%");
        }

        if (!empty($location) && !empty($words)) {
            if ($location == 1) {
                $map["a.words"] = array("like",$words."%");
            } else {
                $map["a.words"] = array("like","%".$words);
            }
        }

        if ($tdk_flag) {
            $map["b.tdk_id"] = array("exp","is not null");
        } else {
            $map["b.tdk_id"] = array("exp","is null");
        }

        return M("long_tail_keywords")->where($map)->alias("a")
                                      ->join("left join qz_long_tail_tdk_relete as b on b.long_tail_id = a.id")
                                      ->count();
    }

    /**
     * 获取长尾词列表
     * @param  [type] $words [description]
     * @return [type]        [description]
     */
    public function getWords($words,$istdk,$tdk_flag,$pageIndex,$pageCount,$map = '',$location = "")
    {
        $map['istdk'] = array("EQ",$istdk);
        if (!empty($words)) {
            $map["a.words"] = array("like","%".$words."%");
        }

        if (!empty($location) && !empty($words)) {
            if ($location == 1) {
                $map["a.words"] = array("like",$words."%");
            } else {
                $map["a.words"] = array("like","%".$words);
            }
        }

        if ($tdk_flag) {
            $map["b.tdk_id"] = array("exp","is not null");
        } else {
            $map["b.tdk_id"] = array("exp","is null");
        }

        $buildSql = M("long_tail_keywords")->alias("a")->where($map)
                                           ->join("left join qz_long_tail_tdk_relete as b on b.long_tail_id = a.id")
                                           ->field("a.*,b.tdk_id")
                                           ->limit($pageIndex.",".$pageCount)->buildSql();

        return  M("long_tail_keywords")->table($buildSql)->alias("a")
                                    ->join("left join qz_long_tail_relete b on b.long_tail_id = a.id")
                                    ->field("a.id,a.words,a.time,GROUP_CONCAT(b.words SEPARATOR ' | ') as subwords")
                                    ->order("a.id desc")
                                    ->group("a.id")
                                    ->select();
    }

    /**
     * 通过ID获取长尾词列表
     * @param  [type] $words [description]
     * @return [type]        [description]
     */
    public function getWordsById($istdk,$tdk_flag,$map = '')
    {
        $map['istdk'] = array("EQ",$istdk);

        if ($tdk_flag) {
            $map["b.tdk_id"] = array("exp","is not null");
        } else {
            $map["b.tdk_id"] = array("exp","is null");
        }

        $buildSql = M("long_tail_keywords")->alias("a")->where($map)
            ->join("left join qz_long_tail_tdk_relete as b on b.long_tail_id = a.id")
            ->field("a.*,b.tdk_id")
            ->buildSql();

        return  M("long_tail_keywords")->table($buildSql)->alias("a")
            ->join("left join qz_long_tail_relete b on b.long_tail_id = a.id")
            ->field("a.id,a.words,a.time,GROUP_CONCAT(b.words SEPARATOR ' | ') as subwords")
            ->order("a.id desc")
            ->group("a.id")
            ->select();
    }
}