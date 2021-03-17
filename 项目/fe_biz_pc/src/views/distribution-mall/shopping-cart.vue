<template>
  <div class="shopping-cart">
    <el-card v-if="tableData&&tableData.length>0">
      <div class="flex spa-bet mb10">
        <div class="top-title">全部商品</div>
        <div>
          <el-button @click="toggleSelection()">删除选中的商品</el-button>
        </div>
      </div>
      <el-table
        ref="multipleTable"
        class="mb50"
        :data="tableData"
        tooltip-effect="dark"
        style="width: 100%"
        @selection-change="handleSelectionChange"
      >
        <el-table-column
          type="selection"
          width="55"
        />
        <el-table-column
          label="商品"
          show-overflow-tooltip
        >
          <template slot-scope="scope">
            <div class="flex">
              <img class="u1243_img" :src="scope.row.goodsCover" alt="">
              <div>
                <div class="goods-title col333 font16">{{ scope.row.goodsName }}</div>
                <div class="col878E93 font14">{{ scope.row.goodsSkuDesc }}</div>
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          prop="salePrice"
          label="单价"
          width="180"
          align="center"
        >
          <template slot-scope="scope">
            <div class="colF29126 font16">¥{{ scope.row.salePrice }}</div>
          </template>
        </el-table-column>
        <el-table-column
          prop="address"
          label="数量"
          width="200"
          align="center"
        >
          <template slot-scope="scope">
            <el-input-number v-model="scope.row.goodsNum" :min="1" :max="10" @change="handleChange" />
          </template>
        </el-table-column>
        <el-table-column
          prop="address"
          label="小计"
          width="180"
          align="center"
        >
          <template slot-scope="scope">
            <div class="colF29126 font16">¥{{ scope.row.goodsNum*scope.row.salePrice }}</div>
          </template>
        </el-table-column>
        <el-table-column
          prop="address"
          label="操作"
          width="180"
          align="center"
        >
          <template slot-scope="scope">
            <div v-if="scope.row.ifEnter" class="colD2D2D2 font13">已加入优品库</div>
            <div v-else class="col3B7CFF font13 point" @click="selectGoods(scope.row)">加入优品库</div>
            <div class="colE94747 font13 point" @click="deleteGoods([scope.row.goodsSkuId])">删除</div>
          </template>
        </el-table-column>
      </el-table>
      <!-- <el-pagination
        v-if="tableData&&tableData.length>0"
        class="text-center mt30 mb50"
        :current-page="currentPage"
        :page-sizes="[10, 20, 50, 100]"
        :page-size="pageSize"
        layout="total, sizes, prev, pager, next, jumper"
        :total="total"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      /> -->
      <div class="footer">
        <div class="flex ownmsg">
          <div class="font13 col999">已选中 {{ tableData.length }} 件商品 总价：</div>
          <div class="font18 colF28127 bold">¥{{ totalPrice }}</div>
          <div class="own-btn colfff ml20 point" @click="gocashregister">去结算</div>
        </div>
      </div>
    </el-card>
    <div v-else class="nodata-div">
      <img class="nodata-img" src="../../assets/nodata_images/u1341.svg" alt="">
      <div class="font13 goshop-tips">购物车空空如也，去商城逛逛吧</div>
      <el-button type="primary" @click="goMall">前往商城</el-button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ShoppingCart',
  data() {
    return {
      currentPage: 1,
      pageSize: 10,
      total: 0,
      tableData: [],
      ids: [],
      activeList: []
    }
  },
  computed: {
    totalPrice() {
      const pricelist = []
      this.tableData.forEach(item => {
        pricelist.push(item.goodsNum * item.salePrice)
      })
      return Math.floor(this.sum(pricelist) * 100) / 100
    }
  },
  created() {
    this.shoppingCartList()
  },
  methods: {
    //  分销商城-商品入库
    selectGoods(item) {
      const data = {
        id: item.goodsSkuId,
        goodsId: item.goodsId

      }
      this.$apis.RETAIL.selectGoods(data).then(res => {
        if (res.code === 0) {
          this.shoppingCartList()
          this.$message.success(res.message)
        } else {
          this.$message.error(res.message)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    // 购物车管理-删除商品
    deleteGoods(ids) {
      this.$confirm('你确定要删除选中的商品吗？', '删除提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        const data = {
          ids
        }
        this.$apis.RETAIL.deleteGoods(data).then(res => {
          if (res.code === 0) {
            this.shoppingCartList()
            this.$message.success(res.message)
          } else {
            this.$message.error(res.message)
          }
        })
      }).catch(() => {})
    },
    sum(arr) {
      return arr.reduce((prev, curr, idx, arr) => {
        return prev + curr
      })
    },
    //  购物车管理-列表
    shoppingCartList() {
      const data = {
        // pageNo: this.currentPage,
        // pageSize: this.pageSize
      }
      this.$apis.RETAIL.shoppingCartList(data).then(res => {
        if (res.code === 0) {
          this.tableData = res.data || []
        } else {
          this.$message.error(res.message)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    handleSizeChange(val) {
      this.currentPage = 1
      this.pageSize = val
      this.shoppingCartList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.shoppingCartList()
    },
    gocashregister() {
      const activeList = this.activeList
      if (activeList.length !== 0) {
        localStorage.setItem('cartList', JSON.stringify(activeList))
        this.$router.push('/distribution-mall/submit-order')
      } else {
        this.$message.warning('请先勾选需要购买的商品!')
      }
    },
    toggleSelection() {
      if (this.ids.length !== 0) {
        this.deleteGoods(this.ids)
      } else {
        this.$message.warning('请先勾选需要删除的商品!')
      }
    },
    goMall() {
      this.$router.push('/distribution-mall/selection-mall')
    },
    handleSelectionChange(val) {
      this.activeList = val
      const idarray = []
      val.forEach(item => {
        idarray.push(item.goodsSkuId)
      })
      this.ids = idarray
    },
    handleChange(value) {
      console.log(value)
    }
  }
}
</script>
<style lang="scss" scoped>
.shopping-cart{
  .top-title{
    line-height: 40px;
  }
  .footer{
    position: fixed;
    bottom: 0;
    right: 20px;
    background-color: #fff;
    width: 100%;
    height: 60px;
    line-height: 60px;
    box-shadow: 0px -2px 9px rgba(0, 0, 0, 0.0745098039215686);
    z-index: 1;
  }
  .ownmsg{
    position: absolute;
    right: 0;
  }
  .own-btn{
    width: 111px;
    text-align: center;
    background-color: #F28127;
    border: 1px solid;
  }
}

</style>
