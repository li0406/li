<template>
    <div class="sanWeiJia-offer-add">
        <div class="qian-main">
            <el-form :model="ruleForm" :rules="rules" v-loading="loading" ref="ruleForm" label-width="100px" class="demo-ruleForm">
                <el-row class="mb-20">
                    <el-col :span="6"><div class="lh-40">合作类型：</div></el-col>
                    <el-col :span="18">
                        <el-form-item prop="cooperation_name">
                            <el-input v-model="ruleForm.cooperation_name" placeholder="请输入"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row class="mb-20">
                    <el-col :span="6"><div class="lh-40">报价类型：</div></el-col>
                    <el-col :span="18">
                        <el-form-item prop="quote_type">
                            <el-input v-model="ruleForm.quote_type" placeholder="请输入"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row class="mb-20">
                    <el-col :span="6"><div class="lh-40">合作报价：</div></el-col>
                    <el-col :span="18">
                        <el-form-item prop="cooperation_price">
                            <el-input v-model="ruleForm.cooperation_price" placeholder="请输入"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row>
                    <el-button type="primary" @click="handleSave('ruleForm')">保存</el-button>
                    <el-button @click="handleCancel">取消</el-button>
                </el-row>
            </el-form>
        </div>
    </div>
</template>

<script>
    import { fetchOtherOfferSave, fetchErpSwjOfferDetail } from '@/api/memberReport'
    import inputFilter from '@/directive/input-filter/index.js'
    export default {
        name: "sanWeiJiaOfferAdd",
        directives: {
            inputFilter
        },
        data() {
            const validateInt = (rule, value, callback) => {
                const reg = /^[+]{0,1}(\d+)$/
                if (value && !reg.test(value)) {
                    callback(new Error('请输入正整数'))
                } else {
                    callback()
                }
            }
            return {
                id: '',
                loading: false,
                ruleForm: {
                    cooperation_name: '',
                    quote_type: '',
                    cooperation_price: ''
                },
                rules: {
                    cooperation_name: [
                        { required: true, message: '请输入合作类型', trigger: 'change' }
                    ],
                    quote_type: [
                        { required: true, message: '请输入报价类型', trigger: 'change' }
                    ],
                    cooperation_price: [
                        { required: true, message: '请输入合作报价', trigger: 'change' },
                        { validator: validateInt, trigger: 'change' }
                    ]
                }
            }
        },
        created() {
            if(this.$route.query && this.$route.query.id) {
                this.id = this.$route.query.id
                this.getErpSwjOfferDetail()
                this.$nextTick(() => {
                    const el = document.getElementsByClassName('tags-view-item')[0]
                    el.innerHTML = el.innerHTML.replace('添加', '编辑')
                })
            }
        },
        methods: {
            submitForm(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.handleSaveAjax()
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            handleSave(formName) {
                this.submitForm(formName)
            },
            handleSaveAjax() {
                const queryObj = this.handleArguments()
                this.loading = true
                fetchOtherOfferSave(queryObj).then(res => {
                    if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                        this.$message({
                            type: 'success',
                            message: '保存成功'
                        })
                        setTimeout(() => {
                            window.close()
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
                const queryObj = {
                    id: this.id,
                    cooperation_name: this.ruleForm.cooperation_name,
                    quote_type: this.ruleForm.quote_type,
                    cooperation_price: this.ruleForm.cooperation_price
                }
                return queryObj
            },
            getErpSwjOfferDetail() {
                this.loading = true
                fetchErpSwjOfferDetail({
                    id: this.id
                }).then(res => {
                    if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                        if(res.data.data.detail) {
                            const detail = res.data.data.detail
                            this.ruleForm.cooperation_name = detail.cooperation_name
                            this.ruleForm.quote_type = detail.quote_type
                            this.ruleForm.cooperation_price = detail.cooperation_price
                        }
                    }else{
                        this.$message.error(res.data.error_msg)
                    }
                    this.loading = false
                }).catch(res => {
                    this.$message.error('网络异常，请稍后再试')
                    this.loading = false
                })
            },
            handleCancel() {
                window.close()
            }
        }
    }
</script>

<style rel="stylesheet/scss" lang="scss">
    .sanWeiJia-offer-add {
        width: 100%;
        padding: 20px;
        box-sizing: border-box;
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
        .qian-main {
            margin-top: 20px;
            padding: 20px;
            border-top: 3px solid #999;
            box-sizing: border-box;
            background-color: #fff;
        }
        .demo-ruleForm{
            width: 800px;
        }
        .mb-20{
            margin-bottom: 20px;
        }
        .el-form-item{
            margin-bottom: 0 !important;
        }
        .el-form-item__content{
            margin-left: 0 !important;
        }
    }

</style>
