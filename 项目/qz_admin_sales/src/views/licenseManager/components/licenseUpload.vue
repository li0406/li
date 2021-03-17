<template>
  <div class="license-uolpad">
    <div class="header">
      <div class="mr20 fl">
        公司名称/ID：<br>
        <el-autocomplete
          v-model="company"
          :fetch-suggestions="querySearchId"
          placeholder="请输入公司名称或ID"
          @select="handleIdSelect"
        ></el-autocomplete>
      </div>
      <div class="mr20 fl">
        <span> </span><br>
        <el-button type="primary" @click="handlequery">查询</el-button>
      </div>
    </div>
    <div class="main">
      <el-table
        :data="tableData"
        v-loading="loading"
        border
        style="width: 100%">
        <el-table-column
          prop="id"
          label="ID"
          align="center"
          width="120">
        </el-table-column>
        <el-table-column
          prop="qc"
          label="公司名称"
          align="center">
        </el-table-column>
        <el-table-column
          prop="user_on"
          align="center"
          label="是否会员">
          <template slot-scope="scope">
            {{ parseInt(scope.row.user_on) === 1 ? "否" : "是" }}
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
          align="center"
          label="营业执照"
          width="120">
          <template slot-scope="scope">
            <el-button type="primary" size="mini" v-if="scope.row.child_1" @click="preview(scope.row, 'child_1')">预览</el-button>
            <el-button type="primary" size="mini" v-else @click="uploadAction(scope.row, 'child_1')">上传</el-button>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="装修资质"
          width="120">
          <template slot-scope="scope">
            <el-button type="primary" size="mini" v-if="scope.row.child_3" @click="preview(scope.row, 'child_3')">预览</el-button>
            <el-button type="primary" size="mini" v-else @click="uploadAction(scope.row, 'child_3')">上传</el-button>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="操作"
          width="160">
          <template slot-scope="scope">
            <el-button type="primary" size="mini" @click="showClsOrSitDia(scope.row, 'clear')">清空</el-button>
            <el-button type="primary" size="mini" @click="showClsOrSitDia(scope.row, 'submit')">提交</el-button>
          </template>
        </el-table-column>
      </el-table>
      <!-- 分页 -->
      <el-pagination
        class="page"
        @current-change="handleCurrentChange"
        :current-page.sync="currentPage"
        :page-size="20"
        layout="total, prev, pager, next, jumper"
        :total="totals">
      </el-pagination>
      <!--图片预览-->
      <el-dialog
        :visible.sync="dialogVisiblePreview"
        :close-on-click-modal="false"
        width="60%">
        <div class="img" v-for="item in previewTemp" :key="item"><img :src="item" alt="" style="max-width: 100%; max-height: 100%; display: block; margin: 0 auto;"></div>
      </el-dialog>
      <!--清空/提交-->
      <el-dialog
        :title="clsOrSitTitle"
        :visible.sync="dialogVisibleClsOrSit"
        width="30%"
        @close="handleClose">
        <span>{{ clsOrSitDesc }}</span>
        <span slot="footer" class="dialog-footer">
          <el-button @click="handleClose">取 消</el-button>
          <el-button type="primary" @click="handleToServer">确 定</el-button>
        </span>
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
  </div>
</template>

<script>
  import { fetchFindUser } from '@/api/memberDeadline'
  import { fetchLicenseUploadList, fetchclearLicense, fetchUploadLicense } from '@/api/licenseManager'
  import { fetchCityList } from '@/api/common'
  export default {
    name: 'licenseUpload',
    props: {
      startTag: {
        type: String,
        default: ''
      }
    },
    data() {
      return {
        tableData: [],
        loading: false,
        currentPage: 1,
        totals: 0,
        dialogVisiblePreview: false,
        previewTemp: [],
        tempData: null,
        tempTag: '',
        dialogVisibleClsOrSit: false,
        clsOrSitTitle: '清空',
        clsOrSitDesc: '是否确认清空上传的图片？',
        dialogVisibleUpload: false,
        citys: [],
        cs: '',
        cid: '',
        zxComs: [],
        timeout:  null,
        cityBlurFlag: null,
        comBlurFlag: null,
        restaurantsCity: [],
        company: '',
        companyid: '',
        tempCurrentId: '',
        tempUploadTag: '',
        uploadedImg: '',
        uploadExtendData: {
          prefix: 'yyzz'
        },
        uploadContentType: {
          'token': localStorage.getItem('token')
        },
        upload_img_url: this.$qzconfig.base_api + '/uploads/upimg'
      }
    },
    created() {
      // this.getUploadList()
    },
    watch: {
      // startTag(val) {
      //   this.getUploadList(val)
      // }
    },
    methods: {
      querySearchId(queryString, cb) {
        this.comBlurFlag = null
        this.searchUser(queryString, () => {
          clearTimeout(this.timeout)
          this.timeout = setTimeout(() => {
            cb(this.zxComs)
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
      getDataList(val) {
        if(val === 'fourth') {
          this.getUploadList()
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
      showClsOrSitDia(obj, tag) {
        // 要先判断营业执照和装修资质是否都未上传，如果是给提示
        console.log(obj)
        if(!obj.child_1 && !obj.child_3) {
          this.$message.error('营业执照/装修资质至少上传一个')
          return
        }
        this.dialogVisibleClsOrSit = true
        this.tempData = obj
        this.tempTag = tag
        if(this.tempTag === 'clear') {
          this.clsOrSitTitle = '清空'
          this.clsOrSitDesc = '是否确认清空上传的图片？'
        }else if(this.tempTag === 'submit') {
          this.clsOrSitTitle = '提交'
          this.clsOrSitDesc = '确认提交上传的图片？'
        }
      },
      handleToServer() {
        if(this.tempTag === 'clear') {
          this.clearInfo()
        }else if(this.tempTag === 'submit') {
          this.submitInfo()
        }
      },
      clearInfo() {
        fetchclearLicense({
          company_id: this.tempData.id
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              message: '清空成功',
              type: 'success'
            })
            this.dialogVisibleClsOrSit = false
            this.getUploadList()
          }else{
            this.$message.error(res.data.error_msg)
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
        })
      },
      submitInfo() {
        let img1 = ''
        let img3 = ''
        if(this.tempData.child_1) {
          if(this.tempData.child_1.img1) {
            img1 = this.tempData.child_1.img1
          }else{
            img1 = this.tempData.child_1[0]
          }
        }
        if(this.tempData.child_3) {
          if(this.tempData.child_3.img1) {
            img3 = this.tempData.child_3.img1
          }else{
            img3 = this.tempData.child_3[0]
          }
        }
        fetchUploadLicense({
          company_id: this.tempData.id,
          img1: img1,
          img3: img3,
          img4: ''
        }).then(res => {
          if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              message: '提交成功',
              type: 'success'
            })
            this.dialogVisibleClsOrSit = false
          }else{
            this.$message.error(res.data.error_msg)
          }
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
        })
      },
      getUploadList(val, cb) {
        fetchLicenseUploadList({
          user_keyword: this.companyid,
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
      handleUploadSuccess(res, file) {
        this.uploadedImg = res.data.img_path
      },
      uploadAction(obj, tag) {
        this.dialogVisibleUpload = true
        this.tempCurrentId = obj.id
        this.tempUploadTag = tag
      },
      closeUploadDia() {
        this.$refs.certUpload.clearFiles()
        this.dialogVisibleUpload = false
      },
      uploadSave() {
        // if(!this.uploadedImg) {
        //   this.$message.error('图片上传失败，请删除重新上传')
        //   return
        // }
        this.tableData.forEach(item => {
          if(this.tempCurrentId === item.id) {
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
        this.$refs.certUpload.clearFiles()
        this.dialogVisibleUpload = false
      },
      handlequery() {
        this.tableData = []
        this.loading = true
        this.currentPage = 1
        this.getUploadList()
        if (this.tableData = []) {
          this.totals = 0
        }
      },
      handleCurrentChange(val) {
        this.currentPage = val
        this.loading = true
        this.getUploadList()
      },
      handleClose() {
        this.dialogVisibleClsOrSit = false
      }
    }
  }
</script>

<style scoped>
  .mr20 {
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
</style>
