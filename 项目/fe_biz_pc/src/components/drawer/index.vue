<template>
  <div>
    <el-dialog
      title="商品详情"
      :visible.sync="drawer"
      width="500px"
      top="10vh"
    >
      <div class="drawer">
        <el-carousel height="400px">
          <el-carousel-item v-for="item in info.goodsPicVOList" :key="item.goodsPic">
            <!-- <h3 class="small">{{ item.goodsPic }}</h3> -->
            <img class="small" :src="item.goodsPic" alt="" width="100%" height="100%">
          </el-carousel-item>
        </el-carousel>
        <div class="col333 font18 mt10">{{ info.goodsName }}</div>
        <div class="mt10 font14 col878E93">{{ info.goodsSkuDesc }}</div>
        <div class="mt20 detailmsg">
          <div class="flex spa-bet ali-ite">
            <div class="flex">
              <div class="colF29126"><span class="font12">¥</span><span class="font24 ml5">{{ info.supplyPrice }}</span></div>
              <div class="ml10 bid font12">供货价</div>
            </div>
            <div class="font12 col878E93">市场价：¥{{ info.supplyPrice }}</div>
          </div>
          <div class="font13 col989898 mt20">快递：免运费</div>
        </div>
        <div class="flex ali-ite specs mt20">
          <div class="font13 col333">规格：</div>
          <div class="font12 specs-btn mr5">{{ info.goodsSkuName }}{{ info.goodsSkuDesc }}</div>
        </div>
        <div class="mt20">
          <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-tab-pane label="商品介绍" name="first" v-html="info.goodsLongpic" />
            <el-tab-pane label="规格参数" name="second">
              <div class="format">
                <span class="name">属性名称</span>
                <span class="num">属性值</span>
              </div>
              <div v-for="item in info.goodsParameterVOList" :key="item.goodsFormatName" class="format">
                <span class="name">{{ item.goodsFormatName }}</span>
                <span class="num">{{ item.goodsFormatValue }}</span>
              </div>
            </el-tab-pane>
          </el-tabs>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
export default {
  props: {
    drawer: {
      type: Boolean,
      default: false
    },
    info: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      activeName: 'first'
    }
  },
  methods: {
    handleClose() {
      this.$parent.drawer = false
    },
    handleClick(tab, event) {
      console.log(tab, event)
    }
  }
}
</script>

<style lang="scss" scoped>
.drawer{
  height: 600px;
  overflow-y: auto;

  .detailmsg{
    padding: 20px 10px;
    border-radius: 6px;
    background-color: #F7F9FA;
  }
  .bid{
    border: 1px solid #F28127;
    background-color: #FFF2E9;
    color: #F28127;
    width: 50px;
    height: 20px;
    line-height: 20px;
    text-align: center;
    border-radius: 12px;
    margin: auto 10px;
  }
  .specs{
    height: 30px;
  }
  .specs-btn{
    border: 1px solid #F28127;
    background-color: #FFF2E9;
    color: #F28127;
    width: 100px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    border-radius: 15px;
  }

  .format{
    margin: 0 40px;
    border-bottom: 1px solid #ccc;
    line-height: 40px;
    .name{
      display: inline-block;
      width: 40%;
      border-right: 1px solid #ccc;
      text-align: center;
      color: #333;
      font-size: 14px;
      font-weight: 600;
    }
    .num{
      display: inline-block;
      width: 60%;
      text-align: center;
      color: #999;
    }
  }

  .el-carousel__item h3 {
    color: #475669;
    font-size: 14px;
    opacity: 0.75;
    line-height: 150px;
    margin: 0;
  }

  .el-carousel__item:nth-child(2n) {
    background-color: #99a9bf;
  }

  .el-carousel__item:nth-child(2n+1) {
    background-color: #d3dce6;
  }
}
</style>
