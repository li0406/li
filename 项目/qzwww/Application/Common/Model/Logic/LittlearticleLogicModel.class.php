<?php
/**
 * 分站文章逻辑层
 */
namespace Common\Model\Logic;

class LittlearticleLogicModel
{
    /**
     * 获取分站装修公司列表页的文章
     * @param  string $cs 文章类别
     * @param  int $page 页码
     * @param  int $pageCount 每页数量
     * @return array
     */
    public static function getRecomArticleList($cs, $pageIndex = null, $pageCount = null)
    {
        if (empty($cs)) {
            return [];
        }
        $map['a.state'] = 2;
        $map['a.is_to_subcompany'] = 1;
        $map['a.cs'] = $cs;
        return M('little_article')
            ->alias('a')
            ->where($map)
            ->join('qz_infotype b on b.id = a.classid')
            ->join('qz_quyu c on a.cs = c.cid')
            ->field('a.id,a.cs,a.state,a.authid,a.classid,a.title,a.description,a.keywords,a.face,a.addtime,c.bm')
            ->order('addtime desc,id asc')
            ->page($pageIndex, $pageCount)
            ->select();
    }
}