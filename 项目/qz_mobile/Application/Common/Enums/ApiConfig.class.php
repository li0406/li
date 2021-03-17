<?php
/**
 * 接口状态码定义
 * Created by PhpStorm.
 * User: mcj
 * Date: 2018/6/22
 * Time: 16:07
 */
namespace Common\Enums;

class ApiConfig
{
    const REQUEST_FAILL = 0;//请求失败默认状态
    const REQUEST_SUCCESS = 1;//请求成功
    const  PARAMETER_ILLEGAL = 7;//请求参数不合法
    const  VERIFY_CODE_ERROR = 8;//验证码错误

    /*********** 1 业务接口 **********************/
    const REQUEST_TYPE_ERROR = 1000100;     //错误的请求方式


    /************** 4 数据校验 ******************/
    const LACK_CARDID = 4000100; //缺少优惠券id
    const USER_NO_ORDER = 4000102; //用户没有发布订单
    const LOSS_ORDER_ID = 4000103; //缺少订单ID
    const NO_LIANGFANG_COMPANY = 4000104; //无分配装修公司
    const LOSE_MISS_PARAMETERS = 4000105;   //缺少参数
    const NO_LOGIN_FOR_CARDLIST = 4000106;  //登陆状态有误，请重新登陆
    const ERROR_TO_ADD_MYSQL = 4000107;     //数据库添加失败
    const ERROR_FOR_CARD_OVER = 4000108;    //领券已领取完
    const ERROR_FOR_RECEIVE_AGAIN = 4000109;    //无法重复领取该优惠券


    /**************发单********************/
    const CODE_0 = "请求成功";
    const CODE_9000001 = "订单参数不正确";
    const CODE_9000002 = "当日订单发布超限";
    const CODE_9000003 = "无订单参数";
    const CODE_9001000 = "请选择城市";
    const CODE_9001001 = "未获取到发单标识";

}