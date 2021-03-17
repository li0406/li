<?php


namespace Common\Model\Logic;


use Common\Model\CommentModel;

class CommentLogicModel
{
    public function getYeZhuComment($cs, $limit)
    {
        $commentModel = new CommentModel();
        $list = $commentModel->getYeZhuComment($cs, $limit);
        foreach ($list as $key => $val) {
            $list[$key]["avg"] = round(($val["sj"] + $val["fw"] + $val["sg"]) / 3, 2);
            $list[$key]["url"] = "//" . $val["bm"] . "." . C("QZ_YUMING") . "/company_home/" . $val["comid"] . "/";
        }
        $list = $this->getStar($list);

        return $list;

    }


    /**
     * 计算星星
     */
    private function getStar($list)
    {
        foreach ($list as $key => $value) {
            if ($value["avg"] >= 9) {
                $list[$key]["star"] = 5;
            } elseif ($value["avg"] >= 8 && $value["avg"] < 9) {
                $list[$key]["star"] = 4;
            } elseif ($value["avg"] >= 6 && $value["avg"] < 8) {
                $list[$key]["star"] = 3;
            } elseif ($value["avg"] >= 4 && $value["avg"] < 6) {
                $list[$key]["star"] = 2;
            } else {
                $list[$key]["star"] = 1;
            }
        }
        return $list;
    }

}