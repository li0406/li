// pages/allist/allist.js
const app = getApp()
import { anLiList, anLiStyle, anLiType } from '../../utils/api'
Page({
  data: {
    anli_list: [],
    anli_sytle: [],
    anli_type: [],
    anli_type_zi: [],
    fenggeName: '风格',
    anliName: '装修案例',
    isData: false,
    imgList: {
      title: '',
      huxing: '',
      case_attr: '',
      imgs: []
    },
    isChoose: false,
    isChooseAn: false,
    isChooseOther: false,
    type: 0,
    totalPage: '',
    isFenLei: false,
    nomore: 2, //1加载中 2加载完成 3没数据了
    parms: {
      company_id: '',
      class_id: '',
      type_id: 0,
      fengge: '',
      page: 1
    },
    imgStart: ['/img/indexduione.png', '/img/top.png'],
    imgStarttwo: ['/img/indexduione.png', '/img/top.png'],
    imgEnd: ['/img/indexdui.png', '/img/topactive.png'],
    ifSoll: false,
    sTop: null
  },
  getAnLiList: function () {
    let obj = this.data;
    let that = this
    if (obj.nomore != 2) {
      return
    }
    that.setData({
      nomore: 1
    })
    if (obj.parms.page == 1) {
      tt.showLoading({
        title: '请求中，请稍后...',
      })
    }
    anLiList(
      '/v1/company/case/list',
      that.data.parms
    ).then(res => {
      if (res.error_code === '0') {
        let anli_list = that.data.anli_list
        if (res.data.list != '') {
          anli_list = anli_list.concat(res.data.list)
          that.setData({
            anli_list: anli_list,
            type: 0,
            totalPage: res.data.page.page_total_number,
            isData: false
          })
        } else {
          that.setData({
            isData: true
          })
        }

        if (res.data.list.length < 10) {
          that.setData({
            nomore: 3
          })
        } else {
          that.setData({
            nomore: 2,
            ['parms.page']: that.data.parms.page + 1
          })
        }
      } else {
        that.setData({
          nomore: 3
        })
      }
    })
  },
  getAnLiStyle: function () {
    anLiStyle('/v1/company/case/fengge', {
      company_id: this.data.parms.company_id
    }).then(res => {
      if (res.error_code === '0') {
        res.data.unshift({
          id: '',
          name: '不限'
        })
        this.setData({
          anli_sytle: res.data
        })
      }
    })
  },
  getAnLiType: function () {
    anLiType('/v1/company/case/type', {
      company_id: this.data.parms.company_id
    }).then(res => {
      if (res.error_code === '0') {
        res.data.unshift({
          class_id: '',
          class_name: '全部案例',
          child_list: [{
            type_id: '',
            type_name: '全部'
          }]
        })
        this.setData({
          anli_type: res.data
        })
      }
    })
  },
  openChoose: function (e) {
    let type = e.currentTarget.dataset.type
    this.setData({
      nomore: 2,
    })
    if (type == 1) {
      this.setData({
        isChooseOther: false,
      })
      if (this.data.type == 1) {
        this.setData({
          isChoose: false,
          isChooseAn: false,
          type: 0
        })
      } else {
        this.setData({
          isChoose: true,
          isChooseAn: true,
          type: type
        })
        this.getAnLiType()
      }
    } else {
      if (this.data.type == 2) {
        this.setData({
          isChoose: false,
          isChooseOther: false,
          isChooseAn: false,
          type: 0,
        })
      } else {
        this.setData({
          isChoose: true,
          isChooseOther: true,
          isChooseAn: false,
          type: type
        })
        this.getAnLiStyle()
      }
    }
  },
  getZiType: function (e) {
    let fu_id = e.currentTarget.dataset.class_id
    this.setData({
      ['parms.class_id']: fu_id,
    })
    for (let i = 0; i < this.data.anli_type.length; i++) {
      if (this.data.anli_type[i].class_id == fu_id) {
        this.setData({
          anli_type_zi: this.data.anli_type[i].child_list
        })
      }
    }
  },
  fenggeList: function (e) {
    let fengge_id = e.currentTarget.dataset.id
    if (fengge_id != '') {
      this.setData({
        imgStarttwo: []
      })
    } else {
      this.setData({
        imgStarttwo: ['/img/indexduione.png', '/img/top.png']
      })
    }
    this.setData({
      ['parms.fengge']: fengge_id,
      isChoose: false,
      ['parms.page']: 1,
      anli_list: [],
      isChooseOther: false
    })
    for (let i = 0; i < this.data.anli_sytle.length; i++) {
      if (fengge_id == this.data.anli_sytle[i].id) {
        this.setData({
          fenggeName: this.data.anli_sytle[i].name
        })
      }
    }
    if (this.data.parms.type_id == 0) {
      this.setData({
        ['parms.class_id']: '',
        //isChooseAn: false
      })
    }
    this.getAnLiList()
  },
  anList: function (e) {
    let type_id = e.currentTarget.dataset.type_id
    if (type_id != 0 || type_id == '') {
      this.setData({
        imgStart: []
      })
    }
    this.setData({
      ['parms.type_id']: type_id,
      isChoose: false,
      ['parms.page']: 1,
      anli_list: [],
      isChooseAn: false
    })
    if (type_id == '') {
      for (let i = 0; i < this.data.anli_type.length; i++) {
        if (this.data.parms.class_id == this.data.anli_type[i].class_id) {
          this.setData({
            anliName: this.data.anli_type[i].class_name
          })
        }
      }
    } else {
      for (let i = 0; i < this.data.anli_type_zi.length; i++) {
        if (type_id == this.data.anli_type_zi[i].type_id) {
          this.setData({
            anliName: this.data.anli_type_zi[i].type_name
          })
        }
      }
    }
    this.getAnLiList()
  },
  //滚动到底部100px时触发
  lower: function (e) {
    this.getAnLiList()
  },
  toimages: function (e) {
    let id = e.currentTarget.dataset.id
    let obj = this.data.anli_list
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
  closeMask: function () {
    this.setData({
      isChoose: false,
      isChooseAn: false,
      type: 0
    })
  },
  toSheJi: function () {
    tt.navigateTo({
      url: '../sheji/sheji' // 指定页面的url
    });
  },
  onLoad: function (options) {
    let id = options.id
    this.setData({
      ['parms.company_id']: id
    })
    this.getAnLiList()
  },
  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },
  onPageScroll: function (e) {
  }
})