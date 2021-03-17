<?php
// +----------------------------------------------------------------------
// | CommentsLogicModel
// +----------------------------------------------------------------------
namespace Home\Model\Logic;

use Home\Model\Db\CommentsModel;

class CommentsLogicModel
{
    protected $commentsModel;

    public function __construct()
    {
        $this->commentsModel = new CommentsModel();
    }

    public function getStatisticsByCompany($param, $pageSize = 20)
    {
        $map = [];
        if (!empty($param['city'])) {
            $map['u.cs'] = $param['city'];
        }
        $count = $this->commentsModel->statisticsByCompanyCount($map);
        $result = ['page' => '', 'list' => [], 'totals' => $count];
        if (!empty($count['count'])) {
            import('Library.Org.Util.Page');
            $p = new \Page($count['count'], $pageSize);
            $result['page'] = $p->show();
            $result['list'] = $this->commentsModel->statisticsByCompany($map, $p->firstRow, $p->listRows);
        }
        return $result;
    }


    //验证id是否在用户中心里面有对应数据
    public function checkCommentUserid($ids)
    {
        $map = [];
        if (is_array($ids)) {
            $map['c.id'] = array('in', $ids);
        } else {
            $map['c.id'] = $ids;
        }
        $list = $this->commentsModel->checkCommentUserid($map);
        $commentlist = [];
        if (count($list) > 0) {
            foreach ($list as $key => $val) {
                $commentlist[$key]['id'] = $val['id'];
                $commentlist[$key]['userid'] = $val['userid'];
            }
            return $commentlist;
        } else {
            return [];
        }


    }


}