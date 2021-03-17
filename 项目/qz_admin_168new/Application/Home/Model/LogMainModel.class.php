<?php
/**
 * 综合日志记录表,用于记录各种日志
 * @Author: qz_ls
 * @Date:   2017-03-23 12:41:35
 * @Last Modified by:   qz_ls
 * @Last Modified time: 2017-03-23 13:46:35
 */
namespace Home\Model;
use Think\Model;

class LogMainModel extends Model
{

    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。


    /**
     * 记录日志
     * @param  [type] $time     日志时间
     * @param  [type] $log      日志内容
     * @param  [type] $level    日志等级
     * @param  [type] $module   日志模块
     * @return [type]           日志id
     */
    public function addLog($logarr)
    {
        $data['time']       = $logarr['time'];
        $data['log']        = $logarr['log'];
        $data['level']      = $logarr['level'];
        $data['module']     = $logarr['module'];
        $data['addtime']    = date('Y-m-d H:i:s'); //日志数据库记录时间
        return M("log_main")->add($data);
    }

}