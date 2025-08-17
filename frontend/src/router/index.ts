import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Lazy load components for better performance
const LoginView = () => import('@/views/LoginView.vue')
const BranchSelectionView = () => import('@/views/BranchSelectionView.vue')
const DashboardView = () => import('@/views/DashboardView.vue')
const DataEntryView = () => import('@/views/DataEntryView.vue')
const ParticipantsView = () => import('@/views/ParticipantsView.vue')

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: LoginView,
    meta: {
      requiresAuth: false,
      title: 'Login - Meditation Tracker'
    }
  },
  {
    path: '/branches',
    name: 'BranchSelection',
    component: BranchSelectionView,
    meta: {
      requiresAuth: true,
      title: 'Select Branch - Meditation Tracker'
    }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: DashboardView,
    meta: {
      requiresAuth: true,
      requiresBranch: true,
      title: 'Dashboard - Meditation Tracker'
    }
  },
  {
    path: '/data-entry',
    name: 'DataEntry',
    component: DataEntryView,
    meta: {
      requiresAuth: true,
      requiresBranch: true,
      title: 'Record Session - Meditation Tracker'
    }
  },
  {
    path: '/participants',
    name: 'Participants',
    component: ParticipantsView,
    meta: {
      requiresAuth: true,
      requiresBranch: true,
      title: 'Participants - Meditation Tracker'
    }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    redirect: '/'
  }
]

const router = createRouter({
  history: createWebHistory('/sn-progress/'),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// Global navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Set page title
  if (to.meta.title) {
    document.title = to.meta.title as string
  }
  
  // Try to restore session if not authenticated
  if (!authStore.isLoggedIn) {
    authStore.restoreSession()
  }
  
  // Check authentication requirements
  if (to.meta.requiresAuth && !authStore.isLoggedIn) {
    next('/login')
    return
  }
  
  // Check branch selection requirements
  if (to.meta.requiresBranch && !authStore.hasSelectedBranch) {
    next('/branches')
    return
  }
  
  // Redirect authenticated users away from login
  if (to.name === 'Login' && authStore.isLoggedIn) {
    if (authStore.hasSelectedBranch) {
      next('/data-entry')
    } else {
      next('/branches')
    }
    return
  }
  
  // Redirect users with branch selection away from branch selection
  if (to.name === 'BranchSelection' && authStore.hasSelectedBranch) {
    next('/data-entry')
    return
  }
  
  next()
})

// Global after hooks for cleanup
router.afterEach((to, from) => {
  // Close any open modals or dropdowns
  document.querySelectorAll('[data-dropdown]').forEach(el => {
    el.removeAttribute('data-open')
  })
  
  // Remove any active states
  document.querySelectorAll('.active').forEach(el => {
    el.classList.remove('active')
  })
})

export default router