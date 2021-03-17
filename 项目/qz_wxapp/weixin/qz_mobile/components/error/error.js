// components/error.js
Component({
  /**
   * 组件的属性列表
   */
  properties: {
    noResult: {
      type: Boolean,
      value: false
    },
    noInternet: {
      type: Boolean,
      value: false
    }
  },

  /**
   * 组件的初始数据
   */
  data: {
    noResult: false,
    noInternet: false,
    url:''
  },

  /**
   * 组件的方法列表
   */
  methods: {
    // 网络故障
    toCustom: function () {
      this.triggerEvent('reload',{})
    },
  }
})