<template>
    <div class="gateway">
      <div class="search">
        <el-button @click="showAddGateway">新增通道网关</el-button>
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
            label="通道名称"
          />
          <el-table-column
            align="center"
            prop="type_name"
            label="通道类型"
          />
          <el-table-column
            align="center"
            prop="level"
            label="优先级"
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
                v-model="scope.row.is_enable"
                active-value="1"
                inactive-value="0"
                @change="switchGateway(scope.row)"
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
      <addGateway :dialog-form-visible="dialogFormVisible" :gateway-id="editID" @closeAddDia="closeDia"></addGateway>
    </div>
</template>

<script>
  import { fetchGatewayList, fetchGatewayStatus } from '@/api/gateway'
  import addGateway from './components/addGateway'
  export default {
    name: 'gateway',
    components: {
      addGateway
    },
    filters: {
      transferType(val) {
        switch (val) {
          case 1:
            return '验证码通知(行业)'
            break
          case 2:
            return '营销'
            break
          case 3:
            return '国际验证码'
            break
          case 4:
            return '语音验证码'
            break
          default:
            return '----'
            break
        }
      }
    },
    data() {
      return {
        editID: '',
        dialogFormVisible: false,
        tableData: [],
        loading: false
      }
    },
    created() {
      this.getGatewayList()
    },
    methods: {
      showAddGateway() {
        this.dialogFormVisible = true
      },
      closeDia(val) {
        this.dialogFormVisible = false
        this.editID = ''
        if(val === 'updata') {
          this.getGatewayList()
        }
      },
      getGatewayList() {
        this.loading = true
        this.tableData = []
        fetchGatewayList().then(res => {
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
      switchGateway(obj) {
        fetchGatewayStatus({
          id: obj.id,
          is_enable: parseInt(obj.is_enable)
        }).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              message: '修改成功',
              type: 'success'
            })
          }else {
            this.$message.error(res.data.error_msg)
            this.reductionStatus(obj)
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
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
        this.editID = obj.id
        this.dialogFormVisible = true
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
.gateway{
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
