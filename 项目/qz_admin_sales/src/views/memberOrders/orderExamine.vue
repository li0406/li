<template>
  <div class="member-order">
    <div class="search">
      <div class="fl mr20">
        装修公司：<br>
        <el-input
          class="mt4"
          v-model="zxComvalText"
          placeholder="请输入装修公司名称"
          clearable>
      </el-input>
      </div>
      <div class="city fl mr20">
        日期段: <br>
        <el-select v-model="dataSelectVal" placeholder="申请日期"  @change="changeDate">
          <el-option
            v-for="item in dataOptions"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </div>
      <div class="fl mr20" v-if="showSQ">
        <br>
        <div class="block mt4">
          <el-date-picker
            v-model="begin_time"
            value-format="yyyy-MM-dd"
            type="date"
            format="yyyy-MM-dd "
            style="width:150px;"
            placeholder="请选择日期"/>
            ——
          <el-date-picker
            v-model="end_time"
            value-format="yyyy-MM-dd"
            type="date"
            format="yyyy-MM-dd "
            style="width:150px;"
            placeholder="请选择日期"/>
        </div>
      </div>
      <div class="fl mr20" v-if="showSH">
        <br>
        <div class="block mt4">
          <el-date-picker
            v-model="chkstart"
            value-format="yyyy-MM-dd"
            type="date"
            format="yyyy-MM-dd "
            style="width:150px;"
            placeholder="请选择日期"/>
            ——
          <el-date-picker
            v-model="chkend"
            value-format="yyyy-MM-dd"
            type="date"
            format="yyyy-MM-dd "
            style="width:150px;"
            placeholder="请选择日期"/>
        </div>
      </div>
      <div class="fl mr20" v-if="showQD">
        <br>
        <div class="block mt4">
          <el-date-picker
            v-model="qdstart"
            value-format="yyyy-MM-dd"
            type="date"
            format="yyyy-MM-dd "
            style="width:150px;"
            placeholder="请选择日期"/>
            ——
          <el-date-picker
            v-model="qdend"
            value-format="yyyy-MM-dd"
            type="date"
            format="yyyy-MM-dd "
            style="width:150px;"
            placeholder="请选择日期"/>
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
        审核状态：<br>
        <el-select v-model="orderTypeVal" placeholder="请选择">
          <el-option
            v-for="item in examineStatus"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </div>
      <div class="fl mr20">
        咨询方式：<br>
        <el-select v-model="zxfs" placeholder="请选择" @change="changeWay">
          <el-option
            v-for="item in adviceWay"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </div>
      <div>
        <br>
        <el-button type="primary" icon="el-icon-search" @click="handleSearch" class="mt4">查询</el-button>
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
          label="申请日期"
          prop="time_add"
          width="100"
        >
        </el-table-column>
        <el-table-column
          align="center"
          label="签单日期"
          prop="time_qd"
          width="100"
        >
        </el-table-column>
        <el-table-column
          align="center"
          prop="zxfs"
          label="咨询方式"
        />
        <el-table-column
          align="center"
          prop="name"
          label="业主称呼"
        />
        <el-table-column
          align="center"
          label="所在地区"
        >
          <template slot-scope="scope">
            {{ scope.row.city.cname }}<br>{{ scope.row.area.qz_area }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="xiaoqu"
          label="签单小区"
        >
        </el-table-column>
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
              {{ scope.row.yusuanjt | transferMoneyNuit }}
            </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="公司名称（id）"
        >
          <template slot-scope="scope">
            {{ scope.row.qdcompany.qc }}({{ scope.row.qdcompany.id }})
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="签单审核"
        >
          <template slot-scope="scope">
            <span class="red" v-if="parseInt(scope.row.order_on) === 0">请审核</span>
            <span class="green" v-else>有效</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="操作"
        >
         <template slot-scope="scope">
            <span v-if="showBtn">
              <span class="link-blue pointer" v-if="scope.row.order_on === 0" @click="handleExamine(scope.row.id)">审核通过</span>
              <span class="red pointer" v-else @click="cancelExamine(scope.row.id)">取消审核</span>
            </span>
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
    <el-dialog
        title="查看本单详细"
        :visible.sync="dialogVisible"
        width="30%">
        <div class="group">
            <span class="sp1">发布时间：</span>
            <span>{{detail.time_add}}</span>
        </div>
        <div class="group">
            <span class="sp1">签单时间：</span>
            <span>{{detail.time_qd}}</span>
        </div>
        <div class="group">
            <span class="sp1">咨询方式：</span>
            <span>{{detail.zxfs}}</span>
        </div>
        <div class="group">
            <span class="sp1">业主姓名：</span>
            <span>{{detail.name}}</span>
        </div>
        <div class="group">
            <span class="sp1">所属区域：</span>
            <span>{{detail.areaText}}</span>
        </div>
        <div class="group">
            <span class="sp1">小区名称：</span>
            <span>{{detail.xiaoqu}}</span>
        </div>
        <div class="group">
            <span class="sp1">详细地址：</span>
            <span>{{detail.dz}}</span>
        </div>
        <div class="group">
            <span class="sp1">房屋户型：</span>
            <span>{{detail.hxText}}</span>
        </div>
        <div class="group">
            <span class="sp1">装修类型：</span>
            <span>{{detail.lx === '1'?'家装':'公装'}}</span>
        </div>
        <div class="group">
            <span class="sp1">面  积：</span>
            <span>{{detail.mianji}}m²</span>
        </div>
        <div class="group">
            <span class="sp1">装修风格：</span>
            <span>{{detail.fgText}}</span>
        </div>
        <div class="group">
            <span class="sp1">预  算：</span>
            <span>{{detail.yusuan}}</span>
        </div>
        <div class="group">
            <span class="sp1">备  注：</span>
            <span>{{detail.remarks}}</span>
        </div>
    </el-dialog>

  </div>

</template>

<script>
  import {fetchConsultationList, consultationDetail, consultationCheck} from '@/api/memberOrder'
  import {fetchCityList} from '@/api/common'

  export default {
    name: 'orderExamine',
    components: {
      
    },
    filters: {
      transferMoneyNuit(val) {
        return val + '万'
      }
    },
    data() {
      return {
        lableWidth: '152px',
        showSQ: true,
        showQD: false,
        showSH: false,
        begin_time: '',
        end_time: '',
        qdstart: '',
        qdend: '',
        chkstart: '',
        chkend: '',
        dataOptions: [{
          value: '0',
          label: '申请日期'
        },{
          value: '1',
          label: '签单日期'
        }, {
          value: '2',
          label: '审核日期'
        }],
        citys: [],
        dataSelectVal: '',
        citySelectStr: '',
        citySelectVal: '',
        zxComval: '',
        zxComvalText: '',
        zxComs: [],
        examineStatus: [{
          value: '',
          label: '请选择'
        }, {
          value: '0',
          label: '待审核'
        }, {
          value: '1',
          label: '已审核'
        }],
        adviceWay: [{
          value: '0',
          label: '请选择'
        }, {
          value: '1',
          label: '手机'
        }, {
          value: '2',
          label: 'QQ'
        }, {
          value: '3',
          label: '上门'
        }, {
          value: '4',
          label: '座机'
        }, {
          value: '5',
          label: '其他'
        }],
        orderTypeVal: '',
        zxfs:'',
        tableData: [{
          'time_add': '2018-09-02',
          'time_qd': '2018-09-02',
          'zxfs': '手机',
          'name': '冯女士',
          'city': '{cid:123,cname:北京}',
          'area': '{qz_areaid:123,qz_area:朝阳区}',
          'xiaoqu': '签单小区',
          'mianji': 2,
          'yusuanjt': 2,
          'qdcompany': '{id:123,jc:sss,qc:xxx}',
          'order_on': 1
        }],
        dialogVisible: false,
        loading: false,
        showBtn: false,
        currentPage: 1,
        totals: 0,
        pageSize: 20,
        timeout: null,
        cityBlurFlag: null,
        comBlurFlag: null,
        detail: [{
          time_add: '',
          time_qd: '',
          zxfs: '',
          name: '',
          area: [],
          areaText: '',
          xiaoqu: '',
          dz: '',
          hx: [],
          hxText: '',
          lx: '',
          mianji: '',
          fg: [],
          fgText: '',
          yusuan: '',
          remarks: ''
        }]
      }
    },
    watch: {
      dataSelectVal() {
        this.begin_time = ''
        this.end_time = ''
        this.qdstart = ''
        this.qdend = ''
        this.chkstart = ''
        this.chkend = ''
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
      this.fetchConsultationList()
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
      handleExamine(id) {
        let that = this;
        consultationCheck({
          id:id
        }).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$alert('该订单审核通过！', '审核通过', {
              confirmButtonText: '确定',
              callback: action => {
                this.$message({
                  message: '该订单审核通过！',
                  type: 'success'
                })
                setTimeout(function(){
                  that.fetchConsultationList()
                },500)
              }
            })
          }else{
            this.$message.error(res.data.error_msg)
          }
        }).catch(() => {
            this.visibleTable = false
            this.$message.error('访问错误，请刷新重试')
          })
      },
      cancelExamine(id) {
        let that = this
        consultationCheck({
          id:id
        }).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$alert('该订单已取消审核通过！', '取消审核', {
              confirmButtonText: '确定',
              callback: action => {
                this.$message({
                  message: '该订单已取消审核通过！',
                  type: 'warning'
                })
                setTimeout(function(){
                  that.fetchConsultationList()
                },500)
              }
          });
          }else{
            this.$message.error(res.data.error_msg)
          }
        }).catch(() => {
            this.visibleTable = false
            this.$message.error('访问错误，请刷新重试')
          })
      },
      handleDetail(id) {
        this.dialogVisible = true
        this.dialoading = true
        consultationDetail(
          {id:id}
        ).then(res => {
           if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
              this.detail = [];
              this.detail = Object.assign({}, res.data.data.detail);
              this.detail.areaText = res.data.data.detail.area.qz_area
              this.detail.hxText = res.data.data.detail.hx.name + res.data.data.detail.shi +'室'+ res.data.data.detail.ting+ '厅'+ res.data.data.detail.wei+ '卫'
              this.detail.fgText = res.data.data.detail.fg.name
          } else {
            
          }
        }).catch(() => {
            this.visibleTable = false
            this.$message.error('访问错误，请刷新重试')
          })
      },
      fetchConsultationList() {
        const queryObj = {
          search: this.zxComvalText,
          cs: this.citySelectVal,
          zxfs: this.zxfs,
          order_on: this.orderTypeVal,
          addstart: this.begin_time,
          addend: this.end_time,
          qdstart: this.qdstart,
          qdend: this.qdend,
          chkstart: this.chkstart,
          chkend: this.chkend,
          p: this.currentPage
        }
        this.loading = true
        fetchConsultationList(queryObj).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if (res.data.data.list.length <= 0 && this.currentPage > 1) {
              this.currentPage--
              this.fetchConsultationList()
            } else {
              this.tableData = []
              let showBtn = parseInt(res.data.data.show_btn)
              if( showBtn === 1 ){
                this.showBtn = true
              } else {
                this.showBtn = false
              }
              this.totals = res.data.data.page.total_number
              this.pageSize = res.data.data.page.page_size
              res.data.data.list.forEach((item, index) => {
                this.tableData.push(item)
              })
              this.loading = false
            }
          } else {
            this.tableData = []
            this.totals = 0
            this.loading = false
          }
        }).catch(() => {
            this.visibleTable = false
            this.$message.error('访问错误，请刷新重试')
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
      formatJson(filterVal, jsonData) {
        return jsonData.map(v => filterVal.map(j => v[j]))
      },
      handleSearch() {
        this.fetchConsultationList()
      },
      handleSelect(item) {
        this.cityBlurFlag = item
        this.citySelectVal = item.cid
        this.citySelectStr = item.value
      },
      changeDate(item) {
        if(item === '0'){
          this.showSQ = true;
          this.showQD = false;
          this.showSH = false;
        } else if(item === '1'){
          this.showSQ = false;
          this.showQD = true;
          this.showSH = false;
        } else {
          this.showSQ = false;
          this.showQD = false;
          this.showSH = true;
        }
      },
      changeWay(item) {
        if(item === 0) {
          this.zxfs = ''
        } else if (item === '1') {
          this.zxfs = '手机'
        } else if (item === '2') {
          this.zxfs = 'QQ'
        } else if (item === '3') {
          this.zxfs = '上门'
        } else if (item === '4') {
          this.zxfs = '座机'
        } else {
          this.zxfs = '其他'
        }
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
      handleSizeChange() {
        this.currentPage = 1
        this.fetchConsultationList()
      },
      handleCurrentChange(val) {
        this.currentPage = val
        this.fetchConsultationList()
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
  .group .sp1 {
    display: inline-block;
    width: 100px;
    text-align: right;
    margin-bottom: 10px;
  }
  .el-table .cell {
    padding: 0;
  }
</style>
