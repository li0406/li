<template>
  <div class="feed-manage">
    <tableSearch title="FEED流管理">
      <el-form slot="from" :inline="true">
        <el-form-item label="id">
          <el-input v-model="id" clearable placeholder="id" />
        </el-form-item>
        <el-form-item label="标题">
          <el-input v-model="title" clearable placeholder="标题" />
        </el-form-item>
        <el-form-item label="有效状态" prop="status">
          <el-select v-model="status" clearable placeholder="有效状态">
            <el-option label="未删除" value="0" />
            <el-option label="已删除" value="1" />
          </el-select>
        </el-form-item>
        <el-form-item label="时间" prop="date">
          <el-date-picker
            v-model="date"
            clearable
            type="daterange"
            value-format="yyyy-MM-dd"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="onSearch">搜索</el-button>
        </el-form-item>
      </el-form>
      <el-table
        slot="table"
        v-loading="tableLoading"
        :data="tableData"
        border
        style="width: 100%"
      >
        <el-table-column type="index" label="显示顺序" align="center" width="80" />
        <el-table-column prop="contentId" label="内容ID" align="center" />
        <el-table-column prop="title" label="标题" align="center" />
        <el-table-column prop="contentType" label="类型" align="center">
          <template slot-scope="scope">
            {{ scope.row.contentType | contentType }}
          </template>
        </el-table-column>
        <el-table-column prop="commentCount" label="评论数" align="center" />
        <el-table-column prop="thumbCount" label="点赞数" align="center" />
        <el-table-column prop="collectCount" label="收藏数" align="center" />
        <el-table-column prop="viewCount" label="浏览数" align="center" />
        <el-table-column prop="shareCount" label="分享数" align="center" />
        <el-table-column label="权重值(可调)" align="center">
          <template slot-scope="scope">
            <span v-if="!scope.row.showEdit" class="mr10">{{ scope.row.selfWeight }}</span>
            <!-- <i v-if="!scope.row.showEdit" class="el-icon-edit" @click="edit(scope.row)" />
            <el-input v-if="scope.row.showEdit" v-model="scope.row.selfWeight" size="mini" class="w20" type="text" />
            <i v-if="scope.row.showEdit" class="el-icon-folder" /> -->
            <el-button type="text" class="operate" icon="el-icon-edit" @click="handleEdit(scope.row)" />
          </template>
        </el-table-column>
        <el-table-column prop="hotValue" label="总热度(综合得分)" align="center" />
        <el-table-column label="可见状态" align="center">
          <template slot-scope="scope">
            <el-switch v-model="scope.row.isDelete" active-value="0" inactive-value="1" @change="deleteSwith($event, scope.row)" />
          </template>
        </el-table-column>
      </el-table>
      <div class="mt20 tips">
        <p>提升权重值即可提升排名</p>
        <p>总热度(综合得分) =  { (点赞 * 0.4)  +(收藏  * 0.3)  + (评论  * 0.4)    +  (浏览or播放  * 0.25 ) + (分享 * 0.4 ) + (权重值[默认1] *  1）}   *  (if 最新发布 2 else 1)</p>
      </div>
      <el-pagination
        v-if="total"
        class="mt20 text-center"
        :current-page="currentPage"
        :page-sizes="[10, 20, 50, 100]"
        :page-size="pageSize"
        layout="total, sizes, prev, pager, next, jumper"
        :total="total"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </tableSearch>
  </div>
</template>

<script>
export default {
  name: 'ContentTime',
  filters: {
    contentType(value) {
      switch (value) {
        case '0':
          return '默认'
        case '1':
          return '大家问'
        case '2':
          return '笔记'
        default:
          return '----'
      }
    }
  },
  data() {
    return {
      currentPage: 1,
      pageSize: 20,
      total: 0,
      tableLoading: false,
      id: '',
      title: '',
      status: '',
      orderBy: '',
      startTime: '',
      endTime: '',
      date: '',
      tableData: [],
      dialogVisible: false
    }
  },
  watch: {
    date: {
      handler(val) {
        this.startTime = !val ? '' : val[0] + ' 00:00:00'
        this.endTime = !val ? '' : val[1] + ' 23:59:59'
      }
    }
  },
  created() {
    this.getList()
  },
  methods: {
    onSearch() {
      this.currentPage = 1
      this.getList()
    },
    edit(item) {
      console.log(item)
      item.showEdit = true
    },
    getList() {
      this.tableLoading = true
      const obj = {
        pageNo: this.currentPage,
        pageSize: this.pageSize,
        name: this.name,
        id: this.id,
        title: this.title,
        status: this.status,
        orderBy: this.orderBy,
        startTime: this.startTime,
        endTime: this.endTime
      }
      this.$apis.CONTENT.feedList(obj).then(res => {
        this.tableLoading = false
        if (res.code === 0) {
          this.tableData = res.data
          this.total = res.page.total
          this.pageSize = res.page.pageSize
          this.currentPage = res.page.pageNo
        } else {
          this.$message.error(res.message)
        }
      }).catch(err => {
        this.tableLoading = false
        console.log(err)
      })
    },
    handleSizeChange(val) {
      this.currentPage = 1
      this.pageSize = val
      this.getList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.getList()
    },
    handleEdit(item) {
      this.$prompt('请输入权重值', '权重编辑', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        closeOnClickModal: false,
        inputValue: item.selfWeight,
        inputPattern: /^((\+|-)?[0-9][0-9]*(\.[1-9])?|0\.[1-9])$/,
        inputErrorMessage: '请输入数字，保留一位小数'
      }).then(({ value }) => {
        const obj = {
          id: item.id,
          selfWeight: value
        }
        this.$apis.CONTENT.feedUpdate(obj).then(res => {
          if (res.code === 0) {
            this.$message.success(res.message)
            this.getList()
          } else {
            this.$message.error(res.message)
          }
        })
      }).catch(() => {})
    },
    deleteSwith(val, item) {
      const obj = {
        id: item.id,
        status: val
      }
      this.$apis.CONTENT.feedUpdate(obj).then(res => {
        if (res.code === 0) {
          this.$message.success(res.message)
          this.getList()
        } else {
          this.$message.error(res.message)
        }
      })
    }
  }
}
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>
.feed-manage{
  padding: 20px;
  .tips{
    margin-top: -10px;
    margin-left: 20px;
    font-size: 14px;
    line-height: 1.5;
  }
  .w20{
    width: 60px;
    margin-right: 10px;
  }
}
</style>
