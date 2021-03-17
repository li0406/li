<template>
  <div class="commission-member">
    <el-form :model="ruleForm" :rules="rules" v-loading="loading" ref="ruleForm" label-width="100px" class="demo-ruleForm">
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>1、公司名称：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="companyName">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.companyName" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.companyName" :maxlength="30" placeholder="请输入"></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>2、合作类型：</div>
        </el-col>
        <el-col :span="18">
          <el-input v-if="operationActionTag === 'add'" v-model="memberType" :disabled="true"></el-input>
          <span v-if="operationActionTag === 'edit'">
                        <el-select v-if="!unCheckEditTag" v-model="memberTypeVal" placeholder="请选择" @change="optionChange">
                          <template v-for="item in memberTypeOptions">
                            <el-option
                              v-if="item.value !== 4 && item.value !== 5 && item.value !== 8"
                              :key="item.value"
                              :label="item.label"
                              :value="item.value">
                            </el-option>
                          </template>
                        </el-select>
                        <el-input v-else v-model="memberType" :disabled="true"></el-input>
                    </span>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>3、城市：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="citySelectStr" :rules='{ required: true, message: "请选择城市"}'>
            <el-input v-if="unCheckEditTag" v-model="ruleForm.citySelectStr" :disabled="true" placeholder="请输入"></el-input>
            <el-autocomplete
              v-else
              v-model="ruleForm.citySelectStr"
              class="inline-input mt4"
              :fetch-suggestions="queryCity"
              placeholder="请输入"
              @select="handleSelect"
            />
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>4、装企签单返点：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item
            v-if="memberTypeVal != 15"
            prop="backRatio"
            :rules='{ required: true, message: "请选择返点比例" }'
          >
            <el-select v-if="unCheckEditTag" v-model="ruleForm.backRatio" placeholder="请选择" :disabled="true">
              <el-option
                v-for="item in backRatioOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
            <el-select v-else v-model="ruleForm.backRatio" placeholder="请选择">
              <el-option
                v-for="item in backRatioOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>

          <el-form-item v-else>
            <el-input value="标杆会员（保产值）"  placeholder="请输入" :disabled="true" style="width:200px;"></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>5、新/老会员：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="isNewMember">
            <el-select
              v-if="unCheckEditTag"
              v-model="ruleForm.isNewMember"
              placeholder="请选择"
              :disabled="true"
              @change="isNewMemberChange">
              <el-option
                v-for="item in isNewMemberOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
            <el-select v-else v-model="ruleForm.isNewMember" placeholder="请选择" @change="isNewMemberChange">
              <el-option
                v-for="item in isNewMemberOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>6、联系人：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="contact">
            <el-input v-model="ruleForm.contact" :maxlength="6" placeholder="请输入"></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">7、职务：</div>
        </el-col>
        <el-col :span="18">
          <el-input v-model="ruleForm.duties" :maxlength="10" placeholder="请输入"></el-input>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>8、电话：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="phone">
            <el-input v-model="ruleForm.phone" placeholder="请输入" maxlength="11"></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">9、微信号：</div>
        </el-col>
        <el-col :span="18">
          <el-input v-model="ruleForm.wechat" :maxlength="20" placeholder="请输入"></el-input>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>12、本次款项到账时间：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="moneyInTime">
            <el-date-picker
              v-if="unCheckEditTag"
              v-model="ruleForm.moneyInTime"
              type="date"
              placeholder="请选择"
              :disabled="true"
            >
            </el-date-picker>
            <el-date-picker
              v-else
              v-model="ruleForm.moneyInTime"
              type="date"
              placeholder="请选择">
            </el-date-picker>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">13、下次付款时间：</div>
        </el-col>
        <el-col :span="18">
          <el-date-picker
            v-if="unCheckEditTag"
            v-model="ruleForm.nextpayTime"
            type="date"
            placeholder="请选择"
            :disabled="true"
          >
          </el-date-picker>
          <el-date-picker
            v-else
            v-model="ruleForm.nextpayTime"
            type="date"
            placeholder="请选择">
          </el-date-picker>
        </el-col>
      </el-row>
      <el-row class="mb-20 totalMoney">
        <el-col :span="3">
          <div class="lh-40"><i class="red">*</i>14、总合同金额：</div>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="totalMoney">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.totalMoney" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.totalMoney" :maxlength="9" placeholder="请输入" ></el-input>
          </el-form-item>
        </el-col>
        <el-col :span="2">
          <div class="lh-40">保证金：</div>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="totalBond">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.totalBond" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.totalBond" :maxlength="9" placeholder="请输入" ></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20 totalMoney">
        <el-col :span="3" style="padding-left:33px">
          <div class="lh-40">平台使用费：</div>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="totalPlatformMoney">
            <el-input v-model="ruleForm.totalPlatformMoney" placeholder="0" :disabled="unCheckEditTag"></el-input>
          </el-form-item>
        </el-col>
        <el-col :span="3">
          <div class="lh-40">平台使用费有效期：</div>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="totalPlatformMoneyDate">
            <el-date-picker
              v-model="ruleForm.totalPlatformMoneyDate"
              type="daterange"
              range-separator="-"
              value-format="yyyy-MM-dd"
              start-placeholder="选择开始日期"
              end-placeholder="选择结束日期"
              :disabled="unCheckEditTag || !ruleForm.totalPlatformMoney || ruleForm.totalPlatformMoney == 0"
            />
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20 totalMoney">
        <el-col :span="3" style="padding-left:33px">
          <div class="lh-40"><i class="red">*</i>总轮单费：</div>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="total_contract_round_total_amount">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.total_contract_round_total_amount" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.total_contract_round_total_amount" :maxlength="9" placeholder="请输入" @change="totalContractRoundTotalAmount"></el-input>
          </el-form-item>
        </el-col>
        <el-col :span="2">
          <div class="lh-40"><i class="red">*</i>轮单费：</div>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="totalAmount">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.totalAmount" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.totalAmount" :maxlength="9" placeholder="请输入" ></el-input>
          </el-form-item>
        </el-col>
        <el-col :span="2">
          <div class="lh-40"><i class="red"></i>返现金额：</div>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="total_contract_gift_amount">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.total_contract_gift_amount" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.total_contract_gift_amount" :maxlength="9" placeholder="请输入" ></el-input>
          </el-form-item>
        </el-col>
      </el-row>
       <el-row class="mb-20 totalMoney" v-if="memberTypeVal == 15">
        <el-col :span="4" style="padding-left:33px">
          <div class="lh-40"><i class="red">*</i>总合同保产值金额：</div>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="total_contract_amount_guaranteed">
            <el-input v-model="ruleForm.total_contract_amount_guaranteed" maxlength="19" placeholder="请输入" :disabled="unCheckEditTag"></el-input>
          </el-form-item>
        </el-col>
       </el-row>
      <el-row class="mb-20 totalMoney">
        <el-col :span="3">
          <div class="lh-40"><i class="red">*</i>15、本次到账金额：</div>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="getMoney">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.getMoney" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.getMoney" :maxlength="9" @blur="checkContract()" placeholder="请输入"></el-input>
          </el-form-item>
        </el-col>
        <el-col :span="2">
          <div class="lh-40">保证金：</div>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="currentBond">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.currentBond" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.currentBond" :maxlength="9" placeholder="请输入"></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20 totalMoney">
        <el-col :span="3" style="padding-left:33px">
          <div class="lh-40">平台使用费：</div>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="currentPlatformMoney">
            <el-input v-model="ruleForm.currentPlatformMoney" placeholder="0" :disabled="unCheckEditTag"></el-input>
          </el-form-item>
        </el-col>
        <el-col :span="3">
          <div class="lh-40">平台使用费有效期：</div>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="currentPlatformMoneyDate">
            <el-date-picker
              v-model="ruleForm.currentPlatformMoneyDate"
              type="daterange"
              range-separator="-"
              value-format="yyyy-MM-dd"
              start-placeholder="选择开始日期"
              end-placeholder="选择结束日期"
              :disabled="unCheckEditTag || !ruleForm.currentPlatformMoney || ruleForm.currentPlatformMoney == 0"
            />
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20 totalMoney">
        <el-col :span="3" style="padding-left:33px">
          <div class="lh-40"><i class="red">*</i>总轮单费：</div>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="current_contract_round_total_amount">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.current_contract_round_total_amount" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.current_contract_round_total_amount" :maxlength="9" placeholder="请输入" @change="currentContractRoundTotalAmount"></el-input>
          </el-form-item>
        </el-col>
        <el-col :span="2">
          <div class="lh-40"><i class="red">*</i>轮单费：</div>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="currentAmount">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.currentAmount" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.currentAmount" :maxlength="9" placeholder="请输入" ></el-input>
          </el-form-item>
        </el-col>
        <el-col :span="2">
          <div class="lh-40"><i class="red"></i>返现金额：</div>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="current_contract_gift_amount">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.current_contract_gift_amount" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.current_contract_gift_amount" :maxlength="9" placeholder="请输入" ></el-input>
          </el-form-item>
        </el-col>
       </el-row>
       <el-row class="mb-20 totalMoney" v-if="memberTypeVal == 15">
        <el-col :span="4" style="padding-left:33px">
          <div class="lh-40"><i class="red">*</i>本次到账保产值金额：</div>
        </el-col>
        <el-col :span="3">
          <el-form-item prop="current_contract_amount_guaranteed">
            <el-input v-model="ruleForm.current_contract_amount_guaranteed" maxlength="10" placeholder="请输入" :disabled="unCheckEditTag"></el-input>
          </el-form-item>
        </el-col>
       </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">16、余款多少：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="lastMoney">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.lastMoney" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.lastMoney" :maxlength="9" placeholder="请输入"></el-input>
          </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>17、订单承诺量：</div>
        </el-col>
        <el-col :span="3">
          <div class="lh-40">轮单费单价</div>
        </el-col>
        <el-col :span="15">
            <el-form-item prop="roundOrderAmount">
              <el-input
                v-model="ruleForm.roundOrderAmount"
                v-if="unCheckEditTag"
                placeholder="请输入"
                :disabled="true"></el-input>
              <el-input v-model="ruleForm.roundOrderAmount" v-else placeholder="请输入"></el-input>
            </el-form-item>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">18、LOGO位置：</div>
        </el-col>
        <el-col :span="18">
          <el-input v-model="ruleForm.logoPos" :maxlength="15" placeholder="请输入"></el-input>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">19、通栏位置：</div>
        </el-col>
        <el-col :span="18">
          <el-select v-model="ruleForm.bannerPos" placeholder="请选择">
            <el-option
              v-for="item in bannerPosOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value">
            </el-option>
          </el-select>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>20、总合同广告赠送：</div>
        </el-col>
        <el-col :span="18">
          <el-row>
            <div style="display: inline-block">
              <el-form-item prop="giftAdCircleDays">
                <el-col :span="4" class="lh-40">轮显</el-col>
                <el-col :span="8">
                  <el-input v-model="ruleForm.giftAdCircleDays" placeholder="请输入"></el-input>
                </el-col>
                <el-col :span="7" class="lh-40"><span style="padding-left: 10px">天</span></el-col>
                <el-col :span="4"></el-col>
              </el-form-item>
            </div>
            <div style="display: inline-block">
              <el-form-item prop="giftAdBannerDays" style="display: inline-block">
                <el-col :span="4" class="lh-40">通栏</el-col>
                <el-col :span="8">
                  <el-input v-model="ruleForm.giftAdBannerDays" placeholder="请输入"></el-input>
                </el-col>
                <el-col :span="7" class="lh-40"><span style="padding-left: 10px">天</span></el-col>
              </el-form-item>
            </div>
          </el-row>

        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">21、本次上广告情况：</div>
        </el-col>
        <el-col :span="18">
          <el-input
            type="textarea"
            :rows="2"
            :maxlength="50"
            placeholder="请输入"
            v-model="ruleForm.adOnlineNote">
          </el-input>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">22、接单金额及区域：</div>
        </el-col>
        <el-col :span="18">
          <el-input
            type="textarea"
            :rows="2"
            :maxlength="50"
            placeholder="请输入"
            v-model="ruleForm.orderAreaNote">
          </el-input>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>23、家装/公装是否都接：</div>
        </el-col>
        <el-col :span="7">
          <el-form-item prop="isAllOrder">
            <el-select v-model="ruleForm.isAllOrder" placeholder="请选择">
              <el-option
                v-for="item in isAllOrderOptions"
                :key="item.value"
                :label="item.label"
                :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>
        </el-col>
        <el-col :span="11">
          <el-input v-model="ruleForm.acceptOtherOrder" v-if='isAcceptOtherOrder' placeholder="请输入"></el-input>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>24、网店代码：</div>
        </el-col>
        <el-col :span="8">
          <el-form-item prop="onlineShopCode">
            <el-input v-if="unCheckEditTag" v-model="ruleForm.onlineShopCode" placeholder="请输入" :disabled="true"></el-input>
            <el-input v-else v-model="ruleForm.onlineShopCode" placeholder="请输入"></el-input>
          </el-form-item>
        </el-col>
        <el-col :span="3" style="text-align: center;">
          <el-button @click="searchCompanyByIdOrName">验证</el-button>
        </el-col>
        <el-col :span="7" style="position: relative;"><p class="red keftips">{{ shopName }}</p></el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">25、备注：</div>
        </el-col>
        <el-col :span="18">
          <el-input
            type="textarea"
            :rows="5"
            :maxlength="500"
            placeholder="请输入"
            v-model="ruleForm.otherNote">
          </el-input>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40">26、是否需要客服传凭：</div>
        </el-col>
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
        <el-col :span="6">
          <div class="lh-40">27、上传图片：</div>
        </el-col>
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
        <el-col :span="6">
          <div class="lh-40">28、客服上传图片：</div>
        </el-col>
        <el-col :span="18">
          <el-col :span="5" v-for='item in kfuploadImg' :key="item">
            <img :src="item" alt="" class="upimg" @click="preview(item)">
          </el-col>
        </el-col>
      </el-row>
      <el-row class="mb-20">
        <el-col :span="6">
          <div class="lh-40"><i class="red">*</i>29、开站讨论组备注：</div>
        </el-col>
        <el-col :span="18">
          <el-form-item prop="openCityChatRemark">
            <el-input v-model="ruleForm.openCityChatRemark" type="textarea" :rows="5" :maxlength="500" placeholder="请输入"></el-input>
          </el-form-item>
        </el-col>
      </el-row>
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
  import {fetchMemberReportAdd, fetchFindUser, fetchCheckContract, fetchOptions} from '@/api/memberReport'
  import {fetchCityList} from '@/api/common'
  import {checkPhoneNum} from '@/utils/index'
  import {fortmatDate, compareTime} from '@/utils/index'
  import memberReportDetail from '@/mixins/memberReport'
  import QZ_CONFIG from '@/utils/config'

  export default {
    name: 'CommissionMember',
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
      },
      cooperationVal: {
        type: String,
        default: ''
      },
      isQh: {
        type: Boolean,
        default: ''
      }
    },
    mixins: [memberReportDetail],
    data() {
      const validateRatio = (rule, value, callback) => {
        if (parseFloat(value) === 0) {
          callback(new Error('请选择单倍/几倍'))
        } else {
          callback()
        }
      }
      const validateisNewMember = (rule, value, callback) => {
        if (parseInt(value) === 0) {
          callback(new Error('请选择新/老会员'))
        } else {
          callback()
        }
      }
      const validatePhone = (rule, value, callback) => {
        if (!value) {
          callback(new Error('请输入电话'))
        } else if (!checkPhoneNum(value)) {
          callback(new Error('请输入正确的手机号'))
        } else {
          callback()
        }
      }
      const validateAccessOrders = (rule, value, callback) => {
        if (parseInt(value) === 0) {
          callback(new Error('请选择家装/公装是否都接'))
        } else {
          callback()
        }
      }
      const validatePromiseOrders = (rule, value, callback) => {
        if (this.ruleForm.monthPromiseOrder == '' && this.ruleForm.promiseOrder == '') {
          callback(new Error('请输入承诺订单量'))
        } else {
          const reg = /^[+]{0,1}(\d+)$/
          if (this.ruleForm.monthPromiseOrder != '' && !reg.test(this.ruleForm.monthPromiseOrder)) {
            callback(new Error('月承诺订单量请输入正整数'))
          } else {
            callback()
          }
          if (this.ruleForm.promiseOrder != '' && !reg.test(this.ruleForm.promiseOrder)) {
            callback(new Error('总承诺订单量请输入正整数'))
          } else {
            callback()
          }
        }
      }
      const validateInt = (rule, value, callback) => {
        const reg = /^[+]{0,1}(\d+)$/
        if (value && !reg.test(value)) {
          callback(new Error('请输入正整数'))
        } else {
          callback()
        }
      }
      const validateisCompare = (rule, value, callback) => {
        if (parseInt(this.ruleForm.getMoney) > parseInt(this.ruleForm.totalMoney)) {
          callback(new Error('本次到账金额不得大于总合同金额'))
        } else {
          callback()
        }
      }
      const validateisCompareGetMoney = (rule, value, callback) => {
        if (parseInt(this.ruleForm.getMoney)) {
          this.$refs['ruleForm'].validateField('getMoney')
          callback()
        } else {
          callback()
        }
      }
      const validateisAmount = (rule, value, callback) => {
        if (parseInt(this.ruleForm.totalAmount) < parseInt(this.ruleForm.currentAmount)) {
          callback(new Error('本次轮单费不得大于总合同轮单费'))
        } else {
          callback()
        }
      }

      /* const validateLundanheji = (rule, value, callback) => {
          if (parseInt(this.ruleForm.total_contract_round_total_amount) != (parseInt(this.ruleForm.totalAmount) + parseInt(this.ruleForm.total_contract_gift_amount))) {
              callback(new Error('总轮单费与轮单费，返现金额的合计不一致'))
          } else {
              callback()
          }
      }
      const validateLundanheji2 = (rule, value, callback) => {
          if (parseInt(this.ruleForm.current_contract_round_total_amount) != (parseInt(this.ruleForm.currentAmount) + parseInt(this.ruleForm.current_contract_gift_amount))) {
              callback(new Error('总轮单费与轮单费，返现金额的合计不一致'))
          } else {
              callback()
          }
      } */
      // 总轮单费大于轮单费 （总合同）
      const validateLundan = (rule, value, callback) => {
        if (parseInt(this.ruleForm.total_contract_round_total_amount) < parseInt(this.ruleForm.totalAmount)) {
          callback(new Error('轮单费不得大于总轮单费'))
        } else {
          callback()
        }
      }
      // 总合同大于本次 -------轮单费
      const validateLundanComp = (rule, value, callback) => {
        if (parseInt(this.ruleForm.total_contract_round_total_amount) < parseInt(this.ruleForm.current_contract_round_total_amount)) {
          callback(new Error('本次总轮单费不得大于总合同总轮单费'))
        } else {
          callback()
        }
      }
      // 总轮单费大于轮单费（本次）
      const validateLundan2 = (rule, value, callback) => {
        if (parseInt(this.ruleForm.current_contract_round_total_amount) < parseInt(this.ruleForm.currentAmount)) {
          callback(new Error('轮单费不得大于总轮单费'))
        } else {
          callback()
        }
      }

      // 总合同大于本次 -------返现金额
      const validateJineComp = (rule, value, callback) => {
        if (parseInt(this.ruleForm.total_contract_gift_amount) < parseInt(this.ruleForm.current_contract_gift_amount)) {
          callback(new Error('本次返现金额不得大于总合同返现金额'))
        } else {
          callback()
        }
      }
      // 保证金比较
      const validateBond =  (rule, value, callback) => {
        if (parseInt(this.ruleForm.currentBond) > parseInt(this.ruleForm.totalBond)) {
          callback(new Error('本次保证金不得大于总合同保证金'))
        } else {
          callback()
        }
      }
      const valTotalPlatformMoneyDate = (rule, value, callback) => {
        const money = Number(this.ruleForm.totalPlatformMoney) // 总合同平台使用费
        if (money && !value) {
          callback(new Error('请选择平台使用费有效期'))
        } else {
          callback()
        }
      }
      const valCurrentPlatformMoney = (rule, value, callback) => {
        const money = Number(this.ruleForm.totalPlatformMoney) // 总合同平台使用费
        if (money < value) {
          callback(new Error('本次平台使用费不得大于总合同平台使用费'))
        } else {
          callback()
        }
      }
      const valCurrentPlatformMoneyDate = (rule, value, callback) => {
        const money = Number(this.ruleForm.currentPlatformMoney) // 本次合同平台使用费
        if (money && !value) {
          callback(new Error('请选择平台使用费有效期'))
        } else {
          callback()
        }
      }
      // 总保产值验证
      const valTotalContractAmountGuaranteed = (rule, value, callback) => {
        const money = Number(this.ruleForm.total_contract_round_total_amount) // 总总轮单费
        const val = Number(value)
        if (money && val && val < money) {
          callback(new Error('总合同保产值金额不得小于总轮单费'))
        } else {
          callback()
        }
      }
      // 本次保产值验证
      const valCurrentContractAmountGuaranteed = (rule, value, callback) => {
        const vipMoney = Number(this.ruleForm.current_contract_round_total_amount) // 本次总轮单费
        const money = Number(this.ruleForm.total_contract_amount_guaranteed) // 总保产值
        const val = Number(value)
        if (vipMoney && val && val < vipMoney) {
          callback(new Error('本次合同保产值金额不得小于本次总轮单费'))
        } else if (money && val && money < val) {
          callback(new Error('本次到账保产值金额不得大于总合同保产值金额'))
        } else {
          callback()
        }
      }
      /* const validateisTotalCompare = (rule, value, callback) => {
        if (parseInt(this.ruleForm.totalMoney) != (Number(this.ruleForm.totalFixed) + parseInt(this.ruleForm.totalBond) + parseInt(this.ruleForm.totalAmount))) {
          callback(new Error('总合同金额与轮单费、保证金、服务费合计不一致'))
        } else {
          callback()
        }
      }
      const validateisCurrentCompare = (rule, value, callback) => {
        if(parseInt(this.ruleForm.getMoney) != (Number(this.ruleForm.currentFixed) + parseInt(this.ruleForm.currentBond) + parseInt(this.ruleForm.currentAmount))) {
          callback(new Error('本次到账金额与轮单费、保证金、服务费合计不一致'))
        } else {
          callback()
        }
      }
      const validateisTotalCompareMoney = (rule, value, callback) => {
        if (parseInt(this.ruleForm.totalMoney)) {
          this.$refs['ruleForm'].validateField('totalMoney')
          callback()
        } else {
          callback()
        }
      }
      const validateisCurrentCompareMoney = (rule, value, callback) => {
        if (parseInt(this.ruleForm.getMoney)) {
          this.$refs['ruleForm'].validateField('getMoney')
          callback()
        } else {
          callback()
        }
      } */
      return {
        ruleForm: {
          id: '',
          cooperation_type: '',
          companyName: '',
          citySelectStr: '',
          ratio: '',
          backRatio: '',
          isNewMember: '0',
          contact: '',
          duties: '',
          phone: '',
          wechat: '',
          contractTime: '', // 总合同时间数组
          contractTimeBegin: '',
          contractTimeEnd: '',
          memberTime: '', // 本次会员时间
          memberTimeBegin: '',
          memberTimeEnd: '',
          moneyInTime: '',
          nextpayTime: '',
          totalMoney: '',
          totalPlatformMoney: '',
          totalPlatformMoneyDate: '',
          currentPlatformMoney: '',
          currentPlatformMoneyDate: '',
          getMoney: '',
          lastMoney: '',
          logoPos: '',
          bannerPos: '0',
          giftAdCircleDays: '',
          giftAdBannerDays: '',
          adOnlineNote: '',
          orderAreaNote: '',
          isAllOrder: '0',
          monthPromiseOrder: '',
          promiseOrder: '',
          roundOrderAmount: '', // 轮单费单价
          time_begin: '',
          time_end: '',
          onlineShopCode: '',
          otherNote: '',
          acceptOtherOrder: '',
          totalBond: '',
          totalAmount: '',
          total_contract_round_total_amount: '', // 新增总轮单费
          total_contract_gift_amount: '',
          currentBond: '',
          currentAmount: '',
          current_contract_round_total_amount: '', // 新增本次总轮单费
          current_contract_gift_amount: '',
          contractRemark: '',
          totalContractRemark: '',
          is_abnormal: '',
          status: '',
          kfVoucher: '0',
          openCityChatRemark: '',
          total_contract_amount_guaranteed: '', // 总保产值
          current_contract_amount_guaranteed: '' // 本次保产值
        },
        rules: {
          companyName: [
            { required: true, message: '请输入公司名称', trigger: 'change' }
          ],
          citySelectStr: [
            { required: true, message: '请输入城市', trigger: 'change' }
          ],
          ratio: [
            { validator: validateRatio, trigger: 'change' }
          ],
          isNewMember: [
            { validator: validateisNewMember, trigger: 'change' }
          ],
          contact: [
            { required: true, message: '请输入联系人', trigger: 'change' }
          ],
          phone: [
            { validator: validatePhone, trigger: 'blur' }
          ],
           contractTimeBegin: [
            { required: true, message: '请选择总合同开始时间', trigger: 'change' }
          ],
          contractTimeEnd: [
            { required: true, message: '请选择总合同结束时间', trigger: 'change' }
          ],
          memberTimeBegin: [
            { required: true, message: '请选择本次会员开始时间', trigger: 'change' }
          ],
          memberTimeEnd: [
            { required: true, message: '请选择本次会员结束时间', trigger: 'change' }
          ],
          moneyInTime: [
            { required: true, message: '请选择本次款项到账时间', trigger: 'change' }
          ],
          totalMoney: [
            { required: true, message: '请输入总合同金额', trigger: 'change' },
            { validator: validateInt, trigger: 'change' },
            { validator: validateisCompareGetMoney, trigger: ['blur', 'change'] },
             // { validator: validateisTotalCompare, trigger: ['blur', 'change'] }
          ],
          // 总平台使用费
          totalPlatformMoney: [
            { validator: validateInt, trigger: 'change' }
          ],
          // 总平台使用费有效期
          totalPlatformMoneyDate: [
            { validator: valTotalPlatformMoneyDate, trigger: ['blur', 'change'] }
          ],
          totalBond: [
            { required: true, message: '请输入总合同保证金', trigger: 'change' },
            { validator: validateInt, trigger: 'change' }
          ],
          totalAmount: [
            { required: true, message: '请输入总合同轮单费', trigger: 'change' },
            { validator: validateInt, trigger: 'change' },
            { validator: validateisAmount, trigger: ['blur', 'change'] }
            // { validator: validateLundan, trigger: ['blur', 'change'] }
          ],
          // 总合同总轮单费
          total_contract_round_total_amount: [
            { required: true, message: '请输入总合同总轮单费', trigger: 'change' },
            { validator: validateInt, trigger: 'change' },
            // { validator: validateLundan, trigger: ['blur', 'change'] },
            { validator: validateLundanComp, trigger: ['blur', 'change'] }
            // { validator: validateLundanheji, trigger: ['blur', 'change'] }
          ],
          total_contract_gift_amount: [
            { validator: validateInt, trigger: 'change' },
            { validator: validateJineComp, trigger: ['blur', 'change'] }
          ],
          total_contract_amount_guaranteed: [
            { required: true, message: '请输入总合同保产值金额', trigger: 'change' },
            { validator: validateInt, trigger: 'change' },
            { validator: valTotalContractAmountGuaranteed, trigger: 'change' }
          ],
          // 结束
          getMoney: [
            { required: true, message: '请输入本次到账金额', trigger: 'change' },
            { validator: validateInt, trigger: 'change' },
            { validator: validateisCompare, trigger: 'change' }
            // { validator: validateisCurrentCompare, trigger: ['blur', 'change'] }
          ],
          // 本次平台使用费
          currentPlatformMoney: [
            { validator: validateInt, trigger: 'change' },
            { validator: valCurrentPlatformMoney, trigger: 'change' }
          ],
          // 本次平台使用费有效期
          currentPlatformMoneyDate: [
            { validator: valCurrentPlatformMoneyDate, trigger: ['blur', 'change'] }
          ],
          currentBond: [
            { required: true, message: '请输入本次保证金', trigger: 'change' },
            { validator: validateInt, trigger: 'change' },
            { validator: validateBond, trigger: 'change' }
          ],
          currentAmount: [
            { required: true, message: '请输入本次轮单费', trigger: 'change' },
            { validator: validateInt, trigger: 'change' },
            { validator: validateisAmount, trigger: ['blur', 'change'] }
            // { validator: validateLundan2, trigger: ['blur', 'change'] }
          ],
          // 本次总轮单费
          current_contract_round_total_amount: [
            { required: true, message: '请输入本次合同总轮单费', trigger: 'change' },
            { validator: validateInt, trigger: 'change' },
            // { validator: validateLundan2, trigger: ['blur', 'change'] },
            { validator: validateLundanComp, trigger: ['blur', 'change'] }
            // { validator: validateLundanheji2, trigger: ['blur', 'change'] }
          ],
          current_contract_gift_amount: [
            { validator: validateInt, trigger: 'change' },
            { validator: validateJineComp, trigger: ['blur', 'change'] }
          ],
          current_contract_amount_guaranteed: [
            { required: true, message: '请输入本次到账保产值金额', trigger: 'change' },
            { validator: validateInt, trigger: 'change' },
            { validator: valCurrentContractAmountGuaranteed, trigger: 'change' }
          ],
          lastMoney: [
            { validator: validateInt, trigger: 'change' }
          ],
          giftAdCircleDays: [
            { required: true, message: '请输入广告赠送', trigger: 'change' },
            { validator: validateInt, trigger: 'change' }
          ],
          giftAdBannerDays: [
            { required: true, message: '请输入广告赠送', trigger: 'change' },
            { validator: validateInt, trigger: 'change' }
          ],
          isAllOrder: [
            { validator: validateAccessOrders, trigger: 'change' }
          ],
          roundOrderAmount: [
            { required: true, message: '请输入轮单费单价', trigger: 'change' }
          ],
          onlineShopCode: [
            { required: true, message: '请输入网店代码', trigger: 'change' }
          ],
          openCityChatRemark: [
            { required: true, message: '请输入开站讨论组备注', trigger: 'blur' }
          ]
        },
        backRatioOptions: [],
        isNewMemberOptions: [{
          value: '0',
          label: '请选择'
        }, {
          value: '1',
          label: '新会员'
        }, {
          value: '2',
          label: '老会员'
        }],
        emptyOrderOptions: [{
          value: '',
          label: '请选择'
        }, {
          value: '2020/1/5至2020/2/3',
          label: '2020/1/5至2020/2/3'
        }, {
          value: '2021/1/21至2021/2/20',
          label: '2021/1/21至2021/2/20'
        }, {
          value: '2022/1/12至2022/2/10',
          label: '2022/1/12至2022/2/10'
        }, {
          value: '2023/1/2至2023/1/31',
          label: '2023/1/2至2023/1/31'
        }, {
          value: '2024/1/21至2024/2/19',
          label: '2024/1/21至2024/2/19'
        }],
        bannerPosOptions: [{
          value: '0',
          label: '请选择'
        }, {
          value: '1',
          label: 'A通栏'
        }, {
          value: '2',
          label: 'B通栏'
        }, {
          value: '3',
          label: 'A+B通栏'
        }],
        isAllOrderOptions: [{
          value: '0',
          label: '请选择'
        }, {
          value: '1',
          label: '只接家装'
        }, {
          value: '2',
          label: '只接公装'
        }, {
          value: '3',
          label: '家装公装都接'
        }, {
          value: '4',
          label: '其它'
        }],
        memberTypeOptions: [],
        kfVoucherOptions: [{
          value: '0',
          label: '否'
        }, {
          value: '1',
          label: '是'
        }],
        memberTypeVal: '1',
        submitStatus: 1, // 保存状态为1，提交状态为2
        shopName: '',
        unCheckEditTag: false,
        loading: false,
        uploadExtendData: {
          prefix: 'sale_report'
        },
        uploadedImg: [],
        kfuploadImg: [],
        previewTemp: [],
        uploadContentType: {
          'token': localStorage.getItem('token')
        },
        fileList: [],
        isAcceptOtherOrder: false,
        backOrRatio: true,
        contranDay: '',
        tranAB: '',
        memberDay: '',
        memberAB: '',
        cs: '',
        citySelectStr: '',
        citySelectVal: '',
        citys: [],
        cityBlurFlag: null,
        report_payment_id: '',
        upload_img_url: this.$qzconfig.base_api + '/uploads/upimg',
        pickerOptionsStart: {
          disabledDate(time) {
              return time.getTime() < new Date(new Date().toLocaleDateString()).getTime();
          }
        }
      }
    },
    watch: {
      'memberTypeId': {
        handler(val){
          this.memberTypeVal = val
        },
        immediate: true
      },
      'ruleForm.onlineShopCode'(value) {
        if (!value) {
          this.shopName = ''
        }
      },
      'ruleForm.contractTimeBegin'(val) {
        let begin = new Date(this.ruleForm.contractTimeBegin).getTime()
        let end = new Date(this.ruleForm.contractTimeEnd).getTime()
        if (!isNaN(end - begin)) {
          let contranDay = (end - begin) / 86400000
          let tranA = Math.floor(contranDay / 30)
          let tranB = contranDay % 30 ? parseInt(contranDay % 30) + '天' : ''
          let tranAB = tranA ? tranA + '月' + tranB : tranB
          this.contranDay = contranDay > -1 ? contranDay : ''
          this.tranAB = tranAB
        }
        if (begin == 0) {
          this.contranDay = ''
        }
        if (this.ruleForm.contractTimeEnd) {
          if (!compareTime(val, this.ruleForm.contractTimeEnd)) {
            this.$message.error('开始时间不能大于结束时间')
            this.ruleForm.contractTimeBegin = ''
          }
        }
      },
      'ruleForm.contractTimeEnd'(val) {
        let begin = new Date(this.ruleForm.contractTimeBegin).getTime()
        let end = new Date(this.ruleForm.contractTimeEnd).getTime()
        if (!isNaN(end - begin)) {
          let contranDay = (end - begin) / 86400000
          let tranA = Math.floor(contranDay / 30)
          let tranB = contranDay % 30 ? parseInt(contranDay % 30) + '天' : ''
          let tranAB = tranA ? tranA + '月' + tranB : tranB
          this.tranAB = tranAB
          this.contranDay = contranDay > -1 ? contranDay : ''
        }
        if (this.ruleForm.contractTimeBegin) {
          if (!compareTime(this.ruleForm.contractTimeBegin, val)) {
            this.$message.error('结束时间不能小于开始时间')
            this.ruleForm.contractTimeEnd = ''
          }
        }
      },
      'ruleForm.memberTimeBegin'(val) {
        let begin = new Date(this.ruleForm.memberTimeBegin).getTime()
        let end = new Date(this.ruleForm.memberTimeEnd).getTime()
        if (!isNaN(end - begin)) {
          let memberDay = (end - begin) / 86400000
          let tranA = Math.floor(memberDay / 30)
          let tranB = memberDay % 30 ? parseInt(memberDay % 30) + '天' : ''
          let memberAB = tranA ? tranA + '月' + tranB : tranB
          this.memberAB = memberAB
          this.memberDay = memberDay > -1 ? memberDay : ''
        }
        if (begin == 0) {
          this.memberDay = ''
        }
        if (this.ruleForm.memberTimeEnd) {
          if (!compareTime(val, this.ruleForm.memberTimeEnd)) {
            this.$message.error('开始时间不能大于结束时间')
            this.ruleForm.memberTimeBegin = ''
          }
        }
        this.checkContract()
      },
      'ruleForm.memberTimeEnd'(val) {
        let begin = new Date(this.ruleForm.memberTimeBegin).getTime()
        let end = new Date(this.ruleForm.memberTimeEnd).getTime()
        if (!isNaN(end - begin)) {
          let memberDay = (end - begin) / 86400000
          let tranA = Math.floor(memberDay / 30)
          let tranB = memberDay % 30 ? parseInt(memberDay % 30) + '天' : ''
          let memberAB = tranA ? tranA + '月' + tranB : tranB
          this.memberAB = memberAB
          this.memberDay = memberDay > -1 ? memberDay : ''
        }
        if (this.ruleForm.memberTimeBegin) {
          if (!compareTime(this.ruleForm.memberTimeBegin, val)) {
            this.$message.error('结束时间不能小于开始时间')
            this.ruleForm.memberTimeEnd = ''
          }
        }
        this.checkContract()
      },
      'ruleForm.isAllOrder'(val) {
        if (parseInt(val) === 4) {
          this.isAcceptOtherOrder = true
        } else {
          this.isAcceptOtherOrder = false
        }
      /* },
      'ruleForm.total_contract_round_total_amount'(val) {
        let reg = /^[+]{0,1}(\d+)$/
        if(reg.test(val)){
          this.ruleForm.total_contract_amount_guaranteed = Number(val)*10
        }
      },
      'ruleForm.current_contract_round_total_amount'(val) {
        let reg = /^[+]{0,1}(\d+)$/
        if(reg.test(val)){
          this.ruleForm.current_contract_amount_guaranteed = Number(val)*10
        } */
      }
    },
    mounted() {
      this.loadAllCity()
    },
    created() {
      this.fetchOptions()
      // 根据编辑还是添加标识符确定是否需要请求数据
      if (this.operationActionTag === 'edit') {
        this.id = this.$route.query.id
        this.getMemberReportDetail()
        if (this.$route.query && this.$route.query.status && parseInt(this.$route.query.status) === 6) {
          this.unCheckEditTag = true
        }
        this.$nextTick(() => {
          const el = document.getElementsByClassName('tags-view-item')[0]
          el.innerHTML = el.innerHTML.replace('添加', '编辑')
        })
      }
      const smallReport = JSON.parse(localStorage.getItem('smallReport'))
      if (smallReport) {
        this.ruleForm.companyName = smallReport.company_name
        this.ruleForm.ratio = smallReport.viptype
        this.ruleForm.getMoney = smallReport.payment_money
        this.ruleForm.moneyInTime = smallReport.payment_date
        this.ruleForm.backRatio = smallReport.back_ratio
        this.report_payment_id = smallReport.report_payment_id
        this.ruleForm.citySelectStr = smallReport.citySelectStr
        this.ruleForm.current_contract_round_total_amount = smallReport.deposit_to_round_money
        this.ruleForm.currentAmount = smallReport.deposit_to_round_money
        this.citySelectVal = smallReport.citySelectVal
      }
    },
    destroyed() {
      localStorage.removeItem('smallReport')
    },
    methods: {
      totalContractRoundTotalAmount(val) {
        let reg = /^[+]{0,1}(\d+)$/
        if(reg.test(val)){
          this.ruleForm.total_contract_amount_guaranteed = Number(val)*10
        }
      },
      currentContractRoundTotalAmount(val) {
        let reg = /^[+]{0,1}(\d+)$/
        if(reg.test(val)){
          this.ruleForm.current_contract_amount_guaranteed = Number(val)*10
        }
      },
      fetchOptions() {
        fetchOptions().then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            const datas = res.data.data
            const ratioList = datas.back_ratio_list.map((item) => {
              return {
                value: item,
                label: item
              }
            })
            const memberTypeOptions = datas.cooperation_type_list.map((item) => {
              return {
                value: item.id,
                label: item.name
              }
            })
            const arr = [
              {
                value: '',
                label: '请选择'
              }
            ]
            this.backRatioOptions = [...arr, ...ratioList]
            this.memberTypeOptions = memberTypeOptions
          } else {
            this.$message.error(res.data.error_msg)
          }
        })
      },
      optionChange() { // 切换模板
        if(parseInt(this.memberTypeVal) !== 6 && parseInt(this.memberTypeVal) !== 15){
          this.$emit('refreshList',this.memberTypeVal+'',this.memberTypeId,this.ruleForm.memberType,false)
        }
      },
      loadAllCity() {
        fetchCityList().then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            const data = res.data.data[0]
            data.forEach((item, index) => {
              this.citys.push(item)
            })
          } else {
            this.citys = []
          }
        })
      },
      handleSelect(item) {
        this.cityBlurFlag = item
        this.citySelectVal = item.cid
        this.citySelectStr = item.value
        this.ruleForm.citySelectStr = item.true_cname
      },
      queryCity(queryString, cb) {
        const citys = this.citys
        const results = queryString ? citys.filter(this.createFilter(queryString)) : citys
        this.cityBlurFlag = null
        cb(results)
      },
      createFilter(queryString) {
        return (city) => {
          return (city.value.toLowerCase().indexOf(queryString.toLowerCase()) > -1)
        }
      },
      searchCompanyByIdOrName() {
        if (!this.ruleForm.onlineShopCode) {
          this.$message.error('请输入网店代码')
          return
        }
        fetchFindUser({
          uid: this.ruleForm.onlineShopCode
        }).then(res => {
          const data = res.data.data[0]
          if (data && data.length > 0) {
            this.shopName = '匹配到公司名称为：' + data[0].qc + ' 注册地点：' + data[0].cname
          } else {
            this.shopName = '抱歉未匹配到公司名称'
          }
        })
      },
      submitForm(formName) {

        this.$refs[formName].validate((valid) => {
          if (valid) {
            this.handleAjax()
          } else {
            // console.log('error submit!!');
            return false;
          }
        });
      },
      handleAjax() {
        // queryObj有可能返回一个false
        const queryObj = this.handleArguments()

        // return
        if (!queryObj) {
          return
        }
        this.loading = true
        fetchMemberReportAdd(queryObj).then(res => {
          if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
            this.$message({
              type: 'success',
              message: '保存成功'
            })
            localStorage.removeItem('smallReport')
            setTimeout(() => {
              // window.close()
              this.$router.push('/memberReport/list')
            }, 1000)
          } else {
            this.$message.error(res.data.error_msg)
          }
          this.loading = false
        }).catch(res => {
          this.$message.error('网络异常，请稍后再试')
          this.loading = false
        })
      },
      handleArguments() {
        if (this.unCheckEditTag) {
          // 当状态等于6，即”客服未通过，普通信息更改“，需要手动将提交的状态码改成3
          this.submitStatus = 3
        }
        if (this.uploadedImg && this.uploadedImg.length >= 0) {
          if (Array.isArray(this.uploadedImg)) {
            this.uploadedImg = this.uploadedImg.join(',')
          }
        } else {
          this.uploadedImg = []
        }
        const queryObj = {
          id: this.id,
          report_payment_id: this.report_payment_id,
          company_name: this.ruleForm.companyName,
          cooperation_type: this.memberTypeId,
          city_name: this.ruleForm.citySelectStr,
          cs: this.citySelectVal,
          back_ratio: this.ruleForm.backRatio,
          viptype: this.ruleForm.ratio,
          is_new: this.ruleForm.isNewMember,
          company_contact: this.ruleForm.contact,
          company_contact_position: this.ruleForm.duties,
          company_contact_phone: this.ruleForm.phone,
          company_contact_weixin: this.ruleForm.wechat,
          contract_start: fortmatDate(this.ruleForm.contractTimeBegin),
          contract_end: fortmatDate(this.ruleForm.contractTimeEnd),
          current_contract_start: fortmatDate(this.ruleForm.memberTimeBegin),
          current_contract_end: fortmatDate(this.ruleForm.memberTimeEnd),
          current_payment_time: fortmatDate(this.ruleForm.moneyInTime),
          total_contract_amount: Number(this.ruleForm.totalMoney),
          next_payment_time: this.ruleForm.nextpayTime ? fortmatDate(this.ruleForm.nextpayTime) : '',
          current_contract_amount: Number(this.ruleForm.getMoney),

          total_contract_bond: this.ruleForm.totalBond,
          total_contract_round_amount: this.ruleForm.totalAmount,
          total_contract_round_total_amount: this.ruleForm.total_contract_round_total_amount,
          total_contract_gift_amount: this.ruleForm.total_contract_gift_amount == '' ? 0 : this.ruleForm.total_contract_gift_amount,

          current_contract_bond: this.ruleForm.currentBond,
          current_contract_round_amount: this.ruleForm.currentAmount,
          current_contract_round_total_amount: this.ruleForm.current_contract_round_total_amount,
          current_contract_gift_amount: this.ruleForm.current_contract_gift_amount == '' ? 0 : this.ruleForm.current_contract_gift_amount,

          // 平台使用费
          total_platform_money: this.ruleForm.totalPlatformMoney,
          total_platform_money_start_date: this.ruleForm.totalPlatformMoneyDate ? this.ruleForm.totalPlatformMoneyDate[0] : '',
          total_platform_money_end_date: this.ruleForm.totalPlatformMoneyDate ? this.ruleForm.totalPlatformMoneyDate[1] : '',
          current_platform_money: this.ruleForm.currentPlatformMoney,
          current_platform_money_start_date: this.ruleForm.currentPlatformMoneyDate ? this.ruleForm.currentPlatformMoneyDate[0] : '',
          current_platform_money_end_date: this.ruleForm.currentPlatformMoneyDate ? this.ruleForm.currentPlatformMoneyDate[1] : '',


          total_contract_amount_guaranteed: this.ruleForm.total_contract_amount_guaranteed, // 总保产值
          current_contract_amount_guaranteed: this.ruleForm.current_contract_amount_guaranteed, // 本次保产值

          // 跟后端协商不填的情况下传-1
          lave_amount: this.ruleForm.lastMoney === '' ? -1 : this.ruleForm.lastMoney,
          logo_address: this.ruleForm.logoPos,
          passage_position: parseInt(this.ruleForm.bannerPos) === 0 ? '' : this.ruleForm.bannerPos,
          contract_handsel_passage: this.ruleForm.giftAdBannerDays,
          contract_handsel_banner: this.ruleForm.giftAdCircleDays,
          current_adver_content: this.ruleForm.adOnlineNote,
          amount_and_area: this.ruleForm.orderAreaNote,
          lxs: this.ruleForm.isAllOrder,
          lxs_remark: this.ruleForm.acceptOtherOrder,

          round_order_amount: this.ruleForm.roundOrderAmount,

          company_id: this.ruleForm.onlineShopCode,
          remarke: this.ruleForm.otherNote,
          status: this.submitStatus,
          imgs: this.uploadedImg,
          contract_remark: this.ruleForm.contractRemark,
          total_contract_remark: this.ruleForm.totalContractRemark,
          is_kf_voucher: this.ruleForm.kfVoucher,
          open_city_chat_remark: this.ruleForm.openCityChatRemark
        }
        if (this.operationActionTag === 'edit') {
          queryObj.cooperation_type = this.memberTypeVal
        }
        if (parseInt(this.memberTypeVal) === 6) {
          queryObj.viptype = ''
        } else if (parseInt(this.memberTypeVal) === 15) {
          queryObj.viptype = ''
          queryObj.backOrRatio = '1.0'
        } else {
          queryObj.backOrRatio = ''
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
        localStorage.removeItem('smallReport')
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
        for (var i = 0; i < ret.img_list.length; i++) {
          let obj = {url: QZ_CONFIG.oss_img_host + ret.img_list[i]}
          this.fileList.push(obj)
        }
        this.uploadedImg = ret.img_list
        for (var i = 0; i < ret.kf_voucher_img.length; i++) {
          this.kfuploadImg[i] = QZ_CONFIG.oss_img_host + ret.kf_voucher_img[i]
        }
        this.kfuploadImg = this.kfuploadImg
        this.ruleForm.companyName = ret.company_name
        if(this.isQh) {
          this.memberTypeVal = ret.cooperation_type
        } else {
          this.memberTypeVal = parseInt(this.cooperationVal)
        }
        this.ruleForm.memberType = ret.cooperation_type_name
        this.citySelectVal = ret.cs
        this.ruleForm.citySelectStr = ret.city_name
        this.ruleForm.ratio = String(ret.viptype) === '0.0' ? '' : ret.viptype
        this.ruleForm.backRatio = ret.back_ratio
        this.ruleForm.isNewMember = !ret.is_new ? '0' : String(ret.is_new)
        this.ruleForm.contact = ret.company_contact
        this.ruleForm.duties = ret.company_contact_position
        this.ruleForm.phone = ret.company_contact_phone
        this.ruleForm.wechat = ret.company_contact_weixin
        this.ruleForm.contractTimeBegin = fortmatDate(ret.contract_start * 1000)
        this.ruleForm.contractTimeEnd = fortmatDate(ret.contract_end * 1000)
        this.ruleForm.memberTimeBegin = fortmatDate(ret.current_contract_start * 1000)
        this.ruleForm.memberTimeEnd = fortmatDate(ret.current_contract_end * 1000)
        this.ruleForm.moneyInTime = fortmatDate(ret.current_payment_time * 1000)
        this.ruleForm.nextpayTime = fortmatDate(ret.next_payment_time * 1000)
        this.ruleForm.totalMoney = ret.total_contract_amount
        this.ruleForm.getMoney = ret.current_contract_amount

        this.ruleForm.totalBond = ret.total_contract_bond
        this.ruleForm.totalAmount = ret.total_contract_round_amount
        this.ruleForm.total_contract_round_total_amount = ret.total_contract_round_total_amount
        this.ruleForm.total_contract_gift_amount = ret.total_contract_gift_amount


        this.ruleForm.currentBond = ret.current_contract_bond
        this.ruleForm.currentAmount = ret.current_contract_round_amount
        this.ruleForm.current_contract_round_total_amount = ret.current_contract_round_total_amount
        this.ruleForm.current_contract_gift_amount = ret.current_contract_gift_amount

        // 平台使用费
        this.ruleForm.totalPlatformMoney = ret.total_platform_money // 总合同平台使用费
        if (ret.total_platform_money_start_date && ret.total_platform_money_end_date) {
          this.ruleForm.totalPlatformMoneyDate = [ret.total_platform_money_start_date, ret.total_platform_money_end_date] // 总合同平台使用费有效期
        } else {
          this.ruleForm.totalPlatformMoneyDate = '' // 总合同平台使用费有效期
        }
        this.ruleForm.currentPlatformMoney = ret.current_platform_money // 本次平台使用费
        if (ret.current_platform_money_start_date && ret.current_platform_money_end_date) {
          this.ruleForm.currentPlatformMoneyDate = [ret.current_platform_money_start_date, ret.current_platform_money_end_date] // 本次合同平台使用费有效期
        } else {
          this.ruleForm.currentPlatformMoneyDate = ''
        }

        this.ruleForm.total_contract_amount_guaranteed = ret.total_contract_amount_guaranteed // 总保产值
        this.ruleForm.current_contract_amount_guaranteed = ret.current_contract_amount_guaranteed // 本地保产值

        this.ruleForm.lastMoney = String(ret.lave_amount) === '-1' ? '' : ret.lave_amount
        this.ruleForm.logoPos = ret.logo_address
        this.ruleForm.bannerPos = String(ret.passage_position)
        this.ruleForm.giftAdCircleDays = ret.contract_handsel_banner
        this.ruleForm.giftAdBannerDays = ret.contract_handsel_passage
        this.ruleForm.adOnlineNote = ret.current_adver_content
        this.ruleForm.orderAreaNote = ret.amount_and_area
        this.ruleForm.isAllOrder = String(ret.lxs)
        if (ret.is_abnormal == 1) {
          this.ruleForm.is_abnormal = 'red'
        }
        if (parseInt(ret.lxs) === 4) {
          this.ruleForm.acceptOtherOrder = ret.lxs_remark
        }
        this.ruleForm.roundOrderAmount = ret.round_order_amount

        this.ruleForm.onlineShopCode = ret.company_id
        this.ruleForm.otherNote = ret.remarke
        this.ruleForm.contractRemark = ret.contract_remark,
        this.ruleForm.totalContractRemark = ret.total_contract_remark
        this.loading = false
        this.ruleForm.openCityChatRemark = ret.open_city_chat_remark
      },
      handleUploadSuccess(res, file, fileList) {
        this.uploadedImg.push(res.data.img_path)
      },
      handleRemove(res, file, fileList) {
        this.uploadedImg.forEach((item, index) => {
          if (res.url.indexOf(item) != -1) {
            this.uploadedImg.splice(index, 1)
          }
        })
      },
      preview(item) {
        this.previewTemp = []
        this.previewTemp.push(item)
        this.dialogVisiblePreview = true
      },
      checkContract() {
        let memberTimeBegin = this.ruleForm.memberTimeBegin
        let memberTimeEnd = this.ruleForm.memberTimeEnd
        let citySelectStr = this.ruleForm.citySelectStr
        let ratio = this.ruleForm.ratio
        let getMoney = this.ruleForm.getMoney
        let cooperation_type = this.memberTypeVal

        if (memberTimeBegin != '' && memberTimeEnd != '' && citySelectStr != '' && ratio != '' && getMoney != '') {
          fetchCheckContract({
            cooperation_type: cooperation_type,
            city_name: citySelectStr,
            current_contract_amount: getMoney,
            current_contract_start: memberTimeBegin,
            current_contract_end: memberTimeEnd,
            viptype: ratio
          }).then(res => {
            if (res.data.error_code === 0) {
              if (res.data.data.info.is_abnormal == 1) {
                this.ruleForm.is_abnormal = 'red'
              } else {
                this.ruleForm.is_abnormal = 'default'
              }
            }
          })
        }
      },
      isNewMemberChange(val) {
        if (val === '1') {
          this.ruleForm.kfVoucher = '0'
        } else if (val === '2') {
          this.ruleForm.kfVoucher = '1'
        }
      }
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
  .commission-member {
    .text-center {
      text-align: center;
    }

    .keftips {
      width: 600px;
      position: absolute;
      left: 30px;
      top: -6px;
    }

    .el-range-separator {
      padding: 0;
    }
    .el-form-item__content .el-date-editor.w250{
      width: 250px;
    }

    .exclude-time {
      position: absolute;
      width: 850px;
      left: 0px;
      top: 55px;
    }

    .el-form-item {
      margin-bottom: 0 !important;
      .el-form-item__error{
        white-space: nowrap;
      }
    }

    .el-form-item__content {
      margin-left: 0 !important;
    }

    .month-promise {
      width: 76%;
      margin-left: 3%;
    }

    .sum-promise {
      width: 77%;
      margin-top: 20px;
      margin-left: 2%;
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

    .inline-block {
      display: inline-block;
    }

    .order-width {
      width: 120px;
    }

    .order-time-width {
      width: 150px;
    }

    .total-contract {
      margin-bottom: 20px;
    }

    .fixwidth01 {
      width: 80px;
    }

    .total-contract {
      margin-right: -315px;
      width: 1172px;
    }

    .current-contract {
      margin-right: -315px;
      width: 1172px;
    }

    .note {
      margin-top: 20px;
    }

    .ml-20 {
      margin-left: 20px;
    }

    .beizhu {
      margin-top: 18px;
    }

    .beizhu.w105 {
      width: 105%;
    }

    .upimg {
      display: inline-block;
      width: 100px;
      height: 100px;
      margin-right: 10px;
    }

    .el-date-editor.el-input.red input {
      color: red;
    }
    .totalMoney{
      width: 1220px;
    }
    .to-line{
      margin-left: 37px;
    }
  }

</style>
