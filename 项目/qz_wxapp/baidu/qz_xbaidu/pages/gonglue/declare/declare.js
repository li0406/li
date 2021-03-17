Component({
    properties: {
        propName: { 
            type: String, 
            value: 'val', 
            observer: function(newVal, oldVal) {
              
            }
        }
    },

    data: {},
    methods: {
        onTap: function () {
            this.setData({
            });
        }
    }
});