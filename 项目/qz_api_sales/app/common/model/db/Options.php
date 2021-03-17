<?php
// +----------------------------------------------------------------------
// | Options
// +----------------------------------------------------------------------
namespace app\common\model\db;

use think\Model;

class Options extends Model
{
    protected $autoWriteTimestamp = false;
    protected $table = 'qz_options';

    public function getOptionsByName($option_name)
    {
        return $this->where(['option_name' => $option_name])->find();
    }

    public function getOptionsByNames($option_names)
    {
        return $this->where("option_name", "in", $option_names)->select();
    }

    /**
     * 通过后台用户ID查询 用户自定义电话呼叫系统提供商
     */
    public function getMyTelCenterChannelByid($id)
    {
        $TelCenter_Channel     = $this->getOptionNameCC('TelCenter_Channel')['option_value'];
        $TelCenter_Diy_id      = $this->getOptionNameCC('TelCenter_Diy_id')['option_value'];
        $TelCenter_Diy_idArr   = explode(',', trim($TelCenter_Diy_id,','));
        $TelCenter_Diy_Channel = $this->getOptionNameCC('TelCenter_Diy_Channel')['option_value'];

        if (in_array($id, $TelCenter_Diy_idArr)) {
            //如果用户自定义了电话呼叫系统提供商
            $TelCenter_Channel = $TelCenter_Diy_Channel;
        }

        return $TelCenter_Channel;
    }

    /*
 * [getOptionNameCC 获取某一项的配置值 是否获取缓存可控制]
 * @param  [str] $name [单独的配置项目名称]
 * @param  [str] $CC  [缓存控制开关,1获取缓存,0不获取缓存]
 * @return [array]       [单独一项的表一行记录]
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
                $map[] = ['option_name',"EQ",$name];
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