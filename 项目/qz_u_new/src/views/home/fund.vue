// 企业信息
<template>
  <div class="fund">
    <el-row :gutter="24" class="tips">
      <el-col :span="18" class="message">
        <div class="head">
          <div class="left">企业信息</div>
          <div class="right">
            资料完善度{{integrity}}%
            <span @click="goLink('base-info')">
              去完善
              <i class="el-icon-arrow-right"></i>
            </span>
          </div>
        </div>
        <div class="content">
          <div class="left">
              <img :src="user.logo" alt="logo" class="logo" v-if="user.logo" />
            <img src="../../assets/default-logo.jpg" class="logo" v-else />
            <div>
              <div class="tit">{{user.qc}}</div>
              <el-popover placement="right" width="290" trigger="hover">
                <div class="pop-fund">
                  <div class="tit">
                    如何提升排名?
                    <br />
                    <span>可以通过以下几种方式提升排名</span>
                  </div>
                  <div class="item">
                    <span>1:完善店铺信息</span>
                    <span class="go" @click="goLink('base-info')">立刻前往</span>
                  </div>
                  <div class="item">
                    <span>2:上传至少8套设计案例</span>
                    <span class="go" @click="goLink('decoration-case/case')">立刻前往</span>
                  </div>
                  <div class="item">
                    <span>3:添加至少两位设计师</span>
                    <span class="go" @click="goLink('set-first')">立刻前往</span>
                  </div>
                  <div class="item item-last">
                    <span>4:及时更新签单/量房状态</span>
                    <span class="go" @click="goLink('order-all')">立刻前往</span>
                  </div>
                </div>
                <span slot="reference" class="howif">如何提升排名?</span>
              </el-popover>
            </div>
          </div>
          <div class="right" v-loading="rightLoading">
            <div class="rank iconcon">
              <span class="el-icon-refresh refresh" @click="refresh"></span>
              <br />刷新
            </div>
            <div class="rank">
              <span>综合排名</span>
              <br />
              <span class="num">{{sort.strength}}</span>
            </div>
            <div class="rank">
              <span>签单排名</span>
              <br />
              <span class="num">{{sort.qiandan}}</span>
            </div>
            <div class="rank">
              <span>量房排名</span>
              <br />
              <span class="num">{{sort.liangfang}}</span>
            </div>
          </div>
        </div>
      </el-col>
      <el-col :span="6" style="padding: 0; ">
        <div class="head heads">抢单池动态</div>
        <div class="contents">
          <div class="goRop">
            当前有
            <span>{{rob.rob_fen_count + rob.rob_zeng_count}}</span> 个订单可抢，新单不等人，
            <br />赶快去抢吧~
          </div>
          <el-button type="danger" @click="goLink('order-pond')">立即抢单</el-button>
        </div>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import ApiHome from '@/api/home';

export default {
  async created() {
    this.loading = true;
    const res = await ApiHome.companyInfo();
    if (res.data.error_code === 0) {
      this.loading = false;
      this.user = res.data.data.user;
      this.sort = res.data.data.sort;
      this.rob = res.data.data.rob_info;
      this.integrity = res.data.data.integrity;
    } else {
      this.loading = false;
      this.$message({
        type: 'error',
        message: res.data.data.error_msg,
      });
    }
  },
  data() {
    return {
      loading: false,
      user: {},
      sort: {},
      rob: {},
      integrity: '',
      rightLoading: false,
    };
  },
  methods: {
    goLink(e) {
      this.$router.push(e);
    },
    async refresh() {
      this.rightLoading = true;
      const res = await ApiHome.companySort();
      if (res.data.error_code === 0) {
        this.rightLoading = false;
        this.sort = res.data.data;
      } else {
        this.rightLoading = false;
        this.$message({
          type: 'error',
          message: res.data.data.error_msg,
        });
      }
    },
  },
};
</script>

<style lang="less" scoped>
.fund {
  overflow: hidden;
  .tips {
    height: 100%;
    color: #333;
    font-size: 18px;
    .head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 30px 20px 30px 30px;
      .right {
        color: #777;
        font-size: 14px;
        span {
          margin-left: 10px;
          color: #ff6969;
          cursor: pointer;
        }
      }
    }
    .content {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 20px 30px 30px;
      .left {
        display: flex;
        align-items: center;
        font-size: 16px;
        img {
          display: block;
          width: 160px;
          height: 80px;
          margin-right: 20px;
          cursor: pointer;
        }
        span {
          color: #ff6969;
          font-size: 14px;
        }
      }
      .right {
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #777;
        font-size: 14px;
        text-align: right;

        .rank {
          margin-left: 50px;
          .num {
            color: #333;
            font-weight: 700;
            font-size: 16px;
            line-height: 40px;
          }
        }
      }
      .iconcon {
        text-align: center;
        cursor: pointer;
        .refresh {
          color: #5cb6ff;
          font-size: 26px;
        }
      }
      .tit {
        margin-bottom: 10px;
      }
      .howif {
        cursor: pointer;
      }
    }
  }
  .heads {
    padding-left: 20px !important;
  }
  .contents {
    padding: 0 20px;
    padding-right: 30px;
    .goRop {
      margin-bottom: 8px;
      color: #333;
      font-size: 14px;
      span {
        color: #ff6969;
        font-size: 18px;
      }
    }
    .el-button {
      width: 100%;
    }
  }
  .message {
    border-right: 10px solid #f2f2f2;
  }
}
.qz-card .content {
  overflow: auto;
}
</style>

<style lang="less">
.pop-fund {
  .tit {
    box-sizing: border-box;
    width: 100%;
    height: auto;
    padding: 10px;
    color: #fff;
    font-size: 16px;
    line-height: 28px;
    background: #e64d4d;
    span {
      font-size: 12px;
    }
  }
  .item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-sizing: border-box;
    height: 60px;
    padding-right: 10px;
    padding-left: 20px;
    color: #333;
    line-height: 60px;
    border-bottom: 1px solid #f0f0f0;
    .go {
      color: #ff6969;
      cursor: pointer;
    }
  }
  .item-last {
    border: none;
  }
}
</style>
