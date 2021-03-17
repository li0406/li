// 齐装云管家APP
<template>
  <div class="mod">
    <img class="item" src="@/assets/app.jpg" />
    <span class="desc" :lines="undefined">
      下载齐装云管家，
      <br />随时随地掌握订单动态！
    </span>
  </div>
</template>

<script>
export default {
  data() {
    return {
      constants: {},
    };
  },
  methods: {},
};
</script>

<style scoped>
.mod {
  display: flex;
  flex-direction: column;
  align-items: center;
  height: 262px;
  background-color: white;
}

.info {
  position: relative;
  max-width: 267px;
  height: 20px;
  margin-top: 18px;
  margin-left: 33px;
  overflow: hidden;
  color: rgba(0, 0, 0, 0.85);
  font-weight: 400;
  font-size: 12px;
  line-height: 20px;
  white-space: nowrap;
  text-overflow: ellipsis;
}

.item {
  position: relative;
  width: 160px;
  height: 160px;
  margin-top: 0;
}

.desc {
  position: relative;
  max-width: 267px;
  margin-top: 31px;
  overflow: hidden;
  color: #666;
  font-weight: 400;
  font-size: 14px;
  line-height: 24px;
  white-space: nowrap;
  text-align: center;
  text-overflow: ellipsis;
}
</style>
