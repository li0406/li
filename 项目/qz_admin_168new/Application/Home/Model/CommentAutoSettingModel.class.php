<?php

namespace Home\Model;

use Think\Model;

class CommentAutoSettingModel extends Model
{

    protected $autoCheckFields = false;

    const FLAG_BASH = 1;
    const FLAG_PROGRAM = 2;

    // 获取配置
    public function getSetting($config_flag = 1)
    {
        $list = $this->where(['config_flag' => $config_flag])->select();
        return $this->setFormatter($list);
    }

    // 保存配置
    public function saveSettingByKey($config_key, $config_value)
    {
        return $this->where(['config_key' => $config_key])->save([
            'config_value' => $config_value,
            'updated_at' => time(),
        ]);
    }

    // 格式化处理
    public function setFormatter($list)
    {
        $config = [];
        foreach ($list as $key => $item) {
            $field = $item["config_key"];
            $config[$field] = $item["config_value"];
        }

        return $config;
    }
}
