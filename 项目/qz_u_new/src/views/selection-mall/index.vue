<template>
  <div class="selection-mall">
    <el-card>
      <div class="clearfix">
        <div class="top-pic">
          <img class="selection-img" src="https://staticqn.qizuang.com/custom/20210129/FmacP9VtyiJDum86ug9LheExmBcg.png" alt="">
        </div>
        <div class="top-right-text">
          <div class="tips-title">分销商城</div>
          <div class="tips-sub">通过自购/兑换的形式，赠送准业主，促成业主与装修公司的签单转化</div>
          <div>
            <div v-if="settleInDetail.apply_status=='0'" class="apply-btn btn-m col3399FF point" @click="getApplySettleIn">申请入驻</div>
            <div v-else-if="settleInDetail.apply_status=='1'" class="apply-btn btn-m colD0D0D0 point">审核中</div>
            <div v-else-if="settleInDetail.apply_status=='2'" class="flex btn-m">
              <div class="apply-btn col3399FF point" @click="getApplySettleIn">再次申请</div>
              <div class="examine-tips">{{settleInDetail.apply_refuse_reason}}</div>
            </div>
            <div v-else-if="settleInDetail.apply_status=='3'" class="apply-btn btn-m col3399FF point" @click="goShoppingMall">进入商品中心</div>
            <div class="apply-btn btn-m colD0D0D0 point" v-if="settleInDetail.apply_status=='4'">停用</div>
          </div>
        </div>
      </div>
    </el-card>
    <el-card class="elcard">
      <span>商城介绍</span>
      <div class="shop-mall">
        <div class="shop-tips">
          <div class="shop-tips-title">分销商城</div>
          <div class="shop-tips-sub">通过自购/兑换的形式，赠送准业主，促成业主与装修公司的签单转化</div>
        </div>
        <img class="shop-img"
        src="https://staticqn.qizuang.com/custom/20210129/FviGPwU-G1E2BJiE7c5oeJK8PE2d.png" alt="">
      </div>
    </el-card>
    <div>
    </div>
  </div>

</template>

<script>
import distribution from '@/api/distribution'; // 接口引入

export default {
  name:'selectionMall',
  data(){
    return{
      settleInDetail:{
        apply_status: '',//  申请状态（0：未申请；1：待审核；2：审核不通过；3：通过；4：停用）
        apply_refuse_reason: '' // 申请失败原因
      }
    }
  },
  created(){
    this.getSettleInDetails()
  },
  methods:{
    goShoppingMall(){
      const token = localStorage.getItem('token')
      window.open(`http://cpmall.qizuang.com/jump?token=${token}`)
    },
    // 分销商城-入驻详情
    getSettleInDetails(){
      const params = {}
      distribution.applyinfo(params).then((res) => {
          if (parseInt(res.data.error_code, 10) === 0) {
            this.settleInDetail=res.data.data
          } else {
            this.$message({
              message: res.data.error_msg,
              type: 'error'
            });
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    // 分销商城-申请入驻（再次申请）
    getApplySettleIn(){
      const params = {}
      distribution.applyadd(params).then((res) => {
          if (parseInt(res.data.error_code, 10) === 0) {
            this.getSettleInDetails()
            this.$message({
              message: res.data.error_msg,
              type: 'success'
            });
          } else {
            this.$message({
              message: res.data.error_msg,
              type: 'error'
            });
          }
        })
        .catch((err) => {
          this.$message.error(err);
        });
    }
  }
}
</script>

<style lang="less" scoped>
.selection-mall{
  .clearfix::after{
    display: block;
    clear: both;
    height: 0;
    content: '';
  }
  .clearfix{
    zoom: 1;
  }
  .top-pic{
    float: left;
    width: 197px;
    height: 197px;
  }
  .top-right-text{
    padding-left: 220px;
  }
}
.selection-img{
  width: 197px;
  height: 197px;
}
.tips-title{
  font-size: 18px;
  line-height: 42px;
}
.tips-sub{
  font-size: 14px;
  line-height: 32px;
}
.apply-btn{
    width: 97px;
    height: 32px;
    padding: 0 15px;
    color: #FFF;
    font-size: 14px;
    line-height: 32px;
    text-align: center;
    border-radius: 2px;
}
.btn-m{
   margin: 82px 0 0 12px;
}
.col3399FF{
  background-color:#39F;
}
.colD0D0D0{
  background-color:#D0D0D0;
}
.point{
  cursor: pointer;
}
.flex{
  display: flex;
  height: 32px;
  line-height: 32px;
}
.examine-tips{
  margin: 0 0 0 20px;
  color: #e94747;
  font-size: 13px;
}
.elcard{
  margin: 20px 0 0 0;
}
.shop-mall{
  position: relative;
  width: 100%;
  height: 378px;
  margin: 20px 0 0 0;
  background-color: #FFC83A;
}
.shop-img{
  position:absolute;
  top:23px;
  right:23px;
  width: 514px;
  height: 332px;
}
.shop-tips{
  position: absolute;
  top:150px;
  left:100px;
}
.shop-tips-title{
  font-weight: bold;
  font-size: 31px;
}
.shop-tips-sub{
  margin: 20px 0 0 0;
  font-weight: 20;
  font-size: 15px;
}
</style>
