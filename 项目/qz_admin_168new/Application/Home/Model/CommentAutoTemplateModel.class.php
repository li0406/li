<?php

namespace Home\Model;

use Think\Model;

class CommentAutoTemplateModel extends Model
{

    protected $autoCheckFields = false;

    public $stageList = [
        1 => "开工阶段",
        2 => "水电阶段",
        3 => "泥木阶段",
        4 => "油漆阶段",
        5 => "竣工阶段",
    ];
}
