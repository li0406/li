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

    const CANNOT_DELETE = 400600;      //表示无法删除
    const CANNOT_GETDATA = 400601;     //为获取到必要参数
    const DELETE_FALE = 400602;        //删除数据失败

    const ACCOUNT_CANNOT_NULL = 400603;          //账号不能为空
    const PASSWORD_CANNOT_NULL = 400604;        //密码不能为空
    const REPLAYPWDISWRONG = 400605;            //两次密码不一致
    const ACCOUNT_HAD_INTABLE = 400606;         //账号已存在

    const CANNOT_FIND_DATA = 400505;         //数据不存在


    const  PARAMETER_MISSING = 400700;//请求参数不完整
    const  DATA_HAD_IN_TABLE = 400701;//数据已存在，无法重复添加
    const  DATA_HAD_IN_TABLE_MAX = 400702;//数据存储规则下已达上线，无法重复添加
    const  EXCEL_DATA_ERROR = 400703;   //excel表格数据格式错误

    const  REVIEW_ORDER_ERROR = 400801;   //回访订单参数错误
    const  REVIEW_ORDER_SAVE_ERROR = 500801;   //回访订单保存错误

    const SERVER_ERROR = 500100 ; //服务器错误



}