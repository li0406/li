<?php
/**
 * 后台用户安全问题表
 */
namespace Common\Model;
use Think\Model;
class AdminuserquestionModel extends Model{
    protected $_validate = array(
        array('question1','require','安全问题一不能为空',0,"",1), //安全问题一
        array('answer1','require','安全问题答案一不能为空',0,"",1), //安全问题答案一
        array('question2','require','安全问题二不能为空',0,"",1), //安全问题二
        array('answer2','require','安全问题答案二不能为空',0,"",1), //安全问题答案二
        array('question3','require','安全问题三不能为空',0,"",1), //安全问题三
        array('answer3','require','安全问题答案三不能为空',0,"",1) //安全问题答案三
    );
    protected $tableName = "adminuser_question";
    /**
     * 添加安全问题和答案
     * @param [type] $data [description]
     */
    public function addQuestion($data){
        return M("adminuser_question")->add($data);
    }


    /**
     * [getQuestion description]
     * @return [type] [description]
     */
    public function getQuestionRecord($uid){
        $map = array(
            "uid"=>array("EQ",$uid)
                     );
        return M("adminuser_question")->where($map)->count();
    }

    /**
     * 获取用户的密保答案
     * @return [type] [description]
     */
    public function getUserQuestion($uid){
        $map = array(
            "uid"=>array("EQ",$uid)
                     );
        return M("adminuser_question")->where($map)->find();
    }
}