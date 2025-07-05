<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'

const currentYear = ref(new Date().getFullYear())

// Example navigation links
const footerNavigation = {
  main: [
    { name: 'About', href: '#' },
    { name: 'Blog', href: '#' },
    { name: 'Jobs', href: '#' },
    { name: 'Pricing', href: '#' },
    { name: 'Contact', href: '#' },
  ],
  social: [
    {
      name: 'GitHub',
      href: 'https://github.com/yourusername/jobcompass-ai',
      icon: 'github',
    },
    {
      name: 'Twitter',
      href: 'https://twitter.com/yourusername',
      icon: 'twitter',
    },
    {
      name: 'LinkedIn',
      href: 'https://linkedin.com/company/yourcompany',
      icon: 'linkedin',
    },
  ],
}

// Get icon component based on name
const getIconComponent = (iconName) => {
  const icons = {
    github: 'M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.166 6.839 9.49.5.09.682-.218.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.464-1.11-1.464-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.254-.446-1.27.098-2.647 0 0 .84-.268 2.75 1.025A9.578 9.578 0 0112 6.836c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.202 2.393.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.163 22 16.418 22 12c0-5.523-4.477-10-10-10z',
    twitter: 'M8.29 20.25c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84',
    linkedin: 'M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z',
  }
  
  return icons[iconName] || ''
}
</script>

<template>
  <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex justify-center space-x-6 md:order-2">
          <a 
            v-for="item in footerNavigation.social" 
            :key="item.name"
            :href="item.href" 
            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
            target="_blank"
            rel="noopener noreferrer"
          >
            <span class="sr-only">{{ item.name }}</span>
            <svg 
              class="h-6 w-6" 
              fill="currentColor" 
              viewBox="0 0 24 24"
              aria-hidden="true"
            >
              <path :d="getIconComponent(item.icon)" />
            </svg>
          </a>
        </div>
        <div class="mt-8 md:mt-0 md:order-1">
          <p class="text-center text-sm text-gray-500 dark:text-gray-400">
            &copy; {{ currentYear }} JobCompass AI. All rights reserved.
          </p>
          <div class="mt-2 flex justify-center space-x-6">
            <a
              v-for="item in footerNavigation.main"
              :key="item.name"
              :href="item.href"
              class="text-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300"
            >
              {{ item.name }}
            </a>
          </div>
        </div>
      </div>
      <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-8 md:flex md:items-center md:justify-between">
        <div class="flex space-x-6 md:order-2">
          <a href="#" class="text-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
            Terms & Conditions
          </a>
          <a href="#" class="text-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
            Privacy Policy
          </a>
          <a href="#" class="text-sm text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
            Cookie Policy
          </a>
        </div>
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 md:mt-0 md:order-1">
          Made with ❤️ by the JobCompass AI team
        </p>
      </div>
    </div>
  </footer>
</template>
