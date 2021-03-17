<template>
  <div class="repair-order">
    <div class="search">
      <div class="clearfix">
        <div class="yixiang fl mr20">
          订单查询：<br>
          <el-input v-model="orderKey" placeholder="订单号、小区名称"></el-input>
        </div>
        <div class="fl mr20">
          所属区域：<br>
          <el-select v-model="cityVal" filterable @change="getAreaList" placeholder="请选择">
            <el-option
              v-for="item in cityOptions"
              :key="item.value"
              :label="item.value"
              :value="item.cid"
            >
            </el-option>
          </el-select>
        </div>
        <div class="fl mr20">
           <br>
          <div class="block">
            <el-select v-model="areaVal" placeholder="请选择">
              <el-option
                v-for="item in areaOptions"
                :key="item.area_id"
                :label="item.value"
                :value="item.area_id">
              </el-option>
            </el-select>
          </div>
        </div>
        <div class="fl mr20">
          订单状态: <br>
          <div class="block">
            <el-select v-model="orderStatusVal" placeholder="请选择">
              <el-option
                v-for="item in orderStatusOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </div>
        </div>
        <div class="fl mr20">
          公装/家装: <br>
          <div class="block">
            <el-select v-model="decortTypeVal" placeholder="请选择">
              <el-option
                v-for="item in decortTypeOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </div>
        </div>
        <div class="fl mr20">
          推送时间开始-结束: <br>
          <div class="block">
            <el-date-picker
              v-model="start_time"
              type="date"
              placeholder="开始时间">
            </el-date-picker>
            <el-date-picker
              v-model="end_time"
              type="date"
              placeholder="结束时间">
            </el-date-picker>
          </div>
        </div>
      </div>
      <div style="text-align: right">
        <br>
        <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
        <el-button type="primary" icon="el-icon-search" @click="handleReset">重置</el-button>
      </div>
    </div>
    <div class="qian-main">
      <div style="margin-bottom: 20px;"><el-button type="primary" @click="handleRepairOrder('all')">批量不补单</el-button></div>
      <el-table
        v-loading="loading"
        :data="tableData"
        border
        @selection-change="handleSelectionChange"
      >
        <el-table-column
          align="center"
          type="selection"
        >
        </el-table-column>
        <el-table-column
          align="center"
          prop="time_real"
          label="发布日期"
        />
        <el-table-column
          align="center"
          label="小区名称"
        >
          <template slot-scope="scope">
            {{ scope.row.xiaoqu }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="city"
          label="城市区县"
        />
        <el-table-column
          align="center"
          prop="name"
          label="业主姓名"
        />
        <el-table-column
          align="center"
          label="公装/家装"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.lx) === 1 ? '家装' : '公装' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="yusuan"
          label="预算"
        />
        <el-table-column
          align="center"
          prop="mianji"
          label="面积m²"
        />
        <el-table-column
          align="center"
          label="量房时间"
        >
          <template slot-scope="scope">
            {{ scope.row.lftime ? scope.row.lftime : '----' }}
          </template>
        </el-table-column>

        <el-table-column
          align="center"
          label="订单状态"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.type_fw) === 1? '分单' : '赠单' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="已分配名额"
        >
          <template slot-scope="scope">
            {{ scope.row.fen_com ? scope.row.fen_com : '----' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="剩余名额"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.allot_num) - parseInt(scope.row.fen_com) }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="订单推送时间"
        >
          <template slot-scope="scope">
            {{ scope.row.push_time }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="操作"
        >
          <template slot-scope="scope">
            <span class="link-blue pointer" @click="handleRepair(scope.row)">补单</span>
            <span class="link-blue pointer" @click="handleRepairOrder(scope.row)">不补</span>
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
    <repair-order-dia :showRepairDia="isShowDia" :order-id="tempOrderInfo.id" @closeDia="closeRepairDia"></repair-order-dia>
  </div>
</template>

<script>
  import { fetchCityList, fetchAreaList } from '@/api/common'
  import repairOrderDia from './components/repairOrderDia'
  import { fetchRepairOrder, fetchRemoveRepairOrder } from '@/api/orderManage'
  import { parseTime } from '@/utils/index'
  export default {
    name: 'repairOrder',
    components: {
      repairOrderDia
    },
    data() {
      return {
        orderKey: '',
        cityVal: '0',
        cityOptions: [{
          cid: '0', cname: "请选择", value: "请选择", true_cname: "请选择", city_name: "请选择"
        }],
        areaVal: '0',
        areaOptions: [{
          area_id: '0', area: "请选择", value: "请选择"
        }],
        orderStatusVal: '0',
        orderStatusOptions: [{
          value: '0',
          label: '请选择'
        }, {
          value: '1',
          label: '分单'
        }, {
          value: '2',
          label: '赠单'
        }],
        decortVal: '0',
        decortOptions: [{
          value: '0',
          label: '请选择'
        }, {
          value: '1',
          label: '半包'
        }, {
          value: '2',
          label: '全包'
        }],
        decortTypeVal: '0',
        decortTypeOptions: [{
          value: '0',
          label: '请选择'
        }, {
          value: '1',
          label: '家装'
        }, {
          value: '2',
          label: '公装'
        }],
        accessGrantVal: '0',
        accessGrantOptions: [{
          value: '0',
          label: '请选择'
        }, {
          value: '1',
          label: '是'
        }, {
          value: '2',
          label: '否'
        }],
        start_time: '',
        end_time: '',
        loading: false,
        tableData: [{
          qian_money: '123',
          companyname: '赣州新居缘装饰'
        }],
        currentPage: 1,
        totals: 0,
        isShowDia: false,
        multipleSelection: [],
        tempOrderInfo: {}
      }
    },
    created() {
      this.getCityList()
      this.getMemberCompanys()
    },
    methods: {
      getCityList() {
        fetchCityList().then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.cityOptions = this.cityOptions.concat(res.data.data[0])
          }
        })
      },
      getAreaList() {
        fetchAreaList({
          cid: this.cityVal
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.areaOptions = [{
              area_id: 0, area: "请选择", value: "请选择"
            }]
            this.areaVal = '0'
            this.areaOptions = this.areaOptions.concat(res.data.data[0])
          }
        })
      },
      getMemberCompanys() {
        let start_time = ''
        let end_time = ''
        if(!this.start_time && this.end_time) {
          this.$message.error('请选择开始时间')
          return
        }
        if(this.start_time && !this.end_time) {
          this.$message.error('请选择结束时间')
          return
        }
        if(new Date(this.start_time).getTime() > new Date(this.end_time).getTime()) {
          this.$message.error('结束时间不能大于开始时间')
          return
        }
        if(this.start_time && this.end_time) {
          start_time = new Date(this.start_time).getFullYear()+'-'+(new Date(this.start_time).getMonth()+1)+'-'+new Date(this.start_time).getDate()
          end_time = new Date(this.end_time).getFullYear()+'-'+(new Date(this.end_time).getMonth()+1)+'-'+new Date(this.end_time).getDate()
        }
        this.tableData = []
        this.loading = true
        fetchRepairOrder({
          condition: this.orderKey,
          cs: this.cityVal,
          qx: this.areaVal,
          status: this.orderStatusVal,
          lx: this.decortTypeVal,
          start: start_time,
          end: end_time,
          page: this.currentPage
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.tableData = res.data.data.list
            this.tableData.forEach(item => {
              item.time_real = parseTime(item.time_real)
              if(item.lf_time) {
                item.lf_time = parseTime(item.lf_time)
              }
              item.push_time = parseTime(item.push_time)
            })
            this.totals = res.data.data.page.total_number
          }else{
            this.$message.error(res.data.error_msg)
          }
          this.loading = false
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
          this.loading = false
        })
      },
      handleRepairOrder(obj) {
        if(obj === 'all') {
          if(this.multipleSelection && this.multipleSelection.length <=0) {
            this.$message.error('请先勾选订单')
            return
          }
          this.$confirm('确定执行批量不补单操作?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            this.handleDelOrder('all')
          }).catch(() => {
            this.$message({
              type: 'info',
              message: '已取消批量不补单'
            })
          })
        }else{
          this.handleDelOrder(obj)
        }
      },
      handleDelOrder(obj) {
        let order = []
        if(obj === 'all') {
          this.multipleSelection.forEach(item => {
            order.push(item.id)
          })
        }else{
          order.push(obj.id)
        }
        fetchRemoveRepairOrder({
          order: order
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              type: 'success',
              message: '操作成功!'
            })
            this.getMemberCompanys()
          }else{
            this.$message.error('处理成功')
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
        })

      },
      handleSelectionChange(val) {
        this.multipleSelection = val
      },
      handleSearch() {
        this.getMemberCompanys()
      },
      handleReset() {
        this.orderKey = ''
        this.cityVal = '0'
        this.areaVal = '0'
        this.orderStatusVal = '0'
        this.decortTypeVal = '0'
        this.start_time = ''
        this.end_time = ''
      },
      // 分页处理
      handleSizeChange() {
        this.currentPage = 1
        this.getMemberCompanys()
      },
      handleCurrentChange(val) {
        this.currentPage = val
        this.getMemberCompanys()
      },
      handleRepair(obj) {
        this.isShowDia = true
        this.tempOrderInfo = obj
      },
      closeRepairDia(tag) {
        this.isShowDia = false
        if(tag === 'update') {
          this.getMemberCompanys()
        }
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
  .repair-order {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
    .el-date-editor{
      .el-range-separator{
        padding: 0;
      }
    }
    .search {
      background: #fff;
      padding: 20px;
      border-top: 3px solid #999;
      box-sizing: border-box;
    }
    .qian-main {
      margin-top: 20px;
      padding: 20px;
      border-top: 3px solid #999;
      box-sizing: border-box;
      background-color: #fff;
    }
    .el-pagination{
      text-align: center;
      margin-top: 20px;
    }
    .el-dialog__header{
      border-bottom: 1px dashed #CCC;
    }
    .dia_table{
      width: 100%;
    }
    .dia_table td{
      line-height: 2.5;
    }
    .fix_letter_spance{
      letter-spacing: 3px;
    }
    .fl {
      float: left;
    }
    .mr20 {
      margin-right: 20px;
    }
    .search{
      line-height: 36px;
    }
  }
</style>
