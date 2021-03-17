<template>
  <div class="license-statistic">
    <!-- 顶部搜索 -->
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
        营业执照状态：<br>
        <el-select v-model="zhizhao" placeholder="请选择">
          <el-option
            v-for="item in options"
            :key="item.value"
            :label="item.label"
            :value="item.value">
          </el-option>
        </el-select>
      </div>
      <div class="mr20 fl">
        装修资质状态：<br>
        <el-select v-model="zizhi" placeholder="请选择">
          <el-option
            v-for="item in options"
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
    </div>
    <div class="main">
      <!-- 列表 -->
      <el-table
        :data="tableData"
        v-loading="loading"
        border
        style="width: 100%">
        <el-table-column
          prop="id"
          label="ID"
          align="center"
        >
        </el-table-column>
        <el-table-column
          prop="qc"
          label="公司名称"
          align="center">
        </el-table-column>
        <el-table-column
          prop="is_member"
          align="center"
          label="是否会员"
        >
        </el-table-column>
        <el-table-column
          prop="cname"
          align="center"
          label="城市"
        >
        </el-table-column>
        <el-table-column
          prop="dev_manage"
          align="center"
          label="城市负责人"
        >
        </el-table-column>
        <el-table-column
          prop="yystate"
          align="center"
          label="营业执照状态"
        >
        </el-table-column>
        <el-table-column
          prop="zxstate"
          align="center"
          label="装修资质状态"
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
  </div>
</template>

<script>
  import { fetchFindUser, fetchLicenseStatistics } from '@/api/licenseManager'
  import { fetchCityList } from '@/api/common'

  export default {
    name: 'licenseStatistic',
    data() {
      return {
        loading: true,
        // 搜索部分模糊搜索
        restaurantsId: [],
        restaurantsCity: [],
        company: '',
        companyid: '',
        city: '',
        cs: '',
        cid: '',
        zxComs: [],
        citys: [],
        timeout:  null,
        cityBlurFlag: null,
        comBlurFlag: null,
        // 搜索部分执照和资质
        options: [
          {
            value: '',
            label: '请选择'
          },
          {
            value: '1',
            label: '待上传'
          },
          {
            value: '2',
            label: '待审核'
          },
          {
            value: '4',
            label: '已通过'
          },
          {
            value: '5',
            label: '未通过'
          }
        ],
        zhizhao: '',
        zizhi: '',
        // 列表数据
        tableData: [],
        // 分页
        currentPage: 1,
        totals: 0
      };
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
      // 会员id
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

      // 列表渲染
      inittablelist(val, cb) {
        fetchLicenseStatistics({
          company: this.companyid,
          city: this.cid,
          ying: this.zhizhao,
          zhuang: this.zizhi,
          page: this.currentPage
        }).then(res =>{
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if (res.data.data.list.length > 0) {
              this.tableData = res.data.data.list
              this.tableData.forEach((item,index) => {
                // 营业执照
                if (item.yystate === 1 || item.yystate === 2 || item.yystate === 3) {
                  this.tableData[index].yystate = '待审核'
                }else if (item.yystate === 4){
                  this.tableData[index].yystate = '通过'
                }else if (item.yystate === 5){
                  this.tableData[index].yystate = '不通过'
                }else{
                  this.tableData[index].yystate = '待上传'
                }
                // 资质
                if (item.zxstate === 1 || item.zxstate === 2 || item.zxstate === 3) {
                  this.tableData[index].zxstate = '待审核'
                }else if (item.zxstate === 4){
                  this.tableData[index].zxstate = '通过'
                }else if (item.zxstate === 5){
                  this.tableData[index].zxstate = '不通过'
                }else{
                  this.tableData[index].zxstate = '待上传'
                }
                // 是否会员
                if (item.is_member === 1) {
                  this.tableData[index].is_member = '是'
                }else{
                  this.tableData[index].is_member = '否'
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
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
        })
      },

      // 分页
      handleCurrentChange(val) {
        this.currentPage = val
        this.loading = true
        this.inittablelist()
      }
    }
  }
</script>

<style scoped>
.mr20 {
  margin-right: 20px;
}
.license-statistic {
  padding: 20px;
}
.license-statistic .header {
  background-color: #fff;
  padding: 20px;
  box-sizing: border-box;
  height: 100px;
  border-top: 3px solid #d2d6de;
}
.license-statistic .main {
  margin-top: 20px;
  background-color: #fff;
  padding: 20px 20px 60px 20px;
  border-top: 3px solid #d2d6de;
  position: relative;
}
.license-statistic .main .page {
  position: absolute;
  left: 50%;
  bottom: 10px;
  transform: translateX(-50%);
}
</style>
