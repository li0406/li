// 最新申请量房动态

<template>
  <div class="apply">
    <el-table stripe border class="fix-table">
      <el-table-column label="业主"></el-table-column>
      <el-table-column label="装修预算"></el-table-column>
      <el-table-column label="项目类型"></el-table-column>
      <el-table-column label="装修类型"></el-table-column>
      <el-table-column label="装修小区"></el-table-column>
      <el-table-column label="最新量房时间"></el-table-column>
    </el-table>
    <qz-marquee-text :duration="40">
      <el-table :data="tableData" stripe border class="tableData">
        <el-table-column prop="name" label="业主"></el-table-column>
        <el-table-column prop="yusuan" label="装修预算"></el-table-column>
        <el-table-column prop="hxname" label="项目类型"></el-table-column>
        <el-table-column prop="fname" label="装修类型"></el-table-column>
        <el-table-column prop="xiaoqu" label="装修小区"></el-table-column>
        <el-table-column prop="time" label="最新量房时间">
          <template slot-scope="scope">
            <qz-timestamp-format :timestamp="scope.row.time.toString()"></qz-timestamp-format>
          </template>
        </el-table-column>
      </el-table>
    </qz-marquee-text>
  </div>
</template>

<script>
import ApiHome from '@/api/home';
import QzMarqueeText from '@/components/marquee.vue';
import QzTimestampFormat from '@/components/timestamp-format.vue';

export default {
  components: {
    QzMarqueeText,
    QzTimestampFormat,
  },
  async created() {
    const res = await ApiHome.liangFang();
    this.tableData = res.data.data;
  },
  data() {
    return {
      tableData: [],
    };
  },
};
</script>

<style lang="less" scope>
.apply {
  height: 400px;
  overflow: hidden;

  .marquee-text-text {
    animation-direction: reverse;
  }
  .tableData {
    width: 100%;
    .el-table__header-wrapper {
      display: none;
    }
  }
  .el-table__body {
    width: 100% !important;
  }
  .fix-table {
    .is-scrolling-none {
      display: none;
    }
  }
}
</style>
