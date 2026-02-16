import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import { useBranchStore } from '@/stores/branchStore'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'Home',
    component: () => import('@/views/HomeView.vue'),
    meta: {
      title: 'Shivanum Naanum - Brahma Kumaris | Shivratri Experience 2026'
    }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/views/DashboardView.vue'),
    meta: {
      title: 'Dashboard - Shivanum Naanum',
      requiresBranch: true
    }
  },
  {
    path: '/event-reports',
    name: 'EventReports',
    component: () => import('@/views/EventReportsView.vue'),
    meta: {
      title: 'Event Reports - Shivanum Naanum',
      requiresBranch: true
    }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (to.hash) {
      return {
        el: to.hash,
        behavior: 'smooth'
      }
    } else if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

router.beforeEach((to, from, next) => {
  // Check if route requires branch selection
  if (to.meta.requiresBranch) {
    const branchStore = useBranchStore()
    if (!branchStore.hasBranch()) {
      return next('/')
    }
  }

  // Set page title
  if (to.meta.title) {
    document.title = to.meta.title as string
  }

  next()
})

export default router
