<?php

namespace app\common\model\db;

use think\Db;
use think\Model;
use think\db\Query;

class WorksiteMedia extends Model {

    protected $autoWriteTimestamp = false;

    /**
     * 处理施工信息多媒体文件
     * @param $info_id
     * @param $media_urls
     * @param int $media_type
     * @return bool
     */
    public function setInfoMedia($info_id, $media_urls, $media_type = 1){
        // 删除原有图片信息
        $this->deleteMediaByInfoId($info_id);

        if (!empty($media_urls)) {
            $url_list = explode(",", $media_urls);
            $url_list = array_values(array_filter(array_unique($url_list)));

            $rows = [];
            foreach ($url_list as $key => $url) {
                $rows[] = array(
                    "info_id" => $info_id,
                    "type" => $media_type,
                    "url" => $url,
                    "sort" => $key,
                    "created_at" => time(),
                    "updated_at" => time()
                );
            }

            Db::name("worksite_media")->insertAll($rows);
        }

        return true;
    }

    /**
     * 删除施工信息多媒体数据
     * @param $info_id
     * @return int
     */
    public function deleteMediaByInfoId($info_id){
        return Db::name("worksite_media")->where("info_id", $info_id)->delete();
    }

    public static function deleteByInfoIds($infoIds){
        return static::where("info_id", "IN", $infoIds)->delete();
    }
}