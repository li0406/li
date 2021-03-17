<template>
  <div class="goods-manage warp">
    <tableSearch title="商品管理">
      <el-form slot="from" ref="ruleForm" :inline="true" :model="fromData">
        <el-form-item label="商品名称" prop="goodsName">
          <el-input v-model="fromData.goodsName" clearable placeholder="商品名称" />
        </el-form-item>
        <el-form-item label="商品编号" prop="goodsNo">
          <el-input v-model="fromData.goodsNo" clearable placeholder="商品编号" />
        </el-form-item>
        <el-form-item label="商品状态" prop="goodsStatus">
          <el-select v-model="fromData.goodsStatus" clearable placeholder="商品状态">
            <el-option label="已上架" value="1" />
            <el-option label="已下架" value="2" />
            <el-option label="无效" value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="商品分类" prop="goodsTypeId">
          <el-select v-model="fromData.goodsTypeId" clearable placeholder="商品分类">
            <el-option v-for="item in goodsTypeList" :key="item.id" :label="item.typeName" :value="item.id" />
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
        <el-form-item>
          <el-button type="primary" @click="onSearch">搜索</el-button>
          <el-button @click="onReset">重置</el-button>
          <el-button type="success" @click="addGoods">添加商品</el-button>
        </el-form-item>
      </el-form>
      <el-table
        slot="table"
        :data="tableData"
        :span-method="arraySpanMethod"
        border
        style="width: 100%"
      >
        <el-table-column prop="goodsNo" label="商品编号" align="center" />
        <el-table-column prop="goodsName" label="商品名称" align="center" />
        <el-table-column label="商品封面" align="center">
          <template slot-scope="scope">
            <el-image
              style="width: 100px; height: 100px"
              :src="scope.row.goodsCover"
              :preview-src-list="[scope.row.goodsCover]"
              fit="fit"
            />
          </template>
        </el-table-column>
        <el-table-column prop="goodsSkuName" label="购买属性" align="center" />
        <el-table-column prop="goodsType.typeName" label="商品分类" align="center" />
        <el-table-column label="商品状态" align="center">
          <template slot-scope="scope">
            <span v-if="scope.row.goodsStatus === '0'" class="red">无效</span>
            <span v-else-if="scope.row.goodsStatus === '1'" class="green">已上架</span>
            <span v-else-if="scope.row.goodsStatus === '2'" class="red">已下架</span>
          </template>
        </el-table-column>
        <el-table-column prop="createDate" label="添加时间" align="center" />
        <el-table-column prop="purchasePrice" label="齐装采购价" align="center" />
        <el-table-column prop="supplyPrice" label="齐装供销价" align="center" />
        <el-table-column prop="normalPrice" label="市场价" align="center" />
        <el-table-column label="推荐" align="center">
          <template slot-scope="scope">
            <span>{{ scope.row.recommandStatus === '1' ? '已推荐' : '未推荐' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" align="center">
          <template slot-scope="scope">
            <el-button type="text" @click="edit(scope.row.id)">编辑</el-button>
            <el-button v-if="scope.row.goodsStatus === '1'" type="text" @click="state(scope.row.id,2)">下架</el-button>
            <el-button v-else type="text" @click="state(scope.row.id,1)">上架</el-button>
            <el-button v-if="scope.row.goodsStatus !== '0'" type="text" @click="deleteItem((scope.row.id))">删除</el-button>
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
import { getGoodsList, getGoodsUpdateState, getGoodsDelete } from '@/api/goods'
export default {
  name: 'GoodsManage',
  data() {
    return {
      currentPage: 1,
      pageSize: 20,
      total: 0,
      fromData: {
        goodsName: '',
        goodsNo: '',
        goodsStatus: '',
        goodsTypeId: ''
      },
      createTime: '',
      endTime: '',
      goodsTypeList: [],
      tableData: []
    }
  },
  computed: {},
  watch: {
    'fromData.date': {
      handler(val) {
        this.createTime = !val ? '' : val[0] + ' 00:00:00'
        this.endTime = !val ? '' : val[1] + ' 23:59:59'
      }
    }
  },
  created() {
    this.getList()
    this.getGoodsTypes()
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
    getList() {
      const that = this
      const obj = {
        pageNo: that.currentPage,
        pageSize: that.pageSize,
        createTime: that.createTime,
        endTime: that.endTime,
        goodsName: that.fromData.goodsName,
        goodsNo: that.fromData.goodsNo,
        goodsStatus: that.fromData.goodsStatus,
        goodsTypeId: that.fromData.goodsTypeId
      }
      getGoodsList(obj).then(res => {
        if (res.code === 0) {
          this.tableData = res.data || []
          this.total = res.page.total
          this.pageSize = res.page.pageSize
          this.currentPage = res.page.pageNo
          this.convertTableData()
        } else {
          this.$message.error(res.message)
        }
      })
    },
    convertTableData() {
      const data = this.tableData
      const arr = []
      const rowspan = []
      data.forEach(item => {
        if (item.goodsDetailList) {
          for (let i = 0; i < item.goodsDetailList.length; i++) {
            const newData = item.goodsDetailList[i]
            if (newData.id) {
              newData.itemId = newData.id
              delete newData.id
            }
            const rdata = {
              ...item,
              ...newData
            }
            rdata.combineNum = item.goodsDetailList.length
            arr.push(rdata)
            if (i === 0) {
              rowspan.push(item.goodsDetailList.length)
            } else {
              rowspan.push(0)
            }
          }
        } else {
          arr.push(item)
          rowspan.push(0)
        }
      })
      this.tableData = arr
      this.rowspan = rowspan
    },
    arraySpanMethod({ row, column, rowIndex, columnIndex }) {
      if ([0, 1, 2, 4, 5, 6, 10, 11].includes(columnIndex)) {
        const _row = this.rowspan[rowIndex]
        const _col = _row > 0 ? 1 : 0 // 如果这一行隐藏了，这列也隐藏
        return {
          rowspan: _row,
          colspan: _col
        }
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
    edit(id) {
      this.$router.push('/wares/goods-addedit?id=' + id)
    },
    addGoods() {
      this.$router.push('/wares/goods-addedit')
    },
    // 启用禁用
    state(id, val) {
      this.$confirm(`确认${val === 1 ? '上' : '下'}架后，该商品将${val === 1 ? '' : '不'}在前台展示。`, `确认${val === 1 ? '上架' : '下架'}该商品？`, {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        getGoodsUpdateState({ ids: [{ id: id }], goodsStatus: val }).then(res => {
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
      this.$confirm('确认删除后，该商品将在后台保留，状态为无效，前台购买快照保留。', '确认删除该商品？', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        getGoodsDelete({ ids: [id] }).then(res => {
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
    async getGoodsTypes() {
      const res = await this.$apis.COMMODITY.getGoodsTypes()
      if (res.code === 0) {
        this.goodsTypeList = res.data
      }
    }
  }
}
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.goods-manage{
  height: 100%;
}
</style>
