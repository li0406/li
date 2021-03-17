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
        appid: 'wx1927d68a26de218f', //① 修改appid
        xcxtitle: '厨房',
        jubuGonglueType: 'chufang',
        jubuxgtType: '7',
        jubusyxgtpx: 'h,a,b,i,c,t,e',
        sourceMark: 'xcx-all'
    }
};
//console.log(config);

module.exports = config;