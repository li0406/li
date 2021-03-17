<?php
namespace Common\Controller;
use Think\Controller;

class PoplayerController extends Controller
{
    public function _initialize(){

    }

    public function pop()
    {
        $type = I("get.type");
        $bm = explode(".", $_SERVER['HTTP_HOST']);
        $bm = $bm[0];
        $tmp = [];
        switch ($type) {
            //
            case 'designer_order':
                // $user = D('Common/User')->getDesingerInfo(I("get.id"),I("get.cs"));
                $user = I("get.");
                $user['logo'] = $user['src'];
                $this->assign('user',$user);
                $tmp = $this->fetch("Common@PopupWindow/designer_order");
                break;
            case 'find-sj':
                $info = I('get.');
                $this->assign('info',$info);
                $tmp = $this->fetch("Common@PopupWindow/find-sj");
                break;
            case 'thankapply':
                $yusuan = D('Common/Yusuan')->getAllYusuan();
                $this->assign('yusuan',$yusuan);
                $tmp = $this->fetch("Common@PopupWindow/thankapply");
                break;
            case 'yuyuebox':
                $tmp = $this->fetch("Common@PopupWindow/yuyuebox");
                break;
            case 'yuyuesuccess':
                $tmp = $this->fetch("Common@PopupWindow/yuyuesuccess");
                break;
            case 'yysuccess':
                $tmp = $this->fetch("Common@PopupWindow/yysuccess");
                break;
            case 'sqsuccess':
                $tmp = $this->fetch("Common@PopupWindow/sqsuccess");
                break;
            case 'luxury_success':
                $tmp = $this->fetch("Common@PopupWindow/luxury_success");
                break;
            case 'first_order_fen':
                $tmpPath = "Common@PopupWindow/".$type;
                //装修贷弹层
                if (in_array($bm,array("sz"))) {
                    $tmpPath = "Common@PopupWindow/zxdk";
                }
                $tmp = $this->fetch($tmpPath);
                break;
            case "smzxdk" :
                //装修贷弹层
                if (in_array($bm,array("sz"))) {
                    $tmpPath = "Common@PopupWindow/smzxdk";
                    $tmp = $this->fetch($tmpPath);
                }
                break;
            default:
                $tmpPath = "Common@PopupWindow/".$type;
                $tmp = $this->fetch($tmpPath);
            break;
        }
        return ['status' => 0, 'tmp' => $tmp];
    }
}