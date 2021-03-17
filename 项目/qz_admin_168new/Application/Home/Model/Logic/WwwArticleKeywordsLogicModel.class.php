<?php

namespace Home\Model\Logic;
use Home\Model\WwwArticleKeywordsModel;

/**
 *
 */
class WwwArticleKeywordsLogicModel
{
    public function findWord($word,$module)
    {
        $model = new WwwArticleKeywordsModel();
        return $model->findWord($word,$module);
    }
}