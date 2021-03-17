<?php
/**
 * 装修公司查看订单密码表 qz_order_pass
 */
namespace Common\Model;
use Think\Model;
class OrderpassModel extends Model{
    protected $tableName = 'order_pass';
    //最后一个参数 验证范围 4 修改订单查看密码
        protected $_validate = array(
        array('pass','require','请输入密码',1,"",4),//新增的时候密码不能为空
        array('confirmpassword','pass','两次密码不匹配',0,"confirm",4),

    );

    /**
     * 保存订单查看密码
     */
    public function setOrderPass($data){
        return M("order_pass")->add($data);
    }

    /**
     * 查询订单密码
     * @param  [type] $comid [装修公司编号]
     * @return [type]        [description]
     */
    public function getOrderPassById($comid){
        $map = array(
                "comid"=>array("EQ",$comid)
                     );
        return M("order_pass")->where($map)->find();
    }

    /**
     * 编辑信息
     * @return [type] [description]
     */
    public function editPass($id,$data){
        $map = array(
                "comid"=>array("EQ",$id)
                     );
        return M("order_pass")->where($map)->save($data);
    }

}