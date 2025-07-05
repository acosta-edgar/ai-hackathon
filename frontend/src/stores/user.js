import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useUserStore = defineStore('user', () => {
  // State
  const user = ref(null)
  const isAuthenticated = computed(() => !!user.value)
  const isLoading = ref(false)
  const error = ref(null)

  // Actions
  const setUser = (userData) => {
    user.value = userData
  }

  const clearUser = () => {
    user.value = null
    error.value = null
  }

  const setError = (err) => {
    error.value = err
  }

  const clearError = () => {
    error.value = null
  }

  const setLoading = (loading) => {
    isLoading.value = loading
  }

  // For demo purposes - remove in production
  const demoLogin = async (credentials) => {
    try {
      setLoading(true)
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1000))
      
      // Mock user data
      const mockUser = {
        id: '1',
        name: 'Demo User',
        email: 'demo@example.com',
        role: 'user'
      }
      
      setUser(mockUser)
      return { success: true, user: mockUser }
    } catch (err) {
      setError(err.message || 'Failed to login')
      return { success: false, error: err.message || 'Failed to login' }
    } finally {
      setLoading(false)
    }
  }

  // Getters
  const userInitials = computed(() => {
    if (!user.value?.name) return ''
    return user.value.name
      .split(' ')
      .map(n => n[0])
      .join('')
      .toUpperCase()
      .substring(0, 2)
  })

  return {
    // State
    user,
    isAuthenticated,
    isLoading,
    error,
    
    // Getters
    userInitials,
    
    // Actions
    setUser,
    clearUser,
    setError,
    clearError,
    setLoading,
    demoLogin
  }
})
