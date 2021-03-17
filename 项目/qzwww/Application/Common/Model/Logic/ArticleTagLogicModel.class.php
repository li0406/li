<?php
// +----------------------------------------------------------------------
// | ArticleTagsLogicModel  artciletag逻辑层
// +----------------------------------------------------------------------

namespace Common\Model\Logic;

use Common\Model\Db\WwwArticleTagsModel;

class ArticleTagLogicModel
{
    /**
     * 获取处理完成的标签列表数据
     *
     * @param int $style 展示页
     * @param int|boolean $position 位置
     */
    public function getTagData($style, $position = false)
    {
        if (empty($style) && $style !== 0) {
            return false;
        }
        $map['style'] = $style;
        if ($position !== false) {
            $map['position'] = $position;
        }
        $tagModel = new WwwArticleTagsModel();

        /********** 按照添加时间倒序查询到所有数据 **********/
        $list = $tagModel->getList($map);

        $category = $sortbyArray = [];

        if (!empty($list)) {
            foreach ($list as $k => $v) {
                if ($v['p_id'] == 0) {;
                    $category[$v['id']]['id'] = $v['id'];
                    $category[$v['id']]['name'] = $position == 1 ? mbstr($v['name'], 0, 8, 'utf-8', false) : ($position == 2 ? mbstr($v['name'], 0, 8, 'utf-8', false) : $v['name']);
                    $sortbyArray[] = $v['addtime'];
                    unset($k);
                }
            }
            /********** 按照添加时间正序重新排列 **********/
            sort($sortbyArray,SORT_NUMERIC);
            array_multisort($sortbyArray,SORT_DESC,$category);
            if(!empty($category)){
                foreach ($list as $k => $v) {
                    foreach ($category as $k1 => $value) {
                        if ($v['p_id'] == $value['id']) {
                            $category[$k1]['sub_tags'][$v['id']] = [
                                'id' => $v['id'],
                                'name' => $position == 1 ? mbstr($v['name'], 0, 10, 'utf-8', false) : ($position == 2 ? mbstr($v['name'], 0, 10, 'utf-8', false) : $v['name']),
                                'url' => $v['url'],
                                'm_url' => $v['m_url']
                            ];
                        }
                    }
                }
            }
            foreach ($category as $k2 => $value) {
                if (empty($value['sub_tags'])) {
                    unset($category[$k2]);
                } else {
                    $category[$k2]['sub_tags'] = $position == 1 ? array_slice($category[$k2]['sub_tags'],0,35) : ($position == 2 ? array_slice($category[$k2]['sub_tags'],0,10) : $category[$k2]['sub_tags']);
                }
            }
        }
        return array_slice($category,0,8);
    }
}