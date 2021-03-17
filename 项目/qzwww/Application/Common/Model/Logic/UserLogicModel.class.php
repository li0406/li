<?php

namespace Common\Model\Logic;

use Common\Model\CasesModel;
use Common\Model\Db\UserCompanyRankModel;
use Common\Model\QuyuModel;
use Common\Model\UserModel;

class UserLogicModel
{
    public function getDesignerInfoByCompany($company_id = [], $qianfancompanyIds = [],$num = 30)
    {
        if ((count($company_id) == 0) && (count($qianfancompanyIds) == 0)) {
            return [];
        }
        $userDb = new UserModel();
        $designer = $userDb->getDesingerInfoByCompany($company_id, $qianfancompanyIds, 0,$num);
        if (count($designer) > 0) {
            $caseDb = new CasesModel();
            //获取城市
            $cityDb = new QuyuModel();
            $city = $cityDb->getCityByCid(array_column($designer,'cs'));
            $city = array_column($city,null,'cid');
            foreach ($designer as $k => $v) {
                $designer[$k]['case_count'] = 0;
                //获取案例数
                $case = $caseDb->getDesingerCaseInfoCountv3($v['id'], $v['comid']);
                if (!empty($case)) {
                    $designer[$k]['case_count'] = $case['case_count'] ? $case['case_count'] : 0;
                }
                if (isset($city[$v['cs']])) {
                    $designer[$k]['bm'] = $city[$v['cs']]['bm'];
                }
            }
        }
        return $designer;
    }

	public function getPackageInfo($company_id)
    {
        $model = new UserModel();
        return $model->getPackageInfo($company_id);
    }

    public function getHomeCompanyUser(){
        $rankDb = new UserCompanyRankModel();
        return $rankDb->getUserYesterdayRank();
    }
}
