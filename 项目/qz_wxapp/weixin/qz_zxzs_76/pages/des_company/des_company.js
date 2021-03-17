// pages/zhuangxiugs/zhuangxiugs.js
const app = getApp(),
      apiUrl = app.getApiUrl();
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
        isHide: [true, true, true],
        itemHide: true,
        isHideCity: true,
        selectText:'',
        prev: [],
        city: [],
        area: [],
        prevIndex: '0',
        cityIndex: '0',
        areaIndex: '0',
        serverVal: '服务区域',
        styleVal: '擅长风格',
        rangeVal: '公司规模',
        styleId:'',
        rangeId:'',
        navListData: [],
        navItemData: [],
        navIndex:'',
        companyList: [],// 装修公司的条目数据
        count:9,// 首次加载装修公司的条目
        areaId:'',// 区县ID
        loadBool:true,
        boolName: false,
        boolTel: false,
        personName:'',
        xzcity:'请选择城市',
        telNumber:'',
        src:'',
        emptyCompany:true,
        mark:true,
        userId:'',
        val:[],
        valuecity:null

    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad (options) {
        let that = this;
        let json = [];
        let prevJson = [];
        let cityJson = [];
        let areaJson = [];
        let cityUrl,userId;
        // 获取页面来源src
        if (options.src) {
            that.setData({ src: options.src });
            app.setNewStorage('src', options.src, 86400);
        } else {
            if (app.getNewStorage('src')) {
                that.setData({ src: app.getNewStorage('src') });
            } else {
                that.setData({ src: app.globalData.sourceMark });
            }
        }
        app.getUserInfo((res)=>{
            userId = res.userId;
        });
        wx.request({
            url: apiUrl + '/appletcarousel/company?userid=' + userId,
            data: { count: 9 },
            header: {
                'content-type': 'application/json'
            },
            success: function (res) {
                wx.showLoading({
                    title: '页面加载中',
                })

                setTimeout(function () {
                    wx.hideLoading()
                }, 1200);
                let arr = [],
                    style = res.data.attribute.fengge,
                    scale = res.data.attribute.guimo;
                arr[0] = [];
                arr[1] = style;
                arr[2] = scale;
                that.setData({ navListData: arr, companyList: res.data.list, userId: userId });
                wx.setStorage({
                    key: 'companyInfo',
                    data: res.data
                });
            }
        });
        // 获取缓存城市数据
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
        this.setData({ isHideCity: true });
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
    navHandle(e) {
        let isHideArr = [true, true, true],
            index = e.currentTarget.dataset.index;
        isHideArr[index] = !this.data.isHide[index];
        this.setData({ navIndex: index, itemHide: isHideArr[index], isHide: isHideArr, navItemData: this.data.navListData[index] });
    },
    /**
     * 装修公司条件筛选
     */
    selectItemVal(e){
        let navIndexVal = e.currentTarget.dataset.val,
            id = e.currentTarget.dataset.id,
            that = this,
            isHideArr = [true, true, true];
        if (e.currentTarget.dataset.index == 0){

        } else if (e.currentTarget.dataset.index == 1){

            that.setData({ itemHide: true, styleVal: navIndexVal, styleId: id, isHide: isHideArr});

        } else if (e.currentTarget.dataset.index == 2){
            that.setData({ itemHide: true, rangeVal: navIndexVal, rangeId: id, isHide: isHideArr });
        }
        wx.request({
            url: apiUrl + '/appletcarousel/company?userid=' + that.data.userId,
            data: {qx: that.data.areaId, fg: that.data.styleId, gm: that.data.rangeId,count:9},
            header: {
                'content-type': 'application/json'
            },
            success: function (res) {
                let arr = [],
                    style = res.data.attribute.fengge,
                    scale = res.data.attribute.guimo;
                arr[0] = [];
                arr[1] = style;
                arr[2] = scale;
                if (res.data.list.length <= 0) {
                    that.setData({ emptyCompany: false });
                } else {
                    that.setData({ emptyCompany: true });
                }
                that.setData({ navListData: arr, companyList: res.data.list, loadBool: true, count: 9})
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
    /**
     * 城市选择弹框 显示/隐藏
     */
    selectHandle() {
        let that = this;
        that.setData({ isHideCity: false});
    },
    /** 
     * 城市选择 滑动/滚动
     */
    bindChange (e) {
        let that = this;
        let cityJson = [];
        let areaJson = [];
        let oldVal = that.data.val;
        let val = e.detail.value;
        that.setData({ val: val });
        let prev = val[0];
        let city = val[1];
        let area = val[2];
        wx.getStorage({
            key: 'cityJson',
            success (res) {
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
    /**
     * 城市选择 取消/关闭 按钮
     */
    closebtn() {
        this.setData({ isHideCity: true });
    },
    /**
     * 城市选择 确认/选中 按钮
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
        this.setData({ xzcity: '',isHideCity: true, selectText: selectText, prevCityAreaId: prevCityAreaId, areaId: areaId, serverVal: areaText});
        wx.request({
            url: apiUrl + '/appletcarousel/company?userid=' + that.data.userId,
            data: {qx: areaId, fg: that.data.styleId, gm:that.data.rangeId,count:9},
            header: {
                'content-type': 'application/json'
            },
            success: function (res) {
                let arr = [],
                    style = res.data.attribute.fengge,
                    scale = res.data.attribute.guimo;
                arr[0] = [];
                arr[1] = style;
                arr[2] = scale;
                if (res.data.list.length <= 0) {
                    that.setData({ emptyCompany: false });
                }else{
                    that.setData({ emptyCompany: true });
                }
                that.setData({navListData: arr, companyList: res.data.list,loadBool:true ,count:9});
            }
        });

    },
    /**
     * 上拉加载更多数据
     */
    downLoad(){
        let count = this.data.count,
            that = this;
        if (that.data.loadBool){
            wx.showToast({
                title: '数据加载中...',
                icon: 'loading'
            });
            let len = that.data.companyList.length;
            count += 10;
            wx.request({
                url: apiUrl + '/appletcarousel/company?userid=' + that.data.userId,
                data: { qx: that.data.areaId, fg: that.data.styleId, gm: that.data.rangeId, count: count },
                header: {
                    'content-type': 'application/json'
                },
                success: function (res) {
                    if (len == res.data.list.length){
                        that.setData({ loadBool:false });
                    }else{
                        that.setData({ companyList: res.data.list, count: count, loadBool: true });
                    }
                }
            });
        }else{
            wx.showToast({
                title: '没有更多了',
                icon: 'success'
            });
        }
    },
    getName(e){
        this.setData({ personName: e.detail.value});
    },
    getTel(e){
        this.setData({ telNumber: e.detail.value });
    },
    getSheJi() {
        let regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
        let re = new RegExp(regu);
        let that = this;
        if (that.data.selectText.length < 1) {
            that.setData({
                xzcity: '选择城市',
            })
            alertViewWithCancel("提示", "请选择您的所在地区", function () { });
            return;
        } else {
            that.setData({
                xzcity: '',
            })
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
        if (that.data.personName.length < 1) {
            alertViewWithCancel("提示", "请输入您的称呼", function () {
                that.setData({ boolName: true });
            });
            return;
        } else if (that.data.personName.search(re) == -1) {
            alertViewWithCancel("提示", "用户名不能为数字、或者特殊字符", function () {
                that.setData({ boolName: true });
            });
            return;
        }
        wx.request({
            url: apiUrl + '/zxjt/submit_order/?src=' + that.data.src,
            data: {
                name: that.data.personName,
                tel: that.data.telNumber,
                cs: that.data.prevCityAreaId
            },
            header: {
                'content-type': 'application/x-www-form-urlencoded'
            },
            method: "POST",
            success(res) {
                alertViewWithCancel("提示", "提交成功，请注意接听电话", function () { });
            }
        });
    }
})