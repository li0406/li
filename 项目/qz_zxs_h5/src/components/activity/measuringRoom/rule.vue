<template>
  <div class="measuring-room-rule">
    <div class="title-img">
      <img src="@/assets/img/activity/measuringRoom/title.png" alt="">
    </div>
    <div class="main">
      <h4>活动时间</h4>
      <p>2020年5月20日0:00-2020年10月30日24：00</p>
      <h4>活动参与资格</h4>
      <p>本次参与用户需完成装企量房行为方可领奖兑换。</p>
      <h4>活动规则详情</h4>
      <p>(一)参与方式</p>
      <p>1、本活动页仅限活动期限内齐装网用户参与，可免费抽优酷视频VIP月卡、季卡、年卡，每位用户仅能参与一次。</p>
      <p>2、特别注意：领取资格仅在活动期间有效，活动结束后，资格会自动失效。</p>
      <p>3、奖品数量有限，送完即止。</p>
      <p>(二)发奖时间、方式</p>
      <p>奖品实时到账，获得的奖品可在齐装网APP：我的 > 卡券中心中查看。</p>
      <p>(三)参与资格、获奖资格的取消</p>
      <p>您若有下列任何一种行为或情况的，齐装网有权单方面取消您的参与资格、获奖资格等：</p>
      <p>(1)不符合参与资格的；</p>
      <p>(2)提供虚假信息的；</p>
      <p>(3)虚假交易或恶意破坏活动的；</p>
      <p>(4)以任何机器人软件、蜘蛛软件、爬虫软件、刷奖软件或其它任何自动方式、不正当手段等参与本活动的；</p>
      <p>(5)有任何违反诚实信用、公序良俗、公平、公正等原则行为的；</p>
      <p>(6)其他违反相关法规、本规则行为的。</p>
      <p>(四)免责条款</p>
      <p>用户知悉互联网存在诸多不确定性因素，因此理解并同意，如因不可抗力、网络、通讯线路故障、计算机大规模瘫痪及活动中存在大面积作弊行为等非齐装网原因致使本活动出现异常情况或难以继续开展的，齐装网有权采取包括但不限于通过各种方式消除异常情况或调整、暂停、取消本活动等措施，因此造成用户损失的，齐装网不承担任何责任。</p>
      <h4>其他说明</h4>
      <p>请用户理性参与活动，活动期间，如用户存在违反法律、法规或违反包括但不限于《齐装网服务协议》及活动合作平台规则，包括但不限于以作弊等非正当方式获取奖品、会员时长等行为，齐装网有权取消用户中奖资格，撤销违规交易，收回奖品或权益。如有疑问，请致电400-6969-336咨询相关事宜。</p>
    </div>
  </div>
</template>

<script>
export default {
name: 'rule',
mounted() {
    document.body.style.backgroundColor="#FD535D"
  }
}
</script>

<style scoped>
.measuring-room-rule{
  font-family: 'PingFang-SC-Bold','Microsoft YaHei';
  background: #FD535D;
  position: relative;
  padding:0.354rem 0 .128rem;
}
.measuring-room-rule .title-img{
  position: absolute;
  margin: 0 0.43rem;
  left: 0;
  top: .15rem;
}
.measuring-room-rule .title-img img{
  width: 100%;
  height: 100%;
}
.measuring-room-rule .main{
  border-radius: 0.068rem;
  border: 0.04rem solid #DB3625;
  background: #fff;
  margin: 0 0.128rem 0.13rem;
  padding: 0.3rem 0.128rem .7rem;
}
.measuring-room-rule .main h4{
  color: #FD910B;
  font-size: .14rem;
  font-weight: bold;
  line-height: .15rem;
  padding: .1rem 0;
}
.measuring-room-rule .main p{
  color: #333;
  font-size: .12rem;
  font-weight: bold;
  line-height: .154rem;
  padding: .06rem 0;
  text-align:justify;
}
</style>
