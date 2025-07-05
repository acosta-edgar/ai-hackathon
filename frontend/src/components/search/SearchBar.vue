<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { MagnifyingGlassIcon, XMarkIcon, FunnelIcon } from '@heroicons/vue/24/outline'
import { debounce } from 'lodash'

const props = defineProps({
  /**
   * Whether to show the search filters
   */
  showFilters: {
    type: Boolean,
    default: false
  },
  
  /**
   * Whether to show the clear button
   */
  showClear: {
    type: Boolean,
    default: true
  },
  
  /**
   * Placeholder text for the search input
   */
  placeholder: {
    type: String,
    default: 'Search jobs...'
  },
  
  /**
   * Debounce time in milliseconds
   */
  debounceTime: {
    type: Number,
    default: 300
  },
  
  /**
   * Initial search query
   */
  modelValue: {
    type: String,
    default: ''
  },
  
  /**
   * Additional CSS classes
   */
  class: {
    type: String,
    default: ''
  }
})

const emit = defineEmits([
  'update:modelValue',
  'search',
  'clear',
  'toggle-filters'
])

const route = useRoute()
const router = useRouter()
const searchQuery = ref(props.modelValue)
const isFocused = ref(false)
const searchInput = ref(null)

// Focus the input when the component is mounted
onMounted(() => {
  // Focus the input if it's in the header (not in the filters panel)
  if (!props.showFilters) {
    searchInput.value?.focus()
  }
  
  // Listen for keyboard shortcut (Ctrl+K or Cmd+K)
  const handleKeyDown = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault()
      searchInput.value?.focus()
    }
  }
  
  window.addEventListener('keydown', handleKeyDown)
  
  // Cleanup
  onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeyDown)
  })
})

// Watch for changes to the modelValue prop
watch(() => props.modelValue, (newVal) => {
  if (newVal !== searchQuery.value) {
    searchQuery.value = newVal
  }
})

// Debounced search function
const debouncedSearch = debounce((query) => {
  emit('update:modelValue', query)
  emit('search', query)
  
  // Update URL query parameter if we're not already on the jobs page
  if (route.name !== 'jobs') {
    router.push({ 
      name: 'jobs', 
      query: { ...route.query, q: query || undefined } 
    })
  }
}, props.debounceTime)

// Handle search input
const handleInput = (e) => {
  const query = e.target.value
  searchQuery.value = query
  debouncedSearch(query)
}

// Clear search
const clearSearch = () => {
  searchQuery.value = ''
  emit('update:modelValue', '')
  emit('clear')
  
  // Update URL query parameter
  if (route.name === 'jobs') {
    const query = { ...route.query }
    delete query.q
    router.replace({ query })
  }
  
  // Focus the input after clearing
  searchInput.value?.focus()
}

// Handle search submission
const handleSubmit = (e) => {
  e.preventDefault()
  debouncedSearch.flush()
  searchInput.value?.blur()
}

// Toggle filters panel
const toggleFilters = () => {
  emit('toggle-filters')
}

// Expose focus method
defineExpose({
  focus: () => {
    searchInput.value?.focus()
  }
})
</script>

<template>
  <div 
    class="relative flex-1 max-w-2xl"
    :class="class"
  >
    <form @submit="handleSubmit" class="w-full">
      <div 
        class="relative flex items-center"
        :class="{
          'ring-2 ring-primary-500': isFocused,
          'ring-1 ring-gray-300 dark:ring-gray-600': !isFocused,
          'rounded-lg': !showFilters,
          'rounded-t-lg': showFilters,
          'bg-white dark:bg-gray-800': true
        }"
      >
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
        </div>
        
        <input
          ref="searchInput"
          type="text"
          :value="searchQuery"
          :placeholder="placeholder"
          class="block w-full pl-10 pr-12 py-2 border-0 bg-transparent text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-0 sm:text-sm"
          @input="handleInput"
          @focus="isFocused = true"
          @blur="isFocused = false"
          @keydown.esc="searchInput?.blur()"
        >
        
        <!-- Clear button -->
        <div v-if="showClear && searchQuery" class="absolute inset-y-0 right-0 flex items-center pr-3">
          <button
            type="button"
            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none"
            @click="clearSearch"
          >
            <span class="sr-only">Clear search</span>
            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
          </button>
        </div>
        
        <!-- Search button (for mobile) -->
        <div class="hidden sm:flex absolute inset-y-0 right-0 py-1.5 pr-1.5">
          <kbd class="inline-flex items-center px-2 py-1 border border-gray-200 dark:border-gray-600 rounded text-xs font-sans font-medium text-gray-400 dark:text-gray-400">
            ⌘K
          </kbd>
        </div>
        
        <!-- Filters toggle button -->
        <div 
          v-if="showFilters" 
          class="absolute inset-y-0 right-0 flex items-center pr-3 border-l border-gray-200 dark:border-gray-700 pl-3"
          :class="{
            'bg-gray-100 dark:bg-gray-700': isFocused
          }"
        >
          <button
            type="button"
            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none flex items-center"
            @click="toggleFilters"
          >
            <FunnelIcon class="h-5 w-5" aria-hidden="true" />
            <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Filters</span>
          </button>
        </div>
      </div>
    </form>
    
    <!-- Filters panel (slot) -->
    <div 
      v-if="showFilters" 
      class="bg-white dark:bg-gray-800 shadow-lg rounded-b-lg border border-t-0 border-gray-200 dark:border-gray-700 p-4"
    >
      <slot name="filters"></slot>
    </div>
  </div>
</template>
