<?php
/**
 *  微信票据日志
 */
namespace Common\Model;
use Think\Model;
class LogwxticketModel extends Model{
    protected $tableName = "log_wx_ticket";

    /**
     * 添加日志
     * @param [type] $data [description]
     */
    public function addLog($data){
        return M("log_wx_ticket")->add($data);
    }

    /**
     * 根据票据查询用户信息
     * @return [type] [description]
     */
    public function getTicketmd5ToUserid($ticket,$sceneid){
        $map = array(
                "wx_ticket_now_md5"=>array("EQ",$ticket),
                "sceneid"=>array("EQ",$sceneid)
                     );
        return M("log_wx_ticket")->where($map)->find();
    }


}