<?php

namespace app\common\enum;

class BaseStatusCodeEnum {

    /************** 0 请求成功 *****************/
    const CODE_0 = "请求成功";

    /************** 1 业务接口 *****************/
    const CODE_1000001 = "调用接口业务错误";
    const CODE_1000002 = "图片上传失败";
	const CODE_1000003 = "apk上传失败";
    const CODE_1000004 = "不存在的路径";

    /************* 3 用户权限 ******************/
    const CODE_3000001 = "缺少token";
    const CODE_3000002 = '用户身份验证失败';
    const CODE_3000003 = "用户无权限";

    /************* 4 数据校验 ******************/
    const CODE_4000001 = "暂无查询数据";
    const CODE_4000002 = "缺少参数拒绝请求";


    /************* 5 服务器问题 ****************/
    const CODE_5000001 = "数据库链接失败";
    const CODE_5000002 = "数据库操作失败";
    const CODE_5000003 = "系统异常";


    // 获取枚举列表
    public static function getList($variate){
        $list = [];
        if (isset(static::$$variate)) {
            foreach (static::$$variate as $key => $value) {
                $list[] = [
                    "id" => $key,
                    "name" => $value
                ];
            }
        }

        return $list;
    }

    // 获取枚举值
    public static function getItem($variate, $code, $default = ''){
        if (isset(static::$$variate)) {
            $module = static::$$variate;
            if (array_key_exists($code, $module)) {
                return $module[$code];
            }
        }

        return $default;
    }
}
