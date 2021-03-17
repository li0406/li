// components/fadan/fadan.js
import {fadanHandle} from '../../utils/mapi'
const app = getApp()
Component({
  data: {
    wait:false,
    defaultData:{
      cs:{
        placeholder:"",
        value:"请选择您所在的城市",
        tagsName:"button",
        itemName:'cs',
        type:"",
        validate:function(ct){
          if(ct == ""){
            return '请选择城市'
          }
          return 1
        }
      },
      name:{
        placeholder:"请输入您的名称",
        value:"",
        tagsName:"input",
        type:"text",
        itemName:'name',
        validate:function(val){
          let reg = new RegExp("^[\u4e00-\u9fa5_a-zA-Z]+$")
          if(val==""){
            return '请输入您的名称'
          }
          if(!reg.test(val)){
            return '请输入正确的名称'
          }
          return 1
        }
      },
      tel:{
        placeholder:"请输入您的手机号码",
        value:"",
        tagsName:"input",
        type:"number",
        itemName:'tel',
        validate:function(val){
          let reg = new RegExp("^(13|14|15|16|17|18|19)[0-9]{9}$");
          if(val==""){
            return '请输入您的手机号码'
          }
          if(val.length!=11){
            tt.showToast({
              title: '请输入11位手机号码',
              icon:'none'
            });
            return false
          }
          if(!reg.test(val)){
            return '请输入正确的手机号码'
          }
          return 1
        }
      },
      mianji:{
        placeholder:"请输入您的房屋面积",
        value:"",
        tagsName:"input",
        type:"number",
        itemName:'mianji',
        validate:function(val){
          if(val==""){
            return '请输入您的房屋面积'
          }
          if(isNaN(val)||val<0||val>1000){
            return  '请输入1-1000的纯数字'
          }
          return 1
        }
      },
      xiaoqu:{
        placeholder:"请填写小区名称以便精确匹配",
        value:"",
        tagsName:"input",
        type:"text",
        itemName:'xiaoqu',
        validate:function(val){
          if(val==""){
            return '请填写小区名称以便精确匹配'
          }
          return 1
        }
      },
      start:{
         placeholder:"请选择开工时间",
         value:"",
         tagsName:"select",
         type:"select",
         itemName:"start",
         options:[
           {
             text:"请选择开工时间",
             value:''
           },
           {
             text:"1个月",
             value:'1个月'
           },
           {
             text:"2个月",
             value:'2个月'
           },
           {
             text:"3个月",
             value:'3个月'
           },
           {
             text:"3个月以上",
             value:'3个月以上'
           },
           {
             text:"面议",
             value:'拿房后开工'
           }
         ],
         validate:function(val){
          if(val==""){
            return '请选择开工时间'
          }
          return 1
        }
      },
      yusuan:{
        placeholder:"请选择不包括家具、电器的预算",
        value:"",
        itemName:'yusuan',
        tagsName:"select",
        type:"select",
        options:[
          {
            text:"请选择不包括家具、电器的预算",
            value:""
          },
          {
            text:"3万以下",
            value:14
          },{
            text:"3-4万",
            value:16
          },{
            text:"4-5万",
            value:15
          },{
            text:"5-7万",
            value:17
          },{
            text:"7-10万",
            value:18
          },{
            text:"10-15万",
            value:19
          },{
            text:"15-20万",
            value:21
          },{
            text:"20-30万",
            value:22
          },{
            text:"30-50万",
            value:23
          },{
            text:"50-100万",
            value:24
          },{
            text:"100万以上",
            value:25
          },{
            text:"面议",
            value:42
          }
        ],
        validate:function(val){
          if(val==""){
            return '请选择预算'
          }
          return 1
        }
      }
    },
    renderTags:[],
    isHide:true,
    hideSelect:false,
    selectText:'请选择您所在的城市',
    hasChecked:true,
    extendsParms:{},
    cid:''
  },
  options:[],
  tempSelectValue:{
    target:'',
    value:'',
    text:'',
    index: 0
  },
  properties: {
    changeCity:{
      type:String,
      value:'',
      observer:function(newVal){
        this.setData({
         cid:newVal
        })
      }
    },
    elements:{
      type:String,
      value:''
    },
    btnText:{
      type:String,
      value:""
    },
    source:{
      type:String,
      value:'19090648'
    },
    itemBg:{
      type:String,
      value:""
    },
    parmsExtends:{
      type:Object,
      value:{},
      observer:function(newVal){
        this.setData({
          extendsParms:newVal
        })
      }
    }
  },
  attached:function(){
    this.renderTags()
  },
  methods: {
    // 渲染发单组件
    renderTags:function(){
      let tagsArray = this.data.elements.split(",")
      let newArray = []
      tagsArray.forEach((ele,index)=>{
        newArray.push(this.data.defaultData[ele])
      })
      this.setData({
        renderTags:newArray
      })
    },
    // 展开城市选择框
    selectHandle: function () {
        this.setData({
            isHide: false
        })
    },
    // 关闭城市选择框，获取定位数据
    closeSelect: function (res) {
      if(this.data.renderTags[0].itemName=="cs"){
        this.setData({
          isHide: true,
          ["renderTags[0].placeholder"]:res.detail[1],
          ["renderTags[0].value"]:res.detail[0]
        })
      }
    },
    // 免责声明修改
    changeCheck:function(e){
      this.setData({
        hasChecked:!this.data.hasChecked
      })
    },
    // 打开选择框
    openSelector:function(e){
      let data = e.target.dataset.item
      this.setData({
        hideSelect:true,
        options:data.options,
        ["tempSelectValue.target"]:e.target.dataset.itemname,
        ["tempSelectValue.index"]:0
      })
    },
   closeOptoinSelect:function(){
    //  拿到当前对象
     let currentTarget = this.data.tempSelectValue.target
     let index = this.data.tempSelectValue.index
     let value = this.data.defaultData[currentTarget].options[index].value
     let text = this.data.defaultData[currentTarget].options[index].text
      // 遍历到当前选项
      this.data.renderTags.forEach((item,index)=>{
        if(currentTarget == item.itemName){
           this.setData({
             ["renderTags["+index+"].placeholder"]:text,
             ["renderTags["+index+"].value"]:value,
           })
        }
      })
     this.setData({
        hideSelect:false
      })
   },
    // 滑动选择框
    bindChange:function(e){
      // 获取滑动的当前序号
      this.setData({
        ["tempSelectValue.index"]:e.detail.value[0]
      })
    },
    // 监听获取input值
    getThisValue:function(e){
      // 获取当前input框
      let currentTarget = e.target.dataset.itemname
      let value = e.detail.value
      this.data.renderTags.forEach((item,index)=>{
        if(currentTarget == item.itemName){
           this.setData({
             ["renderTags["+index+"].value"]:value,
           })
        }
      })
    },
    // 点击提交按钮
    commonSubmit:function(){
      try{
         this.data.renderTags.forEach((item, index)=>{
           if(item.validate(item.value) != 1){
              throw item.validate(item.value)
            }
         })
        this.toCommonOrder()
      }
      catch(error){
        tt.showToast({
          title: error,
          icon:'none'
        });
      }
    },
    // 发单
    toCommonOrder:function(){
      if(!this.data.hasChecked){
        tt.showToast({
          title: "请选择阅读齐装网的免责声明",
          icon:'none'
        });
        return
      }
      // 参数收集整合
      let parms = {}
      this.data.renderTags.forEach((item, index)=>{
        parms[item.itemName] = item.value
      })
      parms.source = this.data.source
      parms.src = app.globalData.sourceMark
      if(JSON.stringify(this.data.extendsParms) !== "{}"){
        for(let i in this.data.extendsParms){
          parms[i]=this.data.extendsParms[i]
        }
      }
      this.setData({
        wait:true
      })
      // 发单成功回调
       fadanHandle(
        '/v1/fb?src='+app.globalData.sourceMark,
        parms
      ).then(res=>{
        if(res.error_code == 0){
          this.setData({
            wait:false
          })
          this.triggerEvent("orderSuccess",{})
        }
      }).catch(res=>{
        tt.showToast({
          title: "网络错误，请稍后重试",
          icon:'none'
        });
      })
    }
  }
})