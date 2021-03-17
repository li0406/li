import CONFIG_INFO from './utils/config.js'
import validate from './utils/validate.js'
let QQMapWX = require('./utils/qqmap-wx-jssdk.min.js');
App({
  onLaunch(options) {
    if(options.query.src){
      this.globalData.sourceMark = options.query.src;
    } else {
      this.globalData.sourceMark = CONFIG_INFO.CHANNEL_FLAG
    }
  },
  globalData: {
    sourceMark:''
  },
  checkFun: validate,
  getLocationCity: function (cityJsonNew, that, index) {
    tt.getStorage({
      key: 'locationCity',
      success: function(res) {},
      fail: function (){
        let qqmapsdk = new QQMapWX({
          key: CONFIG_INFO.qqMapKey
        });
        tt.getLocation({
          type: 'wgs84',
          success: function (res) {
            let latitude = res.latitude
            let longitude = res.longitude
            qqmapsdk.reverseGeocoder({
              location: {
                latitude: latitude,
                longitude: longitude
              },
              success: function (data) {
                let currentProv = data.result.address_component.province;
                let currentCity = data.result.address_component.city;
                let currentArea = data.result.address_component.district;
                tt.setStorage({
                  key: 'locationCity',
                  data: {
                    currentProv: currentProv,
                    currentCity: currentCity,
                    currentArea: currentArea,
                  }
                })
                tt.getStorage({
                  key: 'locationCity',
                  success: function (res) {
                    let cityId;
                    for (let i = 0; i < cityJsonNew.json.length; i++) {
                      if (cityJsonNew.json[i].text.indexOf(res.data.currentProv) != -1) {
                        let cityJson = cityJsonNew.json[i].children;
                        for (let j = 0; j < cityJson.length; j++) {
                          if (res.data.currentCity.indexOf(cityJson[j].text) != -1) {
                            let areaJson = cityJson[j].children;
                            cityId = cityJson[j].id;
                          }
                        }
                      }
                    } 
                    // 绑定城市所有数据
                    that.setData({
                      selectText: res.data.currentProv + " " + res.data.currentCity,
                      selectTextDefault: '',
                      colorCont: [true],
                      cityId: cityId,
                    })     
                  },
                  fail: function (res) {
                    console.log(res)
                  }
                });               
              },
              fail: function (data) {
                console.log(data)
              }
            })
          },
          fail: function (res) {
            console.log('fail' + JSON.stringify(res))
          }
        })
      }
    })
  },
  getMyPosition: function (cityJsonNew, that, index) {
    this.getLocationCity(cityJsonNew, that, index);
    tt.getStorage({
      key: 'locationCity',
      success: function (res) {
        let cityId;
        for (let i = 0; i < cityJsonNew.json.length; i++) {
          if (cityJsonNew.json[i].text.indexOf(res.data.currentProv) != -1) {
            let cityJson = cityJsonNew.json[i].children;
            for (let j = 0; j < cityJson.length; j++) {
              if (res.data.currentCity.indexOf(cityJson[j].text) != -1) {
                let areaJson = cityJson[j].children;
                cityId = cityJson[j].id;
              }
            }
          }
        } 
        // 绑定城市所有数据
        that.setData({
          selectText: res.data.currentProv + " " + res.data.currentCity,
          selectTextDefault: '',
          colorCont: [true],
          cityId: cityId,
        })     
      },
      fail: function (res) {
        console.log(res)
      }
    });
  },
  getApiUrl: function () {
    let apiUrl = CONFIG_INFO.API_DEV_HOST;
    return apiUrl
  }
})
