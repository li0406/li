<?php

namespace app\index\model\db;
use think\Model;

class Options extends Model
{
    public function getOptionList()
    {
        $map[] = ["autoload","EQ","yes"];
        return $this->where($map)->select();
    }

    /*
     * getOptionNameCC 获取某一项的配置值 是否获取缓存可控制
     * @param  string $name [单独的配置项目名称]
     * @param  string $CC  [缓存控制开关,1获取缓存,0不获取缓存]
     * @return mixed       [单独一项的表一行记录]
     */
    public function getOptionNameCC($name, $CC=0)
    {
        if (!empty($name)) {
            //缓存
            $optionone = cache('C:OP:NV:'.$name);

            //如果缓存存在 且 要取缓存
            if (!empty($optionone) && 1 == $CC) {
                return $optionone;
            }

            //如果缓存不存在 或者 不需要取缓存
            if (empty($optionone) || 0 == $CC) {
                $map = array(
                    'option_name' => array('eq', $name)
                );
                $optionone = $this->where($map)->find();
                //去掉备注信息
                unset($optionone['option_remark']);
                //缓存
                cache('C:OP:NV:'.$name, $optionone, 60 * 5);
                return $optionone;
            }
        }else{
            return array();
        }
    }

}