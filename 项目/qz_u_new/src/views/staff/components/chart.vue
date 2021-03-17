<template>
  <div class="chart-contains">
    <el-row>
      <!-- 注释 -->
      <!-- <TreeChart :json="treeData" /> -->
      <span class="item-name">{{ companyName}}</span>
      <span class="line"></span>
      <span class="item-name nick">{{nickName}}</span>
    </el-row>
    <el-row class="backBtn">
      <el-button type="danger" plain @click="goList">返回列表</el-button>
    </el-row>
  </div>
</template>

<script>
// import TreeChart from 'vue-tree-chart';
import staff from '@/api/staff-con';

export default {
  // components: {
  //   TreeChart,
  // },
  name: 'Chart',
  data() {
    return {
      // 后期工作
      treeData: {
        name: '苏州青宜景装饰设计苏州青宜景装饰设计',
        children: [
          {
            name: '华沙',
            children: [{ name: '华沙1' }],
          },
          {
            name: '华沙2',
            children: [
              {
                name: '华沙',
                children: [{ name: '华沙' }],
              },
              {
                name: '华沙',
              },
              {
                name: '华沙',
              },
            ],
          },
          {
            name: '华沙',
          },
        ],
      },
      companyName: '',
      nickName: '',
    };
  },
  created() {
    this.companyName = JSON.parse(localStorage.getItem('companyName'));
    const account = this.$route.query.account ? this.$route.query.account : '';
    this.account = account;
    this.getData(account);
  },
  methods: {
    getData(e) {
      staff
        .accountinfo({
          account: e,
        })
        .then((res) => {
          if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
            const datas = res.data.data;
            this.nickName = datas.nick_name;
          }
        });
    },
    // 跳转
    goList() {
      this.$router.push({
        path: '/workers-list',
      });
    },
  },
};
</script>

<style lang="less">
.chart-contains {
  background: #fff;
  .node {
    width: 14em !important;
  }
  .avat {
    display: none !important;
    height: 0 !important;
    img {
      display: none;
    }
  }
  .name {
    box-sizing: border-box;
    height: 40px !important;
    margin-top: 3px;
    padding: 0 20px;
    line-height: 40px !important;
    border: 1px solid #e6e6e6;
  }
  .node.hasMate::after {
    display: none;
  }
  .node .person {
    width: auto !important;
  }
  .item-name {
    display: inline-block;
    height: 40px;
    margin-top: 14px;
    padding: 0 20px;
    overflow: hidden;
    color: rgba(48, 49, 51, 1);
    font-weight: 400;
    font-size: 14px;
    line-height: 40px;
    background: rgba(255, 255, 255, 1);
    border: 1px solid rgba(220, 223, 230, 1);
    border-radius: 2px;
  }
  .nick {
    color: #fff;
    background: #e94747;
    border: 1px solid #e94747;
  }
  .line {
    display: inline-block;
    width: 30px;
    height: 1px;
    margin-bottom: 20px;
    background: #dcdfe6;
  }
}
</style>
