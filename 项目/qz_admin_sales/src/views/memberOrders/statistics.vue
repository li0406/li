<template>
  <div class="member-order">
    <div class="search">
      <div class="city fl mr20">
        日期: <br>
        <el-select v-model="dataSelectVal" placeholder="请选择">
          <el-option
            v-for="item in dataOptions"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </div>

      <div class="fl mr20">
        <br>
        <div class="block mt4">
          <el-date-picker
            v-model="begin_time"
            value-format="yyyy-MM-dd"
            type="date"
            format="yyyy-MM-dd "
            placeholder="开始时间"/>
          <el-date-picker
            v-model="end_time"
            value-format="yyyy-MM-dd"
            type="date"
            format="yyyy-MM-dd "
            placeholder="结束时间"/>
        </div>
      </div>
      <div class="yixiang fl mr20">
        城市：<br>
        <el-autocomplete
          v-model="citySelectStr"
          class="inline-input mt4"
          :fetch-suggestions="queryCity"
          placeholder="请选择"
          @select="handleSelect"
          @blur="handleBlur"
        />
      </div>
      <div class="fl mr20">
        装修公司：<br>
        <el-input
          class="mt4"
          v-model="zxComvalText"
          placeholder="请输入装修公司名称"
          clearable>
        </el-input>
      </div>
      <div class="fl mr20">
        订单类型：<br>
        <el-select v-model="orderTypeVal" placeholder="请选择">
          <el-option
            v-for="item in orderTypeOptions"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </div>
      <div>
        <br>
        <el-button type="primary" icon="el-icon-search" @click="handleSearch" class="mt4">搜索</el-button>
        <el-button plain @click="handleExport" v-loading="exportLoading" class="mt4">导出</el-button>
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
          label="城市/区县"
        >
          <template slot-scope="scope">
            {{ scope.row.city }}<br>{{ scope.row.area }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="订单类型"
        >
          <template slot-scope="scope">
            {{ scope.row.order_status | transferOrderType }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="name"
          label="联系人"
        />
        <el-table-column
          align="center"
          prop="xiaoqu"
          label="签单小区"
        />
        <el-table-column
          align="center"
          label="签单面积"
        >
          <template slot-scope="scope">
            {{ scope.row.mianji }}m²
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="签单金额"
        >
          <template slot-scope="scope">
            {{ scope.row.qiandan_jine | transferMoneyNuit }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="签单公司"
        >
          <template slot-scope="scope">
            {{ scope.row.qiandan_company }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="name"
          label="公司ID"
        >
          <template slot-scope="scope">
            {{ scope.row.qiandan_companyid }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="操作"
        >
          <template slot-scope="scope">
            <span class="link-blue pointer" @click="handleDetail(scope.row.id)">详情</span>
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
    <qz-dialog :dia-title="'查看订单详情：' + orderNO" :dialog-visible="dialogTableVisible" :dia-width="'650px'"
               @diaClose="closeDialog">
      <table v-loading="dialoading" class="dia_table">
        <tr>
          <td width="80">发布时间：</td>
          <td width="240">{{ orderInfo.publist_time }}</td>
          <td width="80">业&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;主：</td>
          <td>{{ orderInfo.name }}</td>
        </tr>
        <tr>
          <td width="80">电&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;话：</td>
          <td width="240">{{ orderInfo.tel }}</td>
          <td width="80">所属区域：</td>
          <td>{{ orderInfo.cs }}/{{ orderInfo.area }}</td>
        </tr>
        <tr>
          <td width="80">装修类型：</td>
          <td width="240">{{ orderInfo.lxs_text }}</td>
          <td width="80">小区名称：</td>
          <td>{{ orderInfo.xiaoqu }}</td>
        </tr>
        <tr>
          <td width="80">房屋户型：</td>
          <td width="240">{{ orderInfo.huxing }}</td>
          <td width="80">面&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;积：</td>
          <td>{{ orderInfo.mianji }}</td>
        </tr>
        <tr>
          <td width="80">房屋用途：</td>
          <td width="240">{{ orderInfo.yt }}</td>
          <td width="80">方&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;式：</td>
          <td>{{ orderInfo.fangshi }}</td>
        </tr>
        <tr>
          <td width="80">风&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;格：</td>
          <td width="240">{{ orderInfo.fengge }}</td>
          <td width="80">预&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;算：</td>
          <td>{{ orderInfo.yusuan }}</td>
        </tr>
        <tr>
          <td width="80">选择公司：</td>
          <td colspan="3">
          <span v-for="(com, index) in orderInfo.companys" class="link-blue">
            {{ com.jc }}<i v-if="index < parseInt(orderInfo.companys.length) - 1">、</i>
          </span>
          </td>
        </tr>
        <tr>
          <td width="80">分&nbsp;&nbsp;单&nbsp;&nbsp;人：</td>
          <td colspan="3">{{ orderInfo.fen_customer_name }}</td>
        </tr>
        <tr>
          <td colspan="4">装修要求：</td>
        </tr>
        <tr>
          <td colspan="4" v-html="orderInfo.text"/>
        </tr>
      </table>
    </qz-dialog>
  </div>
</template>

<script>
  import {fetchMemberOrderList, fetchOrderInfo, fetchFindUser} from '@/api/memberOrder'
  import {fetchCityList} from '@/api/common'
  import QzDialog from '@/components/QzDialog'
  import {export_json_to_excel} from '@/excel/Export2Excel'
import { formatDate } from '@/utils/index'
  export default {
    name: 'MemberOrder',
    components: {
      QzDialog
    },
    filters: {
      transferOrderType(val) {
        val = parseInt(val)
        if (parseInt(val) === 1) {
          return '分单'
        } else if (parseInt(val) === 2) {
          return '赠单'
        }
      },
      transferMoneyNuit(val) {
        return val + '万'
      }
    },
    data() {
      return {
        begin_time: '',
        end_time: '',
        dataOptions: [{
          value: '1',
          label: '发布日期'
        }, {
          value: '2',
          label: '订单分配日期'
        }, {
          value: '3',
          label: '申请签单日期'
        }, {
          value: '4',
          label: '审核日期'
        }],
        // queryTimeRange: '',
        citys: [],
        dataSelectVal: '3',
        citySelectStr: '',
        citySelectVal: '',
        zxComval: '',
        zxComvalText: '',
        zxComs: [],
        orderTypeOptions: [{
          value: '0',
          label: '全部'
        }, {
          value: '1',
          label: '分单'
        }, {
          value: '2',
          label: '赠单'
        }],
        orderTypeVal: '',
        tableData: [{
          'id': 1,
          'publish_time': 2018 - 9 - 2,
          'allocate_time': 2018 - 9 - 2,
          'sign_time': 2018 - 9 - 2,
          'verify_time': 2018 - 9 - 2,
          'city': '北京',
          'area': '草阳区',
          'order_status': 1,
          'name': '例子',
          'xiaoqu': '金红',
          'qiandan_company': '贸易',
          'qiandan_companyid': 12,
          'qiandan_mianji': 2,
          'qiandan_jine': 2
        }, {
          'id': 2,
          'publish_time': 2018 - 9 - 2,
          'allocate_time': 2018 - 9 - 2,
          'sign_time': 2018 - 9 - 2,
          'verify_time': 2018 - 9 - 2,
          'city': '北京',
          'area': '草阳区',
          'order_status': 1,
          'name': '例子',
          'xiaoqu': '金红',
          'qiandan_company': '贸易',
          'qiandan_companyid': 12,
          'qiandan_mianji': 2,
          'qiandan_jine': 2
        }],
        currentPage: 1,
        dialogTableVisible: false,
        loading: false,
        exportLoading: false,
        dialoading: false,
        totals: 0,
        pageSize: 20,
        orderInfo: {},
        orderNO: '',
        exportData: [],
        timeout: null,
        cityBlurFlag: null,
        comBlurFlag: null
      }
    },
    watch: {
      dataSelectVal() {
        // this.queryTimeRange = ''
        this.begin_time = ''
        this.end_time = ''
      },
      citySelectStr(value) {
        if (!value) {
          this.citySelectVal = ''
          this.cityBlurFlag = null
        }
      },
      zxComvalText(value) {
        if (!value) {
          this.zxComval = ''
          this.comBlurFlag = null
        }
      },
      begin_time() {
        if (this.begin_time == null) {
          this.begin_time = ''
        }
      },
      end_time() {
        if (this.end_time == null) {
          this.end_time = ''
        }
      }
    },
    mounted() {
      this.loadAllCity()
    },
    created() {
      this.fetchMemberOrders()
    },
    methods: {
      loadAllCity() {
        fetchCityList().then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            const data = res.data.data[0]
            data.forEach((item, index) => {
              this.citys.push(item)
            })
          } else {
            this.citys = []
          }
        })
      },
      fetchMemberOrders() {
        const queryObj = this.handleArguments()
        this.loading = true
        fetchMemberOrderList(queryObj).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if (res.data.data.list.length <= 0 && this.currentPage > 1) {
              this.currentPage--
              this.fetchMemberOrders()
            } else {
              this.tableData = []
              this.totals = res.data.data.page.total_number
              this.pageSize = res.data.data.page.page_size
              res.data.data.list.forEach((item, index) => {
                this.tableData.push(item)
              })
              this.begin_time = formatDate(res.data.data.search.qd_start_time)
              this.end_time = formatDate(res.data.data.search.qd_end_time)
              this.loading = false
            }
          } else {
            this.tableData = []
            this.totals = 0
            this.loading = false
          }
        })
      },
      fetchExportMemberOrders(val, cb) {
        const queryObj = this.handleArguments(val)
        fetchMemberOrderList(queryObj).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if (res.data.data.list.length > 0) {
              res.data.data.list.forEach((item, index) => {
                if (parseInt(item.order_status) === 1) {
                  item.order_status_text = '分单'
                } else if (parseInt(item.order_status) === 2) {
                  item.order_status_text = '赠单'
                }
                item.city_and_area = item.city + "/" + item.area
                this.exportData.push(item)
              })
              cb && cb.call()
            } else {
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
          publish_start: '', // 发布查询开始时间
          publish_end: '', // 发布查询结束时间
          allocate_start: '', // 订单分配查询开始时间
          allocate_end: '', // 订单分配查询结束时间
          sign_start: '', // 签单查询开始时间
          sign_end: '', // 签单查询结束时间
          verify_start: '', // 审核查询开始时间
          verify_end: '', // 审核查询结束时间
          cs: this.citySelectVal, // 查询城市ID
          company: this.zxComvalText, // 装修公司名称/ID
          fw: this.orderTypeVal, // 订单类型：0或者空->请选择  1->分单  2->赠单
          p: this.currentPage, // 分页 页码  默认1
          down: '', // 是否导出 非0或非空->导出
          psize: 20
        }
        if (val === 'export') {
          queryObj.down = 1
        }
        if ((this.begin_time == '' && this.end_time != '') || (this.begin_time != '' && this.end_time == '')) {
          this.$message.warning('请选择开始和结束时间')
          return
        }
        if (this.begin_time != '' && this.end_time != '' && this.begin_time > this.end_time) {
          this.$message.warning('开始时间不能大于结束时间')
          return
        }
        if ((this.begin_time != '' && this.end_time != '' && this.begin_time < this.end_time)||(this.begin_time != '' && this.end_time != ''&&this.begin_time==this.end_time) ) {
          if (!this.dataSelectVal) {
            this.$message.error('请选择日期类型')
            return
          }
          time_begin = new Date(this.begin_time).getFullYear() + '-' + (new Date(this.begin_time).getMonth() + 1) + '-' + new Date(this.begin_time).getDate()
          time_end = new Date(this.end_time).getFullYear() + '-' + (new Date(this.end_time).getMonth() + 1) + '-' + new Date(this.end_time).getDate()
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
      fetchOrderInfo(id) {
        this.dialoading = true
        fetchOrderInfo({
          id: id
        }).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            const data = res.data.data
            this.orderInfo = data
            this.orderInfo.publist_time = data.time
            switch (this.orderInfo.lxs) {
              case 1:
                this.orderInfo.lxs_text = '新房装修'
                break
              case 2:
                this.orderInfo.lxs_text = '旧房装修'
                break
              case 3:
                this.orderInfo.lxs_text = '旧房改造'
                break
              default:
                this.orderInfo.lxs_text = '----'
            }
            this.orderNO = id
            this.dialoading = false
          }
        })
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
      querySearchUser(queryString, cb) {
        this.comBlurFlag = null
        this.searchUser(queryString, () => {
          clearTimeout(this.timeout)
          this.timeout = setTimeout(() => {
            cb(this.zxComs)
          }, 1000)
        })
      },
      searchUser(query, cb) {
        fetchFindUser({uid: query}).then(res => {
          let data = res.data.data
          this.zxComs = data[0]
          cb && cb.call()
        })
      },
      handleSelectUser(item) {
        console.log(item);

        this.comBlurFlag = item
        this.zxComval = item.id
        this.zxComvalText = item.jc
      },
      closeDialog() {
        this.orderInfo = {}
        this.orderNO = ''
        this.dialogTableVisible = false
      },
      handleExport() {
        this.exportLoading = true
        const tHeader = [
          // '订单分配日期',
          // '申请签单日期',
          // '审核日期',
          '城市/区县',
          '订单类型',
          '联系人',
          '签单小区',
          '签单面积',
          '签单金额',
          '签单装修公司',
          '签单公司ID'
        ]
        // 上面设置Excel的表格第一行的标题
        const filterVal = [
          // 'allocate_time',
          // 'sign_time',
          // 'verify_time',
          'city_and_area',
          'order_status_text',
          'name',
          'xiaoqu',
          'mianji',
          'qiandan_jine',
          'qiandan_company',
          'qiandan_companyid'
        ]
        this.fetchExportMemberOrders('export', () => {
          // 上面的index、phone_Num、school_Name是tableData里对象的属性
          const list = this.exportData // 把data里的exportData存到list
          this.exportData = []
          const data = this.formatJson(filterVal, list)
          export_json_to_excel(tHeader, data, '会员签单统计')
          this.exportLoading = false
        })
      },
      formatJson(filterVal, jsonData) {
        return jsonData.map(v => filterVal.map(j => v[j]))
      },
      handleSearch() {
        this.fetchMemberOrders()
      },
      handleSelect(item) {
        console.log(item)
        this.cityBlurFlag = item
        this.citySelectVal = item.cid
        this.citySelectStr = item.value
      },
      handleBlur() {
        if (!this.cityBlurFlag) {
          this.citySelectStr = ''
          this.citySelectVal = ''
        }
      },
      handleComBlur() {
        if (!this.comBlurFlag) {
          this.zxComval = ''
          this.zxComvalText = ''
        }
      },
      handleDetail(id) {
        this.dialogTableVisible = true
        this.dialoading = true
        this.fetchOrderInfo(id)
      },
      handleSizeChange() {
        this.currentPage = 1
        this.fetchMemberOrders()
      },
      handleCurrentChange(val) {
        this.currentPage = val
        this.fetchMemberOrders()
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
  .member-order {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;

    .el-date-editor {
      .el-range-separator {
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

    .el-pagination {
      text-align: center;
      margin-top: 20px;
    }

    .el-dialog__header {
      border-bottom: 1px dashed #CCC;
    }

    .dia_table {
      width: 100%;
    }

    .dia_table td {
      line-height: 2.5;
    }

    .fix_letter_spance {
      letter-spacing: 3px;
    }

    .fl {
      float: left;
    }

    .mr20 {
      margin-right: 20px;
    }

    .mt4 {
      margin-top: 4px;
    }
  }
</style>
