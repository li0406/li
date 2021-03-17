<template>
  <div class="member-deadline">
    <div class="header">
      <div class="mr20 fl">
        公司名称/ID：<br>
        <el-autocomplete
          v-model="company"
          :fetch-suggestions="querySearchId"
          placeholder="请输入公司名称或ID"
          @select="handleIdSelect"
          @blur="handleComBlur"
        ></el-autocomplete>
      </div>
      <div class="mr20 fl">
        城市：<br>
        <el-autocomplete
          v-model="cs"
          :fetch-suggestions="querySearchCity"
          placeholder="请选择城市"
          @select="handleCitySelect"
          @blur="handleBlur"
        ></el-autocomplete>
      </div>
      <div class="mr20 fl">
        会员状态：<br>
        <el-select v-model="type" placeholder="请选择">
          <el-option
            v-for="item in options"
            :key="item.value"
            :label="item.label"
            :value="item.value">
          </el-option>
        </el-select>
      </div>
      <div class="mr20 fl">
        会员类型：<br>
        <el-select v-model="classid" placeholder="请选择">
          <el-option
            v-for="item in types"
            :key="item.value"
            :label="item.label"
            :value="item.value">
          </el-option>
        </el-select>
      </div>
      <div class="mr20 fl">
        本次合同结束时间：<br>
        <el-date-picker
          v-model="contractTime"
          type="daterange"
          range-separator="至"
          start-placeholder="开始日期"
          end-placeholder="结束日期">
        </el-date-picker>
      </div>
      <div class="mr20 fl">
        剩余天数≤：<br>
        <el-select v-model="lastday" placeholder="请选择">
          <el-option
                  v-for="item in lastdays"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value">
          </el-option>
        </el-select>
      </div>
      <div class="mr20 fl">
        <span> </span><br>
        <el-button type="primary" @click="handlequery">查询</el-button>
      </div>
      <div class="mr20 fl" style="margin-right:0;">
        <span> </span><br>
        <el-button type="primary" @click="handleExport" v-loading="exportLoading">导出</el-button>
      </div>
    </div>
    <div class="main">
      <!-- 列表 -->
      <el-table
        :data="tableData"
        v-loading="loading"
        border
      >
        <el-table-column
          prop="id"
          label="会员ID"
          align="center"
        >
        </el-table-column>
        <el-table-column
          prop="qc"
          label="公司名称"
          align="center">
        </el-table-column>
        <el-table-column
          prop="cname"
          align="center"
          label="城市"
        >
        </el-table-column>
        <el-table-column
          prop="start"
          align="center"
          label="本次合同开始时间"
        >
        </el-table-column>
        <el-table-column
          prop="end"
          align="center"
          label="本次合同结束时间"
        >
        </el-table-column>
        <el-table-column
          prop="endoffset"
          align="center"
          label="总天数"
        >
        </el-table-column>
        <el-table-column
          align="center"
          label="剩余天数"
        >
          <template slot-scope="scope">
            <span v-if="scope.row.lastday >= 0">{{scope.row.lastday}}</span>
            <span v-else>——</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="memberstatus"
          align="center"
          label="会员状态"
        >
        </el-table-column>
        <el-table-column
          prop="classid"
          align="center"
          label="会员类型"
        >
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <el-pagination
        class="page"
        @current-change="handleCurrentChange"
        :current-page.sync="currentPage"
        :page-size="20"
        layout="total, prev, pager, next, jumper"
        :total="totals">
      </el-pagination>
    </div>
    <div class="bottom">
      <p>会员状态解释： </p>
      <p>（1）新会员：第一次合作的装修公司。</p>
      <p>（2）到期续费会员：到期续费的装修公司，合同时间连续。</p>
      <p>（3）过期续费会员：上次合同过期后续费的装修公司，两次合同时间不连续。</p>
      <p>（4）即将到期会员：会员剩余天数15天（包含）内的装修公司。</p>
    </div>
  </div>
</template>

<script>
  import { fetchDeadList, fetchFindUser } from '@/api/memberDeadline'
  import { fetchCityList } from '@/api/common'
  import { export_json_to_excel } from '@/excel/Export2Excel'
  export default {
    name: 'memberDeadline',
    data () {
      return {
        loading: true,
        restaurantsId: [],
        restaurantsCity: [],
        company: '',
        companyid: '',
        cs: '',
        cid: '',
        zxComs: [],
        citys: [],
        timeout:  null,
        cityBlurFlag: null,
        comBlurFlag: null,
        options: [
          {
            value: '',
            label: '全部'
          },
          {
            value: '1',
            label: '新会员'
          },
          {
            value: '3',
            label: '即将到期会员'
          },
          {
            value: '4',
            label: '过期会员'
          },
          {
            value: '5',
            label: '到期续费'
          },
          {
            value: '6',
            label: '过期续费'
          }
        ],
        types: [
          {
            value: '',
            label: '全部'
          },
          {
            value: '1',
            label: '普通会员'
          },
          {
            value: '2',
            label: '签返会员'
          }
        ],
        lastdays: [
          {
            value: '',
            label: '请选择'
          },
          {
            value: '10',
            label: '10天'
          },
          {
            value: '15',
            label: '15天'
          },
          {
            value: '20',
            label: '20天'
          },
          {
            value: '25',
            label: '25天'
          },
          {
            value: '30',
            label: '30天'
          }
        ],
        // 会员状态
        type: '3',
        classid: '',
        lastday: '',
        down: '',
        exportLoading: false,
        // 本次合同结束时间
        contractTime: '',
        // 列表数据
        tableData: [],
        // 分页
        currentPage: 1,
        totals: 0,
        exportData: []
      }
    },
    watch: {
      cs(value) {
        if(!value){
          this.cid = ''
          this.cityBlurFlag = null
        }
      },
      company(value) {
        if(!value) {
          this.companyid = ''
          this.comBlurFlag = null
        }
      }
    },
    mounted() {
      this.initcity()
      this.restaurantsCity = this.citys
    },
    created() {
      this.inittablelist()
    },
    methods: {
      // 公司id
      querySearchId(queryString, cb) {
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
          const data = res.data.data[0]
          this.zxComs = data
          cb && cb.call()
        })
      },
      handleIdSelect(item) {
        this.comBlurFlag = item
        this.companyid = item.id
      },
      handleComBlur() {
        if(!this.comBlurFlag) {
          this.companyid = ''
          this.company = ''
        }
      },

      // 城市搜索
      initcity() {
        fetchCityList().then(res =>{
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
      querySearchCity(queryString, cb) {
        var restaurantsCity = this.restaurantsCity;
        var results = queryString ? restaurantsCity.filter(this.createStateFiltercity(queryString)) : restaurantsCity;
        this.cityBlurFlag = null
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => {
          cb(results);
        }, 1000);
      },
      createStateFiltercity(queryString) {
        return (cs) => {
          return (cs.value.toLowerCase().indexOf(queryString.toLowerCase()) > -1);
        };
      },
      handleBlur() {
        if(!this.cityBlurFlag) {
          this.cid = ''
        }
      },
      handleCitySelect(item) {
        this.cityBlurFlag = null
        this.cid = item.cid
      },

      // 查询按钮
      handlequery() {
        this.tableData = []
        this.loading = true
        this.currentPage = 1
        this.inittablelist()
        if (this.tableData = []) {
          this.totals = 0
        }
      },

      // 分页
      handleCurrentChange(val) {
        this.currentPage = val
        this.loading = true
        this.inittablelist()
      },

      // 列表渲染
      inittablelist(val, cb) {
        fetchDeadList({
          company: this.companyid,
          type: this.type,
          classid: this.classid,
          lastday: this.lastday,
          cs: this.cid,
          start: this.contractTime[0],
          end: this.contractTime[1],
          page: this.currentPage
        }).then(res =>{
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if (res.data.data.list.length > 0) {
              this.tableData = res.data.data.list
              this.tableData.forEach((item,index) => {
                if (item.isend === 1) {
                  this.tableData[index]['memberstatus'] = '即将到期会员'
                }else{
                  if(item.mark === 'new') {
                    this.tableData[index]['memberstatus'] = '新会员'
                  }else{
                    if(item.status === 'in'){
                      this.tableData[index]['memberstatus'] = '到期续费'
                    }else if(item.status === 'out'){
                      this.tableData[index]['memberstatus'] = '过期续费'
                    }else{
                      this.tableData[index]['memberstatus'] = '过期会员'
                    }
                  }
                }
                if (item.classid === 3) {
                  this.tableData[index]['classid'] = '普通会员'
                } else if (item.classid === 6) {
                  this.tableData[index]['classid'] = '签返会员'
                }
              })
              this.totals = res.data.data.page.total_number
              cb && cb.call()
              this.loading = false
            }else{
              this.$message.error('未查询到符合要求的数据')
              this.loading = false
            }
          }else{
            this.$message.error(res.data.error_msg)
            this.tableData = []
            this.loading = false
          }
        })
      },

      // 导出
      fetchExportOrders(val, cb) {
        fetchDeadList({
          company: this.companyid,
          type: this.type,
          lastday: this.lastday,
          cs: this.cid,
          start: this.contractTime[0],
          end: this.contractTime[1],
          page: this.currentPage,
          down: 1
        }).then(res =>{
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if (res.data.data.list.length > 0) {
              this.exportData = res.data.data.list
              this.exportData.forEach((item,index) => {
                if (item.isend === 1) {
                  this.exportData[index]['memberstatus'] = '即将到期会员'
                }else{
                  if(item.mark === 'new') {
                    this.exportData[index]['memberstatus'] = '新会员'
                  }else{
                    if(item.status === 'in'){
                      this.exportData[index]['memberstatus'] = '到期续费'
                    }else if(item.status === 'out'){
                      this.exportData[index]['memberstatus'] = '过期续费'
                    }else{
                      this.exportData[index]['memberstatus'] = '过期会员'
                    }
                  }
                }
              })
              cb && cb.call()
              this.exportLoading = false
            }else{
              this.$message.error('未查询到符合要求的数据')
              this.exportLoading = false
            }
          }else{
            this.$message.error(res.data.error_msg)
            this.exportData = []
            this.exportLoading = false
          }
        })
      },
      handleExport() {
        this.exportLoading = true
        const tHeader = [
          '会员ID',
          '公司名称',
          '城市',
          '本次合同开始时间',
          '本次合同结束时间',
          '总天数',
          '剩余天数',
          '会员状态'
        ]
        // 上面设置Excel的表格第一行的标题
        const filterVal = [
          'id',
          'qc',
          'cname',
          'start',
          'end',
          'endoffset',
          'lastday',
          'memberstatus'
        ]
        this.fetchExportOrders('export', () => {
          const list = this.exportData // 把data里的exportData存到list
          this.exportData = []
          const data = this.formatJson(filterVal, list)
          export_json_to_excel(tHeader, data, '会员到期提醒')
          this.exportLoading = false
        })
      },
      formatJson(filterVal, jsonData) {
        return jsonData.map(v => filterVal.map(j => v[j]))
      }
    }
  }
</script>

<style>
.member-deadline .mr20 {
  margin-right: 20px;
  margin-bottom: 20px;
}
.member-deadline {
  padding: 20px;
}
.member-deadline .header {
  background-color: #fff;
  padding: 20px 20px 0;
  box-sizing: border-box;
  border-top: 3px solid #d2d6de;
  overflow: hidden;
}
.member-deadline .main {
  margin-top: 20px;
  background-color: #fff;
  padding: 20px 20px 60px 20px;
  border-top: 3px solid #d2d6de;
  position: relative;
}
.member-deadline .main .page {
  position: absolute;
  left: 50%;
  bottom: 10px;
  transform: translateX(-50%);
}
.member-deadline .bottom {
  margin-top: 20px;
  padding: 20px;
}
.member-deadline .bottom p {
  color: red;
}
.member-deadline .el-date-editor .el-range-separator{
  padding: 0;
}
.member-deadline .el-range-editor--medium.el-input__inner {
  margin-top: 4px;
}
</style>
