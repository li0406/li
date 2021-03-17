<template>
  <div class="tabs">
    <div
      v-for="item of tabs"
      :key="item.on"
      :class="['tab-item', 'point', { 'on': item.on === on }]"
      @click="setdateparams(item.on)"
    >
      {{ item.name }}
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      tabs: [
        { name: '最近一个月', on: 1 },
        { name: '最近三个月', on: 2 },
        { name: '近半年', on: 3 },
        { name: '近一年', on: 4 }
      ],
      on: 1 // 默认最近一个月 切换样式
    }
  },
  methods: {
    setdateparams(step) {
      this.on = step
      this.$emit('getdateparams', step)
    }
  }
}
</script>

<style lang="scss" scoped>
.tabs {
  position: absolute;
  top: -40px;
  right: 0;
  height: 30px;
  border-bottom: 1px solid #2c9cfa;
  color: #2c9cfa;
  font-size: 14px;
  text-align: center;
  display: flex;
  z-index: 1;
}

.tab-item {
  width: 88px;
  height: 30px;
  line-height: 30px;
  border-radius: 4px 4px 0 0;
  border: 1px solid #2c9cfa;
  margin-left: 2px;
  &:hover {
    box-shadow: inset 0 0 30px rgba(44, 112, 205, 0.5);
    color: #04e3ff;
  }
}

.on {
  box-shadow: inset 0 0 30px rgba(44, 112, 205, 0.5);
  color: #04e3ff;
}

.point {
  cursor: pointer;
}
</style>
