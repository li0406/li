// 详细信息
<template>
  <div class="base-detail" v-loading="loading">
    <el-form
      ref="ruleForm"
      :model="ruleForm"
      status-icon
      :rules="rules"
      label-position="right"
      label-width="100px"
      class="demo-ruleForm"
    >
      <el-row :gutter="10">
        <el-form-item label="服务区域:" prop="dqtags">
          <div class="add-list">
            <span class="item" v-for="item in dqtagList" :key="item.id">{{ item.name }}</span>
            <div class="add-btn" @click="addTags(1)">+添加</div>
          </div>
        </el-form-item>
      </el-row>
      <el-row :gutter="10">
        <el-form-item label="服务类型:" prop="lxtags">
          <div class="add-list">
            <span class="item" v-for="item in lxtagList" :key="item.id">{{ item.name }}</span>
            <div class="add-btn" @click="addTags(2)">+添加</div>
          </div>
        </el-form-item>
      </el-row>
      <el-row :gutter="10">
        <el-form-item label="服务保障:">
          <div class="add-list">
            <span class="item" v-for="item in bztagList" :key="item.id">{{ item.name }}</span>
            <div class="add-btn" @click="addTags(3)">+添加</div>
            <div class="add-hint">
              <i class="el-icon-warning"></i>所勾选的服务保障必须是本公司真实提供的，否则需要提供法律责任
            </div>
          </div>
        </el-form-item>
      </el-row>
      <el-row :gutter="10">
        <el-form-item label="擅长风格:" prop="fgtags">
          <div class="add-list">
            <span class="item" v-for="item in fgtagList" :key="item.id">{{ item.name }}</span>
            <div class="add-btn" @click="addTags(4)">+添加</div>
          </div>
        </el-form-item>
      </el-row>
      <el-row>
        <el-col class="w200">
          <el-form-item label="承接价位：" prop="jiawei">
            <el-input v-model="ruleForm.jiawei" placeholder />
          </el-form-item>
        </el-col>
        <el-col :span="4" class="price-info">
          万起
          <i class="el-icon-warning"></i>
          <span>留空为不限</span>
        </el-col>
      </el-row>
      <el-form-item class="date" label="成立时间：" prop>
        <el-col :span="4">
          <el-date-picker
            v-model="ruleForm.chengli"
            type="date"
            value-format="yyyy-MM-dd"
            format="yyyy-MM-dd"
            placeholder="选择日期"
          ></el-date-picker>
        </el-col>
      </el-form-item>
      <el-row>
        <el-col  class="w200">
          <el-form-item label="施工队伍：" prop>
            <el-input v-model="ruleForm.team_num" placeholder />
          </el-form-item>
        </el-col>
        <el-col :span="4" class="price-info">队</el-col>
      </el-row>
      <el-form-item label="公司规模:" prop>
        <el-col :span="4">
          <el-select v-model="ruleForm.guimo" placeholder="请选择">
            <el-option v-for="item in scaleList" :key="item.value" :label="item.label" :value="item.value"></el-option>
          </el-select>
        </el-col>
      </el-form-item>
      <el-form-item label="交通路线：" prop="luxian">
        <el-col :span="8">
          <el-input v-model="ruleForm.luxian" placeholder />
        </el-col>
      </el-form-item>
      <el-form-item label="简介:" prop="text">
        <el-col :span="8">
          <el-input type="textarea" v-model="ruleForm.text"></el-input>
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button type="danger" class="footer-btn" @click="submitForm('ruleForm')">更新</el-button>
      </el-form-item>
    </el-form>
    <el-dialog :title="tagName" :visible.sync="dialogVisible" top="300px" width="720px" :before-close="handleClose">
      <el-checkbox-group v-if="numInfo == 1" v-model="ruleForm.dqtags">
        <el-checkbox v-for="item in tags" :key="item.id" :label="item.id">{{ item.name }}</el-checkbox>
      </el-checkbox-group>
      <el-checkbox-group v-if="numInfo == 2" v-model="ruleForm.lxtags">
        <el-checkbox v-for="item in tags1" :key="item.id" :label="item.id">{{ item.name }}</el-checkbox>
      </el-checkbox-group>
      <el-checkbox-group v-if="numInfo == 3" v-model="ruleForm.bztags">
        <el-checkbox v-for="item in tags2" :key="item.id" :label="item.id">{{ item.name }}</el-checkbox>
      </el-checkbox-group>
      <el-checkbox-group v-if="numInfo == 4" v-model="ruleForm.fgtags">
        <el-checkbox v-for="item in tags3" :key="item.id" :label="item.id">{{ item.name }}</el-checkbox>
      </el-checkbox-group>
      <span slot="footer" class="dialog-footer">
        <el-button type="danger" @click="confirm">确 定</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import { validNum } from '@/utils/validate';
import base from '@/api/base-info';

export default {
  name: 'Basedetail',
  data() {
    const validatedq = (rule, value, callback) => {
      if (this.ruleForm.dqtags.length < 1) {
        callback(new Error('请选择服务区域'));
      } else {
        callback();
      }
    };
    const validatelx = (rule, value, callback) => {
      if (this.ruleForm.lxtags.length < 1) {
        callback(new Error('请选择服务类型'));
      } else {
        callback();
      }
    };
    const validatefg = (rule, value, callback) => {
      if (this.ruleForm.fgtags.length < 1) {
        callback(new Error('请选择擅长风格'));
      } else {
        callback();
      }
    };
    const validatenum = (rule, value, callback) => {
      if (!value) {
        callback();
      } else if (!validNum(value)) {
        callback(new Error('请填写正确价位'));
      } else {
        callback();
      }
    };
    return {
      ruleForm: {
        dqtags: [],
        lxtags: [],
        bztags: [],
        fgtags: [],
        jiawei: '',
        chengli: '',
        team_num: '',
        guimo: '',
        luxian: '',
        text: '',
        quyu: '',
        fuwu: '',
        baozhang: '',
        fengge: '',
      },
      rules: {
        dqtags: [{ required: true, validator: validatedq, trigger: 'blur' }],
        lxtags: [{ required: true, validator: validatelx, trigger: 'blur' }],
        fgtags: [{ required: true, validator: validatefg, trigger: 'blur' }],
        jiawei: [{ validator: validatenum, trigger: 'change' }],
        luxian: [{ required: true, message: '请输入交通路线', trigger: 'change' }],
        text: [{ required: true, message: '请填写简介', trigger: 'change' }],
      },
      tags: [],
      tags1: [],
      tags2: [],
      tags3: [],
      tagName: '服务区域',
      dqtagList: [],
      lxtagList: [],
      bztagList: [],
      fgtagList: [],
      numInfo: '',
      scaleList: [
        {
          value: '1',
          label: '全国连锁',
        },
        {
          value: '2',
          label: '大中型',
        },
        {
          value: '3',
          label: '中小型',
        },
      ],
      dialogVisible: false,
      loading: false,
    };
  },
  created() {
    this.getData();
  },
  methods: {
    getData() {
      base.get({}).then((res) => {
        // eslint-disable-next-line radix
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          const form = res.data.data;
          this.ruleForm.jiawei = form.jiawei;
          this.ruleForm.chengli = form.chengli;
          this.ruleForm.team_num = form.team_num;
          this.ruleForm.guimo = form.guimo;
          this.ruleForm.luxian = form.luxian;
          this.ruleForm.text = form.text;
          base
            .getareainfobycityid({
              city_name: form.cname,
            })
            .then((result) => {
              const dqtag = result.data.data.slice(1);
              const tagList = dqtag.map((item) => {
                return {
                  id: item.area_id,
                  name: item.area_name,
                };
              });
              this.tags = tagList;
              const arrId = form.quyu ? form.quyu : [];
              const arrTags = tagList;
              // eslint-disable-next-line eqeqeq
              const dqtagList = arrTags.filter((ele) => arrId.filter((x) => x == ele.id).length > 0);
              this.ruleForm.dqtags = arrId.map((item) => {
                return Number(item);
              });
              this.dqtagList = dqtagList;
            });
          this.leixing(form);
          this.baozhang(form);
          this.fengge(form);
        } else {
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },
    leixing(form) {
      base
        .leixing({
          type: 'leixing',
        })
        .then((res) => {
          // eslint-disable-next-line radix
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.tags1 = res.data.data;

            const arrId = form.fuwu ? form.fuwu : [];
            const arrTags = res.data.data;
            // eslint-disable-next-line eqeqeq
            const lxtagList = arrTags.filter((ele) => arrId.filter((x) => x == ele.id).length > 0);
            this.ruleForm.lxtags = arrId.map((item) => {
              return Number(item);
            });
            this.lxtagList = lxtagList;
          } else {
            this.$message({
              message: res.data.error_msg,
              type: 'error',
            });
          }
        });
    },
    baozhang(form) {
      base
        .leixing({
          type: 'baozhang',
        })
        .then((res) => {
          // eslint-disable-next-line radix
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.tags2 = res.data.data;

            const arrId = form.baozhang ? form.baozhang : [];
            const arrTags = res.data.data;
            // eslint-disable-next-line eqeqeq
            const bztagList = arrTags.filter((ele) => arrId.filter((x) => x == ele.id).length > 0);
            this.ruleForm.bztags = arrId.map((item) => {
              return Number(item);
            });
            this.bztagList = bztagList;
          } else {
            this.$message({
              message: res.data.error_msg,
              type: 'error',
            });
          }
        });
    },
    fengge(form) {
      base.fengge({}).then((res) => {
        // eslint-disable-next-line radix
        if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
          this.tags3 = res.data.data;

          const arrId = form.fengge ? form.fengge : [];
          const arrTags = res.data.data;
          // eslint-disable-next-line eqeqeq
          const fgtagList = arrTags.filter((ele) => arrId.filter((x) => x == ele.id).length > 0);
          this.ruleForm.fgtags = arrId.map((item) => {
            return Number(item);
          });
          this.fgtagList = fgtagList;
        } else {
          this.$message({
            message: res.data.error_msg,
            type: 'error',
          });
        }
      });
    },

    handleClose() {
      this.dialogVisible = false;
    },
    submitForm(formName) {
      this.ruleForm.quyu = this.ruleForm.dqtags;
      this.ruleForm.fuwu = this.ruleForm.lxtags;
      this.ruleForm.baozhang = this.ruleForm.bztags;
      this.ruleForm.fengge = this.ruleForm.fgtags;
      // eslint-disable-next-line consistent-return
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.loading = true;
          base.editDetail(this.ruleForm).then((res) => {
            // eslint-disable-next-line radix
            if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
              this.loading = false;
              this.$message({
                message: '上传成功',
                type: 'success',
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
    addTags(ele) {
      this.dialogVisible = true;
      this.numInfo = ele;
      switch (ele) {
        case 1:
          this.tagName = '服务区域';
          break;
        case 2:
          this.tagName = '服务类型';
          break;
        case 3:
          this.tagName = '服务保障';
          break;
        case 4:
          this.tagName = '擅长风格';
          break;
        default:
          this.tagName = '服务区域';
      }
    },
    confirm() {
      if (this.numInfo === 1) {
        const tags = this.tags;
        const dqtags = this.ruleForm.dqtags;
        const arr = tags.filter((item) => {
          return dqtags.some((ele) => ele === item.id);
        });
        this.dqtagList = arr;
      } else if (this.numInfo === 2) {
        const tags = this.tags1;
        const lxtags = this.ruleForm.lxtags;
        const arr = tags.filter((item) => {
          return lxtags.some((ele) => ele === item.id);
        });
        this.lxtagList = arr;
      } else if (this.numInfo === 3) {
        const tags = this.tags2;
        const bztags = this.ruleForm.bztags;
        const arr = tags.filter((item) => {
          return bztags.some((ele) => ele === item.id);
        });
        this.bztagList = arr;
      } else if (this.numInfo === 4) {
        const tags = this.tags3;
        const fgtags = this.ruleForm.fgtags;
        const arr = tags.filter((item) => {
          return fgtags.some((ele) => ele === item.id);
        });
        this.fgtagList = arr;
      }
      this.dialogVisible = false;
    },
  },
};
</script>

<style lang="less">
.base-detail {
  .el-form-item__label {
    font-size: 12px;
  }

  .el-input__inner {
    height: 36px;
    padding: 0 10px;
    padding-right: 32px;
    font-size: 12px;
    line-height: 36px;
  }

  .add-list {
    .item {
      display: inline-block;
      height: 30px;
      margin-right: 10px;
      padding: 0 20px;
      color: rgba(255, 83, 83, 1);
      font-weight: 400;
      font-size: 12px;
      line-height: 30px;
      text-align: center;
      background: rgba(255, 247, 247, 1);
      border-radius: 2px;
    }
  }

  .add-btn {
    display: inline-block;
    width: 70px;
    height: 32px;
    margin-right: 10px;
    color: #f86f72;
    font-size: 12px;
    line-height: 34px;
    text-align: center;
    border: 1px dashed #ff474a;
    border-radius: 2px;
    cursor: pointer;
  }

  .add-hint {
    display: inline-block;
    color: rgba(255, 83, 83, 1);
    font-weight: 400;
    font-size: 12px;
  }

  .add-hint i {
    margin: 0 10px;
    font-size: 14px;
  }

  .price-info {
    margin-left: 20px;
    color: #333;
    font-weight: 400;
    font-size: 12px;
    line-height: 42px;
  }

  .price-info span {
    color: rgba(255, 83, 83, 1);
  }

  .price-info i {
    margin: 0 10px;
    color: rgba(255, 83, 83, 1);
    font-size: 14px;
  }
  .date {
    .el-input__inner {
      padding-left: 30px;
    }
  }
  .el-checkbox__input.is-checked + .el-checkbox__label {
    color: rgba(255, 83, 83, 1);
  }
  .el-checkbox__input.is-checked .el-checkbox__inner,
  .el-checkbox__input.is-indeterminate .el-checkbox__inner {
    background-color: rgba(255, 83, 83, 1);
    border-color: rgba(255, 83, 83, 1);
  }
  .footer-btn {
    margin-bottom: 200px;
  }
  .w200 {
    width: 200px;
  }
}
</style>
