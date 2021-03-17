<template>
  <div class="member-statistics">
    <h2>条件查询</h2>
    <div class="search">
      <div class="clearfix">
        <div class="fl mr20">
          所属城市<br>
          <el-select v-model="city_id" filterable placeholder="请选择">
            <el-option
              v-for="item in cityOptions"
              :key="item.value"
              :label="item.value"
              :value="item.cid"
            />
          </el-select>
        </div>
        <div class="fl mr20">
          收款日期<br>
          <div class="block mt4">
            <el-date-picker
              v-model="payment_date_begin"
              type="date"
              value-format="yyyy-MM-dd"
              format="yyyy-MM-dd"
              placeholder="开始时间"
            />
            <el-date-picker
              v-model="payment_date_end"
              type="date"
              value-format="yyyy-MM-dd"
              format="yyyy-MM-dd"
              clearable
              placeholder="结束时间"
            />
          </div>
        </div>
        <div class="fl mr20">
          收/退款类型<br>
          <div class="block">
            <el-select v-model="payment_type" clearable placeholder="请选择">
              <el-option
                v-for="item in paymentType"
                :key="item.id"
                :label="item.name"
                :value="item.id"
              />
            </el-select>
          </div>
        </div>

        <div class="fl mr20 mt4 get-money">
          收/退款金额<br>
          <el-input v-model="payment_money_min" type="number" placeholder="万" />
          <span style="float:left;margin-right: 15px;">至</span>
          <el-input v-model="payment_money_max" type="number" placeholder="万" />
        </div>
        <div class="fl mr20 m-right">
          收/退款方名称<br>
          <div class="block">
            <el-select v-model="payee_type" clearable filterable placeholder="请选择">
              <el-option
                v-for="item in payeeList"
                :key="item.id"
                :label="item.name"
                :value="item.id"
              />
            </el-select>
          </div>
        </div>
        <div class="fl mr20">
          所属部门<br>
          <div class="block">
            <el-select v-model="top_team_id" clearable filterable placeholder="请选择">
              <el-option
                v-for="item in teamList"
                :key="item.current_id"
                :label="item.current_name"
                :value="item.current_id"
              />
            </el-select>
          </div>
        </div>
        <div class="fl mr20">
          业绩人<br>
          <div class="block">
            <el-select v-model="saler_id" clearable filterable placeholder="请选择">
              <el-option
                v-for="item in salerList"
                :key="item.saler_id"
                :label="item.full_name"
                :value="item.saler_id"
              />
            </el-select>
          </div>
        </div>
        <div class="fl mr20 mt4 btn">
          <br>
          <el-button v-preventReClick type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
          <el-button v-loading="exportLoading" v-preventReClick type="success" @click="handleReport">导出</el-button>
        </div>
      </div>
    </div>
    <div class="qian-main">
      <el-table
        v-loading="loading"
        :data="tableData"
        border
        :summary-method="getSummaries"
        show-summary
      >
        <el-table-column
          align="center"
          label="汇/退款日期"
          prop="payment_date"
        />
        <el-table-column
          align="center"
          label="城市"
          prop="city_name"
        />
        <el-table-column
          align="center"
          label="公司名称"
          prop="company_name"
        />
        <el-table-column
          align="center"
          label="收/退款金额"
        >
          <template slot-scope="scope">
            <span :class="scope.row.payment_total_money < 0 ? 'red' : 'link-blue'">{{ scope.row.payment_total_money }}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="业绩金额"
        >
          <template slot-scope="scope">
            <span :class="scope.row.payment_money < 0 ? 'red' : 'link-blue'">{{ scope.row.payment_money }}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="平台使用费"
        >
          <template slot-scope="scope">
            <div v-if="scope.row.cooperation_type === 1 || scope.row.cooperation_type === 2 || scope.row.cooperation_type === 3 || scope.row.cooperation_type === 6 || scope.row.cooperation_type === 7|| scope.row.cooperation_type === 12|| scope.row.cooperation_type === 14|| scope.row.cooperation_type === 15">{{ scope.row.platform_money }}</div>
            <div v-else>-</div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="会员费"
        >
          <template slot-scope="scope">
            <div>{{ scope.row.round_order_money && (scope.row.cooperation_type === 1 || scope.row.cooperation_type === 2 || scope.row.cooperation_type === 3 || scope.row.cooperation_type === 7 || scope.row.cooperation_type === 14) ? scope.row.round_order_money : 0 }}</div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="轮单费"
        >
          <template slot-scope="scope">
            <div>{{ scope.row.round_order_money && (scope.row.cooperation_type === 6 || scope.row.cooperation_type === 15) ? scope.row.round_order_money : 0 }}</div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="保证金"
        >
          <template slot-scope="scope">
            <div>{{ scope.row.deposit_money && (scope.row.cooperation_type === 6 || scope.row.cooperation_type === 15) ? scope.row.deposit_money : 0 }}</div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="其他金额"
        >
          <template slot-scope="scope">
            <div>{{ scope.row.other_money ? scope.row.other_money : 0 }}</div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="报备人"
          prop="creator_name"
        />
        <el-table-column
          align="center"
          label="业绩人"
        >
          <template slot-scope="scope">
            <div v-for="(item,index) in scope.row.person_list" :key="index">{{ item.saler_name }}({{ item.share_ratio_text }})</div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="合作类型"
          prop="cooperation_type_name"
        />
        <el-table-column
          align="center"
          label="收/退款类型"
          prop="payment_type_name"
        >
          <template slot-scope="scope">
            <span :class="scope.row.cooperation_type != 11 ? 'link-blue' : 'red'">{{ scope.row.payment_type_name }}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="收/退款方名称"
          width="210px"
        >
          <template slot-scope="scope">
            <div v-for="(item) in scope.row.payee_list" :key="item.payee_px">{{ item.payee_type_name }}({{ item.payee_money }})</div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="收/汇款方名称"
          prop="payment_uname"
        />
        <el-table-column
          align="center"
          label="凭证"
        >
          <template slot-scope="scope">
            <span class="link-blue pointer" @click="handleDetails(scope.row)">查看({{ scope.row.auth_imgs.length }})</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="其他说明"
        >
          <template slot-scope="scope">
            <div v-if="scope.row.round_order_money > 0">{{ scope.row.cooperation_type === 6 || scope.row.cooperation_type === 15 ? '轮单费':'会员费' }}{{ scope.row.cooperation_type === 8 ? '抵扣':'' }}：{{ scope.row.round_order_money }}</div>
            <div v-if="scope.row.deposit_money > 0">保证金{{ scope.row.cooperation_type === 8 ? '抵扣':scope.row.cooperation_type === 10 || scope.row.cooperation_type === 13 ? '余额':'' }}：{{ scope.row.deposit_money }}</div>
            <div v-if="scope.row.other_money > 0">其他金额：{{ scope.row.other_money }}</div>
            <div v-if="scope.row.deposit_to_round_money > 0">保证金抵扣轮单费：{{ scope.row.deposit_to_round_money }}</div>
            <div v-if="scope.row.deposit_to_platform_money > 0">保证金抵扣平台使用费：{{ scope.row.deposit_to_platform_money }}</div>
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
    <div id="app">
      <div v-if="centerDialogVisible" class="imgMask" @click.stop="centerDialogVisible=!centerDialogVisible">
        <i class="iconfont el-icon-arrow-left prev" @click.stop="prev" />
        <div class="showImg">
          <img class="bigImg" :src="url[num].img_full">
        </div>
        <i class="iconfont el-icon-arrow-right next" @click.stop="next" />
      </div>
    </div>
  </div>
</template>

<script>
import { fetchPersonList, fetchPerformDetailList, fetchPerformExport, fetchSelect, fetchPayeelist, fetchPaymentTotal } from '@/api/performanceStatistics'
import { export_json_to_excel } from '@/excel/Export2Excel'
import { fetchCityList } from '@/api/common'
export default {
  name: 'PerformanceDetails',
  data() {
    return {
      cityOptions: [{
        cid: '0', cname: '请选择', value: '请选择', true_cname: '请选择', city_name: '请选择'
      }],
      city_id: '0',
      saler_id: '',
      top_team_id: '',
      payee_type: '',
      payment_type: 0,
      paymentType: [], // 收款类型
      salerList: [],
      teamList: [],
      payeeList: [],
      payment_date_begin: '',
      payment_date_end: '',
      payment_money_min: '',
      payment_money_max: '',
      loading: false,
      exportLoading: false,
      tableData: [],
      exportData: [],
      currentPage: 1,
      totals: 0,
      centerDialogVisible: false,
      num: 0,
      url: [],
      statusVal: 0,
      skTotal: 0,
      tkTotal: 0,
      yjTotal: 0
    }
  },
  mounted() {
    this.fetchPersonList()
    this.fetchSelect()
    this.fetchPayeelist()
  },
  created() {
    if (this.$route.query.saler_id) {
      this.saler_id = Number(this.$route.query.saler_id)
      this.payment_date_begin = this.$route.query.month_date_begin
      this.payment_date_end = this.$route.query.month_date_end
    } else {
      this.statusVal = 1
      this.saler_id = ''
    }
    this.loading = true
    this.getCityList()
    this.fetchPerformDetailList()
  },
  methods: {
    prev() {
      const imgNum = this.url.length
      if (this.num === 0) {
        this.num = imgNum
      }
      this.num--
    },
    next() {
      const imgNum = this.url.length
      if (this.num === imgNum - 1) {
        this.num = -1
      }
      this.num++
    },
    getCityList() {
      fetchCityList().then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.cityOptions = this.cityOptions.concat(res.data.data[0])
        }
      })
    },
    // 搜索
    handleSearch() {
      if (this.payment_date_begin === '' || this.payment_date_begin === null) {
        this.$message.error('请选择收款开始日期')
        return
      }
      if (this.payment_date_end === '' || this.payment_date_end === null) {
        this.$message.error('请选择收款结束日期')
        return
      }
      if (new Date(this.payment_date_begin).getTime() > new Date(this.payment_date_end).getTime()) {
        this.$message.error('结束日期不得小于开始日期')
        return
      }
      this.currentPage = 1
      this.statusVal = 1
      this.fetchPerformDetailList()
    },
    // 导出
    handleReport() {
      this.exportLoading = true
      const tHeader = [
        '汇款日期',
        '城市',
        '公司名称',
        '收款金额',
        '业绩金额',
        '平台使用费',
        '会员费',
        '轮单费',
        '保证金',
        '其他金额',
        '报备人',
        '业绩人',
        '合作类型',
        '收款类型',
        '收款方式',
        '汇款方名称',
        '其他说明'
      ]
      // 上面设置Excel的表格第一行的标题
      const filterVal = [
        'payment_date',
        'city_name',
        'company_name',
        'payment_total_money',
        'performanceMoney',
        'platform_money',
        'round_order_money1', // 会员费
        'round_order_money2', // 轮单费
        'deposit_money', // 保证金
        'other_money', // 其他金额
        'creator_name',
        'xperson_list',
        'cooperation_type_name',
        'payment_type_name',
        'payyeeList',
        'payment_uname',
        'instruction'
      ]
      this.fetchPerformExport('export', () => {
        const list = this.exportData // 把data里的exportData存到list
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '业绩明细统计')
        this.exportLoading = false
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]))
    },
    fetchPerformExport(val, cb) {
      fetchPerformExport({
        city_id: this.city_id,
        payment_date_begin: this.payment_date_begin,
        payment_date_end: this.payment_date_end,
        payment_type: this.payment_type,
        saler_id: this.saler_id,
        payee_type: this.payee_type,
        top_team_id: this.top_team_id,
        payment_money_min: this.payment_money_min,
        payment_money_max: this.payment_money_max
      }).then(res => {
        if (res.status === 200 & res.data.error_code === 0) {
          if (res.data.data.list.length > 0) {
            this.exportData = []
            res.data.data.list.forEach((item, index) => {
              item.performanceMoney = item.payment_money
              if (item.cooperation_type === 1 || item.cooperation_type === 2 || item.cooperation_type === 3 || item.cooperation_type === 7 || item.cooperation_type === 14) {
                item.round_order_money1 = item.round_order_money
              } else {
                item.round_order_money1 = 0
              }
              if (item.cooperation_type === 6 || item.cooperation_type === 15) {
                item.round_order_money2 = item.round_order_money
              } else {
                item.round_order_money2 = 0
              }
              /* if (item.cooperation_type !== 6) {
                item.deposit_money = 0
              } */
              const list = item.person_list.map((it, index) => {
                return it.saler_name + it.share_ratio_text
              })
              item.xperson_list = list.toString('')
              const payeeList = item.payee_list.map((ip, index) => {
                return `${ip.payee_type_name}(${ip.payee_money})`
              })
              item.payyeeList = payeeList.toString('')
              item.instruction = ''
              if (item.round_order_money > 0) {
                let name = ''
                if (item.cooperation_type === 8) {
                  name = '轮单费抵扣：'
                } else if (item.cooperation_type === 6 || item.cooperation_type === 15) {
                  name = '轮单费：'
                } else {
                  name = '会员费：'
                }
                item.instruction += name + item.round_order_money
              }
              if (item.deposit_money > 0) {
                let name = ''
                if (item.cooperation_type === 8) {
                  name = '保证金抵扣：'
                } else if (item.cooperation_type === 10 || item.cooperation_type === 13) {
                  name = '保证金余额：'
                } else {
                  name = '保证金：'
                }
                item.instruction += name + item.deposit_money
              }
              if (item.other_money > 0) {
                item.instruction += '其他金额：' + item.other_money
              }
              if (item.deposit_to_round_money > 0) {
                item.instruction += '保证金抵扣轮单费：' + item.deposit_to_round_money
              }
              if (item.deposit_to_platform_money > 0) {
                item.instruction += '保证金抵扣平台使用费：' + item.deposit_to_platform_money
              }
              this.exportData.push(item)
            })
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
    fetchPersonList() {
      fetchPersonList({
        id: '',
        saler_ids: '',
        saler_name: ''
      }).then(res => {
        if (res.status === 200 & res.data.error_code === 0) {
          this.salerList = res.data.data.list
        }
      })
    },
    // 团队管理
    fetchSelect() {
      fetchSelect({
        actfrom: 'pc'
      }).then(res => {
        if (res.status === 200 & res.data.error_code === 0) {
          this.teamList = res.data.data.list
        }
      })
    },
    // 收款方列表
    fetchPayeelist() {
      fetchPayeelist({
        select_type: 1
      }).then(res => {
        if (res.status === 200 & res.data.error_code === 0) {
          this.payeeList = [{ id: '', name: '请选择', paixu: '' }, ...res.data.data.payment_payee_list]
          this.paymentType = [{ id: 0, name: '请选择' }, ...res.data.data.payment_type_list]
        }
      })
    },
    // 列表数据
    fetchPerformDetailList() {
      this.loading = true
      const obj = {}
      obj.city_id = this.city_id
      obj.payment_date_begin = this.payment_date_begin
      obj.payment_date_end = this.payment_date_end
      obj.payment_type = this.payment_type
      obj.saler_id = this.saler_id
      obj.payee_type = this.payee_type
      obj.top_team_id = this.top_team_id
      obj.payment_money_min = this.payment_money_min
      obj.payment_money_max = this.payment_money_max
      obj.page = this.currentPage
      fetchPerformDetailList(obj).then(res => {
        this.loading = false
        if (res.status === 200 & res.data.error_code === 0) {
          this.tableData = res.data.data.list
          this.totals = res.data.data.page.total_number
          if (this.statusVal === 0) {
            this.payment_date_begin = this.$route.query.month_date_begin
          } else {
            this.payment_date_begin = res.data.data.options.payment_date_begin
          }
          if (this.statusVal === 0) {
            this.payment_date_end = this.$route.query.month_date_end
          } else {
            this.payment_date_end = res.data.data.options.payment_date_end
          }
        } else {
          this.tableData = []
          this.$message.error(res.data.error_msg)
        }
      }).catch(res => {
        this.tableData = []
        this.loading = false
        this.$message.error(res.data.error_msg)
      })
      // 获取合计
      fetchPaymentTotal(obj).then(res => {
        if (res.status === 200 & res.data.error_code === 0) {
          this.skTotal = res.data.data.info.payment_total_money
          this.tkTotal = res.data.data.info.refund_money
          this.yjTotal = res.data.data.info.payment_money
        }
      })
    },

    // 查看
    handleDetails(obj) {
      this.num = 0
      this.centerDialogVisible = true
      this.url = obj.auth_imgs
    },
    getSummaries(param) {
      const { columns } = param
      const sums = []
      columns.forEach((column, index) => {
        if (index === 2) {
          sums[index] = '合计'
        } else if (index === 3) {
          sums[index] = this.skTotal + '(收款)；' + this.tkTotal + '(退款)'
        } else if (index === 4) {
          sums[index] = this.yjTotal + '(业绩)'
        }
      })
      return sums
    },
    // 分页处理
    handleSizeChange() {
      this.currentPage = 1
      this.fetchPerformDetailList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.fetchPerformDetailList()
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
  .member-statistics {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
    h2 {
      font-size: 18px;
      margin-top: 0;
      font-weight: normal;
    }
    .search {
      line-height: 36px;
      background: #fff;
      padding: 20px;
      border-top: 3px solid #999;
      box-sizing: border-box;
    }
    .qian-main {
      margin-top: 20px;
      padding: 20px;
      border-top: 3px solid #999;
      -webkit-box-sizing: border-box;
      box-sizing: border-box;
      background-color: #fff;
    }
    .mt4 {
      margin-top: 4px;
    }
    .mr20 {
      margin-right: 20px;
      margin-bottom: 20px;
    }
    .el-pagination {
      text-align: center;
      margin-top: 20px;
    }
    .get-money .el-input {
      width: 70px;
      float: left;
      margin-right: 15px;
    }
    .el-dialog--center .el-dialog__body {
      text-align: center;
    }
    .imgMask {
      position: fixed;
      height: 100%;
      width: 100%;
      top: 0;
      left: 0;
      bottom: 0;
      right: 0;
      z-index: 99999;
      background: rgba(0, 0, 0, .6);
    }
    .showImg {
      height: 550px;
      width: 1300px;
      position: absolute;
      left: 50%;
      top: 50%;
      overflow: hidden;
      transform: translate(-50%, -50%);
    }
    .bigImg {
        width: 100%;
        height: 100%;
        object-fit: contain;
      }
    .prev {
      position: absolute;
      top: 50%;
      left: 50px;
      font-size: 46px;
      cursor: pointer;
      color: #fff;
      transform: translate(10px, -50%);
    }
    .next {
      font-size: 46px;
      cursor: pointer;
      transform: translate(10px, -50%);
      position: absolute;
      top: 50%;
      color: #fff;
      right: 50px;
    }
    .btn{
      margin-right: 66px;
    }
    .m-right{
      margin-right: 80px;
    }
  }
</style>
