const app = getApp()
import { getFilterData } from '../../utils/api'
Page({
  data: {
    dataList: [],
    nomore: 2,
    page: 1,
    city: 320500,
    cname: '',
    area: null,
    aname: '',
    cityInfo: {},
  },
  search() {
    tt.navigateTo({
      url: '/pages/search/search'
    })
  },
  onLoad: function (options) {
    if (options.cid) {
      let city = options.cid ? options.cid : ''
      let cname = options.cname ? options.cname : ''
      this.setData({
        city: city,
        cname: cname
      })
      app.globalData.cityInfo = options
      this.getFilter()
    } else {
      this.getCity()
    }
  },
  getCity: function (options) {
    let that = this
    app.getMyPosition((cityname) => {
      getFilterData(
        '/v2/getLocationv2').then(res => {
          if (res.error_code == 0) {
            let info = {
              cid: res.data.city_id,
              cname: res.data.city_name,
              area: res.data.area_id,
              aname: res.data.area_name
            }
            that.setData({
              city: res.data.city_id,
              cname: res.data.city_name,
              area: res.data.area_id,
              aname: res.data.area_name
            })
            app.globalData.cityInfo = info
            that.getFilter()
          } else {
            that.setData({
              city: 320500,
              cname: '苏州'
            })
            that.getFilter()
          }
        })
    })
  },
  getFilter: function () {
    var obj = this.data;
    if (obj.nomore != 2) {
      return
    }
    this.setData({
      nomore: 1
    })
    if (obj.page == 1) {
      tt.showLoading({
        title: '加载中',
      })
    }
    getFilterData(
      '/v1/company/list',
      {
        page: this.data.page,
        city: this.data.city ? this.data.city : 320500,
        area: this.data.area
      }
    ).then(data => {
      var datas = data;
      if (obj.page == 1) {
        tt.hideLoading()
        this.setData({
          dataList: []
        })
      }
      if (datas.error_code == 0) {
        var dataList = this.data.dataList;
        dataList = dataList.concat(datas.data.list);
        dataList.forEach(item => {
          item.url = '/pages/detail/detail?id=' + item.id
          let x = parseInt((Math.ceil(item.score * 10 / 5)) / 2)
          let y = (Math.ceil(item.score * 10 / 5)) % 2
          item.xxin = new Array(x)
          item.yxin = new Array(y)
        })
        this.setData({
          dataList: dataList
        })
        if (datas.data.list.length < 10) {
          this.setData({
            nomore: 3
          })
        } else {
          this.setData({
            nomore: 2,
            page: this.data.page + 1
          })
        }
      } else {
        this.setData({
          nomore: 3
        })
      }
    }).catch(error => {
    })
  },
  lower(e) {
    this.getFilter()
  },
  upper(e) {
    this.setData({
      dataList: [],
      nomore: 2,
      page: 1
    })
    this.getFilter()
  },
  toItem(e) {
    let url = e.currentTarget.dataset.url
    tt.navigateTo({
      url: url
    })
  },
  hotClick(e) {
    let url = e.currentTarget.dataset.url
    tt.reLaunch({
      url: url
    })
  },
})
