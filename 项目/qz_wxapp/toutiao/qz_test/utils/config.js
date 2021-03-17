// 配置项变量采用大写字母加下划线：A_B

const CONFIG_INFO = {
    API_HOST: 'https://appapi.qizuang.com', // 老接口host
    API_DEV_HOST: 'https://mpapi.qizuang.com', //新接口
    STATIC_HOST: 'https://staticqn.qizuang.com', // 静态资源host
    TEL_400: '400-6969-336', // 400 电话
    REQUEST_TIMEOUT: 5000, // 网路请求超时时间
    CHANNEL_FLAG: 'ttxcx-cs', //渠道标识
    CACH_TIME:3600*1000, //本地缓存时间
    qqMapKey: 'KGWBZ-HKQHP-QVGDL-VETAU-TWGP7-QBFOL',
}
module.exports = CONFIG_INFO
