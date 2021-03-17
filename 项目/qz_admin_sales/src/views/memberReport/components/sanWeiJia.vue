<template>
    <div class="san-wei-jia">
        <el-form :model="ruleForm" :rules="rules" v-loading="loading" ref="ruleForm" label-width="100px" class="demo-ruleForm">
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>1、公司名称：</div></el-col>
                <el-col :span="18">
                    <el-form-item prop="companyName">
                        <el-input v-if="unCheckEditTag" v-model="ruleForm.companyName" placeholder="请输入" :disabled="true"></el-input>
                        <el-input v-else v-model="ruleForm.companyName" :maxlength="30" placeholder="请输入"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>2、合作类型：</div></el-col>
                <el-col :span="18">
                    <el-input v-model="memberType" :disabled="true"></el-input>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>3、部门：</div></el-col>
                <el-col :span="18">
                    <el-form-item prop="department">
                        <el-input v-model="ruleForm.department" :disabled="true"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>4、姓名：</div></el-col>
                <el-col :span="18">
                    <el-form-item prop="name">
                        <el-input v-model="ruleForm.name" :maxlength="6" placeholder="请输入"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>5、电话：</div></el-col>
                <el-col :span="18">
                    <el-form-item prop="phone">
                        <el-input v-model="ruleForm.phone" placeholder="请输入"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>6、账号：</div></el-col>
                <el-col :span="18">
                    <el-form-item prop="account">
                        <el-input v-model="ruleForm.account" :maxlength="20" placeholder="请输入"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>7、金额：</div></el-col>
                <el-col :span="18">
                    <el-form-item prop="money">
                        <el-input v-if="unCheckEditTag" v-model="ruleForm.money" placeholder="请输入" :disabled="true"></el-input>
                        <el-input v-else v-model="ruleForm.money" :maxlength="7" placeholder="请输入"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>8、角色：</div></el-col>
                <el-col :span="18">
                    <el-form-item prop="role">
                        <el-input v-model="ruleForm.role" :disabled="true"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>9、标签：</div></el-col>
                <el-col :span="18">
                    <el-form-item prop="tag">
                        <el-input v-model="ruleForm.tag" :disabled="true"></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>10、时间：</div></el-col>
                <el-col :span="18">
                    <div v-if="unCheckEditTag">
                        <el-form-item prop="timeBegin" class="inline-block">
                            <el-date-picker
                                v-model="ruleForm.timeBegin"
                                type="date"
                                placeholder="开始日期"
                                :disabled="true"
                            >
                            </el-date-picker>
                        </el-form-item>
                        <el-form-item prop="timeEnd" class="inline-block">
                            <el-date-picker
                                v-model="ruleForm.timeEnd"
                                type="date"
                                placeholder="结束日期"
                                disabled
                            >
                            </el-date-picker>
                        </el-form-item>
                    </div>
                    <div v-else>
                        <el-form-item prop="timeBegin" class="inline-block">
                            <el-date-picker
                                v-model="ruleForm.timeBegin"
                                type="date"
                                placeholder="开始日期"
                            >
                            </el-date-picker>
                        </el-form-item>
                        <el-form-item prop="timeEnd" class="inline-block">
                            <el-date-picker
                                v-model="ruleForm.timeEnd"
                                type="date"
                                placeholder="结束日期"
                                disabled
                            >
                            </el-date-picker>
                        </el-form-item>
                    </div>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">11、备注：</div></el-col>
                <el-col :span="18">
                    <el-input v-model="ruleForm.note" type="textarea" :rows="5" :maxlength="500" placeholder="请输入"></el-input>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">12、是否需要客服传凭：</div></el-col>
                <el-col :span="18">
                    <el-select v-model="ruleForm.kfVoucher" placeholder="请选择">
                        <el-option
                            v-for="item in kfVoucherOptions"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value">
                        </el-option>
                    </el-select>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">13、上传图片：</div></el-col>
                <el-col :span="18">
                    <el-upload
                        ref="certUpload"
                        class="upload-demo"
                        :action="upload_img_url"
                        :limit="20"
                        :data="uploadExtendData"
                        :headers="uploadContentType"
                        :on-success="handleUploadSuccess"
                        :file-list="fileList"
                        :on-remove="handleRemove"
                        drag
                        list-type="picture">
                        <div class="el-upload__text"><em>点击上传</em></div>
                    </el-upload>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">14、客服上传图片：</div></el-col>
                <el-col :span="18">
                    <el-col :span="5" v-for='item in kfuploadImg' :key="item">
                        <img :src="item" alt="" class="upimg" @click="preview(item)">
                    </el-col>
                </el-col>
            </el-row>
            <!--图片预览-->
            <el-dialog
                :visible.sync="dialogVisiblePreview"
                :close-on-click-modal="false"
                width="60%"
            >
            <div class="img" v-for="item in previewTemp" :key="item"><img :src="item" alt="" style="max-width: 100%; max-height: 100%; display: block; margin: 0 auto;"></div>
            </el-dialog>
            <el-row>
                <el-button type="primary" @click="handleSave('ruleForm')">保存</el-button>
                <el-button type="success" v-if="!unCheckEditTag" @click="handleSubmit('ruleForm')">提交</el-button>
                <el-button @click="handleCancel">取消</el-button>
            </el-row>
            <el-row>
                <p class="red">提示：提交后将推送至副总裁审核</p>
            </el-row>
        </el-form>
    </div>
</template>

<script>
    import { fetchMemberReportAdd, fetchMemberReportSup } from '@/api/memberReport'
    import { checkPhoneNum } from '@/utils/index'
    import { fortmatDate, compareTime } from '@/utils/index'
    import memberReportDetail from '@/mixins/memberReport'
    import QZ_CONFIG from '@/utils/config'
    export default {
        name: "sanWeiJia",
        props: {
            memberType: {
                type: String,
                default: ''
            },
            memberTypeId: {
                type: String,
                default: ''
            },
            operationActionTag: {
                type: String,
                default: ''
            }
        },
        mixins: [memberReportDetail],
        data() {
            const validatePhone = (rule, value, callback) => {
                if (!value) {
                    callback(new Error('请输入电话'))
                } else if(!checkPhoneNum(value)) {
                    callback(new Error('请输入正确的手机号'))
                } else {
                    callback()
                }
            }
            const validateInt = (rule, value, callback) => {
                const reg = /^[+]{0,1}(\d+)$/
                if (!reg.test(value)) {
                    callback(new Error('请输入正整数'))
                } else {
                    callback()
                }
            }
            const validateLetter = (rule, value, callback) => {
                const reg = /^[A-Za-z]+$/
                console.log(!reg.test(value))
                if (!reg.test(value)) {
                    callback(new Error('账号仅支持字母'))
                } else {
                    callback()
                }
            }
            return {
                id: '',
                ruleForm: {
                    id: '',
                    cooperation_type: '',
                    status: '',
                    companyName: '',
                    department: '运营部管理',
                    name: '',
                    phone: '',
                    account: '',
                    money: '',
                    role: '门店老板',
                    tag: '付费账号',
                    time: '',
                    timeBegin: '',
                    timeEnd: '',
                    note: '',
                    kfVoucher: '0'
                },
                rules: {
                    companyName: [
                        {required: true, message: '请输入公司名称', trigger: 'change'}
                    ],
                    department: [
                        {required: true, message: '请输入部门', trigger: 'change'}
                    ],
                    name: [
                        { required: true, message: '请输入姓名', trigger: 'change' }
                    ],
                    phone: [
                        { validator: validatePhone, trigger: 'change' }
                    ],
                    account: [
                        { required: true, message: '请输入账号', trigger: 'change' },
                        { validator: validateLetter, trigger: 'change' }
                    ],
                    money: [
                        { required: true, message: '请输入金额', trigger: 'change' },
                        { validator: validateInt, trigger: 'change' }
                    ],
                    role: [
                        { required: true, message: '请输入角色', trigger: 'change' }
                    ],
                    tag: [
                        { required: true, message: '请输入标签', trigger: 'change' }
                    ],
                    timeBegin: [
                        { required: true, message: '请选择开始时间', trigger: 'change' }
                    ],
                    timeEnd: [
                        { required: true, message: '请选择结束时间', trigger: 'change' }
                    ]
                },
                submitStatus: 1, // 保存状态为1，提交状态为2
                unCheckEditTag: false,
                loading: false,
                uploadExtendData: {
                    prefix: 'sale_report'
                },
                uploadedImg: [],
                kfuploadImg: [],
                previewTemp: [],
                dialogVisiblePreview: false,
                uploadContentType: {
                'token': localStorage.getItem('token')
                },
                kfVoucherOptions: [{
                    value: '0',
                    label: '否'
                },{
                    value: '1',
                    label: '是'
                }],
                fileList: [],
                upload_img_url: this.$qzconfig.base_api + '/uploads/upimg'
            }
        },
        watch: {
            'ruleForm.timeBegin'(val) {
                let begin = (new Date(val).getTime())/1000 + 31622400-86400
                let xbegin = new Date(begin*1000);
                this.ruleForm.timeEnd = xbegin
                if(this.ruleForm.timeEnd) {
                    if(!compareTime(val, this.ruleForm.timeEnd)){
                        this.$message.error('开始时间不能大于结束时间')
                        this.ruleForm.timeBegin = ''
                    }
                }
            },
            'ruleForm.timeEnd'(val) {
                if(this.ruleForm.timeBegin) {
                    if(!compareTime(this.ruleForm.timeBegin, val)) {
                        this.$message.error('结束时间不能小于开始时间')
                        this.ruleForm.timeEnd = ''
                    }
                }
            },
        },
        created() {
            // 根据编辑还是添加标识符确定是否需要请求数据
            if(this.operationActionTag === 'edit') {
                this.id = this.$route.query.id
                this.getMemberReportDetail()
                if(this.$route.query && this.$route.query.status && parseInt(this.$route.query.status) === 6) {
                    this.unCheckEditTag = true
                }
                this.$nextTick(() => {
                    const el = document.getElementsByClassName('tags-view-item')[0]
                    el.innerHTML = el.innerHTML.replace('添加', '编辑')
                })
            }
            const smallReport = JSON.parse(localStorage.getItem('smallReport'))
            if (smallReport) {
                this.ruleForm.money = String(smallReport.payment_money)
            }

        },
        methods: {
            submitForm(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.handleAjax()
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            handleAjax() {
                const queryObj = this.handleArguments()
                this.loading = true

                fetchMemberReportAdd(queryObj).then(res => {
                    if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                        this.$message({
                            type: 'success',
                            message: '保存成功'
                        })
                        setTimeout(() => {
                            this.$router.push('/memberReport/list')
                        }, 1000)
                    }else{
                        this.$message.error(res.data.error_msg)
                    }
                    this.loading = false
                }).catch(res => {
                    this.$message.error('网络异常，请稍后再试')
                    this.loading = false
                })
            },
            handleArguments() {
                if(this.unCheckEditTag) {
                    // 当状态等于6，即”客服未通过，普通信息更改“，需要手动将提交的状态码改成5
                    this.submitStatus = 3
                }
                const uploadedImg = this.uploadedImg.join(',')
                const queryObj = {
                    id: this.id,
                    company_name: this.ruleForm.companyName,
                    cooperation_type: this.memberTypeId,
                    section: this.ruleForm.department,
                    account: this.ruleForm.account,
                    current_contract_amount: this.ruleForm.money,
                    company_rolers: this.ruleForm.role,
                    company_tag: this.ruleForm.tag,
                    company_contact: this.ruleForm.name,
                    company_contact_phone: this.ruleForm.phone,
                    current_contract_start: fortmatDate(this.ruleForm.timeBegin),
                    current_contract_end: fortmatDate(this.ruleForm.timeEnd),
                    remarke: this.ruleForm.note,
                    status: this.submitStatus,
                    imgs: uploadedImg,
                    is_kf_voucher: this.ruleForm.kfVoucher
                }
                return queryObj
            },
            handleSave(formName) {
                this.submitStatus = 1
                this.submitForm(formName)
            },
            handleSubmit(formName) {
                this.submitStatus = 2
                this.submitForm(formName)
            },
            handleCancel() {
                this.$router.push('/memberReport/list')
            },
            getMemberReportDetail() {
                const queryObj = {
                    cooperation_type: this.memberTypeId,
                    id: this.id
                }
                this.loading = true
                this.memberReportDetail(queryObj, this.setData)
            },
            setData(data) {
                const ret = data.info
                this.ruleForm.id = ret.id
                this.ruleForm.cooperation_type = ret.cooperation_type
                this.ruleForm.status = ret.status

                for(var i = 0;i < ret.img_list.length;i++){
                    let obj = {url:QZ_CONFIG.oss_img_host + ret.img_list[i]}
                    this.fileList.push(obj)
                }
                this.uploadedImg = ret.img_list
                for(var i = 0;i < ret.kf_voucher_img.length;i++){
                    this.kfuploadImg[i] =  QZ_CONFIG.oss_img_host + ret.kf_voucher_img[i]
                }
                this.kfuploadImg = this.kfuploadImg
                this.ruleForm.companyName = ret.company_name
                this.ruleForm.memberType = ret.cooperation_type_name
                this.ruleForm.department = ret.section
                this.ruleForm.name = ret.company_contact
                this.ruleForm.phone = ret.company_contact_phone
                this.ruleForm.account = ret.account
                this.ruleForm.money = ret.current_contract_amount
                this.ruleForm.role = ret.company_rolers ? ret.company_rolers : '门店老板'
                this.ruleForm.tag = ret.company_tag ? ret.company_tag : '付费账号'
                this.ruleForm.timeBegin = ret.current_contract_start * 1000
                this.ruleForm.timeEnd = ret.current_contract_end * 1000
                this.ruleForm.note = ret.remarke
                this.loading = false
            },
            handleUploadSuccess(res, file,fileList) {
                this.uploadedImg.push(res.data.img_path)
            },
            handleRemove(res,file, fileList) {
                this.uploadedImg.forEach((item, index) => {
                    if(res.url.indexOf(item)!= -1){
                        this.uploadedImg.splice(index,1)
                    }
                })
            },
            preview(item) {
                this.previewTemp = []
                this.previewTemp.push(item)
                this.dialogVisiblePreview = true
            }
        }
    }
</script>

<style rel="stylesheet/scss" lang="scss">
    .san-wei-jia {
        .text-center{
            text-align: center;
        }
        .keftips{
            width: 500px;
            position: absolute;
            left: 30px;
            top: -6px;
        }
        .el-range-separator{
            padding: 0;
        }
        .exclude-time{
            position: absolute;
            width: 850px;
            left: 0px;
            top: 0px;
        }
        .el-form-item{
            margin-bottom: 0 !important;
        }
        .el-form-item__content{
            margin-left: 0 !important;
        }
        .el-upload-list--picture .el-upload-list__item {
            width: 100px;
            float: left;
            margin-right: 10px;
        }
        .el-upload-dragger {
            width: 100px;
            height: 100px;
            line-height: 100px;
            text-align: center;
            float: left;
        }
        .inline-block{
            display: inline-block;
        }
        .upimg {
            display: inline-block;
            width: 100px;
            height: 100px;
            margin-right: 10px;
        }
    }

</style>
