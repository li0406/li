<?php

namespace Home\Model\Logic;

use Home\Model\Db\CasesModel;
use Home\Model\Db\UserModel;

class ManageCompanyLogicModel
{
    public function getKeywords($keyword)
    {
        $keywords = [];
        $search = [];   // 搜索关键字
        $replace = [];   //替换关键字
        preg_match_all('/\((.*)\)/U', $keyword, $keywords);
        if (count($keywords) > 0) {
            foreach ($keywords[1] as $v) {
                $keywords = explode(",", $v);
                $tmp = '/(' . $keywords[0] . ')/i';
                $tmp = str_replace('\'', '', $tmp);
                $search[] = $tmp;
                $replace[] = $keywords[1];
            }
            return ['search' => $search, 'replace' => $replace];
        } else {
            return [];
        }
    }

    public function replaceUser($user, $keywords)
    {
        $save['jc'] = preg_replace($keywords['search'], $keywords['replace'], $user['jc']);
        $save['qc'] = preg_replace($keywords['search'], $keywords['replace'], $user['qc']);
        $save['dz'] = preg_replace($keywords['search'], $keywords['replace'], $user['dz']);
        return (new UserModel())->saveUserInfo(['id' => ['eq', $user['id']]], $save);
    }

    public function replaceUserCompany($user_id, $keywords)
    {
        $model = new UserModel();
        $list = $model->getUserCompanyInfoById(['userid' => ['eq', $user_id]]);
        $save = [
            'kouhao' => preg_replace($keywords['search'], $keywords['replace'], $list['kouhao']),
            'text' => preg_replace($keywords['search'], $keywords['replace'], $list['text'])
        ];
        return $model->saveUserCompanyInfo(['userid' => ['eq', $user_id]], $save);
    }

    public function replaceCases($user_id, $keywords)
    {
        $db = new CasesModel();
        $where = [
            'uid'=>['eq',$user_id],
            'status'=>['eq',2],
        ];
        $list = $db->getCasesByCompany($where, 'id,title,text');
        $set_val = [];
        if (count($list) > 0) {
            $set_val['case_field'] = ['`id`'];
            $set_val['set_field'] = ['`title`','`text`'];
            $ids = '';
            foreach ($list as $k => $v) {
                $ids .= $v['id'].',';
                $title = preg_replace($keywords['search'], $keywords['replace'], $v['title']);
                $text = preg_replace($keywords['search'], $keywords['replace'], $v['text']);
                $set_val['corresponding_data'][0][$v['id']] = !empty($title) ? "'$title'" : '';
                $set_val['corresponding_data'][1][$v['id']] = !empty($title) ? "'$text'" : '';
            }
            $sql = updateAll('qz_cases', $set_val, " `id` in (" . substr($ids, 0, -1) . ")");
            $db->saveAllCases($sql);
        }
    }
}
