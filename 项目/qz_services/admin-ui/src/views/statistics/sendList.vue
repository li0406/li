<template>
  <div class="member-order">
    <div class="search clearfix">
      <div class="city fl mr20">
        项目应用名: <br>
        <el-select v-model="typeVal" placeholder="请选择">
          <el-option
            v-for="item in typeOptions"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </div>
      <div class="yixiang fl mr20">
        通道网关：<br>
        <el-select v-model="gatewayVal" placeholder="请选择">
          <el-option
            v-for="item in gatewayOptions"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </div>
      <div class="fl mr20">
        提交状态：<br>
        <el-select v-model="statusVal" placeholder="请选择">
          <el-option
            v-for="item in statusOptions"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </div>
      <div class="fl mr20">
        提交时间：<br>
        <el-date-picker
          v-model="submitTimeVal"
          value-format="yyyy-MM-dd"
          type="date"
          placeholder="选择日期">
        </el-date-picker>
      </div>
      <div class="fl mr20">
        手机号：<br>
        <el-input v-model="phone" placeholder="请输入手机号"></el-input>
      </div>
      <div class="fl mr20">
        模板关键字：<br>
        <el-input v-model="tplText" placeholder="请输入模板关键字"></el-input>
      </div>
      <div class="fr">
        <br>
        <el-button type="primary" icon="el-icon-search" @click="handleSearch">搜索</el-button>
        <!--<el-button plain @click="handleExport" v-loading="exportLoading">导出</el-button>-->
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
          prop="tel"
          label="手机号"
        />
        <el-table-column
          align="center"
          prop="content"
          label="发送内容"
        />
        <el-table-column
          align="center"
          prop="create_time"
          label="提交时间"
        />
        <el-table-column
          align="center"
          prop="module"
          label="项目应用名称"
        />
        <el-table-column
          align="center"
          label="发送位置"
        >
          <template slot-scope="scope">
            {{ scope.row.url ? scope.row.url : '----' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="sign"
          label="签名"
        />
        <el-table-column
          align="center"
          prop="template_id"
          label="模板ID"
        />
        <el-table-column
          align="center"
          prop="gateway"
          label="通道网关"
        />
        <el-table-column
          align="center"
          label="提交状态"
        >
          <template slot-scope="scope">
            {{ parseInt(scope.row.status) === 1 ? '成功' : '失败' }}
          </template>
        </el-table-column>
        <el-table-column
          prop="body"
          align="center"
          label="通道网关信息"
        >
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
  </div>
</template>

<script>
import { fetchSmsSendList, fetchSmsSendListExport } from '@/api/statistic'
import { fetchFilterOptions } from '@/api/smsTpl'
import { fetchApplyList } from '@/api/projectApply'
import { export_json_to_excel } from '@/excel/Export2Excel'
import { isEmptyObject } from '@/utils/index'
export default {
  name: 'MemberOrder',
  data() {
    return {
      typeVal: '0',
      typeOptions: [{
        value: '0',
        label: '请选择'
      }],
      gatewayVal: '0',
      gatewayOptions: [{
        value: '0',
        label: '请选择'
      }],
      statusVal: '0',
      statusOptions: [{
        value: '0',
        label: '请选择'
      },{
        value: '1',
        label: '成功'
      },{
        value: '2',
        label: '失败'
      }],
      submitTimeVal: '',
      phone: '',
      tplText: '',
      tableData: [],
      currentPage: 1,
      loading: false,
      exportLoading: false,
      totals: 0,
      pageSize: 20,
      exportData: []
    }
  },
  created() {
    this.getFilterOption()
    this.getApplication()
    this.getSmsSendList()
  },
  methods: {
    getApplication() {
      fetchApplyList().then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if(res.data.data && res.data.data.list && res.data.data.list.length > 0) {
            const data = res.data.data.list
            data.forEach(item => {
              this.typeOptions.push({
                value: item.id,
                label: item.name
              })
            })
          }
        }
      })
    },
    getFilterOption() {
      fetchFilterOptions().then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if(res.data.data.gateway_list && res.data.data.gateway_list.length > 0) {
            res.data.data.gateway_list.forEach(item => {
              this.gatewayOptions.push({
                value: item.id,
                label: item.name
              })
            })
          }
        }else{
          this.$message.error(res.data.error_code)
        }
      }).catch(res => {
        this.$message.error('网络异常，请稍后再试 ')
      })
    },
    getSmsSendList() {
      const queryObj = this.handleArguments()
      this.loading = true
      this.tableData = []
      fetchSmsSendList(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if(parseInt(isEmptyObject(res.data.data)) > 0){
            const data = res.data.data.list
            this.tableData = data
            this.totals = res.data.data.page.total_number
          }else{
            if(this.currentPage > 1) {
              this.currentPage--
              this.getSmsSendList()
            }
            this.totals = 0
          }
          this.loading = false
        } else {
          this.tableData = []
          this.totals = 0
          this.loading = false
        }
      }).catch(res => {
          this.loading = false
            this.$message.error('网络异常，请稍后再试')
      })
    },
    handleArguments(val) {
      const queryObj = {
        module: this.typeVal,
        gateway: this.gatewayVal,
        status: this.statusVal,
        date: this.submitTimeVal,
        tel: this.phone,
        content: this.tplText,
        page: this.currentPage
      }
      return queryObj
    },
    handleSearch() {
        this.currentPage = 1
      this.getSmsSendList()
    },
    handleExport() {
      fetchSmsSendListExport().then(res => {
        const blob = new Blob([res.data], {
          type: 'application/vnd.ms-excel',
          crossOrigin: 'Anonymous'
        })
        const objectUrl = URL.createObjectURL(blob)
        window.location.href = objectUrl
      }).catch(err => {
        console.log(err)
      })
    },
    handleSizeChange() {
      this.currentPage = 1
      this.getSmsSendList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.getSmsSendList()
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
  .member-order {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
    .category{
      margin-bottom: 20px;
    }
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
  }
</style>
