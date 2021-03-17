<?php

namespace app\http\middleware;

use app\common\enum\StatusCodeEnum;
use Qizuang\passport\src\JwtToken;
use think\Exception;

/**
 * @description:已登录检测中间件
 */
class UcenterAuthCheck
{
    public function handle($request, \Closure $next)
    {
        try {
            $token = $request->cookie(config('cookie_name_u_center'), '');
            if (empty($token)) {
                throw new Exception(StatusCodeEnum::CODE_3000002, 3000002);
            }

            //检测
            $ret = JwtToken::check($token);

            $res['user_id'] = $ret['data']['uuid'];
            $res['uuid'] = $ret['data']['uuid'];
            $res['token'] = $ret['token'];
            $res['phone'] = $ret['data']['phone'];
            $request->user = $res;
        } catch (\Exception $e) {
            cookie(config('cookie_name_u_center'), null);
            return redirect(config('ACCOUNT_HOST').'/login');
        }
        return $next($request);
    }
}
