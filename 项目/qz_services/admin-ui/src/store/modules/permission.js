import { asyncRoutes, constantRoutes } from '@/router'
import { routeArray } from '@/api/login'
const _import = require('@/router/_import_' + process.env.NODE_ENV)// 获取组件的方法
import Layout from '@/views/layout/Layout'
import store from "../index";

/**
 * 通过meta.role判断是否与当前用户权限匹配
 * @param roles
 * @param route
 */
function hasPermission(roles, route) {
  if (route.meta && route.meta.roles) {
    return roles.some(role => route.meta.roles.includes(role))
  } else {
    return true
  }
}

/**
 * 递归过滤异步路由表，返回符合用户角色权限的路由表
 * linktype 1 表示绝对路径 2 表示相对路径
 * 绝对路径没有component属性
 * @param routes asyncRoutes
 * @param roles
 */
function filterAsyncRouter(asyncRouterMap) { // 遍历后台传来的路由字符串，转换为组件对象
  const accessedRouters = asyncRouterMap.filter(route => {
    if (route.component) {
      if (route.component === 'Layout') { // Layout组件特殊处理
        route.component = Layout
      } else {
        if (route.linktype && parseInt(route.linktype) === 1) {
          delete route.component
          route.path = route.linkurl
        } else {
          route.component = _import(route.component)
        }
      }
    } else {
      delete route.component
      route.path = route.linkurl
    }
    if (route.children && route.children.length) {
      route.children = filterAsyncRouter(route.children)
    }
    return true
  })

  return accessedRouters
}

/**
 * 删除子菜单多余数据，否则子菜单大于一个的情况下，将渲染不出来
 * 该函数主要针对go语言接口
 * @param data
 * @returns {*}
 */
function delChildrenData(data) {
  data.forEach(item => {
    if(item.children && item.children.length > 0){
      item.children.forEach(citem => {
        delete citem.children
        delete citem.redirect
      })
    }
  })
  return data
}

const permission = {
  state: {
    routes: [],
    addRoutes: []
  },
  mutations: {
    SET_ROUTES: (state, routes) => {
      state.addRoutes = routes
      state.routes = constantRoutes.concat(routes)
    }
  },
  actions: {
    GenerateRoutes({ commit }, data) {
      return new Promise(resolve => {
        // const accessedRoutes = asyncRoutes
        data = delChildrenData(data)
        const accessedRoutes = filterAsyncRouter(data) // 请求得到的菜单需要遍历处理
        commit('SET_ROUTES', accessedRoutes)
        resolve(accessedRoutes)
      })
    }
  }
}

export default permission
