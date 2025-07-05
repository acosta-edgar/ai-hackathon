<script setup>
import { computed } from 'vue'

const props = defineProps({
  /**
   * The size of the logo (small, medium, large, or xl)
   */
  size: {
    type: String,
    default: 'medium',
    validator: (value) => ['small', 'medium', 'large', 'xl'].includes(value)
  },
  
  /**
   * Whether to show the text next to the logo
   */
  withText: {
    type: Boolean,
    default: false
  },
  
  /**
   * Custom class to apply to the logo
   */
  customClass: {
    type: String,
    default: ''
  }
})

// Size classes for the logo
const sizeClasses = computed(() => {
  const sizes = {
    small: 'h-6 w-6',
    medium: 'h-8 w-8',
    large: 'h-12 w-12',
    xl: 'h-16 w-16'
  }
  return sizes[props.size] || sizes.medium
})

// Text size classes based on logo size
const textSizeClasses = computed(() => {
  const sizes = {
    small: 'text-sm',
    medium: 'text-base',
    large: 'text-2xl',
    xl: 'text-4xl'
  }
  return sizes[props.size] || sizes.medium
})

// Custom colors for the logo
const logoColors = {
  primary: '#4F46E5',
  secondary: '#7C3AED',
  highlight: '#8B5CF6'
}
</script>

<template>
  <div class="flex items-center">
    <!-- Logo SVG -->
    <svg 
      :class="[sizeClasses, customClass]"
      viewBox="0 0 40 40" 
      fill="none" 
      xmlns="http://www.w3.org/2000/svg"
      aria-hidden="true"
    >
      <!-- Background circle -->
      <circle cx="20" cy="20" r="20" :fill="logoColors.primary" fill-opacity="0.1"/>
      
      <!-- Main compass shape -->
      <path 
        d="M20 8C13.3726 8 8 13.3726 8 20C8 26.6274 13.3726 32 20 32C26.6274 32 32 26.6274 32 20C32 13.3726 26.6274 8 20 8Z" 
        :fill="logoColors.primary"
      />
      
      <!-- Compass needle north -->
      <path 
        d="M20 12L23 20H17L20 12Z" 
        :fill="logoColors.highlight"
      />
      
      <!-- Compass needle south -->
      <path 
        d="M20 28L17 20H23L20 28Z" 
        :fill="logoColors.highlight"
      />
      
      <!-- Compass center -->
      <circle cx="20" cy="20" r="3" :fill="logoColors.secondary"/>
      
      <!-- Compass outline -->
      <path 
        d="M20 11V8M20 32V29M29 20H32M8 20H11M25.6569 14.3431L27.0711 12.9289M12.9289 27.0711L14.3431 25.6569M14.3431 14.3431L12.9289 12.9289M27.0711 27.0711L25.6569 25.6569" 
        :stroke="logoColors.primary" 
        stroke-width="2" 
        stroke-linecap="round"
      />
    </svg>
    
    <!-- Optional text -->
    <span 
      v-if="withText" 
      :class="['ml-2 font-bold text-gray-900 dark:text-white', textSizeClasses]"
    >
      JobCompass
    </span>
  </div>
</template>
