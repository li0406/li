<template>
  <div class="city-vip-count">
    <h3>商务外销城市会员统计</h3>
    <div class="main">
      <el-form :inline="true" class="demo-form-inline">
        <el-form-item label="部门：">
          <el-select v-model="dept" clearable placeholder="请选择部门">
            <el-option label="商务" value="1" />
            <el-option label="外销" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="等级：">
          <el-select v-model="level" clearable placeholder="请选择等级">
            <el-option label="≥20" value="20" />
            <el-option label="≥10" value="10" />
            <el-option label="≥8" value="8" />
            <el-option label="≥6" value="6" />
            <el-option label="≥4" value="4" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" icon="el-icon-search" @click="getDataList">查询</el-button>
          <el-button :loading="exportLoading" type="success" icon="el-icon-download" @click="handleExport">导出Excel</el-button>
        </el-form-item>
      </el-form>
    </div>
    <div v-loading="loading" class="main">
      <el-row class="table-top">
        <el-col :span="6" class="title">
          城市会员统计列表
        </el-col>
        <el-col class="top-right">
          <div class="item">
            <span class="ml20">城市数量：</span><span class="num">{{ data.city_num || 0 }}</span>
            <span class="ml20">会员数：</span><span class="num">{{ data.vip_sum_num || 0 }}</span>
            <span class="ml20">会员数倍数：</span><span class="num">{{ data.vip_multiple_count || 0 }}</span>
            <span class="ml20">1.0会员数：</span><span class="num">{{ data.vipone_num || 0 }}</span>
            <span class="ml20">1.0会员倍数：</span><span class="num">{{ data.vipone_multiple_count || 0 }}</span>
            <span class="ml20">2.0 有效会员数：</span><span class="num">{{ data.viptwo_multiple_count || 0 }}</span>
            <span class="ml20">2.0 无效会员数：</span><span class="num">{{ data.viptwo_multiple_count_invalid || 0 }}</span>
            <span class="ml20">老签返数：</span><span class="num">{{ data.old_vip_count || 0 }}</span>
            <span class="ml20">1.0标杆会员数：</span><span class="num">{{ data.vipthree_num || 0 }}</span>
            <span class="ml20">2.0标杆有效会员数：</span><span class="num">{{ data.vipfour_num || 0 }}</span>
          </div>
          <div class="item">
            <span class="ml20">2.0标杆无效会员数：</span><span class="num">{{ data.vipfour_multiple_count_invalid || 0 }}</span>
          </div>
        </el-col>
      </el-row>
      <el-row v-if="list.length > 0" class="table">
        <el-col v-for="item in list" :key="item.cs" :span="2">
          <span>{{ item.cname }}</span>
          <span class="red">{{ item.vip_multiple_count }}</span>
        </el-col>
      </el-row>
      <p v-else class="text-center"> 暂无数据</p>
    </div>
  </div>
</template>

<script>
import { fetchGetCityVipCount, fetchGetCityVipCountExport } from '@/api/orderManage'
import { export_json_to_excel } from '@/excel/Export2Excel'
export default {
  name: 'Cityvipcount',
  components: {},
  data() {
    return {
      loading: false,
      data: {},
      dept: '',
      level: '',
      list: [],
      exportLoading: false,
      export: 1,
      exportData: []
    }
  },
  computed: {},
  watch: {},
  created() {
    this.getDataList()
  }, // 生命周期 - 创建完成（可以访问当前this实例）
  methods: { // 方法集合
    getDataList() {
      const that = this
      that.loading = true
      const query = {
        dept: that.dept,
        level: that.level
      }
      fetchGetCityVipCount(query).then(res => {
        that.loading = false
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          that.data = res.data.data
          that.list = res.data.data.list
        } else {
          that.$message.warning(res.data.error_msg)
        }
      }).catch(res => {
        that.loading = false
      })
    },
    handleExport() {
      this.export = 1
      this.exportLoading = true
      const tHeader = [
        '城市',
        '会员数'
      ]
      // 上面设置Excel的表格第一行的标题
      const filterVal = [
        'cname',
        'vip_multiple_count'
      ]
      this.getDataExcel('export', () => {
        const list = this.exportData // 把data里的exportData存到list
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '城市会员统计')
        this.exportLoading = false
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]))
    },
    getDataExcel(val, cb) {
      const that = this
      const query = {
        dept: that.dept,
        level: that.level
      }
      fetchGetCityVipCountExport(query).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          that.exportLoading = false
          if (res.data.data.list.length > 0) {
            that.exportData = []
            that.exportData = res.data.data.list
            cb & cb.call()
          } else {
            that.$message.warning('未查询到符合要求的数据')
          }
        } else {
          that.$message.error(res.data.error_msg)
          that.exportData = []
          that.exportLoading = false
        }
      }).catch(res => {
        that.exportLoading = false
        that.$message.error(res.data.error_msg)
      })
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.city-vip-count {
  padding: 0 20px;
  h3 {
    font-size: 18px;
    font-weight: normal;
    line-height: 30px;
  }
  .main {
    background: #fff;
    border-top: 3px solid #ccc;
    padding: 20px;
    margin-bottom: 30px;
    .el-select{
      margin-top: 0;
    }
    .top-right{
      .item{
        line-height: 20px;
        margin: 10px 0;
        .ml20{
          color: #333;
        }
        .num{
          margin-right: 20px;
          color: #0099ff;
        }
      }
    }
    .table{
      border-top: 1px solid #ccc;
      border-left: 1px solid #ccc;
      .el-col{
        display: flex;
        height: 44px;
        line-height: 1.5;
        font-size: 14px;
        color: #333;
        text-align: center;
        border-right: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        justify-content: center;
        align-items: center;
        .red{
          margin-left: 10px;
        }
      }
    }
  }
}
</style>
