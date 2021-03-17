<?php

namespace app\http\middleware;

use think\Exception;
use Qizuang\JWTAuth\Src\Facade\Auth;
use Qizuang\JWTAuth\Src\JWTAuthException;

use app\index\model\logic\AdminuserLogic;
use app\common\enum\BaseStatusCodeEnum;
use app\index\enum\AdminuserCodeEnum;

class AdminAuth
{
	public function handle($request, \Closure $next) {
		try {
			$token = $request->header("token");
			if (empty($token)) {
				throw new Exception(BaseStatusCodeEnum::CODE_3000001, 3000002);
			}

			$res = Auth::check($token);
			if ($res["data"]["token_type"] != 2) {
				throw new Exception(BaseStatusCodeEnum::CODE_3000002, 3000002);
			}

			$role_id = $res["data"]["role_id"];
			$res["data"]["is_admin"] = $role_id == AdminuserCodeEnum::RBAC_ROLE_ADMIN;
			if ($res["data"]["is_admin"] == false) {
				$roleDeptInfo = AdminuserLogic::getRoleDepartmentInfo($role_id);
			}

			$res["data"]["dept_id"] = $roleDeptInfo["dept1_id"] ?? 0;
			$res["data"]["dept_top_id"] = $roleDeptInfo["dept_top_id"] ?? 0;
			$res["data"]["dept_top_name"] = $roleDeptInfo["dept_top_name"] ?? "";

			$request->user = $res["data"];
		} catch (JWTAuthException $e) {
			//token暂时失效，或者永久失效，token被注销，第一阶段都视为token过期
			return json(sys_response(3000002, BaseStatusCodeEnum::CODE_3000002));
		} catch (Exception $e) {
			return json(sys_response($e->getCode(), $e->getMessage()));
		}

		return $next($request);
	}
}
