<?php

namespace Home\Model\Logic;

use Home\Model\Db\ContentCategoryParticipleModel;

/**
 *
 */
class BaikeLogicModel
{
    public function setClassParticiple($category_id, $words)
    {
        $all = [];
        foreach ($words as $word) {
            $all[] = [
                "category_id" => $category_id,
                "word" => $word,
                "module" => 2,
                "created_at" => time()
            ];
        }
        $model = new ContentCategoryParticipleModel();
        return $model->addAllInfo($all);
    }

    public function delClassParticiple($category_id, $module)
    {
        $model = new ContentCategoryParticipleModel();
        return $model->delInfo($category_id, $module);
    }

    public function getBaikeList($input,$user,$statusArray)
    {
        $map = [];
        //指定角色可以查看全部数据
        if(!in_array($user['uid'],[1,69])){
            $map['a.uid'] = $user['id'];
        }
        /*S-筛选条件获取*/
        //分类筛选
        if (!empty($input['category'])) {
            $map['a.cid'] = intval($input['category']);
        }
        if (!empty($input['sub_category'])) {
            $map['a.sub_category'] = intval($input['sub_category']);
        }
        //标题或ID筛选
        if (!empty($input['keyword'])) {
            $map["_complex"] = array(
                'a.id' => intval($input['keyword']),
                'a.title' => array('LIKE', '%' . $input['keyword'] . '%'),
                'a.item' => array('LIKE', '%' . $input['keyword'] . '%'),
                '_logic' => 'OR'
            );
        }
        //发布人来源获取
        if (!empty($input['source'])) {
            if ('1' == $input['source']) {
                $map['a.source'] = 1;
            } else if ('2' == $input['source']) {
                $map['a.source'] = 0;
            }
        }

        //只获取删除状态为0的
        $map['remove'] = 0;
        //状态获取
        if (!empty($input['status'])) {
            if (isset($statusArray[$input['status']])) {
                $map['_string'] = $statusArray[$input['status']]['sql'];
                if ($input['status'] == 4) {
                    unset($map['remove']);
                }
            }
        }

        //分页筛选
        $pageIndex = 1;
        if (!empty($input["p"])) {
            $pageIndex = $input["p"];
        }
        $pageCount = 10;
        if (!empty($input["page_count"])) {
            if (isset($pageArray[$input["page_count"]])) {
                $pageCount = $pageArray[$input["page_count"]]['value'];
            }
        }
        /*S-筛选条件获取*/

        unset($user);
        //获取结果
        $result = D("Adminbaike")->getList($map, ($pageIndex - 1) * $pageCount, $pageCount);

        $list = $result['result'];
        //分页
        import('Library.Org.Util.Page');
        $page = new \Page($result['count'], $pageCount);
        $show = $page->show();
        return ['list' => $list, 'page' => $show];
    }
}