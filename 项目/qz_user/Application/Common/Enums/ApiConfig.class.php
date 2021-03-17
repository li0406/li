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
    const  ORDER_STATUS_LOCK = 8;//订单状态被锁定，只能到二次回访中修改订单状态
    const  ORDER_HAS_SIGN = 9;//订单已被签约
    const  ORDER_EXPIRE = 10;//订单时间太久远，不可签约
    const  ORDER_MISS = 11;//订单未找到


    /**************************/
    const ADD_SUCCESS = 201;    //添加到数据库成功
    const EDIT_SUCCESS = 202;   //保存成功


    const ADD_ERROR = 400001;       //添加到数据库失败
    const EDIT_ERROR = 400002;      //保存数据失败
    const REQUEST_NODATA = 400003;  //无请求数据


}