<?php

namespace app\http\middleware;

use app\common\enum\StatusCodeEnum;
use Qizuang\passport\src\JwtToken;
use think\Exception;

/**
 * @description:未登录检测中间件
 */
class UcenterAuthRedirect
{
    public function handle($request, \Closure $next)
    {
        try {
            $token = $request->cookie(config('cookie_name_u_center'), '');
            if (!empty($token)) {
                $ret = JwtToken::check($token);
                return redirect(config('SPACE_HOST'));
            }
            return $next($request);

        } catch (\Exception $e) {
            cookie(config('cookie_name_u_center'), null);
            return redirect(config('ACCOUNT_HOST') . '/login');
        }
    }
}
