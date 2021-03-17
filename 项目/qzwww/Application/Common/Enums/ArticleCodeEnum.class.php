<?php
/**
 * 文章
 */

namespace Common\Enums;

class ArticleCodeEnum
{
    const ARTICLE_CLASS_Q = 'zhunbei';//装修前－准备阶段
    const ARTICLE_CLASS_Z = 'shigong';//装修中－施工阶段
    const ARTICLE_CLASS_H = 'ruzhu';//装修后－入住阶段
    const ARTICLE_ZN_FG = 'fg';//装修风格
    const ARTICLE_ZN_SPACE = 'jubu';//空间搭配
    const ARTICLE_ZN_FS = 'fs';//家居风水(风水)
    const ARTICLE_ZN_YY = 'yingyang';//营养(家居)

    protected static $zhinanShort = [
        'fg', //装修风格
        'jubu', //空间搭配
        'fs', //家居风水
        'yingyang', //营养
        'news' //新闻
    ];

    //选材导购
    protected static $xcdgShort = [
        // 'diban', //地板
        // 'youqituliao', //油漆涂料
        // 'cizhuan', //瓷砖
        // 'weiyushebei', //卫浴
        // 'dengju', //灯具
        // 'menchuang', //门窗
        // 'chugui', //橱柜
        // 'diaoding', //吊顶
        // 'wujin', //五金
        'jiaju'
    ];

    public static function getZhinanTopShort()
    {
        return self::$zhinanShort;
    }

    public static function getXcdgTopShort()
    {
        return self::$xcdgShort;
    }
}