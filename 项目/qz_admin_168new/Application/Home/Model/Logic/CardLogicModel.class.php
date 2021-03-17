<?php

namespace Home\Model\Logic;

use Home\Model\Db\CardModel;
use Home\Model\Db\CompanyModel;

class CardLogicModel
{
    /**
     * 添加/编辑通用礼券
     * @param $data
     * @param $id
     */
    public function addCommon($data,$id){
        $cardDb = new CardModel();
        $lock = 0;
        if(!empty($id)&&isset($id)){
            $old = $cardDb->getCardInfo($id);
            //编辑
            $result = $cardDb->editCommon($data,$id);
            //启用状态修改时才发消息通知
            if($old['enable'] != $data['enable']){
                $lock = 1;
            }
        }else{
            //添加
            $result = $cardDb->addCommon($data);
            //若添加时选择启用才发消息通知
            if($data['enable']  == 1){
                $lock = 1;
            }
        }

        //发送消息通知
        if($lock){
            if($result>0){
                if(empty($data['name'])){
                    $data = $cardDb->getCardInfo($id);
                }
                if($data['enable'] == 1){
                    //启用通知
                    $this->sendMessage($data,5);
                }else{
                    //禁用通知
                    $this->sendMessage($data,6);
                }
            }
        }

        return $result;
    }

    /**
     *根据公司名获取公司id
     * @param $name
     */
    public function searchCom($name){
        $cardDb = new CompanyModel();
        $result =  $cardDb->searchidbyname($name,10);
        return $result;
    }

    /**
     * 添加公司领用通用券信息
     * @param $data
     * @return mixed
     */
    public function addCardCom($cardid,$comid,$dataRecord){
        $cardDb = new CardModel();
        //是否有该公司礼券信息
        $exist = $cardDb->existComCard($cardid,$comid);

        if(empty($exist['id'])){
            //添加公司礼券
            $exist['id'] = $cardDb->addCardCom(['card_id'=>$cardid,'com_id'=>$comid]);
        }
        //添加公司礼券领用
        $dataRecord['card_com_id'] = $exist['id'];
        return $cardDb->addCardComRecord($dataRecord);
    }

    /**
     * 更新公司领用通用券信息
     * @param $data
     * @param $id
     * @return bool
     */
    public function updateCardComRecord($data,$id,$type){
        $data['update_time'] = time();
        $cardDb = new CardModel();
        $result =  $cardDb->updateCardComRecord($data,$id);
        if($result>0){
            //添加消息通知
            if($type == 2){
                $info = $this->getSpecialInfo($id,2);
                if($data['check'] == 2){
                    $type = 2;
                }else{
                    $type = 3;
                }
            }else{
                $info = $this->getCommonInfo($id);
                if($data['check'] == 2){
                    $type = 4;
                }else{
                    $type = 7;
                }
            }
            $this->sendMessage($info,$type,$info['cs'],$info['com_id']);
        }
        return $result;
    }

    /**
     * 专用列表
     * @param $where
     * @return array
     */
    public function special($where){
        $cardDb = new CardModel();
        $count = $cardDb->getSpecialCount($where);
        if($count>0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = $cardDb->getSpecialList($where,$p->firstRow, $p->listRows);
            return ['list' => $list, 'page' => $show];
        }
    }

    public function getSpecialInfo($id,$type){
        $cardDb = new CardModel();
        return $cardDb->getSpecialInfo($id,$type);
    }

    /**
     * 获取通用券信息
     * @param $id
     * @return mixed
     */
    public function getCommonInfo($id){
        $cardDb = new CardModel();
        $result =  $cardDb->getCommonInfo($id);
        return $result;
    }

    /**
     * 通用列表
     * @param $where
     * @return array
     */
    public function common($where){
        $cardDb = new CardModel();
        $count = $cardDb->getCommonCount($where);
        if($count>0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = $cardDb->getCommonList($where,$p->firstRow, $p->listRows);
            return ['list' => $list, 'page' => $show];
        }
    }

    /**
     * 商家领用查询
     * @param $where
     * @return array
     */
    public function seller($where){
        $cardDb = new CardModel();
        $count = $cardDb->getSellerCount($where);
        if($count>0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = $cardDb->getSellerList($where,$p->firstRow, $p->listRows);
            return ['list' => $list, 'page' => $show];
        }
    }

    /**
     * 业主领用礼券
     * @param $where
     */
    public function user($where){
        $cardDb = new CardModel();
        $count = $cardDb->getUserCount($where);
        if($count>0){
            import('Library.Org.Util.Page');
            $p = new \Page($count,20);
            $show = $p->show();
            $list = $cardDb->getUserList($where,$p->firstRow, $p->listRows);
            foreach($list as $key=>$vo){
                if(strlen($vo['tel']) > 4){
                    $list[$key]['tel'] =  substr_cut($vo['tel'],3,3);
                }
            }
            return ['list' => $list, 'page' => $show];
        }
    }

    /**
     * 获取通用礼券信息
     * @param $id
     */
    public function getCardInfo($id){
        $cardDb = new CardModel();
        return $cardDb->getCardInfo($id);
    }

    /**
     * 公司是否是真会员
     * @param $comid
     */
    public function getCompanyInfo($comid){
        $cardDb = new CompanyModel();
        $company = $cardDb->searchid($comid);
        if(!empty($company['id'])&&isset($company['id'])){
            return true;
        }else{
            return false;;
        }
    }

    /**
     * 消息通知
     * @param $info 消息数组
     * @param $type 模板类型
     * @param string $cs 城市
     * @param int $cityid 公司号
     * @return mixed
     */
    public function sendMessage($info,$type,$cs='',$comid=0)
    {
        if(!empty($info['rule'])){
           $info['rule'] = preg_replace('/\n/',"<br/>" ,$info['rule']);
           $info['rule'] = '<div style="float:left;">'.$info['rule'].'</div>';
        }
        $html = '';
        switch ($type) {
            case 1:
                $title = '用户领取';
                $html .= '礼券名称: '.$info['name'].'<br><br>';
                $html .= '礼券编号: '.$info['card_number'].'<br><br>';
                $html .= '业主手机号: '.$info['tel'].'<br><br>';
                $html .= '小区名称: '.$info['xiaoqu'].'<br><br>';
                $html .= '领取时间: '.date('Y-m-d H:i:s',$info['add_time']);
                break;
            case 2:
                $title = '礼券审核结果通知'; //专用审核通过
                $html .= '礼券名称: '.$info['name'].'<br><br>';
                if($info['active_type'] == 2){
                    $html .= '优惠活动: 满'.intval($info['money3']).'送'.$info['gift'].'<br><br>';
                }else{
                    if($info['money1'] == 0){
                        $html .= '优惠活动: 立减'.intval($info['money2']).'<br><br>';
                    }else{
                        $html .= '优惠活动: '.intval($info['money1']).'减'.intval($info['money2']).'<br><br>';
                    }
                }

//                $html .= '使用条件: '.$info['service'].'<br><br>';
                $html .= '<div style="float:left;">使用规则:</div> '.$info['rule'].' <div style="clear: both"></div><br><br>';
                $html .= '有效时间: '.date('Y-m-d H:i:s',$info['start']).'至'.date('Y-m-d H:i:s',$info['end']).'<br><br>';
                $html .= '<span style="color:#980001">审核结果: 通过</span>';
                break;
            case 3:
                $title = '礼券审核结果通知'; //专用审核不通过
                $html .= '礼券名称: '.$info['name'].'<br><br>';
                if($info['active_type'] == 2){
                    $html .= '优惠活动: 满'.intval($info['money3']).'送'.$info['gift'].'<br><br>';
                }else{
                    if($info['money1'] == 0){
                        $html .= '优惠活动: 立减'.intval($info['money2']).'<br><br>';
                    }else{
                        $html .= '优惠活动: '.intval($info['money1']).'减'.intval($info['money2']).'<br><br>';
                    }
                }
                $html .= '使用条件: '.$info['service'].'<br><br>';
                $html .= '<div style="float:left;">使用规则:</div> '.$info['rule'].' <div style="clear: both"></div><br><br>';
                $html .= '<span style="color:#980001">审核结果: 不通过</span><br><br>';
                $html .= '有效时间: '.date('Y-m-d H:i:s',$info['start']).'至'.date('Y-m-d H:i:s',$info['end']).'<br><br>';
                $html .= '<span style="color:#980001">审核意见:'.$info['check_reason'].' </span><br><br>';
//                $html .= '<span style="color:#980001">'.$info['check_reason'].'</span><br><br>';
                break;
            case 4:
                $title = '通用礼券申请结果通知'; //通过
                $html .= '礼券名称: '.$info['name'].'<br><br>';
                if($info['money1'] == 0){
                    $html .= '立减金额: 立减'.intval($info['money2']).'<br><br>';
                }else{
                    $html .= '立减金额: '.intval($info['money1']).'减'.intval($info['money2']).'<br><br>';
                }
//                $html .= '使用条件: '.$info['service'].'<br><br>';
                $html .= '<div style="float:left;">使用规则:</div> '.$info['rule'].' <div style="clear: both"></div><br><br>';
                $html .= '有效时间: '.date('Y-m-d H:i:s',$info['start']).'至'.date('Y-m-d H:i:s',$info['end']).'<br><br>';
                $html .= '<span style="font-size:20px;"><a href="/oneselfevent/common/">该礼券贵公司已领取'.$info['amount'].'份,请在优惠活动中查看>></a></span>';
                break;
            case 7:
                $title = '通用礼券申请结果通知'; //不通过
                $html .= '礼券名称: '.$info['name'].'<br><br>';
                if($info['money1'] == 0){
                    $html .= '立减金额: 立减'.intval($info['money2']).'<br><br>';
                }else{
                    $html .= '立减金额: '.intval($info['money1']).'减'.intval($info['money2']).'<br><br>';
                }
//                $html .= '使用条件: '.$info['service'].'<br><br>';
                $html .= '<div style="float:left;">使用规则:</div> '.$info['rule'].' <div style="clear: both"></div><br><br>';
                $html .= '有效时间: '.date('Y-m-d H:i:s',$info['start']).'至'.date('Y-m-d H:i:s',$info['end']).'<br><br>';
                $html .= '<span style="color:#980001">审核结果: 不通过</span><br><br>';
                $html .= '<span style="color:#980001">审核意见:'.$info['check_reason'].' </span><br><br>';
//                $html .= '<span style="color:#980001">'.$info['check_reason'].'</span><br><br>';
                break;
            case 5:
                $title = '通用礼券上新通知';
                $html .= '礼券名称: '.$info['name'].'<br><br>';
                if($info['money1'] == 0){
                    $html .= '立减金额: 立减'.intval($info['money2']).'<br><br>';
                }else{
                    $html .= '立减金额: '.intval($info['money1']).'减'.intval($info['money2']).'<br><br>';
                }
//                $html .= '使用条件: '.$info['service'].'<br><br>';
                $html .= '<div style="float:left;">使用规则:</div> '.$info['rule'].' <div style="clear: both"></div><br><br>';
                $html .= '<span style="font-size:20px;"><a href="/oneselfevent/common">礼券上新,请在优惠活动中查看>></a></span>';
                break;
            case 6:
                $title = '通用礼券禁用通知';
                $html .= '礼券名称: '.$info['name'].'<br><br>';
                if($info['money1'] == 0){
                    $html .= '立减金额: 立减'.intval($info['money2']).'<br><br>';
                }else{
                    $html .= '立减金额: '.intval($info['money1']).'减'.intval($info['money2']).'<br><br>';
                }
//                $html .= '使用条件: '.$info['service'].'<br><br>';
                $html .= '<div style="float:left;">使用规则:</div> '.$info['rule'].' <div style="clear: both"></div><br><br>';
                $html .= '<span style="font-size:15px;color:#980001">禁用时间: '.date('Y-m-d H:i:s',$info['disable_time']).'</span><br><br>';
                $html .= '<span style="font-size:20px;">该礼券即将禁止使用,请贵公司注意禁用时间</span>';
                break;
            default:
                $this->ajaxReturn(['status' => ApiConfig::PARAMETER_ILLEGAL, 'error_msg' => '操作参数不合法!']);
                break;
        }

        $notice['title'] = $title;
        $notice['type'] = '2';
        $notice['cs'] = $cs;
        $notice['text'] = htmlspecialchars_decode($html);
        $notice['userid'] = $_SESSION['uc_userinfo']['id'];
        $notice['username'] = '系统';
        $notice['time'] = time();
        $noticle_id = M("user_system_notice")->add($notice);
        if(($type == 5) || ($type == 6) ){
            //准生产上线先关闭-start
            $companydb = new CompanyModel();
            $companyids = $companydb->getCompany();
            foreach($companyids as $key=>$val){
                $companyData[$key]['uid'] = $val['id'];
                $companyData[$key]['noticle_id'] = $noticle_id;
            }
           return D("UserNoticeRelated")->addAllRelated($companyData);
            //准生产上线先关闭-end
        }else{
           return M("user_notice_related")->add(array('noticle_id'=>$noticle_id,'uid'=>$comid));
        }

    }




}