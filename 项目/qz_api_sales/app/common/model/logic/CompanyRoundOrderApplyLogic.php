<?php

namespace app\common\model\logic;

use app\common\model\db\UserCompanyRoundOrderApply;
use Util\Page;

class CompanyRoundOrderApplyLogic {

    protected $roundOrderApplyModel;

    public function __construct(){
        $this->roundOrderApplyModel = new UserCompanyRoundOrderApply();
    }

    // 补轮申请列表
    public function getRoundOrderApplyList($input, $page = 1, $limit = 10){
        $count = $this->roundOrderApplyModel->getRoundApplyPageCount($input);
        $pageobj = new Page($page, $limit, $count);
        $pageshow = $pageobj->show();

        $list = [];
        if ($count > 0) {
            $list = $this->roundOrderApplyModel->getRoundApplyPageList($input, $pageobj->firstrow, $pageobj->pageSize);
            foreach ($list as $key => &$item) {
                $item["apply_date"] = date("Y-m-d H:i:s", $item["created_at"]);
                $item["exam_date"] = $item["exam_time"] ? date("Y-m-d H:i:s", $item["exam_time"]) : "";

                unset($item["fen_num"], $item["apply_num"]);
            }
        }

        return [
            "list" => $list,
            "page" => $pageshow
        ];
    }
}