<?php
/**
 *  微信公众平台回调,记录表
 */
namespace Common\Model;
use Think\Model;
class LogwechatcallbakModel extends Model{
    protected $tableName = "log_wechat_callback";

     /**
     * [checkCallbackTicket 查询微信回调数据中是否有这个ticket]
     * @param  [string] $ticket [传入ticket]
     * @return [array]         [返回查询结果]
     */
    public function checkCallbackTicket($ticket,$scan) {
        $map = array(
                "Ticket"=>array('EQ',$ticket)
                     );
        if(!empty($scan)){
            $map["Event"] = array("EQ",$scan);
        }
        return M("log_wechat_callback")->where($map)->order("createtime desc")->find();
    }
}