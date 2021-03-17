<template>
  <div class="content-photo">
    <tableSearch title="问题分类管理">
      <el-form slot="from" ref="ruleForm" :inline="true" :model="formData">
        <el-form-item label="分类名称">
          <el-input v-model="formData.name" clearable placeholder="请输入分类名称" />
        </el-form-item>
        <el-form-item label="启用状态">
          <el-select v-model="formData.status" clearable placeholder="请选择">
            <el-option label="启用" value="1" />
            <el-option label="停用" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="onSearch">搜索</el-button>
          <el-button @click="onReset">重置</el-button>
        </el-form-item>
        <el-form-item style="float:right;">
          <el-button type="success" @click="add">添加分类</el-button>
        </el-form-item>
      </el-form>
      <el-table
        :data="tableData"
        style="width: 100%;margin-bottom: 20px;"
        row-key="id"
        border
        :tree-props="{children: 'children', hasChildren: 'hasChildren'}"
      >
        <el-table-column prop="sort" label="分类名称" align="center" />
        <el-table-column prop="goodsName" label="别名" align="center" />
        <el-table-column prop="px" label="排序" align="center" />
        <el-table-column label="启用状态" align="center">
          <template slot-scope="scope">
            <span v-if="scope.row.goodsStatus === '0'" class="red">待发布</span>
            <span v-else-if="scope.row.goodsStatus === '1'" class="green">已发布</span>
            <span v-else-if="scope.row.goodsStatus === '2'" class="red">下架</span>
          </template>
        </el-table-column>
        <el-table-column prop="date" label="添加时间" align="center" />
        <el-table-column label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="edit(scope.row.id)">编辑</el-button>
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
        :title="title"
        :visible.sync="dialogVisible"
        width="25%"
        :before-close="handleClose">
        <div>
          <el-form slot="from" ref="diaFormData" :rules="rules" :inline="true" :model="diaFormData" label-width="120px">
            <el-form-item label="分类名称：" prop="fenLeiVal">
              <el-select v-model="diaFormData.fenLeiVal" clearable placeholder="请选择分类名称">
                <el-option v-for="item in fenLeiList" :key="item.id" :label="item.value" :value="item.id" />
              </el-select>
            </el-form-item>
            <el-form-item label="上级名称：">
              <el-select v-model="diaFormData.upVal" clearable placeholder="请选择上级名称">
                <el-option v-for="item in upList" :key="item.id" :label="item.value" :value="item.id" />
              </el-select>
              <p class="grey">不选择分类替代为顶级分类</p>
            </el-form-item>
            <el-form-item label="排序值：" prop="px">
              <el-input v-model.number="diaFormData.px" clearable placeholder="请输入显示顺序" />
              <p class="grey">排序值越大优先级越高</p>
            </el-form-item>
            <el-form-item label="启用状态：">
              <el-switch v-model="diaFormData.status"></el-switch>
            </el-form-item>
            <el-form-item class="button">
              <el-button type="primary" @click="onHandleDiaSubmit('diaFormData')">确定</el-button>
              <el-button @click="dialogVisible = false">取消</el-button>
            </el-form-item>
          </el-form>
        </div>
      </el-dialog>
    </tableSearch>
  </div>
</template>

<script>
export default {
  name: 'ContentPhoto',
  components: {},
  data() {
    return {
      currentPage: 1,
      pageSize: 20,
      total: 0,
      formData: {
        name: '',
        status: ''
      },
      tableData: [{
        id: 1,
        date: '2016-05-02',
        sort: '1111',
        address: '上海市普陀区金沙江路 1518 弄'
      }, {
        id: 2,
        date: '2016-05-04',
        sort: '2222',
        address: '上海市普陀区金沙江路 1517 弄'
      }, {
        id: 3,
        date: '2016-05-01',
        sort: '3333',
        address: '上海市普陀区金沙江路 1519 弄',
        children: [{
          id: 31,
          date: '2016-05-01',
          sort: '4444',
          address: '上海市普陀区金沙江路 1519 弄'
        }, {
          id: 32,
          date: '2016-05-01',
          sort: '55555',
          address: '上海市普陀区金沙江路 1519 弄'
        }]
      }, {
        id: 4,
        date: '2016-05-03',
        sort: '6666',
        address: '上海市普陀区金沙江路 1516 弄'
      }],
      title: '新增分类',
      dialogVisible: false,
      diaFormData: {
        fenLeiVal: '',
        upVal: '',
        px: 0,
        status: ''
      },
      fenLeiList: [
        {
          id: 1,
          value: '测试1'
        },
        {
          id: 2,
          value: '测试2'
        },
        {
          id: 3,
          value: '测试3'
        }
      ],
      upList: [
        {
          id: 1,
          value: '测试1'
        },
        {
          id: 2,
          value: '测试2'
        },
        {
          id: 3,
          value: '测试3'
        }
      ],
      rules: {
        fenLeiVal: [
          { required: true, message: '请选择分类名称', trigger: 'change' }
        ]
      }
    }
  },
  computed: {},
  watch: {},
  created() {
  },
  methods: {
    handleSelectionChange(val) {
      console.log(val)
    },
    onSearch() {

    },
    onReset() {
      this.formData.name = ''
      this.formData.status = ''
    },
    // 添加分类
    add() {
      this.dialogVisible = true
      this.title = '增加分类'
      this.diaFormData.fenLeiVal = ''
      this.diaFormData.upVal = ''
      this.diaFormData.px = 0
      this.diaFormData.status = true
      if (this.$refs.diaFormData) {
        this.$refs.diaFormData.resetFields()
      }
    },
    edit() {
      this.dialogVisible = true
      this.title = '编辑分类'
      if (this.$refs.diaFormData) {
        this.$refs.diaFormData.resetFields()
      }
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
    onHandleDiaSubmit(formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          console.log(333)
        } else {
          console.log('error submit!!')
          return false
        }
      })
    }
  }
}
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>
.content-photo{
  padding: 20px;
  .button{
    display: block;
    margin-left: 120px;
  }
}
</style>
