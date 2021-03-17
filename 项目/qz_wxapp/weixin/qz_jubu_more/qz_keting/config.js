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
        appid: 'wx66f4dcf42ab8ebc4', //① 修改appid
        xcxtitle: '客厅',
        jubuGonglueType: 'keting',
        jubuxgtType: '4',
        jubusyxgtpx: 'a,b,i,h,c,t,e',
        sourceMark: 'xcx-all'
    }
};
//console.log(config);

module.exports = config;