// pages/zxgonglue_sy/zxgonglue_sy.js
//获取应用实例
const app = getApp()
let apiUrl = app.getApiUrl(),
    oImgUrl = app.getImgUrl(),
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

    /**
     * 页面的初始数据
     */
    data: {
        oImgUrl: oImgUrl,
        articleList:[],
        urlstr:'',
        articleCount:'10',
        userId:'',
        selectText: '',
        selectTextDefault: '请选择城市',
        valuecity: [0, 0, 0],
        val: [],
        prev: [],
        city: [],
        area: [],
        prevIndex: '0',
        cityIndex: '0',
        areaIndex: '0',
        isHideCity: true,
        personName: '',
        telNumber: '',
        tanchuang:true,
        emptyNameValue: '',
        emptyPhoneValue: '',
        isColor:false,
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this,
            userId = '';
        // wx.setNavigationBarTitle({
        //     title: options.urlstrname
        // });
        app.getUserInfo(function(res){that.setData({ userId: res.userId});})

        //console.log(jubuGonglueType);
        wx.request({
            url: apiUrl + '/gongluejubu/' + jubuGonglueType,
            data: { count: '10' },
            header: {
                'content-type': 'application/json'
            },
            success: function (res) {
                console.log(res)
                that.setData({ articleList: res.data,})
                // app.setNewStorage('articleList',res.data,10000)
            }
        });

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
        let val = e.detail.value;
        let prev = val[0];
        let city = val[1];
        let area = val[2];
        let oldVal = that.data.val;
        that.setData({ val: val })
        wx.getStorage({
            key: 'cityJson',
            success: function (res) {
                let json = res.data.json
                // 滑动省份获取城市
                if (oldVal[0] != prev && oldVal[1] == city && oldVal[2] == area) {
                  city = 0; area = 0;
                  that.setData({ valuecity: [prev, 0, 0] })
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
        this.setData({ isHideCity: true, isColor: true, selectText: selectText, prevCityAreaId: prevCityAreaId, areaId: areaId, serverVal: areaText, selectTextDefault: '' });
    },


    getName: function (e) {
        this.setData({ personName: e.detail.value });
    },
    getPhone: function (e) {
        this.setData({ telNumber: e.detail.value });
    },
    getSheJi: function () {
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
        if (that.data.personName.length < 1) {
            alertViewWithCancel("提示", "请输入您的称呼", function () {
                that.setData({ boolName: true });
            });
            return;
        } else if (that.data.personName.search(re) == -1) {
            alertViewWithCancel("提示", "用户名不能为数字", function () {
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
                  that.setData({
                    tanchuang: true,
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
                    name: that.data.personName,
                    tel: that.data.telNumber,
                    cs: that.data.prevCityAreaId
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                success: function (res) {
                  that.setData({
                    tanchuang: true,
                    emptyNameValue: '',
                    emptyPhoneValue: '',
                  })
                    alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
                }
            });
        }

    },

    Guanbi:function(){
      let that=this;
      that.setData({
        tanchuang:true,
        emptyNameValue: '',
        emptyPhoneValue: '',
      })
    },

    Tanchuang:function(){
      let that = this;
      that.setData({
        tanchuang:false,
      })
    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function () {
    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function () {
        console.log(this.data.userId)
    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function () {

    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function () {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function () {

    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function () {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function () {

    },
    /**
     * 上拉加载数据
     */
    lower:function(){
        let that = this,
            count = parseInt(that.data.articleCount);
            count+=10;
        wx.request({
            url: apiUrl + '/gongluejubu/' + jubuGonglueType,
            data: { count: count },
            header: {
                'content-type': 'application/json'
            },
            success: function (res) {
                wx.showLoading({
                    title: '数据加载中',
                });
                setTimeout(function () {
                    wx.hideLoading()
                }, 1200);
                that.setData({ articleList: res.data, articleCount: count});
            }
        });
    },
    /**
     * 跳转到详情页面
     */
    toArticle:function(e){
        let id = e.currentTarget.dataset.id;
        wx.navigateTo({
            url: '../shouyexq/shouyexq?id='+id
        })
    },
    toMark:function(e){
        let id = e.currentTarget.dataset.id,
            that = this;
        if (that.data.userId){
            wx.request({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: that.data.userId,
                    classtype: '10', // 装修攻略
                    classid: id
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "POST",
                success: function (res) {
                    if (res.data.state == 1) {
                        wx.showToast({
                            title: '收藏成功',
                            icon: 'success',
                            duration: 1200
                        });

                        wx.request({
                            url: apiUrl + '/gonglue/' + that.data.urlstr + '?userid=' + that.data.userId,
                            data: { count: 10 },
                            header: {
                                'content-type': 'application/json'
                            },
                            success: function (res) {
                                that.setData({ articleList: res.data })
                            }
                        });
                    }
                }
            });
        }else{
            app.getLoginAgain(function (res) {
                that.setData({ userId: res.userId});
                let userId = res.userId;
                wx.request({
                    url: apiUrl + '/appletcarousel/editcollect',
                    data: {
                        userid: userId,
                        classtype: '10', // 装修攻略
                        classid: id
                    },
                    header: {
                        'content-type': 'application/x-www-form-urlencoded'
                    },
                    method: "POST",
                    success: function (res) {
                        if (res.data.state == 1) {
                            wx.showToast({
                                title: '收藏成功',
                                icon: 'success',
                                duration: 1200
                            });

                            wx.request({
                                url: apiUrl + '/gonglue/' + that.data.urlstr + '?userid=' + userId,
                                data: { count: 10 },
                                header: {
                                    'content-type': 'application/json'
                                },
                                success: function (res) {
                                    wx.showLoading({
                                        title: '页面加载中',
                                    });
                                    setTimeout(function () {
                                        wx.hideLoading()
                                    }, 1200);
                                    that.setData({ articleList: res.data })
                                }
                            });
                        }
                    }
                });
            })
        }

    },
    toSearch: function () {
        wx.navigateTo({
            url: '../zxgonglue_soso/zxgonglue_soso?id=2'
        })
    }
})