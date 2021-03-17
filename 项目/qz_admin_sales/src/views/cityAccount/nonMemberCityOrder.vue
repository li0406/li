<template>
  <div class="nonMemberCityOrder">
    <div class="search">
      <div class="city fl mr20">
        城市:
        <el-autocomplete
          v-model="citySelectStr"
          class="inline-input"
          :fetch-suggestions="queryCity"
          placeholder="请选择"
          @select="handleSelect"
          @blur="handleBlur"
        />
      </div>
      <div class="city fl mr20">
        订单状态:
        <el-select v-model="orderStatusVal" placeholder="请选择">
          <el-option
            v-for="item in orderStatusOptions"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </div>
      <div class="city fl mr20">
        发单时间-开始:
        <el-date-picker
          v-model="date_begin"
          value-format="yyyy-MM-dd"
          type="date"
          format="yyyy-MM-dd "
          placeholder="选择日期"/>
      </div>
      <div class="city fl mr20">
        发单时间-结束:
        <el-date-picker
          v-model="date_end"
          value-format="yyyy-MM-dd"
          type="date"
          format="yyyy-MM-dd "
          placeholder="选择日期"/>
      </div>
      <div>
        <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
        <el-button plain @click="handleExportData">导出</el-button>
        <el-button plain @click="goBack" style="float: right;">返回</el-button>
      </div>
    </div>
    <div class="qian-main">
      <el-table
        v-loading="loading"
        :data="tableData"
        border
      >
        <el-table-column
          align="center"
          prop="date"
          label="发布日期"
        />
        <el-table-column
          align="center"
          prop="qy_name"
          label="地区"
        />
        <el-table-column
          align="center"
          label="业主"
        >
          <template slot-scope="scope">
            {{ !scope.row.yz_name ? '----' : scope.row.yz_name }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="小区"
        >
          <template slot-scope="scope">
            {{ !scope.row.xiaoqu ? '----' : scope.row.xiaoqu }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="面积"
        >
          <template slot-scope="scope">
            <span>{{ !scope.row.mianji ? '----' : scope.row.mianji+'m²' }}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="手机号码"
        >
          <template slot-scope="scope">
            {{ scope.row.tel }}&nbsp;&nbsp;{{ scope.row.tel_location }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="status"
          label="订单状态"
        />
      </el-table>
      <el-pagination
        :current-page="currentPage"
        :page-size="pageSize"
        layout="total, prev, pager, next, jumper"
        :total="totals"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </div>
  </div>
</template>

<script>
  import { fetchNonmemberOrderList } from '@/api/cityAccount'
  import { fetchNoVipCitys } from '@/api/common'
  import { export_json_to_excel } from '@/excel/Export2Excel'
  Date.prototype.Format = function (fmt) { //author: meizz
    const o = {
      "M+": this.getMonth() + 1, //月份
      "d+": this.getDate(), //日
      "h+": this.getHours(), //小时
      "m+": this.getMinutes(), //分
      "s+": this.getSeconds(), //秒
      "q+": Math.floor((this.getMonth() + 3) / 3), //季度
      "S": this.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
      if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
  }
  export default {
    name: "nonMemberCityOrder",
    data() {
      return {
        citySelectStr: '',
        citySelectVal: '',
        cityBlurFlag: null,
        orderStatusOptions: [{
          value: '',
          label: '全部'
        }, {
          value: '1',
          label: '分单'
        }, {
          value: '2',
          label: '赠单'
        }],
        orderStatusVal: '',
        date_begin: '',
        date_end: '',
        loading: false,
        tableData: [],
        currentPage: 1,
        pageSize: 20,
        totals: 0,
        citys: [],
        exportLoading: false,
        exportData: []
      }
    },
    watch: {
      citySelectStr(value) {
        if(!value) {
          this.citySelectVal = ''
          this.cityBlurFlag = null
        }
      }
    },
    mounted() {
      this.loadAllCity()
    },
    created() {
      this.citySelectVal = this.$route.query.cs
      // this.citySelectStr = this.$route.query.cs
      this.date_begin = this.$route.query.begin
      this.date_end = this.$route.query.end
      this.fetchNonMemberCityOrder()
    },
    methods: {
      loadAllCity() {
        fetchNoVipCitys().then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            const data = res.data.data[0]
            data.forEach((item, index) => {
              this.citys.push(item)
            })
            this.initCityInput()
          } else {
            this.citys = []
          }
        })
      },
      initCityInput() {
        if(this.citySelectVal) {
          this.citys.forEach((item, index) => {
            if(this.citySelectVal == item.cid) {
              this.citySelectStr = item.value
            }
          })
        }
      },
      fetchNonMemberCityOrder() {
        const queryObj = this.handleArguments()
        if(!queryObj.cid) {
          this.$message.error('请选择城市再查询')
          return
        }
        // 判断时间大小
        if(this.date_begin || this.date_end) {
          if(!this.date_begin) {
            this.$message.error('请选择开始时间')
            return
          }
          if(!this.date_end) {
            this.$message.error('请选择结束时间')
            return
          }
        }
        if(this.date_begin && this.date_end) {
          if(new Date(new Date(this.date_begin).Format("yyyy-MM-dd")).getTime() > new Date(new Date(this.date_end).Format("yyyy-MM-dd")).getTime()) {
            this.$message.error('开始时间不能大于结束时间')
            this.date_end = ''
            return
          }
        }
        this.loading = true
        fetchNonmemberOrderList(queryObj).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if (res.data.data.list.length <= 0 && this.currentPage > 1) {
              this.currentPage--
              this.fetchNonMemberCityOrder()
            } else {
              this.tableData = []
              this.totals = res.data.data.page.total_number
              this.pageSize = res.data.data.page.page_size
              res.data.data.list.forEach((item, index) => {
                this.tableData.push(item)
              })
              this.loading = false
            }
          } else {
            this.$message.error(res.data.error_msg)
            this.loading = false
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后重试')
          this.loading = false
        })
      },
      fetchExportNonMemberCityOrders(val, cb) {
        const queryObj = this.handleArguments(val)
        fetchNonmemberOrderList(queryObj).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if (res.data.data.list.length > 0) {
              res.data.data.list.forEach((item, index) => {
                this.exportData.push(item)
              })
              cb && cb.call()
            }else{
              this.$message.error('未查询到符合要求的数据')
              this.exportLoading = false
            }
          } else {
            this.$message.error(res.data.error_msg)
            this.exportData = []
            this.exportLoading = false
          }
        })
      },
      handleArguments(val) {
        let time_begin = ''
        let time_end = ''
        const queryObj = {
          start: this.date_begin, // 发布查询开始时间
          end: this.date_end, // 发布查询结束时间
          cid: this.citySelectVal, // 查询城市ID
          type_fw: this.orderStatusVal, // 1 分单 2 增单
          p: this.currentPage, // 分页 页码  默认1
          export: '', // 是否导出 非0或非空->导出
          page_size: 20
        }
        if (val === 'export') {
          queryObj.down = 1
        }
        if (this.queryTimeRange && this.queryTimeRange.length > 0) {
          if (!this.dataSelectVal) {
            this.$message.error('请选择日期类型')
            return
          }
          time_begin = new Date(this.queryTimeRange[0]).getFullYear() + '-' + (new Date(this.queryTimeRange[0]).getMonth() + 1) + '-' + new Date(this.queryTimeRange[0]).getDate()
          time_end = new Date(this.queryTimeRange[1]).getFullYear() + '-' + (new Date(this.queryTimeRange[1]).getMonth() + 1) + '-' + new Date(this.queryTimeRange[1]).getDate()
          switch (this.dataSelectVal) {
            case '1':
              queryObj.publish_start = time_begin
              queryObj.publish_end = time_end
              break
            case '2':
              queryObj.allocate_start = time_begin
              queryObj.allocate_end = time_end
              break
            case '3':
              queryObj.sign_start = time_begin
              queryObj.sign_end = time_end
              break
            case '4':
              queryObj.verify_start = time_begin
              queryObj.verify_end = time_end
              break
            default:
              break
          }
        }
        return queryObj
      },
      queryCity(queryString, cb) {
        const citys = this.citys
        const results = queryString ? citys.filter(this.createFilter(queryString)) : citys
        this.cityBlurFlag = null
        cb(results)
      },
      createFilter(queryString) {
        return (city) => {
          return (city.value.toLowerCase().indexOf(queryString.toLowerCase()) > -1)
        }
      },
      handleExportData() {
        this.exportLoading = true
        const tHeader = [
          '发布日期',
          '地区',
          '业主',
          '小区',
          '面积',
          '手机号码',
          '订单状态'
        ]
        // 上面设置Excel的表格第一行的标题
        const filterVal = [
          'date',
          'qy_name',
          'yz_name',
          'xiaoqu',
          'mianji',
          'tel',
          'status'
        ]
        this.fetchExportNonMemberCityOrders('export', () => {
          // 上面的index、phone_Num、school_Name是tableData里对象的属性
          const list = this.exportData // 把data里的exportData存到list
          this.exportData = []
          const data = this.formatJson(filterVal, list)
          export_json_to_excel(tHeader, data, '无会员城市订单')
          this.exportLoading = false
        })
      },
      formatJson(filterVal, jsonData) {
        return jsonData.map(v => filterVal.map(j => v[j]))
      },
      goBack() {
        history.go(-1)
        // this.$router.push({
        //   path: 'nonMemberCity'
        // })
      },
      handleSearch() {
        this.fetchNonMemberCityOrder()
      },
      handleSelect(item) {
        console.log(item)
        this.cityBlurFlag = item
        this.citySelectVal = item.cid
        this.citySelectStr = item.value
      },
      handleBlur() {
        if(!this.cityBlurFlag) {
          this.citySelectStr = ''
          this.citySelectVal = ''
        }
      },
      handleSizeChange() {
        this.currentPage = 1
        this.fetchNonMemberCityOrder()
      },
      handleCurrentChange(val) {
        this.currentPage = val
        this.fetchNonMemberCityOrder()
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
  .nonMemberCityOrder {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
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
    .mr20 {
      margin-right: 20px;
    }
  }
</style>
