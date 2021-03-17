<template>
  <div class="goods-manage warp">
    <tableSearch title="商品分类管理">
      <el-form slot="from" ref="ruleForm" :inline="true" :model="fromData">
        <!-- <el-form-item label="分类ID" prop="type_id">
          <el-input v-model="fromData.type_id" placeholder="分类ID" />
        </el-form-item> -->
        <el-form-item label="分类名称" prop="typeName">
          <el-input v-model="fromData.typeName" clearable placeholder="分类名称" />
        </el-form-item>
        <el-form-item label="启用状态" prop="enableFlag">
          <el-select v-model="fromData.enableFlag" clearable placeholder="启用状态">
            <el-option label="全部" value="" />
            <el-option label="启用中" value="1" />
            <el-option label="未启用" value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="添加时间" prop="date">
          <el-date-picker
            v-model="fromData.date"
            clearable
            type="daterange"
            value-format="yyyy-MM-dd"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          />
        </el-form-item>
        <el-form-item label="等级" prop="typeLevel">
          <el-select v-model="fromData.typeLevel" clearable placeholder="等级">
            <el-option label="一级" value="1" />
            <el-option label="二级" value="2" />
            <el-option label="三级" value="3" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="onSearch">搜索</el-button>
          <el-button @click="onReset">重置</el-button>
          <el-button type="success" @click="add">添加分类</el-button>
        </el-form-item>
      </el-form>
      <div v-loading="loading" class="teble">
        <el-table slot="table" :data="tableData" border style="width: 100%">
          <el-table-column label="分类ID" align="center">
            <template slot-scope="scope">
              <span>{{ scope.row.id || '——' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="渠道代号" align="center">
            <template slot-scope="scope">
              <span>{{ scope.row.channelNo || '——' }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="typeName" label="分类名称" align="center" />
          <el-table-column prop="typeLevel" label="等级" align="center" />
          <el-table-column label="上级分类" align="center">
            <template slot-scope="scope">
              <span>{{ scope.row.parentTypeName || '——' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="启用状态" align="center">
            <template slot-scope="scope">
              <span :class="scope.row.enableFlag === '1' ? 'green' : 'red'">{{ scope.row.enableFlag === '1' ? '已启用' : '未启用' }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="createDate" label="添加时间" align="center" />
          <el-table-column prop="sort" label="排序值" align="center" />
          <el-table-column label="操作" align="center">
            <template slot-scope="scope">
              <el-button type="text" @click="edit(scope.row.id)">编辑</el-button>
              <el-button v-if="scope.row.enableFlag === '2'" type="text" @click="state(scope.row.id,1)">启用</el-button>
              <el-button v-else type="text" @click="state(scope.row.id,2)">停用</el-button>
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
      </div>

    </tableSearch>
  </div>
</template>

<script>
import { getTypeList, goodsTypeUpdateState, goodsTypeDelete } from '@/api/goods'
export default {
  name: 'GoodsManage',
  data() {
    return {
      loading: false,
      currentPage: 1,
      pageSize: 20,
      total: 0,
      fromData: {
        type_id: '',
        typeName: '',
        enableFlag: '1',
        typeLevel: '',
        dete: ''
      },
      startTime: '',
      endTime: '',
      tableData: []
    }
  },
  computed: {},
  watch: {
    'fromData.date': {
      handler(val) {
        this.startTime = !val ? '' : val[0] + ' 00:00:00'
        this.endTime = !val ? '' : val[1] + ' 23:59:59'
      }
    }
  },
  created() {
    this.getList()
  },
  mounted() {},
  methods: {
    onSearch() {
      this.currentPage = 1
      this.getList()
    },
    onReset() {
      this.$refs['ruleForm'].resetFields()
    },
    async getList() {
      const that = this
      that.loading = true
      const obj = {
        pageNo: that.currentPage,
        pageSize: that.pageSize,
        startTime: that.startTime,
        endTime: that.endTime,
        type_id: that.fromData.type_id,
        typeName: that.fromData.typeName,
        enableFlag: that.fromData.enableFlag,
        typeLevel: that.fromData.typeLevel
      }
      const res = await getTypeList(obj)
      if (res.code === 0) {
        this.tableData = res.data
        this.total = res.page.total
        this.pageSize = res.page.pageSize
        this.currentPage = res.page.pageNo
        that.loading = false
      } else {
        that.loading = false
      }
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
    add() {
      this.$router.push('/wares/classify-addedit')
    },
    edit(id) {
      this.$router.push('/wares/classify-addedit?id=' + id)
    },
    // 启用禁用
    state(id, val) {
      this.$confirm(`确认${val === 1 ? '上' : '下'}架后，该分类将${val === 1 ? '' : '不'}在前台展示。`, `确认${val === 1 ? '启用' : '停用'}该分类？`, {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        goodsTypeUpdateState({ id: id, enableFlag: val }).then(res => {
          console.log(res)
          if (res.code === 0) {
            this.$message.success(res.message)
            this.getList()
          } else {
            this.$message.error(res.message)
          }
        })
      }).catch(() => {})
    },
    // 删除
    deleteItem(id) {
      this.$confirm('确认删除后，该分类将在后台删除并不再显示。', '确认删除该分类？', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        goodsTypeDelete({ ids: [id] }).then(res => {
          console.log(res)
          if (res.code === 0) {
            this.$message.success(res.message)
            this.getList()
          } else {
            this.$message.error(res.message)
          }
        })
      }).catch(() => {})
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.goods-manage{
  height: 100%;
}
</style>
