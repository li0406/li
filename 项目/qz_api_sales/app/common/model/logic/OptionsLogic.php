<?php

namespace app\common\model\logic;

use think\facade\Cache;
use app\common\model\db\Options;
use app\common\enum\CacheKeyCodeEnum;

class OptionsLogic
{
    /**
     * 通过后台用户ID查询 用户自定义电话呼叫系统提供商
     */
    public function getMyTelCenter_ChannelByid($id)
    {
        $TelCenter_Channel     = $this->getOptionName('TelCenter_Channel')['option_value'];
        $TelCenter_Diy_id      = $this->getOptionName('TelCenter_Diy_id')['option_value'];
        $TelCenter_Diy_idArr   = explode(',', trim($TelCenter_Diy_id,','));
        $TelCenter_Diy_Channel = $this->getOptionName('TelCenter_Diy_Channel')['option_value'];

        if (in_array($id, $TelCenter_Diy_idArr)) {
            //如果用户自定义了电话呼叫系统提供商
            $TelCenter_Channel = $TelCenter_Diy_Channel;
        }

        return $TelCenter_Channel;
    }

    /**
     * 通过 电话呼叫提供商 获取完整的信息
     * @param $TelCenter_Channel
     * @return mixed
     */
    public function getTelCenter_ChannelInfoByChannel($TelCenter_Channel)
    {
        $TelCenter_Channel_INFO['TelCenter_Channel'] = $TelCenter_Channel;
        switch ($TelCenter_Channel) {
            case 'ytx':
                $TelCenter_Channel_INFO['TelCenter_Channel_name']='ytx(容联云通讯)';
                $TelCenter_Channel_INFO['solutions'] = 'yuntongxun';
                break;
            case 'cuct';
                $TelCenter_Channel_INFO['TelCenter_Channel_name']='cuct(联通云总机)';
                $TelCenter_Channel_INFO['solutions'] = 'cuct';
                break;
            default:
                $TelCenter_Channel_INFO['TelCenter_Channel_name'] ='系统未定义的 电话呼叫系统提供商';
                $TelCenter_Channel_INFO['solutions']              = '';
                break;
        }
        return $TelCenter_Channel_INFO;
    }

    public function getOptionName($name)
    {
        $opDb = new Options();
        $cache_key = sprintf(CacheKeyCodeEnum::OPTIONS_NAME_VALUE, $name);
        $info = Cache::get($cache_key);
        if (empty($info)) {
            $info = $opDb->getOptionsByName($name);
            Cache::set($cache_key, $info, 60 * 5);
        }
        return $info;
    }

    public function getMyTelCenterChannelByid($user_id)
    {
        $model = new Options();
        return $model->getMyTelCenterChannelByid($user_id);
    }

    /**
     * 获取七牛相关参数
     * @return [type] [description]
     */
    public function getQiniuOptions() {
        $optionNames = [
            "QINIU_AK", "QINIU_CK", "QINIU_BUCKET", "QINIU_DOMAIN",
            "QINIU_AK_DEV", "QINIU_CK_DEV", "QINIU_BUCKET_DEV", "QINIU_DOMAIN_DEV"
        ];

        $cache_key = CacheKeyCodeEnum::OPTIONS_QINIU;
        $options = Cache::get($cache_key);
        if (empty($options)) {
            $optionModel = new Options();
            $list = $optionModel->getOptionsByNames($optionNames)->toArray();
            $options = array_column($list, "option_value", "option_name");
            Cache::set($cache_key, $options);
        }

        return $options;
    }
}