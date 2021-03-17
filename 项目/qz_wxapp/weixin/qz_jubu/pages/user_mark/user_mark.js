// pages/user_mark/user_mark.js
let app = getApp();
let apiUrl = app.getApiUrl(),
    oImgUrl = app.getImgUrl();
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
        isHide: [true, false, false],
        isEmpty:[true,true,true],
        xgtArr:[],
        zxgsArr:[],
        zxgl:[],
        oImgUrl: oImgUrl,
        loginUserId: '',
        isHideCity: true,
        selectTextDefault: '选择城市',
        selectText: '',
        prev: [],
        city: [],
        area: [],
        prevIndex: '0',
        cityIndex: '0',
        areaIndex: '0',
        isColor: false,
        shejiPopHide: true,
        personNamesj: '',
        telNumbersj: '',
        fenlei:'0',
        countXgt:10,
        countZxgl:10,
        countZxgs:10,
        valuecity:[],
        val:[],
        emptyName:'',
        emptyPhone:''
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        let that = this;
        that.setData({ userId: options.userid})
        app.getUserInfo(function (res) {
            if (options.userid) {
                // 获取装修效果图
                wx.request({
                    url: apiUrl + '/appletcarousel/collect',
                    data: {
                        count: '10',
                        userid: options.userid,
                        classtype: '8'//效果图
                    },
                    header: {
                        'content-type': 'application/json'
                    },
                    success: function (res) {
                        console.log(res)
                        let empty = that.data.isEmpty;

                        if (res.data.length > 0) {
                            empty[0] = true;
                            that.setData({ xgtArr: res.data, isEmpty: empty })
                        } else {
                            empty[0] = false;
                            that.setData({ xgtArr: res.data, isEmpty: empty })
                        }
                    }
                });
                // 获取装修公司
                wx.request({
                    url: apiUrl + '/appletcarousel/collect',
                    data: {
                        count: '10',
                        userid: options.userid,
                        classtype: '9'//装修公司
                    },
                    header: {
                        'content-type': 'application/json'
                    },
                    success: function (res) {
                        let empty = that.data.isEmpty;
                        if (res.data.length > 0) {
                            empty[1] = true;
                            that.setData({ zxgsArr: res.data, isEmpty: empty })
                        } else {
                            empty[1] = false;
                            that.setData({ zxgsArr: res.data, isEmpty: empty })
                        }
                    }
                });
                // 获取装修攻略
                wx.request({
                    url: apiUrl + '/appletcarousel/collect',
                    data: {
                        count: '10',
                        userid: options.userid,
                        classtype: '10'//装修攻略
                    },
                    header: {
                        'content-type': 'application/json'
                    },
                    success: function (res) {
                        let empty = that.data.isEmpty;
                        if (res.data.length > 0) {
                            empty[2] = true;
                            that.setData({ zxglArr: res.data, isEmpty: empty })
                        } else {
                            empty[2] = false;
                            that.setData({ zxglArr: res.data, isEmpty: empty })
                        }

                    }
                })
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
        // 获取缓存城市数据
        wx.getStorage({
            key: 'cityJson',
            success: function (res) {
                let cityJsonNew = res.data;
                that.setData({ prev: cityJsonNew.prev, city: cityJsonNew.city, area: cityJsonNew.area });
                //   that.setData({ json: cityJsonNew.json })
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
                        let cityUrlStr = cityUrlArr[0] + 's:' + cityUrlArr[1]
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
                                    json[i].children.shift();
                                    for (var j = 0; j < json[i].children.length; j++) {
                                        json[i].children[j].children.shift();
                                    }
                                };
                                // 筛选省份名称+id
                                for (let i = 0; i < json.length; i++) {
                                    prevJson.push({ id: json[i].id, text: json[i].text });
                                }
                                // 筛选第一省的第一个城市名称+id
                                for (let j = 0; j < json[0].children.length; j++) {
                                    cityJson.push({ id: json[0].children[j].id, text: json[0].children[j].text });
                                }
                                // 筛选第一省/第一个城市/第一个区域名称+id
                                for (let k = 0; k < json[0].children[0].children.length; k++) {
                                    areaJson.push({ id: json[0].children[0].children[k].id, text: json[0].children[0].children[k].text });
                                }
                                that.setData({ prev: prevJson, city: cityJson, area: areaJson, json: json });
                                wx.setStorage({
                                    key: 'cityJson',
                                    data: { prev: prevJson, city: cityJson, area: areaJson, json: json }
                                });
                            }
                        });
                    }
                });
            }
        });
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
    onShareAppMessage: function (res) {
        if (res.from === 'button') {
            // 来自页面内转发按钮
            if(res.target.dataset.style=='xgt'){
                return {
                    title: res.target.dataset.title,
                    path: '/pages/xiaoguotuxiangqfd/xiaoguotuxiangqfd?id=' + res.target.dataset.id + '&title=' + res.target.dataset.title,
                    imageUrl: oImgUrl + res.target.dataset.imgsrc,
                    success: function (res) {
                        // 转发成功
                    },
                    fail: function (res) {
                        // 转发失败
                    }
                }
            } else if (res.target.dataset.style == 'zxgs'){
                return {
                    title: res.target.dataset.title,
                    path: '/pages/det_company/det_company?id=' + res.target.dataset.id,
                    imageUrl: res.target.dataset.imgsrc,
                    success: function (res) {
                        // 转发成功
                    },
                    fail: function (res) {
                        // 转发失败
                    }
                }
            } else if (res.target.dataset.style == 'zxgl'){
                return {
                    title: res.target.dataset.title,
                    path: '/pages/shouyexq/shouyexq?id=' + res.target.dataset.id,
                    imageUrl: oImgUrl + res.target.dataset.imgsrc,
                    success: function (res) {
                        // 转发成功
                    },
                    fail: function (res) {
                        // 转发失败
                    }
                }
            }
        }

    },
    /**
     * 我的收藏tab切换
     */
    showModal: function (res) {
        let index = (res.currentTarget.dataset.index);
        let hideArr = [false,false,false];
        hideArr[index] = true;
        this.setData({ isHide: hideArr, fenlei: index});
        console.log(index)
    },
    /**
     * 点击跳转到效果图
     */
    toxgtDet:function(e){
        let id = e.currentTarget.dataset.id
        wx.navigateTo({
            url: '../xiaoguotuxiangqfd/xiaoguotuxiangqfd?id='+id
        })
    },
    /**
     * 点击跳转到装修公司
     */
    tozxgsDet: function (e) {
        let id = e.currentTarget.dataset.id
        wx.navigateTo({
            url: '../det_company/det_company?id='+id
        })
    },
    /**
     * 点击跳转到装修攻略
     */
    tozxglDet: function (e) {
        let id = e.currentTarget.dataset.id
        wx.navigateTo({
            url: '../shouyexq/shouyexq?id='+id
        })
    },
    toDel:function(e){
        let id = e.currentTarget.dataset.id,
            eStyle = e.currentTarget.dataset.style,
            userId = e.currentTarget.dataset.userid,
            that = this;
        if (eStyle === 'xgt'){
            wx.request({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: userId, // 用户ID
                    classtype: '8', // 装修效果图
                    classid: id
                },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {

                    if(res.data.state==1){
                        // 获取装修效果图
                        wx.showToast({
                            title: '取消收藏',
                            icon: 'success',
                            duration: 1200
                        });
                        wx.request({
                            url: apiUrl + '/appletcarousel/collect',
                            data: {
                                count: '10',
                                userid: userId,
                                classtype: '8'//效果图
                            },
                            header: {
                                'content-type': 'application/json'
                            },
                            success: function (res) {
                                let empty = that.data.isEmpty;

                                if (res.data.length > 0) {
                                    empty[0] = true;
                                    that.setData({ xgtArr: res.data, isEmpty: empty })
                                } else {
                                    empty[0] = false;
                                    that.setData({ xgtArr: res.data, isEmpty: empty })
                                }
                            }
                        });
                    }
                }
            });
        }else if(eStyle === 'zxgs'){
            wx.request({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: userId, // 用户ID
                    classtype: '9', // 装修公司
                    classid: id
                },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    if(res.data.state == 1){
                        // 获取装修公司
                        wx.request({
                            url: apiUrl + '/appletcarousel/collect',
                            data: {
                                userid: userId,
                                classtype: '9'//装修公司
                            },
                            header: {
                                'content-type': 'application/json'
                            },
                            success: function (res) {
                                let empty = that.data.isEmpty;
                                if (res.data.length > 0) {
                                    empty[1] = true;
                                    that.setData({ zxgsArr: res.data, isEmpty: empty })
                                } else {
                                    empty[1] = false;
                                    that.setData({ zxgsArr: res.data, isEmpty: empty })
                                }
                            }
                        });
                    }

                }
            });
        }else if(eStyle === 'zxgl'){
            wx.request({
                url: apiUrl + '/appletcarousel/editcollect',
                data: {
                    userid: userId, // 用户ID
                    classtype: '10', // 装修攻略
                    classid: id
                },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    if(res.data.state == 1){
                        // 获取装修攻略
                        wx.request({
                            url: apiUrl + '/appletcarousel/collect',
                            data: {
                                userid: userId,
                                classtype: '10'//装修攻略
                            },
                            header: {
                                'content-type': 'application/json'
                            },
                            success: function (res) {
                                let empty = that.data.isEmpty;
                                if (res.data.length > 0) {
                                    empty[2] = true;
                                    that.setData({ zxglArr: res.data, isEmpty: empty })
                                } else {
                                    empty[2] = false;
                                    that.setData({ zxglArr: res.data, isEmpty: empty })
                                }
                            }
                        });
                    }
                }
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
        let oldVal = that.data.val;
        that.setData({ val: val })
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
        this.setData({ isHideCity: true, isHide: [true, true, true] });
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
        this.setData({ selectTextDefault: '', isColor: true, isHideCity: true, selectText: selectText, prevCityAreaId: prevCityAreaId, areaId: areaId, serverVal: areaText });
    },
    getSheJi: function () {
        let regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
        let re = new RegExp(regu);

        let that = this;
        if (that.data.selectText.length < 1) {
            that.setData({ selectTextDefault: '选择城市' })
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
            alertViewWithCancel("提示", "用户名不能为数字", function () {
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
                    that.setData({ shejiPopHide: true })
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
                    that.setData({ shejiPopHide: true })
                    alertViewWithCancel("提示", "领取成功，稍后我们将联系您", function () { });
                }
            });
        }
    },
    getShejiPop() {
        this.setData({ shejiPopHide: false })
    },
    shejiclose() {
        this.setData({ shejiPopHide: true,emptyName:'',emptyPhone:''})
    },
    getNamesj(e) {
        this.setData({ personNamesj: e.detail.value })
    },
    getPhonesj(e) {
        this.setData({ telNumbersj: e.detail.value })
    },
    toZXGS(){
        wx.navigateTo({
            url: '../des_company/des_company',
        })
    },
    toZXGL() {
        wx.switchTab({
            url: '../zxgonglue_sy/zxgonglue_sy'
        })
    },
    toXGT(){
        wx.switchTab({
            url: '../xiaoguotu/xiaoguotu'
        })
    },
    downLoad(){
        let that = this;
        let xgtCount=that.data.countXgt+10,
            zxglCount = that.data.countZxgl+10,
            zxgsCount = that.data.countZxgs+10;
        console.log(this.data.fenlei)
        if(this.data.fenlei == '0'){
            // 获取装修效果图
            wx.request({
                url: apiUrl + '/appletcarousel/collect',
                data: {
                    count: xgtCount,
                    userid: that.data.userId,
                    classtype: '8'//效果图
                },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {

                    let empty = that.data.isEmpty;

                    if (res.data.length > 0) {
                        empty[0] = true;
                        that.setData({ xgtArr: res.data, isEmpty: empty, countXgt: xgtCount })
                    } else {
                        empty[0] = false;
                        that.setData({ xgtArr: res.data, isEmpty: empty, countXgt: xgtCount })
                    }
                }
            });


        } else if (this.data.fenlei == '1') {
            // 获取装修攻略
            wx.request({
                url: apiUrl + '/appletcarousel/collect',
                data: {
                    count: zxglCount,
                    userid: that.data.userId,
                    classtype: '10'//装修攻略
                },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    let empty = that.data.isEmpty;
                    if (res.data.length > 0) {
                        empty[2] = true;
                        that.setData({ zxglArr: res.data, isEmpty: empty, countZxgl:zxglCount })
                    } else {
                        empty[2] = false;
                        that.setData({ zxglArr: res.data, isEmpty: empty, countZxgl:zxglCount  })
                    }

                }
            })
        } else if (this.data.fenlei == '2'){
            // 获取装修公司
            wx.request({
                url: apiUrl + '/appletcarousel/collect',
                data: {
                    count: zxgsCount,
                    userid: that.data.userId,
                    classtype: '9'//装修公司
                },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    let empty = that.data.isEmpty;
                    if (res.data.length > 0) {
                        empty[1] = true;
                        that.setData({ zxgsArr: res.data, isEmpty: empty, countZxgs: zxgsCount})
                    } else {
                        empty[1] = false;
                        that.setData({ zxgsArr: res.data, isEmpty: empty, countZxgs: zxgsCount})
                    }
                }
            });
        }
    }
})