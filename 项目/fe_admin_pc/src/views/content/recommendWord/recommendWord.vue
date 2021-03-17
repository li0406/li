<template>
  <div class="recommend-word">
    <h2 class="title">推荐语管理</h2>
    <el-tabs v-model="activeName" type="card" @tab-click="handleClick">
      <el-tab-pane label="推荐语管理" name="first">
        <wordManage />
      </el-tab-pane>
      <el-tab-pane label="默认推荐语" name="second">
        <defaultWord />
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script>
import wordManage from './wordManage.vue'
import defaultWord from './defaultWord.vue'
export default {
  name: 'recommendWord',
  components: {
    wordManage,
    defaultWord
  },
  data() {
    return {
      activeName: 'first'
    }
  },
  computed: {},
  watch: {},
  created() {
    // this.getList()
  },
  methods: {
    handleClick(tab, event) {
      console.log(tab, event)
    }
  }
}
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>
.recommend-word{
  padding: 20px;
  .title{
    font-size: 20px;
    line-height: 30px;
    padding-bottom: 20px;
  }
}
</style>
