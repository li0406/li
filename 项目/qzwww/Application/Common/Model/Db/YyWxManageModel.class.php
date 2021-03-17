<?php
/**
 *  微信表信息
 */
namespace Common\Model\Db;
use Think\Model;
class YyWxManageModel extends Model
{
    /**
     * 根据查询条件获取单个微信配置信息
     * $map
     */
    public function getInfoByMap($map,$field = 'id,appid,appsecret,encodingaeskey,token,wxid,wxname')
    {
        return M('yy_wx_manage')->field($field)->where($map)->find();
    }


    /**
     * 根据查询条件获取多个微信配置信息
     * $map
     */
    public function getInfoListByMap($map,$field = 'id,appid,appsecret,encodingaeskey,token,wxid,wxname')
    {
        return M('yy_wx_manage')->field($field)->where($map)->select();
    }
}