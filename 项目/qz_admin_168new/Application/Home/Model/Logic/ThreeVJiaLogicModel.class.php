<?php

namespace Home\Model\Logic;

use Home\Model\Db\ThreeVJiaUserRegisterModel;

class ThreeVJiaLogicModel
{
    public function getRegisterList()
    {
        $model = new ThreeVJiaUserRegisterModel();
        $count = $model->getRegisterListCount();

        if ($count > 0) {
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = $model->getRegisterList($p->firstRow,$p->listRows);
            foreach ($list as $key => $value) {
                switch ($value["industry"]){
                    case 1: $value["industry"] = '定制家居';break;
                    case 2: $value["industry"] = '顶墙装饰';break;
                    case 3: $value["industry"] = '成品家居';break;
                    case 4: $value["industry"] = '瓷砖卫浴';break;
                    default: $value["industry"] = '其他';
                }

                switch ($value["type"]){
                    case 1: $value["type"] = '品牌商/工厂';break;
                    case 2: $value["type"] = '装修公司';break;
                    case 3: $value["type"] = '门店';break;
                    case 4: $value["type"] = '工作室';break;
                    case 5: $value["type"] = '设计师';break;
                    default: $value["type"] = '其他';
                }

                $list[$key]["industry"] = $value["industry"];
                $list[$key]["type"] = $value["type"];
            }
        }
        return array("page"=>$show,"list"=>$list);
    }
}