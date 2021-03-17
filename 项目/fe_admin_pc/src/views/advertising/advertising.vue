<template>
  <div class="advertising">
    <tableSearch title="广告管理">
      <el-form slot="from" :inline="true">
        <el-form-item label="广告名称">
          <el-input v-model="name" clearable placeholder="广告名称" />
        </el-form-item>
        <el-form-item label="广告位置">
          <el-select v-model="pageLocation" clearable placeholder="广告位置">
            <el-option label="APP首页轮播" value="1" />
            <el-option label="PC端首页轮播" value="2" />
            <el-option label="PC端分类广告" value="3" />
          </el-select>
        </el-form-item>
        <!-- <el-form-item label="上线/下线">
          <el-select v-model="status" clearable placeholder="上线/下线">
            <el-option label="上线" value="1" />
            <el-option label="下线" value="2" />
          </el-select>
        </el-form-item> -->
        <el-form-item label="时间" prop="date">
          <el-date-picker
            v-model="date"
            clearable
            type="daterange"
            value-format="yyyy-MM-dd"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="到期日期"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="onSearch">搜索</el-button>
          <el-button type="success" @click="addAdvert">添加广告</el-button>
          <el-button type="danger" @click="deleteAdvert">批量删除</el-button>
        </el-form-item>
      </el-form>
      <el-table
        slot="table"
        v-loading="tableLoading"
        :data="tableData"
        border
        style="width: 100%"
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="55" align="center" />
        <el-table-column prop="id" label="编号" align="center" />
        <el-table-column prop="name" label="广告名称" align="center" />
        <el-table-column label="广告位置" align="center">
          <template slot-scope="scope">
            {{ scope.row.pageLocation | pageLocation }}
          </template>
        </el-table-column>
        <el-table-column prop="salePrice" label="广告图片" align="center">
          <template slot-scope="scope">
            <el-image
              style="width: 100px; height: 100px"
              :src="scope.row.bannerUrl"
              :preview-src-list="[scope.row.bannerUrl]"
              fit="fit"
            />
          </template>
        </el-table-column>
        <el-table-column prop="saleNum" label="时间" align="center" width="250">
          <template slot-scope="scope">
            <div>开始时间：{{ scope.row.startTime }}</div>
            <div>到期时间：{{ scope.row.endTime }}</div>
          </template>
        </el-table-column>
        <el-table-column prop="purchasePrice" label="上线/下线" align="center">
          <template slot-scope="scope">
            {{ scope.row.status | status }}
          </template>
        </el-table-column>
        <el-table-column prop="clickCount" label="点击次数" align="center" />
        <el-table-column label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="edit(scope.row.id)">编辑</el-button>
            <el-button type="text" @click="top(scope.row.id)">置顶</el-button>
            <el-button type="text" @click="deleteItem(scope.row.id)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
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
  name: 'Advertising',
  filters: {
    // <el-option label="APP首页轮播" value="1" />
    // <el-option label="PC端首页轮播" value="2" />
    // <el-option label="PC端分类广告" value="3" />
    status(value) {
      switch (value) {
        case '1':
          return '是'
        case '2':
          return '否'
        default:
          return '----'
      }
    },
    pageLocation(value) {
      switch (value) {
        case '1':
          return 'APP首页轮播'
        case '2':
          return 'PC端首页轮播'
        case '3':
          return 'PC端分类广告'
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
      name: '',
      pageLocation: '',
      status: '',
      date: '',
      startTime: '',
      endTime: '',
      tableData: [],
      deldetList: []
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
    handleData() {
      const obj = {
        pageNo: this.currentPage,
        pageSize: this.pageSize,
        startTime: this.startTime,
        endTime: this.endTime,
        name: this.name,
        status: this.status,
        pageLocation: this.pageLocation
      }
      return obj
    },
    getList() {
      const that = this
      that.tableLoading = true
      const obj = this.handleData()
      this.$apis.ADVERT.list(obj).then(res => {
        that.tableLoading = false
        if (res.code === 0) {
          that.tableData = res.data || []
          that.total = res.page.total
          that.pageSize = res.page.pageSize
          that.currentPage = res.page.pageNo
        } else {
          that.$message.error(res.message)
        }
      }).catch(err => {
        console.log(err.message)
        that.tableLoading = false
        // that.$message.error(err.message)
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
    addAdvert(id) {
      this.$router.push('/advertising/advertising-add')
    },
    edit(id) {
      this.$router.push('/advertising/advertising-add?id=' + id)
    },
    top(id) {
      this.$confirm('确定置顶该条数据?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.$apis.ADVERT.top({ id }).then(res => {
          if (res.code === 0) {
            this.$message.success(res.message)
            this.getList()
          } else {
            this.$message.error(res.message)
          }
        })
      }).catch(() => {})
    },
    deleteItem(id) {
      this.$confirm('确定删除该条数据?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.$apis.ADVERT.deleteBanner({ ids: [id] }).then(res => {
          if (res.code === 0) {
            this.$message.success(res.message)
            this.getList()
          } else {
            this.$message.error(res.message)
          }
        })
      }).catch(() => {})
    },
    deleteAdvert() {
      if (this.deldetList.length > 0) {
        this.$confirm('确定批量删除选择数据?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          const ids = this.deldetList.map(item => {
            return item.id
          })
          this.$apis.ADVERT.deleteBanner({ ids }).then(res => {
            if (res.code === 0) {
              this.$message.success(res.message)
              this.getList()
            } else {
              this.$message.error(res.message)
            }
          })
        }).catch(() => {})
      } else {
        this.$message.warning('请选择数据')
      }
    },
    handleSelectionChange(val) {
      console.log(val)
      this.deldetList = val
    }
  }
}
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>
.advertising{
  padding: 20px;
}
</style>
