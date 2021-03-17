<template>
  <div class="member-statistics">
    <h2>条件查询</h2>
    <div class="search">
      <div class="clearfix">
        <div class="fl mr20">
          查询日期<br>
          <div class="block mt4">
            <el-date-picker
              v-model="dateTime"
              type="daterange"
              value-format="yyyy-MM-dd"
              format="yyyy-MM-dd"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
            />
          </div>
        </div>
        <div class="fl mr20">
          所属部门<br>
          <div class="block">
            <el-select v-model="top_team_id" clearable placeholder="请选择">
              <el-option
                v-for="item in top_team"
                :key="item.current_id"
                :label="item.current_name"
                :value="item.current_id">
              </el-option>
            </el-select>
          </div>
        </div>
        <div class="fl mr20">
          所属团队<br>
          <div class="block">
            <el-select v-model="team_id" clearable filterable placeholder="请选择">
              <el-option
                v-for="item in team_list"
                :key="item.current_id"
                :label="item.current_name"
                :value="item.current_id">
              </el-option>
            </el-select>
          </div>
        </div>
        <div class="fl mr20">
          个人业绩<br>
          <div class="block">
            <el-select v-model="performanceStart" clearable placeholder="请选择">
              <el-option
                v-for="item in performanceStartList"
                :key="item.id"
                :label="item.name"
                :value="item.id"
              />
            </el-select>
          </div>
        </div>
        <div class="fl mr20 mt4">
          业绩人<br>
          <el-input v-model="saler_name" placeholder="请输入"></el-input>
        </div>
        <div class="fl mr20 mt4">
          <br/>
          <el-button type="primary" icon="el-icon-search" @click="handleSearch" v-preventReClick>查询</el-button>
          <el-button type="success" @click="handleReport" v-loading="exportLoading" v-preventReClick>导出</el-button>
        </div>
      </div>
    </div>
    <div class="qian-main">
      <el-table
        v-loading="loading"
        :data="tableData"
        border
        @sort-change="sortHandle"
      >
        <el-table-column
          align="center"
          label="部门"
        >
          <template slot-scope="scope">
            {{ scope.row.top_team_name ? scope.row.top_team_name : '-' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="业绩人"
          prop="saler_name"
        />
        <el-table-column
          align="center"
          label="姓名"
          prop="saler_nickname"
        />
        <el-table-column
          align="center"
          label="所属团队"
        >
          <template slot-scope="scope">
            {{ scope.row.team_name ? scope.row.team_name : '-' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="团队负责人"
        >
          <template slot-scope="scope">
            {{ scope.row.team_leader_name ? scope.row.team_leader_name : '-' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="个人指标"
        >
          <template slot-scope="scope">
            {{ scope.row.saler_indicators && scope.row.saler_indicators !== 0 ? scope.row.saler_indicators : '-' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          sortable="custom"
          prop="saler_noplat_achievement"
          label="个人业绩"
        />
        <el-table-column
          align="center"
          prop="platmoney_achievement"
          label="平台使用费业绩"
        />
        <el-table-column
          align="center"
          sortable="custom"
          prop="saler_achievement"
          label="个人总业绩"
        />
        <el-table-column label="个人完成率">
          <template slot-scope="scope">
            <div class="percentage">
              <el-progress
                :show-text="false"
                :text-inside="true"
                :stroke-width="18"
                :percentage="scope.row.saler_finish_ratio < 100 ?scope.row.saler_finish_ratio : 100"
                status="success"
              />
              <span>{{ scope.row.saler_finish_ratio }}%</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="team_achievement"
          sortable="custom"
          label="团队业绩"
        >
          <template slot-scope="scope">
            {{ scope.row.team_achievement && scope.row.team_achievement !== 0 ? scope.row.team_achievement : '-' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="操作"
        >
          <template slot-scope="scope">
            <span class="link-blue pointer" @click="handleDetails(scope.row)">查看个人业绩明细</span>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination
        :current-page="currentPage"
        :page-size="20"
        layout="total, prev, pager, next, jumper"
        :total="totals"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </div>
  </div>
</template>

<script>
import { fetchTeamList, fetchPerformList, fetchMemberExport } from '@/api/performanceStatistics'
import { export_json_to_excel } from '@/excel/Export2Excel'
export default {
  name: 'memberStatistics',
  data() {
    return {
      saler_name: '',
      top_team_id: '',
      top_team: [],
      team_id: '',
      team_list: [],
      dateTime: '',
      loading: false,
      tableData: [],
      sort_field: '',
      sort_rule: '',
      currentPage: 1,
      totals: 0,
      exportData: [],
      exportLoading: false,
      performanceStart: '',
      performanceStartList: [
        { id: '1', name: '有个人业绩' },
        { id: '2', name: '无个人业绩' }
      ]
    }
  },
  mounted() {
    this.fetchTeamList(1)
    this.fetchTeamList(2)
  },
  created() {
    this.loading = true
    this.fetchPerformList()
  },
  methods: {
    // 搜索
    handleSearch() {
      this.currentPage = 1
      this.fetchPerformList()
    },
    // 导出
    handleReport() {
      this.exportLoading = true
      const tHeader = [
        '部门',
        '业绩人',
        '姓名',
        '所属团队',
        '团队负责人',
        '个人指标',
        '个人业绩',
        '平台使用费业绩',
        '个人总业绩',
        '个人完成率',
        '团队业绩'
      ]
      // 上面设置Excel的表格第一行的标题
      const filterVal = [
        'top_team_name',
        'saler_name',
        'saler_nickname',
        'team_name',
        'team_leader_name',
        'saler_indicators',
        'saler_noplat_achievement',
        'platmoney_achievement',
        'saler_achievement',
        'saler_finish_ratio',
        'team_achievement'
      ]
      this.fetchMemberExport('export', () => {
        const list = this.exportData // 把data里的exportData存到list
        list.forEach((index, item) => {
          if (index.saler_finish_ratio !== 0) {
            index.saler_finish_ratio = index.saler_finish_ratio + '%'
          }
        })
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '个人业绩统计')
        this.exportLoading = false
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]))
    },
    fetchMemberExport(val, cb) {
      fetchMemberExport({
        date_begin: this.dateTime ? this.dateTime[0] : '',
        date_end: this.dateTime ? this.dateTime[1] : '',
        top_team_id: this.top_team_id,
        team_id: this.team_id,
        saler_name: this.saler_name,
        sort_field: this.sort_field,
        sort_rule: this.sort_rule,
        has_achievement: this.performanceStart
      }).then(res => {
        if (res.status === 200 & res.data.error_code === 0) {
          if (res.data.data.list.length > 0) {
            this.exportData = res.data.data.list
            cb & cb.call()
          } else {
            this.$message.warning('未查询到符合要求的数据')
            this.exportLoading = false
          }
        } else {
          this.$message.error(res.data.error_msg)
          this.exportData = []
          this.exportLoading = false
        }
      })
    },
    fetchTeamList(select_type) {
      fetchTeamList({
        select_type: select_type
      }).then(res => {
        if (res.status === 200 & res.data.error_code === 0) {
          if (select_type === 1) {
            this.top_team = res.data.data.list
          } else {
            this.team_list = res.data.data.list
          }
        }
      })
    },
    // 列表数据
    fetchPerformList() {
      const that = this
      fetchPerformList({
        date_begin: that.dateTime ? that.dateTime[0] : '',
        date_end: that.dateTime ? that.dateTime[1] : '',
        top_team_id: that.top_team_id,
        team_id: that.team_id,
        saler_name: that.saler_name,
        page: that.currentPage,
        sort_field: that.sort_field,
        sort_rule: that.sort_rule,
        has_achievement: that.performanceStart
      }).then(res => {
        that.loading = false
        if (res.status === 200 && res.data.error_code === 0) {
          that.tableData = res.data.data.list
          that.totals = res.data.data.page.total_number
          that.dateTime = []
          that.dateTime[0] = res.data.data.options.date_begin
          that.dateTime[1] = res.data.data.options.date_end
        } else {
          that.tableData = []
          that.$message.error(res.data.error_msg)
        }
      })
    },
    // 查看个人业绩明细
    handleDetails(obj) {
      this.$router.push({
        path: 'performanceDetails',
        query: {
          saler_id: obj.saler_id,
          month_date_begin: this.dateTime ? this.dateTime[0] : '',
          month_date_end: this.dateTime ? this.dateTime[1] : ''
        }
      })
    },
    // 排序
    sortHandle(column) {
      if (column.prop == null) {
        this.sort_field = ''
        this.sort_rule = ''
      } else {
        this.sort_field = column.prop
        this.sort_rule = column.order === 'ascending' ? 'ASC' : 'DESC'
      }
      this.fetchPerformList()
    },
    // 分页处理
    handleSizeChange() {
      this.currentPage = 1
      this.fetchPerformList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.fetchPerformList()
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
  .member-statistics {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
    .mt4{
      margin-top:4px;
    }
    .mr20{
      margin-right: 20px;
    }
    .el-pagination{
      text-align: center;
      margin-top: 20px;
    }
    .finish-ratio{
      background: green;
      color: #fff;
      height: 23px;
      display: inline-block;
    }
    .percentage{
      position: relative;
      span{
        position: absolute;
        right: 0;
        top: 0;
        color: #000;
        margin: 0 5px;
        line-height: 18px;
      }
    }
    .el-range-separator{
      padding: 0;
    }
  }
  .member-statistics h2{
    font-size: 18px;
    margin-top: 0;
    font-weight: normal;
  }
  .member-statistics .search {
    line-height: 36px;
    background: #fff;
    padding: 20px;
    border-top: 3px solid #999;
    box-sizing: border-box;
  }
  .member-statistics .qian-main {
    margin-top: 20px;
    padding: 20px;
    border-top: 3px solid #999;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    background-color: #fff;
  }
</style>
