<template>
  <div class="nonMemberCity">
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
        是否开站:
        <el-select v-model="hasWebVal" placeholder="请选择">
          <el-option
            v-for="item in hasWebOptions"
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
        <el-button plain @click="handleExportData" v-loading="exportLoading">导出</el-button>
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
          prop="cname"
          label="城市"
        />
        <el-table-column
          align="center"
          label="是否开站"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.is_open_city) === 1 ? '已开站' : '未开站' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="重点系数"
        >
          <template slot-scope="scope">
            {{ scope.row.little | filterRatioText }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="fa"
          label="城市发单量"
        />
        <el-table-column
          align="center"
          prop="fengandzeng"
          label="分单量"
        />
        <el-table-column
          align="center"
          label="相邻城市"
        >
          <template slot-scope="scope">
            <span v-for="item in scope.row.parent_city_list" :key="item.cid" class="near_city">
              {{ item.cname }}
            </span>
            <span v-if="!scope.row.parent_city_list || scope.row.parent_city_list.length === 0">----</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="操作"
        >
          <template slot-scope="scope">
            <span class="link-blue pointer" @click="handleCheckOrder(scope.row)">查看订单</span>
          </template>
        </el-table-column>
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
  import { fetchNonmemberCityList } from '@/api/cityAccount'
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
    name: "nonMemberCity",
    data() {
      return {
        citySelectStr: '',
        citySelectVal: '',
        cityBlurFlag: null,
        hasWebOptions: [{
          value: '',
          label: '请选择'
        }, {
          value: '1',
          label: '已开站'
        }, {
          value: '0',
          label: '未开站'
        }],
        hasWebVal: '',
        date_begin: '',
        date_end: '',
        loading: false,
        tableData: [],
        currentPage: 1,
        pageSize: 20,
        totals: 0,
        citys: [],
        exportLoading: false,
        exportData: [],
        hasSearch: 0
      }
    },
    filters: {
      filterRatioText(val) {
        switch (val) {
          case 0:
            return "A类"
            break
          case 1:
            return "B类"
            break
          case 2:
            return "C类"
            break
          case 3:
            return "D类"
            break
          case 4:
            return "S1类"
            break
          case 5:
            return "S2类"
            break
          default:
            return '----'
            break
        }
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
      this.fetchNonMemberCity()
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
      fetchNonMemberCity() {
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
          // console.log(new Date(new Date(this.date_begin).Format("yyyy-MM-dd")).getTime())
          // console.log(new Date(new Date(this.date_end).Format("yyyy-MM-dd")).getTime())
          if(new Date(new Date(this.date_begin).Format("yyyy-MM-dd")).getTime() > new Date(new Date(this.date_end).Format("yyyy-MM-dd")).getTime()) {
            this.$message.error('开始时间不能大于结束时间')
            this.date_end = ''
            return
          }
        }
        const queryObj = this.handleArguments()
        this.loading = true
        fetchNonmemberCityList(queryObj).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if (res.data.data.list.length <= 0 && this.currentPage > 1) {
              this.currentPage--
              this.fetchNonMemberCity()
            } else {
              this.tableData = []
              this.totals = res.data.data.page.total_number
              this.pageSize = res.data.data.page.page_size
              res.data.data.list.forEach((item, index) => {
                this.tableData.push(item)
              })
              this.loading = false
              if(res.data.data.timestamp) {
                const timeStamp_begin = res.data.data.timestamp[0] * 1000
                const timeStamp_end = res.data.data.timestamp[1] * 1000
                this.date_begin = new Date(timeStamp_begin).getFullYear() + '-' + (new Date(timeStamp_begin).getMonth() + 1) + '-' + new Date(timeStamp_begin).getDate()
                this.date_end = new Date(timeStamp_end).getFullYear() + '-' + (new Date(timeStamp_end).getMonth() + 1) + '-' + new Date(timeStamp_end).getDate()
              }
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
      fetchExportNonMemberCity(val, cb) {
        const queryObj = this.handleArguments(val)
        fetchNonmemberCityList(queryObj).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if (res.data.data.list.length > 0) {
              res.data.data.list.forEach((item, index) => {
                item.openCity = item.is_open_city === 1 ? '已开站' : '未开站'
                item.iptRatio = this.handleFilterRatio(item.little)
                const nearCity = []
                if(item.parent_city_list) {
                  for(const p in item.parent_city_list) {
                    nearCity.push(item.parent_city_list[p].cname)
                  }
                  item.nearCity = nearCity.join(',')
                }
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
        let time_begin = localStorage.getItem('nonMemberCity_dateBegin')
        let time_end = localStorage.getItem('nonMemberCity_dateEnd')
        let page = localStorage.getItem('nonMemberCity_page')
        let isSearch = localStorage.getItem('nonMemberCity_search')
        let openCity = localStorage.getItem('nonMemberCity_openCity')
        let cs = ''
        if(isSearch) {
          cs = localStorage.getItem('nonMemberCity_cs')
          this.citySelectVal = cs
        }else{
          cs = ''
        }
        if(openCity) {
          this.hasWebVal = String(openCity)
        }
        page ? this.currentPage = parseInt(page) : ''
        localStorage.removeItem('nonMemberCity_cs')
        localStorage.removeItem('nonMemberCity_dateBegin')
        localStorage.removeItem('nonMemberCity_dateEnd')
        localStorage.removeItem('nonMemberCity_page')
        localStorage.removeItem('nonMemberCity_search')
        localStorage.removeItem('nonMemberCity_openCity')
        const queryObj = {
          start: '', // 发布查询开始时间
          end: '', // 发布查询结束时间
          cs: this.citySelectVal ? this.citySelectVal : cs, // 查询城市ID
          isopen: this.hasWebVal, // 是否开站
          orderby: '', // 签单查询开始时间
          down: '', // 签单查询结束时间
          p: this.currentPage, // 分页 页码  默认1
          psize: 20
        }
        if (val === 'export') {
          queryObj.down = 1
        }
        if(this.date_begin) {
          time_begin = new Date(this.date_begin).getFullYear() + '-' + (new Date(this.date_begin).getMonth() + 1) + '-' + new Date(this.date_begin).getDate()
        }
        if(this.date_end) {
          time_end = new Date(this.date_end).getFullYear() + '-' + (new Date(this.date_end).getMonth() + 1) + '-' + new Date(this.date_end).getDate()
        }
        queryObj.start = time_begin
        queryObj.end = time_end
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
          '城市',
          '是否开站',
          '重点系数',
          '城市发单量',
          '分单量',
          '相邻城市'
        ]
        // 上面设置Excel的表格第一行的标题
        const filterVal = [
          'cname',
          'openCity',
          'iptRatio',
          'fa',
          'feng',
          'nearCity'
        ]
        this.fetchExportNonMemberCity('export', () => {
          // 上面的index、phone_Num、school_Name是tableData里对象的属性
          const list = this.exportData // 把data里的exportData存到list
          this.exportData = []
          const data = this.formatJson(filterVal, list)
          export_json_to_excel(tHeader, data, '无会员城市')
          this.exportLoading = false
        })
      },
      formatJson(filterVal, jsonData) {
        return jsonData.map(v => filterVal.map(j => v[j]))
      },
      handleSearch() {
        this.hasSearch = 1
        this.fetchNonMemberCity()
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
        this.fetchNonMemberCity()
      },
      handleCurrentChange(val) {
        this.currentPage = val
        this.fetchNonMemberCity()
      },
      handleCheckOrder(obj) {
        let time_begin = ''
        let time_end = ''
        if(this.date_begin) {
          time_begin = new Date(this.date_begin).getFullYear() + '-' + (new Date(this.date_begin).getMonth() + 1) + '-' + new Date(this.date_begin).getDate()
        }
        if(this.date_end) {
          time_end = new Date(this.date_end).getFullYear() + '-' + (new Date(this.date_end).getMonth() + 1) + '-' + new Date(this.date_end).getDate()
        }
        // 设置本地存储
        localStorage.setItem('nonMemberCity_cs', this.citySelectVal)
        localStorage.setItem('nonMemberCity_dateBegin', time_begin)
        localStorage.setItem('nonMemberCity_dateEnd', time_end)
        localStorage.setItem('nonMemberCity_openCity', this.hasWebVal)
        localStorage.setItem('nonMemberCity_page', this.currentPage)
        if(this.hasSearch) {
          localStorage.setItem('nonMemberCity_search', this.hasSearch)
        }

        this.$router.push({
          path: 'nonMemberCityOrder',
          query: {
            cs: !this.citySelectVal ? obj.cid : this.citySelectVal,
            begin: time_begin,
            end: time_end
          }
        })
      },
      handleFilterRatio(val) {
        switch (val) {
          case 0:
            return "A类"
            break
          case 1:
            return "B类"
            break
          case 2:
            return "C类"
            break
          case 3:
            return "D类"
            break
          case 4:
            return "S1类"
            break
          case 5:
            return "S2类"
            break
          default:
            return '----'
            break
        }
      },
      fixCurrrentPage() {
        // 修复带参数返回页码不正确的情况
        this.$nextTick(() => {
          const page = document.querySelector('.el-pager').getElementsByTagName('li')
          const len = page.length
          for(let i = 0; i < len; i++) {
            const cp = parseInt(page[i].innerText)
            page[i].className = 'number'
            if(cp === this.currentPage) {
              page[i].className = 'number active'
            }
          }
        })
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
  .nonMemberCity {
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
    .near_city::after{
      content: '、'
    }
    .near_city:last-child::after{
      content: ''
    }
  }
</style>
