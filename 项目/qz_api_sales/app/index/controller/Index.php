<?php
namespace app\index\controller;
use app\common\enum\BaseStatusCodeEnum;
use think\captcha\Captcha;
use think\Controller;
use think\Request;

class Index extends Controller
{
    public function index(Request $request)
    {
        if ($request->get("test") == "msg") {
            $msgService = new \app\common\model\service\MsgService();
            $res = $msgService->sendSystemMsg("201909211009", "4", "?", "0", "测试");
            dump($res);
            exit;
        }

        return ["error_code" => "1000004","error_msg" => BaseStatusCodeEnum::CODE_1000004];
    }


    //图形验证码
    public function verifyImg(Request $request)
    {
        $data = $request->get();
        if (isset($data['ssid'])) {
            $captcha = new Captcha();
            $captcha->length = 4;
            $entry = $captcha->entry($data['ssid']);        //图形验证码存redis的时候key加了前缀来进行分组管理
            return $entry;
        } else {
            return ["error_code" => "4000002","error_msg" => BaseStatusCodeEnum::CODE_4000002];
        }
    }

}
