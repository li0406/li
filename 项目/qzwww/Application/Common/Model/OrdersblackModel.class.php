<?php
/**
 *  黑名单号码发布的订单列表 Ordersblack表
 */
namespace Common\Model;
use Think\Model;
class OrdersblackModel extends Model{
    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。
    protected $trueTableName   = "orders_black"; //指定表

    /**
     * [getTelAtBlackStatus 通过一个加密号码查询业主黑名单]
     * @param  [type] $tel_encrypt [description]
     * @return [type]              [description]
     */
    public function  getTelAtBlackStatus($tel_encrypt) {
        if (empty($tel_encrypt)) {
            return false;
        }

        $map                = array();
        $map['tel_encrypt'] = array('eq', $tel_encrypt); //加密的号码
        $map['status']      = array('eq', 1); //有效的
        return M('order_blacklist')->where($map)->find();

    }

    /**
     * [getTelEncrypt 电话号码加密 手动加盐]
     * @param  [type] $tel [电话号码]
     * @return [type]      [md5($tel.$salt)密文]
     */
    public function getTelEncrypt($tel) {
        import('Library.Org.Util.App');
        $app         = new \App();
        return $app->order_tel_encrypt($tel); //生成加密电话
    }

    /**
     * [checkTelBlackTrue 判断号码是否在黑名单]
     * @param  $tel 号码
     * @return bool
     */
    public function checkTelBlackTrue($tel) {
        if (empty($tel)) {
            return false;
        }
        $tel_encrypt = self::getTelEncrypt($tel);
        $result      = self::getTelAtBlackStatus($tel_encrypt);
        return (bool) count($result);
    }

    /**
     * [orderpublish 黑名单号码发布订单]
     * @param  [array] $data   [传入订单参数]
     * @param  [str]   $method [方法. insert 为新增. update为修改]
     * @return [str]   [成功返回 单号  失败返回 false]
     */
    public  function orderpublish($data,$method) {
        if (empty($data) || empty($method)) {
            return false;
        }

        import('Library.Org.Util.App');
        $app = new \App();
        $data['ip']        = $app->get_client_ip();
        $data['time']      = time();
        $data['time_real'] = time();
        $data['userid']    = I("session.u_userInfo")['id'];
        if ($method == "insert") {
            //新增订单
            $data['id']     = date('Ymd'). sprintf("%05d%03d", time()%86400, mt_rand(1,1000)); // 生成订单号

            //如果城市为空
            if(empty($data['cs'])) {
                $data['cs'] = I("session.cityId");
            }

            //存放一个加密电话号码用于订单号码搜索用途
            $data['tel_encrypt'] = $app->order_tel_encrypt($data['tel']);

            //新增加订单
            $result = M("Orders_black")->add($data);

            return $result;

        }else if($method == "update"){
            //预留
            return false;
        } else {
             //未定义的 $method
            return false;
        }
    }
}