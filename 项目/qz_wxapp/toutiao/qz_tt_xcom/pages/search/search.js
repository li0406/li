const app = getApp()
import { getFilterData } from '../../utils/api'
Page({
  data: {
    dataList: [],
    nomore: 2,
    page: 1,
    city: null,
    keyword: '',
    cname: '',
    area: null,
    noData: false,
    noList: [],
    noSearch: true,
    ifZan: true
  },
  onLoad: function (options) {
    let city = options.city ? options.city : ''
    let area = options.area ? options.area : ''
    this.setData({
      city: city,
      area: area
    })
    this.getList()
  },
  onShow: function () {
    let arr = tt.getStorageSync("search").length ? tt.getStorageSync("search") : [];
    if (arr.length == 0) {
      this.setData({
        ifZan: false
      })
    }
    let rearr = arr.reverse()
    let xarr = Array.from(new Set(rearr))
    this.setData({
      noarr: xarr
    })
  },
  getFilter: function () {
    if(this.data.keyword == ''){
      return
    }
    this.setData({
      noSearch: false
    })
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
      '/v1/company/search',
      {
        page: this.data.page,
        city: this.data.city,
        area: this.data.area,
        keyword: this.data.keyword
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
        dataList = dataList.concat(datas.data.list.list);
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
        if (dataList.length == 0) {
          this.setData({
            noData: true
          })
        }
        if (datas.data.list.list.length < 10) {
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
  getList: function () {
    getFilterData(
      '/v1/company/list',
      {
        page: 1,
        city: this.data.city,
        area: this.data.area
      }
    ).then(data => {
      var datas = data;
      if (datas.error_code == 0) {
        let noList = datas.data.list.slice(0, 3)
        this.setData({
          noList: noList
        })
      } else {
      }
    }).catch(error => {
    })
  },
  searchWord: function (e) {
    let that = this;
    let value = e.detail.value;
    if (value == '') {
      return false
    }
    that.setData({
      keyword: value,
      page: 1,
      nomore: 2,
      dataList: [],
      noData: false
    })
    let arr = tt.getStorageSync("search").length ? tt.getStorageSync("search") : [];
    arr.push(value)
    let xarr = arr.slice(-10)
    tt.setStorage({
      key: 'search',
      data: xarr 
    });
    that.getFilter()
  },
  seaClear(e) {
    let value = e.detail.value;
    this.setData({
      xvalue : value
    })
  },
  clearArr(e){
    this.setData({
      xvalue : '',
      keyword: ''
    })
  },
  lateWord(e) {
    let that = this;
    let value = e.currentTarget.dataset.search ? e.currentTarget.dataset.search : '';
    that.setData({
      xvalue : value,
      keyword: value,
      page: 1,
      nomore: 2,
      dataList: [],
      noData: false
    })
    that.getFilter()
  },
  clearSea() {
    let that = this
    tt.showModal({
      title: "删除提示",
      content: "确定清除最近搜索的所有标签吗?",
      success(res) {
        if (res.confirm) {
          tt.removeStorageSync("search");
          that.setData({
            noarr: [],
            ifZan: false
          })
        } else if (res.cancel) {
        } else {
        }
      },
      fail(res) {
      }
    });
  },
  lower(e) {
    this.getFilter()
  },
})
