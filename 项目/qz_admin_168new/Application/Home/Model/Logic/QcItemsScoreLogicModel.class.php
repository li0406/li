<?php

namespace Home\Model\Logic;

class QcItemsScoreLogicModel
{
    public function getQcItem($type = 1)
    {
        $list = [];
        $result = D("Home/Db/QcItemsScore")->getQcItem($type);
        foreach ($result as $key => $value) {
            if ($value["parentid"] == 0) {
                switch ($value["group"]) {
                    case '1':
                        $list[$value["group"]]["name"] = "服务";
                        break;
                    case '2':
                        $list[$value["group"]]["name"] = "沟通";
                        break;
                    case '3':
                        $list[$value["group"]]["name"] = "业务";
                        break;
                }
                $list[$value["group"]]["child"][$value["id"]]["id"] = $value["id"];
                $list[$value["group"]]["child"][$value["id"]]["name"] = $value["name"];
            } else {
                $list[$value["group"]]["child"][$value["parentid"]]["child"][] = $value;
            }
        }
        return $list;
    }

    public function getOrderScoreItem($order_id,$type)
    {
        $ids = [];
        $result = D("Home/Db/QcItemsScore")->getOrderScoreItem($order_id,$type);
        foreach ($result as $key => $value) {
            $ids[] = $value["qc_item_id"];
        }
        return $ids;
    }

    public function delQcScoreItemByIds($order_id,$ids)
    {
        return D("Home/Db/QcItemsScore")->delQcItemByIds($order_id,$ids);
    }

    public function addQcScoreItem($data)
    {
        return D("Home/Db/QcItemsScore")->addQcScoreItem($data);
    }

    public function getQcInfoById($order_id)
    {
        return D("Home/Db/QcItemsScore")->getQcInfoById($order_id);
    }
}