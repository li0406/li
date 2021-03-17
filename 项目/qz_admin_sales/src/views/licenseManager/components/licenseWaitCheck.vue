<template>
  <div class="license-wait-check">
    <div class="header">
      <div class="mr20 fl">
        公司名称/ID：<br>
        <el-autocomplete
          v-model="company"
          :fetch-suggestions="querySearchId"
          placeholder="请输入公司名称或ID"
          @select="handleIdSelect"
          @blur="handleComBlur"
        ></el-autocomplete>
      </div>
      <div class="mr20 fl">
        城市负责人：<br>
        <el-select v-model="cityManager" filterable placeholder="请选择城市负责人" clearable>
          <el-option
            v-for="item in cityManagerOption"
            :key="item.id"
            :label="item.name"
            :value="item.id"
          />
        </el-select>
      </div>
      <div class="mr20 fl">
        城市：<br>
        <el-select v-model="cs" filterable placeholder="城市" clearable>
          <el-option
            v-for="item in citysOption"
            :key="item.cid"
            :label="item.cname"
            :value="item.cid"
          />
        </el-select>
      </div>
      <div class="mr20 fl">
        <span> </span><br>
        <el-button type="primary" @click="handleQuery">查询</el-button>
      </div>
    </div>
    <div class="main">
      <!-- 列表 -->
      <el-table
        :data="tableData"
        v-loading="loading"
        border
        style="width: 100%">
        <el-table-column
          prop="company_id"
          label="ID"
          align="center"
          width="120">
        </el-table-column>
        <el-table-column
          label="公司名称"
          align="center">
          <template slot-scope="scope">
            {{ scope.row.qc ? scope.row.qc : '----' }}
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="是否会员">
          <template slot-scope="scope">
            {{ parseInt(scope.row.user_on) === 2 ? '是' : '否' }}
          </template>
        </el-table-column>
        <el-table-column
          prop="cname"
          align="center"
          label="城市">
        </el-table-column>
        <el-table-column
          prop="city_manager"
          align="center"
          label="城市负责人">
        </el-table-column>
        <el-table-column
          prop="type_name"
          align="center"
          label="审核项目">
        </el-table-column>
        <el-table-column
          align="center"
          label="查看"
          width="120">
          <template slot-scope="scope">
            <el-button type="primary" size="mini" @click="preview(scope.row, 'child')">点击预览</el-button>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="工商总局查询"
          width="120">
          <template slot-scope="scope">
            <div v-if="scope.row.type_name === '装修资质'">---</div>
            <div v-else>
              <el-button type="primary" size="mini" v-if="!scope.row.gszj" @click="uploadAction(scope.row, 'gszj')">上传</el-button>
              <el-button type="primary" size="mini" v-else @click="preview(scope.row, 'gszj')">预览</el-button>
            </div>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="操作"
          width="120">
          <template slot-scope="scope">
            <el-button type="primary" size="mini" @click="auditLicense(scope.row)">审核</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <el-pagination
        class="page"
        @current-change="handleCurrentChangea"
        :current-page.sync="currentPage"
        :page-size="20"
        layout="total, prev, pager, next, jumper"
        :total="totals">
      </el-pagination>
    </div>
    <!--图片预览-->
    <el-dialog
      :visible.sync="dialogVisiblePreview"
      :close-on-click-modal="false"
      width="60%"
    >
      <div class="img" v-for="item in previewTemp" :key="item"><img :src="item" alt="" style="max-width: 100%; max-height: 100%; display: block; margin: 0 auto;"></div>
    </el-dialog>
    <!-- 审核弹框 -->
    <el-dialog :visible.sync="dialogVisibleaudit" :close-on-click-modal="false" width="400px">
      <el-form :model="ruleForm" :rules="rules" ref="ruleForm" class="demo-ruleForm">
        <el-form-item label="审核" prop="resource" :label-width="formLabelWidth">
          <el-radio-group v-model="ruleForm.resource">
            <el-radio label="1">通过</el-radio>
            <el-radio label="2">不通过</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="备注" prop="desc" :label-width="formLabelWidth">
          <el-input type="textarea" v-model="ruleForm.desc"></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogVisibleaudit = false">取 消</el-button>
        <el-button type="primary" @click="submitAudit('ruleForm')">确 定</el-button>
      </div>
    </el-dialog>
    <!-- 上传弹框 -->
    <el-dialog
      :visible.sync="dialogVisibleUpload"
      @close="closeUploadDia"
      width="30%">
      <el-upload
        ref="certUpload"
        class="upload-demo"
        :action="upload_img_url"
        :limit="1"
        :data="uploadExtendData"
        :headers="uploadContentType"
        :on-success="handleUploadSuccess"
        drag
        list-type="picture">
        <div class="el-upload__text"><em>点击上传</em></div>
      </el-upload>
      <span slot="footer" class="dialog-footer">
        <el-button @click="closeUploadDia">取 消</el-button>
        <el-button type="primary" @click="uploadSave">确 定</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
  import { fetchFindUser } from '@/api/memberDeadline'
  import { fetchLicenseList, fetchLicenseAuditWait, fetchUploadLicense, fetchCityManager } from '@/api/licenseManager'
  import { fetchCityList } from '@/api/common'
  export default {
    name: 'licenseWaitCheck',
    props: {
      startTag: {
        type: String,
        default: ''
      }
    },
    data() {
      return {
        previewTemp: [],
        tempMemberInfo: null,
        tableData: [],
        loading: true,
        dialogVisiblePreview: false,
        dialogVisibleup: false,
        totals: 0,
        currentPage: 1,
        dialogVisibleaudit: false,
        cityManager: '',
        cityManagerOption: [],
        dialogVisibleUpload: false,
        citys: [],
        cs: '',
        cid: '',
        zxComs: [],
        timeout:  null,
        cityBlurFlag: null,
        comBlurFlag: null,
        company: '',
        companyid: '',
        citysOption: [],
        tempObj: null,
        tempCurrentId: '',
        tempUploadTag: '',
        uploadedImg: '',
        uploadExtendData: {
          prefix: 'yyzz'
        },
        uploadContentType: {
          'token': localStorage.getItem('token')
        },
        // 审核表单
        ruleForm: {
          resource: '1',
          desc: ''
        },
        formLabelWidth: '50px',
        // 表单验证
        rules: {
          resource: [
            { required: true, message: '请选择是否通过', trigger: 'change' }
          ]
        },
        upload_img_url: this.$qzconfig.base_api + '/uploads/upimg'
      }
    },
    created() {
      this.getWaitCheckList()
      this.querySearchCity()
      this.querySearchcityManager()
    },
    mounted() {},
    watch: {
      startTag(val) {
        console.log(val)
        this.getDataList(val)
      },
      cs(value) {
        if(!value){
          this.cid = ''
          this.cityBlurFlag = null
        }
      },
      company(value) {
        if(!value) {
          this.companyid = ''
          this.comBlurFlag = null
        }
      }
    },
    methods: {
      querySearchId(queryString, cb) {
        this.comBlurFlag = null
        this.searchUser(queryString, () => {
          clearTimeout(this.timeout)
          this.timeout = setTimeout(() => {
            cb && cb(this.zxComs)
          }, 1000)
        })
      },
      searchUser(query, cb) {
        fetchFindUser({ uid: query }).then(res => {
          const data = res.data.data[0]
          this.zxComs = data
          cb && cb.call()
        })
      },
      handleIdSelect(item) {
        this.comBlurFlag = item
        this.companyid = item.id
      },
      handleComBlur() {
        if(!this.comBlurFlag) {
          this.companyid = ''
          this.company = ''
        }
      },
      querySearchCity() {
        fetchCityList().then(res =>{
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            const data = res.data.data[0]
            data.forEach((item, index) => {
              this.citysOption.push(item)
            })
          } else {
            this.citys = []
          }
        })
      },
      createStateFiltercity(queryString) {
        return (cs) => {
          return (cs.value.toLowerCase().indexOf(queryString.toLowerCase()) > -1);
        };
      },
      handleBlur() {
        if(!this.cityBlurFlag) {
          this.cid = ''
        }
      },
      handleCitySelect(item) {
        this.cityBlurFlag = null
        this.cid = item.cid
      },
      querySearchcityManager() {
        fetchCityManager({
          user_keyword: ''
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.cityManagerOption = res.data.data.list
          }
        })
      },
      handlecityManagerSelect() {},
      getDataList(val) {
        if(val === 'first') {
          this.getWaitCheckList()
        }
      },
      preview(obj, tag) {
        const tempData = obj[tag]
        this.previewTemp = []
        for(let p in tempData) {
          if(tempData[p]) {
            this.previewTemp.push(this.$qzconfig.oss_img_host + tempData[p])
          }
        }
        this.dialogVisiblePreview = true
      },
      auditLicense(obj) {
        this.tempMemberInfo = obj
        this.dialogVisibleaudit = true
      },
      getWaitCheckList(val, cb) {
        fetchLicenseList({
          actfrom: 'examine',
          company_keyword: this.companyid,
          city_manager: this.cityManager,
          cs: this.cs,
          page: this.currentPage
        }).then(res =>{
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            if (res.data.data.list.length > 0) {
              this.tableData = res.data.data.list
              this.totals = res.data.data.page.total_number
              cb && cb.call()
              this.loading = false
            }else{
              this.$message.error('未查询到符合要求的数据')
              this.loading = false
            }
          }else{
            this.$message.error(res.data.error_msg)
            this.tableData = []
            this.loading = false
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
          this.loading = false
        })
      },
      submitAudit(formName) {
        this.$refs[formName].validate((valid) => {
          if (valid) {
            this.ajaxAuditInfo()
          } else {
            console.log('error submit!!')
            return false
          }
        })
      },
      ajaxAuditInfo() {
        fetchLicenseAuditWait({
          company_id: this.tempMemberInfo.company_id,
          type: this.tempMemberInfo.type,
          state: this.ruleForm.resource,
          remark: this.ruleForm.desc
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              message: '提交成功',
              type: 'success'
            })
            this.getWaitCheckList()
          }else{
            this.$message.error(res.data.error_msg)
          }
          this.dialogVisibleaudit = false
          this.ruleForm.resource = '1'
          this.ruleForm.desc = ''
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
          this.dialogVisibleaudit = false
        })
      },
      handleUploadSuccess(res, file) {
        this.uploadedImg = res.data.img_path
      },
      uploadAction(obj, tag) {
        this.dialogVisibleUpload = true
        this.tempCurrentId = obj.company_id
        this.tempUploadTag = tag
        this.tempObj = obj
      },
      closeUploadDia() {
        this.$refs.certUpload.clearFiles()
        this.dialogVisibleUpload = false
      },
      uploadSave() {
        this.tableData.forEach(item => {
          if(this.tempCurrentId === item.company_id) {
            if(!item[this.tempUploadTag]) {
              item[this.tempUploadTag] = []
            }
            if(item[this.tempUploadTag].length > 0) {
              item[this.tempUploadTag][0] = this.uploadedImg
            }else{
              item[this.tempUploadTag].push(this.uploadedImg)
            }
          }
        })
        this.dialogVisibleUpload = false
        this.submitCert()
      },
      submitCert() {
        fetchUploadLicense({
          company_id: this.tempCurrentId,
          img1: this.tempObj.child.img1,
          img3: '',
          img4: this.tempObj.gszj ? this.tempObj.gszj[0] : '',
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              message: '提交成功',
              type: 'success'
            })
          }else{
            this.$message.error(res.data.error_msg)
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
        })
      },
      handleQuery() {
        this.tableData = []
        this.loading = true
        this.currentPage = 1
        this.getWaitCheckList()
      },
      handleCurrentChangea(val) {
        this.currentPage = val
        this.loading = true
        this.getWaitCheckList()
      }
    }
  }
</script>

<style>
  .license-check .mr20 {
    margin-right: 20px;
  }
  .license-check .header {
    background-color: #fff;
    padding: 20px;
    box-sizing: border-box;
    height: 100px;
    border-top: 3px solid #d2d6de;
  }
  .license-check .main {
    margin-top: 20px;
    background-color: #fff;
    padding: 20px 20px 60px 20px;
    border-top: 3px solid #d2d6de;
    position: relative;
  }
  .license-check .main .page {
    position: absolute;
    left: 50%;
    bottom: 10px;
    transform: translateX(-50%);
  }
  .license-check .el-dialog__body{height: 70vh;overflow: auto;}
</style>
