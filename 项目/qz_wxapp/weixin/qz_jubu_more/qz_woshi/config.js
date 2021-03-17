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
        appid: 'wxfe10d910cd6a49bd', //① 修改appid
        xcxtitle: '卧室',
        jubuGonglueType: 'woshi',
        jubuxgtType: '5',
        jubusyxgtpx: 'b,a,i,h,c,t,e',
        sourceMark: 'xcx-all'
    }
};
//console.log(config);

module.exports = config;