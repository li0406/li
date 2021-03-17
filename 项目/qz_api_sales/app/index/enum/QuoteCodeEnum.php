<?php
// +----------------------------------------------------------------------
// | QuoteCodeEnum
// +----------------------------------------------------------------------

/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/30
 * Time: 10:56
 */

namespace app\index\enum;


class QuoteCodeEnum
{
    /**
     * 导入键/值映射
     * @var array
     */
    public $key_to_field = [
      'A' => 'cid',
      'B' => 'parend_cid',
      'C' => '',
      'D' => '',
      'E' => '',
      'F' => '',
      'G' => '',
      'H' => '',
      'I' => '',
      'J' => '',
      'K' => '',
      'L' => '',
      'M' => '',
      'N' => '',
      'O' => '',
    ];

    /**
     * 城市等级映射
     * @var array
     */
    protected static $little_to_num = [
        0 => 'A',
        1 => 'B',
        2 => 'C',
        3 => 'D',
        4 => 'S1',
        5 => 'S2'
    ];

    /**
     * 获取城市等级
     * @param $val
     * @return mixed
     */
    public static function getLittle($val)
    {
        $val = strtoupper($val);
        return array_search($val, static::$little_to_num) !== false ? array_search($val, static::$little_to_num) : 3;
    }
}