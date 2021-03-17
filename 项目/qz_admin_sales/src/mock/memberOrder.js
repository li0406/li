import Mock from 'mockjs'
import { param2Obj } from '@/utils'

const List = []
const count = 100

for (let i = 0; i < count; i++) {
  List.push(Mock.mock({
    id: '@increment',
    publish_time: +Mock.Random.date('T'),
    allocate_time: +Mock.Random.date('T'),
    sign_time: +Mock.Random.date('T'),
    verify_time: +Mock.Random.date('T'),
    city: '@first',
    area: '@first',
    field_30: '@title(5, 10)', // 订单类型
    field_31: '我是测试数据', // 联系人
    field_32: @title(5, 10), // 签单小区
    field_33: '@float(0, 100, 2, 2)',  // 签单面积
    field_34: '@integer(1, 3)', // 签单金额
    field_35: '@datetime', // 审批人
    company_name: true, // 签单公司
    company_id: '@integer(300, 5000)', // 公司ID
  }))
}

export default {
  getList: config => {
    const { importance, type, title, page = 1, limit = 20, sort } = param2Obj(config.url)

    let mockList = List.filter(item => {
      if (importance && item.importance !== +importance) return false
      if (type && item.type !== type) return false
      if (title && item.title.indexOf(title) < 0) return false
      return true
    })

    if (sort === '-id') {
      mockList = mockList.reverse()
    }

    const pageList = mockList.filter((item, index) => index < limit * page && index >= limit * (page - 1))

    return {
      total: mockList.length,
      items: pageList
    }
  },
  getPv: () => ({
    pvData: [{ key: 'PC', pv: 1024 }, { key: 'mobile', pv: 1024 }, { key: 'ios', pv: 1024 }, { key: 'android', pv: 1024 }]
  }),
  getArticle: (config) => {
    const { id } = param2Obj(config.url)
    for (const article of List) {
      if (article.id === +id) {
        return article
      }
    }
  },
  createArticle: () => ({
    data: 'success'
  }),
  updateArticle: () => ({
    data: 'success'
  })
}
