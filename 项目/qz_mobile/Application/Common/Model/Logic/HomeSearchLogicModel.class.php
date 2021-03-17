<?php
// +----------------------------------------------------------------------
// |  首页搜索逻辑
// +----------------------------------------------------------------------
namespace Common\Model\Logic;

use Common\Model\AskModel;
use Common\Model\Db\ErpUserRegisterModel;
use Common\Model\Db\ThematicWordsModel;
use Common\Model\WwwArticleClassModel;
use Common\Model\WwwArticleModel;

class HomeSearchLogicModel
{
    public function getList($param)
    {
        if (!$param['keywords']) {
            return [];
        }
        $server = C('SERVICE');
        if (empty($server)) {
            return [];
        }
        $p = !empty($param['p'])?(int)$param['p']:1;
        $size = !empty($param['size'])?(int)$param['size']:3;
        $module = !empty($param['module'])?(int)$param['module']:0;
        $content_count = 40;
        $url = $server['ELASTIC']['PROTOCOL'] . '://' . $server['ELASTIC']['DOMAIN'] . ':' . $server['ELASTIC']['PORT'] . '/v1/themtic/getlabel?title=' . $param['keywords'];
        $url .= '&type=1&limit=' . $content_count;
        $list = curl($url);
        if ($list['error_code'] != 0 || count($list['data']) == 0) {
            return [];
        }
        //获取指定内容
        $ids = array_unique(array_column($list['data'], 'id'));
        return $this->getContentInfo($ids, $module, ($p - 1) * $size, $size);
    }

    public function getContentInfo($ids,$module = 0,$p,$size){
        if(count($ids) == 0){
            return [];
        }
        $where['rel.thematic_id'] = ['in', $ids];
        if (!empty($module)) {
            switch ($module) {
                case 1:
                    //攻略
                    //获取文章id
                    $content = $this->getGonglue($where,$p,$size);
                    return $content;
                    break;
                case 2:
                    //百科
                    $baike = $this->getBaike($where,$p,$size);
                    return $baike;
                    break;
                case 3:
                    //问答
                    $wenda = $this->getWenda($where,$p,$size);
                    return $wenda;
                    break;
            }
        }
        //查看全部
        return $this->getAllContent($where);
    }

    public function getAllContent($where)
    {
        $returnData = [
            'gonglue' => [],
            'baike' => [],
            'wenda' => []
        ];
        $where['rel.module'] = ['eq',1];
        $returnData['gonglue'] = $this->getGonglue($where, 0, 3);
        $where['rel.module'] = ['eq',2];
        $returnData['baike'] = $this->getBaike($where, 0, 3);
        $where['rel.module'] = ['eq',3];
        $returnData['wenda'] = $this->getWenda($where, 0, 3);
        return $returnData;
    }

    public function getGonglue($where, $page = 0, $page_count = 3)
    {
        $where['w.state'] = ['eq', 2];
        $db = new ThematicWordsModel();
        $field = 'w.id,w.title,w.pv,w.likes,FROM_UNIXTIME(w.addtime,"%Y-%m-%d %H:%i:%s") addtime,w.face,w.subtitle';
        //如果是全部数据,则只查需要的内容信息
        $where['rel.module'] = ['eq', 1];
        $content = $db->getGonglueContentByIds($where,$field,$page, $page_count);
        //获取文章类型
        if(count($content) > 0){
            $class = (new WwwArticleClassModel())->getArticleClassByIds(array_column($content,'id'));
            $class = array_column($class,null,'article_id');
            foreach ($content as $k=>$v){
                if(isset($class[$v['id']])){
                    $content[$k]['shortname'] = $class[$v['id']]['shortname'];
                }
            }
        }
        return $content?:[];
    }

    public function getBaike($where, $page = 0, $page_count = 3,$is_all = 0)
    {
        $where['b.visible'] = ['eq', 0];
        $where['b.remove'] = ['eq', 0];

        $db = new ThematicWordsModel();
        $field = 'b.id,b.title,FROM_UNIXTIME(b.post_time,"%Y-%m-%d %H:%i:%s") post_time,b.views,b.description,b.thumb';
        //如果是全部数据,则只查需要的内容信息
        $where['rel.module'] = ['eq', 2];
        $baike = $db->getBaikeContentByIds($where,$field,$page, $page_count);
        return $baike?:[];
    }

    public function getWenda($where,$page = 0, $page_count = 3,$is_all = 0)
    {
        $where['a.visible'] = ['eq', 0];

        $db = new ThematicWordsModel();
        $wendaDb = new AskModel();
        $field = 'a.id,a.title,a.anwsers,FROM_UNIXTIME(a.post_time,"%Y-%m-%d") post_time';
        //如果是全部数据,则只查需要的内容信息
        $where['rel.module'] = ['eq', 3];
        $wenda = $db->getWendaContentByIds($where,$field,$page, $page_count);
        if(count($wenda) > 0){
            //获取问答人员信息
            $where = [
                'a.id'=>['in',array_column($wenda,'id')]
            ];
            $user = $wendaDb->getQuestionAndUserList($where,[],[],'a.id,c.name AS sub_category_name, u.name AS user_name, u.logo AS user_logo');
            $user = array_column($user,null,'id');
            foreach ($wenda as $k => $v) {
                if(isset($user[$v['id']])){
                    $wenda[$k]['sub_category_name'] = $user[$v['id']]['sub_category_name']?:'';
                    $wenda[$k]['user_name'] = $user[$v['id']]['user_name']?:'';
                    $wenda[$k]['user_logo'] = $user[$v['id']]['user_logo']?:'';
                }
                $wenda[$k]['user_name'] = $user[$v['id']]['user_name'] ?: '';
                $wenda[$k]['user_logo'] = $user[$v['id']]['user_logo'] ?: '//' . C('QINIU_DOMAIN') . '/Public/default/images/default_logo.png';
                $wenda[$k]['sub_category_name'] = $user[$v['id']]['sub_category_name'] ?: '';
            }
        }
        return $wenda?:[];
    }
}