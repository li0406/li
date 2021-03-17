/**
 * 小程序配置文件
 */

// API接口
var host_api = 'https://appapi.qizuang.com';
var host_zxs_api = 'https://api.qizuang.com';
var host_img = 'https://staticqn.qizuang.com';
var config = {
    service: {
        host_api: host_api,
        host_zxs_api: host_zxs_api,
        host_img: host_img,
        appid: 'wxf5339337d1b69b21', //① 修改appid
        xcxtitle: '齐装网',
        jubuGonglueType: '',
        jubuxgtType: '8',
        jubusyxgtpx: 'i,a,b,h,c,t,e',
        bdMapKey: 'Y0glUX95DIaKDjqIEBGqC8tPb4c63SNs',
        qqMapKey: 'KGWBZ-HKQHP-QVGDL-VETAU-TWGP7-QBFOL',
        sourceMark: 'xcx-qzw',
        secret: '2851a2c4cbd403f0c4b94d93741ae17a',
        'App-From': '678269d281118caeb4e0305396e540d7',//装修家居小程序：678269d281118caeb4e0305396e540d7
        'Addr': 'APPLET_ZXJJ', //平台的缩写（装修家居小程序：APPLET_ZXJJ）
        sr_token: 'bi3d5b7f49a84f420f' //腾讯有数 token
    }
};
module.exports = config;