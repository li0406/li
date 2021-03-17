<template>
  <div class="content-comment">
    <tableSearch title="评论管理">
      <el-form slot="from" ref="ruleForm" :inline="true" :model="fromData">
        <el-form-item label="标题" prop="goodsName">
          <el-input v-model="fromData.goodsName" clearable placeholder="标题" />
        </el-form-item>
        <el-form-item label="发布来源" prop="goodsSource">
          <el-select v-model="fromData.goodsSource" clearable placeholder="请选择">
            <el-option label="笔记" value="1" />
            <el-option label="文章" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="审核状态" prop="goodsStatus">
          <el-select v-model="fromData.goodsStatus" clearable placeholder="请选择">
            <el-option label="待审核" value="1" />
            <el-option label="通过" value="2" />
            <el-option label="不通过" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="标签" prop="goodsTagsId">
          <el-select v-model="fromData.goodsTagsId" clearable placeholder="请选择">
            <el-option v-for="item in goodsTags" :key="item.id" :label="item.typeName" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="分类" prop="goodsTypeId">
          <el-select v-model="fromData.goodsTypeId" clearable placeholder="请选择">
            <el-option v-for="item in goodsTypeList" :key="item.id" :label="item.typeName" :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="onSearch">查询</el-button>
          <el-button @click="onReset">重置</el-button>
        </el-form-item>
        <el-form-item>
          <el-button type="primary">批量下架</el-button>
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
        <el-table-column label="图片" align="center">
          <template slot-scope="scope">
            <el-image
              style="width: 100px; height: 100px"
              :src="scope.row.goodsCover"
              :preview-src-list="[scope.row.goodsCover]"
              fit="fit"
            />
          </template>
        </el-table-column>
        <el-table-column prop="goodsNo" label="评论管理" align="center" />
        <el-table-column prop="content" label="评论对象" align="center" />
        <el-table-column prop="goodsSkuName" label="发布来源" align="center" />
        <el-table-column prop="likeCount" label="点赞" align="center" />
        <el-table-column prop="commentCount" label="回复数" align="center" />
        <el-table-column label="内部标签" align="center">
          <template slot-scope="scope">
            <span>{{ scope.row.recommandStatus === '1' ? '已推荐' : '未推荐' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="审核状态" align="center">
          <template slot-scope="scope">
            <span v-if="scope.row.goodsStatus === '0'" class="red">待发布</span>
            <span v-else-if="scope.row.goodsStatus === '1'" class="green">已发布</span>
            <span v-else-if="scope.row.goodsStatus === '2'" class="red">下架</span>
          </template>
        </el-table-column>
        <el-table-column prop="createDate" label="审核人/审核时间" align="center">
          <template slot-scope="scope">
            <p>{{ scope.row.name }}</p>
            <p>{{ scope.row.time }}</p>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="diaCheck(scope.row.id)">审核</el-button>
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
        :before-close="handleClose"
      >
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
          <el-row style="margin-left: 80px;margin-top:20px;">
            <el-button @click="dialogVisible = false">取消</el-button>
            <el-button type="primary" @click="onHandleDiaSubmit">确定</el-button>
          </el-row>
        </div>
      </el-dialog>
      <el-dialog title="项目标题项目标题项目标题项目标题" :visible.sync="dialogDetailVisible" width="40%">
        <div style="overflow: hidden;margin-top: -30px;">
          <div>
            <el-row>发布人：XXX</el-row>
            <el-row>当前状态：已发布</el-row>
            <el-row>审核：
              <el-radio v-model="radio" label="1">已发布</el-radio>
              <el-radio v-model="radio" label="2">下架</el-radio>
            </el-row>
            <el-row class="mtp20">
              <el-input
                v-model="tags"
                type="textarea"
                :rows="2"
                placeholder="请填写标签"
              />
            </el-row>
            <el-row class="mtp20 center">
              <el-button type="primary">提交</el-button>
              <el-button @click="diaCancel">取消</el-button>
            </el-row>
            <el-row class="mtp20 center edit-btn">
              <el-col :span="12">
                <i class="el-icon-arrow-left" @click="pre" />
                <el-row class="hand" @click="pre">上一条</el-row>
              </el-col>
              <el-col :span="12">
                <i class="el-icon-arrow-right" @click="next" />
                <el-row class="hand" @click="next">下一条</el-row>
              </el-col>
            </el-row>
          </div>
        </div>
      </el-dialog>
    </tableSearch>
  </div>
</template>

<script>
export default {
  name: 'ContentComment',
  components: {},
  data() {
    return {
      currentPage: 1,
      pageSize: 20,
      total: 0,
      fromData: {
        goodsName: '',
        goodsNo: '',
        goodsStatus: '',
        goodsSource: '',
        goodsTypeId: '',
        goodsTagsId: ''
      },
      goodsTypeList: [],
      goodsTags: [],
      tableData: [1],
      dialogVisible: false,
      diaFormData: {
        px: 0
      },
      dialogDetailVisible: false,
      radio: '1',
      tags: '111'
    }
  },
  computed: {},
  watch: {},
  created() {
    this.getCommentList()
  },
  methods: {
    onSearch() {
      this.currentPage = 1
      this.getCommentList()
    },
    onReset() {
      this.$refs['ruleForm'].resetFields()
    },
    getCommentList() {
      const obj = {
        postId: '',
        pageNo: this.currentPage,
        pageSize: this.pageSize
      }
      this.$apis.CONTENT.getCommentList(obj).then(res => {
        console.log(res)
        if (res.code === 0) {
          this.tableData = res.data
        }
      })
    },
    handleSizeChange(val) {
      this.currentPage = 1
      this.pageSize = val
    },
    handleCurrentChange(val) {
      this.currentPage = val
    },
    check(id) {

    },
    handleSelectionChange(val) {
      console.log(val)
    },
    onSort() {
      this.dialogVisible = true
    },
    handleClose(done) {
      this.dialogVisible = false
    },
    // 查看
    diaCheck(id) {
      this.dialogDetailVisible = true
    },
    // 取消
    diaCancel() {
      this.dialogDetailVisible = false
    },
    onHandleDiaSubmit() {
      console.log(333)
    },
    pre() {
      console.log(11)
    },
    next() {
      console.log(22)
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.content-comment{
  padding: 20px;
  .el-row{
    line-height: 40px;
  }
  .el-col{
    line-height: 40px;
  }
  .name{
    word-break:break-all;
    word-wrap:break-word;
    overflow: hidden;
    text-overflow:ellipsis;
    white-space: nowrap;
  }
  .mtp20{
    margin-top:20px;
  }
  .center{
    text-align: center;
  }
  .edit-btn{
    margin-top: 80px;
  }
  .edit-btn i{
    font-size: 30px;
    cursor: pointer;
  }
  .hand{
    cursor: pointer;
  }
}
</style>
