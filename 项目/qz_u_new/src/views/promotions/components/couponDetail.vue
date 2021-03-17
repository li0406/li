<template>
  <div class="couponDetail">
    <h2>礼券详情</h2>
    <div class="detail-item"><span class="title">礼券名称:</span> {{ name }}</div>
    <div class="detail-item"><span class="title">优惠活动:</span> {{ activetext }}</div>
    <div class="detail-item"><span class="title">发放数量:</span> {{ amount }}</div>
    <div class="detail-item"><span class="title">有效时间:</span> {{ start }} - {{ end }}</div>
    <div class="detail-item"><span class="title">活动时间:</span> {{ activity_start }} - {{ activity_end }}</div>
    <div class="detail-item"><span class="title">上传时间:</span> {{ add_time }}</div>
    <div class="detail-item"><div>使用规则:</div> <div v-html="rule" style="margin-top: 15px;line-height: 24px"></div></div>
    <div class="detail-item"><span>礼券图片:</span>

      <div class="mj" v-if="active_type === 1" style="margin-top: 15px">
        <div>
          移动端:
          <div class="mobile" style="margin-top: 15px">
            <p><strong>{{ money2 }}</strong>满<span>{{ money1 }}</span>元可领</p>
            <p>使用时间：{{ start }} - {{ end }}</p>
            <div class="lq">
              立即<br/>领取
            </div>
          </div>
        </div>

        <div style="margin-top: 15px">
          PC端:
          <div class="pc" style="margin-top: 15px">
            <p><strong>{{ money2 }}</strong>满<span>{{ money1 }}</span>元可领</p>
            <p>使用时间：{{ start }} - {{ end }}</p>
          </div>
        </div>

      </div>

      <div class="ms" v-else-if="active_type === 2" style="margin-top: 15px">
        <div>
          移动端:
          <div class="mobile" style="margin-top: 15px">
            <p><strong>{{ gift }}</strong>满<span>{{ money3 }}</span>元可领</p>
            <p>使用时间：{{ start }} - {{ end }}</p>
            <div class="lq">
              立即<br/>领取
            </div>
          </div>
        </div>

        <div style="margin-top: 15px">
          PC端:
          <div class="pc" style="margin-top: 15px">
            <p><strong>{{ gift }}</strong>满<span>{{ money3 }}</span>元可领</p>
            <p>使用时间：{{ start }} - {{ end }}</p>
          </div>
        </div>

      </div>

    </div>
    <div class="detail-item">审核状态:
      <template>
        <span v-if="check === 1">
          待审核
        </span>
        <span v-if="check === 2">
          审核通过
        </span>
        <span v-if="check === 3">
          未通过
        </span>
        <span v-if="check === 4">
          撤回
        </span>
      </template>

    </div>

    <div class="detail-item">审核意见: <span>{{ check_reason }}</span></div>

    <el-button style="margin-bottom: 80px" @click="$router.go(-1)">返回</el-button>
  </div>
</template>

<script>
  import api from '@/api/promotions';
  import dayjs from 'dayjs';

  export default {
    name: 'couponDetail',
    data() {
      return {
        name: '',
        activetext: '',
        amount: '',
        activity_start: '',
        activity_end: '',
        start: '',
        end: '',
        add_time: '',
        rule: '',
        active_type: '',
        check: '',
        check_reason: '',
        money1: '',
        money2: '',
        money3: '',
        gift: '',
      };
    },
    mounted() {
      this.getDetail();
    },
    methods: {
      getDetail() {
        api.getCouponDetail(this.$route.params.id).then((res) => {
          this.name = res.data.data.name;
          this.activetext = res.data.data.activetext;
          this.amount = res.data.data.amount;
          this.activity_start =  dayjs(res.data.data.activity_start * 1000).format('YYYY-MM-DD');
          this.activity_end =  dayjs(res.data.data.activity_end * 1000).format('YYYY-MM-DD');
          this.start = dayjs(res.data.data.start * 1000).format('YYYY-MM-DD');
          this.end =  dayjs(res.data.data.end * 1000).format('YYYY-MM-DD');
          this.add_time = dayjs(res.data.data.add_time * 1000).format('YYYY-MM-DD');
          this.rule = res.data.data.rule.replace(/(\r\n|\n|\r)/gm, '<br />');
          this.active_type = res.data.data.active_type;
          this.check = res.data.data.check;
          this.check_reason = res.data.data.check_reason;
          this.money1 = res.data.data.money1;
          this.money2 = res.data.data.money2;
          this.money3 = res.data.data.money3;
          this.gift = res.data.data.gift;
        });
      },
    },

    filters: {
      dateFormat(time) {
        const date = new Date(time * 1000);
        const y = date.getFullYear();
        const m = (date.getMonth() + 1) < 10 ? `0${date.getMonth() + 1}` : date.getMonth() + 1;
        const d = date.getDate() < 10 ? `0${date.getDate()}` : date.getDate();
        return `${y}-${m}-${d}`;
      },
    },
  };
</script>

<style scoped lang="scss">
  .couponDetail {
    padding: 30px;
    background: #fff;

    .title {
      display: block;
      float: left;
      width: 80px;
    }

    .detail-item {
      margin: 15px 0;
    }

    .ms {
      p {
        margin: 0;
      }

      .mobile {
        box-sizing: border-box;
        width: 355px;
        height: 98px;
        padding-top: 18px;
        padding-left: 22px;
        color: #fff;
        background: url('../../../assets/modul_bgm_2.png') no-repeat center;
        background-size: 100%;

        p {
          font-size: 14px;
          line-height: 28px;

          strong {
            margin-right: 5px;
            font-size: 18px;
          }

          span {
            margin-left: 5px;
          }
        }

        .lq {
          position: absolute;
          top: 25px;
          right: 23px;
          font-weight: 600;
          font-size: 20px;
        }
      }

      .pc {
        box-sizing: border-box;
        width: 355px;
        height: 98px;
        padding-top: 18px;
        padding-left: 22px;
        color: #fff;
        background: url('../../../assets/modul_bgp_2.png') no-repeat center;
        background-size: 100%;

        p {
          font-size: 14px;
          line-height: 28px;

          strong {
            margin-right: 5px;
            font-size: 18px;
          }

          span {
            margin-left: 5px;
          }
        }
      }
    }

    .mj {
      p {
        margin: 0;
      }

      .mobile {
        position: relative;
        box-sizing: border-box;
        width: 355px;
        height: 98px;
        padding-top: 18px;
        padding-left: 22px;
        color: #fff;
        background: url('../../../assets/modul_bgm_1.png') no-repeat center;
        background-size: 100%;

        p {
          font-size: 14px;
          line-height: 28px;

          strong {
            margin-right: 5px;
            font-size: 18px;
          }

          span {
            margin-left: 5px;
          }
        }

        .lq {
          position: absolute;
          top: 25px;
          right: 23px;
          font-weight: 600;
          font-size: 20px;
        }
      }

      .pc {
        box-sizing: border-box;
        width: 355px;
        height: 98px;
        padding-top: 18px;
        padding-left: 22px;
        color: #fff;
        background: url('../../../assets/modul_bgp_1.png') no-repeat center;
        background-size: 100%;

        p {
          font-size: 14px;
          line-height: 28px;

          strong {
            margin-right: 5px;
            font-size: 18px;
          }

          span {
            margin-left: 5px;
          }
        }
      }
    }
  }

</style>
