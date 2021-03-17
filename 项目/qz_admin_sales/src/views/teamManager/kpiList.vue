<template>
  <div class="team-manager-list">
    <div class="main">
      <el-tabs v-model="activeName" @tab-click="handleClick">
        <el-tab-pane label="团队指标" name="team">
          <teamItemList />
        </el-tab-pane>
        <el-tab-pane label="个人指标" name="individual">
          <individualList />
        </el-tab-pane>
      </el-tabs>
    </div>
  </div>
</template>
<script>
import teamItemList from './components/teamItemList'
import individualList from './components/individualList'
export default {
  name: 'TeamList',
  components: {
    teamItemList,
    individualList
  },
  data() {
    return {
      activeName: 'team'
    }
  },
  methods: {
    handleClick(tab, event) {
      // console.log(tab)
    }
  }
}
</script>
<style lang='scss' scoped>
.team-manager-list {
  width: 100%;
  padding: 20px;

  .main {
    background: #fff;
    margin-top: 20px;
    padding: 20px;
    border-top: 3px solid #999;
    box-sizing: border-box;
  }
}
</style>
