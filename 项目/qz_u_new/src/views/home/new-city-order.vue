// 最新城市签单动态

<template>
  <div class="new-city">
    <marquee-text :duration="40">
      <div v-for="data in tableData" :key="data.companyid" class="item">
        <div class="img-border">
          <img :src="data.logo" alt="logo" class="logo" v-if="data.logo" />
          <img src="../../assets/default-logo.jpg" class="logo" v-else />
        </div>
        <p class="title">{{data.jc}}</p>
      </div>
    </marquee-text>
  </div>
</template>

<script>
import ApiHome from '@/api/home';
import MarqueeText from 'vue-marquee-text-component';

export default {
  name: 'NewCity',
  components: {
    MarqueeText,
  },
  async created() {
    const res = await ApiHome.qianDan();
    this.tableData = res.data.data;
  },
  data() {
    return {
      tableData: [],
    };
  },
};
</script>

<style lang="less" scoped>
.new-city {
  width: 100%;
  overflow: hidden;
  white-space: nowrap;

  .item {
    display: inline-block;
    margin: 0 10px;
    .logo {
      width: 160px;
      height: 80px;
    }
    .title {
      width: 206px;
      height: 40px;
      overflow: hidden;
      color: #999;
      line-height: 40px;
      white-space: nowrap;
      text-align: center;
      text-overflow: ellipsis;
    }
    .img-border {
      box-sizing: border-box;
      width: 210px;
      height: 110px;
      line-height: 110px;
      text-align: center;
      border: 1px solid rgba(227, 227, 227, 0.6);
      .logo {
        margin-top: 15px;
      }
    }
  }
}
</style>
