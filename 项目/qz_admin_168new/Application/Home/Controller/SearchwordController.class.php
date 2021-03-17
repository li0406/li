<?php

namespace Home\Controller;
use Home\Common\Controller\HomeBaseController;

/**
 * 统计功能
 */
class SearchwordController extends HomeBaseController
{
    /**
     * [ 搜索关键词统计]
     * @return [type] [description]
     * @return [type] [description]
     */
    public function index()
    {
        //初始化时间
        $lastDate = strtotime("-1 month") * 1000;
        $this->assign('lastDate',$lastDate);

        $p = I("get.p");
        $pageIndex = 1;
        if(!empty($p)){
            $pageIndex = $p;
        }
        $pageCount = 20;

        $keyword = $_GET;
        $list = $this->getSearchWords($keyword,$pageIndex,$pageCount);

        $this->assign('list',$list['list']);
        $this->assign('page',$list['page']);
        $this->assign('keyword',$keyword);
        $this->assign('indexStart', (($pageIndex - 1) * $pageCount));
        $this->display();
    }

    /**
     * [ 搜索关键词查询]
     * @param array $keyword    查询条件
     * @param string $pageIndex    页码
     * @param string $pageCount    分页数
     * @return [array] $result
     */
    private function getSearchWords($map,$pageIndex,$pageCount=20)
    {
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = intval($pageCount) <= 0 ? 1 : intval($pageCount);

        $list = D('Searchword')->getSearchWords($map,($pageIndex-1)*$pageCount,$pageCount);
        if($list['count'] > $pageCount){
            import('Library.Org.Util.Page');
            $page = new \Page($list['count'],$pageCount);
            $result['page'] = $page->show();
        }
        $result['list'] = $list['list'];
        $result['totalnum'] = $list['count'];
        return $result;
    }


    
}