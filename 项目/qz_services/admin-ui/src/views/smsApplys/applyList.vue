<template>
  <div class="apply-list">
    <div class="search">
      <el-button @click="showAddApplication">新增项目应用</el-button>
    </div>
    <div class="qian-main">
      <el-table
        v-loading="loading"
        :data="tableData"
        border
      >
        <el-table-column
          align="center"
          prop="id"
          label="项目应用ID"
          
        />
        <el-table-column
          align="center"
          prop="slot"
          label="应用标识"
        />
        <el-table-column
          align="center"
          prop="name"
          label="应用名称"
        >
            <template slot-scope="scope">
                <span class="cells">{{scope.row.name}}</span>
            </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="note"
          label="应用说明"
        >
            <template slot-scope="scope">
                <span class="cells">{{scope.row.note}}</span>
            </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="应用类型"
        >
            <template slot-scope="scope">
                {{scope.row.app_type | appTypeFileter}}
            </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="接入类型"
        >
            <template slot-scope="scope">
                <span v-show="scope.row.access_list == null">----</span>
                <span v-for="(item, index) in scope.row.access_list" :key="index" class="access" >{{item.access_type_name}}</span>
            </template>
        </el-table-column>
        <el-table-column
          align="center"
          prop="creator_name"
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
              v-model="scope.row.is_enable"
              active-value="1"
              inactive-value="0"
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
  import { fetchApplyList, fetchAppEnable } from '@/api/projectApply'
  import addApplication from './components/addApplication'
  export default {
    name: 'applyList',
    components: {
      addApplication
    },
    data() {
      return {
        dialogFormVisible: false,
        tableData: [],
        loading: false,
        applicationId: 0
      }
    },
    filters: {
        appTypeFileter(val) {
            switch(val){
                case 1:
                    return '前台系统'
                case 2:
                    return '后台系统'
                default:
                    return '----'
            }
        },
    },
    created() {
      this.getApplyList()
    },
    methods: {
        showAddApplication() {
            this.dialogFormVisible = true
        },
        closeDia(val) {
            this.dialogFormVisible = false
            this.applicationId = ''
            if(val === 'update' ) {
            this.getApplyList()
            }
        },
        getApplyList() {
            this.loading = true
            this.tableData = []
            fetchApplyList().then(res => {
            if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                this.tableData = res.data.data.list
                this.tableData.forEach((item, index) => {
                    item.is_enable = String(item.is_enable)
                })
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
            if(parseInt(obj.is_enable) === 0) {
            this.$confirm('警告！禁用后，本应用中所有的服务都不能发送', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            }).then(() => {
                // 发送请求，更改禁用状态
                this.ajaxStatus(obj)
            }).catch(() => {
                // 还原原有状态
                this.reductionStatus(obj)
            });
            }else{
            this.ajaxStatus(obj)
            }
        },
        ajaxStatus(obj) {
            fetchAppEnable({
            id: obj.id,
            is_enable: parseInt(obj.is_enable)
            }).then(res => {
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
            this.tableData.forEach((item, index) => {
            if(item.id === obj.id) {
                item.is_enable = parseInt(obj.is_enable) === 0 ? '1' : '0'
            }
            })
        },
        handleEdit(obj) {
            this.applicationId = obj.id
            this.dialogFormVisible = true
        },
        addClass({row,rowIndex,column,columnIndex}){
            if(columnIndex === 2){
                return 'cells'
            }
        }
    }
}
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
    .apply-list{
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
        .access::after{
            content: '，'
        }
         .access:last-child::after{
            display: none;
        }
    }
</style>
<style>
    .cells{
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }
</style>

