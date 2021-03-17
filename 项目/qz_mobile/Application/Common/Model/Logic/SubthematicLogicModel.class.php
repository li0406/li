<?php
//分站专题逻辑
namespace Common\Model\Logic;


use Common\Model\Db\LittleArticleModel;
use Common\Model\Db\SubthematicModel;

class SubthematicLogicModel
{
    //专题基本信息
   public function getInfoByUrl($cs,$url){
       $model = new SubthematicModel();
       $result = $model->getInfoByUrl($cs,$url);
       return $result;
   }

    //专题公司列表
    public function getCompanyList($id){
        $model = new SubthematicModel();
        $result = $model->getCompanyList($id);
        $scheme_host = getSchemeAndHost();
        foreach($result as $key=>$val){
            $result[$key]['logo'] =  empty($val['logo'])?$scheme_host["scheme_full"].C('QINIU_DOMAIN')."/Public/default/images/default_logo.png":$val['logo'];
        }
        return $result;
    }

    //专题公司案例列表
    public function getCasesList($id){
        $model = new SubthematicModel();
        $result = $model->getCasesList($id);
        return $result;
    }

    public function getZhuantiList($cs){
        $model = new SubthematicModel();
        $result = $model->getZhuantiList($cs);
        return $result;
    }

    public function getArticleList($tag_id,$cs){
        $model = new LittleArticleModel();
        $result = $model->getArticleList($tag_id,$cs);

        foreach($result as $key=>$val){
            $val['content'] = strip_tags( $val['content']);
            $result[$key]['content'] = mbstr( $val['content'],0,120);
        }
        return $result;
    }

    public function getArticleListV2($tag_id,$cs){
        $model = new LittleArticleModel();
        $result = $model->getArticleListV2($tag_id,$cs);

        foreach($result as $key=>$val){
            $val['content'] = strip_tags( $val['content']);
            $result[$key]['content'] = mbstr( $val['content'],0,120);
        }
        return $result;
    }

    //获取首页底部专题显示
//    public function getIndexZhuantiList($cs,$limit){
//        $model = new SubthematicModel();
//        return $model->getZhuantiList($cs,$limit,1);
//    }

    //获取文章详情页底部专题页
    public function getArticleListByTag($tags,$cs){
        if(!empty($tags)){
            $tags = explode(',',$tags);
            $model = new SubthematicModel();
            return $model->getZhuantiListByTag($tags,$cs);
        }
    }

    //文章标识-获取文章详情页底部专题页
    public function getArticleListBySubTag($article_id,$cs){
        //获取文章对应的标识
        $model = new SubthematicModel();
        $tags = $model->getSubTag($article_id);
        if(!empty($tags)){
            $tags = array_column($tags,'tag_id');
            return $model->getZhuantiListBySubTag($tags,$cs);
        }
    }

    //获取公司标识对应专题
    public function getNameByCompanyTag($id,$cs){
        //获取公司对应的标识
        $model = new SubthematicModel();
        $tags = $model->getSubTagByCompany($id);
        if(!empty($tags)){
            $tags = array_column($tags,'tag_id');;
            $result = $model->getZhuantiListBySubTag($tags,$cs);
            return $result;
        }
    }

    //获取公司标识对应专题
    public function getNameByCaseTag($id,$cs){
        //获取公司对应的标识
        $model = new SubthematicModel();
        $tags = $model->getSubTagByCase($id);
        if(!empty($tags)){
            $tags = array_column($tags,'tag_id');;
            return $model->getZhuantiListBySubTag($tags,$cs);
        }
    }
}