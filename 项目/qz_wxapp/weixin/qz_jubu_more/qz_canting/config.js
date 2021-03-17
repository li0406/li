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
        appid: 'wxa79f6cf0326566a1', //① 修改appid
        xcxtitle: '餐厅',
        jubuGonglueType: 'canting',
        jubuxgtType: '6',
        jubusyxgtpx: 'g,a,b,i,h,c,t',
        sourceMark: 'xcx-all'
    }
};
//console.log(config);

module.exports = config;