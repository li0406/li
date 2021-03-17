<template>
  <div class="license-check-list">
    <div class="header">
      <div class="mr20 fl">
        公司名称/ID：<br>
        <el-autocomplete
          v-model="company"
          :fetch-suggestions="querySearchId"
          placeholder="请输入公司名称或ID"
          @select="handleIdSelect"
        ></el-autocomplete>
      </div>
      <div class="mr20 fl">
        城市：<br>
        <el-select v-model="cs" filterable placeholder="城市" clearable>
          <el-option
            v-for="item in citysOption"
            :key="item.value"
            :label="item.cname"
            :value="item.cid"
          />
        </el-select>
      </div>
      <div class="mr20 fl">
        审核时间：<br>
        <el-date-picker
          v-model="audittimec"
          type="daterange"
          range-separator="至"
          start-placeholder="开始日期"
          end-placeholder="结束日期">
        </el-date-picker>
      </div>
      <div class="mr20 fl">
        <span> </span><br>
        <el-button type="primary" @click="handleQuery">查询</el-button>
      </div>
    </div>
    <div class="main">
      <el-table
        :data="tableData"
        v-loading="loading"
        border
        style="width: 100%">
        <el-table-column
          prop="company_id"
          label="ID"
          align="center"
          width="120">
        </el-table-column>
        <el-table-column
          prop="qc"
          label="公司名称"
          align="center">
        </el-table-column>
        <el-table-column
          prop="cname"
          align="center"
          label="城市">
        </el-table-column>
        <el-table-column
          prop="type_name"
          align="center"
          label="审核项目">
        </el-table-column>
        <el-table-column
          align="center"
          label="查看"
          width="120">
          <template slot-scope="scope">
            <el-button type="primary" size="mini" @click="preview(scope.row, 'child')">点击预览</el-button>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="工商总局查询"
          width="120">
          <template slot-scope="scope">
            <el-button type="primary" size="mini" @click="preview(scope.row, 'gszj')">点击预览</el-button>
          </template>
        </el-table-column>
        <el-table-column
          prop="state_name"
          align="center"
          label="初审状态">
        </el-table-column>
        <el-table-column
          align="center"
          label="备注">
          <template slot-scope="scope">
            {{ scope.row.remark ? scope.row.remark : '----'}}
          </template>
        </el-table-column>
        <el-table-column
          prop="audit_date"
          align="center"
          label="审核时间">
        </el-table-column>
        <el-table-column
          prop="zizhi"
          align="center"
          label="操作"
          width="100">
          <template slot-scope="scope">
            <el-button type="primary" size="mini" @click="handleWidthdraw(scope.row)">撤回</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <el-pagination
        class="page"
        @current-change="handleCurrentChangec"
        :current-page.sync="currentPage"
        :page-size="20"
        layout="total, prev, pager, next, jumper"
        :total="totals">
      </el-pagination>
      <!--图片预览-->
      <el-dialog
        :visible.sync="dialogVisiblePreview"
        :close-on-click-modal="false"
        width="60%">
        <div class="img" v-for="item in previewTemp" :key="item"><img :src="item" alt="" style="max-width: 100%; max-height: 100%; display: block; margin: 0 auto;"></div>
      </el-dialog>
      <!--审核/重审-->
      <el-dialog
        title="撤回"
        :visible.sync="dialogVisibleCheck"
        width="30%"
        @close="handleClose">
        <span>确认撤回审核结果？撤回后将重新进入待审核！</span>
        <span slot="footer" class="dialog-footer">
          <el-button @click="this.handleClose">取 消</el-button>
          <el-button type="primary" @click="handleToServer">确 定</el-button>
        </span>
      </el-dialog>
    </div>
  </div>
</template>

<script>
  import { fetchFindUser } from '@/api/memberDeadline'
  import { fetchLicenseList, fetchResetCheck } from '@/api/licenseManager'
  import { fetchCityList } from '@/api/common'
  export default {
    name: 'licenseCheckList',
    props: {
      startTag: {
        type: String,
        default: ''
      }
    },
    data() {
      return {
        tableData: [],
        loading: true,
        audittimec: '',
        totals: 0,
        currentPage: 1,
        dialogVisiblePreview: false,
        dialogVisibleCheck: false,
        tempData: null,
        citys: [],
        cs: '',
        cid: '',
        zxComs: [],
        timeout:  null,
        cityBlurFlag: null,
        comBlurFlag: null,
        restaurantsCity: [],
        company: '',
        companyid: '',
        previewTemp: [],
        citysOption: []
      }
    },
    created() {
      this.getCheckList()
      this.querySearchCity()
    },
    watch: {
      startTag(val) {
        console.log('startTag')
        console.log(val)
        this.getDataList(val)
      },
      citylist(val) {
        console.log('cityList')
        console.log(val)
      }
    },
    methods: {
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
      querySearchCity() {
        fetchCityList().then(res =>{
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            const data = res.data.data[0]
            data.forEach((item, index) => {
              this.citysOption.push(item)
            })
          } else {
            this.citys = []
          }
        })
      },
      handleCitySelect(item) {
        this.cityBlurFlag = null
        this.cid = item.cid
      },
      getDataList(val) {
        if(val === 'third') {
          this.getCheckList()
        }
      },
      preview(obj, tag) {
        const tempData = obj[tag]
        this.previewTemp = []
        for(let p in tempData) {
          if(tempData[p]) {
            this.previewTemp.push(this.$qzconfig.oss_img_host + tempData[p])
          }
        }
        this.dialogVisiblePreview = true
      },
      getCheckList(val, cb) {
        fetchLicenseList({
          actfrom: 'record',
          company_keyword: this.companyid,
          cs: this.cs,
          date_begin: this.audittimec[0],
          date_end: this.audittimec[1],
          page: this.currentPage
        }).then(res =>{
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if (res.data.data.list.length > 0) {
              this.tableData = res.data.data.list
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
          this.loading = false
        })
      },
      handleToServer() {
        fetchResetCheck({
          company_id: this.tempData.company_id,
          type: this.tempData.type
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              message: '重置成功',
              type: 'success'
            })
            this.dialogVisibleCheck = false
            this.getCheckList()
          }else{
            this.$message.error(res.data.error_msg)
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
        })
      },
      handleWidthdraw(obj) {
        this.tempData = obj
        this.dialogVisibleCheck = true
      },
      handleQuery() {
        this.tableData = []
        this.loading = true
        this.currentPage = 1
        this.getCheckList()
      },
      handleCurrentChangec(val) {
        this.currentPage = val
        this.loading = true
        this.getCheckList()
      },
      handleClose() {
        this.dialogVisibleCheck = false
      }
    }
  }
</script>

<style scoped>
  .mr20 {
    margin-right: 20px;
  }
  .license-check .header {
    background-color: #fff;
    padding: 20px;
    box-sizing: border-box;
    height: 100px;
    border-top: 3px solid #d2d6de;
  }
  .license-check .main {
    margin-top: 20px;
    background-color: #fff;
    padding: 20px 20px 60px 20px;
    border-top: 3px solid #d2d6de;
    position: relative;
  }
  .license-check .main .page {
    position: absolute;
    left: 50%;
    bottom: 10px;
    transform: translateX(-50%);
  }
</style>
