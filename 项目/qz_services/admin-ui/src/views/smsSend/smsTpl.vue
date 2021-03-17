<template>
    <div class="sms-tpl">
      <div class="search">
        <div class="yixiang fl mr20">
          模板内容：<br>
          <el-input
            v-model="tplText"
            placeholder="请输入模板内容"
            clearable>
          </el-input>
        </div>
        <div class="fl mr20">
          模板类型：<br>
          <el-select v-model="tplTypeVal" placeholder="请选择">
            <el-option
              v-for="item in tplTypeOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </div>
        <div class="fl mr20">
          签名：<br>
          <el-select v-model="signatureVal" placeholder="请选择">
            <el-option
              v-for="item in signatureOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </div>
        <div class="fl mr20">
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
          是否启用：<br>
          <el-select v-model="enableVal" placeholder="请选择">
            <el-option
              v-for="item in enableOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </div>
        <div>
          <br>
          <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
          <el-button plain @click="handleExport" v-loading="exportLoading">导出</el-button>
          <el-button type="primary" @click="handleAdd" class="fr">新增短信模板</el-button>
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
            prop="id"
            label="模板ID"
          />
          <el-table-column
            align="center"
            prop="sign_name"
            label="签名"
          />
          <el-table-column
            align="center"
            prop="content"
            label="模板内容"
          />
          <el-table-column
            align="center"
            prop="type_name"
            label="模板类型"
          />
          <el-table-column
            align="center"
            prop="use_scene"
            label="使用场景"
          />
          <el-table-column
            align="center"
            label="是否脱敏"
          >
            <template slot-scope="scope">
              {{ parseInt(scope.row.encrypt) === 1 ? '是' : '否' }}
            </template>
          </el-table-column>
          <el-table-column
            align="center"
            label="通道网关"
          >
            <template slot-scope="scope">
              <span v-for="(item,index) in scope.row.gateway_list"  :key='index' class="gateway_item">{{ item.name }}({{ item.third_temp_id }} {{ item.prepared }})</span>
            </template>
          </el-table-column>
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
                @change="switchTplStatus(scope.row)"
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
  import { fetchSmsTplList, fetchFilterOptions, fetchSmsTpl, fetchTplEnable, fetchSmsTplExport } from '@/api/smsTpl'
  import { export_json_to_excel } from '@/excel/Export2Excel'
  import { isEmptyObject } from '@/utils/index'
  export default {
    name: 'smsTpl',
    data() {
      return {
        tplText: '',
        tplTypeVal: '0',
        tplTypeOptions: [{
          value: '0',
          label: '请选择'
        }],
        signatureVal: '0',
        signatureOptions: [{
          value: '0',
          label: '请选择'
        }],
        gatewayVal: '0',
        gatewayOptions: [{
          value: '0',
          label: '请选择'
        }],
        enableVal: '100000',
        enableOptions: [{
          value: '100000',
          label: '请选择'
        }, {
          value: '1',
          label: '是'
        }, {
          value: '0',
          label: '否'
        }],
        tableData: [],
        loading: false,
        currentPage: 1,
        totals: 0,
        exportLoading: false
      }
    },
    created() {
      this.getFilterOptions()
      this.getSmsTplList()
    },
    methods: {
      getFilterOptions() {
        fetchFilterOptions().then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if(res.data.data.type_list && res.data.data.type_list.length > 0) {
              res.data.data.type_list.forEach(item => {
                this.tplTypeOptions.push({
                  value: item.id,
                  label: item.name
                })
              })
            }
            if(res.data.data.sign_list && res.data.data.sign_list.length > 0) {
              res.data.data.sign_list.forEach(item => {
                this.signatureOptions.push({
                  value: item.id,
                  label: item.name
                })
              })
            }
            if(res.data.data.gateway_list && res.data.data.gateway_list.length > 0) {
              res.data.data.gateway_list.forEach(item => {
                this.gatewayOptions.push({
                  value: item.id,
                  label: item.name
                })
              })
            }
          }else{
            this.$message.error(res.data.error_msg)
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
        })
      },
      getSmsTplList() {
        this.loading = true
        this.tableData = []
        const queryObj = this.handleArgs()
        fetchSmsTplList(queryObj).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if(!res.data.data.list && this.currentPage > 1) {
              this.currentPage--
              this.getSmsTplList()
            }else{
              if(res.data.data.list) {
                this.tableData = res.data.data.list
                this.tableData.forEach((item, index) => {
                  item.enabled = String(item.enabled)
                })
              }
              this.totals = res.data.data.page.total_number
            }
          }else{
            this.$message.error(res.data.error_msg)
          }
          this.loading = false
        }).catch(res => {
          this.$message.error('网络异常，请稍后重试')
          this.loading = false
        })
      },
      formatJson(filterVal, jsonData) {
        return jsonData.map(v => filterVal.map(j => v[j]))
      },
      switchTplStatus(obj) {
        fetchTplEnable({
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
          this.$message.erro('网络异常，请稍后再试')
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
      handleArgs() {
        const queryObj = {
          content: this.tplText,
          type: parseInt(this.tplTypeVal) === 0 ? '' : this.tplTypeVal,
          sign_id: parseInt(this.signatureVal) === 0 ? '' : this.signatureVal,
          gateway_id: parseInt(this.gatewayVal) === 0 ? '' : this.gatewayVal,
          enabled: parseInt(this.enableVal) === 100000 ? '' : String(this.enableVal),
          page: this.currentPage
        }
        return queryObj
      },
      handleSearch() {
        this.currentPage = 1
        this.getSmsTplList()
      },
      handleExport() {
        const queryObj = this.handleArgs()
        fetchSmsTplExport(queryObj).then(res => {
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
      handleAdd() {
        this.$router.push({
          path: 'createSmsTpl'
        })
      },
      handleEdit(obj) {
        this.$router.push({
          path: 'createSmsTpl',
          query: {
            id: obj.id
          }
        })
      },
      handleSizeChange() {
        this.currentPage = 1
        this.getSmsTplList()
      },
      handleCurrentChange(val) {
        this.currentPage = val
        this.getSmsTplList()
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
  .sms-tpl {
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
    .gateway_item::after{
        content: '，'
    }
    .gateway_item:last-child::after{
        display: none
    }
  }
</style>
