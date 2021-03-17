<template>
  <div class="member-manager">
    <h2><router-link to="teamList" class="team-manager">团队管理</router-link> / 成员管理</h2>
    <el-row class="qian-main">
      <h2 style="font-weight: bold;">团队架构</h2>
      <el-col :span="6" style="padding-right: 25px;">
        <el-tree
          :data="levelData"
          show-checkbox
          empty-text="数据加载中..."
          ref="tree"
          node-key="id"
          :default-expanded-keys="expanded"
          :default-checked-keys="[parseInt(current_id)]"
          :props="defaultProps"
          check-strictly
          @node-click="handleNodeClick"
          @check="handleNodeClick"
        />
      </el-col>
      <el-col :span="18">
        <el-row>
          <el-col :span="3">
            <p>团队名称</p>
            <p>{{ team_name }}</p>
          </el-col>
          <el-col :span="3">
            <p>上级团队</p>
            <p>{{ top_name ? top_name : '销售中心' }}</p>
          </el-col>
          <el-col :span="3">
            <p>负责人</p>
            <p>{{ leader }}</p>
          </el-col>
          <el-col :span="3">
            <p>成员人数</p>
            <p>{{ team_count }}</p>
          </el-col>
          <el-col :span="3">
            <p>状态</p>
            <p>{{ team_status === 1 ? '正常' : '禁用' }}</p>
          </el-col>
          <el-col :span="3" style="margin-top:20px;">
            <el-button type="primary" icon="el-icon-plus" @click="handleAddMember" class="mt4">添加成员</el-button>
          </el-col>
        </el-row>
        <div style="margin:10px 0">
          <span>团队描述：</span>
          <span>{{ description ? description : '暂无描述' }}</span>
        </div>
        <el-tabs v-model="activeName" @tab-click="handleClick">
          <el-tab-pane label="成员查看" name="first">
            <el-table
              v-loading="loading"
              :data="memberData"
              border
            >
              <el-table-column
                align="center"
                label="账号名称"
                prop="user_name"
              >
              </el-table-column>
              <el-table-column
                align="center"
                label="所属角色"
                prop="role_name"
              >
              </el-table-column>
              <el-table-column
                align="center"
                prop="team_name"
                label="所属团队"
              />
              <el-table-column
                align="center"
                label="是否在职"
              >
                <template slot-scope="scope">
                  <span>{{ scope.row.state === 1 ? '在职' : '不在职' }}</span>
                </template>
              </el-table-column>
              <el-table-column
                align="center"
                label="账号状态"
              >
                <template slot-scope="scope">
                  <span :class="scope.row.status === 1 ? 'circle circle_green' : 'circle circle_red'"></span>
                  {{ scope.row.status === 1 ? '已启用' : '已禁用' }}
                </template>
              </el-table-column>
              <el-table-column
                align="center"
                label="操作"
              >
                <template slot-scope="scope">
                  <span class="link-blue pointer" @click="handleEdit(scope.row)">编辑</span>
                  <span class="link-blue pointer" @click="handleRemoveTeam(scope.row.id)">移出团队</span>
                </template>
              </el-table-column>
            </el-table>
          </el-tab-pane>
        </el-tabs>
      </el-col>
    </el-row>
    <el-dialog
      title="添加成员"
      :visible.sync="dialogVisibleAccount"
      center
      width="25%">
      <el-form ref="ruleForm" :model="ruleForm" label-width="90px">
        <el-form-item label="账号名称：" style="margin-top:20px;" prop="account" label-width="80">
          <el-select v-model="ruleForm.account" filterable remote :remote-method="remoteMethod" :loading="sloading" placeholder="请输入" style="margin-top:0;width:75%;">
            <el-option
              v-for="item in options"
              :key="item.id"
              :label="item.full_name"
              :value="item.id">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item style="text-align: center;margin-left:-90px!important">
          <el-button @click="handleCancel('ruleForm')">取消</el-button>
          <el-button type="primary" @click="handleAccountSave">保存</el-button>
        </el-form-item>
      </el-form>
    </el-dialog>
    <el-dialog
      title="编辑成员"
      :visible.sync="dialogVisible"
      center
      width="25%">
      <el-row class="group">
        <el-col :span="12" style="padding-left: 10px;">账号名称：{{ edit_name }}</el-col>
        <el-col :span="12" style="padding-left: 10px;">所属角色：{{ edit_role }}</el-col>
      </el-row>
      <el-form ref="form" :model="form" label-width="90px">
        <el-form-item label="所属团队：" style="margin-top:20px;">
          <el-select v-model="form.teamValue" filterable placeholder="请选择" style="margin-top:0;width:80%;">
            <el-option
              v-for="item in teamData"
              :key="item.id"
              :label="item.team_name"
              :value="item.id">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item style="text-align: center;margin-left:-90px!important">
          <el-button @click="handleCancel('form')">取消</el-button>
          <el-button type="primary" @click="handleSave">保存</el-button>
        </el-form-item>
      </el-form>
    </el-dialog>
  </div>

</template>

<script>
import { fetchMemberList, fetchRemoveTeam, fetchMemberTree, fetchMemberEdit, fetchTopTeamList, fetchTeamPersonList, fetchMemberInfo } from '@/api/teamManager'
export default {
  name: 'MemberList',
  components: {

  },
  data() {
    return {
      levelData: [],
      ruleForm: {
        account: ''
      },
      defaultProps: {
        children: 'children',
        label: 'name'
      },
      activeName: 'first',
      memberData: [],
      options: [],
      teamData: [],
      expanded: [],
      checked: [],
      form: {
        teamValue: ''
      },
      id: '',
      current_id: '',
      team_id: '',
      user_id: '',
      select_id: '',
      team_name: '',
      team_count: '',
      team_status: '',
      leader: '',
      top_name: '',
      description: '',
      edit_name: '',
      edit_role: '',
      loading: false,
      sloading: false,
      dialogVisible: false,
      dialogVisibleAccount: false
    }
  },
  watch: {

  },
  mounted() {
    this.team_id = this.$route.query.id
    this.current_id = this.$route.query.id
    this.$nextTick(function() {
      this.$refs.tree.setCurrentKey(parseInt(this.current_id))
    })
    this.fetchMemberList()
    this.fetchMemberTree()
  },
  created() {

  },
  methods: {
    remoteMethod(query) {
      if (query !== '') {
        this.sloading = true
        fetchTeamPersonList({
          cid: '',
          uid: query,
          psize: '',
          status: 1
        }).then(res => {
          if (res.status === 200 && res.data.error_code === 0) {
            const _this = this
            setTimeout(() => {
              _this.sloading = false
              _this.options = res.data.data[0]
            }, 200)
          }
        })
      } else {
        this.options = []
      }
    },
    handleNodeClick(data) {
      this.$refs.tree.setCheckedKeys([])   // 清空所有的选项
      if (Number(data.id) === 0) {
        this.team_id = this.$route.query.id
      } else {
        this.team_id = data.id
      }
      this.current_id = Number(data.id)
      this.fetchMemberList()
    },
    handleClick(tab, event) {
      console.log(event)
      console.log(tab)
    },
    // 获取团队list
    fetchTopTeamList(obj) {
      const _this = this
      fetchTopTeamList().then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          _this.teamData = res.data.data
          _this.teamData.forEach(function(index, item) {
            if (index.team_name === obj.team_name) {
              _this.form.teamValue = index.id
              _this.select_id = index.id
            }
          })
        }
      })
    },
    // 添加成员
    handleAddMember() {
      this.dialogVisibleAccount = true
      this.options = []
    },
    // 编辑
    handleEdit(obj) {
      this.user_id = obj.id
      this.edit_name = obj.user_name
      this.edit_role = obj.role_name
      this.dialogVisible = true
      this.id = obj.id
      this.fetchTopTeamList(obj)
    },
    // 成员查看列表
    fetchMemberList() {
      fetchMemberList({
        team_id: this.team_id
      }).then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          this.team_name = res.data.data.team_name
          this.team_count = res.data.data.team_count
          this.team_status = res.data.data.team_status
          this.leader = res.data.data.leader
          this.top_name = res.data.data.top_name
          this.description = res.data.data.description
          this.memberData = res.data.data.users
          this.totals = res.data.data.users.length
        }
      })
    },
    fetchMemberTree() {
      fetchMemberTree({
        team_id: this.team_id
      }).then(res => {
        this.levelData.push(res.data.data)
        this.checked = res.data.data.checked
        if (res.data.data.expand.length <= 0) {
          this.expanded = [0]
        } else {
          this.expanded = res.data.data.expand
        }
      })
    },
    // 编辑
    fetchMemberEdit() {
      fetchMemberEdit({
        id: this.user_id,
        team_id: this.form.teamValue
      }).then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          this.$message({
            type: 'success',
            message: '保存成功!'
          })
          this.dialogVisible = false
          this.fetchMemberList()
        } else {
          this.$message.error(res.data.error_msg)
        }
      })
    },
    // 移出团队
    handleRemoveTeam(id) {
      this.$confirm('确认要移出该成员吗？', '移出团队确认', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
        center: true
      }).then(() => {
        fetchRemoveTeam({
          id: id
        }).then(res => {
          if (res.data.error_code === 0 && res.status === 200) {
            this.$message({
              type: 'success',
              message: '移出成功!'
            })
            this.fetchMemberList()
          } else {
            this.$message.error(res.data.error_msg)
          }
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: '取消移出'
        })
      })
    },
    handleCancel(formName) {
      this.$refs[formName].resetFields()
      this.dialogVisible = false
      this.dialogVisibleAccount = false
      this.ruleForm.account = ''
      this.form.teamValue = ''
    },
    handleCommon() {
      fetchMemberInfo({
        id: this.ruleForm.account
      }).then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          if (res.data.data) {
            if (this.ruleForm.account !== res.data.data.top_id) {
              this.$confirm(res.data.data.user_name + '为' + res.data.data.team_name + '团成员，确定要更改团队吗？该操作将被记录，确定后从该账号原团队移出，添加到现团队中', '更换团队提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning',
                center: true
              }).then(() => {
                this.changeTeamSave()
              }).catch(() => {
                this.$message({
                  type: 'info',
                  message: '取消保存'
                })
              })
            }
          } else {
            this.changeTeamSave()
          }
        } else {
          this.changeTeamSave()
        }
      })
    },
    handleAccountSave() {
      this.handleCommon()
    },
    changeTeamSave() {
      fetchMemberEdit({
        id: this.ruleForm.account,
        team_id: this.team_id
      }).then(res => {
        if (res.status === 200 && res.data.error_code === 0) {
          this.$message({
            type: 'success',
            message: '保存成功!'
          })
          this.dialogVisibleAccount = false
          this.ruleForm.account = ''
          this.fetchMemberList()
        } else {
          this.$message.error(res.data.error_msg)
        }
      })
    },
    handleSave() {
      if (this.select_id === this.form.teamValue) {
        this.fetchMemberEdit()
      } else {
        this.$confirm('确认要更改该成员团队吗？该操作将导致成员团队变更', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning',
          center: true
        }).then(() => {
          this.fetchMemberEdit()
        }).catch(() => {
          this.$message({
            type: 'info',
            message: '取消保存'
          })
        })
      }
    }
  }
}
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
  .member-manager{
    width: 100%;
    padding: 20px;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
  }
  .team-manager{
    font-weight: bold;
    color:#0099FF;
  }
  .member-manager h2{
    font-size: 16px;
    font-weight: normal;
    color: #666;
    margin-top:0;
  }
  .member-manager .qian-main {
    margin-top: 20px;
    padding: 20px;
    border-top: 3px solid #999;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    background-color: #fff;
  }
  .el-tabs__item{
    font-size: 16px!important;
  }
  .qian-main .el-col p:nth-child(2){
    font-weight: bold;
  }
  /deep/ .el-tree-node__label{
    font-size: 16px!important;
  }
  /deep/ #tab-first{
    font-size: 16px;
  }
  .el-pagination{
    text-align: center;
    margin-top: 20px;
  }
</style>
