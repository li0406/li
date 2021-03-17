<template>
  <div>
    <div>
      <div>
        <h3>
          选择组织架构
          <span>默认选中当前公司，可直接下一步</span>
        </h3>
      </div>
    </div>
    <div>
      <el-button class="btn-back sel-btn">{{companyName}}</el-button>
    </div>
    <div class="btns">
      <el-button disabled v-if="bul">上一步</el-button>
      <el-button v-else class="btn-back" @click="goBack()">上一步</el-button>
      <el-button type="danger" @click="toSetThird()">下一步</el-button>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      //  组织结构列表
      companyList: [{ name: '苏州青宜景装饰设计有限公司', type: 1 }],
      selIndex: 0,
      selfactory: 1, // 选中的组织架构
      bul: 0, // 是否可以上一步 1不可以 2可以
      companyName: '', //  公司名字
    };
  },
  methods: {
    goBack() {
      this.$router.push({
        path: '/set-first',
      });
    },
    //  选择组织架构
    selCompany(company, index) {
      this.selfactory = company.name;
      this.selIndex = index;
    },
    //  点击下一步
    toSetThird() {
      this.$router.push({
        path: '/set-third', // 跳转路由
      });
      this.$locSet('setSecond', {
        selfactory: '',
      });
    },
  },
  mounted() {
    const params = this.$locGet('setSecond');
    this.companyName = this.$locGet('companyName');
    this.bul = this.$locGet('bul');
    if (params) {
      this.selfactory = params.selfactory;
    }
  },
};
</script>
<style scoped>
.btns {
  display: flex;
  margin: 80px 0 0 0;
}
/* 取消按钮样式覆sel-btn盖 */
.btn-back {
  color: #606266;
  border: 1px solid #dcdfe6;
}
.btn-back:hover {
  color: #f56c6c;
  background-color: rgba(255, 239, 239, 1);
  border: 1px solid #f56c6c;
}
.company-items {
  height: 20px;
  background: rgba(233, 71, 71, 1);
  border-radius: 2px;
}
.sel-btn {
  color: #fff;
  background-color: rgba(233, 71, 71, 1);
}
.sel-btn:hover {
  color: #fff;
  background-color: rgba(228, 31, 43, 1);
}
h3 span {
  margin-left: 20px;
  color: rgb(255, 83, 83);
  font-weight: 500;
  font-size: 14px;
}
</style>
