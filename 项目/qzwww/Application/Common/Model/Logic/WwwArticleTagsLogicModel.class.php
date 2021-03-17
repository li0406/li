<?php
/**
 * Created by PhpStorm.
 * User: qzjsb
 * Date: 2019/5/4
 * Time: 14:58
 */

namespace Common\Model\Logic;

use Common\Model\WwwArticleTagsModel;

class WwwArticleTagsLogicModel
{
    public function getTags($style,$on=1,$position=1){
        $model = new WwwArticleTagsModel();
        $where['style'] = $style;
        $where['on'] = $on;
        $where['position'] = $position;
        $tags = $model->getData2($where);

        $parent = [];
        $child = [];
        foreach($tags as $k=>$v){
            if($v['p_id'] == 0){
                array_push($parent,$v);
            }else{
                array_push($child,$v);
            }
        }
        $child = array_reverse($child);    //倒序取tag
        foreach($child as $k=>$v){
            foreach ($parent as $key=>$val){
                if($v['p_id'] == $val['id']){    //每个标签组最多取35条
                    if(count($parent[$key]['child']) < 35){
                        $parent[$key]['child'][$k] = $v;
                    }
                }

            }
        }
        $ret = [];
        foreach ($parent as $k=>$v){
            if(isset($v['child']) && !empty($v['child'])){
                array_push($ret,$v);
            }
            if(count($ret) >= 8){
                break;
            }
        }
        return $ret;
    }
}