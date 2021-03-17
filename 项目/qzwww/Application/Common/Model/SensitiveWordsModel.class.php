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
           "status" => array("EQ",0)
        );

        return M("sensitive_words")->where($map)->select();
   }
}
