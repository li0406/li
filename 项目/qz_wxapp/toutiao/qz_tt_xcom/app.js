/**
 * @file app.js
 * @author swan
 */
/* globals swan */
import CONFIG_INFO from './utils/config.js'
import validate from './utils/validate.js'
let QQMapWX = require('./utils/qqmap-wx-jssdk.min.js');
App({
    onLaunch(options) {
        if (options.query.src) {
            this.globalData.sourceMark = options.query.src;
        } else {
            this.globalData.sourceMark = CONFIG_INFO.CHANNEL_FLAG
        }
        // 检测iPhoneX
        this.checkMobilePhone()
    },
    onShow(options) {
    },
    onHide() {
    },
    config: CONFIG_INFO,
    checkFun: validate,
    globalData: {
        sourceMark: '',
        isIphoneX: false,
        cityInfo: {
            cname: "",
            cid: "",
            bm: ""
        }
    },
  getMyPosition(fn) {
    let that = this
    tt.getLocation({
        success:function(res){
            console.log(res,'123')
            if(res.city){
                fn(res.city)
            }else{
               that.iOSGetPosition(res,function(city){
                   fn(city)
               }) 
            }
        },
        fail:function(res){
            fn('苏州')
        }
    })
  },
  iOSGetPosition:function(res,cb){
    let qqmapsdk = new QQMapWX({
        key: CONFIG_INFO.qqMapKey
    });
    qqmapsdk.reverseGeocoder({
        location: {
            latitude: res.latitude,
            longitude: res.longitude
        },
        success:function(data){
            cb(data.result.address_component.city)
        },
        fail:function(data){
            cb("苏州")
        }
    })
  },
  setStorage:function(obj){
        tt.setStorage({
            key: obj.key,
            data: {
                data:obj.data,
                startTime:new Date().getTime()
            }
        });
    },
    // 取出缓存
  getStorage:function(obj){
    tt.getStorage({
        key:obj.key,
        success:function(res){
            let startTime = res.data.startTime
            let nowTime = new Date().getTime()
            let time = CONFIG_INFO.CACH_TIME
            if(time>nowTime-startTime){
                obj.success({
                    code:1,
                    data:res.data
                })
            }else{
                tt.removeStorage({
                    key: 'key',
                    success: function (res) {
                        obj.success({
                            code:0
                        })
                    }
                })
            }
        },
        fail:function(){
            obj.success({
                code:0
            })
        }
    })
  },




    // 判断是否是iOS
    getSysInfo() {
        try {
            const result = tt.getSystemInfoSync();
            return result.system.indexOf('iOS') === 0 && result.platform !== "devtools"
        } catch (e) {
            return false
        }
    },
    checkMobilePhone: function () {
        var self = this;
        tt.getSystemInfo({
            success: function (e) {
                var a = e.model;
                if (-1 != a.search("iPhone X") || -1 != a.search("iPhone XS")) {//找到
                    self.globalData.isIphoneX = true
                } else {
                    self.globalData.isIphoneX = false
                }
            }
        })
    }
});
