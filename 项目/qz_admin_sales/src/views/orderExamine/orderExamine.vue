<template>
  <div class="order-examine">
    <div class="form">
      <h4>查询条件</h4>
      <el-form ref="form" :inline="true" :model="formData" label-position="top" class="demo-form-inline">
        <el-form-item label="订单查询：" prop="condition">
          <el-input v-model="formData.condition" placeholder="订单号，小区名称" />
        </el-form-item>
        <el-form-item label="起始日期：" prop="time">
          <el-date-picker
            v-model="formData.time"
            type="daterange"
            range-separator="至"
            label="查询时间："
            value-format="yyyy-MM-dd"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          />
        </el-form-item>
        <el-form-item label="签单审核状态：" prop="status">
          <el-select v-model="formData.status" placeholder="请选择">
            <el-option label="请选择" value="" />
            <el-option label="审核中" value="3" />
            <el-option label="已签约 " value="1" />
            <el-option label="继续跟进 " value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="赠单/分单筛选：" prop="state">
          <el-select v-model="formData.state" placeholder="请选择">
            <el-option label="请选择" value="" />
            <el-option label="分单" value="1" />
            <el-option label="赠单" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="所属城市：" prop="cs">
          <el-select v-model="formData.cs" filterable :clearable="true" placeholder="请选择">
            <el-option
              v-for="item in citys"
              :key="item.cid"
              :label="item.value"
              :value="item.cid"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="公司名称：" prop="company">
          <el-input v-model="formData.company" placeholder="公司名称" />
        </el-form-item>
        <el-form-item label="签单返点订单：" prop="qian">
          <el-select v-model="formData.qian" placeholder="请选择">
            <el-option label="请选择" value="" />
            <el-option label="是" value="1" />
            <el-option label="否" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item class="mt46 mr30">
          <el-button @click="resetForm('form')">重置</el-button>
          <el-button type="primary" @click="search">查询</el-button>
        </el-form-item>
        <el-form-item class="mt46">
          <el-button type="primary" @click="fail">不通过列表</el-button>
        </el-form-item>
      </el-form>
    </div>
    <div class="table">
      <h4>订单列表</h4>
      <el-table v-loading="loading" :data="tableData" style="width: 100%">
        <el-table-column align="center" label="订单编号" width="180">
          <template slot-scope="scope">
            {{ scope.row.id }} <span v-if="scope.row.is_qf" class="green">返</span>
          </template>
        </el-table-column>
        <el-table-column
          prop="qiandan_addtime"
          label="申请签单时间"
          align="center"
          width="120"
        />
        <el-table-column
          prop="time_real"
          label="发单时间"
          align="center"
          width="120"
        />
        <el-table-column label="城市/区县" align="center">
          <template slot-scope="scope">
            {{ scope.row.cname }}/{{ scope.row.qz_area }}
          </template>
        </el-table-column>
        <el-table-column prop="name" label="订单类型" align="center" width="100">
          <template slot-scope="scope">
            {{ scope.row.type_fw=== 1?'分单':'赠单' }}
          </template>
        </el-table-column>
        <el-table-column
          prop="xiaoqu"
          align="center"
          label="签单小区"
        />
        <el-table-column label="签单面积" align="center" width="100">
          <template slot-scope="scope">
            {{ scope.row.mianji }}㎡
          </template>
        </el-table-column>
        <el-table-column label="签单金额" align="center" width="100">
          <template slot-scope="scope">
            {{ scope.row.qiandan_jine }}万元
          </template>
        </el-table-column>
        <el-table-column
          prop="name"
          align="center"
          label="联系人"
        />
        <el-table-column
          prop="qc"
          align="center"
          label="申请公司"
        />
        <el-table-column label="喜报" align="center" width="100">
          <template slot-scope="scope">
            <el-button type="text" @click="xibao(scope.row.jc)">查看</el-button>
          </template>
        </el-table-column>
        <el-table-column label="签单审核状态" align="center" width="120">
          <template slot-scope="scope">
            <div v-if="scope.row.qiandan_status === 1">
              <span style="color: #008000;">已签单</span><br>
              <el-button size="small" type="text" @click="cancel(scope.row.id)">取消</el-button>
            </div>
            <span v-if="scope.row.qiandan_status===0" style="color: #FF9900;">请审核</span>
            <span v-else-if="scope.row.qiandan_status===2">继续跟踪</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" align="center" fixed="right" width="150">
          <template slot-scope="scope">
            <div>
              <el-button type="text" size="small" @click="acceptanceDialog(scope.row)">受理</el-button>
              <el-button type="text" size="small" @click="openTel(scope.row.id)">呼叫记录（{{ scope.row.tel_count }}）</el-button>
            </div>
            <div>
              <el-button v-if="scope.row.qiandan_status === 1" type="text" size="small" style="color: #008000;" @click="returnRecording(scope.row.id)">返点</el-button>
              <el-button v-if="scope.row.qiandan_status === 1" type="text" size="small" style="color: #008000;" @click="returnRecordingLog(scope.row.id)">返点记录（{{ scope.row.report_count }}）</el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination
        :current-page="currentPage"
        :page-size="limit"
        layout="total, prev, pager, next, jumper"
        :total="totals"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </div>
    <el-dialog
      title="不通过列表"
      :visible.sync="dialogVisible"
      width="60%"
      :before-close="handleClose"
    >
      <fail-list />
    </el-dialog>
    <el-dialog
      title="返点记录"
      :visible.sync="fdDialogVisible"
      width="50%"
      :before-close="handleClose"
    >
      <rebate-record :order-id="orderId" />
    </el-dialog>
    <el-dialog
      title="受理审核"
      :visible.sync="slDialogVisible"
      width="40%"
      :before-close="handleClose"
    >
      <span slot="title" class="dialog-footer">
        <span class="sl-title">受理审核</span>
        <span>订单编号：{{ orderId }}</span>
      </span>
      <acceptance :order-id="orderId" :is-qf="isQf" />
    </el-dialog>
    <el-dialog
      :visible.sync="xbDialogVisible"
      :width="canvasW + 'px'"
      :before-close="handleClose"
      :show-close="false"
      custom-class="xibao-dialog"
    >
      <canvas id="mycanvas" :width="canvasW" :height="canvasH" />
    </el-dialog>

  </div>
</template>

<script>
import { fetchCityList } from '@/api/common'
import { fetchOrderExamineList, FetchOrdersQdup } from '@/api/orderExamine'
import { fortmatDate } from '@/utils/index'
import failList from './components/noPassList'
import rebateRecord from './components/rebateRecord'
import acceptance from './components/acceptance'
export default {
  name: 'OrderExamine',
  components: {
    failList,
    rebateRecord,
    acceptance
  },
  data() {
    return {
      name: '1',
      loading: false,
      currentPage: 1,
      limit: 20,
      totals: 0,
      citys: [],
      orderId: '',
      isQf: 0,
      formData: {
        condition: '',
        time: [],
        start: '',
        end: '',
        status: '',
        state: '',
        company: '',
        cs: '',
        qian: ''
      },
      tableData: [],
      dialogVisible: false,
      slDialogVisible: false,
      fdDialogVisible: false,
      xbName: '',
      xbDialogVisible: false,
      canvasW: 398,
      canvasH: 640
    }
  },
  created() {

  },
  mounted() {
    this.getList()
    this.loadAllCity()
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
    getList() {
      const that = this
      that.loading = true
      const obj = that.getData()
      fetchOrderExamineList(obj).then(res => {
        that.loading = false
        if (res.status === 200 && res.data.error_code === 0) {
          that.tableData = res.data.data.list.map(item => {
            item.qiandan_addtime = fortmatDate(item.qiandan_addtime * 1000)
            item.time_real = fortmatDate(item.time_real * 1000)
            return item
          })
          that.formData.time = []
          that.formData.time[0] = res.data.data.search.start
          that.formData.time[1] = res.data.data.search.end
          that.totals = res.data.data.page.total_number
        } else {
          that.tableData = []
          that.totals = 0
        }
      }).catch(res => {
        console.log(res)
        that.loading = false
      })
    },
    getData() {
      const obj = {}
      obj.page = this.currentPage
      obj.limit = this.limit
      obj.condition = this.formData.condition
      obj.start = this.formData.time[0]
      obj.end = this.formData.time[1]
      obj.status = this.formData.status
      obj.state = this.formData.state
      obj.company = this.formData.company
      obj.cs = this.formData.cs
      obj.qian = this.formData.qian
      return obj
    },
    handleSizeChange() {
      this.currentPage = 1
      this.getList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.getList()
    },
    resetForm(formName) {
      console.log(formName)
      this.$refs[formName].resetFields()
    },
    search() {
      this.currentPage = 1
      this.getList()
    },
    cancel(id) {
      console.log('取消')
      this.$confirm('确定要取消签单？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        const obj = {}
        obj.id = id
        obj.qiandan_status = 0
        FetchOrdersQdup(obj).then(res => {
          if (res.status === 200 && res.data.error_code === 0) {
            this.$message({
              type: 'success',
              message: '操作成功!'
            })
            this.getList()
          } else {
            this.$message({
              type: 'error',
              message: res.data.error_msg
            })
          }
        })
      }).catch(() => {})
    },
    fail() {
      this.dialogVisible = true
    },
    returnRecording(id) {
      if (!id) {
        return
      }
      const href = '/smallReport/add?type=8&orderId=' + id
      window.open(href, '_blank')
    },
    returnRecordingLog(id) {
      this.fdDialogVisible = true
      this.orderId = id
    },
    acceptanceDialog(val) {
      this.slDialogVisible = true
      this.orderId = val.id
      this.isQf = val.is_qf
    },
    handleClose(done) {
      done()
    },
    xibao(val) {
      this.xbName = val
      this.xbDialogVisible = true
      this.xibaoCanvas()
    },
    xibaoCanvas() {
    //  获取canvas
      var canvas = document.getElementById('mycanvas')
      var name = this.xbName
      var width = this.canvasW
      var height = this.canvasH
      //  由于弹窗，确保已获取到
      var a = setInterval(() => {
        //  重复获取
        canvas = document.getElementById('mycanvas')
        if (!canvas) {
          return false
        } else {
          clearInterval(a)
          //  可以理解为一个画笔，可画路径、矩形、文字、图像
          var context = canvas.getContext('2d')
          var img = new Image()
          img.src = '//staticqn.qizuang.com/custom/20200727/FgwVv7kjtITowFsgJrcMOszxgUpA'
          //  加载图片
          img.onload = function() {
            if (img.complete) {
              //  根据图像重新设定了canvas的长宽
              canvas.setAttribute('width', width)
              canvas.setAttribute('height', height)
              //  绘制图片
              context.drawImage(img, 0, 0, width, height)
              // 绘制文字
              context.font = '30px 微软雅黑'
              context.fillStyle = '#fff'
              context.textAlign = 'center'
              context.fillText(name, width / 2, height * 0.61, width)
            }
          }
        }
      }, 1)
    },
    openTel(id) {
      window.open('//168new.qizuang.com/voip/othervoiprecord/?id=' + id)
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
.order-examine{
  padding: 20px;
  .form,.table{
    background: #fff;
    border-top: 3px #999 solid;
    margin-bottom: 20px;
    padding: 20px;
    .el-select{
      margin-top: 0;
    }
    .mt46{
      margin-top: 46px;
    }
    .mr30{
      margin-right: 30px;
    }
    h4{
      font-size: 18px;
      font-weight: normal;
      line-height: 20px;
      margin: 0 0 20px 0;
    }
    .el-date-editor{
      .el-range-separator{
        padding: 0;
      }
    }
  }
  .xibao-dialog{
    .el-dialog__header{
      display: none;
    }
    .el-dialog__body{
      padding: 0;
      height: 640px;
    }
  }
  .green{
    color: #009900;
  }
  .el-pagination{
    text-align: center;
    margin-top: 20px;
  }
  .sl-title{
    font-size: 20px;
    font-weight: bold;
    margin-right: 30px;
  }
}
</style>
