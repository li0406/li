/**
 * 小程序配置文件
 */

// API接口
var host_api = 'https://wxapp.qizuang.com';
var host_img = 'https://staticqns.qizuang.com/';

var config = {
    service: {
        host_api: host_api,
        host_img: host_img,
        appid: '{$config.appid}', //① 修改appid
        xcxtitle: '{$config.xcxtitle}',
        jubuGonglueType: '{$config.jubuGonglueType}',
        jubuxgtType: '{$config.jubuxgtType}',
        jubusyxgtpx: '{$config.jubusyxgtpx}',
        sourceMark: '{$config.sourceMark}'
    }
};
//console.log(config);

module.exports = config;