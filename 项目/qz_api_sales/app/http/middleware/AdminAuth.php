<?php

namespace app\http\middleware;

use app\common\enum\BaseStatusCodeEnum;
use Qizuang\JWTAuth\Src\Facade\Auth;
use Qizuang\JWTAuth\Src\JWTAuthException;
use app\common\model\logic\AdminuserLogic;

class AdminAuth
{
	public function handle($request, \Closure $next) {

		$token = $request->header("token");
		if (empty($token)) {
        	return json(sys_response(3000002, BaseStatusCodeEnum::CODE_4000002));
		}

		try {
			$res = Auth::check($token);
			if ($res["data"]["token_type"] != 2) {
				return json(sys_response(3000002, BaseStatusCodeEnum::CODE_3000002));
			}

			// 补充部门信息
			$adminuserLogic = new AdminuserLogic();
			$saler_department = AdminuserLogic::SALER_DEPARTMENT_ID;
			$deptInfo = $adminuserLogic->getRoleDepartments($res["data"]["role_id"]);
			$res["data"]["department_top_id"] = $deptInfo["department_top_id"] ?? 0;
			$res["data"]["department_id"] = $deptInfo["department_id"] ?? 0;
			$res["data"]["departments"] = $deptInfo["departments"] ?? [];
			$res["data"]["super_admin"] = intval($res["data"]["role_id"] == 1); // 是否是超级管理员
			$res["data"]["is_saler"] = intval($res["data"]["department_top_id"] == $saler_department);
			$request->user = $res["data"];

		} catch (JWTAuthException $e) {
			//token暂时失效，或者永久失效，token被注销，第一阶段都视为token过期
			return json(sys_response(3000002, BaseStatusCodeEnum::CODE_3000002));
		}

		return $next($request);
	}
}
