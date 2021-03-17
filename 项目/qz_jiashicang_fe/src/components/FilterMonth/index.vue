<template>
  <div class="FilterMonth">
    <el-radio-group v-model="monthParams" class="elradio" size="small" @change="setMonthParams">
      <el-radio-button v-for="item of monthOptions" :key="item.params" :label="item.params">{{ item.name }}</el-radio-button>
    </el-radio-group>
  </div>
</template>

<script>
export default {
  data() {
    return {
      monthParams: 1,
      monthOptions: [
        {
          name: '当月',
          params: 1
        },
        {
          name: '三个月',
          params: 2
        }
      ]
    }
  },
  methods: {
    setMonthParams() {
      this.$emit('setMonthParams', this.monthParams)
    }
  }
}
</script>

