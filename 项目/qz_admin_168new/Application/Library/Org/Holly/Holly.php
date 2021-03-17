<?php
/*
 *  合力亿捷 应用类
 *
 */

namespace Library\Org\Holly;

require_once(dirname(__FILE__) . '/HollySdk.php');

use Library\Org\Holly\HollySdk;
use Home\Model\Db\ThreeTokenModel;

class Holly extends HollySdk
{
    public $Type = 'holly';

    function __construct($option = [])
    {
        // 获取默认配置项
        $option = self::defaultOption($option);

        parent::__construct($option);
    }

    function defaultOption($option)
    {
        if (empty($option['appid'])) {

            // 根据环境给配置
            $APP_ENV = C('APP_ENV');

            if ($APP_ENV == 'prod') {
                // 给默认的配置
                $option['appid']   = OP('holly_appid');
                $option['account'] = OP('holly_account');
                $option['secret']  = OP('holly_secret');
            } else {
                // 给默认的配置
                $option['appid']   = OP('holly_appid_dev');
                $option['account'] = OP('holly_account_dev');
                $option['secret']  = OP('holly_secret_dev');
            }

        }
        return $option;
    }

    // 生成和刷新token
    public function GenToken()
    {
        $ThreeTokenDb = new ThreeTokenModel();

        $TokenCache = $ThreeTokenDb->getToken($this->Appid, $this->Type);
        //dump(M()->getLastSql());

        // 本地没有token生成token
        if (empty($TokenCache['token'])) {
            // 从api获取token
            $TokenResult = $this->getAccessToken();
            $Token       = $TokenResult['api_resp'];

            //{"success":true;"accessToken":"D514D428768D7017002E7B9125EA2479",﻿"invalidTime" : "2019-06-10 19:07:10","message":"更新accessToken成功","hostname":"a1new_02"}
            if (!empty($Token)) {
                // 从api获取到token后保存到本地
                $data                 = [];
                $data['type']         = $this->Type;
                $data['appid']        = $this->Appid;
                $data['token']        = $Token['accessToken'];
                $data['invalid_time'] = $Token['invalidTime'];
                $data['message']      = $Token['message'];
                $data['remarks']      = $Token['hostname'];
                $data['updated_at']   = date('Y-m-d H:i:s');
                $data['created_at']   = $data['updated_at'];
                $ThreeTokenDb->addToken($data);
                // 把token赋值到对象上
                $this->AccessToken = $Token['accessToken'];

                return true;
            }
        } else {
            // 如果本地有token 就处理自动更新 换新token
            $diffTime = (strtotime($TokenCache['invalid_time']) - (60 * 30)); // 过期时间减去30分钟的时间戳, 等于提前30分钟刷新token
            if (time() >= $diffTime) {
                // 当前时间 超过了过期时间 就需要更新token了
                // 从api获取token
                $TokenResult = $this->getAccessToken();
                $Token       = $TokenResult['api_resp'];
                if (!empty($Token)) {
                    // 从api获取到token后保存到本地
                    $data                 = [];
                    $data['type']         = $this->Type;
                    $data['appid']        = $this->Appid;
                    $data['token']        = $Token['accessToken'];
                    $data['invalid_time'] = $Token['invalidTime'];
                    $data['message']      = $Token['message'];
                    $data['remarks']      = $Token['hostname'];
                    $data['updated_at']   = date('Y-m-d H:i:s');
                    $ThreeTokenDb->addToken($data);

                    // 把token赋值到对象上
                    $this->AccessToken = $Token['accessToken'];

                    return true;
                }
            }

            // token没有过期直接赋值
            // 把token赋值到对象上
            $this->AccessToken = $TokenCache['token'];
            return true;
        }


        // token过期前自动更新

        return false;
    }
}
