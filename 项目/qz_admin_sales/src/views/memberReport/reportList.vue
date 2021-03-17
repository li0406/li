<template>
  <div class="member-report">
    <div class="search">
      <div class="clearfix">
        <div class="yixiang fl mr20">
          公司名称：<br>
          <el-input v-model="companyName" placeholder="请输入"></el-input>
        </div>
        <div class="fl mr20">
          负责人：<br>
          <el-input v-model="manager" placeholder="请输入"></el-input>
        </div>
        <div class="fl mr20">
          城市：<br>
          <el-input v-model="citySelectStr" placeholder="请输入"></el-input>
        </div>
        <div class="fl mr20">
          合作类型：<br>
          <el-select v-model="cooperation" filterable placeholder="请选择">
            <el-option
              v-for="item in cptOptions"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            >
            </el-option>
          </el-select>
        </div>
        <div class="fl mr20">
          新/老：<br>
          <el-select v-model="isMemberVal" filterable placeholder="请选择">
            <el-option
              v-for="item in isMemberOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            >
            </el-option>
          </el-select>
        </div>
        <div class="fl mr20">
          保存时间: <br>
          <div class="block">
            <el-date-picker
              v-model="saveTime"
              type="daterange"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期">
            </el-date-picker>
          </div>
        </div>
        <div class="fl">
          <br>
          <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
        </div>
      </div>
      <div><br>
        <el-button type="success" @click="handleAdd">添加</el-button>
      </div>
    </div>
    <div class="qian-main">
      <el-table
        v-loading="loading"
        :data="tableData"
        border>
        <el-table-column
          align="center"
          prop="tableIndex"
          width="50"
          label="序号">
        </el-table-column>
        <el-table-column
          align="center"
          prop="company_name"
          label="公司名称"/>
        <el-table-column
          align="center"
          prop="cooperation_type_name"
          label="合作类型"
        />
        <el-table-column
          align="center"
          width="60"
          label="城市">
          <template slot-scope="scope">
            {{ scope.row.city_name ? scope.row.city_name : '----' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="单倍/几倍">
          <template slot-scope="scope">
            <span v-if="scope.row.cooperation_type == 14 || scope.row.cooperation_type == 15">标杆会员（保产值）</span>
            <span v-else>{{ (parseFloat(scope.row.viptype) === 0 || !scope.row.viptype) && (scope.row.viptype != '0%') ? '----' : scope.row.viptype }}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="新/老">
          <template slot-scope="scope">
            {{ scope.row.is_new | isNewFilter }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="联系人/姓名"
        >
          <template slot-scope="scope">
            {{ scope.row.company_contact ? scope.row.company_contact : '----' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="电话/手机号"
        >
          <template slot-scope="scope">
            {{ scope.row.company_contact_phone ? scope.row.company_contact_phone : '----' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="微信号">
          <template slot-scope="scope">
            {{ scope.row.company_contact_weixin ? scope.row.company_contact_weixin : '----' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="账号">
          <template slot-scope="scope">
            {{ scope.row.account ? scope.row.account : '----' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="company_name"
          label="本次会员时间/时间">
          <template slot-scope="scope">
            {{ scope.row.current_contract_start }} -- {{ scope.row.current_contract_end }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="状态">
          <template slot-scope="scope">
            <span :class="classFilter(scope.row.status)">{{ scope.row.status | statusFilter }}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="create_time"
          width="100"
          label="保存时间"/>
        <el-table-column
          align="center"
          prop="saler"
          label="负责人"/>
        <el-table-column
          align="center"
          label="操作"
          width="200">
          <template slot-scope="scope">
            <span class="edit-btn" v-if="parseInt(scope.row.status) === 1 || parseInt(scope.row.status) === 4 || parseInt(scope.row.status) === 6 || parseInt(scope.row.status) === 7 || parseInt(scope.row.status) === 11" @click="handleEdit(scope.row)">编辑</span>
            <span class="del-btn" v-if="parseInt(scope.row.status) === 1" @click="handleDel(scope.row)">删除</span>
            <span class="check-btn" @click="handleCheck(scope.row)">查看</span>
            <span class="widthdraw-btn" v-if="parseInt(scope.row.status) === 2" @click="handleWithdraw(scope.row)">撤回</span>
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
  </div>
</template>

<script>
import { fetchMemberReportList, fetchMemberReportDel, fetchMemberReportStatus, fetchOptions } from '@/api/memberReport'
import { fetchCityList } from '@/api/common'
import { fortmatDate } from '@/utils/index'

export default {
  name: 'reportList',
  data() {
    return {
      companyName: '',
      manager: '',
      cooperation: '',
      cptOptions: [],
      isMemberVal: '0',
      isMemberOptions: [{
        value: '0',
        label: '请选择'
      }, {
        value: '1',
        label: '新会员'
      }, {
        value: '2',
        label: '老会员'
      }],
      saveTime: '',
      citySelectStr: '',
      citySelectVal: '',
      citys: [],
      cityBlurFlag: null,
      tableData: [{
        id: 1
      }],
      currentPage: 1,
      totals: 0,
      pageSize: 20,
      // 交互
      loading: false
    }
  },
  filters: {
    isNewFilter(val) {
      switch (val) {
        case 0:
          return '----'
          break
        case 1:
          return '新会员'
          break
        case 2:
          return '老会员'
          break
        default:
          return '----'
      }
    },
    statusFilter(val) {
      switch (val) {
        case 1:
          return '待提交'
          break
        case 2:
          return '待审核'
          break
        case 3:
          return '审核通过/待客服上传'
          break
        case 4:
          return '未通过'
          break
        case 5:
          return '客服审核通过'
          break
        case 6:
          return '客服未通过，普通信息更改'
          break
        case 7:
          return '客服未通过，需要重新审核'
          break
        case 8:
          return '客服完成上传'
          break
        case 9:
          return '客服暂停'
          break
        case 10:
          return '待客服补充'
          break
        case 11:
          return '客服补充完成'
          break
        default:
          return '----'
      }
    }
  },
  created() {
    this.fetchOptions()
    this.getMemberReportList()
  },
  methods: {
    fetchOptions() {
      fetchOptions().then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          const datas = res.data.data.cooperation_type_list
          const arr = [
            {
              id: '',
              name: '请选择'
            }
          ]
          this.cptOptions = [...arr, ...datas]
        } else {
          this.$message.error(res.data.error_msg)
        }
      })
    },
    handleArguments() {
      let time_start = ''
      let time_end = ''
      if (this.saveTime) {
        time_start = fortmatDate(this.saveTime[0])
        time_end = fortmatDate(this.saveTime[1])
      }
      const queryObj = {
        company: this.companyName, // 公司名称
        saler: this.manager, // 负责人
        cname: this.citySelectStr, // 城市
        cooperation_type: this.cooperation, // 合作类型
        is_new: this.isMemberVal, // 新/老会员
        start: time_start, // 保存时间-开始
        end: time_end, // 保存时间-结束
        p: this.currentPage, // 分页 页码  默认1
        psize: 20
      }
      return queryObj
    },
    getMemberReportList() {
      const queryObj = this.handleArguments()
      this.loading = true
      let tableIndex = 1
      fetchMemberReportList(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (res.data.data.list.length <= 0 && this.currentPage > 1) {
            this.currentPage--
            this.getMemberReportList()
          } else {
            this.tableData = []
            this.totals = res.data.data.page.total_number
            this.pageSize = res.data.data.page.page_size
            if (!this.saveTime) {
              this.saveTime = [res.data.data.options.start, res.data.data.options.end]
            }
            res.data.data.list.forEach((item, index) => {
              item.tableIndex = (this.currentPage - 1) * 20 + tableIndex
              if (item.cooperation_type == 6) {
                item.viptype = item.back_ratio
              }
              this.tableData.push(item)
              tableIndex++
            })
            this.loading = false
          }
        } else {
          // this.tableData = []
          this.totals = 0
          this.loading = false
        }
      })
    },
    handleSearch() {
      this.currentPage = 1
      this.getMemberReportList()
    },
    handleEdit(obj) {
      if (!obj || !obj.id) {
        return
      }
      const queryObj = {
        id: obj.id, // 数据id
        type: obj.cooperation_type // 1,2,3代表装修会员，4 三维家， 5 erp
      }
      if (parseInt(obj.status) === 6) {
        // 无需重新审核直接编辑，对应编辑页面好多参数不可修改
        queryObj.status = obj.status
      }
      this.$router.push({
        path: '/memberReport/add',
        query: queryObj
      })
    },
    handleDel(obj) {
      this.$confirm('确认删除该公司信息?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.handleDelAjax(obj)
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消删除'
        })
      })
    },
    handleDelAjax(obj) {
      fetchMemberReportDel({
        cooperation_type: obj.cooperation_type,
        id: obj.id
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.$message({
            type: 'success',
            message: '删除成功'
          })
          this.getMemberReportList()
        } else {
          this.$message.error(res.data.error_code)
        }
      }).catch(res => {
        this.$message.error('网络异常，请稍后再试')
      })
    },
    handleCheck(obj) {
      const { href } = this.$router.resolve({
        path: '/memberReport/detail',
        query: {
          id: obj.id,
          type: obj.cooperation_type
        }
      })
      window.open(href, '_blank')
    },
    handleWithdraw(obj) {
      this.$confirm('确认撤回公司信息?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.handleWithdrawAjax(obj)
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '已取消撤回'
        })
      })
    },
    handleWithdrawAjax(obj) {
      fetchMemberReportStatus({
        cooperation_type: obj.cooperation_type,
        status: 1,
        id: obj.id
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.$message({
            type: 'success',
            message: '撤回成功'
          })
          this.getMemberReportList()
        } else {
          this.$message.error(res.data.error_msg)
        }
      }).catch(res => {
        this.$message.error('网络异常，请稍后再试')
      })
    },
    handleAdd() {
      this.$router.push({
        path: '/memberReport/add'
      })
    },
    classFilter(val) {
      switch (val) {
        case 1:
          return 'c3'
          break
        case 2:
          return 'c2'
          break
        case 3:
          return 'c5'
          break
        case 4:
          return 'c1'
          break
        case 5:
          return 'c5'
          break
        case 6:
          return 'c1'
          break
        case 7:
          return 'c1'
          break
        case 8:
          return 'c5'
          break
        case 9:
          return 'c1'
          break
        case 10:
          return 'c5'
          break
        case 11:
          return 'c6'
          break
        default:
          return ''
      }
    },
    // 分页处理
    handleSizeChange() {
      this.currentPage = 1
      this.getMemberReportList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.getMemberReportList()
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
  .member-report {
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

    .search {
      line-height: 36px;
    }

    .el-select {
      margin-top: 0px;
    }

    .c1 {
      color: #FF0000;
    }

    .c2 {
      color: #FF33CC;
    }

    .c3 {
      color: #FF6600;
    }

    .c4 {
      color: #008000;
    }

    .c5 {
      color: #339999;
    }

    .c6 {
      color: #409eff;
    }

    .edit-btn {
      color: #e6a23c;
      cursor: pointer;
    }

    .supplement-btn {
      color: #008000;
      cursor: pointer;
    }

    .del-btn {
      color: #f56c6c;
      cursor: pointer;
    }

    .check-btn {
      color: #409eff;
      cursor: pointer;
    }

    .widthdraw-btn {
      color: #67c23a;
      cursor: pointer;
    }
  }
</style>
