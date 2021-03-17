import { constantRoutes } from '@/router'
import core from '@/utils/core'
import apis from '@/api/common'

/**
 * Use meta.role to determine if the current user has permission
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
 * Filter asynchronous routing tables by recursion
 * @param routes asyncRoutes
 * @param roles
 */
export function filterAsyncRoutes(routes, roles) {
  const res = []

  routes.forEach(route => {
    const tmp = { ...route }
    if (hasPermission(roles, tmp)) {
      if (tmp.children) {
        tmp.children = filterAsyncRoutes(tmp.children, roles)
      }
      res.push(tmp)
    }
  })

  return res
}

const state = {
  routes: [],
  addRoutes: []
}

const mutations = {
  SET_ROUTES: (state, routes) => {
    state.addRoutes = routes
    state.routes = constantRoutes.concat(routes)
  }
}
const actions = {
  generateRoutes({ commit }, roles) {
    return new Promise(resolve => {
      const menulist = sessionStorage.getItem('menulist')
      if (menulist) {
        const MENULIST = core.formatmenudata(JSON.parse(menulist))
        commit('SET_ROUTES', MENULIST)
        resolve(MENULIST)
      } else {
        apis.menu().then(res => {
          if (res.error_code === 0) {
            sessionStorage.setItem('menulist', JSON.stringify(res.data))
            const MENULIST = core.formatmenudata(res.data)
            commit('SET_ROUTES', MENULIST)
            resolve(MENULIST)
          } else {
            console.log(res.error_msg)
          }
        }).catch(err => {
          console.log(err)
        })
      }
    })
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}
