<template>
  <div class="free_bill">
    <div class="head">
      <div class="marquee">

      </div>
      <div class="posi_text">
        <p class="title">免费获取4套设计方案</p>
        <p class="bord"></p>
        <p>专业设计师1对1服务</p>
        <p>4份设计风格PK</p>
      </div>
    </div>
    <app-bill></app-bill>
  </div>
</template>
<script>
import appBill from "../sheji/appBill.vue";
export default {
  name: "free_bill",
  data() {
    return {
      disclaimer: true,
      addrText: "请选择城市",
      iSpeed: 2,
    };
  },
  components: {
    appBill,
  },
  mounted() {
  },
  methods: {
  },
};
</script>
<style scoped>
.free_bill {
  width: 100%;
  height: 100%;
  background-color: #f2f3f7;
}
.marquee {
    width: 100%;
    height: 2.133rem;
    background-color: red;
    color: white;
    white-space: nowrap;
    overflow: hidden;
    box-sizing: border-box;
    background:url('https://staticqn.qizuang.com/custom/20210129/FuEB1oDip4q6FKiccf_wW-mIVJVH') no-repeat;
    background-size: 114% 2.133rem;
    animation: marquee 6s linear infinite;
}
@keyframes marquee {
    0%   { background-position: 0 0; }
    50% { background-position: 100% 0; }
    100% { background-position: 0 0; }
}
.head {
  width: 100%;
}
.posi_text {
  position: absolute;
  top: 0.388rem;
  left: 0.196rem;
  font-size: 0.154rem;
  font-weight: 500;
  color: #fff;
}
.title {
  font-size: 0.256rem;
  font-weight: bold;
}
.bord {
  width: 0.341rem;
  height: 0.043rem;
  background-color: #fff;
  margin-top: 0.205rem;
  margin-bottom: 0.111rem;
}

#div1 {
  position: relative;
  height: 230px;
  overflow: hidden;
}

#div1 ul {
  position: absolute;
  left: 0;
}

#div1 ul li {
  float: left;
  list-style: none;
  width: 150px;
  height: 150px;
}
</style>




