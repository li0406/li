<template>
    <div class="sms-tpl-dialog">
      <el-dialog title="选择短信模板" :visible="dialogFormVisible" :close-on-click-modal="false" @close="closeDia">
        <div style="height: 60vh;overflow-y: auto;">
          <div class="sms-search clearfix">
            <div class="fl filter-input mr20">
              模板类型：
              <el-select v-model="tplTypeVal" placeholder="请选择">
                <el-option
                  v-for="item in tplType"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                />
              </el-select>
            </div>
            <div class="fl filter-input mr20">
              模板内容：
              <el-input
                v-model="tplText"
                placeholder="请输入模板内容"
                clearable>
              </el-input>
            </div>
            <div class="fl">
              <el-button type="primary" icon="el-icon-search" @click="handleSearch">查询</el-button>
            </div>
          </div>
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
              label="操作"
            >
              <template slot-scope="scope">
                <span class="link-blue pointer" @click="handleChose(scope.row)">选择</span>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </el-dialog>
    </div>
</template>

<script>
  import { fetchSmsSendTplList } from '@/api/smsTpl'
  export default {
    name: 'smsTplDialog',
    props: {
      tplType: {
        type: Array,
        default: []
      },
      dialogFormVisible: {
        type: Boolean,
        default: false
      }
    },
    data() {
      return {
        formLabelWidth: '30',
        tplTypeVal: '0',
        tplText: '',
        tableData: [],
        loading: false
      }
    },
    watch: {
      dialogFormVisible(val) {

      }
    },
    methods: {
      getSmsTplList() {
        if(parseInt(this.tplTypeVal) === 0) {
          this.$message.error('请选择模板类型')
          return
        }
        this.loading = true
        fetchSmsSendTplList({
          content: this.tplText,
          type: this.tplTypeVal
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.tableData = []
            const data = res.data.data
            if(data){
              for(let p in res.data.data) {
                if(parseInt(data[p].type) === 1) {
                  data[p].type_name = '验证码通知(行业)'
                }else if(parseInt(data[p].type) === 2) {
                  data[p].type_name = '营销'
                }
                else if(parseInt(data[p].type) === 3) {
                  data[p].type_name = '国际验证码'
                }else if(parseInt(data[p].type) === 4) {
                  data[p].type_name = '语音验证码'
                }
                this.tableData.push(data[p])
              }
            }
          }else{
            this.$message.error(res.data.error_msg)
          }
          this.loading = false
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
          this.loading = false
        })
      },
      handleSearch() {
        if(parseInt(this.tplTypeVal) === 0 && !this.tplText) {
          this.$message.error('请选择模板类型或填写模板内容')
          return
        }
        this.getSmsTplList()
      },
      handleChose(obj) {
        this.closeDia(obj)
      },
      closeDia(tag) {
        this.$emit('closeAddDia', tag)
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
  .sms-tpl-dialog{
    .sms-search{
      margin-bottom: 20px;
      div{
        width: auto;
      }
    }
  }
</style>
