import { createRouter, createWebHistory } from 'vue-router'
import NavBar from '../views/Header.vue'
import { isLoggedIn, loggedInId } from '../javascript/loggedin.js'

const router = createRouter({
  beforeEach(toRoute, fromRoute, next) {
    window.document.title = toRoute.meta && toRoute.meta.title ? toRoute.meta.title : 'Home';

    // if (to.name === 'login' && isLoggedIn()) next({ name: 'home' })
    // else next()
  },
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      alias: '/home',
      components: {
        Header: NavBar,
        MainContent: () => import('../views/HomePage.vue')
      }
    },
    {
      path: '/search',
      name: 'search',
      alias: '/browse',
      components: {
        Header: NavBar,
        MainContent: () => import('../views/SearchPage.vue')
      },
      props: true
    },
    {
      path: '/login',
      name: 'login',
      components: {
        Header: NavBar,
        MainContent: () => import('../views/Login.vue')
      },
      beforeEnter(to, from, next) {
        if (isLoggedIn()) {
          next('/home')
        }
        next()
      }
    },
    {
      path: '/logout',
      name: 'logout',
      components: {
        Header: NavBar,
        MainContent: () => import('../views/Logout.vue')
      },
      beforeEnter(to, from, next) {
        if (!isLoggedIn()) {
          next('/home')
        }
        next()
      }
    },
    {
      path: '/register',
      name: 'register',
      components: {
        Header: NavBar,
        MainContent: () => import('../views/Register.vue')
      },
      beforeEnter(to, from, next) {
        if (isLoggedIn()) {
          next('/home')
        }
        next()
      }
    },
    {
      path: '/post/:id',
      name: 'post',
      components: {
        Header: NavBar,
        MainContent: () => import('../views/Post.vue')
      },
      props: true
    },
    {
      path: '/post/new',
      name: 'new_post',
      components: {
        Header: NavBar,
        MainContent: () => import('../views/NewPost.vue')
      },
      props: true,
      beforeEnter(to, from, next) {
        if (!isLoggedIn()) {
          next('/login')
        }
        next()
      }
    },
    {
      path: '/post/:id/edit',
      name: 'edit_post',
      components: {
        Header: NavBar,
        MainContent: () => import('../views/EditPost.vue')
      },
      props: true,
      beforeEnter(to, from, next) {
        if (!isLoggedIn()) {
          next('/login')
        }
        next()
      }
    },
    {
      path: '/me',
      name: 'profile',
      alias: '/profile',
      components: {
        Header: NavBar,
        MainContent: () => import('../views/ProfilePage.vue')
      },
      props: true,
      beforeEnter(to, from, next) {
        if (!isLoggedIn()) {
          next('/login')
        }
        next()
      }
    },
    {
      path: '/tag/new',
      name: 'new_tag',
      components: {
        Header: NavBar,
        MainContent: () => import('../views/NewTag.vue')
      },
      props: true,
      beforeEnter(to, from, next) {
        if (!isLoggedIn()) {
          next('/login')
        }
        next()
      }
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not_found',
      components: {
        Header: NavBar,
        MainContent: () => import('../views/PageNotFound.vue')
      },
      props: true
    }
  ]
})

export default router
