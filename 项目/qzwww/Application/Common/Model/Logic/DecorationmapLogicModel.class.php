<?php

namespace Common\Model\Logic;

use Common\Model\Db\DecorationMapModel;

class DecorationmapLogicModel
{
    protected $mapDb;
    protected $user;

    public function __construct($user = [])
    {
        $this->mapDb = new DecorationMapModel();
        $this->user = $user;
    }

    public function getDecorationMapCount($input)
    {
        return $this->mapDb->getMapCount($input);
    }

    public function getDecorationMapList($input, $offset = 0, $limit = 10)
    {
        return $this->mapDb->getMapList($input, $offset, $limit);
    }
}
