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
        appid: 'wx7929059f195387d4', //① 修改appid
        xcxtitle: '书房',
        jubuGonglueType: 'shufang',
        jubuxgtType: '10',
        jubusyxgtpx: 'j,a,b,i,h,c,t,',
        sourceMark: 'xcx-all'
    }
};
//console.log(config);

module.exports = config;