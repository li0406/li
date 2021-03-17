import CONFIG_INFO from './utils/config.js'
let QQMapWX = require('./utils/qqmap-wx-jssdk.min.js');
App({
  onLaunch: function (options) {
    if(options.query.src){
      this.globalData.sourceMark = options.query.src
    }
  },
  globalData:{
    sourceMark:CONFIG_INFO.CHANNEL_FLAG,
    compayName:'铜陵市东易日盛装饰工程有限责任公司',
    companyId:559497,
    aource:'',
    bm:'tongl'
  },
  getMyPosition(fn) {
    let that = this
    tt.getLocation({
        success:function(res){
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
    //  如果是iOS环境，不走缓存
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
})
