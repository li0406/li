<?php
namespace Common\Model\Logic;
use Common\Model\Db\ContentCategoryParticipleModel;

/**
 *
 */
class WwwArticleClassLogicModel
{
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