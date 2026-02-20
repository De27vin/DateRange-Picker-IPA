<template>
  <div v-show="loading">
    <div class="fixed top-0 w-96 right-0 flex flex-col items-end justify-end pr-4 pt-4 pointer-events-none" style="z-index:10000;">
      <div class="w-full max-w-sm bg-white pointer-events-auto overflow-hidden shadow-lg rounded-md border-l-4 border-blue-500">
        <div class="p-4 bg-white border-l-2 border-blue-500 flex justify-between items-center">
          <div class="pt-1">
            <p class="text-base leading-5 text-medium">{{ trans('Loading') }}...</p>
          </div>
          <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-e-transparent align-[-0.125em] text-surface motion-reduce:animate-[spin_1s_linear_infinite] text-blue-500" role="status">
            <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-8 w-8 mb-4"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { isEmpty, shiftValue } from "../assets/js/globalUtils"
import axios from "axios"

export default {
  data() {
    return {
      actions: [],
      translations: {}
    }
  },

  computed: {
    loading() {
      return this.actions.length > 0
    }
  },

  methods: {
    trans(key) {
      return this.translations?.[key] || key
    },

    addAction(action) {
      this.actions.push(action)
    },

    removeAction(action) {
      shiftValue(this.actions, action)
    },

    handleLoading(event) {
      if (isEmpty(event.detail.loading) || isEmpty(event.detail.action)) return
      event.detail.loading ? this.addAction(event.detail.action) : this.removeAction(event.detail.action)
    },

    async fetchTranslations() {
      try {
        const response = await axios.get('/data/translations')
        this.translations = response.data
      } catch (error) {
        console.error('Error fetching translations:', error)
      }
    }
  },

  created() {
    window.addEventListener('loading', this.handleLoading)
    this.fetchTranslations()
  },

  destroyed() {
    window.removeEventListener('loading', this.handleLoading)
  }
}
</script>

<style>
.loader {
  border-top-color: rgb(59, 130, 246);
  -webkit-animation: spinner 2s linear infinite;
  animation: spinner 2s linear infinite;
}

@-webkit-keyframes spinner {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spinner {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>