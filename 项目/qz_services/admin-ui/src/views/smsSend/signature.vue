<template>
    <div class="signature">
      <div class="search">
        <el-button @click="showAddSignature">新增签名</el-button>
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
            label="签名ID"
          />
          <el-table-column
            align="center"
            prop="name"
            label="签名"
          />
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
                v-model="scope.row.enabled"
                active-value="1"
                inactive-value="0"
                @change="switchSignatureStatus(scope.row)"
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
      <add-signature
        :dialog-form-visible="dialogFormVisible"
        :signature-text="signature"
        :signature-status="signatureStatus"
        :signature-id="signatureId"
        @closeAddDia="closeDia"
      ></add-signature>
    </div>
</template>

<script>
  import { fetchSignatureList, fetchSignatureEnable } from '@/api/smsTpl'
  import addSignature from './components/addSignature'
  export default {
    name: 'signature',
    components: {
      addSignature
    },
    data() {
      return {
        dialogFormVisible: false,
        tableData: [],
        loading: false,
        signature: '',
        signatureStatus: '',
        signatureId: ''
      }
    },
    created() {
      this.getSignatureList()
    },
    methods: {
      getSignatureList() {
        this.loading = true
        this.tableData = []
        fetchSignatureList().then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.tableData = res.data.data.list
            this.tableData.forEach((item, index) => {
              item.enabled = String(item.enabled)
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
      switchSignatureStatus(obj) {
        console.log(obj)
        if(parseInt(obj.enabled) === 0) {
          this.$confirm('一旦禁用了该签名，跟该签名关联的模板都不可使用，请谨慎操作。', '提示', {
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
        }
      },
      ajaxStatus(obj) {
        fetchSignatureEnable({
          id: obj.id,
          enabled: parseInt(obj.enabled)
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
            item.enabled = parseInt(obj.enabled) === 0 ? '1' : '0'
          }
        })
      },
      showAddSignature() {
        this.signature = ''
        this.signatureStatus = ''
        this.signatureId = ''
        this.dialogFormVisible = true
      },
      closeDia(val) {
        this.dialogFormVisible = false
        if(val === 'updata') {
          this.getSignatureList()
        }
      },
      handleEdit(obj) {
        this.signature = obj.name
        this.signatureStatus = obj.enabled
        this.signatureId = String(obj.id)
        this.dialogFormVisible = true
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
  .signature{
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
  }
</style>
