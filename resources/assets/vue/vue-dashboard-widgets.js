import Vue from 'vue'
import DashboardWidgetsPage from '../../vue/dashboard-widgets/DashboardWidgetsPage.vue'

window.app = new Vue({
  el: '#vue-dashboard-widgets',
  components: { DashboardWidgetsPage },
  template: '<DashboardWidgetsPage/>',
})
