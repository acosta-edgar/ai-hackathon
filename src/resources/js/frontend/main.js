import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import VueToast from 'vue-toastification'
import VCalendar from 'v-calendar'
import App from './App.vue'
import routes from './router'
import 'v-calendar/style.css'
import 'vue-toastification/dist/index.css'
import './assets/scss/main.scss'

// Initialize the application
const app = createApp(App)
const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    return savedPosition || { top: 0 }
  }
})

// Global error handler
const errorHandler = (error, vm, info) => {
  console.error('Vue error:', { error, vm, info })
  // You can add error reporting service here (e.g., Sentry)
}

// Global directives
const clickOutside = {
  beforeMount(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event)
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}

// Global properties
app.config.globalProperties.$filters = {
  formatDate(value, format = 'MMM d, yyyy') {
    if (!value) return ''
    return new Date(value).toLocaleDateString(undefined, { dateStyle: 'medium' })
  },
  currency(value, currency = 'USD') {
    if (value === null || value === undefined) return ''
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency,
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(value)
  },
  truncate(text, length = 50, suffix = '...') {
    if (!text) return ''
    return text.length > length
      ? text.substring(0, length) + suffix
      : text
  }
}

// Register plugins
app.use(createPinia())
app.use(router)
app.use(VueToast, {
  position: 'top-right',
  timeout: 5000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
  draggablePercent: 0.6,
  showCloseButtonOnHover: false,
  hideProgressBar: false,
  closeButton: 'button',
  icon: true,
  rtl: false
})
app.use(VCalendar, {})

// Register global directives
app.directive('click-outside', clickOutside)

// Global error handler
app.config.errorHandler = errorHandler

// Simple navigation guard (no auth required)
router.beforeEach((to, from, next) => {
  // Scroll to top on route change
  window.scrollTo(0, 0)
  next()
})

// Mount the app
app.mount('#app')

// Global error handler for unhandled promise rejections
window.addEventListener('unhandledrejection', (event) => {
  console.error('Unhandled promise rejection:', event.reason)
  // You can add error reporting service here
})
