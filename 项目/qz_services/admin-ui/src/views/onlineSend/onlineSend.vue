<template>
  <div class="online-send">
    <div class="main">
      <div class="title">
        <h4 class="title-fl">APP在线推送</h4>
        <el-button type="primary" class="btn-fr" @click="addClick">新增推送内容</el-button>
      </div>
      <div class="form">
        <el-form :model="formData" :inline="true" label-position="top">
          <el-form-item label="消息标题">
            <el-input v-model="formData.title" clearable placeholder="请输入消息标题" />
          </el-form-item>
          <el-form-item label="推送方式">
            <el-select v-model="formData.type" clearable placeholder="请选择">
              <el-option label="群发" value="1" />
              <el-option label="单发" value="2" :disabled="true" />
            </el-select>
          </el-form-item>
          <el-form-item class="btn">
            <el-button type="primary" @click="onQuery">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
      <div class="table">
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
            width="120"
          />
          <el-table-column
            align="center"
            prop="title"
            label="消息标题"
          />
          <el-table-column
            align="center"
            prop="body"
            label="消息提醒内容"
          />
          <el-table-column
            align="center"
            label="应用平台链接"
          >
            <template slot-scope="scope">
              <p v-for="(item, index) in scope.row.link_list" :key="index">{{ item.app_name }} <br> ({{ item.link }})</p>
            </template>
          </el-table-column>
          <el-table-column
            align="center"
            prop="leixing_name"
            label="消息类型"
            width="120"
          />
          <el-table-column
            align="center"
            prop="push_type"
            label="推送方式"
            width="80"
          >
            <template slot-scope="scope">
              {{ scope.row.push_type === 2 ? '单发' : '群发' }}
            </template>
          </el-table-column>
          <el-table-column
            align="center"
            prop="send_count"
            label="发送数"
            width="100"
          />
          <el-table-column
            align="center"
            prop="read_count"
            label="打开数"
            width="100"
          />
          <el-table-column
            align="center"
            prop="operator_name"
            label="添加人"
            width="100"
          />
          <el-table-column
            align="center"
            prop="created_at_string"
            label="添加时间"
            width="155"
          />
          <el-table-column
            align="center"
            prop="push_time_string"
            label="推送时间"
            width="155"
          />
          <el-table-column
            label="操作"
            width="50"
          >
            <template slot-scope="scope">
              <el-button v-if="scope.row.push_status === 2" type="text" size="small" @click="editClick(scope.row, 0)">查看</el-button>
              <el-button v-else type="text" size="small" @click="editClick(scope.row, 1)">编辑</el-button>
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
  </div>
</template>

<script>
import { fetchSendlList } from '@/api/onlineSend'
export default {
  name: 'OnlineSend',
  data() {
    return {
      loading: false,
      formData: {
        title: '',
        type: ''
      },
      tableData: [],
      currentPage: 1,
      totals: 0
    }
  },
  created() {
    this.getList()
  },
  methods: {
    onQuery() {
      this.currentPage = 1
      this.getList()
    },
    handleSizeChange() {
      this.currentPage = 1
      this.getList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.getList()
    },
    getList() {
      const that = this
      that.loading = true
      let obj = {}
      obj = this.formData
      obj.page = this.currentPage
      fetchSendlList(obj).then(res => {
        that.loading = false
        if (res.status === 200 && res.data.error_code === 0) {
          that.tableData = res.data.data.list
          that.totals = res.data.data.page.total_number
        } else {
          that.$message.error(res.data.error_msg)
        }
      })
    },
    addClick() {
      this.$router.push({
        path: `/messageManage/onlineSendAdd/`
      })
    },
    editClick(val, off) {
      this.$router.push({
        path: '/messageManage/onlineSendAdd/',
        query: {
          id: val.id,
          isEdit: off
        }
      })
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
  .online-send{
    padding: 20px;
    .main{
      border-top: 3px solid #999;
      padding:0 20px 20px;
      background: #fff;
      .title{
        overflow: hidden;
        .title-fl{
          display: inline-block;
        }
        .btn-fr{
          margin-top: 20px;
          float: right;
        }
        h4{
          font-size: 16px;
          font-weight: normal;
          line-height: 1;
        }
      }
      .el-pagination{
        text-align: center;
        margin-top: 20px;
      }
      .btn{
          margin-left: 40px;
          padding-top: 46px;;
        }
    }
  }
</style>
