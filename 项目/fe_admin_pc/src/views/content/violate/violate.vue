<template>
  <div class="content-violate">
    <tableSearch title="违规管理">
      <el-form slot="from" ref="ruleForm" :inline="true" :model="formData">
        <h4>查询条件</h4>
        <el-form-item label="标题">
          <el-input v-model="formData.title" clearable placeholder="请输入标题" />
        </el-form-item>
        <el-form-item label="发布来源">
          <el-select v-model="formData.source" clearable placeholder="请选择">
            <el-option label="笔记" value="1" />
            <el-option label="文章" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="审核状态">
          <el-select v-model="formData.status" clearable placeholder="请选择">
            <el-option label="待审核" value="1" />
            <el-option label="通过" value="2" />
            <el-option label="不通过" value="3" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="onSearch">查询</el-button>
          <el-button @click="onReset">重置</el-button>
        </el-form-item>
        <el-form-item class="fr">
          <el-button type="primary" @click="onSearch">批量下架</el-button>
          <el-button type="primary" @click="onSort">推荐排序</el-button>
        </el-form-item>
      </el-form>
      <el-table
        slot="table"
        :data="tableData"
        border
        style="width: 100%"
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="55" align="center" />
        <el-table-column label="图片" align="center" style="display: table-cell;vertical-align: middle;">
          <template slot-scope="scope">
            <el-image
              style="width: 100px; height: 100px"
              :src="scope.row.goodsCover"
              :preview-src-list="[scope.row.goodsCover]"
              fit="fit"
            />
          </template>
        </el-table-column>
        <el-table-column label="违规内容" align="center">
          <template slot-scope="scope">
            <p class="title">{{scope.row.title}}</p>
            <p class="description">
              <span>{{scope.row.name}}</span>
              <span>{{scope.row.city}}</span>
              <span>{{scope.row.time}}</span>
            </p>
          </template>
        </el-table-column>
        <el-table-column prop="reason" label="最近一次违规原因" align="center" />
        <el-table-column prop="source" label="发布来源" align="center" />
        <el-table-column prop="number" label="违规次数" align="center" />
        <el-table-column label="发布状态" align="center">
          <template slot-scope="scope">
            <span v-if="scope.row.status === 0" class="red">待发布</span>
            <span v-else-if="scope.row.status === 1" class="green">已发布</span>
            <span v-else-if="scope.row.status === 2" class="grey">下架</span>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="state(scope.row.id)">查看</el-button>
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
      <el-dialog
        title=""
        :visible.sync="dialogVisible"
        width="25%"
        :before-close="handleClose">
        <div>
          <el-row>
            <el-col :span="8">推荐内容</el-col>
            <el-col :span="8">排序值</el-col>
          </el-row>
          <el-row>
            <el-col :span="8">芝麻开门芝麻开门</el-col>
            <el-col :span="8">
              <el-input v-model="diaFormData.px" clearable placeholder="请输入排序值" />
            </el-col>
          </el-row>
          <el-row style="margin-left: 80px;">
            <el-button @click="dialogVisible = false">取消</el-button>
            <el-button type="primary" @click="onHandleDiaSubmit">确定</el-button>
          </el-row>
        </div>
      </el-dialog>
    </tableSearch>
  </div>
</template>

<script>
export default {
  name: 'ContentViolate',
  components: {},
  data() {
    return {
      currentPage: 1,
      pageSize: 20,
      total: 0,
      formData: {
        title: '',
        source: '',
        status: ''
      },
      tableData: [
        {
          goodsCover: '',
          content: '测试',
          title: '芝麻开门',
          name: '张三三',
          city: '苏州',
          time: '2020-10-14',
          reason: '111',
          number: 'ceshi22',
          source: '百度/APP',
          status: 1
        }
      ],
      dialogVisible: false,
      diaFormData: {
        px: 1
      }
    }
  },
  computed: {},
  watch: {},
  created() {
  },
  methods: {
    onSearch() {

    },
    onReset() {
      this.formData.title = ''
      this.formData.source = ''
      this.formData.status = ''
    },
    onSort() {
      this.dialogVisible = true
    },
    handleSelectionChange(val) {
      console.log(val)
    },
    state(id) {
      this.$router.push('/content/note-manage?id=' + id)
    },
    handleSizeChange(val) {
      this.currentPage = 1
      this.pageSize = val
    },
    handleCurrentChange(val) {
      this.currentPage = val
    },
    handleClose(done) {
      this.dialogVisible = false
    },
    onHandleDiaSubmit() {
      console.log(333)
    }
  }
}
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>
.content-violate{
  padding: 20px;
  h4{
    font-weight: normal;
    margin-bottom: 20px;
  }
  .title{
    margin-bottom: 10px;
    font-weight: 700;
  }
  .description span{
    margin-right: 10px;
  }
  .description span:last-child{
    margin-right: 0;
  }
  .el-col{
    height: 40px;
    line-height: 40px;
    margin-bottom: 10px;
  }
}
</style>
