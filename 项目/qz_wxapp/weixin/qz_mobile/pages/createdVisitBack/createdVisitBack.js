// pages/order/order.js
import {
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
        fen: [],
        fen_index: -1,
        yu: [],
        yu_index: -1,
        cla: [],
        cla_index: -1,
        meth: [],
        meth_index: -1,
        fenId: '',
        yuId: '',
        claId: '',
        methId: '',
        // 请求数据
        list: [],
        pageCurrent: '0',
        pageTotalNumber: '0',
        parms: {
            id: '',
            search: '',
            date_begin: '',
            date_end: '',
            yusuan: '',
            type_fw: '',
            leixing: '',
            fangshi: '',
            cid: '',
            area_id: '',
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
        hf_order_id: '' // 需要回访的订单id
    },
    getInfo(options) {
        let that = this
        wx.getStorage({
            key: 'info',
            success: function(res) {
                let token = res.data.token;
                getAddOptions('/v1/visit/add_options', {
                    order_id: options.orderid,
                }, {
                    'content-type': '	application/json',
                    'token': token
                }).then(res => {
                    console.log(res)
                    if (res.error_code == 0) {
                        let companys = res.data.company_list
                        let visit_step_list = [{id: 0, name: '请选择'}].concat(res.data.visit_step_list)
                     
                        that.setData({
                            companys: companys,
                            visit_step_list: visit_step_list,
                            hfId: visit_step_list[0].id,
                            hf_order_id: options.orderid
                        })
                    } else {
                        console.log('请求失败，请重新请求')
                    }
                })
            }
        })
        that.setData({
            showModal: true
        })
    },
    checkboxChange_hf: function(e) {
        this.setData({
            company_ids_hf: e.detail.value
        })
    },
    bindPickerChange_hf: function(e) {
        let id = this.data.visit_step_list[e.detail.value].id
        console.log(id)
        console.log(e.detail.value)
        this.setData({
            hfIndex: e.detail.value,
            hfId: id,
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
                        wx.navigateBack({
                            delta: 1
                        })
                    }else{
                        wx.showToast({
                            icon: 'none',
                            duration: 2000,
                            title: res.error_msg
                        })
                    }
                })
            }
        })
    },
    onLoad: function(options) {
        console.log(options)
        this.getInfo(options)
    },

    onReady: function() {

    },

    onShow: function() {
        
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