<template>
  <div class="offer-list">
    <div class="top-qian-main">
      <el-tabs v-model="activeName" @tab-click="handleClick">
        <el-tab-pane label="城市报价" name="first">
          <cityOfferList />
        </el-tab-pane>
        <el-tab-pane label="三维家ERP报价" name="second">
          <sanWeiJiaOfferList />
        </el-tab-pane>
      </el-tabs>
    </div>
  </div>
</template>

<script>
import cityOfferList from './components/cityOfferList'
import sanWeiJiaOfferList from './components/sanWeiJiaOfferList'
export default {
  name: 'OfferList',
  components: {
    cityOfferList,
    sanWeiJiaOfferList
  },
  data() {
    return {
      activeName: 'first'
    }
  },
  created() {
    const currentTab = localStorage.getItem('offerManager_current_tab')
    if (currentTab) {
      this.activeName = currentTab
    }
  },
  beforeRouteLeave(to, from, next) {
    const currentTab = localStorage.getItem('offerManager_current_tab')
    if (currentTab) {
      localStorage.removeItem('offerManager_current_tab')
    }
    next()
  },
  methods: {
    handleClick(tab, event) {
      const currentTab = tab.name
      localStorage.setItem('offerManager_current_tab', currentTab)
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
    .offer-list {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
        .top-qian-main {
            margin-top: 20px;
            padding: 20px;
            border-top: 3px solid #999;
            box-sizing: border-box;
            background-color: #fff;
        }
    }
</style>
