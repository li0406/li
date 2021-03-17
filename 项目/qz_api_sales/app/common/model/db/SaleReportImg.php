<?php

namespace app\common\model\db;

use app\index\enum\SalesReportCodeEnum;
use think\Model;
use think\db\Query;

class SaleReportImg extends Model {

    protected $autoWriteTimestamp = false;

    public function setSaleReportImg($report_id, $cooperation_type, $imgs, $img_type = SalesReportCodeEnum::REPORT_IMG_BB)
    {
        // 删除原图片
        $this->deleteByUnique($report_id, $cooperation_type,$img_type);

        if (!empty($imgs) && is_string($imgs)) {
            $imgList = explode(",", $imgs);
            $imgList = array_values(array_filter(array_unique($imgList)));

            $rows = [];
            foreach ($imgList as $key => $img) {
                $rows[] = array(
                    "report_id" => $report_id,
                    "cooperation_type" => $cooperation_type,
                    "img_type" => $img_type,
                    "created_at" => time(),
                    "img_path" => $img,
                    "sort" => $key
                );
            }

            $result = $this->insertAll($rows);
        }
        
        return true;
    }

    /**
     * 获取图片
     * @param  [type] $report_id        [description]
     * @param  [type] $cooperation_type [description]
     * @return [type]                   [description]
     */
    public function getByUnique($report_id, $cooperation_type){
        $map = new Query();
        $map->where("report_id", $report_id);
        $map->where("cooperation_type", $cooperation_type);
        return $this->where($map)->order("sort asc")->select();
    }

    /**
     * 根据组合ID删除图片
     * @param  [type] $report_id        [description]
     * @param  [type] $cooperation_type [description]
     * @return [type]                   [description]
     */
    public function deleteByUnique($report_id, $cooperation_type,$img_type = 1){
        $map = new Query();
        $map->where("report_id", $report_id);
        $map->where("cooperation_type", $cooperation_type);
        $map->where("img_type", $img_type);
        return $this->where($map)->delete();
    }

}