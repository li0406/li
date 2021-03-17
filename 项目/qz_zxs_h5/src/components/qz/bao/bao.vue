<template>
  <section class="bao">
    <div class="banner">
      <img src="../../../assets/img/qz/bao/banner3.png">
    </div>

    <div class="guarantee">

      <div class="title">
        <img src="../../../assets/img/qz/bao/title.png" alt="">
      </div>

      <div :class="{'close':isClose}" class="text">
        齐装网业主保障体系“齐装保”服务，不仅在前期源头上严格把控装修公司，要求缴纳保证金，给予业主精心筛选的4家优质企业自由选择。更在装修时保障业主装修质量，为业主监管工程，在装修完毕后提供售后服务，有售后问题，平台负责协调解决。如经确认属装修公司造成的损失而其又无法履行责任的，用户凭相关部门证明材料，齐装网先行赔付用户损失的30%，单笔最高赔付可达100万。具体赔付条件、金额及范围详见<span @click="showProtocol">《齐装保赔付服务协议》</span>
      </div>

      <div class="more">
        <img src="../../../assets/img/qz/bao/more.png" class="more-img" :class="{'openImg':!isClose}"
             @click="showMore">
      </div>
    </div>

    <div class="img-list">
      <img src="../../../assets/img/qz/bao/fq.jpg">
      <img src="../../../assets/img/qz/bao/process.jpg">
      <img src="../../../assets/img/qz/bao/brand.jpg">
    </div>

    <transition name="slide-fade">
      <div v-if="protocolOpen" class="protocol" :class="{'protocol-open':protocolOpen}">
        <div class="container">
          <div class="content">
            <h1>齐装保赔付服务协议</h1>
            <p>本协议仅为选择齐装网推荐的装修公司进行交易并申请齐装保登记的业主（下称“用户”）。</p>
            <h2>一、总则</h2>
            <p>本服务条款由服务条款、保障单共同组成。凡涉及到本保障服务材料均采用电子文本格式。 </p>
            <h2>二、齐装保服务定义</h2>
            <p>齐装保赔付服务：</p>
            <p>1.用户通过在齐装网（包含不限于：PC网站、M端网站、小程序、公众号、APP等）上发布真实装修需求，并且从齐装网推荐的装修公司中选择一家装修公司签订装修合同/协议，在签约后7日内且在装修公司进场施工前，用户通过“齐装”APP提交装修合同申请齐装保，逾期或已进场施工均视为无效。</p>
            <p>2.对上述齐装网推荐的装修公司在装修过程中给用户造成的财产损失，并经仲裁委员会裁决或法院判决，确定属于装修公司造成的损失，而装修公司无法履行责任，用户提供仲裁委员会的裁决书或法院的判决书，齐装网负责先行赔付用户与该装修公司签订的合同金额的30%。</p>
            <p>3.单笔最高赔付可达100万。</p>
            <p>齐装网用户享有赔付需满足的条件：</p>
            <p>1.用户签订的装修公司必须是齐装网推荐的装修公司。</p>
            <p>2.签订合同之日起7日内且在装修公司进场施工前，在“齐装”APP上传施工合同申请齐装保。</p>
            <p>3.全国服务热线：400-6969-336。</p>
            <p>齐装保赔付金的定义：</p>
            <p>1.齐装网提供给予齐装网用户的赔付金额是根据纠纷发生时用户已在齐装网上提交的有效申请为准，如果在纠纷发生损失之后用户再申请齐装保并申请赔付，齐装网不给予赔偿。</p>
            <p>2.用户签定合同或缴纳定金后，于7日内在“齐装”APP上传施工合同申请齐装保，并配合齐装网客服人员审核取证，且签约的装修公司需在齐装网后台确认该合同。</p>
            <h2>三、责任范围</h2>
            <p>用户应同时具备以下条件，方可获得齐装保赔付服务：</p>
            <p>1、用户选择的交易的装修公司必须在齐装网上有保证金展示；</p>
            <p>2、用户达成交易后必须在“齐装”APP上传施工合同申请齐装保，由客服人员进行审核取证以及签约装修公司确认合同。</p>
            <p>3、用户享受的赔付服务，只能是齐装网推荐的装修公司对用户造成的损失，经仲裁委员会裁定或法院判决（以和解、调解方式结案的不予赔偿），方可申请齐装保赔付。赔付商家对用户造成的损失，单笔最高赔付可达100万，而对用户自身原因造成损失不进行赔付。</p>
            <h2>四、免责条款</h2>
            <p>包括但不限于以下情形下导致用户在齐装网上发生的任何财产损失、一切费用，均不属于齐装保赔付保障范围，齐装网均不负责赔偿：</p>
            <p>1、用户自身责任导致的损失：</p>
            <p>1）未选择齐装网推荐的装修公司进行交易；</p>
            <p>2）选择齐装网推荐的装修公司进行交易，但并未在“齐装”APP上传施工合同申请齐装保；</p>
            <p>3）在与商家交易过程中，私自与商家进行口头承诺；</p>
            <p>4）用户的故意行为、犯罪行为、重大过失行为；</p>
            <p>5）用户证据提供不足的情况，包括但不限于齐装网客服无法获取以下证据中的1项或多项：用户填写的信息虚假、与商家签定的合同无法确认、与商家私自进行书面的附加协议； 6）超出用户的装修款项全额，上限以上的财产损失由用户自己承担。</p>
            <p>2、涉嫌侵犯国家法律、法规禁止的任何交易。</p>
            <p>3、经齐装网审核认定用户确实存在骗取赔付的情形（齐装网保留追究法律责任权利）。</p>
            <h2>五、用户的权利义务</h2>
            <p>1、用户的权利</p>
            <p>1）当通过齐装网推荐的装修公司达成交易的用户，在齐装网要求的责任范围内进行交易的，仍然产生财产损失时，齐装网将根据协议的规定进行相关现金赔付。</p>
            <p>2）在与齐装网推荐的装修公司发生纠纷时，用户有权要求齐装网在7个工作日内调查清楚事件并给予答复。</p>
            <p>2、用户的义务</p>
            <p>1）用户有义务保持齐装网服务需要的条件处于正常状态，并在进行交易前，查看赔付运行情况，以保证全程赔付过程均受到保护，避免交易后造成的财产损失。</p>
            <p>2）当保险承诺的服务发生后，用户有义务严格履行本协议及齐装网的有关规则，支持并配合齐装网履行保险服务承诺。</p>
            <p>3）用户有义务对通过齐装网交易的相关信息保存并告知齐装网，以便作为用户索赔依据。 4）用户有义务遵守交易过程中的相关法律、法规及政策。</p>
            <h2>六、齐装网的权利与义务</h2>
            <p>（一）在收到用户的齐装保服务申请后，齐装网有权对该用户的申请予以审核真伪以确定是否向其提供赔付服务。用户同意齐装网有权根据情况同意或拒绝用户的申请。</p>
            <p>（二）如果用户使用齐装保赔付服务，在交易的过程中发生财产损失，并根据本协议规定的齐装保赔付服务规则提起索赔，则齐装网有权根据本协议规定、齐装网官网的有关规则及用户提供的资料和证据，以其独立判断确定用户索赔是否成立，如索赔成立，齐装网将向用户提供现金赔付。</p>
            <p>（三）齐装网完全有义务就本服务条款所列明的风险向保险公司投保，以保障服务承诺的兑现。</p>
            <p>（四）齐装网仅有义务根据齐装网官网有关规则向用户索要有关证据，并对用户证据的真实性与准确性、交易的实际情况作进一步调查。由于用户过错导致齐装网做出错误的赔付判断，齐装网不对用户承担补偿责任。一旦发现用户有存在骗保行为等导致齐装网发生误赔的，齐装网保留追回赔付金的权利。</p>
            <p>（五）无论何种情形，用户同意齐装网对其使用服务所致损害的最高上限赔付依照本协议第二条执行。</p>
            <h2>七、服务条款终止及修改</h2>
            <p>（一）自然终止：如用户通过“齐装”APP上签署的《齐装保赔付服务协议》因任何原因终止，则本协议将同时终止。</p>
            <p>（二）单方解除权： 如用户违反齐装网官网进行赔付的规则，齐装网有权利立刻终止本协议，并且按有关规则对用户进行处罚。如用户自主删除其理赔依据需要保留的信息，则本协议即告终止。</p>
            <p>（三）本协议规定的其他协议终止条件发生或实现，导致本协议终止。</p>
            <h2>八、其他</h2>
            <p>（一）本协议的解释与适用，以及与本协议有关的争议，均适用中华人民共和国法律。</p>
            <p>（二）如本协议的任何条款被视作无效或无法执行，则上述条款可被分离，其余部份则仍具有法律效力。</p>
            <p>（三）齐装网于用户过失或违约时放弃本协议规定的权利的，不得视为其对用户的其他或以后同类之过失或违约行为弃权。</p>
            <p>至此，您肯定已经详细阅读并已理解本《协议》，并同意严格遵守各条款和条件。以上服务协议的最终解释权归齐装网所有，因本协议产生的纠纷由齐装网公司所在地管辖。</p>
          </div>
        </div>
        <div class="close-btn" @click="showProtocol">
          ×
        </div>
      </div>
    </transition>
  </section>
</template>

<script>
  export default {
    name: 'bao',
    data () {
      return {
        isClose: true,
        dialogVisible: false,
        protocolOpen: false,
      }
    },
    methods: {
      showMore () {
        this.isClose = !this.isClose
      },
      showProtocol () {
        this.protocolOpen = !this.protocolOpen
        if (this.protocolOpen) {
          document.body.style.overflow = 'hidden'
        } else {
          document.body.style.overflow = 'scroll'
        }

      }

    }
  }
</script>

<style scoped lang="scss">
  .bao {
    font-size: 0;
    background: rgb(245, 247, 250);

    .banner {
      img {
        width: 100%;
      }
    }

    .img-list {
      img {
        width: 100%;
        margin-top: 10px;
      }
    }

    .guarantee {
      background: #fff;
      padding: 25px 27px;
      text-align: justify;

      .title {
        text-align: center;
        margin-bottom: 20px;

        img {
          width: 65%;
        }
      }

      .text {
        font-size: 14px;
        color: #666666;
        line-height: 28px;

        span {
          color: #FA9227;
        }
      }

      .more {
        text-align: center;
        margin-top: 20px;

        img {
          width: 16px;
        }

      }

      .close {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
        overflow: hidden;
      }

      .openImg {
        transform: rotate(180deg);
      }
    }

    .m-btn {
      display: block;
      width: 100%;
      font-size: 18px;
      color: #fff;
      padding: 10px 0;
      background-image: linear-gradient(to right, #D8C298, #C5A771);
    }

    .protocol {
      background: rgba(0, 0, 0, .6);
      position: fixed;
      width: 100%;
      height: 100%;
      left: 0;
      top: 0;
      padding: 0 15px;
      box-sizing: border-box;
      z-index: 999;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      display: none;

      .container {
        height: 60%;
        padding: 15px;
        background: #fff;
        border-radius: 8px;
      }

      &.protocol-open {
        display: flex;

        .container {
          opacity: 1;
        }
      }

      .content {
        height: 100%;
        overflow: scroll;
        padding-right: 20px;
        text-align: justify;

        h1 {
          text-align: center;
          position: relative;
          font-size: 18px;
          font-weight: 600;
          color: #333333;
          z-index: 1;
          margin-bottom: 15px;

          &:after {
            content: '';
            display: block;
            width: 100px;
            height: 8px;
            background: #E7D9C4;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: 0;
            z-index: -1;
          }
        }

        h2 {
          font-size: 16px;
          font-weight: 500;
          color: #666666;
          margin: 12px 0 10px;
        }

        p {
          font-size: 14px;
          color: #666666;
          line-height: 28px;
        }
      }

      .close-btn {
        width: 30px;
        height: 30px;
        text-align: center;
        line-height: 30px;
        font-size: 16px;
        color: #A6A6A6;
        border: 1px solid #A6A6A6;
        border-radius: 50%;
        margin-top: 15px;
      }
    }
    .slide-fade-enter-active {
      transition: all .3s ease;
    }

    .slide-fade-leave-active {
      transition: all .3s cubic-bezier(1.0, 0.5, 0.8, 1.0);
    }

    .slide-fade-enter, .slide-fade-leave-to {
      transform: translateY(-10px);
      opacity: 0;
    }
  }




</style>
