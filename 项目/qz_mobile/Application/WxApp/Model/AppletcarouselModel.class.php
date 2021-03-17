<?php

/**
 * 首页所需要的文章Model
 */

namespace Common\Model;

use Think\Model;

class AppletcarouselModel extends Model
{

    protected $autoCheckFields = false;
    const BANNERSHOW = 1; //banner启用
    const BANNERHIDE = 0; //banner不启用

    public function getBanner($map, $page, $pageSize)
    {
        $map['status'] = array('eq', self::BANNERSHOW);
        return M('applet_carousel')
            ->field('name,url,order,img_url')
            ->order('`order` asc')
            ->page($page, $pageSize)
            ->select();
    }

}