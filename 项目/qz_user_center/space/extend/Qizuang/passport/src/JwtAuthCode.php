<?php


namespace Qizuang\passport\src;


class JwtAuthCode
{
    const TOKEN_EXPIRE = 20001;//token过期，需要刷新
    const TOKEN_EXPIRE_LONG = 20002;//token过期，过期时间超过上限
    const TOKEN_LOGOUT = 20003;//token已经被注销
    const TOKEN_REFRUSH_SUCCES = 20004;//token刷新成功
    const TOKEN_REFRUSH_FALSE = 20005;//token刷新失败
    const INVALID_TOKEN = 40001;//token格式不正确不合法，异常的token
    const INVALID_HEADER_ENCODING = 40002;//无效的头编码
    const INVALID_CLAIMS_ENCODING = 40003;//无效的声明编码
    const INVALID_SIGNATURE_ENCODING = 40004;//无效的声明编码
    const INVALID_PARAMS = 40005;   //无效的参数
    const ENCRYPT_EORROR = 50001;//jwt加密算法运算时异常
    const JWT_SECRET_MISS = 50002;//jwt加密秘钥值未设置
}