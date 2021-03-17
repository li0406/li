<?php


namespace Home\Model\Logic;


use Home\Model\Db\QuanWuRecordModel;

class QuanWuRecordLogicModel
{

    public function getquanwuOrderList($data,$pageIndex = 1)
    {
        $model = new QuanWuRecordModel();
        $map = [];
        //城市
        if(isset($data['cs']) && !empty($data['cs'])){
            $map['quanwu.cs'] = array('EQ',$data['cs']);
        }
        //订单查询
        $map2=[];
        if(isset($data['keyword']) && !empty($data['keyword'])){
            $map2['quanwu.name'] = array('EQ',$data['keyword']);
            $map2['quanwu.tel'] = array('EQ',$data['keyword']);
            $map2['_logic'] = 'or';
        }
        if($map2){
            $map['_complex'] = $map2;
        }
        //来源页面
        if(isset($data['source']) && !empty($data['source'])) {
            $map['quanwu.source'] = array('EQ',$data['source']);
        }
        //发单开始   发单结束
        if( (isset($data['start']) && isset($data['end'])) && $data['start'] && $data['end']){      //两个时间都有
            $start = strtotime($data['start']);
            $end = strtotime($data['end'].' 23:59:59');
            $map['quanwu.created_at'] = array('between',array($start,$end));
        }else{
            if(isset($data['start']) && $data['start']){        //只有开始时间
                $start = strtotime($data['start']);
                $map['quanwu.created_at'] = array('EGT',$start);
            }
            if(isset($data['end']) && $data['end']){            //只有结束时间
                $end = strtotime($data['end'].' 23:59:59');
                $map['quanwu.created_at'] = array('ELT',$end);
            }
        }
        //强制数字整数
        $pageIndex = intval($pageIndex) <= 0 ? 1 : intval($pageIndex);
        $pageCount = 10;
        $count = $model->getquanwuorderCount($map);
        $list = [];
        if($count > 0){
            import('Library.Org.Util.Page');
            $p = new \Page($count, $pageCount);
            $show = $p->show();
            $list = $model->getquanwuorderList($map, ($pageIndex - 1) * $pageCount, $pageCount);
            foreach ($list as $key => $val){
                $list[$key]['created_at'] = date('Y-m-d H:i:s',$val['created_at']);
            }
        }
        $return = [];
        $return['list'] = $list;
        $return['page'] = $show ? $show  : '';
        return $return;
    }


    public function getAllquanwuOrderListByData($data)
    {
        $model = new QuanWuRecordModel();
        $map = [];
        //城市
        if(isset($data['cs']) && !empty($data['cs'])){
            $map['quanwu.cs'] = array('EQ',$data['cs']);
        }
        //订单查询
        $map2=[];
        if(isset($data['keyword']) && !empty($data['keyword'])){
            $map2['quanwu.name'] = array('EQ',$data['keyword']);
            $map2['quanwu.tel'] = array('EQ',$data['keyword']);
            $map2['_logic'] = 'or';
        }
        if($map2){
            $map['_complex'] = $map2;
        }
        //来源页面
        if(isset($data['source']) && !empty($data['source'])) {
            $map['quanwu.source'] = array('EQ',$data['source']);
        }
        //发单开始   发单结束
        if( (isset($data['start']) && isset($data['end'])) && $data['start'] && $data['end']){      //两个时间都有
            $start = strtotime($data['start']);
            $end = strtotime($data['end'].' 23:59:59');
            $map['quanwu.created_at'] = array('between',array($start,$end));
        }else{
            if(isset($data['start']) && $data['start']){        //只有开始时间
                $start = strtotime($data['start']);
                $map['quanwu.created_at'] = array('EGT',$start);
            }
            if(isset($data['end']) && $data['end']){            //只有结束时间
                $end = strtotime($data['end'].' 23:59:59');
                $map['quanwu.created_at'] = array('ELT',$end);
            }
        }

        $list = $model->getquanwuorderList($map);
        foreach ($list as $key => $val){
            $list[$key]['created_at'] = date('Y-m-d H:i:s',$val['created_at']);
        }
        return $list;
    }

}