import { createRouter, createWebHistory } from 'vue-router'

// Import views
import JobsView from '@/views/JobsView.vue'
import JobListView from '@/views/jobs/JobListView.vue'
import DashboardView from '@/views/DashboardView.vue'
import ApplicationsView from '@/views/ApplicationsView.vue'
import ProfileView from '@/views/ProfileView.vue'
import SettingsView from '@/views/SettingsView.vue'
import AccountSettingsView from '@/views/settings/AccountSettingsView.vue'
import NotificationSettingsView from '@/views/settings/NotificationSettingsView.vue'
import BillingSettingsView from '@/views/settings/BillingSettingsView.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: JobsView,
    meta: { title: 'JobCompass AI - Find Your Perfect Job' }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { title: 'Dashboard - JobCompass AI' }
  },
  {
    path: '/jobs',
    name: 'jobs',
    component: JobsView,
    meta: { title: 'Job Search - JobCompass AI' }
  },
  {
    path: '/jobs/list',
    name: 'job-list',
    component: JobListView,
    meta: { title: 'Job List - JobCompass AI' }
  },
  {
    path: '/applications',
    name: 'applications',
    component: ApplicationsView,
    meta: { title: 'Applications - JobCompass AI' }
  },
  {
    path: '/profile',
    name: 'profile',
    component: ProfileView,
    meta: { title: 'Profile - JobCompass AI' }
  },
  {
    path: '/settings',
    name: 'settings',
    component: SettingsView,
    meta: { title: 'Settings - JobCompass AI' },
    children: [
      {
        path: '',
        name: 'settings.index',
        redirect: { name: 'settings.account' }
      },
      {
        path: 'account',
        name: 'settings.account',
        component: AccountSettingsView,
        meta: { title: 'Account Settings - JobCompass AI' }
      },
      {
        path: 'notifications',
        name: 'settings.notifications',
        component: NotificationSettingsView,
        meta: { title: 'Notification Settings - JobCompass AI' }
      },
      {
        path: 'billing',
        name: 'settings.billing',
        component: BillingSettingsView,
        meta: { title: 'Billing Settings - JobCompass AI' }
      }
    ]
  },
  // Add more routes as needed
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/components/NotFound.vue'),
    meta: { title: 'Page Not Found' }
  }
]

export default routes 