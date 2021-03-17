<template>
  <div class="msgType-list">
    <div class="search">
      <el-button @click="showAddApplication">新增消息类型</el-button>
    </div>
    <div class="qian-main">
      <el-table
        v-loading="loading" 
        :data="tableData"
        border
        class="cells"
      >
        <el-table-column
          align="center"
          prop="slot"
          label="唯一标识"
        />
        <el-table-column
          align="center"
          prop="name"
          label="消息类型"
        >
            <template slot-scope="scope">
                <span class="cells">{{scope.row.name}}</span>
            </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="接收方式"
        >
        <template slot-scope="scope">
          {{scope.row.receive_type === 1 ? '实时接收' : '延后接收' }}
        </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="提醒方式"
        >
        <template slot-scope="scope">
          {{ scope.row.remind_type === 1 ? '数量提醒' : '弹窗提醒' }}
        </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="项目应用"
        >
        <template slot-scope="scope">
            <span class="cells">
                <span v-for="(item,index) in scope.row.app_list" :key='index' class="gateway_item">{{item.name}}</span>
            </span>
        </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="user"
          label="添加人"
        />
        <el-table-column
          align="center"
          prop="created_at"
          label="添加时间"
        />
        <el-table-column
          align="center"
          label="是否启用"
        >
          <template slot-scope="scope">
            <el-switch
              v-model="scope.row.enabled"
              active-value="1"
              inactive-value="2"
              @change="switchAppStatus(scope.row)"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="操作"
        >
          <template slot-scope="scope">
            <span class="link-blue pointer" @click="handleEdit(scope.row)">编辑</span>
          </template>
        </el-table-column>
      </el-table>

    </div>
    <add-application :dialog-form-visible="dialogFormVisible" :application-id="applicationId" @closeAddDia="closeDia"></add-application>
  </div>
</template>

<script>
  // import { fetchApplyList, fetchAppEnable } from '@/api/projectApply'
  import { getMessageList,fetchMessageEnable } from '@/api/messageType'
  import addApplication from './components/addApplication'
  import moment from "moment"
  export default {
    name: 'applyList',
    components: {
      addApplication
    },
    data() {
      return {
        currentPage: 1,
        totals: 0,
        dialogFormVisible: false,
        tableData: [],
        loading: false,
        applicationId: 0
      }
    },
    created() {
      this.getApplyList()
    },
    methods: {
      moment,
      handleSizeChange() {
        // this.currentPage = 1
        this.getApplyList()
      },
      handleCurrentChange(val) {
        // this.currentPage = val
        this.getApplyList()
      },
      showAddApplication() {
        this.dialogFormVisible = true
      },
      closeDia(val) {
        this.dialogFormVisible = false
        // this.$nextTick(() => {
        //   this.$refs["applicationForm"].resetFields();
        // });
        this.applicationId = ''
        if(val === 'update' ) {
          this.getApplyList()
        }
      },
      getApplyList() {
        this.loading = true
        this.tableData = []
        getMessageList().then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.tableData = res.data.data.list
            this.tableData.forEach((item, index) => {
              item.enabled = String(item.enabled)
              item.created_at = moment((item.created_at)*1000).format('YYYY-MM-DD HH:mm:ss')
            })
            // this.totals = res.data.data.page.total_number
          }else{
            this.$message.error(res.data.error_msg)
          }
          this.loading = false
        }).catch(res => {
          this.$message.error('网络异常，请稍后重试')
          this.loading = false
        })
      },
      switchAppStatus(obj) {
        console.log('状态',obj);
        fetchMessageEnable({
          id: obj.id,
        }).then(res => {
          console.log('res', res);
          
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              message: '修改成功',
              type: 'success'
            })
          }else{
            this.$message.error(res.data.error_msg)
            this.reductionStatus(obj)
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后重试')
          this.reductionStatus(obj)
        })
      },
      reductionStatus(obj) {
        console.log('执行了reductionStatus',obj);
        
        this.tableData.forEach((item, index) => {
          if(item.id === obj.id) {
            item.enabled = parseInt(obj.enabled) === 1 ? '2' : '1'
          }
        })
      },
      handleEdit(obj) {
        this.applicationId = obj.id
        this.dialogFormVisible = true
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
  .msgType-list{
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
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
   .cells{
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }
    .cells span::after{
        content: '，';
    }
    .cells span:last-child:after{
        display: none;
    }
   
    /deep/ .el-checkbox__label{
        white-space: normal;
        word-break: break-all;
    }
    
  }
</style>
