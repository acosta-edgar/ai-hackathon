<template>
  <div 
    class="inline-flex items-center justify-center"
    :class="containerClasses"
    role="status"
    :aria-label="ariaLabel"
  >
    <svg
      class="animate-spin"
      :class="spinnerClasses"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      ></circle>
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      ></path>
    </svg>
    <span v-if="showText" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ text }}
    </span>
  </div>
</template>

<script>
export default {
  name: 'LoadingSpinner',
  props: {
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
    },
    color: {
      type: String,
      default: 'primary',
      validator: (value) => ['primary', 'secondary', 'gray', 'white'].includes(value)
    },
    text: {
      type: String,
      default: 'Loading...'
    },
    showText: {
      type: Boolean,
      default: false
    },
    fullScreen: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    containerClasses() {
      const classes = []
      
      if (this.fullScreen) {
        classes.push('fixed inset-0 z-50 flex items-center justify-center bg-white bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75')
      }
      
      return classes.join(' ')
    },
    spinnerClasses() {
      const sizeClasses = {
        xs: 'w-3 h-3',
        sm: 'w-4 h-4',
        md: 'w-6 h-6',
        lg: 'w-8 h-8',
        xl: 'w-12 h-12'
      }
      
      const colorClasses = {
        primary: 'text-primary-600',
        secondary: 'text-secondary-600',
        gray: 'text-gray-600',
        white: 'text-white'
      }
      
      return `${sizeClasses[this.size]} ${colorClasses[this.color]}`
    },
    ariaLabel() {
      return this.text || 'Loading'
    }
  }
}
</script> 