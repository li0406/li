// pages/order/order.js
import {
    getOrderList,
    getAddOptions,
    visitAdd
} from "../../utils/api.js"
const app = getApp();
const util = require('../../utils/util.js');

function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
    wx.showModal({
        title: title,
        content: content,
        showCancel: false,
        success: function(res) {
            if (res.confirm) {
                confirm();
            }
        }
    });
}

function getTime(timestamp) {
    let date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
    let Y = date.getFullYear() + '-';
    let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
    let D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
    return Y + M + D;
}
Page({
    data: {
        curCity: '不限',
        curArea: '',
        cs: '',
        qx: '',
        createTime: '2010-10-10',
        nowTime: util.formatDate(new Date()),
        date_begin: '',
        date_end: '',
        nomore: 2, //1加载中 2加载完成 3没数据了
        page: 1,
        // tab
        fen: [''],
        fen_index: -1,
        yu: [], //筛选预算
        yu_index: -1,
        cla: [],
        cla_index: -1,
        meth: [],
        meth_index: -1,
        fenId: '',
        yuId: '',
        claId: '',
        methId: '',
        pxList:[
            {id:'1', name: '按发单时间'},
            {id:'2', name: '按会员跟单时间'}
        ],
        pxIndex: -1,
        lfList:[
            {id:'', name: '请选择'},
            {id:'1', name: '已量房'},
            {id:'2', name: '未量房'}
        ],
        lfIndex: 0,
        gdList:[
            {id:'', name: '请选择'},
            {id:'1', name: '已跟单'},
            {id:'2', name: '未跟单'}
        ],
        gdIndex: 0,
        // 请求数据
        list: [],
        pageCurrent: '0',
        pageTotalNumber: '0',
        parms: {
            id: '',
            order_search: '',
            company_search: '',
            date_begin: '',
            date_end: '',
            yusuan: '',
            type_fw: '',
            leixing: '',
            fangshi: '',
            cid: '',
            area_id: '',
            liangfang_status: '',
            track_status: '',
            order_by: '',
            mianji_max: '',
            mianji_min: '',
            page: 1,
            page_size: 10
        },
        noResult: false,
        page: false,
        pageNumber: [],
        showModal: false, //回访弹框是否显示
        items: [{
                name: '1',
                value: '公司1'
            },
            {
                name: '2',
                value: '公司2',
                checked: 'true'
            },
            {
                name: '3',
                value: '公司3'
            },
            {
                name: '4',
                value: '公司4',
                checked: 'true'
            },
        ],
        companys: [], //需要回访的装修公司
        company_ids_hf: [], //选中的需要回访的装修公司
        visit_step_list: [], //回访阶段
        hfIndex: 0, //回访索引
        hfId: '', //回访id 
        // 下拉选择
        select: false,
        grade_name: '--请选择--',
        order_id: '', //订单id
        company_ids: '', //要跟进公司id
        visit_step: '', //回访阶段
        visit_need: '', //需要回访内容
        visit_user_need: '', //装企反馈详情
        hf_order_id: '', // 需要回访的订单id
        maskOfferShow: false, // 筛序侧边栏
        offerHeight: 0,
        activeTag: false,
        typeFwId: '', // 订单状态
        leixingId:'', //装修类型
    },
    //弹窗显示
    visitShow: function(e) {
        let that = this;
        wx.navigateTo({
            url: '../createdVisitBack/createdVisitBack?orderid=' + e.target.dataset.id
        })
        return
        
    },
    //复制
    copyBtn: function(e) {
        wx.setClipboardData({
            data: e.target.dataset.id,
            success (res) {
              wx.showToast({
                title: '订单号已复制',
                duration: 1000
              })
            }
        })
    },
    checkboxChange_hf: function(e) {
        this.setData({
            company_ids_hf: e.detail.value
        })
    },
    //回访内容
    visitCont(e) {
        console.log('文本', e.detail.value)
        this.setData({
            visit_need: e.detail.value
        })
    },
    //反馈详情
    feedbackDetail(e) {
        console.log('反馈', e.detail.value)
        this.setData({
            visit_user_need: e.detail.value
        })
    },

    //关闭弹窗
    go() {
        this.closeDia()
    },
    // 取消
    quxiao(e) {
        this.closeDia()
    },
    //提交
    tijiao: function(e) {
        let that = this
        if(!this.data.company_ids_hf.join(',')) {
            wx.showToast({
                icon: 'none',
                duration: 2000,
                title: '请选择要求回访的装修公司名称'
            })
            return
        }
        if(this.data.hfId == 0) {
            wx.showToast({
                icon: 'none',
                duration: 2000,
                title: '请选择回访阶段'
            })
            return
        }
        if(!this.data.visit_need) {
            wx.showToast({
                icon: 'none',
                duration: 2000,
                title: '请输入需要回访的内容'
            })
            return
        }
        wx.getStorage({
            key: 'info',
            success: function(res) {
                let token = res.data.token;
                visitAdd('/v1/visit/add', {
                    order_id: e.target.dataset.orderid,
                    company_ids: that.data.company_ids_hf.join(','),
                    visit_step: that.data.hfId,
                    visit_need: that.data.visit_need,
                    visit_user_need: that.data.visit_user_need
                }, {
                    'content-type': '	application/json',
                    'token': token
                }).then(res => {
                    if (res.error_code == 0) {
                        wx.showToast({
                            title: '成功',
                            icon: 'success',
                            duration: 2000,
                        })
                    }else{
                        wx.showToast({
                            icon: 'none',
                            duration: 2000,
                            title: res.error_msg
                        })
                    }
                })
                that.closeDia()
            }
        })
    },
    closeDia() {
        this.setData({
            showModal: false,
            visit_need: '',
            visit_user_need: '',
            company_ids_hf: []
        })
    },
    //下拉选择
    bindShowMsg() {
        this.setData({
            select: !this.data.select
        })
    },
    /**
     * 已选下拉框
     */
    mySelect(e) {
        console.log(e)
        var name = e.currentTarget.dataset.name
        this.setData({
            grade_name: name,
            select: false
        })
    },

    //城市选择
    toCity: function() {
        let city = this.data.curCity;
        wx.navigateTo({
            url: '../city/city?needArea=1&curCity=' + city
        })
    },
    //搜索框(订单、小区名)
    orderSearch(e){
        let that = this;
        let value = e.detail.value;
        that.setData({
            ['parms.order_search']: value,
            ['parms.page']: 1
        })
        that.getOrderList(that.data.parms);
    },
    orderSearchChange(e){
        let that = this;
        let value = e.detail.value;
        that.setData({
            ['parms.order_search']: value
        })
    },
    //搜索框(公司id简称全程)
    companySearch(e){
        let that = this;
        let value = e.detail.value;
        that.setData({
            ['parms.company_search']: value,
            ['parms.page']: 1
        })
        that.getOrderList(that.data.parms);
    },
    companySearchChange(e){
        let that = this;
        let value = e.detail.value;
        that.setData({
            ['parms.company_search']: value
        })
    },
    // 监听输入
    watchPassWord: function(event) {
        console.log(event.detail.value);
    },
    //选择时间
    dateChange: function(e) {
        var keys = e.currentTarget.dataset.time;
        var obj = {};
        obj[keys] = e.detail.value;
        if (keys == 'date_begin' && this.data.date_end != '' && e.detail.value > this.data.date_end) {
            obj.date_end = e.detail.value;
        }
        this.setData(obj);
        let that = this
        if (that.data.date_begin != '' && that.data.date_end != '') {
            let date_begin = that.data.date_begin
            let date_end = that.data.date_end
            that.setData({
                ['parms.date_begin']: date_begin,
                ['parms.date_end']: date_end,
                ['parms.page']: 1
            })
            that.getOrderList(that.data.parms);
        }
    },
    // 选项卡
    // 排序
    bindPickerChange_px: function(e) {
        let that = this;
        let id = that.data.pxList[e.detail.value].id
        that.setData({
            pxIndex: e.detail.value,
            ['parms.order_by']: id,
            ['parms.page']: 1
        })
        that.getOrderList(that.data.parms);
    },
    // 量房
    bindPickerChange_lf: function(e) {
        let that = this;
        let id = that.data.lfList[e.detail.value].id
        that.setData({
            lfIndex: e.detail.value,
            ['parms.liangfang_status']: id,
            ['parms.page']: 1
        })
        that.getOrderList(that.data.parms);
    },
    // 跟单
    bindPickerChange_gd: function(e) {
        let that = this;
        let id = that.data.gdList[e.detail.value].id
        that.setData({
            gdIndex: e.detail.value,
            ['parms.track_status']: id,
            ['parms.page']: 1
        })
        that.getOrderList(that.data.parms);
    },
    getFormOptions: function() {
        let that = this;
        wx.getStorage({
            key: 'info',
            success: function(res) {
                let token = res.data.token;
                getOrderList('/v1/order/options', '', {
                    'content-type': 'application/json',
                    'token': token
                }).then(res => {
                    let yu = res.data.yusuan.map(item => {
                        item.active = false
                        return item
                    })
                    let meth = res.data.fangshi.map(item => {
                        item.active = false
                        return item
                    })
                    that.setData({
                        fen: res.data.type_fw,
                        yu: yu,
                        cla: res.data.leixing,
                        meth: meth
                    })
                })
            }
        })
    },

    //列表数据
    getOrderList: function(parms) {
        let that = this;
        wx.getStorage({
            key: 'info',
            success: function(res) {
                let token = res.data.token;
                getOrderList('/v1/order/list',
                    parms, {
                        'content-type': 'application/json',
                        'token': token
                    }
                ).then(res => {
                    if (res.error_code == 0) {
                        // let idArr = res.data.list
                        //获取页码
                        let totalNumber = res.data.page.page_total_number;
                        let pageArr = [];
                        for (let i = 0; i < totalNumber; i++) {
                            pageArr.push(i + 1)
                        }
                        let datalist = res.data.list
                        if (datalist.length < 1) {
                            that.setData({
                                list: [],
                                page: false,
                                noResult: true
                            })
                            return false
                        }
                        datalist.forEach((item, index) => {
                            item.url = '../orderdetail/orderdetail?id=' + item.id + '&type_fw=' + item.type_fw
                        })
                        // let time_begin = res.data.search ? getTime(res.data.search.time_begin) : ''
                        // let time_end = res.data.search ? getTime(res.data.search.time_end) : ''
                        let time_begin = res.data.search ? res.data.search.time_begin : ''
                        let time_end = res.data.search ? res.data.search.time_end : ''
                        that.setData({
                            list: datalist,
                            pageCurrent: res.data.page.page_current,
                            pageTotalNumber: res.data.page.page_total_number,
                            pageNumber: pageArr,
                            noResult: false,
                            page: true,
                            date_begin: time_begin,
                            date_end: time_end
                        })
                        if (res.data.page.total_number < 10) {
                            that.setData({
                                page: false
                            })
                        } else {
                            that.setData({
                                page: true
                            })
                        }
                    } else {
                        that.setData({
                            list: [],
                            page: false,
                            noResult: true,
                            noInternet: false,

                        })
                    }
                }).catch(function(error) {
                    that.setData({
                        list: [],
                        page: false,
                        noResult: false,
                        noInternet: true
                    })
                })
            },
            fail: function(res) {
                wx.showToast({
                    title: '请登陆',
                    icon: 'none',
                    duration: 2000
                })
                setTimeout(function() {
                    wx.navigateTo({
                        url: '../login/login'
                    })
                }, 2000)

            }
        })
    },
    prevBtn: function(e) {
        let that = this;
        let page = that.data.pageCurrent;
        if (page <= 1) {
            page = 1;
            return;
        } else {
            page = page - 1;
        }
        that.setData({
            ['parms.page']: page
        })
        that.getOrderList(that.data.parms)

    },
    nextBtn: function(e) {
        let that = this;
        let page = that.data.pageCurrent;
        let max = that.data.pageTotalNumber;
        if (page >= max) {
            page = max;
            return;
        } else {
            page = page + 1;
        }
        that.setData({
            ['parms.page']: page
        })
        that.getOrderList(that.data.parms)
        wx.pageScrollTo({
            scrollTop: 0,
            duration: 0
        })

    },
    // 网络故障
    toCustom: function() {
        wx.navigateTo({
            url: '../order/order'
        })
    },
    // 分页
    toPage: function(e) {
        let that = this;
        let p = e.detail.value;
        p = Number(p) + 1;
        that.setData({
            ['parms.page']: p
        })
        that.getOrderList(that.data.parms);
        wx.pageScrollTo({
            scrollTop: 0,
            duration: 0
        })
    },
    preventTouchMove() {
        return true;
    },
    // 显示筛选
    showOfferDetail() {
        this.setData({
            maskOfferShow: true,
            offerHeight: "100%"
        })
        setTimeout(() => {
            this.setData({
                activeTag: true
            })
        }, 200)
    },
    // 隐藏筛选
    hideMaskOffer() {
        this.setData({
            maskOfferShow: false
        })
    },
    // 背景点击
    closeOffer() {
        this.setData({
            maskOfferShow: false,
            activeTag: false
        })
      this.getDetail()
    },
    // 订单状态
    typeFwBtn(e){
        let that = this
        let id = e.currentTarget.dataset.id
        let oldId = that.data.typeFwId
        if(id == oldId){
            id = ''
        }
        that.setData({
            ['prams.type_fw']: id,
            typeFwId: id
        })
    },
    // 装修类型
    leixingBtn(e){
        let that = this
        let id = e.currentTarget.dataset.id
        let oldId = that.data.leixingId
        if(id == oldId){
            id = ''
        }
        that.setData({
            ['parms.leixing']: id,
            leixingId: id
        })
    },
    mianjiMax(e){
        let val = e.detail.value;
        this.setData({
            ['parms.mianji_max']: val
        })

    },
    mianjiMin(e){
        let val = e.detail.value;
        this.setData({
            ['parms.mianji_min']: val
        })
    },
    zxBtn(e){
        let that = this
        let id = e.currentTarget.dataset.id
        let index = e.currentTarget.dataset.index
        let meth = that.data.meth
        meth[index].active = !meth[index].active
        that.setData({
            meth : meth
        })
    },
    ysBtn(e){
        let that = this
        let id = e.currentTarget.dataset.id
        let index = e.currentTarget.dataset.index
        let yu = that.data.yu
        yu[index].active = !yu[index].active
        that.setData({
            yu : yu
        })
    },
    // 重置
    resetBtn(){
        let that = this
        let yu = that.data.yu
        let meth = that.data.meth
        yu.map(item => {
            item.active = false
            return item
        })
        meth.map(item => {
            item.active = false
            return item
        })
        that.setData({
            yu: yu,
            meth: meth,
            typeFwId: '',
            leixingId: '',
            ['parms.type_fw']: '',
            ['parms.leixing']: '',
            ['parms.fangshi']: '',
            ['parms.yusuan']: '',
            ['parms.mianji_min']: '',
            ['parms.mianji_max']: ''
        })
    },
    //完成
    completeBtn(){
        let that = this
        let yu = that.data.yu
        let meth = that.data.meth
        let max = that.data.parms.mianji_max
        let min = that.data.parms.mianji_min
        if(min > max && min && max){
            alertViewWithCancel('提示', '面积最低值不得大于最高值',function(){})
            return
        }else{
            let fangshi = []
            let yusuan = []
            yu.forEach(item => {
                if(item.active){
                    yusuan.push(item.id)
                }
            })
            meth.forEach(item => {
                if(item.active){
                    fangshi.push(item.id)
                }
            })
            that.setData({
                ['parms.type_fw']: that.data.typeFwId,
                ['parms.leixing']:  that.data.leixingId,
                ['parms.fangshi']: fangshi.join(','),
                ['parms.yusuan']: yusuan.toString(),
            })
        }
        that.getOrderList(that.data.parms);
        that.hideMaskOffer()
    },
    onLoad: function(options) {
        this.getFormOptions()
        let that = this;
        // 获取参数
        if (Object.keys(options).length != 0) {
            let company_id = options.id
            if (options.date_begin) {
                let date_begin = options.date_begin
                let date_end = options.date_end
                that.setData({
                    ['parms.order_search']: company_id,
                    ['parms.date_begin']: date_begin,
                    ['parms.date_end']: date_end,
                    date_begin: date_begin,
                    date_end: date_end
                })
            } else {
                that.setData({
                    ['parms.company_search']: company_id
                })
            }
        }
        that.getOrderList(that.data.parms);
    },

    onReady: function() {

    },

    onShow: function() {
        let that = this;
        that.setData({
            ['parms.cid']: that.data.cs,
            ['parms.area_id']: that.data.qx
        });

        that.getOrderList(that.data.parms)
        if (that.data.list != '') {
            that.setData({
                noResult: false
            })
        }
    },

    /**
     * 生命周期函数--监听页面隐藏
     */
    onHide: function() {

    },

    /**
     * 生命周期函数--监听页面卸载
     */
    onUnload: function() {

    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function() {

    },

    /**
     * 页面上拉触底事件的处理函数
     */
    onReachBottom: function() {

    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function() {

    }
})