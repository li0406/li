<template>
  <div class="status-order">
    <div class="search">
      <div class="search-top">
        <div class="fl mr20">
          时间: <br>
          <div class="block">
            <el-date-picker
              v-model="queryTimeRange"
              value-format="yyyy-MM-dd"
              type="daterange"
              range-separator="至"
              start-placeholder="开始时间"
              end-placeholder="结束时间"
            />
          </div>
        </div>
        <div class="fl mr20">
          装修公司：<br>
          <el-autocomplete
            v-model="zxComvalText"
            :fetch-suggestions="querySearchUser"
            :trigger-on-focus="false"
            class="inline-input"
            @select="handleSelectUser"
            placeholder="请输入装修公司名称/ID"
            @blur="handleComBlur"
          ></el-autocomplete>
        </div>
        <div class="fl mr20">
          订单号: <br>
          <el-input
            v-model="codeNum"
            placeholder="请输入订单号"
            clearable>
          </el-input>
        </div>
        <div class="fl mr20">
          订单状态: <br>
          <el-select v-model="codeStatus" clearable placeholder="">
            <el-option
              v-for="item in statusList"
              :key="item.value"
              :label="item.label"
              :value="item.value">
            </el-option>
          </el-select>
        </div>
        <div class="fl mr20">
          小区名称: <br>
          <el-input
            v-model="codeHouse"
            placeholder="请输入小区"
            clearable>
          </el-input>
        </div>
      </div>
      <div class="search-bottom">
        <div class="fl mr20">
          装修类型: <br>
          <el-select v-model="codeType" clearable placeholder="">
            <el-option
              v-for="item in typeList"
              :key="item.value"
              :label="item.label"
              :value="item.value">
            </el-option>
          </el-select>
        </div>
        <div class="fl mr20">
          装修方式: <br>
          <el-select v-model="codeWay" clearable placeholder="">
            <el-option
              v-for="item in wayList"
              :key="item.value"
              :label="item.label"
              :value="item.value">
            </el-option>
          </el-select>
        </div>
        <div class="yixiang fl mr20">
          城市：<br>
          <el-autocomplete
            v-model="citySelectStr"
            class="inline-input"
            :fetch-suggestions="queryCity"
            placeholder="请选择"
            @select="handleSelect"
            @blur="handleBlur"
          />
        </div>
        <div class="fl mr20">
          订单量房状态：<br>
          <el-select v-model="orderLfStatus" clearable placeholder="请选择">
            <el-option value="0" label="请选择" />
            <el-option value="1" label="是" />
            <el-option value="2" label="否" />
          </el-select>
        </div>
        <div class="fl mr20">
          会员是否量房：<br>
          <el-select v-model="isliangfang" clearable placeholder="请选择">
            <el-option value="0" label="请选择" />
            <el-option value="1" label="是" />
            <el-option value="2" label="否" />
          </el-select>
        </div>
        <div>
          <br>
          <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
          <el-button plain @click="handleExport" v-loading="exportLoading">导出</el-button>
        </div>
      </div>
    </div>
    <div class="qian-main">
      <el-table
        v-loading="loading"
        :data="tableData"
        border
        :row-style="tableRowClassName"
      >
        <el-table-column
          align="center"
          prop="id"
          width="160"
          label="订单号"
        />
        <el-table-column
          align="center"
          prop="xiaoqu"
          label="小区名称"
        />
        <el-table-column
          align="center"
          label="城市"
        >
          <template slot-scope="scope">
            {{ scope.row.cname }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="area_name"
          label="区域"
        />
        <el-table-column
          align="center"
          prop="yusuan"
          label="预算"
        />
        <el-table-column
          align="center"
          prop="mianji"
          label="面积"
        />
        <el-table-column
          align="center"
          label="装修类型"
        >
          <template slot-scope="scope">
            {{ scope.row.lx | transferOrderlx }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="装修方式"
        >
          <template slot-scope="scope">
            {{ scope.row.fangshi }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="fen_time"
          label="分单时间"
        />
        <el-table-column
          align="center"
          label="订单量房状态"
        >
          <template slot-scope="scope">
            {{ scope.row.order_liangfang_status | transferOrderState }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="jc"
          label="装修公司"
        />
        <el-table-column
          align="center"
          label="分/赠单"
        >
          <template slot-scope="scope">
            {{ scope.row.type_fen }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="分配方式"
        >
          <template slot-scope="scope">
            {{ scope.row.allot_type_fw }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="签单时间"
        >
          <template slot-scope="scope">
            {{ scope.row.qiandan_chktime }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="qiandan_jine"
          label="签单金额（万）"
        />
        <el-table-column
          align="center"
          label="会员是否量房"
        >
          <template slot-scope="scope">
             {{ scope.row.liangfang_status | transferOrderOff }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="操作"
          fixed="right"
        >
          <template slot-scope="scope">
            <span class="link-blue pointer" @click="handleDetail(scope.row.id)">查看详情</span>
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
    <qz-dialog :dia-title="'订单编号：' + orderNO" :dialog-visible="dialogTableVisible" :dia-width="'650px'" @diaClose="closeDialog">
      <table v-loading="dialoading" class="dia_table">
        <tr>
          <td width="80">发布时间：</td><td width="240">{{ orderInfo.time_pub }}</td>
          <td width="80">区&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;域：</td><td>{{ orderInfo.cs }} {{ orderInfo.area }}</td>
        </tr>
        <tr>
          <td width="80">业&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;主：</td><td width="240">{{ orderInfo.name }}</td>
          <td width="80">联系电话：</td><td>{{ orderInfo.tel }}</td>
        </tr>
        <tr>
          <td width="80">小区名称：</td><td width="240">{{ orderInfo.xiaoqu }}</td>
          <td width="80">装修方式：</td><td>{{ orderInfo.fangshi }}</td>
        </tr>
        <tr>
          <td width="80">面&nbsp;&nbsp;&nbsp;积&nbsp;&nbsp;&nbsp;(m²)：</td><td width="240">{{ orderInfo.mianji }}</td>
          <td width="80">房屋户型：</td><td>{{ orderInfo.huxing }}</td>
        </tr>
        <tr>
          <td width="80">装修类型：</td><td width="240">{{ orderInfo.lx_text }}{{ orderInfo.lxs_text }}</td>
          <td width="80">装修预算：</td><td>{{ orderInfo.yusuan }}</td>
        </tr>
        <tr>
          <td width="80">分配上限：</td><td width="240">{{ orderInfo.allot_num }}</td>
          <td width="80">已抢公司：</td>
          <td>
            <span v-for="(com) in orderInfo.companys" :key='com.company_id'>
                <span v-if="com.allot_source == 3" :class="com.isread == 0 ? 'link-red' : ''">{{ com.jc }}</span>&nbsp;
            </span>
          </td>
        </tr>
        <tr>
          <td width="80">已分公司：</td>
          <td width="240">
             <span v-for="(com) in orderInfo.companys" :key="com.company_id">
                <span v-if="com.allot_source != 3" :class="com.isread ==0 ? 'link-red' : '' ">{{ com.jc }}</span>&nbsp;
            </span>
          </td>
          <td width="80">量房公司：</td>
          <td>
            <span v-for="(com) in orderInfo.liangfang_companys" :key='com.orderid' class="link-blue">
            {{ com.jc }}&nbsp;
            </span>
          </td>
        </tr>
        <tr>
          <td width="80">签约公司：</td>
          <td width="240">
            <span v-for="(com) in orderInfo.qdcompanys" :key="com.id" class="link-red">
            {{ com.jc }}
            </span>
          </td>
          <td width="80">签约金额：</td><td  class="link-red">{{ orderInfo.qiandan_jine }}</td>
        </tr>
        <tr style="margin-top:10px">
         装修要求：
          <td colspan="4"><textarea  name="" disabled="disabled" id="" cols="30" rows="8" v-html="orderInfo.text" class="area"></textarea></td>
        </tr>
      </table>
    </qz-dialog>
  </div>
</template>

<script>
import { fetchStautsOrderDetail, fetchFindUser, fetchOrderInfo } from '@/api/statusOrder'
// 模拟接口（订单编号，小区名称）
import { fetchCityList } from '@/api/common'
import QzDialog from '@/components/QzDialog'
import { export_json_to_excel } from '@/excel/Export2Excel'
export default {
  name: 'Detail',
  components: {
    QzDialog
  },
  filters: {
    transferOrderState(val) {
      val = parseInt(val)
      if (parseInt(val) === 1) {
        return '已量房'
      } else if (parseInt(val) === 2) {
        return '未量房'
      } else if (parseInt(val) === 3) {
        return '未知'
      }
    },
    transferOrderOff(val) {
      val = parseInt(val)
      if (parseInt(val) === 1) {
        return '是'
      } else if (parseInt(val) === 2) {
        return '否'
      } else if (parseInt(val) === 3) {
        return '否'
      }
    },
    transferOrderlx(val) {
      val = parseInt(val)
      if (parseInt(val) === 1) {
        return '家装'
      } else if (parseInt(val) === 2) {
        return '公装'
      }
    },
    transferOrderFw(val) {
      val = parseInt(val)
      if (parseInt(val) === 1) {
        return '分单'
      } else if (parseInt(val) === 2) {
        return '赠单'
      }
    },
    transferOrderSource(val) {
      val = parseInt(val)
      if (parseInt(val) === 1) {
        return '手动分配'
      } else if (parseInt(val) === 2) {
        return '自动分配'
      } else if (parseInt(val) === 3) {
        return '抢单'
      }
    }
  },
  data() {
    return {
      // 搜索字段
      queryTimeRange: [],
      citySelectStr: '',
      zxComvalText: '',
      codeNum: '',
      codeStatus: '',
      codeHouse: '',
      codeType: '',
      codeWay: '',
      zxComval: '',
      zxHouse: '',
      zxNum: '',
      citys: [],
      comList: [],
      citySelectVal: '',
      cityBlurFlag: null,
      comBlurFlag: null,
      houseBlurFlag: null,
      numBlurFlag: null,
      statusList: [{
        value: '',
        label: '全部'
      }, {
        value: '1',
        label: '分单'
      }, {
        value: '2',
        label: '赠单'
      }, {
        value: '3',
        label: '分单（抢）'
      }],
      typeList: [{
        value: '',
        label: '全部'
      }, {
        value: '1',
        label: '家装'
      }, {
        value: '2',
        label: '公装'
      }],
      wayList: [{
        value: '',
        label: '全部'
      }, {
        value: '29',
        label: '半包'
      }, {
        value: '30',
        label: '全包'
      }],
      // 表格
      tableData: [{
        'id': 19,
        'xiaoqu': '红富士-mock',
        'cname': '苏州-mock',
        'area_name': '姑苏区-mock',
        'yusuan': '100万-mock',
        'mianji': 119,
        'lx': '公装-mock',
        'fangshi': '半包-mock',
        'fen_time': '2019-01-09',
        'liangfang_status': '未知-mock',
        'jc': '红蚂蚁-mock',
        'type_fw': '分单-mock',
        'qiandan_chktime': '2019-04-19',
        'qiandan_jine': 19
      }],
      currentPage: 1,
      totals: 0,
      pageSize: 20,
      exportData: [],
      // 弹窗
      orderNO: '',
      dialogTableVisible: false,
      orderInfo: {},
      // 交互
      timeout: null,
      exportLoading: false,
      loading: false,
      dialoading: false,
      isliangfang: '',
      orderLfStatus: ''
    }
  },
  watch: {
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
    }
  },
  mounted() {
    this.loadAllCity()
  },
  created() {
    this.zxComvalText = this.$route.query.company
    this.citySelectStr = this.$route.query.cname
    this.citySelectVal = this.$route.query.cid
    this.zxComval = this.$route.query.id
    this.codeNum = this.$route.query.order
    if(this.$route.query.beginTime!=undefined){
      this.queryTimeRange[0]=this.$route.query.beginTime
    }
    if(this.$route.query.endTime!=undefined){
      this.queryTimeRange[1]=this.$route.query.endTime
    }
    // 表格数据获取
    this.fetchOrderList()
    const fendanObj = JSON.parse(localStorage.getItem('fendandata'))
    if (fendanObj !== null && (fendanObj.start!=''|| fendanObj.end!='')) {
      this.queryTimeRange[0] = fendanObj.start
      this.queryTimeRange[1] = fendanObj.end
    }
  },
  methods: {
    // 城市匹配
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
    handleSelect(item) {
      this.cityBlurFlag = item
      this.citySelectVal = item.cid
      this.citySelectStr = item.value
    },
    handleBlur() {
      if (!this.cityBlurFlag) {
        // this.citySelectStr = ''
        // this.citySelectVal = ''
      }
    },
    // 装修公司匹配
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
      fetchFindUser({ uid: query }).then(res => {
        const data = res.data.data
        this.zxComs = data[0]
        cb && cb.call()
      })
    },
    handleSelectUser(item) {
      this.comBlurFlag = item
      this.zxComval = item.id
      this.zxComvalText = item.jc
    },
    handleComBlur() {
      if (!this.comBlurFlag) {
        this.zxComval = ''
        // this.zxComvalText = ''
      }
    },
    // 表格数据获取
    handleArguments(val) {
      let time_begin = ''
      let time_end = ''
      const queryObj = {
        // 真实数据
        start: '', // 发布查询开始时间
        end: '', // 发布查询结束时间
        company: this.zxComval, // 装修公司名称/ID
        order: this.codeNum, // 订单编号
        fz_type: this.codeStatus, // 订单状态
        xq: this.codeHouse, // 小区名称
        lx: this.codeType, // 装修类型
        fs: this.codeWay, // 装修方式
        cs: this.citySelectVal, // 查询城市ID
        p: this.currentPage, // 分页 页码  默认1
        down: '', // 是否导出 非0或非空->导出
        psize: 20,
        order_liangfang_status: this.orderLfStatus, // 订单量房状态
        liangfang_status: this.isliangfang // 是否量房
      }
      if (queryObj.company == '') {
        queryObj.company = this.zxComvalText
      }
      if (val === 'export') {
        queryObj.down = 1
      }
      const fendanObj = JSON.parse(localStorage.getItem('fendandata'))
      if (fendanObj !== null && (fendanObj.start!=''|| fendanObj.end!='')) {
        queryObj.start = fendanObj.start
        queryObj.end = fendanObj.end
      }
      if (this.queryTimeRange && this.queryTimeRange.length > 0 ) {
        time_begin = new Date(this.queryTimeRange[0]).getFullYear() + '-' + (new Date(this.queryTimeRange[0]).getMonth() + 1) + '-' + new Date(this.queryTimeRange[0]).getDate()
        time_end = new Date(this.queryTimeRange[1]).getFullYear() + '-' + (new Date(this.queryTimeRange[1]).getMonth() + 1) + '-' + new Date(this.queryTimeRange[1]).getDate()
        queryObj.start = time_begin
        queryObj.end = time_end
      }
      return queryObj
    },
    fetchOrderList() {
      if (this.queryTimeRange && this.queryTimeRange.length > 0) {
        const times_k = new Date(this.queryTimeRange[0]).getTime()
        const times_j = new Date(this.queryTimeRange[1]).getTime()
        const diff = Math.abs(times_j - times_k)
        if (diff > 7776000000 && !(this.zxComval || this.codeHouse || this.citySelectVal || this.zxComvalText)) {
          this.$message({
            message: '查询时间段超过三个月，必须选择装修公司、城市、小区名称中的一个筛选条件',
            type: 'error',
            duration: 5 * 1000
          })
          return false
        }
      }
      const queryObj = this.handleArguments()
      this.loading = true
      fetchStautsOrderDetail(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (res.data.data.list.length <= 0 && this.currentPage > 1) {
            this.currentPage--
            this.fetchOrderList()
          } else {
            this.tableData = []
            this.totals = res.data.data.page.total_number
            this.pageSize = res.data.data.page.page_size
            res.data.data.list.forEach((item, index) => {
              if (parseInt(item.type_fw) === 1 && parseInt(item.allot_source) === 3) {
                item.type_fen = '分单抢'
              } else if (parseInt(item.type_fw) === 2 && parseInt(item.allot_source) === 3) {
                item.type_fen = '赠单'
              } else if (parseInt(item.type_fw) === 1 && parseInt(item.allot_source) !== 3) {
                item.type_fen = '分单'
              } else if (parseInt(item.type_fw) === 2 && parseInt(item.allot_source) !== 3) {
                item.type_fen = '赠单'
              }
              if (parseInt(item.allot_type_fw) === 1) {
                item.allot_type_fw = '分单'
              } else if (parseInt(item.allot_type_fw) === 2){
                item.allot_type_fw = '赠单'
              }
              this.tableData.push(item)
            })
            this.queryTimeRange = [res.data.data.options.start,res.data.data.options.end]
            this.loading = false
          }
        } else {
          this.tableData = []
          this.totals = 0
          this.loading = false
        }
      })
    },
    // 查询
    handleSearch() {
      this.fetchOrderList()
    },
    // 导出
    fetchExportOrders(val, cb) {
      const queryObj = this.handleArguments(val)
      fetchStautsOrderDetail(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (res.data.data.list.length > 0) {
            res.data.data.list.forEach((item, index) => {
              // 此处对数据进行处理(后续)
              if (parseInt(item.order_liangfang_status) === 1) {
                item.liangfang_text = '已量房'
              } else if (parseInt(item.order_liangfang_status) === 2) {
                item.liangfang_text = '未量房'
              } else if (parseInt(item.order_liangfang_status) === 3) {
                item.liangfang_text = '未知'
              }
              if (parseInt(item.liangfang_status) === 1) {
                item.liangfang_off = '是'
              } else if (parseInt(item.liangfang_status) === 2) {
                item.liangfang_off = '否'
              } else if (parseInt(item.liangfang_status) === 3) {
                item.liangfang_off = '否'
              }
              if (parseInt(item.lx) === 1) {
                item.lx_text = '家装'
              } else if (parseInt(item.lx) === 2) {
                item.lx_text = '公装'
              }
              if (parseInt(item.type_fw) === 1 && parseInt(item.allot_source) === 3) {
                item.type_fen = '分单抢'
              } else if (parseInt(item.type_fw) === 2 && parseInt(item.allot_source) === 3) {
                item.type_fen = '赠单'
              } else if (parseInt(item.type_fw) === 1 && parseInt(item.allot_source) !== 3) {
                item.type_fen = '分单'
              } else if (parseInt(item.type_fw) === 2 && parseInt(item.allot_source) !== 3) {
                item.type_fen = '赠单'
              }
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
    handleExport() {
      this.exportLoading = true
      const tHeader = [
        '订单号',
        '小区名称',
        '城市',
        '区域',
        '预算',
        '面积',
        '装修类型',
        '装修方式',
        '分单时间',
        '订单量房状态',
        '装修公司',
        '分/赠单',
        '签单时间',
        '签单金额（万）',
        '会员是否量房'
      ]
      // 上面设置Excel的表格第一行的标题
      const filterVal = [
        'id',
        'xiaoqu',
        'cname',
        'area_name',
        'yusuan',
        'mianji',
        'lx_text',
        'fangshi',
        'fen_time',
        'liangfang_text',
        'jc',
        'type_fen',
        'qiandan_chktime',
        'qiandan_jine',
        'liangfang_off'
      ]
      this.fetchExportOrders('export', () => {
        const list = this.exportData // 把data里的exportData存到list
        this.exportData = []
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '会员分单统计')
        this.exportLoading = false
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]))
    },
    // 查看详情
    handleDetail(id) {
      this.dialogTableVisible = true
      this.dialoading = true
      this.fetchOrderInfo(id)
    },
    fetchOrderInfo(id) {
      this.dialoading = true
      fetchOrderInfo({
        id: id
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          const data = res.data.data
          this.orderInfo = data
          switch (parseInt(this.orderInfo.lxs)) {
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
          switch (parseInt(this.orderInfo.lx)) {
            case 1:
              this.orderInfo.lx_text = '家装-'
              break
            case 2:
              this.orderInfo.lx_text = '公装-'
              break
            default:
              this.orderInfo.lx_text = '----'
          }
          this.orderNO = id
          this.dialoading = false
        }
      })
    },
    closeDialog() {
      this.orderInfo = {}
      this.orderNO = ''
      this.dialogTableVisible = false
    },
    // 分页处理
    handleSizeChange() {
      this.currentPage = 1
      this.fetchOrderList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.fetchOrderList()
    },
    // 状态处理
    tableRowClassName({ row, rowIndex }) {
      const allread = row.order2com_allread
      const isread = row.isread
      if (isread !== undefined && allread !== undefined) {
        if (isread === 0) {
          return 'background:#FFEBFF'
        } else if (isread === 1) {
          return 'background:#ffffff'
        }
      } else {
        if (allread === 0) {
          return 'background:#FFEBFF'
        } else if (allread === 1) {
          return 'background:#ffffff'
        }
      }
      return ''
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
  .status-order {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
  .el-date-editor{
  .el-range-separator{
    padding: 0;
  }
  }
  .el-select {
    margin-top: 0;
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
  .search-top{
    width: 1320px;
    height: auto;
    overflow: hidden;
    margin-bottom: 6px;
  }
  .search-bottom{
    width: 1320px;
    height: auto;
    overflow: hidden;
  }
  .search{
    line-height: 36px;
  }
  .link-blue{
    color: #199ED8;
  }
  .link-red{
    color: #FF0000;
  }
  .area{
    display: block;
    width: 440px;
    font-size: 14px;
    line-height: 28px;
  }
  textarea{
    outline:none;
    resize:none
  }
  .one {
    background: red;
    width: 100%;
    height: 100%;
  }
  .el-table .el-table__body .warning-row {
    background: oldlace;
  }

  .el-table .el-table__body .success-row {
    background: #f0f9eb;
  }
</style>

