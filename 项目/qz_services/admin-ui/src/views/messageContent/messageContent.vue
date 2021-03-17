<template>
    <div class="sms-tpl">
      <div class="search">
        <div class="yixiang fl mr20">
          消息内容：<br>
          <el-input
            v-model="keyword"
            placeholder="请输入消息内容"
            clearable>
          </el-input>
        </div>
        <div class="fl mr20">
          推送方式：<br>
            <el-select v-model="send_type" placeholder="请选择">
                <el-option label="请选择" value="0"></el-option>
                <el-option label="单发" value="1"></el-option>
                <el-option label="群发" value="2"></el-option>
            </el-select>
        </div>
        <div class="fl mr20">
          消息类型：<br>
          <el-select v-model="type_id" placeholder="请选择">
            <el-option
              v-for="item in signatureOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </div>
        <div class="fl mr20">
          是否启用：<br>
          <el-select v-model="enabled" placeholder="请选择">
            <el-option label="请选择" value="0"></el-option>
            <el-option label="是" value="1"></el-option>
            <el-option label="否" value="2"></el-option>
          </el-select>
          <!-- <el-select v-model="enableVal" placeholder="请选择">
            <el-option
              v-for="item in enableOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select> -->
        </div>
        <div>
          <br>
          <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
          <el-button plain @click="handleExport" v-loading="exportLoading">导出</el-button>
          <el-button type="primary" @click="handleAdd" class="fr">新增消息内容</el-button>
        </div>
      </div>
      <div class="qian-main">
        <el-table
            v-loading="loading"
            :data="tableData"
            border
            class="cell"
        >
          <el-table-column
            align="center"
            prop="id"
            label="消息ID"
          />
          <el-table-column
            align="center"
            prop="notice"
            label="消息提醒内容"
          />
          <el-table-column
            align="center"
            prop="title"
            label="消息标题"
          />
          <el-table-column
            align="center"
            prop="content"
            label="消息内容"
          />
          <el-table-column
            align="center"
            prop=""
            label="应用平台链接"
            
          >
            <template slot-scope="scope">
                <div class="cell gao" >
                    <span v-show="scope.row.link_list == null">----</span>
                    <!-- <span v-for="item in scope.row.link_list" :key="item.app_slot">{{ item.app_name }}({{ item.link }})</span> -->
                    <div v-for="item in scope.row.link_list" :key="item.app_slot" >
                        <p class="cell">{{ item.app_name | ellipsis}}</p>
                        <p class="cell">({{ item.link | ellipsis}})</p>
                    </div>
                </div>
            </template>
          </el-table-column>
          <el-table-column
            align="center"
            prop="send_type"
            label="推送方式"
            >
            <template slot-scope="scope">
              {{scope.row.send_type === 1 ? '单发' : '群发' }}
            </template>
          </el-table-column>
          <el-table-column
            align="center"
            prop="type_name"
            label="消息类型"
          />
          <el-table-column
            align="center"
            prop="creator_name"
            label="添加人"
          />
          <el-table-column
            align="center"
            prop="created_date"
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
  import { fetchMessageType, fetchMessagelList, fetchMessageExport,fetchMessageEnable } from '@/api/messageContent'

  import { export_json_to_excel } from '@/excel/Export2Excel'
  import { isEmptyObject } from '@/utils/index'
  export default {
    name: 'messageContent',
    data() {
      return {
        keyword:'', // 搜索关键字
        send_type:'', // 推送方式
        type_id:'',  // 消息类型
        enabled:'', // 是否启用
        page:'', // 页码
        signatureVal: '0',
        signatureOptions: [{
          value: '0',
          label: '请选择'
        }],
        tableData: [],
        loading: false,
        currentPage: 1,
        totals: 0,
        exportLoading: false
      }
    },
    filters: {
        ellipsis(value) {
            if (!value) return "";
            if (value.length > 10) {
                return value.slice(0, 10) + "...";
            }
            return value;
        }
    },
    created() {
      this.getFilterOptions()
      // 获取列表信息
      this.getSmsTplList()
    },
    methods: {
      getFilterOptions() {
        fetchMessageType().then(res => {
            if(res.data.data.list && res.data.data.list.length > 0) {
              res.data.data.list.forEach(item => {
                this.signatureOptions.push({
                  value: item.id,
                  label: item.name
                })
              })
            }
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
        })
      },
      // 获取列表数据
      getSmsTplList() {
        this.loading = true
        this.tableData = []
        const queryObj = this.handleArgs()
        fetchMessagelList(queryObj).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if(!res.data.data.list && this.currentPage > 1) {
              this.currentPage--
              this.getSmsTplList()
            }else{
              if(res.data.data.list) {
                this.tableData = res.data.data.list
                // 是否启用要求必须为字符串的123
                this.tableData.forEach((item, index) => {
                    item.enabled = String(item.enabled)
                    item.send_type = Number(item.send_type)
                    item.notice = item.notice == '' ? '----': item.notice
                    item.title = item.title == ''? '----' : item.title
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
      switchTplStatus(obj) {
        var qur = {
           id: obj.id,
          enabled: parseInt(obj.enabled)
        }
        console.log('修改状态',qur);
        
        fetchMessageEnable({
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
          keyword : this.keyword || '',
          send_type : !this.send_type || parseInt(this.send_type) === 0 ? '' : this.send_type,
          type_id : !this.type_id || parseInt(this.type_id) === 0 ? '' : this.type_id,
          enabled : !this.enabled || parseInt(this.enabled) === 0 ? '' : this.enabled,
          page : this.currentPage 
        }
        return queryObj
      },
      handleSearch() {
        this.currentPage = 1
        this.getSmsTplList()
      },
      handleExport() {
        const queryObj = this.handleArgs()
        fetchMessageExport(queryObj).then(res => {
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
          path: 'addMessage'
        })
      },
      handleEdit(obj) {
        this.$router.push({
          path: 'addMessage',
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
      content: "";
    }
    .cell{
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }
    .gao{
        height:80px;
        line-height: 80px;
    }
  }
</style>
