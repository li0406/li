// pages/report/report.js
import { getContactList, getJournalDetail, editJournal, addUser, addSign } from "../../utils/api.js"
const app = getApp();
var dateTimePicker = require('../../utils/dateTimePicker.js');
var dateObj = new Date();
function alertViewWithCancel(title = "提示", content = "消息提示", confirm) {
  wx.showModal({
    title: title,
    content: content,
    success: function (res) {
      if (res.confirm) {
        confirm();
      } else if (res.cancel) {
        // console.log('用户点击取消')
      }
    }
  });
}
function getTime1(timestamp) {
  let date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
  let Y = date.getFullYear() + '-';
  let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
  let D = date.getDate() + ' ';
  let h = (date.getHours() < 10 ? '0' + (date.getHours()) : date.getHours()) + ':';
  let m = (date.getMinutes() < 10 ? '0' + (date.getMinutes()) : date.getMinutes());
  // let s = (date.getSeconds() < 10 ? '0' + (date.getSeconds()) : date.getSeconds());
  return Y + M + D + h + m;
}
function getTime2(timestamp) {
  let date = new Date(timestamp * 1000);//时间戳为10位需*1000，时间戳为13位的话不需乘1000
  let Y = date.getFullYear() + '-';
  let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
  let D = date.getDate() + ' ';
  return Y + M + D;
}
Page({
  /**
   * 页面的初始数据
   */
  data: {
    token: "",
    company_id: '',
    tabActive: true,
    company_name: '',
    cname: '',
    aname: '',
    contact_name: '',
    contactName1:'',
    contact_job: '',
    contact_job1: '',
    tel: '',
    wechat: '',
    qq: '',
    khStyle: '',
    address: '',
    intention: '',
    visit_style: '',
    qianyue_status: '',
    desc: '',
    visit_next_time: '',
    uname: '',
    pre_money: '',
    retainage_time: '',
    user_id: '',
    contactName: ['请选择'],
    allUser: [],
    userList: [],
    allUserList:[],
    userIndex: 0,
    valueName:null,
    boxTitle: '添加联系人',
    valueName: null,
    isHide: true,
    show: true,
    box: true,
    khType: ['请选择', '新客户', '老客户'],
    khTypeIndex: 0,
    khSource: ['请选择', '已签会员', '客服咨询转接', '会员转介绍', '后台注册公司0', '扫楼', '同行网站', '订单轰炸', '空间营销', '其他', '合作页面'],
    khSourceIndex: 0,
    selectTextDefault: '请选择',
    selectText: '',
    way: ['请选择', '上门', '电话', 'QQ', '微信'],
    level: ['请选择', 'A', 'B', 'C'],
    wayIndex: 0,
    levelIndex: 0,
    signTime: ['请选择', '未签约', '已签约'],
    timeIndex: 0,
    date: '2018-10-01',
    time: '12:00',
    visitStartTime: '请选择',
    visitEndTime: '请选择',
    contactTime: '请选择',
    dateTimeArray: null, //二维数组，保存年月日时分秒各列能选择的数据
    dateTime: null,   //用户保存翻动后时间的位置 
    defaultTime: null, //用户保存当前默认时间的位置
    startYear: 2000, //初始化开始年份
    endYear: 2030,   //初始化结束年份
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    let that = this;
    let companyId = options.companyId;
    let visitId = options.visitId;
    // 获取完整的年月日 时分秒，以及默认显示的数组
    var obj = dateTimePicker.dateTimePicker(this.data.startYear, this.data.endYear);
    // 精确到分的处理，将数组的秒去掉
    var lastArray = obj.dateTimeArray.pop();
    var lastTime = obj.dateTime.pop();
    this.setData({
      defaultTime: obj.dateTime,
      dateTime: obj.dateTime,
      dateTimeArray: obj.dateTimeArray,
      company_id: companyId
    });
    that.getList(companyId);

  },
  getList: function (companyId) {
    let that = this;
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        getContactList('/v1/report/list_user', {
          company_id: companyId
        }, {
            'content-type': 'application/json',
            'token': token
          }).then(res => {
            if (res.error_code == 0) {
              let userList = [];
              let user = {};
              for (let i = 0; i < res.data.contacts.length; i++) {
                user = res.data.contacts[i];
                userList.push(user);
              }
              let allUser = res.data.contacts;
              let allUserList = [];
              for (let k in allUser) {
                allUserList[k] = { company_id: '', user_id: '', user_name: '', user_tel: '', user_wx: '', user_qq: '', user_job: '' };
                allUserList[k].company_id = allUser[k].company_id;
                allUserList[k].user_id = allUser[k].id;
                allUserList[k].user_name = allUser[k].name;
                allUserList[k].user_tel = allUser[k].tel;
                allUserList[k].user_wx = allUser[k].wx;
                allUserList[k].user_qq = allUser[k].qq;
                allUserList[k].user_job = allUser[k].job;
              }

              that.setData({
                company_name: res.data.company_name,
                cname: res.data.cname,
                aname: res.data.aname,
                contact_name: res.data.contacts[0].name,
                contact_job: res.data.contacts[0].job,
                tel: res.data.contacts[0].tel,
                wechat: res.data.contacts[0].wx,
                qq: res.data.contacts[0].qq,
                khTypeIndex: res.data.is_new,
                khSourceIndex: res.data.customer_source,
                address: res.data.address,
                levelIndex: res.data.intention,
                user_id: res.data.user_id,
                allUser: res.data.contacts,
                userList: userList,
                allUserList: allUserList
              })
            }
          });
      },
      fail: function () {
        wx.redirectTo({
          url: '../login/login',
        })
      }
    })
  },
  changeType: function (e) {
    this.setData({
      tabActive: e.currentTarget.dataset.type == "true"
    });
  },
  
  // 选择客户类型
  selectType: function (e) {
    this.setData({
      khTypeIndex: e.detail.value
    })
  },
  // 选择客户来源
  selectSource: function (e) {
    this.setData({
      khSourceIndex: e.detail.value
    })
  },
  // 选择谈判方式
  selectWay: function (e) {
    this.setData({
      wayIndex: e.detail.value
    })
  },
  // 选择意向等级
  selectLevel: function (e) {
    this.setData({
      levelIndex: e.detail.value
    })
  },
  // 选择签约时间
  selectSignTime: function (e) {
    this.setData({
      timeIndex: e.detail.value
    })
  },
  baseSave: function (e) {
    let that = this;
    let user_id = that.data.user_id;
    let company_id = that.data.company_id;
    let companyName = that.data.company_name;
    let contactName = that.data.contact_name;
    let contactjob = that.data.contact_job;
    let user_intention = that.data.levelIndex;
    let tel = that.data.tel;
    let wechat = that.data.wechat;
    let qq = that.data.qq;
    let user_style = that.data.khTypeIndex;
    let khSource = that.data.khSource[that.data.khSourceeIndex];
    let user_dz = that.data.address;
    let allUserList = that.data.allUserList;
    if (companyName == '') {
      alertViewWithCancel("保存失败", "请输入公司名称", function () {
      });
      return;
    }
    if (contactName == '') {
      alertViewWithCancel("保存失败", "请输入联系人", function () {
      });
      return;
    }
    if (user_intention == 0) {
      alertViewWithCancel("保存失败", "请选择意向等级", function () {
      });
      return;
    }
    if (user_style == 0) {
      alertViewWithCancel("保存失败", "请选择客户类型", function () {
      });
      return;
    }
    if (user_dz == 0) {
      alertViewWithCancel("保存失败", "请输入公司地址", function () {
      });
      return;
    }
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        editJournal('/v1/report/edit_user', {
          company_id: company_id,
          user_intention: user_intention,
          user_style: user_style,
          user_dz: user_dz,
          company_user_id: user_id,
          users: allUserList,
        }, {
            'content-type': 'application/json',
            'token': token
          }).then(res => {
            if(res.error_code == 0){
              wx.showToast({
                title: '保存成功',
                icon: 'success',
                duration: 200
              })
              setTimeout(function () {
                wx.navigateBack({
                  delta: 1,
                })
              }, 300)
            }else{
              alertViewWithCancel("保存失败", res.error_msg, function () {
              });
            }
          })
      },
      fail: function () {
        wx.redirectTo({
          url: '../login/login',
        })
      }
    })

  },
  signSave: function (e) {
    let that = this;
    let company_id = that.data.company_id; 
    let visit_style = that.data.wayIndex;
    let visit_start_time = that.data.visitStartTime;
    let visit_end_time = that.data.visitEndTime;
    let qianyue_status = that.data.timeIndex;
    let desc = that.data.desc;
    let pre_money = that.data.pre_money;
    let visit_next_time = that.data.contactTime;
    if (visit_style == 0) {
      alertViewWithCancel("保存失败", "请选择谈判方式", function () {
      });
      return;
    }
    if (visit_start_time == '请选择' || visit_end_time == '请选择') {
      alertViewWithCancel("保存失败", "请选择拜访时间", function () {
      });
      return;
    } else if (visit_start_time > visit_end_time) {
      alertViewWithCancel("保存失败", "请选择正确的拜访时间", function () {
      });
      return;
    } 
    // if (qianyue_status == 0) {
    //   alertViewWithCancel("保存失败", "请选择预计签约", function () {
    //   });
    //   return;
    // }
    if (desc == '') {
      alertViewWithCancel("保存失败", "请输入谈话内容", function () {
      });
      return;
    } else if (desc.length < 35) {
      alertViewWithCancel("保存失败", "字数未满35字，请继续输入", function () {
      });
      return;
    }
    if (visit_next_time == '请选择') {
      alertViewWithCancel("保存失败", "请选择下次联系时间", function () {
      });
      return;
    }
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        addSign('/v1/report/add_visit',{
          company_id: company_id,
          visit_style: visit_style,
          visit_start_time: visit_start_time,
          visit_end_time: visit_end_time,
          qianyue_status: qianyue_status,
          desc: desc,
          pre_money: pre_money,
          visit_next_time: visit_next_time
        },{
          'content-type': 'application/json',
          'token': token
        }).then(res => {
          if (res.error_code == 0) {
            wx.showToast({
              title: '保存成功',
              icon: 'success',
              duration: 200
            })
            setTimeout(function () {
              wx.navigateBack({
                delta: 1,
              })
            }, 300)
          } else {
            alertViewWithCancel("保存失败", res.error_msg, function () {
            });
          }
        })
      },
      fail: function () {
        wx.redirectTo({
          url: '../login/login',
        })
      }
    })
  },
  changeDateTime1(e) {
    var arr = this.data.dateTime,
      dateArr = this.data.dateTimeArray;
    var year = dateArr[0][arr[0]];
    var month = dateArr[1][arr[1]];
    var date = dateArr[2][arr[2]];
    var hour = dateArr[3][arr[3]];
    var min = dateArr[4][arr[4]];
    var text = year + '-' + month + '-' + date + ' ' + hour + ':' + min;
    this.setData({
      dateTime: e.detail.value,
      visitStartTime: text
    });
  },
  changeDateTimeColumn1(e) {
    // 根据月份判断天数
    var arr = this.data.dateTime,
      dateArr = this.data.dateTimeArray;
    var year = dateArr[0][arr[0]];
    var month = dateArr[1][arr[1]];
    var date = dateArr[2][arr[2]];
    var hour = dateArr[3][arr[3]];
    var min = dateArr[4][arr[4]];
    arr[e.detail.column] = e.detail.value;
    dateArr[2] = dateTimePicker.getMonthDay(dateArr[0][arr[0]], dateArr[1][arr[1]]);
    this.setData({
      dateTimeArray: dateArr,
      dateTime: arr
    });
  },
  changeDateTime2(e) {
    var arr = this.data.dateTime,
      dateArr = this.data.dateTimeArray;
    var year = dateArr[0][arr[0]];
    var month = dateArr[1][arr[1]];
    var date = dateArr[2][arr[2]];
    var hour = dateArr[3][arr[3]];
    var min = dateArr[4][arr[4]];
    var text = year + '-' + month + '-' + date + ' ' + hour + ':' + min;
    this.setData({
      dateTime: e.detail.value,
      visitEndTime: text
    });
  },
  changeDateTimeColumn2(e) {
    // 根据月份判断天数
    var arr = this.data.dateTime,
      dateArr = this.data.dateTimeArray;
    var year = dateArr[0][arr[0]];
    var month = dateArr[1][arr[1]];
    var date = dateArr[2][arr[2]];
    var hour = dateArr[3][arr[3]];
    var min = dateArr[4][arr[4]];
    arr[e.detail.column] = e.detail.value;
    dateArr[2] = dateTimePicker.getMonthDay(dateArr[0][arr[0]], dateArr[1][arr[1]]);
    this.setData({
      dateTimeArray: dateArr,
      dateTime: arr
    });
  },
  changeDateTime3(e) {
    var arr = this.data.dateTime,
      dateArr = this.data.dateTimeArray;
    var year = dateArr[0][arr[0]];
    var month = dateArr[1][arr[1]];
    var date = dateArr[2][arr[2]];
    var hour = dateArr[3][arr[3]];
    var min = dateArr[4][arr[4]];
    var text = year + '-' + month + '-' + date + ' ' + hour + ':' + min;
    this.setData({
      dateTime: e.detail.value,
      contactTime: text
    });
  },
  changeDateTimeColumn3(e) {
    // 根据月份判断天数
    var arr = this.data.dateTime,
      dateArr = this.data.dateTimeArray;
    var year = dateArr[0][arr[0]];
    var month = dateArr[1][arr[1]];
    var date = dateArr[2][arr[2]];
    var hour = dateArr[3][arr[3]];
    var min = dateArr[4][arr[4]];
    arr[e.detail.column] = e.detail.value;
    dateArr[2] = dateTimePicker.getMonthDay(dateArr[0][arr[0]], dateArr[1][arr[1]]);
    this.setData({
      dateTimeArray: dateArr,
      dateTime: arr
    });
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },
  // 选择联系人
  selectContactName: function (e) {
    let userIndex = e.currentTarget.dataset.index;
    this.setData({
      isHide: false,
      show: true,
      userIndex: userIndex,
    })
  },
  choose: function (e) {
    let that = this;
    let name = e.currentTarget.dataset.name;
    let userIndex = e.currentTarget.dataset.index;
    let contact_job = that.data.allUser[userIndex].job;
    let tel = that.data.allUser[userIndex].tel;
    let wechat = that.data.allUser[userIndex].wx;
    let qq = that.data.allUser[userIndex].qq;
    this.setData({
      isHide: true,
      contact_name: name,
      contact_job: contact_job,
      userIndex: userIndex,
      tel:tel,
      wechat:wechat,
      qq:qq,
      valueName: [userIndex]
    });
  },
  changeName: function (e) {
    let index = e.detail.value[0];
    this.setData({
      userIndex: index,
      valueName: [index]
    })
  },
  close: function (e) {
    this.setData({ isHide: true });
  },
  addbtn: function () {
    let that = this;
    that.setData({
      show: false,
      box: true,
      contactName1: '',
      contact_job1: '',
      tel1: '',
      wechat1: '',
      qq1: ''
    });
  },
  editbtn: function (e) {
    let that = this;
    let userIndex = that.data.userIndex;
    let userList = that.data.userList[userIndex];
    that.setData({
      show: false,
      box: false,
      boxTitle: '编辑联系人',
      contactName1: userList.name,
      contact_job1: userList.job,
      tel1: userList.tel,
      wechat1: userList.wx,
      qq1: userList.qq
    });
  },
  delbtn: function () {
    let that = this;
    let index = that.data.userIndex;
    let company_id = that.data.company_id;
    let allUserList = that.data.allUserList;
    if (allUserList.length == 1){
      alertViewWithCancel("删除失败", "最少绑定一个联系人", function () {
      });
      return;
    }else{
      alertViewWithCancel("确定删除联系人吗", "", function () {
        allUserList.splice(index, 1);
        that.setData({
          isHide: true
        })
      });
    }
    
  },
  boxchange: function (e) {
    // console.log(e)
  },
  cancel: function () {
    let that = this;
    that.setData({ 
      show: true,
      isHide:true
    });
  },
  newSure: function () {
    let that = this;
    let company_id = that.data.company_id;
    let contancName1 = that.data.contactName1;
    let contactJob1 = that.data.contact_job1;
    let tel = that.data.tel1;
    let wechat = that.data.wechat1;
    let qq = that.data.qq1;
    if (contancName1 == '') {
      alertViewWithCancel("保存失败", "请输入联系人", function () {
      });
      return;
    }
    if (tel =='' && wechat == '' && qq == ''){
      alertViewWithCancel("保存失败", "请输入联系方式，手机、微信、QQ必须填写一项", function () {
      });
      return;
    } else if (tel != '') {
      var reg = new RegExp("^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\\d{8}$");
      if (!reg.test(tel)) {
        alertViewWithCancel("提示", "请输入正确的手机号", function () {
        });
        return false;
      }
    }
    wx.getStorage({
      key: 'info',
      success: function (res) {
        let token = res.data.token;
        addUser('/v1/report/add_user', {
          company_id: company_id,
          user_name: contancName1,
          user_job: contactJob1,
          user_tel: tel,
          user_wx: wechat,
          user_qq: qq
        }, {
            'content-type': 'application/json',
            'token': token
          }).then(res => {
            var obj = {};
            obj.company_id = company_id;
            obj.user_name = contancName1;
            obj.user_job = contactJob1;
            obj.user_tel = tel;
            obj.user_wx = wechat;
            obj.user_qq = qq;
            that.data.allUserList.push(obj);

            that.setData({
              isHide:true,
              userList: that.data.allUserList
            })
            that.getList(company_id);
          })
      },
      fail: function () {
        wx.redirectTo({
          url: '../login/login',
        })
      }
    })


  },
  editSure: function () {
    let that = this;
    let index = that.data.userIndex;

    // 编辑选中的一个联系人
    let user_name = that.data.contactName1;
    let user_job = that.data.contact_job1;
    let user_tel = that.data.tel1;
    let user_wx = that.data.wechat1;
    let user_qq = that.data.qq1;
    that.data.allUserList[index].user_name = user_name;
    that.data.allUserList[index].user_job = user_job;
    that.data.allUserList[index].user_tel = user_tel;
    that.data.allUserList[index].user_wx = user_wx;
    that.data.allUserList[index].user_qq = user_qq;
    if (user_name == '') {
      alertViewWithCancel("保存失败", "请输入联系人", function () {
      });
      return;
    }
    if (user_tel == '' && user_wx == '' && user_qq == '') {
      alertViewWithCancel("保存失败", "请输入联系方式，手机、微信、QQ必须填写一项", function () {
      });
      return;
    } else if (user_tel != '') {
      var reg = new RegExp("^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\\d{8}$");
      if (!reg.test(user_tel)) {
        alertViewWithCancel("提示", "请输入正确的手机号", function () {
        });
        return false;
      }
    }

    that.setData({
      show:true,
      isHide: true,
      contact_name:that.data.contactName1,
      contact_job: that.data.contact_job1,
      tel:that.data.tel1,
      wechat: that.data.wechat1,
      qq: that.data.qq1,
    })
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
  onShareAppMessage: function () {

  },

  contactName: function (e) {
    this.setData({
      contactName1: e.detail.value
    })
  },
  contactJob: function (e) {
    this.setData({
      contact_job1: e.detail.value
    })
  },
  tel: function (e) {
    this.setData({
      tel1: e.detail.value
    })
  },
  wechat: function (e) {
    this.setData({
      wechat1: e.detail.value
    })
  },
  qq: function (e) {
    this.setData({
      qq1: e.detail.value
    })
  },
  companyAddress: function (e) {
    this.setData({
      address: e.detail.value
    })
  },
  desc: function (e) {
    this.setData({
      desc: e.detail.value
    })
  },
  pre_money: function (e) {
    this.setData({
      pre_money: e.detail.value
    })
  }
})