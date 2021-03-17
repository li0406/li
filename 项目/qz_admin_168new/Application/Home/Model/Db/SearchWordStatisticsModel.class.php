<?php

namespace Home\Model\Db;

use Think\Model;
class SearchWordStatisticsModel extends Model
{
    public function getSearchWordsListCount($date,$word,$exact)
    {
        if (!empty($exact) && !empty($word)) {
            $map["word"] = array("EQ",$word);
        } elseif (!empty($word)) {
            $map["word"] = array("LIKE","%".$word."%");
        }

        if (!empty($date)) {
            $map["date"] = array("EQ",$date);
        }
        return $this->where($map)->count();
    }

    public function getSearchWordsList($date,$word,$exact,$pageIndex,$pageCount)
    {
        if (!empty($exact) && !empty($word)) {
            $map["word"] = array("EQ",$word);
        } elseif (!empty($word)) {
            $map["word"] = array("LIKE","%".$word."%");
        }

        if (!empty($date)) {
            $map["date"] = array("EQ",$date);
        }

        return $this->where($map)->field("word,sum(count) as all_count,sum(if(`from` = 'm',count,null)) as m_count,sum(if(`from` = 'p',count,null)) as p_count")->order("all_count desc")->group("word")->limit($pageIndex,$pageCount)->select();

    }
}