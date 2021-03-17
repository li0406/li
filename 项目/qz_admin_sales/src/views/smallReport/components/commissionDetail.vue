<template>
    <div class="home-decoration-detail">
        <div class="main">
            <el-row class="mb-20 lh-40">
                <el-col :span="3"><div>公司名称：</div></el-col>
                <el-col :span="18">
                    <span>
                        {{ companyName }}
                    </span>
                    <el-button type="primary" v-if="is_related" @click="handleCheck">
                        已报备会员
                    </el-button>
                </el-col>
            </el-row>
            <el-row class="mb-20 lh-40">
                <el-col :span="3"><div>会员类型：</div></el-col>
                <el-col :span="18">{{ memberType }}</el-col>
            </el-row>
            <el-row class="mb-20 lh-40">
                <el-col :span="3"><div>收款城市：</div></el-col>
                <el-col :span="18">{{ city }}</el-col>
            </el-row>
            <el-row class="mb-20 lh-40">
                <el-col :span="3"><div>返点比例：</div></el-col>
                <el-col :span="18">{{ backRatio }}</el-col>
            </el-row>
            <el-row class="mb-20 lh-40">
                <el-col :span="3"><div>收款类型：</div></el-col>
                <el-col :span="18">{{ payment_type_name }}</el-col>
            </el-row>
            <el-row class="mb-20 lh-40">
                <el-col :span="3"><div>收款金额：</div></el-col>
                <el-col :span="18">{{ payment_money }}</el-col>
            </el-row>
            <el-row v-if="round_order_money > 0" class="mb-20 lh-40">
                <el-col :span="3"><div>轮单费：</div></el-col>
                <el-col :span="18">{{ round_order_money }}</el-col>
            </el-row>
            <el-row v-if="deposit_money > 0" class="mb-20 lh-40">
                <el-col :span="3"><div>保证金：</div></el-col>
                <el-col :span="18">{{ deposit_money }}</el-col>
            </el-row>
            <el-row class="mb-20 lh-40">
                <el-col :span="3"><div>收款方名称：</div></el-col>
                <el-col :span="18"> <span v-for="item in payee_list" :key="item.payee_px">{{item.payee_type_name}}、</span></el-col>
            </el-row>
            <el-row class="mb-20 lh-40">
                <el-col :span="3"><div>汇款时间：</div></el-col>
                <el-col :span="18">{{ payment_date_show }}</el-col>
            </el-row>
            <el-row class="mb-20 lh-40">
                <el-col :span="3"><div>汇款方名称：</div></el-col>
                <el-col :span="18">{{ payment_uname }}</el-col>
            </el-row>
            <el-row class="mb-20 lh-40">
                <el-col :span="3"><div>汇款凭证：</div></el-col>
                <el-col :span="18">
                    <el-col :span="5" v-for='(item,index) in img_list' :key="item">
                        <img :src="item" alt="" class="upimg" @click="preview(index)">
                    </el-col>
                </el-col>
            </el-row>
            <el-row class="mb-20 lh-40"  v-if="remark != ''">
                <el-col :span="3"><div>销售备注：</div></el-col>
                <el-col :span="18">{{ remark }}</el-col>
            </el-row>
            <el-row class="mb-20 lh-40">
                <el-col :span="3"><div>报备人：</div></el-col>
                <el-col :span="3">{{ creator_name }}</el-col>
                <el-col :span="3" v-if="person_list.length>0"><div>其他业绩人：</div></el-col>
                <el-col :span="15">
                    <div v-if="person_list">
                        <div class="person-list" v-for="(item,index) in person_list" :key="index">{{item.saler_name}}({{item.share_ratio_text}})</div>
                    </div>
                    <div v-else>-</div>
                </el-col>
            </el-row>
            <el-row class="mb-20 lh-40">
                <el-col :span="3"><div>审批状态：</div></el-col>
                <el-col :span="18">{{ exam_status }}</el-col>
            </el-row>
            <el-row class="mb-20 lh-40" v-if="exam_status === '审核不通过'">
                <el-col :span="3"><div>备注：</div></el-col>
                <el-col :span="18">{{ exam_reason }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-button @click="handleBack">返回</el-button>
            </el-row>
        </div>
        <div id="app">
            <div class="imgMask" v-if="centerDialogVisible" @click.stop="centerDialogVisible=!centerDialogVisible">
                <i class="iconfont el-icon-arrow-left prev" @click.stop="prev"></i>
                <div class="showImg" >
                    <img class="bigImg" :src="url[num].img_full">
                </div>
                <i class="iconfont el-icon-arrow-right next" @click.stop="next"></i>
            </div>
        </div>
    </div>
</template>

<script>
    import smallReportDetail from '@/mixins/memberReport'
    import { fortmatDate } from '@/utils/index'
    import QZ_CONFIG from '@/utils/config'
    export default {
        name: "commissionDetail",
        mixins: [smallReportDetail],
        data() {
            return {
                report_id: '',
                cooperation_type: '',
                report_cooperation_type: '',
                is_related: true,
                companyName: '',
                memberType: '',
                city: '',
                backRatio: '',
                payment_type_name: '',
                payment_money: '',
                round_order_money: '',
                deposit_money: '',
                payee_list: '',
                payment_date_show: '',
                payment_uname: '',
                remark: '',
                img_list: [],
                creator_name: '',
                exam_status: '',
                exam_reason: '',
                person_list: [],
                url: [],
                centerDialogVisible: false,
                num: 0
            }
        },
        created() {
            this.getSmallReportDetail()
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
            getSmallReportDetail() {
                const queryObj = {
                    cooperation_type: this.$route.query.type,
                    id: this.$route.query.id
                }
                this.smallReportDetail(queryObj, this.setData)
            },
            setData(data) {
                console.log(data)
                const ret = data.detail
                if (ret.is_related == 1) {
                    this.is_related = false
                } else if (ret.is_related == 2) {
                    this.is_related = true
                }
                for(var i = 0;i < ret.auth_imgs.length;i++){
                    this.img_list[i] =  QZ_CONFIG.oss_img_host + ret.auth_imgs[i].img_src
                }
                this.img_list = this.img_list
                this.companyName = ret.company_name
                this.memberType = ret.cooperation_type_name
                this.city = ret.city_name
                this.backRatio = ret.back_ratio
                this.payment_type_name = ret.payment_type_name
                this.payment_money = ret.payment_total_money
                this.round_order_money = ret.round_order_money
                this.deposit_money = ret.deposit_money
                this.payee_list = ret.payee_list
                this.payment_date_show = ret.payment_date_show
                this.payment_uname = ret.payment_uname
                this.remark = ret.remark
                this.creator_name = ret.creator_name
                if (ret.exam_status == 1) {
                    this.exam_status = '待提交'
                } else if (ret.exam_status == 2) {
                    this.exam_status = '待审核'
                } else if (ret.exam_status == 3) {
                    this.exam_status = '审核通过'
                } else if (ret.exam_status == 4) {
                    this.exam_status = '审核不通过'
                }
                this.exam_reason = ret.exam_reason
                this.report_id = ret.report_id
                this.report_cooperation_type = ret.report_cooperation_type
                this.person_list = ret.person_list
                this.url = ret.auth_imgs
            },
            handleCheck() {
                const {href} = this.$router.resolve({
                    name: 'reportDetail',
                    path: "/memberReport/detail",
                    query: {
                        id: this.report_id,
                        type: this.report_cooperation_type
                    }
                })
                window.open(href, '_blank')
            },
            handleBack() {
                this.$router.push({
                    path: '/smallReport/list'
                })
            },
            preview(index) {
                this.num = index
                this.centerDialogVisible = true
            }
        }
    }
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.home-decoration-detail{
    .main{
        width: 800px;
        border-top:none !important;
    }
    .mb-20{
        margin-bottom: 20px;
    }
    .lh-40 {
        line-height: 40px;
    }
    .inline-block{
        display: inline-block;
    }
    .keftips{
        width: 500px;
        position: absolute;
        left: 30px;
        top: -17px;
    }
    .black{
        color: black;
    }
    .upimg {
        display: inline-block;
        width: 100px;
        height: 100px;
        margin-right: 10px;
    }
    .promiseorder{
        margin-bottom: 20px;
        margin-right: -160px;
        width: 1172px;
        i{
            font-style: normal;
        }
        .one{
            width: 80px;
        }
        .two{
            width: 150px;
        }
        .three{
            width: 150px;
        }
    }
    .person-list{
        display: inline-block!important;
        margin-right: 5px;
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
}
</style>
