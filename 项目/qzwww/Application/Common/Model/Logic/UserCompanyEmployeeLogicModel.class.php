<?php


namespace Common\Model\Logic;


use Common\Model\Db\UserCompanyEmployeeModel;
use Common\Model\Db\UserDesModel;

class UserCompanyEmployeeLogicModel
{

    /**
     * 获取新签返装修公司职位信息
     * @param $comid
     * @return mixed
     */
    public function getZwInfoByComId($comid)
    {
        $model = new UserCompanyEmployeeModel();

        $list = $model->getZwInfoByComId($comid);
        $list = $this->changeList($list);

        return $list ? $list : [];

    }


    // 获取新签返装修公司的在职设计师数量
    public function getTeamDesignerListCount($comId)
    {
        $model = new UserCompanyEmployeeModel();
        $count = $model->getTeamDesignerListCount($comId);
        return $count ? $count : 0;
    }

    public function getTeamDesignerList($comid, $pageIndex = 0, $pageCount = 10)
    {
        $model = new UserCompanyEmployeeModel();
        $list = $model->getTeamDesignerList($comid, $pageIndex, $pageCount);
        $list = $this->changeList($list);
        return $list ? $list : [];

    }


    /**
     * 修改列表数据
     * @param $list
     * @return mixed
     */
    private function changeList($list)
    {

        foreach ($list as $key => $val) {
            //职位修改
            switch ($val['zw']) {
                case 2 :
                    $list[$key]['zw'] = '设计师';
                    break;
                case 3 :
                    $list[$key]['zw'] = '首席设计师';
                    break;
                case 4 :
                    $list[$key]['zw'] = '设计总监';
                    break;
                default :
                    $list[$key]['zw'] = '其他';
                    break;
            }
        }

        return $list;
    }

    /**
     * 获取新签返公司下的设计师信息
     * @param $id
     * @param $cs
     * @return object
     */
    public function getQianfanSignerInfo($id, $cs)
    {
        $model = new UserCompanyEmployeeModel();
        $info = $model->getQianfanSignerInfo($id, $cs);
        if (count($info) > 0) {
            //职位修改
            switch ($info['zw']) {
                case 2 :
                    $info['zw'] = '设计师';
                    break;
                case 3 :
                    $info['zw'] = '首席设计师';
                    break;
                case 4 :
                    $info['zw'] = '设计总监';
                    break;
                default :
                    $info['zw'] = '其他';
                    break;
            }
        }
        return $info ? $info : (object)array();
    }

    /**
     * 新签返装修公司下设计师添加人气值
     * @param $designerId
     * @return bool|int|string
     */
    public function SetIncDesignerPv($designerId)
    {
        $model = new UserDesModel();
        if (intval($designerId) <= 0) {
            return false;
        }
        return $model->SetIncDesignerPv($designerId);
    }

    /**
     * 获取新签返公司的设计师详情页中的其他设计师推荐列表
     * @param $userId
     * @param $companyId
     * @return array
     */
    public function otherDesignerByQianfanCompanyAndUserId($userId, $companyId)
    {
        $model = new UserCompanyEmployeeModel();
        $list = $model->otherDesignerByQianfanCompanyAndUserId($userId, $companyId);
        if (count($list) > 0) {
            $list = $this->changeList($list);
        }
        return $list;
    }


    /**
     * 根据条件获取默认排序下的设计师列表
     * @param $map
     * @return array
     */
    public function defaultSortDesignerListCount($map)
    {
        // 获取优质装修公司

        return array();
    }


}