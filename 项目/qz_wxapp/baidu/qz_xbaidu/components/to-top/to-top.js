Component({
    properties: {
        backTopValue: {
            type: Boolean,
            value: false
          }
    },
    data: {
        isShow: true
    },
    methods: {
        // 回到顶部
        backTop(){
            this.triggerEvent('backTop')
        }
    }
});
