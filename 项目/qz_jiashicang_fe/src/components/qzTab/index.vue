<template>
  <div class="qzTab">
    <div class="flex">
      <div v-for="item of tabArray" :key="item.type" :class="['title',{'on':type === item.type}]" @click="seltab(item.type)">{{ item.name }}</div>
    </div>
  </div>
  <!-- <div class="tab-swith">
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
  </div> -->
</template>

<script>
export default {
  components: {},
  props: {
    tabArray: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      type: 1
    }
  },
  computed: {},
  watch: {},
  created() {},
  mounted() {},
  methods: {
    seltab(type) {
      this.type = type
      this.$emit('setTabParams', type)
    }
  }
}
</script>
<style lang="scss" scoped>
.qzTab{
  height: 50px;
  line-height: 50px;
.title{
  font-size: 20px;
  color: #2c9cf9;
  margin: 0 20px 0 0;
  cursor: pointer;
}
.on{
  color: #fff;
}
}
</style>
