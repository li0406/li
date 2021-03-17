import { login, logout, getInfo, menu, clean } from '@/api/user'
import { getToken, setToken, removeToken } from '@/utils/auth'
import { asyncRoutes, constantRoutes, resetRouter } from '@/router'

const getDefaultState = () => {
  return {
    token: getToken(),
    name: '',
    avatar: '',
    routes: [],
    addRoutes: []
  }
}
export function formatmenudata(menudatalist) {
  const MENU = []
  const notfound = [{ path: '*', hidden: true, name: '404', component: () => import('@/views/404'), meta: { title: '404' }}]
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
  return [...asyncRoutes, ...MENU, ...notfound]
}
const state = getDefaultState()

const mutations = {
  RESET_STATE: (state) => {
    Object.assign(state, getDefaultState())
  },
  SET_TOKEN: (state, token) => {
    state.token = token
  },
  SET_NAME: (state, name) => {
    state.name = name
  },
  SET_AVATAR: (state, avatar) => {
    state.avatar = avatar
  },
  SET_MENULIST: (state, routes) => {
    state.addRoutes = routes
    state.routes = constantRoutes.concat(routes)
  }
}

const actions = {
  // user login
  login({ commit }, userInfo) {
    const { username, password } = userInfo
    return new Promise((resolve, reject) => {
      login({ username: username.trim(), password: password }).then(response => {
        const { data } = response
        commit('SET_TOKEN', data.token)
        setToken(data.token)
        resolve()
      }).catch(error => {
        reject(error)
      })
    })
  },

  // get user info
  getInfo({ commit, state }) {
    return new Promise((resolve, reject) => {
      getInfo().then(response => {
        const { data } = response
        if (!data) {
          return reject('验证失败，请重新登录.')
        }

        const avatar = data.logo
        const name = {
          nick_name: data.nickName,
          role_name: data.roleName,
          avatar
        }
        sessionStorage.setItem('name', JSON.stringify(name))
        commit('SET_NAME', name)
        commit('SET_AVATAR', avatar)
        resolve(data)
      }).catch(error => {
        reject(error)
      })
    })
  },

  // user logout
  logout({ commit, state }) {
    return new Promise((resolve, reject) => {
      logout(state.token).then(() => {
        removeToken() // must remove  token  first
        resetRouter()
        commit('RESET_STATE')
        resolve()
      }).catch(error => {
        reject(error)
      })
    })
  },

  menu({ commit, state }) {
    return new Promise((resolve, reject) => {
      menu().then(response => {
        const menuList = formatmenudata(response.data.list)
        commit('SET_MENULIST', menuList)
        resolve(menuList)
      }).catch(error => {
        reject(error)
      })
    })
  },

  clean({ commit, state }) {
    return new Promise((resolve, reject) => {
      clean().then(response => {
        resolve(response)
      }).catch(error => {
        reject(error)
      })
    })
  },

  // remove token
  resetToken({ commit }) {
    return new Promise(resolve => {
      removeToken() // must remove  token  first
      commit('RESET_STATE')
      resolve()
    })
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}

