<?php

/**
 *  敏感词表
 */
namespace Common\Model;
use Think\Model;
class SensitiveWordsModel extends Model
{

   public function getAllWords()
   {
        $map = array(
           "a.status" => array("EQ",0)
        );

        return M("sensitive_words")->where($map)->alias("a")
                                   ->join("join qz_sensitive_words_type b on a.type = b.id")
                                   ->field("a.word,a.type,b.name")
                                   ->select();
   }
}
