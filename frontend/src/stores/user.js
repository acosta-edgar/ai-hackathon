import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api'

export const useUserStore = defineStore('user', () => {
  const router = useRouter()
  const toast = useToast()
  
  // State
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)
  const isAuthenticated = ref(!!localStorage.getItem('token'))
  const isLoading = ref(false)
  const error = ref(null)
  const notifications = ref([])
  const unreadNotifications = computed(() => 
    notifications.value.filter(n => !n.read).length
  )
  
  // Set auth token
  const setToken = (newToken) => {
    token.value = newToken
    if (newToken) {
      localStorage.setItem('token', newToken)
      axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
    } else {
      localStorage.removeItem('token')
      delete axios.defaults.headers.common['Authorization']
    }
    isAuthenticated.value = !!newToken
  }
  
  // Set user data
  const setUser = (userData) => {
    user.value = userData
  }
  
  // Login user
  const login = async (credentials) => {
    try {
      isLoading.value = true
      error.value = null
      
      const response = await axios.post(`${API_BASE_URL}/auth/login`, credentials)
      
      const { token: authToken, user: userData } = response.data.data
      
      setToken(authToken)
      setUser(userData)
      
      // Fetch notifications
      await fetchNotifications()
      
      return userData
    } catch (err) {
      console.error('Login error:', err)
      error.value = err.response?.data?.message || 'Login failed. Please check your credentials.'
      throw error.value
    } finally {
      isLoading.value = false
    }
  }
  
  // Register user
  const register = async (userData) => {
    try {
      isLoading.value = true
      error.value = null
      
      const response = await axios.post(`${API_BASE_URL}/auth/register`, userData)
      
      // Optionally log the user in after registration
      // const { token: authToken, user: userData } = response.data.data
      // setToken(authToken)
      // setUser(userData)
      
      return response.data.data.user
    } catch (err) {
      console.error('Registration error:', err)
      error.value = err.response?.data?.message || 'Registration failed. Please try again.'
      throw error.value
    } finally {
      isLoading.value = false
    }
  }
  
  // Logout user
  const logout = async () => {
    try {
      // Call logout API if needed
      // await axios.post(`${API_BASE_URL}/auth/logout`)
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      // Clear auth state
      setToken(null)
      setUser(null)
      notifications.value = []
      
      // Redirect to login
      router.push({ name: 'login' })
    }
  }
  
  // Fetch current user
  const fetchUser = async () => {
    try {
      isLoading.value = true
      error.value = null
      
      const response = await axios.get(`${API_BASE_URL}/auth/me`)
      setUser(response.data.data)
      
      // Fetch notifications
      await fetchNotifications()
      
      return user.value
    } catch (err) {
      console.error('Failed to fetch user:', err)
      
      // If unauthorized, clear auth state
      if (err.response?.status === 401) {
        setToken(null)
        setUser(null)
        router.push({ name: 'login' })
      }
      
      error.value = 'Failed to load user data'
      throw error.value
    } finally {
      isLoading.value = false
    }
  }
  
  // Forgot password
  const forgotPassword = async (email) => {
    try {
      isLoading.value = true
      error.value = null
      
      await axios.post(`${API_BASE_URL}/auth/forgot-password`, { email })
      
      return true
    } catch (err) {
      console.error('Forgot password error:', err)
      error.value = err.response?.data?.message || 'Failed to send password reset email.'
      throw error.value
    } finally {
      isLoading.value = false
    }
  }
  
  // Reset password
  const resetPassword = async (data) => {
    try {
      isLoading.value = true
      error.value = null
      
      const response = await axios.post(`${API_BASE_URL}/auth/reset-password`, data)
      
      // Optionally log the user in after password reset
      // const { token: authToken, user: userData } = response.data.data
      // setToken(authToken)
      // setUser(userData)
      
      return response.data
    } catch (err) {
      console.error('Reset password error:', err)
      error.value = err.response?.data?.message || 'Failed to reset password.'
      throw error.value
    } finally {
      isLoading.value = false
    }
  }
  
  // Update profile
  const updateProfile = async (data) => {
    try {
      isLoading.value = true
      error.value = null
      
      const formData = new FormData()
      
      // Append all fields to form data
      Object.keys(data).forEach(key => {
        if (data[key] !== undefined && data[key] !== null) {
          formData.append(key, data[key])
        }
      })
      
      const response = await axios.post(`${API_BASE_URL}/profile`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      
      // Update user data
      setUser({
        ...user.value,
        ...response.data.data
      })
      
      toast.success('Profile updated successfully')
      
      return response.data.data
    } catch (err) {
      console.error('Update profile error:', err)
      error.value = err.response?.data?.message || 'Failed to update profile.'
      throw error.value
    } finally {
      isLoading.value = false
    }
  }
  
  // Change password
  const changePassword = async (data) => {
    try {
      isLoading.value = true
      error.value = null
      
      await axios.post(`${API_BASE_URL}/auth/change-password`, data)
      
      toast.success('Password updated successfully')
      
      return true
    } catch (err) {
      console.error('Change password error:', err)
      error.value = err.response?.data?.message || 'Failed to change password.'
      throw error.value
    } finally {
      isLoading.value = false
    }
  }
  
  // Fetch notifications
  const fetchNotifications = async () => {
    if (!isAuthenticated.value) return
    
    try {
      const response = await axios.get(`${API_BASE_URL}/notifications`)
      notifications.value = response.data.data || []
      return notifications.value
    } catch (err) {
      console.error('Failed to fetch notifications:', err)
      return []
    }
  }
  
  // Mark notification as read
  const markNotificationAsRead = async (notificationId) => {
    try {
      await axios.patch(`${API_BASE_URL}/notifications/${notificationId}/read`)
      
      // Update local state
      const notification = notifications.value.find(n => n.id === notificationId)
      if (notification) {
        notification.read = true
      }
      
      return true
    } catch (err) {
      console.error('Failed to mark notification as read:', err)
      return false
    }
  }
  
  // Mark all notifications as read
  const markAllNotificationsAsRead = async () => {
    try {
      await axios.post(`${API_BASE_URL}/notifications/mark-all-read`)
      
      // Update local state
      notifications.value = notifications.value.map(notification => ({
        ...notification,
        read: true
      }))
      
      return true
    } catch (err) {
      console.error('Failed to mark all notifications as read:', err)
      return false
    }
  }
  
  // Initialize auth state if token exists
  const initAuth = async () => {
    if (token.value && !user.value) {
      try {
        await fetchUser()
      } catch (err) {
        console.error('Auth initialization error:', err)
        setToken(null)
      }
    }
  }
  
  // Call init on store creation
  initAuth()
  
  return {
    // State
    user,
    token,
    isAuthenticated,
    isLoading,
    error,
    notifications,
    unreadNotifications,
    
    // Actions
    setToken,
    setUser,
    login,
    register,
    logout,
    fetchUser,
    forgotPassword,
    resetPassword,
    updateProfile,
    changePassword,
    fetchNotifications,
    markNotificationAsRead,
    markAllNotificationsAsRead,
    initAuth
  }
})
