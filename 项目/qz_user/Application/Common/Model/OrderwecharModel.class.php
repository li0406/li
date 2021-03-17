<?php
/**
 *  微信订单通知表
 */
namespace Common\Model;
use Think\Model;
class OrderwecharModel extends Model{
    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。
    protected $tableName ="order_wechat";

   /**
    * 根据公司编号获取通知列表
    * @param  [type] $comid [公司编号]
    * @return [type]        [description]
    */
    public function getOrderNoticeList($comid, $limit = 3){
        $map = array(
                "comid"=>array("EQ",$comid),
                "is_delete"=>array("EQ",0),
                "wx_openid"=>array("exp","is not null")
        );
        return M("order_wechat")->where($map)->order('update_time asc, id asc')->limit($limit)->select();
    }

    /**
     * 解除订单通知绑定
     * @return [type] [description]
     */
    public function unbindWechat($wx_unionid){
        $map = array(
                    "wx_unionid"=>array("EQ",$wx_unionid),
                    "wx_openid"=>array("EQ",$wx_unionid),
                    "comid"=>array("EQ",$wx_unionid),
                    "_logic"=>"OR"
                );
        return M("order_wechat")->where($map)->save(array("is_delete"=>1));
    }

    /**
     * 添加绑定帐号
     */
    public function addAccount($data){
        return M("order_wechat")->add($data);
    }

    /**
     * 根据openid查询绑定信息
     * @return [type] [description]
     */
    public function getAccountByOpenId($openid, $comid, $is_delete = '', $employee_id = 0)
    {
        $map = array(
            'wx_openid' => array('EQ', $openid),
            'comid' => array('EQ', $comid),
            'employee_id' => array('EQ', $employee_id),
        );
        if ($is_delete === 1) {
            $map['is_delete'] = ['EQ', 1];
        }
        if ($is_delete === 0) {
            $map['is_delete'] = ['EQ', 0];
        }
        return M('order_wechat')->where($map)->find();
    }

    /**
     * 修改绑定信息
     * @param  [type] $openid [description]
     * @param  [type] $comid  [description]
     * @param  [type] $data   [description]
     * @return [type]         [description]
     */
    public function editAccount($openid,$comid="",$data){
        $map = array(
                "wx_openid"=>array("EQ",$openid)
                     );
        if(!empty($comid)){
            $map["comid"] = array("EQ",$comid);
        }
        return M("order_wechat")->where($map)->save($data);
    }

    /**
     * 获取公司绑定威信账号的数量
     * @param  [type] $comid [公司编号]
     * @return [type]        [description]
     */
    public function getAccountCount($comid, $employee_id = 0){
        $map = array(
            "comid"=>array("eq", $comid),
            "employee_id"=>array("eq", $employee_id),
            "is_delete"=>array("EQ", 0)
        );
        $buildSql = M("order_wechat")->where($map)->group("wx_openid")->buildSql();
        return M("order_wechat")->table($buildSql)->alias("t")->count();
    }

}