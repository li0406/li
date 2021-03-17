<?php
// +----------------------------------------------------------------------
// | SubTagLogic 分站标识表
// +----------------------------------------------------------------------
namespace app\common\model\logic;

use app\common\model\db\SubTag;
use app\common\model\service\WechatService;
use think\Exception;
use think\Request;

class SubTagLogic
{
    public function getSendTagInfo($input, $tag)
    {
        if (empty($input['city_name'])) {
            return [];
        }
        foreach ($tag as $k => $v) {
            $tag[$k] = $input['city_name'] . $v;
        }
        //获取标签信息
        $tagDb = new SubTag();
        $tag_list = $tagDb->getTagListByName($tag)->toArray();
        //全部查出 就直接返回
        if (count($tag_list) == count($tag)) {
            return $tag_list;
        }

        //将查不到的添加到标签中
        $tag_list = array_column($tag_list, null, 'name');
        $new_save = [];
        foreach ($tag as $k => $v) {
            if (!isset($tag_list[$v])) {
                $new_save[] = $v;
            }
        }
        $this->addSubTag($input['cs'], $new_save);

        //获取最新的标签数据
        return $tagDb->getTagListByName($tag)->toArray();
    }

    public function addSubTag($cs, $tag_name = [])
    {
        if (count($tag_name) == 0) {
            return [];
        }
        $tagDb = new SubTag();
        $save = [];
        foreach ($tag_name as $k => $v) {
            $save[] = [
                'cs' => $cs,
                'name' => $v,
                'enabled' => 1,
                'created_at' => time(),
                'updated_at' => time(),
            ];
        }
        return $tagDb->addSubTag($save);
    }
}