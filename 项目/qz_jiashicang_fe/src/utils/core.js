
export default {
  formatmenudata(menudatalist) {
    const MENU = []
    const notfound = [{ path: '*', name: '404', component: () => import('@/views/404'), meta: { title: '404' }}]
    menudatalist.forEach(item => {
      MENU.push({
        path: `/${item.path}`,
        component: () => import('@/layout'),
        redirect: item.redirect,
        name: item.name,
        meta: {
          title: item.meta.title, icon: `el-icon-${item.meta.icon}`
        },
        alwaysShow: true,
        children: item.children.map(el => {
          return {
            path: el.path,
            component: resolve => require([`@/views/${el.component}`], resolve),
            name: el.name,
            meta: {
              title: el.meta.title,
              icon: `el-icon-${el.meta.icon}`,
              noCache: true
            }
          }
        })
      })
    })
    return [...MENU, ...notfound]
  },
  formatechartsdata(ConfigList, ResDataSeries) {
    const series = []
    ConfigList.forEach((item, i) => {
      ResDataSeries.forEach((ele, e) => {
        if (i === e) {
          series.push({ ...item, ...ele })
        }
      })
    })
    return series
  }
}
