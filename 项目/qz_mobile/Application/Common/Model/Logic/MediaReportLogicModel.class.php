<?php
// +----------------------------------------------------------------------
// | MediaReportLogicModel
// +----------------------------------------------------------------------
namespace Common\Model\Logic;


use Common\Model\Db\MediaReportModel;

class MediaReportLogicModel
{
    public function getMediaReports($param = [])
    {
        $where = [];
        if (!empty($param['title'])) {
            $where['title'] = ['like', '%' . $param['title'] . '%'];
        }
        $mediaReportmodel = new MediaReportModel();
        $count = $mediaReportmodel->getCount($where);
        if (!empty($count)) {

            import('Library.Org.Page.MobilePage');
            $p = I('get.p', 1, 'intval');
            $pageIndex = $p <= 0 ? 1 : $p;
            $page = new \MobilePage($pageIndex, 10, $count, ['prev', 'next'], '', '');
            $show = $page->show2();
            $list = $mediaReportmodel->getList($where, ($pageIndex - 1) * 10, 10);
            return ['list' => $list, 'page' => $show];
        }
        return ['list' => [], 'page' => ''];
    }
}