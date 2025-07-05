<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { useToast } from 'vue-toastification'
import AppLogo from '@/components/ui/AppLogo.vue'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()
const toast = useToast()

// Layout state
const isLoading = ref(false)
const error = ref(null)
const isMobile = ref(false)

// Check if mobile view
const checkIfMobile = () => {
  isMobile.value = window.innerWidth < 1024
}

// Handle window resize
const handleResize = () => {
  checkIfMobile()
}

// Redirect to dashboard if already authenticated
const checkAuth = () => {
  if (userStore.isAuthenticated) {
    router.push({ name: 'dashboard' })
  }
}

// Set page title based on route meta
const pageTitle = computed(() => {
  return route.meta.title || 'JobCompass AI'
})

// Set document title
watch(pageTitle, (newTitle) => {
  document.title = `${newTitle} | JobCompass AI`
}, { immediate: true })

// Handle form submission
const handleSubmit = async (formData) => {
  try {
    isLoading.value = true
    error.value = null
    
    if (route.name === 'login') {
      await userStore.login(formData)
      toast.success('Successfully logged in')
      router.push(route.query.redirect || { name: 'dashboard' })
    } else if (route.name === 'register') {
      await userStore.register(formData)
      toast.success('Registration successful! Please check your email to verify your account.')
      router.push({ name: 'login', query: { registered: 'true' } })
    } else if (route.name === 'forgot-password') {
      await userStore.forgotPassword(formData)
      toast.success('Password reset link sent to your email')
      router.push({ name: 'login', query: { reset: 'true' } })
    } else if (route.name === 'reset-password') {
      await userStore.resetPassword({
        ...formData,
        token: route.params.token
      })
      toast.success('Password reset successful! You can now login with your new password.')
      router.push({ name: 'login', query: { reset: 'success' } })
    }
  } catch (err) {
    console.error('Authentication error:', err)
    error.value = err.response?.data?.message || 'An error occurred. Please try again.'
    toast.error(error.value)
  } finally {
    isLoading.value = false
  }
}

// Initialize
onMounted(() => {
  checkIfMobile()
  window.addEventListener('resize', handleResize)
  checkAuth()
  
  // Show success message if redirected after registration
  if (route.query.registered === 'true') {
    toast.success('Registration successful! Please check your email to verify your account.')
  } else if (route.query.reset === 'true') {
    toast.success('Password reset link sent to your email')
  } else if (route.query.reset === 'success') {
    toast.success('Password reset successful! You can now login with your new password.')
  } else if (route.query.verified === 'true') {
    toast.success('Email verified successfully! You can now login.')
  }
})

// Cleanup
onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

<template>
  <div class="min-h-screen flex flex-col bg-gray-50 dark:bg-gray-900">
    <!-- Skip to main content link for accessibility -->
    <a
      href="#main-content"
      class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:p-2 focus:bg-white focus:ring-2 focus:ring-primary-500 focus:rounded-md"
    >
      Skip to main content
    </a>

    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex-shrink-0 flex items-center">
            <router-link to="/" class="flex items-center">
              <AppLogo class="h-8 w-auto" />
              <span class="ml-2 text-xl font-bold text-gray-900 dark:text-white">JobCompass</span>
            </router-link>
          </div>
          <div class="hidden md:ml-6 md:flex md:items-center space-x-4">
            <router-link 
              v-if="route.name !== 'login'"
              :to="{ name: 'login' }"
              class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white"
            >
              Sign in
            </router-link>
            <router-link 
              v-if="route.name !== 'register'"
              :to="{ name: 'register' }"
              class="px-3 py-2 rounded-md text-sm font-medium text-white bg-primary-600 hover:bg-primary-700"
            >
              Get started
            </router-link>
          </div>
        </div>
      </div>
    </header>

    <!-- Main content -->
    <main 
      id="main-content"
      class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
    >
      <div class="max-w-md w-full space-y-8">
        <!-- Logo and heading -->
        <div class="text-center">
          <div class="flex justify-center">
            <AppLogo class="h-16 w-auto" />
          </div>
          <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white">
            {{ pageTitle }}
          </h2>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            <template v-if="route.name === 'login'">
              Or 
              <router-link 
                :to="{ name: 'register' }" 
                class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
              >
                create a new account
              </router-link>
            </template>
            <template v-else-if="route.name === 'register'">
              Already have an account? 
              <router-link 
                :to="{ name: 'login' }" 
                class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
              >
                Sign in
              </router-link>
            </template>
            <template v-else-if="route.name === 'forgot-password'">
              Remember your password? 
              <router-link 
                :to="{ name: 'login' }" 
                class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
              >
                Return to sign in
              </router-link>
            </template>
          </p>
        </div>

        <!-- Error message -->
        <div 
          v-if="error"
          class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 dark:border-red-500 p-4 rounded"
        >
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-700 dark:text-red-300">
                {{ error }}
              </p>
            </div>
          </div>
        </div>

        <!-- Success message (e.g., after password reset) -->
        <div 
          v-if="route.query.reset === 'true'"
          class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-400 dark:border-green-500 p-4 rounded"
        >
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-green-700 dark:text-green-300">
                We've sent you an email with instructions to reset your password.
              </p>
            </div>
          </div>
        </div>

        <!-- Main content slot -->
        <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
          <slot :submit="handleSubmit" :is-loading="isLoading"></slot>
        </div>

        <!-- Additional links -->
        <div class="text-center">
          <template v-if="route.name === 'login'">
            <router-link 
              :to="{ name: 'forgot-password' }" 
              class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
            >
              Forgot your password?
            </router-link>
          </template>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="md:flex md:items-center md:justify-between">
          <div class="mt-8 md:mt-0 md:order-1">
            <p class="text-center text-sm text-gray-500 dark:text-gray-400">
              &copy; {{ new Date().getFullYear() }} JobCompass AI. All rights reserved.
            </p>
            <div class="mt-2 flex justify-center space-x-6">
              <a href="#" class="text-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
                Terms
              </a>
              <a href="#" class="text-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
                Privacy
              </a>
              <a href="#" class="text-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
                Contact
              </a>
            </div>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>
