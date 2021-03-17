import {
    memberReportCheckList,
    memberReportCheckAction,
    seleOptions,
} from "../../utils/api.js"

function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
    wx.showModal({
        title: title,
        content: content,
        showCancel: false,
        success: function(res) {
            if (res.confirm) {
                confirm();
            } else if (res.cancel) {

            }
        }
    });
}

function alertViewNoCancel(title = "提示", content = "消息提示", confirm) {
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
    let date = new Date(timestamp * 1000); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
    let Y = date.getFullYear() + '/';
    let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '/';
    let D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
    return Y + M + D;
}
function getTime1(timestamp) {
  let date = new Date(timestamp * 1000); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
  let Y = date.getFullYear() + '/';
  let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '/';
  let D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
  let h = (date.getHours() < 10 ? '0' + (date.getHours()) : date.getHours()) + ':';
  let m = (date.getMinutes() < 10 ? '0' + (date.getMinutes()) : date.getMinutes()) + ':';
  let s = (date.getSeconds() < 10 ? '0' + (date.getSeconds()) : date.getSeconds());
  return Y + M + D + ' ' + h + m + s;
}
Page({

    /**
     * 页面的初始数据
     */
    data: {
        curCity: '不限',
        cs: '',
        style: [],  // 合作类型范例
        style_index: 0,
        isNew: [{
            id: '',
            name: '请选择'
        }, {
            id: '1',
            name: '新会员'
        }, {
            id: '2',
            name: '老会员'
        }],
        isNew_index: 0,
        status: [{
            id: '',
            name: '请选择'
        }, {
            id: '2',
            name: '待审核'
        }, {
            id: '3',
            name: '审核通过/待客服上传'
        }, {
            id: '4',
            name: '未通过'
        }, {
            id: '5',
            name: '客服审核通过'
        }, {
            id: '6',
            name: '客服未通过普通信息更改'
        }, {
            id: '7',
            name: '客服未通过需要重新审核'
        }, {
            id: '8',
            name: '客服完成上传'
          }, {
            id: '9',
            name: '客服暂停'
          }],
        status_index: 0,
        parms: {
            cs: '',
            id: '',
            search: '',
            cid: '',
            area_id: '',
            page: 1,
            page_size: 10
        },
        pageNumber: [],
        showModal: false,
        rejectShowModal: false,
        modalTitle: '审核',
        page: true,
        pageCurrent: 1,
        checkList: [],
        checkCooperationType: '', // 需要审核记录的cooperation_type
        checkId: '', // 需要审核记录的id
        checkPassVal: '', // 存储通过还是不通过的值，3表示通过，4表示不通过
        checkRemark: '', // 存储审核备注
        condition: '', // 搜索条件
        noResult: false
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        this.saleOptions()
    },

    /**
     * 生命周期函数--监听页面初次渲染完成
     */
    onReady: function() {

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function() {
        let that = this;
        that.setData({
            ['parms.cs']: that.data.curCity,
        });
        if (that.data.parms.cs == '不限') {
            that.setData({
                ['parms.cs']: '',
            })
        }
        that._search();
    },
    // 获取返点比例
    saleOptions() {
      let that = this;
      wx.getStorage({
        key: 'info',
        success: function (res) {
          let token = res.data.token;
          seleOptions('/v1/sale_report/options', {}, {
            'content-type': 'application/json',
            'token': token
          }).then(res => {
            if (res.error_code == 0) {
              let typeList =[{id:'',name:'请选择'} , ...res.data.cooperation_type_list]
              that.setData({
                style: typeList
              })
            } else {
              alertViewNoCancel("请求失败", res.error_msg, function () { });
              return;
            }
          }).catch(function (error) { })
        },
        fail: function (res) {
          wx.showToast({
            title: '请登陆',
            icon: 'none',
            duration: 2000
          })
          setTimeout(function () {
            wx.navigateTo({
              url: '../login/login'
            })
          }, 2000)
        }
      })
    },
    bindPickerChange_style: function(e) {
        let that = this;
        that.setData({
            style_index: e.detail.value,
            ['parms.page']: 1
        })
        that._search();
    },
    bindPickerChange_isNew: function(e) {
        let that = this;
        that.setData({
            isNew_index: e.detail.value,
            ['parms.page']: 1
        })
        that._search();
    },
    bindPickerChange_status: function(e) {
        let that = this;
        that.setData({
            status_index: e.detail.value,
            ['parms.page']: 1
        })
        that._search();
    },
    toCity: function() {
        let city = this.data.curCity;
        wx.navigateTo({
            url: '../city/city?needArea=1&curCity=' + city
        })
    },
    toDel: function(e) {
        let that = this;
        // let id = that.
        // let cooperation_type = that.
        alertViewWithCancel("删除", "确认删除该公司信息", function() {
            wx.getStorage({
                key: 'info',
                success: function(res) {
                    let token = res.data.token;
                    delReport('/v1/sale_report/del', {
                        cooperation_type: cooperation_type,
                        id: id
                    }, {
                        token: token,
                        'content-type': 'application/x-www-form-urlencoded',
                    }).then(res => {
                        if (res.error_code == 0) {

                        } else {
                            alertViewNoCancel("删除失败", res.error_msg, function() {});
                            return;
                        }
                    })
                }
            })
        });
    },
    toPage: function(e) {
        let that = this;
        let p = e.detail.value;
        p = Number(p) + 1;
        that.setData({
            ['parms.page']: p
        })
        that._search();
        wx.pageScrollTo({
            scrollTop: 0,
            duration: 0
        })
    },
    _search() {
        let that = this;
        let style_index = !this.data.style.length ? '' : this.data.style[this.data.style_index].id
        let is_new = this.data.isNew[this.data.isNew_index].id
        let status = this.data.status[this.data.status_index].id
        // 用于正常筛选
        let queryObj = {
            cooperation_type: style_index,
            is_new,
            status,
            cs: this.data.parms.cs,
            admin_user: 1,
            p: this.data.parms.page,
            size: 10
        }
        // 用于查询
        if (this.data.condition) {
            queryObj = {
                condition: this.data.condition,
                p: this.data.parms.page,
                admin_user: 1,
                cs: this.data.parms.cs,
                size: 10
            }
        }
        wx.getStorage({
            key: 'info',
            success: (res) => {
                let token = res.data.token;
                memberReportCheckList('/v1/sale_report/list', queryObj, {
                    'content-type': 'application/json',
                    'token': token
                }).then(res => {
                    if (res.error_code == 0) {
                        let totalNumber = res.data.page.page_total_number;
                        let list = res.data.list;
                        let pageArr = [];
                        for (let i = 0; i < totalNumber; i++) {
                            pageArr.push(i + 1)
                        }
                        if (list.length <= 0) {
                            that.setData({
                                page: false,
                                noResult: true,
                                checkList: [],
                                pageNumber: []
                            })
                        } else {
                            list.forEach((item, index) => {
                                item.contract_start = item.contract_start ? getTime(item.contract_start) : item.contract_start
                                item.contract_end = item.contract_end ? getTime(item.contract_end) : item.contract_end
                                item.current_contract_start = item.current_contract_start ? getTime(item.current_contract_start) : item.current_contract_start
                                item.current_contract_end = item.current_contract_end ? getTime(item.current_contract_end) : item.current_contract_end
                              if (item.submit_time == '0'){
                                item.submit_time = ''
                              }else{
                                item.submit_time = getTime1(item.submit_time)
                              }
                              if (item.check_time == '0') {
                                item.check_time = ''
                              } else {
                                item.check_time = getTime1(item.check_time)
                              }
                              
                            })
                            that.setData({
                                page: true,
                                checkList: list,
                                pageCurrent: res.data.page.page_current,
                                pageTotalNumber: res.data.page.page_total_number,
                                noResult: false,
                                pageNumber: pageArr
                            })
                        }
                    } else {
                        that.setData({
                            checkList: []
                        })
                    }
                })
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
        that._search()
        wx.pageScrollTo({
            scrollTop: 0,
            duration: 0
        })
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
        that._search()
        wx.pageScrollTo({
            scrollTop: 0,
            duration: 0
        })

    },
    /**
     * 显示审核弹窗，同时要记录cooperation_type和id
     */
    showModalFn(e) {
        const checkCooperationType = e.currentTarget.dataset.cooperationtype
        const checkId = e.currentTarget.dataset.id
        this.setData({
            showModal: true,
            checkCooperationType: checkCooperationType,
            checkId: checkId,
            checkRemark: '',
            checkPassVal: ''
        })
    },
    rejectShowModalFn(e) {
        const checkCooperationType = e.currentTarget.dataset.cooperationtype
        const checkId = e.currentTarget.dataset.id
        this.setData({
            rejectShowModal: true,
            checkCooperationType: checkCooperationType,
            checkId: checkId,
            rejectCheckRemark: ''
        })
    },

    /**
     * 自定义模态框取消事件
     */
    modalCancel() {
        // wx.showToast({
        //   title: '取消',
        // })
    },
    /**
     * 自定义模态框确认事件
     */
    modalConfirm() {
        // wx.showToast({
        //   title: '确认',
        // })
        if (!this.data.checkPassVal) {
            alertViewWithCancel('提示', '请选择是否通过', function() {})
            this.setData({
                showModal: true
            })
            return
        }
        this.handleCheckAjax()
    },
    checkMemberReport(e) {
        let type = 'hd';
        let id = e.currentTarget.dataset.id;
        let ctype = e.currentTarget.dataset.cooperationtype
        switch (ctype) {
            case 1: // 装修
                wx.navigateTo({
                    url: '../zxMemberOffer/zxMemberOffer?type=' + type + '&id=' + id + '&ctype=' + ctype
                })
                break
            case 2: // 独家
                wx.navigateTo({
                    url: '../zxMemberOffer/zxMemberOffer?type=' + type + '&id=' + id + '&ctype=' + ctype
                })
                break
            case 3: // 垄断
                wx.navigateTo({
                    url: '../zxMemberOffer/zxMemberOffer?type=' + type + '&id=' + id + '&ctype=' + ctype
                })
                break
            case 4: // 三维家
                wx.navigateTo({
                    url: '../swjMemberOffer/swjMemberOffer?type=' + type + '&id=' + id + '&ctype=' + ctype
                })
                break
            case 5: // erp
                wx.navigateTo({
                    url: '../erpMemberOffer/erpMemberOffer?type=' + type + '&id=' + id + '&ctype=' + ctype
                })
                break
            case 6: // 签单返点
                wx.navigateTo({
                    url: '../zxMemberOffer/zxMemberOffer?type=' + type + '&id=' + id + '&ctype=' + ctype
                })
                break
            case 7: // 全屋定制
                wx.navigateTo({
                  url: '../zxMemberOffer/zxMemberOffer?type=' + type + '&id=' + id + '&ctype=' + ctype
                })
              break
            case 8: // 会员延期
                wx.navigateTo({
                  url: '../delayMemberOffer/delayMemberOffer?type=' + type + '&id=' + id + '&ctype=' + ctype
                })
              break
            case 14: // 常规保产值
                wx.navigateTo({
                    url: '../zxMemberOffer/zxMemberOffer?type=' + type + '&id=' + id + '&ctype=' + ctype
                })
                break
            case 15: // 签单返点
                wx.navigateTo({
                    url: '../zxMemberOffer/zxMemberOffer?type=' + type + '&id=' + id + '&ctype=' + ctype
                })
                break
            default:
                break
        }
    },
    /**
     * 发送审核数据
     */
    handleCheckAjax() {
        let that = this;
        wx.getStorage({
            key: 'info',
            success: (res) => {
                let token = res.data.token;
                memberReportCheckAction('/v1/sale_report/set_status', {
                    cooperation_type: this.data.checkCooperationType,
                    id: this.data.checkId,
                    status: this.data.checkPassVal, // 通过填3， 不通过填4
                    admin_remarke: this.data.checkRemark
                }, {
                    'content-type': 'application/json',
                    'token': token
                }).then(res => {
                    if (res.error_code == 0) {
                        that.setData({
                            showModal: false
                        })
                        that._search()
                    } else {
                        alertViewWithCancel('提示', res.error_msg, function() {})
                        that.setData({
                            showModal: true
                        })
                    }
                })
            }
        })
    },
    // 驳回按钮
    rejectModalCancel() {},
    rejectModalConfirm(){
        this.rejectCheckAjax()
    },
    // 驳回接口
    rejectCheckAjax() {
        let that = this;
        let query = {
            cooperation_type: this.data.checkCooperationType,
            id: this.data.checkId,
            remark: this.data.rejectCheckRemark
        }
        console.log(query)
        wx.getStorage({
            key: 'info',
            success: (res) => {
                let token = res.data.token;
                memberReportCheckAction('/v1/sale_report/recall', query, 
                {
                    'content-type': 'application/json',
                    'token': token
                }).then(res => {
                    if (res.error_code == 0) {
                        that.setData({
                            rejectShowModal: false
                        })
                        that._search()
                    } else {
                        alertViewWithCancel('提示', res.error_msg, function() {})
                        that.setData({
                            rejectShowModal: true
                        })
                    }
                })
            }
        })
    },
    /**
     * 搜索函数
     */
    searchWord(e) {
        let that = this;
        let value = e.detail.value;
        that.setData({
            condition: e.detail.value,
            ['parms.page']: 1
        })
        that._search();
    },
    /**
     * 获取通过还是不通过的值
     */
    radioChange(e) {
        this.setData({
            checkPassVal: e.detail.value
        })
    },
    /**
     * 获取备注的值
     */
    getRemark(e) {
        this.setData({
            checkRemark: e.detail.value
        })
    },
    getRemarkReject(e) {
        this.setData({
            rejectCheckRemark: e.detail.value
        })
    }
})