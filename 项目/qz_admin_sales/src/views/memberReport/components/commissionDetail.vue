<template>
    <div class="commission-detail">
        <div class="main">
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>1、公司名称：</div></el-col>
                <el-col :span="18">{{ companyName }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>2、合作类型：</div></el-col>
                <el-col :span="18">{{ memberName }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>3、城市：</div></el-col>
                <el-col :span="18">{{ city }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>4、装企签单返点：</div></el-col>
                <el-col :span="18">{{ memberType === 15 ? '标杆会员（保产值）' : backRatio }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>5、新/老会员：</div></el-col>
                <el-col :span="18">{{ isNewMember }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>6、联系人：</div></el-col>
                <el-col :span="18">{{ contact }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">7、职务：</div></el-col>
                <el-col :span="18">{{ duties ? duties : '----' }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>8、电话：</div></el-col>
                <el-col :span="18">{{ phone }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">9、微信号：</div></el-col>
                <el-col :span="18">{{ wechat ? wechat : '----' }}</el-col>
            </el-row>
            <!-- <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>10、总合同时间：</div></el-col>
                <el-col :span="18">{{ contractTime }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">备注：</div></el-col>
                <el-col :span="18">{{ totalContractRemark }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>11、本次会员时间：</div></el-col>
                <el-col :span="6" :class="is_abnormal">{{ memberTime }}</el-col>
                <el-col :span="4" style="position:relative;"><p class="red keftips">客服注意，请记得按这个上传，销售勿删</p></el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">备注：</div></el-col>
                <el-col :span="18">{{ contractRemark }}</el-col>
            </el-row> -->
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>12、本次款项到账时间：</div></el-col>
                <el-col :span="18">{{ moneyInTime }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">13、下次付款时间：</div></el-col>
                <el-col :span="18">{{ nextpayTime ? nextpayTime : '----' }}</el-col>
            </el-row>
            <el-row class="mb-20 amount">
                <el-col :span="4"><div class="lh-40"><i class="red">*</i>14、总合同金额：</div></el-col>
                <el-col :span="2">{{ totalMoney }}</el-col>
                <el-col :span="2"><div class="lh-40">保证金：</div></el-col>
                <el-col :span="2">{{ totalBond }}</el-col>
            </el-row>
            <el-row class="mb-20 amount">
                <el-col :span="4" style="padding-left:38px"><div class="lh-40">平台使用费：</div></el-col>
                <el-col :span="2">{{ info.total_platform_money }}</el-col>
                <el-col v-if="info.total_platform_money" :span="4"><div class="lh-40">平台使用费有效期：</div></el-col>
                <el-col v-if="info.total_platform_money" :span="4">{{ info.total_platform_money_start_date }} - {{ info.total_platform_money_end_date }}</el-col>
            </el-row>
            <el-row class="mb-20 amount">
                <el-col :span="4" v-if="totalAmount > 0" style="padding-left:38px"><div class="lh-40">轮单费：</div></el-col>
                <el-col :span="2" v-if="totalAmount > 0" class="c90f">{{ totalAmount }}</el-col>
                <el-col :span="2"><div class="lh-40">返现金额：</div></el-col>
                <el-col :span="2" class="c90f">{{ total_contract_gift_amount }}</el-col>
                <el-col :span="2" v-if="total_contract_round_total_amount > 0"><div class="lh-40">总轮单费：</div></el-col>
                <el-col :span="2" v-if="total_contract_round_total_amount > 0">{{ total_contract_round_total_amount }}</el-col>
            </el-row>
            <el-row v-if="memberType === 15" class="mb-20 amount">
                <el-col :span="4" style="padding-left:38px"><div class="lh-40">总合同保产值金额：</div></el-col>
                <el-col :span="2">{{ info.total_contract_amount_guaranteed }}</el-col>
            </el-row>
            <el-row class="mb-20 amount">
                <el-col :span="4"><div class="lh-40"><i class="red">*</i>15、本次到账金额：</div></el-col>
                <el-col :span="2" class="c90f">{{ getMoney }}</el-col>
                <el-col :span="2"><div class="lh-40">保证金：</div></el-col>
                <el-col :span="2">{{ currentBond }}</el-col>
            </el-row>
            <el-row class="mb-20 amount">
                <el-col :span="4" style="padding-left:38px"><div class="lh-40">平台使用费：</div></el-col>
                <el-col :span="2">{{ info.current_platform_money }}</el-col>
                <el-col v-if="info.current_platform_money" :span="4"><div class="lh-40">平台使用费有效期：</div></el-col>
                <el-col v-if="info.current_platform_money" :span="4">{{ info.current_platform_money_start_date }} - {{ info.current_platform_money_end_date }}</el-col>
            </el-row>
            <el-row class="mb-20 amount">
                <el-col :span="4" v-if="currentAmount > 0" style="padding-left:38px"><div class="lh-40">轮单费：</div></el-col>
                <el-col :span="2" v-if="currentAmount > 0" class="c90f">{{ currentAmount }}</el-col>
                <el-col :span="2"><div class="lh-40">返现金额：</div></el-col>
                <el-col :span="2" class="c90f">{{ current_contract_gift_amount }}</el-col>
                <el-col :span="2" v-if="current_contract_round_total_amount > 0" ><div class="lh-40">总轮单费：</div></el-col>
                <el-col :span="2" v-if="current_contract_round_total_amount > 0">{{ current_contract_round_total_amount }}</el-col>
                <el-col :span="2" v-if="showSmall"><div class="lh-40">小报备：</div></el-col>
                <el-col :span="4" v-if="showSmall" style="color:green;">{{ paymentMoney }}（{{ paymentNumber }}）次</el-col>
            </el-row>
            <el-row v-if="memberType === 15" class="mb-20 amount">
                <el-col :span="4" style="padding-left:38px"><div class="lh-40">本次到账保产值金额：</div></el-col>
                <el-col :span="2">{{ info.current_contract_amount_guaranteed }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">16、余款多少：</div></el-col>
                <el-col :span="18">{{ String(lastMoney) === '-1' ? '----' : lastMoney }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6">
                    <div class="lh-40"><i class="red">*</i>17、订单承诺量：</div>
                </el-col>
                <el-col :span="18">
                    <div class="promiseorder" style="margin-bottom: 0">
                        <span class="inline-block">轮单费单价：{{ roundOrderAmount }}</span>
                    </div>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">18、LOGO位置：</div></el-col>
                <el-col :span="18">{{ logoPos ? logoPos : '----' }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">19、通栏位置：</div></el-col>
                <el-col :span="18">{{ bannerPos | bannerPosFilter }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">20、总合同广告赠送：</div></el-col>
                <el-col :span="18">轮显{{ giftAdCircleDays }}天 通栏{{ giftAdBannerDays }}天</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">21、本次上广告情况：</div></el-col>
                <el-col :span="18">{{ adOnlineNote }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">22、接单金额及区域：</div></el-col>
                <el-col :span="18">{{ orderAreaNote }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>23、家装/公装是否都接：</div></el-col>
                <el-col :span="4">{{ isAllOrder }}</el-col>
                <el-col :span="14">{{isAllOrderOther}}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">24、网店代码：</div></el-col>
                <el-col :span="18">{{ onlineShopCode }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">25、备注：</div></el-col>
                <el-col :span="18">{{ otherNote }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">26、上传图片：</div></el-col>
                <el-col :span="18">
                    <el-col :span="5" v-for='(item,index) in img_list' :key="item">
                        <img :src="item" alt="" class="upimg" @click="preview(index)">
                    </el-col>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">27、客服上传图片：</div></el-col>
                <el-col :span="18">
                    <el-col :span="5" v-for='(item,index) in kfuploadImg' :key="item">
                        <img :src="item" alt="" class="upimg" @click="kfpreview(index)">
                    </el-col>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">28、开站讨论组备注：</div></el-col>
                <el-col :span="18">
                    <el-col :span="18">{{ openCityChatRemark }}</el-col>
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
        name: "commissionDetail",
        mixins: [memberReportDetail],
        components: {
            logs
        },
        data() {
            return {
                companyName: '',
                memberType: '',
                memberName: '',
                city: '',
                backRatio: '',
                isNewMember: '',
                contact: '',
                duties: '',
                phone: '',
                wechat: '',
                contractTime: '',
                memberTime: '',
                moneyInTime: '',
                nextpayTime: '',
                totalMoney: '',
                getMoney: '',
                lastMoney: '',
                logoPos: '',
                bannerPos: '',
                giftAdCircleDays: '',
                giftAdBannerDays: '',
                adOnlineNote: '',
                orderAreaNote: '',
                isAllOrder: '',
                onlineShopCode: '',
                otherNote: '',
                img_list: [],
                roundOrderAmount: '',
                isAllOrderOther: '',
                totalFixed: '',
                totalBond: '',
                totalAmount: '',
                total_contract_round_total_amount: '',
                total_contract_gift_amount: '',
                currentFixed: '',
                currentBond: '',
                currentAmount: '',
                current_contract_round_total_amount: '',
                current_contract_gift_amount: '',
                contractRemark: '',
                totalContractRemark: '',
                kfuploadImg: [],
                is_abnormal: '',
                paymentMoney: '',
                paymentNumber: '',
                showSmall: true,
                url: [],
                centerDialogVisible: false,
                num: 0,
                openCityChatRemark: '',
                cashdepositHandler: '',
                roundOrderAmount: '',
                info: {}
            }
        },
        filters: {
            bannerPosFilter(val) {
                switch (val) {
                    case 1:
                        return 'A通栏'
                        break
                    case 2:
                        return 'B通栏'
                        break
                    case 3:
                        return 'A+B通栏'
                        break
                    default:
                        return '----'
                }
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
                for (var i = 0; i < ret.img_list.length; i++) {
                    this.img_list[i] = QZ_CONFIG.oss_img_host + ret.img_list[i]
                }
                for (var i = 0; i < ret.kf_voucher_img.length; i++) {
                    this.kfuploadImg[i] = QZ_CONFIG.oss_img_host + ret.kf_voucher_img[i]
                }
                this.kfuploadImg = this.kfuploadImg
                this.img_list = this.img_list
                this.companyName = ret.company_name
                this.memberType = ret.cooperation_type
                this.memberName = ret.cooperation_type_name
                this.city = ret.city_name
                this.backRatio = ret.back_ratio
                this.isNewMember = ret.is_new_name
                this.contact = ret.company_contact
                this.duties = ret.company_contact_position
                this.phone = ret.company_contact_phone
                this.wechat = ret.company_contact_weixin
                this.contractTime = ret.contract_start == 0 ? '----' : fortmatDate(ret.contract_start * 1000, '/') + ' - ' + fortmatDate(ret.contract_end * 1000, '/')
                this.memberTime = ret.current_contract_start == 0 ? '----' : fortmatDate(ret.current_contract_start * 1000, '/') + ' - ' + fortmatDate(ret.current_contract_end * 1000, '/')
                this.moneyInTime = fortmatDate(ret.current_payment_time * 1000, '/')
                this.nextpayTime = fortmatDate(ret.next_payment_time * 1000, '/')
                this.totalMoney = ret.total_contract_amount
                this.getMoney = ret.current_contract_amount

                this.totalFixed = ret.total_contract_fixed_amount
                this.totalBond = ret.total_contract_bond
                this.totalAmount = ret.total_contract_round_amount
                this.total_contract_round_total_amount = ret.total_contract_round_total_amount
                this.total_contract_gift_amount = ret.total_contract_gift_amount
                this.currentFixed = ret.current_contract_fixed_amount
                this.currentBond = ret.current_contract_bond
                this.currentAmount = ret.current_contract_round_amount
                this.current_contract_round_total_amount = ret.current_contract_round_total_amount
                this.current_contract_gift_amount = ret.current_contract_gift_amount

                this.lastMoney = ret.lave_amount
                this.logoPos = ret.logo_address
                this.bannerPos = ret.passage_position
                this.giftAdCircleDays = ret.contract_handsel_banner
                this.giftAdBannerDays = ret.contract_handsel_passage
                this.adOnlineNote = ret.current_adver_content ? ret.current_adver_content : '----'
                this.orderAreaNote = ret.amount_and_area ? ret.amount_and_area : '----'
                this.isAllOrderOther = ret.lxs_remark
                this.contractRemark = ret.contract_remark
                this.totalContractRemark = ret.total_contract_remark
                this.openCityChatRemark = ret.open_city_chat_remark
                if (ret.is_abnormal === 1) {
                    this.is_abnormal = 'red'
                }
                if (ret.report_payment_number === 0) {
                    this.showSmall = false
                }
                if (ret.report_money_compart === 0 && this.showSmall) {
                    this.report_money_compart = 'red'
                }
                switch (ret.lxs) {
                    case 1:
                        this.isAllOrder = '家装'
                        break
                    case 2:
                        this.isAllOrder = '公装'
                        break
                    case 3:
                        this.isAllOrder = '公装/家装'
                        break
                    case 4:
                        this.isAllOrder = '其他'
                        break
                    default:
                        this.isAllOrder = '----'
                }
                this.onlineShopCode = ret.company_id ? ret.company_id : '----'
                this.otherNote = ret.remarke ? ret.remarke : '----'
                this.roundOrderAmount = ret.round_order_amount
                this.paymentMoney = ret.report_payment_money + ret.deposit_to_round_money
                this.paymentNumber = ret.report_payment_number
                this.cashdepositHandler = fortmatDate(ret.cashdeposit_handler * 1000, '/')
                this.roundOrderAmount = ret.round_order_amount
                this.info = ret
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
.commission-detail{
    .main{
        width: 800px;
        border-top:none !important;
    }
    .mb-20{
        margin-bottom: 20px;
    }
    .c90f{
      color: #9900ff;
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
    .amount{
        width: 1202px;
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
    .next-line{
        margin-left: 70px;
    }
}
</style>
