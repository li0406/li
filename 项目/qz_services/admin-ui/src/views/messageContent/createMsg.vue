<template>
    <div class="create-msg">
        <div class="qian-main">
            <div class="addMsg">新增消息内容</div>
            <div>
                <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="100px" class="demo-ruleForm">
                    <el-form-item label="消息描述" prop="name" :label-width="formLabelWidth">
                        <el-input 
                        v-model="ruleForm.name" 
                        placeholder="请输入消息描述" 
                        maxlength="50"
                        class="biaodan"></el-input>
                        <span style="color:#ddd">{{this.ruleForm.name.length}}/50</span>
                    </el-form-item>
                    <el-form-item label="消息主标题" prop="title">
                        <el-input 
                        v-model="ruleForm.title"  
                        placeholder="请输入消息主标题"
                        maxlength="30"
                        class="biaodan"></el-input>
                        <span style="color:#ddd">{{this.ruleForm.title.length}}/30</span>
                    </el-form-item>
                    <el-form-item label="消息副标题" prop="sub_title">
                        <el-input 
                            v-model="ruleForm.sub_title"  
                            placeholder="请输入消息副标题" 
                            maxlength="50"
                            class="biaodan"></el-input>
                        <span style="color:#ddd">{{this.ruleForm.sub_title.length}}/50</span>
                    </el-form-item>
                    <el-form-item label="消息内容" prop="body">
                        <el-input type="textarea" :rows="4" 
                            v-model="ruleForm.body" 
                            placeholder="请填写消息任务消息内容，不得超过500字"
                            maxlength="500"
                            class="biaodan"></el-input>
                        <span style="color:#ddd">{{this.ruleForm.body.length}}/500</span>
                    </el-form-item>
                 
                    <el-form-item label="消息图片" prop="subTitle">
                        <el-upload
                            el-upload
                            ref="upload"
                            :limit="1"
                            :auto-upload="true"
                            :on-exceed="handleExceed"
                            :on-preview="handlePictureCardPreview"
                            :on-remove="handleRemove"
                            :on-success="handleAvatarSuccess"
                            :before-upload="beforeAvatarUpload"
                            :file-list="ruleForm.images"
                            :http-request="handleUploadImg"
                            accept=".jpg, .jpeg, .png, .JPG, .JPEG"
                            action
                            list-type="picture-card"
                            style="float:left"
                            >
                            <i class="el-icon-plus"></i>
                        </el-upload>
                        <!-- <el-button class="reUp" @click="resetUpload()">重新上传</el-button> -->
                    </el-form-item>
                    <p class="imgtext">图片封面仅支持jpg、png,格式72*72/96*96/144x144</p>
                    <el-form-item label="消息跳转链接" prop="link">
                        <el-checkbox-group v-model="ruleForm.link" style="width: 6%;float: left;">
                            <el-checkbox label="需跳转链接" name="link" @change="link = !link"></el-checkbox>
                        </el-checkbox-group>
                        <el-tooltip placement="top">
                            <div slot="content">使用 3D Touch 或查看详情后展示消息内容，支持图片、<br/>视频、音乐等。仅适用于 iOS 10 以上的设备。</div>
                             <span class="tishi">?</span>
                        </el-tooltip>
                       <!-- 填写key value -->
                        <span v-if="link" >
                            <el-form-item label="Key" prop="skip_key" class="linkZ">
                                <el-input v-model="ruleForm.skip_key" placeholder="请输入Key" style="width:200px;"></el-input>
                            </el-form-item>
                            <el-form-item label="Value" prop="skip_url" class="linkZ">
                                <el-input v-model="ruleForm.skip_url" placeholder="请输入Value" style="width:350px"></el-input>
                            </el-form-item>
                        </span>
                        
                    </el-form-item>
                    <el-form-item label="目标人群" prop="target_groups">
                        <el-radio-group v-model="ruleForm.target_groups">
                            <el-radio :label="1" >全部用户</el-radio>
                            <el-radio :label="2" >部分用户</el-radio>
                            <el-radio :label="3" >独立用户</el-radio>
                        </el-radio-group>
                        <!-- 部分用户 -->
                        <div v-if="ruleForm.target_groups == '2'">
                            <el-row>
                                <el-col  class="sxtj">
                                    <span style="color:#409eff;">筛选条件</span>
                                </el-col>
                                <!-- 标签 -->
                                <span 
                                    class="tags" 
                                    v-for="item in tags" 
                                    :key="item.e"
                                    @click="showTag(item.e)" >
                                    <i class="el-icon-circle-plus-outline"></i>
                                    {{item.c}}
                                </span>
                            </el-row>
                           
                    
                            <div class="target" v-if="addXt">
                                <span class="bfyh">系统:</span> 
                                <el-select v-model="ruleForm.system" placeholder="所有系统">
                                    <el-option
                                        v-for="item in systemOptions"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value"
                                    />
                                </el-select>
                                <i class="el-icon-close" @click="addXt = false"></i>
                            </div>
                            <div class="target"  v-if="addCity">
                                <span class="bfyh">城市:</span> 
                                <el-select v-model="ruleForm.province" placeholder="请选择城市" @change="sheng">
                                    <el-option
                                        v-for="item in cityList"
                                        :key="item.qz_provinceid"
                                        :label="item.qz_province"
                                        :value="item.qz_provinceid"
                                    />
                                </el-select>
                                <span>省</span> 
                                <el-select v-model="ruleForm.city" placeholder="请选择市区">
                                    <el-option
                                        v-for="city in cheng"
                                        :key="city.cid"
                                        :label="city.cname"
                                        :value="city.cid"
                                    />
                                </el-select>
                                <span>市</span>
                                <i class="el-icon-close"  @click="addCity = false"></i>
                            </div>
                            <div class="target"  v-if="addHy">
                                <span class="bfyh">用户活跃时间：</span>
                                <!-- <el-form-item prop="ruleForm.search_data"> -->
                                    <el-date-picker
                                    v-model="ruleForm.search_data.startTime"
                                    type="datetime"
                                    placeholder="开始时间">
                                    </el-date-picker>

                                    <el-date-picker
                                    v-model="ruleForm.search_data.endTime"
                                    type="datetime"
                                    placeholder="结束时间">
                                    </el-date-picker>
                                  
                                <!-- </el-form-item>   -->
                                <i class="el-icon-close" @click="addHy = false"></i>
                            </div>
                            <div class="target"  v-if="addYw" >
                                <span class="bfyh">业务标签：</span>
                                <el-checkbox-group v-model="ruleForm.business_tag" class="yonghu" >
                                    <el-checkbox v-for="(item,index) in businessTag" :label="item.id" :key="index" 
                                    @change="handleCheck(item,index)">{{ item.c }}</el-checkbox>
                                </el-checkbox-group>
                                <i class="el-icon-close"  @click="addYw = false"></i>
                            </div>
                            <div class="target"  v-if="addAttr">
                                <span class="bfyh">属性标签：</span> 
                                <el-select v-model="ruleForm.is_ordered" placeholder="请选择">
                                    <el-option
                                        v-for="item in attrOptions"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value"
                                    />
                                </el-select>
                                <i class="el-icon-close" @click="addAttr = false"></i>
                            </div>
                        </div>
                        <!-- 独立用户 -->
                        <div v-if="ruleForm.target_groups == '3'">
                            <el-form-item label="Value" prop="device_token" class="linkZ">
                                <el-input v-model="ruleForm.device_token" placeholder="请输入" style="width:350px" ref='device'></el-input>
                            </el-form-item>
                        </div>
                    </el-form-item>
                    <el-form-item label="推送方式" prop="push_type">
                        <el-radio-group v-model="ruleForm.push_type">
                            <el-radio :label="1">立即推送</el-radio>
                            <el-radio :label="2">定时推送</el-radio>
                        </el-radio-group>
                        <el-date-picker
                            v-if="ruleForm.push_type == '2'"
                            v-model="ruleForm.timingTime"
                            type="datetime"
                            placeholder="选择日期时间">
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item label="推送APP" prop="push_location">
                        <el-radio-group v-model="ruleForm.push_location">
                            <el-radio :label="1">齐装APP</el-radio>
                            <el-radio :label="2">装修说APP</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="submitForm('ruleForm')">保存</el-button>
                        <el-button @click="goBack('ruleForm')">返回</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
    </div>
</template>

<script>
    import{ fetchMsgAdd,getMsgEdit,getCityList  } from '@/api/projectApply'
    import request from "@/utils/request"
    import moment from "moment"
    export default {
    name: 'createMsg',
    data() {
        return {
            id: '', 
            formLabelWidth: '100px',
            signatureOptions: [{
                value: '0',
                label: '请选择'
            }],
            tplTypeOptions: [{
                value: '0',
                label: '请选择'
            }],
            gatewayOptions: [],
            imageUrl: '',
            ruleForm: {
                name: '', //消息描述
                title:'', //主标题
                sub_title:'', //副标题
                body:'', //消息内容
                link:'', //跳转链接
                skip_key:'', //跳转参数
                skip_url:'', //跳转地址
                target_groups:'' ,//目标人群
                push_type:'', //推送方式
                push_location:'', //推送app
                timingTime:'',//定时时间
                search_data:{   //用户活跃时间
                    startTime:'', 
                    endTime:''
                },
                business_tag:[],  //业务标签
                is_ordered:'',  //属性标签
                system:'1', //系统
                province:'', //省份
                city:'', //城市
                images: [],
                device_token:'',
            },
            rules: {
                name: [
                    { required: true, message: '请填写消息描述', trigger: 'blur' },
                    { min: 0, max: 50, message: '长度在 0 到 50 个字符', trigger: 'blur' }
                ],
                body: [
                    { required: true, message: '请填写消息内容', trigger: 'blur' },
                    { min: 0, max: 500, message: '长度在 0 到 500 个字符', trigger: 'blur' }
                ],
                target_groups: [
                    { required: true, message: '请选择目标人群', trigger: 'change' }
                ],
                push_type: [
                    { required: true, message: '请选择推送方式', trigger: 'change' }
                ],
                push_location: [
                    { required: true, message: '请选择推送APP', trigger: 'change' }
                ],
                skip_key: [
                    { required: true, message: '请填写Key', trigger: 'blur' },
                ],
                skip_url: [
                    { required: true, message: '请填写Value', trigger: 'blur' },
                ],
            },
            link:false,
            attrOptions:[{
                value: '0',
                label: '请选择'
            }, {
                value: '1',
                label: '未发单'
            },{
                value: '2',
                label: '已发单'
            }], 
            systemOptions:[{
                value: '1',
                label: '所有系统'
            },{
                value: '2',
                label: 'Ios'
            },{
                value: '3',
                label: 'Android'
            }], 
            // 业务标签
            businessTag:[
                {'c':'随便看看','id':0,'state':'false'},
                {'c':'准备装修','id':1,'state':'false'},
                {'c':'装修进行中','id':2,'state':'false'},
                {'c':'装修已结束','id':3,'state':'false'}
            ],
            checkValue: [],
            attr:'0',           
            // 筛选标签
            tags:[
                {'e':'addXt','c':'系统','t':'true'},
                {'e':'addCity','c':'城市区县','t':'true'},
                {'e':'addHy','c':'用户活跃度','t':'true'},
                {'e':'addYw','c':'业务标签','t':'true'},
                {'e':'addAttr','c':'属性标签','t':'true'},
            ],
            // 城市
            cityList:[],
            cheng:[],
            qz_provinceid:'',
            cid:'',
            provinceOptions:[],
            // 区县
            cityOptions:[],
            yhHy:'', //用户活跃
            tag:"addXt",
            addXt:false, // 系统
            addCity:false, // 城市
            addHy:false, // 用户活跃度
            addYw:false, // 业务标签
            addAttr:false, // 属性标签
            // push_status:'1',
            picLoading: false,
            fy:'',//复用,
        }
    },
    
    created() {
        this.id = this.$route.query.id;
        this.fy = this.$route.query.fy; //复用
        if(this.id) {
            this.getMsgDetail()
        }else{
            this.getCityData()
        }
    },
    methods: {
        moment,
        // 筛选标签显示
        showTag(v){
            if(v == 'addXt'){
                this.addXt = true;
            }else{
                if(v == 'addCity'){
                    this.addCity = true;
                }else if( v == 'addHy'){
                    this.addHy = true;
                }else if( v == 'addYw'){
                    this.addYw = true;
                }else if( v == 'addAttr'){
                    this.addAttr = true;
                }
            }
        },
        handleCheck(item,index){
            console.log(item,index)
            
        },
        // 获取城市数据
        getCityData() {
            getCityList().then(res => {
                if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                    this.cityList = res.data.data.info
                    if(this.cityList.length > 0) {
                        this.ruleForm.province = !this.ruleForm.province ? this.cityList[0].qz_provinceid : this.ruleForm.province
                        // this.cheng = !this.cityList[0].sub ? [] : this.cityList[0].sub
                        // if(this.cheng.length > 0) {
                        //     this.ruleForm.city = !this.ruleForm.city ? this.cheng[0].cname : this.ruleForm.city
                        // }
                        this.cheng = this.cityList.filter(item => item.qz_provinceid == this.ruleForm.province)[0].sub
                        console.log('城市',this.cheng)

                        if(this.cheng.length == 0){
                            this.ruleForm.city = ''
                        }else{
                            //当点击省份时切换城市
                            this.ruleForm.city = this.cityList.filter((item) => item.qz_provinceid == this.ruleForm.province)[0].sub[0].cname
                        }
                    }
                }
            })
        },
        //获取省份列表
        sheng(){
            this.cheng = this.cityList.filter(item => item.qz_provinceid == this.ruleForm.province)[0].sub
            if(this.cheng.length == 0){
                this.ruleForm.city = ''
            }else{
                //当点击省份时切换城市
                this.ruleForm.city = this.cityList.filter((item) => item.qz_provinceid == this.ruleForm.province)[0].sub[0].cname
            }
        },
        //保存
        submitForm(formName) {
            let startTime = '', endTime = ''
            if(this.ruleForm.search_data.startTime) {
                let datatime = new Date(this.ruleForm.search_data.startTime)
                startTime = datatime.getFullYear() + '-' + (datatime.getMonth() + 1) + '-' + datatime.getDate()
            }
        
            if(this.ruleForm.search_data.endTime) {
                let datatime = new Date(this.ruleForm.search_data.endTime)
                endTime = datatime.getFullYear() + '-' + (datatime.getMonth() + 1) + '-' + datatime.getDate()
            }
            if( (this.ruleForm.search_data.startTime != '' && this.ruleForm.search_data.startTime != null) && (this.ruleForm.search_data.endTime == '' || this.ruleForm.search_data.endTime == null) ){
                alert("请选择结束时间")
                return false
            }
            if( (this.ruleForm.search_data.startTime == '' || this.ruleForm.search_data.startTime == null) && (this.ruleForm.search_data.endTime != '' && this.ruleForm.search_data.endTime != null)){
                alert("请选择开始时间")
                return false
            }
            if( this.ruleForm.search_data.startTime > this.ruleForm.search_data.endTime && this.ruleForm.search_data.endTime < this.ruleForm.search_data.startTime){
                alert("开始时间不能晚于结束时间")
                return false
            }
            if(this.ruleForm.push_type === 2 && this.ruleForm.timingTime == '' || this.ruleForm.timingTime == null){
                alert('请选择推送时间')
                return false
            }else if(this.ruleForm.push_type === 1){
                this.ruleForm.timingTime = ''
            }
          
            this.$refs[formName].validate((valid) => {
                const queryObj = this.handleArgs()
                if(valid){
                    this.ajaxGateway()
                }else{
                    return false
                }
            })
        },
        //编辑
        getMsgDetail(){
            getMsgEdit({id:this.id}).then(res => {
                console.log('res---.',res)
                if (parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0){
                    this.getCityData()
                    this.setData(res.data.data.info)
                }else{
                    this.$message.error(res.data.error_msg)
                }
            }).catch(res => {
                this.$message.error('网络异常，请稍后再试')
            })
        },
        setData(data) {
            let business_tag = null;
            this.ruleForm.name = data.name //消息描述
            this.ruleForm.title = data.title //主标题
            this.ruleForm.sub_title = data.sub_title //副标题
            this.ruleForm.body = data.body //消息内容
            this.ruleForm.skip_key = data.skip_key //跳转参数
            this.ruleForm.skip_url = data.skip_url //跳转地址
            this.ruleForm.target_groups = data.target_groups // 目标人群
            this.ruleForm.push_type = data.push_type //推送方式
            this.ruleForm.push_location = data.push_location //推送app
            this.ruleForm.timingTime = data.push_time == 0 ? '' : moment(data.push_time*1000).format('YYYY-MM-DD HH:mm:ss')// 定时推送时间
            this.ruleForm.search_data.startTime = data.active_start_time == 0 ? '' : moment(data.active_start_time*1000).format('YYYY-MM-DD HH:mm:ss')//用户活跃时间
            this.ruleForm.search_data.endTime = data.active_end_time == 0 ? '' : moment(data.active_end_time*1000).format('YYYY-MM-DD HH:mm:ss')
            business_tag = !data.business_tag ? []:data.business_tag.split(',') //业务标签
            business_tag.map(item => {
                this.ruleForm.business_tag.push(parseInt(item))
            })
            this.ruleForm.is_ordered = String(data.is_ordered ) //属性标签
            this.ruleForm.system = String(data.system) //系统
            this.ruleForm.province = data.province //省份
            this.ruleForm.city = String(data.city)//城市
            this.ruleForm.images =  [] //消息图片  
            this.ruleForm.device_token = data.device_token
            
            if(data.img) {
                this.ruleForm.images.push({
                    url: data.img,
                })
            }
            if(this.ruleForm.skip_key && this.ruleForm.skip_url) {
                this.ruleForm.link = true
                this.link = true
            }

            if(parseInt(data.target_groups) === 2) {
                this.addXt = true // 系统
                if(this.ruleForm.province && this.ruleForm.city) {
                    this.addCity = true // 城市
                }

                if(data.active_start_time && data.active_end_time && parseFloat(data.active_start_time) > 0 && parseFloat(data.active_end_time) > 0) {
                    this.addHy = true // 用户  
                }
                if(data.business_tag) {
                    this.addYw = true // 业务标签
                }
                if(data.is_ordered) {
                    this.addAttr = true // 属性标签 
                }
            }
        },
        ajaxGateway(){
            const queryObj = this.handleArgs()
            fetchMsgAdd(queryObj).then(res => {
                if(parseInt(res.status) === 200 && parseInt(res.data.error_code) === 0) {
                     this.$message({
                        message: '保存成功',
                        type: 'success'
                    })
                    this.$router.push({
                        path: 'sendNews'
                    })    
                }else{
                    this.$message.error(res.data.error_msg)
                }
            }).catch(res => {
                this.$message.error('网络异常，请稍后再试')
            })
        },
        handleArgs() {
            var push_time = ""
            if (this.ruleForm.timingTime) {
                push_time = moment(this.ruleForm.timingTime).format('YYYY-MM-DD HH:mm:ss')
            }
           
            if(this.fy =='fy'){
                this.id = ''
            }else{
                this.id = this.id
            }
            
            this.tableData = [];

            const queryObj = {
                id: this.id,
                name: this.ruleForm.name,
                title: this.ruleForm.title,
                sub_title: this.ruleForm.sub_title,
                body: this.ruleForm.body,
                img: this.ruleForm.images.length > 0 ? this.ruleForm.images[0].url : '',
                skip_key: this.ruleForm.skip_key,
                skip_url: this.ruleForm.skip_url,
                push_type: this.ruleForm.push_type,  //推送方式
                push_location: this.ruleForm.push_location,
                system: this.ruleForm.system,
                province: this.ruleForm.province,
                city: this.ruleForm.city,
                active_start_time: moment(this.ruleForm.search_data.startTime).format('YYYY-MM-DD HH:mm:ss'),
                active_end_time: moment(this.ruleForm.search_data.endTime).format('YYYY-MM-DD HH:mm:ss'),
                is_ordered: this.ruleForm.is_ordered,
                device_token: this.ruleForm.device_token, //独立用户
                push_time: push_time, //推送时间
                target_groups:parseInt(this.ruleForm.target_groups !== '1')?'':this.ruleForm.target_groups,
                business_tag: ''
            }
            if(this.ruleForm.business_tag instanceof Array) {
                queryObj.business_tag = this.ruleForm.business_tag.join(',')
            }
            if(!this.addXt) {
                queryObj.system = '1'
            }else{
                queryObj.system = this.ruleForm.system
            }
            if(!this.addCity) {
                queryObj.province = ''
                queryObj.city = ''
            }
            if(!this.addHy) {
                queryObj.active_start_time = ''
                queryObj.active_end_time = ''
            }
            if(!this.addYw) {
                queryObj.business_tag = ''
            }
            if(!this.addAttr) {
                queryObj.is_ordered = ''
            }
            if(!this.ruleForm.link) {
                queryObj.skip_key = ''
                queryObj.skip_url = ''
            }
            if(parseInt(this.ruleForm.push_type) === 1) {
                queryObj.push_time = ''
            }
            return queryObj
        },

        //上传前处理事件
        handleAvatarSuccess(res, file) {
            this.imageUrl = URL.createObjectURL(file.raw);
        },
        beforeAvatarUpload(file) {
            const _this = this;
            const isLt2M = file.size / 1024 / 1024 < 5;
            if (!isLt2M) {
                this.$message.error("上传图片大小不能超过 5MB!");
                return isLt2M;
            }
            return new Promise(function(resolve, reject) {
                const reader = new FileReader();
                reader.onload = function() {
                const img = new Image();
                img.onload = function() {
                    const valid =
                        parseInt(this.width) === 72 && parseInt(this.height) === 72 || 
                        parseInt(this.width) === 96 && parseInt(this.height) === 96 ||  
                        parseInt(this.width) === 144 && parseInt(this.height) === 144 ;
                    if (!valid) {
                        _this.$message.error("图片尺寸不符合要求");
                        reject();
                    }
                        resolve();
                };
                    img.src = reader.result;
                };
                    reader.readAsDataURL(file);
            });
        },
        handleExceed(file, fileList) {
            this.$message.error("最多上传1张图片");
        },
        handlePictureCardPreview(file) {
            this.dialogImageUrl = file.url;
            this.dialogVisible = true;
        },
        handleRemove(file, fileList) {
            this.ruleForm.images = [];
        },
        handleUploadImg(item) {
            const formData = new FormData();
            formData.append("file", item.file);
            this.picLoading = true;
        request
            .post("/common/upload", formData)
            .then(res => {
                console.log('<<<',res.data.data)
                console.log(this.ruleForm.images)
            this.ruleForm.images.push({
                name: "",
                url: res.data.data.img_full,
                url_part:res.data.data.img_path
            });
            this.picLoading = false;
                console.log(this.ruleForm.images)
            })
            .catch(err => {
                this.$message.error(err);
                this.picLoading = false;
            });
        },
       
        // 重新上传
        resetUpload(){
           
        },
        // 返回
        goBack() {
            history.go(-1)
        },
    }
  }
</script>

<style rel="stylesheet/scss" lang="scss">
.create-msg {
    width: 100%;
    padding: 20px;
    box-sizing: border-box;
    .qian-main {
        margin-top: 20px;
        padding: 20px;
        border-top: 3px solid #999;
        box-sizing: border-box;
        background-color: #fff;
        .tpl-content-note{
            padding-left: 100px;
        }
        .red{
            color: red;
        }
        .gateways{
            margin-bottom: 10px;
        }
        .gateways>td{
            padding-right: 10px;
        }
        .gatewayinput{
            width: auto;
        }
        .addMsg{
            padding:0 0 15px 0;
        }
        .biaodan{
            width:60%;
            margin-right:20px;
        }
        .linkZ{
            width:300px;
            float:left;
        }
        .el-icon-plus{
            border:1px solid #909399;
            margin-right: 10px;
        }
        .sxtj{
            width:100px;
            text-align:center;
            border-bottom:3px solid #409eff;
        }
        .tags{
            padding:0 20px;
            cursor: pointer;
        }
        .target{
            padding:10px 0
        }
        .target span{
            padding-right: 10px
        }
        .target i{
            cursor: pointer;
            // padding-left:15px;
        }
        .reUp{
            font-size: 12px;
            position: absolute;
            bottom: 0px;
            border-radius: 15px;
            padding: 5px;
            margin-left: 10px;
        }
        .tishi{
            width: 20px;
            height: 20px;
            line-height: 20px;
            border-radius: 50%;
            border: 1px solid rgb(204, 204, 204);
            text-align: center;
            display: inline-block;
            color: #ccc;
            cursor: pointer;
            margin-left: 10px;
        }
        .imgtext{
            padding-left:35px;
        }
        .bfyh{
            display: block;
            width:110px;
            float: left;
            text-align: right;
        }
        .yonghu{
            width: 550px;
            float: left;
        }
    }
}
</style>
