<?php

namespace Common\Model\Logic;

use Common\Model\Db\UserCompanyEmployeeModel;
use Common\Model\DesignerModel;
use Common\Enums\CompanyCodeEnum;

class DesignerLogicModel
{

    /**
     * @param $cs // 城市
     * @param $map // 查询条件？  这边不需要这个
     * @param $pageIndex // 当前页码
     * @param $pageCount // 每页显示的数量
     * @param $order // 排序规则？  这边不需要这个
     * @param $condition // 包含了风格【fengge】，从业时间【jobtime】，设计级别【zw】
     * @param $reqPrice // 收费  1:不限，2:面议，3:1-100，4:101-200，5:201-300，6: 大于300
     * @param $sortValue // 排序 ''表示默认， 1表示作品量 ， 2表示人气值
     * @return array|null
     */
    public function getDesignersV2($cs, $pageIndex, $pageCount, $condition, $reqPrice, $sortValue = '')
    {
        $model = new DesignerModel();

        $map1 = [];
        // 真会员数据未洗干净，加个筛选条件
        $map1['m.zw'] = array("in", ['设计师', '首席设计师', '设计总监']);
        $map2 = [];
        $map2['ce.position'] = array('in', [2,3,4]);

        // 擅长风格
        if (isset($condition['fengge']) && !empty($condition["fengge"])) {
            $map1['ud.fengge'] = array("like", '%' . $condition['fengge'] . '%');
            $map2['ud.fengge'] = array("like", '%' . $condition['fengge'] . '%');
        }

        // 从业时间
        if (isset($condition['jobtime']) && !empty($condition["jobtime"])) {
            $map1['ud.jobtime'] = array("EQ", $condition['jobtime']);
            $map2['ud.jobtime'] = array("EQ", $condition['jobtime']);
        }

        //设计级别
        if (isset($condition['zw']) && !empty($condition["zw"])) {
            $map1['m.zw'] = array("EQ", $condition['zw']);
            switch ($condition['zw']) {
                case '设计师' :
                    $map2['ce.position'] = array("EQ", 2);
                    break;
                case '首席设计师' :
                    $map2['ce.position'] = array("EQ", 3);
                    break;
                case '设计总监' :
                    $map2['ce.position'] = array("EQ", 4);
                    break;
                default :
                    $map2['ce.position'] = array('in', [2,3,4]);
                    break;
            }
        }

        //设计收费
        switch ($reqPrice) {
            case 2 :
                //面议，没有价格
                $map1['_string'] = "ud.cost ='' or ud.cost=0";
                $map2['_string'] = "ud.cost ='' or ud.cost=0";
                break;
            case 3 :
                //0-100
                $map1['_string'] = "substring_index(ud.cost, '-', 1)>=1 and substring_index(ud.cost, '-', 1) <=100 and ud.cost !=''";
                $map2['_string'] = "substring_index(ud.cost, '-', 1)>=1 and substring_index(ud.cost, '-', 1) <=100 and ud.cost !=''";
                break;
            case 4 :
                //100-200
                $map1['_string'] = "substring_index(ud.cost, '-', 1)>=101 and substring_index(ud.cost, '-', 1) <=200 and ud.cost !=''";
                $map2['_string'] = "substring_index(ud.cost, '-', 1)>=101 and substring_index(ud.cost, '-', 1) <=200 and ud.cost !=''";
                break;
            case 5 :
                //200-300
                $map1['_string'] = "substring_index(ud.cost, '-', 1)>=201 and substring_index(ud.cost, '-', 1) <=300 and ud.cost !=''";
                $map2['_string'] = "substring_index(ud.cost, '-', 1)>=201 and substring_index(ud.cost, '-', 1) <=300 and ud.cost !=''";
                break;
            case 6 :
                //>300
                $map1['_string'] = "substring_index(ud.cost, '-', 1)>=301";
                $map2['_string'] = "substring_index(ud.cost, '-', 1)>=301";
                break;
            default :
                break;

        }

        $count = $model->getDesignerListCount3($cs, $map1, $map2);

        if ($count > 0) {
            import('Library.Org.Page.LitePage');
            //自定义配置项
            $config = array("prev", "next");
            $page = new \LitePage($pageIndex, $pageCount, $count, $config);
            $pageTmp = $page->show();
            $users = $model->getDesignerList3($cs, $map1, $map2, $sortValue, ($page->pageIndex - 1) * $pageCount, $pageCount);

            foreach ($users as $key => $value) {
                $ids[] = $value["userid"];
                if ($value['zw'] == '0') {
                    $users[$key]['zw'] = '';
                }
            }

            if (empty($ids)) {
                $ids = '';
            }
            //获取设计师最新的2个案例数

            foreach ($users as $key => $value) {
                //清洗数据
                $users[$key]['fengge'] = !empty($value['fengge']) ? $value['fengge'] : '';
                $users[$key]['lingyu'] = !empty($value['lingyu']) ? $value['lingyu'] : '';
                $users[$key]['linian'] = !empty($value['linian']) ? $value['linian'] : '';

                //cost 设计师价格
                $costPrice = !empty($value['cost']) ? $value['cost'] . '元/平米' : '面议';
                $users[$key]['cost_price'] = $costPrice;
                $users[$key]["child"] = $cases = D("Common/Designer")->getLastDesigner($value['userid'], $value['comid'], 3);;

                $appointmentName = $value['name'];
                if (mb_strlen($appointmentName) > 6) {
                    $appointmentName = mb_substr($appointmentName, 0, 6);
                }
                $users[$key]['appointment_name'] = $appointmentName;
//                if (empty($value['linian'])) {
//                    $value['linian'] = ' 这个人很懒,什么都没有留下';
//                } elseif (mb_strlen($value['linian']) > 80) {
//                    $value['linian'] = mb_substr($value['linian'], 0, 80) . '...';
//                }
//                foreach ($cases as $k => $val) {
//                    if ($value["userid"] == $val["userid"]) {
//                        $users[$key]["child"][] = $val;
//                    }
//                }
            }


            return array("users" => $users, "page" => $pageTmp);
        }

        return null;
    }


    // 获取首页人气设计师
    public function getPopularDesigner($cs)
    {
        $userCompanyEmployeeModel = new UserCompanyEmployeeModel();
        $list = $userCompanyEmployeeModel->getPopularDesigner($cs, 6);
        foreach ($list as $key => $val) {
            switch ($val["zw"]) {
                case 2:
                    $list[$key]["zw"] = "设计师";
                    break;
                case 3:
                    $list[$key]["zw"] = "首席设计师";
                    break;
                case 4:
                    $list[$key]["zw"] = "设计总监";
                    break;
                default :
                    $list[$key]["zw"] = "设计师";
                    break;
            }


            $list[$key]["logo"] = $val["logo"] ? changeImgUrl($val["logo"]) : changeImgUrl(CompanyCodeEnum::EMPLOYEE_DEFAULT_LOGO);
            $list[$key]["company_type"] = 2;
        }

        return $list;
    }

}