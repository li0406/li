<?php
/**
 * Created by PhpStorm.
 * User: mcj
 * Date: 2018/5/3
 * Time: 14:56
 * 视频学装修模型
 */

namespace Common\Model;

use Think\Model;

class VideoModel extends Model
{
    protected $autoCheckFields = false; //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。

    public function getlist($map, $order = 'id desc', $pageIndex = 0, $pageCount = 10)
    {
        $map['a.isdelete'] = array("EQ", 0);
       return M('article_vedio')->alias("a")
            ->field("a.*")
            ->where($map)
            ->order($order)
            ->limit($pageIndex . "," . $pageCount)
            ->select();

    }
}