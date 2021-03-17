<template>
  <div class="license-check">
    <div class="all">
      <el-tabs v-model="activeName" @tab-click="handleClick">
        <!-- 第一个tab -->
        <el-tab-pane label="待审核" name="first">
          <licenseWaitCheck :start-tag="activeName"></licenseWaitCheck>
        </el-tab-pane>
        <!-- 第二个tab -->
        <el-tab-pane label="审核确认" name="second">
          <licenseConfirmChecked :start-tag="activeName"></licenseConfirmChecked>
        </el-tab-pane>
        <!-- 第三个tab -->
        <el-tab-pane label="审核记录" name="third">
          <licenseCheckList :start-tag="activeName"></licenseCheckList>
        </el-tab-pane>
        <!-- 第四个tab -->
        <el-tab-pane label="营业执照上传" name="fourth">
          <licenseUpload :start-tag="activeName"></licenseUpload>
        </el-tab-pane>
      </el-tabs>
    </div>
    <div class="bottom">
      <p>执照审核步骤：</p>
      <p>(1)点击打开“执照审核查看”，记录公司全称、法人、有效期。</p>
      <p>(2)登陆“国家企业信用信息公示系统”输入“公司全称”，核对“全称、法人、有效期”与营业执照一致。</p>
      <p>(3)核对一致的，并确认营业执照图片是清晰的，点击“执照审核通过”键，完成。</p>
    </div>
  </div>
</template>

<script>
  import licenseCheckList from './components/licenseCheckList'
  import licenseConfirmChecked from './components/licenseConfirmChecked'
  import licenseUpload from './components/licenseUpload'
  import licenseWaitCheck from './components/licenseWaitCheck'

  export default {
    name: 'licenseCheck',
    data() {
      return {
        activeName: '',
        currentTag: ''
      };
    },
    components: {
      licenseCheckList,
      licenseConfirmChecked,
      licenseUpload,
      licenseWaitCheck
    },
    created() {
      this.activeName = 'first'
    },
    methods: {
      handleClick(tab, event) {
        this.currentTag = this.activeName
      }
    }
  }
</script>

<style scoped>
.license-check .all {
  padding: 20px;
}
.bottom {
  color: red;
  padding: 20px;
}
.img {
  width: 100%;
  height: 100%;
  background-color: #666;
}
.el-upload__text {
  margin-top: 80px;
}
</style>
