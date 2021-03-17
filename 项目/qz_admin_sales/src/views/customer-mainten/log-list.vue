<template>
  <div class="log-list">
    <!-- 日志中的上方数据表格 -->
    <div class="rizhi-jilu" v-loading="loading">
      <!-- 第一个表格 -->
      <el-table
        class="elTable"
        :data="tableDetail"
        border
        style="width: 100%"
      >
        <el-table-column
          align="center"
          label="会员ID"
        >
          <template slot-scope="scope">
            <span>{{ parseInt(scope.row.user_id) === 0 ? '----' : scope.row.user_id }}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="company_name"
          label="公司名称"
        />
        <el-table-column
          align="center"
          label="城市/区县"
        >
          <template slot-scope="scope">
            {{ scope.row.cname }}/{{ scope.row.aname }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="联系人"
          width="160"
        >
          <template slot-scope="scope">
            <div v-for="(item, index) in scope.row.user_name" :key="index">
              <p>{{ item ? item : '——' }}</p>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="手机"
          width="160"
        >
          <template slot-scope="scope">
            <div v-for="(item, index) in scope.row.user_tel" :key="index">
              <p>{{ item ? item : '——' }}</p>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="微信"
          width="160"
        >
          <template slot-scope="scope">
            <div v-for="(item, index) in scope.row.user_wx" :key="index">
              <p>{{ item ? item : '——' }}</p>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="QQ"
          width="160"
        >
          <template slot-scope="scope">
            <div v-for="(item, index) in scope.row.user_qq" :key="index">
              <p>{{ item ? item : '——' }}</p>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="address"
          label="地址"
        />
        <el-table-column
          align="center"
          width="100"
          label="意向"
        >
          <template slot-scope="scope">
            <span>{{ scope.row.intention_text }}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="is_new_text"
          label="客户类型"
          width="80"
        />
      </el-table>
      <!-- 第二个表格 -->
      <el-table
        class="mt20"
        :data="contractTime"
        border
      >
        <el-table-column
          align="center"
          label="总合同开始时间"
        >
          <template slot-scope="scope">
            {{ scope.row.allStartTime ? scope.row.allStartTime : "----" }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="allEndTime"
          label="总合同结束时间"
        >
          <template slot-scope="scope">
            {{ scope.row.allEndTime ? scope.row.allEndTime : "----" }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="本次合同开始时间"
        >
          <template slot-scope="scope">
            {{ scope.row.theStartTime ? scope.row.theStartTime : "----" }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="本次合同结束时间"
        >
          <template slot-scope="scope">
            {{ scope.row.theEndTime ? scope.row.theEndTime : "----" }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="尾款时间"
        >
          <template slot-scope="scope">
            {{ scope.row.balanceTime ? scope.row.balanceTime : "----" }}
          </template>
        </el-table-column>
      </el-table>
    </div>
    <!-- 日志中的下方联系详情表格 -->
    <div class="rizhi-details mt20">
      <!-- search查询 -->
      <div class="search-list">
        <div class="fl mr20">
          日期：<br>
          <el-select v-model="dataSelectVal" placeholder="请选择" clearable>
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
          <el-date-picker
            style="width:380px"
            v-model="queryTimeRange"
            type="datetimerange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          />
        </div>
        <div class="fl mr20">
          <br>
          <el-button type="primary" icon="el-icon-search" @click="handleQuery">查询</el-button>
        </div>
        <div class="fl mr20">
          <br>
          <el-button @click="handleExport" v-loading="exportLoading">导出</el-button>
        </div>
      </div>
      <!-- 详情记录表格 -->
      <div class="main">
        <el-table
          v-loading="loading"
          :data="tableDataC"
          border
          style="width: 100%"
        >
          <el-table-column
            align="center"
            prop="visit_id"
            label="记录次数"
            width="80"
          />
          <el-table-column
            align="center"
            label="拜访时间"
          >
            <template slot-scope="scope">
              {{ scope.row.visit_start_time }}<br>{{ scope.row.visit_end_time }}
            </template>
          </el-table-column>
          <el-table-column
            align="center"
            prop="created_time"
            label="录入时间"
          />
          <el-table-column
            align="center"
            prop="visit_next_time"
            label="下次联系时间"
          />
          <el-table-column
            align="center"
            prop="qianyue_status"
            label="签约状态"
            width="100"
          />
          <el-table-column
            align="center"
            prop="uname"
            label="报备人"
            width="100"
          />
          <el-table-column
            align="center"
            prop="visit_style"
            label="谈判方式"
            width="100"
          />
          <el-table-column
            align="center"
            prop="desc"
            label="谈话内容"
            width="380"
          />
          <el-table-column
            align="center"
            label="录音"
          >
            <template slot-scope="scope">
              <div v-if="scope.row.log_list.length > 0">
                <el-button type="text" v-for="(rcd, index) in scope.row.log_list" :key="index" @click="handleSoundRecord(rcd)">录音{{ index+1 }}</el-button>
              </div>
              <span v-else>----</span>
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
    <el-dialog
      title="录音播放"
      :visible="recordPlay"
      width="30%"
      @close="handleCloseRecord"
      >
        <audio class="player" :src="recordUrl" autoplay preload="auto" controls="" data-on="0" ref="record" v-if="isplaying">
        </audio>
    </el-dialog>
    <qz-dialog :dia-title="'id:'+ orderNO + '的电话呼叫记录'" :dialog-visible="dialogTableVisible" @diaClose="closeDialog">
      <el-table :data="gridData" border>
        <el-table-column align="center" property="time_add" label="产生时间" width="150" />
        <el-table-column align="center" property="action_name" label="呼叫动作" />
        <el-table-column align="center" property="bye" label="挂机类型(代码)" width="120" />
        <el-table-column
          align="center"
          label="通话录音"
        >
          <template slot-scope="scope">
            <div v-if="scope.row.action =='Hangup' && parseInt(scope.row.duration) > 0">
              <span @click="handleRecordDetail(scope.row)" class="pointer link-blue">
                <i class="fa fa-play-circle-o" aria-hidden="true"></i>
              </span>
              <span>{{ scope.row.duration }}秒</span>
            </div>
            <div v-else>
              <span>-</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column align="center" property="call_sid" label="callSid(callID)" width="190" />
        <el-table-column align="center" property="caller" label="主叫号码" width="120" />
        <el-table-column align="center" property="called" label="被叫号码" width="120" />
      </el-table>
    </qz-dialog>
  </div>
</template>

<script>
import { fetchListUser, fetchVoip, fetchListVisit } from '@/api/logList'
import { fetchContract, fetchSoundRecordList, fetchSoundRecordDetail } from '@/api/report-again'
import { export_json_to_excel } from '@/excel/Export2Excel'
import QzDialog from '@/components/QzDialog'
export default {
  name: 'LogList',
  components: {
    QzDialog
  },
  data() {
    return {
      // 日期选择 日期区间
      dataSelectVal: '',
      dataOptions: [
        {
          value: '1',
          label: '拜访时间'
        },
        {
          value: '2',
          label: '下次联系时间'
        },
        {
          value: '3',
          label: '录入时间'
        }
      ],
      currentPage: 1,
      queryTimeRange: '',
      // 录音数据
      gridData: [],
      recordUrl: '',
      recordPlay: false,
      orderNO: '',
      // 第一个表格
      com_id: '',
      company_info: {},
      tableDetail: [
        {
          company_name: 'ss',
          cname: '苏州',
          aname: '姑苏',
          user_id: 15616,
          user_name: ['发穷恶','赵文林'],
          user_tel: ['17696761244','13402515810'],
          user_qq: ['125325623',''],
          user_wx: ['21454125',''],
          contacts: [
            {
              name: "发穷恶",
              qq: "125325623",
              tel: "17696761244",
              wx: "21454125"
            },
            {
              name: "赵文林",
              qq: "",
              tel: "13402515810",
              wx: ""
            }
          ]
        }
      ],
      user_name: [],
      user_tel: [],
      user_wx: [],
      user_qq: [],
      userid: '',
      // 第二个表格
      contractTime: [{
        allStartTime: '',
        allEndTime: '',
        theStartTime: '',
        theEndTime: '',
        balanceTime: ''
      }],
      // 第三个表格
      tableDataC: [{}],
      exportLoading: false,
      exportData: [],
      // 以下是下拉列表选项
      dialogTableVisible: false,
      loading: false,
      dialoading: false,
      // 分页
      totals: 0,
      pageSize: 20,
      // 导出
      tableDao: []
    }
  },
  watch: {
    dataSelectVal() {
      this.queryTimeRange = ''
    }
  },
  created() {
    this.com_id = this.$route.params.id
    this.fetchLog()
    this.ListUser()
  },
  // mounted() {
  //   this.contactTime()
  // },
  methods: {
    fetchLog(val) {
      const queryObj = this.handleArguments(val)
      // this.loading = true
      fetchListVisit(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (res.data.data.list.length <= 0 && this.currentPage > 1) {
            this.currentPage--
            this.fetchLog()
          } else {
            this.tableDataC = []
            this.totals = res.data.data.page.total_number
            this.pageSize = res.data.data.page.page_size
            res.data.data.list.forEach((item, index) => {
              this.tableDataC.push(item)
              if (this.currentPage <= 1) {
                this.tableDataC[index].visit_id = this.totals - (index + 1) + 1
              }else {
                this.tableDataC[index].visit_id = this.totals - (index + 1 + (this.currentPage-1)*this.pageSize) + 1
              }
              this.tableDataC[index].created_time = new Date(item.created_time*1000).getFullYear() + "-" + (new Date(item.created_time*1000).getMonth() + 1) + "-" + new Date(item.created_time*1000).getDate() + " " + new Date(item.created_time*1000).getHours() + ":" + new Date(item.created_time*1000).getMinutes()
              this.tableDataC[index].visit_end_time = new Date(item.visit_end_time*1000).getFullYear() + "-" + (new Date(item.visit_end_time*1000).getMonth() + 1) + "-" + new Date(item.visit_end_time*1000).getDate() + " " + new Date(item.visit_end_time*1000).getHours() + ":" + new Date(item.visit_end_time*1000).getMinutes()
              this.tableDataC[index].visit_next_time =new Date(item.visit_next_time*1000).getFullYear() + "-" + (new Date(item.visit_next_time*1000).getMonth() + 1) + "-" + new Date(item.visit_next_time*1000).getDate() + " " + new Date(item.visit_next_time*1000).getHours() + ":" + new Date(item.visit_next_time*1000).getMinutes()
              this.tableDataC[index].visit_start_time =  new Date(item.visit_start_time*1000).getFullYear() + "-" + (new Date(item.visit_start_time*1000).getMonth() + 1) + "-" + new Date(item.visit_start_time*1000).getDate() + " " + new Date(item.visit_start_time*1000).getHours() + ":" + new Date(item.visit_start_time*1000).getMinutes()
            })
            this.loading = false
          }
        } else {
          this.tableDataC = []
          this.totals = 0
        }
      })
    },
    // 数据处理
    handleArguments(val) {
      let time_begin = ''
      let time_end = ''
      const queryObj = {
        company_id: Number(this.com_id), // 城市id
        visit_start: '', // 拜访查询开始时间
        visit_end: '', // 拜访查询结束时间
        next_start: '', // 下次联系查询开始时间
        next_end: '', // 下次联系查询结束时间
        created_at_start: '', // 录入开始时间
        created_at_end: '', // 录入结束时间
        page: this.currentPage, // 分页 页码  默认1
        page_count: this.pageSize,
        down: '' // 是否导出 非0或非空->导出
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
            queryObj.visit_start = time_begin
            queryObj.visit_end = time_end
            break
          case '2':
            queryObj.next_start = time_begin
            queryObj.next_end = time_end
            break
          case '3':
            queryObj.created_at_start = time_begin
            queryObj.created_at_end = time_end
            break
          default:
            break
        }
      }
      return queryObj
    },
    // 查询
    handleQuery() {
      this.tableDataC = []
      this.fetchLog()
    },
    fetchExportlist(val, cb) {
      const queryObj = this.handleArguments(val)
      queryObj.page_count = 999
      fetchListVisit(queryObj).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          if (res.data.data.list.length > 0) {
            res.data.data.list.forEach((item, index) => {
              this.exportData.push(item)
              this.exportData[index].visit_id = index + 1
              const eTmpA = new Date(parseInt(item.created_time) * 1000)
              this.exportData[index].created_time = eTmpA.getFullYear() + '-' + (parseInt(eTmpA.getMonth()) + 1) + '-' + eTmpA.getDate() + ' ' + eTmpA.getHours() + ':' + eTmpA.getMinutes() + ':' + eTmpA.getSeconds()
              const eTmpB = new Date(parseInt(item.visit_end_time) * 1000)
              this.exportData[index].visit_end_time = eTmpB.getFullYear() + '-' + (parseInt(eTmpB.getMonth()) + 1) + '-' + eTmpB.getDate() + ' ' + eTmpB.getHours() + ':' + eTmpB.getMinutes() + ':' + eTmpB.getSeconds()
              const eTmpC = new Date(parseInt(item.visit_next_time) * 1000)
              this.exportData[index].visit_next_time = eTmpC.getFullYear() + '-' + (parseInt(eTmpC.getMonth()) + 1) + '-' + eTmpC.getDate() + ' ' + eTmpC.getHours() + ':' + eTmpC.getMinutes() + ':' + eTmpC.getSeconds()
              const eTmpD = new Date(parseInt(item.visit_start_time) * 1000)
              this.exportData[index].visit_start_time = eTmpD.getFullYear() + '-' + (parseInt(eTmpD.getMonth()) + 1) + '-' + eTmpD.getDate() + ' ' + eTmpD.getHours() + ':' + eTmpD.getMinutes() + ':' + eTmpD.getSeconds()
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
    // 导出
    handleExport() {
      // require.ensure([], () => {
      // const { export_json_to_excel } = require('../../vendor/Export2Excel');
      this.exportLoading = true
      const tHeader = [
        '记录次数',
        '拜访时间',
        '录入时间',
        '下次联系时间',
        '报备人',
        '谈判方式',
        '谈话内容',
        '签约状态',
        '公司名称',
        '城市',
        '区县',
        '联系人',
        '地址',
        '意向',
        '客户类型',
        '总合同开始时间',
        '总合同结束时间',
        '本次合同开始时间',
        '本次合同结束时间',
        '尾款时间'
      ]
      // 上面设置Excel的表格第一行的标题
      const filterVal = [
        'visit_id',
        'visit_time',
        'created_time',
        'visit_next_time',
        'uname',
        'visit_style',
        'desc',
        'qianyue_status',
        'company_name',
        'cname',
        'aname',
        'user_name',
        'address',
        'intention',
        'is_new_text',
        'allEndTime',
        'allStartTime',
        'balanceTime',
        'theEndTime',
        'theStartTime',
      ]
      // 上面的index、phone_Num、school_Name是tableData里对象的属性
      this.fetchExportlist('export', () => {
        this.tableDao = []
        this.exportData.forEach((item, index) => {
          item.visit_time = item.visit_start_time + '--' + item.visit_end_time
          this.tableDao.push(item)
        })
        this.tableDao[0]['company_name'] = this.tableDetail[0].company_name
        this.tableDao[0]['cname'] = this.tableDetail[0].cname
        this.tableDao[0]['aname'] = this.tableDetail[0].aname
        this.tableDetail[0].contacts.forEach((item, index) => {
          this.tableDao[index]['user_name'] = this.tableDetail[0].contacts[index].name
        })
        this.tableDao[0]['address'] = this.tableDetail[0].address
        switch (this.tableDetail[0].intention) {
          case 1 :
            this.tableDetail[0].intention = 'A'
            break
          case 2 :
            this.tableDetail[0].intention = 'B'
            break
          case 3 :
            this.tableDetail[0].intention = 'C'
            break
          default:
            break
        }
        this.tableDao[0]['intention'] = this.tableDetail[0].intention
        this.tableDao[0]['is_new_text'] = this.tableDetail[0].is_new_text
        this.tableDao[0]['allEndTime'] = this.contractTime[0].allEndTime
        this.tableDao[0]['allStartTime'] = this.contractTime[0].allStartTime
        this.tableDao[0]['balanceTime'] = this.contractTime[0].balanceTime
        this.tableDao[0]['theEndTime'] = this.contractTime[0].theEndTime
        this.tableDao[0]['theStartTime'] = this.contractTime[0].theStartTime
        const list = this.tableDao // 把data里的tableData存到list
        this.tableDao = []
        this.exportData = []
        const data = this.formatJson(filterVal, list)
        export_json_to_excel(tHeader, data, '日记记录统计')
        this.exportLoading = false
      })
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]))
    },
    // 录音查看
    handleSoundRecord(obj) {
      this.dialogTableVisible = true
      this.orderNO = obj.id
      this.gridData = []
      fetchSoundRecordList({
        call_sid: obj.call_sid,
        channel: obj.channel
      }).then(res => {
        if(parseInt(res.status) === 200 && res.data.data.length > 0) {
          this.gridData = res.data.data
          this.gridData.forEach((item, index) => {
            if(item.byetype_name === '-') {
              item.bye = '-'
            }else{
              item.bye = item.byetype_name + "(" + item.byetype + ")"
            }
          })
        }
      })
    },
    handleRecordDetail(obj) {
      this.recordUrl = ''
      if (obj.TelCenter_Channel == 'cuct' || obj.TelCenter_Channel == 'holly') {
        fetchSoundRecordDetail({
          call_sid: obj.call_sid,
          channel: obj.channel
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) == 0) {
            this.recordUrl = this.$qzconfig.oss_audio_host + res.data.data.url
            this.recordPlay = true
            this.isplaying = true
          }
        })
      } else if (obj.TelCenter_Channel == 'ytx') {
        this.recordPlay = true
        this.recordUrl = obj.recordurl
      }
    },
    handleCloseRecord() {
      this.isplaying = false
      this.recordPlay = false
    },
    closeDialog() {
      this.orderNO = ''
      this.dialogTableVisible = false
    },
    // 分页处理
    handleSizeChange() {
      this.currentPage = 1
      this.fetchLog()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.fetchLog()
    },
    // 表格上方信息
    ListUser() {
      fetchListUser({
        company_id: this.com_id
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.tableDetail = []
          this.user_name = []
          this.user_tel = []
          this.user_wx = []
          this.user_qq = []
          const data = res.data.data
          this.userid = res.data.data.user_id
          this.contactTime(this.userid)
          switch (data.is_new) {
            case 1:
              data.is_new_text = '新客户'
              break
            case 2:
              data.is_new_text = '老客户'
              break
            default:
              break
          }
          switch (data.intention) {
            case 1:
              data.intention_text = 'A'
              break
            case 2:
              data.intention_text = 'B'
              break
            case 3:
              data.intention_text = 'C'
              break
            default:
              break
          }
          this.tableDetail[0] = data
          res.data.data.contacts.forEach((item, index) => {
            this.user_name.push(item.name)
            this.user_tel.push(item.tel)
            this.user_wx.push(item.wx)
            this.user_qq.push(item.qq)
          })
          this.tableDetail[0]['user_name'] = this.user_name
          this.tableDetail[0]['user_tel'] = this.user_tel
          this.tableDetail[0]['user_wx'] = this.user_wx
          this.tableDetail[0]['user_qq'] = this.user_qq
        }
      })
    },
    // 合同时间列表
    contactTime(id) {
      fetchContract({
        user_id: id
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.contractTime[0].allStartTime = res.data.data.allstart
          this.contractTime[0].allEndTime = res.data.data.allend
          this.contractTime[0].theStartTime = res.data.data.start
          this.contractTime[0].theEndTime = res.data.data.end
          this.contractTime[0].balanceTime = res.data.data.retainage_time
        }
      })
    }
  }
}
</script>

<style scoped>
  .fl {
    float: left;
  }
  .mr20 {
    margin-right: 20px;
  }
  .mt20 {
    margin-top: 20px;
  }

  .log-list {
    padding: 20px;
  }
  .rizhi-jilu,
  .rizhi-details {
    width: 100%;
    padding: 20px;
    background-color: #fff;
    border-top: 3px solid rgb(197, 196, 196);
    box-sizing: border-box;
  }
  .search-list {
    height: 80px;
  }
  .el-pagination{
    text-align: center;
    margin-top: 20px;
  }
  .zhu{
    color: #0099FF;
    cursor: pointer;
  }
  .elTable p {
    line-height: 4px;
  }
</style>
