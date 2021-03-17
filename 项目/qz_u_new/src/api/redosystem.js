import request from '@/utils/request';

export default {
    //  登录-获取登录二维码
    qr(query){
        return request({
            url: `/cp/v1/account/qr`,
            method: 'get',
            params: query,
        });
    },
    //  登录-扫码二维码
    qrlogin(query){
        return request({
            url: `/cp/v1/account/qrlogin`,
            method: 'get',
            params: query,
        });
    },
    //  登录-二维码状态扫描
    qrcheck(query){
        return request({
            url: `/cp/v1/account/qrcheck`,
            method: 'get',
            params: query,
        });
    },
    //  商家注册
    reg(data,step){
        return request({
            url: `/cp/v1/user/reg?step=${step}`,
            method: 'post',
            data,
        });
    },
    //  账号密码登录
    loginpass(data){
        return request({
            url: `/cp/v1/account/login`,
            method: 'post',
            data,
        });
    },
    //  手机验证码登录
    loginphone(data){
        return request({
            url: `/cp/v1/phone/login`,
            method: 'post',
            data,
        });
    },
    //  发送验证码（无token验证）
    send(data){
        return request({
            url: `/cp/v1/msgcode/send`,
            method: 'post',
            data,
        });
    },
    //  忘记密码
    change(data,step){
        return request({
            url: `/cp/v1/pwd/change?step=${step}`,
            method: 'post',
            data,
        });
    },
    //  订单管理-获取订单密码设置信息
    orderpass(query){
        return request({
            url: `/cp/v1/config/orderpass`,
            method: 'get',
            params: query,
        });
    },
    //  订单管理-设置订单密码设置信息
    orderpassconfigup(data){
        return request({
            url: `/cp/v1/config/orderpassconfigup`,
            method: 'post',
            data,
        });
    },
    //  订单管理-获取是否有订单密码
    checkorderpassstatus(query){
        return request({
            url: `/cp/v1/order/checkorderpassstatus`,
            method: 'get',
            params: query,
        });
    },
    //  账号安全检测
    accountsafecheck(query){
        return request({
            url: `/cp/v1/common/accountsafecheck`,
            method: 'get',
            params: query,
        });
    },
    //  城市列表页
    allcitys(query){
        return request({
            url: `/cp/v1/allcitys`,
            method: 'get',
            params: query,
        });
    },
}