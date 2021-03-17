
const app = getApp()
import { getFilterData } from '../../utils/api'
Page({
  data: {
    company_id: '',
    bas: {},
    example: [],
    designer: [],
    imgList: {
      title: '',
      huxing: '',
      case_attr: '',
      imgs: []
    },
  },
  onLoad: function (options) {
    let company_id = options.id ? options.id : ''
    this.setData({
      company_id: company_id
    })
    this.getComdata()
    this.getCompanyData()
  },
  getComdata: function (options) {
    let that = this
    getFilterData(
      '/v1/company/basicinfo',
      {
        company_id: that.data.company_id,
      }
    ).then(res => {
      if (res.error_code == 0) {
        let bas = res.data
        let x = parseInt((Math.ceil(bas.star * 10 / 5)) / 2)
        let y = (Math.ceil(bas.star * 10 / 5)) % 2
        let xxin = new Array(x)
        let yxin = new Array(y)
        that.setData({
          bas: bas,
          xxin: xxin,
          yxin: yxin
        })
      } else {
      }
    }).catch(error => {
    })
  },
  getCompanyData: function (options) {
    let that = this
    getFilterData(
      '/v1/company/detaillist',
      {
        company_id: that.data.company_id,
      }
    ).then(res => {
      if (res.error_code == 0) {
        let example = res.data.example
        let designer = res.data.designer
        that.setData({
          example: example,
          designer: designer
        })
      } else {
      }
    }).catch(error => {
    })
  },
  toimages: function (e) {
    let id = e.currentTarget.dataset.id
    let obj = this.data.example
    for (let i = 0; i < obj.length; i++) {
      if (obj[i].id == id) {
        this.setData({
          ['imgList.imgs']: obj[i].img_list,
          ['imgList.title']: obj[i].title,
          ['imgList.case_attr']: obj[i].case_attr,
          ['imgList.huxing']: obj[i].huxing
        })
      }
    }
    tt.navigateTo({
      url: '../openimages/openimages?imgList=' + JSON.stringify(this.data.imgList) + '&type=1' // 指定页面的url
    });
  },
  toRight() {
    
    tt.navigateTo({
      url: '/pages/designteam/designteam?id='+this.data.bas.id
    });
  }
})
