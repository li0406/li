<template>
  <div class="sales-map-data">
    <el-row>
      <el-col :span="8">
        <span class="title">当月总业绩：</span>
        <span class="num">{{ detail.current_month_achievement }}</span>
      </el-col>
      <el-col :span="16">
        <span class="title">当月MVP：</span>
        <span class="num">{{ detail.current_mvp_sale }}</span>
        <span class="num ml30">{{ detail.current_mvp_money }}</span>
      </el-col>
    </el-row>
    <el-row>
      <el-col v-for="item in detail.sale_depart" :key="item.name" :span="8">
        <span class="title">{{ item.name }}：</span>
        <span class="num">{{ item.value }}</span>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="8">
        <span class="title">已开通城市：</span>
        <span class="num">{{ detail.open_city }}</span>
      </el-col>
      <el-col :span="16">
        <span class="title">有会员城市：</span>
        <span class="num">{{ detail.user_vip_city }}</span>
      </el-col>
      <el-col :span="8">
        <span class="title">总签约装企：</span>
        <span class="num">{{ detail.total_zx_company }}</span>
      </el-col>
      <el-col :span="16">
        <span class="title">当月新签装企：</span>
        <span class="num">{{ detail.current_sign_zx_company }}</span>
      </el-col>
      <el-col :span="8">
        <span class="title">月所需分单：</span>
        <span class="num">{{ detail.need_fendan }}</span>
      </el-col>
      <el-col :span="8">
        <span class="title">实际分单量：</span>
        <span class="num">{{ detail.real_fendan }}</span>
      </el-col>
      <el-col :span="8">
        <span class="title">缺单量：</span>
        <span class="num">{{ detail.require_order }}</span>
      </el-col>
    </el-row>
  </div>
</template>

<script>
export default {
  name: 'SalesMapData',
  props: {
    mapData: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      detail: {}
    }
  },
  watch: {
    mapData(val, oldValue) {
      this.detail = val
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.sales-map-data {
  padding: 100px 30px;
  font-size: 18px;
  .title{
    color: #fff;
  }
  .num{
    color: #c00;
  }
  .ml30{
    margin-left: 30px;
  }
  ::v-deep .el-col{
    margin-bottom: 50px;
  }
}
</style>
