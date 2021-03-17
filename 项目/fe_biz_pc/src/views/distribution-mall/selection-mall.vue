<template>
  <div>
    <el-card>
      <el-row type="flex" class="category">
        <el-col :span="2">
          <div class="title">商品类目:</div>
        </el-col>
        <el-col :span="22" :pull="1">
          <div class="flex">
            <div :class="['flex','sellist','rowwrap','ml10',{'hid':hid},{'mb20':hid}]">
              <div v-for="(item,index) of commoditycategorylist" :key="item.id" :class="['point','sels','font14', {'on':categorylistindex==index}]" @click="selcommodity(index,item)">{{ item.typeName }}</div>
            </div>
            <div v-if="commoditycategorylist.length>11">
              <div v-if="hid" class="switch-btn point" @click="cutout(0)">展开</div>
              <div v-else class="switch-btn point" @click="cutout(1)">收起</div>
            </div>
          </div>
        </el-col>
      </el-row>
      <el-row type="flex" class="search">
        <el-col :span="12">
          <el-row type="flex" align="middle">
            <el-col :span="6">
              搜索商品
            </el-col>
            <el-col :span="12" :pull="3">
              <el-input v-model="goodsName" placeholder="搜索商品名称" />
            </el-col>
            <el-col :span="6" :pull="2">
              <el-button type="primary" @click="getGoodsList">搜索</el-button>
            </el-col>
          </el-row>
        </el-col>
        <el-col :span="12" :push="10">
          <el-badge :value="goodsnumber">
            <el-button @click="goshoping"><i class="el-icon-shopping-cart-2 font16" /> 购物车</el-button>
          </el-badge>
        </el-col>
      </el-row>
    </el-card>
    <div v-loading="listLoading">
      <el-row type="flex" class="modgood">
        <el-col :span="24">
          <div class="flex rowwrap">
            <el-card v-for="item of goodsList" :key="item.id" class="elcard">
              <img class="goodsimg" :src="item.goodsCover" alt="">
              <div class="mt15">{{ item.goodsName }}</div>
              <div class="flex spa-bet mt15 submsg">
                <div>{{ item.typeName }}</div>
                <div>市场价:￥{{ item.normalPrice }}</div>
              </div>
              <div class="mt15 submsg">供货商:<span class="price font16">￥{{ item.supplyPrice }}</span></div>
              <div class="flex spa-bet ali-ite mt15">
                <div>
                  <el-tooltip class="point mr20" effect="dark" content="加入购物车" placement="top">
                    <i class="listicon el-icon-shopping-cart-full" @click="addGoods(item)" />
                  </el-tooltip>
                  <el-tooltip class="point" effect="dark" content="加入选品库" placement="top">
                    <i class="listicon el-icon-circle-plus-outline" @click="selectGoods(item)" />
                  </el-tooltip>
                </div>
                <div>
                  <el-button type="warning" round @click="gosubmitorder(item.id, item.goodsId)">立即购买</el-button>
                </div>
              </div>
            </el-card>
          </div>
        </el-col>
      </el-row>
      <div v-if="goodsList&&goodsList.length>0" class="pagination">
        <el-pagination
          :current-page="currentPage"
          :page-sizes="[10, 20, 50, 100]"
          :page-size="pageSize"
          layout="total, sizes, prev, pager, next, jumper"
          :total="total"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
      <div v-else class="nodata col999">
        暂无数据
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'SelectionMall',
  data() {
    return {
      listLoading: false,
      currentPage: 1,
      pageSize: 10,
      total: 0,
      hid: 1,
      categorylistindex: 0,
      commoditycategorylist: [],
      goodsTypeId: 0,
      goodsName: '',
      goodsList: [],
      goodsnumber: 0
    }
  },
  created() {
    this.listByGoods()
    this.count()
  },
  methods: {
    count() {
      const params = {}
      this.$apis.RETAIL.count(params).then(res => {
        if (res.code === 0) {
          this.goodsnumber = res.data.nums || 0
        } else {
          this.$message.error(res.message)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    //  购物车管理-添加商品
    addGoods(item) {
      /* const data = {
        goodsId: item.goodsId,
        goodsSkuId: item.id,
        goodsName: item.goodsName,
        goodsSkuName: item.goodsSkuName,
        goodsSkuDesc: item.goodsSkuDesc,
        goodsCover: item.goodsCover
      } */
      item.goodsSkuId = item.id
      this.$apis.RETAIL.addGoods(item).then(res => {
        if (res.code === 0) {
          this.count()
          this.$message.success(res.message)
        } else {
          this.$message.error(res.message)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    //  分销商城-商品入库
    selectGoods(item) {
      const data = {
        id: item.id,
        goodsId: item.goodsId

      }
      this.$apis.RETAIL.selectGoods(data).then(res => {
        if (res.code === 0) {
          this.$message.success(res.message)
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
      this.getGoodsList()
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.getGoodsList()
    },
    //  分销商城-分类商品-获取分类
    listByGoods() {
      this.listLoading = true
      const params = {}
      this.$apis.RETAIL.listByGoods(params).then(res => {
        this.listLoading = false
        if (res.code === 0) {
          const list = res.data || []
          this.commoditycategorylist = [{ id: 0, typeName: '全部' }, ...list]
          this.getGoodsList()
        } else {
          console.log(res)
        }
      }).catch(err => {
        this.listLoading = false
        console.log(err)
      })
    },
    getGoodsList() {
      const data = {
        goodsName: this.goodsName,
        pageNo: this.currentPage,
        pageSize: this.pageSize
      }
      if (this.goodsTypeId !== 0) {
        data.goodsTypeId = this.goodsTypeId
      }
      this.$apis.RETAIL.goodsList(data).then(res => {
        if (res.code === 0) {
          this.goodsList = res.data || []
          this.currentPage = res.page.pageNo
          this.pageSize = res.page.pageSize
          this.total = res.page.total
        } else {
          console.log(res)
        }
      }).catch(err => {
        console.log(err)
      })
    },
    gosubmitorder(id, goodsId) {
      this.$router.push({
        path: `submit-order?id=${id}&goodsId=${goodsId}`
      })
    },
    cutout(hid) {
      this.hid = hid
    },
    selcommodity(index, item) {
      this.categorylistindex = index
      this.goodsTypeId = item.id
      console.log(item)
      this.getGoodsList()
    },
    goshoping() {
      this.$router.push(
        {
          path: '/distribution-mall/shopping-cart'
        }
      )
    }
  }
}
</script>

<style lang="scss" scoped>
$colred:#E94747;
.title{
  margin: 5px;
}
.sellist{
  width: 1300px;
}
.rowwrap{
  flex-flow: row wrap;
}
.hid{
  height: 30px;
  overflow: hidden;
}
.sels{
  width: 100px;
  height: 30px;
  line-height: 30px;
  margin: 0 0 10px 10px;
  text-align: center;
  border-radius: 4px;
}
.switch-btn{
  width: 100px;
  height: 30px;
  line-height: 30px;
  margin: 0 0 10px 80px;
  text-align: center;
  color: $colred;
}
.on{
  background-color: $colred;
  color: #fff;
}
.category{
  border-bottom: 1px dashed #ccc ;
}
.search{
  margin: 20px 0 0 0;
}
.modgood{
  margin: 20px 0 0 0;
}
.elcard{
  width: 310px;
  margin: 0 20px 20px 0;
}
.goodsimg{
  width: 100%;
  height: 260px;
}
.listicon{
  font-size: 20px;
}
.submsg{
  font-size: 12px;
  color: #878E93;
}
.price{
  color: #F28127;
}
</style>
