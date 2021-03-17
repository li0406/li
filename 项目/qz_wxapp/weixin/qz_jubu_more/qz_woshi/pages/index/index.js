//index.js
//获取应用实例
const app = getApp()
let apiUrl = app.getApiUrl(),
    xcxTitle = app.gettitle(),
    oImgUrl = app.getImgUrl(),
    syxgtPX = app.getJubusyxgtpx(),
    jubuGonglueType = app.getJubuGonglueType();
function getLocalTime(nS) {
    return new Date(parseInt(nS) * 1000).toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ").replace('/', '-').replace('/', '-').split(' ')[0];
}
function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
    wx.showModal({
        title: title,
        content: content,
        showCancel: false,
        success: function (res) {
            if (res.confirm) {
                confirm();
            }
        }
    });
}
Page({
    data: {
      xcxbiaotie: xcxTitle,
        imgUrl: oImgUrl,
        oImgUrl: oImgUrl,
        syxgtPX: syxgtPX,
        bannerList: [],
        articleList: [],
        indicatorDots: false,
        count :5,
        companyList: [],// 装修公司的条目数据
        loadBool: true,
        isHideCity: true,
        selectText: '',
        selectTextDefault:'请选择城市',
        emptyPhoneValue:'',
        emptyNameValue: '',
        emptyXiaoquValue: '',
        prev: [],
        city: [],
        area: [],
        prevIndex: '0',
        cityIndex: '0',
        areaIndex: '0',
        popHide:true,
        personName:'',
        personNamesj: '',
        telNumbersj: '',
        telNumber:'',
        telNumber2: '',
        xiaoQu2:'',
        src:'',
        isColor:false,
        tanchuang: true,
        tanchuang2: true,
        weisj:'top-hoverd-btn',
        xgtzsj: null, // 首页效果图总数据
        valuecity: [0, 0, 0],
        val:[],
        xgtshujv:null,
        boolArr:[]


    },
    onLoad(options){

        let that = this,
            json = [],
            prevJson = [],
            cityJson = [],
            areaJson = [],
            cityUrl,
            bannerList;

        wx.setNavigationBarTitle({ title: this.data.xcxbiaotie + '装修' })

        wx.request({
          url: apiUrl + '/appletcarousel/company',
          data: { count: 3 },
          header: {
            'content-type': 'application/json'
          },
          success: function (res) {
            let listArr = res.data.list
            for (let i = 0; i < listArr.length; i++) {
                listArr[i].logo = listArr[i].logo.split('http://staticqn.qizuang.com/')[1]
            }
            that.setData({ companyList: listArr });
            wx.setStorage({
              key: 'companyInfo',
              data: res.data
            });
          }
        });

  // 请求首页效果图数据
  
        wx.request({
          url: apiUrl + '/appletcarousel/homemeituurl',
          data:{
            location_wz: syxgtPX,
          },
          header: {
            'content-type': 'application/json'
          },
          success:function(res){
            console.log(res.data)
            let boolArr1 =[];
            for (let i = 0; i < res.data.length;i++){
              if (i == 0) {
                boolArr1[i] = false;
              }else{
                boolArr1[i] = true;
              }
            }
            that.setData({
              xgtzsj: res.data,
              xgtshujv: res.data[0],
              boolArr:boolArr1
            });
          }
        });


        // 获取页面来源src
        if (options.src) {
            that.setData({ src: options.src });
            app.setNewStorage('src', options.src, 86400);
        } else {
            if (app.getNewStorage('src')) {
                that.setData({ src: app.getNewStorage('src') });
            } else {
                that.setData({ src: 'xcx-all' });
            }
        }

        let popState = app.getNewStorage('popState')||'';
        if (popState==="true") {
            this.setData({ popHide: false });
        }else{
            this.setData({ popHide: true });
        }

        app.getUserInfo(function(res){
            wx.setStorage({
                key: 'userId',
                data: res.userId,
            });
        });
        /**
         * 获取首页banner数据
         */
        bannerList = app.getNewStorage('bannerList') || '';
        if (bannerList){
            that.setData({ bannerList: bannerList });
        }else{
            wx.request({
                url: apiUrl+'/appletcarousel/banner',
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    that.setData({bannerList:res.data.bannerList});
                    app.setNewStorage('bannerList', res.data.bannerList, 60)
                }
            });
        }
        /**
         * 获取城市缓存数据
         */
        wx.getStorage({
            key: 'cityJson',
            success: function (res) {
                let cityJsonNew = res.data;
                that.setData({ prev: cityJsonNew.prev, city: cityJsonNew.city, area: cityJsonNew.area });
            },
            // 获取缓存失败
            fail: function () {
                // ajax请求数据并且数据本地缓存存储
                wx.request({
                    url: apiUrl + '/zxjt/getcityurl',
                    header: {
                        'content-type': 'application/json'
                    },
                    success: function (res) {
                        cityUrl = res.data.data.replace("/common/js", "");
                        let cityUrlArr = cityUrl.split(':');
                        let host = cityUrlArr[1].split('.');
                        host[0] = host[0] + 's';
                        cityUrlArr[1] = host.join('.');
                        let cityUrlStr = cityUrlArr[0] + 's:' + cityUrlArr[1] // s:
                        wx.request({
                            url: cityUrlStr, // + 'common/js/rlpca20170721143503.js',
                            header: {
                                'content-type': 'application/json'
                            },
                            success: function (res) {
                                let str = res.data.replace("var rlpca = ", "");
                                json = JSON.parse(str), prevJson = [], cityJson = [], areaJson = [];
                                json.shift();
                                // 删除空省份/城市/区域
                                for (let i = 0; i < json.length; i++) {
                                    json[i].children.shift()
                                    for (var j = 0; j < json[i].children.length; j++) {
                                        json[i].children[j].children.shift()
                                    }
                                };
                                // 筛选省份名称+id
                                for (let i = 0; i < json.length; i++) {
                                    prevJson.push({ id: json[i].id, text: json[i].text });
                                }
                                // 筛选第一省的第一个城市名称+id
                                for (let j = 0; j < json[0].children.length; j++) {
                                    cityJson.push({ id: json[0].children[j].id, text: json[0].children[j].text })
                                }
                                // 筛选第一省/第一个城市/第一个区域名称+id
                                for (let k = 0; k < json[0].children[0].children.length; k++) {
                                    areaJson.push({ id: json[0].children[0].children[k].id, text: json[0].children[0].children[k].text })
                                }
                                that.setData({ prev: prevJson, city: cityJson, area: areaJson });
                                // 设置缓存
                                wx.setStorage({
                                    key: 'cityJson',
                                    data: { prev: prevJson, city: cityJson, area: areaJson, json: json },
                                });
                            }
                        })
                    }
                })
            }
        });


        wx.request({
          url: apiUrl + '/gongluejubu/' + jubuGonglueType,
          data: { count: '3' },
          header: {
            'content-type': 'application/json'
          },
          success: function (res) {
            that.setData({ articleList: res.data, })
            // app.setNewStorage('articleList',res.data,10000)
          }
        });

    },

    /**
     * 点击跳转到装修公司详情页
     */
    toDet(e) {
      let id = e.currentTarget.dataset.id,
        star = e.currentTarget.dataset.star,
        anlicount = e.currentTarget.dataset.anlicount;
      wx.navigateTo({
        url: '../det_company/det_company?id=' + id + '&star=' + star + '&anlicount=' + anlicount,
      });
    },

    select(e){
      let index  = e.currentTarget.dataset.index,
        xgtshujvnow = this.data.xgtshujv,
          boolArr1 = this.data.boolArr;
      for (let i = 0; i < boolArr1.length;i++){
        if(index == i){
          boolArr1[index]=false;
          xgtshujvnow = this.data.xgtzsj[index];
        }else{
          boolArr1[i] = true;
        }
      }
      

      this.setData({ 
        boolArr: boolArr1,
        xgtshujv: xgtshujvnow,
        })
      
    },
   


    Guanbi: function () {
      let that = this;
      that.setData({
        tanchuang: true,
        emptyXiaoquValue:'',
        emptyPhoneValue:'',
      })
    },

    Guanbi2: function () {
      let that = this;
      that.setData({
        tanchuang2: true,
        emptyNameValue: '',
        emptyPhoneValue: '',
      })
    },

    Tanchuang: function () {
      let that = this;
      that.setData({
        tanchuang: false,
      })
    },

    Tanchuang2: function () {
      let that = this;
      that.setData({
        tanchuang2: false,
      })
    },
    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function (res) {

    },
    selectHandle() {
        this.setData({ isHideCity: false });
    },
    /**
     * 城市选择滑动
     */
    bindChange: function (e) {
        let that = this;
        let cityJson = [];
        let areaJson = [];
        let oldVal = that.data.val;
        let val = e.detail.value;
        that.setData({ val:val})
        let prev = val[0];
        let city = val[1];
        let area = val[2];
        wx.getStorage({
            key: 'cityJson',
            success: function (res) {
                let json = res.data.json
                // 滑动省份获取城市
                if (oldVal[0] != prev && oldVal[1] == city && oldVal[2] == area) {
                    city = 0; area = 0;
                    that.setData({ valuecity: [prev,0,0]})
                } else if (oldVal[0] == prev && oldVal[1] != city && oldVal[2] == area) {
                    area = 0;
                    that.setData({ valuecity: [prev, city, 0] })
                } else if (oldVal[0] == prev && oldVal[1] == city && oldVal[2] != area) {

                }
                for (let j = 0; j < json[prev].children.length; j++) {
                    cityJson.push({ id: json[prev].children[j].id, text: json[prev].children[j].text });
                }
                // 滑动城市获取区域
                for (let k = 0; k < json[prev].children[city].children.length; k++) {
                    areaJson.push({ id: json[prev].children[city].children[k].id, text: json[prev].children[city].children[k].text });
                }
                that.setData({ city: cityJson, area: areaJson, prevIndex: prev, cityIndex: city, areaIndex: area });
            }
        });

    },
    closebtn() {
        this.setData({ isHideCity: true });
    },
    /**
     * 城市选择
     */
    okbtn() {
        let that = this;
        let prevInfo = that.data.prev;
        let cityInfo = that.data.city;
        let areaInfo = that.data.area;

        let prevId = prevInfo[that.data.prevIndex].id;
        let cityId = cityInfo[that.data.cityIndex].id;
        let areaId = areaInfo[that.data.areaIndex].id;

        let prevText = prevInfo[that.data.prevIndex].text;
        let cityText = cityInfo[that.data.cityIndex].text;
        let areaText = areaInfo[that.data.areaIndex].text;

        let prevCityAreaId = prevId + ',' + cityId + ',' + areaId;
        let selectText = prevText + ' ' + cityText + ' ' + areaText;
        this.setData({ isHideCity: true, isColor: true, selectText: selectText, prevCityAreaId: prevCityAreaId, areaId: areaId, serverVal: areaText, selectTextDefault:''});
    },
    changeIndicatorDots: function (e) {
        this.setData({
            indicatorDots: !this.data.indicatorDots
        })
    },
    changeAutoplay: function (e) {
        this.setData({
            autoplay: !this.data.autoplay
        })
    },
    intervalChange: function (e) {
        this.setData({
            interval: e.detail.value
        })
    },
    durationChange: function (e) {
        this.setData({
            duration: e.detail.value
      })
    },

    /**
   * 传参跳转到装修效果图页面
   */
    sytzxgt: function (e) {
      let Location = e.currentTarget.dataset.leibieid;
      let Fenge = e.currentTarget.dataset.fgid;
      let Title = e.currentTarget.dataset.title;
      let Fenggetext = e.currentTarget.dataset.fengtext;
      wx.navigateTo({
        url: '../sytiaozxguotu/sytiaozxguotu?feng=' + Fenge + '&loca=' + Location + '&Title=' + Title + '&Fenggetext=' + Fenggetext
      })
    },

    /**
     * 跳转到装修设计页面
     */
    toSheji:function(){
        wx.navigateTo({
          url: '../zhuangxiusj/zhuangxiusj'
        })
    },
    /**
     * 跳转到装修报价页面
     */
    toBaojia:function() {
        wx.navigateTo({
            url: '../zhuangxiubj/zhuangxiubj'
        })
    },
    /**
    * 跳转到装修攻略
    */
    toGonglue:function() {
      wx.switchTab({
        url: '../zxgonglue_sy/zxgonglue_sy'
      })
    },

    /**
  * 跳转到装修公司
  */
    tozxgongshi: function () {
      wx.navigateTo({
        url: '../des_company/des_company'
      })
    },

    /**
* 跳转到装修效果图
*/
    tozxxiaoguot: function () {
      wx.switchTab({
        url: '../xiaoguotu/xiaoguotu?aa'+3,
      })
    },

    toPage:function(e){
      wx.navigateTo({
        url: '../sytiaozxguotu/sytiaozxguotu'
      })
    },
    /**
    * 弹窗消失
    */
    popHide:function(){
        this.setData({popHide:true});
    },
    /**
    * 立即计算跳转到报价页
    */
    toBaojiaPop:function(){
        this.setData({ popHide: true });
        wx.navigateTo({
            url: '../zhuangxiubj/zhuangxiubj'
        })
    },
    getName:function(e){
        this.setData({ personName: e.detail.value });
    },
    getNamesj: function (e) {
      this.setData({ personNamesj: e.detail.value });
    },
    getXiaoqu2: function (e) {
      this.setData({ xiaoQu2: e.detail.value });
    },
    getPhone:function(e) {
        this.setData({ telNumber: e.detail.value });
    },
    getPhonesj: function (e) {
      this.setData({ telNumbersj: e.detail.value });
    },
    getPhone2: function (e) {
      this.setData({ telNumber2: e.detail.value });
    },
    getSheJi:function() {
        let regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
        let re = new RegExp(regu);

        let that = this;
        if (that.data.selectText.length < 1) {
          that.setData({ selectTextDefault: '请选择城市'})
            alertViewWithCancel("提示", "请选择您的所在地区", function () { });
            return;
        } else {
            that.setData({ selectTextDefault: ''})
        }
        if (that.data.personName.length < 1) {
            alertViewWithCancel("提示", "请输入您的称呼", function () {
                that.setData({ boolName: true });
            });
            return;
        } else if (that.data.personName.search(re) == -1) {
          alertViewWithCancel("提示", "请输入正确的姓名，仅限中文和英文", function () {
                that.setData({ boolName: true });
            });
            return;
        }
        if (that.data.telNumber.length < 1) {
            alertViewWithCancel("提示", "请输入手机号", function () { });
            return;
        } else {
            var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
            if (!reg.test(that.data.telNumber)) {
                alertViewWithCancel("提示", "请填写正确的手机号码", function () { });
                return false;
            }
        }
        console.log(that.data.personName, that.data.telNumber, that.data.prevCityAreaId)
        if (that.data.src) {
            wx.request({

                url: apiUrl + '/zxjt/submit_order_v2/?src=' + that.data.src,
                data: {
                    name: that.data.personName,
                    tel: that.data.telNumber,
                    cs: that.data.prevCityAreaId
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                success: function (res) {
                    alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
                }
            });
        } else {
            wx.request({
                url: apiUrl + '/zxjt/submit_order_v2/?src=' + app.globalData.sourceMark,
                data: {
                    name: that.data.personName,
                    tel: that.data.telNumber,
                    cs: that.data.prevCityAreaId
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                success: function (res) {
                    alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
                }
            });
        }

    },

    getSheJi2: function () {
      let regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
      let re = new RegExp(regu);

      let that = this;
      if (that.data.selectText.length < 1) {
        that.setData({ selectTextDefault: '请选择城市' })
        alertViewWithCancel("提示", "请选择您的所在地区", function () { });
        return;
      } else {
        that.setData({ selectTextDefault: '' })
      }
      if (that.data.xiaoQu2.length < 1) {
        alertViewWithCancel("提示", "请输入您的小区名称", function () {
          that.setData({ boolName: true });
        });
        return;
      } else if (that.data.xiaoQu2.search(re) == -1) {
        alertViewWithCancel("提示", "请输入正确的小区名称", function () {
          that.setData({ boolName: true });
        });
        return;
      }
      if (that.data.telNumber2.length < 1) {
        alertViewWithCancel("提示", "请输入手机号", function () { });
        return;
      } else {
        var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
        if (!reg.test(that.data.telNumber2)) {
          alertViewWithCancel("提示", "请填写正确的手机号码", function () { });
          return false;
        }
      }

      if (that.data.src) {
        wx.request({

            url: apiUrl + '/zxjt/submit_order_v2/?src=' + that.data.src,
          data: {
            name: that.data.xiaoQu2,
            tel: that.data.telNumber2,
            cs: that.data.prevCityAreaId
          },
          header: {
            'content-type': 'application/x-www-form-urlencoded'
          },
          method: "POST",
          success: function (res) {
            that.setData({
              tanchuang: true,
              emptyXiaoquValue: '',
              emptyPhoneValue: '',
            })
            alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
          }
        });
      } else {
        wx.request({
            url: apiUrl + '/zxjt/submit_order_v2/?src=' + app.globalData.sourceMark,
          data: {
            name: that.data.xiaoQu2,
            tel: that.data.telNumber2,
            cs: that.data.prevCityAreaId
          },
          header: {
            'content-type': 'application/x-www-form-urlencoded'
          },
          method: "POST",
          success: function (res) {
            that.setData({
              tanchuang: true,
              emptyXiaoquValue: '',
              emptyPhoneValue: '',
            })
            alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });

          }
        });
      }

    },

    getSheJisj: function () {
      let regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
      let re = new RegExp(regu);

      let that = this;
      if (that.data.selectText.length < 1) {
        that.setData({ selectTextDefault: '请选择城市' })
        alertViewWithCancel("提示", "请选择您的所在地区", function () { });
        return;
      } else {
        that.setData({ selectTextDefault: '' })
      }
      if (that.data.personNamesj.length < 1) {
        alertViewWithCancel("提示", "请输入您的称呼", function () {
          that.setData({ boolName: true });
        });
        return;
      } else if (that.data.personNamesj.search(re) == -1) {
        alertViewWithCancel("提示", "请输入正确的姓名，仅限中文和英文", function () {
          that.setData({ boolName: true });
        });
        return;
      }
      if (that.data.telNumbersj.length < 1) {
        alertViewWithCancel("提示", "请输入手机号", function () { });
        return;
      } else {
        var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
        if (!reg.test(that.data.telNumbersj)) {
          alertViewWithCancel("提示", "请填写正确的手机号码", function () { });
          return false;
        }
      }

      if (that.data.src) {
        wx.request({

          url: apiUrl + '/zxjt/submit_order_v2/?src=' + that.data.src,
          data: {
            name: that.data.personNamesj,
            tel: that.data.telNumbersj,
            cs: that.data.prevCityAreaId
          },
          header: {
            'content-type': 'application/x-www-form-urlencoded'
          },
          method: "POST",
          success: function (res) {
            that.setData({
              tanchuang2: true,
              emptyNameValue: '',
              emptyPhoneValue: '',
            })
            alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
          }
        });
      } else {
        wx.request({
          url: apiUrl + '/zxjt/submit_order_v2/?src=' + app.globalData.sourceMark,
          data: {
            name: that.data.personNamesj,
            tel: that.data.telNumbersj,
            cs: that.data.prevCityAreaId
          },
          header: {
            'content-type': 'application/x-www-form-urlencoded'
          },
          method: "POST",
          success: function (res) {
            that.setData({
              tanchuang2: true,
              emptyNameValue: '',
              emptyPhoneValue: '',
            })
            alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });

          }
        });
      }

    },



    toArticle: function (e) {
      let id = e.currentTarget.dataset.id;
      wx.navigateTo({
        url: '../shouyexq/shouyexq?id=' + id
      })
    },


})
