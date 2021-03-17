<template>
  <div class="word-manage">
    <el-form slot="from" ref="ruleForm" :inline="true" :model="formData" label-width="100px">
      <el-form-item label="商品：" prop="age">
        <el-input v-model.number="formData.goodsName" clearable placeholder="请输入商品名称" />
      </el-form-item>
      <el-form-item label="推荐来源：">
        <el-select v-model="formData.sourceId" clearable placeholder="请选择">
          <el-option v-for="item in sourceList" :key="item.id" :label="item.value" :value="item.id" />
        </el-select>
      </el-form-item>
      <el-form-item label="审核状态：">
        <el-select v-model="formData.statusId" clearable placeholder="请选择">
          <el-option v-for="item in statusList" :key="item.id" :label="item.value" :value="item.id" />
        </el-select>
      </el-form-item>
      <el-form-item class="button">
        <el-button type="primary" @click="onHandleSearch">查询</el-button>
        <el-button @click="onHandleReset">重置</el-button>
      </el-form-item>
    </el-form>
    <el-button class="xiajia" type="primary">批量下架</el-button>
    <el-table
      slot="table"
      :data="tableData"
      border
      style="width: 100%"
      @selection-change="handleSelectionChange"
    >
      <el-table-column type="selection" width="55" align="center" />
      <el-table-column label="商品图片" align="center" style="display: table-cell;vertical-align: middle;">
        <template slot-scope="scope">
          <el-image
            style="width: 100px; height: 100px"
            :src="scope.row.goodsCover"
            :preview-src-list="[scope.row.goodsCover]"
            fit="fit"
          />
        </template>
      </el-table-column>
      <el-table-column prop="name" label="商品名称" align="center" />
      <el-table-column prop="word" label="推荐语" align="center" />
      <el-table-column prop="person" label="推荐人" align="center" />
      <el-table-column prop="time" label="推荐时间" align="center" />
      <el-table-column prop="source" label="推荐来源" align="center" />
      <el-table-column label="审核状态" align="center">
        <template slot-scope="scope">
          <span v-if="scope.row.status === 0" class="red">待发布</span>
          <span v-else-if="scope.row.status === 1" class="green">已发布</span>
          <span v-else-if="scope.row.status === 2" class="grey">下架</span>
        </template>
      </el-table-column>
      <el-table-column label="审核人/审核时间" align="center">
        <template slot-scope="scope">
          <p>{{scope.row.s_person}}</p>
          <p>{{scope.row.s_time}}</p>
        </template>
      </el-table-column>
      <el-table-column label="操作" align="center">
        <template slot-scope="scope">
          <el-button type="text" v-if="scope.row.status != 0" @click="state(scope.row.id,scope.row.status)">审核</el-button>
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
  </div>
</template>

<script>
export default {
  name: 'ContentNote',
  components: {},
  data() {
    return {
      formData: {
        goodsName: '',
        sourceId: '',
        statusId: ''
      },
      sourceList: [
        {
          id: 1,
          value: '笔记'
        },
        {
          id: 2,
          value: '文章'
        }
      ],
      statusList: [
        {
          id: 1,
          value: '待审核'
        },
        {
          id: 2,
          value: '通过'
        },
        {
          id: 3,
          value: '不通过'
        }
      ],
      currentPage: 1,
      pageSize: 20,
      total: 0,
      tableData: [
        {
          goodsCover: '',
          person: '测试',
          time: '2021-12-23',
          word: '测试2',
          source: '百度',
          status: 1,
          s_person: '张三',
          s_time: '2012-05-15'
        }
      ]
    }
  },
  computed: {},
  watch: {},
  created() {
  },
  methods: {
    onHandleSearch() {

    },
    onHandleReset() {
      this.formData.goodsName = ''
      this.formData.sourceId = ''
      this.formData.statusId = ''
    },
    handleSizeChange(val) {
      this.currentPage = 1
      this.pageSize = val
    },
    handleCurrentChange(val) {
      this.currentPage = val
    },
    handleSelectionChange(val) {
      console.log(val)
    },
    // 启用禁用
    state(id, val) {
      this.$confirm(`确认${val === 2 ? '上' : '下'}架后，该商品将${val === 2 ? '' : '不'}在前台展示。`, `确认${val === 2 ? '上架' : '下架'}该商品？`, {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {

      }).catch(() => {})
    },
  }
}
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>
.recommend-word{
  padding: 20px;
  .title{
    font-size: 20px;
    line-height: 30px;
    padding-bottom: 20px;
  }
  .el-form{
    border-bottom: 3px solid #999;
    margin-top: 10px;
  }
  .xiajia{
    margin: 20px 20px;
  }
}
</style>
