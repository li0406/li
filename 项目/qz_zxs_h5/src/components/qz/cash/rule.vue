<template>
  <section class="rule">
    <div>
      <p class="time">活动时间</p>
      <div class="content">
        <p>2020年7月15日0:00至2020年10月30日24:00</p>
        <p>活动规则详情：</p>
        <p>1、本活动仅限齐装网用户在活动有效期内参与，每个账号每个设备每天最多抽奖一次，活动期间内最多能参与三次，每次抽奖需要消耗888现金券。</p>
        <p>2、奖品实时到账，获得的奖品可在齐装网APP：我的>我的卡券中査看。奖品数量有限，抽完即止。</p>
        <p>3、提醒：邀请好友获得200现金券，只能通过完成“推荐有礼”获得，且被推荐人下载登录齐装APP后奖励才能到账。</p>
        <p>4、特别注意：奖品需在2020年10月30日24:00前完成兑换，否则自动失效。</p>
        <p class="mt2">参与规则：</p>
        <p>您若有下列任何一种行为或情况的，齐装网有权单方面取消您的参与资格、获奖资格等：</p>
        <p>(1)不符合参与资格的；</p>
        <p>(2)提供虚假信息的；</p>
        <p>(3)虚假交易或恶意破坏活动的；</p>
        <p>(4)以任何机器人软件、蜘蛛软件、爬虫软件、刷奖软件或其它任何自动方式、不正当手段等参与本活动的；</p>
        <p>(5)有任何违反诚实信用、公序良俗、公平、公正等原则行为的；</p>
        <p>(6)其他违反相关法规、本规则行为的。 请用户理性参与活动，活动期间，如用户存在违反法律、法规或违反包括但不限于《齐装网服务协议》及活动合作平台规则，包括但不限于以作弊等非正当方式获取奖品、会员时长等行为，齐装网有权取消用户中奖资格，撤销违规交易，收回奖品或权益。如有疑问，请致电400-6969-336咨询相关事宜。</p>
      </div>
    </div>
  </section>
</template>

<script>
  export default {
    name: 'rule',
    data () {
      return {

      }
    },
    methods: {

    }
  }
</script>

<style scoped lang="scss">
  .rule{
    background: #292929;
    padding: 0.213rem 0.141rem;
    .time{
      margin-bottom: 0.162rem;
      font-size: 0.171rem;
      font-weight:bold;
      background-image: linear-gradient(to right , #EDC79D, #FFFFFF, #FFC685);
      -webkit-background-clip:text;
      -webkit-text-fill-color:transparent;
    }
    .content{
      color: #FFD097;
      font-size: 0.119rem;
      padding: 0.188rem 0.115rem 0.19rem;
      border: 3px solid #D1C0A5;
      border-radius: 0.047rem;
      background:linear-gradient(0deg,rgba(54,55,55,0.1) 0%,rgba(94,94,94,0.1) 100%);
      box-shadow:0px 0px 43px 0px rgba(0, 0, 0, 0.7);
      p{
        margin-bottom: 0.11rem;
      }
      p.mt2{
        margin-top: 0.33rem;
      }
    }
  }
</style>
