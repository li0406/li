<template>
  <div class="number-grow-warp">
    <span ref="numberGrow" :data-time="time" :data-value="value">0</span>
  </div>
</template>

<script>
export default {
  props: {
    time: {
      type: Number,
      default: 2
    },
    value: {
      type: Number,
      default: 0
    }
  },
  mounted() {
    this.numberGrow(this.$refs.numberGrow)
  },
  methods: {
    numberGrow(ele) {
      const _this = this
      const step = (_this.value * 10) / (_this.time * 1000)
      let current = 0
      let start = 0
      let t = setInterval(function() {
        start += step
        if (start > _this.value) {
          clearInterval(t)
          start = _this.value
          t = null
        }
        if (current === start) {
          return
        }
        current = start
        ele.innerHTML = parseInt(current)
      }, 10)
    }
  }
}
</script>

<style scoped>
.number-grow-warp{
  transform: translateZ(0);
}
</style>
