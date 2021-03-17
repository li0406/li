<template>
  <el-dialog :visible.sync="transferDiaVis" :width="diaWidth" class="qz_dialog" @close="closeDialog">
    <span slot="title" class="dialog-title">
      <img src="@/assets/dialog_images/logo-new.jpg" alt="">
      <img src="@/assets/dialog_images/logo-fubiao.gif" alt="">
      <span style="float: right; margin-right: 30px; margin-top: 25px;">{{ diaTitle }}</span>
    </span>
    <slot />
  </el-dialog>
</template>

<script>
export default {
  name: 'Index',
  props: {
    dialogVisible: {
      type: Boolean,
      default: true
    },
    diaWidth: {
      type: String,
      default: '50%'
    },
    diaTitle: {
      type: String,
      default: '弹窗标题'
    }
  },
  data() {
    return {
      transferDiaVis: false
    }
  },
  watch: {
    dialogVisible() {
      this.transferDiaVis = this.dialogVisible
    }
  },
  methods: {
    closeDialog(evt) {
      this.transferDiaVis = false
      this.$emit('diaClose', evt)
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss">
  .qz_dialog{
    .el-dialog{
      background: white url('~@/assets/dialog_images/qz_bg.png') no-repeat;
      background-position: center center;
    }
    .el-dialog__header{
      border-bottom: 1px dashed #CCC;
    }
  }
</style>
