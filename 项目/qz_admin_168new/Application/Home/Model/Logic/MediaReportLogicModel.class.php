<?php
// +----------------------------------------------------------------------
// | MediaReportLogicModel
// +----------------------------------------------------------------------

/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/5
 * Time: 11:32
 */

namespace Home\Model\Logic;


use Home\Model\Db\MediaReportModel;

class MediaReportLogicModel
{
    public function getMediaReports($param)
    {
        $where = [];
        if (!empty($param['title'])) {
            $where['title'] = ['like', '%' . $param['title'] . '%'];
        }
        $mediaReportmodel = new MediaReportModel();
        $count = $mediaReportmodel->getCount($where);
        if (!empty($count)) {
            import('Library.Org.Util.Page');
            $p = new \Page($count, 20);
            $show = $p->show();
            $list = $mediaReportmodel->getList($where, $p->firstRow, $p->listRows);
            return ['list' => $list, 'page' => $show];
        }
        return ['list' => [], 'page' => ''];
    }

    public function getMediaReport($id)
    {
        $mediaReportmodel = new MediaReportModel();
        $map['id'] = $id;
        return $mediaReportmodel->getInfo($map);
    }

    public function filterMediaReport($post)
    {
        if (empty($post['title'])) {
            return '请输入标题：1-25个中文字符';
        }
        $length = mb_strlen((string)$post['title']);
        if ($length < 1 || $length > 25) {
            return '请输入标题2：1-25个中文字符';
        }

        $mediaReportmodel = new MediaReportModel();
        $map['title'] = ['EQ', $post['title']];
        if (!empty($post['id'])) {
            $map['id'] = ['NEQ', $post['id']];
        }
        $mediaReportmodel->getInfo($map);
        if ($mediaReportmodel->getInfo($map)) {
            return '请重新输入标题，标题重复';
        }

        if (empty($post['description'])) {
            return '请输入简介：1-1000个中文字符';
        }
        $length = mb_strlen((string)$post['description']);
        if ($length < 1 || $length > 1000) {
            return '请输入简介：1-1000个中文字符';
        }

        if (empty($post['url'])) {
            return '请输入完整url';
        }
        if (!filter_var($post['url'], FILTER_VALIDATE_URL)) {
            return '请输入完整url';
        }
        if (!isset($post['sequence'])) {
            return '排序请输入大于0整数（数字正序排列）';
        }
        if ($post['sequence'] < 0 || !filter_var($post['sequence'], FILTER_VALIDATE_INT)) {
            return '排序请输入大于0整数（数字正序排列）';
        }
        if (empty($post['image'])) {
            return '请上传图片';
        }
        if (empty($post['publish_time'])) {
            return '请输入发布时间';
        }
        return true;
    }

    public function saveMediaReport($post)
    {
        $map = [];
        if (!empty($post['id'])) {
            $map['id'] = $post['id'];
        }
        $mediaReportmodel = new MediaReportModel();
        return $mediaReportmodel->saveinfo($post, $map);
    }

    public function delMediaReport($id)
    {
        if (empty($id)) {
            return false;
        }

        $map['id'] = ['in', $id];
        $mediaReportmodel = new MediaReportModel();
        return $mediaReportmodel->dellist($map);
    }

}