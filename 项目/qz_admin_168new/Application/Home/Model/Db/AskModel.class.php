<?php
// +----------------------------------------------------------------------
// | AskModel  问答
// +----------------------------------------------------------------------

namespace Home\Model\Db;

Use Think\Model;

class AskModel extends Model
{

    public function getAsk($fields,$where){
        return M('ask')->field($fields)->where($where)->select();
    }
	
	public function getAskCount($fields,$where){
		return (int)M('ask')->field($fields)->where($where)->count();
	}
}

