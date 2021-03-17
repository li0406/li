<template>
  <div class="tab-swith">
    <div
      class="tab-swith-item"
      :style="[
        {width: width + 'px'},
        {backgroundImage: 'url(' + require(`@/assets/imgs/tab-swith-bj${width}-${tabBj}.png`) + ')'}
      ]"
    >
      <el-row>
        <el-col :span="tab3 ? 8: 12" @click.native="tabSwithBtn(1)">{{ tab1 }}</el-col>
        <el-col :span="tab3 ? 8: 12" @click.native="tabSwithBtn(2)">{{ tab2 }}</el-col>
        <el-col :span="tab3 ? 8: 12" @click.native="tabSwithBtn(3)">{{ tab3 }}</el-col>
      </el-row>
    </div>
  </div>
</template>

<script>
export default {
  components: {},
  props: {
    tab1: {
      type: String,
      default: ''
    },
    tab2: {
      type: String,
      default: ''
    },
    tab3: {
      type: String,
      default: ''
    },
    fn: {
      type: String,
      default: ''
    },
    tabBj: {
      type: Number,
      default: 1
    },
    width: {
      type: Number,
      default: 362
    }
  },
  data() {
    return {}
  },
  computed: {},
  watch: {},
  created() {},
  mounted() {},
  methods: {
    tabSwithBtn(val) {
      this.$emit('tabSwitch', val)
    }
  }
}
</script>
<style rel=" stylesheet/scss" lang="scss" scoped>
.tab-swith{
  .tab-swith-item{
    height: 46px;
    line-height: 46px;
    background-size: 100% 100%;
    background-repeat: no-repeat;
    cursor: pointer;
    text-align: center;
  }
}
</style>
