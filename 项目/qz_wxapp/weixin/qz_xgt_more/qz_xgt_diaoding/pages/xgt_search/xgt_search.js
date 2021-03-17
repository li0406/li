// pages/searchxgt/searchxgt.js
const app = getApp()
let apiUrl = app.getApiUrl(),
    apid = app.getAPPid(),
    oImgUrl = app.getImgUrl();
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
        shoucpd: true,
        inputValue: "",
        data: null,
        xianspd: true,
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
        tanchuang: true,
        emptyNameValue: '',
        emptyPhoneValue: '',
        userId: null,
        count: 10,
        isColor: false,
        scrollCount:10

    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;

        app.getUserInfo(function (res) {
            that.setData({ userId: res.userId });
        })

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

    scqiehuan: function (e) {
        let that = this,

            id = e.currentTarget.dataset.id,
            index = e.currentTarget.dataset.index;
        if (that.data.userId) {
            wx.request({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: that.data.userId,
                    classtype: '8', // 装修效果图
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
                        let arr = that.data.data;
                        arr[index].is_collect = 1;
                        that.setData({ data: arr });
                    }
                }
            });
        } else {
            app.getLoginAgain(function (e) {
                wx.login({
                    success: function (l) {
                        if (l.code) {
                            wx.request({
                                url: apiUrl + '/login',
                                data: {
                                    appid: apid,
                                    code: l.code,
                                    name: e.nickName,
                                    logo: e.avatarUrl
                                },
                                header: {
                                    'content-type': 'application/x-www-form-urlencoded'
                                },
                                method: "POST",
                                dataType: 'json',
                                success: function (i) {
                                    wx.request({
                                        url: apiUrl + '/appletcarousel/editcollect',
                                        data: {
                                            userid: i.data.data,
                                            classtype: '8', // 装修效果图
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
                                                let arr = that.data.data;
                                                arr[index].is_collect = 1;
                                                that.setData({ data: arr })
                                            }
                                        }
                                    });
                                }
                            });
                        }
                    }
                })
            });
        }
    },

    delscqiehuan: function (e) {
        let that = this,

            id = e.currentTarget.dataset.id,
            index = e.currentTarget.dataset.index;
        if (that.data.userId) {
            wx.request({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: that.data.userId,
                    classtype: '8', // 装修效果图
                    classid: id
                },
                header: {
                    'content-type': 'application/x-www-form-urlencoded'
                },
                method: "GET",
                success: function (res) {
                    if (res.data.state == 1) {
                        wx.showToast({
                            title: '取消收藏',
                            icon: 'success',
                            duration: 1200
                        });
                        let arr = that.data.data;
                        arr[index].is_collect = 0;
                        that.setData({ data: arr });
                    }
                }
            });
        } else {
            app.getLoginAgain(function (e) {
                wx.login({
                    success: function (l) {
                        if (l.code) {
                            wx.request({
                                url: apiUrl + '/login',
                                data: {
                                    appid: apid,
                                    code: l.code,
                                    name: e.nickName,
                                    logo: e.avatarUrl
                                },
                                header: {
                                    'content-type': 'application/x-www-form-urlencoded'
                                },
                                method: "GET",
                                dataType: 'json',
                                success: function (i) {
                                    wx.request({
                                        url: apiUrl + '/appletcarousel/editcollect',
                                        data: {
                                            userid: i.data.data,
                                            classtype: '8', // 装修效果图
                                            classid: id
                                        },
                                        header: {
                                            'content-type': 'application/x-www-form-urlencoded'
                                        },
                                        method: "GET",
                                        success: function (res) {
                                            if (res.data.state == 1) {
                                                wx.showToast({
                                                    title: '取消收藏',
                                                    icon: 'success',
                                                    duration: 1200
                                                });
                                                let arr = that.data.data;
                                                arr[index].is_collect = 0;
                                                that.setData({ data: arr })
                                            }
                                        }
                                    });
                                }
                            });
                        }
                    }
                })
            });
        }
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
            alertViewWithCancel("提示", "请输入姓名", function () {
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
            alertViewWithCancel("提示", "请输入手机号码", function () { });
            return;
        } else {
            var reg = new RegExp("^(13|14|15|17|18)[0-9]{9}$");
            if (!reg.test(that.data.telNumber)) {
                alertViewWithCancel("提示", "请输入正确的手机号码", function () { });
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

    Guanbi: function () {
        let that = this;
        that.setData({
            tanchuang: true,
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

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function () {

    },

    xgtxq: function () {
        wx.navigateBack({
            delta: 1
        })
    },

    toSeji: function () {
        wx.navigateTo({
            url: '../zx_sheji/zx_sheji'
        })
    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function () {

    },


    inputValue: function (e) {
        let that = this;
        that.setData({ inputValue: e.detail.value });
        if (!that.data.inputValue) {

            that.setData({
                xianspd: false,
            })
        } else {

            wx.request({
                url: apiUrl + '/appletcarousel/meitujubu?keyword=' + that.data.inputValue,
                data: {
                    userid: that.data.userId,
                    count: 10,

                },
                header: {
                    'Content-Type': 'application/json'
                },
                success: function (res) {
                    if (res.data.info.list < 1) {
                        that.setData({
                            data: res.data.info.list,
                            xianspd: false,
                        })
                    } else {
                        that.setData({
                            data: res.data.info.list,
                            xianspd: true,
                        })
                    }
                    if (!that.data.inputValue) {

                        that.setData({
                            xianspd: false,
                        })
                    }

                },
                fail: function (res) {

                },
                complete: function (res) { },
            })
        }
    },

    downLoad: function () {
        let that = this,
            scrollCount = that.data.scrollCount;

        wx.showToast({
            title: '加载中...',
            icon: 'loading',
            duration: 2000
        });
        wx.request({
            url: apiUrl + '/appletcarousel/meitujubu?keyword=' + that.data.inputValue,
            data: {
                userid: that.data.userId,
                count: scrollCount + 10,
            },
            header: {
                'Content-Type': 'application/json'
            },
            success: function (res) {
                if (res.data.info.list.length <= scrollCount){
                    wx.showToast({
                        title: '没有更多...',
                        icon: 'success',
                        duration: 2000
                    });
                }else{
                    that.setData({
                        data: res.data.info.list,
                        xianspd: true,
                        scrollCount: scrollCount + 10
                    })
                }
                
            }
        })

    },

    xiaoguotuxq: function (e) {
        let id = e.currentTarget.dataset.id,
            title = e.currentTarget.dataset.title;
        wx.navigateTo({
            url: '../xgt_det_single/xgt_det_single?id=' + id + '&title=' + title
        });
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

    }
})