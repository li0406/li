
import CONFIG_INFO from './utils/config.js'
import validate from './utils/validate.js'
App({
    onLaunch(options) {
        if(options.query.src){
            this.globalData.sourceMark = options.query.src;
        } else {
            this.globalData.sourceMark = CONFIG_INFO.CHANNEL_FLAG
        }
        this.checkMobilePhone()
    },
    onShow() {},
    onHide() {},
    config:CONFIG_INFO,
    checkFun: validate,
    globalData: {
        sourceMark:'',
        isIphoneX:false,
        commentParam:{}
    },
    getMyPosition(fn) {
        let that = this
        swan.getLocation({
            success:function(res){
               fn(res.city)
            },
            fail:function(res){
               fn('苏州')
            }
        })
    },
    // 设置缓存
    setStorage:function(obj){
        swan.setStorage({
            key: obj.key,
            data: {
                data:obj.data,
                startTime:new Date().getTime()
            }
        });
    },
    // 取出缓存
    getStorage:function(obj){
        //  如果是iOS环境，不走缓存
        if(this.getSysInfo()){
            obj.success({
                code:0
            })
            return
        }
        swan.getStorage({
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
                    swan.removeStorage({
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
    getSysInfo(){
        try {
            const result = swan.getSystemInfoSync();
            return result.system.indexOf('iOS') === 0 && result.platform !== "devtools"
        } catch (e) {
            return false
        }
    },
    checkMobilePhone:function(){
        var self = this;
        swan.getSystemInfo({
            success: function (e) {
            var a = e.model;
            if (-1 != a.search("iPhone X") || -1 != a.search("iPhone XS") || -1 != a.search("iPhone 11")){//找到
                self.globalData.isIphoneX = true
            }else{
                self.globalData.isIphoneX = false
            }
            }
        })
    }
});
