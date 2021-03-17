<?php
/**
 * Created by PhpStorm.
 * User: jsb
 * Date: 2019/11/22
 * Time: 10:36
 */

namespace Home\Model\Logic;

use Common\Enums\ApiConfig;
use Home\Model\Db\MallCouponModel;
use Home\Model\Db\MallExchangeOrderModel;
use Home\Model\Db\MallGiftModel;
use Home\Model\Db\MallGoodsImgModel;
use Home\Model\Db\MallGoodsModel;
use Home\Model\Db\MallGoodsScoreModel;
use Home\Model\Db\ScoreLevelModel;
use Think\Exception;

class MallGoodsLogicModel
{
    protected $goodsModel;
    protected $maxSize;
    public function __construct()
    {
        $this->goodsModel = new MallGoodsModel();
        $this->maxSize = 999999;
    }

    //列表页
    public function getList($get){
        $count =  $this->goodsModel->getGoodsCount($get);
        if($count>0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = $this->goodsModel->getGoodsList($get,$p->firstRow, $p->listRows);
            return ['list' => $list, 'page' => $show];
        }
    }

    //获取指定商品信息
    public function getGoodsInfo($id){
        $info = $this->goodsModel->getGoodsById($id);

        if(!empty($info[0])){
            $info = $info[0];
            $info['img_url'] = $info['img_list'];
            if(!empty( $info['img_url'])){
                $info['img_list'] = explode(',',$info['img_list']);

                $imgs = $qr_preconfig = [];
                $code = time();
                foreach ($info['img_list'] as $v) {
                    $imgs[] = '<img data-id=' . $code . ' class="file-preview-image" src= "http://' . C('QINIU_DOMAIN') . '/' . $v . '" >';
                    $qr_preconfig[] = ['url' => '/advposition/del_delimg', 'extra' => ['id' => $code]];
                }
                $info['imgs'] = json_encode($imgs);
                $info['previewconfig'] = json_encode($qr_preconfig);

            }

            if($info['type'] == 2 && $info['use_type'] == 1){
                $info['type'] = 2; //积分优惠券
            }else if($info['type'] == 2 && $info['use_type'] == 2){
                $info['type'] = 3; //平台优惠券
            }
            $info['start_time'] = empty($info['start_time'])?'':date('Y-m-d',$info['start_time']);
            $info['end_time'] = empty($info['end_time'])?'':date('Y-m-d',$info['end_time']);
//            $info['exchange_num'] = empty($info['exchange_num'])?'':$info['exchange_num'];
//            $info['stock'] = empty($info['stock'])?'':$info['stock'];
            $info['coupon_full'] = $info['coupon_full'] == '0.0'?'':floatval($info['coupon_full']);
            $info['coupon_sub'] = $info['coupon_sub'] == '0.0'?'':floatval($info['coupon_sub']);
            $info['coupon_money'] = $info['coupon_money'] == '0.0'?'':floatval($info['coupon_money']);
            $info['per_num'] = empty($info['per_num'])?'':$info['per_num'];
            $info['gift_price'] = $info['gift_price'] == '0.0'?'':floatval($info['gift_price']);
            return $info;
        }else{
            return false;
        }
    }


    //商品是否存在
    public function getExistGoods($id){
        $map['is_show'] = ['eq',1];
        $map['id'] = ['eq',$id];
        if($this->goodsModel->findGoodsCount($map)>0){
            return true;
        }else{
            return false;
        }
    }

    //删除商品
    public function deleteInfo($id){
        $data['is_show'] = 2;
        return $this->goodsModel->saveInfo($data,$id);
    }

    //上架/下架
    public function changeStatus($id,$status){
        $data['status'] = ($status == 1)?2:1;
        return $this->goodsModel->saveInfo($data,$id);
    }

    //添加
    public function addGoods($data){

        try {
            M()->startTrans();

            $goodData['type'] = $data['type'];
            $goodData['name'] = $data['name'];
            $goodData['status'] = $data['status'];
            $goodData['is_gift'] = 1;
            $goodData['stock'] = $data['stock'];
            $goodData['px'] = $data['px'];
            $goodData['exchange_num'] = isset($data["exchange_num"])?$data["exchange_num"]:'';
            $goodData['is_repeat'] = isset($data["is_repeat"])?$data["is_repeat"]:'';
            $goodData['per_num'] = isset($data["per_num"])?$data["per_num"]:'';
            $goods_id = $this->goodsModel->insertInfo($goodData);
            if(empty($goods_id)){
                throw new Exception('数据添加失败', 1);
            }
            //图片表
            foreach($data['imgs'] as $key=>$val){
                $imgData[$key]['goods_id'] = $goods_id;
                $imgData[$key]['img'] = $val;
                $imgData[$key]['created_at'] = time();
                $imgData[$key]['updated_at'] = time();
            }
            $result = (new MallGoodsImgModel())->insertInfo($imgData);
            if(empty($result)){
                throw new Exception('数据添加失败', 1);
            }
            //商品积分表
            $scoreData['goods_id'] = $goods_id;
            $scoreData['point'] = $data["point"];
            $scoreData['grade'] = $data["grade"];
            $scoreData['know'] = $data["know"];

            $result = (new MallGoodsScoreModel())->insertInfo($scoreData);
            if(empty($result)){
                throw new Exception('数据添加失败', 1);
            }
            if($data['type'] == 1){
                //礼品表
                $giftData['goods_id'] = $goods_id;
                $giftData['price'] = $data["gift_price"];
                $giftData['content'] = isset($data["gift_content"])?$data["gift_content"]:'';
                $result = (new MallGiftModel())->insertInfo($giftData);
                if(empty($result)){
                    throw new Exception('数据添加失败', 1);
                }
            }else{
                //优惠券表
                $couponData['goods_id'] = $goods_id;
                $couponData['type'] = $data["coupon_type"];
                $couponData['start_time'] = strtotime($data["start_time"]);
                $couponData['end_time'] =  strtotime($data["end_time"].' 23:59:59');
                //1:满减券 2:代金券 3:礼品券 4.活动券 5.其他
                switch( $data['coupon_type']){
                    case 1:
                        $couponData['full_money'] = $data["coupon_full"];
                        $couponData['sub_money'] = $data["coupon_sub"];
                        break;
                    case 2:
                        $couponData['money'] = $data["coupon_money"];
                        break;
                    case 3:
                        $couponData['gift'] = $data["coupon_gift"];
                        break;
                }
                $couponData['rule'] = $data["coupon_rule"];
                $result = (new MallCouponModel())->insertInfo($couponData);
                if(empty($result)){
                    throw new Exception('数据添加失败', 1);
                }
            }
            //添加操作日志
            $log = array(
                'remark' => '添加商品【' .$goods_id . '】基本操作',
                'logtype' => 'save/addGoods',
                'action_id' => $goods_id,
                'info' => $data
            );
            D('LogAdmin')->addLog($log);

            M()->commit();
            $code = 0;
            $msg = 'success';
        } catch (Exception $e) {
            M()->rollback();
            $code = 1;
            $msg = '操作失败';
        }
        return ['error_code'=>$code,'error_msg'=>$msg];
    }

    //编辑
    public function editGoods($data){
        try {
            M()->startTrans();
            $goods_id = $data['id'];
            //获取实际已兑换量
            $goodsInfo = $this->goodsModel->getStockInfo($goods_id);
            if( $goodsInfo['real_exchange_num']+$data["exchange_num"] > $data["stock"] ){
                throw new Exception('库存量必须大于等于兑换量之和,请刷新重试', 1);
            }
            //主表
            $goodData['name'] = $data['name'];
            $goodData['status'] = $data['status'];
            $goodData['is_gift'] = 1;
            $goodData['stock'] = $data['stock'];
            $goodData['px'] = $data['px'];
            $goodData['exchange_num'] = isset($data["exchange_num"])?$data["exchange_num"]:'';
            $goodData['is_repeat'] = isset($data["is_repeat"])?$data["is_repeat"]:'';
            $goodData['per_num'] = isset($data["per_num"])?$data["per_num"]:'';
            $result = $this->goodsModel->saveInfo($goodData,$goods_id);
            if(empty($result)){
                throw new Exception('数据编辑失败', 1);
            }
            //图片表
            $imgData = [];
            foreach($data['imgs'] as $key=>$val){
                $imgData[$key]['goods_id'] = $goods_id;
                $imgData[$key]['img'] = $val;
                $imgData[$key]['created_at'] = time();
                $imgData[$key]['updated_at'] = time();
            }
            (new MallGoodsImgModel())->deleteInfo($goods_id);
            $result = (new MallGoodsImgModel())->insertInfo($imgData);
            if(empty($result)){
                throw new Exception('数据编辑失败', 1);
            }
            //商品积分表
            $scoreData['point'] = $data["point"];
            $scoreData['grade'] = $data["grade"];
            $scoreData['know'] = $data["know"];
            $result = (new MallGoodsScoreModel())->saveInfo($scoreData,$goods_id);
            if(empty($result)){
                throw new Exception('数据编辑失败', 1);
            }
            if($data['type'] == 1){
                //礼品表
                $giftData['price'] = $data["gift_price"];
                $giftData['content'] = isset($data["gift_content"])?$data["gift_content"]:'';
                $result = (new MallGiftModel())->saveInfo($giftData,$goods_id);
                if(empty($result)){
                    throw new Exception('数据编辑失败', 1);
                }
            }else{
                //优惠券表
                $couponData['type'] = $data["coupon_type"];
                $couponData['start_time'] = strtotime($data["start_time"]);
                $couponData['end_time'] =  strtotime($data["end_time"].' 23:59:59');
                $couponData['full_money'] = '';
                $couponData['sub_money'] = '';
                $couponData['money'] = '';
                $couponData['gift'] = '';
                //1:满减券 2:代金券 3:礼品券 4.活动券 5.其他
                switch( $data['coupon_type']){
                    case 1:
                        $couponData['full_money'] = $data["coupon_full"];
                        $couponData['sub_money'] = $data["coupon_sub"];
                        break;
                    case 2:
                        $couponData['money'] = $data["coupon_money"];
                        break;
                    case 3:
                        $couponData['gift'] = $data["coupon_gift"];
                        break;
                }
                $couponData['rule'] = $data["coupon_rule"];
                $result = (new MallCouponModel())->saveInfo($couponData,$goods_id);
                if(empty($result)){
                    throw new Exception('数据编辑失败', 1);
                }
            }
            //添加操作日志
            $log = array(
                'remark' => '编辑商品【' .$goods_id . '】基本操作',
                'logtype' => 'save/editGoods',
                'action_id' => $goods_id,
                'info' => $data
            );
            D('LogAdmin')->addLog($log);

            M()->commit();
            $code = 0;
            $msg = 'success';
        } catch (Exception $e) {
            M()->rollback();
            $code = $e->getCode();
            $msg = $e->getMessage();
        }
        return ['error_code' => $code,'error_msg'=>$msg];
    }

    /**
     * 名称是否已存在
     * @param $data
     * @return bool
     */
    public function checkGoodsName($data){
        if(!empty($data['id'])){
            $map['id'] = ['neq',$data['id']];
        }
        $map['name'] = ['eq',$data['name']];
        $map['is_show'] = ['eq',1];
        return $this->goodsModel->findGoodsCount($map);
    }

    /**
     * 检查必填数据
     * @param $data
     */
    public function checkRequire($data){

        if(empty($data['name'])){
            return ['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'礼品名称必填'];
        }

        if(!in_array($data['type'],[1,2])){
            return ['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'礼品类型不合法'];
        }

        if(empty($data['imgs'])){
            return ['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'图片必填'];
        }

        if(empty($data['point'])||$data['point']>$this->maxSize){
            return ['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'消耗积分不合法'];
        }
        if($data['exchange_num']>$data['stock']){
            return ['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'库存不合法'];
        }

        if(empty($data['grade'])){
            return ['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'兑换等级不能为空'];
        }

        if(empty($data['px'])||$data['px']>$this->maxSize){
            return ['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'排序不合法'];
        }

        if(empty($data['know'])){
            return ['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'兑换须知不能为空'];
        }

        if(!in_array($data['status'],[1,2])){
            return ['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'上架状态不合法'];
        }


        if($data['type'] == 1){
            if(empty($data['gift_price'])){
                return ['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'礼品价格不能为空'];
            }
        }else{
            //1:满减券 2:代金券 3:礼品券 4.活动券 5.其他
            switch( $data['coupon_type']){
                case 1:
                    if( empty($data['coupon_full']) || empty($data['coupon_sub']) || $data['coupon_full']<$data['coupon_sub'] ){
                        return ['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'满额必须大于减额'];
                    }
                    break;
                case 2:
                    if( empty($data['coupon_money']) ){
                        return ['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'请输入金额'];
                    }
                    break;
                case 3:
                    if( empty($data['coupon_gift']) ){
                        return ['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'请输入礼品'];
                    }
                    break;
            }

            if(empty($data['coupon_rule'])){
                return ['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'使用规则不能为空'];
            }

            if(empty($data['start_time'])||empty($data['end_time'])){
                return ['error_code'=>ApiConfig::PARAMETER_ILLEGAL,'error_msg'=>'活动时间不能为空'];
            }
        }

        return false;
    }

    public function getLevelInfo(){
        return (new ScoreLevelModel())->getLevelInfo();
    }

    public function getExchangeList($get){
        $exchange_model = new MallExchangeOrderModel();

        if(!empty($get["start_time"])){
            $get['start_time'] = strtotime($get["start_time"]);
        }

        if(!empty($get["end_time"])){
            $get['end_time'] =  strtotime($get["end_time"].' 23:59:59');
        }

        $count =  $exchange_model->getExchangeCount($get);
        if($count>0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = $exchange_model->getExchangeList($get,$p->firstRow, $p->listRows);
//            var_dump($list);
            return ['list' => $list, 'page' => $show];
        }
    }
}