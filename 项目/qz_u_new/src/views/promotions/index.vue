<template>
  <div class="decoration-case-tab">
    <el-tabs v-model="activeName" class="tabs" @tab-click="changeActive">
      <el-tab-pane label="优惠活动" name="promotions">
        <!--优惠活动列表-->
        <event-list></event-list>
      </el-tab-pane>

      <el-tab-pane name="effect">
        <span slot="label">
          专用礼券
          <el-tooltip content="裝修公司自行设置！" placement="bottom" effect="light">
            <i class="el-icon-warning" style="color: #333;"></i>
          </el-tooltip>
        </span>
        <!--礼券列表-->
        <coupon-list></coupon-list>
      </el-tab-pane>

      <el-tab-pane name="global">
        <span slot="label">
          通用礼券
          <el-tooltip content="齐装网统一设置，需要请点击领取" placement="bottom" effect="light">
            <i class="el-icon-warning" style="color: #333;"></i>
          </el-tooltip>
        </span>
        <!--通用礼券-->
        <global-list></global-list>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script>
import eventList from './components/eventList.vue';
import couponList from './components/couponList.vue';
// eslint-disable-next-line import/no-unresolved
import globalList from './components/globalList.vue';

export default {
  data() {
    return {
      activeName: localStorage.activeName || 'promotions',
    };
  },
  created() {},
  components: {
    eventList,
    couponList,
    globalList,
  },
  methods: {
    changeActive(val) {
      localStorage.setItem('activeName', val.name);
    },
  },
  filters: {},
};
</script>

<style lang="less">
.decoration-case-tab {
  height: 100%;
  background: #fff;

  .el-tabs__content {
    padding: 0 30px;
    background: #fff;
  }

  .el-tabs__item {
    font-size: 16px;
  }

  .el-tabs__item.is-active {
    color: red;
    font-weight: bold;
  }

  .el-tabs__active-bar {
    background-color: red;
  }

  .el-tabs__item:hover {
    color: red;
  }

  .tabs {
    height: 60px;
    line-height: 60px;
  }

  .el-tabs__nav-wrap {
    padding: 0 30px;
  }
}

.el-table thead {
  line-height: 21px;
}

.effect-pic {
  .app-search {
    .more-button {
      float: right;
    }
  }

  .pagination {
    margin-top: 10px;
    margin-bottom: 80px;
  }

  .el-table__header tr,
  .el-table__header th {
    height: 50px;
    padding: 0;
  }
}
</style>


