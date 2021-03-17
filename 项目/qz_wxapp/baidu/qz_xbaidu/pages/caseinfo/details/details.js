const app = getApp()
import { cases } from '../../../utils/api.js'
Page({
    data: {
        info: '',
        img_list: []
    },
    caseinfoDetail(case_id) {
        cases(
            '/bd/v1/case/details',
            {
                case_id: case_id
             }
        ).then(data => {
            if(data.error_code === 0){
                let imgArray = data.data.info.img.map(item =>{
                    return item.img_preview
                })
                this.setData({
                    info: data.data.info,
                    img_list: imgArray
                })
                swan.setPageInfo({
                    title: data.data.tdk.title,
                    keywords: data.data.tdk.keywords,
                    description: data.data.tdk.description,
                    image: []
                })
            } else {
                swan.showToast({
                    title: '网络异常',
                    icon: 'none'
                })
            }
        }).catch(error => {
            swan.showToast({
                title: '网络异常',
                icon: 'none'
            })
        })
    },
    onLoad: function (options) {
        this.caseinfoDetail(options.id)
    },
    viewImage: function(e) {
        let currentSrc = e.currentTarget.dataset.src
        swan.previewImage({
            current: currentSrc,
            urls: this.data.img_list
        });
    }
});