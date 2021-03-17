<template>
    <div class="erp-detail">
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
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>3、城市：</div></el-col>
                <el-col :span="18">{{ city }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>4、新/老会员：</div></el-col>
                <el-col :span="18">{{ isNewMember }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>5、联系人：</div></el-col>
                <el-col :span="18">{{ contact }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">6、职务：</div></el-col>
                <el-col :span="18">{{ duties ? duties : '----' }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>7、电话：</div></el-col>
                <el-col :span="18">{{ phone }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">8、微信号：</div></el-col>
                <el-col :span="18">{{ wechat ? wechat : '----' }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>9、合作模式：</div></el-col>
                <el-col :span="18">{{ cooperationMode }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>10、合同时间：</div></el-col>
                <el-col :span="6">{{constart ? contractTime: '----' }}</el-col>
                <el-col :span="4" style="position:relative;"><p class="red keftips">客服注意，请记得按这个上传，销售勿删</p></el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>11、本次款项到账时间：</div></el-col>
                <el-col :span="18">{{ moneyInTime }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">12、下次付款时间：</div></el-col>
                <el-col :span="18">{{ nextpayTime ? nextpayTime : '----' }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>13、总合同金额：</div></el-col>
                <el-col :span="18">{{ totalMoney }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40"><i class="red">*</i>14、本次到账金额：</div></el-col>
                <el-col :span="18">{{ getMoney }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">15、余款多少：</div></el-col>
                <el-col :span="18">{{ String(lastMoney) === '-1' ? '----' : lastMoney }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">16、网店代码：</div></el-col>
                <el-col :span="18">{{ onlineShopCode }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">17、账户登录名称：</div></el-col>
                <el-col :span="18">{{ account }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">18、备注：</div></el-col>
                <el-col :span="18">{{ otherNote }}</el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">19、上传图片：</div></el-col>
                <el-col :span="18">
                    <el-col :span="5" v-for='(item,index) in img_list' :key="item">
                        <img :src="item" alt="" class="upimg" @click="preview(index)">
                    </el-col>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">20、客服上传图片：</div></el-col>
                <el-col :span="18">
                    <el-col :span="5" v-for='(item,index) in kfuploadImg' :key="item">
                        <img :src="item" alt="" class="upimg" @click="kfpreview(index)">
                    </el-col>
                </el-col>
            </el-row>
            <el-row class="mb-20">
                <el-col :span="6"><div class="lh-40">21、开站讨论组备注：</div></el-col>
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
    import { fortmatDate } from "@/utils/index";
    import logs from './logs'
    import QZ_CONFIG from '@/utils/config'
    export default {
        name: "erp-detail",
        mixins: [memberReportDetail],
        components: {
            logs
        },
        data() {
            return {
                companyName: '',
                memberType: '',
                city: '',
                isNewMember: '',
                contact: '',
                duties: '',
                phone: '',
                wechat: '',
                contractTime: '',
                cooperationMode: '',
                memberTime: '',
                moneyInTime: '',
                nextpayTime: '',
                totalMoney: '',
                getMoney: '',
                lastMoney: '',
                onlineShopCode: '',
                otherNote: '',
                account: '',
                img_list: [],
                kfuploadImg: [],
                constart: '',
                adminRemarke: '',
                serviceRemarke: '',
                url: [],
                centerDialogVisible: false,
                num: 0,
                openCityChatRemark: ''
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
                for(var i = 0;i < ret.img_list.length;i++){
                    this.img_list[i] =  QZ_CONFIG.oss_img_host + ret.img_list[i]
                }
                this.img_list = this.img_list
                for(var i = 0;i < ret.kf_voucher_img.length;i++){
                    this.kfuploadImg[i] =  QZ_CONFIG.oss_img_host + ret.kf_voucher_img[i]
                }
                this.kfuploadImg = this.kfuploadImg
                this.companyName = ret.company_name
                this.memberType = ret.cooperation_type_name
                this.city = ret.city_name
                this.isNewMember = ret.is_new_name
                this.contact = ret.company_contact
                this.duties = ret.company_contact_position
                this.phone = ret.company_contact_phone
                this.wechat = ret.company_contact_weixin
                this.constart = ret.contract_start
                this.contractTime = fortmatDate(ret.contract_start * 1000, '/') + ' - ' + fortmatDate(ret.contract_end * 1000, '/')
                this.cooperationMode = ret.cooperation_mode == 1 ? '一年' : '终生'
                this.memberTime = fortmatDate(ret.current_contract_start * 1000, '/')  + ' - ' + fortmatDate(ret.current_contract_end * 1000, '/')
                this.moneyInTime = fortmatDate(ret.current_payment_time * 1000, '/')
                this.nextpayTime = fortmatDate(ret.next_payment_time * 1000, '/')
                this.totalMoney = ret.total_contract_amount
                this.getMoney = ret.current_contract_amount
                this.lastMoney = ret.lave_amount
                this.onlineShopCode = ret.company_id ? ret.company_id : '----'
                this.otherNote = ret.remarke ? ret.remarke : '----'
                this.account = ret.account ? ret.account : '----'
                this.adminRemarke = ret.admin_remarke
                this.serviceRemarke = ret.service_remarke
                this.openCityChatRemark = ret.open_city_chat_remark
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
    .erp-detail{
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
