<?php
/**
 * 视频
 */

namespace Common\Enums;

class VideoCodeEnum
{
    const VIDEO_CLASS_CJ = 'changjian';//常见问题
    const VIDEO_CLASS_KJ = 'kongjian';//空间利用
    const VIDEO_CLASS_MN = 'maoni';//装修猫腻
    const VIDEO_CLASS_RZ = 'ruanzhuang';//软装搭配

    //装修指南顶级分类
    protected static $videoClass = [
        self::VIDEO_CLASS_CJ => [
            'pid' => 3,
            'cid' => 13,
        ],
        self::VIDEO_CLASS_KJ => [
            'pid' => 1,
            'cid' => 1,
        ],
        self::VIDEO_CLASS_MN => [
            'pid' => 3,
            'cid' => 12,
        ],
        self::VIDEO_CLASS_RZ => [
            'pid' => 4,
            'cid' => 15,
        ],
    ];


    public static function getVideoClass($class = '')
    {
        if (!empty($class)) {
            return self::$videoClass[$class];
        }
        return self::$videoClass;
    }
}