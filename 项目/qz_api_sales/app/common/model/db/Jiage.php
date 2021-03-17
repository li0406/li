<?php

/**
 * @Author: liuyupeng
 * @Email: lypeng9205@163.com
 * @Date:   2019-06-04 14:13:55
 * @Last Modified by:   liuyupeng
 * @Last Modified time: 2019-06-04 14:17:15
 */

namespace app\common\model\db;

use think\Model;

class Jiage extends Model
{
    protected $autoWriteTimestamp = false;

    // 获取价格信息
    public function getJiage()
    {
        return self::where('type', 'jiage')->order('px')->field('id,name')->select();
    }

    // 获取价格信息
    public static function getYusuan($id, $field)
    {
        return self::field($field)->where('id', $id)->find();
    }
}