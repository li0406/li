<?php
/**
 * Created by PhpStorm.
 * author: mcj
 * Date: 2018/8/22
 * Time: 13:16
 * 未量房订单/二次回访订单
 */
namespace Common\Model\Logic;


use Think\Exception;

class CompanyLiangFangLogicModel
{


    /**
     * 获取二次回访记录条数
     * author: mcj
     * @param $company_id
     * @param $search
     * @param $isread
     * @return mixed
     */
    public function getTwiceBackCount($company_id,$search,$isread){
        $map = $this->_getTwiceBackMap($company_id,$search,$isread);
        return D("Common/Db/CompanyLiangFang")->getTwiceBackCount($map);
    }

    /**
     * 获取二次回访记录
     * author: mcj
     * @param $company_id
     * @param $search
     * @param $isread
     * @param int $p
     * @param int $p_size
     * @return int
     */
    public function getTwiceBack($company_id,$search,$isread,$p=1,$p_size=20){
        $map = $this->_getTwiceBackMap($company_id,$search,$isread);
        $skip = ($p-1)*$p_size;
        return D("Common/Db/CompanyLiangFang")->getTwiceBack($map,$skip,$p_size);
    }

    /**
     * 根据条件设置二次回访查询条件数组
     * author: mcj
     * @param $company_id
     * @param $search
     * @param $isread
     * @return array
     */
    protected  function _getTwiceBackMap($company_id,$search,$isread){
        $map = array(
            't.company_id'=>array('EQ',$company_id),
            'b.`on`'=>array('EQ',4)
        );
        if(!empty($search)){
            $map['_complex'] = array(
                'b.xiaoqu'=>array('LIKE',"%$search%"),
                'b.tel'=>array('EQ',$search),
                'b.id'=>array('LIKE',"%$search%"),
                '_logic'=>'OR'
            );
        }
        if($isread !== "" && $isread !== null){
            $map["t.isread"] = array("EQ",$isread);
        }
        return $map;
    }

}