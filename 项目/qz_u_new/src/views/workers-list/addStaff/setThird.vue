<!--
 * @Author: your name
 * @Date: 2020-06-28 10:15:01
 * @LastEditTime: 2020-07-13 09:09:30
 * @LastEditors: Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: \qz_admin_centerd:\wamp64\www\qz_u_new\src\views\workers-list\addStaff\setThird.vue
-->
<template>
  <div>
    <div>
      <div class="setThird-title">
        <h3>选择角色权限</h3>
        <div @click="statement=true">角色权限声明</div>
      </div>
    </div>
    <div class="allClass">

      <div v-for="role of roleList"
           :key="role.role_group_name">
        <div class="power">{{role.role_group_name}}</div>
        <div class="power-btn">

          <div :class="['role-btn',{'on':selRoleId.includes(roleSon.id)}]"
               v-for="roleSon of role.child"
               :key="roleSon.role_name"
               @click="selRoleMethod(roleSon)">
            <el-popover placement="top-start"
                        title="描述"
                        width="200"
                        trigger="hover"
                        :content="roleSon.description">
              <div slot="reference">{{roleSon.role_name}}</div>
            </el-popover>
          </div>

        </div>
      </div>
    </div>
    <div class="sel-btn">
      <div class="power">已选角色：</div>
      <div class="sel-allBtn"
           v-for="(sel,index) of selRoleList"
           :key="index">{{sel.role_name}}</div>
    </div>
    <div class="btns">
      <el-button class="btn-back"
                 @click="goBack()">上一步</el-button>
      <el-button type="danger"
                 @click="toSetFourth()">下一步</el-button>
    </div>
    <!-- 角色权限声明-->
    <el-dialog title="角色权限说明"
               :visible.sync="statement"
               :close-on-click-modal="false"
               top="250px"
               width="570.89px">
      <div class="explain">
        <div>
          <h3>超级管理员：</h3>
          <div>商家企业账号</div>
        </div>
        <div>
          <h3>客服：</h3>
          <div>具备查看装企所在城市区域订单以及订单“跟单”和“指派”的权限</div>
        </div>
        <div>
          <h3>设计师</h3>
          <div>具备被指派订单的“查看”、“跟进”“装修案例”相关权限</div>
        </div>
        <div>
          <h3>网店管理员：</h3>
          <div>具备“装修案例”、“企业管理”、“员工管理”等相关权限</div>
        </div>
      </div>
      <div class="reason-button">
        <el-button class="btn-back"
                   @click="statement = false">取 消</el-button>
        <el-button type="danger"
                   @click="statement = false">确 定</el-button>
      </div>
    </el-dialog>
    <!-- 角色权限声明-->
  </div>
</template>
<script>
import api from '@/api/staff';

export default {
  data () {
    return {
      account: '', // 账号手机号码
      //  角色权限弹窗
      statement: false, //  默认不弹
      roleList: [], //  角色列表
      selRoleList: [], //  已选择的角色列表
      selRoleId: [], //  已选角色id
    };
  },
  methods: {
    //  点击选取角色
    selRoleMethod (roleSon) {
      if (this.selRoleId.includes(roleSon.id)) {
        const i = this.selRoleId.indexOf(roleSon.id)
        this.selRoleId.splice(i, 1);
        this.selRoleList.splice(i, 1);
      } else {
        this.selRoleId.push(roleSon.id);
        this.selRoleList.push(roleSon);
      }
      this.selRoleId = [...new Set(this.selRoleId)];
    },
    //  获取角色列表
    getRolelist () {
      const params = {
        account: this.account,
      };
      api
        .rolelist(params)
        .then((res) => {
          this.roleList = res.data.data;
        })
        .catch((err) => {
          this.$message.error(err);
        });
    },
    //  上一步
    goBack () {
      this.accessParams();
      this.$router.push({
        path: '/set-second',
      });
    },
    //  点击下一步
    toSetFourth () {
      if (this.selRoleList.length === 0) {
        this.$message.error('请选择角色');
        return;
      }
      this.accessParams();
      const params = this.$locGet('setFirst');
      if (params.role === '1') {
        //  客服
        this.$router.push({
          path: '/set-fourth', // 跳转路由
        });
      } else {
        //  设计
        this.$router.push({
          path: '/set-fourth-design', // 跳转路由set-fourth-design
        });
      }
    },
    accessParams () {
      //  样式 数据存储
      this.$locSet('setThird', {
        selRoleId: this.selRoleId,
        selRoleList: this.selRoleList,
      });
    },
  },
  created () {
    this.getRolelist();
  },
  mounted () {
    this.account = this.$locGet('setFirst').tel;
    const params = this.$locGet('setThird');
    if (params === null) {
      this.selRoleId = [];
      this.selRoleList = [];
    } else {
      this.selRoleId = params.selRoleId;
      this.selRoleList = params.selRoleList;
    }
  },
};
</script>
<style scoped>
/* 头部小标题样式 */
.setThird-title {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.setThird-title > div {
  color: #e94747;
  cursor: pointer;
}
.btns {
  display: flex;
}
/* 取消按钮样式覆盖 */
.btn-back {
  color: #606266;
  border: 1px solid #dcdfe6;
}
.btn-back:hover {
  color: #f56c6c;
  background-color: rgba(255, 239, 239, 1);
  border: 1px solid #f56c6c;
}
.power {
  height: 15px;
  padding: 0 0 0 5px;
  line-height: 15px;
  border-left: 3px solid #e94747;
}
.power-btn {
  display: flex;
  margin: 10px 0;
}
.role-btn {
  height: 20px;
  margin: 0 5px 20px 0;
  padding: 5px 20px;
  color: #000;
  font-size: 14px;
  line-height: 20px;
  text-align: center;
  background: rgba(255, 255, 255, 1);
  border: 1px solid #dee1e7;
  border-radius: 50px;
  cursor: pointer;
}
.sel-btn {
  display: flex;
  align-items: center;
  height: 30px;
  margin: 0 0 30px 0;
}
.sel-allBtn {
  height: 20px;
  margin: 0 5px;
  padding: 5px 20px;
  color: #e94747;
  font-size: 14px;
  line-height: 20px;
  text-align: center;
  background: rgba(255, 236, 237, 1);
  border: 1px solid rgba(233, 71, 71, 1);
  border-radius: 50px;
  cursor: pointer;
}
.on {
  color: #e94747;
  background: rgba(255, 236, 237, 1);
  border: 1px solid rgba(233, 71, 71, 1);
}
.allClass {
  margin: 0 0 30px 0;
  border-bottom: 1px solid rgba(242, 246, 252, 1);
}
/* 确定 取消按钮 */
.reason-button {
  margin: 30px 0 0 0;
  text-align: center;
}
.reason-button > button {
  width: 100px;
}
/* 角色权限说明样式 */
.explain {
  margin: -20px 0 0 0;
  padding: 0 0 40px 0;
  border-top: 1px solid #eee;
  border-bottom: 1px solid #eee;
}
.explain > div > h3 {
  color: #444;
}
.explain > div > div {
  color: #aaa;
}
</style>
