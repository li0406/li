<template>
  <div class="editLimits-contains" v-loading="loading">
    <el-card class="box-card">
      <div class="clearfix">
        <span>编辑</span>
      </div>
    </el-card>
    <el-form
      ref="ruleForm"
      :model="ruleForm"
      status-icon
      :rules="rules"
      label-position="right"
      label-width="100px"
      class="demo-ruleForm"
    >
      <el-form-item label-width="0px">
        <el-checkbox-group v-model="ruleForm.child">
          <div class="checks" v-for="(item) in tagsList" :key="item.role_group_name">
            <div class="check-title">{{item.role_group_name}}:</div>
            <el-checkbox-button v-for="(it) in item.child" :key="it.id" :label="it.id">
              <el-popover
                placement="top-start"
                title="描述"
                width="200"
                trigger="hover"
                :content="it.description"
              >
                <div slot="reference">{{it.role_name}}</div>
              </el-popover>
            </el-checkbox-button>
          </div>
        </el-checkbox-group>
      </el-form-item>
      <div class="line"></div>
      <el-row class="tips">
        <el-col class="gap">已选角色:</el-col>
        <span v-for="item in selectTags" :key="item.id">{{item.role_name}}</span>
      </el-row>
      <el-form-item label-width="0px" class="last-item">
        <el-button type="danger" @click="submitForm('ruleForm')">保存</el-button>
        <el-button plain @click="resetForm('ruleForm')">取消</el-button>
        <el-button type="danger" plain @click="goList">返回列表</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import staff from '@/api/staff-con';

export default {
  name: 'Basedetail',
  data() {
    return {
      ruleForm: {
        child: [],
      },
      rules: {},
      tagsList: [],
      child: [],
      loading: false,
    };
  },
  computed: {
    selectTags() {
      const arrId = this.ruleForm.child;
      const arrTags = this.child;
      return arrTags.filter((ele) => arrId.filter((x) => x === ele.id).length > 0);
    },
  },
  created() {
    const account = this.$route.query.account ? this.$route.query.account : '';
    this.account = account;
    this.getData(account);
  },
  methods: {
    getData(tel) {
      staff.rolelist({ account: tel }).then((res) => {
        // eslint-disable-next-line radix
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          const tagsList = res.data.data;
          this.tagsList = tagsList;
          const tagsArr = tagsList.map((item) => {
            return item.child;
          });
          let child = [];
          tagsArr.forEach((item) => {
            child = [...child, ...item];
          });
          this.child = child;
          const rule = child.filter((item) => item.is_checked === 1);
          this.ruleForm.child = rule.map((item) => item.id);
        } else {
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
    submitForm(formName) {
      // eslint-disable-next-line consistent-return
      this.$refs[formName].validate((valid) => {
        if (valid) {
          if (this.ruleForm.child.length < 1) {
            this.$message({
              message: '请选择角色',
              type: 'error',
            });
            return false;
          }
          this.loading = true;
          const datas = {
            role: this.ruleForm.child,
            account: this.account,
          };
          staff.roleup(datas).then((res) => {
            if (parseInt(res.status, 10) === 200 && parseInt(res.data.error_code, 10) === 0) {
              this.loading = false;
              this.$message({
                message: '保存成功',
                type: 'success',
              });
              this.$router.push({
                path: '/staff',
                query: {
                  grade: 'fourth',
                  account: this.account,
                },
              });
            } else {
              this.loading = false;
              this.$message({
                message: res.data.error_msg,
                type: 'error',
              });
            }
          });
        } else {
          return false;
        }
      });
    },
    resetForm(formName) {
      this.$refs[formName].resetFields();
      this.$router.push({
        path: '/staff',
        query: {
          grade: 'fourth',
          account: this.account,
        },
      });
    },
    // 跳转
    goList() {
      this.$router.push({
        path: '/workers-list',
      });
    },
  },
};
</script>

<style lang="less">
.editLimits-contains {
  min-height: 1000px;
  background: #fff;
  .demo-ruleForm {
    margin-top: 20px;
    margin-left: 30px;
  }
  .title {
    color: rgba(102, 102, 102, 1);
    font-weight: bold;
    font-size: 14px;
    line-height: 60px;

    span {
      color: #ff5353;
    }
  }

  .checks {
    margin-top: 20px;
    line-height: 30px;

    .el-checkbox-button__inner {
      margin-right: 10px;
      padding: 10px 20px;
      color: #666;
      font-size: 12px;
      vertical-align: initial;
      background: rgba(255, 255, 255, 1);
      background-color: #fafafa;
      border: none;
      border: 1px solid rgba(222, 225, 231, 1);
      border-radius: 16px;
      box-shadow: none;
    }

    .el-checkbox-button__inner:hover {
      color: #666;
    }

    .check-title {
      width: 64px;
      margin: 20px 0;
      margin-top: 30px;
      color: rgba(48, 49, 51, 1);
      font-weight: 400;
      font-size: 14px;
      line-height: 18px;
      text-align: center;
      border-left: 2px solid #e94747;
    }

    .el-checkbox-button.is-checked .el-checkbox-button__inner {
      color: #e94747;
      background-color: #fff7f7;
      border: 1px solid #e94747;
    }
  }
  .tips {
    display: flex;
    align-items: center;
    padding-bottom: 20px;
    font-size: 12px;
    .gap {
      width: 82px;
      margin-right: 32px;
      padding-left: 10px;
      color: #303133;
      font-weight: 400;
      font-size: 14px;
      line-height: 18px;
      border-left: 2px solid #e94747;
    }
    span {
      display: inline-block;
      margin-right: 10px;
      padding: 10px 20px;
      color: #fff;
      color: #e94747;
      line-height: 36px;
      line-height: 1;
      text-align: center;
      background: #f56c6c;
      background-color: #fff7f7;
      border: 1px solid #e94747;
      border-radius: 2px;
      border-radius: 16px;
    }
  }
  .last-item {
    margin: 20px 0;
  }
  .el-card.is-always-shadow {
    box-shadow: none;
  }
  .el-card {
    border: none;
    border-bottom: 2px solid #e4e7ed;
  }
  .line {
    width: 100%;
    height: 1px;
    margin: 20px 0;
    background: rgba(242, 246, 252, 1);
  }
}
</style>
