<?php

/**
 * @Author: liuyupeng
 * @Email: lypeng9205@163.com
 * @Date:   2019-06-04 14:19:00
 * @Last Modified by:   liuyupeng
 * @Last Modified time: 2019-06-04 14:20:24
 */

namespace app\common\model\db;

use think\Model;

class Fangshi extends Model
{
    protected $autoWriteTimestamp = false;

    // 获取装修方式信息
    public function getFs()
    {
        return self::where('type', 'fangshi')->order('px')->field('id,name')->select();
    }

    // 获取方式信息
    public static function getFangshi($id, $field)
    {
        return self::field($field)->where('id', $id)->find();
    }
}