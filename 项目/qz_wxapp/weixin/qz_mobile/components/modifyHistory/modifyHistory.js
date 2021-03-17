// components/modifyHistory.js
Component({
  /**
   * 组件的属性列表
   */
  properties: {
    showHistoryModal: {
      type: Boolean,
      value: false
    },
    historyData: {
      type: Object,
      value: {}
    }
  },

  /**
   * 组件的初始数据
   */
  data: {

  },

  /**
   * 组件的方法列表
   */
  methods: {
    preventTouchMoveHistory: function (e) {
      let that = this
      that.setData({
          showHistoryModal: false
      })
    },
  }
})
