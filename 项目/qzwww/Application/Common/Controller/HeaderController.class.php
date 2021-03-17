<?php

namespace Common\Controller;

use Common\Model\Logic\QuyuLogicModel;

class HeaderController extends BaseController
{
    public function headerCity()
    {
        //获取头部城市数据
        //获取热门城市
        $quyuLogic = new QuyuLogicModel();
        $topCity["hotCity"] = S("Cache:Base:Header:HotCity");
        if (empty($topCity["hotCity"])) {
            $topCity["hotCity"] = $quyuLogic->getHotCitys();
            S("Cache:Base:Initialize:HotCity", $topCity["hotCity"], 21600);
        }
        //按首字母排序
        $topCity["accordCity"] = S("Cache:Base:Header:accordCity");
        if (empty($topCity["accordCity"])) {
            $topCity["accordCity"] = $quyuLogic->getTopCitys();

            S("Cache:Base:Initialize:accordCity", $topCity["accordCity"], 21600);
        }
        $this->ajaxReturn(['error_code' => 200, 'list' => $topCity]);
    }
}
