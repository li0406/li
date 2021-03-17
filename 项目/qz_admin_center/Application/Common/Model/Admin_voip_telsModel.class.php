<?php
//voip电话列表 qz_admin_voip_tels model
namespace Common\Model;
use Think\Model;
class Admin_voip_telsModel extends Model{

    /**
     * 通过后台用户id获取 绑定的voip电话
     * @param   $admin_id  后台用户id
     * @param   $solutions voip提供商
     * @return  查询结果
     */
    public function getVoipInfoByid($admin_id, $solutions){
        $map = array(
                    "use_id"    =>array("EQ",$admin_id),
                    "solutions" =>array("EQ",$solutions)
                         );
        return M("admin_voip_tels")->where($map)->find();
    }


    /**
     * 通过voip账号查询本条voip信息
     * @param   $voipaccount  voip账号
     * @param   $solutions voip提供商
     * @return  查询结果
     */
    public function getVoipInfoByvoipaccount($voipaccount, $solutions){
        $map = array(
                    "voipAccount"    =>array("EQ",$voipaccount),
                    "solutions"      =>array("EQ",$solutions)
                         );
        return M("admin_voip_tels")->where($map)->find();
    }


    /**
     * 绑定后台账号信息到 qz_admin_voip_tels 表
     * @param  $voipaccount voip账号
     * @param  $solutions   提供商 默认 yuntongxun
     * @param  $data        后台账号信息
     * @return 查询结果
     */
    public function bindVoipInfoByvoipaccount($voipaccount, $solutions = 'yuntongxun', $data){
        $map = array(
                    "voipAccount"    =>   array("EQ",$voipaccount),
                    "solutions"      =>   array("EQ",$solutions)
                         );
        return M("admin_voip_tels")->where($map)->save($data);
    }


    /**
     * 解除绑定后台账号信息到 qz_admin_voip_tels 表
     * @param  $voipaccount voip账号
     * @param  $solutions   提供商 默认 yuntongxun
     * @return 查询结果
     */
    public function unbindVoipInfoByvoipaccount($voipaccount, $solutions = 'yuntongxun'){
        $map = array(
                    "voipAccount"    =>   array("EQ",$voipaccount),
                    "solutions"      =>   array("EQ",$solutions)
                         );
        $data['use_on']   = '0';
        $data['use_id']   = ''; // 使用人帐号id
        $data['use_name'] = ''; // 使用人姓名
        $data['use_time'] = ''; // 开始设置使用时间

        return M("admin_voip_tels")->where($map)->save($data);
    }

    /**
     * 获取制定提供商所有 voip信息
     * @param   $solutions 提供商
     * @return  查询结果
     */
    public function getVoipInfoAllList($solutions = 'yuntongxun') {
        $map = array(
                    "solutions"      =>   array("EQ",$solutions)
                         );

        return M("admin_voip_tels")->where($map)->select();

    }

}
?>