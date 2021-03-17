<?php
// +----------------------------------------------------------------------
// | ErpUserLogic ERP营销用户逻辑
// +----------------------------------------------------------------------
namespace Home\Model\Logic;

use Home\Model\Db\ErpUserRegisterModel;

class ErpUserLogicModel
{
    public function getRegisterList()
    {
        $model = new ErpUserRegisterModel();
        $count = $model->getRegisterListCount();

        $result = [
            'page' => '',
            'list' => []
        ];
        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $result['page'] = $p->show();
            $result['list'] = $model->getRegisterList([], $p->nowPage, $p->listRows);
        }
        return $result;
    }
}