<?php

namespace Common\Enum;

class TellEnum
{
    /**
     * 电话提供商
     */
    const TEL_CHANNEL = [
        ['id' => 'ytx', 'name' => '容联云通讯', 'full_name' => 'ytx(容联云通讯)', 'status' => '不可用'],
        ['id' => 'cuct', 'name' => '联通云总机', 'full_name' => 'cuct(联通云总机)', 'status' => '可用'],
        ['id' => 'holly', 'name' => 'holly', 'full_name' => 'holly', 'status' => '可用'],
    ];
}
