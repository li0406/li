<?php

namespace Common\Model\Logic;

use Common\Enums\VideoCodeEnum;
use Common\Model\ArticleVedioModel;

class VideoLogicModel
{
    public function getCategoryVideoList($category = VideoCodeEnum::VIDEO_CLASS_CJ)
    {
        $info = VideoCodeEnum::getVideoClass($category);
        if (empty($info)) {
            return [];
        }
        $map = [
            'b.pid' => ['eq', $info['pid']],
            'b.cid' => ['eq', $info['cid']],
        ];
        $videoDb = new ArticleVedioModel();
        $field = 'a.id,a.title,a.description,a.cover_img,a.url,a.mobile_url';
        return $videoDb->getVideoListDataByTime(0, 6, $map, $field);
    }
}