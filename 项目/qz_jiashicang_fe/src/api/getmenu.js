// import apis from '@/api/common'
const MENU = []
// apis.menu().then(res => {
//   if (res.error_code === 0) {
//     res.data.forEach(item => {
//       MENU.push({
//         path: item.path,
//         name: item.name,
//         component: resolve => require([`${item.component}`], resolve),
//         redirect: item.redirect,
//         meta: item.meta,
//         children: item.children
//       })
//     })
//   } else {
//     console.log(res.error_msg)
//   }
// }).catch(err => {
//   console.log(err)
// })
export default MENU
