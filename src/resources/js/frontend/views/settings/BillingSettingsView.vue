<template>
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Billing & Subscription</h2>
    
    <div class="space-y-6">
      <!-- Current Plan -->
      <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
        <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Current Plan</h3>
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ currentPlan.name }}</h4>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ currentPlan.description }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                ${{ currentPlan.price }}/month • Next billing: {{ formatDate(currentPlan.nextBilling) }}
              </p>
            </div>
            <div class="text-right">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                Active
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Plan Features -->
      <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
        <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Plan Features</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="feature in currentPlan.features" :key="feature.name" class="flex items-start">
            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ feature.name }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ feature.description }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Method -->
      <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
        <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Payment Method</h3>
        <div v-if="paymentMethod" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="w-10 h-6 bg-gray-300 dark:bg-gray-600 rounded flex items-center justify-center mr-3">
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">••••</span>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ paymentMethod.type }} ending in {{ paymentMethod.last4 }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Expires {{ paymentMethod.expMonth }}/{{ paymentMethod.expYear }}
                </p>
              </div>
            </div>
            <button
              @click="showUpdatePayment = true"
              class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 text-sm font-medium"
            >
              Update
            </button>
          </div>
        </div>
        <div v-else class="text-center py-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">No payment method on file</p>
          <button
            @click="showUpdatePayment = true"
            class="mt-2 px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Add Payment Method
          </button>
        </div>
      </div>

      <!-- Billing History -->
      <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
        <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Billing History</h3>
        <div class="space-y-3">
          <div v-for="invoice in billingHistory" :key="invoice.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ invoice.description }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ formatDate(invoice.date) }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900 dark:text-white">${{ invoice.amount }}</p>
              <span
                :class="getStatusClass(invoice.status)"
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
              >
                {{ invoice.status }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Plan Management -->
      <div>
        <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Plan Management</h3>
        <div class="space-y-4">
          <button
            @click="showUpgradePlan = true"
            class="w-full px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Upgrade Plan
          </button>
          <button
            @click="showCancelPlan = true"
            class="w-full px-4 py-2 border border-red-300 text-red-700 rounded-md hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:border-red-600 dark:text-red-400 dark:hover:bg-red-900/20"
          >
            Cancel Subscription
          </button>
        </div>
      </div>
    </div>

    <!-- Update Payment Modal -->
    <div v-if="showUpdatePayment" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Update Payment Method</h3>
          <form @submit.prevent="updatePaymentMethod" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Card Number</label>
              <input
                v-model="paymentForm.cardNumber"
                type="text"
                placeholder="1234 5678 9012 3456"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
              >
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Expiry Date</label>
                <input
                  v-model="paymentForm.expiryDate"
                  type="text"
                  placeholder="MM/YY"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">CVC</label>
                <input
                  v-model="paymentForm.cvc"
                  type="text"
                  placeholder="123"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                >
              </div>
            </div>
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="showUpdatePayment = false"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              >
                Update
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Cancel Plan Modal -->
    <div v-if="showCancelPlan" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3 text-center">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Cancel Subscription</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Are you sure you want to cancel your subscription? You'll lose access to premium features at the end of your current billing period.
            </p>
          </div>
          <div class="items-center px-4 py-3">
            <button
              @click="cancelSubscription"
              class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300"
            >
              Yes, Cancel Subscription
            </button>
            <button
              @click="showCancelPlan = false"
              class="mt-2 px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300"
            >
              Keep Subscription
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const showUpdatePayment = ref(false)
const showCancelPlan = ref(false)
const showUpgradePlan = ref(false)

const currentPlan = ref({
  name: 'Pro Plan',
  description: 'Advanced job matching and AI-powered insights',
  price: 19.99,
  nextBilling: new Date('2024-02-01'),
  features: [
    {
      name: 'Unlimited Job Applications',
      description: 'Apply to as many jobs as you want'
    },
    {
      name: 'AI Job Matching',
      description: 'Advanced AI-powered job recommendations'
    },
    {
      name: 'Priority Support',
      description: 'Get help faster with priority support'
    },
    {
      name: 'Advanced Analytics',
      description: 'Detailed insights into your job search'
    }
  ]
})

const paymentMethod = ref({
  type: 'Visa',
  last4: '4242',
  expMonth: '12',
  expYear: '25'
})

const paymentForm = ref({
  cardNumber: '',
  expiryDate: '',
  cvc: ''
})

const billingHistory = ref([
  {
    id: 1,
    description: 'Pro Plan - January 2024',
    date: new Date('2024-01-01'),
    amount: '19.99',
    status: 'Paid'
  },
  {
    id: 2,
    description: 'Pro Plan - December 2023',
    date: new Date('2023-12-01'),
    amount: '19.99',
    status: 'Paid'
  },
  {
    id: 3,
    description: 'Pro Plan - November 2023',
    date: new Date('2023-11-01'),
    amount: '19.99',
    status: 'Paid'
  }
])

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const getStatusClass = (status) => {
  const classes = {
    Paid: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    Pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    Failed: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
  }
  return classes[status] || classes.Paid
}

const updatePaymentMethod = () => {
  // Handle payment method update
  console.log('Updating payment method:', paymentForm.value)
  showUpdatePayment.value = false
  paymentForm.value = { cardNumber: '', expiryDate: '', cvc: '' }
}

const cancelSubscription = () => {
  // Handle subscription cancellation
  console.log('Cancelling subscription')
  showCancelPlan.value = false
}
</script> 