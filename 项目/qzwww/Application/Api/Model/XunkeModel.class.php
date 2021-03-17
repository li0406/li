<?php
/**
 *  寻客 qz_xunke
 */
namespace Api\Model;
use Think\Model;

class XunkeModel extends Model{

    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。

    /**
     * 保存提交过来的数据
     * @param  array $data 数据字段
     * @return 数据库执行结果
     */
    public function xunkeSave($data)
    {
        if (empty($data)) {
            return false;
        }
        return M('xunke')->add($data);
    }


}
