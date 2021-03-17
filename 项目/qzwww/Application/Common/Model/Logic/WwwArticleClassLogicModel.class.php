<?php

namespace Common\Model\Logic;

use Common\Model\Db\ContentCategoryParticipleModel;
use Common\Model\WwwArticleClassModel;

class WwwArticleClassLogicModel
{
    protected $articleClassModel;

    public function __construct()
    {
        $this->articleClassModel = new WwwArticleClassModel();
    }

    //根据他爷爷的id获取三级子分类
    public function getByGrandParent($grandClass, $count)
    {
        $data = $this->articleClassModel->getByGrandParent($grandClass, $count);
        return $data;
    }

    public function getArticleClassIdsByClass($grandClass)
    {
        $data = $this->articleClassModel->getArticleClassIdsByClass($grandClass);
        //处理数据
        return $data;
    }

    public function getByGrandClassAndShortName($grandClass,$shortname)
    {
        $data = $this->articleClassModel->getByGrandClassAndShortName($grandClass,$shortname);
        //处理数据
        return $data;
    }

    public function getParentClassBySonClass($id)
    {
        $data = $this->articleClassModel->getParentClassBySonClass($id);
        //处理数据
        return $data;
    }

    public function getArticleClassParticiple($category_id,$module)
    {
        $model  = new ContentCategoryParticipleModel();
        $result = $model->getArticleClassParticiple($category_id,$module);
        $word = "";
        if (count($result) > 0) {
            $word = implode(" ", array_column($result,"word"));

        }
        return $word;
    }
}
