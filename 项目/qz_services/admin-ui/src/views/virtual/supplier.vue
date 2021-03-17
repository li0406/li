<template>
    <div class="supplier-list">
        <div class="search">
            <el-button @click="showAddApplication">新增供应商</el-button>
        </div>
        <div class="qian-main">
            <el-table
                    v-loading="loading"
                    :data="tableData"
                    border
            >
                <el-table-column
                        align="center"
                        prop="slot"
                        label="唯一标识"

                />
                <el-table-column
                        align="center"
                        prop="name"
                        label="供应商名称"
                />
                <el-table-column
                        align="center"
                        prop="op_uname"
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
                                v-model="scope.row.is_enabled"
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
        <addSupplier :dialog-form-visible="dialogFormVisible" :application-id="applicationId" @closeAddDia="closeDia"></addSupplier>
    </div>
</template>

<script>
import { fetchSupplierList, providerstatusup } from '@/api/virtual'
import addSupplier from './components/addSupplier'
export default {
  name: 'Supplier',
  components: {
    addSupplier
  },
  data() {
    return {
      dialogFormVisible: false,
      tableData: [],
      loading: false,
      applicationId: ''
    }
  },
  created() {
    this.getSupplerList()
  },
  methods: {
    getSupplerList() {
      this.loading = true
      this.tableData = []
      fetchSupplierList().then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.tableData = res.data.data.list
          this.tableData.forEach((item, index) => {
            item.is_enabled = String(item.is_enabled)
          })
        } else {
          this.$message.error(res.data.error_msg)
        }
        this.loading = false
      }).catch(res => {
        this.loading = false
      })
    },
    showAddApplication() {
      this.dialogFormVisible = true
    },
    handleEdit(obj) {
      this.applicationId = String(obj.id)
      this.dialogFormVisible = true
    },
    closeDia(val) {
      this.dialogFormVisible = false
      this.applicationId = ''
      if (val === 'update') {
        this.getSupplerList()
      }
    },
    switchAppStatus(obj) {
      if (parseInt(obj.is_enabled) === 2) {
        this.$confirm('警告！是否关闭', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.ajaxStatus(obj)
        }).catch(() => {
          console.log(obj.is_enabled, '1111')
          this.reductionStatus(obj)
        })
      } else {
        this.ajaxStatus(obj)
      }
    },
    ajaxStatus(obj) {
      providerstatusup({
        id: obj.id,
        enabled: parseInt(obj.is_enabled)
      }).then(res => {
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.$message({
            message: '启用状态修改成功',
            type: 'success'
          })
        } else {
          this.$message.error(res.data.error_msg)
          this.reductionStatus(obj)
        }
      }).catch(res => {
        this.reductionStatus(obj)
      })
    },
    reductionStatus(obj) {
      this.tableData.forEach((item, index) => {
        if (item.id === obj.id) {
          item.is_enabled = parseInt(obj.is_enabled) === 2 ? '1' : '2'
        }
      })
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
    .supplier-list{
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
            overflow: hidden;
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