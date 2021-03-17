<template>
    <div class="san-wei-jia-detail">
        <div class="main">
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>1、公司名称：</div></el-col>
                <el-col :span="18">{{ companyName }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>2、合作类型：</div></el-col>
                <el-col :span="18">{{ memberType }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>3、部门：</div></el-col>
                <el-col :span="18">{{ department }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>4、姓名：</div></el-col>
                <el-col :span="18">{{ username }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>5、电话：</div></el-col>
                <el-col :span="18">{{ phone }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">6、账号：</div></el-col>
                <el-col :span="18">{{ account }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>7、金额：</div></el-col>
                <el-col :span="18">{{ money }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>8、角色：</div></el-col>
                <el-col :span="4">{{ role }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>9、标签：</div></el-col>
                <el-col :span="18">{{ tags }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">10、时间：</div></el-col>
                <el-col :span="18">{{ time }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">25、备注：</div></el-col>
                <el-col :span="18">{{ note ? note : '----' }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">20、上传图片：</div></el-col>
                <el-col :span="18">
                    <el-col :span="5" v-for='(item,index) in img_list' :key="item">
                        <img :src="item" alt="" class="upimg" @click="preview(index)">
                    </el-col>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">21、客服上传图片：</div></el-col>
                <el-col :span="18">
                    <el-col :span="5" v-for='(item,index) in kfuploadImg' :key="item">
                        <img :src="item" alt="" class="upimg" @click="kfpreview(index)">
                    </el-col>
                </el-col>
            </el-row>
            <!--图片预览-->
            <div id="app">
                <div class="imgMask" v-if="centerDialogVisible" @click.stop="centerDialogVisible=!centerDialogVisible">
                    <i class="iconfont el-icon-arrow-left prev" @click.stop="prev"></i>
                    <div class="showImg" >
                        <img class="bigImg" :src="url[num]">
                    </div>
                    <i class="iconfont el-icon-arrow-right next" @click.stop="next"></i>
                </div>
            </div>
        </div>
        <logs></logs>
    </div>
</template>

<script>
    import memberReportDetail from '@/mixins/memberReport'
    import { fortmatDate } from '@/utils/index'
    import logs from './logs'
    import QZ_CONFIG from '@/utils/config'
    export default {
        name: "sanWeiJiaDetail",
        mixins: [memberReportDetail],
        components: {
            logs
        },
        data() {
            return {
                companyName: '',
                memberType: '',
                department: '',
                username: '',
                phone: '',
                account: '',
                money: '',
                role: '',
                tags: '',
                time: '',
                note: '',
                img_list: [],
                kfuploadImg: [],
                url: [],
                centerDialogVisible: false,
                num: 0
            }
        },
        created() {
            this.getMemberReportDetail()
        },
        methods: {
            prev() {
                const imgNum = this.url.length
                if (this.num === 0) {
                    this.num = imgNum
                }
                this.num--
            },
            next() {
                const imgNum = this.url.length
                if (this.num === imgNum - 1) {
                    this.num = -1
                }
                this.num++
            },
            getMemberReportDetail() {
                const queryObj = {
                    cooperation_type: this.$route.query.type,
                    id: this.$route.query.id
                }
                this.memberReportDetail(queryObj, this.setData)
            },
            setData(data) {
                const ret = data.info
                for(var i = 0; i < ret.img_list.length; i++) {
                    this.img_list[i] = QZ_CONFIG.oss_img_host + ret.img_list[i]
                }
                this.img_list = this.img_list
                for(var i = 0; i < ret.kf_voucher_img.length; i++) {
                    this.kfuploadImg[i] = QZ_CONFIG.oss_img_host + ret.kf_voucher_img[i]
                }
                this.kfuploadImg = this.kfuploadImg
                this.companyName = ret.company_name
                this.memberType = ret.cooperation_type_name
                this.department = ret.section
                this.username = ret.company_contact
                this.phone = ret.company_contact_phone
                this.account = ret.account ? ret.account : '----'
                this.money = ret.current_contract_amount
                this.role = ret.company_rolers ? ret.company_rolers : '----'
                this.tags = ret.company_tag ? ret.company_tag : '----'
                this.time = fortmatDate(ret.current_contract_start * 1000, '/') + ' - ' + fortmatDate(ret.current_contract_end * 1000, '/')
                this.note = ret.remarke ? ret.remarke : '----'
            },
            preview(index) {
                this.url = this.img_list
                this.num = index
                this.centerDialogVisible = true
            },
            kfpreview(index) {
                this.url = this.kfuploadImg
                this.num = index
                this.centerDialogVisible = true
            }
        }
    }
</script>

<style rel="stylesheet/scss" lang="scss">
    .san-wei-jia-detail{
        .main{
            width: 800px;
            border-top:none !important;
        }
        .mb-20{
            margin-bottom: 20px;
        }
        .keftips{
            width: 500px;
            position: absolute;
            left: 30px;
            top: -17px;
        }
        table{
            text-align: center;
        }
        .upimg {
            display: inline-block;
            width: 100px;
            height: 100px;
            margin-right: 10px;
        }
    }
    .imgMask{
        position: fixed;
        height: 100%;
        width:100%;
        top:0;
        left:0;
        bottom:0;
        right:0;
        z-index: 99999;
        background:rgba(0,0,0,.6);
    }
    .showImg{
        height:550px;
        width:800px;
        position: absolute;
        left:50%;
        top:50%;
        overflow:hidden;
        transform:translate(-50%,-50%);
    }
    .bigImg{
        height:100%;
        display: block;
        margin:0 auto;
    }
    .prev{
        position: absolute;
        top:50%;
        left:50px;
        font-size: 46px;
        cursor: pointer;
        color:#fff;
        transform:translate(10px,-50%);
    }
    .next{
        font-size: 46px;
        cursor: pointer;
        transform:translate(10px,-50%);
        position: absolute;
        top:50%;
        color:#fff;
        right:50px;
    }
</style>
