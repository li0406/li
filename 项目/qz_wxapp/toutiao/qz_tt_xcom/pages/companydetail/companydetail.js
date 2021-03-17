
import { getFilterData } from '../../utils/api'
const app = getApp()
Page({
  data: {
    company_id: '',
    bas: {},
    fwstrs: [],
    ifDesc: true,
    desLen: true,
    largeView: false,
    currentIndex: 1
  },
  onLoad: function (options) {
    let company_id = options.id ? options.id : ''
    this.setData({
      company_id: company_id
    })
    this.getComdata()
  },
  getComdata: function (options) {
    let that = this
    getFilterData(
      '/v1/company/basicinfo',
      {
        company_id: that.data.company_id
      }
    ).then(res => {
      if (res.error_code == 0) {
        let bas = res.data;
        let desc = res.data.introduction;
        let desLen = desc.length
        if (desLen < 120) {
          that.setData({
            desLen: false
          })
        }
        let xdesc = desc.slice(0, 120) + '...'
        let str = res.data.fw_type;
        if (str) {
          var fwstrs = new Array();
          fwstrs = str.split("、");
          that.setData({
            fwstrs: fwstrs,
          })
        }
        that.setData({
          bas: bas,
          xdesc: xdesc
        })
      } else {
      }
    }).catch(error => {
    })
  },
  infoDesc() {
    this.setData({
      ifDesc: false
    })
  },
  tuClick(e) {
    let id = e.currentTarget.dataset.id
    let that = this
    let swiperData = that.data.bas.honour
    that.setData({
      swiperData: swiperData,
      swiperLen: swiperData.length,
      largeView: !that.data.largeView,
      currentIndex : id+1

    })
  },
  getLargeImage() {
    this.setData({
      largeView: !this.data.largeView
    })
  },
  swiperChange(e) {
    this.setData({
      currentIndex: e.detail.current + 1
    })
  },
})
