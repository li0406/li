// 配置项变量采用大写字母加下划线：A_B

const CONFIG_INFO = {
    API_HOST: 'https://mpapi.qizuang.com', // 老接口host
    API_DEV_HOST: 'https://mpapi.qizuang.com', //新接口
    API_TS: 'https://192.168.8.109:3000/mock/48', //调试接口
    STATIC_HOST: 'https://staticqn.qizuang.com', // 静态资源host
    TEL_400: '400-6969-336', // 400 电话
    REQUEST_TIMEOUT: 5000, // 网路请求超时时间
    CHANNEL_FLAG: 'zmtb-bjh-3-1', //渠道标识
    CACH_TIME:3600*1000 //本地缓存时间
}
module.exports = CONFIG_INFO
