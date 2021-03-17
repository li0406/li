<?php


namespace Home\Model\Logic;


use Home\Model\Db\UcenterPlatformModel;

class UcenterPlatformLogicModel
{
    protected $ucenterPlatformModel;

    public function __construct()
    {
        $this->ucenterPlatformModel = new UcenterPlatformModel();
    }

    public function getPlatformList()
    {
        $list = $this->ucenterPlatformModel->getList();
        return $list ? $list : [];
    }

}